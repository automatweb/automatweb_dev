<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_search.aw,v 1.6 2005/12/18 14:01:05 voldemar Exp $
// realestate_search.aw - Kinnisvaraobjektide otsing
/*

@classinfo syslog_type=ST_REALESTATE_SEARCH relationmgr=yes no_comment=1 no_status=1

@groupinfo grp_search caption="Otsing"
@groupinfo grp_formdesign caption="Parameetrite valik"

@default table=objects
@default group=general
@default field=meta
@default method=serialize
	@property template type=select
	@caption Näitamise põhi (template)

	@property result_format type=select
	@caption Otsingutulemuste näitamise formaat

	@property result_no_form type=checkbox ch_value=1
	@comment Näita otsingutulemusi ilma otsinguvormita. Ei mõjuta admin liidese otsingut.
	@caption Tulemused otsinguvormita

	@property searchform_select_size type=textbox datatype=int
	@comment [0] - võimalus valida parameetrile vaid üks väärtus, [1 - ...] - võimalus valida mitu.
	@caption Otsinguvormi valikuelementide suurus

	@property realestate_mgr type=relpicker reltype=RELTYPE_OWNER clid=CL_REALESTATE_MANAGER automatic=1
	@comment Kinnisvarahalduskeskkond, mille objektide hulgast otsida soovitakse
	@caption Kinnisvarahalduskeskkond

	@property administrative_structure type=relpicker reltype=RELTYPE_ADMINISTRATIVE_STRUCTURE clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE automatic=1
	@caption Haldusjaotus

	@property save_search type=checkbox ch_value=1
	@caption Salvesta otsingutulemus


@default group=grp_search
	@property search_class_id type=select multiple=1 size=5
	@caption Objekt

	@property search_transaction_type type=select multiple=1 size=3
	@caption Tehingu tüüp

	@layout box1 type=hbox
	@caption Hinnavahemik
	@property search_transaction_price_min type=textbox parent=box1 size=10
	@caption Hind min
	@property search_transaction_price_max type=textbox parent=box1 size=10
	@caption Hind max

	@layout box2 type=hbox
	@comment Ruutmeetrit
	@caption Üldpinna vahemik
	@property search_total_floor_area_min type=textbox parent=box2 size=10
	@caption Üldpind min
	@property search_total_floor_area_max type=textbox parent=box2 size=10
	@caption Üldpind max

	@property search_number_of_rooms type=textbox datatype=int size=10
	@caption Tubade arv

	@property searchparam_address1 type=select multiple=1
	@caption Maakond

	@property searchparam_address2 type=select multiple=1
	@caption Linn

	@property searchparam_address3 type=select multiple=1
	@caption Linnaosa

	// @property search_address_text type=textbox
	// @caption Aadress vabatekstina

	@property search_condition type=select multiple=1 size=5
	@caption Valmidus

	@property searchparam_fromdate type=date_select
	@caption Alates kuupäevast

	@property search_usage_purpose type=select multiple=1 size=5
	@caption Äripinna tüüp

	@property search_agent type=select multiple=1 size=5
	@caption Maakler

	@property search_special_status type=select
	@caption Eristaatus

	@property search_is_middle_floor type=checkbox
	@caption Pole esimene ega viimane korrus

	@property searchparam_onlywithpictures type=checkbox
	@caption Näita ainult pildiga kuulutusi

	@property search_button type=submit store=no
	@caption Otsi

	@property searchresult type=table store=no
	@caption Otsingutulemused


@default group=grp_formdesign
	@property formelements type=chooser multiple=1 orient=vertical
	@caption Otsinguparameetrid

	@property agent_sections type=select multiple=1 size=5
	@comment Osakonnad, mille maaklereid otsinguparameetri väärtuse valikus näidatakse
	@caption Maaklerite osakonnad


// --------------- RELATION TYPES ---------------------

@reltype OWNER clid=CL_REALESTATE_MANAGER value=1
@caption Kinnisvaraobjektide halduskeskkond

@reltype ADMINISTRATIVE_STRUCTURE clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE value=2
@caption Haldusjaotus

*/


define ("REALESTATE_SEARCH_ALL", "ALL");
define ("NEWLINE", "<br />\n");

class realestate_search extends class_base
{
	var $realestate_classes = array (
		CL_REALESTATE_HOUSE,
		CL_REALESTATE_ROWHOUSE,
		CL_REALESTATE_COTTAGE,
		CL_REALESTATE_HOUSEPART,
		CL_REALESTATE_APARTMENT,
		CL_REALESTATE_COMMERCIAL,
		CL_REALESTATE_GARAGE,
		CL_REALESTATE_LAND,
	);
	var $result_table_recordsperpage = 50;

	function realestate_search ()
	{
		$this->init (array (
			"tpldir" => "applications/realestate_management/realestate_search",
			"clid" => CL_REALESTATE_SEARCH,
		));
	}

