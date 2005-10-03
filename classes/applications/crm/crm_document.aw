<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_document.aw,v 1.3 2005/10/03 14:01:57 kristo Exp $
// crm_document.aw - CRM Dokument 
/*

@classinfo syslog_type=ST_CRM_DOCUMENT relationmgr=yes no_status=1 prop_cb=1

@default table=objects
@tableinfo aw_crm_document index=aw_oid master_index=brother_of master_table=objects


@default group=general

	@property project type=popup_search clid=CL_PROJECT table=aw_crm_document field=aw_project
	@caption Projekt

	@property task type=popup_search clid=CL_TASK table=aw_crm_document field=aw_task
	@caption &Uuml;lesanne

	@property customer type=popup_search clid=CL_CRM_COMPANY table=aw_crm_document field=aw_customer
	@caption Klient

	@property state type=select table=aw_crm_document field=aw_state
	@caption Staatus

	@property reg_date type=date_select table=aw_crm_document field=aw_reg_date
	@caption Reg kuup&auml;ev

	@property make_date type=date_select table=aw_crm_document field=aw_make_date
	@caption Koostamise kuup&auml;ev

	@property reg_nr type=date_select table=aw_crm_document field=aw_reg_nr
	@caption Registreerimisnumber

	@property comment type=textarea rows=5 cols=50 table=objects field=comment
	@caption Kirjeldus

@default group=files

	@property files type=releditor reltype=RELTYPE_FILE field=meta method=serialize mode=manager props=name,file,type,comment,file_url,newwindow table_fields=name 
	@caption Failid

@groupinfo files caption="Failid"

@reltype FILE value=1 clid=CL_FILE
@caption fail

*/

class crm_document extends class_base
{
	function crm_document()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_document",
			"clid" => CL_CRM_DOCUMENT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "project":
				$i = get_instance(CL_CRM_COMPANY);
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
