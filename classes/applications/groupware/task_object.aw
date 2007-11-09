<?php

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
}
?>