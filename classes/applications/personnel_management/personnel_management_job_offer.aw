<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_job_offer.aw,v 1.12 2006/04/04 11:44:26 ahti Exp $
// personnel_management_job_offer.aw - Tööpakkumine 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_OFFER relationmgr=yes r2=yes no_comment=1

@default table=objects
@default group=general


tableinfo personnel_management_job index=oid master_table=objects master_index=oid

@property toolbar type=toolbar no_caption=1

@property name type=textbox
@caption Nimi

@property status type=status
@caption Aktiivne

@default field=meta
@default method=serialize

@property company type=relpicker reltype=RELTYPE_ORG
@caption Organisatsioon

@property contact type=relpicker reltype=RELTYPE_CONTACT
@caption Kontaktisik

@property start type=date_select
@caption Konkursi algusaeg

@property end type=date_select
@caption Konkursi tähtaeg

@property profession type=relpicker reltype=RELTYPE_PROFESSION
@caption Ametikoht

@property field type=classificator reltype=RELTYPE_FIELD store=connect
@caption Valdkond

@property location type=relpicker reltype=RELTYPE_LOCATION
@caption Asukoht

@property workinfo type=textarea rows=15 cols=60
@caption Töö sisu

@property requirements type=textarea rows=15 cols=60
@caption Nõudmised kandidaadile

@property suplementary type=textarea rows=15 cols=60
@caption Kasuks tuleb

@property weoffer type=textarea rows=15 cols=60
@caption Omalt poolt pakume

@property info type=textarea rows=15 cols=60
@caption Lisainfo

@groupinfo candidate caption="Kandideerimised" submit=no
@default group=candidate

@property candidate_toolbar type=toolbar no_caption=1

@property candidate_table type=table no_caption=1

@reltype CANDIDATE value=1 clid=CL_PERSONNEL_MANAGEMENT_CANDIDATE
@caption Kandidatuur

@reltype ORG value=2 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype CONTACT value=3 clid=CL_CRM_PERSON
@caption Kontaktisik

@reltype PROFESSION value=4 clid=CL_CRM_PROFESSION
@caption Ametikoht

@reltype LOCATION value=5 clid=CL_CRM_COUNTY,CL_CRM_COUNTRY,CL_CRM_CITY
@caption Asukoht

@reltype FIELD value=6 clid=CL_META
@caption Valdkond

*/

class personnel_management_job_offer extends class_base
{
	function personnel_management_job_offer()
	{
		// change this to the folder under the templates folder, where this classes templates will be,
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/personnel_management/personnel_management_job_offer",
			"clid" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			case "toolbar":
				$this->toolbar($arr);
				break;

			case "candidate_toolbar":
				$this->candidate_toolbar($arr);
				break;

			case "candidate_table":
				$this->candidate_table($arr);
				break;
			
			case "profession":
				if($this->can("view", $this->owner_org))
				{
					$section_inst = get_instance(CL_CRM_SECTION);
					$prop["options"] = $section_inst->get_all_org_proffessions($this->owner_org);
				}
				break;
				
			case "location":
				$objs = new object_list(array(
					"class_id" => CL_CRM_COUNTY,
				));
				$prop["options"] = array(0 => t("--vali--")) + $objs->names();
				break;
		}
		return $retval;
	}

	function candidate_toolbar($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "add",
			"tooltip" => t("Lisa uus kandideerija"),
			"img" => "new.gif",
			"url" => $this->mk_my_orb("new", array("alias_to" => $arr["obj_inst"]->id(), "reltype" => 1, "parent" => $arr["obj_inst"]->id(), "return_url" => get_ru()), CL_PERSONNEL_MANAGEMENT_CANDIDATE),
		));
	}

	function toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"img" => "save.gif",
			"action" => "",
		));
		if(!$arr["new"])
		{
			$tb->add_button(array(
				"name" => "preview",
				"tooltip" => t("Eelvaade"),
				"img" => "preview.gif",
				"url" => $this->mk_my_orb("show", array("id" => $arr["obj_inst"]->id())),
				"target" => "_blank",
			));
			$tb->add_button(array(
				"name" => "genpdf",
				"img" => "pdf_upload.gif",
				"tooltip" => t("Genereeri PDF"),
				"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id(), "oid" => $arr["obj_inst"]->id())),
				"target" => "_blank",
			));
		}
	}
	
	function candidate_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
				
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "person",
			"caption" => t("Isik"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuupäev"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "intro",
			"caption" => t("Kaaskiri"),
			"sortable" => 1,
		));
			
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CANDIDATE")) as $candidate)
		{
			$obj = $candidate->to();
			$id = $obj->id();
			$person = $obj->get_first_obj_by_reltype("RELTYPE_PERSON");
			$intro = $obj->prop("intro");
			$file = $obj->prop("intro_file");
			$fid = reset($file);
			if($this->can("view", $fid))
			{
				$file = obj($fid);
				$file_inst = get_instance(CL_FILE);
				$intro_url = html::href(array(
					"caption" => t("kaaskiri"),
					"url" => $file_inst->get_url($fid, $file->name()),
				));
			}
			elseif(!empty($intro))
			{
				$intro_url = html::href(array(
					"caption" => t("kaaskiri"),
					"url" => $this->mk_my_orb("view_intro", array("id" => $id), CL_PERSONNEL_MANAGEMENT_CANDIDATE),
					"target" => "_blank",
				));
			}
			else
			{
				$intro_url = t("Puudub");
			}
			$t->define_data(array(
				"name" => html::get_change_url($id, array("return_url" => get_ru()), $obj->name()),
				"person" => html::href(array(
					"caption" => $person->prop("firstname")." ".$person->prop("lastname"),
					"url" =>  html::get_change_url($person->id(), array("group" => "cv_view", "return_url" => get_ru())),
				)),
				"date" => get_lc_date($obj->created()),
				"id" => $id,
				"intro" => $intro_url,
			));
		}
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
	
	/**
		@attrib name=show nologin=1
		@param id required type=int
	**/
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
		
		$company = $ob->get_first_obj_by_reltype("RELTYPE_ORG");
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
		@attrib name=gen_job_pdf nologin=1
		@param id required type=int
	**/
	function gen_job_pdf($arr)
	{
		$job = obj($arr["id"]);
		$pdf_gen = get_instance("core/converters/html2pdf");
		session_cache_limiter("public");
		die($pdf_gen->gen_pdf(array(
			"filename" => $arr["id"],
			"source" => $this->show(array(
				"id" => $arr["id"]
			))
		)));
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

	function request_execute($arr)
	{
		$args = $_REQUEST;
		$done = aw_global_get("pk_been_here");
		if ($done)
		{
			return false;
		};
		aw_global_set("pk_been_here",1);
		$args["id"] = $arr->id();
		$rv = $this->show($args);
		return $rv;
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
}
?>
