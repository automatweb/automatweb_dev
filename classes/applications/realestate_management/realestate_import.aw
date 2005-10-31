<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_import.aw,v 1.1 2005/10/31 17:13:35 voldemar Exp $
// realestate_import.aw - Kinnisvaraobjektide Import
/*

@classinfo syslog_type=ST_REALESTATE_IMPORT relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize
	@property realestate_mgr type=relpicker reltype=RELTYPE_OWNER clid=CL_REALESTATE_MANAGER automatic=1
	@comment Kinnisvarahalduskeskkond mille objektide hulka soovitakse importida
	@caption Kinnisvarahalduskeskkond

	@property country type=relpicker reltype=RELTYPE_COUNTRY clid=CL_COUNTRY automatic=1
	@caption Riik

	@property import_url type=textbox
	@comment URL millelt objektid imporditakse
	@caption URL

	@property company type=relpicker reltype=RELTYPE_COMPANY clid=CL_CRM_COMPANY editonly=1
	@comment Organisatsioon mille alla objektid imporditakse
	@caption Organisatsioon

	@property city24_import type=text editonly=1
	@comment URL millele päringut tehes imporditakse objektid City24 süsteemist AW'i
	@caption City24 Importimine

	@property last_city24import type=hidden

// --------------- RELATION TYPES ---------------------

@reltype OWNER clid=CL_REALESTATE_MANAGER value=1
@caption Kinnisvaraobjektide halduskeskkond

@reltype COUNTRY clid=CL_COUNTRY value=2
@caption Riik

@reltype COMPANY clid=CL_CRM_COMPANY value=3
@caption Organisatsioon

*/

define ("REALESTATE_IMPORT_OK", 0);

define ("REALESTATE_IMPORT_ERR1", 1);
define ("REALESTATE_IMPORT_ERR2", 2);
define ("REALESTATE_IMPORT_ERR3", 3);
define ("REALESTATE_IMPORT_ERR4", 4);
define ("REALESTATE_IMPORT_ERR5", 5);
define ("REALESTATE_IMPORT_ERR6", 6);
define ("REALESTATE_IMPORT_ERR7", 7);
define ("REALESTATE_IMPORT_ERR8", 8);
define ("REALESTATE_IMPORT_ERR9", 9);
define ("REALESTATE_IMPORT_ERR10", 10);
define ("REALESTATE_IMPORT_ERR11", 11);
define ("REALESTATE_IMPORT_ERR61", 12);
define ("REALESTATE_IMPORT_ERR62", 13);
define ("REALESTATE_IMPORT_ERR12", 14);
define ("REALESTATE_IMPORT_ERR13", 15);
define ("REALESTATE_IMPORT_ERR14", 16);
define ("REALESTATE_IMPORT_ERR15", 17);

define ("REALESTATE_NEWLINE", "<br />");

