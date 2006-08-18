<?php
// $Header: /home/cvs/automatweb_dev/classes/common/unit.aw,v 1.1 2006/08/18 13:07:39 markop Exp $
// unit.aw - Unit 
/*

@classinfo syslog_type=ST_UNIT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=seralize

@property unit_code type=textbox
@caption &Uuml;hiku kood

@property unit_sort type=select
@caption &Uuml;hiku liik
*/

class unit extends class_base
{
	function unit()
	{
		$this->init(array(
			"tpldir" => "common//unit",
			"clid" => CL_UNIT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "unit_sort":
				$prop["options"] = array(0 => "" , 1 => t("pikkus&uuml;hik"), 2 => t("massi&uuml;hik"), 3 => t("koguse&uuml;hik"), 4 => t("mahu&uuml;hik"), 5 => t("aja&uuml;hik"));
			
			//-- get_property --//
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
