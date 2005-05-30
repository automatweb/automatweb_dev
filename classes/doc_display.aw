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
		$doc_parent = obj($doc->parent());
		$this->tpl_reset();
		$this->tpl_init("automatweb/documents");
		$this->read_any_template($this->_get_template($arr));

		lc_site_load("document",$this);

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
		$this->vars($al->get_vars());
		$_date = $doc->prop("doc_modified") > 1 ? $doc->prop("doc_modified") : $doc->modified();

		$this->vars(array(
			"text" => $text,
			"title" => $doc->prop("title"),
			"author" => $doc->prop("author"),
			"channel" => $doc->prop("channel"),
			"docid" => $doc->id(),
			"date_est" => locale::get_lc_date($_date, LC_DATE_FORMAT_LONG),
			"modified" => date("d.m.Y", $doc->modified()),
			"parent_id" => $doc->parent(),
			"parent_name" => $doc_parent->name(),
			"user1" => $doc->prop("user1"),
			"page_title" => strip_tags($doc->prop("title"))
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
				$text = $doc->prop("lead").aw_ini_get("document.lead_splitter").$doc->prop("content");
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

		return $text;
	}
}
