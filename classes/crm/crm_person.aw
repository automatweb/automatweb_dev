<?php                  

// $Header: /home/cvs/automatweb_dev/classes/crm/crm_person.aw,v 1.74 2005/03/14 17:27:30 kristo Exp $
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_COMPANY, on_connect_org_to_person)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_SECTION, on_connect_section_to_person)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_COMPANY, on_disconnect_org_from_person)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_SECTION, on_disconnect_section_from_person)

@classinfo relationmgr=yes syslog_type=ST_CRM_PERSON
@tableinfo kliendibaas_isik index=oid master_table=objects master_index=oid

@default table=objects
@default group=general2

@property navtoolbar type=toolbar store=no no_caption=1 group=general,overview editonly=1

@property friend_groups type=classificator reltype=RELTYPE_FRIEND_GROUPS store=no group=general
@caption Sõbragrupid

@property name type=text
@caption Nimi

@default table=kliendibaas_isik

@property firstname type=textbox size=15 maxlength=50
@caption Eesnimi

@property lastname type=textbox size=15 maxlength=50
@caption Perekonnanimi

@property personal_id type=textbox size=13 maxlength=11
@caption Isikukood

@property ext_id type=textbox table=objects field=subclass
@caption Sidussüsteemi ID

@property gender type=chooser 
@caption Sugu

@property title type=chooser
@caption Tiitel

@property nickname type=textbox size=10 maxlength=20
@caption Hüüdnimi

property messenger type=textbox size=30 maxlength=200
caption Msn/yahoo/aol/icq

@property birthday type=date_select year_from=1930 year_to=2010 default=-1
@caption Sünnipäev

@property education type=relpicker reltype=RELTYPE_EDUCATION automatic=1
@caption Haridus



@property social_status type=chooser 
@caption Perekonnaseis

@property spouse type=textbox size=25 maxlength=50 group=relatives
@caption Abikaasa

@property children type=relpicker reltype=RELTYPE_CHILDREN group=relatives
@caption Lapsed

@property pictureurl type=textbox size=40 maxlength=200
@caption Pildi/foto url

@property picture type=releditor reltype=RELTYPE_PICTURE rel_id=first props=file 
@caption Pilt/foto

@property username type=text store=no
@caption Kasutaja

@property password type=password table=objects field=meta method=serialize
@caption Parool

//@property profession type=select store=no edit_only=1
//@caption Ametinimetus

@property notes type=textarea cols=60 rows=10 group=description
@caption Vabas vormis tekst

@property aliasmgr type=aliasmgr group=description no_caption=1 store=no
@caption Seostehaldur

@property work_contact type=relpicker reltype=RELTYPE_WORK table=kliendibaas_isik group=contact
@caption Organisatsioon

@property org_section type=relpicker reltype=RELTYPE_SECTION multiple=1 table=objects field=meta method=serialize group=contact
@caption Osakond

@property rank type=relpicker reltype=RELTYPE_RANK table=kliendibaas_isik automatic=1 group=contact
@caption Ametinimetus

property personal_contact type=relpicker reltype=RELTYPE_ADDRESS table=kliendibaas_isik
caption Kodused kontaktandmed

@default group=contact

@property address type=relpicker reltype=RELTYPE_ADDRESS
@caption Aadress
	
@property email type=relmanager table=objects field=meta method=serialize group=contact reltype=RELTYPE_EMAIL props=mail
@caption Meiliaadressid

@property phone type=relmanager table=objects field=meta method=serialize group=contact reltype=RELTYPE_PHONE props=name
@caption Telefoninumbrid

@property url type=relmanager table=objects field=meta method=serialize group=contact reltype=RELTYPE_URL props=url
@caption Veebiaadressid

@property comment type=textarea cols=40 rows=3 table=objects field=comment group=contact
@caption Kontakt


//property email type=textbox store=no 
//caption E-post
@property default_cv type=hidden table=objects field=meta method=serialize

@default group=overview

@property org_actions type=calendar no_caption=1 group=all_actions viewtype=relative
@caption org_actions

@property org_calls type=calendar no_caption=1 group=calls viewtype=relative
@caption Kõned

@property org_meetings type=calendar no_caption=1 group=meetings viewtype=relative
@caption Kohtumised

@property org_tasks type=calendar no_caption=1 group=tasks viewtype=relative
@caption Toimetused

---------------CV PROPERTID-------------------

---- OSKUSED

layout toolbar_hbox_toolbar type=hbox group=skills
layout skills_main type=hbox group=skills width=20%:80%
layout skills_hbox_tree type=vbox  group=skills parent=skills_main
layout skills_hbox_table type=vbox group=skills parent=skills_main

property skills_toolbar type=toolbar store=no no_caption=1 wrapchildren=1 group=skills parent=toolbar_hbox_toolbar
property skills_listing_tree type=treeview no_caption=1 store=no parent=skills_hbox_tree group=skills
property skills_table type=table store=no group=skills no_caption=1 parent=skills_hbox_table

property language_list type=classificator group=skills parent=skills_hbox_table store=no
caption Keelesoskus

property language_levels type=classificator group=skills parent=skills_hbox_table store=no
caption Keelesoskus

property language_skills_table type=table store=no parent=skills_hbox_table no_caption=1 group=skills

property juhiload type=classificator field=meta table=objects method=serialize group=skills store=connect parent=skills_hbox_table reltype=RELTYPE_DRIVING_LICENSE
caption Juhiload

property submit_driving_licenses type=submit store=no parent=skills_hbox_table group=skills
caption Salvesta

property programming_skills type=releditor rel_id=first reltype=RELTYPE_PROGRAMMING_SKILLS group=skills props=php,c,charp,cplus,java,python,vb,perl,pascal,delphi,foxpro parent=skills_hbox_table

----Muu

@property education_main type=table no_caption=1 store=no group=education
@property job_experiences type=table store=no group=experiences no_caption=1


----Töökogemused
@property previous_jobs_tb type=toolbar no_caption=1 store=no group=experiences
@property previous_jobs_table type=table store=no no_caption=1 group=experiences

@property tookogemused_edit type=releditor reltype=RELTYPE_PREVIOUS_JOB props=organisation,profession,date_from,date_to,duties group=experiences store=no

----Haridus
@property education_tb type=toolbar no_caption=1 store=no group=education
@property education_table type=table store=no no_caption=1 group=education

@property add_edu_table type=table no_caption=1 store=no group=add_edu
@property add_edu_editor type=releditor store=no group=add_edu mode=manager reltype=RELTYPE_EDUCATION props=school,date_from,date_to,additonal_info,subject table_fields=school,subject,date_from,date_to

property cv_view_tb type=toolbar no_caption=1 store=no wrapchildren=1 group=cv_view
property cv_view type=text store=no wrapchildren=1 group=cv_view no_caption=1



