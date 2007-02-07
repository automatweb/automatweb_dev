<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/trademark_status.aw,v 1.2 2007/02/07 12:48:46 markop Exp $
// trademark_status.aw - Trademark status 
/*

@classinfo syslog_type=ST_TRADEMARK_STATUS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property verified type=checkbox 
	@caption Kinnitatud

	@property exported type=checkbox caption=no
	@caption Eksporditud

	@property export_date type=date_select
	@caption Ekspordi kuup&auml;ev

	@property nr type=textbox
	@caption Taotluse number


*/

class trademark_status extends class_base
{
	function trademark_status()
	{
		$this->init(array(
			"tpldir" => "applications/clients/patent_office/trademark_status",
			"clid" => CL_TRADEMARK_STATUS
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
