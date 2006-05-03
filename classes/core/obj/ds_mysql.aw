<?php

class _int_obj_ds_mysql extends _int_obj_ds_base
{
	function _int_obj_ds_mysql()
	{
		$this->init();
		$this->cache = get_instance("cache");
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

		return $this->db_fetch_field($q, OID);
	}

	function get_objdata($oid, $param = array())
	{
		if (!empty($GLOBALS["object2version"][$oid]) && $GLOBALS["object2version"][$oid] != "_act")
		{
			$v = $GLOBALS["object2version"][$oid];
			$ret = $this->db_fetch_row("SELECT * FROM objects WHERE oid = '$oid' AND status != 0");
			$ret2 = $this->db_fetch_row("SELECT o_alias, o_jrk, o_metadata FROM documents_versions WHERE docid = '$oid' AND version_id = '$v'");
			$ret["alias"] = $ret2["o_alias"];
			$ret["jrk"] = $ret2["o_jrk"];
			$ret["metadata"] = $ret2["o_metadata"];
			$rv =  $this->_get_objdata_proc($ret, $param, $oid);
			return $rv;
		}

		if (isset($this->read_properties_data_cache[$oid]))
		{
			$ret = $this->_get_objdata_proc($this->read_properties_data_cache[$oid], $param, $oid);
		}
		else
		{
			$ret = $this->db_fetch_row("SELECT * FROM objects WHERE oid = '$oid' AND status != 0");
			$ret = $this->_get_objdata_proc($ret, $param, $oid);
		}
		return $ret;
	}

	function _get_objdata_proc($ret, $param, $oid = -1)
	{
		if ($ret === false)
		{
			if ($param["no_errors"])
			{
				return NULL;
			}
			else
			{
				error::raise(array(
					"id" => ERR_NO_OBJ,
					"msg" => sprintf(t("object::load(%s): no such object!"), $oid)
				));
			}
		}

		$ret["meta"] = aw_unserialize($ret["metadata"]);
		if ($ret["metadata"] != "" && $ret["meta"] === NULL)
		{
			$ret["meta"] = aw_unserialize(stripslashes(stripslashes($ret["metadata"])));
		}
		unset($ret["metadata"]);

		if ($ret["brother_of"] == 0)
		{
			$ret["brother_of"] = $ret["oid"];
		}

		// unserialize acldata
		$ret["acldata"] = aw_unserialize($ret["acldata"]);

		// filter it for all current groups 

		// or we could join the acl table based on the current user. 
		// but we can't do that, cause here we can't do things based on the user
		// then again we could just read all the acl and save that. maybe. you think?

		// crap. descisions, descisions...

		// ok, so try for the store-shit-in-object-table
		return $ret;
	}

	// parameters:
	//	properties - property array
	//	tableinfo - tableinfo from propreader
	//	objdata - result of this::get_objdata
	function read_properties($arr)
	{
		extract($arr);
		if (!empty($GLOBALS["object2version"][$objdata["oid"]]) && $GLOBALS["object2version"][$objdata["oid"]] != "_act")
		{
			$arr["objdata"]["load_version"] = $GLOBALS["object2version"][$objdata["oid"]];
			return $this->load_version_properties($arr);
		}
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
				$ret[$prop["name"]] = isset($objdata[$prop['field']]) && isset($objdata[$prop["field"]][$prop["name"]]) ? $objdata[$prop["field"]][$prop["name"]] : "";
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

			if (isset($prop["datatype"]) && $prop["datatype"] == "int" && $ret[$prop["name"]] == "")
			{
				$ret[$prop["name"]] = "0";
			}
		}

		// fix old broken databases where brother_of may be 0 for non-brother objects
		$object_id = ($objdata["brother_of"] ? $objdata["brother_of"] : $objdata["oid"]);

