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

class country_administrative_structure_encoding_object extends _int_object
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

	function country_administrative_structure_encoding_object ($param)
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
				case "encoding_by_unit":
					return $this->as_get_unit_encoding ($param);
			}
		}
		else
		{
			switch ($param)
			{
				default:
					return parent::prop ($param);
			}
		}
	}

	function set_prop ($name, $param)
	{
		switch ($name)
		{
			case "encoding_by_unit":
				return;

			default:
				return parent::set_prop ($name, $param);
		}
	}

    // @attrib name=as_get_unit_encoding
	// @param unit required
	// @returns Encoded value for unit.
	function as_get_unit_encoding ($arr)
	{
		### validate unit object
		if (is_object ($arr["unit"]))
		{
			$unit =& $arr["unit"];
		}
		elseif (is_oid ($arr["unit"]) and $this->can ("view", $arr["unit"]))
		{
			$unit = obj ($arr["unit"]);
		}
		else
		{
/* dbg */ if ($_GET[ADDRESS_DBG_FLAG]) { echo "adminstructureencoding::as_get_unit_encoding: unit not defined or not visible [{$arr["unit"]}]".NEWLINE; }
			return false;
		}

		$encoded_value = $unit->meta (
			"admin_structure_enc-" . $this->obj["oid"] . "-value1"
		);
		return $encoded_value;
	}
}

?>
