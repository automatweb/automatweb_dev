<?php
// $Header: /home/cvs/automatweb_dev/classes/core.aw,v 2.111 2002/10/09 09:54:49 kristo Exp $
// core.aw - Core functions

define("ARR_NAME", 1);
define("ARR_ALL",2);

define("TPLTYPE_TPL",0);
define("TPLTYPE_FORM",1);

classload("db");
class core extends db_connector
{
	var $errmsg;		

	////
	// !fetchib kirje suvalisest tabelist
	function get_record($table,$field,$selector)
	{
		$q = "SELECT * FROM $table WHERE $field = '$selector'";
		$this->db_query($q);
		return $this->db_fetch_row();
	}

	////
	// !kustutab kirje mingist tabelist. Kahtlane värk
	function dele_record($table,$field,$selector)
	{
		$q = "DELETE FROM $table WHERE $field = '$selector'";
		$this->db_query($q);
	}

	////
	// !fetch a config key
	function get_cval($ckey)
	{
		$q = sprintf("SELECT content FROM config WHERE ckey = '%s'",$ckey);
		return $this->db_fetch_field($q,"content");
	}

	////
	// !Setter for object
	function set_opt($key,$val)
	{
		$this->$key = $val;
		global $DBG;
		if ($DBG)
		{
			print "setting $key to $val<br>";
		}
	}
	
	////
	// !Setter for object
	function get_opt($key)
	{
		return $this->$key;
	}

	// Objekti metadata handlemine. Seda hoitakse objects tabelis metadata väljas XML kujul.
	// Järgnevate funktsioonidega saab selle välja sisu kätte ja muuta
	////
	// !Loeb objekti metainfo sisse
	// oid - objekti oid
	// key - key, mille sisu teada soovitakse
	// $data = $this->get_object_metadata(array(
	//					"oid" => "666",
	//					"key" => "notes",));
	function get_object_metadata($args = array())
	{
		extract($args);
		// if metadata is defined in the arguments, we will not read
		// the object into memory.
		if ($oid)
		{
			$odata = $this->get_object($oid,$args["no_cache"]);
			//$odata = $this->_get_object_metadata($oid);
		}
		else
		{
			$odata["metadata"] = $args["metadata"];
		};

		if (!$odata)
		{
			return false;
		};

		// load, and parse the information
		$metadata = aw_unserialize($odata["metadata"]);
		
		// if key is defined, return only that part of the metainfo
		if ($args["key"])
		{	
			$retval = $metadata[$args["key"]];
		}
		// otherwise the whole thing
		else
		{
			$retval = $metadata;
		};
		return $retval;
	}

	function _get_object_metadata($oid)
	{
		$q = "SELECT metadata FROM objects WHERE oid = '$oid'";
		$this->db_query($q);
		return $this->db_next();
	}

	////
	// !Kirjutab objekti metadata väljas mingi key üle
	// argumendid
	// oid - objekti oid
	// key - votme nimi
	// value - võtme sisu (integer, string, array, whatever)
	// $this->set_object_metadata(array(
	//				"oid" => $oid,
	//				"key" => "notes",
	//				"value" => "3l33t",
	// ));
	// mitu keyd saab korraga ka ette anda - $data arrays
	// kui see on antud, siis mergetakse see metadata arrayga kokku
	function set_object_metadata($args = array())
	{
		$this->quote($args);
		extract($args);

		$old = $this->_get_object_metadata($oid);

		// no such object? bail out
		if (not($old))
		{
			return false;
		};
			
		$metadata = aw_unserialize($old["metadata"]);
		if ($overwrite)
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
		
		if ($delete_key)
		{
			unset($metadata[$key]);
		};
		
		$newmeta = aw_serialize($metadata,SERIALIZE_PHP);

		if (not($serialize))
		{
			$this->quote($newmeta);
			$q = "UPDATE objects SET metadata = '$newmeta' WHERE oid = '$oid'";
			$this->db_query($q);
			$retval = true;
		}
		else
		{
			$retval = $newmeta;
		};
		return $retval;
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
		#$this->dequote($row["meta"]);
		$retval = $php_ser->php_unserialize($meta);
		return $retval;
	}


