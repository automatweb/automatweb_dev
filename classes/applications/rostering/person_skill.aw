<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/rostering/person_skill.aw,v 1.1 2006/09/14 09:11:38 kristo Exp $
// person_skill.aw - Oskus 
/*

@classinfo syslog_type=ST_PERSON_SKILL relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_person_skill master_table=objects master_index=brother_of index=aw_oid

@default table=objects
@default group=general

	@property hrs_per_week_to_keep type=textbox size=5 table=aw_person_skill field=aw_hrs_per_week_to_keep
	@caption Tunde n&auml;dalas, mis on p&auml;devuse hoidmiseks vajalikud

*/

class person_skill extends class_base
{
	function person_skill()
	{
		$this->init(array(
			"tpldir" => "applications/rostering/person_skill",
			"clid" => CL_PERSON_SKILL
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_person_skill (aw_oid int primary key, aw_hrs_per_week_to_keep int)");
			return true;
		}
	}
}
?>
