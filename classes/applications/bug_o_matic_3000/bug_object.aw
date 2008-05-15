<?php
/*
@classinfo  maintainer=robert
*/

class bug_object extends _int_object
{
	function save()
	{
		// before saving, set default props if they are not set yet
		if (!is_oid($this->id()))
		{
			$this->_set_default_bug_props();
		}
		return parent::save();
	}

	function _set_default_bug_props()
	{
		if (!$this->prop("orderer"))
		{
			$c = get_current_company();
			if($c)
			{
				$this->set_prop("orderer", $c->id());
			}
		}
		if (!$this->prop("orderer_unt"))
		{
			$p = get_current_person();
			if($p)
			{
				$sets = $p->prop("org_section");
			}
			if (is_array($sets))
			{
				$sets = reset($sets);
			}
			$this->set_prop("orderer_unit", $sets);
		}
		if (!$this->prop("orderer_person"))
		{
			$p = get_current_person();
			if($p)
			{
				$this->set_prop("orderer_person", $p->id());
			}
		}
	}

	function sum_guess()
	{
		$sum = 0;
		if($this->prop("num_hrs_guess"))
		{
			$sum = $this->prop("num_hrs_guess") * $this->prop("skill_used.hour_price");
		}
		return $sum;
	}
}
