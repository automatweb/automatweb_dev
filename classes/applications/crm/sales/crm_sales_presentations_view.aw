<?php

class crm_sales_presentations_view
{
	public static function _get_presentations_tree(&$arr)
	{
		$tree = $arr["prop"]["vcl_inst"];

		foreach (crm_sales::$presentations_list_views as $key => $data)
		{
			if ($data["in_tree"])
			{
				$url = automatweb::$request->get_uri();

				if (crm_sales::PRESENTATIONS_DEFAULT === $key)
				{
					$url->unset_arg(array(
						"ft_page",
						"ps_submit",
						"ps_name",
						"ps_salesman",
						"ps_lead_source",
						"ps_address",
						"ps_phone",
						"ps_status"
					));
				}

				$tree->add_item (0, array (
					"name" => $data["caption"],
					"id" => $key,
					"parent" => 0,
					"url" => $url->get()
				));
			}
		}

		$tree->set_selected_item (crm_sales::$presentations_list_view);
		return PROP_OK;
	}

	public static function _get_presentations_toolbar(&$arr)
	{
		$toolbar = $arr["prop"]["vcl_inst"];
		$toolbar->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"action" => "submit",
			"tooltip" => t("Salvesta")
		));
		return PROP_OK;
	}

	protected static function get_presentations_list(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$filter = array();

		if (!empty($arr["request"]["ps_submit"]))
		{
			if (!empty($arr["request"]["ps_name"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_PRESENTATION.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_COMPANY).name" => "%{$arr["request"]["ps_name"]}%",
						"CL_CRM_PRESENTATION.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_PERSON).name" => "%{$arr["request"]["ps_name"]}%"
					)
				));
			}

			if (!empty($arr["request"]["ps_phone"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_PRESENTATION.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_COMPANY).RELTYPE_PHONE.name" => "{$arr["request"]["ps_phone"]}%",
						"CL_CRM_PRESENTATION.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_PERSON).RELTYPE_PHONE.name" => "{$arr["request"]["ps_phone"]}%"
					)
				));
			}

			if (!empty($arr["request"]["ps_salesman"]))
			{
				$filter["customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).salesman.name"] = "%{$arr["request"]["ps_salesman"]}%";
			}

			if (!empty($arr["request"]["ps_lead_source"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_PRESENTATION.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).sales_lead_source(CL_CRM_COMPANY).name" => "%{$arr["request"]["ps_lead_source"]}%",
						"CL_CRM_PRESENTATION.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).sales_lead_source(CL_CRM_PERSON).name" => "%{$arr["request"]["ps_lead_source"]}%"
					)
				));
			}

			if (!empty($arr["request"]["ps_address"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_PRESENTATION.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_COMPANY).RELTYPE_ADDRESS_ALT.name" => "%{$arr["request"]["ps_address"]}%",
						"CL_CRM_PRESENTATION.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_PERSON).RELTYPE_ADDRESS_ALT.name" => "%{$arr["request"]["ps_address"]}%"
					)
				));
			}

			if (!empty($arr["request"]["ps_status"]))
			{
				$filter["CL_CRM_PRESENTATION.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).sales_state"] = $arr["request"]["ps_status"];
			}
		}

		// pagination and limit
		$presentations = new crm_presentation_list($filter);
		$presentations_count = $presentations->count();

		$per_page = $this_o->prop("tables_rows_per_page");
		$page = isset($arr["request"]["ft_page"]) ? (int) $arr["request"]["ft_page"] : 0;
		$start = $page*$per_page;
		$filter[] = new obj_predicate_limit($per_page, $start);

		// sorting
		$sort_by = "buyer.name";
		$sort_dir = obj_predicate_sort::ASC;
		$sortable_fields = array( // table field => array( default sort order, database field name)
			"name" => array(obj_predicate_sort::ASC, "name"),
			"unit" => array(obj_predicate_sort::ASC, "name")
		);
		if (isset($arr["request"]["sortby"]) and isset($sortable_fields[$arr["request"]["sortby"]]))
		{
			$sort_by = $sortable_fields[$arr["request"]["sortby"]][1];
			$sort_dir = isset($arr["request"]["sortby"]) ? ($arr["request"]["sortby"] === "asc" ? obj_predicate_sort::ASC : obj_predicate_sort::DESC) : $sortable_fields[$arr["request"]["sortby"]][0];
		}
		//!!! sortimine praegu tegemata. sortida ei saa telemarketing, teised saavad
		// $filter[] = new obj_predicate_sort(array($sort_by => $sort_dir));

		// ...
		$presentations = new crm_presentation_list($filter);
		return array($presentations, $presentations_count);
	}

	public static function _get_presentations_list(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$table = $arr["prop"]["vcl_inst"];
		$table->set_hover_hilight(true);
		self::define_presentations_list_tbl_header($arr);
		$owner = $arr["obj_inst"]->prop("owner");
		list($presentations, $presentations_count) = self::get_presentations_list($arr);
		$not_available_str = "";

		if ($presentations->count())
		{
			$presentation = $presentations->begin();
			do
			{
				$customer_relation = new object($presentation->prop("customer_relation"));
				if (is_oid($customer_relation->id()))
				{
					$customer = $customer_relation->get_first_obj_by_reltype("RELTYPE_BUYER");
					$salesman = $customer_relation->prop("salesman");
					$salesman = is_oid($salesman) ? new object($salesman) : obj(null, array(), CL_CRM_PERSON);

					$lead_source = $customer_relation->prop("sales_lead_source");
					if ($lead_source)
					{
						$lead_source = new object($lead_source);
						$lead_source = $lead_source->name();
					}
					else
					{
						$lead_source = $not_available_str;
					}

					$unit = $salesman->get_org_section();
					if ($unit)
					{
						$unit = new object($unit);
						$unit = $unit->name();
					}
					else
					{
						$unit = $not_available_str;
					}

					$phones = new object_list($customer->connections_from(array("type" => "RELTYPE_PHONE")));
					$phones_str = array();
					$phone = $phones->begin();
					$core = new core();
					do
					{
						$number = trim($phone->name());
						if (strlen($number) > 1)
						{
							$request = (array) $arr["request"];
							$request["return_url"] = get_ru();
							unset($request["action"]);
							$phones_str[] = html::href(array("caption" => $phone_nr, "url" => $url, "title" => $title));
						}
					}
					while ($phone = $phones->next());

					// time
					$presentation_timestamp = $presentation->prop("real_start");
					$time = $presentation_timestamp > 1 ? date("d.m.Y H:i", $presentation_timestamp) : $not_available_str;

					// result

					// address
					$address = $customer->get_first_obj_by_reltype("RELTYPE_ADDRESS_ALT");
					$address = is_object($address) ? $address->name() : $not_available_str;

					// define table row
					$table->define_data(array(
						"name" => $customer->name(),
						"phones" => implode(", ", $phones_str),
						"address" => $address,
						"unit" => $unit,
						"lead_source" => $lead_source,
						"time" => $time,
						"result" => $result,
						"presentation_timestamp" => $presentation_timestamp,
						"salesman" => $salesman->name()
					));
				}
			}
			while ($presentation = $presentations->next());

			if (!empty($arr["request"]["ps_submit"]))
			{
				$table->set_caption(t("Otsingu tulemused"));
			}
		}
		return PROP_OK;
	}

	protected static function define_presentations_list_tbl_header(&$arr)
	{
		$table = $arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Kliendi nimi")
		));
		$table->define_field(array(
			"name" => "phones",
			"caption" => t("Telefon(id)")
		));
		$table->define_field(array(
			"name" => "unit",
			"caption" => t("Osakond")
		));
		$table->define_field(array(
			"name" => "address",
			"caption" => t("Aadress")
		));
		$table->define_field(array(
			"name" => "lead_source",
			"caption" => t("Allikas/soovitaja")
		));
		$table->define_field(array(
			"name" => "time",
			"caption" => t("Aeg")
		));
		$table->define_field(array(
			"name" => "result",
			"caption" => t("Tulemus")
		));
		$table->define_field(array(
			"name" => "salesman",
			"caption" => t("M&uuml;&uuml;giesindaja")
		));
		$table->set_numeric_field(array("presentation_timestamp"));
		$table->set_default_sortby("presentation_timestamp");
		$table->set_default_sorder("asc");
	}
}

?>
