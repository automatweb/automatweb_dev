<?php
// crm_skill.aw - Skill
/*

@classinfo syslog_type=ST_CRM_SKILL relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property subheading type=checkbox cb_value=1 field=meta method=serialize
@caption Vahepealkiri
@comment Kui oskus on vahepealkiri, ei saa teda siduda isikuga ega m&auml;&auml;rata tema taset.

@property lvl type=checkbox ch_value=1 field=meta method=serialize
@property Saab m&auml;&auml;rata taset

@property lvl_meta type=relpicker reltype=RELTYPE_LEVELS field=meta method=serialize
@property Tasemed

@reltype LEVELS value=1 clid=CL_META
@caption Tasemed

*/

class crm_skill extends class_base
{
	function crm_skill()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_skill",
			"clid" => CL_CRM_SKILL
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
