<?php
/*
@classinfo  maintainer=markop
*/
class crm_company_bills_impl extends class_base
{
	function crm_company_bills_impl()
	{
		$this->init();
	}

	function _init_bill_proj_list_t(&$t, $custs)
	{
		$t->define_field(array(
			"caption" => t("Ava"),
			"name" => "open",
			"align" => "center",
			"sortable" => 1,
			"valign" => "top"
		));

		$t->define_field(array(
			"caption" => t("Projekt"),
			"name" => "name",
			"align" => "center",
			"sortable" => 1,
			"valign" => "top"
		));

		$t->define_field(array(
			"caption" => t("Klient"),
			"name" => "cust",
			"align" => "center",
			"sortable" => 1,
			"valign" => "top",
			"filter" => $custs
		));

		$t->define_field(array(
			"caption" => t("Summa"),
			"name" => "sum",
			"align" => "right",
			"sortable" => 1,
			"valign" => "top",
			"width" => "50%"
		));
	}

	function _get_bill_proj_list($arr)
	{
		if ($arr["request"]["proj"] || $arr["request"]["cust"])
		{
			return PROP_IGNORE;
		}
/*
	//konvertimise algoritm
	$cnt = 0;

	if(aw_global_get("uid") == "Teddi.Rull"){
		$tasks = new object_list(array(
			"class_id" => CL_TASK,
			"lang_id" => array(),
			"site_id" => array(),
			"brother_of" => new obj_predicate_prop("id"),
		));
		arr($tasks->count());
		foreach($tasks->arr() as $task)
		{
			foreach($task->get_all_rows() as $row_id)
			{$row = obj($row_id);
				if($row->prop("task")) continue;
				$cnt++;
				
//				print "rida id=".$row_id." nimi=".$row->name()." saab taski id=".$task->id()." nimega ".$task->name()."<br>\n";
				$row->set_prop("task", $task->id());
				$row->save();
			}
		}
		arr($cnt);
	}
*/

	enter_function("bills_impl::_get_bill_proj_list1");
		$t =& $arr["prop"]["vcl_inst"];
		$format = t('%s maksmata t&ouml;&ouml;d');
		//$t->set_caption(sprintf($format, $arr['obj_inst']->name()));
		//k6ik arvele minevad taskid
		$all_tasks = new object_list(array(
			"class_id" => CL_TASK,
			"send_bill" => 1,
	//		"is_done" => 1,
			"lang_id" => array(),
			"brother_of" => new obj_predicate_prop("id"),
		));

		// list all task rows that are not billed yet
		$rows = new object_list(array(
			"class_id" => CL_TASK_ROW,
			"bill_id" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
			"on_bill" => 1,
			"done" => 1,
			"task" => $all_tasks->ids(),
		));

		$projs = array();
		$tasks = new object_list();
		$sum2proj = array();
		$agreement_tasks = array();

		//kokkuleppehinnaga taskid
		$this->deal_tasks = array();
		foreach($all_tasks->arr() as $row)
		{
			if(strlen($row->prop("deal_price")) > 0)
			{
				$this->deal_tasks[] = $row->id();
				$projs[$row->prop("project")] = $row->prop("project");
				$sum2proj[$row->prop("project")] += str_replace(",", ".", $row->prop("deal_price"));
			}
		}

//		if ($rows->count())
//		{
//			$c = new connection();
//			$t2row = $c->find(array(
//				"from.class_id" => CL_TASK,
//				"to" => $rows->ids(),
//				"type" => "RELTYPE_ROW",
//				"to.bill_id" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
//				"to.on_bill" => 1,
//				"to.done" => 1,
//				"to.class_id" => CL_TASK_ROW,
//			));
			foreach($rows->arr() as $row)
			{
//				if(!$row->prop("task")) continue;
//				$project_id = $row->prop("task.project");
				$task = obj($row->prop("task"));
				if(in_array($task->id(), $this->deal_tasks))
				{
//					$agreement_tasks[] = $task;
					continue;
				}
				if ($task->prop("send_bill"))
				{
//					$row = obj($conn["to"]);
					$sum2proj[$task->prop("project")] += str_replace(",", ".", $row->prop("time_to_cust")) * $task->prop("hr_price");
					$tasks->add($task->id());
					$projs[$task->prop("project")] = $task->prop("project");
				}
			}
//		}

		exit_function("bills_impl::_get_bill_proj_list1");
		enter_function("bills_impl::_get_bill_proj_list2");
		//siia vaid need kokkuleppehinna taskid, millel on m6ni arvele minev rida ka olemas
//		foreach($agreement_tasks as $row)
//		{
//			$projs[$row->prop("project")] = $row->prop("project");
//			$sum2proj[$row->prop("project")] += str_replace(",", ".", $row->prop("deal_price"));
//		}


		// get all projects from the lists
/*		foreach($tasks->arr() as $row)
		{
			$projs[$row->prop("project")] = $row->prop("project");
		}
*/
		// list all meetings that are not billed yet
		$meetings = new object_list(array(
			"class_id" => CL_CRM_MEETING,
			"send_bill" => 1,
			"bill_no" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
			"is_done" => 8
		));
		foreach($meetings->arr() as $row)
		{
			$projs[$row->prop("project")] = $row->prop("project");
			$sum2proj[$row->prop("project")] += str_replace(",", ".", $row->prop("time_to_cust")) * $row->prop("hr_price");
		}

		$other_expenses = new object_list(array(
			"class_id" => CL_CRM_EXPENSE,
//			"on_bill" => 1,
			"bill_id" => '',
			"parent" => $all_tasks->ids(),
		));
		
		foreach($other_expenses->arr() as $row)
		{
//			$c = new connection();
//			$t2row = $c->find(array(
//				"to.class_id" => CL_CRM_EXPENSE,
//				"to" => $row->id(),
//				"to.send_bill" => 
//				"type" => "RELTYPE_EXPENSE"
//			));

//			foreach($t2row as $conn)
//			{
				$task = obj($row->parent());
//				$row = obj($conn["to"]);
//				if(!$task->prop("send_bill"))
//				{
//					continue;
//				}//if(aw_global_get("uid") == "Teddi.Rull") {arr($row->properties());}
				$projs[$task->prop("project")] = $task->prop("project");
				$sum2proj[$task->prop("project")] += $row->prop("cost");
//			}
		//	$projs[$row->prop("project")] = $row->prop("project");
		//	$sum2proj[$row->prop("project")] += str_replace(",", ".", $row->prop("time_to_cust")) * $row->prop("hr_price");
		}
		exit_function("bills_impl::_get_bill_proj_list2");
		enter_function("bills_impl::_get_bill_proj_list3");
		$custs = array();
		foreach($projs as $p)
		{
			if (!$this->can("view", $p))
			{
				continue;
			}
			$po = obj($p);
			$ord = $po->prop("orderer");
			$ord = is_array($ord) ? reset($ord) : $ord;
			$ord_name = "";
			if($this->can("view" , $ord))
			{
				$orderer = obj($ord);
				$ord_name = $orderer->name();
			}

			$lister = "<span id='cust".$po->id()."' style='display: none;'>";

			$table = new vcl_table;
			$table->name = "cust".$po->id();
			$params = array(
				"request" => array("proj" => $po->id(), "cust" => $ord),
				"prop" => array(
					"vcl_inst" => &$table
				)
			);
			$this->_get_bill_task_list($params);

			$lister .= $table->draw();
			$lister .= "</span>";
			$dat[] = array(
				"name" => html::obj_change_url($po),
				"open" => html::href(array(
					"url" => "#", //aw_url_change_var("proj", $p),
					"onClick" => "el=document.getElementById(\"cust".$po->id()."\"); if (navigator.userAgent.toLowerCase().indexOf(\"msie\")>=0){if (el.style.display == \"block\") { d = \"none\";} else { d = \"block\";} } else { if (el.style.display == \"table-row\") {  d = \"none\"; } else {d = \"table-row\";} }  el.style.display=d;",
					"caption" => t("Ava")
				)),
				"cust" => html::obj_change_url($ord),
				"sum" => number_format($sum2proj[$p], 2).$lister
,				"cust_name" => $ord_name,
			);
			if ($this->can("view", $ord))
			{
				$ordo = obj($ord);
				$custs[] = $ordo->name();
			}
		}
		sort($custs);
		$this->_init_bill_proj_list_t($t, array_unique($custs));
		foreach($dat as $dr)
		{
			$t->define_data($dr);
		}

		$t->set_default_sortby("cust_name");
		exit_function("bills_impl::_get_bill_proj_list3");
		return;

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
				foreach($rows as $row)
				{
					if (!$row["bill_id"])
					{
						$has_rows = true;
						$sum += $row["sum"];
					}
				}
			}

