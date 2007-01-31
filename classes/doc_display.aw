<?php

class doc_display extends aw_template
{
	function doc_display()
	{
		$this->init();
	}

	/** displays document

		@comment
			docid - document id
			tpl - template file to use
			leadonly - show only lead, defaults to full
			vars - extra vars for doc template
			
			not_last_in_list
			no_link_if_not_act
	**/
	function gen_preview($arr)
	{
		$doc = obj($arr["docid"]);
		if (aw_ini_get("config.object_versioning") == 1 && $_GET["docversion"] != "")
		{
			$doc->load_version($_GET["docversion"]);
		}

		$doc_parent = obj($doc->parent());
		$this->tpl_reset();
		$this->tpl_init("automatweb/documents");
		$this->read_any_template($this->_get_template($arr));

		$si = __get_site_instance();
		if ($si)
		{
			$si->parse_document_new($doc);
		}

		$text = $this->_get_text($arr, $doc);
		$this->create_relative_links($text);
		$text_no_aliases = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$text);

		$al = get_instance("alias_parser");
		$mt = $doc->meta();
		$al->parse_oo_aliases(
			$doc->id(),
			&$text,
			array(
				"templates" => &$this->templates,
				"meta" => &$mt
			)
		);

		lc_site_load("document",$this);
		
		$this->vars(array("image_inplace" => ""));
		$this->vars($al->get_vars());
		$docmod = $doc->prop("doc_modified");
		$_date = $doc->prop("doc_modified") > 1 ? $doc->prop("doc_modified") : $doc->modified();
		$modf = $doc->modifiedby();
		$modf_eml = "";
		if ($modf != "" && $this->template_has_var_full("modifiedby_email"))
		{
			$u = get_instance(CL_USER);
			$p = $u->get_person_for_uid($modf);
			$modf = $p->name();
			$modf_eml = $p->prop("email.mail");
		}

		$doc_link = $this->get_doc_link($doc);

		if ($this->template_has_var_full("edit_doc"))
		{
			$em = $this->_get_edit_menu($doc);
		}

		$user1 = $doc->prop("user1");
		$al->parse_oo_aliases($doc->id(), &$user1, array("templates" => $this->templates, "meta" => $mt));
		$user2 = $doc->prop("user2");
		$al->parse_oo_aliases($doc->id(), &$user2, array("templates" => $this->templates, "meta" => $mt));
		$user3 = $doc->prop("user3");
		$al->parse_oo_aliases($doc->id(), &$user3, array("templates" => $this->templates, "meta" => $mt));
		$user4 = $doc->prop("user4");
		$al->parse_oo_aliases($doc->id(), &$user4, array("templates" => $this->templates, "meta" => $mt));
		$user5 = $doc->prop("user5");
		$al->parse_oo_aliases($doc->id(), &$user5, array("templates" => $this->templates, "meta" => $mt));
		$user6 = $doc->prop("user6");
		$al->parse_oo_aliases($doc->id(), &$user6, array("templates" => $this->templates, "meta" => $mt));
		
		$userta1 = $doc->prop("userta1");
		$al->parse_oo_aliases($doc->id(), &$userta1, array("templates" => $this->templates, "meta" => $mt));
		$userta2 = $doc->prop("userta2");
		$al->parse_oo_aliases($doc->id(), &$userta2, array("templates" => $this->templates, "meta" => $mt));
		$userta3 = $doc->prop("userta3");
		$al->parse_oo_aliases($doc->id(), &$userta3, array("templates" => $this->templates, "meta" => $mt));
		$userta4 = $doc->prop("userta4");
		$al->parse_oo_aliases($doc->id(), &$userta4, array("templates" => $this->templates, "meta" => $mt));
		$userta5 = $doc->prop("userta5");
		$al->parse_oo_aliases($doc->id(), &$userta5, array("templates" => $this->templates, "meta" => $mt));
		$userta6 = $doc->prop("userta6");
		$al->parse_oo_aliases($doc->id(), &$userta6, array("templates" => $this->templates, "meta" => $mt));
		
