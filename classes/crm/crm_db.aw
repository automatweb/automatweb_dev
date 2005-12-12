<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_db.aw,v 1.22 2005/12/12 09:47:04 ekke Exp $
// crm_db.aw - CRM database
/*
	@classinfo relationmgr=yes syslog_type=ST_CRM_DB
	@default table=objects
	@default group=general

	@default field=meta
	@default method=serialize

	@property selections type=relpicker reltype=RELTYPE_SELECTIONS group=general
	@caption Vaikimisi valim

	@default group=f2
	@groupinfo f2 submit=no caption=Otsing parent=org
	
	@property orgtoolbar type=toolbar store=no no_caption=1 group=firmad,f2
	@caption Org. toolbar

	@property search_form1 type=form sclass=crm/crm_org_search sform=crm_search
	@caption Compound search

	@default group=firmad
	@groupinfo firmad submit=no caption=Nimekiri parent=org

	@groupinfo org caption=Organisatsioonid
			
	@property manageri type=text callback=firma_manager
	@caption Firmade nimekiri
	
	@default group=tegevusalad
	@groupinfo tegevusalad submit=no caption=Tegevusalad 

	@property tegtoolbar type=toolbar store=no no_caption=1	
	@caption Tegevusalade toolbar
	
	@property sector_tree type=treeview store=no
	@caption Tegevusalade puu
	
	@property sector_manager type=callback callback=callback_sector_manager
	@caption Tegevusalade kataloog

////////////////////////////////////////////////////////////
	@default group=settings
	@groupinfo settings caption=Seaded

	@property dir_firma type=relpicker reltype=RELTYPE_FIRMA_CAT
	@caption Vaikimisi firmade kaust

	@property folder_person type=relpicker reltype=RELTYPE_ISIK_CAT
	@caption Vaikimisi töötajate kaust

	@property dir_address type=relpicker reltype=RELTYPE_ADDRESS_CAT
	@caption Vaikimisi aadresside kaust

	@property dir_ettevotlusvorm type=relpicker reltype=RELTYPE_ETTEVOTLUSVORM_CAT
	@caption Vaikimisi õiguslike vormide kaust

	@property dir_linn type=relpicker reltype=RELTYPE_LINN_CAT
	@caption Vaikimisi linnade kaust

	@property dir_maakond type=relpicker reltype=RELTYPE_MAAKOND_CAT
	@caption Vaikimisi maakondade kaust

//	@property dir_riik type=relpicker reltype=RELTYPE_RIIK_CAT
//	@caption riikide kataloog(id)

	@property dir_tegevusala type=relpicker reltype=RELTYPE_TEGEVUSALA_CAT
	@caption Vaikimisi tegevusalade kaust

	@property dir_toode type=relpicker reltype=RELTYPE_TOODE_CAT
	@caption Vaikimisi toodete kaust

	@property dir_default type=relpicker reltype=RELTYPE_GENERAL_CAT
	@caption Vaikimisi kaust, kui mõni eelnevatest pole määratud, siis kasutatakse seda

//	@property where_firm type=checkbox ch_value=on
//	@caption näita ainult tegevusalasid, kus alal on ka ettevõtteid

	@property flimit type=select
	@caption Kirjeid ühel lehel
	
	default group=objects_manager
	groupinfo objects_manager caption=Objektide&nbsp;lisamine submit=no

	property addtoolbar type=toolbar store=no no_caption=1
	
	@property active_selection type=textbox group=firmad
	


*/

// do I really want to put that thing in here?

/*
@reltype SELECTIONS value=1 clid=CL_CRM_SELECTION
@caption Valimid

@reltype FIRMA_CAT value=2 clid=CL_MENU
@caption Organisatsioonide kaust

@reltype ISIK_CAT value=3 clid=CL_MENU
@caption Töötajate kaust

@reltype ADDRESS_CAT value=4 clid=CL_MENU
@caption Aadresside kaust

@reltype LINN_CAT value=5 clid=CL_MENU
@caption Linnade kaust

@reltype MAAKOND_CAT value=6 clid=CL_MENU
@caption Maakondade kaust

@reltype RIIK_CAT value=7 clid=CL_MENU
@caption Riikide kaust

@reltype TEGEVUSALA_CAT value=8 clid=CL_MENU
@caption Tegevusalade kaust

@reltype TOODE_CAT value=9 clid=CL_MENU
@caption Toodete kataloogide kaust

@reltype GENERAL_CAT value=10 clid=CL_MENU
@caption Üldkaust

@reltype CALENDAR value=11 clid=CL_PLANNER
@caption Kalender

@reltype ETTEVOTLUSVORM_CAT value=12 clid=CL_MENU
@caption Õiguslike vormide kaust

@reltype FORMS  value=13 clid=CL_CFGFORM
@caption Sisestusvormid

@reltype METAMGR value=14 clid=CL_METAMGR
@caption Muutujad

*/

