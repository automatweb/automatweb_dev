<?php

// this should be here, url parsing and variable initialization
// should be the first thing that is done

// apparently __FILE__ does not work with Zend Encoder. But since
// don't use that anyway, it's of no concern. At least now.

// include aw const
include_once(dirname(__FILE__)."/const.aw");

class _config_dummy {};

function aw_ini_get($var)
{
//	enter_function("__global::aw_ini_get",array());
	if (($pos = strpos($var,".")) !== false)
	{
		$class = substr($var,0,$pos);
		$var = substr($var,$pos+1);
	}
	else
	{
		$class = "__default";
	}
//	exit_function("__global::aw_ini_get");
	return $GLOBALS["cfg"][$class][$var];
}

function parse_config($file)
{
	$fd = file($file);
	foreach($fd as $line)
	{
		// ok, parse line
		// 1st, strip comments
		if (($pos = strpos($line,"#")) !== false)
		{
			$line = substr($line,0,$pos);
		}
		// now, strip all whitespace
		$line = trim($line);

		if ($line != "")
		{
			// now, config opts are class.variable = value
			$eqpos = strpos($line," = ");
			if ($eqpos !== false)
			{
				$var = trim(substr($line,0,$eqpos));
				$varvalue = trim(substr($line,$eqpos+3));
				
				// now, replace all variables in varvalue
				$varvalue = preg_replace('/\$\{(.*)\}/e',"aw_ini_get(\"\\1\")",$varvalue);
				$var = preg_replace('/\$\{(.*)\}/e',"aw_ini_get(\"\\1\")",$var);

				// if the varname contains . split it into class and variable parts
				// if not, class will be __default
				if (($dotpos = strpos($var,".")) !== false)
				{
					$varclass = substr($var,0,$dotpos);
					$varname = substr($var,$dotpos+1);
				}
				else
				{
					$varclass = "__default";
					$varname = $var;
				}

				// check if variable is an array 
				if (($bpos = strpos($varname,"[")) !== false)
				{
					// ok, do the bad eval version
					$arrparams = substr($varname,$bpos);
					$arrname = substr($varname,0,$bpos);
					if (!isset($GLOBALS["cfg"][$varclass][$arrname]) || !is_array($GLOBALS["cfg"][$varclass][$arrname]))
					{
						$GLOBALS["cfg"][$varclass][$arrname] = array();
					}
					$code = "\$GLOBALS[\"cfg\"][\"\$varclass\"][\"\$arrname\"]".$arrparams." = \"".$varvalue."\";";
					$len = strlen($code);
					for($i = 0; $i < $len; $i++)
					{
						if ($code{$i} == "[" && !($code{$i+1} == "\"" || $code{$i+1} == "'" || $code{$i+1} == "]"))
						{
							$code = substr($code, 0, $i+1)."\"".substr($code, $i+1);
						}
						if ($code{$i} == "]" && !($code{$i-1} == "\"" || $code{$i-1} == "'" || $code{$i-1} == "["))
						{
							$code = substr($code, 0, $i)."\"".substr($code, $i);
						}
					}
					eval($code);
				}
				else
				{
					// and stuff the thing in the array
					$GLOBALS["cfg"][$varclass][$varname] = $varvalue;
					//echo "setting [$varclass][$varname] to $varvalue <br>";
				}
			}
		}
	}
}

