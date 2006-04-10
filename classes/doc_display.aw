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

		$al = get_instance("aliasmgr");
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
		
		$this->vars($al->get_vars());
		$_date = $doc->prop("doc_modified") > 1 ? $doc->prop("doc_modified") : $doc->modified();

		$this->vars(array(
			"text" => $text,
			"title" => $doc->prop("title"),
			"author" => $doc->prop("author"),
			"channel" => $doc->prop("channel"),
			"docid" => $doc->id(),
			"date_est" => locale::get_lc_date($_date, LC_DATE_FORMAT_LONG),
			"print_date_est" => locale::get_lc_date(time(), LC_DATE_FORMAT_LONG),
			"modified" => date("d.m.Y", $doc->modified()),
			"parent_id" => $doc->parent(),
			"parent_name" => $doc_parent->name(),
			"user1" => $doc->prop("user1"),
			"user2" => $doc->prop("user2"),
			"user3" => $doc->prop("user3"),
			"user4" => $doc->prop("user4"),
			"user5" => $doc->prop("user5"),
			"user6" => $doc->prop("user6"),
			"userta2" => $doc->prop("userta2"),
			"userta3" => $doc->prop("userta3"),
			"userta4" => $doc->prop("userta4"),
			"userta5" => $doc->prop("userta5"),
			"userta6" => $doc->prop("userta6"),
			"link_text" => $doc->prop("link_text"),
			"page_title" => strip_tags($doc->prop("title")),			
			"date" => $_date,
			"edit_doc" => $this->_get_edit_menu($doc)
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

		$this->vars(array(
			"logged" => (aw_global_get("uid") != "" ? $this->parse("logged") : ""),
		));

		$str = $this->parse();
		return $str;
	}

	function _get_template($arr)
	{
		// use special template for printing if one is set in the cfg file
		if (aw_global_get("print") && ($this->cfg["print_tpl"]) )
		{
			return $this->cfg["print_tpl"];
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
		if ($arr["leadonly"] > -1)
		{
			$text = $doc->prop("lead");
		}
		else
		{
			if ($doc->prop("showlead") || $arr["showlead"])
			{
				$lead = $doc->prop("lead");
				if (aw_ini_get("document.boldlead"))
				{
					$lead = "<b>".$lead."</b>";
				}
				$text = $lead.aw_ini_get("document.lead_splitter").$doc->prop("content");
			}
			else
			{
				$text = $doc->prop("content");
			}
		}

		// line break conversion between wysiwyg and not
		$cb_nb = $doc->meta("cb_nobreaks");
		if (!($doc->prop("nobreaks") || $cb_nb["content"]))	
		{
			$text = str_replace("\r\n","<br />",$text);
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
		if (!$this->prog_acl())
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
}
