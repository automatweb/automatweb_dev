<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/promo.aw,v 1.57 2005/03/08 13:24:44 kristo Exp $
// promo.aw - promokastid.

/*
	@classinfo trans=1
	@default group=general
	
	@property caption type=textbox table=objects field=meta method=serialize trans=1
	@caption Pealkiri

	@property image type=relpicker reltype=RELTYPE_IMAGE table=objects field=meta method=serialize 
	@caption Pilt

	@property tpl_lead type=select table=menu group=show
	@caption Template näitamiseks
	
	@property type type=select table=objects field=meta method=serialize trans=1
	@caption Kasti tüüp

	@property tpl_edit type=select table=menu group=show
	@caption Template dokumentide muutmiseks

	@property link type=textbox table=menu
	@caption Link
	
	@property link_caption type=textbox table=objects field=meta method=serialize
	@caption Lingi kirjeldus
	
	@property is_dyn type=checkbox ch_value=1 table=objects field=meta method=serialize
	@caption Sisu ei cacheta
	
	@property trans_all_langs type=checkbox ch_value=1 table=objects field=meta method=serialize
	@caption Sisu n&auml;idatakse k&otilde;ikides keeltes
	
	@property content_all_langs type=checkbox ch_value=1 table=objects field=meta method=serialize
	@caption Sisu n&auml;idatakse k&otilde;ikides keeltes
	
	@property promo_tpl type=select table=objects field=meta method=serialize
	@caption Template (dokumendi sees)

	@default table=objects
	@default field=meta

	@property no_title type=checkbox ch_value=1  group=show method=serialize
	@caption Ilma pealkirjata

	@property groups type=select multiple=1 size=15 group=show method=serialize
	@caption Grupid, kellele kasti näidata

	@property use_fld_tpl type=checkbox ch_value=1 group=show method=serialize
	@caption Kasuta dokumendi asukoha templatet
	
	@property all_menus type=checkbox ch_value=1 group=menus method=serialize
	@caption Näita igal pool

	@property section type=table group=menus method=serialize store=no
	@caption Vali menüüd, mille all kasti näidata
	
	@property last_menus type=table group=menus method=serialize store=no
	@caption Vali menüüd, mille alt viimaseid dokumente võetakse

	@property ndocs type=textbox size=4 group=menus table=menu field=ndocs 
	@caption Mitu viimast dokumenti

	@property start_ndocs type=textbox size=4 group=menus table=objects field=meta method=serialize
	@caption Mitu algusest &auml;ra j&auml;tta

	@property sort_by type=select table=objects field=meta method=serialize group=show
	@caption Dokumente j&auml;rjestatakse

	@property sort_ord type=select table=objects field=meta method=serialize group=show

	@property show_inact type=checkbox ch_value=1 table=objects field=meta method=serialize group=show
	@caption N&auml;ita mitteaktiivseid dokumente tekstina

	@classinfo relationmgr=yes
	@classinfo syslog_type=ST_PROMO

	@tableinfo menu index=id master_table=objects master_index=oid

	@groupinfo menus caption=Kaustad
	@groupinfo show caption=Näitamine

	@reltype ASSIGNED_MENU value=1 clid=CL_MENU
	@caption näita menüü juures

	@reltype DOC_SOURCE value=2 clid=CL_MENU
	@caption võta dokumente selle menüü alt

	@reltype IMAGE value=3 clid=CL_IMAGE
	@caption pilt
	
	@reltype NO_SHOW_MENU value=4 clid=CL_MENU
	@caption &auml;ra näita menüü juures
			
*/
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


	function get_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK; 
		switch($prop["name"])
		{
			case "promo_tpl":
				$tm = get_instance("templatemgr");
				$prop["options"] = $tm->template_picker(array(
					"folder" => "promo/doctemplates"
				));
				break;

			case "tpl_edit":
				$tplmgr = get_instance("templatemgr");
				$prop["options"] = $tplmgr->get_template_list(array(
					"type" => 0,
					"menu" => $arr["obj_inst"]->id(),
				));
				break;

			case "tpl_lead":
				// kysime infot lyhikeste templatede kohta
				$tplmgr = get_instance("templatemgr");
				$prop["options"] = $tplmgr->get_template_list(array(
					"type" => 1,
					"def" => aw_ini_get("promo.default_tpl"),
					"caption" => t("Vali template")
				));
				break;
	
			case "type":
				$pa = aw_ini_get("promo.areas");
				if (is_array($pa) && count($pa) > 0)
				{
					$opts = array();
					foreach($pa as $pid => $pd)
					{
						$opts[$pid] = $pd["name"];
					}
				}
				else
				{
					$opts = array(
						"0" => t("Vasakul"),
						"1" => t("Paremal"),
						"2" => t("Üleval"),
						"3" => t("All"),
						"scroll" => t("Skrolliv"),
					);
				}
				$prop["options"] = $opts;
				break;

			case "groups":
				$u = get_instance("users");
				$prop["options"] = $u->get_group_picker(array(
					"type" => array(GRP_REGULAR,GRP_DYNAMIC),
				));
				break;

			case "sort_by":
				$prop['options'] = array(
					'' => "",
					'objects.jrk' => t("J&auml;rjekorra j&auml;rgi"),
					'objects.created' => t("Loomise kuup&auml;eva j&auml;rgi"),
					'objects.modified' => t("Muutmise kuup&auml;eva j&auml;rgi"),
					'documents.modified' => t("Dokumenti kirjutatud kuup&auml;eva j&auml;rgi"),
					'objects.name' => t("Objekti nime j&auml;rgi"),
					'planner.start' => t("Kalendris valitud aja j&auml;rgi"),
				);
				break;

			case "sort_ord":
				$prop['options'] = array(
					'DESC' => t("Suurem (uuem) enne"),
					'ASC' => t("V&auml;iksem (vanem) enne"),
				);
				break;
	
			case "last_menus":
				$this->get_doc_sources($arr);
				break;

			case "section":
				$this->get_menus($arr);
				break;

			case "trans_all_langs":
				if (!aw_ini_get("config.object_translation"))
				{
					return PROP_IGNORE;
				}
				break;

			case "content_all_langs":
				if (aw_ini_get("config.object_translation"))
				{
					return PROP_IGNORE;
				}
				break;
		}
		return $retval;
	}

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "section":
				$arr["obj_inst"]->set_meta("section_include_submenus",$arr["request"]["include_submenus"]);
				break;

			case "ndocs":
				$arr["obj_inst"]->set_meta("as_name",$arr["request"]["as_name"]);
				$arr["obj_inst"]->set_meta("src_submenus",$this->make_keys($arr["request"]["src_submenus"]));
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
			"caption" => t("ID"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"talign" => "center",
		));
		$t->define_field(array(
			"name" => "check",
			"caption" => t("k.a. alammenüüd"),
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
			"caption" => t("ID"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"talign" => "center",
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "as_name",
			"caption" => t("Pane pealkirjaks"),
			"talign" => "center",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "src_submenus",
			"caption" => t("k.a. alammen&uuml;&uuml;d"),
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
			$c_id = $c_o->id();
			$t->define_data(array(
				"id" => $c_id,
				"name" => $c_o->path_str(array(
					"max_len" => 3
				)),
				"as_name" => html::radiobutton(array(
					"name" => "as_name",
					"value" => $c_id,
						"checked" => ($as_name == $c_id)
				)),
				"src_submenus" => html::checkbox(array(
					"name" => "src_submenus[]",
					"value" => $c_id,
					"checked" => ($ssm[$c_id] == $c_id)
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

		// if there is another promo dok template, then use that
		if ($ob->prop("promo_tpl") != "")
		{
			$this->read_template("doctemplates/".$ob->prop("promo_tpl"));
		}
		else
		{
			$this->read_template("default.tpl");
		}

		$ss = get_instance("contentmgmt/site_show");
		if ($ob->prop("trans_all_langs"))
		{
			obj_set_opt("no_auto_translation", 1);
			obj_set_opt("no_cache", 1);
		}
		$def = new aw_array($ss->get_default_document(array(
			"obj" => obj($alias["target"])
		)));
		if ($ob->prop("trans_all_langs"))
		{
			obj_set_opt("no_auto_translation", 0);
			obj_set_opt("no_cache", 0);
		}

		if ($ob->prop("sort_by"))
		{
			$_ob = $ob->prop("sort_by")." ".$ob->prop("sort_ord");
			if ($ob->prop("sort_by") == "documents.modified")
			{
				$_ob .= " ,objects.created DESC";
			};
		}
		if (($_ob != "") && (sizeof($def->get()) > 0))
		{
			$ol = new object_list(array(
				"class_id" => array(CL_DOCUMENT, CL_PERIODIC_SECTION, CL_BROTHER_DOCUMENT),
				"oid" => $def->get(),
				"sort_by" => $_ob,
				"lang_id" => array()
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
			"boldlead" => $this->cfg["boldlead"],
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
	
		
		$_numdocs = count($def->get());
		$_curdoc = 1;
		foreach($def->get() as $key => $val)
		{
			$_parms = $parms;
			$_parms["docid"] = $val;
			$_parms["not_last_in_list"] = ($_curdoc < $_numdocs);
			$_parms["no_link_if_not_act"] = 1;
			$content .= $doc->gen_preview($_parms);
			$_curdoc ++;
		}

		if ($this->is_template("PREV_LINK"))
		{
			$this->do_prev_next_links($def->get(), $this);
		}

		$as_name = $ob->meta("as_name");

		if ($as_name && $ob->meta("caption") == "")
		{
			if ($this->can("view",$as_name))
			{
				$as_n_o = obj($as_name);
				$ob->set_meta('caption',$as_n_o->name());
			}
		}

		$image = "";
		$image_url = "";
		$image_id = $ob->prop("image");
		if ($image_id)
		{
			$i = get_instance("image");
			$image_url = $i->get_url_by_id($image_id);
			$image = $i->make_img_tag($image_url);
		}

		$align= array("k" => "align=\"center\"", "p" => "align=\"right\"" , "v" => "align=\"left\"" ,"" => "");
		$this->vars(array(
			"title" => $ob->meta("caption"),
			"content" => $content,
			"align" => $align[$args["matches"][4]],
			"link" => $ob->prop("link"),
			"link_caption" => $ob->prop("link_caption"),
			"image" => $image,
			"image_url" => $image_url,
			"image_or_title" => ($image == "" ? $ob->meta("caption") : $image),
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

		// ADD_ITEM subtemplate should contain the link to add a new document
		// to a container and will be made visible if there is an logged in user
		// this check of course sucks, acl should be used instead --duke
		if (aw_global_get("uid") != "" && $this->is_template("ADD_ITEM"))
		{
			$conns = $ob->connections_from(array(
				"type" => "RELTYPE_DOC_SOURCE",
			));
			if (sizeof($conns) > 0)
			{
				$first = reset($conns);
				$this->vars(array(
					"add_item_url" => $this->mk_my_orb("new",array(
						"parent" => $first->prop("to"),
						"period" => aw_global_get("current_period"),
					),"doc",true),
				));
				$this->vars(array(
					"ADD_ITEM" => $this->parse("ADD_ITEM"),
				));
			};
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

		/*if (aw_ini_get("menuedit.lang_menus"))
		{
			$filter["lang_id"] = aw_global_get("lang_id");
		}*/
		$filter["lang_id"] = array();

		$list = new object_list($filter);

		$tplmgr = get_instance("templatemgr");
		$promos = array();
		$gidlist = aw_global_get("gidlist");
		$lang_id = aw_global_get("lang_id");
		$rootmenu = aw_ini_get("rootmenu");
		$default_tpl_filename = aw_ini_get("promo.default_tpl");
		$tpldir = aw_ini_get("tpldir");
		$no_acl_checks = aw_ini_get("menuedit.no_view_acl_checks");
		$promo_areas = aw_ini_get("promo.areas");
		foreach($list->arr() as $o)
		{
			if ($o->lang_id() != $lang_id && !$o->prop("content_all_langs"))
			{
				continue;
			}

			if (!$o->prop("tpl_lead"))
			{
				$tpl_filename = $default_tpl_filename;
				if (!$default_tpl_filename)
				{
					continue;
				}
			}
			else
			{
				// find the file for the template by id. sucks. we should join the template table
				// on the menu template I guess
				$tpl_filename = $tplmgr->get_template_file_by_id(array(
					"id" => $o->prop("tpl_lead"),
				));
			}
			
			$promo_link = $o->prop("link");

			$found = false;

			$groups = $o->meta("groups");
			if (!is_array($groups) || count($groups) < 1)
			{
				$found = true;
			}
			else
			{
				foreach($groups as $gid)
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

			$section_include_submenus = $o->meta("section_include_submenus");

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
			if (is_array($section_include_submenus))
			{
				$pa = array($rootmenu);
				foreach($inst->path as $p_o)
				{
					$pa[] = $p_o->id();
				}

				// here we need to check, whether any of the parent menus for
				// this menu has been assigned a promo box and has been told
				// that it should be shown in all submenus as well
				$sis = new aw_array($section_include_submenus);
				$intersect = array_intersect($pa,$sis->get());
				if (sizeof($intersect) > 0)
				{
					$show_promo = true;
				}
			}

			// do ignore menus
			foreach($o->connections_from(array("type" => "RELTYPE_NO_SHOW_MENU")) as $ignore_menu)
			{
				if ($inst->sel_section_real == $ignore_menu->prop("to"))
				{
					$show_promo = false;
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
				if ($_GET["PROMO_DBG"] == 1)
				{
					echo "showing promo ".$o->name()." (".$o->id().")  type = ".$o->meta("type")."<br>";
				}
				enter_function("show_promo::".$o->name());
				// visible. so show it
				// get list of documents in this promo box
				$pr_c = "";
				global $awt;
				$awt->start("def-doc");
				if ($o->prop("trans_all_langs"))
				{
					obj_set_opt("no_auto_translation", 1);
					obj_set_opt("no_cache", 1);
				}

				$docid = $inst->get_default_document(array(
					"obj" => $o,
					"all_langs" => true
				));


				if ($o->prop("trans_all_langs"))
				{
					obj_set_opt("no_auto_translation", 0);
					obj_set_opt("no_cache", 0);
				}
				$awt->stop("def-doc");

				if (!is_array($docid))
				{
					$docid = array($docid);
				}


				$d_cnt = 0;
				$d_total = count($docid);
				foreach($docid as $d)
				{
					if (($d_cnt % 2)  == 1)
					{
						if (file_exists($tpldir."/automatweb/documents/".$tpl_filename."2"))
						{
							$tpl_filename .= "2";
						}
					}
					enter_function("promo-prev");
					$cont = $doc->gen_preview(array(
						"docid" => $d,
						"tpl" => $tpl_filename,
						"leadonly" => $leadonly,
						"section" => $inst->sel_section,
						"strip_img" => false,
						"showlead" => 1,
						"boldlead" => $this->cfg["boldlead"],
						"no_strip_lead" => 1,
						"no_acl_checks" => $no_acl_checks,
						"vars" => array("doc_ord_num" => $d_cnt+1),
						"not_last_in_list" => (($d_cnt+1) < $d_total)
					));
					exit_function("promo-prev");
					$pr_c .= str_replace("\r","",str_replace("\n","",$cont));
					$d_cnt++;
				}

				if (true || $inst->is_template("PREV_LINK"))
				{
					$this->do_prev_next_links($docid, $inst);
				}

				$image = "";
				$image_url = "";
				if ($o->prop("image"))
				{
					$i = get_instance("image");
					$image_url = $i->get_url_by_id($o->prop("image"));
					$image = $i->make_img_tag($image_url);
				}

				$inst->vars(array(
					"comment" => $o->comment(),
					"title" => $o->name(), 
					"caption" => $o->meta("caption"),
					"content" => $pr_c,
					"url" => $promo_link,
					"link" => $promo_link,
					"link_caption" => $o->meta("link_caption"),
					"promo_doc_count" => (int)$d_cnt,
					"image" => $image, 
					"image_url" => $image_url,
					"image_or_title" => ($image == "" ? $o->meta("caption") : $image),
				));

				// which promo to use? we need to know this to use
				// the correct SHOW_TITLE subtemplate
				if (is_array($promo_areas) && count($promo_areas) > 0)
				{
					$templates = array();
					foreach($promo_areas as $pid => $pd)
					{
						$templates[$pid] = $pd["def"]."_PROMO";
					}
				}
				else
				{
					$templates = array(
						"scroll" => "SCROLL_PROMO",
						"0" => "LEFT_PROMO",
						"1" => "RIGHT_PROMO",
						"2" => "UP_PROMO",
						"3" => "DOWN_PROMO",
					);
				}
	
				$use_tpl = $templates[$o->meta("type")];
				if (!$use_tpl)
				{
					$use_tpl = "LEFT_PROMO";
				};

				$hlc = "";
				if ($o->meta("link_caption") != "")
				{
					$hlc = $inst->parse($use_tpl.".HAS_LINK_CAPTION");
				}
				$inst->vars(array(
					"HAS_LINK_CAPTION" => $hlc
				));

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
				if ($promo_link != "")
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
				exit_function("show_promo::".$o->name());
			}
		};

		$inst->vars($promos);
	}

	function do_prev_next_links($docs, &$tpl)
	{
		$s_prev = $s_next = "";

		$cur_doc = obj(aw_global_get("section"));
		if ($cur_doc->class_id() == CL_DOCUMENT)
		{
			$fp_prev = false;
			$fp_next = false;
			$prev = false;
			$get_next = false;
			foreach($docs as $d)
			{
				if ($get_next)
				{
					$fp_next = $d;
					$get_next = false;
				}
				if ($d == $cur_doc->id())
				{
					$fp_prev = $prev;
					$get_next = true;
				}
				$prev = $d;
			}

			if ($fp_prev)
			{
				$tpl->vars(array(
					"prev_link" => obj_link($fp_prev)
				));
				$s_prev = $tpl->parse("PREV_LINK");
			}

			if ($fp_next)
			{
				$tpl->vars(array(
					"next_link" => obj_link($fp_next)
				));
				$s_next = $tpl->parse("NEXT_LINK");
			}
		}

		$tpl->vars(array(
			"PREV_LINK" => $s_prev,
			"NEXT_LINK" => $s_next
		));
	}
}
?>
