<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_search.aw,v 1.14 2006/04/21 11:41:39 voldemar Exp $
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

	@property searchform_columns type=textbox datatype=int default=2
	@comment Mtimes tulbas otsingu vormielemente kuvada.
	@caption Otsinguvormi tulpade arv

	@property searchform_pagesize type=textbox datatype=int default=25
	@caption Otsingutulemusi lehel

	@property realestate_mgr type=relpicker reltype=RELTYPE_OWNER clid=CL_REALESTATE_MANAGER automatic=1
	@comment Kinnisvarahalduskeskkond, mille objektide hulgast otsida soovitakse
	@caption Kinnisvarahalduskeskkond

	@property administrative_structure type=relpicker reltype=RELTYPE_ADMINISTRATIVE_STRUCTURE clid=CL_COUNTRY_ADMINISTRATIVE_STRUCTURE automatic=1
	@caption Haldusjaotus

	@property save_search type=checkbox ch_value=1
	@caption Salvesta otsingutulemus

	@property sort_by_options type=hidden


@default group=grp_search
	@property search_class_id type=select multiple=1 size=5
	@caption Objekt

	@property search_city24_id type=textbox datatype=int size=10
	@caption City24 ID

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

	@property searchparam_sort_by type=select
	@caption Järjestus

	@property searchparam_sort_order type=select
	@caption Järjestuse suund

	@property search_button type=submit store=no
	@caption Otsi

	@property searchresult type=table store=no
	@caption Otsingutulemused