class crm_db extends class_base
{
	var $selections_reltype;
	

	function crm_db()
	{
		$this->init(array(
			'clid' => CL_CRM_DB,
			'tpldir' => 'kliendibaas',
		));
		$this->selections_reltype = RELTYPE_SELECTIONS;
	}	
		
	function get_property(&$args)
	{

		$data = &$args['prop'];
		$retval = PROP_OK;
		$req = &$args['request'];
		
		//// valim///
		/* loome valimi instansi kui seda juba tehtud pole */
		if (!is_object($this->selection_object))
		{
			classload('crm/crm_selection');
			$this->selection_object = new crm_selection();
			$this->selection = $args['obj'];
		}

		// so, loeme sisse kõik selle objekti seosed ja jaotame nad tüübi järgi ära, jees
		if (!$args["new"] && !is_array($this->got_aliases))
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
			
			
			case 'sector_tree':
				$tree  = &$data["vcl_inst"];
				
				$menu_tree = new object_tree(array(
					"parent" => $args["obj_inst"]->prop("dir_tegevusala"),
					"class_id" => CL_CRM_SECTOR,
				));
				$sectors_list = $menu_tree->to_list();
				foreach ($sectors_list->list as $oid => $sect)
				{
					// sort by code
				//	$kood	= $sect->prop('kood');
				//	$id	= is_numeric($kood) ? $kood : $sect->id();
				//	$parent	= is_numeric($kood) && $kood > 9 ? substr($kood, 0, strlen($kood)-1) : 0;
					// sort by parent 
					$id = $sect->id();
					$parent = isset($sectors_list->list[$sect->parent()]) ? $sect->parent() : 0 ;

					$tree->add_item($parent,array(
						"id" => $id,
						"name" => $sect->name(),
						"url" => aw_url_change_var("teg_oid",$sect->id()),
					));
				}
				$tree->set_selected_item(ifset($args['request'], 'teg_oid'));
				
			/* XXX: DEL kui pole vaja sortida hierarhias
				$cur_keys	= array_keys($menu_tree->tree);
				$cur_vals	= array_values($menu_tree->tree);
				$cur_parent = null;
				
				$stack_keys = $stack_vals = $stack_parents = array ();

				
				while (is_array($cur_keys) && count($cur_keys))
				{
					$this_key	= array_shift($cur_keys);
					$this_val	= array_shift($cur_vals);
					if (is_array($this_val))
					{
						array_push($stack_keys, $cur_keys);
						array_push($stack_vals, $cur_vals);
						array_push($stack_parents, $cur_parent === null ? 0 : $cur_parent);
						$cur_keys = array_keys($this_val);
						$cur_vals	= array_values($this_val);
						$cur_parent	=	$cur_parent === null ? 0 : $this_key;
					} else {
						
					}
					
					
					if (!count($cur_keys))
					{
						if (count($stack_keys))
						{
							$cur_keys = array_pop($stack_keys);
							$cur_vals	= array_pop($stack_vals);
							$cur_parent = array_pop($stack_parents);
						}
					}
				}
//				*/
				/*
        for ($item = $menu_list->begin(); !$menu_list->end(); $item = $menu_list->next())
        {
        	$tree->add_item(0,array(
          	"id" => $item->id(),
            "name" => $item->name(),
            "url" => aw_url_change_var("folder_id",$item->id()),
					));
				};
				*/
				
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
		
			/*
			case 'search_form':

				$data['value'] = $this->search_form($args);
				break;

			case 'sfield':
				$retval=PROP_IGNORE;
				break;

			case 'exclude':
				$retval=PROP_IGNORE;
				break;

			case 'make_search':
				$args["obj_inst"]->set_meta("make_search",0);
				$retval=PROP_IGNORE;
				break;

			case 'search_type':
				$args["obj_inst"]->set_meta("search_type",0);
				$retval=PROP_IGNORE;
				break;
			*/
						
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
		$form = &$args["request"];
		$retval = PROP_OK;
		switch($data['name'])
		{
		};
		return $retval;
	}

