<?php

/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_COMPANY, on_connect_org_to_person)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_SECTION, on_connect_section_to_person)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_COMPANY, on_disconnect_org_from_person)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_SECTION, on_disconnect_section_from_person)

@classinfo relationmgr=yes syslog_type=ST_CRM_PERSON no_status=1 confirm_save_data=1
@tableinfo kliendibaas_isik index=oid master_table=objects master_index=oid
@tableinfo aw_account_balances master_index=oid master_table=objects index=aw_oid

@default table=objects
------------------------------------------------------------------

@groupinfo general2 caption="&Uuml;ldine" parent=general
@default group=general2

@property person_tb type=toolbar submit=no no_caption=1
@caption Isiku toolbar

@property name type=text
@caption Nimi

@property balance type=hidden table=aw_account_balances field=aw_balance

@default table=kliendibaas_isik

@property firstname type=textbox size=15 maxlength=50
@caption Eesnimi

@property lastname type=textbox size=15 maxlength=50
@caption Perekonnanimi

@property nickname type=textbox size=10 maxlength=20
@caption H&uuml;&uuml;dnimi

@property personal_id type=textbox size=13 maxlength=11
@caption Isikukood

@property birthday type=date_select year_from=1930 year_to=2010 default=-1 save_format=iso8601
@caption S&uuml;nniaeg

@property birthday_hidden type=checkbox ch_value=1 table=objects field=meta method=serialize
@caption Peida s&uuml;nniaeg

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
@caption Numbriline siduss&uuml;steemi ID

@property ext_id_alphanumeric type=textbox maxlength=25
@caption Siduss&uuml;steemi ID

@property code type=textbox
@caption Kood

@property username type=text store=no
@comment Kasutajanimes on lubatud ladina t&auml;hestiku suur- ja v&auml;iket&auml;hed, numbrid 0-9 ning m&auml;rgid alakriips ja punkt
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

@property wage_doc type=relpicker ch_value=1 table=objects field=meta method=serialize reltype=RELTYPE_WAGE_DOC
@caption Palga dokument

@property nationality type=relpicker table=objects field=meta method=serialize reltype=RELTYPE_NATIONALITY
@caption Rahvus

@property citizenship_table type=table submit=no no_caption=1 editonly=1
@caption Kodakondsuse tabel

------------------------------------------------------------------

@groupinfo cust_rel caption="Kliendisuhe" parent=general
@default group=cust_rel

	@layout co_bottom_seller area_caption=Kliendisuhe&#44;&nbsp;tema_ostab_meilt closeable=1 type=hbox width=50%:50%

		@layout co_bottom_seller_l type=vbox parent=co_bottom_seller

			@property co_is_cust type=checkbox ch_value=1 store=no parent=co_bottom_seller_l no_caption=1 prop_cb=1
			@caption Kehtib

			@property cust_contract_creator type=select table=kliendibaas_isik parent=co_bottom_seller_l
			@caption Kliendisuhte looja

			@property cust_contract_date type=date_select table=kliendibaas_isik parent=co_bottom_seller_l
			@caption Kliendisuhte alguskuup&auml;ev

			@property contact_person type=text store=no parent=co_bottom_seller_l
			@caption Kliendpoolne kontaktisik

		@layout co_bottom_seller_r type=vbox parent=co_bottom_seller

			@property priority type=textbox table=kliendibaas_isik  parent=co_bottom_seller_r
			@caption Kliendi prioriteet

			@property referal_type type=classificator store=connect reltype=RELTYPE_REFERAL_TYPE parent=co_bottom_seller_r
			@caption Sissetuleku meetod

			@property client_manager type=relpicker reltype=RELTYPE_CLIENT_MANAGER table=kliendibaas_isik parent=co_bottom_seller_r
			@caption Kliendihaldur

			@property bill_due_date_days type=textbox size=5 table=kliendibaas_isik parent=co_bottom_seller_r
			@caption Makset&auml;htaeg (p&auml;evi)

			@property bill_penalty_pct type=textbox table=kliendibaas_isik size=5  parent=co_bottom_seller_r
			@caption Arve viivise %

	@layout co_bottom_buyer area_caption=Kliendisuhe&#44;&nbsp;meie_ostame_talt closeable=1 type=hbox width=50%:50%

		@layout co_bottom_buyer_l type=vbox parent=co_bottom_buyer
			@property co_is_buyer type=checkbox ch_value=1 store=no parent=co_bottom_buyer_l no_caption=1 prop_cb=1
			@caption Kehtib

			@property buyer_contract_creator type=select store=no parent=co_bottom_buyer_l
			@caption Hankijasuhte looja

			@property buyer_contract_date type=date_select store=no parent=co_bottom_buyer_l prop_cb=1
			@caption Hankijasuhte alguskuup&auml;ev

		@layout co_bottom_buyer_r type=vbox parent=co_bottom_buyer
			@property buyer_priority type=textbox store=no  parent=co_bottom_buyer_r prop_cb=1
			@caption M&uuml;&uuml;ja prioriteet

			@property buyer_contact_person type=text store=no parent=co_bottom_buyer_r prop_cb=1
			@caption M&uuml;&uuml;ja kontaktisik

------------------------------------------------------------------

@groupinfo contact caption="Kontaktandmed" parent=general
@default group=contact

@property ct_rel_tb type=toolbar no_caption=1 store=no

	@layout ct_super type=vbox  closeable=1 area_caption=Kontaktid

		@layout contact_l type=hbox parent=ct_super width=30%:30%:30%

			@property contact_desc_text type=text store=no parent=contact_l captionside=top
			@caption Kontaktandmed

			@property address type=relpicker reltype=RELTYPE_ADDRESS parent=contact_l captionside=top
			@caption Aadress


	@layout work_super type=vbox  closeable=1 area_caption=T&ouml;&ouml;kohad

		@property work_tbl type=table parent=work_super store=no no_caption=1

#		@layout work type=hbox parent=work_super width=30%:30%:30%


#			@property work_contact type=relpicker reltype=RELTYPE_WORK parent=work captionside=top
#			@caption Organisatsioon

#			@property org_section type=relpicker reltype=RELTYPE_SECTION parent=work multiple=1 table=objects field=meta method=serialize store=connect captionside=top
#			@caption Osakond

#			@property rank type=relpicker reltype=RELTYPE_RANK automatic=1 parent=work captionside=top
#			@caption Ametinimetus

#			@property comment type=textarea cols=40 rows=3 table=objects field=comment parent=work captionside=top
#			@caption Kontakt

#		@layout work_down type=hbox parent=work_super width=20%:80%

#			@property work_contact_start parent=work_down captionside=top type=releditor reltype=RELTYPE_CURRENT_JOB rel_id=first props=start store=no
#			@caption T&ouml;&ouml;le asumise aeg


		@layout ceditphf type=hbox width=50%:50%

			@layout cedit_phone type=vbox parent=ceditphf closeable=1 area_caption=Telefonid

				@property cedit_phone_tbl type=table no_caption=1 parent=cedit_phone store=no

			@layout cedit_fax type=vbox parent=ceditphf closeable=1 area_caption=Faksid

				@property cedit_telefax_tbl type=table no_caption=1 parent=cedit_fax store=no




		@layout ceditemlurl type=hbox width=50%:50%

			@layout cedit_email type=vbox parent=ceditemlurl closeable=1 area_caption=E-mail

				@property cedit_email_tbl type=table store=no no_caption=1 parent=cedit_email store=no

			@layout cedit_url type=vbox parent=ceditemlurl closeable=1 area_caption=URL

				@property cedit_url_tbl type=table store=no no_caption=1 parent=cedit_url store=no

		@layout ceditbank type=vbox closeable=1 area_caption=Pangaarved

			@property cedit_bank_account_tbl type=table store=no no_caption=1 parent=ceditbank

		@layout ceditprof type=vbox closeable=1 area_caption=T&ouml;&ouml;suhted

			@property cedit_profession_tbl type=table store=no no_caption=1 parent=ceditprof store=no

		@layout ceditadr type=vbox closeable=1 area_caption=Aadressid

			@property cedit_adr_tbl type=table store=no no_caption=1 parent=ceditadr

		@layout ceditmsn type=vbox closeable=1 area_caption=Msn/yahoo/aol/icq

			@property messenger type=textbox size=30 maxlength=200 parent=ceditmsn no_caption=1


@property email type=hidden table=objects field=meta method=serialize
@property phone type=hidden table=objects field=meta method=serialize
@property fax type=hidden table=objects field=meta method=serialize
@property url type=hidden table=objects field=meta method=serialize
@property aw_bank_account type=hidden table=objects field=meta method=serialize

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
@caption V&auml;ljund

@property server_folder type=server_folder_selector table=objects field=meta method=serialize
@caption Kataloog serveris, kus asuvad failid

@property languages type=relpicker multiple=1 automatic=1 reltype=RELTYPE_LANGUAGE store=connect
@caption Keeled

@property bill_due_days type=textbox size=5  table=objects field=meta method=serialize
@caption Makset&auml;htaeg (p&auml;evi)

@property currency type=relpicker reltype=RELTYPE_CURRENCY table=objects field=meta method=serialize
@caption Valuuta

@property is_quickmessenger_enabled type=checkbox table=objects field=meta method=serialize ch_value=1 default=0
@caption Quickmessenger enabled

-----------------------
@groupinfo work_hrs caption="T&ouml;&ouml;ajad" parent=general
@default group=work_hrs

	@property work_hrs type=textarea rows=7 cols=20 table=objects field=meta method=serialize
	@caption T&ouml;&ouml;ajad
	@comment Formaat: E: 9-17\nT: 14-19

	@property vacation_hrs type=textarea rows=7 cols=20 table=objects field=meta method=serialize
	@caption Puhkused
	@comment Formaat: 15.03.2007 - 18.03.2007

------------------------------------------------------------------
@groupinfo cv caption="Elulugu"

@groupinfo education caption="Haridusk&auml;ik" parent=cv
@default group=education

property education_edit type=releditor store=no mode=manager reltype=RELTYPE_EDUCATION props=degree,school,field,speciality,main_speciality,obtain_language,start,end,end_date,diploma_nr table_fields=degree,school,field,speciality,main_speciality,obtain_language,start,end,end_date,diploma_nr table=objects field=meta method=serialize

@property education_edit type=releditor store=no mode=manager2 reltype=RELTYPE_EDUCATION props=degree,school,field,speciality,main_speciality,obtain_language,start,end,end_date,diploma_nr table_fields=degree,school,field,speciality,main_speciality,obtain_language,start,end,end_date,diploma_nr table=objects field=meta method=serialize
@caption Haridus



------------------------------------------------------------------

@groupinfo add_edu caption="T&auml;ienduskoolitus" parent=cv submit=no

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
Arvutioskus: Programm	Valik v&otilde;i tekstikast / Tase	Valik

@property drivers_license type=text subtitle=1
@caption Autojuhiload

