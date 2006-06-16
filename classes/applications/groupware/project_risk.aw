<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/project_risk.aw,v 1.1 2006/06/16 11:23:14 kristo Exp $
// project_risk.aw - Projekti risk 
/*

@classinfo syslog_type=ST_PROJECT_RISK relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo aw_project_risks index=aw_oid master_index=brother_of master_table=objects
@default group=general

	@property owner type=relpicker reltype=RELTYPE_OWNER table=aw_project_risks field=aw_owner
	@caption Omanik

	@property countermeasure type=textarea rows=5 cols=30 table=aw_project_risks field=aw_countermeasure
	@caption Vastumeede

@reltype OWNER value=1 clid=CL_CRM_PERSON
@caption Omanik
*/

class project_risk extends class_base
{
	function project_risk()
	{
		$this->init(array(
			"tpldir" => "applications/groupware/project_risk",
			"clid" => CL_PROJECT_RISK
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "owner":
				if (!$this->can("view", $prop["value"]))
				{
					$cp = get_current_person();
					$prop["value"] = $cp->id();
					$prop["options"][$cp->id()] = $cp->name();
				}
				break;
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_project_risks(aw_oid int primary key, aw_owner int, aw_countermeasure text)");
			return true;
		}
	}
}
?>
