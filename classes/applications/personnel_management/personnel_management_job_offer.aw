<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_job_offer.aw,v 1.9 2005/03/24 10:13:00 ahti Exp $
// personnel_management_job_offer.aw - Tööpakkumine 
/*
//HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_PERSONNEL_MANAGEMENT_JOB_OFFER, on_job_save)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_PERSONNEL_MANAGEMENT_JOB_OFFER, on_connect_to_sector)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_PERSONNEL_MANAGEMENT_JOB_OFFER, on_connect_from_sector)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_PERSONNEL_MANAGEMENT_JOB_OFFER, on_disconnect_job_from_section)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_TO, CL_PERSONNEL_MANAGEMENT_JOB_OFFER, on_disconnect_section_from_job)

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_OFFER relationmgr=yes no_yah=1

@default table=objects
@default group=general

@tableinfo personnel_management_job index=oid master_table=objects master_index=oid


@property profession type=select table=personnel_management_job datatype=int
@caption Ametikoht

@property deadline type=date_select table=personnel_management_job
@caption Konkursi tähtaeg

@property beginning type=date_select table=personnel_management_job
@caption Konkursi algusaeg

///*
//@property name type=textbox table=objects field=name group=info_about_job
//@caption Ametikoht
//
//@property navtoolbar type=toolbar no_caption=1 store=no group=kandideerinud
//
//@property toosisu type=textarea field=about_job table=personnel_management_job  group=info_about_job
//@caption Töö kirjeldus
//
//@property noudmised type=textarea field=requirements table=personnel_management_job  group=info_about_job
//@caption N&otilde;udmised kandidaadile
//
//@property asukoht type=relpicker reltype=RELTYPE_LINN automatic=1 method=serialize field=meta table=objects  group=info_about_job
//@caption Asukoht
//
//@property deadline type=date_select table=personnel_management_job group=info_about_job
//@caption Konkursi tähtaeg
//
//@property tegevusvaldkond type=classificator field=meta method=serialize multiple=1 table=objects store=connect group=info_about_job reltype=RELTYPE_TEGEVUSVALDKOND orient=vertical
//@caption Tegevusvaldkond
//
//@property cvfail type=text subtitle=1 group=info_about_job_file store=no
//@caption Tööpakkumine failina.
//
//@property cv_file_rel type=releditor reltype=RELTYPE_JOBFILE props=file group=info_about_job_file no_caption=1 field=meta method=serialize 
//@property cv_file_del type=toolbar group=info_about_job_file no_caption=1
//
//@property candits type=table group=kandideerinud no_caption=1
//@caption Kandideerijad
//
//
//----------------MINU KANDIDATUUR---------------------
//@property kandideerin type=chooser field=meta method=serialize store=no group=minu_kandidatuur edit_links=1
//@caption Vali cv kandideerimiseks
//
//@property kaaskiri type=textarea store=no
//@caption Kaaskiri
//------------------------------------------------------
//
//
//@property statistika type=table no_caption=1 group=statistika
//
//property email type=relmanager reltype=RELTYPE_EMAIL props=name group=info_about_job field=meta method=serialize
//caption E-mail
//
//property phone type=relmanager reltype=RELTYPE_PHONE props=name group=info_about_job field=meta method=serialize
//caption Telefon
//
//@property job_from type=date_select year_to=2010 field=meta method=serialize group=info_about_job
//@caption Tööleasumise aeg
//
//@property job_nr type=textbox size=3 field=meta method=serialize group=info_about_job
//@caption Ametikohtade arv
//
//@property contact_person type=textarea field=meta method=serialize group=info_about_job
//@caption Kontaktisik ja kontaktandmed 
//
//@property tookoormused type=classificator field=meta method=serialize multiple=1 table=objects store=connect group=info_about_job reltype=RELTYPE_WORKLOAD orient=vertical
//@caption Töökoormused
//
//@property is_public type=checkbox field=meta method=serialize group=info_about_job ch_value=1
//@caption Avalik konkurss
//
//@property praktika type=classificator field=meta mehtod=serialize group=info_about_job reltype=RELTYPE_PRACTICE orient=vertical store=connect
//@caption  Liik
//
//@reltype WORKLOAD value=1 clid=CL_META
//@caption Töökoormused
//
//@reltype EMAIL value=2 clid=CL_ML_MEMBER
//@caption E-post
//
//@reltype PHONE value=3 clid=CL_CRM_PHONE
//@caption Telefon
//
//@reltype LINN value=4 clid=CL_CRM_CITY
//@caption Linn
//
//@reltype KANDIDAAT value=5 clid=CL_CV
//@caption Kandidaat
//
//@reltype TEGEVUSVALDKOND value=6 clid=CL_META
//@caption Tegevusvaldkond
//
//@reltype JOBFILE value=7 clid=CL_FILE
//@caption Tööpakkumine failina
//
//@reltype PRACTICE value=8 clid=CL_META
//@caption Tööliik
//--------------------- TÖÖPAKKUMISE VAADE ------------------------------
//@property job_view_tb type=toolbar no_caption = 1 group=job_view no_caption=1
//@property job_view type=text no_caption=1 store=no wrapchildren=1 group=job_view
//
//--------------------TABS------------------------------------------------
//@groupinfo info_about_job caption="Tööpakkumine" parent=info_about_job_main
//@groupinfo info_about_job_main caption="Tööpakkumine"
//@groupinfo info_about_job_file caption="Tööpakkumine failina" parent=info_about_job_main
//
//@groupinfo job_view caption="Vaata Tööpakkumist" submit=no
//@groupinfo minu_kandidatuur caption="Minu kandidatuur"
//@groupinfo kandideerinud caption="Kandideerijad" submit=no
//@groupinfo statistika caption="Statistika" submit=no

@reltype SECTION value=9 clid=CL_CRM_SECTION
@caption Üksus
*/

