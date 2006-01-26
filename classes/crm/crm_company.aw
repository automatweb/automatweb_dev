<?php
/*
//on_connect_person_to_org handles the connection from person to section too
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_PERSON, on_connect_person_to_org)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_PERSON, on_disconnect_person_from_org)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_CRM_ADDRESS, on_save_address)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_NEW, CL_CRM_ADDRESS, on_save_address)
HANDLE_MESSAGE_WITH_PARAM(MSG_EVENT_ADD, CL_CRM_PERSON, on_add_event_to_person)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_CATEGORY, on_create_customer)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_NEW, CL_CRM_COMPANY, on_create_company)

@classinfo relationmgr=yes syslog_type=ST_CRM_COMPANY no_status=1 r2=yes

@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid

@default table=objects


@default group=general_sub
	@property navtoolbar type=toolbar store=no no_caption=1 group=general_sub editonly=1

	@property name type=textbox size=30 maxlength=255 table=objects
	@caption Organisatsiooni nimi

	@property short_name type=textbox size=10 table=kliendibaas_firma field=aw_short_name
	@caption Nime l&uuml;hend

	@property comment type=textarea cols=65 rows=3 table=objects
	@caption Kommentaar

	@property extern_id type=hidden table=kliendibaas_firma field=extern_id 

	@property reg_nr type=textbox size=10 maxlength=20 table=kliendibaas_firma
	@caption Registri number

	@property ettevotlusvorm type=relpicker table=kliendibaas_firma automatic=1 reltype=RELTYPE_ETTEVOTLUSVORM
	@caption Õiguslik vorm

	@property code type=textbox table=kliendibaas_firma 
	@caption Kood

	@property tax_nr type=textbox table=kliendibaas_firma 
	@caption KMKohuslase nr

	@property cust_contract_date type=date_select table=kliendibaas_firma 
	@caption Kliendisuhte alguskuup&auml;ev

	@property cust_contract_creator type=select table=kliendibaas_firma 
	@caption Kliendisuhte looja

	@property referal_type type=classificator store=connect reltype=RELTYPE_REFERAL_TYPE
	@caption Sissetuleku meetod

	@property logo type=releditor reltype=RELTYPE_ORGANISATION_LOGO use_form=emb rel_id=first method=serialize field=meta table=objects
	@caption Organisatsiooni logo

	@property firmajuht type=chooser orient=vertical table=kliendibaas_firma  editonly=1
	@caption Firmajuht

	@property contact_person type=relpicker table=kliendibaas_firma  editonly=1 reltype
	@caption Kontaktisik 

	@property contact_person2 type=relpicker table=kliendibaas_firma  editonly=1 
	@caption Kontaktisik 2

	@property contact_person3 type=relpicker table=kliendibaas_firma  editonly=1 
	@caption Kontaktisik 3

	@property year_founded type=date_select table=kliendibaas_firma year_from=1800 default=-1
	@caption Asutatud

	@property openhours type=releditor reltype=RELTYPE_OPENHOURS rel_id=first use_form=emb store=no
	@caption Avamisajad

	@property priority type=textbox table=kliendibaas_firma 
	@caption Prioriteet

	@property client_manager type=relpicker reltype=RELTYPE_CLIENT_MANAGER table=kliendibaas_firma field=client_manager
	@caption Kliendihaldur

------ Üldine - Tegevused grupp -----
@default group=org_sections

	@property kaubamargid type=textarea cols=65 rows=3 table=kliendibaas_firma
	@caption Kaubamärgid

	@property tegevuse_kirjeldus type=textarea cols=65 rows=3 table=kliendibaas_firma
	@caption Tegevuse kirjeldus

	@property tooted type=relpicker reltype=RELTYPE_TOOTED automatic=1 method=serialize field=meta table=objects
	@caption Tooted
	
	@property pohitegevus type=popup_search clid=CL_CRM_SECTOR table=kliendibaas_firma style=relpicker reltype=RELTYPE_TEGEVUSALAD
	@caption P&otilde;hitegevus / Tegevusalad

------ Yldine - Lisainfo grupp----------
@default group=add_info

	@property field_manager type=releditor mode=manager reltype=RELTYPE_FIELD props=name,class_name table_fields=name,class_name direct_links=1
	@caption Valdkonnad

	property classif1 type=classificator store=connect reltype=RELTYPE_METAMGR
	caption Asutuse omadused
	
	@property userta1 type=textarea rows=10 cols=50 table=objects field=meta method=serialize
	@caption User-defined TA 1

	@property userta2 type=textarea rows=10 cols=50 table=objects field=meta method=serialize
	@caption User-defined TA 2

	@property userta3 type=textarea rows=10 cols=50 table=objects field=meta method=serialize
	@caption User-defined TA 3

	@property userta4 type=textarea rows=10 cols=50 table=objects field=meta method=serialize
	@caption User-defined TA 4

	@property userta5 type=textarea rows=10 cols=50 table=objects field=meta method=serialize
	@caption User-defined TA 5
	
	@property description_doc type=popup_search clid=CL_DOCUMENT style=relpicker store=no reltype=RELTYPE_DESCRIPTION
	@caption Lisakirjelduse dokument

------ Yldine - kasutajate seaded grupp
@default group=user_settings

	@property do_create_users type=checkbox ch_value=1 table=objects field=meta method=serialize group=user_settings
	@caption Kas isikud on kasutajad

	@property server_folder type=textbox table=objects field=meta method=serialize group=user_settings
	@caption Kataloog serveris, kus asuvad failid

--------------------------------------
@default group=contacts2

	@layout hbox_toolbar type=hbox 

		@property contact_toolbar type=toolbar no_caption=1 store=no parent=hbox_toolbar
		@caption "The Green Button"

	@layout hbox_others type=hbox width=20%:80%

		@layout vbox_contacts_left type=vbox parent=hbox_others 

			@property unit_listing_tree type=treeview no_caption=1 store=no parent=vbox_contacts_left 
			@caption Puu

		@layout vbox_contacts_right type=vbox parent=hbox_others 

			@property human_resources type=table store=no no_caption=1 parent=vbox_contacts_right
			@caption Inimesed

			///////////// contact search
			@property contact_search_firstname type=textbox size=30 store=no parent=vbox_contacts_right
			@caption Eesnimi

			@property contact_search_lastname type=textbox size=30 store=no parent=vbox_contacts_right
			@caption Perenimi

			@property contact_search_code type=textbox size=30 store=no parent=vbox_contacts_right
			@caption Isikukood

			@property contact_search type=hidden store=no no_caption=1 parent=vbox_contacts_right value=1
			@caption contact_search

			@property contact_search_submit type=submit store=no parent=vbox_contacts_right no_caption=1
			@caption Otsi

			@property contacts_search_results type=table store=no no_caption=1 parent=vbox_contacts_right
			@caption Otsingutulemused

			///////////// profession search
			@property prof_search_firstname type=textbox size=30 store=no parent=vbox_contacts_right
			@caption Eesnimi

			@property prof_search_lastname type=textbox size=30 store=no parent=vbox_contacts_right
			@caption Perenimi

			@property prof_search_code type=textbox size=30 store=no parent=vbox_contacts_right
			@caption Isikukood

			@property prof_search type=hidden store=no no_caption=1 parent=vbox_contacts_right value=1
			@caption prof_search

			@property prof_search_submit type=submit store=no parent=vbox_contacts_right no_caption=1
			@caption Otsi

			@property prof_search_results type=table store=no no_caption=1 parent=vbox_contacts_right
			@caption Otsingutulemused



@default group=cedit

	@property contact type=relpicker reltype=RELTYPE_ADDRESS table=kliendibaas_firma
	@caption Vaikimisi aadress

	@property currency type=relpicker reltype=RELTYPE_CURRENCY table=kliendibaas_firma field=aw_currency
	@caption Valuuta

	@property phone_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_PHONE props=name
	@caption Telefon

	@property telefax_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_TELEFAX props=name
	@caption Faks

	@property url_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_URL props=name 
	@caption Veebiaadress

	@property email_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_EMAIL props=mail
	@caption E-posti aadressid

	@property b_acct_desc type=text subtitle=1 store=no
	@caption Pangaarved

	@property bank_account type=releditor mode=manager reltype=RELTYPE_BANK_ACCOUNT table=kliendibaas_firma field=aw_bank_account props=name,acct_no,bank table_fields=name,acct_no,bank
	@caption Pangaarve

@default group=personal_offers
-------------- PERSONALI PROPERTID ---------------

	@layout personal_toolbar type=hbox 
		@property personal_offers_toolbar type=toolbar store=no no_caption=1 parent=personal_toolbar

	@layout personal_tree_table type=hbox  width=20%:80%

		@layout personal_hbox_tree type=vbox parent=personal_tree_table
			@property unit_listing_tree_personal type=treeview no_caption=1 store=no parent=personal_hbox_tree 

		@layout personal_hbox_table type=vbox parent=personal_tree_table
			@property personal_offers_table type=table no_caption=1 parent=personal_hbox_table

@default group=personal_candits

	@layout personal_toolbar_cand type=hbox 
		@property personal_candidates_toolbar type=toolbar store=no no_caption=1 parent=personal_toolbar_cand

	@layout personal_tree_table_cand type=hbox width=20%:80%
		@layout personal_hbox_tree_cand type=vbox parent=personal_tree_table_cand
			@property unit_listing_tree_candidates type=treeview no_caption=1 store=no parent=personal_hbox_tree_cand

		@layout personal_hbox_table_cand type=vbox parent=personal_tree_table_cand
			@property personal_candidates_table type=table no_caption=1 parent=personal_hbox_table_cand



---------------------------------------------------
@default group=edit_sects

	@property sect_tb type=toolbar no_caption=1 store=no

	@property sect_edit type=table no_caption=1 store=no


/////start of my_customers
@default group=relorg_s

	@property my_customers_toolbar type=toolbar no_caption=1 store=no 
	@caption "Klientide toolbar"

	@layout my_cust_bot type=hbox width=20%:80%

		@layout vbox_customers_left type=vbox parent=my_cust_bot

			@property customer_search_name type=textbox size=30 store=no parent=vbox_customers_left captionside=top
			@caption Nimi

			@property customer_search_reg type=textbox size=30 store=no parent=vbox_customers_left captionside=top
			@caption Reg nr.

			@property customer_search_worker type=textbox size=30 store=no parent=vbox_customers_left captionside=top
			@caption T&ouml;&ouml;taja

			@property customer_search_address type=textbox size=30 store=no parent=vbox_customers_left captionside=top
			@caption Aadress

			@property customer_search_city type=textbox size=30 store=no parent=vbox_customers_left captionside=top
			@caption Linn/Vald/Alev

			@property customer_search_county type=textbox size=30 store=no parent=vbox_customers_left captionside=top
			@caption Maakond

			@property customer_search_ev type=textbox size=30 store=no parent=vbox_customers_left captionside=top
			@caption &Otilde;iguslik vorm

			@property customer_search_is_co type=chooser  store=no parent=vbox_customers_left multiple=1 no_caption=1
			@caption Organisatsioon 

			@property customer_search_cust_mgr type=text size=25 store=no parent=vbox_customers_left captionside=top
			@caption Kliendihaldur

			@property customer_search_submit type=submit size=15 store=no parent=vbox_customers_left no_caption=1
			@caption Otsi

		@property my_customers_table type=table store=no no_caption=1 parent=my_cust_bot
		@caption Kliendid


@default group=relorg_t

	@property customer_toolbar type=toolbar no_caption=1 store=no 
	@caption "Klientide toolbar"

	@layout relorg_t_l type=hbox group=relorg_t width=20%:80%

		@property customer_listing_tree type=treeview no_caption=1 parent=relorg_t_l
		@caption Rühmade puu

		@property customer_t type=table store=no no_caption=1 parent=relorg_t_l
		@caption Kliendid
	

/////end of my_customers


---------- ERIPAKKUMISED ---------
@default group=special_offers

	@property special_offers type=releditor reltype=RELTYPE_SPECIAL_OFFERS field=meta method=serialize mode=manager props=name,comment,ord,status,valid_from,valid_to table_fields=name,ord table_edit_fields=ord table=objects direct_links=1 override_parent=this
	@caption Eripakkumised
---------- END ERIPAKKUMISED ---------
	
---------- PILDID ---------
@default group=org_images
	@property images type=releditor reltype=RELTYPE_IMAGE field=meta method=serialize mode=manager props=name,ord,status,file,file2,new_w,new_h,new_w_big,new_h_big,comment,cfgform table_fields=name,ord table_edit_fields=ord table=objects
	@caption Pildid
---------- END PILDID ---------

------------ ORGANISATSIOONI OBJEKTID ---------
default group=org_objects

	layout objects_toolbar type=hbox 
		property objects_listing_toolbar type=toolbar no_caption=1 parent=objects_toolbar 

	layout objects_main type=hbox width=20%:80% 
		layout objects_tree type=vbox parent=objects_main 
			property objects_listing_tree type=treeview no_caption=1 parent=objects_tree 

		layout objects_table type=vbox parent=objects_main 
			property objects_listing_table type=table no_caption=1 parent=objects_table 

---------- END ORGANISATSIOONI OBJEKTID ---------


---------- PROJEKTID ----------------------------
@default group=org_projects_archive

	@property org_proj_tb type=toolbar no_caption=1 group=my_projects
	@property org_proj_arh_tb type=toolbar no_caption=1 group=org_projects_archive

	@layout projects_main type=hbox width=20%:80% 
		@layout projects_tree type=vbox parent=projects_main 
			@property projects_listing_tree type=treeview no_caption=1 parent=projects_tree no_caption=1

			@layout all_proj_search_b type=vbox parent=projects_tree

				@layout all_proj_search_b_top type=vbox parent=all_proj_search_b

					@property all_proj_search_cust type=textbox store=no parent=all_proj_search_b_top size=33 captionside=top
					@caption Klient

					@property all_proj_search_part type=text size=28 parent=all_proj_search_b_top store=no captionside=top
					@caption Osaleja

					@property all_proj_search_name type=textbox store=no parent=all_proj_search_b_top size=33 captionside=top
					@caption Projekti nimi

					@property all_proj_search_code type=textbox store=no parent=all_proj_search_b_top size=33 captionside=top
					@caption Projekti kood

					@property all_proj_search_contact_person type=textbox store=no parent=all_proj_search_b_top size=33 captionside=top
					@caption Projekti kontaktisik

					@property all_proj_search_task_name type=textbox store=no parent=all_proj_search_b_top size=33 captionside=top
					@caption &Uuml;lesande nimi

				@layout all_proj_search_b_dl type=vbox parent=all_proj_search_b

					@property all_proj_search_dl_from type=date_select store=no parent=all_proj_search_b_dl  captionside=top format=day_textbox,month_textbox,year_textbox 
					@caption T&auml;htaeg alates

					@property all_proj_search_dl_to type=date_select store=no parent=all_proj_search_b_dl  captionside=top format=day_textbox,month_textbox,year_textbox 
					@caption T&auml;htaeg kuni

				@layout all_proj_search_b_end type=vbox parent=all_proj_search_b

					@property all_proj_search_end_from type=date_select store=no parent=all_proj_search_b_end  captionside=top format=day_textbox,month_textbox,year_textbox 
					@caption L&otilde;pp alates

					@property all_proj_search_end_to type=date_select store=no parent=all_proj_search_b_end  captionside=top format=day_textbox,month_textbox,year_textbox 
					@caption L&otilde;pp kuni

					@property all_proj_search_state type=select store=no parent=all_proj_search_b  captionside=top
					@caption Staatus

			@layout all_proj_search_but_row type=hbox parent=projects_tree 

				@property all_proj_search_sbt type=submit  parent=all_proj_search_but_row no_caption=1 
				@caption Otsi

				@property all_proj_search_clear type=submit  parent=all_proj_search_but_row no_caption=1
				@caption T&uuml;hista otsing

		@layout projects_table type=vbox parent=projects_main 
			@property projects_listing_table type=table no_caption=1 parent=projects_table no_caption=1


@default group=my_projects

	@layout my_proj type=hbox width=20%:80%

		@layout my_proj_search type=vbox parent=my_proj

			@layout my_proj_search_b type=vbox parent=my_proj_search

				@layout my_proj_search_b_top type=vbox parent=my_proj_search_b

					@property proj_search_cust type=textbox store=no parent=my_proj_search_b_top size=33 captionside=top
					@caption Klient

					@property proj_search_part type=text size=28 parent=my_proj_search_b_top store=no captionside=top
					@caption Osaleja

					@property proj_search_name type=textbox store=no parent=my_proj_search_b_top size=33 captionside=top
					@caption Projekti nimi

					@property proj_search_code type=textbox store=no parent=my_proj_search_b_top size=33 captionside=top
					@caption Projekti kood

					@property proj_search_contact_person type=textbox store=no parent=my_proj_search_b_top size=33 captionside=top
					@caption Projekti kontaktisik

					@property proj_search_task_name type=textbox store=no parent=my_proj_search_b_top size=33 captionside=top
					@caption &Uuml;lesande nimi

				@layout my_proj_search_b_dl type=vbox parent=my_proj_search_b

					@property proj_search_dl_from type=date_select store=no parent=my_proj_search_b_dl  captionside=top format=day_textbox,month_textbox,year_textbox 
					@caption T&auml;htaeg alates

					@property proj_search_dl_to type=date_select store=no parent=my_proj_search_b_dl  captionside=top format=day_textbox,month_textbox,year_textbox 
					@caption T&auml;htaeg kuni

			@property proj_search_state type=select store=no parent=my_proj_search_b  captionside=top
			@caption Staatus

			@layout my_proj_search_but_row type=hbox parent=my_proj_search 

				@property proj_search_sbt type=submit  parent=my_proj_search_but_row no_caption=1 
				@caption Otsi

				@property proj_search_clear type=submit  parent=my_proj_search_but_row no_caption=1
				@caption T&uuml;hista otsing

		@property my_projects type=table no_caption=1 store=no parent=my_proj 

@default group=my_reports,all_reports

	@property report_list type=table store=no no_caption=1
	@caption P&auml;eva raportid

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

@default group=documents_news

	@property docs_news_tb type=toolbar no_caption=1

	@layout docs_news_lt type=hbox width=20%:80%

		@layout docs_news_left type=vbox parent=docs_news_lt 
	
			@property dn_s_name type=textbox size=30 store=no captionside=top parent=docs_news_left
			@caption Nimi

			@property dn_s_lead type=textbox size=30 store=no captionside=top parent=docs_news_left
			@caption Lead

			@property dn_s_content type=textbox size=30 store=no captionside=top parent=docs_news_left
			@caption Sisu

			@property dn_s_sbt type=submit size=30 store=no captionside=top parent=docs_news_left no_caption=1
			@caption Otsi

		@property dn_res type=table no_caption=1 store=no parent=docs_news_lt

@default group=documents_lmod

	@property documents_lmod type=table store=no no_caption=1

@default group=bills_create

	@property bill_tb type=toolbar store=no no_caption=1

	@property bill_proj_list type=table store=no no_caption=1
	@property bill_task_list type=table store=no no_caption=1

@default group=bills_monthly

	@property bills_mon_tb type=toolbar no_caption=1 store=no

@default group=bills_search

	@property bs_tb type=toolbar no_caption=1

@default group=bills_list

	@property bills_tb type=toolbar no_caption=1 store=no

	@layout bills_list_box type=hbox width=20%:80%

		@layout bills_list_s type=vbox parent=bills_list_box

			otsing Kliendi, arve nr,  esitamise ajavahemiku, 
			kliendihalduri, koostamisel/makstud/maksmata järgi


			@property bill_s_cust type=textbox size=30 store=no parent=bills_list_s captionside=top group=bills_list
			@caption Klient

			@property bill_s_bill_no type=textbox size=30 store=no parent=bills_list_s captionside=top group=bills_list
			@caption Arve nr

			@property bill_s_from type=date_select store=no parent=bills_list_s captionside=top group=bills_list
			@caption Esitatud alates

			@property bill_s_to type=date_select store=no parent=bills_list_s captionside=top group=bills_list
			@caption Esitatud kuni

			@property bill_s_client_mgr type=text store=no parent=bills_list_s captionside=top group=bills_list
			@caption Kliendihaldur

			@property bill_s_status type=select store=no parent=bills_list_s captionside=top group=bills_list
			@caption Staatus

			@property bill_s_search type=submit store=no parent=bills_list_s captionside=top no_caption=1 group=bills_list
			@caption Otsi

		@property bills_list type=table store=no no_caption=1 parent=bills_list_box group=bills_list,bills_monthly

	

@default group=my_tasks,meetings,calls,ovrv_offers,all_actions

	@property my_tasks_tb type=toolbar store=no no_caption=1

	@layout my_tasks type=hbox width=20%:80%

		@layout all_act_search type=vbox parent=my_tasks

			@layout act_s_dl_layout_top type=vbox parent=all_act_search

			@property act_s_cust type=textbox size=33 parent=act_s_dl_layout_top store=no captionside=top group=my_tasks,meetings,calls,ovrv_offers,all_actions,bills_search
			@caption Klient

			@property act_s_part type=text size=30 parent=act_s_dl_layout_top store=no captionside=top group=my_tasks,meetings,calls,ovrv_offers,all_actions,bills_search
			@caption Osaleja

			@property act_s_task_name type=textbox size=33 parent=act_s_dl_layout_top store=no captionside=top
			@caption Tegevuse nimi

			@property act_s_task_content type=textbox size=33 parent=act_s_dl_layout_top store=no captionside=top
			@caption Tegevuse sisu

			@property act_s_code type=textbox size=33 parent=act_s_dl_layout_top store=no captionside=top
			@caption Toimetuse kood

			@property act_s_proj_name type=textbox size=33 parent=all_act_search store=no captionside=top group=my_tasks,meetings,calls,ovrv_offers,all_actions,bills_search
			@caption Projekti nimi

			@layout act_s_dl_layout type=vbox parent=all_act_search

				@property act_s_dl_from type=date_select store=no parent=act_s_dl_layout captionside=top format=day_textbox,month_textbox,year_textbox group=my_tasks,meetings,calls,ovrv_offers,all_actions,bills_search
				@caption T&auml;htaeg alates

				@property act_s_dl_to type=date_select store=no parent=act_s_dl_layout captionside=top format=day_textbox,month_textbox,year_textbox group=my_tasks,meetings,calls,ovrv_offers,all_actions,bills_search
				@caption T&auml;htaeg kuni

			@property act_s_status type=select parent=all_act_search store=no captionside=top
			@caption Staatus

			@property act_s_print_view type=checkbox parent=all_act_search store=no captionside=top ch_value=1 no_caption=1
			@caption Printvaade

			@property act_s_sbt type=submit  parent=all_act_search no_caption=1 group=my_tasks,meetings,calls,ovrv_offers,all_actions,bills_search
			@caption Otsi

		@property my_tasks type=table store=no no_caption=1 parent=my_tasks group=my_tasks,meetings,calls,ovrv_offers,all_actions,bills_search
		@property my_tasks_cal type=calendar store=no no_caption=1 parent=my_tasks

@default group=stats_s

	@property stats_s_toolbar type=toolbar store=no no_caption=1

	@property stats_s_cust type=textbox store=no
	@caption Klient

	@property stats_s_cust_type type=chooser store=no
	@caption Kliendi t&uuml;&uuml;p

	@property stats_s_proj type=textbox store=no
	@caption Projekt

	@property stats_s_worker type=textbox store=no
	@caption T&ouml;&ouml;taja

	@property stats_s_worker_sel type=select multiple=1 store=no
	@caption T&ouml;&ouml;taja

	@property stats_s_from type=date_select store=no
	@caption Alates

	@property stats_s_to type=date_select store=no
	@caption Kuni

	@property stats_s_time_sel type=select store=no
	@caption Ajavahemik

	@property stats_s_state type=select store=no
	@caption Toimetuse staatus

	@property stats_s_bill_state type=select store=no
	@caption Arve staatus

	@property stats_s_only_billable type=checkbox ch_value=1 store=no
	@caption Arvele minevad tunnid ainult

	@property stats_s_area type=select store=no
	@caption Valdkond

	@property stats_s_res_type type=select store=no
	@caption Tulemused

	@property stats_s_sbt type=submit store=no
	@caption Otsi

	@property stats_s_res type=table store=no no_caption=1
	@caption Tulemused

@default group=stats_view

	@property stats_tb type=toolbar no_caption=1 store=no

	@property stats_list type=table no_caption=1 store=no

@default group=quick_view

	@property qv_cust_inf type=text store=no no_caption=1
	@property qv_t type=table store=no no_caption=1

@default group=resources

	@property res_tb type=toolbar no_caption=1

	@layout res_lt type=hbox width=20%:80%

		@property res_tree type=treeview no_caption=1 parent=res_lt

		@property res_tbl type=table no_caption=1 parent=res_lt

@default group=transl
	
	@property transl type=callback callback=callback_get_transl store=no
	@caption T&otilde;lgi

@default group=documents_forum

	@property forum type=text store=no no_caption=1
	@caption Foorumi sisu

ype=callback callback=callback_gen_forum store=no no_caption=1

-------------------------------------------------
@groupinfo general_sub caption="&Uuml;ldine" parent=general
@groupinfo cedit caption="Üldkontaktid" parent=general
@groupinfo org_sections caption="Tegevus" parent=general
@groupinfo add_info caption="Lisainfo" parent=general
@groupinfo user_settings caption="Seaded" parent=general
@groupinfo special_offers caption="Eripakkumised" submit=no parent=general
@groupinfo people caption="T&ouml;&ouml;tajad"

	@groupinfo contacts2 caption="Inimesed puuvaates" parent=people submit=no
	@groupinfo personal_offers caption="Tööpakkumised" parent=people submit=no
	@groupinfo personal_candits caption="Kandideerijad" parent=people submit=no
	@groupinfo edit_sects caption="Muuda &uuml;ksuseid" parent=people submit=no

@groupinfo resources caption="Ressursid"  submit=no
@groupinfo contacts caption="Kontaktid"
@groupinfo overview caption="Tegevused" 

	@groupinfo all_actions caption="Kõik" parent=overview submit=no
	@groupinfo my_tasks caption="Toimetused" parent=overview submit=no
	@groupinfo meetings caption="Kohtumised" parent=overview submit=no
	@groupinfo calls caption="Kõned" parent=overview submit=no
	@groupinfo ovrv_offers caption="Dokumendihaldus" parent=overview submit=no

@groupinfo projs caption="Projektid"
	@groupinfo my_projects caption="Projektid" parent=projs submit=no
	groupinfo org_projects caption="Projektid" submit=no parent=projs
	@groupinfo org_projects_archive caption="Projektide arhiiv" submit=no parent=projs
	@groupinfo my_reports caption="Minu raportid" submit=no parent=projs
	@groupinfo all_reports caption="K&otilde;ik raportid" submit=no parent=projs

@groupinfo relorg caption="Kliendid" focus=customer_search_name
	@groupinfo relorg_s caption="Otsing" focus=customer_search_name parent=relorg submit=no
	@groupinfo relorg_t caption="Puuvaade" parent=relorg submit=no

groupinfo org_objects_main caption="Objektid" submit=no

	groupinfo org_objects caption="Objektid" submit=no parent=org_objects_main


@groupinfo org_images caption="Pildid" submit=yes parent=general

groupinfo documents caption="Dokumendid" submit=no

	@groupinfo documents_all caption="Dokumendid" submit=no parent=general
	@groupinfo documents_news caption="Uudised" submit=no parent=general submit_method=get
	@groupinfo documents_forum caption="Foorum" submit=no parent=general 
	@groupinfo documents_lmod caption="Viimati muudetud" submit=no parent=general	

@groupinfo bills caption="Arved" submit=no

	@groupinfo bills_list parent=bills caption="Nimekiri" submit=no
	@groupinfo bills_monthly parent=bills caption="Kuuarved" submit=no
	@groupinfo bills_search parent=bills caption="Otsi toimetusi" submit=no
	@groupinfo bills_create parent=bills caption="Loo arve" submit=no

@groupinfo stats caption="Aruanded" 

	@groupinfo stats_s parent=stats caption="Otsi" submit_method=get
	@groupinfo stats_view parent=stats caption="Vaata" submit=no

@groupinfo quick_view caption="Vaata"  submit=no

@groupinfo transl caption=T&otilde;lgi

@reltype ETTEVOTLUSVORM value=1 clid=CL_CRM_CORPFORM
@caption Õiguslik vorm

@reltype ADDRESS value=3 clid=CL_CRM_ADDRESS
@caption Kontaktaadress

@reltype TEGEVUSALAD value=5 clid=CL_CRM_SECTOR
@caption Tegevusalad

@reltype TOOTED value=6 clid=CL_CRM_PRODUCT
@caption Tooted

@reltype CHILD_ORG value=7 clid=CL_CRM_COMPANY
@caption Tütar-organisatsioonid

@reltype WORKERS value=8 clid=CL_CRM_PERSON
@caption Töötajad

@reltype OFFER value=9 clid=CL_CRM_OFFER
@caption Pakkumine

@reltype DEAL value=10 clid=CL_CRM_DEAL
@caption Tehing

@reltype KOHTUMINE value=11 clid=CL_CRM_MEETING
@caption Kohtumine

@reltype CALL value=12 clid=CL_CRM_CALL
@caption Kõne

@reltype TASK value=13 clid=CL_TASK
@caption Toimetus

@reltype EMAIL value=15 clid=CL_ML_MEMBER
@caption E-post

@reltype URL value=16 clid=CL_EXTLINK
@caption Veebiaadress

@reltype PHONE value=17 clid=CL_CRM_PHONE
@caption Telefon

@reltype TELEFAX value=18 clid=CL_CRM_PHONE
@caption Fax

@reltype JOBS value=19 clid=CL_PERSONNEL_MANAGEMENT_JOB_OFFER
@caption T&ouml;&ouml;pakkumine

@reltype TOOPAKKUJA value=20 clid=CL_CRM_COMPANY
@caption Tööpakkuja

@reltype TOOTSIJA value=21 clid=CL_CRM_PERSON
@caption Tööotsija

@reltype CUSTOMER value=22 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype POTENTIONAL_CUSTOMER value=23 clid=CL_CRM_COMPANY
@caption Tulevane klient

@reltype PARTNER value=24 clid=CL_CRM_COMPANY
@caption Partner

@reltype POTENTIONAL_PARTNER value=25 clid=CL_CRM_COMPANY
@caption Tulevane partner

@reltype COMPETITOR value=26 clid=CL_CRM_COMPANY
@caption Konkurent

@reltype ORDER value=27 clid=CL_SHOP_ORDER
@caption tellimus

@reltype SECTION value=28 clid=CL_CRM_SECTION
@caption Üksus

@reltype PROFESSIONS value=29 clid=CL_CRM_PROFESSION
@caption Võimalikud ametid

@reltype CATEGORY value=30 clid=CL_CRM_CATEGORY
@caption Kategooria

@reltype MAINTAINER value=31 clid=CL_CRM_PERSON
@caption Persoon, kellele firma on klient

@reltype SELLER value=32 clid=CL_CRM_PERSON
@caption Persoon, kes müüs

@reltype PROJECT value=33 clid=CL_PROJECT
@caption Projekt

@reltype CLIENT_MANAGER value=34 clid=CL_CRM_PERSON
@caption Kliendihaldur

@reltype SECTION_WEBSIDE value=35 clid=CL_CRM_MANAGER
@caption Üksus veebis

@reltype GROUP value=36 clid=CL_GROUP
@caption Organisatsiooni grupp

@reltype CONTRACT value=37 clid=CL_FILE
@caption Leping

@reltype OFFER_FILE value=38 clid=CL_FILE
@caption Pakkumise fail

@reltype DAY_REPORT value=39 clid=CL_CRM_DAY_REPORT
@caption p&auml;eva raport

@reltype DOCS_FOLDER value=40 clid=CL_MENU
@caption dokumentide kataloog
		
@reltype REFERAL_TYPE value=41 clid=CL_META
@caption sissetuleku meetod

@reltype IMAGE value=42 clid=CL_IMAGE
@caption Pilt

@reltype SPECIAL_OFFERS value=43 clid=CL_CRM_SPECIAL_OFFER
@caption Eripakkumine

@reltype OPENHOURS value=44 clid=CL_OPENHOURS
@caption Avamisajad

@reltype ORGANISATION_LOGO value=45 clid=CL_IMAGE
@caption Organisatsiooni logo

@reltype BANK_ACCOUNT value=46 clid=CL_CRM_BANK_ACCOUNT
@caption arveldusarve

@reltype CURRENCY value=47 clid=CL_CURRENCY
@caption valuuta

@reltype CONTENT_DOCS_FOLDER value=48 clid=CL_DOCUMENT
@caption uudiste kataloog

@reltype METAMGR value=49 clid=CL_METAMGR
@caption Muutujate haldur

@reltype FIELD value=50 clid=CL_CRM_FIELD_ACCOMMODATION,CL_CRM_FIELD_FOOD,CL_CRM_FIELD_ENTERTAINMENT,CL_CRM_FIELD_CONFERENCE_ROOM
@caption Valdkond

@reltype SERVER_FILES value=51 clid=CL_SERVER_FOLDER
@caption failide kataloog serveris

@reltype RESOURCES_FOLDER value=52 clid=CL_MENU
@caption ressursside kataloog

@reltype RESOURCE_MGR value=53 clid=CL_MRP_WORKSPACE
@caption ressursihalduskeskkond

@reltype CONTACT_PERSON value=54 clid=CL_CRM_PERSON
@caption Kontaktisik

@reltype WAREHOUSE value=55 clid=CL_SHOP_WAREHOUSE
@caption Ladu

@reltype DESCRIPTION value=56 clid=CL_DOCUMENT
@caption Lisakirjelduse dokument

@reltype NUMBER_SERIES value=57 clid=CL_CRM_NUMBER_SERIES
@caption Numbriseeria

@reltype FORUM value=58 clid=CL_FORUM_V2
@caption Foorum

*/
/*
CREATE TABLE `kliendibaas_firma` (
  `oid` int(11) NOT NULL default '0',
  `firma_nim` varchar(255) default NULL,
  `reg_nr` varchar(20) default NULL,
  `ettevotlusvorm` int(11) default NULL,
  `pohitegevus` int(11) default NULL,
  `tegevuse_kirjeldus` text,
  `contact` int(11) default NULL,
  `firmajuht` int(11) default NULL,
  `korvaltegevused` text,
  `kaubamargid` text,
  `tooted` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`),
  KEY `teg_i` (`pohitegevus`)
) TYPE=MyISAM;
*/


