<?php
if (!defined("AW_CONST_INC"))
{


define("AW_CONST_INC", 1);
// 1:42 PM 8/3/2008 - const.aw now contains only parts of old startup script that are to be moved to new appropriate files or deleted. const.aw file to be removed eventually.

//UnWasted - set_magic_quotes_runtime is deprecated since php 5.3
if (version_compare(PHP_VERSION, '5.3.0', '<'))
set_magic_quotes_runtime(0);

foreach ($GLOBALS["cfg"] as $key => $value)
{
	if (!is_array($value))
	{
		$GLOBALS["cfg__default__short"][$key] = $value;
	}
}

function get_time()
{
	list($micro,$sec) = explode(" ",microtime());
	return ((float)$sec + (float)$micro);
}
define ("AW_SHORT_PROCESS", 1);
define ("AW_LONG_PROCESS", 2);
$section = null;

ini_set("memory_limit", "900M");
if (get_magic_quotes_gpc() && !defined("GPC_HANDLER"))
{
	function stripslashes_deep($value)
	{
		$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
		return $value;
	}

	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
	define("GPC_HANDLER", 1);
}

$pi = "";

$PATH_INFO = isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : null;
$QUERY_STRING = isset($_SERVER["QUERY_STRING"]) ? $_SERVER["QUERY_STRING"] : null;
$REQUEST_URI = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : null;

$PATH_INFO = isset($PATH_INFO) ? preg_replace("|\?automatweb=[^&]*|","", $PATH_INFO) : "";
$QUERY_STRING = isset($QUERY_STRING) ? preg_replace("|\?automatweb=[^&]*|","", $QUERY_STRING) : "";

if (($QUERY_STRING == "" && $PATH_INFO == "") && $REQUEST_URI != "")
{
        $QUERY_STRING = $REQUEST_URI;
        $QUERY_STRING = str_replace("xmlrpc.aw", "", str_replace("index.aw", "", str_replace("orb.aw", "", str_replace("login.aw", "", str_replace("reforb.aw", "", $QUERY_STRING)))));
}

if (strlen($PATH_INFO) > 1)
{
	$pi = $PATH_INFO;
}

if (strlen($QUERY_STRING) > 1)
{
	$pi .= "?".$QUERY_STRING;
}

$pi = trim($pi);

if (substr($pi, 0, strlen("/class=image")) == "/class=image")
{
	$pi = substr(str_replace("/", "&", str_replace("?", "&", $pi)), 1);
	parse_str($pi, $_GET);
	extract($_GET);
}

if (substr($pi, 0, strlen("/class=file")) == "/class=file")
{
	$pi = substr(str_replace("/", "&", str_replace("?", "&", $pi)), 1);
	parse_str($pi, $_GET);
	extract($_GET);
}
elseif (substr($pi, 0, strlen("/class=flv_file")) == "/class=flv_file")
{
	$pi = substr(str_replace("/", "&", str_replace("?", "&", $pi)), 1);
	parse_str($pi, $_GET);
	extract($_GET);
}
else
{
	$_SERVER["REQUEST_URI"] = isset($_SERVER['REQUEST_URI']) ? preg_replace("|\?automatweb=[^&]*|","", $_SERVER["REQUEST_URI"]) : "";
	$pi = preg_replace("|\?automatweb=[^&]*|ims", "", $pi);
	if ($pi)
	{
		if (($_pos = strpos($pi, "section=")) === false)
		{
			// ok, we need to check if section is followed by = then it is not really the section but
			// for instance index.aw/set_lang_id=1
			// we check for that like this:
			// if there are no / or ? chars before = then we don't prepend

			$qpos = strpos($pi, "?");
			$slpos = strpos($pi, "/");
			$eqpos = strpos($pi, "=");
			$qpos = $qpos ? $qpos : 20000000;
			$slpos = $slpos ? $slpos : 20000000;

			if (!$eqpos || ($eqpos > $qpos || $slpos > $qpos))
			{
				// if no section is in url, we assume that it is the first part of the url and so prepend section = to it
				$pi = str_replace("?", "&", "section=".substr($pi, 1));
			}
		}

		// support for links like http://bla/index.aw?291?lcb=117 ?424242?view=3&date=20
		// this is a quick fix for a specific problem on june 22th 2010 with opera.ee site
		// might have been a configuration error, for increase of tolerance in that case then
		if (preg_match("/^\\?([0-9]+)\\?/", $pi, $section_info))
		{
			$section = $section_info[1];
		}

		if (($_pos = strpos($pi, "section=")) !== false)
		{
			// this here adds support for links like http://bla/index.aw/section=291/lcb=117
			$t_pi = substr($pi, $_pos+strlen("section="));
			if (($_eqp = strpos($t_pi, "="))!== false)
			{
				$t_pi = substr($t_pi, 0, $_eqp);
				$_tpos1 = strpos($t_pi, "?");
				$_tpos2 = strpos($t_pi, "&");
				if ($_tpos1 !== false || $_tpos2 !== false)
				{
					// if the thing contains ? or & , then section is the part before it
					if ($_tpos1 === false)
					{
						$_tpos = $_tpos2;
					}
					else
					if ($_tpos2 === false)
					{
						$_tpos = $_tpos1;
					}
					else
					{
						$_tpos = min($_tpos1, $_tpos2);
					}
					$section = substr($t_pi, 0, $_tpos);
				}
				else
				{
					// if not, then te section is the part upto the last /
					$_lslp = strrpos($t_pi, "/");
					if ($_lslp !== false)
					{
						$section = substr($t_pi, 0, $_lslp);
					}
					else
					{
						$section = $t_pi;
					}
				}
			}
			else
			{
				$section = $t_pi;
			}
		}
	}
}

$GLOBALS["section"] = $section;

$ext = "aw";  // filename extension

if (empty($LC))
{
	$LC="et";
}

// stat function fields
define("FILE_SIZE",7);
define("FILE_MODIFIED",9);

// please use $row[OID] instead of row["oid"] everywhere you can,
// because "oid" is a reserved word in postgres (and probably others)
// and we really-really want to port AW to other databases ASAP
define("OID","oid");

// CL_PSEUDO is deprecated, please use CL_MENU instead
// it's defined here to provide a safe migration path
// define("CL_PSEUDO",1); // nov 3 2008 -- cl_pseudo not found in any .aw files. removing completely later.

// mix 69? well mulle meeldib see number :-P
define("MN_CLIENT",69);
// sisurubriik
define("MN_CONTENT",70);
// adminni ylemine menyy
define("MN_ADMIN1",71);
// promo kast
define("MN_PROMO_BOX",73);
// kodukataloog
define("MN_HOME_FOLDER",74);
// kodukataloogi alla tehtud kataloog, et sharetud katalooge olex lihtsam n2idata
define("MN_HOME_FOLDER_SUB",75);
// formi element, mis on samas ka menyy
define("MN_FORM_ELEMENT",76);
// public method
define("MN_PMETHOD",77);

// formide tyybid
define("FTYPE_ENTRY",1);
define("FTYPE_SEARCH",2);
define("FTYPE_FILTER_SEARCH",4);
define("FTYPE_CONFIG",5);

// formide alamtyybid
// subtype voiks bitmask olla tegelikult
define("FSUBTYPE_JOIN",1);

// kas seda vormi saab kasutada eventite sisestamiseks
// mingisse kalendrisse?
define("FSUBTYPE_EV_ENTRY",2);

// kas seda vormi saab kasutada vormi baasil e-maili
// actionite tegemiseks?
define("FSUBTYPE_EMAIL_ACTION",4);

// kas seda vormi kasutatakse kalendri ajavahemike defineerimiseks?
define("FSUBTYPE_CAL_CONF",8);

// kui see on otsinguvorm, siis kas otsingutulemusi filtreeritakse
// l2bi kalendri?
define("FSUBTYPE_CAL_SEARCH",16);

// like CAL_CONF, but data is entered directly
define("FSUBTYPE_CAL_CONF2",32);

// sum of all form & calendar settings, used to figure out
// whether a form has any relation to a calendar
define("FORM_USES_CALENDAR",58);

// object flags - bitmask
define("OBJ_FLAGS_ALL", (1 << 30)-1);	// this has all the flags checked, so you can build masks, by negating this

define("OBJ_HAS_CALENDAR",1 << 0);
// this will be set for objects that need to be translated
define("OBJ_NEEDS_TRANSLATION",1 << 1);
// this will be set for objects whose translation has been checked/confirmed
define("OBJ_IS_TRANSLATED",1 << 2);
// this will be used for objects with calendar functionality
define("OBJ_IS_DONE",1 << 3);
// if you need to select an active object from a bunch of objects, then this flag marks the active object
define("OBJ_FLAG_IS_SELECTED", 1 << 4);
// this says that the object is part of the auto-object translation. in addition to this it can have the NEEDS_TRANSLATION ot IS_TRANSLATED
define("OBJ_HAS_TRANSLATION", 1 << 5);
// this says that the object used to be a calendar vacancy
define("OBJ_WAS_VACANCY", 1 << 6);

// objektide subclassid - objects.subclass sees juusimiseks

// for CL_BROTHER_DOCUMENT
define("SC_BROTHER_DOC_KEYWORD", 1);	// kui dokumendi vend on tehtud t2nu menuu keywordile

// always-defined reltypes
define("RELTYPE_BROTHER", 10000);
define("RELTYPE_ACL", 10001);

//Date formats
define("LC_DATE_FORMAT_SHORT", 1); // For example: 20.06.88 or 05.12.98
define("LC_DATE_FORMAT_SHORT_FULLYEAR", 2); // For example: 20.06.1999 or 05.12.1998
define("LC_DATE_FORMAT_LONG", 3); // For example: 20. juuni 99
define("LC_DATE_FORMAT_LONG_FULLYEAR", 4); // For example: 20. juuni 1999

// project statuses
define("PROJ_IN_PROGRESS", 1);
define("PROJ_DONE", 2);

function ifset(&$item_orig)
{
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
			return null;
		}
	}
	return $item;
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
		if (is_readable($trans_fn))
		{
			require_once($trans_fn);

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
				if (!empty($ad["def"]) && ($_tmp = t2("syslog.action.".$ad["def"])) != "")
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

function aw_config_init_class($that)
{
//	enter_function("__global::aw_config_init_class",array());
	$class = get_class($that);
	$that->cfg = array_merge((isset($GLOBALS["cfg"][$class]) ? $GLOBALS["cfg"][$class] : array()),$GLOBALS["cfg__default__short"]);
	$that->cfg["acl"] = $GLOBALS["cfg"]["acl"];
	$that->cfg["config"] = $GLOBALS["cfg"]["config"];
//	exit_function("__global::aw_config_init_class");
}

// loads localization variables from the site's $site_basedir
function lc_site_load($file, $obj)
{
	if (aw_ini_get("user_interface.full_content_trans") === "1")
	{
		$LC = aw_global_get("ct_lang_lc");
	}
	else
	{
		$LC = aw_global_get("LC");
	}

	if (empty($LC))
	{
		$LC = "et";
	}

	$fname = aw_ini_get("site_basedir")."/lang/{$LC}/{$file}".AW_FILE_EXT;
	if (is_readable($fname))
	{
		include_once($fname);
	}

	if ($obj instanceof aw_template)
	{
		// kui objekt anti kaasa, siis loeme tema template sisse muutuja $lc_$file
		$var = "lc_".$file;
		global $$var;
		if (is_array($$var))
		{
			$obj->vars($$var);
		}
	}
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
		if (is_readable($lib))
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
		$default_lib = $lib = $olib = str_replace(".","", $lib);

		//klassile pakihalduse teemalise versiooni
		//$lib muutuja on paketihalduse versiooni nimega, $default_lib ilma - default v22rtuseks
		if(function_exists("get_class_version"))
		{
			$lib = get_class_version($lib);
		}

		try
		{
			$cl_id = aw_ini_get("class_lut.".basename($lib));
		}
		catch (Exception $e)
		{
		}

		if (isset($cl_id) and isset($GLOBALS["cfg"]["classes"][$cl_id]["site_class"]) and $GLOBALS["cfg"]["classes"][$cl_id]["site_class"] == 1)
		{
			$lib = $GLOBALS["cfg"]["site_basedir"]."/classes/".basename($lib).".".$GLOBALS["cfg"]["ext"];
		}
		elseif (substr($lib,0,13) === "designedclass")
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
				if (is_readable($trans_fn))
				{
					require_once($trans_fn);
				}
				else
				{
					$trans_fn = $GLOBALS["cfg"]["basedir"]."/lang/trans/$adm_ui_lc/aw/".basename($default_lib);
					if (is_readable($trans_fn))
					{
						require_once($trans_fn);
					}
				}
			}
		}
		if (is_readable($lib))
		{
			include_once($lib);
		}
		else
		{
			if (empty($olib))
			{
				throw new aw_exception("Can't load class when no name given.");
			}

			// try to handle it with class_index and autoload
			__autoload(basename($olib));
		}
	}
