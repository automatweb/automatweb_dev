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

@property firmajuht type=relpicker reltype=WORKERS table=kliendibaas_firma
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
			/*case 'tooted':
			
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
			
			$alist = array(
				array('caption' => 'Lisa töötaja','class' => 'isik', 'reltype' => WORKERS),
				array('caption' => 'Lisa tegevusala','class' => 'tegevusala', 'reltype' => TEGEVUSALAD),
				array('caption' => 'Lisa aadress','class' => 'address', 'reltype' => ADDRESS),
				array('caption' => 'Lisa Pakkumine','class' => 'toode', 'reltype' => TEHING),
				array('caption' => 'Lisa Tehing','class' => 'toode', 'reltype' => PAKKUMINE),
				
				//array('caption' => '','class' => '', 'reltype' => ),				
			);
			$menudata = '';
			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					if (!$parents[$val['reltype']]) continue;
					$this->vars(array(
						'link' => $this->mk_my_orb('new',array(
							'alias_to' => $args['obj']['oid'],
							'reltype' => $val['reltype'],
							'class' => $val['class'],
							'parent' => $parents[$val['reltype']],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						)),
						'text' => $val['caption'],
					));

					$menudata .= $this->parse("MENU_ITEM");
				};
			};

			$action = array(
				array('caption' => 'Lisa Kohtumine','class' => 0, 'reltype' => 0),
				array('caption' => 'Lisa Kõne','class' => 0, 'reltype' => 0),				
			);
/*			
- Eraldi toolbari nupp on, kus ma saan lisada Kõnet, Kohtumist, Pakkumist ja Tehingut. 
Pakkumise ja Tehingu "objekt" on "toode" seosetüübiga Pakkumine või Tehing.			
*/			
			
			$this->vars(array(
				"MENU_ITEM" => $menudata,
				"id" => "create_event",
			));

			$menu = $this->parse();

                	$toolbar->add_cdata($menu);
	
			$toolbar->add_button(array(
                                "name" => "add",
                                "tooltip" => "Uus",
				"url" => "",
				"onClick" => "return buttonClick(event, 'create_event');",
                                "img" => "new.gif",
                                "imgover" => "new_over.gif",
                                "class" => "menuButton",
                        ));
			
		
                };
	}


}
?>
