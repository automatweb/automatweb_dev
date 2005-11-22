<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_search.aw,v 1.4 2005/11/22 16:50:49 voldemar Exp $
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

	@property searchform_select_size type=textbox datatype=int
	@comment [0] - võimalus valida parameetrile vaid üks väärtus, [1 - ...] - võimalus valida mitu.
	@caption Otsinguvormi valikuelementide suurus

	@property realestate_mgr type=relpicker reltype=RELTYPE_OWNER clid=CL_REALESTATE_MANAGER automatic=1
	@comment Kinnisvarahalduskeskkond, mille objektide hulgast otsida soovitakse
	@caption Kinnisvarahalduskeskkond

	@property searchparam_country type=relpicker reltype=RELTYPE_COUNTRY clid=CL_COUNTRY automatic=1
	@caption Riik

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


// --------------- RELATION TYPES ---------------------

@reltype OWNER clid=CL_REALESTATE_MANAGER value=1
@caption Kinnisvaraobjektide halduskeskkond

@reltype COUNTRY clid=CL_COUNTRY value=2
@caption Riik

*/


define ("REALESTATE_SEARCH_ALL", "ALL");

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
			$this_object = obj($arr["request"]["id"]);

			if (is_oid ($this_object->prop ("realestate_mgr")) and $this->can ("view", $this_object->prop ("realestate_mgr")))
			{
				$this->realestate_mgr = obj ($this_object->prop ("realestate_mgr"));
			}
		}

		$this->classificator = get_instance(CL_CLASSIFICATOR);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		if (!is_object ($this->classificator))
		{
			$this->classificator = get_instance(CL_CLASSIFICATOR);
		}

		if (!is_object ($this->realestate_mgr))
		{
			if (is_oid ($this_object->prop ("realestate_mgr")) and $this->can ("view", $this_object->prop ("realestate_mgr")))
			{
				$this->realestate_mgr = obj ($this_object->prop ("realestate_mgr"));
				$this->administrative_structure = $realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");
			}
		}

		if ($prop["group"] == "grp_search" and !is_object ($this->realestate_mgr))
		{
			$prop["error"] = t("Kinnisvarahalduskeskkond määramata");
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
					"division" => $this->realestate_mgr->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_1"),
				));
				$options = is_object ($list) ? $list->names () : array (); ### maakond
				$prop["options"] = $options;
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["a1"];
				break;

			case "searchparam_address2":
				$list =& $this->administrative_structure->prop (array (
					"prop" => "units_by_division",
					"division" => $this->realestate_mgr->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_2"),
				));
				$options = is_object ($list) ? $list->names () : array (); ### linn
				$prop["options"] = $options;
				$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["a2"];
				break;

			case "searchparam_address3":
				$list =& $this->administrative_structure->prop (array (
					"prop" => "units_by_division",
					"division" => $this->realestate_mgr->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_3"),
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

			case "search_agent":
				$cl_user = get_instance(CL_USER);
				$company = $cl_user->get_current_company ();

				if (is_object ($company))
				{
					$employees = new object_list($company->connections_from(array(
						"type" => "RELTYPE_WORKERS",
						"class_id" => CL_CRM_PERSON,
					)));
					$prop["options"] = $employees->names ();
					$prop["value"] = (!$_GET["realestate_srch"] and $this_object->prop ("save_search")) ? $prop["value"] : $_GET["realestate_search"]["agent"];
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

		$this_object = obj ($arr["id"]);
		$formelements = $this_object->prop ("formelements");
		$realestate_manager = obj ($this_object->prop ("realestate_mgr"));

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
				"owp" => $_GET["realestate_owp"],
			);
		}
		$search = $this->get_search_args ($args);

		### form
		$select_size = (int) $this_object->prop ("searchform_select_size");

		$form_ci = in_array ("search_class_id", $formelements) ?
		html::select(array(
			"name" => "realestate_ci",
			"multiple" => $select_size,
			"size" => $select_size,
			"options" => $this->options_ci,
			"value" => $search["ci"],
		)) : "";
		$form_tt = in_array ("search_transaction_type", $formelements) ?
		html::select(array(
			"name" => "realestate_tt",
			"multiple" => $select_size,
			"size" => $select_size,
			"options" => $this->options_tt,
			"value" => $search["tt"],
		)) : "";
		$form_tpmin = in_array ("search_transaction_price_min", $formelements) ?
		html::textbox(array(
			"name" => "realestate_tpmin",
			"value" => empty ($search["tpmin"]) ? "" : $search["tpmin"],
			"size" => "6",
			// "textsize" => "11px",
		)) : "";
		$form_tpmax = in_array ("search_transaction_price_max", $formelements) ?
		($form_tpmin ? " - " : "") . html::textbox(array(
			"name" => "realestate_tpmax",
			"value" => empty ($search["tpmax"]) ? "" : $search["tpmax"],
			"size" => "6",
			// "textsize" => "11px",
		)) : "";
		$form_tfamin = in_array ("search_total_floor_area_min", $formelements) ?
		html::textbox(array(
			"name" => "realestate_tfamin",
			"value" => empty ($search["tfamin"]) ? "" : $search["tfamin"],
			"size" => "6",
			// "textsize" => "11px",
		)) : "";
		$form_tfamax = in_array ("search_total_floor_area_max", $formelements) ?
		($form_tfamin ? " - " : "") . html::textbox(array(
			"name" => "realestate_tfamax",
			"value" => empty ($search["tfamax"]) ? "" : $search["tfamax"],
			"size" => "6",
			// "textsize" => "11px",
		)) : "";
		$form_nor = in_array ("search_number_of_rooms", $formelements) ?
		html::textbox(array(
			"name" => "realestate_nor",
			"value" => $search["nor"],
			"size" => "6",
			// "textsize" => "11px",
		)) : "";
		$form_a1 = in_array ("searchparam_address1", $formelements) ?
		html::select(array(
			"name" => "realestate_a1",
			"multiple" => $select_size,
			"size" => $select_size,
			"options" => $this->options_a1,
			"value" => $search["a1"],
		)) : "";
		$form_a2 = in_array ("searchparam_address2", $formelements) ?
		html::select(array(
			"name" => "realestate_a2",
			"multiple" => $select_size,
			"size" => $select_size,
			"options" => $this->options_a2,
			"value" => $search["a2"],
		)) : "";
		$form_a3 = in_array ("searchparam_address3", $formelements) ?
		html::select(array(
			"name" => "realestate_a3",
			"multiple" => $select_size,
			"size" => $select_size,
			"options" => $this->options_a3,
			"value" => $search["a3"],
		)) : "";
		$form_at = in_array ("searchparam_addresstext", $formelements) ?
		html::textbox(array(
			"name" => "realestate_at",
			"value" => $search["at"],
			"size" => "16",
			// "textsize" => "11px",
		)) : "";
		$form_fd = in_array ("searchparam_fromdate", $formelements) ?
		html::date_select(array(
			"name" => "realestate_fd",
			"mon_for" => 1,
			"value" => $search["fd"],
			// "textsize" => "11px",
		)) : "";
		$form_c = in_array ("search_condition", $formelements) ?
		html::select(array(
			"name" => "realestate_c",
			"multiple" => $select_size,
			"size" => $select_size,
			"options" => $this->options_c,
			"value" => $search["c"],
		)) : "";
		$form_up = in_array ("search_usage_purpose", $formelements) ?
		html::select(array(
			"name" => "realestate_up",
			"multiple" => $select_size,
			"size" => $select_size,
			"options" => $this->options_up,
			"value" => $search["up"],
		)) : "";
		// $form_agent = in_array ("search_agent", $formelements) ?
		// html::select(array(
			// "name" => "realestate_agent",
			// "multiple" => $select_size,
			// "size" => $select_size,
			// "options" => $this->options_agent,
			// "value" => $search["agent"],
		// )) : "";
		$form_agent = in_array ("search_agent", $formelements) ?
		html::textbox(array(
			"name" => "realestate_agent",
			"value" => $search["agent"],
			"size" => "16",
			// "textsize" => "11px",
		)) : "";
		$form_imf = in_array ("search_is_middle_floor", $formelements) ?
		html::checkbox(array(
			"name" => "realestate_imf",
			"value" => 1,
			"checked" => $search["imf"],
		)) : "";
		$form_owp = in_array ("searchparam_onlywithpictures", $formelements) ?
		html::checkbox(array(
			"name" => "realestate_owp",
			"value" => 1,
			"checked" => $search["owp"],
		)) : "";

		if ( ($_GET["realestate_srch"] == 1) and $this->can ("view", $this_object->prop ("realestate_mgr")) )
		{ ### search
			$args = array (
				"manager" => obj ($this_object->prop ("realestate_mgr")),
				"search" => $search,
			);
			$list =& $this->search ($args);
		}
		elseif ($this_object->prop ("save_search"))
		{
			$args = array (
				"id" => $this_object->id (),
				"manager" => obj ($this_object->prop ("realestate_mgr")),
			);
			$list =& $this->search ($args);
		}
		else
		{
			$list = array ();
		}

		if (count ($list))
		{ ### result
			classload("vcl/table");
			$table = new vcl_table();
			$classes = aw_ini_get("classes");

			switch ($this_object->prop ("result_format"))
			{
				case "format1":
					$template = obj ($realestate_manager->prop ("template_obj_search_result"));
					$tpl_source = $template->prop ("source_html");
					$table->set_layout("realestate_searchresult");
					$table->define_field(array(
						"name" => "object",
						"caption" => NULL,
					));
					$table->define_pageselector (array (
						"type" => "text",
						"records_per_page" => 25,
					));

					foreach ($list as $property)
					{
						$cl_property = get_instance($property->class_id ());
						$object_html = $cl_property->view (array (
							"id" => $property->id (),
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

		### captions
		$cl_cfgu = get_instance("cfg/cfgutils");
		$properties = $cl_cfgu->load_properties(array ("clid" => CL_REALESTATE_SEARCH));

		### style
		$template = $this_object->prop ("template") . ".css";
		$this->read_template($template);
		$this->vars (array());
		$table_style = $this->parse ();

		### output
		$template = $this_object->prop ("template") . ".tpl";
		$this->read_template($template);
		$this->vars(array(
			"form_ci" => $form_ci,
			"form_tt" => $form_tt,
			"form_tpmin" => $form_tpmin,
			"form_tpmax" => $form_tpmax,
			"form_tfamin" => $form_tfamin,
			"form_tfamax" => $form_tfamax,
			"form_nor" => $form_nor,
			"form_a1" => $form_a1,
			"form_a2" => $form_a2,
			"form_a3" => $form_a3,
			"form_at" => $form_at,
			"form_fd" => $form_fd,
			"form_up" => $form_up,
			"form_agent" => $form_agent,
			"form_c" => $form_c,
			"form_imf" => $form_imf,
			"form_owp" => $form_owp,
			"caption_ci" => $form_ci ? $properties["search_class_id"]["caption"] : "",
			"caption_tt" => $form_tt ? $properties["search_transaction_type"]["caption"] : "",
			"caption_tpmin" => $form_tpmin ? $properties["search_transaction_price_min"]["caption"] : "",
			"caption_tpmax" => $form_tpmax ? ($form_tpmin ? " - " : "") . $properties["search_transaction_price_max"]["caption"] : "",
			"caption_tfamin" => $form_tfamin ? $properties["search_total_floor_area_min"]["caption"] : "",
			"caption_tfamax" => $form_tfamax ? ($form_tfamin ? " - " : "") . $properties["search_total_floor_area_max"]["caption"] : "",
			"caption_nor" => $form_nor ? $properties["search_number_of_rooms"]["caption"] : "",
			"caption_a1" => $form_a1 ? $properties["searchparam_address1"]["caption"] : "",
			"caption_a2" => $form_a2 ? $properties["searchparam_address2"]["caption"] : "",
			"caption_a3" => $form_a3 ? $properties["searchparam_address3"]["caption"] : "",
			"caption_at" => $form_at ? $properties["searchparam_addresstext"]["caption"] : "",
			"caption_fd" => $form_fd ? $properties["searchparam_fromdate"]["caption"] : "",
			"caption_up" => $form_up ? $properties["search_usage_purpose"]["caption"] : "",
			"caption_agent" => $form_agent ? $properties["search_agent"]["caption"] : "",
			"caption_c" => $form_c ? $properties["search_condition"]["caption"] : "",
			"caption_imf" => $form_imf ? $properties["search_is_middle_floor"]["caption"] : "",
			"caption_owp" => $form_owp ? $properties["searchparam_onlywithpictures"]["caption"] : "",
			"buttondisplay" => count ($formelements) ? "block" : "none",
			"table_style" => $table_style,
			"result" => $result,
			"number_of_results" => count ($list),
		));
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
			echo t("Kinnisvarahalduskeskkond otsinguobjektil defineerimata.");
		}

		$administrative_structure = $realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADMINISTRATIVE_STRUCTURE");

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
		$list =& $administrative_structure->prop (array (
			"prop" => "units_by_division",
			"division" => $realestate_manager->get_first_obj_by_reltype ("RELTYPE_ADDRESS_EQUIVALENT_3"),
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

		if ($arr["get_agent_options"])
		{
			### agent
			$cl_user = get_instance(CL_USER);
			$company = $cl_user->get_current_company ();
			$list = new object_list($realestate_manager->connections_from(array(
				"type" => "RELTYPE_REALESTATEMGR_USER",
				"class_id" => CL_CRM_COMPANY,
			)));
			$companies = $list->arr ();
			$this->options_agent = array(REALESTATE_SEARCH_ALL => "");
			$options = array ();

			if (is_object ($company) and array_key_exists ($company->id (), $companies))
			{
				$employees = new object_list($company->connections_from(array(
					"type" => "RELTYPE_WORKERS",
					"class_id" => CL_CRM_PERSON,
				)));
				$employees = $employees->arr ();

				foreach ($employees as $employee)
				{
					if ($this->agent_has_realestate_properties ($employee))
					{
						$options[$employee->id ()] = $employee->name ();
					}
				}
			}
			else
			{ ### get agents for all companies in this realestatemgr
				foreach ($companies as $company)
				{
					$employees = new object_list($company->connections_from(array(
						"type" => "RELTYPE_WORKERS",
						"class_id" => CL_CRM_PERSON,
					)));
					$employees = $employees->arr ();

					foreach ($employees as $employee)
					{
						if ($this->agent_has_realestate_properties ($employee))
						{
							$options[$employee->id ()] = $employee->name ();
						}
					}
				}
			}

			natcasesort ($options);
			$this->options_agent += $options;
		}
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
			"agent" => $search_agent,
			"c" => $search_c,
			"imf" => $search_imf,
			"owp" => $search_owp,
		);
		return $args;
	}

	function &search ($arr)
	{
		$this_object = is_oid ($arr["id"]) ? obj ($arr["id"]) : NULL;

		if (is_object ($this_object) and $this_object->prop ("save_search"))
		{
			$search_ci = $this_object->prop ("search_class_id");
			$search_tpmin = $this_object->prop ("search_transaction_price_min");
			$search_tpmax = $this_object->prop ("search_transaction_price_max");
			$search_tfamin = $this_object->prop ("search_total_floor_area_min");
			$search_tfamax = $this_object->prop ("search_total_floor_area_max");
			$search_fd = $this_object->prop ("searchparam_fromdate");
			$search_nor = $this_object->prop ("search_number_of_rooms");
			$search_tt = $this_object->prop ("search_transaction_type");
			$search_c = $this_object->prop ("search_condition");
			$search_agent = $this_object->prop ("search_agent");
			$search_a1 = $this_object->prop ("searchparam_address1");
			$search_a2 = $this_object->prop ("searchparam_address2");
			$search_a3 = $this_object->prop ("searchparam_address3");
			$search_at = $this_object->prop ("search_address_text");
			$search_owp = $this_object->prop ("searchparam_onlywithpictures");
			$search_imf = $this_object->prop ("search_is_middle_floor");
		}
		else
		{
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
			$search_agent = $arr["search"]["agent"];
			$search_a1 = $arr["search"]["a1"];
			$search_a2 = $arr["search"]["a2"];
			$search_a3 = $arr["search"]["a3"];
			$search_at = $arr["search"]["at"];
			$search_owp = $arr["search"]["owp"];
			$search_imf = $arr["search"]["imf"];
		}

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
					}
					break;
				case CL_REALESTATE_ROWHOUSE:
					if (is_oid ($manager->prop ("rowhouses_folder")))
					{
						$parents[] = $manager->prop ("rowhouses_folder");
					}
					break;
				case CL_REALESTATE_COTTAGE:
					if (is_oid ($manager->prop ("cottages_folder")))
					{
						$parents[] = $manager->prop ("cottages_folder");
					}
					break;
				case CL_REALESTATE_HOUSEPART:
					if (is_oid ($manager->prop ("houseparts_folder")))
					{
						$parents[] = $manager->prop ("houseparts_folder");
					}
					break;
				case CL_REALESTATE_COMMERCIAL:
					if (is_oid ($manager->prop ("commercial_properties_folder")))
					{
						$parents[] = $manager->prop ("commercial_properties_folder");
					}
					break;
				case CL_REALESTATE_GARAGE:
					if (is_oid ($manager->prop ("garages_folder")))
					{
						$parents[] = $manager->prop ("garages_folder");
					}
					break;
				case CL_REALESTATE_LAND:
					if (is_oid ($manager->prop ("land_estates_folder")))
					{
						$parents[] = $manager->prop ("land_estates_folder");
					}
					break;
				case CL_REALESTATE_APARTMENT:
					if (is_oid ($manager->prop ("apartments_folder")))
					{
						$parents[] = $manager->prop ("apartments_folder");
					}
					break;
			}
		}

		if (is_string ($search_agent) and !empty ($search_agent))
		{
			$agents_list = new object_list (array (
				"class_id" => CL_CRM_PERSON,
				"name" => "%" . $search_agent . "%",
				"site_id" => array (),
				"lang_id" => array (),
			));
			$search_agent = $agents_list->ids ();
		}

		$result_list = array ();

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

		$args = array (
			"class_id" => $search_ci,
			"parent" => $parents,
			"transaction_type" => $search_tt,
			"transaction_price" => $tp_constraint,
			"created" => new obj_predicate_compare (OBJ_COMP_GREATER, $search_fd),
			"site_id" => array(),
			"lang_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"realestate_agent1" => $search_agent,
					"realestate_agent2" => $search_agent,
				)
			)),

			### class specific
			"total_floor_area" => $tfa_constraint,
			"condition" => $search_c,
			"is_middle_floor" => (empty ($search_imf) ? NULL : $search_imf),
			"number_of_rooms" => (empty ($search_nor) ? NULL : $search_nor),
			"usage_purpose" => $search_up,
		);

		$list_cl = new object_list ($args);
		$result_list = $result_list + $list_cl->arr ();
		$list_keys = array_keys ($result_list);
		$list_addresses = array ();

