<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_property.aw,v 1.2 2005/11/07 16:49:59 ahti Exp $
// realestate_property.aw - Kinnisvaraobjekt
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_NEW, CL_REALESTATE_PROPERTY, on_create)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_REALESTATE_PROPERTY, on_delete)

@classinfo syslog_type=ST_REALESTATE_PROPERTY relationmgr=yes no_status=1 trans=1

@tableinfo realestate_property index=oid master_table=objects master_index=oid

@groupinfo grp_main caption="&Uuml;ldandmed ja asukoht"
@groupinfo grp_detailed caption="Kirjeldus"
@groupinfo grp_additional_info caption="Lisainfo"
@groupinfo grp_photos caption="Pildid"
@groupinfo grp_map caption="Kaart"


@property header type=text store=no no_caption=1 group=grp_main,grp_detailed,grp_additional_info,grp_photos,grp_map

@default table=objects
@default group=grp_main
	@property property_toolbar type=toolbar store=no no_caption=1

	@property oid type=text
	@caption Objekti id AutomatWeb-is

	@property realestate_manager type=hidden field=meta method=serialize

	@property city24_object_id type=text field=meta method=serialize
	@caption Objekti id City24 andmebaasis

	@property is_visible type=checkbox ch_value=1 field=meta method=serialize
	@caption Nähtav

	@property is_archived type=checkbox ch_value=1 field=meta method=serialize
	@caption Arhiveeritud

	@property title1 type=text store=no subtitle=1
	@caption Objekti aadress
		@property address_connection type=releditor reltype=RELTYPE_REALESTATE_ADDRESS rel_id=first editonly=1 props=location_country,location,postal_code,street_address,po_box,apartment
		@caption Aadress

		@property address_text type=hidden table=realestate_property

	@property title2 type=text store=no subtitle=1
	@caption Tehingu andmed
		@property transaction_type type=classificator table=realestate_property
		@caption Tehingu t&uuml;&uuml;p

		@property transaction_price type=textbox table=realestate_property
		@caption Hind

		@property transaction_price2 type=textbox field=meta method=serialize
		@caption Müügihind

		@property transaction_rent type=textbox field=meta method=serialize
		@caption Kuuüür

		@property transaction_constraints type=classificator table=realestate_property
		@caption Piirangud

		@property transaction_down_payment type=textbox field=meta method=serialize
		@caption Ettemaks

		@property transaction_date type=date_select field=meta method=serialize default=-1
		@caption Tehingu kuupäev

		@property transaction_closed type=checkbox field=meta method=serialize ch_value=1
		@caption Tehing sõlmitud


	@property title3 type=text store=no subtitle=1
	@caption M&uuml;&uuml;ja andmed
		@property seller_search type=text store=no
		@caption Müüja

		@property seller type=releditor reltype=RELTYPE_REALESTATE_SELLER rel_id=first editonly=1 props=firstname,lastname,personal_id,gender,birthday,phone,email,comment,notes

		@property seller_heard_from type=classificator field=meta method=serialize
		@caption Infoallikas

	@property title31 type=text store=no subtitle=1
	@caption Ostja andmed
		@property buyer_search type=text store=no
		@caption Ostja

		@property buyer type=releditor reltype=RELTYPE_REALESTATE_BUYER rel_id=first editonly=1 props=firstname,lastname,personal_id,gender,birthday,phone,email,comment,notes

		@property buyer_heard_from type=classificator field=meta method=serialize
		@caption Infoallikas


	@property title5 type=text store=no subtitle=1
	@caption Tänukiri
		@property appreciation_note_date type=date_select field=meta method=serialize default=-1
		@caption Tänukirja saatmise kuupäev

		@property appreciation_note_type type=classificator field=meta method=serialize
		@caption Tänukirja tüüp



	@property title4 type=text store=no subtitle=1
	@caption Lisaandmed
		@property realestate_agent1 type=relpicker reltype=RELTYPE_REALESTATE_AGENT clid=CL_CRM_PERSON automatic=1 table=realestate_property
		@caption Maakler 1

		@property realestate_agent2 type=relpicker reltype=RELTYPE_REALESTATE_AGENT2 clid=CL_CRM_PERSON automatic=1 table=realestate_property
		@caption Maakler 2

		@property weeks_valid_for type=chooser default=12 field=meta method=serialize
		@caption Kehtib (n&auml;dalat)

		@property visible_to type=classificator table=realestate_property
		@caption N&auml;htav

		@property priority type=classificator table=realestate_property
		@caption Prioriteet

		@property show_on_webpage type=checkbox ch_value=1 field=meta method=serialize default=1
		@caption N&auml;ita firma kodulehel

		@property show_house_number_on_web type=checkbox ch_value=1 field=meta method=serialize
		@caption N&auml;ita majanumbrit kodulehel

		@property special_homepage type=textbox field=meta method=serialize
		@caption Objekti koduleht

		@property special_status type=classificator table=realestate_property
		@caption Eristaatus (eripakkumiste kuvamiseks veebis)

		@property project type=relpicker reltype=RELTYPE_REALESTATE_PROJECT clid=CL_PROJECT automatic=1 field=meta method=serialize
		@caption Projekt


@default group=grp_detailed


@default group=grp_additional_info
	@property additional_info_et type=textarea rows=5 cols=74 field=meta method=serialize
	@caption Lisainfo EST

	@property additional_info_en type=textarea rows=5 cols=74 field=meta method=serialize
	@caption Lisainfo ENG

	@property additional_info_fi type=textarea rows=5 cols=74 field=meta method=serialize
	@caption Lisainfo FIN

	@property additional_info_ru type=textarea rows=5 cols=74 field=meta method=serialize
	@caption Lisainfo RUS

	@property keywords_et type=textarea rows=5 cols=74 field=meta method=serialize
	@caption M&auml;rks&otilde;nad

	@property translation type=translator store=no


@default group=grp_photos
	@property pictures type=releditor reltype=RELTYPE_REALESTATE_PICTURE mode=manager props=name,file,alt table_fields=name,created field=meta method=serialize
	@caption Pildid

	@property picture_icon type=text field=meta method=serialize
	@caption Ikooni url

@default group=grp_map
	@property map_create type=text store=no
	@caption Loo kaart (salvestatakse kaardi pilt ja asukoha andmed)

	// @property map_show type=text field=meta method=serialize
	// @caption Kaart

	@property map_url type=text field=meta method=serialize
	@caption Kaart

	@property map_description type=textarea rows=5 cols=74 field=meta method=serialize
	@caption Kaardi kirjeldus

	@property map_point type=hidden field=meta method=serialize
	@property map_area type=hidden field=meta method=serialize
	@property map_id type=hidden field=meta method=serialize

// --------------- RELATION TYPES ---------------------

@reltype REALESTATE_ADDRESS value=1 clid=CL_ADDRESS
@caption Aadress

@reltype REALESTATE_SELLER value=2 clid=CL_CRM_PERSON
@caption Klient (Müüja)

@reltype REALESTATE_BUYER value=7 clid=CL_CRM_PERSON
@caption Klient (Ostja)

@reltype REALESTATE_AGENT value=3 clid=CL_CRM_PERSON
@caption Maakler

@reltype REALESTATE_AGENT2 value=6 clid=CL_CRM_PERSON
@caption Maakler 2

@reltype REALESTATE_PROJECT value=4 clid=CL_PROJECT
@caption Projekt

@reltype REALESTATE_PICTURE value=5 clid=CL_IMAGE
@caption Pilt

*/

