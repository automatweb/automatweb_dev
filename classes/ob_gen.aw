<?php

/*
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default field=meta
	@default method=serialize

	@property source_table type=select
	@caption source_table

	@property limit type=select
	@caption korraga baasist

	@property source_table type=select
	@caption source table


	@property use_object type=checkbox ch_value=on
	@caption kasuta objektitabelit

	@property my_class_id type=select
	@caption tehakse selle klassi objektid

	@property my_status type=status
	@caption uute objektide aktiivsus vaikimisi

	@property my_parent type=select
	@caption uued objektid salvesta kataloogi:


	@property use_sisu type=checkbox ch_value=on
	@caption kasuta sisutabelit

	@property sisu_table type=select
	@caption sisutabel


	@default group=genereeri

	@property gen_all type=text
	@caption GENEREERI TERVEST ANDMETABELIST OBJEKTID

	@property gen_x type=text
	@caption GENEREERI ANDMETABELIST ESIMESED x OBJEKTI

	@property gen_broken type=text
	@caption JÄTKA KATKENUD IMPORTI

	@default group=obgennn

	@property ob_conf type=text
	@caption testdfgh
	@property multiple type=text


	@default group=log
	@property log_display type=checkbox ch_value=on
	@caption log_display
	@property log_db_table type=checkbox ch_value=on
	@caption log_db_table
	@property log_made_objects type=checkbox ch_value=on
	@caption log_made_objects
	@property log_a_source_field type=checkbox ch_value=on
	@caption log_a_source_field
	@property log_log_warnings type=checkbox ch_value=on
	@caption log_log_warnings

*/

/*
	@property meta
	@property dejoin
	@property multiple
	@property add
*/

class ob_gen extends class_base
{
	function ob_gen()
	{
		$this->init(array(
			'clid' => CL_OB_GEN,
		));
	}


	function set_property($args = array())
	{
		$data = &$args["prop"];
//			print_r($args);die();
		$retval = PROP_OK;
		switch($data["name"])
		{
/*			case 'meta':
			print_r($args);
				$args['prop']['value']=serialize($args['prop']['value']);
			break;*/
			case 'multiple':
				die();
				$args['prop']['value']=serialize($args['prop']['value']);
			break;
		};
		return $retval;
	}


	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = true;

		static $list_tables;
		if (!is_array($list_tables))
		{
			$list_tables=$this->picker_list_db_tables();
  		}
		$id=$args['obj']['oid'];
		$meta=$args['obj']['meta'];

