<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_resource_operator.aw,v 1.3 2008/01/31 13:54:53 kristo Exp $
// mrp_resource_operator.aw - Operaator 
/*

@classinfo syslog_type=ST_MRP_RESOURCE_OPERATOR relationmgr=yes no_comment=1 no_status=1 maintainer=voldemar

@default table=objects
@default group=general

@tableinfo aw_mrp_resource_operator index=id master_table=objects master_index=brother_of
@default table=aw_mrp_resource_operator

@property profession type=relpicker reltype=RELTYPE_PROFESSION 
@caption Ametinimetus

@property resource type=relpicker reltype=RELTYPE_RESOURCE
@caption Resurss

@property unit type=relpicker reltype=RELTYPE_UNIT
@caption &Uuml;ksus

@reltype PROFESSION value=1 clid=CL_CRM_PROFESSION
@caption ametinimetus

@reltype RESOURCE value=2 clid=CL_MRP_RESOURCE
@caption resurss

@reltype UNIT value=3 clid=CL_CRM_SECTION
@caption osakond

*/

class mrp_resource_operator extends class_base
{
	function mrp_resource_operator()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "mrp/mrp_resource_operator",
			"clid" => CL_MRP_RESOURCE_OPERATOR
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
