<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/register/register_search.aw,v 1.22 2005/02/14 15:12:50 kristo Exp $
// register_search.aw - Registri otsing 
/*

@classinfo syslog_type=ST_REGISTER_SEARCH relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general
@default field=meta 
@default method=serialize

@property register type=relpicker reltype=RELTYPE_REGISTER 
@caption Register, millest otsida

@property per_page type=textbox size=5
@caption Mitu kirjet lehel

@property show_all_in_empty_search type=checkbox ch_value=1
@caption T&uuml;hi otsing n&auml;itab k&otilde;iki

@property show_only_act type=checkbox ch_value=1
@caption N&auml;ita ainult aktiivseid objekte

@property show_all_right_away type=checkbox ch_value=1
@caption Otsingus n&auml;idatakse ilma otsimata k&otilde;iki

@property notfound_text type=textarea rows=5 cols=40
@caption Mida n&auml;idatakse kui midagi ei leita (%s on otsing)

@property show_date type=checkbox ch_value=1
@caption Tulemuste all on kuup&auml;ev

/////////
@groupinfo mkfrm caption="Koosta otsinguvorm"
@default group=mkfrm

@property sform_frm type=table store=no no_caption=1

@property butt_text type=textbox 
@caption Otsi nupu tekst

////////
@groupinfo mktbl caption="Koosta tulemuste tabel"
@default group=mktbl

@property sform_tbl type=table store=no no_caption=1 


////////
@groupinfo search caption="Otsi" submit_method=get
@default group=search

@property search type=callback store=no callback=callback_get_sform no_caption=1
@property search_res type=table store=no no_caption=1

@reltype REGISTER value=1 clid=CL_REGISTER
@caption register millest otsida


*/

class register_search extends class_base
{
	function register_search()
	{
		$this->init(array(
			"tpldir" => "applications/register/register_search",
			"clid" => CL_REGISTER_SEARCH
		));

		$this->fts_name = "fulltext_search";
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "sform_frm":
				if (!$arr["obj_inst"]->prop("register"))
				{
					return PROP_IGNORE;
				}
				$this->do_sform_frm_tbl($arr);
				break;

			case "sform_tbl":
				if (!$arr["obj_inst"]->prop("register"))
				{
					return PROP_IGNORE;
				}
				$this->do_sform_tbl_tbl($arr);
				break;

			case "search_res":
				if (!$arr["obj_inst"]->prop("register"))
				{
					return PROP_IGNORE;
				}
				$this->do_search_res_tbl($arr);
				break;
		};

		$this->request = $arr["request"];
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "sform_frm":
				$arr["obj_inst"]->set_meta("fdata", $arr["request"]["fdata"]);
				break;

