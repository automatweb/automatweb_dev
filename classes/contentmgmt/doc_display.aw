<?php
/*
@classinfo  maintainer=kristo
*/
class doc_display extends aw_template
{
	var $no_left_pane;
	var $no_right_pane;

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
		enter_function("doc_display::gen_preview::".$arr["docid"]);
		$arr["leadonly"] = isset($arr["leadonly"]) ? $arr["leadonly"] : null;
		$doc = obj($arr["docid"]);
		if (aw_ini_get("config.object_versioning") == 1 && $_GET["docversion"] != "")
		{
			$doc->load_version($_GET["docversion"]);
		}

		$doc_parent = obj($doc->parent());
		$this->tpl_reset();
		$this->tpl_init("automatweb/documents");
		$tpl_file = $this->_get_template($arr);
		$this->read_any_template($tpl_file);

		$si = __get_site_instance();

		if ($si)
		{
			$si->parse_document_new($doc);
		}

		$text = $this->_get_text($arr, $doc);
		$lead = $this->_get_lead($arr, $doc);
		$content = $this->_get_content($arr, $doc);
		
		// parse keyword subs
		$this->parse_keywords($doc);

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
		
		$al->parse_oo_aliases(
			$doc->id(),
			&$content,
			array(
				"templates" => &$this->templates,
				"meta" => &$mt
			)
		);
		
