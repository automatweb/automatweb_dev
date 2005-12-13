<?

class promo_display 
{
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
		//$filter["sort_by"] = "objects.jrk";

		/*if (aw_ini_get("menuedit.lang_menus"))
		{
			$filter["lang_id"] = aw_global_get("lang_id");
		}*/
		$filter["lang_id"] = array();

		enter_function("promo_get_list");
		$list = new object_list($filter);
		$parr = $list->arr();
		$list->sort_by(array("prop" => "ord"));
		$parr = $list->arr();
		exit_function("promo_get_list");

		$tplmgr = get_instance("templatemgr");
		$promos = array();
		$gidlist = aw_global_get("gidlist");
		$lang_id = aw_global_get("lang_id");
		$rootmenu = aw_ini_get("rootmenu");
		$default_tpl_filename = aw_ini_get("promo.default_tpl");
		$tpldir = aw_ini_get("tpldir");
		$no_acl_checks = aw_ini_get("menuedit.no_view_acl_checks");
		$promo_areas = aw_ini_get("promo.areas");
		foreach($parr as $o)
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

				if ($o->meta("version") == 2 && (aw_ini_get("promo.version") == 2))
				{
					enter_function("mainc-contentmgmt/promo-read_docs");
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
						$nids = $ol->ids();
						$tmp = array();	
						foreach($docid as $_id)
						{
							if (in_array($_id, $nids))
							{
								$tmp[] = $_id;
							}
						}
						$docid = $tmp;
					}
					exit_function("mainc-contentmgmt/promo-read_docs");
				}
				else
				{
					enter_function("mainc-contentmgmt/promo-read_docs-old");
					// get_default_document prefetches docs by itself so no need to do list here
					$docid = $inst->get_default_document(array(
						"obj" => $o,
						"all_langs" => true
					));
					exit_function("mainc-contentmgmt/promo-read_docs-old");
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
				aw_global_set("in_promo_display", 1);
				enter_function("mainc-contentmgmt/promo-show-docs");
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
						"boldlead" => aw_ini_get("promo.boldlead"),
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
				exit_function("mainc-contentmgmt/promo-show-docs");
				aw_global_set("in_promo_display", 0);

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
}
?>