----------------------------------------------
@groupinfo general2 caption="Üldine" parent=general
@groupinfo description caption="Kirjeldus" parent=general
@groupinfo relatives caption="Sugulased" parent=general
@groupinfo contact caption="Kontaktandmed"
@groupinfo overview caption=Tegevused
@groupinfo all_actions caption="Kõik" parent=overview submit=no
@groupinfo calls caption="Kõned" parent=overview submit=no
@groupinfo meetings caption="Kohtumised" parent=overview submit=no
@groupinfo tasks caption="Toimetused" parent=overview submit=no
@groupinfo forms caption=Väljundid
@groupinfo cv caption="Elulugu"

@groupinfo education caption="Hariduskäik" parent=cv
@groupinfo add_edu caption="Täienduskoolitus" parent=cv
groupinfo skills caption="Oskused" parent=cv submit=no
@groupinfo experiences caption="Töökogemused" parent=cv
@groupinfo recommends caption="Soovitajad" parent=cv
groupinfo cv_view caption="CV vaade" parent=cv

default group=forms
default field=meta
default table=objects
default method=serialize

property forms type=relpicker reltype=RELTYPE_BACKFORMS
caption tagasiside vormid
selection.aw

@property templates type=select group=forms table=objects field=meta method=serialize
@caption Väljund

default group=show
groupinfo show caption=Visiitkaart submit=no
@groupinfo contact caption=Kontaktandmed
property dokus type=text callback=show_isik

// 1 - make it display my documents - how on earth am I going to do that?

@classinfo no_status=1


*/

/*

CREATE TABLE `kliendibaas_isik` (
  `oid` int(11) NOT NULL default '0',
  `firstname` varchar(50) default NULL,
  `lastname` varchar(50) default NULL,
  `name` varchar(100) default NULL,
  `gender` varchar(10) default NULL,
  `personal_id` bigint(20) default NULL,
  `title` varchar(10) default NULL,
  `nickname` varchar(20) default NULL,
  `messenger` varchar(200) default NULL,
  `birthday` varchar(20) default NULL,
  `social_status` varchar(20) default NULL,
  `spouse` varchar(50) default NULL,
  `children` varchar(100) default NULL,
  `personal_contact` int(11) default NULL,
  `work_contact` int(11) default NULL,
  `rank` int(11) default NULL,  
  `digitalID` text,
  `notes` text,
  `pictureurl` varchar(200) default NULL,
  `picture` blob,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

/*
@reltype ADDRESS value=1 clid=CL_CRM_ADDRESS
@caption aadressid

@reltype PICTURE value=3 clid=CL_IMAGE
@caption pilt

@reltype BACKFORMS value=4 clid=CL_PILOT
@caption tagasiside vorm

@reltype CHILDREN value=5 clid=CL_CRM_PERSON
@caption lapsed

@reltype WORK value=6 clid=CL_CRM_COMPANY
@caption töökoht

@reltype RANK value=7 clid=CL_CRM_PROFESSION
@caption ametinimetus

@reltype PERSON_MEETING value=8 clid=CL_CRM_MEETING
@caption kohtumine

@reltype PERSON_CALL value=9 clid=CL_CRM_CALL
@caption kõne

@reltype PERSON_TASK value=10 clid=CL_TASK
@caption toimetus

@reltype EMAIL value=11 clid=CL_ML_MEMBER
@caption E-post

@reltype URL value=12 clid=CL_EXTLINK
@caption Veebiaadress

@reltype PHONE value=13 clid=CL_CRM_PHONE
@caption Telefon

@reltype PROFILE value=14 clid=CL_PROFILE
@caption Profiil

reltype USER_DATA value=15 
caption Andmed

reltype CV value=19 clid=CL_CV
caption CV

@reltype ORDER value=20 clid=CL_SHOP_ORDER
@caption tellimus

@reltype SECTION value=21 clid=CL_CRM_SECTION
@caption Üksus

//parem nimi teretulnud, person on cl_crm_company jaox
//kliendihaldur
@reltype HANDLER value=22 clid=CL_CRM_COMPANY

@reltype EDUCATION value=23 clid=CL_CRM_PERSON_EDUCATION 
@caption Haridus

@reltype DRIVING_LICENSE value=24 clid=CL_CRM_DRIVING_LICENSE
@caption Juhiluba

@reltype PREVIOUS_JOB value=26 clid=CL_CRM_PERSON_PREVIOUS_JOB
@caption Töökogemus

@reltype LANGUAGE_SKILL value=27 clid=CL_CRM_PERSON_LANGUAGE
@caption Keeleoskus

@reltype PROGRAMMING_SKILLS value=33 clid=CL_CRM_PERSON_PROGRAMMING_SKILLS
@caption Programmeerimisoskus

@reltype DESCRIPTION_DOC value=34 clid=CL_DOCUMENT,CL_MENU
@caption kirjelduse dokument

@reltype FRIEND value=35 clid=CL_CRM_PERSON
@caption Sõber

@reltype FAVOURITE value=36 clid=CL_CRM_PERSON
@caption Lemmik

@reltype MATCH value=37 clid=CL_CRM_PERSON
@caption Väljavalitu

@reltype BLOCKED value=38 clid=CL_CRM_PERSON
@caption blokeeritud

@reltype IGNORED value=39 clid=CL_CRM_PERSON
@caption ignoreeritud

@reltype FRIEND_GROUPS value=40 clid=CL_META
@caption Sõbragrupid

@reltype VACATION value=41 clid=CL_CRM_VACATION
@caption Puhkus

@reltype CONTRACT_STOP value=42 clid=CL_CRM_CONTRACT_STOP
@caption Töölepingu peatamine

*/

class crm_person extends class_base
{
	function crm_person()
	{
		$this->init(array(
			"tpldir" => "crm/person",
			"clid" => CL_CRM_PERSON,
		));
	}

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$form = &$arr["request"];

		switch($prop["name"])
		{
			case "lastname":
				if (!empty($form["firstname"]) || !empty($form["lastname"]))
				{
					$arr["obj_inst"]->set_name($form["firstname"]." ".$form["lastname"]);
				}			
				break;

		};
		return $retval;
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
	
