<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/Attic/personnel_management_cv.aw,v 1.8 2005/04/26 14:14:52 duke Exp $
// personnel_management_cv.aw - CV 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_PERSONNEL_MANAGEMENT_CV, on_cv_save)

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_CV relationmgr=yes no_yah=1

@default table=objects
@default group=general


@tableinfo staff_cv master_table=objects master_index=oid index=oid

@property active_until type=date_select group=general table=staff_cv
@caption Aktiivne kuni

-----------------------------
@property comuter_skills_tb type=toolbar no_caption=1 store=no group=arvutioskus
@property previous_jobs_tb type=toolbar no_caption=1 store=no group=tookogemus
@property lang_skills_tb type=toolbar no_caption=1 store=no group=keeleoskused

@property jobs type=table store=no group=tookogemus no_caption=1
@property comp_skill_label type=text group=arvutioskus subtitle=1 value=Lisa&nbsp;arvutioskus store=no

@property kogemused type=releditor reltype=RELTYPE_KOGEMUS props=asutus,algus,kuni,ametikoht,tasks group=tookogemus store=no
@caption Kogemused

@property juhiload type=classificator method=serialize group=driving_licenses store=connect reltype=RELTYPE_JUHILUBA
@caption Juhiload

@property soovitajad type=textarea group=soovitajad field=recommenders table=staff_cv
@caption Soovitajad

property sain_tood type=checkbox group=toosoov field=gotjob table=staff_cv
caption Sain t&ouml;&ouml;d teie kaudu

@property stats_table type=table group=statistika no_caption=1

@property cv_view_tb type=toolbar no_caption=1 store=no group=cv_view
@property cv_view type=text no_caption=1 store=no group=cv_view

------------------ARVUTIOSKUSED---------------------
@property computer_skills type=table store=no group=arvutioskus no_caption=1
@property comp_skill_label type=text group=arvutioskus subtitle=1 value=Arvutioskuste&nbsp;lisamine store=no
@property arvutioskus type=releditor reltype=RELTYPE_ARVUTIOSKUS props=oskus,tase group=arvutioskus store=no
@caption Arvutioskus

@property other_computer_skills type=textarea table=staff_cv group=arvutioskus

@property other_compterskills_label type=text group=arvutioskus store=no
@caption Teised arvutioskused

------------------KEELEOSKUS-------------------------
@property keeleoskused type=table store=no group=keeleoskused no_caption=1
@property lang_skill_label type=text group=keeleoskused subtitle=1 value=Lisa&nbsp; store=no
@property language_skills type=releditor reltype=RELTYPE_LANG props=keel,tase  group=keeleoskused store=no
@caption Keeleoskus
@property other_languages_label type=text group=keeleoskused store=no
@property other_languages type=textarea group=keeleoskused table=staff_cv
@caption Teised keeled

------------------TÖÖSOOVID--------------------------
@property jobs_wanted_tb type=toolbar no_caption=1 store=no group=toosoov
@property jobs_wanted_table type=table no_caption=1 group=toosoov
@property jobs_wanted type=releditor reltype=RELTYPE_JOBWANTED props=name,palgasoov,valdkond,liik,asukoht,koormus,lisainfo,sbutton group=toosoov store=no
@property jobs_wanted_label type=text store=no subtitle=1 group=toosoov
@caption Lisa uus töösoov

-------------------HARIDUS----------------------------
@property education_tb type=toolbar no_caption=1 store=no group=haridustee
@property educationtabel type=table no_caption=1 store=no group=haridustee
@property haridus_label type=text group=haridustee subtitle=1 store=no
@property education type=releditor reltype=RELTYPE_EDUCATION props=lisainfo_edu,loppaasta,algusaasta,eriala,kool,education_type group=haridustee store=no

-----------------TÄIENDUSKOOLITUS---------------------
@property education_additional_tb type=toolbar no_caption=1 group=taienduskoolitus
@property education_additional_table type=table no_caption=1 group=taienduskoolitus
@property education_additional type=releditor reltype=RELTYPE_EDUCATION props=lisainfo_edu,date_from,date_to,kool,education_type,submit_edu group=taienduskoolitus store=no group=taienduskoolitus

