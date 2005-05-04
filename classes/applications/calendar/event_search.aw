<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/event_search.aw,v 1.62 2005/05/04 14:04:54 ahti Exp $
// event_search.aw - Sndmuste otsing 
/*

@classinfo syslog_type=ST_EVENT_SEARCH relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property event_cfgform type=relpicker reltype=RELTYPE_EVENT_CFGFORM
@caption Kasutatav vorm

@property use_output type=relpicker reltype=RELTYPE_EVENT_SHOW
@caption N&auml;itamise vorm

@property show_type type=select field=meta method=serialize
@caption N&auml;ta vaikimisi s&uuml;ndmusi

@default group=ftsearch

@property navigator_range type=chooser orient=vertical
@caption Ajavahemiku navigaator

@property ftsearch_fields type=chooser multiple=1 orient=vertical
@caption Vabateksti v&auml;jad

@property ftsearch_fields2 type=chooser multiple=1 orient=vertical
@caption Vabateksti v&auml;jad 2

@default group=ftform
@property ftform type=table no_caption=1
@caption Vorm

@default group=ftsearch
@property search_form type=callback callback=callback_search_form store=no
@caption Otsinguvorm

@default group=styles

@property month_navigator_style type=relpicker reltype=RELTYPE_STYLE
@caption Kuu navigaatori stiil

@property week_navigator_style type=relpicker reltype=RELTYPE_STYLE
@caption N&auml;dala navigaatori stiil

@property sform_table_style type=relpicker reltype=RELTYPE_STYLE
@caption Otsinguvormi tabeli stiil

@property sform_submit_style type=relpicker reltype=RELTYPE_STYLE
@caption Otsinguvormi nupu stiil

@groupinfo ftsearch caption="Vabateksti otsing"
@groupinfo ftform caption="Otsinguvorm seadistamine"
@groupinfo ftsearch caption="Otsinguvorm"
@groupinfo styles caption="Stiilid"

@groupinfo ftresults caption="Tulemuste seadistamine"
@default group=ftresults

@property result_table type=table 
@caption Tulemuste tabel

@reltype EVENT_CFGFORM value=1 clid=CL_CFGFORM
@caption S&uuml;ndmuse vorm

@reltype EVENT_SOURCE value=3 clid=CL_MENU,CL_PLANNER,CL_PROJECT
@caption S&uuml;ndmuste allikas

@reltype EVENT_SHOW value=4 clid=CL_CFGFORM
@caption N&auml;itamise vorm

@reltype STYLE value=5 clid=CL_CSS
@caption Stiil

*/

