<?php

/*
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default field=meta
	@default method=serialize

	@property tyyp type=select
	@caption t¸¸p

	@property prefix type=textbox size=10
	@caption nimedele lisatakse eesliide

	@property sufix type=textbox size=10
	@caption nimedele lisatakse j‰relliide

//	@property destination_path type=objpicker clid=CL_PSEUDO
//	@caption uued kaustad salvesta sellesse kausta
// well tahaks ikka n‰ha ka pathi
	@property destination_path type=select
	@caption uued kaustad salvesta sellesse kausta

////////////////////////////////////////////////////////////////////////////
	@default group=how

//	@property gen_and_deal type=checkbox ch_value=ok
//	@caption genereeri kataloogid ja jaga neisse objektid

	@property group_by type=select
	@caption mille j‰rgi objektid kataloogidesse grupeerida

//	@property gen_rest_into type=textbox size=8
//	@caption tee kataloog, kuhu l‰hevad objektid, mida ei suudetud grupeerida, muidu objekte ei liigutata

	@property gen_only type=checkbox ch_value=ok
	@caption genereeri ainult kataloogid

//	@property deal_excisting type=checkbox ch_value=ok
//	@caption jaga objektid olemasolevatesse kataloogidesse

////////////////////////////////////////////////////////////////////////////
	@default group=misc

//	@property save_undo type=checkbox ch_value=saveundo
//	@caption save undo

	@property make_catalogs type=text
	@caption tee kataloogid

//	@property undo type=href caption=undo target=_blank editonly=1 url=
//	@caption undo last gen

//	@property analyse_this type=select size=10 multiple=1
//	@caption objektid vıta kataloogi(de)st

////////////////////////////////////////////////////////////////////////////
	@default group=analyse


	@property sub_menus type=checkbox ch_value=ok
	@caption otsi ka alamkataloogidest

	@property show_all_objects type=text
	@caption objektid
////////////////////////////////////////////////////////////////////////////

*/


