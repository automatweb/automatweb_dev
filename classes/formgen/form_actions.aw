<?php
// $Header: /home/cvs/automatweb_dev/classes/formgen/form_actions.aw,v 1.18 2004/02/11 11:50:05 kristo Exp $
// form_actions.aw - creates and executes form actions
classload("formgen/form_base");
class form_actions extends form_base
{
	function form_actions()
	{
		$this->form_base();
		$this->sub_merge = 1;

		$this->actiontype2func = array(
			"join_list" => array("execute" => "do_join_list_action"),
			"email_form" => array("execute" => "do_email_form_action"),
			"email" => array("execute" => "do_email_action"),
			"after_submit_controller" => array("execute" => "do_after_submit_controller_action")
		);
	}

	/** listst the actions for form $id 
		
		@attrib name=list_actions params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function list_actions($arr)
	{
		extract($arr);
		$this->if_init($id, "actions.tpl", LC_FORM_ACTIONS_FORM_ACTIONS);

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

	/** Generates the form for adding actions 
		
		@attrib name=add_action params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function add_action($arr)
	{
		extract($arr);
		$this->if_init($id, "add_action.tpl", "<a href='".$this->mk_orb("list_actions", array("id" => $id)).LC_FORM_ACTIONS_ADD_ACTIONS);
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

	/** saves or adds the submitted action 
		
		@attrib name=submit_action params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_action($arr)
	{
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
						$data["email_el"] = $email_el;
						$data["add_pdf"] = $add_pdf;
						$data["op_id"] = $op_id;
						$data["l_section"] = $l_section;
						$data["no_mail_on_change"] = $no_mail_on_change;
						$data["link_to_change"] = $link_to_change;
						$data["link_caption"] = $link_caption;
						$la = get_instance("languages");
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

					case "after_submit_controller":
						$data["controller"] = $controller;
						$data = serialize($data);
						break;
				}
				$this->db_query("UPDATE form_actions SET data = '$data' WHERE id = $action_id");
				$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $action_id","name");
				$this->_log(ST_FORM_ACTION, SA_CHANGE, $name, $id);
				return $this->mk_orb("change_action", array("id" => $id, "aid" => $action_id, "level" => 2));
			}
			else
			{
				$this->upd_object(array(
					"name" => $name, 
					"comment" => $comment ,
					"oid" => $action_id,
					"metadata" => array(
						"activate_on_button" => $this->make_keys($activate_on_button),
						"controllers" => $this->make_keys($controllers)
					)
				));
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
			$this->_log(ST_FORM_ACTION, SA_ADD, $name, $id);
			return $this->mk_orb("change_action", array("id" => $id, "aid" => $action_id, "level" => 2));
		}
	}

	/** generates the html for changing action $aid of form $id , page $level 
		
		@attrib name=change_action params=name default="0"
		
		@param id required acl="edit;view"
		@param aid required
		@param level optional
		
		@returns
		
		
		@comment
		I would really really like to get rid of that stupid level 2 and settle with one
		form for each page. But that then means that I have to somehow embed the
		form fields for writing name and comment for the action inside all other forms

	**/
	function change_action($arr)
	{
		extract($arr);
		$this->db_query("SELECT form_actions.*,objects.name as name, objects.comment as comment, objects.metadata as metadata FROM form_actions
										 LEFT JOIN objects ON objects.oid = form_actions.id
										 WHERE objects.oid = $aid");
		if (!($row = $this->db_next()))
		{
			$this->raise_error(ERR_FG_NOACTION,"form->gen_change_action($id, $aid, $level): no such action!", true);
		}

		if ($level < 2)
		{
			$meta = aw_unserialize($row["metadata"]);

			$f = get_instance("formgen/form");
			$f->load($id);
			$bts = $f->get_all_elements(array(
				"typematch" => "button"
			));
			$this->if_init($id, "add_action.tpl", "<a href='".$this->mk_orb("list_actions", array("id" => $id)).LC_FORM_ACTIONS_FORM_ACTIONS_CHANGE_ACTION);
			$this->vars(array(
				"name"							=> $row["name"], 
				"comment"						=> $row["comment"], 
				"email_selected"				=> checked($row["type"] == 'email'),
				"move_filled_selected"			=> checked($row["type"] == 'move_filled'),
				"join_list_selected"			=> checked($row["type"] == 'join_list'),
				"email_form"					=> checked($row["type"] == 'email_form'),
				"after_submit_controller"		=> checked($row["type"] == 'after_submit_controller'),
				"activate_on_button" => $this->mpicker($meta["activate_on_button"], $bts),
				"controllers" => $this->mpicker($meta["controllers"], $f->get_list_controllers()),
				"action_id"						=> $id,
				"reforb"						=> $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 1))
			));
			return $this->parse();
		}
		else
		{
			$this->if_init($id, "", "<a href='".$this->mk_orb("list_actions", array("id" => $id)).LC_FORM_ACTIONS_FORM_ACTIONS_CHANGE_ACTION);
			$dt = array("id" => $id,"aid" => $aid,"row" => $row);
			switch($row["type"])
			{
				case "email":
					return $this->_change_email_action($dt);
					break;

				case "email_form":
					return $this->_change_form_email_action($dt);
					break;

				case "join_list":
					return $this->_change_join_list_action($dt);

				case "after_submit_controller":
					return $this->_change_after_submit_controller_action($dt);
			}
		}
	}

	/**  
		
		@attrib name=delete_action params=name default="0"
		
		@param id required
		@param aid required
		
		@returns
		
		
		@comment

	**/
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
		// XXX: what if the form gets data from SQL table?

	}

	////
	// !Generats the form for editing email action
	function _change_email_action($args = array())
	{
		extract($args);
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
		$ob = get_instance("objects");
		$la = get_instance("languages");
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
			"sec" => $this->picker($data["l_section"],$ob->get_list(false, true)),
			"reforb" => $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 2)),
			"T_LANG" => $lt,
			"LANG" => $lc,
			"no_mail_on_change" => checked($data["no_mail_on_change"]),
            "link_to_change" => checked($data["link_to_change"]),
            "link_caption" => $data["link_caption"],
			"add_pdf" => checked($data["add_pdf"]),
			"email_el" => $this->picker($data["email_el"], $this->get_elements_for_forms(array($id), false, true))
		));

		return $this->parse();
	}

	////
	// !Generates the form for editing form based email action
	function _change_form_email_action($args = array())
	{
		extract($args);
		$this->read_template("form_email.tpl");
		$data = unserialize($row["data"]);
				
		$_ops = $this->get_op_list($id);
			
		$ops = array("0" => "-- plain text --");
	
		if (is_array($_ops[$id]))
		{
			$ops = $ops + $_ops[$id];
		}

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


		$srcfields = array();

		// now I have to figure out all the e-mail fields
		if (is_array($srcforms))
		{
			$fg = get_instance("formgen/form_base");
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

	}

	////
	// !Generates a form for subscribing user to a mailing list
	function _change_join_list_action($args = array())
	{
		extract($args);
		$this->read_template("action_join_list.tpl");
		$data = unserialize($row["data"]);

		$finst = get_instance("formgen/form");
		$finst->load($id);

		// make checkboxes
		$checks = $finst->get_element_by_type("checkbox","",true);
		$chk = array();
		foreach($checks as $el)
		{
			$chk[$el->get_id()] = $el->get_el_name() == "" ? $el->get_text() == "" ? $el->get_type() : $el->get_text() : $el->get_el_name();
		}
		// make textboxes
		$textboxs = $finst->get_element_by_type("textbox","",true);
		$txt = array();
		foreach($textboxs as $el)
		{
			$txt[$el->get_id()] = $el->get_el_name() == "" ? $el->get_text() == "" ? $el->get_type() : $el->get_text() : $el->get_el_name();
		}

		$li = get_instance("lists");
		$this->vars(array(
			"checkbox"	=> $this->option_list($data["checkbox"],$chk),
			"list"			=> $this->option_list($data["list"],$li->get_op_list()),
			"textbox"		=> $this->option_list($data["textbox"],$txt),
			"name_tb"		=> $this->option_list($data["name_tb"],$txt),
			"action_id"	=> $id,
			"reforb" => $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 2))
		));
		return $this->parse();
	}

	////
	// !Generates a form for selecting the controller to execute after the form has been submitted
	function _change_after_submit_controller_action($args = array())
	{
		extract($args);
		$this->read_template("action_after_submit_controller.tpl");
		$data = unserialize($row["data"]);

		$lst = $this->list_objects(array("class" => CL_FORM_CONTROLLER, "addempty" => true, "add_folders" => true));
		$this->vars(array(
			"controller" => $this->picker($data["controller"], $lst),
			"reforb" => $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 2))
		));
		return $this->parse();
	}


	////
	// !ececutes form actions, gets called after form submit
	// params:
	// form - reference to the calling form
	// entry_id - submitted form entry id
	function do_actions(&$form, $entry_id)
	{
		$this->db_query("SELECT *, objects.metadata as metadata FROM form_actions LEFT JOIN objects ON objects.oid = form_actions.id WHERE form_id = ".$form->get_id()." AND objects.status != 0");
		while($row = $this->db_next())
		{
			$show = false;
			$meta = aw_unserialize($row["metadata"]);
			if (is_array($meta["activate_on_button"]) && count($meta["activate_on_button"]) > 0)
			{
				foreach($meta["activate_on_button"] as $btid)
				{
					if ($GLOBALS["HTTP_POST_VARS"]["submit"][$btid] != "")
					{
						$show = true;
					}
				}
			}
			else
			{
				$show = true;
			}

			// controllers
			$ca = new aw_array($meta["controllers"]);
			foreach($ca->get() as $ctr)
			{
				$fc = get_instance("formgen/form_controller");
				if (!$fc->eval_controller($ctr, $form->entry, $form))
				{
					$show = false;
				} 
			}

			if ($show)
			{
				$this->save_handle();
				$fname = $this->actiontype2func[$row["type"]]["execute"];
				$data = aw_unserialize($row["data"]);

				$this->$fname($form, $data, $entry_id);
				$this->restore_handle();
			}
		}
	}

	////
	// !ececutes form actions, gets called after form submit
	// params:
	// form - reference to the calling form
	// entry_id - submitted form entry id
	function do_on_delete_actions(&$form, $entry_id)
	{
		$this->db_query("SELECT * FROM form_actions LEFT JOIN objects ON objects.oid = form_actions.id WHERE form_id = ".$form->get_id()." AND objects.status != 0");
		while($row = $this->db_next())
		{
			$this->save_handle();
			$data = aw_unserialize($row["data"]);
			$fname = $this->actiontype2func[$row["type"]]["execute"];

			if ($row["type"] == "after_submit_controller")
			{
				$this->$fname($form, $data, $entry_id);
			}
			$this->restore_handle();
		}
	}

	////
	// !the action through which the user can join a mailinglist
	// params:
	// $form - the instance of the form that was submitted
	// $data - the action data, unpacked
	// $entry_id - submitted form entry id
	function do_join_list_action(&$form, $data, $entry_id)
	{
		if ($data["list"])
		{
			$li = get_instance("mlist");
			$li->mlist($data["list"]);

			// if the checkbox is checked, then add user to list, else remove
			// if no checkbox is set, always add
			if ($form->get_element_value($data["checkbox"], true) == 1 || $data["checkbox"] < 1)
			{
				$li->db_add_user(array(
					"name" => $form->get_element_value($data["name_tb"]), 
					"email" => $form->get_element_value($data["textbox"])
				));
			}
			else
			{
				$li->db_remove_user($form->get_element_value($data["textbox"]));
			};
		}
	}

	////
	// !sends email to an adress from a different form or something
	// params:
	// $form - the instance of the form that was submitted
	// $data - the action data, unpacked
	// $entry_id - submitted form entry id
	function do_email_form_action(&$form, $data, $entry_id)
	{
		if ($data["sbt_bind"] == 0)
		{
			$send = true;
		}
		else
		{
			$send = ($data["sbt_bind"] == $form->el_submit["id"]);
		};

		if (not($send))
		{
			// YIKES
			return;
		};

		$f = get_instance("formgen/form");

		if ($data["output"] > 0)
		{
			// have to create the output
			$msg = $f->show(array(
				"id" => $form->get_id(),
				"entry_id" => $entry_id,
				"op_id" => $data["output"],
			));
		}
		else
		{
			// warning, the following code has a very high
			// suck factor. Some sites have a "update" link
			// by each document. Clickin on that link opens 
			// a new document which contains a FG form that can
			// be used to submit comments about the visited
			// document. That form has an email action and
			// the contents of that e-mail action should
			// contain the referer, the link, from which 
			// the user clicked on the Update button.

			// can this be done in some other way? if so,
			// this should be fixed.

			// oh and last_section is set in the site code
			$last = aw_global_get("last_section");
			if ($last)
			{
				$msg = "Referer: " . $this->cfg["baseurl"] . "/$last\n";
			};

			$f->load($form->get_id());
			$f->load_entry($entry_id);
			$msg .= $f->show_text();
		};

		$this->awm = get_instance("aw_mail");
		if ($data["output"] > 0)
		{
			// we have to form a special html message
			$body = strtr($msg,array("<br />"=>"\r\n","<br />"=>"\r\n","</p>"=>"\r\n","</p>"=>"\r\n"));
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

		$elvals = $this->get_entries_for_element(array(
			"rel_form" => $data["srcform"],
			"rel_element" => $data["srcfield"],
			"rel_unique" => true,
			"ret_values" => true
		));
		foreach($elvals as $addr)
		{
			// don't try to send to invalid addresses
			if (is_email($addr))
			{
//				echo "sending to $addr <br />";
				$this->awm->set_header("Subject",$data["subject"]);
				$this->awm->set_header("From",$data["from"]);
				$this->awm->set_header("To",$addr);
				$this->awm->set_header("Return-path", $data["from"]);
				$this->awm->gen_mail();
			}
			else
			{
				print "$addr is invalid<br />";
			};
		}
	}

	////
	// !sends email about the fact that the form was submitted
	// params:
	// $form - the instance of the form that was submitted
	// $data - the action data, unpacked
	// $entry_id - submitted form entry id
	function do_email_action(&$form, $data, $entry_id)
	{
		if ($data["no_mail_on_change"] && !$form->is_new_entry)
		{
			return;
		}

		if (aw_global_get("uid") != "")
		{
			$us = get_instance("users");
			$uif = $us->fetch(aw_global_get("uid"));
			$jfes = unserialize($uif["join_form_entry"]);

			if (is_array($jfes))
			{
				$app = LC_FORM_BASE_USER.aw_global_get("uid").LC_FORM_BASE_INFO;
				foreach($jfes as $fid => $eid)
				{
					$app .= $this->mk_my_orb("show", array("id" => $fid, "entry_id" => $eid),"form")."\n";
				}
				
				if ($data['link_caption'] != '')
				{
					$app_html .= html::href(array(
						'url' => $this->mk_my_orb("show", array("id" => $fid, "entry_id" => $eid),"form"),
						'caption' => LC_FORM_BASE_USER.aw_global_get("uid").LC_FORM_BASE_INFO
					)).'<br />';
				}
			}
		}
		$f = get_instance("formgen/form");
		if ($data['op_id'] && $data['link_caption'] != '')
		{
			$msg_html = $f->show(array(
				"id" => $form->get_id(),
				"entry_id" => $entry_id,
				"op_id" => $data['op_id']
			));
		}

		$f->load($form->get_id());
		$f->load_entry($entry_id);
		$msg = $f->show_text();

		if (!is_array($data))
		{
			$data = array("email" => $data);
		}

		if ($data['link_to_change'])
		{
			$link_url ="\n".$this->mk_my_orb("show", array(
				"id" => $form->get_id(), 
				"entry_id" => $entry_id, 
				"section" => $data["l_section"]
			), "form", false, false);
		}
		else
		if ($data["op_id"])
		{
			$link_url ="\n".$this->mk_my_orb("show_entry", array(
				"id" => $form->get_id(), 
				"entry_id" => $entry_id, 
				"op_id" => $data["op_id"],
				"section" => $data["l_section"]
			), "form", false, false);
		}
		$link_url = str_replace('/automatweb','',$link_url);

		$subj = $data["subj"][aw_global_get("lang_id")] != "" ? $data["subj"][aw_global_get("lang_id")] :LC_FORM_BASE_ORDER_FROM_AW;

		if ($data['link_caption'] != '')
		{
			$awm = get_instance("aw_mail");
			$awm->create_message(array(
				"froma" => "automatweb@automatweb.com",
				"fromn" => "AutomatWeb",
				"subject" => $subj,
				"to" => $data['email'],
				"body" => $msg.$app.$link_url,
			));
			
			$app = $msg_html.'<br />'.$app_html.'<br />'.html::href(array(
				'url' => $link_url,
				'caption' => $data['link_caption']
			));

			$awm->htmlbodyattach(array("data" => $app));

			if (aw_global_get("fa_mail_priority"))
			{
				$awm->set_header("X-Priority: ".aw_global_get("fa_mail_priority"));
			}

			$fname = "attachment.pdf";
			if (aw_global_get("fa_mail_attach_name") != "")
			{
				$fname = aw_global_get("fa_mail_attach_name");
			}

			if ($data["add_pdf"])
			{
				$co = get_instance("core/converters/html2pdf");
				$pdf = $co->convert(array(
					"source" => $msg_html
				));
				$awm->fattach(array(
					"content" => $pdf,
					"filename" => $fname,
					"contenttype" => "application/pdf",
					"name" => $fname
				));
			}

			$awm->gen_mail();

			if ($data["email_el"] && ($_to = $f->get_element_value($data["email_el"])))
			{
				$awm = get_instance("aw_mail");
				$awm->create_message(array(
					"froma" => "automatweb@automatweb.com",
					"fromn" => "AutomatWeb",
					"subject" => $subj,
					"to" => $_to,
					"body" => $msg.$app.$link_url,
				));
			
				$app = $msg_html.'<br />'.$app_html.'<br />'.html::href(array(
					'url' => $link_url,
					'caption' => $data['link_caption']
				));

				$awm->htmlbodyattach(array("data" => $app));

				if (aw_global_get("fa_mail_priority"))
				{
					$awm->set_header("X-Priority: ".aw_global_get("fa_mail_priority"));
				}

				$fname = "attachment.pdf";
				if (aw_global_get("fa_mail_attach_name") != "")
				{
					$fname = aw_global_get("fa_mail_attach_name");
				}

				if ($data["add_pdf"])
				{
					$co = get_instance("core/converters/html2pdf");
					$pdf = $co->convert(array(
						"source" => $msg_html
					));
					$awm->fattach(array(
						"content" => $pdf,
						"filename" => $fname,
						"contenttype" => "application/pdf",
						"name" => $fname
					));
				}

				$awm->gen_mail();
			}
		}
		else
		{
			$app .= $link_url;
			send_mail($data["email"],$subj, $msg.$app,"From: automatweb@automatweb.com\n");
			if ($data["email_el"] && ($_to = $f->get_element_value($data["email_el"])))
			{
				send_mail($_to,$subj, $msg.$app,"From: automatweb@automatweb.com\n");
			}
		}
	}

	function do_after_submit_controller_action(&$form, $data, $entry_id)
	{
		$cinst = get_instance("formgen/form_controller");
		$cinst->eval_controller($data["controller"],false,$form,false);
	}
}
?>
