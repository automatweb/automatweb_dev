<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/Attic/personnel_management_cv.aw,v 1.2 2004/05/23 11:46:45 kristo Exp $
// personnel_management_cv.aw - CV 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_PERSONNEL_MANAGEMENT_CV, on_cv_save)

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_CV relationmgr=yes

@default table=objects
@default group=general


@tableinfo staff_cv master_table=objects master_index=oid index=oid

@property active_until type=date_select group=general table=staff_cv
@caption Aktiivne kuni

/////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
@property comuter_skills_tb type=toolbar no_caption=1 store=no group=arvutioskus
@property previous_jobs_tb type=toolbar no_caption=1 store=no group=tookogemus
@property lang_skills_tb type=toolbar no_caption=1 store=no group=keeleoskused
@property education_tb type=toolbar no_caption=1 store=no group=haridustee


@property educationtabel type=table no_caption=1 store=no group=haridustee

@property keeleoskused type=table store=no group=keeleoskused no_caption=1
@property lang_skill_label type=text group=keeleoskused subtitle=1 value=Lisa&nbsp; store=no

@property computer_skills type=table store=no group=arvutioskus no_caption=1
@property comp_skill_label type=text group=arvutioskus subtitle=1 value=Arvutioskuste&nbsp;lisamine store=no

@property jobs type=table store=no group=tookogemus no_caption=1
@property comp_skill_label type=text group=arvutioskus subtitle=1 value=Lisa&nbsp;arvutioskus store=no

@property haridus_label type=text group=haridustee subtitle=1 value=Kooli&nbsp;lisamine store=no

@property education type=releditor reltype=RELTYPE_EDUCATION props=lisainfo_edu,loppaasta,algusaasta,eriala,kool,education_type group=haridustee store=no

@property language_skills type=releditor reltype=RELTYPE_LANG props=keel,tase  group=keeleoskused store=no
@caption Keeleoskus


@property kogemused type=releditor reltype=RELTYPE_KOGEMUS props=asutus,algus,kuni,ametikoht,tasks group=tookogemus rel_id=57486 store=no
@caption Kogemused

@property arvutioskus type=releditor reltype=RELTYPE_ARVUTIOSKUS props=oskus,tase group=arvutioskus store=no
@caption Arvutioskus

@property juhiload type=classificator method=serialize group=driving_licenses store=connect reltype=RELTYPE_JUHILUBA
@caption Juhiload

//////////////////// TAB Tï¿½SOOV \\\\\\\\\\\\\\\\\\\\\\\\\
@property ametinimetus type=textbox field=meta method=serialize group=toosoov
@caption Ametinimetus

@property palgasoov type=textbox field=meta method=serialize group=toosoov size=5
@caption Palgasoov

@property valdkond type=classificator group=toosoov method=serialize multiple=1 orient=vertical store=connect reltype=RELTYPE_TEGEVUSVALDKOND
@caption Tegevusala

@property liik type=classificator multiple=1 group=toosoov method=serialize store=connect reltype=RELTYPE_LIIK
@caption T&ouml;&ouml; liik

@property asukoht type=relpicker multiple=1 automatic=1 reltype=RELTYPE_LINN group=toosoov field=meta method=serialize orient=vertical
@caption Ttamise piirkond

@property koormus type=classificator group=toosoov field=meta method=serialize multiple=1 orient=vertical
@caption T&ouml;&ouml; koormus

@property job_addinfo type=textarea group=toosoov field=addinfo table=staff_cv
@caption Lisainfo soovitava t&ouml;&ouml; kohta

@property soovitajad type=textarea group=soovitajad field=recommenders table=staff_cv
@caption Soovitajad

property sain_tood type=checkbox group=toosoov field=gotjob table=staff_cv
caption Sain t&ouml;&ouml;d teie kaudu

@property stats_table type=table group=statistika no_caption=1