		$this->vars_safe(array(
			"date_est_docmod" => $docmod > 1 ? locale::get_lc_date($_date, LC_DATE_FORMAT_LONG) : "",
			"text" => $text,
			"text_no_aliases" => $text_no_aliases,
			"title" => $doc->trans_get_val("title"),
			"author" => $doc->prop("author"),
			"channel" => $doc->prop("channel"),
			"docid" => $doc->id(),
			"date_est" => locale::get_lc_date($_date, LC_DATE_FORMAT_LONG),
			"date_est_fullyear" => locale::get_lc_date($_date, LC_DATE_FORMAT_LONG_FULLYEAR),
			"print_date_est" => locale::get_lc_date(time(), LC_DATE_FORMAT_LONG),
			"modified" => date("d.m.Y", $doc->modified()),
			"modifiedby" => $modf,
			"modifiedby_email" => $modf_eml,
			"parent_id" => $doc->parent(),
			"parent_name" => $doc_parent->name(),
			"user1" => $user1,
			"user2" => $user2,
			"user3" => $user3,
			"user4" => $user4,
			"user5" => $user5,
			"user6" => $user6,
			"userta1" => $userta1,
			"userta2" => $userta2,
			"userta3" => $userta3,
			"userta4" => $userta4,
			"userta5" => $userta5,
			"userta6" => $userta6,
			"link_text" => $doc->prop("link_text"),
			"page_title" => strip_tags($doc->trans_get_val("title")),			
			"date" => $_date,
			"edit_doc" => $em,
			"doc_link" => $doc_link,
			"print_link" => aw_url_change_var("print", 1),
			"trans_lc" => aw_global_get("ct_lang_lc")
		));

		$ablock = "";
		if ($doc->prop("author") != "")
		{
			$this->vars(array(
				"ablock" => $this->parse("ablock")
			));
		}

		$nll = "";
		if ($arr["not_last_in_list"])
		{
			$nll = $this->parse("NOT_LAST_IN_LIST");
		}
		$this->vars(array(
			"NOT_LAST_IN_LIST" => $nll
		));

		$this->vars(array(
			"logged" => (aw_global_get("uid") != "" ? $this->parse("logged") : ""),
		));

		$this->vars(array(
                        "SHOW_MODIFIED" => ($doc->prop("show_modified") ? $this->parse("SHOW_MODIFIED") : ""),
                ));


		$ps = "";
		if (( ($doc->prop("show_print")) && (!$_GET["print"]) && $arr["leadonly"] != 1))
		{
			$ps = $this->parse("PRINTANDSEND");
		}

		if ($doc->prop("title_clickable"))
		{
			$this->vars(array("TITLE_LINK_BEGIN" => $this->parse("TITLE_LINK_BEGIN"), "TITLE_LINK_END" => $this->parse("TITLE_LINK_END")));
		}
		$this->vars(array(
			"SHOW_TITLE" => ($doc->prop("show_title") == 1 && $doc->prop("title") != "") ? $this->parse("SHOW_TITLE") : "",
			"PRINTANDSEND" => $ps,
			"SHOW_MODIFIED" => ($doc->prop("show_modified") ? $this->parse("SHOW_MODIFIED") : ""),
		));

		$this->_do_forum($doc);
		$this->_do_charset($doc);
		$this->_do_checkboxes($doc);
		$this->_do_user_subs($doc);

		$this->vars(array(
			"logged" => (aw_global_get("uid") != "" ? $this->parse("logged") : ""),
		));

