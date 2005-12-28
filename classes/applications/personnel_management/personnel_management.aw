<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management.aw,v 1.11 2005/12/28 11:07:24 ahti Exp $
// personnel_management.aw - Personalikeskkond 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT relationmgr=yes no_status=1

@default table=objects
@default group=general

-----------------MANAGER PROFILE--------------------
@property my_manager_profile_persons_tb type=toolbar no_caption=1 group=manager_profile_persons
@property my_manager_profile_orgs_tb type=toolbar no_caption=1 group=manager_profile_orgs


-----------------MY PROFILE PROPERTIES--------------
@property my_profile_mycvs_tb type=toolbar no_caption=1 group=my_profile_mycvs
@property my_profile_candits_tb type=toolbar no_caption=1 group=my_profile_candits

@property my_profile_cvtable type=table no_caption=1 group=my_profile_mycvs
@property my_profile_candits_table type=table no_caption=1 group=my_profile_candits group=my_profile_candits

-----------------MY ORG PROFILE--------------------
@property org_profile_jobs_tb type=toolbar group=org_profile_jobs store=no no_caption=1
@property org_profile_jobs_candits_tb type=toolbar group=org_profile_candits store=no no_caption=1

@property org_profile_jobtable type=table group=org_profile_jobs no_caption=1
@property org_profile_candits type=table group=org_profile_candits no_caption=1

@property my_join_worker type=callback group=my_join_worker,my_profile_personal  callback=callback_get_join_worker store=no
@property my_join_offerer type=callback group=my_join_offerer,org_profile_info callback=callback_get_join_offerer store=no

-------------------TÖÖOTSIJAD-----------------------
@property treeview_person type=text store=no group=employee no_caption=1
@property personlist type=table store=no group=employee no_caption=1

-------------------TÖÖPAKKUMISED---------------------
property manager type=text no_caption=1 store=no wrapchildren=1 group=employers
@property treeview type=text store=no group=employers no_caption=1
@property joblist type=table store=no group=employers no_caption=1

-----------------TAB DEFINTIONS--------------------
@groupinfo my_profile caption="Minu profiil" tabgroup=navi
@groupinfo org_profile caption="Tööpakkuja profiil"
@groupinfo employee caption="Tööotsijad" submit=no
@groupinfo employers caption="Tööpakkumised" submit=no
@groupinfo managers caption="Halduri profiil"

@groupinfo my_profile_mycvs caption="Minu CV-d" parent=my_profile submit=no tabgroup=navi
@groupinfo my_profile_candits caption="Kandideerin" parent=my_profile submit=no tabgroup=navi
@groupinfo my_profile_personal caption="Minu andmed" parent=my_profile tabgroup=navi

@groupinfo org_profile caption="Tööpakkuja profiil" submit=no
@groupinfo org_profile_jobs caption="Tööpakkumised" parent=org_profile submit=no
@groupinfo org_profile_candits caption="Kandideerijad" parent=org_profile
@groupinfo org_profile_info caption="Tööpakkuja andmed" parent=org_profile

@groupinfo all_setings caption="Seadistused"
@groupinfo dir_setings caption="Kaustade seaded" parent=all_setings
@groupinfo layout_setings caption="Seaded" parent=all_setings
@groupinfo env_setings caption="Keskkonna seaded" parent=all_setings
@groupinfo education_setings caption="Haridus seaded" parent=all_setings

@groupinfo my_join caption="Registreeru" tabgroup=left
@groupinfo my_join_worker caption="T&ouml;&ouml;otsija" parent=my_join tabgroup=left
@groupinfo my_join_offerer caption="T&ouml;&ouml;pakkuja" parent=my_join tabgroup=left

@groupinfo manager_profile caption="Halduri profiil"
@groupinfo manager_profile_persons caption="Tööotsijad" parent=manager_profile
@groupinfo manager_profile_orgs caption="Tööpakkujad" parent=manager_profile

@groupinfo search caption="Otsi"
@groupinfo search_cv caption="Otsi CV'sid"
@groupinfo search_offer caption="Otsi pakkumisi"

--------------------PROPERTIES----------------------
@property cv_acitvity_prop type=chooser orient=vertical table=objects group=env_setings store=no
@caption CVde aktiivsus

@property max_active_cv type=textbox size=3 group=env_setings store=no
@caption CV maksimaalselt aktiivne(päevades)

@property max_active_cv_def type=textbox size=3 group=env_setings store=no
@caption CV maksimaalselt aktiivne(päevades)

@property max_active_job type=textbox size=3 group=env_setings store=no
@caption Tööpakkumine maksimaalselt aktiivne(päevades) 

--------------------EDUCATION PROPERTIES----------------
@property education_types type=text store=no group=education_setings subtitle=1
@caption Vali milliseid hariduse liike saab lisada

@property list_of_education_cfgform type=table store=no no_caption=1 group=education_setings

@property additional_edu type=select group=education_setings store=no
@caption Täienduskoolituse vorm
-------------------------------------------------------------

@property orgs type=relpicker group=dir_setings table=objects method=serialize field=meta reltype=RELTYPE_MENU
@caption Organisatsioonide kaust

@property persons type=relpicker group=dir_setings table=objects method=serialize field=meta reltype=RELTYPE_MENU
@caption Isikute kaust

@property cvparent type=relpicker group=dir_setings table=objects method=serialize field=meta reltype=RELTYPE_MENU
@caption CV-de kaust

@property offers type=relpicker group=dir_setings table=objects method=serialize field=meta reltype=RELTYPE_MENU
@caption Tööpakkumiste kaust