define("CRM_TASK_VIEW_TABLE", 0);
define("CRM_TASK_VIEW_CAL", 1);

class crm_company extends class_base
{
	var $unit = 0;
	var $category = 0;
	var $active_node = 0;
	var $users_person = null;

	//bad name, it is in the meaning of
	//show_contacts_search
	var $do_search = 0;

	function crm_company()
	{
		$this->init(array(
			'clid' => CL_CRM_COMPANY,
			'tpldir' => 'crm/crm_company',
		));

		$this->trans_props = array(
			"tegevuse_kirjeldus", "userta1", "userta2", "userta3", "userta4", "userta5"
		);
	}

	function crm_company_init()
	{
		$us = get_instance(CL_USER);
		$this->users_person = new object($us->get_current_person());
	}

	/*
		arr[]
			tree_inst -> the treeview object
			obj_inst -> the root object
			conn_type -> what type of connections are allowed
			skip -> a type can have many "to" object types, if any of them
						should be skipped, then $skip does the trick
			attrib -> the node link can have some extra attributes
			leafs -> if leafs should be shown (not exactly what the description implies)
			style -> css style added to the node - sound funny - yeah, it is
	*/
	//function generate_tree($tree, $obj,$node_id,$type1,$skip, $attrib, $leafs, $style=false)
	function generate_tree($arr)
	{
		//all connections from the currrent object
		//different reltypes
		extract($arr);
		$tree = &$arr['tree_inst'];
		$obj = &$arr['obj_inst'];
		$node_id = &$arr['node_id'];
		$attrib = &$arr['attrib'];
		$tmp_type = $conn_type;

		if(sizeof($arr['skip']))
		{
			$skip = &$arr['skip'];
		}
		else
		{
			$skip = array();
		}
		if($conn_type != "RELTYPE_CATEGORY")
		{
			$conn_type = "RELTYPE_SECTION";
		}
		$conns = $obj->connections_from(array(
			'type' => $conn_type,
			'sort_by' => 'to.jrk',
			'sort_dir' => 'asc',
		));
		
		//parent nodes'id actually
		$this_level_id = $node_id;
		foreach($conns as $key=>$conn)
		{
			//$skip in action
			if(in_array($conn->prop('type'),$skip))
			{
				continue;
			}
			//iga alam item saab ühe võrra suurema väärtuse
			//if the 'to.id' eq active_node then it should be bold
			$name = $conn->prop('to.name');
			if($style)
			{
				$name = '<span class=&quot;'.$style.'&quot;>'.$name.'</span>';
			}
			if($conn->prop('to')==$this->active_node)
			{
				$name='<b>'.$name.'</b>';
			}
			$tmp_obj = $conn->to();
			
			//use the plural unless plural is empty -- this is just for reltype_section
			if ($this->tree_uses_oid)
			{
				$node_id = $conn->prop("to");
			}
			else
			{
				++$node_id;
			}
			$tree_node_info = array(
				'id'=>$node_id,
				'name'=>$name,
				'url'=>aw_url_change_var(array(
					$attrib=>$conn->prop('to'),
					'cat'=>'',
					'org_id' => '',
				)),
				'oid' => $conn->prop('to'),
				"class_id" => $conn->prop("to.class_id"),
			);
			//i know, i know, this function is getting really bloated
			//i just don't know yet, how to refactor it nicely, until then
			//i'll be just adding the bloat
			//get all the company for the current leaf
			$blah = $conn->to();
			$conns_tmp = $blah->connections_from(array(
				"type" => "RELTYPE_CUSTOMER",
			));
			$oids = array();
			foreach($conns_tmp as $conn_tmp)
			{
				$oids[$conn_tmp->prop('to')] = $conn_tmp->prop('to');
			}
			$tree_node_info['oid'] = $oids;
			//let's find the picture for this obj
			$img_conns = $tmp_obj->connections_from(array("type" => "RELTYPE_IMAGE"));
			//uuuuu, we have a pic
			if(is_object(current($img_conns)))
			{
				//icon url
				$img = current($img_conns);
				$img_inst = get_instance(CL_IMAGE);
				$tree_node_info['iconurl'] = $img_inst->get_url_by_id($img->prop('to'));
			}

			$tli = $this_level_id;

			$tree->add_item($tli,$tree_node_info);
			//$this->generate_tree(&$tree,&$tmp_obj,&$node_id,$tmp_type,&$skip, &$attrib, $leafs);
			$this->generate_tree(array(
						'tree_inst' => &$tree,
						'obj_inst' => &$tmp_obj,
						'node_id' => &$node_id,
						'conn_type' => $tmp_type,
						'skip' => &$skip,
						'attrib' => &$attrib,
						'leafs' => $leafs,
			));
		}
		//if leafs
		if($leafs)
		{
			if(is_callable(array($this, $leafs)))
			{
				$this->$leafs(&$tree,&$obj,$this_level_id,&$node_id);
			}
			else
			{
				$this->tree_node_items(&$tree,&$obj,$this_level_id,&$node_id);
			}
		}
	}