		switch($data["name"])
		{
/*			case '':
				$retval = PROP_IGNORE;
			break;*/
			case 'sisu_table':
   				if (!$args['obj']['meta']['use_sisu'])
				{
					return PROP_IGNORE;
				}
				$data['options'] = $list_tables;
				$data['selected'] = $args['obj']['meta']['sisu_table'];
			break;

			case 'my_status':
				if (!$args['obj']['meta']['use_object'])
				{
					return PROP_IGNORE;
				}
			break;
			case 'my_parent':
				if (!$args['obj']['meta']['use_object'])
				{
					return PROP_IGNORE;
				}
				$par = get_instance("objects");
				$parents = $par->get_list(false,true,50477);//$parents = $par->get_list(false,true);
				$data['options'] = $parents;
				$data['selected'] = $args['obj']['meta']['my_parent'];

			break;

			case 'my_class_id':
				if (!$args['obj']['meta']['use_object'])
				{
					return PROP_IGNORE;
				}

				$list_classes[]=" - ";
				foreach ($this->cfg["classes"] as $key => $val)
				{
					$list_classes[$key]=$val["def"];
				}
				asort($list_classes);

				$data['options'] = $list_classes;
				$data['selected'] = $args['obj']['meta']['my_class_id'];
			break;
			case 'source_table':
				$data['selected'] = $args['obj']['meta']['source_table'];
				$data['options'] = $list_tables;
			break;
			case 'limit':
				$chunks=array("1" => 1,"10" => 10,"100" => 100);
				$data['selected'] = $args['obj']['meta']['limit'];
				$data['options'] = $chunks;
			break;
			case 'ob_conf':
				$data['value'] = $this->callback_get_ob_conf($args['obj']);
			break;

			case 'gen_all':
				$data['value'] =html::href(array(
					'url'=>$this->mk_my_orb('generate_objects', array(
					'id' => $id,
					)),
				'target'=>'_blank',
				'caption'=>' nupp all',
				));
			break;
			case 'gen_x':
				$data['value'] =html::href(array(
					'url'=>$this->mk_my_orb('generate_objects', array(
					'id' => $id,
					'test_limit' => $many=8,
					)),
				'target'=>'_blank',
				'caption'=>' nupp '.$many,
				));
			break;
			case 'gen_broken':
				$source_pointer=$this->db_fetch_field("select max(id)as maks from ".$meta['source_table'],"maks");
				$log_pointer=$this->db_fetch_field('select max(source_id)as maks from ob_gen_log where generator_oid="'.$id.'"','maks');
				if($source_pointer > $log_pointer)
				{
					$data['value'] =html::href(array(
						'url'=>$this->mk_my_orb('generate_objects', array(
						'id' => $id,
						'alg' => $log_pointer,
						)),
					'target'=>'_blank',
					'caption'=>'JÄTKA KATKENUD IMPORTI '.$log_pointer,
					));
				};

			break;
		};
		return $retval;
	}


	// !this gets called when the user clicks on ob_conf
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function callback_get_ob_conf($ob)
	{


		$id=$ob['oid'];

$object_tpl=<<<TPL
		<table border=1><tr><td>
		<select name='object[{VAR:field_name}][{VAR:nr}]'  class="formselect">
		{VAR:object_f}
		</select>
		<input {VAR:dejoin_object} type="checkbox" name="dejoin[object][{VAR:field_name}][{VAR:nr}]" title="leia id teisest tabelist">
		{VAR:dejoin_object_conf}
		<input {VAR:multiple} type="checkbox" name="multiple[object][{VAR:field_name}][{VAR:nr}]" title="multiple values">
		<input {VAR:add} type="checkbox" name="add[object][{VAR:field_name}][{VAR:nr}]" title="extra rida">
		{VAR:d_conf}
		</td></tr></table>
TPL;

$meta_tpl=<<<TPL
		<table border=1><tr><td>
		<input value="{VAR:meta_f}" type="text" name="meta[{VAR:field_name}][{VAR:nr}]" size=8 class="formtext">
		<input {VAR:dejoin_meta} type="checkbox" name="dejoin[meta][{VAR:field_name}][{VAR:nr}]" title="leia id teisest tabelist">
		{VAR:dejoin_meta_conf}
		<input {VAR:multiple} type="checkbox" name="multiple[meta][{VAR:field_name}][{VAR:nr}]" title="multiple values">
		<input {VAR:add} type="checkbox" name="add[meta][{VAR:field_name}][{VAR:nr}]" title="extra rida"><br />
		{VAR:d_conf}
		</td></tr></table>
TPL;

$sisu_tpl=<<<TPL
		<table border=1><tr><td>
		<select name="sisu[{VAR:field_name}][{VAR:nr}]"  class="formselect">
		{VAR:sisu_f}
		</select>
		<input {VAR:dejoin_sisu} type="checkbox" name="dejoin[sisu][{VAR:field_name}][{VAR:nr}]" title="leia id teisest tabelist">
		{VAR:dejoin_sisu_conf}
		<input {VAR:multiple} type="checkbox" name="multiple[sisu][{VAR:field_name}][{VAR:nr}]" title="multiple values">
		<input {VAR:add} type="checkbox" name="add[sisu][{VAR:field_name}][{VAR:nr}]" title="extra rida"><br />
		{VAR:d_conf}
		</td></tr></table>
TPL;

$dejoin_tpl=<<<TPL
<br>
<select name="dejoin_table[{VAR:what}][{VAR:field_name}][{VAR:nr}]" class="formselect" title="sellest tabelist">
	{VAR:dejoin_tables}
</select>
<select name="dejoin_field[{VAR:what}][{VAR:field_name}][{VAR:nr}]" class="formselect" title="see veerg">
	{VAR:dejoin_fields}
</select><br />
TPL;

$remember_tpl=<<<TPL
<input {VAR:remember} type="checkbox" name="remember[{VAR:what}][{VAR:field_name}][{VAR:nr}]">
<small>remember join result</small>
TPL;

$unique_tpl=<<<TPL
	<input {VAR:unique} type="checkbox" name="unique[{VAR:field_name}]">
TPL;


		if ($ob['meta']['sisu_table'])
		{
			$list_sisu_fields=$this->db_get_fieldnames($ob['meta']['sisu_table'],1);
		}

		if ($ob['meta']['dejoin'])
		{
			$list_tables=$this->picker_list_db_tables();
		}

		$object_fields = $this->db_get_fieldnames('objects',1);
		$fnames = $this->db_get_fieldnames($ob['meta']['source_table']);
		$whats=array("object","sisu","meta");

		foreach($fnames as $field)
		{
			$aargh=array();
			foreach($whats as $what)
			{

$ob['meta']['add'][$what][$field][]='';

foreach ($ob['meta']['add'][$what][$field] as $nr => $xxx)
{
				$dejoin_conf[$what]="";
				if ($ob['meta']['dejoin'][$what][$field][$nr])
				{
					if (!$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field][$nr]] && $ob['meta']['dejoin_table'][$what][$field][$nr])
					{
						$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field][$nr]]=$this->db_get_fieldnames($ob['meta']['dejoin_table'][$what][$field][$nr],1);
					}
					else
					{
						$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field][$nr]]=array(0=>" - ");
					}
					$vars=array(
				"field_name" => $field,
						'nr' => $nr,
						"what" => $what,
						"dejoin_tables" => $this->picker($ob['meta']['dejoin_table'][$what][$field][$nr],$list_tables),
						"dejoin_fields" => $this->picker($ob['meta']['dejoin_field'][$what][$field][$nr],$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field][$nr]]),
						"remember" =>checked($ob['meta']['remember'][$what][$field][$nr]),
					);
					$dejoin_conf[$what] = localparse($dejoin_tpl,$vars);
					$oncemore=1;
				}


				$vars=array(
				"field_name" => $field,
					'nr' => $nr,
					"dejoin_".$what => checked($ob['meta']['dejoin'][$what][$field][$nr]),
					"object_f" => $this->picker($ob['meta']['object'][$field][$nr],$object_fields),
					"meta_f" => $ob['meta']['meta'][$field][$nr],
					"sisu_f" => $this->picker($ob['meta']['sisu'][$field][$nr],$list_sisu_fields),
					"add"=>checked($ob['meta']['add'][$what][$field][$nr]),
					'd_conf' =>$dejoin_conf[$what],
				);
				$tpl=$what.'_tpl';
				$aargh[$what] .=  localparse($$tpl, $vars);
}
			}
			$vars=array(
				"field_name" => $field,
				'nr' => $nr,
				"dejoini" => $dejoin,
				"unique" => checked($ob['meta']['unique'][$field]),
			);
			$data[]=array(
				"source" => $field,
				"unique" => localparse($unique_tpl, $vars),
//				"dejoin" => $this->parse("dejoin"),
			)+ $aargh;
		}


		return $this->ob_conf_table($data);

	}