			if (!$has_rows)
			{
				continue;
			}
			$po = obj($p);
			$t->define_data(array(
				"name" => html::obj_change_url($po),
				"open" => html::href(array(
					"url" => aw_url_change_var("proj", $p),
					"caption" => t("Ava")
				)),
				"cust" => html::obj_change_url(reset($po->prop("orderer"))),
				"sum" => number_format($sum, 2)
			));
		}
	}

	function _init_bill_task_list_t(&$t, $proj)
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
			"align" => "right",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Tunni hind"),
			"name" => "hr_price",
			"align" => "right",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Summa"),
			"name" => "sum",
			"align" => "right",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Arvele m&auml;&auml;ramise kuup&auml;ev"),
			"name" => "set_date",
			"align" => "right",
			"sortable" => 1,
			"type" => "time",
			"format" => "d.m.Y"
		));

		$t->define_field(array(
			"name" => "count",
			"type" => "hidden",
		));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel".$proj
		));
	}

	function _get_bill_task_list($arr)
	{
		if (!$arr["request"]["proj"] && !$arr["request"]["cust"])
		{
			return PROP_IGNORE;
		}
		enter_function("bills_impl::_get_bill_task_list");
		$t =& $arr["prop"]["vcl_inst"];
		$t->unset_filter();
		$this->_init_bill_task_list_t($t, $arr["request"]["proj"]);

		// list all task rows that are not billed yet
		$rows = new object_list(array(
			"class_id" => array(CL_TASK_ROW,CL_CRM_MEETING,CL_CRM_EXPENSE),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
/*					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array(
							"class_id" => CL_TASK_ROW,
							"bill_id" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
							"on_bill" => 1,
							"done" => 1,
						)
					)),*/
					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array(
							"class_id" => CL_CRM_MEETING,
							"send_bill" => 1,
							"bill_no" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
							"flags" => array("mask" => OBJ_IS_DONE, "flags" => OBJ_IS_DONE)
						)
					)),
/*					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array(
							"class_id" => CL_CRM_EXPENSE,
	//						"on_bill" => 1,
	//						"bill_no" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
//							"flags" => array("mask" => OBJ_IS_DONE, "flags" => OBJ_IS_DONE)
						)
					))*/
				)
			))
		));
		//kokkuleppehinna jaoks
		//see ka optimaalsemaks vaja tegelt
		$all_tasks = new object_list(array(
			"class_id" => CL_TASK,
			"send_bill" => 1,
	//		"is_done" => 1,
			"project" => $arr["request"]["proj"],
			"brother_of" => new obj_predicate_prop("id"),
		));

		$tasks = new object_list();
		$sum2task = array();
		$hr2task = array();
		$task2row = array();
//		$deal_tasks = array();
//		$possible_task_rows = $possible_expenses = array();
		foreach($all_tasks->arr() as $row)
		{
//			$possible_task_rows = array_merge($possible_task_rows , $row->get_all_rows());
			$rows->add($row->get_all_expenses());
			if((strlen($row->prop("deal_price")) > 0) && ($row->prop("send_bill")))
			{
				$t->define_data(array(
						"oid" => $row->id(),
						"name" => $row->name(),
//						"hrs" => number_format(str_replace(",", ".", $row->prop("time_to_cust")), 2),
//						"hr_price" => number_format($row->prop("hr_price"),2),
						"sum" => $row->prop("deal_price").t("(Kokkuleppehind)"),
						"set_date" => $row->prop("to_bill_date"),
				));
//				$deal_tasks[] = $row->id();
				$sum2task[$row->id()] += str_replace(",", ".", $row->prop("deal_price"));
				$hr2task[$row->id()] += str_replace(",", ".", $row->prop("deal_amt"));
			}
		}
		
		//toimetuse read lykkas tahapoole, et saaks vaid need toimetuste read, mis on 6igete toimetuste kyljes
		$task_rows = new object_list(array(
			"class_id" => CL_TASK_ROW,
			"bill_id" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
			"on_bill" => 1,
			"done" => 1,
//			"oid" => $possible_task_rows
			"task" => $all_tasks->ids(),
		));
		
