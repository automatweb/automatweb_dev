<?php
/*
@classinfo  maintainer=voldemar
*/

require_once(AW_DIR . "classes/common/address/as_header.aw");

class address_object extends _int_object
{
	var $as_changed = false;
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

	function __construct ($param)
	{
		parent::__construct($param);
		$this->as_load_data();
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

	function prop_str ($param, $is_oid = null)
	{
		switch ($param)
		{
			case "address_array":
				return $this->as_get_string ();

			default:
				return parent::prop_str ($param, $is_oid);
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

	function save ($exclusive = false, $previous_state = null)
	{
		if ($this->as_save())
		{
			$this->as_changed = false;
			return parent::save($exclusive, $previous_state);
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
	private function as_get_array ()
	{
		### make address array
		$address_array = array ();

		if (!empty ($this->as_address_data))
		{
			$this->as_load_structure ();
			$this->as_load_country ();
			$address_array[ADDRESS_COUNTRY_TYPE] = $this->as_country->name ();

			foreach ($this->as_address_data as $division_id => $unit_data)
			{
				$address_array[$division_id] = $unit_data["name"];
			}
		}

		return $address_array;
	}

	private function as_get_string ()
	{
		$address_array = $this->as_get_array ();
		$street_address = $this->prop ("street_address");
		$apartment = $this->prop ("apartment");
		$po_box = $this->prop ("po_box");

		$address_str = implode (", ", $address_array);
		$address_str .= !empty ($street_address) ? " " . $street_address : "";
		$address_str .= !empty ($apartment) and !empty ($street_address) ? "-" . $apartment : "";
		$address_str .= !empty ($po_box) ? " " . t("Postkast") . " " . $po_box : "";

		return $address_str;
	}

    // @attrib name=as_get_id_array
	// @returns Associative array: administrative_division_oid => administrative_unit oid
	private function as_get_id_array ()
	{
		### make address array
		$address_array = array ();

		if (!empty ($this->as_address_data))
		{
			$this->as_load_structure ();
			$this->as_load_country ();
			$address_array[ADDRESS_COUNTRY_TYPE] = $this->as_country->id ();

			foreach ($this->as_address_data as $division_id => $unit_data)
			{
				$address_array[$division_id] = $unit_data["id"];
			}
		}

		return $address_array;
	}

    // @attrib name=as_set_by_id
	// @param id required
	// @param division optional
	// @comment Sets administrative unit corresponding to division of given id
	private function as_set_by_id ($arr)
	{
		### get&validate admin division
		if (is_object ($arr["division"]))
		{
			$division = $arr["division"];

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_id: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).AS_NEWLINE; }
				return false;
			}
		}
		elseif (is_oid ($arr["division"]))
		{
			$division = obj ($arr["division"]);

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_id: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).AS_NEWLINE; }
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
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_id: unit of wrong class [%s]. id: [%s]", $unit->class_id (), $arr["id"]).AS_NEWLINE; }
				return false;
			}
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_by_id: division object not found. id: [{$arr["id"]}]".AS_NEWLINE; }
			return false;
		}

		$this->as_load_structure ();

		### check if all specified unit is in the same admin structure as others
		$admin_structure_id = $this->as_administrative_structure->id ();

