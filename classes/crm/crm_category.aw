<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_category.aw,v 1.2 2004/06/22 09:19:30 rtoomas Exp $
// crm_category.aw - Kategooria 
/*

@classinfo syslog_type=ST_CRM_CATEGORY relationmgr=yes

@default table=objects
@default group=general

@property img_upload type=releditor reltype=RELTYPE_IMAGE props=file,file_show
@caption Pilt

@reltype IMAGE value=1 clid=CL_IMAGE
@caption Pilt

@reltype CATEGORY value=2 clid=CL_CRM_CATEGORY
@caption Alam kategooria

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY
@caption Klient

*/

class crm_category extends class_base
{
	function crm_category()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "crm/crm_category",
			"clid" => CL_CRM_CATEGORY
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