/*		$task_expenses = new object_list(array(
			"class_id" => CL_CRM_EXPENSE,
	//		"on_bill" => 1,
	//		"bill_no" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
//			"flags" => array("mask" => OBJ_IS_DONE, "flags" => OBJ_IS_DONE)
			"oid" => $possible_expenses,
		);
		$rows->add($task_expenses);*/
		$rows->add($task_rows);

		if ($rows->count())
		{
			$c = new connection();
			$t2row = $c->find(array(
				"from.class_id" => CL_TASK,
				"to" => $rows->ids(),
				"type" => "RELTYPE_ROW"
			));
			foreach($t2row as $conn)
			{
				$task = obj($conn["from"]);
				$row = obj($conn["to"]);

				$task2row[$task->id()][] = $row->id();
				if ($task->prop("project") == $arr["request"]["proj"])
				{
					if(!in_array($task->id(), $this->deal_tasks))
					{
						$sum2task[$task->id()] += str_replace(",", ".", $row->prop("time_to_cust")) * $task->prop("hr_price");
						$hr2task[$task->id()] += str_replace(",", ".", $row->prop("time_to_cust"));
						$tasks->add($conn["from"]);
					}
				}
			}
		}

		if ($rows->count())
		{
			$c = new connection();
			$t2row = $c->find(array(
				"to.class_id" => CL_CRM_EXPENSE,
				"to" => $rows->ids(),
//				"type" => "RELTYPE_EXPENSE"
			));
			foreach($t2row as $conn)
			{
				$task = obj($conn["from"]);
				$row = obj($conn["to"]);
				if(is_oid($row->prop("bill_id")) && $this->can("view" , $row->prop("bill_id"))) continue;
				$task2row[$task->id()][] = $row->id();
				if ($task->prop("project") == $arr["request"]["proj"])
				{
//					if(!in_array($task->id(), $deal_tasks))
//					{
						$sum2task[$task->id()] += str_replace(",", ".", $row->prop("cost"));
						$tasks->add($conn["from"]);
//					}
				}
			}
		}

		foreach($tasks->arr() as $o)
		{
			if(!$o->prop("send_bill"))
			{	
				continue;
			}
			$rs = $task2row[$o->id()];
			if (count($rs))
			{
				foreach($rs as $row_id)
				{
					$ro = obj($row_id);
				//	$sel_ = 0;
				//	if(in_array($_SESSION["task_sel"],$row_id)) $sel_ =1;
					if($ro->class_id() == CL_CRM_EXPENSE)
					{$date = $ro->prop("date");
						$t->define_data(array(
							"oid" => $row_id,
							"name" => $ro->name(),
							"sum" => number_format(str_replace(",", ".", $ro->prop("cost")),2),
							"set_date" => mktime(0,0,0, $date["month"], $date["day"], $date["year"]),
						));
						continue;
					}
					$t->define_data(array(
						"oid" => $row_id,
						"name" => $ro->prop("content"),
						"hrs" => number_format(str_replace(",", ".", $ro->prop("time_to_cust")), 3),
						"hr_price" => number_format($o->prop("hr_price"),2),
						"sum" => number_format(str_replace(",", ".", $ro->prop("time_to_cust")) * $o->prop("hr_price"),2),
						"set_date" => $ro->prop("to_bill_date"),
						"count" => html::hidden(array("name" => "count[".$o->id()."]" , "value" => count($rs))),
					));
				}
			}
			else
			{
				$t->define_data(array(
					"name" => html::obj_change_url($o),
					"oid" => $o->id(),
					"hrs" => number_format($hr2task[$o->id()], 3),
					"hr_price" => number_format($o->prop("hr_price"),2),
					"sum" => number_format($sum2task[$o->id()],2)
				));
			}
		}

		// list all meetings that are not billed yet
		$meetings = new object_list(array(
			"class_id" => CL_CRM_MEETING,
			"send_bill" => 1,
			"bill_no" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
			"is_done" => 1
		));
		foreach($meetings->arr() as $row)
		{
			$projs[$row->prop("project")] = $row->prop("project");
			$sum2proj[$row->prop("project")] += str_replace(",", ".", $row->prop("time_to_cust")) * $row->prop("hr_price");
		}

		foreach($projs as $p)
		{
			$po = obj($p);
			$ord = $po->prop("orderer");
			$t->define_data(array(
				"name" => html::obj_change_url($po),
				"open" => html::href(array(
					"url" => aw_url_change_var("proj", $p),
					"caption" => t("Ava")
				)),
				"cust" => html::obj_change_url(reset($ord)),
				"sum" => number_format($sum2proj[$p], 2)
			));
		}
		$t->set_default_sorder("asc");
		$t->set_default_sortby("set_date");
		$t->sort_by();
