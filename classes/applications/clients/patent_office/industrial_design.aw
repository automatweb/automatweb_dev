<?php
/*
@classinfo syslog_type=ST_INDUSTRIAL_DESIGN relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@extends applications/clients/patent_office/intellectual_property
@tableinfo aw_industrial_design master_index=brother_of master_table=objects index=aw_oid

@default table=aw_industrial_design
@default group=general

*/

class industrial_design extends class_base
{
	function industrial_design()
	{
		$this->init(array(
			"tpldir" => "applications/clients/patent_office/industrial_design",
			"clid" => CL_INDUSTRIAL_DESIGN
		));
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_industrial_design(aw_oid int primary_key)");
			return true;
		}

		switch($f)
		{
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}
}

?>
