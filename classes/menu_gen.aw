<?php

/*
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default field=meta
	@default method=serialize

	@property tyyp type=select
	@caption tüüp

	@property prefix type=textbox size=10
	@caption nimedele lisatakse eesliide

	@property sufix type=textbox size=10
	@caption nimedele lisatakse järelliide

//	@property destination_path type=objpicker clid=CL_PSEUDO
//	@caption uued kaustad salvesta sellesse kausta
// well tahaks ikka näha ka pathi
	@property destination_path type=select
	@caption uued kaustad salvesta sellesse kausta


	@default group=how

	@property gen_and_deal type=checkbox ch_value=ok
	@caption genereeri kataloogid ja jaga neisse objektid

	@property group_by type=select
	@caption mille järgi objektid kataloogidesse grupeerida

	@property gen_rest_into type=textbox size=8
	@caption tee kataloog, kuhu lähevad objektid, mida ei suudetud grupeerida, muidu objekte ei liigutata

	@property gen_only type=checkbox ch_value=ok
	@caption genereeri ainult kataloogid

	@property deal_excisting type=checkbox ch_value=ok
	@caption jaga objektid olemasolevatesse kataloogidesse



	@default group=misc

	@property save_undo type=checkbox ch_value=saveundo
	@caption save undo

	@property make_catalogs type=href target=_blank editonly=1
	@caption tee kataloogid

	@property undo type=href caption=undo target=_blank editonly=1 url=
	@caption undo last gen


	@default group=analyse

	@property analyse_this type=select size=10 multiple=1
	@caption objektid võta kataloogi(de)st

	@property sub_menus type=checkbox ch_value=ok
	@caption otsi ka alamkataloogidest

	@property show_all_objects type=text
	@caption objektid

*/


