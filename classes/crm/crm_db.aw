<?php

/*
	@classinfo relationmgr=yes
	@default table=objects
	@default group=general

	@default field=meta
	@default method=serialize

	@property selections type=relpicker reltype=RELTYPE_SELECTIONS group=general
	@caption Vaikimisi valim

	@default group=firmad
	@groupinfo firmad submit=no caption=Organisatsioonid
			
	@property orgtoolbar type=toolbar store=no no_caption=1
	
	@property search_form type=text no_caption=1
	@caption otsing
	
	@property sfield type=textbox
	@property exclude type=textbox
		
	@property manageri type=text callback=firma_manager
	
	//valimi nupud	
	@property selection_manage_buttons type=text callback=selection_manage_bar
	
	@property make_search type=textbox size=1
	@property search_type type=textbox size=1
	
////////////////////////////////////////////////////////////

	@default group=tegevusalad
	@groupinfo tegevusalad submit=no caption=Tegevusalad 

	@property tegtoolbar type=toolbar store=no no_caption=1	
	
	@property tegevusala_manager type=text callback=tegevusala_manager
	
	//valimi nupud	
	@property selection_manage_buttons2 type=text callback=selection_manage_bar
	

////////////////////////////////////////////////////////////
	@default group=settings
	@groupinfo settings caption=Seaded

	@property dir_firma type=relpicker reltype=RELTYPE_FIRMA_CAT
	@caption Vaikimisi firmade kataloog

	@property dir_isik type=relpicker reltype=RELTYPE_ISIK_CAT
	@caption Vaikimisi töötajate kataloog

	@property dir_address type=relpicker reltype=RELTYPE_ADDRESS_CAT
	@caption Vaikimisi aadresside kataloog

	@property dir_ettevotlusvorm type=relpicker reltype=RELTYPE_ETTEVOTLUSVORM_CAT
	@caption Vaikimisi õiguslike vormide kataloog

	@property dir_linn type=relpicker reltype=RELTYPE_LINN_CAT
	@caption Vaikimisi linnade kataloog

	@property dir_maakond type=relpicker reltype=RELTYPE_MAAKOND_CAT
	@caption Vaikimisi maakondade kataloog

//	@property dir_riik type=relpicker reltype=RELTYPE_RIIK_CAT
//	@caption riikide kataloog(id)

	@property dir_tegevusala type=relpicker reltype=RELTYPE_TEGEVUSALA_CAT
	@caption Vaikimisi tegevusalade kataloog

	@property dir_toode type=relpicker reltype=RELTYPE_TOODE_CAT
	@caption Vaikimisi toodete kataloog

	@property dir_default type=relpicker reltype=RELTYPE_GENERAL_CAT
	@caption Vaikimisi kataloog, kui mõni eelnevatest pole määratud, siis kasutatakse seda

	@property kone_form type=relpicker reltype=RELTYPE_FORMS
	@caption Kõne sisestusvorm

	@property pakkumine_form type=relpicker reltype=RELTYPE_FORMS
	@caption Pakkumise sisestusvorm
		
	@property tehing_form type=relpicker reltype=RELTYPE_FORMS
	@caption Tehingu sisestusvorm
	
	@property kohtumine_form type=relpicker reltype=RELTYPE_FORMS
	@caption Kohtumise sisestusvorm
	
	@property default_form type=relpicker reltype=RELTYPE_FORMS
	@caption Vaikimisi sisestusvorm
	
//	@property where_firm type=checkbox ch_value=on
//	@caption näita ainult tegevusalasid, kus alal on ka ettevõtteid

	@property flimit type=select
	@caption Kirjeid ühel lehel
	
	@property default_kliendibaas type=checkbox 
	@caption See on kasutaja default kliendibaas


	@default group=objects_manager
	@groupinfo objects_manager caption=Objektide&nbsp;lisamine submit=no

	@property addtoolbar type=toolbar store=no no_caption=1
	
		
	//@property objects_manager type=text callback=objects_manager

	
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

/*
@reltype SELECTIONS value=1 clid=CL_CRM_SELECTION
@caption Valimid

@reltype FIRMA_CAT value=2 clid=CL_MENU
@caption Organisatsioonide kataloogid

@reltype ISIK_CAT value=3 clid=CL_MENU
@caption Töötajate kataloogid

@reltype ADDRESS_CAT value=4 clid=CL_MENU
@caption Aadresside kataloogid

@reltype LINN_CAT value=5 clid=CL_MENU
@caption Linnade kataloogid

@reltype MAAKOND_CAT value=6 clid=CL_MENU
@caption Maakondade kataloogid

@reltype RIIK_CAT value=7 clid=CL_MENU
@caption Riikide kataloogid

@reltpye TEGEVUSALA_CAT value=8 clid=CL_MENU
@caption Tegevusalade kataloogid

@reltype TOODE_CAT value=9 clid=CL_MENU
@caption Toodete kataloogid

@reltype GENERAL_CAT value=10 clid=CL_MENU
@caption Üldkataloog

@reltype CALENDAR value=11 clid=CL_PLANNER
@caption Kalender

@reltype ETTEVOTLUSVORM_CAT value=12 clid=CL_MENU
@caption Õiguslike vormide kataloogid

@reltype FORMS  value=13 clid=CL_CFGFORM
@caption Sisestusvormid

// this seems to be the same as the first one
reltype SELECTIONS_RELTYPE value=14



*/
//define('SELECTIONS_RELTYPE',SELECTIONS);
			
