<?php
// $Header: /home/cvs/automatweb_dev/classes/personalihaldus/Attic/personalihaldus_lang.aw,v 1.2 2004/06/07 13:14:50 sven Exp $
// personalihaldus_lang.aw - Keeleoskus 
/*

@classinfo syslog_type=ST_PERSONALIHALDUS_LANG relationmgr=yes no_status=1

@default table=objects
@default group=general

@property keel type=classificator method=serialize field=meta
@caption Keel

@property tase type=classificator method=serialize field=meta
@caption Tase

*/

class personalihaldus_lang extends class_base
{
	function personalihaldus_lang()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_PERSONALIHALDUS_LANG
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
			case "keel":
				print_r($data);
			break;
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
