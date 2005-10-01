<?php

// this should be here, url parsing and variable initialization
// should be the first thing that is done

// apparently __FILE__ does not work with Zend Encoder. But since
// don't use that anyway, it's of no concern. At least now.

// include aw const
include_once(dirname(__FILE__)."/const.aw");

	// Retuns null unless exists $arr[$key1][$key2][...][$keyN] (then returns the value)
	// $a = array_isset($arr,$key1,$key2,$keyN)  
	//         instead of $a=isset($arr[$key1]) && isset($arr[$key2) .... ? $arr[$key1][$key2] : null
	function ifset($item)
	{
		$tmp[] = array();
		$i = 0;
		$count = func_num_args();
		$tmp[0] = $item;
		for (; $i < $count-1; $i++)
		{
			$key = func_get_arg($i+1);
			if (is_array($tmp[$i]) && isset($tmp[$i][$key]))
			{
				$tmp[$i+1] = $tmp[$i][$key];
			}
			else if (is_object($tmp[$i]) && isset($tmp[$i]->$key))
			{
				$tmp[$i+1] = $tmp[$i]->$key;
			}
			else
			{
				return null;
			}
		}
		return $tmp[$i];
	}

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
	return isset($GLOBALS["cfg"][$class]) && array_key_exists($var, $GLOBALS["cfg"][$class]) ? $GLOBALS["cfg"][$class][$var] : null; 
}

