<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/defs.aw,v 2.28 2001/11/20 14:29:45 duke Exp $
// defs.aw - common functions (C) StruktuurMeedia 2000,2001
if (!defined("DEFS"))
{
define(DEFS,1);
////
// !saadab 404 Not found vmt.
function bail_out()
{
	header(LC_DEFS_HTTP);
	print LC_DEFS_NOT_FOUND;
	print LC_DEFS_NOT_FOUND2;
	global $REQUEST_URI;
	global $SERVER_SIGNATURE;
	print LC_DEFS_REG_URL . $REQUEST_URI . LC_DEFS_NOT_IN_SERVER;
	print "<hr>\n<address>" . $SERVER_SIGNATURE . "</address>\n</body></html>";
	exit;
	lc_load("definition");
}

////
// !Replaces links inside text
function create_links($src)
{
	$src = preg_replace("/((http(s?):\/\/)|(www\.))(.+?)(\s?)/iU", "<a href=\"http$3://$4$5\" target=\"_blank\">$2$4$5</a>", $src);
	$src = preg_replace("/(\w*?\@.*\.\w*)/i","<a href=\"mailto:$1\">$1</a>",$src);
	return $src;
}
	
function localparse($src = "",$vars = array())
{
	return preg_replace("/{VAR:(.+?)}/e","\$vars[\"\\1\"]",$src);
}

// laeb XML failist orbi definitsiooni
function load_xml_orb_def($class)
{
	global $basedir;
	// klassi definitsioon sisse
	$xmldef = get_file(array(
		"file" => "$basedir/xml/orb/$class.xml"
	));

	// loome parseri
	$parser = xml_parser_create();
	xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
	// xml data arraysse
	xml_parse_into_struct($parser,$xmldef,&$values,&$tags);
	// R.I.P. parser
	xml_parser_free($parser);
 
	// konteinerite tüübid
	$containers = array("class","action","function","arguments");
 
	// argumentide tüübid
	$argtypes = array("optional","required","define");
 
	// ja siia moodustub loplik struktuur
	$orb_defs = array();
	
	foreach($values as $key => $val)
	{
		// parajasti töödeldava tag-i nimi
		$tag = $val["tag"];
 
		// on kas tyhi, "open", "close" voi "complete".
		$tagtype = $val["type"];
 
		// tagi parameetrid, array
		$attribs = isset($val["attributes"]) ? $val["attributes"] : "";
 
		// kui tegemist on nö "konteiner" tag-iga, siis...
		if (in_array($tag,$containers))
		{

			if (in_array($tagtype,array("open","complete")))
			{
				$$tag = $attribs["name"];
				if (($tag == "action") && (isset($attribs["nologin"]) && $attribs["nologin"]))
				{
					$orb_defs[$class][$attribs["name"]]["nologin"] = 1;
				};
				if (($tag == "action") && (isset($attribs["all_args"]) && $attribs["all_args"]))
				{
					$orb_defs[$class][$attribs["name"]]["all_args"] = true;
				};
				if (isset($attribs["default"]) && $attribs["default"] && ($tag == "action"))
				{
					$orb_defs[$class]["default"] = $attribs["name"];
				};
				if ($tag == "function")
				{
					$orb_defs[$class][$action][$tag] = $$tag;
					// seda saab kasutada monede lisakontrollide jaoks
					$orb_defs[$class][$action]["xml"] = 1;
					// initsialiseerime need arrayd
					$orb_defs[$class][$action]["required"] = array();
					$orb_defs[$class][$action]["optional"] = array();
					$orb_defs[$class][$action]["define"] = array();
					// default action
					if (isset($attribs["default"]) && $attribs["default"])
					{
						$orb_defs[$class]["default"] = $action;
						//print "def is = $action<br>";
					};
				};
			}
			elseif ($tagtype == "close")
			{
				$$tag = "";
			};
		};
 
		// kui leidsime argumenti määrava tag-i, siis ...
		if (in_array($tag,$argtypes))
		{
			// kontroll, just in case
			if ($tagtype == "complete")
			{
				if ($tag == "define")
				{
					$val = $attribs["value"];
				}
				else
				{
					$val = 1;
				};
				$orb_defs[$class][$action][$tag][$attribs["name"]] = $val;
			};
		};
	}; // foreach

	return $orb_defs;
}; // function
	
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
 
// debuukimisel on see funktsioon abiks
function dump_struct($data)
{
	print "<pre>";
	print_r($data);
	print "</pre>";
	// kui on array, siis resetime selle pointeri, sest print_r jätab selle array lõppu
	if (is_array($data))
	{
		reset($data);
	};
}

// asendab numbri vastava stringiga, kui number < 11, 
// see on meffi idee ja X-i juures seda ka kasutatakse
// arvud saab loomulikult lokaliseeritud
function verbalize_number($number)
{
	$strings = array("0",LC_D1,LC_D2,LC_D3,LC_D4,LC_D5,LC_D6,LC_D7,LC_D8,LC_D9,LC_D10);
	if ( ($number > 10) || ($number < 0))
	{
		$ret = $number;
	} else {
		$ret = $strings[$number];
	};
	return $ret;
}

////
// !Tagastab mingile klassile vastava ikooni
function get_icon_url($clid,$name)
{
	classload("config");
	global $d_icon_cache,$d_icons_loaded,$d_fileicon_cache, $d_fileicons_loaded,$d_othericon_cache, $d_othericons_loaded;
	if (!$d_icons_loaded)
	{
		$c = new db_config;
		$d_icon_cache = unserialize($c->get_simple_config("menu_icons"));
		$d_icons_loaded = true;
	}
	$i = $d_icon_cache["content"][$clid]["imgurl"];

	if ($clid == CL_FILE)
	{
		if (!$d_fileicons_loaded)
		{
			$c = new db_config;
			$d_fileicon_cache = unserialize($c->get_simple_config("file_icons"));
			$d_fileicons_loaded = true;
		}
		$extt = substr($name,strpos($name,"."));
		#$extt = substr($name,strpos($name,".")+1);
		if ($d_fileicon_cache[$extt]["url"] != "")
		{
			$i = $d_fileicon_cache[$extt]["url"];
		}
	}

	if ($clid == "promo_box" || $clid == "brother" || $clid == "conf_icon_other" || $clid == "conf_icon_programs" || $clid == "conf_icon_classes" || $clid == "conf_icon_ftypes" || $clid == "conf_icons" || $clid == "conf_jf" || $clid == "conf_users" || $clid == "conf_icon_import" || $clid == "conf_icon_db" || $clid == "homefolder" || $clid == "shared_folders" || $clid == "hf_groups" || $clid == "bugtrack" )
	{
		if (!$d_othericons_loaded)
		{
			$c = new db_config;
			$d_othericon_cache = unserialize($c->get_simple_config("other_icons"));
			$d_othericons_loaded = true;
		}
		if ($d_othericon_cache[$clid]["url"] != "")
		{
			$i = $d_othericon_cache[$clid]["url"];
		}
		else
		{
			$i = "images/ftv2doc.gif";
		}
	}
		$retval = $i == "" ? "/images/icon_aw.gif" : $i;
		$retval = preg_replace("/^http:\/\/(.*)\//","/",$retval);
		return $retval;
}

////
// !tagastab lokaliseeritud kuunime numbri järgi
function get_lc_month($id)
{
	$mnames = explode("|",LC_MONTH);
	$id = (int)$id;
	return $mnames[$id];
}

////
// !tagastab lokaliseeritud päevanime
function get_lc_weekday($id)
{
	$daynames = explode("|",LC_WEEKDAY);
	return $daynames[$id];
}

function format_text($text)
{
	$text = str_replace("\n\n","<p>",$text);
	$text = str_replace("\n","<br>",$text);
	return $text;
}

////
// !väljastab refresh headeri koos muude vajalike tilullidega
function http_refresh($delay,$url)
{
	header("Refresh: $delay;url=$url");
	print "\n\n";
	exit;
}

////
// !kasutamine
// if (is_valid("password",$pass_entered_in_a_form))
function is_valid($set,$string)
{
	$sets = array(
		"password" 	=> array(
			"content" 	=> "1234567890qwertyuiopasdfghjklzxcvbnm_QWERTYUIOPASDFGHJKLZXCVBNM",
			"min"		=> 4,
			"max"		=> 32),
		"uid"		=> array(
			"content"	=> "1234567890qwertyuiopasdfghjklzxcvbnm_QWERTYUIOPASDFGHJKLZXCVBNM",
			"min"		=> 3,
			"max"		=> 30)
		);
	// defineerimata character set, bail out	
	if (!$sets["$set"])
	{
		return false;
	};
	$checkagainst = $sets["$set"]["content"];
	$valid = true;
	$i = 0;
	while( ($i < strlen($string) && (!($valid === false))))
	{
		$valid = !(strpos($checkagainst,$string[$i]) === false);
		if (!$valid)
		{
//			echo "invd char = ", $string[$i],"<br>";
		}
		$i++;
	};
	return $valid;
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
// !resolvib ip aadressiks. cacheb kiiruse huvides tulemusi
// voib kasutada ntx syslogi juures
function aw_gethostbyaddr($addr)
{
	// *wink terryf*, kena regexp mis?
	// idee on selles, et parsib lahti ntx syslogis olevad
	// aadressid kujul host.ee / 1.2.3.4
	if (preg_match("/^(.*?)\s*?\/\s+?([0-9\.]+?)$/",$addr,$parts))
	{
		$addr = $parts[2];
	};
	global $solved;
	if (!$solved[$addr])
	{
		$solved[$addr] = gethostbyaddr($addr);
	};
	// tagastab 2 elemendiga array, esimene on lahendatud 
	// nimi, teine aadress, mis ette anti voi stringist välja
	// parsiti
	return array($solved[$addr],$addr);
};

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
			if ( ($parts[$i] < 0) || ($parts[$i] > 255) ) {
				$valid = false;
			};
		};
	};
	return $valid;
};

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
			$retval[]= sprintf($format,$val);
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
		$retval[] = sprintf($format,$array);
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
	global $REMOTE_ADDR,$HTTP_X_FORWARDED_FOR;
	$ip = $HTTP_X_FORWARDED_FOR;
	if (!is_ip($ip))
	{
		$ip = $REMOTE_ADDR;
	}
	return $ip;
}

////
// !Kas argument on e-maili aadress?
// FIXME: should be rewritten. There are easier ways to do this with preg_match function
function is_email ($Address = "") 
{
	if(empty($Address)) 
	{
		// tühi aadress
  	return false;
	}

	if(!ereg("@",$Address)) 
	{
		// @-i pole
		return false;
	}

	list($User,$Host) = split("@",$Address);

	if ( (empty($User)) or (empty($Address)) ) 
	{
		// kuju pole user@host
		return false;
	}
       
	if( (!(strpos($User," ")===false) or !(strpos($Host," ")===false)) )
	{
		// whitespace sees
		return false;
	}

	return true;
};

function is_admin()
{
		return (stristr($GLOBALS["REQUEST_URI"],LC_DEFS_AUTOMATWEB)!=false);
}

////
// !prints the message if $GLOBALS["DEBUG"] == 1, basically the same as core::dmsg, but with this you don't need to fiddle
// around with sessions
function dbg($msg)
{
	global $DEBUG;
	if ($DEBUG == 1)
	{
		echo $msg;
	}
}


};

?>
