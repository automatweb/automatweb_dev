<?php
/*
	@classinfo relationmgr=yes
	@default table=objects
	@default group=general

	@default field=meta
	@default method=serialize


	@property selections type=relpicker reltype=SELECTIONS group=general
	@caption Vaikimisi valim

//////////////////////////////////////////////////////	

	@default group=firmad
	@groupinfo firmad submit=no caption=Organisatsioonid

@property orgtoolbar type=toolbar store=no no_caption=1
	
	@property search_form type=text no_caption=1
	@caption otsing
	
	@property sfield type=textbox
	@property exclude type=textbox
		
	//valimi nupud	
	@property selection_manage_buttons type=text callback=selection_manage_bar
	
	@property manageri type=text callback=firma_manager

	@property make_search type=textbox size=1 	
	
//	@property page type=textbox size=3
//	@caption page
//	@property search type=textbox size=3
/	@property search_require type=textbox size=3
/	@property do_search type=textbox size=3
/	@caption do otsing
/	@property search_history type=textbox size=3
/	@property search_require_history type=textbox size=3
/	@property actions type=text
/	@caption actions
/	@property select type=textbox size=3
/	@property objs type=textbox size=3
/	@property sele type=checkbox ch_value=on

////////////////////////////////////////////////////////////

	@default group=tegevusalad
	@groupinfo tegevusalad caption=Tegevusalad

@property tegtoolbar type=toolbar store=no no_caption=1	
	
	@property tegevusala_manager type=text callback=tegevusala_manager

//	@property teg_page type=textbox size=3
//	@caption teg_page
//	@property teg_do_search type=textbox size=3
//	@caption do otsing
//	@property teg_search type=textbox size=3

////////////////////////////////////////////////////////////
	@default group=settings
	@groupinfo settings caption=Seaded

//	@property limit_per_page type=textbox size=8
//	@caption mitu rida näita
//	@property show_columns type=select multiple=1
//	@caption ettevõtete tabelis näita neid veerge
	
	@property order_by_columns type=select
	@caption sorteeri veeru järgi

	@property dir_firmad type=relpicker reltype=FIRMA_CAT
	@caption Vaikimisi firmade kataloog

	@property dir_isik type=relpicker reltype=ISIK_CAT
	@caption Vaikimisi töötajate kataloog

	@property dir_address type=relpicker reltype=ADDRESS_CAT
	@caption Vaikimisi aadresside kataloog

	@property dir_ettevotlusvorm type=relpicker reltype=ETTEVOTLUSVORM_CAT
	@caption Vaikimisi õiguslike vormide kataloog

	@property dir_linn type=relpicker reltype=LINN_CAT
	@caption Vaikimisi linnade kataloog

//	@property dir_maakond type=relpicker reltype=MAAKOND_CAT
//	@caption maakondade kataloog(id)

//	@property dir_riik type=relpicker reltype=RIIK_CAT
//	@caption riikide kataloog(id)

	@property dir_tegevusala type=relpicker reltype=TEGEVUSALA_CAT
	@caption Vaikimisi tegevusalade kataloog

//	@property dir_toode type=relpicker reltype=TOODE_CAT
//	@caption toodete kataloog(id)

	@property dir_default type=relpicker reltype=GENERAL_CAT
	@caption Vaikimisi kataloog, kui mõni eelnevatest pole määratud, siis kasutatakse seda

//	@property where_firm type=checkbox ch_value=on
//	@caption näita ainult tegevusalasid, kus alal on ka ettevõtteid

	@property flimit type=select
	@caption Kirjeid ühel lehel
	
	@property default_kliendibaas type=checkbox 
	@caption See on kasutaja default kliendibaas


////////////////////////////////////////////////////////////
/	@default group=tests
/	@groupinfo tests caption=tests
/
/	@property test2 type=text
/
/	@property test3 type=text
/
////////////////////////////////////////////////////////////


//	@default group=objects_manager
//	@groupinfo objects_manager caption=Objektide&nbsp;lisamine
//
//	@property objects_manager type=text callback=objects_manager

////////////////////////////////////////////////////////////

/	@default group=overview
/	@groupinfo overview caption=Ülevaade
/
/	@property overview type=text callback=owerview

//////////////valimite kraam////////////////////////////////////////////////////////////////////////////

	@default group=selectione
	@groupinfo selectione submit=no caption=Valimid
	@property active_selection_objects type=text callback=callback_obj_list
	@property active_selection type=textbox group=firmad,selectione
	


*/

define ('SELECTIONS',1);
define ('FIRMA_CAT',2);
define ('ISIK_CAT',3);
define ('ADDRESS_CAT',4);
define ('LINN_CAT',5);
define ('MAAKOND_CAT',6);
define ('RIIK_CAT',7);
define ('TEGEVUSALA_CAT',8);
define ('TOODE_CAT',9);
define ('GENERAL_CAT',10);
define ('CALENDAR',11);
define ('ETTEVOTLUSVORM_CAT',12);

define('SELECTIONS_RELTYPE',SELECTIONS);
		
//define ('',11);
//define ('',);
//define ('',);
//define ('',);
//define ('',);



class kliendibaas extends class_base
{
	var $show_columns;
	var $selections_reltype;
	

	function kliendibaas()
	{
		$this->init(array(
			'clid' => CL_KLIENDIBAAS,
			'tpldir' => 'kliendibaas',
		));
		$this->selections_reltype = SELECTIONS_RELTYPE;
/*		$this->show_columns= array(
			////////firma
//			'firma_oid' => 'firma id',
			'firma_nimetus' => 'firma nimi',
			'firma_reg_nr' => 'reg nr',
			'firma_ettevotlusvorm' => 'ettevõtlusvorm',
			'pohitegevus' => 'põhitegevus',
			'tegevusala_kood' => 'tegevusala kood',
			'tegevuse_kirjeldus' => 'tegevuse kirjeldus',
			'firma_juht' => 'firmajuht',
			'kaubamargid' => 'kaubamärgid',
			'f_aadress' => 'aadress',
			'f_riik' => ' asukoha riik',
			'f_linn' => 'linn',
			'f_maakond' => 'maakond',
			'f_postiindeks' => 'postiindeks',
			'f_telefon' => 'telefon',
			'f_mobiil' => 'mobiiltelefon',
			'f_faks' => 'faks',
			'f_piipar' => 'piipar',
			'f_e_mail' => 'e-mail',
			'f_kodulehekylg' => 'kodulehekülg',
//			'korvaltegevused' => '',
//			'tooted' => '',

/////////// isik
// oid   | firstname | lastname | name | gender | personal_id | title | nickname | messenger | birthday | social_status |
//spouse | children | personal_contact | work_contact | digitalID | notes | pictureurl | picture |

/////////// aadress
// oid   | name | tyyp | riik | linn  | maakond | postiindeks | telefon | mobiil | faks | piipar |
// aadress | e_mail | kodulehekylg
		);*/
	}	
		
//// valim///
/* ühesõnaga valimi klassiga näitame valimeid ja manageerime neid
põhimõtteliselt seda valimi tabi ei olegi vaja siin näidata
*/

	function callback_obj_list($args)
	{
		classload('kliendibaas/selection');
		$arg2['obj'][OID] = $args['obj']['meta']['active_selection'];
		$arg2['obj']['parent'] = $args['obj']['parent'];
		$arg2['obj']['meta']['active_selection'] = $args['obj']['meta']['active_selection'];
		$arg2['sel']['oid'] = $args['obj'][OID];	
		return $this->selection_object->obj_list($arg2);
	}
	
	function selection_manage_bar($args = array())
	{
		$nodes = array();
		$nodes['toolbar'] = array(
			'value' => $this->selection_object->mk_toolbar(array(
				'selection' => $args['obj'][OID],
				'parent' => $this->selection['parent'],
				'selected' => $this->selection['meta']['active_selection'],
				'align' => 'right',
				'show_buttons' => array('add','change'),
			))
		);
		return $nodes;
	}
//// end:valim///



