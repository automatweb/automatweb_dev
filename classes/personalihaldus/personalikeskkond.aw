<?
// $Header: /home/cvs/automatweb_dev/classes/personalihaldus/Attic/personalikeskkond.aw,v 1.6 2004/04/07 10:18:24 sven Exp $
// personalikeskkond.aw - Personalikeskkond 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_PERSONALIKESKKOND, on_connect_manager_to_keskkond)

@classinfo syslog_type=ST_PERSONALIKESKKOND relationmgr=yes no_status=1
@default table=objects

//////////////////////////RELATIONS\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ 

@reltype KAUST value=2 clid=CL_MENU
@caption Kaust

@reltype HALDUR value=3 clid=CL_CRM_COMPANY
@caption Haldur

@reltype CONFIGFORM value=4 clid=CL_CFGMANAGER 
@caption Seadete vorm

@reltype VALDKONNAD value=20 clid=CL_META
@caption Tegevusvaldkonnad
//////////////////////////TOOLBARS\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ 

@property navtoolbar_job_seeker type=toolbar no_caption=1 store=no group=cv_nimekiri,tootsijad_nimekiri,tootsijad_valdkonnad
@property navtoolbar_job_offer type=toolbar no_caption=1 store=no group=toopakkujad_nimekiri,toopakkujad_tood,toopakkumised_cats
@property navtoolbar_manager type=toolbar no_caption=1 store=no group=haldurid

///////////////////////////SETTINGS_TAB\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ 

@property orgs type=relpicker group=setings table=objects method=serialize field=meta reltype=RELTYPE_KAUST
@caption Organisatsioonide kaust

@property persons type=relpicker group=setings table=objects method=serialize field=meta reltype=RELTYPE_KAUST
@caption Isikute kaust

@property cvparent type=relpicker group=setings table=objects method=serialize field=meta reltype=RELTYPE_KAUST
@caption CV-de kaust

@property offers type=relpicker group=setings table=objects method=serialize field=meta reltype=RELTYPE_KAUST
@caption Tööpakkumiste kaust

@property tegevusvaldkonnad type=relpicker reltype=RELTYPE_VALDKONNAD method=serialize field=meta group=setings
@caption Tegevusvaldkondade kaust

///////////////////////////TABLES\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ 

@property jobtable type=table group=toopakkujad_tood no_caption=1

@property users type=text group=tootsijad_nimekiri subtitle=1
@caption Tööotsijad:
@property persontable type=table group=tootsijad_nimekiri no_caption=1

@property companytable type=table group=toopakkujad_nimekiri no_caption=1
@property cvtable type=table group=cv_nimekiri no_caption=1


@property subtitle_tootsijad type=text group=haldurid subtitle=1
@caption Personalikeskkonna haldurid:
@property manager_table type=table group=haldurid no_caption=1

///////////////////////TÖÖOTSIJATE_CV_PUU\\\\\\\\\\\\\\\\\\\
@property manager_person type=text no_caption=1 store=no wrapchildren=1 group=tootsijad_valdkonnad
@property treeview_person type=text parent=manager_person store=no group=tootsijad_valdkonnad
@property catjoblist_person type=table parent=manager_person store=no group=tootsijad_valdkonnad


/////////////////////////TÖÖPAKKUMISTE PUU\\\\\\\\\\\\\\\\\\\\\

@property manager type=text no_caption=1 store=no wrapchildren=1 group=toopakkumised_cats
@property treeview type=text parent=manager store=no group=toopakkumised_cats
@property catjoblist type=table parent=manager store=no group=toopakkumised_cats


/////////////////////////MINU PROFIIL TÖÖOTSIJA\\\\\\\\\\\\\\\\\\\\\\\
@property my_personal_toolbar type=toolbar group=mycvs no_caption=1
@property my_personal_toolbar_mycandits type=toolbar group=my_candits no_caption=1

@property mycvs type=table group=mycvs no_caption=1
@property my_candits type=table group=my_candits no_caption=1
@property my_personal_info type=table group=mycvs no_caption=1
________________________________________________________________
@property my_personal_info_username type=text group=my_personal_info store=no
@caption Kasutajanimi

@property my_personal_info_firstname type=textbox group=my_personal_info store=no
@caption Eesnimi

@property my_personal_info_lastname type=textbox group=my_personal_info store=no
@caption Perekonnanimi

@property gender type=chooser group=my_personal_info store=no
@caption Sugu

@property my_personal_info_id type=textbox group=my_personal_info store=no
@caption Isikukood

@property my_default_cv type=select group=mycvs
@caption Minu cv vaikimisi

@property my_personal_info_password type=textbox group=my_personal_info store=no
@caption Parool

@property my_personal_info_password_again type=textbox group=my_personal_info store=no
@caption Parool uuesti

/////////////////////////MINU PROFIIL TÖÖPAKKUJA\\\\\\\\\\\\\\\\\\\\\\\
@property org_toolbar type=toolbar group=org_jobs,org_jobs_candits,org_info no_caption=1
@property org_jobs type=table group=org_jobs no_caption=1
@property org_candits type=table group=org_jobs_candits no_caption=1
@property org_info type=table group=org_info no_caption=1
@property org_bookmarks type=table group=org_bookmarks no_caption=1
________________________________________________________________________


