<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management.aw,v 1.1 2004/04/22 12:41:53 sven Exp $
// personnel_management.aw - Personalikeskkond 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT relationmgr=yes no_status=1 layout=boxed

@default table=objects
@default group=general

-------------------MY PROFILE PROPERTIES-----------------
@property my_profile_mycvs_tb type=toolbar no_caption=1 group=my_profile_mycvs
@property my_profile_candits_tb type=toolbar no_caption=1 group=my_profile_candits

@property my_profile_cvtable type=table no_caption=1 group=my_profile_mycvs
@property my_profile_candits_table type=table no_caption=1 group=my_profile_candits group=my_profile_candits


@property my_personal_info_firstname type=textbox group=my_profile_personal store=no
@caption Eesnimi

@property my_personal_info_lastname type=textbox group=my_profile_personal store=no
@caption Perekonnanimi

@property my_personal_info_username type=text group=my_profile_personal store=no
@caption Kasutajanimi

@property gender type=chooser group=my_profile_personal store=no
@caption Sugu

@property my_personal_info_id type=textbox group=my_profile_personal store=no
@caption Isikukood

@property my_personal_info_password type=textbox group=my_profile_personal store=no
@caption Parool

@property my_personal_info_password_again type=textbox group=my_profile_personal store=no
@caption Parool uuesti

-----------------MY ORG PROFILE--------------------
@property org_profile_jobs_tb type=toolbar group=org_profile_jobs store=no no_caption=1
@property org_profile_jobs_candits_tb type=toolbar group=org_profile_candits store=no no_caption=1

@property org_profile_jobtable type=table group=org_profile_jobs no_caption=1
@property org_profile_candits type=table no_caption=1 group=org_profile_candits

@property org_username type=text group=org_profile_info
@caption Kasutajanimi

@property orgname type=textbox group=org_profile_info
@caption Organisatsiooni nimi

@property org_password type=textbox group=org_profile_info
@caption Parool

@property org_password_rep type=textbox group=org_profile_info
@caption Parooli kordus

@property my_join_worker type=callback group=my_join_worker callback=callback_get_join_worker store=no
@property my_join_offerer type=callback group=my_join_offerer callback=callback_get_join_offerer store=no

-------------------TÖÖOTSIJAD-----------------------
@property manager_person type=text no_caption=1 store=no wrapchildren=1 group=employee
@property treeview_person type=text parent=manager_person store=no group=employee
@property personlist type=table parent=manager_person store=no group=employee


-------------------TÖÖPAKKUMISED---------------------
@property manager type=text no_caption=1 store=no wrapchildren=1 group=employers
@property treeview type=text parent=manager store=no group=employers
@property joblist type=table parent=manager store=no group=employers



-----------------TAB DEFINTIONS--------------------
@groupinfo my_profile caption="Minu profiil"
@groupinfo org_profile caption="Tööpakkuja profiil"
@groupinfo employee caption="Tööotsijad" submit=no
@groupinfo employers caption="Tööpakkumised" submit=no
@groupinfo managers caption="Halduri profiil"

@groupinfo my_profile_mycvs caption="Minu CV-d" parent=my_profile submit=no
@groupinfo my_profile_candits caption="Kandideerin" parent=my_profile submit=no
@groupinfo my_profile_personal caption="Minu andmed" parent=my_profile

@groupinfo org_profile caption="Tööpakkuja profiil" submit=no
@groupinfo org_profile_jobs caption="Tööpakkumised" parent=org_profile submit=no
@groupinfo org_profile_candits caption="Kandideerijad" parent=org_profile
@groupinfo org_profile_info caption="Tööpakkuja andmed" parent=org_profile

@groupinfo all_setings caption="Seadistused"
@groupinfo dir_setings caption="Kaustade seaded" parent=all_setings
@groupinfo layout_setings caption="Seaded" parent=all_setings

@groupinfo my_join caption="Liitu kasutajaks" 
@groupinfo my_join_worker caption="T&ouml;&ouml;otsija" parent=my_join
@groupinfo my_join_offerer caption="T&ouml;&ouml;pakkuja" parent=my_join

