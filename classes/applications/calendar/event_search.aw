<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/event_search.aw,v 1.4 2004/12/01 12:12:14 kristo Exp $
// event_search.aw - Sündmuste otsing 
/*

@classinfo syslog_type=ST_EVENT_SEARCH relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property event_cfgform type=relpicker reltype=RELTYPE_EVENT_CFGFORM
@caption Kasutatav vorm

@default group=ftsearch
@property ftsearch_fields type=chooser multiple=1 orient=vertical
@caption Vabateksti väljad

@default group=ftform
@property ftform type=table
@caption Vorm

@default group=ftsearch
@property search_form type=callback callback=callback_search_form store=no
@caption Otsinguvorm

@default group=ftresults
@property result_table type=table 
@caption Tulemuste tabel

@groupinfo ftsearch caption="Vabateksti otsing"
@groupinfo ftform caption="Otsinguvorm seadistamine"
@groupinfo ftsearch caption="Otsinguvorm"
@groupinfo ftresults caption="Tulemuste seadistamine"

@reltype EVENT_CFGFORM value=1 clid=CL_CFGFORM
@caption Sündmuse vorm

@reltype EVENT_SOURCE value=2 clid=CL_PROJECT
@caption Sündmuste allikas

@reltype PROJECT_SELECTOR value=3 clid=CL_MENU
@caption Projekti valik

@reltype EVENT_SHOW value=4 clid=CL_CFGFORM
@caption Näitamise vorm

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
			"clid" => CL_EVENT_SEARCH
		));

		$this->fields = array("fulltext","start_date","end_date","project1","project2");
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
		$o = $arr["obj_inst"];
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

		$t->set_sortable(false);

		$t->define_data(array(
			"type" => t("Tekstiotsing"),
			"caption" => html::textbox(array(
				"name" => "fulltext[caption]",
				"value" => $formconfig["fulltext"]["caption"] ? $formconfig["fulltext"]["caption"] : ("Tekstiotsing"),
			)),
		));
		
		$t->define_data(array(
			"type" => t("Alguskuupäev"),
			"caption" => html::textbox(array(
				"name" => "start_date[caption]",
				"value" => $formconfig["start_date"]["caption"] ? $formconfig["start_date"]["caption"] : t("Alguskuupäev"),
			)),
		));
		
		$t->define_data(array(
			"type" => t("Lõppkuupäev"),
			"caption" => html::textbox(array(
				"name" => "end_date[caption]",
				"value" => $formconfig["end_date"]["caption"] ? $formconfig["end_date"]["caption"] : t("Lõppkuupäev"),
			)),
		));
		
		$prj_conns = $o->connections_from(array(
			"type" => "RELTYPE_PROJECT_SELECTOR",
		));

		$prj_opts = array("0" => t("--vali--"));

		foreach($prj_conns as $prj_conn)
		{
			$id = $prj_conn->prop("to");
			$name = $prj_conn->prop("to.name");
			$prj_opts[$id] = $name;
		};
		
		$t->define_data(array(
			"type" => t("Projekt 1"),
			"caption" => html::textbox(array(
				"name" => "project1[caption]",
				"value" => $formconfig["project1"]["caption"] ? $formconfig["project1"]["caption"] : t("Projekt 1"),
			)),
			"data" => html::select(array(
				"name" => "project1[rootnode]",
				"options" => $prj_opts,
				"value" => $formconfig["project1"]["rootnode"],
			)),
		));
		
		$t->define_data(array(
			"type" => t("Projekt 2"),
			"caption" => html::textbox(array(
				"name" => "project2[caption]",
				"value" => $formconfig["project2"]["caption"] ? $formconfig["project2"]["caption"] : t("Projekt 2"),
			)),
			"data" => html::select(array(
				"name" => "project2[rootnode]",
				"options" => $prj_opts,
				"value" => $formconfig["project2"]["rootnode"],
			)),
		));

	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
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

		$oldvals = $o->meta("result_table");

		$tc = get_instance(CL_CFGFORM);
		$cform_obj = new object($this->cfgform_id);
		$use_output = $cform_obj->prop("use_output");


		if (!is_oid($use_output))
		{
			$arr["prop"]["error"] = t("Väljundvorm on valimata");
			return PROP_ERROR;
		};

		$pname = $arr["prop"]["name"];


		$props = $tc->get_props_from_cfgform(array("id" => $use_output));

		$props["name"]["name"] = "name";

		foreach($props as $prop)
		{
			$sname = $prop["name"];
			$t->define_data(array(
				"caption" => html::textbox(array(
					"name" => "${pname}[${sname}][caption]",
					"value" => empty($oldvals[$sname]["caption"]) ? $prop["caption"] : $oldvals[$sname]["caption"],
					"size" => 20,
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
					
			));


		};
		$t->set_sortable(false);

	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$o = $arr["obj_inst"];
		switch($prop["name"])
		{
			case "ftform":
				$o = $arr["obj_inst"];
				$fdata = array();
				foreach($this->fields as $fname)
				{
					if ($arr["request"][$fname])
					{
						$fdata[$fname] = $arr["request"][$fname];
					};
				};	
				$o->set_meta("formconfig",$fdata);
				break;

			case "result_table":
				$o->set_meta("result_table",$arr["request"]["result_table"]);
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
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function get_search_results($arr)
	{
		// 1. pane kokku object list
		$ob = new object($arr["id"]);
		$formconfig = $ob->meta("formconfig");
		$ft_fields = $ob->prop("ftsearch_fields");
		$all_projects1 = new object_list(array(
			"parent" => array($formconfig["project1"]["rootnode"]),
			"class_id" => CL_PROJECT,
		));
		$all_projects2 = new object_list(array(
			"parent" => array($formconfig["project2"]["rootnode"]),
			"class_id" => CL_PROJECT,
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
		$search["class_id"] = array(CL_CALENDAR_EVENT);
		$start_tm = strtotime("today 0:00");
		$end_tm = strtotime("+30 days",$start_tm);
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
		$htmlc = get_instance("cfg/htmlclient",array("template" => "webform.tpl"));
		$htmlc->start_output();

		$formconfig = $ob->meta("formconfig");
		
		$do_search = false;
		$search = array();
		load_vcl("date_edit");
		$dt = new date_edit();
		$start_tm = $dt->get_timestamp($arr["start_date"]);
		$end_tm = $dt->get_timestamp($arr["end_date"]);



		$htmlc->add_property(array(
			"name" => "fulltext",
			"caption" => $formconfig["fulltext"]["caption"],
			"type" => "textbox",
			"value" => $arr["fulltext"],
		));

		$htmlc->add_property(array(
			"name" => "start_date",
			"caption" => $formconfig["start_date"]["caption"],
			"type" => "date_select",
			"value" => $start_tm != -1 ? $start_tm : time(),
		));
		
		$htmlc->add_property(array(
			"name" => "end_date",
			"caption" => $formconfig["end_date"]["caption"],
			"type" => "date_select",
			"value" => $end_tm != -1 ? $end_tm : time() + (30 * 86400),
		));
		
		$htmlc->add_property(array(
			"name" => "project1",
			"caption" => $formconfig["project1"]["caption"],
			"type" => "select",
			"options" => $this->_get_project_choices($formconfig["project1"]["rootnode"]),
			"value" => $arr["project1"],
		));
		
		$htmlc->add_property(array(
			"name" => "project2",
			"caption" => $formconfig["project2"]["caption"],
			"type" => "select",
			"options" => $this->_get_project_choices($formconfig["project2"]["rootnode"]),
			"value" => $arr["project2"],
		));
		
		$htmlc->add_property(array(
			"name" => "sbt",
			"caption" => t("Otsi"),
			"type" => "submit",
		));

		// perform the search only if a start_date has been selected - this means no search
		// when first viewing the page with search form
		if ($start_tm != -1 && $end_tm != -1)
		{
			$do_search = true;
		};

		if ($do_search)
		{
			$search["parent"] = array();
			$search["sort_by"] = "planner.start";
			$search["class_id"] = array(CL_CALENDAR_EVENT);
			$by_parent = array();
			$ft_fields = $ob->prop("ftsearch_fields");
			$all_projects1 = new object_list(array(
				"parent" => array($formconfig["project1"]["rootnode"]),
				"class_id" => CL_PROJECT,
			));
			$all_projects2 = new object_list(array(
				"parent" => array($formconfig["project2"]["rootnode"]),
				"class_id" => CL_PROJECT,
			));
			$par1 = $all_projects1->ids();
			$par2 = $all_projects2->ids();
			if ($start_tm != -1)
			{
				$search["CL_CALENDAR_EVENT.start1"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $start_tm, $end_tm);
			};
			if (is_oid($arr["project1"]))
			{
				$search["parent"][] = $arr["project1"];
			}
			else
			{
				$search["parent"] = $all_projects1->ids();

			};
			if (is_oid($arr["project2"]))
			{
				$search["parent"][] = $arr["project2"];
			}
			else
			{
				$search["parent"] = array_merge($search["parent"],$all_projects2->ids());
			};

			// kuidas ma nüüd need parentid kokku laon?
			if ($arr["fulltext"])
			{
				$ft_fields = $ob->meta("ftsearch_fields");
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
			//arr($search);
			if (sizeof($search["parent"]) != 0)
			{
				$ol = new object_list($search);
				/*$bl = new object_list(array(
					"brother_of" => $ol->ids(),
				));
				*/
				$this->read_template("search_results.tpl");
				$tabledef = $ob->meta("result_table");
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
				// või siis .. näidata ainult eventeid, mis on mõlema valitud parenti all?
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
					// iga sündmuse kohta ma pean vaatama kas ta on mind huvitava projekti all või mitte?
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
							"date" => date("d-m-Y",$res->prop("start1")),
						);
						$edata[$orig_id] = array_merge($edata[$orig_id],$res->properties());
					};
					$orig = $res->get_original();
					$ecount[$orig->id()]++;
				};
			};

			$blist = new object_list(array(
				"brother_of" => $origs,
			));
			$pr1 = $formconfig["project1"]["rootnode"];
			foreach($blist->arr() as $b_o)
			{
				$p2 = new object($b_o->parent());
				//$id = $b_o->id();
				if ($p2->parent() == $pr1)
				{
					$orig = $b_o->brother_of();
					if ($edata[$orig])
					{
						$edata[$orig]["project_selector"] = $p2->name();
					};
				};
			};

			//arr($edata);
			$res = "";

			foreach($edata as $eval)
			{
				//if (!empty($eval["parent1"]) && !empty($eval["parent2"]))
				//{
					$cdat = "";
					foreach($tabledef as $sname => $propdef)
					{
						if ($propdef["active"])
						{
							$val = $eval[$sname];
							if ($sname == "start1")
							{
								$val = date("d-m-Y",$val);
							};
							if ($sname == "end")
							{
								$val = date("d-m-Y",$val);
							};
							if ($sname == "name")
							{
								$val = html::href(array(
									"url" => aw_ini_get("baseurl") . "/" . $eval["event_id"],
									"caption" => $val,
								));
							};
							$this->vars(array("cell" => $val));
							//print "exporting $sname" . $eval[$sname];
							$cdat .= $this->parse("CELL");
						};
						$this->vars(array("CELL" => $cdat));
				};
				$res .= $this->parse("EVENT");
				//var_dump($res);
			};

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
				"class" => get_class($this),
				"section" => aw_global_get("section"),
				"action" => "search",
				"id" => $ob->id(),
			),
			"method" => "get",
			"form_handler" => aw_ini_get("baseurl") . "/" . aw_global_get("section"),
		));

		$html = $htmlc->get_result(array(
			"form_only" => 1
		));

		//arr($arr);

		return $html;

		// kuupäeva numbrid on lihtsalt selectid
	}

	function _get_project_choices($parent)
	{
		if (!is_oid($parent))
		{
			return array();
		};
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => CL_PROJECT,
		));
		return array("0" => t("kõik")) + $ol->names();

	}

	function __sort_props_by_ord($el1,$el2)
	{
		return (int)($el1["ord"] - $el2["ord"]);
	}
}
?>
