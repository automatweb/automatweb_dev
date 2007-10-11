<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_remote_makse.aw,v 1.1 2007/10/11 15:02:46 dragut Exp $
// expp_remote_makse.aw - expp remote makse 
/*

@classinfo syslog_type=ST_EXPP_REMOTE_MAKSE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class expp_remote_makse extends class_base
{
	function expp_remote_makse()
	{
		$this->init(array(
			"tpldir" => "expp/expp_remote_makse",
			"clid" => CL_EXPP_REMOTE_MAKSE
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