class event_search extends class_base
{
	var $cfgform_id;
	function event_search()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/event_search",
			"clid" => CL_EVENT_SEARCH,
		));

		$this->fields = array("fulltext","fulltext2", "start_date","end_date","project1","project2", "active", "format");
		lc_site_load("event_search", &$this);
	}

	function callback_search_form($arr)
	{
		$rv = array();
		return $rv;
	}

	function callback_pre_edit($arr)
	{
		$o = $arr["obj_inst"];
		$cfgform_id = $o->prop("event_cfgform");
		if (is_oid($cfgform_id) && $this->can("view", $cfgform_id))
		{
			$this->cfgform_id = $cfgform_id;
		};
	}

	function gen_ftsearch_fields($arr)
	{
		if (!$this->cfgform_id)
		{
			return PROP_IGNORE;
		};
		$t = get_instance(CL_CFGFORM);
		$props = $t->get_props_from_cfgform(array("id" => $this->cfgform_id));
		foreach($props as $propname => $propdata)
		{
			if ($propdata["type"] == "textbox" || $propdata["type"] == "textarea")
			{
				$opts[$propname] = $propdata["caption"];
			};
		};
		$arr["prop"]["options"] = $opts;
		
	}

	function gen_ftform($arr)
	{
		$prop = &$arr["prop"];
		$o = &$arr["obj_inst"];
		$t = &$prop["vcl_inst"];
		$formconfig = $o->meta("formconfig");
		$t->define_field(array(
			"name" => "type",
			"caption" => t("T&uuml;&uuml;p"),
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		
		$t->define_field(array(
			"name" => "caption",
			"caption" => t("Pealkiri"),
		));
		
		$t->define_field(array(
			"name" => "data",
			"caption" => t("Sisu"),
		));
		
		$t->define_field(array(
			"name" => "active",
			"caption" => t("Aktiivne"),
		));
		
		$t->set_sortable(false);
		
		$t->define_data(array(
			"name" => t("Tekstiotsing"),
			"caption" => html::textbox(array(
				"name" => "fulltext[caption]",
				"value" => $formconfig["fulltext"]["caption"] ? $formconfig["fulltext"]["caption"] : ("Tekstiotsing"),
			)),
			"active" => html::checkbox(array(
				"name" => "fulltext[active]",
				"value" => $formconfig["fulltext"]["active"],
				"checked" => $formconfig["fulltext"]["active"],
			)),
		));
		$t->define_data(array(
			"name" => t("Tekstiotsing 2"),
			"caption" => html::textbox(array(
				"name" => "fulltext2[caption]",
				"value" => $formconfig["fulltext2"]["caption"] ? $formconfig["fulltext2"]["caption"] : ("Tekstiotsing 2"),
			)),
			"active" => html::checkbox(array(
				"name" => "fulltext2[active]",
				"value" => $formconfig["fulltext2"]["active"],
				"checked" => $formconfig["fulltext2"]["active"],
			)),
		));
		
		$format_selector = html::select(array(
			"options" => array(
				0 => t("Ainult kuup&auml;ev"),
				1 => t("Kuup&auml;ev, kellaajad"),
			),
			"name" => "start_date[format]",
			"value" => $formconfig["start_date"]["format"],
		));
		
		$t->define_data(array(

			"name" => t("Alguskuup&auml;ev"),
			"caption" => html::textbox(array(
				"name" => "start_date[caption]",
				"value" => $formconfig["start_date"]["caption"] ? $formconfig["start_date"]["caption"] : t("Alguskuup&auml;ev"),
			)),
			"active" => html::checkbox(array(
				"name" => "start_date[active]",
				"value" => $formconfig["start_date"]["active"],
				"checked" => $formconfig["start_date"]["active"],
	
			)),
		));
		
		$t->define_data(array(
			"name" => t("L&otilde;ppkuup&auml;ev"),
			"caption" => html::textbox(array(
				"name" => "end_date[caption]",
				"value" => $formconfig["end_date"]["caption"] ? $formconfig["end_date"]["caption"] : t("L&otilde;ppkuup&auml;ev"),
			)),
			
			"active" => html::checkbox(array(
				"name" => "end_date[active]",
				"value" => $formconfig["end_date"]["active"],
				"checked" => $formconfig["end_date"]["active"],
			)),
		));
		
		$prj_conns = $o->connections_from(array(
			"type" => "RELTYPE_EVENT_SOURCE",
		));

		$prj_opts = array("0" => t("--vali--"));

		foreach($prj_conns as $prj_conn)
		{
			$id = $prj_conn->prop("to");
			$name = $prj_conn->prop("to.name");
			$prj_opts[$id] = $name;
		}
		$t->define_data(array(
			"name" => t("Projekt 1"),
			"caption" => html::textbox(array(
				"name" => "project1[caption]",
				"value" => $formconfig["project1"]["caption"] ? $formconfig["project1"]["caption"] : t("Projekt 1"),
			)),
			"active" => html::checkbox(array(
				"name" => "project1[active]",
				"value" => $formconfig["project1"]["active"],
				"checked" => $formconfig["project1"]["active"],
			)),
			"data" => html::select(array(
				"name" => "project1[rootnode]",
				"options" => $prj_opts,
				"multiple" => 1,
				"value" => $formconfig["project1"]["rootnode"],
			)),
		));
		
		$t->define_data(array(
			"name" => t("Projekt 2"),
			"caption" => html::textbox(array(
				"name" => "project2[caption]",
				"value" => $formconfig["project2"]["caption"] ? $formconfig["project2"]["caption"] : t("Projekt 2"),
			)),
			"data" => html::select(array(
				"name" => "project2[rootnode]",
				"options" => $prj_opts,
				"multiple" => 1,
				"value" => $formconfig["project2"]["rootnode"],
			)),
			"active" => html::checkbox(array(
				"name" => "project2[active]",
				"value" => $formconfig["project2"]["active"],
				"checked" => $formconfig["project2"]["active"],
			))
		));
		$t->define_data(array(
			"name" => t("Otsi nupp"),
			"caption" => html::textbox(array(
				"name" => "search_btn[caption]",
				"value" => $formconfig["search_btn"]["caption"] ? $formconfig["search_btn"]["caption"] : t("Otsi nupp"),
			)),
			"data" => "",
			"active" => html::checkbox(array(
				"name" => "search_btn[active]",
				"value" => $formconfig["search_btn"]["active"],
				"checked" => $formconfig["search_btn"]["active"],
			))
		));

	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "navigator_range":
				$prop["options"] = array(
					0 => t("Kuu navigaator"),
					1 => t("N&auml;dala navigaator"),
				);
				break;
			case "show_type":
				$prop["options"] = array(
					0 => t("Kuu j&auml;rgi"),
					1 => t("P&auml;eva j&auml;rgi"),
				);
				break;
			case "ftsearch_fields":
			case "ftsearch_fields2":
				$this->gen_ftsearch_fields($arr);
				break;

			case "ftform":
				$this->gen_ftform($arr);
				break;
				
			case "result_table":
				$retval = $this->gen_result_table($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$o = &$arr["obj_inst"];
		switch($prop["name"])
		{
			case "ftform":
				$fdata = array();
				
				foreach($this->fields as $fname)
				{
					if ($arr["request"][$fname])
					{
						$fdata[$fname] = $arr["request"][$fname];
					}
				}
				$o->set_meta("formconfig", $fdata);
				break;

			case "result_table":
				$o->set_meta("result_table", $arr["request"]["result_table"]);
				break;
		}
		return $retval;
	}

	function parse_alias($arr)
	{
		$args = $_GET;
		$args["id"] = $arr["alias"]["to"];
		return $this->show($args);
	}

	function gen_result_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$o = $arr["obj_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "caption",
			"caption" => t("Pealkiri"),
		));
		$t->define_field(array(
			"name" => "active",
			"caption" => t("Aktiivne"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "clickable",
			"caption" => t("Klikitav"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "fullview",
			"caption" => t("T&auml;isvaates"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "brs",
			"caption" => t("Reavahetused"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "sepb",
			"caption" => t("Eraldaja enne"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "sepa",
			"caption" => t("Eraldaja pärast"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "ord",
			"caption" => t("Jrk"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "props",
			"caption" => t("Seaded"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "sep",
			"caption" => t("V&auml;ljade eraldaja"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "fields",
			"caption" => t("Lisav&auml;ljad"),
		));
		
		$oldvals = $o->meta("result_table");

		$tc = get_instance(CL_CFGFORM);
		$cform_obj = new object($this->cfgform_id);
		$use_output = $cform_obj->prop("use_output");

		$prop_output = $arr["obj_inst"]->prop("use_output");
		if(is_oid($prop_output))
		{
			$use_output = $prop_output;
		}
		elseif (!is_oid($use_output))
		{
			$arr["prop"]["error"] = t("V&auml;ljundvorm on valimata");
			return PROP_ERROR;
		};

		$pname = $arr["prop"]["name"];


		$props = $tc->get_props_from_cfgform(array("id" => $use_output));

		$props["name"]["name"] = "name";
		$names = array();
		foreach($props as $prz)
		{
			$names[$prz["name"]] = $prz["name"];
		}
		foreach($props as $prop)
		{
			$sname = $prop["name"];
			$prps = array(
				"caption" => html::textbox(array(
					"name" => "${pname}[${sname}][caption]",
					"value" => empty($oldvals[$sname]["caption"]) ? $prop["caption"] : $oldvals[$sname]["caption"],
					"size" => 20,
				)),
				"sep" => html::textbox(array(
					"name" => "${pname}[${sname}][sep]",
					"value" => $oldvals[$sname]["sep"],
					"size" => 2,
				)),
				"name" => $prop["name"],
				"active" => html::checkbox(array(
					"name" => "${pname}[${sname}][active]",
					"value" => 1,
					"checked" => ($oldvals[$sname]["active"] == 1),
				)),
				"clickable" => html::checkbox(array(
					"name" => "${pname}[${sname}][clickable]",
					"value" => 1,
					"checked" => ($oldvals[$sname]["clickable"] == 1),
				)),
				"fullview" => html::checkbox(array(
					"name" => "${pname}[${sname}][fullview]",
					"value" => 1,
					"checked" => ($oldvals[$sname]["fullview"] == 1),
				)),
				"brs" => html::checkbox(array(
					"name" => "${pname}[${sname}][brs]",
					"value" => 1,
					"checked" => ($oldvals[$sname]["brs"] == 1),
				)),
				"sepa" => html::textbox(array(
					"name" => $pname."[$sname][sepa]",
					"value" => $oldvals[$sname]["sepa"],
					"size" => 3,
				)),
				"sepb" => html::textbox(array(
					"name" => $pname."[$sname][sepb]",
					"value" => $oldvals[$sname]["sepb"],
					"size" => 3,
				)),
				"ord" => html::textbox(array(
					"name" => "${pname}[${sname}][ord]",
					"value" => $oldvals[$sname]["ord"],
					"size" => 2,
				)),
			);
			if($prop["type"] == "date_select" || $prop["type"] == "datetime_select")
			{
				$prps["props"] = html::textarea(array(
					"name" => "${pname}[${sname}][props]",
					"value" => $oldvals[$sname]["props"],
					"rows" => 5,
					"cols" => 15,
				));
			}
			$nums = count($oldvals[$sname]["fields"]);
			foreach(safe_array($oldvals[$sname]["fields"]) as $k => $v)
			{
				if(empty($v))
				{
					$nums--;
				}
			}
			for($i = 0; $i <= $nums; $i++)
			{
				$prps["fields"] .= html::select(array(
					"name" => "${pname}[${sname}][fields][$i]",
					"options" => array(0 => "-- vali --") + $names,
					"value" => $oldvals[$sname]["fields"][$i],
				))."<br />";
			}
			$t->define_data($prps);
		};
		$t->set_sortable(false);
	}
	
	/**
		@attrib name=search nologin="1" all_args="1"
	**/
	function show($arr)
	{
		enter_function("event_search::show");
		$ob = new object($arr["id"]);
		$htmlc = get_instance("cfg/htmlclient", array("template" => "webform.tpl"));
		$htmlc->start_output();

		$formconfig = $ob->meta("formconfig");
		$do_search = false;
		$search = array();
		load_vcl("date_edit");
		$dt = new date_edit();
		$start_tm = $dt->get_timestamp($arr["start_date"]);
		$end_tm = $dt->get_timestamp($arr["end_date"]);
		$cur_days = cal_days_in_month(CAL_GREGORIAN, date("m"), date("Y"));
		$sd = $ob->prop("show_type") == 1 ? date("d") : 1;
		$ed = $ob->prop("show_type") == 1 ? date("d") : $cur_days;
		if($start_tm == -1)
		{
			$start_tm = mktime(0, 0, 0, date("m"), $sd, date("Y"));
			$arr["start_date"]["month"] = date("m");
			$arr["start_date"]["year"] = date("Y");
			$arr["start_date"]["day"] = 1;
		}
		if($end_tm == -1)
		{
			$end_tm = mktime(0, 0, 0, date("m"), $ed, date("Y"));
			$arr["end_date"]["month"] = date("m");
			$arr["end_date"]["year"] = date("Y");
			$arr["end_date"]["day"] = $cur_days;
		}
		if($formconfig["fulltext"]["active"])
		{
			$htmlc->add_property(array(
				"name" => "fulltext",
				"caption" => $formconfig["fulltext"]["caption"],
				"type" => "textbox",
				"value" => $arr["fulltext"],
			));
		}
		if($formconfig["fulltext2"]["active"])
		{
			$htmlc->add_property(array(
				"name" => "fulltext2",
				"caption" => $formconfig["fulltext2"]["caption"],
				"type" => "textbox",
				"value" => $arr["fulltext2"],
			));
		}
		
		if($formconfig["start_date"]["active"])
		{
			$htmlc->add_property(array(
				"name" => "start_date",
				"caption" => $formconfig["start_date"]["caption"],
				"type" => "date_select",
				"value" => $start_tm,
			));
		}
		
		if($formconfig["end_date"]["active"])
		{
			$htmlc->add_property(array(
				"name" => "end_date",
				"caption" => $formconfig["end_date"]["caption"],
				"type" => "date_select",
				"value" => $end_tm,
			));
		}
		$search_p1 = false;
		$search_p2 = false;
		$p_rn1 = $formconfig["project1"]["rootnode"];
		$p_rn2 = $formconfig["project2"]["rootnode"];
		$p_rn1 = is_array($p_rn1) ? $p_rn1 : array($p_rn1);
		$p_rn2 = is_array($p_rn2) ? $p_rn2 : array($p_rn2);
		foreach($p_rn1 as $pkey => $pval)
		{
			if(!is_oid($pval) || !$this->can("view", $pval))
			{
				unset($p_rn1[$pkey]);
			}
		}
		foreach($p_rn2 as $pkey => $pval)
		{
			if(!is_oid($pval) || !$this->can("view", $pval))
			{
				unset($p_rn2[$pkey]);
			}
		}
		if(count($p_rn1) > 0)
		{
			$prj_ch1 = array();
			$optgnames1 = array();
			$rn1 = array();
			foreach($p_rn1 as $trn1)
			{
				$tmp = obj($trn1);
				if($tmp->class_id() == CL_MENU)
				{
					$prj_cx = $this->_get_project_choices($trn1);
					// if there are projects to choose from, search from them, else assume that it's a event folder
					if(!empty($prj_cx))
					{
						$search_p1 = true;
						$prj_ch1[] = $prj_cx;
						$optgnames1[] = $tmp->name();
					}
					else
					{
						$rn1[] = $tmp->id();
					}
				}
				elseif($tmp->class_id() == CL_PLANNER)
				{
					$r = $tmp->prop("event_folder");
					if(is_oid($r) && $this->can("view", $r))
					{
						$rn1[] = $r;
					}
					// this goddamn calendar has to manage the 
					// events from other calendars and projects aswell.. oh hell..
					$sources = $tmp->connections_from(array(
						"type" => "RELTYPE_EVENT_SOURCE",
					));
					foreach($sources as $source)
					{
						if($source->prop("to.class_id") == CL_PLANNER)
						{
							$_tmp = $source->to();
							$rn1[] = $_tmp->prop("event_folder");
						}
						else
						{
							$rn1[] = $source->prop("to");
						}
					}
				}
				elseif($tmp->class_id() == CL_PROJECT)
				{
					$rn1[] = $trn1;
					$sources = $tmp->connections_from(array(
						"type" => "RELTYPE_SUBPROJECT",
					));
					foreach($sources as $source)
					{
						$rn1[] = $source->prop("to");
					}
				}
			}
		}
		if(count($p_rn2) > 0)
		{
			$rn2 = array();
			$prj_ch2 = array();
			$optgnames2 = array();
			foreach($p_rn2 as $trn2)
			{
				$tmp = obj($trn2);
				if($tmp->class_id() == CL_MENU)
				{
					$prj_cx = $this->_get_project_choices($trn2);
					if(!empty($prj_cx))
					{
						$optgnames2[] = $tmp->name();
						$search_p2 = true;
						$prj_ch2[] = $prj_cx;
					}
					else
					{
						$rn2[] = $tmp->id();
					}
				}
				elseif($tmp->class_id() == CL_PLANNER)
				{
					$r = $tmp->prop("event_folder");
					if(is_oid($r) && $this->can("view", $r))
					{
						$rn2[] = $r;
					}
					$sources = $tmp->connections_from(array(
						"type" => "RELTYPE_EVENT_SOURCE",
					));
					foreach($sources as $source)
					{
						if($source->prop("to.class_id") == CL_PLANNER)
						{
							$_tmp = $source->to();
							$rn2[] = $_tmp->prop("event_folder");
						}
						else
						{
							$rn2[] = $source->prop("to");
						}
					}
				}
				elseif($tmp->class_id() == CL_PROJECT)
				{
					$rn2[] = $trn2;
					$sources = $tmp->connections_from(array(
						"type" => "RELTYPE_SUBPROJECT",
					));
					foreach($sources as $source)
					{
						$rn2[] = $source->prop("to");
					}
				}
			}
		}
		if($search_p1 && $formconfig["project1"]["active"])
		{
			$vars = array(
				"name" => "project1",
				"caption" => $formconfig["project1"]["caption"],
				"type" => "select",
				"value" => $arr["project1"],
			);
			if(count($prj_ch1) > 1)
			{
				$vars["options"] = array(0 => t("kõik"));
				$vars["optgnames"] = $optgnames1;
				$vars["optgroup"] = $prj_ch1;
			}
			else
			{
				$vars["options"] = array(0 => t("kõik")) + reset($prj_ch1);
			}
			$htmlc->add_property($vars);
		}
		
		if($search_p2 && $formconfig["project2"]["active"])
		{
			$vars = array(
				"name" => "project2",
				"caption" => $formconfig["project2"]["caption"],
				"type" => "select",
				"value" => $arr["project2"],
			);
			if(count($prj_ch2) > 1)
			{
				$vars["options"] = array(0 => t("kõik"));
				$vars["optgnames"] = $optgnames2;
				$vars["optgroup"] = $prj_ch2;
			}
			else
			{
				$vars["options"] = array(0 => t("kõik")) + reset($prj_ch2);
			}
			$htmlc->add_property($vars);
		}
		
		$htmlc->add_property(array(
			"name" => "sbt",
			"caption" => t("Otsi"),
			"type" => "submit",
		));
		
		$do_search = true;
		if ($do_search)
		{
			$search["parent"] = $parx2 = array();
			$search["sort_by"] = "planner.start";
			$search["class_id"] = array(CL_CALENDAR_EVENT, CL_CRM_MEETING);
			$par1 = array();
			$par2 = array();
			if($search_p1 || $search_p2)
			{
				$all_projects1 = new object_list(array(
					"parent" => $p_rn1,
					"class_id" => array(CL_PROJECT, CL_PLANNER),
				));
				$par1 = $all_projects1->ids();
				$all_projects2 = new object_list(array(
					"parent" => $p_rn2,
					"class_id" => array(CL_PROJECT, CL_PLANNER),
				));
				$par2 = $all_projects2->ids();
				if (is_oid($arr["project1"]))
				{
					$search["parent"][] = $arr["project1"];
				}
				elseif($search_p1)
				{
					$search["parent"] = $par1;
				}
				if (is_oid($arr["project2"]))
				{
					$parx2[] = $arr["project2"];
				}
				elseif($search_p2)
				{
					$parx2 = $par2;
				}
			}
			elseif($rn1 || $rn2)
			{
				if($rn1)
				{
					if(is_array($rn1))
					{
						$search["parent"] = array_merge($rn1, $search["parent"]);
					}
					else
					{
						$search["parent"][] = $rn1;
					}
				}
				if($rn2)
				{
					if(is_array($rn2))
					{
						$parx2 = array_merge($rn2, $parx2);
					}
					else
					{
						$parx2[] = $rn2;
					}
				}
			}
			$search[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array(
							"end" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start_tm),
							"start1" => new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, ($end_tm + 86399)),
						),
					)),
					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array(
							"end" => -1,
							"start1" => new obj_predicate_compare(OBJ_COMP_BETWEEN, $start_tm, ($end_tm + 86399)),
						),
					)),
				),
			));
			$search["lang_id"] = array();
			$search["site_id"] = array();

			$ft_fields = $ob->prop("ftsearch_fields");
			$ft_fields2 = $ob->prop("ftsearch_fields2");
			if ($arr["fulltext"])
			{
				$or_parts = array();
				foreach(safe_array($ft_fields) as $ft_field)
				{
					$or_parts[$ft_field] = "%" . $arr["fulltext"] . "%";
				}
				$search[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => $or_parts,
				));
			}
			if ($arr["fulltext2"])
			{
				$or_parts = array();
				foreach(safe_array($ft_fields2) as $ft_field)
				{
					$or_parts[$ft_field] = "%" . $arr["fulltext2"] . "%";
				}
				$search[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => $or_parts,
				));
			}
			if(is_oid($arr["evt_id"]) && $this->can("view", $arr["evt_id"]))
			{
				$obj = obj($arr["evt_id"]);
				$orig = $obj->get_original();
				$search = array(
					"oid" => $orig->id(),
					"lang_id" => array(),
					"site_id" => array(),
				);
			}
			$clinf = aw_ini_get("classes");
			$edata = array();
			$ecount = array();
			if (sizeof($search["parent"]) != 0 || $search["oid"])
			{
				if($search["oid"])
				{
					$ol = new object_list($search);
				}
				else
				{
					$ol = new object_list($search);
					$oris = $ol->brother_ofs();
					if($arr["project2"])
					{
						$search2 = $search;
						$search2["parent"] = $parx2;
						$ol = new object_list($search2);
						$oris2 = $ol->brother_ofs();
						$ids = array_intersect($oris2, $oris);
					}
					else
					{
						$ids = $oris;
					}
					if(!empty($ids))
					{
						$ol = new object_list(array(
							"oid" => $ids,
							"class_id" => array(CL_CRM_MEETING, CL_CALENDAR_EVENT),
							"start1" => new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, ($end_tm + 3600*24)),
							"sort_by" => "planner.start",
							"lang_id" => array(),
							"site_id" => array(),
						));
					}
					else
					{
						$ol = new object_list();
					}
				}
				$origs = array();
				foreach($ol->arr() as $res)
				{
					$orig_id = $res->id();
					$origs[] = $orig_id;
					$edata[$orig_id] = array(
						"event_id" => $res->id(),
						"event" => $res->name(),
						"project_selector" => "n/a",
						"date" => date("d-m-Y", $res->prop("start1")),
					);
					$edata[$orig_id] = array_merge($edata[$orig_id], $res->properties());
					$ecount[$orig_id]++;
				}
				$this->read_template(($search["oid"] ? "show_event.tpl" : "search_results.tpl"));
				$tabledef = $ob->meta("result_table");
				uasort($tabledef, array($this, "__sort_props_by_ord"));
				$cdat = "";
				$col_count = 0;
				$clickable = false;
				foreach($tabledef as $key => $propdef)
				{
					if(!$propdef["active"])
					{
						continue;
					}
					if($key == "content")
					{
						continue;
					}
					if($propdef["clickable"])
					{
						$clickable = true;
					}
					
					$this->vars(array(
						"colcaption" => $propdef["caption"],
					));
					$cdat .= $this->parse("COLHEADER");
					$col_count++;
					
					$this->vars(array(
						"COLHEADER" => $cdat,
						"col_count" => $col_count,
					));
				}
			}
			$blist = array();
			if(!empty($origs))
			{
				$fls = new object_list(array(
					"brother_of" => $origs,
				));
				$blist = $fls->arr();
			}
			$pr1 = $formconfig["project1"]["rootnode"];
			$dats = array();
			foreach($blist as $b_o)
			{
				$par = $b_o->parent();
				if (!is_oid($par) || !$this->can("view", $par))
				{
					continue;
				}
				$orig = $b_o->brother_of();
				if ($edata[$orig])
				{
					if(!in_array($par, $par2) && !in_array($par, $par1))
					{
						continue;
					}
					$p2 = new object($par);
					$nm = $p2->name();
					if ($p2->class_id() == CL_MENU)
					{
						continue;
					}
					$dats[$orig][$par] = $nm;
				}
			}
			foreach($dats as $key => $val)
			{
				$val = safe_array($val);
				$valz = $val;
				sort($valz);

				$edata[$key]["projs"] = array_keys($val);
				$edata[$key]["project_selector"] = implode(", ", $valz);
			}
			if(count($prj_ch1) > 1)
			{
				$groups = array();
				$grps = array();
				foreach($prj_ch1 as $gid => $parr)
				{
					$obj = obj(key($parr));
					$parq = obj($obj->parent());
					$grps[$gid] = $parq->name();
					$groups[$gid] = array();
				}
				foreach($edata as $ekey => $eval)
				{
					foreach($prj_ch1 as $ckey => $cval)
					{
						$z = 0;
						foreach($cval as $key => $xz)
						{
							$cval[$key] = $z; 
							$z++;
						}
						foreach(safe_array($eval["projs"]) as $dkey)
						{
							foreach($cval as $xkey => $xv)
							{
								if($dkey == $xkey)
								{
									$groups[$ckey][$ekey] = $eval + array("ord" => $xv);
									break;
								}
							}
						}
					}
				}
			}
			else
			{
				$groups = array($edata);
			}
			exit_function("event_search::search_speed");
			$res = "";
			$aliasmrg = get_instance("aliasmgr");
			foreach($groups as $gkey => $edata)
			{
				if(count($groups) > 1)
				{
					$this->vars(array(
						"block_caption" => $grps[$gkey],
					));
					$res .= $this->parse("BLOCK");
					uasort($edata, array($this, "__sort_props_by_proj"));
				}
				foreach($edata as $ekey => $eval)
				{
					$id = $eval["event_id"];
					$obj = obj($id);
					$cdat = "";
					foreach($tabledef as $sname => $propdef)
					{
						if($sname == "content")
						{
							continue;
						}
						if($search["oid"] && !$propdef["fullview"])
						{
							continue;
						}
						if($search["oid"] && (empty($eval[$sname]) || $eval[$sname] == -1))
						{
							continue;
						}
						elseif(!$propdef["active"] && !$search["oid"])
						{
							continue;
						}
						$names = array_merge($sname, safe_array($tabledef[$sname]["fields"]));
						$names = $this->make_keys($names);
						$val = array();
						$skip = false;
						foreach($names as $nms)
						{
							if(empty($nms))
							{
								continue;
							}
							$v = create_links($eval[$nms]);
							if($nms == "image")
							{
								if(is_oid($v) && $this->can("view", $v))
								{
									$asd = obj($v);
									//$
								}
							}
							if ($nms == "start1" || $nms == "end")
							{
								if($skip)
								{
									continue;
								}
								$value = $tabledef[$nms]["props"];
								if(!empty($value))
								{
									if(strpos($value, "#php#") !== false)
									{
										$value = str_replace("#php#", "", $value);
										$value = str_replace("#/php#", "", $value);
										// eval is evil and inherited from the satan himself,
										// so i decided to use it here -- ahz
										eval($value);
									}
									else
									{
										$v = date($value, $v);
									}
								}
								else
								{
									$v = date("d-m-Y", $v);
								}
							}
							if($nms == "name")
							{
								if($obj->prop("udeftb1") != "")
								{
									$v = html::popup(array(
										"url" => $obj->prop("udeftb1"),
										"caption" => $v,
										"target" => "_blank",
										"toolbar" => 1,
										"directories" => 1,
										"status" => 1,
										"location" => 1,
										"resizable" => 1,
										"scrollbars" => 1,
										"menubar" => 1,
									));
								}
							}
							if($tabledef[$nms]["clickable"] == 1 && !$search["oid"])
							{
								$v = html::href(array(
									"url" => aw_ini_get("baseurl").aw_url_change_var(array("evt_id" => $id)),
									"caption" => $v,
								));
							}
							if($tabledef[$nms]["brs"] == 1)
							{
								$v = nl2br($v);
							}
							if(strpos($v, "#") !== false)
							{
								$aliasmrg->parse_oo_aliases($ekey, $v);
							}
							$val[] = $tabledef[$sname]["sepb"].$v.$tabledef[$sname]["sepa"];
						}
						$val = implode(" ".$tabledef[$sname]["sep"]." ", $val);
						$this->vars(array(
							"cell" => $val,
							"colcaption" => $propdef["caption"],
						));
						$cdat .= $this->parse("CELL");
						$this->vars(array("CELL" => $cdat));
					}
					$nmx = "content";
					$use = false;
					if($search["oid"] && $tabledef["content"]["fullview"])
					{
						$use = true;
					}
					elseif($tabledef["content"]["active"] && !($search["oid"]))
					{
						$use = true;
					}
					$content = "";
					if($use)
					{
						if(!empty($eval["content"]))
						{
							$content = nl2br($eval["content"]);
						}
						elseif(!empty($eval["utextarea1"]))
						{
							$content = nl2br($eval["utextarea1"]);
						}
						if(strpos($content, "#") !== false)
						{
							$aliasmrg->parse_oo_aliases($ekey, $content);
						}
					}
					$i++;
					$this->vars(array(
						"num" => $i % 2 ? 1 : 2,
					));
					if($use && !empty($content))
					{
						$this->vars(array(
							"fulltext_name" => $tabledef[$nmx]["caption"],
							"fulltext" => $content,
						));
						$fulltext = $this->parse("FULLTEXT");
					}
					else
					{
						$fulltext = "";
					}
					$this->vars(array(
						"FULLTEXT" => $fulltext,
					));
					$res .= $this->parse("EVENT");
				}
			}
			
			//Navigation bar
			$arr = $arr + array("section" => aw_global_get("section"));
			$next_month_args = $arr;
			$prev_month_args = $arr;
			
			if($next_month_args["start_date"]["month"] == 12)
			{
				$next_month_args["start_date"]["month"] = 1;
				$next_month_args["end_date"]["month"] = 1;
				
				$next_month_args["start_date"]["year"]++;
				$next_month_args["end_date"]["year"]++;
			}
			else
			{
				$next_month_args["start_date"]["month"]++;
				$next_month_args["end_date"]["month"] = $next_month_args["start_date"]["month"];
			}
			$next_month_args["start_date"]["day"] = 1;
			$next_month_args["end_date"]["day"] = cal_days_in_month(CAL_GREGORIAN, $next_month_args["end_date"]["month"], $next_month_args["end_date"]["year"]);
			
			if($prev_month_args["start_date"]["month"] == 1)
			{
				$prev_month_args["start_date"]["month"] = 12;
				$prev_month_args["start_date"]["year"]--;
				
				$prev_month_args["end_date"]["month"] = 12;
				$prev_month_args["end_date"]["year"]--;
			}
			else
			{
				$prev_month_args["start_date"]["month"]--;
				$prev_month_args["end_date"]["month"] = $prev_month_args["start_date"]["month"];
			}
			
			$prev_month_args["start_date"]["day"] = 1;
			$prev_month_args["end_date"]["day"] = cal_days_in_month(CAL_GREGORIAN, $prev_month_args["end_date"]["month"], $prev_month_args["end_date"]["year"]);
			
			$prev_days = $prev_month_args["end_date"]["day"];
			$next_days = $next_month_args["end_date"]["day"];
			$s_date = $arr["start_date"];
			$cur_days = cal_days_in_month(CAL_GREGORIAN, $s_date["month"], $s_date["year"]);
			$t_day = mktime(0, 0, 0, $s_date["month"], 1, $s_date["year"]);
			$day_of_week = date("w", $t_day);
			$offset = $day_of_week - 1 < 0 ? 6 : $day_of_week - 1;
			$weeks = ceil(($cur_days + $offset) / 7);
			for($i = 1; $i <= $weeks; $i++)
			{
				if($offset)
				{
					$start_day = $offset > 0 ? $prev_days - $offset : $t_day;
					$start_month = $offset > 0 ? $prev_month_args["start_date"]["month"] : $s_date["month"];
					$start_year = $offset > 0 ? $prev_month_args["start_date"]["year"] : $s_date["year"];
					$end_day = 7 - $offset;
					$end_year = $s_date["year"];
					$end_month = $s_date["month"];
					unset($offset);
				}
				elseif($i == $weeks)
				{
					$b_days = $end_day + 7;
					$start_day = $end_day + 1;
					$end_month = $b_days > $cur_days ? $next_month_args["start_date"]["month"] : $end_month;
					$end_year = $b_days > $cur_days ? $next_month_args["start_date"]["year"] : $end_year;
					$end_day = $b_days > $cur_days ? $b_days - $cur_days : $b_days;
				}
				else
				{
					$start_day = $end_day + 1;
					$end_day = $end_day + 7;
					$start_month = $s_date["month"];
					$start_year = $s_date["year"];
				}
				$week_args = array(
					"section" => aw_global_get("section"),
					"start_date" => array(
						"day" => $start_day,
						"year" => $start_year,
						"month" => $start_month,
					),
					"end_date" => array(
						"day" => $end_day,
						"year" => $end_year,
						"month" => $end_month,
					),
				);
				
				$this->vars(array(
					"week_url" => str_replace("event_search", "", $this->mk_my_orb("search", $week_args, "event_search")),
					"week_nr" => $i,
				));
				
				$nx = ($i == $weeks ? "next_weeks_end": "next_weeks").($start_day == $arr["start_date"]["day"] && $end_day == $arr["end_date"]["day"] ? "_b" : "");
				$res_weeks .= $this->parse($nx);
			}
			
			$this->vars(array(
				"begin_month_name" => locale::get_lc_month($arr["start_date"]["month"]),
				"begin_year" => $arr["start_date"]["year"],
				"prev_month_url" => str_replace("event_search", "", $this->mk_my_orb("search", $prev_month_args)),
				"next_month_url" => str_replace("event_search", "", $this->mk_my_orb("search", $next_month_args)),
				"next_weeks" => $res_weeks,
			));
			
			$this->vars(array(
				"EVENT" => $res,
			));
			$result = $this->parse();
			$htmlc->add_property(array(
				"name" => "results",
				"type" => "text",
				"no_caption" => 1,
				"value" => $result,
			));
		};

		$htmlc->finish_output(array(
			"data" => array(
				"class" => "",
				"section" => aw_global_get("section"),
				"action" => "search",
				"id" => $ob->id(),
				"alias" => "event_search",
			),
			"method" => "get",
			"form_handler" => aw_ini_get("baseurl")."/".aw_global_get("section"),
			"submit" => "no"
		));

		$html = $htmlc->get_result(array(
			"form_only" => 1
		));
		exit_function("event_search::show");
		return $search["oid"] ? $result : $html;
	}

	function _get_project_choices($parent)
	{
		if(!is_oid($parent) || !$this->can("view", $parent))
		{
			return array();
		}
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => array(CL_PROJECT, CL_PLANNER),
			"sort_by" => "objects.jrk",
		));
		return $ol->names();

	}

	function __sort_props_by_ord($el1, $el2)
	{
		return (int)($el1["ord"] - $el2["ord"]);
	}
	
	function __sort_props_by_proj($el1, $el2)
	{
		if((int)($el1["ord"] - $el2["ord"]) == 0)
		{
			return (int)($el1["start1"] - $el2["start1"]);
		}
		else
		{
			return (int)($el1["ord"] - $el2["ord"]);
		}
	}
}
?>
