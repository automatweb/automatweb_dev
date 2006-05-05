<?php

// $Header: /home/cvs/automatweb_dev/classes/crm/crm_person.aw,v 1.128 2006/05/05 12:46:24 kristo Exp $
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_COMPANY, on_connect_org_to_person)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_SECTION, on_connect_section_to_person)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_COMPANY, on_disconnect_org_from_person)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_SECTION, on_disconnect_section_from_person)

@classinfo relationmgr=yes syslog_type=ST_CRM_PERSON no_status=1 confirm_save_data=1
@tableinfo kliendibaas_isik index=oid master_table=objects master_index=oid

@default table=objects
------------------------------------------------------------------

@groupinfo general2 caption="Üldine" parent=general
@default group=general2

@property name type=text
@caption Nimi

@default table=kliendibaas_isik

@property firstname type=textbox size=15 maxlength=50
@caption Eesnimi

@property lastname type=textbox size=15 maxlength=50
@caption Perekonnanimi

@property nickname type=textbox size=10 maxlength=20
@caption Hüüdnimi

@property personal_id type=textbox size=13 maxlength=11
@caption Isikukood

@property birthday type=date_select year_from=1930 year_to=2010 default=-1
@caption Sünnipäev

@property gender type=chooser
@caption Sugu

@property title type=chooser
@caption Tiitel

@property social_status type=chooser
@caption Perekonnaseis

@property spouse type=textbox size=25 maxlength=50
@caption Abikaasa

@property children1 type=select table=objects field=meta method=serialize
@caption Lapsi

@property pictureurl type=textbox size=40 maxlength=200
@caption Pildi/foto url

@property picture type=releditor reltype=RELTYPE_PICTURE rel_id=first props=file
@caption Pilt/foto

@property picture2 type=releditor reltype=RELTYPE_PICTURE2 rel_id=first props=file
@caption Pilt suuremana

@property ext_id type=textbox table=objects field=subclass maxlength=11
@caption Numbriline sidussüsteemi ID

@property ext_id_alphanumeric type=textbox maxlength=25
@caption Sidussüsteemi ID

@property code type=textbox
@caption Kood

property messenger type=textbox size=30 maxlength=200
caption Msn/yahoo/aol/icq

@property username type=text store=no
@comment Kasutajanimes on lubatud ladina tähestiku suur- ja väiketähed, numbrid 0-9 ning märgid alakriips ja punkt
@caption Kasutaja

@property password type=password table=objects field=meta method=serialize
@caption Parool

@property client_manager type=relpicker reltype=RELTYPE_CLIENT_MANAGER
@caption Kliendihaldur

@property is_customer type=checkbox ch_value=1 field=aw_is_customer
@caption Lisa kliendina

@property is_important type=checkbox ch_value=1 store=no
@caption Oluline

@property crm_settings type=text store=no
@caption CRM Seaded

@property cvactive type=checkbox ch_value=1 table=objects field=meta method=serialize
@caption CV aktiivne

------------------------------------------------------------------

@groupinfo contact caption="Kontaktandmed" parent=general
@default group=contact

@property work_contact type=relpicker reltype=RELTYPE_WORK
@caption Organisatsioon

@property org_section type=relpicker reltype=RELTYPE_SECTION multiple=1 table=objects field=meta method=serialize store=connect
@caption Osakond

@property rank type=relpicker reltype=RELTYPE_RANK automatic=1
@caption Ametinimetus

property personal_contact type=relpicker reltype=RELTYPE_ADDRESS
caption Kodused kontaktandmed

@property address type=relpicker reltype=RELTYPE_ADDRESS
@caption Aadress

@property email type=releditor mode=manager table=objects field=meta method=serialize reltype=RELTYPE_EMAIL props=mail table_fields=mail choose_default=1 always_show_add=1
@caption Meiliaadressid

@property phone type=releditor table=objects field=meta method=serialize mode=manager props=name,type table_fields=name,type reltype=RELTYPE_PHONE choose_default=1 always_show_add=1
@caption Telefoninumbrid

@property url type=releditor mode=manager table=objects field=meta method=serialize reltype=RELTYPE_URL props=url table_fields=url choose_default=1 always_show_add=1
@caption Veebiaadressid

@property comment type=textarea cols=40 rows=3 table=objects field=comment
@caption Kontakt

------------------------------------------------------------------
@groupinfo description caption="Kirjeldus" parent=general
@default group=description

@property notes type=textarea cols=60 rows=10
@caption Vabas vormis tekst

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Seostehaldur

------------------------------------------------------------------

@groupinfo documents_all caption="Dokumendid" submit=no parent=general
@default group=documents_all

@property docs_tb type=toolbar no_caption=1

@layout docs_lt type=hbox width=20%:80%

@layout docs_left type=vbox parent=docs_lt

@property docs_tree type=treeview parent=docs_left no_caption=1

@layout docs_search type=vbox parent=docs_left

@property docs_s_name type=textbox size=30 store=no captionside=top parent=docs_search
@caption Nimetus

@property docs_s_type type=select store=no captionside=top parent=docs_search
@caption Liik

