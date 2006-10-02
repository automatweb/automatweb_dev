<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/questionary/question_group.aw,v 1.1 2006/10/02 12:31:39 tarvo Exp $
// question_group.aw - K&uml;simustegrupp 
/*

@classinfo syslog_type=ST_QUESTION_GROUP relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class question_group extends class_base
{
	function question_group()
	{
		$this->init(array(
			"tpldir" => "applications/questionary/question_group",
			"clid" => CL_QUESTION_GROUP
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
