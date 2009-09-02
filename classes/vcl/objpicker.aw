<?php

/*
@classinfo maintainer=voldemar
*/

class objpicker extends core implements vcl_interface
{
	function __construct()
	{
		$this->init("");
	}

	/**
		@attrib params=name api=1

		@param name required type=string
			String to indetify the object picker

		@param oid required type=int
			The object's ID the picker picks objects for

		@param property required type=string
			The property's name that picker picks objects for

		@param clid optional type=array
			Class id-s of objects to be picked from. Default is empty array, meaning any class object can be picked. If not specified, options must be defined or mode 'autocomplete'

		@param no_sel optional type=bool default=false

		@param no_edit optional type=bool default=false

		@param delete_button optional type=bool default=false

		@param options optional type=array default=array()
			Options to be displayed in the picker select box. Array(oid => caption).

		@param mode optional type=string default=''
			Values: 'autocomplete' Default is NULL

		@param buttonspos optional type=string default=''
			Position for buttons. Values: right, bottom. Default: right

		@returns string
			The HTML of the object picker.
	**/
	public function create($arr)
	{
	}

	function init_vcl_property($arr)
	{
		$prop = $arr["prop"];
		$name = $prop["name"];
		$o = new object($arr["obj_inst"]->prop($name));
		$prop["value"] = $o->name() . "\n" . html::hidden(array("name" => $name, "value" => $o->id()));
		return array($prop["name"] => $prop);
	}

	function process_vcl_property(&$arr)
	{
		// $prop =& $arr["prop"];
		// $name = $prop["name"];
		// $arr["obj_inst"]->set_prop($name, $timestamp);
	}
}

?>