-------------------OSKUSED----------------------------
@property additional_skills type=textarea group=other_skills table=staff_cv
@caption Muud oskused 

@property driving_since type=select table=staff_cv group=driving_licenses
@caption Juhistaa¾ alates aastast

--------------------TABID----------------------------
@groupinfo skills caption="Oskused"
@groupinfo arvutioskus caption="Arvutioskus" parent=skills
@groupinfo keeleoskused caption="Keeleoskus" parent=skills

@groupinfo driving_licenses caption="Juhiload" parent=skills
@groupinfo other_skills caption="Muud oskused" parent=skills

@groupinfo education_main caption="Hariduskäik" submit=no 
@groupinfo haridustee caption="Hariduskäik" submit=no parent=education_main
@groupinfo taienduskoolitus caption="Täienduskoolitus" submit=no parent=education_main


@groupinfo soovitajad caption="Soovitajad"

@groupinfo tookogemus caption="Töökogemused"
@groupinfo toosoov caption="Soovitud töö" submit=no

@groupinfo statistika caption="Statistika" submit=no
@groupinfo cv_view caption="Vaata CV-d" submit=no

---------------------SEOSED---------------------
@reltype EDUCATION value=1 clid=CL_EDUCATION 
@caption Haridus

@reltype LANG value=2 clid=CL_PERSONALIHALDUS_LANG
@caption Keeleoskus

@reltype ARVUTIOSKUS value=3 clid=CL_PERSONALIHALDUS_ARVUTIOSKUS
@caption Arvutioskus

@reltype KOGEMUS value=4 clid=CL_PERSONALIHALDUS_TOOKOGEMUS
@caption Kogemus

@reltype JUHILUBA value=8 clid=CL_META
@caption Juhiluba

@reltype JOBWANTED value=10 clid=CL_PERSONNEL_MANAGEMENT_JOB_WANTED
@caption Töösoov

*/

class personnel_management_cv extends class_base
{
	var $my_profile;
	
