<?php

define("OBJ_COMP_LESS", 1);
define("OBJ_COMP_GREATER", 2);
define("OBJ_COMP_LESS_OR_EQ", 4);
define("OBJ_COMP_GREATER_OR_EQ", 8);
define("OBJ_COMP_BETWEEN", 16);
define("OBJ_COMP_EQUAL", 32);
define("OBJ_COMP_BETWEEN_INCLUDING", 64);
define("OBJ_COMP_NULL", 128);

class obj_predicate_compare
{
	/**
		@attrib api=1 params=pos
		@param comparator required type=int
		Comparator type.
		Available types:
		CONSTANT (integer_assigned to that constant) - action
		OBJ_COMP_LESS (1) - values less than $data
		OBJ_COMP_GREATER (2) - values greater than $data
		OBJ_COMP_LESS_OR_EQ (4) - values less or equal to $data
		OBJ_COMP_GREATER_OR_EQ (8) - values greater or equal to $data
		OBJ_COMP_BETWEEN (16) - values between £data and $data2
		OBJ_COMP_EQUAL (32) - values equal to $data
		OBJ_COMP_BETWEEN_INCLUDING (64) - values between and $data and $data2, including $data & $data2 themselves
		OBJ_COMP_NULL (128) - value NULL
		
		@param data required type=string
		data to compared
		@param data2 optional type=string
		data to compared

		@comment
		Used in object list filtering property values.
		@examples
		$filt = array(
			"class_id" => CL_BUG,
			"bug_status" => new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, 1, 6),
		);
		$ol = new object_list($filt);
		
		// generates list of bugs with statuses from 1 to 6 (inclucing 1 and 6)
	**/
	function obj_predicate_compare($comparator, $data, $data2 = NULL)
	{
		$this->comparator = $comparator;
		$this->data = $data;
		$this->data2 = $data2;
	}
}
?>
