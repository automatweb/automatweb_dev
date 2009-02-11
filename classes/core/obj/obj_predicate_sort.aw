<?php

class obj_predicate_sort
{
	private $arr;

	function obj_predicate_sort($data)
	{
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