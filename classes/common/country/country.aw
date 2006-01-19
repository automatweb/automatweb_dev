<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/country.aw,v 1.5 2006/01/19 19:21:51 voldemar Exp $
// country.aw - Riik v2
/*

@classinfo syslog_type=ST_COUNTRY relationmgr=yes no_comment=1 no_status=1

@groupinfo grp_settings caption="Seaded"


@default table=objects
@default field=meta
@default method=serialize
@default group=general

@default group=grp_settings
	@property administrative_structure type=relpicker reltype=RELTYPE_ADMINISTRATIVE_STRUCTURE clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE
	@caption Haldusjaotus

	@property code type=textbox
	@comment Kahetäheline riigi kood (ISO 3166-1 alpha-2)
	@caption Kood


// --------------- RELATION TYPES ---------------------

@reltype ADMINISTRATIVE_STRUCTURE value=1 clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE
@caption Haldusjaotus

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

class country extends class_base
{
	var $admin_division_classes = array (
		CL_COUNTRY_ADMINISTRATIVE_UNIT,
		CL_COUNTRY_CITYDISTRICT,
		CL_COUNTRY_CITY,
		CL_ADDRESS_STREET,
	);

	function country()
	{
		$this->init(array(
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "":
				$addresses_using_this = "";

				if ( ($addresses_using_this > 0) and (is_oid ($prop["value"])) )
				{
					$prop["error"] = sprintf (t("%s aadressi kasutab hetkel valitud haldusjaotust! Muudatuste salvestamisel ..."), $addresses_using_this);//!!! t2psustada mis juhtub kui uus struktuur m22rata.
				}
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
			case "administrative_structure":
				if (is_oid ($prop["value"]))
				{
					$o = obj ($prop["value"]);

					if ($o->parent () != $this_object->id ())
					{
						$retval = PROP_FATAL_ERROR;
						$prop["error"] = t("Riigi adminstratiivjaotuse objekt peab asuma riigiobjekti all");
					}
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
