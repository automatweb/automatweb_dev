<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/root.aw,v 2.14 2002/10/10 12:39:54 duke Exp $
// root.aw - the root class
// this contains all the supplementary functions

classload("defs");

// wrapper for arrays - helps to get rid of numerous is_array checks
// in code and reduces the amount of indenting
class aw_array
{
        function aw_array($arg)
        {
                $this->arg = (is_array($arg)) ? $arg : array();
        }

        function &get()
        {
                return $this->arg;
        }

	function next()
	{
		return each($this->arg);
	}

	function reset()
	{
		reset($this->arg);
	}
};

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
		if (is_array($this->stacks[$stack]))
		{
			return array_pop($this->stacks[$stack]);
		}
	}

	// right, I made a stupid mistake by assuming
	// that this function was no longer used, so I 
	// deleted it, so I found out, that it was still
	// needed. right, my bad, fuck me, BUT
	// fuck, why wasn't this in the cvs anyway?
	// I could have just restored it from there
	// now I have not even the faintest idea, what this
	// function is supposed to do? is this right?
	// or is something broken somewhere?
	//
	// this is used in form_db_base.aw for generating the sql join from the form relation tree 
	// and it seems correct to me - terryf
	function _clear_stack($stack = "root")
	{
		$tmp = $this->stacks[$stack];
		$this->stacks[$stack] = array();
		return $tmp;
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