// /* dbg */ if ($_GET["researchdbg"]==1){ arr ($args); }
/* dbg */ if ($_GET["researchdbg"]==1){ arr (count($result_list)); }

		### search for objects under admin units, if units specified, intersect results with objectlist found
		aw_switch_user (array ("uid" => $manager->prop ("almightyuser")));

		if (count ($search_a1))
		{
			$list_a = array ();

			foreach ($search_a1 as $parent)
			{
				$tree = new object_tree (array (
					"parent" => $parent,
					"site_id" => array(),
					"lang_id" => array(),
				));
				$list = $tree->to_list ();
				$list = $list->arr ();

				foreach ($list as $o)
				{
					if ($o->class_id () != CL_ADDRESS)
					{
						unset ($list[$o->id ()]);
					}
				}

				$list_a = $list_a + $list;
			}

			$list_a = array_keys ($list_a);
			$list_addresses = $list_addresses + $list_a;
		}

		if (count ($search_a2))
		{
			$list_a = array ();

			foreach ($search_a2 as $parent)
			{
				$tree = new object_tree (array (
					"parent" => $parent,
					"site_id" => array(),
					"lang_id" => array(),
				));
				$list = $tree->to_list ();
				$list = $list->arr ();

				foreach ($list as $o)
				{
					if ($o->class_id () != CL_ADDRESS)
					{
						unset ($list[$o->id ()]);
					}
				}

				$list_a = $list_a + $list;
			}

			$list_a = array_keys ($list_a);
			$list_addresses = $list_addresses + $list_a;
		}

		if (count ($search_a3))
		{
			$list_a = array ();

			foreach ($search_a3 as $parent)
			{
				$tree = new object_tree (array (
					"parent" => $parent,
					"site_id" => array(),
					"lang_id" => array(),
				));
				$list = $tree->to_list ();
				$list = $list->arr ();

				foreach ($list as $o)
				{
					if ($o->class_id () != CL_ADDRESS)
					{
						unset ($list[$o->id ()]);
					}
				}

				$list_a = $list_a + $list;
			}

			$list_a = array_keys ($list_a);
			$list_addresses = $list_addresses + $list_a;
		}

		if (count ($search_a1) or count ($search_a2) or count ($search_a3))
		{
			foreach ($result_list as $oid => $property)
			{
				$address = $property->get_first_obj_by_reltype ("RELTYPE_REALESTATE_ADDRESS");//!!! v6ibolla oleks kiirem venna parenti j2rgi aadress saada

				if (is_object ($address))
				{
					if (!in_array ($address->id (), $list_addresses))
					{
						unset ($result_list[$oid]);
					}
				}
				else
				{
					unset ($result_list[$oid]);
				}
			}
		}

		aw_restore_user ();

