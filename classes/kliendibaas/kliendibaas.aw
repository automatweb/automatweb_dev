<?php

/*
	@classinfo relationmgr=yes
	@default table=objects
	@default group=general

	@default field=meta
	@default method=serialize

	@property limit_per_page type=textbox size=8
	@caption mitu veergu näita


////////////////////////////////////////////////////////////
	@default group=directorys
	@groupinfo directorys caption=kataloogid

	@property dir_firmad type=select
	@caption firmade kataloog(id)

	@property dir_isik type=select
	@caption isikute kataloog(id)

	@property dir_address type=select
	@caption aadresside kataloog(id)

	@property dir_linn type=select
	@caption linnade kataloog(id)

	@property dir_maakond type=select
	@caption maakondade kataloog(id)

	@property dir_riik type=select
	@caption riikide kataloog(id)

	@property dir_tegevusala type=select
	@caption tegevusalade kataloog(id)

	@property dir_toode type=select
	@caption toodete kataloog(id)

	@property dir_default type=select
	@caption üld kataloog kataloog


////////////////////////////////////////////////////////////
	@default group=manager
	@groupinfo manager caption=ettevõtted

	@property manager type=generated callback=address_manager

	@property show_columns type=select multiple=1
	@caption näita veerge

	@property order_by_columns type=select
	@caption sorteerituna

	@property page type=textbox size=3
	@caption page

	@property search type=textbox size=3

	@property search_require type=textbox size=3

	@property do_search type=textbox size=3
	@caption do otsing

	@property actions type=text
	@caption actions

	@property select type=textbox size=3
	@property objs type=textbox size=3
	@property sel type=checkbox ch_value=on

////////////////////////////////////////////////////////////
	@default group=tegevusalad
	@groupinfo tegevusalad caption=tegevusalad

	@property teg_page type=textbox size=3
	@caption teg_page

	@property tegevusala_manager type=generated callback=tegevusala_manager

	@property where_firm type=checkbox ch_value=on
	@caption näita ainult tegevusalasid, kus alal on ka ettevõtteid

	@property teg_do_search type=textbox size=3
	@caption do otsing
	@property teg_search type=textbox size=3


////////////////////////////////////////////////////////////
	@default group=selection
	@groupinfo selection caption=selection

	@property valim type=relpicker reltype=VALIM
	@caption vali valim

*/

define ('VALIM',1);


class kliendibaas extends class_base
{
	var $show_columns;
	function callback_get_rel_types()
	{
		return array(
			VALIM => 'valimid',
//			 => '',
//			 => '',
//			 => '',
		);
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = true;
		$meta=$args['obj']['meta'];
		//print_r($args);die();
		switch($data["name"])
		{
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'alias':
				$retval=PROP_IGNORE;
			break;
			case 'search':
//				foreach ($meta['select'] as $key => $val)
				{
//					echo $key.'<br />';
//					$data['value'].=$val.'<br />';
				}
				$retval=PROP_IGNORE;
//				die('kk');
			break;
			case 'select':
				$retval=PROP_IGNORE;
			break;
			case 'objs':
				$retval=PROP_IGNORE;
			break;
			case 'valim':
				$data['data']='test';
				//$retval=PROP_IGNORE;
			break;
			case 'teg_search':
				$retval=PROP_IGNORE;
			break;
			case 'teg_do_search':
				$data['value']='';
			break;
			case 'search_require':
				$retval=PROP_IGNORE;
			break;
			case 'limit_per_page':
				$data['value']=$data['value']?$data['value']:20;
			break;
			case 'do_search':
				$data['value']='';
			break;
			case 'order_by_columns':
				$data['options']=$this->show_columns;
			break;
			case 'show_columns':
				$data['options']=$this->show_columns;
//				print_r($args['obj']['meta']['show_columns']);die();
//				$data['selected']=$args['obj']['meta']['show_columns'];
//				$data['multiple']=1;
			break;
		}
		return  $retval;
	}


