<?php
// $Header: /home/cvs/automatweb_dev/classes/mailinglist/Attic/ml_member.aw,v 1.22 2003/11/26 12:22:39 duke Exp $
// ml_member.aw - Mailing list member

/*
	@default table=objects
	@default group=general

	@property conf_obj type=objpicker clid=CL_ML_LIST_CONF field=meta method=serialize newonly=1
	@caption Vali konfiguratsioon

	@property fchange type=text store=no editonly=1 field=meta method=serialize
	@caption Muuda

	@property name type=textbox table=ml_users
	@caption Nimi

	@property mail type=textbox table=ml_users
	@caption E-post

	@classinfo syslog_type=ST_MAILINGLIST_MEMBER
	@classinfo no_status=1

	@tableinfo ml_users index=id master_table=objects master_index=oid
*/

class ml_member extends class_base
{
	function ml_member()
	{
		$this->init(array(
			"tpldir" => "mailinglist/ml_member",
			"clid" => CL_ML_MEMBER,
		));
		lc_load("definition");
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "comment":
				$retval = PROP_IGNORE;
				break;

			case "mail":
				/*
				if ("" == $arr["obj_inst"]->prop("conf_obj"))
				{
					$retval = PROP_IGNORE;
				};
				*/
				break;
			case "fchange":
				if (strlen($arr["obj_inst"]->meta("email")) > 0)
				{
					return PROP_IGNORE;
				};
				$conf_obj = $arr["obj_inst"]->prop("conf_obj");
				if (!empty($conf_obj))
				{
					$mlc_inst = get_instance("mailinglist/ml_list_conf");
					$fl = $mlc_inst->get_forms_by_id($conf_obj);

					$fid = $arr["request"]["fid"];
					// if fid is set, use that, if not, take the forst from the conf
					if (!$fid)
					{
						list($fid, ) = each($fl);
					}
					$f = get_instance("formgen/form");
					$meta = $arr["obj_inst"]->meta();
					$fparse = $f->gen_preview(array(
						"id" => $fid,
						"entry_id" => $meta["form_entries"][$fid],
						"reforb" => $this->mk_reforb("submit",array(
							"id" => $arr["obj_inst"]->id(),
							"fid" => $fid,
							"group" => $this->group,
				
						))
					));

					$this->read_template("member_change.tpl");
					$this->group = $data["group"];
					$this->vars(array(
						"editform" => $fparse,
						"selecter" => $this->make_form_selecter($fl, $arr["obj_inst"]->id(), $fid),
						"l_sent" => $this->mk_my_orb("sent",array("id" => $arr["obj_inst"]->id(),"lid" => $lid)),
					));
					$data["value"] = $this->parse();
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;
		}
		return $retval;
	}

	function set_property($arr)
        {
                $data = &$arr["prop"];
                $retval = PROP_OK;
                switch($data["name"])
                {
			case "conf_obj":
				// dunno really, but that was done in the old orb_submit_new
				$ml_inst = get_instance("mailinglist/ml_list");
				$ml_inst->flush_member_cache();
				break;
			case "fchange":
				if ("" != $arr["obj_inst"]->prop("conf_obj"))
				{
					$this->handle_submit($args["request"]);
				};	
				break;

		}
		return $retval;
	}

