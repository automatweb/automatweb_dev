<?php
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

classload("core/obj/acl_base");
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

	/** This function writes an entry to the aw syslog table. This is automatically called for most actions, via classbase, but if you perform any special actions in your class, that should be logged, then use this function to log the action. 

		@attrib api=1

		@param type required type=int
			Log entry type. The types are defined in the ini file, in the syslog.types array. The type specifies the class in most cases. The prefix for syslog type define's is ST_, for instance ST_DOCUMENT

		@param action required type=int
			Log action type. The actions are defined in the ini file, in the syslog.actions array. The action specifies, what was done - for instance, change/add/delete. The prefix for syslog actions is SA_, for instance SA_ADD

		@param text required type=string
			the text of the log message. 

		@param oid optional type=int 
			defaults to 0 The object id of the object this action is about. 

		@param honor_ini optional type=bool
			if set to true, the disable logging ini setting will be ignored. 
	
		@comment
			The logging can be disabled by the ini setting logging_disabled

		@errors
			none

		@returns 
			none

		@examples
			$this->_log(ST_DOCUMENT, SA_ADD, "Added document $name", $docid);
	**/
	function _log($type,$action,$text,$oid = 0,$honor_ini = true)
	{
		if(aw_ini_get('logging_disabled') && $honor_ini)
		{
			return;
		}

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
			$values = array($t,aw_global_get("uid"),$type,$text,$ip,(int)$oid,$action,$ref);
			if (aw_ini_get("users.tafkap"))
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
			/*
				It seems that mssql doesn't support insert delayd syntax.
				We're on the safe side as long as AWs running on
				MSSQL have used the aw.ini directive logging_disabled.
				Im sure a more permanent fix will surface one day.
			*/
			$q = sprintf("INSERT INTO syslog (%s) VALUES (%s)",join(",",$fields),join(",",map("'%s'",$values)));

			if (!$this->db_query($q,false))
			{
				die("cannot write to syslog: " . $this->db_last_error["error_string"]);
			};
		}
	}

	/** Converts the given timestamp to text format. 
		@attrib api=1

		@param timestamp required
			The unix timestamp to convert to text format. 

		@param format optional
			The date format string identifier, from the ini file. These are defined in config.date_formats
	**/
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
	function flush_cache($oid = NULL)
	{
		if (aw_global_get("no_cache_flush") == 1)
		{
		return;
		}
		if (!aw_ini_get("cache.use_html_cache"))
		{
			return;
		}

		if (aw_global_get("old_cache_flushed"))
		{
			//return;
		}
		aw_global_set("old_cache_flushed", 1);
		if ($oid !== NULL)
		{
			$hoid = " AND oid = '$oid'";
		}

		if (aw_ini_get("cache.table_is_sep"))
		{
			$q = "UPDATE objects_cache_data SET cachedirty = 1, cachedata = '' ".($hoid != "" ? " WHERE ".$hoid : "");
			$this->db_query($q);
		}
		else
		{
			$q = "UPDATE objects SET cachedirty = 1, cachedata = '' where status != 0 ".$hoid;
			$this->db_query($q);
		}
	}

	////
	// !returns true if object $oid 's cahe dirty flag is set
	function cache_dirty($oid, $fname = "")
	{
		if (!aw_ini_get("cache.use_html_cache"))
		{
			return true;
		}

		if (aw_ini_get("cache.table_is_sep"))
		{
			$q = "SELECT cachedirty,cachedata FROM objects_cache_data WHERE oid = '$oid'";
		}
		else
		{
			$q = "SELECT cachedirty,cachedata FROM objects WHERE oid = '$oid'";
		}

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
		if (!aw_ini_get("cache.use_html_cache"))
		{
			return;
		}
		if (!is_oid($oid))
		{
			return;
		}

		if (aw_ini_get("cache.table_is_sep"))
		{
			$ccd = $this->db_fetch_field("SELECT cachedata FROM objects_cache_data WHERE oid = '$oid'","cachedata");
		}
		else
		{
			$ccd = $this->db_fetch_field("SELECT cachedata FROM objects WHERE oid = '$oid'","cachedata");
		}

		$dat = aw_unserialize($ccd);
		if ($fname != "")
		{
			$dat[$fname] = 1;
		}
		$ds = aw_serialize($dat);
		$this->quote($ds);


		if (aw_ini_get("cache.table_is_sep"))
		{
			$q = "UPDATE objects_cache_data SET cachedirty = 0 , cachedata = '$ds' WHERE oid = '$oid'";
		}
		else
		{
			$q = "UPDATE objects SET cachedirty = 0 , cachedata = '$ds' WHERE oid = '$oid'";
		}
		//XXX the following query was commented out on eau, have to watch out for this one

		$this->db_query($q);
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
			$this->parsers = new stdClass;
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

	/** Signals an error condition, displays it to the user if specified, sends an e-mail to the vead@struktuur.ee mailinglists and registers the error in the aw.struktuur.ee bugtrack. All relavant variables and a backtrace are displayed. If specified, also halts execution. 
		@attrib api=1

		@param err_type required
			The type of the error to throw. Error types are registered in the ini file, errors array. 

		@param msg required
			The error message. 

		@param fatal optional
			If true, execution is halted after the error is displayed. Defaults to false. 

		@param silent optional 
			If true, error is not displayed to the user. It is still sent to the list and reported to the error server. Defaults to false. 

		@param oid optional
			If set, must contain the oid of the object that the error is about. 

	**/
	function raise_error($err_type,$msg, $fatal = false, $silent = false, $oid = 0, $send_mail = true)
	{
		/*if (!$_SESSION["err_retry"])
		{
			$_SESSION["err_retry"] = 1;
			$c = get_instance("cache");
			$c->full_flush();
			header("Location: ".aw_ini_get("baseurl").aw_global_get("REQUEST_URI"));
			die();
		}*/
		if(aw_ini_get('raise_error.no_email'))
		{
			$send_mail = false;
		}

		$GLOBALS["aw_is_error"] = 1;

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


		//XXX die($msg) was here
		$consts = get_defined_constants();
		$v = "";
		foreach($consts as $c => $d)
		{
			if(!(strpos($c, "ERR_") !== false))
			{
				continue;
			}
			if($d == $err_type)
			{
				$v = "$c: ";
				break;
			}
		}
		// meilime veateate listi ka
		$subj = $v."Viga saidil ".$this->cfg["baseurl"];

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
		$content.= "section = ".$_REQUEST["section"]."\n";
		$content.= "url = ".$this->cfg["baseurl"].aw_global_get("REQUEST_URI")."\n-----------------------\n";
		$content.= "is_rpc_call = $is_rpc_call\n";
		$content.= "rpc_call_type = $rpc_call_type\n";
		$content .= "\n\nCookie vars\n";
		foreach($_COOKIE as $k => $v)
		{
			$content.="$k = $v \n";
		}
		$content .= "\n\nGet vars\n";
		foreach($_GET as $k => $v)
		{
			$content.="$k = $v \n";
		}
		$content .= "\n\nPost vars\n";
		foreach($_POST as $k => $v)
		{
			$content.="$k = $v \n";
		}
		$keys = array("DOCUMENT_ROOT","HTTP_ACCEPT_LANGUAGE","HTTP_HOST","HTTP_REFERER","HTTP_USER_AGENT","REMOTE_ADDR",
			"SCRIPT_FILENAME","SCRIPT_URI","SCRIPT_URL","REQUEST_METHOD","QUERY_STRING");
		$content.="\n\nHelpful server vars:\n\n";
		foreach($_SERVER as $k => $v)
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


		if ($err_type == 30 && strpos($_SERVER["HTTP_USER_AGENT"], "Microsoft-WebDAV-MiniRedir") !== false)
		{
			$send_mail = false;
		}

		if ($err_type == 30)
		{
			if (count($_GET) < 1 && count($_POST) < 1)
			{
				$send_mail = false;
			}
		}

		if ($err_type == 83 && aw_ini_get("site_id") == 543)
		{
			$send_mail = false;
		}

		// ifthe request is too big or b0rked
		if ($err_type == 30 && $_SERVER["REQUEST_METHOD"] == "POST" && count($_POST) == 0)
		{
			$send_mail = false;
		}

		if ($err_type == 31 && substr($_REQUEST["class"], -3) == "...")
		{
			$send_mail = false;
		}

		if ($err_type == 31 && strpos($_REQUEST["class"], "@") !== false)
		{
			$send_mail = false;
		}

		$si = __get_site_instance();
		if (is_object($si) && method_exists($si,"process_error"))
		{
			$send_mail = $si->process_error(array(
				"err_type" => $err_type,
				"content" => $content
			));
		}

		if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
		{
			$send_mail = false;
		}

		if ($_SERVER["REDIRECT_REQUEST_METHOD"] == "PROPFIND")
		{
			$send_mail = false;
		}

		// if error type is class not defined and get and post are empty, the orb.aw url was requested probably, no need ot send error
		if ($err_type == 30 && count($_GET) == 0 && count($_POST) == 0)
		{
			$send_mail = false;
		}

		if ($err_type == "ERR_IMAGE_FORMAT")
		{
			$send_mail = false;
		}

		$mh = md5($content);
		if ($_SESSION["last_mail"] == $mh && $_SESSION["last_mail_time"] > (time() - 60))
		{
			$send_mail = false;
		}

		if ($send_mail)
		{
			send_mail("vead@struktuur.ee", $subj, $content,$head);
			$_SESSION["last_mail"] = $mh;
			$_SESSION["last_mail_time"] = time();
		}

		// here we replicate the error to the site that logs all errors (usually aw.struktuur.ee)
		// we replicate by POST request, cause this thing can be too long for a GET request

		$class = $_REQUEST["class"];
		$action = $_REQUEST["action"];

		//XXX: watchout, on eau the following if block had a "false &&" part in it
		//i just deleted that, but for further testing i'm writing this comment
		//so i could find the place easily
		if (!($class == "bugtrack" && $action="add_error") && aw_ini_get("config.error_log_site") != "")
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
				$driver_inst = get_instance("core/orb/".$rpc_call_type);
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
				$seu = aw_ini_get("core.show_error_users");
				if ($seu == "")
				{
					$uid_arr = array();
				}
				else
				{
					$uid_arr = explode(",", $seu);
				}
				if ($u != "" && !headers_sent() && aw_ini_get("site_id") != 138 && !in_array(aw_global_get("uid"), $uid_arr) )
				{
					if (aw_global_get("uid") != "ahti")
					{	
						header("Location: $u");
						die();
					}
				}
				flush();
				die("<br /><b>".
					"AW_ERROR: $msg</b><br />\n\n<br />");
			}
		};
		aw_global_set("__from_raise_error",0);
	}

	////
	// !prints an error message about the fact that the user has no access to do this
	function acl_error($right, $oid)
	{
		error::raise(array(
			"id" => ERR_ACL,
			"msg" => sprintf(t("ACL error saidil %s: CAN_%s denied for oid %s"), aw_ini_get("baseurl"),$right, $oid)
		));
	}

	/** Creates orb links
		@attrib api=1

		@comment
			This function is documented in the orb specification. 

			the idea is this that it determines itself whether we go through the site (index.aw)
			or the orb (orb.aw) - for the admin interface
			you can force it to point to the admin interface
			this function also handles array arguments!
			crap, I hate this but I gotta do it - shoulda used array arguments or something -
			if $use_orb == 1 then the url will go through orb.aw, not index.aw - which means that it will be shown
			directly, without drawing menus and stuff
	**/
	function mk_my_orb($fun,$arr=array(),$cl_name="",$force_admin = false,$use_orb = false,$sep = "&",$honor_r_orb = true)
	{
		// resolve to name

		// kui on numeric, siis ma saan class_lut-ist teada tema nime
		if (is_numeric($cl_name))
		{
			$fx = array_search($cl_name,$GLOBALS["cfg"]["class_lut"]);
			if (isset($GLOBALS["cfg"]["__default"]["classes"][$cl_name]))
			{
				$cl_name = $GLOBALS["cfg"]["__default"]["classes"][$cl_name]["file"];
			}
		};

		$cl_name = ("" == $cl_name) ? get_class($this) : basename($cl_name);

		// tracked_vars comes from orb->process_request
		$this->orb_values = $GLOBALS["tracked_vars"];

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

		// figure out the request method once.
		static $r_use_orb;
		if (!isset($r_use_orb))
		{
			$r_use_orb = basename($_SERVER["SCRIPT_NAME"],".aw") == "orb";
		};

		if (!$honor_r_orb)
		{
			$r_use_orb = false;
		}

		$in_admin = $GLOBALS["cfg"]["__default"]["in_admin"];

		// XXX: admin_folders sets use_empty directly, but shouldn't.
		if (empty($this->use_empty))
		{
			$this->use_empty = false;
		};

		if (isset($arr["return_url"]))
		{
			$arr["return_url"] = substr($arr["return_url"], 0, 3500);
			if ($arr["return_url"] == "")
			{
				unset($arr["return_url"]);
			}
		}

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

	/** creates the necessary hidden elements to put in a form that tell the orb which function to call
		@attrib api=1

		@comment
			This function is documented in the orb specification. 
	**/
	function mk_reforb($fun,$arr = array(),$cl_name = "")
	{

		$cl_name = ("" == $cl_name) ? get_class($this) : basename($cl_name);

		// tracked_vars comes from orb->process_request
		$this->orb_values = $GLOBALS["tracked_vars"];
		$this->orb_values["class"] = $cl_name;
		$this->orb_values["action"] = $fun;

		if (empty($arr["no_reforb"]))
		{
			$this->orb_values["reforb"] = 1;
		};

		$this->use_empty = true;

		// flatten is not the correct term!
		$this->process_orb_args("",$arr, false);
		$res = "";
		foreach($this->orb_values as $name => $value)
		{
			$value = str_replace("\"","&amp;",$value);
			$res .= "<input type='hidden' name='$name' value='$value' />\n";
		};
		return $res;
	}

	function process_orb_args($prefix,$arr, $enc = true)
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
					if ($enc)
					{
						$value = urlencode($value);
					}
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

	/** Creates a link from the $args array and returns it. 

		@param args optional type=array
			An array of key => value pairs. These are inserted as key => value pairs in the result url. 

		@param skip_empty optional type=bool
			If true, empty values are not inserted into the result url.
	**/
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
		if ($this->can("view", $oid))
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

	/** Returns the contents of the given file. If file is not found, false is returned. 
		@attrib api=1

		@param file required type=string
			The full path of the file whose contents must be returned. 

		@errors 
			none

		@examples
			echo $this->get_file(array("file" => aw_ini_get("basedir")."/init.aw"));
	**/
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

	/** Writes a file. 
		@attrib api=1

		@param file required type=string
			The full path of the file to write to. 

		@param content requires type=string
			The content of the file. 

		@errors
			error is thrown if no file is given or file cannot be written to

		@returns 
			true if file was written

		@examplex
			$this->put_file(array(
				"file" => "/www/foo",
				"content" => "allah"
			));
	**/
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
		// "b" is for os-indepence, winblowsil on huvitav omadus isiklikke reavahetusi kasutada
		if (not(($fh = fopen($file,"wb"))))
		{
			$this->raise_error(ERR_CORE_NOP_OPEN_FILE,sprintf(t("Couldn't open file '%s' for writing"), $file),true);
		}
		else
		{
			fwrite($fh,$arr["content"]);
			fclose($fh);
		}
		// actually this should return a boolean value and an error message should
		// be stored somewhere inside the class.
		return true;
	}

	/** Retrieves a list of files in a directory
		@attrib api=1

		@param dir required type=string
			The directory on the server whose contents to return.

		@errors
			none
	**/
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

	/** converts all characters of string $str to their hex representation and returns the resulting string
		@attrib api=1

		@param str required type=string
			The string to convert. 

		@errors
			none

		@returns
			the given string, in hex character codes
		
		@examples
			echo $this->binhex("abx"); // echos 616263
			echo $this->hexbin($this->binhex("abc"));	// echos abc
	**/
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

	/** opposite of binhex, decodes a string of hex numbers to their values and creates a string from them
		@attrib api=1

		@param str required type=string
			The string to convert. 

		@errors
			none

		@returns
			the given string, converted back to the original text
		
		@examples
			echo $this->binhex("abx"); // echos 616263
			echo $this->hexbin($this->binhex("abc"));	// echos abc
	**/
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

		$clid = clid_for_name($file);
		$t = get_instance($clid ? $clid : $file);
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

		if ($rootobj == -1)
		{
			$rootobj = cfg_get_admin_rootmenu2();
		}

		$ot = new object_tree(array(
			"class_id" => CL_MENU,
			"parent" => $rootobj,
			"status" => ($onlyact ? STAT_ACTIVE : array(STAT_NOTACTIVE, STAT_ACTIVE)),
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

	/** executes an orb function call and returns the data that the function returns

		@attrib api=1

		@param action required type=string
			orb action to exec

		@param class optional type=string
			class for the action - default the current class

		@param params optional type=array
			params to the action 

		@param method optional type=string
			the method to use when doing the function call - possible values: local / xmlrpc / (soap - not implemented yet) 

		@param server optional type=string
			if doing a rpc call, the server where to connect

		@param login_obj optional type=int
			if we must log in to a server the id of the CL_AW_LOGIN that will be used to login to the server. if this is set, then server will be ignored

		@comment
			Further information can be found in the orb specification
	**/
	function do_orb_method_call($arr)
	{
		extract($arr);

		if (!$arr["class"])
		{
			$arr["class"] = get_class($this);
		}

		$ob = get_instance("core/orb/orb");
		return $ob->do_method_call($arr);
	}

	/** this takes an array and goes through it and makes another array that has as keys the values of the given array and also the velues of the given array
		@attrib api=1

		@param arr required type=array
			The array to convert. 

		@examples
			$arr = array("a", "b", "c");
			echo dbg::dump($arr);	
			// echos:
			array(3) {
			  [0]=>
			  string(1) "a"
			  [1]=>
			  string(1) "b"
			  [2]=>
			  string(1) "c"
			}	
	**/
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

};
?>