@property dl_cat type=classificator store=no
@caption Kategooria

@property dl_since type=select
@caption Alates

@property dl_can_use type=checkbox ch_value=1
@caption Kas v&otilde;imalik kasutada t&ouml;&ouml;eesm&auml;rkidel

@property addinfo type=textarea
@caption Muud oskused

------------------------------------------------------------------

@groupinfo work caption="T&ouml;&ouml;"

@groupinfo experiences caption="T&ouml;&ouml;kogemus" parent=work submit=no
@default group=experiences

@property previous_jobs_tb type=toolbar no_caption=1 store=no
@property previous_jobs_table type=table store=no no_caption=1

------------------------------------------------------------------

@groupinfo work_projects caption="Projektid" parent=work

	@property work_projects group=work_projects type=table store=no no_caption=1
	@caption Projektid

	@property work_projects_tasks group=work_projects type=hidden no_caption=1 field=meta method=serialize

------------------------------------------------------------------

@groupinfo work_wanted caption="Soovitud t&ouml;&ouml;" parent=work submit=no
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


@groupinfo skills caption="P&auml;devused" parent=work submit=no
@default group=skills

	@property skills_tb type=toolbar no_caption=1 store=no
	@property skills_table type=table no_caption=1 store=no

@groupinfo atwork caption="T&ouml;&ouml;ajad" parent=work submit=no
@default group=atwork

	@property atwork_table type=text no_caption=1 store=no

------------------------------------------------------------------

@groupinfo overview caption="Tegevused"
@groupinfo all_actions caption="K&otilde;ik" parent=overview submit=no
@groupinfo calls caption="K&otilde;ned" parent=overview submit=no
@groupinfo meetings caption="Kohtumised" parent=overview submit=no
@groupinfo tasks caption="Toimetused" parent=overview submit=no

@property org_actions type=calendar no_caption=1 group=all_actions viewtype=relative
@caption org_actions

@property org_calls type=calendar no_caption=1 group=calls viewtype=relative
@caption K&otilde;ned

@property org_meetings type=calendar no_caption=1 group=meetings viewtype=relative
@caption Kohtumised

@property org_tasks type=calendar no_caption=1 group=tasks viewtype=relative
@caption Toimetused

------------------------------------------------------------------

@groupinfo data caption="Andmed"
@default group=data

@property correspond_address type=relpicker reltype=RELTYPE_CORRESPOND_ADDRESS
@caption Kirjavahetuse aadress


@property udef_ch1 type=chooser multiple=1
@caption Kasutajadefineeritud CH1

@property udef_ch2 type=chooser multiple=1
@caption Kasutajadefineeritud CH2

@property udef_ch3 type=chooser multiple=1
@caption Kasutajadefineeritud CH3

@property user1 type=textbox
@caption Kasutajadefineeritud tekstikast 1

@property user2 type=textbox
@caption Kasutajadefineeritud tekstikast 2

@property user3 type=textbox
@caption Kasutajadefineeritud tekstikast 3

@property user4 type=textbox
@caption Kasutajadefineeritud tekstikast 4

@property user5 type=textbox
@caption Kasutajadefineeritud tekstikast 5

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

@property uservar1 type=classificator field=aw_varuser1 reltype=RELTYPE_VARUSER1 store=connect
@caption User-defined var 1

@property uservar2 type=classificator field=aw_varuser2 reltype=RELTYPE_VARUSER2 store=connect
@caption User-defined var 2

@property uservar3 type=classificator field=aw_varuser3 reltype=RELTYPE_VARUSER3 store=connect
@caption User-defined var 3

@property uservar4 type=classificator field=aw_varuser4 reltype=RELTYPE_VARUSER4 store=connect
@caption User-defined var 4

@property uservar5 type=classificator field=aw_varuser5 reltype=RELTYPE_VARUSER5 store=connect
@caption User-defined var 5

@property uservar6 type=classificator field=aw_varuser6 reltype=RELTYPE_VARUSER6 store=connect
@caption User-defined var 6

@property uservar7 type=classificator field=aw_varuser7 reltype=RELTYPE_VARUSER7 store=connect
@caption User-defined var 7

@property uservar8 type=classificator field=aw_varuser8 reltype=RELTYPE_VARUSER8 store=connect
@caption User-defined var 8

@property uservar9 type=classificator field=aw_varuser9 reltype=RELTYPE_VARUSER9 store=connect
@caption User-defined var 9

@property uservar10 type=classificator field=aw_varuser10 reltype=RELTYPE_VARUSER10 store=connect
@caption User-defined var 10

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

@property stats_s_type type=select store=no
@caption Vaade

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

@groupinfo ext_sys caption="Siduss&uuml;steemid" parent=general
@default group=ext_sys

	@property ext_sys_t type=table store=no no_caption=1

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
@caption T&ouml;&ouml;koht

@reltype RANK value=7 clid=CL_CRM_PROFESSION
@caption Ametinimetus

@reltype PERSON_MEETING value=8 clid=CL_CRM_MEETING
@caption Kohtumine

@reltype PERSON_CALL value=9 clid=CL_CRM_CALL
@caption K&otilde;ne

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
@caption &Uuml;ksus

//parem nimi teretulnud, person on cl_crm_company jaox
//kliendihaldur
@reltype HANDLER value=22 clid=CL_CRM_COMPANY

@reltype EDUCATION value=23 clid=CL_CRM_PERSON_EDUCATION
@caption Haridus

@reltype ADD_EDUCATION value=24 clid=CL_CRM_PERSON_ADD_EDUCATION
@caption T&auml;iendkoolitus

@reltype LANGUAGE_SKILL value=27 clid=CL_CRM_PERSON_LANGUAGE
@caption Keeleoskus

@reltype DESCRIPTION_DOC value=34 clid=CL_DOCUMENT,CL_MENU
@caption Kirjelduse dokument

reltype FRIEND value=35 clid=CL_CRM_PERSON
caption S&otilde;ber

reltype FAVOURITE value=36 clid=CL_CRM_PERSON
caption Lemmik

reltype MATCH value=37 clid=CL_CRM_PERSON
caption V&auml;ljavalitu

reltype BLOCKED value=38 clid=CL_CRM_PERSON
caption blokeeritud

reltype IGNORED value=39 clid=CL_CRM_PERSON
caption ignoreeritud

reltype FRIEND_GROUPS value=40 clid=CL_META
caption S&otilde;bragrupid

@reltype VACATION value=41 clid=CL_CRM_VACATION
@caption Puhkus

@reltype CONTRACT_STOP value=42 clid=CL_CRM_CONTRACT_STOP
@caption T&ouml;&ouml;lepingu peatamine

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

@reltype WAGE_DOC value=52 clid=CL_DOCUMENT
@caption Palgainfo

@reltype HAS_SKILL value=53 clid=CL_PERSON_HAS_SKILL
@caption P&auml;devus

@reltype FAX value=54 clid=CL_CRM_PHONE
@caption Faks

@reltype VARUSER1 value=55 clid=CL_META
@caption kasutajadefineeritud muutuja 1

@reltype VARUSER2 value=56 clid=CL_META
@caption kasutajadefineeritud muutuja 2

@reltype VARUSER3 value=57 clid=CL_META
@caption kasutajadefineeritud muutuja 3

@reltype CORRESPOND_ADDRESS value=58 clid=CL_CRM_ADDRESS
@caption Aadressid

@reltype VARUSER4 value=59 clid=CL_META
@caption kasutajadefineeritud muutuja 4

@reltype VARUSER5 value=60 clid=CL_META
@caption kasutajadefineeritud muutuja 5

@reltype VARUSER6 value=61 clid=CL_META
@caption kasutajadefineeritud muutuja 6

@reltype VARUSER7 value=62 clid=CL_META
@caption kasutajadefineeritud muutuja 7

@reltype VARUSER8 value=63 clid=CL_META
@caption kasutajadefineeritud muutuja 8

@reltype VARUSER9 value=64 clid=CL_META
@caption kasutajadefineeritud muutuja 9

@reltype VARUSER10 value=65 clid=CL_META
@caption kasutajadefineeritud muutuja 10


@reltype PREVIOUS_JOB value=66 clid=CL_CRM_PERSON_WORK_RELATION
@caption Eelnev t&ouml;&ouml;kogemus

@reltype CURRENT_JOB value=67 clid=CL_CRM_PERSON_WORK_RELATION
@caption Praegune t&ouml;&ouml;koht

@reltype CURRENCY value=68 clid=CL_CURRENCY
@caption valuuta

@reltype REFERAL_TYPE value=69 clid=CL_META
@caption sissetuleku meetod

@reltype CONTACT_PERSON value=70 clid=CL_CRM_PERSON
@caption Kontaktisik

@reltype BUYER_REFERAL_TYPE value=71 clid=CL_META
@caption sissetuleku meetod

@reltype BANK_ACCOUNT value=72 clid=CL_CRM_BANK_ACCOUNT
@caption arveldusarve

@reltype NATIONALITY value=73 clid=CL_NATIONALITY
@caption rahvus

@reltype CITIZENSHIP value=74 clid=CL_CITIZENSHIP
@caption kodakondsus

