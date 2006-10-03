<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/rostering/rostering_workbench.aw,v 1.4 2006/10/03 18:27:12 kristo Exp $
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

@default group=skills_g

	@property skg_tbl type=table no_caption=1 store=no

@default group=shifts_g

	@property shifts_g_tbl type=table no_caption=1 store=no

@default group=day_g

	@property day_g_tbl type=table no_caption=1 store=no

@groupinfo ppl caption="Isikud" submit=no

@groupinfo settings caption="Seaded"
	@groupinfo cycles caption="Ts&uuml;klid" parent=settings submit=no
	@groupinfo scenarios caption="Stsenaariumid" parent=settings submit=no
	@groupinfo wa caption="T&ouml;&ouml;kohad" parent=settings submit=no
	@groupinfo shifts caption="Vahetused" parent=settings submit=no
	@groupinfo skills caption="P&auml;devused" parent=settings submit=no 
	@groupinfo skills_losing caption="P&auml;devuste kadumine" parent=settings submit=no 

@groupinfo stats caption="Statistika"
	@groupinfo stats_wp caption="T&ouml;&ouml;postid" parent=stats submit=no
	@groupinfo stats_overtime caption="&Uuml;letunnid" parent=stats submit=no

@groupinfo graph caption="Graafikud"
	@groupinfo admin_act caption="Administratiivsed tegevused" parent=graph submit=no
	@groupinfo op_act caption="Operatiivsed tegevused" parent=graph
	@groupinfo skills_g caption="P&auml;devused" parent=graph
	@groupinfo shifts_g caption="Vahetused" parent=graph submit=no
	@groupinfo day_g caption="P&auml;eva vaade" parent=graph submit=no

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
		classload("core/date/date_calc");
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "day_g_tbl":
				$this->_day_g_tbl($arr);
				break;

			case "shifts_g_tbl":
				$this->_shifts_g_tbl($arr);
				break;

			case "skg_tbl":
				$this->_skg_tbl($arr);
				break;

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
		if ($arr["request"]["rostering_chart_start"])
		{
			$start = $arr["request"]["rostering_chart_start"];
		}
		if ($arr["request"]["rostering_chart_length"])
		{
			$end = $start + 24*$arr["request"]["rostering_chart_length"]*3600;
		}
		$columns = $arr["request"]["rostering_chart_length"] ? $arr["request"]["rostering_chart_length"] : 7;

		$chart = get_instance("vcl/gantt_chart");
		$chart->configure_chart (array (
			"chart_id" => "person_wh",
			"style" => "aw",
			"subdivisions" => $columns > 1 ? 3 : 24,
			"timespans" => $columns > 1 ? 3 : 24,
			"start" => $start,
			"end" => $end,
			"width" => 850,
			"row_height" => 12,
			"columns" => $columns,
			"row_dfn" => t("T&ouml;&ouml;post")
		));

		$i = 0;
		$days = array ("P", "E", "T", "K", "N", "R", "L");
		while ($i < $columns)
		{
			$day_start = ($start + ($i * 86400));
			$day = date ("w", $day_start);
			$date = date ("j/m/Y", $day_start);
			$uri = aw_url_change_var ("rostering_chart_length", 1);
			$uri = aw_url_change_var ("rostering_chart_start", $day_start, $uri);
			$chart->define_column (array (
				"col" => ($i + 1),
				"title" => $days[$day] . " - " . $date,
				"uri" => $uri,
			));
			$i++;
		}

		$co = get_instance(CL_CRM_COMPANY);
		$ppl = $co->get_employee_picker(obj($arr["obj_inst"]->prop("owner")));
		static $wtid;
		$wpl2p = array();
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
				$wpl2p[$wt_item["workplace"]][$p_id] = $bar;
				$bar["row"] = $wt_item["workplace"]."_".$p_id;
				$chart->add_bar ($bar);
			}
		}


		$ol = new object_list(array(
			"class_id" => CL_ROSTERING_WORKPLACE,
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $wpl)
		{
			//html::get_change_url($wpl->id(), array("return_url" => get_ru()))
			$bar = array (
				"name" => $wpl->id(),
				"title" => "<b>".$wpl->name()."</b>",
				"uri" => aw_url_change_var("show_p", $wpl->id())
			);
			$chart->add_row ($bar);

			// get ppl for workpost
			foreach($wpl2p[$wpl->id()] as $p_id => $sets)
			{
				$po= obj($p_id);
				$chart->add_row (array (
					"name" => $wpl->id()."_".$p_id,
					"title" => " -&gt; ".$po->name(),
					"uri" => html::get_change_url($p_id, array("return_url" => get_ru()))
				));
			}
		}


		$arr["prop"]["value"] = '<div id="tablebox">
		    <div class="pais">
			<div class="caption">'.$this->create_chart_navigation($arr).'</div>
			<div class="navigaator">
			</div>
		    </div>
		    <div class="sisu">
		    <!-- SUB: GRID_TABLEBOX_ITEM -->
			'.$chart->draw_chart().'
		    <!-- END SUB: GRID_TABLEBOX_ITEM -->
		    </div>
		    <div>
		    </div>	
		</div>';	
	}

    // @attrib name=get_time_days_away
	// @param days required type=int
	// @param direction optional type=int
	// @param time optional
	// @returns UNIX timestamp for time of day start $days away from day start of $time
	// @comment DST safe if cumulated error doesn't exceed 12h. If $direction is negative, time is computed for days back otherwise days to.
	function get_time_days_away ($days, $time = false, $direction = 1)
	{
		if (false === $time)
		{
			$time = time ();
		}

		$time_daystart = mktime (0, 0, 0, date ("m", $time), date ("d", $time), date("Y", $time));
		$day_start = ($direction < 0) ? ($time_daystart - $days*86400) : ($time_daystart + $days*86400);
		$nodst_hour = (int) date ("H", $day_start);

		if ($nodst_hour)
		{
			if ($nodst_hour < 13)
			{
				$dst_error = $nodst_hour;
				$day_start = $day_start - $dst_error*3600;
			}
			else
			{
				$dst_error = 24 - $nodst_hour;
				$day_start = $day_start + $dst_error*3600;
			}
		}

		return $day_start;
	}

	function get_week_start ($time = false) //!!! somewhat dst safe (safe if error doesn't exceed 12h)
	{
		if (!$time)
		{
			$time = time ();
		}

		$date = getdate ($time);
		$wday = $date["wday"] ? ($date["wday"] - 1) : 6;
		$week_start = $time - ($wday * 86400 + $date["hours"] * 3600 + $date["minutes"] * 60 + $date["seconds"]);
		$nodst_hour = (int) date ("H", $week_start);

		if ($nodst_hour)
		{
			if ($nodst_hour < 13)
			{
				$dst_error = $nodst_hour;
				$week_start = $week_start - $dst_error*3600;
			}
			else
			{
				$dst_error = 24 - $nodst_hour;
				$week_start = $week_start + $dst_error*3600;
			}
		}

		return $week_start;
	}

	function create_chart_navigation ($arr)
	{
		$start = (int) ($arr["request"]["rostering_chart_start"] ? $arr["request"]["rostering_chart_start"] : time ());
		$columns = (int) ($arr["request"]["rostering_chart_length"] ? $arr["request"]["rostering_chart_length"] : 7);
		$start = ($columns == 7) ? $this->get_week_start ($start) : $start;
		$period_length = $columns * 86400;
		$length_nav = array ();
		$start_nav = array ();

		for ($days = 1; $days < 8; $days++)
		{
			if ($columns == $days)
			{
				$length_nav[] = $days;
			}
			else
			{
				$length_nav[] = html::href (array (
					"caption" => $days,
					"url" => aw_url_change_var ("rostering_chart_length", $days),
				));
			}
		}

		$start_nav[] = html::href (array (
			"caption" => t("<<"),
			"title" => t("5 tagasi"),
			"url" => aw_url_change_var ("rostering_chart_start", ($this->get_time_days_away (5*$columns, $start, -1))),
		));
		$start_nav[] = html::href (array (
			"caption" => t("Eelmine"),
			"url" => aw_url_change_var ("rostering_chart_start", ($this->get_time_days_away ($columns, $start, -1))),
		));
		$start_nav[] = html::href (array (
			"caption" => t("Täna"),
			"url" => aw_url_change_var ("rostering_chart_start", $this->get_week_start ()),
		));
		$start_nav[] = html::href (array (
			"caption" => t("Järgmine"),
			"url" => aw_url_change_var ("rostering_chart_start", ($this->get_time_days_away ($columns, $start))),
		));
		$start_nav[] = html::href (array (
			"caption" => t(">>"),
			"title" => t("5 edasi"),
			"url" => aw_url_change_var ("rostering_chart_start", ($this->get_time_days_away (5*$columns, $start))),
		));

		$navigation = sprintf(t('&nbsp;&nbsp;Periood: %s &nbsp;&nbsp;Päevi perioodis: %s'), implode (" ", $start_nav) ,implode (" ", $length_nav));

		if (is_oid ($arr["request"]["rostering_hilight"]))
		{
			$project = obj ($arr["request"]["rostering_hilight"]);
			$deselect = html::href (array (
				"caption" => t("Kaota valik"),
				"url" => aw_url_change_var ("rostering_hilight", ""),
			));
			$change_url = html::obj_change_url ($project);
			$navigation .= t(' &nbsp;&nbsp;Valitud projekt: ') . $change_url . ' (' . $deselect . ')';
		}

		return $navigation;
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
		))." t&ouml;&ouml;tunnid &uuml;letatud <br>";
		$rv .= html::checkbox(array(
			"name" => "stupid",
			"ch_value" => 1,
			"checked" => true
		))." teises vahetuses <br>";
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

	function _init_skg_tbl(&$t, &$wpl2skill)
	{
		// list all wp's and for those, needed skills
		$ol = new object_list(array(
			"class_id" => CL_ROSTERING_WORKPLACE,
			"lang_id" => array(),
			"site_id" => array()
		));
		$t->define_field(array(
			"name" => "empl",
			"caption" => t("T&ouml;&ouml;taja"),
			"align" => "left"
		));
		foreach($ol->arr() as $o)
		{
			$t->define_field(array(
				"name" => $o->id(),
				"caption" => $o->name(),
				"align" => "center"
			));
			foreach($o->connections_from(array("type" => "RELTYPE_SKILL")) as $c)
			{
				$skill_id = $c->prop("to");
				$skill = obj($skill_id);
				$t->define_field(array(
					"name" => $skill_id,
					"parent" => $o->id(),
					"caption" => $skill->name(),
					"align" => "center"
				));
				$wpl2skill[$o->id()][$skill_id] = $skill_id;
			}
		}
	}

	function _skg_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_skg_tbl($t, $wpl2skill);

		$co = obj($arr["obj_inst"]->prop("owner"));
		$co_i = $co->instance();
		$empl = $co_i->get_employee_picker($co);

		$sects = $this->get_all_org_sections($co, $t, $empl);
		$t->set_sortable(false);
	}

	function get_all_org_sections($obj, &$t, $empl)
	{
		static $retval, $level;
		$level++;
		foreach ($obj->connections_from(array("type" => "RELTYPE_SECTION")) as $section)
		{
			$retval[$obj->id()][] = $section->prop("to");
			$section_obj = $section->to();
			$t->define_data(array(
				"empl" => str_repeat("&nbsp;", $level*3).$section_obj->name()
			));

			// get all employees for this
			foreach($section_obj->connections_from(array("type" => "RELTYPE_WORKERS")) as $w_c)
			{
				$emplo = $w_c->to();

				$d = array(
					"empl" => str_repeat("&nbsp;", ($level+1)*3).html::obj_change_url($emplo)
				);
				// read the skills each person has
				foreach($emplo->connections_from(array("type" => "RELTYPE_HAS_SKILL")) as $c)
				{
					$hs = $c->to();
					$d[$hs->prop("skill")] = "x";
				}
				$t->define_data($d);
			}

			$this->get_all_org_sections($section_obj, $t, $empl);

		}
		$level--;
		return $retval;
	}

	function _init_shifts_g_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "shift",
			"caption" => t("Vahetus"),
			"align" => "left"
		));
		$ws = get_week_start();
		for($i = 0; $i < 7; $i++)
		{
			$t->define_field(array(
				"name" => "d".$i,
				"caption" => date("d.m.Y", $ws + $i * 24 * 3600),
				"align" => "center"
			));
		}
	}

	function _shifts_g_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_shifts_g_tbl($t);

		$m = get_instance("applications/rostering/rostering_model");
		$co = obj($arr["obj_inst"]->prop("owner"));
		$co_i = $co->instance();
		$ppl = $co_i->get_employee_picker($co);

		$wt = array();
		foreach($ppl as $p_id => $p_nm)
		{
			$wts = $m->get_schedule_for_person(obj($p_id), get_week_start(), get_week_start() + 24 * 7 * 3600);
			foreach($wts as $wtm)
			{
				$wt[$wtm["workplace"]][date("d.m.Y", $wtm["start"])][$p_id] = $wtm;
			}
		}
		$shift_list = new object_list(array(
			"class_id" => CL_ROSTERING_SHIFT,
			"site_id" => array(),
			"lang_id" => array()
		));
		$wpl2shift = array();
		$start = get_week_start();
		foreach($shift_list->arr() as $shift)
		{
			foreach($shift->connections_from(array("type" => "RELTYPE_WORKPLACE")) as $c)
			{
				$wpl = $c->to();
				$wpl2shift[$wpl->id()][] = $shift;
			}
		}

		foreach($wpl2shift as $wpl_id => $shifts)
		{
			$t->define_data(array(
				"shift" => html::obj_change_url($wpl_id)
			));
			foreach($shifts as $shift)
			{
				$d = array(
					"shift" => "&nbsp;&nbsp;&nbsp;&nbsp;".html::obj_change_url($shift)
				);

				for ($i = 0; $i < 7; $i++)
				{
					$tm = date("d.m.Y", $start + $i * 24 * 3600);
					$wpl_data = $wt[$wpl_id][$tm];
					list($p_id) = each($wpl_data);
					if (!$p_id)
					{
						$d["d".$i] = t("Puudu inimene");
					}
					else
					{
						$d["d".$i] = html::obj_change_url($p_id);
					}
				}
				$t->define_data($d);
			}
		}
		$t->set_sortable(false);
	}

	function _init_day_g_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "hr",
			"caption" => t("Aeg"),
			"align" => "left"
		));
		
		$wpl_list = new object_list(array(
			"class_id" => CL_ROSTERING_WORKPLACE,
			"site_id" => array(),
			"lang_id" => array()
		));
		foreach($wpl_list->arr() as $wpl)
		{
			$t->define_field(array(
				"name" => $wpl->id(),
				"caption" => $wpl->name(),
				"align" => "center"
			));
		}
	}

	function _day_g_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_day_g_tbl($t);

		$m = get_instance("applications/rostering/rostering_model");
		$co = obj($arr["obj_inst"]->prop("owner"));
		$co_i = $co->instance();
		$ppl = $co_i->get_employee_picker($co);

		$wt = array();
		foreach($ppl as $p_id => $p_nm)
		{
			$wts = $m->get_schedule_for_person(obj($p_id), get_week_start(), get_week_start() + 24 * 7 * 3600);
			foreach($wts as $wtm)
			{
				$wtm["person_id"] = $p_id;
				$wt[date("H", $wtm["start"])][] = $wtm;
			}
		}

		$shift_list = new object_list(array(
			"class_id" => CL_ROSTERING_SHIFT,
			"site_id" => array(),
			"lang_id" => array(),
		));
		$shift_list->sort_by(array("prop" => "start_time"));
		foreach($shift_list->arr() as $shift)
		{
			$t->define_data(array(
				"hr" => html::obj_change_url($shift)
			));
			list($endt) = explode(":", $shift->prop("end_time"));
			list($st) = explode(":", $shift->prop("start_time"));
			if ($endt < $st)
			{
				$endt = 24;
			}
			for($i = $st; $i < $endt; $i++)
			{
				$d = array(
					"hr" => sprintf("&nbsp;&nbsp;&nbsp;&nbsp;%02d:00 - %02d:00", $i, $i+1)
				);
				foreach($wt[date("H", get_day_start() + $i * 3600)] as $item)
				{
					$d[$item["workplace"]] = html::obj_change_url($item["person_id"]);
				}
				$t->define_data($d);
				
			}
		}
		$t->set_sortable(false);
	}
}
?>
