<?php
// crm_recommendation.aw - Soovitus
/*

@classinfo syslog_type=ST_CRM_RECOMMENDATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property person type=relpicker reltype=RELTYPE_PERSON store=connect field=meta method=serialize
@caption Soovitav isik

@property jobwish type=relpicker reltype=RELTYPE_JOBWISH store=connect field=meta method=serialize
@caption Soovitatav t&ouml;&ouml;

@reltype JOBWISH value=1 clid=CL_PERSONNEL_MANAGEMENT_JOB_WANTED
@caption Soovitatav t&ouml;&ouml;

@reltype PERSON value=2 clid=CL_CRM_PERSON
@caption Soovitav isik

*/

class crm_recommendation extends class_base
{
	function crm_recommendation()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_recommendation",
			"clid" => CL_CRM_RECOMMENDATION
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
