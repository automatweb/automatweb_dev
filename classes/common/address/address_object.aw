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

class address_object extends _int_object
{
	var $as_address_data = array ();
	var $as_address_data_loaded = false;
	var $as_administrative_structure;
	var $as_country;
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

	function address_object ($param)
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
				case "unit_by_id":
					return $this->as_get_by_id ($param);

				case "unit_name":
					return $this->as_get_by_division ($param);

				case "unit_encoded":
					return $this->as_get_unit_encoded ($param);
			}
		}
		else
		{
			switch ($param)
			{
				case "address_array":
					return $this->as_get_array ();

				case "address_ids":
					return $this->as_get_id_array ();

				case "administrative_structure":
					$this->as_load_structure ();
					return $this->as_administrative_structure;

				case "country":
					$this->as_load_country ();
					return $this->as_country;

				default:
					return parent::prop ($param);
			}
		}
	}

	function set_prop ($name, $param)
	{
		switch ($name)
		{
			case "unit_by_id":
				return $this->as_set_by_id ($param);

			case "unit_name":
				return $this->as_set_by_name ($param);

			case "administrative_structure":
				return $this->as_set_structure ($param);

			case "country":
				return $this->as_set_country ($param);

			case "unit_encoded":
			case "address_array":
			case "address_ids":
				return;

			default:
				return parent::set_prop ($name, $param);
		}
	}

	function save ()
	{
		$status = $this->as_save ();

		if ($status)
		{
			return parent::save ();
		}
		else
		{
			error::raise(array(
				"msg" => sprintf(t("address::save(): object (%s) couldn't be saved."), $this->obj["oid"])
			));
		}
	}

    // @attrib name=as_get_array
	// @returns Associative array: administrative_division_oid => administrative_unit name
	function as_get_array ()
	{
		$this->as_load_data ();

		### make address array
		$address_array = array ();

		if (!empty ($this->as_address_data))
		{
			$this->as_load_structure ();
			$this->as_load_country ();
			$address_array[ADDRESS_COUNTRY_TYPE] = $this->as_country->name ();

			foreach ($this->as_address_data as $unit_data)
			{
				$address_array[$unit_data["division"]] = $unit_data["name"];
			}
		}

		return $address_array;
	}

    // @attrib name=as_get_id_array
	// @returns Associative array: administrative_division_oid => administrative_unit oid
	function as_get_id_array ()
	{
		$this->as_load_data ();

		### make address array
		$address_array = array ();

		if (!empty ($this->as_address_data))
		{
			$this->as_load_structure ();
			$this->as_load_country ();
			$address_array[ADDRESS_COUNTRY_TYPE] = $this->as_country->id ();

			foreach ($this->as_address_data as $unit_data)
			{
				$address_array[$unit_data["division"]] = $unit_data["id"];
			}
		}

		return $address_array;
	}

    // @attrib name=as_set_by_id
	// @param id required
	// @param division optional
	// @comment Sets administrative unit corresponding to division of given id
	function as_set_by_id ($arr)
	{
		### get&validate admin division
		if (is_object ($arr["division"]))
		{
			$division = $arr["division"];

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_id: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).NEWLINE; }
				return false;
			}
		}
		elseif (is_oid ($arr["division"]))
		{
			$division = obj ($arr["division"]);

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_id: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).NEWLINE; }
				return false;
			}
		}
		elseif (ADDRESS_STREET_TYPE == (string) $arr["division"])
		{
			$division = ADDRESS_STREET_TYPE;
		}
		elseif (is_oid ($arr["id"]))
		{
			$unit = obj ($arr["id"]);

			if ($unit->class_id () == CL_ADDRESS_STREET)
			{
				$division = ADDRESS_STREET_TYPE;
			}
			elseif (in_array ($unit->class_id (), $this->as_unit_classes))
			{
				$division = obj ($unit->subclass ());
			}
			else
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_id: unit of wrong class [%s]. id: [%s]", $unit->class_id (), $arr["id"]).NEWLINE; }
				return false;
			}
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_by_id: division object not found. id: [{$arr["id"]}]".NEWLINE; }
			return false;
		}

		$this->as_load_structure ();

		### check if all specified unit is in the same admin structure as others
		if ($this->as_administrative_structure->id () != $division->prop ("administrative_structure"))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_id: division [%s] admin structure [%s] different from current [%s]", $division->id (), $division->prop ("administrative_structure"), $this->as_administrative_structure->id ()).NEWLINE; }
			return false;
		}

		### ...
		$key = is_object ($division) ? $division->ord () : $division;
		$this->as_address_data[$key] = array (
			"division" => $division,
			"unit" => is_object ($unit) ? $unit : NULL,
			"id" => $arr["id"],
		);
	}

    // @attrib name=as_set_by_name
	// @param division required
	// @param name required
	// @comment Sets administrative unit corresponding to given division (admin division object, oid or ADDRESS_STREET_TYPE)
	function as_set_by_name ($arr)
	{
		### get&validate admin division
		if (is_object ($arr["division"]))
		{
			$division = $arr["division"];

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_name: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).NEWLINE; }
				return false;
			}
		}
		elseif (is_oid ($arr["division"]))
		{
			$division = obj ($arr["division"]);

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_name: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).NEWLINE; }
				return false;
			}
		}
		elseif (ADDRESS_STREET_TYPE == (string) $arr["division"])
		{
			$division = ADDRESS_STREET_TYPE;
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_by_name: division object not defined. name: [{$arr["name"]}]".NEWLINE; }
			return false;
		}

		### check if all specified unit is in the same admin structure as others
		$this->as_load_structure ();

		if (is_object ($division) and $this->as_administrative_structure->id () != $division->prop ("administrative_structure"))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_name: division [%s] admin structure [%s] different from current [%s]", $division->id (), $division->prop ("administrative_structure"), $this->as_administrative_structure->id ()).NEWLINE; }
			return false;
		}

		### validate name. validation needed here to give a chance to avoid corruptions in address structure -- spot errors before any changes made.
		if (empty ($arr["name"]))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::setbyname: name not defined for unit [%s]", $division->id ()) . NEWLINE; }
			return false;
		}

		### ...
		$key = is_object ($division) ? $division->ord () : $division;
		$this->as_address_data[$key] = array (
			"division" => $division,
			"name" => (string) $arr["name"],
		);
	}

    // @attrib name=as_get_by_id
	// @param id required
	// @returns administrative unit corresponding to given id
	function as_get_by_id ($id)
	{
		if (is_oid ($id))
		{
			$this->as_load_data ();

			foreach ($this->as_address_data as $unit_data)
			{
				if ($unit_data["id"] == (int) $id)
				{
					if (is_object ($unit_data["unit"]))
					{
						return $unit_data["unit"];
					}
					else
					{
						return obj ($unit_data["id"]);
					}
				}
			}
		}

		return false;
	}

    // @attrib name=as_get_by_division
	// @param name required
	// @comment returns first administrative unit corresponding to given name
	function as_get_by_division ($division)
	{
		### get&validate admin division
		if (is_object ($division))
		{
			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_name: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).NEWLINE; }
				return false;
			}
		}
		elseif (is_oid ($division))
		{
			$division = obj ($division);

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_name: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).NEWLINE; }
				return false;
			}
		}
		elseif (ADDRESS_STREET_TYPE == (string) $division)
		{
			$division = ADDRESS_STREET_TYPE;
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_by_name: division object not defined. name: [{$arr["name"]}]".NEWLINE; }
			return false;
		}

		$this->as_load_data ();

		foreach ($this->as_address_data as $unit_data)
		{
			if ($unit_data["id"] == $division->id ())
			{
				return $unit_data["name"];
			}
		}
	}

    // @attrib name=as_get_unit_encoded
	// @param division required
	// @param encoding required
	// @returns String encoded value for unit of $division.
	function as_get_unit_encoded ($arr)
	{
		### get&validate admin division
		if (is_object ($arr["division"]))
		{
			$division = $arr["division"];

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_get_unit_encoded: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).NEWLINE; }
				return false;
			}
		}
		elseif (is_oid ($arr["division"]))
		{
			$division = obj ($arr["division"]);

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_get_unit_encoded: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).NEWLINE; }
				return false;
			}
		}
		elseif (ADDRESS_STREET_TYPE == (string) $arr["division"])
		{
			$division = ADDRESS_STREET_TYPE;
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::as_get_unit_encoded: division object not defined".NEWLINE; }
			return false;
		}

		### get&validate encoding
		if (is_object ($arr["encoding"]))
		{
			$encoding = $arr["encoding"];
		}
		elseif (is_oid ($arr["encoding"]))
		{
			$encoding = obj ($arr["encoding"]);
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::as_get_unit_encoded: encoding object not defined".NEWLINE; }
			return false;
		}

		if ($encoding->class_id () != CL_COUNTRY_ADMINISTRATIVE_STRUCTURE_ENCODING)
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_get_unit_encoded: encoding [%s] of wrong class [%s]", $encoding->id (), $encoding->class_id ()).NEWLINE; }
			return false;
		}

		$this->as_load_data ();
		$key = is_object ($division) ? $division->ord () : $division;
		$param = array (
			"prop" => "encoding_by_unit",
			"unit" => $this->as_address_data[$key]["id"],
		);
		$encoded_value = $encoding->prop ($param);
		return $encoded_value;
	}

    // @attrib name=as_get_envelope_label
	// @param this required
	// @returns String address for printing on envelopes etc.
	function as_get_envelope_label ($arr)///!!!todo
	{
			//!!! use address array from property if adm str etc not changed, otherwise build array from current values
			//!!! teha nii et viimane rida oleks: T2nav majanr-krtnr
			//!!! ymbrikuaadressi formaati peaks saama riigi v. haldusjaotuse juurest dfn-da
		$address_text = array_reverse ($this->prop ("address_array"));
		$address_text = implode (", ", $address_text);
		return implode ("\n", $address_text);
	}

    // @attrib name=as_set_structure
	// @param structure required
	// @returns boolean success
	function as_set_structure ($structure)
	{
		if (is_oid ($structure))
		{
			$this->as_administrative_structure = obj ($structure);
		}
		elseif (is_object ($structure))
		{
			$this->as_administrative_structure = $structure;
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_structure: administrative_structure not defined".NEWLINE; }
			return false;
		}

		$this->connect (array (
			"to" => $this->as_administrative_structure,
			"reltype" => "RELTYPE_ADMINISTRATIVE_STRUCTURE",
		));
		parent::set_prop ("administrative_structure", $this->as_administrative_structure->id ());
		return true;
	}

    // @attrib name=as_set_country
	// @param country required
	// @returns boolean success
	function as_set_country ($country)
	{
		if (is_oid ($country))
		{
			$this->as_country = obj ($country);
		}
		elseif (is_object ($country))
		{
			$this->as_country = $country;
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_country: country not defined".NEWLINE; }

			return false;
		}

		$this->as_administrative_structure = $this->as_country->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");

		if (!is_object ($this->as_administrative_structure))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_country: country has no administrarive structure defined".NEWLINE; }

			$this->as_country = NULL;
			return false;
		}

		return true;
	}

    // @attrib name=as_save
	// @returns boolean success
	// @comment Saves address location currently defined in this address object
	function as_save ()
	{
		$this->as_load_structure ();

		### process units
		ksort ($this->as_address_data, SORT_STRING);
		$parent = $this->as_administrative_structure;

		foreach ($this->as_address_data as $ord => $unit_data)
		{
			### check if unit goes under right place in address structure
			if ((string) $unit_data["division"] == ADDRESS_STREET_TYPE)
			{ ### last position. name should be for street
				$division = ADDRESS_STREET_TYPE;
				$division_of_parent = $parent_division = 0;
			}
			elseif ($parent->class_id () == CL_COUNTRY_ADMINISTRATIVE_STRUCTURE)
			{ ### first position.
				$division = $unit_data["division"];
				$division_of_parent = $parent_division = 0;
			}
			elseif (in_array ($parent->class_id (), $this->as_unit_classes))
			{
				$division = $unit_data["division"];
				$division_of_parent = $parent->subclass ();
				$parent_division = $division->prop ("parent_division");
			}
			else
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_save: parent of wrong class [%s]", $parent->class_id ()) . NEWLINE; }
				return false;
			}

			if ($division_of_parent != $parent_division)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_save: division of parent [%s] wrong. doesn't match parent division [%s]", $division_of_parent, $parent_division) . NEWLINE; }
				// return false;
				continue; // more fault tolerance. Sets units in parental relation to the one encountered first. Units of wrong parental relation ignored.
			}

			### set location on this level
			$new_parent = NULL;

			if (is_object ($unit_data["unit"]))
			{ ### specific unit given
				$new_parent = $unit_data["unit"];
			}
			elseif (is_oid ($unit_data["id"]))
			{ ### specific unit given by oid
				$new_parent = obj ($unit_data["id"]);
			}
			elseif (!empty ($unit_data["name"]))
			{ ### unit specified by name
				### add unit
				$new_parent = $this->as_administrative_structure->set_prop ("unit_by_name", array (
					"name" => $unit_data["name"],
					"parent" => $parent,
					"division" => $division,
					"return_object" => true,
				));

				if ($new_parent === false)
				{
					return false;
				}
			}

			if (isset ($new_parent))
			{
				$this->as_address_data[$ord] = array (
					"name" => $new_parent->name (),
					"id" => $new_parent->id (),
					"division" => is_object ($division) ? $division->id () : $division,
					"class" => $new_parent->class_id (),
				);
				$parent = $new_parent;
				$this->connect (array (
					"to" => $new_parent,
					"reltype" => "RELTYPE_ADMINISTRATIVE_UNIT",
				));
			}
			else
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_save: unit not specified on this level [%s].", $division) . NEWLINE; }
			}
		}

		$this->set_prop ("administrative_structure", $this->as_administrative_structure);
		$this->set_prop ("country", $this->as_administrative_structure->prop ("country"));
		$this->set_prop ("address_data", $this->as_address_data);
		$this->set_parent ($parent->id ());
		return true;
	}

	function as_load_structure ()
	{
		$administrative_structure = parent::prop ("administrative_structure");

		if (is_oid ($administrative_structure))
		{
			$this->as_administrative_structure = obj ($administrative_structure);
		}
		else
		{
			error::raise(array(
				"msg" => sprintf(t("address::as_load_structure(): administrative_structure not defined for object (%s)!"), $this->obj["oid"])
			));
		}
	}

	function as_load_country ()
	{
		if (!is_object ($this->as_country))
		{
			$this->as_country = $this->as_administrative_structure->get_first_obj_by_reltype("RELTYPE_COUNTRY");

			if (!is_object ($this->as_country))
			{
				error::raise(array(
					"msg" => sprintf(t("address::as_load_country(): country not defined for administrative structure of object (%s)!"), $this->obj["oid"])
				));
			}
		}
	}

	function as_load_data ()
	{
		if (!$this->as_address_data_loaded)
		{
			$this->as_address_data = $this->prop ("address_data");
			$this->as_address_data_loaded = true;
		}
	}
}

?>
