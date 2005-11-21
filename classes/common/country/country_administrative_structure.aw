<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/country_administrative_structure.aw,v 1.3 2005/11/21 09:04:13 voldemar Exp $
// country_administrative_structure.aw - Riigi haldusjaotus
/*

@classinfo syslog_type=ST_COUNTRY_ADMINISTRATIVE_STRUCTURE relationmgr=yes no_comment=1 no_status=1

@groupinfo grp_administrative_structure caption="Haldusjaotuse struktuur"


@default table=objects
@default field=meta
@default method=serialize
@default group=general
	@property country type=relpicker reltype=RELTYPE_COUNTRY clid=CL_COUNTRY automatic=1
	@comment Riik, mille haldusjaotuse struktuuri määratakse.
	@caption Riik

	@property address_admin type=textbox
	@comment Kasutaja kellel on 6igused k6igele ja k6igeks aadressisysteemi objektidel. Kasutatakse p6hiliselt programmaatiliselt systeemi haldamiseks t88 k2igus.
	@caption Aadresside administraatori kasutaja uid

@default group=grp_administrative_structure
	@property administrative_structure type=releditor reltype=RELTYPE_ADMINISTRATIVE_DIVISION mode=manager props=name,type,parent_division,division,jrk table_fields=jrk,name,parent_division_show editonly=1
	@caption Haldusjaotuse struktuur

	@property administrative_structure_data type=hidden


// --------------- RELATION TYPES ---------------------

@reltype ADMINISTRATIVE_DIVISION value=1 clid=CL_COUNTRY_ADMINISTRATIVE_DIVISION
@caption Haldusüksus

@reltype COUNTRY value=2 clid=CL_COUNTRY
@caption Riik

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

class country_administrative_structure extends class_base
{
	function country_administrative_structure ($arr = array ())
	{
		$this->init (array (
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY_ADMINISTRATIVE_STRUCTURE
		));
	}

/* classbase methods */
	function callback_on_load ($arr)
	{
		aw_global_set ("address_system_administrative_structure", 1);

		if (is_oid ($arr["request"]["id"]))
		{
			$this_object = obj ($arr["request"]["id"]);

			### prepare unit parent selection list for unit releditor
			$country = $this_object->get_first_obj_by_reltype("RELTYPE_COUNTRY");
			$units = array ();
			$units[$country->id ()] = $country->name ();

			foreach ($this_object->connections_from (array ("type" => "RELTYPE_ADMINISTRATIVE_DIVISION")) as $connection)
			{
				$unit = $connection->to ();

				if ($arr["request"]["administrative_structure"] != $unit->id ())
				{
					$units[$unit->id ()] = $unit->name ();
				}
			}

			aw_global_set ("address_system_parent_select_units", $units);
		}
	}

	function get_property ($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch ($prop["group"])
		{
			case "grp_administrative_structure":
				if (!$this_object->get_first_obj_by_reltype("RELTYPE_COUNTRY"))
				{
					$retval = PROP_FATAL_ERROR;
					$prop["error"] = t("Riik valimata");
				}
				break;
		}

		switch($prop["name"])
		{
			case "administrative_structure":
				$addresses_using_this = "";

				if ($addresses_using_this > 0)
				{
					$prop["error"] = sprintf (t("%s aadressi kasutab seda haldusjaotust! Muudatuste salvestamisel tekivad neis aadressides vead."), $addresses_using_this);
						//!!! vead tekivad ainult siis kui midagi kustutatakse vahelt, mis on mingi aadressi parentiks. muidu muutub ainult pealisstruktuur aadress ise aga j22b selle parenti alla mille all ta ennegi oli ilma ylevalpoolset muudatust "tajumata". v6ibolla v6iks muutmisel k6igi nende aadresside sissekirjutatud asju apdeitida. kui yritatakse teha muudatust, mis tooks kaasa jamasid olemasolevate aadressidega, siis tuleb kasutajat teavitada jms. sarnane kontroll peaks olema ka admin division ja admin unit klassides. struktuuri muutmisel peab ka olemasolevad halduspiirkonnad ymber t6stma kui v5imalik.
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
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_post_save ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$divisions = array ();

		foreach ($this_object->connections_from (array ("type" => "RELTYPE_ADMINISTRATIVE_DIVISION")) as $connection)
		{
			$division = $connection->to ();
			$divisions[] = $division;
		}

		usort ($divisions, array ($this, "sort_by_ord"));

		foreach ($divisions as $key => $division)
		{
			### set this_object oid for all divisions under this structure. for easier maintenance.
			$division->set_prop ("administrative_structure", $this_object->id ());

			### set corrected order nr
			$division->set_ord ($key + 1);

			### ...
			$division->save ();
		}
	}
/* END classbase methods */

	function sort_by_ord ($a, $b)
	{
		if ($a->ord () > $b->ord ())
		{
			$result = 1;
		}
		elseif ($a->ord () < $b->ord ())
		{
			$result = -1;
		}
		else
		{
			$result = 0;
		}

		return $result;
	}
}

?>
