<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management.aw,v 1.12 2006/03/17 15:06:30 ahti Exp $
// personnel_management.aw - Personalikeskkond 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT relationmgr=yes r2=yes no_status=1 no_comment=1

@default group=general
@default table=objects
@default field=meta
@default method=serialize

@property orgs type=relpicker reltype=RELTYPE_MENU
@caption Organisatsioonide kaust

@property persons type=relpicker reltype=RELTYPE_MENU
@caption Isikute kaust

@property offers type=relpicker reltype=RELTYPE_MENU
@caption Tööpakkumiste kaust

@property tegevusvaldkonnad type=relpicker reltype=RELTYPE_SECTORS
@caption Tegevusvaldkonnad

@property person_ot type=relpicker reltype=RELTYPE_PERSON_OT
@caption Isikute objektitüüp

@property crmdb type=relpicker reltype=RELTYPE_CRM_DB
@caption Kliendibaas

@property owner_org type=relpicker reltype=RELTYPE_OWNER_ORG
@caption Omanikorganisatsioon

-------------------TÖÖOTSIJAD-----------------------
@groupinfo employee caption="Tööotsijad" submit=no

@groupinfo employee_search caption="Otsing" parent=employee
@default group=employee_search

@property search_save type=relpicker reltype=RELTYPE_SEARCH_SAVE
@caption Varasem otsing

@property search_cv type=form sclass=applications/personnel_management/personnel_management_cv_search sform=cv_search store=no
@caption Otsi CV-sid

----------------------------------------

@groupinfo employee_list caption="Nimekiri" parent=employee submit=no
@default group=employee_list

@property employee_list_toolbar type=toolbar no_caption=1

@layout employee_list type=hbox width=15%:85%

@property employee_list_tree type=treeview no_caption=1 parent=employee_list

@property employee_list_table type=table no_caption=1 parent=employee_list

----------------------------------------

@groupinfo candidate caption="Kandideerijad" submit=no
@default group=candidate

@property candidate_toolbar type=toolbar no_caption=1

@layout candidate type=hbox width=15%:85%

@property candidate_tree type=treeview no_caption=1 parent=candidate

@property candidate_table type=table no_caption=1 parent=candidate

----------------------------------------
@groupinfo offers caption="Tööpakkumised" submit=no
@default group=offers

@property offers_toolbar type=toolbar no_caption=1

@layout offers type=hbox width=15%:85%

@property offers_tree type=treeview no_caption=1 parent=offers

@property joblist type=table no_caption=1 parent=offers

----------------------------------------

@groupinfo actions caption="Tegevused" submit=no
@default group=actions

@property treeview3 type=text no_caption=1 default=asd

----------------------------------------

@groupinfo clients caption="Kliendid" submit=no
@default group=clients

@property treeview4 type=text no_caption=1 default=asd

---------------RELATION DEFINTIONS-----------------
@reltype MENU value=1 clid=CL_MENU
@caption Kaust

@reltype CRM_DB value=2 clid=CL_CRM_DB
@caption Kliendibaas

@reltype SECTORS value=3 clid=CL_METAMGR
@caption Tegevusvaldkonnad

@reltype PERSON_OT value=4 clid=CL_OBJECT_TYPE
@caption Objektitüüp

@reltype OWNER_ORG value=5 clid=CL_CRM_COMPANY
@caption Omanikorganisatsioon

@reltype SEARCH_SAVE value=6 clid=CL_BLAH
@caption Otsingu salvestus

*/

class personnel_management extends class_base
{
	function personnel_management()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_PERSONNEL_MANAGEMENT
		));
	}


	function callback_on_load($arr)
	{
		if(!$arr["new"])
		{
			if($this->can("view", $arr["request"]["id"]))
			{
				$obj = obj($arr["request"]["id"]);
				if($this->can("view", $obj->prop("owner_org")))
				{
					$this->owner_org = $obj->prop("owner_org");
				}
			}
		}
		$this->cfgmanager = $this->cfg["configform_manager"];
	}

	function callback_mod_tab($arr)
	{
		if(!$arr["new"] && $this->owner_org)
		{
			if($arr["id"] == "actions")
			{
				$arr["link"] = $this->mk_my_orb("change", array("id" => $this->owner_org, "group" => "overview"), CL_CRM_COMPANY);
			}
			elseif($arr["id"] == "clients")
			{
				$arr["link"] = $this->mk_my_orb("change", array("id" => $this->owner_org, "group" => "relorg"), CL_CRM_COMPANY);
			}
		}
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "candidate_toolbar":
			case "employee_list_toolbar":
			case "offers_toolbar":
				$prop["vcl_inst"]->add_button(array(
					"name" => "add",
					"caption" => t("Lisa"),
					"img" => "new.gif",
				));
				break;

			case "employee_list_table":
			case "candidate_table":
				$prop["vcl_inst"]->define_field(array(
					"name" => "name",
					"caption" => t("Nimi"),
				));
				$prop["vcl_inst"]->define_data(array(
					"name" => "test",
				));
				break;

			case "employee_list_tree":
			case "candidate_tree":
			case "offers_tree":
				$prop["vcl_inst"]->add_item(0, array(
					"id" => 3,
					"name" => t("Element"),
				));
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
							"url" => $this->mk_my_orb("change", array("id" => $data["cv_obj"]->id()), CL_PERSONNEL_MANAGEMENT_CV, false, true),
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
        	//"deadline" => new obj_predicate_compare(OBJ_COMP_GREATER, time()),
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
					"url" => $this->mk_my_orb("change", array("id" => $job->id()) , CL_PERSONNEL_MANAGEMENT_JOB_OFFER, false, true),
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
				"name" => $tegevusala->name() ."($count)",
    		    "id" =>	$tegevusala->id(),
    	        "url" => $this->mk_my_orb("change", array( 
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
			"url" => $this->mk_my_orb("new", array(), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true),
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
					"url" => $this->mk_my_orb("change", array("id" => $mycv->id()), "personnel_management_cv", true, true),
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
						"url" => $this->mk_my_orb("view_letter", array("id" => $rel_obj->id()), CL_PERSONNEL_MANAGEMENT, false, true),
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
							"url" => $this->mk_my_orb("change", array("id" => $cv->id()) , CL_PERSONNEL_MANAGEMENT_CV, true, true),
						)),
						"job" 	=> html::href(array(
							"caption" => $job->name(),
							"url" => $this->mk_my_orb("change", array("id" => $job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true),
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
							"url" => $this->mk_my_orb("change", array("id" => $job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true),
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
    		"url" => $this->mk_my_orb("new", array(), "personnel_management_cv", true, true),
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
					"url" => $this->mk_my_orb("change", array("id" => $job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true),
				)),
				"deadline" => get_lc_date($job->prop("deadline")),
				"kandidaate" => html::href(array(
					"caption" => $candits_count,
					"url" => $this->mk_my_orb("change", array("id" => $job->id(), "group" => "kandideerinud"), CL_PERSONNEL_MANAGEMENT_JOB_OFFER, true, true),
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
		@attrib name=view_letter all_args="1"
	**/
	
	function view_letter($arr)
	{
		$rel_obj = &obj($arr["id"]);
		echo $rel_obj->meta("kaaskiri");	
	}
}
?>