//	exit_function("__global::classload");
}

function get_instance($class, $args = array(), $errors = true)
{
	if (empty($class))
	{
		throw new aw_exception("Can't load class when no name given.");
	}

	if (!empty($GLOBALS["TRACE_INSTANCE"]))
	{
		echo "get_instance $class from ".dbg::short_backtrace()." <br>";
	}
	enter_function("__global::get_instance",array());

	$site = $designed = false;
	if (is_numeric($class))
	{
		if (!aw_ini_isset("classes.{$class}"))
		{
			$designed = true;
		}
		else
		{
			$class = aw_ini_get("classes.{$class}.file");
		}
	}

	try
	{
		$cl_id = aw_ini_get("class_lut.".basename($class));
		$site = aw_ini_isset("classes." . $cl_id . ".site_class");
	}
	catch (Exception $e)
	{
		$site = false;
	}

	if (substr($class,0,13) === "designedclass")
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
			include_once($proxy_file);
			return new $proxy_class($rs);
		}
	}

	if ($site)
	{
		$classdir = aw_ini_get("site_basedir")."/classes";
	}
	else if ($designed)
	{
		$classdir = aw_ini_get("site_basedir")."/files/classes";
		$class = basename($class);
		$lib = $GLOBALS["gen_class_name"];
//echo "dir = $classdir class = $class , lib = $lib <br>";
	}
	else
	{
		$classdir = aw_ini_get("classdir");
	}

	$replaced = str_replace(".","", $class);
	//klassile pakihalduse teemalise versiooni