////
// !this is the very first aw function to get called when serving a page, so we should do most if not all initializing tasks here. 
// arguments:
//	cache_file = where the ini cache will be written, if not specified, no caching will be done
//	ini_files = array of ini files to parse
function init_config($arr)
{
	extract($arr);

//	list($micro,$sec) = split(" ",microtime());
//	$ts_s = $sec + $micro;

	$is_cached = false;
	// bloody catch22 situation. It would be nice to get the name of
	// the cache directory from the initialization file, but we are 
	// trying to read it.
	if (isset($cache_file))
	{
		// get the modification date on the ini cache
		if (file_exists($cache_file))
		{
			// check the modification date of each of the config files
			$cache_timestamp = filemtime($cache_file);

			if (is_array($ini_files))
			{
				$is_cached = true;
				foreach($ini_files as $file)
				{
					if (filemtime($file) >= $cache_timestamp)
					{
						$is_cached = false;
					}
				}
			}
		}
	}
//	list($micro,$sec) = split(" ",microtime());
//	$ts_e = $sec + $micro;
//	echo "timestamping took ",($ts_e - $ts_s), "seconds <br>";
//	result: 0.00013	 - too small to measure correctly

	if ($is_cached)
	{
//		list($micro,$sec) = split(" ",microtime());
//		$ts_s = $sec + $micro;
		$f = fopen($cache_file,"r");
		$fc = fread($f,filesize($cache_file));
		fclose($f);
		$GLOBALS["cfg"] = unserialize($fc);
//		list($micro,$sec) = split(" ",microtime());
//		$ts_e = $sec + $micro;
//		echo "cache unserialize ",($ts_e - $ts_s), " seconds <br>";
//		result: 0.005 - too small to measure correctly
	}
	else
	{
//		list($micro,$sec) = split(" ",microtime());
//		$ts_s = $sec + $micro;
		$rootini = false;
		foreach($ini_files as $file)
		{
			if ($rootini == false)
			{
				// now deduce the aw path from the rootini file path
				$rootini = $file;
				$basedir = dirname($file);
				$GLOBALS["cfg"]["__default"]["basedir"] = $basedir;
			}
			parse_config($file);
		}


		// and write to cache if file is specified
		if ($cache_file)
		{
			$str = serialize($GLOBALS["cfg"]);
			$f = fopen($cache_file,"w");
			fwrite($f,$str);
			fclose($f);
		}
//		list($micro,$sec) = split(" ",microtime());
//		$ts_e = $sec + $micro;
//		echo "ini parsing & cache writing took ",($ts_e - $ts_s), " seconds <br>";
//		result: 0.022812 - not exactly a showstopper, but still, glad to be rid of it
// 		yeah, not a showstopper, but still there are a whole freaking lot of preg_*
//		calls, getting rid of those was the real win -- duke
	}

	// siin ei saa veel aw_global_get'i kasutada, kuna defsi pole veel laetud
	$GLOBALS["cfg"]["__default"]["site_tpldir"] = $GLOBALS["cfg"]["__default"]["tpldir"];
	global $PHP_SELF;
	if (strpos($PHP_SELF,"automatweb")) 
	{
		// keemia. Kui oleme saidi adminnis sees, siis votame templated siit
		$GLOBALS["cfg"]["__default"]["tpldir"] = aw_ini_get("basedir") . "/templates";
	} 
	// kui saidi "sees", siis votame templated tolle saidi juurest, ehk siis ei puutu miskit

	// only load those definitions if fastcall is not set. This shouldnt break anything
	// and should save us a little memory. -- duke
	if (!isset($GLOBALS["fastcall"]))
	{
		// and here do the defs for classes
		foreach($GLOBALS["cfg"]["__default"]["classes"] as $clid => $cld)
		{
			define($cld["def"], $clid);
		}

		// and here do the defs for programs
		foreach($GLOBALS["cfg"]["__default"]["programs"] as $prid => $prd)
		{
			define($prd["def"], $prid);
		}

		// and here do the defs for errors
		foreach($GLOBALS["cfg"]["__default"]["errors"] as $erid => $erd)
		{
			define($erd["def"], $erid);
		}

		// defines for syslog
		foreach($GLOBALS["cfg"]["syslog"]["types"] as $stid => $std)
		{
			define($std["def"], $stid);
		}
		// defines fos syslog actions
		foreach($GLOBALS["cfg"]["syslog"]["actions"] as $said => $sad)
		{
			define($sad["def"], $said);
		}
	};

	// db driver quoting settings
	$GLOBALS['cfg']['__default']['magic_quotes_gpc'] = ini_get('magic_quotes_gpc');
}

function aw_ini_set($key,$value)
{
	// split $key in class / variable pair
	// load config file. 
	// find position of key
	// replace it
	// write config file
}

function aw_config_init_class(&$that)
{
//	enter_function("__global::aw_config_init_class",array());
	$class = get_class($that);
	$that->cfg = array_merge((isset($GLOBALS["cfg"][$class]) ? $GLOBALS["cfg"][$class] : array()),$GLOBALS["cfg"]["__default"]);
	$that->cfg["acl"] = $GLOBALS["cfg"]["acl"];
	$that->cfg["config"] = $GLOBALS["cfg"]["config"];
//	exit_function("__global::aw_config_init_class");
}


// loads localization constants
function lc_load($file)
{
//	enter_function("__global::lc_load",array());
//	global $LC,$admin_lang_lc;
	$LC =  $GLOBALS["__aw_globals"]["LC"];
	$admin_lang_lc = $GLOBALS["__aw_globals"]["admin_lang_lc"];
	if (!$admin_lang_lc)
	{
		$admin_lang_lc = "et";
	}
	@include_once($GLOBALS["cfg"]["__default"]["basedir"]."/lang/" . $admin_lang_lc . "/$file.".$GLOBALS["cfg"]["__default"]["ext"]);
//	exit_function("__global::lc_load");
}

