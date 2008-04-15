<?php
/*
@classinfo  maintainer=markop
*/
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

	function get_sum()
	{
		if($this->prop("sum"))
		{
			$sum = $this->prop("sum");
		}
		else
		{
			$sum = str_replace(",", ".", $this->prop("amt")) * str_replace(",", ".", $this->prop("price"));
		}
		return $sum;
	}
}
?>