--------------------PROPERTIES----------------------
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

---------------RELATION DEFINTIONS-----------------
@reltype MENU value=1 clid=CL_MENU
@caption Kaust

@reltype SECTORS value=20 clid=CL_META
@caption Tegevusvaldkonnad

@reltype JOIN_OBJ value=21 clid=CL_JOIN_SITE
@caption liitumisvorm

@reltype CONTENT value=22 clid=CL_MENU_AREA,CL_POLL,CL_PROMO
@caption Sisuelement
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
		
		$this->my_profile = $this->get_my_profile();
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
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "my_profile_cvtable":
				$this->do_my_profile_cvtable($arr);
			break;
			
			case "org_username":
				$prop["value"] = aw_global_get("uid");
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
			
			case "my_personal_info_username":
				$prop["value"] = aw_global_get("uid");
			break;
			
			case "my_personal_info_firstname":
				$prop["value"] = $this->my_profile["person_obj"]->prop("firstname");
			break;
			
			case "my_personal_info_lastname":
				$prop["value"] = $this->my_profile["person_obj"]->prop("lastname");
			break;
			
			case "my_personal_info_id":
				$prop["value"] = $this->my_profile["person_obj"]->prop("personal_id");
			break;
			
			case "my_personal_info_gender":
				$prop["value"] = $this->my_profile["person_obj"]->prop("gender");
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
			
			case "gender":
				$prop["options"] = array(
					"M" => "Mees",
					"N" => "Naine"
					);
            break;
            
            case "personlist":
            	$this->do_personlist_table($arr);
            break;
		};
		return $retval;
	}
	
	function do_personlist_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
				"name" => "nimi",
				"caption" => "Nimi",
				"sortable" => 1,
		));
				
		$table->define_field(array(
				"name" => "modified",
				"caption" => "Muudetud",
				"sortable" => 1,
		));	
		
		$table->define_field(array(
				"name" => "synd",
				"caption" => "Sündinud",
				"sortable" => 1,
		));
		
		$table->define_field(array(
				"name" => "sectors",
				"caption" => "Valdkonnad",
				"sortable" => 1,
		));
		
		$manager = current($this->my_profile["manager_list"]);
		
		foreach ($manager->connections_from(array("type" => RELTYPE_TOOTSIJA)) as $tootu)
		{
			$tootu = $tootu->to();
			$default_cv = $tootu->prop("default_cv");
			
			if(!$default_cv)
			{
				$default_cv = current($tootu->connections_from(array("type" => RELTYPE_CV)));
				if(is_object($default_cv))
				{
					$default_cv = $default_cv->prop("to");
				}
			}
			
			$default_cv_obj = &obj($default_cv);
			if($this->my_profile["person_obj"]->prop("birthday"))
			{
				$date = get_lc_date($this->my_profile["person_obj"]->prop("birthday"));
			}				
				
			if($default_cv_obj->status() == STAT_ACTIVE)
			{
				$table->define_data(array(
					"nimi" => html::href(array(
						"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $tootu->id()), CL_PERSONNEL_MANAGEMENT_CV). "\",\"cv\",800,600)",
						"caption" => $tootu->name(),
					)),
					"modified" => get_lc_date($this->my_profile["person_obj"]->modified()),
					"synd" => $date,
				));		
			}	
		}
	}
	
	function do_joblist($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "amet",
			"caption" => "Ametinimetus",
			"sortable" => 1,
		));
			
		$table->define_field(array(
			"name" => "pakkuja",
			"caption" => "Tööpakkuja",
			"sortable" => 1,
		));
				
		$table->define_field(array(
			"name" => "asukoht",
			"caption" => "Asukoht",
			"sortable" => 1,
			
		));

		$table->define_field(array(
			"name" => "sectors",
			"caption" => "Valdkond",
			"sortable" => 1,
			
		));	

		$table->define_field(array(
			"name" => "deadline",
			"caption" => "Tähtaeg",
			"sortable" => 1,
				
		));
				
		$manager = current($this->my_profile["manager_list"]);
		
		foreach ($manager->connections_from(array("type" => RELTYPE_TOOPAKKUJA)) as $toopakkuja)
		{
			$toopakkuja = $toopakkuja->to();
			
			foreach ($toopakkuja->connections_from(array("type" => RELTYPE_JOBS)) as $job)
			{
								
				$job_connection_id = $job->id();
				$job = $job->to();
				
				foreach ($job->connections_from(array("type" => RELTYPE_TEGEVUSVALDKOND)) as $sector)
				{
					$sector_list[] = $sector->prop("to.name");
				}				
				$sector_list_str = join(",", $sector_list);
				$sector_list = array();
				
				if($job->prop("status") == STAT_ACTIVE)
				{
					$job_cat_conns = new connection();
						
					$job_cat_conns = $job_cat_conns->find(array(
						"from" => $job->id(),
						"to" => $arr["request"]["sector_id"],
					));
					
					if($job_cat_conns)
					{		
						if($job->prop("asukoht"))
						{
							$city = &obj($job->prop("asukoht"));
							$city = $city->name();
						}
								
						$table->define_data(array(
							"amet" => html::href(array(
								"caption" => $job->prop("name"),
								"url" => "javascript:aw_popup_scroll(\"".$this->mk_my_orb("change", array("id" => $job->id()) , CL_PERSONNEL_MANAGEMENT_JOB_OFFER). "\",\"cv\",800,600)",
								)),

								"pakkuja" => $toopakkuja->name(),
							"asukoht" => $city,
							"deadline" => get_lc_date($job->prop("deadline")),
							"sectors" =>  $sector_list_str,
							
						));
					}
				}
				
			}
		}			
	}
	
	
	
	function count_sector_jobs(&$arr, $sector_id)
	{
		$manager = current($this->my_profile["manager_list"]);		
		foreach ($manager->connections_from(array("type" => RELTYPE_TOOPAKKUJA)) as $toopakkuja)
		{
			$toopakkuja = $toopakkuja->to();
			
			foreach ($toopakkuja->connections_from(array("type" => RELTYPE_JOBS)) as $job)
			{		
				$job = $job->to();
				if($job->prop("status") == STAT_ACTIVE)
				{
					$job_cat_conns = new connection();
						
					$job_cat_conns = $job_cat_conns->find(array(
						"from" => $job->id(),
						"to" => $sector_id,
					));
					if($job_cat_conns)
					{
						$counter++;
					}
				}
			}
		}
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
    		"root_name" => "AutomatWeb",
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
				$count = 0;
			}
			
			$tree->add_item($parent, array(
				"name" =>	$tegevusala->name() ."($count)",
    		    "id" =>		$tegevusala->id(),
    	        "url" =>	$this->mk_my_orb("change", array( 
    	                  				"sector_id" => $tegevusala->id(), 
	                      				"group" => $arr["request"]["group"], 
	                      				"id"=> $arr["obj_inst"]->id(),
								))
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
			"tooltip" => "Kustuta kandidaadid",
			"action" => "delete_rels",
		));
	}
	
	function do_org_profile_jobs_tb(&$arr)
	{

		$tb = &$arr["prop"]["toolbar"];
			
		$tb->add_menu_button(array(
   			"name" => "new",
   			"img" => "new.gif",
   			"tooltip" => "Uus",
		));
			
		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => "Tööpakkumine",
			"title" => "Tööpakkumine",
			"url" => $this->mk_my_orb("new", array(
						"parent" => $this->my_profile["org_obj"]->parent(),
						"return_url" => urlencode(aw_global_get('REQUEST_URI')),
						), CL_PERSONNEL_MANAGEMENT_JOB_OFFER)
		));
			
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta tööpakkumised",
			"action" => "delete_rels",
		));	
	}
	
	
	function do_my_profile_cvtable(&$arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "default",
			"caption" => "Default cv",
		));
		
		$table->define_field(array(
			"name" => "nimi",
			"caption" => "Nimi",
			"sortable" => 1,
		));
				
		$table->define_field(array(
			"name" => "active",
			"caption" => "Aktiivne kuni",
			"sortable" => 1,
			"align" => "center",
		));
			
		$table->define_field(array(
			"name" => "muudetud",
			"caption" => "Muudetud",
			"sortable" => 1,
			"align" => "center"
		));	

			
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
			"align" => "center",
		));
		
		foreach ($this->my_profile["person_obj"]->connections_from(array("type" => RELTYPE_CV)) as $mycv)
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
					"url" => "javascript:aw_popup_scroll(\"". $this->mk_my_orb("change", array("id" => $mycv->id()), "personnel_management_cv"). "\",\"cv\",800,600)",
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
			"caption" => "Nimi",
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "job",
			"caption" => "Tööpakkumine",
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "deadline",
			"caption" => "Kuupäev",
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
			"align" => "center",
		));
		
		foreach ($this->my_profile["org_obj"]->connections_from(array("type" => RELTYPE_JOBS, "to" => $arr["request"]["jobid"])) as $job)
		{
			$job = $job->to();
			foreach ($job->connections_from(array("type" => 5)) as $cv)
			{
				$cv_rel_created = $cv->prop("created");
				
				$cv_connid = $cv->id();
							
				$cvcreated = $cv->prop("created");
				
				$rel_obj = obj($cv->prop("relobj_id"));
					
				$cv = $cv->to();

				foreach ($cv->connections_from(array("type" => RELTYPE_CV_OWNER)) as $person)
				{
					$table->define_data(array(
						"name"  => html::href(array(
							"caption" => $person->prop("to.name"),
							"url" => $this->mk_my_orb("change", array("id" => $cv->id()) , CL_PERSONNEL_MANAGEMENT_CV),
						)),
						"job" 	=> html::href(array(
								"caption" => $job->name(),
								"url" => $this->mk_my_orb("change", array("id" => $job->id()) ,CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
						)),
						"deadline" => get_lc_date($cv_rel_created),
						"from" => $cv_connid,
						"to" => $cv_connid,
						"hinne" => $rel_obj->meta("hinne"),
					));
				}
			}
		}
		
		$table->set_default_sortby("hinne");
		$table->sort_by(); 
	}

	function my_profile_candits_table($arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "organisatsioon",
			"caption" => "Organisatsioon",
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => "Ametikoht",
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "deadline",
			"caption" => "Kandideerimise lõpptähtaeg",
			"sortable" => 1,
		));
		
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
			"align" => "center",
		));

		foreach ($this->my_profile["person_obj"]->connections_from(array("type" => RELTYPE_CV)) as $cv)
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
								"url" => $this->mk_my_orb("change", array("id" => $job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
								"caption" => $job->name(),
								)),
						"deadline" => get_lc_date($job->prop("deadline")),
						"organisatsioon" => html::href(array(
							"caption" => $company->prop("from.name"),
							"url" => $this->mk_my_orb("change", array("id" => $company->prop("from")), CL_CRM_COMPANY),
						)),
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
    		"tooltip" => "Uus CV",
		));
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta CV-d",
			"action" => "delete_rels",
			"confirm" => "Kas soovid valitud objektid kustutada?",
		));
		
		$tb->add_button(array(
			"name" => "Save",
			"img" => "save.gif",
			"tooltip" => "Salvesta",
			"action" => "set_default_cv",
		));
		
		$tb->add_menu_item(array(
    		"parent" => "new",
    		"text" => "CV",
    		"title" => "CV",
    		"url" => $this->mk_my_orb("new", array(
    							"parent" => $this->my_profile["person_obj"]->prop("parent"),
    							"return_url" => urlencode(aw_global_get('REQUEST_URI')),
    							), "personnel_management_cv"),
    		"disabled" => false,
		));
	}
	
	function my_profile_candits_tb($arr)
	{
		$tb = &$arr["prop"]["toolbar"];
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta CV-d",
			"action" => "delete_rels",
			"confirm" => "Kas soovid valitud objektid kustutada?",
		));
	}
	
	function do_org_profile_jobtable($arr)
	{
		$table =& $arr["prop"]["vcl_inst"];
			
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => "Ametikoht",
			"sortable" => 1,
			"width" => "70%"
		));
			
		$table->define_field(array(
			"name" => "deadline",
			"caption" => "Tähtaeg",
			"sortable" => 1,
			"width" => "10%",
			"align" => "center"
		));
		
		$table->define_field(array(
			"name" => "kandidaate",
			"caption" => "Kandidaate",
			"sortable" => 1,
			"width" => "10%",
			"align" => "center"
		));
		
		$table->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
			"sortable" => 1,
			"with" => "10%",
		));
			
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
			"align" => "center",
		));
		
		foreach ($this->my_profile["org_obj"]->connections_from(array("type" => RELTYPE_JOBS)) as $job)
		{
			$job = $job->to();
			$candits_count = count(($job->connections_from(array("type" => 1))));
			
			$table->define_data(array(
					"ametikoht" =>	html::href(array(
										"caption" => $job->name(),
										"url" => $this->mk_my_orb("change", array("id" => $job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
									)),
					"deadline" => get_lc_date($job->prop("deadline")),
					"kandidaate" => html::href(array(
									"caption" => $candits_count,
									"url" => $this->mk_my_orb("change", array("id" => $job->id(), "group" => "kandideerinud"), CL_PERSONNEL_MANAGEMENT_CV),
								)),
					"status" => ($job->status() == STAT_ACTIVE)?"Aktiivne":"Mitteaktiivne",
			));
		}
		
	}
	
	
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "my_personal_info_firstname":
				$this->my_profile["person_obj"]->set_prop("firstname", $prop["value"]);
			break;
			
			case "my_personal_info_lastname":
				$this->my_profile["person_obj"]->set_prop("lastname", $prop["value"]);
			break;
			
			case "my_personal_info_id":
				$this->my_profile["person_obj"]->set_prop("personal_id", $prop["value"]);
			break;
			
			case "gender":
				$this->my_profile["person_obj"]->set_prop("gender", $prop["value"]);
			break;
			
			case "org_profile_candits":
				foreach ($arr["request"]["hinne"] as $key => $value)
				{
					$conn = new connection($key);
					$relobject = &obj($conn->prop("relobj_id"));
					$relobject->set_meta("hinne", $value);
					$relobject->save();
				}
			break;
			
			case "my_join_offerer":
				$j_oid = $arr["obj_inst"]->prop("join_obj_offerer");
				if ($j_oid)
				{
					$tmp = $arr["request"];
					$tmp["id"] = $j_oid;
					if (!empty($arr["request"]["join_butt"]))
					{
						$ji = get_instance("contentmgmt/join/join_site");
						$url = $ji->submit_join_form($tmp);
						if ($url != "")
						{
							header("Location: $url");
							die();
						}
					}
					else
					if (!empty($arr["request"]["upd_butt"]))
					{
						$ji = get_instance("contentmgmt/join/join_site");
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
						$ji = get_instance("contentmgmt/join/join_site");
						$url = $ji->submit_join_form($tmp);
						if ($url != "")
						{
							header("Location: $url");
							die();
						}
					}
					else
					{
						$ji = get_instance("contentmgmt/join/join_site");
						$ji->submit_update_form($tmp);
					}
				}
			break;
		}
		
		if($this->my_profile["person_obj"])
		{
			$this->my_profile["person_obj"]->save();
		}
		
		return $retval;
	}	
	

	function get_employee_profile()
	{
		$employee_profile["group"] = "employee";
		$user_id = users::get_oid_for_uid(aw_global_get("uid"));
		
		$employee_profile["user_obj"] = & obj($user_id);
		$employee_profile["person_obj"] = array_pop($employee_profile["user_obj"]->connections_from(array("type" => RELTYPE_PERSON)));
		$employee_profile["person_obj"] = $employee_profile["person_obj"]->to();
		
		$manager_conns = new connection();
		
		$manager_conns_list = &$manager_conns->find(array(
    	    "to" => $employee_profile["person_obj"]->id(),
    	    "type" => 21,
		)); 
		
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
		
		$employer_profile["person_obj"] = array_pop($employer_profile["user_obj"]->connections_from(array("type" => RELTYPE_PERSON)));
		$employer_profile["person_obj"] = $employer_profile["person_obj"]->to(); 
		
		$employer_profile["org_obj"] = array_pop($employer_profile["person_obj"]->connections_from(array("type" => RELTYPE_WORK)));
		$employer_profile["org_obj"] = $employer_profile["org_obj"]->to();
		
		
		$manager_conns = new connection();
		$manager_conns_list = &$manager_conns->find(array(
    	    "to" => $employer_profile["org_obj"]->id(),
    	    "type" => 20,
		));
		
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
		
		$manager_profile["person_obj"] = array_pop($manager_profile["user_obj"]->connections_from(array("type" => RELTYPE_PERSON)));
		$manager_profile["person_obj"] = $manager_profile["person_obj"]->to(); 
		
		$manager_profile["org_obj"] = array_pop($manager_profile["person_obj"]->connections_from(array("type" => RELTYPE_WORK)));
		$manager_profile["org_obj"] = $manager_profile["org_obj"]->to();
		
		return $manager_profile;
	}
	
	function get_my_profile()
	{
		$gidlist = users::getgroupsforuser(aw_global_get("uid"));
		
		//Konverdime grupi_id-d objekti id-deks.
		foreach ($gidlist as $key=>$value)
		{
			$gidlist[$key] = users::get_oid_for_gid($key); 
		}

		if(array_search($this->cfg["employee_group"] , $gidlist))
		{
			return $this->get_employee_profile();
		}
		elseif(array_search($this->cfg["employer_group"] , $gidlist))
		{
			return $this->get_employer_profile();
		}
		elseif (array_search($this->cfg["manager_group"] , $gidlist))
		{
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
	
			$ji = get_instance("contentmgmt/join/join_site");
			$pps = $ji->get_elements_from_obj($join, array(
				"err_return_url" => aw_ini_get("baseurl").aw_global_get("REQUEST_URI")
			));
			if (aw_global_get("uid") == "")
			{	
				$pps["join_butt"] = array(
					"name" => "join_butt",
					"type" => "submit",
					"caption" => "Liitu!"
				);
			}
			else
			{
				$pps["upd_butt"] = array(
					"name" => "upd_butt",
					"type" => "submit",
					"caption" => "Uuenda andmed!"
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
	
			$ji = get_instance("contentmgmt/join/join_site");
			$pps = $ji->get_elements_from_obj($join,aw_global_get("REQUEST_URI"));
			if (aw_global_get("uid") == "")
			{	
				$pps["join_butt"] = array(
					"name" => "join_butt",
					"type" => "submit",
					"caption" => "Liitu!"
				);
			}
			else
			{
				$pps["upd_butt"] = array(
					"name" => "upd_butt",
					"type" => "submit",
					"caption" => "Uuenda andmed!"
				);
			}
			return $pps;
		}
		return array();
	}

	function callback_get_locations($arr)
	{
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_CONTENT,
		));

		$old = $arr["obj_inst"]->meta("locations");

		$rv = array();
		foreach($conns as $conn)
		{
			$target = $conn->to();
			$name = $target->name();
			$id = $target->id();
			$rv["title_" . $id] = array(
				"caption" => "Objekt",
				"type" => "text",
				"name" => "title_" . $id,
				"value" => $name,
			);

			$rv["location_" . $id] = array(
				"caption" => "Asukoht",
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
		$obj_inst = $arr["obj_inst"];
		$els = $obj_inst->connections_from(array(
			"type" => RELTYPE_CONTENT,
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
                                        $clinst = get_instance(CL_PROMO);
                                        $ct = $clinst->parse_alias(array(
                                                "alias" => array(
                                                        "target" => $to,
                                                ),
                                        ));
                                };
				if (CL_MENU_AREA == $to_obj->class_id())
				{
					$ss = get_instance("contentmgmt/site_show");
					$rf = $to_obj->prop("root_folder");
					$ct = $ss->do_show_menu_template(array(
						"template" => "menus.tpl",
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
}
?>
