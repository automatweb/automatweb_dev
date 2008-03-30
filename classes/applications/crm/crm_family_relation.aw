<?php
// crm_family_relation.aw - Sugulusside
/*

@classinfo syslog_type=ST_CRM_FAMILY_RELATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo kliendibaas_sugulusside index=oid master_table=objects master_index=oid

@default table=kliendibaas_sugulusside
@default group=general

@property person type=relpicker reltype=RELTYPE_PERSON store=connect
@caption Isik

@property relation_type type=select
@caption Sugulussideme t&uuml;&uuml;p

@property start type=date_select field=fr_start
@caption Algus

@property end type=date_select field=fr_end
@caption L&otilde;pp

@reltype PERSON value=1 clid=CL_CRM_PERSON
@caption Isik

*/

class crm_family_relation extends class_base
{
	function crm_family_relation()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_family_relation",
			"clid" => CL_CRM_FAMILY_RELATION
		));
		$this->relation_type_options = array(
			0 => t("Abikaasa"),
			1 => t("Laps"),
			2 => t("Vanem"),
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "relation_type":
				$prop["options"] = $this->relation_type_options;
				break;
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
	function do_db_upgrade($tbl, $field, $q, $err)
	{
		if ($tbl == "kliendibaas_sugulusside" && $field == "")
		{
			$this->db_query("create table kliendibaas_sugulusside (oid int primary key)");
			return true;
		}

		switch($field)
		{
			case "relation_type":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "int"
				));
				return true;
			
			case "fr_start":
			case "fr_end":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "varchar(20)"
				));
				return true;
		}
		return false;
	}
}

?>
