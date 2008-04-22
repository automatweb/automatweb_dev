<?php
/*
@classinfo  maintainer=kristo
*/

//  this should be here, url parsing and variable initialization
// should be the first thing that is done
error_reporting(E_PARSE | E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
ini_set("display_errors", "On");
// apparently __FILE__ does not work with Zend Encoder. But since
// don't use that anyway, it's of no concern. At least now.

class aw_exception extends Exception {}
set_exception_handler("aw_exception_handler");

// include aw const
include_once(dirname(__FILE__)."/const.aw");

// Retuns null unless exists $arr[$key1][$key2][...][$keyN] (then returns the value)
// $a = array_isset($arr,$key1,$key2,$keyN)
//         instead of $a=isset($arr[$key1]) && isset($arr[$key2) .... ? $arr[$key1][$key2] : null
function ifset(&$item_orig)
{
	// enter_function("ifset");
	$i = 0;
	$count = func_num_args();
	$item =& $item_orig;
	for (; $i < $count-1; $i++)
	{
		$key = func_get_arg($i+1);
		if (is_array($item) && isset($item[$key]))
		{
			$item =& $item[$key];
		}
		else if (is_object($item) && isset($item->$key))
		{
			$item =& $item->$key;
		}
		else
		{
			// exit_function("ifset");
			return null;
		}
	}
/*		$tmp[0] = $item_orig;
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
			exit_function("ifset");
			return null;
		}
	}
*/
	// exit_function("ifset");
	return $item;
//	return $tmp[$i];
}

function aw_ini_get($var)
{
//	enter_function("__global::aw_ini_get",array());
	$path = explode(".", $var);

	if ("" === $path[0])
	{
		throw new aw_exception("Invalid key");
	}
	else
	{
		foreach ($path as $index)
		{
			if (isset($val[$index]))
			{
				$val = $val[$index];
			}
			elseif(!isset($val) and isset($GLOBALS["cfg"][$index]))
			{
				$val = $GLOBALS["cfg"][$index];
			}
			else
			{
				throw new aw_exception("Invalid key '" . $var . "'");
			}
		}
	}

//	exit_function("__global::aw_ini_get");
	return $val;
}

// this will not save the new value to the ini file
function aw_ini_set($var, $value, $save = false)
{
	$setting = "\$GLOBALS['cfg']['" . str_replace(".", "']['", $var) . "'] = " . var_export($value, true) . ";";
	eval($setting);

	if (false === strpos($var, "."))
	{
		$setting = "\$GLOBALS['cfg']['__default__short']['" . str_replace(".", "']['", $var) . "'] = " . var_export($value, true) . ";";
		eval($setting);
	}

	// if ($save)
	// {
	// }
}

function parse_config($file, $return = false)
{
	$fd = file($file);
	$config = array();

	foreach($fd as $linenum => $line)
	{
		// ok, parse line
		if (strlen(trim($line)) and $line{0} != "#") // exclude comments and empty lines
		{
			// now, config opts are variable = value. variable is class1. ... .classN.
			$data = explode("=", $line, 2);

			if (2 === count($data))
			{ // process regular variable
				$var = str_replace(array('["','"]',"['","']","[","]"), array(".","",".", "",".", ""), trim($data[0]));//!!! should be deprecated and only '.' notation used. kept here for back compatibility.
				$value = trim($data[1]);

				// now, replace all variables in varvalue
				$value = preg_replace('/\$\{(.*)\}/e', "aw_ini_get(\"\\1\")",$value);
				$var = preg_replace('/\$\{(.*)\}/e', "aw_ini_get(\"\\1\")",$var);

				// add setting
				if ($return)
				{
					$config[] = $var . "=" . $value;
				}
				else
				{
					$setting_index = explode(".", $var);
					$setting_path = "\$GLOBALS['cfg']";

					foreach ($setting_index as $key => $index)
					{
						$setting_path .= "['" . $index . "']";

						if (isset($setting_index[$key + 1]) and eval("return (isset(" . $setting_path . ") and !is_array(" . $setting_path . "));"))
						{
							eval($setting_path . " = array();");
						}
					}

					$setting = "\$GLOBALS['cfg']['" . str_replace(".", "']['", $var) . "'] = " . var_export($value, true) . ";";
					eval($setting);
				}
			}
			elseif ("include" === substr(trim($line), 0, 7))
			{ // process config file include
				$line = preg_replace('/\$\{(.*)\}/e',"aw_ini_get(\"\\1\")",$line);
				$ifile = trim(substr($line, 7));

				if (!file_exists($ifile) || !is_readable($ifile))
				{
					if (isset($GLOBALS["stderr"]))
					{
						fwrite($GLOBALS["stderr"], "Failed to open include file '" . $ifile . "' on line " . $linenum+1 . " in file '" . $file . "'\n");
					}

					return false;
				}

				if ($return)
				{
					$config = array_merge($config, parse_config($ifile, true));
				}
				else
				{
					parse_config($ifile);
				}
			}
		}
	}

	if ($return)
	{
		return $config;
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
//	result: 0.00013 - too small to measure correctly

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
			if (is_array($GLOBALS["cfg"]["classes"]) && $GLOBALS["cfg"]["frontpage"] > 0)
			{
				$read_from_cache = true;
			}
		}
		else
		{
			$read_from_cache = false;
		}
//		list($micro,$sec) = split(" ",microtime());
//		$ts_e = $sec + $micro;
//		echo "cache unserialize ",($ts_e - $ts_s), " seconds <br>";
//		result: 0.005 - too small to measure correctly
	}

//selle peab ikka igaltpoolt uuesti saama, muidu ei saa sisev6rgust ja mujalt ligi
	if (!$GLOBALS["cfg"]["no_update_baseurl"])
	{
		$baseurl = isset($_SERVER["HTTP_HOST"]) ? "http://".$_SERVER["HTTP_HOST"] : "";
		$GLOBALS["cfg"]["baseurl"] = $baseurl;
	}

	if (!$read_from_cache)
	{
//		list($micro,$sec) = split(" ",microtime());
//		$ts_s = $sec + $micro;

		// now deduce the aw path from the rootini file path
		$basedir = dirname($ini_files[0]);
		$GLOBALS["cfg"]["basedir"] = $basedir;

		// also, site_basedir from the second ini file
		$site_basedir = isset($ini_files[1]) ? dirname($ini_files[1]) : "";
		$GLOBALS["cfg"]["site_basedir"] = $site_basedir;

		// now, baseurl
		// XXX: what about https urls though?
//		$baseurl = isset($_SERVER["HTTP_HOST"]) ? "http://".$_SERVER["HTTP_HOST"] : "";
//		$GLOBALS["cfg"]["baseurl"] = $baseurl;
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
	aw_ini_set("site_tpldir", aw_ini_get("tpldir"));

	if (strpos($_SERVER["PHP_SELF"],"automatweb"))
	{
		// keemia. Kui oleme saidi adminnis sees, siis votame templated siit
		aw_ini_set("tpldir", aw_ini_get("basedir") . "/templates");

		// lots of places in code need to know whether we are in admin interface
		// and use different approaches for that .. let's do it here instead
		aw_ini_set("in_admin", 1);
	}
	else
	{
		aw_ini_set("in_admin", 0);
	}
	// kui saidi "sees", siis votame templated tolle saidi juurest, ehk siis ei puutu miskit

	// only load those definitions if fastcall is not set. This shouldnt break anything
	// and should save us a little memory. -- duke
	if (!isset($_GET["fastcall"]))
	{
		// I don't know how the fuck it happens, but somethis these things are not arrays
		// check it and bail out if so. Not a solution, but still kind of better than
		// pages of error messages
		if (!is_array($GLOBALS["cfg"]["classes"]))
		{
			return false;
		}

		// and here do the defs for classes
		foreach($GLOBALS["cfg"]["classes"] as $clid => $cld)
		{
			if (isset($cld["def"]))
			{
				define($cld["def"], $clid);
				if (isset($cld["file"]))
				{
					$bnf = basename($cld["file"]);
					if (!isset($GLOBALS["cfg"]["class_lut"][$bnf]))
					{
						$GLOBALS["cfg"]["class_lut"][$bnf] = $clid;
					}
				}
			}
		}

		// special case for doc
		$GLOBALS["cfg"]["class_lut"]["doc"] = 7;

		// and here do the defs for programs
		foreach($GLOBALS["cfg"]["programs"] as $prid => $prd)
		{
			define($prd["def"], $prid);
		}

		// and here do the defs for errors
		foreach($GLOBALS["cfg"]["errors"] as $erid => $erd)
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
	}

	// db driver quoting settings
	aw_ini_set("magic_quotes_runtime", ini_get('magic_quotes_runtime'));

	// also, make a short version on __default that is used to init classes
	foreach ($GLOBALS["cfg"] as $key => $value)
	{
		if (!is_array($value))
		{
			$GLOBALS["cfg"]["__default__short"][$key] = $value;
		}
	}

	if (!empty($GLOBALS["cfg"]["session_handler"]))
	{
		if ($GLOBALS["cfg"]["session_handler"] == "db")
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
	}
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
	if (isset($GLOBALS["cfg"]["user_interface"]["default_language"]) && ($adm_ui_lc = $GLOBALS["cfg"]["user_interface"]["default_language"]) != "")
	{
		$trans_fn = $GLOBALS["cfg"]["basedir"]."/lang/trans/$adm_ui_lc/aw/aw.ini.aw";
		if (file_exists($trans_fn))
		{
			incl_f($trans_fn);
			if (file_exists($trans_fn) && is_readable($trans_fn))
			{
				require_once($trans_fn);
			}
			foreach($GLOBALS["cfg"]["classes"] as $clid => $cld)
			{
				if (isset($cld["name"]) && ($_tmp = t2("Klassi ".$cld["name"]." ($clid) nimi")) != "")
				{
					$GLOBALS["cfg"]["classes"][$clid]["name"] = $_tmp;
				}
				if(isset($cld["prod_family"]) && ($_tmp = t2("Klassi tooteperekonna ".$cld["prod_family"]." ($clid) nimi")) != "")
				{
					$GLOBALS["cfg"]["classes"][$clid]["prod_family"] = $_tmp;
				}
			}

			foreach($GLOBALS["cfg"]["classfolders"] as $clid => $cld)
			{
				if (($_tmp = t2("Klassi kataloogi ".$cld["name"]." ($clid) nimi")) != "")
				{
					$GLOBALS["cfg"]["classfolders"][$clid]["name"] = $_tmp;
				}
			}


			foreach($GLOBALS["cfg"]["acl"]["names"] as $n => $cap)
			{
				if(($_tmp = t2("ACL tegevuse ".$cap." (".$n.") nimi")) != "")
				{
					$GLOBALS["cfg"]["acl"]["names"][$n] = $_tmp;
				}
			}

			foreach($GLOBALS["cfg"]["syslog"]["types"] as $typid => $td)
			{
				if (isset($td["def"]) && ($_tmp = t2("syslog.type.".$td["def"])) != "")
				{
					$GLOBALS["cfg"]["syslog"]["types"][$typid]["name"] = $_tmp;
				}
			}

			foreach($GLOBALS["cfg"]["syslog"]["actions"] as $actid => $ad)
			{
				if (($_tmp = t2("syslog.action.".$ad["def"])) != "")
				{
					$GLOBALS["cfg"]["syslog"]["actions"][$actid]["name"] = $_tmp;
				}
			}

			foreach($GLOBALS["cfg"]["languages"]["list"] as $laid => $ad)
			{
				if (($_tmp = t2("languages.list.".$ad["acceptlang"])) != "")
				{
					$GLOBALS["cfg"]["languages"]["list"][$laid]["name"] = $_tmp;
				}
			}
		}
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
	$LC = isset($GLOBALS["__aw_globals"]) ? $GLOBALS["__aw_globals"]["LC"] : "";
	$admin_lang_lc = isset($GLOBALS["__aw_globals"]) && isset($GLOBALS["__aw_globals"]["admin_lang_lc"]) ? $GLOBALS["__aw_globals"]["admin_lang_lc"] : false;
	if (!$admin_lang_lc)
	{
		$admin_lang_lc = "et";
	}
	$fn = $GLOBALS["cfg"]["basedir"]."/lang/" . $admin_lang_lc . "/$file.".$GLOBALS["cfg"]["ext"];
	incl_f($fn);
	if (file_exists($fn) && is_readable($fn))
	{
		include_once($fn);
	}
}

// loads localization constants from the site's $site_basedir
function lc_site_load($file,&$obj)
{
//	enter_function("__global::lc_site_load",array());
	//$LC = aw_global_get("admin_lang_lc");

	if (aw_ini_get("user_interface.full_content_trans") == 1)
	{
		$LC = aw_global_get("ct_lang_lc");
	}
	else
	{
		$LC = aw_global_get("LC");
	}
	if ($LC == "")
	{
		$LC = "et";
	}
	$fname = $GLOBALS["cfg"]["site_basedir"]."/lang/".$LC."/$file.".$GLOBALS["cfg"]["ext"];
	incl_f($fname);
	if (file_exists($fname) && is_readable($fname))
	{
		include_once($fname);
	}
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
		$lib = $GLOBALS["cfg"]["classdir"]."/".$lib.".".$GLOBALS["cfg"]["ext"];
		incl_f($lib);
		if (file_exists($lib) && is_readable($lib))
		{
			include_once($lib);
		}
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
		$lib = $olib = str_replace(".","", $lib);

		try
		{
			$cl_id = aw_ini_get("class_lut.".basename($lib));
		}
		catch (Exception $e)
		{
		}

		if ($GLOBALS["cfg"]["classes"][$cl_id]["site_class"] == 1)
		{
			$lib = $GLOBALS["cfg"]["site_basedir"]."/classes/".basename($lib).".".$GLOBALS["cfg"]["ext"];
		}
		elseif (substr($lib,0,13) == "designedclass")
		{
			$lib = basename($lib);
			$lib = $GLOBALS["cfg"]["site_basedir"]."/files/classes/".$lib.".".$GLOBALS["cfg"]["ext"];
		}
		else
		{
			$lib = $GLOBALS["cfg"]["classdir"]."/".$lib.".".$GLOBALS["cfg"]["ext"];

			if (isset($GLOBALS['cfg']['user_interface']["default_language"]) && ($adm_ui_lc = $GLOBALS["cfg"]["user_interface"]["default_language"]) != "")
			{
				$trans_fn = $GLOBALS["cfg"]["basedir"]."/lang/trans/$adm_ui_lc/aw/".basename($lib);
				if (file_exists($trans_fn))
				{
					incl_f($trans_fn);
					if (file_exists($trans_fn) && is_readable($trans_fn))
					{
						require_once($trans_fn);
					}
				}
			}
		}
		incl_f($lib);
		if (file_exists($lib) && is_readable($lib))
		{
			include_once($lib);
		}
		else
		{
			// try to handle it with class_index and autoload
			__autoload(basename($olib));

			/*classload("core/error");
			error::raise(array(
				"id" => "ERR_NO_CLASS",
				"msg" => sprintf(t("classload(): class %s not found!"), $lib)
			));*/
		}
		if (file_exists($lib) && is_readable($lib))
		{
			include_once($lib);
		}
	};
//	exit_function("__global::classload");
}

function get_instance($class,$args = array(), $errors = true)
{
if (!empty($GLOBALS["TRACE_INSTANCE"]))
{
	echo "get_instance $class from ".dbg::short_backtrace()." <br>";
}
	enter_function("__global::get_instance",array());

	$site = $designed = false;
	if (is_numeric($class))
	{
		if (!isset($GLOBALS["cfg"]["classes"][$class]))
		{
			$designed = true;
		}
		else
		{
			$class = $GLOBALS["cfg"]["classes"][$class]["file"];
		}
	}


	try
	{
		$cl_id = aw_ini_get("class_lut.".basename($class));
		if ($GLOBALS["cfg"]["classes"][$cl_id]["site_class"] == 1)
		{
			//$class = $GLOBALS["cfg"]["site_classes"][$class];
			$site = true;
		}
	}
	catch (Exception $e)
	{
		$site = false;
	}

	if (substr($class,0,13) == "designedclass")
	{
		$designed = true;
	}

	$lib = basename($class);
	$rs = "";
	$clid = (isset($GLOBALS['cfg']['class_lut']) && isset($GLOBALS["cfg"]["class_lut"][$lib])) ? $GLOBALS["cfg"]["class_lut"][$lib] : 0;
	if (isset($GLOBALS['cfg']['classes'][$clid]))
	{
		$clinf = $GLOBALS['cfg']['classes'][$clid];
		$rs = isset($clinf["is_remoted"]) ? $clinf["is_remoted"] : null;
	};
	// check if the class is remoted. if it is, then create proxy class instance, not real class instance
	if ($rs != "")
	{
		if ($rs != $GLOBALS["cfg"]["baseurl"])
		{
			$proxy_file = $GLOBALS["cfg"]["basedir"]."/classes/core/proxy_classes/".$lib.".aw";
			$proxy_class = "__aw_proxy_".$lib;
			incl_f($proxy_file);
			include_once($proxy_file);
			return new $proxy_class($rs);
		}
	}

		if ($site)
		{
			$classdir = $GLOBALS["cfg"]["site_basedir"]."/classes";
		}
		else if ($designed)
		{
			$classdir = $GLOBALS["cfg"]["site_basedir"]."/files/classes";
			$class = basename($class);
			$lib = $GLOBALS["gen_class_name"];
//echo "dir = $classdir class = $class , lib = $lib <br>";
		}
		else
		{
			$classdir = $GLOBALS["cfg"]["classdir"];
		}
		$ext = $GLOBALS["cfg"]["ext"];
		if (!file_exists($classdir."/".str_replace(".","", $class).".".$ext))
		{
			__autoload(basename($class));
			/*if (!$errors)
			{
				return false;
			}
			if (class_exists('error'))
			{
				error::raise(array(
					"id" => ERR_CLASS,
					"msg" => t("the requested class $class does not exist !"),
				));
			}
			else
			{
				print("Class $class does not exist. Also, class 'error' not loaded.");
			}*/
		}
		$_fn = $classdir."/".str_replace(".","", $class).".".$ext;
		incl_f($_fn);
		if (file_exists($_fn) && is_readable($_fn))
		{
			require_once($_fn);
		}

		// also load translations
		if (isset($GLOBALS["cfg"]["user_interface"]["default_language"]) && ($adm_ui_lc = $GLOBALS["cfg"]["user_interface"]["default_language"]) != "")
		{
			$trans_fn = $GLOBALS["cfg"]["basedir"]."/lang/trans/$adm_ui_lc/aw/".basename($class).".aw";
//echo "get_instance: tf = $trans_fn <br>";
			if (file_exists($trans_fn))
			{
				incl_f($trans_fn);
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
		}
		else
		{
			$instance = false;
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
	if (empty($GLOBALS["cfg"]["user_interface"]["default_language"]))
	{
		return;
	}
	$adm_ui_lc = $GLOBALS["cfg"]["user_interface"]["default_language"];
	$trans_fn = $GLOBALS["cfg"]["basedir"]."/lang/trans/$adm_ui_lc/aw/".basename($class).".aw";
	if (file_exists($trans_fn) && is_readable($trans_fn))
	{
		incl_f($trans_fn);
		require_once($trans_fn);
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
	if (isset($GLOBALS['cfg']['user_interface']) && ($adm_ui_lc = $GLOBALS["cfg"]["user_interface"]["default_language"]) != "")
	{
		$trans_fn = $GLOBALS["cfg"]["basedir"]."/lang/trans/$adm_ui_lc/aw/".basename($lib).".aw";
		if (file_exists($trans_fn) && is_readable($trans_fn))
		{
			incl_f($trans_fn);
			require_once($trans_fn);
		}
	}
	$fn = $GLOBALS["cfg"]["classdir"]."/vcl/$lib.".$GLOBALS["cfg"]["ext"];
	incl_f($fn);
	if (file_exists($fn) && is_readable($fn))
	{
		include_once($fn);
	}
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

	// check pagecache folders
	check_pagecache_folders();

	classload("defs", "core/error", "core/obj/object");
	_aw_global_init();

	//$m = get_instance("menuedit");
	//$m->request_startup();

	$l = get_instance("languages");
	$l->request_startup();

	// check multi-lang frontpage
	if (is_array(aw_ini_get("frontpage")))
	{
		$tmp = aw_ini_get("frontpage");
		$GLOBALS["cfg"]["ini_frontpage"] = $tmp;
		$GLOBALS["cfg"]["frontpage"] = $tmp[aw_global_get("lang_id")];
	}

	$LC = $GLOBALS["cfg"]["user_interface"]["full_content_trans"] ? aw_global_get("ct_lang_lc") : aw_global_get("LC");

	@include($GLOBALS["cfg"]["basedir"]."/lang/" . $LC . "/errors.".$GLOBALS["cfg"]["ext"]);
	@include($GLOBALS["cfg"]["basedir"]."/lang/" . $LC . "/common.".$GLOBALS["cfg"]["ext"]);
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

	aw_global_set("aw_init_done", 1);

	$m = get_instance("menuedit");
	$m->request_startup();
	__init_aw_session_track();


//	list($micro,$sec) = split(" ",microtime());
//	$ts_e = $sec + $micro;
	// the following breaks reforb
	#echo("<!-- aw_startup() took ".($ts_e - $ts_s)." seconds -->\n");
//die("lid = ".aw_global_get("lang_id")." fp = ".aw_ini_get("frontpage")." <br>");
}

////
// !called just before the very end
function aw_shutdown()
{
	// whotta fook, this messenger thingie goes here then?:S

	$i = get_instance("file");
	if($i->can("view", $_SESSION["current_user_has_messenger"]) && $i->can("view", $_SESSION["uid_oid"]))
	{
		{
		$cur_usr = new object($_SESSION["uid_oid"]);
		if (((time() - $_SESSION["current_user_last_m_check"]) > (5 * 60) /*|| true*/) && $cur_usr->prop("notify") == 1)
		{
			$drv_inst = get_instance("protocols/mail/imap");
			$drv_inst->set_opt("use_mailbox", "INBOX");

			$inst = new object($_SESSION["current_user_has_messenger"]);
			$conns = $inst->connections_from(array("type" => "RELTYPE_MAIL_SOURCE"));
			list(,$_sdat) = each($conns);
			$sdat = new object($_sdat->to());

			$drv_inst->connect_server(array("obj_inst" => $_sdat->to()));
			$emails = $drv_inst->get_folder_contents(array(
				"from" => 0,
				"to" => "*",
			));

			foreach($emails as $mail_id => $data)
			{
				if($data["seen"] == 0)
				{
					$new[] = $data["fromn"];
				}
			}
			$count = count($new);
			$new = join(", ", $new);
			if(strlen($new))
			{
				$sisu = sprintf(t("Sul on %s lugemata kirja! (saatjad: %s)"), $count, $new);
				$_SESSION["aw_session_track"]["aw"]["do_message"] = $sisu;
			}
			$_SESSION["current_user_last_m_check"] = time();
		}
		}
	}
	// end of that messenger new mail notifiaction crap


	global $awt;
	if (is_object($awt) && !empty($GLOBALS["cfg"]["debug"]["profile"]))
	{
		$sums = $awt->summaries();

		echo "<!--\n";
		while(list($k,$v) = each($sums))
		{
			print "$k = $v\n";
		};
		echo " querys = ".aw_global_get("qcount")." \n";
		if (function_exists("get_time"))
		{
			echo "total  = ".(get_time()-$GLOBALS["__START"])."\n";
			echo "proc  = ".($GLOBALS["__END_DISP"]-$GLOBALS["__START"])."\n";
			echo "print  = ".(get_time()-$GLOBALS["__END_DISP"])."\n";
		}
		echo "-->\n";
	}

	echo "<!--\n";
	//echo function_exists('memory_get_usage') ? ("memory_get_usage = " . memory_get_usage()." \n") : "";
	echo "enter_function calls = ".$GLOBALS["enter_function_calls"]." \n";
	echo "exit_function calls = ".$GLOBALS["exit_function_calls"]." \n";

	if (is_array($GLOBALS["profile_query_counts"]))
	{
		echo "query counts by function:\n";
		asort($GLOBALS["profile_query_counts"]);
		foreach($GLOBALS["profile_query_counts"] as $fn => $cnt)
		{
			echo "$fn => $cnt \n";
		}
	}

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
		$fname = "site.".$GLOBALS["cfg"]["ext"];
		$fname = aw_ini_get("site_basedir")."/public/".$fname;
		if (file_exists($fname))
		{
			include($fname);
		}
		else
		{
			$fname = aw_ini_get("site_basedir")."/htdocs/"."site.".$GLOBALS["cfg"]["ext"];
			if (file_exists($fname))
			{
				include($fname);
			}
		}
		if (class_exists("site", false))
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
	if (empty($GLOBALS["cfg"]["debug"]["profile"]))
	{
		return;
	}
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
	if (empty($GLOBALS["cfg"]["debug"]["profile"]))
	{
		return;
	}
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

function aw_set_exec_time($c_type)
{
	if ($c_type == AW_LONG_PROCESS)
	{
		set_time_limit( aw_ini_get("core.long_process_exec_time") );
	}
	if ($c_type == AW_SHORT_PROCESS)
	{
		set_time_limit( aw_ini_get("core.default_exec_time") );
	}
}

function __init_aw_session_track()
{
	if ($_SERVER["REQUEST_METHOD"] != "GET")
	{
		return;
	}
	if (!empty($_SESSION["aw_session_track"]["aw"]["do_redir"]))
	{
		$tmp = $_SESSION["aw_session_track"]["aw"]["do_redir"];
		$_SESSION["aw_session_track"]["aw"]["do_redir"] = "";
		header("Location: ".$tmp);
		die();
	}

	if (!empty($_SESSION["aw_session_track"]["aw"]["do_message"]))
	{
		$tmp = $_SESSION["aw_session_track"]["aw"]["do_message"];
		$_SESSION["aw_session_track"]["aw"]["do_message"] = "";
		echo "<script language=\"javascript\">alert(\"".$tmp."\");</script>";
	}

	// add session tracking options
	$_SESSION["aw_session_track"] = array(
		"server" => array(
			"ip" => isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null,
			"referer" => isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null,
			"ru" => $_SERVER["REQUEST_URI"],
			"site" => $_SERVER["HTTP_HOST"],
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

	$msg = "Suhtuge veateadetesse rahulikult!  Te ei ole korda saatnud midagi katastroofilist. Ilmselt juhib programm Teie t&auml;helepanu mingile ebat&auml;psusele  andmetes v&otilde;i n&auml;puveale.<Br><br>\n\n PHP error: errno = $errno , errstr = $errstr, errfile = $errfile, errline = $errline , context = $context\n<br>";

	// meilime veateate listi ka
	$subj = "Viga saidil ".$GLOBALS["cfg"]["baseurl"];
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
	$content.= "url = ".$GLOBALS["cfg"]["baseurl"].$GLOBALS["__aw_globals"]["REQUEST_URI"]."\n-----------------------\n";
	$content.= "is_rpc_call = $is_rpc_call\n";
	$content.= "rpc_call_type = $rpc_call_type\n";
	foreach($_COOKIE as $k => $v)
	{
		$content.="_COOKIE[$k] = $v \n";
	}
	foreach($_GET as $k => $v)
	{
		$content.="_GET[$k] = $v \n";
	}
	foreach($_POST as $k => $v)
	{
		$content.="_POST[$k] = $v \n";
	}
	foreach($_SERVER as $k => $v)
	{
		// we will not send out the password or the session key
		if ( ($k == "PHP_AUTH_PW") || ($k == "automatweb") )
		{
			continue;
		};
		$content.="_SERVER[$k] = $v \n";
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
	if (file_exists("/home/revalhotels/automatweb_dev/logger.aw"))
	{
		@include_once("/home/revalhotels/automatweb_dev/logger.aw");
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

function call_fatal_handler($str)
{
	if (function_exists($GLOBALS["fatal_error_handler"]))
	{
		$GLOBALS["fatal_error_handler"]($str);
	}
}

function incl_f($lib)
{
	return;
	static $f;
	if ($f[$lib] == 1)
	{
		return;
	}
	$f[$lib] = 1;
	echo "$lib ";
	echo shbt()." <Br>";
}

function shbt()
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

function check_pagecache_folders()
{
	// folders are:

	$flds = array(
		"menu_area_cache",			// done
		"storage_search",  			// done
		"storage_object_data",		// done
		"html",						// done
		"acl",						// done
	);

	$pg = aw_ini_get("cache.page_cache");
	foreach($flds as $f)
	{
		$fq = $pg."/".$f;
		if (!is_dir($fq))
		{
			@mkdir($fq, 0777);
			chmod($fq, 0777);
			for($i = 0; $i < 16; $i++)
			{
				$ffq = $fq ."/".($i < 10 ? $i : chr(ord('a') + ($i- 10)));
				@mkdir($ffq, 0777);
				chmod($ffq, 0777);
			}
		}
	}
	if (!is_dir($pg."/temp"))
	{
		@mkdir($pg."/temp", 0777);
		chmod($pg."/temp", 0777);
		touch($pg."/temp/lmod");
	}
}

function __autoload($class_name)
{
	enter_function("__autoload");
	require_once("class_index.aw");

	if ("class_index" === $class_name)
	{
		exit_function("__autoload");
		return;
	}

	try
	{
		$class_file = class_index::get_file_by_name($class_name);
		require_once($class_file);
	}
	catch (awex_clidx_double_dfn $e)
	{
		exit_function("__autoload");
		exit ("Class '" . $e->clidx_cl_name . "' redeclared. Fix error in '" . $e->clidx_path1 . "' or '" . $e->clidx_path2 . "'.");//!!! tmp

		//!!! take action -- delete/rename one of the classes or load both or ...
		// $class_file = class_index::get_file_by_name($class_name);
	}
	catch (awex_clidx $e)
	{
		try
		{
			class_index::update(true);
		}
		catch (awex_clidx $e)
		{
		}

		try
		{
			$class_file = class_index::get_file_by_name($class_name);
			require_once($class_file);
		}
		catch (awex_clidx $e)
		{
			if (basename($class_name) !== $class_name)
			{
				try
				{
					$tmp = $class_name;
					$class_name = basename($class_name);
					$class_file = class_index::get_file_by_name($class_name);
					echo "Invalid class name: '" . $tmp . "'. ";
					require_once($class_file);
				}
				catch (awex_clidx $e)
				{
					//!!! take action
				}
			}
			//!!! take action
		}
	}

	if (!class_exists($class_name, false) and !interface_exists($class_name, false))
	{ // class may be moved to another file, force update and try again
		try
		{
			class_index::update(true);
		}
		catch (awex_clidx $e)
		{
			exit_function("__autoload");
			echo ("Fatal update error. " . $e->getMessage() . " Tried to load '" . $class_name . "'");//!!! tmp
			echo dbg::process_backtrace(debug_backtrace());
			die();
			//!!! take action
		}

		try
		{
			$class_file = class_index::get_file_by_name($class_name);
			require_once($class_file);
		}
		catch (awex_clidx $e)
		{
			exit_function("__autoload");
			echo ("Fatal classload error. " . $e->getMessage() . " Tried to load '" . $class_name . "'");//!!! tmp
			echo dbg::process_backtrace(debug_backtrace());
		}
	}
	exit_function("__autoload");
}

function aw_exception_handler($e)
{
	try
	{
		error::raise(array(
			"id" => ERR_UNCAUGHT_EXCEPTION,
			"msg" => $e->getMessage(),
			"fatal" => true,
			"exception" => $e
		));
	}
	catch (Exception $ee)
	{
		echo "Couldn't load handler <br/><pre>" . $e->getTraceAsString() . "</pre>";
	}
}


interface request_startup
{
	/** This will get called in the beginning if the aw request and should initialize things that this class needs
		@attrib api=1
	**/
	function request_startup();
}
?>
