<?php
/*
@classinfo relationmgr=yes
@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@property navtoolbar type=toolbar store=no no_caption=1 group=general,overview

@property name type=textbox size=30 maxlenght=255 table=objects
@caption Organisatsiooni nimi

@property comment type=textarea cols=65 rows=3 table=objects
@caption Kommentaar

@property reg_nr type=textbox size=10 maxlenght=20 table=kliendibaas_firma
@caption Registri number

@property pohitegevus type=relpicker reltype=TEGEVUSALAD table=kliendibaas_firma
@caption Põhitegevus

@property ettevotlusvorm type=relpicker reltype=ETTEVOTLUSVORM table=kliendibaas_firma
@caption Õiguslik vorm

@property tooted type=relpicker reltype=TOOTED method=serialize field=meta table=objects
@caption Tooted

@property kaubamargid type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Kaubamärgid

@property contact type=relpicker reltype=ADDRESS table=kliendibaas_firma
@caption Aadress

@property tegevuse_kirjeldus type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Tegevuse kirjeldus

@property logo type=textbox size=40 method=serialize field=meta table=objects
@caption Organisatsiooni logo(url)

//@property firmajuht type=relpicker reltype=WORKERS table=kliendibaas_firma
@property firmajuht type=text table=kliendibaas_firma store=
@caption Organisatsiooni juht

@default group=overview
@groupinfo overview caption="Seotud tegevused"

@property progress type=text callback=callback_org_actions store=no no_caption=1
@caption org_actions

//@default group=look
//@groupinfo look caption=Vaata
//@property look type=text callback=look_firma table=objects method=serialize field=meta

@reltype ETTEVOTLUSVORM value=1 clid=CL_ETTEVOTLUSVORM
@caption Õiguslik vorm

@reltype ADDRESS value=3 clid=CL_ADDRESS
@caption Kontaktaadress

@reltype TEGEVUSALAD value=5 clid=CL_TEGEVUSALA
@caption Tegevusalad

@reltype TOOTED value=6 clid=CL_TOODE
@caption Tooted

@reltype CHILD_ORG value=7 clid=CL_CRM_COMPANY
@caption Tütar-organisatsioonid

@reltype WORKERS value=8 clid=CL_ISIK
@caption Töötajad

@reltype PAKKUMINE value=9 clid=CL_PAKKUMINE
@caption Pakkumine

@reltype TEHING value=10 clid=CL_TEHING
@caption Tehing

@reltype KOHTUMINE value=11 clid=CL_KOHTUMINE
@caption Kohtumine

@reltype CALL value=12 clid=CL_CRM_CALL
@caption Kõne
			
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

	function look_firma()
	{

		$nodes = array();
		$nodes['firma'] = array(
			"value" => 'Firma andmed tulevad siia',
		);
		return $nodes;

	}

	function crm_company()
	{
		$this->init(array(
			'clid' => CL_CRM_COMPANY,
			'tpldir' => 'firma',
		));
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
		switch($data['name'])
		{
			case 'status':
				$retval=PROP_IGNORE;
			break;
			case 'firmajuht':
				$i = 1;
				$arr = $this->get_aliases(array(
					'oid' => $args['obj_inst']->id(),
					'reltype' => RELTYPE_WORKERS,
					'type' => CL_ISIK,
				));
//arr($arr);
				$str = '
				<table style="font-size:12px;">';
				$arr[]=array('name' => ' - vali - ','oid' => '0',);
				foreach($arr as $key => $val)
				{
					$col = ($val[OID] == $data['value']) ? 'red': 'blue';
					$str.="<tr><td>
					<a id=\"".$data['name']."_".$i."\" href=\"\" style=\"color:".$col."\" 
					onclick=\"list_preset('".$data['name']."','".$val[OID]."');this.style.color='red';return false;\">".
					$val['name']."</a></td><td>";
					if ($val[OID])
					{
					$str.="<a href=\"".$this->mk_my_orb('change',array(
						'id' => $val[OID],
						'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						),'isik'
						)."\">muuda</a>";
					}
					$str.="</td></tr>";
					$i++;
				}

				$str.='</table><input type="hidden" name="'.$data['name'].'" id="'.$data['name'].'" value="'.$data['value'].'">';
				
				$data['value'] = $str;
			
			break;
			
			case 'navtoolbar':
				/*
				if (!aw_global_get('kliendibaas') || !$args['obj'][OID])
				{
					$retval=PROP_IGNORE;
				}
				else
				{
					$args['kliendibaas'] = aw_global_get('kliendibaas');
					$this->firma_toolbar($args);
				}*/
		                if ($args['obj']['oid'])
                		{
					$args['kliendibaas'] = aw_global_get('kliendibaas');
					$this->firma_toolbar($args);
				}
			break;
			
		};
		return $retval;
	}
	
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		$form = &$args["form_data"];
		$obj = &$args["obj"];
		
		switch($data["name"])
		{
			case 'firmajuht':
				if ($args['obj'][OID])
				{
					$this->db_query('update kliendibaas_firma set firmajuht="'.$form['firmajuht'].'" where oid='.$args['obj'][OID]);
				}
			break;
		};
		return $retval;
	}	

	function callback_org_actions($args)
	{
		$relobjects = $this->get_aliases(array(
			'oid' => $args['obj'][OID],
			'type' => CL_DOCUMENT,
			//'reltype' => ,
		));
		
		$t = new aw_table(array(
			'prefix' => 'org_actions',
		));

		$t->set_default_sortby('changed');//peaks otsima docust sündmuse kellaaja

		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Nimi',
			'sortable' => '1',
		));
		
		$t->define_field(array(
			'name' => 'type',
			'caption' => 'Tüüp',
			'sortable' => '1',
		));
		
		$t->define_field(array(
			'name' => 'moreinfo',
			'caption' => 'lisainfo',
			//'sortable' => '1',
		));
		
		$t->define_field(array(
			'name' => 'event_start',
			'caption' => 'Sündmus algab',
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'event_end',
			'caption' => 'Sündmus lõppeb',
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'createdby',
			'caption' => 'Looja',
			'sortable' => '1',
		));
		$t->define_field(array(
			'name' => 'modifiedby',
			'caption' => 'Muutja',
			'sortable' => '1',
		));

		$reltype_caption = $this->callback_get_rel_types();
		
		foreach($relobjects as $val)
		{
		//arr($val);
			$pl = $this->db_fetch_row('select * from planner where id='.$val[OID]);
			$doc = $this->db_fetch_row('select * from documents where docid='.$val[OID]);		
			
			$classes = $this->cfg["classes"];
			$cldat = $classes[$val['class_id']];
			if (isset($cldat["alias"]))
			{
				if ($cldat["alias_class"])
				{
					$cldat["file"] = $cldat["alias_class"];
				}
			}
			
			
			$t->define_data(array(
				'name' => html::href(array('caption' => $val['name'],'url' => $this->mk_my_orb('change', array('id' => $val[OID]), $cldat["file"]))),
				'type' => $reltype_caption[$val['reltype']],
				'event_start' => date('Y-m-d H:i',$pl['start']),
				'event_end' => date('Y-m-d H:i',$pl['end']),
				'moreinfo' => $doc['moreinfo'],
				'modifiedby' => $val['modifiedby'],
				'createdby' => $val['createdby'],
			));
		}
		$t->sort_by();
		
		$nodes = array();
		$nodes['actions'] = array(
			'value' => $t->draw(),
		);
		return $nodes;	
	}
	
	
	function firma_toolbar(&$args)
	{

		$toolbar = &$args["prop"]["toolbar"];
		$menu_cdata = '';
		$this->read_template('js_popup_menu.tpl');

		$kliendibaas = $this->get_object($args['kliendibaas']);
		//arr($kliendibaas);
		if ($args['kliendibaas'])
		{
			$parents[RELTYPE_ADDRESS] = $kliendibaas['meta']['dir_address'] ? $kliendibaas['meta']['dir_address'] : $kliendibaas['meta']['dir_default'];
			$parents[RELTYPE_TEGEVUSALAD] = $kliendibaas['meta']['dir_tegevusala'] ? $kliendibaas['meta']['dir_tegevusala'] : $kliendibaas['meta']['dir_default'];
			$parents[RELTYPE_WORKERS] = $kliendibaas['meta']['dir_isik'] ? $kliendibaas['meta']['dir_isik'] : $kliendibaas['meta']['dir_default'];
			$parents[RELTYPE_ETTEVOTLUSVORM] = $kliendibaas['meta']['dir_ettevotlusvorm'] ? $kliendibaas['meta']['dir_ettevotlusvorm'] : $kliendibaas['meta']['dir_default'];
			
			$cfgform[RELTYPE_CALL] = $kliendibaas['meta']['kone_form'] ? $kliendibaas['meta']['kone_form'] : $kliendibaas['meta']['default_form'];
			$cfgform[RELTYPE_PAKKUMINE] = $kliendibaas['meta']['pakkumine_form'] ? $kliendibaas['meta']['pakkumine_form'] : $kliendibaas['meta']['default_form'];
			$cfgform[RELTYPE_KOHTUMINE] = $kliendibaas['meta']['kohtumine_form'] ? $kliendibaas['meta']['kohtumine_form'] : $kliendibaas['meta']['default_form'];
			$cfgform[RELTYPE_TEHING] = $kliendibaas['meta']['tehing_form'] ? $kliendibaas['meta']['tehing_form'] : $kliendibaas['meta']['default_form'];
		}
		else
		{
			$parents[RELTYPE_ETTEVOTLUSVORM] = $parents[RELTYPE_WORKERS] = $parents[RELTYPE_ADDRESS] = $parents[RELTYPE_TEGEVUSALAD] = $args['obj']['parent'];
		}

		if ($cal_id = aw_global_get('user_calendar'))
		{
			$user_calendar = $this->get_object($cal_id);
			$parents[RELTYPE_CALL] = $parents[RELTYPE_PAKKUMINE] = $parents[RELTYPE_KOHTUMINE] = $parents[RELTYPE_TEHING] = $user_calendar['meta']['event_folder'];
		}


		$alist = array(
			array('caption' => 'Töötaja','class' => 'isik', 'reltype' => RELTYPE_WORKERS),
			array('caption' => 'Tegevusala','class' => 'tegevusala', 'reltype' => RELTYPE_TEGEVUSALAD),
			array('caption' => 'Aadress','class' => 'address', 'reltype' => RELTYPE_ADDRESS),
			array('caption' => 'Õiguslik vorm','class' => 'ettevotlusvorm', 'reltype' => RELTYPE_ETTEVOTLUSVORM),
			//array('caption' => 'Pakkumine','class' => 'pakkumine', 'reltype' => PAKKUMINE),
			//array('caption' => '','class' => '', 'reltype' => ),
		);
		
		
		$menudata = '';
		if (is_array($alist))
		{
			foreach($alist as $key => $val)
			{
				if (!$parents[$val['reltype']])
				{
					$this->vars(array(
						'title' => 'Kalender määramata',
						'text' => 'Lisa '.$val['caption'],
					));
					$menudata .= $this->parse("MENU_ITEM_DISABLED");
				}
				else
				{
				// continue;
					$this->vars(array(
						'link' => $this->mk_my_orb('new',array(
							'alias_to' => $args['obj']['oid'],
							'reltype' => $val['reltype'],
							'class' => 'planner',
							'id' => $cal_id,
							'group' => 'add_event',
							'action' => 'change',
							'title' => $val['title'].' : '.$args['obj']['name'],
//							'reltype' => $val['reltype'],
//							'class' => $val['class'],
//http://axel.dev.struktuur.ee/automatweb/orb.aw?class=planner&action=change&id=125451&group=add_event
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
				"id" => "firma_sub",
			));
			$menu_cdata .= $this->parse();
		};

		$action = array(
			array('reltype' => RELTYPE_PAKKUMINE, 'title' => 'Pakkumine'),
			array('reltype' => RELTYPE_TEHING,'title' => 'Tehing'),
			array('reltype' => RELTYPE_KOHTUMINE, 'title' => 'Kohtumine'),
			array('reltype' => RELTYPE_KONE,'title' => 'Kõne'),
		);

		$menudata = '';
		if (is_array($action))
		{
			foreach($action as $key => $val)
			{
				if (!$parents[$val['reltype']] || !$cfgform[$val['reltype']])
				{
					$this->vars(array(
						'title' => 'Konfivorm, kalender või kalendri sündumste kataloog määramata',
						'text' => 'Lisa '.$val['title'],
					));
					$menudata .= $this->parse("MENU_ITEM_DISABLED");
				}
				else
				{
					$this->vars(array(
						'link' => $this->mk_my_orb('new',array(
							'alias_to_org' => $args['obj']['oid'],
							'reltype_org' => $val['reltype'],
							'class' => 'planner',
							'id' => $cal_id,
							'group' => 'add_event',
							'action' => 'change',
							'title' => urlencode($val['title'].': '.$args['obj']['name']),
							'parent' => $parents[$val['reltype']],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
							'cfgform_id' => $cfgform[$val['reltype']],
						)),
						'text' => 'Lisa '.$val['title'],
					));
					$menudata .= $this->parse("MENU_ITEM");
				}
			};
			$this->vars(array(
				"MENU_ITEM" => $menudata,
				"id" => "calendar_sub",
			));
			$menu_cdata .= $this->parse();
		};
			
		
		$menudata = '';
		$this->vars(array(
			'sub_menu_id' => 'calendar_sub',
			'text' => 'Lisa kalendrisse...',
		));
		$menudata .= $this->parse("MENU_ITEM_SUB");	
		$this->vars(array(
			'sub_menu_id' => 'firma_sub',
			'text' => 'Lisa organisatsioonile...',
		));
		$menudata .= $this->parse("MENU_ITEM_SUB");	
		$this->vars(array(
			'MENU_ITEM' => '',
			"MENU_ITEM_SUB" => $menudata,
			"id" => "mainmenu",
		));
		$menu_cdata = $this->parse().$menu_cdata;
		
		$toolbar->add_button(array(
			"name" => "add_item_button",
			"tooltip" => "Uus",
			"url" => "",
			"onClick" => "return buttonClick(event, 'mainmenu');",
			"img" => "new.gif",
			"imgover" => "new_over.gif",
			"class" => "menuButton",
		));
		
		if ($cal_id = aw_global_get('user_calendar'))
		{
			$toolbar->add_button(array(
				"name" => "user_calendar",
				"tooltip" => "Kasutaja kalender",
				"url" => $this->mk_my_orb('change', array('id' => $cal_id,'return_url' => urlencode(aw_global_get('REQUEST_URI')),),'planner'),
				"onClick" => "",
				"img" => "icon_cal_today.gif",
				"imgover" => "icon_cal_today_over.gif",
				"class" => "menuButton",
			));
		}
		
		$toolbar->add_cdata($menu_cdata);

	}


}
?>