@property docs_s_task type=textbox size=30 store=no captionside=top parent=docs_search
@caption Toimetus

@property docs_s_user type=textbox size=30 store=no captionside=top parent=docs_search
@caption Tegija

@property docs_s_customer type=textbox size=30 store=no captionside=top parent=docs_search
@caption Klient

@layout docs_s_but_row type=hbox parent=docs_left

@property docs_s_sbt type=submit store=no no_caption=1 parent=docs_s_but_row
@caption Otsi

@property docs_s_clear type=submit store=no no_caption=1 parent=docs_s_but_row
@caption T&uuml;hista otsing

@property docs_tbl type=table store=no no_caption=1 parent=docs_lt

------------------------------------------------------------------

@groupinfo settings caption="Muud seaded" parent=general
@default group=settings

@property templates type=select table=objects field=meta method=serialize
@caption Väljund

@property server_folder type=server_folder_selector table=objects field=meta method=serialize
@caption Kataloog serveris, kus asuvad failid

@property languages type=relpicker multiple=1 automatic=1 reltype=RELTYPE_LANGUAGE store=connect
@caption Keeled

------------------------------------------------------------------
@groupinfo cv caption="Elulugu"

@groupinfo education caption="Hariduskäik" parent=cv submit=no
@default group=education

@property edulevel type=select table=objects field=meta method=serialize
@caption Haridustase

@property education_edit type=releditor store=no mode=manager reltype=RELTYPE_EDUCATION props=school,field,speciality,start,end table_fields=school,field,speciality,start,end table=objects field=meta method=serialize

------------------------------------------------------------------

@groupinfo add_edu caption="Täienduskoolitus" parent=cv submit=no

@property add_edu_edit type=releditor store=no mode=manager reltype=RELTYPE_ADD_EDUCATION props=org,field,time,length table_fields=org,field,time,length group=add_edu
------------------------------------------------------------------

@groupinfo orgs caption="Organisatoorne kuuluvus" parent=cv submit=no

@property org_edit type=releditor store=no mode=manager reltype=RELTYPE_ORG_RELATION props=org,profession,start,end table_fields=org,profession,start,end group=orgs

------------------------------------------------------------------

@groupinfo recommends caption="Soovitajad" parent=cv submit=no

@property recommends_edit type=releditor store=no mode=manager reltype=RELTYPE_RECOMMENDS props=firstname,lastname,rank,work_contact,comment table_fields=firstname,lastname,rank,work_contact,comment group=recommends

------------------------------------------------------------------

@groupinfo addinfo caption="Muud oskused" parent=cv
@default group=addinfo
@default table=objects
@default field=meta
@default method=serialize

@property language type=text subtitle=1
@caption Keeleoskus

@property mlang type=relpicker reltype=RELTYPE_LANGUAGE_SKILL table=objects field=meta method=serialize
@caption Emakeel

@property lang_edit type=releditor mode=manager reltype=RELTYPE_LANGUAGE_SKILL props=language,talk,understand,write table_fields=language,talk,understand,write

@property compskills type=text subtitle=1
@caption Arvutioskus

@property compskills_edit type=releditor store=no mode=manager reltype=RELTYPE_EDUCATION props=school,date_from,date_to,additonal_info,subject table_fields=school,subject,date_from,date_to
Arvutioskus: Programm	Valik või tekstikast / Tase	Valik

@property drivers_license type=text subtitle=1
@caption Autojuhiload

@property dl_cat type=classificator store=no
@caption Kategooria

@property dl_since type=select
@caption Alates

@property dl_can_use type=checkbox ch_value=1
@caption Kas võimalik kasutada tööeesmärkidel

@property addinfo type=textarea
@caption Muud oskused

------------------------------------------------------------------

@groupinfo work caption="Töö"

@groupinfo experiences caption="Töökogemus" parent=work submit=no
@default group=experiences

@property tookogemused_edit type=releditor reltype=RELTYPE_EDUCATION props=organisation,profession,date_from,date_to,duties store=no
@property previous_jobs_tb type=toolbar no_caption=1 store=no
@property previous_jobs_table type=table store=no no_caption=1

------------------------------------------------------------------

@groupinfo work_wanted caption="Soovitud töö" parent=work submit=no
@default group=work_wanted

@property jobs_wanted_tb type=toolbar no_caption=1 store=no

@property jobs_wanted_table type=table no_caption=1

@property jobs_wanted type=releditor reltype=RELTYPE_EDUCATION props=name,palgasoov,valdkond,liik,asukoht,koormus,lisainfo,sbutton store=no

------------------------------------------------------------------

@groupinfo candidate caption="Kandideerimised" parent=work submit=no
@default group=candidate

@property candidate_tb type=toolbar no_caption=1 store=no

@property candidate_table type=table no_caption=1

@property candidate type=releditor reltype=RELTYPE_EDUCATION props=name,palgasoov,valdkond,liik,asukoht,koormus,lisainfo,sbutton store=no

------------------------------------------------------------------

@groupinfo overview caption="Tegevused"
@groupinfo all_actions caption="Kõik" parent=overview submit=no
@groupinfo calls caption="Kõned" parent=overview submit=no
@groupinfo meetings caption="Kohtumised" parent=overview submit=no
@groupinfo tasks caption="Toimetused" parent=overview submit=no

