<?php


class _config_dummy {};

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
			$eqpos = strpos($line,"=");
			if ($eqpos !== false)
			{
				$var = trim(substr($line,0,$eqpos));
				$varvalue = trim(substr($line,$eqpos+1));
				
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
					$code = "\$_config_instance->data[\"$varclass\"][\"$arrname\"]".$arrparams." = \"".$varvalue."\";";
					eval($code);
				}
				else
				{
					// and stuff the thing in the array
					$_config_instance->data[$varclass][$varname] = $varvalue;
				}
			}
		}
	}
}

function init_config()
{
	$arg_list = func_get_args();
	while(list(,$file) = each($arg_list))
	{
		parse_config($file);
	}
}

function aw_ini_get($var)
{
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
	return $_config_instance->data[$class][$var];
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
	$_config_instance =& __init_config_instance();
	$class = get_class($that);
	$that->cfg = $_config_instance->data[$class];
}

?>