<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/calendar_event.aw,v 1.5 2004/08/26 14:35:27 duke Exp $
// calendar_event.aw - Kalendri sündmus 
/*

@classinfo syslog_type=ST_CALENDAR_EVENT relationmgr=yes

@default table=objects
@default group=general

@property start1 type=datetime_select field=start table=planner
@caption Algab

@property end type=datetime_select field=end table=planner
@caption Lõpeb

@default field=meta
@default method=serialize

@property utextbox1 type=textbox 
@caption

@property utextbox2 type=textbox
@caption 

@property utextbox3 type=textbox
@caption 

@property utextbox4 type=textbox
@caption 

@property utextbox5 type=textbox
@caption 

@property utextbox6 type=textbox
@caption 

@property utextbox7 type=textbox
@caption 

@property utextbox8 type=textbox
@caption 

@property utextbox9 type=textbox
@caption 

@property utextbox10 type=textbox
@caption 

@property utextarea1 type=textarea
@caption 

@property utextarea2 type=textarea
@caption 

@property utextarea3 type=textarea
@caption 

@property utextarea4 type=textarea
@caption 

@property utextarea5 type=textarea
@caption 

@property utextvar1 type=classificator
@caption 

@property utextvar2 type=classificator
@caption 

@property utextvar3 type=classificator
@caption 

@property utextvar4 type=classificator
@caption 

@property utextvar5 type=classificator
@caption 

@property utextvar6 type=classificator
@caption 

@property utextvar7 type=classificator
@caption 

@property utextvar8 type=classificator
@caption 

@property utextvar9 type=classificator
@caption 

@property utextvar10 type=classificator
@caption 

@tableinfo planner index=id master_table=objects master_index=brother_of

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