	//hardcoded
	function tree_node_items($tree,$obj,$this_level_id,$node_id)
	{
		//getting the list of professions for the current
		//unit/organization
		$prof_connections = $obj->connections_from(array(
			"type"=> "RELTYPE_PROFESSIONS",
		));

		$key = 'unit';
		$value = '';
		if($obj->prop("class_id") == CL_CRM_SECTION)
		{
			$value = $obj->id();
		}

		foreach($prof_connections as $prof_conn)
		{
			$tmp_obj = new object($prof_conn->to());
			$name = strlen($tmp_obj->prop('name_in_plural'))?$tmp_obj->prop('name_in_plural'):$tmp_obj->prop('name');
			
			if($tmp_obj->id()==$this->active_node && ($_GET["unit"] == $obj->id()))
			{
				$name = '<b>'.$name.'</b>';
			}
			
			$url = array();
			$url = aw_url_change_var(array('cat'=>$prof_conn->prop('to'),$key=>$value));
			$tree->add_item($this_level_id,
				array(
					'id' => ++$node_id,
					'name' => $name,
					'iconurl' =>' images/scl.gif',
					'url'=>$url,
					"class_id" => $tmp_obj->class_id()
				)
			);
		}	
	}
	
	function get_property($arr)
	{
		$data = &$arr['prop'];
		$retval = PROP_OK;
	
		switch($data['name'])
		{
			/// GENERAL TAB
			case "forum":
				$forum = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_FORUM");
				if (!$forum)
				{
					$o = obj();
					$o->set_class_id(CL_FORUM_V2);
					$o->set_parent($arr["obj_inst"]->id());
					$o->set_name(sprintf(t("%s foorum"), $arr["obj_inst"]->name()));
					$o->save();
					$arr["obj_inst"]->connect(array(
						"to" => $o->id(),
						"type" => "RELTYPE_FORUM"
					));

					$fi = $o->instance();
					$fi->callback_post_save(array(
						"obj_inst" => $o,
						"request" => array("new" => 1)
					));
					$forum = $o;
				}

				$i = $forum->instance();
				$i->obj_inst = $forum;
				$data["value"] = $i->draw_all_folders(array(
					"obj_inst" => $forum,
					"request" => $arr["request"]
				));
				break;

			case "name":
				$data["autocomplete_source"] = "/automatweb/orb.aw?class=crm_company&action=name_autocomplete_source";
				$data["autocomplete_params"] = array("name");
				//$data["option_is_tuple"] = true;
				break;

			case "reg_nr":
				// append link to go to thingie
				$data["post_append_text"] = "<a href='#' onClick='win = window.open(); win.document.write(\"<form action=https://info.eer.ee/ari/ariweb_package1.lihtparingu_vastus METHOD=POST name=kraaks><INPUT TYPE=text NAME=paritud_arinimi><INPUT TYPE=text NAME=paritud_arir_kood><input type=submit></form>\" );win.document.kraaks.paritud_arinimi.value = document.changeform.name.value;win.document.kraaks.paritud_arir_kood.value = document.changeform.reg_nr.value;win.document.kraaks.submit();'>&Auml;riregistri p&auml;ring</a>";
				break;

			case "tax_nr":
				// append link to go to thingie
				$data["post_append_text"] = "<a href='#' onClick='win = window.open(); win.document.write(\"<form action=https://maksuamet.ma.ee/e-service/doc/a0003.xsql METHOD=POST name=kraaks><INPUT TYPE=text NAME=p_kkood><input type=hidden name=p_submit value=Otsi><input type=hidden name=p_isikukood ><input type=hidden name=p_tegevus ><input type=hidden name=p_context ><input type=hidden name=p_tagasi ><input type=hidden name=p_mode value=1><input type=hidden name=p_queryobject></form>\" );win.document.kraaks.p_kkood.value = document.changeform.reg_nr.value;win.document.kraaks.submit();'>Maksuameti p&auml;ring</a>";
				break;

			case "contact_person":
			case "contact_person2":
			case "contact_person3":
				$data["options"] = $this->get_employee_picker($arr["obj_inst"], true);

			case "cust_contract_date":
			case "referal_type":
			case "priority":
				// read from rel
				if (($rel = $this->get_cust_rel($arr["obj_inst"])))
				{
					$data["value"] = $rel->prop($data["name"]);
				}
				if (isset($data["options"]) && !isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$data["value"]] = $tmp->name();
				}
				break;

			case "cust_contract_creator":
				$this->_get_cust_contract_creator($arr);
				break;

			case "currency":
				// get all currencies, sorted by order
				$ol = new object_list(array(
					"class_id" => CL_CURRENCY,
					"sort_by" => "objects.jrk"
				));
				$data["options"] = $ol->names();
				break;

			case "bank_account":
				$data["direct_links"] = 1;
				break;

			case "client_manager":
				$u = get_instance(CL_USER);
				$data["options"] = $this->get_employee_picker(obj($u->get_current_company()), true);
				if ($arr["new"])
				{
					$data["value"] = $u->get_current_person();
				}

				if (($rel = $this->get_cust_rel($arr["obj_inst"])))
				{
					$data["value"] = $rel->prop($data["name"]);
				}
				if (isset($data["options"]) && !isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$data["value"]] = $tmp->name();
				}
				break;

			/*case "code":
				if ($data["value"] == "" && is_oid($ct = $arr["obj_inst"]->prop("contact")) && $this->can("view", $ct))
				{
					$ct = obj($ct);
					$rk = $ct->prop("riik");
					if (is_oid($rk) && $this->can("view", $rk))
					{
						$rk = obj($rk);
						$code = substr(trim($rk->ord()), 0, 1);
						// get number of companies that have this country as an address
						$ol = new object_list(array(
							"class_id" => CL_CRM_COMPANY,
							"CL_CRM_COMPANY.contact.riik.name" => $rk->name()
						));
						$ol2 = new object_list(array(
							"class_id" => CL_CRM_PERSON,
							"CL_CRM_PERSON.address.riik.name" => $rk->name()
						));
						$code .= "-".sprintf("%04d", $ol->count() + $ol2->count());
						$data["value"] = $code;
					}
				}
				break;*/

			/* new code allows selection from all connected sectors
			case "pohitegevus":
				$ol = new object_list(array(
					"class_id" => CL_CRM_SECTOR,
				));
				if ($ol->count() < 30)
				{
					$data["options"] = array("" => t("--Vali--")) + $ol->names();
				}
				break;
			*/
			case "year_founded":
				$data["year_from"] = date("Y");
				$data["year_to"] = 1800;
				break;

			case 'contact':
				//hägish, panen nime kõrval html lingi ka
				if(sizeof($data['options']) > 1)
				{
					$url = $this->mk_my_orb('change',array(
						'id' => max(array_keys($data['options'])),
						"return_url" => get_ru(),
					),CL_CRM_ADDRESS);
					$data['caption'] .= '<br><a href="'.$url.'">'.t("Muuda").'</a>';
				}
				else
				{
					$url = $this->mk_my_orb('new',array(
						'alias_to' => $arr['obj_inst']->id(),
						'parent' => $arr['obj_inst']->id(),
						'reltype' => 3, //crm_company.reltype_address
						"return_url" => get_ru(),
					),CL_CRM_ADDRESS);
					$data['caption'] .= '<br><a href="'.$url.'">'.t("Lisa").'</a>';
				}
				break;

			case "firmajuht":
				$this->_get_firmajuht($arr);
				break;
			
			case "navtoolbar":
				$this->navtoolbar($arr);
				break;

			/// CUSTOMER tab
			case "my_projects":
			case "customer_search_cust_mgr":
			case "customer_search_is_co":
			case "my_customers_toolbar":
			case "my_customers_listing_tree":
			case "my_customers_table":
			case "offers_listing_toolbar":
			case "offers_listing_tree":
			case "offers_listing_table":
			case "offers_current_org_id":
			case "projects_listing_tree":
			case "projects_listing_table":
			case "org_proj_tb":
			case "org_proj_arh_tb":
			case "report_list":
			case "all_proj_search_part":
			case "proj_search_part":
			case "customer_toolbar":
			case "customer_listing_tree":
			case "customer":
			case "customer_t":
				static $cust_impl;
				if (!$cust_impl)
				{
					$cust_impl = get_instance("applications/crm/crm_company_cust_impl");
				}
				$fn = "_get_".$data["name"];
				return $cust_impl->$fn($arr);
			
			case "customer_search_name":
			case "customer_search_reg":
			case "customer_search_worker":
			case "customer_search_county":
			case "customer_search_city":
			case "customer_search_address":
			case "customer_search_ev":
			case "customer_search_submit":
			case "customer_search":
				$data['value'] = $arr['request'][$data["name"]];
				break;

