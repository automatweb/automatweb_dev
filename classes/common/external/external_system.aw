<?php
// $Header: /home/cvs/automatweb_dev/classes/common/external/external_system.aw,v 1.1 2006/05/30 14:34:42 kristo Exp $
// external_system.aw - Siduss&uuml;steem 
/*

@classinfo syslog_type=ST_EXTERNAL_SYSTEM relationmgr=yes no_comment=1 prop_cb=1

@tableinfo aw_ext_systems index=aw_oid master_table=objects master_index=brother_of

@default table=aw_ext_systems
@default group=general

	@property ord type=textbox size=5 table=objects field=jrk
	@caption J&auml;rjekord

	@property apply_class type=select field=aw_apply_class
	@caption Klass, millele kehtib
*/

class external_system extends class_base
{
	function external_system()
	{
		$this->init(array(
			"tpldir" => "common/external/external_system",
			"clid" => CL_EXTERNAL_SYSTEM
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "apply_class":
				$prop["options"] = get_class_picker();
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
			$this->db_query("CREATE TABLE aw_ext_systems (aw_oid int primary key, aw_apply_class int)");
			return true;
		}
	}
}
?>
