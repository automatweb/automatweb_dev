<?php

if (!defined("AW_DIR"))
{
	exit;
}

// load libraries
// required for startup
require_once AW_DIR . "lib/errorhandling" . AW_FILE_EXT;
require_once AW_DIR . "lib/config" . AW_FILE_EXT;
require_once AW_DIR . "classes/core/util/class_index" . AW_FILE_EXT;

// other. later perhaps implement conditional loading.
require_once AW_DIR . "lib/core/obj/object" . AW_FILE_EXT;
require_once AW_DIR . "lib/debug" . AW_FILE_EXT;

function __autoload($class_name)
{
	try
	{
		$class_file = class_index::get_file_by_name($class_name);
		require_once $class_file;
	}
	catch (awex_clidx_double_dfn $e)
	{
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
			require_once $class_file;
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
					require_once $class_file;
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
			exit("Fatal update error. " . $e->getMessage() . " Tried to load '" . $class_name . "'");//!!! tmp
			//!!! take action
		}

		try
		{
			$class_file = class_index::get_file_by_name($class_name);
			require_once $class_file;
		}
		catch (awex_clidx $e)
		{
			exit("Fatal classload error. " . $e->getMessage() . " Tried to load '" . $class_name . "'");//!!! tmp
		}
	}
}

function get_include_contents($filename)
{
	if (is_file($filename))
	{
		ob_start();
		include $filename;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
}

function array_union_recursive($array1 = array(), $array2 = array())
{
	if (!is_array($array1) or !is_array($array1))
	{
		throw new aw_exception("Invalid argument type.");
	}

	$array = $array1 + $array2;

	foreach ($array1 as $key => $value)
	{
		if (is_array($value) and isset($array2[$key]) and is_array($array2[$key]))
		{
			$array[$key] = array_union_recursive($value, $array2[$key]);
		}
	}

	return $array;
}

?>