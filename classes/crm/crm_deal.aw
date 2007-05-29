<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_deal.aw,v 1.18 2007/05/29 09:58:13 markop Exp $
// crm_deal.aw - Tehing 
/*

@classinfo syslog_type=ST_CRM_DEAL relationmgr=yes

@default table=objects
@default group=general

@tableinfo aw_crm_deal index=aw_oid master_index=brother_of master_table=objects


@default group=general

	@property customer type=popup_search clid=CL_CRM_COMPANY,CL_CRM_PERSON table=aw_crm_deal field=aw_customer style=autocomplete
	@caption Klient

	@property project type=popup_search clid=CL_PROJECT table=aw_crm_deal field=aw_project style=autocomplete
	@caption Projekt

	@property task type=popup_search clid=CL_TASK table=aw_crm_deal field=aw_task style=autocomplete
	@caption &Uuml;lesanne

	@property creator type=relpicker reltype=RELTYPE_CREATOR table=aw_crm_deal field=aw_creator
	@caption Koostaja

	@property reader type=relpicker reltype=RELTYPE_READER table=aw_crm_deal field=aw_reader
	@caption Lugeja

	@property reg_date type=date_select table=aw_crm_deal field=aw_reg_date
	@caption Reg kuup&auml;ev

	@property comment type=textarea rows=5 cols=50 table=objects field=comment
	@caption Kirjeldus

	@property sides type=relpicker store=connect multiple=1 reltype=RELTYPE_SIDE table=objects field=meta method=serialize
	@caption Osapooled

@default group=files

	@property files_tb type=toolbar no_caption=1
	@caption Failid

	@property files type=table
	@caption Failid

@default group=parts

	@property parts_tb type=toolbar no_caption=1

	@property acts type=table store=no no_caption=1
	@caption Tegevused

@groupinfo files caption="Failid"
@groupinfo parts caption="Osalejad" 
@groupinfo acl caption=&Otilde;igused
@default group=acl
	
	@property acl type=acl_manager store=no
	@caption &Otilde;igused

@reltype FILE value=1 clid=CL_FILE
@caption fail

@reltype CREATOR value=2 clid=CL_CRM_PERSON
@caption looja

@reltype READER value=3 clid=CL_CRM_PERSON
@caption lugeja

@reltype SIDE value=4 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Osapool

@reltype ACTION value=8 clid=CL_CRM_DOCUMENT_ACTION
@caption Tegevus
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
		$b = get_instance("applications/crm/crm_document_base");
		$retval = $b->get_property($arr);

		$prop = &$arr["prop"];
		switch($prop["name"])
		{
			case "files":
				$this->_get_files_table($arr);
				break;
			case "files_tb":
				$this->_get_files_tb($arr);
				break;	
				
/*			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$p_i = get_instance(CL_CRM_PERSON);
				$cust = $i->get_my_customers();
				if (count($cust))
				{
					$ol = new object_list(array("oid" => $cust));
					$prop["options"] = $ol->names();
					if (is_oid($prop["value"]) && $this->can("view", $prop["value"]) && !isset($prop["options"][$prop["value"]]))
					{
						$tmp = obj($prop["value"]);
						$prop["options"][$prop["value"]] = $tmp->name();
					}
				}
				asort($prop["options"]);
				break;*/
			
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$b = get_instance("applications/crm/crm_document_base");
		$retval = $b->set_property($arr);

		$prop = &$arr["prop"];
		switch($prop["name"])
		{
		 	case "customer":
				// check if the 
		 		if(!is_oid($prop["value"]))
 				{
 					if(is_oid($arr["request"]["customer_awAutoCompleteTextbox"]) && $this->can("view" , $arr["request"]["customer_awAutoCompleteTextbox"]))
 					{
 						$prop["value"] = $arr["request"]["customer_awAutoCompleteTextbox"];
 					}
 					elseif($arr["request"]["customer_awAutoCompleteTextbox"])
 					{
 						$ol = new object_list(array(
 							"name" => $arr["request"]["customer_awAutoCompleteTextbox"],
 							"class_id" => array(CL_CRM_COMPANY, CL_CRM_PERSON),
 							"lang_id" => array(),
 						));
 						$cust_obj = $ol->begin();
 						if(is_object($cust_obj))$prop["value"] = $cust_obj->id();
 					}
 				}
				if ($this->can("view", $prop["value"]))
				{
					$cust = obj($prop["value"]);
					if($cust->class_id() == CL_CRM_COMPANY)
					{
						$arr["request"]["sides"][] = $prop["value"];
					}
					else
					{
						$u = get_instance(CL_USER);
						$arr["request"]["sides"][] = $u->get_company_for_person($prop["value"]);
					}
				}
				break;
			case "sides":
				$u = get_instance(CL_USER);
				$prop["value"] = $arr["request"]["sides"];
				$prop["value"][]= $u->get_current_company();
				break;
			case "project":
				// check if the 
		 		if(!is_oid($prop["value"]))
 				{
 					if(is_oid($arr["request"]["project_awAutoCompleteTextbox"]) && $this->can("view" , $arr["request"]["project_awAutoCompleteTextbox"]))
 					{
 						$prop["value"] = $arr["request"]["project_awAutoCompleteTextbox"];
 					}
 					elseif($arr["request"]["project_awAutoCompleteTextbox"])
 					{
 						$ol = new object_list(array(
 							"name" => $arr["request"]["project_awAutoCompleteTextbox"],
 							"class_id" => array(CL_PROJECT),
 							"lang_id" => array(),
 						));
 						$cust_obj = $ol->begin();
 						if(is_object($cust_obj))$prop["value"] = $cust_obj->id();
 					}
 				}
				break;	
			case "task":
				// check if the 
		 		if(!is_oid($prop["value"]))
 				{
 					if(is_oid($arr["request"]["task_awAutoCompleteTextbox"]) && $this->can("view" , $arr["request"]["task_awAutoCompleteTextbox"]))
 					{
 						$prop["value"] = $arr["request"]["task_awAutoCompleteTextbox"];
 					}
 					elseif($arr["request"]["task_awAutoCompleteTextbox"])
 					{
 						$ol = new object_list(array(
 							"name" => $arr["request"]["task_awAutoCompleteTextbox"],
 							"class_id" => array(CL_TASK),
 							"lang_id" => array(),
 						));
 						$cust_obj = $ol->begin();
 						if(is_object($cust_obj))$prop["value"] = $cust_obj->id();
 					}
 				}
				break;
		}
		return $retval;
	}	

	function _get_files_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new",
			"tooltip" => t("Lisa fail"),
			"img" => "new.gif",
			"url" => $this->mk_my_orb("add_file", array("id" => $arr["obj_inst"]->id(), "ru" => get_ru()))
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta failid"),
			"confirm" => t("Oled kindel et soovid failid kustutada?"),
			"action" => "delete_files"
		));
		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta muudatused"),
			"action" => "save_files"
		));
	}

	/**
		@attrib name=delete_files
	**/
	function delete_files($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	/**
		@attrib name=add_file all_args=1
	**/
	function add_file($arr)
	{
		$deal = obj($arr["id"]);

		$file = obj();
		$file->set_parent($arr["id"]);
		$file->set_class_id(CL_FILE);
		$file->save();

		$deal->connect(array(
			"to" => $file->id(),
			"type" => "RELTYPE_FILE"
		));
		return $this->mk_my_orb("change", array("id" => $file->id(), "return_url" => $arr["ru"], "group" => "general"), CL_FILE);
	}
	
	/**
		@attrib name=save_files
	**/
	function save_files($arr)
	{
		die();
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	function _get_files_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_files_table($t);
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_FILE",
		));
		$file_inst = get_instance(CL_FILE);
		classload("core/icons");
		foreach($conns as $c)
		{
			$f = $c->to();
			$fu = $f->prop("file");
			$name = basename($fu);
			$fname = $file_inst->check_file_path($f->prop("file"));
			$t->define_data(array(
				"name" => html::textbox(array("size" => 70,"value" => html::href(array(
					"url" => $file_inst->get_url($f->id(), $f->name()),
					"caption" => $f->name(),)))),//$f->name(),
				"oid" => $f->id(),
				"change" => html::obj_change_url($f->id(),t("Muuda")),
				"changed" => date("d.m.Y h:i" , $f->prop("modified")),
				"changer" => $f->modifiedby(),
				
				"file" => html::href(array(
					"url" => $file_inst->get_url($f->id(), $f->name()),
					"caption" => "Ava",
					"target" => "_blank",
	//				"alt" => $fname,
	//				"title" => $fname
				)),
			));
		}
	}

	function _init_files_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "file",
			"caption" => t("Fail"),
		));
		$t->define_field(array(
			"name" => "changed",
			"caption" => t("Muudetud"),
		));
		$t->define_field(array(
			"name" => "changer",
			"caption" => t("Muutja"),
		));
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));
	}

	function callback_post_save($arr)
	{
		if($arr["new"]==1 && is_oid($arr["request"]["project"]) && $this->can("view" , $arr["request"]["project"]))
		{
			$arr["obj_inst"]->set_prop("project" , $arr["request"]["project"]);
		}
	}
	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		if(!$arr["id"])
		{
			$arr["project"] = $_GET["project"];
		}
	}
	
}
?>
