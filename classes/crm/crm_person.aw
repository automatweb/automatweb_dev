<?php                  
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_person.aw,v 1.2 2003/11/20 21:21:49 duke Exp $
/*
@classinfo relationmgr=yes
@tableinfo kliendibaas_isik index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@property navtoolbar type=toolbar store=no no_caption=1 group=general,overview editonly=1

@property name type=text
@caption Nimi

@default table=kliendibaas_isik

@property firstname type=textbox size=15 maxlength=50
@caption Eesnimi

@property lastname type=textbox size=15 maxlength=50
@caption Perekonnanimi

@property title type=textbox size=5 maxlength=10
@caption Tiitel

@property gender type=textbox size=5 maxlength=10
@caption Sugu

@property personal_id type=textbox size=13 maxlength=11
@caption Isikukood

@property nickname type=textbox size=10 maxlength=20
@caption Hüüdnimi

@property messenger type=textbox size=30 maxlength=200
@caption Msn/yahoo/aol/icq

@property birthday type=textbox size=10 maxlength=20
@caption Sünnipäev

@property social_status type=textbox size=20 maxlength=20
@caption Perekonnaseis

@property spouse type=textbox size=25 maxlength=50
@caption Abikaasa

@property children type=relpicker reltype=RELTYPE_CHILDREN
@caption Lapsed

//	@property digitalID type=textbox size=20 maxlength=300
//	@caption Digitaalallkiri(fail vms)?

@property pictureurl type=textbox size=40 maxlength=200
@caption Pildi/foto url

@property picture type=relpicker reltype=RELTYPE_PICTURE
@caption Pilt/foto

@property work_contact type=relpicker reltype=RELTYPE_WORK table=kliendibaas_isik
@caption Organisatsioon

@property rank type=relpicker reltype=RELTYPE_RANK table=kliendibaas_isik
@caption Ametinimetus

@property personal_contact type=relpicker reltype=RELTYPE_ADDRESS table=kliendibaas_isik
@caption Kodused kontaktandmed

@property comment type=textarea cols=40 rows=3 table=objects field=comment
@caption Kommentaar

@default group=overview
@groupinfo overview caption="Seotud tegevused"

@property progress type=text callback=callback_org_actions store=no no_caption=1
@caption org_actions


@default group=forms
@default field=meta
@default table=objects
@default method=serialize
@groupinfo forms caption=Väljundid

@property forms type=relpicker reltype=RELTYPE_BACKFORMS
@caption tagasiside vormid
selection.aw

@property templates type=select
@caption templiidid

@default group=show
@groupinfo show caption=Visiitkaart submit=no
@property dokus type=text callback=show_isik

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

@reltype ISIK_KOHTUMINE value=8 clid=CL_CRM_MEETING
@caption kohtumine

@reltype ISIK_KONE value=9 clid=CL_CRM_CALL
@caption kõne


*/

