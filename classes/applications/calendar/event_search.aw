<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/event_search.aw,v 1.12 2005/01/12 12:51:45 ahti Exp $
// event_search.aw - Sündmuste otsing 
/*

@classinfo syslog_type=ST_EVENT_SEARCH relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property event_cfgform type=relpicker reltype=RELTYPE_EVENT_CFGFORM
@caption Kasutatav vorm

@property use_output type=relpicker reltype=RELTYPE_EVENT_SHOW
@caption Näitamise vorm

@property show_type type=select field=meta method=serialize
@caption Näita vaikimisi sündmusi

@default group=ftsearch

@property navigator_range type=chooser orient=vertical
@caption Ajavahemiku navigaator

@property ftsearch_fields type=chooser multiple=1 orient=vertical
@caption Vabateksti väljad

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
@caption Nädala navigaatori stiil

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
@caption Sündmuse vorm

@reltype EVENT_SOURCE value=3 clid=CL_MENU,CL_PLANNER
@caption Sündmuste allikas

@reltype EVENT_SHOW value=4 clid=CL_CFGFORM
@caption Näitamise vorm

@reltype STYLE value=5 clid=CL_CSS
@caption Stiil

*/

// seostatakse seadete vorm
// seostatakse projektid kust alt otsida
// lastakse vormi sisu natuke konffida - see siis ilmelt toimub tabelis .. vms ..
// hoooly fuck

