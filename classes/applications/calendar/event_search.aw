<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/event_search.aw,v 1.1 2004/11/03 14:22:01 duke Exp $
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

@groupinfo ftsearch caption="Vabateksti otsing"
@groupinfo ftform caption="Otsinguvorm seadistamine"
@groupinfo ftsearch caption="Otsinguvorm"

@reltype EVENT_CFGFORM value=1 clid=CL_CFGFORM
@caption Sündmuse vorm

@reltype EVENT_SOURCE value=2 clid=CL_PROJECT
@caption Sündmuste allikas

@reltype PROJECT_SELECTOR value=3 clid=CL_MENU
@caption Projekti valik

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
		$rv["k1"] = array(
			"name" => "k1",
			"type" => "textbox",
			"caption" => "Väli1",
		);
		$rv["k2"] = array(
			"name" => "k2",
			"type" => "textbox",
			"caption" => "Väli2",
		);
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
		$opts = array();
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
			"caption" => "Tüüp",
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));
		
		$t->define_field(array(
			"name" => "caption",
			"caption" => "Pealkiri",
		));
		
		$t->define_field(array(
			"name" => "data",
			"caption" => "Sisu",
		));

		$t->set_sortable(false);

		$t->define_data(array(
			"type" => "Tekstiotsing",
			"caption" => html::textbox(array(
				"name" => "fulltext[caption]",
				"value" => $formconfig["fulltext"]["caption"] ? $formconfig["fulltext"]["caption"] : "Tekstiotsing",
			)),
		));
		
		$t->define_data(array(
			"type" => "Alguskuupäev",
			"caption" => html::textbox(array(
				"name" => "start_date[caption]",
				"value" => $formconfig["start_date"]["caption"] ? $formconfig["start_date"]["caption"] : "Alguskuupäev",
			)),
		));
		
		$t->define_data(array(
			"type" => "Lõppkuupäev",
			"caption" => html::textbox(array(
				"name" => "end_date[caption]",
				"value" => $formconfig["end_date"]["caption"] ? $formconfig["end_date"]["caption"] : "Lõppkuupäev",
			)),
		));
		
		$prj_conns = $o->connections_from(array(
			"type" => "RELTYPE_PROJECT_SELECTOR",
		));

		$prj_opts = array("0" => "--vali--");

		foreach($prj_conns as $prj_conn)
		{
			$id = $prj_conn->prop("to");
			$name = $prj_conn->prop("to.name");
			$prj_opts[$id] = $name;
		};
		
		$t->define_data(array(
			"type" => "Projekt 1",
			"caption" => html::textbox(array(
				"name" => "project1[caption]",
				"value" => $formconfig["project1"]["caption"] ? $formconfig["project1"]["caption"] : "Projekt 1",
			)),
			"data" => html::select(array(
				"name" => "project1[rootnode]",
				"options" => $prj_opts,
				"value" => $formconfig["project1"]["rootnode"],
			)),
		));
		
		$t->define_data(array(
			"type" => "Projekt 2",
			"caption" => html::textbox(array(
				"name" => "project2[caption]",
				"value" => $formconfig["project2"]["caption"] ? $formconfig["project2"]["caption"] : "Projekt 2",
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

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
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
			"caption" => "Otsi",
			"type" => "submit",
		));

		// perform the search only if a start_date has been selected - this means no search
		// when first viewing the page with search form
		if ($start_tm != -1)
		{
			$do_search = true;
		};

		if ($do_search)
		{
			$search["parent"] = array();
			$search["sort_by"] = "planner.start";
			$search["class_id"] = CL_STAGING;
			$ft_fields = $ob->prop("ftsearch_fields");
			$all_projects = new object_list(array(
				"parent" => array($formconfig["project1"]["rootnode"],$formconfig["project2"]["rootnode"]),
				"class_id" => CL_PROJECT,
			));
			if ($start_tm != -1)
			{
				$search["CL_STAGING.start1"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start_tm);
			};
			if (is_oid($arr["project1"]))
			{
				$search["parent"][] = $arr["project1"];
			}
			else
			{
				$search["parent"] = $all_projects->ids();

			};
			if (is_oid($arr["project2"]))
			{
				$search["parent"][] = $arr["project2"];
			}
			else
			{
				$search["parent"] = $all_projects->ids();
			};

			// kuidas ma nüüd need parentid kokku laon?
			if ($arr["fulltext"])
			{
				$ft_fields = $ob->meta("ftsearch_fields");
				$or_parts = array();
				foreach($ft_fields as $ft_field)
				{
					$or_parts[$ft_field] = "%" . $arr["fulltext"] . "%";

				};
				$search[] = new object_list_filter(array(
                			"logic" => "OR",
                			"conditions" => $or_parts,
        			));
			};
			$clinf = aw_ini_get("classes");
			if (sizeof($search["parent"]) != 0)
			{
				$ol = new object_list($search);
				$this->read_template("search_results.tpl");
				$this->sub_merge = 1;
				foreach($ol->arr() as $res)
				{
					$pr = new object($res->parent());
					$this->vars(array(
						"event" => $res->name(),
						"place" => $pr->name(),
						"date" => date("d-m-Y",$res->prop("start1")),
					));
					$this->parse("EVENT");
				};
				$htmlc->add_property(array(
					"name" => "results",
					"type" => "text",
					"no_caption" => 1,
					"value" => $this->parse(),
				));
			};
		};

		$htmlc->finish_output(array("data" => array(
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
		return array("0" => "kõik") + $ol->names();

	}


}
?>
