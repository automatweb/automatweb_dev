<?php

class crm_sales_calls_view
{
	public static function _get_calls_tree(&$arr)
	{
		$tree = $arr["prop"]["vcl_inst"];

		foreach (crm_sales::$calls_list_views as $key => $data)
		{
			if ($data["in_tree"])
			{
				$url = automatweb::$request->get_uri();

				if (crm_sales::CALLS_CURRENT === $key)
				{
					$url->unset_arg(array(
						"ft_page",
						"cs_submit",
						"cs_name",
						"cs_salesman",
						"cs_lead_source",
						"cs_address",
						"cs_phone",
						"cs_status"
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

		$tree->set_selected_item (crm_sales::$calls_list_view);
		return PROP_OK;
	}

	public static function _get_calls_toolbar(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$role = $this_o->get_current_user_role();
		$toolbar = $arr["prop"]["vcl_inst"];

		$toolbar->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"action" => "submit",
			"tooltip" => t("Salvesta")
		));

		if (crm_sales_obj::ROLE_TELEMARKETING_SALESMAN !== $role)
		{
			$toolbar->add_delete_button();
		}

		return PROP_OK;
	}

	protected static function get_calls_list(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$filter = array();

		if (!empty($arr["request"]["cs_submit"]))
		{
			if (!empty($arr["request"]["cs_name"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_COMPANY).name" => "%{$arr["request"]["cs_name"]}%",
						"CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_PERSON).name" => "%{$arr["request"]["cs_name"]}%"
					)
				));
			}

			if (!empty($arr["request"]["cs_phone"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_COMPANY).RELTYPE_PHONE.name" => "{$arr["request"]["cs_phone"]}%",
						"CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_PERSON).RELTYPE_PHONE.name" => "{$arr["request"]["cs_phone"]}%"
					)
				));
			}

			if (!empty($arr["request"]["cs_salesman"]))
			{
				$filter["customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).salesman.name"] = "%{$arr["request"]["cs_salesman"]}%";
			}

			if (!empty($arr["request"]["cs_lead_source"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).sales_lead_source(CL_CRM_COMPANY).name" => "%{$arr["request"]["cs_lead_source"]}%",
						"CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).sales_lead_source(CL_CRM_PERSON).name" => "%{$arr["request"]["cs_lead_source"]}%"
					)
				));
			}

			if (!empty($arr["request"]["cs_address"]))
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						"CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_COMPANY).RELTYPE_ADDRESS_ALT.name" => "%{$arr["request"]["cs_address"]}%",
						"CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).buyer(CL_CRM_PERSON).RELTYPE_ADDRESS_ALT.name" => "%{$arr["request"]["cs_address"]}%"
					)
				));
			}

			if (!empty($arr["request"]["cs_status"]))
			{
				$filter["CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).sales_state"] = $arr["request"]["cs_status"];
			}
		}

		// pagination and limit
		$calls = new crm_call_list($filter);
		$calls_count = $calls->count();

		$per_page = $this_o->prop("tables_rows_per_page");
		$page = isset($arr["request"]["ft_page"]) ? (int) $arr["request"]["ft_page"] : 0;
		$start = $page*$per_page;
		$filter[] = new obj_predicate_limit($per_page, $start);

		// sorting
		$sort_by = "buyer.name";
		$sort_dir = obj_predicate_sort::ASC;
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
		//!!! sortimine praegu tegemata. sortida ei saa telemarketing, teised saavad
		// $filter[] = new obj_predicate_sort(array($sort_by => $sort_dir));
		$filter[] = new obj_predicate_sort(array(
			"CL_CRM_CALL.customer_relation(CL_CRM_COMPANY_CUSTOMER_DATA).sales_lead_source" => obj_predicate_sort::DESC,
			"deadline" => array(new obj_predicate_compare(obj_predicate_compare::GREATER, 10000), obj_predicate_sort::ASC)
		));

		// ...
		$calls = new crm_call_list($filter);
		return array($calls, $calls_count);
	}

	public static function _get_calls_list(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$table = $arr["prop"]["vcl_inst"];
		$table->set_hover_hilight(true);
		self::define_calls_list_tbl_header($arr);
		$owner = $arr["obj_inst"]->prop("owner");
		list($calls, $calls_count) = self::get_calls_list($arr);
		$not_available_str = "";

		if ($calls->count())
		{
			$call = $calls->begin();
			do
			{
				$customer_relation = new object($call->prop("customer_relation"));
				if (is_oid($customer_relation->id()))
				{
					$customer = $customer_relation->get_first_obj_by_reltype("RELTYPE_BUYER");
					$salesman = $customer_relation->prop("salesman");
					$salesman = is_oid($salesman) ? new object($salesman) : obj(null, array(), CL_CRM_PERSON);
					$call_timestamp =  $call->prop("start1");
					$deadline =  $call->prop("deadline");
					$deadline = $deadline < 10000 ? 0 : $deadline;
					$call_time = $call_timestamp > 1 ? date("d.m.Y H:i", $call_timestamp) : $not_available_str;

					$calls_made = $this_o->get_calls_count($customer_relation);

					if ($calls_made > 0)
					{
						$last_call = $this_o->get_last_call($customer_relation);
						$last_call_result = crm_call_obj::result_names($last_call->prop("result"));
						$last_call_result = reset($last_call_result);
						$last_call = aw_locale::get_lc_date($last_call->prop("real_start"), LC_DATE_FORMAT_SHORT_FULLYEAR);
					}
					else
					{
						$last_call = $last_call_result = $not_available_str;
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
					$core = new core();
					do
					{
						$number = trim($phone->name());
						if (strlen($number) > 1)
						{
							$request = (array) $arr["request"];
							$request["return_url"] = get_ru();
							unset($request["action"]);
							if ($call->prop("real_start") < 2)
							{ // a normal unstarted call
								$url = $core->mk_my_orb("change", array(
									"id" => $call->id(),
									"return_url" => get_ru(),
									"preparing_to_call" => 1,
									"phone_id" => $phone->id()
								), "crm_call");
								$phone_nr = $phone->name();
								$title = "";
							}
							elseif ($call->prop("real_duration") < 1)
							{ // a call that is started but not finished
								$person = get_current_person();
								$role = $this_o->get_current_user_role();

								if (crm_sales_obj::ROLE_TELEMARKETING_SALESMAN !== $role or $call->has_participant($person))
								{
									$url = $core->mk_my_orb("change", array(
										"id" => $call->id(),
										"unlock_call" => 1,
										"return_url" => get_ru()
									), "crm_call");
									$phone_nr = "<span style=\"color: red;\">" . $phone->name() . "</span>";
									$title = t("L&otilde;petamata k&otilde;ne");
								}
							}
							$phones_str[] = html::href(array("caption" => $phone_nr, "url" => $url, "title" => $title));
						}
					}
					while ($phone = $phones->next());

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
						"last_call" => $last_call,
						"call_time" => $call_time,
						"call_timestamp" => $call_timestamp,
						"deadline" => $deadline,
						"last_call_result" => $last_call_result,
						"calls_made" => $calls_made,
						"oid" => $call->id(),
						"salesman" => $salesman->name()
					));
				}
			}
			while ($call = $calls->next());

			if (!empty($arr["request"]["cs_submit"]))
			{
				$table->set_caption(t("Otsingu tulemused"));
			}
		}
		return PROP_OK;
	}

	protected static function define_calls_list_tbl_header(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$table = $arr["prop"]["vcl_inst"];
		$role = $this_o->get_current_user_role();

		if (crm_sales_obj::ROLE_TELEMARKETING_SALESMAN !== $role)
		{
			$table->define_chooser(array(
				"name" => "sel",
				"field" => "oid"
			));
		}

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
			"name" => "last_call",
			"caption" => t("Viimase k&otilde;ne aeg")
		));
		$table->define_field(array(
			"name" => "call_time",
			"sorting_field" => "call_timestamp",
			"caption" => t("Helistada")
		));
		$table->define_field(array(
			"name" => "last_call_result",
			"caption" => t("Kontakti staatus")
		));
		$table->define_field(array(
			"name" => "calls_made",
			"caption" => t("K&otilde;nesid")
		));
		$table->define_field(array(
			"name" => "salesman",
			"caption" => t("M&uuml;&uuml;giesindaja")
		));
		$table->set_numeric_field(array("deadline"));
		$table->set_numeric_field(array("call_timestamp"));
		$table->set_default_sortby("deadline");
		$table->set_default_sorder("asc", true);
		$table->define_pageselector (array (
			"type" => "lbtxt",
			"position" => "both",
			"d_row_cnt" => $contacts_count,
			"records_per_page" => $this_o->prop("tables_rows_per_page")
		));
	}
}

?>