	function callback_on_load ($arr)
	{
		if (is_oid ($arr["request"]["id"]))
		{
			$this_object = obj ($arr["request"]["id"]);

			if ($this->can ("view", $this_object->prop ("realestate_mgr")))
			{
				$this->manager = obj ($this_object->prop ("realestate_mgr"));
			}

			if (is_object ($this->manager))
			{
				$this->administrative_structure = $this->manager->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");
			}
		}

		$this->classificator = get_instance(CL_CLASSIFICATOR);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		if ($prop["group"] == "grp_search" and !is_object ($this->manager))
		{
			$prop["error"] = t("Kinnisvarahalduskeskkond määramata");
			return PROP_FATAL_ERROR;
		}

		if ($prop["group"] == "grp_search" and !is_object ($this->administrative_structure))
		{
			$prop["error"] = t("Haldusjaotus määramata");
			return PROP_FATAL_ERROR;
		}

		switch($prop["name"])
		{
			case "template":
				$prop["options"] = array (
					"search1" => "search1",
				);
				break;

			case "result_format":
				$prop["options"] = array (
					"format1" => t("V1"),
				);
				break;

			case "search_class_id":
				$prop["options"] = array (
					CL_REALESTATE_HOUSE => t("Maja"),
					CL_REALESTATE_ROWHOUSE => t("Ridaelamu"),
					CL_REALESTATE_COTTAGE => t("Suvila"),
					CL_REALESTATE_HOUSEPART => t("Majaosa"),
					CL_REALESTATE_APARTMENT => t("Korter"),
					CL_REALESTATE_COMMERCIAL => t("Äripind"),
					CL_REALESTATE_GARAGE => t("Garaaz"),
					CL_REALESTATE_LAND => t("Maatükk"),
				);
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["ci"];
				break;

			case "search_transaction_type":
				$prop_args = array (
					"clid" => CL_REALESTATE_PROPERTY,
					"name" => "transaction_type",
				);
				list ($options, $name, $use_type) = $this->classificator->get_choices($prop_args);
				// $prop["options"] = array("" => "") + $options->names();
				$prop["options"] = $options->names();
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["tt"];
				break;

			case "search_transaction_price_min":
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["tpmin"];
				break;

			case "search_transaction_price_max":
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["tpmax"];
				break;

			case "search_total_floor_area_min":
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["tfamin"];
				break;

			case "search_total_floor_area_max":
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["tfamax"];
				break;

			case "search_total_floor_area":
				break;

			case "search_number_of_rooms":
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["nor"];
				break;

			case "searchparam_address1":
				$list =& $this->administrative_structure->prop (array (
					"prop" => "units_by_division",
					"division" => $this->manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_1"),
				));
				$options = is_object ($list) ? $list->names () : array (); ### maakond
				$prop["options"] = $options;
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["a1"];
				break;

			case "searchparam_address2":
				$list =& $this->administrative_structure->prop (array (
					"prop" => "units_by_division",
					"division" => $this->manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_2"),
				));
				$options = is_object ($list) ? $list->names () : array (); ### linn
				$prop["options"] = $options;
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["a2"];
				break;

			case "searchparam_address3":
				$list =& $this->administrative_structure->prop (array (
					"prop" => "units_by_division",
					"division" => $this->manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_3"),
				));
				$options = is_object ($list) ? $list->names () : array (); ### linnaosa
				$prop["options"] = $options;
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["a3"];
				break;

			case "searchparam_fromdate":
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : isset ($_GET["realestate_search"]["fd"]) ? mktime (0, 0, 0, (int) $_GET["realestate_search"]["fd"]["month"], (int) $_GET["realestate_search"]["fd"]["day"], (int) $_GET["realestate_search"]["fd"]["year"]) : (time () - 60*86400);
				break;

			case "search_condition":
				$prop_args = array (
					"clid" => CL_REALESTATE_HOUSE,
					"name" => "condition",
				);
				list ($options, $name, $use_type) = $this->classificator->get_choices($prop_args);
				// $prop["options"] = array("" => "") + $options->names();
				$prop["options"] = $options->names();
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["c"];
				break;

			case "search_usage_purpose":
				$prop_args = array (
					"clid" => CL_REALESTATE_COMMERCIAL,
					"name" => "usage_purpose",
				);
				list ($options, $name, $use_type) = $this->classificator->get_choices($prop_args);
				// $prop["options"] = array("" => "") + $options->names();
				$prop["options"] = $options->names();
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["up"];
				break;

			case "search_special_status":
				$prop_args = array (
					"clid" => CL_REALESTATE_HOUSE,
					"name" => "special_status",
				);
				list ($options, $name, $use_type) = $this->classificator->get_choices($prop_args);
				// $prop["options"] = array("" => "") + $options->names();
				$prop["options"] = $options->names();
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["ss"];
				break;

			case "search_agent":
				$sections = $this_object->prop ("agent_sections");

				if (is_array ($sections))
				{
					$options = array ();
					aw_switch_user (array ("uid" => $this->manager->prop ("almightyuser")));

					foreach ($sections as $section_oid)
					{
						if (is_oid ($section_oid))
						{
							$section = obj ($section_oid);
							$employees = new object_list ($section->connections_from(array(
								"type" => "RELTYPE_WORKERS",
								"class_id" => CL_CRM_PERSON,
							)));
							$options += $employees->names ();
						}
					}

					natcasesort ($options);
					$prop["options"] = $options;
					$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["agent"];
					aw_restore_user ();
				}
				break;

			case "searchresult":
				$table =& $prop["vcl_inst"];
				$this->_init_properties_list ($table);
				break;

			case "formelements":
				$options = array ();

				$cl_cfgu = get_instance("cfg/cfgutils");
				$properties = $cl_cfgu->load_properties(array ("clid" => CL_REALESTATE_SEARCH));
				$applicable_properties = array (
					"search_class_id",
					"search_transaction_type",
					"search_transaction_price_min",
					"search_transaction_price_max",
					"search_total_floor_area_min",
					"search_total_floor_area_max",
					"search_number_of_rooms",
					"searchparam_address1",
					"searchparam_address2",
					"searchparam_address3",
					"search_condition",
					"searchparam_fromdate",
					"search_usage_purpose",
					"search_is_middle_floor",
					"search_special_status",
					"searchparam_onlywithpictures",
					"search_agent",
				);

				foreach ($properties as $name => $property_data)
				{
					if (in_array ($name, $applicable_properties))
					{
						$options[$name] = $property_data["caption"];
					}
				}

				$prop["options"] = $options;
				break;

			case "agent_sections":
				$options = array ();
				$list = new object_list ($this->manager->connections_from(array(
					"type" => "RELTYPE_REALESTATEMGR_USER",
					"class_id" => CL_CRM_COMPANY,
				)));
				$companies = $list->arr ();

				foreach ($companies as $company)
				{
					$list = new object_list (array (
						"parent" => $company->id (),
						"class_id" => CL_CRM_SECTION,
						"site_id" => array (),
					));
					$options += $list->names ();
				}

				$prop["options"] = $options;
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		if (($prop["group"] == "grp_search") and (!$this_object->prop ("save_search")))
		{
			return PROP_IGNORE;
		}

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_pre_save ($arr)
	{
		// arr ($arr);
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		if (is_oid ($_GET["realestate_show_property"]))
		{
			return $this->show_property ($arr);
		}

		enter_function("re_search::show - search");
		$this_object = obj ($arr["id"]);
		$visible_formelements = $this_object->prop ("formelements");

		if (is_oid ($this_object->prop ("realestate_mgr")))
		{
			$realestate_manager = obj ($this_object->prop ("realestate_mgr"));
		}
		else
		{
			echo t("Kinnisvarahalduskeskkond määramata.") . NEWLINE;
			return false;
		}

		### options
		$this->get_options ($arr);

		### values
		if ($this_object->prop ("save_search") and !$_GET["realestate_srch"])
		{
			$args = array (
				"ci" => $this_object->prop ("search_class_id"),
				"tt" => $this_object->prop ("search_transaction_type"),
				"tpmin" => $this_object->prop ("search_transaction_price_min"),
				"tpmax" => $this_object->prop ("search_transaction_price_max"),
				"tfamin" => $this_object->prop ("search_total_floor_area_min"),
				"tfamax" => $this_object->prop ("search_total_floor_area_max"),
				"nor" => $this_object->prop ("search_number_of_rooms"),
				"a1" => $this_object->prop ("searchparam_address1"),
				"a2" => $this_object->prop ("searchparam_address2"),
				"a3" => $this_object->prop ("searchparam_address3"),
				"at" => $this_object->prop ("searchparam_addresstext"),
				"fd" => $this_object->prop ("searchparam_fromdate"),
				"up" => $this_object->prop ("search_usage_purpose"),
				"agent" => $this_object->prop ("search_agent"),
				"c" => $this_object->prop ("search_condition"),
				"imf" => $this_object->prop ("search_is_middle_floor"),
				"ss" => $this_object->prop ("search_special_status"),
				"owp" => $this_object->prop ("searchparam_onlywithpictures"),
			);
		}
		else
		{
			$args = array (
				"realestate_srch" => $_GET["realestate_srch"],
				"ci" => $_GET["realestate_ci"],
				"tt" => $_GET["realestate_tt"],
				"tpmin" => $_GET["realestate_tpmin"],
				"tpmax" => $_GET["realestate_tpmax"],
				"tfamin" => $_GET["realestate_tfamin"],
				"tfamax" => $_GET["realestate_tfamax"],
				"nor" => $_GET["realestate_nor"],
				"a1" => $_GET["realestate_a1"],
				"a2" => $_GET["realestate_a2"],
				"a3" => $_GET["realestate_a3"],
				"at" => $_GET["realestate_at"],
				"fd" => $_GET["realestate_fd"],
				"up" => $_GET["realestate_up"],
				"agent" => $_GET["realestate_agent"],
				"c" => $_GET["realestate_c"],
				"imf" => $_GET["realestate_imf"],
				"ss" => $_GET["realestate_ss"],
				"owp" => $_GET["realestate_owp"],
			);
		}
		$search = $this->get_search_args ($args);

		if (!$this_object->prop ("result_no_form"))
		{
			### captions
			$cl_cfgu = get_instance("cfg/cfgutils");
			$properties = $cl_cfgu->load_properties(array ("clid" => CL_REALESTATE_SEARCH));

			### formelements
			$select_size = (int) $this_object->prop ("searchform_select_size");
			$form_elements = array ();

			if (in_array ("search_class_id", $visible_formelements))
			{
				$form_elements["ci"]["caption"] = $properties["search_class_id"]["caption"];
				$form_elements["ci"]["element"] = html::select(array(
					"name" => "realestate_ci",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $this->options_ci,
					"value" => $search["ci"],
				));
			}

			if (in_array ("search_transaction_type", $visible_formelements))
			{
				$form_elements["tt"]["caption"] = $properties["search_transaction_type"]["caption"];
				$form_elements["tt"]["element"] = html::select(array(
					"name" => "realestate_tt",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $this->options_tt,
					"value" => $search["tt"],
				));
			}

			if (in_array ("search_transaction_price_min", $visible_formelements))
			{
				$form_elements["tpmin"]["caption"] = $properties["search_transaction_price_min"]["caption"];
				$form_elements["tpmin"]["element"] = html::textbox(array(
					"name" => "realestate_tpmin",
					"value" => empty ($search["tpmin"]) ? "" : $search["tpmin"],
					"size" => "6",
					// "textsize" => "11px",
				));
			}

			if (in_array ("search_transaction_price_max", $visible_formelements))
			{
				$form_elements["tpmax"]["caption"] = $properties["search_transaction_price_max"]["caption"];
				$form_elements["tpmax"]["element"] = html::textbox(array(
					"name" => "realestate_tpmax",
					"value" => empty ($search["tpmax"]) ? "" : $search["tpmax"],
					"size" => "6",
					// "textsize" => "11px",
				));
			}

			if (in_array ("search_transaction_price_min", $visible_formelements) and in_array ("search_transaction_price_max", $visible_formelements))
			{
				$form_elements["tp"]["caption"] = t("Hind");
				$form_elements["tp"]["element"] = $form_elements["tpmin"]["element"]  . t(" kuni ") . $form_elements["tpmax"]["element"];
				unset ($form_elements["tpmin"]);
				unset ($form_elements["tpmax"]);
			}

			if (in_array ("search_total_floor_area_min", $visible_formelements))
			{
				$form_elements["tfamin"]["caption"] = $properties["search_total_floor_area_min"]["caption"];
				$form_elements["tfamin"]["element"] = html::textbox(array(
					"name" => "realestate_tfamin",
					"value" => empty ($search["tfamin"]) ? "" : $search["tfamin"],
					"size" => "6",
					// "textsize" => "11px",
				));
			}

			if (in_array ("search_total_floor_area_max", $visible_formelements))
			{
				$form_elements["tfamax"]["caption"] = $properties["search_total_floor_area_max"]["caption"];
				$form_elements["tfamax"]["element"] = html::textbox(array(
					"name" => "realestate_tfamax",
					"value" => empty ($search["tfamax"]) ? "" : $search["tfamax"],
					"size" => "6",
					// "textsize" => "11px",
				));
			}

			if (in_array ("search_total_floor_area_min", $visible_formelements) and in_array ("search_total_floor_area_max", $visible_formelements))
			{
				$form_elements["tfa"]["caption"] = t("Üldpind");
				$form_elements["tfa"]["element"] = $form_elements["tfamin"]["element"]  . t(" kuni ") . $form_elements["tfamax"]["element"];
				unset ($form_elements["tfamin"]);
				unset ($form_elements["tfamax"]);
			}

			if (in_array ("search_number_of_rooms", $visible_formelements))
			{
				$form_elements["nor"]["caption"] = $properties["search_number_of_rooms"]["caption"];
				$form_elements["nor"]["element"] = html::textbox(array(
					"name" => "realestate_nor",
					"value" => $search["nor"],
					"size" => "6",
					// "textsize" => "11px",
				));
			}

			if (in_array ("searchparam_address1", $visible_formelements))
			{
				$form_elements["a1"]["caption"] = $properties["searchparam_address1"]["caption"];
				$form_elements["a1"]["element"] = html::select(array(
					"name" => "realestate_a1",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $this->options_a1,
					"value" => $search["a1"],
				));
			}

			if (in_array ("searchparam_address2", $visible_formelements))
			{
				$form_elements["a2"]["caption"] = $properties["searchparam_address2"]["caption"];
				$form_elements["a2"]["element"] = html::select(array(
					"name" => "realestate_a2",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $this->options_a2,
					"value" => $search["a2"],
					"onchange" => (in_array ("searchparam_address3", $visible_formelements) ? "changeA3(this);" : NULL),
				));

				if (in_array ("searchparam_address3", $visible_formelements))
				{
					if (!is_object ($this->division3))
					{
						$this->division3 = $realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_3");
					}

					$a3_division = $this->division3->id ();
				}
			}

			if (in_array ("searchparam_address3", $visible_formelements))
			{
				$form_elements["a3"]["caption"] = $properties["searchparam_address3"]["caption"];
				$form_elements["a3"]["element"] = html::select(array(
					"name" => "realestate_a3",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => (in_array ("searchparam_address2", $visible_formelements) ? NULL : $this->options_a3),
					"value" => $search["a3"],
				));
			}

			if (in_array ("searchparam_addresstext", $visible_formelements))
			{
				$form_elements["at"]["caption"] = $properties["searchparam_addresstext"]["caption"];
				$form_elements["at"]["element"] = html::textbox(array(
					"name" => "realestate_at",
					"value" => $search["at"],
					"size" => "16",
					// "textsize" => "11px",
				));
			}

			if (in_array ("searchparam_fromdate", $visible_formelements))
			{
				$form_elements["fd"]["caption"] = $properties["searchparam_fromdate"]["caption"];
				$form_elements["fd"]["element"] = html::date_select(array(
					"name" => "realestate_fd",
					"mon_for" => 1,
					"value" => $search["fd"],
					// "textsize" => "11px",
				));
			}

			if (in_array ("search_condition", $visible_formelements))
			{
				$form_elements["c"]["caption"] = $properties["search_condition"]["caption"];
				$form_elements["c"]["element"] = html::select(array(
					"name" => "realestate_c",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $this->options_c,
					"value" => $search["c"],
				));
			}

			if (in_array ("search_usage_purpose", $visible_formelements))
			{
				$form_elements["up"]["caption"] = $properties["search_usage_purpose"]["caption"];
				$form_elements["up"]["element"] = html::select(array(
					"name" => "realestate_up",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $this->options_up,
					"value" => $search["up"],
				));
			}

			if (in_array ("search_agent", $visible_formelements))
			{
				$form_elements["agent"]["caption"] = $properties["search_agent"]["caption"];
				$form_elements["agent"]["element"] = html::select(array(
					"name" => "realestate_agent",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $this->options_agent,
					"value" => $search["agent"],
				));
				// $form_elements["agent"]["element"] = html::textbox(array(
					// "name" => "realestate_agent",
					// "value" => $search["agent"],
					// "size" => "16",
					// "textsize" => "11px",
				// ));
			}

			if (in_array ("search_is_middle_floor", $visible_formelements))
			{
				$form_elements["imf"]["caption"] = $properties["search_is_middle_floor"]["caption"];
				$form_elements["imf"]["element"] = html::checkbox(array(
					"name" => "realestate_imf",
					"value" => 1,
					"checked" => $search["imf"],
				));
			}

			if (in_array ("search_special_status", $visible_formelements))
			{
				$form_elements["ss"]["caption"] = $properties["search_special_status"]["caption"];
				$form_elements["ss"]["element"] = html::select(array(
					"name" => "realestate_ss",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $this->options_ss,
					"value" => $search["ss"],
				));
			}

			if (in_array ("searchparam_onlywithpictures", $visible_formelements))
			{
				$form_elements["owp"]["caption"] = $properties["searchparam_onlywithpictures"]["caption"];
				$form_elements["owp"]["element"] = html::checkbox(array(
					"name" => "realestate_owp",
					"value" => 1,
					"checked" => $search["owp"],
				));
			}
		}

		if ($_GET["realestate_srch"] == 1)
		{ ### search
			$args = array (
				"this" => $this_object,
				"manager" => $realestate_manager,
				"search" => $search,
			);
			$list =& $this->search ($args);
			$search_requested = true;
		}
		elseif ($this_object->prop ("save_search"))
		{
			$args = array (
				"this" => $this_object,
				"manager" => $realestate_manager,
			);
			$list =& $this->search ($args);
			$search_requested = true;
		}
		else
		{
			$list = array ();
			$search_requested = false;
		}

		exit_function("re_search::show - search");

		if (count ($list))
		{ ### result
			classload ("vcl/table");
			$table = new vcl_table ();
			$classes = aw_ini_get ("classes");

			switch ($this_object->prop ("result_format"))
			{
				case "format1":
					$table->set_layout("realestate_searchresult");
					$table->define_field(array(
						"name" => "object",
						"caption" => NULL,
					));
					$table->define_pageselector (array (
						"type" => "text",
						"records_per_page" => 25,
					));

					foreach ($this->realestate_classes as $cls_id)
					{
						$cl_instance_var = "cl_property_" . $cls_id;

						if (!is_object ($this->$cl_instance_var))
						{
							$this->$cl_instance_var = get_instance ($cls_id);
							$this->$cl_instance_var->classes = $classes;
						}
					}

					foreach ($list as $property)
					{
						$cl_instance_var = "cl_property_" . $property->class_id ();
						$object_html = $this->$cl_instance_var->view (array (
							"this" => $property,
							"view_type" => "short",
						));

						$data = array (
							"object" => $object_html,
 						);
						$table->define_data ($data);
					}
					break;
			}

			$result = $table->get_html ();
		}

		### style
		$template = $this_object->prop ("template") . ".css";
		$this->read_template($template);
		$this->vars (array());
		$table_style = $this->parse ();

		### output
		$template = $this_object->prop ("template") . ".tpl";
		$this->read_template($template);

		if ($this_object->prop ("result_no_form") and $search_requested)
		{ #### don't show search form
			$this->vars(array(
				"table_style" => $table_style,
				"result" => $result,
				"number_of_results" => count ($list),
			));
		}
		else
		{
			$el_count = count ($form_elements);
			$column_count = 2;
			$elements_in_column = ceil ($el_count/$column_count);
			$columns = "";
			$j = $column_count;

			while ($j--)
			{
				$i = $elements_in_column;
				$rows = "";

				while ($i--)
				{
					$caption = $element = "&nbsp;";
					$el = array_shift ($form_elements);

					if ($el)
					{
						$caption = $el["caption"];
						$element = $el["element"];
					}

					$this->vars (array (
						"caption" => $caption,
						"element" => $element,
					));
					$rows .= $this->parse ("RE_SEARCHFORM_ROW");
				}

				$this->vars (array (
					"RE_SEARCHFORM_ROW" => $rows,
				));
				$columns .= $this->parse ("RE_SEARCHFORM_COL");
			}

			$this->vars (array (
				"RE_SEARCHFORM_COL" => $columns,
				"buttondisplay" => $el_count ? "block" : "none",
				"columns" => $column_count,
			));
			$form = $this->parse ("RE_SEARCHFORM");

			$a3_options_url = $this->mk_my_orb ("get_a3_options", array (
				"id" => $this_object->id (),
			), CL_REALESTATE_SEARCH, false, true);

			$this->vars (array (
				"RE_SEARCHFORM" => $form,
				"table_style" => $table_style,
				"result" => $result,
				"a3_options_url" => $a3_options_url,
				"a3_element_id" => "realestate_a3",
				"a3_division" => $a3_division,
				"number_of_results" => count ($list),
			));
		}

		return $this->parse();
	}

	function get_options ($arr)
	{
		$this_object = obj ($arr["id"]);
		$classificator = get_instance(CL_CLASSIFICATOR);

		if (is_oid ($this_object->prop ("realestate_mgr")))
		{
			$realestate_manager = obj ($this_object->prop ("realestate_mgr"));
		}
		else
		{
			echo t("Kinnisvarahalduskeskkond otsinguobjektil defineerimata.") . NEWLINE;
			return false;
		}

		$administrative_structure = $realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");

		if (!is_object ($administrative_structure))
		{
			echo t("Kinnisvarahalduskeskkonnas haldusjaotus defineerimata.") . NEWLINE;
			return false;
		}

		### class_id
		$this->options_ci = array (
			REALESTATE_SEARCH_ALL => "",
			CL_REALESTATE_HOUSE => t("Maja"),
			CL_REALESTATE_ROWHOUSE => t("Ridaelamu"),
			CL_REALESTATE_COTTAGE => t("Suvila"),
			CL_REALESTATE_HOUSEPART => t("Majaosa"),
			CL_REALESTATE_APARTMENT => t("Korter"),
			CL_REALESTATE_COMMERCIAL => t("Äripind"),
			CL_REALESTATE_GARAGE => t("Garaaz"),
			CL_REALESTATE_LAND => t("Maatükk"),
		);
		natcasesort ($this->options_ci);

		### transaction_type
		$prop_args = array (
			"clid" => CL_REALESTATE_PROPERTY,
			"name" => "transaction_type",
		);
		list ($options_tt, $name, $use_type) = $classificator->get_choices($prop_args);
		$this->options_tt = array(REALESTATE_SEARCH_ALL => "") + $options_tt->names();
		natcasesort ($this->options_tt);

		### address1
		$list =& $administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_1"),
		));
		$options = is_object ($list) ? $list->names () : array (); // maakond
		$this->options_a1 = array(REALESTATE_SEARCH_ALL => "") + $options;
		natcasesort ($this->options_a1);

		### to save time, get only a minimal set of options for elementary web search
		if ($arr["get_minimal_set"])
		{
			return;
		}

		### address2
		$list =& $administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_2"),
		));
		$options = is_object ($list) ? $list->names () : array (); // linn
		$this->options_a2 = array(REALESTATE_SEARCH_ALL => "") + $options;
		natcasesort ($this->options_a2);

		### address3
		if (!is_object ($this->division3))
		{
			$this->division3 = $realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_3");
		}

		$list =& $administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $this->division3,
		));
		$options = is_object ($list) ? $list->names () : array (); // linnaosa
		$this->options_a3 = array(REALESTATE_SEARCH_ALL => "") + $options;
		natcasesort ($this->options_a3);

		### condition
		$prop_args = array (
			"clid" => CL_REALESTATE_HOUSE,
			"name" => "condition",
		);
		list ($options_c, $name, $use_type) = $classificator->get_choices($prop_args);
		$this->options_c = array(REALESTATE_SEARCH_ALL => "") + $options_c->names();
		natcasesort ($this->options_c);

		### usage_purpose
		$prop_args = array (
			"clid" => CL_REALESTATE_COMMERCIAL,
			"name" => "usage_purpose",
		);
		list ($options_up, $name, $use_type) = $classificator->get_choices($prop_args);
		$this->options_up = array(REALESTATE_SEARCH_ALL => "") + $options_up->names();
		natcasesort ($this->options_up);

		### special_status
		$prop_args = array (
			"clid" => CL_REALESTATE_HOUSE,
			"name" => "special_status",
		);
		list ($options_ss, $name, $use_type) = $classificator->get_choices($prop_args);
		$this->options_ss = array(REALESTATE_SEARCH_ALL => "") + $options_ss->names();
		natcasesort ($this->options_ss);

		### agent
		$sections = $this_object->prop ("agent_sections");
		$options = array ();

		if (is_array ($sections))
		{
			aw_switch_user (array ("uid" => $realestate_manager->prop ("almightyuser")));

			foreach ($sections as $section_oid)
			{
				if (is_oid ($section_oid))
				{
					$section = obj ($section_oid);
					$employees = new object_list ($section->connections_from(array(
						"type" => "RELTYPE_WORKERS",
						"class_id" => CL_CRM_PERSON,
					)));
					$options = array(REALESTATE_SEARCH_ALL => "") + $employees->names ();
				}
			}

			aw_restore_user ();
		}

		natcasesort ($options);
		$this->options_agent = $options;
	}

	function agent_has_realestate_properties ($agent)
	{
		$connections = $agent->connections_to ();

		foreach ($connections as $connection)
		{
			if (in_array ($connection->prop ("from.class_id"), $this->realestate_classes))
			{
				return true;
			}
		}

		return false;
	}

	function get_search_args ($arr)
	{
		if ($arr["realestate_srch"] == 1)
		{
			$arr["ci"] = ($arr["ci"] === REALESTATE_SEARCH_ALL) ? NULL : $arr["ci"];
			$search_ci = (array) $arr["ci"];
			unset ($search_ci[REALESTATE_SEARCH_ALL]);

			foreach ($search_ci as $value)
			{
				if (!isset ($this->options_ci[$value]))
				{
					$search_ci = NULL;
					break;
				}
			}

			$arr["tt"] = ($arr["tt"] === REALESTATE_SEARCH_ALL) ? NULL : $arr["tt"];
			$search_tt = (array) $arr["tt"];
			unset ($search_tt[REALESTATE_SEARCH_ALL]);

			foreach ($search_tt as $value)
			{
				if (!isset ($this->options_tt[$value]))
				{
					$search_tt = NULL;
					break;
				}
			}

			$search_tpmin = (float) $arr["tpmin"];
			$search_tpmax = (float) $arr["tpmax"];
			$search_tfamin = (float) $arr["tfamin"];
			$search_tfamax = (float) $arr["tfamax"];
			$search_nor = $arr["nor"] ? (int) $arr["nor"] : NULL;

			$arr["a1"] = ($arr["a1"] === REALESTATE_SEARCH_ALL) ? NULL : $arr["a1"];
			$search_a1 = (array) $arr["a1"];
			unset ($search_a1[REALESTATE_SEARCH_ALL]);

			foreach ($search_a1 as $value)
			{
				if (!isset ($this->options_a1[$value]))
				{
					$search_a1 = NULL;
					break;
				}
			}

			$arr["a2"] = ($arr["a2"] === REALESTATE_SEARCH_ALL) ? NULL : $arr["a2"];
			$search_a2 = (array) $arr["a2"];
			unset ($search_a2[REALESTATE_SEARCH_ALL]);

			foreach ($search_a2 as $value)
			{
				if (!isset ($this->options_a2[$value]))
				{
					$search_a2 = NULL;
					break;
				}
			}

			$arr["a3"] = ($arr["a3"] === REALESTATE_SEARCH_ALL) ? NULL : $arr["a3"];
			$search_a3 = (array) $arr["a3"];
			unset ($search_a3[REALESTATE_SEARCH_ALL]);

			foreach ($search_a3 as $value)
			{
				if (!isset ($this->options_a3[$value]))
				{
					$search_a3 = NULL;
					break;
				}
			}

			$search_at = str_pad ($arr["at"], 200);
			$search_fd = mktime (0, 0, 0, (int) $arr["fd"]["month"], (int) $arr["fd"]["day"], (int) $arr["fd"]["year"]);

			$arr["c"] = ($arr["c"] === REALESTATE_SEARCH_ALL) ? NULL : $arr["c"];
			$search_c = (array) $arr["c"];
			unset ($search_c[REALESTATE_SEARCH_ALL]);

			foreach ($search_c as $value)
			{
				if (!isset ($this->options_c[$value]))
				{
					$search_c = NULL;
					break;
				}
			}

			$arr["up"] = ($arr["up"] === REALESTATE_SEARCH_ALL) ? NULL : $arr["up"];
			$search_up = (array) $arr["up"];
			unset ($search_up[REALESTATE_SEARCH_ALL]);

			foreach ($search_up as $value)
			{
				if (!isset ($this->options_up[$value]))
				{
					$search_up = NULL;
					break;
				}
			}

			$arr["ss"] = ($arr["ss"] === REALESTATE_SEARCH_ALL) ? NULL : $arr["ss"];
			$search_ss = (array) $arr["ss"];
			unset ($search_ss[REALESTATE_SEARCH_ALL]);

			foreach ($search_ss as $value)
			{
				if (!isset ($this->options_ss[$value]))
				{
					$search_ss = NULL;
					break;
				}
			}

			// $arr["agent"] = ($arr["agent"] === REALESTATE_SEARCH_ALL) ? NULL : $arr["agent"];
			// $search_agent = (array) $arr["agent"];
			// unset ($search_agent[REALESTATE_SEARCH_ALL]);

			// foreach ($search_agent as $value)
			// {
				// if (!isset ($this->options_agent[$value]))
				// {
					// $search_agent = NULL;
					// break;
				// }
			// }
			$search_agent = trim ($arr["agent"]);

			$search_imf = (int) $arr["imf"];
			$search_owp = (int) $arr["owp"];
		}
		else
		{
			$search_fd = (time () - 60*86400);
		}

		$args = array (
			"ci" => $search_ci,
			"tt" => $search_tt,
			"tpmin" => $search_tpmin,
			"tpmax" => $search_tpmax,
			"tfamin" => $search_tfamin,
			"tfamax" => $search_tfamax,
			"nor" => $search_nor,
			"a1" => $search_a1,
			"a2" => $search_a2,
			"a3" => $search_a3,
			"at" => $search_at,
			"fd" => $search_fd,
			"up" => $search_up,
			"ss" => $search_ss,
			"agent" => $search_agent,
			"c" => $search_c,
			"imf" => $search_imf,
			"owp" => $search_owp,
		);
		return $args;
	}

	function &search ($arr)
	{
		enter_function ("re_search::search");
		$this_object = $arr["this"];

		$search_ci = $arr["search"]["ci"];
		$search_tpmin = $arr["search"]["tpmin"];
		$search_tpmax = $arr["search"]["tpmax"];
		$search_tfamin = $arr["search"]["tfamin"];
		$search_tfamax = $arr["search"]["tfamax"];
		$search_fd = $arr["search"]["fd"];
		$search_nor = $arr["search"]["nor"];
		$search_tt = $arr["search"]["tt"];
		$search_c = $arr["search"]["c"];
		$search_up = $arr["search"]["up"];
		$search_ss = $arr["search"]["ss"];
		$search_agent = $arr["search"]["agent"];
		$search_a1 = $arr["search"]["a1"];
		$search_a2 = $arr["search"]["a2"];
		$search_a3 = $arr["search"]["a3"];
		$search_at = $arr["search"]["at"];
		$search_owp = $arr["search"]["owp"];
		$search_imf = $arr["search"]["imf"];

		$list = array ();
		$manager =& $arr["manager"];
		$parents = array ();

		if (!count ($search_ci))
		{
			$search_ci = $this->realestate_classes;
		}

		foreach ($search_ci as $clid)
		{
			switch ($clid)
			{
				case CL_REALESTATE_HOUSE:
					if (is_oid ($manager->prop ("houses_folder")))
					{
						$parents[] = $manager->prop ("houses_folder");
						// $search_ci_clstr = "CL_REALESTATE_HOUSE";
					}
					break;
				case CL_REALESTATE_ROWHOUSE:
					if (is_oid ($manager->prop ("rowhouses_folder")))
					{
						$parents[] = $manager->prop ("rowhouses_folder");
						// $search_ci_clstr = "CL_REALESTATE_ROWHOUSE";
					}
					break;
				case CL_REALESTATE_COTTAGE:
					if (is_oid ($manager->prop ("cottages_folder")))
					{
						$parents[] = $manager->prop ("cottages_folder");
						// $search_ci_clstr = "CL_REALESTATE_COTTAGE";
					}
					break;
				case CL_REALESTATE_HOUSEPART:
					if (is_oid ($manager->prop ("houseparts_folder")))
					{
						$parents[] = $manager->prop ("houseparts_folder");
						// $search_ci_clstr = "CL_REALESTATE_HOUSEPART";
					}
					break;
				case CL_REALESTATE_COMMERCIAL:
					if (is_oid ($manager->prop ("commercial_properties_folder")))
					{
						$parents[] = $manager->prop ("commercial_properties_folder");
						// $search_ci_clstr = "CL_REALESTATE_COMMERCIAL";
					}
					break;
				case CL_REALESTATE_GARAGE:
					if (is_oid ($manager->prop ("garages_folder")))
					{
						$parents[] = $manager->prop ("garages_folder");
						// $search_ci_clstr = "CL_REALESTATE_GARAGE";
					}
					break;
				case CL_REALESTATE_LAND:
					if (is_oid ($manager->prop ("land_estates_folder")))
					{
						$parents[] = $manager->prop ("land_estates_folder");
						// $search_ci_clstr = "CL_REALESTATE_LAND";
					}
					break;
				case CL_REALESTATE_APARTMENT:
					if (is_oid ($manager->prop ("apartments_folder")))
					{
						$parents[] = $manager->prop ("apartments_folder");
						// $search_ci_clstr = "CL_REALESTATE_APARTMENT";
					}
					break;
			}
		}

		if (!empty ($search_agent))
		{
			// ### freetext agent search
			// $agents_list = new object_list (array (
				// "class_id" => CL_CRM_PERSON,
				// "name" => "%" . $search_agent . "%",
				// "site_id" => array (),
				// "lang_id" => array (),
			// ));
			// $search_agent = $agents_list->ids ();

			### agent by selection
			$agent_constraint = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array (
					"realestate_agent1" => (int) $search_agent,
					"realestate_agent2" => (int) $search_agent,
				)
			));
		}
		else
		{
			$agent_constraint = NULL;
		}

		### compose transaction_price constraint
		if ($search_tpmin and $search_tpmax)
		{
			$common_args = new obj_predicate_compare (OBJ_COMP_BETWEEN, $search_tpmin, $search_tpmax);
		}
		elseif ($search_tpmin)
		{
			$common_args = new obj_predicate_compare (OBJ_COMP_GREATER_OR_EQ, $search_tpmin);
		}
		elseif ($search_tpmax)
		{
			$tp_constraint = new obj_predicate_compare (OBJ_COMP_LESS_OR_EQ, $search_tpmax);
		}
		else
		{
			$tp_constraint = NULL;
		}

		### compose total_floor_area constraint
		if ($search_tfamin and $search_tfamax)
		{
			$tfa_constraint = new obj_predicate_compare (OBJ_COMP_BETWEEN, $search_tpmin, $search_tpmax);
		}
		elseif ($search_tfamin)
		{
			$tfa_constraint = new obj_predicate_compare (OBJ_COMP_GREATER_OR_EQ, $search_tpmin);
		}
		elseif ($search_tfamax)
		{
			$tfa_constraint = new obj_predicate_compare (OBJ_COMP_LESS_OR_EQ, $search_tpmax);
		}
		else
		{
			$tfa_constraint = NULL;
		}

		### compose address constraint
		// if (count ($search_a1) or count ($search_a2) or count ($search_a3))
		// {
			// $search_admin_units = array_merge ($search_a1, $search_a2, $search_a3);

			// if ($search_ci_clstr)
			// {
				// $address_constraint = new object_list_filter (array (
					// "logic" => "OR",
					// "conditions" => array (
						// $search_ci_clstr . ".RELTYPE_REALESTATE_ADDRESS.RELTYPE_ADMINISTRATIVE_UNIT" => $search_admin_units,
					// )
				// ));
			// }
			// else
			// {
				// $address_constraint = new object_list_filter (array (
					// "logic" => "OR",
					// "conditions" => array (
						// "CL_REALESTATE_APARTMENT.RELTYPE_REALESTATE_ADDRESS.RELTYPE_ADMINISTRATIVE_UNIT" => $search_admin_units,
						// "CL_REALESTATE_COMMERCIAL.RELTYPE_REALESTATE_ADDRESS.RELTYPE_ADMINISTRATIVE_UNIT" => $search_admin_units,
						// "CL_REALESTATE_COTTAGE.RELTYPE_REALESTATE_ADDRESS.RELTYPE_ADMINISTRATIVE_UNIT" => $search_admin_units,
						// "CL_REALESTATE_GARAGE.RELTYPE_REALESTATE_ADDRESS.RELTYPE_ADMINISTRATIVE_UNIT" => $search_admin_units,
						// "CL_REALESTATE_HOUSE.RELTYPE_REALESTATE_ADDRESS.RELTYPE_ADMINISTRATIVE_UNIT" => $search_admin_units,
						// "CL_REALESTATE_HOUSEPART.RELTYPE_REALESTATE_ADDRESS.RELTYPE_ADMINISTRATIVE_UNIT" => $search_admin_units,
						// "CL_REALESTATE_LAND.RELTYPE_REALESTATE_ADDRESS.RELTYPE_ADMINISTRATIVE_UNIT" => $search_admin_units,
						// "CL_REALESTATE_ROWHOUSE.RELTYPE_REALESTATE_ADDRESS.RELTYPE_ADMINISTRATIVE_UNIT" => $search_admin_units,
					// )
				// ));
				// $address_constraint = NULL;
			// }
		// }

		// ### search addresses
		// $search_admin_units = array_merge ($search_a1, $search_a2, $search_a3);
		// $address_ids = NULL;

		// if (count ($search_admin_units))
		// {
			// $ids = array ();
			// $applicable_classes = array (
				// CL_ADDRESS,
				// CL_ADDRESS_STREET,
				// CL_COUNTRY,
				// CL_COUNTRY_ADMINISTRATIVE_STRUCTURE,
				// CL_COUNTRY_ADMINISTRATIVE_UNIT,
				// CL_COUNTRY_CITY,
				// CL_COUNTRY_CITYDISTRICT,
			// );

			// foreach ($search_admin_units as $admin_unit)
			// {
				// if (is_oid ($admin_unit))
				// {
					// $tree = new object_tree (array (
						// "parent" => $admin_unit,
						// "class_id" => $applicable_classes,
					// ));
					// $list = $tree->to_list ();
					// $ids = array_merge ($ids, $list->ids ());
				// }
			// }

			// $list = new object_list (array (
				// "oid" => $ids,
				// "class_id" => CL_ADDRESS,
				// "site_id" => array (),
				// "lang_id" => array (),
			// ));

			// if ($list->count ())
			// {
				// $address_ids = $list->ids ();
			// }
		// }

		### limit
		$limit = isset ($_GET["ft_page"]) ? ($_GET["ft_page"] * $this->result_table_recordsperpage) . "," . $this->result_table_recordsperpage : NULL;

		### search
		$args = array (
			"class_id" => $search_ci,
			"parent" => $parents,
			"transaction_type" => $search_tt,
			"transaction_price" => $tp_constraint,
			"special_status" => $search_ss,
			"created" => new obj_predicate_compare (OBJ_COMP_GREATER, $search_fd),
			"site_id" => array (),
			"lang_id" => array (),
			"limit" => $limit,
			// "address_connection" => $address_ids,
			// $address_constraint,
			$agent_constraint,

			### class specific
			"total_floor_area" => $tfa_constraint,
			"condition" => $search_c,
			"is_middle_floor" => (empty ($search_imf) ? NULL : $search_imf),
			"number_of_rooms" => (empty ($search_nor) ? NULL : $search_nor),
			"usage_purpose" => $search_up,
		);

		$result_list = new object_list ($args);
		$result_list = $result_list->arr ();

		### search by address
		if (count ($search_a3))
		{
			$search_admin_units = $search_a3;
		}
		elseif (count ($search_a2))
		{
			$search_admin_units = $search_a2;
		}
		elseif (count ($search_a1))
		{
			$search_admin_units = $search_a1;
		}
		else
		{
			$search_admin_units = false;
		}

		if ($search_admin_units !== false)
		{
			$unit_classes = array (
				CL_COUNTRY_ADMINISTRATIVE_UNIT,
				CL_COUNTRY_CITY,
				CL_COUNTRY_CITYDISTRICT,
			);

			### get addresses for all properties found
			$connection = new connection ();
			$address_connections = $connection->find (array (
					"from" => array_keys ($result_list),
					"type" => 1,
			));

			$address_ids = array ();
			$property_index = array ();

			foreach ($address_connections as $connection)
			{
				$address_ids[$connection["from"]] = $connection["to"];
			}

			### search by adminunit
			$connection = new connection ();
			$unit_connections = $connection->find (array (
					"from" => $address_ids,
					"to" => $search_admin_units,
					"type" => 2,
			));

			### filter out properties not under specified admin units
			$applicable_address_ids = array ();

			foreach ($unit_connections as $connection)
			{
				$applicable_address_ids[] = $connection["from"];
			}

			foreach ($result_list as $property_oid => $property)
			{
				if (!in_array ($address_ids[$property_oid], $applicable_address_ids))
				{
					unset ($result_list[$property_oid]);
				}
			}
		}

		// ### address search in case searching from all realestate property classes. search for objects under admin units, if units specified, intersect results with objectlist found
		// if (count ($search_a1) or count ($search_a2) or count ($search_a3) and $search_ci_clstr)
		// {
			// aw_switch_user (array ("uid" => $manager->prop ("almightyuser")));
			// $search_admin_units = array_merge ($search_a1, $search_a2, $search_a3);

			// foreach ($result_list as $oid => $property)
			// {
				// $address = $property->get_first_obj_by_reltype ("RELTYPE_REALESTATE_ADDRESS");

				// if (is_object ($address))
				// {
					// $address_ids = $address->prop ("address_ids");

					// if (array_intersect ($search_admin_units, $address_ids) != $search_admin_units)
					// {
						// unset ($result_list[$oid]);
					// }
				// }
				// else
				// {
					// unset ($result_list[$oid]);
				// }
			// }

			// aw_restore_user ();
		// }


