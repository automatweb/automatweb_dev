<?php

class _int_obj_ds_local_sql extends acl_base
{
	function _int_obj_ds_local_sql()
	{
		$this->init();
		// grmbl - acl_base class needs this. 
		// normally it gets inited in aw_template::init
		// but since this class hooks into the inheritance tree before that, 
		// we gots to init it here. and we don't want all the ini settings, cause they are not needed and take up much memory
		$this->cfg["acl"] = $GLOBALS["cfg"]["acl"];
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
		$ret = $this->db_fetch_row("SELECT * FROM objects WHERE oid = $oid");
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
				$ret[$prop["name"]] = aw_unserialize($objdata[$prop["field"]]);
			}
			else
			{
				$ret[$prop["name"]] = $objdata[$prop["field"]];
			}
		}

		//echo dbg::dump($tableinfo);
		// do a query for each table
		foreach($tables as $table)
		{
			//echo "table = $table <br>";
			$fields = array();
			foreach($tbl2prop[$table] as $prop)
			{
				$fields[] = $prop["field"];
			}

			if (count($fields) > 0)
			{
				$q = "SELECT ".join(",", $fields)." FROM $table WHERE ".$tableinfo[$table]["index"]." = '".$objdata["brother_of"]."'";
				//echo "q = $q <br>";
				$data = $this->db_fetch_row($q);
				if (is_array($data))
				{
					$ret += $data;
				}

				foreach($tbl2prop[$table] as $prop)
				{
					if ($prop["method"] == "serialize")
					{
						$ret[$prop["name"]] = aw_unserialize($this->obj["properties"][$prop["name"]]);
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
		//echo "q = <pre>". htmlentities($q)."</pre> <br>";
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
			//echo "q = <pre>". htmlentities($q)."</pre> <br>";
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

		$metadata = aw_serialize($objdata["metadata"]);
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
//		echo "q = <pre>". htmlentities($q)."</pre> <br>";
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
				//echo "q = <pre>". htmlentities($q)."</pre> <br>";
				$this->db_query($q);
			}
		}
	}

	function read_connection($id)
	{
		return $this->db_fetch_row("SELECT * FROM aliases WHERE id = $id");
	}

	function save_connection($data)
	{
		if ($data["id"])
		{
			$q = "UPDATE aliases SET 
				source = '$data[source]',
				target = '$data[target]',
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
				'$data[source]',			'$data[target]',		'$data[type]',			'$data[data]',
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

	function find_connection($from, $to)
	{
		return $this->db_fetch_field("SELECT id FROM aliases WHERE source = '$from' AND target = '$to'", "id");
	}

	// arr - { [from] , [to] }
	function find_connections($arr)
	{
		$sql = "
			SELECT 
				* 
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
		if ($params["class_id"])
		{
			$classes = aw_ini_get("classes");
			list($properties, $tableinfo) = $GLOBALS["object_loader"]->load_properties(array(
				"file" => $classes[$params["class_id"]]["file"],
				"clid" => $params["class_id"]
			));
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
				if (strpos("%", $val) !== false)
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
			$q = "SELECT oid FROM objects $joins WHERE $where $sby";
			//echo "q = $q <br>";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$ret[$row["oid"]] = $row["oid"];
			}
		}
		return $ret;
	}
}

?>
