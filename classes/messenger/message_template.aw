<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/message_template.aw,v 1.2 2004/06/11 16:24:48 duke Exp $
// message_template.aw - Kirja template 
/*

@classinfo syslog_type=ST_MESSAGE_TEMPLATE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@classinfo no_comment=1

@property is_html type=checkbox ch_value=1
@caption HTML

@property content type=textarea cols=50 rows=20
@caption Sisu


*/

class message_template extends class_base
{
	function message_template()
	{
		$this->init(array(
			"tpldir" => "messenger/message_template",
			"clid" => CL_MESSAGE_TEMPLATE
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
