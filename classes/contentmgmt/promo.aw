<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/promo.aw,v 1.23 2002/12/24 14:22:00 kristo Exp $
// promo.aw - promokastid.

/*
	@classinfo trans=1
	@default group=general
	
	@property caption type=textbox table=objects field=meta method=serialize
	@caption Pealkiri

	@property tpl_lead type=select table=menu group=show
	@caption Template näitamiseks
	
	@property type type=select table=objects field=meta method=serialize
	@caption Kasti tüüp

	@property tpl_edit type=select table=menu group=show
	@caption Template dokumentide muutmiseks

	@property link type=textbox table=menu
	@caption Link
	
	@property link_caption type=textbox table=objects field=meta method=serialize
	@caption Lingi kirjeldus
	
	@default table=objects
	@default field=meta

	@property no_title type=checkbox ch_value=1 value=1 group=show method=serialize
	@caption Ilma pealkirjata

	@property groups type=select multiple=1 size=15 group=show method=serialize
	@caption Grupid, kellele kasti näidata

	@property use_fld_tpl type=checkbox ch_value=1 group=show method=serialize
	@caption Kasuta dokumendi asukoha templatet
	
	@property all_menus type=checkbox ch_value=1 value=1 group=menus method=serialize
	@caption Näita igal pool

	@property section type=table group=menus method=serialize store=no
	@caption Vali menüüd, mille all kasti näidata
	
	@property last_menus type=table group=menus method=serialize store=no
	@caption Vali menüüd, mille alt viimaseid dokumente võetakse

	@property ndocs type=textbox size=4 group=menus table=menu field=ndocs 
	@caption Mitu viimast dokumenti

	@property sort_by type=select table=objects field=meta method=serialize group=show
	@caption Dokumente j&auml;rjestatakse

	@property sort_ord type=select table=objects field=meta method=serialize group=show

	@classinfo relationmgr=yes
	@classinfo syslog_type=ST_PROMO

	@tableinfo menu index=id master_table=objects master_index=oid

	@groupinfo menus caption=Kaustad
	@groupinfo show caption=Näitamine
			
*/
define("RELTYPE_ASSIGNED_MENU",1);
define("RELTYPE_DOC_SOURCE",2);
class promo extends class_base
{
	function promo()
	{
		$this->init(array(
			"clid" => CL_PROMO,
			"tpldir" => "promo",
		));
		lc_load("definition");
		$this->lc_load("promo","lc_promo");
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_ASSIGNED_MENU || $args["reltype"] == RELTYPE_DOC_SOURCE)
		{
			return array(CL_MENU);
		}
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_ASSIGNED_MENU => "näita menüü juures",
			RELTYPE_DOC_SOURCE => "võta dokumente selle menüü alt",
		);
	}

	function get_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK; 
		switch($data["name"])
		{
			case "tpl_edit":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 0, "menu" => $args["obj"]["oid"]));
				break;

			case "tpl_lead":
				// kysime infot lyhikeste templatede kohta
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array(
					"type" => 1,
					"def" => aw_ini_get("promo.default_tpl"),
					"caption" => "Vali template"
				));
				break;
	
			case "type":
				$data["options"] = array(
					"0" => "Vasakul",
					"1" => "Paremal",
					"2" => "Üleval",
					"3" => "All",
					"scroll" => "Skrolliv",
				);
				break;

			case "groups":
				$u = get_instance("users");
				$data["options"] = $u->get_group_picker(array(
					"type" => array(GRP_REGULAR,GRP_DYNAMIC),
				));
				break;

			case "sort_by":
				$data['options'] = array(
					'' => "",
					'objects.jrk' => "J&auml;rjekorra j&auml;rgi",
					'objects.created' => "Loomise kuup&auml;eva j&auml;rgi",
					'objects.modified' => "Muutmise kuup&auml;eva j&auml;rgi",
					'documents.modified' => "Dokumenti kirjutatud kuup&auml;eva j&auml;rgi"
				);
				break;

			case "sort_ord":
				$data['options'] = array(
					'DESC' => "Suurem (uuem) enne",
					'ASC' => "V&auml;iksem (vanem) enne",
				);
				break;
	
			case "last_menus":
				$this->get_doc_sources($arr);
				break;

			case "section":
				$this->get_menus($arr);
				break;
		}
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "section":
				$arr["obj_inst"]->set_meta("section_include_submenus",$arr["form_data"]["include_submenus"]);
				break;

			case "ndocs":
				$arr["obj_inst"]->set_meta("as_name",$arr["form_data"]["as_name"]);
				$arr["obj_inst"]->set_meta("src_submenus",$this->make_keys($arr["form_data"]["src_submenus"]));
				break;
		};
		return $retval;

	}

	function get_menus($arr)
	{
		$obj = $arr["obj_inst"];
		$section_include_submenus = $obj->meta("section_include_submenus");

		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
		));
		$t->define_field(array(
			"name" => "check",
			"caption" => "k.a. alammenüüd",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));

		$conns = $obj->connections_from(array(
			"type" => RELTYPE_ASSIGNED_MENU
		));

		foreach($conns as $c)
		{
			$c_o = $c->to();

			$t->define_data(array(
				"id" => $c_o->id(),
				"name" => $c_o->path_str(array(
					"max_len" => 3
				)),
				"check" => html::checkbox(array(
					"name" => "include_submenus[".$c_o->id()."]",
					"value" => $c_o->id(),
					"checked" => $section_include_submenus[$c_o->id()],
				)),
			));
		}
	}

	function get_doc_sources($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "as_name",
			"caption" => "Pane pealkirjaks",
			"talign" => "center",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "src_submenus",
			"caption" => "k.a. alammen&uuml;&uuml;d",
			"talign" => "center",
			"align" => "center",
		));

		$obj = $arr["obj_inst"];

		$conns = $obj->connections_from(array(
			"type" => RELTYPE_DOC_SOURCE
		));

		$as_name = $obj->meta("as_name");
		$ssm = $obj->meta("src_submenus");

		foreach($conns as $c)
		{
			$c_o = $c->to();
			$t->define_data(array(
				"id" => $c_o->id(),
				"name" => $c_o->path_str(array(
					"max_len" => 3
				)),
				"as_name" => html::radiobutton(array(
					"name" => "as_name",
					"value" => $c_o->id(),
						"checked" => ($as_name == $c_o->id())
				)),
				"src_submenus" => html::checkbox(array(
					"name" => "src_submenus[]",
					"value" => $c_o->id(),
					"checked" => ($ssm[$c_o->id()] == $c_o->id())
				))
			));
		}
		$t->define_data(array(
			"id" => 0,
			"name" => "",
			"as_name" => html::radiobutton(array(
				"name" => "as_name",
				"value" => 0,
				"checked" => (!$as_name)
			))
		));
	}

	function callback_pre_edit($args = array())
	{
		$id = $args["obj_inst"]->id();
		$obj = $args["obj_inst"];

		// first check, whether the promo box was in the very old format (contained serialized data
		// in the comment field
		$check1 = aw_unserialize($obj->prop("comment"));
		$check2 = aw_unserialize($obj->meta("sss"));
		if (is_array($check1) || is_array($check2))
		{
			$convert_url = $this->mk_my_orb("promo_convert",array(),"converters");
			print "See objekt on vanas formaadis. Enne kui seda muuta saab, tuleb kõik süsteemis olevad promokastis uude formaati konvertida. <a href='$convert_url'>Kliki siia</a> konversiooni alustamiseks";
			exit;
		};
		
		// now, check, whether we have to convert the current contents of comment and sss to relation objects
		// we use a flag in object metainfo for that
		// converters->convert_promo_relations()

	}

	function callback_on_submit_relation_list($args = array())
	{
		// this is where we put data back into object metainfo, for backwards compatibility
		$obj =& obj($args["id"]);

		$oldaliases = $obj->connections_from(array(
			"class" => CL_MENU
		));
		
		$section = array();
		$last_menus = array();

		foreach($oldaliases as $alias)
		{
			if ($alias->prop("reltype") == RELTYPE_ASSIGNED_MENU)
			{
				$section[$alias->prop("to")] = $alias->prop("to");
			};
			
			if ($alias->prop("reltype") == RELTYPE_DOC_SOURCE)
			{
				$last_menus[$alias->prop("to")] = $alias->prop("to");
			};
		};

		// serializes makes empty array into array("0" => "0") and this is bad in this
		// case, so we work around it
		if (sizeof($last_menus) == 0)
		{
			$last_menus = "";
		};

		$obj->set_meta("section",$section);
		$obj->set_meta("last_menus",$last_menus);

		$obj->save();
	}

	function callback_on_addalias($args = array())
	{
		$obj_list = explode(",",$args["alias"]);
		$obj =&obj($args["id"]);

		if ($args["reltype"] == RELTYPE_ASSIGNED_MENU)
		{
			$var = "section";
		}
		else
		if ($args["reltype"] == RELTYPE_DOC_SOURCE)
		{
			$var = "last_menus";
		};

		$data = $obj->meta($var);

		foreach($obj_list as $val)
		{
			$data[$val] = $val;
		};

		$obj->set_meta($var,$data);
		$obj->save();
	}


	function parse_alias($args = array())
	{
		$alias = $args["alias"];
		$ob =& obj($alias["target"]);

		$this->read_template("default.tpl");

		$ss = get_instance("contentmgmt/site_show");
		$def = new aw_array($ss->get_default_document(array(
			"obj" => obj($alias["target"])
		)));
		if ($ob->prop("sort_by"))
		{
			$_ob = $ob->prop("sort_by")." ".$ob->prop("sort_ord");
		}
		if (($_ob != "") && (sizeof($def->get()) > 0))
		{
			$ol = new object_list(array(
				"class_id" => array(CL_DOCUMENT, CL_PERIODIC_SECTION, CL_BROTHER_DOCUMENT),
				"oid" => $def->get(),
				"sort_by" => $_ob,
			));
			
			
			$def = new aw_array($ol->ids());
		}

		$ndocs = $ob->prop("ndocs");
		if ($ndocs)
		{
			$def = new aw_array(array_slice($def->get(), 0, $ndocs));
		}

		$content = "";
		$doc = get_instance("document");

		$parms = array(
			"leadonly" => 1,
			"showlead" => 1,
			"boldlead" => $thid->cfg["boldlead"],
			"no_strip_lead" => 1,
		);

		if (!$ob->meta('use_fld_tpl'))
		{
			$mgr = get_instance("templatemgr");
			$parms["tpl"] = $mgr->get_template_file_by_id(array(
				"id" => $ob->prop("tpl_lead")
			));
		}
		else
		{
			$parms["tpl_auto"] = 1;
		}

		foreach($def->get() as $key => $val)
		{
			$_parms = $parms;
			$_parms["docid"] = $val;
			$content .= $doc->gen_preview($_parms);
		}

		if ($ob->meta('as_name') && $ob->meta("caption") == "")
		{
			if ($this->object_exists($ob->meta("as_name")))
			{
				$as_n_o = obj($ob->meta("as_name"));
				$ob->set_meta('caption',$as_n_o->name());
			}
		}

		$align= array("k" => "align=\"center\"", "p" => "align=\"right\"" , "v" => "align=\"left\"" ,"" => "");
		$this->vars(array(
			"title" => $ob->meta("caption"),
			"content" => $content,
			"align" => $align[$args["matches"][4]],
			"link" => $ob->prop("link")
		));

		if (!$ob->meta('no_title'))
		{
			$this->vars(array(
				"SHOW_TITLE" => $this->parse("SHOW_TITLE")
			));
		}
		else
		{
			$this->vars(array(
				"SHOW_TITLE" => ""
			));
		}
		return $this->parse();
	}

	////
	// !this must set the content for subtemplates in main.tpl
	// params
	//	inst - instance to set variables to
	//	content_for - array of templates to get content for
	function on_get_subtemplate_content($arr)
	{
		$inst =& $arr["inst"];

		$doc = get_instance("document");
		# reset period, or we don't see contents of promo boxes under periodic menus:
		$doc->set_period(0);

		if (aw_ini_get("menuedit.promo_lead_only"))
		{
			$leadonly = 1;
		}
		else
		{
			$leadonly = -1;
		}

		$filter = array();
		$filter["status"] = STAT_ACTIVE;
		$filter["class_id"] = CL_PROMO;
		$filter["sort_by"] = "objects.jrk";

		if (aw_ini_get("menuedit.lang_menus"))
		{
			$filter["lang_id"] = aw_global_get("lang_id");
		}

		$list = new object_list($filter);

		$tplmgr = get_instance("templatemgr");
		$promos = array();
		$gidlist = aw_global_get("gidlist");
		for($o =& $list->begin(); !$list->end(); $o =& $list->next())
		{
			if (!$o->prop("tpl_lead"))
			{
				$tpl_filename = aw_ini_get("promo.default_tpl");
				if (!$tpl_filename)
				{
					continue;
				}
			}
			else
			{
				// find the file for the template by id. sucks. we should join the template table
				// on the menu template I guess
				$tpl_filename = $tplmgr->get_template_file_by_id(array("id" => $o->prop("tpl_lead")));
			}

			$found = false;

			if (!is_array($o->meta("groups")) || count($o->meta("groups")) < 1)
			{
				$found = true;
			}
			else
			{
				foreach($o->meta("groups") as $gid)
				{
					if (isset($gidlist[$gid]) && $gidlist[$gid] == $gid)
					{
						$found = true;
					}
				}
			}

			$doc->doc_count = 0;

			$show_promo = false;
			
			$msec = $o->meta("section");

			if ($o->meta("all_menus"))
			{
				$show_promo = true;
			}
			else
			if (isset($msec[$inst->sel_section_real]) && $msec[$inst->sel_section_real])
			{
				$show_promo = true;
			}
			else
			if (is_array($o->meta("section_include_submenus")))
			{
				$pa = array(aw_ini_get("frontpage"), aw_ini_get("rootmenu"));
				foreach($inst->path as $p_o)
				{
					$pa[] = $p_o->id();
				}

				// here we need to check, whether any of the parent menus for
				// this menu has been assigned a promo box and has been told
				// that it should be shown in all submenus as well
				$sis = new aw_array($o->meta("section_include_submenus"));
				$intersect = array_intersect($pa,$sis->get());
				if (sizeof($intersect) > 0)
				{
					$show_promo = true;
				}
			}

			if ($found == false)
			{
				$show_promo = false;
			};
			
			// this line decides, whether we should show this promo box here or not.
			// now, how do I figure out whether the promo box is actually in my path?
			if ($show_promo)
			{
				// visible. so show it
				// get list of documents in this promo box
				$pr_c = "";
				$docid = $inst->get_default_document(array(
					"obj" => $o
				));

				if (!is_array($docid))
				{
					$docid = array($docid);
				}

				$d_cnt = 0;
				foreach($docid as $d)
				{
					if (($d_cnt % 2)  == 1)
					{
						if (file_exists(aw_ini_get("tpldir")."/automatweb/documents/".$tpl_filename."2"))
						{
							$tpl_filename .= "2";
						}
					}
					$cont = $doc->gen_preview(array(
						"docid" => $d,
						"tpl" => $tpl_filename,
						"leadonly" => $leadonly,
						"section" => $inst->sel_section,
						"strip_img" => false,
						"showlead" => 1,
						"boldlead" => $this->cfg["boldlead"],
						"no_strip_lead" => 1,
						"no_acl_checks" => aw_ini_get("menuedit.no_view_acl_checks"),
						"vars" => array("doc_ord_num" => $d_cnt+1),
					));
					$pr_c .= str_replace("\r","",str_replace("\n","",$cont));
					$d_cnt++;
				}

				$inst->vars(array(
					"comment" => $o->comment(),
					"title" => $o->name(), 
					"content" => $pr_c,
					"url" => $o->prop("link"),
					"link_caption" => $o->meta("link_caption"),
					"promo_doc_count" => (int)$d_cnt,
				));

				// which promo to use? we need to know this to use
				// the correct SHOW_TITLE subtemplate
				$templates = array(
					"scroll" => "SCROLL_PROMO",
					"0" => "LEFT_PROMO",
					"1" => "RIGHT_PROMO",
					"2" => "UP_PROMO",
					"3" => "DOWN_PROMO",
				);
	
				$use_tpl = $templates[$o->meta("type")];
				if (!$use_tpl)
				{
					$use_tpl = "LEFT_PROMO";
				};

				if ($o->meta("no_title") != 1)
				{
					$inst->vars(array(
						"SHOW_TITLE" => $inst->parse($use_tpl . ".SHOW_TITLE")
					));
				}
				else
				{
					$inst->vars(array(
						"SHOW_TITLE" => ""
					));
				}
				$ap = "";
				if ($o->prop("link") != "")
				{
					$ap = "_LINKED";
				}
				if ($this->used_promo_tpls[$use_tpl] != 1)
				{
					$ap.="_BEGIN";
					$this->used_promo_tpls[$use_tpl] = 1;
				}

				if ($inst->is_template($use_tpl . $ap))
				{
					$promos[$use_tpl] .= $inst->parse($use_tpl . $ap);
					$inst->vars(array($use_tpl . $ap => ""));
				}
				else
				{
					$promos[$use_tpl] .= $inst->parse($use_tpl);
					$inst->vars(array($use_tpl => ""));
				};
				// nil the variables that were imported for promo boxes
				// if we dont do that we can get unwanted copys of promo boxes
				// in places we dont want them
				$inst->vars(array("title" => "", "content" => "","url" => ""));
			}
		};

		$inst->vars($promos);
	}
}
?>
