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

///////////////////////////////////////////////////////////////

	@default group=genereeri

	@property gen_all type=text
	@caption GENEREERI TERVEST ANDMETABELIST OBJEKTID

	@property gen_x type=text
	@caption GENEREERI ANDMETABELIST ESIMESED x OBJEKTI

	@property gen_broken type=text
	@caption JÄTKA KATKENUD IMPORTI



///////////////////////////////////////////////////////////////
	@default group=obgennn
	@property ob_conf type=text

//	@property multiple type=text

	@property dejoin type=textbox
	@property multiple type=textbox
	@property add type=textbox

	@property dejoin_table type=textbox
	@property dejoin_field type=textbox
	@property remember type=textbox

	@property object type=textbox
	@property meta type=textbox
	@property sisu type=textbox
	@property unique type=textbox

///////////////////////////////////////////////////////////////

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
		$data = &$args['prop'];
		$retval = PROP_OK;
		switch($data['name'])
		{

		};
		return $retval;
	}



	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;

		static $list_tables;
		if (!is_array($list_tables))
		{
			$list_tables=$this->picker_list_db_tables();
  		}
		$id=$args['obj']['oid'];
		$meta=$args['obj']['meta'];

		switch($data['name'])
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

			case 'dejoin_table':
				return PROP_IGNORE;
			break;
			case 'dejoin_field':
				return PROP_IGNORE;
			break;
			case 'remember':
				return PROP_IGNORE;
			break;


			case 'alias':
				return PROP_IGNORE;
			break;

			case 'status':
//				return PROP_IGNORE;
			break;

			case 'unique':
				return PROP_IGNORE;
			break;
			case 'object':
				return PROP_IGNORE;
			break;
			case 'meta':
				return PROP_IGNORE;
			break;

			case 'sisu':
				return PROP_IGNORE;
			break;
			case 'dejoin':
				return PROP_IGNORE;
			break;
			case 'multiple':
				return PROP_IGNORE;
			break;
			case 'add':
				return PROP_IGNORE;
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
				$par = get_instance('objects');
				$parents = $par->get_list(false,true,50477);//$parents = $par->get_list(false,true);
				$data['options'] = $parents;
				$data['selected'] = $args['obj']['meta']['my_parent'];

			break;

			case 'my_class_id':
				if (!$args['obj']['meta']['use_object'])
				{
					return PROP_IGNORE;
				}

				$list_classes[]=' - ';
				$clss = aw_ini_get("classes");
				foreach ($clss as $key => $val)
				{
					if ($val['name'])
					{
						$list_classes[$key]=$val['name'];
					}
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
				$chunks=array('1' => 1,'10' => 10,'100' => 100);
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
				$source_pointer=$this->db_fetch_field('select max(id)as maks from '.$meta['source_table'],'maks');
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


	function callback_get_ob_conf($ob)
	{
	$id=$ob['oid'];
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
		$whats=array('object','sisu','meta');

		foreach($fnames as $field)
		{
			$aargh=array();
			foreach($whats as $what)
			{

				$ob['meta']['add'][$what][$field][]='';

			foreach ($ob['meta']['add'][$what][$field] as $nr => $xxx)
			{
				$dejoin_conf[$what]='';
				if ($ob['meta']['dejoin'][$what][$field][$nr])
				{
					if (!$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field][$nr]] && $ob['meta']['dejoin_table'][$what][$field][$nr])
					{
						$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field][$nr]]=$this->db_get_fieldnames($ob['meta']['dejoin_table'][$what][$field][$nr],1);
					}
					else
					{
						$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field][$nr]]=array(0=>' - ');
					}
					$dejoin_conf[$what] =
