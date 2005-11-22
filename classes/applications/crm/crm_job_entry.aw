<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_job_entry.aw,v 1.1 2005/11/22 09:45:38 kristo Exp $
// crm_job_entry.aw - T88 kirje 
/*

@classinfo syslog_type=ST_CRM_JOB_ENTRY no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property cust_d type=text subtitle=1
@caption Kliendi andmed

@property cust_n type=textbox 
@caption Nimetus

@property ettevotlusvorm type=select
@caption &Otilde;iguslik vorm

@property addr type=textbox 
@caption Aadress

@property addr_linn type=textbox 
@caption Linn

@property maakond type=textbox 
@caption Maakond

@property riik type=textbox default=Eesti
@caption Riik 

@property cont_d type=text subtitle=1
@caption Kontaktisiku andmed

@property ct_fn type=textbox 
@caption Eesnimi

@property ct_ln type=textbox 
@caption Perenimi

@property ct_phone type=textbox 
@caption Telefon

@property ct_email type=textbox 
@caption E-post


@property proj_header type=text subtitle=1
@caption Projekti andmed

@property proj_name type=textbox
@caption  Nimetus

@property proj_desc type=textbox
@caption Kirjeldus

@property proj_parts type=select multiple=1
@caption Osalejad

@property task_desc type=text subtitle=1 
@caption Tegevuse andmed

@property task_type type=select 
@caption Liik

@property task_start type=datetime_select 
@caption Algus

@property task_end type=datetime_select 
@caption L&otilde;pp

@property task_content type=textarea rows=10 cols=50
@caption Sisu


*/

class crm_job_entry extends class_base
{
	function crm_job_entry()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_job_entry",
			"clid" => CL_CRM_JOB_ENTRY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "name":
				return PROP_IGNORE;

			case "ettevotlusvorm":
				$ol = new object_list(array(
					"class_id" => CL_CRM_CORPFORM,
					"lang_id" => array(),
					"site_id" => array()
				));
				$prop["options"] = array("" => t("--Vali--")) + $ol->names();
				break;

			case "proj_parts":
				$u = get_instance(CL_USER);
				$co = obj($u->get_current_company());
				$i = $co->instance();

				$prop["options"] = $i->get_employee_picker($co);
				break;

			case "task_type":
				$clss = aw_ini_get("classes");
				$prop["options"] = array(
					CL_TASK => $clss[CL_TASK]["name"],
					CL_CRM_MEETING => $clss[CL_CRM_MEETING]["name"],
					CL_CRM_CALL => $clss[CL_CRM_CALL]["name"],
					CL_CRM_OFFER => $clss[CL_CRM_OFFER]["name"],
				);
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

	function callback_pre_save($arr)
	{
		// create cust
		$c = obj();
		$c->set_class_id(CL_CRM_COMPANY);
		$c->set_parent($arr["request"]["parent"]);
		$c->set_name($arr["request"]["cust_n"]);
		$c->set_prop("ettevotlusvorm", $arr["request"]["ettevotlusvorm"]);
		$c->save();

		// create address
		$addr = obj();
		$addr->set_class_id(CL_CRM_ADDRESS);
		$addr->set_parent($c->id());
		$addr->set_prop("aadress", $arr["request"]["addr"]);
		$addr->save();
		$this->set_by_n($addr, "linn", $arr["request"]["addr_linn"], CL_CRM_CITY, $addr->id());
		$this->set_by_n($addr, "maakond", $arr["request"]["maakond"], CL_CRM_COUNTY, $addr->id());
		$this->set_by_n($addr, "riik", $arr["request"]["riik"], CL_CRM_COUNTRY, $addr->id());
		$name = array();	
		$form = $arr["request"];
		$name[] = $form['addr'];
		$name[] = $form['addr_linn'];
		$name[] = $form['maakond'];
		$addr->set_name(join(",  ", $name));
		$addr->save();
				
		// kontaktisik
		$pers = obj();
		$pers->set_class_id(CL_CRM_PERSON);
		$pers->set_parent($c->id());
		$pers->set_name($arr["request"]["ct_fn"]." ".$arr["request"]["ct_ln"]);
		$pers->set_prop("firstname", $arr["request"]["ct_fn"]);
		$pers->set_prop("lastname", $arr["request"]["ct_ln"]);
		$this->set_by_n($pers, "phone", $arr["request"]["ct_phone"], CL_CRM_PHONE, $addr->id());
		$this->set_by_n($pers, "email", $arr["request"]["ct_email"], CL_ML_MEMBER, $addr->id());
		$pers->save();

		$c->set_prop("contact", $addr->id());
		$c->save();

		// add person as employee
		$pers->set_prop("work_contact", $c->id());
		$pers->save();
		$c->connect(array(
			"to" => $pers->id(),
			"type" => "RELTYPE_WORKERS"
		));

		// add as important person for me
		$u = get_instance(CL_USER);
		$cur_p = obj($u->get_current_person());
		$cur_p->connect(array(
			"to" => $pers->id(),
			"type" => "RELTYPE_IMPORTANT_PERSON"
		));

		// create proj
		$p = obj();
		$p->set_class_id(CL_PROJECT);
		$p->set_parent($arr["request"]["parent"]);
		$p->set_name($arr["request"]["proj_name"]);
		$p->set_prop("orderer", $c->id());
		$p->set_prop("description", $arr["request"]["proj_desc"]);
		$p->set_prop("participants", $arr["request"]["proj_parts"]);
		$p->save();

		// create task
		$t = obj();
		$t->set_class_id($arr["request"]["task_type"]);
		$t->set_parent($p->id());
		$t->set_prop("start1", date_edit::get_timestamp($arr["request"]["task_start"]));
		$t->set_prop("end", date_edit::get_timestamp($arr["request"]["task_end"]));
		$t->set_prop("content", $arr["request"]["task_content"]);
		if ($t->class_id() == CL_CRM_OFFER)
		{
			$t->set_prop("orderer", $c->id());
		}
		else
		if ($t->class_id() != CL_CRM_CALL)
		{
			$t->set_prop("customer", $c->id());
			$t->set_prop("project", $p->id());
		}
		$t->save();

		header("Location: ".html::get_change_url($t->id()));
		die();
	}

	function set_by_n($ro, $prop, $val, $clid, $pt)
	{
		$ol = new object_list(array(
			"class_id" => $clid,
			"name" => $val,
			"lang_id" => array(),
			"site_id" => array()
		));
		if ($ol->count())
		{
			$o = $ol->begin();
		}
		else
		{
			$o = obj();
			$o->set_class_id($clid);
			$o->set_parent($pt);
			$o->set_name($val);
			if ($clid == CL_ML_MEMBER)
			{
				$o->set_prop("mail", $val);
			}
			$o->save();
		}
		$ro->set_prop($prop, $o->id());
	}
}
?>
