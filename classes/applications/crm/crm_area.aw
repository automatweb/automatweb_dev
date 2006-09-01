<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_area.aw,v 1.2 2006/09/01 09:35:40 markop Exp $
// crm_area.aw - Piirkond 
/*

@classinfo syslog_type=ST_CRM_AREA relationmgr=yes no_status=1 prop_cb=1

@default table=objects
@default group=general

@property country type=relpicker reltype=RELTYPE_COUNTRY
@caption Riik

@property comment type=textarea cols=40 rows=3 table=objects field=comment
@caption Kommentaar

@reltype COUNTRY value=1 clid=CL_CRM_COUNTRY
@caption Riik

*/

class crm_area extends class_base
{
	function crm_area()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_area",
			"clid" => CL_CRM_AREA
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
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
