<?php
/*
@classinfo relationmgr=yes

@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid
@default table=objects
@default group=general

@property navtoolbar type=toolbar group=general store=no no_caption=1
@caption 

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

//@default group=look
//@groupinfo look caption=Vaata
//@property look type=text callback=look_firma table=objects method=serialize field=meta
	
	
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

define ('ETTEVOTLUSVORM',1);
define ('ADDRESS',3);
//define ('FIRMAJUHT',4);
define ('TEGEVUSALAD',5);
define ('TOOTED',6);
define ('CHILD_ORG',7);
define ('WORKERS',8);
define ('PAKKUMINE',9);
define ('TEHING',10);
define ('KOHTUMINE',11);
define ('KONE',12);
//define ('',);

class firma extends class_base
{

	function look_firma()
	{

		$nodes = array();
		$nodes['firma'] = array(
			"value" => 'Firma andmed tulevad siia',
		);
		return $nodes;

	}

	function firma()
	{
		$this->init(array(
			'clid' => CL_FIRMA,
			'tpldir' => 'firma',
		));
	}

	function callback_get_rel_types()
	{
		return array(
			ETTEVOTLUSVORM => 'Õiguslik vorm',
			ADDRESS => 'Kontaktaadress',
			//FIRMAJUHT => 'Organisatsiooni juht',
			WORKERS => 'Töötajad',
			TEGEVUSALAD => 'Tegevusalad',
			TOOTED => 'Tooted',
			CHILD_ORG => 'Tütar-organisatsioonid',
			PAKKUMINE => 'Pakkumine',
			TEHING => 'Tehing',
		);
	}
	
	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
                switch($args["reltype"])
                {
			case ETTEVOTLUSVORM:
				$retval = array(CL_ETTEVOTLUSVORM);
			break;
			/*case FIRMAJUHT:
				$retval = array(CL_ISIK);
			break;*/
			case WORKERS:
				$retval = array(CL_ISIK);
			break;
			case TEGEVUSALAD:
				$retval = array(CL_TEGEVUSALA);
			break;
			case TOOTED:
				$retval = array(CL_TOODE);
			break;
			case ADDRESS:
				$retval = array(CL_ADDRESS);
			break;
			case CHILD_ORG:
				$retval = array(CL_FIRMA);
			break;
		};
		return $retval;
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
//		$meta=$args['obj']['meta'];
//		$parent=$args['obj']['parent'];
		switch($data['name'])
		{
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'status':
				$retval=PROP_IGNORE;
			break;
			case 'alias':
				$retval=PROP_IGNORE;
			break;
			case 'firmajuht':
				$i = 1;
				$arr = $this->get_aliases(array(
					'oid' => $args['obj'][OID],
					'reltype' => WORKERS,
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
					$str.="<a target=\"_blank\" href=\"".$this->mk_my_orb('change',array('id' => $val[OID]),'isik')."\">muuda</a>";
					}
					$str.="</td></tr>";
					$i++;
				}

				$str.='</table><input type="hidden" name="'.$data['name'].'" id="'.$data['name'].'" value="'.$data['value'].'">';
				
				$data['value'] = $str;
			
			break;
			
			/*case '':
			
			break;*/
			/*case '':
			
			break;*/
			
			
			case 'navtoolbar':
				if (!aw_global_get('kliendibaas') || !$args['obj'][OID])
				{
					$retval=PROP_IGNORE;
				}
				else
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


	function firma_toolbar(&$args)
	{
                if ($args['obj']['oid'])
                {
			$toolbar = &$args["prop"]["toolbar"];

			$this->read_template('js_popup_menu.tpl');
			
			$kliendibaas = $this->get_object($args['kliendibaas']);
			//arr($kliendibaas);
$parents[ADDRESS] = $kliendibaas['meta']['dir_address'] ? $kliendibaas['meta']['dir_address'] : $kliendibaas['meta']['dir_default'];
$parents[TEGEVUSALAD] = $kliendibaas['meta']['dir_tegevusala'] ? $kliendibaas['meta']['dir_tegevusala'] : $kliendibaas['meta']['dir_default'];
$parents[WORKERS] = $kliendibaas['meta']['dir_isik'] ? $kliendibaas['meta']['dir_isik'] : $kliendibaas['meta']['dir_default'];
			if ($cal_id = aw_global_get('user_calendar'))
			{
				$user_calendar = $this->get_object($cal_id);
				$parents[PAKKUMINE] = $user_calendar['meta']['event_folder'];
				$parents[KONE] = $parents[PAKKUMINE];
				$parents[KOHTUMINE] = $parents[PAKKUMINE];
				$parents[TEHING] = $parents[PAKKUMINE];
			}
	
	
			$alist = array(
				array('caption' => 'Töötaja','class' => 'isik', 'reltype' => WORKERS),
				array('caption' => 'Tegevusala','class' => 'tegevusala', 'reltype' => TEGEVUSALAD),
				array('caption' => 'Aadress','class' => 'address', 'reltype' => ADDRESS),
				array('caption' => 'Pakkumine','class' => 'pakkumine', 'reltype' => PAKKUMINE),
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
							'alt' => 'Kalender määramata',
							'text' => 'Lisa : '.$val['caption'],
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
								'class' => $val['class'],
								'parent' => $parents[$val['reltype']],
								'return_url' => urlencode(aw_global_get('REQUEST_URI')),
							)),
							'text' => 'Lisa : '.$val['caption'],
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
					"imgover" => "new_over.gif",
					"class" => "menuButton",
				));
				
			};
			
			

			$action = array(
				//array('caption' => 'Lisa Pakkumine','class' => '', 'reltype' => PAKKUMINE, 'title' => 'Pakkumine'),
				array('caption' => 'Lisa Tehing','class' => '', 'reltype' => TEHING,'title' => 'Tehing'),
				array('caption' => 'Lisa Kohtumine','class' => '', 'reltype' => KOHTUMINE,'title' => 'Kohtumine'),
				array('caption' => 'Lisa Kõne','class' => '', 'reltype' => KONE,'title' => 'Kõne'),
			);

			$menudata = '';
			if ($cal_id && is_array($action))
			{
				foreach($action as $key => $val)
				{
					if (!$parents[$val['reltype']]) continue;
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
						'text' => 'Lisa '.$val['title'],
					));

					$menudata .= $this->parse("MENU_ITEM");
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
					"imgover" => "new_over.gif",
					"class" => "menuButton",
				));
	
			};
			
			
/*			
- Eraldi toolbari nupp on, kus ma saan lisada Kõnet, Kohtumist, Pakkumist ja Tehingut. 
Pakkumise ja Tehingu "objekt" on "toode" seosetüübiga Pakkumine või Tehing.			
*/			
			


			
		
                };
	}


}
?>
