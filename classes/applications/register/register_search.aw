<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/register/register_search.aw,v 1.4 2004/05/27 08:42:38 kristo Exp $
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
				"u_name" => html::textbox(array(
					"name" => "fdata[$pn][caption]",
					"value" => $fdata[$pn]["caption"]
				)),
			));
		}

		$pn = $this->fts_name;
		$t->define_data(array(
			"jrk" => html::textbox(array(
				"size" => 5,
				"name" => "fdata[$pn][jrk]",
				"value" => $fdata[$pn]["jrk"]
			)),
			"el" => "T&auml;istekstiotsing",
			"searchable" => html::checkbox(array(
				"name" => "fdata[$pn][searchable]",
				"value" => 1,
				"checked" => ($fdata[$pn]["searchable"] == 1)
			)),
			"is_num" => "",
			"u_name" => html::textbox(array(
				"name" => "fdata[$pn][caption]",
				"value" => ($fdata[$pn]["caption"] == "" ? "T&auml;istekstiotsing" : $fdata[$pn]["caption"])
			)),
		));

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
		foreach($props as $pn => $pd)
		{
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
				"u_name" => html::textbox(array(
					"name" => "tdata[$pn][caption]",
					"value" => ($tdata[$pn]["caption"] == "" ? $pd["caption"] : $tdata[$pn]["caption"])
				)),
			));
		}

		$pn = "change_link";
		$t->define_data(array(
			"jrk" => html::textbox(array(
				"size" => 5,
				"name" => "tdata[$pn][jrk]",
				"value" => $tdata[$pn]["jrk"]
			)),
			"el" => "Muuda",
			"visible" => html::checkbox(array(
				"name" => "tdata[$pn][visible]",
				"value" => 1,
				"checked" => ($tdata[$pn]["visible"] == 1)
			)),
			"sortable" => "",
			"u_name" => html::textbox(array(
				"name" => "tdata[$pn][caption]",
				"value" => ($tdata[$pn]["caption"] == "" ? "Muuda" : $tdata[$pn]["caption"])
			)),
		));

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
		$props =  $this->get_sform_properties($ob, $request);
		
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

		classload("vcl/table");
		$t = new aw_table(array(
			"layout" => "generic"
		));
		$this->do_search_res_tbl(array(
			"prop" => array(
				"vcl_inst" => &$t
			),
			"obj_inst" => &$ob,
			"request" => $request,
		));
		$table = $t->draw();
		
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
		return $this->parse();
	}

	function get_props_from_reg($reg)
	{
		$properties = array();
		$awa = new aw_array($reg->prop("data_cfgform"));
		foreach($awa->get() as $cfid)
		{
			$cff = obj($cfid);
			$class_id = $cff->prop("ctype");
			$class_i = get_instance($class_id);
			$tmp = $class_i->load_from_storage(array(
				"id" => $cff->id()
			));

			foreach($tmp as $k => $v)
			{
				if ($v["name"] != "needs_translation" && $v["name"] != "is_translated")
				{
					$properties[$k] = $v;
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
		}

		if ($fdata[$this->fts_name]["searchable"] == 1)
		{
			$tmp[$this->fts_name] = array(
				"name" => $this->fts_name,
				"type" => "textbox",
				"caption" => $fdata[$this->fts_name]["caption"],
				"value" => $request["rsf"][$this->fts_name],
				"zee_shaa_helper" => 1
			);
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

		return $xp;
	}

	function __proptbl_srt($a, $b)
	{
		if ($a["jrk"] == $b["jrk"])
		{
			return 0;
		}
		return $a["jrk"] > $b["jrk"];
	}

	function _init_search_res_tbl(&$t, $o)
	{
		$tdata = $o->meta("tdata");

		// get register
		$reg = obj($o->prop("register"));
		$props = $this->get_props_from_reg($reg);
		$this->__tdata = $tdata;
		uksort($props, array(&$this, "__proptbl_srt"));

		foreach($props as $pn => $pd)
		{
			if ($tdata[$pn]["visible"])
			{
				$t->define_field(array(
					"name" => $pn,
					"caption" => $tdata[$pn]["caption"],
					"sortable" => $tdata[$pn]["sortable"]
				));
			}
		}

		$pn = "change_link";
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

	function get_search_results($o, $request)
	{
		$reg = obj($o->prop("register"));
		$props = $this->get_props_from_reg($reg);

		$filter = array(
			"class_id" => CL_REGISTER_DATA,
			"register_id" => $reg->id()
		);

		foreach($props as $pn => $pd)
		{
			if ($request["rsf"][$pn] != "")
			{
				if (is_array($request["rsf"][$pn]))
				{
					$filter[$pn] = $request["rsf"][$pn];
				}
				else
				{
					$filter[$pn] = "%".$request["rsf"][$pn]."%";
				}
			}
		}

		// if fulltext search
		if ($request["rsf"][$this->fts_name] != "")
		{
			$tmp = array();
			foreach($props as $pn => $pd)
			{
				if ($pn == "status" || $pn == "register_id")
				{
					continue;
				}
				$tmp[$pn] = "%".$request["rsf"][$this->fts_name]."%";
			}

			$filter[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => $tmp
			));
		}


		$ret = new object_list();
		$ol_cnt = new object_list();
		if (count($filter) > 2)
		{
			$ol_cnt = new object_list($filter);
			if (($ppg = $o->prop("per_page")))
			{
				$filter["limit"] = ($request["ft_page"] * $ppg).",".$ppg;
			}
			$ret = new object_list($filter);
		}

		return array($ret, $ol_cnt);
	}

	function do_search_res_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_search_res_tbl($t, $arr["obj_inst"]);

		list($ol, $ol_cnt) = $this->get_search_results($arr["obj_inst"], $arr["request"]);
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$data = array();
			foreach($t->rowdefs as $k => $v)
			{
				if ($v["name"] == "change_link")
				{
					$data[$v["name"]] = html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()),
						"caption" => "Muuda"
					));
				}
				else
				{
					$data[$v["name"]] = $o->prop($v["name"]);
				}
			}			

			$t->define_data($data);
		}

		$t->set_default_sortby("name");
		$t->sort_by();
		if ($arr["obj_inst"]->prop("per_page"))
		{
			$t->pageselector_string = $t->draw_text_pageselector(array(
				"d_row_cnt" => $ol_cnt->count(),
				"records_per_page" => $arr["obj_inst"]->prop("per_page")
			));
		}
	}
}
?>