	function obj_set_meta($args = array())
	{
		extract($args);
		$old = $this->obj_get_meta(array("oid" => $oid));
		$old = array_merge($old,$args["meta"]);
		classload("php");
		$php_ser = new php_serializer();
		$ser = $php_ser->php_serialize($old);
		$this->quote($ser);
		$q = "UPDATE objects SET meta = '$ser' WHERE oid = $oid";
		$this->db_query($q);
	}

	////
	// !write to syslog. 
	function _log($type,$action,$oid = 0)
	{
		$REMOTE_ADDR = aw_global_get("REMOTE_ADDR");
		$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
		if (!is_ip($ip))
		{
			$ip = $REMOTE_ADDR;
		}
		$t = time();
		$this->quote($action);
		$this->quote($time);
		$fields = array("tm","uid","type","action","ip","oid","created_hour","created_day","created_week","created_month","created_year");
		$values = array($t,aw_global_get("uid"),$type,$action,$ip,$oid,date("H",$t),date("d",$t),date("w",$t),date("m",$t),date("Y",$t));
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

		$this->db_query($q);
	}
		
	////
	// !outputs debug information if $HTTP_SESSION_VARS["debugging"] is set
	function dmsg($msg)
	{
		if (aw_global_get("debugging") || aw_global_get("DEBUG"))
		{
			if (is_array($msg))
			{
				print "<b>------DBG START---------</b><br>";
				print "<pre>";
				print_r($msg);
				print "</pre>";
				print "<b>------DBG END--------</b><br>";
			}
			else
			{
				print "DBG: $msg<br>";
			};
		};
	}

	////
	// !Saadab alerdi (ebaonnestunud sisselogimine, vms) aadressile config.alert_addr
	function send_alert($msg)
	{
		$subject = sprintf(aw_ini_get("config.alert_subject"),aw_global_get("HTTP_HOST"));
		$msg = "IP: ".aw_global_get("REMOTE_ADDR")."\nTeade:" . $msg;
		mail(aw_ini_get("config.alert_addr"),$subject,$msg,aw_ini_get("config.alert_from"));
	}

	//// 
	// !Küsib infot kasutaja kohta. Tõstsin selle siia, sest mitmed teised klassid vajavad seda funktsiooni
	// ja users klassi laadimine oleks overkill sellisel juhul
	// Parameetrid:
	//	uid - kelle info
	//	field - millise välja sisu. Kui defineerimata, siis terve kirje
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
		if (!is_array(aw_cache_get("users_cache",$uid)))
		{
			$q = "SELECT * FROM users WHERE uid = '$uid'";
			$this->db_query($q);
			$row = $this->db_next();
			// nu nii. enne oli siin ntx turvaprobleem et sai urlist parooli 2ra nullida. not so any more :)
			aw_cache_set("users_cache",$uid,$row);
		}
		else
		{
			$row = aw_cache_get("users_cache",$uid);
		};
		if (isset($field))
		{
			$retval = $row[$field];
		}
		else
		{
			if (isset($row))
			{
				// inbox defauldib kodukataloogile, kui seda määratud pole
				$inbox = isset($row["msg_inbox"]) ? $row["msg_inbox"] : $row["home_folder"];
				$row["msg_inbox"] = $inbox;
				$retval = $row;
			};
		};
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
	// !tagastab objekti info aliase kaudu
	// if optional $parent argument is also se, we add that to the WHERE clause
	// of the query (needed for parsing long urs like http://site/kalad/tursk
	// where we have to make sure that "tursk" actually belongs to "kalad"
	function _get_object_by_alias($alias,$parent = 0) 
	{
		// bail out, if $alias doesn't have the value, because otherwise
		// we end up scanning _all_ objects which have no alias and are not deleted
		// for no reason at all. I don't know why this is called with no value
		// but it does sometimes
		if (strlen($alias) == 0)
		{
			return false;
		};
		if ($parent)
		{
			$ps = " AND parent = $parent ";
		};
		$q = "SELECT * FROM objects
			WHERE alias = '$alias' AND status = 2 $ps $ss";
		$this->db_query($q);
		$res = $this->db_fetch_row();
		return $res;
	}

