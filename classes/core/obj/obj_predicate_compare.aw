<?php

define("OBJ_COMP_LESS", 1);
define("OBJ_COMP_GREATER", 2);
define("OBJ_COMP_LESS_OR_EQ", 4);
define("OBJ_COMP_GREATER_OR_EQ", 8);

class obj_predicate_compare
{
	function obj_predicate_compare($comparator, $data)
	{
		$this->comparator = $comparator;
		$this->data = $data;
	}
}
?>