/*

CREATE TABLE `realestate_property` (
	`oid` int(11) NOT NULL default '0',
	`transaction_type` int(11) unsigned default NULL,
	`transaction_constraints` int(11) unsigned default NULL,
	`transaction_price` float(12,2) unsigned default NULL,
	`visible_to` int(11) unsigned default NULL,
	`realestate_agent1` int(11) unsigned default NULL,
	`realestate_agent2` int(11) unsigned default NULL,
	`priority` int(11) unsigned default NULL,
	`special_status` int(11) unsigned default NULL,
	`usage_purpose` int(11) unsigned default NULL,
	`condition` int(11) unsigned default NULL,
	`legal_status` int(11) unsigned default NULL,
	`land_use` int(11) unsigned default NULL,
	`land_use_2` int(11) unsigned default NULL,
	`roof_type` int(11) unsigned default NULL,
	`total_floor_area` float(7,2) unsigned default NULL,
	`number_of_rooms` tinyint unsigned default NULL,
	`is_middle_floor` bit default '0',
	`address_text` char(255) default NULL,

	PRIMARY KEY  (`oid`),
	UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

ALTER TABLE `realestate_property` ADD `roof_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `facade_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `building_society_state` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `hallway_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `privatization` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `kitchen_type` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `kitchen_walls` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `kitchen_floor` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `stove_type` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `kitchen_furniture` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `kitchen_furniture_option` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `kitchen_furniture_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `kitchenware_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `room_walls` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `room_floors` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `lavatories_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `lavatory_equipment_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `windows_type` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `doors_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `finishing_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `parquet_type` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `bearing_walls` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `interior_walls` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `interior_ceilings` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `foundation_type` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `exterior_finishing` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `location_description` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `fee_payer` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `ownership_type` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `communications_electricity` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `communications_water` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `communications_sewerage` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `building_type` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `quality_class` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `technical_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `finishing` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `electricity_manf_type` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `electricity_meter_type` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `plumbing_condition` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `apartment_situation` INT(11) UNSIGNED AFTER `roof_type`;
ALTER TABLE `realestate_property` ADD `loading_facilities` INT(11) UNSIGNED AFTER `roof_type`;

*/

define ("REALESTATE_NF_DEC", 2);
define ("REALESTATE_NF_POINT", ",");
define ("REALESTATE_NF_SEP", " ");

class realestate_property extends class_base
{
	var $float_types = array (
		"transaction_price",
		"transaction_price2",
		"transaction_down_payment",
		"transaction_rent",
		"total_floor_area",
		"estate_price_sqmeter",
		"transaction_sqmeter_price",
		"transaction_rent_sqmeter",
		"living_area",
		"usable_area",
		"montlhy_expenses",
		"property_area",
		"transaction_price_total",
		"transaction_selling_price",
		"transaction_rent_total",
		"estate_price_total",
		"transaction_additional_costs",
		"transaction_monthly_rent",
		"transaction_broker_fee",
		"heatable_area",
		"kitchen_area",
	);

	var $propnames_starting_with_acronym = array (
		"has_separate_wc",
	);


/* classbase methods */
	function realestate_property()
	{
		$this->init(array(
			"tpldir" => "applications/realestate_management/realestate_property",
			"clid" => CL_REALESTATE_PROPERTY
		));
	}

