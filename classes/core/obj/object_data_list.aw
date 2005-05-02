<?php

class object_data_list
{
	function object_data_list($param, $props)
	{
		if (!is_array($param))
		{
			error::raise(array(
				"id" => "ERR_PARAM",
				"msg" => t("object_data_list::object_data_list($param): parameter must be array!")
			));
		}

		$this->_int_load($param, $props);
	}

	function arr()
	{
		return $this->list;
	}

	////////// private

	function _int_load($arr, $props)
	{
		$this->_int_init_empty();
		$this->list = $GLOBALS["object_loader"]->ds->search($arr, $props);
	}

	function _int_init_empty()
	{
		$this->list = array();
	}
}
?>