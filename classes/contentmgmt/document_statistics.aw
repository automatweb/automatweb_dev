<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/document_statistics.aw,v 1.6 2004/03/25 13:51:17 kristo Exp $
// document_statistics.aw - Dokumentide vaatamise statistika 
/*

@classinfo syslog_type=ST_DOCUMENT_STATISTICS relationmgr=yes no_status=1

@default table=objects
@default group=general

@property timespan type=select field=meta method=serialize
@caption Ajavahemik

@property count type=textbox field=meta method=serialize
@caption Mitu esimest

@property stats type=table store=no
@caption TOP

@groupinfo folders caption="Kataloogid ja perioodid"
@default group=folders

@property folders type=table store=no 
@caption Kataloogid

@property periods type=table store=no 
@caption Perioodid

@property period_type type=select field=meta method=serialize
@caption Milliseid perioode kasutada

@reltype SHOW_FOLDER value=1 clid=CL_MENU
@caption kataloog

@reltype SHOW_PERIOD value=2 clid=CL_PERIOD
@caption periood

*/

class document_statistics extends class_base
{
	function document_statistics()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/document_statistics",
			"clid" => CL_DOCUMENT_STATISTICS
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "timespan":
				$data["options"] = array(
					"day" => "P&auml;ev",
					"week" => "N&auml;dal",
					"mon" => "Kuu"
				);
				break;

			case "period_type":
				$data["options"] = array(
					"rel" => "Seostatud perioodid",
					"all" => "K&otilde;ik perioodid",
					"not" => "Mitteperioodilised",
					"act" => "Aktiivne periood"
				);
				break;

			case "stats":
				$st = $this->get_stat_arr($arr["obj_inst"]);
				
				$data["vcl_inst"]->define_field(array(
					"name" => "docid",
					"caption" => "Dokument"
				));

				$data["vcl_inst"]->define_field(array(
					"name" => "hits",
					"caption" => "Vaatamisi",
					"align" => "center",
					"type" => "int",
					"numeric" => 1,
					"sortable" => 1
				));

				foreach($st as $did => $hc)
				{
					$o = obj($did);
					$a = array(
						"docid" => $o->name(),
						"hits" => $hc
					);
					$data["vcl_inst"]->define_data($a);
				}

				$data["vcl_inst"]->set_default_sortby("hits");
				$data["vcl_inst"]->set_default_sorder("desc");
				$data["vcl_inst"]->sort_by();
				break;

			case "folders":
				$this->do_folders_table($arr);
				break;