class event_search extends class_base
{
	var $cfgform_id;
	function event_search()
	{
		$this->init(array(
			"tpldir" => "applications/calendar",
			"clid" => CL_EVENT_SEARCH,
		));

		$this->fields = array("fulltext","start_date","end_date","project1","project2", "active", "format");
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
		if (is_oid($cfgform_id))
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
			"caption" => t("Tüüp"),
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
			//"type" => 
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
		
		$format_selector = html::select(array(
			"options" => array(
				0 => "Ainult kuupäev",
				1 => "Kuupäev, kellaajad"
			),
			"name" => "start_date[format]",
			"value" => $formconfig["start_date"]["format"],
		));
		
		$t->define_data(array(

			"name" => t("Alguskuupäev"),
			"caption" => html::textbox(array(
				"name" => "start_date[caption]",
				"value" => $formconfig["start_date"]["caption"] ? $formconfig["start_date"]["caption"] : t("Alguskuupäev"),
			)),
			"active" => html::checkbox(array(
				"name" => "start_date[active]",
				"value" => $formconfig["start_date"]["active"],
				"checked" => $formconfig["start_date"]["active"],
	
			)),
		));
		
		$t->define_data(array(
			"name" => t("Lõppkuupäev"),
			"caption" => html::textbox(array(
				"name" => "end_date[caption]",
				"value" => $formconfig["end_date"]["caption"] ? $formconfig["end_date"]["caption"] : t("Lõppkuupäev"),
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
		};
		
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
					0 => "Kuu navigaator",
					1 => "Nädala navigaator",
				);
				break;
			case "show_type":
				$prop["options"] = array(
					0 => "Kuu järgi",
					1 => "Päeva järgi",
				);
				break;
			case "ftsearch_fields":
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
					};
				};
				$o->set_meta("formconfig", $fdata);
				break;

			case "result_table":
				$o->set_meta("result_table", $arr["request"]["result_table"]);
				break;
		}
		return $retval;
	}	

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
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
			"caption" => t("Väljade eraldaja"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "fields",
			"caption" => t("Lisaväljad"),
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
			$arr["prop"]["error"] = t("Väljundvorm on valimata");
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
				"ord" => html::textbox(array(
					"name" => "${pname}[${sname}][ord]",
					"value" => $oldvals[$sname]["ord"],
					"size" => 2,
				)),
			);
			if($prop["type"] == "date_select" || $prop["type"] == "datetime_select")
			{
				$prps["props"] = html::textbox(array(
					"name" => "${pname}[${sname}][props]",
					"value" => $oldvals[$sname]["props"],
					"size" => 15,
				));
			}
			//arr($oldvals[$sname]["fields"]);
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
	
	function get_search_results($arr)
	{
		// 1. pane kokku object list
		$ob = new object($arr["id"]);
		$formconfig = $ob->meta("formconfig");
		$ft_fields = $ob->prop("ftsearch_fields");
		$all_projects1 = new object_list(array(
			"parent" => array($formconfig["project1"]["rootnode"]),
			"class_id" => array(CL_PROJECT, CL_PLANNER),
		));
		$all_projects2 = new object_list(array(
			"parent" => array($formconfig["project2"]["rootnode"]),
			"class_id" => array(CL_PROJECT, CL_PLANNER),
		));
		$par1 = $all_projects1->ids();
		$par2 = $all_projects2->ids();

		$search = array();
		$search["parent"] = array_merge($par1,$par2);
			
	       $ft_fields = $ob->meta("ftsearch_fields");
	       $or_parts = array("name" => "%" . $arr["str"] . "%");
	       foreach($ft_fields as $ft_field)
	       {
		       $or_parts[$ft_field] = "%" . $arr["str"] . "%";

	       };
	       $search[] = new object_list_filter(array(
		       "logic" => "OR",
		       "conditions" => $or_parts,
	       ));
		$search["sort_by"] = "planner.start";
		$search["class_id"] = array(CL_CRM_MEETING, CL_CALENDAR_EVENT);
		$start_tm = strtotime("today 0:00");
		$end_tm = strtotime("+30 days", $start_tm);
		$search["CL_CALENDAR_EVENT.start1"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $start_tm, $end_tm);
		$ol = new object_list($search);
		$ret = array();
		$baseurl = aw_ini_get("baseurl");
		foreach($ol->arr() as $o)
		{
			$orig = $o->get_original();
			$oid = $orig->id();
			$ret[$oid] = array(
				"url" => $baseurl . "/" . $oid,
				"title" => $orig->name(),
				"modified" => $orig->prop("start1"),
			);
		};

		return $ret;


		// 2. tagasta tulemused

	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	/**
		@attrib name=search nologin="1" all_args="1"
	**/
	function show($arr)
	{
		// vormigenekas
		// projektivalikute asemel kuvatakse 
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
		if(is_oid($formconfig["project1"]["rootnode"]) && $this->can("view", $formconfig["project1"]["rootnode"]))
		{
			$rn1 = $formconfig["project1"]["rootnode"];
			$tmp = obj($rn1);
			if($tmp->class_id() == CL_MENU)
			{
				$prj_ch1 = $this->_get_project_choices($rn1);
				// if there are projects to choose from, search from them, else assume that it's a event folder
				if(!empty($prj_ch1))
				{
					$search_p1 = true;
					$prj_ch1 = array("0" => t("kõik")) + $prj_ch1;
				}
				else
				{
					$rn1 = $tmp->id();
				}
			}
			elseif($tmp->class_id() == CL_PLANNER)
			{
				$r = $tmp->prop("event_folder");
				if(is_oid($r) && $this->can("view", $r))
				{
					$rn1 = $r;
				}
			}
		}
		if(is_oid($formconfig["project2"]["rootnode"]) && $this->can("view", $formconfig["project2"]["rootnode"]))
		{
			$rn2 = $formconfig["project2"]["rootnode"];
			$tmp = obj($rn2);
			if($tmp->class_id() == CL_MENU)
			{
				$prj_ch2 = $this->_get_project_choices($rn2);
				if(!empty($prj_ch2))
				{
					$search_p2 = true;
					$prj_ch2 = array("0" => t("kõik")) + $prj_ch2;
				}
				else
				{
					$rn2 = $tmp->id();
				}
			}
			elseif($tmp->class_id() == CL_PLANNER)
			{
				$r = $tmp->prop("event_folder");
				if(is_oid($r) && $this->can("view", $r))
				{
					$rn2 = $r;
				}
			}
		}
		if($search_p1)
		{
			$htmlc->add_property(array(
				"name" => "project1",
				"caption" => $formconfig["project1"]["caption"],
				"type" => "select",
				"options" => $prj_ch1,
				"value" => $arr["project1"],
			));
		}
		
		if($search_p2)
		{
			$htmlc->add_property(array(
				"name" => "project2",
				"caption" => $formconfig["project2"]["caption"],
				"type" => "select",
				"options" => $prj_ch2,
				"value" => $arr["project2"],
			));
		}
		
		$htmlc->add_property(array(
			"name" => "sbt",
			"caption" => t("Otsi"),
			"type" => "submit",
		));

		// perform the search only if a start_date has been selected - this means no search
		// when first viewing the page with search form
		
		//arr($arr);
		/*
		if ($start_tm != -1 && $end_tm != -1)
		{
			$do_search = true;
		};
		*/
		$do_search = true;
		if ($do_search)
		{
			$search["parent"] = array();
			$search["sort_by"] = "planner.start";
			$search["class_id"] = array(CL_CALENDAR_EVENT, CL_CRM_MEETING);
			$par1 = array();
			$par2 = array();
			if($search_p1 || $search_p2)
			{
				if (is_oid($arr["project1"]))
				{
					$search["parent"][] = $arr["project1"];
				}
				elseif($search_p1)
				{
					$all_projects1 = new object_list(array(
						"parent" => array($formconfig["project1"]["rootnode"]),
						"class_id" => array(CL_PROJECT, CL_PLANNER),
					));
					
					$search["parent"] = $par1 = $all_projects1->ids();
				}
				if (is_oid($arr["project2"]))
				{
					$search["parent"][] = $arr["project2"];
				}
				elseif($search_p2)
				{
					$all_projects2 = new object_list(array(
						"parent" => array($formconfig["project2"]["rootnode"]),
						"class_id" => array(CL_PROJECT, CL_PLANNER),
					));
					$par2 = $all_projects2->ids();
					$search["parent"] = array_merge($search["parent"], $par2);
				}
			}
			elseif($rn1 || $rn2)
			{
				if($rn1)
				{
					$search["parent"][] = $rn1;
				}
				if($rn2)
				{
					$search["parent"][] = $rn2;
				}
			}
			$search["CL_CRM_MEETING.start1"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $end_tm);
			$search["CL_CRM_MEETING.end"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start_tm);
			$search["CL_CALENDAR_EVENT.start1"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $end_tm);
			$search["CL_CALENDAR_EVENT.end"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start_tm);
			$ft_fields = $ob->prop("ftsearch_fields");
			// kuidas ma nd need parentid kokku laon?
			if ($arr["fulltext"])
			{
				$or_parts = array("name" => "%" . $arr["fulltext"] . "%");
				foreach($ft_fields as $ft_field)
				{
					$or_parts[$ft_field] = "%" . $arr["fulltext"] . "%";

				};
				//arr($or_parts);
				$search[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => $or_parts,
				));
			};
			$clinf = aw_ini_get("classes");
			$edata = array();
			$ecount = array();
			
			// there is a fatal flaw in my logic
			if (sizeof($search["parent"]) != 0)
			{
				$ol = new object_list($search);
				/*$bl = new object_list(array(
					"brother_of" => $ol->ids(),
				));
				*/
				$this->read_template("search_results.tpl");
				$tabledef = $ob->meta("result_table");
				//arr($tabledef);
				uasort($tabledef,array($this,"__sort_props_by_ord"));
				// first I have to sort the bloody thing in correct order
				//$this->sub_merge = 1;
				$cdat = "";
				foreach($tabledef as $propdef)
				{
					if ($propdef["active"])
					{
						$this->vars(array(
							"colcaption" => $propdef["caption"],
						));
						$cdat .= $this->parse("COLHEADER");
					};
					$this->vars(array("COLHEADER" => $cdat));
				};
				// vï¿½ siis .. nï¿½data ainult eventeid, mis on mï¿½ema valitud parenti all?
				//arr($ol);
				$origs = array();
				foreach($ol->arr() as $res)
				{
					$pr = new object($res->parent());
					// see on project
					// aga mitte orig_name vaid .. HAHA. bljaad raisk
					$orig_id = $res->brother_of();
					$origs[] = $orig_id;
					//print "oid = " . $res->id() . "<br>";
					$mpr = $pr->parent();
					//print "parent = " . $pr->name() . "/" . $pr->id() . "<br>";
					$mo = new object($mpr);
					//print "mpr = " . $mo->id() . "/" . $mo->name() . "<br>";
					//print "n = " . $res->name() . "<br>";
					// iga sndmuse kohta ma pean vaatama kas ta on mind huvitava projekti all vï¿½ mitte?
					$parent1 = $parent2 = "";
					if (in_array($pr->id(),$par1))
					{
						$parent1 = $pr->name();
						if ($edata[$orig_id])
						{
							$edata[$orig_id]["parent1"] = $parent1;
						};
					}
					if (in_array($pr->id(),$par2))
					{
						$parent2 = $pr->name();
						if ($edata[$orig_id])
						{
							$edata[$orig_id]["parent2"] = $parent2;
						};
					};

					if (!$edata[$orig_id])
					{
						 $edata[$orig_id] = array(
							"event_id" => $res->id(),
							"event" => $res->name(),
							"parent1" => $parent1,
							"parent2" => $parent2,
							"place" => $pr->name(),
							"parent" => $mo->name(),
							"project_selector" => "n/a",
							"date" => date("d-m-Y", $res->prop("start1")),
						);
						$edata[$orig_id] = array_merge($edata[$orig_id],$res->properties());
					};
					$orig = $res->get_original();
					$ecount[$orig->id()]++;
				};
			};
			$blist = array();
			if(!empty($origs))
			{
				$fls = new object_list(array(
					"brother_of" => $origs,
				));
				$blist = $fls->arr();
			}
			enter_function("event_search::search_speed");
			$pr1 = $formconfig["project1"]["rootnode"];
			foreach($blist as $b_o)
			{
				if (!is_oid($b_o->parent()) || !$this->can("view", $b_o->parent()))
				{
					continue;
				}
				$p2 = new object($b_o->parent());
				if ($p2->parent() == $pr1)
				{
					$orig = $b_o->brother_of();
					if ($edata[$orig])
					{
						$edata[$orig]["project_selector"] = $p2->name();
					};
				};
			};
			exit_function("event_search::search_speed");
			$res = "";
			
			$aliasmrg = get_instance("aliasmgr");
			foreach($edata as $ekey => $eval)
			{
				$cdat = "";
				foreach($tabledef as $sname => $propdef)
				{
					if(!$propdef["active"])
					{
						continue;
					}
					$names = array_merge($sname, safe_array($tabledef[$sname]["fields"]));
					$val = array();
					foreach($names as $nms)
					{
						if(empty($nms))
						{
							continue;
						}
						$v = $eval[$nms];
						if ($nms == "start1" || $nms == "end")
						{
							$a = $tabledef[$nms]["props"];
							if(!empty($a))
							{
								/*
								preg_replace("/#(.*)#/imsUe", $a, $mt);
								foreach($mt[0] as $m)
								{
								}
								*/
								//preg_match_all("/<(.*)>/", $a, $mt);
								//arr($mt);
								//while(strpos("<", 
								//$a = eregi_replace("#<*>#i", '#\1#', );
								//$a = str_replace("<br />", "##", $tabledef[$nms]["props"]);
								//$a = str_replace("<br>", "##", $a);
								$v = date($a, $v);
								//$v= str_replace("##", "<br />", $v);
							}
							else
							{
								$v = date("d-m-Y", $v);
							}
						}
						$aliasmrg->parse_oo_aliases($ekey, $v);
						$val[] = $v;
					}
					$val = implode(" ".$tabledef[$sname]["sep"]." ", $val);
					$this->vars(array("cell" => $val));
					$cdat .= $this->parse("CELL");
					$this->vars(array("CELL" => $cdat));
				}
				$nmx = "";
				if(!empty($eval["content"]) && $tabledef["content"]["active"])
				{
					$nmx = "content";
					$aliasmrg->parse_oo_aliases($ekey, $eval["content"]);
					$content = $eval["content"];
				}
				elseif(!empty($eval["utextarea1"]) && $tabledef["content"]["active"])
				{
					$aliasmrg->parse_oo_aliases($ekey, $eval["utextarea1"]);
					$content = $eval["utextarea1"];
					$nmx = "utextarea1";
				}
				$i++;
				$this->vars(array(
					"num" => $i % 2 ? 1 : 2,
				));
				if($nmx != "")
				{
					$this->vars(array(
						"fulltext_name" => $tabledef[$nmx]["caption"],
						"fulltext" => $content,
					));
					$this->vars(array(
						"FULLTEXT" => $this->parse("FULLTEXT"),
					));
					
				}
				$res .= $this->parse("EVENT");
			};
			
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
			$weeks = ceil(($cur_days + $offset) /7);
			for($i=1; $i <= $weeks; $i++)
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
				$res_weeks .= $this->parse(($i == $weeks ? "next_weeks_end": "next_weeks"));
			}
			
			$this->vars(array(
				"begin_month_name" => locale::get_lc_month($arr["start_date"]["month"]),
				"begin_year" => $arr["start_date"]["year"],
				//"next_month_url" => str_replace("class", "alias", $this->mk_my_orb("search", $next_month_args, "event_search")),
				"prev_month_url" => str_replace("event_search", "", $this->mk_my_orb("search", $prev_month_args)),
				"next_month_url" => str_replace("event_search", "", $this->mk_my_orb("search", $next_month_args)),
				"next_weeks" => $res_weeks,
			));
			
			$this->vars(array(
				"EVENT" => $res,
			));


			$htmlc->add_property(array(
				"name" => "results",
				"type" => "text",
				"no_caption" => 1,
				"value" => $this->parse(),
			));
		};

		$htmlc->finish_output(array(
			"data" => array(
				"class" => "",//get_class($this),
				"section" => aw_global_get("section"),
				"action" => "search",
				"id" => $ob->id(),
				"alias" => get_class($this),
			),
			"method" => "get",
			"form_handler" => aw_ini_get("baseurl") . "/" . aw_global_get("section"),
		));

		$html = $htmlc->get_result(array(
			"form_only" => 1
		));

		return $html;

		// kuupva numbrid on lihtsalt selectid
	}

	function _get_project_choices($parent)
	{
		if (!is_oid($parent))
		{
			return array();
		};
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => array(CL_PROJECT, CL_PLANNER),
		));
		return $ol->names();

	}

	function __sort_props_by_ord($el1,$el2)
	{
		return (int)($el1["ord"] - $el2["ord"]);
	}
}
?>
