<?php

class ml_member extends aw_template
{
	function ml_member()
	{
		$this->init("mailinglist/ml_member");
		lc_load("definition");
	}

	////
	//! Näitab uue liikme lisamist
	function orb_new($arr)
	{
		extract($arr);
		$this->mk_path($parent,"Lisa meililisti liige");
		$this->read_template("add.tpl");
		
		$this->vars(array(
			"conf" => $this->picker(0, $this->list_objects(array("class" => CL_ML_LIST_CONF))),
			"reforb" => $this->mk_reforb("submit_new", array("parent" => $parent))
		));
		return $this->parse();
	}

	////
	//! Händleb uue liikme lisamist
	function orb_submit_new($arr)
	{
		extract($arr);

		$id = $this->new_object(array(
			"parent" => $parent,
			"class_id" => CL_ML_MEMBER,
			"metadata" => array(
				"conf_obj" => $conf
			)
		));
		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	//! Händleb muutmist
	function orb_submit_change($arr)
	{
		$this->quote(&$arr);
		extract($arr);
	
		$ob = $this->get_object($id);

		$f = get_instance("formgen/form");
		$f->process_entry(array(
			"id" => $fid,
			"entry_id" => $ob["meta"]["form_entries"][$fid]
		));
		$ob["meta"]["form_entries"][$fid] = $f->entry_id;

		$row = $this->db_fetch_row("SELECT * FROM ml_member2form_entry WHERE member_id = '$ob[brother_of]' AND form_id = '$fid'");
		if (!is_array($row))
		{
			$this->db_query("INSERT INTO ml_member2form_entry (member_id, form_id, entry_id) VALUES('$ob[brother_of]', '$fid', '".$f->entry_id."')");
		}
		else
		{
			$this->db_query("UPDATE ml_member2form_entry SET entry_id = '".$f->entry_id."' WHERE member_id = '$ob[brother_of]' AND form_id = '$fid'");
		}

		// now put together the name of the object and update it
		$mlc_inst = get_instance("mailinglist/ml_list_conf");

		// oh crap. we have to load all the entries here, because the elements may be in any form
		$finst_ar = array();
		$ar = new aw_array($mlc_inst->get_forms_by_id($ob["meta"]["conf_obj"]));
		foreach($ar->get() as $_fid)
		{
			$finst_ar[$_fid] = get_instance("formgen/form");
			$finst_ar[$_fid]->load($_fid);
			if ($ob["meta"]["form_entries"][$_fid])
			{
				$finst_ar[$_fid]->load_entry($ob["meta"]["form_entries"][$_fid]);
			}
		}

		$name = array();
		foreach($mlc_inst->get_name_els_by_id($ob["meta"]["conf_obj"]) as $elid)
		{
			foreach($finst_ar as $_fid => $finst)
			{
				if (is_object($el = $finst->get_element_by_id($elid)))
				{
					$name[] = $el->get_value();
					break;
				}
			}
		}
		$namestr = join(" ", $name);

		$this->upd_object(array(
			"oid" => $id, 
			"name" => $namestr, 
			"metadata" => $ob["meta"]
		));

		$rule=get_instance("mailinglist/ml_rule");
//		$rule->check_entry(array($ob["meta"]["form_entries"][$fid]));

		$this->_log("mlist","muutis liiget $namestr");
		return $this->mk_my_orb("change",array("id" => $id,"fid" => $fid));
	}

	////
	//! Näitab liikme muutmist
	function orb_change($ar)
	{
		extract($ar);
		$o=$this->get_object($id);
		$this->mk_path($o["parent"],"Muuda meililisti liiget");				

		$mlc_inst = get_instance("mailinglist/ml_list_conf");
		$fl = $mlc_inst->get_forms_by_id($o["meta"]["conf_obj"]);

		// if fid is set, use that, if not, take the forst from the conf
		if (!$fid)
		{
			list($fid, ) = each($fl);
		}
		$f = get_instance("formgen/form");
		$fparse = $f->gen_preview(array(
			"id" => $fid,
			"entry_id" => $o["meta"]["form_entries"][$fid],
			"reforb" => $this->mk_reforb("submit_change",array(
				"id" => $id,
				"fid" => $fid
			))
		));

		$this->read_template("member_change.tpl");
		$this->vars(array(
			"editform" => $fparse,
			"selecter" => $this->make_form_selecter($fl, $id, $fid),
			"l_sent" => $this->mk_my_orb("sent",array("id" => $id,"lid" => $lid)),
		));

		return $this->parse();
	}

	////
	//! Näitab liikmele saadetud meile
	function orb_sent($arr)
	{
		extract($arr);
		$o=$this->get_object($id);
		$link="<a href=\"".$this->mk_my_orb("change",array("id" => $id,"lid" => $lid))."\">Muuda meililisti liiget</a> / Saadetud meilid";
		$this->mk_path($o["parent"],$link);

		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "ml_member",
		));
		$t->define_header("Saadetud meilid",array());
		$t->parse_xml_def($this->cfg["basedir"] . "/xml/mlist/sentmails.xml");