/*
	function submit($arr)
	{
					"oid" => $id,
					"name" => $name,
					"comment" => $comment,
					"metadata" =>array(
						"my_status" => $my_status,
						"limit" => $limit,
						"sisu_table" => $sisu_table,
						"my_parent" => $my_parent,
						"source_table" => $source_table,
						"my_class_id" => $my_class_id,
						"use_object" => $use_object,
						"use_sisu" => $use_sisu,
						"log" => $log,
						"object" => $object,
						"meta" => $meta,
						"sisu" => $sisu,
						"unique" => $unique,
						"dejoin" => $dejoin,
						"add" => $add,
						"dejoin_table" => $dejoin_table,
						"dejoin_field" => $dejoin_field,
						"remember" => $remember,

*/
	function clear_log($arr)
	{
		extract($oid);
		$q="delete from ob_gen_log where generator_oid=$oid";
		//$this->db_query();

	}

	////
	// ! object generator
	// makes new object
	// object - array(
	//		name => value,
	//		class => CL_CLASS_NAME,
	//		parent => int,
	//		status => 0|2,
	//		...
	//	)
	// meta - array (
	//		key => val,
	//		...
	//	)
	// sql - array(
	//		table_name => "name of sql table",
	//		data - array (
	//			column_name => value,
	//			.....
	//			)
	//	)
	//
	// returns oid
	function create_my_object($arr)
	{
//		print "<pre>";
//		print_r($arr);
/*
		extract($arr);

		if (is_array($object))
		{
			$object['metadata']=$meta;
		}
		$object['no_flush']=1;

		//teeme objekti objektitabelisse

		$oid = $this->new_object($object,false);

		// ja kui on andmeid sisu tabeli kohta, siis ka sisutabelisse kirje
		if (is_array($sql))
		{
			extract($sql);
			if (!$table_name)
			{
				break;
			}
			if (is_array($data))
			{
				foreach ($data as $key => $val)
				{
					$fields[]=$key;
					$values[]="'".$val."'";
				}
			}
			$timed=time();
			if ($oid)
			{
				$fields[]="oid";
				$values[]="'".$oid."'";
			}
			else
			{
				//ühesõnaga lisatakse kirje tabelisse ilma oid-ta, kui see ongi soov siis on ok, muidu on jama majas
			}

			$q='insert into '.$table_name.' ('.implode(',',$fields).') values ('.implode(',',$values).')';

			$this->db_query($q);
		}


		$oid=$oid?$oid:$this->db_last_insert_id;
		$this->newcount++;
/**/
		return array('oid'=>$oid, "msg"=>$msg,"timed"=>$timed,);
	}
