<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/commune/Attic/community.aw,v 1.1 2004/06/25 08:51:45 ahti Exp $
// community.aw - Kogukond 
/*

@classinfo syslog_type=ST_COMMUNITY relationmgr=yes

@default table=objects
@default group=general
-------------------------------------
@groupinfo forum caption="Foorum"
//property forum type=releditor rel_id=first reltype=RELTYPE_FORUM group=forum props=name
@property forum type=relpicker reltype=RELTYPE_FORUM field=meta method=serialize table=objects group=forum
@caption Foorum

@groupinfo calendar caption="Kalender"
@property calendar type=relpicker reltype=RELTYPE_CALENDAR field=meta method=serialize table=objects group=calendar
@caption Kalender

-------------------------------------
@reltype FORUM value=1 clid=CL_FORUM_V2
@caption foorum

@reltype CALENDAR value=2 clid=CL_PLANNER
@caption kalender

*/

class community extends class_base
{
	function community()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/commune/community",
			"clid" => CL_COMMUNITY
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