		$q="SELECT * FROM ml_sent_mails WHERE member='$ob[brother_of]'";
		$this->db_query($q);

		while ($row = $this->db_next())
		{
			$this->save_handle();
			$row["eid"]=$row["id"];
			$row["mail"] = $this->db_fetch_field("SELECT name FROM objects WHERE oid='".$row["mail"]."'","name")."(".$row["mail"].")";
			$this->restore_handle();
			$t->define_data($row);
		};

		$t->sort_by();
		return $t->draw();
	}

	////
	//! Näitab täpsemalt ühte liikmele saadetud meili $id
	function orb_sent_show($arr)
	{
		extract($arr);
		$this->read_template("sent_show.tpl");
		
		$q="SELECT * FROM ml_sent_mails WHERE id='$id'";
		$this->db_query($q);

		$r=$this->db_next();

		//	id,mail,member,uid,tm,vars,message,subject,mailfrom
		$r["tm"]=$this->time2date($r["tm"],2);
		$r["message"]=str_replace("\n","<br>",$r["message"]);
		
		$this->vars($r);
		return $this->parse();
	}

	////
	//! Kustutab liikmele saadetud meili logi tablast
	function orb_sent_delete($arr)
	{
		extract($arr);
		$this->db_query("DELETE FROM ml_sent_mails WHERE id='$id'");
		return "<script language='javascript'>opener.history.go(0);window.close();</script>";
	}

	////
	// !draws the bar where you can select the form to fill
	// parameters:
	//    flist - list of form id's to show
	//		id of the member object
	//		fid - the selected form
	function make_form_selecter($flist, $id, $fid)
	{
		$str = "";
		$liststr = join(",", $flist);
		if ($liststr != "")
		{
			$dat = array();

			$this->db_query("SELECT name, oid FROM objects WHERE oid IN ($liststr)");
			while ($row = $this->db_next())
			{
				$dat[$row["oid"]] = $row["name"];
			}

			foreach($flist as $_fid)
			{
				$this->vars(array(
					"fname" => $dat[$_fid],
					"fid" => $_fid,
					"link" => $this->mk_my_orb("change", array("id" => $id, "fid" => $_fid))
				));
				if ($fid == $_fid)
				{
					$str .= $this->parse("ITEM_SEL");
				}
				else
				{
					$str .= $this->parse("ITEM");
				}
			}
		}
		$this->vars(array(
			"ITEM_SEL" => "",
			"ITEM" => ""
		));
		return $str;
	}

	function update_member_name($id)
	{
		$ob = $this->get_object($id, false);
		// now put together the name of the object and update it
		$mlc_inst = get_instance("mailinglist/ml_list_conf");

		// oh crap. we have to load all the entries here, because the elements may be in any form
		$finst_ar = array();
		$ar = new aw_array($mlc_inst->get_forms_by_id($ob["meta"]["conf_obj"]));
		foreach($ar->get() as $_fid)
		{
			$finst_ar[$_fid] = get_instance("formgen/form");
			$finst_ar[$_fid]->load($_fid);
			if ($ob["meta"]["form_entries"][$_fid])
			{
				$finst_ar[$_fid]->load_entry($ob["meta"]["form_entries"][$_fid]);
			}
		}

		$name = array();
		foreach($mlc_inst->get_name_els_by_id($ob["meta"]["conf_obj"]) as $elid)
		{
			foreach($finst_ar as $_fid => $finst)
			{
				if (is_object($el = $finst->get_element_by_id($elid)))
				{
					$name[] = $el->get_value();
					break;
				}
			}
		}
		$namestr = join(" ", $name);
		$this->upd_object(array(
			"oid" => $id,
			"name" => $namestr
		));
	}

	////
	// !creates list member under $parent, using form/entry pairs in $entries, member uses conf object $conf
	function create_member($arr)
	{
		extract($arr);
		$id = $this->new_object(array(
			"parent" => $parent,
			"class_id" => CL_ML_MEMBER,
			"metadata" => array(
				"conf_obj" => $conf
			)
		));

		$dat = array("conf_obj" => $conf);
		foreach($entries as $fid => $eid)
		{
			$dat["form_entries"][$fid] = $eid;
			$this->db_query("INSERT INTO ml_member2form_entry (member_id, form_id, entry_id) VALUES('$id', '$fid', '$eid')");
		}
		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => $dat
		));

		$this->update_member_name($id);
		return $id;
	}
};
?>