			case "periods":
				$this->do_periods_table($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "folders":
				$arr["obj_inst"]->set_meta("subs", $arr["request"]["subs"]);
				break;
		}
		return $retval;
	}	

	function _init_folders_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Kataloog"
		));

		$t->define_field(array(
			"name" => "subs",
			"caption" => "K.A. Alamkataloogid",
			"align" => "center"
		));
	}

	function do_folders_table(&$arr)
	{
		$t =&$arr["prop"]["vcl_inst"];
		$this->_init_folders_table($t);

		$subs = $arr["obj_inst"]->meta("subs");

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_SHOW_FOLDER)) as $c)
		{
			$o = $c->to();
			$t->define_data(array(
				"name" => $o->path_str(),
				"subs" => html::checkbox(array(
					"name" => "subs[".$o->id()."]",
					"value" => 1,
					"checked" => ($subs[$o->id()] == 1)
				))
			));
		}
	}

	function _init_periods_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Periood"
		));
	}

	function do_periods_table(&$arr)
	{
		$t =&$arr["prop"]["vcl_inst"];
		$this->_init_periods_table($t);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_SHOW_PERIOD)) as $c)
		{
			$o = $c->to();
			$t->define_data(array(
				"name" => $o->path_str(),
			));
		}
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);

		classload("vcl/table");
		$t = new aw_table();

		$st = $this->get_stat_arr($ob);
				

		$this->read_template("show.tpl");

		$l = "";

		foreach($st as $did => $hc)
		{
			$o = obj($did);
			$this->vars(array(
				"doc_name" => $o->name(),
				"docid" => $did,
				"hits" => $hc
			));

			$l .= $this->parse("LINE");
		}

		$this->vars(array(
			"LINE" => $l
		));
		return $this->parse();
	}

	/** adds a hit to the document hit list to the document $docid
	**/
	function add_hit($docid)
	{
		// open and lock file
		list($fp, $size) = $this->_open_and_lock_stat_file();

		// get contents
		$fc = explode("\n", fread($fp, $size));

		// modify the correct line
		$nf = "";
		$found = false;
		foreach($fc as $line)
		{
			if ($line == "")
			{
				continue;
			}
			list($did, $hc) = explode(",", $line);
			if ($did == $docid)
			{
				$line = $docid.",".($hc+1);
				$found = true;
			}
			$nf .= trim($line)."\n";
		}

		if (!$found)
		{
			$nf .= $docid.",1\n";
		}
	
		// write file
		ftruncate($fp, 0);
		fwrite($fp, $nf);

		// unlock & close
		$this->_close_and_unlock_stat_file($fp);
	}

	function _open_and_lock_stat_file()
	{
		$fld = $this->cfg["site_basedir"]."/files/docstats";
		if (!is_dir($fld))
		{
			mkdir($fld, 0777);
			@chmod($fld, 0777);
		}
		$fp = $fld."/".date("Y-m-d").".txt";
		$ret = fopen($fp, "a+");
		flock($ret, LOCK_EX);
		return array($ret, filesize($fp));
	}

	function _close_and_unlock_stat_file($fp)
	{
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	/** returns an array of statistics for document views

		@comment

			returns an array of document id => hit count 
			taking into account the display statistics from the object passed as a parameter
	**/
	function get_stat_arr($obj)
	{
		$timespan = $obj->prop("timespan");
		$count = $obj->prop("count");

		classload("date_calc");
		if ($timespan == "week")
		{
			$fc = array();
			$tm = get_week_start();
			while ($tm < time())
			{
				$fp = $this->cfg["site_basedir"]."/files/docstats/".date("Y-m-d", $tm).".txt";
				$tmp = explode("\n", $this->get_file(array("file" => $fp)));
				if (is_array($tmp))
				{
					$fc += $tmp;
				}
				$tm += 24*3600;
			}
		}
		else
		if ($timespan == "mon")
		{
			$fc = array();
			$tm = get_month_start();
			while ($tm < time())
			{
				$fp = $this->cfg["site_basedir"]."/files/docstats/".date("Y-m-d", $tm).".txt";
				$tmp = explode("\n", $this->get_file(array("file" => $fp)));
				if (is_array($tmp))
				{
					$fc += $tmp;
				}
				$tm += 24*3600;
			}
		}
		else
		{
			// day
			$fp = $this->cfg["site_basedir"]."/files/docstats/".date("Y-m-d").".txt";
			$fc = explode("\n", $this->get_file(array("file" => $fp)));
		}

		// now, get list of documents 
		$c_dids = $this->_get_document_list($obj);

		$ds_arr = array();

		foreach($fc as $line)
		{
			if ($line == "")
			{
				continue;
			}
			list($did, $hc) = explode(",", $line);
			if ($c_dids[$did])
			{
				$ds_arr[$did] += $hc;
			}
		}
		
		arsort($ds_arr);
		$ret = array();
		$i = 0;
		foreach($ds_arr as $did => $hc)
		{
			if ($i > $count)
			{
				return $ret;
			}
		
			$ret[$did] = $hc;
			$i++;
		}
		return $ret;
	}

	/** returns a list of document id's that this object can show stats for
	**/
	function _get_document_list($o)
	{
		$menus = array();

		$subs = $o->meta("subs");
		foreach($o->connections_from(array("type" => 1 /* RELTYPE_SHOW_FOLDER */)) as $c)
		{	
			$menus[$c->prop("to")] = $c->prop("to");

			if ($subs[$c->prop("to")] == 1)
			{
				$tmp = $this->get_menu_list(false, false, $c->prop("to"));
				foreach($tmp as $_id => $_pt)
				{
					$menus[$_id] = $_id;
				}
			}
		}

		switch($o->prop("period_type"))
		{
			case "all": /* all periods */
				$pl = new object_list(array(
					"class_id" => CL_PERIOD
				));
				$pc = $pl->ids();
				break;

			case "not": /* not periodic */
				$pc = "0";
				break;

			case "act": /* active period only */
				$pc = aw_global_get("act_per_id");
				break;

			case "rel": /* connected poeriods */
			default:
				$pc = array();
				foreach($o->connections_from(array("type" => 2 /* RELTYPE_SHOW_PERIOD */)) as $c)
				{
					$pc[] = $c->prop("to");
				}
				break;
		}

		// now get docs
		$ret = array();
		$ol = new object_list(array(
			"class_id" => array(CL_DOCUMENT, CL_DOCUMENT_BROTHER, CL_PERIODIC_SECTION),
			"parent" => $menus,
			"period" => $pc
		));

		return $this->make_keys($ol->ids());
	}
}
?>
