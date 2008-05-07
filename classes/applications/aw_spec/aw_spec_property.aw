<?php
/*
@classinfo syslog_type=ST_AW_SPEC_PROPERTY relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_spec_properties master_index=brother_of master_table=objects index=aw_oid

@default table=aw_spec_properties
@default group=general


@property prop_type type=select field=aw_prop_type
@caption Omaduse t&uuml;&uuml;p

@property prop_desc type=textarea field=aw_prop_desc rows=10 cols=50
@caption Omaduse kirjeldus


*/

class aw_spec_property extends class_base
{
	function aw_spec_property()
	{
		$this->init(array(
			"tpldir" => "applications/aw_spec/aw_spec_property",
			"clid" => CL_AW_SPEC_PROPERTY
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_spec_properties(aw_oid int primary key, aw_prop_type varchar(255), aw_prop_desc text)");
			return true;
		}
	}
}

?>