function parse_config($file)
{
	$fd = file($file);
	foreach($fd as $k => $line)
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
				foreach($ini_files as $k => $file)
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

	$read_from_cache = false;
	if ($is_cached)
	{
//		list($micro,$sec) = split(" ",microtime());
//		$ts_s = $sec + $micro;
		$f = @fopen($cache_file,"r");
		if ($f && filesize($cache_file) > 0)
		{
			$fc = fread($f,filesize($cache_file));
			fclose($f);
			$GLOBALS["cfg"] = unserialize($fc);
			if (is_array($GLOBALS["cfg"]["__default"]["classes"]) && $GLOBALS["cfg"]["__default"]["frontpage"] > 0)
			{
				$read_from_cache = true;
			}
		}
		else
		{
			$read_from_cache = false;
		};
//		list($micro,$sec) = split(" ",microtime());
//		$ts_e = $sec + $micro;
//		echo "cache unserialize ",($ts_e - $ts_s), " seconds <br>";
//		result: 0.005 - too small to measure correctly
	}


	if (!$read_from_cache)
	{
//		list($micro,$sec) = split(" ",microtime());
//		$ts_s = $sec + $micro;
		
		// now deduce the aw path from the rootini file path
		$basedir = dirname($ini_files[0]);
		$GLOBALS["cfg"]["__default"]["basedir"] = $basedir;

		// also, site_basedir from the second ini file
		$site_basedir = isset($ini_files[1]) ? dirname($ini_files[1]) : "";
		$GLOBALS["cfg"]["__default"]["site_basedir"] = $site_basedir;

		// now, baseurl
		$baseurl = isset($GLOBALS["HTTP_SERVER_VARS"]["HTTP_HOST"]) ? "http://".$GLOBALS["HTTP_SERVER_VARS"]["HTTP_HOST"] : "";
		$GLOBALS["cfg"]["__default"]["baseurl"] = $baseurl;
		foreach($ini_files as $k => $file)
		{
			parse_config($file);
		}

		// and write to cache if file is specified
		if (!empty($cache_file))
		{
			$str = serialize($GLOBALS["cfg"]);
			
			$f = @fopen($cache_file,"w");
			if (!$f)
			{
				die(t("pagecache is not writable, cannot continue!"));
			};
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
		// I don't know how the fuck it happens, but somethis these things are not arrays
		// check it and bail out if so. Not a solution, but still kind of better than
		// pages of error messages
		if (!is_array($GLOBALS["cfg"]["__default"]["classes"]))
		{
			return false;
		};
		// and here do the defs for classes
		foreach($GLOBALS["cfg"]["__default"]["classes"] as $clid => $cld)
		{
			define($cld["def"], $clid);
			$GLOBALS["cfg"]["class_lut"][basename($cld["file"])] = $clid;
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
			if (isset($std['def']) && !defined($std["def"]))
			{
				define($std["def"], $stid);
			}
		}
		// defines fos syslog actions
		foreach($GLOBALS["cfg"]["syslog"]["actions"] as $said => $sad)
		{
			define($sad["def"], $said);
		}
		if (is_array($GLOBALS["cfg"]["translate"]["ids"]))
		{
			foreach($GLOBALS["cfg"]["translate"]["ids"] as $tid => $tdef)
			{
				define($tdef,$tid);
			}
		}
	};

	// db driver quoting settings
	$GLOBALS['cfg']['__default']['magic_quotes_runtime'] = ini_get('magic_quotes_runtime');

	// also, make a short version on __default that is used to init classes
	$td = $GLOBALS["cfg"]["__default"];

	unset($td["classes"]);
	unset($td["classfolders"]);
	unset($td["programs"]);
	unset($td["errors"]);
	
	$GLOBALS["cfg"]["__default__short"] = $td;

	if (!empty($GLOBALS["cfg"]["__default"]["session_handler"]))
	{
		if ($GLOBALS["cfg"]["__default"]["session_handler"] == "db")
		{
			classload("core/aw_session");
			$ses_class = new aw_session();

			session_set_save_handler (array(&$ses_class, '_open'),
					  array(&$ses_class, '_close'),
					  array(&$ses_class, '_read'),
					  array(&$ses_class, '_write'),
					  array(&$ses_class, '_destroy'),
					  array(&$ses_class, '_gc'));
		}
	};
}

// this is separate from ini parsing, because the session is not started yet, when ini file is parsed :(
function lc_init()
{
	// see if user has an ui language pref
	if (isset($_SESSION['user_adm_ui_lc']) && ($_tmp = $_SESSION["user_adm_ui_lc"]) != "")
	{
		$GLOBALS["cfg"]["user_interface"]["default_language"] = $_tmp;
	}

	// translate class names if it is so said
	if (isset($GLOBALS["cfg"]["user_interface"]) && ($adm_ui_lc = $GLOBALS["cfg"]["user_interface"]["default_language"]) != "")
	{
		$trans_fn = $GLOBALS["cfg"]["__default"]["basedir"]."/lang/trans/$adm_ui_lc/aw/aw.ini.aw";
		if (file_exists($trans_fn))
		{
			require_once($trans_fn);
			foreach($GLOBALS["cfg"]["__default"]["classes"] as $clid => $cld)
			{
				if (($_tmp = t2("Klassi ".$cld["name"]." ($clid) nimi")) != "")
				{
					$GLOBALS["cfg"]["__default"]["classes"][$clid]["name"] = $_tmp;
				}
			}

			foreach($GLOBALS["cfg"]["__default"]["classfolders"] as $clid => $cld)
			{
				if (($_tmp = t2("Klassi kataloogi ".$cld["name"]." ($clid) nimi")) != "")
				{
					$GLOBALS["cfg"]["__default"]["classfolders"][$clid]["name"] = $_tmp;
				}
			}
		}
	}
}

// this will not save the new value to the ini file
function aw_ini_set($class,$key,$value)
{
	if ($class == "")
	{
		$GLOBALS["cfg"]["__default"][$key] = $value;
		$GLOBALS["cfg"]["__default__short"][$key] = $value;
	}
	else
	{
		$GLOBALS["cfg"][$class][$key] = $value;
	}
}

function aw_config_init_class(&$that)
{
//	enter_function("__global::aw_config_init_class",array());
	$class = get_class($that);
	$that->cfg = array_merge((isset($GLOBALS["cfg"][$class]) ? $GLOBALS["cfg"][$class] : array()),$GLOBALS["cfg"]["__default__short"]);
	$that->cfg["acl"] = $GLOBALS["cfg"]["acl"];
	$that->cfg["config"] = $GLOBALS["cfg"]["config"];
//	exit_function("__global::aw_config_init_class");
}


// loads localization constants
function lc_load($file)
{
//	enter_function("__global::lc_load",array());
//	global $LC,$admin_lang_lc;
	$LC = isset($GLOBALS["__aw_globals"]) ? $GLOBALS["__aw_globals"]["LC"] : "";
	$admin_lang_lc = isset($GLOBALS["__aw_globals"]) && isset($GLOBALS["__aw_globals"]["admin_lang_lc"]) ? $GLOBALS["__aw_globals"]["admin_lang_lc"] : false;
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
	$LC = aw_global_get("admin_lang_lc");
	if ($LC == "")
	{
		$LC = "et";
	}
	$fname = $GLOBALS["cfg"]["__default"]["site_basedir"]."/lang/".$LC."/$file.".$GLOBALS["cfg"]["__default"]["ext"];
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

function aw_classload($args)
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
		// check if we need to load a site class instead
		if (isset($GLOBALS["cfg"]["__default"]["site_classes"][$lib]))
		{
			$lib = $GLOBALS["cfg"]["__default"]["site_classes"][$lib];
			$lib = $GLOBALS["cfg"]["__default"]["site_basedir"]."/classes/".$lib.".".$GLOBALS["cfg"]["__default"]["ext"];
		}
		else
		{
			if (substr($lib,0,13) == "designedclass")
			{
				$lib = basename($lib);
				$lib = $GLOBALS["cfg"]["__default"]["site_basedir"]."/files/classes/".$lib.".".$GLOBALS["cfg"]["__default"]["ext"];
			}
			else
			{
				$lib = $GLOBALS["cfg"]["__default"]["classdir"]."/".$lib.".".$GLOBALS["cfg"]["__default"]["ext"];

				if (isset($GLOBALS['cfg']['user_interface']) && ($adm_ui_lc = $GLOBALS["cfg"]["user_interface"]["default_language"]) != "")
				{
					$trans_fn = $GLOBALS["cfg"]["__default"]["basedir"]."/lang/trans/$adm_ui_lc/aw/".basename($lib);
//echo "classload: tf = $trans_fn <br>";
					if (file_exists($trans_fn))
					{
						require_once($trans_fn);
					}
				}
			}
		}
		include_once($lib);
	};
//	exit_function("__global::classload");
}

