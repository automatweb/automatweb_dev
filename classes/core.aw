<?php
// $Header: /home/cvs/automatweb_dev/classes/core.aw,v 2.28 2001/06/20 06:09:02 duke Exp $
// core.aw - Core functions

classload("connect");
class core extends db_connector
{
	var $errmsg;		

	function core_init()
	{
		// globaalsed muutujad impordime siin, niimoodi saab vältita koiki neid globaalsete
		// muutujate sissetoomist all over the field.
		// ja seda ka kuniks meil paremat lahendust pole

		// s.t. nii peaks tehtama, aga samas ei tehta, kuna seda konstruktorit siin
		// ei kutsuta jargnevatest välja. Kuigi peaks
		// ntx $this->core_init();
		global $basedir;
		$this->basedir = $basedir;
		$this->parsers = "";
	}

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
		$odata = $this->_get_object_metadata($oid);
		if (!$odata)
		{
			return false;
		};
		// load, and parse the information
		classload("xml");
		$xml = new xml(array("ctag" => "metadata"));
		$metadata = $xml->xml_unserialize(array(
					"source" => $odata["metadata"],
		));
		return $metadata[$key];
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
	function set_object_metadata($args = array())
	{
		$this->quote($args);
		extract($args);
		// loeme vana metadata sisse
		$old = $this->_get_object_metadata($oid);
		if (!$old)
		{
			return false;
		};
		classload("xml");
		$xml = new xml(array("ctag" => "metadata"));
		$metadata = $xml->xml_unserialize(array(
					"source" => $old["metadata"],
		));

		$metadata[$key] = $value;
		$newmeta = $xml->xml_serialize($metadata);
		$this->quote($newmeta);
		$q = "UPDATE objects SET metadata = '$newmeta' WHERE oid = '$oid'";
		$this->db_query($q);
		return true;
	}

	////
	// järgmised 2 funktsiooni on vajalikud ntx siis, kui on tarvis ühe objekti
	// juures korraga 2 või enamat päringut kasutada. Enne uue sisestamist tuleb
	// siis lihtsalt vana handle ära seivida

	////
	// !Salvestab query handle sisemise pinu sisse
	function save_handle()
	{
		if (!isset($this->qID))
		{
			$this->qID = 0;
		}
		$this->_push($this->qID,"qID");
	}

	////
	// !Taastab query handle pinu seest
	function restore_handle()
	{
		$this->qID = $this->_pop("qID");
	}

	////
	// !write to syslog. 
	function _log($type,$action,$oid = 0)
	{
		global $uid;
		global $REMOTE_ADDR,$HTTP_X_FORWARDED_FOR;
		$ip = $HTTP_X_FORWARDED_FOR;
		if (!is_ip($ip))
		{
			$ip = $REMOTE_ADDR;
		}
		$t = time();
		$this->quote($action);
		$this->quote($time);
		$q = "INSERT DELAYED INTO syslog (syslog.tm,uid,type,action,ip,oid)
			VALUES('$t','$uid','$type','$action','$ip','$oid')";
		$this->db_query($q);
	}
		
