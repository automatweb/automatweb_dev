<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cb_search.aw,v 1.2 2004/06/09 19:09:16 kristo Exp $
// cb_search.aw - Classbase otsing 
/*

@classinfo syslog_type=ST_CB_SEARCH relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property root_class type=select
@caption Juurklass

@property root_class_cf type=select
@caption Seadete vorm

@property next_connection type=select
@caption Where do you want to go from here?

@groupinfo props caption="Väljad"

@property choose_fields type=table group=props no_caption=1
@caption Vali omadused

@groupinfo mktbl caption="Koosta tulemuste tabel"
@default group=mktbl

@property sform_tbl type=table store=no no_caption=1 

@groupinfo search caption="Otsi" submit_method=get

@property search type=callback callback=callback_gen_search group=search
@caption Otsi

@property sbt type=submit group=search
@caption Otsi

@property results type=table group=search no_caption=1
@caption Tulemused

// step 1 - choose a class
// step 2 - choose a connection (might be optional)
// step 3 - choose another class (also optional)

*/

class cb_search extends class_base
{
	function cb_search()
	{
		$this->init(array(
			"clid" => CL_CB_SEARCH,
			"tpldir" => "applications/register/register_search"
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$o = $arr["obj_inst"];
		switch($prop["name"])
		{
			case "root_class":
				$this->make_class_list(&$prop);
				break;

			case "root_class_cf":
				if ($arr["obj_inst"]->prop("root_class"))
				{
					$ol = new object_list(array(
						"class_id" => CL_CFGFORM,
						"subclass" => $arr["obj_inst"]->prop("root_class")
					));
					$prop["options"] = array("" => "") + $ol->names();
				}
				break;

			case "next_connection":
				return PROP_IGNORE; // just for now
				$cfgx = get_instance("cfg/cfgutils");
				$tmp = $cfgx->load_class_properties(array(
					"clid" => $o->prop("root_class"),
				));
				$relx = new aw_array($cfgx->get_relinfo());
				$choices = array("" => "");
				$clinf = aw_ini_get("classes");
				foreach($relx->get() as $relkey => $relval)
				{
					if (is_numeric($relkey))
					{
						$choices[$relkey] = $relval["caption"] . " - " . $clinf[$relval["clid"][0]]["name"];
					};
				};
				$prop["options"] = $choices;
				break;

			case "choose_fields":
				$this->mk_prop_table($arr);
				break;

			case "results":
				$this->mk_result_table($arr);
				break;

			case "sform_tbl":
				$this->do_sform_tbl_tbl($arr);
				break;
		};
		return $retval;
	}

	function _init_prop_table(&$t)
	{
		$t->define_field(array(
			"name" => "classn",
			"caption" => "Klass",
		));	

		$t->define_field(array(
			"name" => "property",
			"caption" => "Omadus",
		));	
		
		$t->define_field(array(
			"name" => "in_form",
			"caption" => "Näita vormis",
			"align" => "center",
		));	

		$t->define_field(array(
			"name" => "caption",
			"caption" => "Tekst",
			"align" => "center",
		));	

		$t->define_field(array(
			"name" => "ord",
			"caption" => "J&auml;rjekord",
			"align" => "center",
		));	
	}

	function mk_prop_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$o = $arr["obj_inst"];
		$this->_init_prop_table($t);

		$form_dat = $o->meta("form_dat");

		$clist = aw_ini_get("classes");
		list($props, $clid, $relinfo) = $this->get_props_from_obj($o);
		$cll = $clist[$clid]["name"];
		foreach($props as $pn => $item)
		{
			if (!is_array($form_dat[$pn]))
			{
				$form_dat[$pn]["caption"] = $item["caption"];
			}
			$t->define_data(array(
				"classn" => $cll,
				"property" => $item["caption"],
				"in_form" => html::checkbox(array(
					"name" => "form_dat[$clid][$pn][visible]",
					"value" => 1,
					"checked" => ($form_dat[$clid][$pn]["visible"] == 1)
				)),
				"caption" => html::textbox(array(
					"name" => "form_dat[$clid][$pn][caption]",
					"value" => $form_dat[$clid][$pn]["caption"]
				)),
				"ord" => html::textbox(array(
					"name" => "form_dat[$clid][$pn][ord]",
					"size" => 5,
					"value" => $form_dat[$clid][$pn]["ord"]
				))
			));
		};

		if ($o->prop("next_connection"))
		{
			$relin = $relx[$o->prop("next_connection")];
			$tgt = $relin["clid"][0];
			
			$tmp = $cfgx->load_class_properties(array(
				"clid" => $tgt,
			));
			$cl2 = $clinf[$tgt]["name"];
			
			foreach($tmp as $item)
			{
				if ($item["type"] == "textbox" || $item["type"] == "textarea")
				{
					$t->define_data(array(
						"class" => $cl2,
						"property" => $item["caption"] . " / " . $item["name"],
						"xname" => $tgt . "/" . $item["name"],
					));
				};
			};
		}
	}

	function make_class_list($arr)
	{
		$cl = aw_ini_get("classes");
		$names = array();
		foreach($cl as $clid => $clinf)
		{
			if (!empty($clinf["name"]))
			{
				$names[$clid] = $clinf["name"];
			};
		};
		asort($names);
		$arr["options"] = array("0" => "--vali--") + $names;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$o = $arr["obj_inst"];
		switch($prop["name"])
		{
			case "choose_fields":
				$o->set_meta("form_dat",$arr["request"]["form_dat"]);
				break;

			case "sform_tbl":
				$arr["obj_inst"]->set_meta("tdata", $arr["request"]["tdata"]);
				break;
		}
		return $retval;
	}	

	function callback_gen_search($arr)
	{
		// now, get a list of properties in both classes and generate the search form
		// get a list of properties in both classes
		$this->_prepare_form_data($arr);
		$this->_prepare_search($arr);
		// would be nice to separate things by blah
		$res = array();
		foreach($this->in_form as $iname => $item)
		{
			$name = $item["name"];
			$item["name"] = "s[" . $item["clid"] . "][" . $item["name"] . "]";
			if ($this->search_data[$item["clid"]][$name])
			{
				$item["value"] = $this->search_data[$item["clid"]][$name];
			};
			$res[$iname] = $item;
		};
		return $res;
	}

	function _prepare_search($arr)
	{
		if ($this->search_prepared)
		{
			return false;
		}
		$this->search_data = array();
		$this->search_prepared = 1;
		foreach($this->in_form as $iname => $item)
		{
			if ($arr["request"]["s"][$item["clid"]][$item["name"]])
			{
				$val = $arr["request"]["s"][$item["clid"]][$item["name"]];
				$this->search_data[$item["clid"]][$item["name"]] = $val;
			};
		};

	}

	function mk_result_table($arr)
	{
		$this->_prepare_form_data($arr);
		$this->_prepare_search($arr);
		$t = &$arr["prop"]["vcl_inst"];
		foreach($this->in_results as $iname => $item)
		{
			$t->define_field(array(
				"name" => $iname,
				"caption" => $item["caption"],
			));
		}
		// now do the actual bloody search
		foreach($this->search_data as $clid => $data)
		{
			if (!empty($data))
			{
				$sdata = array();
				$sdata["class_id"] = $clid;
				foreach($data as $key => $val)
				{
					$sdata[$key] = "%" . $val . "%";
				};
				$olist = new object_list($sdata);
				for($o = $olist->begin(); !$olist->end(); $o = $olist->next())
				{
					$row = $o->properties();
					$row["view_link"] = html::href(array(
						"url" => $this->mk_my_orb("view", array("id" => $o->id()), $o->class_id()),
						"caption" => $this->in_results["view_link"]["caption"]
					));
					$row["change_link"] = html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()),
						"caption" => $this->in_results["change_link"]["caption"]
					));
					$t->define_data($row);
				};
			};
		};
	}
				
	function _prepare_form_data($arr)
	{
		if ($this->prepared)
		{
			return false;
		};
		$this->prepared = true;
		$o = $arr["obj_inst"];

		list($props, $clid, $relx) = $this->get_props_from_obj($o);

		$clinf = aw_ini_get("classes");
		$cl1 = $clinf[$clid]["name"];

		$this->form_dat = $o->meta("form_dat");
		$this->tdata = $o->meta("tdata");

		$this->in_form = array();
		$res = array();
		foreach($this->form_dat[$clid] as $pn => $pd)
		{
			if (!$pd["visible"])
			{
				continue;
			}

			$this->in_form[$pn] = $props[$pn];
			$this->in_form[$pn]["clid"] = $clid;
			$this->in_form[$pn]["caption"] = $pd["caption"];
		};

		$this->in_results = array();
		foreach($this->tdata as $pn => $pd)
		{
			if (!$pd["visible"] || !is_array($pd))
			{
				continue;
			}
			$this->in_results[$pn] = $props[$pn];
			$this->in_results[$pn]["clid"] = $clid;
			$this->in_results[$pn]["caption"] = $pd["caption"];
		}

		/*
		$relin = $relx[$o->prop("next_connection")];
		$tgt = $relin["clid"][0];
		
		$tmp = $cfgx->load_class_properties(array(
			"clid" => $tgt,
		));
		
		foreach($tmp as $iname => $item)
		{
			$xname = $tgt . "/" . $item["name"];
			if ($in_form[$xname])
			{
				$item["clid"] = $tgt;
				$this->in_form[$xname] = $item;
			};
			if ($in_results[$xname])
			{
				$this->in_results[$xname] = $item;
			};
		};


		*/
	}

	function get_props_from_obj($o, $addt = true)
	{
		// get a list of properties in both classes
		$cfgx = get_instance("cfg/cfgutils");
		$ret = $cfgx->load_class_properties(array(
			"clid" => $o->prop("root_class"),
		));

		if ($o->prop("root_class_cf"))
		{
			$class_i = get_instance($o->prop("root_class"));
			$tmp = $class_i->load_from_storage(array(
				"id" => $o->prop("root_class_cf")
			));

			$dat = array();
			foreach($tmp as $pn => $pd)
			{
				$dat[$pn] = $ret[$pn];
				$dat[$pn]["caption"] = $pd["caption"];
			}
			$ret = $dat;
		}

		if ($addt)
		{
			$ret["fts_search"] = array(
				"type" => "textbox",
				"caption" => "T&auml;istekstiotsing",
				"name" => "fts_search"
			);

			$ret["parent"] = array(
				"type" => "folder_select",
				"caption" => "Kataloog",
				"name" => "parent"
			);
		}

		if ($ret["name"])
		{
			$ret["name"]["type"] = "textbox";
			$ret["name"]["name"] = "name";
		}
		return array($ret, $o->prop("root_class"), $cfgx->get_relinfo());
	}

	function _init_sform_tbl_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "jrk",
			"caption" => "J&auml;rjekord",
			"sortable" => 1,
			"align" => "center",
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "el",
			"caption" => "Element",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "visible",
			"caption" => "Tabelis",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "sortable",
			"caption" => "Sorditav",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "defaultsort",
			"caption" => "Vaikimisi sort",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "view_col",
			"caption" => "Vaata tulp",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "u_name",
			"caption" => "Tulba pealkiri",
			"sortable" => 1,
			"align" => "center"
		));
	}

	function do_sform_tbl_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sform_tbl_tbl($t);

		$tdata = $arr["obj_inst"]->meta("tdata");

		// get register
		list($props, $clid, $relinfo) = $this->get_props_from_obj($arr["obj_inst"], false);
		$max_jrk = 0;
		$props["change_link"]["caption"] = "Muuda";
		$props["view_link"]["caption"] = "Vaata";
		$props["del_link"]["caption"] = "Kustuta";
		foreach($props as $pn => $pd)
		{
			if ($pn == "needs_translation" || $pn == "is_translated")
			{
				continue;
			}
			$defs = "";
			if ($tdata[$pn]["sortable"])
			{
				$defs = html::radiobutton(array(
					"name" => "tdata[__defaultsort]",
					"value" => $pn,
					"checked" => ($tdata["__defaultsort"] == $pn)
				));
			}
			$vc = "";
			if ($tdata[$pn]["visible"])
			{
				$vc = html::radiobutton(array(
					"name" => "tdata[__view_col]",
					"value" => $pn,
					"checked" => ($tdata["__view_col"] == $pn)
				));
			}
			$t->define_data(array(
				"jrk" => html::textbox(array(
					"size" => 5,
					"name" => "tdata[$pn][jrk]",
					"value" => $tdata[$pn]["jrk"]
				)),
				"el" => $pd["caption"],
				"visible" => html::checkbox(array(
					"name" => "tdata[$pn][visible]",
					"value" => 1,
					"checked" => ($tdata[$pn]["visible"] == 1)
				)),
				"sortable" => html::checkbox(array(
					"name" => "tdata[$pn][sortable]",
					"value" => 1,
					"checked" => ($tdata[$pn]["sortable"] == 1)
				)),
				"defaultsort" => $defs,
				"view_col" => $vc,
				"u_name" => html::textbox(array(
					"name" => "tdata[$pn][caption]",
					"value" => ($tdata[$pn]["caption"] == "" ? $pd["caption"] : $tdata[$pn]["caption"])
				)),
			));
		}

		$t->set_default_sortby("jrk");
		$t->sort_by();
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$request = array("s" => $GLOBALS["s"]);
		if ($GLOBALS["search_butt"])
		{
			$request["search_butt"] = $GLOBALS["search_butt"];
		}
		if ($GLOBALS["ft_page"])
		{
			$request["ft_page"] = $GLOBALS["ft_page"];
		}
		list($props, $clid, $relinfo) = $this->get_props_from_obj($ob);
		
		$props = $this->callback_gen_search(array(
			"obj_inst" => $ob,
			"request" => $request
		));

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		foreach($props as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->add_property(array(
			"name" => "search",
			"caption" => "Otsi",
			"type" => "submit",
			"store" => "no"
		));
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1
		));

		classload("vcl/table");
		$t = new aw_table(array(
			"layout" => "generic"
		));
		$this->mk_result_table(array(
			"prop" => array(
				"vcl_inst" => &$t
			),
			"obj_inst" => &$ob,
			"request" => $request,
		));
		$table = $t->draw();

		$this->read_template("show.tpl");
		$this->vars(array(
			"form" => $html,
			"section" => aw_global_get("section"),
			"table" => $table
		));
		return $this->parse();
	}
}
?>
