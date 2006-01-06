<?php
// $Header: /home/cvs/automatweb_dev/classes/common/address/address.aw,v 1.7 2006/01/06 09:37:26 voldemar Exp $
// address.aw - Aadress v2
/*

@classinfo syslog_type=ST_ADDRESS relationmgr=yes no_comment=1 no_status=1

@groupinfo grp_address caption="Aadress"


@default table=objects
@default field=meta
@default method=serialize
@default group=general
	@property administrative_structure type=relpicker reltype=RELTYPE_ADMINISTRATIVE_STRUCTURE clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE automatic=1
	@caption Riik/haldusjaotus

	@property address_data type=hidden
	@property country type=hidden

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

@reltype ADMINISTRATIVE_STRUCTURE value=1 clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE
@caption Haldusjaotus

@reltype ADMINISTRATIVE_UNIT value=2 clid=clid=CL_COUNTRY_ADMINISTRATIVE_UNIT,CL_COUNTRY_CITY,CL_COUNTRY_CITYDISTRICT,CL_COUNTRY
@caption Halduspiirkond

*/

### address system settings
if (!defined ("ADDRESS_SYSTEM"))
{
	define ("ADDRESS_SYSTEM", 1);
	define ("NEWLINE", "<br />");
	define ("ADDRESS_STREET_TYPE", "street"); # used in many places. also in autocomplete javascript -- caution when changing.
	define ("ADDRESS_COUNTRY_TYPE", "country"); # used in many places. also in autocomplete javascript -- caution when changing.
	define ("ADDRESS_DBG_FLAG", "address_dbg");
}

class address extends class_base
{
	var $address_classes = array (
		CL_COUNTRY_ADMINISTRATIVE_UNIT,
		CL_COUNTRY_CITY,
		CL_COUNTRY_CITYDISTRICT,
		CL_ADDRESS_STREET,
	);

