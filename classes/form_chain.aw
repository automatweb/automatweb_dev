<?php
lc_load("form");
global $orb_defs;
$orb_defs["form_chain"] = "xml";

classload("form_base");

class form_chain extends form_base
{
	function form_chain()
	{
		$this->form_base();
		$this->sub_merge = 1;
		lc_load("definition");
		global $lc_form;
		if (is_array($lc_form))
		{
			$this->vars($lc_form);}
	}

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_FORM_CHAIN_ADD_WREATH);
		$this->read_template("add_chain.tpl");

		$this->vars(array(
			"forms" => $this->multiple_option_list(array(),$this->get_list(FTYPE_ENTRY,false,true)),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent,"alias_doc" => $alias_doc))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		$ct = array();
		$ct["forms"] = array();
		if (is_array($forms))
		{
			foreach($forms as $fid)
			{
				$ct["forms"][$fid] = $fid;
			}
		}

		$ct["lang_form_names"] = $fname;
		$ct["form_order"] = $fjrk;
		$ct["gotonext"] = $fgoto;

		$ct["fillonce"] = $fillonce;

		$ct["after_show_entry"] = $after_show_entry;
		$ct["after_show_op"] = $after_show_op;

		$ct["during_show_entry"] = $during_show_entry;
		$ct["during_show_op"] = $during_show_op;
		$ct["op_pos"] = $op_pos;

		$this->chain = $ct;
		uksort($ct["forms"],array($this,"__ch_sort"));
		
		classload("xml");
		$x = new xml;
		$content = $x->xml_serialize($ct);
		$this->quote(&$content);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
			$this->db_query("UPDATE form_chains SET content = '$content' WHERE id = $id");
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "name" => $name, "comment" => $comment, "status" => 2, "class_id" => CL_FORM_CHAIN));
			$this->db_query("INSERT INTO form_chains(id,content) VALUES($id,'$content')");
			if ($alias_doc)
			{
				$this->add_alias($alias_doc, $id);
			}
		}

		$this->db_query("DELETE FROM form2chain WHERE chain_id = $id");
		if (is_array($forms))
		{
			foreach($forms as $fid)
			{
				$this->db_query("INSERT INTO form2chain(form_id,chain_id,ord) values($fid,$id,'".$ct["form_order"][$fid]."')");
			}
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function __ch_sort($a,$b)
	{
		if ($this->chain["form_order"][$a] < $this->chain["form_order"][$b])
		{
			return -1;
		}
		else
		if ($this->chain["form_order"][$a] > $this->chain["form_order"][$b])
		{
			return 1;
		}
		return 0;
	}

	function change($arr)
	{
		extract($arr);
		$fc = $this->load_chain($id);
		$this->mk_path($fc["parent"], LC_FORM_CHAIN_CHANGE_WREATH);
		$this->read_template("add_chain.tpl");

		classload("languages");
		$la = new languages;
		$lar = $la->listall();

		foreach($lar as $l)
		{
			$this->vars(array(
				"lang_name" => $l["name"]
			));
			$lh.=$this->parse("LANG_H");
		}

		if (is_array($this->chain["forms"]))
		{
			foreach($this->chain["forms"] as $fid)
			{
				$lg= "";
				foreach($lar as $l)
				{
					if (!isset($this->chain["lang_form_names"][$fid][$l["id"]]))
					{
						$fname = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $fid", "name");
					}
					else
					{
						$fname = $this->chain["lang_form_names"][$fid][$l["id"]];
					}
					$this->vars(array(
						"form_id" => $fid,
						"fname" => $fname,
						"lang_id" => $l["id"]
					));
					$lg.=$this->parse("LANG");
				}
				$this->vars(array(
					"fjrk" => $this->chain["form_order"][$fid],
					"fgoto" => checked($this->chain["gotonext"][$fid]),
					"LANG" => $lg
				));
				$this->parse("FORM");
			}
		}

		$this->vars(array(
			"forms" => $this->multiple_option_list($this->chain["forms"],$this->get_list(FTYPE_ENTRY,false,true)),
			"name" => $fc["name"],
			"comment" => $fc["comment"],
			"fillonce" => checked($this->chain["fillonce"]),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"import" => $this->mk_my_orb("import_chain_entries", array("id" => $id),"form_import"),
			"entries" => $this->mk_my_orb("show_chain_entries", array("id" => $id)),
			"ops" => $this->picker($this->chain["after_show_op"], $this->listall_ops()),
			"after_show_entry" => checked($this->chain["after_show_entry"] == 1),
			"during_show_entry" => checked($this->chain["during_show_entry"] == 1),
			"op_up" => checked($this->chain["op_pos"] == "up"),
			"op_down" => checked($this->chain["op_pos"] == "down"),
			"op_left" => checked($this->chain["op_pos"] == "left"),
			"op_right" => checked($this->chain["op_pos"] == "right"),
			"d_ops" => $this->picker($this->chain["during_show_op"], $this->listall_ops()),
			"LANG_H" => $lh,
		));
		return $this->parse();
	}

	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->formaliases))
		{
			$this->aliases = $this->get_aliases(array(
				"oid" => $oid,
				"type" => array(CL_FORM_CHAIN)
			));
		};
		$f = $this->aliases[$matches[3] - 1];
		global $section;
		$ar = array("id" => $f["target"], "section" => $section);

		if ($GLOBALS["form_id"])
		{
			$ar["form_id"] = $GLOBALS["form_id"];
		}
		if ($GLOBALS["entry_id"])
		{
			$ar["entry_id"] = $GLOBALS["entry_id"];
		}
		$ar["from_alias"] = true;
		$replacement = $this->show($ar);
		return $replacement;
	}

	function get_default_form_in_chain()
	{
		if ($this->chain["default_form"])
		{
			return $this->chain["default_form"];
		}
		if (is_array($this->chain["forms"]))
		{
			reset($this->chain["forms"]);
			list($fid,) = each($this->chain["forms"]);
		}
		return $fid;
	}

	////
	// !shows the form chain
	// args:
	// id - chain id
	// section - the document's id in what we are
	// form_id - the active form in the chain, if omitted the default is opened
	// entry_id - the chain entry id
	function show($arr)
	{
		global $awt;
		$awt->start("form_chain::show");
		$awt->count("form_chain::show");

		extract($arr);
		$ch = $this->load_chain($id);

		if (!$form_id)
		{
			$form_id = $this->get_default_form_in_chain();
		}

		$this->read_template("chain.tpl");

		if ($this->chain["fillonce"])
		{
			// kui seda saab aint yx kord t2ita siis yritame leida selle t2itmise
			$entry_id = $this->db_fetch_field("SELECT id FROM form_chain_entries WHERE chain_id = $id AND uid = '".$GLOBALS["uid"]."'","id");
		}

		if ($entry_id)
		{
			$ear = $this->get_chain_entry($entry_id);
			$form_entry_id = $ear[$form_id];
		}

		$sep = $this->parse("SEP");
		$first = true;
		global $lang_id;
		foreach($this->chain["forms"] as $fid)
		{
			if (!$first)
			{
				$ff.=$sep;
			}
			if ($section && $from_alias)
			{
				$url = $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$section."/form_id=".$fid."/entry_id=".$entry_id;
			}
			else
			{
				$url = $this->mk_my_orb("show", array("id" => $id, "section" => $section, "form_id" => $fid, "entry_id" => $entry_id));
			}
			if (isset($this->chain["lang_form_names"]))
			{
				$name = $this->chain["lang_form_names"][$fid][$lang_id];
			}
			else
			{
				$name = $this->chain["form_names"][$fid];
			}
			$this->vars(array(
				"url" => $url,
				"name" => $name
			));
			if ($fid != $form_id)
			{
				$ff.=$this->parse("FORM");
			}
			else
			{
				$ff.=$this->parse("SEL_FORM");
			}
			$first = false;
		}

		classload("form");
		$f = new form;
		$this->vars(array(
			"cur_form" => $f->gen_preview(array("id" => $form_id,"entry_id" => $form_entry_id, "reforb" => $this->mk_reforb("submit_form", array("id" => $id, "section" => $section, "form_id" => $form_id, "chain_entry_id" => $entry_id,"form_entry_id" => $form_entry_id)))),
			"FORM" => $ff,
			"SEL_FORM" => "",
			"SEP" => ""
		));

		if ($this->chain["during_show_entry"] && $entry_id && $this->chain["during_show_op"])
		{
			// siin on j2relikult $ear array olemas k6ikidest formi sisestustest ja tuleb n2idata v2ljundit valitud kohas
			$show_form_id = 0;
			$this->db_query("SELECT * FROM output2form WHERE op_id = ".$this->chain["during_show_op"]);
			while ($row = $this->db_next())
			{
				if ($ear[$row["form_id"]])
				{
					$show_form_id = $row["form_id"];
					break;
				}
			}
			if ($show_form_id)
			{
				$f = new form;
				$entry = $f->show(array("id" => $show_form_id, "entry_id" => $ear[$show_form_id],"op_id" => $this->chain["during_show_op"]));
				switch ($this->chain["op_pos"])
				{
					case "down":
						$this->vars(array("down_entry" => $entry));
						break;
					case "left":
						$this->vars(array("left_entry" => $entry));
						break;
					case "right":
						$this->vars(array("right_entry" => $entry));
						break;
					case "up":
					default:
						$this->vars(array("up_entry" => $entry));
						break;
				}
			}
		}
		$awt->stop("form_chain::show");
		return $this->parse();
	}

	////
	// !this gets called when a form in the chain is submitted
	function submit_form($arr)
	{
		extract($arr);
		// ok, here we must create a new chain_entry if none is specified
		if (!$chain_entry_id)
		{
			$chain_entry_id = $this->db_fetch_field("SELECT MAX(id) as id FROM form_chain_entries","id")+1;
			$this->db_query("INSERT INTO form_chain_entries(id,chain_id,uid) VALUES($chain_entry_id,$id,'".$GLOBALS["uid"]."')");
		}

		// then we must let formgen process the form entry and then add the entry to the chain. 
		classload("form");
		$f = new form;
		$f->process_entry(array("id" => $form_id, "chain_entry_id" => $chain_entry_id, "entry_id" => $form_entry_id));

		$this->add_entry_to_chain($chain_entry_id,$f->entry_id,$form_id);

		$this->load_chain($id);
		$tfid = $form_id;
		if ($this->chain["gotonext"][$form_id] && !isset($GLOBALS["no_chain_forward"]))
		{
			$prev = 0;
			foreach($this->chain["forms"] as $fid)
			{
				if (isset($GLOBALS["chain_backward"]))
				{
					if ($fid == $form_id)
					{
						if ($prev)
						{
							$form_id = $prev;
						}
						break;
					}
				}

				if ($prev == $form_id)
				{
					$form_id = $fid;
					break;
				}
				$prev = $fid;
			}
		}

		if ($tfid == $form_id && !isset($GLOBALS["no_chain_forward"]))
		{
			// check that if we are after the last form then if the user has selected that we should show the entry then do so
			if ($this->chain["after_show_entry"] == 1 && $this->chain["after_show_op"] > 0  && $this->chain["gotonext"][$form_id] == 1)
			{
				return $this->mk_my_orb("show_entry", array("id" => $form_id,"entry_id" => $f->entry_id,"op_id" => $this->chain["after_show_op"],"section" => $section),"form");
			}
		}

		if ($section && $GLOBALS["class"] == "")
		{
			$url = $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$section."/form_id=".$form_id."/entry_id=".$chain_entry_id;
		}
		else
		{
			$url = $this->mk_my_orb("show", array("id" => $id, "section" => $section, "form_id" => $form_id, "entry_id" => $chain_entry_id));
		}
		return $url;
	}

	function add_entry_to_chain($chain_entry_id,$form_entry_id,$form_id)
	{
		$this->db_query("SELECT * FROM form_chain_entries WHERE id = $chain_entry_id");
		$row = $this->db_next();
		classload("xml");
		$x = new xml;
		$ar = $x->xml_unserialize(array("source" => $row["ids"]));
		$ar[$form_id] = $form_entry_id;
		$tx = $x->xml_serialize($ar);
		$this->quote(&$tx);
		$this->db_query("UPDATE form_chain_entries SET ids = '$tx' WHERE id = $chain_entry_id");
	}

	function show_chain_entries($arr)
	{
		global $awt;
		$awt->start("form_chain::show_chain_entries");
		$awt->count("form_chain::show_chain_entries");

		extract($arr);
		$ob = $this->load_chain($id);
		$this->mk_path($ob["parent"],"<a href='".$this->mk_my_orb("change", array("id" => $id)).LC_FORM_CHAIN_CHANGE_WREATH_INPUT);
		$this->read_template("show_chain_entries.tpl");

		$this->db_query("SELECT * FROM form_chain_entries WHERE chain_id = $id");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"tm" => $this->time2date($row["tm"], 2),
				"uid" => $row["uid"],
				"change" => $this->mk_my_orb("show", array("id" => $id, "entry_id" => $row["id"])),
				"delete" => $this->mk_my_orb("delete_entry", array("id" => $id, "entry_id" => $row["id"])),
			));
			$this->parse("LINE");
		}
		$awt->stop("form_chain::show_chain_entries");
		return $this->parse();
	}

	function delete_entry($arr)
	{
		extract($arr);

/*		$e = $this->get_chain_entry($entry_id);
		foreach($e as $fid => $fentry_id)
		{
			$this->delete_object($fentry_id);
		}*/

		//$this->db_query("DELETE FROM form_chain_entries WHERE id = $entry_id");

		header("Location: ".$this->mk_my_orb("show_chain_entries", array("id" => $id)));
	}
}
?>
