<?php

class crm_company_bills_impl extends class_base
{
	function crm_company_bills_impl()
	{
		$this->init();
	}

	function _init_bill_proj_list_t(&$t)
	{
		$t->define_field(array(
			"caption" => t("Projekt"),
			"name" => "name",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Ava"),
			"name" => "open",
			"align" => "center",
			"sortable" => 1
		));
	}

	function _get_bill_proj_list($arr)
	{	
		if ($arr["request"]["proj"])
		{
			return PROP_IGNORE;
		}

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bill_proj_list_t($t);

		// get all open tasks
		$i = get_instance(CL_CRM_COMPANY);
		$proj = $i->get_my_projects();
		$proj_i = get_instance(CL_PROJECT);

		foreach($proj as $p)
		{
			$events = $proj_i->get_events(array(
				"id" => $p,
				"range" => array(
					"start" => 1,
					"end" => time() + 24*3600*365*10
				)
			));
			if (!count($events))
			{
				continue;
			}
			$evt_ol = new object_list(array(
				"class_id" => CL_TASK,
				"oid" => array_keys($events),
				"bill_no" => new obj_predicate_compare(OBJ_COMP_EQUAL, ""),
				"send_bill" => 1
			));
			if (!$evt_ol->count())
			{
				continue;
			}

			$po = obj($p);

			$t->define_data(array(
				"name" => html::get_change_url($p, array("return_url" => get_ru()), $po->name()),
				"open" => html::href(array(
					"url" => aw_url_change_var("proj", $p),
					"caption" => t("Ava")
				))
			));
		}
	}

	function _init_bill_task_list_t(&$t)
	{
		$t->define_field(array(
			"caption" => t("Juhtumi nimi"),
			"name" => "name",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_bill_task_list($arr)
	{
		if (!$arr["request"]["proj"])
		{
			return PROP_IGNORE;
		}
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bill_task_list_t($t);

		$proj_i = get_instance(CL_PROJECT);
		$events = $proj_i->get_events(array(
			"id" => $arr["request"]["proj"],
			"range" => array(
				"start" => 1,
				"end" => time() + 24*3600*365*10
			)
		));
		foreach($events as $evt)
		{
			$o = obj($evt["id"]);
			if ($o->prop("send_bill"))
			{
				if ($o->prop("bill_no") == "")
				{
					$t->define_data(array(
						"name" => html::get_change_url($o->id(), array("return_url" => get_ru()), $o->name()),
						"oid" => $o->id()
					));
				}
			}
		}
	}

	function _get_bill_tb($arr)
	{
		if (!$arr["request"]["proj"])
		{
			return PROP_IGNORE;
		}

		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_button(array(
			"name" => "create_bill",
			"img" => "save.gif",
			"tooltip" => t("Koosta arve"),
			"action" => "create_bill"
		));
	}

	function _init_bills_list_t(&$t)
	{
		$t->define_field(array(
			"name" => "bill_no",
			"caption" => t("Number"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "bill_date",
			"caption" => t("Kuup&auml;ev"),
			"type" => "time",
			"format" => "d.m.Y",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "bill_due_date",
			"caption" => t("Makset&auml;htaeg"),
			"type" => "time",
			"format" => "d.m.Y",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "customer",
			"caption" => t("Klient"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "state",
			"caption" => t("Staatus"),
			"sortable" => 1
		));
	}

	function _get_bills_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bills_list_t($t);
		
		$bills = new object_list(array(
			"class_id" => CL_CRM_BILL,
			"parent" => $arr["obj_inst"]->id()
		));
		$bill_i = get_instance(CL_CRM_BILL);
		foreach($bills->arr() as $bill)
		{
			$cust = "";
			if (is_oid($bill->prop("customer")) && $this->can("view", $bill->prop("customer")))
			{
				$tmp = obj($bill->prop("customer"));
				$cust = html::get_change_url($tmp->id(), array("retyurn_url" => get_ru()), $tmp->name());
			}
			$t->define_data(array(
				"bill_no" => html::get_change_url($bill->id(), array("return_url" => get_ru()), parse_obj_name($bill->prop("bill_no"))),
				"bill_date" => $bill->prop("bill_date"),
				"bill_due_date" => $bill->prop("bill_due_date"),
				"customer" => $cust,
				"state" => $bill_i->states[$bill->prop("state")]
			));
		}
	}
}
?>