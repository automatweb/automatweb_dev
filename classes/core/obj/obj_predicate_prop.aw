<?php

class obj_predicate_prop
{
	var $prop; // the property that this object references

	function obj_predicate_prop($p1, $p2 = NULL)
	{
		if ($p2 !== NULL)
		{
			$this->prop = $p2;
			$this->compare = $p1;
		}
		else
		{
			$this->prop = $p1;
			$this->compare = OBJ_COMP_EQUAL;
		}
	}
}
?>