		$al->parse_oo_aliases(
			$doc->id(),
			&$lead,
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

		$orig = $doc->get_original();

		$user1 = $doc->trans_get_val("user1");
		$al->parse_oo_aliases($doc->id(), &$user1, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user1")));
		$user2 = $doc->trans_get_val("user2");
		$al->parse_oo_aliases($doc->id(), &$user2, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user2")));
		$user3 = $doc->trans_get_val("user3");
		$al->parse_oo_aliases($doc->id(), &$user3, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user3")));
		$user4 = $doc->trans_get_val("user4");
		$al->parse_oo_aliases($doc->id(), &$user4, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user4")));
		$user5 = $doc->trans_get_val("user5");
		$al->parse_oo_aliases($doc->id(), &$user5, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user5")));
		$user6 = $doc->trans_get_val("user6");
		$al->parse_oo_aliases($doc->id(), &$user6, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user6")));
		$user7 = $doc->trans_get_val("user7");
		$al->parse_oo_aliases($doc->id(), &$user7, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user7")));
		$user8 = $doc->trans_get_val("user8");
		$al->parse_oo_aliases($doc->id(), &$user8, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user8")));
		$user9 = $doc->trans_get_val("user9");
		$al->parse_oo_aliases($doc->id(), &$user9, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user9")));
		$user10 = $doc->trans_get_val("user10");
		$al->parse_oo_aliases($doc->id(), &$user10, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user10")));
		$user11 = $doc->trans_get_val("user11");
		$al->parse_oo_aliases($doc->id(), &$user11, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user11")));
		$user12 = $doc->trans_get_val("user12");
		$al->parse_oo_aliases($doc->id(), &$user12, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user12")));
		$user13 = $doc->trans_get_val("user13");
		$al->parse_oo_aliases($doc->id(), &$user13, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user13")));
		$user14 = $doc->trans_get_val("user14");
		$al->parse_oo_aliases($doc->id(), &$user14, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user14")));
		$user15 = $doc->trans_get_val("user15");
		$al->parse_oo_aliases($doc->id(), &$user15, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user15")));
		$user16 = $doc->trans_get_val("user16");
		$al->parse_oo_aliases($doc->id(), &$user16, array("templates" => $this->templates, "meta" => $mt, "data" => array("prop" => "user16")));

		$userta1 = $orig->trans_get_val("userta1");
		$al->parse_oo_aliases($doc->id(), &$userta1, array("templates" => $this->templates, "meta" => $mt));
		$userta2 = $orig->trans_get_val("userta2");
		$al->parse_oo_aliases($doc->id(), &$userta2, array("templates" => $this->templates, "meta" => $mt));
		$userta3 = $orig->trans_get_val("userta3");
		$al->parse_oo_aliases($doc->id(), &$userta3, array("templates" => $this->templates, "meta" => $mt));
		$userta4 = $orig->trans_get_val("userta4");
		$al->parse_oo_aliases($doc->id(), &$userta4, array("templates" => $this->templates, "meta" => $mt));
		$userta5 = $orig->trans_get_val("userta5");
		$al->parse_oo_aliases($doc->id(), &$userta5, array("templates" => $this->templates, "meta" => $mt));
		$userta6 = $orig->trans_get_val("userta6");
		$al->parse_oo_aliases($doc->id(), &$userta6, array("templates" => $this->templates, "meta" => $mt));
	
		$title = $doc->trans_get_val("title");
		if (aw_global_get("set_doc_title") != "")
                {
                        $title = aw_global_get("set_doc_title");
                        aw_global_set("set_doc_title","");
                }
		
		$uinst = get_instance(CL_USER);
		$mb_person = $uinst->get_person_for_uid($doc->prop("modifiedby"));
		$this->vars($al->get_vars());

		$title = $doc->trans_get_val("title");
		$tmp = $arr;
		$tmp["leadonly"] = -1;
		$this->vars_safe(array(
			"date_est_docmod" => $docmod > 1 ? locale::get_lc_date($_date, LC_DATE_FORMAT_LONG) : "",
			"text" => $text,
			"fullcontent" => $this->_get_text($tmp, $doc),
			"text_no_aliases" => $text_no_aliases,
			"title" => $title,
			"author" => $doc->prop("author"),
			"channel" => $doc->prop("channel"),
			"docid" => $doc->id(),
			"modified_by" => $mb_person->name(),
			"date_est" => locale::get_lc_date($_date, LC_DATE_FORMAT_LONG),
			"date_est_fullyear" => locale::get_lc_date($_date, LC_DATE_FORMAT_LONG_FULLYEAR),
			"print_date_est" => locale::get_lc_date(time(), LC_DATE_FORMAT_LONG),
			"modified" => date("d.m.Y", $doc->modified()),
			"created_tm" => $doc->created(),
			"created_hr" => "<?php classload(\"doc_display\"); echo doc_display::get_date_human_readable(".$doc->created()."); ?>",
			"created_human_readable" => "<?php classload(\"doc_display\"); echo doc_display::get_date_human_readable(".$doc->created()."); ?>",
			"created" => date("d.m.Y", $doc->modified()),
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
			"user7" => $user7,
			"user8" => $user8,
			"user9" => $user9,
			"user10" => $user10,
			"user11" => $user11,
			"user12" => $user12,
			"user13" => $user13,
			"user14" => $user14,
			"user15" => $user15,
			"user16" => $user16,
			"userta1" => $userta1,
			"userta2" => $userta2,
			"userta3" => $userta3,
			"userta4" => $userta4,
			"userta5" => $userta5,
			"userta6" => $userta6,
			"link_text" => $doc->prop("link_text"),
			"page_title" => strip_tags($title),			
			"date" => $_date,
			"doc_modified" => $_date, // backward compability
			"edit_doc" => $em,
			"doc_link" => $doc_link,
			"document_link" => $doc_link,
			"print_link" => aw_url_change_var("print", 1),
			"printlink" => aw_url_change_var("print", 1), // backward compability
			"trans_lc" => aw_global_get("ct_lang_lc"),
			"lead" => $lead,
			"content" => $content,
		));

		$ablock = "";
		if ($doc->prop("author") != "")
		{
			$this->vars(array(
				"ablock" => $this->parse("ablock")
			));
		}
		
		$nll = "";
		if (!empty($arr["not_last_in_list"]))
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
		if (( ($doc->prop("show_print")) && empty($_GET["print"]) && $arr["leadonly"] != 1))
		{
			$ps = $this->parse("PRINTANDSEND");
		}

		if ($doc->prop("title_clickable"))
		{
			$this->vars(array("TITLE_LINK_BEGIN" => $this->parse("TITLE_LINK_BEGIN"), "TITLE_LINK_END" => $this->parse("TITLE_LINK_END")));
		}
		$this->vars_safe(array(
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
		exit_function("doc_display::gen_preview::".$arr["docid"]);
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
		if (!$arr["no_strip_lead"])
		{
			$lead = preg_replace("/#pict(\d+?)(v|k|p|)#/i","",$lead);
			$lead = preg_replace("/#p(\d+?)(v|k|p|)#/i","",$lead);
		}
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
		$content = str_replace("<!--[", "<!-- [", $content);
		$content = str_replace("]-->","] -->", $content);
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
		
		if (aw_ini_get("document.use_wiki_parser") == 1)
		{
			$this->_parse_wiki(& $text);
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
				if (($port = aw_ini_get("auth.display_over_ssl_port")) > 0)
				{
					if (!$_SERVER["HTTPS"])
					{
						$bits = parse_url(aw_ini_get("baseurl"));
						header("Location: https://".$bits["host"].":".$port.aw_global_get("REQUEST_URI"));
						die();
					}
				}
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
	
	function _get_lead($arr, $doc)
	{
		$lead = $doc->trans_get_val("lead");
		if (!$arr["no_strip_lead"])
		{
			$lead = preg_replace("/#pict(\d+?)(v|k|p|)#/i","",$lead);
			$lead = preg_replace("/#p(\d+?)(v|k|p|)#/i","",$lead);
		}

		if ($sps["lead"])
		{
			$lead = $sps["lead"];
		}
		
		if (trim(strtolower($lead)) == "<br>")
		{
			$lead = "";
		}
		
		$text = $lead; //$doc->trans_get_val("lead");
		
		if (aw_ini_get("document.use_wiki_parser") == 1)
		{
			$this->_parse_wiki(& $text);
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
		
		return $text;
	}
	
	function _get_content($arr, $doc)
	{
		$content = $doc->trans_get_val("content");
		$content = str_replace("<!--[", "<!-- [", $content);
		$content = str_replace("]-->","] -->", $content);
		
		$text = $content; //$doc->trans_get_val("content");
		
		if (aw_ini_get("document.use_wiki_parser") == 1)
		{
			$this->_parse_wiki(& $text);
		}
		
		// line break conversion between wysiwyg and not
		$cb_nb = $doc->meta("cb_nobreaks");
		if (!($doc->prop("nobreaks") || $cb_nb["content"]))	
		{
			if (aw_ini_get("content.doctype") == "xhtml")
			{
				$text = str_replace("\r\n","<br />",$text);
			}
			else
			{
				$text = str_replace("\r\n","<br>",$text);
			}
				$text = str_replace("</li><br />", "</li>", $text);
				$text = str_replace("<br /><ul><br />", "<ul>", $text);
				$text = str_replace("</ul><br />", "</ul>", $text);
		}
		
		return $text;
	}
	
	function _parse_wiki($str)
	{
		$str = trim($str);
		$this->_parse_wiki_lists(& $str);
		$this->_parse_wiki_titles(& $str);
		$this->_parse_youtube_links(& $str);
	}
	
	function _parse_wiki_titles($str)
	{
		$a_pattern[0] = "/\r\n======([a-zA-Z0-9\s,\/\.��������\-\?]+)======\r\n/mU";
		$a_replacement[0] = "<h6>\\1</h6>";
		$a_pattern[1] = "/^======([a-zA-Z0-9\s,\/\.��������\-\?]+)======\r\n/mU";
		$a_replacement[1] = "<h6>\\1</h6>";
		$a_pattern[2] = "/\r\n=====([a-zA-Z0-9\s,\/\.��������\-\?]+)=====\r\n/mU";
		$a_replacement[2] = "<h5>\\1</h5>";
		$a_pattern[3] = "/^=====([a-zA-Z0-9\s,\/\.��������\-\?]+)=====\r\n/mU";
		$a_replacement[3] = "<h5>\\1</h5>";
		$a_pattern[4] = "/\r\n====([a-zA-Z0-9\s,\/\.��������\-\?]+)====\r\n/mU";
		$a_replacement[4] = "<h4>\\1</h4>";
		$a_pattern[5] = "/^====([a-zA-Z0-9\s,\/\.��������\-\?]+)====\r\n/mU";
		$a_replacement[5] = "<h4>\\1</h4>";
		$a_pattern[6] = "/\r\n===([a-zA-Z0-9\s,\/\.��������\-\?]+)===\r\n/mU";
		$a_replacement[6] = "<h3>\\1</h3>";
		$a_pattern[7] = "/^===([a-zA-Z0-9\s,\/\.��������\-\?]+)===\r\n/mU";
		$a_replacement[7] = "<h3>\\1</h3>";
		$a_pattern[8] = "/\r\n==([a-zA-Z0-9\s,\/\.��������\-\?]+)==\r\n/mU";
		$a_replacement[8] = "<h2>\\1</h2>";
		$a_pattern[9] = "/^==([a-zA-Z0-9\s,\/\.��������\-\?]+)==\r\n/mU";
		$a_replacement[9] = "<h2>\\1</h2>";
		$a_pattern[10] = "/\r\n=([a-zA-Z0-9\s,\/\.��������\-\?]+)=\r\n/mU";
		$a_replacement[10] = "<h1>\\1</h1>";
		$a_pattern[11] = "/^=([a-zA-Z0-9\s,\/\.��������\-\?]+)=\r\n/mU";
		$a_replacement[11] = "<h1>\\1</h1>";
		
		
		$str = preg_replace  ( $a_pattern  , $a_replacement  , $str );
	}
	
	function _parse_wiki_lists($str)
	{
		$tmp = $str;
		$a_text = array();
		
		while ( strlen(trim( $tmp)) > 0 )
		{
			if (preg_match  ( "/^(.*)\r\n/U" , $tmp, &$mt))
			{
				$tmp = preg_replace  ( "/^(.*)\r\n/U"  , "" , $tmp );
			}
			else if (preg_match  ( "/^(.*)$/U" , $tmp, &$mt))
			{
				$tmp = preg_replace  ( "/^(.*)$/U"  , "" , $tmp );
			}
			$a_text[] = $mt[1];
		}
		
		
		$tmp = "";
		
		$working_on_list = false;
		for ($i=0;$i<count($a_text);$i++)
		{
			if ( preg_match  ( "/\*(.*)/U" , $a_text[$i], &$mt)  )
			{
				$a_text[$i] = substr  ($a_text[$i], 2);
				if ($working_on_list == false)
				{
					$tmp .= "<ul>";
					$tmp .= "<li>".$a_text[$i]."</li>";
					$working_on_list = true;
				}
				else
				{
					$tmp .= "<li>".$a_text[$i]."</li>";
				}
			}
			else
			{
				if ($working_on_list == true)
				{
					$tmp .= "</ul>";
					$tmp .= $a_text[$i]."\r\n";
					$working_on_list = false;
				}
				else
				{
					$tmp .=  $a_text[$i]."\r\n";
					
				}
			}
		}
		
		$str = $tmp;
	}
	
	function _parse_youtube_links($str)
	{
		$tmp_template = end(explode("/", $this->template_filename));
		if ( $this->is_template("youtube_link") && strpos($str, "http://www.youtube.com/")!==0)
		{
			$str = str_replace  ( array("http://www.youtube.com/watch?v=", "http://youtube.com/watch?v=")  , array("http://www.youtube.com/v/", "http://youtube.com/v/"), $str );
			
			if (strpos($str, "http://www.youtube.com/v/")!==0)
			{
				$this->vars(array(
					"link" => "\${1}\${2}\${3}\${4}",
				));
				$s_embed = $this->parse("youtube_link");
				$str = preg_replace  ("/(http:\/\/www.youtube.com\/v\/[a-zA-Z0-9_]*)$|(http:\/\/www.youtube.com\/v\/.*)\n|(http:\/\/youtube.com\/v\/[a-zA-Z0-9_]*)$|(http:\/\/youtube.com\/v\/.*)\n/imsU", $s_embed, $str);
			}
		}
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
		$this->vars(array(
			'FORUM_ADD' => $fr
		));
		
		if ($doc->prop("is_forum") && $this->is_template("FORUM_POST"))
		{
			if ($num_comments>0)
			{
				$this->db_query("SELECT id, name, url,  time, comment FROM comments WHERE board_id = ".$doc->id() ." ORDER BY time ASC");
				
				while($row = $this->db_next())
				{
					$s_comment = $row["comment"];
					$this->_parse_youtube_links(& $s_comment);
					$s_name = $row["name"];
					$s_url = $row["url"];
					if (strlen($s_url)>0)
					{
						$s_name = html::href(array(
							"caption" => $s_name,
                                "url" => $s_url,
						));
					}
					$this->dequote(&$s_comment);
					$this->vars(array(
						"id" => $row["id"],
						"name" => $s_name,
						"post_created_hr" => $this->get_date_human_readable( $row["time"]),
						"comment" => $s_comment,
					));
					$tmp .= $this->parse("FORUM_POST");
					
					$this->vars(array(
						"FORUM_POST" => $tmp,
					));
					$this->parse();
				}
			}
		}
	}

	function _do_charset($doc)
	{
		if ($this->template_has_var("charset"))
		{
			$_langs = get_instance("languages");
			$_ld = $_langs->fetch(aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_id") : aw_global_get("lang_id"));
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
		if (!$this->prog_acl() || !empty($_SESSION["no_display_site_editing"]))
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
		if (isset($_SESSION["site_admin"]["cut_doc"]) && $this->can("view", $_SESSION["site_admin"]["cut_doc"]))
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
		$u7s = "";
		if ($doc->prop("user7") != "")
		{
			$u7s = $this->parse("user7_sub");
		}
		$u8s = "";
		if ($doc->prop("user8") != "")
		{
			$u8s = $this->parse("user8_sub");
		}
		$u9s = "";
		if ($doc->prop("user9") != "")
		{
			$u9s = $this->parse("user9_sub");
		}
		$u10s = "";
		if ($doc->prop("user10") != "")
		{
			$u6s = $this->parse("user10_sub");
		}
		$u6s = "";
		if ($doc->prop("user11") != "")
		{
			$u11s = $this->parse("user11_sub");
		}
		$u12s = "";
		if ($doc->prop("user12") != "")
		{
			$u6s = $this->parse("user12_sub");
		}
		$u13s = "";
		if ($doc->prop("user13") != "")
		{
			$u6s = $this->parse("user13_sub");
		}
		$u14s = "";
		if ($doc->prop("user14") != "")
		{
			$u6s = $this->parse("user14_sub");
		}
		$u15s = "";
		if ($doc->prop("user15") != "")
		{
			$u6s = $this->parse("user15_sub");
		}
		$u16s = "";
		if ($doc->prop("user16") != "")
		{
			$u6s = $this->parse("user16_sub");
		}
		$ut2 = "";
		if ($doc->prop("userta2") != "")
		{
			$ut2 = $this->parse("userta2_sub");
		}
		$ut3 = "";
		if ($doc->prop("userta3") != "")
		{
			$ut3 = $this->parse("userta3_sub");
		}
		$ut4 = "";
		if ($doc->prop("userta4") != "")
		{
			$ut4 = $this->parse("userta4_sub");
		}
		$ut5 = "";
		if ($doc->prop("userta5") != "")
		{
			$ut5 = $this->parse("userta5_sub");
		}
		$ut6 = "";
		if ($doc->prop("userta6") != "")
		{
			$ut6 = $this->parse("userta6_sub");
		}
		$this->vars(array(
			"user1_sub" => $u1s,
			"user2_sub" => $u2s,
			"user3_sub" => $u3s,
			"user4_sub" => $u4s,
			"user5_sub" => $u5s,
			"user6_sub" => $u6s,
			"user7_sub" => $u7s,
			"user8_sub" => $u8s,
			"user9_sub" => $u9s,
			"user10_sub" => $u10s,
			"user11_sub" => $u11s,
			"user12_sub" => $u12s,
			"user13_sub" => $u13s,
			"user14_sub" => $u14s,
			"user15_sub" => $u15s,
			"user16_sub" => $u16s,
			"userta2_sub" => $ut2,
			"userta3_sub" => $ut3,
			"userta4_sub" => $ut4,
			"userta5_sub" => $ut5,
			"userta6_sub" => $ut6
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

                if (!empty($_GET["path"]))
                {
                        $new_path = array();
                        $path_ids = explode(",", $_GET["path"]);
                        foreach($path_ids as $_path_id)
                        {
                                $new_path[] = $_path_id;
                                $pio = obj($_path_id);
                                if ($pio->brother_of() == $doc->parent())
                                {
                                        break;
                                }
                        }
                        $doc_link = aw_ini_get("baseurl")."/?section=".$doc->id()."&path=".join(",",$new_path).",".$doc->id();
                }

		return $doc_link;
	}
	
	// todo 2 viimast if'i
	function get_date_human_readable($i_timestamp_created)
	{
		$a_months = array(
			1=>t("jaanuar"),
			2=>t("veebruar"),
			3=>t("m�rts"),
			4=>t("aprill"),
			5=>t("mai"),
			6=>t("juuni"),
			7=>t("juuli"),
			8=>t("august"),
			9=>t("september"),
			10=>t("oktoober"),
			11=>t("november"),
			12=>t("detsember")
		);
	
		$i_time_from_created_to_current_time = time() - $i_timestamp_created;
		
		if ($i_time_from_created_to_current_time < 60)
		{
			return t("Just postitatud");
		}
		else if ($i_time_from_created_to_current_time < 60*60)
		{
			$i_minutes = floor($i_time_from_created_to_current_time / 60);
			if ($i_minutes == 1)
			{
				return t(sprintf("%s minut tagasi",$i_minutes));
			}
			else
			{
				return t(sprintf("%s minutit tagasi",$i_minutes));
			}
		}
		else if ($i_time_from_created_to_current_time < 60*60*24)
		{
			$i_hours = floor($i_time_from_created_to_current_time / 60 / 60);
			if ($i_hours == 1)
			{
				return t(sprintf("%s tund tagasi",$i_hours));
			}
			else
			{
				return t(sprintf("%s tundi tagasi",$i_hours));
			}
		}
		else if ($i_time_from_created_to_current_time < 60*60*24*31)
		{
			$i_days = floor($i_time_from_created_to_current_time / 60 / 60 / 24);
			if ($i_days == 1)
			{
				return t(sprintf("%s p&auml;ev tagasi",$i_days));
			}
			else
			{
				return t(sprintf("%s p&auml;eva tagasi",$i_days));
			}
		}
		else if (date("Y", $i_timestamp_created) == date("Y", time() ))
		{
			return date("j", $i_timestamp_created).". ".$a_months[date("n", $i_timestamp_created)];
		}
		else 
		{
			return date("j", $i_timestamp_created).". ".$a_months[date("n", $i_timestamp_created)]." ".date("Y", $i_timestamp_created);
		}
	}
	
	function parse_keywords($doc)
	{
		// parse subs KEYWORD_BEGIN, KEYWORD and KEYWORD_END
		if ($doc->prop("keywords") != "")
		{
			// if commas are not used then ake spaces as separators
			if (strpos($doc->prop("keywords"), ",")===false)
			{
				$a_keywords = explode(" ", $doc->prop("keywords"));
			}
			else
			{
				$a_keywords = explode(",", $doc->prop("keywords"));
			}
			
			$tmp = "";
			for ($i=0;$i<count($a_keywords);$i++)
			{
				if ($i==0 && $this->is_template("KEYWORD_BEGIN"))
				{
					$this->vars(array(
						"text" => $a_keywords[$i],
						"link" => "link",
					));
					
					$tmp .= trim($this->parse("KEYWORD_BEGIN"));
					continue;
				}
				
				if ($i<count($a_keywords)-1 && $this->is_template("KEYWORD"))
				{
					$this->vars(array(
						"text" =>  $a_keywords[$i],
						"link" => "link",
					));
					
					$tmp .= trim($this->parse("KEYWORD"));
					continue;
				}
				
				if ($i==count($a_keywords)-1 && $this->is_template("KEYWORD_END"))
				{
					$this->vars(array(
						"text" =>  $a_keywords[$i],
						"link" => "link",
					));
					
					$tmp .= trim($this->parse("KEYWORD_END"));
					continue;
				}
				
				$this->vars(array(
						"text" =>  $a_keywords[$i],
						"link" => "link",
					));
				
				$tmp .= trim($this->parse("KEYWORD"));
			}
			// now try to put the keywords to template
			if ($this->is_template("KEYWORD_BEGIN"))
			{
				$this->vars(array(
						"KEYWORD_BEGIN" => $tmp,
				));
			}
			else if ($this->is_template("KEYWORD"))
			{
				$this->vars(array(
						"KEYWORD" => $tmp,
				));
			}
			
			
		}

	}
}
