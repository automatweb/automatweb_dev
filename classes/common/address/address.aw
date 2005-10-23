<?php
// $Header: /home/cvs/automatweb_dev/classes/common/address/address.aw,v 1.1 2005/10/23 17:18:02 voldemar Exp $
// address.aw - Aadress v2
/*

@classinfo syslog_type=ST_ADDRESS relationmgr=yes no_comment=1 no_status=1

@groupinfo grp_address caption="Aadress"


@default table=objects
@default field=meta
@default method=serialize
@default group=general
	@property country type=relpicker reltype=RELTYPE_COUNTRY clid=CL_COUNTRY automatic=1
	@caption Riik

	@property address_array type=hidden
	@property administrative_structure_oid type=hidden

@default group=grp_address
	@property location_country type=hidden store=no
	@property location type=callback callback=callback_location no_caption=1 store=no

	@property postal_code type=textbox
	@caption Postiindeks

	@property street_address type=textbox
	@caption Maja number

	@property apartment type=textbox
	@caption Korter/Tuba

	@property po_box type=textbox
	@caption Postkast


// --------------- RELATION TYPES ---------------------

@reltype COUNTRY value=1 clid=CL_COUNTRY
@caption Riik

*/

### address system settings


class address extends class_base
{
	function address()
	{
		$this->init(array(
			"tpldir" => "common/address",
			"clid" => CL_ADDRESS
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch ($prop["group"])
		{
			case "grp_address":
				### check that country is defined
				if (!$this_object->get_first_obj_by_reltype ("RELTYPE_COUNTRY"))
				{
					$retval = PROP_FATAL_ERROR;
					$prop["error"] = t("Riik valimata");
				}
				break;
		}

		switch($prop["name"])
		{
			case "location_country":
				$prop["value"] = $this_object->prop ("country");
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch($prop["name"])
		{
			case "location":
				break;
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_pre_save ($arr)
	{
		$this_object =& $arr["obj_inst"];

		if (!$arr["new"] and array_key_exists ("location_street", $arr["request"]))
		{
			if ($country = $this_object->get_first_obj_by_reltype("RELTYPE_COUNTRY"))
			{ ### get administrative structure
				if ($this->can ("view", $this_object->prop ("administrative_structure_oid")))
				{
					$administrative_structure = obj ($this_object->prop ("administrative_structure_oid"));
				}
				else
				{
					$administrative_structure = $country->get_first_obj_by_reltype("RELTYPE_ADMINISTRATIVE_STRUCTURE");
				}

				if (!is_object ($administrative_structure))
				{
					//!!! throw user err
					echo t("Haldusjaotuse struktuur riigi jaoks defineerimata.");
				}

				$cl_administrative_structure = get_instance (CL_COUNTRY_ADMINISTRATIVE_STRUCTURE);
				$unit_types =& $cl_administrative_structure->get_structure (array(
					"id" => $administrative_structure->id(),
				));
				$parents[$country->id ()] = $country;

				### Add street to the end of unit_types array. Relieves from extra processing of street data.
				$unit_types[] = "street";

				### if no units were defined by user, address will have country for its parent (error shown if so)
				$saved_unit = $country;

				foreach ($unit_types as $key => $unit_type)
				{
					if (is_object ($unit_type))
					{
						$selected_unit_id = $arr["request"]["location_" . $unit_type->id ()];
					}
					else
					{
						$selected_unit_id = $arr["request"]["location_" . $unit_type];
					}

					if ($unit_type == "street")
					{ ### lowest defined unit in structure hierarchy, save street as address' parent
						if (is_oid ($selected_unit_id))
						{
							$saved_unit = obj ($selected_unit_id);
						}
						elseif (strlen ($selected_unit_id) > 1)
						{
							### check if object exists with exact same name
							$parent = $saved_unit;
							$list = new object_list (array (
								"class_id" => CL_ADDRESS_STREET,
								"parent" => $parent->id (),
								"name" => "%" . $selected_unit_id,
							));
							$existing_unit = NULL;

							// $existing = $this->db_fetch_field("
								// SELECT
									// count(objects.oid) AS count
								// FROM
									// objects
								// WHERE
									// objects.parent = " . $parent->id () . " AND
									// objects.class_id = " . CL_ADDRESS_STREET . " AND
									// objects.name LIKE '" . $selected_unit_id . "' AND
									// objects.status > 0
							// ", "count");

							if (1 == $list->count ())
							{
								$existing_unit = $list->begin();
							}
							else
							{
								$list = $list->arr ();

								foreach ($list as $unit)
								{
									if (strtolower ($unit->name ()) == strtolower ($selected_unit_id))
									{
										$existing_unit = $list->begin();
									}
								}
							}

							if (is_object ($existing_unit))
							{
								$saved_unit = $existing_unit;
							}
							else
							{ ### save new street
								$saved_unit = obj ();
								$saved_unit->set_class_id (CL_ADDRESS_STREET);
								$saved_unit->set_parent ($parent->id ());
								$saved_unit->set_name ($selected_unit_id);
								$saved_unit->save ();
							}
						}

						break;
					}

					if (is_oid ($selected_unit_id))
					{
						$saved_unit = obj ($selected_unit_id);
					}
					elseif (strlen ($selected_unit_id) > 1)
					{
						if (!array_key_exists ($unit_type->prop ("parent_unit"), $parents))
						{
							echo t("Kõrgem haldusüksus määramata.");
							break;
						}

						$parent = $parents[$unit_type->prop ("parent_unit")];

						### check if object exists with exactly same name
						$list = new object_list (array (
							"class_id" => $unit_type->prop ("unit_type"),
							"parent" => $parent->id (),
							"name" => "%" . $selected_unit_id,
							"subclass" => $unit_type->id (),
						));
						$existing_unit = NULL;

						// $existing = $this->db_fetch_field("
							// SELECT
								// count(objects.oid) AS count
							// FROM
								// objects
							// WHERE
								// objects.parent = " . $parent->id () . " AND
								// objects.class_id = " . $unit_type->prop ("unit_type") . " AND
								// objects.subclass = " . $unit_type->id () . " AND
								// objects.name LIKE '" . $selected_unit_id . "' AND
								// objects.status > 0
						// ", "count");

						if (1 == $list->count ())
						{
							$existing_unit = $list->begin();
						}
						else
						{
							$list = $list->arr ();

							foreach ($list as $unit)
							{
								if (strtolower ($unit->name ()) == strtolower ($selected_unit_id))
								{
									$existing_unit = $list->begin();
								}
							}
						}

						if (is_object ($existing_unit))
						{
							$saved_unit = $existing_unit;
						}
						else
						{ ### save new unit
							$saved_unit = obj ();
							$saved_unit->set_class_id ($unit_type->prop ("unit_type"));
							$saved_unit->set_parent ($parent->id ());
							$saved_unit->set_name ($selected_unit_id);
							$saved_unit->set_subclass ($unit_type->id ());
							$saved_unit->save();
						}
					}
					else
					{
						### user selection for this unit type was empty
					}

					$parents[$unit_type->id ()] = $saved_unit;
				}

				### Lowest defined unit in structure hierarchy passed, save last defined as address' parent
				$this_object->set_parent ($saved_unit->id ());
				$this_object->set_prop ("administrative_structure_oid", $administrative_structure->id ());

				### update address array
				$this->update_address_array (array ("id" => $this_object->id ()));
			}
			else
			{
				//!!! user error
				echo t("Riik valimata.");
				return false;
			}
		}
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function callback_location ($arr)
	{
		$this_object =& $arr["obj_inst"];

		### get administrative structure for country of address
		if (!($country = $this_object->get_first_obj_by_reltype("RELTYPE_COUNTRY")))
		{
			error::raise(array(
				"msg" => t("Aadressi riik defineerimata."),
				"fatal" => true,
				"show" => true,
			));
		}

		$administrative_structure = NULL;

		if (is_oid ($this_object->prop ("administrative_structure_oid")))
		{ ### address has once been saved according to an admin. structure
			if ($this->can ("view", $this_object->prop ("administrative_structure_oid")))
			{ ### structure object still exists
				$administrative_structure = obj ($this_object->prop ("administrative_structure_oid"));
			}
			else
			{ ### structure object has probably been deleted
				echo t("Admin struktuur m22ramata");//!!! siin midagi...
			}
		}

		if (empty ($administrative_structure))
		{
			$administrative_structure = $country->get_first_obj_by_reltype("RELTYPE_ADMINISTRATIVE_STRUCTURE");
		}

		if (!is_object ($administrative_structure))
		{
			//!!! throw user err
			t("Haldusjaotuse struktuur riigi jaoks defineerimata.");
			return PROP_FATAL_ERROR;
		}

		$cl_administrative_structure = get_instance (CL_COUNTRY_ADMINISTRATIVE_STRUCTURE);
		$unit_types =& $cl_administrative_structure->get_structure (array(
			"id" => $administrative_structure->id(),
		));

		### get autocomplete parameters parent hierarchy for each unit type
		$autocomplete_params = array ();
		$autocomplete_params["street"][] = "location_street";

		foreach ($unit_types as $unit_type_id => $unit_type)
		{
			$parent = obj (
				$unit_type->prop ("parent_unit")
			);

			if ($parent->class_id() == CL_COUNTRY_ADMINISTRATIVE_UNIT_TYPE)
			{
				$autocomplete_params[$unit_type->id ()][] = "location_" . $parent->id ();
			}
			elseif ($parent->class_id() == CL_COUNTRY)
			{
				$autocomplete_params[$unit_type->id ()][] = "location_country";
			}

			### add all unit types to potential parent unit types for street
			$autocomplete_params["street"][] = "location_" . $unit_type->id ();
		}

		### get address hierarchy for displaying currently chosen values of address fields
		$parent = obj ($this_object->parent ());
		$current_values = array ();
		$address_classes = array (
			CL_ADDRESS_STREET,
			CL_COUNTRY_ADMINISTRATIVE_UNIT,
			CL_COUNTRY_CITY,
			CL_COUNTRY_CITYDISTRICT,
		);

		while (is_object ($parent) and in_array ($parent->class_id (), $address_classes))
		{
			$subclass = ($parent->class_id () == CL_ADDRESS_STREET)
					?
				$parent->class_id ()
					:
				$parent->subclass ();

			$current_values[$subclass]["name"] = $parent->name ();
			$current_values[$subclass]["value"] = $parent->id ();
			$parent = obj (
				$parent->parent ()
			);
		}

		### define callback properties
		$prop = array ();
		$params = array (
			"id" => $this_object->id (),
		);
		$autocomplete_source = $this->mk_my_orb ("location_autocomplete", $params, CL_ADDRESS, false, true);
		$orb = parse_url ($autocomplete_source);
		$autocomplete_source = $orb["path"] . "?" . $orb["query"];

		foreach ($unit_types as $unit_type_id => $unit_type)
		{
			$prop["location_" . $unit_type->id ()] = array(
				"autocomplete_source" => $autocomplete_source,
				"autocomplete_params" => $autocomplete_params[$unit_type->id ()],
				"option_is_tuple" => true,
				"type" => "textbox",
				"name" => "location_" . $unit_type->id (),
				"caption" => $unit_type->name (),
				"value" => $current_values[$unit_type->id ()]["value"],
				"content" => $current_values[$unit_type->id ()]["name"],
			);
		}

		$prop["location_street"] = array(
			"autocomplete_source" => $autocomplete_source,
			"autocomplete_params" => $autocomplete_params["street"],
			"option_is_tuple" => true,
			"type" => "textbox",
			"name" => "location_street",
			"caption" => t("Tänav"),
			"value" => $current_values[CL_ADDRESS_STREET]["value"],
			"content" => $current_values[CL_ADDRESS_STREET]["name"],
		);
		return $prop;
	}

/**
    @attrib name=location_autocomplete all_args=1
	@param id required type=int
	@returns List of autocomplete options separated by newline (\n). Void on error.
**/
	function location_autocomplete ($arr)
	{
		$this_object = obj ($arr["id"]);

		### get administrative structure for country of address
		$country = $this_object->get_first_obj_by_reltype("RELTYPE_COUNTRY");
		$administrative_structure = is_oid ($this_object->prop ("administrative_structure_oid"))
				?
			obj ($this_object->prop ("administrative_structure_oid"))
				:
			$country->get_first_obj_by_reltype("RELTYPE_ADMINISTRATIVE_STRUCTURE");

		if (!is_object ($administrative_structure))
		{
			error::raise(array(
				"msg" => t("Autocomplete'i valikute jaoks riigile administratiivjaotuse struktuur määramata"),
				"fatal" => true,
				"show" => false,
			));
			exit;
		}

		$cl_administrative_structure = get_instance (CL_COUNTRY_ADMINISTRATIVE_STRUCTURE);
		$unit_types =& $cl_administrative_structure->get_structure (array(
			"id" => $administrative_structure->id(),
		));

		### get parameters passed from autocomplete
		$parameters = $this->get_autocomplete_parameters ($arr);

		### get $parent according to unit type
		if (array_key_exists ("street", $parameters))
		{
			$unit_types_reversed = array_reverse ($unit_types, true);

			foreach ($unit_types_reversed as $unit_type)
			{
				if (is_oid ($parameters[$unit_type->id ()]))
				{
					$parent = (int) $parameters[$unit_type->id ()];
					break;
				}
			}
		}
		elseif (array_key_exists ("country", $parameters))
		{
			$parent = (int) $parameters["country"];
		}
		elseif (is_oid (reset ($parameters)))
		{
			$parent = (int) reset ($parameters);
		}

		if (empty ($parent))
		{
			exit;
		}

		### get subclass/class of requesting property
		$subclass = NULL;
		preg_match ("/location_(\d{1,11}|street)/S", urldecode ($arr["requester"]), $matches);
		$requester_id = $matches[1];

		if (is_oid ($requester_id) and $this->can ("view", $requester_id))
		{
			$requesting_unit_type = obj ($requester_id);
			$class_id = (int) $requesting_unit_type->prop ("unit_type");
			$subclass = $requesting_unit_type->id ();
		}
		elseif ($requester_id == "street")
		{
			$class_id = CL_ADDRESS_STREET;
		}
		else
		{
			exit;
		}

// /* dbg */ $o = obj(140168);
// /* dbg */ echo $class_id . "=" . $o->class_id() . "<br>";
// /* dbg */ echo $subclass . "=" . $o->subclass() . "<br>";
// /* dbg */ echo $parent . "=" . $o->parent() . "<br>";

		### get options
		$list = new object_list (array (
			"class_id" => $class_id,
			"subclass" => $subclass,
			"parent" => $parent,
		));

// /* dbg */ arr ($list->count());

		$administrative_units = $list->arr ();

		### parse units to autocomplete options
		$autocomplete_options = array ();

		foreach ($administrative_units as $unit)
		{
			$autocomplete_options[] = $unit->id () . "=>" . $unit->name ();
		}

		$autocomplete_options = implode ("\n", $autocomplete_options);
		$charset = aw_global_get("charset");
		header ("Content-Type: text/html; charset=" . $charset);
		echo $autocomplete_options;
		exit;
	}

	function get_autocomplete_parameters ($arr)
	{
		$parameters = array ();

		foreach ($arr as $name => $value)
		{
			if (is_array ($value))
			{
				$parameters = $this->get_autocomplete_parameters ($value) + $parameters;
			}

			if (preg_match ("/location_(\d{1,11}|street|country)/S", $name, $matches))
			{
				$unit_type_id = $matches[1];

				if ($unit_type_id == "street")
				{
					$parameters["street"] = urldecode ($value);
				}
				elseif ($unit_type_id == "country")
				{
					$parameters["country"] = urldecode ($value);
				}
				else
				{
					$parameters[$unit_type_id] = urldecode ($value);
				}
			}
		}

		return $parameters;
	}

/**
    @attrib name=get_address_array all_args=1
	@param id required type=int
	@returns Associative array: administrative_unit_type name => array ("name" => administrative_unit name, "id" => administrative_unit oid)
**/
	function get_address_array ($arr)
	{
		$this_object = obj ($arr["id"]);
		// $this->update_address_array ($arr);//!!! pole m6tet iga kysimisega teha, seda tuleb teha salvestamisel ja muutuste tegemisel
		$address_array = $this_object->prop ("address_array");
		return $address_array;
	}

	function update_address_array ($arr)
	{
		if (!is_oid ($arr["id"]) or !$this->can ("view", $arr["id"]))
		{
			return false;
		}

		$this_object = obj ($arr["id"]);
		$current =$this_object;
		$address_array = array ();
		$address_classes = array (
			CL_COUNTRY_ADMINISTRATIVE_UNIT,
			CL_COUNTRY_CITY,
			CL_COUNTRY_CITYDISTRICT,
		);

		do
		{
			$current = obj ($current->parent ());

			if ($current->class_id () == CL_COUNTRY)
			{
				$address_array[t("Riik")] = $current->name ();
			}
			elseif ($current->class_id () == CL_ADDRESS_STREET)
			{
				$address_array[t("Tänav")] = $current->name ();
			}
			elseif (in_array ($current->class_id (), $address_classes))
			{
				$unit_type = obj ($current->subclass ());
				$address_array[$unit_type->name ()] = $current->name ();
			}
		}
		while ($current->class_id () != CL_COUNTRY);

		$this_object->set_prop ("address_array", $address_array);
		$this_object->save ();
	}

/**
    @attrib name=get_envelope_address all_args=1
	@param id required type=int
	@returns String address for printing on envelopes etc.
**/
	function get_envelope_address ($arr)
	{
		$this_object = obj ($arr["id"]);
			//!!! use address array from property if adm str etc not changed, otherwise build array from current values
			//!!! teha nii et viimane rida oleks: T2nav majanr-krtnr
			//!!! ymbrikuaadressi formaati peaks saama riigi v. haldusjaotuse juurest dfn-da
		return implode ("\n", $this_object->prop ("address_array"));
	}
}

?>
