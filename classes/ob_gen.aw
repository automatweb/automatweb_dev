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
				"comment"=>$comment,
				"metadata"=>array(
					"status"=>$status,
					"limit"=>$limit,
					"extra_table"=>$extra_table,
					"save_to_parent"=>$save_to_parent,
					"pick_table"=>$pick_table,
					"create_object"=>$create_object,

				),
			));
			}
			else
			{
			$this->upd_object(array(
				"oid" => $id,
				"metadata"=>array(
					"object"=>$object,
					"object_meta"=>$object_meta,
					"extra_table_data"=>$extra_table_data,
					"unique"=>$unique,
					"dejoin"=>$dejoin,
					"dejoin_table"=>$dejoin_table,
					"dejoin_field"=>$dejoin_field,
				),
			));

			}
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment"=>$comment,
				"class_id" => CL_OB_GEN,
				"metadata"=>array(
					"status"=>$status,
					"limit"=>$limit,
					"save_to_parent"=>$save_to_parent,
					"pick_table"=>$pick_table,
					"create_object"=>$create_object,
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
	// object  required
	//	class - CLASS  eg CL_LINK required
	//	parent - required
	//	status
	//	name	
	// meta - array of keys and values / optional
	// sql - optional
	//	table_name - name of sql table -  required
	//	data - array of fieldnames as keys and their values
	//
	// returns oid
	function create_my_object($arr)
	{
		extract($arr);
		if (is_array($object))
		foreach ($object as $val)
		{
			if ($val) $ok=1;
		}
		if (is_array($meta))
		foreach ($meta as $val)
		{
			if ($val) $ok=1;
		}

		if (!$ok) 
		{
			return "object contains no data!";
		}

		if (is_array($meta))
		{
			$object=(array)$object+array("metadata"=>$meta);
		}
		//		teeme objekti objektitabelisse
//				$id = $this->new_object($object);

		// ja kui on andmeid sisu tablei kohta siis ka sisutabelisse kirje
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
					$this->quote($val);
					$values[]="'".$val."'";
				}
			}

			$fields[]="oid";
			$values[]="'".$id."'";
echo			$q='insert into '.$table_name.' ('.implode(',',$fields).')values('.implode(',',$values).')';
	
