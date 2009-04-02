<?php
/*
@classinfo  maintainer=kristo
*/

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

		$this->quote(&$alias);
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
		$this->save_handle();
		$oid = (int)$oid;
		if (!empty($GLOBALS["object2version"][$oid]) && $GLOBALS["object2version"][$oid] != "_act")
		{
			$v = $GLOBALS["object2version"][$oid];
			$ret = $this->db_fetch_row("SELECT * FROM objects WHERE oid = '$oid' AND status != 0");
			$ret2 = $this->db_fetch_row("SELECT o_alias, o_jrk, o_metadata FROM documents_versions WHERE docid = '$oid' AND version_id = '$v'");
			$ret["alias"] = $ret2["o_alias"];
			$ret["jrk"] = $ret2["o_jrk"];
			$ret["metadata"] = $ret2["o_metadata"];
			$rv =  $this->_get_objdata_proc($ret, $param, $oid);
			$this->restore_handle();
			return $rv;
		}

		if (isset($this->read_properties_data_cache[$oid]))
		{
			$td = $this->read_properties_data_cache[$oid];
			if (isset($GLOBALS["read_properties_data_cache_conn"][$oid]))
			{
				$ps = $GLOBALS["properties"][$td["class_id"]];
				$rts = $GLOBALS["relinfo"][$td["class_id"]];

				$cfp_dat = array();
				foreach($GLOBALS["read_properties_data_cache_conn"][$oid] as $_tmp => $d)
				{
					foreach($d as $_tmp2 => $d2)
					{
						// find prop from reltype
						// add to that
						$pn = false;
						foreach($ps as $pid => $pd)
						{
							if ($pd["store"] == "connect" && $rts[$pd["reltype"]]["value"] == $d2["reltype"])
							{
								$v = $d2["target"];
								if (!empty($pd["multiple"]))
								{
									$td[$pid][] = $v;
								}
								else
								{
									$td[$pid] = $v;
								}
								continue;
							}
						}
					}
				}
			}
			$ret = $this->_get_objdata_proc($td, $param, $oid);
		}
		else
		{
			$ret = $this->db_fetch_row("SELECT * FROM objects WHERE oid = '$oid' AND status != 0");
			if ($ret["oid"] != $ret["brother_of"])
			{
				$ret["metadata"] = $this->db_fetch_field("SELECT metadata FROM objects WHERE oid = '".$ret["brother_of"]."'", "metadata");
			}
			$ret = $this->_get_objdata_proc($ret, $param, $oid);
		}
		$this->restore_handle();
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
		//unset($ret["metadata"]);

		if ($ret["brother_of"] == 0)
		{
			$ret["brother_of"] = $ret["oid"];
		}

		// unserialize acldata
		$ret["acldata"] = aw_unserialize(isset($ret["acldata"]) ? $ret["acldata"] : null);

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
		$datagrids = array();
		foreach($properties as $prop => $data)
		{
			if (isset($data["store"]) && $data["store"] === "no")
			{
				continue;
			}

			if (empty($data["table"]))
			{
				$data["table"] = "objects";
			}

			if ($data["type"] === "datagrid")
			{
				$datagrids[$prop] = $data;
			}
			elseif ($data["table"] !== "objects")
			{
				$tables[$data["table"]] = $data["table"];
				$tbl2prop[$data["table"]][] = $data;
			}
			else
			{
				$objtblprops[] = $data;
			}
		}
		$conn_prop_vals = array();
		$conn_prop_fetch = array();

		// import object table properties in the props array
		foreach($objtblprops as $prop)
		{
			if ($prop["store"] === "connect")
			{
				if ($GLOBALS["cfg"]["site_id"] != 139)
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
			elseif ($prop["method"] === "serialize")
			{
				// metadata is unserialized in read_objprops
				$ret[$prop["name"]] = isset($objdata[$prop['field']]) && isset($objdata[$prop["field"]][$prop["name"]]) ? $objdata[$prop["field"]][$prop["name"]] : "";
			}
			elseif ($prop["method"] === "bitmask")
			{
				$ret[$prop["name"]] = ((int)$objdata[$prop["field"]]) & ((int)$prop["ch_value"]);
			}
			else
			{
				$ret[$prop["name"]] = isset($objdata[$prop["field"]]) ? $objdata[$prop["field"]] : null;
			}

			if (isset($prop["datatype"]) && $prop["datatype"] === "int" && $ret[$prop["name"]] == "")
			{
				$ret[$prop["name"]] = "0";
			}
		}

		// fix old broken databases where brother_of may be 0 for non-brother objects
		$object_id = ($objdata["brother_of"] ? $objdata["brother_of"] : $objdata["oid"]);

		foreach($datagrids as $g_pn => $g_prop)
		{
			// fetch all rows from the table
			$q = "SELECT * FROM ".$g_prop["table"]." WHERE ".$tableinfo[$g_prop["table"]]["index"]." = ".$object_id." ORDER BY ".$g_prop["field"];
			$this->db_query($q);
			$val = array();
			while ($row = $this->db_next())
			{
				$val[$row[$g_prop["field"]]] = $row;
			}
			$ret[$g_pn] = $val;
		}

		// do a query for each table
		foreach($tables as $table)
		{
			$fields = array();
			$_got_fields = array();
			foreach($tbl2prop[$table] as $prop)
			{
				if ($prop['field'] === "meta" && $prop["table"] === "objects")
				{
					$prop['field'] = "metadata";
				}

				if ($prop["method"] === "serialize" && $prop["store"] !== "connect")
				{
					if (!array_key_exists($prop["field"], $_got_fields))
					{
						$fields[] = $table.".`".$prop["field"]."` AS `".$prop["field"]."`";
						$_got_fields[$prop["field"]] = true;
					}
				}
				else
				if ($prop["store"] === "connect")
				{
					if ($GLOBALS["cfg"]["site_id"] != 139)
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
				if ($prop['type'] === 'range')
				{
					$fields[] = $table.".`".$prop["field"]."_from` AS `".$prop["name"]."_from`";
					$fields[] = $table.".`".$prop["field"]."_to` AS `".$prop["name"]."_to`";
				}

				else
				{
					$fields[] = $table.".`".$prop["field"]."` AS `".$prop["name"]."`";
				}
			}

			if (count($fields) > 0)
			{
				$q = "SELECT ".join(",", $fields)." FROM {$table} WHERE `".$tableinfo[$table]["index"]."` = '".$object_id."'";
				if (isset($this->read_properties_data_cache[$object_id]))
				{
					$data = $this->read_properties_data_cache[$object_id];
					$this->dequote(&$data);
				}
				else
				{
					$data = $this->db_fetch_row($q);
					$this->dequote(&$data);
				}
				if (is_array($data))
				{
					$ret += $data;
				}
				foreach($tbl2prop[$table] as $prop)
				{
					if ($prop["method"] === "serialize")
					{
						if ($prop['field'] === "meta" && $prop["table"] === "objects")
						{
							$prop['field'] = "metadata";
						}

						$unser = aw_unserialize($ret[$prop["field"]]);
						$ret[$prop["name"]] = isset($unser[$prop["name"]]) ? $unser[$prop["name"]] : null;
					}

					if (isset($prop["datatype"]) && $prop["datatype"] === "int" && $ret[$prop["name"]] == "")
					{
						$ret[$prop["name"]] = "0";
					}

					if ($prop["type"] === "range")
					{
						$ret[$prop['name']] = array(
							'from' => $ret[$prop['name'].'_from'],
							'to' => $ret[$prop['name'].'_to']
						);
						unset($ret[$prop['name'].'_from'], $ret[$prop['name'].'_to']);

					}
				}
			}
		}

		if (count($conn_prop_fetch))
		{
			$cfp_dat = array();
			if (isset($GLOBALS["read_properties_data_cache_conn"][$object_id]))
			{
				$cfp_dat = array(); //$GLOBALS["read_properties_data_cache_conn"][$object_id];
				foreach($GLOBALS["read_properties_data_cache_conn"][$object_id] as $_tmp => $d)
				{
					foreach($d as $_tmp2 => $d2)
					{
						$cfp_dat[] = $d2;
					}
				}
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
				if (!empty($prop["multiple"]))
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
		$conn_prop_fetch = array();
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

			if ($data["store"] == "connect")
			{
				if ($GLOBALS["cfg"]["site_id"] != 139)
				{
					// resolve reltype and do find_connections
					$_co_reltype = $data["reltype"];
					$_co_reltype = $GLOBALS["relinfo"][$class_id][$_co_reltype]["value"];
					$conn_prop_fetch[$data["name"]] = $_co_reltype;
				}
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

		$fields = array();
		// do a query for each table
		foreach($tables as $table)
		{
			$_got_fields = array();
			foreach($tbl2prop[$table] as $prop)
			{
				if ($prop['field'] == "meta" && $prop["table"] == "objects")
				{
					$prop['field'] = "metadata";
				}

				if ($prop["method"] == "serialize")
				{
					if (!array_key_exists($prop["field"], $_got_fields))
					{
						$fields[] = $table.".`".$prop["field"]."` AS `".$prop["field"]."`";
						$_got_fields[$prop["field"]] = true;
					}
				}
				else
				if ($prop['type'] == 'range') // range support by dragut
				{
					$fields[] = $table.".`".$prop["field"]."_from` AS `".$prop["name"]."_from`";
					$fields[] = $table.".`".$prop["field"]."_to` AS `".$prop["name"]."_to`";
				}
				else
				if ($prop["method"] == "bitmask")
				{
					$fields[] = " (".$table.".`".$prop["field"]."` & ".$prop["ch_value"].") AS `".$prop["name"]."`";
				}
				else
				if ($prop["store"] != "connect")	// must not try to read store=connect fields at all, since they don't have to exist!
				{
					$fields[] = $table.".`".$prop["field"]."` AS `".$prop["name"]."`";
				}
			}
/*			if ($q != "")
			{
				return array();
			}*/
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
				objects.metadata as metadata,
				objects.subclass as subclass,
				objects.flags as flags";
			if (aw_ini_get("acl.use_new_acl") == 1)
			{
				$q .= ",objects.acldata as acldata";
			}
			if (count($objtblprops))
			{
				foreach($objtblprops as $objtblprop)
				{
	                                if ($objtblprop["method"] == "bitmask")
	                                {
	                                        $q .= ",\n(objects.`".$objtblprop["field"]."` & ".$objtblprop["ch_value"].") AS `".$objtblprop["name"]."`";
	                                }
				}
			}
			if (count($fields) > 0)
			{
				$joins = "";
				foreach($tables as $table)
				{
					$joins .= " LEFT JOIN $table ON objects.brother_of = ".$table.".`".$tableinfo[$table]["index"]."` ";
				}
				$q .= ",".join(",", $fields)." FROM objects $joins  WHERE ";
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
			$table = reset($tables);
			$from = " FROM $table ";
			$o_t = $table;
			while($table = each($tables))
			{
				$from .= " LEFT JOIN $table ON ".$o_t.".`".$tableinfo[$o_t]["index"]."` = ".$table.".`".$tableinfo[$table]["index"]."` ";
			}
			$q = "SELECT ".join(",", $fields)." $from WHERE `".$tableinfo[$table]["index"]."`";
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

		$q2 = null;
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
					reltype,
					objects.name as target_name
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

		$this->quote(&$metadata);
		$this->quote(&$objdata);
		// insert default new acl to object table here
		$acld_fld = $acld_val = "";
		$n_acl_data = null;
		if (aw_ini_get("acl.use_new_acl") && $_SESSION["uid"] != "" && is_oid(aw_global_get("uid_oid")))
		{
			$uo = obj(aw_global_get("uid_oid"));
			$g_d = $uo->get_default_group();

			$n_acl_data = array(
				$g_d => $this->get_acl_value_n($this->acl_get_default_acl_arr())
			);

			$acld_fld = ",acldata";
			$acld_val = ",'".str_replace("'", "\\'", aw_serialize($n_acl_data))."'";
		}

		// create oid
		$q = "
			INSERT INTO objects (
				parent,						class_id,						name,						createdby,
				created,					modified,						status,						site_id,
				hits,						lang_id,						comment,					modifiedby,
				jrk,						period,							alias,						periodic,
				metadata,						subclass,					flags
				$acld_fld
		) VALUES (
				'".$objdata["parent"]."',	'".$objdata["class_id"]."',		'".$objdata["name"]."',		'".$objdata["createdby"]."',
				'".$objdata["created"]."',	'".$objdata["modified"]."',		'".$objdata["status"]."',	'".$objdata["site_id"]."',
				'".$objdata["hits"]."',		'".$objdata["lang_id"]."',		'".$objdata["comment"]."',	'".$objdata["modifiedby"]."',
				'".$objdata["jrk"]."',		'".$objdata["period"]."',		'".$objdata["alias"]."',	'".$objdata["periodic"]."',
										'".$metadata."',				'".$objdata["subclass"]."',	'".$objdata["flags"]."'
				$acld_val
		)";
		//echo "q = <pre>". htmlentities($q)."</pre> <br />";

		$this->db_query($q);
		$oid = $this->db_last_insert_id();

		if (!aw_ini_get("acl.use_new_acl_final"))
		{
			// create all access for the creator
			$this->create_obj_access($oid);
		}
		// set brother to self if not specified.
		if (!$objdata["brother_of"])
		{
			$this->db_query("UPDATE objects SET brother_of = oid WHERE oid = $oid");
		}

		// put into cache to avoid query for the same object's data in the can() a few lines down
		$tmp = $objdata;
		$tmp["acldata"] = $n_acl_data;
		$GLOBALS["__obj_sys_objd_memc"][$oid] = $tmp;
		$this->can("admin", $oid);

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
					if ($data["method"] != "serialize")
					{
						$tbls[$data["table"]]["defaults"][$data["field"]] = $data["default"];
					}
					else
					{
						$_field_val = aw_unserialize($tbls[$data["table"]]["defaults"][$data["field"]]);
						$_field_val[$prop] = $data["default"];
						$tbls[$data["table"]]["defaults"][$data["field"]] = aw_serialize($_field_val);
					}
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

		$this->create_new_object_cache_update(null);

		return $oid;
	}

	function create_new_object_cache_update($oid)
	{
		// we need to clear the html cache here, not in ds_cache, because ds_cache can be not loaded
		// even when html caching is turned on

		if (!obj_get_opt("no_cache"))
		{
			$this->cache->file_clear_pt("html");
		}
	}

	// saves object properties, including all object table fields,
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

		if ($ot_sets != "")
		{
			$ot_sets = " , ".$ot_sets;
		}

		$obj_q = "UPDATE objects SET
			mod_cnt = IFNULL(mod_cnt, 0) + 1
			$ot_sets
			WHERE oid = '".$objdata["oid"]."'
		";

//		echo "q = <pre>". htmlentities($q)."</pre> <br />";
//		$this->db_query($q);

		// now save all properties


		$data_qs = array();
		$used_tables = array("objects" => "objects");

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
					if (!empty($arr["props_modified"][$prop["name"]]) || isset($mod_flds[$prop["table"]][$prop["field"]]))
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
				if ($objdata["oid"] == $objdata["brother_of"])
				{
					continue; // no double save. but if this is brother, then meta need to be saved
				}
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
					if ($prop["type"] == "datagrid")
					{
						continue;
					}
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
					if ($prop['type'] == 'range') // range support by dragut
					{
						$seta[$prop['field'].'_from'] = (int)$propvalues[$prop['name']]['from'];
						$seta[$prop['field'].'_to'] = (int)$propvalues[$prop['name']]['to'];
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

			// actually, this is a bit mopre complicated here - if this is a brother
			// and the table is the objects table, then we must ONLY write the metadata field
			// to the original object. because if we write all, then ot fields will be the same for the brother and the original
			// always. and that's not good.
			if ($tbl == "objects" && $objdata["brother_of"] != $objdata["oid"])
			{
				$seta = array("metadata" => $seta["metadata"]);
			}
			$sets = join(",",map2("`%s` = '%s'",$seta,0,true));
			if ($sets != "")
			{
				$q = "UPDATE $tbl SET $sets WHERE ".$tableinfo[$tbl]["index"]." = '".$objdata["brother_of"]."'";
				$used_tables[$tbl] = $tbl;
				$data_qs[] = $q;
			}
		}

		// make datagrid inserts
		foreach($tbls as $tbl => $tbld)
		{
			if ($tbl == "")
			{
				continue;
			}

			foreach($tbld as $prop)
			{
				if ($prop["type"] == "datagrid")
				{
					$data_qs[] = "DELETE FROM ".$prop["table"]." WHERE ".$tableinfo[$prop["table"]]["index"]." = ".$objdata["oid"];
					// insert data back
					$data = $propvalues[$prop["name"]];
					if (is_array($data))
					{
						foreach($data as $idx => $data_row)
						{
							$tmp = array();
							foreach($prop["fields"] as $field_name)
							{
								$this->quote(&$data_row[$field_name]);
								$tmp[$field_name] = $data_row[$field_name];
							}

							if ($idx < 1)
							{
								$data_qs[] = "INSERT INTO ".$prop["table"]."(".$tableinfo[$prop["table"]]["index"].",".join(",", map('`%s%``', $prop["fields"])).") VALUES(".$objdata["oid"].",".join(",", map("'%s'", $tmp)).") ";
							}
							else
							{
								$data_qs[] = "INSERT INTO ".$prop["table"]."(".$prop["field"].",".$tableinfo[$prop["table"]]["index"].",".join(",", map('`%s%``', $prop["fields"])).") VALUES(".$idx.",".$objdata["oid"].",".join(",", map("'%s'", $tmp)).") ";
							}
						}
					}
				}
			}
		}

//echo (dbg::dump($data_qs));
		// check exclusivity
		if ($arr["exclusive_save"])
		{
			// lock tables and check mod count
			$this->db_query("LOCK TABLES ".join(" , ", map(" %s WRITE ", $used_tables)));
			$db_mod_cnt = $this->db_fetch_field("SELECT mod_cnt FROM objects WHERE oid = '".$objdata["oid"]."'", "mod_cnt");
			if ($db_mod_cnt != $arr["current_mod_count"])
			{
				// unlock tables and except
				$this->db_query("UNLOCK TABLES");
				throw new awex_obj_modified_by_others(sprintf(t("Mod count difference, new %s , old %s!"), $db_mod_cnt, $arr["current_mod_count"]));
				return;
			}
			// not modified, go save
		}

//echo "obj_q = $obj_q <br>";
		$this->db_query($obj_q);
//die("meh");
		foreach($data_qs as $q)
		{
			$this->db_query($q);
		}

		if ($arr["exclusive_save"])
		{
			// un lock tables
			$this->db_query("UNLOCK TABLES");
		}

		unset($GLOBALS["__obj_sys_objd_memc"][$objdata["brother_of"]]);
		unset($GLOBALS["__obj_sys_objd_memc"][$objdata["oid"]]);

		unset($this->read_properties_data_cache[$objdata["oid"]]);
		unset($this->read_properties_data_cache[$objdata["brother_of"]]);
		$this->save_properties_cache_update($objdata["oid"]);
	}

	function save_properties_cache_update($oid)
	{
		if (!obj_get_opt("no_cache"))
		{
			$this->cache->file_clear_pt("html");
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
			if (isset($GLOBALS["objects"][$data["to"]]))
			{
				$data["type"] = $GLOBALS["objects"][$data["to"]]->class_id();
			}
			else
			{
				$data["type"] = $this->db_fetch_field("SELECT class_id FROM objects WHERE oid = '".$data["to"]."'", "class_id");
			}
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
			// we don't need the index if the connection has a reltype, cause the index is only used for aliases
			if (!$data["idx"] && !$data["reltype"])
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
		$this->save_connection_cache_update(null);

		return $data['id'];
	}

	function save_connection_cache_update($oid)
	{
		if (!obj_get_opt("no_cache"))
		{
			$this->cache->file_clear_pt("html");
		}
	}

	function delete_connection($id)
	{
		$this->db_query("DELETE FROM aliases WHERE id = '$id'");
		$this->delete_connection_cache_update($id);
	}

	function delete_connection_cache_update($oid)
	{
		if (!obj_get_opt("no_cache"))
		{
			$this->cache->file_clear_pt("html");
		}
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
				o_s.comment as `from.comment`,
				o_t.acldata as `to.acldata`,
				o_s.acldata as `from.acldata`
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

		if (!empty($arr["from"]))
		{
			$awa = new aw_array($arr["from"]);
			$sql .= " AND source IN (".$awa->to_sql().") ";
		}

		if (!empty($arr["to"]))
		{
			$awa = new aw_array($arr["to"]);
			$sql .= " AND target IN (".$awa->to_sql().") ";
		}

		if (!empty($arr["type"]))
		{
			$awa = new aw_array($arr["type"]);
			$sql .= " AND reltype IN (".$awa->to_sql().") ";
		}

		if (!empty($arr["class"]))
		{
			$awa = new aw_array($arr["class"]);
			$sql .= " AND type IN (".$awa->to_sql().") ";
		}

		if (!empty($arr["relobj_id"]))
		{
			$awa = new aw_array($arr["relobj_id"]);
			$sql .= " AND relobj_id IN (".$awa->to_sql().") ";
		}

		if (!empty($arr["idx"]))
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

	//	$sql .= " ORDER BY a.id ";

		$this->db_query($sql);
		$ret = array();
		while ($row = $this->db_next())
		{
			$row["from.acldata"] = aw_unserialize($row["from.acldata"]);
			$row["to.acldata"] = aw_unserialize($row["to.acldata"]);
			$ret[$row["id"]] = $row;
		}

		ksort($ret);
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
		$this->class_id = null;
		if (isset($params["class_id"]))
		{
			$this->_do_add_class_id($params["class_id"]);
			$this->class_id = $params["class_id"];
			if (is_array($this->class_id))
			{
				$this->class_id = reset($this->class_id);
			}
		}

		$this->stat = false;
		$this->sby = "";
		$this->limit = "";
		$this->has_lang_id = false;

		$this->meta_filter = array();
		$this->alias_joins = array();
		$this->done_ot_js = array();
		$this->joins = array();

		// this contains the full names of all the tables used in any part of the sql ( fetch, join, where) so that we ca use this to leave out unused tables from the resulting query.
		// this could of course be done during query construction, but it is much much harder to do it there, so we do it as a post-process step.
		$this->search_tables_used = array("objects" => 1);

		$this->has_data_table_filter = false;
		list($fetch_sql, $fetch_props, $fetch_metafields, $has_sql_func, $multi_fetch_fields) = $this->_get_search_fetch($to_fetch, $params);

		// set fetch sql as member, so that req_make_sql can rewrite
		// in it fetch columns that are not known yet from data fetch, that get search fetch puts in it
		$this->current_fetch_sql = &$fetch_sql;
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

		// now, optimize out the joins that are not needed
		$joins = $this->_optimize_joins($joins, $this->search_tables_used);

		$ret = array();
		if ($where != "")
		{
			$acld = "";
			if ($GLOBALS["cfg"]["acl"]["use_new_acl"])
			{
				$acld = ", objects.acldata as acldata, objects.parent as parent";
			}

			$datafetch = true;
			if ($fetch_sql == "")
			{
				$fetch_sql = "
					objects.oid as oid,
					objects.jrk as jrk,
					objects.name as name,
					objects.parent as parent,
					objects.brother_of as brother_of,
					objects.status as status,
					objects.class_id as class_id
					$acld
				";
				$datafetch = false;
			}

			$gpb = "";
			if ($this->limit != "")
			{
				// this is here for a quite complicated, but unfortunately, quite necessary reason:
				// if you do a search that searches through several relations and several of them match
				// then you would get several rows for each object
				// and thus you would get less than the limit amount of objects
				// which, in the sql sense is quite falid, since joins are cross products on data sets
				// but in the gimme-a-list-of-objects-with-those-props is not
				// so we solve this by making sure we only get separate objects in the result set.
				// we do this by adding the group by clause here.
				// it slows things by quite a bit, but unfortunately, it is the only way to avoid this.
				$gpb = "GROUP BY objects.oid";
			}

			$q = "
				SELECT
					$fetch_sql
				FROM
					$joins
				WHERE
					$where $gpb ".$this->sby."  ".$this->limit;

			$acldata = array();
			$parentdata = array();
			$objdata = array();

			$this->db_query($q);

			if ($datafetch)
			{
				$ret2 = array();
				while($row = $this->db_next())
				{
					if (!$has_sql_func && count($multi_fetch_fields) && isset($ret2[$row["oid"]]))
					{
						// add the multi field values as arrays
						foreach($multi_fetch_fields as $field)
						{
							if (!is_array($ret2[$row["oid"]][$field]))
							{
								$ret2[$row["oid"]][$field] = array($ret2[$row["oid"]][$field] => $ret2[$row["oid"]][$field]);
							}
							$ret2[$row["oid"]][$field][$row[$field]] = $row[$field];
						}
						continue;
					}

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

					if ($has_sql_func)
					{
						$ret2[] = $row;
					}
					else
					{
						foreach($multi_fetch_fields as $field)
						{
							if (!is_array($row[$field]))
							{
								if (empty($row[$field]))
								{
									$row[$field] = array();
								}
								else
								{
									$row[$field] = array($row[$field] => $row[$field]);
								}
							}
						}
						$ret2[$row["oid"]] = $row;
					}

					$ret[$row["oid"]] = $row["name"];

					$parentdata[$row["oid"]] = $row["parent"];

					$objdata[$row["oid"]] = array(
						"brother_of" => $row["brother_of"],
						"status" => $row["status"],
						"class_id" => $row["class_id"],
						"jrk" => ifset($row, "jrk"),
					);

					if ($GLOBALS["cfg"]["acl"]["use_new_acl"])
					{
						$row["acldata"] = safe_array(aw_unserialize($row["acldata"]));
						$acldata[$row["oid"]] = $row;
					}
				}
				return array($ret, $this->meta_filter, $acldata, $parentdata, $objdata, $ret2, $has_sql_func);
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
						"class_id" => $row["class_id"],
						"jrk" => $row["jrk"],
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
		$this->delete_object_cache_update($oid);
	}

	function delete_object_cache_update($oid)
	{
		if (!obj_get_opt("no_cache"))
		{
			$this->cache->file_clear_pt_oid("acl", $oid);
			$this->cache->file_clear_pt("html");
			$this->cache->file_clear_pt("menu_area_cache");
		}
	}

	function delete_multiple_objects($oid_list)
	{
		$awa = new aw_array($oid_list);
		$this->db_query("UPDATE objects SET status = '".STAT_DELETED."', modified = ".time().",modifiedby = '".aw_global_get("uid")."' WHERE oid IN(".$awa->to_sql().")");
		if (!obj_get_opt("no_cache"))
		{
			$this->cache->file_clear_pt("acl");
			$this->cache->file_clear_pt("html");
			$this->cache->file_clear_pt("menu_area_cache");
		}
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
		// hits, acl
		$this->db_query("DELETE FROM hits WHERE oid = '$oid'");
		if (!aw_ini_get("acl.use_new_acl_final"))
		{
			$this->db_query("DELETE FROM acl WHERE oid = '$oid'");
		}

		if (!obj_get_opt("no_cache"))
		{
			$this->cache->file_clear_pt("acl");
			$this->cache->file_clear_pt("html");
			$this->cache->file_clear_pt("menu_area_cache");
		}
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
				// add to list of used tables
				$bits = explode(",", $val);
				foreach($bits as $bit)
				{
					list($bit_tbl, $bit_field) = explode(".", $bit);
					if ($bit_tbl != "")
					{
						$this->_add_s($bit_tbl);
					}
				}
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
			$is_done = false;
			if (strpos($key, ".") !== false)
			{
				$_okey = $key;
				list($tbl, $fld) = $this->_do_proc_complex_param(array(
					"key" => &$key,
					"val" => $val,
					"params" => $p_tmp
				));
				if ($tbl == "__rewrite_prop")
				{
					$key = $fld;
				}
				else
				{
					$is_done = true;
				}
				$this->_add_s($tbl);
				// replace unknown columns in fetch sql
				$this->current_fetch_sql = str_replace("%%REPLACE($_okey)%%", $tbl.".`".$key."`", $this->current_fetch_sql);
			}

			if (!$is_done && isset($this->properties[$key]) && $this->properties[$key]["store"] != "no")
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
				$this->_add_s($tbl);
			}

			if ($tbl != "objects")
			{
				$this->has_data_table_filter = true;
			}
			$tf = $tbl.".`".$fld."`";

			if (isset($this->properties[$key]["store"]) && $this->properties[$key]["store"] == "connect")
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
					"on" => $tbl.".".$idx." = "."aliases_".$key.".source AND aliases_".$key.".reltype=".$GLOBALS["relinfo"][$this->class_id][$this->properties[$key]["reltype"]]["value"]
				);
				$this->_add_s("aliases_".$key);
			}

			if (isset($this->properties[$key]["store"]) && $this->properties[$key]["store"] == "connect" && $fld == "meta")
			{
				// figure out the joined alias table name and search from that
				$tbl = "aliases_".$key;
				$fld = "target";
				$tf = $tbl.".`".$fld."`";
				$this->_add_s($tbl);
			}

			if (is_array($val) && ((isset($this->properties[$key]["method"]) && $this->properties[$key]["method"] == "bitmask") || $key == "flags"))
			{
				$sql[] = $tf." & ".$val["mask"]." = ".((int)$val["flags"]);
			}
			else
			if (!is_array($val) && isset($this->properties[$key]) && ($this->properties[$key]["method"] == "bitmask") && $this->properties[$key]["ch_value"] > 0)
			{
				if (is_object($val))
				{
					switch(get_class($val))
					{
						case "obj_predicate_not":
							$sql[] = $tf." & ".((int)$this->properties[$key]["ch_value"])." != ".((int)$this->properties[$key]["ch_value"]);
							break;

						case "obj_predicate_compare":
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

								case OBJ_COMP_IN_TIMESPAN:
									break;

								default:
									error::raise(array(
										"id" => ERR_OBJ_COMPARATOR,
										"msg" => sprintf(t("obj_predicate_compare's comparator operand must be either OBJ_COMP_LESS,OBJ_COMP_GREATER,OBJ_COMP_LESS_OR_EQ,OBJ_COMP_GREATER_OR_EQ,OBJ_COMP_NULL,OBJ_COMP_IN_TIMESPAN. the value supplied, was: %s!"), $val->comparator)
									));
							}

							if ($val->comparator == OBJ_COMP_IN_TIMESPAN)
							{
								$tbl_fld1 = $this->_get_tablefield_from_prop($val->data[0]);
								$tbl_fld2 = $this->_get_tablefield_from_prop($val->data[1]);
								$sql[] = " (NOT ($tbl_fld1 >= '".$val->data2[1]."' OR $tbl_fld2 <= '".$val->data2[0]."')) ";
							}
							else
							if ($val->comparator == OBJ_COMP_NULL)
							{
								$sql[] = $tf." & ".((int)$this->properties[$key]["ch_value"])." IS NULL ";
							}
							else
							if (is_array($v_data))
							{
								$tmp = array();
								foreach($v_data as $d_k)
								{
									$tmp[] = $tf." & ".((int)$d_k)." $comparator ".((int)$d_k)." ";
								}
								$sql[] = "(".join(" OR ", $tmp).")";
							}
							else
							{
								$sql[] = $tf." & ".((int)$v_data)." $comparator ".((int)$v_data)." ";
							}

//							$sql[] = $tf." & ".((int)$this->properties[$key]["ch_value"])." != ".((int)$this->properties[$key]["ch_value"]);
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
							$sql[] = $tf." NOT IN (".join(",", map("'%s'", $v_data)).") ";
						}
					}
					else
					{
						$opn_app = "";
						if (!is_numeric($v_data) || $v_data != 0)
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
				if ($class_name == "obj_predicate_regex")
				{
					$v_data = $val->data;
					$sql[] = " (".$tf." REGEXP '".$v_data."'  ) ";
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

						case OBJ_COMP_IN_TIMESPAN:
							break;

						default:
							error::raise(array(
								"id" => ERR_OBJ_COMPARATOR,
								"msg" => sprintf(t("obj_predicate_compare's comparator operand must be either OBJ_COMP_LESS,OBJ_COMP_GREATER,OBJ_COMP_LESS_OR_EQ,OBJ_COMP_GREATER_OR_EQ,OBJ_COMP_NULL,OBJ_COMP_IN_TIMESPAN. the value supplied, was: %s!"), $val->comparator)
							));
					}

					if ($val->comparator == OBJ_COMP_IN_TIMESPAN)
					{
						$tbl_fld1 = $this->_get_tablefield_from_prop($val->data[0]);
						$tbl_fld2 = $this->_get_tablefield_from_prop($val->data[1]);
						$sql[] = " (NOT ($tbl_fld1 >= '".$val->data2[1]."' OR $tbl_fld2 <= '".$val->data2[0]."')) ";
					}
					else
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
						if(!($val->type == "int"))
						{
							$ent = "'";
						}
						else
						{
							$ent = "";
						}
						$sql[] = $tf." $comparator $ent".$v_data."$ent ";
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
				else
				if ($class_name == "obj_predicate_limit")
				{
					if (($tmp = $val->get_per_page()) > 0)
					{
						$this->limit = " LIMIT ".$val->get_from().",$tmp ";
					}
					else
					{
						$this->limit = " LIMIT ".$val->get_from()." ";
					}
				}
				else
				if ($class_name == "obj_predicate_sort")
				{
					$this->sby = " ORDER BY ";
					$tmp = array();
					foreach($val->get_sorter_list() as $sl_item)
					{
						if (strpos($sl_item["prop"], ".") !== false)
						{
							// no support for prop.prop.prop yet, just class definer
							list(, $sl_item["prop"]) = explode(".", $sl_item["prop"]);
							$pd = $this->properties[$sl_item["prop"]];
						}
						else
						if (isset($GLOBALS["object_loader"]->all_ot_flds[$sl_item["prop"]]) || $sl_item["prop"] == "oid")
						{
							$pd = array("table" => "objects", "field" => $sl_item["prop"]);
						}
						else
						{
							$pd = $this->properties[$sl_item["prop"]];
						}
						if ($pd["table"])
						{
							$this->used_tables[$pd["table"]] = $pd["table"];
						}
						$tmp[] = $pd["table"].".`".$pd["field"]."` ".($sl_item["direction"] == "desc" ? "DESC" : "ASC")." ";
						$this->_add_s($pd["table"]);
					}
					$this->sby .= join(", ", $tmp);
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
					if (isset($this->properties[$key]["store"]) && $this->properties[$key]["store"] == "connect")
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
				if (isset($this->properties[$key]["store"]) && $this->properties[$key]["store"] == "connect")
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
				$clss = aw_ini_get("classes");

				list($GLOBALS["properties"][$clid], $GLOBALS["tableinfo"][$clid], $GLOBALS["relinfo"][$clid]) = $GLOBALS["object_loader"]->load_properties(array(
					"file" => ($clid == CL_DOCUMENT ? "doc" : basename(isset($clss[$clid]) ? $clss[$clid]["file"] : "")),
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

		// fetch site id from the parent
		$od = $this->get_objdata($parent);
		$objdata["site_id"] = $od["site_id"];//aw_ini_get("site_id");

		$acld_fld = $acld_val = "";
		if (aw_ini_get("acl.use_new_acl") && $_SESSION["uid"] != "" && is_oid(aw_global_get("uid_oid")))
		{
			$uo = obj(aw_global_get("uid_oid"));
			$g_d = $uo->get_default_group();
			$acld_fld = ",acldata";
			$acld_val = ",'".str_replace("'", "\\'", aw_serialize(array(
				$g_d => $this->get_acl_value_n($this->acl_get_default_acl_arr())
			)))."'";
		}

		// create oid
		$q = "
			INSERT INTO objects (
				parent,						class_id,						name,						createdby,
				created,					modified,						status,						site_id,
				hits,						lang_id,						comment,					modifiedby,
				jrk,						period,							alias,						periodic,
				metadata,					subclass,					flags,
				brother_of					$acld_fld
		) VALUES (
				'".$parent."',				'".$objdata["class_id"]."',		'".$objdata["name"]."',		'".$objdata["createdby"]."',
				'".$objdata["created"]."',	'".$objdata["modified"]."',		'".$objdata["status"]."',	'".$objdata["site_id"]."',
				'".$objdata["hits"]."',		'".$objdata["lang_id"]."',		'".$objdata["comment"]."',	'".$objdata["modifiedby"]."',
				'".$objdata["jrk"]."',		'".$objdata["period"]."',		'".$objdata["alias"]."',	'".$objdata["periodic"]."',
										'".$metadata."',				'".$objdata["subclass"]."',	'".$objdata["flags"]."',
				'".$objdata["oid"]."'		$acld_val
		)";
		//echo "q = <pre>". htmlentities($q)."</pre> <br />";
		$this->db_query($q);
		$oid = $this->db_last_insert_id();

		if (!aw_ini_get("acl.use_new_acl_final"))
		{
			// create all access for the creator
			$this->create_obj_access($oid);
		}

		// hits
		$this->db_query("INSERT INTO hits(oid,hits,cachehits) VALUES($oid, 0, 0 )");

		$this->create_brother_cache_update(null);

		return $oid;
	}

	function create_brother_cache_update($oid)
	{
		if (!obj_get_opt("no_cache"))
		{
			$this->cache->file_clear_pt("html");
		}
	}

	// $key, $val
	function _do_proc_complex_param($arr)
	{
		extract($arr);
		$filt = explode(".", $key);
		if (!defined($filt[0]))
		{
			$clid = $arr["params"]["class_id"];
			if (is_array($clid))
			{
				error::raise(array(
					"id" => "ERR_OL_PARAM_ERROR",
					"msg" => sprintf(t("You must specify class id in a complex filter parameter (%s) if searching from multiple classes!"), $key)
				));
			}
		}
		else
		{
			$clid = constant($filt[0]);
		}

		if (substr($filt[0], 0, 3) != "CL_" && (is_array($params["class_id"]) || is_class_id($params["class_id"])))
		{
			$clss = aw_ini_get("classes");
			if (is_array($params["class_id"]))
			{
				$m_clid = reset($params["class_id"]);
			}
			else
			{
				$m_clid = $params["class_id"];
			}
			$key = $clss[$m_clid]["def"].".".$key;
			$filt = explode(".", $key);
			$clid = constant($filt[0]);
		}
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
					// so just return the table and field for that clas
					if (!isset($GLOBALS["properties"][$clid]))
					{
						$this->_do_add_class_id($clid);
					}
					if (isset($GLOBALS["properties"][$clid][$filt[1]]))
					{
						$prop = $GLOBALS["properties"][$clid][$filt[1]];
					}
					else
					{
						// see if it is an objtbl prop
						switch($filt[1])
						{
							case "id":
							case "oid":
								return array("objects", "oid");

							case "created":
							case "createdby":
							case "modified":
							case "modifiedby":
							case "parent":
							case "name":
							case "lang_id":
							case "comment":
							case "period":
							case "ord":
							case "site_id":
								return array("objects", $filt[1]);
						}
					}

					if ($prop["store"] == "connect" || $prop["method"] == "serialize")	// need psecial handling, rewrite to undefined class filter
					{
						return array("__rewrite_prop", $filt[1]);
					}
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
				//$this->joins[] = $str;
				$this->_add_join($str);
				$this->_add_s($tbldat["master_table"]);
			}
		}

		$done_ot_js = array(); 	// reverting remembering this, because previously it was needed for double joins on same names, but now we filter the doubles and unneeded joins out later anyway
					// and this actually can cause some joins to go missing that are needed, cause it does not recurd the full parameter list
		// now make joins and for the final prop, query
//echo dbg::dump($this->join_data);
		foreach($this->join_data as $pos => $join)
		{
//echo "process ".dbg::dump($join)." <br>";
			if ($join["via"] == "rel")
			{
				// from prev to alias from alias to obj
				if (empty($join["table"]))
				{
					$prev_t = "";
				}
				else
				{
					$prev_t = $join["table"]."_".$join["from_class"];
				}
				$prev_clid = $join["from_class"];

				$tmp_prev = $this->join_data[$pos-1];
				$cur_al_name = "aliases_".$tmp_prev["from_class"]."_".$tmp_prev["reltype"]."_".$join["to_class"]."_".$join["reltype"];
				$rel_from_field = "source";
				$rel_to_field = "target";
				if (ifset($join, "is_reverse") == 1)
				{
					$rel_from_field = "target";
					$rel_to_field = "source";
				}
				$str  = " LEFT JOIN aliases $cur_al_name ON ".$cur_al_name.".".$rel_from_field." = ";
				if ($join["from_class"] == $clid)
				{
					$str .= " objects.oid ";
				}
				else
				if ($tmp_prev["via"] == "rel")
				{
					$_tb_name = "objects__".$tmp_prev["from_class"]."_".$join["from_class"]."_".$tmp_prev["reltype"];
					$str .= " ".$_tb_name.".oid ";
					$this->_add_s($_tb_name);
				}
				else
				{
					$_tb_name = "objects_".$join["from_class"];
					$str .= " ".$_tb_name.".oid ";
					$this->_add_s($_tb_name);
				}

				if ($join["reltype"])
				{
					$str .= " AND ".$cur_al_name.".reltype = ".$join["reltype"];
				}
				$this->_add_join($str);
//				$this->joins[] = $str;

				$tmp_cur_obj_name = "objects_".$tmp_prev["reltype"]."_".$join["from_class"]."_".$join["to_class"]."_".$join["reltype"];

				$str  = " LEFT JOIN objects $tmp_cur_obj_name  ON ".$cur_al_name.".".$rel_to_field." = ";
				$str .= " ".$tmp_cur_obj_name.".oid ";
				$prev_clid = $join["to_class"];
				$this->_add_s($cur_al_name);

				$this->_add_join($str);
//				$this->joins[] = $str;

				$new_t = $GLOBALS["tableinfo"][$join["to_class"]];

				if (is_array($new_t))
				{
					$objt_name = $tmp_cur_obj_name;
					$tbl = $tbl_r = reset(array_keys($new_t));
					if ($tbl != "")
					{
						$field = $new_t[$tbl]["index"];
						$tbl .= "_".$join["from_class"]."_".$join["field"];
						if (!isset($done_ot_js[$tbl_r]))
						{
							$str = " LEFT JOIN ".$tbl_r." $tbl ON ".$tbl.".".$field." = ".$objt_name.".brother_of";
							$this->_add_s($objt_name);
							$this->_add_join($str);
//							$this->joins[] = $str;
							$done_ot_js[$tbl_r] = 1;
							$prev_t = $tbl;
						}
					}

					// now, if the next join is via rel, we are gonna need the objects table here as well, so add that
					if ($this->join_data[$pos+1]["via"] == "rel" && $tbl != "")
					{
						$o_field = "oid";
						$o_tbl = "objects_".$join["to_class"];
						if (!isset($done_ot_js[$o_tbl]))
						{
							$str = " LEFT JOIN objects $o_tbl ON ".$o_tbl.".".$o_field." = ".$tbl.".".$field;
							$this->_add_s($tbl);
							$this->_add_join($str);
//							$this->joins[] = $str;
						}
					}
				}

				$prev_al_name = $cur_al_name;
				$ret = array(
					$cur_al_name, //"aliases_".$join["from_class"]."_".$join["reltype"],
					$rel_to_field,
				);
			}
			else	// via prop
			{
				if (!$join["to_class"] && $this->join_data[$pos-1]["via"] == "rel")
				{
					$prev = $this->join_data[$pos-1];
					$prev_prev = $this->join_data[$pos-2];

					$this->_do_add_class_id($join["from_class"]);
					// join from rel to prop
					$prev_t = $prev_al_name; //"aliases_".$prev["from_class"]."_".$prev["reltype"];

					$new_t = $GLOBALS["tableinfo"][$join["from_class"]];
					$do_other_join = false;
					$and_buster = "";
					if (!is_array($new_t) || !isset($GLOBALS["properties"][$join["from_class"]][$join["prop"]]) || $GLOBALS["properties"][$join["from_class"]][$join["prop"]]["table"] == "objects")
					{
						// class only has objects table, so join that
						$tbl = "objects_rel_".$prev["from_class"]."_".$prev["reltype"]."_".$join["from_class"]."_".$prev["reltype"]."_".$prev_prev["reltype"];
						$tbl_r = "objects";
						$field = "oid";

						// and also join any other tables as well just to be on the safe side.
						$do_other_join = is_array($new_t);
						$and_buster = " AND $tbl.status > 0 ";
					}
					else
					{
						$tbl = $tbl_r = reset(array_keys($new_t));
						$field = $new_t[$tbl]["index"];
					}

					if (ifset($prev, "is_reverse") == 1)
					{
						$tmp_fld = "source";
					}
					else
					{	
						$tmp_fld = "target";
					}
					$str = " LEFT JOIN ".$tbl_r." $tbl ON ".$tbl.".".$field." = ".$prev_t.".".$tmp_fld." $and_buster ";
					$this->_add_s($prev_t);
					$this->_add_join($str);
//					$this->joins[] = $str;
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
					if ($join["prop"] == "parent")
					{
						$__fld = "parent";
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
				if (empty($prev_t))
				{
					$prev_t = $join["table"]."_".$join["from_class"];
				}
				$prev_filt = $join["field"];
				$prev_clid = $join["from_class"];

				$objt_name = "objects_".$join["from_class"]."_".$join["field"];
				if (!isset($done_ot_js[$objt_name]))
				{
					$this->_add_join(" LEFT JOIN objects $objt_name ON ".$objt_name.".oid = $prev_t.".$join["field"]." ");
					$this->_add_s($prev_t);
					$done_ot_js[$objt_name] = 1;
				}

				$new_t = $GLOBALS["tableinfo"][$join["to_class"]];

				if (is_array($new_t) && count($new_t))
				{
					$tbl = $tbl_r = reset(array_keys($new_t));
					if ($tbl)
					{
						$field = $new_t[$tbl]["index"];
						$tbl .= "_".$join["from_class"]."_".$join["field"];
						if (!isset($done_ot_js[$tbl_r]))
						{
							$str = " LEFT JOIN ".$tbl_r." $tbl ON ".$tbl.".".$field." = ".$objt_name.".brother_of";
							$this->_add_s($objt_name);
							$this->_add_join($str);
//							$this->joins[] = $str;
							$done_ot_js[$tbl_r] = 1;
							$prev_t = $tbl;
						}
					}

					// now, if the next join is via rel, we are gonna need the objects table here as well, so add that
					if ($this->join_data[$pos+1]["via"] == "rel")
					{
						$o_field = "oid";
						$o_tbl = "objects_".$join["to_class"];
						if (!isset($done_ot_js[$o_tbl]))
						{
							$str = " LEFT JOIN objects $o_tbl ON ".$o_tbl.".".$o_field." = ".$tbl.".".$field;
							$this->_add_s($tbl);
							$this->_add_join($str);
//							$this->joins[] = $str;
						}
					}
				}
			}
		}

		$this->done_ot_js = $done_ot_js;

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

			// check if this is RELTYPE_FOO(CL_CLID) that means a reverse relation check
			if (preg_match("/RELTYPE_(.*)\((.*)\)/", $pp, $mt))
			{
				$nxt_clid = constant($mt[2]);
				if ($nxt_clid)
				{
					$this->_do_add_class_id($nxt_clid);
					$reltype_id = $GLOBALS["relinfo"][$nxt_clid]["RELTYPE_".$mt[1]]["value"];
					error::raise_if(!$reltype_id && $pp != "RELTYPE", array(
						"id" => ERR_OBJ_NO_RELATION,
						"msg" => sprintf(t("ds_mysql::_req_do_pcp(): no relation from class %s named %s"), $cur_clid, $pp)
					));

					// calc new class id
					$new_clid = $nxt_clid;

					$this->join_data[] = array(
						"via" => "rel",
						"reltype" => $reltype_id,
						"from_class" => $cur_clid,
						"to_class" => $nxt_clid,
						"is_reverse" => 1
					);
				}
			}
			else
			{
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
			if ($pp == "oid")
			{
				$cur_prop = array("name" => "oid", "table" => "objects", "field" => "oid");
			}
			else
			if ($pp == "class_id")
			{
				$cur_prop = array("name" => "id", "table" => "objects", "field" => "class_id");
			}
			else
			if ($pp == "parent")
			{
				$cur_prop = array("name" => "parent", "table" => "objects", "field" => "parent");
			}
			else
			if ($pp == "created")
			{
				$cur_prop = array("name" => "created", "table" => "objects", "field" => "created");
			}
			else
			if ($pp == "createdby")
			{
				$cur_prop = array("name" => "createdby", "table" => "objects", "field" => "createdby");
			}
			else
			if ($pp == "modified")
			{
				$cur_prop = array("name" => "modified", "table" => "objects", "field" => "modified");
			}
			else
			if ($pp == "modifiedby")
			{
				$cur_prop = array("name" => "modifiedby", "table" => "objects", "field" => "modifiedby");
			}
			else
			if ($pp == "ord")
			{
				$cur_prop = array("name" => "ord", "table" => "objects", "field" => "jrk");
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
				error::raise_if($cur_prop["method"] == "serialize" && $cur_prop["store"] != "connect", array(
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
						case "releditor":
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

			if (ifset($cur_prop, "store") == "connect")
			{
				$this->_do_add_class_id($cur_clid);
				// rewrite to a reltype join
				$pp = $cur_prop["reltype"];
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
		}

		if ($pos < (count($filt)-1))
		{
			$this->_req_do_pcp($filt, $pos+1, $new_clid, $arr);
		}
	}

	function _get_joins($params)
	{
		// check if join strategy is present in args and do joins based on that
		if (false && !empty($params["join_strategy"]))
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
				if ($tbl != "objects" && $tbl != "")
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
			// this can not be cached, because it holds in it object id's for the query. silly, really.
			if (true || !($sql = aw_cache_get("storage::get_read_properties_sql",$clid)))
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
//				echo dbg::dump($sql).dbg::short_backtrace();
				$this->db_query($sql["q"]);
				while ($row = $this->db_next())
				{
					foreach ($this->properties as $property_name => $property_data)
					{
						if (isset($property_data['type']) and $property_data['type'] == 'range')
						{
							$row[$property_name] = array(
								"from" => $row[$property_name."_from"],
								"to" => $row[$property_name."_to"],
							);
							unset($row[$property_name."_from"], $row[$property_name."_to"]);
						}
					}
					$this->read_properties_data_cache[$row["oid"]] = $row;
					$GLOBALS["read_properties_data_cache_conn"][$row["oid"]] = array();
					$ret[] = $row;
				}
			}
			if ($sql["q2"] != "")
			{
				$this->db_query($sql["q2"]);
				while ($row = $this->db_next())
				{
					$GLOBALS["read_properties_data_cache_conn"][$row["source"]][$row["reltype"]][] = $row;
					$ret2[] = $row;
					//$GLOBALS["obj_conn_fetch_vals"][$this->oid][$param]
				}
			}
		}
		return $ret;
	}

	/*function quote(&$str)
	{
		$str = str_replace("'", "\\'", str_replace("\\", "\\\\", $str));
	}*/

	function _get_search_fetch($to_fetch, &$filter)
	{
		if (!is_array($to_fetch))
		{
			return array(0 => "", 1 => array(), 2 => array(), 3 => false, 4 => array());
		}
		$has_func = false;
		$ret = array();
		$serialized_fields = array();
		$multi_fields = array();
		foreach($to_fetch as $clid => $props)
		{
			$p = $GLOBALS["properties"][$clid];
			$this->_do_add_class_id($clid, true);
			foreach($props as $pn => $resn)
			{
				if (is_numeric($pn) && !is_object($resn))
				{
					$pn = $resn;
				}
				if (is_object($resn) && get_class($resn) == "obj_sql_func")
				{
					$has_func = true;
					$param = $resn->params;
					if (isset($p[$param]))
					{
						$this->_add_s($p[$param]["table"]);
						$param = $p[$param]["table"].".`".$p[$param]["field"]."`";
					}
					switch($resn->sql_func)
					{
						case OBJ_SQL_UNIQUE:
							$ret[$pn] = " DISTINCT(".$param.") AS `".$resn->name."` ";
							break;

						case OBJ_SQL_COUNT:
							$ret[$pn] = " COUNT(".$param.") AS `".$resn->name."` ";
							break;

						case OBJ_SQL_MAX:
							$ret[$pn] = " MAX(".$param.") AS `".$resn->name."` ";
							break;

						case OBJ_SQL_MIN:
							$ret[$pn] = " MIN(".$param.") AS `".$resn->name."` ";
							break;

						default:
							error::raise(array(
								"id" => "MSG_WRONG_FUNC",
								"msg" => sprintf(t("ds_mysql::_get_search_fetch() was called with incorrect sql func %s"), $resn->sql)
							));
					}
				}
				else
				if (is_numeric($pn))
				{
					$pn = $resn;
				}
				else
				if (substr($pn, 0, 5) == "meta.")
				{
					$serialized_fields["objects.metadata"][] = substr($pn, 5);
				}
				else
				if (strpos($pn, ".") !== false)
				{
					// over-prop join fetch. we don't know the column name yet, so let it replace it in req_make_sql when we figure it out.
					if (!isset($filter[$pn]))
					{
						$filter[$pn] = new obj_predicate_anything();
						$ret[$pn] = "%%REPLACE($pn)%% AS `$resn`"; //aliases___1063_26.target AS $resn ";
					}
				}
				else
				if (!isset($p[$pn]))
				{
					// assume obj table
					$ret[$pn] = " objects.$pn AS `$resn` ";
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
						$this->_add_s($p[$pn]["table"]);
					}
				}
				else
				if ($p[$pn]["store"] == "connect")
				{
					// fetch value from aliases table
					if (!isset($filter[$pn]))
					{
						$filter[$pn] = new obj_predicate_anything();
						$ret[$pn] = " aliases_".$pn.".target AS $resn ";
						$this->_add_s("aliases_".$pn);
					}
					else
					{
						$tbl_name = "aliases_".$clid."_".$GLOBALS["relinfo"][$clid][$p[$pn]["reltype"]]["value"];
						$ret[$pn] = " ".$tbl_name.".target AS $resn ";
						$this->_add_s($tbl_name);
					}
					if (!empty($p[$pn]["multiple"]))
					{
						$multi_fields[$pn] = $pn;
					}
				}
				else
				{
					$ret[$pn] = " ".$p[$pn]["table"].".`".$p[$pn]["field"]."` AS `$resn` ";
					$this->_add_s($p[$pn]["table"]);
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

		$acld = "";
		if ($GLOBALS["cfg"]["acl"]["use_new_acl"])
		{
			$acld = " objects.acldata as acldata, objects.parent as parent,";
		}

		if ($has_func)
		{
			$fetch_sql = "";
		}
		else
		{
			$fetch_sql = "
				objects.oid as oid,
				objects.name as name,
				objects.parent as parent,
				objects.brother_of as brother_of,
				objects.status as status,
				objects.class_id as class_id,
				$acld
			";
		}
		$res =  $fetch_sql.join(",", $ret);
		return array($res, array_keys($ret), $sf, $has_func, $multi_fields);
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

		if (!obj_get_opt("no_cache"))
		{
			$this->cache->file_clear_pt("html");
		}
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
					if (!array_key_exists($prop["field"], $_got_fields))
					{
						$fields[] = $table."_versions.`".$prop["field"]."` AS `".$prop["field"]."`";
						$_got_fields[$prop["field"]] = true;
					}
				}
				else
				if ($prop["store"] == "connect")
				{
					if ($GLOBALS["cfg"]["site_id"] != 139)
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
			if (isset($GLOBALS["read_properties_data_cache_conn"][$object_id]))
			{
				$cfp_dat = $GLOBALS["read_properties_data_cache_conn"][$object_id];
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

	function backup_current_version($arr)
	{
		$id = $arr["id"];
		// create a complete copy of the current object to the _versions table

		$table_name = reset(array_keys($arr["tableinfo"]))."_versions";
		$table_dat = reset($arr["tableinfo"]);
		$properties = $arr["properties"];
		$tableinfo = $arr["tableinfo"];

		$version_id = gen_uniq_id();
		$this->db_query("INSERT INTO `$table_name` (version_id, $table_dat[index], vers_crea, vers_crea_by) values('$version_id', $id, ".time().", '".aw_global_get("uid")."')");

		$objdata = $this->get_objdata($id);
		$propvalues = $this->read_properties(array(
			"properties" => $properties,
			"tableinfo" => $tableinfo,
			"objdata" => $objdata,
		));
		$objdata["metadata"] = $this->db_fetch_field("SELECT metadata FROM objects WHERE oid = '$id'", "metadata");

		$ot_sets = array();
		$arr["ot_modified"] = $GLOBALS["object_loader"]->all_ot_flds;
		foreach(safe_array($arr["ot_modified"]) as $_field => $one)
		{
			$this->quote(&$objdata[$_field]);
			$ot_sets[] = " o_".$_field." = '".$objdata[$_field]."' ";
		}

		$ot_sets = join(" , ", $ot_sets);

		$q = "UPDATE `$table_name` SET
			$ot_sets
			WHERE version_id = '".$version_id."'
		";

		//echo "q = <pre>". htmlentities($q)."</pre> <br />";
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
	}

	function originalize($oid)
	{
		$brof = $this->db_fetch_field("SELECT brother_of from objects where oid = '$oid'", "brother_of");
		$this->db_query("UPDATE objects SET brother_of = '$oid' WHERE brother_of = '$brof'");
		$this->db_query("UPDATE aliases SET source = '$oid' WHERE source = '$brof'");
		$this->db_query("UPDATE aliases SET target = '$oid' WHERE target = '$brof'");
		$this->originalize_cache_update($oid);
	}

	function originalize_cache_update($oid)
	{

	}

	/** returns table.field, for the given prop **/
	function _get_tablefield_from_prop($key, $val=null, $p_tmp=null)
	{
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
		return $tbl.".`".$fld."`";
	}

	function compile_oql_query($oql)
	{
		// parse into bits
		preg_match("/SELECT(.*)FROM(.*)WHERE(.*?)/imsU", $oql, $mt);

		// now turn it into sql
		$main_clid = constant(trim($mt[2]));
		error::raise_if(!is_class_id($main_clid), array(
			"id" => "ERR_NO_MAIN_CLID",
			"msg" => sprintf(t("object_complex_query::compile_oql_query(): FROM clause has an error, unrecognized clid %s"), $main_clid)
		));

		$this->properties = array();
		$this->tableinfo = array();
		$this->used_tables = array();
		$this->done_ot_js = array();
		$this->_do_add_class_id($main_clid);

		$fetch = $this->_parse_fetch($mt[1], $main_clid);
		list($joins, $where) = $this->_parse_where($mt[3], $main_clid);
		$from = $this->_parse_from($main_clid);

		$q =  "SELECT $fetch FROM $from $joins WHERE $where";
		return $q;
	}

	function execute_oql_query($sql)
	{
		$this->db_query($sql);
		$rv = array();
		while ($row = $this->db_next())
		{
			$rv[$row["oid"]] = $row;
		}
		return $rv;
	}

	function _parse_from($main_clid)
	{
		$str = "";
		foreach($GLOBALS["tableinfo"][$main_clid] as $tbl => $dat)
		{
			$str .= " LEFT JOIN $tbl ON $tbl.".$dat["index"]." = ".$dat["master_table"].".".$dat["master_index"]." ";
		}
		return " objects ".$str;
	}

	function _parse_where($str, $main_clid)
	{
		// we have to tokenize things here.
		$p = get_instance("core/aw_code_analyzer/parser");
		$p->p_init(trim($str));
		$new_str = " objects.status > 0 AND objects.class_id = $main_clid AND ";
		$props = $GLOBALS["properties"][$main_clid];
		while (!$p->p_eos())
		{
			$tok = $p->_p_get_token();
			if (substr($tok, 0, 2) == "CL")
			{
				list($t, $f) = $this->_do_proc_complex_param(array(
					"key" => &$tok,
					"val" => $val,
					"params" => $p_tmp,
				));
				// resolve prop
				//list($clid, $p2) = explode(".", $tok, 2);
				$tf = $t.".`".$f."`";//$props[$p2]["table"].".`".$props[$p2]["field"]."`";
				$new_str .= $tf;
			}
			else
			if (isset($props[$tok]))
			{
				// also prop
				$tf = $props[$tok]["table"].".`".$props[$tok]["field"]."`";
				$new_str .= $tf;
			}
			else
			if ($tok == "?")
			{
				$new_str .= "%s";
			}
			else
			{
				$new_str .= $tok;
			}
		}
		return array(join(" ", $this->joins), $new_str);
	}

	function _parse_fetch($str, $main_clid)
	{
		$props = $GLOBALS["properties"][$main_clid];
		$fetch = array();
		foreach(explode(",", trim($str)) as $prop_fetch)
		{
			if (preg_match("/(.*)\sAS\s(.*)/imsU", $prop_fetch, $pf))
			{
				$fetch[trim($pf[1])] = trim($pf[2]);
			}
			else
			{
				$fetch[trim($prop_fetch)] = trim($prop_fetch);
			}
		}
		$nf = array();
		foreach($fetch as $prop => $as)
		{
			if ($prop == "id")
			{
				$tf = "objects.oid";
			}
			else
			{
				if (!isset($props[$prop]))
				{
					error::raise(array(
						"id" => "ERR_NO_PROP",
						"msg" => sprintf(t("ds_mysql::_parse_fetch(): no property %s in class %s"), $prop, $main_clid)
					));
				}
				$tf = $props[$prop]["table"].".`".$props[$prop]["field"]."`";
			}
			$nf[$tf] = $as;
		}
		$str = array();
		foreach($nf as $tf => $as)
		{
			$str[] = " $tf AS `$as` ";
		}
		return "objects.oid AS oid, objects.parent AS parent, objects.acldata AS acldata, ".join(",", $str);
	}

	private function _add_s($tbl)
	{
		$this->search_tables_used[$tbl] = 1; //dbg::short_backtrace();
	}

	private function _add_join($str)
	{
//	echo "add join $str from ".dbg::short_backtrace()." <br> <br>";
		$this->joins[] = $str;
	}

	private function _optimize_joins($j, $used)
	{
//echo dbg::dump($j);
enter_function("ds_mysql::optimize_joins");
		$j = trim(substr($j, strlen("objects")));
		$js = explode("\n", str_replace("LEFT JOIN", "\nLEFT JOIN", $j));
		$rs = "objects ";
		foreach($js as $join_line)
		{
			if (trim($join_line) == "")
			{
				continue;
			}
			$joined_table = null;
			if (preg_match("/LEFT JOIN (.*) (.*) ON (.*)\.(.*) = (.*)\.(\S*)/imsU", $join_line, $mt))
			{
				$joined_table = $mt[2];
			}
			else	// no rename table
			if (preg_match("/LEFT JOIN (.*) ON (.*)\.(.*) = (.*)\.(\S+?)/imsU", $join_line, $mt))
			{
				$joined_table = $mt[1];
			}

			if ($joined_table !== null)
			{
				if (isset($this->search_tables_used[trim($joined_table)]) || $joined_table == "documents")
				{
					$rs .= $join_line." ";
				}
			}
		}
exit_function("ds_mysql::optimize_joins");
		return $rs;
	}
}

?>