			case "sform_tbl":
				$arr["obj_inst"]->set_meta("tdata", $arr["request"]["tdata"]);
				break;
		}
		return $retval;
	}	

	function _init_sform_frm_tbl(&$t)
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
			"name" => "searchable",
			"caption" => "Otsitav",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "is_num",
			"caption" => "Numbrite vahemiku otsing",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "is_chooser",
			"caption" => "Valik olemasolevatest",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "u_name",
			"caption" => "Elemendi tekst",
			"sortable" => 1,
			"align" => "center"
		));
	}

	function do_sform_frm_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sform_frm_tbl($t);

		$fdata = $arr["obj_inst"]->meta("fdata");

		// get register
		$reg = obj($arr["obj_inst"]->prop("register"));
		$props = $this->get_props_from_reg($reg);

		$props[$this->fts_name]["caption"] = "T&auml;istekstiotsing";
		foreach($props as $pn => $pd)
		{
			if (!is_array($fdata[$pn]) || $fdata[$pn]["caption"] == "")
			{
				$fdata[$pn] = array(
					"caption" => $pd["caption"]
				);
			}
			$t->define_data(array(
				"jrk" => html::textbox(array(
					"size" => 5,
					"name" => "fdata[$pn][jrk]",
					"value" => $fdata[$pn]["jrk"]
				)),
				"el" => $pd["caption"],
				"searchable" => html::checkbox(array(
					"name" => "fdata[$pn][searchable]",
					"value" => 1,
					"checked" => ($fdata[$pn]["searchable"] == 1)
				)),
				"is_num" => html::checkbox(array(
					"name" => "fdata[$pn][is_num]",
					"value" => 1,
					"checked" => ($fdata[$pn]["is_num"] == 1)
				)),
				"is_chooser" => html::checkbox(array(
					"name" => "fdata[$pn][is_chooser]",
					"value" => 1,
					"checked" => ($fdata[$pn]["is_chooser"] == 1)
				)),
				"u_name" => html::textbox(array(
					"name" => "fdata[$pn][caption]",
					"value" => $fdata[$pn]["caption"]
				)),
			));
		}

		$t->set_default_sortby("jrk");
		$t->sort_by();
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
		$reg = obj($arr["obj_inst"]->prop("register"));
		$props = $this->get_props_from_reg($reg);
		$max_jrk = 0;
		$props["change_link"]["caption"] = "Muuda";
		$props["view_link"]["caption"] = "Vaata";
		$props["del_link"]["caption"] = "Kustuta";
		foreach($props as $pn => $pd)
		{
			$defs = "";
			if (true || $tdata[$pn]["sortable"])
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

	////
	// !shows the search
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$request = array("rsf" => $GLOBALS["rsf"]);
		if ($GLOBALS["search_butt"])
		{
			$request["search_butt"] = $GLOBALS["search_butt"];
		}

		if (!$request["search_butt"] && is_array($request["rsf"]) && count($request["rsf"]))
		{
			$request["search_butt"] = 1;
		}
		if ($GLOBALS["ft_page"])
		{
			$request["ft_page"] = $GLOBALS["ft_page"];
		}

		enter_function("register_search::show::form");

		enter_function("register_search::show::form::gsp");
		$props =  $this->get_sform_properties($ob, $request);
		exit_function("register_search::show::form::gsp");

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		foreach($props as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1
		));
		exit_function("register_search::show::form");

		classload("vcl/table");
		$t = new aw_table(array(
			"layout" => "generic"
		));
		enter_function("register_search::show::dsrt");
		$this->do_search_res_tbl(array(
			"prop" => array(
				"vcl_inst" => &$t
			),
			"obj_inst" => &$ob,
			"request" => $request,
		));
		exit_function("register_search::show::dsrt");

		enter_function("register_search::show::final");
		if (count($t->data) < 1 && $request["search_butt"] != "" && $ob->prop("notfound_text") != "")
		{
			$table = nl2br(sprintf($ob->prop("notfound_text"), $request["rsf"][$this->fts_name]));
		}
		else
		if ($request["search_butt"] != "")
		{
			$table = $t->draw();
		}
		else
		{
			$table = "";
		}

		if ($ob->prop("show_date") && $request["search_butt"] != "")
		{
			$table .= "<br>".date("d.m.Y H:i:s");
		}
		
		if ($arr["no_form"])
		{
			return $html."<br>".$table;
		}

		$this->read_template("show.tpl");
		$this->vars(array(
			"form" => $html,
			"section" => aw_global_get("section"),
			"table" => $table
		));
		exit_function("register_search::show::final");
		return $this->parse();
	}

	function get_props_from_reg($reg)
	{
		$properties = array();
		$awa = new aw_array($reg->prop("data_cfgform"));
		foreach($awa->get() as $cfid)
		{
			if (!is_oid($cfid) || !$this->can("view", $cfid))
			{
				continue;
			}
			$cff = obj($cfid);
			$class_id = $cff->prop("ctype");

			$cfgu = get_instance("cfg/cfgutils");
			$f_props = $cfgu->load_properties(array(
				"clid" => $class_id
			));

			$class_i = get_instance($class_id);
			$tmp = $class_i->load_from_storage(array(
				"id" => $cff->id()
			));

			foreach(safe_array($tmp) as $k => $v)
			{
				if ($v["name"] != "needs_translation" && $v["name"] != "is_translated")
				{
					$properties[$k] = $v;
					$properties[$k]["type"] = $f_props[$k]["type"];
				}
			}
		}
		return $properties;
	}

	function get_clid_from_reg($reg)
	{
		$awa = new aw_array($reg->prop("data_cfgform"));
		foreach($awa->get() as $cfid)
		{
			if (!is_oid($cfid) || !$this->can("view", $cfid))
			{
				continue;
			}
			$cff = obj($cfid);
			$class_id = $cff->prop("ctype");
			return $class_id;
		}
	}

	function callback_get_sform($arr)
	{
		return $this->get_sform_properties($arr["obj_inst"], $arr["request"]);
	}

	function get_sform_properties($o, $request)
	{
		$reg = obj($o->prop("register"));
		$props = $this->get_props_from_reg($reg);
		$clid = $this->get_clid_from_reg($reg);
		$fdata = $o->meta("fdata");

		// load props for entire class, cause from cfgform we don't get all dat
		$cfgu = get_instance("cfg/cfgutils");
		$f_props = $cfgu->load_properties(array(
			"clid" => $clid
		));

		$tmp = array();
		foreach($props as $pn => $pd)
		{
			if (!$fdata[$pn]["searchable"])
			{
				continue;
			}

			$tmp[$pn] = $pd + $f_props[$pn];
			$tmp[$pn]["value"] = $request["rsf"][$pn];
			$tmp[$pn]["caption"] = $fdata[$pn]["caption"];

			// if is_chooser , make list of all possible options and insert into options.
			if ($fdata[$pn]["is_chooser"] == 1)
			{
				$this->mod_chooser_prop($tmp, $pn, $reg);
			}
		}

		if ($fdata[$this->fts_name]["searchable"] == 1)
		{
			$tmp[$this->fts_name] = array(
				"name" => $this->fts_name,
				"type" => "textbox",
				"caption" => $fdata[$this->fts_name]["caption"],
				"value" => $request["rsf"][$this->fts_name]
			);
			
			if (aw_ini_get("site_id") == 125)
			{
				$tmp[$this->fts_name]["zee_shaa_helper"] = 1;
			}
		}

		$i = get_instance($clid);
		$xp = $i->parse_properties(array(
			"properties" => $tmp,
			"name_prefix" => "rsf"
		));

		$xp["search_butt"] = array(
			"name" => "search_butt",
			"caption" => $o->prop("butt_text"),
			"type" => "submit",
			"store" => "no",
		);
		header("loll");

		return $xp;
	}

	function __proptbl_srt($pa, $pb)
	{
		$a = $this->__tdata[$pa];
		$b = $this->__tdata[$pb];

		if ($a["jrk"] == $b["jrk"])
		{
			return 0;
		}
		return $a["jrk"] > $b["jrk"];
	}

	function _init_search_res_tbl(&$t, $o)
	{
		$tdata = $o->meta("tdata");

		$cfgu = get_instance("cfg/cfgutils");
		$f_props = $cfgu->load_properties(array(
			"clid" => CL_REGISTER_DATA
		));	

		// get register
		$reg = obj($o->prop("register"));
		$props = $this->get_props_from_reg($reg);
		$this->__tdata = $tdata;
		uksort($props, array(&$this, "__proptbl_srt"));

		foreach($props as $pn => $pd)
		{
			if ($tdata[$pn]["visible"])
			{
				$fd = array(
					"name" => $pn,
					"caption" => $tdata[$pn]["caption"],
					"sortable" => $tdata[$pn]["sortable"]
				);
				if ($f_props[$pn]["type"] == "date_select")
				{
					$fd["type"] = "time";
					$fd["format"] = "Y-m-d";
					$fd["numeric"] = 1;
				}
				$t->define_field($fd);
			}
		}

		$pnn = array("change_link", "view_link", "del_link");
		foreach($pnn as $pn)
		{
			if ($tdata[$pn]["visible"])
			{
				$t->define_field(array(
					"name" => $pn,
					"caption" => $tdata[$pn]["caption"],
					"sortable" => $tdata[$pn]["sortable"],
					"align" => "center"
				));
			}
		}
	}

	function get_search_results($o, $request)
	{
		// return immediately if nothing is to be done
		if (empty($request["search_butt"]) && !$o->prop("show_all_right_away"))
		{
			return array(new object_list(), new object_list());
		}
		
		enter_function("register_search::show::dsrt::gsr::init");
		$reg = obj($o->prop("register"));
		$reg_i = $reg->instance();

		$props = $this->get_props_from_reg($reg);

		$filter = array(
			"class_id" => CL_REGISTER_DATA,
			"status" => $o->prop("show_only_act") ? STAT_ACTIVE : array(STAT_ACTIVE, STAT_NOTACTIVE),
			new object_list_filter(array(
				"logic" => "OR", 
				"conditions" => array(
					"register_id" => $reg->id(),
					"parent" => $reg_i->_get_reg_folders($reg)
				)
			))
			
		);
		exit_function("register_search::show::dsrt::gsr::init");

		enter_function("register_search::show::dsrt::gsr::loop");
		foreach($props as $pn => $pd)
		{
			if ($request["rsf"][$pn] != "")
			{
				if (is_array($request["rsf"][$pn]))
				{
					$filter[$pn] = $request["rsf"][$pn];
				}
				else
				if ($pd["type"] == "classificator")
				{
					$filter[$pn] = $request["rsf"][$pn];
				}
				else
				{
					$filter[$pn] = "%".$request["rsf"][$pn]."%";
				}
			}
		}

		$cfgu = get_instance("cfg/cfgutils");
		$f_props = $cfgu->load_properties(array(
			"clid" => CL_REGISTER_DATA
		));	

		// if fulltext search
		if ($request["rsf"][$this->fts_name] != "")
		{
			$tmp = array();
			foreach($props as $pn => $pd)
			{
				if ($pn == "status" || $pn == "register_id" || $f_props[$pn]["store"] == "no" || $f_props[$pn]["field"] == "meta"
|| $f_props[$pn]["type"] == "submit" || !isset($f_props[$pn]))
				{
					continue;
				}

				if ($f_props[$pn]["type"] == "classificator")
				{
					$tmp["CL_REGISTER_DATA.".$f_props[$pn]["reltype"].".name"] = "%".$request["rsf"][$this->fts_name]."%";
				}
				else
				{
					$tmp[$pn] = "%".$request["rsf"][$this->fts_name]."%";
				}
			}
			$filter[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => $tmp
			));
		}
		exit_function("register_search::show::dsrt::gsr::loop");

		enter_function("register_search::show::dsrt::gsr::finit");
		$tdata = $o->meta("tdata");

		if ($GLOBALS["sortby"] != "")
		{
			$sp = $f_props[$GLOBALS["sortby"]];
			if ($sp)
			{
				$filter["sort_by"] = $sp["table"].".".$sp["field"]." ".$GLOBALS["sort_order"];
			}
		}
		else
		if ($tdata["__defaultsort"] != "")
		{
			$sp = $f_props[$tdata["__defaultsort"]];
			if ($sp)
			{
				$filter["sort_by"] = $sp["table"].".".$sp["field"]." DESC ";
			}
		}
		else
		{
			$filter["sort_by"] = "objects.name ASC ";
		}

		if (!empty($request["search_butt"]) || $o->prop("show_all_right_away") == 1)
		{
			$ol_cnt = new object_list($filter);
			if (($ppg = $o->prop("per_page")))
			{
				$filter["limit"] = ($request["ft_page"] * $ppg).",".$ppg;
			}
			$ret = new object_list($filter);
		}
		else
		{
			if ($o->prop("show_all_in_empty_search") && !empty($request["search_butt"]))
			{
				$ol_cnt = new object_list($filter);
				if (($ppg = $o->prop("per_page")))
				{
					$filter["limit"] = ($request["ft_page"] * $ppg).",".$ppg;
				}
				$ret = new object_list($filter);
			}
			else
			{
				$ret = new object_list();
				$ol_cnt = new object_list();
			}
		}

		exit_function("register_search::show::dsrt::gsr::finit");
		return array($ret, $ol_cnt);
	}

	function do_search_res_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_search_res_tbl($t, $arr["obj_inst"]);

		$tdata = $arr["obj_inst"]->meta("tdata");

		$can_change = false;
		$can_delete = false;

		enter_function("register_search::show::dsrt::gsr");
		list($ol, $ol_cnt) = $this->get_search_results($arr["obj_inst"], $arr["request"]);
		exit_function("register_search::show::dsrt::gsr");

		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$data = array();
			foreach($t->rowdefs as $k => $v)
			{
				if ($v["name"] == "change_link")
				{
					if ($this->can("edit", $o->id()))
					{
						$data[$v["name"]] = html::href(array(
							"url" => $this->mk_my_orb("change", array("section" => aw_global_get("section"), "id" => $o->id()), $o->class_id()),
							"caption" => "Muuda"
						));
						$can_change = true;
					}
					else
					{
						$data[$v["name"]] = "";
					}
				}
				else
				if ($v["name"] == "view_link")
				{
					$data[$v["name"]] = html::href(array(
						"url" => $this->mk_my_orb("view", array("id" => $o->id(), "section" => aw_global_get("section")), $o->class_id()),
						"caption" => "Vaata"
					));
				}
				else
				if ($v["name"] == "del_link")
				{
					if ($this->can("delete", $o->id()))
					{
						$delurl = $this->mk_my_orb("delete", array("id" => $o->id(), "return_url" => urlencode(aw_global_get("REQUEST_URI"))));
						$data[$v["name"]] = html::href(array(
							"url" => "#",
							"onClick" => "if(confirm(\"Kustutada objekt?\")){window.location=\"$delurl\";};",
							"caption" => "Kustuta"
						));
						$can_delete = true;
					}
				}
				else
				{
					$data[$v["name"]] = $o->prop_str($v["name"]);
					if ($tdata["__view_col"] == $v["name"])
					{
						$data[$v["name"]] = html::href(array(
							"url" => $this->mk_my_orb("view", array("section" => aw_global_get("section"), "id" => $o->id()), $o->class_id()),
							"caption" => $data[$v["name"]]
						));
					}
				}
			}			

			$t->define_data($data);
		}

		if (!$can_change)
		{
			$t->remove_field("change_link");
		}

		if (!$can_delete)
		{
			$t->remove_field("del_link");
		}

		if ($tdata["__defaultsort"] != "")
		{
			$t->set_default_sortby($tdata["__defaultsort"]);	
			$t->set_default_sorder("desc");
		}
		else
		{
			$t->set_default_sortby("name");
			$t->set_default_sorder("asc");
		}
		$t->sort_by();
		if ($arr["obj_inst"]->prop("per_page"))
		{
			$t->pageselector_string = $t->draw_text_pageselector(array(
				"d_row_cnt" => $ol_cnt->count(),
				"records_per_page" => $arr["obj_inst"]->prop("per_page")
			));
		}
	}

	function mod_chooser_prop(&$props, $pn, $reg )
	{
		enter_function("register_search::show::form::mod_chooser_p");
		// since storage can't do this yet, we gots to do sql here :(
		$p =& $props[$pn];
		$opts = array("" => "");
		if ($p["table"] != "" && $p["field"] != "")
		{
			// also must filter by register data folder
			$reg_i = $reg->instance();
			$flds = $reg_i->_get_reg_folders($reg);

			// this is an expensive query, so cache the results
			$c = get_instance("cache");
			$cfn = "register_search_mod_chooser_p_".$pn;

			if (!($res = $c->file_get_ts($cfn, $c->get_objlastmod())))
			{
				$this->db_query("SELECT distinct($p[field]) as val FROM $p[table] 
					LEFT JOIN objects ON objects.oid = ".$p["table"].".aw_id WHERE objects.parent IN(".join(",",$flds).")");
				while ($row = $this->db_next())
				{
					$opts[$row["val"]] = $row["val"];
				}
				$c->file_set($cfn, aw_serialize($opts));
			}
			else
			{
				$opts = aw_unserialize($res);
			}
		}

		$p["type"] = "select";
		$p["options"] = $opts;
		exit_function("register_search::show::form::mod_chooser_p");
	}

	/**

		@attrib name=delete

		@param id required type=int acl=view;delete
		@param return_url required
	**/
	function delete($arr)
	{
		$o = obj($arr["id"]);
		$o->delete();

		header("Location: ".$arr["return_url"]);
		die();
	}

}
?>
