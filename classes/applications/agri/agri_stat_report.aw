<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/agri/Attic/agri_stat_report.aw,v 1.1 2004/03/28 21:45:14 kristo Exp $
// agri_stat_report.aw - Agri statistika 
/*

@classinfo syslog_type=ST_AGRI_STAT_REPORT no_comment=1 no_status=1

@default table=objects
@default group=general

@groupinfo general caption="Statistika aja l&otilde;ikes" submit=no
@property stat_time type=table group=general no_caption=1

@groupinfo stat_amt caption="Statistika koguste l&otilde;ikes" submit=no


@property stat_amt type=table group=stat_amt no_caption=1


@property stat_amt_eek type=table group=stat_amt no_caption=1

*/

class agri_stat_report extends class_base
{
	function agri_stat_report()
	{
		$this->init(array(
			"tpldir" => "applications/agri/agri_stat_report",
			"clid" => CL_AGRI_STAT_REPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "name":
				if (!is_admin())
				{
					return PROP_IGNORE;
				}
				break;

			case "stat_time":
				$this->do_stat_time($arr);
				break;

			case "stat_amt":
				$this->do_stat_amt($arr);
				break;

			case "stat_amt_eek":
				$this->do_stat_amt_eek($arr);
				break;
		};
		return $retval;
	}

	function init_stat_time(&$t)
	{
		$t->define_field(array(
			"name" => "place",
			"caption" => "Asukoht",
		));

		// create cols for -7days to  +7 days
		for ($i = -7; $i < 7; $i++)
		{
			$t->define_field(array(
				"name" => "d".date("dmY", time() + ($i * 24 * 3600)),
				"caption" => date("d.m.Y", time() + ($i * 24 * 3600)),
				"align" => "center"
			));
		}
	}

	function do_stat_time(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_stat_time($t);

		$ad = get_instance("applications/agri/agri_data");

		foreach($ad->states as $nr => $state)
		{
			$this->db_query("SELECT 
					count(*) as cnt, 
					DAYOFMONTH(FROM_UNIXTIME(created)) as day, 
					MONTH(FROM_UNIXTIME(created))as mon, 
					YEAR(FROM_UNIXTIME(created)) as year,
					created
				FROM 
					objects 
					LEFT JOIN agri_data ON objects.brother_of = agri_data.id
				WHERE 
					class_id = ".CL_AGRI_DATA." AND 
					status > 0 AND
					agri_data.rep_addr_pers_state = '$nr'
				GROUP BY  year,mon,day");
			$td = array();
			while ($row = $this->db_next())
			{
				$td["d".date("dmY", $row["created"])] = $row["cnt"]." tk";
			}

			$td["place"] = $state;

			$t->define_data($td);
		}

		$t->set_sortable(false);
	}


	function init_stat_amt(&$t)
	{
		$t->define_field(array(
			"name" => "place",
			"caption" => "Asukoht",
		));

		// create cols for all products
		$ol = new object_list(array(
			"class_id" => CL_AGRI_PROD_CODE,
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$t->define_field(array(
				"name" => "o".$o->prop("code"),
				"caption" => $o->name(),
				"align" => "center"
			));
		}
	}

	function do_stat_amt(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_stat_amt($t);

		$ad = get_instance("applications/agri/agri_data");

		$this->db_query("
			SELECT 
				SUM(value) as sum,
				agri_prod_data.prod_code as prod_code,
				agri_data.rep_addr_pers_state as state
			FROM
				agri_prod_data_row_sums
				LEFT JOIN agri_prod_data ON agri_prod_data_row_sums.obj_id = agri_prod_data.id
				LEFT JOIN objects as pd_o ON agri_prod_data.id = pd_o.oid
				LEFT JOIN aliases ON aliases.target = agri_prod_data.id
				LEFT JOIN agri_data ON aliases.source = agri_data.id
			WHERE
				pd_o.status > 0 AND 
				unit = 'kg' 
			GROUP BY 
				agri_data.rep_addr_pers_state,agri_prod_data.prod_code
		");
		$dat = array();
		while ($row = $this->db_next())
		{
			$dat[$row["state"]]["o".$row["prod_code"]] += $row["sum"];
		}

		foreach($ad->states as $nr => $state)
		{
			$td = array();
			if (is_array($dat[$nr]))
			{
				foreach($dat[$nr] as $pc => $sm)
				{
					$td[$pc] = $sm." kg";
				}
			}

			$td["place"] = $state;

			$t->define_data($td);
		}

		$t->set_sortable(false);
	}

	function do_stat_amt_eek(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_stat_amt($t);

		$ad = get_instance("applications/agri/agri_data");

		$this->db_query("
			SELECT 
				SUM(value) as sum,
				agri_prod_data.prod_code as prod_code,
				agri_data.rep_addr_pers_state as state
			FROM
				agri_prod_data_row_sums
				LEFT JOIN agri_prod_data ON agri_prod_data_row_sums.obj_id = agri_prod_data.id
				LEFT JOIN objects as pd_o ON agri_prod_data.id = pd_o.oid
				LEFT JOIN aliases ON aliases.target = agri_prod_data.id
				LEFT JOIN agri_data ON aliases.source = agri_data.id
			WHERE
				pd_o.status > 0 AND 
				unit = 'eek' 
			GROUP BY 
				agri_data.rep_addr_pers_state,agri_prod_data.prod_code
		");
		$dat = array();
		while ($row = $this->db_next())
		{
			$dat[$row["state"]]["o".$row["prod_code"]] += $row["sum"];
		}

		foreach($ad->states as $nr => $state)
		{
			$td = array();
			if (is_array($dat[$nr]))
			{
				foreach($dat[$nr] as $pc => $sm)
				{
					$td[$pc] = $sm." eek";
				}
			}

			$td["place"] = $state;

			$t->define_data($td);
		}

		$t->set_sortable(false);
	}


	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
}
?>
