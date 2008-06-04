<?php

class shop_order_center_obj extends _int_object
{
	function filter_get_fields()
	{
		$class_filter_fields = $this->meta("class_filter_fields");
		$prop_filter_fields = $this->meta("prop_filter_fields");

		$rv = array();
		$ic_fields = $this->get_integration_class_instance()->get_filterable_fields();
		foreach(safe_array($class_filter_fields) as $field_name => $one)
		{
			if ($one == 1)
			{
				$rv["ic::".$field_name] = $ic_fields[$field_name];
			}
		}

		$prod_props = obj()->set_class_id(CL_SHOP_PRODUCT)->get_property_list();
		foreach(safe_array($prop_filter_fields) as $field_name => $one)
		{
			if ($one == 1)
			{
				$rv["prod::".$field_name] = $prod_props[$field_name]["caption"];
			}
		}
		return $rv;
	}
	
	function filter_get_all_values($filter_name)
	{
		list($type, $field_name) = explode("::",$filter_name);

		if ($type == "ic")
		{
			$inst = $this->get_integration_class_instance();
			return $inst->get_all_filter_values($field_name);
		}
		else
		if ($type == "prod")
		{
			$rv = array();

			$odl = new object_data_list(
				array(
					"lang_id" => array(),
					"site_id" => array(),
					"class_id" => CL_SHOP_PRODUCT
				),
				array(
					CL_SHOP_PRODUCT => array(new obj_sql_func(OBJ_SQL_UNIQUE, "value", $field_name))
				)
			);
			foreach($odl->arr() as $od)
			{
				$rv[$od["value"]] = $od["value"];
			}
			return $rv;
		}

		return array();
	}

	function get_integration_class_instance()
	{
		if (!is_class_id($ic = $this->prop("integration_class")))
		{
			return null;
		}

		$clss = aw_ini_get("classes");
		return get_instance($clss[$ic]["file"]);
	}

	function filter_set_active_by_folder($data)
	{
		$this->set_meta("filter_by_folder", $data);
	}

	function filter_get_active_by_folder($folder_id)
	{
		$fbf = safe_array($this->meta("filter_by_folder"));
		if (is_oid($fbf[$folder_id]) && $GLOBALS["object_loader"]->can("view", $fbf[$folder_id]))
		{
			return $fbf[$folder_id];
		}
		foreach(obj($folder_id)->path(array("full_path" => 1)) as $path_item)
		{
			if (is_oid($fbf[$path_item->id()]) && $GLOBALS["object_loader"]->can("view", $fbf[$path_item->id()]))
			{
				return $fbf[$path_item->id()];
			}
		}
		return null;
	}
}