	////
	// !outputs debug information if $HTTP_SESSION_VARS["debugging"] is set
	function dmsg($msg)
	{
		global $HTTP_SESSION_VARS;
		if ($HTTP_SESSION_VARS["debugging"])
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
	// !Saadab alerdi (ebaonnestunud sisselogimine, vms) aadressile ALERT_ADDR (const.aw)
	function send_alert($msg)
	{
		global $HTTP_HOST;
		global $REMOTE_ADDR;
		$subject = sprintf(ALERT_SUBJECT,$HTTP_HOST);
		$msg = "IP: $REMOTE_ADDR\nTeade:" . $msg;
		mail(ALERT_ADDR,$subject,$msg,ALERT_FROM);
	}

	//// 
	// !Küsib infot kasutaja kohta. Tõstsin selle siia, sest mitmed teised klassid vajavad seda funktsiooni
	// ja users klassi laadimine oleks overkill sellisel juhul
	// Parameetrid:
	//	uid - kelle info
	//	field - millise välja sisu. Kui defineerimata, siis terve kirje
	function get_user($args = array())
	{
		global $users_cache;
		extract($args);
		if (!is_array($users_cache[$uid]))
		{
			$q = "SELECT * FROM users WHERE uid = '$uid'";
			$this->db_query($q);
			$row = $this->db_next();
			$users_cache[$uid] = $row;
		}
		else
		{
			$row = $users_cache[$uid];
		};
		if (isset($field))
		{
			$retval = $row[$field];
		}
		else
		if (isset($row))
		{
			// inbox defauldib kodukataloogile, kui seda määratud pole
			$inbox = isset($row["msg_inbox"]) ? $row["msg_inbox"] : $row["home_folder"];
			$row["msg_inbox"] = $inbox;
			$retval = $row;
		};
		return $row;	
	}

	////
	// !Tagastab formaaditud timestambi
	// Tekalt voiks defineerida mingid konstandid nende numbrite asemele
	// See parandaks loetavust
	function time2date($timestamp = "",$format = 0)
	{
		switch($format)
		{
			case "1" :
				// 12:22 04-Jan
				$dateformat = "H:i d-M";
				break;	
			case "2":
				// 12:22 04-Jan-2000
				$dateformat = "d.m.Y / H:i";
				break;
			case "3":
				// 04-Jan-2000
				$dateformat = "d-M-Y";
				break;
			case "4":
				// 12:22 04/01/00
				$dateformat = "H:i d/m/y";
				break;
			case "5":
				// 04/01/00
				$dateformat = "d/m/y";
				break;
			case "6":
				// 12:20 04-01-00
				$dateformat = "H:i d-M-Y";
				break;
			case "7":
				// 04-2001
				$dateformat = "M-Y";
				break;
			case "8":
				// 04.01.00
				$dateformat = "d.m.y";
				break;
			default:
				// 12:22 04-01
				$dateformat = "H:i d-m";

		};
		return ($timestamp) ? date($dateformat,$timestamp) : date($dateformat);
	}

	////
	// !tagastab objekti info aliase kaudu
	function _get_object_by_alias($alias) 
	{
		if ($GLOBALS["lang_menus"] == 1)
		{
			$ss = " AND lang_id = ".$GLOBALS["lang_id"];
		};
		$q = "SELECT * FROM objects
			WHERE alias = '$alias' AND status != 0 $ss";
		$this->db_query($q);
		$res = $this->db_fetch_row();
		return $res;
	}

	////
	// !tagastab array grupi id'dest, kuhu kasutaja kuulub
	function get_gids_by_uid($uid)
	{
		$q = "SELECT gid FROM groupmembers WHERE uid = '$uid'";
		$this->db_query($q);
		$retval = array();
		while($row = $this->db_next())
		{
			$retval[] = $row["gid"];
		};
		return $retval;
	}

	////
	// !adds a new object, inserts all fields that are given in the array and puts default
	// values for all others
	// lang_id,site_id,created,createdby,modified,modifiedby are set to correct values
	function new_object($arr,$add_acl = true)
	{
		if (!is_array($arr))
		{
			print "Insufficient data to create an object. How did this happen anyway?";
			die();
		};
		
		// objektitabeli väljad
		// dummy on selleks, et array_flop juures parenti väärtuseks "0" ei tuleks
		$_ofields = array("dummy","parent","name","createdby","class_id","created","modified",
				"status","hits","lang_id","comment","last","modifiedby",
				"jrk","period","alias","periodic","site_id","activate_at",
				"deactivate_at","autoactivate","autodeactivate","brother_of",
				"cache_dirty",
			);

		// vahetame key=>value paarid ära
		$ofields = array_flip($_ofields);
		
		// oid voib tulla naiteks serializetud objektist
		// anyhow, meil siin pole sellega midagi peale hakata, so nil.
		unset($arr["oid"]);

		global $uid, $lang_id,$SITE_ID;
		
		// Nende väärtustega kirjutame üle whatever asjad $arr sees olid
		$_localdata = array(
			"createdby" => $uid,
			"created" => time(),
			"modifiedby" => $uid,
			"modified" => time(),
			"lang_id" => $lang_id,
			"site_id" => $SITE_ID,
		);

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
		
		$q = "SELECT MAX(oid) AS oid FROM objects WHERE createdby = '$uid'";
		$oid = $this->db_fetch_field($q,"oid");
		$this->db_query("INSERT INTO hits (oid,hits) VALUES ($oid,0)");

		if ($oid > 0 && $add_acl)
		{
			$this->create_obj_access($oid);
		};
		$this->flush_cache();
		return $oid;
	}

	////
	// !kirjutab objekti kustutatux
	function delete_object($oid)
	{
		global $uid;
		$t = time();
		$q = "UPDATE objects
			SET status = 0,
			    modified = '$t',
			    modifiedby = '$uid'
			WHERE oid = '$oid'";
		$this->_log("OBJECT","$oid kustutati");
		$this->db_query($q);
	}

	///
	// !Margib koik saidi objektid dirtyks
	function flush_cache()
	{
		$q = "UPDATE objects SET cachedirty = 1 WHERE objects.site_id = ".$GLOBALS["SITE_ID"]." or objects.site_id IS NULL";
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
			$this->raise_error("UPD_OBJECT called without object id",0);
		};
		$params["modifiedby"] = UID;
		$params["modified"] = time();
		$params["cachedirty"] = 1;
		$this->quote($params);
		$q_parts = array(); // siia sisse paneme päringu osad
		while(list($k,$v) = each($params)) {
			if ($k != "oid") {
				$q_parts[] = " $k = '$v' ";
			};
		};
		$q = " UPDATE objects SET " . join(",",$q_parts) . " WHERE oid = $params[oid] ";
		$this->db_query($q);
		// see on siis see vinge funktsioon
		$this->flush_cache();
	}