// loads localization constants from the site's $site_basedir
function lc_site_load($file,&$obj)
{
//	enter_function("__global::lc_site_load",array());
	$LC = aw_global_get("LC");
	$fname = $GLOBALS["cfg"]["__default"]["site_basedir"]."/lang/".$LC."/$file.".$GLOBALS["cfg"]["__default"]["ext"];
	global $DLC;
	if ($DLC)
	{
		print "fn = $fname<br>";
	};
	@include_once($GLOBALS["cfg"]["__default"]["site_basedir"]."/lang/" . $LC . "/$file.".$GLOBALS["cfg"]["__default"]["ext"]);
	if ($obj)
	{
		// kui objekt anti kaasa, siis loeme tema template sisse muutuja $lc_$file 
		$var = "lc_".$file;
		global $$var;
		if (is_array($$var))
		{
			$obj->vars($$var);
		}
	}
//	exit_function("__global::lc_site_load");
}


// nyyd on voimalik laadida ka mitu librat yhe calliga 
// a la classload("users","groups","someothershit");
// 
// kurat. j6le n6me. nimelt siit inkluuditud asjad ei satu ju globaalsesse skoopi, 
// niiet ei tasu imestada kui muutujaid faili sees 2kki pole :P 
// a nuh, muud varianti ka pole - terryf
function classload($args)
{
//	enter_function("__global::classload",array());
	$arg_list = func_get_args();
	while(list(,$lib) = each($arg_list))
	{
		// let's not allow including ../../../etc/passwd :)
		$lib = str_replace(".","", $lib);
		$lib = $GLOBALS["cfg"]["__default"]["classdir"]."/".$lib.".".$GLOBALS["cfg"]["__default"]["ext"];
		include_once($lib);
	};
//	exit_function("__global::classload");
}

function get_instance($class,$args = array())
{
	enter_function("__global::get_instance",array());
	$classdir = $GLOBALS["cfg"]["__default"]["classdir"];
	$ext = $GLOBALS["cfg"]["__default"]["ext"];

	$id = sprintf("instance::%s",$class);
	$instance = aw_global_get($id);

	preg_match("/(\w*)$/",$class,$m);
	$lib = $m[1];

	if (not(is_object($instance)))
	{
		include_once($classdir."/".str_replace(".","", $class).".".$ext);
		if (class_exists($lib))
		{
			if (sizeof($args) > 0)
			{
				$instance = new $lib($args);
			}
			else
			{
				$instance = new $lib();
			};
			aw_global_set($id,$instance);
		}
		else
		{
			$instance = false;
		};
	};
	// now register default members - we do this here, because they might have changed
	// from the last time that the instance was created
	$members = aw_cache_get("__aw_default_class_members", $lib);
	if (is_array($members))
	{
		foreach($members as $k => $v)
		{
			$instance->$k = $v;
		}
	}

	exit_function("__global::get_instance",array());
	return $instance;
}

function upd_instance($class,$ref)
{
	$id = sprintf("instance::%s",$class);
	aw_global_set($id,$ref);
}


////
// !A neat little functional programming function
function not($arg)
{
	return !$arg;
}

function load_vcl($lib)
{
	include_once($GLOBALS["cfg"]["__default"]["basedir"]."/vcl/$lib.".$GLOBALS["cfg"]["__default"]["ext"]);
}


////
// !here we initialize the stuff that we couldn't initialize in parse_config, cause there are no services 
// available in parse_config, but now most of them are.
function aw_startup()
{
//	list($micro,$sec) = split(" ",microtime());
//	$ts_s = $sec + $micro;

	// reset aw_cache_* function globals
	$GLOBALS["__aw_cache"] = array();

	classload("defs");
	_aw_global_init();

	$l = get_instance("languages");
	$l->request_startup();

	global $LC;

	@include($GLOBALS["cfg"]["__default"]["basedir"]."/lang/" . $LC . "/errors.".$GLOBALS["cfg"]["__default"]["ext"]);
	@include($GLOBALS["cfg"]["__default"]["basedir"]."/lang/" . $LC . "/common.".$GLOBALS["cfg"]["__default"]["ext"]);

	$p = get_instance("periods");
	$p->request_startup();

	$u = get_instance("users");
	$u->request_startup();

	$syslog = get_instance("syslog/syslog");
	$syslog->request_startup();

	$m = get_instance("menuedit");
	$m->request_startup();

//	list($micro,$sec) = split(" ",microtime());
//	$ts_e = $sec + $micro;
	// the following breaks reforb
	#echo("<!-- aw_startup() took ".($ts_e - $ts_s)." seconds -->\n");
}

