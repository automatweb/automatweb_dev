<?php
// $Header: /home/cvs/automatweb_dev/classes/core.aw,v 2.265 2004/06/11 09:18:30 kristo Exp $
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

classload("acl_base");
class core extends acl_base
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

		// objektitabeli väljad
		// dummy on selleks, et array_flip juures parenti väärtuseks "0" ei tuleks
		$_ofields = array("dummy","parent","name","createdby","class_id","created","modified",
				"status","hits","lang_id","comment","last","modifiedby",
				"jrk","period","alias","periodic","site_id","activate_at",
				"deactivate_at","autoactivate","autodeactivate","brother_of",
				"cache_dirty","metadata","subclass","flags"
			);

		// vahetame key=>value paarid ära
		$ofields = array_flip($_ofields);

		// check whether to flush the cache
		$no_flush = $arr["no_flush"];
		unset($arr["no_flush"]);
		
		// oid voib tulla naiteks serializetud objektist
		// anyhow, meil siin pole sellega midagi peale hakata, so nil.
		unset($arr["oid"]);

		// Nende väärtustega kirjutame üle whatever asjad $arr sees olid
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
		$q_parts = array(); // siia sisse paneme päringu osad
		while(list($k,$v) = each($params)) 
		{
			if ($k != "oid" && $k != "no_flush") 
			{
				$q_parts[] = " $k = '$v' ";
			};
		};
		$q = " UPDATE objects SET " . join(",",$q_parts) . " WHERE oid = $params[oid] ";

		$this->db_query($q);
		aw_cache_set("objcache",$params["oid"],"");
		$this->flush_cache();
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////
	// Objekti metadata handlemine. Seda hoitakse objects tabelis metadata väljas serializetud kujul.
	// Järgnevate funktsioonidega saab selle välja sisu kätte ja muuta

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
		if (empty($this->dc))
		{
			print "SYSLOG: $text\n";
		}
		else
		{
			$this->quote($text);

			$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
			if (!inet::is_ip($ip))
			{
				$ip = aw_global_get("REMOTE_ADDR");
			}
			$t = time();
			$this->quote(&$text);
			$this->quote(&$oid);
			$this->quote(&$type);
			$ref = aw_global_get("HTTP_REFERER");
			$this->quote(&$ref);
			$fields = array("tm","uid","type","action","ip","oid","act_id", "referer");
			$values = array($t,aw_global_get("uid"),$type,$text,$ip,$oid,$action,$ref);
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

			if (aw_ini_get("syslog.has_lang_id") == 1)
			{
				$fields[] = "lang_id";
				$values[] = aw_global_get("lang_id");
			};
			$q = sprintf("INSERT DELAYED INTO syslog (%s) VALUES (%s)",join(",",$fields),join(",",map("'%s'",$values)));
			if (!$this->db_query($q,false))
			{
				die("cannot write to syslog: " . $this->db_last_error["error_string"]);
			};
		}
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
				// inbox defauldib kodukataloogile, kui seda määratud pole
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

	///
	// !Margib koik saidi objektid dirtyks
	function flush_cache()
	{
		if (aw_global_get("old_cache_flushed"))
		{
			return;
		}
		aw_global_set("old_cache_flushed", 1);
		$q = "UPDATE objects SET cachedirty = 1, cachedata = '' where status != 0";
		$this->db_query($q);
	}
		
	////
	// !Tagastab koik mingist nodest allpool olevad objektid
	// seda on mugav kasutada, kui tegemist on suhteliselt
	// vaikse osaga kogu objektihierarhiast, ntx kodukataloog
	// parent(int) - millisest nodest alustame?
	// class(int) - milline klass meid huvitab?
	// type(int) - kui tegemist on menüüga, siis loetakse sisse ainult seda tüüpi menüüd.
	// status - only objects of this status are returned if set
	// orderby(string) - millise välja järgi tulemus järjestada?
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
				$row["meta"] = aw_unserialize($row["metadata"]);
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
			if ($GLOBALS["INTENSE_CACHE"] == 1)
			{
				echo "cache_dirty no fname , ret = ".$row["cachedirty"]." <br>";
			}
			return ($row["cachedirty"] == 1) ? true : false;
		}
		else
		{
			$dat = aw_unserialize($row["cachedata"]);
			if ($GLOBALS["INTENSE_CACHE"] == 1)
			{
				echo "cache_dirty oid = $oid fname = $fname , dat = ".dbg::dump($dat)." retv = ".dbg::dump(!$dat[$fname])." <br>";
			}
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
			if ($GLOBALS["INTENSE_CACHE"] == 1)
			{
				echo "clear_cache oid = $oid fname = $fname , dat = ".dbg::dump($dat)." <br>";
			}
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
		return $this->addalias(array(
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

		$this->_log(ST_CORE, SA_CHANGE_ALIAS, "Muutis objekti $source aliast $target", $source);
	}

	////
	// !deletes alias $target from object $source
	function delete_alias($source,$target, $no_cache = false, $no_callback = false)
	{
		$q = "DELETE FROM aliases WHERE source = '$source' AND target = '$target'";
		$this->db_query($q);
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
	// reltype - optional - relation type
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

		if ($reltype)
		{
			$reltypestr = " aliases.reltype = '$reltype' ";
		}
		$q = "SELECT aliases.*,objects.*
			FROM aliases
			LEFT JOIN objects ON
			(aliases.target = objects.oid)
			WHERE source = '$oid' $typestring $reltstr
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
	function get_aliases_of($args = array()) 
	{
		extract($args);
		$rl = "";
		if (!empty($reltype))
		{
			$rl = " AND reltype = $reltype ";
		};
		if (!empty($lang_id))
		{
			$ll = " AND lang_id = $lang_id ";
		};
		$q = "SELECT *,objects.name as name,objects.parent as parent FROM aliases
			LEFT JOIN objects ON
			(aliases.source = objects.oid)
			WHERE target = '$oid' $rl $ll ORDER BY id";
		$this->db_query($q);
		$aliases = array();
		while($row = $this->db_next())
		{
			$aliases[$row["source"]]=array(
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
	//   no_cache - if true, cache is not updated
	//   add_obj_type_history - if set, save object type in session for use in aliasmgr obj type listbox
	function addalias($arr)
	{
		//arr($arr);
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

		// aliasmgr object type history
		if (isset($add_obj_type_history))
		{		
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
		}

		$this->db_query($q);

		$ret = $this->db_last_insert_id();

		$this->_log(ST_CORE, SA_ADD_ALIAS,"Lisas objektile $id aliase $alias", $id);

		return $ret;
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
		// esimesel kasutamisel loome uue nö dummy objekti, mille sisse
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
			// kui klassi ja funktsiooni polnud defineeritud, siis järelikult
			// soovitakse siia alla registreerida nö. sub_parsereid.
			$block = array(
				"reg" => $reg,
				"parserchain" => array(),
			);
		};
		$this->parsers->reglist[] = $block;
		
	
		// tagastab äsja registreeritud parseriobjekti ID nimekirjas
		return sizeof($this->parsers->reglist) - 1;
	}

	////
	// !Registreerib alamparseri
	// argumendid:
	// idx(int) - millise $match array elemendi peale erutuda
	// match(string) - mis peaks elemendi väärtuses olema, et see välja kutsuks
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
		$o = obj($oid);
		$meta = $o->meta();

		// tuleb siis teha tsykkel yle koigi registreeritud regulaaravaldiste
		// esimese tsükliga kutsume parserite reset funktioonud välja. If any.
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
			// itereerime seni, kuni see äsjaleitud regulaaravaldis enam ei matchi.
			$cnt = 0;
			while(preg_match($parser["reg"],$text,$matches))
			{
				$cnt++;
				if ($cnt > 50)
				{
					return;
				};
				// siia tuleb tekitada mingi if lause, mis 
				// vastavalt sellele kas parserchain on defineeritud voi mitte, kutsub oige asja välja
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
				// siit tsyklist välja
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

		list($oid) = explode(":", $oid);

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
			$this->raise_error(ERR_CORE_WTYPE,"get_object: $oid ei ole tüüpi $class_id",true);
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
	// hiljem voib seda funktsiooni täiendada nii, et ta joinib kylge ka igale klassile vastava tabeli
	// argumendid
	// class (int/array) - class id
	// parent(id/array)(opt) - kui defineeritud, siis loeb ainult objekte selle parenti all
	// status(bool) - if set, then only objects of this status are returned
	// subclass - objects.subclass
	// flags - objects.flags
	// fields(string) - can be used to specify the names of fields you need
	// orderby - if set, object list is ordered by this
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

		// kui tegemist on menüüdega, siis joinime kylge ka menu tabeli
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
	// !tagastab array oid => name matchinud objektidest
	// parameetrid on samad, mis get_objects_by_class funxioonil
	// lisaks:
	// addempty - if true, empty element is inserted
	// return - if ARR_ALL, all data about objects is returned, else only name
	// add_folders - if true, then folder names are added to objects
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
		if (isset($arr["return"]) && $arr["return"] == ARR_ALL)
		{
			while ($row = $this->db_next())
			{
				$parens[$row['oid']] = $row['parent'];
				$ret[$row["oid"]] = $row;
			}
		}
		else
		{
			while ($row = $this->db_next())
			{
				$parens[$row['oid']] = $row['parent'];
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
	// !Veateade
	// $msg - teate tekst
	// $fatal - katkestada töö?
	// $silent - logida viga, aga jätkata tööd
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

		$msg = "Suhtuge veateadetesse rahulikult!  Te ei ole korda saatnud midagi katastroofilist. Ilmselt juhib programm Teie tähelepanu mingile ebatäpsusele  andmetes või näpuveale.<br /><br />\n\n".$msg." </b>";

		// also attach backtrace
		if (function_exists("debug_backtrace"))
		{
			$msg .= dbg::process_backtrace(debug_backtrace());
		}

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
		//	if (in_array($k,$keys))
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

		if ($err_type == 30)
		{
			if (count($HTTP_GET_VARS) < 1 && count($HTTP_POST_VARS) < 1)
			{
				$send_mail = false;
			}
		}

		if ($send_mail)
		{		
			send_mail("vead@struktuur.ee", $subj, $content,$head);
		}

		// here we replicate the error to the site that logs all errors (usually aw.struktuur.ee)
		// we replicate by POST request, cause this thing can be too long for a GET request
		global $class,$action;

		if (!($class == "bugtrack" && $action="add_error"))
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
				$la = get_instance("languages");
				$ld = $la->fetch(aw_global_get("lang_id"));
				$u = $co->get_simple_config("error_redirect_".$ld["acceptlang"]);
				if (!$u)
				{
					$u = $co->get_simple_config("error_redirect");
				}

				if ($u != "" && aw_global_get("uid") != "kix" && aw_global_get("uid") != "duke" && !headers_sent() && aw_ini_get("site_id") != 138)
				{
					header("Location: $u");
					die();
				}
				flush();
				die("<br /><b>AW_ERROR: $msg</b><br />\n\n<br />");
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
		$tplmgr = get_instance("templatemgr");
		return $tplmgr->get_lead_template($section);
	}

	////
	// !finds the full document template for menu $section
	// if the template is not set for this menu, traverses the object tree upwards 
	// until it finds a menu for which it is set
	function get_long_template($section)
	{
		$tplmgr = get_instance("templatemgr");
		return $tplmgr->get_long_template($section);
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
			send_mail("vead@struktuur.ee", "ACL error saidil ".aw_ini_get("baseurl"), sprintf(E_ACCESS_DENIED1,"CAN_".$right,$oid));
		}
		else
		{
			printf(E_ACCESS_DENIED2);
			send_mail("vead@struktuur.ee", "ACL error saidil ".aw_ini_get("baseurl"), sprintf(E_ACCESS_DENIED2));
		};
		die();
	}

	////
	// !orb link maker
	// the idea is this that it determines itself whether we go through the site (index.aw)
	// or the orb (orb.aw) - for the admin interface
	// you can force it to point to the admin interface
	// this function also handles array arguments!
	// crap, I hate this but I gotta do it - shoulda used array arguments or something -
	// if $use_orb == 1 then the url will go through orb.aw, not index.aw - which means that it will be shown
	// directly, without drawing menus and stuff

	function mk_my_orb($fun,$arr=array(),$cl_name="",$force_admin = false,$use_orb = false,$sep = "&")
	{
		// resolve to name
		if (is_numeric($cl_name))
		{
			$cl_name = $GLOBALS["cfg"]["__default"]["classes"][$cl_name]["file"];
		};
		$cl_name = ("" == $cl_name) ? get_class($this) : basename($cl_name);

		$this->orb_values = array();

		if (!empty($arr["section"]))
		{
			$this->orb_values["section"] = $arr["section"];
		};

		if (isset($arr["_alias"]) && !empty($arr["section"]))
		{
			$this->orb_values["alias"] = $arr["_alias"];
			unset($arr["_alias"]);
		}
		else
		{
			$this->orb_values["class"] = $cl_name;
		};
		$this->orb_values["action"] = $fun;

		// grr, I hate this, but I can optimize it later
		$track_vars = array("cal","date","trid","project");
		foreach($track_vars as $tvar)
		{
			$tvar_val = aw_global_get($tvar);
			if ($tvar_val && empty($arr[$tvar]))
			{
				$this->orb_values[$tvar] = $tvar_val;
			};
		};		


		// figure out the request method once.
		static $r_use_orb;
		static $in_admin;
		if (!isset($r_use_orb))
		{
			$r_use_orb = basename($_SERVER["SCRIPT_NAME"],".aw") == "orb";
		};

		if (!isset($in_admin))
		{
			$in_admin = stristr($_SERVER["REQUEST_URI"],"/automatweb") != false;
		}

		// this could probably use some more optimizing
		if (empty($this->use_empty))
		{
			$this->use_empty = false;
		};

		$this->process_orb_args("",$arr);
		$res = $this->cfg["baseurl"] . "/";
		if ($force_admin || $in_admin)
		{
			$res .= "automatweb/";
			$use_orb = true;
		};
		if ($use_orb || $r_use_orb)
		{
			$res .= "orb.aw";
		};
		$res .= ($sep == "/") ? "/" : "?";
		foreach($this->orb_values as $name => $value)
		{
			$res .= $name."=".$value.$sep;
		};
		return substr($res,0,-1);
	}

	////
	// !creates the necessary hidden elements to put in a form that tell the orb which function to call
	function mk_reforb($fun,$arr = array(),$cl_name = "")
	{

		$cl_name = ("" == $cl_name) ? get_class($this) : basename($cl_name);

		$this->orb_values = array(
			"class" => $cl_name,
			"action" => $fun,
		);

		if (empty($arr["no_reforb"]))
		{
			$this->orb_values["reforb"] = 1;
		};
		
		// grr, I hate this, but I can optimize it later
		$track_vars = array("cal","date","trid","project");
		foreach($track_vars as $tvar)
		{
			$tvar_val = aw_global_get($tvar);
			if ($tvar_val && empty($arr[$tvar]))
			{
				$this->orb_values[$tvar] = $tvar_val;
			};
		};		

		$this->use_empty = true;

		// flatten is not the correct term!
		$this->process_orb_args("",$arr);
		$res = "";
		foreach($this->orb_values as $name => $value)
		{
			$value = str_replace("\"","&amp;",$value);
			$res .= "<input type='hidden' name='$name' value='$value' />\n";
		};
		return $res;
	}

	function process_orb_args($prefix,$arr)
	{
		foreach($arr as $name => $value)
		{
			if (is_array($value))
			{
				$_tpref = "" == $prefix ? $name : "[".$name."]";
				$this->process_orb_args($prefix.$_tpref,$arr[$name]);
			}
			else
			{
				// commented this out, because it breaks stuff - namely, urls that are created via
				// $this->mk_orb("admin_cell", array("id" => $this->id, "col" => (int)$arr["r_col"], "row" => (int)$arr["r_row"]))
				// where the col and row parameters will be "0"
				// it will not include them.. damned if I know why
				// so, before putting this back, check that
				// - terryf

				// 0 will get included now, "" will not. reforb sets use_empty so
				// that gets everything
				if ((isset($value) && ($value !== "")) || $this->use_empty)	
				//{
					$this->orb_values[empty($prefix) ? $name : $prefix."[".$name."]"] = $value;
				//};
			}
		};
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
		if (!$this->can("view", $oid))
		{
			return;
		}
		if (is_oid($oid))
		{
			$path = "";
			$current = new object($oid);
			// path() always return an array
			$chain = array_reverse($current->path());

			foreach($chain as $obj)
			{
				$name = $obj->name();
				$name = !empty($name) ? $name : "(nimetu)";
				$path = html::href(array(
					"url" => $this->mk_my_orb("right_frame",array(
						"parent" => $obj->id(),
						"period" => $period,
					),"admin_menus"),
					"caption" => strip_tags($name),
				)) . " / " . $path;
			};
		}			

		$GLOBALS["site_title"] = $path.$text;
		// find the bit after / in text
		$sps = strrpos(strip_tags($text),"/");
		if ($sps > 0)
		{
			$sps++;
		}

		$old = aw_global_get("title_action");
		if (empty($old))
		{
			aw_global_set("title_action", substr(strip_tags($text),$sps));
		};
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
			if (($fs = filesize($arr["file"])) > 0)
			{
				$retval = fread($fh,$fs); // SLURP
			}
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
		// faili kirjutavad, peaks olema suht väikese tõenäosusega sündmus, sest
		// üldjuhul me kasutame random nimedega faile.
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

		$tmp = aw_ini_get("classes");
		$v = $tmp[$obj["class_id"]];
		if (!is_array($v))
		{
			return false;
		}

		$file = $v["alias_class"] != "" ? $v["alias_class"] : $v["file"];
		if ($file == "document")
		{
			$file = "doc";
		};

		$t = get_instance($file);
		$s = $t->_serialize($arr);
		if (!$s)
		{
			return false;
		}

		if (aw_global_get("__is_rpc_call"))
		{
			$arr["raw"] = 1;
		};
		$str = array("class_id" => $obj["class_id"], "str" => $s);
		return isset($arr["raw"]) ? $s : serialize($str);
	}

	////
	// !creates an object based on the string representation created by serialize()
	// $arr["str"] = string
	// $arr["parent"] = new object's parent
	// $arr["period"] = new object's period
	function unserialize($arr)
	{
		extract($arr);

		if (is_array($str))
		{
			$arr["raw"] = $str;
		};
		
		$s = isset($arr["raw"]) ? $arr["raw"] : unserialize($str);

		if (!is_array($s))
		{
			return false;
		}


		$tmp = aw_ini_get("classes");
		$s_class_id = $s["class_id"];
		$v = $tmp[$s_class_id];
		if (!is_array($v))
		{
			return false;
		}

		$fl = $v["alias_class"] != "" ? $v["alias_class"] : $v["file"];
		if ($fl == "document")
		{
			$fl = "doc";
		};


		$t = get_instance($fl);
		return $t->_unserialize(array("str" => $s["str"], "parent" => $parent, "period" => $period, "raw" => $arr["raw"]));
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
		if (is_array($args) && isset($args["trid"]))
		{
			if (!is_object($this->tr))
			{
				$this->tr = get_instance("translate/class_translator");
			}
			$this->tr->load_catalog($this->clid);
		}
	}

	function tr($arg)
	{
		return $this->tr->get($this->trid,$arg);
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

	function _get_menu_list_cb($o, $param)
	{
		$this->tt[$o->id()] = $o->path_str();
	}
	
	////
	// !teeb objektide nimekirja ja tagastab selle arrays, sobiv picker() funxioonile ette andmisex
	// ignore_langmenus = kui sait on mitme keelne ja on const.aw sees on $lang_menus = true kribatud
	// siis kui see parameeter on false siis loetaxe aint aktiivse kelle menyyd

	// empty - kui see on true, siis pannaxe k6ige esimesex arrays tyhi element
	// (see on muiltiple select boxide jaoks abix)

	// rootobj - mis objektist alustame
	function get_menu_list($ignore_langmenus = false,$empty = false,$rootobj = -1, $onlyact = -1, $make_path = true) 
	{
		enter_function("core::get_menu_list");
		$admin_rootmenu = $this->cfg["admin_rootmenu2"];

		if ($rootobj == -1)
		{
			$rootobj = $admin_rootmenu;
		}

		global $awt;

		$ot = new object_tree(array(
			"class_id" => CL_MENU,
			"parent" => $rootobj,
			"status" => array(STAT_NOTACTIVE, STAT_ACTIVE),
			"sort_by" => "objects.parent",
			"lang_id" => array(),
			"site_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"lang_id" => aw_global_get("lang_id"),
					"type" => MN_CLIENT
				)
			)),
			"sort_by" => "objects.parent, objects.jrk"
		));


		$this->tt = array();
		if ($empty)
		{
			$this->tt[] = "";
		}

		if ($make_path)
		{
			$ot->foreach_cb(array(
				"save" => false,
				"func" => array(&$this, "_get_menu_list_cb"),
				"param" => ""
			));
		}
		else
		{
			$_tmp = $ot->to_list();
			$_tmp2 = $_tmp->names();
			if (is_array($_tmp2))
			{
				$this->tt = $_tmp2;
			};
		};

		exit_function("core::get_menu_list");
		return $this->tt;
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
	
		$ob = get_instance("orb");
		return $ob->do_method_call($arr);
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
};
?>