@property tegevusvaldkonnad type=relpicker group=dir_setings reltype=RELTYPE_SECTORS method=serialize field=meta group=dir_setings
@caption Tegevusvaldkondade kaust

@property join_obj_worker type=relpicker reltype=RELTYPE_JOIN_OBJ method=serialize field=meta group=layout_setings
@caption T&ouml;&ouml;taja liitumisvorm

@property join_obj_offerer type=relpicker reltype=RELTYPE_JOIN_OBJ method=serialize field=meta group=layout_setings
@caption T&ouml;&ouml;pakkuja liitumisvorm

@property locations type=callback callback=callback_get_locations group=layout_setings field=meta method=serialize
@caption Asukohad

--------------- SEARCH -----------------

// CV
@property search_cv type=form group=search_cv sclass=applications/personnel_management/personnel_management_cv_search sform=cv_search store=no
@caption Otsi CV-sid

property search_cv_name type=textbox group=search_cv store=no
caption Otsi nimest

property search_cv_results type=table group=search_cv store=no
caption Otsingu tulemused

// OFFER 
property search_offer_name type=textbox group=search_offer store=no
caption Otsi nimest

property search_offer_results type=table group=search_offer store=no
caption Otsingu tulemused


---------------RELATION DEFINTIONS-----------------
@reltype MENU value=1 clid=CL_MENU
@caption Kaust

@reltype SECTORS value=20 clid=CL_META
@caption Tegevusvaldkonnad

@reltype JOIN_OBJ value=21 clid=CL_JOIN_SITE
@caption liitumisvorm

@reltype CONTENT value=22 clid=CL_MENU_AREA,CL_POLL,CL_PROMO
@caption Sisuelement

@reltype LAYOUT_BACKGROUND value=23 clid=CL_IMAGE
@caption Kujunduse taustapilt

@reltype LAYOUT_LOGO value=24 clid=CL_IMAGE
@caption Kujunduse logo
*/

class personnel_management extends class_base
{
	var $my_profile;

