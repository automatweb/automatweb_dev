<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/root.aw,v 2.6 2001/07/27 23:24:16 duke Exp $
// root.aw - the root class
// actually I'm not sure this class is needed at all
// this contains all the supplementary functions

classload("defs");

class root
{
	// siin asuvad mõned sagedamini kasutataivamad funktsioonid
	var $errorlevel;
	// siia me salvestame erinevad stackid
	var $stacks = array("root" => array("subcount" => 0));
	function root()
	{
		$this->errorlevel = 0;
		$this->stacks = array();
		lc_load("definition");
	}

	//  siit algavad pinu funktsioonid
	//-----------------------------------------------------
	//	kood voimaldab kasutada mitut pinu,
	//	kui pinufunktsioonile pinu ID-d ette ei anta
	//	siis, kasutatakse meelevaldselt välja mõeldud nime
	//			'root'

	////
	// !Pushes a variable onto the stock
	function _push($item,$stack = "root")
	{
		if ( not(is_array($this->stacks[$stack])) )
		{
			$this->stacks[$stack] = array();
		};

		array_push($this->stacks[$stack],$item);
	}

	////
	// !Pops a variable from the stack
	function _pop($stack = "root")
	{
		return array_pop($this->stacks[$stack]);
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
	function map2($format,$array,$type = 0)
	{
		return map2($format,$array,$type);
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
		global $HTTP_GET_VARS,$PHP_SELF;
		$ura = $HTTP_GET_VARS;
		reset($arr);
		while (list($k,$v) = each($arr))
		{
			$ura[$k] = $v;
		};
		$urs = join("&",$this->map2("%s=%s",$ura));
		return $PHP_SELF."?".$urs;
	}
};
?>
