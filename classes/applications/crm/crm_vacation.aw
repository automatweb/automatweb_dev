<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_vacation.aw,v 1.1 2005/01/18 15:39:40 duke Exp $
// crm_vacation.aw - Puhkus 
/*

@classinfo syslog_type=ST_CRM_VACATION relationmgr=yes

@default group=general

@default table=planner

@property start1 type=datetime_select field=start 
@caption Algab

@property end type=datetime_select field=end 
@caption Lõpeb

@default table=objects
@default field=meta
@default method=serialize

@property duration_days type=textbox 
@caption Kestvus päevades

@property type type=relpicker reltype=RELTYPE_VACATION_TYPE automatic=1
@caption Puhkuse tüüp

@reltype VACATION_TYPE value=1 clid=CL_META
@caption Puhkuse tüüp

*/

class crm_vacation extends class_base
{
	function crm_vacation()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/crm/crm_vacation",
			"clid" => CL_CRM_VACATION
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
