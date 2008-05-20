<?php
/*
@classinfo syslog_type=ST_EURO_PATENT_ET_DESC relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@extends applications/clients/patent_office/intellectual_property
@tableinfo aw_euro_patent_et_desc master_index=brother_of master_table=objects index=aw_oid

@default table=aw_euro_patent_et_desc
@default group=general

*/

class euro_patent_et_desc extends class_base
{
	function euro_patent_et_desc()
	{
		$this->init(array(
			"tpldir" => "applications/clients/patent_office/euro_patent_et_desc",
			"clid" => CL_EURO_PATENT_ET_DESC
		));
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_euro_patent_et_desc(aw_oid int primary_key)");
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