'<br />'.
html::select(array('name'=>'dejoin_table['.$what.']['.$field.']['.$nr.']','selected'=>$ob['meta']['dejoin_table'][$what][$field][$nr],'options'=>$list_tables)).
html::select(array('name'=>'dejoin_field['.$what.']['.$field.']['.$nr.']','selected'=>$ob['meta']['dejoin_field'][$what][$field][$nr],'options'=>$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field][$nr]])).
html::checkbox(array('name'=>'remember['.$what.']['.$field.']['.$nr.']','checked'=>$ob['meta']['remember'][$what][$field][$nr])).
'<small>remember join result</small><br />';
					$oncemore=1;
				}

				$aargh[$what].='<table border=1><tr><td>';

				switch($what)
				{
					case 'sisu':
						$aargh[$what].= html::select(array(
							'name'=>'sisu['.$field.']['.$nr.']',
							'options' => $list_sisu_fields,
							'selected' => $ob['meta']['sisu'][$field][$nr],
						));
					break;
					case 'meta':
						$aargh[$what].= html::textbox(array(
							'name'=>'meta['.$field.']['.$nr.']',
							'value'=>$ob['meta']['meta'][$field][$nr],
							'size'=>9
						));
					break;
					case 'object':
						$aargh[$what].= html::select(array(
							'name'=>'object['.$field.']['.$nr.']',
							'options' => $object_fields,
							'selected' => $ob['meta']['object'][$field][$nr]
						));
					break;
				}
				$aargh[$what].= html::checkbox(array(
						'name'=>'dejoin['.$what.']['.$field.']['.$nr.']',
						'checked'=>$ob['meta']['dejoin'][$what][$field][$nr]
					)).'leia id teisest tabelist'.
					html::checkbox(array(
						'name'=>'add['.$what.']['.$field.']['.$nr.']',
						'checked'=>$ob['meta']['add'][$what][$field][$nr]
					)).'extra rida'.
				$dejoin_conf[$what].
				'</td></tr></table>';
			}
			
			}