class realestate_import extends class_base
{
	function realestate_import()
	{
		$this->init(array(
			"tpldir" => "applications/realestate_management/realestate_import",
			"clid" => CL_REALESTATE_IMPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object = $arr["obj_inst"];

		switch($prop["name"])
		{
			case "city24_import":
				$url = $this->mk_my_orb ("city24import", array (
					"id" => $this_object->id(),
					"company" => $this_object->prop ("company"),
				));
				$prop["value"] = html::href(array(
					"url" => $url,
					"target" => "_blank",
					"caption" => t("Impordi")
				));
				break;

			case "company":
				if (is_oid ($this_object->prop("realestate_mgr")))
				{
					$manager = obj ($this_object->prop("realestate_mgr"));
					$list = new object_list ($manager->connections_from(array(
						"type" => "RELTYPE_REALESTATEMGR_USER",
						"class_id" => CL_CRM_COMPANY,
					)));
					$prop["options"] = $list->names ();
				}
				else
				{
					$prop["error"] = t("Kinnisvarahalduskeskkond defineerimata");
				}
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

/**
	@attrib name=city24import nologin=1
	@param id required type=int
	@param company required type=int
	@param import_check_city24id_in_aw optional type=int
	@param import_city24id optional type=int
	@param charset_from optional
	@param charset_to optional
	@param quiet optional type=int
**/
	function city24_import ($arr)
	{
		obj_set_opt("no_cache", 1);
		$status = REALESTATE_IMPORT_OK;

		if (1 != $arr["quiet"]) { echo t("Import CITY24 xml allikast:") . REALESTATE_NEWLINE; }

		if (!empty ($arr["charset_from"]))
		{
			define ("REALESTATE_IMPORT_CHARSET_FROM", $arr["charset_from"]);
		}
		else
		{
			define ("REALESTATE_IMPORT_CHARSET_FROM", "UTF-8");
		}

		if (!empty ($arr["charset_to"]))
		{
			define ("REALESTATE_IMPORT_CHARSET_TO", $arr["charset_to"]);
		}
		else
		{
			define ("REALESTATE_IMPORT_CHARSET_TO", "ISO-8859-4");
		}

		$import_time = time();
		$this_object = obj ($arr["id"]);
		$last_import = $this_object->prop ("last_city24import");
		$this_object->set_prop ("last_city24import", $import_time);
		$this_object->save ();

		if (!is_oid ($this_object->prop ("realestate_mgr")))
		{
			echo t("Viga: halduskeskond defineerimata.") . REALESTATE_NEWLINE;
			return REALESTATE_IMPORT_ERR1;
		}
		else
		{
			$manager = obj ($this_object->prop ("realestate_mgr"));
		}

		if (!is_oid ($this_object->prop ("country")))
		{
			echo t("Viga: riik defineerimata.") . REALESTATE_NEWLINE;
			return REALESTATE_IMPORT_ERR2;
		}
		else
		{
			$country = obj ($this_object->prop ("country"));
		}

		$import_url = $this_object->prop ("import_url");
		// $import_url = "http://erivaldused:erivaldused@maakler.city24.ee/broker/city24broker/xml?lang=EST&search_count=10000";
		// $import_url = "/www/dev/voldemar/test.xml";
		// $fp = fopen ($import_url, "r");
		$xml = file_get_contents ($import_url);
		$parser = xml_parser_create();
		xml_parse_into_struct($parser, $xml, $xml_data, $xml_index);
		xml_parser_free($parser);

		$cl_realestate_mgr = get_instance (CL_REALESTATE_MANAGER);
		$cl_classificator = get_instance(CL_CLASSIFICATOR);
		$cl_address = get_instance (CL_ADDRESS);
		$cl_administrative_structure = get_instance (CL_COUNTRY_ADMINISTRATIVE_STRUCTURE);

		//!!!vaja?
		if ($this->changed_visible_to)
		{
			#### visible_to
			$prop_args = array (
				"clid" => CL_REALESTATE_PROPERTY,
				"name" => "visible_to",
			);
			list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
			$visible_tos = $options->names();
			$this->changed_visible_to = false;
		}

		if ($this->changed_special_statuses)
		{
			#### special_statuses
			$prop_args = array (
				"clid" => CL_REALESTATE_PROPERTY,
				"name" => "special_status",
			);
			list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
			$special_statuses = $options->names();
			$this->changed_special_statuses = false;
		}
		//!!!

		### variables
		$this->property_data = NULL;
		$this->end_property_import = false;
		$this->changed_maakonnad = true;
		$this->changed_transaction_types = true;
		$this->changed_conditions = true;
		$this->changed_stove_types = true;
		$this->changed_usage_purposes = true;
		$this->changed_transaction_constraints = true;
		$this->changed_visible_to = true;
		$this->changed_priorities = true;
		$this->changed_special_statuses = true;
		$this->changed_legal_statuses = true;
		$this->changed_roof_types = true;
		$this->changed_land_uses = true;

		#### address
		$administrative_structure = $country->get_first_obj_by_reltype("RELTYPE_ADMINISTRATIVE_STRUCTURE");

		if (!is_object ($administrative_structure))
		{
			echo t("Valitud riigiobjektil pole määratud haldusjaotust või puuduvad kasutajal sellele õigused.") . REALESTATE_NEWLINE;
			return REALESTATE_IMPORT_ERR3;
		}

		$unit_types =& $cl_administrative_structure->get_structure (array(
			"id" => $administrative_structure->id(),
		));

		##### admin unit type objects
		$maakond_unit_type = reset ($unit_types);

		foreach ($unit_types as $unit_type)
		{
			if ($unit_type->prop ("parent_unit") == $maakond_unit_type->id ())
			{
				if ($unit_type->prop ("unit_type") == CL_COUNTRY_ADMINISTRATIVE_UNIT)
				{
					$vald_unit_type = $unit_type;
				}

				if ($unit_type->prop ("unit_type") == CL_COUNTRY_CITY)
				{
					$linn_unit_type = $unit_type;
				}
			}

			if ($unit_type->prop ("unit_type") == CL_COUNTRY_CITYDISTRICT)
			{
				$linnaosa_unit_type = $unit_type;
			}
		}

		foreach ($unit_types as $unit_type)
		{
			if ($unit_type->prop ("parent_unit") == $vald_unit_type->id ())
			{
				$asula_unit_type = $unit_type;
			}
		}

		#### organisatsiooni t88tajad
		$company = obj ($arr["company"]);
		$cl_user = get_instance(CL_USER);
		$employees = new object_list($company->connections_from(array(
			"type" => "RELTYPE_WORKERS",
			"class_id" => CL_CRM_PERSON,
		)));
		$employees = $employees->names ();

		### indices
		#### property types
		$this->index_property_types = array (
			"Maja" => "house",
			"Ridaelamu" => "rowhouse",
			"Suvila" => "cottage",
			"Majaosa" => "housepart",
			"Korter" => "apartment",
			"Ã„ripind" => "commercial",
			"Garaazh" => "garage",
			"Maa" => "land",
		);

		#### index of already imported properties' city24 id-s
		$realestate_classes = array (
			CL_REALESTATE_HOUSE,
			CL_REALESTATE_ROWHOUSE,
			CL_REALESTATE_COTTAGE,
			CL_REALESTATE_HOUSEPART,
			CL_REALESTATE_APARTMENT,
			CL_REALESTATE_COMMERCIAL,
			CL_REALESTATE_GARAGE,
			CL_REALESTATE_LAND,
		);
		$realestate_folders = array (
			$manager->prop ("houses_folder"),
			$manager->prop ("rowhouses_folder"),
			$manager->prop ("cottages_folder"),
			$manager->prop ("houseparts_folder"),
			$manager->prop ("apartments_folder"),
			$manager->prop ("commercial_properties_folder"),
			$manager->prop ("garages_folder"),
			$manager->prop ("land_estates_folder"),
		);
		$list = new object_list (array (
			"class_id" => $realestate_classes,
			"parent" => $realestate_folders,
			// "city24_object_id" => new obj_predicate_prop (OBJ_COMP_GREATER, 0),//!!! viga: can not complex searches on metadata fields!
		));
		$list = $list->arr ();

		$imported_object_ids = array ();

		foreach ($list as $property)
		{
			if ((int) $property->prop ("city24_object_id"))
			{
				$imported_object_ids[$property->prop ("city24_object_id")] = $property->id ();
			}
		}

		$duplicates = array ();
		$tmp = array ();

		foreach ($imported_object_ids as $city24_id => $aw_oid)
		{
			if (in_array ($city24_id, $tmp))
			{
				$duplicates[] = $city24_id;
			}
			else
			{
				$tmp[] = $city24_id;
			}
		}

		if (count ($duplicates) and (1 != $arr["quiet"]))
		{
			$duplicates = implode (",", $duplicates);
			echo t("NB! Loetletud City24 id-ga objekte on AW objektisüsteemis rohkem kui üks:") . $duplicates . REALESTATE_NEWLINE;
			$status = REALESTATE_IMPORT_ERR4;
		}

		### process data
		foreach ($xml_data as $key => $data)
		{
			switch ($data["tag"])
			{
				case "OBJECTTYPE":
					$this->property_type = $this->index_property_types[$data["value"]];
					break;
			}

			if ($this->end_property_import)
			{ ### finish last processed property import
				if (is_object ($property))
				{
					if (1 != $arr["quiet"]) { echo sprintf (t("Objekt city24 id-ga %s imporditud. AW id: %s. Impordi staatus: %s"), $this->property_data["ID"], $property->id (), $property_status) . REALESTATE_NEWLINE; flush(); }
				}
				else
				{
					if (1 != $arr["quiet"]) { echo sprintf (t("Viga objekti city24 id-ga %s impordil. Veastaatus: %s"), $this->property_data["ID"], $property_status) . REALESTATE_NEWLINE; flush(); }
				}

				if ($property_status)
				{
					$status = REALESTATE_IMPORT_ERR9;
				}

				$this->end_property_import = false;
			}

			if (("ROW" === $data["tag"]) and ("open" === $data["type"]))
			{
				### start property import
				$this->property_data = array ();
				$this->property_data["PILT"] = array ();
			}

			if (is_array ($this->property_data))
			{ ### get&process property data
				switch ($data["tag"])
				{
					case "SISESTATUD":
						// list ($year, $month, $day, $hour, $min, $sec) = sscanf(trim ($xml_data[$key]["value"]),"%u-%u-%u %u:%u:%u");
						// $created = mktime ($hour, $min, $sec, $month, $day, $year);

						// if ( (($created < $last_import) or ($created > $import_time)) and (1 != $arr["import_check_city24id_in_aw"]) and $last_import )
						// {
							// $this->property_data = false;
						// }
						//!!! oleneb mida SISESTATUD t2hendab. kui created siis pole teda yldse vaja. kui modified siis selle j2rgi k2ituda.
						break;

					case "ID":
					case "TEHING":
					case "MAAKOND":
					case "LINN":
					case "LINNAOSA":
					case "VALD":
					case "ASULA":
					case "TANAV":
					case "MAJANR":
					case "MAAKLER_NIMI":
					case "MAAKLER_EMAIL":
					case "MAAKLER_TELEFON":
					case "PRIO":
					case "VALMIDUS":
					case "NAITAMAJANR":
					case "OMANDIVORM":
					case "HIND":
					case "TEHING_MYYGIHIND":
					case "TEHING_ETTEMAKS":
					case "TEHING_KUUYYR":
					case "ASUKOHT_KORRUSEID":
					case "ASUKOHT_KORRUS":
					case "LISAINFO_INFO":
					case "SEISUKORD_SIGNA":
					case "SEISUKORD_TURVAUKS":
					case "SEISUKORD_TREPIKODA":
					case "SEISUKORD_LIFT":
					case "KIRJELDUS_YLDPIND":
					case "KIRJELDUS_TOAD":
					case "KIRJELDUS_AHJUKYTE":
					case "KIRJELDUS_ELKYTE":
					case "KIRJELDUS_DUSH":
					case "KIRJELDUS_KYLMKAPP":
					case "KIRJELDUS_KELDER":
					case "KIRJELDUS_PARKETT":
					case "KIRJELDUS_KAABELTV":
					case "KIRJELDUS_BOILER":
					case "KIRJELDUS_MOOBELVOIM":
					case "KIRJELDUS_MAGAMISTOAD":
					case "KIRJELDUS_VANNITOAD":
					case "KIRJELDUS_KESKKYTE":
					case "KIRJELDUS_MOOBEL":
					case "KIRJELDUS_GARAAZH":
					case "KIRJELDUS_RODU":
					case "KIRJELDUS_PESUMASIN":
					case "KIRJELDUS_TELEFON":
					case "KIRJELDUS_KOOGISUURUS":
					case "KIRJELDUS_TV":
					case "KIRJELDUS_VANN":
					case "KIRJELDUS_SAUN":
					case "KIRJELDUS_KAMIN":
					case "KIRJELDUS_GAASIKYTE":
					case "KIRJELDUS_KOOK":
					case "KIRJELDUS_TELEFONE":
					case "KIRJELDUS_TOOSTUSVOOL":
					case "KIRJELDUS_LOKKANAL":
					case "MYYJA_NIMI":
					case "MYYJA_TELEFON":
					case "MYYJA_EMAIL":
					case "PINNA_TYYP":
					case "KOMMU_ISDN":
					case "KOMMU_ELEKTER":
					case "KOMMU_VESI":
					case "PLIIT":
					case "KRUNT":
					case "KATUS":
					case "KOHANIMI":
					case "MUU_DETAILPLAN":
					case "OTSTARVE_VEEL":
					case "ASUKOHT_KORTERINR":
					case "IKOONI_URL":
					case "KIRJELDUS_GARDEROOB":
					case "KIRJELDUS_TSENTKANAL":
					case "KIRJELDUS_WC":
					case "KOMMU_KANALISATSIOON":
					case "LINN_LINNAOSA":
					case "MUU_KAUGUSTLN":
					case "MUU_OTSTARBEMUUT":
					case "NAITAKORTERINR":
					case "SEISUKORD_EHITUSAASTA":
					case "TEHING_KUURENT":
					case "TEHING_PIIRANGUD":
						$this->property_data[$data["tag"]] = $data["value"];
						break;
				}

				if ("PILT" == substr ($data["tag"], 0, 4))
				{
					$pic_nr = (int) substr ($data["tag"], 4);
					$this->property_data["PILT"][$pic_nr] = $data["value"];
				}
			}

// /* dbg */ continue;
// /* dbg */ exit;

			if (("ROW" === $data["tag"]) and ("close" === $data["type"]) and is_array ($this->property_data))
			{ ### import property to aw
				$property_status = REALESTATE_IMPORT_OK;
				$property = NULL;
				$new_property = true;
				$agent = NULL;
				$section = NULL;
				$this->end_property_import = true;
				$city24_id = (int) $this->property_data["ID"];

				### load existing object corresponding to city24 id
				if (array_key_exists ($city24_id, $imported_object_ids))
				{
					$property = obj ($imported_object_ids[$city24_id]);
					$new_property = false;
				}

				### get agent
				if (!$new_property)
				{
					$agent = $property->get_first_obj_by_reltype("RELTYPE_REALESTATE_AGENT");
				}

				if ($new_property or !is_object ($agent))
				{
					$agent_data = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["MAAKLER_NIMI"]));

					foreach ($employees as $employee_oid => $employee_name)
					{
						if ($agent_data === $employee_name)
						{
							$agent = obj ($employee_oid);
							break;
						}
					}
				}

				### get section by agent name
				if (is_object ($agent))
				{
					$section = $agent->get_first_obj_by_reltype ("RELTYPE_SECTION");
				}
				else
				{
					if (1 != $arr["quiet"])
					{
						echo sprintf (t("Viga importides objekti city24 id-ga %s. Maakleri nimele ei vastanud süsteemis ühkti kasutajat."), $city24_id) . REALESTATE_NEWLINE;
					}

					$property_status = REALESTATE_IMPORT_ERR5;
					continue;
				}

				if (is_object ($section))
				{
					$section = $section->id ();
				}
				else
				{
					if (1 != $arr["quiet"])
					{
						echo sprintf (t("Importides objekti city24 id-ga %s ilmnes: maakleril puudub üksus."), $city24_id) . REALESTATE_NEWLINE;
					}

					$property_status = REALESTATE_IMPORT_ERR6;
					$section = NULL;
				}

				### switch to property owner user
				$connection = new connection();
				$connections = $connection->find(array(
					"to" => $agent->id(),
					"from.class_id" => CL_USER,
					"type" => "RELTYPE_PERSON",
				));

				if (count ($connections))
				{
					$connection = reset ($connections);

					if (is_oid ($connection["from"]))
					{
						$cl_users = get_instance("users");
						$agent_uid = $cl_users->get_uid_for_oid ($connection["from"]);
						aw_switch_user (array ("uid" => $agent_uid));
/* dbg */ if (1 == $_GET["re_import_dbg"]){ echo "kasutaja vahetatud maakleri kasutajaks: [{$agent_uid}]"; }
					}
					else
					{
						if (1 != $arr["quiet"])
						{
							echo sprintf (t("Viga importides objekti city24 id-ga %s: maakleri kasutajaandmetes on viga. Osa infot võib jääda salvestamata."), $city24_id) . REALESTATE_NEWLINE;
						}

						$property_status = REALESTATE_IMPORT_ERR61;
						continue;
					}
				}
				else
				{
					if (1 != $arr["quiet"])
					{
						echo sprintf (t("Viga importides objekti city24 id-ga %s: maakleri kasutajaandmeid ei leitud. Osa infot võib jääda salvestamata."), $city24_id) . REALESTATE_NEWLINE;
					}

					$property_status = REALESTATE_IMPORT_ERR62;
					continue;
				}


				if ($new_property)
				{
					### create new property object in aw
					$oid = $cl_realestate_mgr->add_property (array ("manager" => $manager->id (), "type" => $this->property_type, "section" => $section));

					if (is_oid ($oid))
					{
						$property = obj ($oid);
						$property->set_prop ("realestate_agent1", $agent->id ());
						$property->connect (array (
							"to" => $agent,
							"reltype" => "RELTYPE_REALESTATE_AGENT",
						));

						if (1 != $arr["quiet"])
						{
							echo sprintf (t("Loodud objekt aw oid: %s. (City24 id: %s)"), $property->id (), $city24_id) . REALESTATE_NEWLINE;
						}
					}
					else
					{
						if (1 != $arr["quiet"])
						{
							echo sprintf (t("Viga importides objekti city24 id-ga %s. Objekti loomine ei tagastanud aw objekti id-d."), $city24_id) . REALESTATE_NEWLINE;
						}

						$property_status = REALESTATE_IMPORT_ERR7;
						continue;
					}
				}

				### set general property values
				#### city24_object_id
				$property->set_prop ("city24_object_id", $city24_id);

				#### address
				$address = $property->get_first_obj_by_reltype("RELTYPE_REALESTATE_ADDRESS");

				if (!is_object ($address))
				{
					if (1 != $arr["quiet"])
					{
						echo sprintf (t("Viga importides objekti city24 id-ga %s. Objekt (oid: %s) loodi ilma aadressita."), $city24_id, $property->id ()) . REALESTATE_NEWLINE;
					}

					$property_status = REALESTATE_IMPORT_ERR8;
					continue;
				}


				$maakond = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["MAAKOND"]));
				$linn = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["LINN"]));
				$linnaosa = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["LINNAOSA"]));
				$vald = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["VALD"]));
				$asula = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["ASULA"]));
				$t2nav = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["TANAV"]));
				$maja_nr = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["MAJANR"]));
				$korteri_nr = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["ASUKOHT_KORTERINR"]));
				$kohanimi = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["KOHANIMI"]));

				$lowest_unit_oid = $country->id ();

				if (!empty ($maakond))
				{
					##### maakond
					$maakond_oid = $cl_administrative_structure->add_adminunit (array (
						"id" => $administrative_structure->id (),
						"name" => $maakond,
						"parent" => $country->id (),
						"type" => $maakond_unit_type,
					));
					$lowest_unit_oid = is_oid ($maakond_oid) ? $maakond_oid : $lowest_unit_oid;

					if (!empty ($linn) and is_oid ($maakond_oid))
					{
						##### linn
						$linn_oid = $cl_administrative_structure->add_adminunit (array (
							"id" => $administrative_structure->id (),
							"name" => $linn,
							"parent" => $maakond_oid,
							"type" => $linn_unit_type,
						));
						$lowest_unit_oid = is_oid ($linn_oid) ? $linn_oid : $lowest_unit_oid;

						if (!empty ($linnaosa) and is_oid ($linn_oid))
						{
							##### linnaosa
							$linnaosa_oid = $cl_administrative_structure->add_adminunit (array (
								"id" => $administrative_structure->id (),
								"name" => $linnaosa,
								"parent" => $linn_oid,
								"type" => $linnaosa_unit_type,
							));
							$lowest_unit_oid = is_oid ($linnaosa_oid) ? $linnaosa_oid : $lowest_unit_oid;
						}
					}
					elseif (!empty ($vald) and is_oid ($maakond_oid))
					{
						##### vald
						$vald_oid = $cl_administrative_structure->add_adminunit (array (
							"id" => $administrative_structure->id (),
							"name" => $vald,
							"parent" => $maakond_oid,
							"type" => $vald_unit_type,
						));
						$lowest_unit_oid = is_oid ($vald_oid) ? $vald_oid : $lowest_unit_oid;

						if (is_oid ($vald_oid) and !empty ($asula))
						{
							##### asula
							$asula_oid = $cl_administrative_structure->add_adminunit (array (
								"id" => $administrative_structure->id (),
								"name" => $asula,
								"parent" => $vald_oid,
								"type" => $asula_unit_type,
							));
							$lowest_unit_oid = is_oid ($asula_oid) ? $asula_oid : $lowest_unit_oid;
						}
					}
				}

				##### t2nav
				if (!empty ($t2nav))
				{
					$t2nav_oid = $cl_administrative_structure->add_adminunit (array (
						"id" => $administrative_structure->id (),
						"name" => $t2nav,
						"parent" => $lowest_unit_oid,
						"type" => "street",
					));
					$lowest_unit_oid = is_oid ($t2nav_oid) ? $t2nav_oid : $lowest_unit_oid;
				}

				##### set address
				$address->set_parent ($lowest_unit_oid);
				$address->set_prop ("street_address", $maja_nr);
				$address->set_prop ("apartment", $korteri_nr);
				$address->save ();

				$address_update_status = $cl_address->update_address_array (array ("id" => $address->id ()));
				$address_text = $cl_address->get_address_array (array ("id" => $address->id ()));
				array_pop ($address_text);
				$address_text = array_reverse ($address_text);
				$address_text = implode (", ", $address_text);
				$name = $address_text . " " . $address->prop ("street_address") . ($address->prop ("apartment") ? "-" . $address->prop ("apartment") : "");
				$property->set_name ($name);

				if (!$address_update_status)
				{
					$property_status = REALESTATE_IMPORT_ERR11;

					if (1 != $arr["quiet"])
					{
						echo sprintf (t("Importides objekti city24 id-ga %s ilmnes: viga aadressi uuendamisel. AW oid: %s."), $city24_id, $property->id ()) . REALESTATE_NEWLINE;
					}
				}

				#### transaction_type
				if ($this->changed_transaction_types)
				{
					#### transaction types
					$prop_args = array (
						"clid" => CL_REALESTATE_PROPERTY,
						"name" => "transaction_type",
					);
					list ($options, $NULL, $NULL) = $cl_classificator->get_choices ($prop_args);
					$transaction_types = $options->names();
					$this->changed_transaction_types = false;
				}

				$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["TEHING"]));
				$variable_oid = (int) reset (array_keys ($transaction_types, $value));

				if (!is_oid ($variable_oid) and !empty ($value))
				{
					$variable_oid = $this->add_variable (CL_REALESTATE_PROPERTY, "transaction_type", $value);
					$this->changed_transaction_types = true;
				}

				$property->set_prop ("transaction_type", $variable_oid);

				#### transaction_price
				$value = round ($this->property_data["HIND"], 2);
				$property->set_prop ("transaction_price", $value);

				#### transaction_price2
				$value = round ($this->property_data["TEHING_MYYGIHIND"], 2);
				$property->set_prop ("transaction_price2", $value);

				#### transaction_rent
				$value = round ($this->property_data["TEHING_KUUYYR"], 2);
				$property->set_prop ("transaction_rent", $value);

				#### transaction_constraints
				if ($this->changed_transaction_constraints)
				{
					#### transaction_constraints
					$prop_args = array (
						"clid" => CL_REALESTATE_PROPERTY,
						"name" => "transaction_constraints",
					);
					list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
					$transaction_constraints = $options->names();
					$this->changed_transaction_constraints = false;
				}

				$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["TEHING_PIIRANGUD"]));
				$variable_oid = (int) reset (array_keys ($transaction_constraints, $value));

				if (!is_oid ($variable_oid) and !empty ($value))
				{
					$variable_oid = $this->add_variable (CL_REALESTATE_PROPERTY, "transaction_constraints", $value);
					$this->changed_transaction_constraints = true;
				}

				$property->set_prop ("transaction_constraints", $variable_oid);

				#### transaction_down_payment
				$property->set_prop ("transaction_down_payment", $this->property_data["TEHING_ETTEMAKS"]);

				#### seller data
				$client = $property->get_first_obj_by_reltype("RELTYPE_REALESTATE_SELLER");
				$clients = array ();

				if (!is_object ($client) and !empty ($this->property_data["MYYJA_NIMI"]))
				{
					$duplicate_client = false;

					$seller_firstname = strtok ($this->property_data["MYYJA_NIMI"], " ");
					$seller_lastname = strtok (" ");

					##### search for existing client by name
					$list = new object_list (array (
						"class_id" => CL_CRM_PERSON,
						"parent" => array ($manager->prop ("clients_folder")),
						"firstname" => array ($seller_firstname),
						"lastname" => array ($seller_lastname),
					));

					if ($list->count ())
					{
						$client = $list->begin ();

						if ($list->count () > 1)
						{
							if (1 != $arr["quiet"])
							{
								$list = $list->arr ();

								foreach ($list as $o)
								{
									$clients[] = html::href(array(
										"url" => $this->mk_my_orb ("change", array (
											"id" => $o->id(),
										), "crm_person"),
										"target" => "_blank",
										"caption" => $o->id (),
									));
								}

 								$clients = implode (" ", $clients);
								echo sprintf (t("Importides objekti city24 id-ga %s ilmnes: antud nimega kliente on rohkem kui üks. Ei tea millist valida. AW oid: %s. Leitud kliendid: %s"), $city24_id, $property->id (), $clients) . REALESTATE_NEWLINE;
							}

							$property_status = REALESTATE_IMPORT_ERR10;
						}
					}
					else
					{
						##### create seller
						$client = new object ();
						$client->set_class_id (CL_CRM_PERSON);
						$client->set_parent ($manager->prop ("clients_folder"));
						$client->save ();

						###### create seller email
						$email = new object ();
						$email->set_class_id (CL_ML_MEMBER);
						$email->set_parent ($manager->prop ("clients_folder"));
						$email->save ();
						$client->connect (array (
							"to" => $email,
							"reltype" => "RELTYPE_EMAIL",
						));

						###### create seller phone
						$phone = new object ();
						$phone->set_class_id (CL_CRM_PHONE);
						$phone->set_parent ($manager->prop ("clients_folder"));
						$phone->save ();
						$client->connect (array (
							"to" => $phone,
							"reltype" => "RELTYPE_PHONE",
						));
					}

					##### save seller data
					$client->set_prop ("firstname", $seller_firstname);
					$client->set_prop ("lastname", $seller_lastname);
					$client->set_name ($seller_firstname . " " . $seller_lastname);

					$email = $client->get_first_obj_by_reltype ("RELTYPE_EMAIL");
					$phone = $client->get_first_obj_by_reltype ("RELTYPE_PHONE");
					$email->set_prop ("mail", $this->property_data["MYYJA_EMAIL"]);
					$phone->set_name ($this->property_data["MYYJA_TELEFON"]);

					$client->save ();
					$email->save ();
					$phone->save ();

					$property->connect (array (
						"to" => $client,
						"reltype" => "RELTYPE_REALESTATE_SELLER",
					));
				}

				#### priority
				if ($this->changed_priorities)
				{
					#### priorities
					$prop_args = array (
						"clid" => CL_REALESTATE_PROPERTY,
						"name" => "priority",
					);
					list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
					$options = $options->arr ();
					$priorities = array ();

					foreach ($options as $variable)
					{
						$priorities[$variable->comment ()] = $variable->id ();
					}

					$this->changed_priorities = false;
				}

				$altvalue = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["PRIO"]));
				$variable_oid = $priorities[$altvalue];

				if (!is_oid ($variable_oid) and !empty ($value))
				{
					$variable_oid = $this->add_variable (CL_REALESTATE_PROPERTY, "priority", $value);
					$this->changed_priorities = true;
				}

				$property->set_prop ("priority", $variable_oid);

				#### show_house_number_on_web
				$value = (int) (bool) strstr ($this->property_data["NAITAMAJANR"], "Y");
				$property->set_prop ("show_house_number_on_web", $value);

				#### additional_info
				// $value = iconv("iso-8859-4", "UTF-8", $this->property_data["LISAINFO_INFO"]);
				$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, $this->property_data["LISAINFO_INFO"]);
				$property->set_prop ("additional_info_et", $value);

				#### picture_icon
				$value = $this->property_data["IKOONI_URL"];
				$property->set_prop ("picture_icon", $value);

				#### pictures
				$list = new object_list ($property->connections_from(array(
					"type" => "RELTYPE_REALESTATE_PICTURE",
					"class_id" => CL_IMAGE,
				)));
				$list = $list->arr();

				##### remove removed
				foreach ($list as $image)
				{
					if (!in_array ($image->meta ("picture_city24_id"), $this->property_data["PILT"]))
					{
						$file = $image->prop ("file");
						unlink ($file);
						$image->delete ();
					}
					else
					{
						$existing_pictures[] = $image->meta ("picture_city24_id");
					}
				}

				##### add new pictures
				ksort ($this->property_data["PILT"]);

				foreach ($this->property_data["PILT"] as $key => $picture_id)
				{
					if (!in_array ($picture_id, $existing_pictures))
					{
						$image_url = "http://www.city24.ee/MEDIA/PICTURE/PICTURE_{$picture_id}.jpeg";
						$cl_file = get_instance(CL_FILE);
						$imagedata = file_get_contents ($image_url);
						$file = $cl_file->_put_fs(array(
							"type" => "image/jpeg",
							"content" => $imagedata,
						));

						$picture =& new object ();
						$picture->set_class_id (CL_IMAGE);
						$picture->set_parent ($property->id ());
						$picture->set_status(STAT_ACTIVE);
						$picture->set_ord ($key);
						$picture->set_name ($property->id () . "_" . t(" pilt ") . $key);
						$picture->set_prop("file", $file);
						$picture->set_meta("picture_city24_id", $picture_id);
						$picture->save ();
						$property->connect (array (
							"to" => $picture,
							"reltype" => "RELTYPE_REALESTATE_PICTURE",
						));
					}
				}


				### set type specific property values
				switch ($this->property_type)
				{
					case "house":
					case "rowhouse":
					case "cottage":
					case "housepart":
					case "apartment":
					case "commercial":
					case "garage":
						#### total_floor_area
						$value = round ($this->property_data["KIRJELDUS_YLDPIND"], 2);
						$property->set_prop ("total_floor_area", $value);

						#### has_alarm_installed
						$value = (int) (bool) strstr ($this->property_data["SEISUKORD_SIGNA"], "Y");
						$property->set_prop ("has_alarm_installed", $value);

						#### condition
						if ($this->changed_conditions)
						{
							#### conditions
							$prop_args = array (
								"clid" => CL_REALESTATE_HOUSE,
								"name" => "condition",
							);
							list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
							$conditions = $options->names();
							$this->changed_conditions = false;
						}

						$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["VALMIDUS"]));
						$variable_oid = (int) reset (array_keys ($conditions, $value));

						if (!is_oid ($variable_oid) and !empty ($value))
						{
							$variable_oid = $this->add_variable (CL_REALESTATE_HOUSE, "condition", $value);
							$this->changed_conditions = true;
						}

						$property->set_prop ("condition", $variable_oid);
						break;
				}

				switch ($this->property_type)
				{
					case "house":
					case "rowhouse":
					case "cottage":
					case "housepart":
					case "apartment":
					case "commercial":
						#### number_of_storeys
						$value = (int) $this->property_data["ASUKOHT_KORRUSEID"];
						$property->set_prop ("number_of_storeys", $value);

						#### number_of_rooms
						$value = (int) $this->property_data["KIRJELDUS_TOAD"];
						$property->set_prop ("number_of_rooms", $value);

						#### has_central_heating
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_KESKKYTE"], "Y");
						$property->set_prop ("has_central_heating", $value);

						#### has_electric_heating
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_ELKYTE"], "Y");
						$property->set_prop ("has_electric_heating", $value);

						#### has_gas_heating
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_GAASIKYTE"], "Y");
						$property->set_prop ("has_gas_heating", $value);

						#### has_shower
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_DUSH"], "Y");
						$property->set_prop ("has_shower", $value);

						#### has_refrigerator
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_KYLMKAPP"], "Y");
						$property->set_prop ("has_refrigerator", $value);

						#### has_furniture
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_MOOBEL"], "Y");
						$property->set_prop ("has_furniture", $value);

						#### has_furniture_option
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_MOOBELVOIM"], "Y");
						$property->set_prop ("has_furniture_option", $value);
						break;
				}

				switch ($this->property_type)
				{
					case "house":
					case "rowhouse":
					case "cottage":
					case "housepart":
					case "apartment":
						#### year_built
						$value = (int) $this->property_data["SEISUKORD_EHITUSAASTA"];
						$property->set_prop ("year_built", $value);

						#### legal_status
						if ($this->changed_legal_statuses)
						{
							#### legal_statuses
							$prop_args = array (
								"clid" => CL_REALESTATE_HOUSE,
								"name" => "legal_status",
							);
							list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
							$legal_statuses = $options->names();
							$this->changed_legal_statuses = false;
						}

						$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["OMANDIVORM"]));
						$variable_oid = (int) reset (array_keys ($legal_statuses, $value));

						if (!is_oid ($variable_oid) and !empty ($value))
						{
							$variable_oid = $this->add_variable (CL_REALESTATE_HOUSE, "legal_status", $value);
							$this->changed_legal_statuses = true;
						}

						$property->set_prop ("legal_status", $variable_oid);

						#### number_of_bedrooms
						$value = (int) $this->property_data["KIRJELDUS_MAGAMISTOAD"];
						$property->set_prop ("number_of_bedrooms", $value);

						#### number_of_bathrooms
						$value = (int) $this->property_data["KIRJELDUS_VANNITOAD"];
						$property->set_prop ("number_of_bathrooms", $value);

						#### has_wardrobe
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_GARDEROOB"], "Y");
						$property->set_prop ("has_wardrobe", $value);

						#### has_separate_wc
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_WC"], "Y");
						$property->set_prop ("has_separate_wc", $value);

						#### has_garage
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_GARAAZH"], "Y");
						$property->set_prop ("has_garage", $value);

						#### has_sauna
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_SAUN"], "Y");
						$property->set_prop ("has_sauna", $value);

						#### has_balcony
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_RODU"], "Y");
						$property->set_prop ("has_balcony", $value);

						#### has_wood_heating
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_AHJUKYTE"], "Y");
						$property->set_prop ("has_wood_heating", $value);

						#### has_cable_tv
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_KAABELTV"], "Y");
						$property->set_prop ("has_cable_tv", $value);

						#### has_phone
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_TELEFON"], "Y");
						$property->set_prop ("has_phone", $value);

						#### has_tv
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_TV"], "Y");
						$property->set_prop ("has_tv", $value);

						#### has_bath
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_VANN"], "Y");
						$property->set_prop ("has_bath", $value);

						#### has_boiler
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_BOILER"], "Y");
						$property->set_prop ("has_boiler", $value);

						#### has_washing_machine
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_PESUMASIN"], "Y");
						$property->set_prop ("has_washing_machine", $value);

						#### has_parquet
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_PARKETT"], "Y");
						$property->set_prop ("has_parquet", $value);
						break;
				}

				switch ($this->property_type)
				{
					case "apartment":
					case "commercial":
						#### floor
						$value = (int) $this->property_data["ASUKOHT_KORRUS"];
						$property->set_prop ("floor", $value);

						#### has_lift
						$value = (int) (bool) strstr ($this->property_data["SEISUKORD_LIFT"], "Y");
						$property->set_prop ("has_lift", $value);
						break;
				}

				switch ($this->property_type)
				{
					case "house":
					case "rowhouse":
					case "cottage":
					case "housepart":
						#### property_area
						$value = round ($this->property_data["KRUNT"]);
						$property->set_prop ("property_area", $value);

						#### has_cellar
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_KELDER"], "Y");
						$property->set_prop ("has_cellar", $value);

						#### has_industrial_voltage
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_TOOSTUSVOOL"], "Y");
						$property->set_prop ("has_industrial_voltage", $value);

						#### has_local_sewerage
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_LOKKANAL"], "Y");
						$property->set_prop ("has_local_sewerage", $value);

						#### has_central_sewerage
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_TSENTKANAL"], "Y");
						$property->set_prop ("has_central_sewerage", $value);

						#### roof_type
						if ($this->changed_roof_types)
						{
							#### roof_types
							$prop_args = array (
								"clid" => CL_REALESTATE_HOUSE,
								"name" => "roof_type",
							);
							list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
							$roof_types = $options->names();
							$this->changed_roof_types = false;
						}

						$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["KATUS"]));
						$variable_oid = (int) reset (array_keys ($roof_types, $value));

						#### has_fireplace
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_KAMIN"], "Y");
						$property->set_prop ("has_fireplace_heating", $value);

						if (!is_oid ($variable_oid) and !empty ($value))
						{
							$variable_oid = $this->add_variable (CL_REALESTATE_HOUSE, "roof_type", $value);
							$this->changed_roof_types = true;
						}

						$property->set_prop ("roof_type", $variable_oid);
						break;

					case "apartment":
						#### show_apartment_number
						$value = (int) (bool) strstr ($this->property_data["NAITAKORTERINR"], "Y");
						$property->set_prop ("show_apartment_number", $value);

						#### is_middle_floor
						$value = 0;
						$floors = (int) $this->property_data["ASUKOHT_KORRUSEID"];
						$floor = (int) $this->property_data["ASUKOHT_KORRUS"];

						if (
							($floors) and
							($floor) and
							($floors - $floor) and
							($floor != 1) and
							($floors > 2)
						)
						{
							$value = 1;
						}

						$property->set_prop ("is_middle_floor", $value);

						#### has_hallway_locked
						$value = (int) (bool) strstr ($this->property_data["SEISUKORD_TREPIKODA"], "Y");
						$property->set_prop ("has_hallway_locked", $value);

						#### kitchen_area
						$value = round ($this->property_data["KIRJELDUS_KOOGISUURUS"], 1);
						$property->set_prop ("kitchen_area", $value);

						#### has_cellar
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_KELDER"], "Y");
						$property->set_prop ("has_cellar", $value);

						#### has_fireplace
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_KAMIN"], "Y");
						$property->set_prop ("has_fireplace", $value);

						#### stove_type
						if ($this->changed_stove_types)
						{
							#### stove_types
							$prop_args = array (
								"clid" => CL_REALESTATE_APARTMENT,
								"name" => "stove_type",
							);
							list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
							$stove_types = $options->names();
							$this->changed_stove_types = false;
						}

						$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["PLIIT"]));
						$variable_oid = (int) reset (array_keys ($stove_types, $value));

						if (!is_oid ($variable_oid) and !empty ($value))
						{
							$variable_oid = $this->add_variable (CL_REALESTATE_APARTMENT, "stove_type", $value);
							$this->changed_stove_types = true;
						}

						$property->set_prop ("stove_type", $value);

						#### has_security_door
						$value = (int) (bool) strstr ($this->property_data["SEISUKORD_TURVAUKS"], "Y");
						$property->set_prop ("has_security_door", $value);
						break;

					case "commercial":
						#### transaction_monthly_rent
						$value = round ($this->property_data["TEHING_KUURENT"], 2);
						$property->set_prop ("transaction_monthly_rent", $value);

						#### usage_purpose
						if ($this->changed_usage_purposes)
						{
							#### usage purposes
							$prop_args = array (
								"clid" => CL_REALESTATE_COMMERCIAL,
								"name" => "usage_purpose",
							);
							list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
							$usage_purposes = $options->names();
							$this->changed_usage_purposes = false;
						}

						$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["PINNA_TYYP"]));
						$variable_oid = (int) reset (array_keys ($usage_purposes, $value));

						if (!is_oid ($variable_oid) and !empty ($value))
						{
							$variable_oid = $this->add_variable (CL_REALESTATE_COMMERCIAL, "usage_purpose", $value);
							$this->changed_usage_purposes = true;
						}

						$property->set_prop ("usage_purpose", $variable_oid);

						#### has_kitchen
						$value = (int) (bool) strstr ($this->property_data["KIRJELDUS_KOOK"], "Y");
						$property->set_prop ("has_kitchen", $value);

						#### has_internet
						$value = (int) (bool) strstr ($this->property_data["XXXXXXX"], "Y");
						// $property->set_prop ("has_internet", $value);//!!! puudub?

						#### has_isdn
						$value = (int) (bool) strstr ($this->property_data["KOMMU_ISDN"], "Y");
						$property->set_prop ("has_isdn", $value);

						#### number_of_phone_lines
						$value = (int) $this->property_data["KIRJELDUS_TELEFONE"];
						$property->set_prop ("number_of_phone_lines", $value);
						break;

					case "land":
						#### distance_from_tallinn
						$value = (int) $this->property_data["MUU_KAUGUSTLN"];
						$property->set_prop ("distance_from_tallinn", $value);

						#### land_use
						if ($this->changed_land_uses)
						{
							#### land_uses
							$prop_args = array (
								"clid" => CL_REALESTATE_LAND,
								"name" => "land_use",
							);
							list ($options, $NULL, $NULL) = $cl_classificator->get_choices($prop_args);
							$land_uses = $options->names();
							$this->changed_land_uses = false;
						}

						$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["OTSTARVE_VEEL"]));
						$variable_oid = (int) reset (array_keys ($land_uses, $value));

						if (!is_oid ($variable_oid) and !empty ($value))
						{
							$variable_oid = $this->add_variable (CL_REALESTATE_LAND, "land_use", $value);
							$this->changed_land_uses = true;
						}

						$property->set_prop ("land_use", $variable_oid);

						#### land_use_2
						// $value = iconv(REALESTATE_IMPORT_CHARSET_FROM, REALESTATE_IMPORT_CHARSET_TO, trim ($this->property_data["OTSTARVE_VEEL"]));//!!! city-s pole kaht maa otstarvet?
						// $variable_oid = (int) reset (array_keys ($land_uses, $value));

						// if (!is_oid ($variable_oid) and !empty ($value))
						// {
							// $variable_oid = $this->add_variable (CL_REALESTATE_LAND, "land_use", $value);
						// }

						// $property->set_prop ("land_use_2", $variable_oid);

						#### is_changeable
						$value = (int) (bool) strstr ($this->property_data["MUU_OTSTARBEMUUT"], "Y");
						$property->set_prop ("is_changeable", $value);

						#### has_electricity
						$value = (int) (bool) strstr ($this->property_data["KOMMU_ELEKTER"], "Y");
						$property->set_prop ("has_electricity", $value);

						#### has_sewerage
						$value = (int) (bool) strstr ($this->property_data["KOMMU_KANALISATSIOON"], "Y");
						$property->set_prop ("has_sewerage", $value);

						#### has_water
						$value = (int) (bool) strstr ($this->property_data["KOMMU_VESI"], "Y");
						$property->set_prop ("has_water", $value);

						#### has_zoning_ordinance
						$value = (int) (bool) strstr ($this->property_data["MUU_DETAILPLAN"], "Y");
						$property->set_prop ("has_zoning_ordinance", $value);
						break;
				}

				$property->save ();
			}
		}

		$additional_languages = array (
			"ENG",
			"RUS",
			"FIN",
		);

		$lang_codes = array (
			"ENG" => "en",
			"RUS" => "ru",
			"FIN" => "fi",
		);

		foreach ($additional_languages as $lang_name)
		{
			$tmp_import_url = str_replace ("lang", "tmpvariable39903", $import_url);

			$import_url = str_replace ("tmpvariable39903", "lang", aw_url_change_var ("tmpvariable39903", $lang_name, $tmp_import_url));
			$xml = file_get_contents ($import_url);
			$parser = xml_parser_create();
			xml_parse_into_struct($parser, $xml, $xml_data, $xml_index);
			xml_parser_free($parser);
			$this->end_property_import = false;

			foreach ($xml_data as $key => $data)
			{
				if ($this->end_property_import)
				{ ### finish last processed property import
					if (is_object ($property))
					{
						if (1 != $arr["quiet"]) { echo sprintf (t("Lisainfo (%s) objektile city24 id-ga %s imporditud. AW id: %s. Impordi staatus: %s"), $lang_name, $this->property_data["ID"], $property->id (), $property_status) . REALESTATE_NEWLINE; }
					}
					else
					{
						if (1 != $arr["quiet"]) { echo sprintf (t("Viga objekti city24 id-ga %s lisainfo (%s) impordil. Veastaatus: %s"), $this->property_data["ID"], $lang_name, $property_status) . REALESTATE_NEWLINE; }
					}

					if ($property_status)
					{
						$status = REALESTATE_IMPORT_ERR9;
					}

					$this->end_property_import = false;
				}

				if (("ROW" === $data["tag"]) and ("open" === $data["type"]))
				{
					### start property additional info import
					$this->property_data = array ();
				}

				if (is_array ($this->property_data))
				{ ### get&process property data
					switch ($data["tag"])
					{
						case "ID":
						case "LISAINFO_INFO":
							$this->property_data[$data["tag"]] = $data["value"];
							break;
					}
				}

				if (("ROW" === $data["tag"]) and ("close" === $data["type"]) and is_array ($this->property_data))
				{ ### import property additional info to aw
					$property_status = REALESTATE_IMPORT_OK;
					$this->end_property_import = true;
					$city24_id = (int) $this->property_data["ID"];

					### load existing object corresponding to city24 id
					$list = new object_list (array (
						"class_id" => $realestate_classes,
						"parent" => $realestate_folders,
						"city24_object_id" => $city24_id,
					));
					$property = $list->begin ();

					if (!is_object ($property))
					{
						if (1 != $arr["quiet"])
						{
							echo sprintf (t("Viga importides lisainfot (%s) objekti city24 id-ga %s: vastavat aw objekti ei leitud."), $lang_name, $city24_id) . REALESTATE_NEWLINE;
						}

						$property_status = REALESTATE_IMPORT_ERR15;
						continue;
					}

					### switch to property owner user
					$agent = $property->get_first_obj_by_reltype("RELTYPE_REALESTATE_AGENT");
					$connection = new connection();
					$connections = $connection->find(array(
						"to" => $agent->id(),
						"from.class_id" => CL_USER,
						"type" => "RELTYPE_PERSON",
					));

					if (count ($connections))
					{
						$connection = reset ($connections);

						if (is_oid ($connection["from"]))
						{
							$cl_users = get_instance("users");
							$agent_uid = $cl_users->get_uid_for_oid ($connection["from"]);
							aw_switch_user (array ("uid" => $agent_uid));
						}
						else
						{
							if (1 != $arr["quiet"])
							{
								echo sprintf (t("Viga importides lisainfot (%s) objektile city24 id-ga %s: maakleri kasutajaandmetes on viga. Osa infot võib jääda salvestamata."), $lang_name, $city24_id) . REALESTATE_NEWLINE;
							}

							$property_status = REALESTATE_IMPORT_ERR12;
							continue;
						}
					}
					else
					{
						if (1 != $arr["quiet"])
						{
							echo sprintf (t("Viga importides lisainfot (%s) objekti city24 id-ga %s: maakleri kasutajaandmeid ei leitud. Osa infot võib jääda salvestamata."), $lang_name, $city24_id) . REALESTATE_NEWLINE;
						}

						$property_status = REALESTATE_IMPORT_ERR13;
						continue;
					}

					### set property values
					#### additional_info
					$lang_code = $lang_codes[$lang_name];
					$list = new object_list(array(
						"class_id" => CL_LANGUAGE,
						"lang_acceptlang" => $lang_code,
						"site_id" => array(),
						"lang_id" => array(),
					));
					$language = $list->begin ();

					if (is_object ($language))
					{
						$charset = $language->prop("lang_charset");
						$value = iconv(REALESTATE_IMPORT_CHARSET_FROM, $charset, $this->property_data["LISAINFO_INFO"]);
						$property->set_prop ("additional_info_{$lang_code}", $value);
					}
					else
					{
						if (1 != $arr["quiet"])
						{
							echo sprintf (t("Viga importides lisainfot (%s) objekti city24 id-ga %s ilmnes: keeleobjekti ei leitud."), $lang_name, $city24_id) . REALESTATE_NEWLINE;
						}

						$property_status = REALESTATE_IMPORT_ERR14;
					}

					$property->save ();
				}
			}
		}

		if (1 != $arr["quiet"]) { echo t("Import tehtud.") . REALESTATE_NEWLINE; }
		$manager->set_cache_dirty (true);
		return $status;
	}

	function add_variable ($clid, $name, $value)
	{
		$ot = get_instance(CL_OBJECT_TYPE);
		$ff = $ot->get_obj_for_class(array(
			"clid" => $clid,
		));
		$oft = obj ($ff);
		$clf = $oft->meta("classificator");
		$clf_type = $oft->meta("clf_type");
		$use_type = $clf_type[$name];
		$ofto = obj ($clf[$name]);
		$parent = $ofto->id ();

		if (is_oid ($parent))
		{
			$no = new object;
			$no->set_class_id(CL_META);
			$no->set_status(STAT_ACTIVE);
			$no->set_parent($parent);
			$no->set_name($value);
			$no->save();

			return $no->id ();
		}
		else
		{
			echo sprintf (t("Viga: muutuja %s klassil id-ga %s defineerimata."), $name, $clid) . REALESTATE_NEWLINE;
			return false;
		}
	}
}
?>