///////////////////////////TAB PROPS\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ 

@groupinfo my_profile caption="Minu profiil"
@groupinfo org_profile caption="Organisatsiooni profiil"
@groupinfo tootsijad caption="T&ouml;&ouml;otsijad" 
@groupinfo toopakkujad caption="T&ouml;&ouml;pakkujad"
@groupinfo haldurid caption="Haldurid"


@groupinfo toopakkujad_nimekiri caption="Tööpakkujate nimekiri" submit=no parent=toopakkujad
@groupinfo toopakkujad_tood caption="Tööpakkumised" submit=no parent=toopakkujad
@groupinfo toopakkumised_cats parent=toopakkujad submit=no caption="Tööpakkumised - Valdkonnad"

@groupinfo tootsijad_nimekiri caption="T&ouml;&ouml;otsijate nimekiri" submit=no parent=tootsijad
@groupinfo tootsijad_valdkonnad caption="Tööotsijad - Valdkonnad" submit=no parent=tootsijad
@groupinfo cv_nimekiri caption="CV nimekiri" submit=no parent=tootsijad


@groupinfo mycvs caption="Minu CV-d" parent=my_profile submit=no
@groupinfo my_candits caption="Kandideerin" parent=my_profile submit=no
@groupinfo my_personal_info caption="Minu andmed" parent=my_profile


@groupinfo org_jobs caption="Tööpakkumised" parent=org_profile submit=no
@groupinfo org_jobs_candits caption="Kandideerijad" parent=org_profile submit=no
@groupinfo org_bookmarks caption="Huvitavad kandidaadid" parent=org_profile submit=no

@groupinfo org_jobs_candits caption="Kandideerijad" parent=org_profile submit=no
@groupinfo org_info caption="Organisatsiooni andmed" parent=org_profile


@group all_setings caption="Kaustade seaded"
@groupinfo setings caption="Seaded" parent=all_setings

*/
class personalikeskkond extends class_base
{
	var $my_profile;

	function personalikeskkond()
	{

		$this->init(array(
			'clid' => CL_PERSONALIKESKKOND,
		));		
		$this->my_profile = $this->get_my_profile();

	}
	
