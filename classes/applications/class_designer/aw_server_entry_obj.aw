<?php

class aw_server_entry_obj extends _int_object
{

	function name()
	{
		return $this->prop("name");
	}

	function set_name($p)
	{
		parent::set_name($p);
		return parent::set_prop("name", $p);
	}

	function set_prop($k, $v)
	{
		$rv = parent::set_prop($k, $v);
		if ($k == "name")
		{
			parent::set_name($v);
		}
		return $rv;
	}
}

?>
