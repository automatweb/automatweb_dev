<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/root.aw,v 2.23 2002/12/06 13:59:09 kristo Exp $
// root.aw - the root class
// this contains all the supplementary functions

classload("defs");
class root
{
	////
	// !the init function. all base classes that need to be initialized
	// must override this and call parent::init($args) before doing their own init
	function init()
	{
		$this->stacks = array();
	}

	////
	// !Pushes a variable onto the stack
	function _push($item,$stack = "root")
	{
		if (not(is_array($this->stacks[$stack])))
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

	////
	// !clears the stack $stack and returns all values in the stack as an array
	function _clear_stack($stack = "root")
	{
		$tmp = $this->stacks[$stack];
		$this->stacks[$stack] = array();
		return $tmp;
	}

	////
	// !this takes an array and goes through it and makes another array that has as keys the values of the given array and also
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
}
?>