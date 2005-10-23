<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/Attic/country_administrative_unit_type.aw,v 1.1 2005/10/23 17:17:15 voldemar Exp $
// country_administrative_unit_type.aw - Haldusjaotis
/*

@classinfo syslog_type=ST_COUNTRY_ADMINISTRATIVE_UNIT_TYPE relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
	@property unit_type type=select field=meta method=serialize
	@comment Haldusjaotise tüüp
	@caption Tüüp

	@property parent_unit_show type=text field=meta method=serialize
	@caption Kõrgem haldusjaotis

	@property parent_unit type=relpicker reltype=RELTYPE_PARENT_ADMINISTRATIVE_UNIT clid=CL_COUNTRY,CL_COUNTRY_ADMINISTRATIVE_UNIT_TYPE,CL_COUNTRY_ADMINISTRATIVE_UNIT,CL_COUNTRY_CITY,CL_COUNTRY_CITYDISTRICT automatic=1 field=meta method=serialize
	@comment Haldusüksus, millesse käesolev haldusüksus kuulub
	@caption Kõrgem haldusjaotis

	@property jrk type=textbox datatype=int
	@comment Positiivne täisarv
	@caption Näitamisjärjekord


// --------------- RELATION TYPES ---------------------

@reltype PARENT_ADMINISTRATIVE_UNIT value=1 clid=CL_COUNTRY_ADMINISTRATIVE_UNIT_TYPE,CL_COUNTRY_ADMINISTRATIVE_UNIT,CL_COUNTRY_CITY,CL_COUNTRY_CITYDISTRICT,CL_COUNTRY
@caption Kõrgem haldusjaotis

*/

class country_administrative_unit_type extends class_base
{
	function country_administrative_unit_type()
	{
		$this->init(array(
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY_ADMINISTRATIVE_UNIT_TYPE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "parent_unit":
				$units = aw_global_get ("address_system_parent_select_units");

				if (is_array ($units))
				{
					$prop["options"] = $units;
				}
				break;

			case "parent_unit_show":
				if (!aw_global_get ("address_system_administrative_structure"))
				{
					return PROP_IGNORE;
				}
				break;

			case "unit_type":
				$prop["options"] = array (
					CL_COUNTRY_ADMINISTRATIVE_UNIT => t("Haldusjaotis"),
					CL_COUNTRY_CITY => t("Linna tüüpi haldusjaotis"),
					CL_COUNTRY_CITYDISTRICT => t("Linnaosa tüüpi haldusjaotis"),
				);
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
			case "parent_unit":
				if (is_oid ($prop["value"]))
				{
					$parent = obj ($prop["value"]);
					$this_object->set_prop ("parent_unit_show", $parent->name());
				}
				break;
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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
}

?>
