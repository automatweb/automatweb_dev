<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/quickmessage/quickmessage.aw,v 1.4 2004/08/17 11:17:48 ahti Exp $
// quickmessage.aw - Kiirteade 
/*

@classinfo syslog_type=ST_QUICKMESSAGE relationmgr=yes
@tableinfo quickmessages index=id master_table=objects master_index=brother_of

@default table=quickmessages
@default group=general

@groupinfo message caption="Uus teade"
@default group=message
//@default form=message2 parent=message

@property user_from type=text
@caption Kellelt

@property user_to type=textbox
@caption Kellele

@property subject type=textbox
@caption Teema

@property content type=textarea
@caption Sisu

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

class quickmessage extends class_base
{
	function quickmessage()
	{
		$this->init(array(
			"clid" => CL_QUICKMESSAGE
		));
	}

	// now - I need to embed this thing into my commune object
	// and I also need an additonal view to show the messages I have sent
	// and I also need an additonal view to show the messages that I have received

	// 1. create a new message
	// 2. send it away

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "user_from":
				$prop["value"] = aw_global_get("uid");
				break;
			case "user_to":
				$prop["value"] = $arr["request"]["user"];
				break;
			case "name":
				$retval = PROP_IGNORE;
				break;

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