////
// !called just before the very end
function aw_shutdown()
{
	$apd = get_instance("layout/active_page_data");
	echo $apd->on_shutdown_get_styles();

	global $awt;
	if (is_object($awt))
	{
		$sums = $awt->summaries();

		echo "<!--\n";
		while(list($k,$v) = each($sums))
		{
			print "$k = $v\n";
		};
		echo " querys = ".aw_global_get("qcount")." \n";
		echo "-->\n";
	}

	echo "<!--\n";
	echo "enter_function calls = ".$GLOBALS["enter_function_calls"]." \n";
	echo "exit_function calls = ".$GLOBALS["exit_function_calls"]." \n";
/*	echo "error handler calls = ".$GLOBALS["error_handler_calls"]." \n";
	echo "error handler calls by type: \n";
	foreach($GLOBALS["error_handler_calls_by_type"] as $errno => $cnt)
	{
		echo "    $errno => $cnt \n";
	}
	echo "\nerror handler calls by file: \n";
	arsort($GLOBALS["error_handler_calls_by_file"]);
	foreach($GLOBALS["error_handler_calls_by_file"] as $errno => $cnt)
	{
		arsort($GLOBALS["error_handler_calls_by_file_line"][$errno]);
		echo "    $errno => $cnt (on lines: ".join(" ",map2("lnr: %s , cnt = %s ;",$GLOBALS["error_handler_calls_by_file_line"][$errno])).")\n";
	}*/
	echo "-->\n";
}

function &__get_site_instance()
{
	global $__site_instance;
	if (!is_object($__site_instance))
	{
		@include("site.".$GLOBALS["cfg"]["__default"]["ext"]);
		if (class_exists("site"))
		{
			$__site_instance = new site;
		}
		else
		{
			$__site_instance = false;
		};
	}
	return $__site_instance;
}

function enter_function($name,$args = array())
{
	global $awt;
	if(is_object($awt))
	{
		$awt->start($name);
		$awt->count($name);
	}
	$GLOBALS["enter_function_calls"]++;
}

function exit_function($name,$ret = "")
{
	global $awt;
	if(is_object($awt))
	{
		$awt->stop($name);
	}
	$GLOBALS["exit_function_calls"]++;
}

$GLOBALS["error_handler_calls"] = 0;

function __aw_error_handler($errno, $errstr, $errfile, $errline,  $context)
{
	if ($errno == 8 || $errno == 2)
	{
		$GLOBALS["error_handler_calls"]++;
		if (!isset($GLOBALS["error_handler_calls_by_type"][$errno]))
		{
			$GLOBALS["error_handler_calls_by_type"][$errno] = 0;
		}
		$GLOBALS["error_handler_calls_by_type"][$errno]++;

		if (!isset($GLOBALS["error_handler_calls_by_file"][$errfile]))
		{
			$GLOBALS["error_handler_calls_by_file"][$errfile] = 0;
		}
		$GLOBALS["error_handler_calls_by_file"][$errfile]++;

		if (!isset($GLOBALS["error_handler_calls_by_file_line"][$errfile][$errline]))
		{
			$GLOBALS["error_handler_calls_by_file_line"][$errfile][$errline] = 0;
		}
		$GLOBALS["error_handler_calls_by_file_line"][$errfile][$errline]++;
		return;
	}
	/*ob_start();
	var_dump($context);
	$ct = ob_get_contents();
	ob_end_clean();*/

	$is_rpc_call = $GLOBALS["__aw_globals"]["__is_rpc_call"];
	$rpc_call_type = $GLOBALS["__aw_globals"]["__rpc_call_type"];

	$msg = "Suhtuge veateadetesse rahulikult!  Te ei ole korda saatnud midagi katastroofilist. Ilmselt juhib programm Teie tähelepanu mingile ebatäpsusele  andmetes või näpuveale.<Br><br>\n\n PHP error: errno = $errno , errstr = $errstr, errfile = $errfile, errline = $errline , context = $context\n<br>";

	// meilime veateate listi ka
	$subj = "Viga saidil ".$GLOBALS["cfg"]["__default"]["baseurl"];
	if (!$is_rpc_call && !headers_sent())
	{
		header("X-AW-Error: 1");
	}
	$content = "\nVeateade: ".$msg;
	$content.= "\nKood: ".$err_type;
	$content.= "\nfatal = ".($fatal ? "Jah" : "Ei" )."\n";
	$content.= "PHP_SELF = ".$GLOBALS["__aw_globals"]["PHP_SELF"]."\n";
	$content.= "lang_id = ".$GLOBALS["__aw_globals"]["lang_id"]."\n";
	$content.= "uid = ".$GLOBALS["__aw_globals"]["uid"]."\n";
	$content.= "section = ".$GLOBALS["section"]."\n";
	$content.= "url = ".$GLOBALS["cfg"]["__default"]["baseurl"].$GLOBALS["__aw_globals"]["REQUEST_URI"]."\n-----------------------\n";
	$content.= "is_rpc_call = $is_rpc_call\n";
	$content.= "rpc_call_type = $rpc_call_type\n";
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
	mail("vead@struktuur.ee", $subj, $content,$head);

	die("<br><b>AW_ERROR: $msg</b><br>");
}

/*error_reporting(E_ALL ^ E_NOTICE);
set_error_handler("__aw_error_handler");
error_reporting(E_ALL ^ E_NOTICE);*/
?>