	function personnel_management_cv()
	{
		// change this to the folder under the templates folder, where this classes templates will be,
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_PERSONNEL_MANAGEMENT_CV,
			"tpldir" => "applications/personnel_management/personnel_management_cv",
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

	/**
	@attrib name=change nologin="1" all_args="1"
	**/
	function change($params)
	{
		return parent::change($params);
	}

	//When new cv is saved, this function is called by message. Creates relation betweeb current logged user and cv.
	function on_new_cv($arr)
	{
		$cv_obj = &obj($arr["oid"]);
		if($this->my_profile["group"] == "employee")
		{
			$this->my_profile["person_obj"]->connect(array(
				"to" => $cv_obj->id(),
				"reltype" => 19,
			));
		}	
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "education_additional_table":
				$this->do_education_additional_table($arr);
			break;
			
			case "education_additional_tb":
				$this->do_education_additional_tb($arr);
			break;
			
			case "other_computer_skills":
				if(!$arr["request"]["ocomp"])
				{
					return PROP_IGNORE;
				}
			break;
			case "other_compterskills_label":
				if($arr["request"]["ocomp"])
				{
					return PROP_IGNORE;
				}
				$prop["value"] = html::href(array(
					"caption" => t("Teised arvutioskused"),
					"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "ocomp" => true), CL_PERSONNEL_MANAGEMENT_CV),
				));
			break;
			case "comp_skill_label":
				if($arr["request"]["eoid"])
				{
					$prop["value"] = t("Arvutioskuse muutmine");
				} 
			break;
			case "other_languages":
				if(!$arr["request"]["olang"])
				{
					return PROP_IGNORE;
				}
			break;
			case "other_languages_label":
				if($arr["request"]["olang"])
				{
					return PROP_IGNORE;
				}
				$prop["value"] = html::href(array(
					"caption" => t("Mõni muu keel"),
					"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "olang" => true), CL_PERSONNEL_MANAGEMENT_CV),
				));
			
			break;
			case "driving_since":
				for($i=date("Y"); $i>date("Y") - 80; $i--)
				{
					$prop["options"][$i]=$i;
				}
			break;
			case "jobs_wanted_label":
				if(!$arr["request"]["eoid"])
				{
					return PROP_IGNORE;
				}
				elseif(is_numeric($arr["request"]["eoid"]))
				{
					$prop["caption"] = t("Muuda töösoovi");
				}
			break;
			case "jobs_wanted_tb":
				$this->jobs_wanted_tb($arr);
			break;
			
			case "computer_skills":
				$this->do_computer_skills_table(&$arr);
			break;
			case "comuter_skills_tb":
				$this->do_comuter_skills_tb(&$arr);
			break;
			case "arvutioskus":
				if($arr["request"]["ocomp"])
				{
					return PROP_IGNORE;
				}
				$prop["rel_id"] = $arr["request"]["eoid"];
			break;
			case "jobs":
				$this->do_jobs_table($arr);
			break;

			case "previous_jobs_tb":
				$this->do_previous_jobs_tb($arr);
			break;

			case "kogemused":
				$prop["rel_id"] = $arr["request"]["eoid"];
			break;

			case "keeleoskused":
				$this->do_lang_table($arr);
			break;

			case "lang_skills_tb":
				$this->do_lang_tb($arr);
			break;

			case "cv_view_tb":
				$this->do_view_tb($arr);
			break;

			case "language_skills":
				if($arr["request"]["olang"])
				{
					return PROP_IGNORE;
				}
				$prop["rel_id"] = $arr["request"]["eoid"];
			break;

			case "education_tb":
				$this->do_education_tb($arr);
			break;

			case "educationtabel":
				$this->do_educationtabel($arr);
			break;
			
			case "jobs_wanted":
				$prop["rel_id"] = $arr["request"]["eoid"];
				if(!$arr["request"]["eoid"]=="new")
				{
					$prop["rel_id"] = $arr["request"]["eoid"];
					return PROP_IGNORE;
				}
			break;
			
			case "jobs_wanted_table":
				
				if($this->do_jobs_wanted_table($arr) == 0)
				{
					return PROP_IGNORE;
				}
			break;
			
			case "active_until":
				$prop["year_from"] = date("Y", time());
				
				$manager = current($this->my_profile["manager_list"]);
				if(!is_object($manager))
				{
					return PROP_IGNORE;
				}
				
				if($manager->meta("cv_acitvity_prop") == 1)
				{
					return PROP_IGNORE;
				}
			break;
			
			case "haridus_label":
				return PROP_IGNORE;
			break;
			
			case "education":
				$prop["rel_id"] = $arr["request"]["eoid"];
				if($arr["request"]["eoid"])
				{
					$conn = new connection($arr["request"]["eoid"]);
					$eduobj = $conn->to();
					$arr["request"]["type"] = $eduobj->prop("education_type");
				}
				
				if(!$arr["request"]["type"])
				{
					return PROP_IGNORE;
				}
				
				$manager = current($this->my_profile["manager_list"]);		
				$configform = &obj($arr["request"]["type"]);
				
				$prop["props"] = array();
				
				foreach ($configform->meta("cfg_proplist") as $key=>$value)
				{
					
					$prop["props"][] = $key;
					$value;
				}
				
			break;
			
			case "stats_table":
				$this->do_stats_table($arr);
			break;
			
			case "education_additional":
				$prop["rel_id"] = $arr["request"]["eoid"];
			break;
			
			case "cv_view":
				$prop["value"] = $this->show(array("id" => $arr["obj_inst"]->id()));
				if($this->my_profile["group"]=="employer")
				{
					$this->add_view(array("id" => $arr["obj_inst"]->id()));
				}
			break;
		};
		return $retval;
	}