	function callback_on_load($arr)
	{
		$this->cfgmanager = aw_ini_get("emp_cfgmanager");
	}
	
	
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($data["name"])
		{
			
			case "my_personal_info_username":
				$data["value"] = aw_global_get("uid");
			break;
				
			case "my_personal_info_firstname":
				$data["value"] = $this->my_profile->prop("firstname");
			break;
			
			case "my_personal_info_lastname":
				$data["value"] = $this->my_profile->prop("lastname");
			break;
			
			case "my_personal_info_id":
				$data["value"] = $this->my_profile->prop("personal_id");
			break;
			
			case "my_personal_info_gender":
				$data["value"] = $this->my_profile->prop("gender");
			break;
			
			case "my_personal_toolbar":
				$this->do_my_personal_toolbar($arr);
			break;
			
			case "my_personal_toolbar_mycandits":
				$this->do_my_personal_toolbar_mycandits($arr);
			break;
			
			case "my_candits":
				$this->do_mycantis_table($arr);
			break;
			
			case "org_toolbar":
				$this->do_org_toolbar($arr);
			break;
			
			case "org_candits":
				$this->do_org_candits_table($arr);
			break;
			
			case "org_bookmarks":
				$this->do_org_bookmakrs_table($arr);
			break;
			
			case "manager_table":
				$this->do_manager_table($arr);
			break;
			
			case "catjoblist":
				$this->do_joblist_table($arr);
			break;
			
			case "treeview":
				$this->do_jobcats_tree($arr);	 
			break;
			
			case "treeview_person":
				$this->do_jobcats_tree($arr);
			break;
			
			case "navtoolbar_manager":	
				$this->do_manager_toolbar($arr);	
			break;

			case "navtoolbar_job_offer":
				$this->do_navtoolbar_joboffer($arr);	
			break;
			
			case "cvtable":
				$this->do_sector_table_cvs($arr);
			break;
			
			case "persontable":
				$this->do_persontable($arr);		
			break;
			
			case "navtoolbar_job_seeker":
				$this->navtoolbar_job_seeker($arr);	
			break;
			
			case "catjoblist_person":
				$this->do_sector_table_cvs($arr);
			break;
			
			case "mycvs":
				$this->do_mycvs_table($arr);
			break;
	
			case "org_jobs":
				$this->do_org_jobs_table($arr);
			break;
			
			case "gender":
				 $data["options"] = array(
					"M" => "Mees",
					"N" => "Naine"
					);
            break;
			
            case "my_default_cv":
            	$mycvs = $this->get_cvs_of_person($this->my_profile);
            	foreach ($mycvs as $mycv)
				{
					$data["options"][$mycv->id()] = $mycv->name();	
				}
            break;
			
            case "jobtable":

				$table=&$arr["prop"]["vcl_inst"];
				$this->do_joblist_table_cols($table);
				
				//See peaks ehk kuidagi optimaalsem olema
				foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_HALDUR)) as $haldur)
				{
					$haldur = obj($haldur->prop("to"));
						
					foreach ($haldur->connections_from(array("type" => RELTYPE_TOOPAKKUJA)) as $toopakkuja)
					{
						$toopakkuja = obj($toopakkuja->prop("to"));
						foreach ($toopakkuja->connections_from(array("type" => RELTYPE_JOBS)) as $toopakkumine)
						{
							$connection_id = $toopakkumine->id();
							$toopakkumine = obj($toopakkumine->prop("to"));
							
							if($toopakkumine->prop("status") == STAT_NOTACTIVE)
							{
								$linn = $toopakkumine->prop("asukoht");
								
								//Kas linn on määratud?
								if($linn)
								{
									$linn = &obj($linn);
									$linn = $linn->name();
								}
								else
								{
									unset($linn);
								}
							
								$table->define_data(array(
									"pakkuja" => html::href(array(
												"caption" => $toopakkuja->prop("name"),
												"url" => $this->mk_my_orb("change", array("id" => $toopakkuja->id()), "crm_company"),
											)),
									"amet" => html::href(array(
												"caption" => $toopakkumine->prop("name"),
												"url" => $this->mk_my_orb("change", array("id" => $toopakkumine->id()), "job_offer"),
											)),
									"asukoht" => $linn,
									"deadline" => get_lc_date($toopakkumine->prop("deadline")),
									"from" => $connection_id
								));
							}
						}
					}
				}
				
			break;
			
			case "companytable":
			
				$table=&$arr["prop"]["vcl_inst"];

				$table->define_field(array(
					"name" => "toopakkuja",
					"caption" => "T&ouml;&ouml;pakkuja",
					"sortable" => 1,
				));
				
				$table->define_field(array(
					"name" => "pakkumised",
					"caption" => "T&ouml;&ouml;pakkumised",
					"width" => "100"
				));
				
				$table->define_field(array(
					"name" =>"lisa",
					"caption" => "Lisa t&ouml;&ouml;pakkumine",
					"width" => "100",
				));

				foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_HALDUR)) as $haldur)
				{
					$haldur = obj($haldur->prop("to"));
					
					foreach ($haldur->connections_from(array("type" => RELTYPE_TOOPAKKUJA)) as $company)
					{
						$company = obj($company->prop("to"));

						$table->define_data(array(
							"toopakkuja" => html::href(array(
												"caption" => $company->prop("name"),
												"url" => $this->mk_my_orb("change", array("id" => $company->id()) ,"crm_company"), 
											)),
							"lisa" 		=> html::href(array(
												"caption" => "Lisa tööpakkumine",
												"url" =>$this->mk_my_orb("new", array(
																"parent" => $arr["obj_inst"]->prop("offers"),
    															"return_url" => urlencode(aw_global_get('REQUEST_URI')),
																"alias_to" => $company->id(),
																"reltype" => 19,
															), "job_offer"), 
											)),
											
							"pakkumised" => html::href(array(
												"caption" => "Vaata tööpakkumisi",
												"url" =>  $this->mk_my_orb("change", array("id" => $company->id(), "group" => "jobs") ,"crm_company"),
											)), 					
						));
					}
				}
				
			break;
		};
		return $retval;
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
		
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]), "personalikeskkond");
	}
	
	/**
		@attrib name=save_changes
	**/
	function save_changes($arr)
	{
		$myprofile = $this->get_my_profile();
		
		foreach ($myprofile->connections_from(array("type" => RELTYPE_CV)) as $cv)
		{
			$cv = $cv->to();
			$cv->set_status(STAT_NOTACTIVE);
			$cv->save();
		}
		
		foreach ($arr["act"] as $key => $value)
		{
			$cv = &obj($key);
			$cv->set_status(STAT_ACTIVE);
			$cv->save();
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]), "personalikeskkond");
	}
	
	/**
		@attrib name=refresh_jobs
		Show only candidates for selected job
	**/
	function refresh_jobs($arr)
	{
		return $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"group" => $arr["group"],
			"jobid" => $arr["org_jobs"], 
		), $arr["class"]);
	}
	
	function get_cvs_of_person(&$person)
	{
		$cv_conns = $person->connections_from(array("type" => 19)); 
		foreach ($cv_conns as $cv_conn)
		{
			$return_cvs[] = $cv_conn->to();
		}
		return $return_cvs;
	}
	
	function on_connect_manager_to_keskkond($arr)
	{
		$conn = &$arr["connection"];
		if($conn->prop("to.class_id") == CL_CRM_COMPANY && $conn->prop("reltype") == RELTYPE_HALDUR)
		{
			$manager = $conn->to();
			
			$manager->connect(array(
				"to" => $conn->prop("from"),
				"reltype" => RELTYPE_PERSONAL_MRG,
			));
		}
	}
	
	function do_org_bookmakrs_table(&$arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "synd",
			"caption" => "Sündinud",
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "jobs",
			"caption" => "Kandideerib järgmistele ametikohtadele: ",
			"sortable" => 1,
		));
		
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
			"align" => "center",
		));
		
		$my_org = $this->get_my_org_profile();
		
		foreach ($my_org->connections_from(array("type" => RELTYPE_KANDIDAAT)) as $bookmark)
		{
			$person_obj = $bookmark->to();
			
			$table->define_data(array(
				"name" => html::href(array(
					"caption" => $bookmark->prop("to.name"),
					"url" => $this->mk_my_orb("change", array("id" => $bookmark->prop("to")), "crm_person"),
				)),
				"synd" => $person_obj->prop("birthday"),
				//"jobs" => "",
				"form" => $bookmark->id(),
			));
		}
	}
	
	function do_org_candits_table(&$arr)
	{
		if($org_profile = $this->get_my_org_profile())
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
			
			$table->define_chooser(array(
				"name" => "sel",
				"field" => "from",
				"align" => "center",
			));
			foreach ($org_profile->connections_from(array("type" => RELTYPE_JOBS, "to" => $arr["request"]["jobid"])) as $job)
			{
				$job = $job->to();
				foreach ($job->connections_from(array("type" => 5)) as $cv)
				{
					$cv_rel_created = $cv->prop("created");
					$cv_connid = $cv->id();					
					$cvcreated = $cv->prop("created");
					$cv = $cv->to();
					
					foreach ($cv->connections_from(array("type" => RELTYPE_CV_OWNER)) as $person)
					{
						$table->define_data(array(
							"name"  => html::href(array(
									"caption" => $person->prop("to.name"),
									"url" => $this->mk_my_orb("change", array("id" => $cv->id()) ,"cv"),
							)),
							"job" 	=> html::href(array(
									"caption" => $job->name(),
									"url" => $this->mk_my_orb("change", array("id" => $job->id()) ,"job_offer"),
							)),
							"deadline" => get_lc_date($cv_rel_created),
							"from" => $cv_connid,
						));
					}
				}
			}
		}
	}
	
	function do_joblist_table($arr)
	{
		$table =& $arr["prop"]["vcl_inst"];
		$this->do_joblist_table_cols($table);	

		if($_GET["sector_id"])
		{	
			foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_HALDUR)) as $haldur)
			{
				$haldur = $haldur->to();
				foreach ($haldur->connections_from(array("type" => RELTYPE_TOOPAKKUJA)) as $toopakkuja)
				{
					$toopakkuja = $toopakkuja->to();
				
					foreach ($toopakkuja->connections_from(array("type" => RELTYPE_JOBS)) as $job)
					{
						
						$job_connection_id = $job->id();

						$job = $job->to();
						
						if($job->prop("status") == 2 && $job->prop("deadline") > time())
						{
							$job_cat_conns = new connection();
							
							$job_cat_conns = $job_cat_conns->find(array(
								"from" => $job->id(),
								"to" => $_GET["sector_id"],
							));
						
							if($job_cat_conns)
							{
								
								if($job->prop("asukoht"))
								{
									$city = &obj($job->prop("asukoht"));
								}
								
								$table->define_data(array(
									"amet" => html::href(array(
										"caption" => $job->prop("name"),
										"url" => $this->mk_my_orb("change", array("id" => $job->id()) ,"job_offer")
										)),
									"pakkuja" => html::href(array(
										"caption" => $toopakkuja->name(),
										"url" => $this->mk_my_orb("change", array("id" => $toopakkuja->id()) ,"crm_company")
										)),
									"asukoht" => @$city->name(),
									"deadline" => get_lc_date($job->prop("deadline")),
									"from" => $job_connection_id,
								));
							}
						}
					}
				}
			}		
		}
	}

	function do_joblist_table_cols(&$table)
	{		
		$table->define_field(array(
			"name" => "amet",
			"caption" => "Ametinimetus",
			"sortable" => 1,
		));
			
		$table->define_field(array(
			"name" => "pakkuja",
			"caption" => "T&ouml;&ouml;pakkuja",
			"sortable" => 1,
		));
				
				
		$table->define_field(array(
			"name" => "asukoht",
			"caption" => "Asukoht",
			"sortable" => 1,
			
		));
				
		$table->define_field(array(
			"name" => "deadline",
			"caption" => "Tähtaeg",
			"sortable" => 1,
				
		));
				
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
			"align" => "center",
		));
	}
	
	
	function do_personlist_table_cols(&$table)
	{
		$table->define_field(array(
			"name" => "nimi",
			"caption" => "Nimi",
			"sortable" => 1,
			"width" => "75%",
		));
				
		$table->define_field(array(
			"name" => "cv",
			"caption" => "CV",
			"sortable" => 1,
			"align" => "center",
			"width" => "10%",
		));
				
		$table->define_field(array(
			"name" => "cv_view",
			"caption" => "Vaata CV-sid",
			"sortable" => 1,
			"width" => "10%",
			"align" => "center",
		));
				
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
			"width" => "40",
			"align" => "center",
		));
	}
	
	function do_cvtable_cols(&$table)
	{
		$table->define_field(array(
				"name" => "nimi",
				"caption" => "Nimi",
				"sortable" => 1,
				"width" => "65%"
		));
				
		$table->define_field(array(
				"name" => "lisatud",
				"caption" => "Lisamise kuupäev",
				"sortable" => 1,
				"width" => "15%"
		));
				
		$table->define_field(array(
				"name" => "muudetud",
				"caption" => "Muutmise kuupäev",
				"sortable" => 1,
				"width" => "15%",
		));
				
		$table->define_chooser(array(
				"name" => "sel",
				"field" => "from",
		));
	}
	
	
	function do_manager_table(&$arr)
	{
		$table=&$arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "organisatsioon",
			"caption" => "Organisatsioon",
			"sortable" => 1,
			"align" => "left",
			"width" => "95%"
		));
				
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));
			
		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_HALDUR)) as $manager_conn)
		{
			$table->define_data(array(
				"organisatsioon" => html::href(array(
								"caption" 	=> $manager_conn->prop("to.name"),
								"url"		=> $this->mk_my_orb("change", array("id" => $manager_conn->prop("to")), "crm_company"), 
								)),
				"from"	=> $manager_conn->id(),
			));
				
		}
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

			
			$tree->add_item($parent, array(
				"name" =>	$tegevusala->name(),
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
	
	function do_manager_toolbar(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];
				
		$tb->add_menu_button(array(
    		"name" => "new",
    		"img" => "new.gif",
    		"tooltip" => "Click this button to create a new object",
		));
				
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta valitud seosed",
			"action" => "delete_rels",
			"confirm" => "Kustutada?",
		));
				
		$tb->add_menu_item(array(
    		"parent" => "new",
    		"text" => "Haldur",
    		"title" => "Haldur",
    				
    		"url" => $this->mk_my_orb("new", array(
    								"parent" => $arr["obj_inst"]->prop("orgs"),
    								"return_url" => urlencode(aw_global_get('REQUEST_URI')),
									"alias_to" => $arr["obj_inst"]->id(),
									"reltype" => RELTYPE_HALDUR,
    							), "crm_company"),
    		"disabled" => false,
		));
	}
	
	
	function do_my_personal_toolbar_mycandits(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta valitud seosed",
			"action" => "delete_rels",
			"confirm" => "Kas soovid valitud objektid kustutada?",
		));
	}
	
	function do_my_personal_toolbar(&$arr)
	{
		$my_person_obj = $this->get_my_profile();
				
		$tb = &$arr["prop"]["toolbar"];
		
		$tb->add_menu_button(array(
    		"name" => "new",
    		"img" => "new.gif",
    		"tooltip" => "New object",
		));
		
	
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta valitud seosed",
			"action" => "delete_rels",
			"confirm" => "Kas soovid valitud objektid kustutada?",
		));
		
		
		$tb->add_button(array(
				"name" => "Save",
				"img" => "save.gif",
				"tooltip" => "Salvesta",
				"action" => "save_changes",
		));
		
		
		$tb->add_menu_item(array(
    		"parent" => "new",
    		"text" => "CV",
    		"title" => "CV",
    				
    		"url" => $this->mk_my_orb("new", array(
    								"parent" => $my_person_obj->prop("parent"),
    								"return_url" => urlencode(aw_global_get('REQUEST_URI')),
									"alias_to" => $my_person_obj->id(),
									"reltype" => RELTYPE_CV,
    							), "cv"),
    		"disabled" => false,
		));
	}
	
	function navtoolbar_job_seeker(&$arr)
	{
		$tb=&$arr["prop"]["toolbar"];
			
		$tb->add_menu_button(array(
    		"name" => "new",
    		"img" => "new.gif",
    		"tooltip" => "Uus",
		));
				
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta valitud seosed",
			"action" => "delete_rels",
		));	
				
		$connections = $arr["obj_inst"]->connections_from(array("type" => RELTYPE_HALDUR));
				
		if(count($connections)>1)
		{
			foreach ($connections as $conn)
			{
				$tb->add_sub_menu(array(
					"parent" => "new",
					"name" => $conn->prop("to.name"),
					"text" => $conn->prop("to.name"),
					"title" => $conn->prop("to.name"),
					"disabled" => false,
				));
						
				$tb->add_menu_item(array(
					"parent" => $conn->prop("to.name"),
					"text" => "Tööotsija",
					"title" => "Tööotsija",
					"url" => $this->mk_my_orb("new", array(
									"parent" => $arr["obj_inst"]->prop("persons"),
									"return_url" => urlencode(aw_global_get('REQUEST_URI')),
									"alias_to" => $conn->prop("to"),
									"reltype" => 21,
					) ,"crm_person"),
					
					));	
			}
		}		
		elseif(count($connections)==1)
		{
			$conn=array_pop($connections);
			$tb->add_menu_item(array(
					"parent" => "new",
					"text" => "Tööpakkuja",
					"title" => "Tööpakkuja",
					"url" => $this->mk_my_orb("new", array(
									"parent" => $arr["obj_inst"]->prop("orgs"),
									"return_url" => urlencode(aw_global_get('REQUEST_URI')),
									"alias_to" => $conn->prop("to"),
									"reltype" => 20,
						) ,"crm_person"),			
			));
		}
	}
	
	function do_org_toolbar(&$arr)
	{

		if($myorg_profile = &$this->get_my_org_profile()) 
		{
			$tb = &$arr["prop"]["toolbar"];
			if($arr["request"]["group"] == "org_jobs" or $arr["request"]["group"] == "org_profile")
			{
			
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
						"parent" => $arr["obj_inst"]->prop("orgs"),
						"return_url" => urlencode(aw_global_get('REQUEST_URI')),
						"alias_to" => $myorg_profile->id(),	
						"reltype" => RELTYPE_JOBS,
					
						), "job_offer")
				));
			}		
			
			$tb->add_button(array(
				"name" => "delete",
				"img" => "delete.gif",
				"tooltip" => "Kustuta valitud seosed",
				"action" => "delete_rels",
			));	
			
			if($arr["request"]["group"] == "org_jobs_candits")
			{
				$jobs_options[] = "--Kõik tööpakkumised--";
				$org_obj = $this->get_my_org_profile();
			
				foreach ($org_obj->connections_from(array("type" => RELTYPE_JOBS)) as $joboffer)
				{
					$jobs_options[$joboffer->prop("to")] = $joboffer->prop("to.name");	
				}
			
				$orgjobs_sel = html::select(array(
					"name" => "org_jobs",
					"options" => $jobs_options,
					"selected" => $arr["request"]["jobid"],
				));
				
				$tb->add_button(array(
					"name" => "bookmark",
					"tooltip" => "Vali huvitavad kandidaadid",
					"action" => "bookmark_persons",
				));	
			
				$tb->add_cdata($orgjobs_sel, "right");	
			
				$tb->add_button(array(
					"name" => "refesh",
					"img" => "refresh.gif",
					"tooltip" => "Uuenda",
					"action" => "refresh_jobs",
					"side" => "right",
				));				
			}
		}
	}
	
	/**
		@attrib name=bookmark_persons
	**/
	function bookmark_persons($arr)
	{
		foreach ($arr["sel"] as $cv)
		{
			$cvconnection = new connection($cv);
			$cv = $cvconnection->to();
			$person_conn = array_pop($cv->connections_from(array("type" => RELTYPE_CV_OWNER)));
			$persons[] = $person_conn->to();
		}
		
		$persons = array_unique($persons);
		$my_org_profile = $this->get_my_org_profile();
		
		foreach($persons as $person)
		{
			$my_org_profile->connect(array(
				"to" => $person->id(),
				"reltype" => RELTYPE_KANDIDAAT,
			));	
		}
	}
	
	function do_navtoolbar_joboffer(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];
				
		$tb->add_menu_button(array(
    			"name" => "new",
    			"img" => "new.gif",
    			"tooltip" => "Click this button to create a new object",
		));
				
		$connections = $arr["obj_inst"]->connections_from(array("type" => RELTYPE_HALDUR));
				
		//Kui haldureid on rohkem kui 1
		if(count($connections)>1)
		{
			foreach ($connections as $conn)
			{
				$tb->add_sub_menu(array(
						"parent" => "new",
						"name" => $conn->prop("to.name"),
						"text" => $conn->prop("to.name"),
						"title" => $conn->prop("to.name"),
						"disabled" => false,
				));
						
				$tb->add_menu_item(array(
						"parent" => $conn->prop("to.name"),
						"text" => "Tööpakkuja",
						"title" => "Tööpakkuja",
						"url" => $this->mk_my_orb("new", array(
										"parent" => $arr["obj_inst"]->prop("orgs"),
										"return_url" => urlencode(aw_global_get('REQUEST_URI')),
										"alias_to" => $conn->prop("to"),
										"reltype" => 20,
							) ,"crm_company"),
				));
						
			}
		}		
		elseif(count($connections)==1)
		{
			$conn=array_pop($connections);
			$tb->add_menu_item(array(
					"parent" => "new",
					"text" => "Tööpakkuja",
					"title" => "Tööpakkuja",
					"url" => $this->mk_my_orb("new", array(
									"parent" => $arr["obj_inst"]->prop("orgs"),
									"return_url" => urlencode(aw_global_get('REQUEST_URI')),
									"alias_to" => $conn->prop("to"),
									"reltype" => 20,
						) ,"crm_company"),
					
			));
		}
						
		$tb->add_button(array(
				"name" => "delete",
				"img" => "delete.gif",
				"tooltip" => "Kustuta valitud seosed",
				"action" => "delete_rels",
		));	
	}
	
	function do_sector_table_cvs(&$arr)
	{
		$table =& $arr["prop"]["vcl_inst"];
		$this->do_cvtable_cols($table);
		
		if($_GET["sector_id"] or $arr["request"]["group"]=="cv_nimekiri")
		{	
			foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_HALDUR)) as $haldur)
			{
				$haldur = $haldur->to();
				foreach ($haldur->connections_from(array("type" => RELTYPE_TOOTSIJA)) as $toootsija)
				{
					$toootsija = $toootsija->to();
					foreach ($toootsija->connections_from(array("type" => RELTYPE_CV)) as $mycv)
					{
						$mycv_conn_id = $mycv->id();
						$mycv = $mycv->to();
						
						if($mycv->status() == STAT_ACTIVE)
						{
 							
							if($arr["request"]["group"]=="tootsijad_valdkonnad")
							{
								$valdkond_conn = new connection();
						
								$valdkond_conn = $valdkond_conn->find(array(
									"type" => RELTYPE_TEGEVUSVALDKOND,
									"from" => $mycv->id(),
									"to" => $_GET["sector_id"],
								));
							}
							if($valdkond_conn or $arr["request"]["group"]=="cv_nimekiri")
							{
								$table->define_data(array(
									"nimi" => html::href(array(
										"caption" => $toootsija->name(),
										"url" => $this->mk_my_orb("change", array("id" => $mycv->id()), "cv"),
									)),
									"lisatud" =>  get_lc_date($mycv->created()),
									"muudetud" => get_lc_date($mycv->modified()),
									"from" => $mycv_conn_id,				
								));
							}
						}
					}
				}
			}
		}
	}
		
	function do_persontable(&$arr)
	{
		$table=&$arr["prop"]["vcl_inst"];	
		$this->do_personlist_table_cols($table);
		$manager_connections = $arr["obj_inst"]->connections_from(array("type" => RELTYPE_HALDUR));
		
	
		foreach ($manager_connections as $manager)
		{
			$manager_obj = $manager->to();
			
			foreach ($manager_obj->connections_from(array("type" => RELTYPE_TOOTSIJA)) as $toootsija)
			{
				$toootsija_conn_id = $toootsija->id();
				
				$toootsija = $toootsija->to();
				
				$cvlink = html::href(array(
							"caption" => "Lisa CV",
							"url" => $this->mk_my_orb("new", array(
											"parent" => $arr["obj_inst"]->prop("cvparent"),
											"return_url" => urlencode(aw_global_get('REQUEST_URI')),
											"alias_to" => $toootsija->id(),
											"reltype" => RELTYPE_CV,
							), "cv"),
				));
				
				$person_cvs = $toootsija->connections_from(array("type" => RELTYPE_CV));
				
				if($person_cvs)
				{
					$person_cvs = array_pop($person_cvs);
					$person_cvs = $person_cvs->to();
					
					$view_cvs_link = html::href(array(
								"url" => $this->mk_my_orb("change", array(
									"id" => $person_cvs->id(),
									"group" => "othercvs",
									), "cv"),
								"caption" => "Vaata CV-sid",
							));
				}	
				else
				{
					$view_cvs_link = "CV Puudub";
				}
				$table->define_data(array(
					"nimi" => html::href(array(
							"caption" => $toootsija->name(),
							"url" => $this->mk_my_orb("change", array("id" => $toootsija->id()), "crm_person"),
						)),
					"cv" => $cvlink,
					"cv_view" => $view_cvs_link,
					"from" => $toootsija_conn_id,				
				));		
			}
		}	
		
	}
	
	
	function do_mycantis_table(&$arr)
	{
		if($person_obj = &$this->my_profile)
		{
			$table=&$arr["prop"]["vcl_inst"];
						
			$table->define_field(array(
				"name" => "ametikoht",
				"caption" => "Ametikoht",
				"sortable" => 1,
				"width" => "45%",
			));
			
			$table->define_field(array(
				"name" => "org",
				"caption" => "Organisatsioon",
				"sortable" => 1,
				"width" => "40%",				
			));	
			
			$table->define_field(array(
				"name" => "deadline",
				"caption" => "Tähtaeg",
				"sortable" => 1,
				"width" => "10%",
				"align" => "center",
					
			));
			
			$table->define_chooser(array(
				"name" => "sel",
				"field" => "from",
				"align" => "center",
				"width" => "5%",
			));
			
			foreach ($person_obj->connections_from(array("type" => RELTYPE_CV)) as $cv)
			{
				$cv = $cv->to();
				
				foreach ($cv->connections_to(array("from.class_id" => CL_JOB_OFFER)) as $candit)
				{
					
					$job = &obj($candit->prop("from"));
					foreach ($job->connections_to(array("from.class_id" => CL_CRM_COMPANY)) as $company)
					{
						$table->define_data(array(
							"ametikoht" => html::href(array(
									"url" => $this->mk_my_orb("change", array("id" => $job->id()), "job_offer"),
									"caption" => $job->name(),
								)),
							"deadline" => get_lc_date($job->prop("deadline")),
							"org" => html::href(array(
								"caption" => $company->prop("from.name"),
								"url" => $this->mk_my_orb("change", array("id" => $company->prop("from")), "crm_company"),
							)),
						));
					}
				}
			}
		}
	}
	
	
	function do_mycvs_table(&$arr)
	{
		if($person_obj = &$this->my_profile)
		{
			$table =& $arr["prop"]["vcl_inst"];
			
			$table->define_field(array(
				"name" => "nimi",
				"caption" => "Nimi",
				"sortable" => 1,
				"width" => "85%"
			));
				
			$table->define_field(array(
				"name" => "aktiivne",
				"caption" => "Aktiivne",
				"sortable" => 1,
				"width" => "5%",
				"align" => "center",
			));
			
			$table->define_field(array(
				"name" => "muudetud",
				"caption" => "Muudetud",
				"sortable" => 1,
				"width" => "10%",
				"align" => "center"
			));	

			
			$table->define_chooser(array(
				"name" => "sel",
				"field" => "from",
				"align" => "center",
			));
			
			foreach ($person_obj->connections_from(array("type" => RELTYPE_CV)) as $cv)
			{
				$cv_connid = $cv->id();
				$cv = $cv->to();
				
				$cv_id = $cv->id();
				
				if($cv->status() == STAT_ACTIVE)
				{
					$checked = 1;
				}
				else
				{
					$checked = 0;
				}
				
				$table->define_data(array(
					"nimi" => html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $cv->id()), "cv"),
						"caption" => $cv->name(),
					)),
					"lisatud"  => get_lc_date($cv->created()),
					"muudetud" => get_lc_date($cv->modified()),
					"aktiivne" => html::checkbox(array(
									"name" => "act[$cv_id]",
									"value" => $cv->status(),
									"checked" => $checked,
								)),
					"from"		=> $cv_connid,
				));
				
			}
			
		}
	}
	
	function do_org_jobs_table(&$arr)
	{
		if($myorg_obj = $this->get_my_org_profile())
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
			
			foreach ($myorg_obj->connections_from(array("type" => RELTYPE_JOBS)) as $job_offer)
			{
				
				$job_offer = $job_offer->to();
				$candits_count = count(($job_offer->connections_from(array("type" => 5))));
				
				$table->define_data(array(
					"ametikoht" =>	html::href(array(
								"url" => $this->mk_my_orb("change", array("id" => $job_offer->id()), "job_offer"),
								"caption" => $job_offer->name(),
								)),
					"deadline" => get_lc_date($job_offer->prop("deadline")),
					"kandidaate" => html::href(array(
									"caption" => $candits_count,
									"url" => $this->mk_my_orb("change", array("id" => $job_offer->id(), "group" => "kandideerinud"), "job_offer"),
								)),
					"status" => "Aktiivne",
				));
			}	
		}
	}
	
	function get_my_org_profile()
	{
		$gidlist = aw_global_get("gidlist");

		//Koneverime grupi-id, objekti -id ks.
		foreach ($gidlist as $key => $value)
		{
			$gidlist[$key] = users::get_oid_for_gid($value);
		}

		if(array_search(aw_ini_get("employer.group") , $gidlist))
		{
			
			$user_id = users::get_oid_for_uid(aw_global_get("uid"));
			$user_obj = & obj($user_id);
			$person_obj = array_pop(( $user_obj->connections_from(array("type" => RELTYPE_PERSON))));
			$person_obj = $person_obj->to();
			if(is_object($person_obj))
			{
				$org_obj = array_pop($person_obj->connections_from(array("type" => RELTYPE_WORK)));
				return $org_obj->to();
			}
			
		}	
		
	}
	
	//See funktsioon leiab sisseloginud kasutjale vastava isikuobjekti.
	function get_my_profile()
	{	
		$gidlist = aw_global_get("gidlist");
		//Koneverime grupi-id, objekti -id ks.
		foreach ($gidlist as $key => $value)
		{
			$gidlist[$key] = users::get_oid_for_gid($value);
		}

		//Kas kasutaja on tööotsijate grupis
		if(array_search(aw_ini_get("employee.group") , $gidlist))
		{
			$user_id = users::get_oid_for_uid(aw_global_get("uid"));
			$user_obj = & obj($user_id);
			$person_obj = array_pop(( $user_obj->connections_from(array("type" => RELTYPE_PERSON))));
			return $person_obj->to();
		}
	}
	
	function get_my_mrg_profile()
	{
		//List of groups where user belongs
		$gidlist = users::getgroupsforuser(aw_global_get("uid"));
		
		//Konverdime grupi_id-d objekti id-deks.
		foreach ($gidlist as $key=>$value)
		{
			$gidlist[$key] = users::get_oid_for_gid($key); 
		}
		
		if(array_search(aw_ini_get("emp_manager.group") , $gidlist))
		{
			$user_id = users::get_oid_for_uid(aw_global_get("uid"));
			$user_obj = & obj($user_id);
			$person_obj = array_pop(( $user_obj->connections_from(array("type" => RELTYPE_PERSON))));
			$person_obj = $person_obj->to();
			if(is_object($person_obj))
			{
				$org_obj = array_pop($person_obj->connections_from(array("type" => RELTYPE_WORK)));
				return $org_obj->to();
			}
		}
			
	}
	
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($data["name"])
        {	
			case "my_personal_info_firstname":
				$this->my_profile->set_prop("firstname", $data["value"]);
			break;
			
			case "my_personal_info_lastname":
				$this->my_profile->set_prop("lastname", $data["value"]);
			break;
			
			case "my_personal_info_id":
				$this->my_profile->set_prop("personal_id", $data["value"]);
			break;
			
			case "my_personal_info_gender":
				$this->my_profile->set_prop("gender", $data["value"]);
				$this->my_profile->save();
			break;
		}
		return $retval;
	}	
	
	function parse_alias($arr)
	{
		return $this->change(array(
			"id" => $arr["alias"]["target"]
		));
	}
	
}
?>
