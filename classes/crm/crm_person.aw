<?php                  
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_person.aw,v 1.28 2004/05/20 11:22:44 duke Exp $
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_COMPANY, on_connect_org_to_person)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_COMPANY, on_disconnect_org_from_person)

@classinfo relationmgr=yes
@tableinfo kliendibaas_isik index=oid master_table=objects master_index=oid

@default table=objects
@default group=general2

@property navtoolbar type=toolbar store=no no_caption=1 group=general,overview editonly=1

@property name type=text
@caption Nimi

@default table=kliendibaas_isik

@property firstname type=textbox size=15 maxlength=50
@caption Eesnimi

@property lastname type=textbox size=15 maxlength=50
@caption Perekonnanimi

@property personal_id type=textbox size=13 maxlength=11
@caption Isikukood

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

@property social_status type=chooser 
@caption Perekonnaseis

@property spouse type=textbox size=25 maxlength=50 group=relatives
@caption Abikaasa

@property children type=relpicker reltype=RELTYPE_CHILDREN group=relatives
@caption Lapsed

property pictureurl type=textbox size=40 maxlength=200
caption Pildi/foto url

@property picture type=releditor reltype=RELTYPE_PICTURE rel_id=first use_form=emb 
@caption Pilt/foto

@property notes type=textarea cols=60 rows=10 group=description
@caption Vabas vormis tekst

@property aliasmgr type=aliasmgr group=description no_caption=1 store=no
@caption Seostehaldur

@property work_contact type=relpicker reltype=RELTYPE_WORK table=kliendibaas_isik group=contact
@caption Organisatsioon

@property rank type=relpicker reltype=RELTYPE_RANK table=kliendibaas_isik automatic=1 group=contact
@caption Ametinimetus

@property sect type=relpicker reltype=RELTYPE_SECTION table=kliendibaas_isik automatic=1 group=general field=section
@caption Üksus

property personal_contact type=relpicker reltype=RELTYPE_ADDRESS table=kliendibaas_isik
caption Kodused kontaktandmed

@default group=contact
	
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

@reltype PROFILE value=14 clid=CL_PROFIIL
@caption Profiil

reltype USER_DATA value=15 
caption Andmed

@reltype CV value=19 clid=CL_CV
@caption CV

@reltype ORDER value=20 clid=CL_SHOP_ORDER
@caption tellimus

@reltype SECTION value=21 clid=CL_CRM_SECTION
@caption Üksus


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
		$data = &$arr["prop"];
		$retval = PROP_OK;
		$form = &$arr["request"];
		
		switch($data["name"])
		{
			case "lastname":
				if ($form['firstname'] || $form['lastname'])
				{
					//$title = $form['title'] ? $form['title'].' ' : '';
					$arr["obj_inst"]->set_name($form['firstname']." ".$form['lastname']);
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
			case "title":
				$data["options"] = array("Härra","Proua","Preili");
				break;

			case "social_status":
				$data["options"] = array("Vallaline","Abielus","Vabaabielus");
				break;

			case "templates":
				$data["options"] = array(
					"1" => "Pilt, kontakt, artiklid",
					"2" => "kontakt",
				);
				break;

			case "forms":
				$data["multiple"] = 1;
				break;

			case "navtoolbar":
				$this->isik_toolbar($arr);
				break;

			case "gender":
				$data["options"] = array(
					"1" => "mees",
					"2" => "naine",
				);
				break;

			case "email":
				/*
				$personal_contact = $arr["obj_inst"]->prop("personal_contact");
				if ($personal_contact)
				{
					$pc = new object($personal_contact);
					$addr = new object($pc->prop("primary_mail"));
					$data["value"] = $addr->prop("mail");
				};
				*/
				break;
				

			case "org_actions":
                        case "org_calls":
                        case "org_meetings":
                        case "org_tasks":
                                $this->do_org_actions(&$arr);
                                break;

			case "picture":
				//$data["rel_id"] = "52960";
				break;
		}
		return $retval;

	}

	function isik_toolbar(&$args)
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
			array('caption' => 'Organisatsioon','class' => 'crm_company', 'reltype' => RELTYPE_WORK),
		);
		
		$toolbar->add_menu_button(array(
			"name" => "add_relation",
			"tooltip" => "Uus",
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
						"title" => "Kalender määramata",
						'text' => 'Lisa '.$val['caption'],
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
						'text' => 'Lisa '.$val['caption'],
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
			"tooltip" => "Uus",
		));

		$menudata = '';
		if (is_array($action))
		{
			foreach($action as $key => $val)
			{
				if (!$parents[$val['reltype']])
				{
					$toolbar->add_menu_item(array(
						"parent" => "add_event",
						'title' => 'Kalender määramata',
						'text' => 'Lisa '.$this->cfg["classes"][$val["clid"]]["name"],
						'disabled' => true,
					));
				}
				else
				{
					$toolbar->add_menu_item(array(
						"parent" => "add_event",
						'url' => $this->mk_my_orb('new',array(
							'alias_to_org' => $args["obj_inst"]->id(),
							'reltype_org' => $val['reltype'],
							'class' => 'planner',
							'id' => $cal_id,
							'clid' => $val["clid"],
							'group' => 'add_event',
							'action' => 'change',
							'title' => $this->cfg["classes"][$val["clid"]]["name"].': '.$args['obj_inst']->name(),
							'parent' => $parents[$val['reltype']],///?
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						)),
						'text' => 'Lisa '.$this->cfg["classes"][$val["clid"]]["name"],
					));
				}
			};

			if (!empty($cal_id))
			{
				$toolbar->add_button(array(
					"name" => "user_calendar",
					"tooltip" => "Kasutaja kalender",
					"url" => $this->mk_my_orb('change', array('id' => $cal_id,'return_url' => urlencode(aw_global_get('REQUEST_URI')),),'planner'),
					"onClick" => "",
					"img" => "icon_cal_today.gif",
					"class" => "menuButton",
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

		$phones = $emails = $urls = $ranks = array();

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
                        "type" => 7,
                ));
		foreach($conns as $conn)
		{
			$ranks[] = $conn->prop("to.name");
		};

		$rv = array(
			"phone" => join(",",$phones),
			"url" => join(",",$urls),
			"email" => join(",",$emails),
			"rank" => join(",",$ranks),
			"add_task_url" => $this->mk_my_orb("change",array(
				"id" => $cal_id,
				"group" => "add_event",
				"alias_to_org" => $o->id(),
				"reltype_org" => 10,
				"clid" => CL_TASK,
			),CL_PLANNER),
			"add_call_url" => $this->mk_my_orb("change",array(
				"id" => $cal_id,
				"group" => "add_event",
				"alias_to_org" => $o->id(),
				"reltype_org" => 9,
				"clid" => CL_CRM_CALL,
			),CL_PLANNER),
			"add_meeting_url" => $this->mk_my_orb("change",array(
				"id" => $cal_id,
				"group" => "add_event",
				"alias_to_org" => $o->id(),
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
					continue;

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

		$classes = $this->cfg["classes"];

		$return_url = urlencode(aw_global_get("REQUEST_URI"));
		$planner = get_instance(CL_PLANNER);
		classload("icons");
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
                                "reltype" => 6,
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


}
?>
