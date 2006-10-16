<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/rostering/rostering_work_entry.aw,v 1.3 2006/10/16 10:37:48 kristo Exp $
// rostering_work_entry.aw - T&ouml;&ouml;aegade sisestus 
/*

@classinfo syslog_type=ST_ROSTERING_WORK_ENTRY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_rostering_work_entry index=aw_oid master_index=brother_of master_table=objects

@default table=aw_rostering_work_entry
@default group=general

	@property graph type=relpicker automatic=1 reltype=RELTYPE_GRAPH field=aw_graph
	@caption Graafik

	@property unit type=select field=aw_unit
	@caption &Uuml;ksus

	@property day type=select field=aw_day
	@caption P&auml;ev

	@property g_wp type=relpicker automatic=1 reltype=RELTYPE_WORKBENCH field=aw_g_wp
	@caption T&ouml;&ouml;laud

@default group=entry

	@property entry_t type=table store=no no_caption=1

@groupinfo entry caption="Sisestamine"

@reltype GRAPH value=1 clid=CL_ROSTERING_SCHEDULE
@caption Graafik

@reltype WORKBENCH value=2 clid=CL_ROSTERING_WORKBENCH
@caption T&ouml;&ouml;laud
*/

class rostering_work_entry extends class_base
{
	function rostering_work_entry()
	{
		$this->init(array(
			"tpldir" => "applications/rostering/rostering_work_entry",
			"clid" => CL_ROSTERING_WORK_ENTRY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "entry_t":
				$this->_entry_t($arr);
				break;

			case "g_wp":
				if ($arr["request"]["wp"])
				{
					$prop["value"] = $arr["request"]["wp"];
				}
				break;

			case "unit":
				if ($arr["new"])
				{
					return PROP_IGNORE;
				}
				$wp = obj($arr["obj_inst"]->prop("g_wp"));
				$co = obj($wp->prop("owner"));
				$co_i = $co->instance();
				$se = $co_i->get_all_org_sections($co);
				$opts = array("" => t("--vali--"));	
				foreach($se as $se_id)
				{
					$s = obj($se_id);
					$opts[$se_id] = $s->name();
				}
				$prop["options"] = $opts;
				break;

			case "day":
				if (!$arr["obj_inst"]->prop("graph"))
				{
					return PROP_IGNORE;
				}
				$gr = obj($arr["obj_inst"]->prop("graph"));
				$start = $gr->prop("g_start");
				$end = $gr->prop("g_end");
				$opts = array("" => t("--vali--"));
				for($i = $start; $i < $end; $i+=24*3600)
				{
					$opts[$i] = date("d.m.Y", $i);
				}
				$prop["options"] = $opts;
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "entry_t":
				$arr["obj_inst"]->set_meta("d", $arr["request"]["d"]);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_rostering_work_entry(aw_oid int primary key, aw_graph int)");
			return true;
		}
		switch($f)
		{
			case "aw_unit":
			case "aw_day":
			case "aw_g_wp":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function _init_entry_t(&$t)
	{
		$t->define_field(array(
			"name" => "def",
			"caption" => t("Planeeritud"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "wpl",
			"caption" => t("T&ouml;&ouml;post"),
			"align" => "center",
			"parent" => "def"
		));
		$t->define_field(array(
			"name" => "person",
			"caption" => t("Isik"),
			"align" => "center",
			"parent" => "def"
		));
		$t->define_field(array(
			"name" => "hrs",
			"caption" => t("Aeg"),
			"align" => "center",
			"parent" => "def"
		));

		$t->define_field(array(
			"name" => "real",
			"caption" => t("Tegelik"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "correct",
			"caption" => t("Graafik korrektne"),
			"align" => "center",
			"parent" => "real"
		));
		$t->define_field(array(
			"name" => "set_person",
			"caption" => t("T&ouml;&ouml;taja"),
			"align" => "center",
			"parent" => "real"
		));
		$t->define_field(array(
			"name" => "set_hrs_from",
			"caption" => t("Alates"),
			"align" => "center",
			"parent" => "real"
		));
		$t->define_field(array(
			"name" => "set_hrs_to",
			"caption" => t("Kuni"),
			"align" => "center",
			"parent" => "real"
		));
		$t->define_field(array(
			"name" => "pay_type",
			"caption" => t("Tasu liik"),
			"align" => "center",
			"parent" => "real"
		));
	}

	function _entry_t($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_entry_t($t);

		// filter persons by section and graph by day selected
		$wp = obj($arr["obj_inst"]->prop("g_wp"));
		$co = obj($wp->prop("owner"));
		$co_i = $co->instance();
		
		$ppl = array();
		if ($arr["obj_inst"]->prop("unit"))
		{
			$u = obj($arr["obj_inst"]->prop("unit"));
			foreach($u->connections_from(array("type" => "RELTYPE_WORKERS")) as $c)
			{
				$ppl[$c->prop("to")] = $c->prop("to.name");
			}
		}
		else
		{
			$ppl = $co_i->get_employee_picker($co);
		}

		if ($arr["obj_inst"]->prop("day"))
		{
			$start = $arr["obj_inst"]->prop("day");
			$end += 24*3600;
		}
		else
		{
			$gr = obj($arr["obj_inst"]->prop("graph"));
			$start = $gr->prop("g_start");
			$end = $gr->prop("g_end");
		}

		$pt = array();
		foreach($wp->connections_from(array("type" => "RELTYPE_PAYMENT_TYPE")) as $c)
		{
			$pt[$c->prop("to")] = $c->prop("to.name");
		}

		$d = $arr["obj_inst"]->meta("d");
		$m = get_instance("applications/rostering/rostering_model");
		foreach($ppl as $p_id => $p_nm)
		{
			$wt = $m->get_schedule_for_person(obj($p_id), $start, $end);
			foreach($wt as $wt_id => $wt_item)
			{
				$t->define_data(array(
					"wpl" => html::obj_change_url($wt_item["workplace"]),
					"person" => html::obj_change_url($p_id),
					"hrs" => date("d.m.Y H:i", $wt_item["start"])." - ".date("d.m.Y H:i", $wt_item["end"]),
					"correct" => html::checkbox(array(
						"name" => "d[$wt_id][correct]",
						"value" => 1,
						"checked" => $d[$wt_id]["correct"]
					)),
					"set_person" => html::select(array(
						"name" => "d[$wt_id][person]",
						"options" => array("" => t("--vali--")) + $ppl,
						"value" => $d[$wt_id]["person"]
					)),
					"set_hrs_from" => html::textbox(array(
						"name" => "d[$wt_id][h_from]",
						"size" => 5,
						"value" => $d[$wt_id]["h_from"]
					)),
					"set_hrs_to" => html::textbox(array(
						"name" => "d[$wt_id][h_to]",
						"size" => 5,
						"value" => $d[$wt_id]["h_to"]
					)),
					"pay_type" => html::select(array(
						"name" => "d[$wt_id][pay_type]",
						"options" => array("" => t("--vali--")) + $pt,
						"value" => $d[$wt_id]["pay_type"]
					)),
				));
			}
		}
		$t->set_sortable(false);
	}
}
?>
