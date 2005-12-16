<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_company_customer_data.aw,v 1.1 2005/12/16 11:04:41 kristo Exp $
// crm_company_customer_data.aw - Kliendi andmed 
/*

@classinfo syslog_type=ST_CRM_COMPANY_CUSTOMER_DATA relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_crm_customer_data index=aw_oid master_index=brother_of master_table=objects

@default table=objects


@default group=general

	@property buyer type=relpicker reltype=RELTYPE_BUYER table=aw_crm_customer_data field=aw_buyer
	@caption Ostja

	@property seller type=relpicker reltype=RELTYPE_SELLER table=aw_crm_customer_data field=aw_seller
	@caption M&uuml;&uuml;ja

	@property cust_contract_creator type=select table=aw_crm_customer_data field=aw_cust_contract_creator
	@caption Kliendisuhte looja

	@property cust_contract_date type=date_select table=aw_crm_customer_data field=aw_cust_contract_date
	@caption Kliendisuhte alguskuup&auml;ev

	@property referal_type type=classificator table=aw_crm_customer_data field=aw_referal_type reltype=RELTYPE_REFERAL_TYPE
	@caption Sissetuleku meetod

	@property contact_person type=relpicker reltype=RELTYPE_CONTACT_PERSON table=aw_crm_customer_data field=aw_contact_person1
	@caption Kontaktisik 1

	@property contact_person2 type=relpicker reltype=RELTYPE_CONTACT_PERSON table=aw_crm_customer_data field=aw_contact_person2
	@caption Kontaktisik 2

	@property contact_person3 type=relpicker reltype=RELTYPE_CONTACT_PERSON table=aw_crm_customer_data field=aw_contact_person3
	@caption Kontaktisik 3

	@property priority type=textbox table=aw_crm_customer_data field=aw_priority
	@caption Prioriteet

	@property client_manager type=relpicker reltype=RELTYPE_CLIENT_MANAGER table=aw_crm_customer_data field=aw_client_manager
	@caption Kliendihaldur

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
}
?>