	////
	// !sector manager
	function callback_sector_manager($args)
	{
		$obj = $args["obj_inst"];
		$tase = $args['request']['tase'] ? $args['request']['tase'] : 1;
		$kood = $args['request']['kood'] ? $args['request']['kood'] : '0';
		$teg_oid = $args['request']['teg_oid'] ? $args['request']['teg_oid'] : 0;
		
		$fpage = $args['request']['tpage'] ? $args['request']['tpage'] : '1';
		$flimit = 20;
		
		$tase = ($tase>3)?3:$tase;

		$limit = 100; // siia vaja ka aretada leheküljed //axel 

		// võtame tegevusalasid kusagilt alt, wuh? või siis ei võta? või mida see parent_in tegigi
		
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

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'kliendibaas_manager',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'tegevusala',
			'caption' => t('Tegevusala'),
		));

		$t->define_field(array(
			'name' => 'fcount',
			'caption' => t('Organisatsioone'),
		));
		
		$t->define_field(array(
			'name' => 'check',
			'caption' => "<a href='javascript:selall(\"sel\")'>".t("Vali")."</a>",
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
			if ($val["oid"])
				$yahbar = $val['name'].' / '.$yahbar;
		
		}
	
		if (is_array($arr))
		foreach($arr as $val)
		{
			$code = $val['kood'];
			$cnt = $this->db_fetch_field('select count(*) as cnt from aliases as t1 left join objects as t2 on t1.target=t2.oid 
				left join objects as t3 on t1.source=t3.oid		
				where t1.target="'.$val["oid"].'" and t1.reltype=5 and t1.source<>0 and t2.status=1
				and t3.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
				','cnt');

			$t->define_data(
				array(
					'tegevusala' => '<a href="'.$this->mk_my_orb('change', array(
				'id' => $args['obj_inst']->id(),
				'group' => 'tegevusalad',
				'tase' => ($tase + 1),
				'kood' => $val['kood'],
				'teg_oid' => $val["oid"],
				)).'">'.$val['name'],'</a>',
				'fcount' => ($cnn ? ($cnn.' / '):'').$cnt,
				)
			);
		}
		
		// right, I have a teg_obj, now I need to create a list of connected objects
		if (!empty($teg_oid))
		{
			$teg_obj = new object($teg_oid);
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
			// _virmade tabel, eh?
			$obj = $conn->from();
			if ($obj->class_id() == CL_CRM_COMPANY)
			{
				$this->_add_org_to_table(&$tf,$obj);
			}
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

	function _add_org_to_table($tf,$obj)
	{
		if (is_oid($obj->prop('contact')))
		{
			$addr_obj = new object($obj->prop('contact'));
			$url_id = (int)$addr_obj->prop("kodulehekylg");
			$url_obj = new object($url_id);
			$kodulehekylg = $url_obj->prop("url");
			$linn_id = $addr_obj->prop("linn");
		}
		else
		{
			$addr_obj = new object();
		};

		$tf->define_data(array(
			'id' => $obj->id(),
			'fname' => html::href(array(
				'url' => $this->mk_my_orb('change',array(
					'id' => $obj->id(),
					'return_url' => urlencode(aw_global_get('REQUEST_URI')),
				), CL_CRM_COMPANY),
				'caption' => strlen($obj->prop("name")) ? $obj->prop("name") : t("(nimetu)") ,
			)),
			'check' => $check,
			'reg_nr' => $obj->prop('reg_nr'),
			'ettevotlusvorm' => $this->_get_name_for_object($obj->prop("ettevotlusvorm")),
			'full_address' => $obj->prop('full_address'),
			'address' => $this->_get_name_for_object($obj->prop('contact')),
			'firmajuht' => 	html::href(array(
				'url' => $this->mk_my_orb('change',array(
					'id' => $obj->prop("firmajuht"),
					'return_url' => urlencode(aw_global_get('REQUEST_URI')),
				), CL_CRM_PERSON),
				'caption' => $this->_get_name_for_object($obj->prop("firmajuht")),
			)),
			'linn' => $this->_get_name_for_object($addr_obj->prop("linn")),
			'maakond' => $this->_get_name_for_object($addr_obj->prop("county")),
			'e_mail' => $this->_get_name_for_object($addr_obj->prop("e_mail")),
			'kodulehekylg' => html::href(array('url' => $kodulehekylg,'caption' => $kodulehekylg,'target' => '_blank')),
			'telefon' => $this->_get_name_for_object($addr_obj->prop("telefon")),
			'pohitegevus' => $this->_get_name_for_object($obj->prop("pohitegevus")),
		));
	}

	function _get_name_for_object($obj_id)
	{
		if ($obj_id)
		{
			$obj = new object($obj_id);
			$rv = $obj->name();
		}
		else
		{
			$rv = "";
		};
		return $rv;
	}

	function parent_in($arr)
	{
		if (is_array($arr))
		{
			foreach($arr as $val)
			$parents[] = $val;
		}

		if (count($parents) == 1)
		{
			return '='.$parents[0];
		}

		if (count($parents) > 1)
		{
			return ' in ('.implode(',',$parents).') ';
		}

		return '<>0';
	}

	function firma_manager($args)
	{
		$letter = $args['request']['letter'] ? $args['request']['letter'] : 'A';
		$fpage = $args['request']['fpage'] ? $args['request']['fpage'] : '1';
		$flimit = 20;
		if ($args["obj_inst"]->prop("flimit") != "")
		{
			$flimit = $args["obj_inst"]->prop("flimit");
		};
		$letters = '';
		$pages = '<style> BUTTON {height:23px;spacing:0px;padding:0px;}</style>';
		$showpagenr = array();

		// I need to be able to generate search forms from property definitions.
		
		//echo 
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
		
		// seeb meil siis nimekirja, eksju. onju. ahju?
		$cnt = $this->db_fetch_field('
		select count(*) as cnt from objects as t1
		where 
		t1.class_id='.CL_CRM_COMPANY.' and t1.status<>0 and
		t1.parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).'
		and t1.name like ("'.$letter.'%")
		','cnt');
		
		if ($cnt)
		if ($cnt>$flimit)
		{
			$pagearray = array();
			$pagecnt = ceil($cnt/$flimit);
			for($i = 1; $i <= $pagecnt; $i++)
			{
			$uri = "'".$this->mk_my_orb('change',
					array(
						'id' => $args['obj_inst']->id(),
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
			
		$arr = $this->db_fetch_array($q);
		$firmad = $this->firmad_table($arr);
		// sellest sitast tuleb ju ikka ka tabeligeneka featuur teha. geezas christ and mother of god
		// but how do I implement it in there?


		$all_letters = $this->db_fetch_array('select UCASE(substring(name,1,1)) as letter from objects 
		where class_id='.CL_CRM_COMPANY.' and status<>0 and parent'.$this->parent_in($this->got_aliases[FIRMA_CAT]).' 
		group by letter
		order by letter
		limit 50
		'
		);
		
		
		
		if(is_array($all_letters))
		{
			foreach($all_letters as $val)
			{
				$uri = "'".$this->mk_my_orb('change',
						array(
							'id' => $args['obj_inst']->id(),
							'group' => 'firmad',
							'page' => $i,
							'letter' => $val['letter'],
							'no_search' => '1',						
						)
					)."'";
				
			
				if ($val["letter"])
				{
					$letters.='<button style="width:21px" onclick="document.location='.$uri.';return false;">'.
					(($val['letter']==$letter) ? '<b><u>'.$val['letter'].'</u></b>' : $val['letter']).
					'</button>';
				};
			
			}
		};
				
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
			'caption' => t('Organisatsioon'),
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'reg_nr',
			'caption' => t('Reg nr.'),
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'pohitegevus',
			'caption' => t('Põhitegevus'),
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'ettevotlusvorm',
			'caption' => t('Õiguslik vorm'),
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'address',
			'caption' => t('Aadress'),
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'linn',
			'caption' => t('Linn/Vald/Alev'),
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'maakond',
			'caption' => t('Maakond'),
			'sortable' => '1',
		));		
		
		$tf->define_field(array(
			'name' => 'e_mail',
			'caption' => t('E-post'),
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'kodulehekylg',
			'caption' => t('Kodulehekülg'),
			'sortable' => '1',
		));
		$tf->define_field(array(
			'name' => 'telefon',
			'caption' => t('Telefon'),
			'sortable' => '1',
		));
		
		$tf->define_field(array(
			'name' => 'firmajuht',
			'caption' => t('Organisatsiooni juht'),
			'sortable' => '1',
		));

		$tf->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
		
		return $tf;
	}

	function firmad_table($arr)
	{
		$tf = $this->init_firmad_table();
		if (is_array($arr))
		foreach($arr as $val)
		{
			$obj = new object($val["oid"]);
			$this->_add_org_to_table(&$tf,$obj);
		}

		$tf->sort_by();
		$rv = $tf->draw();
		return $rv;
	}	

	// organisatsioonide toolbar
	function org_toolbar(&$args)
	{
		$toolbar = &$args["prop"]["toolbar"];                
		if (empty($args["new"]))
		{
			$crm_db = $args["obj_inst"];

			$parents[CL_CRM_COMPANY] = $crm_db->prop("dir_firma") == "" ? $crm_db->prop("dir_default") : $crm_db->prop("dir_firma");

			$toolbar->add_menu_button(array(
				"name" => "create_event",
				"tooltip" => t("Uus"),
			));

			$alist = array(
				array('clid' => CL_CRM_COMPANY),
			);
			$menudata = '';
			$clss = aw_ini_get("classes");

			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					$classinf = $clss[$val["clid"]];
					if (!$parents[$val['clid']])
					{
						$toolbar->add_menu_item(array(
							"parent" => "create_event",
							'title' => t('Kaust määramata'),
							'text' => sprintf(t('Lisa %s'),$classinf["name"]),
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
								'return_url' => get_ru(),
							)),
							'text' => sprintf(t('Lisa %s'),$classinf["name"]),
						));
					}
				};
			};
			
			$pl = get_instance(CL_PLANNER);
			$cal_id = $pl->get_calendar_for_user(array(
				"uid" => aw_global_get("uid"),
			));
			
			if (!empty($cal_id))	
			{
				$toolbar->add_button(array(
					"name" => "user_calendar",
					"tooltip" => t("Kasutaja kalender"),
					"url" => $this->mk_my_orb('change', array('id' => $cal_id,"group" => "views", 'return_url' => urlencode(aw_global_get('REQUEST_URI')),),'planner'),
					"onClick" => "",
					"img" => "icon_cal_today.gif",
					"class" => "menuButton",
				));
			}
		}

		$toolbar->add_separator();
		$toolbar->add_menu_button(array(
			"name" => "go_navigate",
			"tooltip" => t("Ava valim"),
			"img" => "iother_shared_folders.gif",
		));

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"action" => "delete_organizations",
			"confirm" => t("Kustutada valitud organisatsioonid?"),
			"img" => "delete.gif",
		));
		
		$conns = $args["obj_inst"]->connections_from(array(
			"class" => CL_CRM_SELECTION,
			"sort_by" => "to.name",
		));

		$ops = array();
		$ops[0] = t("-- vali valim --");

		foreach($conns as $conn)
		{
			$ops[$conn->prop("to")] = $conn->prop("to.name");
			$toolbar->add_menu_item(array(
				"parent" => "go_navigate",
				"text" => $conn->prop("to.name"),
				"url" => $this->mk_my_orb("change",array("id" => $conn->prop("to")),CL_CRM_SELECTION),
			));
		};

		$str .= html::select(array(
			"name" => "add_to_selection",
			"options" => $ops,
			"selected" => $selected,
		));

		$toolbar->add_separator(array(
			"side" => "right",
		));
		$toolbar->add_cdata($str,"right");
		$toolbar->add_button(array(
			"name" => "go_add",
			"tooltip" => t("Lisa valitud valimisse"),
			"action" => "copy_to_selection",
			"confirm" => t("Paiguta valitud organisatsioonid sellesse valimisse?"),
			"img" => "import.gif",
			"side" => "right",
		));

	}	

	////
	// !Objektide lisamise tabi toolbar. Geez.
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
				"tooltip" => t("Lisa uus objekt"),
			));

			foreach($par as $val)
			{
				$parents[$key] = $crm_db->prop("dir_".$val) ? $kliendibaas['meta']['dir_'.$val] : $kliendibaas['meta']['dir_default'];
			}

			// ah et muudkui lisame objekte?
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
			$clss = aw_ini_get("classes");
			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					$classinf = $clss[$val["class_id"]];
					if (!$parents[$val['class_id']])
					{
						$toolbar->add_menu_item(array(
							"parent" => "add_item",
							'title' => sprintf(t('%s kaust määramata'), $classinf['name']),
							'text' => sprintf(t('Lisa %s'),$classinf['name']),
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
	
	////
	// !Tegevusalade toolbar
	function teg_toolbar(&$args)
	{
		if (empty($args["new"]))
		{
			$toolbar = &$args["prop"]["toolbar"];
			$crm_db = $args["obj_inst"];

			$parents[CL_CRM_SECTOR] = $crm_db->prop('dir_tegevusala') == "" ? $crm_db->prop("dir_default") : $crm_db->prop("dir_tegevusala");

			if (isset($args['request']['teg_oid']) && is_oid($args['request']['teg_oid']))
			{
				$parents[CL_CRM_SECTOR] = $args['request']['teg_oid'];
			}
			$parents[CL_CRM_COMPANY] = $crm_db->prop("dir_firma") == "" ? $crm_db->prop("dir_default") : $crm_db->prop("dir_firma");
			
			$alist = array(
				array(
					'clid' => CL_CRM_SECTOR,
					),
				array(
					'clid'=> CL_CRM_COMPANY,
					'alias_to' => $args['request']['teg_oid'],
					'reltype' => 5, // RELTYPE_TEGEVUSALAD
					'disabled' => empty($args['request']['teg_oid']) ? t("Tegevusala valimata") : false,
					),
			);

			$toolbar->add_menu_button(array(
				"name" => "add_item",
				"tooltip" => t("Lisa"),
			));

			$menudata = '';
			$clss = aw_ini_get("classes");

			if (is_array($alist))
			{
				foreach($alist as $key => $val)
				{
					$val['disabled'] = $parents[$val['clid']] ? $val['disabled'] : t('Kaust määramata');
					$classinf = $clss[$val["clid"]];
					if ($val['disabled'])
					{
						$toolbar->add_menu_item(array(
							"parent" => "add_item",
							'title' => $val['disabled'],
							'text' => sprintf(t('Lisa %s'),$classinf["name"]),
							'disabled' => true,
						));					
					}
					else if ($val['clid'] == CL_CRM_COMPANY)
					{
						$toolbar->add_menu_item(array(
							"parent" => "add_item",
							'link' => $this->mk_my_orb('create_new_company',array(
								'sector'			=> $args['request']['teg_oid'],
								'category'		=> $parents[$val['clid']],
								'create_users'	=> 1,
								'return_url'	=> urlencode(aw_global_get('REQUEST_URI')),
							), CL_CRM_COMPANY),
							'text' => sprintf(t('Lisa %s'),$classinf['name']),
						));
					} 
					else // Add section
					{
						$toolbar->add_menu_item(array(
							"parent" => "add_item",
							'link' => $this->mk_my_orb('new',array(
								'class' 	=> basename($classinf["file"]),
								'reltype'	=> array_key_exists('reltype', $val) ? $val['reltype'] : "",
								'alias_to'	=> array_key_exists('alias_to', $val) ? $val['alias_to'] : "",
								'parent'	=> $parents[$val['clid']],
								'return_url' => urlencode(aw_global_get('REQUEST_URI')),
								
							)),
							'text' => sprintf(t('Lisa %s'),$classinf['name']),
						));
					}
				};
			};
			$toolbar->add_separator();
	
			$toolbar->add_button(array(
				"name" => "delete",
				"tooltip" => t("Kustuta"),
				"action" => "delete_organizations",
				"confirm" => t("Kustutada valitud organisatsioonid?"),
				"img" => "delete.gif",
			));
		
			$pl = get_instance(CL_PLANNER);
			$cal_id = $pl->get_calendar_for_user(array(
				"uid" => aw_global_get("uid"),
			));

			if (!empty($cal_id))
			{
				$toolbar->add_button(array(
					"name" => "user_calendar",
					"tooltip" => t("Kasutaja kalender"),
					"url" => $this->mk_my_orb('change', array('id' => $cal_id),'planner'),
					"onClick" => "",
					"img" => "icon_cal_today.gif",
				));
			}

		


			$conns = $args["obj_inst"]->connections_from(array(
				"class" => CL_CRM_SELECTION,
				"sort_by" => "to.name",
			));

			$ops = array();

			foreach($conns as $conn)
			{
				$ops[$conn->prop("to")] = $conn->prop("to.name");
			};

			$REQUEST_URI = aw_global_get("REQUEST_URI");

			$ops[0] = t('- lisa uude valimisse -');
			$str .= html::select(array(
				'name' => 'add_to_selection',
				'options' => $ops,
				'selected' => $selected,
			));

			$toolbar->add_separator(array(
				"side" => "right",
			));

			$toolbar->add_cdata($str,"right");

			$parent = $args["obj_inst"]->parent();

			$toolbar->add_button(array(
				"name" => 'go_add',
				"tooltip" => t("Lisa valitud valimisse"),
				"url" => "javascript:void(0);",
				"img" => "import.gif",
				"side" => "right",
				'onClick' => "go_manage_selection(document.changeform.add_to_selection.value,'".$REQUEST_URI."','add_to_selection','".$parent."');return true;",
			));

			$str = "";
			$toolbar->add_button(array(
				"name" => 'change_it',
				"tooltip" => t('Muuda valimit'),
				"url" => "javascript:void(0);",
				"img" => "edit.gif",
				"side" => "right",
				'onClick' => "JavaScript: if (document.changeform.add_to_selection.value < 1){return false}; url='".$this->mk_my_orb('change',array('group' => 'contents'),'crm_selection')."&id=' + document.changeform.add_to_selection.value; window.open(url);",
			));

			$str .= html::hidden(array('name' => 'new_selection_name'));
			$str .= $this->get_file(array("file" => $this->cfg['tpldir'].'/selection/go_add_to_selection.script'));
			$toolbar->add_cdata($str);
		};
	}

	/**  
		
		@attrib name=delete_organizations params=name all_args="1" 
		
	**/
	function delete_organizations($arr)
	{
		unset($arr["MAX_FILE_SIZE"]);
		unset($arr["action"]);
		unset($arr["reforb"]);
		unset($arr["add_to_selection"]);

		$sel = new aw_array($arr["sel"]);
		foreach($sel->get() as $obj_id)
		{
			$o = new object($obj_id);
			$o->delete();
		};
		unset($arr["sel"]);
		$tmp = $arr;

		if (is_array($arr["search_form1"]))
		{
			$tmp["search_form1"] = $arr["search_form1"];
		};
		
		// now I need to redirect back to whatever that url was
		$rv = $this->mk_my_orb("change",$tmp);
		return $rv;
	}
	
	/**  
		
		@attrib name=copy_to_selection params=name all_args="1" 
		
	**/
	function copy_to_selection($arr)
	{
		unset($arr["MAX_FILE_SIZE"]);
		unset($arr["action"]);
		unset($arr["reforb"]);

		$selinst = get_instance(CL_CRM_SELECTION);
		$selinst->add_to_selection(array(
			"add_to_selection" => $arr["add_to_selection"],
			"sel" => $arr["sel"],
		));

		$sel = new aw_array($arr["sel"]);

		unset($arr["sel"]);
		$tmp = $arr;

		if (is_array($arr["search_form1"]))
		{
			$tmp["search_form1"] = $arr["search_form1"];
		};
		
		// now I need to redirect back to whatever that url was
		$rv = $this->mk_my_orb("change",$tmp);
		return $rv;
	}

	function callback_mod_retval($arr)
	{
		$args = &$arr["args"];
		// no I need add all those things in search_form1 do my request vars
		if (is_array($arr["request"]["search_form1"]))
		{
			$args["search_form1"] = $arr["request"]["search_form1"];
		};
	}
		
};
?>
