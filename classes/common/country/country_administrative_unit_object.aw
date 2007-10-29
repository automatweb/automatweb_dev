<?php

require_once(aw_ini_get("basedir") . "/classes/common/address/as_header.aw");

class country_administrative_unit_object extends _int_object
{
	function save()
	{
		// find parent administrative structure
		$o = $this;

		do
		{
			$o = new object($o->parent());
		}
		while (CL_COUNTRY_ADMINISTRATIVE_STRUCTURE !== $o->class_id());

		$this->set_prop("administrative_structure", $o->id());

		// save this unit object
		$rv = parent::save();

		// add saved unit to adm str index
		$o->set_prop("unit_index", $this);
		$o->save();

		return $rv;
	}
}

?>
