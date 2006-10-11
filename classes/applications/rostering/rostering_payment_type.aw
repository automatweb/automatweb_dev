<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/rostering/rostering_payment_type.aw,v 1.1 2006/10/11 13:06:42 kristo Exp $
// rostering_payment_type.aw - Tasu liik 
/*

@classinfo syslog_type=ST_ROSTERING_PAYMENT_TYPE relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo aw_rostering_payment_type index=aw_oid master_index=brother_of master_table=objects
@default table=aw_rostering_payment_type
@default group=general

	@property hr_price type=textbox size=10 field=aw_hr_price
	@caption Tunnihind
*/

class rostering_payment_type extends class_base
{
	function rostering_payment_type()
	{
		$this->init(array(
			"tpldir" => "applications/rostering/rostering_payment_type",
			"clid" => CL_ROSTERING_PAYMENT_TYPE
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
			$this->db_query("CREATE TABLE aw_rostering_payment_type (aw_oid int primary key, aw_hr_price double)");
			return true;
		}
	}
}
?>
