<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_actions.aw,v 2.10 2002/06/28 00:17:56 duke Exp $

// form_actions.aw - creates and executes form actions

class form_actions extends form_base
{
	function form_actions()
	{
		$this->form_base();
		$this->sub_merge = 1;
		lc_load("definition");
		$this->lc_load("form","lc_form");
	}

	////
	// !listst the actions for form $id
	function list_actions($arr)
	{
		extract($arr);
		$this->init($id, "actions.tpl", LC_FORM_ACTIONS_FORM_ACTIONS);

		$this->db_query("SELECT *,objects.name as name, objects.comment as comment 
										 FROM form_actions
										 LEFT JOIN objects ON objects.oid = form_actions.id 
										 WHERE objects.status != 0 AND form_actions.form_id = $id");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"action_id" => $row["id"], 
				"action_name" => $row["name"], 
				"action_comment" => $row["comment"],
				"change"	=> $this->mk_orb("change_action", array("id" => $id, "aid" => $row["id"])),
				"delete"	=> $this->mk_orb("delete_action", array("id" => $id, "aid" => $row["id"]))
			));
			$this->parse("LINE");
		}
		$this->vars(array("add"		=> $this->mk_orb("add_action", array("id" => $id))));
		return $this->do_menu_return();
	}

	////
	// !Generates the form for adding actions
	function add_action($arr)
	{
		extract($arr);
		$this->init($id, "add_action.tpl", "<a href='".$this->mk_orb("list_actions", array("id" => $id)).LC_FORM_ACTIONS_ADD_ACTIONS);
		$this->vars(array(
			"name" => "", 
			"comment" => "", 
			"email_selected" => "checked", 
			"move_filled_selected" => "", 
			"action_id" => 0,
			"reforb" => $this->mk_reforb("submit_action", array("id" => $id))
		));
		return $this->parse();
	}

	////
	// !saves or adds the submitted action
	function submit_action($arr)
	{
		$this->dequote(&$arr);
		extract($arr);

		if ($action_id)
		{
			// alter
			$data = "";
			if ($level == 2)
			{
				$type = $this->db_fetch_field("SELECT type FROM form_actions WHERE id = $action_id", "type");
				switch($type)
				{
					case "email":
						$data["email"] = $email;
						$data["op_id"] = $op_id;
						$data["l_section"] = $l_section;
						$la = new languages;
						$ls = $la->listall();
						foreach($ls as $ld)
						{
							$data["subj"][$ld["id"]] = $subj[$ld["id"]];
						}
						$data = serialize($data);
						break;

					case "move_filled":
						reset($arr);
						$selarr = array();
						while (list($k, $v) = each($arr))
						{
							if (substr($k, 0,3) == "ch_")
							{
								if ($v == 1)
								{
									$selarr[substr($k,3)] = $v;
								}
							}
						}
						$data = serialize($selarr);
						break;

					case "email_form":
						$data = array();
						$data["srcform"] = $arr["srcform"];
						$data["srcfield"] = $arr["srcfield"];
						$data["subject"] = $arr["subject"];
						$data["from"] = $arr["from"];
						$data["output"] = $arr["output"];
						$data["sbt_bind"] = $arr["sbt_bind"];
						$data = serialize($data);
						break;

					case "join_list":
						$data["list"] = $j_list;
						$data["checkbox"] = $j_checkbox;
						$data["textbox"] = $j_textbox;
						$data["name_tb"] = $j_name_tb;
						$data = serialize($data);
						break;
				}
				$this->db_query("UPDATE form_actions SET data = '$data' WHERE id = $action_id");
				$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $action_id","name");
				$this->_log("form",sprintf(LC_FORM_ACTIONS_CHANGED_ACTION,$this->name,$name));
				return $this->mk_orb("change_action", array("id" => $id, "aid" => $action_id, "level" => 2));
			}
			else
			{
				$this->upd_object(array("name" => $name, "comment" => $comment ,"oid" => $action_id));
				$this->db_query("UPDATE form_actions SET type = '$type' WHERE id = $action_id");
				return $this->mk_orb("change_action", array("id" => $id, "aid" => $action_id, "level" => 2));
			}
		}
		else
		{
			// add
			$action_id = $this->new_object(array(
				"parent" => $id, 
				"name" => $name, 
				"class_id" => CL_FORM_ACTION, 
				"comment" => $comment, 
				"status" => 2
			));
			$this->db_query("INSERT INTO form_actions(id,form_id,type) VALUES($action_id, $id, '$type')");
			$this->_log("form",sprintf(LC_FORM_ACTIONS_ADDED_FORM_ACTIONS,$id,$name));
			return $this->mk_orb("change_action", array("id" => $id, "aid" => $action_id, "level" => 2));
		}
	}

	////
	// !generates the html for changing action $aid of form $id , page $level
	function change_action($arr)
	{
		extract($arr);
		$this->db_query("SELECT form_actions.*,objects.name as name, objects.comment as comment FROM form_actions
										 LEFT JOIN objects ON objects.oid = form_actions.id
										 WHERE objects.oid = $aid");
		if (!($row = $this->db_next()))
		{
			$this->raise_error(ERR_FG_NOACTION,"form->gen_change_action($id, $aid, $level): no such action!", true);
		}

		if ($level < 2)
		{
			$this->init($id, "add_action.tpl", "<a href='".$this->mk_orb("list_actions", array("id" => $id)).LC_FORM_ACTIONS_FORM_ACTIONS_CHANGE_ACTION);
			$this->vars(array(
				"name"									=> $row["name"], 
				"comment"								=> $row["comment"], 
				"email_selected"				=> checked($row["type"] == 'email'),
				"move_filled_selected"	=> checked($row["type"] == 'move_filled'),
				"join_list_selected"		=> checked($row["type"] == 'join_list'),
				"email_form"						=> checked($row["type"] == 'email_form'),
				"action_id"							=> $id,
				"reforb"								=> $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 1))
			));
			return $this->parse();
		}
		else
		{
			$this->init($id, "", "<a href='".$this->mk_orb("list_actions", array("id" => $id)).LC_FORM_ACTIONS_FORM_ACTIONS_CHANGE_ACTION);
			switch($row["type"])
			{
				case "email":
					$this->read_template("action_email.tpl");
					$try = unserialize($row["data"]);
					if (is_array($try))
					{
						$data = $try;
					}
					else
					{
						$data = array("email" => $data);
					}
					$opar = $this->get_op_list($id);
					classload("objects");
					$ob = new db_objects();
					classload("languages");
					$la = new languages;
					$ls = $la->listall();
					foreach($ls as $ld)
					{
						$this->vars(array(
							"lang_name" => $ld["name"],
							"lang_id" => $ld["id"],
							"subj" => $data["subj"][$ld["id"]]
						));
						$lt.=$this->parse("T_LANG");
						$lc.=$this->parse("LANG");
					}
					$this->vars(array(
						"email" => $data["email"],
						"ops" => $this->picker($data["op_id"],array(0 => "") + (array)$opar[$id]),
						"sec" => $this->picker($data["l_section"],$ob->get_list()),
						"reforb" => $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 2)),
						"T_LANG" => $lt,
						"LANG" => $lc
					));

					return $this->parse();
					break;

				case "email_form":
					$this->read_template("form_email.tpl");
					$data = unserialize($row["data"]);
				
					$_ops = $this->get_op_list($id);

					$ops = array("0" => "-- plain text --") + $_ops[$id];

					$srcforms = $this->get_flist(array(
						"type" => FTYPE_ENTRY,
						"subtype" => FSUBTYPE_EMAIL_ACTION,
					));
					
					$els = $this->get_form_elements(array("id" => $id));
					$sbt_binds = array("0" => "--all--");

					foreach($els as $key => $val)
					{
						if ($val["type"] == "button")
						{
							$sbt_binds[$val["id"]] = $val["name"];
						};

					};


					#$srcforms = array("0" => "--vali--") + $srcforms;

					$srcfields = array();

					// now I have to figure out all the e-mail fields
					if (is_array($srcforms))
					{
						$fg = get_instance("form_base");
						foreach($srcforms as $key => $val)
						{
							if ($key > 0)
							{
								$els = $this->get_form_elements(array("id" => $key));
							foreach($els as $fkey => $fval)
							{
								if ($fval["subtype"] == "email")
								{
									$srcfields[$key] = $fval["id"];
									$srcnames[$fval["id"]] = $fval["name"];
								}

									

							};
							};

						}
					}

					$this->vars(array(
						"srcforms" => $this->picker($data["srcform"],$srcforms),
						"srcfields" => $this->picker($data["srcfield"],$srcnames),
						"outputs" => $this->picker($data["output"],$ops),
						"subject" => $data["subject"],
						"sbt_binds" => $this->picker($data["sbt_bind"],$sbt_binds),
						"from" => $data["from"],
						"reforb" => $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 2)),

					));

					return $this->parse();
					break;

				case "move_filled":
					$this->read_template("action_move_filled.tpl");
					$selarr = unserialize($row["data"]);
					$this->db_query("SELECT * FROM objects WHERE class_id = 13 AND objects.status != 0 AND last = $this->id");
					$this->vars(array("LINE" => ""));
					while ($row = $this->db_next())
					{
						$this->vars(array(
							"cat_name"		=> $row["name"], 
							"cat_id"			=> $row["oid"], 
							"cat_comment"	=> $row["comment"], 
							"cat_checked" => ($selarr[$row["oid"]] == 1 ? "CHECKED" : ""),
							"action_id"		=> $id
						));
						$this->parse("LINE");
					}
					return $this->parse();
					break;
				case "join_list":
					$this->read_template("action_join_list.tpl");
					$data = unserialize($row["data"]);

					$this->load($id);
					for ($row = 0; $row < $this->arr["rows"]; $row++)
					{
						for ($col = 0; $col < $this->arr["cols"]; $col++)
						{
							$elar = $this->arr["contents"][$row][$col]->get_elements();
							reset($elar);
							while (list(,$el) = each($elar))
							{
								if ($el["type"] == "checkbox")
								{
									$checks[$el["id"]] = $el["name"] == "" ? $el["text"] == "" ? $el["type"] : $el["text"] : $el["name"];
								}
								if ($el["type"] == "textbox")
								{
									$texts[$el["id"]] = $el["name"] == "" ? $el["text"] == "" ? $el["type"] : $el["text"] : $el["name"];
								}
							}
						}
					}
					
					classload("lists");
					$li = new lists;
					$lists = $li->get_op_list();

					$this->vars(array(
						"checkbox"	=> $this->option_list($data["checkbox"],$checks),
						"list"			=> $this->option_list($data["list"],$lists),
						"textbox"		=> $this->option_list($data["textbox"],$texts),
						"name_tb"		=> $this->option_list($data["name_tb"],$texts),
						"action_id"	=> $id,
						"reforb" => $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 2))
					));
					return $this->parse();
					break;
			}
		}
	}

	function delete_action($arr)
	{
		extract($arr);
		$this->db_query("DELETE FROM form_actions WHERE id = $aid");
		return $this->mk_my_orb("list_actions",array("id" => $id));
	}

	function do_action($args = array())
	{
		extract($args);
		// retrieve e-mail aadresses to send mail to
		$fid = sprintf("form_%d_entries",$data["srcform"]);
		$did = sprintf("ev_%d",$data["srcfield"]);
		$q = "SELECT $did FROM $fid LEFT JOIN objects ON ($fid.id = objects.oid) WHERE status = 2";

		$this->awm = get_instance("aw_mail");
		if ($data["output"] > 0)
		{
			// we have to form a special html message
			$body=strtr($msg,array("<br>"=>"\r\n","<BR>"=>"\r\n","</p>"=>"\r\n","</P>"=>"\r\n"));

		}
		else
		{
			$body = $msg;
		};

		// we set all the relevant fields later on
		$this->awm->create_message(array(
                        "froma" => "",
                        "fromn" => "",
                        "subject" => "",
                        "to" => "",
                        "body" => $body,
                ));

		if ($data["output"] > 0)
		{
			$this->awm->htmlbodyattach(array("data"=>$msg));
		};


		$this->db_query($q);
		while($row = $this->db_next())
		{
			$addr = $row[$did];
			// don't try to send to invalid addresses
			if (is_email($addr))
			{
				print "sending to $addr<br>";
				$this->awm->set_header("Subject",$data["subject"]);
				$this->awm->set_header("From",$data["from"]);
				$this->awm->set_header("To",$addr);
				$this->awm->set_header("Return-path", $data["from"]);
				$this->awm->gen_mail();
			}
			else
			{
				print "$addr is invalid<br>";
			};
		}

	}

	////
	// !called from do_action - delivers an email message
	function _deliver($args = array())
	{
		extract($args);
	}
}
?>