	////
	//! Händleb muutmist
	function handle_submit($arr)
	{
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
					$name[$elid] = $el->get_value();
					break;
				}
			}
		}

		// now sort the damn things
		$ordar = $mlc_inst->get_name_els_order($ob["meta"]["conf_obj"]);
		$postar = $mlc_inst->get_name_els_seps($ob["meta"]["conf_obj"]);

		asort($ordar);
		$tt = array();
		foreach($ordar as $elid => $ord)
		{
			$tt[] = $name[$elid];
			if ($postar[$elid] != "")
			{
				$tt[] = $postar[$elid];
			}
			else
			{
				$tt[] = " ";
			}
		}

		$namestr = join("", $tt);

		$this->upd_object(array(
			"oid" => $id, 
			"name" => $namestr, 
			"metadata" => $ob["meta"]
		));

		$ml_inst = get_instance("mailinglist/ml_list");
		$ml_inst->flush_member_cache();
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
		$r["message"]=str_replace("\n","<br />",$r["message"]);
		
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
					"link" => $this->mk_my_orb("change", array("id" => $id, "fid" => $_fid, "group" => $this->group))
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
		if (!$parent)
		{
//			echo "create_member::no_parent! <br />";
			return;
		}
		if (!$conf)
		{
//			echo "create_member::no_conf! <br />";
			return;
		}
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
		$this->upd_object(array(
								"oid" => $id,
								"metadata" => $dat
								));

		$this->update_member_name($id);
		$ml_inst = get_instance("mailinglist/ml_list");
		$ml_inst->flush_member_cache();
		return $id;
	}

	////
	// email(string) - email addy
	// folder(string) - id of the folder to check
	function check_member($args = array())
	{
		$this->quote($args);
		extract($args);
		$q = "SELECT oid FROM objects LEFT JOIN ml_users ON (objects.oid = ml_users.id) WHERE mail = '$email' AND parent = '$folder' AND status != 0";
		$this->db_query($q);
		return $this->db_next();
	}

	////
	// !Creates a new subscribe, the other version, deals with members with no config form
	// name - name of the subscriber
	// email - email addy
	// list_id - id of the list to use for subscribing
	function subscribe_member_to_list($args = array())
	{
		// it would be _really_, _really_ nice if I could init 
		// the data from the class_base, but it's not yet possible
		$this->quote($args);
		$name = $args["name"];
		$email = $args["email"];
		$list_id = $args["list_id"];

		$list_obj = $this->get_object(array(
			"oid" => $list_id,
			"class_id" => CL_ML_LIST,
		));

		$section = aw_global_get("section");
		
		if (empty($list_obj["meta"]["def_user_folder"]))
		{
			return $this->cfg["baseurl"] . "/" . $section;
		};

		$fldr = $list_obj["meta"]["def_user_folder"];
		$status = STAT_ACTIVE;



		
		// I need to validate that stuff as well
		if (!empty($list_obj["meta"]["confirm_subscribe"]))
		{
			// generate the confirm code
			$status = STAT_DEACTIVE;
			$ts = time();
			$hash = substr(gen_uniq_id(),0,15);
			// now I need to generate the confirm url
			$url = $this->mk_my_orb("confirmsub",array("hash" => $hash,"addr" => $email));
		};
			
		$objname = $name . " <" . $email . ">";

		if (!$this->check_member(array("email" => $email,"folder" => $fldr)))
		{
			$objname = htmlspecialchars($objname);
			// Why do we duplicate name and email in object metadata?
			$new_id = $this->new_object(array(
				"parent" => $list_obj["meta"]["def_user_folder"],
				"class_id" => $this->clid,
				"name" => $objname,
				"status" => $status,
				"metadata" => array(
					"name" => $name,
					"email" => $email,
					"hash" => $hash,
					"time" => $ts,
				),
				"no_flush" => 1,
			));
			$q = "INSERT INTO ml_users (name,mail,id) VALUES ('$name','$email','$new_id')";
			$this->db_query($q);
		};
		
		if (!empty($list_obj["meta"]["confirm_subscribe"]) && !empty($list_obj["meta"]["confirm_subscribe_msg"]))
		{
			// now generate and send the bloody message
			$msg = get_instance("messenger/mail_message");
			$msg->process_and_deliver(array(
				"id" => $list_obj["meta"]["confirm_subscribe_msg"],
				"to" => $objname,
				"replacements" => array(
					"#list#" => parse_obj_name($list_obj["name"]),
					"#url#" => $url,
				),
			));
		}

		return $this->cfg["baseurl"] . "/" . $section;

	}

	////
	// !Removes a member from list
	// email - email addy
	// list_id - id of the list to unsubscribe from
	function unsubscribe_member_from_list($args = array())
	{
		$this->quote($args);
		$email = $args["email"];
		$list_id = $args["list_id"];

		$list_obj = $this->get_object(array(
			"oid" => $list_id,
			"class_id" => CL_ML_LIST,
		));
		
		$section = aw_global_get("section");

		if (empty($list_obj["meta"]["def_user_folder"]))
		{
			return $this->cfg["baseurl"] . "/" . $section;
		};

		$fldr = $list_obj["meta"]["def_user_folder"];

		$check = $this->check_member(array("email" => $args["email"],"folder" => $fldr));

		if ($check)
		{
			$this->delete_object($check["oid"],$this->clid);
			$q = "DELETE FROM ml_users WHERE id = '$check[oid]'";
			$this->db_query($q);
		};

		return $this->cfg["baseurl"] . "/" . $section;
	}

	////
	// !Returns member information (e-mail address and variables)
	// lid (int) - list id
	// member (int) - member id
	function get_member_information($args = array())
	{
		extract($args);

		$memberdata = array();
		$mailto = "";

		$list_obj = $this->get_object(array(
			"oid" => $lid,
			"class_id" => CL_ML_LIST,
		));

		$m = $this->get_object(array(
			"oid" => $member,
			"class_id" => CL_ML_MEMBER,
		));

		$replica = $this->db_fetch_row("SELECT name,mail FROM ml_users WHERE id = '$member'");

		$ml_list_inst = get_instance("mailinglist/ml_list");

		if (is_array($replica))
		{
			$mailto = $replica["mail"];
			$memberdata["name"] = $replica["name"];
		}
		// ah, but you see, the list actually _can_ have a config form
		// but the member may not.
		else
		if ($list_obj["meta"]["user_form_conf"])
		{
			$form_inst = get_instance("formgen/form");
			$mailel = $ml_list_inst->get_mailto_element($lid);
			$vars = $ml_list_inst->get_all_varnames($lid);
			$user_forms = $ml_list_inst->get_forms_for_list($lid);
			foreach($user_forms as $uf_id)
			{
				//echo "uf_id = $uf_id <br />";
				if (($uf_eid = $m["meta"]["form_entries"][$uf_id]))
				{
					$uf_inst =& $form_inst->cache_get_form_instance($uf_id);
					$uf_inst->load_entry($uf_eid);
					foreach($vars as $var_id => $var_name)
					{
						$el = $uf_inst->get_element_by_id($var_id);
						if (is_object($el))
						{
							$memberdata[$var_name] = $el->get_value();
							if ($var_id == $mailel)
							{
								$mailto = $memberdata[$var_name];
							};
						}
					}
				}
			}
		}
		else
		{
			// find the plain old member then
		

		}
		return array($mailto,$memberdata);
	}

	function callback_pre_save($arr)
	{
		$request = $arr["request"];
		if (!empty($request["name"]) && !empty($request["mail"]))
		{
			$arr["obj_inst"]->set_name($request["name"] . " &lt;" .$request["mail"] . "&gt;");
		};
	}		
};
?>
