<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/calendar_event.aw,v 1.1 2004/08/25 08:56:58 duke Exp $
// calendar_event.aw - Kalendri sündmus 
/*

@classinfo syslog_type=ST_CALENDAR_EVENT relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property utextbox1 type=textbox
@caption Utext1

@property utextbox2 type=textbox
@caption Utext2

@property utextbox3 type=textbox
@caption Utext3

@property utextbox4 type=textbox
@caption Utext4

@property utextbox5 type=textbox
@caption Utext5

@property utextbox6 type=textbox
@caption Utext6

@property utextbox7 type=textbox
@caption Utext7

@property utextbox8 type=textbox
@caption Utext8

@property utextarea1 type=textarea
@caption Utextarea1

*/

class calendar_event extends class_base
{
	function calendar_event()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/calendar_event",
			"clid" => CL_CALENDAR_EVENT
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}
	*/

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
