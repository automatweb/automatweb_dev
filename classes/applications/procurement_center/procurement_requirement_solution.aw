<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement_requirement_solution.aw,v 1.1 2006/05/18 11:19:09 kristo Exp $
// procurement_requirement_solution.aw - N&otilde;ude lahendus 
/*

@classinfo syslog_type=ST_PROCUREMENT_REQUIREMENT_SOLUTION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=aw_procurement_requirement_solution
@tableinfo aw_procurement_requirement_solution index=aw_oid master_index=brother_of mater_table=objects

@default group=general

	@property readyness type=select field=aw_readyness
	@caption Valmidus

	@property price type=textbox size=5 field=aw_price
	@caption Hind

	@property time_to_install type=textbox size=5 field=aw_time_to_install
	@caption Seadistamise aeg

	@property solution type=textarea rows=5 cols=40 field=aw_solution
	@caption Lahendus

	@property offerer_co type=relpicker reltype=RELTYPE_CO field=aw_offerer_co
	@caption Pakkuja organisatsioon

	@property offerer_p type=relpicker reltype=RELTYPE_P field=aw_offerer_p
	@caption Pakkuja isik

@reltype CO value=1 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype P value=2 clid=CL_CRM_PERSON
@caption Isik

*/

define("PO_IN_BASE", 1);
define("PO_NEEDS_INSTALL", 2);
define("PO_NEEDS_DEVELOPMENT", 3);

class procurement_requirement_solution extends class_base
{
	function procurement_requirement_solution()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/procurement_requirement_solution",
			"clid" => CL_PROCUREMENT_REQUIREMENT_SOLUTION
		));

		$this->readyness_states = array(
			PO_IN_BASE => t("Kohe olemas"),
			PO_NEEDS_INSTALL => t("Vajab seadistamist"),
			PO_NEEDS_DEVELOPMENT => t("Uus arendus")
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "offerer_co":
				if (!$prop["value"])
				{
					$cc = get_current_company();
					$prop["value"] = $cc->id();
				}
				if (!isset($prop["options"][$prop["value"]]) && $prop["value"])
				{
					$po = obj($prop["value"]);
					$prop["options"][$prop["value"]] = $po->name();
				}
				break;

			case "offerer_p":
				if (!$prop["value"])
				{
					$cc = get_current_person();
					$prop["value"] = $cc->id();
				}
				if (!isset($prop["options"][$prop["value"]]) && $prop["value"])
				{
					$po = obj($prop["value"]);
					$prop["options"][$prop["value"]] = $po->name();
				}
				break;

			case "readyness":
				$prop["options"] = $this->readyness_states;
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
			$this->db_query("
				CREATE TABLE aw_procurement_requirement_solution (aw_oid int primary key, aw_readyness int, aw_price double, 
				aw_time_to_install double, aw_solution text)
			");
			return true;
		}

		switch($f)
		{
			case "aw_offerer_co":
			case "aw_offerer_p":
				$this->db_add_col($t, array("name" => $f, "type" => "int"));
				return true;
		}
	}
}
?>