	function callback_get_rel_types()
	{
		return array(
			SELECTIONS => 'Valimid',
			FIRMA_CAT => 'Firmade kataloogid',
			ISIK_CAT => 'Töötajate kataloogid',
			ADDRESS_CAT => 'Aadresside kataloogid',
			LINN_CAT => 'Linnade kataloogid',
			MAAKOND_CAT => 'Maakondade kataloogid',
			RIIK_CAT => 'Riikide kataloogid',
			TEGEVUSALA_CAT => 'Tegevusalade kataloogid',
			TOODE_CAT => 'Toodete kataloogid',
			GENERAL_CAT => 'Üldkataloog',
			CALENDAR => 'Kalender',
			ETTEVOTLUSVORM_CAT => 'Õiguslike vormide kataloogid',
			
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
                switch($args["reltype"])
                {
			case SELECTIONS:
				$retval = array(CL_SELECTION);
			break;
			case FIRMA_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case ISIK_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case ADDRESS_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case LINN_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case MAAKOND_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case RIIK_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case TEGEVUSALA_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case TOODE_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case ETTEVOTLUSVORM_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case GENERAL_CAT:
				$retval = array(CL_PSEUDO);
			break;
			case CALENDAR:
				$retval = array(CL_PLANNER);
			break;
			
		};
		return $retval;
	}


	function get_property(&$args)
	{

		$data = &$args['prop'];
		$retval = PROP_OK;
		$meta = &$args['obj']['meta'];
		$req = &$args['request'];
		//print_r($args);die();
		
		//// valim///
		/* loome valimi instansi kui seda juba tehtud pole */
		if (!is_object($this->selection_object) && method_exists($this,'callback_obj_list'))
		{
			classload('kliendibaas/selection');
			$this->selection_object = new selection();
			$this->selection = $args['obj'];
		}

		if (!is_array($this->got_aliases))
		{
			$arr = $this->get_aliases(array('oid' => $args['obj'][OID]));
			foreach($arr as $key => $val)
			{
				$this->got_aliases[$val['reltype']][] = $val['target'];
			}
		}

		switch($data["name"])
		{
			//// valim///
			/* ühesõnaga see on hidden element, meil on vaja et ta metas salvestuks */
			case 'active_selection':
				$retval=PROP_IGNORE;
				break;
			
			case 'default_kliendibaas':
				$this->users = get_instance("users");
				
				if ($args['obj'][OID] ==
					$this->users->get_user_config(array(
						"uid" => aw_global_get("uid"),
						"key" => "kliendibaas",
				)))
				{
					$data['checked'] = true;
				}
				$data['ch_value'] = $args['obj'][OID];
			
			break;
								
			
			case 'orgtoolbar':
				/*if (!aw_global_get('user_calender') || kliendibaasiga seotud kalender)
				{
					$retval=PROP_IGNORE;
				}
				else*/
				{
					//$args['kliendibaas'] = aw_global_get('kliendibaas');
					$this->org_toolbar($args);
				}
			break;
			case 'tegtoolbar':
				{
					$this->teg_toolbar($args);
				}
			break;
				
			/*case 'test1':
			
				if(is_array($meta['valimid']))
				{
				foreach($meta['valimid'] as $key)
				{
					$sel_obj=$this->get_object($key);
					//$sel_obj['name']
					$str.=html::button(array('name' => "valim[$key]", 'value' => "v: ".$sel_obj['name']));
				}
				}
				$data['value'] = $str;

			break;

			case 'test2':
				$data['value'] = '';
			break;

			case 'test3':
				$data['value'] = '';
			break;

			case 'test':
				$retval=PROP_IGNORE;
			break;*/
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'pagelimit':
				$data['options'] = array ('10' => '10', '20' => '20', '30' => '30');
			break;
			
			
			case 'alias':
				$retval=PROP_IGNORE;
			break;
			case 'search_form':

/*				if (isset($req['do_search']))
				{
					$meta['do_search'] = true;
					$meta['search'] = $meta['search_history'][$req['search_history']];
				}*/
				$data['value'] = $this->search_form($args);
			break;
			case 'sfield':
				$retval=PROP_IGNORE;
			break;
			case 'exclude':
				$retval=PROP_IGNORE;
			break;
			case 'make_search':

				/*$this->set_object_metadata(array(
					OID => $args['obj']['oid'],
					'key'=>'make_search',
					'value'=>'0',
				));*/
				$data['value'] = '0';
				$args['obj']['oid']['meta']['make_search'] = '0';
				
			break;
		
						
			/*case 'search':
//				foreach ($meta['select'] as $key => $val)
				{
//					echo $key.'<br />';
//					$data['value'].=$val.'<br />';
				}
				$retval=PROP_IGNORE;
//				die('kk');
			break;*/
//			case 'manager':
//				$data['value']=$this->selall().$this->firma_manager($args);
//			break;
//			case 'tegevusala_manager':
//				$data['value']=$this->selall().$this->tegevusala_manager($args);
//			break;
			case 'select':
				$retval=PROP_IGNORE;
			break;
			case 'objs':
				$retval=PROP_IGNORE;
			break;
			/*case 'teg_search':
				$retval=PROP_IGNORE;
			break;*/
/*			case 'teg_do_search':
				$data['value']='';
			break;
			case 'search_require':
				$retval=PROP_IGNORE;
			break;
			case 'search_history':
				$retval=PROP_IGNORE;
			break;

			case 'limit_per_page':
				$data['value']=$data['value']?$data['value']:20;
			break;*/
			/*case 'do_search':
				$data['value']='';
			break;*/
			case 'order_by_columns':
				$data['options'] =  array(
					'firma_nimetus' => 'firma nimi',
//					'firma_reg_nr' => 'reg nr',
//					'firma_ettevotlusvorm' => 'ettevõtlusvorm',
//					'pohitegevus' => 'põhitegevus',
//					'tegevusala_kood' => 'tegevusala kood',
//					'tegevuse_kirjeldus' => 'tegevuse kirjeldus',
//					'isik.firma_juht' => 'firmajuht',
//					'kaubamargid' => 'kaubamärgid',
//					'f_address.f_aadress' => 'aadress',
//					'f_riik' => ' asukoha riik',
//					'f_address.f_linn' => 'linn',
//					'f_address.f_maakond' => 'maakond',
//					'f_postiindeks' => 'postiindeks',
//					'f_telefon' => 'telefon',
//					'f_mobiil' => 'mobiiltelefon',
//					'f_faks' => 'faks',
//					'f_piipar' => 'piipar',
//					'f_e_mail' => 'e-mail',
//					'f_address.f_kodulehekylg' => 'kodulehekülg',
				);
			break;
			case 'show_columns':
			
				$data['options'] = $this->show_columns;
//				print_r($args['obj']['meta']['show_columns']);die();
//				$data['selected']=$args['obj']['meta']['show_columns'];
//				$data['multiple']=1;
			break;
		}
		return  $retval;
	}


	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$meta =  &$args['obj']['meta'];
		$retval = PROP_OK;
		$prop = &$arr["prop"];
		
/*		print_r($args);
		die();
*/
		switch($data['name'])
		{
		
			case 'default_kliendibaas':

					$this->users = get_instance("users");
					
					$kb = $this->users->get_user_config(array(
						"uid" => aw_global_get("uid"),
						"key" => "kliendibaas",
					));
					if(($kb == $args['obj'][OID]) || ($kb == ''))
					{
						$this->users->set_user_config(array(
							"uid" => aw_global_get("uid"),
							"key" => "kliendibaas",
							"value" => $args['obj'][OID],
						));
						aw_session_set('kliendibaas', $args['obj'][OID]);
					}

/*			
				if ($form['default_kliendibaas'])
				{
					
				}
				else
				{
				
				
				}*/
	
			break;
		/*
			case 'select':
			if ($meta['valim'])
			{
				if (is_array($form['objs']))
				{
					$sl = get_instance('kliendibaas/selection');
//					$selection = (array)$this->get_object_metadata(array(OID=>$meta['valim'],'key'=>'selection'));
					$selection = $sl->get_selection($meta['valim']);
					if (is_array($selection))
					foreach($form['objs'] as $key => $val)
					{
						if (isset($form['objs'][$key]) && !isset($form['select'][$key]) && $selection[$key])
						{
							unset($selection[$key]);
						}
					}
//					$this->set_object_metadata(array(OID=>$meta['valim'],'key'=>'selection','value'=>$selection+(array)$form['select']));
					$objects = (array)$selection+(array)$form['select'];
					$sl->set_selection($meta['valim'],$objects);
				}
				$retval = PROP_IGNORE;

			}
			break;*/

/*			case 'search':
				if ($form['do_search'])
				{
					$oo = $this->get_object_metadata(array('no_cashe' => 1,OID => $args['obj'][OID],'key' => 'search_history'));
					//$pp = $this->get_object_metadata(array(OID => $args['obj'][OID],'key' => 'search__require_history'));
					if (count($oo) > 5)
					{
						array_pop($oo);
					//	array_pop($pp);
					}
					$oo = (array)$oo;
					//$pp = (array)$pp;
					array_push($oo,$form['search']);
					//array_push($pp,$form['search_require']);
					$this->set_object_metadata(array(OID => $args['obj'][OID], 'key' => 'search_history', 'value' => $oo));
					$this->search_values = $form['search'];
					//$this->set_object_metadata(array(OID => $args['obj'][OID], 'key' => 'search_require_history', 'value' => $pp));
					$retval = PROP_IGNORE;
				}
			break;*/

		};

		return $retval;
	}

