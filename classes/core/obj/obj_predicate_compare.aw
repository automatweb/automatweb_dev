<?php

define("OBJ_COMP_LESS", 1);
define("OBJ_COMP_GREATER", 2);
define("OBJ_COMP_LESS_OR_EQ", 4);
define("OBJ_COMP_GREATER_OR_EQ", 8);
define("OBJ_COMP_BETWEEN", 16);
define("OBJ_COMP_EQUAL", 32);
define("OBJ_COMP_BETWEEN_INCLUDING", 64);

class obj_predicate_compare
{
	function obj_predicate_compare($comparator, $data, $data2 = NULL)
	{
		$this->comparator = $comparator;
		$this->data = $data;
		$this->data2 = $data2;
	}
}
?>