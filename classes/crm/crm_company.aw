<?php
/*
//on_connect_person_to_org handles the connection from person to section too
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_PERSON, on_connect_person_to_org)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_PERSON, on_disconnect_person_from_org)
HANDLE_MESSAGE_WITH_PARAM(MSG_EVENT_ADD, CL_CRM_PERSON, on_add_event_to_person)

@classinfo relationmgr=yes syslog_type=ST_CRM_COMPANY
@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid

@default table=objects
@default group=general_sub

@property navtoolbar type=toolbar store=no no_caption=1 group=general_sub,all_actions,meetings,tasks,calls editonly=1

@property name type=textbox size=30 maxlength=255 table=objects
@caption Organisatsiooni nimi

@property comment type=textarea cols=65 rows=3 table=objects
@caption Kommentaar

@property extern_id type=hidden table=kliendibaas_firma field=extern_id 

@property reg_nr type=textbox size=10 maxlength=20 table=kliendibaas_firma
@caption Registri number

//@property ettevotlusvorm type=relpicker reltype=RELTYPE_ETTEVOTLUSVORM table=kliendibaas_firma 
//@caption Õiguslik vorm

@property ettevotlusvorm type=select table=kliendibaas_firma
@caption Õiguslik vorm

//@property ettevotlusvorm type=objpicker clid=CL_CRM_CORPFORM table=kliendibaas_firma 
//@caption Õiguslik vorm



@property logo type=textbox size=40 method=serialize field=meta table=objects
@caption Organisatsiooni logo(url)

@property firmajuht type=chooser orient=vertical table=kliendibaas_firma  editonly=1
@caption Kontaktisik

@property year_founded type=date_select table=kliendibaas_firma year_from=1800 default=-1
@caption Asutatud

@property priority type=textbox table=kliendibaas_firma 
@caption Prioriteet

------ Üldine - Tegevused grupp -----
@default group=org_sections

@property kaubamargid type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Kaubamärgid

@property tegevuse_kirjeldus type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Tegevuse kirjeldus

@property tooted type=relpicker reltype=RELTYPE_TOOTED method=serialize field=meta table=objects
@caption Tooted

@property pohitegevus type=relpicker reltype=RELTYPE_TEGEVUSALAD table=kliendibaas_firma
@caption Põhitegevus

------ Yldine - Lisainfo grupp----------
@default group=add_info

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

------ Yldine - kasutajate seaded grupp

@property do_create_users type=checkbox ch_value=1 table=objects field=meta method=serialize group=user_settings
@caption Kas isikud on kasutajad

--------------------------------------
@default group=oldcontacts

@property addresslist type=text store=no no_caption=1 group=oldcontacts
@caption Aadress

@property old_human_resources type=table store=no no_caption=1 group=oldcontacts
@caption Nimekiri

@default group=contacts2

@layout hbox_toolbar type=hbox group=contacts2

@property contact_toolbar type=toolbar no_caption=1 store=no parent=hbox_toolbar
@caption "The Green Button"

@layout hbox_others type=hbox group=contacts2 width=20%:80%

@layout vbox_contacts_left type=vbox parent=hbox_others group=contacts2

@property unit_listing_tree type=treeview no_caption=1 store=no parent=vbox_contacts_left 
@caption Puu

@layout vbox_contacts_right type=vbox parent=hbox_others group=contacts2

@property human_resources type=table store=no no_caption=1 parent=vbox_contacts_right
@caption Inimesed

@property contact_search_firstname type=textbox size=30 store=no parent=vbox_contacts_right
@caption Eesnimi

@property contact_search_lastname type=textbox size=30 store=no parent=vbox_contacts_right
@caption Perenimi

@property contact_search_code type=textbox size=30 store=no parent=vbox_contacts_right
@caption Isikukood

@property contact_search type=hidden store=no no_caption=1 parent=vbox_contacts_right value=1
@caption contact_search

@property contact_search_submit type=submit store=no parent=vbox_contacts_right
@caption Otsi

@property contacts_search_results type=table store=no no_caption=1 parent=vbox_contacts_right
@caption Otsingutulemused

@default group=cedit

@property contact type=relpicker reltype=RELTYPE_ADDRESS table=kliendibaas_firma
@caption Vaikimisi aadress

@property phone_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_PHONE props=name
@caption Telefon

@property url_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_URL props=name
@caption Veebiaadress

@property email_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_EMAIL props=mail
@caption E-posti aadressid

@property telefax_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_TELEFAX props=name
@caption Faks

@default group=tasks_overview

@property tasks_call type=text store=no no_caption=1
@caption Kõned

@property org_add_call type=releditor reltype=RELTYPE_CALL props=name,comment,start1,is_done,content store=no
@caption Lisa kõne

@default group=overview

@property org_actions type=calendar no_caption=1 group=all_actions viewtype=relative
@caption org_actions

@property org_calls type=calendar no_caption=1 group=calls viewtype=relative
@caption Kõned

@property org_meetings type=calendar no_caption=1 group=meetings viewtype=relative
@caption Kohtumised

@property org_tasks type=calendar no_caption=1 group=tasks viewtype=relative
@caption Toimetused

//@property t1 type=text subtitle=1 group=jobs store=no
//@caption Aktiivsed

//@property jobsact type=table no_caption=1 group=jobs

//@property t2 type=text subtitle=1 group=jobs store=no
//@caption Mitteaktiivsed

//@property jobsnotact type=table no_caption=1 group=jobs

-------------- PERSONALI PROPERTID ---------------

@layout personal_toolbar type=hbox group=personal_offers
@layout personal_tree_table type=hbox group=personal_offers width=20%:80%
@layout personal_hbox_tree type=vbox group=personal_offers parent=personal_tree_table
@layout personal_hbox_table type=vbox group=personal_offers parent=personal_tree_table

@layout personal_toolbar_cand type=hbox group=personal_candits
@layout personal_tree_table_cand type=hbox group=personal_candits width=20%:80%
@layout personal_hbox_tree_cand type=vbox  group=personal_candits parent=personal_tree_table_cand
@layout personal_hbox_table_cand type=vbox group=personal_candits parent=personal_tree_table_cand

@property personal_offers_toolbar type=toolbar group=personal_offers store=no no_caption=1 parent=personal_toolbar
@property unit_listing_tree_personal type=treeview no_caption=1 store=no parent=personal_hbox_tree group=personal_offers
@property personal_offers_table type=table group=personal_offers no_caption=1 parent=personal_hbox_table

@property personal_candidates_toolbar type=toolbar group=personal_candits store=no no_caption=1 parent=personal_toolbar_cand
@property unit_listing_tree_candidates type=treeview no_caption=1 store=no group=personal_candits parent=personal_hbox_tree_cand
@property personal_candidates_table type=table group=personal_candits no_caption=1 parent=personal_hbox_table_cand

---------------------------------------------------

// disabled until the functionality is coded
//@property org_toolbar type=toolbar group=customers store=no no_caption=1
//@caption Org. toolbar


////box////
////---------------
////|-------------| <- hbox1 
////|------|------| <- hobx2
@default group=customers

@layout customer_hbox_toolbar type=hbox group=customers

@property customer_toolbar type=toolbar no_caption=1 store=no parent=customer_hbox_toolbar
@caption "Klientide toolbar"

@layout customers_hbox_others type=hbox group=customers width=20%:80%

@layout vbox_customers_left type=vbox parent=customers_hbox_others group=customers

@property customer_listing_tree type=treeview no_caption=1 parent=vbox_customers_left
@caption Rühmade puu

@layout vbox_customers_right type=vbox parent=customers_hbox_others group=customers

@property customer type=table store=no no_caption=1 parent=vbox_customers_right
@caption Kliendid

@property customer_search_name type=textbox size=30 store=no parent=vbox_customers_right
@caption Nimi

@property customer_search_reg type=textbox size=30 store=no parent=vbox_customers_right
@caption Reg nr.

@property customer_search_leader type=textbox size=30 store=no parent=vbox_customers_right
@caption Firmajuht

@property customer_search_field type=textbox size=30 store=no parent=vbox_customers_right
@caption Põhitegevus

@property customer_search_county type=textbox size=30 store=no parent=vbox_customers_right
@caption Maakond

@property customer_search_city type=textbox size=30 store=no parent=vbox_customers_right
@caption Linn/Vald/Alev

@property customer_search_address type=textbox size=30 store=no parent=vbox_customers_right
@caption Tänav/Küla

@property customer_search_only orient=vertical type=chooser store=no parent=vbox_customers_right
@caption Valim

@property customer_search_submit type=submit size=15 store=no parent=vbox_customers_right
@caption Otsi

@property customer_search_add type=submit size=15 store=no parent=vbox_customers_right action=create_new_company
@caption Lisa

@property customer_search type=hidden store=no parent=vbox_customers_right value=1 no_caption=1
@caption Otsi

@property customer_search_results type=table store=no parent=vbox_customers_right no_caption=1
@caption Otsi tulemused 

////end of box////

/////start of my_customers
@default group=my_customers

@layout my_customers_hbox_toolbar type=hbox group=my_customers

@property my_customers_toolbar type=toolbar no_caption=1 store=no parent=my_customers_hbox_toolbar
@caption "Klientide toolbar"

@layout my_customers_hbox_others type=hbox group=my_customers width=20%:80%

@layout vbox_my_customers_left type=vbox parent=my_customers_hbox_others group=my_customers

@property my_customers_listing_tree type=treeview no_caption=1 parent=vbox_my_customers_left
@caption Rühmade puu

@layout vbox_my_customers_right type=vbox parent=my_customers_hbox_others group=my_customers

@property my_customers_table type=table store=no no_caption=1 parent=vbox_my_customers_right
@caption Kliendid
/////end of my_customers

------------- PAKKUMISED ---------------
@default group=offers

@layout offers_toolbar type=hbox group=offers
@layout offers_main type=hbox width=20%:80% group=offers
@layout offers_tree type=vbox parent=offers_main group=offers
@layout offers_table type=vbox parent=offers_main group=offers

@property offers_listing_toolbar type=toolbar no_caption=1 parent=offers_toolbar group=offers
@property offers_listing_tree type=treeview no_caption=1 parent=offers_tree group=offers
@property offers_listing_table type=table no_caption=1 parent=offers_table group=offers

@property offers_current_org_id type=hidden store=no group=offers
------------ END PAKKUMISED -------------------

------------ ORGANISATSIOONI OBJEKTID ---------
@layout objects_toolbar type=hbox group=org_objects
@layout objects_main type=hbox width=20%:80% group=org_objects
@layout objects_tree type=vbox parent=objects_main group=org_objects
@layout objects_table type=vbox parent=objects_main group=org_objects

@property objects_listing_toolbar type=toolbar no_caption=1 parent=objects_toolbar group=org_objects
@property objects_listing_tree type=treeview no_caption=1 parent=objects_tree group=org_objects
@property objects_listing_table type=table no_caption=1 parent=objects_table group=org_objects
---------- END ORGANISATSIOONI OBJEKTID ---------

---------- PROJEKTID ----------------------------
@layout projects_main type=hbox width=20%:80% group=org_projects
@layout projects_tree type=vbox parent=projects_main group=org_projects
@layout projects_table type=vbox parent=projects_main group=org_projects

@default group=org_projects
@default no_caption=1

property projects_listing_toolbar type=toolbar no_caption=1 parent=projects_toolbar 
@property projects_listing_tree type=treeview no_caption=1 parent=projects_tree 
@property projects_listing_table type=table no_caption=1 parent=projects_table

----- Minu projektid
@property my_projects type=table no_caption=1 store=no group=my_projects

-------------------------------------------------
@groupinfo general_sub caption="&Uuml;ldine" parent=general
@groupinfo cedit caption="Üldkontaktid" parent=general
@groupinfo org_sections caption="Tegevus" parent=general
@groupinfo add_info caption="Lisainfo" parent=general
@groupinfo user_settings caption="Kasutajate seaded" parent=general


@groupinfo people caption="Inimesed"

@groupinfo contacts caption="Kontaktid"
@groupinfo contacts2 caption="Puuvaade" parent=people submit=no
@groupinfo oldcontacts caption="Isikud" parent=people submit=no
@groupinfo overview caption="Tegevused" 
@groupinfo all_actions caption="Kõik" parent=overview submit=no
@groupinfo calls caption="Kõned" parent=overview submit=no
@groupinfo meetings caption="Kohtumised" parent=overview submit=no
@groupinfo tasks caption="Toimetused" parent=overview submit=no
@groupinfo tasks_overview caption="Ülevaade" parent=overview

@groupinfo relorg caption="Kliendid"
@groupinfo customers caption="Kõik kliendid" parent=relorg submit=no
@groupinfo my_customers caption="Minu kliendid" parent=relorg submit=no
@groupinfo fcustomers caption="Tulevased kliendid" parent=relorg
@groupinfo partners caption="Partnerid" parent=relorg
@groupinfo fpartners caption="Tulevased partnerid" parent=relorg
@groupinfo competitors caption="Konkurendid" parent=relorg


@groupinfo personal_offers caption="Tööpakkumised" parent=people submit=no
@groupinfo personal_candits caption="Kandideerijad" parent=people submit=no

@groupinfo offers caption="Pakkumised" submit=no parent=relorg
@groupinfo org_objects_main caption="Objektid" submit=no
@groupinfo org_objects caption="Objektid" submit=no parent=org_objects_main

@groupinfo org_projects caption="Projektid" submit=no parent=relorg
@groupinfo my_projects caption="Minu projektid" parent=relorg submit=no

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

@reltype CUSTOMER value=22 clid=CL_CRM_COMPANY
@caption Klient

@reltype POTENTIONAL_CUSTOMER value=23 clid=CL_CRM_COMPANY
@caption Tulevane klient

@reltype PARTNER value=24 clid=CL_CRM_COMPANY
@caption Partner

@reltype POTENTIONAL_PARTNER value=25 clid=CL_CRM_COMPANY
@caption Tulevate partner

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

@reltype CLIENT_MANAGER value=34 clid=CL_CRM_MANAGER
@caption Kliendihaldur

@reltype SECTION_WEBSIDE value=35 clid=CL_CRM_MANAGER
@caption Üksus veebis

@reltype GROUP value=36 clid=CL_GROUP
@caption organisatsiooni grupp

@classinfo no_status=1
			
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

class crm_company extends class_base
{
	var $unit=0;
	var $category=0;
	var $active_node = 0;
	var $group_not_shown = true;
	var $data = null;

	var $customer_search_results;

	var $users_person = null;
	//bad name, it is in the meaning of
	//show_contacts_search
	var $do_search = 0;

	var $show_customer_search = 0;
	/*
		Problem. The datamodel is as follows.
			company -> section -> profession|section|member 
			or
			company -> profession|section|member
		profession, section, member are relations.
		crm_company.reltype_section.value!=crm_person.reltype_section
		This is why i need to hold the values of the relations.
		When the time comes and i can use textual relations('RELTYPE_FOO'), i would just
		have the names the same and the methods operating on the relations
		would do the work. But until then i'll have tmp variables...
		Later on they can be just changed to text
	*/
	//sections
	//crm_company.reltype_section.value = 28;
	var $crm_company_reltype_section = 28;
	//crm_section.reltype_section.value = 1;
	var $crm_section_reltype_section = 1;
	//professions
	//crm_company.reltype_professions.value = 29;
	var $crm_company_reltype_professions = 29;
	//crm_section.reltype_professions.value = 1;
	var $crm_section_reltype_professions = 3;
	//workers
	//crm_company.reltype_workers.value = 8;
	var $crm_company_reltype_workers = 8;
	//crm_section.reltype_workers.value = 2;
	var $crm_section_reltype_workers = 2;
	//categorys
	var $crm_company_reltype_category = 30;
	var $crm_category_reltype_category = 2;


	//default to company relation values
	var $reltype_section = 0;//$this->crm_company_reltype_section;
	var $reltype_professions = 0;//$this->crm_company_reltype_professions;
	var $reltype_workers = 0;//$this->crm_company_reltype_workers;
	var $reltype_category = 0;

	function crm_company()
	{
		$this->init(array(
			'clid' => CL_CRM_COMPANY,
			'tpldir' => 'crm/crm_company',
		));
	}

	function crm_company_init()
	{
		
		$this->customer_search_results = new object_list();
		//default to company relation values
		$this->reltype_section = $this->crm_company_reltype_section;
		$this->reltype_professions = $this->crm_company_reltype_professions;
		$this->reltype_workers = $this->crm_company_reltype_workers;
		$this->reltype_category = $this->crm_company_reltype_category;
		//
		$this->group_not_shown = false;
		$us = get_instance(CL_USER);
		$this->users_person = new object($us->get_current_person());
		$this->users_company = new object($us->get_current_company());
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

		$customer_reltype = 3;//crm_category.reltype_customer 
		if($obj->prop('class_id')==CL_CRM_COMPANY)
		{
			$customer_reltype = 22; //crm_company.reltye_customer
			if($conn_type=='RELTYPE_CATEGORY')
			{
				$conn_type = $this->crm_company_reltype_category;
			}
			else
			{
				$conn_type = $this->crm_company_reltype_section;
			}
		}
		else if($obj->prop('class_id')==CL_CRM_SECTION)
		{
			if($conn_type=='RELTYPE_CATEGORY')
			{
				$conn_type = $this->crm_category_reltype_category;
			}
			else
			{
				$conn_type = $this->crm_section_reltype_section;
			}
		}
	
		$conns = $obj->connections_from(array(
			'type'=>$conn_type,
			'sort_by' => 'from.jrk',
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
				$name = '<span class="'.$style.'">'.$name.'</span>';
			}
			if($conn->prop('to')==$this->active_node)
			{
				$name='<b>'.$name.'</b>';
			}
			$tmp_obj = $conn->to();
			
			//use the plural unless plural is empty -- this is just for reltype_section
			$tree_node_info = array(
				'id'=>++$node_id,
				'name'=>$name,
				'url'=>aw_url_change_var(array(
					$attrib=>$conn->prop('to'),
					'cat'=>'',
					'org_id' => '',
				)),
				'oid' => $conn->prop('to'),
				"class_id" => $conn->prop("to.class_id")
			);
			//i know, i know, this function is getting really bloated
			//i just don't know yet, how to refactor it nicely, until then
			//i'll be just adding the bloat
			//get all the company for the current leaf
			$blah = $conn->to();
			$conns_tmp = $blah->connections_from(array(
				'type'=>$customer_reltype
			));
			$oids = array();
			foreach($conns_tmp as $conn_tmp)
			{
				$oids[$conn_tmp->prop('to')] = $conn_tmp->prop('to');
			}
			$tree_node_info['oid'] = $oids;
			//let's find the picture for this obj
			$img_conns = $tmp_obj->connections_from(array('type'=>'RELTYPE_IMAGE'));
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
		$type = 'RELTYPE_PROFESSIONS';
		if($obj->prop('class_id')==CL_CRM_COMPANY)
		{
			$type = $this->crm_company_reltype_professions;
		}
		else if($obj->prop('class_id')==CL_CRM_SECTION)
		{
			$type = $this->crm_section_reltype_professions;
		}
		//getting the list of professions for the current
		//unit/organization
		$prof_connections = $obj->connections_from(array(
			'type'=>$type,
		));

		$key = 'unit';
		$value = '';
		if($obj->prop('class_id')==CL_CRM_SECTION)
		{
			$value = $obj->id();
		}

		foreach($prof_connections as $prof_conn)
		{
			$tmp_obj = new object($prof_conn->to());
			$name = strlen($tmp_obj->prop('name_in_plural'))?$tmp_obj->prop('name_in_plural'):$tmp_obj->prop('name');
			
			if($tmp_obj->id()==$this->active_node)
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
	
	function callback_mod_tab(&$arr)
	{
		switch ($arr['id'])
		{
			/*
			case 'customers':
				$tmp_obj = new object($arr['request']['id']);
				$arr['caption'] = $tmp_obj->prop('name');
			break;
			
			case 'my_customers':
					$arr['caption'] = $this->users_person->prop('name');			
			break;
			*/
			case 'people':
				//$arr['link'] = aw_url_change_var(array("group" => "contacts2"));
				//arr($arr);
			break;
		}
	}

	function get_property($arr)
	{
		$data = &$arr['prop'];
		$retval = PROP_OK;
	
		/*
			weird shiznit, one day its to show the search
			the other day is not to show the search
		*/
		/*if($arr['request']['group']=='relorg')
		{
			$this->show_customer_search=true;
			$arr['request']['no_results'] = 1;
		}*/
		switch($data['name'])
		{
			//hägish, panen nime kõrval html lingi ka
			case 'contact':
				if(sizeof($data['options'])>1)
				{
					$url = $this->mk_my_orb('change',array(
						'id' => max(array_keys($data['options'])),
					),CL_CRM_ADDRESS);
				}
				else
				{
					$url = $this->mk_my_orb('new',array(
						'alias_to' => $arr['obj_inst']->id(),
						'parent' => $arr['obj_inst']->id(),
						'reltype' => 3, //crm_company.reltype_address
					),CL_CRM_ADDRESS);
				}
				$data['caption'] .= '<br><a href="'.$url.'">'.t("Muuda").'</a>';
			break;
			case "year_founded":
				if(!$data["value"])
				{
					$data["value"] = 0;
				}
			break;
			case "tabpanel":
				//arr($data);
			break;
			
			case "my_projects":
				$this->do_my_projects_table($arr);
			break;
			
			case "projects_listing_tree":
					$this->do_offers_listing_tree($arr);
			break;
			//START OF CUSTOMER SEARCH
			case 'customer_search_name':
				if($this->show_customer_search)
				{
					$data['value'] = $arr['request']['customer_search_name'];
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search_field':
				if($this->show_customer_search)
				{
					$data['value'] = $arr['request']['customer_search_field'];
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search_reg':
				if($this->show_customer_search)
				{
					$data['value'] = $arr['request']['customer_search_reg'];
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search_address':
				if($this->show_customer_search)
				{
					$data['value'] = $arr['request']['customer_search_address'];
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search_only':
				if($this->show_customer_search)
				{
					$obj = new object($arr['request']['id']);					
					$data['options'] = array('all'=> t('Otsi kogu süsteemist'),
						'company' => sprintf(t('Otsi %s klientide hulgast'), $obj->prop('name')),
						'person' => sprintf(t('Otsi %s klientide hulgast'), $this->users_person->prop('name'))
					);
					if(in_array($arr['request']['customer_search_only'],array_keys($data['options'])))
					{
						$data['value'] = $arr['request']['customer_search_only'];
					}
					else
					{
						list($data['value'],) = each($data['options']);
					}
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search_leader':
				if($this->show_customer_search)
				{
					$data['value'] = $arr['request']['customer_search_leader'];
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search_city':
				if($this->show_customer_search)
				{
					$data['value'] = $arr['request']['customer_search_city'];
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search_county':
				if($this->show_customer_search)
				{
					$data['value'] = $arr['request']['customer_search_county'];
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search_submit':
				if(!$this->show_customer_search)
				{
					return PROP_IGNORE;	
				}
				break;
			case 'customer_search_add':
				if($this->show_customer_search)
				{
					$filter = $this->construct_customer_search_filter(&$arr);
					$this->customer_search_results = &$this->get_customer_search_results($filter);
					if($this->customer_search_results && sizeof($this->customer_search_results->ids()) 
						|| $arr['request']['no_results'])
					{
						return PROP_IGNORE;
					}
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search_results':
				if($this->show_customer_search)
				{
					if($this->customer_search_results)
					{
						$this->do_search(&$arr, $this->customer_search_results);
					}
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			case 'customer_search':
				if(!$this->show_customer_search)
				{
					return PROP_IGNORE;
				}
				break;
			//END OF CUSTOMER SEARCH

			//START OF OFFERERS
			case 'offers_listing_toolbar':
				$this->do_offers_listing_toolbar($arr);
			break;
			
			case 'offers_listing_tree':
				$this->do_offers_listing_tree($arr);
			break;
			
			case 'offers_listing_table':
				$this->do_offers_listing_table($arr);	
			break;
			
			//START OF CONTACTS SEARCH
			case 'contacts_search_results':
				if($this->do_search && $arr['request']['contacts_search_show_results'])
				{
					$this->do_contacts_search_results(&$arr);
				}
				else
				{
					return IGNORE_PROP;
				}
				break;
			//show or don't show search stuff
			case 'contact_search_firstname':
				if(!$arr['request']['contact_search'])
				{
					return PROP_IGNORE;
				}
				else
				{
					$data['value'] = $arr['request']['contact_search_firstname'];
				}
				break;	
			case 'contact_search_lastname':
				if(!$arr['request']['contact_search'])
				{
					return PROP_IGNORE;
				}
				else
				{
					$data['value'] = $arr['request']['contact_search_lastname'];
				}
				break;
			case 'contact_search_code':
				if(!$arr['request']['contact_search'])
				{
					return PROP_IGNORE;
				}
				else
				{
					$data['value'] = $arr['request']['contact_search_code'];
				}
				break;
			case 'contact_search_submit':
				if(!$arr['request']['contact_search'])
				{
					return PROP_IGNORE;
				}
				break;
			case 'contact_search':
				if(!$arr['request']['contact_search'])
				{
					return PROP_IGNORE;
				}
				break;
			//END OF CONTACTS SEARCH
			case "unit_listing_tree":
			{
				$this->_do_unit_listing_tree($arr);
				break;
			}
			
			case "unit_listing_tree_candidates":
				$tree_inst = &$arr['prop']['vcl_inst'];
				$node_id = 0;
				$this->active_node = (int)$arr['request']['unit'];
				//$this->generate_tree(&$tree_inst,$arr['obj_inst'],&$node_id,'RELTYPE_SECTION',array(),'unit',true);
				
				$this->generate_tree(array(
					'tree_inst' => &$tree_inst,
					'obj_inst' => $arr['obj_inst'],
					'node_id' => &$node_id,
					'conn_type' => 'RELTYPE_SECTION',
					'attrib' => 'unit',
					'leafs' => true,
				));
				break;	
			break;
			
			case "unit_listing_tree_personal":
				$tree_inst = &$arr['prop']['vcl_inst'];
				$node_id = 0;
				$this->active_node = (int)$arr['request']['unit'];

				$this->generate_tree(array(
							'tree_inst' => &$tree_inst,
							'obj_inst' => $arr['obj_inst'],
							'node_id' => &$node_id,
							'conn_type' => 'RELTYPE_SECTION',
							'attrib' => 'unit',
							'leafs' => true,
				));
			break;

			case "customer_listing_tree":
			{
				$this->customer_listing_tree($arr);
				break;
			}
			case "my_customers_listing_tree":
			{
				$tree_inst = &$arr['prop']['vcl_inst'];	
				$node_id = 0;
				$this->active_node = (int)$arr['request']['category'];

				$this->generate_tree(array(
							'tree_inst' => &$tree_inst,
							'obj_inst' => $arr['obj_inst'],
							'node_id' => &$node_id,
							'conn_type' => 'RELTYPE_CATEGORY',
							'skip' => array(CL_CRM_COMPANY),
							'attrib' => 'category',
							'leafs' => 'false',
							'style' => 'nodetextbuttonlike',
				));
				
				//need to delete every category of the tree that the person doesn't
				//have a relation with
				$my_data = array();
				$us = get_instance(CL_USER);
				$person = obj($us->get_current_person());
				$conns = $person->connections_from(array(
					'type' => 22,//crm_person.reltype_CLIENT_IM_HANDLING
				));
				
				foreach($conns as $conn)
				{
					$my_data[$conn->prop('to')] = $conn->prop('to');
				}
				$this->_clean_up_the_tree(&$tree_inst->items, 0, &$my_data);
				break;
			}
			case 'ettevotlusvorm':
			{
				$ol = new object_list(array(
					'class_id' => CL_CRM_CORPFORM,
					'sort_by' => 'objects.jrk, objects.name',
				));
				$elements = array();
				$elements[0] = t('--vali--');
				for($o=$ol->begin();!$ol->end();$o=$ol->next())
				{
					if($o->id() == $data['value'])
					{
						$arr['prop']['value'] = $o->id();
					}
					$elements[$o->id()] = $o->name();//prop('shortname');
				}
				$arr['prop']['options'] = $elements;
				break;
			}
			case 'contact_toolbar':
			{
				$this->do_contact_toolbar(&$data['toolbar'],&$arr);
				break;
			}
			case 'customer_toolbar':
			{
				$this->do_customer_toolbar(&$data['toolbar'],&$arr);	
				break;
			}
			case 'my_customers_toolbar':
			{
				$this->do_my_customers_toolbar(&$data['toolbar'],&$arr);
				break;
			}
			case "customer":
				if($this->show_customer_search)
				{
					return PROP_IGNORE;
				}
				else
				{
					$this->org_table(&$arr);
				}
				break;
			case 'my_customers_table':
			{
				$us = get_instance(CL_USER);
				$person = obj($us->get_current_person());
				$conns = $person->connections_from(array(
					'type'=>22, //crm_person.reltype_CLIENT_IM_HANDLING
				));
				$filter = array();
				foreach($conns as $conn)
				{
					$filter[$conn->prop('to')] = $conn->prop('to');
				}
				$this->org_table(&$arr, $filter);
				break;
			}
			case "org_toolbar":
				$vcl_inst = &$arr["prop"]["toolbar"];
				$vcl_inst->add_button(array(
					"name" => "delete",
					"img" => "delete.gif",
					"caption" => t('Kustuta')
				));
				break;

			case "firmajuht":
				$conns = $arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_WORKERS",
				));
				foreach($conns as $conn)
				{
					$data["options"][$conn->prop("to")] = $conn->prop("to.name");
				};
				break;
			
			case "navtoolbar":
				$this->navtoolbar($arr);
				break;

			case "org_actions":
			case "org_calls":
			case "org_meetings":
			case "org_tasks":
				$this->do_org_actions(&$arr);
				break;
			case 'old_human_resources':
				$this->do_human_resources($arr,true);
				break;
			case 'contact_search':
				$this->do_contact_search($arr);
				break;
			case "human_resources":
				//don't show it if i wan't to show the search part
				if(!$arr['request']['contact_search'])
				{
					$this->do_human_resources($arr);
				}
				break;
			case "tasks_call":
				$this->do_tasks_call($arr);
				break;
				
			case "offers_current_org_id":
				$data["value"] = $arr["request"]["org_id"];
		 	break;
				
			break;
				
			case "addresslist":
				$this->do_addresslist($arr);
			break;
			
			case "personal_offers_toolbar":
				$this->do_personal_offers_toolbar(&$data["toolbar"], &$arr);
			break;
			
			case "personal_candidates_toolbar":
				$this->do_personal_candidates_toolbar(&$data["toolbar"], &$arr);
			break;
			
			case "personal_offers_table":
				$this->personal_offers_table($arr);
			break;
			
			case "personal_candidates_table":
				$this->do_personal_candidates_table($arr);	
			break;

			// Begin of org objects
			case "objects_listing_toolbar":
				$this->do_objects_listing_toolbar($arr);
			break;
			
			case "objects_listing_table":
				$this->do_objects_listing_table($arr);
			break;
			
			case "objects_listing_tree":
				$this->do_objects_listing_tree($arr);
			break;
			
			case "projects_listing_toolbar":
				$this->do_projects_listing_toolbar($arr);
			break;
			
			case "projects_listing_table":
				$this->do_projects_listing_table($arr);
			break;
			
		};
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr['prop'];
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

	function _clean_up_the_tree($tree_items, $arrkey, $my_data)
	{
		$ret = false;
		foreach($tree_items[$arrkey] as $key=>$value)
		{
			//these are toplevel nodes
			//checking if one has sub_elements
			if(array_key_exists($value['id'], $tree_items))
			{
				//has subelements
				$ret = $this->_clean_up_the_tree(&$tree_items, $value['id'], &$my_data);
				$keep_it = false;

				foreach($my_data as $key2=>$value2)
				{
					if(in_array($value2, $value['oid']))
					{
						$keep_it = true;
						$ret = true;
					}
				}

				if(!$ret && !$keep_it)
				{
					unset($tree_items[$arrkey][$key]);
				}
			}
			//no sub elements, now if this node isn't useful to me
			//it will get deleted :)
			else
			{
				$keep_it = false;
				foreach($my_data as $key2=>$value2)
				{
					if(in_array($value2, $value['oid']))
					{
						$keep_it = true;
					}
				}
				if(!$keep_it)
				{
					unset($tree_items[$arrkey][$key]);
				}
				return $keep_it;
			}
		}
		return $ret;
	}

	function personal_offers_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
	
		$table->define_field(array(
			"name" => "osakond",
			"caption" => t("Osakond"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "ametinimi",
			"caption" => t("Ametinimi"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "comments",
			"caption" => t("Kommentaar"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "kehtiv_alates",
			"caption" => t("Kehtiv alates"),
			"sortable" => "1",
			"width" => 80,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "kehtiv_kuni",
			"caption" => t("Kehtiv kuni"),
			"sortable" => "1",
			"width" => 80,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"field" => "job_id",
			"caption" => t("X"),
			"width" => 20,
			"align" => "center"
		));
		
		$section_cl = get_instance(CL_CRM_SECTION);	
		
		if(is_oid($arr['request']['unit']))
		{
			$jobs_ids = $section_cl->get_section_job_ids_recursive($arr['request']['unit']);
		}
		else
		{
			$jobs_ids = $section_cl->get_all_org_job_ids($arr["obj_inst"]->id());
			$professions = $section_cl->get_all_org_proffessions($arr["obj_inst"]->id(), true);
		}
			
		if(!$jobs_ids)
		{
			return;
		}

		$job_obj_list = new object_list(array(
			"oid" => array_keys($jobs_ids),
			"profession" => $arr["request"]["cat"],
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER
		));
		$job_obj_list = $job_obj_list->arr();
		foreach ($job_obj_list as $job)
		{
			if($arr['request']['unit'])
			{
				$professions = $section_cl->get_professions($arr['request']['unit'], true);
			}
			
			if(!$professions[$job->prop("profession")])
			{
				$professin_cap = t("Määramata");
			}
			else
			{
				$professin_cap = $professions[$job->prop("profession")];					
			}
			
			$table->define_data(array(
				"osakond" => $jobs_ids[$job->id()],
				"kehtiv_kuni" => $job->prop("deadline"),
				"ametinimi" => html::href(array(
					"caption" => $professin_cap,
					"url" => $this->mk_my_orb("change", array("id" =>$job->id()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
				)),
				"kehtiv_alates" => $job->prop("beginning"),
				"job_id" => $job->id(),
				"comments" => $job->prop("comment"),
			));				
		}
		
	}
	
	function do_personal_candidates_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "person_name",
			"caption" => t("Kandideerija nimi"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => t("Ametikoht"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "osakond",
			"caption" => t("Osakond"),
			"sortable" => "1",
		));
	
		$section_cl = get_instance(CL_CRM_SECTION);	
		
		if(is_oid($arr['request']['unit']))
		{
			$jobs_ids = $section_cl->get_section_job_ids_recursive($arr['request']['unit']);
		}
		else
		{
			$jobs_ids = $section_cl->get_all_org_job_ids($arr["obj_inst"]->id());
			$professions = $section_cl->get_all_org_proffessions($arr["obj_inst"]->id(), true);
		}
			
		if(!$jobs_ids)
		{
			return;
		}
		
		$candidate_conns = new connection();
		$candidate_conns = $candidate_conns->find(array(
        	"from" => array_keys($jobs_ids),
        	"to.class_id" => CL_CRM_PERSON,
        	"reltype" => 66666, //RELTYPE_CANDIDATE
		));
		
		$professions = $section_cl->get_all_org_proffessions($arr["obj_inst"]->id(), true);
	
		foreach ($candidate_conns as $candidate_conn)
		{				
			$table->define_data(array(
				"person_name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $candidate_conn['to']), CL_CRM_PERSON),
					"caption" => $candidate_conn['to.name'],
				)),	
				"ametikoht" => $candidate_conn['from.name'],
				"osakond" => $jobs_ids[$candidate_conn['from']],
			));
		}
	}
	
	/*function do_jobs_notact_list($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => t("Ametikoht"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "deadline",
			"caption" => t("Tähtaeg"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "kandideerijad",
			"caption" => t("Kandidaadid"),
			"sortable" => "1",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"caption" => t("X"),
		));
		
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_JOBS")) as $job)
		{
			$job = &obj($job->prop("to"));
			
			if($job->prop("deadline") < time())
			{
				$table->define_data(array(
					"ametikoht" => html::href(array(
						"caption" => $job->name(),
						"url" => $this->mk_my_orb("change", array("id" => $job->id()), "job_offer"),
				)),
					"deadline" => get_lc_date($job->prop("deadline"), LC_DATE_FORMAT_LONG_FULLYEAR),
					"kandideerijad" => html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $job->id(), "group" => "kandideerinud"), "job_offer"),
						"caption" => t("Vaata kandidaate"),
					)), 
				));
			}
		}
	}
	
	function do_jobslist($arr)
	{
		
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => t("Ametikoht"),
			"sortable" => "1",
			"width" => "200",
		));
		
		$table->define_field(array(
			"name" => "deadline",
			"caption" => t("Tähtaeg"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "kandideerijad",
			"caption" => t("Kandidaadid"),
			"sortable" => "1",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"caption" => t("X"),
		));
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_JOBS")) as $job)
		{
			$job = &obj($job->prop("to"));
			
			if($job->prop("deadline")>time())
			{
				$table->define_data(array(
					"ametikoht" => html::href(array(
						"caption" => $job->name(),
						"url" => $this->mk_my_orb("change", array("id" => $job->id()), "job_offer"),
					)),
					"deadline" => get_lc_date($job->prop("deadline"), LC_DATE_FORMAT_LONG_FULLYEAR),
					"kandideerijad" => html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $job->id(), "group" => "kandideerinud"), "job_offer"),
						"caption" => t("Vaata kandidaate"),
					)), 
				));
			}
		}
		
	}
*/
	function do_contact_search($arr)
	{
		$table = &$arr['prop']['vcl_inst'];
	}

	function _init_human_resources_table(&$t, $old_iface)
	{
		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi'),
			'sortable' => '1',
			"chgbgcolor" => "cutcopied",
			'callback' => array(&$this, 'callb_human_name'),
			'callb_pass_row' => true,
		));
		$t->define_field(array(
        	'name' => 'phone',
			"chgbgcolor" => "cutcopied",
			'caption' => t('Telefon'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'email',
			"chgbgcolor" => "cutcopied",
			'caption' => t('E-post'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'section',
			"chgbgcolor" => "cutcopied",
			'caption' => t('Üksus'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'rank',
			"chgbgcolor" => "cutcopied",
			'caption' => t('Ametinimetus'),
            'sortable' => '1',
		));

		if($old_iface)
		{		
			$t->define_field(array(
				"chgbgcolor" => "cutcopied",
				'name' => 'new_call',
				'align' => 'center',
			));
			$t->define_field(array(
				'name' => 'new_meeting',
				"chgbgcolor" => "cutcopied",
				'align' => 'center',
			));
			$t->define_field(array(
				"chgbgcolor" => "cutcopied",
				'name' => 'new_task',
				'align' => 'center',
			));
		}
		
		if(!$old_iface)
		{
			$t->define_chooser(array(
				'name'=>'check',
				'field'=>'id',
				"chgbgcolor" => "cutcopied",
			));
		}
	}

	function do_human_resources($arr,$old_iface=false)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_human_resources_table($t, $old_iface);
					 
		$crmp = get_instance(CL_CRM_PERSON);

		// http://intranet.automatweb.com/automatweb/orb.aw?class=planner&action=change&alias_to_org=87521&reltype_org=RELTYPE_ISIK_KOHTUMINE&id=46394&clid=224&group=add_event&title=Kohtumine:%20Anti%20Veeranna&parent=46398

		// to get those adding links work, I need 
		// 1. id of my calendar
		// 2. relation type
		// alias_to_org oleks isiku id
		// reltype_org oleks vastava seose id

		$pl = get_instance(CL_PLANNER);
		$cal_id = $pl->get_calendar_for_user(array('uid'=>aw_global_get('uid')));

		// XXX: I should check whether $this->cal_id exists and only include those entries
		// when it does.

		// call : rel=9 : clid=CL_CRM_CALL
		// meeting : rel=8 : clid=CL_CRM_MEETING
		// task : rel=10 : clid=CL_TASK
		$persons = array();
		$professions = array();
		//if section present, i'll get all the professions
		if(is_oid($arr['request']['unit']))
		{
			$tmp_obj = new object($arr['request']['unit']);
			$conns = $tmp_obj->connections_from(array(
				'type' => 'RELTYPE_PROFESSIONS'
			));
			foreach($conns as $conn)
			{
				$professions[$conn->prop('to')] = $conn->prop('to.name');
			}
		}
	
		if(is_oid($arr['request']['cat']))
		{
			$professions = array();
			$tmp_obj = new object($arr['request']['cat']);
			$professions[$tmp_obj->id()] = $tmp_obj->prop('name');
		}

		if($old_iface)
		{
			$this->get_all_workers_for_company(&$arr['obj_inst'],&$persons,true);
		}
		else
		{
			//if listing from a specific unit, then the reltype is different
			if((int)$arr['request']['unit'])
			{
				$obj = new object((int)$arr['request']['unit']);
				$conns = $obj->connections_from(array(
					'type' => $this->crm_section_reltype_workers,
				));
			}
			else
			{
				$conns = $arr["obj_inst"]->connections_from(array(
					"type" => $this->crm_company_reltype_workers,
				));
			}

			foreach($conns as $conn)
			{
				$persons[] = $conn->prop('to');
			}
		}

		if (isset($arr["person_filter"]) && is_array($arr["person_filter"]))
		{
			$tmp = array();
			foreach($persons as $person)
			{
				if (isset($arr["person_filter"][$person]))
				{
					$tmp[] = $person;
				}
			}
			$persons = $tmp;
		}

		foreach($persons as $person)
		{
			$person = new object($person);
			$idat = $crmp->fetch_all_data($person->id());
			$pdat = $crmp->fetch_person_by_id(array(
				"id" => $person->id(),
				"cal_id" => $cal_id,
			));

			if(is_oid($arr['request']['cat']))
			{
				//persons only from this category
				if(!in_array($arr['request']['cat'], array_keys($pdat['ranks_arr'])))
				{
					continue;
				}
			}

			if(is_oid($arr['request']['cat']) || is_oid($arr['request']['unit']))
			{
				//showing only the professions that the unit AND the person is associated with
				//in php 4.3 it would be a one-liner with intersect_assoc
				$tmp_arr = array_intersect(array_keys($professions),array_keys($pdat['ranks_arr']));
				$tmp_arr2 = array();
				foreach($tmp_arr as $key=>$value)
				{
					$tmp_arr2[] = $professions[$value];
				}
				//getting the professions that the professions of the person are associated with
				foreach($pdat['ranks_arr'] as $key=>$rank)
				{
					$tmp_obj = new object($key);
					$conns=$tmp_obj->connections_from(array(
						'type' => 1,
					));
					$tmp_arr = array();
					foreach($conns as $conn)
					{
						if(!in_array($conn->prop('to'), array_keys($tmp_arr2)))
						{
							$tmp_arr2[$conn->prop('to')] = $conn->prop('to.name');
						}
					}
				}
				$pdat['rank'] = join(', ',$tmp_arr2);
			}
			
			$sections_professions = array();
			$section = '';
			foreach($pdat['sections_arr'] as $key=>$value)
			{
				$crm_section = get_instance(CL_CRM_SECTION);
				$sections_professions[$key] = $crm_section->get_professions($key);
				$tmp_arr = array_intersect(array_keys($pdat['ranks_arr']),array_keys($pdat['ranks_arr']));
				$tmp_arr2 = array();
				foreach($tmp_arr as $key2=>$value2)
				{
					$tmp_arr2[] = $pdat['ranks_arr'][$value2];
				}
				$section = current($pdat['sections_arr']);//.', '.join(', ',$tmp_arr2);
				//damn, i'm not sure if a person can have multiple sections?
				//until then the break stays here
				break;
			}

			//kui amet kuulub $pdat['sections_arr'] olevasse sektsiooni ja persoon on seotud
			//selle ametiga, siis seda näidata kujul 
			
			$ccp = (isset($_SESSION["crm_copy_p"][$person->id()]) || isset($_SESSION["crm_cut_p"][$person->id()]) ? "#E2E2DB" : "");

			$tdata = array(
				"name" => $person->prop('name'),
				"id" => $person->id(),
				"phone" => $pdat["phone"],
				"rank" => $pdat["rank"],
				'section' => $section,
				"email" => html::href(array(
					"url" => "mailto:" . $pdat["email"],
					"caption" => $pdat["email"],
				)),
				"cutcopied" => $ccp
			);
			if($old_iface)
			{
				if ($cal_id)
				{
					$tdata["new_task"] = html::href(array(
						"caption" => t("Uus toimetus"),
						"url" => $pdat["add_task_url"],
					));
					$tdata["new_call"] = html::href(array(
						"caption" => t("Uus kõne"),
						"url" => $pdat["add_call_url"],
					));
					$tdata["new_meeting"] = html::href(array(
						"caption" => t("Uus kohtumine"),
						"url" => $pdat["add_meeting_url"],
					));
				};
			}
			$t->define_data($tdata);
		};

	}

	function get_all_workers_for_company($obj,$data,$workers_too=false)
	{	
		//getting all the workers for the $obj
		$conns = $obj->connections_from(array(
			"type" => $this->crm_section_reltype_workers,
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

	function callb_human_name($arr)
	{
		return html::href(array(
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["id"],
				"return_url" => urlencode(aw_global_get("REQUEST_URI")),
			),CL_CRM_PERSON),
			"caption" => $arr["name"],
		));
	}

	function do_tasks_call($arr)
	{
		$prop = &$arr["prop"];
		$obj = $arr["obj_inst"];
		$conns = $obj->connections_from(array(
			"type" => "RELTYPE_CALL",
		));
		$rv = "";
		foreach($conns as $conn)
		{
			$target_obj = $conn->to();
			$inst = $target_obj->instance();
			if (method_exists($inst,"request_execute"))
			{
				$rv .= $inst->request_execute($target_obj);
			};
		};
		$prop["value"] = $rv;
	}
	
	function do_addresslist($arr)
	{
		$prop = &$arr["prop"];
		$obj = $arr["obj_inst"];
		$conns = $obj->connections_from(array(
			"type" => "RELTYPE_ADDRESS",
		));
		$rv = "";
		foreach($conns as $conn)
		{
			$target_obj = $conn->to();
			$inst = $target_obj->instance();
			if (method_exists($inst,"request_execute"))
			{
				$rv .= $inst->request_execute($target_obj);
			};
		};
		$prop["value"] = $rv;
	}
	
	function do_org_actions($arr)
	{
		// whee, this thing includes project and that uses properties, so we gots
		// to do this here or something. damn, we need to do the reltype
		// loading in get_instance or something
		$cfgu = get_instance("cfg/cfgutils");
		$cfgu->load_class_properties(array(
			"file" => "project",
			"clid" => 239
		));

		$ob = $arr["obj_inst"];
		$args = array();
		switch($arr["prop"]["name"])
		{
			case "org_calls":
				$args["type"] = 12; //RELTYPE_CALL
				break;
			
			case "org_meetings":
				$args["type"] = 11; //RELTYPE_KOHTUMINE;
				break;
			
			case "org_tasks":
				$args["type"] = 13; //RELTYPE_TASK;
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

		// gather a list of events to show
		$evts = array();

		// XXX: optimize the hell out of it. I have the range, I should use 
		// it.
		foreach($conns as $conn)
		{
			$evts[$conn->prop("to")] = $conn->prop("to");
		};

		$prj = get_instance(CL_PROJECT);
		$evts = $evts + $prj->get_events_for_participant(array(
			"id" => $arr["obj_inst"]->id(),
			"clid" => $this->relinfo[$args["type"]]["clid"],
		));

		$ol = new object_list(array(
			"orderer" => $arr["obj_inst"]->id(),
			"class_id" => CL_CRM_OFFER,
		));
		foreach ($ol->arr() as $tmp)
		{	
			if($tmp->id() == $tmp->brother_of())
			{
				$evts[$tmp->id()] = $tmp->id();
			}
		}
		
		$this->overview = array();
		classload("core/icons");

		foreach($evts as $obj_id)
		{
			$item = new object($obj_id);
			// relative needs last n and next m items, those might be 
			// outside of the current range
			if ($range["viewtype"] != "relative" && $item->prop("start1") < $overview_start)
			{
				continue;
			};
			
			$icon = icons::get_icon_url($item);

			if ($item->class_id() == CL_DOCUMENT)
			{
				$link = $this->mk_my_orb("change",array(
					"id" => $item->id(),
					"return_url" => $return_url,
				),CL_DOCUMENT);
			}
			else
			{
				$link = $planner->get_event_edit_link(array(
					"cal_id" => $this->cal_id,
					"event_id" => $item->id(),
					"return_url" => $return_url,
				));
			};

			if ($item->prop("start1") > $start)
			{
				$t->add_item(array(
					"timestamp" => $item->prop("start1"),
					"data" => array(
						"name" => $item->name(),
						"link" => $link,
						"modifiedby" => $item->prop("modifiedby"),
						"icon" => $icon,
						'comment' => $item->comment(),
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
					"reltype" => $this->crm_company_reltype_workers,
				));
			}
		}
		else if($target_obj->class_id() == CL_CRM_SECTION)
		{
			$target_obj->connect(array(
				"to" => $conn->prop("from"),
				"reltype" => $this->crm_section_reltype_workers,
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
				"reltype" => $this->crm_section_reltype_workers,
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
		@attrib name=search_for_customers
	**/
	function search_for_customers($arr)
	{
		return $this->mk_my_orb(
			'change',array(
				'id' => $arr['id'],
				'group' => $arr['group'],
				'customer_search' => 1,
				'unit' => $arr['unit'],
				'category' => $arr['category'],
				'no_results' => 1
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
		$fake_alias = 0;
		
		reset($arr['check']);

		$fake_alias = current($arr['check']);

		$url = $this->mk_my_orb('change',array(
			'id'=>$cal_id,
			'group'=>'add_event',
			'alias_to_org'=>$fake_alias,
			'reltype_org'=> $arr['reltype'],
			'clid'=> $arr['clid'],
			'alias_to_org_arr'=>urlencode(serialize($arr['check'])),
			//'person_id'=>$person,
		),'planner');
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

	
	function navtoolbar(&$args)
	{
		$RELTYPE_ADDRESS = 3; //crm_company.reltype_address
		
		$toolbar = &$args["prop"]["toolbar"];
		$users = get_instance("users");

		$crm_db_id = $users->get_user_config(array(
			"uid" => aw_global_get("uid"),
			"key" => "kliendibaas",
		));

		// hm, I dunno but there seems to be a conflict here. Because you set the folders
		// through the crm_db class, which means that they can be different for each user
		if (empty($crm_db_id))
		{
			$parents[19] = $parents[8] = $parents[$RELTYPE_ADDRESS] = $args['obj_inst']->parent();
		}
		else
		{
			$crm_db = new object($crm_db_id);
			$default_dir = $crm_db->prop("dir_default");
			$parents[$RELTYPE_ADDRESS] = $crm_db->prop("dir_address") == "" ? $default_dir : $crm_db->prop('dir_address');
			$parents[8] = $crm_db->prop("folder_person") == "" ? $default_dir : $crm_db->prop('folder_person');
		};

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
		$action = array(10, 11, 12, 13);
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
				"alias_to" => $my_org_id
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
					),'planner'),
				"onClick" => "",
				"img" => "icon_cal_today.gif",
				"class" => "menuButton",
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

	function _org_table_header($tf)
	{
		$tf->define_field(array(
			"name" => "name",
			"caption" => t("Organisatsioon"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "pohitegevus",
			"caption" => t("Põhitegevus"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "corpform",
			"caption" => t("Õiguslik vorm"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "email",
			"caption" => t("E-post"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "url",
			"caption" => t("WWW"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "phone",
			"caption" => t('Telefon'),
		));

		$tf->define_field(array(
			"name" => "ceo",
			"caption" => t("Juht"),
			"sortable" => 1,
		));
		
		$tf->define_field(array(
			"name" => "rollid",
			"caption" => t("Rollid"),
			"sortable" => 0,
		));

		$tf->define_chooser(array(
			"field" => "id",
			"name" => "check",
		));
	}

	function org_table(&$arr, $filter=null)
	{
		$tf = &$arr["prop"]["vcl_inst"];
		$this->_org_table_header(&$tf);

		//will list the companys from the category
		//if category is selected
		$organization = &$arr['obj_inst'];
		if($arr['request']['category']!='parent' && is_oid($arr['request']['category']))
		{
			$organization = new object($arr['request']['category']);
		}
		$orgs = $organization->connections_from(array(
			"type" => 'RELTYPE_CUSTOMER',
		));

		$orglist = array();
		foreach($orgs as $org)
		{
			$orglist[$org->prop("to")] = $org->prop("to");
		}

		$rs_by_co = array();
		$role_entry_list = new object_list(array(
			"class_id" => CL_CRM_COMPANY_ROLE_ENTRY,
			"company" => $arr["request"]["id"],
			"client" => $orglist,
			"project" => new obj_predicate_compare(OBJ_COMP_LESS, 1)
		));
		foreach($role_entry_list->arr() as $role_entry)
		{
			$rc_by_co[$role_entry->prop("client")][$role_entry->prop("person")][] = html::get_change_url(
					$arr["request"]["id"], 
					array(
						"group" => "contacts2",
						"unit" => $role_entry->prop("unit"),
					), 
					$role_entry->prop_str("unit")
				)
				."/".
				html::get_change_url(
					$arr["request"]["id"], 
					array(
						"group" => "contacts2",
						"cat" => $role_entry->prop("role")
					), 
					$role_entry->prop_str("role")
				);
		}

		foreach($orgs as $org)
		{
			if($filter)
			{
				if(!in_array($org->prop('to'),$filter))
				{
					continue;
				}
			}
			$o = $org->to();
			// aga ülejäänud on kõik seosed!
			$vorm = $tegevus = $contact = $juht = $juht_id = $phone = $url = $mail = "";
			if (is_oid($o->prop("ettevotlusvorm")))
			{
				$tmp = new object($o->prop("ettevotlusvorm"));
				$vorm = $tmp->prop('shortname');
			};

			if (is_oid($o->prop("pohitegevus")))
			{
				$tmp = new object($o->prop("pohitegevus"));
				$tegevus = $tmp->name();
			};
			
			if (is_oid($o->prop("contact")))
			{
				$tmp = new object($o->prop("contact"));
				$contact = $tmp->name();
			};

			if (is_oid($o->prop("firmajuht")))
			{
				$juht_obj = new object($o->prop("firmajuht"));
				$juht = $juht_obj->name();
				$juht_id = $juht_obj->id();
			};

			if (is_oid($o->prop("phone_id")))
			{
				$ph_obj = new object($o->prop("phone_id"));
				$phone = $ph_obj->name();
			};
			
			if (is_oid($o->prop("url_id")))
			{
				$url_obj = new object($o->prop("url_id"));
				$url = $url_obj->prop("url");
			};

			if (is_oid($o->prop("email_id")))
			{
				$mail_obj = new object($o->prop("email_id"));
				$mail = html::href(array(
					"url" => "mailto:" . $mail_obj->prop("mail"),
					"caption" => $mail_obj->prop("mail"),
				));

			};

			$roles = $this->_get_role_html(array(
				"from_org" => $arr["request"]["id"],
				"to_org" => $o->id(),
				"rc_by_co" => $rc_by_co
			));

			$tf->define_data(array(
				"id" => $o->id(),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $o->id(),
					),$o->class_id()),
					"caption" => $o->name(),
				)),
				"reg_nr" => $o->prop("reg_nr"),
				"pohitegevus" => $tegevus,
				"corpform" => $vorm,
				"address" => $contact,
				"ceo" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $juht_id,
					),CL_CRM_PERSON),
					"caption" => $juht,
				)),
				"phone" => $phone,
				"url" => html::href(array(
					"url" => $url,
					"caption" => $url,
				)),
				"email" => $mail,
				'rollid' => $roles,
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
			foreach($arr['check'] as $key=>$value)
			{
				if ($main_obj->is_connected_to(array("to" => $value)))
				{
					$main_obj->disconnect(array('from'=>$value));
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
		$this->crm_company_init();
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
		}

		return $this->mk_my_orb('change',array(
			'id' => $arr['id'],
			'group' => 'my_customers',
			'category' => $arr['category']
		));
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
		//for post stuff
		if(array_key_exists('request',$arr))
		{
			$this->do_search = $arr['request']['contact_search'];
			$this->show_customer_search = $arr['request']['customer_search'];
			//pean processima tulnud infot, tundub küll imelik koht
			//ilmselt kui vale, keegi hakkab karjuma
		}
		//for get stuff
		else
		{
			$this->do_search = $arr['contact_search'];
			$this->show_customer_search = $arr['customer_search'];
		}

		if(is_oid($arr['request']['cat']))
		{
			$this->cat = $arr['request']['cat'];
		}

		//stuff
		if((int)$arr['request']['unit'])
		{
			//section relations are active now
			$this->unit=$arr['request']['unit'];
			$this->reltype_section = $this->crm_section_reltype_section;
			$this->reltype_professions = $this->crm_section_reltype_professions;
			$this->reltype_workers = $this->crm_section_reltype_workers;
		}
		else
		{
			//company relations are default
			$this->reltype_section = $this->crm_company_reltype_section;
			$this->reltype_professions = $this->crm_company_reltype_professions;
			$this->reltype_workers = $this->crm_company_reltype_workers;
		}

		if(is_oid($arr['request']['category']))
		{
			$this->category=$arr['request']['category'];
		}
		
		if(is_oid($arr['request']['category']))
		{
			$this->reltype_category = $this->crm_category_reltype_category;
		}
		else
		{
			$this->reltype_category = $this->crm_company_reltype_category;
		}
	}

	/*
		kõik lingid saavad $key muutuja lisaks
	*/
	function callback_mod_reforb($arr)
	{
		$arr['unit'] = $this->unit;
		$arr['category'] = $this->category;
		$arr['return_url'] = urlencode(aw_global_get('REQUEST_URI'));
		$arr['cat'] = $this->cat;
	}

	/**
		@attrib name=create_new_company
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
				'reltype' => 3, //crm_company.reltype_address
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
	
		if($this->show_customer_search)
		{
			$arr['args']['customer_search_name'] = urlencode($arr['request']['customer_search_name']);
			$arr['args']['customer_search_reg'] = urlencode($arr['request']['customer_search_reg']);
			$arr['args']['customer_search_address'] = urlencode($arr['request']['customer_search_address']);
			$arr['args']['customer_search_leader'] = urlencode($arr['request']['customer_search_leader']);
			$arr['args']['customer_search_city'] = urlencode($arr['request']['customer_search_city']);
			$arr['args']['customer_search_county'] = urlencode($arr['request']['customer_search_county']);
			$arr['args']['customer_search_field'] = urlencode($arr['request']['customer_search_field']);
			$arr['args']['customer_search_only'] = $arr['request']['customer_search_only'];
			$arr['args']['customer_search'] = $this->show_customer_search;
			$arr['args']['group'] = 'customers';
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

	function do_contact_toolbar($tb,$arr)
	{
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));
		
		$alias_to = $arr['obj_inst']->id();
		
		if((int)$arr['request']['unit'])
		{
			$alias_to = $arr['request']['unit'];
		}

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Töötaja'),
			'link'=>aw_url_change_var(array(
				'action' => 'create_new_person',
				'parent' => $arr['obj_inst']->id(),
				'alias_to' => $alias_to,
				'reltype' => $this->reltype_workers,
				'return_url' => urlencode(aw_global_get('REQUEST_URI')),
				"class" => "crm_company",
				"profession" => $arr["request"]["cat"]
			))
		));
		
		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Üksus'),
			'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=> $this->reltype_section,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI'))
				),
				'crm_section'
			)
		));
		
		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Ametinimetus'),
			'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=> $this->reltype_professions,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI'))
				),
				'crm_profession'
			)
		));
	
		//delete button
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			'action' => 'submit_delete_relations',
		));
	
		//uus kõne
		$tb->add_button(array(
			'name' => 'Kone',
			'img' => 'class_223.gif',
			'tooltip' => t('Tee kõne'),
			'action' => 'submit_new_call'
		));

		//uus date
		$tb->add_button(array(
			'name' => 'Kohtumine',
			'img' => 'class_224.gif',
			'tooltip' => t('Uus kohtumine'),
			'action' => 'submit_new_meeting'
		));

		//uus task
		$tb->add_button(array(
			'name' => 'Toimetus',
			'img' => 'class_244.gif',
			'tooltip' => t('Uus toimetus'),
			'action' => 'submit_new_task'
		));

		$tb->add_separator();

		$tb->add_button(array(
			'name' => 'Search',
			'img' => 'search.gif',
			'tooltip' => t('Otsi'),
			'action' => 'search_for_contacts'
		));

		if($arr['request']['contact_search'])
		{
			$tb->add_button(array(
				'name' => 'Save',
				'img' => 'save.gif',
				'tooltip' => t('Salvesta'),
				'action' => 'save_search_results'
			));
		}

		$tb->add_separator();

		$tb->add_button(array(
			"name" => "cut",
			"img" => "cut.gif",
			"tooltip" => t("L&otilde;ika"),
			"action" => "cut_p",
		));

		$tb->add_button(array(
			"name" => "copy",
			"img" => "copy.gif",
			"tooltip" => t("Kopeeri"),
			"action" => "copy_p",
		));

		if (is_array($_SESSION["crm_cut_p"]) || is_array($_SESSION["crm_copy_p"]))
		{
			$tb->add_button(array(
				"name" => "paste",
				"img" => "paste.gif",
				"tooltip" => t("Kleebi"),
				"action" => "paste_p",
			));
		}
	}
	
	function do_my_customers_toolbar($tb, $arr)
	{
		//delete button
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			'action' => 'submit_delete_my_customers_relations',
		));
	}

	function do_customer_toolbar($tb, $arr)
	{
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$alias_to = $arr['obj_inst']->id();
		$rel_type = 30; //RELTYPE_CATEGORY;
		
		if((int)$arr['request']['category'])
		{
			$alias_to = $arr['request']['category'];
			$parent = (int)$arr['request']['category'];
			$rel_type = 30; //RELTYPE_CATEGORY;
		}

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Kategooria'),
			'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=>$this->reltype_category,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI'))
				),
				'crm_category'
			)
				
		));

		if (is_oid($arr["request"]["category"]))
		{
			$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text' => t('Klient'),
				'link' => $this->mk_my_orb('new',array(
						'parent' => $arr['obj_inst']->id(),
						'alias_to' => $alias_to,
						'reltype' => 3, // crm_category.CUSTOMER,
						'return_url' => urlencode(aw_global_get('REQUEST_URI'))
					),
					'crm_company'
				)
			));
		}

		//delete button
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud'),
			'action' => 'submit_delete_customer_relations',
		));

		$tb->add_separator();

		$tb->add_button(array(
			'name' => 'Search',
			'img' => 'search.gif',
			'tooltip' => t('Otsi'),
			'action' => 'search_for_customers'
		));
		
		if($arr['request']['customer_search'])
		{
			$tb->add_button(array(
				'name' => 'Save',
				'img' => 'save.gif',
				'tooltip' => t('Salvesta'),
				'action' => 'save_customer_search_results'
			));
		}
	}
	
	function do_personal_candidates_toolbar($toolbar, $arr)
	{
		$toolbar->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud tööpakkumised'),
		));
	}
	
	function do_personal_offers_toolbar($toolbar, $arr)
	{
		$toolbar->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=>t('Uus')
		));
		
		$toolbar->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud tööpakkumised'),
			'action' => 'delete_selected_objects',
			'confirm' => t("Kas oled kindel et soovid valitud tööpakkumised kustudada?")
		));

		if($arr["request"]["cat"] && $arr["request"]["unit"])
		{
			$alias_to =  $arr["request"]["unit"];
			$reltype = 4;
		}
		else
		{
			$alias_to = $arr["obj_inst"]->id();
			$reltype = 19;
		}
		
		$toolbar->add_menu_item(array(
				'parent'=>'add_item',
				'text'=> t('Tööpakkumine'),
				'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=> $reltype,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI')),
					'cat' => $arr["request"]["cat"],
					'unit' => $arr["request"]["unit"],
					'org' => $arr['obj_inst']->id(),
				), CL_PERSONNEL_MANAGEMENT_JOB_OFFER)
		));
	}

	/*
		loops the customer_search_results and draws a fancy table
	*/
	function do_search($arr, $results)
	{
		global $awt;

		$awt->start("crm-company-org-search");
		
		$tf = &$arr["prop"]["vcl_inst"];
		$tf->define_field(array(
				"name" => "name",
				"caption" => t("Organisatsioon"),
				"sortable" => 1,
		));

		$tf->define_field(array(
				"name" => "corpform",
				"caption" => t("Õiguslik vorm"),
				"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "email",
			"caption" => t("E-post"),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "url",
			"caption" => t("WWW"),
			"sortable" => 1,
		));
		$tf->define_field(array(
			"name" => "phone",
			"caption" => t('Telefon'),
			"sortable" => 1,
		));

		$tf->define_field(array(
			"name" => "ceo",
			"caption" => t("Juht"),
			"sortable" => 1,
		));

		$tf->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));

		$count = 0;
		for ($o = $results->begin(); !$results->end(); $o = $results->next())
		{
			if($no_results)
			{
				break;
			}
			$count++;
			// aga ülejäänud on kõik seosed!
			$vorm = $tegevus = $contact = $juht = $juht_id = $phone = $url = $mail = "";
			if (is_oid($o->prop("ettevotlusvorm")) && $this->can("view", $o->prop("ettevotlusvorm")))
			{
				$tmp = new object($o->prop("ettevotlusvorm"));
				$vorm = $tmp->name();
			};

			if (is_oid($o->prop("contact")))
			{
				$tmp = new object($o->prop("contact"));
				$contact = $tmp->name();
			};

			if (is_oid($o->prop("firmajuht")))
			{
				$juht_obj = new object($o->prop("firmajuht"));
				$juht = $juht_obj->name();
				$juht_id = $juht_obj->id();
			};

			if (is_oid($o->prop("phone_id")))
			{
				$ph_obj = new object($o->prop("phone_id"));
				$phone = $ph_obj->name();
			};
				
			
			if (is_oid($o->prop("url_id")))
			{
				$url_obj = new object($o->prop("url_id"));
				$url = $url_obj->prop("url");
				// I dunno, sometimes people write url into the name field and expect this to work
				if (empty($url))
				{
					$url = $url_obj->name();
				};
			};

			if (is_oid($o->prop("email_id")))
			{
				$mail_obj = new object($o->prop("email_id"));
				$mail = html::href(array(
					"url" => "mailto:" . $mail_obj->prop("mail"),
					"caption" => $mail_obj->prop("mail"),
				));

			};

			$tf->define_data(array(
				"id" => $o->id(),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $o->id(),
					),$o->class_id()),
					"caption" => $o->name(),
				)),
				"reg_nr" => $o->prop("reg_nr"),
				//"pohitegevus" => $tegevus,
				"corpform" => $vorm,
				"address" => $contact,
				"ceo" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $juht_id,
					),CL_CRM_PERSON),
					"caption" => $juht,
				)),
				"phone" => $phone,
				"url" => html::href(array(
					"url" => $url,
					"caption" => $url,
				)),
				"email" => $mail,
			));
		}

		if ($count == 0)
		{
			$tf->set_header("Otsing ei leidnud ühtegi objekti");
		};
		$awt->stop("cmr-company-org-search");
		return $count;
	}

	/*
		constructs a object list base on the xfilter
	*/
	function get_customer_search_results($xfilter)
	{	
		$company_id = $xfilter['company_id'];
		unset($xfilter['company_id']);
		if (!sizeof($xfilter))
		{
			return false;
		};

		if($xfilter['no_results'])
		{
			return false;
		}

		if (sizeof($xfilter['firmajuht']))
		{
			// search by ceo name? first create a list of all crm_persons
			// that match the search criteria and after that create a list
			// of crm_companies that have one of the results as a ceo
			$ceo_filter = array(
				"class_id" => CL_CRM_PERSON,
				"limit" => 100,
				"name" => "%" . $xfilter['firmajuht'] . "%",
			);
			$ceo_list = new object_list($ceo_filter);
			if (sizeof($ceo_list->ids()) > 0)
			{
				$xfilter['firmajuht'] = &$filter['firmajuht'];
			};
		};

		$addr_xfilter = array();
		$no_results = false;

		if(sizeof($xfilter['linn']))
		{
			$city_list = new object_list(array(
				'class_id'=>CL_CRM_CITY,
				'limit' => 100,
				'name' => $xfilter['linn'],
			));
							
			if(sizeof($city_list->ids()))
			{
				$addr_xfilter['linn'] = $city_list->ids();
			}
			else
			{
				$no_results = true;
			}
			unset($xfilter['linn']);
		}

		if(sizeof($xfilter['maakond']))
		{
			$county_list = new object_list(array(
				'class_id' => CL_CRM_COUNTY,
				'limit' => 100,
				'name' => $xfilter['maakond']
			));
			if(sizeof($county_list->ids()))
			{
				$addr_xfilter['maakond'] = $county_list->ids();
			}
			else
			{
				$no_results = true;
			}
			unset($xfilter['maakond']);
		}
	
		if(sizeof($xfilter['address']))
		{
			$addr_xfilter['name'] = &$xfilter['address'];
			unset($xfilter['address']);
		}

		if (sizeof($addr_xfilter)>0)
		{
			$addr_xfilter['class_id'] = CL_CRM_ADDRESS;
			$addr_xfilter['limit'] = 100;

			$addr_list = new object_list($addr_xfilter);

			if (sizeof($addr_list->ids()) > 0)
			{
				$xfilter['contact'] = $addr_list->ids();
			}
			else
			{
				$no_results=true;
			}
		}


		if(sizeof($xfilter['pohitegevus']))
		{
			$tmp_filter['class_id'] = CL_CRM_SECTOR;
			$tmp_filter['limit'] = 100;
			$tmp_filter['name'] = $xfilter['pohitegevus'];
			$tmp_list = new object_list($tmp_filter);
			unset($xfilter['pohitegevus']);
			if(sizeof($tmp_list->ids())>0)
			{
				$xfilter['pohitegevus'] = $tmp_list->ids();
			}
			else
			{
				$no_results=true;
			}
		}
		
		if($xfilter['customer_search_only']=='company')
		{
			//have to get the list of all the clients for this company
			$company = new object($company_id);
			$data = array();
			$this->get_customers_for_company($company, &$data);
			foreach($data as $value)
			{
				$xfilter['oid'][$value] = $value;	
			}
		}
		else if($xfilter['customer_search_only']=='person')
		{
			//have to get the list of all the companys for
			//this users person
			$us = get_instance(CL_USER);
			$person = obj($us->get_current_person());
			//if the user has a person's object associated with him
			if($person)
			{
				//genereerin listi persooni kõikidest firmadest
				$person = new object($person);
				$conns=$person->connections_from(array(
					'type' => 22,//crm_person.reltype_CLIENT_IM_HANDLING
				));
				foreach($conns as $conn)
				{
					$xfilter['oid'][$conn->prop('to')] = $conn->prop('to');
				}
			}
			else
			{
				//@todo võix visata errori, aga peax mõtlema kuidas see error peax välja nägema
				//
			}
		}
		unset($xfilter['customer_search_only']);
		if(!$no_results)
		{
			return new object_list($xfilter);
		}
		else
		{
			return new object_list(NULL);
		}
	}

	/*
		constructs the xfilter for get_customer_search_results
	*/
	function construct_customer_search_filter($arr)
	{
		//i'll try the search from crm_org_search.aw
		$searchable_fields = array('customer_search_name' => 'name',
			'customer_search_reg' => 'reg_nr',
			'customer_search_address'=> 'address',
			'customer_search_city' => 'linn',
			'customer_search_county' => 'maakond',
			'customer_search_field' => 'pohitegevus',
			'customer_search_leader' => 'firmajuht'
		);

		$search_params = array('class_id'=>CL_CRM_COMPANY,'limit'=>100,'sort_by'=>'name');

		foreach($searchable_fields as $key=>$value)
		{
			if($arr['request'][$key])
			{
				//let's clean up the item
				$tmp_arr = explode(',',$arr['request'][$key]);
				array_walk($tmp_arr,create_function('&$param','$param = trim($param);'));
				array_walk($tmp_arr,create_function('&$param','$param = "%".$param."%";'));
				$search_params[$value] = $tmp_arr;
			}
		}

		if(!in_array($arr['request']['customer_search_only'], array('all','company','person')))
		{
			$search_params['customer_search_only'] = 'all';
		}
		else
		{
			$search_params['customer_search_only'] = $arr['request']['customer_search_only'];
		}

		$search_params['company_id'] = $arr['request']['id'];

		if($arr['request']['no_results'])
		{
			$search_params['no_results'] = true;
			return $search_params;
		}
		else
		{
			return $search_params;
		}
	}

	function do_contacts_search_results($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi'),
			'sortable' => '1',
			'callback' => array(&$this, 'callb_human_name'),
			'callb_pass_row' => true,
		));
		$t->define_field(array(
			'name' => 'phone',
			'caption' => t('Telefon'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'email',
			'caption' => t('E-post'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'section',
			'caption' => t('Üksus'),
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'rank',
			'caption' => t('Ametinimetus'),
			'sortable' => '1',
		));
		$t->define_chooser(array(
			'name'=>'check',
			'field'=>'id',
		));

		$search_params = array(
			'class_id' => CL_CRM_PERSON,
			'limit' => 50,
			'sort_by'=>'name'
		);

		if($arr['request']['contact_search_firstname'])
		{
			//$search_params['CL_CRM_PERSON.firstname'] = $arr['request']['contact_search_firstname'];
			$search_params['firstname'] = '%'.urldecode($arr['request']['contact_search_firstname']).'%';
		}

		if($arr['request']['contact_search_lastname'])
		{
			//$search_params['CL_CRM_PERSON.lastname'] = $arr['request']['contact_search_lastname'];
			$search_params['lastname'] = '%'.urldecode($arr['request']['contact_search_lastname']).'%';
		}

		if($arr['request']['contact_search_code'])
		{
			//$search_params['CL_CRM_PERSON.personal_id'] = $arr['request']['contact_search_code'];
			$search_params['personal_id'] = '%'.urldecode($arr['request']['contact_search_code']).'%';
		}
	
		//let's try to get certain fields
		$search_params['sort_by'] = 'name';

		$ol = new object_list($search_params);

		$pl = get_instance(CL_PLANNER);
		$person = get_instance(CL_CRM_PERSON);
		$cal_id = $pl->get_calendar_for_user(array('uid'=>aw_global_get('uid')));

		for($o=$ol->begin();!$ol->end();$o=$ol->next())
		{
			$person_data = $person->fetch_person_by_id(array(
				'id' => $o->id(),
				'cal_id' => $calid
			));
			$t->define_data(array(
				"name" => $o->prop('name'),
				"id" => $o->id(),
				"phone" => $person_data['phone'],
				"rank" => $person_data["rank"],
				'section' => $person_data['section'],
				"email" => html::href(array(
					"url" => "mailto:" . $person_data['email'],
					"caption" => $person_data['email'],
				)),
			));
		}
	}
	
	/**
		@attrib name=save_customer_search_results
	**/
	function save_customer_search_results($arr)
	{
		if(is_array($arr['sel']))
		{
			$to = 0;
			$reltype = -1;
			$from = null;
			if(is_oid($arr['category']))
			{
				$reltype = 3; //crm_category.customer
				$from = new object((int)$arr['category']);
			}
			else
			{
				$reltype = 22; //crm_company.RELTYPE_CUSTOMER;
				$from = new object((int)$arr['id']);
			}

			foreach($arr['sel'] as $key=>$value)
			{
				$from->connect(array(
					'to'=>$value,
					'reltype'=>$reltype,
				));
			}
		}
		return $this->mk_my_orb('change',array(
				'id' => $arr['id'],
				'category' => $arr['category'],
				'group' => $arr['group'],
			),
			$arr['class']
		);
	}

	
	/**
		@attrib name=save_search_results
	**/
	/*
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
			
			// run user creation
			$cuc = get_instance("crm/crm_user_creator");
			$cuc->on_save_person(array(
				"oid" => $person->id()
			));
		}

		return $this->mk_my_orb('change',array(
				'id' => $arr['id'],
				'unit' => $arr['unit'],
				'group' => $arr['group'],
			),
			$arr['class']
		);
	}
	*/

	//goes through all the relations and builds a set of id into $data
	function get_customers_for_company($obj, $data, $category=false)
	{
		//if the $obj is a category
		if($category)
		{
			$conns = $obj->connections_from(array(
				'type' => 3,//crm_category.reltype_CUSTOMER
			));
		}
		//if the $obj is a company
		else
		{
			$conns = $obj->connections_from(array(
				'type' => "RELTYPE_CUSTOMER",
			));
		}
		foreach($conns as $conn)
		{
			$data[$conn->prop('to')] = $conn->prop('to');
		}

		//let's look through the categories
		if($category)
		{
			$conns = $obj->connections_from(array(
				'type' => 2, //crm_category.RELTYPE_CATEGORY
			));
		}
		else
		{
			$conns = $obj->connections_from(array(
				'type' => "RELTYPE_CATEGORY",
			));
		}
		
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
				'type' => "RELTYPE_PROJECT",
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
	
	function do_offers_listing_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		if(!$arr["request"]["org_id"])
		{
			$table->define_field(array(
				"name" => "org",
				"caption" => t("Organisatsioon"),
				"sortable" => "1",
				"align" => "center",
			));
		}
		
		$table->define_field(array(
			"name" => "offer_name",
			"caption" => t("Nimi"),
			"sortable" => "1",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "salesman",
			"caption" => t("Koostaja"),
			"sortable" => "1",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "offer_made",
			"caption" => t("Lisatud"),
			"sortable" => "1",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "offer_sum",
			"caption" => t("Summa"),
			"sortable" => "1",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "offer_status",
			"caption" => t("Staatus"),
			"sortable" => "1",
			"align" => "center",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"field" => "select",
			"caption" => t("X"),
		));
		
		$offer_inst = get_instance(CL_CRM_OFFER);
		if($arr["request"]["org_id"])
		{
			$offers = &$offer_inst->get_offers_for_company($arr["request"]["org_id"], $arr["obj_inst"]->id());
		}
		else
		{
			$params = array(
				"preformer" => $arr["obj_inst"]->id(),
				"offer_status" => array(0,1,2),
				"class_id" => CL_CRM_OFFER,
			);
			
			if(is_oid($arr["request"]["category"]))
			{
				$cat = &obj($arr["request"]["category"]);
				$data = array();
				$this->get_customers_for_company($cat,&$data,true);
				foreach ($data as $org)
				{
					$offer_obj = $offer_inst->get_offers_for_company($org, $arr["obj_inst"]->id());
					foreach ($offer_obj->arr() as $tmp)
					{
						$ids[] = $tmp->id();
					}
				}
				$params["oid"] = $ids;
				if(count($ids)>0)
				{
					$offers = new object_list($params);
				}
			}
			if(!$arr["request"]["org_id"] && !$arr["request"]["category"])
			{
				$offers = new object_list($params);
			}
		}
		
		if(is_object($offers))
		{
			if($offers->count() > 0)
			{
				$statuses = array(
					t("Koostamisel"), 
					t("Saadetud"), 
					t("Esitletud"), 
					t("Tagasilükatud"), 
					t("Positiivelt lõppenud")
				);
				foreach ($offers->arr() as $offer)
				{
					//Do not list brother offers
					if($offer->is_brother())
					{
						continue;
					}
					$org = &obj($offer->prop("orderer"));
					if($this->can("view", $offer->prop("salesman")))
					{
						$salesman = &obj($offer->prop("salesman"));
						$salesmanlink = html::get_change_url($salesman->id(), array(), $salesman->name());
					}
					$table->define_data(array(
						"org" => is_object($org)?html::get_change_url($org->id(), array(), $org->name()):false,
						"salesman" => $salesmanlink,
						"offer_name" => html::get_change_url($offer->id(), array(), $offer->name()),
						"offer_made" => $offer->created(),
						"offer_sum" => $offer->prop("sum"),//$offer_inst->total_sum($offer->id()),
						"select" => $offer->id(),
						"offer_status" => $statuses[$offer->prop("offer_status")],
						"offer_nr_status" => $offer->prop("offer_status"),
					));
					$table->set_default_sortby("offer_made");
					$table->set_default_sorder('desc');
				}
			}
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
	
	
	function do_offers_listing_tree($arr)
	{
		get_instance("core/icons");

		// list all child rels
		$parents = array();
		$c = new connection();
		foreach($c->find(array("from" => $data, "type" => 7 /* "RELTYPE_CHILD_ORG" */)) as $rel)
		{
			$parents[$rel["to"]] = $rel["from"];
		}

		$tree = &$arr["prop"]["vcl_inst"];
		$node_id = 0;
		$this->active_node = (int)$arr['request']['category'];
		$this->generate_tree(array(
			'tree_inst' => &$tree,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_CATEGORY',
			'attrib' => 'category',
			'leafs' => "do_offer_tree_leafs",
			'style' => 'nodetextbuttonlike',
			'parent2chmap' => $parents
		));
		
		$node_id++;
		$tree->add_item(0, array(
			'id' => $node_id,
			'name' => t('Kõik organisatsioonid'),
			'url' => '',
		));
		
		$all_org_parent = $node_id;
		
		$data = array();
		$this->get_customers_for_company($arr["obj_inst"], &$data);

		foreach ($data as $customer)
		{
			$obj = &obj($customer);
			$pt = $all_org_parent;
			if (isset($parents[$customer]))
			{
				$pt = "ao".$parents[$customer];
			}
			$tree->add_item($pt, array(
				'id' => "ao".$customer,
				'name' => $obj->id()==$arr["request"]["org_id"]?"<b>".$obj->name()."</b>":$obj->name(),
				'iconurl' => icons::get_icon_url($obj->class_id()),
				'url' => aw_url_change_var(array('org_id' => $obj->id())),
			));
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
	
	function do_offers_listing_toolbar(&$arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));
		
		$params = array(
				'alias_to'=> $arr['obj_inst']->id(),
				'reltype'=> 9, //RELTYPE_OFFER,
				//'return_url'=> urlencode(aw_global_get('REQUEST_URI')),
				'org' => $arr['obj_inst']->id(),
				'alias_to_org' => $arr['request']['org_id'],
		);
		
		$tb->add_menu_item(array(
				'disabled' => $arr['request']['org_id']? false : true,
				'parent'=>'add_item',
				'text'=>t('Pakkumine'),
				'url' => html::get_new_url(CL_CRM_OFFER, $arr['obj_inst']->id(), $params),
		));
		
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_selected_objects",
			"confirm" => t("Kas oled kindel, et soovid valitud pakkumise(d) kustutada?"),
			"tooltip" => t("Kustuta")
		));
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
		$person->save();
		return html::get_change_url($person->id())."&return_url=".urlencode($arr["return_url"]);
	}
	
	function do_objects_listing_toolbar($arr)
	{
		$tb = & $arr["prop"]["toolbar"];
				
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=>t('Uus')
		));
				
		//Add classes
		foreach ((aw_ini_get("classes")) as $class_id => $classinfo)
		{
			$parents = split(",",$classinfo["parents"]);
			$newparent = $arr["request"]["parent"]? $arr["request"]["parent"]:$arr["obj_inst"]->id();
			
			if(count($parent) == 0)
			{
				$parents[] = "add_item";
			}
			
			foreach ($parents as $parent)
			{	
				$tb->add_menu_item(array(
					//'disabled' => $classinfo["can_add"]==0?false:true,
					'parent'=> $parent,
					'text'=> $classinfo["name"],
					'url' => html::get_new_url($class_id, $newparent),
				));
			}
		}
		//Add submenus
		foreach ((aw_ini_get("classfolders")) as $key => $menu)
		{
			$tb->add_sub_menu(array(
    			"parent" => $menu["parent"]?$menu["parent"]:'add_item',
    			"name" => $key,
   	 			"text" => $menu["name"],
    		));
		}
		
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud objektid'),
			'action' => 'delete_selected_objects',
		));
		
		$tb->add_button(array(
			'name' => 'cut',
			'img' => 'cut.gif',
			'tooltip' => t('Cut'),
			'action' => 'cut',
		));
		
		if($_SESSION["crm_cut"])
		{
			$tb->add_button(array(
				'name' => 'paste',
				'img' => 'paste.gif',
				'tooltip' => t('Paste'),
				'url' => $this->mk_my_orb("paste", array(
					"parent" => $arr["request"]["parent"],
					"id" => $arr["obj_inst"]->id(),
					"group" => $arr["request"]["group"],
						), CL_CRM_COMPANY),//'paste',
				'disabled' => 'true',
			));
		}
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
	
	function do_objects_listing_tree($arr)
	{
		classload("core/icons");
		$tree = &$arr["prop"]["vcl_inst"];
		$ot = new object_tree(array(
    		"parent" => $arr["obj_inst"]->id(),
    		"class_id" => CL_MENU
		));
		$ol = $ot->to_list();
		
		foreach ($ol->arr() as $obj)
		{
			if($obj->parent() == $arr["obj_inst"]->id())
			{
				$parent = 0;
			}
			else
			{
				$parent = $obj->parent();
			}
			$tree->add_item($parent, array(
				'id' => $obj->id(),
				'name' => $obj->id()==$arr["request"]["parent"]?"<b>".$obj->name()."</b>":$obj->name(),
				'iconurl' => icons::get_icon_url($obj->class_id()),
				'url' => aw_url_change_var(array('parent' => $obj->id())),
			));
		}
	}
	
	function do_object_table_header(&$table)
	{
		$table->define_field(array(
			"name" => "icon",
			"width" => 15
		));
			
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"sortable" => "1",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "class_id",
			"caption" => t("Tüüp"),
			"sortable" => "1",
			"callback" => array(&$this, "get_class_name"),
		));
			
		$table->define_chooser(array(
			"name" => "select",
			"field" => "select",
		));
	}
	
	function get_class_name($id)
	{
		$classes = aw_ini_get("classes"); 
		return $classes[$id]["name"];
	}
	
	function define_object_table_data(&$arr)
	{
		$classes = aw_ini_get("classes");
		unset($classes[CL_RELATION]);
		$class_ids = array_keys($classes);
		
		$ol = new object_list(array(
			"parent" => $arr["request"]["parent"] ? $arr["request"]["parent"] : $arr["obj_inst"]->id(),
			"class_id" => $class_ids 
		));
		
		$table = &$arr["prop"]["vcl_inst"];
		
		get_instance("core/icons");
		foreach ($ol->arr() as $item)
		{
			$table->define_data(array(
				"class_id" => $item->class_id(),
				"name" => html::href(array(
					"url" => html::get_change_url($item->id()),
					"caption" => $item->name(),
				)),
				"modified" => get_lc_date($item->modified()),
				"select" => $item->id(),
				"icon" => html::img(array(
					"url" => icons::get_icon_url($item->class_id()),
				)),
			));
		}
	}
	
	function do_objects_listing_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		$this->do_object_table_header($table);
		$this->define_object_table_data($arr);
	}

	function get_client_manager($arr)
	{
		$manager = $arr['obj_inst']->get_first_conn_by_reltype('RELTYPE_CLIENT_MANAGER');
		if($manager)
		{
			return $manager;
		}
		else
		{
			$obj = new object();
			$obj->set_class_id(CL_CRM_MANAGER);
			$obj->set_parent($arr['obj_inst']->id());
			$obj->save();
			$arr['obj_inst']->connect(array(
				'to' => $obj->id(),
				'reltype' => 'RELTYPE_CUSTOMER',
			));
			
			return $obj;
		}
	}
	
	function do_projects_listing_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
	}
	
	function do_projects_listing_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$this->do_projects_table_header(&$table);
		
		$project_conns = new connection();
	
		if(!$arr["request"]["org_id"])
		{
			return;
		}
		
		$project_conns = $project_conns->find(array(
			"to" => $arr["request"]["org_id"],
			"reltype" => 10,
			"from.class_id" => CL_PROJECT
		));
		
		if(count($project_conns) == 0)
		{
			return 0;
		}
		
		foreach ($project_conns as $conn)
		{
			$tmp_ids[] = $conn["from"];
		}
		
		$ol = new object_list(array(
			"oid" => $tmp_ids,
		));

		$rs_by_co = array();
		$role_entry_list = new object_list(array(
			"class_id" => CL_CRM_COMPANY_ROLE_ENTRY,
			"company" => $arr["request"]["id"],
			"client" => $arr["request"]["org_id"],
			"project" => $ol->ids()
		));
		foreach($role_entry_list->arr() as $role_entry)
		{
			$rc_by_co[$role_entry->prop("client")][$role_entry->prop("project")][$role_entry->prop("person")][] = html::get_change_url(
					$arr["request"]["id"], 
					array(
						"group" => "contacts2",
						"unit" => $role_entry->prop("unit"),
					), 
					$role_entry->prop_str("unit")
				)
				."/".
				html::get_change_url(
					$arr["request"]["id"], 
					array(
						"group" => "contacts2",
						"cat" => $role_entry->prop("role")
					), 
					$role_entry->prop_str("role")
				);
		}
		
		foreach ($ol->arr() as $project)
		{
			$participants = $project->connections_from(array(
				"type" => "RELTYPE_PARTICIPANT",
			));
			
			foreach ($participants as $participant)
			{
				$partic_row .= " ".html::href(array(
					"url" => html::get_change_url($participant->prop("to")),
					"caption" => $participant->prop("to.name"),
				)); 
			}

			$roles = $this->_get_role_html(array(
				"from_org" => $arr["request"]["id"],
				"to_org" => $arr["request"]["org_id"],
				"rc_by_co" => $rc_by_co,
				"to_project" => $project->id()
			));

			$table->define_data(array(
				"project_name" => html::get_change_url($project->id(), array(), $project->name()),
				"project_participants"	=> $partic_row,
				"project_created" => get_lc_date($project->created()),
				"roles" => $roles
			));
		}
	}
	
	function do_projects_table_header(&$table)
	{
		$table->define_field(array(
			"name" => "project_name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "project_participants",
			"caption" => t("Osalejad"),
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "project_created",
			"caption" => t("Loodud"),
			"sortable" => 1,
		));

		$table->define_field(array(
			"name" => "roles",
			"caption" => t("Rollid"),
			"sortable" => 0,
		));
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
	
	function do_my_projects_table(&$arr)
	{	
		$table = &$arr["prop"]["vcl_inst"];
		$this->do_projects_table_header(&$table);
		
		$uid = users::get_oid_for_uid(aw_global_get("uid"));
		$conns = new connection();
		$conns_arr = $conns->find(array(
			"from.class_id" => CL_PROJECT,
			"to" => $uid,
			"type" =>  2,
		));
		foreach ($conns_arr as $my_project)
		{
			$project_obj = &obj($my_project["from"]);
			
			$participants = array();
			$participants = $project_obj->connections_from(array(
				"type" => "RELTYPE_PARTICIPANT",
			));
			
			$partic_row = "";
			foreach ($participants as $participant)
			{
				$partic_row .= " ".html::href(array(
					"url" => html::get_change_url($participant->prop("to")),
					"caption" => $participant->prop("to.name"),
				)); 
			}
			
			$table->define_data(array(
				"project_name" => html::get_change_url($project_obj->id(), array(), $project_obj->name()),
				"project_participants" => $partic_row,
				"project_created" => get_lc_date($project_obj->created()),
			));
		}
	}

	/** implement our own view!

		@attrib name=view nologin=1

		@param id required
		@param cfgform optional

	**/
	function view($arr)
	{
		if ($arr["cfgform"])
		{
			$cfg = get_instance(CL_CFGFORM); 
			$props = $cfg->get_props_from_cfgform(array("id" => $arr["cfgform"]));
		}
		else
		{
			$cfg = get_instance("cfg/cfgutils");
			$props = $cfg->load_properties(array(
				"clid" => CL_CRM_COMPANY
			));
		}

		$this->read_template("show.tpl");

		$o = obj($arr["id"]);

		foreach($props as $pn => $pd)
		{
			//echo "$pn => $pd[caption] <br>";
			$this->vars(array(
				"prop" => $pd["caption"],
				"value" => nl2br($o->prop_str($pn, in_array($pn, array("ettevotlusvorm", "firmajuht", "telefax_id"))))
			));
			$l .= $this->parse("LINE");
		}

		$this->vars(array(
			"LINE" => $l
		));
		return $this->parse();
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

		return urldecode($arr["return_url"]);
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

		return urldecode($arr["return_url"]);
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
						"type" => 21 // crm_person.reltype_section
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

		return urldecode($arr["return_url"]);
	}

	function _get_role_html($arr)
	{
		extract($arr);
		$role_url = $this->mk_my_orb("change", array(
			"from_org" => $from_org,
			"to_org" => $to_org,
			"to_project" => $to_project
		), "crm_role_manager");

		$roles = array();
			
		$iter = safe_array($rc_by_co[$to_org]);
		if (!empty($to_project))
		{
			$iter = safe_array($rc_by_co[$to_org][$to_project]);
		}
		foreach($iter as $r_p_id => $r_p_data)
		{
			$r_p_o = obj($r_p_id);
			$roles[] = html::get_change_url($r_p_o->id(), array(), $r_p_o->name()).": ".join(",", $r_p_data);
		}
		$roles = join("<br>", $roles);

		$roles .= ($roles != "" ? "<br>" : "" ).html::popup(array(
			"url" => $role_url,
			'caption' => t('Rollid'),
			"width" => 800,
			"height" => 600,
			"scrollbars" => "auto"
		));
		return $roles;
	}

	function _do_unit_listing_tree($arr)
	{
		$tree_inst = &$arr['prop']['vcl_inst'];
		$node_id = 0;
		$this->active_node = (int)$arr['request']['unit'];
		if(is_oid($arr['request']['cat']))
		{
			$this->active_node = $arr['request']['cat'];
		}
		$this->generate_tree(array(
			'tree_inst' => &$tree_inst,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_SECTION',
			'attrib' => 'unit',
			'leafs' => true,
		));
	}

	function customer_listing_tree($arr)
	{
		$tree_inst = &$arr['prop']['vcl_inst'];	
		$node_id = 0;
		$this->active_node = (int)$arr['request']['category'];
		$tree_inst->set_only_one_level_opened(1);

		$this->generate_tree(array(
			'tree_inst' => &$tree_inst,
			'obj_inst' => $arr['obj_inst'],
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_CATEGORY',
			'skip' => array(CL_CRM_COMPANY),
			'attrib' => 'category',
			'leafs' => false,
			'style' => 'nodetextbuttonlike',
		));
	}
}
?>
