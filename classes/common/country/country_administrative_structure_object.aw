<?php

### address system settings
if (!defined ("ADDRESS_SYSTEM"))
{
	define ("ADDRESS_SYSTEM", 1);
	define ("NEWLINE", "<br />");
	define ("ADDRESS_STREET_TYPE", "street"); # used in many places. also in autocomplete javascript -- caution when changing.
	define ("ADDRESS_COUNTRY_TYPE", "country"); # used in many places. also in autocomplete javascript -- caution when changing.
	define ("ADDRESS_DBG_FLAG", "address_dbg");
}

class country_administrative_structure_object extends _int_object
{
	var $as_unit_classes = array (
		CL_COUNTRY_ADMINISTRATIVE_UNIT,
		CL_COUNTRY_CITY,
		CL_COUNTRY_CITYDISTRICT,
	);
	var $as_address_classes = array (
		CL_COUNTRY_ADMINISTRATIVE_UNIT,
		CL_COUNTRY_CITY,
		CL_COUNTRY_CITYDISTRICT,
		CL_ADDRESS_STREET,
	);

	function country_administrative_structure_object ($param)
	{
		parent::_int_object ($param);
	}

	function prop ($param)
	{
		if (is_array ($param))
		{
			$name = $param["prop"];

			switch ($name)
			{
				case "unit_by_name":
					return $this->as_get_unit_by_name ($param);

				case "units_by_division":
					return $this->as_get_units_by_division ($param);
			}
		}
		else
		{
			switch ($param)
			{
				case "structure_array":
					return $this->as_get_structure ();

				default:
					return parent::prop ($param);
			}
		}
	}

	function set_prop ($name, $param)
	{
		switch ($name)
		{
			case "unit_by_name":
				return $this->as_add_adminunit ($param);

			case "structure_array":
			case "units_by_division":
				return;

			default:
				return parent::set_prop ($name, $param);
		}
	}

    // @attrib name=as_get_structure
	// @returns
	function &as_get_structure ()
	{
		$divisions = array ();

		foreach ($this->connections_from (array ("type" => "RELTYPE_ADMINISTRATIVE_DIVISION")) as $connection)
		{
			$division = $connection->to ();
			$divisions[$division->ord ()] = $division;
		}

		ksort ($divisions);
		return $divisions;
	}

    // @attrib name=as_add_adminunit
	// @param name required
	// @param parent required
	// @param division required
	// @param return_object optional
	// @returns Created unit object/oid (depending on whether return_object is true or false). If existing unit with name was found that will be returned.
	// @comment division is object or oid of object from class CL_COUNTRY_ADMINISTRATIVE_DIVISION or ADDRESS_STREET_TYPE in case a street is to be added
	function as_add_adminunit ($arr)
	{
		### validate division object
		if (is_object ($arr["division"]))
		{
			$admin_division =& $arr["division"];
		}
		elseif (is_oid ($arr["division"]) and $this->can ("view", $arr["division"]))
		{
			$admin_division = obj ($arr["division"]);
		}

		### get subclass and class
		if (is_object ($admin_division))
		{
			if ($admin_division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("adminstructure::as_add_adminunit: adminunit division class wrong [%s]", $admin_division->class_id ()).NEWLINE; }
				return false;
			}

			$class_id = $admin_division->prop ("type");
			$subclass = $admin_division->id ();
		}
		elseif (ADDRESS_STREET_TYPE == (string) $arr["division"])
		{
			$class_id = CL_ADDRESS_STREET;
			$subclass = 0;
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructure::as_add_adminunit: division undefined [{$arr["division"]}]".NEWLINE; }
			return false;
		}

		### search for existing unit by name
		$arr["return_object"] = 1;
		$arr["type"] = $class_id;
		$o = $this->as_get_unit_by_name ($arr);

