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
			"caption" => t("Loo arve"),
			"name" => "open",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Projekt"),
			"name" => "name",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Klient"),
			"name" => "cust",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Summa"),
			"name" => "sum",
			"align" => "center",
			"sortable" => 1
		));
	}

	function _get_bill_proj_list($arr)
	{	
		if ($arr["request"]["proj"] || $arr["request"]["cust"])
		{
			return PROP_IGNORE;
		}

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bill_proj_list_t($t);

		// get all open tasks
		$i = get_instance(CL_CRM_COMPANY);
		//$proj = $i->get_my_projects();
		$proj_i = get_instance(CL_PROJECT);
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
			"site_id" => array(),
			"lang_id" => array()
		));
		foreach($ol->ids() as $p)
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
			$sum = 0;
			$task_i = get_instance(CL_TASK);
			$has_rows = false;
			foreach($evt_ol->arr() as $evt)
			{
				if (!$evt->prop("send_bill"))
				{
					continue;
				}
				$rows = $task_i->get_task_bill_rows($evt);
				if (!count($rows))
				{
					continue;
				}
				$has_rows = true;
				foreach($rows as $row)
				{
					$sum += $row["sum"];
				}
			}

			if (!$has_rows)
			{
				continue;
			}
			$po = obj($p);

			$t->define_data(array(
				"name" => html::get_change_url($p, array("return_url" => get_ru()), $po->name()),
				"open" => html::href(array(
					"url" => aw_url_change_var("proj", $p),
					"caption" => t("Loo arve")
				)),
				"cust" => html::obj_change_url(reset($po->prop("orderer"))),
				"sum" => number_format($sum, 2)
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

		$t->define_field(array(
			"caption" => t("Tunde"),
			"name" => "hrs",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Tunni hind"),
			"name" => "hr_price",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Summa"),
			"name" => "sum",
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
		if (!$arr["request"]["proj"] && !$arr["request"]["cust"])
		{
			return PROP_IGNORE;
		}
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bill_task_list_t($t);

		if ($arr["request"]["cust"])
		{
			$i = get_instance(CL_CRM_COMPANY);
			$arr["request"]["proj"] = $i->get_projects_for_customer(obj($arr["request"]["cust"]));
		}
		$proj_i = get_instance(CL_PROJECT);
		$events = array();
		$awa = new aw_array($arr["request"]["proj"]);
		foreach($awa->get() as $p)
		{
			$events += $proj_i->get_events(array(
				"id" => $p,
				"range" => array(
					"start" => 1,
					"end" => time() + 24*3600*365*10
				)
			));
		}
		$task_i = get_instance(CL_TASK);
		foreach($events as $evt)
		{
			$o = obj($evt["id"]);
			if ($o->prop("send_bill"))
			{
				if ($o->prop("bill_no") == "")
				{
					$sum = 0;
					$hrs = 0;
					// get task rows and calc sum from those
					$rows = $task_i->get_task_bill_rows($o);
					foreach($rows as $row)
					{
						$sum += $row["sum"];
						$hrs += $row["amt"];
					}

					$t->define_data(array(
						"name" => html::get_change_url($o->id(), array("return_url" => get_ru()), parse_obj_name($o->name())),
						"oid" => $o->id(),
						"hrs" => $hrs,
						"hr_price" => number_format($o->prop("hr_price"),2),
						"sum" => number_format($sum,2)
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
			"sortable" => 1,
			"numeric" => 1
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
			"name" => "client_manager",
			"caption" => t("Kliendihaldur"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"sortable" => 1,
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "state",
			"caption" => t("Staatus"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "print",
			"caption" => t("Tr&uuml;ki"),
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_bills_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bills_list_t($t);
		
		if ($arr["request"]["bill_s_search"] == "")
		{
			// init default search opts
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			$arr["request"]["bill_s_client_mgr"] = $p->name();
			$arr["request"]["bill_s_from"]["day"] = date("d");
			$arr["request"]["bill_s_from"]["month"] = date("m");
			$arr["request"]["bill_s_from"]["year"] = date("Y")-1;

			$arr["request"]["bill_s_to"]["day"] = date("d");
			$arr["request"]["bill_s_to"]["month"] = date("m");
			$arr["request"]["bill_s_to"]["year"] = date("Y");

			$arr["request"]["bill_s_status"] = "0";
		}
		list($f1, $f2) = $this->_get_bill_search_filt($arr["request"], $arr["obj_inst"]->id());
		$bills = new object_list($f1);
		$bills2 = new object_list($f2);
		$bills->add($bills2->ids());
/*		}
		else
		{
			$bills = new object_list(array(
				"class_id" => CL_CRM_BILL,
				"parent" => $arr["obj_inst"]->id(),
				"state" => 0
			));	
		}*/
		$bill_i = get_instance(CL_CRM_BILL);
		foreach($bills->arr() as $bill)
		{
			$cust = "";
			$cm = "";
			if (is_oid($bill->prop("customer")) && $this->can("view", $bill->prop("customer")))
			{
				$tmp = obj($bill->prop("customer"));
				$cust = html::get_change_url($tmp->id(), array("return_url" => get_ru()), $tmp->name());
				$cm = html::obj_change_url($tmp->prop("client_manager"));
			}
			$cursum = $bill_i->get_sum($bill);
			$t->define_data(array(
				"bill_no" => html::get_change_url($bill->id(), array("return_url" => get_ru()), parse_obj_name($bill->prop("bill_no"))),
				"bill_date" => $bill->prop("bill_date"),
				"bill_due_date" => $bill->prop("bill_due_date"),
				"customer" => $cust,
				"state" => html::select(array(
					"options" => $bill_i->states,
					"selected" => $bill->prop("state"),
					"name" => "bill_states[".$bill->id()."]"
				)),
				"sum" => number_format($cursum, 2),
				"client_manager" => $cm,
				"oid" => $bill->id(),
				"print" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $bill->id(), "group" => "preview"), CL_CRM_BILL),
					"caption" => t("Tr&uuml;ki"),
					"target" => "_blank"
				))
			));
			$sum+= $cursum;
		}

		$t->set_default_sorder("desc");
		$t->set_default_sortby("bill_no");
		$t->sort_by();
		$t->set_sortable(false);

		$t->define_data(array(
			"sum" => "<b>".number_format($sum, 2)."</b>",
			"bill_no" => t("<b>Summa</b>")
		));
	}

	function _get_bill_s_client_mgr($arr)
	{
		if ($arr["request"]["bill_s_search"] == "")
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			$v = $p->name();
		}
		else
		{
			$v = $arr["request"]["bill_s_client_mgr"];
		}
		$arr["prop"]["value"] = html::textbox(array(
			"name" => "bill_s_client_mgr",
			"value" => $v,
			"size" => 25
		))."<a href='javascript:void(0)' onClick='document.changeform.bill_s_client_mgr.value=\"\"'><img src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0></a>";
		return PROP_OK;
	}

	function _get_bill_s_status($arr)
	{
		$b = get_instance(CL_CRM_BILL);
		$arr["prop"]["options"] = array("-1" => "") + $b->states;
		if ($arr["request"]["bill_s_search"] == "")
		{
			$arr["prop"]["value"] = 0;
		}
		else
		{
			$arr["prop"]["value"] = $arr["request"]["bill_s_status"];
		}
	}

	function _get_bill_search_filt($r, $p)
	{
		$ret = array(
			"class_id" => CL_CRM_BILL,
			"parent" => $p
		);

		if ($r["bill_s_bill_no"] != "")
		{
			$ret["bill_no"] = "%".$r["bill_s_bill_no"]."%";
		}

		$r["bill_s_from"] = date_edit::get_timestamp($r["bill_s_from"]);
		$r["bill_s_to"] = date_edit::get_timestamp($r["bill_s_to"]);

		if ($r["bill_s_from"] > 100 && $r["bill_s_to"] > 100)
		{
			$ret["bill_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $r["bill_s_from"], $r["bill_s_to"]);
		}
		else
		if ($r["bill_s_from"] > 100)
		{
			$ret["bill_date"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $r["bill_s_from"]);
		}
		else
		if ($r["bill_s_to"] > 100)
		{
			$ret["bill_date"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $r["bill_s_to"]);
		}

		if ($r["bill_s_status"] > -1)
		{
			$ret["CL_CRM_BILL.state"] = $r["bill_s_status"];
		}

		$ret2 = $ret;
		if ($r["bill_s_client_mgr"] != "")
		{
			$ret["CL_CRM_BILL.customer(CL_CRM_COMPANY).client_manager.name"] = map("%%%s%%", explode(",", $r["bill_s_client_mgr"]));
			$ret2["CL_CRM_BILL.customer(CL_CRM_PERSON).client_manager.name"] = map("%%%s%%", explode(",", $r["bill_s_client_mgr"]));
		}

		if ($r["bill_s_cust"] != "")
		{
			$ret["CL_CRM_BILL.customer(CL_CRM_COMPANY).name"] = "%".$r["bill_s_cust"]."%";
			$ret2["CL_CRM_BILL.customer(CL_CRM_PERSON).name"] = "%".$r["bill_s_cust"]."%";
		}
		return array($ret, $ret2);
	}

	function _get_bills_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta'),
			'action' => 'save_bill_list',
		));
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud arved'),
			"confirm" => t("Oled kindel et soovid valitud arved kustutada?"),
			'action' => 'delete_bills',
		));
	}
}
?>