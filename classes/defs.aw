<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/defs.aw,v 2.65 2002/12/03 11:18:01 kristo Exp $
// defs.aw - common functions 
if (!defined("DEFS"))
{
	define("DEFS",1);

	define("SERIALIZE_PHP",1);
	define("SERIALIZE_XML",2);
	define("SERIALIZE_NATIVE",3);
	define("SERIALIZE_PHP_NOINDEX",4);
	define("SERIALIZE_XMLRPC", 5);

	classload("xml","php");

	////
	// !places <a href tags around urls and e-mail addresses in text $src
	function create_links($src)
	{
		$src = preg_replace("/((\W|^))((http(s?):\/\/)|(www\.))(\S+)/im", "$2<a href=\"http$5://$6$7\" target=\"_blank\">$4$6$7</a>", $src);
		return preg_replace("/((\s|^)+)(\S+)@(\S+)/","\\2<a href='mailto:\\3@\\4'>\\3@\\4</a>", $src);
	}

	////
	// !parses template source in $src, and replaces variables in it with variables from $vars
	function localparse($src = "",$vars = array())
	{
		// kogu asendus tehakse ühe reaga
		// "e" regexpi lõpus tähendab seda, et teist parameetrit käsitletakse php koodina,
		// mis eval-ist läbi lastakse. 
		$src = preg_replace("/{VAR:(.+?)}/e","\$vars[\"\\1\"]",$src);
		return preg_replace("/{INI:(.+?)}/e","aw_ini_get(\"\\1\")",$src);
	}

	//// 
	// !Parsib XML formaadis datat, s.t. laseb selle läbi PHP xml_parse_into_struct funktsiooni
	// ja tagastab $values ja $keys arrayd
	// Parameetrid:
	// xml - data
	function parse_xml_def($args)
	{
		// loome parseri
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		$values = array();
		$tags = array();
		// xml data arraysse
		xml_parse_into_struct($parser,$args["xml"],&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);
		return array($values,$tags);
	};

	////
	// !replaces the number with the corresponding string, 
	// if number < 11, otherwise returns the number. the strings are localized
	function verbalize_number($number)
	{
		$strings = array("0",LC_D1,LC_D2,LC_D3,LC_D4,LC_D5,LC_D6,LC_D7,LC_D8,LC_D9,LC_D10);
		if ( ($number > 10) || ($number < 0))
		{
			$ret = $number;
		} 
		else 
		{
			$ret = $strings[$number];
		};
		return $ret;
	}

	////
	// !tagastab lokaliseeritud kuunime numbri järgi
	function get_lc_month($id)
	{
		$mnames = explode("|",LC_MONTH);
		return $mnames[(int)$id];
	}

	////
	// !tagastab lokaliseeritud päevanime
	function get_lc_weekday($id)
	{
		$daynames = explode("|",LC_WEEKDAY);
		return $daynames[$id];
	}

	////
	// !creates html linebreaks in text
	// the difference with nl2br is, that nl2br inserts html tags before linebreaks
	// but this replaces linebreaks with html tags
	function format_text($text)
	{
		$text = str_replace("\n\n","<p>",$text);
		$text = str_replace("\n","<br>",$text);
		return $text;
	}

	////
	// !checks if the string $string is a valid $set
	// the currently supported sets are password and uid
	// usage: if (is_valid("password",$pass_entered_in_a_form))
	function is_valid($set,$string)
	{
		$sets = array(
			'password' => array(
				'content' => '1234567890qwertyuiopasdfghjklzxcvbnm_QWERTYUIOPASDFGHJKLZXCVBNM',
				'min' => 4,
				'max' => 32),
			'uid'	=> array(
				'content'	=> '1234567890qwertyuiopasdfghjklzxcvbnm_QWERTYUIOPASDFGHJKLZXCVBNM',
				'min' => 3,
				'max' => 30)
			);
		// defineerimata character set, bail out	
		if (!isset($sets[$set]))
		{
			return false;
		};
		$len = strlen($string);
		if ($len < $sets[$set]['min'] || $len > $sets[$set]['max'])
		{
			return false;
		}
		if (strspn($string,$sets[$set]['content']) != $len)
		{
			return false;
		}
		return true;
	}

	////
	// !Kas argument on integer?
	function is_number($parm)
	{
		$intparm = (int)$parm;
		if (strlen($intparm) == strlen($parm))
		{
			return true;
		}
		return false;
	}

	////
	// !Genereerib md5 hashi kas parameetrist voi suvalisest arvust.
	function gen_uniq_id($param = "")
	{
		// genereerib md5 checksumi kas siis parameetrist voi 
		// juhuslikust arvust
		// md5sum on alati 32 märki pikk
		$src = (strlen($param) > 0) ? $param : uniqid(rand());
		$result = md5($src);
		return $result;
	};

	////
	// !Kasutamiseks vormides checkboxide kuvamise juures, vastavalt argumendi tõeväärtusele
	// tagastab kas stringi "checked" voi tühja stringi.
	function checked($arg)
	{
		return ($arg) ? "CHECKED" : "";
	}

	////
	// !Kasutamiseks vormides listboxide juures, vastavalt argumendi tõeväärtusele
	// tagastab kas stringi "selected" voi tühja stringi
	function selected($arg)
	{
		return ($arg) ? "SELECTED" : "";
	}
	
	////
	// !Kasutamiseks vormides elementide juures, mis võivad olla disabled olekus
	function disabled($arg)
	{
		return ($arg) ? "DISABLED" : "";
	}

	////
	// !kontrollime, kas parameeter on kuupäev (kujul pp-kk-aaaa)
	function is_date($param)
	{
		$valid = preg_match("/^(\d{1,2}?)-(\d{1,2}?)-(\d{4}?)$/",$param,$parts);
		// päevade arv < 0 ?
		if ($parts[1] < 0)
		{
			$valid = false;
		}
		// päevi rohkem, kui selles kuus?
		elseif ($parts[1] > date("t",mktime(0,0,0,$parts[2],1,$parts[3])) )
		{
			$valid = false;
		}
		elseif ( ($parts[2] < 1) || ($parts[2] > 12) )
		{
			$valid = false;
		} 
		//elseif ( ($parts[3] < 2000) || ($parts[3] > 2001) )
		//{
		//	$valid = false;
		//};
		return $valid;
	};

	////
	// !Genereerib XML headeri
	function gen_xml_header($version = "1.0") 
	{
			return "<" . "?xml version='$version'?" . ">\n";
	};

	////
	// !Genereerib XML tagi
	function gen_xml_tag($name,$data) 
	{
		if (is_array($data)) 
		{
			$params = join(" ",map2(" %s='%s'",$data));
		}
		else 
		{
			$params = "";
		};
		$retval = sprintf("<%s%s/>\n",$name,$params);
		return $retval;
	}

	// järgmine funktsioon on inspireeritud perlist ;)
	// kasutusnäide:
	//       print $object->map("--- %s ---\n",array("1","2","3"));
	// tulemus:
	//      --- 1 ---
	//      --- 2 ---
	//      --- 3 ---
			
	function map($format,$array)
	{
		$retval = array();
		if (is_array($array))
		{
			while(list(,$val) = each($array))
			{
				$retval[]= sprintf($format,$val,$val,$val);
			};
		}
		else
		{
			$retval[]= sprintf($format,$array);
		};
		return $retval;
	}

	// sama, mis eelmine, ainult et moodustuvad paarid
	// array iga elemendi indeksist ja väärtusest
	// format peab siis sisaldama vähemalt kahte kohta muutujate jaoks

	// kui $type != 0, siis pööratakse array nö ringi ... key ja val vahetatakse ära	
	function map2($format,$array,$type = 0,$empty = false)
	{
		$retval = array();
		if (is_array($array))
		{
			while(list($key,$val) = each($array))
			{
				if ($type == 0)
				{
					$v1 = $key;
					$v2 = $val;
				}
				else
				{
					$v1 = $val;
					$v2 = $key;
				};
				if ((strlen($v1) > 0) && (strlen($v2) > 0) || $empty)
				{
					$retval[] = sprintf($format,$v1,$v2);
				};
			};
		}
		else
		{
			if ($array)
			{
				$retval[] = sprintf($format,$array);
			};
		};
		return $retval;
	}

	function jerk_alert($contents) 
	{
		$to = "log@struktuur.ee";
		$subject = "Jerk alert!";
		$headers = "From: AK veebiserver <nobody@heaven.eestiajakirjad.ee>";
		mail($to,$subject,$contents,$headers);
	};

	////
	// !hiljem voib siia turvakontrolli kylge ehitada
	function get_file($arr)
	{
		if (!$arr["file"])
		{
			die("defs->get_file was called without filename");
		};

		if (!($fh = @fopen($arr["file"],"r")))
		{
			$retval = false;
			die("Couldn't open file '$arr[file]'");
		}
		else
		{
			$retval = fread($fh,filesize($arr["file"])); // SLURP
			fclose($fh);
		};
		return $retval;
	}

	////
	// !returns the ip address of the current user. tries to bypass the users cache if it can
	function get_ip()
	{
		$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
		if (!inet::is_ip($ip))
		{
			$ip = aw_global_get("REMOTE_ADDR");
		}
		return $ip;
	}

	////
	// !Kas argument on e-maili aadress?
	// Courtesy of martin@linuxator.com ;)
	function is_email ($address = "") 
	{
		return preg_match('/([a-z0-9]((\.|_)?[a-z0-9]+)+@([a-z0-9]+(\.|-)?)+[a-z0-9]\.[a-z]{2,})/i',$address);
	}

	function is_admin()
	{
		return (stristr(aw_global_get("REQUEST_URI"),"/automatweb")!=false);
	}

	// debuukimisel on see funktsioon abiks
	function dump_struct($data)
	{
		print "<pre>";
		var_dump($data);
		print "</pre>";
	}

	////
	// !prints the message if $GLOBALS["DEBUG"] == 1, basically the same as core::dmsg, but with this you don't need to fiddle
	// around with sessions
	function dbg($msg)
	{
		if (aw_global_get("DEBUG") == 1)
		{
			echo $msg;
		}
	}
	
	// prints if the user has a cookie named debug1
	function dbg1($msg)
	{
		if (isset($GLOBALS["HTTP_COOKIE_VARS"]["debug1"]))
		{
			echo $msg;
		}
	}

	// prints if the user has a cookie named debug2
	function dbg2($msg)
	{
		if (isset($GLOBALS["HTTP_COOKIE_VARS"]["debug2"]))
		{
			echo $msg;
		}
	}

	// prints if the user has a cookie named debug3
	function dbg3($msg)
	{
		if (isset($GLOBALS["HTTP_COOKIE_VARS"]["debug3"]))
		{
			echo $msg;
		}
	}

	// prints if the user has a cookie named debug4
	function dbg4($msg)
	{
		if (isset($GLOBALS["HTTP_COOKIE_VARS"]["debug4"]))
		{
			echo $msg;
		}
	}

	// prints if the user has a cookie named debug5
	function dbg5($msg)
	{
		if (isset($GLOBALS["HTTP_COOKIE_VARS"]["debug5"]))
		{
			echo $msg;
		}
	}

	// logs the query, if user has a cookie named log_query
	function log_query($msg)
	{
		$uid = aw_global_get("uid");
		$logfile = "/www/log/mysql-" . $uid . ".log";

		if (isset($GLOBALS["HTTP_COOKIE_VARS"]["log_query"]))
		{
			$fp = fopen($logfile,"a");
			flock($fp, LOCK_EX);
			fwrite($fp,$msg . "\n\n-----------------------------------\n\n");
			flock($fp, LOCK_UN);
			fclose($fp);
		}
	}


	function aw_serialize($arr,$type = SERIALIZE_PHP, $flags = array())
	{
		switch($type)
		{
			case SERIALIZE_PHP:
				$ser = new php_serializer;
				foreach($flags as $fk => $fv)
				{
					$ser->set($fk, $fv);
				}
				$str = $ser->php_serialize($arr);
				break;
			case SERIALIZE_PHP_NOINDEX:
				$ser = new php_serializer;
				$ser->set("no_index",1);
				$str = $ser->php_serialize($arr);
				break;

			case SERIALIZE_XML:
				$ser = new xml($flags);
				$str = $ser->xml_serialize($arr);
				break;

			case SERIALIZE_NATIVE:
				$str = serialize($arr);
				break;

			case SERIALIZE_XMLRPC:
				$ser = get_instance("orb/xmlrpc");
				$str = $ser->xmlrpc_serialize($arr);
		}

		return $str;
	}

	function aw_unserialize($str,$dequote = 0)
	{
		if ($dequote)
		{
			$str = stripslashes($str);
		};

		if (substr($str,0,14) == "<?xml version=")
		{
			$x = new xml;
			$retval = $x->xml_unserialize(array("source" => $str));
		}
		else
		if (substr($str,0,6) == "\$arr =")
		{
			// php serializer
			$p = new php_serializer;
			$retval = $p->php_unserialize($str);
		}
		else
		if ($str{0} == "<")
		{
			$ser = get_instance("orb/xmlrpc");
			$retval = $ser->xmlrpc_unserialize($str);
		}
		else
		{
			$retval = unserialize($str);
		}
		return $retval;
	}

	/// I think we should just use the PHP superglobal $GLOBALS for storing
	// those variables instead of messing with our own objects. Empty it
	// first and then put variables we need into it.

	// oh, dammit. Shouldn't the aw_globals also be initalized and accesed
	// through the aw_dir/init.aw - ?

	// well. our own stuff kinda.. I dunno, feels better. but yeah, it also feels a lot slower. 
	// and yeah. we shouldn't need these before aw_startup() and we could init it in there.. - terryf

	// .. and now they are. 
	function &_aw_global_init()
	{
		// reset aw_global_* function globals
		$GLOBALS["__aw_globals"] = array();

		// import CGI spec variables and apache variables

		// but we must do this in a certain order - first the global vars, then the session vars and then the server vars
		// why? well, then you can't override server vars from the url.

		// known variables - these can be modified by the user and are not to be trusted, so we get them first 
		$impvars = array("lang_id","tafkap","DEBUG","no_menus","section","class","action","fastcall","reforb","set_lang_id","admin_lang","admin_lang_lc","LC","period","oid","print","sortby","sort_order");
		foreach($impvars as $k)
		{
			aw_global_set($k,$GLOBALS[$k]);
		}

		// SESSION vars - these cannot be modified by the user except through aw, so they are relatively trustworthy
		if (is_array($GLOBALS["HTTP_SESSION_VARS"]))
		{
			foreach($GLOBALS["HTTP_SESSION_VARS"] as $k => $v)
			{
				aw_global_set($k,$v);
			}
		}
		aw_global_set("uid", $GLOBALS["HTTP_SESSION_VARS"]["uid"]);

		// server vars - these can be trusted pretty well, so we do these last
		$server = array("SERVER_SOFTWARE", "SERVER_NAME", "GATEWAY_INTERFACE", "SERVER_PROTOCOL", "SERVER_PORT","REQUEST_METHOD",  "PATH_TRANSLATED","SCRIPT_NAME", "QUERY_STRING", "REMOTE_ADDR", "HTTP_ACCEPT","HTTP_ACCEPT_CHARSET", "HTTP_ACCEPT_ENCODING", "HTTP_ACCEPT_LANGUAGE", "HTTP_CONNECTION", "HTTP_HOST", "HTTP_REFERER", "HTTP_USER_AGENT","REMOTE_PORT","SCRIPT_FILENAME", "SERVER_ADMIN", "SERVER_PORT", "SERVER_SIGNATURE", "PATH_TRANSLATED", "SCRIPT_NAME", "REQUEST_URI", "PHP_SELF", "DOCUMENT_ROOT", "PATH_INFO", "SERVER_ADDR", "HTTP_X_FORWARDED_FOR");
		foreach($server as $var)
		{
			aw_global_set($var,$GLOBALS["HTTP_SERVER_VARS"][$var]);
		}
		$GLOBALS["__aw_globals_inited"] = true;
	}

	////
	// !this function replaces php's GLOBAL - it keeps global variables in a global object instance
	// why is this? well, because then they can't be set from the url, overriding the default values
	// and causing potential security problems
	function aw_global_get($var)
	{
		return $GLOBALS["__aw_globals"][$var];
	}

	function aw_global_set($var,$val)
	{
		$GLOBALS["__aw_globals"][$var] = $val;
	}

	////
	// !this replaces global caches - if you use this function, then cache contents cannot be overriden from the url
	function aw_cache_get($cache,$key)
	{
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			return false;
		}
		if (!is_array($GLOBALS["__aw_cache"][$cache]))
		{
			return false;
		}
		return $GLOBALS["__aw_cache"][$cache][$key];
	}

	function aw_cache_set($cache,$key,$val = "")
	{
		// if $key is array, we will just stick it into the cache. 
		// NO!! that's what aw_cache_set_array() is for!!! - terryf
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array($cache => array($key => $val));
		}
		else
		if (!is_array($GLOBALS["__aw_cache"][$cache]))
		{
			$GLOBALS["__aw_cache"][$cache] = array($key => $val);
		}
		else
		{
			$GLOBALS["__aw_cache"][$cache][$key] = $val;
		}
	}

	function aw_cache_flush($cache)
	{
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array();
		}
		$GLOBALS["__aw_cache"][$cache] = false;
	}

	////
	// !this returns the entire cache array - this is useful for instance if you want to iterate over the cache
	function aw_cache_get_array($cache)
	{
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array();
			return false;
		}
		return $GLOBALS["__aw_cache"][$cache];
	}

	////
	// !this is for initializing the cache
	function aw_cache_set_array($cache,$arr)
	{
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array($cache => $arr);
		}
		else
		{
			$GLOBALS["__aw_cache"][$cache] = $arr;
		}
	}

	////
	// !saves a local variable's value to the session - there is no session_get, because 
	// session vars are automatically registered as globals as well, so for retrieval you can use aw_global_get()
	function aw_session_set($name,$value)
	{
		$GLOBALS[$name] = $value;
		session_register($name);
		aw_global_set($name,$value);
	}

	////
	// !deletes the variable $name from the session
	function aw_session_del($name, $leave_global = false)
	{
		session_unregister($name);
		if (!$leave_global)
		{
			aw_global_set($name, "");
		}
	}

	////
	// !deletes all variables from the session that match preg pattern $pattern
	function aw_session_del_patt($pattern)
	{
		foreach($GLOBALS["HTTP_SESSION_VARS"] as $vn => $vv)
		{
			if (preg_match($pattern, $vn))
			{
				aw_session_del($vn);
			}
		}
	}

	function get_ts_from_arr($var)
	{
		return mktime($var["hour"], $var["minute"], $var["second"], $var["month"], $var["day"], $var["year"]);
	}

	function aw_register_default_class_member($class, $member, $value)
	{
		$members = aw_cache_get("__aw_default_class_members", $class);
		$members[$member] = $value;
		aw_cache_set("__aw_default_class_members", $class, $members);
	}

	////
	// !all network functions go in here, all must be static
	class inet
	{
		////
		// !resolvib ip aadressiks. cacheb kiiruse huvides tulemusi
		// voib kasutada ntx syslogi juures
		// tagastab 2 elemendiga array, esimene on lahendatud 
		// nimi, teine aadress, mis ette anti voi stringist välja
		// parsiti
		function gethostbyaddr($addr)
		{
			// *wink terryf*, kena regexp mis?
			// idee on selles, et parsib lahti ntx syslogis olevad
			// aadressid kujul host.ee / 1.2.3.4
			if (preg_match("/^(.*?)\s*?\/\s+?([0-9\.]+?)$/",$addr,$parts))
			{
				$addr = $parts[2];
			};
			if (!($ret = aw_cache_get("solved",$addr)))
			{
				$ret = gethostbyaddr($addr);
				aw_cache_set("solved",$addr,$ret);
			};
			return array($ret,$addr);
		}

		////
		// !kontrollime, kas parameeter on ikka IP aadress
		function is_ip($addr)
		{
			// match 1 to 3 digits
			$oct = "(\d{1,3}?)";
			$valid = preg_match("/^$oct\.$oct\.$oct\.$oct$/",$addr,$parts);
			// kontrollime, ega ei ole tegemist bcast aadressiga
			if (isset($parts[4]) && ( ($parts[4] == 0) || ($parts[4] == 255) ))
			{
				// ongi.
				$valid = false;
			};

			if (isset($parts[1]) && $parts[1] == 0) 
			{
				$valid = false;
			};

			if ($valid) 
			{
				// kontrollime, kas koik oktetid on ikka lubatud vahemikus
				for ($i = 1; $i <= 4; $i++)
				{
					if ( ($parts[$i] < 0) || ($parts[$i] > 255) ) 
					{
						$valid = false;
					};
				};
			};
			return $valid;
		}
	}
};

?>