//echo "dir = $classdir class = $class , lib = $lib <br>";

	if (!file_exists($classdir."/".$replaced.AW_FILE_EXT))
	{
		__autoload(basename($class));
	}

	if(function_exists("get_class_version"))
	{
		$replaced = get_class_version($replaced);
	}

	$_fn = $classdir."/".$replaced.AW_FILE_EXT;

	if (is_readable($_fn) && !class_exists($lib))
	{
		require_once($_fn);
	}

	// also load translations
	if (isset($GLOBALS["cfg"]["user_interface"]["default_language"]) && ($adm_ui_lc = $GLOBALS["cfg"]["user_interface"]["default_language"]) != "")
	{
		$trans_fn = $GLOBALS["cfg"]["basedir"]."/lang/trans/$adm_ui_lc/aw/".basename($class).AW_FILE_EXT;

		if (is_readable($trans_fn))
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
		}
	}
	else
	{
		$instance = false;
	}

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
	$trans_fn = AW_DIR."lang/trans/{$adm_ui_lc}/aw/".basename($class).AW_FILE_EXT;
	if (is_readable($trans_fn))
	{
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
		$trans_fn = AW_DIR."lang/trans/{$adm_ui_lc}/aw/".basename($lib).AW_FILE_EXT;
		if (is_readable($trans_fn))
		{
			require_once($trans_fn);
		}
	}

	$fn = AW_DIR."classes/vcl/{$lib}".AW_FILE_EXT;
	if (is_readable($fn))
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

	classload("core/error", "core/obj/object");
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

	aw_global_set("aw_init_done", 1);

	$m = get_instance("menuedit");
	$m->request_startup();
	__init_aw_session_track();
}

