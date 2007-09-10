<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/budgeting/budgeting_tax_folder_relation.aw,v 1.1 2007/09/10 10:27:33 kristo Exp $
// budgeting_tax_folder_relation.aw - Eelarvestamise maksu kausta seos 
/*

@classinfo syslog_type=ST_BUDGETING_TAX_FOLDER_RELATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_budgeting_tax_relation master_table=objects master_index=brother_of index=aw_oid

@default table=aw_budgeting_tax_relation
@default group=general

@property tax type=relpicker reltype=RELTYPE_TAX field=aw_tax
@caption Maks

@property folder type=textbox field=aw_folder
@caption Asukoht

@reltype TAX value=1 clid=CL_BUDGETING_TAX
@caption Maks

*/

class budgeting_tax_folder_relation extends class_base
{
	function budgeting_tax_folder_relation()
	{
		$this->init(array(
			"tpldir" => "applications/budgeting/budgeting_tax_folder_relation",
			"clid" => CL_BUDGETING_TAX_FOLDER_RELATION
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

	function do_db_upgrade($t,$f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_budgeting_tax_relation (aw_oid int primary key,aw_tax int, aw_folder varchar(50))");
			return true;
		}
	}
}
?>
