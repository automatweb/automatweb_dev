<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_company_customer_data.aw,v 1.9 2007/06/18 14:02:53 voldemar Exp $
// crm_company_customer_data.aw - Kliendi andmed
/*

@classinfo syslog_type=ST_CRM_COMPANY_CUSTOMER_DATA relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_crm_customer_data index=aw_oid master_index=brother_of master_table=objects

@default table=objects

default method=serialize

@default group=general

	@property buyer type=relpicker reltype=RELTYPE_BUYER table=aw_crm_customer_data field=aw_buyer
	@caption Ostja

	@property seller type=relpicker reltype=RELTYPE_SELLER table=aw_crm_customer_data field=aw_seller
	@caption M&uuml;&uuml;ja

@groupinfo buyer caption="Ostja"
@default group=buyer


	@property buyer_contract_creator type=select table=aw_crm_customer_data field=aw_buyer_cust_contract_creator
	@caption Hankijasuhte looja

	@property buyer_contract_date type=date_select table=aw_crm_customer_data field=aw_buyer_cust_contract_date
	@caption Hankijasuhte alguskuup&auml;ev

	@property buyer_contact_person type=relpicker reltype=RELTYPE_CONTACT_PERSON table=aw_crm_customer_data field=aw_buyer_contact_person1
	@caption Ostja kontaktisik 1

	@property buyer_contact_person2 type=relpicker reltype=RELTYPE_CONTACT_PERSON table=aw_crm_customer_data field=aw_buyer_contact_person2
	@caption Ostja kontaktisik 2

	@property buyer_contact_person3 type=relpicker reltype=RELTYPE_CONTACT_PERSON table=aw_crm_customer_data field=aw_buyer_contact_person3
	@caption Ostja kontaktisik 3

	@property buyer_priority type=textbox table=aw_crm_customer_data field=aw_buyer_priority
	@caption Ostja Prioriteet


@groupinfo seller caption="M&uuml;&uuml;ja"
@default group=seller

	@property cust_contract_creator type=select table=aw_crm_customer_data field=aw_cust_contract_creator
	@caption Kliendisuhte looja

	@property cust_contract_date type=date_select table=aw_crm_customer_data field=aw_cust_contract_date
	@caption Kliendisuhte alguskuup&auml;ev

	@property contact_person type=relpicker reltype=RELTYPE_CONTACT_PERSON table=aw_crm_customer_data field=aw_contact_person1
	@caption Kliendi kontaktisik 1

	@property contact_person2 type=relpicker reltype=RELTYPE_CONTACT_PERSON table=aw_crm_customer_data field=aw_contact_person2
	@caption Kliendi kontaktisik 2

	@property contact_person3 type=relpicker reltype=RELTYPE_CONTACT_PERSON table=aw_crm_customer_data field=aw_contact_person3
	@caption Kliendi kontaktisik 3

	@property priority type=textbox table=aw_crm_customer_data field=aw_priority
	@caption Kliendi Prioriteet

	@property referal_type type=classificator table=aw_crm_customer_data field=aw_referal_type reltype=RELTYPE_REFERAL_TYPE
	@caption Sissetuleku meetod

	@property client_manager type=relpicker reltype=RELTYPE_CLIENT_MANAGER table=aw_crm_customer_data field=aw_client_manager
	@caption Kliendihaldur

	@property bill_due_date_days type=textbox size=5  table=aw_crm_customer_data field=aw_bill_due_date_days
	@caption Makset&auml;htaeg (p&auml;evi)

	@property bill_penalty_pct type=textbox size=5  table=aw_crm_customer_data field=aw_bill_penalty_pct
	@caption Viivise %


@reltype BUYER value=1 clid=CL_CRM_COMPANY
@caption Ostja

@reltype SELLER value=2 clid=CL_CRM_COMPANY
@caption M&uuml;&uuml;ja

@reltype CONTACT_PERSON value=3 clid=CL_CRM_PERSON
@caption Kontaktisik

@reltype REFERAL_TYPE value=41 clid=CL_META
@caption Sissetuleku meetod

@reltype CLIENT_MANAGER value=34 clid=CL_CRM_PERSON
@caption Kliendihaldur

@reltype EXT_SYS_ENTRY value=35 clid=CL_EXTERNAL_SYSTEM_ENTRY
@caption Siduss&uuml;steemi sisestus
*/

class crm_company_customer_data extends class_base
{
	function crm_company_customer_data()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_company_customer_data",
			"clid" => CL_CRM_COMPANY_CUSTOMER_DATA
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "cust_contract_creator":
				// list of all persons in my company
				$u = get_instance(CL_USER);
				$co = $u->get_current_company();
				$i = get_instance(CL_CRM_COMPANY);
				$arr["prop"]["options"] = $i->get_employee_picker(obj($co), true);
				break;

			case "referal_type":
				$c = get_instance("cfg/classificator");
				$prop["options"] = array("" => t("--vali--")) + $c->get_options_for(array(
					"name" => "referal_type",
					"clid" => CL_CRM_COMPANY
				));
				break;

			case "contact_person":
			case "contact_person2":
			case "contact_person3":
				if (!$this->can("view", $arr["obj_inst"]->prop("buyer")))
				{
					return PROP_IGNORE;
				}
				$i = get_instance(CL_CRM_COMPANY);
				$arr["prop"]["options"] = $i->get_employee_picker(obj($arr["obj_inst"]->prop("buyer")), true);

				if (isset($prop["options"]) && !isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$prop["value"]] = $tmp->name();
				}
				break;

			case "client_manager":
				$i = get_instance(CL_CRM_COMPANY);
				$u = get_instance(CL_USER);
				$prop["options"] = $i->get_employee_picker(obj($u->get_current_company()), true);
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

	function do_db_upgrade($tbl, $fld, $q, $err)
	{
		switch($fld)
		{
			case "":
				$this->db_query("CREATE TABLE `aw_crm_customer_data` (
				  `aw_oid` int(11) NOT NULL default '0',
				  `aw_buyer` int(11) default NULL,
				  `aw_seller` int(11) default NULL,
				  `aw_cust_contract_creator` int(11) default NULL,
				  `aw_cust_contract_date` int(11) default NULL,
				  `aw_contact_person1` int(11) default NULL,
				  `aw_contact_person2` int(11) default NULL,
				  `aw_contact_person3` int(11) default NULL,
				  `aw_priority` int(11) default NULL,
				  `aw_client_manager` int(11) default NULL,
				  `aw_referal_type` int(11) default NULL,
				  PRIMARY KEY  (`aw_oid`)
				) ");
				return true;

			case "aw_bill_due_date_days":
			case "aw_buyer_cust_contract_creator":
			case "aw_buyer_cust_contract_date":
			case "aw_buyer_contact_person1":
			case "aw_buyer_contact_person2":
			case "aw_buyer_contact_person3":
			case "aw_buyer_priority":
				$this->db_add_col($tbl, array(
					"name" => $fld,
					"type" => "int"
				));
				return true;

			case "aw_bill_penalty_pct":
				$this->db_add_col($tbl, array(
					"name" => $fld,
					"type" => "double"
				));
				return true;
		}
	}
}
?>