////
// !called just before the very end
function aw_shutdown()
{
	// whotta fook, this messenger thingie goes here then?:S

	/*$i = get_instance("file");
	if(isset($_SESSION["current_user_has_messenger"]) and $i->can("view", $_SESSION["current_user_has_messenger"]) and $i->can("view", $_SESSION["uid_oid"]))
	{
		$cur_usr = new object($_SESSION["uid_oid"]);
		if (((time() - $_SESSION["current_user_last_m_check"]) > (5 * 60)) && $cur_usr->prop("notify") == 1)
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
	// end of that messenger new mail notifiaction crap
*/

	global $awt;
	if (is_object($awt) && !empty($GLOBALS["cfg"]["debug"]["profile"]))
	{
		$sums = $awt->summaries();

		echo "<!--\n";
		while(list($k,$v) = each($sums))
		{
			print "$k = $v\n";
		}
		$end = microtime(true);
		echo " querys = ".aw_global_get("qcount")." \n";
		echo "total  = ".($end - $GLOBALS["__START"])."\n";
		echo "proc  = ".($GLOBALS["__END_DISP"]-$GLOBALS["__START"])."\n";
		echo "print  = ".($end - $GLOBALS["__END_DISP"])."\n";
		echo "-->\n";
	}

	echo "<!--\n";
	//echo function_exists('memory_get_usage') ? ("memory_get_usage = " . memory_get_usage()." \n") : "";
	echo "enter_function calls = ".(empty($GLOBALS["enter_function_calls"]) ? "0" : $GLOBALS["enter_function_calls"])." \n";
	echo "exit_function calls = ".(empty($GLOBALS["exit_function_calls"]) ? "0" : $GLOBALS["exit_function_calls"])." \n";

	if (!empty($GLOBALS["profile_query_counts"]) and is_array($GLOBALS["profile_query_counts"]))
	{
		echo "query counts by function:\n";
		asort($GLOBALS["profile_query_counts"]);
		foreach($GLOBALS["profile_query_counts"] as $fn => $cnt)
		{
			echo "$fn => $cnt \n";
		}
	}

	echo "-->\n";
}

function &__get_site_instance()
{
	global $__site_instance;
	if (!is_object($__site_instance))
	{
		$fname = aw_ini_get("site_basedir")."/public/site".AW_FILE_EXT;
		if (is_readable($fname))
		{
			include($fname);
		}
		else
		{
			$fname = aw_ini_get("site_basedir")."/htdocs/site".AW_FILE_EXT;
			if (is_readable($fname))
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

function __aw_error_handler($errno, $errstr, $errfile, $errline,  $context) // Looks abandoned and used nowhere. To be deprecated?
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
	if (is_readable("/home/revalhotels/automatweb_dev/logger.aw"))
	{
		include_once("/home/revalhotels/automatweb_dev/logger.aw");
	}
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
		"acl"							// done
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


}
?>