		if ($o === false)
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructure::as_add_adminunit: existing unit search fail".NEWLINE; }
			return false;
		}
		elseif (!is_object ($o))
		{ ### add new
			$parent = is_object ($arr["parent"]) ? $arr["parent"]->id () : $arr["parent"];
			$name = trim ($arr["name"]);

			if (is_oid ($parent))
			{
				$o =& new object ();
				$o->set_class_id ($class_id);
				$o->set_parent ($parent);
				$o->set_subclass ($subclass);
				$o->set_name ($name);
				$o->save ();
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructure::as_add_adminunit: added object [{$name}] under [{$parent}] with subclass [{$subclass}]".NEWLINE; }
			}
			else
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructure::as_add_adminunit: invalid parent [{$parent}]".NEWLINE; }
				return false;
			}
		}

		### ...
		if ($arr["return_object"])
		{
			return $o;
		}
		else
		{
			return $o->id ();
		}
	}

    // @attrib name=as_get_unit_by_name
	// @param name required
	// @param parent required
	// @param type required
	// @param return_object optional
	// @param calling_address_obj_oid optional for address system internal use
	// @returns Unit object/oid (depending on whether return_object is true or false) corresponding to name.
	function as_get_unit_by_name ($arr)
	{
		$name = trim ($arr["name"]);
		$parent = is_object ($arr["parent"]) ? $arr["parent"]->id () : $arr["parent"];
		$return_object = (boolean) $arr["return_object"];
		$class_id = (int) $arr["type"];

		if (empty ($name) or !in_array ($class_id, $this->as_address_classes))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructure::as_get_unit_by_name: name [{$name}] empty or type [{$class_id}] wrong".NEWLINE; }
			return false;
		}

		### switch user because anyone has to be able to see all addresses and delete duplicates
		$admin_user = $this->prop ("address_admin");

		if (empty ($admin_user))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructure::as_get_unit_by_name: admin user not defined for admin structure".NEWLINE; }
			return false;
		}

		aw_switch_user (array ("uid" => $admin_user));

		### search for existing unit
		$list = new object_list (array (
			"class_id" => $class_id,
			"parent" => $parent,
			"name" => array ($name),
			"site_id" => array (),
			"lang_id" => array (),
		));

		if ($list->count () == 1)
		{
			$o = $list->begin ();
		}
		elseif ($list->count () > 1)
		{ ### structure contains duplicates
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructure::as_get_unit_by_name: duplicates found for name [{$name}] under parent [{$parent}]".NEWLINE; }
			### move everything from under redundant admin units unto one, selected randomly (?)
			$o = $list->begin ();
			$list->remove ($o->id ());
			$redundant_unit = $list->begin ();

			### don't save currently saved address to avoid recursive address::save() call
			if (is_oid ($arr["calling_address_obj_oid"]))
			{
				$oid_constraint = new obj_predicate_not ($arr["calling_address_obj_oid"]);
			}
			else
			{
				$oid_constraint = NULL;
			}

			while (is_object ($redundant_unit))
			{
				$child_list = new object_list (array (
					"oid" => $oid_constraint,
					"parent" => $redundant_unit->id (),
					"site_id" => array (),
					"lang_id" => array (),
				));
				$child_list->set_parent ($o->id ());
				$child_list->save ();
				$redundant_unit = $list->next ();
			}

			### delete redundant admin units
			$list->delete ();
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructure::as_get_unit_by_name: no objects found for name [{$name}]".NEWLINE; }
		}

		### switch user back
		aw_restore_user ();

		### return found unit
		if (is_object ($o))
		{
			if ($return_object)
			{
				return $o;
			}
			else
			{
				return $o->id ();
			}
		}
	}

    // @attrib name=as_get_units_by_division
	// @param division required
	// @returns AW object list of admin units corresponding to $division
	function &as_get_units_by_division ($arr)
	{
		$division = $arr["division"];

		### validate division object
		if (is_object ($division))
		{
			$class = $division->prop ("type");
			$subclass = $division->id ();
		}
		elseif ($this->can ("view", $division))
		{
			$division = obj ($division);
			$class = $division->prop ("type");
			$subclass = $division->id ();
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructure::get_units_by_division: division not defined [{$division}]".NEWLINE; }
			return false;
		}

		### get units
		$args = array (
			"class_id" => $class,
			"subclass" => $subclass,
			"site_id" => array(),
			"lang_id" => array(),
		);
		$list = new object_list ($args);

		return $list;
	}
}

?>