		if (is_object($division) and ($admin_structure_id != $division->prop ("administrative_structure")))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_id: division [%s] admin structure [%s] different from current [%s]. division var: [%s]", $division->id (), $division->prop ("administrative_structure"), $admin_structure_id, $division).AS_NEWLINE; }
			return false;
		}

		### ...
		$ord = is_object ($division) ? $division->ord() : $division;
		$division_id = is_object ($division) ? $division->id() : $division;
		$this->as_address_data[$division_id] = array (
			"ord" => $ord,
			"unit" => is_object ($unit) ? $unit : NULL,
			"id" => $arr["id"],
		);
		$this->as_changed = true;
	}

    // @attrib name=as_set_by_name
	// @param division required
	// @param name required
	// @comment Sets administrative unit corresponding to given division (admin division object, oid or ADDRESS_STREET_TYPE)
	private function as_set_by_name ($arr)
	{
		### validate name. validation needed here to give a chance to avoid corruptions in address structure -- spot errors before any changes made.
		if (empty ($arr["name"]))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::setbyname: name not defined for unit [%s]", var_export($arr["division"], true)) . AS_NEWLINE; }
			return false;
		}

		### get&validate admin division
		if (is_object ($arr["division"]))
		{
			$division = $arr["division"];

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_name: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).AS_NEWLINE; }
				return false;
			}
		}
		elseif (is_oid ($arr["division"]))
		{
			$division = obj ($arr["division"]);

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_name: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).AS_NEWLINE; }
				return false;
			}
		}
		elseif (ADDRESS_STREET_TYPE == (string) $arr["division"])
		{
			$division = ADDRESS_STREET_TYPE;
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_by_name: division object not defined. name: [{$arr["name"]}]".AS_NEWLINE; }
			return false;
		}

		### check if all specified unit is in the same admin structure as others
		$this->as_load_structure ();

		if (is_object ($division) and $this->as_administrative_structure->id () != $division->prop ("administrative_structure"))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::set_by_name: division [%s] admin structure [%s] different from current [%s]", $division->id (), $division->prop ("administrative_structure"), $this->as_administrative_structure->id ()).AS_NEWLINE; }
			return false;
		}

		### ...
		$ord = is_object ($division) ? $division->ord() : $division;
		$division_id = is_object ($division) ? $division->id() : $division;
		$this->as_address_data[$division_id] = array (
			"ord" => $ord,
			"name" => (string) $arr["name"]
		);
		$this->as_changed = true;
	}

    // @attrib name=as_get_by_id
	// @param id required
	// @returns administrative unit corresponding to given id
	private function as_get_by_id ($id)
	{
		if (is_oid ($id))
		{
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
	// @comment returns first administrative unit corresponding to given division
	private function as_get_by_division ($division)
	{
		### get&validate admin division
		if (is_object ($division))
		{
			$division_id = $division->id();
		}
		elseif (ADDRESS_STREET_TYPE === $division)
		{
			$division_id = ADDRESS_STREET_TYPE;
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_by_name: division object not defined. name: [{$arr["name"]}]".AS_NEWLINE; }
			return false;
		}

		return isset($this->as_address_data[$division_id]["name"]) ? $this->as_address_data[$division_id]["name"] : false;
	}

    // @attrib name=as_get_unit_encoded
	// @param division required
	// @param encoding required
	// @returns String encoded value for unit of $division.
	private function as_get_unit_encoded ($arr)
	{
		### get&validate admin division
		if (is_object ($arr["division"]))
		{
			$division = $arr["division"];

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_get_unit_encoded: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).AS_NEWLINE; }
				return false;
			}
		}
		elseif (is_oid ($arr["division"]))
		{
			$division = obj ($arr["division"]);

			if ($division->class_id () != CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_get_unit_encoded: division [%s] of wrong class [%s]", $division->id (), $division->class_id ()).AS_NEWLINE; }
				return false;
			}
		}
		elseif (ADDRESS_STREET_TYPE == (string) $arr["division"])
		{
			$division = ADDRESS_STREET_TYPE;
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::as_get_unit_encoded: division object not defined".AS_NEWLINE; }
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
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::as_get_unit_encoded: encoding object not defined".AS_NEWLINE; }
			return false;
		}

		if ($encoding->class_id () != CL_COUNTRY_ADMINISTRATIVE_STRUCTURE_ENCODING)
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_get_unit_encoded: encoding [%s] of wrong class [%s]", $encoding->id (), $encoding->class_id ()).AS_NEWLINE; }
			return false;
		}

		$division_id = is_object ($division) ? $division->id() : $division;
		$param = array (
			"prop" => "encoding_by_unit",
			"unit" => $this->as_address_data[$division_id]["id"],
		);
		$encoded_value = $encoding->prop ($param);
		return $encoded_value;
	}

    // @attrib name=as_get_envelope_label
	// @param this required
	// @returns String address for printing on envelopes etc.
	private function as_get_envelope_label ($arr)///!!!todo
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
	private function as_set_structure ($structure)
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
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_structure: administrative_structure not defined".AS_NEWLINE; }
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
	private function as_set_country ($country)
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
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_country: country not defined".AS_NEWLINE; }

			return false;
		}

		$this->as_administrative_structure = $this->as_country->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");

		if (!is_object ($this->as_administrative_structure))
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "address::set_country: country has no administrarive structure defined".AS_NEWLINE; }

			$this->as_country = NULL;
			return false;
		}

		return true;
	}

    // @attrib name=as_save
	// @returns boolean success
	// @comment Saves address location currently defined in this address object
	private function as_save ()
	{
		$this->as_load_structure();

		### process units
		$parent = $this->as_administrative_structure;
		$address_data = array();
		$topology = (array) $this->as_administrative_structure->meta("as_division_hierarchy_sequence");

		foreach ($topology as $division_id)
		{
			if (ADDRESS_STREET_TYPE === $division_id)
			{
				$division = $division_id;
			}
			elseif (is_oid($division_id))
			{
				$division = obj($division_id);
			}
			else
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("Fatal error. address::as_save: division not found. Given identifier: [%s].", $division_id) . AS_NEWLINE; }
				return false;
			}

			$unit_data = $this->as_address_data[$division_id];

			### set location on this level
			$unit_o = NULL;

			if (is_object ($unit_data["unit"]))
			{ ### specific unit given
				$unit_o = $unit_data["unit"];
			}
			elseif (is_oid ($unit_data["id"]))
			{ ### specific unit given by oid
				$unit_o = obj ($unit_data["id"]);
			}
			elseif (!empty ($unit_data["name"]))
			{ ### unit specified by name
				### add pending unit
				$args = array (
					"name" => $unit_data["name"],
					"division" => $division,
					"parent" => $parent,
					"calling_address_obj_oid" => $this->obj["oid"]
				);
				$unit_o = $this->as_administrative_structure->set_prop ("unit_by_name", $args);

				if ($unit_o === false)
				{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("Fatal error. address::as_save: adm_struct::set_prop(unit_by_name) failed. Unit name [%s]. Division [%s]", $unit_data["name"], var_export($division, true)) . AS_NEWLINE; }
					return false;
				}
			}

			if (isset ($unit_o))
			{
				$address_data[$division_id] = array (
					"name" => $unit_o->name (),
					"id" => $unit_o->id (),
					"ord" => is_object ($division) ? $division->ord() : $division,
					"class" => $unit_o->class_id (),
				);
				$parent = $unit_o;
			}
			else
			{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo sprintf ("address::as_save: unit not specified on this level. Division [%s].", var_export($division, true)) . AS_NEWLINE; }
			}
		}

		$this->as_address_data = $address_data;
		$this->set_prop ("administrative_structure", $this->as_administrative_structure);
		$this->set_prop ("country", $this->as_administrative_structure->prop ("country"));
		$this->set_prop ("address_data", $this->as_address_data);
		$this->set_parent ($parent->id ());
		return true;
	}

	private function as_load_structure ()
	{
		if (!is_object($this->as_administrative_structure))
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
	}

	private function as_load_country ()
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

	private function as_load_data ()
	{
		if (!$this->as_address_data_loaded)
		{
			$this->as_address_data = (array) $this->prop("address_data");
			$this->as_address_data_loaded = true;
			$this->as_changed = false;
		}
	}
}

?>