	function callback_on_load ($arr)
	{
		if (is_oid ($arr["request"]["id"]))
		{
			$this_object = obj ($arr["request"]["id"]);
			$this->address = $this_object->get_first_obj_by_reltype ("RELTYPE_REALESTATE_ADDRESS");

			if ($this->can ("view", $this_object->prop ("realestate_manager")))
			{
				$this->manager = obj ($this_object->prop ("realestate_manager"));
			}
			else
			{
				echo t("Kinnisvarahalduskeskkond objekti jaoks määramata või puudub juurdepääsuõigus");
			}
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch($prop["name"])
		{
			case "weeks_valid_for":
				$prop["options"] = array (2,4,6,8,10,12);
				break;

			case "transaction_price":
			case "transaction_price2":
			case "transaction_down_payment":
			case "transaction_rent":
			case "total_floor_area":
				$prop["value"] = number_format ($prop["value"], REALESTATE_NF_DEC, REALESTATE_NF_POINT, REALESTATE_NF_SEP);
				break;

			case "property_toolbar":
				$this->_property_toolbar ($arr);
				break;

			case "header":
				$classes = aw_ini_get("classes");
				$prop["value"] = '<div style="padding: .8em;"><b>' . $classes[$this_object->class_id ()]["name"] . '</b> - ' . $this_object->name () . '</div>';
				break;

			### seller data
			case "seller_search":
				$customer_search_url = $this->mk_my_orb ("customer_search", array (
					"id" => $this_object->id(),
					"manager" => $this->manager->id(),
					"return_url" => get_ru (),
					"client_type" => "SELLER",
				));
				$str = "<a href='javascript:void(0)' onClick='aw_popup_scroll(\"{$customer_search_url}\",\"_spop\",640,480)'>Otsi klient</a>";
				$prop["value"] = $str;
				break;

			### buyer data
			case "buyer_search":
				$customer_search_url = $this->mk_my_orb ("customer_search", array (
					"id" => $this_object->id(),
					"manager" => $this->manager->id(),
					"return_url" => get_ru (),
					"client_type" => "BUYER",
				));
				$str = "<a href='javascript:void(0)' onClick='aw_popup_scroll(\"{$customer_search_url}\",\"_spop\",300,400)'>Otsi klient</a>";
				$prop["value"] = $str;
				break;

			### ...
			case "realestate_agent1":
				$cl_user = get_instance(CL_USER);
				$company = $cl_user->get_current_company ();

				if (is_object ($company))
				{
					$employees = new object_list($company->connections_from(array(
						"type" => "RELTYPE_WORKERS",
						"class_id" => CL_CRM_PERSON,
					)));
					$prop["options"] = $employees->names ();
				}

				$current_person_oid = $cl_user->get_current_person ();
				$prop["value"] = is_oid (reset($prop["value"])) ? $prop["value"] : array ($current_person_oid => $current_person_oid);
				break;

			case "realestate_agent2":
				$cl_user = get_instance(CL_USER);
				$company = $cl_user->get_current_company ();

				if (is_object ($company))
				{
					$employees = new object_list($company->connections_from(array(
						"type" => "RELTYPE_WORKERS",
						"class_id" => CL_CRM_PERSON,
					)));
					$prop["options"] = $employees->names ();
				}
				break;

			### additional info
			case "additional_info_en":
				// $prop["value"] = iconv("iso-8859-1", "UTF-8", $prop["value"]);
				// break;
			case "additional_info_ru":
				// $prop["value"] = iconv("iso-8859-5", "UTF-8", $prop["value"]);
				// break;
			case "additional_info_et":
			case "additional_info_fi":
			case "keywords_et":
				// $prop["value"] = iconv("iso-8859-4", "UTF-8", $prop["value"]);
				$lang_code = substr ($prop["name"], -2);
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
					$prop["value"] = iconv($charset, "UTF-8", $prop["value"]);
				}
				else
				{
					$prop["error"] = t("Keeleobjekti ei leitud.");
					$retval = PROP_ERROR;
				}
				break;

			### map
			case "map_create":
				$address_array = $this->address->prop ("address_array");
				$address_1 = $address_array[$this->manager->prop ("address_equivalent_1")];//maakond
				$address_2 = $address_array[$this->manager->prop ("address_equivalent_2")];//linn
				$address_4 = $address_array[$this->manager->prop ("address_equivalent_4")];//vald
				$street = $address_array[ADDRESS_STREET_TYPE];

				if ($address_2)
				{
					$address_parsed[] = urlencode ($address_2);
				}
				else
				{
					$address_parsed[] = urlencode ($address_1);
					$address_parsed[] = urlencode ($address_4);
				}

				$address_parsed[] = urlencode ($street);
				$address_parsed[] = urlencode ($address->prop ("street_address"));


				$address_parsed = implode ("+", $address_parsed);
				$save_url = urlencode ($this->mk_my_orb ("save_map_data", array (
					"id" => $this_object->id (),
				), "realestate_property"));

				### "http://www.city24.ee/client/city24client?pageId=1006&destPageId=1108&address={$address_parsed}&backUrl={$save_url}"
				### "http://www.city24.ee/client/city24client?pageId=1006&destPageId=1108&address={VAR:address_parsed}&backUrl={VAR:save_url}"
				$data = array (
					"address_parsed" => $address_parsed,
					"save_url" => $save_url,
				);
				$tpl_source = $this->manager->prop ("map_server_url");
				$this->use_template ($tpl_source);
				$this->vars ($data);
				$url = $this->parse();

				$prop["value"] = html::popup(array(
					"caption" => t("Vali asukoht kaardil"),
					"url" => $url,
					"height" => 600,
					"width" => 600,
				));
				break;

			case "map_url":
				if (!empty ($prop["value"]))
				{
					$url = $prop["value"];
					$prop["value"] = html::popup(array(
						"caption" => t("Ava kaart uues aknas"),
						"url" => $url,
					));
				}
				else
				{
					$prop["value"] = t("Kaarti pole veel loodud.");
				}
				break;

			case "map_show":
				$prop["value"] = "";
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop =& $arr["prop"];
		$retval = PROP_OK;
		$this_object = $arr["obj_inst"];

		switch($prop["name"])
		{
			case "seller":
			case "buyer":
				if (empty ($prop["value"]["firstname"]) and empty ($prop["value"]["lastname"]) and empty ($prop["value"]["personal_id"]))
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "transaction_price":
			case "total_floor_area":
			case "transaction_price2":
			case "transaction_rent":
			case "transaction_down_payment":
				$prop["value"] = safe_settype_float ($prop["value"]);
				break;

			### additional info
			case "additional_info_en":
				// $prop["value"] = iconv("UTF-8", "iso-8859-1", $prop["value"]);
				// break;
			case "additional_info_ru":
				// $prop["value"] = iconv("UTF-8", "iso-8859-5", $prop["value"]);
				// break;
			case "additional_info_et":
			case "additional_info_fi":
			case "keywords_et":
				$lang_code = substr ($prop["name"], -2);
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
					$prop["value"] = iconv("UTF-8", $charset, $prop["value"]);
				}
				else
				{
					$prop["error"] = t("Keeleobjekti ei leitud.");
					$retval = PROP_ERROR;
				}
				break;
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

		### set object name by address
		$address = $this_object->get_first_obj_by_reltype("RELTYPE_REALESTATE_ADDRESS");
		$address_text = $address->prop ("address_array");
		unset ($address_text[ADDRESS_COUNTRY_TYPE]);
		$address_text = implode (", ", $address_text);
		$name = $address_text . " " . $address->prop ("street_address") . ($address->prop ("apartment") ? "-" . $address->prop ("apartment") : "");
		$this_object->set_name ($name);
		$this_object->save ();

		### seller and buyer info
		#### seller
		$client = $this_object->get_first_obj_by_reltype("RELTYPE_REALESTATE_SELLER");

		if (is_object ($client))
		{
			### parse pid
			$pid_data = $this->parse_pid_et ($arr["pid"]);

			if (is_array ($pid_data))
			{
				list ($birthday, $gender) = $pid_data;
				$client->set_prop ("gender", $gender);
				$client->set_prop ("birthday", $birthday);
				$client->save ();
			}
		}

		#### buyer
		$client = $this_object->get_first_obj_by_reltype("RELTYPE_REALESTATE_BUYER");

		if (is_object ($client))
		{
			### parse pid
			$pid_data = $this->parse_pid_et ($arr["pid"]);

			if (is_array ($pid_data))
			{
				list ($birthday, $gender) = $pid_data;
				$client->set_prop ("gender", $gender);
				$client->set_prop ("birthday", $birthday);
				$client->save ();
			}
		}

		$this->manager->set_cache_dirty (true);
	}
/* END classbase methods */

	## returns array ((timestamp) $birthday, (int) $gender) if pid complies to Estonian personal identification number standard EVS 1990:585, aw-translated string description of errors otherwise. Gender: 1 - male, 2 - female.
	function parse_pid_et ($pid)
	{
		settype ($pid, "string");
		define ("PID_GENDER_FEMALE", 2);
		define ("PID_GENDER_MALE", 1);
		$errors = array ();

		if (strlen ($pid) != 11)
		{
			$errors[] = t("Isikukood vale pikkusega.");
		}

		$quotient = 10;
		$step = 0;
		$check = false;

		while (10 == $quotient and $step < 3 and !$check)
		{
			$order = 0;
			$multiplier = 1 + $step;
			$sum = NULL;

			while ($order < 10)
			{
				$sum += (int) $pid{$order} * $multiplier;
				$order++;
				$multiplier++;

				if (10 == $multiplier)
				{
					$multiplier = 1;
				}
			}

			$step += 2;
			$quotient = $sum%11;

			if ($quotient == (int) $pid{10})
			{
				$check = true;
			}
		}

		if (!$check)
		{
			$errors[] = t("Isikukood ei vasta Eesti Vabariigi isikukoodi standardile.");
		}

		$pid_1 = (int) substr ($pid, 0, 1);
		$pid_day = (int) substr ($pid, 5, 2);
		$pid_month = (int) substr ($pid, 3, 2);
		$pid_year = (int) substr ($pid, 1, 2);

		switch ($pid_1)
		{
			case 1: // 1800–1899  mees;
				$pid_year += 1800;
				$gender = PID_GENDER_MALE;
				break;

			case 2: // 1800–1899  naine;
				$pid_year += 1800;
				$gender = PID_GENDER_FEMALE;
				break;

			case 3: // 1900–1999  mees;
				$pid_year += 1900;
				$gender = PID_GENDER_MALE;
				break;

			case 4: // 1900–1999  naine;
				$pid_year += 1900;
				$gender = PID_GENDER_FEMALE;
				break;

			case 5: // 2000–2099  mees;
				$pid_year += 2000;
				$gender = PID_GENDER_MALE;
				break;

			case 6: // 2000–2099  naine;
				$pid_year += 2000;
				$gender = PID_GENDER_FEMALE;
				break;
		}

		if (checkdate ($pid_month, $pid_day, $pid_year))
		{
			$birth_date = mktime (0, 0, 0, $pid_month, $pid_day, $pid_year);
		}
		else
		{
			$errors[] = t("Isikukoodis leiduv sünnikuupäevateave ei vasta ühelegi kuupäevale Gregoriuse kalendris.");
		}

		if (count ($errors))
		{
			return implode (" \n", $errors);
		}
		else
		{
			return array ($birth_date, $gender);
		}
	}

	/**
		@attrib name=set_customer
		@param id required type=int
		@param client_oid required type=int
		@param client_type required
		@param close optional
	**/
	function set_customer ($arr)
	{
		$client = obj ($arr["client_oid"]);
		$this_object = obj ($arr["id"]);
		$client_types = array (
			"SELLER",
			"BUYER",
		);

		if (in_array ($arr["client_type"], $client_types))
		{
			$connections = $this_object->connections_from (array (
				"type" => "RELTYPE_REALESTATE_" . $arr["client_type"],
				"class_id" => CL_CRM_PERSON,
			));

			foreach ($connections as $connection)
			{
				$connection->delete ();
			}

			$this_object->connect (array (
				"to" => $client,
				"reltype" => "RELTYPE_REALESTATE_" . $arr["client_type"],
			));
		}

		if ($arr["close"])
		{
			exit ('<script language="javascript"> window.opener.location.href = window.opener.location.href; window.close(); </script>');
		}
	}

	/**
		@attrib name=customer_search all_args=1
		@param id required type=int
		@param client_type required
		@param manager required type=int
	**/
	function customer_search ($arr)
	{
		$manager = obj ($arr["manager"]);
		$this_object = obj ($arr["id"]);
		$tmp = $this->template_dir;
		$this->template_dir = $this->cfg["basedir"] . "/templates/applications/realestate_management/realestate_property";
		$this->read_template("customer_search.tpl");

		load_vcl("table");
		$t = new aw_table(array(
			"layout" => "generic"
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "pid",
			"caption" => t("Isikukood"),
		));
		$t->define_field(array(
			"name" => "phone",
			"caption" => t("Telefon"),
		));
		$t->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
		));
		$t->define_field(array(
			"name" => "pick",
			"caption" => t("Vali"),
		));