/**/

//see on kirvemeetodil tabeli normaliseerimine, ei osand praegu kuhugi mujale panna
/*
	function normalizer($arr)
	{
		extract($arr);
//		$ob = $this->get_object($id);
		echo "<pre>";
		$mitu=0;
		$source_table="html_import_firmad";
		$lookup='kliendibaas_toode';
//		$lookup='kliendibaas_tegevusala';
		$field='tooted';
//		$field='tooted';

		$row = $this->db_fetch_row("select max(id),min(id) from ".$source_table);
		$min=$row["min(id)"];
		$max=$row["max(id)"];
//		for ($id=$min;$id<=10;$id++)
		for ($id=$min;$id<=$max;$id++)
		{
			$val = $this->db_fetch_field("select ".$field." from ".$source_table." where id=".$id, $field);

			if ($val)
			{
				$value="";
				switch ($tyyp)
{
case 'get_nrs':
				{
					preg_match_all("/([0-9]{2,})&nbsp;/", $val, $r);// leiab kõik pikemad kui 2digit numbrid
					foreach($r[1] as $key=>$vv)
					{
						//echo $vv;
//						$oooid = $this->db_fetch_field("select oid from ".$lookup." where kood='".$vv."'", "oid");
						$value.=$vv.";";
//						$value.=$oooid.";";
					}
				}
break;

case 'get_nr':

				{
					preg_match("/([0-9]){2,}/", $val, $r);// leiab pikema kui 2digit numbri
					$value = $r[0];
//					$value= $this->db_fetch_field("select oid from ".$lookup." where kood='".$r[0]."'", "oid");
				}
break;

case 'strip_dots':
				$value=trim(str_replace('.','',$val));
break;

}

				$q="update ".$source_table." set ".$field."='".$value."' where id=".$id;
				echo $q.' > ';
//				$this->db_query($q);


				echo $id.">".$value."\n";
				flush();


			}//if val


		}// switch

		echo "</pre>";
		die();
	}

/**/


	function write_log($source_id,$generator_oid,$object_oid,$timed,$msg="",$source_info="",$object_info="")
	{
		$q="insert into ob_gen_log (source_id,generator_oid,object_oid,timed,msg,source_info,object_info)
		values('$source_id','$generator_oid','$object_oid','$timed','$msg','$source_info','$object_info')";
		$this->db_query($q);
	}

	function display_log($source_id,$gen_oid,$ob_oid,$timed,$msg,$source_info="",$object_info="")
	{
		echo "$source_id ($object_info) => made object: $object_oid / message: $msg <br />";
	}



	function get_oid($table,$field,$val)
	{
		$q='select oid from '.$table.' where '.$field.'="'.$val.'" limit 1';
		return $this->db_fetch_field($q, 'oid');
	}

