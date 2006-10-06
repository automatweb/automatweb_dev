<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/project_analysis_ws.aw,v 1.3 2006/10/06 15:14:48 markop Exp $
// project_analysis_ws.aw - Projekti anal&uuml;&uuml;si t&ouml;&ouml;laud 
/*

@classinfo syslog_type=ST_PROJECT_ANALYSIS_WS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general


@tableinfo aw_project_analysis_ws index=aw_oid master_index=brother_of master_table=objects

	@property eval_dl type=date_select table=aw_project_analysis_ws field=aw_eval_dl
	@caption Hindamise t&auml;htaeg

@default group=eval

	@property strat_a type=table no_caption=1 store=no

@default group=strat_res_avg

	@property strat_res type=table no_caption=1 store=no

@default group=strat_res_tree

	@layout srt_hb type=hbox 

		@property srt type=treeview parent=srt_hb store=no no_caption=1
		@property srt_tbl type=table parent=srt_hb store=no no_caption=1

@default group=strat_res_wt

	@property strat_wt type=table no_caption=1 store=no

@default group=cols

	@property cols_tb type=toolbar no_caption=1 no_comment=1
	@property cols_table type=table no_caption=1 no_comment=1

@default group=rows

	@property rows_tb type=toolbar no_caption=1 no_comment=1
	@property rows_table type=table no_caption=1 no_comment=1

@groupinfo strat_res_wt caption="Hindajad" 
@groupinfo cols caption="Tulbad" submit=no
@groupinfo rows caption="Read" submit=no


@groupinfo eval caption="Hindamine"
@groupinfo strat_res caption="Hindamise tulemused"
	@groupinfo strat_res_tree caption="Hindajad" parent=strat_res submit=no
	@groupinfo strat_res_avg caption="Keskmised hinded" parent=strat_res submit=no


@reltype COL value=1 clid=CL_PROJECT_ANALYSIS_COL
@caption Tulp

@reltype ROW value=2 clid=CL_PROJECT_ANALYSIS_ROW
@caption Rida

*/

class project_analysis_ws extends class_base
{
	function project_analysis_ws()
	{
		$this->init(array(
			"tpldir" => "applications/groupware/project_analysis_ws",
			"clid" => CL_PROJECT_ANALYSIS_WS
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "strat_wt":
				$this->_strat_wt($arr);
				break;

			case "cols_tb":
				$this->_cols_tb($arr);
				break;

			case "cols_table":
				$this->_cols_table($arr);
				break;

			case "rows_tb":
				$this->_rows_tb($arr);
				break;

			case "rows_table":
				$this->_rows_table($arr);
				break;

			case "strat_a":
				$this->_strat_a($arr);
				break;

			case "strat_a":
				$this->_save_strat_a($arr);
				break;

			case "srt":
				$this->_srt($arr);
				break;

			case "srt_tbl":
				$this->_srt_tbl($arr);
				break;

			case "strat_res":
				$this->_strat_res($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "strat_wt":
				$this->_save_strat_wt($arr);
				break;

			case "strat_a":
				$this->_save_strat_a($arr);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["project"] = $_GET["project"];
	}


	function callback_post_save($arr)
	{
		if($arr["new"] == 1 && is_oid($arr["request"]["project"]) && $this->can("view" , $arr["request"]["project"]))
		{
			$project = obj($arr["request"]["project"]);
			$project->connect(array("to" => $arr["id"], "reltype" => "ANALYSIS_WS"));
		}
	}

	function _init_strat_wt_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Meeskonna liige"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "is",
			"caption" => t("Hindaja?"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "wt",
			"caption" => t("Hindaja hinde kaal protsentides"),
			"align" => "center",
		));
	}

	function _strat_wt($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_strat_wt_t($t);

		$pi = get_instance(CL_PROJECT);
		$conns = $arr["obj_inst"]->connections_to(array("from.class_id" => CL_PROJECT));
		$c = reset($conns);
		if(!$c) return;
		$proj  = $c->from();

		// get project team
		$team = new object_list($proj->connections_from(array("type" => "RELTYPE_PARTICIPANT")));

		$wts = $arr["obj_inst"]->meta("wts");
		$evals = $arr["obj_inst"]->meta("evals");
		foreach($team->arr() as $o)
		{
			if ($o->class_id() != CL_CRM_PERSON)
			{
				continue;
			}
			$t->define_data(array(
				"name" => html::obj_change_url($o->id()),
				"wt" => html::textbox(array(
					"name" => "wts[".$o->id()."]",
					"size" => 5,
					"value" => $wts[$o->id()]
				)),
				"is" => html::checkbox(array(
					"name" => "is[".$o->id()."]",
					"value" => 1,
					"checked" => $evals[$o->id()]
				))
			));
		}
	}

	function _save_strat_wt($arr)
	{
		$arr["obj_inst"]->set_meta("wts", $arr["request"]["wts"]);
		$arr["obj_inst"]->set_meta("evals", $arr["request"]["is"]);
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_project_analysis_ws (aw_oid int primary key, aw_eval_dl int)");
			return true;
		}
	}

