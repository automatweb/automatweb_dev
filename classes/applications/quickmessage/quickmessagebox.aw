<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/quickmessage/quickmessagebox.aw,v 1.1 2004/08/17 11:33:40 ahti Exp $
// quickmessagebox.aw - Kiirsõnumite haldus 
/*

@classinfo syslog_type=ST_QUICKMESSAGEBOX relationmgr=yes

@default table=objects
@default group=general

@default store=no
@default submit=no

@groupinfo inbox caption="Inbox"
@default group=inbox

@property inbox type=table no_caption=1
@caption this is inbox


@groupinfo outbox caption="Outbox"
@default group=outbox

@property fake1 type=text
@caption this is outbox


@groupinfo archive caption="Arhiiv"
@default group=archive

@property fake2 type=text
@caption this is archive

@classinfo no_status=1 no_comment=1



*/

class quickmessagebox extends class_base
{
	function quickmessagebox()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/quickmessage/quickmessagebox",
			"clid" => CL_QUICKMESSAGEBOX
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function get_inbox_for_user($arr)
	{
		$user_to = $arr["user_to"];
		$ol = new object_list(array(
			"user_to" => $user_to,
			"class_id" => CL_QUICKMESSAGE,
			"sort_by" =>  "objects.created DESC",
		));
		$msgs = array();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$msgs[] = $o->properties();
		};
		return $msgs;
	}

	function get_outbox_for_user($arr)
	{
		$user_from = $arr["user_from"];
		$ol = new object_list(array(
			"user_from" => $user_from,
			"class_id" => CL_QUICKMESSAGE,
			"sort_by" => "objects.created DESC",
		));
		$msgs = array();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$msgs[] = $o->properties();
		};
		return $msgs;
	}
	
	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
