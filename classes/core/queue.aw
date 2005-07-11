<?php

class queue
{
	function queue()
	{
		$this->q = array();
		$this->items = 0;
	}

	function push($item)
	{
		$this->q[] = $item;
		$this->items++;
	}

	function get()
	{
		$this->items--;
		return array_shift($this->q);
	}

	function has_more()
	{
		return $this->items > 0;
	}

	function get_all()
	{
		return $this->q;
	}

	function count()
	{
		return $this->items;
	}

	function set_all($a)
	{
		$this->q = array_values(safe_array($a));
		$this->items = count($this->q);
	}

	function contains($val)
	{
		return in_array($val, $this->q);
	}
}

?>