		if ($arr["firstname"] or $arr["lastname"])
		{
			$list = new object_list(array(
				"class_id" => CL_CRM_PERSON,
				"parent" => $manager->prop ("clients_folder"),
				"firstname" => "%" . $arr["firstname"] . "%",
				"lastname" => "%" . $arr["lastname"] . "%",
			));

			for ($client =& $list->begin(); !$list->end(); $client =& $list->next())
			{
				$customer_select_url = $this->mk_my_orb ("set_customer", array (
					"id" => $this_object->id(),
					"return_url" => $arr["return_url"],
					"client_type" => $arr["client_type"],
					"client_oid" => $client->id(),
					"close" => 1,
				));
				$phone = $client->get_first_obj_by_reltype ("RELTYPE_PHONE");
				$row["pick"] = html::href(array(
					"url" => $customer_select_url,
					"caption" => t("Vali see")
				));
				$row["name"] = $client->name ();
				$row["address"] = $client->prop ("comment");
				$row["pid"] = $client->prop ("personal_id");
				$row["phone"] = $phone->name ();
				$t->define_data($row);
			}

			$t->set_default_sortby("name");
			$t->sort_by();
			$this->vars(array("result" => $t->draw()));
		}

		$customer_search_reforb = $this->mk_reforb("customer_search", array(
			"reforb" => 0,
		));

		$this->vars(array(
			"id" => $this_object->id (),
			"manager" => $manager->id (),
			"reforb" => $customer_search_reforb,
			"firstname" => $arr["firstname"],
			"lastname" => $arr["lastname"],
			"client_type" => $arr["client_type"],
		));

		return $this->parse();
	}

	function _property_toolbar($arr)
	{
		$this_object = $arr["obj_inst"];
		$toolbar =& $arr["prop"]["vcl_inst"];
		$return_url = urlencode (aw_global_get('REQUEST_URI'));
		$classes = aw_ini_get("classes");
		$class = $classes[$this_object->class_id ()]["file"];
		$class = explode ("/", $class);
		$class = array_pop ($class);

		### urls
		$print_url_broker_pics = $this->mk_my_orb("print", array(
			"return_url" => $return_url,
			"id" => $this_object->id (),
			"contact_type" => "broker",
			"show_pictures" => true,
		), $class);
		$print_url_broker_nopics = $this->mk_my_orb("print", array(
			"return_url" => $return_url,
			"id" => $this_object->id (),
			"contact_type" => "broker",
			"show_pictures" => false,
		), $class);
		$print_url_seller_pics = $this->mk_my_orb("print", array(
			"return_url" => $return_url,
			"id" => $this_object->id (),
			"contact_type" => "seller",
			"show_pictures" => true,
		), $class);
		$print_url_seller_nopics = $this->mk_my_orb("print", array(
			"return_url" => $return_url,
			"id" => $this_object->id (),
			"contact_type" => "seller",
			"show_pictures" => false,
		), $class);

		### buttons
		$toolbar->add_menu_button(array(
			"name" => "print",
			"img" => "print.gif",
			"tooltip" => t("Prindi objektiinfo"),
		));

		$toolbar->add_menu_item(array(
			"parent" => "print",
			"text" => t("Maakleri andmetega/piltidega"),
			"link" => $print_url_broker_pics,
			"target" => "_blank",
		));

		$toolbar->add_menu_item(array(
			"parent" => "print",
			"text" => t("Maakleri andmetega/piltideta"),
			"link" => $print_url_broker_nopics,
			"target" => "_blank",
		));

		$toolbar->add_menu_item(array(
			"parent" => "print",
			"text" => t("Müüja andmetega/piltidega"),
			"link" => $print_url_seller_pics,
			"target" => "_blank",
		));

		$toolbar->add_menu_item(array(
			"parent" => "print",
			"text" => t("Müüja andmetega/piltideta"),
			"link" => $print_url_seller_nopics,
			"target" => "_blank",
		));

		$toolbar->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta muudatused"),
			"action" => "submit",
		));
	}

	function on_create ($arr)
	{
		if (is_oid ($arr["oid"]))
		{
			$this_object = obj ($arr["oid"]);

			### create address object
			$address = obj ();
			$address->set_class_id (CL_ADDRESS);

			if (is_oid ($this_object->prop ("realestate_manager")))
			{
				$manager = obj ($this_object->prop ("realestate_manager"));

				### get country
				if (is_oid ($manager->prop ("administrative_structure")))
				{
					### set address' country to default country from manager
					$address->set_parent ($manager->prop ("administrative_structure"));
					$address->set_prop ("administrative_structure", $manager->prop ("administrative_structure"));
					$address->save ();

					### connect property to address
					$this_object->connect (array (
						"to" => $address,
						"reltype" => "RELTYPE_REALESTATE_ADDRESS",
					));

					$this_object->create_brother ($address->id ());
				}
				else
				{
					error::raise(array(
						"msg" => t("Uue kinnisvaraobjekti loomisel vaikimisi riik defineerimata. Tekitati objekt, millel puudub aadress."),
						"fatal" => false,
						"show" => true,
					));
				}
			}
			else
			{
				error::raise(array(
					"msg" => t("Uue kinnisvaraobjekti loomisel kinnsvarahalduskeskkond defineerimata. Tekitati orbobjekt."),
					"fatal" => true,
					"show" => true,
				));
			}
		}
		else
		{
			error::raise(array(
				"msg" => t("Uue kinnisvaraobjekti loomisel ei antud argumendina kaasa loodud obj. id-d."),
				"fatal" => true,
				"show" => true,
			));
		}
	}

	function request_execute ($this_object)
	{
		return $this->view (array (
			"id" => $this_object->id (),
			"view_type" => "detailed",
		));
	}

	// attrib name=view
	// param id required type=int
	// param view_type required
	// param return_url optional
	function view ($arr)
	{
		$this_object = obj ($arr["id"]);
		$view_type = $arr["view_type"];

		// if ($this->can ("view", $this_object->prop ("realestate_manager")))
		// {
			// $realestate_manager = obj ($this_object->prop ("realestate_manager"));
		// }
		// else
		// {
			// error::raise (array (
				// "msg" => sprintf (t("Kinnisvaraobjektil halduskeskkond defineerimata või puudub juurdepääsuõigus (oid: %s)."), $arr["id"]),
				// "fatal" => true,
				// "show" => true,
			// ));
		// }

		// if ($this->can ("view", $realestate_manager->prop ("template_{$view_type}")))
		// {
			// $template = obj ($realestate_manager->prop ("template_{$view_type}"));
		// }
		// else
		// {
			// return t("Template defineerimata või puudub juurdepääsuõigus");
		// }

		$properties = $this->get_property_data (array (
			"id" => $this_object->id (),
		));
		$classes = aw_ini_get("classes");
		$class_name = $classes[$this_object->class_id ()]["name"];
		$data = array ();
		$data["link_return_url"] = $arr["return_url"];
		// $data["link_open"] = obj_link ($this_object->id ());
		$data["link_open"] = aw_url_change_var ("realestate_show_property", $this_object->id ());
		$data["class_name"] = $class_name;

		switch ($view_type)
		{
			case "detailed":
				$class_file = $classes[$this_object->class_id ()]["file"];
				$class_file = explode ("/", $class_file);
				$class_file = array_pop ($class_file);
				$class = str_replace ("realestate_", "", $class_file);
				$tpl = "propview_detailed_" . $class . ".tpl";
				break;

			case "short":
				$tpl = "propview_short.tpl";
				break;

			case "pictures":
				$tpl = "propview_pictures.tpl";
				break;

			default:
				return;
		}

		foreach ($properties as $name => $prop_data)
		{
			$data[$name] = $prop_data["strvalue"];
			$data[$name . "_caption"] = $prop_data["caption"];

			if($prop_data["type"] == "checkbox" and !empty ($prop_data["caption"]) and !empty ($prop_data["value"]) and substr ($name, 0, 4) == "has_")
			{
				### properties that go under tplvar "extras"
				$prop_caption = $prop_data["caption"];
				$first_char = in_array ($name, $this->propnames_starting_with_acronym) ? $prop_caption{0} : strtolower ($prop_caption{0});
				$extras[] = $first_char . substr ($prop_caption, 1);
			}
		}

		$tmp = $this->template_dir;
		$this->template_dir = $this->cfg["basedir"] . "/templates/applications/realestate_management/realestate_property";
		$this->read_template ($tpl);

		$i = 1;

		while (isset ($properties["picture" . $i . "_url"]))
		{
			$picture = array (
				"picture_url" => $properties["picture" . $i . "_url"]["value"],
				"picture_city24_id" => $properties["picture" . $i . "_city24_id"]["value"],
			);
			$this->vars ($picture);
			$data["PICTURE"] .= $this->parse ("PICTURE");
			$i++;

/* dbg */ if ($_GET["retpldbg"]==1){ arr ($picture); flush(); }
		}

		$data["picture_count"] = ($i - 1);
		$data["docid"] = $this_object->id ();
		$data["extras"] = implode (", ", $extras);

		$url_data = parse_url (aw_global_get ("REQUEST_URI"));
		$agent_name = urlencode ($properties["agent_name"]["strvalue"]);
		$query1 = "?realestate_agent={$agent_name}&realestate_srch=1";
		$data["show_agent_properties_url"] = aw_ini_get ("baseurl") . $url_data["path"] . $query1;

		$class = $classes[$this_object->class_id ()]["file"];
		$class = explode ("/", $class);
		$class = array_pop ($class);
		$data["open_pictureview_url"] = $this->mk_my_orb ("pictures_view", array (
			"id" => $this_object->id (),
		), $class);
		$data["open_printview_url"] = $this->mk_my_orb ("print", array (
			"id" => $this_object->id (),
			"contact_type" => "broker",
			"show_pictures" => false,
			"return_url" => urlencode (aw_global_get ("REQUEST_URI")),
		), $class);

		// aw_ini_get ("baseurl");

/* dbg */ if ($_GET["retpldbg"]==1){ arr ($data); flush(); }

		// $tpl_source = $template->prop ("source_html");
		// $this->use_template ($tpl_source);
		$this->vars ($data);
		$res = $this->parse();
		$this->template_dir = $tmp;
		return $res;
	}


