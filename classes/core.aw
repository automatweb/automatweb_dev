<?php
// $Header: /home/cvs/automatweb_dev/classes/core.aw,v 2.199 2003/06/03 16:48:59 duke Exp $
// core.aw - Core functions

// if a function can either return all properties for something or just a name, then use 
// $return parameter and give it one of these defined values, so that it will be consistent
define("ARR_NAME",1);
define("ARR_ALL",2);

// document template types 
define("TPLTYPE_TPL",0);
define("TPLTYPE_FORM",1);

// object statuses
define("STAT_DELETED", 0);
define("STAT_NOTACTIVE", 1);
define("STAT_ACTIVE", 2);

classload("db");
class core extends db_connector
{
	var $errmsg;		

	////
	// !fetch the value for config key $ckey
	function get_cval($ckey)
	{
		$q = sprintf("SELECT content FROM config WHERE ckey = '%s'",$ckey);
		return $this->db_fetch_field($q,"content");
	}

	////
	// !set config key $ckey to value $val
	function set_cval($ckey,$val)
	{
	  $ret = $this->db_fetch_row("SELECT content FROM config WHERE ckey = '$ckey'");
	  if (!is_array($ret))
	  {
			// create key if it does not exist
			$this->db_query("INSERT INTO config(ckey, content, modified, modified_by) VALUES('$ckey','$val',".time().",'".aw_global_get("uid")."')");
	  }
	  else
	  {
			$this->db_query("UPDATE config SET content = '$val', modified = '".time()."', modified_by = '".aw_global_get("uid")."' WHERE ckey = '$ckey' ");
	  }
	  return $val;
	}

	////
	// !Setter for object
	function set_opt($key,$val)
	{
		$this->$key = $val;
	}
	