function get_instance($class,$args = array(), $errors = true)
{
	enter_function("__global::get_instance",array());

	$site = $designed = false;
	if (is_numeric($class))
	{
		if (!isset($GLOBALS["cfg"]["__default"]["classes"][$class]))
		{
			$designed = true;
		}
		else
		{
			$class = $GLOBALS["cfg"]["__default"]["classes"][$class]["file"];
		}
	}
	if (isset($GLOBALS["cfg"]["__default"]["site_classes"]) && isset($GLOBALS["cfg"]["__default"]["site_classes"][$class]))
	{
		$class = $GLOBALS["cfg"]["__default"]["site_classes"][$class];
		$site = true;
	}
	if (substr($class,0,13) == "designedclass")
	{
		$designed = true;
	};

	$id = "instance::" . $class;
	$instance = aw_global_get($id);

	$lib = basename($class);

	// check if the class is remoted. if it is, then create proxy class instance, not real class instance
	$clid = (isset($GLOBALS['cfg']['class_lut']) && isset($GLOBALS["cfg"]["class_lut"][$lib])) ? $GLOBALS["cfg"]["class_lut"][$lib] : 0;
	if (($rs = ifset($GLOBALS,"cfg","__default","classes",$clid,"is_remoted")) != "")
	{
		if ($rs != $GLOBALS["cfg"]["__default"]["baseurl"])
		{
			$proxy_file = $GLOBALS["cfg"]["__default"]["basedir"]."/classes/core/proxy_classes/".$lib.".aw";
			$proxy_class = "__aw_proxy_".$lib;
			include_once($proxy_file);
			return new $proxy_class($rs);
		}
	}

	if (not(is_object($instance)))
	{
		if ($site)
		{
			$classdir = $GLOBALS["cfg"]["__default"]["site_basedir"]."/classes";
		}
		else if ($designed)
		{
			$classdir = $GLOBALS["cfg"]["__default"]["site_basedir"]."/files/classes";
			$class = basename($class);
			$lib = $GLOBALS["gen_class_name"];
//echo "dir = $classdir class = $class , lib = $lib <br>";
		}
		else
		{
			$classdir = $GLOBALS["cfg"]["__default"]["classdir"];
		}
		$ext = $GLOBALS["cfg"]["__default"]["ext"];
		if (!file_exists($classdir."/".str_replace(".","", $class).".".$ext))
		{
			if (!$errors)
			{
				return false;
			}
			error::raise(array(
				"id" => ERR_CLASS,
				"msg" => t("the requested class $class does not exist !"),
			));
		}
		error_reporting(E_PARSE | E_ERROR);
		require_once($classdir."/".str_replace(".","", $class).".".$ext);

		// also load translations
		if (isset($GLOBALS["cfg"]["user_interface"]) && ($adm_ui_lc = $GLOBALS["cfg"]["user_interface"]["default_language"]) != "")
		{
			$trans_fn = $GLOBALS["cfg"]["__default"]["basedir"]."/lang/trans/$adm_ui_lc/aw/".basename($class).".aw";
//echo "get_instance: tf = $trans_fn <br>";
			if (file_exists($trans_fn))
			{
				require_once($trans_fn);
			}
		}

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

	if (aw_global_get("__is_install") && method_exists($instance, "init"))
	{
		$instance->init();
	}

	exit_function("__global::get_instance",array());
	return $instance;
}

function load_class_translations($class)
{
	if (($adm_ui_lc = ifset($GLOBALS,"cfg","user_interface","default_language")) != "")
	{
		$trans_fn = $GLOBALS["cfg"]["__default"]["basedir"]."/lang/trans/$adm_ui_lc/aw/".basename($class).".aw";
		if (file_exists($trans_fn))
		{
			require_once($trans_fn);
		}
	}
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
	include_once($GLOBALS["cfg"]["__default"]["classdir"]."/vcl/$lib.".$GLOBALS["cfg"]["__default"]["ext"]);
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



	classload("defs", "core/error", "core/obj/object");
	_aw_global_init();
	$l = get_instance("languages");
	$l->request_startup();
	
	// check multi-lang frontpage
	if (is_array($GLOBALS["cfg"]["__default"]["frontpage"]))
	{
		$GLOBALS["cfg"]["__default"]["frontpage"] = $GLOBALS["cfg"]["__default"]["frontpage"][aw_global_get("lang_id")];
	}

	$LC = aw_global_get("LC");

	@include($GLOBALS["cfg"]["__default"]["basedir"]."/lang/" . $LC . "/errors.".$GLOBALS["cfg"]["__default"]["ext"]);
	@include($GLOBALS["cfg"]["__default"]["basedir"]."/lang/" . $LC . "/common.".$GLOBALS["cfg"]["__default"]["ext"]);

	$p = get_instance(CL_PERIOD);
	$p->request_startup();
	
	// this check reduces the startup memory usage for not logged in users by a whopping 1.3MB! --duke
	// 
	// the check was if user is logged on. now we need to do this all the time, because public users are acl controlled now. 
	$u = get_instance("users");
	$u->request_startup();
	
	if (!is_array(aw_global_get("gidlist")))
	{
		aw_global_set("gidlist", array());
		aw_global_set("gidlist_pri", array());
	}

	#$syslog = get_instance("syslog/syslog");
	#$syslog->request_startup();
	
	$m = get_instance("menuedit");
	$m->request_startup();
	
	__init_aw_session_track();

//	list($micro,$sec) = split(" ",microtime());
//	$ts_e = $sec + $micro;
	// the following breaks reforb
	#echo("<!-- aw_startup() took ".($ts_e - $ts_s)." seconds -->\n");
}

////
// !called just before the very end
function aw_shutdown()
{
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
		$fname = "site.".$GLOBALS["cfg"]["__default"]["ext"];
		if (file_exists($fname))
		{
			include($fname);
		}
		else
		{
			$fname = aw_ini_get("site_basedir")."/public/".$fname;
			if (file_exists($fname))
			{
				include($fname);
			}
		}
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
	if (!isset($GLOBALS['enter_function_calls']))
	{
		$GLOBALS["enter_function_calls"] = 0;
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
	if (!isset($GLOBALS["exit_function_calls"]))
	{
		$GLOBALS["exit_function_calls"] = 0;
	}
	$GLOBALS["exit_function_calls"]++;
}

function __init_aw_session_track()
{
	if (($tmp = ifset($_SESSION,"aw_session_track","aw","do_redir")) != "")
	{
		$_SESSION["aw_session_track"]["aw"]["do_redir"] = "";
		header("Location: ".$tmp);
		die();
	}

	if (($tmp = ifset($_SESSION,"aw_session_track","aw","do_message")) != "")
	{
		$_SESSION["aw_session_track"]["aw"]["do_message"] = "";
		echo "<script language=\"javascript\">alert(\"".$tmp."\");</script>";
	}

	// add session tracking options
	$_SESSION["aw_session_track"] = array(
		"server" => array(
			"ip" => ifset($_SERVER,"REMOTE_ADDR"),
			"referer" => ifset($_SERVER,"HTTP_REFERER"),
			"ru" => $GLOBALS["REQUEST_URI"],
			"site" => $GLOBALS["HTTP_HOST"],
		),
		"aw" => array(
			"site_id" => aw_ini_get("site_id"),
			"lang_id" => aw_global_get("lang_id"),
			"uid" => aw_global_get("uid"),
			"timestamp" => time()
		)
	);
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

		// also attach backtrace
		if (function_exists("debug_backtrace"))
		{
			$content .= "<br><br> Backtrace: \n\n<Br><br>";
			$bt = debug_backtrace();
			for ($i = count($bt)-1; $i > 0; $i--)
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

				$content .= $fnm." on line ".$bt[$i]["line"]." called <br>\n";

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

				$conten .= $fnm2." with arguments ";

				$content .= "<font size=\"-1\">(".join(",", $bt[$i]["args"]).") file = ".$bt[$i]["file"]."</font>";
			
				$content .= " <br><br>\n\n";
			}
		}

	$head = "";
	//mail("vead@struktuur.ee", $subj, $content,$head);

	die(t("<br><b>AW_ERROR: $msg</b><br>"));
}

function log_pv($mt)
{
	//list($usec, $sec) = explode(" ", $mt);
	$start = $mt ;//((float)$usec + (float)$sec);

	list($usec, $sec) = explode(" ", microtime());
	$end = ((float)$usec + (float)$sec);

	$time = $end - $start;

	if ($time > 1000)
	{
		$time = "0";
	}

	// log pv to file
	$fn = aw_ini_get("basedir")."/files/logs/";
	if (!is_dir($fn))
	{
		@mkdir($fn);
	}
	$fn .= date("Y-m-d").".log";

	$f = @fopen($fn, "a");
	if ($f)
	{
		fwrite($f, date("d.m.Y H:i:s")." ".aw_ini_get("site_id")." ".aw_ini_get("baseurl")." ".aw_global_get("REQUEST_URI")." $time \n");
		fclose($f);
	}
}

function t($s)
{
	return isset($GLOBALS["TRANS"][$s]) ? $GLOBALS["TRANS"][$s] : $s;
}

function t2($s)
{
	return isset($GLOBALS["TRANS"][$s]) ? $GLOBALS["TRANS"][$s] : NULL;
}

//error_reporting(E_ALL ^ E_NOTICE);
//set_error_handler("__aw_error_handler");
//error_reporting(E_ALL ^ E_NOTICE);
?>
