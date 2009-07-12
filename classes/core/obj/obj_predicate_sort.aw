<?php

/*
@classinfo  maintainer=kristo
*/

class obj_predicate_sort
{
	private $arr = array();

	function obj_predicate_sort($data)
	{
		if (!is_array($data))
		{
			throw new awex_obj_type("Invalid argument type");
		}
		$count = array_count_values($data);
		$c1 = isset($count["asc"]) ? $count["asc"] : 0;
		$c2 = isset($count["ASC"]) ? $count["ASC"] : 0;
		$c3 = isset($count["desc"]) ? $count["desc"] : 0;
		$c4 = isset($count["DESC"]) ? $count["DESC"] : 0;
		if (($c1 + $c2 + $c3 + $c4) !== count($data))
		{
			throw new awex_obj("Argument contains invalid sorting direction instruction(s)");
		}
		$this->arr = $data;
	}

	function get_sorter_list()
	{
		$rv = array();
		foreach(safe_array($this->arr) as $prop => $direction)
		{
			$rv[] = array(
				"prop" => $prop,
				"direction" => $direction
			);
		}
		return $rv;
	}

	function __toString()
	{
		$s ="";
		foreach(safe_array($this->arr) as $prop => $direction)
		{
			$s .= $prop."=>".$direction;
		}
		return $s;
	}
}

?>
