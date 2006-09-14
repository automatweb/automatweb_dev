<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/rostering/rostering_workbench.aw,v 1.1 2006/09/14 09:11:38 kristo Exp $
// rostering_workbench.aw - T&ouml;&ouml;aja planeerimine 
/*

@classinfo syslog_type=ST_ROSTERING_WORKBENCH relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_rostering master_table=objects master_index=brother_of index=aw_oid

@default table=objects
@default group=general

	@property owner type=relpicker reltype=RELTYPE_OWNER table=aw_rostering field=aw_owner
	@caption Omanik

@default group=ppl

	@property cedit_tb type=toolbar no_caption=1 store=no

	@layout contacts_edit type=hbox

		@layout contacts_edit_tree type=hbox parent=contacts_edit closeable=1 area_caption=Struktuur

			@property cedit_tree type=treeview store=no parent=contacts_edit_tree no_caption=1

		@layout contacts_edit_table type=hbox parent=contacts_edit 
			@property cedit_table type=table store=no parent=contacts_edit_table no_caption=1


@default group=cycles

	@property cycles_tb type=toolbar no_caption=1 store=no
	@property cycles_table type=table no_caption=1 store=no

@default group=scenarios

	@property sc_tb type=toolbar no_caption=1 store=no
	@property sc_table type=table no_caption=1 store=no

@default group=wa

	@property wa_tb type=toolbar no_caption=1 store=no
	@property wa_table type=table no_caption=1 store=no

@default group=shifts

	@property sh_tb type=toolbar no_caption=1 store=no
	@property sh_table type=table no_caption=1 store=no

@default group=skills

	@property sk_tb type=toolbar no_caption=1 store=no

	@layout sk_main type=hbox width=20%:80%

		@layout sk_tree type=hbox parent=sk_main closeable=1 area_caption=P&auml;devuste&nbsp;puu

			@property sk_tree type=treeview store=no no_caption=1 parent=sk_tree

		@property sk_table type=table no_caption=1 store=no parent=sk_main

@default group=stats_wp

	@property stats_wp type=table no_caption=1 store=no

@default group=stats_overtime

	@property stats_overtime type=table no_caption=1 store=no

@default group=admin_act

	@property admin_act_tb type=toolbar no_caption=1 store=no
	
	@property admin_act_cal type=calendar no_caption=1 store=no

@default group=op_act

	@property op_act_sel type=text store=no
	@caption Stsenaarium

	@property op_act_cal type=text no_caption=1 store=no

	@property op_act_problems type=text store=no
	@caption Takistused


@default group=skills_losing

	@layout skl_main type=hbox width=20%:80%

		@layout skl_tree type=hbox parent=skl_main closeable=1 area_caption=Organisatsiooni&nbsp;struktuur

			@property skl_tree type=treeview store=no no_caption=1 parent=skl_tree

		@property skl_table type=table no_caption=1 store=no parent=skl_main


@groupinfo ppl caption="Isikud"

@groupinfo settings caption="Seaded"
	@groupinfo cycles caption="Ts&uuml;klid" parent=settings
	@groupinfo scenarios caption="Stsenaariumid" parent=settings
	@groupinfo wa caption="T&ouml;&ouml;kohad" parent=settings
	@groupinfo shifts caption="Vahetused" parent=settings
	@groupinfo skills caption="P&auml;devused" parent=settings submit=no 
	@groupinfo skills_losing caption="P&auml;devuste kadumine" parent=settings submit=no 

@groupinfo stats caption="Statistika"
	@groupinfo stats_wp caption="T&ouml;&ouml;postid" parent=stats
	@groupinfo stats_overtime caption="&Uuml;letunnid" parent=stats

@groupinfo graph caption="Graafikud"
	@groupinfo admin_act caption="Administratiivsed tegevused" parent=graph
	@groupinfo op_act caption="Operatiivsed tegevused" parent=graph

@reltype OWNER value=1 clid=CL_CRM_COMPANY
@caption Omanik

@reltype CYCLE value=2 clid=CL_PERSON_WORK_CYCLE
@caption Omanik

@reltype SCENARIO value=3 clid=CL_ROSTERING_SCENARIO
@caption Stsenaarium

@reltype WORKPLACE value=4 clid=CL_ROSTERING_WORKPLACE
@caption T&ouml;&ouml;koht

@reltype SHIFT value=5 clid=CL_ROSTERING_SHIFT
@caption Vahetus

@reltype SKILL value=6 clid=CL_PERSON_SKILL
@caption P&auml;devus

*/

