<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_job_offer.aw,v 1.4 2004/06/17 13:28:07 kristo Exp $
// personnel_management_job_offer.aw - T��pakkumine 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_PERSONNEL_MANAGEMENT_JOB_OFFER, on_job_save)

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_OFFER relationmgr=yes no_yah=1

@default table=objects
@default group=general

@tableinfo personnel_management_job index=oid master_table=objects master_index=oid

@property name type=textbox table=objects field=name group=info_about_job
@caption Ametikoht

@property navtoolbar type=toolbar no_caption=1 store=no group=kandideerinud

@property toosisu type=textarea field=about_job table=personnel_management_job  group=info_about_job
@caption T�� kirjeldus

@property noudmised type=textarea field=requirements table=personnel_management_job  group=info_about_job
@caption N&otilde;udmised kandidaadile

@property asukoht type=relpicker reltype=RELTYPE_LINN automatic=1 method=serialize field=meta table=objects  group=info_about_job
@caption Asukoht

@property deadline type=date_select table=personnel_management_job group=info_about_job
@caption Konkursi t�htaeg

@property tegevusvaldkond type=classificator field=meta method=serialize multiple=1 table=objects store=connect group=info_about_job reltype=RELTYPE_TEGEVUSVALDKOND orient=vertical
@caption Tegevusvaldkond

@property cvfail type=text subtitle=1 group=info_about_job_file store=no
@caption T��pakkumine failina.

@property cv_file_rel type=releditor reltype=RELTYPE_JOBFILE props=file group=info_about_job_file no_caption=1 field=meta method=serialize 
@property cv_file_del type=toolbar group=info_about_job_file no_caption=1

@property candits type=table group=kandideerinud no_caption=1
@caption Kandideerijad


----------------MINU KANDIDATUUR---------------------
@property kandideerin type=chooser field=meta method=serialize store=no group=minu_kandidatuur edit_links=1
@caption Vali cv kandideerimiseks

@property kaaskiri type=textarea store=no
@caption Kaaskiri
------------------------------------------------------


@property statistika type=table no_caption=1 group=statistika

property email type=relmanager reltype=RELTYPE_EMAIL props=name group=info_about_job field=meta method=serialize
caption E-mail

property phone type=relmanager reltype=RELTYPE_PHONE props=name group=info_about_job field=meta method=serialize
caption Telefon

@property job_from type=date_select year_to=2010 field=meta method=serialize group=info_about_job
@caption T��leasumise aeg

@property job_nr type=textbox size=3 field=meta method=serialize group=info_about_job
@caption Ametikohtade arv

@property contact_person type=textarea field=meta method=serialize group=info_about_job
@caption Kontaktisik ja kontaktandmed 

@property tookoormused type=classificator field=meta method=serialize multiple=1 table=objects store=connect group=info_about_job reltype=RELTYPE_WORKLOAD orient=vertical
@caption T��koormused

@property is_public type=checkbox field=meta method=serialize group=info_about_job ch_value=1
@caption Avalik konkurss

@property praktika type=classificator field=meta mehtod=serialize group=info_about_job reltype=RELTYPE_PRACTICE orient=vertical store=connect
@caption  Liik

@reltype WORKLOAD value=1 clid=CL_META
@caption T��koormused

@reltype EMAIL value=2 clid=CL_ML_MEMBER
@caption E-post

@reltype PHONE value=3 clid=CL_CRM_PHONE
@caption Telefon

@reltype LINN value=4 clid=CL_CRM_CITY
@caption Linn

@reltype KANDIDAAT value=5 clid=CL_CV
@caption Kandidaat

@reltype TEGEVUSVALDKOND value=6 clid=CL_META
@caption Tegevusvaldkond

@reltype JOBFILE value=7 clid=CL_FILE
@caption T��pakkumine failina

@reltype PRACTICE value=8 clid=CL_META
@caption T��liik
--------------------- T��PAKKUMISE VAADE ------------------------------
@property job_view_tb type=toolbar no_caption = 1 group=job_view no_caption=1
@property job_view type=text no_caption=1 store=no wrapchildren=1 group=job_view