	function address($arr = array ())
	{
		$this->init(array(
			"tpldir" => "common/address",
			"clid" => CL_ADDRESS
		));
	}

/* classbase methods */
	function callback_on_load ($arr)
	{
		if (is_oid ($arr["request"]["id"]) and !$arr["new"])
		{
			$this_object = obj ($arr["request"]["id"]);
			$this->administrative_structure = $this_object->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object = $arr["obj_inst"];

		switch($prop["name"])
		{
			case "location_country":
				if (!is_object ($this->administrative_structure))
				{
					$this->administrative_structure = $this_object->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");

					if (!is_object ($this->administrative_structure))
					{
						$retval = PROP_FATAL_ERROR;
						$prop["error"] = t("Haldusjaotus määramata.");
					}

					$prop["value"] = $this->administrative_structure->prop ("country");
				}
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
		if (!$arr["new"] and array_key_exists ("location_street", $arr["request"]))//!!! milleks street?
		{
			$this_object = $arr["obj_inst"];
			$structure =& $this->administrative_structure->prop ("structure_array");

			### set address location by unit
			foreach ($structure as $key => $division)
			{
				if (is_object ($division))
				{
					$selected_unit_id = $arr["request"]["location_" . $division->id ()];
				}
				else
				{
					$selected_unit_id = $arr["request"]["location_" . $division];
				}

				if (is_oid ($selected_unit_id))
				{
					$this_object->set_prop ("unit_by_id", array (
						"division" => $division,
						"id" => $selected_unit_id,
					));
				}
				elseif (strlen ($selected_unit_id) > 1)
				{
					$this_object->set_prop ("unit_name", array (
						"division" => $division,
						"name" => $selected_unit_id,
					));
				}
			}
		}
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$this_object = obj ($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $this_object->prop("name"),
		));
		return $this->parse();
	}
/* END classbase methods */

/* public methods */

/**
    @attrib name=location_autocomplete all_args=1
	@param id required type=int
	@returns List of autocomplete options separated by newline (\n). Void on error.
**/
	function location_autocomplete ($arr)
	{
		$this_object = obj ($arr["id"]);

		### get administrative structure
		$this->administrative_structure = $this_object->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");
		$divisions =& $this->administrative_structure->prop ("structure_array");

		### get parameters passed from autocomplete
		$parameters = $this->get_autocomplete_parameters ($arr);

		### get $parent according to division
		if (array_key_exists (ADDRESS_STREET_TYPE, $parameters))
		{
			$divisions_reversed = array_reverse ($divisions, true);

			foreach ($divisions_reversed as $division)
			{
				if (is_oid ($parameters[$division->id ()]))
				{
					$parent = (int) $parameters[$division->id ()];
					break;
				}
			}
		}
		elseif (is_oid (reset ($parameters)))
		{
			$parent = (int) reset ($parameters);
		}
		else
		{
			$parent = $this->administrative_structure->id ();
		}

		define ("AC_ERROR_PREFIX", ">>>AC-error>>>");

		if (empty ($parent))
		{
			exit (AC_ERROR_PREFIX . t("Viga: k6rgem haldusjaotus m22ramata."));//!!! teha autocomplete-i js-i veateate n2itamine
		}

		### get subclass/class of requesting property
		$subclass = NULL;
		preg_match ("/location_(\d{1,11}|street)/S", urldecode ($arr["requester"]), $matches);
		$requester_id = $matches[1];

		if ($this->can ("view", $requester_id))
		{
			$requesting_division = obj ($requester_id);
			$class_id = (int) $requesting_division->prop ("type");
			$subclass = $requesting_division->id ();
		}
		elseif ($requester_id == ADDRESS_STREET_TYPE)
		{
			$class_id = CL_ADDRESS_STREET;
		}
		else
		{
			exit (AC_ERROR_PREFIX . t("Viga: valikute taotleja-property m22ramata."));//!!! teha autocomplete-i js-i veateate n2itamine
		}

		### get options
		$args = array (
			"class_id" => $class_id,
			"subclass" => $subclass,
			"parent" => $parent,
		);
		$list = new object_list ($args);

// /* dbg */ arr ($args);
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
/* END public methods */

	function callback_location ($arr)
	{
		$this_object = $arr["obj_inst"];
		$administrative_structure = $this_object->prop ("administrative_structure");
		$divisions =& $administrative_structure->prop ("structure_array");

		### get autocomplete parameters parent hierarchy for each division
		$autocomplete_params = array ();
		$autocomplete_params[ADDRESS_STREET_TYPE][] = "location_street";

		foreach ($divisions as $division_id => $division)
		{
			$parent = obj (
				$division->prop ("parent_division")
			);

			if ($parent->class_id() == CL_COUNTRY_ADMINISTRATIVE_DIVISION)
			{
				$autocomplete_params[$division->id ()][] = "location_" . $parent->id ();
			}

			### add all divisions to potential parent divisions for street
			$autocomplete_params[ADDRESS_STREET_TYPE][] = "location_" . $division->id ();
		}

		### get address hierarchy for displaying currently chosen values of address fields
		$parent = obj ($this_object->parent ());
		$current_values = array ();

		while (is_object ($parent) and in_array ($parent->class_id (), $this->address_classes))
		{
			$subclass = (CL_ADDRESS_STREET == $parent->class_id ())
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

		foreach ($divisions as $division_id => $division)
		{
			$prop["location_" . $division->id ()] = array(
				"autocomplete_source" => $autocomplete_source,
				"autocomplete_params" => $autocomplete_params[$division->id ()],
				"option_is_tuple" => true,
				"type" => "textbox",
				"name" => "location_" . $division->id (),
				"caption" => $division->name (),
				"value" => $current_values[$division->id ()]["value"],
				"content" => $current_values[$division->id ()]["name"],
			);
		}

		$prop["location_street"] = array(
			"autocomplete_source" => $autocomplete_source,
			"autocomplete_params" => $autocomplete_params[ADDRESS_STREET_TYPE],
			"option_is_tuple" => true,
			"type" => "textbox",
			"name" => "location_street",
			"caption" => t("Tänav"),
			"value" => $current_values[CL_ADDRESS_STREET]["value"],
			"content" => $current_values[CL_ADDRESS_STREET]["name"],
		);
		return $prop;
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

			if (preg_match ("/location_(\d{1,11}|street)/S", $name, $matches))
			{
				$division_id = $matches[1];

				if ($division_id == ADDRESS_STREET_TYPE)
				{
					$parameters[ADDRESS_STREET_TYPE] = urldecode ($value);
				}
				else
				{
					$parameters[$division_id] = urldecode ($value);
				}
			}
		}

		return $parameters;
	}
}

?>
