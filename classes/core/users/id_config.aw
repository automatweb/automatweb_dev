<?php
// $Header: /home/cvs/automatweb_dev/classes/core/users/id_config.aw,v 1.1 2007/01/19 10:48:12 tarvo Exp $
// id_config.aw - ID-Kaardi konfiguratsioon 
/*

@classinfo syslog_type=ST_ID_CONFIG relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class id_config extends class_base
{
	function id_config()
	{
		$this->init(array(
			"tpldir" => "core/users/id_config",
			"clid" => CL_ID_CONFIG
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
