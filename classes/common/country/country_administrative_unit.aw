<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/country_administrative_unit.aw,v 1.3 2006/05/23 10:53:50 kristo Exp $
// country_administrative_unit.aw - Halduspiirkond
/*

@classinfo syslog_type=ST_COUNTRY_ADMINISTRATIVE_UNIT relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
	@property administrative_structure type=hidden

	@property name type=textbox
	@caption Nimi

	@property subclass type=text
	@caption Tüüp

	@property parent type=text
	@comment Halduspiirkond, millesse käesolev halduspiirkond kuulub
	@caption Kõrgem halduspiirkond

	@property parent_show type=text field=meta method=serialize
	@caption Kõrgem halduspiirkond

	@property parent_select type=relpicker reltype=RELTYPE_PARENT_ADMINISTRATIVE_UNIT clid=CL_COUNTRY_ADMINISTRATIVE_UNIT,CL_COUNTRY_CITY,CL_COUNTRY_CITYDISTRICT store=no
	@comment Halduspiirkond, millesse käesolev halduspiirkond kuulub
	@caption Vali kõrgem halduspiirkond

@groupinfo transl caption="T&otilde;lgi"
@default group=transl

@property transl type=callback callback=callback_get_transl store=no
@caption T&otilde;lgi

// --------------- RELATION TYPES ---------------------

@reltype PARENT_ADMINISTRATIVE_UNIT value=1 clid=CL_COUNTRY_ADMINISTRATIVE_UNIT,CL_COUNTRY_CITY,CL_COUNTRY_CITYDISTRICT
@caption Kõrgem halduspiirkond

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

class country_administrative_unit extends class_base
{
	function country_administrative_unit ()
	{
		$this->init(array(
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY_ADMINISTRATIVE_UNIT
		));

		$this->trans_props = array(
			"name"
		);
	}

	function get_property ($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch ($prop["name"])
		{
			case "parent_show":
				break;

			case "subclass":
				if (is_oid ($prop["value"]))
				{
					$administrative_unit = obj ($prop["value"]);
					$prop["value"] = $administrative_unit->name ();
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
		}

		return $retval;
	}

	function set_property ($arr = array ())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch($prop["name"])
		{
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			case "parent_select":
				if (is_oid ($prop["value"]))
				{
					$parent = obj ($prop["value"]);
					$this_object->set_parent ($parent->id ());
					$this_object->set_prop ("parent_show", $parent->name ());
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

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}
}

?>
