<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/defs.aw,v 2.164 2004/11/24 15:12:59 kristo Exp $
// defs.aw - common functions 
if (!defined("DEFS"))
{
	define("DEFS",1);

	define("SERIALIZE_PHP",1);
	define("SERIALIZE_XML",2);
	define("SERIALIZE_NATIVE",3);
	define("SERIALIZE_PHP_NOINDEX",4);
	define("SERIALIZE_XMLRPC", 5);

	function convert_unicode($source)
	{
		$retval = str_replace(chr(195).chr(181), "&otilde;", $source);
		$retval = str_replace(chr(195).chr(149), "&Otilde;", $retval);

		$retval = str_replace(chr(195).chr(164), "&auml;", $retval);
		$retval = str_replace(chr(195).chr(132), "&Auml;", $retval);

		$retval = str_replace(chr(195).chr(188), "&uuml;", $retval);
		$retval = str_replace(chr(195).chr(156), "&Uuml;", $retval);

		$retval = str_replace(chr(195).chr(182), "&ouml;", $retval);
		$retval = str_replace(chr(195).chr(150), "&Ouml;", $retval);


		$retval = str_replace(chr(154), "&Scaron;", $retval);

		return $retval;
	}

	function obj_link($oid)
	{
		return aw_ini_get("baseurl")."/".$oid;
	}

	function create_email_links($str)
	{
		$str = preg_replace("/([-.a-zA-Z0-9_]*)@([-.a-zA-Z0-9_]*)/","<a href='mailto:\\1@\\2'>\\1@\\2</a>", $str);
		return preg_replace("/((\s|^))((http(s?):\/\/)|(www\.))([a-zA-Z0-9\.\-]+)/im", "$2<a href=\"http$5://$6$7\" target=\"_blank\">$4$6$7</a>", $str); 
	}

	function post_message($msg, $params)
	{
		if (aw_global_get("__in_post_message") == 1)
		{
			return;
		}

		aw_global_set("__in_post_message", 1);

		$inst = get_instance("core/msg/msg_dispatch");
		$inst->post_message(array(
			"msg" => $msg, 
			"params" => $params
		));

		aw_global_set("__in_post_message", 0);
	}
	

	function get_lc_date($time=0, $format=3)
	{
		$inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);		
		if(!is_object($inst))
		{
			$inst = get_instance("core/locale/en/date");
		}	
		return $inst->get_lc_date($time, $format);
	}
	
	function post_message_with_param($msg, $param, $params)
	{
		if (aw_global_get("__in_post_message") == 1)
		{
			return;
		}

		aw_global_set("__in_post_message", 1);

		$inst = get_instance("core/msg/msg_dispatch");
		$inst->post_message_with_param(array(
			"msg" => $msg, 
			"param" => $param,
			"params" => $params
		));

		aw_global_set("__in_post_message", 0);
	}

	function send_mail($to,$subject,$msg,$headers="",$arguments="")
	{
		if (!is_email($to))
		{
			return;
		}
		if (empty($arguments))
		{
			$arguments = aw_ini_get("mail.arguments");
		};
		mail($to,$subject,$msg,$headers,$arguments);
	}
	
	////
	// !returns an array of all classes defined in the system, index is class id, value is class name and path
	//  addempty - if true, empty element in front
	//	only_addable - if true, only classes that have can_add = 1 are listed
	function get_class_picker($arr = array())
	{
		extract($arr);
		$cls = aw_ini_get("classes");
		$clfs = aw_ini_get("classfolders");

		$ret = array();
		if ($addempty)
		{
			$ret = array(0 => "");
		}

		$field = ($field) ? $field : "name";

		$trans = array_flip(get_html_translation_table(HTML_ENTITIES));

		foreach($cls as $clid => $cld)
		{

			// what field? it's file
			//if (isset($cld['field']) && ($cld['field'] != ""))
			if (isset($cld['file']) && ($cld['file'] != ""))
			{
				$clname = strtr($cld[$field], $trans);
				if (isset($index))
				{
					$ret[$cld[$index]] = $clname;
				}
				else
				if ($only_addable)
				{
					if ($cld["can_add"] == 1)
					{
						$ret[$clid] = $clname;
					}
				}
				else
				{
					$ret[$clid] = $clname;
				};
			}
		}
		asort($ret);
		return $ret;
	}

	////
	// !adds or changes a variable in the current url
	function aw_url_change_var($arg1, $arg2 = false, $url = false)
	{
		$arg_list = func_get_args();
		if (sizeof($arg_list) > 1)
		{
			$arg_list[0] = array($arg1 => $arg2);
		}
		if (!$url)
		{
			$url = aw_global_get("REQUEST_URI");
		};
		foreach($arg_list[0] as $arg1 => $arg2)
		{
			// remove old
			$url = preg_replace("/$arg1=[^&]*/","", $url);
			if (!empty($arg2))
			{
				$url .= (strpos($url,"?") === false ? "?" : "&" ).$arg1."=".$arg2;
				$url = preg_replace("/&{2,}/","&",$url);
			};
		};
		$url = str_replace('&&','&',$url);
		return $url;
	}

	////
	// !generates a password with length $length
	function generate_password($arr = array())
	{
		extract($arr);
		if (!$length)
		{
			$length = 8;
		}
		$chars = "1234567890-qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_";
		$pwd = "";
		for ($i = 0; $i < $length; $i++)
		{
			$pwd .= $chars{rand(0,strlen($chars)-1)};
		}
		return $pwd;
	}

	////
	// !places <a href tags around urls and e-mail addresses in text $src
	function create_links($src)
	{
		$src = preg_replace("/((\W|^))((http(s?):\/\/)|(www\.))(\S+)/im", "$2<a href=\"http$5://$6$7\" target=\"_blank\">$4$6$7</a>", $src);
		return preg_replace("/([\w*|\.]*?)@([\w*|\.]*?)/imsU","<a href='mailto:$1@$2'>$1@$2</a>", $src);
	}

	////
	// !parses template source in $src, and replaces variables in it with variables from $vars
	function localparse($src = "",$vars = array())
	{
		// kogu asendus tehakse ühe reaga
		// "e" regexpi lõpus tähendab seda, et teist parameetrit käsitletakse php koodina,
		// mis eval-ist läbi lastakse. 
		$src = @preg_replace("/{VAR:(.+?)}/e","\$vars[\"\\1\"]",$src);
		$src = @preg_replace("/{DATE:(.+?)\|(.+?)}/e","((is_numeric(\$vars[\"\\1\"]) && \$vars[\"\\1\"] > 1 )? date(\"\\2\",\$vars[\"\\1\"]) : \"\")",$src);
		return @preg_replace("/{INI:(.+?)}/e","aw_ini_get(\"\\1\")",$src);
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
		if (aw_global_get("LC") == "et")
		{
			return get_est_month($id);
		}
		$mnames = explode("|",LC_MONTH);
		return $mnames[(int)$id];
	}

	function get_est_month($id)
	{
		$mnames = explode("|","jaanuar|veebruar|märts|aprill|mai|juuni|juuli|august|september|oktoober|november|detsember");
		return $mnames[(int)$id-1];
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
		$text = str_replace("\n","<br />",$text);
		return $text;
	}

	////
	// !checks if the parameter is an oid
	function is_oid($oid)
	{
		$oid = str_replace(":", "", str_replace("_", "", $oid));
		return !empty($oid) && is_numeric($oid) && $oid > 0;
	}

	//// 
	// !checks if the parameter is a valid class_id
	function is_class_id($clid)
	{
		if (!is_numeric($clid))
		{
			return false;
		}

		$clss = aw_ini_get("classes");
		if (!isset($clss[$clid]))
		{
			return false;
		}

		return true;
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
				'min' => 3,
				'max' => 32),
			'url' => array(
				'content' => '1234567890qwertyuiopasdfghjklzxcvbnm-QWERTYUIOPASDFGHJKLZXCVBNM._',
				'min' => 3,
				'max' => 255),
			'uid'	=> array(
				'content'	=> '1234567890qwertyuiopasdfghjklzxcvbnm_QWERTYUIOPASDFGHJKLZXCVBNM.',
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
		return is_numeric($parm);
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
		else
		if ($parts[1] > date("t",mktime(0,0,0,$parts[2],1,$parts[3])) )
		{
			$valid = false;
		}
		else
		if ( ($parts[2] < 1) || ($parts[2] > 12) )
		{
			$valid = false;
		} 
		return $valid;
	};

	////
	// !if a is between z and y, return true else return false
	// y < z
	function between($a,$y,$z, $onTrue = true, $onFalse = false)
	{
		if (($a >= $y) && ($a <= $z))			
		{
			return $onTrue;
		}
		else
		{
			return $onFalse;
		}
	}

	////
	// !Kas argument on e-maili aadress?
	// Courtesy of martin@linuxator.com ;)
	function is_email ($address = "") 
	{
		return preg_match('/([a-z0-9-]((\.|_)?[a-z0-9]+)+@([a-z0-9]+(\.|-)?)+[a-z0-9]\.[a-z]{2,})/i',$address);
	}

	function is_admin()
	{
		return (stristr(aw_global_get("REQUEST_URI"),"/automatweb")!=false);
	}

	////
	// !Genereerib md5 hashi kas parameetrist voi suvalisest arvust.
	function gen_uniq_id($param = "")
	{
		// md5sum on alati 32 märki pikk
		$src = (strlen($param) > 0) ? $param : uniqid(rand());
		return md5($src);
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

	function arr($arr,$die=false,$see_html=false)
	{
		echo '<hr/>';
		$tmp = '';
		ob_start();
		print_r($arr);
		$tmp = ob_get_contents();
		ob_end_clean();
		echo '<pre>';
		echo $see_html?htmlspecialchars($tmp):$tmp;
		echo '</pre>';
		echo '<hr/>';
		if ($die)
		{
			die('');
		}
		return $arr;
	}

	////
	// !järgmine funktsioon on inspireeritud perlist ;)
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

	////
	// !sama, mis eelmine, ainult et moodustuvad paarid
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
	// !Let's deal with no name objects in one place
	function parse_obj_name($name)
	{
		$name = trim($name);
		$rv = empty($name) ? "(nimetu)" : $name;
		$rv = str_replace('"',"&quot;", $rv);
		return $rv;
	}

	function aw_serialize($arr,$type = SERIALIZE_PHP, $flags = array())
	{
		switch($type)
		{
			case SERIALIZE_PHP:
				classload("php");
				$ser = new php_serializer;
				foreach($flags as $fk => $fv)
				{
					$ser->set($fk, $fv);
				}
				$str = $ser->php_serialize($arr);
				break;
			case SERIALIZE_PHP_FILE:
				classload("php_file");
				$ser = new php_serializer_file;
				foreach($flags as $fk => $fv)
				{
					$ser->set($fk, $fv);
				}
				$str = $ser->php_serialize($arr);
				break;
			case SERIALIZE_PHP_NOINDEX:
				classload("php");
				$ser = new php_serializer;
				$ser->set("no_index",1);
				$str = $ser->php_serialize($arr);
				break;

			case SERIALIZE_XML:
				classload("xml");
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
		$retval = false;
		if ($dequote)
		{
			$str = stripslashes($str);
		};

		if (substr($str,0,14) == "<?xml version=")
		{
			classload("xml");
			$x = new xml;
			$retval = $x->xml_unserialize(array("source" => $str));
		}
		else
		if (substr($str,0,6) == "\$arr =")
		{
			classload("php");
			// php serializer
			$p = new php_serializer;
			$retval = $p->php_unserialize($str);
		}
		else
		//if ($str{0} == "<")
		if ((strlen($str) > 0) && ($str{0} == "<"))
		{
			$ser = get_instance("orb/xmlrpc");
			$retval = $ser->xmlrpc_unserialize($str);
		}
		elseif (!empty($str))
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
		$impvars = array("lang_id","tafkap","DEBUG","no_menus","section","class","action","fastcall","reforb","set_lang_id","admin_lang","admin_lang_lc","LC","period","oid","print","sortby","sort_order","cal","date","trid", "project", "view");
		foreach($impvars as $k)
		{
			if (isset($GLOBALS[$k]))
			{
				aw_global_set($k,$GLOBALS[$k]);
			}
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
		$server = array("SERVER_SOFTWARE", "SERVER_NAME", "GATEWAY_INTERFACE", "SERVER_PROTOCOL", "SERVER_PORT","REQUEST_METHOD",  "PATH_TRANSLATED","SCRIPT_NAME", "QUERY_STRING", "REMOTE_ADDR", "REMOTE_HOST", "HTTP_ACCEPT","HTTP_ACCEPT_CHARSET", "HTTP_ACCEPT_ENCODING", "HTTP_ACCEPT_LANGUAGE", "HTTP_CONNECTION", "HTTP_HOST", "HTTP_REFERER", "HTTP_USER_AGENT","REMOTE_PORT","SCRIPT_FILENAME", "SERVER_ADMIN", "SERVER_PORT", "SERVER_SIGNATURE", "PATH_TRANSLATED", "SCRIPT_NAME", "REQUEST_URI", "PHP_SELF", "DOCUMENT_ROOT", "PATH_INFO", "SERVER_ADDR", "HTTP_X_FORWARDED_FOR");
		foreach($server as $var)
		{
			aw_global_set($var,$GLOBALS["HTTP_SERVER_VARS"][$var]);
		}
		

		if (isset($GLOBALS["HTTP_COOKIE_VARS"]["lang_id"]))
		{
			aw_global_set("lang_id", $GLOBALS["HTTP_COOKIE_VARS"]["lang_id"]);
		}

		if (isset($_REQUEST))
		{
			aw_global_set("request",$_REQUEST);
		};

		$GLOBALS["__aw_globals_inited"] = true;
	}

	////
	// !this function replaces php's GLOBAL - it keeps global variables in a global object instance
	// why is this? well, because then they can't be set from the url, overriding the default values
	// and causing potential security problems
	function aw_global_get($var)
	{
		return isset($GLOBALS["__aw_globals"][$var]) ? $GLOBALS["__aw_globals"][$var] : false;
	}

	function aw_global_set($var,$val)
	{
		$GLOBALS["__aw_globals"][$var] = $val;
	}

	////
	// !this replaces global caches - if you use this function, then cache contents cannot be overriden from the url
	function aw_cache_get($cache,$key)
	{
		if (is_array($key))
		{
			return false;
		}
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			return false;
		}
		if (!isset($GLOBALS["__aw_cache"][$cache]) || !is_array($GLOBALS["__aw_cache"][$cache]))
		{
			return false;
		}
		return isset($GLOBALS["__aw_cache"][$cache][$key]) ? $GLOBALS["__aw_cache"][$cache][$key] : false;
	}

	function aw_cache_set($cache,$key,$val = "")
	{
		if (is_array($key))
		{
			return false;
		}
		if (!is_array($GLOBALS["__aw_cache"]))
		{
			$GLOBALS["__aw_cache"] = array($cache => array($key => $val));
		}
		else
		{
			// init it, if empty - kills warning
			if (empty($GLOBALS["__aw_cache"][$cache]))
			{
				$GLOBALS["__aw_cache"][$cache] = "";
			};

			if (!is_array($GLOBALS["__aw_cache"][$cache]))
			{
				$GLOBALS["__aw_cache"][$cache] = array($key => $val);
			}
			else
			{
				$GLOBALS["__aw_cache"][$cache][$key] = $val;
			}
		};
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
	// !this replaces global caches - if you use this function, then cache contents cannot be overriden from the url
	function aw_session_cache_get($cache,$key)
	{
		if (!is_array($_SESSION["__aw_sess_cache"]))
		{
			return false;
		}
		if (!isset($_SESSION["__aw_sess_cache"][$cache]) || !is_array($_SESSION["__aw_sess_cache"][$cache]))
		{
			return false;
		}
		return isset($_SESSION["__aw_sess_cache"][$cache][$key]) ? $_SESSION["__aw_sess_cache"][$cache][$key] : false;
	}

	function aw_session_cache_set($cache,$key,$val = "")
	{
		// if $key is array, we will just stick it into the cache. 
		// NO!! that's what aw_cache_set_array() is for!!! - terryf
		if (!is_array($_SESSION["__aw_sess_cache"]))
		{
			$_SESSION["__aw_sess_cache"] = array($cache => array($key => $val));
		}
		else
		if (!is_array($_SESSION["__aw_sess_cache"][$cache]))
		{
			$_SESSION["__aw_sess_cache"][$cache] = array($key => $val);
		}
		else
		{
			$_SESSION["__aw_sess_cache"][$cache][$key] = $val;
		}
	}

	function aw_session_cache_flush($cache)
	{
		if (!is_array($_SESSION["__aw_sess_cache"]))
		{
			$_SESSION["__aw_sess_cache"] = array();
		}
		$_SESSION["__aw_sess_cache"][$cache] = false;
	}

	////
	// !saves a local variable's value to the session - there is no session_get, because 
	// session vars are automatically registered as globals as well, so for retrieval you can use aw_global_get()
	function aw_session_set($name,$value)
	{
		if (headers_sent())
		{
			return false;
		};
		if (empty($value))
		{
			return false;
		};
		$GLOBALS[$name] = $value;
		$_SESSION[$name] = $value;
		aw_global_set($name,$value);
	}

	////
	// !deletes the variable $name from the session
	function aw_session_del($name, $leave_global = false)
	{
		unset($_SESSION[$name]);
		if (!$leave_global)
		{
			aw_global_set($name, "");
			unset($GLOBALS[$name]);
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

	function aw_register_default_class_member($class, $member, $value)
	{
		$members = aw_cache_get("__aw_default_class_members", $class);
		$members[$member] = $value;
		aw_cache_set("__aw_default_class_members", $class, $members);
	}

	/** temporarily switches the current user to $arr[uid]


	**/
	function aw_switch_user($arr)
	{
		$old_uids = aw_global_get("old_uids");
		if (!is_array($old_uids))
		{
			$old_uids = array();
		}
		array_push($old_uids, $arr["uid"]);
		aw_global_set("old_uids", $old_uids);
		
		__aw_int_do_switch_user($arr["uid"]);
	}

	function __aw_int_do_switch_user($uid)
	{
		aw_global_set("uid", $uid);
		$us = get_instance("users");
		$us->request_startup();
		// also, flush acl cache !
		aw_cache_flush("aclcache");
		aw_cache_flush("__aw_acl_cache");
	}

	function aw_restore_user()
	{
		$old_uids = aw_global_get("old_uids");
		if (!is_array($old_uids))
		{
			$old_uids = array();
		}
		__aw_int_do_switch_user(array_pop($old_uids));
		aw_global_set("old_uids", $old_uids);
	}

	function aw_disable_acl()
	{
		$GLOBALS["__aw_disable_acl"] = $GLOBALS["cfg"]["acl"]["no_check"];
		$GLOBALS["cfg"]["acl"]["no_check"] = 1;
	}

	function aw_restore_acl()
	{
		$GLOBALS["cfg"]["acl"]["no_check"] = $GLOBALS["__aw_disable_acl"];
	}
	function clid_for_name($class_name)
	{
		return aw_ini_get("class_lut.".$class_name);
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
		// !returns the ip address that resolves to $name
		function name2ip($name)
		{
			if (!($ret = aw_cache_get("name2ip_solved",$name)))
			{
				$ret = gethostbyname($name);
				aw_cache_set("nam2ip_solved",$name,$ret);
			};
			return $ret;
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

	class dbg
	{
		// debuukimisel on see funktsioon abiks
		function dump($data)
		{
			ob_start();
			print "<pre>";
			var_dump($data);
			print "</pre>";
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}

		function str_dbg($str)
		{
			echo "str = $str <br>";
			for($i = 0; $i < strlen($str); $i++)
			{
				echo "at pos $i: ".$str{$i}." nr = ".ord($str{$i})." <br>";
			}
			echo "---<br>";
		}

		////
		// !prints the message if $GLOBALS["DEBUG"] == 1, basically the same as core::dmsg, but with this you don't need to fiddle
		// around with sessions
		function p($msg)
		{
			if (aw_global_get("DEBUG") == 1)
			{
				echo $msg."<br />\n";
			}
		}

		// prints if the user has a cookie named debug1
		function p1($msg)
		{
			if ($_COOKIE["debug1"])
			{
				echo $msg."<br />\n";
			}
		}

		// prints if the user has a cookie named debug2
		function p2($msg)
		{
			if ($_COOKIE["debug2"])
			{
				echo $msg."<br />\n";
			}
		}

		// prints if the user has a cookie named debug3
		function p3($msg)
		{
			if ($_COOKIE["debug3"])
			{
				echo $msg."<br />\n";
			}
		}

		// prints if the user has a cookie named debug4
		function p4($msg)
		{
			if ($_COOKIE["debug4"])
			{
				echo $msg."<br />\n";
			}
		}

		// prints if the user has a cookie named debug5
		function p5($msg)
		{
			if ($_COOKIE["debug5"])
			{
				print "<pre>";
				var_dump($msg);
				print "</pre>";
			}
		}

		function process_backtrace($bt, $skip = -1)
		{
			$msg .= "<br><br> Backtrace: \n\n<Br><br>";
			for ($i = count($bt)-1; $i > $skip; $i--)
			{
				if ($bt[$i+1]["class"] != "")
				{
					$fnm = "method <b>".$bt[$i+1]["class"]."::".$bt[$i+1]["function"]."</b>";
				}
				else
				if ($bt[$i+1]["function"] != "")
				{
					$fnm = "function <b>".$bt[$i+1]["function"]."</b>";
				}
				else
				{
					$fnm = "file ".$bt[$i]["file"];
				}

				$msg .= $fnm." on line ".$bt[$i]["line"]." called <br>\n";

				if ($bt[$i]["class"] != "")
				{
					$fnm2 = "method <b>".$bt[$i]["class"]."::".$bt[$i]["function"]."</b>";
				}
				else
				if ($bt[$i]["function"] != "")
				{
					$fnm2 = "function <b>".$bt[$i]["function"]."</b>";
				}
				else
				{
					$fnm2 = "file ".$bt[$i]["file"];
				}

				$msg .= $fnm2." with arguments ";

				$awa = new aw_array($bt[$i]["args"]);
				$msg .= "<font size=\"-1\">(".join(",", $awa->get()).") file = ".$bt[$i]["file"]."</font>";
			
				$msg .= " <br><br>\n\n";
			}
			return $msg;
		}

		function short_backtrace()
		{
			$msg = "";
			if (function_exists("debug_backtrace"))
			{
				$bt = debug_backtrace();
				for ($i = count($bt); $i >= 0; $i--)
				{
					if ($bt[$i+1]["class"] != "")
					{
						$fnm = $bt[$i+1]["class"]."::".$bt[$i+1]["function"];
					}
					else
					if ($bt[$i+1]["function"] != "")
					{
						if ($bt[$i+1]["function"] != "include")
						{
							$fnm = $bt[$i+1]["function"];
						}
						else
						{
							$fnm = "";
						}
					}
					else
					{
						$fnm = "";
					}

					$msg .= $fnm.":".$bt[$i]["line"]."->";
				}
			}

			return $msg;
		}

		function q($q)
		{
			$first = true;
			$GLOBALS["__aw_globals"]["db::DBMAIN"]->db_query($q);
			while ($row = $GLOBALS["__aw_globals"]["db::DBMAIN"]->db_next())
			{
				echo "********** Row nr ".++$cnt." *****************\n";
				foreach($row as $k => $v)
				{
					echo "$k: $v\n";
				}
			}
			echo "\n";
		}
	}

	// wrapper for localization classes
	class locale
	{
		var $default_locale = "en";
		var $lc_date_inst = false;
		function locale()
		{
			$this->lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
			if(!is_object($this->lc_date_inst))
			{
				$this->lc_date_inst = get_instance("core/locale/" . ($this->default_locale ? $this->default_locale : "en") . "/date");
			};
		}

		function get_lc_weekday($num, $short = false)
		{
			$this->lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
			if(!is_object($this->lc_date_inst))
			{
				$this->lc_date_inst = get_instance("core/locale/" . ($this->default_locale ? $this->default_locale : "en") . "/date");
			};
			if (method_exists($this->lc_date_inst,"get_lc_weekday"))
			{
				return $this->lc_date_inst->get_lc_weekday($num,$short);
			}
			else
			{
				return "";
			};
		}
		
		function get_lc_month($num)
		{
			$this->lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
			if(!is_object($this->lc_date_inst))
			{
				$this->lc_date_inst = get_instance("core/locale/" . ($this->default_locale ? $this->default_locale : "en") . "/date");
			};
			if (method_exists($this->lc_date_inst,"get_lc_month"))
			{
				return $this->lc_date_inst->get_lc_month($num);
			}
			else
			{
				return "";
			};
		}

		function get_lc_date($timestamp,$format)
		{
			$this->lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
			if(!is_object($this->lc_date_inst))
			{
				$this->lc_date_inst = get_instance("core/locale/" . ($this->default_locale ? $this->default_locale : "en")  . "/date");
			};
			if (method_exists($this->lc_date_inst,"get_lc_date"))
			{
				return $this->lc_date_inst->get_lc_date($timestamp,$format);
			}
			else
			{
				return "";
			};
		}

	}

	// wrapper for arrays - helps to get rid of numerous is_array checks
	// in code and reduces the amount of indenting
	class aw_array
	{
		function aw_array($arg = false)
		{
			if (is_array($arg))
			{
				$this->arg = $arg;
			}
			elseif ($arg)
			{
				$this->arg = array($arg);
			}
			else
			{
				$this->arg = array();
			};
			reset($this->arg);
		}

		function &get()
		{
			return $this->arg;
		}

		function get_at($key)
		{
			return $this->arg[$key];
		}

		function set($val)
		{
			$this->arg[] = $val;
		}

		function set_at($key, $val)
		{
			$this->arg[$key] = $val;
		}

		function next()
		{
			return each($this->arg);
		}

		function reset()
		{
			reset($this->arg);
		}

		function key_exists($key)
		{
			return isset($this->arg[$key]);
		}

		function first()
		{
			$this->reset();
			return $this->next();
		}

		function to_sql()
		{
			$str = join(",",array_values($this->arg));
			if ($str == "")
			{
				return "NULL";
			}
			return $str;
		}

		function count()
		{
			return count($this->arg);
		}
	};

	function do_nothing()
	{
	
	}
	
	/** returns the parameter or an array if the parameter is not an array
	**/
	function safe_array($var)
	{
		if (is_array($var)) 
		{
			return $var;
		}
		return array();
	}

	function t($s)
	{
		return $s;
	}
};
?>
