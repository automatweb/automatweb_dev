<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_company.aw,v 1.48 2004/07/06 06:36:00 rtoomas Exp $
/*
//on_connect_person_to_org handles the connection from person to section too
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_PERSON, on_connect_person_to_org)
//on_disconnect_person_from_org handles the connection from person to section too
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_PERSON, on_disconnect_person_from_org)
HANDLE_MESSAGE_WITH_PARAM(MSG_EVENT_ADD, CL_CRM_PERSON, on_add_event_to_person)

@classinfo relationmgr=yes
@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@property navtoolbar type=toolbar store=no no_caption=1 group=general,all_actions,meetings,tasks,calls editonly=1

@property name type=textbox size=30 maxlenght=255 table=objects
@caption Organisatsiooni nimi

@property comment type=textarea cols=65 rows=3 table=objects
@caption Kommentaar

@property reg_nr type=textbox size=10 maxlength=20 table=kliendibaas_firma
@caption Registri number

@property pohitegevus type=relpicker reltype=RELTYPE_TEGEVUSALAD table=kliendibaas_firma
@caption Põhitegevus

//@property ettevotlusvorm type=relpicker reltype=RELTYPE_ETTEVOTLUSVORM table=kliendibaas_firma 
//@caption Õiguslik vorm

@property ettevotlusvorm type=chooser table=kliendibaas_firma
@caption Õiguslik vorm

//@property ettevotlusvorm type=objpicker clid=CL_CRM_CORPFORM table=kliendibaas_firma 
//@caption Õiguslik vorm

@property tooted type=relpicker reltype=RELTYPE_TOOTED method=serialize field=meta table=objects
@caption Tooted

@property kaubamargid type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Kaubamärgid

@property tegevuse_kirjeldus type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Tegevuse kirjeldus

@property logo type=textbox size=40 method=serialize field=meta table=objects
@caption Organisatsiooni logo(url)

@property firmajuht type=chooser orient=vertical table=kliendibaas_firma  editonly=1
@caption Kontaktisik

@default group=oldcontacts

@property addresslist type=text store=no no_caption=1 group=oldcontacts
@caption Aadress

@property old_human_resources type=table store=no no_caption=1 group=oldcontacts
@caption Nimekiri

@default group=contacts2


@layout hbox_toolbar type=hbox group=contacts2

@property contact_toolbar type=toolbar no_caption=1 store=no parent=hbox_toolbar
@caption "The Green Button"

@layout hbox_others type=hbox group=contacts2 width=15%:30%:55%

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

@property personal_offers_toolbar type=toolbar group=personal_offers store=no no_caption=1 parent=personal_toolbar
@property unit_listing_tree_personal type=treeview no_caption=1 store=no parent=personal_hbox_tree group=personal_offers,personal_candits
@property personal_offers_table type=table group=personal_offers no_caption=1 parent=personal_hbox_table
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

@property customer_search_submit type=submit size=15 store=no parent=vbox_customers_right
@caption Otsi

@property customer_search type=hidden store=no parent=vbox_customers_right value=1 no_caption=1
@caption Otsi

@property customer_search_results type=table store=no parent=vbox_customers_right no_caption=1
@caption Otsi tulemused 

////end of box////

@groupinfo contacts caption="Kontaktid"
@groupinfo oldcontacts caption="Nimekiri" parent=contacts submit=no
@groupinfo contacts2 caption="Kontaktid" parent=contacts submit=no
@groupinfo cedit caption="Kontaktide muutmine" parent=contacts
@groupinfo overview caption="Tegevused" 
@groupinfo all_actions caption="Kõik" parent=overview submit=no
@groupinfo calls caption="Kõned" parent=overview submit=no
@groupinfo meetings caption="Kohtumised" parent=overview submit=no
@groupinfo tasks caption="Toimetused" parent=overview submit=no
@groupinfo tasks_overview caption="Ülevaade" parent=overview

@groupinfo relorg caption="Kliendid"
@groupinfo customers caption="Kliendid" parent=relorg submit=no
@groupinfo my_customers caption="kliendid" parent=relorg submit=no
@groupinfo fcustomers caption="Tulevased kliendid" parent=relorg
@groupinfo partners caption="Partnerid" parent=relorg
@groupinfo fpartners caption="Tulevased partnerid" parent=relorg
@groupinfo competitors caption="Konkurendid" parent=relorg

@groupinfo personal caption="Värbamine"
@groupinfo personal_offers caption="Tööpakkumised" parent=personal submit=no
@groupinfo personal_candits caption="Kandideerijad" parent=personal submit=no


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
		//default to company relation values
		$this->reltype_section = $this->crm_company_reltype_section;
		$this->reltype_professions = $this->crm_company_reltype_professions;
		$this->reltype_workers = $this->crm_company_reltype_workers;
		$this->reltype_category = $this->crm_company_reltype_category;
		//
		$this->init(array(
			'clid' => CL_CRM_COMPANY,
			'tpldir' => 'firma',
		));
	}

	/*
		$tree -> the treeview object
		$obj -> the rootnode
		$type1 -> what type of connections are allowed
		$skip -> a type can have many "to" object types, if any of them
					should be skipped, then $skip does the trick
		$attrib -> the node link can have some extra attributes
		$leafs -> if leafs should be shown (not exactly what the description implies)
	*/
	function generate_tree($tree, $obj,$node_id,$type1,$skip, $attrib, $leafs)
	{
		//all connections from the currrent object
		//different reltypes
		$tmp_type = $type1;
		if($obj->prop('class_id')==CL_CRM_COMPANY)
		{
			if($type1=='RELTYPE_CATEGORY')
			{
				$type1 = $this->crm_company_reltype_category;
			}
			else
			{
				$type1 = $this->crm_company_reltype_section;
			}
		}
		else if($obj->prop('class_id')==CL_CRM_SECTION)
		{
			if($type1=='RELTYPE_CATEGORY')
			{
				$type1 = $this->crm_category_reltype_category;
			}
			else
			{
				$type1 = $this->crm_section_reltype_section;
			}
		}
	
		$conns = $obj->connections_from(array(
			'type'=>$type1,
			'sort_by' => 'from.jrk',
			'sort_dir' => 'asc',
		));
		$conns = $conns;
		//parent nodes'id actually
		$this_level_id = $node_id;
		foreach($conns as $key=>$conn)
		{
			//$skip in action
			if(in_array($conn->prop('type'),$skip))
				continue;
			//iga alam item saab ühe võrra suurema väärtuse
			//if the 'to.id' eq active_node then it should be bold
			if($conn->prop('to')==$this->active_node)
			{
				$name='<b>'.$conn->prop('to.name').'</b>';
			}
			else
			{
				$name=$conn->prop('to.name');
			}
			$tmp_obj = $conn->to();
			
			//use the plural unless plural is empty -- this is just for reltype_section
			$tree_node_info = array(
				'id'=>++$node_id,
				'name'=>$name,
				'url'=>aw_url_change_var(array(
							$attrib=>$conn->prop('to'),
							'cat'=>''
				))
			);
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

			//add another item to the tree
			$tree->add_item($this_level_id,$tree_node_info);
			$this->generate_tree(&$tree,&$tmp_obj,&$node_id,$tmp_type,&$skip, &$attrib, $leafs);
		}
		//if leafs
		if($leafs)
		{
			$this->tree_node_items(&$tree,&$obj,$this_level_id,&$node_id);
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
			$url = array();
			$url = aw_url_change_var(array('cat'=>$prof_conn->prop('to'),$key=>$value));
			$tree->add_item($this_level_id,
						array('id'=>++$node_id,
								'name'=>$name,
								'iconurl'=>'images/scl.gif',
								'url'=>$url
						));
		}	
	}
	
	
	function get_property($arr)
	{
		$data = &$arr['prop'];
		$retval = PROP_OK;
	
		//groupinfo wants user's name for name
		//damn, but this "if" will execute the number of propertys
		//under my_customers times. I'll think of smthing, better yet, i'll ask duke
		//just joking, will have a class variable 
		if($arr['request']['group']=='my_customers' && $this->group_not_shown)
		{
			$this->group_not_shown = false;
			$name = aw_global_get('uid').' kliendid';
			//tsekime kas kasutajal on isik objekt üldse tehtud ja seotud endaga
			//eeldan, et kasutaja existeerib :)
			$user = new object(users::get_oid_for_uid(aw_global_get('uid')));
			//getting the person
			$conns = $user->connections_from(array(
				'type' => 'RELTYPE_PERSON'
			));
			//kui on mitu, siis võtan esimese
			if(sizeof($conns))
			{
				$tmp = current($conns);
				$name = $tmp->prop('to.name').' kliendid';
			}
			$arr['groupinfo']['my_customers']['caption'] = $name;
		}
	
		switch($data['name'])
		{
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
			case 'customer_search_results':
				if($this->show_customer_search)
				{
					$this->do_customer_search_results(&$arr);
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
				$tree_inst = &$arr['prop']['vcl_inst'];
				$node_id = 0;
				$this->active_node = (int)$arr['request']['unit'];
				$this->generate_tree(&$tree_inst,$arr['obj_inst'],&$node_id,'RELTYPE_SECTION',array(),'unit',true);
				break;
			}
			
			case "unit_listing_tree_personal":
				$tree_inst = &$arr['prop']['vcl_inst'];
				$node_id = 0;
				$this->active_node = (int)$arr['request']['unit'];
				$this->generate_tree(&$tree_inst,$arr['obj_inst'],&$node_id,'RELTYPE_SECTION',array(),'unit',true);
			break;

			case "customer_listing_tree":
			{
				$tree_inst = &$arr['prop']['vcl_inst'];	
				$node_id = 0;
				$this->active_node = (int)$arr['request']['category'];
				$this->generate_tree(&$tree_inst,$arr['obj_inst'],&$node_id,
													'RELTYPE_CATEGORY',array(CL_CRM_COMPANY),'category',false);
				break;
			}
			case 'ettevotlusvorm':
			{
				$ol = new object_list(array(
					'class_id' => CL_CRM_CORPFORM,
					'sort_by' => 'objects.jrk, objects.name',
				));
				$elements = array();
				for($o=$ol->begin();!$ol->end();$o=$ol->next())
				{
					if($o->id()==$data['value'])
					{
						$arr['prop']['value'] = $o->id();
					}
					$elements[$o->id()] = $o->prop('shortname');
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

			case "org_toolbar":
				$vcl_inst = &$arr["prop"]["toolbar"];
				$vcl_inst->add_button(array(
					"name" => "delete",
					"img" => "delete.gif",
				));
				break;

			case "firmajuht":
				$conns = $arr["obj_inst"]->connections_from(array(
					"type" => RELTYPE_WORKERS,
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

			case "addresslist":
				$this->do_addresslist($arr);
			break;
			
			case "personal_offers_toolbar":
				$this->do_personal_offers_toolbar(&$data["toolbar"], &$arr);
			break;
			
			case "personal_offers_table":
				$this->personal_offers_table($arr);
			break;
			/*
			case "jobsact":
				$this->do_jobslist($arr);
				break;
			case "jobsnotact":
				$this->do_jobs_notact_list($arr);
				break;
			*/
		};
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr['prop'];
		if($data['name']=='contact_search_submit')
		{
			//arr($arr);
			//contact_search_lastname
			//contact_search_code
			//contact_search_sex
		}
	}

	function callback_pre_edit($arr)
	{
		// initialize
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));
	}
	
	function personal_offers_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
	
		$table->define_field(array(
			"name" => "osakond",
			"caption" => "Osakond",
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "ametinimi",
			"caption" => "Ametinimi",
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "kehtiv_alates",
			"caption" => "Kehtiv alates",
			"sortable" => "1",
			"width" => 80,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y",
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "kehtiv_kuni",
			"caption" => "Kehtiv kuni",
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
			"caption" => "X",
			"width" => 20,
			"align" => "center"
		));
		
		$section_cl = get_instance(CL_CRM_SECTION);	
		
		if(is_oid($arr['request']['unit']))
		{
			$jobs_ids = $section_cl->get_section_job_ids_recrusive($arr['request']['unit']);
		}
		else
		{
			$jobs_ids = $section_cl->get_all_org_job_ids($arr["obj_inst"]->id());
			$professions = $section_cl->get_all_org_proffessions($arr["obj_inst"]->id(), true);
			/*
			foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_SECTION)) as $conn)
			{	
				$professions_temp = $section_cl->get_professions($conn->prop("to"), true);		
				foreach ($professions_temp as $key=>$value)
				{
					$professions[$key] = $value;
				}
			}*/
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
		
		foreach ($job_obj_list->arr() as $job)
		{
			if($arr['request']['unit'])
			{
				$professions = $section_cl->get_professions($arr['request']['unit'], true);
			}
			
			if(!$professions[$job->prop("profession")])
			{
				$professin_cap = "Määramata";
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
			));				
		}
		
	}
	
	/*function do_jobs_notact_list($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => "Ametikoht",
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "deadline",
			"caption" => "Tähtaeg",
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "kandideerijad",
			"caption" => "Kandidaadid",
			"sortable" => "1",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"caption" => "X",
		));
		
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_JOBS)) as $job)
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
						"caption" => "Vaata kandidaate",
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
			"caption" => "Ametikoht",
			"sortable" => "1",
			"width" => "200",
		));
		
		$table->define_field(array(
			"name" => "deadline",
			"caption" => "Tähtaeg",
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "kandideerijad",
			"caption" => "Kandidaadid",
			"sortable" => "1",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"caption" => "X",
		));
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_JOBS)) as $job)
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
						"caption" => "Vaata kandidaate",
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

	function do_human_resources($arr,$old_iface=false)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
                        'name' => 'name',
                        'caption' => 'Nimi',
                        'sortable' => '1',
			'callback' => array(&$this, 'callb_human_name'),
			'callb_pass_row' => true,
                ));
		$t->define_field(array(
                        'name' => 'phone',
                        'caption' => 'Telefon',
                        'sortable' => '1',
                ));
		$t->define_field(array(
                        'name' => 'email',
                        'caption' => 'E-post',
                        'sortable' => '1',
                ));
		$t->define_field(array(
								'name' => 'section',
								'caption' => 'Üksus',
								'sortable' => '1',
					));
		$t->define_field(array(
                        'name' => 'rank',
                        'caption' => 'Ametinimetus',
                        'sortable' => '1',
                ));
		/*$t->define_field(array(
                        'name' => 'lastaction',
                        'caption' => 'Viimane tegevus',
                        'sortable' => '1',
                ));*/
					 
		if($old_iface)
		{		
			$t->define_field(array(
				'name' => 'new_call',
				'align' => 'center',
			));
			$t->define_field(array(
				'name' => 'new_meeting',
				'align' => 'center',
			));
			$t->define_field(array(
				'name' => 'new_task',
				'align' => 'center',
			));
		}
		
		if(!$old_iface)
		{
			$t->define_chooser(array(
				'name'=>'check',
				'field'=>'id',
			));
		}
	

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
			$conns = $arr["obj_inst"]->connections_from(array(
				"type" => $this->crm_company_reltype_workers,
			));

			//if listing from a specific unit, then the reltype is different
			if((int)$arr['request']['unit'])
			{
				$obj = new object((int)$arr['request']['unit']);
				$conns = $obj->connections_from(array(
					'type' => $this->crm_section_reltype_workers,
				));
			}

			foreach($conns as $conn)
			{
				$persons[] = $conn->prop('to');
			}
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
				$crm_section = get_instance('crm/crm_section');
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
			);
			if($old_iface)
			{
				if ($cal_id)
				{
					$tdata["new_task"] = html::href(array(
						"caption" => "Uus toimetus",
						"url" => $pdat["add_task_url"],
					));
					$tdata["new_call"] = html::href(array(
						"caption" => "Uus kõne",
						"url" => $pdat["add_call_url"],
					));
					$tdata["new_meeting"] = html::href(array(
						"caption" => "Uus kohtumine",
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
				'type' => RELTYPE_WORKERS
			));
			foreach($conns as $conn)
			{
				$data[$conn->prop('to')] = $conn->prop('to');
			}
		}
		//getting all the sections
		$conns = $obj->connections_from(array(
			'type' => RELTYPE_SECTION,
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
			"type" => RELTYPE_CALL,
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
			"type" => RELTYPE_ADDRESS,
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
				$args["type"] = RELTYPE_CALL;
				break;
			
			case "org_meetings":
				$args["type"] = RELTYPE_KOHTUMINE;
				break;
			
			case "org_tasks":
				$args["type"] = RELTYPE_TASK;
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

		$classes = $this->cfg["classes"];

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

		$this->overview = array();
		classload("icons");

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
			$target_obj->connect(array(
				"to" => $conn->prop("from"),
				"reltype" => $this->crm_company_reltype_workers,
			));
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
			$target_obj->disconnect(array(
				"from" => $conn->prop("from"),
			));
		}
		else if($target_obj->class_id() == CL_CRM_SECTION)
		{
			//$target_obj->disconnect(array(
			//	"from" => $conn->prop("from"),
			//));
		}
	}
		
	/**
		@attrib name=delete_selected_jobs
	**/
	function delete_selected_jobs($arr)
	{
		foreach ($arr["select"] as $deleted_obj_id)
		{
			$deleted_obj = &obj($deleted_obj_id);
			$deleted_obj->delete();	
		}
		return $arr["return_url"];
	}
	
	/**
		@attrib name=submit_new_task
		@param id required type=int acl=view
	**/
	function submit_new_task($arr)
	{
		$arr['clid'] = CL_TASK;
		$this->submit_new_action_to_person(&$arr);
	}

	/**
		@attrib name=search_for_contacts
	**/
	function search_for_contacts($arr)
	{
		return $this->mk_my_orb(
					'change',array(
						'id' => $arr['id'],
						'group' => $arr['group'],
						'contact_search' => true,
						'unit' => $arr['unit']
						),
					'crm_company');
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
						'unit' => $arr['unit']
						),
					'crm_company');
	}

	/**
		@attrib name=submit_new_call
		@param id required type=int acl=view
	**/
	function submit_new_call($arr)
	{
		$arr['clid'] = CL_CRM_CALL;
		$this->submit_new_action_to_person(&$arr);
	}
	
	/**
		@attrib name=submit_new_meeting
		@param id required type=int acl=view
	**/
	function submit_new_meeting($arr)
	{
		$arr['clid'] = CL_CRM_MEETING;
		$this->submit_new_action_to_person(&$arr);
	}

	function submit_new_action_to_person($arr)
	{
		if(!is_array($arr['check']))
			return;
		$prsn = get_instance(CL_CRM_PERSON);
		$pl = get_instance(CL_PLANNER);
		$cal_id = $pl->get_calendar_for_user(array('uid'=>aw_global_get('uid')));
		$alias_to_org_arr = array();
		$fake_alias = 0;
		reset($arr['check']);
		$fake_alias = current($arr['check']);

		//why need person? cos i want to add the same event to the user too
		//$person = get_instance('crm/crm_person');
		//$person = $person->get_person_by_user_id(users_user::get_oid_for_uid(aw_global_get('uid')));
		$url = $this->mk_my_orb('change',array(
				'id'=>$cal_id,
				'group'=>'add_event',
				'alias_to_org'=>$fake_alias,
				'reltype_org'=>9, //PERSON_CALL
				'clid'=>$arr['clid'],
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
			$parents[RELTYPE_JOBS] = $parents[RELTYPE_WORKERS] = $parents[RELTYPE_ADDRESS] = $args['obj_inst']->parent();
		}
		else
		{
			$crm_db = new object($crm_db_id);
			$default_dir = $crm_db->prop("dir_default");
			$parents[RELTYPE_ADDRESS] = $crm_db->prop("dir_address") == "" ? $default_dir : $crm_db->prop('dir_address');
			$parents[RELTYPE_WORKERS] = $crm_db->prop("dir_isik") == "" ? $default_dir : $crm_db->prop('dir_isik');
		};

		if (!empty($this->cal_id))
		{
			$user_calendar = new object($this->cal_id);
			$parents[RELTYPE_CALL] = $parents[RELTYPE_OFFER] = $parents[RELTYPE_KOHTUMINE] = $parents[RELTYPE_DEAL] = $parents[RELTYPE_TASK] = $user_calendar->prop('event_folder');
		}

		$toolbar->add_menu_button(array(
			"name" => "main_menu",
			"tooltip" => "Uus",
		));

		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "calendar_sub",
			"text" => $this->cfg["classes"][CL_PLANNER]["name"],
		));
		
		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "firma_sub",
			"text" => $this->cfg["classes"][$this->clid]["name"],
		));

		$alist = array(RELTYPE_WORKERS,RELTYPE_ADDRESS,RELTYPE_JOBS);
		foreach($alist as $key => $val)
		{
			$clids = $this->relinfo[$val]["clid"];
			if (is_array($clids))
			{
				foreach($clids as $clid)
				{
					$classinf = $this->cfg["classes"][$clid];

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
						"text" => 'Lisa '.$classinf["name"],
						"link" => $has_parent ? $url : "",
						"title" => $has_parent ? "" : "Kataloog määramata",
						"disabled" => $has_parent ? false : true,
					));
				};
			};
		};

		// aha, I need to figure out which objects can be added to that relation type

		// basically, I need to create a list of relation types that are of any
		// interest to me and then get a list of all classes for those

		$action = array(RELTYPE_OFFER,RELTYPE_DEAL,RELTYPE_KOHTUMINE,RELTYPE_CALL,RELTYPE_TASK);

		foreach($action as $key => $val)
		{
			$clids = $this->relinfo[$val]["clid"];
			$reltype = $this->relinfo[$val]["value"];
			if (is_array($clids))
			{
				foreach($clids as $clid)
				{
					$classinf = $this->cfg["classes"][$clid];
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
						"title" => $has_parent ? "" : "Kalender või kalendri sündmuste kataloog määramata",
						"text" => "Lisa ".$classinf["name"],
						"disabled" => $has_parent ? false : true,
						"link" => $has_parent ? $url : "",
					));
				};
			};
		};
			
		if (!empty($this->cal_id))	
		{
			$toolbar->add_button(array(
				"name" => "user_calendar",
				"tooltip" => "Kasutaja kalender",
				"url" => $this->mk_my_orb('change', array('id' => $this->cal_id,'return_url' => urlencode(aw_global_get('REQUEST_URI')),),'planner'),
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

	function org_table(&$arr)
	{
		$tf = &$arr["prop"]["vcl_inst"];
		$tf->define_field(array(
                        "name" => "name",
                        "caption" => "Organisatsioon",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "pohitegevus",
                        "caption" => "Põhitegevus",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "corpform",
                        "caption" => "Õiguslik vorm",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "address",
                        "caption" => "Aadress",
                        "sortable" => 1,
                ));
	
                $tf->define_field(array(
                        "name" => "email",
                        "caption" => "E-post",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "url",
                        "caption" => "WWW",
                        "sortable" => 1,
                ));
                $tf->define_field(array(
                        "name" => "phone",
                        "caption" => 'Telefon',
                ));

                $tf->define_field(array(
                        "name" => "ceo",
                        "caption" => "Juht",
                        "sortable" => 1,
                ));

		$tf->define_chooser(array(
                        "field" => "id",
                        "name" => "check",
                ));

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

		foreach($orgs as $org)
		{
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
		
		foreach($arr['check'] as $key=>$value)
		{
			$main_obj->disconnect(array('from'=>$value));
		}

		return $this->mk_my_orb('change',array(
			'id' => $arr['id'],
			'group'=>'contacts',
			'unit'=>$arr['unit'],),
			CL_CRM_COMPANY
		);
	}


	/**
		deletes the relations category -> organization || organization -> category
		@attrib name=submit_delete_customer_relations
		@param id required type=int acl=view
		@param customer optional type=int
	**/
	function submit_delete_customer_relations($arr)
	{
		$main_obj = new Object($arr['id']);
		
		if((int)$arr['category'])
		{
			$main_obj = new Object($arr['category']);
		}
		foreach($arr['check'] as $key=>$value)
		{
			$main_obj->disconnect(array('from'=>$value));
		}

		return $this->mk_my_orb('change',array(
			'id' => $arr['id'],
			'group'=>'relorg',
			'category'=>$arr['category']),
			CL_CRM_COMPANY
		);
			
	}

	/*
	
	*/
	function callback_on_load($arr)
	{
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
		$arr['return_url'] = aw_global_get('REQUEST_URI');
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
			$arr['args']['customer_search'] = $this->show_customer_search;
		}
	
		if($arr['request']['unit'])
		{
			$arr['args']['unit'] = $arr['request']['unit'];
		}

		if($arr['request']['category'])
		{
			$arr['args']['category'] = $arr['request']['category'];
		}
	}

	function do_contact_toolbar($tb,$arr)
	{
		$tb->add_menu_button(array(
				'name'=>'add_item',
				'tooltip'=>'Uus'
		));
		
		$alias_to = $arr['obj_inst']->id();
		
		if((int)$arr['request']['unit'])
		{
			$alias_to = $arr['request']['unit'];
		}

		$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text'=>'Töötaja',
				'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,//$arr['obj_inst']->id(),
					'reltype'=>$this->reltype_workers,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI'))
				),'crm_person')
				
		));
		
		$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text'=>'Üksus',
				'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=> $this->reltype_section,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI'))
					),
					'crm_section')
		));
		
		$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text'=>'Ametinimetus',
				'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=> $this->reltype_professions,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI'))
					),
					'crm_profession')
		));
	
		//delete button
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => 'Kustuta valitud',
			'action' => 'submit_delete_relations',
		));
	
		//uus kõne
		$tb->add_button(array(
			'name' => 'Kõne',
			'img' => 'class_223.gif',
			'tooltip' => 'Tee kõne',
			'action' => 'submit_new_call'
		));

		//uus date
		$tb->add_button(array(
			'name' => 'Kohtumine',
			'img' => 'class_224.gif',
			'tooltip' => 'Uus kohtumine',
			'action' => 'submit_new_meeting'
		));

		//uus task
		$tb->add_button(array(
			'name' => 'Toimetus',
			'img' => 'class_244.gif',
			'tooltip' => 'Uus toimetus',
			'action' => 'submit_new_task'
		));

		$tb->add_separator();

		$tb->add_button(array(
			'name' => 'Search',
			'img' => 'search.gif',
			'tooltip' => 'Otsi',
			'action' => 'search_for_contacts'
		));

		if($arr['request']['contact_search'])
		{
			$tb->add_button(array(
				'name' => 'Save',
				'img' => 'save.gif',
				'tooltip' => 'Salvesta',
				'action' => 'save_search_results'
			));
		}
	}

	function do_customer_toolbar($tb, $arr)
	{
	
		$tb->add_menu_button(array(
				'name'=>'add_item',
				'tooltip'=>'Uus'
		));

		$alias_to = $arr['obj_inst']->id();
		$rel_type = RELTYPE_CATEGORY;
		
		if((int)$arr['request']['category'])
		{
			$alias_to = $arr['request']['category'];
			$parent = (int)$arr['request']['category'];
			$rel_type = RELTYPE_CATEGORY;
		}

		$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text'=>'Kategooria',
				'link'=>$this->mk_my_orb('new',array(
					'parent'=>$arr['obj_inst']->id(),
					'alias_to'=>$alias_to,
					'reltype'=>$this->reltype_category,
					'return_url'=>urlencode(aw_global_get('REQUEST_URI'))
				),'crm_category')
				
		));
		//delete button
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => 'Kustuta valitud',
			'action' => 'submit_delete_customer_relations',
		));

		$tb->add_separator();

		$tb->add_button(array(
			'name' => 'Search',
			'img' => 'search.gif',
			'tooltip' => 'Otsi',
			'action' => 'search_for_customers'
		));
		
		if($arr['request']['customer_search'])
		{
			$tb->add_button(array(
				'name' => 'Save',
				'img' => 'save.gif',
				'tooltip' => 'Salvesta',
				'action' => 'save_customer_search_results'
			));
		}
	}
	
	function do_personal_offers_toolbar($toolbar, $arr)
	{
		$toolbar->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=>'Uus'
		));
		
		$toolbar->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => 'Kustuta valitud tööpakkumised',
			'action' => 'delete_selected_jobs',
			'confirm' => "Kas oled kindel et soovid valitud tööpakkumised kustudada?"
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
				'text'=>'Tööpakkumine',
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
	
	function do_customer_search_results($arr)
	{
		//i'll try the search from crm_org_search.aw
		$searchable_fields = array('customer_search_name' => 'name',
											'customer_search_reg' => 'reg_nr',
											'customer_search_address'=> 'address',
											'customer_search_city' => 'linn',
											'customer_search_county' => 'maakond',
		//									'customer_search_field' => 'L_CRM_COMPANY.RELTYPE_TEGEVUSALAD.name',
											'customer_search_leader' => 'firmajuht');

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
		
		$crm_org_search = get_instance('crm/crm_org_search');
		$crm_org_search->do_search(&$arr,$search_params);
	}

	function do_contacts_search_results($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
                        'name' => 'name',
                        'caption' => 'Nimi',
                        'sortable' => '1',
			'callback' => array(&$this, 'callb_human_name'),
			'callb_pass_row' => true,
                ));
		$t->define_field(array(
                        'name' => 'phone',
                        'caption' => 'Telefon',
                        'sortable' => '1',
                ));
		$t->define_field(array(
                        'name' => 'email',
                        'caption' => 'E-post',
                        'sortable' => '1',
                ));
		$t->define_field(array(
								'name' => 'section',
								'caption' => 'Üksus',
								'sortable' => '1',
					));
		$t->define_field(array(
                        'name' => 'rank',
                        'caption' => 'Ametinimetus',
                        'sortable' => '1',
                ));
		$t->define_chooser(array(
			'name'=>'check',
			'field'=>'id',
		));

		$search_params = array('class_id'=>CL_CRM_PERSON,'limit'=>50,'sort_by'=>'name');

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

		/*$ol = new object_list(array(
					'limit' => 50,
					'sort_by' => 'name',
					'class_id' => CL_CRM_PERSON,
					'CL_CRM_PERSON.RELTYPE_PHONE.name' => '%'
				));
		arr($ol);
		die();*/

		$ol = new object_list($search_params);
		//$ol = new object_list(array('class_id' => CL_CRM_PERSON,'firstname'=>'toomas','lastname'=>'koobas'));
		$pl = get_instance(CL_PLANNER);
		$person = get_instance("crm/crm_person");
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
							),$arr['class']);
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
		}
		return $this->mk_my_orb('change',array(
								'id' => $arr['id'],
								'unit' => $arr['unit'],
								'group' => $arr['group'],
							),$arr['class']);
	}
}
?>