	////
	// !Tagastab kasutaja login menüü id
	function get_login_menu()
	{
		return $this->login_menu;
	}

	////
	// !adds a new object, inserts all fields that are given in the array and puts default
	// values for all others
	// lang_id,site_id,created,createdby,modified,modifiedby are set to correct values
	function new_object($arr,$add_acl = true)
	{
		if (!is_array($arr))
		{
			print LC_CORE_HOW_HAPPENED;
			die();
		};
		
		// objektitabeli väljad
		// dummy on selleks, et array_flop juures parenti väärtuseks "0" ei tuleks
		$_ofields = array("dummy","parent","name","createdby","class_id","created","modified",
				"status","hits","lang_id","comment","last","modifiedby",
				"jrk","period","alias","periodic","site_id","activate_at",
				"deactivate_at","autoactivate","autodeactivate","brother_of",
				"cache_dirty","metadata","subclass","flags"
			);

		// vahetame key=>value paarid ära
		$ofields = array_flip($_ofields);
		
		// oid voib tulla naiteks serializetud objektist
		// anyhow, meil siin pole sellega midagi peale hakata, so nil.
		unset($arr["oid"]);

		// Nende väärtustega kirjutame üle whatever asjad $arr sees olid
		$_localdata = array(
			"createdby" => aw_global_get("uid"),
			"created" => time(),
			"modifiedby" => aw_global_get("uid"),
			"modified" => time(),
			"site_id" => $this->cfg["site_id"],
		);

		if (!$arr["lang_id"])
		{
			$arr["lang_id"] = aw_global_get("lang_id");
		}

		// array array_merge (array array1, array array2 [, array ...])
		// If the input arrays have the same string keys,
		//  then the later value for that key will overwrite the previous one.
		$values = array_merge($arr,$_localdata);
		
		reset($values);


		foreach($values as $key => $val)
		{
			// Lisame ainult need valjad, mis objektitabelisse kuuluvad
			if (isset($ofields[$key]))
			{
				$cols[] = $key;
				$vals[] = "'$val'";
			};

		}
		// oid on auto_increment tüüpi väli. Possible race condition
		#$oid = $this->db_fetch_field("SELECT MAX(oid) AS oid FROM objects","oid")+1;

		$q = "INSERT INTO objects ( " . join(",",$cols) . ") VALUES (" . join(",",$vals) . ")";
		$this->db_query($q);
		
		$q = "SELECT MAX(oid) AS oid FROM objects WHERE createdby = '".aw_global_get("uid")."'";
		$oid = $this->db_fetch_field($q,"oid");
		$this->db_query("INSERT INTO hits (oid,hits) VALUES ($oid,0)");

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

		$this->flush_cache();
		return $oid;
	}

	////
	// !kirjutab objekti kustutatux
	function delete_object($oid,$class_id = false)
	{
		$obj = $this->get_object($oid);
		if ($obj["class_id"] == CL_FILE || $obj["class_id"] == CL_PSEUDO)
		{
			// remove the file size from all parent folders as well
			classload("file");
			$fi = new file;
			$meta = $this->get_object_metadata(array(
				"metadata" => $obj["metadata"]
			));
		}
		$t = time();
		$where = " oid = '$oid'";
		if ($class_id)
		{
			$where .= " AND class_id = '$class_id'";
		};

		$q = "UPDATE objects
			SET status = 0,
			    modified = '$t',
			    modifiedby = '".aw_global_get("uid")."'
			WHERE $where";
		$this->_log("OBJECT",sprintf(LC_CORE_TO_ERASED,$oid));
		$this->db_query($q);
	}

	///
	// !Margib koik saidi objektid dirtyks
	function flush_cache()
	{
		$q = "UPDATE objects SET cachedirty = 1";
		$this->db_query($q);
		$q = "UPDATE objects SET cachedata = ''";
		$this->db_query($q);
	}
		
