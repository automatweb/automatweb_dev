<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/promo.aw,v 1.11 2003/09/24 12:49:55 kristo Exp $
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

	@property section type=text callback=callback_get_menus group=menus method=serialize
	@caption Vali menüüd, mille all kasti näidata
	
	@property last_menus type=text callback=callback_get_doc_sources group=menus method=serialize
	@caption Vali menüüd, mille alt viimaseid dokumente võetakse

	@property ndocs type=textbox size=4 group=menus table=menu field=ndocs 
	@caption Mitu viimast dokumenti

	@property sort_by type=select table=objects field=meta method=serialize group=show
	@caption Dokumente j&auml;rjestatakse

	@property sort_ord type=select table=objects field=meta method=serialize group=show

	@classinfo corefields=name,comment,status
	@classinfo relationmgr=yes
	@classinfo syslog_type=ST_PROMO

	@tableinfo menu index=id master_table=objects master_index=oid

	@groupinfo general caption=Üldine
	@groupinfo menus caption=Kaustad
	@groupinfo show caption=Näitamine

	@classinfo trans_id=TR_PROMO
			
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
			"trid" => TR_PROMO,
		));
		lc_load("definition");
		$this->lc_load("promo","lc_promo");
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_ASSIGNED_MENU || $args["reltype"] == RELTYPE_DOC_SOURCE)
		{
			return array(CL_PSEUDO);
		}
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_ASSIGNED_MENU => "näita menüü juures",
			RELTYPE_DOC_SOURCE => "võta dokumente selle menüü alt",
		);
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK; 
		switch($data["name"])
		{
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
		}
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$meta = &$args["metadata"];
		$retval = PROP_OK;
		if ($data["name"] == "section")
		{
			$meta["section_include_submenus"] = $args["form_data"]["include_submenus"];
		};

		if ($data["name"] == "ndocs")
		{
			$args["metadata"]["as_name"] = $args["form_data"]["as_name"];
		}
		return $retval;

	}

	function callback_get_menus($args = array())
	{
		$prop = $args["prop"];
		$nodes = array();
		$section_include_submenus = $args["obj"]["meta"]["section_include_submenus"];
		// now I have to go through the process of setting up a generic table once again
		load_vcl("table");
		$this->t = new aw_table(array(
			"prefix" => "promo_menus",
			"layout" => "generic"
		));
		$this->t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
		));
		$this->t->define_field(array(
			"name" => "check",
			"caption" => "k.a. alammenüüd",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));

		$obj = obj($args["obj"]["oid"]);
		$conns = $obj->connections_from(array(
			"type" => RELTYPE_ASSIGNED_MENU
		));
		foreach($conns as $c)
		{
			$c_o = $c->to();

			$this->t->define_data(array(
				"oid" => $c_o->id(),
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
 
		$nodes[$prop["name"]] = array(
			"type" => "text",
			"caption" => $prop["caption"],
			"value" => $this->t->draw(),
		);
		return $nodes;
	}

	function callback_get_doc_sources($args = array())
	{
		$prop = $args["prop"];
		$nodes = array();
		// now I have to go through the process of setting up a generic table once again
		load_vcl("table");
		$this->t = new aw_table(array(
			"prefix" => "promo_menus",
			"layout" => "generic"
		));
		$this->t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "as_name",
			"caption" => "Pane pealkirjaks",
			"talign" => "center",
			"align" => "center",
		));

		$obj = obj($args["obj"]["oid"]);
		$conns = $obj->connections_from(array(
			"type" => RELTYPE_DOC_SOURCE
		));

		foreach($conns as $c)
		{
			$c_o = $c->to();
			$this->t->define_data(array(
				"oid" => $c_o->id(),
				"name" => $c_o->path_str(array(
					"max_len" => 3
				)),
				"as_name" => html::radiobutton(array(
					"name" => "as_name",
					"value" => $c_o->id(),
						"checked" => ($args["obj"]["meta"]["as_name"] == $c_o->id())
				))
			));
		}
		$this->t->define_data(array(
			"oid" => 0,
			"name" => "",
			"as_name" => html::radiobutton(array(
				"name" => "as_name",
				"value" => 0,
				"checked" => (!$args["obj"]["meta"]["as_name"])
			))
		));
		
		$nodes[$prop["name"]] = array(
			"type" => "text",
			"caption" => $prop["caption"],
			"value" => $this->t->draw(),
		);

		return $nodes;
	}

	function callback_pre_edit($args = array())
	{
		$id = $args["coredata"]["oid"];
		$menu = $this->get_menu($id);
		// first check, whether the promo box was in the very old format (contained serialized data
		// in the comment field
		$check1 = aw_unserialize($menu["comment"]);
		$check2 = aw_unserialize($menu["sss"]);
		if (is_array($check1) || is_array($check2))
		{
			$convert_url = $this->mk_my_orb("promo_convert",array(),"converters");
			print "See objekt on vanas formaadis. Enne kui seda muuta saab, tuleb kõik süsteemis olevad promokastis uude formaati konvertida. <a href='$convert_url'>Kliki siia</a> konversiooni alustamiseks";
			exit;
		};
		
		// now, check, whether we have to convert the current contents of comment and sss to relation objects
		// we use a flag in object metainfo for that

		// and still, it would be nice if we could convert all the promo boxes at once.
		// then I wouldn't have to check for this shit each fucking time, for each
		// fucking promo box. But maybe it's not as bad as I imagine it
		if ($args["object"]["meta"]["uses_relationmgr"])
		{
			return true;
		};
		
		$oldaliases = $this->get_aliases_for($id,CL_PSEUDO);
		$flatlist = array();
		$alias_reltype = $args["coredata"]["meta"]["alias_reltype"];


		$q = "SELECT * FROM aliases WHERE source = '$id'";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			if (($row["reltype"] == 0) && ($alias_reltype[$row["target"]]))
			{
				$this->save_handle();
				$q = "UPDATE aliases SET reltype = " . $alias_reltype[$row["target"]] . " WHERE id = $row[id]";
				$this->db_query($q);
				$this->restore_handle();
			};
		}


		foreach($oldaliases as $alias)
		{
			$flatlist[$alias["target"]] = $alias_reltype[$alias["target"]];
		};

		// basically, I have to get a list of menus in $args["object"]["meta"]["section"]
		// and create a relation of type RELTYPE_ASSIGNED_MENU for each of those

		$sections = $args["coredata"]["meta"]["section"];
		if ( is_array($sections) && (sizeof($sections) > 0) )
		{
			foreach($sections as $key => $val)
			{
				if (!$flatlist[$val])
				{
					$alias_reltype[$val] = RELTYPE_ASSIGNED_MENU;
					$this->addalias(array(
						"id" => $id,
						"alias" => $val,
						"reltype" => RELTYPE_ASSIGNED_MENU,
					));
					//$this->add_alias($id,$val,CL_PSEUDO);
				};
			};
		}

		// then I have to get a list of menus in $args["object"]["meta"]["last_menus"] and
		// create a relation of type RELTYPE_DOC_SOURCE for each of those.

		// I also want to keep the old representation around, so that old code keeps working
		$last_menus = $args["coredata"]["meta"]["last_menus"];
		if ( is_array($last_menus) && (sizeof($last_menus) > 0) )
		{
			foreach($last_menus as $key => $val)
			{
				if (!$flatlist[$val])
				{
					$alias_reltype[$val] = RELTYPE_DOC_SOURCE;
					$this->addalias(array(
						"id" => $id,
						"alias" => $val,
						"reltype" => RELTYPE_DOC_SOURCE,
					));
					//$this->add_alias($id,$val,CL_PSEUDO);
				};
			};
		}

		// update reltype information, that is only if there is anything to update
		if (sizeof($alias_reltype) > 0)
		{
			$this->upd_object(array(
				"oid" => $args["coredata"]["oid"],
				"metadata" => array(
					//"alias_reltype" => $alias_reltype,
					"uses_relationmgr" => 1,
				),
			));
		};
	}

	function callback_pre_save($args = array())
	{
		$objdata = &$args["objdata"];
		if (!$objdata["type"])
		{
			$objdata["type"] = MN_PROMO_BOX;
		};
	}

	function callback_on_submit_relation_list($args = array())
	{
		// this is where we put data back into object metainfo, for backwards compatibility
		$obj =& obj($args["id"]);

		$oldaliases = $obj->connections_from(array(
			"class" => CL_PSEUDO
		));
		
		$section = array();
		$last_menus = array();

		$alias_reltype = $obj->meta("alias_reltype");
		$new_alias_reltype = array();
		foreach($oldaliases as $alias)
		{
			if ($alias["reltype"] == RELTYPE_ASSIGNED_MENU)
			{
				$section[$alias["to"]] = $alias["to"];
				$new_alias_reltype[$alias["to"]] = RELTYPE_ASSIGNED_MENU;
			};
			
			if ($alias["reltype"] == RELTYPE_DOC_SOURCE)
			{
				$last_menus[$alias["to"]] = $alias["to"];
				$new_alias_reltype[$alias["to"]] = RELTYPE_DOC_SOURCE;
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
		$obj->set_meta("alias_reltype",$new_alias_reltype);

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

		$me = get_instance("contentmgmt/site_content");
		if (is_array($ob->meta('last_menus')))
		{
			$def = array();
			foreach($ob->meta('last_menus') as $menu)
			{
				$_t = new aw_array($me->get_default_document($menu,true));
				$def += $_t->get();
			}
			$def = new aw_array($def);
		}
		else
		{
			$def = new aw_array($me->get_default_document($alias["target"],true));
		}

		$_ob = aw_ini_get("menuedit.document_list_order_by");
		if (($_ob != "") && (sizeof($def->get()) > 0))
		{
			$ol = new object_list(array(
				"class_id" => CL_DOCUMENT,
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
			"boldlead" => 1,
			"no_strip_lead" => 1,
		);

		if (!$ob->meta('use_fld_tpl'))
		{
			$mgr = get_instance("templatemgr");
			$parms["tpl"] = $mgr->get_template_file_by_id($ob->prop("tpl_lead"));
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
			$as_n_o = obj($ob->meta("as_name"));
			$ob->set_meta('caption',$as_n_o->name());
		}

		$align= array("k" => "align=\"center\"", "p" => "align=\"right\"" , "v" => "align=\"left\"" ,"" => "");
		$this->vars(array(
			"title" => $ob->meta("caption"),
			"content" => $content,
			"align" => $align[$args["matches"][4]],
		));

		if (!$ob->meta('no_title'))
		{
			$this->vars(array(
				"SHOW_TITLE" => $this->parse("SHOW_TITLE")
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
				$tpl_filename = $tplmgr->get_template_file_by_id($o->prop("tpl_lead"));
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
			
			$msec = $o->prop("section");

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
			if (is_array($o->meta("section_include_submenus")) && $inst->is_in_path($inst->sel_section))
			{
				$pa = array();
				foreach($inst->path as $o)
				{
					$pa[] = $o->id();
				}

				// here we need to check, whether any of the parent menus for
				// this menu has been assigned a promo box and has been told
				// that it should be shown in all submenus as well
				$intersect = array_intersect($pa,$o->meta("section_include_submenus"));
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

				foreach($docid as $d)
				{
					$cont = $doc->gen_preview(array(
						"docid" => $d,
						"tpl" => $tpl_filename,
						"leadonly" => $leadonly,
						"section" => $inst->sel_section,
						"strip_img" => false,
						"showlead" => 1,
						"boldlead" => 1,
						"no_strip_lead" => 1,
						"no_acl_checks" => aw_ini_get("menuedit.no_view_acl_checks"),
					));
					$pr_c .= str_replace("\r","",str_replace("\n","",$cont));
				}

				$inst->vars(array(
					"comment" => $o->comment(),
					"title" => $o->name(), 
					"content" => $pr_c,
					"url" => $o->prop("link"),
					"link_caption" => $o->meta("link_caption")
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