			case "proj_search_dl_from":
				if (!isset($arr["request"]["proj_search_dl_from"]))
				{
					$data["value"] = mktime(0,0,0, date("m"), date("d"), date("Y")-1);
				}
				else
				if ($arr["request"]["proj_search_dl_from"]["year"] > 1)
				{
					$data["value"] = $arr["request"]["proj_search_dl_from"];
				}
				else
				{
					$data["value"] = -1;
				}
				break;

			case "proj_search_dl_to":
				if (!isset($arr["request"]["proj_search_dl_to"]))
				{
					$data["value"] = mktime(0,0,0, date("m"), date("d"), date("Y")+1);
				}
				else
				if ($arr["request"]["proj_search_dl_to"]["year"] > 1)
				{
					$data["value"] = $arr["request"]["proj_search_dl_to"];
				}
				else
				{
					$data["value"] = -1;
				}
				break;

			case "proj_search_state":
				$proj_i = get_instance(CL_PROJECT);
				$data["options"] = array("" => "") + $proj_i->states;
				if (!isset($arr["request"]["proj_search_state"]))
				{
					$data["value"] = PROJ_IN_PROGRESS;
				}	

			case "proj_search_cust":
			case "proj_search_name":
			case "proj_search_code":
			case "proj_search_contact_person":
			case "proj_search_task_name":
				if ($arr["request"]["do_proj_search"])
				{
					$data["value"] = $arr["request"][$data["name"]];
				}
				break;

			case "all_proj_search_state":
				$proj_i = get_instance(CL_PROJECT);
				$data["options"] = array("" => "") + $proj_i->states;
				if ($arr["request"]["search_all_proj"])
				{
					return PROP_IGNORE;
				}
				if (!$arr['request'][$data["name"]])
				{
					$data["value"] = PROJ_DONE;
				}
				else
				{
					$data["value"] = $arr['request'][$data["name"]];
				}
				break;

			case "all_proj_search_dl_from":
			case "all_proj_search_dl_to":
				if ($arr["request"]["group"] == "org_projects_archive")
				{
					return PROP_IGNORE;
				}
			case "all_proj_search_end_from":
			case "all_proj_search_end_to":
				if ($arr["request"]["search_all_proj"])
				{
					return PROP_IGNORE;
				}
				if (!$arr['request'][$data["name"]])
				{
					$data["value"] = -1;
				}
				else
				{
					$data["value"] = $arr['request'][$data["name"]];
				}
				break;

			case "all_proj_search_cust":
			case "all_proj_search_name":
			case "all_proj_search_code":
			case "all_proj_search_contact_person":
			case "all_proj_search_task_name":
			case "all_proj_search_sbt":
			case "all_proj_search_clear":
				if (!$arr["request"]["search_all_proj"])
				{
					$data["value"] = $arr["request"][$data["name"]];
				}
				else
				{
					return PROP_IGNORE;
				}
				break;

			/// OBJECTS TAB
			case "objects_listing_toolbar":
			case "objects_listing_tree":
			case "objects_listing_table":
				static $obj_impl;
				if (!$obj_impl)
				{
					$obj_impl = get_instance("applications/crm/crm_company_objects_impl");
				}
				$fn = "_get_".$data["name"];
				return $obj_impl->$fn($arr);

			// ACTIONS TAB
			case "org_actions":
			case "org_calls":
			case "org_meetings":
			case "org_tasks":
			case "tasks_call":
			case "my_tasks":
			case "my_tasks_cal":
			case "my_tasks_tb":
			case "act_s_part":
				static $overview_impl;
				if (!$overview_impl)
				{
					$overview_impl = get_instance("applications/crm/crm_company_overview_impl");
				}
				$fn = "_get_".$data["name"];
				return $overview_impl->$fn($arr);

			case "act_s_dl_from":
			case "act_s_dl_to":
				if (!$arr['request'][$data["name"]])
				{
					$data["value"] = -1;
				}
				else
				{
					$data["value"] = $arr['request'][$data["name"]];
				}
				break;

			case "act_s_status":
				$data["options"] = array(1 => t("T&ouml;&ouml;s"), 2 => t("Tehtud"), "3" => t("K&otilde;ik"));
				$data['value'] = $arr['request'][$data["name"]];
				break;

			case "act_s_task_content":
			case "act_s_code":
				if ($arr["request"]["group"] == "ovrv_offers")
				{
					return PROP_IGNORE;
				}

			case "act_s_cust":
			case "act_s_task_name":
			case "act_s_proj_name":
			case "act_s_sbt":
				$data['value'] = $arr['request'][$data["name"]];
				break;

			case "act_s_print_view":
				$data['value'] = $arr['request'][$data["name"]];
				$data["onclick"] = "document.changeform.target=\"_blank\"";
				break;

			// PEOPLE TAB
			case "contact_toolbar":
			case "unit_listing_tree":
			case "human_resources":
			case 'contacts_search_results':
			case "prof_search_results":
			case "personal_offers_toolbar":
			case "unit_listing_tree_personal":
			case "personal_offers_table":
			case "personal_candidates_toolbar":
			case "unit_listing_tree_candidates":
			case "personal_candidates_table":
			case "sect_edit":
			case "sect_tb":
				static $people_impl;
				if (!$people_impl)
				{
					$people_impl = get_instance("applications/crm/crm_company_people_impl");
				}
				$fn = "_get_".$data["name"];
				return $people_impl->$fn($arr);
		
			// contacts search
			case "contact_search_firstname":
			case "contact_search_lastname":
			case "contact_search_code":
			case "contact_search":
			case "contact_search_submit":
				if(!$arr['request']['contact_search'])
				{
					return PROP_IGNORE;
				}
				else
				{
					$data['value'] = $arr['request'][$data["name"]];
				}
				break;

			// profession search
			case "prof_search_firstname":
			case "prof_search_lastname":
			case "prof_search_code":
			case "prof_search":
			case 'prof_search_submit':
				if(!$arr['request']['prof_search'])
				{
					return PROP_IGNORE;
				}
				else
				{
					$data['value'] = $arr['request'][$data["name"]];
				}
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

			case "dn_s_name":
			case "dn_s_lead":
			case "dn_s_content":
				$data["value"] = $arr["request"][$data["name"]];
				break;

			case "bill_s_cust":
			case "bill_s_bill_no":
				$data['value'] = $arr['request'][$data["name"]];
				break;

			case "bill_s_from":
			case "bill_s_to":
				$data =& $arr["prop"];
				if (!isset($arr["request"][$data["name"]]))
				{
					$data["value"] = mktime(0,0,0, date("m"), date("d"), date("Y")-($data["name"] == "bill_s_from" ? 1 : 0));
				}
				else
				if ($arr["request"][$data["name"]]["year"] > 1)
				{
					$data["value"] = $arr["request"][$data["name"]];
				}
				else
				{
					$data["value"] = -1;
				}
				break;

			case 'bill_proj_list':
			case 'bill_task_list':
			case 'bill_tb':
			case 'bills_list':
			case 'bills_tb':
			case 'bills_mon_tb':
			case "bill_s_client_mgr":
			case "bill_s_status":
			case "bs_tb":
				static $bills_impl;
				if (!$bills_impl)
				{
					$bills_impl = get_instance("applications/crm/crm_company_bills_impl");
				}
				$fn = "_get_".$data["name"];
				return $bills_impl->$fn($arr);

			case "stats_s_to":
				if ($arr["request"][$data["name"]]["year"] > 1)
				{
					$data["value"] = $arr["request"][$data["name"]];
				}
				else
				{
					$day = date("w");
					if ($day == 0)
					{
						$day = 6;
					}	
					else
					{
						$day--;
					}
					$data["value"] = mktime(0,0,0, date("m"), date("d")+(7-$day), date("Y"));
				}
				break;

			case "stats_s_cust":
			case "stats_s_proj":
			case "stats_s_worker":
			case "stats_s_only_billable":
			case "stats_s_detailed":
				$data["value"] = $arr["request"][$data["name"]];
				aw_global_set("changeform_target",  "_blank");
				break;

			case "stats_tb":
			case "stats_list":
			case "stats_s_toolbar":
			case "stats_s_from":
			case "stats_s_cust_type":
			case "stats_s_res":
			case "stats_s_state":
			case "stats_s_res_type":
			case "stats_s_bill_state":
			case "stats_s_area":
			case "stats_s_worker_sel":
			case "stats_s_time_sel":
				static $stats_impl;
				if (!$stats_impl)
				{
					$stats_impl = get_instance("applications/crm/crm_company_stats_impl");
				}
				$fn = "_get_".$data["name"];
				return $stats_impl->$fn($arr);

			case "qv_t":
			case "qv_cust_inf":
				static $qv_impl;
				if (!$qv_impl)
				{
					$qv_impl = get_instance("applications/crm/crm_company_qv_impl");
				}
				$fn = "_get_".$data["name"];
				return $qv_impl->$fn($arr);

			// RESOURCES tab
			case "res_tb":
			case "res_tree":
			case "res_tbl":
				static $res_impl;
				if (!$res_impl)
				{
					$res_impl = get_instance("applications/crm/crm_company_res_impl");
				}
				$fn = "_get_".$data["name"];
				return $res_impl->$fn($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr['prop'];
		switch($data["name"])
		{
			case "server_folder":
				$this->_proc_server_folder($arr);
				break;

			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			case "openhours":
				if (empty($data['value']['id']) && is_oid($arr['obj_inst']->id()))
				{
					// create new openhours obj as child
					$oh = new object(array(
						'parent' => $arr['obj_inst']->id(),
						'class_id' => CL_OPENHOURS,
						'name' => $arr['obj_inst']->name().' avatud',
						'status' => STAT_ACTIVE,
					));
					$oh->save();
				
					$data['value']['id'] = $oh->id();
					$arr['request']['openhours']['id'] = $oh->id();
					// And link it
					$arr['obj_inst']->connect(array(
						'to' => $oh->id(),
						'reltype' => 'RELTYPE_OPENHOURS',
					));
					
				}
			break;
			case "name":
				if ($data["value"] == "")
				{
					$data["error"] = t("Nimi peab olema t&auml;idetud!");
					return PROP_ERROR;
				}
				break;

			case "cust_contract_date":
				// save to rel
				if (($rel = $this->get_cust_rel($arr["obj_inst"])))
				{
					$rel->set_prop($data["name"], date_edit::get_timestamp($data["value"]));
					$rel->save();
				}
				break;

			case "cust_contract_creator":
			case "referal_type":
			case "contact_person";
			case "contact_person2";
			case "contact_person3";
			case "priority";
			case "client_manager";
				// save to rel
				if (($rel = $this->get_cust_rel($arr["obj_inst"])))
				{
					$rel->set_prop($data["name"], $data["value"]);
					$rel->save();
				}
				break;
		}
		return PROP_OK;
	}

