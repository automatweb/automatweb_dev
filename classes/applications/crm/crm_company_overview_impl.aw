<?php

class crm_company_overview_impl extends class_base
{
	function crm_company_overview_impl()
	{
		$this->init();
	}

	function _get_org_actions($arr)
	{
		$this->do_org_actions($arr, array());
	}

	function _get_org_calls($arr)
	{
		$args = array();
		$args["type"] = "RELTYPE_CALL";
		$this->do_org_actions($arr, $args);
	}

	function _get_org_meetings($arr)
	{
		$args = array();
		$args["type"] = "RELTYPE_KOHTUMINE";
		$this->do_org_actions($arr, $args);
	}
	
	function _get_org_tasks($arr)
	{
		$args = array();
		$args["type"] = "RELTYPE_TASK";
		$this->do_org_actions($arr, $args);
	}

	function get_overview($arr = array())
	{
		return $this->overview;
	}

	function do_org_actions($arr, $args)
	{
		// whee, this thing includes project and that uses properties, so we gots
		// to do this here or something. damn, we need to do the reltype
		// loading in get_instance or something
		$cfgu = get_instance("cfg/cfgutils");
		$cfgu->load_class_properties(array(
			"file" => "project",
			"clid" => 239
		));

		$ob = $arr["obj_inst"];
		$conns = $ob->connections_from($args);
		$t = &$arr["prop"]["vcl_inst"];

		$arr["prop"]["vcl_inst"]->configure(array(
			"overview_func" => array(&$this,"get_overview"),
		));

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => !empty($arr["request"]["viewtype"]) ? $arr["request"]["viewtype"] : $arr["prop"]["viewtype"],
		));

		$start = $range["start"];
		$end = $range["end"];

		$overview_start = $range["overview_start"];

		$classes = aw_ini_get("classes");

		$return_url = urlencode(aw_global_get("REQUEST_URI"));
		$planner = get_instance(CL_PLANNER);

		// gather a list of events to show
		$evts = array();

		// XXX: optimize the hell out of it. I have the range, I should use 
		// it.
		foreach($conns as $conn)
		{
			$evts[$conn->prop("to")] = $conn->prop("to");
		};

		$relinfo = $arr["obj_inst"]->get_relinfo();

		$prj = get_instance(CL_PROJECT);
		$evts = $evts + $prj->get_events_for_participant(array(
			"id" => $arr["obj_inst"]->id(),
			"clid" => $relinfo[$args["type"]]["clid"],
		));

		$ol = new object_list(array(
			"orderer" => $arr["obj_inst"]->id(),
			"class_id" => CL_CRM_OFFER,
		));
		foreach ($ol->arr() as $tmp)
		{	
			if($tmp->id() == $tmp->brother_of())
			{
				$evts[$tmp->id()] = $tmp->id();
			}
		}
		
		$this->overview = array();
		classload("core/icons");

		foreach($evts as $obj_id)
		{
			$item = new object($obj_id);
			// relative needs last n and next m items, those might be 
			// outside of the current range
			if ($range["viewtype"] != "relative" && $item->prop("start1") < $overview_start)
			{
				continue;
			};
			
			$icon = icons::get_icon_url($item);

			if ($item->class_id() == CL_DOCUMENT)
			{
				$link = $this->mk_my_orb("change",array(
					"id" => $item->id(),
					"return_url" => $return_url,
				),CL_DOCUMENT);
			}
			else
			{
				$link = $planner->get_event_edit_link(array(
					"cal_id" => $this->cal_id,
					"event_id" => $item->id(),
					"return_url" => $return_url,
				));
			};

			if ($item->prop("start1") > $start)
			{
				$t->add_item(array(
					"timestamp" => $item->prop("start1"),
					"data" => array(
						"name" => $item->name(),
						"link" => $link,
						"modifiedby" => $item->prop("modifiedby"),
						"icon" => $icon,
						'comment' => $item->comment(),
					),
				));
			};

			if ($item->prop("start1") > $overview_start)
			{
				$this->overview[$item->prop("start1")] = 1;
			};
		}
	}

	function _get_tasks_call($arr)
	{
		$prop = &$arr["prop"];
		$obj = $arr["obj_inst"];
		$conns = $obj->connections_from(array(
			"type" => "RELTYPE_CALL",
		));
		$rv = "";
		foreach($conns as $conn)
		{
			$target_obj = $conn->to();
			$inst = $target_obj->instance();
			if (method_exists($inst,"request_execute"))
			{
				$rv .= $inst->request_execute($target_obj);
			};
		};
		$prop["value"] = $rv;
	}

	function _init_my_tasks_t(&$t)
	{
		$t->define_field(array(
			"caption" => t("Klient"),
			"name" => "customer",
			"align" => "center",
			"chgbgcolor" => "col",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Projekt"),
			"name" => "proj_name",
			"align" => "center",
			"chgbgcolor" => "col",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Toimetus"),
			"name" => "name",
			"align" => "center",
			"chgbgcolor" => "col",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("T&auml;htaeg"),
			"name" => "deadline",
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"chgbgcolor" => "col",
			"format" => "d.m.Y H:i"
		));

		$t->define_field(array(
			"caption" => t("Prioriteet"),
			"name" => "priority",
			"chgbgcolor" => "col",
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1
		));

		$t->define_chooser(array(
			"chgbgcolor" => "col",
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_my_tasks($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_my_tasks_t($t);

		$i = get_instance(CL_CRM_COMPANY);
		if ($arr["request"]["group"] == "all_tasks")
		{
			// get all undone tasks
			$ol = new object_list(array(
				"class_id" => CL_TASK,
				"site_id" => array(),
				"lang_id" => array(),
				"is_done" => new obj_predicate_not(OBJ_IS_DONE),
				"brother_of" => new obj_predicate_prop("id")
			));
			$tasks = $ol->ids();
		}
		else
		{
			$tasks = $i->get_my_tasks();
		}

		if ($arr["request"]["act_s_sbt"] != "")
		{
			// filter
			$ol = new object_list($this->_get_tasks_search_filt($arr["request"], $tasks));
		}
		else
		{
			if (!count($tasks))
			{
				$ol = new object_list();
			}
			else
			{
				$ol = new object_list(array(
					"class_id" => CL_TASK,
					"oid" => $tasks,
					"is_done" => new obj_predicate_not(OBJ_IS_DONE)
				));
			}
		}

		foreach($ol->ids() as $task_id)
		{
			$task = obj($task_id);

			$cust = $task->prop("customer");
			if (is_oid($cust) && $this->can("view", $cust))
			{
				$cust_o = obj($cust);
				$cust_str = html::get_change_url($cust, array("return_url" => get_ru()), $cust_o->name());
			}

			$proj = $task->prop("project");
			if (is_oid($proj) && $this->can("view", $proj))
			{
				$proj_o = obj($proj);
				$proj_str = html::get_change_url($proj, array("return_url" => get_ru()), $proj_o->name());
			}

			$col = "";
			$dl = $task->prop("deadline");
			if (time() > $dl)
			{
				$col = "#BBBBBB";
			}
			else
			if (date("d.m.Y") == date("d.m.Y", $dl)) // today
			{
				$col = "#EEEEEE";
			}
			$t->define_data(array(
				"customer" => $cust_str,
				"proj_name" => $proj_str,
				"name" => html::get_change_url($task->id(), array("return_url" => get_ru()), $task->name()),
				"deadline" => $dl,
				"oid" => $task->id(),
				"priority" => $task->prop("priority"),
				"col" => $col
			));
		}

		$t->set_default_sortby("deadline");
		$t->set_default_sorder("asc");

		$t->sort_by(array(
			"field" => $arr["request"]["sortby"],
			"sorder" => ($arr["request"]["sortby"] == "priority" ? "desc" : $arr["request"]["sort_order"])
		));

		$t->set_sortable(false);
	}

	function _get_tasks_search_filt($r, $tasks)
	{
		$res = array(
			"class_id" => CL_TASK,
			"oid" => $tasks
		);

		if ($r["act_s_cust"] != "")
		{
			$res["CL_TASK.customer(CL_CRM_COMPANY).name"] = "%".$r["act_s_cust"]."%";
		}
		if ($r["act_s_task_name"] != "")
		{
			$res["name"] = "%".$r["act_s_task_name"]."%";
		}
		if ($r["act_s_code"] != "")
		{
			$res["code"] = "%".$r["act_s_code"]."%";
		}
		if ($r["act_s_proj_name"] != "")
		{
			$res["CL_TASK.project(CL_PROJECT).name"] = "%".$r["act_s_proj_name"]."%";
		}

		$r["act_s_dl_from"] = date_edit::get_timestamp($r["act_s_dl_from"]);
		$r["act_s_dl_to"] = date_edit::get_timestamp($r["act_s_dl_to"]);

		if ($r["act_s_dl_from"] > 1 && $r["act_s_dl_to"] > 1)
		{
			$res["deadline"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $r["act_s_dl_from"], $r["act_s_dl_to"]);
		}
		else
		if ($r["act_s_dl_from"] > 1)
		{
			$res["deadline"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $r["act_s_dl_from"]);
		}
		else
		if ($r["act_s_dl_to"] > 1)
		{
			$res["deadline"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $r["act_s_dl_to"]);
		}

		if ($r["act_s_status"] > 0 && $r["act_s_status"] < 3)
		{
			$res["is_done"] = $r["act_s_status"] == 1 ? 0 : 8;
		}
		return $res;
	}

	function _get_my_tasks_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		$url = $this->mk_my_orb('new',array(
			'alias_to_org' => $arr['obj_inst']->id(),
			'reltype_org' => 13,
			'class' => 'planner',
			'id' => $this->cal_id,
			'group' => 'add_event',
			'clid' => CL_TASK,
			'action' => 'change',
			'title' => t("Toimetus"),
			'parent' => $arr["obj_inst"]->id(),
			'return_url' => get_ru()
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text' => t('Toimetus'),
			'link' => $url
		));

		$tb->add_button(array(
			'name' => 'mark_as_done',
			'img' => 'save.gif',
			'tooltip' => t('M&auml;rgi tehtuks'),
			'action' => 'mark_tasks_done',
		));
	}
}
?>