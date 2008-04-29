<?php

class obj_predicate_limit
{
	private $from;
	private $per_page;

	function obj_predicate_limit($limit, $offset = 0)
	{
		$this->from = $offset;
		$this->per_page = $limit;
	}

	function get_from()
	{
		return (int)$this->from;
	}

	function get_per_page()
	{
		return (int)$this->per_page;
	}
}