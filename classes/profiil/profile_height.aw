<?php
// $Header: /home/cvs/automatweb_dev/classes/profiil/Attic/profile_height.aw,v 1.1 2004/02/09 11:19:36 jaanj Exp $
// profile_height.aw - Height 
/*

@classinfo syslog_type=ST_PROFILE_HEIGHT relationmgr=yes

@default table=objects
@default group=general

@tableinfo aw_profile_height index=id master_table=objects master_index=brother_of

@property meters type=textbox table=aw_profile_height
@caption Sentimeetrites

@property inches type=textbox table=aw_profile_height
@caption Tollides

*/

class profile_height extends class_base
{
	function profile_height()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "profiil/profile_height",
			"clid" => CL_PROFILE_HEIGHT
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
