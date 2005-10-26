<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_deal.aw,v 1.10 2005/10/26 20:02:11 kristo Exp $
// crm_deal.aw - Tehing 
/*

@classinfo syslog_type=ST_CRM_DEAL relationmgr=yes

@default table=objects
@default group=general

@tableinfo aw_crm_deal index=aw_oid master_index=brother_of master_table=objects


@default group=general

	@property project type=popup_search clid=CL_PROJECT table=aw_crm_deal field=aw_project
	@caption Projekt

	@property task type=popup_search clid=CL_TASK table=aw_crm_deal field=aw_task
	@caption &Uuml;lesanne

	@property customer type=popup_search clid=CL_CRM_COMPANY table=aw_crm_deal field=aw_customer
	@caption Klient

	@property creator type=relpicker reltype=RELTYPE_CREATOR table=aw_crm_deal field=aw_creator
	@caption Koostaja

	@property reader type=relpicker reltype=RELTYPE_READER table=aw_crm_deal field=aw_reader
	@caption Lugeja

	@property reg_date type=date_select table=aw_crm_deal field=aw_reg_date
	@caption Reg kuup&auml;ev

	@property comment type=textarea rows=5 cols=50 table=objects field=comment
	@caption Kirjeldus

@default group=files

	@property files type=releditor reltype=RELTYPE_FILE field=meta method=serialize mode=manager props=name,file,type,comment,file_url,newwindow table_fields=name 
	@caption Failid

@groupinfo files caption="Failid"

@reltype FILE value=1 clid=CL_FILE
@caption fail
*/

class crm_deal extends class_base
{
	function crm_deal()
	{
		$this->init(array(
			"clid" => CL_CRM_DEAL
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "creator":
			case "reader":
				$u = get_instance("users");
				$ui = get_instance(CL_USER);
				$c_uid = $arr["obj_inst"]->createdby();
				if ($c_uid != "")
				{
					$ps = obj($ui->get_person_for_user(obj($u->get_oid_for_uid($c_uid))));
					$co = obj($ui->get_company_for_person($ps));
				}
				else
				{
					$co = obj($ui->get_current_company());
					$ps = obj($ui->get_current_person());
				}

				$c = get_instance(CL_CRM_COMPANY);
				$prop["options"] = $c->get_employee_picker($co);
	
				if ($prop["value"] == "" && $ps)
				{
					$prop["value"] = $ps->id();
				}
				break;

			case "project":
				$i = get_instance(CL_CRM_COMPANY);
				$prj = $i->get_my_projects();
				if (!count($prj))
				{
					$prop["options"] = array("" => "");
				}
				else
				{
					$ol = new object_list(array("oid" => $prj));
					$prop["options"] = array("" => "") + $ol->names();
				}
				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}
				asort($prop["options"]);
				break;

			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$cst = $i->get_my_customers();
				if (!count($cst))
				{
					$prop["options"] = array("" => "");
				}
				else
				{
					$ol = new object_list(array("oid" => $cst));
					$prop["options"] = array("" => "") + $ol->names();
				}
				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}
				asort($prop["options"]);
				break;

			case "task":
				$i = get_instance(CL_CRM_COMPANY);
				$tsk = $i->get_my_tasks();
				if (!count($tsk))
				{
					$prop["options"] = array("" => "");
				}
				else
				{
					$ol = new object_list(array("oid" => $tsk));
					$prop["options"] = array("" => "") + $ol->names();
				}
				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}
				asort($prop["options"]);
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
