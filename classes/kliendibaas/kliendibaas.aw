<?php

/*
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

	@property dir_contact type=select
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
	@groupinfo manager caption=haldus

	@property manager type=generated callback=contact_manager

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


////////////////////////////////////////////////////////////
	@default group=tegevusala
	@groupinfo tegevusala caption=tegevusala

	@property page type=textbox size=3
	@caption page

	@property tegevusala_manager type=generated callback=tegevusala_manager

	@property where_firm type=checkbox ch_value=on
	@caption näita ainult tegevusalasid, kus alal on ka ettevõtteid


////////////////////////////////////////////////////////////
	@default group=selection
	@groupinfo selection caption=selection


*/
class kliendibaas extends class_base
{
//edasi tagasi lõppu algusesse
//lisa otsi muuda kustuta

//	@property
//	@caption

	var $show_columns;

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = true;
		$meta=$args['obj']['meta'];
		switch($data["name"])
		{
/*			case 'status':
				$retval=PROP_IGNORE;
			break;
*/
			case 'jrk':
				$retval=PROP_IGNORE;
			break;
			case 'alias':
				$retval=PROP_IGNORE;
			break;
/*			case 'comment':
				$retval=PROP_IGNORE;
			break;*/
			case 'search':
				$retval=PROP_IGNORE;
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
		$retval = PROP_OK;
		switch($data['name'])
		{

		};
		return $retval;
	}