	function kliendibaas()
	{
		$this->init(array(
			'clid' => CL_KLIENDIBAAS,
		));

		$this->show_columns= array(
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

		);


	}


	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$meta =  $args['obj']['meta'];
		$retval = PROP_OK;

/*		print_r($args);
		die();
*/
		switch($data['name'])
		{
			case 'select':
			if ($meta['valim'])
			{
				if (is_array($form['objs']))
				{
					$sl = get_instance('kliendibaas/selection');
//					$selection = (array)$this->get_object_metadata(array('oid'=>$meta['valim'],'key'=>'selection'));
					$selection = $sl->get_selection($meta['valim']);
					if (is_array($selection))
					foreach($form['objs'] as $key => $val)
					{
						if (isset($form['objs'][$key]) && !isset($form['select'][$key]) && $selection[$key])
						{
							unset($selection[$key]);
						}
					}
//					$this->set_object_metadata(array('oid'=>$meta['valim'],'key'=>'selection','value'=>$selection+(array)$form['select']));
					$objects = (array)$selection+(array)$form['select'];
					$sl->set_selection($meta['valim'],$objects);
				}
				$retval = PROP_IGNORE;

			}
			break;
		};
		return $retval;
	}

/*
	function selection_manager($ob)
	{

		$meta=$ob['obj']['meta'];
		$limit=$meta['limit_per_page']?$meta['limit_per_page']:20;
		$req=$ob['request'];
		$page=$meta['sel_page']?$meta['sel_page']:0;

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'kliendibaas_sel_manager',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'firmanimi',
			'caption' => 'kood',
		));

		$t->define_field(array(
			'name' => 'tegevusala',
			'caption' => 'tegevusala',
		));

		$data = $this->db_fetch_array($q);

		$arr = new aw_array($data);
		foreach($arr->get() as $row)
		{
			$row['check']=html::checkbox(array('name'=>'select['.$row['oid'].']'));
//			$row['change']=html::href(array('caption'=>'muuda','target'=>'_blank','url'=>$this->mk_my_orb('change',array('id'=>$row['oid']),'kliendibaas/tegevusala')));
			$row['fcount']=$row['fcount']?(html::href(array('caption'=>'<b> [ '.$row['fcount'].' ] </b>',
				'url'=>$this->mk_my_orb('change',
					array('id'=>$ob['obj']['oid'], 'group'=>'manager', 'kood'=>$row['kood'])
				)
			))):'';
			$row['kood']=html::href(array('caption'=>'<b>'.$row['kood'].'</b>',
				'url'=>$this->mk_my_orb('change',
					array('id'=>$ob['obj']['oid'], 'group'=>'tegevusala', 'kood'=>$row['kood'],'page'=>0,'prev'=>$req['kood'])
				)
			));
			$row['tegevusala']=html::href(array('caption'=>$row['tegevusala'],'target'=>'_blank','url'=>$this->mk_my_orb('change',array('id'=>$row['oid']),'kliendibaas/tegevusala')));

			$t->define_data(
				$row
			);
		}

		$navigate=array(
			' |< '=> 0,
			' < ' => abs($page-1),
			' || '=> $page,
			' > ' => ($page+1),
			' >| '=> ((int)($cnt/$limit)),
		);
		$nav=$this->my_buttons($navigate,'sel_page');

		return $t->draw();

	}
*/


	function tegevusala_manager($ob)
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
			'caption' => 'vali',
			'width'=> 15,
		));

		$find = new aw_table(array(
			'prefix' => 'kliendibaas_manager_search',
		));
		$search=html::textbox(array('name'=>'teg_search[kood]','value' =>$meta['search'][$key],'size'=>20)).' '.
		html::textbox(array('name'=>'teg_search[tegevusala]','value' =>$meta['search'][$key],'size'=>20)).' '.
		html::button(array('value'=>'otsi','onclick'=>'document.changeform.teg_do_search.value=1;document.changeform.submit()'));


		if ($meta['teg_do_search'])
		{
			$where="where kood like '%".$meta['teg_search']['kood']."%' and tegevusala like '%".$meta['teg_search']['tegevusala']."%'";
		}
		else
		{
			$where=$req['kood']?"where kood like '".$req['kood']."%'":' where LENGTH(kood) < 3 ';
		}


		$leftright=$meta['where_firm']?'right':'left';

		$q = 'select count(t1.oid) as cnt from kliendibaas_tegevusala as t1 '.$leftright.' join kliendibaas_firma as t2
		 on t2.pohitegevus=t1.oid '.$where;
		$cnt = $this->db_fetch_field($q,'cnt');

		$q='select t1.oid,t1.tegevusala,t1.kood,count(t2.reg_nr) as fcount from
		 kliendibaas_tegevusala as t1
		 '.$leftright.' join kliendibaas_firma as t2
		 on t2.pohitegevus=t1.oid '.$where.
		 '   group by t1.kood order by t1.kood LIMIT '.($page*$limit).','.((int)$limit);
		$data = $this->db_fetch_array($q);

		$t=$this->tegevusalad_table($data,$selection,$t,$ob['obj']['oid']);

		$back = html::href(array('caption'=>'tagasi',
			'url'=>$this->mk_my_orb('change',
				array('id'=>$row['oid'], 'group'=>'tegevusala','kood'=>$req['prev'])
			)
		));

		$navigate=array(
			' |< '=> 0,
			' < ' => abs($page-1),
			' || '=> $page,
			' > ' => ($page+1),
			' >| '=> ((int)($cnt/$limit)),
		);
		$nav=$this->my_buttons($navigate,'teg_page');

		$node["type"] = 'text';
		$node["value"] = $nav.'total :'.$cnt.'.  '.$back.$t.$search;

		$nodes[]=$node;
		return $nodes;
	}



	function tegevusalad_table($data,$selection,$t,$oid)
	{
		$arr = new aw_array($data);
		foreach($arr->get() as $row)
		{
			$row['check']=html::checkbox(array('name'=>'select['.$row['oid'].']'));
//			$row['change']=html::href(array('caption'=>'muuda','target'=>'_blank','url'=>$this->mk_my_orb('change',array('id'=>$row['oid']),'kliendibaas/tegevusala')));
			$row['fcount']=$row['fcount']?(html::href(array('caption'=>'<b> [ '.$row['fcount'].' ] </b>',
				'url'=>$this->mk_my_orb('change',
					array('id'=>$oid, 'group'=>'manager', 'kood'=>$row['kood'])
				)
			))):'';
			$row['kood']=html::href(array('caption'=>'<b>'.$row['kood'].'</b>',
				'url'=>$this->mk_my_orb('change',
					array('id'=>$oid, 'group'=>'tegevusala', 'kood'=>$row['kood'],'page'=>0)//'prev'=>$req['kood'])
				)
			));
			$row['tegevusala']=html::href(array('caption'=>$row['tegevusala'],'target'=>'_blank','url'=>$this->mk_my_orb('change',array('id'=>$row['oid']),'kliendibaas/tegevusala')));

			$t->define_data(
				$row
			);
		}
		return $t->draw();
	}