/* dbg */ if ($_GET["researchdbg"]==1){ arr (count($result_list)); }
		return $result_list;
	}

	function &search_old ($arr)
	{
		$this_object = is_oid ($arr["id"]) ? obj ($arr["id"]) : NULL;

		if (is_object ($this_object) and $this_object->prop ("save_search"))
		{
			$search_ci = $this_object->prop ("search_class_id");
			$search_tpmin = $this_object->prop ("search_transaction_price_min");
			$search_tpmax = $this_object->prop ("search_transaction_price_max");
			$search_tfamin = $this_object->prop ("search_total_floor_area_min");
			$search_tfamax = $this_object->prop ("search_total_floor_area_max");
			$search_fd = $this_object->prop ("searchparam_fromdate");
			$search_nor = $this_object->prop ("search_number_of_rooms");
			$search_tt = $this_object->prop ("search_transaction_type");
			$search_c = $this_object->prop ("search_condition");
			$search_agent = $this_object->prop ("search_agent");
			$search_a1 = $this_object->prop ("searchparam_address1");
			$search_a2 = $this_object->prop ("searchparam_address2");
			$search_a3 = $this_object->prop ("searchparam_address3");
			$search_at = $this_object->prop ("search_address_text");
			$search_owp = $this_object->prop ("searchparam_onlywithpictures");
			$search_imf = $this_object->prop ("search_is_middle_floor");
		}
		else
		{
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
			$search_agent = $arr["search"]["agent"];
			$search_a1 = $arr["search"]["a1"];
			$search_a2 = $arr["search"]["a2"];
			$search_a3 = $arr["search"]["a3"];
			$search_at = $arr["search"]["at"];
			$search_owp = $arr["search"]["owp"];
			$search_imf = $arr["search"]["imf"];
		}

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
					}
					break;
				case CL_REALESTATE_ROWHOUSE:
					if (is_oid ($manager->prop ("rowhouses_folder")))
					{
						$parents[] = $manager->prop ("rowhouses_folder");
					}
					break;
				case CL_REALESTATE_COTTAGE:
					if (is_oid ($manager->prop ("cottages_folder")))
					{
						$parents[] = $manager->prop ("cottages_folder");
					}
					break;
				case CL_REALESTATE_HOUSEPART:
					if (is_oid ($manager->prop ("houseparts_folder")))
					{
						$parents[] = $manager->prop ("houseparts_folder");
					}
					break;
				case CL_REALESTATE_COMMERCIAL:
					if (is_oid ($manager->prop ("commercial_properties_folder")))
					{
						$parents[] = $manager->prop ("commercial_properties_folder");
					}
					break;
				case CL_REALESTATE_GARAGE:
					if (is_oid ($manager->prop ("garages_folder")))
					{
						$parents[] = $manager->prop ("garages_folder");
					}
					break;
				case CL_REALESTATE_LAND:
					if (is_oid ($manager->prop ("land_estates_folder")))
					{
						$parents[] = $manager->prop ("land_estates_folder");
					}
					break;
				case CL_REALESTATE_APARTMENT:
					if (is_oid ($manager->prop ("apartments_folder")))
					{
						$parents[] = $manager->prop ("apartments_folder");
					}
					break;
			}
		}

		if (is_string ($search_agent) and !empty ($search_agent))
		{
			$agents_list = new object_list (array (
				"class_id" => CL_CRM_PERSON,
				"name" => "%" . $search_agent . "%",
				"site_id" => array (),
				"lang_id" => array (),
			));
			$search_agent = $agents_list->ids ();
		}

		$result_list = array ();
		$common_args = array (
			"parent" => $parents,
			"transaction_type" => $search_tt,
			"created" => new obj_predicate_compare (OBJ_COMP_GREATER, $search_fd),
			"site_id" => array(),
			"lang_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"realestate_agent1" => $search_agent,
					"realestate_agent2" => $search_agent,
				)
			)),
		);

		### add transaction_price constraint
		if ($search_tpmin and $search_tpmax)
		{
			$common_args["transaction_price"] = new obj_predicate_compare (OBJ_COMP_BETWEEN, $search_tpmin, $search_tpmax);
		}
		elseif ($search_tpmin)
		{
			$common_args["transaction_price"] = new obj_predicate_compare (OBJ_COMP_GREATER_OR_EQ, $search_tpmin);
		}
		elseif ($search_tpmax)
		{
			$common_args["transaction_price"] = new obj_predicate_compare (OBJ_COMP_LESS_OR_EQ, $search_tpmax);
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

		foreach ($search_ci as $class_id)
		{
			switch ($class_id)
			{
				case CL_REALESTATE_HOUSE://944
				case CL_REALESTATE_ROWHOUSE:
				case CL_REALESTATE_COTTAGE://978
				case CL_REALESTATE_HOUSEPART://979
					$args = array_merge ($common_args, array (
						"class_id" => $class_id,
						"number_of_rooms" => $search_nor,
						"condition" => $search_c,
					));

					if (isset ($tfa_constraint))
					{
						$args["total_floor_area"] = $tfa_constraint;
					}
					break;
				case CL_REALESTATE_APARTMENT://943
					$args = array_merge ($common_args, array (
						"class_id" => $class_id,
						"number_of_rooms" => $search_nor,
						"is_middle_floor" => $search_imf,
						"condition" => $search_c,
					));

					if (isset ($tfa_constraint))
					{
						$args["total_floor_area"] = $tfa_constraint;
					}
					break;
				case CL_REALESTATE_COMMERCIAL://947
					$args = array_merge ($common_args, array (
						"class_id" => $class_id,
						"number_of_rooms" => $search_nor,
						"usage_purpose" => $search_up,
						"condition" => $search_c,
					));

					if (isset ($tfa_constraint))
					{
						$args["total_floor_area"] = $tfa_constraint;
					}
					break;
				case CL_REALESTATE_GARAGE://946
					$args = array_merge ($common_args, array (
						"class_id" => $class_id,
						"condition" => $search_c,
						"total_floor_area" => $tfa_constraint,
					));
					break;
				case CL_REALESTATE_LAND://945
					$args = array_merge ($common_args, array (
						"class_id" => $class_id,
					));
					break;
			}

			$list_cl = new object_list ($args);
			$result_list = $result_list + $list_cl->arr ();
/* dbg */ if ($_GET["researchdbg"]==1){ arr ($args); }
		}

		$list_keys = array_keys ($result_list);
		$list_addresses = array ();

		### search for objects under admin units, if units specified, intersect results with objectlist found
		if (count ($search_a1))
		{
			$list_a = array ();

			foreach ($search_a1 as $parent)
			{
				$tree = new object_tree (array (
					"parent" => $parent,
					"site_id" => array(),
					"lang_id" => array(),
				));
				$list = $tree->to_list ();
				$list = $list->arr ();

				foreach ($list as $o)
				{
					if ($o->class_id () != CL_ADDRESS)
					{
						unset ($list[$o->id ()]);
					}
				}

				$list_a = $list_a + $list;
			}

			$list_a = array_keys ($list_a);
			$list_addresses = $list_addresses + $list_a;
			// $list_keys = array_intersect ($list_keys, $list_a);
		}

		if (count ($search_a2))
		{
			$list_a = array ();

			foreach ($search_a2 as $parent)
			{
				$tree = new object_tree (array (
					"parent" => $parent,
					"site_id" => array(),
					"lang_id" => array(),
				));
				$list = $tree->to_list ();
				$list = $list->arr ();

				foreach ($list as $o)
				{
					if ($o->class_id () != CL_ADDRESS)
					{
						unset ($list[$o->id ()]);
					}
				}

				$list_a = $list_a + $list;
			}

			$list_a = array_keys ($list_a);
			$list_addresses = $list_addresses + $list_a;
			// $list_keys = array_intersect ($list_keys, $list_a);
		}

		if (count ($search_a3))
		{
			$list_a = array ();

			foreach ($search_a3 as $parent)
			{
				$tree = new object_tree (array (
					"parent" => $parent,
					"site_id" => array(),
					"lang_id" => array(),
				));
				$list = $tree->to_list ();
				$list = $list->arr ();

				foreach ($list as $o)
				{
					if ($o->class_id () != CL_ADDRESS)
					{
						unset ($list[$o->id ()]);
					}
				}

				$list_a = $list_a + $list;
			}

			$list_a = array_keys ($list_a);
			$list_addresses = $list_addresses + $list_a;
			// $list_keys = array_intersect ($list_keys, $list_a);
		}

		if (count ($search_a1) or count ($search_a2) or count ($search_a3))
		{
			foreach ($result_list as $oid => $property)
			{
				$address = $property->get_first_obj_by_reltype ("RELTYPE_REALESTATE_ADDRESS");//!!! v6ibolla oleks kiirem venna parenti j2rgi aadress saada

				if (is_object ($address))
				{
					if (!in_array ($address->id (), $list_addresses))
					{
						unset ($result_list[$oid]);
					}
				}
				else
				{
					unset ($result_list[$oid]);
				}
			}
		}

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
			"records_per_page" => 50,
		));
	}

	function show_property ($arr)
	{
		$property = obj ($_GET["realestate_show_property"]);
		$cl_property = get_instance ($property->class_id ());
		return $cl_property->request_execute ($property);
	}
}
?>
