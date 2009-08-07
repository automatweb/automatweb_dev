<?php

class cfgform_obj extends _int_object
{
	/**
		@attrib params=pos
		@param property required type=string/array
			Property/properties to be disabled
	**/
	public function disable_property($properties)
	{
		$disabled_properties = safe_array($this->meta("disabled_properties"));
		foreach((array)$properties as $property)
		{
			$disabled_properties[$property] = true;
		}
		$this->set_prop("disabled_properties", $disabled_properties);
	}

	/**
		@attrib params=pos
		@param property required type=string/array
			Property/properties to be enabled
	**/
	public function enable_property($properties)
	{
		$disabled_properties = safe_array($this->meta("disabled_properties"));
		foreach((array)$properties as $property)
		{
			if(isset($disabled_properties[$property]))
			{
				unset($disabled_properties[$property]);
			}
		}
		$this->set_prop("disabled_properties", $disabled_properties);
	}
}

?>