		$conn_prop_vals = array();
		$conn_prop_fetch = array();

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
						$fields[] = $table.".`".$prop["field"]."` AS `".$prop["field"]."`";
						$_got_fields[$prop["field"]] = true;
					}
				}
				else
				if ($prop["store"] == "connect")
				{
					if ($GLOBALS["cfg"]["__default"]["site_id"] != 139)
					{
						$_co_reltype = $prop["reltype"];
						$_co_reltype = $GLOBALS["relinfo"][$objdata["class_id"]][$_co_reltype]["value"];

						if ($_co_reltype == "")
						{
							error::raise(array(
								"id" => "ERR_NO_RT",
								"msg" => sprintf(t("ds_mysql::read_properties(): no reltype for prop %s (%s)"), $prop["name"], $prop["reltype"])
							));
						}

						$conn_prop_fetch[$prop["name"]] = $_co_reltype;
					}
				}
				else
				{
					$fields[] = $table.".`".$prop["field"]."` AS `".$prop["name"]."`";
				}
			}

			if (count($fields) > 0)
			{
				$q = "SELECT ".join(",", $fields)." FROM $table WHERE `".$tableinfo[$table]["index"]."` = '".$object_id."'";
				
				if (isset($this->read_properties_data_cache[$object_id]))
				{
					$data = $this->read_properties_data_cache[$object_id];
				}
				else
				{
					$data = $this->db_fetch_row($q);
				}
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

					if (isset($prop["datatype"]) && $prop["datatype"] == "int" && $ret[$prop["name"]] == "")
					{
						$ret[$prop["name"]] = "0";
					}
				}
			}
		}


		if (count($conn_prop_fetch))
		{
			$cpf_dat = array();
			if (isset($this->read_properties_data_cache_conn[$object_id]))
			{
				$cfp_dat = $this->read_properties_data_cache_conn[$object_id];
			}
			else
			{
				$q = "
					SELECT 
						target,
						reltype
					FROM 
						aliases 
					LEFT JOIN objects ON objects.oid = aliases.target
					WHERE 
						source = '".$object_id."' AND 
						reltype IN (".join(",", map("'%s'", $conn_prop_fetch)).") AND 
						objects.status != 0
				";
				$this->db_query($q);
				while ($row = $this->db_next())
				{
					$cfp_dat[] = $row;
				}
			}

			foreach($cfp_dat as $row)
			{
				$prop_name = array_search($row["reltype"], $conn_prop_fetch);
				if (!$prop_name)
				{
					error::raise(array(
						"id" => "ERR_NO_PROP",
						"msg" => sprintf(t("ds_mysql::read_properties(): no prop name for reltype %s in store=connect fetch! q = %s"), $row["reltype"], $q)
					));
				}

				$prop = $properties[$prop_name];
				if ($prop["multiple"] == 1)
				{
					$ret[$prop_name][$row["target"]] = $row["target"];
				}
				else
				{
					if (!isset($ret[$prop_name])) // just the first one
					{
						$ret[$prop_name] = $row["target"];
					}
				}
			}
		}

		return $ret;
	}


	// parameters:
	//	properties - property array
	//	tableinfo - tableinfo from propreader
	//  object_id - array of object id's
	// class_id - class id
	// full - bool, true - read objdata, false - just tables
	function get_read_properties_sql($arr)
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

		$conn_prop_fetch = array();

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
						$fields[] = $table.".`".$prop["field"]."` AS `".$prop["field"]."`";
						$_got_fields[$prop["field"]] = true;
					}
				}
				else
				if ($prop["store"] == "connect")
				{
					if ($GLOBALS["cfg"]["__default"]["site_id"] != 139)
					{
					// resolve reltype and do find_connections
					//$values = array();
					$_co_reltype = $prop["reltype"];
					$_co_reltype = $GLOBALS["relinfo"][$class_id][$_co_reltype]["value"];

					$conn_prop_fetch[$prop["name"]] = $_co_reltype;

					//$conn_prop_vals[$prop["name"]] = $values;
					//echo "resolved reltype to ".dbg::dump($_co_reltype)." <br>";
					}
				}
				else
				{
					$fields[] = $table.".`".$prop["field"]."` AS `".$prop["name"]."`";
				}
			}

			if ($q != "")
			{
				return array();
			}

			if ($full)
			{
				$q = "SELECT 
					objects.oid as oid,
					objects.parent as parent,
					objects.name as name,
					objects.createdby as createdby,
					objects.class_id as class_id,
					objects.created as created,
					objects.modified as modified,
					objects.status as status,
					objects.hits as hits,
					objects.lang_id as lang_id,
					objects.comment as comment,
					objects.last as last,
					objects.modifiedby as modifiedby,
					objects.jrk as jrk,
					objects.visible as visible,
					objects.period as period,
					objects.alias as alias,
					objects.periodic as periodic,
					objects.site_id as site_id,
					objects.brother_of as brother_of,
					objects.cachedirty as cachedirty,
					objects.metadata as metadata,
					objects.subclass as subclass,
					objects.cachedata as cachedata,
					objects.flags as flags";
				if (aw_ini_get("acl.use_new_acl") == 1)
				{
					$q .= ",objects.acldata as acldata";
				}
				if (count($fields) > 0)
				{
					$q .= ",".join(",", $fields)." FROM objects LEFT JOIN $table ON objects.brother_of = ".$table.".`".$tableinfo[$table]["index"]."` WHERE ";
					$q .= " objects.oid ";
				}
				else
				{
					$q .= " FROM objects WHERE oid";
				}
			}
			else
			if (count($fields) > 0)
			{
				$q = "SELECT ".join(",", $fields)." FROM $table WHERE `".$tableinfo[$table]["index"]."`";
			}

			if (!$full)
			{
				if (is_array($object_id))
				{
					$q .= " IN (".join(",", $object_id).")";
				}
				else
				{
					$q .= " = '".$object_id."'";
				}
			}
		}


		if (count($conn_prop_fetch))
		{
			if (is_array($object_id))
			{
				$source = "source IN (".join(",", map("'%s'", $object_id)).")";
			}
			else
			{
				$source  = "source = '".$object_id."'";
			}
			$q2 = "
				SELECT 
					source,
					target,
					reltype
				FROM 
					aliases 
				LEFT JOIN objects ON objects.oid = aliases.target
				WHERE 
					$source AND 
					reltype IN (".join(",", map("'%s'", $conn_prop_fetch)).") AND 
					objects.status != 0
			";
		}

		return array("q" => $q, "q2" => $q2, "conn_prop_fetch" => $conn_prop_fetch);
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
		// insert default new acl to object table here
		$acld_fld = $acld_val = "";
		if (aw_ini_get("acl.use_new_acl") && $_SESSION["uid"] != "")
		{
			$g_d = aw_global_get("current_user_group");
			$acld_fld = ",acldata";
			$acld_val = ",'".str_replace("'", "\\'", aw_serialize(array(
				$g_d["oid"] => $this->get_acl_value_n($this->acl_get_default_acl_arr())
			)))."'";
		}

		// create oid
		$q = "
			INSERT INTO objects (
				parent,						class_id,						name,						createdby,
				created,					modified,						status,						site_id,
				hits,						lang_id,						comment,					modifiedby,
				jrk,						period,							alias,						periodic,
				cachedirty,					metadata,						subclass,					flags
				$acld_fld
		) VALUES (
				'".$objdata["parent"]."',	'".$objdata["class_id"]."',		'".$objdata["name"]."',		'".$objdata["createdby"]."',
				'".$objdata["created"]."',	'".$objdata["modified"]."',		'".$objdata["status"]."',	'".$objdata["site_id"]."',
				'".$objdata["hits"]."',		'".$objdata["lang_id"]."',		'".$objdata["comment"]."',	'".$objdata["modifiedby"]."',
				'".$objdata["jrk"]."',		'".$objdata["period"]."',		'".$objdata["alias"]."',	'".$objdata["periodic"]."',
				'1',						'".$metadata."',				'".$objdata["subclass"]."',	'".$objdata["flags"]."'
				$acld_val
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

		// cache data
		if (aw_ini_get("cache.table_is_sep"))
		{
			$this->db_query("INSERT INTO objects_cache_data(oid) values($oid)");
		}

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
					// if the prop is in a serialized field, then respect that
					if ($data["method"] == "serialize")
					{
						// unpack field, add value, repack field
						$_field_val = aw_unserialize($tbls[$data["table"]]["defaults"][$data["field"]]);
						$_field_val[$prop] = $objdata["properties"][$prop];
						$tbls[$data["table"]]["defaults"][$data["field"]] = aw_serialize($_field_val);
					}
					else
					{
						$tbls[$data["table"]]["defaults"][$data["field"]] = $objdata["properties"][$prop];
					}
				}
				else
				{
					$tbls[$data["table"]]["defaults"][$data["field"]] = $data["default"];
				}

				if ($data["datatype"] == "int" && $tbls[$data["table"]]["defaults"][$data["field"]] == "")
				{
					$tbls[$data["table"]]["defaults"][$data["field"]] = "0";
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
					$fds .=",`".$fd."`";
					$vls .=",'".$vl."'";
				}
			}

			$q = "INSERT INTO $tbl (".$idx.$fds.") VALUES('".$oid."'".$vls.")";
			$this->db_query($q);
		}

		// we need to clear the html cache here, not in ds_cache, because ds_cache can be not loaded
		// even when html caching is turned on
		// now. there are two ways of doing this:
		// 1) clear the html cache folder
		// 2) $this->cache->flush_cache() that clears the object table in db
		// now, previously 1) would have been pretty slow
		// but now it should no longer be, so we do 1)
		$this->cache->file_clear_pt("html");

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

		if ($arr["create_new_version"] == 1)
		{
			return $this->save_properties_new_version($arr);
		}
		if ($GLOBALS["object2version"][$arr["objdata"]["oid"]] != "")
		{
			$arr["objdata"]["version_id"] = $GLOBALS["object2version"][$arr["objdata"]["oid"]];
			return $this->save_properties_new_version($arr);
		}

		$metadata = aw_serialize($objdata["meta"]);
		$this->quote(&$metadata);
		$this->quote(&$objdata);
		$objdata["metadata"] = $metadata;

		if ($objdata["brother_of"] == 0)
		{
			$objdata["brother_of"] = $objdata["oid"];
		}

		$ot_sets = array();
		if (!isset($arr["ot_modified"]))
		{
			$arr["ot_modified"] = $GLOBALS["object_loader"]->all_ot_flds;
		}
		foreach(safe_array($arr["ot_modified"]) as $_field => $one)
		{
			$ot_sets[] = " $_field = '".$objdata[$_field]."' ";
		}

		$ot_sets = join(" , ", $ot_sets);

		$q = "UPDATE objects SET
			$ot_sets
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

		// remove all props that are not supposed to be saved
		if (isset($arr["props_modified"]))
		{
			// get a list of all table fields that have modified props in them
			// and include all props that are modified that are written to those
			// cause if we do not do that, it breaks serialized fields with several props
			$mod_flds = array();
			foreach(safe_array($arr["props_modified"]) as $_pn => $_one)
			{
				$mod_flds[$properties[$_pn]["table"]][$properties[$_pn]["field"]] = 1;
			}

			$tmp = array();
			foreach($tbls as $tbl => $tbld)
			{
				foreach($tbld as $idx => $prop)
				{
					if ($arr["props_modified"][$prop["name"]] == 1 || isset($mod_flds[$prop["table"]][$prop["field"]]))
					{
						$tmp[$tbl][$idx] = $prop;
					}
				}
			}
			$tbls = $tmp;
		}

		// now save all props to tables.
		foreach($tbls as $tbl => $tbld)
		{
			if ($tbl == "")
			{
				continue;
			}

			if ($tbl == "objects")
			{
				continue;
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

					if ($prop["datatype"] == "int" && $seta[$prop["field"]] == "")
					{
						$seta[$prop["field"]] = "0";
					}
				}
			}

			foreach($serfs as $field => $dat)
			{
				$str = aw_serialize($dat);
				$this->quote($str);
				$seta[$field] = $str;
			}
			$sets = join(",",map2("`%s` = '%s'",$seta,0,true));
			if ($sets != "")
			{
				$q = "UPDATE $tbl SET $sets WHERE ".$tableinfo[$tbl]["index"]." = '".$objdata["brother_of"]."'";
				$this->db_query($q);
			}
		}

		unset($GLOBALS["__obj_sys_objd_memc"][$objdata["brother_of"]]);
		unset($GLOBALS["__obj_sys_objd_memc"][$objdata["oid"]]);

		unset($this->read_properties_data_cache[$objdata["oid"]]);
		unset($this->read_properties_data_cache[$objdata["brother_of"]]);

		$this->cache->file_clear_pt("html");
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

		$this->cache->file_clear_pt("html");

		return $data['id'];
	}

	function delete_connection($id)
	{
		$this->db_query("DELETE FROM aliases WHERE id = '$id'");
		$this->cache->file_clear_pt("html");
	}

	function connection_query_fetch()
	{
		$ret = "a.id as `id`,
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
				o_s.status as `from.status`,
				o_t.parent as `to.parent`,
				o_s.parent as `from.parent`,
				o_t.comment as `to.comment`,
				o_s.comment as `from.comment`
		";

		if ($GLOBALS["cfg"]["acl"]["use_new_acl"])
		{
			$ret .= ",o_t.acldata as `to.acldata`,o_s.acldata as `from.acldata`";
		}
		return $ret;
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
					if (strpos($v, "%") !== false)
					{
						$sql .= " AND o_t.".substr($k, 3)." LIKE '$v' ";
					}
					else
					{
						$sql .= " AND o_t.".substr($k, 3)." = '$v' ";
					}
				};
			}
			if (substr($k, 0, 5) == "from.")
			{
				if (is_array($v))
				{
					$sql .= " AND o_s.".substr($k, 5)." IN (" . join(",",$v) . ") ";
				}
				else
				{
					if (strpos($v, "%") !== false)
					{
						$sql .= " AND o_s.".substr($k, 5)." LIKE '$v' ";
					}
					else
					{
						$sql .= " AND o_s.".substr($k, 5)." = '$v' ";
					}
				};
			}
		}

		$sql .= " ORDER BY a.id ";

		$this->db_query($sql);
		$ret = array();
		while ($row = $this->db_next())
		{
			$row["from.acldata"] = aw_unserialize($row["from.acldata"]);
			$row["to.acldata"] = aw_unserialize($row["to.acldata"]);
			$ret[$row["id"]] = $row;
		}

		return $ret;
	}

	// params:
	//	array of filter parameters 
	// if class id is present, properties can also be filtered, otherwise only object table fields
	function search($params, $to_fetch = NULL)
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

		$this->joins = array();

		$this->has_data_table_filter = false;
		list($fetch_sql, $fetch_props, $fetch_metafields) = $this->_get_search_fetch($to_fetch);

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

		$joins = $this->_get_joins($params);

		$ret = array();
		if ($where != "")
		{
			if ($GLOBALS["cfg"]["acl"]["use_new_acl"])
			{
				$acld = ", objects.acldata as acldata, objects.parent as parent";
			}

			$datafetch = true;
			if ($fetch_sql == "")
			{
				$fetch_sql = "					
					objects.oid as oid,
					objects.name as name,
					objects.parent as parent, 
					objects.brother_of as brother_of,
					objects.status as status,
					objects.class_id as class_id
					$acld 
				";
				$datafetch = false;
			}

			$q = "
				SELECT 
					$fetch_sql
				FROM 
					$joins 
				WHERE 
					$where ".$this->sby." ".$this->limit;

			$acldata = array();
			$parentdata = array();
			$objdata = array();
			$this->db_query($q);

			if ($datafetch)
			{
				$ret = array();
				while($row = $this->db_next())
				{
					// process metafields
					foreach($fetch_metafields as $f_mf => $f_keys)
					{
						$f_unser = aw_unserialize($row[$f_mf]);
						foreach($f_keys as $f_key_name)
						{
							$row[$f_key_name] = $f_unser[$f_key_name];
						}
						unset($row[$f_mf]);
					}
					$ret[] = $row;
				}
				return $ret;
			}
			else
			{
				while ($row = $this->db_next())
				{
					$ret[$row["oid"]] = $row["name"];
					$parentdata[$row["oid"]] = $row["parent"];
					$objdata[$row["oid"]] = array(
						"brother_of" => $row["brother_of"],
						"status" => $row["status"],
						"class_id" => $row["class_id"]
					);
					if ($GLOBALS["cfg"]["acl"]["use_new_acl"])
					{
						$row["acldata"] = safe_array(aw_unserialize($row["acldata"]));
						$acldata[$row["oid"]] = $row;
					}
				}
			}
		}

		return array($ret, $this->meta_filter, $acldata, $parentdata, $objdata);
	}

	function delete_object($oid)
	{
		$this->db_query("UPDATE objects SET status = '".STAT_DELETED."', modified = ".time().",modifiedby = '".aw_global_get("uid")."' WHERE oid = '$oid'");
		//$this->db_query("DELETE FROM aliases WHERE target = '$oid'");
		//$this->db_query("DELETE FROM aliases WHERE source = '$oid'");
		$this->cache->file_clear_pt("html");
	}

	function final_delete_object($oid)
	{
		$clid = $this->db_fetch_field("SELECT class_id FROM objects WHERE oid = '$oid'", "class_id");
		if (!$clid)
		{
			error::raise(array(
				"id" => "ERR_NO_OBJECT",
				"msg" => sprintf(t("ds_mysql::final_delete_object(%s): no suct object exists!"), $oid)
			));
		}
			
		// load props by clid
		$cl = aw_ini_get("classes");
		$file = $cl[$clid]["file"];
		if ($clid == 29)
		{
			$file = "doc";
		}

		list($properties, $tableinfo, $relinfo) = $GLOBALS["object_loader"]->load_properties(array(
			"file" => basename($file),
			"clid" => $clid
		));

		$tableinfo = safe_array($tableinfo);
		$tableinfo["objects"] = array(
			"index" => "oid"
		);
		foreach($tableinfo as $tbl => $inf)
		{
			$sql = "DELETE FROM $tbl WHERE $inf[index] = '$oid' LIMIT 1";
			$this->db_query($sql);
		}

		// also, aliases
		$this->db_query("DELETE FROM aliases WHERE source = '$oid' OR target = '$oid'");
		$this->cache->file_clear_pt("html");
	}

	function req_make_sql($params, $logic = "AND", $dbg = false)
	{
		$sql = array();
		$p_tmp = $params;
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

			if ("join_strategy" == (string)($key))
			{
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

			// check for dots in key. if there are any, then we gots some join thingie
			if (strpos($key, ".") !== false)
			{
				list($tbl, $fld) = $this->_do_proc_complex_param(array(
					"key" => &$key, 
					"val" => $val, 
					"params" => $p_tmp
				));
			}
			else
			if (isset($this->properties[$key]) && $this->properties[$key]["store"] != "no")
			{
				$tbl = $this->properties[$key]["table"];
				$fld = $this->properties[$key]["field"];
				if ($fld == "meta")
				{
					if ($this->properties[$key]["store"] != "connect")
					{
						$this->meta_filter[$key] = $val;
						continue;
					}
				}
				else
				if ($this->properties[$key]["method"] == "serialize")
				{
					error::raise(array(
						"id" => ERR_FIELD,
						"msg" => sprintf(t("filter cannot contain properties (%s) that are in serialized fields other than metadata!"), $key)
					));
				}
				$this->used_tables[$tbl] = $tbl;
			}

			if ($tbl != "objects")
			{
				$this->has_data_table_filter = true;
			}
			$tf = $tbl.".`".$fld."`";

			if ($this->properties[$key]["store"] == "connect")
			{
				// join aliases as many-many relation and filter by that
				if ($tbl == "objects")
				{
					$idx = "brother_of";
				}
				else
				{
					$idx = $this->tableinfo[$tbl]["index"];
				}
				$this->alias_joins[$key] = array(
					"name" => "aliases_".$key,
					"on" => $tbl.".".$idx." = "."aliases_".$key.".source"
				);
			}

			if (($this->properties[$key]["method"] == "bitmask" || $key == "flags") && is_array($val))
			{
				$sql[] = $tf." & ".$val["mask"]." = ".((int)$val["flags"]);
			}
			else
			if (($this->properties[$key]["method"] == "bitmask") && !is_array($val) && $this->properties[$key]["ch_value"] > 0)
			{
				if (is_object($val))
				{
					switch(get_class($val))
					{
						case "obj_predicate_not":
							$sql[] = $tf." & ".((int)$this->properties[$key]["ch_value"])." != ".((int)$this->properties[$key]["ch_value"]);
							break;

						default:
							error::raise(array(
								"id" => "OBJ_BF_NOTSUPPORTED",	
								"msg" => sprintf(t("complex compares of this type (%s) are not yet supported on bitfields (%s)!"), get_class($val), $key)
							));
							return;
					}
				}
				else
				{
					$sql[] = $tf." & ".((int)$this->properties[$key]["ch_value"])." = ".((int)$this->properties[$key]["ch_value"]);
				}
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
						$aa = $this->req_make_sql($val->filter["conditions"], $val->filter["logic"],true);
						if ($aa != "")
						{
							$sql[] = "(".$aa.")";
						}
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
						$has_pct = false;
						$tmp_sql = array();
						foreach($v_data as $__val)
						{
							if (strpos($__val , "%") !== false)
							{
								$has_pct = true;
							}
							$tmp_sql[] = $tf." NOT LIKE '$__val' ";
						}
						if ($has_pct)
						{
							$sql[] = " ( ".join(" AND ", $tmp_sql)." ) ";
						}
						else
						{
							$sql[] = $tf." NOT IN (".join(",", $v_data).") ";
						}
					}
					else
					{
						$opn_app = "";
						if ($v_data != 0)
						{
							$opn_app = "OR $tf IS NULL";
						}
	
						if (strpos($v_data, "%") !== false)
						{
							$sql[] = " (".$tf." NOT LIKE '".$v_data."'  $opn_app ) ";
						}
						else
						{
							$sql[] = " (".$tf." != '".$v_data."'  $opn_app ) ";
						}
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

						case OBJ_COMP_BETWEEN:
							$comparator = " > ".$v_data." AND $tf < ";
							$v_data = $val->data2;
							break;

						case OBJ_COMP_BETWEEN_INCLUDING:
							$comparator = " >= ".$v_data." AND $tf <= ";
							$v_data = $val->data2;
							break;

						case OBJ_COMP_EQUAL:
							$comparator = " = ";
							$v_data = $val->data2;
							break;

						case OBJ_COMP_NULL:
							$comparator = " IS NULL ";
							$v_data = "";
							break;

						default:
							error::raise(array(
								"id" => ERR_OBJ_COMPARATOR,
								"msg" => sprintf(t("obj_predicate_compare's comparator operand must be either OBJ_COMP_LESS,OBJ_COMP_GREATER,OBJ_COMP_LESS_OR_EQ,OBJ_COMP_GREATER_OR_EQ. the value supplied, was: %s!"), $val->comparator)
							));
					}

					if ($val->comparator == OBJ_COMP_NULL)
					{
						$sql[] = $tf." IS NULL ";
					}
					else
					if (is_array($v_data))
					{
						$tmp = array();
						foreach($v_data as $d_k)
						{
							$tmp[] = $tf." $comparator '$d_k' ";
						}
						$sql[] = "(".join(" OR ", $tmp).")";
					}
					else
					{
						$sql[] = $tf." $comparator '".$v_data."' ";
					}
				}
				else
				if ($class_name == "obj_predicate_prop")
				{
					if ($val->prop == "id")
					{
						$tbl2 = "objects";
						$fld2 = "oid";
					}
					else
					{
						$tbl2 = $this->properties[$val->prop]["table"];
						$fld2 = $this->properties[$val->prop]["field"];
					}
					switch($val->compare)
					{
						case OBJ_COMP_LESS:
							$compr = " < ";
							break;

						case OBJ_COMP_GREATER:
							$compr = " > ";
							break;

						case OBJ_COMP_LESS_OR_EQ:
							$compr = " <= ";
							break;

						case OBJ_COMP_GREATER_OR_EQ:
							$compr = " >= ";
							break;

						case OBJ_COMP_BETWEEN:
							error::raise(array(
								"id" => "ERR_WRONG_COMPARATOR",
								"msg" => t("OBJ_COMP_BETWEEN does not make sense with obj_predicate_prop!")
							));
							break;

						default:
						case OBJ_COMP_EQUAL:
							$compr = " = ";
							break;
					}
					$sql[] = $tf.$compr.$tbl2.".".$fld2." ";
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

					$this->quote(&$v);
					if ($this->properties[$key]["store"] == "connect")
					{
						$str[] = " aliases_".$key.".target = '$v' ";
					}
					else
					if (strpos($v, "%") !== false)
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
				$this->quote(&$val);
				if ($this->properties[$key]["store"] == "connect")
				{
					$sql[] = " aliases_".$key.".target = '$val' ";
				}
				else
				if (($key == "modified" && strpos($val, "%") === false) || $key == "flags")
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
			if (!is_class_id($clid))
			{
				continue;
			}
			if (!isset($GLOBALS["properties"][$clid]) || !isset($GLOBALS["tableinfo"][$clid]) || !isset($GLOBALS["relinfo"][$clid]))
			{
				list($GLOBALS["properties"][$clid], $GLOBALS["tableinfo"][$clid], $GLOBALS["relinfo"][$clid]) = $GLOBALS["object_loader"]->load_properties(array(
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

		$objdata["createdby"] = $objdata["modifiedby"] = aw_global_get("uid");		
		$objdata["created"] = $objdata["modified"] = time();		

		$objdata["lang_id"] = aw_global_get("lang_id");		
		$objdata["site_id"] = aw_ini_get("site_id");		

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
				'".$objdata["jrk"]."',		'".$objdata["period"]."',		'".$objdata["alias"]."',	'".$objdata["periodic"]."',
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

		$this->cache->file_clear_pt("html");

		return $oid;
	}

	// $key, $val 
	function _do_proc_complex_param($arr)
	{
		extract($arr);
		
		$filt = explode(".", $key);
		$clid = constant($filt[0]);
		
		if (!is_class_id($clid))
		{
			if (!is_array($params["class_id"]))
			{
				error::raise_if(!is_class_id($params["class_id"]), array(
					"id" => ERR_OBJ_NO_CLID,
					"msg" => sprintf(t("ds_mysql::do_proc_complex_param(%s, %s): if a complex join parameter is given without a class id as the first element, the class_id parameter must be set!"), $key, $val)
				));
				$clid = $params["class_id"];
			}
			else
			{
				$clid = reset($params["class_id"]);
			}
		}
		else
		{
			// if the first part is a class id and there are only two parts then it is not a join
			// then it is a specification on what class's property to search from 
			// UNLESS the second part begins with RELTYPE
			if (count($filt) == 2)
			{
				if (substr($filt[1], 0, 7) != "RELTYPE")
				{
					// so just return the table and field for that class
					$prop = $GLOBALS["properties"][$clid][$filt[1]];
					$this->used_tables[$prop["table"]] = $prop["table"];
					return array($prop["table"], $prop["field"]);	
				}
			}
		}

		$this->foo = array();
		$this->join_data = array();
		$this->_req_do_pcp($filt, 1, $clid, $arr);
//		$this->joins = array();
		// join all other tables from the starting class except the objects table
		$tmp = $GLOBALS["tableinfo"][$clid];
		unset($tmp["objects"]);
		foreach($tmp as $tbl => $tbldat)
		{
			// check uniqueness
			$str = " LEFT JOIN $tbl ".$tbl."_".$clid." ON ".$tbl."_".$clid.".".$tbldat["index"]." = ".$tbldat["master_table"].".".$tbldat["master_index"]." ";
			if (!in_array($str, $this->joins))
			{
				$this->joins[] = $str;
			}
		}
		// now make joins and for the final prop, query
		foreach($this->join_data as $pos => $join)
		{
			if ($join["via"] == "rel")
			{
				// from prev to alias from alias to obj
				$prev_t = $join["table"]."_".$join["from_class"];
				$prev_clid = $join["from_class"];

				$str  = " LEFT JOIN aliases aliases_".$join["from_class"]."_".$join["reltype"]." ON aliases_".$join["from_class"]."_".$join["reltype"].".source = ";
				if ($join["from_class"] == $clid)
				{
					$str .= " objects.oid ";
				}
				else
				{
					$str .= " objects_".$join["from_class"].".oid ";
				}

				if ($join["reltype"])
				{
					$str .= " AND aliases_".$join["from_class"]."_".$join["reltype"].".reltype = ".$join["reltype"];
				}
				$this->joins[] = $str;

				$str  = " LEFT JOIN objects objects_".$join["to_class"]."_".$join["reltype"]." ON aliases_".$join["from_class"]."_".$join["reltype"].".target = ";
				$str .= " objects_".$join["to_class"]."_".$join["reltype"].".oid ";
				$prev_clid = $join["to_class"];
				$this->joins[] = $str;

				$ret = array(
					"aliases_".$join["from_class"]."_".$join["reltype"],
					"target",
				);
			}
			else	// via prop
			{
				if (!$join["to_class"] && $this->join_data[$pos-1]["via"] == "rel")
				{
					$prev = $this->join_data[$pos-1];

					$this->_do_add_class_id($join["from_class"]);
					// join from rel to prop
					$prev_t = "aliases_".$prev["from_class"]."_".$prev["reltype"];
					$new_t = $GLOBALS["tableinfo"][$join["from_class"]];
					$do_other_join = false;
					if (!is_array($new_t) || $GLOBALS["properties"][$join["from_class"]][$join["prop"]]["table"] == "objects")
					{
						// class only has objects table, so join that
						$tbl = "objects_rel_".$prev["reltype"];
						$tbl_r = "objects";
						$field = "oid";

						// and also join any other tables as well just to be on the safe side.
						$do_other_join = is_array($new_t);
					}
					else
					{
						$tbl = $tbl_r = reset(array_keys($new_t));
						$field = $new_t[$tbl]["index"];
					}

					$str = " LEFT JOIN ".$tbl_r." $tbl ON ".$tbl.".".$field." = ".$prev_t.".target ";
					$this->joins[] = $str;
					$ret = array(
						$tbl,
						$join["field"],
					);

					break;
				}
				else
				if (!$join["to_class"])
				{
					if ($pos == (count($this->join_data)-1))
					{
						$prev_t = $join["table"]."_".$prev_clid."_".$prev_filt;
					}
					$__fld = $GLOBALS["properties"][$join["from_class"]][$join["prop"]]["field"];
					if ($join["prop"] == "class_id")
					{
						$__fld = "class_id";
					}
					$ret = array(
						$prev_t,
						$__fld,
					);
					continue;
				}
				// if the next stop is a property
				// then join all the tables in that class
				// first the objects table

				if (!$prev_t)
				{
					$prev_t = $join["table"]."_".$join["from_class"];
				}
				$prev_filt = $join["field"];
				$prev_clid = $join["from_class"];
				
				$objt_name = "objects_".$join["from_class"]."_".$join["field"];
				if (!isset($done_ot_js[$objt_name]))
				{
					$this->joins[] = " LEFT JOIN objects $objt_name ON ".$objt_name.".oid = $prev_t.".$join["field"]." ";
					$done_ot_js[$objt_name] = 1;
				}

				$new_t = $GLOBALS["tableinfo"][$join["to_class"]];

				if (is_array($new_t))
				{
					$tbl = $tbl_r = reset(array_keys($new_t));
					$field = $new_t[$tbl]["index"];
					$tbl .= "_".$join["from_class"]."_".$join["field"];
					if (!isset($done_ot_js[$tbl_r]))
					{
						$str = " LEFT JOIN ".$tbl_r." $tbl ON ".$tbl.".".$field." = ".$objt_name.".brother_of";
						$this->joins[] = $str;
						$done_ot_js[$tbl_r] = 1;
						$prev_t = $tbl;
					}

					// now, if the next join is via rel, we are gonna need the objects table here as well, so add that
					if ($this->join_data[$pos+1]["via"] == "rel")
					{
						$o_field = "oid";
						$o_tbl = "objects_".$join["to_class"];
						if (!isset($done_ot_js[$o_tbl]))
						{
							$str = " LEFT JOIN objects $o_tbl ON ".$o_tbl.".".$o_field." = ".$tbl.".".$field;
							$this->joins[] = $str;
						}
					}
				}
			}
		}

		$arr["key"] = $filt[count($filt)-1];
		$this->joins = array_unique($this->joins);
		return $ret;
	}

	function _req_do_pcp($filt, $pos, $cur_clid, $arr)
	{
		$pp = $filt[$pos];

		// if the next param is RELTYPE_* then via relation
		// else, if it is property for cur class - via property
		// else - throw up

		if (substr($pp, 0, 7) == "RELTYPE")
		{
			$this->_do_add_class_id($cur_clid);
			$reltype_id = $GLOBALS["relinfo"][$cur_clid][$pp]["value"];
			error::raise_if(!$reltype_id && $pp != "RELTYPE", array(
				"id" => ERR_OBJ_NO_RELATION,
				"msg" => sprintf(t("ds_mysql::_req_do_pcp(): no relation from class %s named %s"), $cur_clid, $pp)
			));

			// calc new class id
			$new_clid = $GLOBALS["relinfo"][$cur_clid][$pp]["clid"][0];

			$this->join_data[] = array(
				"via" => "rel",
				"reltype" => $reltype_id,
				"from_class" => $cur_clid,
				"to_class" => $new_clid
			);
		}
		else
		{
			if (!isset($GLOBALS["properties"][$cur_clid]))
			{
				$classes = aw_ini_get("classes");
				list($GLOBALS["properties"][$cur_clid], $GLOBALS["tableinfo"][$cur_clid], $GLOBALS["relinfo"][$cur_clid]) = $GLOBALS["object_loader"]->load_properties(array(
					"file" => ($cur_clid == CL_DOCUMENT ? "doc" : basename($classes[$cur_clid]["file"])),
					"clid" => $cur_clid
				));
			}

			$set_clid = false;
			if (($_pos = strpos($pp, "(")) !== false)
			{
				$set_clid = constant(substr($pp, $_pos+1, -1));
				$pp = substr($pp, 0, $_pos);
			}

			if ($pp == "id")
			{
				$cur_prop = array("name" => "id", "table" => "objects", "field" => "oid");
			}
			else
			if ($pp == "class_id")
			{
				$cur_prop = array("name" => "id", "table" => "objects", "field" => "class_id");
			}
			else
			{
				$cur_prop = $GLOBALS["properties"][$cur_clid][$pp];
			}

			error::raise_if(!is_array($cur_prop), array(
				"id" => ERR_OBJ_NO_PROP,
				"msg" => sprintf(t("ds_mysql::_req_do_pcp(): no property %s in class %s "), $pp, $cur_clid)
			));


			$table = $cur_prop["table"];
			$field = $cur_prop["field"];

			// if it is the last one, then it can be anything
			if ($pos < (count($filt) - 1))
			{
				/*error::raise_if(!$set_clid && $cur_prop["type"] != "relpicker" && $cur_prop["type"] != "relmanager" && $cur_prop["type"] != "classificator", array(
					"id" => ERR_OBJ_NO_RP,
					"msg" => t("ds_mysql::_req_do_pcp(): currently join properties can only be of type relpicker - can't figure out the class id of the object-to-join otherwise")
				));*/
	
				error::raise_if($cur_prop["method"] == "serialize", array(
					"id" => ERR_OBJ_NO_META,
					"msg" => sprintf(t("ds_mysql::_req_do_pcp(): can not join classes on serialized fields (property %s in class %s)"), $pp, $cur_clid)
				));

				if ($set_clid)
				{
					$new_clid = $set_clid;
					error::raise_if(!$set_clid, array(
						"id" => ERR_OBJ_W_TP,
						"msg" => sprintf(t("ds_mysql::_req_do_pcp(): incorrect prop type! (%s)"), $cur_prop["type"])
					));
				}
				else
				{
					switch ($cur_prop["type"])
					{
						case "relpicker":
						case "relmanager":
						case "classificator":
						case "popup_search":
						case "crm_participant_search":
							$new_clid = false;

							$relt_s = $cur_prop["reltype"];
							$relt = $GLOBALS["relinfo"][$cur_clid][$relt_s]["value"];

							if (!$relt)
							{
								$new_clid = @constant($cur_prop["clid"]);
							}				

							error::raise_if(!$relt && !$new_clid, array(
								"id" => ERR_OBJ_NO_REL,
								"msg" => sprintf(t("ds_mysql::_req_do_pcp(): no reltype %s in class %s , got reltype from relpicker property %s"), $relt_s, $cur_clid, $cur_prop["name"])
							));
		
							if (!$new_clid)
							{
								$new_clid = $GLOBALS["relinfo"][$cur_clid][$relt_s]["clid"][0];
							}
							break;
		
						default:
							$new_clid = $set_clid;
							error::raise_if(!$set_clid, array(
								"id" => ERR_OBJ_W_TP,
								"msg" => sprintf(t("ds_mysql::_req_do_pcp(): incorrect prop type! (%s)"), $cur_prop["type"])
							));
					}
				}
			}

			$jd = array(
				"via" => "prop",
				"prop" => $pp,
				"from_class" => $cur_clid,
				"to_class" => $new_clid,
				"table" => $table,
				"field" => $field
			);
			$this->join_data[] = $jd;
		}

		if ($pos < (count($filt)-1))
		{
			$this->_req_do_pcp($filt, $pos+1, $new_clid, $arr);
		}
	}

	function _get_joins($params)
	{
		// check if join strategy is present in args and do joins based on that
		if (!empty($params["join_strategy"]))
		{
			$join_strategy = $params["join_strategy"];
		}
		else
		{
			$join_strategy = "obj";
		}

		if ($join_strategy == "obj" || !$this->has_data_table_filter)
		{
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
			return "objects ".join("", $js).join(" ", $this->joins);
		}
		else
		if ($join_strategy == "data")
		{
			// make joins
			$js = array();
			$first_table = NULL;
			foreach($this->used_tables as $tbl)
			{
				if ($tbl == "objects")
				{
					continue;
				}

				if (count($js) == 0)
				{
					// first table
					$js[] = $tbl;
					$first_table = $tbl;
				}
				else
				{
					// other tables
					$js[] = " LEFT JOIN $tbl ON $tbl.".$this->tableinfo[$tbl]["index"]." = objects.brother_of ";
				}
			}

			if ($first_table !== NULL)
			{
				$js[] = " LEFT JOIN objects ON objects.oid = $first_table.".$this->tableinfo[$first_table]["index"];
			}
			else
			{
				$js[] = " objects ";
			}

			foreach($this->alias_joins as $aj)
			{
				$js[] = " LEFT JOIN aliases $aj[name] ON $aj[on] ";
			}
			return join("", $js).join(" ", $this->joins);
		}
	}

	function fetch_list($to_fetch)
	{
		$this->used_tables = array();

		$this->properties = array();
		$this->tableinfo = array();

		// make list of uniq class_id's
		$clids = array();
		$cl2obj = array();	
		foreach($to_fetch as $oid => $clid)
		{
			$clids[$clid] = $clid;
			$cl2obj[$clid][$oid] = $oid;
		}

		// read props
		$this->_do_add_class_id($clids);

		$ret = array();
		$ret2 = array();

		// do joins on the data objects for those
		$joins = array();
		foreach($clids as $clid)
		{
			if (!($sql = aw_cache_get("storage::get_read_properties_sql",$clid)))
			{
				$sql = $this->get_read_properties_sql(array(
					"properties" => $GLOBALS["properties"][$clid],
					"tableinfo" => $GLOBALS["tableinfo"][$clid],
					"class_id" => $clid,
					"object_id" => $cl2obj[$clid],
					"full" => true
				));
				aw_cache_set("storage::get_read_properties_sql",$clid,$sql);
				//echo "sql = ".dbg::dump($sql)." <br>";
			}
			if ($sql["q"] == "")
			{
				// just ot fetch
				//$sql["q"] = "SELECT * FROM objects WHERE status > 0 AND oid ";
			}

			if ($sql["q"] != "")
			{
				$sql["q"] .= " IN (".join(",", $cl2obj[$clid]).")";
			}
			if ($sql["q"] != "")
			{
				// query
				$this->db_query($sql["q"]);
				while ($row = $this->db_next())
				{
					$this->read_properties_data_cache[$row["oid"]] = $row;
					$ret[] = $row;
				}
			}
			if ($sql["q2"] != "")
			{
				$this->db_query($sql["q2"]);
				while ($row = $this->db_next())
				{
					$this->read_properties_data_cache_conn[$row["source"]][] = $row;
					$ret2[] = $row;
				}
			}			
		}

		return $ret;
	}

	/*function quote(&$str)
	{
		$str = str_replace("'", "\\'", str_replace("\\", "\\\\", $str));
	}*/

	function _get_search_fetch($to_fetch)
	{
		if (!is_array($to_fetch))
		{
			return array();
		}
		$ret = array();
		$serialized_fields = array();
		foreach($to_fetch as $clid => $props)
		{
			$p = $GLOBALS["properties"][$clid];
			$this->_do_add_class_id($clid, true);

			foreach($props as $pn => $resn)
			{
				if (substr($pn, 0, 5) == "meta.")
				{
					$serialized_fields["objects.metadata"][] = substr($pn, 5);
				}
				else
				if (!isset($p[$pn]))
				{
					// assume obj table
					$ret[$pn] = " objects.$pn AS $resn ";
				}
				else
				if ($p[$pn]["method"] == "serialize")
				{
					if ($p[$pn]["table"] == "objects" && $p[$pn]["field"] == "meta")
					{
						$serialized_fields["objects.metadata"][] = $pn;
					}
					else
					{
						$serialized_fields[$p[$pn]["table"].".`".$p[$pn]["field"]."`"][] = substr($pn, 5);
					}
				}
				else
				{
					$ret[$pn] = " ".$p[$pn]["table"].".`".$p[$pn]["field"]."` AS $resn ";
				}
			}
		}

		$sf = array();
		foreach($serialized_fields as $fld => $stuff)
		{
			$fldn = str_replace(".", "_", $fld);
			$ret[] = $fld." AS ".$fldn." ";
			$sf[$fldn] = $stuff;
		}

		$res =  join(",", $ret);
		return array($res, array_keys($ret), $sf);
	}

	function save_properties_new_version($arr)
	{
		extract($arr);

		$metadata = aw_serialize($objdata["meta"]);
		$this->quote(&$metadata);
		$this->quote(&$objdata);
		$objdata["metadata"] = $metadata;

		if ($objdata["brother_of"] == 0)
		{
			$objdata["brother_of"] = $objdata["oid"];
		}

		if (!$arr["objdata"]["version_id"])
		{
			// insert new record & get id
			$version_id = gen_uniq_id();
			$this->db_query("INSERT INTO documents_versions (version_id, docid, vers_crea, vers_crea_by) values('$version_id', $objdata[oid], ".time().", '".aw_global_get("uid")."')");
		}
		else
		{
			$version_id = $arr["objdata"]["version_id"];
			$this->db_query("UPDATE documents_versions SET vers_crea = ".time().", vers_crea_by = '".aw_global_get("uid")."' WHERE docid = $objdata[oid] AND version_id = '$version_id'");
		}

		$ot_sets = array();
		$arr["ot_modified"] = $GLOBALS["object_loader"]->all_ot_flds;
		foreach(safe_array($arr["ot_modified"]) as $_field => $one)
		{
			$ot_sets[] = " o_".$_field." = '".$objdata[$_field]."' ";
		}

		$ot_sets = join(" , ", $ot_sets);

		$q = "UPDATE documents_versions SET
			$ot_sets
			WHERE version_id = '".$version_id."'
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
			if ($tbl == "")
			{
				continue;
			}

			if ($tbl == "objects")
			{
				continue;
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

					if ($prop["datatype"] == "int" && $seta[$prop["field"]] == "")
					{
						$seta[$prop["field"]] = "0";
					}
				}
			}

			foreach($serfs as $field => $dat)
			{
				$str = aw_serialize($dat);
				$this->quote($str);
				$seta[$field] = $str;
			}
			$sets = join(",",map2("`%s` = '%s'",$seta,0,true));
			if ($sets != "")
			{
				$tbl .= "_versions";
				$q = "UPDATE $tbl SET $sets WHERE version_id = '".$version_id."'";
//echo "q = $q <br>";
				$this->db_query($q);
			}
		}

		unset($GLOBALS["__obj_sys_objd_memc"][$objdata["brother_of"]]);
		unset($GLOBALS["__obj_sys_objd_memc"][$objdata["oid"]]);

		unset($this->read_properties_data_cache[$objdata["oid"]]);
		unset($this->read_properties_data_cache[$objdata["brother_of"]]);

		$this->cache->file_clear_pt("html");
	}

	function load_version_properties($arr)
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
				$ret[$prop["name"]] = isset($objdata[$prop['field']]) && isset($objdata[$prop["field"]][$prop["name"]]) ? $objdata[$prop["field"]][$prop["name"]] : "";
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

			if (isset($prop["datatype"]) && $prop["datatype"] == "int" && $ret[$prop["name"]] == "")
			{
				$ret[$prop["name"]] = "0";
			}
		}

		// fix old broken databases where brother_of may be 0 for non-brother objects
		$object_id = ($objdata["brother_of"] ? $objdata["brother_of"] : $objdata["oid"]);

		$conn_prop_vals = array();
		$conn_prop_fetch = array();

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
						$fields[] = $table."_versions.`".$prop["field"]."` AS `".$prop["field"]."`";
						$_got_fields[$prop["field"]] = true;
					}
				}
				else
				if ($prop["store"] == "connect")
				{
					if ($GLOBALS["cfg"]["__default"]["site_id"] != 139)
					{
						$_co_reltype = $prop["reltype"];
						$_co_reltype = $GLOBALS["relinfo"][$objdata["class_id"]][$_co_reltype]["value"];

						if ($_co_reltype == "")
						{
							error::raise(array(
								"id" => "ERR_NO_RT",
								"msg" => sprintf(t("ds_mysql::read_properties(): no reltype for prop %s (%s)"), $prop["name"], $prop["reltype"])
							));
						}

						$conn_prop_fetch[$prop["name"]] = $_co_reltype;
					}
				}
				else
				{
					$fields[] = $table."_versions.`".$prop["field"]."` AS `".$prop["name"]."`";
				}
			}

			if (count($fields) > 0)
			{
				$q = "SELECT ".join(",", $fields)." FROM ".$table."_versions WHERE `version_id` = '".$arr["objdata"]["load_version"]."'";
				
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

					if (isset($prop["datatype"]) && $prop["datatype"] == "int" && $ret[$prop["name"]] == "")
					{
						$ret[$prop["name"]] = "0";
					}
				}
			}
		}


		if (count($conn_prop_fetch))
		{
			$cpf_dat = array();
			if (isset($this->read_properties_data_cache_conn[$object_id]))
			{
				$cfp_dat = $this->read_properties_data_cache_conn[$object_id];
			}
			else
			{
				$q = "
					SELECT 
						target,
						reltype
					FROM 
						aliases 
					LEFT JOIN objects ON objects.oid = aliases.target
					WHERE 
						source = '".$object_id."' AND 
						reltype IN (".join(",", map("'%s'", $conn_prop_fetch)).") AND 
						objects.status != 0
				";
				$this->db_query($q);
				while ($row = $this->db_next())
				{
					$cfp_dat[] = $row;
				}
			}

			foreach($cfp_dat as $row)
			{
				$prop_name = array_search($row["reltype"], $conn_prop_fetch);
				if (!$prop_name)
				{
					error::raise(array(
						"id" => "ERR_NO_PROP",
						"msg" => sprintf(t("ds_mysql::read_properties(): no prop name for reltype %s in store=connect fetch! q = %s"), $row["reltype"], $q)
					));
				}

				$prop = $properties[$prop_name];
				if ($prop["multiple"] == 1)
				{
					$ret[$prop_name][$row["target"]] = $row["target"];
				}
				else
				{
					if (!isset($ret[$prop_name])) // just the first one
					{
						$ret[$prop_name] = $row["target"];
					}
				}
			}
		}
		return $ret;
	}
}

?>
