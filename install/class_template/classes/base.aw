<?php
// $Header: /home/cvs/automatweb_dev/install/class_template/classes/base.aw,v 1.12 2003/07/25 15:46:21 duke Exp $
// __classname.aw - __name 
/*

@classinfo syslog_type=__syslog_type relatiomgr=yes

@default table=objects
@default group=general

*/

class __classname extends class_base
{
	function __classname()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "__tplfolder",
			"clid" => __classdef
		));
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		return $this->parse();
	}
}
?>
