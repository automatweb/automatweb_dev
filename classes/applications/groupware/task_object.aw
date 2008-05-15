<?php

// maintainer=markop 
class task_object extends _int_object
{
	function task_object()
	{
		parent::_int_object();
	}
      
	function name()
	{
		if ($this->_no_display)
		{
			return t("Isiklik");
		}
		return parent::name();
	}

	function comment()
	{
		if ($this->_no_display)
		{
			return t("Isiklik");
		}
		return parent::comment();
	}

	function prop($pn)
	{
		$show_props = array(
			"start1", "end", "deadline", "is_personal"
		);
		if (!$this->_no_display || in_array($pn, $show_props))
		{
			return parent::prop($pn);
		}

		if ($pn == "name")
		{
			return t("Isiklik");
		}
		return "";
	}

	function _init_override_object()
	{
		$this->_no_display = 0;
		if ($this->meta("is_personal"))
		{
			if (aw_global_get("uid") != $this->createdby())
			{
				$this->_no_display = 1;
			}
		}
	}

	function sum_guess()
	{
		$sum = 0;
		if($this->prop("num_hrs_guess"))
		{
			$sum = $this->get_hr_price()*$this->prop("num_hrs_guess");
		}
		return $sum;
	}

	function get_hr_price()
	{
		if ($this->prop("hr_price"))
		{
			return $this->prop("hr_price");
		}
		$conns = $this->connections_to(array());
		foreach($conns as $conn)
		{
			if($conn->prop('from.class_id')==CL_CRM_PERSON)
			{
				$pers = $conn->from();
				// get profession
				$rank = $pers->prop("rank");
				if (is_oid($rank) && $this->can("view", $rank))
				{
					$rank = obj($rank);
					if($rank->prop("hr_price"))
					{
						//salvestada, et m6nes teises vaates teisi arve ei n2itaks
						$this->set_prop("hr_price" , $rank->prop("hr_price"));
						$this->save();
						return $rank->prop("hr_price");
					}
				}
			}
		}
		return 0;
	}
}
?>