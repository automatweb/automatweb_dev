<?php
// $Header: /home/cvs/automatweb_dev/classes/core.aw,v 2.1 2001/05/16 03:03:48 duke Exp $
/*       _\|/_
         (o o)
 +----oOO-{_}-OOo----------------------------------+
 |          AW Foundation Classes                  |
 |          (C) StruktuurMeedia 2000,2001          |
 +------------------------------------------------*/

// you are such a microsoft's bitch duke :)

include("$classdir/connect.$ext");

class core extends db_connector
{
	var $errmsg;		

	function core()
	{
		// globaalsed muutujad impordime siin, niimoodi saab vältita koiki neid globaalsete
		// muutujate sissetoomist all over the field.

		// ja seda ka kuniks meil paremat lahendust pole
		global $basedir;
		$this->basedir = $basedir;
	}

	// abifunktsioonid
	// fetchib kirje suvalisest tabelist
	function get_record($table,$field,$selector)
	{
		$q = "SELECT * FROM $table WHERE $field = '$selector'";
		$this->db_query($q);
		return $this->db_fetch_row();
	}

	// kustutab kirje mingist tabelist. Kahtlane värk
	function dele_record($table,$field,$selector)
	{
		$q = "DELETE FROM $table WHERE $field = '$selector'";
		$this->db_query($q);
	}

	// fetch a config key
	function get_cval($ckey)
	{
		$q = sprintf("SELECT content FROM config WHERE ckey = '%s'",$ckey);
		return $this->db_fetch_field($q,"content");
	}

	// järgmised 4 funktsiooni on vajalikud ntx siis, kui on tarvis ühe objekti
	// juures korraga 2 või enamat päringut kasutada. Enne uue sisestamist tuleb
	// siis lihtsalt vana handle ära seivida
	// -------------------------------------------------------------------------
	// samas, seda saab kindlasti ka paremini teha, ntx kasutada
	// sissehitatud pinu handlete jaoks
        // get query handle
	function get_handle()
	{
		return $this->qID;
	}

	// set query handle
	function set_handle($qID)
	{
		$this->qID = $qID;
	}

	// need on tegelikult analoogid eelmisele, aga kasutavad objekti sisseehitatud
	// pinu qID salvestamiseks
	function save_handle()
	{
		$this->_push($this->qID,"qID");
	}

	function restore_handle()
	{
		$this->qID = $this->_pop("qID");
	}
	
	// write to syslog. the other way.
	// nimelt uid-d pole ju vaja igalt poolt siia ette anda, selle voime
	// globaalsest skoobist importida. Aga kuna log_action funktsiooni
	// kasutatakse nii paljudes kohtades, siis tegin uue funktsiooni
	function _log($type,$action,$oid = 0)
	{
		global $uid;
		global $REMOTE_ADDR,$HTTP_X_FORWARDED_FOR;
		$ip = $HTTP_X_FORWARDED_FOR;
		if ($ip == "" || !(strpos($ip,"unknown") === false))
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
		
	// write to syslog
	// DEPRECATED
	function log_action($uid,$type,$action)
	{
		global $REMOTE_HOST;
		global $REMOTE_ADDR;
		$ip = "$REMOTE_HOST / $REMOTE_ADDR";
		$when = time();
		$this->quote($action);
		$q = "INSERT DELAYED INTO syslog (syslog.tm,uid,type,action,ip) VALUES ('$when','$uid','$type','$action','$ip')";
		$this->db_query($q);
	}

	// outputs debug information if $HTTP_SESSION_VARS["debugging"] is set
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
		if ($field)
		{
			$retval = $row[$field];
		}
		elseif ($row)
		{
			// inbox ja draft defauldivad kodukataloogida, kui neid pole määratud
			$inbox = $row["msg_inbox"] ? $row["msg_inbox"] : $row["home_folder"];
			$draft = $row["msg_draft"] ? $row["msg_draft"] : $row["home_folder"];
			$row["msg_inbox"] = $inbox;
			$row["msg_draft"] = $draft;
			$retval = $row;
		};
                return $row;	
	}

