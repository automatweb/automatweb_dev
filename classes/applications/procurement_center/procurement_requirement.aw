<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement_requirement.aw,v 1.1 2006/04/27 08:14:37 kristo Exp $
// procurement_requirement.aw - N&otilde;ue 
/*

@classinfo syslog_type=ST_PROCUREMENT_REQUIREMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo procuremnent_requirements index=aw_oid master_index=brother_of master_table=objects

@default table=objects
@default group=general

	@property desc type=textarea rows=20 cols=50 table=procuremnent_requirements field=aw_desc
	@caption Kirjeldus
*/

class procurement_requirement extends class_base
{
	function procurement_requirement()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/procurement_requirement",
			"clid" => CL_PROCUREMENT_REQUIREMENT
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
		if ($f == "" && $t == "procuremnent_requirements")
		{
			$this->db_query("CREATE TABLE procuremnent_requirements (aw_oid int primary key, aw_desc text)");
			return true;
		}
	}
}
?>
