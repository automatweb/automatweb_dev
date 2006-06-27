<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_admin.aw,v 1.1 2006/06/27 18:07:32 tarvo Exp $
// scm_admin.aw - Spordiv&otilde;istluste haldus 
/*

@classinfo syslog_type=ST_SCM_ADMIN relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class scm_admin extends class_base
{
	function scm_admin()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_admin",
			"clid" => CL_SCM_ADMIN
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
