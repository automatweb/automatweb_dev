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

		$text = $this->_get_text($arr, $doc);

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
			"parent_name" => $doc_parent->name()
		));

		$ablock = "";
		if ($doc->prop("author") != "")
		{
			$this->vars(array(
				"ablock" => $this->parse("ablock")
			));
		}

		$ps = "";
		if (( ($doc->prop("show_print")) && (!$_GET["print"]) && $arr["leadonly"] != 1))
		{
			$ps = $this->parse("PRINTANDSEND");
		}

		$this->vars(array(
			"SHOW_TITLE" => ($doc->prop("show_title") == 1 && $doc->prop("title") != "") ? $this->parse("SHOW_TITLE") : "",
			"PRINTANDSEND" => $ps
		));

		$str = $this->parse();

		$al = get_instance("aliasmgr");
		$mt = $doc->meta();
		$al->parse_oo_aliases(
			$doc->id(),
			&$str,
			array(
				"templates" => &$this->templates,
				"meta" => &$mt
			)
		);
		
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
	}

	function _get_text($arr, $doc)
	{
		if ($arr["leadonly"] > -1)
		{
			$text = $doc->prop("lead");
		}
		else
		{
			if ($doc->prop("show_lead"))
			{
				$text = $doc->prop("lead").$this->cfg["lead_splitter"].$doc->prop("content");
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