	////
	// !uus ja parem objekti uuendamise funktsioon, votab andmed ette arrayst
	// ja uuendab ainult neid, mida ette anti
	//  	oid peab olema
	function upd_object($params) 
	{
		if (!$params["oid"])
		{
			$this->raise_error(ERR_CORE_NO_OID,LC_CORE_CALLED_WITHOUT_ID,0);
		};

		if ($params["oid"] == $params["parent"])
		{
			$this->raise_error(ERR_CORE_NO_OID,"Object can't be it's own parent",0);
		};

		$params["modifiedby"] = aw_global_get("uid");
		$params["modified"] = time();
		$params["cachedirty"] = 1;
		if ($params["metadata"])
		{
			$params["metadata"] = $this->set_object_metadata(array(
				"oid" => $params["oid"],
				"data" => $params["metadata"],
				"serialize" => 1,
			));
		};
		$this->quote($params);
		$q_parts = array(); // siia sisse paneme päringu osad
		while(list($k,$v) = each($params)) 
		{
			if ($k != "oid") 
			{
				$q_parts[] = " $k = '$v' ";
			};
		};
		$q = " UPDATE objects SET " . join(",",$q_parts) . " WHERE oid = $params[oid] ";
		$this->db_query($q);
		$this->flush_cache();
	}

	////
	// !Tagastab koik mingist nodest allpool olevad objektid
	// seda on mugav kasutada, kui tegemist on suhteliselt
	// vaikse osaga kogu objektihierarhiast, ntx kodukataloog
	// parent(int) - millisest nodest alustame?
	// class(int) - milline klass meid huvitab?
	// type(int) - kui tegemist on menüüga, siis loetakse sisse ainult seda tüüpi menüüd.
	// active(bool) - kui true, siis tagastakse ainult need objektid, mis on aktiivse
	// orderby(string) - millise välja järgi tulemus järjestada?
	function get_objects_below($args = array())
	{
		extract($args);
		$groups = array();
		$this->get_objects_by_class(array(
			"parent" => $parent,
			"class" => $class,
			"type" => $type,
			"lang_id" => $lang_id,
			"active" => $active,
			"status" => $status,
			"orderby" => $orderby,
		));
			
		while($row = $this->db_next())
		{
			$groups[$row["oid"]] = $row;
		};
		return $groups;
	}

