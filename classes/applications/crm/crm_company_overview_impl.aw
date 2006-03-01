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
		$this->do_org_actions($arr, $args, CL_CRM_CALL);
	}

	function _get_org_meetings($arr)
	{
		$args = array();
		$args["type"] = "RELTYPE_KOHTUMINE";
		$this->do_org_actions($arr, $args, CL_CRM_MEETING);
	}
	
	function _get_org_tasks($arr)
	{
		$args = array();
		$args["type"] = "RELTYPE_TASK";
		$this->do_org_actions($arr, $args, CL_TASK);
	}

	function get_overview($arr = array())
	{
		return $this->overview;
	}

	function do_org_actions($arr, $args, $clid)
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

		$p = get_instance(CL_PLANNER);
		$cal = $p->get_calendar_for_user();
		if ($cal)
		{
			$calo = obj($cal);
			if (!$arr["request"]["viewtype"])
			{
				$arr["request"]["viewtype"] = $p->viewtypes[$calo->prop("default_view")];
			}

			$wds = safe_array($calo->prop("workdays"));
			$full_weeks = false;
			// if no workdays are defined, use all of them
			for($wd = 1; $wd <= 7; $wd++)
			{
				if(!$wds[$wd])
				{
					$full_weeks = false;
					break;
				}	
				else
				{
					$full_weeks = true;
				}
			}
			$arr["prop"]["vcl_inst"]->configure(array(
				"full_weeks" => $full_weeks
			));
		}

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

		$task_ol = $this->_get_task_list($arr);
		$evts = $this->make_keys($task_ol->ids());


		$this->overview = array();
		classload("core/icons");
		// get b-days
		if ($calo && $calo->prop("show_bdays") == 1)
		{
			$s_m = date("m", $start);
			$e_m = date("m", $end);
			$pred = $s_m > $e_m ? "OR" : "AND";
			$q = "
				SELECT 
					objects.name as name,
					objects.oid as oid,
					kliendibaas_isik.birthday as bd
				FROM 
					objects  LEFT JOIN kliendibaas_isik ON kliendibaas_isik.oid = objects.brother_of  
				WHERE	
					objects.class_id = '145' AND 
					objects.status > 0  AND
					kliendibaas_isik.birthday != -1 AND kliendibaas_isik.birthday != 0 AND kliendibaas_isik.birthday is not null
			";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$m = date("m", $row["bd"]);
				if (($s_m > $e_m ? ($m >= $s_m || $m <= $e_m) : ($m >= $s_m && $m <= $e_m)))
				{
					$evts[$row["oid"]] = $row["oid"];
				}	
			}
		}

		foreach($evts as $obj_id)
		{
			$item = new object($obj_id);
			// relative needs last n and next m items, those might be 
			// outside of the current range
			$date = $item->prop("start1");
			if ($item->class_id() == CL_CRM_DOCUMENT_ACTION)
			{
				$date = $item->prop("date");
			}
			else
			if ($item->class_id() == CL_CRM_PERSON && $calo)
			{
				$ds = $calo->prop("day_start");
				$bd = $item->prop("birthday");
				$date = mktime($ds["hour"], $ds["minute"], 0, date("m", $bd), date("d", $bd), date("Y"));
			}

			// if this thing has recurrences attached, then stick those in there
			$recurs = array();
			foreach($item->connections_from(array("type" => "RELTYPE_RECURRENCE")) as $c)
			{
				// get all times for this one from the recurrence table
				$this->db_query("SELECT recur_start, recur_end from recurrence where recur_id = ".$c->prop("to")." AND recur_start > $overview_start");
				while ($row = $this->db_next())
				{
					$recurs[] = $row;
				}
			}

			if ($range["viewtype"] != "relative" && ($date < $overview_start) && count($recurs) == 0)
			{
				continue;
			};

			$icon = icons::get_icon_url($item);

			if ($item->class_id() == CL_CRM_DOCUMENT_ACTION)
			{
				$t_c = reset($item->connections_to());
				$t_o = $t_c->from();
				$link = $this->mk_my_orb("change",array(
					"id" => $t_o->id(),
					"return_url" => $return_url,
				),$t_o->class_id());
			}
			else
			if ($item->class_id() == CL_DOCUMENT)
			{
				$link = $this->mk_my_orb("change",array(
					"id" => $item->id(),
					"return_url" => $return_url,
				),CL_DOCUMENT);
			}
			if ($item->class_id() == CL_CRM_PERSON)
			{
				$link = $this->mk_my_orb("change",array(
					"id" => $item->id(),
					"return_url" => $return_url,
				),CL_CRM_PERSON);
			}
			else
			{
				$link = $planner->get_event_edit_link(array(
					"cal_id" => $this->cal_id,
					"event_id" => $item->id(),
					"return_url" => $return_url,
				));
			};

			// if this thing has recurrences attached, then stick those in there
			foreach($recurs as $row)
			{
				$rd = $row["recur_start"];
				if ($rd > ($start-(7*24*3600)))
				{
					$t->add_item(array(
						"timestamp" => $rd,
						"item_start" => $rd,
						"item_end" => $row["recur_end"],
						"data" => array(
							"name" => $item->class_id() == CL_CRM_PERSON ? sprintf(t("%s s&uuml;nnip&auml;ev!"), $item->name()) : $item->name(),
							"link" => $link,
							"modifiedby" => $item->prop("modifiedby"),
							"icon" => $icon,
							'comment' => $item->comment(),
						),
					));
				};

				if ($rd > $overview_start)
				{
					$this->overview[$rd] = 1;
				};
			}

			if ($date > ($start-(7*24*3600)))
			{
				$t->add_item(array(
					"timestamp" => $date,
					"item_start" => ($item->class_id() == CL_CRM_MEETING ? $item->prop("start1") : NULL),
					"item_end" => ($item->class_id() == CL_CRM_MEETING ? $item->prop("end") : NULL),
					"data" => array(
						"name" => $item->class_id() == CL_CRM_PERSON ? sprintf(t("%s s&uuml;nnip&auml;ev!"), $item->name()) : $item->name(),
						"link" => $link,
						"modifiedby" => $item->prop("modifiedby"),
						"icon" => $icon,
						'comment' => $item->comment(),
					),
				));
			};

			if ($date > $overview_start)
			{
				$this->overview[$date] = 1;
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

	function _init_my_tasks_t(&$t, $data = false, $r = array())
	{
		if (is_array($data) && $r["act_s_print_view"] != 1)
		{
			$filt = array();
			foreach($data as $row)
			{
				$filt["customer"][] = strip_tags($row["customer"]);
				$filt["proj_name"][] = strip_tags($row["proj_name"]);
				$filt["priority"][] = strip_tags($row["priority"]);
				$part = strip_tags($row["parts"]);
				foreach(explode(",", $part) as $nm)
				{
					$filt["parts"][] = trim($nm);
				}
			}
		}

		$t->define_field(array(
			"caption" => t(""),
			"name" => "icon",
			"align" => "center",
//			"chgbgcolor" => "col",
			"sortable" => 1,
			"width" => 1
		));

		if ($r["act_s_print_view"] != 1)
		{
			$t->define_field(array(
				"caption" => t(""),
				"name" => "menu",
				"align" => "center",
	//			"chgbgcolor" => "col",
			));
		}

		$t->define_field(array(
			"caption" => t("Pealkiri"),
			"name" => "name",
			"align" => "center",
//			"chgbgcolor" => "col",
			"sortable" => 1
		));

		if ($r["group"] == "meetings")
		{
			$t->define_field(array(
				"caption" => t("Toimumisaeg"),
				"name" => "when",
				"align" => "center",
	//			"chgbgcolor" => "col",
			));
		}

		$t->define_field(array(
			"caption" => t("Klient"),
			"name" => "customer",
			"align" => "center",
//			"chgbgcolor" => "col",
			"sortable" => 1,
			"filter" => array_unique($filt["customer"])
		));

		$t->define_field(array(
			"caption" => t("Projekt"),
			"name" => "proj_name",
			"align" => "center",
//			"chgbgcolor" => "col",
			"sortable" => 1,
			"filter" => array_unique($filt["proj_name"])
		));

		if ($r["group"] != "meetings")
		{
			$t->define_field(array(
				"caption" => t("Aeg"),
				"name" => "deadline",
				"align" => "center",
				"sortable" => 1,
				"numeric" => 1,
				//"type" => "time",
				"chgbgcolor" => "col",
				//"format" => "d.m.Y H:i",
				"callback" => array(&$this, "_format_deadline"),
				"callb_pass_row" => 1
			));
		}

		if ($r["group"] != "ovrv_offers")
		{
			$t->define_field(array(
				"caption" => t("Prioriteet"),
				"name" => "priority",
	//			"chgbgcolor" => "col",
				"align" => "center",
				"sortable" => 1,
				"numeric" => 1,
				"filter" => array_unique($filt["priority"])
			));
		}

		$t->define_field(array(
			"caption" => t("Osalejad"),
			"name" => "parts",
			"align" => "center",
//			"chgbgcolor" => "col",
			"sortable" => 1,
			"filter" => array_unique($filt["parts"])
		));

		if ($r["act_s_print_view"] != 1)
		{
			$t->define_chooser(array(
		//			"chgbgcolor" => "col",
				"field" => "oid",
				"name" => "sel"
			));
		}
	}

	function _get_my_tasks($arr)
	{
		if (aw_global_get("crm_task_view") != CRM_TASK_VIEW_TABLE)
		{
			return PROP_IGNORE;
		}
		classload("core/icons");

		$ol = $this->_get_task_list($arr);
		if ($arr["request"]["group"] == "ovrv_offers")
		{
			return $this->_get_ovrv_offers($arr, $ol);
		}
		$pm = get_instance("vcl/popup_menu");

		// make task2person list
		$task2person = $this->_get_participant_list_for_tasks($ol->ids());

		$task2recur = $this->_get_recur_list_for_tasks($ol->ids());

		$table_data = array();
		foreach($ol->ids() as $task_id)
		{
			$task = obj($task_id);

			$cust = $task->prop("customer");
			$cust_str = "";
			if (is_oid($cust) && $this->can("view", $cust))
			{
				$cust_o = obj($cust);
				$cust_str = html::get_change_url($cust, array("return_url" => get_ru()), parse_obj_name($cust_o->name()));
			}

			$proj = $task->prop("project");
			$proj_str = "";
			if (is_oid($proj) && $this->can("view", $proj))
			{
				$proj_o = obj($proj);
				$proj_str = html::get_change_url($proj, array("return_url" => get_ru()), parse_obj_name($proj_o->name()));
			}

			$col = "";
			if ($task->class_id() == CL_CRM_MEETING || $task->class_id() == CL_CRM_CALL || $task->class_id() == CL_CRM_OFFER)
			{
				$dl = $task->prop("start1");
			}
			else
			{
				$dl = $task->prop("deadline");
			}
			if ($dl > 100 && time() > $dl)
			{
				$col = "#ff0000";
			}
			else
			if ($dl > 100 && date("d.m.Y") == date("d.m.Y", $dl)) // today
			{
				$col = "#f3f27e";
			}

			$ns = array();
			foreach(safe_array($task2person[$task->id()]) as $p_oid)
			{
				$ns[] = html::obj_change_url($p_oid);
			}

			$t_id = $task->id();
			$pm->begin_menu("task_".$t_id);
			$pm->add_item(array(
				"text" => t("Kustuta"), 
				"link" => $this->mk_my_orb("delete_tasks", array(
					"sel" => array($t_id => $t_id),
					"post_ru" => get_ru()
				), CL_CRM_COMPANY)
			));
			$pm->add_item(array(
				"text" => t("M&auml;rgi tehtuks"), 
				"link" => $this->mk_my_orb("mark_tasks_done", array(
					"sel" => array($t_id => $t_id),
					"post_ru" => get_ru()
				), CL_CRM_COMPANY)
			));
			$pm->add_item(array(
				"text" => t("Koosta arve"), 
				"link" => $this->mk_my_orb("create_bill_from_task", array(
					"id" => $t_id,
					"post_ru" => get_ru()
				), CL_TASK)
			));
			$ti = get_instance(CL_TASK);
			if (!$ti->stopper_is_running($task->id()))
			{
				$url = $this->mk_my_orb("stopper_pop", array(
					"id" => $t_id,
					"s_action" => "start",
					"type" => t("Toimetus"),
					"name" => urlencode($task->name())
				),CL_TASK);

				$pm->add_item(array(
					"text" => t("K&auml;ivita stopper"), 
					"link" => "#", 
					"oncl" => "onClick='aw_popup_scroll(\"$url\",\"aw_timers\",320,400)'"
				));
			}
			else
			{

				$url = $this->mk_my_orb("stopper_pop", array(
					"id" => $t_id,
					"s_action" => "stop",
					"type" => t("Toimetus"),
					"name" => urlencode($task->name())
				),CL_TASK);

				$elapsed = $ti->get_stopper_time($task->id());
				$hrs = (int)($elapsed / 3600);
				$mins = (int)(($elapsed - ($hrs * 3600)) / 60);
				$elapsed = sprintf("%02d:%02d", $hrs, $mins); 

				$pm->add_item(array(
					"text" => sprintf(t("Peata stopper (%s)"), $elapsed), 
					"link" => "#",
					"oncl" => "onClick='aw_popup_scroll(\"$url\",\"aw_timers\",320,400)'"
				));
			}
			
			// if this thing has recurrences attached, then stick those in there
			$recurs = array();
			foreach(safe_array($task2recur[$task->id()]) as $recur_id)
			{
				// get all times for this one from the recurrence table
				$this->db_query("SELECT recur_start, recur_end from recurrence where recur_id = ".$recur_id);
				while ($row = $this->db_next())
				{
					$table_data[] = array(
						"icon" => html::img(array("url" => icons::get_icon_url($task))),
						"customer" => $cust_str,
						"proj_name" => $proj_str,
						"name" => html::get_change_url($task->id(), array("return_url" => get_ru()), parse_obj_name($task->name())),
						"deadline" => $dl,
						"end" => $row["recur_end"],
						"start" => $row["recur_start"],
						"oid" => $task->id(),
						"priority" => $task->prop("priority"),
						"col" => $col,
						"parts" => join(", ", $ns),
						"menu" => $pm->get_menu(),
						"when" => date("d.m.Y H:i", $row["recur_start"])." - ".date("d.m.Y H:i",$row["recur_end"]),
						"is_recur" => 1
					);
				}
			}

			$table_data[] = array(
				"icon" => html::img(array("url" => icons::get_icon_url($task))),
				"customer" => $cust_str,
				"proj_name" => $proj_str,
				"name" => html::get_change_url($task->id(), array("return_url" => get_ru()), parse_obj_name($task->name())),
				"deadline" => $dl,
				"end" => $task->prop("end"),
				"oid" => $task->id(),
				"priority" => $task->prop("priority"),
				"col" => $col,
				"parts" => join(", ", $ns),
				"menu" => $pm->get_menu(),
				"when" => date("d.m.Y H:i", $task->prop("start1"))." - ".date("d.m.Y H:i",$task->prop("end"))
			);
		}

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_my_tasks_t($t, $table_data, $arr["request"]);

		foreach($table_data as $row)
		{
			if ($row["deadline"] > 100 || ($_GET["sortby"] != "" && $_GET["sortby"] != "deadline"))
			{
				$t->define_data($row);
			}
		}
		$t->set_default_sortby("deadline");
		$t->set_default_sorder("asc");

		$t->sort_by(array(
			"field" => $arr["request"]["sortby"],
			"sorder" => ($arr["request"]["sortby"] == "priority" ? "desc" : $arr["request"]["sort_order"])
		));

		$t->set_sortable(false);
		if (!($_GET["sortby"] != "" && $_GET["sortby"] != "deadline"))
		{
			foreach($table_data as $row)
			{
				if ($row["deadline"] < 100)
				{
					$t->define_data($row);
				}
			}
		}

		if ($arr["request"]["act_s_print_view"] == 1)
		{
			$sf = new aw_template;
			$sf->db_init();
			$sf->tpl_init("automatweb");
			$sf->read_template("index.tpl");
			$sf->vars(array(
				"content"	=> $t->draw(),
				"uid" => aw_global_get("uid"),
				"charset" => aw_global_get("charset")
			));
			die($sf->parse());
		}
	}

	function _get_tasks_search_filt($r, $tasks, $clid)
	{
		$res = array(
			"class_id" => $clid,
			"brother_of" => new obj_predicate_prop("id")
		);
		if (count($tasks))
		{
			$res["oid"] = $tasks;
		}

		$clss = aw_ini_get("classes");
		if (is_array($clid))
		{
			$def = "CL_TASK";
		}
		else
		{
			$def = $clss[$clid]["def"];
		}
	
		if ($r["act_s_cust"] != "")
		{
			$str_filt = $this->_get_string_filt($r["act_s_cust"]);
			if ($clid == CL_CRM_DOCUMENT_ACTION)
			{
				$res[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array(
						$def.".document(CL_CRM_DEAL).customer.name" => $str_filt,
						$def.".document(CL_CRM_MEMO).customer.name" => $str_filt	
					)
				));
			}
			else
			{
				$res[$def.".customer(CL_CRM_COMPANY).name"] = $str_filt;
			}
		}
		if ($r["act_s_part"] != "")
		{
			$str_filt = $this->_get_string_filt($r["act_s_part"]);
			if ($clid == CL_CRM_DOCUMENT_ACTION)
			{
				$res[$def.".actor.name"] = $str_filt;//map("%%%s%%", explode(",", $r["act_s_part"]));
			}
			else
			if ($clid == CL_CRM_OFFER)
			{
				$res["CL_CRM_OFFER.RELTYPE_SALESMAN.name"] = $str_filt; //map("%%%s%%", explode(",", $r["act_s_part"]));
			}
			else
			{
				// since someone stupidly decided that task participant relations are FROM person TO task, not the other way around (duh)
				// we need to select all tasks here and the pass the oids to the rest of the filter

				// get the person(s) typed
				$persons = new object_list(array(
					"class_id" => CL_CRM_PERSON,
					"name" => $str_filt, //map("%%%s%%", explode(",", $r["act_s_part"])),
					"lang_id" => array(),
					"site_id" => array()
				));
				if (!$persons->count())
				{
					$_res["oid"] = -1;
				}
				else
				{
					$c = new connection();
					$conns = $c->find(array(
						"from" => $persons->ids(),
						"from.class_id" => CL_CRM_PERSON,
						"to.class_id" => $clid,
						//"type" => "RELTYPE_PERSON_TASK"
					));
					$oids = array();
					foreach($conns as $con)
					{
						if (!isset($res["oid"]) || !isset($res["oid"][$con["to"]]))
						{
							$oids[] = $con["to"];
						}
					}

					if (count($oids))
					{
						$_res["oid"] = $oids;
					}
					else
					{
						$_res["oid"] = 1;
					}
				}

				// also search from connected resources
				$res[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array(
						"oid" => $_res["oid"],
						$def.".RELTYPE_RESOURCE.name" => $str_filt //map("%%%s%%", explode(",", $r["act_s_part"]))	
					)
				));
			}
		}

		if ($r["act_s_task_name"] != "")
		{
			$str_filt = $this->_get_string_filt($r["act_s_task_name"]);
			if ($clid == CL_CRM_DOCUMENT_ACTION)
			{
				$res[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array(
						"name" =>  $str_filt, //"%".$r["act_s_task_name"]."%",
						"CL_CRM_DOCUMENT_ACTION.document.name" => $str_filt // "%".$r["act_s_task_name"]."%",
					)	
				));
			}
			else
			{
				$res["name"] = $str_filt; //"%".$r["act_s_task_name"]."%";
			}
		}
		if ($r["act_s_task_content"] != "")
		{
			$str_filt = $this->_get_string_filt($r["act_s_task_content"]);
			$res[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"content" => $str_filt, //"%".$r["act_s_task_content"]."%",
					"summary" => $str_filt, //"%".$r["act_s_task_content"]."%",
					"CL_TASK.RELTYPE_ROW.content" => $str_filt //"%".$r["act_s_task_content"]."%"
				)
			));
		}
		if ($r["act_s_code"] != "")
		{
			$str_filt = $this->_get_string_filt($r["act_s_code"]);
			$res["code"] = $str_filt; //"%".$r["act_s_code"]."%";
		}
		if ($r["act_s_proj_name"] != "")
		{
			$str_filt = $this->_get_string_filt($r["act_s_proj_name"]);
			if ($clid == CL_CRM_DOCUMENT_ACTION)
			{
				$res[$def.".document.project.name"] = $str_filt; //"%".$r["act_s_proj_name"]."%";
			}
			else
			{
				$res[$def.".project(CL_PROJECT).name"] = $str_filt;  //"%".$r["act_s_proj_name"]."%";
			}
		}

		$r["act_s_dl_from"] = date_edit::get_timestamp($r["act_s_dl_from"]);
		$r["act_s_dl_to"] = date_edit::get_timestamp($r["act_s_dl_to"]);

		$dl = "deadline";
		if ($clid == CL_CRM_OFFER || $clid == CL_CRM_MEETING || $clid == CL_CRM_CALL )
		{
			$dl = "start1";
		}
		else
		if ($clid == CL_CRM_DOCUMENT_ACTION)
		{
			$dl = "date";
		}
		if ($r["act_s_dl_from"] > 1 && $r["act_s_dl_to"] > 1)
		{
			$res[$dl] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $r["act_s_dl_from"], $r["act_s_dl_to"]);
		}
		else
		if ($r["act_s_dl_from"] > 1)
		{
			$res[$dl] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $r["act_s_dl_from"]);
		}
		else
		if ($r["act_s_dl_to"] > 1)
		{
			$res[$dl] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $r["act_s_dl_to"]);
		}

		if ($r["act_s_status"] > 0 && $r["act_s_status"] < 3)
		{
			if ($clid == CL_CRM_DOCUMENT_ACTION)
			{
				$res["is_done"] = $r["act_s_status"] == 1 ? 0 : 1;
			}
			else
			{
				$res["flags"] = array("mask" => OBJ_IS_DONE, "flags" => $r["act_s_status"] == 1 ? 0 : OBJ_IS_DONE);
			}
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

		$clids = array(CL_TASK => 13, CL_CRM_MEETING => 11, CL_CRM_CALL => 12, CL_CRM_OFFER => 9);
		$clss = aw_ini_get("classes");

		$u = get_instance(CL_USER);
		$cur_co = $u->get_current_company();

		foreach($clids as $clid => $relt)
		{
			$url = $this->mk_my_orb('new',array(
				'alias_to_org' => $cur_co == $arr['obj_inst']->id() ? null : $arr['obj_inst']->id(),
				'reltype_org' => $relt,
				'add_to_cal' => $this->cal_id,
				'clid' => $clid,
				'title' => $clss[$clid]["name"],
				'parent' => $arr["obj_inst"]->id(),
				'return_url' => get_ru()
			), $clid);

			$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text' => $clss[$clid]["name"],
				'link' => $url
			));
		}

		/*$tb->add_menu_item(array(
			'parent' => 'add_item',
			"text" => t("P&auml;eva raport"),
			'link' => html::get_new_url(
				CL_CRM_DAY_REPORT, 
				$arr["obj_inst"]->id(), 
				array(
					"alias_to" => $arr["obj_inst"]->id(),
					"reltype" => 39,
					"return_url" => get_ru()
				)
			),
		));*/

		$tb->add_button(array(
			'name' => 'mark_as_done',
			'img' => 'save.gif',
			'tooltip' => t('M&auml;rgi tehtuks'),
			'action' => 'mark_tasks_done',
		));

		$tb->add_button(array(
			'name' => 'delete_tasks',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta toimetused'),
			"confirm" => t("Oled kindel et soovid valitud toimetusi kustutada?"),
			'action' => 'delete_tasks',
		));

		$tb->add_separator();

		if (aw_global_get("crm_task_view") == CRM_TASK_VIEW_TABLE)
		{
			$tb->add_button(array(
				'name' => 'tasks_switch_to_cal',
				'img' => 'icon_cal_today.gif',
				'tooltip' => t('Kalendrivaade'),
				'action' => 'tasks_switch_to_cal_view',
			));
		}
		else
		{
			$tb->add_button(array(
				'name' => 'tasks_switch_to_table',
				'img' => 'class_'.CL_TABLE.'.gif',
				'tooltip' => t('Tabelivaade'),
				'action' => 'tasks_switch_to_table_view',
			));
		}
	}

	function _get_act_s_part($arr)
	{
		if ($arr["request"]["act_s_sbt"] == "" && $arr["request"]["act_s_is_is"] != 1)
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			$v = $p->name();
		}
		else
		{
			$v = $arr["request"]["act_s_part"];
		}
		$tt = t("Kustuta");
		$arr["prop"]["value"] = html::textbox(array(
			"name" => "act_s_part",
			"value" => $v,
			"size" => 25
		))."<a href='javascript:void(0)' title=\"$tt\" alt=\"$tt\" onClick='document.changeform.act_s_part.value=\"\"'><img title=\"$tt\" alt=\"$tt\" src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0></a>";
		return PROP_OK;
	}

	function _get_my_tasks_cal($arr)
	{
		if (aw_global_get("crm_task_view") != CRM_TASK_VIEW_CAL)
		{
			return PROP_IGNORE;
		}
		$args = array();
		switch($arr["request"]["group"])
		{
			case "my_tasks":
			case "overview":
				$args["type"] = "RELTYPE_TASK";
				$clid = CL_TASK;
				break;

			case "meetings":
				$args["type"] = "RELTYPE_KOHTUMINE";
				$clid = CL_CRM_MEETING;
				break;

			case "calls":
				$args["type"] = "RELTYPE_CALL";
				$clid = CL_CRM_CALL;
				break;

			case "ovrv_offers":
				//$args["type"] = "RELTYPE_OFFER";
				$clid = CL_CRM_DOCUMENT_ACTION;
				break;

			default:
				$args["type"] = array("RELTYPE_TASK", "RELTYPE_KOHUTMINE", "RELTYPE_CALL", "RELTYPE_OFFER");
				$clid = array(CL_TASK, CL_CRM_MEETING, CL_CRM_CALL, CL_CRM_OFFER);
				break;
		}
		$this->do_org_actions($arr, $args, $clid);
	}

	function _get_task_list($arr)
	{
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();

		$i = get_instance(CL_CRM_COMPANY);
		$clid = NULL;
		switch($arr["request"]["group"])
		{
			case "my_tasks":
				$tasks = $i->get_my_tasks(!($arr["request"]["act_s_sbt"] != "" || $arr["request"]["act_s_is_is"] == 1));
				$clid = array(CL_TASK,CL_CRM_MEETING,CL_CRM_CALL,CL_CRM_OFFER);
				break;
			case "meetings":
				$tasksi = $i->get_my_meetings();
				$clid = CL_CRM_MEETING;
				$tasks = array();
				 foreach($tasksi as $t_id)
                                        {
                                                $o = obj($t_id);
                                                if (!($o->flags() & OBJ_IS_DONE))
                                                {
                                                        $tasks[$o->id()] = $o->id();
                                                }
                                        }
				break;

			case "calls":
				$tasksi = $i->get_my_calls();
				$clid = CL_CRM_CALL;
				$tasks = array();
				 foreach($tasksi as $t_id)
                                        {
                                                $o = obj($t_id);
                                                if (!($o->flags() & OBJ_IS_DONE))
                                                {
                                                        $tasks[$o->id()] = $o->id();
                                                }
                                        }
				break;

			case "ovrv_offers":
				/// this tab got turned into docmanagement. whoo
				$clid = CL_CRM_DOCUMENT_ACTION;
				// now, find all thingies that I am part of
				$ol = new object_list(array(
					"class_id" => CL_CRM_DOCUMENT_ACTION,
					"site_id" => array(),
					"lang_id" => array(),
					"actor" => $u->get_current_person(),
				));
				$tasks = $this->make_keys($ol->ids());
				break;

			default:
				$clid = array(CL_TASK,CL_CRM_MEETING,CL_CRM_CALL,CL_CRM_OFFER);

				if ($co == $arr["obj_inst"]->id())
				{
					$tasks = array();
					$tg = $i->get_my_actions();
					if (!count($tg))
					{
						$tasks = array();
					}
					else
					{
						$ol = new object_list(array(
							"oid" => $tg,
							"site_id" => array(),
							"lang_id" => array(),
							"flags" => array("mask" => OBJ_IS_DONE, "flags" => 0)
						));
						$tasks = $this->make_keys($ol->ids());
					}
				}
				else
				{
					$ol = new object_list($arr["obj_inst"]->connections_from(array(
						"type" => array("RELTYPE_KOHTUMINE", "RELTYPE_CALL", "RELTYPE_TASK", "RELTYPE_DEAL", "RELTYPE_OFFER")
					)));

					$ol2 = new object_list(array(
						"class_id" => $clid,
						"customer" => $arr["obj_inst"]->id()
					));
					$ol->add($ol2);
					$tasks = $this->make_keys($ol->ids());
				}
				break;
		}
		if ($arr["request"]["act_s_sbt"] != "" || $arr["request"]["act_s_is_is"] == 1)
		{
			// filter
			$param = $tasks;
			if ($co == $arr["obj_inst"]->id())
			{
				$param = array();
			}
			$p = $this->_get_tasks_search_filt($arr["request"], $param, $clid);
			$p["lang_id"] = array();
			$p["site_id"] = array();
			$p["brother_of"] = new obj_predicate_prop("id");
			$ol = new object_list($p);
			return $ol;
		}
		else
		{
			if (!count($tasks))
			{
				$ol = new object_list();
				return $ol;
			}
			else
			{
				$ol = new object_list(array(
					"oid" => $tasks,
					"lang_id" => array(),
					"site_id" => array(),
					"brother_of" => new obj_predicate_prop("id")
				));
				return $ol;
			}
		}

		if ($ol->count())
		{
			$ol = new object_list(array(
				"oid" => $ol->ids(),
				"brother_of" => new obj_predicate_prop("id"),
				"lang_id" => array(),
				"site_id" => array()
			));
		}
		return $ol;
	}

	function _format_deadline($arg)
	{
		$o = obj($arg["oid"]);
		if ($arg["is_recur"] == 1)
		{
			if ($arg["start"] == $arg["end"])
			{
				return date("d.m.Y H:i", $arg["start"]);
			}
			return date("d.m.Y H:i", $arg["start"])." - ".date("d.m.Y H:i", $arg["end"]);
		}
		if ($o->class_id() == CL_TASK)
		{
			if ($arg["deadline"] > 1000)
			{
				$arg["deadline"] = date("d.m.Y H:i", $arg["deadline"]);
			}
			else
			{
				return "";
			}
		}
		else
		if ($arg["end"] > 1000 && $arg["end"] > $arg["deadline"] && $arg["end"] != $arg["deadline"])
		{
			$d1 = date("d.m.Y", $arg["deadline"]);
			$d2 = date("d.m.Y", $arg["end"]);
			if ($d1 == $d2)
			{
				$arg["deadline"] = $d1."<br>".date("H:i", $arg["deadline"])." - ".date("H:i", $arg["end"]);
			}
			else
			{
				$arg["deadline"] = date("d.m.Y H:i", $arg["deadline"])." - ".date("d.m.Y H:i", $arg["end"]);
			}
		}
		else
		if ($arg["deadline"] > 1000)
		{
			$arg["deadline"] = date("d.m.Y H:i", $arg["deadline"]);
		}
		else
		{
			return "";
		}

		return $arg["deadline"];
	}

	function _get_ovrv_offers($arr, $ol)
	{
		$pm = get_instance("vcl/popup_menu");
		$table_data = array();
		foreach($ol->ids() as $act_id)
		{
			$act = obj($act_id);
			$task_c = reset($act->connections_to());
			$task = $task_c->from();

			// if this has a predicate thingie, then check if that is done before showing it here
			$preds = safe_array($act->prop("predicate"));
			foreach($preds as $pred)
			{
				if ($this->can("view", $pred))
				{
					$pred = obj($pred);
					if ($pred->prop("is_done") != 1)
					{
						continue;
					}
				}
			}

			if ($task->class_id() == CL_CRM_OFFER)
			{
				$cust = $task->prop("orderer");
			}
			else
			{
				$cust = $task->prop("customer");
			}
			$cust_str = "";
			if (is_oid($cust) && $this->can("view", $cust))
			{
				$cust_o = obj($cust);
				$cust_str = html::get_change_url($cust, array("return_url" => get_ru()), parse_obj_name($cust_o->name()));
			}

			$proj = $task->prop("project");
			$proj_str = "";
			if (is_oid($proj) && $this->can("view", $proj))
			{
				$proj_o = obj($proj);
				$proj_str = html::get_change_url($proj, array("return_url" => get_ru()), parse_obj_name($proj_o->name()));
			}

			$col = "";
			$dl = $act->prop("date");
			if ($dl > 100 && time() > $dl)
			{
				$col = "#ff0000";
			}
			else
			if ($dl > 100 && date("d.m.Y") == date("d.m.Y", $dl)) // today
			{
				$col = "#f3f27e";
			}

			$ns = html::obj_change_url($act->prop("actor"));
			if ($ns != "")
			{
				$nso = obj($act->prop("actor"));
				$work = html::obj_change_url($nso->prop("work_contact"));
				$ns .= ($work != "" ? ", ".$work : "");
			}

			$t_id = $task->id();
			
			$table_data[] = array(
				"icon" => html::img(array("url" => icons::get_icon_url($task))),
				"customer" => $cust_str,
				"proj_name" => $proj_str,
				"name" => html::get_change_url($task->id(), array("return_url" => get_ru()), parse_obj_name($task->name()))." / ".html::get_change_url($act->id(), array("return_url" => get_ru()), parse_obj_name($act->name())),
				"deadline" => $dl,
				"oid" => $act->id(),
				"col" => $col,
				"parts" => $ns,
			);
		}

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_my_tasks_t($t, $table_data, $arr["request"]);

		foreach($table_data as $row)
		{
			if ($row["deadline"] > 100 || ($_GET["sortby"] != "" && $_GET["sortby"] != "deadline"))
			{
				$t->define_data($row);
			}
		}
		$t->set_default_sortby("deadline");
		$t->set_default_sorder("asc");

		$t->sort_by(array(
			"field" => $arr["request"]["sortby"],
			"sorder" => ($arr["request"]["sortby"] == "priority" ? "desc" : $arr["request"]["sort_order"])
		));

		$t->set_sortable(false);
		if (!($_GET["sortby"] != "" && $_GET["sortby"] != "deadline"))
		{
			foreach($table_data as $row)
			{
				if ($row["deadline"] < 100)
				{
					$t->define_data($row);
				}
			}
		}

		if ($arr["request"]["act_s_print_view"] == 1)
		{
			$sf = new aw_template;
			$sf->db_init();
			$sf->tpl_init("automatweb");
			$sf->read_template("index.tpl");
			$sf->vars(array(
				"content"	=> $t->draw(),
				"uid" => aw_global_get("uid"),
				"charset" => aw_global_get("charset")
			));
			die($sf->parse());
		}
	}

	function _get_string_filt($s)
	{
		$this->dequote(&$s);
		// separated by commas delimited by "
		$p = array();
		$len = strlen($s);
		for ($i = 0; $i < $len; $i++)
		{
			if ($s[$i] == "\"" && $in_q)
			{
				// end of quoted string
				$p[] = $cur_str;
				$in_q = false;
			}
			else
			if ($s[$i] == "\"" && !$in_q)
			{
				$cur_str = "";
				$in_q = true;
			}
			else
			if ($s[$i] == "," && !$in_q)
			{
				$p[] = $cur_str;
				$cur_str = "";
			}
			else
			{
				$cur_str .= $s[$i];
			}
		}
		$p[] = $cur_str;
		$p = array_unique($p);

		return map("%%%s%%", $p);
	}

	function _get_participant_list_for_tasks($tasks)
	{
		if (count($tasks) == 0)
		{
			return array();
		}
		$c = new connection();
		$conns = $c->find(array(
			"from.class_id" => CL_CRM_PERSON,
			"to" => $tasks
		));
		$ret = array();
		$plist = array();
		foreach($conns as $conn)
		{
			$ret[$conn["to"]][$conn["from"]] = $conn["from"];
			$plist[$conn["from"]] = $conn["from"];
		}
		// warm cache
		$ol = new object_list(array("oid" => $plist, "site_id" => array(), "lang_id" => array()));
		$ol->arr();
		return $ret;
	}

	function _get_recur_list_for_tasks($tasks)
	{
		$c = new connection();
		$conns = $c->find(array(
			"from" => $tasks,
			"to.class_id" => CL_RECURRENCE
		));
		$ret = array();
		foreach($conns as $conn)
		{
			$ret[$conn["from"]][$conn["to"]] = $conn["to"];
		}
		return $ret;
	}
}
?>
