<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/questionary/questionary.aw,v 1.1 2006/10/02 12:18:17 tarvo Exp $
// questionary.aw - K&uuml;simustik 
/*

@classinfo syslog_type=ST_QUESTIONARY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general


@groupinfo edit caption=Timmi
@default group=edit
	@property q_nr type=textbox
	@caption kysimuste arv

@groupinfo show caption=N&auml;ita
@default group=show
	@property show_tbl type=table
*/

class questionary extends class_base
{
	function questionary()
	{
		$this->init(array(
			"tpldir" => "questionary",
			"clid" => CL_QUESTIONARY
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
