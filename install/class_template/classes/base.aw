<?php
// $Header: /home/cvs/automatweb_dev/install/class_template/classes/base.aw,v 1.17 2005/03/07 16:00:31 kristo Exp $
// __classname.aw - __name 
/*

@classinfo syslog_type=__syslog_type relationmgr=yes

@default table=objects
@default group=general

*/

class __classname extends class_base
{
	function __classname()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "__tplfolder",
			"clid" => __classdef
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
/* get_property */
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
/* set_property */
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["return_url"] = post_ru();
	}

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

/* methods */
}
?>