/**
	@attrib name=pictures_view nologin=1
	@param id required type=int
**/
	function pictures_view ($arr)
	{
		return $this->view (array (
			"id" => $arr["id"],
			"view_type" => "pictures",
		));
	}

/**
	@attrib name=print nologin=1
	@param id required type=int
	@param contact_type required
	@param show_pictures optional
	@param view_type optional
	@param return_url optional
**/
	function print_view ($arr)
	{
		### init
		$this_object = obj ($arr["id"]);
		$view_type = isset ($arr["view_type"]) ? $arr["view_type"] : "printview";
		$show_pictures = isset ($arr["show_pictures"]) ? (boolean) $arr["show_pictures"] : false;
		$contact_type = $arr["contact_type"];

		if ($this->can ("view", $this_object->prop ("realestate_manager")))
		{
			$realestate_manager = obj ($this_object->prop ("realestate_manager"));
		}
		else
		{
			error::raise (array (
				"msg" => sprintf (t("Kinnisvaraobjektil halduskeskkond defineerimata või puudub juurdepääsuõigus (oid: %s)."), $arr["id"]),
				"fatal" => true,
				"show" => true,
			));
		}

		$properties = $this->get_property_data (array (
			"id" => $this_object->id (),
		));

		$classes = aw_ini_get("classes");
		$class_file = $classes[$this_object->class_id ()]["file"];
		$class_file = explode ("/", $class_file);
		$class_file = array_pop ($class_file);
		$class = str_replace ("realestate_", "", $class_file);

		$tmp = $this->template_dir;
		$this->template_dir = $this->cfg["basedir"] . "/templates/applications/realestate_management/realestate_property";
		$this->read_template("printview_{$class}.tpl");

		$data = array ();
		$cl_image = get_instance(CL_IMAGE);

		### process data
		#### contact information
		switch ($contact_type)
		{
			case "seller":
				$contact_person = $this_object->get_first_obj_by_reltype ("RELTYPE_REALESTATE_SELLER");
				break;

			case "broker":
				$contact_person = $this_object->get_first_obj_by_reltype ("RELTYPE_REALESTATE_AGENT");
				$contact_person2 = $this_object->get_first_obj_by_reltype ("RELTYPE_REALESTATE_AGENT2");
				break;
		}

		$contacts = array ();

		if (is_object ($contact_person))
		{
			$cl_image = get_instance(CL_IMAGE);
			$contact_data = array ();
			$contact_data[] = $contact_person->name ();

			$phone = $contact_person->get_first_obj_by_reltype ("RELTYPE_PHONE");
			$phone = is_object ($phone) ? $phone->name () : "";
			$contact_data[] = $phone;

			$email = $contact_person->get_first_obj_by_reltype ("RELTYPE_EMAIL");
			$email = is_object ($email) ? $email->prop ("mail") : "";

			if ($email)
			{
				$vars = array (
					"contact_email" => $email,
				);
				$this->vars ($vars);
				$contact_data[] = $this->parse ("contact_email");
			}

			$contacts[1]["contact_data"] = implode (", ", $contact_data);
			$contacts[1]["contact_email"] = $email;
			$contacts[1]["contact_phone"] = implode (", ", $phone);

			$agent_picture = $contact_person->get_first_obj_by_reltype ("RELTYPE_PICTURE");

			if (is_object ($agent_picture))
			{
				$agent_picture_url = $cl_image->get_url_by_id ($agent_picture->id ());
			}
			else
			{
				$agent_picture_url = "";
			}

			$contacts[1]["contact_picture_url"] =$agent_picture_url;
		}

		if (is_object ($contact_person2))
		{
			$cl_image = get_instance(CL_IMAGE);
			$contact_data = array ();
			$contact_data[] = $contact_person2->name ();

			$phone = $contact_person2->get_first_obj_by_reltype ("RELTYPE_PHONE");
			$phone = is_object ($phone) ? $phone->name () : "";
			$contact_data[] = $phone;

			$email = $contact_person->get_first_obj_by_reltype ("RELTYPE_EMAIL");
			$email = is_object ($email) ? $email->prop ("mail") : "";

			if ($email)
			{
				$vars = array (
					"contact2_email" => $email,
				);
				$this->vars ($vars);
				$contact_data[] = $this->parse ("contact_email");
			}

			$contacts[2]["contact2_data"] = implode (", ", $contact_data);
			$contacts[2]["contact2_email"] = $email;
			$contacts[2]["contact2_phone"] = implode (", ", $phone);

			$agent_picture = $contact_person2->get_first_obj_by_reltype ("RELTYPE_PICTURE");

			if (is_object ($agent_picture))
			{
				$agent_picture_url = $cl_image->get_url_by_id ($agent_picture->id ());
			}
			else
			{
				$agent_picture_url = "";
			}

			$contacts[2]["contact2_picture_url"] =$agent_picture_url;
		}

		foreach ($contacts as $contact)
		{
			$this->vars ($contact);
			$data["CONTACT"] .= $this->parse ("CONTACT");
		}

		#### logo
		if (is_oid ($this_object->meta ("owner_company_section")) and $this->can ("view", $this_object->meta ("owner_company_section")))
		{
			$company_section = obj ($this_object->meta ("owner_company_section"));
			$parent = $company_section;

			do
			{
				$parent = obj ($parent->parent ());
			}
			while (is_oid ($parent->parent ()) and (CL_CRM_COMPANY != $parent->class_id ()) and (CL_CRM_SECTION == $parent->class_id ()));

			if (is_object ($parent))
			{
				$company = $parent;
				// $company_logos = new object_list($realestate_manager->connections_from(array(
					// "type" => "RELTYPE_COMPANY_LOGO",
					// "class_id" => CL_IMAGE,
					// "name" => $company->name (),
				// )));
				// $company_logo = obj ($realestate_manager->prop ("company_logo"));
				// $data["company_logo_url"] = $cl_image->get_url_by_id ($company_logo->id ());
				// $data["company_logo_alt"] = $company_logo->prop ("alt");

				$data["company_logo_url"] = $company->prop ("logo");
				$data["company_logo_alt"] = $company->name ();
			}
		}

		#### class_name
		$classes = aw_ini_get("classes");
		$data["class_name"] = $classes[$this_object->class_id ()]["name"];

		#### pictures
		$contact_data["pictures"] = "";

		if ($show_pictures)
		{
			$pictures = new object_list($realestate_manager->connections_from(array(
				"type" => "RELTYPE_REALESTATE_PICTURE",
				"class_id" => CL_IMAGE,
			)));
			$pictures = $pictures->arr ();

			foreach ($pictures as $picture)
			{
				$vars = array (
					"picture_url" => $cl_image->get_url_by_id ($picture->id ()),
				);
				$this->vars ($vars);
				$contact_data["pictures"] .= $this->parse ("pictures");
			}
		}

		$data["address"] = $this_object->name ();

		#### class specific property selection
		$cl_cfgu = get_instance("cfg/cfgutils");
		// $properties = $cl_cfgu->load_properties(array ("clid" => $this_object->class_id ()));
		$properties = $this->get_property_data (array (
			"id" => $this_object->id (),
		));

		$class = $classes[$this_object->class_id ()]["file"];
		$class = explode ("/", $class);
		$class = array_pop ($class);
		$class = substr ($class, 11);

		$property_export_object = obj ($realestate_manager->prop ("print_properties_{$class}"));
		$display_properties = $property_export_object->meta("dat");

		$i = 0;
		$rows = "";
		$row = "";
		$extras = array ();

		foreach ($display_properties as $prop_name => $prop_data)
		{
			if ($prop_data["visible"])
			{
				$prop_caption = $properties[$prop_name]["caption"];

				if($properties[$prop_name]["type"] == "checkbox" and !empty ($prop_caption) and !empty ($properties[$prop_name]["value"]))
				{
					### properties that go under tplvar "extras"
					$first_char = in_array ($prop_name, $this->propnames_starting_with_acronym) ? $prop_caption{0} : strtolower ($prop_caption{0});
					$extras[] = $first_char . substr ($prop_caption, 1);
				}
				else
				{
					### ..
					$vars = array (
						"caption" => $properties[$prop_name]["caption"],
						"value" => $properties[$prop_name]["strvalue"],
						"suffix" => $prop_data["caption"],
					);
					$this->vars ($vars);
					$property_parsed = $this->parse ("re_" . $prop_name);
					$data["re_" . $prop_name] = $property_parsed;
				}
			}
		}

		$data["docid"] = $this_object->id ();
		$data["extras"] = implode (", ", $extras);
		$data["additional_info"] = $this_object->prop ("additional_info_" . aw_global_get("LC"));
		$data["city24_object_id"] = $this_object->prop ("city24_object_id");

		### ...
		$data["return_url"] = $arr["return_url"];

/* dbg */ if ($_GET["retpldbg"]==1){ arr ($data); flush(); }

		### parse tpl
		$this->vars ($data);
		$res = $this->parse();
		$this->template_dir = $tmp;
		return $res;
	}

	// attrib name=export_xml
	// param id required type=int
	// param no_declaration optional
	function export_xml ($arr)
	{
		$this->export_errors = "";

		if ($this->can ("view", $arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$this->export_errors .= t("Objekti id pole aw id v6i puudub juurdep22su6igus.\n");
		}

		$properties = $this->get_property_data (array (
			"id" => $arr["id"],
		));

		if (empty ($properties))
		{
			$this->export_errors .= t("Objekti atribuute ei 6nnestunud lugeda.\n");
		}

		$classes = aw_ini_get("classes");
		$class = $classes[$this_object->class_id ()]["file"];
		$class = explode ("/", $class);
		$class = array_pop ($class);

		if (empty ($class))
		{
			$this->export_errors .= t("Objekti klassi m22ramine eba6nnestus.\n");
		}

		$xml_data = $arr["no_declaration"] ? array () : array ('<?xml version="1.0" encoding="iso-8859-4"?>');
		$xml_data[] = '<realestate_object xmlns="http://www.automatweb.com/realestate_management">';
		$xml_data[] = '<class_name>' . $class . '</class_name>';

		foreach ($properties as $prop_data)
		{
			$tag_name = $prop_data["name"];
			$value = $prop_data["value"];
			$strvalue = htmlspecialchars ($prop_data["strvalue"], ENT_NOQUOTES);
			$altvalue = $prop_data["altvalue"];

			if ($prop_data["type"] == "releditor")
			{
				if (substr ($prop_data["name"], 0, 7) == "picture")
				{
					$tag_name = "picture_url";
				}
			}
//!!! midagi siin teha eksporditavate v22rtustega mis on teises charsetis, vene jne. additional info.
			$xml_data[] =
				'<' . $tag_name . ' type="' . $prop_data["type"] . '">' .
				'<value><![CDATA[' . $value . ']]></value>' .
				'<strvalue>' . $strvalue . '</strvalue>' .
				'<altvalue><![CDATA[' . $altvalue . ']]></altvalue>' .
				'</' . $tag_name . '>'
			;
		}

		$xml_data[] = '</realestate_object>';
		$xml_data = implode ("\n", $xml_data);
		return $xml_data;
	}

	// attrib name=get_property_data
	// param id required type=int
	function get_property_data ($arr)
	{
		$this_object = obj ($arr["id"]);
		$cl_image = get_instance(CL_IMAGE);
		$cl_cfgu = get_instance("cfg/cfgutils");
		$properties = $cl_cfgu->load_properties(array ("clid" => $this_object->class_id ()));

		### add local properties
		foreach ($properties as $name => $data)
		{
			$altvalue = "";

			if ($data["type"] == "classificator" and is_oid ($this_object->prop ($name)))
			{
				aw_disable_acl();
				$meta = obj ($this_object->prop ($name));
				$altvalue = $meta->comment ();
				aw_restore_acl();
			}

			$properties[$name]["value"] = $this_object->prop ($name);
			// $properties[$name]["strvalue"] = $this_object->prop_str ($name);
			$properties[$name]["altvalue"] = $altvalue;
			$properties[$name]["caption"] = $data["caption"];

			// if (is_float ($this_object->prop ($name)))
			if (in_array ($name, $this->float_types))
			{
				$properties[$name]["strvalue"] = number_format ($this_object->prop ($name), REALESTATE_NF_DEC, REALESTATE_NF_POINT,
REALESTATE_NF_SEP);
			}
			else
			{
				$properties[$name]["strvalue"] = $this_object->prop_str ($name);
			}
		}

		### add address properties
		$address = $this_object->get_first_obj_by_reltype ("RELTYPE_REALESTATE_ADDRESS");

		if (is_object ($address))
		{
			$parent = $address;
			$adminunits = array ();

			while (($parent->class_id () != CL_COUNTRY_ADMINISTRATIVE_STRUCTURE) and is_oid ($parent->parent ()) and $this->can ("view", $parent->parent ()))
			{////!!!!! update
				$parent = obj ($parent->parent ());

				switch ($parent->class_id ())
				{
					case CL_COUNTRY_CITYDISTRICT:
						$address_citydistrict = $parent->name ();
						break;
					case CL_COUNTRY_CITY:
						$address_city = $parent->name ();
						break;
					case CL_ADDRESS_STREET:
						$address_street = $parent->name ();
						break;
					case CL_COUNTRY_ADMINISTRATIVE_UNIT:
						$unit = obj ($parent->subclass ());
						$adminunits[] = array (
							"name" => $unit->name (),
							"value" => $parent->name (),
						);
						break;
				}
			}
//!!! END update
			$adminunits = array_reverse ($adminunits, false);
			$address_street_address = $address->prop ("street_address");
			$address_apartment = $address->prop ("apartment");
		}
		else
		{
			$adminunits = array_fill (0, 10, array ());
		}

		foreach ($adminunits as $key => $adminunit)
		{
			$prop_name = "address_adminunit{$key}";
			$properties[$prop_name] = array (
				"name" => $prop_name,
				"type" => "text",
				"caption" => $adminunit["name"],
				"value" => $adminunit["value"],
				"strvalue" => $adminunit["value"],
				"altvalue" => $adminunit["value"],
			);
		}

		$prop_name = "address_city";
		$properties[$prop_name] = array (
			"name" => $prop_name,
			"type" => "text",
			"caption" => t("Linn"),
			"value" => $address_city,
			"strvalue" => $address_city,
			"altvalue" => $address_city,
		);

		$prop_name = "address_citydistrict";
		$properties[$prop_name] = array (
			"name" => $prop_name,
			"type" => "text",
			"caption" => t("Linnaosa"),
			"value" => $address_citydistrict,
			"strvalue" => $address_citydistrict,
			"altvalue" => $address_citydistrict,
		);

		$prop_name = "address_street";
		$properties[$prop_name] = array (
			"name" => $prop_name,
			"type" => "text",
			"caption" => t("Tänav"),
			"value" => $address_street,
			"strvalue" => $address_street,
			"altvalue" => $address_street,
		);

		$prop_name = "address_street_address";
		$properties[$prop_name] = array (
			"name" => $prop_name,
			"type" => "text",
			"caption" => t("Maja nr."),
			"value" => $address_street_address,
			"strvalue" => $address_street_address,
			"altvalue" => $address_street_address,
		);

		$prop_name = "address_apartment";
		$properties[$prop_name] = array (
			"name" => $prop_name,
			"type" => "text",
			"caption" => t("Korter"),
			"value" => $address_apartment,
			"strvalue" => $address_apartment,
			"altvalue" => $address_apartment,
		);

		### add agent properties
		$agent = $this_object->get_first_obj_by_reltype ("RELTYPE_REALESTATE_AGENT");

		if (is_object ($agent))
		{
			$agent_phones = array ();

			foreach($agent->connections_from (array("type" => "RELTYPE_PHONE")) as $connection)
			{
				$agent_phones[] = $connection->prop ("to.name");
			}

			$agent_phones = implode (", ", $agent_phones);

			$agent_email = $agent->get_first_obj_by_reltype ("RELTYPE_EMAIL");
			$agent_email =  is_object ($agent_email) ? $agent_email->prop ("mail") : "";

			$agent_picture = $agent->get_first_obj_by_reltype ("RELTYPE_PICTURE");

			if (is_object ($agent_picture))
			{
				$agent_picture_url = $cl_image->get_url_by_id ($agent_picture->id ());
			}
			else
			{
				$agent_picture_url = "";
			}

			$name = "agent_name";
			$properties[$name] = array (
				"name" => $name,
				"type" => "text",
				"caption" => t("Maakler"),
				"value" => $agent->name (),
				"strvalue" => $agent->name (),
				"altvalue" => $agent->name (),
			);

			$name = "agent_email";
			$properties[$name] = array (
				"name" => $name,
				"type" => "text",
				"caption" => t("Maakleri e-mail"),
				"value" => $agent_email,
				"strvalue" => $agent_email,
				"altvalue" => $agent_email,
			);

			$name = "agent_phone";
			$properties[$name] = array (
				"name" => $name,
				"type" => "text",
				"caption" => t("Maakleri telefon"),
				"value" => $agent_phones,
				"strvalue" => $agent_phones,
				"altvalue" => $agent_phones,
			);

			$name = "agent_picture_url";
			$properties[$name] = array (
				"name" => $name,
				"type" => "text",
				"caption" => t("Maakleri pilt"),
				"value" => $agent_picture_url,
				"strvalue" => $agent_picture_url,
				"altvalue" => $agent_picture_url,
			);
		}

		### add agent2 properties
		$agent = $this_object->get_first_obj_by_reltype ("RELTYPE_REALESTATE_AGENT2");

		if (is_object ($agent))
		{
			$agent_phones = array ();

			foreach($agent->connections_from (array("type" => "RELTYPE_PHONE")) as $connection)
			{
				$agent_phones[] = $connection->prop ("to.name");
			}

			$agent_phones = implode (", ", $agent_phones);

			$agent_email = $agent->get_first_obj_by_reltype ("RELTYPE_EMAIL");
			$agent_email =  is_object ($agent_email) ? $agent_email->prop ("mail") : "";

			$agent_picture = $agent->get_first_obj_by_reltype ("RELTYPE_PICTURE");

			if (is_object ($agent_picture))
			{
				$agent_picture_url = $cl_image->get_url_by_id ($agent_picture->id ());
			}
			else
			{
				$agent_picture_url = "";
			}

			$name = "agent2_name";
			$properties[$name] = array (
				"name" => $name,
				"type" => "text",
				"caption" => t("Maakler"),
				"value" => $agent->name (),
				"strvalue" => $agent->name (),
				"altvalue" => $agent->name (),
			);

			$name = "agent2_email";
			$properties[$name] = array (
				"name" => $name,
				"type" => "text",
				"caption" => t("Maakleri e-mail"),
				"value" => $agent_email,
				"strvalue" => $agent_email,
				"altvalue" => $agent_email,
			);

			$name = "agent2_phone";
			$properties[$name] = array (
				"name" => $name,
				"type" => "text",
				"caption" => t("Maakleri telefon"),
				"value" => $agent_phones,
				"strvalue" => $agent_phones,
				"altvalue" => $agent_phones,
			);

			$name = "agent2_picture_url";
			$properties[$name] = array (
				"name" => $name,
				"type" => "text",
				"caption" => t("Maakleri pilt"),
				"value" => $agent_picture_url,
				"strvalue" => $agent_picture_url,
				"altvalue" => $agent_picture_url,
			);
		}

		### add seller properties
		$seller = $this_object->get_first_obj_by_reltype ("RELTYPE_REALESTATE_SELLER");

		if (is_object ($seller))
		{
			$seller_phones = array ();

			foreach($seller->connections_from (array("type" => "RELTYPE_PHONE")) as $connection)
			{
				$seller_phones[] = $connection->prop ("to.name");
			}

			$seller_phones = implode (", ", $seller_phones);

			$seller_email = $seller->get_first_obj_by_reltype ("RELTYPE_EMAIL");
			$seller_email =  is_object ($seller_email) ? $seller_email->prop ("mail") : "";
			$seller_name = $seller->name ();
		}

		$name = "seller_name";
		$properties[$name] = array (
			"name" => $name,
			"type" => "text",
			"caption" => t("Müüja"),
			"value" => $seller_name,
			"altvalue" => $seller_name,
			"strvalue" => $seller_name,
		);

		$name = "seller_email";
		$properties[$name] = array (
			"name" => $name,
			"type" => "text",
			"caption" => t("Müüja e-mail"),
			"value" => $seller_email,
			"strvalue" => $seller_email,
			"altvalue" => $seller_email,
		);

		$name = "seller_phone";
		$properties[$name] = array (
			"name" => $name,
			"type" => "text",
			"caption" => t("Müüja telefon"),
			"value" => $seller_phones,
			"altvalue" => $seller_phones,
			"strvalue" => $seller_phones,
		);

		### add buyer properties
		$buyer = $this_object->get_first_obj_by_reltype ("RELTYPE_REALESTATE_BUYER");

		if (is_object ($buyer))
		{
			$buyer_phones = array ();

			foreach($buyer->connections_from (array("type" => "RELTYPE_PHONE")) as $connection)
			{
				$buyer_phones[] = $connection->prop ("to.name");
			}

			$buyer_phones = implode (", ", $buyer_phones);

			$buyer_email = $buyer->get_first_obj_by_reltype ("RELTYPE_EMAIL");
			$buyer_email =  is_object ($buyer_email) ? $buyer_email->prop ("mail") : "";
			$buyer_name = $buyer->name ();
		}

		$name = "buyer_name";
		$properties[$name] = array (
			"name" => $name,
			"type" => "text",
			"caption" => t("Ostja"),
			"value" => $buyer_name,
			"strvalue" => $buyer_name,
			"altvalue" => $buyer_name,
		);

		$name = "buyer_email";
		$properties[$name] = array (
			"name" => $name,
			"type" => "text",
			"caption" => t("Ostja e-mail"),
			"value" => $buyer_email,
			"strvalue" => $buyer_email,
			"altvalue" => $buyer_email,
		);

		$name = "buyer_phone";
		$properties[$name] = array (
			"name" => $name,
			"type" => "text",
			"caption" => t("Ostja telefon"),
			"value" => $buyer_phones,
			"strvalue" => $buyer_phones,
			"altvalue" => $buyer_phones,
		);

		### add pictures properties
		$pictures = new object_list($this_object->connections_from(array(
			"type" => "RELTYPE_REALESTATE_PICTURE",
			"class_id" => CL_IMAGE,
		)));
		$pictures = $pictures->arr ();
		$i = 1;

		foreach ($pictures as $picture)
		{
			$name = "picture" . $i . "_url";
			$properties[$name] = array (
				"name" => $name,
				"type" => "releditor",
				"value" => $cl_image->get_url_by_id ($picture->id ()),
				"strvalue" => $cl_image->get_url_by_id ($picture->id ()),
				"altvalue" => $cl_image->get_url_by_id ($picture->id ()),
			);

			$name = "picture" . $i . "_city24_id";
			$properties[$name] = array (
				"name" => $name,
				"type" => "hidden",
				"value" => $picture->meta ("picture_city24_id"),
				"strvalue" => $picture->meta ("picture_city24_id"),
				"altvalue" => $picture->meta ("picture_city24_id"),
			);

			$i++;
		}

		return $properties;
	}

/**
	@attrib name=save_map_data nologin=1
	@param id required type=int
	@param mapUrl optional
	@param mapPoint optional
	@param mapArea optional
	@param mapId optional
**/
	function save_map_data ($arr)
	{
		// if (!fromcity24)//!!! teha et lastaks tulijaid city24st ja mitte mujalt
		// {
			// get_ip();
			// error::raise(array(
				// "msg" => sprintf (t("Attempted map data save by unauthorized . (id: %s)"), $arr["id"]),
				// "fatal" => true,
				// "show" => false,
			// ));
		// }

		$property = obj ($arr["id"]);
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

		if (in_array ($property->class_id (), $realestate_classes))
		{
			$property->set_prop ("map_url", $arr["mapUrl"]);
			$property->set_prop ("map_point", $arr["mapPoint"]);
			$property->set_prop ("map_area", $arr["mapArea"]);
			$property->set_prop ("map_id", $arr["mapId"]);
			$property->save ();
		}
		else
		{
			error::raise(array(
				"msg" => sprintf (t("Attempted map data save on object not of allowed class. (id: %s)"), $arr["id"]),
				"fatal" => true,
				"show" => false,
			));
		}

		echo sprintf ("<br /><center>%s</center>", t("Salvestatud"));
		echo "<script type='text/javascript'>opener.location.reload(); setTimeout('window.close()',1000);</script>";
		exit;
	}

	function on_delete ($arr)
	{
		$this_object = obj ($arr["oid"]);

		### delete connected objects not needed elsewhere
		$applicable_reltypes = array (
			"RELTYPE_REALESTATE_PICTURE",
			"RELTYPE_REALESTATE_ADDRESS",
		);
		$connections = $project->connections_from (array ("type" => $applicable_reltypes));

		foreach ($connections as $connection)
		{
			$o = $connection->to ();

			if ($this->can("delete", $o->id()))
			{
				$o->delete ();
			}
			else
			{
				error::raise(array(
					"msg" => sprintf (t("Kustutatava kinnisvaraobjekti [%s] kaasobjekti ei lubata kasutajal kustutada. Viga õiguste seadetes. Jääb orbobjekt, mille id on %s"), $arr["oid"], $o->id ()),
					"fatal" => false,
					"show" => false,
				));
			}
		}

		if (is_oid ($this_object->prop ("realestate_manager")))
		{
			$manager = obj ($this_object->prop ("realestate_manager"));
			$manager->set_cache_dirty (true);
		}
	}
}

function safe_settype_float ($value)
{
	$separators = ".,";
	$int = (int) preg_replace ("/\s*/S", "", strtok ($value, $separators));
	$dec = preg_replace ("/\s*/S", "", strtok ($separators));
	return (float) ("{$int}.{$dec}");
}

?>