	////
	// !Tagastab formaaditud timestambi
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
			default:
				// 12:22 04-01
				$dateformat = "H:i d-m";

		};
		return ($timestamp) ? date($dateformat,$timestamp) : date($dateformat);
	}

	// tagastab objekti info aliase kaudu
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

	function get_gids_by_uid($uid)
	{
		$q = "SELECT gid FROM groupmembers WHERE uid = '$uid'";
		$this->db_query($q);
		$retval = array();
		while($row = $this->db_next())
		{
			$retval[] = $row[gid];
		};
		return $retval;
	}

	// kontrollib objekti olemasolu
	// DEPRECATED
	// mix deprecated? - terryf
	// see tagastab lihtsalt objekti oid - eeldusel, et see olemas on.
	// kui on, siis on vaja teha veel teine päring kogu objekti info saamiseks.
	// mottetu imho. get_object töötab täpselt sama hästi. - duke
	function object_exists($oid)
	{
		$q = "SELECT oid FROM objects WHERE oid = '$oid'";
		$this->db_query($q);
		$row = $this->db_fetch_row();
		return $row;
	}

	// improved version of the one below.
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
			if ($ofields[$key])
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
		$this->situ_tais();
		return $oid;
	}
	
	// improved version of the one below.
	// function new_object($arr,$add_acl = true)
	//{
		// kui arr pole array, siis voiks see funktsioon töö lopetama
	//	global $uid, $lang_id,$SITE_ID;
	//	$cols = array("createdby", "created", "modifiedby",
	//				"modified", "lang_id", "site_id");
	//	$vals = array("'$uid'",time(), "'$uid'", time(), "'$lang_id'",$SITE_ID);
	//	reset($arr);
	//	while (list($k,$v) = each($arr))
	//	{
	//		$cols[] = $k;
	//		$vals[] = "'".$v."'";
	//	};
		// oid on auto_increment tüüpi väli. Possible race condition
		#$oid = $this->db_fetch_field("SELECT MAX(oid) AS oid FROM objects","oid")+1;

	//	$q = "INSERT INTO objects ( " . join(",",$cols) . ") VALUES (" . join(",",$vals) . ")";
	//	$this->db_query($q);
		
	//	$q = "SELECT MAX(oid) AS oid FROM objects WHERE createdby = '$uid'";
	//	$oid = $this->db_fetch_field($q,"oid");
	//	$this->db_query("INSERT INTO hits (oid,hits) VALUES ($oid,0)");
	//
	//	if ($oid > 0 && $add_acl)
	//	{
	//		$this->create_obj_access($oid);
	//	};
	//	$this->situ_tais();
	//	return $oid;
	//}


	// registreerib uue objekti objektide tabelis
	// DEPRECATED, kasutab positsioneeritud parameetreid
	function register_object($parent,$name,$class,$comment = "",$status = 2,$last="",$visible = 1,$time = -1,$period = 0)
	{
		$cid = $class;
		$this->db_query("SELECT MAX(oid) AS oid FROM objects");
		$row = $this->db_next();
		$oid = $row[oid] + 1;
		$this->quote($comment);
		$this->quote($name);

		if ($this->period > 0)
		{
			$period = $this->period;
		} else {
			$period = 0;
		};
		
		global $uid, $lang_id,$SITE_ID; #sessiooni variaablitest loetud
		if ($time == -1)
			$time = time();
		// uued objektid on vaikimis aktiivsed
		$q = "INSERT INTO objects(oid,parent,name,createdby,class_id,created,period,
							modified,modifiedby,status,lang_id,comment,last,visible,site_id)
			VALUES('$oid','$parent','$name','$uid','$cid','$time',
							'$period',
							'$time','$uid','$status','$lang_id','$comment','$last','$visible','$SITE_ID')";
		$this->db_query($q);
		
		$this->db_query("INSERT INTO hits (oid,hits) VALUES ($oid,0)");

		if ($oid > 0)
			$this->create_obj_access($oid);
		$this->situ_tais();
		return $oid;		
	}
	
	// kirjutab objekti kustutatux
	function delete_object($oid)
	{
		global $uid;
		$t = time();
		$q = "UPDATE objects
			SET status = 0,
			    modified = '$t',
			    modifiedby = '$uid'
			WHERE oid = '$oid'";
		$this->log_action($uid,"object","$oid kustutati");
		$this->db_query($q);
	}

	// 'l337
	// see sai mingis erilises deprekahoos kirjutet. tegelt voiks ymber nimetada.
	//
	// gee, you think? - terryf
	function situ_tais()
	{
		$q = "UPDATE objects SET cachedirty = 1 WHERE objects.site_id = ".$GLOBALS["SITE_ID"]." or objects.site_id IS NULL";
		$this->db_query($q);
	}

	// uus ja parem objekti uuendamise funktsioon, votab andmed ette arrayst
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
		$this->situ_tais();
	}
	
	function update_object($oid, $name = "", $active = -1, $comment = -1) {
		global $uid;
		$t = time();	
		$sql = "UPDATE objects
				SET modified = $t,
				    modifiedby = '$uid'";
		if ($name != "") {
			$sql.=" , name = '$name' ";
		};

		if ($active != -1) {
			$sql.=" , status = $active ";
		}
		
		if ($comment != -1) 
			$sql.=" , comment = '$comment' ";
			
		$this->db_query($sql." WHERE oid = $oid");
		$this->situ_tais;
	}

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
			$GLOBALS["cahe_dirty_cache"][$oid] = $row[cachedirty];
			return ($row[cachedirty] == 1) ? true : false;
		}
  }

  function clear_cache($oid)
  {
		$q = "UPDATE objects SET cachedirty = 0 WHERE oid = '$oid'";
    $this->db_query($q);
  }

	// lisab objektile aliase
	// source on see objekt, mille juurde lingitakse
	// target on see, mida lingitakse
	// aliaste tabelisse paigutame ka klassi id, nii
	// peaks hiljem olema voimalik natuke kiirust gainida
	function add_alias($source,$target,$extra = "") 
	{
		$target_data = $this->get_object($target);

		$q = "INSERT INTO aliases (source,target,type,data)	VALUES('$source','$target','$target_data[class_id]','$extra')";

		$this->db_query($q);
		$this->log_action(UID,"alias","Lisas objektile $source aliase");
	}

	function delete_alias($source,$target)
	{
		$this->db_query("DELETE FROM aliases WHERE source = '$source' AND target = '$target'");
	}

	// koostab aliaste nimekirja objekti jaoks
	function get_aliases_for($oid,$type = -1,$sortby = "", $order = "",$join = "") 
	{
		global $awt;
		$awt->start("core->get_aliases_for()");
		if ($type != -1)
		{
			$ss = " AND aliases.type = $type ";
		}
		if ($sortby == "")
		{
			$sortby = "id";
		}
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
			$row[id] = $row[target];
			$aliases[]=$row;
		};
		$awt->stop("core->get_aliases_for()");
		return $aliases;
	}

	function get_aliases_of($oid) {
		$q = "SELECT *,objects.name as name,objects.parent as parent FROM aliases
			LEFT JOIN objects ON
			(aliases.source = objects.oid)
			WHERE target = '$oid' ORDER BY id";
		$this->db_query($q);
		$aliases = array();
		while($row = $this->db_next()) 
		{
			$aliases[]=array("type" => $row[type],"name" => $row[name], "data" => $row[data],"id" => $row[source],"parent" => $row[parent]);
		};
		return $aliases;
	}

	////
	// !Deletes all aliases for $oid
	function delete_aliases_of($oid)
	{
		$this->db_query("DELETE FROM aliases WHERE source = $oid");
	}

	function update_object_comment($oid,$comment) 
	{
		$this->quote($comment);
		$q = "UPDATE objects
			SET comment = '$comment'
			WHERE oid = '$oid'";
		$this->db_query($q);
	}

	// lisab objektile yhe vaatamise
  function add_hit($oid) 
	{
		if ($oid) 
		{
	    $this->db_query("UPDATE hits SET hits=hits+1 WHERE oid = $oid");
		};
  }
        
	function add_cache_hit($oid) 
	{
		if ($oid) 
		{
			$this->db_query("UPDATE hits SET cachehits=cachehits+1 WHERE oid = $oid");
		};
	}

	// noh, mida see funktsioon teeb, peaks olema ysnagi ilmne
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

	// tagastab objekti
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

		if (!$objcache[$oid])
		{
			$objcache[$oid] = $this->get_record("objects","oid",$oid);
		}

		if ($class_id && ($objcache[$oid]["class_id"] != $class_id) )
		{
			// objekt on valest klassist
			$this->raise_error("get_object: $oid ei ole tüüpi $class_id",true);
		}

		return $objcache[$oid];
	}

	// miski lame funktsioon. Aga praegu paremini ei oska. templateeditor pruugib seda
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

	// hmm .. miski kahtlane värk ... vbla polegi neid vaja?
	// fetchib objekti last välja ja unserializeb selle
	function get_last($oid) 
	{
		$row = $this->get_object($oid);
		return unserialize($row[last]);
	}

	// salvestab objekti last välja uue info
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
				$this->db_query("select template.filename as filename, objects.parent as parent from menu left join template on template.id = menu.tpl_lead left join objects on objects.oid = menu.id where menu.id = $section", "filename");
				$row = $this->db_next();
				$template = $row[filename];
				$section = $row[parent];
			} while ($template == "" && $section > 1);
			$GLOBALS["lead_template_cache"][$section] = $template;
		}
		$awt->stop("core->get_lead_template()");
		return $template;
	}

	function get_long_template($section)
	{
		global $awt;
		$awt->start("core->get_long_template()");
		$awt->count("core->get_long_template()");
		// chekime et kui ette anti dokument, siis ei hakkax seda menyy tabelist otsima
		$obj = $this->get_object($section);	
		if ($obj[class_id] == CL_PERIODIC_SECTION || $obj[class_id] == CL_DOCUMENT)
			$section = $obj[parent];

		do { 
			$this->db_query("select template.filename as filename, objects.parent as parent from menu left join template on template.id = menu.tpl_view left join objects on objects.oid = menu.id where menu.id = $section", "filename");
			$row = $this->db_next();
			$template = $row[filename];
			$section = $row[parent];
		} while ($template == "" && $section > 1);
		$awt->stop("core->get_long_template()");
		return $template;
	}

	function get_edit_template($section)
	{
		do { 
			$this->db_query("select template.filename as filename, objects.parent as parent from menu left join template on template.id = menu.tpl_edit left join objects on objects.oid = menu.id where menu.id = $section");
			$row = $this->db_next();
			$template = $row[filename];
			$section = $row[parent];
		} while ($template == "" && $section > 1);
		return $template;
	}

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
	function mk_my_orb($fun,$arr=array(),$cl_name="",$force_admin = false)
	{
		global $ext;
		if ($cl_name == "")
		{
			$cl_name = get_class($this);
		}
		$urs = join("&",$this->map2("%s=%s",$arr));

		// now figure out if we are in the admin interface. 
		// how do we do that? easy :) we check the url for $baseurl/automatweb :)
		if (substr($GLOBALS["REQUEST_URI"],0,11) == "/automatweb" || $force_admin)
		{
			// admin side.
			return "orb.$ext?class=$cl_name&action=$fun&$urs";
		}
		else
		{
			// user side
			return $GLOBALS["baseurl"]."?class=$cl_name&action=$fun&$urs";
		}
	}

	// kui user = 1, siis suunatakse tagasi Saidi sisse. Now, I do realize that this is not
	// the best solution, but for now, it works
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

	// eelmise analoog. Parameetrid votab arrayst
	function mk_site_orb($args = array())
	{
		extract($args);
		global $ext;
		if (!$class)
		{
			$args["class"] = get_class($this);
		};
		$arguments = join("&",$this->map2("%s=%s",$args));
		global $baseurl;
		$retval = $baseurl . "/?" . $arguments;
		return $retval;
	}

	function mk_reforb($fun,$arr,$cl_name = "")
	{
		global $ext;
		if ($cl_name == "")
			$cl_name = get_class($this);

		$urs = join("\n",$this->map2("<input type='hidden' name='%s' value='%s'>\n",$arr));
		$url = "\n<input type='hidden' name='reforb' value='1'>\n";
		$url .= "<input type='hidden' name='class' value='$cl_name'>\n<input type='hidden' name='action' value='$fun'>".$urs;
		return $url;
	}

	function mk_path($oid,$text = "",$period = 0)
	{
		global $ext;

		$ch = $this->get_object_chain($oid);
		reset($ch);
		while (list(,$row) = each($ch))
			$path="<a href='menuedit.$ext?parent=".$row[oid]."&type=objects&period=".$period."'>".$row[name]."</a> / ".$path;

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

	// fwrite wrapper
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

	// serializemise funktsioonid. 
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
