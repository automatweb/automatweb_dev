<?php


class _config_dummy {};

function aw_ini_get($var)
{
//	enter_function("__global::aw_ini_get",array());
	$_config_instance =& __init_config_instance();

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
	return $_config_instance->data[$class][$var];
}

function &__init_config_instance()
{
	global $_config_instance;
	if (!is_object($_config_instance))
	{
		$_config_instance = new _config_dummy;
		$_config_instance->data = array();
	}
	return $_config_instance;
}

function parse_config($file)
{
	$_config_instance =& __init_config_instance();
	
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
					if (!is_array($_config_instance->data[$varclass][$arrname]))
					{
						$_config_instance->data[$varclass][$arrname] = array();
					}
					$code = "\$_config_instance->data[\$varclass][\$arrname]".$arrparams." = \"".$varvalue."\";";
//					echo "evaling $code <br>";
					eval($code);
				}
				else
				{
					// and stuff the thing in the array
					$_config_instance->data[$varclass][$varname] = $varvalue;
//					echo "setting [$varclass][$varname] to $varvalue <br>";
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

	$_config_instance =& __init_config_instance();

	if ($is_cached)
	{
//		list($micro,$sec) = split(" ",microtime());
//		$ts_s = $sec + $micro;
		$f = fopen($cache_file,"r");
		$fc = fread($f,filesize($cache_file));
		fclose($f);
		$_config_instance->data = unserialize($fc);
//		list($micro,$sec) = split(" ",microtime());
//		$ts_e = $sec + $micro;
//		echo "cache unserialize ",($ts_e - $ts_s), " seconds <br>";
//		result: 0.000791 - too small to measure correctly
	}
	else
	{
//		list($micro,$sec) = split(" ",microtime());
//		$ts_s = $sec + $micro;
		foreach($ini_files as $file)
		{
			parse_config($file);
		}
		// and write to cache if file is specified
		if ($cache_file)
		{
			$_ci =& __init_config_instance();
			$str = serialize($_ci->data);
			$f = fopen($cache_file,"w");
			fwrite($f,$str);
			fclose($f);
		}
//		list($micro,$sec) = split(" ",microtime());
//		$ts_e = $sec + $micro;
//		echo "ini parsing & cache writing took ",($ts_e - $ts_s), " seconds <br>";
//		result: 0.022812 - not exactly a showstopper, but still, glad to be rid of it
	}

	// siin ei saa veel aw_global_get'i kasutada, kuna defsi pole veel laetud
	global $PHP_SELF;
	if (strpos($PHP_SELF,"automatweb")) 
	{
		// keemia. Kui oleme saidi adminnis sees, siis votame templated siit
		$_config_instance->data["__default"]["tpldir"] = aw_ini_get("basedir") . "/templates";
	} 
	// kui saidi "sees", siis votame templated tolle saidi juurest, ehk siis ei puutu miskit

	// and here do the defs for classes
	foreach($_config_instance->data["__default"]["classes"] as $clid => $cld)
	{
		define($cld["def"], $clid);
	}

	// and here do the defs for programs
	foreach($_config_instance->data["__default"]["programs"] as $prid => $prd)
	{
		define($prd["def"], $prid);
	}

	classload("defs");
	
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
	$_config_instance =& __init_config_instance();
	$class = get_class($that);
	$that->cfg = array_merge($_config_instance->data[$class],$_config_instance->data["__default"]);
//	exit_function("__global::aw_config_init_class");
}


// loads localization constants
function lc_load($file)
{
//	enter_function("__global::lc_load",array());
	global $LC,$admin_lang_lc;
	if (!$admin_lang_lc)
	{
		$admin_lang_lc = "et";
	}
	@include_once(aw_ini_get("basedir")."/lang/" . $admin_lang_lc . "/$file.".aw_ini_get("ext"));
//	exit_function("__global::lc_load");
}

// loads localization constants from the site's $site_basedir
function lc_site_load($file,&$obj)
{
//	enter_function("__global::lc_site_load",array());
	$LC = aw_global_get("LC");
	$fname = aw_ini_get("site_basedir")."/lang/".$LC."/$file.".aw_ini_get("ext");
	global $DLC;
	if ($DLC)
	{
		print "fn = $fname<br>";
	};
	@include_once(aw_ini_get("site_basedir")."/lang/" . $LC . "/$file.".aw_ini_get("ext"));
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
		// votab stringist ainult selle osa, mis jääb /-i ja stringi lopu vahele
		// vältimaks katseid laadida faile /etc/passwd
		preg_match("/(\w*)$/",$lib,$m);
		$lib = $m[1];
		$lib = aw_ini_get("classdir")."/".$lib.".".aw_ini_get("ext");
		include_once($lib);
	};
//	exit_function("__global::classload");
}

function get_instance($class,$args = array())
{
	$classdir = aw_ini_get("classdir");
	$ext = aw_ini_get("ext");
	$id = sprintf("instance::%s",$class);
	$instance = aw_global_get($id);
	if (not($instance))
	{
		preg_match("/(\w*)$/",$class,$m);
		$lib = $m[1];
		$lib = "$classdir/$lib.$ext";
		include_once($lib);
		if (sizeof($args) > 0)
		{
			$instance = new $class($args);
		}
		else
		{
			$instance = new $class();
		};
		aw_global_set($id,$instance);
	};
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

function sysload($lib)
{
	classload($lib);
}

function load_vcl($lib)
{
	include_once(aw_ini_get("basedir")."/vcl/$lib.".aw_ini_get("ext"));
}


////
// !here we initialize the stuff that we couldn't initialize in parse_config, cause there are no services 
// available in parse_config, but now most of them are.
function aw_startup()
{
//	list($micro,$sec) = split(" ",microtime());
//	$ts_s = $sec + $micro;

	classload("languages");
	$l = new languages;
	$l->request_startup();

	classload("periods");
	$p = new periods;
	$p->request_startup();

	classload("users");
	$u = new users;
	$u->request_startup();

	$syslog = get_instance("syslog");
	$syslog->request_startup();

	classload("menuedit");
	$m = new menuedit();

	$m->request_startup();


//	list($micro,$sec) = split(" ",microtime());
//	$ts_e = $sec + $micro;
	//dbg1("aw_startup() took ".($ts_e - $ts_s)." seconds <br>");
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
	echo "-->\n";

	if ($GLOBALS["acl_server_socket"])
	{
		fclose($GLOBALS["acl_server_socket"]);
	}
}

function &__get_site_instance()
{
	global $__site_instance;
	if (!is_object($__site_instance))
	{
		include("site.".aw_ini_get("ext"));
		$__site_instance = new site;
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
?>