// vat siin hakataksegi neid objekte kokku panema....

	function generate_objects($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
//print_r($ob['meta']['log']);
//		echo "<pre>";
		$alg=$alg?$alg:0;
		if ($alg>0) echo "continuing imort from $alg<br>";else echo "stating import from 0<br>";
//die();
//	echo 	"starting from: ".$this->db_fetch_field("select max(source_id)as maks from ob_gen_log where generator_oid=$id","maks");

		$lopp=$ob['meta']['limit'];
		$this->newcount=0;
		$whats=array("object","sisu","meta");

		if ($ob['meta']['use_object'])
		{
			$object_data=array(
				'class_id'=>$ob['meta']['my_class_id'],
				'parent'=>$ob['meta']['my_parent'],
				'status'=>$ob['meta']['my_status'],
			);
		}

		if ($ob['meta']['log_display']||$ob['meta']['log_db_table'])
		{
			//setup log
		}

		do
		{
			$q="select * from ".$ob['meta']['source_table']." limit $alg,$lopp \n";
//			$q="select ".$ob['meta']['source_table'].".* from ".$ob['meta']['source_table']." as t1 left join ob_gen_log as t2 where t2.source_id==limit $alg,$lopp \n";
			$data=$this->db_fetch_array($q);
			$count2=0;
			$skipped=0;
			if (is_array($data))
			foreach ($data as $row)
			{



				$object=array();
				$sisu=array();
				$meta=array();
				$msg='';
				if($test_limit && ($this->newcount >= $test_limit ))
				{
					break 2; // vsjoo, rohkem pole vaja midagi teha
				}
				$skipit=0;
				if (!is_array($row))
				{
					break; // see select on ammendunud
				}

				$this->quote($row);

				foreach($row as $field=> $val)
				{

					if ($ob['meta']['unique'][$field])
					{
						if ($unique[$field][$val]==$val) //already made that object
						{
							//echo "$val\n";$skipit=1;
//							echo "skipped, already got ".$val."!\n";
							$skipped++;
							continue 2; //juba olemas, järgmine rida andmeid ette
//							break;
						}
						else
						{
							$unique[$field][$val]=$val;
						}

					}

					foreach($whats as $what)
					{

foreach(count($ob['meta']['add'][$what][$field]) as $nr => $xxx)
//$nr=0;
//for ($j=0;$j<(count($ob['meta']['add'][$what][$field])+1);$j++)
{
						if ($ob['meta'][$what][$field][$nr]){
							if ($ob['meta']['dejoin'][$what][$field][$nr] && $ob['meta']['dejoin_table'][$what][$field][$nr] && $ob['meta']['dejoin_field'][$what][$field][$nr] && $val)
							{
								// kui meil on eelnevalt sama asja baasist küsitud
								if ($ob['meta']['remember'][$what][$field][$nr] && $remembered[$what][$field][$nr][$val])
								{
									$$what+=array($ob['meta'][$what][$field][$nr] => $remembered[$what][$field][$val]);
								}
								else
								{
									///vääga geeruline, t2hendab küsitakse teisest tablist oid
if ($ob['meta']['multiple'][$what][$field][$nr])
{
	$vals=explode($val);

	foreach ($vals as $kkk => $vvv)
	{
					$get_oid=$this->get_oid($ob['meta']['dejoin_table'][$what][$field][$nr],$ob['meta']['dejoin_field'][$what][$field][$nr],$vvv);

									if($ob['meta']['remember'][$what][$field][$nr]) //jätame meelde mida baasist küsisime
									{
										$remembered[$what][$field][$nr][$vvv]=$get_oid;
									}
									if (!$get_oid)
									{
										$msg.="could not find oid for '$val' from table ".$ob['meta']['dejoin_table'][$what][$field][$nr]."\n";
									}
$get_oids.=$get_oid.';';

	}
									$$what+=array($ob['meta'][$what][$field][$nr] => $get_oids?$get_oids:NULL);
} else {
			$get_oid=$this->get_oid($ob['meta']['dejoin_table'][$what][$field][$nr],$ob['meta']['dejoin_field'][$what][$field][$nr],$val);
									if($ob['meta']['remember'][$what][$field][$nr]) //jätame meelde mida baasist küsisime
									{
										$remembered[$what][$field][$nr][$val]=$get_oid;
									}
					if (!$get_oid)
									{
										$msg.="could not find oid for '$val' from table ".$ob['meta']['dejoin_table'][$what][$field][$nr]."\n";
									}
									$$what+=array($ob['meta'][$what][$field][$nr] => $get_oid?$get_oid:NULL);
}

								}

							}
							else
							{
								$$what+=array($ob['meta'][$what][$field][$nr] => $val);
							}
						}

//$nr++;
}


					}
				}
				$gen_ob=$this->create_my_object(array(
					"object" =>(array)$object + (array)$object_data,
					"meta" => $meta,
					"sql" =>array(
						"table_name" => $ob['meta']['sisu_table'],
						"data" => $sisu,
					),
				));

				$msg.=$gen_ob['oid']?"":"!!!could not make object";

				if (($ob['meta']['log_log_warnings']&&$msg)||($ob['meta']['log_made_objects']&&$gen_ob['oid']))
				{
					if($ob['meta']['log_db_table'])
					{
						$this->write_log($row['id'],$ob['oid'],$gen_ob['oid'],
							$gen_ob['timed'],$msg,$row['source'],$row[$ob['meta']['log_a_source_field']]);
					}
					if($ob['meta']['log_display'])
					{
						$this->display_log($row['id'],$ob['oid'],$gen_ob['oid'],
							$gen_ob['timed'],$msg,$row['source'],$row[$ob['meta']['log_a_source_field']]);
					}
				}

				flush();
				set_time_limit (30);

				$count2++;
			}

			$alg+=$lopp; //alg=row[id]
//		break;
		} while ($count2 || $skipped);
		$this->flush_cache();
		die("\n\n total objects generated: ".$this->newcount."</pre>");
	}




	function db_get_fieldnames($table,$addempty="",$get_all="")
	{
		$this->db_query('select * from '.$table.' limit 1');
		if($addempty)
		{
			$all[] = ' - ';
		}

		foreach ($this->db_get_fields() as $key => $val)
		{
			$arr=(array)$val;
			if ($get_all)
			{
				$all[$arr['name']]=$arr;
			}
			else
			{
				$all[$arr['name']]=$arr['name'];
			}
		}

		return $all;
	}



	function picker_list_db_tables()
	{
		$this->db_list_tables();
		$list_tables[]=" - ";
		while ($tb = $this->db_next_table())
		{
			$list_tables[$tb]=$tb;
		}
		return $list_tables;
	}


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
		$row["parent"] = $parent;
		unset($row["brother_of"]);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}



	function ob_conf_table($data)
	{
//	print_r($data);
		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "ob_conf_table",
		));

		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");

		$t->define_field(array(
			"name" => "source",
			"caption" => "veerg andmetabelis ".$source_table_name,
			"valign" => "top",
			"nowrap" => "1",
//			"width" => "10",
		));

//		if ($data["object"])
		{
			$t->define_field(array(
				"name" => "object",
				"caption" => "välja nimi objektitabelis",
				"valign" => "top",
				"nowrap" => "1",
//				"width" => "10",
			));
		}

		{
			$t->define_field(array(
				"name" => "meta",
				"caption" => "veeru nimi objektitabeli metas",
				"valign" => "top",
				"nowrap" => "1",
//				"sortable" => 1,
//				"width" => "10",
			));
		}

		{
			$t->define_field(array(
				"name" => "sisu",
				"caption" => "veeru nimi sisutabelis",
				"valign" => "top",
				"nowrap" => "1",
//				"width" => "10",
			));
		}

		{
			$t->define_field(array(
				"name" => "unique",
				"caption" => "unikaalne",
				"valign" => "top",
				"nowrap" => "1",
//				"width" => "10",
			));
		}
/*
		$t->define_field(array(
			"name" => "lyhend",
			"caption" => "lühend",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
			"sortable" => 1,
		));
*/

		$arr = new aw_array($data);

		foreach($arr->get() as $row)
		{
			$t->define_data(//array()
				$row
			);
		}
//		$t->sort_by();
		return $t->draw();
	}
}

?>