@reltype DEGREE value=75 clid=CL_CRM_DEGREE
@caption Kraad

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
		$this->edulevel_options = array(
			0 => t("-- Vali &uuml;ks --"),
			"pohiharidus" => t("P&otilde;hiharidus"),
			"keskharidus" => t("Keskharidus"),
			"keskeriharidus" => t("Kesk-eriharidus"),
			"kutsekeskharidus" => t("Kutsekeskharidus"),
			"kutsekorgharidus" => t("Kutsek&otilde;rgharidus"),
			"rakenduskorgharidus" => t("Rakendusk&otilde;rgharidus"),
			"korgharidus" => t("K&otilde;rgharidus"),
			"diplom" => t("Diplom"),
			"bakalaureus" => t("Bakalaureus"),
			"magister" => t("Magister"),
			"doktor" => t("Doktor"),
			"teadustekandidaat" => t("Teaduste kandidaat"),
		);
	}

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$form = &$arr["request"];
		switch($prop["name"])
		{
			case "work_tbl":
				$this->_save_work_tbl($arr);
				break;
			case "citizenship_table":
				$this->_save_citizenship_table($arr);
				break;
			case "phone":
			case "fax":
			case "url":
			case "email":
			case "aw_bank_account":
				return PROP_IGNORE;

			case "rank":
				$arr["obj_inst"]->set_prop("rank", $arr["request"]["rank"]);
				return PROP_IGNORE;

			case "cedit_phone_tbl":
			case "cedit_telefax_tbl":
			case "cedit_url_tbl":
			case "cedit_email_tbl":
			case "cedit_adr_tbl":
			case "cedit_bank_account_tbl":
//			case "cedit_profession_tbl":
				static $i;
				if (!$i)
				{
					$i = get_instance("applications/crm/crm_company_cedit_impl");
				}
				$fn = "_set_".$prop["name"];
				$i->$fn($arr);
				break;

			case "ext_sys_t":
				$this->_save_ext_sys_t($arr);
				break;

			case "firstname":
				if (($arr["new"] || !($tmp = $this->has_user($arr["obj_inst"]))))
				{
					$arr["obj_inst"]->set_meta("no_create_user_yet", true);

					if (strlen(trim($prop["value"])) and !strlen(trim($form["username"])) && $arr["request"]["password"] != "")
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

			case "address":
				return PROP_IGNORE;

			//kliendisuhte teema
			case "contact_person":
				$arr["prop"]["value"] = $arr["obj_inst"]->id();
			case "priority":
			case "bill_due_date_days":
			case "bill_penalty_pct":
			case "referal_type":
			case "client_manager":

				if($prop["name"] == "bill_penalty_pct") $prop["value"] = str_replace(",", ".", $prop["value"]);
				$this->set_cust_rel_data($arr);
				break;

			case "cust_contract_date":
			// save to rel
				if (($rel = $this->get_cust_rel($arr["obj_inst"])))
				{
					$rel->set_prop($prop["name"], date_edit::get_timestamp($prop["value"]));
					$rel->save();
				}
				break;

			case "buyer_contract_date":
				$co = get_current_company();
				if (($rel = $this->get_cust_rel($co , 0 , $arr["obj_inst"])))
				{
					$rel->set_prop("buyer_contract_date", date_edit::get_timestamp($prop["value"]));
					$rel->save();
				}
				break;
			case "buyer_contact_person":
				$arr["prop"]["value"] = $arr["obj_inst"]->id();
			case "buyer_priority":
			case "buyer_contract_creator":
				$this->set_buyer_rel_data($arr);
				break;

			case "cust_contract_creator":
			case "bill_due_days":
				// save to rel
				if (($rel = $this->get_cust_rel($arr["obj_inst"])))
				{
					$rel->set_prop($prop["name"] == "bill_due_days" ? "bill_due_date_days" : $prop["name"], $prop["value"]);
					$rel->save();
				}
				break;

			case "co_is_cust":
			case "co_is_buyer":
				$fn = "_set_".$prop["name"];
					$this->$fn($arr);
				break;
		};
		return $retval;
	}

	function is_connected_to_section($org,$section)
	{
		$ret = 0;
		foreach($org->connections_from(array("type" => RELTYPE_SECTION)) as $conn)
		{
			if($conn->prop("to") == $section)
			{
				$ret = 1;
			}
			else
			{
				$sec_obj = $conn->to();
				$ret = $this->is_connected_to_section($sec_obj,$section);
			}

			if($ret == 1)
			{
				break;
			}
		}
		return $ret;
	}

	function _save_work_tbl($arr)
	{
		foreach($arr["obj_inst"]->connections_from(array("type" => array(6, 21))) as $conn)
		{
			$doomed_conns[$conn->prop("to")] = $conn->id();
		}
		foreach($arr["request"]["work_tbl"] as $wr_id => $data)
		{
			$wr = new object($wr_id);
			$wr->set_prop("org", $data["org"]);
			$wr->set_prop("section", $data["sec"]);
			$wr->set_prop("profession", $data["pro"]);
			$wr->save();
			if($this->can("view", $data["org"]))
			{
				$org = obj($data["org"]);
				if($this->can("view", $data["sec"]))
				{
					if(!$this->is_connected_to_section($org,$data["sec"]))
					{
						$org->connect(array(
							"to" => $data["sec"],
							"reltype" => 28,		// RELTYPE_SECTION
						));
					}
				}
				else
				{
					$org->connect(array(
						"to" => $arr["obj_inst"]->id(),
						"reltype" => 8,		// RELTYPE_WORKERS
					));
					unset($doomed_conns[$data["org"]]);
				}
			}
			if($this->can("view", $data["sec"]))
			{
				$sec = obj($data["sec"]);
				if($this->can("view", $data["pro"]))
				{
					$sec->connect(array(
						"to" => $data["pro"],
						"reltype" => 3,		// RELTYPE_PROFESSIONS
					));
				}
				$sec->connect(array(
					"to" => $arr["obj_inst"]->id(),
					"reltype" => 2,		// RELTYPE_WORKERS
				));
				unset($doomed_conns[$data["sec"]]);
			}
		}
		foreach($doomed_conns as $doomed_conn)
		{
			$doomed_conn = new connection($doomed_conn);
			$doomed_conn->delete();
		}
	}

	function _get_cust_contract_creator($arr)
	{
		// list of all persons in my company
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();
		$arr["prop"]["options"] = $this->get_employee_picker(obj($co), true);
		if (($rel = $this->get_cust_rel($arr["obj_inst"])))
		{
			$arr["prop"]["value"] = $rel->prop($arr["prop"]["name"]);
		}

		if (!isset($arr["prop"]["options"][$arr["prop"]["value"]]) && $this->can("view", $arr["prop"]["value"]))
		{
			$v = obj($arr["prop"]["value"]);
			$arr["prop"]["options"][$arr["prop"]["value"]] = $v->name();
		}
	}

	function _get_buyer_contract_creator($arr)
	{
		// list of all persons in my company
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();
		$arr["prop"]["options"] = $this->get_employee_picker(obj($co), true);
		if (($rel = $this->get_cust_rel(obj($co) , 0 , $arr["obj_inst"])))
		{
			$arr["prop"]["value"] = $rel->prop($arr["prop"]["name"]);
		}

		if (!isset($arr["prop"]["options"][$arr["prop"]["value"]]) && $this->can("view", $arr["prop"]["value"]))
		{
			$v = obj($arr["prop"]["value"]);
			$arr["prop"]["options"][$arr["prop"]["value"]] = $v->name();
		}
	}

	function get_employee_picker($co = null, $add_empty = false, $important_only = false)
	{
		$coi = get_instance(CL_CRM_COMPANY);
		if (!$co)
		{
			$u = get_instance(CL_USER);
			$co = obj($u->get_current_company());
		}

		static $cache;
		if (isset($cache[$co->id()][$add_empty][$important_only]))
		{
			return $cache[$co->id()][$add_empty][$important_only];
		}

		if ($add_empty)
		{
			$res = array("" => t("--vali--"));
		}
		else
		{
			$res = array();
		}
		$coi->get_all_workers_for_company($co, $res);
		if (!count($res))
		{
			$cache[$co->id()][$add_empty][$important_only] = $res;
			return $res;
		}

		if ($important_only)
		{
			// filter out my important persons
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());

			$tmp = array();
			foreach($p->connections_from(array("type" => "RELTYPE_IMPORTANT_PERSON")) as $c)
			{
				if ($res[$c->prop("to")])
				{
					$tmp[$c->prop("to")] = $c->prop("to");
				}
			}
			$res = $tmp;
		}

		if (count($res))
		{
			$ol = new object_list(array("oid" => $res, "sort_by" => "objects.name", "lang_id" => array(), "site_id" => array()));
		}
		else
		{
			$ol = new object_list();
		}
		$res = ($add_empty ? array("" => t("--vali--")) : array()) +  $ol->names();
		uasort($res, array(&$this, "__person_name_sorter"));
		$cache[$co->id()][$add_empty][$important_only] = $res;
		return $res;
	}


	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "education_edit[speciality]":
				if(!$arr["called_from"] == "releditor_table")
				{
					$data["value"] = 111;
				}
				break;
			case "work_tbl":
				return $this->_get_work_tbl($arr);
				break;
			case "nationality":
				$ol = new object_list(array(
					"site_id" => array(),
					"lang_id" => array(),
					"class_id" => CL_NATIONALITY,
				));
				$data["options"] = array("" => " ")  + $ol->names();
				break;
			case "org_section":
				if(!is_array($data["value"]) && is_array(unserialize($data["value"])))
				{
					$data["value"] = unserialize($data["value"]);
				}
				break;
			case "work_projects":
				$t = &$data["vcl_inst"];
				$t->define_field(array(
					"name" => "select",
					"caption" => t("CV"),
					"width" => "10"
				));
				$t->define_field(array(
					"name" => "project",
					"caption" => t("Projekt"),
					"width" => "300"
				));
				$t->define_field(array(
					"name" => "value",
					"caption" => t("Hind"),
				));
				$t->define_field(array(
					"name" => "role",
					"caption" => t("Roll"),
				));
				$t->define_field(array(
					"name" => "description",
					"caption" => t("&Uuml;lessanded"),
				));


				$tasks = $this->get_work_project_tasks($arr["obj_inst"]->id());
				$i = get_instance(CL_USER);
				foreach($this->get_person_and_org_related_projects($arr["obj_inst"]->id()) as $oid => $obj)
				{
					$project = html::href(array(
						"caption" => $obj->name(),
						"url" => $this->mk_my_orb("change", array(
							"id" => $oid,
							"return_url" => get_ru(),
						), CL_PROJECT),
					));
					$roles = $this->get_project_roles(array(
						"person" => $arr["obj_inst"]->id(),
						"project" => $oid,
					));
					$roles_url = $this->mk_my_orb("change", array(
						"from_org" => $i->get_current_company(),
						"to_org" => current($obj->prop("orderer")),
						"to_project" => $oid,
						"class" => "crm_role_manager",
					));
					$roles_url = html::href(array(
						"caption" => t("Rollid"),
						"url" => "#",
						"onClick" => "javascript:aw_popup_scroll(\"".$roles_url."\", \"Rollid\", 500, 500);",
					));

					$t->define_data(array(
						"project" => $project,
						"description" => html::textarea(array(
							"name" => "project_tasks[".$oid."]",
							"value" => $tasks[$oid]["task"],
							"rows" => 10,
							"cols" => 100,
						)),
						"value" => ($_t = $obj->prop("proj_price"))?$_t:t("-"),
						"role" => ($_t = join(",", $roles))?$_t." (".$roles_url.")":$roles_url,
						"select" => html::checkbox(array(
							"name" => "project_sel[".$oid."]",
							"checked" => checked($tasks[$oid]["selected"]),
						)),
					));
				}

				break;
			case "work_contact_start":
				if(!$arr["obj_inst"]->prop("work_contact"))
				{
					return PROP_IGNORE;
				}
				break;
			case "address":
				return PROP_IGNORE;

			case "_bd_upg":
				return PROP_IGNORE;

			case "atwork_table":
				$this->_atwork_table($arr);
				break;

			case "skills_tb":
				$this->_skills_tb($arr);
				break;

			case "skills_table":
				$this->_skills_table($arr);
				break;

			case "ct_rel_tb":
				$this->_ct_rel_tb($arr);
				break;

			case "contact_desc_text":
				$data["value"] = $this->get_short_description($arr);
				break;

			case "ext_sys_t":
				$this->_ext_sys_t(&$arr);
				break;

			case "edulevel":
				$data["options"] = array(
					0 => t("-- vali --"),
					1 => t("p&otilde;hi"),
					2 => t("kesk"),
					3 => t("kesk-eri"),
					4 => t("k&otilde;rgem"),
				);
				break;

			case "citizenship_table":
				$cit = $arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CITIZENSHIP"));
				if(!sizeof($cit))
				{
					return PROP_IGNORE;
				}
				$this->_get_citizenship_table(&$arr);
				break;

			case "person_tb":
				$this->_get_person_tb(&$arr);
				break;

			case "cv_view_tb":
				/*
				$arr["prop"]["toolbar"]->add_button(array(
					"name" => "delete",
					"img" => "pdf_upload.gif",
					"tooltip" => t("Genereeri pdf"),
					"url" => $this->mk_my_orb("gen_job_pdf", array(
						"id" => $arr["obj_inst"]->id(),
						"cv_tpl" => ($_t = $arr["request"]["cv_tpl"])?$_t:0,
					))
				));
				*/
				$tpl = $arr["request"]["cv_tpl"]?("cv/".basename($v[$arr["request"]["cv_tpl"]])):false;
				$url = $this->mk_my_orb("show_cv", array(
					"cv" => $tpl,
					"id" => $arr["obj_inst"]->id(),
					"die" => "1",
				));
				$arr["prop"]["toolbar"]->add_button(array(
					"name" => "delete",
					"img" => "preview.gif",
					"tooltip" => t("Popup vaade"),
					"url" => "#",
					/*
					"url" => $this->mk_my_orb("gen_job_pdf", array(
						"id" => $arr["obj_inst"]->id(),
						"cv_tpl" => ($_t = $arr["request"]["cv_tpl"])?$_t:0,
					)),
					*/
					"onClick" => "aw_popup_scroll('".$url."','Eelvaade', 900, 900);"
				));
				//arr($arr);
				$arr["prop"]["toolbar"]->add_cdata(html::select(array(
					"name" => "cv_tpl",
					"options" => array_values($this->get_cv_tpl()),
					"selected" => $arr["request"]["cv_tpl"],
				)));
				$arr["prop"]["toolbar"]->add_button(array(
					"name" => "show",
					"img" => "save.gif",
					"tooltip" => t("N&auml;ita"),
					"action" => "",//"javascript:submit_changeform('');",
				));
				break;
			case "cv_view":
				$v = array_keys($this->get_cv_tpl());
				$tpl = $arr["request"]["cv_tpl"]?("cv/".basename($v[$arr["request"]["cv_tpl"]])):false;
				$arr["prop"]["value"] .= $this->show_cv(array(
					"id" => $arr["obj_inst"]->id(),
					"cv" => $tpl,
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

			case "stats_s_type":
				$data["value"] = $arr["request"]["stats_s_type"];
				$data["options"] = array(
					"rows" => t("Ridade kaupa"),
					"" => t("Kokkuv&otilde;te"),
				);
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
					t("H&auml;rra"),
					t("Proua"),
					t("Preili")
				);
				break;

			case "social_status":
				$data["options"] = array(
					3 => t("Vallaline"),
					1 => t("Abielus"),
					2 => t("Lahutatud"),
					4 => t("Vabaabielus"),
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

			case "cedit_phone_tbl":
				$i = get_instance("applications/crm/crm_company_cedit_impl");
				$t = &$data["vcl_inst"];
				$fields = array(
					"number" => t("Telefoninumber"),
					"type" => t("T&uuml;&uuml;p"),
					"is_public" => t("Avalik"),
					"rels" => t("Seotus t&ouml;&ouml;kohaga"),
				);
				$i->init_cedit_tables(&$t, $fields);
				$i->_get_phone_tbl($t, $arr);
				break;

			case "cedit_telefax_tbl":
				$i = get_instance("applications/crm/crm_company_cedit_impl");
				$t = &$data["vcl_inst"];
				$fields = array(
					"number" => t("Faksi number"),
					"rels" => t("Seotus t&ouml;&ouml;kohaga"),
				);
				$i->init_cedit_tables(&$t, $fields);
				$i->_get_fax_tbl($t, $arr);
				break;

			case "cedit_url_tbl":
				$i = get_instance("applications/crm/crm_company_cedit_impl");
				$t = &$data["vcl_inst"];
				$fields = array(
					"url" => t("Veebiaadress"),
				);
				$i->init_cedit_tables(&$t, $fields);
				$i->_get_url_tbl($t, $arr);
				break;

			case "cedit_email_tbl":
				$i = get_instance("applications/crm/crm_company_cedit_impl");
				$t = &$data["vcl_inst"];
				$fields = array(
					"email" => t("Emaili aadress"),
					"rels" => t("Seotus t&ouml;&ouml;kohaga"),
				);
				$i->init_cedit_tables(&$t, $fields);
				$i->_get_email_tbl($t, $arr);
				break;

			case "cedit_profession_tbl":
				$i = get_instance("applications/crm/crm_company_cedit_impl");
				$t = &$data["vcl_inst"];
				$fields = array(
					"org" => t("Organisatsioon"),
					"profession" => t("Amet"),
					"start" => t("Suhte algus"),
					"end" => t("Suhte l&otilde;pp"),
				);
				$i->init_cedit_tables(&$t, $fields);
				$i->_get_profession_tbl($t, $arr);
				break;

			case "cedit_bank_account_tbl":
				$i = get_instance("applications/crm/crm_company_cedit_impl");
				$t = &$data["vcl_inst"];
				$fields = array(
					"name" => t("Arvenumbri nimetus"),
					"account" => t("Arve number"),
					"bank" => t("Pank"),
					"office_code" => t("Kodukontori kood"),
				);
				$i->init_cedit_tables(&$t, $fields);
				$i->_get_acct_tbl($t, $arr);
				$t->table_caption = t("Pangaarved");
				break;
			case "cedit_adr_tbl":
				$i = get_instance("applications/crm/crm_company_cedit_impl");
				$t = &$data["vcl_inst"];
				$fields = array(
					"aadress" => t("T&auml;nav"),
					"postiindeks" => t("Postiindeks"),
					"linn" => t("Linn"),
					"maakond" => t("Maakond"),
					"piirkond" => t("Piirkond"),
					"riik" => t("Riik")
				);
				$i->init_cedit_tables(&$t, $fields);
				$i->_get_adr_tbl($t, $arr);
				$t->table_caption = t("Aadressid");
				break;
			//rel joga
			case "referal_type":
				$c = get_instance("cfg/classificator");
				$data["options"] = array("" => t("--vali--")) + $c->get_options_for(array(
					"name" => "referal_type",
					"clid" => CL_CRM_COMPANY
				));
				break;

			case "bill_due_days":
			case "cust_contract_date":
			case "priority":
			case "bill_penalty_pct":
				// read from rel
				if (($rel = $this->get_cust_rel($arr["obj_inst"])))
				{
					if ($arr["request"]["action"] == "view")
					{
						$data["value"] = $rel->prop_str($data["name"]);
					}
					else
					{
						$data["value"] = $rel->prop($data["name"]);
					}
				}
				if (isset($data["options"]) && !isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$data["value"]] = $tmp->name();
				}
				break;
			case "buyer_priority":
			case "buyer_contract_date":
				if (($rel = $this->get_cust_rel(get_current_company() , false , $arr["obj_inst"])))
				{
					if ($arr["request"]["action"] == "view")
					{
						$data["value"] = $rel->prop_str($data["name"]);
					}
					else
					{
						$data["value"] = $rel->prop($data["name"]);
					}
				}
				if (isset($data["options"]) && !isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$data["value"]] = $tmp->name();
				}

				break;
			case "buyer_contract_creator":
				$this->_get_buyer_contract_creator($arr);
				break;
			case "cust_contract_creator":
				$this->_get_cust_contract_creator($arr);
				break;
			//kliendisuhte teema
			case "buyer_contact_person":
			case "contact_person":
				$data["value"] = html::href(array(
					"url" => html::get_change_url($arr["obj_inst"]->id()),
					"caption" => $arr["obj_inst"]->name(),
				));
				break;

			case "co_is_cust":
			case "bill_penalty_pct":
			case "co_is_buyer":
			//case "buyer_referal_type":
					$fn = "_get_".$data["name"];
					$this->$fn($arr);
				break;
		}
		return $retval;

	}

	function recursive_connections_from($ids, $reltype, $array)
	{
		foreach(connection::find(array("from" => $ids, "type" => $reltype)) as $conn)
		{
			$array[$conn["to"]] = $conn["to.name"];
			$new_ids[$conn["to"]] = $conn["to.name"];
		}
		if(count($new_ids) > 0)
		{
			$this->recursive_connections_from($new_ids, $reltype, &$array);
		}
	}

	function _get_work_tbl($arr)
	{
		$org_fixed = 0;
		$query = $this->parse_url_parse_query($arr["request"]["return_url"]);
		if($query["class"] == "crm_company" && $this->can("view", $query["id"]))
		{
			$org_fixed = $query["id"];
		}

		$org_arr = new object_data_list(
			array(
				"class_id" => CL_CRM_COMPANY,
				"parent" => array()
			),
			array
			(
				CL_CRM_COMPANY => array("oid" => "oid", "name" => "name")
			)
		);
		$orgs = array(0 => t("--vali--"));
		foreach($org_arr->list_data as $lde)
		{
			$orgs[$lde["oid"]] = $lde["name"];
		}

		$sec_arr = new object_data_list(
			array(
				"class_id" => CL_CRM_SECTION,
				"parent" => array()
			),
			array
			(
				CL_CRM_SECTION => array("oid" => "oid", "name" => "name")
			)
		);
		$secs = array(0 => t("--vali--"));
		foreach($sec_arr->list_data as $lde)
		{
			$secs[$lde["oid"]] = $lde["name"];
		}

		$pro_arr = new object_data_list(
			array(
				"class_id" => CL_CRM_PROFESSION,
				"parent" => array()
			),
			array
			(
				CL_CRM_PROFESSION => array("oid" => "oid", "name" => "name")
			)
		);
		$pros = array(0 => t("--vali--"));
		foreach($pro_arr->list_data as $lde)
		{
			$pros[$lde["oid"]] = $lde["name"];
		}

		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "org",
			"caption" => t("Organisatsioon"),
		));
		$t->define_field(array(
			"name" => "sec",
			"caption" => t("Osakond"),
		));
		$t->define_field(array(
			"name" => "pro",
			"caption" => t("Ametinimetus"),
		));
		$relpicker = get_instance("vcl/relpicker");
		foreach($arr["obj_inst"]->connections_from(array("type" => 67)) as $conn)
		{
			$wr = $conn->to();
			$orgid = $wr->prop("org");
			$secid = $wr->prop("section");
			if($orgid != $org_fixed && $org_fixed != 0)
			{
				continue;
			}
			if($this->can("view", $orgid))
			{
				$org_obj = new object($orgid);
				if(!is_array($sec_options[$orgid]))
				{
					$ids = array();
					foreach($org_obj->connections_from(array("type" => 28)) as $org_conn)
					{
						$sec_options[$orgid][$org_conn->prop("to")] = $org_conn->prop("to.name");
						$ids[$org_conn->prop("to")] = $org_conn->prop("to");
					}
					if(count($ids) > 0)
					{
						$this->recursive_connections_from($ids, 1, &$sec_options[$orgid]);
					}
				}
			}
			$pro_options = array();
			if($this->can("view", $secid))
			{
				$sec_obj = obj($secid);
				foreach($sec_obj->connections_from(array("type" => 3)) as $pro_conn)
				{
					$pro_options[$pro_conn->prop("to")] = $pro_conn->prop("to.name");
				}
			}
			elseif(count($sec_options[$orgid]) > 0)
			{
				foreach(connection::find(array("from" => array_flip($sec_options[$orgid]), "type" => 3)) as $pro_conn)
				{
					$pro_options[$pro_conn["to"]] = $pro_conn["to.name"];
				}
			}
			$t->define_data(array(
				"org" => $relpicker->create_relpicker(array(
					"name" => "work_tbl[".$wr->id()."][org]",
					"reltype" => 1,
					"oid" => $wr->id(),
					"property" => "org",
//					"buttonspos" => "bottom",
				)),
				"sec" => $relpicker->create_relpicker(array(
					"name" => "work_tbl[".$wr->id()."][sec]",
					"reltype" => 7,
					"oid" => $wr->id(),
					"property" => "section",
					"options" => $sec_options[$orgid],
//					"buttonspos" => "bottom",
				)),
				"pro" => $relpicker->create_relpicker(array(
					"name" => "work_tbl[".$wr->id()."][pro]",
					"reltype" => 3,
					"oid" => $wr->id(),
					"property" => "profession",
					"options" => $pro_options,
//					"buttonspos" => "bottom",
				)),
			));
		}
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
						"title" => t("Kalender m&auml;&auml;ramata"),
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
						'title' => t('Kalender m&auml;&auml;ramata'),
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
//vot siuke p&auml;ring, &auml;ra k&uuml;si
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

		$al = get_instance("alias_parser");
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
			// &auml;kki teha kuudega? ah? hm?

			// alguses n&auml;itame viimast 10-t
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
			'tooltip' => t('Kustuta valitud t&ouml;&ouml;pakkumised'),
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
			$lang_capt = "<b>".t("Keeled")."</b>";
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
			"tooltip" => t("Lisa uus t&ouml;&ouml;kogemus"),
			"url" => $this->mk_my_orb("new", array(
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 66,
				"return_url" => get_ru(),
				"parent" => $arr["obj_inst"]->parent(),
			), CL_CRM_PERSON_WORK_RELATION),
		));

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta t&ouml;&ouml;kogemused"),
			"action" => "delete_objects",
			"confirm" => t("Oled kindel, et kustutada?"),
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

		$table->define_field(array(
			"name" => "tasks",
			"caption" => t("T&ouml;&ouml;&uuml;lessanded"),
		));

		$table->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
		));

		$table->define_chooser(array(
			"name" => "sel",
			"field" => "from",
		));

		$cs = array();
		if (is_oid($arr["obj_inst"]->id()))
		{
			$cs = $arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PREVIOUS_JOB"));
		}
		foreach ($cs as $conn)
		{
			$prevjob = $conn->to();
			if($prevjob->prop("org"))
			{
					$url = html::href(array(
						"caption" => $prevjob->prop_str("org"),
						"url" => $this->mk_my_orb("change", array(
							"id" => $prevjob->prop("org"),
							"return_url" => get_ru(),
						), CL_CRM_COMPANY),
					));
			}
			$table->define_data(array(
				"asutus" => $prevjob->prop_str("org")?$url:t("-"),
				"alates" => get_lc_date($prevjob->prop("start")),
				"ametikoht" => $prevjob->prop("profession")?$prevjob->prop_str("profession"):t("-"),
				"kuni" => get_lc_date($prevjob->prop("end")),
				"tasks" => $prevjob->prop("tasks"),
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $prevjob->id(),
						"return_url" => get_ru(),
					), CL_CRM_PERSON_WORK_RELATION),
					"caption" => t("Muuda"),
				)),
				"from" => $conn->id(),
			));
		}

	}

	/**
	@attrib name=delete_objects
	**/
	function delete_objects($arr)
	{
		if (!is_array($arr["sel"]) && is_array($arr["check"]))
		{
			$arr["sel"] = $arr["check"];
		}
		if (!is_array($arr["sel"]) && is_array($arr["select"]))
		{
			foreach ($arr["select"] as $oid)
			{
				$obj = obj($oid);
				$obj->delete();
			}
		}
		foreach ($arr["sel"] as $del_conn)
		{
			$conn = new connection($del_conn);
			$obj = $conn->to();
			$obj->delete();
		}
		return  $arr["post_ru"];
	}

	/**
		@attrib name=delete_obj
	**/
	function delete_obj($arr)
	{
		foreach ($arr["sel"] as $o)
		{
			$o = obj($o);
			$o->delete();
		}
		return  $arr["post_ru"];
	}

	function has_current_job_relation($oid)
	{
		$c = new connection();
		$ret = $c->find(array(
			"from" => $oid,
			"type" => 67,
		));
		$c = current($ret);
		return count($ret)?obj($c["to"]):false;
	}

	function callback_post_save($arr)
	{
		/*
		if($arr["obj_inst"]->prop("work_contact"))
		{
			if(!$o = $this->has_current_job_relation($arr["obj_inst"]->id()))
			{
				$o = new object();
				$o->set_parent($arr["obj_inst"]->parent());
				$o->set_class_id(CL_CRM_PERSON_WORK_RELATION);
				$o->set_name($arr["obj_inst"]->prop_str("work_contact"));
				$o->save();
				$arr["obj_inst"]->connect(array(
					"to" => $o->id(),
					"type" => "RELTYPE_CURRENT_JOB",
				));
				$arr["obj_inst"]->save();
			}
			$o->set_prop("org", $arr["obj_inst"]->prop("work_contact"));
			$o->connect(array(
				"to" => $arr["obj_inst"]->prop("work_contact"),
				"type" => "RELTYPE_ORG",
			));
			$o->save();
		}
		else
		{
			if($o = $this->has_current_job_relation($arr["obj_inst"]->id()))
			{
				$o->delete();
			}
		}
		*/

		if (aw_global_get("uid") != "")
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			if ($arr["request"]["group"] == "general2")
			{
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

		// write name and e-mail to the user
		$u = $this->has_user($arr["obj_inst"]);
		if ($u)
		{
			$mod = false;
			if ($u->prop("real_name") != $arr["obj_inst"]->name())
			{
				$u->set_prop("real_name", $arr["obj_inst"]->name());
				$mod = true;
			}
			if ($u->prop("email") != $arr["obj_inst"]->prop_str("email"))
			{
				$u->set_prop("email", $arr["obj_inst"]->prop("email.mail"));
				$mod = true;
			}

			if ($mod)
			{
				aw_disable_acl();
				$u->save();
				aw_restore_acl();
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
		if(is_array($arr["request"]["project_tasks"]) && count($arr["request"]["project_tasks"]))
		{
			$tasks = $this->get_work_project_tasks($arr["obj_inst"]->id());
			foreach($arr["request"]["project_tasks"] as $project => $task)
			{
				$tasks[$project]["task"] = $task;
			}
			$this->set_work_project_tasks($arr["obj_inst"]->id(), $tasks);
		}
		if($arr["request"]["group"] == "work_projects")
		{
			$tasks = $this->get_work_project_tasks($arr["obj_inst"]->id());
			foreach($tasks as $project => $data)
			{
				$tasks[$project]["selected"] = $arr["request"]["project_sel"][$project]?1:0;
			}
			$this->set_work_project_tasks($arr["obj_inst"]->id(), $tasks);
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
				'text'=>t('P&otilde;hiharidus'),
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
				'text'=>t('K&otilde;rgharidus'),
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
				"etype" => "P&otilde;hiharidus",
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
				"etype" => t("K&otilde;rgharidus"),
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
			"caption" => t("R&auml;&auml;kimine"),
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

		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => array(16, 67),
		));

		foreach($conns as $conn)
		{
			$obj = $conn->to();
			if(is_oid($obj->prop('org')))
				$rtrn[$obj->prop('org')] = $obj->prop('org.name');
			if(is_oid($obj->prop('section')))
			{
				$this->_get_work_contacts(obj($obj->prop('section')), &$rtrn);
			}
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

	/** Returns the user object for the given person
		@attrib api=1 params=pos

		@param o required type=object
			Person object to return user for

		@returns
			User object if the person has an user or false if not
	**/
	function has_user($o)
	{
		$prev = obj_set_opt("no_cache", 1);
		$c = new connection();
		$res = $c->find(array(
			"to" => $o->id(),
			"from.class_id" => CL_USER,
			"type" => 2 // CL_USER.RELTYPE_PERSON
		));
		obj_set_opt("no_cache", $prev);

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
		if ("aw_account_balances" == $tbl)
		{
			$i = get_instance(CL_CRM_CATEGORY);
			return $i->do_db_upgrade($tbl, $field);
		}

		switch($field)
		{
			case "aw_bd_up":
				// create the field, but first, convert all persons bds to iso fields
				$this->db_query("SELECT birthday, oid FROM kliendibaas_isik");
				while ($row = $this->db_next())
				{
					$this->save_handle();
					if (is_numeric($row["birthday"]))
					{
						if ($row["birthday"] == -1)
						{
							$bd = "";
						}
						else
						{
							$bd = date("Y-m-d", $row["birthday"]);
						}
						$this->db_query("UPDATE kliendibaas_isik SET birthday = '$bd' WHERE oid = '$row[oid]'");
					}
					$this->restore_handle();
				}
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "int",
				));
				// clear cache
				$c = get_instance("cache");
				$c->file_clear_pt("storage_object_data");
				return true;
				break;

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
			case "cust_contract_creator":
			case "cust_contract_date":
			case "priority":
			case "bill_due_date_days":
			case "bill_penalty_pct":
			case "buyer_contract_person":
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

	function callback_mod_retval($arr)
	{
		$arr["args"]["cv_tpl"] = $arr["request"]["cv_tpl"];
	}

	function callback_mod_reforb($arr)
	{
		$arr["add_to_task"] = $_GET["add_to_task"];
		$arr["add_to_co"] = $_GET["add_to_co"];
		$arr["post_ru"] = post_ru();
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
 		if ($arr["request"]["stats_s_type"] == "rows" || !isset($arr["request"]["stats_s_type"]))
		{
			$this->_get_my_stats_rows($arr);
			return;
		}
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
		@param cv_tpl optional type=int
	**/
	function gen_job_pdf($arr)
	{
		$job = &obj($arr["id"]);
		$pdf_gen = get_instance("core/converters/html2pdf");
		//session_cache_limiter("public");
		$tpl = $arr["request"]["cv_tpl"]?("cv/".basename($v[$arr["request"]["cv_tpl"]])):false;
		die($pdf_gen->gen_pdf(array(
			"filename" => $arr["id"],
			"source" => $this->show_cv(array(
				"id" => $arr["id"],
				"cv" => $tpl,
			))
		)));
	}

	function get_cv_tpl()
	{
		$dir = aw_ini_get("basedir")."/crm/person/cv/";
		$ret = array(
			$dir."cv_clonmel.tpl" => "clonmel",
			$dir."show_cv.tpl" => "default",
		);
		return $ret;
	}

	/**
		@attrib name=show_cv all_args=1 params=name
	**/
	function show_cv($arr)
	{
		if(!$arr["cv"])
		{
			$arr["cv"] = "cv/".basename(key($this->get_cv_tpl()));
		}
		$ob = new object($arr["id"]);
		$person_obj = current($ob->connections_to(/*array("from.class_id" => CL_CRM_PERSON)*/));
		if(!is_object($person_obj))
		{
			return false;
		}
		$person_obj = &obj($person_obj->prop("from"));

		$email_addr = "";
		if ($this->can("view", $person_obj->prop("email")))
		{
			$email_obj = &obj($person_obj->prop("email"));
			$email_addr = $email_obj->prop("email");
		}
		else
		if (is_email($person_obj->prop("email")))
		{
			$email_addr = $person_obj->prop("email");
		}

		$phone_obj = obj();
		if ($this->can("view", $person_obj->prop("phone")))
		{
			$phone_obj = &obj($person_obj->prop("phone"));
		}

		$this->read_template($arr["cv"]);

		if($person_obj->prop("gender") == 1)
		{
			$gender ="Mees";
		}
		else
		{
			$gender ="Naine";
		}

		foreach ($ob->connections_from(array("type" => "RELTYPE_PREVIOUS_JOB")) as $kogemus)
		{
			$kogemus = $kogemus->to();

			$this->vars(array(
				"company" => $kogemus->prop_str("org"),
				"start" => get_lc_date($kogemus->prop("start")),
				"end" => get_lc_date($kogemus->prop("end")),
				"profession" => $kogemus->prop("proffession"),
				"duties" => $kogemus->prop("tasks"),
			));
			$kogemused_temp .= $this->parse("WORK_EXPERIENCES");
		}

		// additional training
		foreach($ob->connections_from(array("type" => "RELTYPE_ADD_EDUCATION")) as $conn)
		{
			$educ = $conn->to();
			$this->vars(array(
				"education_company" => $educ->prop("org"),
				"education_theme" => $educ->prop("field"),
				"education_time" => get_lc_date($educ->prop("time")),
				"education_length" => $educ->prop("length"),
			));
			$add_training .= $this->parse("ADDITIONAL_TRAINING");
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
		$cur_comp = get_current_company();

		$logo = $cur_comp->prop("logo");
		foreach($this->get_work_project_tasks($ob->id()) as $project => $data)
		{
			if(!$data["selected"])
			{
				continue;
			}
			$p = obj($project);
			$this->vars(array(
				"project_start" =>   get_lc_date($p->prop("start")),
				"project_end" => get_lc_date($p->prop("end")),
				"project_contract" => ($_t = $p->name())?$_t:t("-"),
				"project_tasks" => ($_t = $data["task"])?$_t:t("-"),
				"project_value" => ($_t = $p->prop("proj_price"))?$_t:t("-"),
				"project_roles" => ($_t = join(", ", $this->get_project_roles(array(
					"person" => $ob->id(),
					"project" => $project,
				))))?$_t:t("-"),
			));
			$projects .= $this->parse("PROJECT");
		}

		$gidlist = aw_global_get("gidlist_oid");
		$personname = $person_obj->name();
		$cur_job = $this->has_current_job_relation($ob->id());
		$img_inst = get_instance(CL_IMAGE);
		$bd = split("-", $ob->prop("birthday"));
		$bd = mktime(0,0,0,$bd[1],$bd[2], $bd[0]);
		$tio = $cur_job?(time() - $cur_job->prop("start")):false;
		$m = $tio?round(((($tio/60)/60)/24)/30, 0):0;
		$y = $tio?floor((((($tio/60)/60)/24)/30)/12):0;
		$time = $y?sprintf(t("%s %s, %s %s"), $y, $m,(($y==1)?t("year"):t("years")), (($m == 1)?t("month"):t("months"))):sprintf("%s %s", $m ,(($m == 1)?t("month"):t("months")));
		$this->vars(array(
			"COMP_SKILL" => $ck,
			"LANG_SKILL" => $lsk,
			"DRIVE_SKILL" => join(",", $dsk),
			"WORK_EXPERIENCES" => $kogemused_temp,
			"ED" => $ed,
			"PROJECT" => $projects,
			"ADDITIONAL_TRAINING" => $add_training,
			"recommenders" => nl2br($ob->prop("soovitajad")),
			"first_name" => ucfirst(strtolower($ob->prop("firstname"))),
			"last_name" => ucfirst(strtolower($ob->prop("lastname"))),
			"modified" => get_lc_date($ob->modified()),
			"birthday" => date("d.m.Y", $bd),
			"social_status" => $person_obj->prop("social_status"),
			"mail" => html::href(array(
				"url" => "mailto:" . $email_addr,
				"caption" => $email_addr,
			)),
			"phone" => $phone_obj->name(),
			"sectors" => $tmp_sectors,
			"education" => $temp_edu,
			"driving_licenses" => $driving_licenses,
			"addional_info" => $ob->prop("job_addinfo"),
			"gender" => $gender,
			"cur_org_start" => $cur_job?get_lc_date($cur_job->prop("start")):"",
			"cur_org_position" => $ob->prop_str("rank"),
			"cur_org_time" => $time,
			"picture_url" => $img_inst->get_url_by_id($ob->prop("picture")),
			"company_logo" => $this->can("view",$logo)?$img_inst->get_url_by_id($logo):"",
		));
		return $arr["die"]?die($this->parse()):$this->parse();
	}

	function get_project_roles($arr)
	{
		$role_list = new object_list(array(
			"class_id" => CL_CRM_COMPANY_ROLE_ENTRY,
			"person" => $arr["person"],
			"project" => $arr["project"],
		));
		foreach($role_list->arr() as $re)
		{
			if(trim($_t = $re->prop_str("role")))
			{
				$roles[] = $_t;
			}
		}
		return $roles;
	}

	function _init_ext_sys_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Siduss&uuml;steem"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "value",
			"caption" => t("V&auml;&auml;rtus"),
			"align" => "center"
		));
	}

	function _ext_sys_t($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_ext_sys_t($t);

		$cc = get_instance(CL_CRM_COMPANY);
		$crel = $cc->get_cust_rel($arr["obj_inst"], true);

		$data = array();
		foreach($crel->connections_from(array("type" => "RELTYPE_EXT_SYS_ENTRY")) as $c)
		{
			$ent = $c->to();
			$data[$ent->prop("ext_sys_id")] = $ent->prop("value");
		}
		// list all ext systems and let the user edit those
		$ol = new object_list(array(
			"class_id" => CL_EXTERNAL_SYSTEM,
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "objects.jrk"
		));
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"value" => html::textbox(array(
					"name" => "ext[".$o->id()."]",
					"value" => $data[$o->id()],
				))
			));
		}
	}

	function _save_ext_sys_t($arr)
	{
		$cc = get_instance(CL_CRM_COMPANY);
		$crel = $cc->get_cust_rel($arr["obj_inst"], true);
		$ol = new object_list(array(
			"class_id" => CL_EXTERNAL_SYSTEM,
			"lang_id" => array(),
			"site_id" => array()
		));
		$data = array();
		foreach($crel->connections_from(array("type" => "RELTYPE_EXT_SYS_ENTRY")) as $c)
		{
			$ent = $c->to();
			$data[$ent->prop("ext_sys_id")] = $ent->id();
		}
		foreach($ol->arr() as $o)
		{
			if (!isset($data[$o->id()]))
			{
				// create new entry obj
				$ent = obj();
				$ent->set_name(sprintf(t("Siduss&uuml;steemi %s sisestus objektile %s"), $o->name(), $arr["obj_inst"]->name()));
				$ent->set_class_id(CL_EXTERNAL_SYSTEM_ENTRY);
				$ent->set_parent($arr["obj_inst"]->id());
				$ent->set_prop("ext_sys_id", $o->id());
				$ent->set_prop("obj", $arr["obj_inst"]->id());
				$ent->set_prop("value", $arr["request"]["ext"][$o->id()]);
				$ent->save();
				$crel->connect(array(
					"to" => $ent->id(),
					"type" => "RELTYPE_EXT_SYS_ENTRY"
				));
			}
			else
			{
				$ent = obj($data[$o->id()]);
				$ent->set_prop("value", $arr["request"]["ext"][$o->id()]);
				$ent->save();
			}
		}
	}

	function parse_url_parse_query($return_url)
	{
		$url = parse_url($return_url);
		$query = explode("&", $url["query"]);
		foreach($query as $q)
		{
			$t = explode("=", $q);
			$ret[$t[0]] = $t[1];
		}
		return $ret;
	}

	/** returns a line of info about the person - name, company, section, email, phone
		@attrib api=1 params=pos

		@param p required type=oid
			The person to return the info for
	**/
	function get_short_description($arr)
	{
		$org_fixed = 0;
		$query = $this->parse_url_parse_query($arr["request"]["return_url"]);
		if($query["class"] == "crm_company" && $this->can("view", $query["id"]))
		{
			$org_fixed = $query["id"];
		}
		$p = $arr["obj_inst"];
		$p_href = html::href(array(
			'url' => html::get_change_url($p->id()),
			'caption' => $p->name(),
		));

		if (!is_oid($p->id()))
		{
			return;
		}
		$cwrs = array();
		$cou = 0;
		foreach($p->connections_from(array("type" => 67)) as $conn)		// RELTYPE_CURRENT_JOB
		{
			$toid = $conn->conn["to"];
			$to = obj($toid);
			$orgid = $to->prop("org");
			if($orgid != $org_fixed && $org_fixed != 0)
			{
				continue;
			}

			$cwrs[$orgid]["professions"][$cou] = $to->prop("profession");
			foreach($to->connections_from(array("type" => 8)) as $cn)		// RELTYPE_PHONE
			{
				if($this->can("view", $cn->conn["to"]))
					$cwrs[$orgid]["phones"][$cn->conn["to"]] = $cn->conn["to.name"];
			}
			foreach($to->connections_from(array("type" => 9)) as $cn)		// RELTYPE_EMAIL
			{
				if($this->can("view", $cn->conn["to"]))
					$cwrs[$orgid]["emails"][$cn->conn["to"]] = $cn->conn["to.name"];
			}
			foreach($to->connections_from(array("type" => 10)) as $cn)		// RELTYPE_FAX
			{
				if($this->can("view", $cn->conn["to"]))
					$cwrs[$orgid]["faxes"][$cn->conn["to"]] = $cn->conn["to.name"];
			}

			$cou++;
		}
		$ret = "";
//		arr($cwrs);
		foreach($cwrs as $org_id => $data)
		{
			if(strlen($ret) > 0)
			{
				$ret .= "<br>";
			}
			$ret .= $p_href;
			if($this->can("view", $org_id))
			{
				$company = new object($org_id);
				$ret .= " ".html::href(array(
					'url' => html::get_change_url($org_id),
					'caption' => $company->name(),
				));
			}
			else
			{
				$ret .= " <i>ORGANISATSIOON M&Auml;&Auml;RAMATA</i>";
			}
			foreach($data["professions"] as $prof)
			{
				if(!$this->can("view", $prof))
					continue;
				$profession = new object($prof);
				$ret .= ", ".html::href(array(
					'url' => html::get_change_url($prof),
					'caption' => $profession->name(),
				));
			}
			foreach($data["phones"] as $ph_id => $ph)
			{
				$ret .= ", ".html::href(array(
					'url' => html::get_change_url($ph_id),
					'caption' => $ph,
				));
			}
			foreach($data["emails"] as $ml_id => $ml)
			{
				$ml_obj = new object($ml_id);
				$ret .= ", ".html::href(array(
					'url' => html::get_change_url($ml_id),
					'caption' => $ml_obj->prop("mail"),
				));
			}
			if(sizeof($data["faxes"]) > 0)
				$ret .= ", faks ";
			$mtof = false;
			foreach($data["faxes"] as $fx_id => $fx)
			{
				if($mtof)
					$ret .= ",";

				$fx_obj = new object($fx_id);
				$ret .= " ".html::href(array(
					'url' => html::get_change_url($fx_id),
					'caption' => $fx_obj->name(),
				));
				$mtof = true;
			}
		}
		return $ret;
	}

	/*
	function get_short_description($p)		// OLD VERSION OF THIS FUNCTION
	{
		$p = obj($p);
		$ret = html::href(array(
			'url' => html::get_change_url($p->id()),
			'caption' => $p->name(),
		));
		//default company
		if(is_oid($p->prop('work_contact')))
		{
			$company = new object($p->prop('work_contact'));
			$ret .= " ".html::href(array(
				'url' => html::get_change_url($company->id()),
				'caption' => $company->name(),
			));
		}
		//professions...
		$conns2 = $p->connections_from(array(
			'type' => 'RELTYPE_RANK',
		));
		$professions = '';
		foreach($conns2 as $conn2)
		{
			$professions.=', '.$conn2->prop('to.name');
		}
		if(strlen($professions))
		{
			$ret.=$professions;
		}
		//phones
		$conns2 = $p->connections_from(array(
			'type' => 'RELTYPE_PHONE'
		));
		$phones = '';
		foreach($conns2 as $conn2)
		{
			$phones.=', '.$conn2->prop('to.name');
		}
		if(strlen($phones))
		{
			$ret.=$phones;
		}
		$conns2 = $p->connections_from(array(
			'type' => 'RELTYPE_EMAIL',
		));
		$emails = '';
		foreach($conns2 as $conn2)
		{
			$to_obj = $conn2->to();
			$emails.=', '.$to_obj->prop('mail');
		}
		if(strlen($emails))
		{
			$ret.=$emails;
		}

		$conns = $p->connections_from(array(
			"type" => "RELTYPE_BANK_ACCOUNT",
		));
		if(sizeof($conns))
		{
			$aa = array();
			foreach($conns as $c)
			{
				$a = $c->to();
				$aa[] = $a->prop("acct_no");
			}
			$accounts = join($aa, ', ');
			$ret .= ', '.$accounts;
		}
		return $ret;
	}
	/**/

	function _ct_rel_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$confirm_test = t("Kustutada valitud objektid?");

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_objects"
		));
	}

	function _init_my_stats_rows_t(&$t)
	{
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "cust",
			"caption" => t("Klient"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "proj",
			"caption" => t("Projekt"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "task",
			"caption" => t("Toimetus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "content",
			"caption" => t("Rea sisu"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "length",
			"caption" => t("Kestvus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "length_cust",
			"caption" => t("Kestvus Kliendile"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "state",
			"caption" => t("Staatus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center",
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "bill_nr",
			"caption" => t("Arve nr."),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "bill_state",
			"caption" => t("Arve staatus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "check",
			"caption" => "<a href='#' onClick='aw_sel_chb(document.changeform,\"sel\")'>".t("Vali")."</a>",
			"align" => "center",
		));
	}

	function _get_my_stats_rows($arr)
	{
		classload("core/date/date_calc");
		$r = $arr["request"];
		// list all rows for me and the time span
		if(!($r["stats_s_from"]))
		{
			$r["stats_s_from"] = get_month_start();
		}
		else
		{
			$r["stats_s_from"] = date_edit::get_timestamp($r["stats_s_from"]);
		}
		if($r["stats_s_to"])
		{
			$r["stats_s_to"] = time();
		}
		else
		{
			$r["stats_s_to"] = date_edit::get_timestamp($r["stats_s_to"]);
		}
		if ($r["stats_s_time_sel"] != "")
		{
			switch($r["stats_s_time_sel"])
			{
				case "today":
					$r["stats_s_from"] = time() - (date("H")*3600 + date("i")*60 + date("s"));
					$r["stats_s_to"] = time();
					break;

				case "yesterday":
					$r["stats_s_from"] = time() - ((date("H")*3600 + date("i")*60 + date("s")) + 24*3600);
					$r["stats_s_to"] = time() - (date("H")*3600 + date("i")*60 + date("s"));
					break;

				case "cur_week":
					$r["stats_s_from"] = get_week_start();
					$r["stats_s_to"] = time();
					break;

				case "cur_mon":
					$r["stats_s_from"] = get_month_start();
					$r["stats_s_to"] = time();
					break;

				case "last_mon":
					$r["stats_s_from"] = mktime(0,0,0, date("m")-1, 1, date("Y"));
					$r["stats_s_to"] = get_month_start();
					break;
			}
		}

		$p = get_current_person();
		$ol = new object_list(array(
			"class_id" => CL_TASK_ROW,
			"lang_id" => array(),
			"site_id" => array(),
			"impl" => $p->id(),
			"date" => new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $r["stats_s_from"], $r["stats_s_to"]),
		));

		classload("vcl/table");
		$t = new vcl_table;
		$this->_init_my_stats_rows_t($t);

		$row2task = array();
		$c = new connection();
		foreach($c->find(array("to" => $ol->ids(), "from.class_id" => CL_TASK, "type" => "RELTYPE_ROW")) as $c)
		{
			if ($arr["request"]["stats_s_cust"] != "")
			{
				$task = obj($c["from"]);
				if (strpos(mb_strtolower($task->prop("customer.name"), aw_global_get("charset")), mb_strtolower($arr["request"]["stats_s_cust"], aw_global_get("charset"))) === false)
				{
					continue;
				}
			}
			$row2task[$c["to"]] = $c["from"];
		}
//arr($row2task);
		$stat_inst = get_instance("applications/crm/crm_company_stats_impl");
		$task_inst = get_instance("applications/groupware/task");
		$row_inst = get_instance("applications/groupware/task_row");
		$bill_inst = get_instance(CL_CRM_BILL);
		$company_curr = $stat_inst->get_company_currency();
		$bi = get_instance(CL_BUG);

		$l_cus_s = 0;

		foreach($ol->arr() as $o)
		{
			$impl = $check = $bs = $bn = "";
			$agreement = array();
			if ($this->can("view", $o->prop("bill_id")))
			{
				$b = obj($o->prop("bill_id"));
				//$bs = sprintf(t("Arve nr %s"), $b->prop("bill_no"));
				$bn = $b->prop("bill_no");
				$bs = $bill_inst->states[$b->prop("state")];
				$agreement = $b->meta("agreement_price");
			//	$bs = html::obj_change_url($o->prop("bill_id"));
			}
			elseif ($o->prop("on_bill"))
			{
				$bs = t("Arvele");
				$check = html::checkbox(array(
					"name" => "sel[]",
					"value" => $o->id()
				));
			}
			else
			{
				$bs = t("Arve puudub");
			}

			$task = obj($row2task[$o->id()]);
			if (!is_oid($task->id()))
			{
				continue;
			}

			$sum = str_replace(",", ".", $o->prop("time_to_cust"));
			$sum *= str_replace(",", ".", $task->prop("hr_price"));

			//kui on kokkuleppehind kas arvel, voi kui arvet ei ole, siis toimetusel... tuleb vahe arvutada
			if((is_object($b) && sizeof($agreement) && ($agreement[0]["price"] > 0)) || (!is_object($b) && $task->prop("deal_price")))
			{
				$sum = $row_inst->get_row_ageement_price($o);
			}

			//oigesse valuutasse
			$sum = $stat_inst->convert_to_company_currency(array(
				"sum"=>$sum,
				"o"=>$task,
				"company_curr" => $company_curr,
			));

			$t->define_data(array(
				"date" => $o->prop("date"),
				"cust" => html::obj_change_url($task->prop("customer")),
				"proj" => html::obj_change_url($task->prop("project")),
				"task" => html::obj_change_url($task),
				"content" => $bi->_split_long_words($o->prop("content")),
				"length" => number_format($o->prop("time_real"), 2, ',', ''),
				//"length_cust" => number_format($o->prop("time_to_cust"), 2, ',', ''),
				"length_cust" => (!is_oid($bn))?html::textbox(array(
					"name" => "rows[".$o->id()."][time_to_cust]",
					"value" => number_format($o->prop("time_to_cust"), 2, ',', ''),
					"size" => 4,
				)).html::hidden(array(
					"name" => "rows[".$o->id()."][time_to_cust_real]",
					"value" => number_format($o->prop("time_to_cust"), 2, ',', ''),
				)):number_format($o->prop("time_to_cust"), 2, ',', ''),

				"state" => $o->prop("done") ? t("Tehtud") : t("Tegemata"),
				"bill_state" => $bs,
				"check" => $check,
				"sum" => number_format($sum, 2, ',', ''),
				"bill_nr" => $bn,
			));
			$l_sum += $o->prop("time_real");
			$s_sum += $sum;
			$l_cus_s += $o->prop("time_to_cust");
		}

		$t->set_default_sortby("date");
		$t->sort_by();

		$t->define_data(array(
			"content" => t("<b>Summa</b>"),
			"length" => number_format($l_sum, 2, ',', ''),
			"sum" => number_format($s_sum, 2, ',', ''),
			"length_cust" => number_format($l_cus_s, 2, ',', ''),
		));
		$arr["prop"]["value"] = $t->draw();
	}

	function __content_format($val)
	{
		return $val["content_val"];
	}

	/**
		@attrib name=submit_delete_docs
		@param sel optional
		@param post_ru optional
	**/
	function submit_delete_docs($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array(
				"oid" => $arr["sel"]
			));
			$ol->foreach_o(array("func" => "delete"));
		}
		return $arr["post_ru"];
	}

	function _skills_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => html::get_new_url(CL_PERSON_HAS_SKILL, $arr["obj_inst"]->id(), array(
				"return_url" => get_ru(),
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 53
			)),
			"tooltip" => t("Lisa")
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_skills",
			"tooltip" => t("Kustuta p&auml;devused")
		));
	}

	function _init_skills_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("P&auml;devus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Omandatud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y"
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _skills_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_skills_t($t);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_HAS_SKILL")) as $c)
		{
			$o = $c->to();
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"created" => $o->prop("skill_acquired"),
				"oid" => $o->id()
			));
		}
	}

	function _atwork_table($arr)
	{
		classload("core/date/date_calc");
		$m = get_instance("applications/rostering/rostering_model");
		$start = get_week_start();
		$end = get_week_start()+24*7*3600;
		$work_times = $m->get_schedule_for_person($arr["obj_inst"], $start, $end);
		$chart = get_instance("vcl/gantt_chart");
		$chart->configure_chart (array (
			"chart_id" => "person_wh",
			"style" => "aw",
			"start" => $start,
			"end" => $end,
			"width" => 850,
			"row_height" => 10,
		));

		$pl_done = array();
		foreach($work_times as $wt_item)
		{
			if ($pl_done[$wt_item["workplace"]])
			{
				continue;
			}
			$pl_done[$wt_item["workplace"]] = 1;
			$wpl = obj($wt_item["workplace"]);
			$chart->add_row (array (
				"name" => $wpl->id(),
				"title" => $wpl->name(),
				"uri" => html::obj_change_url($wpl)
			));
		}

		static $wtid;
		foreach($work_times as $wt_item)
		{
			$bar = array (
				"id" => ++$wtid,
				"row" => $wt_item["workplace"],
				"start" => $wt_item["start"],
				"length" => $wt_item["end"] - $wt_item["start"],
				"title" => date("d.m.Y H:i", $wt_item["start"])." - ".date("d.m.Y H:i", $wt_item["end"]),
			);

			$chart->add_bar ($bar);
		}

		$i = 0;
		$days = array ("P", "E", "T", "K", "N", "R", "L");
		$columns = 7;
		while ($i < $columns)
		{
			$day_start = (get_day_start() + ($i * 86400));
			$day = date ("w", $day_start);
			$date = date ("j/m/Y", $day_start);
			$uri = aw_url_change_var ("mrp_chart_length", 1);
			$uri = aw_url_change_var ("mrp_chart_start", $day_start, $uri);
			$chart->define_column (array (
				"col" => ($i + 1),
				"title" => $days[$day] . " - " . $date,
				"uri" => $uri,
			));
			$i++;
		}

		$arr["prop"]["value"] = $chart->draw_chart();
	}

	function get_work_project_tasks($oid)
	{
		$o = obj($oid);
		return aw_unserialize($o->prop("work_projects_tasks"));
	}

	function set_work_project_tasks($oid, $tasks)
	{
		$o = obj($oid);
		$o->set_prop("work_projects_tasks", aw_serialize($tasks, SERIALIZE_NATIVE));
		$o->save();
		return true;
	}

	function get_person_and_org_related_projects($person_oid)
	{
		$i = get_instance(CL_USER);
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
			"CL_PROJECT.RELTYPE_PARTICIPANT" => array(
				$person_oid,
				$i->get_company_for_person($person_oid),
			),
		));
		return $ol->arr();
	}

