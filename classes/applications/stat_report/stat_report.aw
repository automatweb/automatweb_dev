<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/stat_report/Attic/stat_report.aw,v 1.3 2004/03/29 15:58:38 duke Exp $
// stat_report.aw - Stati aruannete upload 
/*

@classinfo syslog_type=ST_STAT_REPORT relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property whitelist type=textarea cols=40 rows=20 group=whitelist
@caption Lubatud laiend (iga laiend eraldi real)

@groupinfo whitelist caption=Seadistamine

@reltype REPORT_TYPE value=1 clid=CL_META
@caption Aruannete tyybid




*/

class stat_report extends class_base
{
	function stat_report()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/stat_report/stat_report",
			"clid" => CL_STAT_REPORT
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