define('DOCUT',"<br /><br />idee siis selline et teeb mingi parenti<br />alla mingi reegli j‰rgi unniku katalooge<br /> ntx A..Z vıi 0..9, vıi JAN..DEC, E..P vms<br />
 ja siis kui vaja on siis jaotab parenti all olevad<br /> objektid vastavatesse kataloogidesse (ntx nime j‰rgi)<br />");

class menu_gen extends class_base
{

	function menu_gen()
	{
		// change this to the folder under the templates folder, where this classes templates will be
		$this->init(array(
//			'tpldir' => 'menu_gen',
			'clid' => CL_MENU_GEN,
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		$meta=$args['obj']['meta'];

		switch($data["name"])
		{
			case 'group_by':

				if ($meta['gen_and_deal'] || $meta['deal_excisting'])
				{

					$data['options'] =array(
					'name' => 'objekti nimi',
//					'class_id' => 'objekti t¸¸p',
//					'modified' => 'muutmise aeg',
//					'modifiedby' => 'muutja',
//					'createdby' => 'looja nimi',
//					'created' => 'loomise aeg',
//					'status' => 'aktiivsus',
//					'lang_id' => 'keel',
//					'jrk' => 'j‰rjekord',
//					'site_id' => 'site_id',
//					'' => '',
					);
				}
				else
				{
					$retval=PROP_IGNORE;
				}
			break;
			case 'analyse_this':
				$par = get_instance('objects');
				$data['selected'] = $this->make_keys($meta['analyse_this']);
				$data['options'] = $par->get_list(false,true);
//				$data['options'] = array('0'=>'-');
			break;
			case 'destination_path':
				$par = get_instance("objects");
				$data['selected'] = $this->make_keys($meta['destination_path']);
				$data['options'] = $par->get_list(false,true);
//				$data['options'] = array('0'=>'-');
			break;
			case 'make_catalogs':
				$url = $this->mk_my_orb('make_the_catalogs', array('id'=>$args['obj']['oid']));
				$data['value'] = html::href(array('url'=>$url,'caption'=>'tee kataloogid', 'target'=>'_blank'));

			break;
			case 'show_all_objects':
				$objects=$this->find_objects_under_parent($meta['analyse_this'],$meta['sub_menus'],'',ARR_ALL);

				$clss = aw_ini_get("classes");
				foreach($objects as $val)
				{
					if ($val['class_id'] == CL_PSEUDO)
					{
						$str.='<b>'.$val['name'].'</b> - '.$clss[$val['class_id']]['name'].'<br />';
					}
					else
					{
						$str.=$val['name'].' - '.$clss[$val['class_id']]['name'].'<br />';
					}
				}
				$data['value'] = 'NB! neid objekte liigutatakse!!!(va kataloogid)<br /><br />'.$str.
				'<br /><b>need kataloogid luuakse</b> <br />'.implode('<br />',$this->complete_names($this->catalogs_to_make($meta),$meta['prefix'],$meta['sufix']));

			break;
			case 'tyyp':
				$data['selected'] = $meta['tyyp'];
				$data['options'] = array(
					'CAPITAL' => 'objekti nimi (esim. t‰ht) A..Z',
//					'' => 'objekti nimi (3 t‰hte)',
//					'WEEKDAY' => 'n‰dalap‰evad (E-P)',
//					'' => 'n‰dalap‰evad (esmasp‰ev-p¸hap‰ev)',
//					'MONTH' => 'kuu (jaanuar - detsember)',
//					'YEAR' => 'aasta',
//					'CREATEDBY' => 'looja nimi',
//					'MODIFIEDBY' => 'muutja nimi',
//					'JRK' => 'j‰rjekorra nr',
//					'CLASS_ID' => 't¸¸bi nimi',
					'NUM_09' => '0..9',
				);
			break;

		}
		return $retval;
	}



// source_cats - list of catalogs
// full - sub catalogs

	function find_objects_under_parent($source_cats,$full,$class='',$ret=ARR_ALL)
	{
		$objects=array();
		$source_cats=(array)$source_cats;
		foreach($source_cats as $parent)
		{
			$ol = new object_list(array(
				'parent' => $parent,
				'class_id' => $class, //
				'status' => STAT_NOTACTIVE,
				'sort_by' => 'objects.name',
			));
			$objects+=$ol->names();
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
			$names+=$this->make_keys(array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','’','ƒ','÷','‹','X','Y','Z'));
//			$names+=array('’'=>'’ƒ÷‹','ƒ'=>'’ƒ÷‹','÷'=>'’ƒ÷‹','‹'=>'’ƒ÷‹','X'=>'XYZ','Y'=>'XYZ','Z'=>'XYZ');
		}

		if ($tyyp == 'MONTH')
		{
			$names+=array(1 => 'jaanuar', 2=> 'veebruar', 3=> 'm‰rts', 4=> 'aprill',  5=> 'mai', 6=> 'juuni', 7=> 'juuli', 8=> 'august', 9=> 'september', 10=> 'oktoober', 11=> 'november', 12=> 'detsember');
		}

		if ($tyyp == 'WEEKDAY')
		{
			$names+=array('E'=>'esmasp‰ev','T'=>'teisip‰ev','K'=>'kolmap‰ev','N'=>'neljap‰ev','R'=>'reede','L'=>'laup‰ev','P'=>'p¸hap‰ev');
		}

		if ($tyyp == 'YEAR')
		{
			$names+=$this->make_keys(array('2002','2003'));
		}

		if ($tyyp == 'CLASS_ID')
		{
			$clss = aw_ini_get("classes");
			foreach($clss as $val)
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

		return //$this->complete_names($names,$prefix,$sufix);//
		$names;

	}

	function name_appender(&$item1, $key, $fix)
	{
		$item1 = $fix[0].$item1.$fix[1];
	}


	function complete_names($names,$prefix,$sufix)
	{
		array_walk($names, array($this,'name_appender'), array($prefix,$sufix));
		return $names;
	}


	function assign_catalog($obj,$made,$group_by,$gen_rest_into)
	{

		switch($group_by)
		{
			case 'name':
				$upper = strtr(strtoupper($obj['name'][0]), "ı‰ˆ¸", "’ƒ÷‹");
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


	/**  
		
		@attrib name=make_the_catalogs params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
 	function make_the_catalogs($arr)
	{
		extract($arr);
		$obj=obj($id);
		$meta=$obj->meta();

		$objects=$this->find_objects_under_parent($meta['analyse_this'],$meta['sub_menus']);//CL_PSEUDO

		$CREATE=$this->complete_names($this->catalogs_to_make($meta),$meta['prefix'],$meta['sufix']);

		if ($meta['save_undo'])
		{
			$str='h‰‰';
		}
		$jrk=0;
		if ($meta['gen_and_deal'] || $meta['gen_only'])
		{
			echo "parent = {$meta['destination_path']} <br />";
			$mn = get_instance('menuedit');
			foreach ($CREATE as $key => $name)
			{
				$jrk++;

				$new_cat = $mn->add_new_menu(array(
					'name' => $name,
					'parent' => $meta['destination_path'],
					'type' => MN_CONTENT,
					'status' => 2,
					'jrk' => $jrk,
					'no_flush' => 1,
				));/**/
				echo $jrk.'. oid('.$new_cat.') - '.$name.'<br />' ;
				$made[$key]=array('id' => $new_cat,'name' => $name);
				//settimeout(20);
				flush();
			}
			$this->flush_cache();
		}
		elseif($meta['deal_excisting'])
		{
			$mde=$this->find_objects_under_parent($meta['analyse_this'],false,CL_PSEUDO,ARR_NAME);
			foreach ($mde as $key => $name)
			{
				$made[$name]=array('oid' => $key,'name' => $name);
			}
		}
		die();
	}
}
?>
