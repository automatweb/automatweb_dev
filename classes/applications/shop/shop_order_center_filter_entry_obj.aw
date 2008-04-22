<?php

class shop_order_center_filter_entry_obj extends _int_object
{
	function filter_get_selected_values($field_name)
	{
		$cache = $this->meta("filter_value_cache");
		$v = $cache[$field_name];
		$rv = array();
		foreach($v as $val => $val_capt)
		{
			$rv[$val] = $val_capt;
		}
		return $rv;
	}

	function filter_set_selected_values($field_name, $field_values)
	{
		$cache = $this->meta("filter_value_cache");
		$cache[$field_name] = $field_values;
		$this->set_meta("filter_value_cache", $cache);
	}
}

?>