	////
	// !Tagastab koik mingist nodest allpool olevad objektid
	// seda on mugav kasutada, kui tegemist on suhteliselt
	// vaikse osaga kogu objektihierarhiast, ntx kodukataloog
	// parent(int) - millisest nodest alustame?
	// class(int) - milline klass meid huvitab?
	function get_objects_below($args = array())
	{
		extract($args);
		$groups = array();
		$this->get_objects_by_class(array(
					"parent" => $parent,
					"class" => $class,
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
	function cache_dirty($oid)
	{
		if (isset($GLOBALS["cahe_dirty_cache"][$oid]))
		{
			return $GLOBALS["cahe_dirty_cache"][$oid];
		}
		else
		{
			$q = "SELECT cachedirty FROM objects WHERE oid = '$oid'";
			$this->db_query($q);
			$row = $this->db_next();
			$GLOBALS["cahe_dirty_cache"][$oid] = $row["cachedirty"];
			return ($row["cachedirty"] == 1) ? true : false;
		}
	}

	////
	// !sets objects $oid's cache dirty flag to false
	function clear_cache($oid)
	{
		$q = "UPDATE objects SET cachedirty = 0 WHERE oid = '$oid'";
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

		$q = "INSERT INTO aliases (source,target,type,data)
			VALUES('$source','$target','$target_data[class_id]','$extra')";

		$this->db_query($q);
		$this->_log("alias","Lisas objektile $source aliase");
	}

	////
	// !deletes alias $target from object $source
	function delete_alias($source,$target)
	{
		$this->db_query("DELETE FROM aliases WHERE source = '$source' AND target = '$target'");
	}

	////
	// !koostab aliaste nimekirja objekti jaoks
	function get_aliases_for($oid,$type = -1,$sortby = "", $order = "",$join = "") 
	{
		global $awt;
		$awt->start("core->get_aliases_for()");
		$ss = "";
		if ($type != -1)
		{
			$ss = " AND aliases.type = $type ";
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
		$awt->stop("core->get_aliases_for()");
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
		global $awt;
		$awt->start("parse_aliases");
		extract($args);
		// tuleb siis teha tsykkel yle koigi registreeritud regulaaravaldiste
		foreach($this->parsers->reglist as $pkey => $parser)
		{
			// itereerime seni, kuni see äsjaleitud regulaaravaldis enam ei matchi.
			while(preg_match($parser["reg"],$text,$matches))
			{
				// siia tuleb tekitada mingi if lause, mis 
				// vastavalt sellele kas parserchain on defineeritud voi mitte, kutsub oige asja välja
				#$replacement = "";
				if (sizeof($parser["parserchain"] > 0))
				{
					foreach($parser["parserchain"] as $skey => $sval)
					{
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
							);
							$replacement = $this->parsers->$cls->$fun($params);
						};
					};
				};
				
				$text = preg_replace("/$matches[0]/",$replacement,$text);
				$replacement = "";
			};
		};
		$awt->stop("parse_aliases");
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
				print "not an object";
				die;
			};
		};
		$parent = $oid;
		$chain = array();
		while($parent > $rootobj) 
		{
			$q = "SELECT menu.*,objects.* FROM menu LEFT join objects on (menu.id = objects.oid)
				WHERE id = '$oid'";
			$this->db_query($q);
			$row = $this->db_fetch_row();
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
				$chain[$row["oid"]] = $row;
				$oid = $row["parent"];
			};
		};
		return $chain;
	}

	////
	// !tagastab objekti
	function get_object($arg) 
	{
		global $objcache;
		if (is_array($arg))
		{
			$oid = $arg["oid"];
			$class_id = $arg["class_id"];
		}
		else
		{
			$oid = $arg;
		};

		if (!isset($objcache[$oid]))
		{
			$objcache[$oid] = $this->get_record("objects","oid",$oid);
		}

		if (isset($class_id) && ($objcache[$oid]["class_id"] != $class_id) )
		{
			// objekt on valest klassist
			$this->raise_error("get_object: $oid ei ole tüüpi $class_id",true);
		}

		return $objcache[$oid];
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
	// parent(id)(opt) - kui defineeritud, siis loeb ainult objekte selle parenti all
	function get_objects_by_class($args = array())
	{
		extract($args);
		// kui parent on antud, siis moodustame sellest IN klausli
		$pstr = ($parent) ? " AND parent IN (" . join(",",map("'%s'",$parent)) . ")" : "";
		// see groyp by jaab mulle natuke segaks tekalt. oidid ju ei kordu eniveis, so what's the point?
		$this->db_query("SELECT objects.*
					FROM objects
					WHERE class_id = $class AND status != 0 $pstr
					GROUP BY objects.oid");
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
	function raise_error($msg, $fatal = false, $silent = false) 
	{
		$this->errmsg[] = $msg;
		$this->errorlevel++;

		$this->_log("error",$msg);	
		if ($silent)
		{
			return;
		};

		if ($fatal)
		{
			die("<br><b>AW_ERROR: $msg</b><br>");
		};
	}

	////
	// !finds the lead template for menu $section
	// if the template is not set for this menu, traverses the object tree upwards 
	// until it finds a menu for which it is set
	function get_lead_template($section)
	{
		global $awt,$lead_template_cache;
		$awt->start("core->get_lead_template()");
		$awt->count("core->get_lead_template()");
		if ($lead_template_cache[$section] != "")
		{
			$template = $lead_template_cache[$section];
		}
		else
		{
			do { 
				$section = (int)$section;
				$this->db_query("SELECT template.filename AS filename, objects.parent AS parent FROM menu LEFT JOIN template ON template.id = menu.tpl_lead LEFT JOIN objects ON objects.oid = menu.id WHERE menu.id = $section", "filename");
				$row = $this->db_next();
				$template = isset($row["filename"]) ? $row["filename"] : "";
				$section = $row["parent"];
			} while ($template == "" && $section > 1);
			$GLOBALS["lead_template_cache"][$section] = $template;
		}
		$awt->stop("core->get_lead_template()");
		return $template;
	}

	////
	// !finds the full document template for menu $section
	// if the template is not set for this menu, traverses the object tree upwards 
	// until it finds a menu for which it is set
	function get_long_template($section)
	{
		global $awt;
		$awt->start("core->get_long_template()");
		$awt->count("core->get_long_template()");
		// chekime et kui ette anti dokument, siis ei hakkax seda menyy tabelist otsima
		$obj = $this->get_object($section);	
		if ($obj["class_id"] == CL_PERIODIC_SECTION || $obj["class_id"] == CL_DOCUMENT)
			$section = $obj["parent"];

		do { 
			$section = (int)$section;
			$this->db_query("SELECT template.filename AS filename, objects.parent AS parent FROM menu LEFT JOIN template ON template.id = menu.tpl_view LEFT JOIN objects ON objects.oid = menu.id WHERE menu.id = $section", "filename");
			$row = $this->db_next();
			$template = isset($row["filename"]) ? $row["filename"] : "";
			$section = $row["parent"];
		} while ($template == "" && $section > 1);
		$awt->stop("core->get_long_template()");
		return $template;
	}

	////
	// !finds the edit template for menu $section
	// if the template is not set for this menu, traverses the object tree upwards 
	// until it finds a menu for which it is set
	function get_edit_template($section)
	{
		do { 
			$section = (int)$section;
			$this->db_query("SELECT template.filename AS filename, objects.parent AS parent FROM menu LEFT JOIN template ON template.id = menu.tpl_edit LEFT JOIN objects ON objects.oid = menu.id WHERE menu.id = $section");
			$row = $this->db_next();
			$template = $row["filename"];
			$section = $row["parent"];
		} while ($template == "" && $section > 1);
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
		}
		else
		{
			printf(E_ACCESS_DENIED2);
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
	function mk_my_orb($fun,$arr=array(),$cl_name="",$force_admin = false)
	{
		global $ext;
		if ($cl_name == "")
		{
			$cl_name = get_class($this);
		}

		// handle arrays!
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
				$str = join("&",$stra);
			}
			else
			{
				$str = $k."=".$v;
			}
			$ura[] = $str;
		}
		$urs = join("&",$ura);

		// now figure out if we are in the admin interface. 
		// how do we do that? easy :) we check the url for $baseurl/automatweb :)
		// aga mis siis, kui me mingil hetkel tahame, et automatweb oleks teisel
		// url-il? Ntx www.kirjastus.ee/pk/automatweb juures see ei tööta. - duke
		if ((stristr($GLOBALS["REQUEST_URI"],"/automatweb")!=false) || $force_admin)
		{
			// admin side.
			return $GLOBALS["baseurl"]."/automatweb/orb.$ext?class=$cl_name&action=$fun&$urs";
		}
		else
		{
			// user side
			return $GLOBALS["baseurl"]."/?class=$cl_name&action=$fun&$urs";
		}
	}

	////
	// !old version of orb url maker, use mk_my_orb instead 
	// kui user = 1, siis suunatakse tagasi Saidi sisse. Now, I do realize that this is not
	// the best solution, but for now, it works

	// Kas seda kasutatkse veel? - duke
	function mk_orb($fun,$arr, $cl_name = "",$user = "")
	{
		global $ext;
		if ($cl_name == "")
			$cl_name = get_class($this);

		$urs = join("&",$this->map2("%s=%s",$arr));

		if ($user)
		{
			if (strpos(HOMEDIR,"?") === false)
			{
				$sep = "?";
			}
			else
			{
				$sep = "&";
			}
			$url = HOMEDIR.$sep."id=" . $arr["parent"];
		}
		else
		{
			$url = "orb.$ext?class=$cl_name&action=$fun&$urs";
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
		global $ext;
		if (!isset($class))
		{
			$args["class"] = get_class($this);
		};
		$arguments = join("&",$this->map2("%s=%s",$args));
		global $baseurl;
		$retval = $baseurl . "/?" . $arguments;
		return $retval;
	}

	////
	// !creates the necessary hidden elements to put in a form that tell the orb which function to call
	function mk_reforb($fun,$arr,$cl_name = "")
	{
		global $ext;
		if ($cl_name == "")
			$cl_name = get_class($this);

		$urs = join("\n",$this->map2("<input type='hidden' name='%s' value='%s'>\n",$arr));
		if (!isset($arr["no_reforb"]) || $arr["no_reforb"] != true)
		{
			$url = "\n<input type='hidden' name='reforb' value='1'>\n";
		}
		$url .= "<input type='hidden' name='class' value='$cl_name'>\n<input type='hidden' name='action' value='$fun'>".$urs;
		return $url;
	}

	////
	// !creates a list of menus above $parent and appends $text and assigns it to the correct variable
	function mk_path($oid,$text = "",$period = 0)
	{
		global $ext;

		$path = "";
		$ch = $this->get_object_chain($oid);
		reset($ch);
		while (list(,$row) = each($ch))
		{
			$path="<a href='menuedit.$ext?parent=".$row["oid"]."&type=objects&period=".$period."'>".$row["name"]."</a> / ".$path;
		}

		$GLOBALS["site_title"] = $path.$text;
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
			$this->raise_error("get_file was called without filename",true);
		};
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
	// !fwrite wrapper
	function put_file($arr)
	{
		if (!$arr["file"])
		{
			$this->raise_error("put_file was called without filename",true);
		};
		$file = $arr["file"];
		if (!($fh = fopen($file,"w")))
		{
			$this->raise_error("Couldn't open file '$file' for writing",true);
		};
		fwrite($fh,$arr["content"]);
		fclose($fh);
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
			return $ret;
		}
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

		global $class_defs;
		$v = $class_defs[$obj["class_id"]];
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

		global $class_defs;
		$v = $class_defs[$s["class_id"]];
		if (!is_array($v))
		{
			return false;
		}

		classload($v["file"]);
		$t = new $v["file"];

		return $t->_unserialize(array("str" => $s["str"], "parent" => $parent, "period" => $period));
	}
};
?>
