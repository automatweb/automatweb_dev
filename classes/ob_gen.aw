<?php

class ob_gen extends aw_template
{
	////////////////////////////////////
	// the next functions are REQUIRED for all classes that can be added from menu editor interface
	////////////////////////////////////

	function ob_gen()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("ob_gen");
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	//    id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			if($do=="change")
			{
				$this->upd_object(array(
					"oid" => $id,
					"name" => $name,
					"comment" => $comment,
					"metadata" =>array(
						"status" => $status,
						"limit" => $limit,
						"sisu_table" => $sisu_table,
						"save_to_parent" => $save_to_parent,
						"source_table" => $source_table,
						"class_id" => $class_id,
						"use_object" => $use_object,
						"use_sisu" => $use_sisu,
					),
				));
			}
			elseif($do=="log_setup")
			{
				$this->upd_object(array(
					"oid" => $id,
					"metadata" =>array(
						"log" => $log,
					),
				));
			}
			else
			{
				$this->upd_object(array(
					"oid" => $id,
					"metadata" =>array(
						"object" => $object,
						"meta" => $meta,
						"sisu" => $sisu,
						"unique" => $unique,
						"dejoin" => $dejoin,
						"dejoin_table" => $dejoin_table,
						"dejoin_field" => $dejoin_field,
						"remember" => $remember,
					),
				));

			}
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => CL_OB_GEN,
				"metadata" =>array(
					"limit" => $limit,
					"source_table" => $source_table,
					"use_object" => $use_object,
					"use_sisu" => $use_sisu,
				),
			));
			$do="change";
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb($do, array("id" => $id, "return_url" => urlencode($return_url)));
	}

	////
	// ! object generator
	// makes new object 
	// object - array(
	// 	name => value,
	//	class => CL_CLASS_NAME,
	//	parent => int,
	//	status => 0|2,
	//	...
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
		print_r($arr);
		/*
		extract($arr);

		if (is_array($object))
		{
			$object['metadata']=$meta;
		}
		//teeme objekti objektitabelisse
		$oid = $this->new_object($object);

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
		*/		$this->newcount++;
		return array('oid'=>$oid, "msg"=>$msg, );
	}


	function normalizer($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
//		echo "<pre>";	
		
		$ob['meta']['source_table']="testtest";
		$field="tekst;
		$row = $this->db_fetch_row("select max(id),min(id) from ".$ob['meta']['source_table'], "id");
		$max = $this->db_fetch_row("select id from ".$ob['meta']['source_table']." where id=min(id)", "id");			

		for ($id=$min;$id<=$max;$id++)
		{
			$val = $this->db_fetch_field("select $field from ".$ob['meta']['source_table']." where id=$id");
			if ($val)
			{
					//process val
				$val="juhhaidii";
				$q="update ".$ob['meta']['source_table']." set $field=$val where id=$id";
				$this->db_query($q);
			}
		}
//		echo "</pre>";			
		die();
	}
	
	function generate_objects($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
//		echo "<pre>";
		$alg=0;
		$lopp=$ob['meta']['limit'];
		$this->newcount=0;
		$whats=array("object","sisu","meta");			

		if ($ob['meta']['use_object'])
		{
			$object_data=array(
				'class_id'=>$ob['meta']['class_id'],
				'parent'=>$ob['meta']['parent'],
				'status'=>$ob['meta']['status'],
			);
		}

		if ($ob['meta']['log']['display']||$ob['meta']['log']['db_table'])
		{
			//setup log
		}



		do
		{
			$q="select * from ".$ob['meta']['source_table']." limit $alg,$lopp \n";

			$data=$this->db_fetch_array($q);
			$count2=0;
			$skipped=0;
			if (is_array($data))
			foreach ($data as $row)
			{
				$object=array();
				$sisu=array();
				$meta=array();
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

				foreach($row as $key=> $val)
				{

				if ($ob['meta']['unique'][$key])
					{
						if ($unique[$val]==$val) //already made that object
						{
							//echo "$val\n";$skipit=1;
							echo "skipped, already got ".$val."!\n";
							$skipped++;
							continue 2; //juba olemas, järgmine rida andmeid ette
//							break;
						}
						else
						{
							$unique[$val]=$val;
						}
						
					}

					foreach($whats as $what)
					{
						if ($ob['meta'][$what][$key]){
						if ($ob['meta']['dejoin'][$what][$key] && $ob['meta']['dejoin_table'][$what][$key] && $ob['meta']['dejoin_field'][$what][$key] && $val)
						{

							// kui meil on eelnevalt sama asja baasist küsitud
							if ($ob['meta']['remember'][$what][$key] && $remembered[$what][$key][$val])
							{
								$$what+=array($ob['meta'][$what][$key] => $remembered[$what][$key][$val]);
							}
							else
							{
								$q='select oid from '.$ob['meta']['dejoin_table'][$what][$key].' where '.$ob['meta']['dejoin_field'][$what][$key].'="'.$val.'" limit 1';

								$get_oid=$this->db_fetch_field($q, 'oid');

								if($ob['meta']['remember'][$what][$key]) //jätame meelde mida baasist küsisime
								{
									$remembered[$what][$key][$val]=$get_oid;
								}
	
								$$what+=array($ob['meta'][$what][$key] => $get_oid?$get_oid:NULL);
							}

						}
						else
						{
							$$what+=array($ob['meta'][$what][$key] => $val);
						}
						}

					}
				}
					$ok=$this->create_my_object(array(
						"object" =>(array)$object + (array)$object_data,
						"meta" => $meta,
						"sql" =>array(
							"table_name" => $ob['meta']['sisu_table'],
							"data" => $sisu,
						),
					));

					echo "created: ".$ok."\n";

					flush();
					set_time_limit (30);

				$count2++;
			}

			$alg+=$lopp;
//		break;
		} while ($count2 || $skipped);
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



	////
	// ! make toolbar
	// oid - if not set only save button will be shown
	// got_source bool if true add obj conf tab and log_setup tab
	// return_url
	function my_toolbar($arr)
	{
		extract($arr);
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));
		if ($oid)
		{
			$toolbar->add_button(array(
				"name" => "change",
				"tooltip" => "üld määrangud",
				"url" => $this->mk_my_orb("change",array("id" => $oid,"return_url" => urlencode($return_url))),
				"imgover" => "settings_over.gif",
				"img" => "settings.gif",
			));

			if ($got_source)
			{
				$toolbar->add_button(array(
					"name" => "ob_conf",
					"tooltip" => "objekti conf",
					"url" => $this->mk_my_orb("ob_conf",array("id" => $oid,"return_url" => urlencode($return_url))),
					"imgover" => "promo_over.gif",
					"img" => "promo.gif",
				));
				$toolbar->add_button(array(
					"name" => "log_setup",
					"tooltip" => "logi setup",
					"url" => $this->mk_my_orb("log_setup",array("id" => $oid,"return_url" => urlencode($return_url))),
					"imgover" => "blaa_over.gif",
					"img" => "blaa.gif",
				));
			}

		}
		return $toolbar->get_toolbar();
	}


	////
	// !this gets called when the user clicks on ob_conf 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function ob_conf($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda ob_gen");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda ob_gen");
		}
		$this->read_template("ob_conf.tpl");

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

		foreach($fnames as $field)
		{
			$this->vars(array(
				"field_name" => $field,
			));

			$whats=array("object","sisu","meta");

			foreach($whats as $what)
			{
				$dejoin_conf[$what]="";

				if ($ob['meta']['dejoin'][$what][$field])
				{
					if (!$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field]] && $ob['meta']['dejoin_table'][$what][$field])
					{
						$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field]]=$this->db_get_fieldnames($ob['meta']['dejoin_table'][$what][$field],1);
					}
					$this->vars(array(
						"what" => $what,
						"dejoin_tables" => $this->picker($ob['meta']['dejoin_table'][$what][$field],$list_tables),
						"dejoin_fields" => $this->picker($ob['meta']['dejoin_field'][$what][$field],$dejoin_fields[$ob['meta']['dejoin_table'][$what][$field]]),
						"remember" =>checked($ob['meta']['remember'][$what][$field]),
					));
					$dejoin_conf[$what]= $this->parse("dejoin");
				}

			}
			$this->vars(array(
				"dejoin_object" =>checked($ob['meta']['dejoin']['object'][$field]),
				"dejoin_sisu" =>checked($ob['meta']['dejoin']['sisu'][$field]),
				"dejoin_meta" =>checked($ob['meta']['dejoin']['meta'][$field]),

				"dejoini" => $dejoin,
				"unique" =>checked($ob['meta']['unique'][$field]),
				"sisu_table_data" => $this->picker($ob['meta']['sisu'][$field],$list_sisu_fields),
				"object_fields" => $this->picker($ob['meta']['object'][$field],$object_fields),
				"object_meta" => $ob['meta']['meta'][$field],
			));


			$data[]=array(
				"source" => $field,
				"object" => $this->parse("object").$dejoin_conf['object'],
				"meta" =>  $this->parse("meta").$dejoin_conf['meta'],
				"sisu" => $this->parse("sisu").$dejoin_conf['sisu'],
				"unique" => $this->parse("unique"),
//				"dejoin" => $this->parse("dejoin"),
			);		

		}		




		$this->vars(array(
			"ob_conf_table" => $this->ob_conf_table($data),
			"toolbar" => $this->my_toolbar(array("oid"=>$id, "return_url"=>$return_url,"got_source"=>$ob['meta']['source_table']?true:false)),
			"genereeri" => $this->mk_my_orb("generate_objects", array("id" => $id)),
			"genereeri5" => $this->mk_my_orb("generate_objects", array("id" => $id, "test_limit" => 10)),

			"normalizer" => $this->mk_my_orb("normalizer", array("id" => $id)),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "do" => "ob_conf","return_url" => urlencode($return_url)))
		));

		return $this->parse();
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
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda ob_gen");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda ob_gen");
		}
		$this->read_template("change.tpl");
	
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));

		$list_tables=$this->picker_list_db_tables();

		$chunks=array("1" => 1,"10" => 10,"100" => 100);
		

		if ($ob['meta']['use_object'])
		{

			$par = get_instance("objects");
			$parents = $par->get_list(false,true,50477);
	//		$parents = $par->get_list(false,true);

			$list_classes[]=" - ";
			foreach ($this->cfg["classes"] as $key => $val)
			{
				$list_classes[$key]=$val["def"];
			}
			asort($list_classes);

			$this->vars(array(
				"list_classes" => $this->picker($ob['meta']['class_id'],$list_classes),		
				"status" => $this->picker($ob['meta']['status'],array(0=> 'mitteaktiivsed',2=> 'aktiivsed')),		
				"parents" => $this->picker($ob['meta']['save_to_parent'], $parents),
			));
	
			$object=$this->parse("object");

		}

		if ($ob['meta']['use_sisu'])
		{
			$this->vars(array(
				"sisu_table" => $this->picker($ob['meta']['sisu_table'],$list_tables),		
			));
	
			$sisu=$this->parse("sisu");

		}

		$this->vars(array(

			"name" => $ob["name"],
			"comment" => $ob['comment'],
			"chunks" => $this->picker($ob["meta"]["limit"],$chunks),
			"source_tables" => $this->picker($ob['meta']['source_table'],$list_tables),
			"use_object" =>checked($ob['meta']['use_object']),
			"use_sisu" =>checked($ob['meta']['use_sisu']),
			"sisu" => $sisu,
			"object" => $object,
			"toolbar" => $this->my_toolbar(array("oid"=>$id, "return_url"=>$return_url,"got_source"=>$ob['meta']['source_table']?true:false)),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "parent" => $parent, "do" => "change","return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

	////
	// !setup logging
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function log_setup($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda ob_gen");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda ob_gen");
		}
		$this->read_template("log_setup.tpl");
	
		$this->vars(array(
			"display" => checked($ob['meta']['log']['display']),
			"db_table" => checked($ob['meta']['log']['db_table']),
			"made_objects" => checked($ob['meta']['log']['made_objects']),
			"a_source_field" => checked($ob['meta']['log']['a_source_field']),
 			"log_warnings" => checked($ob['meta']['log']['log_warnings']),
//			"" => $ob['meta'][],
			"toolbar" => $this->my_toolbar(array("oid"=>$id, "return_url"=>$return_url,"got_source"=>$ob['meta']['source_table']?true:false)),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "parent" => $parent, "do" => "log_setup","return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}


	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

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

		$t->parse_xml_def($this->cfg["basedir"]."/xml/ob_gen/conf_table.xml");

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