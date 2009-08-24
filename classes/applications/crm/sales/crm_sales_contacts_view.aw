<?php

class crm_sales_contacts_view
{
	public static function _get_contacts_tree(&$arr)
	{
		$tree = $arr["prop"]["vcl_inst"];

		foreach (crm_sales::$contacts_list_views as $key => $data)
		{
			if ($data["in_tree"])
			{
				$url = automatweb::$request->get_uri();

				if (crm_sales::CONTACTS_DEFAULT === $key)
				{
					$url->unset_arg(array(
						"ft_page",
						"cts_submit",
						"cts_name",
						"cts_salesman",
						"cts_lead_source",
						"cts_address",
						"cts_phone",
						"cts_status"
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

		$tree->set_selected_item (crm_sales::$contacts_list_view);
		return PROP_OK;
	}

	public static function _get_contacts_toolbar(&$arr)
	{
		$toolbar = $arr["prop"]["vcl_inst"];
		$toolbar->add_button(array(
			"name" => "create_calls",
			"img" => "class_223_done.gif",
			"action" => "create_calls",
			"tooltip" => t("Loo k&otilde;ned")
		));
		$toolbar->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"action" => "submit",
			"tooltip" => t("Salvesta")
		));
		$toolbar->add_delete_button();
		return PROP_OK;
	}

	public static function get_contacts_list(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$filter = array();

		if (!empty($arr["request"]["cts_submit"]))
		{
			if (!empty($arr["request"]["cts_name"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_COMPANY_CUSTOMER_DATA.buyer(CL_CRM_COMPANY).name" => "%{$arr["request"]["cts_name"]}%",
						"CL_CRM_COMPANY_CUSTOMER_DATA.buyer(CL_CRM_PERSON).name" => "%{$arr["request"]["cts_name"]}%"
					)
				));
			}

			if (!empty($arr["request"]["cts_phone"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_COMPANY_CUSTOMER_DATA.buyer(CL_CRM_COMPANY).RELTYPE_PHONE.name" => "{$arr["request"]["cts_phone"]}%",
						"CL_CRM_COMPANY_CUSTOMER_DATA.buyer(CL_CRM_PERSON).RELTYPE_PHONE.name" => "{$arr["request"]["cts_phone"]}%"
					)
				));
			}

			if (!empty($arr["request"]["cts_salesman"]))
			{
				$filter["salesman.name"] = "%{$arr["request"]["cts_salesman"]}%";
			}

			if (!empty($arr["request"]["cts_lead_source"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_COMPANY_CUSTOMER_DATA.sales_lead_source(CL_CRM_COMPANY).name" => "%{$arr["request"]["cts_lead_source"]}%",
						"CL_CRM_COMPANY_CUSTOMER_DATA.sales_lead_source(CL_CRM_PERSON).name" => "%{$arr["request"]["cts_lead_source"]}%"
					)
				));
			}

			if (!empty($arr["request"]["cts_address"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_COMPANY_CUSTOMER_DATA.buyer(CL_CRM_COMPANY).RELTYPE_ADDRESS_ALT.name" => "%{$arr["request"]["cts_address"]}%",
						"CL_CRM_COMPANY_CUSTOMER_DATA.buyer(CL_CRM_PERSON).RELTYPE_ADDRESS_ALT.name" => "%{$arr["request"]["cts_address"]}%"
					)
				));
			}

			if (!empty($arr["request"]["cts_status"]))
			{
				$filter["sales_state"] = $arr["request"]["cts_status"];
			}
		}

		// pagination and limit
		$contacts = new crm_customer_relation_list($filter);
		$contacts_count = $contacts->count();

		$per_page = $this_o->prop("tables_rows_per_page");
		$page = isset($arr["request"]["ft_page"]) ? (int) $arr["request"]["ft_page"] : 0;
		$start = $page*$per_page;
		$filter[] = new obj_predicate_limit($per_page, $start);

		// sorting
		$sort_by = "buyer.name";
		$sort_dir = "asc";
		$sortable_fields = array( // table field => array( default sort order, database field name)
			"name" => array("asc", "name"),
			"unit" => array("asc", "name"),
			"last_call" => array("desc", "sales_last_call_time"),
			"calls_made" => array("asc", "sales_calls_made")
		);
		if (isset($arr["request"]["sortby"]) and isset($sortable_fields[$arr["request"]["sortby"]]))
		{
			$sort_by = $sortable_fields[$arr["request"]["sortby"]][1];
			$sort_dir = isset($arr["request"]["sortby"]) ? ($arr["request"]["sortby"] === "asc" ? "asc" : "desc") : $sortable_fields[$arr["request"]["sortby"]][0];
		}
		$filter[] = new obj_predicate_sort(array($sort_by => $sort_dir));

