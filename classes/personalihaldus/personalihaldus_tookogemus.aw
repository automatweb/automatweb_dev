<?php
// $Header: /home/cvs/automatweb_dev/classes/personalihaldus/Attic/personalihaldus_tookogemus.aw,v 1.1 2004/03/16 14:07:00 sven Exp $
// personalihaldus_tookogemus.aw - Töökogemus 
/*

@classinfo syslog_type=ST_PERSONALIHALDUS_TOOKOGEMUS relationmgr=yes

@default table=objects
@default group=general
@default method=serialize
@default field=meta

@property asutus type=textbox 
@caption Asutus

@property algus type=date_select
@caption Alates

@property kuni type=date_select
@caption Kuni

@property ametikoht type=textbox
@caption Ametikoht

@property tasks type=textarea
@caption T&ouml;&ouml;&uuml;lesanded

*/

class personalihaldus_tookogemus extends class_base
{
	function personalihaldus_tookogemus()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "personalihaldus/personalihaldus_tookogemus",
			"clid" => CL_PERSONALIHALDUS_TOOKOGEMUS
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}
	*/

	/*
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
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