	function _cols_tb($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Tulp"),
			"url" => html::get_new_url(CL_PROJECT_ANALYSIS_COL, $arr["obj_inst"]->id(), array("return_url" => get_ru(), "alias_to" => $arr["obj_inst"]->id(), "reltype" => 1))
		));
		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "del_goals",
			"tooltip" => t("Kustuta"),
		));
	}

	function _init_cols_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Tulba nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "grp_name",
			"caption" => t("Grupi nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "priority",
			"caption" => t("Prioriteet"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "weight",
			"caption" => t("Kaal"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i",
			"numeric" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _cols_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_cols_table($t);

		$u = get_instance(CL_USER);
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_COL")) as $c)
		{
			$st = $c->to();
			$p = $u->get_person_for_uid($st->createdby());
			$t->define_data(array(
				"name" => html::obj_change_url($c->to()),
				"createdby" => $p->name(),
				"created" => $st->created(),
				"ord" => $st->ord(),
				"oid" => $c->prop("to"),
				"grp_name" => $st->prop("group_name"),
				"priority" => $st->prop("priority"),
				"weight" => $st->prop("weight"),
				"ord" => $st->prop("ord")
			));
		}
	}

	/**

		@attrib name=del_goals

	**/
	function del_goals($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array("oid" => $arr["sel"]));
			$ol->delete();
		}

		return $arr["post_ru"];
	}

	function _rows_tb($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Tulp"),
			"url" => html::get_new_url(CL_PROJECT_ANALYSIS_ROW, $arr["obj_inst"]->id(), array("return_url" => get_ru(), "alias_to" => $arr["obj_inst"]->id(), "reltype" => 2))
		));
		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "del_goals",
			"tooltip" => t("Kustuta"),
		));
	}

	function _init_rows_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Rea nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "grp_name",
			"caption" => t("Rea kirjeldus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i",
			"numeric" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _rows_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_rows_table($t);

		$u = get_instance(CL_USER);
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ROW")) as $c)
		{
			$st = $c->to();
			$p = $u->get_person_for_uid($st->createdby());
			$t->define_data(array(
				"name" => html::obj_change_url($c->to()),
				"createdby" => $p->name(),
				"created" => $st->created(),
				"ord" => $st->ord(),
				"oid" => $c->prop("to"),
				"grp_name" => $st->comment()
			));
		}
	}

	function _init_strat_a_tbl(&$t, $o)
	{
		$t->define_field(array(
			"name" => "task",
			"caption" => t(""),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "task_comm",
			"caption" => t(""),
			"align" => "center"
		));
		$grps = array();
		foreach($o->connections_from(array("type" => "RELTYPE_COL", "sort_by" => "to.jrk")) as $c)
		{
			$r = $c->to();
			$grp[$r->prop("group_name")] = $r->prop("group_name");
		}

		foreach($grp as $gn)
		{
			$t->define_field(array(
				"name" => $gn,
				"caption" => $gn,
				"align" => "center",
				"sortable" => 1,
				"numeric" => 1,
			));
		}		

		foreach($o->connections_from(array("type" => "RELTYPE_COL", "sort_by" => "to.jrk")) as $c)
		{
			$r = $c->to();
			$t->define_field(array(
				"name" => $c->prop("to"),
				"caption" => $c->prop("to.name"),
				"align" => "center",
				"sortable" => 1,
				"numeric" => 1,
				"parent" => $r->prop("group_name")
			));
		}
	}

	function _init_strat_a_tbl_r(&$t, $o)
	{
		$t->define_field(array(
			"name" => "task",
			"caption" => t(""),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "task_comm",
			"caption" => t(""),
			"align" => "center"
		));
		$grps = array();
		foreach($o->connections_from(array("type" => "RELTYPE_COL", "sort_by" => "to.jrk")) as $c)
		{
			$r = $c->to();
			$grp[$r->prop("group_name")] = $r->prop("group_name");
		}

		foreach($grp as $gn)
		{
			$t->define_field(array(
				"name" => $gn,
				"caption" => $gn,
				"align" => "center",
				"sortable" => 1,
				"numeric" => 1,
			));
		}		

		foreach($o->connections_from(array("type" => "RELTYPE_COL", "sort_by" => "to.jrk")) as $c)
		{
			$r = $c->to();
			$t->define_field(array(
				"name" => $c->prop("to"),
				"caption" => $c->prop("to.name"),
				"align" => "center",
				"sortable" => 1,
				"numeric" => 1,
				"parent" => $r->prop("group_name")
			));
		}

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1
		));
	}

	function _strat_a($arr)
	{
		$evs = $arr["obj_inst"]->meta("evals");
		$cp = get_current_person();
		if (!isset($evs[$cp->id()]))
		{
			$arr["prop"]["type"] = "text";
			$arr["prop"]["value"] = t("Teie ei ole m&auml;&auml;ratud hindajaks!");
			return;
		}
		$t =& $arr["prop"]["vcl_inst"];

		$se = $this->_get_strat_eval($arr["obj_inst"]);
		$data = $se->meta("grid");
		$this->_init_strat_a_tbl($t, $arr["obj_inst"]);

		$strats = array();
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_COL", "sort_by" => "to.jrk")) as $c)
		{
			$strats[$c->prop("to")] = $c->prop("to");
		}

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ROW", "sort_by" => "to.jrk")) as $c)
		{
			$row = $c->to();
			$ar = array(
				"task" => html::obj_change_url($row),
				"task_comm" => $row->comment()
			);
			foreach($strats as $strat)
			{
				$ar[$strat] = html::textbox(array(
					"name" => "a[".$row->id()."][$strat]",
					"value" => $data[$row->id()][$strat],
					"size" => 3
				));
			}
			$t->define_data($ar);
		}
		$t->set_sortable(false);
	}

	function _get_strat_eval($p)
	{
		$pp = get_current_person();
		$ol = new object_list(array(
			"class_id" => CL_PROJECT_ANALYSIS_ENTRY,
			"lang_id" => array(),
			"site_id" => array(),
			"proj" => $p->id(),
			"evaluator" => $pp->id()
		));
		if ($ol->count())
		{
			return $ol->begin();
		}
		else
		{
			$o = obj();
			$o->set_parent($p->id());
			$o->set_class_id(CL_PROJECT_ANALYSIS_ENTRY);
			$o->set_name(sprintf(t("Hinnang projektile %s"), $p->name()));
			$o->set_prop("proj", $p->id());
			$o->set_prop("evaluator" , $pp->id());
			$o->save();
			return $o;
		}
	}

	function _save_strat_a($arr)
	{
		// see if there is an eval for this person already, if not, create it , if it is, update it
		$se = $this->_get_strat_eval($arr["obj_inst"]);
		$se->set_meta("grid", $arr["request"]["a"]);
		$se->save();
	}

	function _srt($arr)
	{
		$tv =& $arr["prop"]["vcl_inst"];
		// add all evaluators
		$ol = new object_list(array(
			"class_id" => CL_PROJECT_ANALYSIS_ENTRY,
			"lang_id" => array(),
			"site_id" => array(),
			"proj" => $arr["obj_inst"]->id(),
		));
		foreach($ol->arr() as $o)
		{
			$tv->add_item(0, array(
				"name" => $o->prop("evaluator.name"),
				"id" => $o->prop("evaluator"),
				"url" => aw_url_change_var("evalr", $o->prop("evaluator"))
			));
		}
	}

	function _srt_tbl($arr)
	{
		if (!$arr["request"]["evalr"])
		{
			return;
		}
		$t =& $arr["prop"]["vcl_inst"];

		$this->_init_strat_a_tbl_r($t, $arr["obj_inst"]);

		$strats = array();
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_COL")) as $c)
		{
			$strats[$c->prop("to")] = $c->prop("to");
		}

		// get all eval objs
		$ol = new object_list(array(
			"class_id" => CL_PROJECT_ANALYSIS_ENTRY,
			"lang_id" => array(),
			"site_id" => array(),
			"proj" => $arr["obj_inst"]->id(),
			"evaluator" => $arr["request"]["evalr"]
		));
		$data = array();
		foreach($ol->arr() as $o)
		{
			$g = safe_array($o->meta("grid"));
			foreach($g as $evid => $d)
			{
				foreach($d as $strat => $eval)
				{
					$so = obj($strat);
					$data[$evid][$strat] += ($eval * $so->prop("weight") * $so->prop("priority"));
				}
			}
		}
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ROW")) as $c)
		{
			$row = $c->to();
			$ar = array(
				"task" => html::obj_change_url($row),
				"task_comm" => $row->comment()
			);
			$sum = 0;
			foreach($strats as $strat)
			{
				$ar[$strat] = number_format($data[$row->id()][$strat] / $ol->count(), 2);
				$sum += $ar[$strat];
			}
			$ar["sum"] = number_format($sum, 2);
			$t->define_data($ar);
		}
		$t->sort_by();
		$t->set_sortable(false);
	}

	function _strat_res($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$strats = array();
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_COL", "sort_by" => "to.jrk")) as $c)
		{
			$strats[$c->prop("to")] = $c->prop("to");
		}

		// get all eval objs
		$ol = new object_list(array(
			"class_id" => CL_PROJECT_ANALYSIS_ENTRY,
			"lang_id" => array(),
			"site_id" => array(),
			"proj" => $arr["obj_inst"]->id(),
		));
		$wts = $arr["obj_inst"]->meta("wts");
		$data = array();
		foreach($ol->arr() as $o)
		{
			$g = safe_array($o->meta("grid"));
			foreach($g as $evid => $d)
			{
				foreach($d as $strat => $eval)
				{
					$so = obj($strat);
					$wt = !empty($wts[$o->prop("evaluator")]) ? $wts[$o->prop("evaluator")]/100.0 : 1;
					$data[$evid][$strat] += $eval * $wt * $so->prop("weight") * $so->prop("priority");
				}
			}
		}

		$sbs = array();
		$sums = array();
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ROW", "sort_by" => "to.jrk")) as $c)
		{
			$row = $c->to();
			$ar = array(
				"task" => html::obj_change_url($row),
				"task_comm" => $row->comment()
			);
			$sum = 0;
			foreach($strats as $strat)
			{
				$ar[$strat] = number_format($data[$row->id()][$strat] / $ol->count(), 2);
				$sum += $ar[$strat];
				$sbs[$strat] += $ar[$strat];
				$sums[$strat] += $ar[$strat];
			}
			$ar["sum"] = number_format($sum, 2);
			$t->define_data($ar);
		}
		$this->_init_strat_res_tbl($t, $arr["obj_inst"], $sums);

		$t->sort_by();
		$t->set_sortable(false);
		$sbs["task"] = t("<b>Summa</b>");
		//$t->define_data($sbs);
	}

	function _init_strat_res_tbl(&$t, $o, $sums)
	{
		$t->define_field(array(
			"name" => "task",
			"caption" => t(""),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "task_comm",
			"caption" => t(""),
			"align" => "center"
		));
		// add all strats to table
		//arsort($sums);
		foreach($sums as $strat_id => $sum)
		{
			$s = obj($strat_id);
			$t->define_field(array(
				"name" => $s->id(),
				"caption" => $s->name(),
				"align" => "center",
				"numeric" => 1,
				"sortable" => 1
			));
		}

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("<b>Summa</b>"),
			"align" => "center",
			"numeric" => 1,
			"sortable" => 1
		));
	}
}
?>
