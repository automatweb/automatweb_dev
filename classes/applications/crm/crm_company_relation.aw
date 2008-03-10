<?php
// crm_company_relation.aw - Organisatoorne kuuluvus
/*

@classinfo syslog_type=ST_CRM_COMPANY_RELATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property org type=relpicker reltype=RELTYPE_COMPANY store=connect
@caption Organisatsioon

@reltype COMPANY value=1 clid=CL_CRM_COMPANY
@caption Organisatsioon

*/

class crm_company_relation extends class_base
{
	function crm_company_relation()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_company_relation",
			"clid" => CL_CRM_COMPANY_RELATION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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