class personnel_management_job_offer extends class_base
{
	var $my_profile;

	function personnel_management_job_offer()
	{
		// change this to the folder under the templates folder, where this classes templates will be,
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/personnel_management/personnel_management_job_offer",
			"clid" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER
		));
		
		if (!aw_global_get("no_db_connection"))
		{
			$personalikeskkond = get_instance(CL_PERSONNEL_MANAGEMENT);
			$this->my_profile = $personalikeskkond->my_profile;
		}
	}
	
	
	function callback_on_load($arr)
	{
		$this->cfgmanager = aw_ini_get("personnel_management.configform_manager");
	}
	

	function on_connect_from_sector($arr)
	{
		$old_conn = $arr["connection"];	
		if($old_conn->prop("reltype") == 9)
		{
			$conn = new connection();
			$conn->change(array(
				"from" => $old_conn->prop("to"), 
				"to" => $old_conn->prop("from"), 
				"reltype" => 4
			));
		}
	}
	
	//This function is called by message and creates reverse relations between job and section
	function on_connect_to_sector($arr)
	{
		$old_conn = $arr["connection"];	
		if($old_conn->prop("reltype") == 4)
		{
			$conn = new connection();
			$conn->change(array(
				"from" => $old_conn->prop("to"), 
				"to" => $old_conn->prop("from"), 
				"reltype" => 9
			));
		}
	}

	function on_disconnect_job_from_section($arr)
	{
		$deleted_connection = $arr["connection"];
		$target_obj = $deleted_connection->to();
		
		if($target_obj->class_id() == CL_CRM_SECTION)
		{
			if($target_obj->is_connected_to(array('to'=>$deleted_connection->prop('from'))))
			{
				$target_obj->disconnect(array(
					"from" => $deleted_connection->prop("from"),
				));
			}
		}
	}
	
	function on_disconnect_section_from_job($arr)
	{
		$deleted_connection = $arr["connection"];
		$target_obj = $deleted_connection->to();
		
		if($target_obj->class_id() == CL_PERSONNEL_MANAGEMENT_JOB_OFFER)
		{
			if($target_obj->is_connected_to(array('to'=>$deleted_connection->prop('from'))))
			{
				$target_obj->disconnect(array(
					"from" => $deleted_connection->prop("from"),
				));
			}
		}
	}
	
	function on_job_save($arr)
	{
		$job_obj = &obj($arr["oid"]);

		if($this->my_profile["group"] == "employer")
		{
			$this->my_profile["org_obj"]->connect(array(
				"to" => $job_obj->id(),
				"reltype" => 19,
			));

			$job_obj->set_parent($this->my_profile["org_obj"]->parent());
			$job_obj->save();
		}
	}
	/**
		@attrib name=delete_rels
	**/
	function delete_rels($arr)
	{
		foreach ($arr["sel"] as $conn)
		{
			$conn=new connection($conn);
			$conn->delete();
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]), $arr["class"]);
	}

	/**
		@attrib name=change nologin="1" all_args="1"
	**/
	function change($params)
	{
		return parent::change($params);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			case "deadline":
				$prop["year_from"] = date("Y", time());
				$prop["year_to"] = date("Y", time()) + 10;
			break;
			
			case "beginning":
				$prop["year_from"] = date("Y", time());
				$prop["year_to"] = date("Y", time()) + 10;
			break;
			
			case "profession":
				//Natc segane loogika :)
				$section_obj = get_instance(CL_CRM_SECTION);
				
				if($arr["obj_inst"]->id())
				{
					$section = current($arr['obj_inst']->connections_from(array('type' => 9)));
					if(is_object($section))
					{	
						$section = $section->to();
					}
					else
					{
						$org = current($arr["obj_inst"]->connections_to(array("from.class_id" => CL_CRM_COMPANY)));
						if(is_object($org))
						{
							$professions = $section_obj->get_all_org_proffessions($org->prop("from"));
						}
					}
				}
				elseif ($arr["request"]["unit"])
				{
					$section = &obj($arr["request"]["unit"]);
				}
				elseif ($arr["request"]["cat"])
				{	
					$professions = $section_obj->get_all_org_proffessions($arr["request"]["org"]);
				}
				else 
				{
					return PROP_IGNORE;
				}
				
				if($section)
				{
					$section_obj = get_instance(CL_CRM_SECTION);
					$professions = $section_obj->get_professions($section->id(), true);			
				}
					
				$prop["options"] = $professions;
				
				if($arr["request"]["cat"])
				{
					$prop["value"] = $arr["request"]["cat"];
				}
			break;	
		}
		return $retval;
	}
	
