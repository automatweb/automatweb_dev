<?php
// crm_skill_level.aw - Oskuse tase
/*

@classinfo syslog_type=ST_CRM_SKILL_LEVEL relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property skill type=relpicker reltype=RELTYPE_SKILL store=connect
@caption Oskus

@property name type=textbox size=4 field=name
@caption Oskuse tase

@reltype SKILL value=1 clid=CL_CRM_SKILL
@caption Oskus


*/

class crm_skill_level extends class_base
{
	function crm_skill_level()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_skill_level",
			"clid" => CL_CRM_SKILL_LEVEL
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
