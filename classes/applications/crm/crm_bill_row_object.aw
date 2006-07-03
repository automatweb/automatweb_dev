<?php

class crm_bill_row_object extends _int_object
{
	function set_name($name)
	{
		$rv = parent::set_name($name);
		$this->set_prop("desc", $name);
		return $rv;
	}

	function set_prop($p, $v)
	{
		$rv = parent::set_prop($p, $v);
		if ($p == "name")
		{
			$this->set_prop("desc", $v);
		}
		return $rv;
	}
}

?>