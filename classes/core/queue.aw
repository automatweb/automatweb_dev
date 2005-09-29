<?php

class queue
{
	function queue()
	{
		$this->q = array();
		$this->index = array();
		$this->items = 0;
	}

	function push($item)
	{
		$this->q[] = $item;
		$this->index[$item] = 1;
		$this->items++;
	}

	function get()
	{
		$this->items--;
		$ret = array_shift($this->q);
		unset($this->index[$ret]);
		return $ret;
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
		$this->index = array_flip($this->q);
		$this->items = count($this->q);
	}

	function contains($val)
	{
		return isset($this->idx[$val]);
	}
}

?>