define('DOCUT',"<br><br>idee siis selline et teeb mingi parenti<br>alla mingi reegli järgi unniku katalooge<br> ntx A..Z või 0..9, või JAN..DEC, E..P vms<br>
 ja siis kui vaja on siis jaotab parenti all olevad<br> objektid vastavatesse kataloogidesse (ntx nime järgi)<br>");

class menu_gen extends class_base
{

	function menu_gen()
	{
		// change this to the folder under the templates folder, where this classes templates will be
		$this->init(array(
			'tpldir' => 'menu_gen',
			'clid' => CL_MENU_GEN,
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
			case 'group_by':
				$data['options'] =array(
					'name' => 'objekti nimi',
//					'class_id' => 'objekti tüüp',
//					'modified' => 'muutmise aeg',
//					'modifiedby' => 'muutja',
//					'createdby' => 'looja nimi',
//					'created' => 'loomise aeg',
//					'status' => 'aktiivsus',
//					'lang_id' => 'keel',
//					'jrk' => 'järjekord',
//					'site_id' => 'site_id',
//					'' => '',
				);
			break;
			case 'analyse_this':
				$par = get_instance('objects');
				$data['selected'] = $this->make_keys($args['obj']['meta']['analyse_this']);
				$data['options'] = $par->get_list(false,true);
//				$data['options'] = array('0'=>'-');
			break;
			case 'destination_path':
				$par = get_instance("objects");
				$data['selected'] = $this->make_keys($args['obj']['meta']['destination_path']);
				$data['options'] = $par->get_list(false,true);
//				$data['options'] = array('0'=>'-');
			break;
			case 'make_catalogs':
				$data['url'] = $this->mk_my_orb('make_the_catalogs', array('id'=>$args['obj']['oid']));
			break;
			case 'show_all_objects':
				$objects=$this->find_objects_under_parent($args['obj']['meta']['analyse_this'],$args['obj']['meta']['sub_menus'],'',ARR_ALL);

				foreach($objects as $val)
				{
					if ($val['class_id'] == CL_PSEUDO)
					$str.='<b>'.$val['name'].'</b> - '.$this->cfg['classes'][$val['class_id']]['name'].'<br>';
					else
					$str.=$val['name'].' - '.$this->cfg['classes'][$val['class_id']]['name'].'<br>';
				}
				$data['value'] = 'NB! neid objekte liigutatakse!!!(va kataloogid)<br><br>'.$str.
				'<br><b>need kataloogid luuakse</b> <br>'.implode('<br>',$this->catalogs_to_make($args['obj']['meta']));


			break;
			case 'tyyp':
				$data['selected'] = $args['obj']['meta']['tyyp'];
				$data['options'] = array(
					'CAPITAL' => 'objekti nimi (esim. täht) A..Z',
//					'' => 'objekti nimi (3 tähte)',
//					'WEEKDAY' => 'nädalapäevad (E-P)',
//					'' => 'nädalapäevad (esmaspäev-pühapäev)',
//					'MONTH' => 'kuu (jaanuar - detsember)',
//					'YEAR' => 'aasta',
//					'CREATEDBY' => 'looja nimi',
//					'MODIFIEDBY' => 'muutja nimi',
//					'JRK' => 'järjekorra nr',
//					'CLASS_ID' => 'tüübi nimi',
					'NUM_09' => '0..9',
				);
			break;

		}
		return $retval;
	}



// source_cats - list of catalogs
// full - sub catalogs

	function find_objects_under_parent($source_cats=array(),$full,$class='',$ret=ARR_ALL)
	{
		$objects=array();
		foreach($source_cats as $parent)
		{
			$objects+=$this->get_objects_below($args = array(
				'parent' => $parent,
				'class' => $class, //
	//			'type' => MN_CONTENT,
				'active' => false,
				'orderby' => 'name',
				'full' => $full,
				'ret' => $ret,
			));
		};
		return $objects;
	}


	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "plaa":
			break;

		};
		return $retval;
	}


	function catalogs_to_make($arr)
	{
		extract($arr);

		$names=array();

		if ($tyyp == 'NUM_09')
		{
			$names+=$this->make_keys(array('0','1','2','3','4','5','6','7','8','9'));
		}

		if ($tyyp == 'CAPITAL')
		{
			$names+=$this->make_keys(array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','Õ','Ä','Ö','Ü','X','Y','Z'));
//			$names+=array('Õ'=>'ÕÄÖÜ','Ä'=>'ÕÄÖÜ','Ö'=>'ÕÄÖÜ','Ü'=>'ÕÄÖÜ','X'=>'XYZ','Y'=>'XYZ','Z'=>'XYZ');
		}

		if ($tyyp == 'MONTH')
		{
			$names+=array(1 => 'jaanuar', 2=> 'veebruar', 3=> 'märts', 4=> 'aprill',  5=> 'mai', 6=> 'juuni', 7=> 'juuli', 8=> 'august', 9=> 'september', 10=> 'oktoober', 11=> 'november', 12=> 'detsember');
		}

		if ($tyyp == 'WEEKDAY')
		{
			$names+=array('E'=>'esmaspäev','T'=>'teisipäev','K'=>'kolmapäev','N'=>'neljapäev','R'=>'reede','L'=>'laupäev','P'=>'pühapäev');
		}

		if ($tyyp == 'YEAR')
		{
			$names+=$this->make_keys(array('2002','2003'));
		}

		if ($tyyp == 'CLASS_ID')
		{
			foreach($this->cfg['classes'] as $val)
			{
//				$CREATE[$val[]]=$val['name'];
			}
			print_r($val);
		}

		if ($tyyp == 'DEFINED')
		{

			$names+= $defined_catalogs;
		}

		if ($gen_rest_into)
		{
			$names+=array('rest'=>$gen_rest_into);
		}

		return $names;
	}

	function assign_catalog($obj,$made,$group_by,$gen_rest_into)
	{

		switch($group_by)
		{
			case 'name':
				$upper = strtr(strtoupper($obj['name'][0]), "õäöü", "ÕÄÖÜ");
				$new_cat=$made[$upper]['id'];
			break;
			case 'class_id':
				$new_cat=$made[$obj['class_id']]['id'];
			break;
			case 'modified':
/*
				switch($)
				{
				 case 'YEAR':
				 	//get$obj['modified'];
				 break;
				 case 'MONTH':
				 	//get$obj['modified'];
				 break;
				 case 'WEEKDAY':
				 	//get$obj['modified'];
				 break;
				 }
*/
			break;
			case 'lang_id':
				$new_cat=$made[$obj['lang_id']]['id'];
			break;
			case '':
			case 'jrk':
				$new_cat=$made[$obj['jrk']]['id'];
			break;
			case 'blaa':

			break;

		}
/*					'modified' => 'muutmise aeg',
					'modifiedby' => 'muutja',
					'createdby' => 'looja nimi',
					'created' => 'loomise aeg',
//					'status' => 'aktiivsus',
					'site_id' => 'site_id',
*/

			if((!$new_cat) && $made[$gen_rest_into])
			{
				$new_cat = $made[$gen_rest_into]['id'];
			}
		return $new_cat;
	}


 	function make_the_catalogs($arr)
	{
		extract($arr);
		$obj=$this->get_object($id);
		$objects=$this->find_objects_under_parent($obj['meta']['analyse_this'],$obj['meta']['sub_menus']);//CL_PSEUDO

		$CREATE=$this->catalogs_to_make($obj['meta']);

		if ($obj['meta']['save_undo'])
		{
			$str='hää';
		}
		$jrk=0;
		if ($obj['meta']['gen_and_deal'] || $obj['meta']['gen_only'])
		{
			$mn = get_instance('menuedit');
			foreach ($CREATE as $key => $name)
			{
				$jrk++;
			 	$new_cat = $mn->add_new_menu(array(
					'name' => $obj['meta']['prefix'].$name.$obj['meta']['sufix'],
					'parent' => $obj['meta']['destination_path'],
					'type' => MN_CONTENT,
					'status' => 2,
					'jrk' => $jrk,
					'no_flush' => 1,
				));
				echo $new_cat.' - '.$name.'<br>' ;
				$made[$key]=array('id' => $new_cat,'name' => $name);
				set_time_out(20);
				flush();
			}
		}
		elseif($obj['meta']['deal_excisting'])
		{
			$mde=$this->find_objects_under_parent($obj['meta']['analyse_this'],false,CL_PSEUDO,ARR_NAME);
			foreach ($mde as $key => $name)
			{
				$made[$name]=array('oid' => $key,'name' => $name);
			}
		}

		if ($obj['meta']['deal_excisting'] || $obj['meta']['gen_and_deal'])
		{
			foreach($objects as $key => $val)
			{
				if ($val['class_id'] == CL_PSEUDO)
				{
					continue;
				}
				$where_to=$this->assign_catalog($val,$made,$obj['meta']['group_by'],$obj['meta']['gen_rest_into']); //object,available catalogs,grouping
				echo "{$val['name']} - $key >> $where_to<br>";
				if ($where_to)
				{
					$this->upd_object(array('oid'=>$key,'parent'=>$where_to));
					set_time_out(20);
				}
			}
		}
		$this->flush_cache();
		die();
	}

	/*
	////
	// !generates the toolbar for this class
	// default toolbar includes only one button - save button
	function mk_toolbar()
	{
		$tb = get_instance('toolbar');

		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => 'Salvesta',
			'url' => 'javascript:document.add.submit()',
			'imgover' => 'save_over.gif',
			'img' => 'save.gif'
		));

		return $tb->get_toolbar();
	}
/**/

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

}
?>
