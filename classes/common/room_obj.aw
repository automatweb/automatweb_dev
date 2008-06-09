<?php
 
class room_obj extends _int_object
{
	/** Returns the color for the given setting, based on the current settings
		@attrib api=1 params=pos

		@param var required type=string
			The setting to return the value for. 

	**/
	function get_color($var)
	{
		$default = null;
		switch($var)
		{
			case "available":
				$default = "#E1E1E1";
			default:
				if($color = $this->get_setting("col_".$var))
				{
					return "#".$color;
				}
				else
				{
					return $default;
				}
		}
	}

	/** Returns the current active settings for the room
		@attrib api=1 

		@returns
			The cl_room_settings object active for the current user or null if none found
	**/
	function get_settings()
	{
		enter_function("room::get_settings_for_room");
		$si = get_instance(CL_ROOM_SETTINGS);
		$rv = $si->get_current_settings($this);
		exit_function("room::get_settings_for_room");
		return $rv;
	}

	/** Returns a setting from the current active room settings
		@attrib api=1 params=pos

		@param setting required type=string
			A setting property name from the room_settings class

		@returns
			The value for the setting in the currently active settings or "" if no settings are active
	**/
	function get_setting($setting)
	{
		if(!is_object($this->settings))
		{
			$this->settings = $this->get_settings();
		}
		if(!is_object($this->settings))
		{
			return "";
		}
		if(!$this->settings->is_property($setting))
		{
			return "";
		}
		return $this->settings->prop($setting);
	}

	/** Returns the current workers for the room
		@attrib api=1 
		@returns
			array(person id => person name)
	**/
	function get_all_workers()
	{
		$pro = array();
		if(is_array($this->prop("professions")))
		{
			$pro = $this->prop("professions");
		}

		$ol2 = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"lang_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_RANK" => $pro,
					"oid" => $pro,
				)
			)),
		));
		return $ol2->names();
	}

	/** Returns the current sellers for the room
		@attrib api=1 
		@returns
			array(person id => person name)
	**/
	function get_all_sellers()
	{
		$pro = array();
		if(is_array($this->prop("seller_professions")))
		{
			$pro = $this->prop("seller_professions");
		}

		$ol2 = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"lang_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_RANK" => $pro,
					"oid" => $pro,
				)
			)),
		));
		return $ol2->names();
	}
}

?>