//			$this->db_query($q);
		}
		return $id;
	}

	
	
	function generate_objects($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		echo "<pre>";
		$alg=0;
		$lopp=$ob['meta']['limit'];
		$total=0;
		do
		{
		// if ($alg>10) break;
//			$q="select * from ".$ob['meta']['pick_table']." limit $alg,1 \n";
echo 			$q="select * from ".$ob['meta']['pick_table']." limit $alg,$lopp \n";

			$data=$this->db_fetch_array($q);
			$count=0;
			$count2=0;
			if (is_array($data))
			foreach ($data as $row)
			{ 
//				print_r($row);
//unset($object);unset($extra_table);unset($meta);unset($extra_table_data);
				$skipit=0;
				if (!is_array($row)) break;
				foreach($row as $key=>$val)
				{
					if ($ob['meta']['dejoin'][$key] && $ob['meta']['dejoin_table'][$key] && $ob['meta']['dejoin_field'][$key])
					{
echo						$q='select oid from '.$ob['meta']['dejoin_table'][$key].' where '.$ob['meta']['dejoin_field'][$key].'="'.$val.'" limit 1';
 						$val=$this->db_fetch_field($q, 'oid');

if (!$val) {}//suur jama, äkki peaks siinjuures looma siis objekti
					}

					if ($ob['meta']['unique'][$key])
					{
						if ($unique[$val]==$val) //already made that object
						{
							//echo "$val\n";
							$skipit=1;
							break;
						}
						else
						{
							$unique[$val]=$val;
						}
					}
					if ($ob['meta']['object_meta'][$key])
					{
						$meta[$ob['meta']['object_meta'][$key]]=$val;
					}
					if ($ob['meta']['object'][$key])
					{
						$object[$ob['meta']['object'][$key]]=$val;
					}
					if ($ob['meta']['extra_table_data'][$key])
					{
						$extra_table[$ob['meta']['extra_table_data'][$key]]=$val;
					}


				}

		
				if (!$skipit) 
				{
					$ok=$this->create_my_object(array(
						"object"=>(array)$object + array(
							"class_id"=>$ob['meta']['create_object'],
							"parent"=>$ob['meta']['save_to_parent'],
							"status"=>$ob['meta']['status'],
						),
						"meta"=>$meta,
						"sql"=>array(
							"table_name"=>$ob['meta']['extra_table'],
							"data"=>$extra_table,
						),
					));
					
					echo "created: ".$ok."\n";
					$count++;
					flush();
					set_time_limit (30);
					die();
				}
				$count2++;
			}

//			echo $ob['meta']['create_object'];echo "\n";
//			echo $ob['meta']['save_to_parent'];echo "\n";
//			echo $ob['meta']['status'];echo "\n";
//			echo $ob['meta']['extra_table'];echo "\n";

			$total+=$count;
//			if ($total > 10)
//			break;
//			$alg++;
			$alg+=$lopp;
		} while ($count2);
		die("\n\n total objects generated: ".$total."</pre>");
	}




	function db_get_fieldnames($table,$addempty="",$get_all="")
	{
		$this->db_query('select * from '.$table.' limit 1');		
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
		if($addempty)
		{
			return array(0=>" - ") + $all;
		}
		else
		{
			return $all;
		}
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
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));

		$toolbar->add_button(array(
			"name" => "change",
			"tooltip" => "üld määrangud",
			"url" => $this->mk_my_orb("change",array("id"=>$id,"return_url" => urlencode($return_url))),
			"imgover" => "settings_over.gif",
			"img" => "settings.gif",
		));

		if ($ob['meta']['extra_table'])
		{
			$list_sisu_fields=$this->db_get_fieldnames($ob['meta']['extra_table'],1);
		}

		if ($ob['meta']['dejoin'])		
		{
			$this->db_list_tables();
			while ($tb = $this->db_next_table())
			{
				$list_tables[$tb]=$tb;
			}
			$list_tables=array(0=>" - ") + $list_tables;
		}

		$object_fields=$this->db_get_fieldnames('objects',1);
		$fnames=$this->db_get_fieldnames($ob['meta']['pick_table']);

				foreach($fnames as $field)
				{
					if ($ob['meta']['dejoin'][$field])
					{
						if (!$dejoin_fields[$ob['meta']['dejoin_table'][$field]] && $ob['meta']['dejoin_table'][$field])
						{
							$dejoin_fields[$ob['meta']['dejoin_table'][$field]]=$this->db_get_fieldnames($ob['meta']['dejoin_table'][$field]);
						}

						$this->vars(array(
							"field_name"=>$field,
							"dejoin_tables"=>$this->picker($ob['meta']['dejoin_table'][$field],$list_tables),
							"dejoin_fields"=>$this->picker($ob['meta']['dejoin_field'][$field],$dejoin_fields[$ob['meta']['dejoin_table'][$field]]),
						));
						$dejoin=$this->parse("dejoini");

					}
					$this->vars(array(
						"field_name"=>$field,
						"object_meta"=>$ob['meta']['object_meta'][$field],
						"extra_table_data"=>$this->picker($ob['meta']['extra_table_data'][$field],$list_sisu_fields),
						"object_fields"=>$this->picker($ob['meta']['object'][$field],$object_fields),
						"dejoin"=>checked($ob['meta']['dejoin'][$field]),
						"dejoini"=>$ob['meta']['dejoin'][$field]?$dejoin:"",
						"unique"=>checked($ob['meta']['unique'][$field]),
					));
		
					$list_fields.=$this->parse("fields");
				}		
		

		$this->vars(array(
			"toolbar"=>$toolbar->get_toolbar(),
			"genereeri"=>$this->mk_my_orb("generate_objects", array("id" => $id)),
			"list_fields"=>$list_fields,
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "do"=>"ob_conf","return_url" => urlencode($return_url)))
		));

		return $this->parse();
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

		$this->db_list_tables();
		while ($tb = $this->db_next_table())
		{
			$list_tables[$tb]=$tb;
		}

		$list_tables=array(0=>" - ") + $list_tables;

		$chunks=array("1"=>1,"10"=>10,"100"=>100);
		
		$par = get_instance("objects");
		$parents = $par->get_list(false,true,50477);

		if ($ob['meta']['pick_table'])
		{
			$toolbar->add_button(array(
				"name" => "ob_conf",
				"tooltip" => "objekti conf",
				"url" => $this->mk_my_orb("ob_conf",array("id"=>$id,"return_url" => urlencode($return_url))),
				"imgover" => "promo_over.gif",
				"img" => "promo.gif",
			));

		}

		$list_classes[]=" - ";
		foreach ($this->cfg["classes"] as $key => $val)
		{
			$list_classes[$key]=$val["def"];
		}
		asort($list_classes);

		$this->vars(array(
			"toolbar"=>$toolbar->get_toolbar(),
			"chunks"=>$this->picker($ob["meta"]["limit"],$chunks),
//			"genereeri"=>$this->mk_my_orb("generate_objects", array("id" => $id)),
			"status"=>$this->picker($ob['meta']['status'],array(0=>'mitteaktiivsed',2=>'aktiivsed')),
			"extra_table"=>$this->picker($ob['meta']['extra_table'],$list_tables),
			"comment" => $ob['comment'],
			"parents"=> $this->picker($ob['meta']['save_to_parent'], $parents),
			"list_classes"=>$this->picker($ob['meta']['create_object'],$list_classes),
			"list_tables"=>$this->picker($ob['meta']['pick_table'],$list_tables),
			"name" => $ob["name"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "parent" => $parent, "do"=>"change","return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob["name"]
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

	////
	// !this is not required 99% of the time, but you can override adding aliases to documents - when the user clicks
	// on "pick this" from the aliasmanager "add existing object" list and this function exists in the class, then it will be called
	// parameters
	//   id - the object to which the alias is added
	//   alias - id of the object to add as alias
/*	function addalias($arr)
	{
		extract($arr);
		// this is the default implementation, don't include this function if you're not gonna change it
		$this->add_alias($id,$alias);
		header("Location: ".$this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr"));
	}*/
}
?>