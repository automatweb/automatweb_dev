<?php
 
class room_obj extends _int_object
{
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

	function get_settings()
	{
		enter_function("room::get_settings_for_room");
		$si = get_instance(CL_ROOM_SETTINGS);
		$rv = $si->get_current_settings($this);
		exit_function("room::get_settings_for_room");
		return $rv;
	}

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

}

?>
