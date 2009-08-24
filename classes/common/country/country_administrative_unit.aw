<?php
/*

@classinfo syslog_type=ST_COUNTRY_ADMINISTRATIVE_UNIT relationmgr=yes no_comment=1 no_status=1 maintainer=voldemar

@default table=objects
@default group=general
	@property administrative_structure type=hidden

	@property name type=textbox
	@caption Nimi

	@property subclass type=text
	@caption Tüüp

	@property complete_name type=textbox field=meta method=serialize
	@caption T&auml;isnimi

	@property alt_name type=textbox field=meta method=serialize
	@caption Paralleelnimi

	@property ext_id_1 type=textbox field=meta method=serialize
	@caption Identifikaator v&auml;lises s&uuml;steemis 1

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

require_once(aw_ini_get("basedir") . "/classes/common/address/as_header.aw");

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