	/**
		@attrib name=gen_job_pdf nologin="1"
		@param id required type=int
	**/
	function gen_job_pdf($arr)
	{
		$job = &obj($arr["id"]);
		$pdf_gen = get_instance("core/converters/html2pdf");
		session_cache_limiter("public");
		header('Content-type: application/pdf');
		die($pdf_gen->convert(array(
			"source" => $this->show(array(
				"id" => $arr["id"]
			))
		)));
	}

	function do_cv_view_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "pdf",
			"img" => "pdf_upload.gif",
			"tooltip" => t("Genereeri pdf"),
			"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id()))
		));
	
	}
	
	function do_jobs_wanted_table(&$arr)
	{
		$jobswanted = $arr["obj_inst"]->connections_from(array("type" => "RELTYPE_JOBWANTED"));
		if(!$jobswanted)
		{
			return false;
		}
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "ametinimetus",
			"caption" => t("Ametinimetus"),
			"sortable" => 1
		));
		
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));
		
		foreach ($jobswanted as $jobwanted)
		{
			$table->define_data(array(
				"ametinimetus" => html::href(array(
								"caption" => $jobwanted->prop("to.name"),
								"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "eoid" => $jobwanted->id()), CL_PERSONNEL_MANAGEMENT_CV)
							)),
				"from" => $jobwanted->id(),
			));
		}
		return true;
	}
	
	function do_educationtabel(&$arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "kool",
			"caption" => t("Kool"),
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "periood",
			"caption" => t("Periood"),
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "type",
			"caption" => t("Hariduse liik"),
			"sortable" => 1
		));

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));
		
		//Tuleks ymber teha
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_EDUCATION")) as $haridus)
		{
			$connection_id = $haridus->id();

			$haridus = $haridus->to();
			
			//Just for old version support
			if($haridus->prop("education_type") <= 6)
			{
				continue;
			}
			
			$liik = &obj($haridus->prop("education_type"));
			
			if(is_object($liik))
			{
				$liik = $liik->name();
			}
			
			if($haridus->prop("algusaasta"))
			{
				$periood = $haridus->prop("algusaasta") ."-". $haridus->prop("loppaasta");
			}
			elseif ($haridus->prop("date_from") && $haridus->prop("date_to"))
			{
				$periood = get_lc_date($haridus->prop("date_from")) . " - " . get_lc_date($haridus->prop("date_to"));
			}			
			$table->define_data(array(
				"kool" => html::href(array(
							"caption" => $haridus->prop("kool"),
							"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "eoid" => $connection_id), CL_PERSONNEL_MANAGEMENT_CV)
						)),
				"periood" => $periood,
				"from" => $connection_id,
				"type" => $liik,
			));

		}

		$table->set_default_sortby("periood");
		$table->sort_by();
	}
	
	function do_education_additional_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta &otilde;pingud"),
			"action" => "delete_rels",
			"confirm" => t("Kas soovid kustutada valitud täienduskoolitused?")
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => t("Muuda valitud täienduskoolitust"),
			"action" => "edit_something",
		));
	}
	
	function jobs_wanted_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];

		$tb->add_menu_button(array(
   			"name" => "new",
   			"img" => "new.gif",
   			"tooltip" => t("Uus"),
		));
		
		$tb->add_menu_item(array(
    		"parent" => "new",
    		"text" => t("Töösoov"),
    		"title" => t("Töösoov"),
    		"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "eoid" => "new"), "personnel_management_cv", true, true),
    		"disabled" => false,
		));
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta &otilde;pingud"),
			"action" => "delete_rels",
			"confirm" => t("Kas soovid kustutada valitud töösoovid?")
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => t("Muuda valitud töösoovi"),
			"action" => "edit_something",
		));
	}
	
	function do_education_additional_table(&$arr)
	{
		$table =& $arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "school",
			"caption" => t("Haridusasutus"),
			"sortable" => 1
		));
		
		$table->define_field(array(
			"name" => "periood",
			"caption" => t("Periood"),
			"sortable" => 1
		));
		
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));
		
		$manager = current($this->my_profile["manager_list"]);		
		
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_EDUCATION")) as $haridus)
		{
			$conn_id = $haridus->id();
			$haridus = $haridus->to();
			if($haridus->prop("education_type") == $manager->meta("add_edu_form"))
			{
				$table->define_data(array(
					"school" => html::href(array(
									"caption" => $haridus->prop("kool"),
									"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "eoid" => $conn_id), CL_PERSONNEL_MANAGEMENT_CV),
								)),
					"periood" => get_lc_date($haridus->prop("date_from"))."-".get_lc_date($haridus->prop("date_to")),
					"from" => $conn_id,
				));				
			}
		}
	}
	
	function callback_mod_tab($arr)
	{
		//Täienduskoolituse tabi peitmiseks
		switch ($arr["id"])
		{
			case "taienduskoolitus":
				$manager = current($this->my_profile["manager_list"]);		
				if(!$manager->meta("add_edu_form"))
				{
					return false;
				}
			break;
		}
	}
	
	function do_education_tb(&$arr)
	{

		$tb = &$arr["prop"]["toolbar"];

		$tb->add_menu_button(array(
   			"name" => "new",
   			"img" => "new.gif",
   			"tooltip" => t("Uus"),
		));

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta &otilde;pingud"),
			"action" => "delete_rels",
			"confirm" => t("Kas soovid kustutada valitud õpingud?")
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => t("Muuda valitud haridust"),
			"action" => "edit_something",
		));

		$manager = current($this->my_profile["manager_list"]);		
		
		foreach ($manager->meta("education_types") as $key=>$value)
		{
			$key_list[] = $key;
		}
		
		$educations_list = new object_list(array(
			"oid" => $key_list 
		));
		
		$educations_list->sort_by(array(
        	"prop" => "jrk",
        	"order" => "desc",
        ));
        
		foreach ($educations_list->arr() as $configform)
		{
			$tb->add_menu_item(array(
    			"parent" => "new",
    			"text" => $configform->name(),
    			"title" => $configform->name(),
    			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "type" => $configform->id()), "personnel_management_cv", true, true),
    			"disabled" => false,
			));
		}
	}

	function do_lang_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta keeleoskused"),
			"action" => "delete_rels",
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => t("Muuda keeleoskust"),
			"action" => "edit_something",
		));
	}

	function do_lang_table(&$arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "keel",
			"caption" => t("Keel"),
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "tase",
			"caption" => t("Tase"),
			"sortable" => 1
		));

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));

		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_LANG")) as $keeleoskus)
		{
			$connection_id=$keeleoskus->id();
			$keeleoskus = obj($keeleoskus->prop("to"));
			$keel = obj($keeleoskus->prop("keel"));
			$tase = obj($keeleoskus->prop("tase"));

			$table->define_data(array(
				"keel" => html::href(array(
							"caption" => $keel->name(),
							"url" => $this->mk_my_orb("change", array("group" => $arr["request"]["group"], "id" => $arr["obj_inst"]->id(), "eoid" => $connection_id), CL_PERSONNEL_MANAGEMENT_CV),
						)),
				"tase" => $tase->name(),
				"from" => $connection_id,
			));
		}

	}


	function do_previous_jobs_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta t&ouml;&ouml;kogemused"),
			"action" => "delete_rels",
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => t("Muuda t&ouml;&ouml;kogemust"),
			"action" => "edit_something",
		));
	}

	function do_jobs_table($arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "asutus",
			"caption" => t("Asutus"),
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => t("Ametikoht"),
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "alates",
			"caption" => t("Alates"),
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "kuni",
			"caption" => t("Kuni"),
			"sortable" => 1
		));

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));

		
		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_KOGEMUS")) as $kogemus)
		{
			$connection_id=$kogemus->id();
			$kogemus = obj($kogemus->prop("to"));
			$table->define_data(array(
				"asutus" => $kogemus->prop("asutus"),
				"ametikoht" => $kogemus->prop("ametikoht"),
				"alates" => get_lc_date($kogemus->prop("algus")),
				"kuni" => get_lc_date($kogemus->prop("kuni")),
				"from" => $connection_id,
			));
		}

	}

	function do_comuter_skills_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta arvutioskused"),
			"action" => "delete_rels",
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => t("Muuda oskust"),
			"action" => "edit_something",
		));
	}

	/**
		@attrib name=edit_something
	**/
	function edit_something($arr)
	{
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"], "eoid" => current($arr["sel"])), $arr["class"]);
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

	function do_computer_skills_table(&$arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "oskus",
			"caption" => t("Oskus"),
			"sortable" => true,
		));

		$table->define_field(array(
			"name" => "tase",
			"caption" => t("Tase"),
			"sortable" => true,
		));

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));

		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ARVUTIOSKUS")) as $c_skill)
		{
			$connection_id=$c_skill->id();
			$c_skill = obj($c_skill->prop("to"));

			$oskus = obj($c_skill->prop("oskus"));
			$tase = obj($c_skill->prop("tase"));
		
			$table->define_data(array(
				"oskus" => html::href(array(
							"caption" => $oskus->name(),
							"url" => $this->mk_my_orb("change", array("group" => $arr["request"]["group"], "id" => $arr["obj_inst"]->id(), "eoid" => $connection_id), CL_PERSONNEL_MANAGEMENT_CV),
						)),
				"tase" => $tase->name(),
				"from" => $connection_id,
			));
		}

	}

	/**
		@attrib name=cv_to_not_act
	**/
	function cv_to_not_act($arr)
	{
		$not_act_list = new object_list(array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_CV,
			"active_until" => new obj_predicate_compare(OBJ_COMP_LESS, time()),
		));
		
		foreach ($not_act_list->arr() as $ob)
		{
			$ob->set_status(STAT_NOTACTIVE);
			$ob->save();
		}
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{		
			case "status":
				//Aktiivseks ei saa muuta cv-d mille tähtaeg on möödas
				$manager = current($this->my_profile["manager_list"]);
				if(!is_object($manager))
				{
					return 0;
				}
				if(mktime(0, 0, 0, $arr["request"]["active_until"]["month"], $arr["request"]["active_until"]["day"], $arr["request"]["active_until"]["year"]) < time() and $manager->meta("cv_acitvity_prop") == 0)
				{
					$prop["value"] = STAT_NOTACTIVE;
				}
			break;
			
			case "active_until":
				$manager = current($this->my_profile["manager_list"]);
				if(!is_object($manager))
				{
					return 0;
				}
				
				if(!$manager->meta("max_active_cv"))
				{
					$max_active_cv = $manager->meta("max_active_cv") * 24 * 3600;
				}
				else
				{
					$max_active_cv = 365 * 24 * 3600;
				}
				
				$max_time = time() + $max_active_cv;
				
				if($manager->meta("max_active_cv")>0 and $manager->meta("cv_acitvity_prop") == 0)
				{
					$time = mktime(0, 0, 0, $prop["value"]["month"], $prop["value"]["day"], $prop["value"]["year"]);
	
					if($time > $max_time)
					{
						$arr["obj_inst"]->set_prop("active_until", $max_time);
						$arr["obj_inst"]->save();
						return PROP_IGNORE;
					}
				}
				elseif($manager->meta("cv_acitvity_prop") == 1)
				{
					$arr["obj_inst"]->set_prop("active_until", $max_time);
					$arr["obj_inst"]->save();
					return PROP_IGNORE;
				}
			break;
		}
		return $retval;
	}


	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["id"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$person_obj = current($ob->connections_to(/*array("from.class_id" => CL_CRM_PERSON)*/));
		$person_obj = &obj($person_obj->prop("from"));

		$email_obj = &obj($person_obj->prop("email"));
		$phone_obj = &obj($person_obj->prop("phone"));


		$this->read_template("show.tpl");

		if($person_obj->prop("gender") == 1)
		{
			$gender ="Mees";
		}
		else
		{
			$gender ="Naine";
		}
		
		foreach ($ob->connections_from(array("type" => "RELTYPE_KOGEMUS")) as $kogemus)
		{
			$kogemus = $kogemus->to();

			$this->vars(array(
				"company" => $kogemus->prop("asutus"),
				"period" => get_lc_date($kogemus->prop("algus"))." - ".get_lc_date($kogemus->prop("kuni")),
				"profession" => $kogemus->prop("ametikoht"),
				"duties" => $kogemus->prop("tasks"),
			));
			$kogemused_temp .= $this->parse("work_experiences");
		}
		
		//Valdkondade nimekiri
		foreach ($ob->connections_from(array("type" => "RELTYPE_TEGEVUSVALDKOND")) as $sector)
		{
			$this->vars(array(
				"sector" => $sector->prop("to.name"),
			));
			$tmp_sectors.=$this->parse("sectors");
		}
		
		
		//Hariduste nimekiri
		foreach ($ob->connections_from(array("type" => "RELTYPE_EDUCATION")) as $haridus)
		{
			$haridus = $haridus->to();
			$haridus->prop("algusaasta");
			$period = $haridus->prop("algusaasta")." - ". $haridus->prop("loppaasta");
			
			
			$eriala = array_pop($haridus->connections_from(array("type" => "RELTYPE_ERIALA")));
			if (is_object($eriala))
			{
				$ename = $eriala->prop("to.name");
			}
			
			$this->vars(array(
				"oppevorm" => 	$haridus->prop("oppevorm"),
				"oppeaste" => 	$haridus->prop("oppeaste"),
				"oppekava" => 	$haridus->prop("oppekava"),
				"teaduskond" => $haridus->prop("teaduskond"),
				"eriala" =>		$ename,
				"school_name" =>$haridus->prop("kool"),
				"period" => 	$period,
				"addional_info" => $haridus->prop("lisainfo_edu"),
				"kogemused_list" => $kogemused_temp,
			));
			
			$temp_edu.= $this->parse("education");
		}
		
		foreach ($ob->connections_from(array("type" => "RELTYPE_JUHILUBA")) as $driving_license)
		{
			$driving_licenses.= ",".$driving_license->prop("to.name");
		}
		
		$ck = "";
		foreach($ob->connections_from(array("type" => "RELTYPE_ARVUTIOSKUS")) as $c)
		{
			$to = $c->to();
			$oskus = $to->prop("oskus");
			if ($oskus)
			{
				$oo = obj($oskus);
				$this->vars(array(
					"skill_name" => $oo->name()
				));
			}
			$tase = $to->prop("tase");
			if ($tase)
			{
				$oo = obj($tase);
				$this->vars(array(
					"skill_skill" => $oo->name()
				));
			}
			$ck .= $this->parse("COMP_SKILL");
		}

		$lsk = "";
		foreach($ob->connections_from(array("type" => "RELTYPE_LANG")) as $c)
		{
			$to = $c->to();
			$oskus = $to->prop("keel");
			if ($oskus)
			{
				$oo = obj($oskus);
				$this->vars(array(
					"skill_name" => $oo->name()
				));
			}
			$tase = $to->prop("tase");
			if ($tase)
			{
				$oo = obj($tase);
				$this->vars(array(
					"skill_skill" => $oo->name()
				));
			}
			$lsk .= $this->parse("LANG_SKILL");
		}

		$dsk = array();
		foreach($ob->connections_from(array("type" => "RELTYPE_JUHILUBA")) as $c)
		{
			$this->vars(array(
				"skill_name" => $c->prop("to.name"),
				"driving_since" => $ob->prop("driving_since")
			));
			$dsk[] = $this->parse("DRIVE_SKILL");
		}

		$ed = "";
		foreach($ob->connections_from(array("type" => "RELTYPE_EDUCATION")) as $c)
		{
			$to = $c->to();
			$d_from = $to->prop("algusaasta");
			if ($to->prop("date_from") > 100)
			{
				$d_from = get_lc_date($to->prop("date_from"),LC_DATE_FORMAT_LONG_FULLYEAR);
			}
			$d_to = $to->prop("loppaasta");
			if ($to->prop("date_to") > 100)
			{
				$d_to = get_lc_date($to->prop("date_to"),LC_DATE_FORMAT_LONG_FULLYEAR);
			}
			$this->vars(array(
				"from" => $d_from,
				"to" => $d_to,
				"where" => $to->prop("kool"),
				"extra" => nl2br($to->prop("lisainfo_edu"))
			));
			$ed .= $this->parse("ED");
		}

		$gidlist = aw_global_get("gidlist_oid");

		if(array_search(aw_ini_get("personnel_management.unloged_users") , $gidlist))
		{
			$personname = $person_obj->id();
		}
		else
		{
			$personname = $person_obj->name();
		}

		$this->vars(array(
			"COMP_SKILL" => $ck,
			"LANG_SKILL" => $lsk,
			"DRIVE_SKILL" => join(",", $dsk),
			"ED" => $ed,
			"recommenders" => nl2br($ob->prop("soovitajad")),
			"name" => $personname,
			"modified" => get_lc_date($ob->modified()),
			"birthday" => date("d.m.Y", $person_obj->prop("birthday")),
			"social_status" => $person_obj->prop("social_status"),
			"mail" => html::href(array(
				"url" => "mailto:" . $email_obj->prop("mail"),
				"caption" => $email_obj->prop("mail"),
			)),
			"phone" => $phone_obj->name(),
			"sectors" => $tmp_sectors,
			"education" => $temp_edu,
			"driving_licenses" => $driving_licenses,
			"addional_info" => $ob->prop("job_addinfo"),
			"gender" => $gender,
		));

		return $this->parse();
	}
	
	//When new cv is saved, this function is called by message. Creates relation betweeb current logged user and cv.
	function on_cv_save($arr)
	{
		if($this->my_profile["group"] == "employee")
		{
			$cv_obj = &obj($arr["oid"]);
			if($person_obj = &$this->my_profile["person_obj"])
			{				
				$person_obj->connect(array(
					"to" => $cv_obj->id(),
					"reltype" => 19,
				));
				$cv_obj->set_parent($this->my_profile["person_obj"]->parent());
				$cv_obj->save();
			}	
		}
	}
	
	function do_view_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "delete",
			"img" => "pdf_upload.gif",
			"tooltip" => t("Genereeri pdf"),
			"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id()))
		));
	}
	
	function add_view($arr)
	{
		if(!$_SESSION["cv_view".$arr["id"]])
		{ 
			$this->add_hit($arr["id"]);
			$oid = $arr["id"];
			$uid = aw_global_get("uid");
			$ip = getenv("REMOTE_ADDR");
			$time = time();
			$this->db_query("INSERT INTO cv_hits VALUES(NULL,'$oid', '$uid', '$ip', '$time')");
			$_SESSION["cv_view".$arr["id"]] = true;
		}
	}
	
	function do_stats_table(&$arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
			
		$table->define_field(array(
			"name" => "who",
			"caption" => t("Kes vaatas:"),
			"sortable" => 1
		));
			
		$table->define_field(array(
			"name" => "count",
			"caption" => t("Vaatamisi"),
			"sortable" => 1
		));

		$query_str = "SELECT *, count(uid) as vaatamisi FROM syslog WHERE oid=".$arr['obj_inst']->id()." GROUP by uid";
		$this->db_query($query_str);
		$results = array();
		$results = $this->db_fetch_array();
		
		foreach($results as $row)
		{
			$gidlist = aw_global_get("gidlist_oid");

			$user_obj = &obj(users::get_oid_for_uid($row["uid"]));
			$person = $user_obj->connections_from(array("type" => 2));

			if($person)
			{
					$person = array_pop($person);
					$person = $person->to();

					$company = $person->connections_from(array("type" => "RELTYPE_WORK"));
					if($company)
					{
						$company = array_pop($company);

						$table->define_data(array(
							"who" => $company->prop("to.name")/*html::href(array(
										"url" => $this->mk_my_orb("change", array("id" => $company->prop("to")), "crm_company"),
										"caption" => $company->prop("to.name")
									))*/,
							"count" => $row["vaatamisi"],
						));
					}
			}
		}

	}
}
?>
