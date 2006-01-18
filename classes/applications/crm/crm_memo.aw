<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_memo.aw,v 1.8 2006/01/18 18:09:07 kristo Exp $
// crm_memo.aw - Memo 
/*

@classinfo syslog_type=ST_CRM_MEMO relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@tableinfo aw_crm_memo index=aw_oid master_index=brother_of master_table=objects


@default group=general

	@property project type=popup_search clid=CL_PROJECT table=aw_crm_memo field=aw_project
	@caption Projekt

	@property task type=popup_search clid=CL_TASK table=aw_crm_memo field=aw_task
	@caption &Uuml;lesanne

	@property customer type=popup_search clid=CL_CRM_COMPANY table=aw_crm_memo field=aw_customer
	@caption Klient

	@property creator type=popup_search style=relpicker reltype=RELTYPE_CREATOR table=aw_crm_memo field=aw_creator
	@caption Koostaja

	@property reader type=popup_search style=relpicker reltype=RELTYPE_READER table=aw_crm_memo field=aw_reader
	@caption Lugeja

	@property reg_date type=date_select table=aw_crm_memo field=aw_reg_date
	@caption Reg kuup&auml;ev

	@property comment type=textarea rows=5 cols=50 table=objects field=comment
	@caption Kirjeldus

@default group=files

	@property files type=releditor reltype=RELTYPE_FILE field=meta method=serialize mode=manager props=name,file,type,comment,file_url,newwindow table_fields=name 
	@caption Failid

@groupinfo files caption="Failid"

@reltype FILE value=1 clid=CL_FILE
@caption fail

@reltype CREATOR value=2 clid=CL_CRM_PERSON
@caption looja

@reltype READER value=3 clid=CL_CRM_PERSON
@caption lugeja

*/

class crm_memo extends class_base
{
	function crm_memo()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_memo",
			"clid" => CL_CRM_MEMO
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
				$myp = $i->get_my_projects();
				if (!count($myp))
				{
					$ol = new object_list();
				}
				else
				{
					$ol = new object_list(array("oid" => $myp));
				}
				$prop["options"] = array("" => "") + $ol->names();
				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}
				asort($prop["options"]);
				break;

			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$ol = new object_list(array("oid" => $i->get_my_customers()));
				$prop["options"] = array("" => "") + $ol->names();
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
				if (count($tsk))
				{
					$ol = new object_list(array("oid" => $tsk));
					$prop["options"] = array("" => "") + $ol->names();
				}
				else
				{
					$prop["options"] = array("" => "");
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
			case "files":
				$prop["obj_parent"] = $arr["obj_inst"]->id();
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
}
?>