		switch($data["name"])
		{
			case "pictureurl":
				// this one is generated by the picture releditor and should not be edited
				// manually
				$retval = PROP_IGNORE;
				break;

			case "ext_id":
				$retval = PROP_IGNORE;
				break;

			case 'work_contact':
				//i'm gonna to this manually i guess
				//cos a person can be connected to a company
				//through sections, relpicker obviously doesn't cover that
				//maybe i made design flaw and should have done what i did
				//a bit differently?
				$company = $this->get_work_contacts($arr);
				$data['options'] = $company;
				$data['options'][0] = t('--vali--');
				$data['options'] = array_reverse($data['options'], true);
				break;
			case 'rank' :
				//let's list the professions the organization/unit is associated with
				$drop_down_list = array();
				//if the person is associated with a section then show the professions
				//from the section and if not then show all the professions in the system
				$conns = $arr['obj_inst']->connections_to(array(
					'type'=> RELTYPE_SECTION
				));

				$drop_down_list = array();

				if(sizeof($conns))
				{
					foreach($conns as $conn)
					{
						//organization || section
						$tmp_obj = new object($conn->prop('from'));
						//connections from organization||section->profession
						$conns2 = $tmp_obj->connections_from(array(
							'type'=>'RELTYPE_PROFESSIONS'
						));
						foreach($conns2 as $conn2)
						{
							$drop_down_list[$conn2->prop('to')] = $conn2->prop('to.name');
						}
					}
				}
				else
				{
					$ol = new object_list(array(
						'class_id' => CL_CRM_PROFESSION
					));

					foreach($ol->arr() as $o)
					{
						$drop_down_list[$o->id()] = $o->prop('name');
					}
				}

				asort($drop_down_list);
				$drop_down_list = array_reverse($drop_down_list,true);
				$drop_down_list[0] = t('--vali--');
				$drop_down_list = array_reverse($drop_down_list,true);
				$data['options'] = &$drop_down_list;
				break;
			case "title":
				$data["options"] = array(
					t("Härra"),
					t("Proua"),
					t("Preili")
				);
				break;

			case "social_status":
				$data["options"] = array(
					3 => t("Vallaline"), 
					1 => t("Abielus"), 	
					2 => t("Vabaabielus")
				);
				break;

			case "templates":
				$data["options"] = array(
					"1" => t("Pilt, kontakt, artiklid"),
					"2" => t("kontakt"),
				);
				break;

			case "forms":
				$data["multiple"] = 1;
				break;

			case "navtoolbar":
				$this->isik_toolbar(&$arr);
				break;

			case "gender":
				$data["options"] = array(
					"1" => t("mees"),
					"2" => t("naine"),
				);
				break;

			case "email":
				break;
				

			case "org_actions":
			case "org_calls":
			case "org_meetings":
			case "org_tasks":
				$this->do_org_actions(&$arr);
				break;
                                
			case "skills_listing_tree":
				$this->do_person_skills_tree($arr);
			break;
			
			case "picture":
				break;
				
			case "skills_toolbar":
				$this->do_cv_skills_toolbar(&$data["toolbar"], $arr);
				break;
				
			case "skills_table":
				break;
				
			case "juhiload":
				if(!($arr["request"]["skill"]=="driving_licenses"))
				{
					return PROP_IGNORE;
				}
				break;
			
			case "submit_driving_licenses":
				if(!($arr["request"]["skill"]=="driving_licenses"))
				{
					return PROP_IGNORE;
				}
				break;
			
			case "language_list":	
				return PROP_IGNORE;
				break;
			
			case "language_skills_table":
				if($arr["request"]["skill"] =="languages")
				{
					$this->do_language_skills_table($arr);
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			
			case "tookogemused_edit":
			
				if($arr["request"]["eoid"])
				{
					$data["rel_id"] = $arr["request"]["eoid"]; 		
				}
				elseif (!$arr["request"]["new_prevjob"])
				{
					return PROP_IGNORE;
				}
				
				break;
			
			case "language_levels":
				return PROP_IGNORE;
				break;
			
			case "previous_jobs_table":
				$this->do_jobs_table($arr);
				break;
			
			case "previous_jobs_tb":
				$this->do_previous_jobs_tb($arr);
				break;
			
			case "education_tb":
				$this->do_education_tb($arr);
				break;
			
			case "basic_education_edit":
				if($arr["request"]["etype"]=="basic_edu")
				{
					$data["rel_id"] = $arr["request"]["eoid"];	
				}
				else
				{
					return PROP_IGNORE;
				}	
			
				break;
			
			case "vocational_education_edit":
				if($arr["request"]["etype"]=="voc_edu")
				{
					$data["rel_id"] = $arr["request"]["eoid"];	
				}
				else
				{
					return PROP_IGNORE;
				}	
				break;
			
			case "higher_education_edit":
				if($arr["request"]["etype"]=="higher_edu")
				{
					$data["rel_id"] = $arr["request"]["eoid"];	
				}
				else
				{
					return PROP_IGNORE;
				}	
				break;
			
			case "secondary_education_edit":
				if($arr["request"]["etype"]=="secondary_edu")
				{
					$data["rel_id"] = $arr["request"]["eoid"];	
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
				
			case "education_table":
				$this->do_education_table($arr);
				break;
			
			case "programming_skills":
				
				if(!($arr["request"]["skill"] == "programming"))
				{
					return PROP_IGNORE;
				}
				break;

			case "password":
				if ($this->has_user($arr["obj_inst"]))
				{
					return PROP_IGNORE;
				}
				break;

			case "username":
				if ($arr["new"] || !($tmp = $this->has_user($arr["obj_inst"])))
				{
					return PROP_IGNORE;
				}
				$data["value"] = html::get_change_url(
					$tmp->id(),
					array(),
					$tmp->name()
				);
				break;
		}
		return $retval;

	}

	function isik_toolbar($args)
	{
		$toolbar = &$args["prop"]["toolbar"];

		$users = get_instance("users");
		$crm_db_id = $users->get_user_config(array(
			"uid" => aw_global_get("uid"),
			"key" => "kliendibaas",
		));

		$cal_id = $users->get_user_config(array(
			"uid" => aw_global_get("uid"),
			"key" => "user_calendar",
		));

		$parents = array();

		if (empty($crm_db_id))
		{
			$parents[RELTYPE_WORK] = $args["obj_inst"]->parent();
		}
		else
		{
			$crm_db = new object($crm_db_id);
			$parents[RELTYPE_WORK] = $crm_db->prop("dir_firma") == "" ? $crm_db->prop("dir_default") : $crm_db->prop("dir_firma");
		}

		if (!empty($cal_id))
		{
			$user_calendar = new object($cal_id);
			$parents[RELTYPE_PERSON_CALL] = $parents[RELTYPE_PERSON_MEETING] = $user_calendar->prop('event_folder');
		}

		/*

		$alist = array(
			array('caption' => t('Organisatsioon'),'class' => 'crm_company', 'reltype' => RELTYPE_WORK),
		);
		
		$toolbar->add_menu_button(array(
			"name" => "add_relation",
			"tooltip" => t("Uus"),
		));
			

		$menudata = '';
		if (is_array($alist))
		{
			foreach($alist as $key => $val)
			{
				if (!$parents[$val['reltype']])
				{
					$toolbar->add_menu_item(array(
						"parent" => "add_relation",
						"title" => t("Kalender määramata"),
						'text' => sprintf(t('Lisa %s'),$val['caption']),
						'disabled' => true,
					));
				}
				else
				{
					// see on nyyd sihuke link, mis lisab uue objekti
					// ja seostab selle olemasolevaga. grr.
					$toolbar->add_menu_item(array(
						"parent" => "add_relation",
						'link' => $this->mk_my_orb('new',array(
							'alias_to' => $args['obj_inst']->id(),
							'reltype' => $val['reltype'],
							'class' => $val['class'],
							'parent' => $parents[$val['reltype']],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						)),
						'text' => sprintf(t('Lisa %s'),$val['caption']),
					));
				}
			};
		
		};
		*/
		
		

		$action = array(
			array(
				"reltype" => RELTYPE_PERSON_MEETING,
				"clid" => CL_CRM_MEETING,
			),
			array(
				"reltype" => RELTYPE_PERSON_CALL,
				"clid" => CL_CRM_CALL,
			),
		);

		$toolbar->add_menu_button(array(
			"name" => "add_event",
			"tooltip" => t("Uus"),
		));

		$req = urlencode(aw_global_get("REQUEST_URI"));

		$menudata = '';
		$clss = aw_ini_get("classes");
		$oid = $args["obj_inst"]->id();
		if (is_array($action))
		{
			foreach($action as $key => $val)
			{
				if (!$parents[$val['reltype']])
				{
					$toolbar->add_menu_item(array(
						"parent" => "add_event",
						'title' => t('Kalender määramata'),
						'text' => sprintf(t('Lisa %s'),$clss[$val["clid"]]["name"]),
						'disabled' => true,
					));
				}
				else
				{
					$toolbar->add_menu_item(array(
						"parent" => "add_event",
						'url' => $this->mk_my_orb('new',array(
							'alias_to_org' => $oid,
							'reltype_org' => $val['reltype'],
							'class' => 'planner',
							'id' => $cal_id,
							'clid' => $val["clid"],
							'group' => 'add_event',
							'action' => 'change',
							'title' => $clss[$val["clid"]]["name"].': '.$args['obj_inst']->name(),
							'parent' => $parents[$val['reltype']],///?
							'return_url' => $req,
						)),
						'text' => sprintf(t('Lisa '),$clss[$val["clid"]]["name"]),
					));
				}
			};

			if (!empty($cal_id))
			{
				$toolbar->add_button(array(
					"name" => "user_calendar",
					"tooltip" => t("Kasutaja kalender"),
					"url" => $this->mk_my_orb('change', array('id' => $cal_id,'return_url' => $req,),'planner'),
					"onClick" => "",
					"img" => "icon_cal_today.gif",
				));
			}

		};

	}

	
	function show_isik($args)
	{
		$arg2["id"] = $args["obj_inst"]->id();
		$nodes = array();
		$nodes['visitka'] = array(
			"value" => $this->show($arg2),
		);
		return $nodes;
	}

	function fetch_person_by_id($arr)
	{
		// how do I figure out the _last_ action done with a person?

		// I need today's date..
		// I need a list of all events that have a calendar presentation
		// and then I just fetch the latest thingie

		// easy as pie
		
		$o = new object($arr["id"]);
		$cal_id = $arr["cal_id"];

		$phones = $emails = $urls = $ranks = $ranks_arr = $sections_arr = array();

		$tasks = $o->connections_from(array(
			"type" => array(9,10),
		));

		$to_ids = array();
		foreach($tasks as $task)
		{
			$to_ids[] = $task->prop("to");
		};

		$conns = $o->connections_from(array(
			"type" => 13,
		));
		foreach($conns as $conn)
		{
			$phones[] = $conn->prop("to.name");
		};

		$conns = $o->connections_from(array(
			"type" => 12,
		));
		foreach($conns as $conn)
		{
			$url_o = $conn->to();
			$urls[] = html::href(array(
				"url" => $url_o->prop("url"),
				"caption" => $url_o->prop("url"),
			));
		};
		
		$conns = $o->connections_from(array(
			"type" => 11,
		));
		foreach($conns as $conn)
		{
			$to_obj = $conn->to();
			$emails[] = $to_obj->prop("mail");
		};

		$conns = $o->connections_from(array(
			"type" => 'RELTYPE_RANK',
		));

		foreach($conns as $conn)
		{
			$ranks[] = $conn->prop("to.name");
			$ranks_arr[$conn->prop('to')] = $conn->prop('to.name');
		};
	
		$conns = $o->connections_from(array(
			'type' => "RELTYPE_SECTION"
		));
		foreach($conns as $conn)
		{	
			$sections_arr[$conn->prop('to')] = $conn->prop('to.name');
		}


		$address = "";
		$address_d = $o->get_first_obj_by_reltype("RELTYPE_ADDRESS");
		if ($address_d)	
		{
			$address_a = array();
			if ($address_d->prop("aadress") != "")
			{
				$address_a[] = $address_d->prop("aadress");
			}

			if ($address_d->prop("linn"))
			{
				$tmp = obj($address_d->prop("linn"));
				$address_a[] = $tmp->name();
			}

			if ($address_d->prop("riik"))
			{
				$tmp = obj($address_d->prop("riik"));
				$address_a[] = $tmp->name();
			}
		
			$address = join(",", $address_a);
		}

		$oid = $o->id();

		$rv = array(
			'name' => $o->prop('firstname').' '.$o->prop('lastname'),
			'firstname' => $o->prop('firstname'),
			'lastname' => $o->prop('lastname'),
			"phone" => join(", ",$phones),
			"url" => join(", ",$urls),
			"email" => join(", ",$emails),
			"rank" => join(", ",$ranks),
			'section' => join(',',$sections_arr),
			'ranks_arr' => $ranks_arr,
			'sections_arr' => $sections_arr,
			'address' => $address,
			"add_task_url" => $this->mk_my_orb("change",array(
				"id" => $cal_id,
				"group" => "add_event",
				"alias_to_org" => $oid,
				"reltype_org" => 10,
				"clid" => CL_TASK,
			),CL_PLANNER),
			"add_call_url" => $this->mk_my_orb("change",array(
				"id" => $cal_id,
				"group" => "add_event",
				"alias_to_org" => $oid,
				"reltype_org" => 9,
				"clid" => CL_CRM_CALL,
			),CL_PLANNER),
			"add_meeting_url" => $this->mk_my_orb("change",array(
				"id" => $cal_id,
				"group" => "add_event",
				"alias_to_org" => $oid,
				"reltype_org" => 8,
				"clid" => CL_CRM_MEETING,
			),CL_PLANNER),
			
		);
		return $rv;
	}

	function upd_contact_data($arr)
	{
		// I need to figure out whether this person has a personal contact set?
		$personal_contact = $arr["obj_inst"]->prop("personal_contact");
		if (is_oid($personal_contact))
		{
			// load the contact object
			$pc = new object($personal_contact);
		}
		else
		{
			$pc = new object();
			$pc->set_class_id(CL_CRM_ADDRESS);
			$pc->set_name($arr["obj_inst"]->name());
			$pc->set_parent($arr["obj_inst"]->parent());
			$pc->save();

			$arr["obj_inst"]->connect(array(
				"to" => $pc->id(),
				"reltype"=> RELTYPE_ADDRESS,
			));

			$arr["obj_inst"]->set_prop("personal_contact",$pc->id());
		};


		$addr_inst = get_instance(CL_CRM_ADDRESS);
		$addr_inst->set_email_addr(array(
			"obj_id" => $pc->id(),
			"email" => $arr["prop"]["value"],
		));
	}


	/** shows a person

		@attrib name=show

		@param id required

	**/
	function show($arr)
	{
		$arx = array();
		$obj = new object($arr["id"]);
		$arx["alias"]["target"] = $obj->id();
		return $this->parse_alias($arx);
	}

	function show2($args)
	{
		extract($args);

		$obj = new object($id);
		$tpls = $obj->prop("templates");

		if (strlen($tpls) > 4)
		{
			$this->read_template('visit/'.$tpls);
		}
		else
		{
			$this->read_template('visit/visiit1.tpl');
		}

		$row = $this->fetch_all_data($id);

		$forms = $obj->prop("forms");
		if (is_array($this->default_forms))
		{
			$forms = array_merge($this->default_forms, $forms);
		}

		$fb = "";


		if (is_array($forms))
		{
			$forms = array_unique($forms);
			foreach($forms as $val)
			{
				if (!$val)
				{
					continue;
				}

				$form = new object($val);
				$fb.= html::href(array(
					'target' => $form->prop('open_in_window')? '_blank' : NULL,
					'caption' => $form->name(), 'url' => $this->mk_my_orb('form', array(
						'id' => $form->id(),
						'feedback' => $id,
						'feedback_cl' => rawurlencode('crm/crm_person'),
						),
				'pilot_object'))).'<br />';
			}
		}

		
		if (($row['lastname'] == '') &&($row['firstname'] == ''))
		{
			$row['firstname'] = $row['name'];
		}

		if ($row['picture'])
		{
			$img = get_instance('image');

			$im = $img->get_image_by_id($row['picture']);
//			$row['PILT'] = $img->view(array('id' => $row['picture'], 'height' => '65'));

			$row['picture_url'] = $im['url'];

			$this->vars($row);


			$row['PILT'] = $this->parse('PILT');
		}
		else
		{
			$row['picture'] = '';
		}

//		$row['picture']=$row['picture']?html::img(array('src' => $row['picture'])):'';
		//$row['picture'].=$row['pictureurl']?html::img(array('url' => $row['pictureurl'])):'';


		$row['comment'] = $obj['comment'];
		$row['k_e_mail']=(!empty($row['k_e_mail']))?html::href(array('url' => 'mailto:'.$row['k_e_mail'], 'caption' => $row['k_e_mail'])):'';
		$row['w_e_mail']=(!empty($row['w_e_mail']))?html::href(array('url' => 'mailto:'.$row['w_e_mail'],'caption' => $row['w_e_mail'])):'';
		$row['k_kodulehekylg']=$row['k_kodulehekylg']?html::href(array('url' => $row['k_kodulehekylg'],'caption' => $row['k_kodulehekylg'],'target' => '_blank')):'';
		$row['w_kodulehekylg']=$row['w_kodulehekylg']?html::href(array('url' => $row['w_kodulehekylg'],'caption' => $row['w_kodulehekylg'],'target' => '_blank')):'';
		$row['tagasisidevormid'] = $fb;

		$this->vars($row);

		return $this->parse();
	}

	function fetch_all_data($id)
	{
//vot siuke päring, ära küsi
		return  $this->db_fetch_row("select
			t1.oid as oid,
			t2.name as name,
			firstname,
			lastname,
			gender,
			personal_id,
			title,
			nickname,
			messenger,
			birthday,
			social_status,
			spouse,
			children,
			personal_contact,
			work_contact,
			digitalID,
			notes,
			pictureurl,
			picture,
			t11.name as k_riik,
			t6.name as k_maakond,
			t7.name as k_linn,
			t8.name as w_maakond,
			t9.name as w_linn,
			t10.name as w_riik,
			t4.name as fnimi,

			t3.postiindeks as k_postiindex,
			t3.aadress as k_aadress,
			t3.telefon as k_telefon,
			t3.mobiil as k_mobiil,
			t3.faks as k_faks,
			t3.e_mail as k_e_mail,
			t3.kodulehekylg as k_kodulehekylg,

			t4.postiindeks as w_postiindex,
			t4.aadress as w_aadress,
			t4.telefon as w_telefon,
			t4.mobiil as w_mobiil,
			t4.faks as w_faks,
			t4.e_mail as w_e_mail,
			t4.kodulehekylg as w_kodulehekylg

			from objects as t1

			left join kliendibaas_isik as t2 on t1.oid=t2.oid
			left join kliendibaas_address as t3 on t2.personal_contact=t3.oid
			left join kliendibaas_address as t4 on t2.work_contact=t4.oid

			left join kliendibaas_maakond as t6 on t6.oid=t3.maakond
			left join kliendibaas_linn as t7 on t7.oid=t3.linn
			left join kliendibaas_riik as t11 on t11.oid=t3.riik
			left join kliendibaas_maakond as t8 on t8.oid=t4.maakond
			left join kliendibaas_linn as t9 on t9.oid=t4.linn
			left join kliendibaas_riik as t10 on t10.oid=t4.riik

			where t1.oid=".$id);

	//left join images as t5 on t2.picture=t5.id
//			t5.link as picture,
	}

	////
	// !callback, used by selection
	// id - object to show
	function show_in_selection($args)
	{
		return $this->show(array('id' => $args['id']));
	}

	function request_execute($obj)
	{
		$arx = array();
		$arx["alias"]["target"] = $obj->id();
		return $this->parse_alias($arx);
	}

	function parse_alias($arr)
	{
		// okey, I need to determine whether that template has a place for showing
		// a list of authors documents. If it does, then I need to create that list
		extract($arr);
		$to = new object($arr["alias"]["target"]);
		switch($to->prop("templates"))
		{
			case 1:
				$template = "pic_documents.tpl";
				break;
	
			default:
				$template = "pic_documents.tpl";
				break;
		};
		$this->read_template($template);
		$pdat = $this->fetch_person_by_id(array(
			"id" => $to->id(),
		));

		$al = get_instance("aliasmgr");
		$notes = $to->prop("notes");

		$al->parse_oo_aliases($to->id(),&$notes);

		$this->vars(array(
			"name" => $to->name(),
			"phone" => $pdat["phone"],
			"email" => $pdat["email"],
			"notes" => nl2br($notes),
		));
		// show image if there is a placeholder for it in the current template
		if ($this->template_has_var("imgurl"))
		{
			$conns = $to->connections_from(array(
				"type" => RELTYPE_PICTURE,
			));
			$imgurl = "";
			$img_inst = get_instance(CL_IMAGE);
			foreach($conns as $conn)
			{
				$imgurl = $img_inst->get_url_by_id($conn->prop("to"));
			};
			$this->vars(array(
				"imgurl" => $imgurl,
			));
		};

		$at_once = 20;

		// show document list, if there is a placeholder for it in the current template
		// XXX: I need a navigator
		if ($this->is_template("DOCLIST"))
		{
			// how the bloody hell do I get the limiting to work

			// prev 10 / next 10 .. how do I pass the thing?
			// äkki teha kuudega? ah? hm?

			// alguses näitame viimast 10-t
			// ... then how do I limit those?

			// hot damn, this thing sucks
			$dt = aw_global_get("date");
			if ((int)$dt == $dt)
			{
				$date = $dt;
			};
			$at = get_instance(CL_AUTHOR);
			list($nav,$doc_ids) = $at->get_docs_by_author(array(
				"author" => $to->prop("name"),
				"limit" => $at_once,
				"date" => $date,
			));

			// okey, I think I'll do it with dates

			$docs = "";
			// XXX: I need comment counts for each document id
			// how do I accomplish that?
			if (sizeof($doc_ids) > 0)
			{
				$doc_list = new object_list(array(
					"oid" => array_keys($doc_ids),
				));

				for($o = $doc_list->begin(); !$doc_list->end(); $o = $doc_list->next())
				{
					$this->vars(array(
						"url" => html::href(array(
							"url" => aw_ini_get("baseurl") . "/" . $o->id(),
							"caption" => strip_tags($o->prop("title")),
						)),
						"commcount" => $doc_ids[$o->id()]["commcount"],
						"commurl" => $this->mk_my_orb("show_threaded",array("board" => $o->id()),"forum"),
					));
					$docs .= $this->parse("ITEM");
				};
			};
			$this->vars(array(
				"ITEM" => $docs,
			));
			$this->vars(array(
				"DOCLIST" => $this->parse("DOCLIST"),
			));
			$nv = "";
			if ($nav["prev"])
			{
				$this->vars(array(
					"prevurl" => aw_url_change_var("date",$nav["prev"]),
				));
				$this->vars(array(
					"prevlink" => $this->parse("prevlink"),
				));
			};
			if ($nav["next"])
			{
				$this->vars(array(
					"nexturl" => aw_url_change_var("date",$nav["next"]),
				));
				$this->vars(array(
					"nextlink" => $this->parse("nextlink"),
				));
			};
		};
		return $this->parse();
	}

	////
	// !Perhaps I can make a single function that returns the latest event (if any)
	// for each connection?
	
	function do_org_actions($arr)
	{
		$ob = $arr["obj_inst"];
		$args = array();
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));
		switch($arr["prop"]["name"])
		{
			case "org_calls":
				$args["type"] = RELTYPE_PERSON_CALL;
				break;
			
			case "org_meetings":
				$args["type"] = RELTYPE_PERSON_MEETING;
				break;
			
			case "org_tasks":
				$args["type"] = RELTYPE_PERSON_TASK;
				break;
		};
		$conns = $ob->connections_from($args);
		$t = &$arr["prop"]["vcl_inst"];

		$arr["prop"]["vcl_inst"]->configure(array(
			"overview_func" => array(&$this,"get_overview"),
		));

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => !empty($arr["request"]["viewtype"]) ? $arr["request"]["viewtype"] : $arr["prop"]["viewtype"],
		));
		$start = $range["start"];
		$end = $range["end"];

		$overview_start = $range["overview_start"];

		$classes = aw_ini_get("classes");

		$return_url = urlencode(aw_global_get("REQUEST_URI"));
		$planner = get_instance(CL_PLANNER);
		classload("core/icons");
		$this->overview = array();

		foreach($conns as $conn)
		{
			$item = new object($conn->prop("to"));
			if ($item->prop("start1") < $overview_start)
			{
				continue;
			};
			
			$cldat = $classes[$item->class_id()];

			$icon = icons::get_icon_url($item);
		
			// I need to filter the connections based on whether they write to calendar
			// or not.
			$link = $planner->get_event_edit_link(array(
				"cal_id" => $this->cal_id,
				"event_id" => $item->id(),
				"return_url" => $return_url,
			));

			if ($item->prop("start1") > $start)
			{
				$t->add_item(array(
					"timestamp" => $item->prop("start1"),
					"data" => array(
						"name" => $item->name(),
						"link" => $link,
						"modifiedby" => $item->prop("modifiedby"),
						"icon" => $icon,
					),
				));
			};

			if ($item->prop("start1") > $overview_start)
			{
				$this->overview[$item->prop("start1")] = 1;
			};
		}
	}

	function get_overview($arr = array())
	{
		return $this->overview;
	}

	
	function on_connect_section_to_person($arr)
	{
		$conn = $arr['connection'];
		$target_obj = $conn->to();
		if($target_obj->class_id()==CL_CRM_PERSON)
		{
			$target_obj->connect(array(
				'to' => $conn->prop('from'),
				'reltype' => 21 //crm_section.reltype_section
			));
		}
		
	}
	

	// Invoked when a connection is created from organization to person
	// .. this will then create the opposite connection.
	function on_connect_org_to_person($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_PERSON)
		{
			$target_obj->connect(array(
			  "to" => $conn->prop("from"),
			  "reltype" => RELTYPE_WORK
			));
		};
	}

	// Invoked when a connection from organization to person is removed
	// .. this will then remove the opposite connection as well
	function on_disconnect_org_from_person($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_PERSON)
		{
			$target_obj->disconnect(array(
				"from" => $conn->prop("from"),
			));
		};
	}

		
	function on_disconnect_section_from_person($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_PERSON)
		{
			if($target_obj->is_connected_to(array('to'=>$conn->prop('from'))))
			{
				$target_obj->disconnect(array(
					"from" => $conn->prop("from"),
				));
			}
		};
	}
	
	function do_cv_skills_toolbar($toolbar, $arr)
	{
		$toolbar->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=>t('Uus')
		));
		
		$toolbar->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud tööpakkumised'),
		));
		
		$toolbar->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta'),
			'action' => 'submit',
		));
		
	}
	
	function do_person_skills_tree($arr)
	{
		$tree = &$arr['prop']['vcl_inst'];
	
		$tree->add_item(0,array(
    		"id" => 1,
    		"name" => t("Arvutioskused"),
    		"url" => $this->mk_my_orb("do_something",array()),
		));
		
		$tree->add_item(1,array(
    		"id" => 2,
    		"name" => t("Rakendused"),
    		"url" => $this->mk_my_orb("do_something",array()),
		));
		
		$tree->add_item(1,array(
    		"id" => 3,
    		"name" => t("Programmeerimine"),
    		"url" => $this->mk_my_orb("change", array(
    			"id" => $arr['obj_inst']->id(), 
    			"group" => $arr['request']['group'],
    			"skill" => "programming",
    			), CL_CRM_PERSON),
		));
		
		$tree->add_item(1,array(
    		"id" => 4,
    		"name" => t("Muu"),
    		"url" => $this->mk_my_orb("do_something",array()),
		));
		
		if($arr["request"]["skill"] == "languages")
		{
			$lang_capt = t("<b>Keeled</b>");
		}
		else
		{
			$lang_capt = t("Keeled");
		}
		
		$tree->add_item(0, array(
    		"id" => 5,
    		"name" => $lang_capt,
    		"url" => $this->mk_my_orb("change", array(
    			"id" => $arr['obj_inst']->id(), 
    			"group" => $arr['request']['group'],
    			"skill" => "languages",
    			), CL_CRM_PERSON),
		));
		
		$tree->add_item(0, array(
    		"id" => 6,
    		"name" => t("Juhiload"),
    		"url" => $this->mk_my_orb("change", array(
    			"id" => $arr['obj_inst']->id(), 
    			"group" => $arr['request']['group'],
    			"skill" => "driving_licenses",
    			), CL_CRM_PERSON),
		));
		
	}
	
	
	function do_previous_jobs_tb(&$arr)
	{
		$tb = &$arr["prop"]["toolbar"];
		
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus töökogemus"),
			"url" => $this->mk_my_orb("change", array(
				"group" => $arr["request"]["group"],
				"id" => $arr["obj_inst"]->id(),
				"new_prevjob" => true,
			), CL_CRM_PERSON),
		));
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta töökogemused"),
			"action" => "delete_objects",
			"confirm" => t("Oled kindel, et kustutada?"),
		));

		$tb->add_button(array(
			"name" => "edit",
			"img" => "edit.gif",
			"tooltip" => t("Muuda tkogemust"),
			"action" => "edit_something",
		));
		
		
		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta"),
			"action" => "submit",
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
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_PREVIOUS_JOB)) as $conn)
		{
			$prevjob = $conn->to();
			$table->define_data(array(
				"asutus" => html::href(array(
					"caption" => $prevjob->prop("organisation"),
					"url" => $this->mk_my_orb("change", array(
						"id" => $arr["request"]["id"],
						"group" => $arr["request"]["group"],
						"eoid" => $conn->id(),
					), CL_CRM_PERSON),
				)),
				"alates" => get_lc_date($prevjob->prop("date_from")),
				"ametikoht" => $prevjob->prop("profession"),
				"kuni" => get_lc_date($prevjob->prop("date_to")), 
				"from" => $conn->id(),
			));
		}
		
	}

	/**
	@attrib name=delete_objects
	**/
	function delete_objects($arr)
	{
		print_r($arr);
		foreach ($arr["sel"] as $del_conn)
		{
			$conn = new connection($del_conn);
			$obj = $conn->to();
			$obj->delete();
		}
		return  $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]), CL_CRM_PERSON); 
	}
	
	function callback_pre_save($arr)
	{	
		if(is_array($arr["request"]["speaking"]))
		{
			foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_LANGUAGE_SKILL)) as $conn)
			{
				$conn->delete();
			}
		
			foreach ($arr["request"]["speaking"] as $lang => $level)
			{
				$obj = new object(array(
					"class_id" => CL_CRM_PERSON_LANGUAGE,
					"parent" => $arr["obj_inst"]->id(),
				));
				$obj->save();
			
				$obj->set_prop("language", $lang);
				$obj->set_prop("speaking", $arr["request"]["speaking"][$lang]);
				$obj->set_prop("writing", $arr["request"]["writing"][$lang]);
				$obj->set_prop("understanding", $arr["request"]["understanding"][$lang]);
				$obj->set_prop("kogemusi", $arr["request"]["kogemusi"][$lang]);
			
			
				$lang_obj = &obj($lang);
				$obj->set_prop("name", $lang_obj->name());
				$obj->save();
			
				$arr["obj_inst"]->connect(array(
					"to" => $obj->id(),
					"reltype" => RELTYPE_LANGUAGE_SKILL,
				));	
			}
		}
		$arr["obj_inst"]->set_meta("no_create_user_yet", NULL);
	}
	
	
	function do_education_tb(&$arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		
		$tb->add_menu_button(array(
			'name'=>'new',
			'tooltip'=>t('Hariduse lisamine')
		));
	
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta haridus"),
			"action" => "delete_objects",
			"confirm" => t("Oled kindel, et kustutada?"),
		));

		$tb->add_menu_item(array(
				'parent'=>'new',
				'text'=>t('Põhiharidus'),
				'link'=> $this->mk_my_orb('change' ,array(
					"id" => $arr["obj_inst"]->id(),
					"group" => $arr["request"]["group"],
					"etype" => "basic_edu",		
				), CL_CRM_PERSON),
	
		));
			
		$tb->add_menu_item(array(
				'parent'=>'new',
				'text'=>t('Keskharidus'),
				'link'=> $this->mk_my_orb('change' ,array(
					"id" => $arr["obj_inst"]->id(),
					"group" => $arr["request"]["group"],
					"etype" => "secondary_edu",		
				), CL_CRM_PERSON),
		));
		
		$tb->add_menu_item(array(
				'parent'=>'new',
				'text'=>t('Kõrgharidus'),
				'link'=>$this->mk_my_orb('change' ,array(
					"id" => $arr["obj_inst"]->id(),
					"group" => $arr["request"]["group"],
					"etype" => "higher_edu",		
				), CL_CRM_PERSON)
		));
	
		$tb->add_menu_item(array(
				'parent'=>'new',
				'text'=>t('Kutseharidus'),
				'link'=>$this->mk_my_orb('change' ,array(
					"id" => $arr["obj_inst"]->id(),
					"group" => $arr["request"]["group"],
					"etype" => "voc_edu",
				),	CL_CRM_PERSON)
		));
	}
	
	function do_education_table(&$arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "school",
			"caption" => t("Kool"),
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "date_from",
			"caption" => t("Alates"),
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "date_to",
			"caption" => t("Kuni"),
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "etype",
			"caption" => t("Haridusliik"),	
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "profession",
			"caption" => t("Eriala"),
			"sortable" => 1
		));
		
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "sel",
		));
		
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_BASIC_EDUCATION)) as $b_edu_conn)
		{
			$b_edu = $b_edu_conn->to();

			$table->define_data(array(
				"school" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $arr["obj_inst"]->id(),
						"group" => $arr["request"]["group"],
						"eoid" => $b_edu_conn->id(),
						"etype" => "basic_edu",
						), CL_CRM_PERSON),
					"caption" => $b_edu->prop("school"), 
					)),
				"date_to" => $b_edu->prop("date_to"),
				"date_from" => $b_edu->prop("date_from"),
				"etype" => "Põhiharidus",
				"sel" => $b_edu_conn->id(),
			));
		}
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_SECONDARY_EDUCATION)) as $s_edu_conn)
		{
			$s_edu = $s_edu_conn->to();
			$table->define_data(array(
				"school" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $arr["obj_inst"]->id(),
						"group" => $arr["request"]["group"],
						"eoid" => $s_edu_conn->id(),
						"etype" => "secondary_edu",
						), CL_CRM_PERSON),
					"caption" => $s_edu->prop("school"), 
					)),
				"date_to" => $s_edu->prop("date_to"),
				"date_from" => $s_edu->prop("date_from"),
				"etype" => t("Keskharidus"),
				"sel" => $s_edu_conn->id(),
			));
		}
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_HIGHER_EDUCATION)) as $h_edu_conn)
		{
			$h_edu = $h_edu_conn->to();
			$table->define_data(array(
				"school" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $arr["obj_inst"]->id(),
						"group" => $arr["request"]["group"],
						"eoid" => $h_edu_conn->id(),
						"etype" => "higher_edu",
					), CL_CRM_PERSON),
					"caption" => $h_edu->prop("school"), 
				)),
				"date_to" => $h_edu->prop("date_to"),
				"date_from" => $h_edu->prop("date_from"),
				"profession" => $h_edu->prop("profession"),
				"etype" => t("Kõrgharidus"),
				"sel" => $h_edu_conn->id(),
			));
		}
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_VOCATIONAL_EDUCATION)) as $v_edu_conn)
		{
			$v_edu = $v_edu_conn->to();
			$table->define_data(array(
				"school" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $arr["obj_inst"]->id(),
						"group" => $arr["request"]["group"],
						"etype" => "voc_edu",
						"eoid" => $v_edu_conn->id(),
					), CL_CRM_PERSON),
					"caption" => $v_edu->prop("school"), 
				)), 
				"date_to" => $v_edu->prop("date_to"),
				"date_from" => $v_edu->prop("date_from"),
				"profession" => $v_edu->prop("profession"),
				"etype" => t("Kutseharidus"),
				"sel" => $v_edu_conn->id(),
			));
		}

	}
	
	function do_language_skills_table(&$arr)
	{
		$classificator = get_instance(CL_CLASSIFICATOR);
		
		$options = $classificator->get_options_for(array(
			"name" => "language_list",
			"clid" => CL_CRM_PERSON,
		));
		
		$level_options = $classificator->get_options_for(array(
			"name" => "language_levels",
			"clid" => CL_CRM_PERSON,
		));
					
		$table = &$arr["prop"]["vcl_inst"] ;
		
		$table->define_field(array(
			"name" => "language",
			"caption" => t("Keel"),
		));
		
		$table->define_field(array(
			"name" => "speaking",
			"caption" => t("Rääkimine"),
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "writing",
			"caption" => t("Kirjutamine"),
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "understanding",
			"caption" => t("Arusaamine"),
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "kogemusi",
			"caption" => t("Mitu aastat kogemusi"),
			"align" => "center",
		));

			
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_LANGUAGE_SKILL)) as $conn)
		{
			$obj = $conn->to();
			$lang_obj[$obj->prop("language")] = $obj; 
		}
	
		
		foreach ($options as $key => $option)
		{
			if(is_object($lang_obj[$key]))
			{
				$kogemusi_val = $lang_obj[$key]->prop("kogemusi");
				$speaking_val = $lang_obj[$key]->prop("speaking");
				$understanding_val = $lang_obj[$key]->prop("understanding");
				$writing_val = $lang_obj[$key]->prop("writing");
			}
			
			$table->define_data(array(
				"language" => $option,
				
				"kogemusi" => html::textbox(array(
					"name" => "kogemusi[$key]",
					"value" => $kogemusi_val,
					"size" => 3, 
				)),
				
				"speaking" => html::select(array(
					"options" => $level_options,
					"name" => "speaking[$key]",
					"value" => $speaking_val,
				)),
				"understanding" => html::select(array(
					"options" => $level_options,
					"name" => "understanding[$key]",
					"value" => $understanding_val,
				)),
				
				"writing" => html::select(array(
					"options" => $level_options,
					"name" => "writing[$key]",
					"value" => $writing_val, 
				)),
			));
		}
	}
	/**
	@attrib name=edit_something
	**/
	function edit_something($arr)
	{
		if($arr["sel"])
		{
			$eoid = current($arr["sel"]);
		
			return $this->mk_my_orb("change", array(
			"id" => $arr["id"], 
			"group" => $arr["group"], 
			"eoid" => $eoid), $arr["class"]);
		}
	}
	
	/** Needed to add link to login menu to change your person obj.
		@attrib name=edit_my_person_obj is_public=1 caption="Muuda isikuobjekti andmeid"
	**/
	function edit_my_person_obj($arr)
	{
		$u_i = get_instance("core/users/user");
		return $this->mk_my_orb("change", array(
			"id" => $u_i->get_current_person()), CL_CRM_PERSON);
	}

	/*
		the user can be associated with a company in two ways
		1) crm_person.reltype_work stuff
		2) crm_person belongs to a crm_section which can belong
			to a company or another crm_section, eventually the section
			is attached to a company
	*/
	function get_work_contacts($arr)
	{
		$rtrn = array();

		$conns = $arr['obj_inst']->connections_from(array(
			'type' => 'RELTYPE_WORK'
		));

		foreach($conns as $conn)
		{
			$rtrn[$conn->prop('to')] = $conn->prop('to.name');
		}

		$conns = $arr['obj_inst']->connections_from(array(
			'type' => 'RELTYPE_SECTION'
		));

		foreach($conns as $conn)
		{
			$obj = $conn->to();
			$this->_get_work_contacts($obj,&$rtrn);
		}
		return $rtrn;
	}

	function _get_work_contacts(&$obj,&$data)
	{
		//maybe i found the company?
		if($obj->class_id()==CL_CRM_SECTION)
		{
			$conns = $obj->connections_to(array(
				'type' => 28 //crm_company.section
			));

			foreach($conns as $conn)
			{
				$data[$conn->prop('from')] = $conn->prop('from.name');
			}
		}
	
		//getting the sections
		$conns = $obj->connections_to(array(
			'type' => 1, //crm_section.section
		));
		foreach($conns as $conn)
		{
			$obj = $conn->from();
			$this->_get_work_contacts(&$obj,&$data);
		}
	}

	// returns the profiles for person
	// if $all is true, then returns array, else the object
	function get_profile_for_person($person, $all = false)
	{
		$profile = array();
		// first, we'll check, if the person has an active profile
		$active_profile = $person->meta("active_profile");
		if($all)
		{
			$profs = $person->connections_from(array(
				"type" => "RELTYPE_PROFILE",
			));
			//if(count($profs) > 0)
			//{
				$prof_list = new object_list();
				foreach($profs as $prof)
				{
					$prof_list->add($prof->prop("to"));
				}
				$profile = $prof_list->arr();
				//arr($profile);
			//}
		}
		else
		{
			if(!empty($active_profile))
			{
				$profile = obj($active_profile);
			}
			else
			{
				$profile = get_first_obj_by_reltype("RELTYPE_PROFILE");
			}
		}
		return $profile;
	}
	
	/** returns a list of company id's that the given person works for

		@param person required

		@comment
			person - person storage object to find companies for


	**/
	function get_all_employers_for_person($person)
	{
		$c = new connection();
		$list = $c->find(array(
			"type" => 8, // crm_company.RELTYPE_WORKERS,
			"from.class_id" => CL_CRM_COMPANY,
			"to.class_id" => CL_CRM_PERSON,
			"to" => $person->id()
		));
		
		$ret = array();
		foreach($list as $item)
		{
			$ret[$item["from"]] = $item["from"];
		}
		return $ret;
	}

	function has_user($o)
	{
		obj_set_opt("no_cache", 1);
		$c = new connection();
		$res = $c->find(array(
			"to" => $o->id(),
			"from.class_id" => CL_USER,
			"type" => 2 // CL_USER.RELTYPE_PERSON
		));
		obj_set_opt("no_cache", 0);

		if (count($res))
		{
			$tmp = reset($res);
			if (is_oid($tmp["from"]) && $this->can("view", $tmp["from"]))
			{
				return obj($tmp["from"]);
			}
		}
		return false;
	}

	// this is a helper method, which can be used to add or update a specific
	// aspect of the person object
	function create_or_update_image($arr)
	{
		// this things needs to figure out whether this person already has an image
		// but this is going to be extraordinarily slow


	}
}
?>