/* dbg */ if ($_GET["researchdbg"]==1){ arr (count($result_list)); }
		exit_function ("re_search::search");

		return $result_list;
	}

	function _init_properties_list (&$table)
	{
		### table definition
		$table->define_field(array(
			"name" => "class",
			"caption" => t("Tüüp"),
		));

		$table->define_field(array(
			"name" => "address_1",
			"caption" => t("Maa&shy;kond"),
			"sortable" => 1,
		));

		$table->define_field(array(
			"name" => "address_2",
			"caption" => t("Linn"),
			"sortable" => 1,
		));

		$table->define_field(array(
			"name" => "address_3",
			"caption" => t("Vald"),
			"sortable" => 1,
		));

		$table->define_field (array (
			"name" => "transaction_type",
			"caption" => t("Tehing"),
			"sortable" => 1,
		));

		$table->define_field(array(
			"name" => "created",
			"caption" => t("Lisatud"),
			"type" => "time",
			"format" => $this->default_date_format,
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"type" => "time",
			"format" => $this->default_date_format,
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "owner_company",
			"caption" => t("Omanik"),
			"sortable" => 1
		));

		$table->define_field(array(
			"name" => "agent",
			"caption" => t("Maakler"),
			"filter" => $agents_filter,
			"sortable" => 1,
		));

		// $table->define_field(array(
			// "name" => "visible",
			// "caption" => t("<a href='javascript:selall(\"realestatemgr-is_visible\")'>Näh&shy;tav</a>"),
			// "tooltip" => t("Kõik read: vali/kaota valik"),
		// ));

		$table->define_field(array(
			"name" => "archived",
			"caption" => t("Arhi&shy;veeri&shy;tud"),
		));

		$table->set_default_sortby ("created");
		$table->set_default_sorder ("desc");
		$table->define_pageselector (array (
			"type" => "text",
			"records_per_page" => $this->result_table_recordsperpage,
		));
	}

	function show_property ($arr)
	{
		if (!is_oid ($_GET["realestate_show_property"]))
		{
			return false;
		}

		$property = obj ($_GET["realestate_show_property"]);
		$cl_instance_var = "cl_property_" . $property->class_id ();

		if (!is_object ($this->$cl_instance_var))
		{
			$this->$cl_instance_var = get_instance ($property->class_id ());
		}

		return $this->$cl_instance_var->request_execute ($property);
	}