		$str = $this->parse();
		$this->vars(array("image_inplace" => ""));
		return $str;
	}

	function _get_template($arr)
	{
		// use special template for printing if one is set in the cfg file
		if (aw_global_get("print") && aw_ini_get("document.print_tpl"))
		{
			return aw_ini_get("document.print_tpl");
		}

		if (isset($arr["tpl"]))
		{
			return $arr["tpl"];
		}
		
		// do template autodetect from parent
		$tplmgr = get_instance("templatemgr");
		if ($leadonly > -1)
		{
			$tpl = $tplmgr->get_lead_template($doc["parent"]);
		}
		else
		{
			$tpl = $tplmgr->get_long_template($doc["parent"]);
		}
		if ($tpl == "")
		{
			return $arr["leadonly"] ? "lead.tpl" : "plain.tpl";
		}
		return $tpl;
	}

	function _get_text($arr, $doc)
	{
		$lead = $doc->trans_get_val("lead");
		$content = $doc->trans_get_val("content");
		$sps = $doc->meta("setps");
		if ($sps["lead"])
		{
			$lead = $sps["lead"];
		}
		if ($sps["content"])
		{
			$content = $sps["content"];
		}
		if ($arr["leadonly"] > -1)
		{
			$text = $lead; //$doc->trans_get_val("lead");
		}
		else
		{
			if ($doc->prop("showlead") || $arr["showlead"])
			{
				//$lead = $doc->trans_get_val("lead");
				if (trim(strtolower($lead)) == "<br>")
				{
					$lead = "";
				}
				if ($lead != "")
				{
					if (aw_ini_get("document.boldlead"))
					{
						$lead = "<b>".$lead."</b>";
					}
					$text = $lead.aw_ini_get("document.lead_splitter").$content; //$doc->trans_get_val("content");
				}
				else
				{
					$text = $content; //$doc->trans_get_val("content");
				}
			}
			else
			{
				$text = $content; //$doc->trans_get_val("content");
			}
		}
		// line break conversion between wysiwyg and not
		$cb_nb = $doc->meta("cb_nobreaks");
		if (!($doc->prop("nobreaks") || $cb_nb["content"]))	
		{
			$text = str_replace("\r\n","<br />",$text);
			$text = str_replace("</li><br />", "</li>", $text);
			$text = str_replace("<br /><ul><br />", "<ul>", $text);
			$text = str_replace("</ul><br />", "</ul>", $text);
		}

		if (strpos($text, "#login#") !== false)
                {
                        if (aw_global_get("uid") == "")
                        {
                                $li = get_instance("aw_template");
                                $li->init();
                                $li->read_template("login.tpl");
                                $text = str_replace("#login#", $li->parse(), $text);
                        }
                        else
                        {
                                $text = str_replace("#login#", "", $text);
                        }
                }

		// if show in iframe is set, just return the iframe
		if ($doc->prop("show_in_iframe") && !$_REQUEST["only_document_content"])
		{
			$this->vars(array(
				"iframe_url" => obj_link($doc->id())."?only_document_content=1"
			));
			return $this->parse("IFRAME");
		}

		return $text;
	}

	function _do_forum($doc)
	{
		if ($doc->prop("is_forum") &&
			($this->is_template("FORUM_ADD_SUB") || $this->is_template("FORUM_ADD_SUB_ALWAYS") || $this->is_template("FORUM_ADD"))
			
	     	)
		{
			$_sect = aw_global_get("section");
			// calculate the amount of comments this document has
			// XXX: I could use a way to figure out which variables are present in the template
			$num_comments = $this->db_fetch_field("SELECT count(*) AS cnt FROM comments WHERE board_id = '".$doc->id()."'","cnt");
			$this->vars(array(
				"num_comments" => sprintf("%d",$num_comments),
				"comm_link" => $this->mk_my_orb("show_threaded",array("board" => $doc->id(),"section" => $_sect),"forum"),
			));
			$forum = get_instance(CL_FORUM);
			$fr = $forum->add_comment(array("board" => $doc->id(),"section" => $_sect));

			if ($num_comments > 0)
			{
				$this->vars(array("FORUM_ADD_SUB" => $this->parse("FORUM_ADD_SUB")));
			}
			$this->vars(array("FORUM_ADD_SUB_ALWAYS" => $this->parse("FORUM_ADD_SUB_ALWAYS")));
		}
		else
		{
			$this->vars(array("FORUM_ADD_SUB_ALWAYS" => ""));
			$this->vars(array("FORUM_ADD_SUB" => ""));
		}
	}

	function _do_charset($doc)
	{
		if ($this->template_has_var("charset"))
		{
			$_langs = get_instance("languages");
			$_ld = $_langs->fetch(aw_global_get("lang_id"));
			$this->vars(array(
				"charset" => $_ld["charset"]
			));
		};
	}

	function _do_checkboxes($doc)
	{
		if ($doc->prop("ucheck1") == 1)
		{
			$this->vars(array(
				"UCHECK1_CHECKED" => $this->parse("UCHECK1_CHECKED"),
				"UCHECK1_UNCHECKED" => ""
			));
		}
		else
		{
			$this->vars(array(
				"UCHECK1_CHECKED" => "",
				"UCHECK1_UNCHECKED" => $this->parse("UCHECK1_UNCHECKED")
			));
		}
	}
	
	function _get_edit_menu($menu)
	{
		if (!$this->prog_acl() || $_SESSION["no_display_site_editing"])
		{
			return;
		}
		$pm = get_instance("vcl/popup_menu");
		$pm->begin_menu("site_edit_".$menu->id());
		$url = $this->mk_my_orb("new", array("parent" => $menu->parent(), "ord_after" => $menu->id(), "return_url" => get_ru(), "is_sa" => 1), CL_DOCUMENT, true);
		$pm->add_item(array(
			"text" => t("Lisa uus"),
			"oncl" => "onClick=\"aw_popup_scroll('$url', 'aw_doc_edit',800, 600)\"",
			"link" => "javascript:void(0)"
		));
		$url = $this->mk_my_orb("change", array("id" => $menu->id(), "return_url" => get_ru(), "is_sa" => 1), CL_DOCUMENT, true);
		$pm->add_item(array(
			"text" => t("Muuda"),
			"oncl" => "onClick=\"aw_popup_scroll('$url', 'aw_doc_edit',800, 600)\"",
			"link" => "javascript:void(0)"
		));
		$pm->add_item(array(
			"text" => t("Peida"),
			"link" => $this->mk_my_orb("hide_doc", array("id" => $menu->id(), "ru" => get_ru()), "menu_site_admin")
		));
		$pm->add_item(array(
			"text" => t("L&otilde;ika"),
			"link" => $this->mk_my_orb("cut_doc", array("id" => $menu->id(), "ru" => get_ru()), "menu_site_admin")
		));
		if ($this->can("view", $_SESSION["site_admin"]["cut_doc"]))
		{
			$pm->add_item(array(
				"text" => t("Kleebi"),
				"link" => $this->mk_my_orb("paste_doc", array("after" => $menu->id(), "ru" => get_ru()), "menu_site_admin")
			));
		}
		return $pm->get_menu();
	}

	function create_relative_links(&$text)
	{
		while (preg_match("/(#)(\d+?)(#)(.*)(#)(\d+?)(#)/imsU",$text,$matches))
		{
			$text = str_replace($matches[0],"<a href='#" . $matches[2] . "'>$matches[4]</a>",$text);
		}
		while(preg_match("/(#)(s)(\d+?)(#)/",$text,$matches))
		{
			$text = str_replace($matches[0],"<a name='" . $matches[3] . "'> </a>",$text);
		}
	}

	function _do_user_subs($doc)
	{
		$u1s = "";
		if ($doc->prop("user1") != "")
		{
			$u1s = $this->parse("user1_sub");
		}
		$u2s = "";
		if ($doc->prop("user2") != "")
		{
			$u2s = $this->parse("user2_sub");
		}
		$u3s = "";
		if ($doc->prop("user3") != "")
		{
			$u3s = $this->parse("user3_sub");
		}
		$u4s = "";
		if ($doc->prop("user4") != "")
		{
			$u4s = $this->parse("user4_sub");
		}
		$u5s = "";
		if ($doc->prop("user5") != "")
		{
			$u5s = $this->parse("user5_sub");
		}
		$u6s = "";
		if ($doc->prop("user6") != "")
		{
			$u6s = $this->parse("user6_sub");
		}
		$this->vars(array(
			"user1_sub" => $u1s
		));
	}

	function get_doc_link($doc, $lc = null)
	{
		$doc_link = obj_link($doc->id());
		if (aw_ini_get("document.links_to_same_section"))
		{
			$doc_link = aw_url_change_var("docid", $doc->id(), obj_link(aw_global_get("section")));
		}
		if ($doc->prop("alias") != "")
		{
			$doc_link = obj_link($doc->prop("alias"));
		}

		if (aw_ini_get("menuedit.language_in_url"))
		{
			static $ss_i;
			if (!$ss_i)
			{
				$ss_i = get_instance("contentmgmt/site_show");
			}
			$doc_link = $ss_i->make_menu_link($doc, $lc);
		}
		return $doc_link;
	}
}