@property org_actions type=calendar no_caption=1 group=all_actions viewtype=relative
@caption org_actions

@property org_calls type=calendar no_caption=1 group=calls viewtype=relative
@caption Kõned

@property org_meetings type=calendar no_caption=1 group=meetings viewtype=relative
@caption Kohtumised

@property org_tasks type=calendar no_caption=1 group=tasks viewtype=relative
@caption Toimetused

------------------------------------------------------------------

@groupinfo data caption="Andmed"
@default group=data

@property udef_ch1 type=chooser multiple=1
@caption Kasutajadefineeritud CH1

@property udef_ta1 type=textarea rows=5 cols=50
@caption Kasutajadefineeritud TA1

@property udef_ta2 type=textarea rows=5 cols=50
@caption Kasutajadefineeritud TA2

@property udef_ta3 type=textarea rows=5 cols=50
@caption Kasutajadefineeritud TA3

@property udef_ta4 type=textarea rows=5 cols=50
@caption Kasutajadefineeritud TA4

@property udef_ta5 type=textarea rows=5 cols=50
@caption Kasutajadefineeritud TA5

------------------------------------------------------------------

@groupinfo my_stats caption="Minu statistika" submit=no submit_method=get
@default group=my_stats

@property stats_s_from type=date_select store=no
@caption Alates

@property stats_s_to type=date_select store=no
@caption Kuni

@property stats_s_time_sel type=select store=no
@caption Ajavahemik

@property stats_s_cust type=textbox store=no
@caption Klient

@property stats_s_show type=submit no_caption=1
@caption N&auml;ita

@property my_stats type=text store=no no_caption=1

------------------------------------------------------------------

@groupinfo transl caption="T&otilde;lgi"
@default group=transl

@property transl type=callback callback=callback_get_transl store=no
@caption T&otilde;lgi

------------------------------------------------------------------

@groupinfo cv_view caption="CV vaade" submit=no
@default group=cv_view

@property cv_view_tb type=toolbar no_caption=1 store=no

@property cv_view type=text no_caption=1 store=no

----------------------------------------------

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
  `ext_id_alphanumeric` varchar(25) default NULL,
  `picture` blob,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

/*
@reltype ADDRESS value=1 clid=CL_CRM_ADDRESS
@caption Aadressid

@reltype PICTURE2 value=2 clid=CL_IMAGE
@caption Pilt 2

@reltype PICTURE value=3 clid=CL_IMAGE
@caption Pilt

reltype BACKFORMS value=4 clid=CL_PILOT
caption Tagasiside vorm

reltype CHILDREN value=5 clid=CL_CRM_PERSON
caption Lapsed

@reltype WORK value=6 clid=CL_CRM_COMPANY
@caption Töökoht

@reltype RANK value=7 clid=CL_CRM_PROFESSION
@caption Ametinimetus

@reltype PERSON_MEETING value=8 clid=CL_CRM_MEETING
@caption Kohtumine

@reltype PERSON_CALL value=9 clid=CL_CRM_CALL
@caption Kõne

@reltype PERSON_TASK value=10 clid=CL_TASK
@caption Toimetus

@reltype EMAIL value=11 clid=CL_ML_MEMBER
@caption E-post

@reltype URL value=12 clid=CL_EXTLINK
@caption Veebiaadress

@reltype PHONE value=13 clid=CL_CRM_PHONE
@caption Telefon

reltype PROFILE value=14 clid=CL_PROFILE
caption Profiil

reltype USER_DATA value=15
caption Andmed

@reltype ORG_RELATION value=16 clid=CL_CRM_PERSON_WORK_RELATION
@caption Organisatoorne kuuluvus

@reltype RECOMMENDS value=17 clid=CL_CRM_PERSON
@caption Soovitaja

@reltype ORDER value=20 clid=CL_SHOP_ORDER
@caption Tellimus

@reltype SECTION value=21 clid=CL_CRM_SECTION
@caption Üksus

//parem nimi teretulnud, person on cl_crm_company jaox
//kliendihaldur
@reltype HANDLER value=22 clid=CL_CRM_COMPANY

@reltype EDUCATION value=23 clid=CL_CRM_PERSON_EDUCATION
@caption Haridus

@reltype ADD_EDUCATION value=24 clid=CL_CRM_PERSON_ADD_EDUCATION
@caption Täiendkoolitus

@reltype LANGUAGE_SKILL value=27 clid=CL_CRM_PERSON_LANGUAGE
@caption Keeleoskus

@reltype DESCRIPTION_DOC value=34 clid=CL_DOCUMENT,CL_MENU
@caption Kirjelduse dokument

reltype FRIEND value=35 clid=CL_CRM_PERSON
caption Sõber

reltype FAVOURITE value=36 clid=CL_CRM_PERSON
caption Lemmik

reltype MATCH value=37 clid=CL_CRM_PERSON
caption Väljavalitu

reltype BLOCKED value=38 clid=CL_CRM_PERSON
caption blokeeritud

reltype IGNORED value=39 clid=CL_CRM_PERSON
caption ignoreeritud

reltype FRIEND_GROUPS value=40 clid=CL_META
caption Sõbragrupid

@reltype VACATION value=41 clid=CL_CRM_VACATION
@caption Puhkus

@reltype CONTRACT_STOP value=42 clid=CL_CRM_CONTRACT_STOP
@caption Töölepingu peatamine

@reltype IMPORTANT_PERSON value=43 clid=CL_CRM_PERSON
@caption Kontaktisik

@reltype CLIENT_MANAGER value=44 clid=CL_CRM_PERSON
@caption Kliendihaldur

@reltype LANGUAGE value=45 clid=CL_LANGUAGE
@caption Keel

@reltype DOCS_FOLDER value=46 clid=CL_MENU
@caption Dokumentide kataloog

@reltype SERVER_FILES value=51 clid=CL_SERVER_FOLDER
@caption Failide kataloog serveris

*/