/**
    @attrib name=get_a3_options nologin=1
	@param id required type=int
	@param reAddress2Selected optional type=int
	@param reAddress3Division optional type=int
	@returns List of options separated by newline (\n). Void on error.
**/
	function get_a3_options ($arr)
	{
		$this_object = obj ($arr["id"]);
		$a2_value = $arr["reAddress2Selected"];
		$a3_division = $arr["reAddress3Division"];
		$administrative_structure = $this_object->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");

		if (is_oid ($a2_value) and is_oid ($a3_division) and is_object ($administrative_structure))
		{
			### get units
			$list =& $administrative_structure->prop (array (
				"prop" => "units_by_division",
				"division" => $a3_division,
				"parent" => $a2_value,
			));
			$administrative_units = is_object ($list) ? $list->names () : array ();
			natcasesort ($administrative_units);

			### parse units to a3_options
			$a3_options = array ();

			foreach ($administrative_units as $unit_id => $unit_name)
			{
				$a3_options[] = $unit_id . "=>" . $unit_name;
			}

			$a3_options = implode ("\n", $a3_options);
		}
		else
		{
			$a3_options = '=> ';
		}

		$charset = aw_global_get("charset");
		header ("Content-Type: text/html; charset=" . $charset);
		echo $a3_options;
		exit;
	}
}
?>