		// ...
		$contacts = new crm_customer_relation_list($filter);
		return array($contacts, $contacts_count);
	}


	public static function _get_contacts_list(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$table = $arr["prop"]["vcl_inst"];
		$table->set_hover_hilight(true);
		$owner = $arr["obj_inst"]->prop("owner");
		list($contacts, $contacts_count) = self::get_contacts_list($arr);
		self::define_contacts_list_tbl_header($arr, $contacts_count);
		$not_available_str = "";

		if ($contacts->count())
		{
			$customer_relation = $contacts->begin();
			do
			{
				$customer = $customer_relation->get_first_obj_by_reltype("RELTYPE_BUYER");
				$salesman = $customer_relation->prop("salesman");
				$salesman = is_oid($salesman) ? new object($salesman) : obj(null, array(), CL_CRM_PERSON);
				// $call_timestamp =  $call->prop("start1");
				// $call_time =  date("d.m.Y H:i", $call_timestamp);

				$calls_made = $this_o->get_calls_count($customer_relation);

				if ($calls_made > 0)
				{
					$last_call = $this_o->get_last_call($customer_relation);
					$last_call_timestamp =  $last_call->prop("start1");
					$last_call_result = crm_call_obj::result_names($last_call->prop("result"));
					$last_call_result = reset($last_call_result);
					$last_call = locale::get_lc_date($last_call->prop("real_start"), LC_DATE_FORMAT_SHORT_FULLYEAR);
				}
				else
				{
					$last_call = $last_call_result = $not_available_str;
					$last_call_timestamp = 0;
				}


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
				do
				{
					$number = trim($phone->name());
					if (strlen($number) > 1)
					{
						$request = (array) $arr["request"];
						$request["return_url"] = get_ru();
						unset($request["action"]);
						$phone_nr = $phone->name();
						$phones_str[] = $phone_nr;
					}
				}
				while ($phone = $phones->next());

				// address
				$address = $customer->get_first_obj_by_reltype("RELTYPE_ADDRESS_ALT");
				$address = is_object($address) ? $address->name() : $not_available_str;

				// define table row
				$table->define_data(array(
					"name" => html::obj_change_url($customer, strlen($customer->name()) > 1 ? $customer->name() : t("[Nimetu]")),
					"phones" => implode(", ", $phones_str),
					"address" => $address,
					"unit" => $unit,
					"lead_source" => $lead_source,
					"last_call" => $last_call,
					"last_call_timestamp" => $last_call_timestamp,
					"last_call_result" => $last_call_result,
					"calls_made" => $calls_made,
					"oid" => $customer_relation->id(),
					"salesman" => $salesman->name()
				));
			}
			while ($customer_relation = $contacts->next());

			if (!empty($arr["request"]["cts_submit"]))
			{
				$table->set_caption(t("Otsingu tulemused"));
			}
			else
			{
				$table->set_caption(t("Kontaktid"));
			}
		}
		return PROP_OK;
	}

	public static function define_contacts_list_tbl_header(&$arr, $contacts_count)
	{
		$this_o = $arr["obj_inst"];
		$table = $arr["prop"]["vcl_inst"];
		$table->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
		$table->define_field(array(
			"name" => "name",
			"sortable" => true,
			"caption" => t("Nimi")
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
			"name" => "last_call",
			"sortable" => true,
			"caption" => t("Viimase k&otilde;ne aeg")
		));
		$table->define_field(array(
			"name" => "last_call_result",
			"caption" => t("Viimase k&otilde;ne tulemus")
		));
		$table->define_field(array(
			"name" => "calls_made",
			"sortable" => true,
			"caption" => t("K&otilde;nesid")
		));
		$table->define_field(array(
			"name" => "salesman",
			"caption" => t("M&uuml;&uuml;giesindaja")
		));
		$table->set_numeric_field(array("last_call_timestamp"));
		$table->set_default_sortby("name");
		$table->set_default_sorder("asc");
		$table->define_pageselector (array (
			"type" => "lbtxt",
			"position" => "both",
			"d_row_cnt" => $contacts_count,
			"records_per_page" => $this_o->prop("tables_rows_per_page")
		));
	}
}

?>
