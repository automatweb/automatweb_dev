<?php

classload("vcl/popup_search");
class crm_participant_search extends popup_search
{
	function crm_participant_search()
	{
		$this->popup_search();
	}

	function _insert_form_props(&$htmlc, $arr)
	{
		parent::_insert_form_props($htmlc, $arr);
		$htmlc->add_property(array(
			"name" => "s[search_co]",
			"type" => "textbox",
			"value" => $arr["s"]["search_co"],
			"caption" => t("Organisatsioon"),
		));
		$htmlc->add_property(array(
			"name" => "s[show_vals]",
			"type" => "chooser",
			"value" => isset($_GET["MAX_FILE_SIZE"]) ? $arr["s"]["show_vals"] : array("cur_co" => 1),
			"caption" => t("N&auml;ita"),
			"multiple" => 1,
			"options" => array(
				"cur_co" => t("Meie firma"),
				"my_cust" => t("Minu kliendid"),
				"imp" => t("Olulised"),
				"def" => t("Esimesed kolmkümmend")
			)
		));
	}

	function _get_filter_props(&$filter, $arr)
	{
		parent::_get_filter_props($filter, $arr);

		if (!$_GET["MAX_FILE_SIZE"])
		{
			$arr["s"]["show_vals"]["cur_co"] = 1;
		}

		if ($arr["s"]["search_co"] != "")
		{
			$filter["CL_CRM_PERSON.work_contact.name"] = map("%%%s%%", array_filter(explode(",", $arr["s"]["search_co"]), create_function('$a','return $a != "";')));
		}

		if (is_array($arr["s"]["show_vals"]))
		{
			if ($arr["s"]["show_vals"]["cur_co"])
			{
				$c = get_instance(CL_CRM_COMPANY);
				$u = get_instance(CL_USER);
				$filter["oid"] = array_keys($c->get_employee_picker(obj($u->get_current_company())));
			}
		
			if ($arr["s"]["show_vals"]["my_cust"])
			{
				$i = get_instance(CL_CRM_COMPANY);
				$my_c = $i->get_my_customers();
				if (!count($my_c) && !is_array($filter["oid"]))
				{
					$filter["oid"] = -1;
				}
				else
				if (count($my_c))
				{
					$ol = new object_list(array("oid" => $my_c, "class_id" => CL_CRM_PERSON, "lang_id" => array(), "site_id" => array()));
					if (!is_array($filter["oid"]))
					{
						$filter["oid"] = array();
					}
					$filter["oid"] += $ol->ids();
				}
			}

			if ($arr["s"]["show_vals"]["imp"])
			{
				$c = get_instance(CL_CRM_COMPANY);
				$u = get_instance(CL_USER);
				if (!is_array($filter["oid"]))
				{
					$filter["oid"] = array();
				}
				$filter["oid"] += array_keys($c->get_employee_picker(obj($u->get_current_company()), false, true));
			}

			if ($arr["s"]["show_vals"]["def"])
			{
				if (!is_array($filter["oid"]))
				{
					$filter["oid"] = array();
				}
				$ol = new object_list(array("class_id" => CL_CRM_PERSON, "lang_id" => array(), "site_id" => array(), "limit" => 30));
				$filter["oid"] += $ol->ids();
			}
		}

		if (is_array($filter["oid"]) && !count($filter["oid"]))
		{
			$filter["oid"] = -1;
		}
	}
}

?>