/*	function selection_manager($ob)
	{
		$meta=$ob['obj']['meta'];
		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'kliendibaas_manager',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');


		$t->define_field(array(
			'name' => 'name',
			'caption' => 'valim',
		));

		$t->define_field(array(
			'name' => 'o_count',
			'caption' => 'objekte valimis',
		));

		$t->define_field(array(
			'name' => 'comment',
			'caption' => 'kirjeldus',
		));

		$t->define_field(array(
			'name' => 'comment',
			'caption' => 'kirjeldus',
		));

		$t->define_field(array(
			'name' => 'check',
			'caption' => 'vali',
			'width'=> 15,
		));

		$data = $this->db_fetch_array($q);

		$arr = new aw_array($data);
		foreach($arr->get() as $row)
		{
			$t->define_data(
				$row
			);
		}

		$node["type"] = 'text';
		$node["value"] = 'test';

		$nodes[]=$node;
		return $nodes;
	}
*/


	function address_manager($ob)
	{

		$meta=$ob['obj']['meta'];
		$limit=$meta['limit_per_page']?$meta['limit_per_page']:20;
		$page=$meta['page']?$meta['page']:0;
		$req=$ob['request'];
		$order_by=$meta['order_by_columns']?$meta['order_by_columns']:'objects.name';
		$where=' where 1 ';
		$sel=$meta['sel'];

		if ($meta['valim'])
		{

			$sl = get_instance('kliendibaas/selection');
			$selection = $sl->get_selection($meta['valim']);
		}
		else
		{
			unset($sel);
		}




//		if (is_array($meta['select']))
//		{
//			foreach ($meta['select'] as $key => $val)
//			{
//				(array)$selection+$meta['select']
///object kliendibaas_selection
//type=firma|isik|...
//oids=array of type
//			}
//		}

		$tabelid=array(
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

		if (!$sel)
		{
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
				'caption' => 'peab olemas olema',
				'width'=> 15,
			));


			$like_st='%';
			$like_en='%';

			if ($req['kood'])
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


		foreach($this->show_columns as $key => $val)
		{

			if (!$sel)
			{
				$find->define_data(array(
					'attrib' => $val,
					'input' => html::textbox(array('name'=>'search['.$key.']','value' =>$meta['search'][$key],'size'=>20)),
					'require' => html::checkbox(array('name'=> 'search_require['.$key.']','value'=> 1,'checked'=> $meta['search_require'][$key])),
				));
			}
			if (!(isset($meta['show_columns'][$key]) ||($meta['do_search'] && $meta['search'][$key])
			||($meta['do_search'] && $meta['search_require'][$key])))
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

			if (!$sel)
			{
				if ($meta['do_search'])
				{

					if ($meta['search'][$key])
					{
						$where.=' and '.$fields[$key]." like '".$like_st.addslashes($meta['search'][$key]).$like_en."' ";
					}

					if ($meta['search_require'][$key])
					{
						$where.=' and '.$fields[$key].' IS NOT NULL and '.$fields[$key]." <> '' ";

					}
				}
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

		if (!$sel)
		{
			$searchform.=html::button(array('value'=>'otsi','onclick'=>'document.changeform.do_search.value=1;document.changeform.submit()'));
		}
		$fields['oid'] ='firma.oid as oid';

		$t->define_field(array(
			'name' => 'check',
			'caption' => 'vali',
			'width'=> 15,
		));


		$fields['oid'] ='firma.oid as oid';

///*		$t->define_field(array(
//			'name' => 'show',
//			'caption' => 'vaata',
//			'width'=> 20,
//		));


		foreach($tabelid as $key => $val)
		{
			if (isset($joini[$key]))
			{
				$join[$key]=$val;
			}
		}


		if (!$sel)
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
		$t=$this->firmad_table($data,$selection,$t);

		$navigate=array(
			' |< '=> 0,
			' < ' => abs($page-1),
			' || '=> $page,
			' > ' => ($page+1),
			' >| '=> ((int)($cnt/$limit)),
		);

		$nav=$this->my_buttons($navigate,'page');

		$node["type"] ='text';
		$node["value"] ='total: '.$cnt.'<br />'.
		'<table width=200><tr><td>'.($sel?'':$find->draw()).'</td></tr></table>'.$searchform.'<br />'.
		$nav.'lk: '.($page+1).'<br />'.
		$t
		//.$sels
		;

		$nodes[]=$node;
		return $nodes;
	}

	function firmad_table($data,$selection,$t)
	{
		$arr = new aw_array($data);
		foreach($arr->get() as $row)
		{
			$row['check']=html::checkbox(array('name'=>'select['.$row['oid'].']','checked' => $selection[$row['oid']],'value' => 1));
			$row['check'].=html::hidden(array('name'=>'objs['.$row['oid'].']' ,'value'=>1));

 			if ($row['firma_nimetus'])
			{
				$row['firma_nimetus']=html::href(array('caption'=>$row['firma_nimetus'],'target'=>'_blank','url'=>$this->mk_my_orb('change',array('id'=>$row['oid']),'kliendibaas/firma')));
			}
			if ($row['f_kodulehekylg'])
			{
				$row['f_kodulehekylg']=html::href(array('url'=>$row['f_kodulehekylg'],'caption'=> $row['f_kodulehekylg'], 'target' => '_blank' ));
			}
			if ($row['f_e_mail'])
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
		foreach($button as $key => $val)
		{
			$nav.=html::button(array('value'=>$key,'onclick'=>'document.changeform.'.$page.'.value='.$val.';document.changeform.submit()'));
		}
		return $nav;
	}


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
			$f[]='oid';
			$v[]="'".$id."'";

			$q='insert into kliendibaas_'.$tyyp.'('.implode(",",$f).')values('.implode(",",$v).')';

		$this->db_query($q);
		return $id;
	}




/*

$arr=$this->db_fetch_array('select kood1,kood from html_import_tegevusalad');

foreach ($arr as $key => $val)
{
	if (($val['kood1'] && $val['kood']) && ($val['kood1']<>$val['kood']))
	{
		$this->db_query("update kliendibaas_tegevusala set kood='".$val['kood']."' where kood='".$val['kood1']."'");
//		echo $val['kood1'].'=>'.$val['kood'].'<br>';
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

}
?>