/*	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "deadline":
				$prop["year_from"] = date("Y", time());
				$prop["year_to"] = date("Y", time()) + 10;
			break;
			case "job_nr":
				if(!$prop["value"])
				{
					$prop["value"] = 1;
				}
			break;
			case "cv_file_del":
				$tb = &$arr["prop"]["toolbar"];
	
				$tb->add_button(array(
					"name" => "delete",
					"img" => "delete.gif",
					"tooltip" => t("Kustuta fail"),
					"action" => "delete_cv_file",
				));
			break;
			case "cv_file_rel":
				if($jobfile = current($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_JOBFILE"))))
				{
					$prop["rel_id"] = $jobfile->id();
					$prop["props"] = array("file", "filename");
				}
			break;			
			
			case "job_from":
				$prop["year_from"]=date("Y", time());
			break;
			case "job_view":
				$prop["value"] = $this->show(array("id" => $arr["obj_inst"]->id()));
			break;
			case "job_view_tb":
				$this->do_view_tb($arr);
			break;
			
			case "statistika":
				$this->do_stats_table($arr);
			break;
			
			case "kandideerin":

				foreach($this->my_profile["person_obj"]->connections_from(array("type" => "RELTYPE_CV")) as $cv)
				{
					$mycvs[$cv->prop("to")] = $cv->prop("to.name"); 
				}

				foreach ($mycvs as $mycv_id => $value)
				{
					if($arr["obj_inst"]->connections_from(array("to" => $mycv_id)))
					{
						$prop["value"] = $mycv_id;
					}
				}

				$prop["options"] = $mycvs;
			break;
			
			case "candits":
				$this->do_candits_table($arr);
			break;
			
			case "kaaskiri":
				
			break;
			
			case "navtoolbar":
				
				$tb = &$prop["toolbar"];
				
				$tb->add_button(array(
					"name" => "save",
					"img" => "save.gif",
					"tooltip" => t("Salvesta hinded"),
					"url" => "javascript:document.changeform.submit()",
				));

				$tb->add_button(array(
					"name" => "delete",
					"img" => "delete.gif",
					"tooltip" => t("Kustuta kandieerijad"),
					"action" => "delete_rels",
				));
			break;
			
		};
		return $retval;
	}
	*/
	function do_candits_table($arr)
	{
		
		$table=&$arr["prop"]["vcl_inst"];
				
		$table->define_field(array(
			"name" => "nimi",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
				
			
		$table->define_field(array(
			"name" => "date",
			"caption" => t("Kuupäev"),
			"sortable" => 1,
		));

		$table->define_field(array(
			"name" => "kaaskiri",
			"caption" => t("Kaaskiri"),
			"sortable" => 1,
		));
			
		$table->define_field(array(
			"name" => "hinne",
			"caption" => t("Hinne"),
			"sortable" => 1,
			"numeric" => 1,
			"sortable" => 1,
			"callback" => array(&$this, 'callb_jrk'),
			"callb_pass_row" => true,
		));	

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));
				
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_KANDIDAAT")) as $cv)
		{
				
			$connection_id = $cv->id();
			$connection_created= $cv->prop("created");
				
			$rel_obj = obj($cv->prop("relobj_id"));

			$cv = &obj($cv->prop("to"));
			$conn = new connection();
				
			$conn = $conn->find(array(
				"from.class_id" => CL_CRM_PERSON,
				"to" => $cv->id(),	
			));
				
			$conn = array_shift($conn);
			$person =& obj($conn["from"]);
			
			if($rel_obj->meta("kaaskiri"))
			{
				$kaaskiri_url = html::href(array(
					"caption" => t("kaaskiri"),
					"url" => $this->mk_my_orb(array("view_letter", array("id" => $rel_obj->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER)),
				));
			}
			else
			{
				$kaaskiri_url = t("Puudub");
			}
			
			$table->define_data(array(							
				"nimi" => html::href(array(
								"caption" => $person->prop("firstname")." ".$person->prop("lastname"),
								"url" =>  $this->mk_my_orb("change", array("id" => $cv->id()) , CL_PERSONNEL_MANAGEMENT_CV),
						)),
				"date" => get_lc_date($connection_created),
				"from" => $connection_id,
				"to" => $connection_id,
				"hinne" => $rel_obj->meta("hinne"),
				"kaaskiri" => $kaaskiri_url,
			));
		}
	}
	
	/**
		@attrib name=delete_cv_file
	**/
	function delete_cv_file($arr)
	{
		$ob = &obj($arr["id"]);
		if(is_object($ob))
		{
			foreach ($ob->connections_from(array("type" => "RELTYPE_JOBFILE")) as $jobfile)
			{
				$jobfile->delete();
			}
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]), $arr["class"]);
	}
	
	function do_view_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];
	
		$tb->add_button(array(
			"name" => "GEN PDF",
			"img" => "pdf_upload.gif",
			"tooltip" => t("Genereeri pdf"),
			"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id(), "oid" => $arr["obj_inst"]->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
		));
	}
	
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "profession":/*
				if($arr["request"]["cat"])
				{
					$prop["value"] = $arr["request"]["cat"];
				}*/
			break;
		}
		return $retval;
	}
	
	/*function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "kandideerin":
				$this->apply_for_job($arr);		
			break;

			case "candits":
				$this->do_save_candits_table($arr);
			break;
		}
		return $retval;
	}
	*/
	function do_save_candits_table($arr)
	{
		if (!is_array($arr["request"]["hinne"]))
		{
			$arr["request"]["hinne"] = array();
		}
		foreach ($arr["request"]["hinne"] as $key => $value)
		{
			$conn = new connection($key);
			$relobject = &obj($conn->prop("relobj_id"));
			$relobject->set_meta("hinne", $value);
			$relobject->save();
		}
	}

	function apply_for_job(&$arr)
	{
		//Kustutame seosed kasutaja cv de ja tööpakkumiste vahle... juhul kui tööotsija on pakkumisele ka enne kandideerinud
		foreach($this->my_profile["person_obj"]->connections_from(array("type" => "RELTYPE_CV")) as $cv)
		{
			if($cv = current($arr["obj_inst"]->connections_from(array("to" => $cv->prop("to")))))
			{
				$cv->delete();
			}
		}
			
		if($arr["prop"]["value"])
		{
			$newconn = new connection();
			$newconn->change(array("from" => $arr["obj_inst"]->id(), "to" => $arr["prop"]["value"], "reltype" => "RELTYPE_KANDIDAAT"));
		}
		//Salvestame kaaskirja seoseobjekti juurde
		$kaaskiri_obj = &obj($newconn->prop("relobj_id"));
		$kaaskiri_obj->set_meta("kaaskiri", $arr["request"]["kaaskiri"]);
		$kaaskiri_obj->save();
	}
	
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function do_stats_table(&$arr)
	{
	
		$table=&$arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "person",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
				
		$table->define_field(array(
			"name" => "views",
			"caption" => t("Vaatamisi"),
			"sortable" => 1,
		));
		
		$query_str = "SELECT *, count(uid) as vaatamisi FROM cv_hits WHERE oid=".$arr['obj_inst']->id()." GROUP by uid";
		
		$this->db_query($query_str);
		$results = array();
		$results = $this->db_fetch_array();
		
		
		foreach($results as $row)
		{
			$user = obj(users::get_oid_for_uid($row["uid"]));
			if(!is_object($user))
			{
				continue;
			}
			
			$person = current($user->connections_from(array("type" => "RELTYPE_PERSON")));
			$person = $person->to();
			if(!is_object($person))
			{
				continue;
			}
			
			
			if($person->prop("default_cv"))
			{
				$person_link = html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $person->prop("default_cv")), CL_PERSONNEL_MANAGEMENT_CV),
						"caption" => $person->name(),
				));
			}
			else
			{
				$person_link = $person->name();
			}
			$table->define_data(array(
				"person" => $person_link,
				"views" => $row["vaatamisi"],
			));
		}
	}
	
	function show($arr)
	{
		$job_parse_props["company_name"]["publicview"] = true;
		
		$job_parse_props["org_description"]["view"] = true;
		$job_parse_props["phone"]["view"] = true;
		$job_parse_props["email"]["view"] = true;
		
		//Tööpakkumise objekt
		$ob = new object($arr["id"]);
		
		//Kui tööpakkumist vaatas tööotsija , siis lisame ühe HITI.
		if($this->my_profile["group"]=="employee")
		{
			$this->add_view(array("id" => $ob->id()));
		}
		
		
		$company = current($ob->connections_to(array("from.class_id" => CL_CRM_COMPANY)));
		$company = &obj($company->prop("from"));
		$location = " - ";
		if ($ob->prop("asukoht"))
		{
			$location = &obj($ob->prop("asukoht")); 
			$location = $location->name();
		}
		$this->read_template("show.tpl");
		
		
		//ORGANISATION DESCRIPTION SUB
		if($job_parse_props["org_description"]["view"] == true && $company->prop("tegevuse_kirjeldus"))
		{
			$this->vars(array(
				"org_description" => $company->prop("tegevuse_kirjeldus"),
			));
			$org_description = $this->parse("org_description_sub");
			
			$this->vars(array(
				"org_description" => $org_description,
			));
		}
		
		//PHONE NR SUB
		if($job_parse_props["phone"]["view"] == true)
		{
			if($ob->prop("phone"))
			{
				$phone_nr = &obj($ob->prop("phone"));
				$this->vars(array(
					"phone_nr" => $phone_nr->name(),
				));
				$phone_nr_htm = $this->parse("phone_nr_sub");
				$this->vars(array(
					"phone_nr" => $phone_nr_htm,
				));
			}
		}
		
		//EMAIL SUB
		if($job_parse_props["email"]["view"] == true)
		{
			if($ob->prop("email"))
			{
				$email = &obj($ob->prop("email"));
				
				$this->vars(array(
					"email" => $email->prop("name"),
				));

				$email_htm = $this->parse("email_sub");
				$this->vars(array(
					"email" => $email_htm,
				));
			}
		}
		
		//
		//Valdkondade nimekiri
		/*
		foreach ($ob->connections_from(array("type" => "RELTYPE_TEGEVUSVALDKOND")) as $sector)
		{
			$this->vars(array(
				"sector" => $sector->prop("to.name"),
			));
			$tmp_sectors .= $this->parse("sectors_list");
		}*/
		
		$ks = array();
		if (is_array($ob->prop("tookoormused")))
		{
			foreach($ob->prop("tookoormused") as $tkm)
			{
				$_o = obj($tkm);
				$ks[] = $_o->name();
			}
		}
		
		$this->vars(array(
			"name" => @$ob->prop("name"),
			"company" => $company_name,
			"location" => $location,
			"sectors" => $tmp_sectors,
			"deadline" => get_lc_date($ob->prop("deadline")),
			"description" => $ob->prop("toosisu"),
			"requirements" => $ob->prop("noudmised"),
			"start_date" => $ob->prop("job_from") > 100 ? get_lc_date($ob->prop("job_from")) : " - ",
			"tookoormused" => join(",", $ks),
			"contact_person" => $ob->prop("contact_person"),
			"job_nr" => $ob->prop("job_nr")
		));
		
		return $this->parse();
	}
	
	
	//This funcition will be called by scheduler every day and sets jobs where deadline is over unactive.
	/**
		@attrib name=job_to_not_act
	**/
	function job_to_not_act($arr)
	{
		$not_act_list = new object_list(array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			"deadline" => new obj_predicate_compare(OBJ_COMP_LESS, time()),
		));
		foreach ($not_act_list->arr() as $ob)
		{
			$ob->set_status(STAT_NOTACTIVE);
			$ob->save();
		}
	}
	
	/**
		@attrib name=gen_job_pdf nologin="1"
		@param oid required type=int
	**/
	function gen_job_pdf($arr)
	{
		$job = &obj($arr["oid"]);
		$pdf_gen = get_instance("core/converters/html2pdf");
		$content = $pdf_gen->convert(array("source" => $this->show(array("id" => $arr["oid"]))));
		
		header("Content-type: application/pdf");
		header("Content-disposition: inline; filename=joboffer.pdf");
		header("Content-length: " . strlen($content));
		
		echo $content;
	}
	
	
	function add_view($arr)
	{
		if(!$_SESSION["job_view".$arr["id"]])
		{ 
			$this->add_hit($arr["id"]);
			$oid = $arr["id"];
			$uid = aw_global_get("uid");
			$ip = getenv("REMOTE_ADDR");
			$time = time();
			$this->db_query("INSERT INTO cv_hits VALUES(NULL,'$oid', '$uid', '$ip', '$time')");
			$_SESSION["job_view".$arr["id"]] = true;
		}
	}

	function callb_jrk($arr)
	{
		$toid=$arr["to"];	
		return  html::textbox(array(
					"size" => 4,
					"maxlength" => 4,
					"name" => "hinne[$toid]",
					"value" => $arr['hinne'],
                ));
	}
}
?>
