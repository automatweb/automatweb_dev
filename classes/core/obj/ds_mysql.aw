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
				/*
				if ($prop['field'] == "meta")
				{
					$prop['field'] = "metadata";
				}
				*/
				// metadata is unserialized in read_objprops
				$ret[$prop["name"]] = $objdata[$prop["field"]][$prop["name"]];
			}
			else
			{
				$ret[$prop["name"]] = $objdata[$prop["field"]];
			}
		}

		// do a query for each table
		foreach($tables as $table)
		{
			$fields = array();
			foreach($tbl2prop[$table] as $prop)
			{
				if ($prop['field'] == "meta" && $prop["table"] == "objects")
				{
					$prop['field'] = "metadata";
				}
				$fields[] = $table.".".$prop["field"]." AS ".$prop["name"];
			}

			if (count($fields) > 0)
			{
				$q = "SELECT ".join(",", $fields)." FROM $table WHERE ".$tableinfo[$table]["index"]." = '".$objdata["brother_of"]."'";
				//echo "q = $q <br />";
				$data = $this->db_fetch_row($q);
				if (is_array($data))
				{
					$ret += $data;
				}

				foreach($tbl2prop[$table] as $prop)
				{
					if ($prop["method"] == "serialize")
					{
						if ($prop['field'] == "meta")
						{
							$prop['field'] = "metadata";
						}

						$unser = aw_unserialize($ret[$prop["field"]]);
						$ret[$prop["name"]] = $unser[$prop["name"]];
					}
				}
			}
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

			if ($data["store"] != "no")
			{
				$tbls[$data["table"]]["index"] = $tableinfo[$data["table"]]["index"];
				if ($data["default"] != "")
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
			//echo "q = <pre>". htmlentities($q)."</pre> <br />";
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
			flags = '".$objdata["flags"]."'
			
			WHERE oid = '".$objdata["oid"]."'
		";
//		echo "q = <pre>". htmlentities($q)."</pre> <br />";
		$this->db_query($q);

		// now save all properties

		// divide all properties into tables
		$tbls = array();
		foreach($properties as $prop => $data)
		{
			if ($data["store"] != "no")
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
			}
			$seta = array();
			$serfs = array();
			foreach($tbld as $prop)
			{
				// this check is here, so that we won't overwrite default values, that are saved in create_new_object
				if (isset($propvalues[$prop['name']]))
				{
					if ($prop['method'] == "serialize")
					{
						if ($prop['field'] == "meta")
						{
							$prop['field'] = "metadata";
						}
						// since serialized properites can be several for each field, gather them together first
						$serfs[$prop['field']][$prop['name']] = $propvalues[$prop['name']];
					}
					else
					{
						$str = $propvalues[$prop['name']];
						$this->quote(&$str);
	
						$seta[] = $prop["field"]." = '$str'";
					}
				}
			}

			foreach($serfs as $field => $dat)
			{
				$str = aw_serialize($dat);
				$this->quote($str);
				$seta[] = $field." = '$str'";
			}
			$sets = join(",", $seta);
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
				o_s.lang_id as `from.lang_id` ";
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
			$sql .= " AND source = '".$arr["from"]."' ";
		}
		if ($arr["to"])
		{
			$sql .= " AND target = '".$arr["to"]."' ";
		}
		if ($arr["type"])
		{
			$sql .= " AND reltype = '".$arr["type"]."' ";
		}
		if ($arr["class"])
		{
			$sql .= " AND type = '".$arr["class"]."' ";
		}

		foreach($arr as $k => $v)
		{
			if (substr($k, 0, 3) == "to.")
			{
				$sql .= " AND o_t.".substr($k, 3)." = '$v' ";
			}
			if (substr($k, 0, 3) == "from.")
			{
				$sql .= " AND o_s.".substr($k, 3)." = '$v' ";
			}
		}

		$this->db_query($sql);
		$ret = array();
		while ($row = $this->db_next())
		{
			$ret[] = $row;
		}
		return $ret;
	}

	// params:
	//	array of filter parameters 
	// if class id is present, properties can also be filtered, otherwise only object table fields
	function search($params)
	{
		if ($params["class_id"] && !is_array($params["class_id"]))
		{
			$classes = aw_ini_get("classes");
			list($properties, $tableinfo) = $GLOBALS["object_loader"]->load_properties(array(
				"file" => $classes[$params["class_id"]]["file"],
				"clid" => $params["class_id"]
			));
		}
		else
		if (is_array($params["class_id"]))
		{
			$classes = aw_ini_get("classes");
			$properties = array();
			$tableinfo = array();
			foreach($params["class_id"] as $clid)
			{
				list($tmp, $tmp2) = $GLOBALS["object_loader"]->load_properties(array(
					"file" => ($clid == CL_DOCUMENT ? "doc" : $classes[$clid]["file"]),
					"clid" => $clid
				));
				$properties += $tmp;
				if (is_array($tmp2))
				{
					$tableinfo += $tmp2;
				}
			}
		}

		$stat = false;
		$where = array();
		$sby = "";

		foreach($params as $key => $val)
		{
			if ($key == "status")
			{
				$stat = true;
			}
			if ($key == "sort_by")
			{
				$sby = " ORDER BY $val ";
				continue;
			}
			if ($key == "limit")
			{
				$limit = " LIMIT $val ";
				continue;
			}

			$tbl = "objects";
			$fld = $key;
			if (isset($properties[$key]))
			{
				$tbl = $properties[$key]["table"];
				$fld = $properties[$key]["field"];
				if ($fld == "meta" || $properties[$key]["method"] == "serialize")
				{
					error::throw(array(
						"id" => ERR_FIELD,
						"msg" => "filter cannot contain properties ($key) that are in metadata or serialized fields!"
					));
				}
			}

			if (($properties[$key]["method"] == "bitmask" || $key == "flags") && is_array($val))
			{
				$val = "& ".$val["mask"]." = ".$val["flags"];
				$where[$tbl][] = $tbl.".".$fld." ".$val;
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
					if (strpos("%", $v) !== false)
					{
						$str[] = $tbl.".".$fld." LIKE '".$v."'";
					}
					else
					{
						$str[] = $tbl.".".$fld." = '".$v."'";
					}
				}
				$str = join(" OR ", $str);
				if ($str != "")
				{
					$where[$tbl][] = " ( $str ) ";
				}
			}
			else
			{
				if ($key == "modified" || $key == "flags")
				{
					// pass all arguments .. &, >, < or whatever the user wants to
					$where[$tbl][] = $tbl.".".$fld." ".$val;
				}
				else
				if (strpos($val,"%") !== false)
				{
					$where[$tbl][] = $tbl.".".$fld." LIKE '".$val."'";
				}
				else
				{
					$where[$tbl][] = $tbl.".".$fld." = '".$val."'";
				}
			}
		}

		if (!$stat)
		{
			$where["objects"][] = " objects.status != 0 ";
		}

		if (!isset($params["site_id"]))
		{
			$where["objects"][] = " objects.site_id = '".aw_ini_get("site_id")."' ";
		}

		if (!isset($params["lang_id"]))
		{
			$where["objects"][] = " objects.lang_id = '".aw_global_get("lang_id")."' ";
		}

		// make joins
		$js = array();
		$t_w = array();
		foreach($where as $tbl => $flds)
		{
			if ($tbl != "objects")
			{
				$js[] = " LEFT JOIN $tbl ON $tbl.".$tableinfo[$tbl]["index"]." = objects.brother_of ";
			}
			foreach($flds as $fld)
			{
				$t_w[] = $fld;
			}
		}
		$joins = join("", $js);
		$where = join(" AND ", $t_w);

		$ret = array();

		if ($where != "")
		{
			$q = "SELECT objects.oid FROM objects $joins WHERE $where $sby $limit";
			//echo "q = $q <br />";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$ret[$row["oid"]] = $row["oid"];
			}
		}
		return $ret;
	}

	function delete_object($oid)
	{
		$this->db_query("UPDATE objects SET status = '".STAT_DELETED."' WHERE oid = '$oid'");
	}
}

?>
