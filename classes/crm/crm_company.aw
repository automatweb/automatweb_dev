<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_company.aw,v 1.4 2003/12/01 14:26:34 duke Exp $
/*
@classinfo relationmgr=yes
@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@property navtoolbar type=toolbar store=no no_caption=1 group=general,overview editonly=1

@property name type=textbox size=30 maxlenght=255 table=objects
@caption Organisatsiooni nimi

@property comment type=textarea cols=65 rows=3 table=objects
@caption Kommentaar

@property reg_nr type=textbox size=10 maxlenght=20 table=kliendibaas_firma
@caption Registri number

@property pohitegevus type=relpicker reltype=RELTYPE_TEGEVUSALAD table=kliendibaas_firma
@caption Põhitegevus

@property ettevotlusvorm type=relpicker reltype=RELTYPE_ETTEVOTLUSVORM table=kliendibaas_firma
@caption Õiguslik vorm

@property tooted type=relpicker reltype=RELTYPE_TOOTED method=serialize field=meta table=objects
@caption Tooted

@property kaubamargid type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Kaubamärgid

@property contact type=relpicker reltype=RELTYPE_ADDRESS table=kliendibaas_firma
@caption Aadress

@property tegevuse_kirjeldus type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Tegevuse kirjeldus

@property logo type=textbox size=40 method=serialize field=meta table=objects
@caption Organisatsiooni logo(url)

//@property firmajuht type=relpicker reltype=RELTYPE_WORKERS table=kliendibaas_firma
@property firmajuht type=text table=kliendibaas_firma store=
@caption Organisatsiooni juht

@default group=overview
@groupinfo overview caption="Seotud tegevused"

@property progress type=text callback=callback_org_actions store=no no_caption=1
@caption org_actions

//@default group=look
//@groupinfo look caption=Vaata
//@property look type=text callback=look_firma table=objects method=serialize field=meta

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

@reltype PAKKUMINE value=9 clid=CL_CRM_OFFER
@caption Pakkumine

@reltype TEHING value=10 clid=CL_CRM_DEAL
@caption Tehing

@reltype KOHTUMINE value=11 clid=CL_CRM_MEETING
@caption Kohtumine

@reltype CALL value=12 clid=CL_CRM_CALL
@caption Kõne

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
	function crm_company()
	{
		$this->init(array(
			'clid' => CL_CRM_COMPANY,
			'tpldir' => 'firma',
		));
	}
	
	function look_firma()
	{

		$nodes = array();
		$nodes['firma'] = array(
			"value" => 'Firma andmed tulevad siia',
		);
		return $nodes;

	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
		switch($data['name'])
		{
			case 'firmajuht':
				$i = 1;
				$arr = $this->get_aliases(array(
					'oid' => $args['obj_inst']->id(),
					'reltype' => RELTYPE_WORKERS,
					'type' => CL_CRM_PERSON,
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
				$this->firma_toolbar($args);
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
		/// XXX: I need to rewrite this and put in some more logical place
		$ob = $args["obj_inst"];
		$conns = $ob->connections_from();

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

		$classes = $this->cfg["classes"];
	
		foreach($conns as $conn)
		{
			$item = new object($conn->prop("to"));
			
			$cldat = $classes[$item->class_id()];
			if (isset($cldat["alias"]))
			{
				if ($cldat["alias_class"])
				{
					$cldat["file"] = $cldat["alias_class"];
				}
			}
			
			
			$t->define_data(array(
				'name' => html::href(array('caption' => $item->name(),'url' => $this->mk_my_orb('change', array('id' => $item->id()), $cldat["file"]))),
				// oh geez. so that is how it's done
				'type' => $cldat["name"],
				//'type' => $reltype_caption[$val['reltype']],
				//'event_start' => date('Y-m-d H:i',$pl['start']),
				'event_start' => date('Y-m-d H:i',$item->prop("start1")),
				'event_end' => date('Y-m-d H:i',$item->prop("duration")),
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
		$users = get_instance("users");

                $crm_db_id = $users->get_user_config(array(
                        "uid" => aw_global_get("uid"),
                        "key" => "kliendibaas",
                ));

		$cal_id = $users->get_user_config(array(
			"uid" => aw_global_get("uid"),
			"key" => "user_calendar",
		));

		if (empty($crm_db_id))
		{
			$parents[RELTYPE_ETTEVOTLUSVORM] = $parents[RELTYPE_WORKERS] = $parents[RELTYPE_ADDRESS] = $parents[RELTYPE_TEGEVUSALAD] = $args['obj_inst']->parent();
		}
		else
		{
			$crm_db = new object($crm_db_id);
			$default_dir = $crm_db->prop("dir_default");
			$parents[RELTYPE_ADDRESS] = $crm_db->prop("dir_address") == "" ? $default_dir : $crm_db->prop('dir_address');
			$parents[RELTYPE_TEGEVUSALAD] = $crm_db->prop("dir_tegevusala") == "" ? $default_dir : $crm_db->prop('dir_address');
			$parents[RELTYPE_WORKERS] = $crm_db->prop("dir_isik") == "" ? $default_dir : $crm_db->prop('dir_isik');
			$parents[RELTYPE_ETTEVOTLUSVORM] = $crm_db->prop("dir_ettevotlusvorm") == "" ? $default_dir : $crm_db->prop('dir_ettevotlusvorm');
		};

		if (!empty($cal_id))
		{
			$user_calendar = new object($cal_id);
			$parents[RELTYPE_CALL] = $parents[RELTYPE_PAKKUMINE] = $parents[RELTYPE_KOHTUMINE] = $parents[RELTYPE_TEHING] = $user_calendar->prop('event_folder');
		}


		$alist = array(
			array('caption' => 'Töötaja','clid' => CL_CRM_PERSON, 'reltype' => RELTYPE_WORKERS),
			array('caption' => 'Tegevusala','clid' => CL_CRM_SECTOR, 'reltype' => RELTYPE_TEGEVUSALAD),
			array('caption' => 'Aadress','clid' => CL_CRM_ADDRESS, 'reltype' => RELTYPE_ADDRESS),
			array('caption' => 'Õiguslik vorm','clid' => CL_CRM_CORPFORM, 'reltype' => RELTYPE_ETTEVOTLUSVORM),
		);
		
		$toolbar->add_menu_button(array(
			"name" => "main_menu",
			"tooltip" => "Uus",
		));

		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "calendar_sub",
			"text" => "Lisa kalendrisse..",
		));
		
		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "firma_sub",
			"text" => "Lisa organisatsioonile..",
		));


		if (is_array($alist))
		{
			foreach($alist as $key => $val)
			{
				$classinf = $this->cfg["classes"][$val["clid"]];
				if (!$parents[$val['reltype']])
				{
					$toolbar->add_menu_item(array(
						"parent" => "calendar_sub",
						'text' => 'Lisa '.$classinf["name"],
						"disabled" => true,
					));
				}
				else
				{
					$toolbar->add_menu_item(array(
						"parent" => "calendar_sub",
						'link' => $this->mk_my_orb('new',array(
							'alias_to' => $args['obj_inst']->id(),
							'reltype' => $val['reltype'],
							'class' => 'planner',
							'id' => $cal_id,
							'group' => 'add_event',
							'action' => 'change',
							'title' => $classinf["name"].' : '.$args['obj_inst']->name(),
							'parent' => $parents[$val['reltype']],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						)),
						'text' => 'Lisa '.$classinf["name"],
					));
				}
			};
		};

		$action = array(
			array("reltype" => RELTYPE_PAKKUMINE,"clid" => CL_CRM_OFFER),
			array("reltype" => RELTYPE_TEHING,"clid" => CL_CRM_DEAL),
			array("reltype" => RELTYPE_KOHTUMINE,"clid" => CL_CRM_MEETING),
			array("reltype" => RELTYPE_CALL,"clid" => CL_CRM_CALL),
		);

		$menudata = '';
		if (is_array($action))
		{
			foreach($action as $key => $val)
			{
				$classinf = $this->cfg["classes"][$val["clid"]];
				if (!$parents[$val['reltype']])
				{
					$toolbar->add_menu_item(array(
						'parent' => "firma_sub",
						'title' => 'Kalender või kalendri sündmuste kataloog määramata',
						'text' => 'Lisa '.$classinf["name"],
					));
				}
				else
				{
					$toolbar->add_menu_item(array(
						"parent" => "firma_sub",
						'link' => $this->mk_my_orb('new',array(
							'alias_to_org' => $args['obj_inst']->id(),
							'reltype_org' => $val['reltype'],
							'class' => 'planner',
							'id' => $cal_id,
							'group' => 'add_event',
							'clid' => $val["clid"],
							'action' => 'change',
							'title' => urlencode($classinf["name"].': '.$args['obj_inst']->name()),
							'parent' => $parents[$val['reltype']],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						)),
						'text' => 'Lisa '.$classinf["name"],
					));
				}
			};
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
		
	}


}
?>