define("CRM_PERSON_USECASE_COWORKER", "coworker");
define("CRM_PERSON_USECASE_CLIENT", "s_p");
define("CRM_PERSON_USECASE_CLIENT_EMPLOYEE", "customer_employer");

class crm_person extends class_base
{
	function crm_person()
	{
		$this->init(array(
			"tpldir" => "crm/person",
			"clid" => CL_CRM_PERSON,
		));

		$this->trans_props = array(
			"udef_ta1", "udef_ta2", "udef_ta3", "udef_ta4", "udef_ta5"
		);
	}

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$form = &$arr["request"];
		switch($prop["name"])
		{
			case "firstname":
				if (($arr["new"] || !($tmp = $this->has_user($arr["obj_inst"]))))
				{
					$arr["obj_inst"]->set_meta("no_create_user_yet", true);

					if (strlen(trim($prop["value"])) and !strlen(trim($form["username"])))
					{
						$cl_user_creator = get_instance("crm/crm_user_creator");
						$errors = $cl_user_creator->get_uid_for_person($arr["obj_inst"], true);

						if ($errors)
						{
							$prop["error"] = $errors . t(' Palun sisestage nimi loodava kasutaja jaoks lahtrisse "Kasutaja"');
							return PROP_ERROR;
						}
						else
						{
							$arr["obj_inst"]->set_meta("no_create_user_yet", NULL);
						}
					}
				}
				break;

			case "username":
				if (($arr["new"] || !($tmp = $this->has_user($arr["obj_inst"]))) and strlen(trim($prop["value"])))
				{
					$arr["obj_inst"]->set_meta("no_create_user_yet", true);
					$arr["obj_inst"]->set_meta("tmp_crm_person_username", $prop["value"]);
					$cl_user_creator = get_instance("crm/crm_user_creator");
					$errors = $cl_user_creator->get_uid_for_person($arr["obj_inst"], true);

					if ($errors)
					{
						$prop["error"] = $errors;
						return PROP_ERROR;
					}
					else
					{
						$arr["obj_inst"]->set_meta("no_create_user_yet", NULL);
					}
				}
				break;

			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			case "lastname":
				if (!empty($form["firstname"]) || !empty($form["lastname"]))
				{
					$arr["obj_inst"]->set_name($form["firstname"]." ".$form["lastname"]);
				}
				break;

			case "picture":
			case "picture2":
				if(!$arr["new"])
				{
					$this->_resize_img($arr);
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
			case "edulevel":
				$data["options"] = array(
					0 => t("-- vali --"),
					1 => t("põhi"),
					2 => t("kesk"),
					3 => t("kesk-eri"),
					4 => t("kõrgem"),
				);
				break;

			case "cv_view_tb":
				$arr["prop"]["toolbar"]->add_button(array(
					"name" => "delete",
					"img" => "pdf_upload.gif",
					"tooltip" => t("Genereeri pdf"),
					"url" => $this->mk_my_orb("gen_job_pdf", array("id" => $arr["obj_inst"]->id()))
				));
				break;
			case "dl_since":
				for($i=date("Y"); $i>date("Y") - 80; $i--)
				{
					$data["options"][$i]=$i;
				}
				break;

			case "children1":
				$data["options"] = $this->make_keys(range(0, 10));
				break;

			case "stats_s_time_sel":
				$data["options"] = array(
					"" => t("--vali--"),
					"today" => t("T&auml;na"),
					"yesterday" => t("Eile"),
					"cur_week" => t("Jooksev n&auml;dal"),
					"cur_mon" => t("Jooksev kuu"),
					"last_mon" => t("Eelmine kuu")
				);
				$data["value"] = $arr["request"]["stats_s_time_sel"];
				if (!isset($arr["request"]["stats_s_time_sel"]))
				{
					$data["value"] = "cur_mon";
				}
				break;

			case "stats_s_from":
			case "stats_s_to":
				$data["value"] = date_edit::get_timestamp($arr["request"][$data["name"]]);
				break;

			case "stats_s_cust":
				$data["value"] = $arr["request"]["stats_s_cust"];
				break;

			case "my_stats":
				$this->_get_my_stats($arr);
				break;

			case "server_folder":
				$i = get_instance(CL_CRM_COMPANY);
				$i->_proc_server_folder($arr);
				break;

			case "docs_tb":
			case "docs_tree":
			case "docs_tbl":
			case 'docs_s_type':
			case "docs_news_tb":
			case "dn_res":
			case "documents_lmod":
				static $docs_impl;
				if (!$docs_impl)
				{
					$docs_impl = get_instance("applications/crm/crm_company_docs_impl");
				}
				$fn = "_get_".$data["name"];
				return $docs_impl->$fn($arr);


			case 'docs_s_name':
			case 'docs_s_task':
			case 'docs_s_name':
			case 'docs_s_customer':
			case 'docs_s_user':
			case 'docs_s_sbt':
			case 'docs_s_clear':
				if(!$arr['request']['do_doc_search'])
				{
					return PROP_IGNORE;
				}
				else
				{
					$data['value'] = $arr['request'][$data["name"]];
				}
				break;

			case "is_important":
				$u = get_instance(CL_USER);
				$p = obj($u->get_current_person());
				if ($p->is_connected_to(array("to" => $arr["obj_inst"]->id(), "type" => "RELTYPE_IMPORTANT_PERSON")))
				{
					$data["value"] = 1;
				}
				break;

			case "code":
				if ($data["value"] == "" && is_oid($ct = $arr["obj_inst"]->prop("address")) && $this->can("view", $ct))
				{
					$ct = obj($ct);
					$rk = $ct->prop("riik");
					if (is_oid($rk) && $this->can("view", $rk))
					{
						$rk = obj($rk);
						$code = substr(trim($rk->ord()), 0, 1);
						// get number of companies that have this country as an address
						$ol = new object_list(array(
							"class_id" => CL_CRM_PERSON,
							"CL_CRM_PERSON.address.riik.name" => $rk->name()
						));
						$ol2 = new object_list(array(
							"class_id" => CL_CRM_COMPANY,
							"CL_CRM_COMPANY.contact.riik.name" => $rk->name()
						));
						$code .= "-".sprintf("%04d", $ol->count() + $ol2->count() + 1);
						$data["value"] = $code;
					}
				}
				break;

			case "client_manager":
				$u = get_instance(CL_USER);
				$ws = array();
				$c = get_instance(CL_CRM_COMPANY);
				$c->get_all_workers_for_company(obj($u->get_current_company()), $ws);
				if (count($ws))
				{
					$ol = new object_list(array("oid" => $ws));
					$data["options"] = array("" => t("--vali--")) + $ol->names();
				}
				if ($arr["new"])
				{
					$data["value"] = $u->get_current_person();
				}
				if (isset($data["options"]) && !isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$data["value"]] = $tmp->name();
				}
				break;

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
				if($this->can("view", $arr["obj_inst"]->id()))
				{
					$company = $this->get_work_contacts($arr);
				}
				$data['options'] = $company;
				$data['options'][0] = t('--vali--');
				$data['options'] = array_reverse($data['options'], true);
				break;
			case 'rank' :
				/*
				//let's list the professions the organization/unit is associated with
				$drop_down_list = array();
				//if the person is associated with a section then show the professions
				//from the section and if not then show all the professions in the system
				$conns = $arr['obj_inst']->connections_to(array(
					'type'=> 28, //RELTYPE_SECTION
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
				$data['options'] = &$drop_down_list;*/
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
					$data["type"] = "textbox";
				}
				else
				{
					$data["value"] = html::get_change_url(
						$tmp->id(),
						array("return_url" => get_ru()),
						$tmp->name()
					);
				}
				break;