--------------------TABS------------------------------------------------
@groupinfo info_about_job caption="T��pakkumine" parent=info_about_job_main
@groupinfo info_about_job_main caption="T��pakkumine"
@groupinfo info_about_job_file caption="T��pakkumine failina" parent=info_about_job_main

@groupinfo job_view caption="Vaata T��pakkumist" submit=no
@groupinfo minu_kandidatuur caption="Minu kandidatuur"
@groupinfo kandideerinud caption="Kandideerijad" submit=no
@groupinfo statistika caption="Statistika" submit=no

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
			$personalikeskkond = get_instance("applications/personnel_management/personnel_management");
			$this->my_profile = $personalikeskkond->my_profile;
		}
	}

	function callback_on_load($arr)
	{
		$this->cfgmanager = aw_ini_get("personnel_management.configform_manager");
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
					"tooltip" => "Kustuta fail",
					"action" => "delete_cv_file",
				));
			break;
			case "cv_file_rel":
				if($jobfile = current($arr["obj_inst"]->connections_from(array("type" => RELTYPE_JOBFILE))))
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

				foreach($this->my_profile["person_obj"]->connections_from(array("type" => RELTYPE_CV)) as $cv)
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
					"tooltip" => "Salvesta hinded",
					"url" => "javascript:document.changeform.submit()",
				));

				$tb->add_button(array(
					"name" => "delete",
					"img" => "delete.gif",
					"tooltip" => "Kustuta kandieerijad",
					"action" => "delete_rels",
				));
			break;
			
		};
		return $retval;
	}
	
	function do_candits_table($arr)
	{
		
		$table=&$arr["prop"]["vcl_inst"];
				
		$table->define_field(array(
			"name" => "nimi",
			"caption" => "Nimi",
			"sortable" => 1,
		));
				
			
		$table->define_field(array(
			"name" => "date",
			"caption" => "Kuup�ev",
			"sortable" => 1,
		));

		$table->define_field(array(
			"name" => "kaaskiri",
			"caption" => "Kaaskiri",
			"sortable" => 1,
		));
			
		$table->define_field(array(
			"name" => "hinne",
			"caption" => "Hinne",
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
				
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_KANDIDAAT)) as $cv)
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
					"caption" => "kaaskiri",
					"url" => $this->mk_my_orb(array("view_letter", array("id" => $rel_obj->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER)),
				));
			}
			else
			{
				$kaaskiri_url = "Puudub";
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
			foreach ($ob->connections_from(array("type" => RELTYPE_JOBFILE)) as $jobfile)
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
			"tooltip" => "Genereeri pdf",
			"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id(), "oid" => $arr["obj_inst"]->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
		));
	}

	
	function set_property($arr = array())
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
		//Kustutame seosed kasutaja cv de ja t��pakkumiste vahle... juhul kui t��otsija on pakkumisele ka enne kandideerinud
		foreach($this->my_profile["person_obj"]->connections_from(array("type" => RELTYPE_CV)) as $cv)
		{
			if($cv = current($arr["obj_inst"]->connections_from(array("to" => $cv->prop("to")))))
			{
				$cv->delete();
			}
		}
			
		if($arr["prop"]["value"])
		{
			$newconn = new connection();
			$newconn->change(array("from" => $arr["obj_inst"]->id(), "to" => $arr["prop"]["value"], "reltype" => RELTYPE_KANDIDAAT));
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
			"caption" => "Nimi",
			"sortable" => 1,
		));
				
		$table->define_field(array(
			"name" => "views",
			"caption" => "Vaatamisi",
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
			
			$person = current($user->connections_from(array("type" => RELTYPE_PERSON)));
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
		
		//T��pakkumise objekt
		$ob = new object($arr["id"]);
		
		//Kui t��pakkumist vaatas t��otsija , siis lisame �he HITI.
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
		foreach ($ob->connections_from(array("type" => RELTYPE_TEGEVUSVALDKOND)) as $sector)
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
