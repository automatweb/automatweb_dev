<?php

class obj_predicate_limit
{
	private $from;
	private $per_page;

	function obj_predicate_limit($from, $per_page = 0)
	{
		$this->from = $from;
		$this->per_page = $per_page;
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