@property cv_view_tb type=toolbar no_caption=1 store=no wrapchildren=1 group=cv_view
@property cv_view type=text no_caption=1 store=no wrapchildren=1 group=cv_view

//////////////////////////////TABID\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
@groupinfo skills caption="Oskused"
@groupinfo arvutioskus caption="Arvutioskus" parent=skills
@groupinfo keeleoskused caption="Keeleoskus" parent=skills
@groupinfo driving_licenses caption="Juhiload" parent=skills
@groupinfo haridustee caption="Hariduskï¿½k"
@groupinfo soovitajad caption="Soovitajad"


@groupinfo tookogemus caption="Tkogemused"
@groupinfo toosoov caption="Soovitud t"



@groupinfo statistika caption="Statistika" submit=no
@groupinfo cv_view caption="Vaata CV-d" submit=no
////////////////////////////SEOSED\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
@reltype EDUCATION value=1 clid=CL_EDUCATION 
@caption Haridus

@reltype KOGEMUS value=4 clid=CL_PERSONALIHALDUS_TOOKOGEMUS
@caption Kogemus

@reltype LANG value=2 clid=CL_PERSONALIHALDUS_LANG
@caption Keeleoskus

@reltype ARVUTIOSKUS value=3 clid=CL_PERSONALIHALDUS_ARVUTIOSKUS
@caption Arvutioskus

@reltype LINN value=5 clid=CL_CRM_CITY
@caption Linn

@reltype TEGEVUSVALDKOND value=6 clid=CL_META
@caption Tegevusala

@reltype JUHILUBA value=8 clid=CL_META
@caption Juhiluba