	////
	// !Setter for object
	function get_opt($key)
	{
		return $this->$key;
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////
	// object handling functions - add, change, delete
	//////////////////////////////////////////////////////////////////////////////////////////////////


	////
	// !adds a new object, inserts all fields that are given in the array and puts default
	// values for all others
	// lang_id,site_id,created,createdby,modified,modifiedby are set to correct values automatically
	// parameters, that are not written to objects table:
	// no_flush - optional, if true, the cache is not flushed, thus object creation is a lot faster, 
	//            but you must flush the cache manually later. this is useful when creating lots of objects in a row
	// add_acl - if set to false, acl entries are not created for the object
  //
  // when creating an object, at least the parent and class_id values must be specified
	// status defaults to 1 - not active
	// by default all access is given to the creator of the object
	function new_object($arr,$add_acl = true)
	{
		if (!is_array($arr))
		{
			$this->raise_error(ERR_CORE_NO_PARAMS, "core::new_object() - incorrect parameter, 1st parameter must be an array", true, false);
		};

		if (!$arr['class_id'])
		{
			$this->raise_error(ERR_CORE_NO_TYPE, "core::new_object() - no class_id specified", true, false);
		}

/*		if (!$this->can('add', $arr['parent']) || aw_global_get("uid") == "")
		{
			$this->raise_error(ERR_ACL_ERR, "core::new_object() - no can_add access for '$arr[parent]'!", true, false);
		}*/

		// objektitabeli v�ljad
		// dummy on selleks, et array_flip juures parenti v��rtuseks "0" ei tuleks
		$_ofields = array("dummy","parent","name","createdby","class_id","created","modified",
				"status","hits","lang_id","comment","last","modifiedby",
				"jrk","period","alias","periodic","site_id","activate_at",
				"deactivate_at","autoactivate","autodeactivate","brother_of",
				"cache_dirty","metadata","subclass","flags"
			);

		// vahetame key=>value paarid �ra
		$ofields = array_flip($_ofields);

		// check whether to flush the cache
		$no_flush = $arr["no_flush"];
		unset($arr["no_flush"]);
		
		// oid voib tulla naiteks serializetud objektist
		// anyhow, meil siin pole sellega midagi peale hakata, so nil.
		unset($arr["oid"]);

		// Nende v��rtustega kirjutame �le whatever asjad $arr sees olid
		$_localdata = array(
			"createdby" => aw_global_get("uid"),
			"created" => time(),
			"modifiedby" => aw_global_get("uid"),
			"modified" => time(),
			// there is a potentional problem here - if there is some kind
			// of master sites where the contents of multiple sites can
			// be viewed, and then an object under one of those sites is
			// added, it will have the site_id of the current site and
			// not that of the parent
			"site_id" => $this->cfg["site_id"],
		);

		if (!$arr["lang_id"])
		{
			$arr["lang_id"] = aw_global_get("lang_id");
		}

		if (!isset($arr["status"]))
		{
			$arr["status"] = STAT_NOTACTIVE;
		}

		// array array_merge (array array1, array array2 [, array ...])
		// If the input arrays have the same string keys,
		//  then the later value for that key will overwrite the previous one.
		$values = array_merge($arr,$_localdata);
		
		foreach($values as $key => $val)
		{
			// Lisame ainult need valjad, mis objektitabelisse kuuluvad
			if (isset($ofields[$key]))
			{
				$cols[] = $key;
				$vals[] = "'$val'";
			};

		}
		$q = "INSERT INTO objects ( " . join(",",$cols) . ") VALUES (" . join(",",$vals) . ")";
		$this->db_query($q);
		$oid = $this->db_last_insert_id();

		if ($oid > 0 && $add_acl)
		{
			$this->create_obj_access($oid);
		};

		if (is_array($arr["metadata"]))
		{
			$this->set_object_metadata(array(
				"oid" => $oid,
				"data" => $arr["metadata"]
			));
		}

		if (!isset($arr["brother_of"]))
		{
			$this->db_query("UPDATE objects SET brother_of = oid WHERE oid = $oid");
		}

		// hits
		$this->db_query("INSERT INTO hits(oid,hits,cachehits) VALUES($oid, 0, 0 )");

		if (!$no_flush)
		{
			$this->flush_cache();
		};
		return $oid;
	}

	////
	// !updates the given fields in the object
	// parameters:
	//   oid - required, id of the object to update
	//   any object table field - optional
	// all parameters must be quoted when entering this function
	function upd_object($params) 
	{
		if (empty($params["oid"]))
		{
			$this->raise_error(ERR_CORE_NO_OID, "core::upd_object() - called without an oid!", true, false);
		};

		if (isset($params["parent"]) && $params["oid"] == $params["parent"])
		{
			$this->raise_error(ERR_CORE_NO_OID,"core::upd_object() - object can't be it's own parent",true, false);
		};

/*		if (!$this->can('edit', $params['oid']) || aw_global_get("uid") == "")
		{
			$this->raise_error(ERR_ACL_ERR, "core::upd_object() - no can_edit access for $params[oid]!", true, false);
		}*/

		$params["modifiedby"] = aw_global_get("uid");
		// allow overwriting of the modified field. This SHOULD be temporary
		// but right this is the fastest way to make AM not suck.
		$params["modified"] = isset($params["modified"]) ? $params["modified"] : time();
		$params["cachedirty"] = 1;
		if (isset($params["metadata"]))
		{
			$this->dequote(&$params['metadata']);
			$params["metadata"] = $this->set_object_metadata(array(
				"oid" => $params["oid"],
				"data" => $params["metadata"],
				"no_write" => 1,
			));
			$params['metadata'] = aw_serialize($params['metadata']);
			$this->quote(&$params['metadata']);
		};
		$q_parts = array(); // siia sisse paneme p�ringu osad
		while(list($k,$v) = each($params)) 
		{
			if ($k != "oid") 
			{
				$q_parts[] = " $k = '$v' ";
			};
		};
		$q = " UPDATE objects SET " . join(",",$q_parts) . " WHERE oid = $params[oid] ";

		$this->db_query($q);
		aw_cache_set("objcache",$params["oid"],"");
		$this->flush_cache();
	}

	////
	// !marks the object $oid as deleted
	// parameters:
	//   oid - the object to delete
	//   class_id - optional, if specified, then the object's class id must match it, otherwise it is not deleted
	function delete_object($oid,$class_id = false, $flush = true)
	{
		if (!$oid)
		{
			$this->raise_error(ERR_CORE_NO_OID,"core::delete_object() - no oid specified",false, true);
		};

		/*		if (!$this->can("delete", $oid))
		{
			$this->raise_error(ERR_ACL_ERR, "core::delete_object() - no can_delete access for $oid!", true, false);
		}*/

		$obj = $this->get_object($oid);
		$this->_log(ST_CORE, SA_DELETE, "$obj[name], id = $oid, class_id = ".$this->cfg['classes'][$obj['class_id']]['name'], $oid);

		$where = " oid = '$oid'";
		if ($class_id)
		{
			$where .= " AND class_id = '$class_id'";
		};
		$q = "UPDATE objects
			SET status = 0,
			    modified = '".time()."',
			    modifiedby = '".aw_global_get("uid")."'
			WHERE $where";
		$this->db_query($q);
		aw_cache_set("objcache",$oid,"");
		if ($flush)
		{
			$this->flush_cache();
		}
	}


	//////////////////////////////////////////////////////////////////////////////////////////////////
	// object getting functions - by id, by alias, etc..
	//////////////////////////////////////////////////////////////////////////////////////////////////

	////
	// !tagastab objekti info aliase kaudu
	// if optional $parent argument is also set, we add that to the WHERE clause
	// of the query (needed for parsing long urls like http://site/kalad/tursk
	// where we have to make sure that "tursk" actually belongs to "kalad"
	function _get_object_by_alias($alias,$parent = 0) 
	{
		// bail out, if $alias doesn't have the value, because otherwise
		// we end up scanning _all_ objects which have no alias and are not deleted
		// for no reason at all. I don't know why this is called with no value
		// but it does sometimes
		$this->quote($alias);
		if (strlen($alias) == 0)
		{
			return false;
		};
		if ($parent)
		{
			$ps = " AND parent = $parent ";
		};
		$site_id = aw_ini_get("site_id");
		$q = "SELECT * FROM objects WHERE alias = '$alias' AND site_id = '$site_id' AND status = 2 $ps";
		$res = $this->db_fetch_row($q);
		// also unserialize metadata
		if ($res)
		{
			$res['meta'] = aw_unserialize($res['metadata']);
		}
		return $res;
	}



	//////////////////////////////////////////////////////////////////////////////////////////////////
	// Objekti metadata handlemine. Seda hoitakse objects tabelis metadata v�ljas serializetud kujul.
	// J�rgnevate funktsioonidega saab selle v�lja sisu k�tte ja muuta

	////
	// !Reads object metadata
	// oid - object's oid
	// key - optional, the key of the metadata that we want, if not set, all metadata is returned
	// metadata - optional, if set, this is used as the string to read the metadata from, not the database
	// no_cache - optional, if oid is set, and this is true, then the object is not read from the cache, but from the db
	// example usage:
	// $data = $this->get_object_metadata(array(
	//					"oid" => "666",
	//					"key" => "notes"));
	function get_object_metadata($args = array())
	{
		$args["oid"] = isset($args["oid"]) ? $args["oid"] : false;
		$args["no_cache"] = isset($args["no_cache"]) ? $args["no_cache"] : false;
		extract($args);
		// if metadata is defined in the arguments, we will not read
		// the object into memory.
		if ($oid)
		{
			if (!($odata = $this->get_object($oid,$no_cache)))
			{
				return false;
			}
			$metadata = $odata['metadata'];
		}

		// load, and parse the information
		$metadata = aw_unserialize($metadata);
		
		// if key is defined, return only that part of the metainfo
		if (isset($key) && $key)
		{	
			if (is_array($metadata) && isset($metadata[$key]))
			{
				$metadata = $metadata[$key];
			}
			else
			{
				$metadata = false;
			}
		}
		// otherwise the whole thing
		return $metadata;
	}

	////
	// !Modifies object's metadata
	// arguments:
	// oid - object oid
	// key - the name of the metadata key
	// value - the value for the specified key (integer, string, array, whatever)
	// data - array, if set, it is merged with the existing object metadata
	// overwrite - optional, if set, then data is not merged, but cleared and then written
	// delete_key - optional, if set, the specified key is deleted
	// example usage:
	// $this->set_object_metadata(array(
	//				"oid" => $oid,
	//				"key" => "notes",
	//				"value" => "3l33t",
	// ));
	// $this->set_object_metadata(array(
	//       "oid" => $oid,
	//       "data" => array("a" => b", "c" => "d")
	// ));
	// $this->set_object_metadata(array(
	//				"oid" => $oid,
	//				"key" => "notes",
	//				"delete_key" => true
	// ));
	function set_object_metadata($args = array())
	{
		extract($args);

		if (not($obj = $this->get_object($oid)))
		{
			// no such object? bail out
			return false;
		};

		$metadata = $obj['meta'];

		if (isset($overwrite))
		{
			if ($key)
			{
				$metadata[$key] = array();
			}
			else
			{
				$metadata = array();
			};
		}
		
		if (is_array($data))
		{
			// no array_merge here, that screws up numeric indexes - instead of overwriting is renumbers and appends them
			if (is_array($metadata))
			{
				$metadata = $data + $metadata;
			}
			else
			{
				$metadata = $data;
			}
		}
		else
		if ($key)
		{
			$metadata[$key] = $value;
		}
		else
		{
			// nothing to do, return
			return false;
		};
		
		if (isset($delete_key))
		{
			unset($metadata[$key]);
		};

		if (!$no_write)
		{
			$_mt = aw_serialize($metadata);
			$this->quote(&$_mt);
			$this->db_query("UPDATE objects SET 
				modifiedby = '".aw_global_get("uid")."',
				modified = '".time()."',
				metadata = '".$_mt."',
				cachedirty = '1'
				WHERE oid = '$oid'
			");
			aw_cache_set("objcache",$oid,'');
			$this->flush_cache();
		}

		return $metadata;
	}

	////
	// !write to syslog. 
	// params:
	// type - int, defined in syslog.types, log entry type (class name/pageview)
	// action - int, defined in syslog.actions, log entry action (add obj, change obj)
	// text - text for log entry
	// oid - object that the action was performed on
	// example usage:
	//   $this->_log(ST_DOCUMENT, SA_ADD, "Added document $name", $docid);
	function _log($type,$action,$text,$oid = 0)
	{
		$this->quote($text);

		$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
		if (!inet::is_ip($ip))
		{
			$ip = aw_global_get("REMOTE_ADDR");
		}
		$t = time();
		$fields = array("tm","uid","type","action","ip","oid","act_id", "referer");
		$values = array($t,aw_global_get("uid"),$type,$text,$ip,$oid,$action,aw_global_get("HTTP_REFERER"));
		if (aw_ini_get("tafkap"))
		{
			$fields[] = "tafkap";
			$values[] = aw_global_get("tafkap");
		};

		if (aw_ini_get("syslog.has_site_id") == 1)
		{
			$fields[] = "site_id";
			$values[] = $this->cfg["site_id"];
		};
		$q = sprintf("INSERT DELAYED INTO syslog (%s) VALUES (%s)",join(",",$fields),join(",",map("'%s'",$values)));
		if (!$this->db_query($q,false))
		{
			die("cannot write to syslog: " . $this->db_last_error["error_string"]);
		};
	}

	//// 
	// !Returns user information
	// parameters:
	//	uid - required, the user to fetch
	//	field - optional, if set, only this field's value is returned, otherwise the whole record
	function get_user($args = false)
	{
		if (!is_array($args))
		{
			$uid = aw_global_get("uid");
		}
		else
		{
			extract($args);
		}
		if ($uid == "")
		{
			return false;
		}
		if (!is_array(($row = aw_cache_get("users_cache",$uid))))
		{
			$q = "SELECT * FROM users WHERE uid = '$uid'";
			$row = $this->db_fetch_row($q);
			aw_cache_set("users_cache",$uid,$row);
		}

		if (isset($field))
		{
			$row = $row[$field];
		}
		else
		{
			if (isset($row))
			{
				// inbox defauldib kodukataloogile, kui seda m��ratud pole
				$row["msg_inbox"] = isset($row["msg_inbox"]) ? $row["msg_inbox"] : $row["home_folder"];
			}
		}
		return $row;
	}

	////
	// !Tagastab formaaditud timestambi
	// Tekalt voiks defineerida mingid konstandid nende numbrite asemele
	// See parandaks loetavust
	function time2date($timestamp = "",$format = 0)
	{
		if ($format != 0)
		{
			$dateformats = $this->cfg["config"]["dateformats"];
			$dateformat = $dateformats[$format];
		}
		else
		{
			$dateformat = $this->cfg["config"]["default_dateformat"];
		}

		return ($timestamp) ? date($dateformat,$timestamp) : date($dateformat);
	}

	////
	// !Tagastab kasutaja login men�� id
	function get_login_menu()
	{
		return $this->login_menu;
	}


	///
	// !Margib koik saidi objektid dirtyks
	function flush_cache()
	{
		$q = "UPDATE objects SET cachedirty = 1, cachedata = ''";
		$this->db_query($q);
	}
		
	////
	// !Tagastab koik mingist nodest allpool olevad objektid
	// seda on mugav kasutada, kui tegemist on suhteliselt
	// vaikse osaga kogu objektihierarhiast, ntx kodukataloog
	// parent(int) - millisest nodest alustame?
	// class(int) - milline klass meid huvitab?
	// type(int) - kui tegemist on men��ga, siis loetakse sisse ainult seda t��pi men��d.
	// active(bool) - kui true, siis tagastakse ainult need objektid, mis on aktiivse
	// orderby(string) - millise v�lja j�rgi tulemus j�rjestada?
	// full(bool) - if true, also recurses to subfolders
	// ret(int) - ARR_NAME or ARR_ALL, default is ARR_ALL
	function get_objects_below($args = array())
	{
		extract($args);
		$this->save_handle();
		$groups = array();

		if (isset($full))
		{
			$this->get_objects_by_class($args + array("class" => CL_PSEUDO));
			while ($row = $this->db_next())
			{
				$ta = $args;
				$ta["parent"] = $row["oid"];
				$tg = $this->get_objects_below($ta);
				foreach($tg as $k => $v)
				{
					$groups[$k] = $v;
				}
			}
		}

		// just pass everything, hopefully wont break anything, but it does kill
		// a bunch of warnings
		$this->get_objects_by_class($args);

		while($row = $this->db_next())
		{
			if (isset($ret) && $ret == ARR_NAME)
			{
				$groups[$row["oid"]] = $row["name"];
			}
			else
			{
				$row["meta"] = $this->get_object_metadata(array(
					"metadata" => $row["metadata"]
				));
				$groups[$row["oid"]] = $row;
			}
		};
		$this->restore_handle();
		return $groups;
	}


	////
	// !returns true if object $oid 's cahe dirty flag is set
	function cache_dirty($oid, $fname = "")
	{
		$q = "SELECT cachedirty,cachedata FROM objects WHERE oid = '$oid'";
		$this->db_query($q);
		$row = $this->db_next();

		if ($fname == "")
		{
			return ($row["cachedirty"] == 1) ? true : false;
		}
		else
		{
			$dat = aw_unserialize($row["cachedata"]);
			return !$dat[$fname];
		}
	}

	////
	// !sets objects $oid's cache dirty flag to false
	function clear_cache($oid, $fname = "")
	{
		$ob = $this->get_object($oid);
		$dat = aw_unserialize($ob["cachedata"]);
		if ($fname != "")
		{
			$dat[$fname] = 1;
		}
		$ds = aw_serialize($dat);
		$this->quote($ds);
		$q = "UPDATE objects SET cachedirty = 0 , cachedata = '$ds' WHERE oid = '$oid'";
		$this->db_query($q);
	}

	////
	// !lisab objektile aliase
	// source on see objekt, mille juurde lingitakse
	// target on see, mida lingitakse
	// aliaste tabelisse paigutame ka klassi id, nii
	// peaks hiljem olema voimalik natuke kiirust gainida

	// positioned arguments suck, add_alias is the Right Way to do this,
	// but I will leave this in place just in case someone still
	// needs it.
	function add_alias($source,$target,$extra = "")
	{
		$this->addalias(array(
			"id" => $source,
			"alias" => $target,
			"extra" => $extra,
		));
	}

	////
	// !$arr must contain 
	// id = alias id
	// target = alias target
	// and may contain 
	// extra = data to write to alias table
	function change_alias($arr) 
	{
		extract($arr);
		$target_data = $this->get_object($target);

		$source = $this->db_fetch_field("SELECT source FROM aliases WHERE id = '$id'", "source");

		$q = "UPDATE aliases SET target = '$target' , type = '$target_data[class_id]' , data = '$extra' 
					WHERE id = '$id'";

		$this->db_query($q);
		$aliasmgr = get_instance("aliasmgr");
		$_aliases = $aliasmgr->cache_oo_aliases($source);

		$this->_log(ST_CORE, SA_CHANGE_ALIAS, "Muutis objekti $source aliast $target", $source);
	}

	////
	// !deletes alias $target from object $source
	function delete_alias($source,$target)
	{
		$q = "DELETE FROM aliases WHERE source = '$source' AND target = '$target'";
		$this->db_query($q);
		$ainst = get_instance("aliasmgr");
		$ainst->cache_oo_aliases($source);
	}

	////
	// !koostab aliaste nimekirja objekti jaoks
	function get_aliases_for($oid,$type = -1,$sortby = "", $order = "",$join = "",$reltype = -1) 
	{
		$ss = $js = $fs = $rs = "";
		if ($type != -1)
		{
			$ss = " AND aliases.type = '$type' ";
		}
		if ($sortby == "")
		{
			$sortby = "id";
		}
		if ($join != "")
		{
			$js = join(' ',map2('LEFT JOIN %s ON %s',$join));
			$fs = ",".join(',',map2('%s.*',$join));
		}
		if ($reltype != -1)
		{
			$rs = " AND aliases.reltype = '$reltype' ";
		};
		$q = "SELECT aliases.*,objects.* $fs FROM aliases
			LEFT JOIN objects ON
			(aliases.target = objects.oid) $js
			WHERE source = '$oid' $ss $rs ORDER BY aliases.id ";
		$this->db_query($q);
		$aliases = array();
		while($row = $this->db_next()) 
		{
			$row["id"] = $row["target"];
			$aliases[]=$row;
		};
		return $aliases;
	}

	////
	// !tagastab koigi objekti juurde kuuluvate aliaste nimekirja. Alternatiiv eelmisele sisuliselt
	// argumendid
	// oid(int) - objekti id, mis meid huvitab
	// type(int or array) - meid huvitavate objektide tyybiidentifikaatorid
	function get_aliases($args = array())
	{
		extract($args);
		
		// map2 supports both arrays and strings and returns array
		$aliases = array();
		// what would you want with oid=0 anyway? but it does get called
		if (empty($oid))
		{
			return $aliases;
		};
		$typestring = "";
		if (isset($type))
		{
			$tlist = join(',',map('%d',$type));
			$typestring = " AND objects.class_id IN ($tlist)";
		};

		$q = "SELECT aliases.*,objects.*
			FROM aliases
			LEFT JOIN objects ON
			(aliases.target = objects.oid)
			WHERE source = '$oid' $typestring
			ORDER BY aliases.id";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			// note that the index inside the idx array is always one less than the 
			// number in the alias. (e.g. oid of #f1# is stored at the position 0, etc)
			if (sizeof($type) == 1)
			{
				$aliases[$row["idx"]-1] = $row;
			}
			else
			{
				$aliases[] = $row;
			};
		};
		
		return $aliases;
	}

	////
	// !returns array of aliases pointing to object $oid
	function get_aliases_of($oid) 
	{
		$q = "SELECT *,objects.name as name,objects.parent as parent FROM aliases
			LEFT JOIN objects ON
			(aliases.source = objects.oid)
			WHERE target = '$oid' ORDER BY id";
		$this->db_query($q);
		$aliases = array();
		while($row = $this->db_next())
		{
			$aliases[]=array(
				"type" => $row["type"],
				"name" => $row["name"], 
				"data" => $row["data"],
				"id" => $row["source"],
				"parent" => $row["parent"]);
		};
		return $aliases;
	}

	////
	// !Deletes all aliases for $oid
	function delete_aliases_of($oid)
	{
		$this->db_query("DELETE FROM aliases WHERE source = $oid");
	}

	////
	// !the base version of per-class alias adding
	// a class can override this, to implement adding aliases differently
	// for instance - when adding an alias to form_entry it lets you pick the output
	// with which the entry is shown. 
	// but basically what this function needs to do, is to call core::add_alias($id,$alias)
	// and finally redirect the user to $this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr")
	// parameters:
	//   id - the id of the object where the alias will be attached
	//   alias - the id of the object to attach as an alias
	//   relobj_id - reference to the relation object
	//   reltype - type of the relation
	function addalias($arr)
	{
		extract($arr);
		$extra = ($arr["extra"]) ? $arr["extra"] : "";

		$target_data = $this->get_object($alias);

		$idx = $this->db_fetch_field("SELECT MAX(idx) as idx FROM aliases WHERE source = '$id' AND type =  '$target_data[class_id]'","idx");
		if ($idx === "")
		{
			$idx = 1;
		}
		else
		{
			$idx++;
		}

		$relobj_id = (int)$arr["relobj_id"];
		$reltype = (int)$arr["reltype"];
		$q = "INSERT INTO aliases (source,target,type,data,idx,relobj_id,reltype)
			VALUES('$id','$alias','$target_data[class_id]','$extra','$idx','$relobj_id','$reltype')";
			
		$cl = $target_data['class_id'];

		if (is_array($hist = aw_global_get('aliasmgr_obj_history')))
		{
			$hist[time()] = $cl;
			array_unique($hist);
			krsort($hist);
			while(count($hist) > 10)
			{
				array_pop($hist);
			}
		}
		else
		{
			$hist = array(time() => $cl);
		}

		aw_session_set('aliasmgr_obj_history',$hist);

		$usr = get_instance('users_user');

		$usr->set_user_config(array(
			'uid' => aw_global_get('uid'),
			'key' => 'aliasmgr_obj_history',
			'value' => $hist
		));

		$this->db_query($q);
		$aliasmgr = get_instance("aliasmgr");
		$aliasmgr->cache_oo_aliases($id);

		$this->_log(ST_CORE, SA_ADD_ALIAS,"Lisas objektile $id aliase $alias", $id);
	}

	////
	// !Registreerib uue aliasteparseri
	// argumendid:
	// class(string) - klassi nimi, passed to classload. May be empty
	// function(string) - function to be called for this alias. May be empty.
	// reg(string) - regulaaravaldis, samal kujul nagu preg_replace esimene argument

	// aw shit, siin ei saa ju rekursiivseid aliaseid kasutada. Hm .. aga kas neid
	// siis yldse kusagil kasutatakse? No ikka .. tabelis voib olla pilt.

	function register_parser($args = array())
	{
		// esimesel kasutamisel loome uue n� dummy objekti, mille sisse
		// edaspidi registreerime koikide parserite callback meetodid
		if (!isset($this->parsers) || !is_object($this->parsers))
		{
			$this->parsers = get_instance("dummy");
			// siia paneme erinevad regulaaravaldised
			$this->parsers->reglist = array();
		};

		extract($args);

		if (isset($class) && isset($function) && $class && $function)
		{
			if (!is_object($this->parsers->$class))
			{
				$this->parsers->$class = get_instance($class);
			};
		
			$block = array(
				"reg" => $reg,
				"class" => $class,
				"parserchain" => array(),
				"function" => $function,
			);
		}
		else
		{
			// kui klassi ja funktsiooni polnud defineeritud, siis j�relikult
			// soovitakse siia alla registreerida n�. sub_parsereid.
			$block = array(
				"reg" => $reg,
				"parserchain" => array(),
			);
		};
		$this->parsers->reglist[] = $block;
		
	
		// tagastab �sja registreeritud parseriobjekti ID nimekirjas
		return sizeof($this->parsers->reglist) - 1;
	}

	////
	// !Registreerib alamparseri
	// argumendid:
	// idx(int) - millise $match array elemendi peale erutuda
	// match(string) - mis peaks elemendi v��rtuses olema, et see v�lja kutsuks
	// reg_id(int) - millise master parseri juurde see registreerida
	// class(string) - klass
	// function(string) - funktsiooni nimi
	function register_sub_parser($args = array())
	{
		extract($args);	
		if (!isset($this->parsers->$class) || !is_object($this->parsers->$class))
		{
			$this->parsers->$class = get_instance($class);
		};

		$block = array(
			"idx" => isset($idx) ? $idx : 0,
			"match" => isset($match) ? $match : 0,
			"class" => $class,
			"function" => $function,
			"reset" => isset($reset) ? $reset : "",
			"templates" => isset($templates) ? $templates : array(),
		);

		$this->parsers->reglist[$reg_id]["parserchain"][] = $block;
	}

	////
	// !Parsib mingi tekstibloki kasutates selleks register_parser ja register_sub_parser funktsioonide abil
	// registreeritud parsereid
	// argumendid:
	// text(string) - tekstiblokk
	// oid(int) - objekti id, mille juurde see kuulub
	function parse_aliases($args = array())
	{
		$this->blocks = array();
		extract($args);
		$meta = $this->get_object_metadata(array(
			"oid" => $oid,
			"key" => "aliaslinks",
		));

		// tuleb siis teha tsykkel yle koigi registreeritud regulaaravaldiste
		// esimese ts�kliga kutsume parserite reset funktioonud v�lja. If any.
		if (!is_array($this->parsers->reglist))
		{
			return;
		}
		foreach($this->parsers->reglist as $pkey => $parser)
		{
			if (sizeof($parser["parserchain"] > 0))
			{
				foreach($parser["parserchain"] as $skey => $sval)
				{
					$cls = $sval["class"];
					$res = $sval["reset"];
					if ($sval["reset"])
					{
						$this->parsers->$cls->$res();
					};
				};
			};
		}

		foreach($this->parsers->reglist as $pkey => $parser)
		{
			// itereerime seni, kuni see �sjaleitud regulaaravaldis enam ei matchi.
			$cnt = 0;
			while(preg_match($parser["reg"],$text,$matches))
			{
				$cnt++;
				if ($cnt > 50)
				{
					return;
				};
				// siia tuleb tekitada mingi if lause, mis 
				// vastavalt sellele kas parserchain on defineeritud voi mitte, kutsub oige asja v�lja
				if (sizeof($parser["parserchain"] > 0))
				{
					foreach($parser["parserchain"] as $skey => $sval)
					{
						$inplace = false;
						if (($matches[$sval["idx"]] == $sval["match"]) || (!$sval["idx"]))
						{
							$cls = $sval["class"];
							$fun = $sval["function"];
							$tpls = array();

							foreach($sval["templates"] as $tpl)
							{
								$tpls[$tpl] = $this->templates[$tpl];
							};

							$params = array(
								"oid" => $oid,
								"idx" => $sval["idx"],
								"matches" => $matches,
								"tpls" => $tpls,
								"meta" => $meta,
							);

							$repl = $this->parsers->$cls->$fun($params);
							
							if (is_array($repl))
							{
								$replacement = $repl["replacement"];
								$inplace = $repl["inplace"];
							}
							else
							{
								$replacement = $repl;
							};

							if (is_array($this->parsers->$cls->blocks))
							{
								$this->blocks = $this->blocks + $this->parsers->$cls->blocks;
							};
						
							if ($inplace)
							{
								$this->vars(array($inplace => $replacement));	
								$inplace = false;
								$text = preg_replace($parser["reg"],"",$text,1);
							}
							else
							{
								$text = preg_replace($parser["reg"],$replacement,$text,1);
							};
							$replacement = "";
						};
					};
				};
			};
		};
		return $text;
	}

	////
	// !lisab objektile yhe vaatamise
	function add_hit($oid) 
	{
		if ($oid) 
		{
			$this->db_query("UPDATE hits SET hits=hits+1 WHERE oid = $oid");
		};
	}

	////
	// !get object's hits
	// $oid =  object id
	function get_hit($oid)
	{
		if ($oid)
		{
		return $this->db_fetch_field("SELECT hits from hits WHERE oid = $oid","hits");
		};
	}
     

	////
	// !imcrements the objects cached hit count by one
	function add_cache_hit($oid) 
	{
		if ($oid) 
		{
			$this->db_query("UPDATE hits SET cachehits=cachehits+1 WHERE oid = $oid");
		};
	}

	////
	// !traverses the object tree from bottom to top and returns an 
	// array of all objects in the path
	// params:
	//	$oid = object to start from
	//	$check_objs = if this is false, then only menu objects are traversed,
	//		if this is true, all objects
	//	$rootobj = the oid of the object where to stop traversing, ie the root of the tree
	function get_object_chain($oid,$check_objs = false, $rootobj = 0) 
	{
		if (!$oid) 
		{
			$int = (int)$oid;
			if (strlen($oid) > strlen($int)) 
			{
				print LC_CORE_NOT_AN_OBJECT;
				die;
			};
		};
		if (aw_cache_get("goc_cache",$oid))
		{
			return aw_cache_get("goc_cache",$oid);
		};
		$parent = $oid;
		$chain = array();
		// um. this is stupid
		while($parent > $rootobj) 
		{
			$row = $this->get_menu($oid);
			if ($check_objs && !$row)
			{
				$row = $this->get_object($oid);
			}

			if (!$row) 
			{
				// kui me parentit ei leia, siis lihtsalt
				// jargmise while tsykli labimisega kukume
				// siit tsyklist v�lja
				$parent = 0;
			} 
			else 
			{
				$parent = $row["parent"];
				if (isset($chain[$row["oid"]]) && is_array($chain[$row["oid"]]))
				{
					// if we have been here before, we have a cyclic path.. baad karma.
					return $chain;
				}
				$chain[$row["oid"]] = $row;
				$oid = $row["parent"];
			};
		};
		aw_cache_set("goc_cache",$oid,$chain);
		return $chain;
	}

	////
	// !New improved version of get_object_chain.
	// oid(int) - millisest objektist parsimist alustada
	// stop(int)(optional) - millise objekti juures parsimine lopetada (kui puudu, siis lopeb
	//   esimese objekti juures, mille parent on 0
	// check(int)(optional)(n/a) - kontrollib objekti id-ga check olemasolu chainis, kui ei leitud,
	//   siis tagastab false.
	// class_id(int)(optional)(n/a) - millise klassi objektid meid huvitavad
	// Damn. the correct way to do this would be to store the parents of each objects by that object
	// that would save us A LOT of queries.
	function get_obj_chain($args = array())
	{
		extract($args);
		$retval = array();
		$object = $this->get_object($oid);	
		if (!$object)
		{
			return false;
		};

		$retval[$object["oid"]] = $object["name"];
	
		$found = $object["parent"];

		if ($stop == $oid)
		{
			$found = false;
		};

		while($found)
		{
			$object = $this->get_object($object["parent"]);
			$found = $object["parent"];
			$retval[$object["oid"]] = $object["name"];
			if ($stop == $object["oid"])
			{
				$found = false;
			};
		};
		return $retval;
	}

	////
	// !returns object specified in $arg
	// if $arg is an array, it can contain $oid and $class_id variables
	// if not, it is used as the $oid variable
	// if $class_id is specified, the returned object is checked to be of that class,
	// if not, error is raised
	// if no_cache is true, the object cache is not used
	// if $unserialize_meta == true, object's metadata will be unserialized to meta field
	function get_object($arg,$no_cache = false,$unserialize_meta = true) 
	{
		if (is_array($arg))
		{
			$oid = $arg["oid"];
			$class_id = isset($arg["class_id"]) ? $arg["class_id"] : false;
			$unserialize_meta = isset($arg["unserialize_meta"]) ? $arg["unserialize_meta"] : true;
		}
		else
		{
			$oid = $arg;
		};

		if (!$oid)
		{
			return false;
		}

		if ($no_cache)
		{
			$_t = $this->db_fetch_row("SELECT * FROM objects WHERE oid = $oid");
			if ($_t["brother_of"] != $_t["oid"] && $_t["brother_of"])
			{
				// brother object, so we gots to read the real objets metadata
				$_tt = $this->db_fetch_row("SELECT * FROM objects WHERE oid = ".$_t["brother_of"]."");
				$_t["metadata"] = $_tt["metadata"];
			}
			if ($unserialize_meta && $_t)
			{
				$_t["meta"] = aw_unserialize($_t["metadata"]);
			}
		}
		else
		{
			if (!($_t = aw_cache_get("objcache",$oid)))
			{
				$_t = $this->db_fetch_row("SELECT * FROM objects WHERE oid = $oid");
				if ($_t["brother_of"] != $_t["oid"] && $_t["brother_of"])
				{
					// brother object, so we gots to read the real objets metadata
					$_tt = $this->db_fetch_row("SELECT * FROM objects WHERE oid = ".$_t["brother_of"]."");
					$_t["metadata"] = $_tt["metadata"];
				}
				if ($unserialize_meta && $_t)
				{
					$_t["meta"] = aw_unserialize($_t["metadata"]);
				}

				aw_cache_set("objcache",$oid,$_t);
			}
		}

		// damn it, promo boxes act as menus too
		if (!empty($class_id) && ($class_id != CL_PSEUDO) && ($_t["class_id"] != $class_id) )
		{
			// objekt on valest klassist
			$this->raise_error(ERR_CORE_WTYPE,"get_object: $oid ei ole t��pi $class_id",true);
		}

		// and also unserialize the object's metadata
		return $_t;
	}

	////
	// !DEPRECATED - get_object pakib ka metadata lahti nyyd
	// Tagastab objekti ja tema lahtiparsitud metainfo
	function get_obj_meta($oid)
	{
		return $this->get_object($oid);
	}
			
	////
	// !tagastab mingisse kindlasse klassi kuuluvad objektid
	// hiljem voib seda funktsiooni t�iendada nii, et ta joinib kylge ka igale klassile vastava tabeli
	// argumendid
	// class (int) - klassi id
	// parent(id/array)(opt) - kui defineeritud, siis loeb ainult objekte selle parenti all
	// active(bool) - kui true, siis tagastame ainult aktiivsed objektid
	// subclass - objects.subclass
	// flags - objects.flags
	// fields(string) - can be used to specify the names of fields you need
	function get_objects_by_class($args = array())
	{
		extract($args);
		// kui parent on antud, siis moodustame sellest IN klausli
		$sqlparts = array();
		if (isset($parent) && is_array($parent))
		{
			$prtstr = join(",",map("'%s'",$parent));
			if ($prtstr != "")
			{
				$sqlparts[] = "parent IN (" . $prtstr . ")";
			}
		}
		else
		if (isset($parent) && $parent)
		{
			$sqlparts[] = "parent = $parent";
		}

		if (isset($status) && $status)
		{
			$sqlparts[] = " status = '$status' ";
		}
		else
		{
			$sqlparts[] = isset($active) ? "status = 2 " : "status != 0 ";
		};

		if (isset($subclass) && $subclass)
		{
			$sqlparts[] = "subclass = '$subclass' ";
		}

		if (isset($flags) && $flags)
		{
			$sqlparts[] = "flags = '$flags' ";
		}
		
		if (isset($name) && $name != "")
		{
			$sqlparts[] = "name LIKE '%$name%' ";
		}

		if (isset($lang_id) && $lang_id)
		{
			if ($class == CL_PSEUDO)
			{
				$sqlparts[] = "(lang_id = $lang_id OR menu.type = ".MN_CLIENT.") ";
			}
			else
			{
				$sqlparts[] = "lang_id = $lang_id ";
			}
		}

		$cl = is_array($class) ? join(",",$class) : $class;
		if ($cl != "")
		{
			$sqlparts[] = "class_id IN ($cl)";
		}
		if (isset($type) && $class == CL_PSEUDO)
		{
			$sqlparts[] = "menu.type = '$type' ";
		}

		$where = join(" AND ", $sqlparts);
		if ($where != "")
		{
			$where = " WHERE ".$where;
		}

		$ostr = isset($orderby) ? " ORDER BY $orderby " : "";

		if (empty($fields))
		{
			$fields = " objects.* ";
		}

		// kui tegemist on men��dega, siis joinime kylge ka menu tabeli
		if ($cl === CL_PSEUDO)
		{
			$q = "SELECT $fields FROM objects LEFT JOIN menu ON (objects.oid = menu.id) $where $ostr";
		}
		else
		{
			$q = "SELECT $fields FROM objects $where $ostr";
		};

		$this->db_query($q);
	}

	////
	// !Eelmise analoog, kuid class_id seekord ignoreeritakse
	function get_objects($args = array())
	{
		extract($args);
		$pstr = " AND parent IN (" . join(",",map("'%s'",$parent)) . ")";
		$cstr = " AND class_id IN (" . join(",",map("'%s'",$class_id)) . ")";
		$this->db_query("SELECT objects.*
					FROM objects
					WHERE status != 0 $pstr $cstr");
	}
	
	////
	// !tagastab array oid => name matchinud objektidest
	// parameetrid on samad, mis get_objects_by_class funxioonil
	// lisaks:
	// addempty - if true, empty element is inserted
	// return - if ARR_ALL, all data about objects is returned, else only name
	function list_objects($arr)
	{
		if (isset($arr["addempty"]))
		{
			$ret = array("" => "");
		}
		else
		{
			$ret = array();
		}
		$this->save_handle();
		$this->get_objects_by_class($arr);
		$parens = array();
		while ($row = $this->db_next())
		{
			$parens[$row['oid']] = $row['parent'];
			if (isset($arr["return"]) && $arr["return"] == ARR_ALL)
			{
				$ret[$row["oid"]] = $row;
			}
			else
			{
				$ret[$row["oid"]] = $row["name"];
			}
		}

		if (isset($arr["add_folders"]) && $arr["add_folders"] == true && $arr["return"] != RET_ALL)
		{
			$_tret = array();
			$ml = $this->get_menu_list();
			foreach($ret as $oid => $name)
			{
				if ($oid)
				{
					$fullname = $ml[$parens[$oid]]."/".$name;
					if (isset($arr["truncate_names"]) && strlen($fullname) > 60)
					{
						$fullname = substr($fullname,0,20) . "..." . substr($fullname,-30);
					};
					$_tret[$oid] = $fullname;
				}
				else
				{
					$_tret[""] = "";
				}
			}
			$ret = $_tret;
		}

		$this->restore_handle();
		return $ret;
	}

	////
	// !loendab mingisse kindlasse klassi kuuluvad objektid mingi parenti all
	// argumendid
	// class (int) - klassi id
	// parent(id)(opt) - kui defineeritud, siis loeb ainult objekte selle parenti all
	function count_objects($args = array())
	{
		extract($args);
		$this->db_query("SELECT COUNT(*) AS cnt
					FROM objects
					WHERE class_id = $class AND status != 0 AND parent = '$parent'");
		$row = $this->db_next();
		return $row["cnt"];
	}

	//// 
	// !Veateade
	// $msg - teate tekst
	// $fatal - katkestada t��?
	// $silent - logida viga, aga j�tkata t��d
	function raise_error($err_type,$msg, $fatal = false, $silent = false, $oid = 0)
	{
		if (aw_global_get("__from_raise_error") > 0)
		{
			return false;
		}
		aw_global_set("__from_raise_error",1);
		$this->errmsg[] = $msg;

		$orig_msg = $msg;
		$is_rpc_call = aw_global_get("__is_rpc_call");
		$rpc_call_type = aw_global_get("__rpc_call_type");

		$this->_log(ST_CORE, SA_RAISE_ERROR, $msg, $oid);

		$msg = "Suhtuge veateadetesse rahulikult!  Te ei ole korda saatnud midagi katastroofilist. Ilmselt juhib programm Teie t�helepanu mingile ebat�psusele  andmetes v�i n�puveale.<Br><br>\n\n".$msg;


		// meilime veateate listi ka
		$subj = "Viga saidil ".$this->cfg["baseurl"];

		if (!$is_rpc_call && !headers_sent())
		{
			header("X-AW-Error: 1");
		}
		$content = "\nVeateade: ".$msg;
		$content.= "\nKood: ".$err_type;
		$content.= "\nfatal = ".($fatal ? "Jah" : "Ei" )."\n";
		$content.= "PHP_SELF = ".aw_global_get("PHP_SELF")."\n";
		$content.= "lang_id = ".aw_global_get("lang_id")."\n";
		$content.= "uid = ".aw_global_get("uid")."\n";
		$content.= "section = ".$GLOBALS["section"]."\n";
		$content.= "url = ".$this->cfg["baseurl"].aw_global_get("REQUEST_URI")."\n-----------------------\n";
		$content.= "is_rpc_call = $is_rpc_call\n";
		$content.= "rpc_call_type = $rpc_call_type\n";
		global $HTTP_COOKIE_VARS;
		$content .= "\n\nCookie vars\n";
		foreach($HTTP_COOKIE_VARS as $k => $v)
		{
			$content.="$k = $v \n";
		}
		global $HTTP_GET_VARS;
		$content .= "\n\nGet vars\n";
		foreach($HTTP_GET_VARS as $k => $v)
		{
			$content.="$k = $v \n";
		}
		global $HTTP_POST_VARS;
		$content .= "\n\nPost vars\n";
		foreach($HTTP_POST_VARS as $k => $v)
		{
			$content.="$k = $v \n";
		}
		$keys = array("DOCUMENT_ROOT","HTTP_ACCEPT_LANGUAGE","HTTP_HOST","HTTP_REFERER","HTTP_USER_AGENT","REMOTE_ADDR",
			"SCRIPT_FILENAME","SCRIPT_URI","SCRIPT_URL","REQUEST_METHOD","QUERY_STRING");
		global $HTTP_SERVER_VARS;
		$content.="\n\nHelpful server vars:\n\n";
		foreach($HTTP_SERVER_VARS as $k => $v)
		{
			if (in_array($k,$keys))
			{
				$content.="$k = $v \n";
			};
		}

		// try to find the user's email;
		$head = "";
		if (($uid = aw_global_get("uid")) != "")
		{
			$udata = $this->get_user(array("uid" => $uid));
			$eml = $udata["email"];
			if ($eml == "")
			{
				$eml = "automatweb@automatweb.com";
			}
			$head="From: $uid<".$eml.">\n";
		}
		else
		{
			$head="From: automatweb@automatweb.com\n";
		};

		$send_mail = true;

		if ($err_type == 30 && strpos($HTTP_SERVER_VARS["HTTP_USER_AGENT"], "Microsoft-WebDAV-MiniRedir") !== false)
		{
			$send_mail = false;
		}

		if ($send_mail)
		{		
			mail("vead@struktuur.ee", $subj, $content,$head);
		}

		// here we replicate the error to the site that logs all errors (usually aw.struktuur.ee)
		// we replicate by POST request, cause this thing can be too long for a GET request
		global $class,$action;

		if (false && (aw_ini_get("bugtrack.report_to_server") == 1) && !($class == "bugtrack" && $action="add_error"))
		{
			// kui viga tuli bugi replikeerimisel, siis 2rme satu l6pmatusse tsyklisse
			$socket = get_instance("socket");
			$socket->open(array(
				"host" => aw_ini_get("config.error_log_site"),
				"port" => 80,
			));

			$req = "class=bugtrack&action=add_error";
			$req.= "&site_url=".urlencode(aw_ini_get("baseurl"));
			$req.= "&err_type=".$err_type;
			$req.= "&err_msg=".urlencode($msg);
			$req.= "&err_uid=".aw_global_get("uid");
			$req.= "&err_content=".urlencode($content);

			$op = "POST http://".aw_ini_get("config.error_log_site")."/reforb.".aw_ini_get("ext")." HTTP/1.0\r\n";
			$op .= "Host: ".aw_ini_get("config.error_log_site")."\r\n";
			$op .= "Content-type: application/x-www-form-urlencoded\r\n";
			$op .= "Content-Length: " . strlen($req) . "\r\n\r\n";
			$socket->write($op);
			$socket->write($req);
			$socket->close();
		}

		if ($silent)
		{
			aw_global_set("__from_raise_error",0);
			return;
		};

		if ($fatal)
		{
			if ($is_rpc_call)
			{
				$driver_inst = get_instance("orb/".$rpc_call_type);
				$driver_inst->handle_error($err_type, $orig_msg);
				die();
			}
			else
			{
				$co = get_instance("config");
				$u = $co->get_simple_config("error_redirect");
				if ($u != "" && aw_global_get("uid") != "kix")
				{
					header("Location: $u");
					die();
				}
				flush();
				die("<br><b>AW_ERROR: $msg</b><br>\n\n<br>");
			}
		};
		aw_global_set("__from_raise_error",0);
	}

	////
	// !finds the lead template for menu $section
	// if the template is not set for this menu, traverses the object tree upwards
	// until it finds a menu for which it is set
	function get_lead_template($section)
	{
		if (aw_cache_get("lead_template_cache",$section) != "")
		{
			$template = aw_cache_get("lead_template_cache",$section);
		}
		else
		{
			do { 
				$section = (int)$section;
				$this->db_query("SELECT template.filename AS filename, objects.parent AS parent,objects.metadata as metadata FROM menu LEFT JOIN template ON template.id = menu.tpl_lead LEFT JOIN objects ON objects.oid = menu.id WHERE menu.id = $section");
				$row = $this->db_next();
				$meta = $this->get_object_metadata(array(
					"metadata" => $row["metadata"]
				));

				if ((int)$meta["template_type"] == TPLTYPE_TPL)
				{
					$template = $row["filename"];
				}
				else
				{
					$template = $meta["ftpl_lead"];
				}
				$section = $row["parent"];
			} while ($template == "" && $section > 1);
			aw_cache_set("lead_template_cache",$section,$template);
		}
		if ($template == "")
		{
			$template = "lead.tpl";
		}
		return $template;
	}

	////
	// !finds the full document template for menu $section
	// if the template is not set for this menu, traverses the object tree upwards 
	// until it finds a menu for which it is set
	function get_long_template($section)
	{
		// chekime et kui ette anti dokument, siis ei hakkax seda menyy tabelist otsima
		$obj = $this->get_object($section);	
		if ($obj["class_id"] == CL_PERIODIC_SECTION || $obj["class_id"] == CL_DOCUMENT)
		{
			$section = $obj["parent"];
		}

		do { 
			$section = (int)$section;
			$this->db_query("SELECT template.filename AS filename, objects.parent AS parent, objects.metadata as metadata FROM menu LEFT JOIN template ON template.id = menu.tpl_view LEFT JOIN objects ON objects.oid = menu.id WHERE menu.id = $section");
			$row = $this->db_next();
			$meta = $this->get_object_metadata(array(
				"metadata" => $row["metadata"]
			));

			if ((int)$meta["template_type"] == TPLTYPE_TPL)
			{
				$template = $row["filename"];
			}
			else
			{
				$template = $meta["ftpl_view"];
			}
			$section = $row["parent"];
		} while ($template == "" && $section > 1);
		if (not($template))
		{
			$template = "plain.tpl";
		};
		return $template;
	}


	////
	// !prints an error message about the fact that the user has no access to do this
	function acl_error($right, $oid)
	{
		if (func_num_args() == 2)
		{
			$right = func_get_arg(0);
			$oid = func_get_arg(1);
			printf(E_ACCESS_DENIED1,"CAN_".$right,$oid);
			mail("vead@struktuur.ee", "ACL error saidil ".aw_ini_get("baseurl"), sprintf(E_ACCESS_DENIED1,"CAN_".$right,$oid));
		}
		else
		{
			printf(E_ACCESS_DENIED2);
			mail("vead@struktuur.ee", "ACL error saidil ".aw_ini_get("baseurl"), sprintf(E_ACCESS_DENIED2));
		};
		die();
	}

	////
	// !returns the default group for the user
	function get_user_group($uid)
	{
		$this->db_query("SELECT * FROM groups WHERE type=1 AND name='$uid'");
		return $this->db_next();
	}

	function req_mk_my_orb_part($k,$v, $sep)
	{
		$stra = array();
		foreach($v as $k2 => $v2)
		{
			if (is_array($v2))
			{
				$stra[] = $this->req_mk_my_orb_part($k."[$k2]", $v2, $sep);
			}
			else
			{
				$stra[] = $k."[$k2]=".$v2;
			}
		}
		$str = join($sep,$stra);
		return $str;
	}

	////
	// !my proposed version of da ORB makah!
	// the idea is this that it determines itself whether we go through the site (index.aw)
	// or the orb (orb.aw) - for the admin interface
	// you can force it to point to the admin interface
	// this function also handles array arguments! 
	// crap, I hate this but I gotta do it - shoulda used array arguments or something -
	// if $use_orb == 1 then the url will go through orb.aw, not index.aw - which means that it will be shown
	// directly, without drawing menus and stuff
	// this function has became such a spaghetti, that it really should be rewritten. --duke
	function mk_my_orb($fun,$arr=array(),$cl_name="",$force_admin = false,$use_orb = array(),$separator = "&")
	{
		//global $awt;
		//$awt->count("mk_my_orb");
		//$awt->start("my_my_orb");
		if ($cl_name == "")
		{
			$cl_name = get_class($this);
		}

		preg_match("/(\w*)$/",$cl_name,$m);
		$cl_name = $m[1];

		// this means it was not passed as an argument
		if (is_array($use_orb))
		{
			if (!(strpos($this->REQUEST_URI,"orb.".$this->cfg["ext"]) === false) &&  strpos($this->REQUEST_URI,"reforb.".$this->cfg["ext"]) === false)
			{
				$use_orb = true;
			}
			else
			{
				$use_orb = false;
			}
		}

		// handle arrays!
		$section = false;
		$ura = array();
		if (aw_global_get("cal") && empty($arr["cal"]))
		{
			$arr["cal"] = aw_global_get("cal");
		};
		if (aw_global_get("date") && empty($arr["date"]))
		{
			$arr["date"] = aw_global_get("date");
		};
		foreach($arr as $k => $v)
		{
			if (is_array($v))
			{
				$str = $this->req_mk_my_orb_part($k,$v, $separator);
			}
			else
			{
				# this gets special handling
				if ( ($k == "_alias") || (strlen($v) == 0) )
				{
					$str = "";
				}
				else
				{
					$str = $k."=".$v;
				};
			}

			if ($str)
			{
				if ($k != "section")
				{
					$ura[] = $str;
				}
				else
				{
					$section = $v;
				}
			};
		}
		/*
		print "<pre>";
		print_r($ura);
		print "</pre>";
		*/
		$urs = join($separator,$ura);

		$sec = "";
		if ($section)
		{
			$sec = "section=".$section.$separator;
		}

		$qm = "?";
		if ($separator == "/")
		{
			$qm = "/";
		}
		// now figure out if we are in the admin interface.
		// how do we do that? easy :) we check the url for $baseurl/automatweb :)
		// aga mis siis, kui me mingil hetkel tahame, et automatweb oleks teisel
		// url-il? Ntx www.kirjastus.ee/pk/automatweb juures see ei t��ta. - duke
		if ((stristr($this->REQUEST_URI,"/automatweb")!=false) || $force_admin)
		{
			$ret =  $this->cfg["baseurl"]."/automatweb/orb.".$this->cfg["ext"].$qm.$sec."class=$cl_name".$separator."action=$fun".$separator."$urs";
		}
		else
		{
			$uo = "/".$qm;
			if ($use_orb)
			{
				$uo = "/orb.".$this->cfg["ext"].$qm;
			}
			// user side
			if (isset($arr["_alias"]) && $arr["_alias"])
			{
				$ret = $this->cfg["baseurl"].$uo.$sec."alias=$arr[_alias]".$separator."action=$fun".$separator."$urs";
			}
			else
			{
				$ret = $this->cfg["baseurl"].$uo.$sec."class=$cl_name".$separator."action=$fun".$separator."$urs";
			};
		}
		//$awt->stop("my_my_orb");
		return $ret;
	}

	////
	// !old version of orb url maker, use mk_my_orb instead
	// kui user = 1, siis suunatakse tagasi Saidi sisse. Now, I do realize that this is not
	// the best solution, but for now, it works

	// Kas seda kasutatkse veel? - duke
	// well, kasuttakse, aga tegelt ei tohiks. ysnaga, DEPRECATEED - terryf
	function mk_orb($fun,$arr, $cl_name = "",$user = "")
	{
		return $this->mk_my_orb($fun,$arr,$cl_name);
	}

	////
	// !reforbi koostamine
	// ei, ma ei ytleks, et see deprecated on. See on lihtsalt alternatiivne l�henemine
	// reforbi koostamisele, kusjuures ta on v�ga liberaalne parameetrite suhtes.
	// eelmise analoog. Parameetrid votab arrayst
	function mk_site_orb($args = array())
	{
		extract($args);
		if (!isset($class))
		{
			$args["class"] = get_class($this);
		};
		$arguments = join("&",map2("%s=%s",$args));
		$retval = $this->cfg["baseurl"] . "/?" . $arguments;
		return $retval;
	}

	function req_array_reforb($k, $v)
	{
		$ret = "";
		foreach($v as $_k => $_v)
		{
			if (is_array($_v))
			{
				$ret .= $this->req_array_reforb($k."[".$_k."]",$_v);
			}
			else
			{
				$ret .= "<input type='hidden' name='".$k."[".$_k."]' value=\"".str_replace("\"","&amp;",$_v)."\" />\n";
			}
		}
		return $ret;
	}

	////
	// !creates the necessary hidden elements to put in a form that tell the orb which function to call
	function mk_reforb($fun,$arr = array(),$cl_name = "")
	{
		if ($cl_name == "")
		{
			$cl_name = get_class($this);
		}
		preg_match("/(\w*)$/",$cl_name,$m);
		$cl_name = $m[1];

		$urs = "";
		if (aw_global_get("cal") && empty($arr["cal"]))
		{
			$arr["cal"] = aw_global_get("cal");
		};
		if (aw_global_get("date") && empty($arr["date"]))
		{
			$arr["date"] = aw_global_get("date");
		};
		$tmp = new aw_array($arr);
		foreach($tmp->get() as $k => $v)
		{
			if (is_array($v))
			{
				$urs .= $this->req_array_reforb($k, $v);
			}
			else
			{
				$urs .= "<input type='hidden' name='$k' value=\"".str_replace("\"","&amp;",$v)."\" />\n";
			}
		}
		if (!isset($arr["no_reforb"]) || $arr["no_reforb"] != true)
		{
			$url = "\n<input type='hidden' name='reforb' value='1' />\n";
		}
		$url .= "<input type='hidden' name='class' value='$cl_name' />\n<input type='hidden' name='action' value='$fun' />\n".$urs;
		return $url;
	}

	////
	// Tekitab lingi. duh. 
	// params(array) - key/value paarid elementidest, mida lingi tekitamisel kasutada
	// skip_empty makes us ignore keys with no values
	function mk_link($args = array(),$skip_empty = false)
	{
		$retval = array();
		foreach($args as $key => $val)
		{
			if ($val)
			{
				$retval[] = "$key=$val";
			}
			elseif (!$skip_empty)
			{
				$retval[] = $key;
			};
		};
		return join("/",$retval);
	}

	////
	// !creates a list of menus above $parent and appends $text and assigns it to the correct variable
	function mk_path($oid,$text = "",$period = 0)
	{
		$path = "";
		$ch = $this->get_object_chain($oid);
		reset($ch);
		$use = true;
		while (list(,$row) = each($ch))
		{
			if ($row["oid"] == $this->cfg["admin_rootmenu2"])
			{
				$use = false;
			}
			if ($use)
			{
				$name = ($row["name"]) ? $row["name"] : "(nimetu)";
				$path="<a href='".$this->mk_my_orb("right_frame", array("parent" => $row["oid"],"period" => $period),"admin_menus")."'>".$name."</a> / ".$path;
			}
		}

		$GLOBALS["site_title"] = $path.$text;
		// find the bit after / in text
		$sps = strrpos(strip_tags($text),"/");
		if ($sps > 0)
		{
			$sps++;
		}

		aw_global_set("title_action", substr(strip_tags($text),$sps));
		return $path;
	}

	////
	// !Next generation mk_path. Knows how to display a different path if 
	// the page is shown inside the alias manager
	function gen_path($args = array())
	{
		extract($args);

		// if alias_to is set, then we need to shown the PATH in form
		// go back / change object
		if ($alias_to)
		{
			$path = sprintf("<a href='%s'>%s</a> / ",$return_url,$return_cap);
		}	
		else
		{
			$path = "";
			$ch = $this->get_object_chain($oid);
			reset($ch);
			$use = true;
			while (list(,$row) = each($ch))
			{
				if ($row["oid"] == $this->cfg["admin_rootmenu2"])
				{
					$use = false;
				}
				if ($use)
				{
					$name = ($row["name"]) ? $row["name"] : "(nimetu)";
					$path="<a href='".$this->mk_my_orb("obj_list", array("parent" => $row["oid"],"period" => $period),"menuedit")."'>".$name."</a> / ".$path;
				}
			}
		};

		$path .= strip_tags($caption);
		$GLOBALS["site_title"] = $path;
		return $path;

	}

	////
	// !fread wrapper
	// hiljem voib siia turvakontrolli kylge ehitada
	// See funktsioon EI ANNA veateadet, kui faili ei leitud
	// caller peab seda ise kontrollima. (result == false)
	function get_file($arr)
	{
		if (!$arr["file"])
		{
			$this->raise_error(ERR_CORE_NOFILE,LC_CORE_GET_FILE_NO_NAME,true);
		};
		if ( not(is_file($arr["file"])) || not(is_readable($arr["file"])) )
		{
			$retval = false;
		}
		else
		if (!($fh = @fopen($arr["file"],"r")))
		{
			$retval = false;
			#$this->raise_error("Couldn't open file '$arr[file]'",true);
		}
		else
		{
			$retval = fread($fh,filesize($arr["file"])); // SLURP
			fclose($fh);
		};
		return $retval;
	}

	////
	// !fwrite wrapper - args:
	// file - file name to write
	// content - file contents to write
	function put_file($arr)
	{
		if (not($arr["file"]))
		{
			$this->raise_error(ERR_CORE_NOFILENAME,LC_CORE_PUT_FILE_NO_NAME,true);
		};

		$file = $arr["file"];


		// jama on selles, et "w" modes avamine truncateb olemasoleva faili,
		// ja sellest voib tekkida jamasid... see, et mitu inimest korraga sama
		// faili kirjutavad, peaks olema suht v�ikese t�en�osusega s�ndmus, sest
		// �ldjuhul me kasutame random nimedega faile.
		if (not(($fh = fopen($file,"w"))))
		{
			$this->raise_error(ERR_CORE_NOP_OPEN_FILE,"Couldn't open file '$file' for writing",true);
		};
		fwrite($fh,$arr["content"]);
		fclose($fh);
		// actually this should return a boolean value and an error message should
		// be stored somewhere inside the class.
		return true;
	}

	////
	// !Retrieves a list of files in a directory
	// dir - full path to directory
	// XXX: add security checks
	function get_directory($args = array())
	{
		extract($args);
		// Directory Handle
		$files = array();
		if ($DH = @opendir($dir)) {
			while (false !== ($file = readdir($DH))) { 
				$fn = $dir . "/" . $file;
				if (is_file($fn))
				{
					$files[$file] = $file;
				};
			}
			closedir($DH);
		}
		return $files;
	}

	////
	// !converts all characters of string $str to their hex representation and returns the resulting string
	function binhex($str)
	{
		$l = strlen($str);
		$ret = "";
		for ($i=0; $i < $l; $i++)
		{
			$v = ord($str[$i]);
			if ($v < 16)
			{
				$ret.= "0".dechex($v);
			}
			else
			{
				$ret.= dechex($v);
			};
		}
		return $ret;
	}

	////
	// !opposite of binhex, decodes a string of hex numbers to their values and creates a string from them
	function hexbin($str)
	{
		$l = strlen($str);
		$ret = "";
		for ($i=0; $i < $l; $i+=2)
		{
			$ret.= chr(hexdec($str[$i].$str[$i+1]));
		};
		return $ret;
	}

	////
	// !serializemise funktsioonid. 
	// need on siin sellex et kui objekt ei implemendi serializemist, siis errorit ei tulex. 
	function _serialize($arr)
	{
		return false;
	}

	function _unserialize($arr)
	{
		return false;
	}

	////
	// !creates a string representation of object
	// $arr["oid"] = object's id
	function serialize($arr)
	{
		extract($arr);
		$obj = $this->get_object($oid);

		$v = $this->cfg["classes"][$obj["class_id"]];
		if (!is_array($v))
		{
			return false;
		}

		$t = get_instance($v['alias_class'] != '' ? $v['alias_class'] : $v['file']);
		$s = $t->_serialize($arr);
		if (!$s)
		{
			return false;
		}

		$str = array("class_id" => $obj["class_id"], "str" => $s);

		return serialize($str);
	}

	////
	// !creates an object based on the string representation created by serialize()
	// $arr["str"] = string
	// $arr["parent"] = new object's parent
	// $arr["period"] = new object's period
	function unserialize($arr)
	{
		extract($arr);

		$s = unserialize($str);
		if (!is_array($s))
		{
			return false;
		}

		$v = $this->cfg["classes"][$s["class_id"]];
		if (!is_array($v))
		{
			return false;
		}

		$t = get_instance($v['alias_class'] != '' ? $v['alias_class'] : $v['file']);
		return $t->_unserialize(array("str" => $s["str"], "parent" => $parent, "period" => $period));
	}
	
	////
	// !Fetches and caches information about a menu
	function get_menu($id)
	{
		if (aw_cache_get("gm_cache",$id))
		{
			return aw_cache_get("gm_cache",$id);
		}
		else
		{
			$q = "SELECT menu.*,objects.* FROM menu LEFT join objects on (menu.id = objects.oid) WHERE id = '$id'";
			$this->db_query($q);
			$row = $this->db_fetch_row();
			if (is_array($row))
			{
				$row["meta"] = aw_unserialize($row["metadata"]);
			}
			aw_cache_set("gm_cache",$id,$row);
			return $row;
		};
	}

	////
	// !this should be called from the constructor of each class
	function init($args = false)
	{
		parent::init($args);
		if (is_array($args) && isset($args["clid"]))
		{
			$this->clid = $args["clid"];
		}
	}

	////
	// !loads localization constans and imports them to the current class, vars are assumed to be in array $arr_name
	function lc_load($file,$arr_name,$lang_id = "")
	{
		if (empty($lang_id))
		{
			$admin_lang_lc = aw_global_get("admin_lang_lc");
		}
		else
		{
			$admin_lang_lc = $lang_id;
		};
			
		if (!$admin_lang_lc)
		{
			$admin_lang_lc = "et";
		}

		// for better debugging
		$fullpath = $this->cfg["basedir"]."/lang/" . $admin_lang_lc . "/$file.".$this->cfg["ext"];
		@include_once($fullpath);

		if (is_array($GLOBALS[$arr_name]))
		{
			$this->vars($GLOBALS[$arr_name]);
		}
	}

	////
	// !generates a simple one-level menu from the given data structure - the active item is determined by orb action
	function do_menu($items)
	{
		global $action;
		$im = "";
		foreach($items as $iid => $idata)
		{
			$this->vars(array(
				"url"	=> $idata["url"],
				"text" => $idata["name"]
			));
			if ($action == $iid)
			{
				$im.=$this->parse("SEL_ITEM");
			}
			else
			{
				$im.=$this->parse("ITEM");
			}
		}
		$this->vars(array(
			"ITEM" => $im,
			"SEL_ITEM" => ""
		));
		return $this->parse();
	}
	
	////
	// !teeb objektide nimekirja ja tagastab selle arrays, sobiv picker() funxioonile ette andmisex
	// ignore_langmenus = kui sait on mitme keelne ja on const.aw sees on $lang_menus = true kribatud
	// siis kui see parameeter on false siis loetaxe aint aktiivse kelle menyyd

	// empty - kui see on true, siis pannaxe k6ige esimesex arrays tyhi element
	// (see on muiltiple select boxide jaoks abix)

	// rootobj - mis objektist alustame
	function get_menu_list($ignore_langmenus = false,$empty = false,$rootobj = -1) 
	{
		$admin_rootmenu = $this->cfg["admin_rootmenu2"];

		$cf_name = "objects::get_list::ign::".((int)$ignore_langmenus)."::empty::".((int)$empty)."::rootobj::".$rootobj;
		$cf_name.= "::adminroot::".$admin_rootmenu."::uid::".aw_global_get("uid");

		if (!$ignore_langmenus)
		{
			if ($this->cfg["lang_menus"] == 1)
			{
				$aa = " AND (objects.lang_id = ".aw_global_get("lang_id")." OR menu.type = 69)";
				$cf_name.="::lm::1::lang_id::".aw_global_get("lang_id");
			}
		}

		// 1st memory cache
		if (($ret = aw_global_get($cf_name)))
		{
			return $ret;
		}

		// then disk cache
		$cache = get_instance("cache");
		if (($cont = $cache->file_get($cf_name)))
		{
			$dat = aw_unserialize($cont);
			aw_global_set($cf_name, $dat);
			return $dat;
		}

		$x_mar = array();

		// and finally, the database
		$this->db_query("SELECT objects.oid as oid, 
														objects.parent as parent,
														objects.name as name
											FROM objects 
											LEFT JOIN menu ON menu.id = objects.oid
											WHERE objects.class_id = 1 AND objects.status != 0 $aa
											GROUP BY objects.oid
											ORDER BY objects.parent,jrk");
		while ($row = $this->db_next())
		{
			if ($this->can("view", $row['oid']))
			{
				$ret[$row["parent"]][] = $row;
			}
			else
			{
				$x_mar[$row['oid']] = $row;
			}
		}

		if ($rootobj == -1)
		{
			$rootobj = $admin_rootmenu;
		}

		// here we will make the parent of all objects that don't have parents in the tree,
		// but have them in the excluded list, to be the root
		// why is this? well, because then the folders that are somewhere deep in the tree and that the user
		// has can_view acces for, but not their parent folders, can still see them
		foreach($ret as $prnt => $data)
		{
			foreach($data as $d_idx => $d_row)
			{
				if (isset($x_mar[$d_row["parent"]]))
				{
					if ($d_row["oid"] != $admin_rootmenu)
					{
						$d_row["parent"] = $admin_rootmenu;
						$ret[$admin_rootmenu][] = $d_row;
					}
				}
			}
		}

		$tt = array();
		if ($empty)
		{
			$tt[] = "";
		}
		$this->_mkah_used = array();
		$this->mkah(&$ret,&$tt,$rootobj,"");

		if ($rootobj == $admin_rootmenu)
		{
			$hf = $this->db_fetch_field("SELECT home_folder FROM users WHERE uid = '".aw_global_get("uid")."'","home_folder");
			$hf_name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = '$hf'","name");
			// but we must also add the home folder itself!
			$tt[$hf] = $hf_name;
			$this->mkah(&$ret,&$tt,$hf,aw_global_get("uid"));
		}

		$cache->file_set($cf_name,aw_serialize($tt));
		aw_global_set($cf_name, $tt);

		return $tt;
	}

	function mkah(&$arr, &$ret,$parent,$prefix)
	{
		if (!is_array($arr[$parent]))
		{
			return;
    		}
		$this->_mkah_used[$parent] = true;

    		reset($arr[$parent]);
    		while (list(,$v) = each($arr[$parent]))
    		{
			$name = $prefix == "" ? $v["name"] : $prefix."/".$v["name"];
			$ret[$v["oid"]] = $name;
			if (!$this->_mkah_used[$v["oid"]])
			{
				$this->mkah(&$arr,&$ret,$v["oid"],$name);
			}
		}
	}

	////
	// !executes an orb function call and returns the data that the function returns
	// params:
	// required:
	//	action - orb action to exec
	// optional
	//  class - class for the action - default the current class
	//  params - params to the action 
	//  method - the method to use when doing the function call - possible values: local / xmlrpc / (soap - not implemented yet) 
	//  server - if doing a rpc call, the server where to connect
	//  login_obj - if we must log in to a serverm the id of the CL_AW_LOGIN that will be used to login to the server
	//              if this is set, then server will be ignored
	function do_orb_method_call($arr)
	{
		extract($arr);

		if (!$arr["class"])
		{
			$arr["class"] = get_class($this);
		}
		
		classload("orb");
		$ob = new new_orb;
		return $ob->do_method_call($arr);
	}


        function obj_get_meta($args = array())
        {
                extract($args);
                classload("php");
                if (not($oid) && (strlen($meta) == 0))
                {
                        return false;
                };
                $php_ser = new php_serializer();
                if ($args["oid"])
                {
                        $q = "SELECT meta FROM objects WHERE oid = $oid";
                        $this->db_query($q);
                        $row = $this->db_next();
                        $meta = $row["meta"];
                }
                else
                {
                        $meta = $args["meta"];
                };
                $retval = $php_ser->php_unserialize($meta);
                return $retval;
        }


        function obj_set_meta($args = array())
        {
                extract($args);
                $old = $this->obj_get_meta(array("oid" => $oid));
                $old = array_merge($old,$args["meta"]);
                $ser = aw_serialize($old);
                $this->quote($ser);
                $q = "UPDATE objects SET meta = '$ser' WHERE oid = $oid";
                $this->db_query($q);
        }

	////
	// !this takes an array and goes through it and makes another array that has as keys the values of the given array and also
	// tha velues of the given array
	function make_keys($arr)
	{
		$ret = array();
		if (is_array($arr))
		{
			foreach($arr as $v)
			{
				$ret[$v] = $v;
			}
		}
		return $ret;
	}
	
	function parse_alias($args)
	{
		extract($args);
		if (isset($alias['target']))
		{
			return $this->show(array('id' => $alias['target']));
		}
		else
		{
			return '';
		}
	}

	function show($args)
	{
		$obj = $this->get_object($args['id']);
		return $obj['name'];
	}
};
?>
