<?php
/*
@classinfo syslog_type=ST_POST_OFFICE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=smeedia
@tableinfo aw_post_office master_index=brother_of master_table=objects index=aw_oid

@default table=aw_post_office
@default group=general

	@property ord type=textbox table=objects field=jrk
	@caption Jrk

	@property county type=relpicker reltype=RELTYPE_COUNTY field=aw_county
	@caption Maakond

@reltype COUNTY value=1 clid=CL_CRM_COUNTY
@caption Maakond

*/

class post_office extends class_base
{
	function post_office()
	{
		$this->init(array(
			"tpldir" => "import/post_office",
			"clid" => CL_POST_OFFICE
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
			$this->db_query("CREATE TABLE aw_post_office(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_county":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}

?>