class rostering_workbench extends class_base
{
	function rostering_workbench()
	{
		$this->init(array(
			"tpldir" => "applications/rostering/rostering_workbench",
			"clid" => CL_ROSTERING_WORKBENCH
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "cedit_tb":
			case "cedit_tree":
			case "cedit_table":
				return $this->_fwd_co($arr);
		
			case "cycles_tb":
				$this->_cycles_tb($arr);
				break;

			case "cycles_table":
				$this->_cycles_table($arr);
				break;

			case "sc_tb":
				$this->_sc_tb($arr);
				break;

			case "sc_table":
				$this->_sc_table($arr);
				break;

			case "wa_tb":
				$this->_wa_tb($arr);
				break;

			case "wa_table":
				$this->_wa_table($arr);
				break;

			case "sh_tb":
				$this->_sh_tb($arr);
				break;

			case "sh_table":
				$this->_sh_table($arr);
				break;

			case "sk_tb":
				$this->_sk_tb($arr);
				break;

			case "sk_table":
				$this->_sk_table($arr);
				break;
		
			case "sk_tree":
				$this->_sk_tree($arr);
				break;
		
			case "stats_wp":
				$this->_stats_wp($arr);
				break;

			case "stats_overtime":
				$this->_stats_overtime($arr);
				break;

			case "admin_act_tb":
				$this->_admin_act_tb($arr);
				break;

			case "admin_act_cal":
				$this->_admin_act_cal($arr);
				break;

			case "op_act_cal":
				$this->_op_act_cal($arr);
				break;

			case "op_act_sel":
				$this->_op_act_sel($arr);
				break;

			case "op_act_problems":
				return $this->_op_act_problems($arr);

			case "skl_tree":
				$this->_skl_tree($arr);
				break;

			case "skl_table":
				$this->_skl_table($arr);
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
			case "cedit_tb":
			case "cedit_tree":
			case "cedit_table":
				return $this->_setp_fwd_co($arr);
		}
		return $retval;
	}	

	function _fwd_co($arr)
	{
		static $i;
		if (!$i)
		{
			$i = get_instance(CL_CRM_COMPANY);
		}
		$obj = obj($arr["obj_inst"]->prop("owner"));
		$a2 = $arr;
		unset($a2["obj_inst"]);
		$a2["obj_inst"] = $obj;
		$a2["request"]["id"] = $obj->id();
		return $i->get_property($a2);
	}

	function _setp_fwd_co($arr)
	{
		static $i;
		if (!$i)
		{
			$i = get_instance(CL_CRM_COMPANY);
		}
		$obj = obj($arr["obj_inst"]->prop("owner"));
		$a2 = $arr;
		unset($a2["obj_inst"]);
		$a2["obj_inst"] = $obj;
		$a2["request"]["id"] = $obj->id();
		return $i->set_property($a2);
	}

	function callback_mod_retval($arr)
	{
		if($arr['request']['unit'])
		{
			$arr['args']['unit'] = $arr['request']['unit'];
		}

		if($arr['request']['cat'])
		{
			$arr['args']['cat'] = $arr['request']['cat'];
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr['unit'] = $_GET["unit"];
		$arr['cat'] = $_GET["cat"];
		$arr["sbt_data"] = 0;
		$arr["sbt_data2"] = 0;
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_rostering (aw_oid int primary key, aw_owner int)");
			return true;
		}
	}

	/**
		@attrib name=submit_delete_relations
	**/
	function submit_delete_relations($arr)
	{
		return $this->_sbt_fwd_co("submit_delete_relations", $arr);
	}

	/**
		@attrib name=submit_delete_ppl
	**/
	function submit_delete_ppl($arr)
	{
		return $this->_sbt_fwd_co("submit_delete_ppl", $arr);
	}

	/**
		@attrib name=cut_p
	**/
	function cut_p($arr)
	{
		return $this->_sbt_fwd_co("cut_p", $arr);
	}

	/**
		@attrib name=copy_p
	**/
	function copy_p($arr)
	{
		return $this->_sbt_fwd_co("copy_p", $arr);
	}

	/**
		@attrib name=paste_p
	**/
	function paste_p($arr)
	{
		return $this->_sbt_fwd_co("paste_p", $arr);
	}

	/**
		@attrib name=mark_p_as_important
	**/
	function mark_p_as_important($arr)
	{
		return $this->_sbt_fwd_co("mark_p_as_important", $arr);
	}

	function _sbt_fwd_co($act, $arr)
	{
		$i = get_instance(CL_CRM_COMPANY);
		$o = obj($arr["id"]);
		$arr["id"] = $o->prop("owner");
		return $i->$act($arr);
	}

	function _cycles_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => html::get_new_url(CL_PERSON_WORK_CYCLE, $arr["obj_inst"]->id(), array(
				"return_url" => get_ru(), 
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 2
			)),
			"tooltip" => t("Lisa")
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_cycles",
			"tooltip" => t("Kustuta ts&uuml;klid")
		));
	}

	function _init_cycles_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Ts&uuml;kkel"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "pri",
			"caption" => t("Prioriteet"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _cycles_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_cycles_t($t);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CYCLE")) as $c)
		{
			$o = $c->to();
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"oid" => $o->id(),
				"pri" => $o->prop("ord")
			));
		}
	}

	function _sc_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => html::get_new_url(CL_ROSTERING_SCENARIO, $arr["obj_inst"]->id(), array(
				"return_url" => get_ru(), 
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 3
			)),
			"tooltip" => t("Lisa")
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_cycles",
			"tooltip" => t("Kustuta stsenaarium")
		));
	}

	function _init_sc_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Stsenaarium"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _sc_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sc_t($t);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_SCENARIO")) as $c)
		{
			$o = $c->to();
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"oid" => $o->id()
			));
		}
	}

	function _wa_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => html::get_new_url(CL_ROSTERING_WORKPLACE, $arr["obj_inst"]->id(), array(
				"return_url" => get_ru(), 
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 4
			)),
			"tooltip" => t("Lisa")
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_cycles",
			"tooltip" => t("Kustuta t&ouml;&ouml;kohad")
		));
	}

	function _init_wa_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("T&ouml;&ouml;koht"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _wa_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_wa_t($t);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_WORKPLACE")) as $c)
		{
			$o = $c->to();
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"oid" => $o->id()
			));
		}
	}

	function _sh_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => html::get_new_url(CL_ROSTERING_SHIFT, $arr["obj_inst"]->id(), array(
				"return_url" => get_ru(), 
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 5
			)),
			"tooltip" => t("Lisa")
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_cycles",
			"tooltip" => t("Kustuta vahetused")
		));
	}

	function _init_sh_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Vahetus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _sh_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sh_t($t);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_SHIFT")) as $c)
		{
			$o = $c->to();
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"oid" => $o->id()
			));
		}
	}

	/**
		@attrib name=delete_cycles
	**/
	function delete_cycles($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	function _sk_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$pt = $arr["request"]["skill_id"] ? $arr["request"]["skill_id"] : $arr["obj_inst"]->id();
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => html::get_new_url(CL_PERSON_SKILL, $pt, array(
				"return_url" => get_ru(), 
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 6
			)),
			"tooltip" => t("Lisa")
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_cycles",
			"tooltip" => t("Kustuta p&auml;devused")
		));
	}

	function _init_sk_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("P&auml;devus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _sk_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sk_t($t);
		$pt = $arr["request"]["skill_id"] ? $arr["request"]["skill_id"] : $arr["obj_inst"]->id();
		$ol = new object_list(array(
			"class_id" => CL_PERSON_SKILL,
			"parent" => $pt,
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"oid" => $o->id()
			));
		}
	}

	function _init_stats_wp_t(&$t)
	{
		$t->define_field(array(
			"name" => "person",
			"caption" => t("&nbsp;"),
		));

		$ol = new object_list(array(
			"class_id" => CL_ROSTERING_WORKPLACE,
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			$t->define_field(array(
				"name" => $o->id(),
				"caption" => $o->name(),
				"align" => "center"
			));
		}
	}

	function _stats_wp($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_stats_wp_t($t);

		classload("core/date/date_calc");
		$m = get_instance("applications/rostering/rostering_model");		
		$start = get_week_start();
		$end = get_week_start()+24*7*3600;

		// get schedulers for ppl
		$co = get_instance(CL_CRM_COMPANY);
		$empl = $co->get_employee_picker(obj($arr["obj_inst"]->prop("owner")));

		foreach($empl as $empl_id => $empl_name)
		{
			$work_times = $m->get_schedule_for_person(obj($empl_id), $start, $end);
			$d = array(
				"person" => html::obj_change_url($empl_id)
			);
			foreach($work_times as $wt_item)
			{
				$d[$wt_item["workplace"]] .= date("d.m.Y H:i", $wt_item["start"])." - ".date("d.m.Y H:i", $wt_item["end"])." <br>";
			}
			$t->define_data($d);
		}
	}

	function _init_stats_overtime_t(&$t)
	{
		$t->define_field(array(
			"name" => "section",
			"caption" => t("&Uuml;ksus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "ot",
			"caption" => t("&Uuml;letunde"),
			"align" => "center"
		));
	}

	function _stats_overtime($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_stats_overtime_t($t);

		$co = get_instance(CL_CRM_COMPANY);
		$sects = $co->get_all_org_sections(obj($arr["obj_inst"]->prop("owner")));

		foreach($sects as $sect_id)
		{
			$t->define_data(array(
				"section" => html::obj_change_url($sect_id),
				"ot" => rand(1,50)
			));
		}
	}

	function _admin_act_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "new",
		));
	
		$clss = aw_ini_get("classes");
		foreach(array(CL_TASK, CL_CRM_MEETING) as $clid)
		{
			$tb->add_menu_item(array(
				"parent" => "new",
				"text" => $clss[$clid]["name"],
				"link" => html::get_new_url($clid, $arr["obj_inst"]->id(), array("return_url" => get_ru()))
			));
		}
	}

	function _admin_act_cal($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		$arr["prop"]["vcl_inst"]->configure(array(
			"overview_func" => array(&$this,"get_overview"),
		));

		if (!$arr["request"]["viewtype"])
		{
			$arr["request"]["viewtype"] = "month";
		}

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => !empty($arr["request"]["viewtype"]) ? $arr["request"]["viewtype"] : $arr["prop"]["viewtype"],
		));

		$start = $range["start"];
		$end = $range["end"];

		$events = new object_list(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => array(CL_TASK, CL_CRM_MEETING),
			"lang_id" => array(),
			"site_id" => array()
		));
		classload("core/icons");
		foreach($events->arr() as $o)
		{
			$icon = icons::get_icon_url($o);
			$t->add_item(array(
				"timestamp" => $o->prop("start1"),
				"item_start" => ($o->class_id() == CL_CRM_MEETING ? $o->prop("start1") : NULL),
				"item_end" => ($o->class_id() == CL_CRM_MEETING ? $o->prop("end") : NULL),
				"data" => array(
					"name" => $o->name(),
					"link" => html::get_change_url($o->id(), array("return_url" => get_ru())),
					"modifiedby" => $o->prop("modifiedby"),
					"icon" => $icon,
					'comment' => $o->comment(),
				),
			));
		}
	}

	function _op_act_cal($arr)
	{
		classload("core/date/date_calc");
		$m = get_instance("applications/rostering/rostering_model");		
		$start = get_week_start();
		$end = get_week_start()+24*7*3600;

		$chart = get_instance("vcl/gantt_chart");
		$chart->configure_chart (array (
			"chart_id" => "person_wh",
			"style" => "aw",
			"start" => $start,
			"end" => $end,
			"width" => 850,
			"row_height" => 10,
		));

		$i = 0;
		$days = array ("P", "E", "T", "K", "N", "R", "L");
		$columns = 7;
		while ($i < $columns)
		{
			$day_start = (get_day_start() + ($i * 86400));
			$day = date ("w", $day_start);
			$date = date ("j/m/Y", $day_start);
			$uri = aw_url_change_var ("mrp_chart_length", 1);
			$uri = aw_url_change_var ("mrp_chart_start", $day_start, $uri);
			$chart->define_column (array (
				"col" => ($i + 1),
				"title" => $days[$day] . " - " . $date,
				"uri" => $uri,
			));
			$i++;
		}


		$ol = new object_list(array(
			"class_id" => CL_ROSTERING_WORKPLACE,
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $wpl)
		{
			$chart->add_row (array (
				"name" => $wpl->id(),
				"title" => $wpl->name(),
				"uri" => html::obj_change_url($wpl)
			));
		}

		$co = get_instance(CL_CRM_COMPANY);
		$ppl = $co->get_employee_picker(obj($arr["obj_inst"]->prop("owner")));
		static $wtid;
		foreach($ppl as $p_id => $p_n)
		{
			$work_times = $m->get_schedule_for_person(obj($p_id), $start, $end);
			foreach($work_times as $wt_item)
			{
				$bar = array (
					"id" => ++$wtid,
					"row" => $wt_item["workplace"],
					"start" => $wt_item["start"],
					"length" => $wt_item["end"] - $wt_item["start"],
					"title" => $p_n.": ".date("d.m.Y H:i", $wt_item["start"])." - ".date("d.m.Y H:i", $wt_item["end"]),
					"uri" => aw_url_change_var("problem_id", $wtid)
				);

				$chart->add_bar ($bar);
			}
		}

	
		$arr["prop"]["value"] = $chart->draw_chart();
	}

	function _op_act_sel($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_ROSTERING_SCENARIO,
			"lang_id" => array(),
			"site_id" => array()
		));
		$ns = array();
		foreach($ol->names() as $id => $nm)
		{
			$ns[aw_url_change_var("set_scenario", $id)] = $nm;
		}
		$arr["prop"]["value"] = html::select(array(
			"options" => $ns,
			"name" => "set_scenario",
			"value" => aw_url_change_var("set_scenario", $arr["request"]["set_scenario"]),
			"onchange" => "window.location.href=document.changeform.set_scenario.options[document.changeform.set_scenario. selectedIndex].value;"
		));
	}

	function _op_act_problems($arr)
	{
		if (!$arr["request"]["problem_id"])
		{
			return PROP_IGNORE;
		}
		// list some sort of problems
		$rv = "";
		$rv .= html::checkbox(array(
			"name" => "no_laiki",
			"ch_value" => 1,
			"checked" => true
		))." sest ta ei meeldi eriti programmile <br>";
		$rv .= html::checkbox(array(
			"name" => "stupid",
			"ch_value" => 1,
			"checked" => true
		))." sest ta on nac loll <br>";
		$arr["prop"]["value"] = $rv;
		return PROP_OK;
	}

	function _sk_tree($arr)
	{
		classload("vcl/treeview");
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "sk_tree",
			),
			"root_item" => $arr["obj_inst"],
			"ot" => new object_tree(array(
				"class_id" => CL_PERSON_SKILL,
				"parent" => $arr["obj_inst"]->id(),
				"lang_id" => array(),
				"site_id" => array()
			)),
			"var" => "skill_id",
			"icon" => icons::get_icon_url(CL_PERSON_SKILL)
		));
	}

	function _skl_tree($arr)
	{
		$arr["prop"]["name"] = "unit_listing_tree";
		$this->_fwd_co($arr);
		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => "_crit",
			"parent" => 0,
			"name" => t("<font color=red>Kriitiline</font>"),
			"url" => aw_url_change_var("unit", "_crit")
		));
	}

	function _skl_table($arr)
	{
		$skill_list = new object_list(array(
			"class_id" => CL_PERSON_SKILL,
			"lang_id" => array(),
			"site_id" => array()
		));

		if ($arr["request"]["unit"] == "_crit")
		{
			$t =& $arr["prop"]["vcl_inst"];
			$t->define_field(array(
				"name" => "name",
				"caption" => t("Nimi"),
				"align" => "center",
				"sortable" => 1
			));
			$t->define_field(array(
				"name" => "skill",
				"caption" => t("P&auml;devused")
			));
			$co = get_instance(CL_CRM_COMPANY);
			$ws = $co->get_employee_picker(obj($arr["obj_inst"]->prop("owner")));
			foreach($ws as $p_id => $p_n)
			{
				if (rand(1, 10) > 2)
				{
					continue;
				}
				$sks = "";
				foreach($skill_list->arr() as $skill)
				{
					$sks .= $skill->name().": kaob ".rand(1,40)." p&auml;eva p&auml;rast<br>";
				}
				$t->define_data(array(
					"name" => html::obj_change_url($p_id),
					"skill" => $sks
				));
			}
			return;
		}
		$arr["prop"]["name"] = "human_resources";
		$this->_fwd_co($arr);
		$t =& $arr["prop"]["vcl_inst"];
		$t->remove_field("phone");
		$t->remove_field("email");
		$t->remove_field("section");
		$t->remove_field("rank");

		$t->define_field(array(
			"name" => "skill",
			"caption" => t("P&auml;devused")
		));

		foreach($t->get_data() as $idx => $row)
		{
			$p = obj($row["id"]);
			$sks = "";
			foreach($skill_list->arr() as $skill)
			{
				$sks .= $skill->name().": kaob ".rand(1,40)." p&auml;eva p&auml;rast<br>";
			}
			$row["skill"] = $sks;
			$t->set_data($idx, $row);
		}		
	}
}
?>
