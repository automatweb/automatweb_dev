<?php
// $Header: /home/cvs/automatweb_dev/classes/common/citizenship.aw,v 1.1 2007/12/28 13:01:55 markop Exp $
// citizenship.aw - Citizenship 
/*

@classinfo syslog_type=ST_CITIZENSHIP relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property start type=date_select field=meta method=serialize
@caption Alguskuup&auml;ev

@property end type=date_select field=meta method=serialize
@caption L&otilde;ppkuup&auml;ev

@property country type=relpicker store=connect reltype=RELTYPE_COUNTRY
@caption Riik

@reltype COUNTRY value=1 clid=CL_CRM_COUNTRY
@caption Riik


*/

class citizenship extends class_base
{
	function citizenship()
	{
		$this->init(array(
			"tpldir" => "applications/crm/citizenship",
			"clid" => CL_CITIZENSHIP
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
