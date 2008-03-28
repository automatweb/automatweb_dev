<?php
/*
@classinfo  maintainer=robert
*/

class bt_stat_impl extends core
{
	function bt_stat_impl()
	{
		$this->init();
	}

	function _init_stat_hrs_ov_t(&$t)
	{
		$t->define_field(array(
			"name" => "p",
			"caption" => t("Isik"),
			"align" => "center",
			"sortable" => 1
		));
		for($i = 1; $i <= 12; $i++)
		{
			$t->define_field(array(
				"name" => "m".sprintf("%02d", $i),
				"caption" => locale::get_lc_month($i),
				"align" => "center",
				"sortable" => 1
			));
		}
		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center",
			"sortable" => 1,
			"type" => "int",
			"numeric" => 1
		));
	}

	function _get_stat_hrs_overview($arr)
	{
		// table year is group, month is col
		// row is person
		$req_start = empty($arr["request"]["stat_hrs_start"]) ? mktime(0, 0, 0, date("n"), 1, date("Y"), 1) : mktime(0, 0, 0, $arr["request"]["stat_hrs_start"]["month"], $arr["request"]["stat_hrs_start"]["day"], $arr["request"]["stat_hrs_start"]["year"], 1);
		$req_end = empty($arr["request"]["stat_hrs_end"]) ? time() + 86400 : mktime(23, 59, 59, $arr["request"]["stat_hrs_end"]["month"], $arr["request"]["stat_hrs_end"]["day"], $arr["request"]["stat_hrs_end"]["year"], 1);
		$time_constraint = null;

		if (2 < $req_start and $req_start < $req_end)
		{
			$time_constraint = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $req_start, $req_end);
		}
		$stat_hrs = array();
		if($arr["request"]["stat_hr_bugs"] || !$arr["request"]["stat_hrs_end"])
		{
			$ol = new object_list(array(
				"class_id" => CL_BUG_COMMENT,
				"lang_id" => array(),
				"site_id" => array(),
				"add_wh" => new obj_predicate_not(0),
				"created" => $time_constraint
			));
			
	
			foreach($ol->arr() as $o)
			{
				$stat_hrs[$o->createdby()][] = $o;
			}
		}
		$types = $this->get_event_types();
		foreach($types as $type)
		{
			if($arr["request"]["stat_hr_".$type["rname"]] || !$arr["request"]["stat_hrs_end"])
			{
				$ol = new object_list(array(
					"class_id" => $type["class_id"],
					"lang_id" => array(),
					"site_id" => array(),
					"is_work" => 1,
					"start1" => $time_constraint,
					"brother_of" => new obj_predicate_prop("id")
				));
				foreach($ol->arr() as $o)
				{
					if(!$o->prop($type["timevar"]))
					{
						continue;
					}
					$tp = $type["types"];
					foreach($o->connections_to(array("type" => $tp)) as $co)
					{
						$pi = get_instance(CL_CRM_PERSON);
						$po = obj($co->conn["from"]);
						$u = $pi->has_user($po);
						if($u !== false)
						{
							$uname = $u->name();
							$stat_hrs[$uname][] = $o;
						}
					}
				}
			}
		}

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_stat_hrs_ov_t($t);
		foreach($stat_hrs as $uid => $coms)
		{
			$u = get_instance(CL_USER);
			$p = $u->get_person_for_uid($uid);
			$dmz = array();

			foreach($coms as $com)
			{
				if($com->class_id() == CL_BUG_COMMENT)
				{
					$dmz[date("Y", $com->created())]["m".date("m", $com->created())] += $com->prop("add_wh");
				}
				elseif($com->class_id() == CL_TASK)
				{
					$dmz[date("Y", $com->prop("start1"))]["m".date("m",$com->prop("start1"))] += $com->prop("num_hrs_real");
				}
				elseif($com->class_id() == CL_CRM_MEETING || $com->class_id() == CL_CRM_CALL)
				{
					$dmz[date("Y", $com->prop("start1"))]["m".date("m",$com->prop("start1"))] += $com->prop("time_real");
				}
			}

			foreach($dmz as $year => $mons)
			{
				$row_sum = 0;

				foreach($mons as $mon => $wh)
				{
					$mon_num = substr($mon, 1);
					if($mon_num == $arr["request"]["stat_hrs_start"]["month"])
					{
						$det_day_start = $arr["request"]["stat_hrs_start"]["day"];
					}
					if($mon_num == $arr["request"]["stat_hrs_end"]["month"])
					{
						$det_day_end = $arr["request"]["stat_hrs_end"]["day"];
					}
					$mons[$mon] = html::href(array(
						"url" => aw_url_change_var(array(
							"det_uid" => $uid,
							"det_year" => $year,
							"det_mon" => (int)substr($mon, 1),
							"det_day_start" => $det_day_start,
							"det_day_end" => $det_day_end,
						)),
						"caption" => number_format($wh, 2, ".", " ")
					));
					$row_sum += $wh;
				}

				$mons["p"] = html::obj_change_url($p);
				$mons["year"] = $year;

				if ($wh>0)
				{
					$mons["sum"] = number_format($row_sum, 2, ".", " ");
					$t->define_data($mons);
				}
			}
		}

		$t->set_rgroupby(array("year" => "year"));
		$t->set_caption(t("T&ouml;&ouml;tundide statistika aastate ja kuude kaupa"));
		$t->set_default_sortby("sum");
		$t->set_default_sorder("desc");
	}

	function _init_stat_det_t(&$t)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "bug",
			"caption" => t("Bugi"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Kuup&auml;ev"),
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y / H:i",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "wh",
			"caption" => t("Aeg"),
			"align" => "center",
			"sortable" => 1
		));
		$t->sort_by();
		$t->set_default_sortby("time");
	}

	function _get_stat_hrs_detail($arr)
	{
		if (!$arr["request"]["det_uid"] || !$arr["request"]["det_year"] || !$arr["request"]["det_mon"])
		{
			return PROP_IGNORE;
		}

		// list all bugs and their times for that person for that time
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_stat_det_t($t);
		if($dds = $arr["request"]["det_day_start"])
		{
			$startday = $dds;
		}
		else
		{
			$startday = 1;
		}
		if($eds = $arr["request"]["det_day_end"])
		{
			$endday = $eds;
			$endmonth = $arr["request"]["det_mon"];
		}
		else
		{
			$endday = 0;
			$endmonth = $arr["request"]["det_mon"]+1;
		}
		$fancy_filter = new obj_predicate_compare(
			OBJ_COMP_BETWEEN_INCLUDING,
			mktime(0,0,0, $arr["request"]["det_mon"], $startday, $arr["request"]["det_year"]),
			mktime(0,0,0, $endmonth, $endday, $arr["request"]["det_year"])
		);

		if($arr["request"]["stat_hr_bugs"] || !$arr["request"]["stat_hrs_end"])
		{
			$ol = new object_list(array(
				"class_id" => CL_BUG_COMMENT,
				"lang_id" => array(),
				"site_id" => array(),
				"created" => $fancy_filter,
				"createdby" => $arr["request"]["det_uid"]
			));
	
			$bugs = array();
			foreach($ol->arr() as $com)
			{
				$bugs[$com->parent()]["hrs"] += $com->prop("add_wh");
				if(!$bugs[$com->parent()]["lastdate"] || $bugs[$com->parent()]["lastdate"] > $com->created())
				$bugs[$com->parent()]["lastdate"] = $com->created();
			}
		}
		$types = $this->get_event_types();
		
		$ui = get_instance(CL_USER);
		$p = $ui->get_person_for_uid($arr["request"]["det_uid"]);
		$startd = mktime(0,0,0, $arr["request"]["det_mon"], 1, $arr["request"]["det_year"]);
		$endd = mktime(0,0,0, $arr["request"]["det_mon"]+1, 0, $arr["request"]["det_year"]);
		foreach($types as $type)
		{
			if($arr["request"]["stat_hr_".$type["rname"]] || !$arr["request"]["stat_hrs_end"])
			{
				$c = new connection();
				$list = $c->find(array(
					"to.class_id" => $type["class_id"],
					"from.class_id" => CL_CRM_PERSON,
					"type" => $type["types"],
					"from" => $p->id()
				));
				foreach($list as $item)
				{
					$o = obj($item["to"]);
					if($o->prop("is_work") && $o->prop("start1") > $startd && $o->prop("start1")<$endd)
					{
						$bugs[$item["to"]]["hrs"] += $o->prop($type["timevar"]);
						$bugs[$item["to"]]["lastdate"] = $o->modified();
					}
				}
			}
		}

		foreach($bugs as $bug => $data)
		{
			$o = obj($bug);
			classload("core/icons");
			if ($data["hrs"] > 0)
			{
				$t->define_data(array(
					"icon" => html::img(array("url" =>icons::get_icon_url($o->class_id()))),
					"time" => $data["lastdate"],
					"bug" => html::obj_change_url($bug),
					"wh" => $data["hrs"]
				));
			}
		}

		$u = get_instance(CL_USER);
		$p = $u->get_person_for_uid($arr["request"]["det_uid"]);
		if($dds = $arr["request"]["det_day_start"])
		{
			$startday = $dds;
		}
		else
		{
			$startday = 1;
		}
		if($eds = $arr["request"]["det_day_end"])
		{
			$endday = $eds;
			$endmonth = $arr["request"]["det_mon"];
		}
		else
		{
			$endday = 0;
			$endmonth = $arr["request"]["det_mon"]+1;
		}
		$t->set_caption(sprintf(t("%s t&ouml;&ouml;tunnid ajavahemikul %s - %s"),
			$p->name(),
			date("d.m.Y", mktime(0,0,0, $arr["request"]["det_mon"], $startday, $arr["request"]["det_year"])),
			date("d.m.Y", mktime(0,0,0, $endmonth, $endday, $arr["request"]["det_year"]))
		));
	}

	function get_event_types()
	{
		$types = array(
			0 => array(
				"rname" => "tasks",
				"class_id" => CL_TASK,
				"timevar" => "num_hrs_real",
				"types" => array(10,8)
			),
			1 => array(
				"rname" => "calls",
				"class_id" => CL_CRM_CALL,
				"timevar" => "time_real",
				"types" => 9
			),
			2 => array(
				"rname" => "meetings",
				"class_id" => CL_CRM_MEETING,
				"timevar" => "time_real",
				"types" => 8
			),
		);
		return $types;
	}

	function _init_errs_t(&$t)
	{
		$t->define_field(array(
			"name" => "bug",
			"caption" => t("Bug"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "com",
			"caption" => t("Kommentaar"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "wh",
			"caption" => t("T&ouml;&ouml;tunde"),
			"align" => "center",
		));
	}

	function _get_stat_hrs_errs($arr)
	{
		if (!$arr["request"]["dbg"])
		{
			return PROP_IGNORE;
		}
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_errs_t($t);

		$ol = new object_list(array(
			"class_id" => CL_BUG_COMMENT,
			"lang_id" => array(),
			"site_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					new object_list_filter(array(
						"logic" => "OR",
						"conditions" => array(
							"add_wh" => new obj_predicate_compare(OBJ_COMP_LESS, 0)
						)
					)),
					new object_list_filter(array(
						"logic" => "OR",
						"conditions" => array(
							"add_wh" => new obj_predicate_compare(OBJ_COMP_GREATER, 10)
						)
					))
				)
			))
		));
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"bug" => html::obj_change_url($o->parent()),
				"com" => html::obj_change_url($o),
				"wh" => $o->prop("add_wh")
			));
		}
		if (!$ol->count())
		{
			return PROP_IGNORE;
		}
	}

	function _init_stat_proj_det(&$t)
	{
		$t->define_field(array(
			"name" => "p",
			"caption" => t("Isik"),
			"align" => "center",
			"sortable" => 1
		));
		for($i = 1; $i < 13; $i++)
		{
			$t->define_field(array(
				"name" => "m".sprintf("%02d", $i),
				"caption" => locale::get_lc_month($i),
				"align" => "center",
				"sortable" => 1
			));
		}
		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center",
			"sortable" => 1,
			"type" => "int"
		));
	}

	function _get_stat_proj_detail($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_stat_proj_det($t);

		// table year is group, month is col
		// row is person
		$ol = new object_list(array(
			"class_id" => CL_BUG_COMMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"created" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, mktime(0,0,0, 1, 1, date("Y")))
		));
		$stat_hrs = array();
		$bugids = array();
		$sum_by_proj = array();
		foreach($ol->arr() as $o)
		{
			$tm = $o->created();
			$stat_hrs[$o->createdby()][] = $o;
			$bugids[$o->parent()] = 1;
		}

		$bug_ol = new object_list(array(
			"oid" => array_keys($bugids),
			"lang_id" => array(),
			"site_id" => array()
		));
		$bug_ol->begin();

		foreach($ol->arr() as $com)
		{
			$bug = obj($com->parent());
			$sum_by_proj[$bug->prop("project")] += $com->prop("add_wh");
		}

		$tot_sum = 0;
		foreach($stat_hrs as $uid => $coms)
		{
			$u = get_instance(CL_USER);
			$p = $u->get_person_for_uid($uid);

			$dmz = array();
			foreach($coms as $com)
			{
				$bug = obj($com->parent());
				$dmz[$bug->prop("project")]["m".date("m", $com->created())] += $com->prop("add_wh");
			}

			foreach($dmz as $proj => $mons)
			{
				if (!$this->can("view", $proj))
				{
					continue;
				}
				$row_sum = 0;
				foreach($mons as $mon => $wh)
				{
					$mons[$mon] = html::href(array(
						"url" => aw_url_change_var(array(
							"det_uid" => $uid,
							"det_proj" => $proj,
							"det_mon" => (int)substr($mon, 1)
						)),
						"caption" => number_format($wh, 2, ".", " ")
					));
					$row_sum += $wh;
				}
				$mons["p"] = html::obj_change_url($p);
				$mons["year"] = html::obj_change_url($proj)." - ".$sum_by_proj[$proj];
				if ($wh>0)
				{
					$mons["sum"] = number_format($row_sum, 2, ".", " ");
					$tot_sum += $row_sum;
					$t->define_data($mons);
				}
			}
		}

		$t->set_rgroupby(array("year" => "year"));
		$t->set_caption(t("T&ouml;&ouml;tundide statistika projektide ja kuude kaupa"));
		$t->set_default_sortby("sum");
		$t->set_default_sorder("desc");
		$t->sort_by();
		$t->set_sortable(false);
		$t->define_data(array("p" => t("<b>Summa</b>"),"sum" => number_format($tot_sum, 2, ".", " ")));
	}

	function _get_stat_proj_detail_b($arr)
	{
		if (!$arr["request"]["det_uid"] || !$arr["request"]["det_proj"] || !$arr["request"]["det_mon"])
		{
			return PROP_IGNORE;
		}

		// list all bugs and their times for that person for that time
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_stat_det_t($t);

		$ol = new object_list(array(
			"class_id" => CL_BUG_COMMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"created" => new obj_predicate_compare(
				OBJ_COMP_BETWEEN_INCLUDING,
				mktime(0,0,0, $arr["request"]["det_mon"], 1, date("Y")),
				mktime(0,0,0, $arr["request"]["det_mon"]+1, 0, date("Y"))
			),
			"createdby" => $arr["request"]["det_uid"]
		));

		$bugs = array();
		foreach($ol->arr() as $com)
		{
			$bug = obj($com->parent());
			if ($bug->prop("project") == $arr["request"]["det_proj"])
			{
				$bugs[$com->parent()] += $com->prop("add_wh");
			}
		}

		foreach($bugs as $bug => $wh)
		{
			if ($wh > 0)
			{
				$t->define_data(array(
					"bug" => html::obj_change_url($bug),
					"wh" => $wh
				));
			}
		}

		$u = get_instance(CL_USER);
		$p = $u->get_person_for_uid($arr["request"]["det_uid"]);
		$proj = obj($arr["request"]["det_proj"]);

		$t->set_caption(sprintf(t("%s t&ouml;&ouml;tunnid projektis %s ajavahemikul %s - %s"),
			$p->name(),
			$proj->name(),
			date("d.m.Y", mktime(0,0,0, $arr["request"]["det_mon"], 1, date("Y"))),
			date("d.m.Y", mktime(0,0,0, $arr["request"]["det_mon"]+1, 0, date("Y")))
		));
	}
}