	function personnel_management()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_PERSONNEL_MANAGEMENT
		));
		if (!aw_global_get("no_db_connection"))
		{
			$this->my_profile = $this->get_my_profile();
		}
	}


	function callback_on_load($arr)
	{
		$this->cfgmanager = $this->cfg["configform_manager"];
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
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]), CL_PERSONNEL_MANAGEMENT);
	}

	/**
		@attrib name=set_default_cv
	**/
	function set_default_cv($arr)
	{
		$this->my_profile["person_obj"]->set_prop("default_cv", $arr["actcv"]);
		$this->my_profile["person_obj"]->save();
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]), $arr["class"]);
	}

	/**
		@attrib name=view_letter all_args="1"
	**/
	
	function view_letter($arr)
	{
		$rel_obj = &obj($arr["id"]);
		echo $rel_obj->meta("kaaskiri");	
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "tabpanel":
				if($arr["new"])
				{
					return $retval;
				}
				$logos = $arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_LAYOUT_LOGO",
				));
				$first_logo = reset($logos);
				
				$bgs = $arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_LAYOUT_BACKGROUND",
				));
				$first_bg = reset($bgs);
				if($first_logo && $first_bg)
				{
					$t = get_instance(CL_IMAGE);
					$prop["vcl_inst"]->set_style("with_logo");
					$prop["vcl_inst"]->configure(array(
						"logo_image" => $t->get_url_by_id($first_logo->prop("to")),
						"background_image" => $t->get_url_by_id($first_bg->prop("to")),
					));
				}
						
			break;
			
			case "additional_edu":
				$educaton_cfg_form = new object_list(array(
					"class_id" => CL_CFGFORM,
					"subclass" => CL_EDUCATION,
				));
		
				$ch_values = $this->my_profile["org_obj"]->meta("add_edu_form");
				$prop["options"][] = " ";
				foreach ($educaton_cfg_form->arr() as $eduform)
				{
					$prop["options"][$eduform->id()] = $eduform->name();
				}
				$prop["value"] = $ch_values;
			break;
			
			case "list_of_education_cfgform":
				$this->do_list_of_education_cfgform($arr);
			break;
			
			case "max_active_cv":
				$prop["value"] = $this->my_profile["org_obj"]->meta("max_active_cv");
				if($arr["obj_inst"]->prop("cv_acitvity_prop"))
				{
					return PROP_IGNORE;
				}
			break;
			
			case "max_active_cv_def":
				$prop["value"] = $this->my_profile["org_obj"]->meta("max_active_cv_def");
				if(!$arr["obj_inst"]->prop("cv_acitvity_prop"))
				{
					return PROP_IGNORE;
				}
			break;
	
			case "max_active_job":
				$prop["value"] = $this->my_profile["org_obj"]->meta("max_active_job");
			break;
			
			case "cv_acitvity_prop":
				$prop["value"] = $this->my_profile["org_obj"]->meta("cv_acitvity_prop");
				$prop["options"] = array(t("Tööotsija saab CV tähtaega ise määrata"), t("Tööotsija ei saa CV tähtaega ise määrata"));
			break;
			
			case "my_manager_profile_persons_tb":
				$this->do_my_manager_profile_persons_tb($arr);
			break;
			
			case "my_profile_cvtable":
				$this->do_my_profile_cvtable($arr);
			break;
			
			case "my_profile_mycvs_tb":
				$this->my_profile_mycvs_tb($arr);
			break;
			
			case "my_profile_candits_tb":
				$this->my_profile_candits_tb($arr);
			break;
			
			case "my_profile_candits_table":
				$this->my_profile_candits_table($arr);
			break;
			
			case "org_profile_jobtable":
				$this->do_org_profile_jobtable($arr);
			break;
			
			case "org_profile_candits":
				$this->do_org_profile_candits($arr);
			break;

			case "org_profile_jobs_tb":
				$this->do_org_profile_jobs_tb($arr);
			break;
			
			case "org_profile_jobs_candits_tb":
				$this->do_org_profile_candits_tb($arr);
			break;
			
			case "treeview":
				$this->do_jobcats_tree($arr);
			break;
			
			case "treeview_person":
				$this->do_jobcats_tree($arr);
			break;
			
			case "joblist":
				$this->do_joblist($arr);
			break;
            
            case "personlist":
            	$this->do_personlist_table($arr);
            break;
		};
		return $retval;
	}
	
	
	function do_my_manager_profile_persons_tb($arr)
	{
		$tb = &$arr["prop"]["toolbar"];
	
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta kandidaadid"),
			"action" => "delete_rels",
		));
	}
	
	function do_personlist_table($arr)
	{

		$gidlist = aw_global_get("gidlist_oid");
		if(array_search(aw_ini_get("personnel_management.unloged_users") , $gidlist))
		{
			$name_caption = t("Kood");
		}
		else
		{
			$name_caption = t("Nimi");
		}

		$table = &$arr["prop"]["vcl_inst"];

		$table->define_field(array(
				"name" => "nimi",
				"caption" => $name_caption,
				"sortable" => 1,
		));

		$table->define_field(array(
				"name" => "modified",
				"caption" => t("Muudetud"),
				"sortable" => 1,
		));	
		
		$table->define_field(array(
				"name" => "synd",
				"caption" => t("Sündinud"),
				"sortable" => 1,
		));

	
		$manager = current($this->my_profile["manager_list"]);
		if (!is_object($manager))
		{
			return 0;
		}

		$user_inst = get_instance(CL_USER);
		
		foreach ($manager->connections_from(array("type" => "RELTYPE_TOOTSIJA")) as $tootu)
		{
			$tootu_ids[] = $tootu->prop("to");
		}
		
		$tootsijad_obj_list = new object_list(array(
			"oid" => $tootu_ids,
			"class_id" => CL_CRM_PERSON,	
		));
		
		$tootsijad_obj_list = $tootsijad_obj_list->arr();
		
		
		foreach ($tootsijad_obj_list as $tootu)
		{
			if(!$tootu->prop("default_cv"))
			{
				continue;
			}
			$cv_ids[] = $tootu->prop("default_cv");
			$userdata[$tootu->id()]["cv_id"] = $tootu->prop("default_cv");
			$userdata[$tootu->id()]["person"] = $tootu;
			
		}
				
		$cvs_obj_list = new object_list(array(
			"oid" => $cv_ids,
			"class_id" => CL_PERSONNEL_MANAGEMENT_CV,
			"status" => STAT_ACTIVE,
		));
		$cvs_obj_list = $cvs_obj_list->arr();
		
		foreach ($userdata as $key => $persondata)
		{
			$userdata[$key]["cv_obj"]=$cvs_obj_list[$persondata["cv_id"]];
		}
		
		foreach ($userdata as $data)
		{
			if($data["cv_obj"])
			{
		
				if($_GET["sector_id"])
				{
					$toosoovid = $data["cv_obj"]->connections_from(array("type" => 10));

					foreach ($toosoovid as $toosoov)
					{
						$toosoovid_ids[] = $toosoov->prop("to");
					}
					
					$toosoov_sector_conns = new connection(); 
					$toosoov_sector_conns = $toosoov_sector_conns->find(array(
						"from" => $toosoovid_ids,
						"to" => $_GET["sector_id"],
					));
						
					if($toosoov_sector_conns)
					{
						$count = true;
					}
					else
					{
						$count = false;
					}
				}
				else
				{
					$count = true;
				}

				if($count)
				{
					if($data["person"]->prop("birthday"))
					{
						$synd = get_lc_date($data["person"]->prop("birthday"));
					}

					$gidlist = aw_global_get("gidlist_oid");

					if(array_search($this->cfg["unloged_users"] , $gidlist))
					{
						$personname = $data["person"]->id();
					}
					else
					{
						$personname = $data["person"]->name();
					}

					$table->define_data(array(
						"nimi" => html::href(array(
							"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $data["cv_obj"]->id()), CL_PERSONNEL_MANAGEMENT_CV, false, true). "\",\"cv\",800,600)",
							"caption" => $personname,
						)),
						"modified" => get_lc_date($data["cv_obj"]->modified()),
						"synd" => $synd,
					));
				}
				$count = false;
			}
		}
		/*
		foreach ($tootsijad_obj_list as $tootu)
		{
			$default_cv = $tootu->prop("default_cv");
			
			if(!$default_cv)
			{
				continue;
			}
			
			$default_cv_obj = &obj($default_cv);
			if($default_cv_obj->status() == STAT_ACTIVE)
			{
				// $date is for the cv , not the current person

				$u = get_instance("users");
				$creator = obj($u->get_oid_for_uid($default_cv_obj->createdby()));
				$_person_o = obj($user_inst->get_person_for_user($creator));
				if ($_person_o->prop("birthday") < 100)
				{
					$date = "";
				}
				else
				{
					$date = get_lc_date($_person_o->prop("birthday"));
				}

				$job_cat_conns = new connection();
						
				$job_cat_conns = $job_cat_conns->find(array(
					"from" => $default_cv_obj->id(),
					"to" => $_GET["sector_id"],
				));
				
				if($job_cat_conns)
				{
					
					$table->define_data(array(
						"nimi" => html::href(array(
							"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $default_cv_obj->id()), CL_PERSONNEL_MANAGEMENT_CV, true, true). "\",\"cv\",800,600)",
							"caption" => $tootu->name(),
						)),
						"modified" => get_lc_date($default_cv_obj->modified()),
						"synd" => $date,
					));
				}		
			}	
		}*/
	}
	
	function do_list_of_education_cfgform($arr)
	{
		
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "education_type",
			"caption" => t("Haridusliik"),
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "education_check",
			"caption" => t("X"),
			"sortable" => 1,
		));
		
		$educaton_cfg_form = new object_list(array(
			"class_id" => CL_CFGFORM,
			"subclass" => CL_EDUCATION,
		));
		
		$ch_values = $this->my_profile["org_obj"]->meta("education_types");
	
		foreach ($educaton_cfg_form->arr() as $eduform)
		{
			$id = $eduform->id();
			$table->define_data(array(
				"education_type" => $eduform->name(),
				"education_check" => html::checkbox(array(
					"name" => "edu_cfg_form[$id]",
					"checked" => $ch_values[$id],
				)),
			));
		}
	}

	function do_joblist($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "amet",
			"caption" => t("Ametinimetus"),
			"sortable" => 1,
		));

		$table->define_field(array(
			"name" => "pakkuja",
			"caption" => t("Tööpakkuja"),
			"sortable" => 1,
		));

		$table->define_field(array(
			"name" => "asukoht",
			"caption" => t("Asukoht"),
			"sortable" => 1,

		));

		$table->define_field(array(
			"name" => "deadline",
			"caption" => t("Tähtaeg"),
			"sortable" => 1,

		));

		$manager = current($this->my_profile["manager_list"]);
		if  (!is_object($manager))
		{
			return 0;
		}

		$toopakkujad_ids = array();
		$toopakkujad = array();

		foreach($manager->connections_from(array("type" => "RELTYPE_TOOPAKKUJA")) as $toopakkuja)
		{
			array_push($toopakkujad_ids, $toopakkuja->prop("to"));
			$toopakkujad[$toopakkuja->prop("to")]["name"]=$toopakkuja->prop("to.name");
		}

		$jobs_conn = new connection();
		$jobs_list = $jobs_conn->find(array(
			"from" => $toopakkujad_ids,
			"type" => 19, //RELTYPE_JOBS,
			"to.status" => STAT_ACTIVE,
			//"deadline" => new obj_predicate_compare(OBJ_COMP_LESS, time()),
		));

		foreach ($jobs_list as $job)
		{
			$job_data[$job["to"]]["company"] = $job["from.name"];
			$job_ids[] = $job["to"];
		}

		if($_GET["sector_id"])
		{
			$job_sector_conns = new connection();

			$job_sector_list = $job_sector_conns->find(array(
				"from" => $job_ids,
				"to" => $_GET["sector_id"],
			));

			$job_sector_list;
			$job_ids = "";

			//Mingi anomaalia aga foreach ei taha selle masiiviga töödata.
			while($job_sector_list)
			{
				$temp = array_pop($job_sector_list);
				$job_ids[] = $temp["from"];
			}
		}

		$job_ob_list = new object_list(array(
        	"status" => STAT_ACTIVE,
        	"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
        	"oid" => $job_ids,
        	"deadline" => new obj_predicate_compare(OBJ_COMP_GREATER, time()),
        ));

        $city_conns = new connection();

        $city_conns = $city_conns->find(array(
        	"from" => $job_ob_list->ids(),
        	"type" => 4,
        ));

        foreach($city_conns as $city_conn)
        {
        	$city_data[$city_conn["from"]] = $city_conn["to.name"];
        }



        foreach ($job_ob_list->arr() as $job)
		{

			if($job->prop("deadline"))
			{
        		$deadline = get_lc_date($job->prop("deadline"));
        	}
        	else
        	{
        		$deadline = t("Määramata");
        	}

			$table->define_data(array(
				"amet" => html::href(array(
							"caption" => $job->prop("name"),
							"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $job->id()) , CL_PERSONNEL_MANAGEMENT_JOB_OFFER, false, true). "\",\"cv\",800,600)",
				)),

				"pakkuja" => $job_data[$job->id()]["company"],
				"asukoht" => $city_data[$job->id()],
				"deadline" => $deadline,
				"created" => $job->created(),
			));
		}

		$table->set_default_sortby("created");
		$table->set_default_sorder("desc");
		$table->sort_by();
	}
	
	function count_sector_cvs(&$arr, $sector_id)
	{
		$manager = current($this->my_profile["manager_list"]);		
		if (!is_object($manager))
		{
			return 0;
		}
		
		foreach($manager->connections_from(array("type" => "RELTYPE_TOOTSIJA")) as $person)
		{
			$person_ids[] = $person->prop("to");
		}
		
		if(!$person_ids)
		{
			return 0;
		}
		
		$person_obj_list = new object_list(array(
			"oid" => $person_ids,
			"class_id" => CL_CRM_PERSON,
		));
				
		foreach($person_obj_list->arr() as $person)
		{
			if($person->prop("default_cv"))
			{
				$default_cv_ids[] = $person->prop("default_cv");
			}
		}
		
		if(!$default_cv_ids)
		{
			return 0;
		}
		
		$jobs_wanted_conns = new connection();
		
		$jobs_wanted_conns = $jobs_wanted_conns->find(array(
			"from" => $default_cv_ids,
			"to.class_id" => CL_PERSONNEL_MANAGEMENT_JOB_WANTED,
		));
		
		foreach ($jobs_wanted_conns as $wanted)
		{
			$jobs_wanted_ids[] = $wanted["to"];
		}
		
		if(!$jobs_wanted_ids)
		{
			return 0;
		}
		
		$job_cat_conns = new connection();				
		$job_cat_conns = $job_cat_conns->find(array(
			"from" => $jobs_wanted_ids,
			"to" => $sector_id,
		));
		
		$counter = count($job_cat_conns);
		if(!$counter)
		{
			return "0";
		}
		return $counter;
	}
	
	
	function count_sector_jobs(&$arr, $sector_id)
	{
		$manager = current($this->my_profile["manager_list"]);		
		if (!is_object($manager))
		{
			return 0;
		}
		
		foreach ($manager->connections_from(array("type" => "RELTYPE_TOOPAKKUJA")) as $toopakkuja)
		{
			$toopakkujad_ids[] = $toopakkuja->prop("to");
		}
		
		$job_conns_list = new connection();
		$job_conns_list = $job_conns_list->find(array(
			"from" => $toopakkujad_ids,
			"to.class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			"to.status" => STAT_ACTIVE,
		));
		
		foreach ($job_conns_list as $job)
		{
			$jobs_ids[] = $job["to"];
		}
		
		$cat_jobs = new connection();
		$cat_jobs = $cat_jobs->find(array(
			"from" => $jobs_ids,
			"to" => $sector_id,
		));
		
		$counter = count($cat_jobs);
		if(!$counter)
		{
			return "0";
		}
		return $counter;	
	}
	
	function do_jobcats_tree(&$arr)
	{
		$tree = get_instance("vcl/treeview");
			
		$tree->start_tree(array(
    		"type" => TREE_DHTML,
    		"root_name" => t("AutomatWeb"),
    		"root_url" => $this->mk_my_orb("root_action",array()),
		));
				
		$tegevusalad = new object_tree(array(
    		"class_id" => CL_META,
    		"parent" => $arr["obj_inst"]->prop("tegevusvaldkonnad"),
		));
		
		$tegevusalad = $tegevusalad->to_list();
		
		foreach ($tegevusalad->arr() as $tegevusala)
		{		
			if($tegevusala->prop("parent")==$arr["obj_inst"]->prop("tegevusvaldkonnad"))
			{
				$parent = 0;
			}
			else
			{
				$parent = $tegevusala->prop("parent");
			}
			
			if($arr["request"]["group"]=="employers")
			{
				$count = $this->count_sector_jobs($arr, $tegevusala->id());
			}
			else
			{
				$count = $this->count_sector_cvs($arr, $tegevusala->id());
			}
			
			$tree->add_item($parent, array(
				"name" =>	$tegevusala->name() ."($count)",
    		    "id" =>		$tegevusala->id(),
    	        "url" =>	$this->mk_my_orb("change", array( 
    	                  				"sector_id" => $tegevusala->id(), 
	                      				"group" => $arr["request"]["group"], 
	                      				"id"=> $arr["obj_inst"]->id(),
								), "personnel_management", false, false)
			));
		}
		$arr["prop"]["value"] = $tree->finalize_tree();
	}
	
	
	function do_org_profile_candits_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];
	
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta kandidaadid"),
			"action" => "delete_rels",
		));
	}
	
	function do_org_profile_jobs_tb(&$arr)
	{

		$tb = &$arr["prop"]["toolbar"];
			
		$tb->add_menu_button(array(
   			"name" => "new",
   			"img" => "new.gif",
   			"tooltip" => t("Uus"),
		));
			
		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("Tööpakkumine"),
			"title" => t("Tööpakkumine"),
			"url" => "javascript:aw_popup_scroll('".$this->mk_my_orb("new", array(), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true)."','cv',800,600)"//,
		));
				
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta tööpakkumised"),
			"action" => "delete_rels",
		));	
	}
	
	
	function do_my_profile_cvtable(&$arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "default",
			"caption" => t("Default cv"),
		));
		
		$table->define_field(array(
			"name" => "nimi",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
				
		$table->define_field(array(
			"name" => "active",
			"caption" => t("Aktiivne kuni"),
			"sortable" => 1,
			"align" => "center",
		));
			
		$table->define_field(array(
			"name" => "muudetud",
			"caption" => t("Muudetud"),
			"sortable" => 1,
			"align" => "center"
		));	

			
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
			"align" => "center",
		));
		
		foreach ($this->my_profile["person_obj"]->connections_from(array("type" => "RELTYPE_CV")) as $mycv)
		{
			$mycvconn_id = $mycv->id();
			$mycv = $mycv->to();
			
			if($mycv->id() == $this->my_profile["person_obj"]->prop("default_cv"))
			{
				$checked = true;
			}
			
			$table->define_data(array(
				"default" => html::radiobutton(array(
								"name" => "actcv",
								"value" => $mycv->id(),
								"checked" => $checked,
							)),
				"nimi" => html::href(array(
					"caption" => $mycv->name(),
					"url" => "javascript:aw_popup_scroll(\"". $this->mk_my_orb("change", array("id" => $mycv->id()), "personnel_management_cv", true, true). "\",\"cv\",800,600)",
				)),
				"active" => get_lc_date($mycv->prop("active_until")),
				"muudetud" => get_lc_date($mycv->modified()),
				"from" => $mycvconn_id,

			));
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
	
	function do_org_profile_candits($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "job",
			"caption" => t("Tööpakkumine"),
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "deadline",
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
			"align" => "center",
		));
		
		foreach ($this->my_profile["org_obj"]->connections_from(array("type" => "RELTYPE_JOBS")) as $job)
		{
			$job = $job->to();
			foreach ($job->connections_from(array("type" => 5)) as $cv)
			{
				$cv_connid = $cv->id();
							
				$cvcreated = $cv->prop("created");
				
				$rel_obj = obj($cv->prop("relobj_id"));
				
				$cv_rel_created = $rel_obj->created();
				
				$cv = $cv->to();
				
				if($rel_obj->meta("kaaskiri"))
				{
					$kaaskiri_url = html::href(array(
						"caption" => t("kaaskiri"),
						"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("view_letter", array("id" => $rel_obj->id()), CL_PERSONNEL_MANAGEMENT, false, true). "\",\"letter\",500,200)",
					));
				}
				else
				{
					$kaaskiri_url = "Puudub";
				}
				
				foreach ($cv->connections_to(array("from.class_id" => CL_CRM_PERSON)) as $person)
				{
					$table->define_data(array(
						"name"  => html::href(array(
							"caption" => $person->prop("from.name"),
							"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $cv->id()) , CL_PERSONNEL_MANAGEMENT_CV, true, true)."\",\"cv\",800,600)",
						)),
						"job" 	=> html::href(array(
									"caption" => $job->name(),
									"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true)."\",\"cv\",800,600)",
									)),
						"deadline" => get_lc_date($cv_rel_created),
						"from" => $cv_connid,
						"to" => $cv_connid,
						"hinne" => $rel_obj->meta("hinne"),
						"kaaskiri" => $kaaskiri_url,
					));
				}
			}
		}
		
		$table->set_default_sortby("hinne");
		$table->set_default_sorder("desc");
		$table->sort_by();
	}

	function my_profile_candits_table($arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "organisatsioon",
			"caption" => t("Organisatsioon"),
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => t("Ametikoht"),
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "deadline",
			"caption" => t("Kandideerimise lõpptähtaeg"),
			"sortable" => 1,
		));
		
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
			"align" => "center",
		));

		foreach ($this->my_profile["person_obj"]->connections_from(array("type" => "RELTYPE_CV")) as $cv)
		{
			$cvconnn_id = $cv->id();
			$cv = $cv->to();

			foreach ($cv->connections_to(array("from.class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER)) as $job)
			{		
				$job = &obj($job->prop("from"));
			
				foreach ($job->connections_to(array("from.class_id" => CL_CRM_COMPANY)) as $company)
				{

					$table->define_data(array(
						"ametikoht" => html::href(array(
								"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true)."\",\"cv\",800,600)",
								"caption" => $job->name(),
								)),
						"deadline" => get_lc_date($job->prop("deadline")),
						"organisatsioon" => $company->prop("from.name"),
						"from" => $cvconnn_id,
					));
					
				}
			}
		}
		
	}
	
	function my_profile_mycvs_tb($arr)
	{
		$tb = &$arr["prop"]["toolbar"];
		
		$tb->add_menu_button(array(
    		"name" => "new",
    		"img" => "new.gif",
    		"tooltip" => t("Uus CV"),
		));
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta CV-d"),
			"action" => "delete_rels",
			"confirm" => t("Kas soovid valitud objektid kustutada?"),
		));
		
		$tb->add_button(array(
			"name" => "Save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta"),
			"action" => "set_default_cv",
		));
		
		$tb->add_menu_item(array(
    		"parent" => "new",
    		"text" => t("CV"),
    		"title" => t("CV"),
    		"url" => "javascript:aw_popup_scroll('".$this->mk_my_orb("new", array(), "personnel_management_cv", true, true). "','cv',800,600)",
    		"disabled" => false,
		));
	}
	
	function my_profile_candits_tb($arr)
	{
		$tb = &$arr["prop"]["toolbar"];
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta CV-d"),
			"action" => "delete_rels",
			"confirm" => t("Kas soovid valitud objektid kustutada?"),
		));
	}

	function do_org_profile_jobtable($arr)
	{
		$table =& $arr["prop"]["vcl_inst"];
			
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => t("Ametikoht"),
			"sortable" => 1,
		));
			
		$table->define_field(array(
			"name" => "deadline",
			"caption" => t("Tähtaeg"),
			"sortable" => 1,
			"align" => "center"
		));
		
		$table->define_field(array(
			"name" => "kandidaate",
			"caption" => t("Kandidaate"),
			"sortable" => 1,
			"align" => "center"
		));
		
		$table->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"sortable" => 1,
		));
			
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
			"align" => "center",
		));

		foreach ($this->my_profile["org_obj"]->connections_from(array("type" => "RELTYPE_JOBS")) as $job)
		{
			$job_conn_id = $job->id();
			$job = $job->to();
			$candits_count = count(($job->connections_from(array("type" => 5))));
			
			$table->define_data(array(
					"ametikoht" =>	html::href(array(
										"caption" => $job->name(),
										"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true)."\",\"cv\",800,600)",
									)),
					"deadline" => get_lc_date($job->prop("deadline")),
					"kandidaate" => html::href(array(
										"caption" => $candits_count,
										"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $job->id(), "group" => "kandideerinud"), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true)."\",\"cv\",800,600)",
									)),
					"status" => ($job->status() == STAT_ACTIVE)?t("Aktiivne"):t("Mitteaktiivne"),
					"from" => $job_conn_id
			));
		}
		
	}
	
	
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{			
			case "org_profile_candits":
				foreach ($arr["request"]["hinne"] as $key => $value)
				{
					$conn = new connection($key);
					$relobject = &obj($conn->prop("relobj_id"));
					$relobject->set_meta("hinne", $value);
					$relobject->save();
				}
			break;
			
			case "additional_edu":
				$this->my_profile["org_obj"]->set_meta("add_edu_form", $arr["request"]["additional_edu"]);	
				$this->my_profile["org_obj"]->save();
			break;
			
			case "list_of_education_cfgform":
				$this->my_profile["org_obj"]->set_meta("education_types", $arr["request"]["edu_cfg_form"]);
				$this->my_profile["org_obj"]->save();
			break;
			
			case "my_join_offerer":
				$j_oid = $arr["obj_inst"]->prop("join_obj_offerer");
				if ($j_oid)
				{
					$tmp = $arr["request"];
					$tmp["id"] = $j_oid;
					if (!empty($arr["request"]["join_butt"]))
					{
						$ji = get_instance(CL_JOIN_SITE);
						$url = $ji->submit_join_form($tmp);
		
						if ($ji->join_done)
						{
							//Temporay solution
							$my_profile = $this->get_my_profile();
							$haldur = &obj(aw_ini_get("personnel_management.default_manager"));
					
							$haldur->connect(array(
								"to" => $my_profile["org_obj"]->id(),
								"reltype" => 20
							));
						}

						if ($url != "")
						{
							header("Location: $url");
							die();
						}
					}
					else
					if (!empty($arr["request"]["upd_butt"]))
					{
						$ji = get_instance(CL_JOIN_SITE);
						$ji->submit_update_form($tmp);
					}
				}
			break;

			case "my_join_worker":
				$j_oid = $arr["obj_inst"]->prop("join_obj_worker");
				if ($j_oid)
				{
					$tmp = $arr["request"];
					$tmp["id"] = $j_oid;
					if (aw_global_get("uid") == "")
					{
						$ji = get_instance(CL_JOIN_SITE);
						$url = $ji->submit_join_form($tmp);

						if ($ji->join_done)
						{
							//Temporay solution
							$my_profile = $this->get_my_profile();
							$haldur = &obj(aw_ini_get("personnel_management.default_manager"));

							$haldur->connect(array(
								"to" => $my_profile["person_obj"]->id(),
								"reltype" => 21
							));
						}

						if ($url != "")
						{
							header("Location: $url");
							die();
						}
					}
					else
					{
						$ji = get_instance(CL_JOIN_SITE);
						$ji->submit_update_form($tmp);
					}
				}
				break;

			case "search_cv_name":
				$this->url_data["search_cv_name"] = $prop["value"];
				echo "ud = ".dbg::dump($this->url_data)." <br>";
			break;
			
			case "max_active_cv":
				$this->my_profile["org_obj"]->set_meta("max_active_cv", $prop["value"]);
				$this->my_profile["org_obj"]->save();
			break;
			
			case "max_active_cv_def":
				$this->my_profile["org_obj"]->set_meta("max_active_cv_def", $prop["value"]);
				$this->my_profile["org_obj"]->save();
			break;

			case "max_active_job":
				$this->my_profile["org_obj"]->set_meta("max_active_job", $prop["value"]);
				$this->my_profile["org_obj"]->save();
			break;
			
			case "cv_acitvity_prop":
				$this->my_profile["org_obj"]->set_meta("cv_acitvity_prop", $prop["value"]);	
				$this->my_profile["org_obj"]->save();
			break;
		}
		
		return $retval;
	}	


	function get_employee_profile()
	{
		$employee_profile["group"] = "employee";
		$user_id = users::get_oid_for_uid(aw_global_get("uid"));
		
		$employee_profile["user_obj"] = & obj($user_id);
		
		$u_i = get_instance(CL_USER);
		$employee_profile["person_obj"] = obj($u_i->get_current_person());
		
		$manager_conns = new connection();
		
		$manager_conns_list = &$manager_conns->find(array(
    	    "to" => $employee_profile["person_obj"]->id(),
    	    "type" => 21,
		)); 
		
		$employee_profile["manager_list"] = array();
		foreach ($manager_conns_list as $manager_conn)
		{
			$employee_profile["manager_list"][] = &obj($manager_conn["from"]);
		}
		
		return $employee_profile;
	}
	
	function get_employer_profile()
	{
		$employer_profile["group"] = "employer";
		$user_id = users::get_oid_for_uid(aw_global_get("uid"));
		$employer_profile["user_obj"] = & obj($user_id);
		
		$u_i = get_instance(CL_USER);
		$employer_profile["person_obj"] = obj($u_i->get_current_person());

		$employer_profile["org_obj"] = obj($u_i->get_current_company());
		
		
		$manager_conns = new connection();
		$manager_conns_list = &$manager_conns->find(array(
    	    "to" => $employer_profile["org_obj"]->id(),
    	    "type" => 20,
		));
		
		$employer_profile["manager_list"] = array();
		foreach ($manager_conns_list as $manager_conn)
		{
			$employer_profile["manager_list"][] = &obj($manager_conn["from"]);
		}
		return $employer_profile;
	}
	
	function get_manager_profile()
	{
		$manager_profile["group"] = "manager";
		
		$user_id = users::get_oid_for_uid(aw_global_get("uid"));
		$manager_profile["user_obj"] = & obj($user_id);
		
		$u_i = get_instance(CL_USER);
	
		$manager_profile["person_obj"] = obj($u_i->get_current_person());
		
		$manager_profile["org_obj"] = obj($u_i->get_current_company());

		$manager_profile["manager_list"][] = &$manager_profile["org_obj"]; 	

		return $manager_profile;
	}
	
	function get_my_profile()
	{

		$gidlist = aw_global_get("gidlist_oid");

		if(array_search($this->cfg["employee_group"] , $gidlist))
		{
			return $this->get_employee_profile();
		}
		elseif(array_search($this->cfg["employer_group"] , $gidlist))
		{
			return $this->get_employer_profile();
		}
		elseif (array_search($this->cfg["unloged_users"] , $gidlist))
		{
			$unloged_profile["manager_list"][] = &obj($this->cfg["default_manager"]);
			return 	$unloged_profile;
		}
		else
		{
			// if nothing else, then assume manager
			return $this->get_manager_profile();
		}
	}
	
	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
	
	function parse_alias($arr)
	{
		// XXX: this is temporary
		$this->hide_general = true;
		$this->hide_relationmgr = true;
		$v_arr = $_GET;
		$v_arr["id"] = $arr["alias"]["target"];
		return $this->change($v_arr);
	}
	
	function callback_get_join_worker($arr)
	{
		aw_global_set("no_cache", 1);
		$j_oid = $arr["obj_inst"]->prop("join_obj_worker");
		if ($j_oid)
		{
			$join = obj($j_oid);
	
			$ji = get_instance(CL_JOIN_SITE);
			$pps = $ji->get_elements_from_obj($join, array(
				"err_return_url" => aw_ini_get("baseurl").aw_global_get("REQUEST_URI")
			));
			if (aw_global_get("uid") == "")
			{	
				$pps["join_butt"] = array(
					"name" => "join_butt",
					"type" => "submit",
					"caption" => t("Liitu!")
				);
			}
			else
			{
				$pps["upd_butt"] = array(
					"name" => "upd_butt",
					"type" => "submit",
					"caption" => t("Uuenda andmed!")
				);
			}
			return $pps;
		}
		return array();
	}

	function callback_get_join_offerer($arr)
	{
		$j_oid = $arr["obj_inst"]->prop("join_obj_offerer");
		if ($j_oid)
		{
			$join = obj($j_oid);
	
			$ji = get_instance(CL_JOIN_SITE);
			$pps = $ji->get_elements_from_obj($join,aw_global_get("REQUEST_URI"));
			if (aw_global_get("uid") == "")
			{	
				$pps["join_butt"] = array(
					"name" => "join_butt",
					"type" => "submit",
					"caption" => t("Liitu!")
				);
			}
			else
			{
				$pps["upd_butt"] = array(
					"name" => "upd_butt",
					"type" => "submit",
					"caption" => t("Uuenda andmed!")
				);
			}
			return $pps;
		}
		return array();
	}

	function callback_get_locations($arr)
	{
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_CONTENT",
		));

		$old = $arr["obj_inst"]->meta("locations");

		$rv = array();
		foreach($conns as $conn)
		{
			$target = $conn->to();
			$name = $target->name();
			$id = $target->id();
			$rv["title_" . $id] = array(
				"caption" => t("Objekt"),
				"type" => "text",
				"name" => "title_" . $id,
				"value" => $name,
			);

			$rv["location_" . $id] = array(
				"caption" => t("Asukoht"),
				"type" => "chooser",
				"name" => "locations[" . $id . "]",
				"options" => array("top" => "üleval","left" => "vasakul","right" => "paremal","bottom" => "all"),
				"value" => $old[$id],
			);
		};

		return $rv;
	}

	function get_content_elements($arr)
	{
		if($arr["new"])
		{
			return array();
		}
		$obj_inst = $arr["obj_inst"];
		$els = $obj_inst->connections_from(array(
			"type" => "RELTYPE_CONTENT",
		));
		$locations = $obj_inst->meta("locations");
		$rv = array();

		foreach($els as $el)
		{
			$to = $el->prop("to");
			if ($locations[$to])
			{
				//$rv[$to] = $locations[$to];
				$to_obj = $el->to();
				$ct = "";
				if (CL_PROMO == $to_obj->class_id())
				{
					// just shoot me
					$gidlist = aw_global_get("gidlist");
					$found = false;

					if (!is_array($to_obj->meta("groups")) || count($to_obj->meta("groups")) < 1)
					{
						$found = true;
					}
					else
					{
						foreach($to_obj->meta("groups") as $gid)
						{
							if (isset($gidlist[$gid]) && $gidlist[$gid] == $gid)
							{
								$found = true;
							}
						}
					}

					if ($found == true)
					{
						$clinst = get_instance(CL_PROMO);
						$ct = $clinst->parse_alias(array(
							"alias" => array(
								"target" => $to,
							),
						));
					};
                                };
				if (CL_MENU_AREA == $to_obj->class_id())
				{
					$ss = get_instance("contentmgmt/site_show");
					$rf = $to_obj->prop("root_folder");
					$ct = $ss->do_show_menu_template(array(
						"template" => "templates/menus.tpl",
						"mdefs" => array(
							$rf => "YLEMINE"
						)
                               		 ));
				};

				if (CL_POLL == $to_obj->class_id())
				{
					$clinst = get_instance(CL_POLL);
					$ct = $clinst->gen_user_html($to);
				};
                                $rv[$locations[$to]] .=  $ct;
                        };

                        // now, how do I get that thing?
                };
		return $rv;
	}

	/** provide public access to submit

		@attrib name=submit nologin=1


	**/
	function submit($arr)
	{
		return parent::submit($arr);
	}

	// this makes it possible to access that object directly with http://site/id
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
		$rv = $this->change($args);
		return $rv;
	}

	function callback_mod_retval($arr)
	{
		$args =& $arr["args"];
		if (is_array($this->url_data))
		{
			$args += $this->url_data;
		}
		if (is_array($arr["request"]["search_cv"]))
                {
                        $args["search_cv"] = $arr["request"]["search_cv"];
                };

	}
}
?>