@reltype LIIK value=9 clid=CL_META
@caption liik

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
			$personalikeskkond = get_instance("applications/personnel_management/personnel_management");
			$this->my_profile = $personalikeskkond->my_profile;
		}
	}

	function callback_on_load($arr)
	{
		$this->cfgmanager = aw_ini_get("personnel_management.configform_manager");
		
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
	
	//////
	// class_base classes usually need those, uncomment them if you want to use them


	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		
			case "computer_skills":
				$this->do_computer_skills_table(&$arr);
			break;
			case "comuter_skills_tb":
				$this->do_comuter_skills_tb(&$arr);
			break;
			case "arvutioskus":
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
				$prop["rel_id"] = $arr["request"]["eoid"];
			break;

			case "education_tb":
				$this->do_education_tb($arr);
			break;

			case "educationtabel":
				$this->do_educationtabel($arr);
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
					$arr["request"]["type"] = 2;
				}
				
				switch($arr["request"]["type"])
				{
					//Phiharidus
					case 1:
						$prop["props"] = array("kool", "algusaasta", "loppaasta", "lisainfo_edu", "education_type");
					break;
					//Keskharidus
					case 2:
						$prop["props"] = array("kool", "algusaasta", "loppaasta", "lisainfo_edu", "education_type");
					break;
					//Krgharidus
					case 3:
						$prop["props"] = array("kool", "eriala", "algusaasta", "loppaasta", "lisainfo_edu", "education_type");
					break;
					//Tï¿½haridus
					case 4:
						$prop["props"] = array("eriala", "algusaasta", "loppaasta", "teaduskond", "oppekava", "lisainfo_edu");
					break;
					//Tï¿½enduskoolitus
					case 5:
						$prop["props"] = array("kool", "lisainfo_edu");	
					break;
				}
			break;
			
			case "stats_table":
				$this->do_stats_table($arr);
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
		@attrib name=gen_job_pdf
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
			"tooltip" => "Genereeri pdf",
			"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id()))
		));
		/*
		$tb->add_button(array(
			"name" => "print",
			"img" => "print.gif",
			"tooltip" => "Print vaade",
			"action" => "gen_print_view",
		));*/
	}

	function do_educationtabel(&$arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "kool",
			"caption" => "Kool",
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "periood",
			"caption" => "Periood",
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "type",
			"caption" => "Hariduse liik",
			"sortable" => 1
		));

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));
		
		//Tuleks ymber teha
		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_EDUCATION)) as $haridus)
		{
			$connection_id = $haridus->id();

			$haridus = obj($haridus->prop("to"));
			
			$haridusliigid = array("Phiharidus", "Keskharidus", "Krgharidus", "Krgharidus", "Tï¿½enduskoolitus");
			$liik = $haridus->prop("education_type");

			$table->define_data(array(
				"kool" => $haridus->prop("kool"),
				"periood" => $haridus->prop("algusaasta") ."-". $haridus->prop("loppaasta"),
				"from" => $connection_id,
				"type" => $haridusliigid[$haridus->prop("education_type") - 1],
			));

		}

		$table->set_default_sortby("periood");
		$table->sort_by();
	}

	function do_education_tb(&$arr)
	{

		$tb = &$arr["prop"]["toolbar"];

		$tb->add_menu_button(array(
   			"name" => "new",
   			"img" => "new.gif",
   			"tooltip" => "Uus",
		));

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta pingud",
			"action" => "delete_rels",
			"confirm" => "Kas soovid kustutada valitud pingud?"
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => "Muuda keeleoskust",
			"action" => "edit_something",
		));

		$tb->add_menu_item(array(
    			"parent" => "new",
    			"text" => "Phiharidus",
    			"title" => "Phiharidus",
    			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "type" => 1), "personnel_management_cv", true, true),
    			"disabled" => false,
		));

		$tb->add_menu_item(array(
    			"parent" => "new",
    			"text" => "Keskharidus",
    			"title" => "Keskharidus",
    			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "type" => 2), "personnel_management_cv", true, true),
    			"disabled" => false,
		));

		$tb->add_menu_item(array(
    			"parent" => "new",
    			"text" => "Krgharidus",
    			"title" => "Krgharidus",
    			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "type" => 3), "personnel_management_cv", true, true),
    			"disabled" => false,
		));


		$tb->add_menu_item(array(
    			"parent" => "new",
    			"text" => "T&auml;ienduskoolitus",
    			"title" => "T&auml;ienduskoolitus",
    			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "type" => 5), "personnel_management_cv", true, true),
    			"disabled" => false,
		));

		$tb->add_menu_item(array(
    			"parent" => "new",
    			"text" => "T&Uuml; Haridus",
    			"title" => "T&Uuml; Haridus",
    			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "type" => 4), "personnel_management_cv", true, true),
    			"disabled" => false,
		));
	}

	function do_lang_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta keeleoskused",
			"action" => "delete_rels",
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => "Muuda keeleoskust",
			"action" => "edit_something",
		));
	}

	function do_lang_table(&$arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "keel",
			"caption" => "Keel",
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "tase",
			"caption" => "Tase",
			"sortable" => 1
		));

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));

		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_LANG)) as $keeleoskus)
		{
			$connection_id=$keeleoskus->id();
			$keeleoskus = obj($keeleoskus->prop("to"));
			$keel = obj($keeleoskus->prop("keel"));
			$tase = obj($keeleoskus->prop("tase"));

			$table->define_data(array(
				"keel" => $keel->name(),
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
			"tooltip" => "Kustuta tkogemused",
			"action" => "delete_rels",
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => "Muuda tkogemust",
			"action" => "edit_something",
		));
	}

	function do_jobs_table($arr)
	{
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "asutus",
			"caption" => "Asutus",
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => "Ametikoht",
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "alates",
			"caption" => "Alates",
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "kuni",
			"caption" => "Kuni",
			"sortable" => 1
		));

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));


		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_KOGEMUS)) as $kogemus)
		{
			$connection_id=$kogemus->id();
			$kogemus = obj($kogemus->prop("to"));
			$table->define_data(array(
				"asutus" => $kogemus->prop("asutus")/*html::href(array(
						"caption" => $kogemus->prop("asutus"),
						"url" => $this->mk_my_orb("change", array("id" => $kogemus->id()), "personalihaldus_tookogemus"),
					))*/,
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
			"tooltip" => "Kustuta arvutioskused",
			"action" => "delete_rels",
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => "Muuda oskust",
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
			"caption" => "Oskus",
		));

		$table->define_field(array(
			"name" => "tase",
			"caption" => "Tase",
		));

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));

		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_ARVUTIOSKUS)) as $c_skill)
		{
			$connection_id=$c_skill->id();
			$c_skill = obj($c_skill->prop("to"));

			$oskus = obj($c_skill->prop("oskus"));
			$tase = obj($c_skill->prop("tase"));

			$table->define_data(array(
				"oskus" => $oskus->name(),
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
		$now = time();
		$ol = new object_list(array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_CV,
			"status" => STAT_ACTIVE,
		));

		foreach($ol->arr() as $cv)
		{
			if($cv->prop("active_until")< time())
			{
				$cv->set_status(STAT_NOTACTIVE);
				$cv->save();
			}
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


	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["id"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
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
		
		foreach ($ob->connections_from(array("type" => RELTYPE_KOGEMUS)) as $kogemus)
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
		foreach ($ob->connections_from(array("type" => RELTYPE_TEGEVUSVALDKOND)) as $sector)
		{
			$this->vars(array(
				"sector" => $sector->prop("to.name"),
			));
			$tmp_sectors.=$this->parse("sectors");
		}
		
		
		//Hariduste nimekiri
		foreach ($ob->connections_from(array("type" => RELTYPE_EDUCATION)) as $haridus)
		{
			$haridus = $haridus->to();
			$haridus->prop("algusaasta");
			$period = $haridus->prop("algusaasta")." - ". $haridus->prop("loppaasta");
			
			
			$eriala = array_pop($haridus->connections_from(array("type" => RELTYPE_ERIALA)));
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
		
		foreach ($ob->connections_from(array("type" => RELTYPE_JUHILUBA)) as $driving_license)
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
				"skill_name" => $c->prop("to.name")
			));
			$dsk[] = $this->parse("DRIVE_SKILL");
		}

		$ed = "";
		foreach($ob->connections_from(array("type" => "RELTYPE_EDUCATION")) as $c)
		{
			$to = $c->to();
			$this->vars(array(
				"from" => $to->prop("algusaasta"),
				"to" => $to->prop("loppaasta"),
				"where" => $to->prop("kool"),
				"extra" => nl2br($to->prop("lisainfo_edu"))
			));
			$ed .= $this->parse("ED");
		}

		$this->vars(array(
			"COMP_SKILL" => $ck,
			"LANG_SKILL" => $lsk,
			"DRIVE_SKILL" => join(",", $dsk),
			"ED" => $ed,
			"recommenders" => nl2br($ob->prop("soovitajad")),
			"name" => $person_obj->name(),
			"modified" => get_lc_date($ob->modified()),
			"birtday" => $person_obj->prop("birthday"),
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
			"tooltip" => "Genereeri pdf",
			"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id()))
		));
		/*
		$tb->add_button(array(
			"name" => "print",
			"img" => "print.gif",
			"tooltip" => "Print vaade",
			"action" => "gen_print_view",
		));*/
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
			"caption" => "Kes vaatas:",
			"sortable" => 1
		));
			
		$table->define_field(array(
			"name" => "count",
			"caption" => "Vaatamisi",
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

					$company = $person->connections_from(array("type" => RELTYPE_WORK));
					if($company)
					{
						$company = array_pop($company);

						$table->define_data(array(
							"who" => html::href(array(
										"url" => $this->mk_my_orb("change", array("id" => $company->prop("to")), "crm_company"),
										"caption" => $company->prop("to.name")
									)),
							"count" => $row["vaatamisi"],
						));
					}
			}
		}

	}
}
?>
