<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_chain.aw,v 2.24 2002/07/23 05:23:41 kristo Exp $
// form_chain.aw - form chains

classload("form_base");

class form_chain extends form_base
{
	function form_chain()
	{
		$this->form_base();
		$this->sub_merge = 1;
		lc_load("definition");
		$this->lc_load("form","lc_form");
	}

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_FORM_CHAIN_ADD_WREATH);
		$this->read_template("add_chain.tpl");

		classload("objects");
		$ob = new objects;
		$this->vars(array(
			"forms" => $this->multiple_option_list(array(),$this->get_list(FTYPE_ENTRY,false,true)),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent,"alias_doc" => $alias_doc)),
			"search_doc" => $this->mk_orb("search_doc", array(),"links"),
			"folders" => $this->picker(0,$ob->get_list())
		));
		return $this->parse();
	}

	////
	// !Submits a new form chain
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
		$ct["rep"] = $rep;

		$ct["after_redirect"] = $after_redirect;
		$ct["after_redirect_url"] = $after_redirect_url;

		$ct["save_folder"] = $save_folder;
		
		$ct["show_reps"] = $show_reps;
		$ct["rep_tbls"] = $rep_tbls;

		$ct["has_calendar"] = $has_calendar;
		$ct["cal_controller"] = $cal_controller;
		$ct["cal_entry_form"] = $cal_entry_form;
		
		$this->chain = $ct;

		uksort($ct["forms"],array($this,"__ch_sort"));
		
		$content = aw_serialize($ct,SERIALIZE_XML);
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
	
		// notify form_calendar
		if ($cal_entry_form)
		{
			$fc = get_instance("form_calendar");
			$fc->upd_calendar(array(
				"cal_id" => $id,
				"form_id" => $cal_entry_form,
				"vform_id" => $cal_controller,
				"active" => $has_calendar,
			));
		}

		$this->db_query("DELETE FROM form2chain WHERE chain_id = $id");
		if (is_array($forms))
		{
			foreach($forms as $fid)
			{
				$this->db_query("INSERT INTO form2chain(form_id,chain_id,ord,rep) values($fid,$id,'".$ct["form_order"][$fid]."','".$rep[$fid]."')");
			}
		}
		echo "endsubmit! <br>";
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

		$ch_forms = array();

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
					$ch_forms[$fid] = $fname;
					$lg.=$this->parse("LANG");
				}
				$this->vars(array(
					"fjrk" => $this->chain["form_order"][$fid],
					"fgoto" => checked($this->chain["gotonext"][$fid]),
					"rep" => checked($this->chain["rep"][$fid]),
					"show_reps" => checked($this->chain["show_reps"][$fid]),
					"rep_tbls" => $this->picker($this->chain["rep_tbls"][$fid], $this->get_list_tables()),
					"LANG" => $lg
				));
				$this->parse("FORM");
			}
		}

		classload("objects");
		$ob = new objects;


		$forms = $this->get_flist(array(
			"type" => FTYPE_ENTRY,
		));
	
		// retrieve a list of forms that can be used for adding events
		$ev_entry_forms = $this->get_flist(array(
			"type" => FTYPE_ENTRY,
			"subtype" => FSUBTYPE_EV_ENTRY,
		));

		// no form should be selected if the user has not made a choice
		// yet, so we add a default choise to the front of both lists
		$default = array("" => "-- Vali --");
		$ch_forms = $default + $ch_forms;
		$ev_entry_forms = $default + $ev_entry_forms;

		$this->vars(array(
			"forms" => $this->multiple_option_list($this->chain["forms"],$forms),
			"name" => $fc["name"],
			"comment" => $fc["comment"],
			"fillonce" => checked($this->chain["fillonce"]),
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
			"search_doc" => $this->mk_orb("search_doc", array(),"links"),
			"after_redirect" => checked($this->chain["after_redirect"] == 1),
			"after_redirect_url" => $this->chain["after_redirect_url"],
			"folders" => $this->picker($this->chain["save_folder"],$ob->get_list()),
			"has_calendar" => checked($this->chain["has_calendar"]),
			//"cal_forms" => $this->picker($this->chain["cal_form"],$selected_forms),
			"cal_controllers" => $this->picker($this->chain["cal_controller"],$ch_forms),
			"cal_entry_forms" => $this->picker($this->chain["cal_entry_form"],$ev_entry_forms),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
		));
		return $this->parse();
	}

	function parse_alias($args = array())
	{
		extract($args);
		global $section;
		$ar = array("id" => $alias["target"], "section" => $section);

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
		extract($arr);
		$this->start_el = $start_el;
		$this->end_el = $end_el;
		$this->start = $start;
		$this->end = $end;
		$ch = $this->load_chain($id);

		if (!$form_id)
		{
			$form_id = $this->get_default_form_in_chain();
		}

		$this->read_template("chain.tpl");

		if ($this->chain["fillonce"] && aw_global_get("uid"))
		{
			// kui seda saab aint yx kord t2ita siis yritame leida selle t2itmise
			$entry_id = $this->db_fetch_field("SELECT id FROM form_chain_entries WHERE chain_id = $id AND uid = '".aw_global_get("uid")."'","id");
		}

//		echo "entry_id = $entry_id <br>";
		if ($entry_id && !$this->chain["rep"][$form_id] && !$form_entry_id)
		{
			$ear = $this->get_chain_entry($entry_id);
			$form_entry_id = $ear[$form_id];
//			echo "ear = <pre>", var_dump($ear),"</pre> <br>";
		}

		$sep = $this->parse("SEP");
		$first = true;
		$lang_id = aw_global_get("lang_id");
		foreach($this->chain["forms"] as $fid)
		{
			if (!$first)
			{
				$ff.=$sep;
			}
			if ($section && $from_alias)
			{
				$url = $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$section."/form_id=".$fid."/entry_id=".$entry_id;
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
		$cur_form = "";
		if ($this->chain["show_reps"][$form_id] && $entry_id)
		{
			// show form table with all the entries for the current chain entry
			$cur_form = $this->show_table_for_chain_entry(array(
				"chain" => $id,
				"chain_entry" => $entry_id,
				"form_id" => $form_id,
				"section" => $section,
				"table" => $this->chain["rep_tbls"][$form_id],
				"attribs" => array(
					"id" => $id,
					"section" => $section,
					"form_id" => $form_id,
					"entry_id" => $entry_id,
				)
			));
		}

		if (!$form_entry_id)
		{
			$led = $GLOBALS["load_entry_data"];
			$lcd = $GLOBALS["load_chain_data"];
		}
		$f = new form;
		$f->set_current_chain_entry($entry_id);
		aw_global_set("current_chain_entry", $entry_id);
		// FIXME: blah
		global $load_chain_data;
		$lcd = $load_chain_data;

//		echo "showng form $form_id entry $form_entry_id $led $lcd <br>";
		$cur_form .= $f->gen_preview(array(
			"id" => $form_id,
			"entry_id" => $form_entry_id, 
			"load_entry_data" => $led,
			"load_chain_data" => $lcd,
			"reforb" => $this->mk_reforb("submit_form", array(
				"id" => $id, 
				"section" => $section, 
				"form_id" => $form_id, 
				"chain_entry_id" => $entry_id,
				"form_entry_id" => $form_entry_id,
				"load_chain_data" => $load_chain_data, 
			))
		));

		$this->vars(array(
			"cur_form" => $cur_form,
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
		return $this->parse();
	}

	////
	// !this is invoked when a form in the chain is submitted
	function submit_form($arr)
	{
		extract($arr);
		$this->load_chain($id);

		// ok, here we must create a new chain_entry if none is specified
		if (!$chain_entry_id)
		{
			$chain_entry_id = $this->new_object(array(
				"parent" => $this->chain["save_folder"],
				"class_id" => CL_CHAIN_ENTRY,
			));

			$this->db_query("INSERT INTO form_chain_entries(id,chain_id)
						VALUES($chain_entry_id,$id)");
		}

		// then we must let formgen process the form entry and then add the entry to the chain. 
		classload("form");

		// if this form is part of form calendar definition and is used for defining
		// periods, then we need to set a special flag so form->process_entry can
		// call form_calendar to update the calendar2timedef table

		// performs some check before actually doing the update

		// processing takes place inside form->process_entry because I have better
		// access to form elements from there
		$update_fcal_timedef = false;

		if ($this->chain["has_calendar"])
		{
			$fc = get_instance("form_calendar");
			$fcal = $fc->get_calendar(array(
				"cal_id" => $id,
			));

			if (is_array($fcal))
			{
				$update_fcal_timedef = $chain_entry_id;
			};

		};

		$f = new form;
		$f->process_entry(array(
				"id" => $form_id,
				"chain_entry_id" => $chain_entry_id,
				"entry_id" => $form_entry_id,
				"update_fcal_timedef" => $update_fcal_timedef,
		));

		// now update the chain entry object with the form entry name
		$this->upd_object(array(
			"oid" => $chain_entry_id,
			"name" => $f->entry_name
		));

		$sbt = $f->get_opt("el_submit");

		$this->add_entry_to_chain($chain_entry_id,$f->entry_id,$form_id);

		$tfid = $form_id;

		// sbt is a reference to the submit button object that was clicked

		// the following code figures out which form in the chain should be
		// shown next
		if ($this->chain["gotonext"][$form_id] && ($sbt["chain_forward"] == 0) && ($sbt["confirm"] == 0))
		{
			$prev = 0;

			// XXX: rewrite this without using breaks

			// need to figure out whether we have to go "back" in chain
			// so we cycle over all the forms in the chain and ...
			foreach($this->chain["forms"] as $fid)
			{
				if ( $sbt["chain_backward"] > 0)
				{
					if ($fid == $form_id)
					{
						// if prev was set in the last cycle
						// set form_id to that
						if ($prev)
						{
							$form_id = $prev;
						}
						// otherwise just drop out. first form
						// can't go back.
						break;
					}
				}

				// default action, go to the next form.
				// but only if it is not the last in chain
				if ($prev == $form_id)
				{
					$form_id = $fid;
					break;
				}

				$prev = $fid;
			}
		}

		// so, if this was the last form in the chain and no_chain_forward is
		// not set, do what we are supposed to to after filling the last form.

		if ($tfid == $form_id && ($sbt["chain_forward"] == 0) )
		{
			// check that if we are after the last form then if the user has selected that we should show the entry then do so
			if ($this->chain["after_show_entry"] == 1 && $this->chain["after_show_op"] > 0  && $this->chain["gotonext"][$form_id] == 1)
			{
				return $this->mk_my_orb("show_entry", array("id" => $form_id,"entry_id" => $f->entry_id,"op_id" => $this->chain["after_show_op"],"section" => $section),"form");
			}
			else
			if ($this->chain["after_redirect"] == 1 && $this->chain["gotonext"][$form_id] == 1)
			{
				return $this->chain["after_redirect_url"];
			}
		}

		// has something to do with embedding
		if ($section && $GLOBALS["class"] == "")
		{
			$url = $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$section."/form_id=".$form_id."/entry_id=".$chain_entry_id;
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
		$ar = aw_unserialize($row["ids"]);
		$ar[$form_id] = $form_entry_id;
		$tx = aw_serialize($ar,SERIALIZE_XML);
		$this->quote(&$tx);
		$this->db_query("UPDATE form_chain_entries SET ids = '$tx',tm = '".time()."' WHERE id = $chain_entry_id");
	}

	function show_chain_entries($arr)
	{
		extract($arr);
		$ob = $this->load_chain($id);
		$this->mk_path($ob["parent"],"<a href='".$this->mk_my_orb("change", array("id" => $id)).LC_FORM_CHAIN_CHANGE_WREATH_INPUT);
		$this->read_template("show_chain_entries.tpl");

		$this->db_query("SELECT form_chain_entries.*,objects.created AS tm,objects.createdby AS uid FROM form_chain_entries LEFT JOIN objects ON objects.oid = form_chain_entries.id WHERE chain_id = $id AND objects.status != 0");
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
		return $this->parse();
	}

	function delete_entry($arr)
	{
		extract($arr);

		// get all form entries for chain entry and delete all of those as well.
		$e = $this->get_chain_entry($entry_id);
		foreach($e as $fid => $fentry_id)
		{
			$this->delete_object($fentry_id);
			$this->db_query("UPDATE form_".$fid."_entries SET chain_id = NULL WHERE id = $fentry_id");
		}

		// now delete chain entry object
		$this->delete_object($entry_id);
		header("Location: ".$this->mk_my_orb("show_chain_entries", array("id" => $id)));
	}

	function convchainentries($arr)
	{
		// for each chain
		set_time_limit(0);
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_FORM_CHAIN." AND status != 0");
		while ($row = $this->db_next())
		{
			$f = new form_chain;
			$f->load_chain($row["oid"]);

			echo "chain $row[oid] <br>";
			flush();
			// for each entry in chain
			$this->save_handle();
			$this->db_query("SELECT * FROM form_chain_entries WHERE chain_id = ".$row["oid"]);
			while ($erow = $this->db_next())
			{
				$this->save_handle();
				// create object for it
				echo "entry $erow[id] for chain $row[oid] <br>";
				$chain_entry_id = $this->new_object(array(
					"parent" => $f->chain["save_folder"],
					"class_id" => CL_CHAIN_ENTRY,
				));
				
				// update id in form_chain_entries table and all form_xxx_entries tables for the chain entry
				$e = $this->get_chain_entry($erow["id"]);
				foreach($e as $fid => $fentry_id)
				{
					echo "form_entry $fentry_id for form $fid of chain_entry $erow[id] for chain $row[oid] <br>";
					flush();
					$this->db_query("UPDATE form_".$fid."_entries SET chain_id = $chain_entry_id WHERE chain_id = $erow[id]");
				}

				$this->db_query("UPDATE form_chain_entries SET id = $chain_entry_id WHERE id = $erow[id] AND chain_id = $row[oid]");
				$this->restore_handle();
			}
			$this->restore_handle();
		}
	}

	////
	// !this shows all the form $form_id entries for the chain $chain for the entry $chain_entry with form table $table
	function show_table_for_chain_entry($arr)
	{
		extract($arr);
		classload("form_table");
		$ft = new form_table;
		// now get all entries of the form for the chain entry
		$entdat = $ft->get_entries(array(
			"id" => $form_id,
			"all_data" => true,
			"chain_id" => $chain_entry
		));

		$ft->start_table($table);

		if ($this->start_el)
		{
			// filter out everything outside the range that interests us
			// GOD DAMMIT, this sucks
			$new_entdat = array();
			foreach($entdat as $row)
			{
				$rstart = $row["el_" . $this->start_el];
				$rend = $row["el_" . $this->end_el];
				if ( ($rstart > $this->start) && ($rend < $this->end) )
				{
					$new_entdat[] = $row;
				};
			}
			$entdat = $new_entdat;
		}

		foreach($entdat as $row)
		{
			$ft->row_data($row,$form_id,$section,0,$chain,$chain_entry);
		}

		return $ft->finalize_table();
	}

	function delreplicas()
	{
		set_time_limit(0);
		// for each form
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_FORM);
		while ($row = $this->db_next())
		{
			echo "form $row[oid] <br>";
			flush();
			$this->save_handle();
			// for each entry
			$this->db_query("SELECT id FROM form_".$row["oid"]."_entries");
			while ($erow = $this->db_next())
			{
				echo "entry $erow[id] <br>";
				flush();
				// update form_entries table's form_id
				$this->save_handle();
				$this->db_query("UPDATE form_entries SET form_id = $row[oid] WHERE id = $erow[id]");
				$this->restore_handle();
			}
			$this->restore_handle();
		}
	}
}
?>