//----------------------- kliendisuhte funktsioonid-----------------------
	function get_cust_rel($o,$crea_if_not_exists,$my_co)
	{
		$co_inst = get_instance(CL_CRM_COMPANY);
		return $co_inst->get_cust_rel($o, $crea_if_not_exists, $my_co);
	}


	function _get_co_is_cust($arr)
	{
		$crel = $this->get_cust_rel($arr["obj_inst"]);
		if ($crel || $arr["request"]["set_as_is_cust"])
		{
			$arr["prop"]["value"] = 1;
		}
	}

	function _set_co_is_cust($arr)
	{
		if ($arr["prop"]["value"] == 1)
		{
			$crel = $this->get_cust_rel($arr["obj_inst"], true);
		}
		else
		{
			$crel = $this->get_cust_rel($arr["obj_inst"]);
			if ($crel)
			{
				$crel->delete();
			}
		}
	}

	function _get_co_is_buyer($arr)
	{
		$cur = get_current_company();
		$crel = $this->get_cust_rel($cur, false, $arr["obj_inst"]);
		if ($crel || $arr["request"]["set_as_is_buyer"])
		{
			$arr["prop"]["value"] = 1;
		}
	}

	function _set_co_is_buyer($arr)
	{
		$cur = get_current_company();
		if ($arr["prop"]["value"] == 1)
		{
			$crel = $this->get_cust_rel($cur, true,$arr["obj_inst"]);
		}
		else
		{
			$crel = $this->get_cust_rel($cur, false, $arr["obj_inst"]);
			if ($crel)
			{
				$crel->delete();
			}
		}
	}

	function set_cust_rel_data($arr)
	{
		if (!$arr["request"]["co_is_cust"])
		{
			return;
		}
		$cur = get_current_company();
		$crel = $this->get_cust_rel($arr["obj_inst"], false, $cur);
		if ($crel)
		{
			$crel->set_prop($arr["prop"]["name"], $arr["prop"]["value"]);
			$crel->save();
		}
	}

	function set_buyer_rel_data($arr)
	{
		if (!$arr["request"]["co_is_buyer"])
		{
			return;
		}
		$cur = get_current_company();
		$crel = $this->get_cust_rel($cur, false, $arr["obj_inst"]);
		if ($crel)
		{
			$crel->set_prop($arr["prop"]["name"], $arr["prop"]["value"]);
			$crel->save();
		}
	}

	function _get_person_tb($arr)
	{
		$tb = &$arr["prop"]["toolbar"];
		$tb->add_menu_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus"),
		));

		$tb->add_menu_item(array(
			'parent'=>'new',
			'text'=>t('Kodakondsus'),
			"tooltip" => t("Lisa uus kodakondsus"),
			"action" => "add_new_citizenship",
			"confirm" => t("Lisan uue kodakondsuse?"),
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta"),
			"action" => "delete_obj",
			"confirm" => t("Oled kindel, et kustutada?"),
		));

	}

	/**
		@attrib name=add_new_citizenship all_args=1
	**/
	function add_new_citizenship($arr)
	{
		$person = obj($arr["id"]);
		$c = new object();
		$c->set_class_id(CL_CITIZENSHIP);
		$c->set_name($person->name()." ".t("kodakondsus"));
		$c->set_parent($person->id());
		if($person->prop("birthday"))
		{
			$c->set_prop("start" , $person->prop("birthday"));
		}
		$c->save();
 		$person->connect(array(
			"to" => $c->id(),
			"reltype"=> "RELTYPE_CITIZENSHIP",
		));
		return $arr["post_ru"];
	}

	function _get_citizenship_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		$t->set_caption(t("Kodakondsused"));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
		$t->define_field(array(
			"name" => "country",
			"caption" => t("Riik"),
		));
		$t->define_field(array(
			"name" => "start",
			"caption" => t("Algus"),
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("L&otilde;pp"),
		));

		$country_options = new object_list(array(
			"class_id" => CL_CRM_COUNTRY,
			"lang_id" => array(),
			"site_id" => array(),
		));

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CITIZENSHIP")) as $conn)
		{
			$c = $conn->to();
			$data = array();
			$data["oid"] = $c->id();
			$data["start"] = html::date_select(array(
				"name" => "citizenship[".$c->id()."][start]",
				"value" => $c->prop("start"),
				"year_from" => 1900,
				"year_to" => date("Y" , time()) + 5,
				"default" => -1
			));
			$data["end"] = html::date_select(array(
				"name" => "citizenship[".$c->id()."][end]",
				"value" => $c->prop("end"),
				"year_from" => 1900,
				"year_to" => date("Y" , time()) + 5,
				"default" => -1
			));
			$data["country"] = html::select(array(
				"name" => "citizenship[".$c->id()."][country]",
				"value" => $c->prop("country"),
				"options" => $country_options->names(),
			));

			$t->define_data($data);
		}
	}



	/**
		@attrib name=c2wr
		@param id required type=int
		@param wrid required type=int
		@param toid required type=int
		@param reltype required type=int
		@param return_url required type=string
	**/
	function c2wr($arr)
	{
		// Isiklik
		if($arr["wrid"] == 0 && is_oid($arr["toid"]))
		{
			$to = new object($arr["toid"]);
			foreach($to->connections_from() as $conn)
			{
				$pwr = $conn->to();
				if($pwr->class_id() == CL_CRM_PERSON_WORK_RELATION)
				{
					$conn->delete(true);
				}
			}
			$o = new object($arr["id"]);
			$o->connect(array(
				"to" => $arr["toid"],
				"reltype" => $arr["reltype"],
			));
			header("Location: ".$arr["return_url"]);
		}
		elseif(is_oid($arr["wrid"]) && is_oid($arr["toid"]))
		{
			$reltypes = array(
				8 => 13,
				9 => 11,
				10 => 54,
			);
			$connect = true;
			$wr = new object($arr["wrid"]);
			$wrc = 0;
			foreach($wr->connections_from(array("type" => $arr["reltype"])) as $conn)
			{
				$wrc++;
				if($conn->conn["to"] == $arr["toid"])
				{
					$conn->delete(true);
					$connect = false;
				}
			}
			if($wrc <= 1)
			{
				$to = new object($arr["toid"]);
				$o = new object($arr["id"]);
				$o->connect(array(
					"to" => $arr["toid"],
					"reltype" => $reltypes[$arr["reltype"]],
				));
			}
			if($connect)
			{
				$wr->connect(array(
					"to" => $arr["toid"],
					"reltype" => $arr["reltype"],
				));
				$o = new object($arr["id"]);
				$o->disconnect(array(
					"from" => $arr["toid"],
					"errors" => false,
				));
			}
			header("Location: ".$arr["return_url"]);
		}
	}

	function _save_citizenship_table($arr)
	{
		foreach($arr["request"]["citizenship"] as $id => $val)
		{
			$c = obj($id);
			foreach($val as $prop => $v)
			{
				if(($prop == "end" || $prop == "start") && !($v["year"] > 0))
				{
					$v = -1;
				}
				$c->set_prop($prop , $v);
			}
			$c->save();
		}
	}

}
?>