	function tegevusala_manager($args)
	{//arr($args,1);
	
		$tase = $args['request']['tase'] ? $args['request']['tase'] : 1;
		$kood = $args['request']['kood'] ? $args['request']['kood'] : '0';
		$teg_oid = $args['request']['teg_oid'] ? $args['request']['teg_oid'] : 0;
		
		$tase = ($tase>3)?3:$tase;
		//arr($args);
		$limit = 25;

	
		
		$teg_parent = ' t1.parent'.$this->parent_in($this->got_aliases[TEGEVUSALA_CAT]).' and ';
			
		if ($tase == 1)
{
		$arr = $this->db_fetch_array('
		select t1.oid as oid, t1.name as name, t2.kood as kood from objects t1 left join kliendibaas_tegevusala t2 on t1.oid=t2.oid
		where '.$teg_parent.' 
		t1.status>0 and class_id='.CL_TEGEVUSALA.' and
		length(t2.kood)<=2
		order by t2.kood
		limit '.$limit.'
		');
}
elseif ($tase == 2)
{
		$arr = $this->db_fetch_array('
		select t1.oid as oid, t1.name as name, t2.kood as kood from objects t1 left join kliendibaas_tegevusala t2 on t1.oid=t2.oid
		where '.$teg_parent.' 		
		t1.status>0 and class_id='.CL_TEGEVUSALA.' and
		length(t2.kood)>2 and length(t2.kood)<=4 and
		
		t2.kood like("'.$kood.'%")
		order by t2.kood
		limit '.$limit.'
		');
}
elseif ($tase == 3)
{
		$arr = $this->db_fetch_array('
		select t1.oid as oid, t1.name as name, t2.kood as kood from objects t1 left join kliendibaas_tegevusala t2 on t1.oid=t2.oid
		where '.$teg_parent.' 
		t1.status>0 and class_id='.CL_TEGEVUSALA.' and
		length(t2.kood)>4 and length(t2.kood)<=6 and
		
		t2.kood like("'.$kood.'%")
		order by t2.kood
		limit '.$limit.'
		');
}

		$t = new aw_table(array(
			'prefix' => 'kliendibaas_manager',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		/*$t->define_field(array(
			'name' => 'kood',
			'caption' => 'Kood',
		));*/
		$t->define_field(array(
			'name' => 'tegevusala',
			'caption' => 'Tegevusala',
		));

		$t->define_field(array(
			'name' => 'fcount',
			'caption' => 'Firmasid',
		));

		$t->define_field(array(
			'name' => 'check',
			'caption' => "<a href='javascript:selall(\"sel\")'>Vali</a>",
			'width'=> 15,
		));
		
		if (is_array($arr))
		foreach($arr as $val)
		{
		$cnt = $this->db_fetch_field('select count(*) as cnt from aliases as t1 left join objects as t2 on t1.target=t2.oid 
		left join objects as t3 on t1.source=t3.oid		
		where t1.target="'.$val[OID].'" and reltype=5 and t1.source<>0 and t2.status=1
		and t3.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
		','cnt');
		
			$t->define_data(
				array(
					//'kood' => $val['kood'],
					'tegevusala' => '<a href="'.$this->mk_my_orb('change', array(
				'id' => $args['obj'][OID],
				'group' => 'tegevusalad',
				'tase' => ($tase + 1),
				'kood' => $val['kood'],
				'teg_oid' => $val[OID],
				)).'">'.$val['name'],'</a>',
				'fcount' => $cnt,
				)
			);
		}
		
		//select * from objects t1 left join kliendibaas_firma t2 left join aliases where 
		
		// t1 = aliases
		// t2 = tegevusala
		// t3 = firma

	
		
		$q = '
		select t3.* from aliases as t1 left join objects as t2 on t1.target=t2.oid 
		left join objects as t3 on t1.source=t3.oid		
		where t1.target="'.$teg_oid.'" and t1.reltype=5 and t1.source<>0 and t2.status=1 
		and t3.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
		order by t3.name
		';
		$arr = $this->db_fetch_array($q);
		
		//source=firma
		//target=tegevusala

		//arr($arr);

		$tf = new aw_table(array(
			'prefix' => 'kliendibaas_frimad',
		));
		$tf->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$tf->define_field(array(
			'name' => 'fname',
			'caption' => 'Firma',
			'sortable' => '1',
		));

		if (is_array($arr))
		foreach($arr as $val)
		{
			$tf->define_data(
				array(
					//'kood' => $val['kood'],
					'fname' => html::href(array(
						'url' => $this->mk_my_orb('change',array('id' => $val[OID]), 'kliendibaas/firma'),
						'caption' => $val['name'],
					))
				)
			);
		}
		
		$nodes = array();
		$nodes['teg'] = array(
			"value" => $t->draw().$tf->draw(),
		);
		return $nodes;

	}
	
	function parent_in($arr)
	{
		if (is_array($arr))
		{
			foreach($arr as $val)
			$parents[] = $val;
		}

		if (count($parents) == 1)
			return '='.$parents[0];

		if (count($parents) > 1)
			return ' in ('.implode(',',$parents).') ';

		return '<>0';
	}
	
	/*
	function tegevusala_manager_($ob)
	{
		$meta=$ob['obj']['meta'];
		$limit=$meta['limit_per_page']?$meta['limit_per_page']:20;
		$req=$ob['request'];
		$page=$meta['teg_page']?$meta['teg_page']:0;
		$page=isset($req['page'])?$req['page']:$page;

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'kliendibaas_manager',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'kood',
			'caption' => 'kood',
		));

		$t->define_field(array(
			'name' => 'tegevusala',
			'caption' => 'tegevusala',
		));

		$t->define_field(array(
			'name' => 'fcount',
			'caption' => 'firmasid',
		));

		$t->define_field(array(
			'name' => 'check',
			'caption' => "<a href='javascript:selall(\"sel\")'>Vali</a>",
			'width'=> 15,
		));

		$find = new aw_table(array(
			'prefix' => 'kliendibaas_manager_search',
		));

		$search=html::textbox(array('name' => 'teg_search[kood]','value' => $meta['search'][$key],'size' => 20)).' '.
		html::textbox(array('name' => 'teg_search[tegevusala]','value' => $meta['search'][$key],'size' => 20)).' '.
		html::button(array('value' => 'otsi','onclick' => 'document.changeform.teg_do_search.value=1;document.changeform.submit()'));


		if ($meta['teg_do_search'])
		{
			$where="where kood like '%".$meta['teg_search']['kood']."%' and tegevusala like '%".$meta['teg_search']['tegevusala']."%' and";

			$q = 'select count(*) as cnt from kliendibaas_tegevusala as t1 left join objects as t2 on t1.oid=t2.oid '.$where.' status<>0';
			$cnt = $this->db_fetch_field($q,'cnt');

			$q = 'select t1.oid,t1.tegevusala,t1.kood from
		 	kliendibaas_tegevusala as t1 '.$where.
		 	' order by t1.kood LIMIT '.($page*$limit).','.((int)$limit);

			$data = $this->db_fetch_array($q);
			$t=$this->tegevusalad_table($data,$selection,$t,$ob['obj'][OID],'');

		}
		else
		{
//			$where=$req['kood']?"where kood like '".$req['kood']."%'":' where LENGTH(kood) < 3 ';
//		}

//		$leftright=$meta['where_firm']?'right':'left';


		$level=$req['level']?$req['level']:'1';
		$grp="concat('".$req['section']."',SUBSTRING(kood,".$level.",2))";

		$gsection=$req['section']?"and kood like '".$req['section']."%'":'';



		$q="select ".$grp." as code from kliendibaas_tegevusala as t1 left join objects as t2 on t1.oid=t2.oid where status<>0 ".$gsection." group by ".$grp." order by kood";

		$data = $this->db_query($q);
		while ($row=$this->db_next())
		{
			if (strlen(trim($row['code']))>0)
			{
				$wheres[]="'".str_pad($row['code'], 6,'0')."'";
			}
		}


		if (is_array($wheres))
		{

			$where = ' where kood in ('.implode(',',$wheres).') ';

			$q = 'select count(distinct kood) as cnt from kliendibaas_tegevusala '.$where.'';
			$cnt = $this->db_fetch_field($q,'cnt');
*/
/*			$q = 'select t1.oid,t1.tegevusala,t1.kood,count(t2.reg_nr) as fcount from
		 	kliendibaas_tegevusala as t1
		 	'.$leftright.' join kliendibaas_firma as t2
		 	on t2.pohitegevus=t1.oid '.$where.
		 	'   group by t1.kood order by t1.kood LIMIT '.($page*$limit).','.((int)$limit);*/
/*
			$q = 'select t1.oid,t1.tegevusala,t1.kood from
			kliendibaas_tegevusala as t1 '.$where.
			' group by t1.kood   order by t1.kood LIMIT '.($page*$limit).','.((int)$limit);

			$data = $this->db_fetch_array($q);

			//die();

			$t=$this->tegevusalad_table($data,$selection,$t,$ob['obj'][OID],$level+2);
		}
		else
		{
			$t='<br />ei leidnud<br />';
		}
}




		$navigate=array(
			' |< '=> 0,
			' < ' => abs($page-1),
			' || '=> $page,
			' > ' => ($page+1),
			' >| '=> ((int)($cnt/$limit)),
		);
		$nav=$this->my_buttons($navigate,'teg_page');


		//function page_numbers($url,$min,$max)


		$lks = '';
		for ($i=0;$i<=((int)($cnt/$limit));$i++)
		{
			$lks.=
			html::href(array('caption'=>'<b>'.($i+1).'</b>',
				'url'=>$this->mk_my_orb('change',
					array(
						'id'=>$ob['obj'][OID],
						'group'=>'tegevusalad',
						'kood'=>$row['kood'],
						'page'=>$i,
						'level'=> $level,
						'section' =>$req['section'],
					)//'prev'=>$req['kood'])
				)
			)).' ';

		}


		$nodes = array();
		$nodes['teg'] = array(
			"value" => $nav.$lks.'total :'.$cnt.'.  '.$back.$t.$search,
		);
		return $nodes;

//		$data['value']=$this->selall().$this->tegevusala_manager($args);
	}

*/
/*
	function tegevusalad_table($data,$selection,$t,$oid,$level)
	{
		$arr = new aw_array($data);
		foreach($arr->get() as $row)
		{
			$row['check']=html::checkbox(array('name'=>'sel['.$row[OID].']'));
//			$row['change']=html::href(array('caption'=>'muuda','url'=>$this->mk_my_orb('change',array('id'=>$row[OID]),'kliendibaas/tegevusala')));
			$row['fcount']=$row['fcount']?(html::href(array('caption'=>'<b> [ '.$row['fcount'].' ] </b>',
				'url'=>$this->mk_my_orb('change',
					array('id'=>$oid, 'group'=>'manager', 'kood'=>$row['kood'])
				)
			))):'';
			$section=substr($row['kood'],0,2);
			$row['kood']=html::href(array('caption'=>'<b>'.$row['kood'].'</b>',
				'url'=>$this->mk_my_orb('change',
					array('id'=>$oid, 'group'=>'tegevusalad', 'kood'=>$row['kood'],'page'=>0,'level'=> $level, 'section' =>$section)//'prev'=>$req['kood'])
				)
			));
			$row['tegevusala']=html::href(array('caption'=>$row['tegevusala'],'url'=>$this->mk_my_orb('change',array('id'=>$row[OID]),'kliendibaas/tegevusala')));

			$t->define_data(
				$row
			);
		}
		return $t->draw();
	}
	*/
	function firma_manager($args)
	{
	
		$letter = $args['request']['letter'] ? $args['request']['letter'] : 'A';
		$fpage = $args['request']['fpage'] ? $args['request']['fpage'] : '1';
		$flimit = $args['meta']['flimit'] ? $args['meta']['flimit'] : 20;
		$letters = '';
		$pages = '';
		$showpagenr = array();
		
		//echo 
		$make_search = ($args['obj']['meta']['make_search'] && !$args['request']['no_search']) ? true : false;
		
			//t1 objects(firma)
			//t2 kliendibaas_firma
			//t3 objects(ettevotlusvorm) -> kliendibaas_firma
			//t4 objects(contact) -> kliendibaas_firma
			//t5 objects(firmajuht) -> kliendibaas_firma
			//t6 kliendibaas_address -> kliendibaas_firma
			//t7 objects(linn) -> objects(contact)
			//t8 objects(maakond) -> objects(contact)
			//t9 objects(e_mail) -> objects(contact)
			//t10 extlinks(e_mail) -> objects(e_mail)
			//t11 objects(kodulehekylg) -> objects(contact)
			//t12 extlinks(kodulehekylg) -> objects(kodulehekylg)	
			//t13 objects(telefon) -> objects(contact)
			//t14 objects(pohitegevus) -> kliendibaas_firma
			
		$join_tables = array(
			'left join kliendibaas_firma as t2 on t1.oid=t2.oid',
			'left join objects as t3 on t2.ettevotlusvorm=t3.oid',
			'left join objects as t4 on t2.contact=t4.oid',
			'left join objects as t5 on t2.firmajuht=t5.oid',
			'left join kliendibaas_address as t6 on t4.oid=t6.oid',
			'left join objects as t7 on t6.linn=t7.oid',
			'left join objects as t8 on t6.maakond=t8.oid',
			'left join objects as t9 on t6.e_mail=t9.oid',
			'left join extlinks as t10 on t9.oid=t10.id',
			'left join objects as t11 on t6.kodulehekylg=t11.oid',
			'left join extlinks as t12 on t11.oid=t12.id',
			'left join objects as t13 on t6.telefon=t13.oid',
			'left join objects as t14 on t2.pohitegevus=t14.oid',
		);
		
		if ($make_search)
		{	
			$search_params = '';
			$exclude = $args['obj']['meta']['exclude'];
			$sfield = $args['obj']['meta']['sfield'];

			if ($sfield[$id = 'name'])
			{
				$not = $exclude[$id]? 'not' : '';
				$search_params .= ' and t1.'.$id.' '.$not.' like ("%'.addslashes($sfield[$id]).'%") ';
			}
			
			if ($sfield[$id = 'reg_nr'])
			{
				$not = $exclude[$id]? 'not' : '';
				$search_params .= ' and t2.'.$id.' '.$not.' like ("%'.addslashes($sfield[$id]).'%") ';
			}

			if ($sfield[$id = 'ettevotlusvorm'])
			{
				$op = $exclude[$id]? '<>' : '=';
				$search_params .= ' and t2.'.$id.' '.$op.''.$sfield[$id].' ';
			}
			
			if ($sfield[$id = 'linn'])
			{
				$op = $exclude[$id]? '<>' : '=';
				$search_params .= ' and t6.'.$id.' '.$op.''.$sfield[$id].' ';
			}
			
			if ($sfield[$id = 'maakond'])
			{
				$op = $exclude[$id]? '<>' : '=';
				$search_params .= ' and t6.'.$id.' '.$op.''.$sfield[$id].' ';
			}
			
			if ($sfield[$id = 'address'])
			{
				/*$parts = explode(' ',$sfield['address']);
			
				foreach($parts as $val)
				{
					
				}*/
				
				$not = $exclude[$id]? 'not' : '';
				$search_params .= ' and t4.name '.$not.' like ("%'.addslashes($sfield[$id]).'%") ';
			}
			
			if ($sfield[$id = 'firmajuht'])
			{
				$not = $exclude[$id]? 'not' : '';
				$search_params .= ' and t5.name '.$not.' like ("%'.addslashes($sfield[$id]).'%") ';
			}
			
			$cnt = $this->db_fetch_field('
			select count(*) as cnt from objects as t1 
			'.implode(' ',$join_tables).'
			where 
			t1.class_id='.CL_FIRMA.' and t1.status<>0 and
			t1.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
			'.$search_params.'
			','cnt');
			
		}
		else
		{
			$cnt = $this->db_fetch_field('
			select count(*) as cnt from objects as t1
			where 
			t1.class_id='.CL_FIRMA.' and t1.status<>0 and
			t1.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
			and t1.name like ("'.$letter.'%")
			','cnt');
		}
		
		if ($cnt>$flimit)
		{
			$pagearray = array();
			$pagecnt = ceil($cnt/$flimit);
			for($i = 1; $i <= $pagecnt; $i++)
			{
			$uri = "'".$this->mk_my_orb('change',
					array(
						'id' => $args['obj'][OID],
						'group' => 'firmad',
						'fpage' => $i,
						'letter' => $letter,
						'no_search' => $make_search ? '0' : '1',
					)
				)."'";
				
				$pagearray[$i] = '<button onclick="document.location='.$uri.';return false;">'.
			(($i == $fpage) ? '<b><u>'.$i.'</u></b>' : $i).
			'</button>';
			}
			
			if ($pagecnt > 25)
			{
				for($i = $fpage - 6;$i <= $fpage + 4 ; $i++)
				{
					$showpagenr[$i] = true;
				}
				for($i = 1;$i<=9;$i++)
				{
					$showpagenr[$i] = true;
					$showpagenr[$pagecnt-(1*($i-1))] = true;
				}
				
				for($i = 1; $i <= $pagecnt; $i++)
				{
					if ($showpagenr[$i] === true)
					{
						$pages .= $pagearray[$i];
						$b = true;
					}
					else
					{
						if ($b)
						{
							$pages .= ' ... ';
						}
						$b = false;

					}
				}
			}
			else
			{
				$pages = implode("",$pagearray);
			}
		}
		
		$limit = 'limit '.(($fpage-1) * $flimit).','.$flimit;
		
		
		
		
		$select_fields = 't1.*,t2.reg_nr,t3.name as ettevotlusvorm, t4.name as full_address, t5.name as firmajuht,
		t7.name as linn,t8.name as maakond, t10.url as e_mail, t12.url as kodulehekylg, t6.aadress as address,
		t13.name as telefon, t14.name as pohitegevus
		';
		
		if ($make_search)
		{
			$q = '
			select '.$select_fields.'
			from objects as t1 
			'.implode(' ',$join_tables).'
			where 
			t1.class_id='.CL_FIRMA.' and t1.status<>0 and
			t1.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
			'.$search_params.'
			order by t1.name
			'.$limit.'
			';
		}
		else
		{		
			$q = '
			select '.$select_fields.'
			from objects as t1 
			'.implode(' ',$join_tables).'
			where 
			t1.class_id='.CL_FIRMA.' and t1.status<>0 and
			t1.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
			and t1.name like ("'.$letter.'%")
			order by t1.name
			'.$limit.'
			';
		}
		$arr = $this->db_fetch_array($q);

		$tf = new aw_table(array(
			'prefix' => 'kliendibaas_frimad',
		));
		$tf->set_default_sortby('fname');	
		$tf->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$tf->define_field(array(
			'name' => 'fname',
			'caption' => 'Organisatsioon',
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'reg_nr',
			'caption' => 'Reg nr.',
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'pohitegevus',
			'caption' => 'Tegevusala',
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'ettevotlusvorm',
			'caption' => 'Õiguslik vorm',
			'sortable' => '1',
		));
		
		/*$tf->define_field(array(
			'name' => 'full_address',
			'caption' => 'Aadress',
			'sortable' => '1',
		));*/
		
		$tf->define_field(array(
			'name' => 'address',
			'caption' => 'Aadress',
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'linn',
			'caption' => 'Linn/Vald/Alev',
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'maakond',
			'caption' => 'Maakond',
			'sortable' => '1',
		));		
		
		$tf->define_field(array(
			'name' => 'e_mail',
			'caption' => 'E-post',
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'kodulehekylg',
			'caption' => 'Kodulehekülg',
			'sortable' => '1',
		));
		$tf->define_field(array(
			'name' => 'telefon',
			'caption' => 'Telefon',
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'firmajuht',
			'caption' => 'Organisatsiooni juht',
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'check',
			'caption' => "<a href='javascript:selall(\"sel\")'>Vali</a>",
			'width'=> 15,
		));

		if (is_array($arr))
		foreach($arr as $val)
		{
			$check = html::checkbox(array('name'=>'sel['.$val[OID].']','checked' => isset($selection[$val[OID]]) ? $selection[$val[OID]] : false,'value' => $val[OID]));
			$check.= html::hidden(array('name'=>'objs['.$val[OID].']' ,'value'=>1));
		
			$tf->define_data(
				array(
					//'kood' => $val['kood'],
					'fname' => html::href(array(
						'url' => $this->mk_my_orb('change',array('id' => $val[OID]), 'kliendibaas/firma'),
						'caption' => $val['name'],
					)),
					'check' => $check,
					'reg_nr' => $val['reg_nr'],
					'ettevotlusvorm' => $val['ettevotlusvorm'],
					'full_address' => $val['full_address'],
					'address' => $val['address'],
					'firmajuht' => $val['firmajuht'],
					'linn' => $val['linn'],
					'maakond' => $val['maakond'],
					'e_mail' => $val['e_mail'],
					'kodulehekylg' => html::href(array('url' => $val['kodulehekylg'],'caption' => $val['kodulehekylg'],'target' => '_blank')),
					'telefon' => $val['telefon'],
					'pohitegevus' => $val['pohitegevus'],
				)
			);
		}


		$arr = $this->db_fetch_array('select substring(name,1,1) as letter from objects 
		where class_id='.CL_FIRMA.' and status<>0 and parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).' 
		group by substring(name,1,1)
		order by substring(name,1,1)
		limit 50
		'
		);
		
		if (is_array($arr))
		foreach($arr as $val)
		{
			$uri = "'".$this->mk_my_orb('change',
					array(
						'id' => $args['obj'][OID],
						'group' => 'firmad',
//						'kood'=>$row['kood'],
						'page' => $i,
						'letter' => $val['letter'],
						'no_search' => '1',						
//						'level'=> $level,
//						'section' =>$req['section'],
					)
				)."'";
			
			
			$letters.='<button style="width:21px" onclick="document.location='.$uri.';return false;">'.
			(($val['letter']==$letter) ? '<b><u>'.$val['letter'].'</u></b>' : $val['letter']).
			'</button>';
		
		}

	//arr($arr);

		//echo count($arr);
		
		$tf->sort_by();
				
		$nodes = array();
		$nodes['teg'] = array(
			"value" => $letters.'<br />'.$pages.$tf->draw().'<br />total:'.$cnt,
		);
		return $nodes;
	
	}
	
/*
	function firma_manager_($args)
	{
	//arr($args,1);
//error_reporting(E_ALL);
		$meta = $args['obj']['meta'];
		$limit = (isset($meta['limit_per_page']) && ($meta['limit_per_page'] > 0)) ? $meta['limit_per_page'] : 20;
		$req = $args['request'];
		$page = isset($meta['page']) ? $meta['page'] : 0;
		$page = isset($req['page']) ? $req['page'] : $page;
		$order_by = isset($meta['order_by_columns']) ? $meta['order_by_columns'] : 'objects.name';
		$where = ' where objects.status<>0 && objects.parent="'.FIRMA_CAT.'" ';

		$sele = isset($meta['sele']) ? $meta['sele'] : false ;


//		if ($req['do_search'])
//		{
//			$meta['do_search'] = true;
//			$meta['search'] = $meta['search_history'];
//		}
//
//		if ($meta['do_search'])
//			$meta['search'] = $this->search_values;

//
//		if (is_array($meta['valim']))
//		{
//			$sl = get_instance('kliendibaas/selection');
//			$selection = $sl->get_selection($meta['valim']);
//		}
//		else
//		{
//			$nodes = array();
//			$nodes[] = array(
//				"value" => 'vali seadetest valim',
//			);
//			return $nodes;
//		}




///rõvedus!! aga ma tahan et baasist tõesti ei küsitaks asju mida vaja pole
		$tabelid = array(
			'vorm' => 'left join kliendibaas_ettevotlusvorm as vorm on vorm.oid=firma.ettevotlusvorm',
			'juht' => 'left join kliendibaas_isik as juht on juht.oid=firma.firmajuht',
			'f_address' => 'left join kliendibaas_address as f_address on f_address.oid=firma.contact',
			'f_riik' => 'left join kliendibaas_riik as f_riik on f_riik.oid=f_address.riik',
			'f_maakond' => 'left join kliendibaas_maakond as f_maakond on f_maakond.oid=f_address.maakond',
			'f_linn' => 'left join kliendibaas_linn as f_linn on f_linn.oid=f_address.linn',
			'firma'=>' ',
			'tegevusala'=>'left join kliendibaas_tegevusala as tegevusala on tegevusala.oid=firma.pohitegevus',
		);

		load_vcl('table');

		if (!$sele)
		{
			$like_st = '%';
			$like_en = '%';

			if (isset($req['kood']))
			{
				unset($meta['search']);
				unset($meta['search_require']);
				$page = 0;
				$joini['tegevusala']++;
				$meta['search']['tegevusala_kood'] = $req['kood'];
				$like_st = '';
				$like_en = '';
			}
		}

		$t = new aw_table(array(
			'prefix' => 'kliendibaas_manager',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		
		if (!is_array($meta['show_columns']) || (count($meta['show_columns']) < 1)) 
		{
			$meta['show_columns'] = array('firma_nimetus' => 'firma nimi');
		}

		foreach($this->show_columns as $key => $val)
		{

			if (
				!(isset($meta['show_columns'][$key])
				||
				(((isset($meta['do_search']) && $meta['do_search']) || (isset($req['do_search']) && $req['do_search']))  && $meta['search'][$key])
				||((isset($meta['do_search']) && $meta['do_search']) && $meta['search_require'][$key]))
			)
			{
				continue;
			}

			switch($key)
			{
				case 'firma_oid':
					$fields[$key]='objects.oid';
				break;
				case 'kaubamargid':
					$fields[$key]='firma.kaubamargid';
					$joini['firma']++;
				break;
				case 'tegevuse_kirjeldus':
					$fields[$key]='firma.tegevuse_kirjeldus';
					$joini['firma']++;
				break;
				case 'tegevusala_kood':
					$fields[$key]='tegevusala.kood';
					$joini['firma']++;
				break;
				case 'firma_ettevotlusvorm':
					$fields[$key]='vorm.name';
					$joini['vorm']++;
				break;
				case 'pohitegevus':
					$fields[$key]='tegevusala.tegevusala';
					$joini['tegevusala']++;
				break;
				case 'f_aadress':
					$fields[$key]='f_address.aadress';
					$joini['f_address']++;
				break;
				case 'f_riik':
					$fields[$key]='f_riik.name';
					$joini['f_address']++;
					$joini['f_riik']++;
				break;
				case 'f_linn':
					$fields[$key]='f_linn.name';
					$joini['f_address']++;
					$joini['f_linn']++;
				break;
				case 'f_maakond':
					$fields[$key]='f_maakond.name';
					$joini['f_address']++;
					$joini['f_maakond']++;
				break;
				case 'f_postiindeks':
					$fields[$key]='f_address.postiindeks';
					$joini['f_address']++;
				break;
				case 'f_telefon':
					$fields[$key]='f_address.telefon';
					$joini['f_address']++;
				break;
				case 'f_mobiil':
					$fields[$key]='f_address.mobiil';
					$joini['f_address']++;
				break;
				case 'f_faks':
					$fields[$key]='f_address.faks';
					$joini['f_address']++;
				break;
				case 'f_piipar':
					$fields[$key]='f_address.piipar';
					$joini['f_address']++;
				break;
				case 'f_e_mail':
					$fields[$key]='f_address.e_mail';
					$joini['f_address']++;
				break;
				case 'f_kodulehekylg':
					$fields[$key]='f_address.kodulehekylg';
					$joini['f_address']++;
				break;
				case 'firma_juht':
					$fields[$key]='juht.name';
					$joini['juht']++;
				break;
				case 'firma_nimetus':
					$fields[$key]='objects.name';
				break;
				case 'firma_reg_nr':
					$fields[$key]='firma.reg_nr';
					$joini['firma']++;
				break;
			}

			if (!$sele)
			{
				if ($meta['do_search'])
				{

					if ($meta['search'][$key])
					{
						$where.=' and '.$fields[$key]." like '".$like_st.addslashes($meta['search'][$key]).$like_en."' ";
					}

//					if ($meta['search_require'][$key])
//					{
//						$where.=' and '.$fields[$key].' IS NOT NULL and '.$fields[$key]." <> '' ";
//
//					}
				}


//				else
//				{
//					if ($req['do_search'])
//					{
//						if ($meta['search_history'][$req['search_history']][$key])
//						{
//							$where.=' and '.$fields[$key]." like '".$like_st.addslashes($meta['search_history'][$req['search_history']][$key]).$like_en."' ";
//						}
//
//					if ($meta['search_history'][$req['search_history']][$key])
//					{
//						$where.=' and '.$fields[$key].' IS NOT NULL and '.$fields[$key]." <> '' ";
//
//					}
//					}
//				}
				

			}


			if (!isset($meta['show_columns'][$key]))
			{
				unset($fields[$key]);
			}
			else
			{
				$fields[$key].=' as '.$key;
				$t->define_field(array(
					'name' => $key,
					'caption' => $val,
				));
			}
		}
		$s_hist = '';
		if (isset($meta['search_history']) && is_array($meta['search_history']))
		{
			foreach ($meta['search_history'] as $key => $val)
			{
				$s_hist .= html::href(array('caption' => 'otsing',
					'url'=>$this->mk_my_orb('change',
						array(
							'id' => $args['obj'][OID],
							'group' 	=> 'manager',
	//						'page' => $i,
							'do_search' => 1,
							'search_history' => $key,
						)
					)
				)).'<br />';
			}
		}

		$fields[OID] ='firma.oid as oid';

		$t->define_field(array(
			'name' => 'check',
			'caption' => "<a href='javascript:selall(\"sel\")'>Vali</a>",
			'width'=> 15,
		));

		$fields[OID] ='firma.oid as oid';

		foreach($tabelid as $key => $val)
		{
			if (isset($joini[$key]))
			{
				$join[$key]=$val;
			}
		}

		if (!$sele)
		{
			$q='select count(*) as cnt from kliendibaas_firma as firma left join objects on firma.oid=objects.oid '.@implode(' ',$join).$where;
			$cnt = $this->db_fetch_field($q,'cnt');

			$q='select '.implode(',',$fields).' from kliendibaas_firma as firma left join objects on firma.oid=objects.oid '.
			@implode(' ',$join).' '.
			$where.' order by '.$order_by.
			' LIMIT '.($page*$limit).','.((int)$limit);
		}
		elseif (is_array($selection))
		{
			foreach ($selection as $key => $val)
			{
				$oidss[]='firma.oid='.$key;
			}
			$where.=' and ('.implode(' or ',$oidss).') ';
			$cnt = count($selection);
			$q='select '.implode(',',$fields).' from kliendibaas_firma as firma left join objects on firma.oid=objects.oid '.
			@implode(' ',$join).' '.
			$where.' order by '.$order_by.
			' LIMIT '.($page*$limit).','.((int)$limit);
		}
		$data = $this->db_fetch_array($q);

		//define data
		$t=$this->firmad_table($data,isset($selection) ? $selection : array(),$t);

		$navigate=array(
			' |< '=> 0,
			' < ' => abs($page-1),
			' |'.($page+1).'| '=> $page,
			' > ' => ($page+1),
			' >| ' => ((int)($cnt/$limit)),
		);

		$last = ((int)($cnt/$limit));
		$lks = '';
		for ($i = 0; $i <= $last; $i++)
		{
			$lks.=(($i==$page)?' [':'').
			html::href(array('caption'=>'<b>'.($i+1).'</b>',
				'url'=>$this->mk_my_orb('change',
					array(
						'id'=>$args['obj'][OID],
						'group' => 'manager',
						'page' => $i,
					)
				)
			)).(($i==$page)?'] ':'').' ';
			if (($i==10) && ($last>15))
			{
				$lks.=' ... ';
				$i=$last-2;
			}
		}

		$nav = $this->my_buttons($navigate,'page');

//		return $node["value"] = 'total: '.$cnt.'<br />'.$nav.'lk: '.$lks.'<br />'.$t;



		$nodes = array();
		$nodes['firms'] = array(
			"value" => 'total: '.$cnt.'<br />'.$nav.'lk: '.$lks.'<br />'.$t.'<br />'.$s_hist,
		);
		return $nodes;

}
*/

	function search_form($args)
	{
		$this->read_template('search_form.tpl');
		
		$sfield = $args['obj']['meta']['sfield'];
		$exclude = $args['obj']['meta']['exclude'];
		
		$this->vars(array(
			'id' => $id = 'name',
			'value' => htmlentities($sfield[$id]),
			'exclude' => checked($exclude[$id]),
			'caption' => 'Nimi',
		));
		$form = $this->parse('search_field_textbox');
		
		$this->vars(array(
			'id' => $id = 'reg_nr',
			'value' => $sfield[$id],
			'exclude' => checked($exclude[$id]),
			'caption' => 'Reg nr.',
		));
		$form.= $this->parse('search_field_textbox');
		
		$this->vars(array(
			'id' => $id = 'address',
			'value' => htmlentities($sfield[$id]),
			'exclude' => checked($exclude[$id]),
			'caption' => 'Aadress',
		));
		$form.= $this->parse('search_field_textbox');

		$this->vars(array(
			'id' => $id = 'firmajuht',
			'value' => htmlentities($sfield[$id]),
			'exclude' => checked($exclude[$id]),
			'caption' => 'Organisatsiooni juht',
		));
		$form.= $this->parse('search_field_textbox');

		//ettevõtlusvorm
		$id = 'ettevotlusvorm';
		$arr = $this->db_fetch_array('select oid, name from objects where 
			class_id='.CL_ETTEVOTLUSVORM.' and 
			parent'.$this->parent_in($this->got_aliases[ETTEVOTLUSVORM_CAT]). ' order by name'
		);
		$options ='<option value="0"> - kõik - </option>';
		foreach($arr as $val)
		{
			$options.='<option value="'.$val[OID].'" '.(($val[OID]==$sfield[$id])?'selected':'').'>'.$val['name'].'</option>';
		}
		$this->vars(array(
			'id' => $id,
			'options' => $options,
			'exclude' => checked($exclude[$id]),
			'caption' => 'Õiguslik vorm',
		));
		$form.= $this->parse('search_field_select');
		
		//linn
		$id = 'linn';
		$arr = $this->db_fetch_array('select oid, name from objects where 
			class_id='.CL_LINN.' and 
			parent'.$this->parent_in($this->got_aliases[LINN_CAT]). ' order by name'
		);
		$options ='<option value="0"> - kõik - </option>';
		foreach($arr as $val)
		{
			$options.='<option value="'.$val[OID].'" '.(($val[OID]==$sfield[$id])?'selected':'').'>'.$val['name'].'</option>';
		}
		$this->vars(array(
			'id' => $id,
			'options' => $options,
			'exclude' => checked($exclude[$id]),
			'caption' => 'Linn/Vald/Alev',
		));
		$form.= $this->parse('search_field_select');
		
		//maakond
		$id = 'maakond';
		$arr = $this->db_fetch_array('select oid, name from objects where 
			class_id='.CL_MAAKOND.' and 
			parent'.$this->parent_in($this->got_aliases[MAAKOND_CAT]). ' order by name'
		);
		$options ='<option value="0"> - kõik - </option>';
		foreach($arr as $val)
		{
			$options.='<option value="'.$val[OID].'" '.(($val[OID]==$sfield[$id])?'selected':'').'>'.$val['name'].'</option>';
		}
		$this->vars(array(
			'id' => $id,
			'options' => $options,
			'exclude' => checked($exclude[$id]),
			'caption' => 'Maakond',
		));
		$form.= $this->parse('search_field_select');

/*		$this->vars(array(
			'id' => 'reg_nr',
			'value' => '',
			'exclude' => '',
		));
		$name = $this->parse('search_field_textbox');
*/

		$this->vars(array(
			'search_field_textbox' => $form,
		));
		
		
		
		return $this->parse();
	
	}


	function org_toolbar(&$args)
	{
		$toolbar = &$args["prop"]["toolbar"];                
		if ($args['obj'][OID])
                {
			$this->read_template('js_popup_menu.tpl');
			
			$kliendibaas = $this->get_object($args['obj'][OID]);
			//arr($kliendibaas);

$firma_parent = $kliendibaas['meta']['dir_firmad'] ? $kliendibaas['meta']['dir_firmad'] : $kliendibaas['meta']['dir_default'];
			
			$alist = array(
				array('caption' => 'Lisa Firma','class' => 'firma', 'parent' => $firma_parent),
//				array('caption' => 'Lisa tegevusala','class' => 'tegevusala', 'reltype' => TEGEVUSALAD),
				
				//array('caption' => '','class' => '', 'reltype' => ),				
			);
			$menudata = '';
			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					if (!$val['parent']) continue;
					$this->vars(array(
						'link' => $this->mk_my_orb('new',array(
//							'alias_to' => $args['obj']['oid'],
//							'reltype' => $val['reltype'],
							'class' => $val['class'],
							'parent' => $val['parent'],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						)),
						'text' => $val['caption'],
					));

					$menudata .= $this->parse("MENU_ITEM");
				};
			};
			
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


			
			if ($cal_id = aw_global_get('user_calendar'))
			{
				$toolbar->add_button(array(
					"name" => "user_calendar",
					"tooltip" => "Kasutaja kalender",
					"url" => $this->mk_my_orb('change', array('id' => $cal_id),'planner'),
					"onClick" => "",
					"img" => "icon_cal_today.gif",
					"imgover" => "icon_cal_today_over.gif",
					"class" => "menuButton",
				));
				//$user_calendar = $this->get_object($cal_id);
				//$getevent_folder = $user_calendar['meta']['event_folder'];
                		//$toolbar->add_cdata($getevent_folder);
			}

		
                }
				
				
		$toolbar->add_button(array(
			"name" => "org_search",
			"tooltip" => "Otsi organisatsioone",
			"url" => '',
			"onClick" => "document.getElementById('make_search').value = '1';document.forms[0].submit();return false;",
			"img" => "search.gif",
			"imgover" => "search_over.gif",
		));

	
	}	
	

	function teg_toolbar(&$args)
	{
                if ($args['obj'][OID])
                {
			$toolbar = &$args["prop"]["toolbar"];
			$this->read_template('js_popup_menu.tpl');
			$kliendibaas = $this->get_object($args['obj'][OID]);
			//arr($kliendibaas);

$tegevusala_parent = $kliendibaas['meta']['dir_tegevusala'] ? $kliendibaas['meta']['dir_tegevusala'] : $kliendibaas['meta']['dir_default'];
			
			$alist = array(
				array('caption' => 'Lisa Tegevusala','class' => 'tegevusala', 'parent' => $tegevusala_parent),
			);
			$menudata = '';
			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					if (!$val['parent']) continue;
					$this->vars(array(
						'link' => $this->mk_my_orb('new',array(
//							'alias_to' => $args['obj']['oid'],
//							'reltype' => $val['reltype'],
							'class' => $val['class'],
							'parent' => $val['parent'],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						)),
						'text' => $val['caption'],
					));

					$menudata .= $this->parse("MENU_ITEM");
				};
			};
			
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

			if ($cal_id = aw_global_get('user_calendar'))
			{
				$toolbar->add_button(array(
					"name" => "user_calendar",
					"tooltip" => "Kasutaja kalender",
					"url" => $this->mk_my_orb('change', array('id' => $cal_id),'planner'),
					"onClick" => "",
					"img" => "icon_cal_today.gif",
					"imgover" => "icon_cal_today_over.gif",
					"class" => "menuButton",
				));
				//$user_calendar = $this->get_object($cal_id);
				//$getevent_folder = $user_calendar['meta']['event_folder'];
                		//$toolbar->add_cdata($getevent_folder);
			}

		
                };
	}
	
	
/*		
	function search_form_($ob)
	{
		
		$meta=$ob['obj']['meta'];
		load_vcl('table');

		$find = new aw_table(array(
			'prefix' => 'kliendibaas_manager_search',
		));
		$find->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');
			$find->define_field(array(
			'name' => 'attrib',
			'caption' => 'atribuut',
			'width'=> 25,
		));
		$find->define_field(array(
			'name' => 'input',
			'caption' => 'otsingu string',
			'width'=> 100,
		));
		$find->define_field(array(
			'name' => 'require',
			'caption' => "<a href='javascript:selall(\"search_require\")'>peab olemas olema</a>",
			'width'=> 15,
		));

		foreach($this->show_columns as $key => $val)
		{
			$find->define_data(array(
				'attrib' => $val,
				'input' => html::textbox(array('name'=>'search['.$key.']','value' => isset($meta['search'][$key]) ? $meta['search'][$key] : '','size'=>20)),
				'require' => html::checkbox(array('name'=> 'search_require['.$key.']','value'=> 1,'checked'=> isset($meta['search_require'][$key]) ? $meta['search_require'][$key] : false)),
			));
		}

		$submit=html::button(array('value'=>'otsi','onclick'=>'document.changeform.do_search.value=1;document.changeform.submit()'));

		return '<table width=200><tr><td>'.$find->draw().'</td></tr></table>'.$submit.'<br />';
	}
*/

/*
	function firmad_table($data,$selection,$t)
	{
		$arr = new aw_array($data);
		foreach($arr->get() as $row)
		{
			$row['check'] = html::checkbox(array('name'=>'sel['.$row[OID].']','checked' => isset($selection[$row[OID]]) ? $selection[$row[OID]] : false,'value' => $row[OID]));
			$row['check'].= html::hidden(array('name'=>'objs['.$row[OID].']' ,'value'=>1));


			if (isset($row['firma_nimetus']))
			{
				$row['firma_nimetus'] = html::href(array('caption'=>$row['firma_nimetus'],'url'=>$this->mk_my_orb('change',array('id'=>$row[OID]),'kliendibaas/firma')));
			}
			if (isset($row['f_kodulehekylg']))
			{
				$row['f_kodulehekylg']=html::href(array('url'=> ((strpos($row['f_kodulehekylg'],'://')===false) ? 'http://' : '').$row['f_kodulehekylg'],'caption'=> $row['f_kodulehekylg'], 'target' => '_blank' ));
			}
			if (isset($row['f_e_mail']))
			{
				$row['f_e_mail']=html::href(array('url'=>'mailto:'.$row['f_e_mail'],'caption'=> $row['f_e_mail']));
			}

			$t->define_data(
				$row
			);
		}
		return $t->draw();
	}

	function my_buttons($button,$page)
	{
		$nav = '';
		foreach($button as $key => $val)
		{
			$nav.=html::button(array('value'=>$key,'onclick'=>'document.changeform.'.$page.'.value='.$val.';document.changeform.submit()'));
		}
		return $nav;
	}
*/
/*
	////
	// ! create new contact entry
	// contact		at least one element required here
	//	name
	//	riik
	//	linn
	//	...
	// name
	// parent
	// comment
	// ...
	function new_address($arr,$tyyp,$class_id)
	{
		extract($arr);

		print_r($arr);die();
	//		echo $parent.'sfdgs';
			foreach ($datas as $key=>$val)
			{
				{
					$this->quote($val);
					$f[]=$key;
					$v[]="'".$val."'";
				}
			}

			$id = $this->new_object(array(
				"name" => $name,
				"parent" => $parent,
				"class_id" => $class_id,
				"comment" => $comment,
				"metadata" => array(
//					"contact"=>$contact,
				)
			));
			$f[]=OID;
			$v[]="'".$id."'";

			$q='insert into kliendibaas_'.$tyyp.'('.implode(",",$f).')values('.implode(",",$v).')';

		$this->db_query($q);
		return $id;
	}
*/
/*
	function owerview($args)
	{
		$arr=array(
			CL_LINN => 'linn',
			CL_RIIK => 'riik',
			CL_MAAKOND => 'maakond',
			CL_TEGEVUSALA => 'tegevusala',
			CL_TOODE => 'toode',
			CL_FIRMA => 'firma',
			CL_ETTEVOTLUSVORM => 'ettevõtlusvorm',
//			'' => '',
//			'' => '',
		);

		$nodes = array();
		$nodes['overv'] = array(
			"value" => 'TEST',
		);
		return $nodes;
	}
*/


	
		
/*

			$q = 'select oid,kood from kliendibaas_tegevusala where length(kood)=5';
			$data = $this->db_fetch_array($q);
			foreach($data as $val)
			{
					$k='0'.$val['kood'];
				echo			$q = "update kliendibaas_tegevusala set kood='".$k."' where oid=".$val[OID];
//				$this->db_query($q);

			}


/*

$arr=$this->db_fetch_array('select kood1,kood from html_import_tegevusalad');

foreach ($arr as $key => $val)
{
	if (($val['kood1'] && $val['kood']) && ($val['kood1']<>$val['kood']))
	{
		$this->db_query("update kliendibaas_tegevusala set kood='".$val['kood']."' where kood='".$val['kood1']."'");
//		echo $val['kood1'].'=>'.$val['kood'].'<br />';
	}
}

die('done');


//alter table kliendibaas_fimra change column pohitegevus pohitegevus varchar(20);
/*
		$arr=$this->db_fetch_array('
		select t2.pohitegevus as oih, t1.oid as fioid  from kliendibaas_firma as t1, html_import_firmad as t2
		where t1.reg_nr=t2.reg_nr

		');

/*
		$arr=$this->db_fetch_array('
			select t2.oid as fioid, t1.oid as oih  from kliendibaas_tegevusala as t1,kliendibaas_firma as t2
			where t1.kood=t2.pohitegevus


		 ');

foreach ($arr as $row)
{
$q="update kliendibaas_firma set pohitegevus='".$row['oih']."' where oid=".$row['fioid'];
$this->db_query($q);
}
		print_r($arr);
		die();
*/

/*		$arr=array(
			'linn' => 'linn',
			'riik' => 'riik',
			'maakond' => 'maakond',
			'tegevusala' => 'tegevusala',
			'toode' => 'toode',
			'firma' => 'firma',
			'ettevotlusvorm' => 'ettevõtlusvorm',
//			'' => '',
//			'' => '',
		);


*/


	/*function objects_manager($args)
	{
		$arr = array(
			'linn' => 'linn',
			'riik' => 'riik',
			'maakond' => 'maakond',
			'tegevusala' => 'tegevusala',
			'toode' => 'toode',
			'firma' => 'firma',
			'ettevotlusvorm' => 'ettevõtlusvorm',
//			'' => '',
//			'' => '',
		);

		foreach ($arr as $key => $value)
		{
			$k = $this->mk_my_orb('new', array('parent' => $args['obj']['parent']),$key);
			$arr2[$k] = $value;
		}
		$str .= 'lisa objekt ';
		$str .= html::select(array('name' => 'mk_new', 'options' => $arr2, 'onchange' =>
			"window.open(document.changeform.mk_new.value,'new object','')"
		));

		$nodes = array();
		$nodes['obj'] = array(
			"value" => $str,
		);
		return $nodes;
	}*/

	/*	function callback_obj_list_($args)
	{
		$arg2['obj'][OID] = isset($args['obj']['meta']['active_selection']) ? 
			$args['obj']['meta']['active_selection'] : $args['obj'][OID];
		$arg2['obj']['parent'] = $args['obj']['parent'];
		$arg2['obj']['meta']['active_selection'] = $arg2['obj'][OID] ? $arg2['obj'][OID] : $args['obj']['meta']['selections'][0];
		$arg2['sel']['oid'] = $args['obj'][OID];
		return $dat = $this->obj_list($arg2);
	}	*/

}
?>