exit_function("bills_impl::_get_bill_task_list");
		return;
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
						if (!$row["bill_id"] && $row["is_done"])
						{
							$sum += $row["sum"];
							$hrs += $row["amt"];
						}
					}

					if ($sum > 0)
					{
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
	}


	function _get_bill_tb($arr)
	{
		$_SESSION["create_bill_ru"] = get_ru();
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Lisa'),
			'url' => html::get_new_url(CL_CRM_BILL, $arr["obj_inst"]->id(), array("return_url" => get_ru()))
		));

		//if ($arr["request"]["proj"])
		//{
			$tb->add_button(array(
				"name" => "create_bill",
				"img" => "save.gif",
				"tooltip" => t("Koosta arve"),
				"action" => "create_bill"
			));
		//}
		$tb->add_button(array(
			"name" => "search_bill",
			"img" => "search.gif",
			"tooltip" => t("Otsi"),
	//		"action" => "search_bill"
			"url" => "javascript:aw_popup_scroll('".$this->mk_my_orb("search_bill", array("openprintdialog" => 1,))."','Otsing',550,500)",
		));
	}

	/**
		@attrib name=search_bill
	**/
	function search_bill($arr)
	{
		if($_GET["sel"])
		{
			echo t("Valitud t&ouml;&ouml;d on teostatud erinevatele klientidele!");
			classload("vcl/table");
			$t = new aw_table(array(
				"layout" => "generic"
			));
			$t->define_field(array(
				"name" => "bill",
				"caption" => t("Arve"),
				"sortable" => 1,
			));
			$t->define_field(array(
			"name" => "customer",
			"sortable" => 1,
			"caption" => t("Klient")
			));
			$t->define_field(array(
				"name" => "select_this",
				"caption" => t("Vali"),
			));
			$t->set_default_sortby("name");

			$filter["lang_id"] = array();
			$filter["site_id"] = array();
			$filter["class_id"] = CL_CRM_BILL;
			$filter["state"] = 0;


			$ol = new object_list($filter);

			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				$customer = "";
				if(is_oid($o->prop("customer")) && $this->can("view" , $o->prop("customer")))
				{
					$customer_obj = obj($o->prop("customer"));
					$customer = $customer_obj->name();
				}
				$dat = array(
					"bill" => html::obj_change_url($o),
					"customer" => $customer,
					"select_this" => html::href(array(
						"url" => $this->mk_my_orb("search_bill", array("bill_id" => $o->id(),)),
						"caption" => t("Vali see"),
					)),
				);
				$t->define_data($dat);
			}
			$t->sort_by();
			return $t->draw();

		}
		if($_GET["bill_id"])
		{
			$_SESSION["bill_id"] = $_GET["bill_id"];
			die("
				<html><body><script language='javascript'>
					window.opener.submit_changeform('create_bill');
					window.close();
				</script></body></html>
			");
		}
		classload("vcl/table");
		$t = new aw_table(array(
			"layout" => "generic"
		));
		$t->define_field(array(
			"name" => "oid",
			"caption" => t("OID"),
			"sortable" => 1,
		));
			$t->define_field(array(
			"name" => "name",
			"sortable" => 1,
			"caption" => t("Nimi")
		));
			$t->define_field(array(
			"name" => "parent",
			"sortable" => 1,
			"caption" => t("Asukoht")
		));
			$t->define_field(array(
			"name" => "modifiedby",
			"sortable" => 1,
			"caption" => t("Muutja")
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"sortable" => 1,
			"format" => "d.m.Y H:i",
			"type" => "time"
		));
		$t->define_field(array(
			"name" => "select_this",
			"caption" => t("Vali"),
		));
		$t->set_default_sortby("name");

		$filter["lang_id"] = array();
		$filter["site_id"] = array();
		$filter["class_id"] = CL_CRM_BILL;
		$filter["state"] = 0;
		$ol = new object_list($filter);
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$dat = array(
				"oid" => $o->id(),
				"name" => html::obj_change_url($o),
				"parent" => $o->path_str(array("max_len" => 3)),
				"modifiedby" => $o->modifiedby(),
				"modified" => $o->modified(),
				"select_this" => html::href(array(
					"url" => $this->mk_my_orb("search_bill", array("bill_id" => $o->id(),)),
					"caption" => t("Vali see"),
				)),
			);
			$t->define_data($dat);
		}

		$t->sort_by();
		return $t->draw();
	}

	function _init_bills_list_t(&$t, $r)
	{
		$t->define_field(array(
			"name" => "bill_no",
			"caption" => t("Number"),
			"sortable" => 1,
			"numeric" => 1
		));
		if ($r["group"] == "bills_monthly")
		{
			$t->define_field(array(
				"name" => "create_new",
				"caption" => t("Loo uus"),
				"sortable" => 1,
				"numeric" => 1
			));
		}
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
			"numeric" => 1,
			"align" => "right"
		));

		if ($r["group"] != "bills_monthly")
		{
			$t->define_field(array(
				"name" => "state",
				"caption" => t("Staatus"),
				"sortable" => 1
			));
		}
		$t->define_field(array(
			"name" => "print",
			"caption" => t("Prindi"),
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
		$this->_init_bills_list_t($t, $arr["request"]);

		if($arr["request"]["bill_s_with_tax"] == 0)
		{
			$tax_add = 2;
		}
		else
		{
			$tax_add = $arr["request"]["bill_s_with_tax"];
		}
		$cg = $arr["request"]["currency_grouping"];
		$d = get_instance("applications/crm/crm_data");
		if(!$arr["request"]["bill_s_with_tax"])
		{
			$tax_add = 2;
		}
		else
		{
			$tax_add = $arr["request"]["bill_s_with_tax"];
		}
		
		if ($arr["request"]["group"] == "bills_monthly")
		{
			$bills = $d->get_bills_by_co($arr["obj_inst"], array("monthly" => 1));
			$format = t('%s kuuarved');
		}
		else
		{
			$filt = array();
			if ($arr["request"]["bill_s_search"] == "")
			{
				// init default search opts
				$u = get_instance(CL_USER);
				$p = obj($u->get_current_person());
				$filt["client_mgr"] = $p->name();
				$filt["bill_date_range"] = array(
					"from" => mktime(0,0,0, date("m"), date("d"), date("Y")-1),
					"to" => time()
				);
				$filt["state"] = "0";
			}
			else
			{//arr($arr["request"]);
				if ($arr["request"]["bill_s_cust"] != "")
				{
					$filt["customer"] = "%".$arr["request"]["bill_s_cust"]."%";
				}
				if($arr["request"]["bill_s_bill_no"] && $arr["request"]["bill_s_bill_to"])
				{
					$filt["bill_no"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $arr["request"]["bill_s_bill_no"] , $arr["request"]["bill_s_bill_to"], "int");
//					$filt["bill_no"] = "%".$arr["request"]["bill_s_bill_no"]."%";
				}
				elseif($arr["request"]["bill_s_bill_no"])
				{
//					$filt["bill_no"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $arr["request"]["bill_s_bill_no"], "","int");
					$filt["bill_no"] = $arr["request"]["bill_s_bill_no"];
				}
				elseif($arr["request"]["bill_s_bill_to"])
				{
//					$filt["bill_no"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $arr["request"]["bill_s_bill_to"], "","int");
					$filt["bill_no"] = $arr["request"]["bill_s_bill_to"];
				}
				$filt["bill_date_range"] = array(
					"from" => date_edit::get_timestamp($arr["request"]["bill_s_from"]),
					"to" => date_edit::get_day_end_timestamp($arr["request"]["bill_s_to"])
				);
				if ($arr["request"]["bill_s_client_mgr"] != "")
				{
					$filt["client_mgr"] = "%".$arr["request"]["bill_s_client_mgr"]."%";
				}
				if($arr["request"]["bill_s_status"] == -6)
				{
					$filt["on_demand"] = 1;
				}
				else
				{
					$filt["state"] = $arr["request"]["bill_s_status"];
				}
			}//arr($filt);
			$bills = $d->get_bills_by_co($arr["obj_inst"], $filt);
			$format = t('%s arved');
		}

		//$t->set_caption(sprintf($format, $arr['obj_inst']->name()));

		$bill_i = get_instance(CL_CRM_BILL);
		$curr_inst = get_instance(CL_CURRENCY);
		$co_stat_inst = get_instance("applications/crm/crm_company_stats_impl");
		$company_curr = $co_stat_inst->get_company_currency();

		if ($arr["request"]["export_hr"] > 0)
		{
			if (is_array($arr["request"]["bi"]) && count($arr["request"]["bi"]))
			{
				$bills = new object_list();
				$bills->add($arr["request"]["bi"]);
			}
			$this->_do_export_hr($bills, $arr, $arr["request"]["export_hr"]);
		}
		$sum_in_curr = $bal_in_curr = array();
		$balance = 0;
		foreach($bills->arr() as $bill)
		{
			$cust = "";
			$cm = "";
			if (is_oid($bill->prop("customer")) && $this->can("view", $bill->prop("customer")))
			{
				$tmp = obj($bill->prop("customer"));
				$cust = html::get_change_url($tmp->id(), array("return_url" => get_ru()), $bill_i->get_customer_name($bill->id()));
				$cm = html::obj_change_url($tmp->prop("client_manager"));
			}
			if ($arr["request"]["group"] == "bills_search")
			{
				$state = $bill_i->states[$bill->prop("state")];
			}	
			else
			{
				$state = html::select(array(
					"options" => $bill_i->states,
					"selected" => $bill->prop("state"),
					"name" => "bill_states[".$bill->id()."]",
					"width" => 100,
				));
			}

			$cursum = $own_currency_sum = $bill_i->get_bill_sum($bill,$tax_add);
			$curid = $bill->prop("customer.currency");
			$cur_name = $bill->get_bill_currency_name();
			if($company_curr && $curid && ($company_curr != $curid))
			{
				$own_currency_sum  = $co_stat_inst->convert_to_company_currency(array(
					"sum" =>  $cursum,
					"o" => $bill,
				));
			}
			if($cg)//kliendi valuutas
			{
				$sum_str = number_format($cursum, 2)." ".$cur_name;
				$sum_in_curr[$cur_name] += $cursum;
			}
			else//oma organisatsiooni valuutas
			{
				$sum_str = number_format($own_currency_sum, 2);
			}

			$pop = get_instance("vcl/popup_menu");
			$pop->begin_menu("bill_".$bill->id());
			$pop->add_item(Array(
				"text" => t("Prindi arve"),
				"link" => "#",
				"oncl" => "onClick='window.open(\"".$this->mk_my_orb("change", array("openprintdialog" => 1,"id" => $bill->id(), "group" => "preview"), CL_CRM_BILL)."\",\"billprint\",\"width=100,height=100\");'"
			));
			$pop->add_item(Array(
				"text" => t("Prindi arve lisa"),
				"link" => "#",
				"oncl" => "onClick='window.open(\"".$this->mk_my_orb("change", array("openprintdialog" => 1,"id" => $bill->id(), "group" => "preview_add"), CL_CRM_BILL)."\",\"billprintadd\",\"width=100,height=100\");'"
			));
			$pop->add_item(array(
				"text" => t("Prindi arve koos lisaga"),
				"link" => "#",
				"oncl" => "onClick='window.open(\"".$this->mk_my_orb("change", array("openprintdialog_b" => 1,"id" => $bill->id(), "group" => "preview"), CL_CRM_BILL)."\",\"billprintadd\",\"width=100,height=100\");'"
			));
			$partial = "";
			if($bill->prop("state") == 3 && $bill->prop("partial_recieved") && $bill->prop("partial_recieved") < $cursum) $partial = '<br>'.t("osaliselt");
			$bill_data = array(
				"bill_no" => html::get_change_url($bill->id(), array("return_url" => get_ru()), parse_obj_name($bill->prop("bill_no"))),
				"create_new" => html::href(array(
					"url" => $this->mk_my_orb("create_new_monthly_bill", array(
						"id" => $bill->id(), 
						"co" => $arr["obj_inst"]->id(),
						"post_ru" => get_ru()
						), CL_CRM_COMPANY),
					"caption" => t("Loo uus")
				)),
				"bill_date" => $bill->prop("bill_date"),
				"bill_due_date" => $bill->prop("bill_due_date"),
				"customer" => $cust,
				"state" => $state.$partial,
				"sum" => $sum_str,
				"client_manager" => $cm,
				"oid" => $bill->id(),
				"print" => $pop->get_menu(),
			);
			if($arr["request"]["show_bill_balance"])
			{
				$curr_balance = $bill->get_bill_needs_payment();
				if($company_curr && $curid && ($company_curr != $curid))
				{
					
					$total_balance = $own_currency_sum;
					foreach($bill->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
					{
						$p = $conn->to();
						if($p->prop("currency_rate") && $p->prop("currency_rate") != 1)
						{
							$total_balance -= $p->get_free_sum($bill->id()) / $p->prop("currency_rate");
						}
						else
						{
							$total_balance -= $curr_inst->convert(array(
								"from" => $curid,
								"to" => $company_curr,
								"sum" => $p->get_free_sum($bill->id()),
								"date" =>  $p->prop("date"),
							));
						}
					}
				}
				else
				{
					$total_balance = $curr_balance;
				}

				if($cg)
				{
					$bill_data["balance"] = number_format($curr_balance, 2)." ". $bill->get_bill_currency_name();
					$bal_in_curr[$cur_name] += $curr_balance;
				}
				else
				{
					$bill_data["balance"] = number_format($total_balance, 2);
				}
				$balance += $total_balance;
//				$bill_data["balance"] = number_format($bill_data["balance"], 2);
			}

			$t->define_data($bill_data);
			// number_format here to round the number the same way in the add, so the sum is correct
			$sum+= number_format($own_currency_sum,2,".", "");
		}

		$t->set_default_sorder("desc");
		$t->set_default_sortby("bill_no");
		$t->sort_by();
		$t->set_sortable(false);

		$final_dat = array(
			"bill_no" => t("<b>Summa</b>")
		);
		if($cg)
		{
			foreach($sum_in_curr as $cur_name => $amount)
			{
				$final_dat["sum"] .= "<b>".number_format($amount, 2)." ".$cur_name."</b><br>";
				if($arr["request"]["show_bill_balance"])
				{
					$final_dat["balance"] .= "<b>".number_format($bal_in_curr[$cur_name], 2)." ".$cur_name."</b><br>";
				}
			}
			$co_currency_name = "";
			if($this->can("view" , $company_curr))
			{
				$company_curr_obj = obj($company_curr);
				$co_currency_name = $company_curr_obj->name();
			}
			$final_dat["sum"] .= "<b>Kokku: ".number_format($sum, 2).$co_currency_name."</b><br>";
			if($arr["request"]["show_bill_balance"])
			{
				$final_dat["balance"] .= "<b>Kokku: ".number_format($balance, 2).$co_currency_name."</b><br>";
			}
		}
		else
		{
			$final_dat["sum"] = "<b>".number_format($sum, 2)."</b>";
			if($arr["request"]["show_bill_balance"])
			{
				$final_dat["balance"] .= "<b>".number_format($balance, 2)."</b><br>";
			}
		}
		$t->define_data($final_dat);
	}

	function _get_bill_s_with_tax($arr)
	{
		$arr["prop"]["options"] = array(
			0 => t("K&auml;ibemaksuta"),
			1 => t("K&auml;ibemaksuga"),
		);
		if($arr["request"]["bill_s_with_tax"] == "")
		{
			$arr["prop"]["value"] = 1;
		}
		else
		{
			$arr["prop"]["value"] = $arr["request"]["bill_s_with_tax"];
		}
		return PROP_OK;
	}

	function _get_bill_s_client_mgr($arr)
	{
		if ($arr["request"]["bill_s_search"] == "")
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());

			if($p->is_cust_mgr())
			{
				$v = $p->name();
			}
		}
		else
		{
			$v = $arr["request"]["bill_s_client_mgr"];
		}
		$tt = t("Kustuta");
		$arr["prop"]["value"] = html::textbox(array(
			"name" => "bill_s_client_mgr",
			"value" => $v,
			"size" => 25
		))."<a href='javascript:void(0)' onClick='document.changeform.bill_s_client_mgr.value=\"\"' title=\"$tt\" alt=\"$tt\"><img title=\"$tt\" alt=\"$tt\" src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0></a>";
		return PROP_OK;
	}

	function _get_bill_s_status($arr)
	{
		$b = get_instance(CL_CRM_BILL);
		$arr["prop"]["options"] = array("-1" => "") + $b->states + array("-6" => t("Sissen&otilde;udmisel"));
		if ($arr["request"]["bill_s_search"] == "")
		{
			$arr["prop"]["value"] = -1;
		}
		else
		{
			$arr["prop"]["value"] = $arr["request"]["bill_s_status"];
		}
	}

	function _get_bills_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Lisa'),
			'url' => html::get_new_url(CL_CRM_BILL, $arr["obj_inst"]->id(), array("return_url" => get_ru()))
		));

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

		$tb->add_separator();

		$tb->add_menu_button(array(
			'name'=>'export',
			'tooltip'=> t('Ekspordi'),
			"img" => "export.gif"
		));

		$last_bno = $arr["obj_inst"]->meta("last_exp_no");

		$tb->add_menu_item(array(
			'parent'=>'export',
			'text' => t("Hansa raama (ridadega)"),
			'link' => "#",
			"onClick" => "v=prompt('" . t("Sisesta arve number?") . "','$last_bno'); if (v != null) { window.location='".aw_url_change_var("export_hr", 1)."&exp_bno='+v;} else { return false; }"
		));

		$tb->add_menu_item(array(
			"parent" => "export",
			"text" => t("Hansa raama (koondatud)"),
			'link' => "#",
			"onClick" => "v=prompt('" . t("Sisesta arve number?") . "','$last_bno'); if (v != null) { window.location='".aw_url_change_var("export_hr", 2)."&exp_bno='+v;} else { return false; }"
		));
	}

	function _get_bills_mon_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta'),
			'action' => 'create_new_monthly_bill',
		));
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud arved'),
			"confirm" => t("Oled kindel et soovid valitud arved kustutada?"),
			'action' => 'delete_bills',
		));
	}

	function _get_bs_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "create_bill",
			"tooltip" => t("Loo arve"),
			"img" => "save.gif",
			"action" => "create_bill"
		));

	}

	function _do_export_hr($bills, $arr, $type = 1)
	{
		$u = get_instance(CL_USER);
		$i = get_instance(CL_CRM_BILL);
		$p = obj($u->get_current_person());
		$co = obj($u->get_current_company());
		$fn = trim(mb_strtoupper($p->prop("firstname")));

		$ct = array();

		$renumber = false;
		if ($_GET["exp_bno"] > 0)
		{
			$renumber = true;
			$bno = $_GET["exp_bno"];
		}
		$min = time() + 24*3600*10;
		$max = 0;
		foreach($bills->arr() as $b)
		{

			$agreement_price = $b->meta("agreement_price");
			if ($renumber)
			{
				$b->set_prop("bill_no", $bno);
				$b->set_name(sprintf(t("Arve nr %s"), $bno));
				// change bill numbers for all tasks that this bill is to
				$ol = new object_list(array(
					"class_id" => CL_TASK,
					"bill_no" => $b->prop("bill_no"),
					"lang_id" => array(),
					"site_id" => array()
				));
				foreach($ol->arr() as $task)
				{
					$task->set_prop("bill_no", $bno);
					$task->save();
				}
				$b->save();
				$bno++;
			}

			$tmp = array();
			foreach((array)$b->prop("signers") as $signer_id)
			{
				if ($this->can("view", $signer_id))
				{
					$signer_o = obj($signer_id);
					$tmp[] = $signer_o->prop("comment");
				}
			}
			$rfn = join(",", $tmp);

			if ($rfn == "" && $this->can("view", $b->prop("customer")))
			{
				$cc = get_instance(CL_CRM_COMPANY);
				$crel = $cc->get_cust_rel(obj($b->prop("customer")));
				if ($crel)
				{
					if ($this->can("view", $crel->prop("client_manager")))
					{
						$clm = obj($crel->prop("client_manager"));
						$rfn = $clm->prop("comment");
					}
				}
				else
				if ($this->can("view", $b->prop("customer.client_manager")))
				{
					$clm = obj($b->prop("customer.client_manager"));
					$rfn = $clm->prop("comment");
				}
			}

			if ($rfn == "")
			{
				//$rfn = $fn;
				$rfn = $p->prop("comment");
			}
			$rfn = str_replace("\n", "", str_replace("\r", "", trim($rfn)));
			$penalty = "0,00";
			if ($this->can("view", $b->prop("customer")))
			{
				$cust = obj($b->prop("customer"));
				if($cust->prop("bill_penalty_pct")) $penalty = str_replace("." , "," , $cust->prop("bill_penalty_pct"));
				else $penalty = str_replace("." , "," , $co->prop("bill_penalty_pct"));
			}

			if($b->prop("bill_trans_date")>1)
			{
				$date = date("d.m.Y", $b->prop("bill_trans_date"));
			}
			else
			{
				$date = date("d.m.Y", $b->prop("bill_date"));
			}
			$min = min($min, $b->prop("bill_date"));
			$max = max($max, $b->prop("bill_date"));


			// bill info row
			$brow = array();
			$brow[] = $b->prop("bill_no");				// arve nr
			$brow[] = date("d.m.Y", $b->prop("bill_date"));		// arve kuup
			$brow[] = date("d.m.Y", $b->prop("bill_due_date"));	// tasumistahtaeg
			$brow[] = 0;						// 0 (teadmata - vaikevaartus 0)
			$brow[] = 1;						// 1 (teadmata -vaikevaartus 1)
			$brow[] = $b->prop("bill_due_date_days"); 		// 7(tasumistingimuse kood - vordusta hetkel paevade arvuga)
			$brow[] = 7;						// 7(tasumistingimus)
			$brow[] = "";
			$brow[] = "";
			$brow[] = "";
			$brow[] = "";
			//$brow[] = "";
			$brow[] = 0;						// 0 (teadmata - vaikevaartus 0)
			$brow[] = $penalty;					// 0,00 (teadmata - vaikevaartus 0,00) viivis
			$brow[] = "";
			$brow[] = 1;						// 1 (teadmata - vaikevaartus 1)
			$brow[] = "";
			$brow[] = $rfn;						// OBJEKT (kasutaja eesnimi suurte tahtedega, nt TEDDI)
			$brow[] = "";
			$brow[] = 0;						//  0 (teadmata - vaikevaartus 0)
			$i = get_instance(CL_CRM_BILL);
			$cur = $i->get_bill_currency($b);
			$brow[] = "";
			$brow[] = "";
			$brow[] = "";
			$brow[] = ($cur ? $cur : t("EEK"));			// EEK (valuuta)
			$brow[] = $cur == "EUR" ? "15,64664" : "";
			$brow[] = $date;					// arve kuupaev//////////////
			$brow[] = 0;						// (teadmata - vaikevaartus 0)
			$brow[] = "";
			$brow[] = "";
			$brow[] = $cur == "EUR" ? "1" : "";
			if (true || $cur == "EEK")
			{
				$brow[] = "";
				$brow[] = "";
			}
			else
			{
				$brow[] = "15,65";					// (EURO kurss)
				$brow[] = "1,00";					// (kursi suhe, vaikevaartus 1,00)
			}
			$brow[] = "";
			$brow[] = "";
			$ct[] = join("\t", $brow);

			// customer info row
			$custr = array();
			if ($this->can("view", $b->prop("customer")))
			{
				$cust = obj($b->prop("customer"));

				$custr[] = str_replace("\n", "", str_replace("\r", "", trim($cust->comment())));	// kliendi kood hansaraamas
				$custr[] = str_replace("\n", "", str_replace("\r", "", trim($i->get_customer_name($b->id()))))." ".str_replace("\n", "", str_replace("\r", "", trim($cust->prop("ettevotlusvorm.shortname"))));	// kliendi kood hansaraamas

				/*
				if($cust->class_id() == CL_CRM_PERSON)
				{
					$custr[] = $cust->prop_str("address");
					$custr[] = $cust->prop("address.postiindeks")." ".$cust->prop("address.riik.name");
				}
				else
				{
					$custr[] = $cust->prop_str("contact");
					$custr[] = $cust->prop("contact.postiindeks")." ".$cust->prop("contact.riik.name");
				}
				*/
				$custr[] = $i->get_customer_address($b->id());
				$custr[] = $i->get_customer_address($b->id() , "index")." ".$i->get_customer_address($b->id() , "country");

				$cust_code = str_replace("\n", "", str_replace("\r", "", trim($i->get_customer_code($b->id()))));
				list($cm) = explode(" ", $cust->prop_str("client_manager"));
				$cm = mb_strtoupper($cm);
			}
			else
			{
				$custr[] = "";
				$custr[] = "";
				$custr[] = "";
				$custr[] = "";
			}
			$ct[] = join("\t", $custr)."\t\t\t\t\t";
			$ct[] = join("\t", array("", "", "", ""));	// esindajad

			// payment row
			$pr = array();
			$pr[] = "0,00";	// (teadmata - vaikevaartus 0,00)
//			$pr[] = str_replace(".", ",", round($i->get_bill_sum($b,BILL_SUM_WO_TAX)*2.0+0.049,1)/2.0);		// 33492,03 (summa kaibemaksuta)
			$pr[] = str_replace(".", ",", $i->get_bill_sum($b,BILL_SUM_WO_TAX));		// 33492,03 (summa kaibemaksuta)

			$pr[] = "";
			$pr[] = str_replace(".", ",", round($i->get_bill_sum($b,BILL_SUM_TAX)*2.0+0.049,1)/2.0);		// 6028,57 (kaibemaks)
			$pr[] = str_replace(".", ",", round($i->get_bill_sum($b,BILL_SUM)*2.0+0.049,1)/2.0);		// 39520,60 (Summa koos kaibemaksuga)
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "0,00";	//(teadmata - vaikevaartus 0,00)
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "0,00"; //(teadmata - vaikevaartus 0,00)
			$pr[] = "";	//LADU (voib ka tyhjusega asendada)
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";	// 90000 (teadmata, voib ka tyhjusega asendada)
			$pr[] = "";	// 00014 (teadmata, voib ka tyhjusega asendada)
			$pr[] = "";
			$pr[] = "0";	// (teadmata - vaikevaartus 0)
			$pr[] = "";

			$sum = round(str_replace(",", ".", $i->get_bill_sum($b,BILL_SUM))*2.0+0.049,1)/2.0;
			$pr[] = str_replace(".", ",", $sum);	//39520,60 (Summa koos kaibemaksuga)
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = str_replace(".", ",", $i->get_bill_sum($b,BILL_SUM_WO_TAX));		// 33492,03 (summa kaibemaksuta)
			$pr[] = "0";	// (teadmata - vaikevaartus 0)
			$pr[] = "0";	//  (teadmata - vaikevaartus 0)
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "0";	// (teadmata - vaikevaartus 0)
			$pr[] = "";
			$pr[] = "0";	// 0(teadmata - vaikevaartus 0)
			$pr[] = "0";	// (teadmata - vaikevaartus 0)
			$pr[] = "";
			$pr[] =	"0";	// (teadmata - vaikevaartus 0)
			$pr[] = "";
			//$pr[] = str_replace(".", ",", $i->get_bill_sum($b, BILL_AMT)); //77,00 (kogus kokku)
			$pr[] = "";
			$pr[] = "";	// (teadmata - vaikevaartus 0,00)
			$pr[] = "";	// (teadmata - vaikevaartus 0,00)
			$pr[] = "";		// (teadmata - vaikevaartus 0)
			$pr[] = "0";
			$pr[] = "";	//(teadmata - vaikevaartus 0)
			$pr[] = "0";	//(teadmata - vaikevaartus 0)
			$pr[] = "0";
			$pr[] = ""; //(teadmata - vaikevaartus 0)
			$pr[] = "";
			$ct[] = join("\t", $pr);

			$rows = $i->get_bill_rows($b);
			//kui eksisteerib kokkuleppe hind, siis votab selle ridade asemele

			if($agreement_price[0]["price"] && strlen($agreement_price[0]["name"]) > 0)
			{
				$rows = $agreement_price;
			}
			if($agreement_price["price"] && strlen($agreement_price["name"]) > 0)
			{
				$rows = array(0 => array(
					"amt" => $agreement_price["amt"],
					"date" => $agreement_price["date"],
					"unit" => $agreement_price["unit"],
					"price" => $agreement_price["price"],
					"has_tax" => $agreement_price["has_tax"],
					"comment" => $agreement_price["name"],
					"sum" => $agreement_price["sum"],

				));
			}
			if ($type == 1)
			{
			foreach($rows as $idx => $row)
			{
				$ri = array();
				$ri[] = "1";	// (teadmata, vaikevaartus 1))
				//$ri[] = $idx;	// TEST (artikli kood)
				//$ri[] = $row["code"];
				$code = "";
				$acct = "";
				if ($this->can("view", $row["prod"]))
				{
					$prod = obj($row["prod"]);
					$code = $prod->name();
					$acct = $prod->prop("tax_rate.acct");
				}
				$ri[] = $code;
				$ri[] = $row["amt"];	//33 (kogus)
				$dd = trim($row["name"]);
				if ($dd == "")
				{
					$dd = trim($row["comment"]);
				}
				$dd_bits = $this->split_by_word($dd);
				$ri[] = $dd_bits[0];	// testartikkel (toimetuse rea sisu)
				$ri[] = str_replace(".", ",", $row["price"]);	// 555,00 (yhiku hind)
//				$sum = round(str_replace(",", ".", $row["sum"])*2.0+0.049,1)/2.0;
				$sum = str_replace(",", ".", $row["sum"]);
				$ri[] = str_replace(".", ",",$sum);	// 16300,35 (rea summa km-ta)
				$ri[] = str_replace(".", ",", $b->prop("disc")); //11,0 (ale%)
				$ri[] = $acct;		// (konto)
				$ri[] = $this->_get_bill_row_obj_hr($row,$b); // isik siia
				$ri[] = "";
				$ri[] = "";
				$ri[] = str_replace(".", ",", $sum);	// 16300,35 (rea summa km-ta)
				$ri[] = "";
				//$ri[] = "1";	// (kaibemaksukood)
				$ri[] = $row["km_code"];
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = $row["unit"];	//TK (yhik)


				$ct[] = join("\t", $ri);
				for($i = 1; $i < count($dd_bits); $i++)
				{
					$ri = array();
					$ri[] = "1";
					$ri[] = "";
					$ri[] = "";
					$ri[] = $dd_bits[$i];
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ri[] = "";
					$ct[] = join("\t", $ri);
				}
			}
			}
			else
			{
				$code = $amt = $price = $sum = 0;
				foreach($rows as $idx => $row)
				{
					$code = $row["code"];
					$amt += str_replace(",", ".", $row["amt"]);
					$sum += str_replace(",", ".", $row["sum"]);
				}

				$price = $sum / $amt;
				$sum = round($sum*2.0+0.049,1)/2.0;
				$ri = array();
				$ri[] = "1";
				$ri[] = $code;
				$ri[] = $amt;
				$ri[] = $this->nice_trim($b->prop("notes"));
				$ri[] = number_format($price);
				$ri[] = str_replace(".", ",", $sum);
				$ri[] = str_replace(".", ",", $b->prop("disc"));
				$ri[] = 3100;
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = str_replace(".", ",", $sum);
				$ri[] = "";
				$ri[] = "1";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = $b->prop("gen_unit");
				$ct[] = join("\t", $ri);
			}
			$ct[] = ""; // next bill
		}
		if ($renumber)
		{
			$co = obj($_GET["id"]);
			$co->set_meta("last_exp_no", $bno);
			$co->save();
		}
		header("Content-type: text/plain");
		header('Content-Disposition: attachment; filename="arved.txt"');
		echo "format	\r\n";
		echo "1\t44\t1\t\r\n";
		echo "\r\n";
//		echo "sysformat	\r\n";
//		echo "1	1	1	1	.	,	 	\r\n";
//		echo "\n";
		echo "commentstring	\r\n";
		$co = get_current_company();
		$co_n = $co->name();
		$from = date("d.m.Y", $min);
		$to = date("d.m.Y", $max);
		echo "$co_n $from - $to\r\n";
		echo "\r\n";
		echo "fakt1	\r\n";

		die(join("\r\n", $ct));
	}

	function _get_bill_row_obj_hr($row, $b)
	{
		$ret = "";
		$comments = array();
		if (count($row["persons"]) == 0 && $this->can("view", $b->prop("customer")))
		{
			$cc = get_instance(CL_CRM_COMPANY);
			$crel = $cc->get_cust_rel(obj($b->prop("customer")));
			if (!$crel)
			{
				return "";
			}
			//return mb_strtoupper($crel->prop("client_manager.firstname"), aw_global_get("charset")
			$ret = $crel->prop("comment");
		}
		else
		{
			if(!count($row["persons"])) return "";
			$list = new object_list(array(
				"oid" => $row["persons"],
				"lang_id" => array(),
				"site_id" => array()
			));
			foreach($list->arr() as $person)
			{
				$comments[] = $person->prop("comment");
			}
			$ret = join(", " , $comments);
		}
		return $ret;
		//mb_strtoupper(join(", ", $list->names()), aw_global_get("charset"));
	}

	function nice_trim($s, $len = 250)
	{
		if (strlen($s) > $len)
		{
			return substr($s, 0, strrpos(substr($s, 0, $len), " "));
		}
		return $s;
	}

	function split_by_word($str, $len = 50)
	{
		$ret = array();
		do {
			$tmp = $this->nice_trim($str, 50);
			$ret[] = $tmp;
			$str = trim(substr($str, strlen($tmp)));
		} while ($str != "");
		return $ret;
	}
}
?>
