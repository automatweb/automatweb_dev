<?php
// $Header: /home/cvs/automatweb_dev/classes/core.aw,v 2.285 2004/06/28 19:50:43 kristo Exp $
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
		$ccd = $this->db_fetch_field("SELECT cachedata FROM objects WHERE oid = '$oid'","cachedata");
		$dat = aw_unserialize($ccd);
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
	// !lisab objektile yhe vaatamise
	function add_hit($oid) 
	{
		if ($oid) 
		{
			$this->db_query("UPDATE hits SET hits=hits+1 WHERE oid = $oid");
		};
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

		$msg = "Suhtuge veateadetesse rahulikult!  Te ei ole korda saatnud midagi katastroofilist. Ilmselt juhib programm Teie t�helepanu mingile ebat�psusele  andmetes v�i n�puveale.<br /><br />\n\n".$msg." </b>";

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
			$us = get_instance("users");
			$udata = $us->get_user(array("uid" => $uid));
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
		error::throw(array(
			"id" => ERR_ACL,
			"msg" => "ACL error saidil ".aw_ini_get("baseurl")." ".sprintf(E_ACCESS_DENIED1,"CAN_".$right,$oid)
		));
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

		if ($arr["return_url"])
                {
                        // override whatever there was in the URL with our value
                        $arr["return_url"] = urlencode(aw_global_get("REQUEST_URI"));
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
		$obj = obj($oid);

		$tmp = aw_ini_get("classes");
		$v = $tmp[$obj->class_id()];
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
		$str = array("class_id" => $obj->class_id(), "str" => $s);
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
		$obj = obj($args['id']);
		return $obj->name();
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
