<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/root.aw,v 2.10 2002/05/08 20:33:14 duke Exp $
// root.aw - the root class
// this contains all the supplementary functions

classload("defs");

class root
{
	// siin asuvad mõned sagedamini kasutataivamad funktsioonid
	var $errorlevel;
	function root()
	{
		$this->errorlevel = 0;
		$this->stacks = array();
		lc_load("definition");
	}

	////
	// !Right now this is only a wrapper for the function with
	// the same name in defs.aw. This should probably be removed
	// at a later time
	function map($format,$array)
	{
		return map($format,$array);
	}

	////
	// !Right now this is only a wrapper for the function with
	// the same name in defs.aw. This should probably be removed
	// at a later time
	function map2($format,$array,$type = 0,$empty = false)
	{
		return map2($format,$array,$type,$empty);
	}

	////
	// !Right now this is only a wrapper for the function with
	// the same name in defs.aw. This should probably be removed
	// at a later time
	function gen_uniq_id($param = "")
	{
		return gen_uniq_id($param);
	}

	////
	// !Koostab URL-i parameetritest ning HTTP_GET_VARS väärtustest
	// TODO: viia defs.aw-sse
	function make_url($arr)
	{
		global $HTTP_GET_VARS;
		$ura = $HTTP_GET_VARS;
		reset($arr);
		while (list($k,$v) = each($arr))
		{
			$ura[$k] = $v;
		};
		$urs = join("&",$this->map2("%s=%s",$ura));
		return aw_global_get("PHP_SELF")."?".$urs;
	}

	////
	// !this takes an array and goes through it and makes another array that has as keyws the values of the given array and also
	// tha velues of the given array
	function make_keys($arr)
	{
		$ret = array();
		if (is_array($arr))
		{
			foreach($arr as $v)
			{
				$ret[$v] = $v;
			}
		}
		return $ret;
	}
};
?>
