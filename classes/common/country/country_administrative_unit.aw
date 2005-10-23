<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/country_administrative_unit.aw,v 1.1 2005/10/23 17:17:15 voldemar Exp $
// country_administrative_unit.aw - Haldusüksus
/*

@classinfo syslog_type=ST_COUNTRY_ADMINISTRATIVE_UNIT relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
	@property name type=textbox
	@caption Nimi

	@property subclass type=text
	@caption Tüüp

	@property parent type=text
	@comment Haldusüksus, millesse käesolev haldusüksus kuulub
	@caption Kõrgem haldusüksus

	@property parent_show type=text field=meta method=serialize
	@caption Kõrgem haldusüksus

	@property parent_select type=relpicker reltype=RELTYPE_PARENT_ADMINISTRATIVE_UNIT clid=CL_COUNTRY_ADMINISTRATIVE_UNIT,CL_COUNTRY_CITY,CL_COUNTRY_CITYDISTRICT store=no
	@comment Haldusüksus, millesse käesolev haldusüksus kuulub
	@caption Vali kõrgem haldusüksus

// --------------- RELATION TYPES ---------------------

@reltype PARENT_ADMINISTRATIVE_UNIT value=1 clid=CL_COUNTRY_ADMINISTRATIVE_UNIT,CL_COUNTRY_CITY,CL_COUNTRY_CITYDISTRICT
@caption Kõrgem haldusüksus

*/

### address system settings


class country_administrative_unit extends class_base
{
	function country_administrative_unit ()
	{
		$this->init(array(
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY_ADMINISTRATIVE_UNIT
		));
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
}

?>
