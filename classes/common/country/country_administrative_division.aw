<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/country_administrative_division.aw,v 1.2 2005/11/21 09:04:13 voldemar Exp $
// country_administrative_division.aw - Haldusüksus
/*

@classinfo syslog_type=ST_COUNTRY_ADMINISTRATIVE_DIVISION relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
	@property administrative_structure type=hidden field=meta method=serialize

	@property type type=select field=meta method=serialize
	@comment Haldusüksuse tüüp
	@caption Tüüp

	@property parent_division_show type=text field=meta method=serialize
	@caption Kõrgem haldusüksus

	@property parent_division type=relpicker reltype=RELTYPE_PARENT_ADMINISTRATIVE_UNIT clid=CL_COUNTRY,CL_COUNTRY_ADMINISTRATIVE_DIVISION,CL_COUNTRY_ADMINISTRATIVE_UNIT,CL_COUNTRY_CITY,CL_COUNTRY_CITYDISTRICT automatic=1 field=meta method=serialize
	@comment Haldusüksus, millesse käesolev haldusüksus kuulub
	@caption Kõrgem haldusüksus

	@property jrk type=textbox datatype=int
	@comment Positiivne täisarv (vahemikus 1 kuni 1000000)
	@caption Järjekord


// --------------- RELATION TYPES ---------------------

@reltype PARENT_ADMINISTRATIVE_UNIT value=1 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION,CL_COUNTRY_ADMINISTRATIVE_UNIT,CL_COUNTRY_CITY,CL_COUNTRY_CITYDISTRICT,CL_COUNTRY
@caption Kõrgem haldusüksus

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

class country_administrative_division extends class_base
{
	function country_administrative_division()
	{
		$this->init(array(
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY_ADMINISTRATIVE_DIVISION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "parent_division":
				$units = aw_global_get ("address_system_parent_select_units");

				if (is_array ($units))
				{
					$prop["options"] = $units;
				}
				break;

			case "parent_division_show":
				if (aw_global_get ("address_system_administrative_structure"))
				{//!!! vaja releditori muuta et n2idataks kui ainult table_fieldsis on prop aga props-is pole
					$parent = $this_object->get_first_obj_by_reltype("RELTYPE_PARENT_ADMINISTRATIVE_UNIT");
					$prop["value"] = $parent->name ();
				}
				else
				{
					return PROP_IGNORE;
				}
				break;

			case "type":
				$prop["options"] = array (
					CL_COUNTRY_ADMINISTRATIVE_UNIT => t("Haldusüksus"),
					CL_COUNTRY_CITY => t("Linna tüüpi haldusüksus"),
					CL_COUNTRY_CITYDISTRICT => t("Linnaosa tüüpi haldusüksus"),
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
			case "parent_division":
				if (is_oid ($prop["value"]))
				{
					$parent = obj ($prop["value"]);
					$this_object->set_prop ("parent_division_show", $parent->name());
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