			case "crm_settings":
				$u = get_instance(CL_USER);
				$p = $u->get_current_person();
				if (true || $p == $arr["obj_inst"]->id())
				{
				// get all crm settings for this person or user
					$user = $this->has_user($arr["obj_inst"]);
					if (!$user)
					{
						return PROP_IGNORE;
					}
					$ol = new object_list(array(
						"class_id" => CL_CRM_SETTINGS,
						"CL_CRM_SETTINGS.RELTYPE_USER" => $user->id()
					));
					if (!$ol->count())
					{
						$ol = new object_list(array(
							"class_id" => CL_CRM_SETTINGS,
							"CL_CRM_SETTINGS.RELTYPE_PERSON" => $arr["obj_inst"]->id()
						));
					}

					if ($ol->count())
					{
						$b = $ol->begin();
						$data["value"] = html::href(array(
							"url" => html::get_change_url($b->id(), array("return_url" => get_ru())),
							"caption" => t("Muuda")
						));
						return PROP_OK;
					}
				}
				return PROP_IGNORE;
				break;
		}
		return $retval;

	}

	function isik_toolbar($args)
	{
		$toolbar = &$args["prop"]["toolbar"];

		$pl = get_instance(CL_PLANNER);
		$cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		$parents = array();

		$parents[6] = $args["obj_inst"]->parent();

		if (!empty($cal_id))
		{
			$user_calendar = new object($cal_id);
			$parents[8] = $parents[9] = $user_calendar->prop('event_folder');
		}

		/*

		$alist = array(
			array('caption' => t('Organisatsioon'),'class' => 'crm_company', 'reltype' => 6), //RELTYPE_WORK
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
							'return_url' => get_ru(),
						)),
						'text' => sprintf(t('Lisa %s'),$val['caption']),
					));
				}
			};

		};
		*/



		$action = array(
			array(
				"reltype" => 8, //RELTYPE_PERSON_MEETING,
				"clid" => CL_CRM_MEETING,
			),
			array(
				"reltype" => 9, //RELTYPE_PERSON_CALL,
				"clid" => CL_CRM_CALL,
			),
		);

		$toolbar->add_menu_button(array(
			"name" => "add_event",
			"tooltip" => t("Uus"),
		));

		$req = get_ru();

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
				"reltype"=> "RELTYPE_ADDRESS",
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
			$img = get_instance(CL_IMAGE);

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

	function _get_size($fl)
	{
		$fl = basename($fl);
		if ($fl{0} != "/")
		{
			$fl = aw_ini_get("site_basedir")."/files/".$fl{0}."/".$fl;
		}
		$sz = @getimagesize($fl);
		return array("width" => $sz[0], "height" => $sz[1]);
	}

	function parse_alias($arr)
	{
		// okey, I need to determine whether that template has a place for showing
		// a list of authors documents. If it does, then I need to create that list
		extract($arr);
		$to = new object($arr["alias"]["target"]);
		$this->read_template("pic_documents.tpl");
		$pdat = $this->fetch_person_by_id(array(
			"id" => $to->id(),
		));

		$al = get_instance("aliasmgr");
		$notes = $to->prop("notes");

		$al->parse_oo_aliases($to->id(), &$notes);

		$this->vars(array(
			"name" => $to->name(),
			"phone" => $pdat["phone"],
			"email" => $pdat["email"],
			"notes" => nl2br($notes),
		));
		// show image if there is a placeholder for it in the current template
		if ($this->template_has_var("imgurl") || $this->is_template("IMAGE"))
		{
			if($img = $to->get_first_conn_by_reltype("RELTYPE_PICTURE"))
			{
				$img_inst = get_instance(CL_IMAGE);
				$imgurl = $img_inst->get_url_by_id($img->prop("to"));
				if($img2 = $to->get_first_obj_by_reltype("RELTYPE_PICTURE2"))
				{
					$mes = $this->_get_size($img2->prop("file"));
					$imgurl2 = html::popup(array(
						"caption" => html::img(array(
							"url" => $imgurl,
							"border" => 0,
						)),
						"width" => $mes["width"],
						"height" => $mes["height"],
						"url" => $this->mk_my_orb("show_image", array("id" => $to->id()), CL_CRM_PERSON, false ,true),
						"menubar" => 1,
						"resizable" => 1,
					));
				}
				else
				{
					$imgurl2 = html::img(array(
						"url" => $imgurl,
						"border" => 0,
					));
				}
			}
			$this->vars(array(
				"imgurl" => $imgurl,
				"imgurl2" => $imgurl2,
			));
			if(strlen($imgurl) > 0)
			{
				$this->vars(array(
					"IMAGE" => $this->parse("IMAGE"),
				));
			}
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

	/**
		@attrib name=show_image nologin=1
		@param id required type=int acl=edit
		@param side optional
	**/
	function show_image($arr)
	{
		$obj = obj($arr["id"]);
		if($img = $obj->get_first_obj_by_reltype("RELTYPE_PICTURE2"))
		{
			$img_inst = get_instance(CL_IMAGE);
			$image = html::img(array(
				"url" => $img_inst->get_url($img->prop("file")),
				"border" => 0,
			));
		}
		$this->read_template("image_show.tpl");
		$this->vars(array(
			"name" => $img->name(),
			"image" => $image,
		));
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
				$args["type"] = 9; //RELTYPE_PERSON_CALL;
				break;

			case "org_meetings":
				$args["type"] = 8; //RELTYPE_PERSON_MEETING;
				break;

			case "org_tasks":
				$args["type"] = 10; //RELTYPE_PERSON_TASK;
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

		$return_url = get_ru();
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
				'reltype' => "RELTYPE_SECTION",
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
			  "reltype" => "RELTYPE_WORK",
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

		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PREVIOUS_JOB")) as $conn)
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

	function callback_post_save($arr)
	{
		if (aw_global_get("uid") != "")
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			if ($arr["request"]["is_important"] == 1)
			{	
				$p->connect(array(
					"to" => $arr["obj_inst"]->id(),
					"type" => "RELTYPE_IMPORTANT_PERSON"
				));
			}
			else
			if (is_oid($p->id()))
			{
				if (is_oid($p->id()) && $p->is_connected_to(array("to" => $arr["obj_inst"]->id(), "type" => "RELTYPE_IMPORTANT_PERSON")))
				{
					$p->disconnect(array(
						"from" => $arr["obj_inst"]->id(),
					));
				}
			}

			if ($this->can("view", $arr["request"]["add_to_task"]))
			{
				$task = obj($arr["request"]["add_to_task"]);
				$cc = $task->instance();
				$cc->add_participant($task, $arr["obj_inst"]);
			}

			if ($this->can("view", $arr["request"]["add_to_co"]))
			{
				$arr["obj_inst"]->set_prop("work_contact", $arr["request"]["add_to_co"]);
				$arr["obj_inst"]->save();
			}
		}

		// gen code if not done
		if ($arr["obj_inst"]->prop("code") == "")
		{
			if ($this->can("view", ($ct = $arr["obj_inst"]->prop("address"))))
			{
				$ct = obj($ct);
				$rk = $ct->prop("riik");
				if (is_oid($rk) && $this->can("view", $rk))
				{
					$rk = obj($rk);
					$code = substr(trim($rk->ord()), 0, 1);
					// get number of companies that have this country as an address
					$ol = new object_list(array(
						"class_id" => CL_CRM_PERSON,
						"CL_CRM_PERSON.address.riik.name" => $rk->name()
					));
					$ol2 = new object_list(array(
						"class_id" => CL_CRM_COMPANY,
						"CL_CRM_COMPANY.contact.riik.name" => $rk->name()
					));
					$code .= "-".sprintf("%04d", $ol->count() + $ol2->count()+1);
					$arr["obj_inst"]->set_prop("code", $code);
					$arr["obj_inst"]->save();
				}
			}

		}
	}

	function gen_code($o)
	{
		if ($o->prop("code") == "")
		{
			if ($this->can("view", ($ct = $o->prop("address"))))
			{
				$ct = obj($ct);
				$rk = $ct->prop("riik");
				if (is_oid($rk) && $this->can("view", $rk))
				{
					$rk = obj($rk);
					$code = substr(trim($rk->ord()), 0, 1);
					// get number of companies that have this country as an address
					$ol = new object_list(array(
						"class_id" => CL_CRM_PERSON,
						"CL_CRM_PERSON.address.riik.name" => $rk->name()
					));
					$ol2 = new object_list(array(
						"class_id" => CL_CRM_COMPANY,
						"CL_CRM_COMPANY.contact.riik.name" => $rk->name()
					));
					$code .= "-".sprintf("%04d", $ol->count() + $ol2->count()+1);
					$o->set_prop("code", $code);
					$o->save();
				}
			}
		}
	}

	function callback_pre_save($arr)
	{
		if(is_array($arr["request"]["speaking"]))
		{
			foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_LANGUAGE_SKILL")) as $conn)
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
					"reltype" => "RELTYPE_LANGUAGE_SKILL",
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


		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_BASIC_EDUCATION")) as $b_edu_conn)
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

		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_SECONDARY_EDUCATION")) as $s_edu_conn)
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

		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_HIGHER_EDUCATION")) as $h_edu_conn)
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

		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_VOCATIONAL_EDUCATION")) as $v_edu_conn)
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


		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_LANGUAGE_SKILL")) as $conn)
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
		$u_i = get_instance(CL_USER);
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

	function do_db_upgrade($tbl, $field, $q, $err)
	{
		switch($field)
		{
			case "udef_ta1":
			case "udef_ta2":
			case "udef_ta3":
			case "udef_ta4":
			case "udef_ta5":
			case "ext_id_alphanumeric":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "text"
				));
				return true;

			case "udef_ch1":
			case "picture2":
			case "client_manager":
			case "aw_is_customer":
			case "address":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "int",
				));
				return true;

			case "code":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "varchar(255)"
				));
				break;
		}
		return false;
	}

	function callback_mod_reforb($arr)
	{
		$arr["add_to_task"] = $_GET["add_to_task"];
		$arr["add_to_co"] = $_GET["add_to_co"];
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}

		if ($arr["id"] == "my_stats")
		{
			$u = get_instance(CL_USER);
			if ($arr["obj_inst"]->id() != $u->get_current_person())
			{
				return false;
			}
		}
		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	/**
		@attrib name=get_person_count_by_name

		@param co_name optional
		@param ignore_id optional
	**/
	function get_person_count_by_name($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"name" => $arr["co_name"],
			"lang_id" => array(),
			"site_id" => array(),
			"oid" => new obj_predicate_not($arr["ignore_id"])
		));
		die($ol->count()."\n");
	}

	/**
		@attrib name=go_to_first_person_by_name
		@param co_name optional
		@param return_url optional
	**/
	function go_to_first_person_by_name($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"name" => $arr["co_name"],
			"lang_id" => array(),
			"site_id" => array()
		));
		$o = $ol->begin();
		header("Location: ".html::get_change_url($o->id())."&return_url=".urlencode($arr["return_url"])."&warn_conflicts=1");
		die();
	}

	function callback_generate_scripts($arr)
	{
		if (!$arr["new"])
		{
			if ($arr["request"]["warn_conflicts"] == 1)
			{
				// get conflicts list and warn user if there are any

				// to do this, get all projects for this company that have the current company as a side
				$u = get_instance(CL_USER);
				$ol = new object_list(array(
					"class_id" => CL_PROJECT,
					"CL_PROJECT.RELTYPE_SIDE.name" => $arr["obj_inst"]->name(),
					//"CL_PROJECT.RELTYPE_ORDERER" => $u->get_current_company(),
					"lang_id" => array(),
					"site_id" => array()
				));
				if ($ol->count())
				{
					$link = $this->mk_my_orb("disp_conflict_pop", array("id" => $arr["obj_inst"]->id()),CL_CRM_COMPANY);
					return "aw_popup_scroll('$link','confl','200','200');";
				}
			}
			return "";
		}
		return
		"function aw_submit_handler() {".
		"if (document.changeform.firstname.value=='".$arr["obj_inst"]->prop("firstname")."' && document.changeform.lastname.value=='".$arr["obj_inst"]->prop("lastname")."') { return true; }".
		// fetch list of companies with that name and ask user if count > 0
		"var url = '".$this->mk_my_orb("get_person_count_by_name")."';".
		"url = url + '&co_name=' + document.changeform.firstname.value + ' '+document.changeform.lastname.value + '&ignore_id=".$arr["obj_inst"]->id()."';".
		"ct = aw_get_url_contents(url);".
		"num= parseInt(ct);".
		"if (num >0)
		{
			var ansa = confirm('Sellise nimega isik on juba olemas. Kas soovite minna selle objekti muutmisele?');
			if (ansa)
			{
				window.location = '".$this->mk_my_orb("go_to_first_person_by_name", array("return_url" => $arr["request"]["return_url"]))."&co_name=' + document.changeform.firstname.value + ' '+document.changeform.lastname.value;
				return false;
			}
		}".
		"return true;}";
	}

	// args:
	// obj_inst
	function get_current_usecase($arr)
	{
		$usecase = false;

		// if this is the current users employer, do nothing
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();
		if ($co == $arr["obj_inst"]->prop("work_contact"))
		{
			$usecase = CRM_PERSON_USECASE_COWORKER;
		}
		else
		if ($arr["obj_inst"]->prop("is_customer") == 1)
		{
			$usecase = CRM_PERSON_USECASE_CLIENT;
		}
		else
		if ($this->can("view", $arr["obj_inst"]->prop("work_contact")))
		{
			// customer employee
			$usecase = CRM_PERSON_USECASE_CLIENT_EMPLOYEE;
		}

		return $usecase;
	}

	function callback_get_cfgform($arr)
	{
		// if this is the current users employer, do nothing
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();
		if ($co == $arr["obj_inst"]->prop("work_contact"))
		{
			$s = get_instance(CL_CRM_SETTINGS);
			if (($o = $s->get_current_settings()))
			{
				return $o->prop("coworker_cfgform");
			}
		}
		else
		if ($arr["obj_inst"]->prop("is_customer") == 1)
		{
			// find the crm settings object for the current user
			$s = get_instance(CL_CRM_SETTINGS);
			if (($o = $s->get_current_settings()))
			{
				return $o->prop("s_p_cfgform");
			}
		}
		else
		if ($this->can("view", $arr["obj_inst"]->prop("work_contact")))
		{
			// customer employee cfgform
			$s = get_instance(CL_CRM_SETTINGS);
			if (($o = $s->get_current_settings()))
			{
				return $o->prop("customer_employer_cfgform");
			}
		}
		return false;
	}

	function _get_my_stats($arr)
	{
		$i = get_instance("applications/crm/crm_company_stats_impl");
		if (!$arr["request"]["MAX_FILE_SIZE"])
		{
			$arr["request"]["stats_s_time_sel"] = "cur_mon";
			$arr["request"]["MAX_FILE_SIZE"] = 1;
		}
		$arr["request"]["stats_s_res_type"] = "pers_det";
		$u = get_instance(CL_USER);
		$p = $u->get_current_person();
		$arr["request"]["stats_s_worker_sel"] = array($p => $p);
		classload("vcl/table");
		$t = new vcl_table;
		$arr["prop"]["vcl_inst"] = $t;
		$arr["request"]["ret"] = 1;
		$i->table_sum = true;
		$i->table_filt = true;
		$arr["prop"]["value"] = $i->_get_stats_s_res($arr);
	}

	function _resize_img($arr)
	{
		// if image is uploaded
		$img_o = $arr["obj_inst"]->get_first_obj_by_reltype($arr["prop"]["reltype"]);
		if (!$img_o)
		{
			return;
		}

		$s = get_instance(CL_CRM_SETTINGS);
		$settings = $s->get_current_settings();

		if ($settings)
		{
			$gal_conf = $settings->prop("person_img_settings");
			if ($this->can("view", $gal_conf))
			{
				$img_i = $img_o->instance();
				$img_i->do_resize_image(array(
					"o" => $img_o,
					"conf" => obj($gal_conf)
				));
			}
		}
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
		die($pdf_gen->gen_pdf(array(
			"filename" => $arr["id"],
			"source" => $this->show_cv(array(
				"id" => $arr["id"]
			))
		)));
	}

	function show_cv($arr)
	{
		$ob = new object($arr["id"]);
		$person_obj = current($ob->connections_to(/*array("from.class_id" => CL_CRM_PERSON)*/));
		if(!is_object($person_obj))
		{
			return false;
		}
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

		$personname = $person_obj->name();

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
}
?>