	function callback_pre_edit($arr)
	{
		// initialize
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));
	}
	
	function get_all_workers_for_company($obj,&$data,$workers_too=false)
	{	
		//getting all the workers for the $obj
		$conns = $obj->connections_from(array(
			"type" => "RELTYPE_WORKERS",
		));
		foreach($conns as $conn)
		{
			$data[$conn->prop('to')] = $conn->prop('to');	
		}
		
		if($workers_too)
		{
			$conns = $obj->connections_from(array(
				'type' => "RELTYPE_WORKERS",
			));
			foreach($conns as $conn)
			{
				$data[$conn->prop('to')] = $conn->prop('to');
			}
		}

		//getting all the sections
		$conns = $obj->connections_from(array(
			'type' => "RELTYPE_SECTION",
		));
		foreach($conns as $conn)
		{
			$tmp_obj = new object($conn->prop('to'));
			$this->get_all_workers_for_company(&$tmp_obj,&$data);
		}
	}

	// Invoked when a connection is created from person to organization || section
	// .. this will then create the opposite connection.
	function on_connect_person_to_org($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_COMPANY)
		{
			if($conn->prop('reltype')==22) //crm_person.reltype_client_im_handling
			{
				$target_obj->connect(array(
					"to" => $conn->prop("from"),
					"reltype" => "RELTYPE_MAINTAINER",
				));
			}
			else if($conn->prop('reltype')==23) //crm_person.reltype_client_im_selling_to
			{
				$target_obj->connect(array(
					"to" => $conn->prop("from"),
					"reltype" => "RELTYPE_SELLER",
				));
			}
			else if($conn->prop('reltype') == 6) //crm_person.reltype_WORK
			{
				$target_obj->connect(array(
					"to" => $conn->prop("from"),
					"reltype" => "RELTYPE_WORKERS",
				));
			}
		}
		else if($target_obj->class_id() == CL_CRM_SECTION)
		{
			$target_obj->connect(array(
				"to" => $conn->prop("from"),
				"reltype" => "RELTYPE_WORKERS",
			));
		
		}
	}

	// Invoked when a connection is created from person to section
	// .. this will then create the opposite connection.
	function on_connect_person_to_section($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_SECTION)
		{
			$target_obj->connect(array(
				"to" => $conn->prop("from"),
				"reltype" => "RELTYPE_WORKERS",
			));
		}
	}

	// Invoked when a connection from person to organization is removed
	// .. this will then remove the opposite connection as well
	function on_disconnect_person_from_org($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_COMPANY)
		{
			if($conn->prop('reltype') == 6)
			{
				if($target_obj->is_connected_to(array(
						'to' => $conn->prop('from'),
						'type' => 8))) //RELTYPE_WORKER
				{
					$target_obj->disconnect(array(
						"from" => $conn->prop("from"),
						'reltype' => "RELTYPE_WORKERS",
					));
				}
			}
			else if($conn->prop('reltype') == 22) //crm_person.client_im_handling
			{
				if($target_obj->is_connected_to(array(
						'to' => $conn->prop('from'),
						'type' => 31))) //RELTYPE_MAINTAINER
				{
					$target_obj->disconnect(array(
						"from" => $conn->prop("from"),
						"reltype" => "RELTYPE_MAINTAINER",
					));
				}
			}
			else if($conn->prop('reltype') == 23) //crm_person.client_im_selling_to
			{
				if($target_obj->is_connected_to(array(
						'to' => $conn->prop('from'),
						'type' => 32))) //RELTYPE_SELLER
				{
					$target_obj->disconnect(array(
						"from" => $conn->prop("from"),
						'reltype' => "RELTYPE_SELLER",
					));
				}
			}
		}
	}
	
	/**
		@attrib name=delete_selected_objects
	**/
	function delete_selected_objects($arr)
	{
		foreach ($arr["select"] as $deleted_obj_id)
		{
			$deleted_obj = &obj($deleted_obj_id);
			$deleted_obj->delete();	
		}
		return $this->mk_my_orb("change", array(
			"id" => $arr["id"], 
			"group" => $arr["group"], 
			"org_id" => $arr["offers_current_org_id"]),
			$arr["class"]
		);
	}
	
	/**
		@attrib name=submit_new_task
		@param id required type=int acl=view
	**/
	function submit_new_task($arr)
	{
		$arr['clid'] = CL_TASK;
		$arr['reltype'] = 10; //CL_CRM_PERSON.RELTYPE_PERSON_TASK
		$this->submit_new_action_to_person(&$arr);
	}

	/**
		@attrib name=search_for_contacts
		@param cat optional type=int
		@param unit optional type=int
	**/
	function search_for_contacts($arr)
	{
		return $this->mk_my_orb(
			'change',array(
				'id' => $arr['id'],
				'group' => $arr['group'],
				'contact_search' => true,
				'unit' => $arr['unit'],
				'cat' => $arr['cat'],
			),
			'crm_company'
		);
	}
	
	/**
		@attrib name=search_for_profs
		@param cat optional type=int
		@param unit optional type=int
	**/
	function search_for_profs($arr)
	{
		return $this->mk_my_orb(
			'change',array(
				'id' => $arr['id'],
				'group' => $arr['group'],
				'prof_search' => true,
				'unit' => $arr['unit'],
				'cat' => $arr['cat'],
			),
			'crm_company'
		);
	}
	
	/**
		@attrib name=submit_new_call
		@param id required type=int acl=view
	**/
	function submit_new_call($arr)
	{
		$arr['clid'] = CL_CRM_CALL;
		$arr['reltype'] = 9; //CL_CRM_PERSON.RELTYPE_PERSON_CALL
		$this->submit_new_action_to_person(&$arr);
	}
	
	/**
		@attrib name=submit_new_meeting
		@param id required type=int acl=view
	**/
	function submit_new_meeting($arr)
	{
		$arr['clid'] = CL_CRM_MEETING;
		$arr['reltype'] = 8; //CL_CRM_PERSON.RELTYPE_PERSON_MEETING
		$this->submit_new_action_to_person(&$arr);
	}

	function submit_new_action_to_person($arr)
	{
		if(!is_array($arr['check']))
		{
			return;
		}

		$us = get_instance(CL_USER);
		$person = new object($us->get_current_person());
		$arr['check'][$person->id()] = $person->id();

		$prsn = get_instance(CL_CRM_PERSON);
		$pl = get_instance(CL_PLANNER);
		$cal_id = $pl->get_calendar_for_user(array(
			'uid' => aw_global_get('uid')
		));
		$alias_to_org_arr = array();
		$fake_alias = $arr["id"];
		
		reset($arr['check']);

		$fake_alias = current($arr['check']);

		$url = $this->mk_my_orb('new',array(
			'add_to_cal' => $cal_id,
			'alias_to_org'=>$fake_alias,
			'reltype_org'=> $arr['reltype'],
			'alias_to_org_arr'=>urlencode(serialize($arr['check'])),
			"parent" => $arr["id"],
			"return_url" => urlencode($arr["post_ru"])
		),$arr['clid']);
		header('Location: '.$url);	
		die();
	}

	// If an event is added to a person, then this method
	// makes that event appear in any organization
	// calendars that the person has a "workplace" connection
	// with.
	function on_add_event_to_person($arr)
	{
		$event_obj = new object($arr["event_id"]);
		$typemap = array(
			CL_CRM_MEETING => 11,
			CL_CRM_CALL => 12,
			CL_TASK => 13,
		);

		$reltype = $typemap[$event_obj->class_id()];
		if (empty($reltype))
		{
			return false;
		};

		$per_obj = new object($arr["source_id"]);

		$conns = $per_obj->connections_to(array(
			"type" => 8, //RELTYPE_WORKERS
		));

		foreach($conns as $conn)
		{
			$org_obj = $conn->from();
			$org_obj->connect(array(
				"to" => $arr["event_id"],
				"reltype" => $reltype,
			));
		}
	}

	
	////
	// !Listens to MSG_EVENT_ADD broadcasts and creates
	// connections between a CRM_PERSON and a CRM_COMPANY
	// if an event is added to a person.
	function register_humanres_event($arr)
	{
		$event_obj = new object($arr["event_id"]);
		$typemap = array(
			CL_CRM_CALL => 12,
			CL_TASK => 13,
			CL_CRM_MEETING => 11,
		);

		$reltype = $typemap[$event_obj->class_id()];
		if (empty($reltype))
		{
			return false;
		};

		$per_obj = new object($arr["person_id"]);

		$conns = $per_obj->connections_to(array(
			"type" => 8,
		));

		foreach($conns as $conn)
		{
			$org_obj = $conn->from();
			$org_obj->connect(array(
				"to" => $arr["event_id"],
				"reltype" => $reltype,
			));
		}
	}


	/**
		deletes the relations unit -> person || organization -> person
		@attrib name=submit_delete_relations
		@param id required type=int acl=view
		@param unit optional type=int
	**/
	function submit_delete_relations($arr)
	{
		$main_obj = new object($arr['id']);
		
		if((int)$arr['unit'])
		{
			$main_obj = new object($arr['unit']);
		}
	
		if (is_array($arr["check"]))
		{
			foreach($arr['check'] as $key => $value)
			{
				if ($main_obj->is_connected_to(array("to" => $value)))
				{
					$main_obj->disconnect(array('from' => $value));
				}
			}
		};

		return $this->mk_my_orb("change",array(
			"id" 	=> $arr["id"],
			"group"	=> $arr["group"],
			"unit"	=> $arr["unit"],
		));
	}

	/**
		@attrib name=submit_delete_my_customers_relations
		@param id required type=int acl=view
	**/
	function submit_delete_my_customers_relations($arr)
	{
		//die(dbg::dump($arr));
		/*$this->crm_company_init();
		if($arr['check'])
		{
			foreach($arr['check'] as $from)
			{
				if($this->users_person->is_connected_to(array(
						'to' => $arr['check'],
						'reltype' => 22, //crm_person.client_im_handling
					)))
				{
					$this->users_person->disconnect(array(
						'from' => $from,
						'type' => 22,
					));
				}

				if($this->users_person->is_connected_to(array(
						'to' => $arr['check'],
						'reltype' => 23, //crm_person.client_im_selling_to
					)))
				{
					$this->users_person->disconnect(array(
						'from' => $from,
						'type' => 23
					));
				}
			}
		}*/
		if (is_array($arr["check"]) && count($arr["check"]))
		{
			$ol = new object_list(array("oid" => $arr["check"]));
			$ol->delete();
		}

		return $arr["post_ru"];
	}

	/**
		deletes the relations category -> organization || organization -> category
		@attrib name=submit_delete_customer_relations
		@param id required type=int acl=view
		@param customer optional type=int
	**/
	function submit_delete_customer_relations($arr)
	{
		$url = $this->mk_my_orb('change',array(
				'id' => $arr['id'],
				'group'=>'relorg',
				'category'=>$arr['category']
			),
			CL_CRM_COMPANY
		);
		if(!is_array($arr['check']))
		{
			return $url;
		}
		$main_obj = new Object($arr['id']);
		
		if((int)$arr['category'])
		{
			$main_obj = new Object($arr['category']);
		}
		foreach($arr['check'] as $key=>$value)
		{
			$main_obj->disconnect(array('from'=>$value));
		}
		return $url;		
	}

	/*
	
	*/
	function callback_on_load($arr)		
	{
		$this->crm_company_init();
		if(array_key_exists('request',$arr))
		{
			$this->do_search = $arr['request']['contact_search'];
			$this->do_search_prof = $arr['request']['prof_search'];
		}
		else
		{
			$this->do_search = $arr['contact_search'];
			$this->do_search_prof = $arr['prof_search'];
		}

		if(is_oid($arr['request']['cat']))
		{
			$this->cat = $arr['request']['cat'];
		}

		$this->unit=$arr['request']['unit'];
		$this->category=$arr['request']['category'];
	}

	/*
		kõik lingid saavad $key muutuja lisaks
	*/
	function callback_mod_reforb($arr)
	{
		$arr['unit'] = $this->unit;
		$arr['category'] = $this->category;
		$arr['cat'] = $this->cat;
		$arr['proj'] = $_GET["proj"];
		$arr["post_ru"] = post_ru();
		$arr["tf"] = $_GET["tf"];
		$arr["cust_cat"] = 1;
	}

	/**
		@attrib name=create_new_company all_args="1"
	**/
	function create_new_company($arr)
	{
		$parent = -1;
		if(is_oid($arr['id']))
		{
			$parent = $arr['id'];
		}
		else if(is_oid($arr['category']))
		{
			$parent = $arr['category'];
		}

		$new_company = new object(array(
			'parent' => $parent,
		));
		$new_company->set_class_id(CL_CRM_COMPANY);
		$new_company->save();
		if(strlen(trim($arr['customer_search_name'])))
		{
			//the company GETS A NAME!!!
			$new_company->set_prop('name',trim($arr['customer_search_name']));
		}
		if(strlen(trim($arr['customer_search_reg'])))
		{
			//the company GETS A REGISTRATION NuMbEr
			$new_company->set_prop('reg_nr',trim($arr['customer_search_reg']));
		}
		
		if(!empty($arr['sector']) && is_oid($arr['sector']) && $this->can("view", $arr['sector']))
		{
			$sect = obj($arr['sector']);
			if ($sect->class_id() == CL_CRM_SECTOR)
			{
				// the company GETS A SECTOR! AIN'T THIS FUN?
				$new_company->connect(array(
					'to' => $arr['sector'],
					'type' => 'RELTYPE_TEGEVUSALAD'
				));
				$new_company->set_prop('pohitegevus', $arr['sector']);
			}
		}

		//won't create the address object and connection unless some fields from the
		//address really exist and are useable! i'll determine that and then try
		//to do the magic
		$has_address = false;
		$county = null;
		$county_name = '';
		$city = null;
		$city_name = '';
		$street = null;
		$street_name = '';

		//have to trim, explode county, city
		foreach(array('customer_search_county','customer_search_city') as $value)
		{
			if(isset($arr[$value]))
			{
				//let's clean up the item
				$tmp_arr = explode(',',$arr[$value]);
				array_walk($tmp_arr,create_function('&$param','$param = trim($param);'));
				array_walk($tmp_arr,create_function('&$param','$param = "%".$param."%";'));
				$arr[$value] = $tmp_arr;
			}
		}

		if(strlen(trim($arr['customer_search_county'])))
		{
			//i'll try to find a matching county, if i find multiple
			//i'll take the first one, if none is found i'll take no action
			//atleast for now
			
			$ol = new object_list(array(
				'class_id' => CL_CRM_COUNTY,
				'name'	=> $arr['customer_search_county'],
			));
			if(sizeof($ol->ids()))
			{
				list(,$county) = each($ol->ids());
				$county_name = $ol->list_names[$county];
			}

			$has_address = true;
		}

		if(strlen(trim($arr['customer_search_city'])))
		{
			$ol = new object_list(array(
				'class_id' => CL_CRM_CITY,
				'name' => $arr['customer_search_city'],
			));
			
			if(sizeof($ol->ids()))
			{
				list(,$city) = each($ol->ids());
				$city_name = $ol->list_names[$city];
			}
			$has_address = true;
		}

		if(strlen(trim($arr['customer_search_address'])))
		{
			$street = trim($arr['customer_search_address']);
			//just for consistency
			$street_name = &$street;
			$has_address = true;
		}

		if($has_address)
		{
			$address = new object(array(
				'parent' => $new_company->id(),
			));
			$address->set_class_id(CL_CRM_ADDRESS);
			if($street)
			{
				$address->set_prop('aadress',$street);
			}
	
		
			if($county)
			{
				//loome seose
				$address->connect(array(
					'to' => $county,
					'reltype' => 'RELTYPE_MAAKOND'
				));
				//kinnitame seose
				$address->set_prop('maakond',$county);
			}

			if($city)
			{
				//loome seose
				$address->connect(array(
					'to' => $city,
					'reltype' => 'RELTYPE_LINN'
				));
				//kinnitame seose
				$address->set_prop('linn',$city);
			}
			$address->set_prop('name', $street_name.' '.$city_name.' '.$county_name);
			$address->save();
			//kinnitame aadressi kompaniiga
			$new_company->connect(array(
				'to' => $address->id(),
				'reltype' => "RELTYPE_ADDRESS", //crm_company.reltype_address
			));
		}
		$new_company->save();

		//have to direct the user to the just created company
		$url = $this->mk_my_orb('change',array(
				'id' => $new_company->id(),
			),
			'crm_company'
		);
		header('Location: '.$url);
		die();
	}

	function callback_mod_retval($arr)
	{
		if($this->do_search)
		{
			$arr['args']['contact_search_firstname'] = urlencode($arr['request']['contact_search_firstname']);
			$arr['args']['contact_search_lastname'] = urlencode($arr['request']['contact_search_lastname']);
			$arr['args']['contact_search_code'] = urlencode($arr['request']['contact_search_code']);
			$arr['args']['contact_search'] = $this->do_search;
			$arr['args']['contacts_search_show_results'] = 1;
		}

		if ($this->do_search_prof)
		{
			$arr['args']['prof_search_show_results'] = 1;
			$arr['args']['prof_search'] = 1;
		}
	
		if($arr["request"]["customer_search_submit"])
		{
			$arr['args']['customer_search_name'] = urlencode($arr['request']['customer_search_name']);
			$arr['args']['customer_search_worker'] = urlencode($arr['request']['customer_search_worker']);
			$arr['args']['customer_search_ev'] = urlencode($arr['request']['customer_search_ev']);
			$arr['args']['customer_search_cust_mgr'] = urlencode($arr['request']['customer_search_cust_mgr']);
			$arr['args']['customer_search_reg'] = urlencode($arr['request']['customer_search_reg']);
			$arr['args']['customer_search_address'] = urlencode($arr['request']['customer_search_address']);
			$arr['args']['customer_search_city'] = urlencode($arr['request']['customer_search_city']);
			$arr['args']['customer_search_county'] = urlencode($arr['request']['customer_search_county']);
			$arr['args']['customer_search_submit'] = $arr['request']['customer_search_submit'];
			$arr['args']['customer_search_is_co'] = $arr['request']['customer_search_is_co'];
		}

		if ($arr["request"]["proj_search_sbt"])
		{
			$arr["args"]["proj_search_cust"] = $arr["request"]["proj_search_cust"];
			$arr["args"]["proj_search_part"] = $arr["request"]["proj_search_part"];
			$arr["args"]["proj_search_name"] = $arr["request"]["proj_search_name"];
			$arr["args"]["proj_search_code"] = $arr["request"]["proj_search_code"];
			$arr["args"]["proj_search_contact_person"] = $arr["request"]["proj_search_contact_person"];
			$arr["args"]["proj_search_task_name"] = $arr["request"]["proj_search_task_name"];
			$arr["args"]["proj_search_dl_from"] = $arr["request"]["proj_search_dl_from"];
			$arr["args"]["proj_search_dl_to"] = $arr["request"]["proj_search_dl_to"];
			$arr["args"]["proj_search_state"] = $arr["request"]["proj_search_state"];
			$arr["args"]["proj_search_sbt"] = 1;
			$arr["args"]["do_proj_search"] = 1;
		}	

		if ($arr["request"]["all_proj_search_sbt"])
		{
			$arr["args"]["all_proj_search_cust"] = $arr["request"]["all_proj_search_cust"];
			$arr["args"]["all_proj_search_part"] = $arr["request"]["all_proj_search_part"];
			$arr["args"]["all_proj_search_name"] = $arr["request"]["all_proj_search_name"];
			$arr["args"]["all_proj_search_code"] = $arr["request"]["all_proj_search_code"];
			$arr["args"]["all_proj_search_contact_person"] = $arr["request"]["all_proj_search_contact_person"];
			$arr["args"]["all_proj_search_task_name"] = $arr["request"]["all_proj_search_task_name"];
			$arr["args"]["all_proj_search_dl_from"] = $arr["request"]["all_proj_search_dl_from"];
			$arr["args"]["all_proj_search_dl_to"] = $arr["request"]["all_proj_search_dl_to"];
			$arr["args"]["all_proj_search_end_from"] = $arr["request"]["all_proj_search_end_from"];
			$arr["args"]["all_proj_search_end_to"] = $arr["request"]["all_proj_search_end_to"];
			$arr["args"]["all_proj_search_state"] = $arr["request"]["all_proj_search_state"];
			$arr["args"]["search_all_proj"] = 0;
			$arr["args"]["aps_sbt"] = 1;
		}	

		if ($arr["request"]["docs_s_sbt"])
		{
			$arr["args"]["docs_s_name"] = $arr["request"]["docs_s_name"];
			$arr["args"]["docs_s_type"] = $arr["request"]["docs_s_type"];
			$arr["args"]["docs_s_task"] = $arr["request"]["docs_s_task"];
			$arr["args"]["docs_s_user"] = $arr["request"]["docs_s_user"];
			$arr["args"]["docs_s_name"] = $arr["request"]["docs_s_name"];
			$arr["args"]["docs_s_customer"] = $arr["request"]["docs_s_customer"];
			$arr["args"]["docs_s_sbt"] = $arr["request"]["docs_s_sbt"];
			$arr["args"]["do_doc_search"] = 1;
		}	

		if ($arr["request"]["act_s_sbt"] || $arr["request"]["group"] == "bills_search")
		{
			$arr["args"]["act_s_cust"] = $arr["request"]["act_s_cust"];
			$arr["args"]["act_s_part"] = $arr["request"]["act_s_part"];
			$arr["args"]["act_s_task_name"] = $arr["request"]["act_s_task_name"];
			$arr["args"]["act_s_task_content"] = $arr["request"]["act_s_task_content"];
			$arr["args"]["act_s_code"] = $arr["request"]["act_s_code"];
			$arr["args"]["act_s_proj_name"] = $arr["request"]["act_s_proj_name"];
			$arr["args"]["act_s_dl_from"] = $arr["request"]["act_s_dl_from"];
			$arr["args"]["act_s_dl_to"] = $arr["request"]["act_s_dl_to"];
			$arr["args"]["act_s_status"] = $arr["request"]["act_s_status"];
			$arr["args"]["act_s_print_view"] = $arr["request"]["act_s_print_view"];
			$arr["args"]["act_s_sbt"] = $arr["request"]["act_s_sbt"];
			$arr["args"]["act_s_is_is"] = 1;
		}	
		if ($arr["request"]["bill_s_search"] != "")
		{
			$arr["args"]["bill_s_cust"] = $arr["request"]["bill_s_cust"];
			$arr["args"]["bill_s_bill_no"] = $arr["request"]["bill_s_bill_no"];
			$arr["args"]["bill_s_from"] = $arr["request"]["bill_s_from"];
			$arr["args"]["bill_s_to"] = $arr["request"]["bill_s_to"];
			$arr["args"]["bill_s_client_mgr"] = $arr["request"]["bill_s_client_mgr"];
			$arr["args"]["bill_s_status"] = $arr["request"]["bill_s_status"];
			$arr["args"]["bill_s_search"] = $arr["request"]["bill_s_search"];
		}

		if($arr['request']['unit'])
		{
			$arr['args']['unit'] = $arr['request']['unit'];
		}

		if($arr['request']['category'])
		{
			$arr['args']['category'] = $arr['request']['category'];
		}
		
		if($arr['request']['cat'])
		{
			$arr['args']['cat'] = $arr['request']['cat'];
		}
	}


	/**
		@attrib name=save_search_results
	**/
	function save_search_results($arr)
	{
		foreach($arr['check'] as $key=>$value)
		{
			$obj = null;
			$reltype = 0;
			if($arr['unit'])
			{
				$obj = new object($arr['unit']);
				$reltype = 2; //crm_section.workers
			}
			else
			{
				$obj = new object($arr['id']);
				$reltype = 8; //crm_company.workers	
			}
			
			$obj->connect(array(
				'to' => $value,
				'reltype' => $reltype 
			));

			$person = new object($value);
			$person->set_prop('work_contact',$arr['id']);
			$person->save();

			if ($arr["cat"] && $cat != 999999)
			{
				$person->connect(array(
					"to" => $arr["cat"],
					"reltype" => 7
				));
			}

			// run user creation
			$cuc = get_instance("crm/crm_user_creator");
			$cuc->on_save_person(array(
				"oid" => $person->id()
			));
		}

		return $this->mk_my_orb('change',array(
				'id' => $arr['id'],
				'unit' => $arr['unit'],
				'cat' => $arr['cat'],
				'group' => $arr['group'],
			),
			$arr['class']
		);
	}

	//goes through all the relations and builds a set of id into $data
	function get_customers_for_company($obj, $data, $category = false)
	{
		if (!$category)
		{
			$impl = array();
			$this->get_all_workers_for_company($obj, &$impl);  
			$impl[] = $obj->id();
			// also, add all orderers from projects where the company is implementor
			$ol = new object_list(array(
				"class_id" => CL_PROJECT,
				"CL_PROJECT.RELTYPE_IMPLEMENTOR" => $impl,
				"lang_id" => array(),
				"site_id" => array() 
			));
			foreach($ol->arr() as $o)
			{
				foreach((array)$o->prop("orderer") as $ord)
				{
					if ($ord)
					{
						$data[$ord] = $ord;
					}
				}
			}
		}

		$conns = $obj->connections_from(array(
			"type" => "RELTYPE_CUSTOMER",
		));
		foreach($conns as $conn)
		{
			$data[$conn->prop('to')] = $conn->prop('to');
		}

		//let's look through the categories
		$conns = $obj->connections_from(array(
			"type" => "RELTYPE_CATEGORY",
		));
		foreach($conns as $conn)
		{
			$obj = new object($conn->prop('to'));
			$this->get_customers_for_company(&$obj,&$data,true);
		}
	}

	/*
		arr
			id - id of the company who's projects we wan't
	*/
	function get_all_projects_for_company($arr)
	{
		if(is_oid($arr['id']))
		{
			$company = new object($arr['id']);

			$conns = $company->connections_from(array(
				"type" => "RELTYPE_PROJECT",
			));

			$projects = array();
		
			foreach($conns as $conn)
			{
				$projects[$conn->prop('to')] = $conn->to();
			}
			return $projects;
		}
		else
		{
			return array();
		}
	}
	
	function get_all_org_customer_categories($obj)
	{
		static $retval;
		$conns = $obj->connections_from(array(
			"type" => "RELTYPE_CATEGORY",
		));
		
		foreach($conns as $conn)
		{
			$retval[$conn->prop("to")] = $conn->prop("to");
			$obj = $conn->to();
			$this->get_all_org_customer_categories($obj);
		}
		return $retval;
	}
	
	function get_customers_for_category($cat_id)
	{
		if($cat_id)
		{
			$cat_obj = &obj($cat_id);
			$conns = $cat_obj->connections_from(array(
				"type" => "RELTYPE_CUSTOMER"
			));
			foreach ($conns as $conn)
			{
				$retval[$conn->prop("to")] = $conn->prop("to");
			}
			return $retval;
		}
		return false;
	}
	
	/**
		@attrib name=create_new_person

		@param parent required
		@param alias_to required
		@param reltype required
		@param return_url optional
		@param profession optional
		@param return_url optional
	**/
	function create_new_person($arr)
	{
		/*
			why am i writing this?
			cos i want the created object to have certain
			options filled with certain values! it wouldn't
			be nice of me to hack the creating of new objects
		*/
		$person = new object();
		$person->set_class_id(CL_CRM_PERSON);
		$person->set_parent($arr['parent']);
		$person->set_meta("no_create_user_yet", true);
		$person->save();
		$alias_to = new object($arr['alias_to']);

		$alias_to->connect(array(
			'to' => $person->id(),
			'reltype' => $arr['reltype'],
		));

		if (is_oid($arr["profession"]) && $this->can("view", $arr["profession"]))
		{
			$person->connect(array(
				"to" => $arr["profession"],
				"reltype" => "RELTYPE_RANK"
			));
		}

		$work_contact = 0;
		if($alias_to->class_id()==CL_CRM_COMPANY)
		{
			$work_contact = $alias_to->id();
		}
		else
		{
			$person_class = get_instance(CL_CRM_PERSON);
			$work_contact = $person_class->get_work_contacts(array(
				'obj_inst' => &$person,
			));
			list($work_contact,) = each($work_contact);
		}
		$person->set_prop('work_contact',$work_contact);

		$orgs = array();
		foreach($person->connections_from(array("type" => "RELTYPE_SECTION")) as $c)
		{
			$orgs[$c->prop("to")] = $c->prop("to");
		}
		$person->set_prop("org_section", $orgs);
		$person->save();
		if ($arr["get_ru"])
		{
			$ru = urlencode($arr["get_ru"]);
		}
		else
		if ($arr["return_url"])
		{
			$ru = urlencode($arr["return_url"]);
		}
		
		return html::get_change_url($person->id())."&return_url=".$ru;
	}
	

	/**	
		@attrib name=cut
	**/
	function cut($arr)
	{
		$_SESSION["crm_cut"] = $arr["select"];
		return $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"group" => $arr["group"]), CL_CRM_COMPANY);
	}
	
	/**	
		@attrib name=paste all_args=1
	**/
	function paste($arr)
	{	
		foreach ($_SESSION["crm_cut"] as $oid)
		{
			$obj = &obj($oid);
			$obj->set_parent($arr["parent"]);
			$obj->save();
		}
		unset($_SESSION["crm_cut"]);
		return $this->mk_my_orb("change", array(
				"id" => $arr["id"], 
				"group" => $arr["group"],
				"parent" => $arr["parent"],
			), 
			CL_CRM_COMPANY
		);
	}
	
	function get_all_org_sections($obj)
	{
		static $retval;
		foreach ($obj->connections_from(array("type" => "RELTYPE_SECTION")) as $section)
		{
			$retval[$section->prop("to")] = $section->prop("to");
			$section_obj = $section->to();
			$this->get_all_org_sections($section_obj);
		}
		return $retval;	
	}
	
	/** cuts the selected person objects

		@attrib name=cut_p

	**/
	function cut_p($arr)
	{
		// in cut, we must remember the unit/profession from where the person was cut
		// unit is unit, cat is profession
		unset($_SESSION["crm_cut_p"]);
		foreach(safe_array($arr["check"]) as $p_id)
		{
			$_SESSION["crm_cut_p"][$p_id] = array(
				"unit" => $arr["unit"],
				"proffession" => $arr["cat"]
			);
		}

		return $arr["post_ru"];
	}

	/** copies the selected person objects

		@attrib name=copy_p

	**/
	function copy_p($arr)
	{
		// in copy we must just remember the person

		unset($_SESSION["crm_copy_p"]);
		foreach(safe_array($arr["check"]) as $p_id)
		{
			$_SESSION["crm_copy_p"][$p_id] = $p_id;
		}

		return $arr["post_ru"];
	}

	/** pastes the cut/copied person objects

		@attrib name=paste_p

	**/
	function paste_p($arr)
	{
		// first cut persons
		foreach(safe_array($_SESSION["crm_cut_p"]) as $p_id => $p_from)
		{
			if (!(is_oid($p_id) && $this->can("view", $p_id)))
			{
				continue;
			}

			$p = obj($p_id);

			// if copied from a profession
			if (is_oid($p_from["proffession"]))
			{
				// disconnect from that profession
				if ($p->is_connected_to(array("to" => $p_from["proffession"], "type" => 7)))
				{
					$p->disconnect(array(
						"from" => $p_from["proffession"],
						"type" => 7 // crm_person.reltype_rank
					));
				}
			}
			else
			// else
			// if from unit
			if (is_oid($p_from["unit"]))
			{
				// disconnect from that unit
				if ($p->is_connected_to(array("to" => $p_from["unit"], "type" => 21)))
				{
					$p->disconnect(array(
						"from" => $p_from["unit"],
						"type" => "RELTYPE_SECTION",
					));
				}
			}
			
			// if currently under profession
			if ($arr["cat"])
			{
				// connect to that profession
				$p->connect(array(
					"to" => $arr["cat"],
					"reltype" => 7 
				));
			}

			// if currently under unit
			if ($arr["unit"])
			{
				// connect to that unit
				$p->connect(array(
					"to" => $arr["unit"],
					"reltype" => 21
				));
			}
		}

		// now copied persons
		foreach(safe_array($_SESSION["crm_copy_p"]) as $p_id)
		{
			if (!(is_oid($p_id) && $this->can("view", $p_id)))
			{
				continue;
			}

			$p = obj($p_id);

			// if currently under profession
			if ($arr["cat"])
			{
				// connect to that profession
				$p->connect(array(
					"to" => $arr["cat"],
					"reltype" => 7 
				));
			}

			// if currently under unit
			if ($arr["unit"])
			{
				// connect to that unit
				$p->connect(array(
					"to" => $arr["unit"],
					"reltype" => 21
				));
			}
		}

		unset($_SESSION["crm_cut_p"]);
		unset($_SESSION["crm_copy_p"]);

		return $arr["post_ru"];
	}

	function _get_firmajuht($arr)
	{
		$arr["prop"]["options"] = $this->get_employee_picker($arr["obj_inst"]);
	}

	function navtoolbar(&$args)
	{
		$RELTYPE_ADDRESS = 3; //crm_company.reltype_address
		
		$toolbar = &$args["prop"]["toolbar"];
		$users = get_instance("users");

		$parents[19] = $parents[8] = $parents[$RELTYPE_ADDRESS] = $args['obj_inst']->parent();

		if (!empty($this->cal_id))
		{
			$user_calendar = new object($this->cal_id);
			$parents[12] = $parents[11] = $parents[10] = $parents[13] = $user_calendar->prop('event_folder');
		}

		$clss = aw_ini_get("classes");

		$toolbar->add_menu_button(array(
			"name" => "main_menu",
			"tooltip" => t("Uus"),
		));

		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "calendar_sub",
			"text" => $clss[CL_PLANNER]["name"],
		));
		
		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "firma_sub",
			"text" => $clss[$this->clid]["name"],
		));

		//3 == crm_company.reltype_address=3 //RELTYPE_WORKERSRELTYPE_JOBS
		$alist = array(8,$RELTYPE_ADDRESS,19);
		foreach($alist as $key => $val)
		{
			$clids = $this->relinfo[$val]["clid"];
			if (is_array($clids))
			{
				foreach($clids as $clid)
				{
					$classinf = $clss[$clid];

					$url = $this->mk_my_orb('new',array(
						'alias_to' => $args['obj_inst']->id(),
						'reltype' => $val,
						'title' => $classinf["name"].' : '.$args['obj_inst']->name(),
						'parent' => $parents[$val],
						'return_url' => urlencode(aw_global_get('REQUEST_URI')),
					),$clid);

					$has_parent = isset($parents[$val]) && $parents[$val];
					$disabled = $has_parent ? false : true;
					$toolbar->add_menu_item(array(
						"parent" => "firma_sub",
						"text" => sprintf(t('Lisa %s'), $classinf["name"]),
						"link" => $has_parent ? $url : "",
						"title" => $has_parent ? "" : t("Kataloog määramata"),
						"disabled" => $has_parent ? false : true,
					));
				};
			};
		};

		// aha, I need to figure out which objects can be added to that relation type

		// basically, I need to create a list of relation types that are of any
		// interest to me and then get a list of all classes for those
		
		//$action = array(RELTYPE_DEAL,RELTYPE_KOHTUMINE,RELTYPE_CALL,RELTYPE_TASK);
		$action = array(/*10,*/ 11, 12, 13);
		foreach($action as $key => $val)
		{
			$clids = $this->relinfo[$val]["clid"];
			$reltype = $this->relinfo[$val]["value"];
			if (is_array($clids))
			{
				foreach($clids as $clid)
				{
					$classinf = $clss[$clid];
					$url = $this->mk_my_orb('new',array(
						// alright then. so what do those things to? 
						// they add a relation between the object created through
						// the planner and this object


						// can I do that with messages instead? and if I can, how
						// on earth am I going to do that?

						// I'm adding an event object to a calendar, how do I know
						// that I will have to attach it to an organization as well?
						
						// Maybe I should attach it directly to the organization and
						// then send a message somehow that it should be put in my
						// calendar as well .. hm that actually does sound
						// like a solution.
						'alias_to_org' => $args['obj_inst']->id(),
						'reltype_org' => $reltype,
						'class' => 'planner',
						'id' => $this->cal_id,
						'group' => 'add_event',
						'clid' => $clid,
						'action' => 'change',
						'title' => urlencode($classinf["name"].': '.$args['obj_inst']->name()),
						'parent' => $parents[$reltype],
						'return_url' => urlencode(aw_global_get('REQUEST_URI')),
					));
					$has_parent = isset($parents[$val]) && $parents[$val];
					$disabled = $has_parent ? false : true;
					$toolbar->add_menu_item(array(
						"parent" => "calendar_sub",
						"title" => $has_parent ? "" : t("Kalender või kalendri sündmuste kataloog määramata"),
						"text" => sprintf(t("Lisa %s"),$classinf["name"]),
						"disabled" => $has_parent ? false : true,
						"link" => $has_parent ? $url : "",
					));
				};
			};
		};
		
		$ui = get_instance(CL_USER);
		$my_org_id = $ui->get_current_company();
		$toolbar->add_menu_item(array(
			"parent" => "calendar_sub",
			"title" => t("Lisa pakkumine"),
			"text" => t("Lisa pakkumine"),
			"link" => $this->mk_my_orb("new", array(
				"alias_to_org" => $args["obj_inst"]->id(), 
				"alias_to" => $my_org_id,
				"reltype" => 9
			), CL_CRM_OFFER),
		));
		
		if (!empty($this->cal_id))	
		{
			$toolbar->add_button(array(
				"name" => "user_calendar",
				"tooltip" => t("Kasutaja kalender"),
				"url" => $this->mk_my_orb('change', array(
						'id' => $this->cal_id,
						'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						"group" => "views"
					),'planner'),
				"onClick" => "",
				"img" => "icon_cal_today.gif",
				"class" => "menuButton",
			));
		}
		
	}

	function do_offer_tree_leafs(&$tree,&$obj,$this_level_id,&$node_id)
	{	
		$customers = $this->get_customers_for_category($obj->id());
		if(is_array($customers))
		{
			foreach ($customers as $customer)
			{
				$cobj = &obj($customer);
				$tree->add_item($this_level_id, array(
					'id' => ++$node_id,
					'iconurl' => icons::get_icon_url($cobj->class_id()),
					'name' => $cobj->id()==$_GET["org_id"]?"<b>".$cobj->name()."</b>":$cobj->name(),
					'url' => aw_url_change_var(array(
							"org_id" => $cobj->id()
					)),
				));
			}
		}
	}

	/**
		@attrib name=mark_proj_done
	**/
	function mark_proj_done($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array("oid" => $arr["sel"]));
			$ol->foreach_o(array("func" => "set_prop", "params" => array("state", PROJ_DONE), "save" => true));
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=mark_tasks_done
		@param sel optional
		@param post_ru required
	**/
	function mark_tasks_done($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array("oid" => $arr["sel"]));
			$ol->foreach_o(array("func" => "set_prop", "params" => array("is_done", OBJ_IS_DONE), "save" => true));
		}
		return $arr["post_ru"];
	}

	function get_my_projects()
	{
		$conns = new connection();
		$conns_ar = $conns->find(array(
			"from.class_id" => CL_PROJECT,
			"to" => aw_global_get("uid_oid"),
			"type" =>  2,
		));
		$conns_ol = new object_list();
		foreach($conns_ar as $con)
		{
			$conns_ol->add($con["from"]);
		}

		$u = get_instance(CL_USER);
		$pers = $u->get_current_person();
		$conns_ar = $conns->find(array(
			"from.class_id" => CL_PROJECT,
			"to" => $pers,
			"type" =>  2,
		));
		foreach($conns_ar as $con)
		{
			$conns_ol->add($con["from"]);
		}

		if ($conns_ol->count())
		{
			$conns_ol = new object_list(array(
				"oid" => $conns_ol->ids(),
				"class_id" => CL_PROJECT,
				"state" => new obj_predicate_not(PROJ_DONE),
				"lang_id" => array()
			));
		}
		/*$conns_ol = new object_list(array(
			"class_id" => CL_PROJECT,
			"lang_id" => array(),
			"site_id" => array()
		));*/
		return $conns_ol->ids();
	}

	function get_my_customers($co = NULL)
	{
		$projs = $this->get_my_projects();

		$c = new connection();
		$conns = $c->find(array(
			"from.class_id" => CL_PROJECT,
			"type" => "RELTYPE_PARTICIPANT",
			"from" => $projs,
			"to.class_id" => array(CL_CRM_COMPANY,CL_CRM_PERSON)
		));

		$ret = array();
		foreach($conns as $c)
		{
			if ($c["to.class_id"] == CL_CRM_PERSON)
			{
				$p = obj($c["to"]);
				if (!$p->prop("is_customer"))
				{
					continue;
				}
			}
			$ret[] = $c["to"];
		}
		
		// get cust conns for co
		if ($co)
		{
			foreach($co->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
			{
				$ret[] = $c->prop("to");
			}
		}

		// add all customers to whom I am cust mgr
		$u = get_instance(CL_USER);
		$p = $u->get_current_person();
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"client_manager" => $p
		));
		foreach($ol->ids() as $_id)
		{
			$ret[] = $_id;
		}

		$ret = array_unique($ret);
		return $ret;
	}

	function get_my_tasks()
	{
		$u = get_instance(CL_USER);
		$c = new connection();
		$cs = $c->find(array(
			"from" => $u->get_current_person(),
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_PERSON_TASK",
		));
		$ret = array();
		foreach($cs as $c)
		{
			$ret[] = $c["to"];
		}
		return $ret;
	}

	function get_my_meetings()
	{
		$u = get_instance(CL_USER);
		$c = new connection();
		$cs = $c->find(array(
			"from" => $u->get_current_person(),
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_PERSON_MEETING",
		));
		$ret = array();
		foreach($cs as $c)
		{
			$ret[] = $c["to"];
		}
		return $ret;
	}

	function get_my_calls()
	{
		$u = get_instance(CL_USER);
		$c = new connection();
		$cs = $c->find(array(
			"from" => $u->get_current_person(),
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_PERSON_CALL",
		));
		$ret = array();
		foreach($cs as $c)
		{
			$ret[] = $c["to"];
		}
		return $ret;
	}

	function get_my_offers()
	{
		$u = get_instance(CL_USER);
		$c = new connection();
		$cs = $c->find(array(
			"to" => $u->get_current_person(),
			"from.class_id" => CL_CRM_OFFER,
			"type" => "RELTYPE_SALESMAN",
		));
		$ret = array();
		foreach($cs as $c)
		{
			$ret[] = $c["from"];
		}
		return $ret;
	}

	function get_my_actions()
	{
		$u = get_instance(CL_USER);
		$c = new connection();
		$cs = $c->find(array(
			"from" => $u->get_current_person(),
			"from.class_id" => CL_CRM_PERSON,
			"type" => array("RELTYPE_PERSON_TASK", "RELTYPE_PERSON_MEETING", "RELTYPE_PERSON_CALL"),
		));
		$ret = array();
		foreach($cs as $c)
		{
			if ($this->can("view", $c["to"]))
			{
				$ret[] = $c["to"];
			}
		}

		foreach($this->get_my_offers() as $ofid)
		{
			if ($this->can("view", $c["to"]))
			{
				$ret[] = $ofid;
			}
		}
		return $ret;
	}

	/**
		@attrib name=create_bill
	**/
	function create_bill($arr)
	{
		// create a bill for all selected tasks 
		$bill = obj();
		$bill->set_class_id(CL_CRM_BILL);
		$bill->set_parent($arr["id"]);
		$bill->save();

		$ser = get_instance(CL_CRM_NUMBER_SERIES);
		$bill->set_prop("bill_no", $ser->find_series_and_get_next(CL_CRM_BILL));
		$bill->set_name(sprintf(t("Arve nr %s"), $bill->prop("bill_no")));

		if (is_oid($arr["proj"]))
		{
			$proj = obj($arr["proj"]);
			$cust = $proj->get_first_obj_by_reltype("RELTYPE_ORDERER");
			$impl = $proj->prop("orderer");
			if (is_array($impl))
			{
				$impl = reset($impl);
			}
			$bill->set_prop("customer", $impl);
			$bill->set_prop("impl", $proj->prop("implementor"));
		}
		else
		if (is_oid($arr["cust"]))
		{
			$cust = obj($arr["cust"]);
			$u = get_instance(CL_USER);
			$bill->set_prop("impl", $u->get_current_company());
		}

		if ($cust)
		{
			$bill->set_prop("customer", $cust->id());
		}

		$bill->save();

		foreach(safe_array($arr["sel"]) as $task)
		{
			$bill->connect(array(
				"to" => $task,
				"reltype" => "RELTYPE_TASK"
			));

			$task_o = obj($task);
			$task_o->connect(array(
				"to" => $bill->id(),
				"type" => "RELTYPE_BILL"
			));

			// add bill id to all rows in task that don't have one already
			/*$rows = safe_array($task_o->meta("rows"));
			foreach($rows as $idx => $row)
			{
				if (!$row["bill_id"] && $row["on_bill"])
				{
					$rows[$idx]["bill_id"] = $bill->id();
				}
			}
			$task_o->set_meta("rows", $rows);*/
			foreach($task_o->connections_from(array("type" => "RELTYPE_ROW")) as $c)
			{
				$row = $c->to();
				if (!$row->prop("bill_id") && $row->prop("on_bill"))
				{
					$row->set_prop("bill_id", $bill->id());
					$row->save();
				}
			}
			$task_o->save();
		}

		return html::get_change_url($bill->id(), array("return_url" => urlencode(aw_url_change_var("proj", NULL, $arr["post_ru"]))));
	}

	/** 
		@attrib name=add_proj_to_co_as_ord
	**/
	function add_proj_to_co_as_ord($arr)
	{
		return html::get_new_url(
				CL_PROJECT, 
				$arr["id"], 
				array(
					"connect_impl" => reset($arr["check"]),
					"return_url" => urlencode($arr["post_ru"]),
					"connect_orderer" => $arr["id"],
				)
		);
	}

	/** 
		@attrib name=add_proj_to_co_as_impl
	**/
	function add_proj_to_co_as_impl($arr)
	{
		return html::get_new_url(
				CL_PROJECT, 
				$arr["id"], 
				array(
					"connect_impl" => $arr["id"],
					"return_url" => urlencode($arr["post_ru"]),
					"connect_orderer" => reset($arr["check"]),
				)
		);
	}

	/**
		@attrib name=submit_delete_docs
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

	/**
		@attrib name=add_task_to_co
	**/
	function add_task_to_co($arr)
	{
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		return $this->mk_my_orb('new',array(
			'alias_to_org' => reset($arr["check"]),
			'reltype_org' => 13,
			'add_to_cal' => $this->cal_id,
			'title' => t("Toimetus"),
			'parent' => $arr["id"],
			'return_url' => urlencode($arr["post_ru"])
		), CL_TASK);
		
	}

	/**
		@attrib name=add_meeting_to_co
	**/
	function add_meeting_to_co($arr)
	{
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		return $this->mk_my_orb('new',array(
			'alias_to_org' => reset($arr["check"]),
			'reltype_org' => 13,
			'class' => 'planner',
			'id' => $this->cal_id,
			'group' => 'add_event',
			'clid' => CL_CRM_MEETING,
			'action' => 'change',
			'title' => t("Kohtumine"),
			'parent' => $arr["id"],
			'return_url' => urlencode($arr["post_ru"])
		));
		
	}

	/**
		@attrib name=add_offer_to_co
	**/
	function add_offer_to_co($arr)
	{
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		return $this->mk_my_orb('new',array(
			'alias_to_org' => reset($arr["check"]),
			'reltype_org' => 13,
			'class' => 'planner',
			'id' => $this->cal_id,
			'group' => 'add_event',
			'clid' => CL_CRM_OFFER,
			'action' => 'change',
			'title' => t("Pakkumine"),
			'parent' => $arr["id"],
			'return_url' => urlencode($arr["post_ru"])
		));
		
	}

	/**
		@attrib name=add_task_to_proj
	**/
	function add_task_to_proj($arr)
	{
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		$proj = reset($arr["sel"]);
		$o = obj($proj);

		return $this->mk_my_orb('new',array(
			'alias_to_org' => reset($o->prop("orderer")),
			'reltype_org' => 13,
			'add_to_cal' => $this->cal_id,
			'title' => t("Toimetus"),
			'parent' => $arr["id"],
			'return_url' => urlencode($arr["post_ru"]),
			"set_proj" => $proj
		), CL_TASK);
		
	}

	/**
		@attrib name=add_meeting_to_proj
	**/
	function add_meeting_to_proj($arr)
	{
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		$proj = reset($arr["sel"]);
		$o = obj($proj);

		return $this->mk_my_orb('new',array(
			'alias_to_org' => $o->prop("orderer"),
			'reltype_org' => 13,
			'class' => 'planner',
			'id' => $this->cal_id,
			'group' => 'add_event',
			'clid' => CL_CRM_MEETING,
			'action' => 'change',
			'title' => t("Kohtumine"),
			'parent' => $arr["id"],
			'return_url' => urlencode($arr["post_ru"]),
			"set_proj" => $proj
		));
		
	}

	/**
		@attrib name=add_offer_to_proj
	**/
	function add_offer_to_proj($arr)
	{
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		$proj = reset($arr["sel"]);
		$o = obj($proj);

		return $this->mk_my_orb('new',array(
			'alias_to_org' => $o->prop("orderer"),
			'reltype_org' => 13,
			'class' => 'planner',
			'id' => $this->cal_id,
			'group' => 'add_event',
			'clid' => CL_CRM_OFFER,
			'action' => 'change',
			'title' => t("Pakkumine"),
			'parent' => $arr["id"],
			'return_url' => urlencode($arr["post_ru"]),
			"set_proj" => $proj
		));
		
	}

	/**
		@attrib name=mark_p_as_important
	**/
	function mark_p_as_important($arr)
	{	
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());

		foreach(safe_array($arr["check"]) as $pers)
		{
			$p->connect(array(
				"to" => $pers,
				"reltype" => "RELTYPE_IMPORTANT_PERSON"
			));
		}

		return $arr["post_ru"];
	}

	/**
		@attrib name=tasks_switch_to_cal_view
	**/
	function tasks_switch_to_cal_view($arr)
	{
		aw_session_set("crm_task_view", CRM_TASK_VIEW_CAL);
		return $arr["post_ru"];
	}

	/**
		@attrib name=tasks_switch_to_table_view
	**/
	function tasks_switch_to_table_view($arr)
	{
		aw_session_del("crm_task_view");
		return $arr["post_ru"];
	}

	/**
		@attrib name=go_to_create_bill
	**/
	function go_to_create_bill($arr)
	{
		return $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"group" => "bills",
			"cust" => reset($arr["check"]),
			"return_url" => urlencode($arr["post_ru"])
		));
	}

	function get_projects_for_customer($co)
	{
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
			"CL_PROJECT.RELTYPE_ORDERER.id" => $co->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		$ol2 = new object_list(array(
			"class_id" => CL_PROJECT,
			"CL_PROJECT.RELTYPE_IMPLEMENTOR.id" => $co->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		return $ol->ids() + $ol2->ids();
	}

	/**
		@attrib name=delete_projs
	**/
	function delete_projs($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array("oid" => $arr["sel"]));
			$ol->delete();
		}

		return $arr["post_ru"];
	}

	/**
		@attrib name=delete_bills
	**/
	function delete_bills($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array("oid" => $arr["sel"]));
			$ol->delete();
		}
		return $arr["post_ru"];
	}

	function _get_cust_contract_creator($arr)
	{
		// list of all persons in my company
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();
		$arr["prop"]["options"] = $this->get_employee_picker(obj($co), true);
		if (($rel = $this->get_cust_rel($arr["obj_inst"])))
		{
			$arr["prop"]["value"] = $rel->prop("cust_contract_creator");
		}

		if (!isset($arr["prop"]["options"][$arr["prop"]["value"]]) && $this->can("view", $arr["prop"]["value"]))
		{
			$v = obj($arr["prop"]["value"]);
			$arr["prop"]["options"][$arr["prop"]["value"]] = $v->name();
		}
	}

	function get_employee_picker($co, $add_empty = false, $important_only = false)
	{
		if ($add_empty)
		{
			$res = array("" => t("--vali--"));
		}
		else
		{
			$res = array();
		}
		$this->get_all_workers_for_company($co, $res);
		if (!count($res))
		{
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
		return $res;
	}

	function __person_name_sorter($a, $b)
	{
		list($a_fn, $a_ln) = explode(" ", $a);
		list($b_fn, $b_ln) = explode(" ", $b);
		if ($a_ln == $b_ln)
		{
			return strcmp($a_fn, $b_fn);
		}
		return strcmp($a_ln, $b_ln);
	}

	function _gen_company_code($co)
	{
		if ($co->prop("code") == "" && is_oid($ct = $co->prop("contact")) && $this->can("view", $ct))
		{
			$ct = obj($ct);
			$rk = $ct->prop("riik");
			if (is_oid($rk) && $this->can("view", $rk))
			{
				$rk = obj($rk);
				$code = substr(trim($rk->ord()), 0, 1);
				// get number of companies that have this country as an address
				$ol = new object_list(array(
					"class_id" => CL_CRM_COMPANY,
					"CL_CRM_COMPANY.contact.riik.name" => $rk->name()
				));
				$ol2 = new object_list(array(
					"class_id" => CL_CRM_PERSON,
					"CL_CRM_PERSON.address.riik.name" => $rk->name()
				));
				$code .= sprintf("%04d", $ol->count() + $ol2->count());
				$co->set_prop("code", $code);
			}
		}
	}

	function callback_pre_save($arr)
	{
		$this->_gen_company_code($arr["obj_inst"]);
	}

	/**
		@attrib name=save_bill_list
	**/
	function save_bill_list($arr)
	{
		foreach(safe_array($arr["bill_states"]) as $bill_id => $state)
		{
			$bill = obj($bill_id);
			if ($bill->prop("state") != $state)
			{
				$bill->set_prop("state", $state);
				$bill->save();
			}
		}

		return $arr["post_ru"];
	}

	/**
		@attrib name=delete_tasks
		@param sel optional
		@param post_ru required
	**/
	function delete_tasks($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			foreach($arr["sel"] as $id)
			{
				$o = obj($id);
				$o->delete();
			}
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=create_new_monthly_bill

		@param id required type=int acl=view
		@param co required type=int acl=view
		@param post_ru optional
	**/
	function create_new_monthly_bill($arr)
	{
		if (!empty($arr["id"]) && empty($arr["sel"]))
		{
			$arr["sel"] = array($arr["id"]);
		}

		foreach(safe_array($arr["sel"]) as $bill_id)
		{
			$b = obj($bill_id);

			/// copy
			$n = obj();
			$n->set_class_id(CL_CRM_BILL);
			$n->set_parent($b->parent());
			$n->save();
			$ser = get_instance(CL_CRM_NUMBER_SERIES);
			$n->set_prop("bill_no", $ser->find_series_and_get_next(CL_CRM_BILL));
			$n->set_name(sprintf(t("Arve nr %s"), $n->prop("bill_no")));
			$n->set_prop("bill_date", $b->prop("bill_date"));
			$n->set_prop("bill_due_date_days", $b->prop("bill_due_date_days"));
			$n->set_prop("bill_due_date", $b->prop("bill_due_date"));
			$n->set_prop("customer", $b->prop("customer"));
			$n->set_prop("impl", $b->prop("impl"));
			$n->set_prop("notes", $b->prop("notes"));
			$n->set_prop("disc", $b->prop("disc"));
			$n->set_prop("sum", $b->prop("sum"));
			$n->save();

			// connections
			foreach($b->connections_from() as $con)
			{
				$n->connect(array(
					"to" => $con->prop("to"),
					"reltype" => $con->prop("reltype")
				));
			}
		}

		if (count(safe_array($arr["sel"])) == 1)
		{
			return html::get_change_url($n->id(), array("return_url" => urlencode($arr["post_ru"])));
		}
		else
		{
			return $this->mk_my_orb("change", array(
				"id" => $arr["id"], 
				"group" => "bills_list",
				"return_url" => urlencode($arr["post_ru"])
			));
		}
	}

	// handler for address save message
	function on_save_address($arr)
	{
		// get all companies with empty codes that have this country
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"contact" => $arr["oid"],
			"code" => ''
		));
		foreach($ol->arr() as $o)
		{
			$i = $o->instance();
			$i->_gen_company_code($o);
			aw_disable_acl();
			$o->save();
			aw_restore_acl();
		}
	}


	// Finds first matching CRM_FIELD object and it's properties
	//  oid - oid of CRM_COMPANY
	//  type - FIELD type (suffix of class_id) - eg ACCOMMODATION for CL_CRM_FIELD_ACCOMMODATION
	//  clid - FIELD class id (alternative method)
	function find_crm_field_obj($arr)
	{
		$c = obj($arr['oid']);
		if (!is_object($c) || $c->class_id() != CL_CRM_COMPANY || (empty($arr['type']) && empty($arr['clid'])) ) 
		{
			return;
		}
		if (empty($arr['clid']))
		{
			$type = constant('CL_CRM_FIELD_'.strtoupper($arr['type']));
		}
		else
		{
			$type = $arr['clid'];
		}
		if (!is_numeric($type))
		{
			return;
		}
		
		// Get first object reltype RELTYPE_FIELD of class CL_CRM_FIELD_ACCOMMODATION
		$conns = $c->connections_from(array(
			'type' => 'RELTYPE_FIELD',
		));
		$found = false;
		foreach ($conns as $con)
		{
			$o = $con->to();
			if ($o->class_id() == $type)
			{
				$found = true;
				break;
			}
		}
		if ($found)
		{
			return $o;
		}
	}				

	/**
		@attrib name=cut_docs
	**/
	function cut_docs($arr)
	{
		$_SESSION["crm_cut_docs"] = safe_array($arr["sel"]);
		return $arr["post_ru"];
	}

	/**
		@attrib name=submit_paste_docs
	**/
	function submit_paste_docs($arr)
	{
		$fld = $arr["tf"];
		if (!$fld)
		{
			$i = get_instance("applications/crm/crm_company_docs_impl");
			$fld = $i->_init_docs_fld(obj($arr["id"]));
			$fld = $fld->id();
		}
		foreach(safe_array($_SESSION["crm_cut_docs"]) as $did)
		{
			$o = obj($did);
			$o->set_parent($fld);
			$o->save();
		}
		unset($_SESSION["crm_cut_docs"]);
		return $arr["post_ru"];
	}

	/**
		@attrib name=res_delete
	**/
	function res_delete($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array("oid" => $arr["sel"]));
			$ol->delete();
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=res_paste
	**/
	function res_paste($arr)
	{
		if (is_array($_SESSION["co_res_cut"]) && count($_SESSION["co_res_cut"]))
		{
			$ol = new object_list(array("oid" => $_SESSION["co_res_cut"]));
			$ol->set_parent($arr["tf"]);
		}
		$_SESSION["co_res_cut"] = false;
		return $arr["post_ru"];
	}

	/**
		@attrib name=res_cut
	**/
	function res_cut($arr)
	{
		$_SESSION["co_res_cut"] = $arr["sel"];
		return $arr["post_ru"];
	}

	function get_my_resources()
	{
		$i = get_instance("applications/crm/crm_company_res_impl");
		$u = get_instance(CL_USER);

		$ot = new object_tree(array(
			"class_id" => array(CL_MENU, CL_MRP_RESOURCE),
			"parent" => $i->_get_res_parent(obj($u->get_current_company()))
		));
		$ol = $ot->to_list();
		$ret = new object_list();

		foreach($ol->arr() as $o)
		{
			if ($o->class_id() == CL_MRP_RESOURCE)
			{
				$ret->add($o);
			}
		}
		return $ret;
	}

	function callback_get_cfgform($arr)
	{
		// if this is the current users employer, do nothing
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();
		if ($co == $arr["obj_inst"]->id())
		{
			$s = get_instance(CL_CRM_SETTINGS);
			if (($o = $s->get_current_settings()))
			{
				return $o->prop("work_cfgform");
			}
		}

		// find the crm settings object for the current user
		$s = get_instance(CL_CRM_SETTINGS);
		if (($o = $s->get_current_settings()))
		{
			return $o->prop("s_cfgform");
		}
	}

	function on_create_customer($arr)
	{
		$conn = $arr["connection"];
		$buyer_cat = $conn->from();

		// find the company from the category
		while (1)
		{
			$to = $buyer_cat->connections_to(array(
				"from.class_id" => CL_CRM_COMPANY,
				"type" => "RELTYPE_CATEGORY"
			));
			if (count($to))
			{
				$buyer_c = reset($to);
				$buyer = $buyer_c->from();
				break;
			}
			$to = $buyer_cat->connections_to(array(
				"from.class_id" => CL_CRM_CATEGORY,
				"type" => "RELTYPE_CATEGORY"
			));
			if (!count($to))
			{
				error::raise(array(
					"id" => "ERR_NO_PREV_CAT",
					"msg" => sprintf(t("crm_company::on_create_customer(): category %s has no parent company or category!"), $buyer_cat->id())
				));
			}
			$c = reset($to);
			$buyer_cat = $c->from();
		}

		$seller = $conn->to();

		// add customer relation object if it does not exist already
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
			"buyer" => $buyer->id(),
			"seller" => $seller->id()
		));

		if (!$ol->count())
		{
			$o = obj();
			$o->set_class_id(CL_CRM_COMPANY_CUSTOMER_DATA);
			$o->set_name("Kliendisuhe ".$buyer->name()." => ".$seller->name());
			$o->set_parent($buyer->id()); 
			$o->set_prop("seller", $buyer->id()); // yes this is correct, cause I'm a lazy iduit
			$o->set_prop("buyer", $seller->id());
			$o->save();
		}
	}

	function get_cust_rel($view_co)
	{
		if (!is_oid($view_co->id()))
		{
			return false;
		}
		$u = get_instance(CL_USER);
		$my_co = $u->get_current_company();
		if ($view_co->id() == $my_co)
		{
			return false;
		}
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
			"buyer" => $view_co->id(),
			"seller" => $my_co
		));
		if ($ol->count())
		{
			return $ol->begin();
		}
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "general")
		{
			$u = get_instance(CL_USER);
			$co = $u->get_current_company();
			if ($co == $arr["obj_inst"]->id())
			{
				$arr["caption"] = t("B&uuml;roo");
			}	
		}
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	/** 
		@attrib name=get_company_count_by_name

		@param co_name optional
	**/
	function get_company_count_by_name($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"name" => $arr["co_name"],
			"lang_id" => array(),
			"site_id" => array()
		));
		die($ol->count()."\n");
	}
	
	/**
		@attrib name=go_to_first_co_by_name
		@param co_name optional
		@param return_url optional
	**/
	function go_to_first_co_by_name($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"name" => $arr["co_name"],
			"lang_id" => array(),
			"site_id" => array()
		));
		$o = $ol->begin();
		header("Location: ".html::get_change_url($o->id())."&warn_conflicts=1&return_url=".urlencode($arr["return_url"]));
		die();
	}

	/**
		@attrib name=disp_conflict_pop
		@param id required
	**/
	function disp_conflict_pop($arr)
	{
		$co = obj($arr["id"]);
		$u = get_instance(CL_USER);
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
			"CL_PROJECT.RELTYPE_SIDE.name" => $co->name(),
			//"CL_PROJECT.RELTYPE_ORDERER" => $u->get_current_company(),
			"lang_id" => array(),
			"site_id" => array()
		));
		$ret = t("Konfliktsed projektid:<br>");
		foreach($ol->arr() as $o)
		{
			$ret .= html::href(array(
				"url" => html::get_change_url($o->id()),
				"caption" => $o->name(),
				"target" => "_blank"
			))."<br>";
		}
		return $ret;
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
					$link = $this->mk_my_orb("disp_conflict_pop", array("id" => $arr["obj_inst"]->id()));
					return "aw_popup_scroll('$link','confl','200','200');"; 
				}
			}
			return "";
		}
		return 
		"function aw_submit_handler() {".
		// fetch list of companies with that name and ask user if count > 0
		"var url = '".$this->mk_my_orb("get_company_count_by_name")."';".
		"url = url + '&co_name=' + document.changeform.name.value;".
		"num= parseInt(aw_get_url_contents(url));".
		"if (num >0)
		{
			var ansa = confirm('Sellise nimega organisatsioon on juba olemas. Kas soovite minna selle objekti muutmisele?');
			if (ansa)
			{
				window.location = '".$this->mk_my_orb("go_to_first_co_by_name", array("return_url" => urlencode($arr["request"]["return_url"])))."&co_name=' + document.changeform.name.value; 
				return false;
			}
			return false;
		}".
		"return true;}";
	}

	function callback_gen_forum($arr)
	{
		// check/create forum
		$forum = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_FORUM");
		if (!$forum)
		{
			$o = obj();
			$o->set_class_id(CL_FORUM_V2);
			$o->set_parent($arr["obj_inst"]->id());
			$o->set_name(sprintf(t("%s foorum"), $arr["obj_inst"]->name()));
			$o->save();
			$arr["obj_inst"]->connect(array(
				"to" => $o->id(),
				"type" => "RELTYPE_FORUM"
			));

			$fi = $o->instance();
			$fi->callback_post_save(array(
				"obj_inst" => $o,
				"request" => array("new" => 1)
			));
			$forum = $o;
		}

		$fi = $forum->instance();
		return $fi->callback_gen_contents(array(
			"obj_inst" => $forum,
			"request" => $arr["request"],
		));
	}

	function _proc_server_folder($arr)
	{
		if ($arr["prop"]["value"] == "")
		{
			return;
		}

		if ($arr["prop"]["value"] == $arr["obj_inst"]->prop("server_folder"))
		{
			return;
		}

		// if changed, recreate
		$srv = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_SERVER_FILES");
		if (!$srv)
		{
			$srv = obj();
			$srv->set_class_id(CL_SERVER_FOLDER);
			$srv->set_parent($arr["obj_inst"]->id());
			$srv->set_name(sprintf(t("%s serveri failid"), $arr["obj_inst"]->name()));
		}

		$srv->set_prop("folder", $arr["prop"]["value"]);
		$srv->save();
	}

	/**
		@attrib name=name_autocomplete_source
		@param name optional
	**/
	function name_autocomplete_source($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"name" => $arr["name"]."%",
			"lang_id" => array(),
			"site_id" => array()
		));
		$ars = $ol->names();
		/*foreach($ol->names() as $name)
		{
			$ars[] = $name."=>".$name;
		}*/
		header("Content-type: text/html; charset=".aw_global_get("charset"));
		die(join("\n", $ars)."\n");
	}
	
	/**
		@attrib name=submit_delete_sects
	**/
	function submit_delete_sects($arr)
	{
		foreach(safe_array($arr["sel"]) as $oid)
		{
			$o = obj($oid);
			$o->delete();
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=save_as_customer
	**/
	function save_as_customer($arr)
	{
		// add all custs in $check as cust to $cust_cat
		$cat = obj($arr["cust_cat"]);
		foreach(safe_array($arr["check"]) as $cust)
		{
			if (!$cat->is_connected_to(array("to" => $cust)))
			{
				$cat->connect(array(
					"to" => $cust,
					"type" => "RELTYPE_CUSTOMER"
				));
			}
		}

		return $arr["post_ru"];
	}

	function on_create_company($arr)
	{
		// make sure all companies added are added under the current user's company
		$o = obj($arr["oid"]);
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();
		if ($co != $o->parent())
		{
			$o->set_parent($co);
			$o->save();
		}
	}
	
	/**
		@attrib name=save_report
	**/
	function save_report($arr)
	{
		$o = obj();
		$arr = $_GET;
		$o->set_class_id(CL_CRM_REPORT_ENTRY);
		$o->set_parent($arr["id"]);
		$o->set_prop("cust", $arr["stats_s_cust"]);
		$o->set_prop("cust_type", $arr["stats_s_cust_type"]);
		$o->set_prop("proj", $arr["stats_s_proj"]);
		$o->set_prop("worker", $arr["stats_s_worker"]);
		$o->set_prop("worker_sel", $arr["stats_s_worker_sel"]);
		$o->set_prop("from", date_edit::get_timestamp($arr["stats_s_from"]));
		$o->set_prop("to", date_edit::get_timestamp($arr["stats_s_to"]));
		$o->set_prop("time_sel", $arr["stats_s_time_sel"]);
		$o->set_prop("state", $arr["stats_s_state"]);
		$o->set_prop("bill_state", $arr["stats_s_bill_state"]);
		$o->set_prop("only_billable", $arr["stats_s_only_billable"]);
		$o->set_prop("area", $arr["stats_s_area"]);
		$o->set_prop("res_type", $arr["stats_s_res_type"]);
		$o->save();
		return html::get_change_url($o->id(), array("return_url" => urlencode($arr["post_ru"])));
	}
}
?>