class crm_person extends class_base
{
	function crm_person()
	{
		$this->init(array(
			"tpldir" => "isik",
			"clid" => CL_CRM_PERSON,
		));
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		$form = &$arr["request"];
		$obj = &$arr["obj"];
		
		switch($data["name"])
		{
			case 'lastname':
				if ($form['firstname'] || $form['lastname'])
				{
					$title = $form['title'] ? $form['title'].' ' : '';
					$arr["obj_inst"]->set_name($title.$form['firstname']." ".$form['lastname']);
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
			case 'templates':
				$tpls = $this->get_directory(array('dir' => $this->cfg['tpldir'].'/isik/visit/'));
				$data['options'] = $tpls;
				break;

			case 'name':
				$data['value'] = $arr['obj']['name'];
				break;

			case 'forms':
				$data['multiple'] = 1;
				break;

			case 'navtoolbar':
				// kust see global tuleb?
				$this->isik_toolbar($arr);
				break;
			break;
		}
		return $retval;

	}

	function callback_org_actions($args)
	{
		$inst = get_instance('crm/crm_company');
		return $inst->callback_org_actions($args);
	}

	
	function isik_toolbar(&$args)
	{
		$toolbar = &$args["prop"]["toolbar"];

		$this->read_template('../firma/js_popup_menu.tpl');

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
			$parents[RELTYPE_ISIK_KONE] = $parents[RELTYPE_ISIK_KOHTUMINE] = $user_calendar->prop('event_folder');
		}

		$alist = array(
			array('caption' => 'Organisatsioon','class' => 'crm_company', 'reltype' => RELTYPE_WORK),
		);

		$menudata = '';
		if (is_array($alist))
		{
			foreach($alist as $key => $val)
			{
				if (!$parents[$val['reltype']])
				{
					$this->vars(array(
						'alt' => 'Kalender määramata',
						'text' => 'Lisa '.$val['caption'],
					));
					$menudata .= $this->parse("MENU_ITEM_DISABLED");
				}
				else
				{
					// see on nyyd sihuke link, mis lisab uue objekti
					// ja seostab selle olemasolevaga. grr.
					$this->vars(array(
						'link' => $this->mk_my_orb('new',array(
							'alias_to' => $args['obj_inst']->id(),
							'reltype' => $val['reltype'],
							'class' => $val['class'],
							'parent' => $parents[$val['reltype']],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						)),
						'text' => 'Lisa '.$val['caption'],
					));
					$menudata .= $this->parse("MENU_ITEM");	
				}
			};
			
			$this->vars(array(
				"MENU_ITEM" => $menudata,
				"id" => "add_relation",
			));
			$addbutton = $this->parse();
			$toolbar->add_cdata($addbutton);
			$toolbar->add_button(array(
				"name" => "add_item_button",
				"tooltip" => "Uus",
				"url" => "",
				"onClick" => "return buttonClick(event, 'add_relation');",
				"img" => "new.gif",
				"class" => "menuButton",
			));
			
		};
		
		

		$action = array(
			array(
				"reltype" => RELTYPE_ISIK_KOHTUMINE,
				"clid" => CL_CRM_MEETING,
			),
			array(
				"reltype" => RELTYPE_ISIK_KONE,
				"clid" => CL_CRM_CALL,
			),
		);

		$menudata = '';
		if (is_array($action))
		{
			foreach($action as $key => $val)
			{
				if (!$parents[$val['reltype']])
				{
					$this->vars(array(
						'title' => 'Kalender määramata',
						'text' => 'Lisa '.$this->cfg["classes"][$val["clid"]]["name"],
					));
					$menudata .= $this->parse("MENU_ITEM_DISABLED");
				}
				else
				{
					$this->vars(array(
						'link' => $this->mk_my_orb('new',array(
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
					$menudata .= $this->parse("MENU_ITEM");
				}
			};

			$this->vars(array(
				"MENU_ITEM" => $menudata,
				"id" => "add_event",
			));
			$eventbutton = $this->parse();
			$toolbar->add_cdata($eventbutton);
			$toolbar->add_button(array(
				"name" => "add_event_button",
				"tooltip" => "Uus",
				"url" => "",
				"onClick" => "return buttonClick(event, 'add_event');",
				"img" => "new.gif",
				"class" => "menuButton",
			));

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

		$arg2['id'] = $args['obj'][OID];
		$nodes = array();
		$nodes['visitka'] = array(
			"value" => $this->show($arg2),
		);
		return $nodes;
	}


	function show($args)
	{
		extract($args);

		$obj = $this->get_object($id);

		if (strlen($obj['meta']['templates']) > 4)
		{
			$this->read_template('visit/'.$obj['meta']['templates']);
		}
		else
		{
			$this->read_template('visit/visiit1.tpl');
		}

		$row = $this->fetch_all_data($id);

		$forms = '';
		if (is_array($this->deafult_forms))
		{
			$obj['meta']['forms'] = array_merge($this->deafult_forms, $obj['meta']['forms']);
		}


		if (is_array($obj['meta']['forms']))
		{
			$obj['meta']['forms'] = array_unique($obj['meta']['forms']);
			foreach($obj['meta']['forms'] as $val)
//			$val = $obj['meta']['forms'];
			{
				if (!$val)
				continue;

				$form = $this->get_object($val);
				$forms.= html::href(array(
				'target' => $form['meta']['open_in_window']? '_blank' : NULL,
				'caption' => $form['name'], 'url' => $this->mk_my_orb('form', array(
					'id' => $form[OID],
					'feedback' => $id,
					'feedback_cl' => rawurlencode('crm/crm_person'),
					),'pilot_object'))).'<br />';
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
		$row['tagasisidevormid'] = $forms;

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

	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

}
?>