/*			$vars=array(
				'field_name' => $field,
				'nr' => $nr,
				'dejoini' => $dejoin,
				'unique' => checked($ob['meta']['unique'][$field]),
			);*/

			$data[]=array(
				'source' => $field,
				'unique' => html::checkbox(array('name' => 'unique['.field.']', 'value' =>$ob['meta']['unique'][$field])),

//				'dejoin' => $this->parse('dejoin'),
			)+ $aargh;
		}


		return ''.$this->ob_conf_table($data);

	}


	/**  
		
		@attrib name=clear_log params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
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

		extract($arr);

		if (is_array($object))
		{
			$object['metadata']=$meta;
		}
		$object['no_flush']=1;

		//teeme objekti objektitabelisse
		$o = obj();
		$o->set_parent($object["parent"]);
		$o->set_name($object["name"]);
		$o->set_class_id($object["class_id"]);
		if (is_array($object["meta"]))
		{
			foreach($object["meta"] as $k => $v)
			{
				$o->set_meta($k, $v);
			}
		}
		$oid = $o->save();

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
	/**  
		
		@attrib name=generate_objects params=name default="0"
		
		@param id required
		@param alg optional
		@param test_limit optional
		
		@returns
		
		
		@comment

	**/
	function generate_objects($arr)
	{
		extract($arr);
		$ob = obj($id);
//		echo "<pre>";
		$alg=$alg?$alg:0;
		if ($alg>0) echo "continuing imort from $alg<br />";else echo "stating import from 0<br />";
//die();
//	echo 	"starting from: ".$this->db_fetch_field("select max(source_id)as maks from ob_gen_log where generator_oid=$id","maks");

		$lopp=$ob->meta('limit');
		$this->newcount=0;
		$whats=array("object","sisu","meta");

		if ($ob->meta('use_object'))
		{
			$object_data=array(
				'class_id'=>$ob->meta('my_class_id'),
				'parent'=>$ob->meta('my_parent'),
				'status'=>$ob->meta('my_status'),
			);
		}

		do
		{
			$q="select * from ".$ob->meta('source_table')." limit $alg,$lopp \n";
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
					$tmp = $ob->meta('unique');
					if ($tmp[$field])
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
						$tmp = $ob->meta('add');
						$mt = $ob->meta();
						foreach($tmp[$what][$field] as $nr => $xxx)
						{
						if ($mt[$what][$field][$nr]){
							if ($mt['dejoin'][$what][$field][$nr] && $mt['dejoin_table'][$what][$field][$nr] && $mt['dejoin_field'][$what][$field][$nr] && $val)
							{
								// kui meil on eelnevalt sama asja baasist küsitud
								if ($mt['remember'][$what][$field][$nr] && $remembered[$what][$field][$nr][$val])
								{
									$$what+=array($mt[$what][$field][$nr] => $remembered[$what][$field][$val]);
								}
								else
								{
									///vääga geeruline, t2hendab küsitakse teisest tablist oid
if ($mt['multiple'][$what][$field][$nr])
{
	$vals=explode($val);

	foreach ($vals as $kkk => $vvv)
	{
					$get_oid=$this->get_oid($mt['dejoin_table'][$what][$field][$nr],$mt['dejoin_field'][$what][$field][$nr],$vvv);

									if($mt['remember'][$what][$field][$nr]) //jätame meelde mida baasist küsisime
									{
										$remembered[$what][$field][$nr][$vvv]=$get_oid;
									}
									if (!$get_oid)
									{
										$msg.="could not find oid for '$val' from table ".$mt['dejoin_table'][$what][$field][$nr]."\n";
									}
$get_oids.=$get_oid.';';

	}
									$$what+=array($mt[$what][$field][$nr] => $get_oids?$get_oids:NULL);
} else {
			$get_oid=$this->get_oid($mt['dejoin_table'][$what][$field][$nr],$mt['dejoin_field'][$what][$field][$nr],$val);
									if($mt['remember'][$what][$field][$nr]) //jätame meelde mida baasist küsisime
									{
										$remembered[$what][$field][$nr][$val]=$get_oid;
									}
					if (!$get_oid)
									{
										$msg.="could not find oid for '$val' from table ".$mt['dejoin_table'][$what][$field][$nr]."\n";
									}
									$$what+=array($mt[$what][$field][$nr] => $get_oid?$get_oid:NULL);
}

								}

							}
							else
							{
								$$what+=array($mt[$what][$field][$nr] => $val);
							}
						}
}

					}
				}
				$gen_ob=$this->create_my_object(array(
					"object" =>(array)$object + (array)$object_data,
					"meta" => $meta,
					"sql" =>array(
						"table_name" => $ob->meta('sisu_table'),
						"data" => $sisu,
					),
				));

				$msg.=$gen_ob['oid']?"":"!!!could not make object";

				if (($ob->meta('log_log_warnings')&&$msg)||($ob->meta('log_made_objects')&&$gen_ob['oid']))
				{
					if($ob->meta('log_db_table'))
					{
						$this->write_log($row['id'],$ob->id(),$gen_ob['oid'],
							$gen_ob['timed'],$msg,$row['source'],$row[$ob->meta('log_a_source_field')]);
					}
					if($ob->meta('log_display'))
					{
						$this->display_log($row['id'],$ob->id(),$gen_ob['oid'],
							$gen_ob['timed'],$msg,$row['source'],$row[$ob->meta('log_a_source_field')]);
					}
				}

				flush();
				set_time_limit (30);

				$count2++;
			}

			$alg+=$lopp;
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
		$tables[]=" - ";
		$all_db_tables=$this->db_query('show tables');
		while ($row = $this->db_next())
		{
			$tables[$row['Tables_in_'.$GLOBAL['db']['base']]] = $row['Tables_in_'.$GLOBAL['db']['base']];
		}
		return $tables;
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
			));
		}

		{
			$t->define_field(array(
				"name" => "meta",
				"caption" => "veeru nimi objektitabeli metas",
				"valign" => "top",
			));
		}

		{
			$t->define_field(array(
				"name" => "sisu",
				"caption" => "veeru nimi sisutabelis",
				"valign" => "top",
			));
		}

		{
			$t->define_field(array(
				"name" => "unique",
				"caption" => "unikaalne",
				"valign" => "top",
			));
		}

		$arr = new aw_array($data);

		foreach($arr->get() as $row)
		{
			$t->define_data(//array()
				$row
			);
		}
		return '<table border=1>'.$t->draw().'</table>';
	}
}

?>