	function tegevusala_manager($ob)
	{
		$meta=$ob['obj']['meta'];
		$limit=$meta['limit_per_page']?$meta['limit_per_page']:20;
		$req=$ob['request'];
		$page=$meta['page']?$meta['page']:0;
		$page=isset($req['page'])?$req['page']:$page;

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'kliendibaas_manager',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'check',
			'caption' => 'vali',
			'width'=> 15,
		));

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
			'name' => 'change',
			'caption' => 'muuda',
			'width'=> 20,
		));

		$where=$req['kood']?"where kood like '".$req['kood']."%'":' where 1 ';
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

		$arr = new aw_array($data);
		foreach($arr->get() as $row)
		{
			$row['check']=html::checkbox(array('name'=>'select['.$row['oid'].']'));
			$row['change']=html::href(array('caption'=>'muuda','target'=>'_blank','url'=>$this->mk_my_orb('change',array('id'=>$row['oid']),'kliendibaas/tegevusala')));
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

			$t->define_data(
				$row
			);
		}

		$back = html::href(array('caption'=>'tagasi',
			'url'=>$this->mk_my_orb('change',
				array('id'=>$row['oid'], 'group'=>'tegevusala','kood'=>$req['prev'])//)
			)
		));

		$navigate=array(
			' |< '=> 0,
			' < ' => abs($page-1),
			' || '=> $page,
			' > ' => ($page+1),
			' >| '=> ((int)($cnt/$limit)),
		);
		$nav=$this->my_buttons($navigate);

		$node["type"] = 'text';
		$node["value"] = $nav.'total :'.$cnt.'.  '.$back.$t->draw();

		$nodes[]=$node;
		return $nodes;


	}



	function contact_manager($ob)
	{
		$meta=$ob['obj']['meta'];
		$limit=$meta['limit_per_page']?$meta['limit_per_page']:20;
		$page=$meta['page']?$meta['page']:0;
		$req=$ob['request'];
		$order_by=$meta['order_by_columns']?$meta['order_by_columns']:'objects.name';

		$where=' where 1 ';

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'kliendibaas_manager',
		));
		$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

		$tabelid=array(
			'vorm' => 'left join kliendibaas_ettevotlusvorm as vorm on vorm.oid=firma.ettevotlusvorm',
			'juht' => 'left join kliendibaas_isik as juht on juht.oid=firma.firmajuht',
			'f_contact' => 'left join kliendibaas_contact as f_contact on f_contact.oid=firma.contact',
			'f_riik' => 'left join kliendibaas_riik as f_riik on f_riik.oid=f_contact.riik',
			'f_maakond' => 'left join kliendibaas_maakond as f_maakond on f_maakond.oid=f_contact.maakond',
			'f_linn' => 'left join kliendibaas_linn as f_linn on f_linn.oid=f_contact.linn',
			'firma'=>' ',
			'objects'=>'left join objects on firma.oid=objects.oid',
			'tegevusala'=>'left join kliendibaas_tegevusala as tegevusala on tegevusala.oid=firma.pohitegevus',
		);
		$joini['objects']++;

		$t->define_field(array(
			'name' => 'check',
			'caption' => 'vali',
			'width'=> 15,
		));

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


		if ($req['kood'])
		{
			unset($meta['search']);
			unset($meta['search_require']);
			$page=0;
			$joini['tegevusala']++;
			$meta['search']['tegevusala_kood']=$req['kood'];
		}

		foreach($this->show_columns as $key => $val)
		{
			$find->define_data(array(
				'attrib' => $val,
				'input' => html::textbox(array('name'=>'search['.$key.']','value' =>$meta['search'][$key],'size'=>20)),
				'require' => html::checkbox(array('name'=> 'search_require['.$key.']','value'=> 1,'checked'=> $meta['search_require'][$key])),
			));

			if (!(isset($meta['show_columns'][$key]) ||($meta['do_search'] && $meta['search'][$key])
			||($meta['do_search'] && $meta['search_require'][$key])))
			{
				continue;
			}

			switch($key)
			{
				case 'firma_oid':
					$fields[$key]='objects.oid';
					$joini['objects']++;
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
					$fields[$key]='f_contact.aadress';
					$joini['f_contact']++;
				break;
				case 'f_riik':
					$fields[$key]='f_riik.name';
					$joini['f_contact']++;
					$joini['f_riik']++;
				break;
				case 'f_linn':
					$fields[$key]='f_linn.name';
					$joini['f_contact']++;
					$joini['f_linn']++;
				break;
				case 'f_maakond':
					$fields[$key]='f_maakond.name';
					$joini['f_contact']++;
					$joini['f_maakond']++;
				break;
				case 'f_postiindeks':
					$fields[$key]='f_contact.postiindeks';
					$joini['f_contact']++;
				break;
				case 'f_telefon':
					$fields[$key]='f_contact.telefon';
					$joini['f_contact']++;
				break;
				case 'f_mobiil':
					$fields[$key]='f_contact.mobiil';
					$joini['f_contact']++;
				break;
				case 'f_faks':
					$fields[$key]='f_contact.faks';
					$joini['f_contact']++;
				break;
				case 'f_piipar':
					$fields[$key]='f_contact.piipar';
					$joini['f_contact']++;
				break;
				case 'f_e_mail':
					$fields[$key]='f_contact.e_mail';
					$joini['f_contact']++;
				break;
				case 'f_kodulehekylg':
					$fields[$key]='f_contact.kodulehekylg';
					$joini['f_contact']++;
				break;
				case 'firma_juht':
					$fields[$key]='juht.name';
					$joini['juht']++;
				break;
				case 'firma_nimetus':
					$fields[$key]='objects.name';
					$joini['objects']++;
				break;
				case 'firma_reg_nr':
					$fields[$key]='firma.reg_nr';
					$joini['firma']++;
				break;
			}

			if ($meta['do_search'])
			{

				if ($meta['search'][$key])
				{
					$where.=' and '.$fields[$key]." like '%".addslashes($meta['search'][$key])."%' ";
				}

				if ($meta['search_require'][$key])
				{
					$where.=' and '.$fields[$key].' IS NOT NULL and '.$fields[$key]." <> '' ";

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

		$searchform.=html::button(array('value'=>'otsi','onclick'=>'document.changeform.do_search.value=1;document.changeform.submit()'));

		$fields['oid'] ='firma.oid as oid';
		$t->define_field(array(
			'name' => 'change',
			'caption' => 'muuda',
			'width'=> 20,
		));
		
		$fields['oid'] ='firma.oid as oid';
		$t->define_field(array(
			'name' => 'show',
			'caption' => 'vaata',
			'width'=> 20,
		));


		foreach($tabelid as $key => $val)
		{
			if (isset($joini[$key]))
			{
				$join[$key]=$val;
			}
		}

		$q='select count(*) as cnt from kliendibaas_firma as firma '.@implode(' ',$join).$where;
		$cnt = $this->db_fetch_field($q,'cnt');

		$q='select '.implode(',',$fields).' from kliendibaas_firma as firma '.
			@implode(' ',$join).' '.
			$where.' order by '.$order_by.
			' LIMIT '.($page*$limit).','.((int)$limit);
		$data = $this->db_fetch_array($q);

		$arr = new aw_array($data);
		foreach($arr->get() as $row)
		{
			$row['check']=html::checkbox(array('name'=>'select['.$row['oid'].']'));
			$row['change']=html::href(array('caption'=>'muuda','target'=>'_blank','url'=>$this->mk_my_orb('change',array('id'=>$row['oid']),'kliendibaas/firma')));
			$row['show']=html::href(array('caption'=>'vaata','target'=>'_blank','url'=>$this->mk_my_orb('change',array('id'=>$row['oid']),'kliendibaas/firma')));
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


		$navigate=array(
			' |< '=> 0,
			' < ' => abs($page-1),
			' || '=> $page,
			' > ' => ($page+1),
			' >| '=> ((int)($cnt/$limit)),
		);

		$nav=$this->my_buttons($navigate);

		$node["type"] ='text';
		$node["value"] ='total: '.$cnt.'<br />'.
		$nav.'lk: '.($page+1).'<br />'.
		$t->draw().
		'<table width=200><tr><td>'.$find->draw().'</td></tr></table>'.
		$searchform;

		$nodes[]=$node;
		return $nodes;
	}



	function my_buttons($button)
	{
		foreach($button as $key => $val)
		{
			$nav.=html::button(array('value'=>$key,'onclick'=>'document.changeform.page.value='.$val.';document.changeform.submit()'));
		}
		return $nav;
	}





	function add_table_name (&$item1, $key, $prefix)
	{
		$item1 = $prefix.'.'.$item1;
	}



	////
	// !
	// table - required
	// field -
	function pop_select($arr)
	{
		extract($arr);

		if (!$tyyp) die('no type');

		if (($table == 'kliendibaas_contact') || ($table == 'kliendibaas_firma')) $field=$tyyp; else die('vale tabel');
		;
		if ($id)
		{
//			$selected=$this->db_fetch_field("select $field from $table where oid=$id",$field);
//			$ob = $this->get_object($id);
		}

		switch($field)
		{
			case "firmajuht":
				{
					$q="select t1.oid,concat(t1.firstname,' ',t1.lastname) as name from kliendibaas_isik as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='isik';
				}
			break;
			case "korvaltegevused":
				{
					$q="select t1.kood as oid,t1.tegevusala as name from kliendibaas_tegevusala as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='tegevusala';
				}
			break;
			case "tooted":
				{
					$q="select t1.kood as oid,t1.toode as name from kliendibaas_toode as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='toode';
				}
			break;
			case "pohitegevus":
				{
					$q="select t1.kood as oid,t1.tegevusala as name from kliendibaas_tegevusala as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='tegevusala';
				}
			break;
			case "ettevotlusvorm":
				{
					$q="select t1.oid,t1.name from kliendibaas_ettevotlusvorm as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='ettevotlusvorm';
				}
			break;
			case "linn":
				{
					$q="select t1.oid,t1.name from kliendibaas_linn as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='linn';
				}
			break;
			case "riik":
				{
					echo $q="select t1.oid,t1.name from kliendibaas_riik as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='riik';
				}
			break;
			case "maakond":
				{
					$q="select t1.oid,t1.name from kliendibaas_maakond as t1, objects as t2 where t1.oid=t2.oid and t2.status=2";
					$object='maakond';
				}
			break;
		}

		$data[0] = ' - ';

		if ($q)
		{
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$data[$row["oid"]] = substr($row["name"],0,50);
			};
		}
		else
		{
			$data[]='tekkis viga';
		}
		if ($id)
		{
			$add=$this->mk_my_orb('new',array('parent'=>$ob['parent']),'kliendibaas/'.$object);
			$add_new=html::href(array('caption' => 'lisa andmebaasi uus '.$field, 'target' =>'_blank','url' =>$add));
		}

		if (is_array($data))
		{
			asort($data);
		}


$pop_tpl=<<<TPL
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
	<HEAD>
		<SCRIPT language=JavaScript>
			function SendValue(value)
			{
				opener.put_value(document.selectform.tyyp.value,value);
				window.close();
			}
		</SCRIPT>
		<TITLE> Vali {VAR:mida}</TITLE>
	</HEAD>
	<BODY onload="document.selectform.selector.focus()" bgcolor=#777777>
		<form name=selectform>
			 Vali {VAR:mida}<br/>
			{VAR:tyyp}
			{VAR:selector}
			<br>
			{VAR:cancel}
			<br>
			{VAR:ok}
			<br>
			{VAR:add}
		</form>
	</BODY>
</HTML>
TPL;

		$vars=array(

			'tyyp' => html::hidden(array('name'=>'tyyp','value' => $field)),
			'cancel' => html::button(array('value'=>'cancel','onclick'=>"javascript:window.close()")),
			'ok' => html::button(array('value'=>'ok','onclick'=>"javascript:SendValue(document.selectform.selector.value)")),
			"add" => $add_new,
			"mida" => $field,

			'selector' => html::select(array(
				'name' => 'selector',
				'size'=> 10,
				'selected' => $selected,
//				'multiple' => 1,
				'options' => $data,
			)),
		);

		echo localparse($pop_tpl,$vars);
		die();//et mingit jama ei väljastaks
	}


//do+
//tyyp+
//id
//name
//parent

	function contact_makah($arr)
	{
		$this->makah($arr,'contact',CL_CONTACT);
	}

	function isik_makah($arr)
	{
		$this->makah($arr,'isik',CL_ISIK);
	}


	function makah($arr,$tyyp,$class_id)
	{
		extract($arr);
		if ($do=='new')
		{
			$value=$this->new_contact(array('datas'=>array('name'=>$name),'name'=>$name,'parent'=>$parent),$tyyp,$class_id);
		}
		elseif ($do=='delete')
		{
			$this->upd_object(array(
				"oid" => $id,
				'status'=>0,
			));
			$value=0;
		}
		$pop_tpl=$this->get_mk_template();
		$vars=array('msg'=>'ok','tyyp'=>$tyyp,'value'=>$value);
		echo localparse($pop_tpl,$vars);
		die();
	}


	function get_mk_template()
	{

$pop_tpl=<<<TPL
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
		<SCRIPT language=JavaScript>

		$script
				opener.put_value('{VAR:tyyp}','{VAR:value}');
				window.close();
		</SCRIPT>
<title>palun oota$title</tiltle>
</HEAD>

	<BODY>

	$body
	{VAR:msg}

	</BODY>
</HTML>
TPL;
		return $pop_tpl;
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
	function new_contact($arr,$tyyp,$class_id)
	{
		extract($arr);

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


/*//alter table kliendibaas_fimra change column pohitegevus pohitegevus varchar(20);


		$arr=$this->db_fetch_array('
		select t2.pohitegevus as oih, t1.oid as fioid  from kliendibaas_firma as t1, html_import_firmad as t2
		where t1.reg_nr=t2.reg_nr

		');
*/
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
