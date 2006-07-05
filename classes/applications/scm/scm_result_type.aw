<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_result_type.aw,v 1.2 2006/07/05 14:52:42 tarvo Exp $
// scm_result_type.aw - Paremusj&auml;rjestuse t&uuml;&uuml;p 
/*

@classinfo syslog_type=ST_SCM_RESULT_TYPE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property unit type=select
@caption &Uuml;hik

@property sort type=select
@caption Sorteerimine

*/

class scm_result_type extends class_base
{
	function scm_result_type()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_result_type",
			"clid" => CL_SCM_RESULT_TYPE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "unit":
				$prop["options"] = array(
					"time" => t("Aeg"),
					"points" => t("Puntkid"),
					"length" => t("Kaugus"),
				);
			break;

			case "sort":
				$prop["options"] = array(
					"asc" => t("Kasvav"),
					"desc" => t("Kahanev"),
				);
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
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
	
	function get_result_types($arg = array())
	{
		if(strlen($arg["organizer"]))
		{
			$filt["parent"] = $arg["organizer"];
		}
		$filt["class_id"] = CL_SCM_RESULT_TYPE;
		$list = new object_list($filt);
		return $list->arr();
	}

	function add_result_type($arg = array())
	{
		$obj = obj();
		$obj->set_parent($arg["organizer"]);
		$obj->set_class_id(CL_SCM_RESULT_TYPE);
		$obj->set_name($arg["name"]);
		$obj->set_prop("unit", $arg["unit"]);
		$obj->set_prop("sort", $arg["sort"]);
		$oid = $obj->save_new();
		return $oid;
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
