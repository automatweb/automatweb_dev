<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_category.aw,v 1.5 2005/01/13 19:47:15 kristo Exp $
// crm_category.aw - Kategooria 
/*

@classinfo syslog_type=ST_CRM_CATEGORY relationmgr=yes

@default table=objects
@default group=general

@property img_upload type=releditor reltype=RELTYPE_IMAGE props=file,file_show
@caption Pilt

@property extern_id type=hidden field=meta method=serialize 

//@property jrk type=textbox size=4
//@caption Järk

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
		$this->init(array(
			"tpldir" => "crm/crm_category",
			"clid" => CL_CRM_CATEGORY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

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
