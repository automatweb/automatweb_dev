<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgcontroller.aw,v 1.5 2005/01/21 13:17:48 duke Exp $
// cfgcontroller.aw - Kontroller(Classbase) 
/*

@classinfo syslog_type=ST_CFGCONTROLLER relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property formula type=textarea rows=20 cols=80
@caption Valem

@property errmsg type=textbox
@caption Veateade
@comment Kuvatakse, kui kontroller blokeerib sisestuse

property show_error type=checkbox ch_value=1
caption Kas näitamise kontroller näitab elemendi asemel veateadet? 

property only_warn type=checkbox ch_value=1
caption Ainult hoiatus

property error_in_popup type=checkbox ch_value=1
caption Veateade popupis 
*/

class cfgcontroller extends class_base
{
	function cfgcontroller()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "cfg/cfgcontroller",
			"clid" => CL_CFGCONTROLLER
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			
		};
		return $retval;
	}
	
	function check_property($controller_oid, $obj_id, &$prop, $request, $entry, $obj_inst)
	{
		$retval = PROP_OK;
		$controller_inst = &obj($controller_oid);
		eval($controller_inst->prop("formula"));
		return $retval;
	}
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