//pakkumise sisestamise vorm
//kõne sisestamise vorm
//jne

class crm_db extends class_base
{
	//var $show_columns;
	var $selections_reltype;
	

	function crm_db()
	{
		$this->init(array(
			'clid' => CL_CRM_DB,
			'tpldir' => 'kliendibaas',
		));
		$this->selections_reltype = SELECTIONS_RELTYPE;
	}	
		
//// valim///
/* ühesõnaga valimi klassiga näitame valimeid ja manageerime neid
põhimõtteliselt seda valimi tabi ei olegi vaja siin näidata
*/

	function callback_obj_list($args)
	{
		classload('crm/crm_selection');
		$arg2['obj'][OID] = $args['obj']['meta']['active_selection'];
		$arg2['obj']['parent'] = $args['obj']['parent'];
		$arg2['obj']['meta']['active_selection'] = $args['obj']['meta']['active_selection'];
		$arg2['sel']['oid'] = $args['obj'][OID];	
		if (!is_object($this->selection_object))
		{
			$this->selection_object = new crm_selection();
		};
		return $this->selection_object->obj_list($arg2);
	}
	
	function selection_manage_bar($args = array())
	{
		$nodes = array();
		if (!is_object($this->selection_object) && method_exists($this,'callback_obj_list'))
		{
			classload('crm/crm_selection');
			$this->selection_object = new crm_selection();
			$this->selection = $args['obj'];
		}
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


	function get_property(&$args)
	{

		$data = &$args['prop'];
		$retval = PROP_OK;
		$meta = &$args['obj']['meta'];
		$req = &$args['request'];
		
		//// valim///
		/* loome valimi instansi kui seda juba tehtud pole */
		if (!is_object($this->selection_object) && method_exists($this,'callback_obj_list'))
		{
			classload('crm/crm_selection');
			$this->selection_object = new crm_selection();
			$this->selection = $args['obj'];
		}

		// so, loeme sisse kõik selle objekti aliases ja jaotame nad tüübi järgi ära, jees
		if (!is_array($this->got_aliases))
		{
			$conns = $args["obj_inst"]->connections_from();
			foreach($conns as $conn)
			{
				$this->got_aliases[$conn->prop("reltype")][] = $conn->prop("to");
			};
		}

		switch($data["name"])
		{
			//// valim///
			/* ühesõnaga see on hidden element, meil on vaja et ta metas salvestuks */
			case 'active_selection':
				$retval=PROP_IGNORE;
				break;
				


			case 'selection_manage_buttons':
			
				$make_search = ($args['obj']['meta']['make_search'] && !$args['request']['no_search']) ? true : false;
				if (($args['obj']['meta']['search_type'] && !$make_search))
				{
					$retval=PROP_IGNORE;
				}
				break;		
		
						
			
			case 'default_kliendibaas':
				$this->users = get_instance("users");
                                $obj_id = $args["obj_inst"]->id();

                                $data['value'] = $this->users->get_user_config(array(
                                        "uid" => aw_global_get("uid"),
                                        "key" => "kliendibaas",
                                ));
                                $data['ch_value'] = $args["obj_inst"]->id();
				break;

			
			case 'orgtoolbar':
				$this->org_toolbar($args);
				break;

			case 'tegtoolbar':
				$this->teg_toolbar($args);
				break;

			case 'addtoolbar':
				$this->add_toolbar($args);
				break;

			case 'flimit':
				$data['options'] = array ('10' => '10', '20' => '20', '30' => '30');
				break;

			case 'pagelimit':
				$data['options'] = array ('10' => '10', '20' => '20', '30' => '30');
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

				$args['obj']['oid']['meta']['make_search'] = '0';
				$retval=PROP_IGNORE;
				
				break;

			case 'search_type':
				$args['obj']['oid']['meta']['search_type'] = '0';
				$retval=PROP_IGNORE;
				break;
						
			case 'select':
				$retval=PROP_IGNORE;
				break;

			case 'objs':
				$retval=PROP_IGNORE;
				break;

		}
		return  $retval;
	}


	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$retval = PROP_OK;
		switch($data['name'])
		{
		
			case 'sfield':
				if (!$form['sfield'])
				{
					$retval = PROP_IGNORE;
				};
				break;
			
			case 'default_kliendibaas':
				$users = get_instance("users");
                                $users->set_user_config(array(
                                        "uid" => aw_global_get("uid"),
                                        "key" => "kliendibaas",
                                        "value" => $data["value"],
                                ));
				break;

		};
		return $retval;
	}

	
	function tegevusala_manager($args)
	{
	
		$tase = $args['request']['tase'] ? $args['request']['tase'] : 1;
		$kood = $args['request']['kood'] ? $args['request']['kood'] : '0';
		$teg_oid = $args['request']['teg_oid'] ? $args['request']['teg_oid'] : 0;
		
		$fpage = $args['request']['tpage'] ? $args['request']['tpage'] : '1';
		$flimit = $args['obj']['meta']['tlimit'] ? $args['obj']['meta']['tlimit'] : 20;		
		
		$tase = ($tase>3)?3:$tase;

		//arr($args);

		$limit = 100; // siia vaja ka aretada leheküljed //axel 

	
		
		$teg_parent = ' t1.parent'.$this->parent_in($this->got_aliases[TEGEVUSALA_CAT]).' and ';
			
		if ($tase == 1)
		{
				$arr = $this->db_fetch_array('
				select t1.oid as oid, t1.name as name, t2.kood as kood from objects t1 left join kliendibaas_tegevusala t2 on t1.oid=t2.oid
				where '.$teg_parent.' 
				t1.status>0 and class_id='.CL_CRM_SECTOR.' and
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
				t1.status>0 and class_id='.CL_CRM_SECTOR.' and
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
			t1.status>0 and class_id='.CL_CRM_SECTOR.' and
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

		$t->define_field(array(
			'name' => 'tegevusala',
			'caption' => 'Tegevusala',
		));

		$t->define_field(array(
			'name' => 'fcount',
			'caption' => 'Organisatsioone',
		));
		
		$t->define_field(array(
			'name' => 'check',
			'caption' => "<a href='javascript:selall(\"sel\")'>Vali</a>",
			'width'=> 15,
		));
		
		
		//mk yah bar
		$yah = array();
		$code=$kood;
		while($code>=1)
		{
		
			$yah[] = $this->db_fetch_row('select t1.oid as oid,t2.kood as kood, t1.name as name from objects as t1 left join kliendibaas_tegevusala as t2 
			on t1.oid=t2.oid
			where t2.kood="'.$code.'"');
			
			
			$code = substr($code, 0, -1);
		}
		$yahbar = '';
		foreach($yah as $val)
		{
			if ($val[OID])
				$yahbar = $val['name'].' / '.$yahbar;
		
		}
	
		if (is_array($arr))
		foreach($arr as $val)
		{
			$code = $val['kood'];
			$cnt = $this->db_fetch_field('select count(*) as cnt from aliases as t1 left join objects as t2 on t1.target=t2.oid 
				left join objects as t3 on t1.source=t3.oid		
				where t1.target="'.$val[OID].'" and t1.reltype=5 and t1.source<>0 and t2.status=1
				and t3.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
				','cnt');

			$t->define_data(
				array(
					'tegevusala' => '<a href="'.$this->mk_my_orb('change', array(
				'id' => $args['obj'][OID],
				'group' => 'tegevusalad',
				'tase' => ($tase + 1),
				'kood' => $val['kood'],
				'teg_oid' => $val[OID],
				)).'">'.$val['name'],'</a>',
				'fcount' => ($cnn ? ($cnn.' / '):'').$cnt,
				)
			);
		}
		
		$teg_obj = new object($teg_oid);
		// right, I have a teg_obj, now I need to create a list of connected objects
		if (!empty($teg_oid))
		{
			$conns = $teg_obj->connections_to(array(
				"reltype" => 5,
			));
		}
		else
		{
			$conns = array();
		};
		$tf = $this->init_firmad_table();
		foreach($conns as $conn)
		{
			$obj = $conn->from();
			$addr_obj = new object($obj->prop('contact'));
			$url_obj = new object($addr_obj->prop("kodulehekylg"));
			$kodulehekylg = $url_obj->prop("url");
			$email_obj = new object($addr_obj->prop("e_mail"));
			$county_obj = new object($addr_obj->prop("maakond"));
			$linn_id = $addr_obj->prop("linn");
			$corpform = new object($obj->prop("ettevotlusvorm"));
			$sector_obj = new object($obj->prop("pohitegevus"));
			$telefon = new object($addr_obj->prop("telefon"));
			$juht_obj_id = $obj->prop('firmajuht');
			if ($juht_obj_id)
			{
				$juht_obj = new object($juht_obj_id);
				$juht_name = $juht_obj->name();
			}
			else
			{
				$juht_name = "";
			};

			if ($linn_id)
			{
				$city_obj = new object($addr_obj->prop("linn"));
				$cityname = $city_obj->name();
			}
			else
			{
				$cityname = "";
			};
			$tf->define_data(array(
				'fname' => html::href(array(
					'url' => $this->mk_my_orb('change',array(
						'id' => $obj->id(),
						'return_url' => urlencode(aw_global_get('REQUEST_URI')),
					), 'crm/crm_company'),
					'caption' => $conn->prop("from.name"),
				)),
				'check' => $check,
				'reg_nr' => $obj->prop('reg_nr'),
				'ettevotlusvorm' => $corpform->name(),
				'full_address' => $obj->prop('full_address'),
				'address' => $addr_obj->name(),
				'firmajuht' => 	html::href(array(
					'url' => $this->mk_my_orb('change',array(
						'id' => $juht_obj_id,
						'return_url' => urlencode(aw_global_get('REQUEST_URI')),
					), 'crm/crm_person'),
					'caption' => $juht_name,
				)),
				'linn' => $cityname,
				'maakond' => $county_obj->name(),
				'e_mail' => $email_obj->name(),
				'kodulehekylg' => html::href(array('url' => $kodulehekylg,'caption' => $kodulehekylg,'target' => '_blank')),
				'telefon' => $telefon->name(),
				'pohitegevus' => $sector_obj->name(),
			));
		};

				
		$firmad = $tf->draw();
		$nodes = array();
		$nodes['teg'] = array(
			"name" => "teg",
			"type" => "text",
			"no_caption" => 1,
			"value" => '<b>'.$yahbar.'</b>'.$t->draw().$firmad,
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

	
	function firma_manager($args)
	{
		$letter = $args['request']['letter'] ? $args['request']['letter'] : 'A';
		$fpage = $args['request']['fpage'] ? $args['request']['fpage'] : '1';
		$flimit = $args['obj']['meta']['flimit'] ? $args['obj']['meta']['flimit'] : 20;
		$letters = '';
		$pages = '<style> BUTTON {height:23px;spacing:0px;padding:0px;}</style>';
		$showpagenr = array();

		// I need to be able to generate search forms from property definitions.
		
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

		// kuidas faking moodi ma selle päringu kokku pean nüüd panema sinu arvates, ah?
		
		if ($make_search)
		{	
			$search_params = '';
			$exclude = $args['obj']['meta']['exclude'];
			$sfield = $args['obj']['meta']['sfield'];

			if ($sfield[$id = 'name'])
			{
				$not = $exclude[$id]? 'not' : '';
				
				$strs = explode(',',$sfield[$id]);
				$strq = array();
				foreach($strs as $val)
				{
					$strq[] = 't1.'.$id.' '.$not.' like ("%'.addslashes(trim($val)).'%") ';
				}
				$search_params .= ' and ('.implode(' and ',$strq).') ';
			}

			if ($sfield[$id = 'not_name'])
			{
				$not = $exclude[$id]? 'not' : '';
				
				$strs = explode(',',$sfield[$id]);
				$id = 'name';
				$strq = array();
				foreach($strs as $val)
				{
					$strq[] = 't1.'.$id.'  like ("%'.addslashes(trim($val)).'%") ';
				}
				$search_params .= ' and not ('.implode(' or ',$strq).') ';
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
				$strs = explode(',',$sfield[$id]);
				$strq = array();
				foreach($strs as $val)
				{
					$strq[] = 't4.name '.$not.' like ("%'.addslashes(trim($val)).'%") ';
				}
				$search_params .= ' and ('.implode(' and ',$strq).') ';
			}

			if ($sfield[$id = 'not_address'])
			{
				$strs = explode(',',$sfield[$id]);
				$strq = array();
				foreach($strs as $val)
				{
					$strq[] = 't4.name  like ("%'.addslashes(trim($val)).'%") ';
				}
				$search_params .= ' and not ('.implode(' or ',$strq).') ';
			}
						
			
			
			
			if ($sfield[$id = 'firmajuht'])
			{
				$not = $exclude[$id]? 'not' : '';
				$search_params .= ' and t5.name '.$not.' like ("%'.addslashes($sfield[$id]).'%") ';
			}
			
			$cnt = $this->db_fetch_field('select count(*) as cnt from objects as t1 
				'.implode(' ',$join_tables).'
				where 
				t1.class_id='.CL_CRM_COMPANY.' and t1.status<>0 and
				t1.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
				'.$search_params.'
				','cnt');
			
		}
		elseif (!$args['obj']['meta']['search_type'])
		{
			$cnt = $this->db_fetch_field('
			select count(*) as cnt from objects as t1
			where 
			t1.class_id='.CL_CRM_COMPANY.' and t1.status<>0 and
			t1.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
			and t1.name like ("'.$letter.'%")
			','cnt');
		}
		
		if ($cnt)
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
		
		$pages.=' ('.$cnt.')';
		
		$limit = 'limit '.(($fpage-1) * $flimit).','.$flimit;
		
		
		
		
		$select_fields = 't1.*,t2.reg_nr,t3.name as ettevotlusvorm, t4.name as full_address, t5.name as firmajuht,t5.oid as firmajuht_oid,
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
			t1.class_id='.CL_CRM_COMPANY.' and t1.status<>0 and
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
			t1.class_id='.CL_CRM_COMPANY.' and t1.status<>0 and
			t1.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
			and t1.name like ("'.$letter.'%")
			order by t1.name
			'.$limit.'
			';
			
		}


		if ($args['obj']['meta']['search_type'] && !$make_search)
		{
				
		}
		else
		{
				$arr = $this->db_fetch_array($q);
				$firmad = $this->firmad_table($arr);
				
		}

		// sellest sitast tuleb ju ikka ka tabeligeneka featuur teha. geezas christ and mother of god
		// but how do I implement it in there?


		// mis kuradi nimede esitähed?
		if (!$args['obj']['meta']['search_type'])
		{
			$all_letters = $this->db_fetch_array('select substring(name,1,1) as letter from objects 
			where class_id='.CL_CRM_COMPANY.' and status<>0 and parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).' 
			group by substring(name,1,1)
			order by substring(name,1,1)
			limit 50
			'
			);
		}
		
		
		
		//if (!$args['obj']['meta']['search_type'] && is_array($arr))
		if(!$make_search && is_array($all_letters))
		foreach($all_letters as $val)
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
			
		
			if ($val["letter"])
			{
				$letters.='<button style="width:21px" onclick="document.location='.$uri.';return false;">'.
				(($val['letter']==$letter) ? '<b><u>'.$val['letter'].'</u></b>' : $val['letter']).
				'</button>';
			};
		
		}

	//arr($arr);

		//echo count($arr);
		
		
				
		$nodes = array();
		$nodes['teg'] = array(
			"no_caption" => 1,
			"name" => "teg",
			"type" => "text",
			"value" => $letters.'<br />'.$pages.$firmad.'',
		);

		return $nodes;
	
	}
	
	function init_firmad_table()
	{
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
			'caption' => 'Põhitegevus',
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'ettevotlusvorm',
			'caption' => 'Õiguslik vorm',
			'sortable' => '1',
		));
		
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
		return $tf;
	}

	function firmad_table($arr)
	{
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
						'url' => $this->mk_my_orb('change',array(
							'id' => $val[OID],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						), 'crm/crm_company'),
						'caption' => $val['name'],
					)),
					'check' => $check,
					'reg_nr' => $val['reg_nr'],
					'ettevotlusvorm' => $val['ettevotlusvorm'],
					'full_address' => $val['full_address'],
					'address' => $val['address'],
					'firmajuht' => 	html::href(array(
						'url' => $this->mk_my_orb('change',array(
							'id' => $val['firmajuht_oid'],
							'return_url' => urlencode(aw_global_get('REQUEST_URI')),
						), 'crm/crm_person'),
						'caption' => $val['firmajuht'],
					)),
					'linn' => $val['linn'],
					'maakond' => $val['maakond'],
					'e_mail' => $val['e_mail'],
					'kodulehekylg' => html::href(array('url' => $val['kodulehekylg'],'caption' => $val['kodulehekylg'],'target' => '_blank')),
					'telefon' => $val['telefon'],
					'pohitegevus' => $val['pohitegevus'],
				)
			);
		}

		$tf->sort_by();
		$rv = $tf->draw();
		return $rv;
	}	

	function search_form($args)
	{
		if (!$args['obj']['meta']['search_type'])
		{
			$this->read_template('search_default.tpl');
			return $this->parse();
		}
		
		$search_template = 'search_'.$args['obj']['meta']['search_type'].'.tpl';
		$form = '';
	
		$this->read_template($search_template);
		
		$sfield = $args['obj']['meta']['sfield'];
		$exclude = $args['obj']['meta']['exclude'];
		
		$this->vars(array(
			'id' => $id = 'name',
			'value' => htmlentities($sfield[$id]),
			'exclude' => checked($exclude[$id]),
			'caption' => 'Nimi',
		));
		$form .= $this->parse('search_field_textbox');
		
		$this->vars(array(
			'id' => $id = 'not_name',
			'value' => htmlentities($sfield[$id]),
			'exclude' => checked($exclude[$id]),
			'caption' => ' ei sisalda ',
			'br' => '<br />',
		));
		$form .= $this->parse('search_field_textbox');
		
		$this->vars(array(
			'id' => $id = 'reg_nr',
			'value' => $sfield[$id],
			'exclude' => checked($exclude[$id]),
			'caption' => 'Reg nr.',
			'br' => '<br />',
		));
		$form.= $this->parse('search_field_textbox');
		
		$this->vars(array(
			'id' => $id = 'address',
			'value' => htmlentities($sfield[$id]),
			'exclude' => checked($exclude[$id]),
			'caption' => 'Aadress',
			'br' => '',
		));
		$form.= $this->parse('search_field_textbox');

		$this->vars(array(
			'id' => $id = 'not_address',
			'value' => htmlentities($sfield[$id]),
			'exclude' => checked($exclude[$id]),
			'caption' => 'ei sisalda',
			'br' => '<br />',
		));
		$form.= $this->parse('search_field_textbox');
		
		
		$this->vars(array(
			'id' => $id = 'firmajuht',
			'value' => htmlentities($sfield[$id]),
			'exclude' => checked($exclude[$id]),
			'caption' => 'Organisatsiooni juht',
			'br' => '<br />',
		));
		$form.= $this->parse('search_field_textbox');

		//ettevõtlusvorm
		$id = 'ettevotlusvorm';
		$arr = $this->db_fetch_array('select oid, name from objects where 
			class_id='.CL_CRM_CORPFORM.' and 
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
			class_id='.CL_CRM_CITY.' and 
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
			class_id='.CL_CRM_COUNTY.' and 
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
		if (empty($args["new"]))
                {
			$crm_db = $args["obj_inst"];

			$parents[CL_CRM_COMPANY] = $crm_db->prop("dir_firma") == "" ? $crm_db->prop("dir_default") : $crm_db->prop("dir_firma");

			$toolbar->add_menu_button(array(
				"name" => "create_event",
				"tooltip" => "Uus",
			));

			$alist = array(
				array('clid' => CL_CRM_COMPANY),
			);
			$menudata = '';
			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					$classinf = $this->cfg["classes"][$val["clid"]];
					if (!$parents[$val['clid']])
					{
						$toolbar->add_menu_item(array(
							"parent" => "create_event",
							'title' => 'Kaust määramata',
							'text' => 'Lisa '.$classinf["name"],
							'disabled' => true,
						));
					}
					else
					{
						$toolbar->add_menu_item(array(
							"parent" => "create_event",
							'link' => $this->mk_my_orb('new',array(
								'class' => basename($classinf["file"]),
								'parent' => $parents[$val['clid']],
								'return_url' => urlencode(aw_global_get('REQUEST_URI')),
							)),
							'text' => 'Lisa '.$classinf["name"],
						));
					}
				};
			};
			
			$users = get_instance("users");
			$cal_id = $users->get_user_config(array(
				"uid" => aw_global_get("uid"),
				"key" => "user_calendar",
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
                }
				
			

		$toolbar->add_menu_button(array(
			"name" => "search_event",
			"tooltip" => "otsi organisatsioone",
			"img" => "search.gif",
		));

		$stype = array(
			'org' => 'Otsi organisatsioone', 
			'isik' => 'Otsi isikuid',
		);
		if (is_array($stype))
		{
			foreach($stype as $key => $val)
			{
				$toolbar->add_menu_item(array(
					"parent" => "search_event",
					'link' => aw_global_get('REQUEST_URI'),
					'text' => $val,
					'onClick' => "document.getElementById('search_type').value = '".$key."';document.forms[0].submit();return false;"
				));
			};
		};
			
		$toolbar->add_button(array(
			"name" => "list_only",
			"tooltip" => "Organisatsioonide nimekiri",
			'onClick' => "document.getElementById('search_type').value = '0';document.forms[0].submit();return false;",
			"url" => '',
			"img" => "prog_42.gif",
		));
		
	
	}	
	function add_toolbar(&$args)
	{
		$toolbar = &$args["prop"]["toolbar"];                
		if (empty($args["new"]))
                {
			$crm_db = $args["obj_inst"];
			
			$par = array("firma","isik","toode","tegevusala","linn","maakond",
				"amet","ettevotlusvorm","aadress");
			
			$toolbar->add_menu_button(array(
                                "name" => "add_item",
                                "tooltip" => "Lisa uus objekt",
                        ));

			foreach($par as $val)
			{
				$parents[$key] = $crm_db->prop("dir_".$val) ? $kliendibaas['meta']['dir_'.$val] : $kliendibaas['meta']['dir_default'];
			}

			$alist = array(
				array('class_id' => CL_CRM_COMPANY),
				array('class_id' => CL_CRM_PERSON),
				array('class_id' => CL_CRM_CORPFORM),
				array('class_id' => CL_CRM_PRODUCT),
				array('class_id' => CL_CRM_SECTOR),
				array('class_id' => CL_CRM_CITY),
				array('class_id' => CL_CRM_COUNTY),
				array('class_id' => CL_CRM_PROFESSION),
				array('class_id' => CL_CRM_DB),

			);
			$menudata = '';
			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					$classinf = $this->cfg["classes"][$val["class_id"]];
					if (!$parents[$val['class_id']])
					{
						$toolbar->add_menu_item(array(
							"parent" => "add_item",
							'title' => $classinf['name'].' kaust määramata',
							'text' => 'Lisa '.$classinf['name'],
							"disabled" => true,
						));
						$menudata .= $this->parse("MENU_ITEM_DISABLED");
					}
					else
					{
						$toolbar->add_menu_item(array(
							"parent" => "add_item",
							'link' => $this->mk_my_orb('new',array(
								'class' => basename($classinf["file"]),
								'parent' => $parents[$val['class_id']],
								'return_url' => urlencode(aw_global_get('REQUEST_URI')),
							)),
							'text' => $classinf["name"],
						));

					}
				};
			};
                }
	
	}	
	

	function teg_toolbar(&$args)
	{
                if (empty($args["new"]))
                {
			$toolbar = &$args["prop"]["toolbar"];
			$crm_db = $args["obj_inst"];

			$parents[CL_CRM_SECTOR] = $crm_db->prop('dir_tegevusala') == "" ? $crm_db->prop("dir_default") : $crm_db->prop("dir_tegevusala");
			
			$alist = array(
				array('clid' => CL_CRM_SECTOR),
			);

			$toolbar->add_menu_button(array(
				"name" => "add_item",
				"tooltip" => "Lisa",
			));

			$menudata = '';
			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					$classinf = $this->cfg["classes"][$val["clid"]];
					if (!$parents[$val['clid']])
					{
						$toolbar->add_menu_item(array(
							"parent" => "add_item",
							'title' => 'Kaust määramata',
							'text' => 'Lisa '.$classinf["name"],
						));
					}
					else
					{
						$toolbar->add_menu_item(array(
							"parent" => "add_item",
							'link' => $this->mk_my_orb('new',array(
								'class' => basename($classinf["file"]),
								'parent' => $parents[$val['clid']],
								'return_url' => urlencode(aw_global_get('REQUEST_URI')),
							)),
							'text' => 'Lisa '.$classinf['name'],
						));
					}
				};
			};
		
			$users = get_instance("users");
			$cal_id = $users->get_user_config(array(
				"uid" => aw_global_get("uid"),
				"key" => "user_calendar",
                	));

			if (!empty($cal_id))
			{
				$toolbar->add_button(array(
					"name" => "user_calendar",
					"tooltip" => "Kasutaja kalender",
					"url" => $this->mk_my_orb('change', array('id' => $cal_id),'planner'),
					"onClick" => "",
					"img" => "icon_cal_today.gif",
					"class" => "menuButton",
				));
			}

		
                };
	}
	
	function keys_vals($arr)
	{
		$keys = array();
		$vals = array();
		foreach($arr as  $key => $val)
		{
			$keys[] = $key;
			$vals[] = $val;
		}
		return array('keys' => implode(',',$keys), 'vals' => '"'.implode('","',$vals).'"');
	}
		
	function alias_to_org($args)
	{
		$this->addalias(array(
			'id' => $args['org'],
			'alias' => $args['event'],
			'no_cache' => true,
			'reltype' => $args['reltype'],
		));
		if (!$args['nodie'])
		die('seos tehtud');
	}
};
?>
