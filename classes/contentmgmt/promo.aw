<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/promo.aw,v 1.76 2005/09/07 10:42:36 kristo Exp $
// promo.aw - promokastid.

/* content documents for promo boxes are handled thusly:

- when a document is saved, promo boxes are scanned to see if any of them should display the just-saved document
  if so, then the document is added to the list in meta[content_documents], else it is removed

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE,CL_DOCUMENT, on_save_document)


- when a promo box is saved, the list of documents for it's display is regenerated

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE,CL_PROMO, on_save_promo)

- when a document is deleted, the list of documents needs to be regenerated

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE,CL_DOCUMENT, on_delete_document)

*/

/*
	@classinfo trans=1

	@groupinfo general_sub caption="Üldine" parent=general

		@property name type=textbox rel=1 trans=1 table=objects group=general_sub
		@caption Nimi
		@comment Objekti nimi

		@property comment type=textbox table=objects group=general_sub
		@caption Kommentaar
		@comment Vabas vormis tekst objekti kohta

		@property status type=status trans=1 default=1 table=objects group=general_sub
		@caption Aktiivne
		@comment Kas objekt on aktiivne
	
		@property caption type=textbox table=objects field=meta method=serialize trans=1 group=general_sub
		@caption Pealkiri

		@property type type=select table=objects field=meta method=serialize trans=1 group=general_sub
		@caption Kasti tüüp

		@property link type=textbox table=menu group=general_sub
		@caption Link

		@property link_caption type=textbox table=objects field=meta method=serialize group=general_sub
		@caption Lingi kirjeldus

	@groupinfo users caption="Kasutajad" parent=general

		@property groups type=select multiple=1 size=15 group=users method=serialize table=objects field=meta
		@caption Grupid, kellele kasti näidata

@groupinfo show caption=Näitamine

	@groupinfo show_sub caption="Näitamine" parent=show

		@property no_title type=checkbox ch_value=1  group=show_sub method=serialize table=objects field=meta
		@caption Ilma pealkirjata

		@property show_inact type=checkbox ch_value=1 table=objects field=meta method=serialize group=show_sub
		@caption N&auml;ita mitteaktiivseid dokumente tekstina

		@property auto_period type=checkbox ch_value=1  group=show_sub method=serialize table=objects field=meta
		@caption Perioodilise, automaatselt vahetuva sisuga

	@groupinfo container_locations caption="Konteineri näitamise asukohad" parent=show

		@property all_menus type=checkbox ch_value=1 group=container_locations method=serialize table=objects field=meta
		@caption Näita igal pool

		@property not_in_search type=checkbox ch_value=1 table=objects field=meta method=serialize group=container_locations
		@caption &Auml;ra n&auml;ita otsingu tulemuste lehel

		@property trans_all_langs type=checkbox ch_value=1 table=objects field=meta method=serialize group=container_locations
		@caption Sisu n&auml;idatakse k&otilde;ikides keeltes

		@property content_all_langs type=checkbox ch_value=1 table=objects field=meta method=serialize group=container_locations
		@caption Sisu n&auml;idatakse k&otilde;ikides keeltes

		@property section type=table group=container_locations method=serialize store=no
		@caption Vali men&uuml;&uuml;d, mille all kasti n&auml;idata

		@property section_noshow type=table group=container_locations method=serialize store=no
		@caption Vali men&uuml;&uuml;d, mille all kasti EI n&auml;idata

	@groupinfo doc_ord caption="Dokumentide järjestamine" parent=show

		@property sort_by type=select table=objects field=meta method=serialize group=doc_ord
		@caption Dokumente j&auml;rjestatakse

		@property sort_ord type=select table=objects field=meta method=serialize group=doc_ord

@groupinfo look caption="Välimus"

	@groupinfo look_sub caption="Välimus" parent=look

		@property image type=relpicker reltype=RELTYPE_IMAGE table=objects field=meta method=serialize group=look_sub
		@caption Pilt

	@groupinfo templates caption="Kujunduspõhjad" parent=look

		@property tpl_lead type=select table=menu group=templates
		@caption Template näitamiseks


		@property use_fld_tpl type=checkbox ch_value=1 group=templates method=serialize table=objects field=meta
		@caption Kasuta dokumendi asukoha templatet

		@property promo_tpl type=select table=objects field=meta method=serialize group=templates
		@caption Template (dokumendi sees)

@groupinfo menus caption="Sisu seaded"

	@property last_menus type=table group=menus method=serialize store=no
	@caption Vali menüüd, mille alt viimaseid dokumente võetakse

	@property ndocs type=textbox size=4 group=menus table=menu field=ndocs 
	@caption Mitu viimast dokumenti

	@property start_ndocs type=textbox size=4 group=menus table=objects field=meta method=serialize
	@caption Mitu algusest &auml;ra j&auml;tta

	@property is_dyn type=checkbox ch_value=1 table=objects field=meta method=serialize group=menus
	@caption Sisu ei cacheta






	@classinfo relationmgr=yes
	@classinfo syslog_type=ST_PROMO

	@tableinfo menu index=id master_table=objects master_index=oid


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

			case "section_noshow":
				$this->get_menus_noshow($arr);
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

			case "section_noshow":
				$arr["obj_inst"]->set_meta("section_no_include_submenus",$arr["request"]["include_no_submenus"]);
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
			"type" => "RELTYPE_ASSIGNED_MENU"
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

	function get_menus_noshow($arr)
	{
		$obj = $arr["obj_inst"];
		$section_no_include_submenus = $obj->meta("section_no_include_submenus");

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
			"type" => "RELTYPE_NO_SHOW_MENU"
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
					"name" => "include_no_submenus[".$c_o->id()."]",
					"value" => $c_o->id(),
					"checked" => $section_no_include_submenus[$c_o->id()],
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
			"type" => "RELTYPE_DOC_SOURCE"
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
		$doc = get_instance(CL_DOCUMENT);

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
			$i = get_instance(CL_IMAGE);
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

		if (aw_ini_get("document.use_new_parser"))
		{
			$doc = get_instance("doc_display");
		}
		else
		{
			$doc = get_instance(CL_DOCUMENT);
		}

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
			$ign_subs = $o->meta("section_no_include_submenus");
			foreach($o->connections_from(array("type" => "RELTYPE_NO_SHOW_MENU")) as $ignore_menu)
			{
				$ignore_menu_to = $ignore_menu->prop("to");
				if ($inst->sel_section_real == $ignore_menu_to)
				{
					$show_promo = false;
				}
				else
				if (isset($ign_subs[$ignore_menu_to]))
				{
					// get path for current menu and check if ignored menu is above it
					foreach($inst->path_ids as $_path_id)
					{
						if ($_path_id == $ignore_menu_to)
						{
							$show_promo = false;
						}
					}
				}
			}

			if ($found == false)
			{
				$show_promo = false;
			};

			if ($o->meta("not_in_search") == 1 && $_GET["class"] == "site_search_content")
			{
				$show_promo = false;
			}
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

				if ($o->meta("version") == 2 && ($this->cfg["version"] == 2))
				{
					$docid = array_values(safe_array($o->meta("content_documents")));
					foreach($docid as $_idx => $_did)
					{
						if (!is_oid($_did))
						{
							unset($docid[$_idx]);
						}
					}
					if (count($docid))
					{
						// prefetch docs in list so we get them in one query
						$ol = new object_list(array("oid" => $docid));
						$tt = $ol->arr();
						$nids = $this->make_keys($ol->ids());
						$tmp = array();	
						foreach($docid as $_id)
						{
							if (isset($nids[$_id]))
							{
								$tmp[] = $_id;
							}
						}
						$docid = $tmp;
					}
				}
				else
				{
					// get_default_document prefetches docs by itself so no need to do list here
					$docid = $inst->get_default_document(array(
						"obj" => $o,
						"all_langs" => true
					));
				}

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
					$pr_c .= $cont;
					// X marks the spot
					//$pr_c .= str_replace("\r","",str_replace("\n","",$cont));
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
					$i = get_instance(CL_IMAGE);
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

				$inst->vars(array(
					$use_tpl."_image" => $image,
					$use_tpl."_image_url" => $image_url,
					$use_tpl."_image_or_title" => ($image == "" ? $o->meta("caption") : $image),
				));

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
				$inst->vars(array("title" => 
					"", "content" => "","url" => ""));
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

	function on_save_document($arr)
	{
		$o = obj($arr["oid"]);
		
		// figure out if this document is to be shown in any promo in the system
		// to do that
		// make a list of all promo boxes
		$ol = new object_list(array(
			"class_id" => CL_PROMO,
			"lang_id" => array(),
			"site_id" => array()
		));

		$path = $o->path();

		foreach($ol->arr() as $box)
		{
			$add_to_list = false;

			// for each box, check the folders where it gets documents and if this document's parent is one of them, 
			$fld = $this->_get_folders_for_box($box);
			$is_in_promo = false;
			foreach($fld as $f => $subs)
			{
				if ($f == $o->parent() || ($subs && $this->_is_in_path($path, $f)))
				{
					$is_in_promo = true;
					break;
				}
			}

			if ($is_in_promo)
			{
				// check if it has ndocs > 0
				if ($box->prop("ndocs"))
				{
					// if so, check the sorting order and compare the current document to the current list
					// if it belongs in the list, add it to the list
					// how do we do that? 
					// well, make an list of the documents in the current list
					// add the new document to it
					// and give the id's and sort by and length to an object_list and let the database sort it all out
					$ids = safe_array($box->meta("content_documents"));
					$ids[$o->id()] = $o->id();

					$limit = $box->prop("ndocs");

					$filt = array(
						"oid" => $ids,
						"limit" => $limit,
						"status" => ($box->prop("show_inact") ? array(STAT_ACTIVE, STAT_NOTACTIVE) : STAT_ACTIVE),
						new object_list_filter(array("non_filter_classes" => CL_DOCUMENT))
					);
					$ob = $this->_get_ordby($box);
					if (trim($ob) != "")
					{
						$filt["sort_by"] = $ob;
					}

					$ol = new object_list($filt);
					$ids = $ol->ids();
					if ($box->prop("start_ndocs") > 0)
					{
						$fin_cnt = $box->prop("ndocs") - $box->prop("start_ndocs");
						if (count($ids) > $fin_cnt)
						{
							$ids = array_slice($ids, count($ids) - $fin_cnt);
						}
					}
					// now we know the whole list, so just set that
					$box->set_meta("content_documents", $this->make_keys($ids));
					$box->save();
					continue;
				}
				else
				{
					$add_to_list = true;
				}
			}

			if ($o->status() == STAT_NOTACTIVE && !$box->prop("show_inact"))
			{
				$add_to_list = false;
			}

			if ($add_to_list)
			{
				$mt = safe_array($box->meta("content_documents"));
				$mt[$o->id()] = $o->id();

				if ($box->prop("sort_by") != "")
				{
					// need to reorder list
					$filt = array(
						"oid" => $mt,
						"status" => ($box->prop("show_inact") ? array(STAT_ACTIVE, STAT_NOTACTIVE) : STAT_ACTIVE),
						new object_list_filter(array("non_filter_classes" => CL_DOCUMENT))
					);
					$ob = $this->_get_ordby($box);
					if (trim($ob) != "")
					{
						$filt["sort_by"] = $ob;
					}
					$ol = new object_list($filt);
					$mt = $this->make_keys($ol->ids());
				}

				$box->set_meta("content_documents", $mt);
				$box->save();
			}
		}
	}

	function _get_folders_for_box($box)
	{
		$ret = array();
		$subs = safe_array($box->meta("src_submenus"));
		foreach($box->connections_from(array("type" => "RELTYPE_DOC_SOURCE")) as $c)
		{
			$ret[$c->prop("to")] = $subs[$c->prop("to")] == $c->prop("to");
		}

		if (!count($ret))
		{
			return array($box->id() => $box->id());
		}

		return $ret;
	}

	function _is_in_path($path, $f)
	{
		foreach($path as $o)
		{
			if ($o->id() == $f)
			{
				return true;
			}
		}
		return false;
	}

	function _get_ordby($box)
	{
		$ordby = NULL;
		if ($box->meta("sort_by") != "")
		{
			$ordby = $box->meta("sort_by");
			if ($box->meta("sort_ord") != "")
			{
				$ordby .= " ".$box->meta("sort_ord");
			}
			if ($box->meta("sort_by") == "documents.modified")
			{
				$ordby .= ", objects.created DESC";
			};
		}
		return $ordby;
	}

	function on_save_promo($arr)
	{
		$o = obj($arr["oid"]);

		// get list of docs for promo
		$si = get_instance("contentmgmt/site_show");
		
		$dd = $si->get_default_document(array(
			"obj" => $o
		));
		if ($dd == false)
		{
			$dd = array();
		}

		if (!is_array($dd))
		{
			$dd = array($dd);
		}
		$o->set_meta("content_documents", $this->make_keys($dd));

		$o->set_meta("version", 2);
		$o->save();
	}

	function on_delete_document($arr)
	{
		$o = obj($arr["oid"]);

		// figure out if this document is to be shown in any promo in the system
		// to do that
		// make a list of all promo boxes
		$ol = new object_list(array(
			"class_id" => CL_PROMO,
			"lang_id" => array(),
			"site_id" => array()
		));

		$path = $o->path();

		foreach($ol->arr() as $box)
		{
			$add_to_list = false;

			// for each box, check the folders where it gets documents and if this document's parent is one of them, 
			$fld = $this->_get_folders_for_box($box);
			$is_in_promo = false;
			foreach($fld as $f => $subs)
			{
				if ($f == $o->parent() || ($subs && $this->_is_in_path($path, $f)))
				{
					$is_in_promo = true;
					break;
				}
			}

			if ($is_in_promo)
			{
				// get list of docs for promo
				$si = get_instance("contentmgmt/site_show");
		
				$box->set_meta("content_documents", $this->make_keys($si->get_default_document(array(
					"obj" => $box
				))));

				$box->set_meta("version", 2);
				$box->save();
			}
		}
	}
}
?>