	function indent_array($arr,$level)
	{
		static $indent = 0;
		static $flatlist = array();
		$indent++;
		while(list($key,$val) = each($arr[$level]))
		{
			$flatlist[$key] = str_repeat("&nbsp;",$indent*3) . $val;
			if (is_array($arr[$key]))
			{
				$this->indent_array($arr,$key);
			};
		};
		$indent--;
		return $flatlist;
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
	function add_alias($source,$target,$extra = "") 
	{
		$target_data = $this->get_object($target);
		$idx = $this->db_fetch_field("SELECT MAX(idx) as idx FROM aliases WHERE source = '$source' AND type =  '$target_data[class_id]'","idx");
		if ($idx === "")
		{
			$idx = 0;
		}
		else
		{
			$idx = 1;
		}
		$q = "INSERT INTO aliases (source,target,type,data,idx)
			VALUES('$source','$target','$target_data[class_id]','$extra','$idx')";

		$this->db_query($q);
		$aliasmgr = get_instance("aliasmgr");
		$aliasmgr->cache_oo_aliases($source);

		$this->_log("alias",sprintf(LC_CORE_ADD_TO_OBJECT,$source));
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
		$_aliases = $aliasmgr->cache_oo_aliases(array("oid" => $source));

		$this->_log("alias",sprintf(LC_CORE_ADD_TO_OBJECT,$source));
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
	function get_aliases_for($oid,$type = -1,$sortby = "", $order = "",$join = "") 
	{
		$ss = "";
		if ($type != -1)
		{
			$ss = " AND aliases.type = '$type' ";
		}
		if ($sortby == "")
		{
			$sortby = "id";
		}
		$js = "";
		$fs = "";
		if ($join != "")
		{
			$js = join(' ',$this->map2('LEFT JOIN %s ON %s',$join));
			$fs = ",".join(',',$this->map2('%s.*',$join));
		}
		$q = "SELECT aliases.*,objects.* $fs FROM aliases
			LEFT JOIN objects ON
			(aliases.target = objects.oid) $js
			WHERE source = '$oid' $ss ORDER BY aliases.id ";
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
		$tlist = join(',',map('%d',$type));

		$q = "SELECT aliases.*,objects.*
			FROM aliases
			LEFT JOIN objects ON
			(aliases.target = objects.oid)
			WHERE source = '$oid' AND objects.class_id IN ($tlist)
			ORDER BY aliases.id";
		$this->db_query($q);
		$aliases = array();
		while($row = $this->db_next())
		{
			// note that the index inside the idx array is always one less than the 
			// number in the alias. (e.g. oid of #f1# is stored at the position 0, etc)
			$aliases[] = $row;
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
	function addalias($arr)
	{
		extract($arr);
		$this->add_alias($id,$alias);
		//header("Location: ".$this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr"));
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
			classload("dummy");
			$this->parsers = new dummy();
			// siia paneme erinevad regulaaravaldised
			$this->parsers->reglist = array();
		};

		extract($args);

		if (isset($class) && isset($function) && $class && $function)
		{
			if (!is_object($this->parsers->$class))
			{
				classload($class);
				$this->parsers->$class = new $class;
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
		classload($class);
		if (!isset($this->parsers->$class) || !is_object($this->parsers->$class))
		{
			classload($class);
			$this->parsers->$class = new $class;
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
				// siit tsyklist välja
				$parent = 0;
			} 
			else 
			{
				$parent = $row["parent"];
				if (is_array($chain[$row["oid"]]))
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
			$class_id = $arg["class_id"];
			$unserialize_meta = $arg["unserialize_meta"];
		}
		else
		{
			$oid = $arg;
		};

		if ($no_cache)
		{
			$_t = $this->get_record("objects","oid",$oid);
			if ($unserialize_meta && $_t)
			{
				$_t["meta"] = aw_unserialize($_t["metadata"]);
			}
		}
		else
		{
			if (!($_t = aw_cache_get("objcache",$oid)))
			{
				$_t = $this->get_record("objects","oid",$oid);
				if ($unserialize_meta && $_t)
				{
					$_t["meta"] = aw_unserialize($_t["metadata"]);
				}

				aw_cache_set("objcache",$oid,$_t);
			}
		}

		if (isset($class_id) && ($_t["class_id"] != $class_id) )
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
		$object = $this->get_object($oid);
		if (is_array($object))
		{
			$meta = $this->get_object_metadata(array(
				"oid" => $oid,
				"metadata" => $object["metadata"],
			));

			$object["meta"] = $meta;
		};
		return $object;
	}
			

	////
	// !tagastab objekti nime ja class_id jargi
	function get_object_by_name($args = array())
	{
		extract($args);
		if ($class_id)
		{
			$part2 = " AND class_id = '$class_id'";
		};
		$q = "SELECT * FROM objects WHERE name = '$name' $part2 ORDER BY modified DESC";
		$this->db_query($q);
	}

	////
	// !tagastab mingisse kindlasse klassi kuuluvad objektid
	// hiljem voib seda funktsiooni täiendada nii, et ta joinib kylge ka igale klassile vastava tabeli
	// argumendid
	// class (int) - klassi id
	// parent(id/array)(opt) - kui defineeritud, siis loeb ainult objekte selle parenti all
	// active(bool) - kui true, siis tagastame ainult aktiivsed objektid
	// subclass - objects.subclass
	// flags - objects.flags
	function get_objects_by_class($args = array())
	{
		extract($args);
		// kui parent on antud, siis moodustame sellest IN klausli
		$sqlparts = array();
		if (is_array($parent))
		{
			$sqlparts[] = "parent IN (" . join(",",map("'%s'",$parent)) . ")";
		}
		else
		if ($parent)
		{
			$sqlparts[] = "parent = $parent";
		}

		$sqlparts[] = ($active) ? "status = 2 " : "status != 0 ";

		if ($subclass)
		{
			$sqlparts[] = "subclass = '$subclass' ";
		}

		if ($flags)
		{
			$sqlparts[] = "flags = '$flags' ";
		}
		
		if ($name != "")
		{
			$sqlparts[] = "name LIKE '%$name%' ";
		}

		if ($lang_id)
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

		$ostr = ($orderby) ? " ORDER BY $orderby " : "";
		// kui tegemist on menüüdega, siis joinime kylge ka menu tabeli
		if ($cl == CL_PSEUDO)
		{
			$q = "SELECT objects.* FROM objects LEFT JOIN menu ON (objects.oid = menu.id) $where $ostr";
		}
		else
		{
			$q = "SELECT objects.* FROM objects $where $ostr";
		};

		global $DBUG;
		if ($DBUG)
		{
			print $q;
		}
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
		if ($arr["addempty"])
		{
			$ret = array("" => "");
		}
		else
		{
			$ret = array();
		}
		$this->save_handle();
		$this->get_objects_by_class($arr);
		while ($row = $this->db_next())
		{
			if ($arr["return"] == ARR_ALL)
			{
				$ret[$row["oid"]] = $row;
			}
			else
			{
				$ret[$row["oid"]] = $row["name"];
			}
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
	// !miski lame funktsioon. Aga praegu paremini ei oska. templateeditor pruugib seda
	function get_objects_by_parent($parent)
	{
		$q = "SELECT * FROM objects WHERE parent = '$parent'";
		$this->db_query($q);
		$result = array();
		while($row = $this->db_next())
		{
			$result[] = $row;
		};
		return $result;
	}

	////
	// !hmm .. miski kahtlane värk ... vbla polegi neid vaja?
	// fetchib objekti last välja ja unserializeb selle
	// 
	// jah, vbla pole t6esti. kogu see aliaste asi tulex nac yle vaadata - terryf
	function get_last($oid) 
	{
		$row = $this->get_object($oid);
		return unserialize($row["last"]);
	}

	////
	// !salvestab objekti last välja uue info
	function set_last($oid,$field,$val) 
	{
		$old = $this->get_last($oid);
		$old[$field] = $val;
		$new = serialize($old);
		$this->quote($new);
		$q = "UPDATE objects SET last = '$new' WHERE oid = '$oid'";
		$this->db_query($q);
	}

	//// 
	// !Veateade
	// $msg - teate tekst
	// $fatal - katkestada töö?
	// $silent - logida viga, aga jätkata tööd
	function raise_error($err_type,$msg, $fatal = false, $silent = false) 
	{
		$this->errmsg[] = $msg;
		$this->errorlevel++;

		$msg = "Suhtuge veateadetesse rahulikult!  Te ei ole korda saatnud midagi katastroofilist. Ilmselt juhib programm Teie tähelepanu mingile ebatäpsusele  andmetes või näpuveale.<Br><br>\n\n".$msg;

		$this->_log("error",$msg);	
		// meilime veateate listi ka
		$subj = "Viga saidil ".$this->cfg["baseurl"];
		header("X-AW-Error: 1");
		$content = "\nVeateade: ".$msg;
		$content.= "\nKood: ".$err_type;
		$content.= "\nfatal = ".($fatal ? "Jah" : "Ei" )."\n";
		$content.= "PHP_SELF = ".aw_global_get("PHP_SELF")."\n";
		$content.= "lang_id = ".aw_global_get("lang_id")."\n";
		$content.= "uid = ".aw_global_get("uid")."\n";
		$content.= "section = ".$GLOBALS["section"]."\n";
		$content.= "url = ".$this->cfg["baseurl"].aw_global_get("REQUEST_URI")."\n-----------------------\n";
		global $HTTP_COOKIE_VARS;
		foreach($HTTP_COOKIE_VARS as $k => $v)
		{
			$content.="HTTP_COOKIE_VARS[$k] = $v \n";
		}
		global $HTTP_GET_VARS;
		foreach($HTTP_GET_VARS as $k => $v)
		{
			$content.="HTTP_GET_VARS[$k] = $v \n";
		}
		global $HTTP_POST_VARS;
		foreach($HTTP_POST_VARS as $k => $v)
		{
			$content.="HTTP_POST_VARS[$k] = $v \n";
		}
		global $HTTP_SERVER_VARS;
		foreach($HTTP_SERVER_VARS as $k => $v)
		{
			// we will not send out the password or the session key
			if ( ($k == "PHP_AUTH_PW") || ($k == "automatweb") )
			{
				continue;
			};
			$content.="HTTP_SERVER_VARS[$k] = $v \n";
		}

		// try to find the user's email;
		$head = "";
		if (aw_global_get("uid") != "")
		{
			$udata = $this->get_user(array("uid" => $uid));
			$head="From: $uid<".$udata["email"].">\n";
		}
		mail("vead@struktuur.ee", $subj, $content,$head);

		// here we replicate the error to the site that logs all errors (usually aw.struktuur.ee)
		// we replicate by POST request, cause this thing can be too long for a GET request
		global $class,$action;

		if ((aw_ini_get("bugtrack.report_to_server") == 1) && !($class == "bugtrack" && $action="add_error"))
		{
			// kui viga tuli bugi replikeerimisel, siis 2rme satu l6pmatusse tsyklisse
			classload("socket");
			$socket = new socket(array(
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
			return;
		};

		if ($fatal)
		{
			classload("config");
			$co = new config;
			$u = $co->get_simple_config("error_redirect");
			if ($u != "" && aw_global_get("uid") != "kix")
			{
				header("Location: $u");
				die();
			}
			flush();
			die("<br><b>AW_ERROR: $msg</b><br>");
		};
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
	// !finds the edit template for menu $section
	// if the template is not set for this menu, traverses the object tree upwards 
	// until it finds a menu for which it is set
	function get_edit_template($section)
	{
		do { 
			// for edit templates the type is 0
			// this probably breaks the formgen edit templates, but detecting it this way
			// caused major breakage, I got showing templates if I tried to edit documents
			$section = (int)$section;
			
			$this->db_query("SELECT template.filename AS filename, objects.parent AS parent,objects.metadata as metadata FROM menu LEFT JOIN template ON template.id = menu.tpl_edit LEFT JOIN objects ON objects.oid = menu.id WHERE template.type = 0 AND menu.id = $section");
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
				$template = $meta["ftpl_edit"];
			}
			
			if (not($row))
			{
				$prnt = $this->get_object($section);
				$section = $prnt["parent"];
			}
			else
			{
				$section = $row["parent"];
			};
		} while ($template == "" && $section > 1);
		if ($template == "")
		{
			//$this->raise_error(ERR_CORE_NOTPL,"You have not selected an document editing template for this menu!",true);
			// just default to that
			global $DBG;
			if ($DBG)
			{
				print "not found, defaulting to edit<br>";
			}
			$template = "edit.tpl";
		}
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
		if ($cl_name == "")
		{
			$cl_name = get_class($this);
		}

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
		foreach($arr as $k => $v)
		{
			if (is_array($v))
			{
				$stra = array();
				foreach($v as $k2 => $v2)
				{
					$stra[] = $k."[$k2]=".$v2;
				}
				$str = join($separator,$stra);
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
		// url-il? Ntx www.kirjastus.ee/pk/automatweb juures see ei tööta. - duke
		if ((stristr($this->REQUEST_URI,"/automatweb")!=false) || $force_admin)
		{
			// admin side.
			return $this->cfg["baseurl"]."/automatweb/orb.".$this->cfg["ext"].$qm.$sec."class=$cl_name".$separator."action=$fun".$separator."$urs";
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
				return $this->cfg["baseurl"].$uo.$sec."alias=$arr[_alias]".$separator."action=$fun".$separator."$urs";
			}
			else
			{
				return $this->cfg["baseurl"].$uo.$sec."class=$cl_name".$separator."action=$fun".$separator."$urs";
			};
		}
	}

	////
	// !old version of orb url maker, use mk_my_orb instead 
	// kui user = 1, siis suunatakse tagasi Saidi sisse. Now, I do realize that this is not
	// the best solution, but for now, it works

	// Kas seda kasutatkse veel? - duke
	// well, kasuttakse, aga tegelt ei tohiks. ysnaga, DEPRECATEED - terryf
	function mk_orb($fun,$arr, $cl_name = "",$user = "")
	{
		if ($cl_name == "")
		{
			$cl_name = get_class($this);
		}

		$urs = join("&",$this->map2("%s=%s",$arr));

		if ($user)
		{
			if (strpos(aw_ini_get("users.homedir"),"?") === false)
			{
				$sep = "?";
			}
			else
			{
				$sep = "&";
			}
			$url = aw_ini_get("users.homedir").$sep."id=" . $arr["parent"];
		}
		else
		{
			$url = "orb.".$this->cfg["ext"]."?class=$cl_name&action=$fun&$urs";
		};
			
		return $url;
	}

	////
	// !reforbi koostamine
	// ei, ma ei ytleks, et see deprecated on. See on lihtsalt alternatiivne lähenemine
	// reforbi koostamisele, kusjuures ta on väga liberaalne parameetrite suhtes.
	// eelmise analoog. Parameetrid votab arrayst
	function mk_site_orb($args = array())
	{
		extract($args);
		if (!isset($class))
		{
			$args["class"] = get_class($this);
		};
		$arguments = join("&",$this->map2("%s=%s",$args));
		$retval = $this->cfg["baseurl"] . "/?" . $arguments;
		return $retval;
	}

	////
	// !creates the necessary hidden elements to put in a form that tell the orb which function to call
	function mk_reforb($fun,$arr = array(),$cl_name = "")
	{
		if ($cl_name == "")
		{
			$cl_name = get_class($this);
		}

		$urs = join("\n",$this->map2("<input type='hidden' name='%s' value='%s' />\n",$arr));
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
	function mk_link($args = array())
	{
		$retval = array();
		foreach($args as $key => $val)
		{
			if ($val)
			{
				$retval[] = sprintf("%s=%s",$key,$val);
			}
			else
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
				$path="<a href='".$this->mk_my_orb("right_frame", array("parent" => $row["oid"],"period" => $period),"menuedit")."'>".$name."</a> / ".$path;
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

		$v = $this->cfg["classes"][$obj["class_id"]];
		if (!is_array($v))
		{
			return false;
		}

		classload($v["file"]);
		$t = new $v["file"];

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

		classload($v["file"]);
		$t = new $v["file"];


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
	function init($tpldir)
	{
		$this->db_init();
		$this->tpl_init($tpldir);
	}

	////
	// !loads localization constans and imports them to the current class, vars are assumed to be in array $arr_name
	function lc_load($file,$arr_name)
	{
		$admin_lang_lc = aw_global_get("admin_lang_lc");
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
		$cache = new cache;
		if (($cont = $cache->file_get($cf_name)))
		{
			$dat = aw_unserialize($cont);
			aw_global_set($cf_name, $dat);
			return $dat;
		}
		
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
			$ret[$row["parent"]][] = $row;
		}

		$tt = array();
		if ($empty)
		{
			$tt[] = "";
		}
		if ($rootobj == -1)
		{
			$rootobj = $admin_rootmenu;
		}
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

    reset($arr[$parent]);
    while (list(,$v) = each($arr[$parent]))
    {
			$name = $prefix == "" ? $v["name"] : $prefix."/".$v["name"];
      $ret[$v["oid"]] = $name;
      $this->mkah(&$arr,&$ret,$v["oid"],$name);
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

	////
	// !returns an array of all classes defined in the system, index is class id, value is class name and path
	//  addempty - if true, empty element in front
	function get_class_picker($arr = array())
	{
		extract($arr);
		$cls = $this->cfg["classes"];
		$clfs = $this->cfg["classfolders"];

		$ret = array();
		if ($addempty)
		{
			$ret = array(0 => "");
		}

		foreach($cls as $clid => $cld)
		{
			$clname = $cld["name"];
			if ($clname != "")
			{
/*			if ($cld["parents"] != "")
			{
				list($prnt) = explode(",",$cld["parents"]);
				while($prnt)
				{
					$clname = $clfs[$prnt]["name"]."/".$clname;
					$prnt =$clfs[$prnt]["parent"];
				}
			}*/
				$ret[$clid] = $clname;
			}
		}
		asort($ret);
		return $ret;
	}
};
?>