@default group=grp_formdesign
	@property formelements type=chooser multiple=1 orient=vertical
	@caption Otsinguparameetrid

	@property formdesign_sort_options type=select multiple=1 size=5
	@comment Objektiatribuudid, mida näidatakse sortimise valikus
	@caption Järjestamise valik

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
	var $result_table_recordsperpage = 25;
	var $search_sort_options = array ();
	var $search_sort_orders = array ();

	function realestate_search ()
	{
		$this->init (array (
			"tpldir" => "applications/realestate_management/realestate_search",
			"clid" => CL_REALESTATE_SEARCH,
		));
		$this->search_sort_options = array (
			"name" => array ("caption" => t("Nimi"), "table" => "objects"),
			"class_id" => array ("caption" => t("Objekti tüüp"), "table" => "objects"),
			"created" => array ("caption" => t("Loodud"), "table" => "objects"),
			"modified" => array ("caption" => t("Muudetud"), "table" => "objects"),
		);
		$this->search_sort_orders = array (
			"ASC" => t("Kasvav"),
			"DESC" => t("Kahanev"),
		);
	}

	function callback_on_load ($arr)
	{
		if (is_oid ($arr["request"]["id"]))
		{
			$this_object = obj ($arr["request"]["id"]);

			if ($this->can ("view", $this_object->prop ("realestate_mgr")))
			{
				$this->realestate_manager = obj ($this_object->prop ("realestate_mgr"));
			}

			if (is_oid ($this_object->prop ("administrative_structure")))
			{
				$this->administrative_structure = obj ($this_object->prop ("administrative_structure"));
			}
		}

		$this->classificator = get_instance(CL_CLASSIFICATOR);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		if ($prop["group"] == "grp_search" and !is_object ($this->realestate_manager))
		{
			if ($this->can ("view", $this_object->prop ("realestate_mgr")))
			{
				$this->realestate_manager = obj ($this_object->prop ("realestate_mgr"));
			}

			if ($prop["group"] == "grp_search" and !is_object ($this->realestate_manager))
			{
				$prop["error"] = t("Kinnisvarahalduskeskkond määramata");
				return PROP_FATAL_ERROR;
			}
		}

		if ($prop["group"] == "grp_search" and !is_object ($this->administrative_structure))
		{
			if (is_oid ($this_object->prop ("administrative_structure")))
			{
				$this->administrative_structure = obj ($this_object->prop ("administrative_structure"));
			}

			if ($prop["group"] == "grp_search" and !is_object ($this->administrative_structure))
			{
				$prop["error"] = t("Haldusjaotus määramata");
				return PROP_FATAL_ERROR;
			}
		}

		if (!is_object($this->classificator))
		{
			$this->classificator = get_instance(CL_CLASSIFICATOR);
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
				$prop["options"] = is_object($options) ? $options->names() : array();
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

			case "search_city24_id":
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["c24id"];
				break;

			case "searchparam_address1":
				$list =& $this->administrative_structure->prop (array (
					"prop" => "units_by_division",
					"division" => $this->realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_1"),
				));
				$options = is_object ($list) ? $list->names () : array (); ### maakond
				$prop["options"] = $options;
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["a1"];
				break;

			case "searchparam_address2":
				$list =& $this->administrative_structure->prop (array (
					"prop" => "units_by_division",
					"division" => $this->realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_2"),
				));
				$options = is_object ($list) ? $list->names () : array (); ### linn
				$prop["options"] = $options;
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["a2"];
				break;

			case "searchparam_address3":
				$list =& $this->administrative_structure->prop (array (
					"prop" => "units_by_division",
					"division" => $this->realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_3"),
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
				$prop["options"] = is_object($options) ? $options->names() : array();
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["c"];
				break;

			case "search_usage_purpose":
				$prop_args = array (
					"clid" => CL_REALESTATE_COMMERCIAL,
					"name" => "usage_purpose",
				);
				list ($options, $name, $use_type) = $this->classificator->get_choices($prop_args);
				// $prop["options"] = array("" => "") + $options->names();
				$prop["options"] = is_object($options) ? $options->names() : array();
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["up"];
				break;

			case "search_special_status":
				$prop_args = array (
					"clid" => CL_REALESTATE_HOUSE,
					"name" => "special_status",
				);
				list ($options, $name, $use_type) = $this->classificator->get_choices($prop_args);
				$prop["options"] = is_object($options) ? array("" => "") + $options->names() : array();
				// $prop["options"] = $options->names();
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["ss"];
				break;

			case "search_agent":
				$sections = $this_object->prop ("agent_sections");

				if (is_array ($sections))
				{
					$options = array ();
					aw_switch_user (array ("uid" => $this->realestate_manager->prop ("almightyuser")));

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

			case "searchparam_sort_by":
			case "formdesign_sort_options":
				if ($prop["name"] == "formdesign_sort_options")
				{
					$prop["value"] = array_keys ($prop["value"]);
				}
				elseif ($prop["name"] == "searchparam_sort_by")
				{
					$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["sort_by"];
				}

				if (!is_object ($this->cl_cfgu))
				{
					$this->cl_cfgu = get_instance("cfg/cfgutils");
				}

				if (!$this->realestate_object_properties)
				{
					$this->realestate_object_properties = $this->cl_cfgu->load_properties(array ("clid" => CL_REALESTATE_SEARCH));
				}

				foreach ($this->search_sort_options as $prop_name => $prop_data)
				{
					$options[$prop_name] = $prop_data["caption"];
				}

				foreach ($this->realestate_object_properties as $prop_name => $prop_data)
				{
					if ($prop_data["table"] == "realestate_property")
					{
						$options[$prop_name] = $prop_data["caption"];
					}
				}

				$prop["options"] = $options;
				break;

			case "searchparam_sort_order":
				$prop["options"] = $this->search_sort_orders;
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["sort_ord"];
				break;

			case "searchresult":
				$table =& $prop["vcl_inst"];
				$this->_init_properties_list ($table);
				break;

			case "formelements":
				$options = array ();

				if (!is_object ($this->cl_cfgu))
				{
					$this->cl_cfgu = get_instance("cfg/cfgutils");
				}

				if (!$this->realestate_object_properties)
				{
					$this->realestate_object_properties = $this->cl_cfgu->load_properties(array ("clid" => CL_REALESTATE_SEARCH));
				}

				$applicable_properties = array (
					"search_class_id",
					"search_city24_id",
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
					"searchparam_sort_by",
					"searchparam_sort_order",
					"search_agent",
				);

				foreach ($this->realestate_object_properties as $name => $property_data)
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
				$list = new object_list ($this->realestate_manager->connections_from(array(
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

			case "sort_by_options":
				return PROP_IGNORE;
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
			case "formdesign_sort_options":
			case "searchparam_sort_by":
				### save available options for web search.
				if (!$this->search_sort_options_loaded)
				{
					$this->cl_cfgu = get_instance("cfg/cfgutils");
					$properties = $this->cl_cfgu->load_properties(array ("clid" => CL_REALESTATE_PROPERTY));

					foreach ($properties as $prop_name => $prop_data)
					{
						if ($prop_data["table"] == "realestate_property")
						{
							$this->search_sort_options[$prop_name] = array ("caption" => $prop_data["caption"], "table" => "realestate_property");
						}
					}

					$this->search_sort_options_loaded = true;
				}

				if ($prop["name"] == "formdesign_sort_options")
				{
					$selection = array ();

					foreach ($this->search_sort_options as $prop_name => $prop_data)
					{
						if (in_array ($prop_name, $prop["value"]))
						{
							$selection[$prop_name] = $prop_data["caption"];
						}
					}

					$prop["value"] = $selection;
				}
				elseif ($prop["name"] == "searchparam_sort_by")
				{
					$prop["value"] = $this->search_sort_options;
				}
				break;
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
		enter_function("re_search::show");

		if (is_oid ($_GET["realestate_show_property"]))
		{
			return $this->show_property ($arr);
		}

		enter_function("re_search::show - init & generate form & search");
		$this_object = obj ($arr["id"]);
		$visible_formelements = (array) $this_object->prop ("formelements");
		$this->result_table_recordsperpage = (int) $this_object->prop ("searchform_pagesize");

		if (is_oid ($this_object->prop ("realestate_mgr")) and !is_object ($this->realestate_manager))
		{
			$this->realestate_manager = obj ($this_object->prop ("realestate_mgr"));
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
			$saved_search = true;
			$args = array (
				"realestate_srch" => 1,
				"ci" => $this_object->prop ("search_class_id"),
				"c24id" => $this_object->prop ("search_city24_id"),
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
				"sort_by" => $this_object->prop ("searchparam_sort_by"),
				"sort_ord" => $this_object->prop ("searchparam_sort_order"),
			);
		}
		else
		{
			$saved_search = false;
			$args = array (
				"realestate_srch" => $_GET["realestate_srch"],
				"ci" => $_GET["realestate_ci"],
				"c24id" => $_GET["realestate_c24id"],
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
				"sort_by" => $_GET["sort_by"],
				"sort_ord" => $_GET["sort_ord"],
			);
		}

		$search = $this->get_search_args ($args, $this_object);

		if (!$this_object->prop ("result_no_form"))
		{
			### captions
			$properties = $this_object->get_property_list ();

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
					"value" => ($saved_search and is_array ($search["ci"])) ? NULL : $search["ci"],
				));
			}

			if (in_array ("search_city24_id", $visible_formelements))
			{
				$form_elements["c24id"]["caption"] = $properties["search_city24_id"]["caption"];
				$form_elements["c24id"]["element"] = html::textbox(array(
					"name" => "realestate_c24id",
					"value" => $search["c24id"],
					"size" => "6",
					// "textsize" => "11px",
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
					"value" => ($saved_search and is_array ($search["tt"])) ? NULL : $search["tt"],
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
				if (in_array ("searchparam_address2", $visible_formelements))
				{
					if (!is_object ($this->division2))
					{
						$this->division2 = $this->realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_2");
					}

					$a2_division_id = $this->division2->id ();
				}

				$form_elements["a1"]["caption"] = $properties["searchparam_address1"]["caption"];
				$form_elements["a1"]["element"] = html::select(array(
					"name" => "realestate_a1",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $this->options_a1,
					"value" => ($saved_search and is_array ($search["a1"])) ? NULL : $search["a1"],
					"onchange" => (in_array ("searchparam_address2", $visible_formelements) ? "reChangeSelection(this, false, 'realestate_a2', '{$a2_division_id}');" : NULL),
				));
			}

			if (in_array ("searchparam_address2", $visible_formelements))
			{
				if (in_array ("searchparam_address1", $visible_formelements))
				{
					$options = array(REALESTATE_SEARCH_ALL => t("Kõik linnad"));
					$options = !empty ($search["a2"]) ? array (reset ($search["a2"]) => $this->options_a2[reset ($search["a2"])]) + $options : $options;
				}
				else
				{
					$options = $this->options_a2;
				}

				if (in_array ("searchparam_address3", $visible_formelements))
				{
					if (!is_object ($this->division3))
					{
						$this->division3 = $this->realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_3");
					}

					$a3_division_id = $this->division3->id ();
				}

				$form_elements["a2"]["caption"] = $properties["searchparam_address2"]["caption"];
				$form_elements["a2"]["element"] = html::select(array(
					"name" => "realestate_a2",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $options,
					"value" => ($saved_search and is_array ($search["a2"])) ? NULL : $search["a2"],
					"onchange" => (in_array ("searchparam_address3", $visible_formelements) ? "reChangeSelection(this, false, 'realestate_a3', '{$a3_division_id}');" : NULL),
				));
			}

			if (in_array ("searchparam_address3", $visible_formelements))
			{
				if (in_array ("searchparam_address2", $visible_formelements))
				{
					$options = array(REALESTATE_SEARCH_ALL => t("Kõik linnaosad"));
					$options = !empty ($search["a3"]) ? array (reset ($search["a3"]) => $this->options_a3[reset ($search["a3"])]) + $options : $options;
				}
				else
				{
					$options = $this->options_a3;
				}

				$form_elements["a3"]["caption"] = $properties["searchparam_address3"]["caption"];
				$form_elements["a3"]["element"] = html::select(array(
					"name" => "realestate_a3",
					"multiple" => $select_size,
					"size" => $select_size,
					"options" => $options,
					"value" => ($saved_search and is_array ($search["a3"])) ? NULL : $search["a3"],
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
					"value" => ($saved_search and is_array ($search["c"])) ? NULL : $search["c"],
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
					"value" => ($saved_search and is_array ($search["up"])) ? NULL : $search["up"],
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
					"value" => ($saved_search and is_array ($search["agent"])) ? NULL : $search["agent"],
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
					"value" => ($saved_search and is_array ($search["ss"])) ? NULL : $search["ss"],
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

			if (in_array ("searchparam_sort_by", $visible_formelements))
			{
				$form_elements["sort_by"]["caption"] = $properties["searchparam_sort_by"]["caption"];
				$form_elements["sort_by"]["element"] = html::select(array(
					"name" => "realestate_sort_by",
					"options" => $this_object->prop ("formdesign_sort_options"),
					"value" => $search["sort_by"],
				));
			}

			if (in_array ("searchparam_sort_order", $visible_formelements))
			{
				$form_elements["sort_ord"]["caption"] = $properties["searchparam_sort_order"]["caption"];
				$form_elements["sort_ord"]["element"] = html::select(array(
					"name" => "realestate_sort_ord",
					"options" => $this->search_sort_orders,
					"value" => $search["sort_ord"],
				));
			}
		}

		if ($_GET["realestate_srch"] == 1 or $this_object->prop ("save_search"))
		{ ### search
			$args = array (
				"this" => $this_object,
				"search" => $search,
			);
			$list =& $this->search ($args);
			$search_requested = true;
		}
		else
		{
			$list = new object_list ();
			$search_requested = false;
		}

		exit_function("re_search::show - init & generate form & search");
		enter_function("re_search::show - process searchresults");

		$result_count = $list->count ();

		if ($result_count)
		{ ### result
			classload ("vcl/table");
			$table = new vcl_table ();
			$classes = aw_ini_get ("classes");

			switch ($this_object->prop ("result_format"))
			{
				case "format1":
					### leave only objects for requested page in list
					$table->set_layout("realestate_searchresult");
					$table->define_field(array(
						"name" => "object",
						"caption" => NULL,
					));

					if ($this->result_count > $this->result_table_recordsperpage)
					{
						$table->define_pageselector (array (
							"type" => "text",
							"d_row_cnt" => $this->result_count,
							"no_recount" => true,
							"records_per_page" => $this->result_table_recordsperpage,
						));
					}

					foreach ($this->realestate_classes as $cls_id)
					{
						$cl_instance_var = "cl_property_" . $cls_id;

						if (!is_object ($this->$cl_instance_var))
						{
							$this->$cl_instance_var = get_instance ($cls_id);
							$this->$cl_instance_var->classes = $classes;
						}
					}

					$property = $list->begin ();

					while (is_object ($property))
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
						$property = $list->next ();
					}

					// foreach ($list as $property)
					// {
						// $cl_instance_var = "cl_property_" . $property->class_id ();
						// $object_html = $this->$cl_instance_var->view (array (
							// "this" => $property,
							// "view_type" => "short",
						// ));

						// $data = array (
							// "object" => $object_html,
 						// );
						// $table->define_data ($data);
					// }
					break;
			}

			$result = $table->get_html ();
		}

		exit_function("re_search::show - process searchresults");
		enter_function("re_search::show - parse");

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
				// "number_of_results" => $result_count ? t("Otsinguparameetritele vastavaid objekte ei leitud") : sprintf (t("Leitud %s objekti"), $result_count),
			));
		}
		else
		{
			$el_count = count ($form_elements);
			$column_count = $this_object->prop ("searchform_columns");
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
				"a1_element_id" => "realestate_a1",
				"a2_element_id" => "realestate_a2",
				"a3_element_id" => "realestate_a3",
				"a2_division" => $a2_division_id,
				"a3_division" => $a3_division_id,
			));
			$form = $this->parse ("RE_SEARCHFORM");

			$options_url = $this->mk_my_orb ("get_select_options", array (
				"id" => $this_object->id (),
			), CL_REALESTATE_SEARCH, false, true);

			### search result count
			$number_of_results = "";

			if ($search_requested)
			{
				$number_of_results = (0 < $this->result_count) ? sprintf (t("Leitud %s objekti"), $this->result_count) : t("Otsinguparameetritele vastavaid objekte ei leitud");
			}

			$this->vars (array (
				"RE_SEARCHFORM" => $form,
				"table_style" => $table_style,
				"result" => $result,
				"options_url" => $options_url,
				"number_of_results" => $number_of_results,
			));
		}

		$result = $this->parse();
		exit_function("re_search::show - parse");
		exit_function("re_search::show");
		return $result;
	}

	function get_options ($arr)
	{
		enter_function ("re_search::get_options");
		$this_object = obj ($arr["id"]);

		if (!is_object ($this->classificator))
		{
			$this->classificator = get_instance(CL_CLASSIFICATOR);
		}

		if (!is_object ($this->realestate_manager))
		{
			if (is_oid ($this_object->prop ("realestate_mgr")))
			{
				$this->realestate_manager = obj ($this_object->prop ("realestate_mgr"));
			}

			if (!is_object ($this->realestate_manager))
			{
				echo t("Kinnisvarahalduskeskkond otsinguobjektil defineerimata.") . NEWLINE;
				return false;
			}
		}


		if (!is_object ($this->administrative_structure))
		{
			if (is_oid ($this_object->prop ("administrative_structure")))
			{
				$this->administrative_structure = obj ($this_object->prop ("administrative_structure"));
			}

			if (!is_object ($this->administrative_structure))
			{
				if (is_oid ($this->realestate_manager->prop ("administrative_structure")))
				{
					$this->administrative_structure = obj ($this->realestate_manager->prop ("administrative_structure"));
				}

				if (!is_object ($this->administrative_structure))
				{
					echo t("Haldusjaotus otsinguobjektil ja kinnisvarahalduskeskkonnas defineerimata.") . NEWLINE;
					return false;
				}
			}
		}

		### class_id
		$this->options_ci = array (
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
		$this->options_ci = array(REALESTATE_SEARCH_ALL => t("Kõik objektid")) + $this->options_ci;

		### transaction_type
		$prop_args = array (
			"clid" => CL_REALESTATE_PROPERTY,
			"name" => "transaction_type",
		);
		list ($options_tt, $name, $use_type) = $this->classificator->get_choices($prop_args);
		$this->options_tt = $options_tt->names();
		natcasesort ($this->options_tt);
		$this->options_tt = array(REALESTATE_SEARCH_ALL => t("Kõik tehingud")) + $this->options_tt;

		### address1
		$list =& $this->administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $this->realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_1"),
		));
		$this->options_a1 = is_object ($list) ? $list->names () : array (); // maakond;
		natcasesort ($this->options_a1);
		$this->options_a1 = array(REALESTATE_SEARCH_ALL => t("Kõik maakonnad")) + $this->options_a1;

		### to save time, get only a minimal set of options for elementary web search
		if ($arr["get_minimal_set"])
		{
			return;
		}

		### address2
		$list =& $this->administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $this->realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_2"),
		));
		$this->options_a2 = is_object ($list) ? $list->names () : array (); // linn;
		natcasesort ($this->options_a2);
		$this->options_a2 = array(REALESTATE_SEARCH_ALL => t("Kõik linnad")) + $this->options_a2;

		### address3
		if (!is_object ($this->division3))
		{
			$this->division3 = $this->realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_3");
		}

		$list =& $this->administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $this->division3,
		));
		$this->options_a3 = is_object ($list) ? $list->names () : array (); // linnaosa;
		natcasesort ($this->options_a3);
		$this->options_a3 = array(REALESTATE_SEARCH_ALL => t("Kõik linnaosad")) + $this->options_a3;

		### condition
		$prop_args = array (
			"clid" => CL_REALESTATE_HOUSE,
			"name" => "condition",
		);
		list ($options_c, $name, $use_type) = $this->classificator->get_choices($prop_args);
		$this->options_c = $options_c->names();
		natcasesort ($this->options_c);
		$this->options_c = array(REALESTATE_SEARCH_ALL => t("Kõik valmidused")) + $this->options_c;

		### usage_purpose
		$prop_args = array (
			"clid" => CL_REALESTATE_COMMERCIAL,
			"name" => "usage_purpose",
		);
		list ($options_up, $name, $use_type) = $this->classificator->get_choices($prop_args);
		$this->options_up = $options_up->names();
		natcasesort ($this->options_up);
		$this->options_up = array(REALESTATE_SEARCH_ALL => t("Kõik tüübid")) + $this->options_up;

		### special_status
		$prop_args = array (
			"clid" => CL_REALESTATE_HOUSE,
			"name" => "special_status",
		);
		list ($options_ss, $name, $use_type) = $this->classificator->get_choices($prop_args);
		$this->options_ss = $options_ss->names();
		natcasesort ($this->options_ss);
		$this->options_ss = array(REALESTATE_SEARCH_ALL => t("Kõik")) + $this->options_ss;

		### agent
		$sections = $this_object->prop ("agent_sections");
		$options = array ();

		if (is_array ($sections))
		{
			aw_switch_user (array ("uid" => $this->realestate_manager->prop ("almightyuser")));

			foreach ($sections as $section_oid)
			{
				if (is_oid ($section_oid))
				{
					// 1
					// $section = obj ($section_oid);
					// $employees = new object_list ($section->connections_from(array(// teeb iga objekti jaoks loadi
						// "type" => "RELTYPE_WORKERS",
						// "class_id" => CL_CRM_PERSON,
					// )));
					// END 1

					// 2
					$connection = new connection ();
					$section_connections = $connection->find (array (
						"from" => $section_oid,
						"type" => 2,
					));

					foreach ($section_connections as $connection)
					{
						$employee_ids[] = $connection["to"];
					}

					$employees = new object_list (array (
						"oid" => $employee_ids,
						"class_id" => CL_CRM_PERSON,
						"site_id" => array (),
						"lang_id" => array (),
					));
					// END 2

					$options = $options + $employees->names ();
				}
			}

			aw_restore_user ();
		}

		natcasesort ($options);
		$this->options_agent = array(REALESTATE_SEARCH_ALL => t("Kõik maaklerid")) + $options;
		exit_function ("re_search::get_options");
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

	function get_search_args ($arr, $this_object = NULL)
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
			$search_nor = trim($arr["nor"]) ? (int) $arr["nor"] : NULL;
			$search_c24id = trim($arr["c24id"]) ? (int) $arr["c24id"] : NULL;

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

			$options = $this_object->prop ("sort_by_options");

			if (is_array ($options))
			{
				$this->search_sort_options = $options;
			}

			$search_sort_by = array_key_exists ($arr["sort_by"], $this->search_sort_options) ? $this->search_sort_options[$arr["sort_by"]]["table"] . "" . $arr["sort_by"] : NULL;
			$search_sort_ord = array_key_exists ($arr["sort_ord"], $this->search_sort_orders) ? $arr["sort_ord"] : NULL;
		}
		else
		{
			$search_fd = (time () - 60*86400);
		}

		$args = array (
			"ci" => $search_ci,
			"c24id" => $search_c24id,
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
			"sort_by" => $search_sort_by,
			"sort_ord" => $search_sort_ord,
		);
		return $args;
	}

	function &search ($arr)
	{
		enter_function ("re_search::search");
		$this_object = $arr["this"];

		$search_ci = $arr["search"]["ci"];
		$search_c24id = $arr["search"]["c24id"];
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
		$search_sort_by = $arr["search"]["sort_by"];
		$search_sort_ord = $arr["search"]["sort_ord"];

		$list = array ();
		$parents = array ();

		enter_function ("re_search::search - process arguments & constraints");

		if (!count ($search_ci))
		{
			$search_ci = $this->realestate_classes;
		}

		foreach ($search_ci as $clid)
		{
			switch ($clid)
			{
				case CL_REALESTATE_HOUSE:
					if (is_oid ($this->realestate_manager->prop ("houses_folder")))
					{
						$parents[] = $this->realestate_manager->prop ("houses_folder");
						// $search_ci_clstr = "CL_REALESTATE_HOUSE";
					}
					break;
				case CL_REALESTATE_ROWHOUSE:
					if (is_oid ($this->realestate_manager->prop ("rowhouses_folder")))
					{
						$parents[] = $this->realestate_manager->prop ("rowhouses_folder");
						// $search_ci_clstr = "CL_REALESTATE_ROWHOUSE";
					}
					break;
				case CL_REALESTATE_COTTAGE:
					if (is_oid ($this->realestate_manager->prop ("cottages_folder")))
					{
						$parents[] = $this->realestate_manager->prop ("cottages_folder");
						// $search_ci_clstr = "CL_REALESTATE_COTTAGE";
					}
					break;
				case CL_REALESTATE_HOUSEPART:
					if (is_oid ($this->realestate_manager->prop ("houseparts_folder")))
					{
						$parents[] = $this->realestate_manager->prop ("houseparts_folder");
						// $search_ci_clstr = "CL_REALESTATE_HOUSEPART";
					}
					break;
				case CL_REALESTATE_COMMERCIAL:
					if (is_oid ($this->realestate_manager->prop ("commercial_properties_folder")))
					{
						$parents[] = $this->realestate_manager->prop ("commercial_properties_folder");
						// $search_ci_clstr = "CL_REALESTATE_COMMERCIAL";
					}
					break;
				case CL_REALESTATE_GARAGE:
					if (is_oid ($this->realestate_manager->prop ("garages_folder")))
					{
						$parents[] = $this->realestate_manager->prop ("garages_folder");
						// $search_ci_clstr = "CL_REALESTATE_GARAGE";
					}
					break;
				case CL_REALESTATE_LAND:
					if (is_oid ($this->realestate_manager->prop ("land_estates_folder")))
					{
						$parents[] = $this->realestate_manager->prop ("land_estates_folder");
						// $search_ci_clstr = "CL_REALESTATE_LAND";
					}
					break;
				case CL_REALESTATE_APARTMENT:
					if (is_oid ($this->realestate_manager->prop ("apartments_folder")))
					{
						$parents[] = $this->realestate_manager->prop ("apartments_folder");
						// $search_ci_clstr = "CL_REALESTATE_APARTMENT";
					}
					break;
			}
		}

		if (!empty ($search_agent) && $search_agent > 0)
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
			$tp_constraint = new obj_predicate_compare (OBJ_COMP_BETWEEN, $search_tpmin, $search_tpmax);
		}
		elseif ($search_tpmin)
		{
			$tp_constraint = new obj_predicate_compare (OBJ_COMP_GREATER_OR_EQ, $search_tpmin);
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
			$tfa_constraint = new obj_predicate_compare (OBJ_COMP_BETWEEN, $search_tfamin, $search_tfamax);
		}
		elseif ($search_tfamin)
		{
			$tfa_constraint = new obj_predicate_compare (OBJ_COMP_GREATER_OR_EQ, $search_tfamin);
		}
		elseif ($search_tfamax)
		{
			$tfa_constraint = new obj_predicate_compare (OBJ_COMP_LESS_OR_EQ, $search_tfamax);
		}
		else
		{
			$tfa_constraint = NULL;
		}

		### get address constraint
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

		### sorting
		$sort = $search_sort_by ? $search_sort_by . " " . ($search_sort_ord ? $search_sort_ord : "ASC") : NULL;

		### class specific arguments
		$class_specific_args = array();

		if (
			in_array(CL_REALESTATE_HOUSE, $search_ci) or
			in_array(CL_REALESTATE_HOUSEPART, $search_ci) or
			in_array(CL_REALESTATE_ROWHOUSE, $search_ci) or
			in_array(CL_REALESTATE_COTTAGE, $search_ci) or
			in_array(CL_REALESTATE_APARTMENT, $search_ci) or
			in_array(CL_REALESTATE_COMMERCIAL, $search_ci) or
			in_array(CL_REALESTATE_GARAGE, $search_ci)
		)
		{
			$class_specific_args["condition"] = $search_c;
			$class_specific_args["total_floor_area"] = $tfa_constraint;

			if (!in_array(CL_REALESTATE_GARAGE, $search_ci))
			{
				$class_specific_args["number_of_rooms"] = (empty ($search_nor) ? NULL : $search_nor);
			}
		}

		#### apartment
		if (in_array(CL_REALESTATE_APARTMENT, $search_ci))
		{
			$class_specific_args["is_middle_floor"] = (empty ($search_imf) ? NULL : $search_imf);
		}

		#### commercial
		if (in_array(CL_REALESTATE_COMMERCIAL, $search_ci))
		{
			$class_specific_args["usage_purpose"] = $search_up;
		}

		exit_function ("re_search::search - process arguments & constraints");
		enter_function ("re_search::search - get objlist");

		### search
		$args = array (
			"class_id" => $search_ci,
			"parent" => $parents,
			"created" => new obj_predicate_compare (OBJ_COMP_GREATER, $search_fd),
			"site_id" => array (),
			"lang_id" => array (),
			"transaction_type" => $search_tt,
			"transaction_price" => $tp_constraint,
			"special_status" => $search_ss,
			"is_visible" => 1,
			// "address_connection" => $address_ids,
			// $address_constraint,
			$agent_constraint,
			"sort_by" => $sort,
		);

		if ($search_c24id)
		{
			$args["city24_object_id"] = $search_c24id;
		}

		$args = $args + $class_specific_args;

		$result_list = new object_list ($args);
		// $result_list = $result_list->arr ();

		exit_function ("re_search::search - get objlist");
		enter_function ("re_search::search - address");

		### search by address
		if ($search_admin_units !== false and $result_list->count ())
		{
			$unit_classes = array (
				CL_COUNTRY_ADMINISTRATIVE_UNIT,
				CL_COUNTRY_CITY,
				CL_COUNTRY_CITYDISTRICT,
			);
			$result_list_ids = $result_list->ids ();

			### get addresses for all properties found
			$connection = new connection ();
			$address_connections = $connection->find (array (
					// "from" => array_keys ($result_list),
					"from" => $result_list_ids,
					"type" => 1,
			));

			$address_ids = array ();
			$property_index = array ();

			foreach ($address_connections as $connection)
			{
				$address_ids[$connection["from"]] = $connection["to"];
			}

			### search by adminunit
			$unit_connections = array ();

			if (count ($address_ids))
			{
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

				// foreach ($result_list as $property_oid => $property)
				// {
					// if (!in_array ($address_ids[$property_oid], $applicable_address_ids))
					// {
						// unset ($result_list[$property_oid]);
					// }
				// }

				// $o = $result_list->begin ();

				$start_offset = (int) $_GET["ft_page"] * $this->result_table_recordsperpage;
				$end_offset = $start_offset + $this->result_table_recordsperpage;
				$result_count = 0;

				foreach ($result_list_ids as $oid)
				{
					$result_count++;

					if (!in_array ($address_ids[$oid], $applicable_address_ids))
					{
						$result_list->remove ($oid);
						$result_count--;
					}
					elseif (($result_count <= $start_offset) or ($result_count > $end_offset))
					{
						$result_list->remove ($oid);
					}
				}

				$this->result_count = $result_count;
			}
		}
		else
		{
			### count all
			$this->result_count = $result_list->count ();

			### limit
			$limit = ((int) $_GET["ft_page"] * $this->result_table_recordsperpage) . "," . $this->result_table_recordsperpage;
			$args["limit"] = $limit;
			$result_list->filter ($args);
		}

		exit_function ("re_search::search - address");
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
			"d_row_cnt" => $this->result_count,
			"records_per_page" => $this->result_table_recordsperpage,
		));
	}

	function show_property ($arr)
	{
		if (!$this->can ("view", $_GET["realestate_show_property"]))
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
    @attrib name=get_select_options nologin=1
	@param id required type=int
	@param reAddressSelected optional
	@param reAddressDivision optional
	@returns List of options separated by newline (\n). Void on error.
**/
	function get_select_options ($arr)
	{
		$this_object = obj ($arr["id"]);
		$parent_value = $arr["reAddressSelected"];
		$child_division = obj ((int) $arr["reAddressDivision"]);
		$administrative_structure = $this_object->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");
		$all_selection = ($child_division->prop ("type") == CL_COUNTRY_CITY) ? t("Kõik linnad") : t("Kõik linnaosad");
		$options = array(REALESTATE_SEARCH_ALL . "=>" . $all_selection);

		if (is_oid ($parent_value) and is_object ($child_division) and is_object ($administrative_structure))
		{
			### get units
			$list =& $administrative_structure->prop (array (
				"prop" => "units_by_division",
				"division" => $child_division,
				"parent" => $parent_value,
			));
			$administrative_units = is_object ($list) ? $list->names () : array ();
			natcasesort ($administrative_units);

			### parse units to a3_options
			foreach ($administrative_units as $unit_id => $unit_name)
			{
				$options[] = $unit_id . "=>" . $unit_name;
			}
		}

		$options = implode ("\n", $options);
		$charset = aw_global_get("charset");
		header ("Content-Type: text/html; charset=" . $charset);
		echo $options;
		exit;
	}
}
?>
