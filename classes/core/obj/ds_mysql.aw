<?php

class _int_obj_ds_mysql extends _int_obj_ds_base
{
	function _int_obj_ds_mysql()
	{
		$this->init();
	}

	// returns oid for alias
	// parameters:
	//	alias - required
	//	site_id - optional
	//	parent - optional
	function get_oid_by_alias($arr)
	{
		extract($arr);
		if (isset($arr["parent"]))
		{
			$parent = " AND parent = '".$parent."'";
		}
		if (isset($arr["site_id"]))
		{
			$site_id = " AND site_id = '".$site_id."'";
		}

		$q = sprintf("
			SELECT 
				%s 
			FROM 
				objects 
			WHERE 
				alias = '%s' AND 
				status != 0 %s %s
		", OID, $alias, $site_id,$parent);

		return $this->db_fetch_row($q);
	}

	function get_objdata($oid)
	{
		$ret = $this->db_fetch_row("SELECT * FROM objects WHERE oid = $oid AND status != 0");
		if ($ret === false)
		{
			error::throw(array(
				"id" => ERR_NO_OBJ,
				"msg" => "object::load($oid): no such object!"
			));
		}

		$ret["meta"] = aw_unserialize($ret["metadata"]);
		unset($ret["metadata"]);

		if ($ret["brother_of"] == 0)
		{
			$ret["brother_of"] = $ret["oid"];
		}
		return $ret;
	}


	// parameters:
	//	properties - property array
	//	tableinfo - tableinfo from propreader
	//	objdata - result of this::get_objdata
	function read_properties($arr)
	{
		extract($arr);

		$ret = array();

		// then read the properties from the db
		// find all the tables that the properties are in
		$tables = array();
		$tbl2prop = array();
		$objtblprops = array();
		foreach($properties as $prop => $data)
		{
			if ($data["store"] == "no")
			{
				continue;
			}

			if ($data["table"] == "")
			{
				$data["table"] = "objects";
			}

			if ($data["table"] != "objects")
			{
				$tables[$data["table"]] = $data["table"];
				if ($data["store"] != "no")
				{
					$tbl2prop[$data["table"]][] = $data;
				}
			}
			else
			{
				$objtblprops[] = $data;
			}
		}
		// import object table properties in the props array
		foreach($objtblprops as $prop)
		{
			if ($prop["method"] == "serialize")
			{
				// metadata is unserialized in read_objprops
				$ret[$prop["name"]] = $objdata[$prop["field"]][$prop["name"]];
			}
			else
			if ($prop["method"] == "bitmask")
			{
				$ret[$prop["name"]] = ((int)$objdata[$prop["field"]]) & ((int)$prop["ch_value"]);
			}
			else
			{
				$ret[$prop["name"]] = $objdata[$prop["field"]];
			}
		}

		// fix old broken databases where brother_of may be 0 for non-brother objects
		$object_id = ($objdata["brother_of"] ? $objdata["brother_of"] : $objdata["oid"]);

		$conn_prop_vals = array();

		// do a query for each table
		foreach($tables as $table)
		{
			$fields = array();
			$_got_fields = array();
			foreach($tbl2prop[$table] as $prop)
			{
				if ($prop['field'] == "meta" && $prop["table"] == "objects")
				{
					$prop['field'] = "metadata";
				}

				if ($prop["method"] == "serialize")
				{
					if (!$_got_fields[$prop["field"]])
					{
						$fields[] = $table.".".$prop["field"]." AS ".$prop["field"];
						$_got_fields[$prop["field"]] = true;
					}
				}
				else
				if ($prop["store"] == "connect")
				{
					// resolve reltype and do find_connections
					$values = array();
					$_co_reltype = $prop["reltype"];
					$_co_reltype = $GLOBALS["relinfo"][$objdata["class_id"]][$_co_reltype]["value"];
					if ($_co_reltype)
					{
						$this->db_query("
							SELECT 
								target 
							FROM 
								aliases 
								LEFT JOIN objects ON objects.oid = aliases.target
							WHERE 
								source = '".$object_id."' AND 
								reltype = '$_co_reltype' AND 
								objects.status != 0
						");
						while ($row = $this->db_next())
						{
							if ($prop["multiple"] == 1)
							{
								$values[$row["target"]] = $row["target"];
							}
							else
							{
								$values = $row["target"];
								break;
							}
						}
					}
					$conn_prop_vals[$prop["name"]] = $values;
					//echo "resolved reltype to ".dbg::dump($_co_reltype)." <br>";
				}
				else
				{
					$fields[] = $table.".".$prop["field"]." AS ".$prop["name"];
				}
			}

			if (count($fields) > 0)
			{
				$q = "SELECT ".join(",", $fields)." FROM $table WHERE ".$tableinfo[$table]["index"]." = '".$object_id."'";
				if (aw_global_get("uid") == "kix")
				{
					//echo "q = $q <br />";
				}
				$data = $this->db_fetch_row($q);
				if (is_array($data))
				{
					$ret += $data;
				}

				foreach($tbl2prop[$table] as $prop)
				{
					if ($prop["method"] == "serialize")
					{
						if ($prop['field'] == "meta" && $prop["table"] == "objects")
						{
							$prop['field'] = "metadata";
						}

						//echo "unser for prop ".dbg::dump($prop)." <br>";
						$unser = aw_unserialize($ret[$prop["field"]]);
						//echo "unser = ".dbg::dump($unser)." <br>";
						$ret[$prop["name"]] = $unser[$prop["name"]];
					}

				}
			}
		}

		foreach($conn_prop_vals as $prop => $val)
		{
			$ret[$prop] = $val;
			//echo "set $prop => ".dbg::dump($val)." <br>";
		}
		return $ret;
	}

	// creates new, empty object 
	// params:
	//	properties - prop array from propreader
	//	objdata - object data from objtable
	//	tableinfo - tableinfo from prop reader
	// returns:
	//	new oid

	function create_new_object($arr)
	{
		// add default values to metadata as well
		foreach($arr["properties"] as $prop => $data)
		{
			if (empty($data["table"]))
			{
				continue;
			}

			
			if ($data["table"] == "objects" && $data["field"] == "meta" && !isset($arr["objdata"]["meta"][$data["name"]]) && !empty($data["default"]))
			{
				$arr["objdata"]["meta"][$data["name"]] = $data["default"];
			}
		}

		extract($arr);

		$metadata = aw_serialize($objdata["meta"]);
		$this->quote($metadata);
		$this->quote(&$objdata);

		// create oid
		$q = "
			INSERT INTO objects (
				parent,						class_id,						name,						createdby,
				created,					modified,						status,						site_id,
				hits,						lang_id,						comment,					modifiedby,
				jrk,						period,							alias,						periodic,
				cachedirty,					metadata,						subclass,					flags
		) VALUES (
				'".$objdata["parent"]."',	'".$objdata["class_id"]."',		'".$objdata["name"]."',		'".$objdata["createdby"]."',
				'".$objdata["created"]."',	'".$objdata["modified"]."',		'".$objdata["status"]."',	'".$objdata["site_id"]."',
				'".$objdata["hits"]."',		'".$objdata["lang_id"]."',		'".$objdata["comment"]."',	'".$objdata["modifiedby"]."',
				'".$objdata["jrk"]."',		'".$objdata["period"]."',		'".$objdata["alias"]."',	'".$objdata["periodic"]."',
				'1',						'".$metadata."',				'".$objdata["subclass"]."',	'".$objdata["flags"]."'
		)";
		//echo "q = <pre>". htmlentities($q)."</pre> <br />";

		$this->db_query($q);
		$oid = $this->db_last_insert_id();


		// create all access for the creator
		$this->create_obj_access($oid);
		

		// set brother to self if not specified.
		if (!$objdata["brother_of"])
		{
			$this->db_query("UPDATE objects SET brother_of = oid WHERE oid = $oid");
		}

		// hits
		$this->db_query("INSERT INTO hits(oid,hits,cachehits) VALUES($oid, 0, 0 )");

		// now we need to create entries in all tables that are in properties as well.
		$tbls = array();
		foreach($properties as $prop => $data)
		{
			if (empty($data["table"]))
			{
				continue;
			}
			
			if ($data["table"] == "objects")
			{
				continue;
			}

			if ($data["store"] != "no" && $data["store"] != "connect")
			{
				$tbls[$data["table"]]["index"] = $tableinfo[$data["table"]]["index"];
				// check if the property has a value
				if (isset($objdata["properties"][$prop]))
				{
					$tbls[$data["table"]]["defaults"][$data["field"]] = $objdata["properties"][$prop];
				}
				else
				{
					$tbls[$data["table"]]["defaults"][$data["field"]] = $data["default"];
				}
			}
		}


		foreach($tbls as $tbl => $dat)
		{
			$idx = $dat["index"];
			$fds = "";
			$vls = "";
			if (is_array($dat["defaults"]))
			{
				foreach($dat["defaults"] as $fd => $vl)
				{
					$this->quote($vl);
					$fds .=",".$fd;
					$vls .=",'".$vl."'";
				}
			}

			$q = "INSERT INTO $tbl (".$idx.$fds.") VALUES('".$oid."'".$vls.")";
			$this->db_query($q);
		}
		
		return $oid;
	}

	// saves object prtoperties, including all object table fields, 
	// just stores the data, does not update or check it in any way, 
	// except for db quoting of course
	// params:
	//	properties - prop array from propreader
	//	objdata - object data from objtable
	//	tableinfo - tableinfo from prop reader
	//	propvalues - property values
	function save_properties($arr)
	{
		extract($arr);

		$metadata = aw_serialize($objdata["meta"]);
		$this->quote(&$metadata);
		$this->quote(&$objdata);

		if ($objdata["brother_of"] == 0)
		{
			$objdata["brother_of"] = $objdata["oid"];
		}

		// first, save all object table fields.
		$q = "UPDATE objects SET
			parent = '".$objdata["parent"]."',
			name = '".$objdata["name"]."',
			class_id = '".$objdata["class_id"]."',
			modified = '".$objdata["modified"]."',
			status = '".$objdata["status"]."',
			lang_id = '".$objdata["lang_id"]."',
			comment = '".$objdata["comment"]."',
			modifiedby = '".$objdata["modifiedby"]."',
			jrk = '".$objdata["jrk"]."',
			period = '".$objdata["period"]."',
			alias = '".$objdata["alias"]."',
			periodic = '".$objdata["periodic"]."',
			site_id = '".$objdata["site_id"]."',
			cachedirty = '1',
			metadata = '".$metadata."',
			subclass = '".$objdata["subclass"]."',
			flags = '".$objdata["flags"]."',
			brother_of = '".$objdata["brother_of"]."'
			
			WHERE oid = '".$objdata["oid"]."'
		";

//		echo "q = <pre>". htmlentities($q)."</pre> <br />";
		$this->db_query($q);

		// now save all properties


		// divide all properties into tables
		$tbls = array();
		foreach($properties as $prop => $data)
		{
			if ($data["store"] != "no" && $data["store"] != "connect")
			{
				$tbls[$data["table"]][] = $data;
			}
		}

		// now save all props to tables.
		foreach($tbls as $tbl => $tbld)
		{
			if ($tbl == "objects")
			{
				$tableinfo[$tbl]["index"] = "oid";
				$serfs["metadata"] = $objdata["meta"];
			}
			else
			{
				$serfs = array();
			};
			$seta = array();
			foreach($tbld as $prop)
			{
				// this check is here, so that we won't overwrite default values, that are saved in create_new_object
				if (isset($propvalues[$prop['name']]))
				{
					if ($prop['method'] == "serialize")
					{
						if ($prop['field'] == "meta" && $prop["table"] == "objects")
						{
							$prop['field'] = "metadata";
						}
						// since serialized properites can be several for each field, gather them together first
						$serfs[$prop['field']][$prop['name']] = $propvalues[$prop['name']];
					}
					else
					if ($prop['method'] == "bitmask")
					{
						$val = $propvalues[$prop["name"]];
	
						if (!isset($seta[$prop["field"]]))
						{	
							// jost objects.flags support for now
							$seta[$prop["field"]] = $objdata["flags"];
						}

						// make mask for the flag - mask value is the previous field value with the
						// current flag bit(s) set to zero. flag bit(s) come from prop[ch_value]	
						$mask = $seta[$prop["field"]] & (~((int)$prop["ch_value"]));
						// add the value
						$mask |= $val;
						
						$seta[$prop["field"]] = $mask;;
					}
					else
					{
						$str = $propvalues[$prop["name"]];
						$this->quote(&$str);
	
						$seta[$prop["field"]] = $str;
					}
				}
			}

			foreach($serfs as $field => $dat)
			{
				$str = aw_serialize($dat);
				$this->quote($str);
				$seta[$field] = $str;
			}
			$sets = join(",",map2("%s = '%s'",$seta,0,true));
			if ($sets != "")
			{
				$q = "UPDATE $tbl SET $sets WHERE ".$tableinfo[$tbl]["index"]." = '".$objdata["brother_of"]."'";
//				echo "q = <pre>". htmlentities($q)."</pre> <br />";
				$this->db_query($q);
			}
		}
	}

	function read_connection($id)
	{
		return $this->db_fetch_row("
			SELECT 
				".$this->connection_query_fetch()."
			FROM 
				aliases a
				LEFT JOIN objects o_s ON o_s.oid = a.source
				LEFT JOIN objects o_t ON o_t.oid = a.target
			WHERE 
				id = $id
		");
	}

	function save_connection($data)
	{
		if (!$data["type"])
		{
			$data["type"] = $this->db_fetch_field("SELECT class_id FROM objects WHERE oid = '".$data["to"]."'", "class_id");
		}

		if ($data["id"])
		{
			$q = "UPDATE aliases SET 
				source = '$data[from]',
				target = '$data[to]',
				type = '$data[type]',
				data = '$data[data]',
				idx = '$data[idx]',
				cached = '$data[cached]',
				relobj_id = '$data[relobj_id]',
				reltype = '$data[reltype]',
				pri = '$data[pri]'
			WHERE id = '$data[id]'";
			$this->db_query($q);
		}
		else
		{
			if (!$data["idx"])
			{
				$q = "SELECT MAX(idx) as idx FROM aliases where source = '$data[from]' and type = '$data[type]'";
				$data["idx"] = $this->db_fetch_field($q, "idx")+1;
			}
			$q = "INSERT INTO aliases (
				source,						target,					type,					data,
				idx,						cached,					relobj_id,				reltype,
				pri
			) VALUES(
				'$data[from]',				'$data[to]',			'$data[type]',			'$data[data]',
				'$data[idx]',				'$data[cached]',		'$data[relobj_id]',		'$data[reltype]',
				'$data[pri]'
			)";
			$this->db_query($q);
			$data['id'] = $this->db_last_insert_id();
		}

		return $data['id'];
	}

	function delete_connection($id)
	{
		$this->db_query("DELETE FROM aliases WHERE id = '$id'");
	}

	function connection_query_fetch()
	{
		return "a.id as `id`,
				a.source as `from`,
				a.target as `to`,
				a.type as `type`,
				a.data as `data`,
				a.idx as `idx`,
				a.cached as `cached`,
				a.relobj_id as `relobj_id`,
				a.reltype as `reltype`,
				a.pri as pri,
				o_t.lang_id as `to.lang_id`,
				o_s.lang_id as `from.lang_id`,
				o_t.flags as `to.flags`,
				o_s.flags as `from.flags`,
				o_t.modified as `to.modified`,
				o_s.modified as `from.modified`,
				o_t.modifiedby as `to.modifiedby`,
				o_s.modifiedby as `from.modifiedby`,
				o_t.name as `to.name`,
				o_s.name as `from.name`,
				o_t.class_id as `to.class_id`,
				o_s.class_id as `from.class_id`,
				o_t.jrk as `to.jrk`,
				o_s.jrk as `from.jrk`,
				o_t.status as `to.status`,
				o_s.status as `from.status`
		";
	}

	// arr - { [from], [to], [type], [class], [to.obj_table_field], [from.obj_table_field] }
	function find_connections($arr)
	{
		$sql = "
			SELECT 
				".$this->connection_query_fetch()."
			FROM 
				aliases a
				LEFT JOIN objects o_s ON o_s.oid = a.source
				LEFT JOIN objects o_t ON o_t.oid = a.target
			WHERE 
				o_s.status != 0 AND
				o_t.status != 0
		";

		if ($arr["from"])
		{
			$awa = new aw_array($arr["from"]);
			$sql .= " AND source IN (".$awa->to_sql().") ";
		}

		if ($arr["to"])
		{
			$awa = new aw_array($arr["to"]);
			$sql .= " AND target IN (".$awa->to_sql().") ";
		}

		if ($arr["type"])
		{
			$awa = new aw_array($arr["type"]);
			$sql .= " AND reltype IN (".$awa->to_sql().") ";
		}

		if ($arr["class"])
		{
			$awa = new aw_array($arr["class"]);
			$sql .= " AND type IN (".$awa->to_sql().") ";
		}

		if ($arr["relobj_id"])
		{
			$awa = new aw_array($arr["relobj_id"]);
			$sql .= " AND relobj_id IN (".$awa->to_sql().") ";
		}

		if ($arr["idx"])
		{
			$awa = new aw_array($arr["idx"]);
			$sql .= " AND idx IN (".$awa->to_sql().") ";
		}

		foreach($arr as $k => $v)
		{
			if (substr($k, 0, 3) == "to.")
			{
				if (is_array($v))
				{
					$sql .= " AND o_t.".substr($k, 3)." IN (" . join(",",$v) . ") ";
				}
				else
				{
					$sql .= " AND o_t.".substr($k, 3)." = '$v' ";
				};
			}
			if (substr($k, 0, 5) == "from.")
			{
				$sql .= " AND o_s.".substr($k, 5)." = '$v' ";
			}
		}

		$sql .= " ORDER BY a.id ";

		$this->db_query($sql);
		$ret = array();
		while ($row = $this->db_next())
		{
			$ret[$row["id"]] = $row;
		}

		return $ret;
	}

	// params:
	//	array of filter parameters 
	// if class id is present, properties can also be filtered, otherwise only object table fields
	function search($params)
	{
		$this->used_tables = array();

		$this->properties = array();
		$this->tableinfo = array();

		// load property defs and table defs
		if (isset($params["class_id"]))
		{
			$this->_do_add_class_id($params["class_id"]);
		}

		$this->stat = false;
		$this->sby = "";
		$this->limit = "";
		$this->has_lang_id = false;

		$this->meta_filter = array();
		$this->alias_joins = array();

		$where = $this->req_make_sql($params);

		if (!$this->stat)
		{
			$where .= ($where != "" ? " AND " : "")." objects.status > 0 ";
		}

		if (!isset($params["site_id"]))
		{
			$where .= ($where != "" ? " AND " : "")." objects.site_id = '".aw_ini_get("site_id")."' ";
		}

		if (!$this->has_lang_id)
		{
			$where .= ($where != "" ? " AND " : "")." objects.lang_id = '".aw_global_get("lang_id")."' ";
		}

		// make joins
		$js = array();
		foreach($this->used_tables as $tbl)
		{
			if ($tbl != "objects")
			{
				$js[] = " LEFT JOIN $tbl ON $tbl.".$this->tableinfo[$tbl]["index"]." = objects.brother_of ";
			}
		}
		foreach($this->alias_joins as $aj)
		{
			$js[] = " LEFT JOIN aliases $aj[name] ON $aj[on] ";
		}
		$joins = join("", $js);

		$ret = array();
		if ($where != "")
		{
			$q = "SELECT objects.oid as oid,objects.name as name FROM objects $joins WHERE $where ".$this->sby." ".$this->limit;

			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$ret[$row["oid"]] = $row["name"];
			}
		}

		// try this - get list of special objects fiest time around
		// then if we later do a search-by-parent, let the class handle it
		/*if (!is_array($GLOBALS["obj_fs_globals"]["container_objs"]))
		{
			$GLOBALS["obj_fs_globals"]["container_objs"] = array();
			list($data) = $this->search(array("class_id" => CL_OTV_DS_POSTIPOISS));
			foreach($data as $d_oid)
			{
				$GLOBALS["obj_fs_globals"]["container_objs"][$d_oid] = obj($d_oid);
			}
		}

		if (!empty($params["parent"]) && !is_array($params["parent"]))
		{
		echo dbg::dump($GLOBALS["obj_fs_globals"]);
			foreach($GLOBALS["obj_fs_globals"]["container_objs"] as $c_oid => $c_obj)
			{
				if ($c_obj->id() == $params["parent"])
				{
					$inst = $c_obj->instance();
					$tmp = $inst->get_objects($c_obj);
					foreach($tmp as $t_id => $t_dat)
					{
						$full_oid = $c_oid.":".$t_id;
						$ret[$full_oid] = $full_oid;
					}
				}
			}
		}*/
		return array($ret, $this->meta_filter);
	}

	function delete_object($oid)
	{
		$this->db_query("UPDATE objects SET status = '".STAT_DELETED."', modified = ".time().",modifiedby = '".aw_global_get("uid")."' WHERE oid = '$oid'");
		$this->db_query("DELETE FROM aliases WHERE target = '$oid'");
		$this->db_query("DELETE FROM aliases WHERE source = '$oid'");
	}

	function req_make_sql($params, $logic = "AND")
	{
		$sql = array();
		foreach($params as $key => $val)
		{
			if ($val === NULL)
			{
				continue;
			}

			if ("sort_by" == (string)($key))
			{
				$this->sby = " ORDER BY $val ";
				continue;
			}

			if ("limit" == (string)($key))
			{
				$this->limit = " LIMIT $val ";
				continue;
			}

			if ("status" == (string)($key))
			{
				$this->stat = true;
			}

			if ("lang_id" == (string)($key))
			{
				$this->has_lang_id = true;
			}

			$tbl = "objects";
			$fld = $key;
			if (isset($this->properties[$key]) && $this->properties[$key]["store"] != "no")
			{
				$tbl = $this->properties[$key]["table"];
				$fld = $this->properties[$key]["field"];
				if ($fld == "meta")
				{
					$this->meta_filter[$key] = $val;
					continue;
				}
				else
				if ($this->properties[$key]["method"] == "serialize")
				{
					error::throw(array(
						"id" => ERR_FIELD,
						"msg" => "filter cannot contain properties ($key) that are in serialized fields other than metadata!"
					));
				}
			}

			$tf = $tbl.".".$fld;
			$this->used_tables[$tbl] = $tbl;

			if ($this->properties[$key]["store"] == "connect")
			{
				// join aliases as many-many relation and filter by that
				$this->alias_joins[$key] = array(
					"name" => "aliases_".$key,
					"on" => $tbl.".".$this->tableinfo[$this->properties[$key]["table"]]["index"]." = "."aliases_".$key.".source"
				);
			}

			if (($this->properties[$key]["method"] == "bitmask" || $key == "flags") && is_array($val))
			{
				$sql[] = $tf." & ".$val["mask"]." = ".$val["flags"];
			}
			else
			if (is_object($val))
			{
				$class_name = get_class($val);
				if ($class_name == "object_list_filter")
				{
					if (!empty($val->filter["non_filter_classes"]))
					{
						$this->_do_add_class_id($val->filter["non_filter_classes"], true);
					}
					if (isset($val->filter["logic"]))
					{
						$sql[] = "(".$this->req_make_sql($val->filter["conditions"], $val->filter["logic"]).")";
					}
				}
				else
				if ($class_name == "obj_predicate_not")
				{
					$v_data = $val->data;
					if (is_object($val->data) && get_class($val->data) == "aw_array")
					{
						$v_data = $v_data->get();
					}

					if (is_array($val->data))
					{
						$sql[] = $tf." NOT IN (".join(",", $v_data).") ";
					}
					else
					{
						$sql[] = $tf." != ".$v_data." ";
					}
				}
				else
				if ($class_name == "obj_predicate_compare")
				{
					$v_data = $val->data;
					if (is_object($val->data) && get_class($val->data) == "aw_array")
					{
						$v_data = $v_data->get();
					}

					$comparator = "";
					switch($val->comparator)
					{
						case OBJ_COMP_LESS:
							$comparator = " < ";
							break;

						case OBJ_COMP_GREATER:
							$comparator = " > ";
							break;

						case OBJ_COMP_LESS_OR_EQ:
							$comparator = " <= ";
							break;

						case OBJ_COMP_GREATER_OR_EQ:
							$comparator = " >= ";
							break;

						default:
							error::throw(array(
								"id" => ERR_OBJ_COMPARATOR,
								"msg" => "obj_predicate_compare's comparator operand must be either OBJ_COMP_LESS,OBJ_COMP_GREATER,OBJ_COMP_LESS_OR_EQ,OBJ_COMP_GREATER_OR_EQ. the value supplied, was: ".$val->comparator."!"
							));
					}

					if (is_array($v_data))
					{
						$tmp = array();
						foreach($v_data as $d_k)
						{
							$tmp[] = $tf." $comparator $d_k ";
						}
						$sql[] = "(".join(" OR ", $tmp).")";
					}
					else
					{
						$sql[] = $tf." $comparator ".$v_data." ";
					}
				}
			}
			else
			if (is_array($val) || (is_object($val) && get_class($val) == "aw_array"))
			{
				if (is_object($val))
				{
					$val = $val->get();
				}
				$str = array();
				foreach($val as $v)
				{
					if ($v === "")
					{
						continue;
					}
					if ($this->properties[$key]["store"] == "connect")
					{
						$str[] = " aliases_".$key.".target = '$v' ";
					}
					else
					if (strpos("%", $v) !== false)
					{
						$str[] = $tf." LIKE '".$v."'";
					}
					else
					{
						$str[] = $tf." = '".$v."'";
					}
				}
				$str = join(" OR ", $str);
				if ($str != "")
				{
					$sql[] = " ( $str ) ";
				}
			}
			else
			{
				if ($this->properties[$key]["store"] == "connect")
				{
					$sql[] = " aliases_".$key.".target = '$val' ";
				}
				else
				if ($key == "modified" || $key == "flags")
				{
					// pass all arguments .. &, >, < or whatever the user wants to
					$sql[] = $tf." ".$val;
				}
				else
				if (strpos($val,"%") !== false)
				{
					$sql[] = $tf." LIKE '".$val."'";
				}
				else
				{
					$sql[] = $tf." = '".$val."'";
				}
			}
		}
		return join(" ".$logic." ", $sql);
	}

	function _do_add_class_id($clids, $add_table = false)
	{
		if (!is_array($clids))
		{
			$clids = array($clids);
		}

		foreach($clids as $clid)
		{
			if (!isset($GLOBALS["properties"][$clid]) || !isset($GLOBALS["tableinfo"][$clid]))
			{
				list($GLOBALS["properties"][$clid], $GLOBALS["tableinfo"][$clid]) = $GLOBALS["object_loader"]->load_properties(array(
					"file" => ($clid == CL_DOCUMENT ? "doc" : $classes[$clid]["file"]),
					"clid" => $clid
				));
			}

			$this->properties += $GLOBALS["properties"][$clid];
			if (is_array($GLOBALS["tableinfo"][$clid]))
			{
				if ($add_table)
				{
					foreach($GLOBALS["tableinfo"][$clid] as $_tbl => $td)
					{
						$this->used_tables[$_tbl] = $_tbl;
					}
				}
				$this->tableinfo += $GLOBALS["tableinfo"][$clid];
			}
			if (isset($this->tableinfo["documents"]))
			{
				$this->used_tables["documents"] = "documents";
			}
		}
	}

	function create_brother($arr)
	{
		extract($arr);

		$metadata = aw_serialize($objdata["meta"]);
		$this->quote($metadata);
		$this->quote(&$objdata);

		// create oid
		$q = "
			INSERT INTO objects (
				parent,						class_id,						name,						createdby,
				created,					modified,						status,						site_id,
				hits,						lang_id,						comment,					modifiedby,
				jrk,						period,							alias,						periodic,
				cachedirty,					metadata,						subclass,					flags,
				brother_of
		) VALUES (
				'".$parent."',				'".$objdata["class_id"]."',		'".$objdata["name"]."',		'".$objdata["createdby"]."',
				'".$objdata["created"]."',	'".$objdata["modified"]."',		'".$objdata["status"]."',	'".$objdata["site_id"]."',
				'".$objdata["hits"]."',		'".$objdata["lang_id"]."',		'".$objdata["comment"]."',	'".$objdata["modifiedby"]."',
				'".$objdata["jrk"]."',		'".aw_global_get("act_per_id")."',		'".$objdata["alias"]."',	'".$objdata["periodic"]."',
				'1',						'".$metadata."',				'".$objdata["subclass"]."',	'".$objdata["flags"]."',
				'".$objdata["oid"]."'
		)";
		//echo "q = <pre>". htmlentities($q)."</pre> <br />";
		$this->db_query($q);
		$oid = $this->db_last_insert_id();

		// create all access for the creator
		$this->create_obj_access($oid);

		// hits
		$this->db_query("INSERT INTO hits(oid,hits,cachehits) VALUES($oid, 0, 0 )");

		return $oid;
	}
}

?>
