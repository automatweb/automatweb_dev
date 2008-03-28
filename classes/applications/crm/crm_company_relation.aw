<?php
// crm_company_relation.aw - Organisatoorne kuuluvus
/*

@classinfo syslog_type=ST_CRM_COMPANY_RELATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo kliendibaas_organisatoorne_kuuluvus master_index=oid master_table=objects index=oid

@default table=objects
@default group=general

@property org type=relpicker reltype=RELTYPE_COMPANY store=connect
@caption Organisatsioon

@property start type=date_select year_from=1950 save_format=iso8601 field=rel_start table=kliendibaas_organisatoorne_kuuluvus
@caption Algus

@property end type=date_select year_from=1950 save_format=iso8601 field=rel_end table=kliendibaas_organisatoorne_kuuluvus
@caption L&otilde;pp

@property add_info type=textarea field=comment
@caption Lisainfo

@reltype COMPANY value=1 clid=CL_CRM_COMPANY
@caption Organisatsioon

*/

class crm_company_relation extends class_base
{
	function crm_company_relation()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_company_relation",
			"clid" => CL_CRM_COMPANY_RELATION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

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

	function do_db_upgrade($tbl, $field, $q, $err)
	{
		if ($tbl == "kliendibaas_organisatoorne_kuuluvus" && $field == "")
		{
			$this->db_query("create table kliendibaas_organisatoorne_kuuluvus (oid int primary key)");
			return true;
		}

		switch($field)
		{
			case "rel_end":
			case "rel_start":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "date"
				));
				return true;
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}

?>
