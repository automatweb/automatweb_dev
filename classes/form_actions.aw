<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_actions.aw,v 2.4 2001/07/26 16:49:56 duke Exp $

// form_actions.aw - creates and executes form actions
lc_load("form");
global $orb_defs;
$orb_defs["form_actions"] = 
array("list_actions"	=> array("function" => "list_actions", "params" => array("id")),
			"add_action"		=> array("function" => "add_action", "params" => array("id")),
			"submit_action" => array("function" => "submit_action", "params" => array()),
			"change_action" => array("function" => "change_action", "params" => array("id", "aid"),"opt" => array( "level"))
			);

class form_actions extends form_base
{
	function form_actions()
	{
		$this->tpl_init("forms");
		$this->db_init();
		$this->sub_merge = 1;
		lc_load("definition");
		global $lc_form;
		if (is_array($lc_form))
		{
			$this->vars($lc_form);}
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
			$this->vars(array("action_id" => $row["id"], "action_name" => $row["name"], "action_comment" => $row["comment"],
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
		$this->vars(array("name" => "", "comment" => "", "email_selected" => "checked", "move_filled_selected" => "", "action_id" => 0,
											"reforb" => $this->mk_reforb("submit_action", array("id" => $id))));
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
						$data = $email;
						break;

					case "move_filled":
						reset($arr);
						$selarr = array();
						while (list($k, $v) = each($arr))
							if (substr($k, 0,3) == "ch_")
								if ($v == 1)
									$selarr[substr($k,3)] = $v;
						$data = serialize($selarr);
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
			$action_id = $this->new_object(array("parent" => $id, "name" => $name, "class_id" => CL_FORM_ACTION, "comment" => $comment, "status" => 2));
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
			$this->raise_error("form->gen_change_action($id, $aid, $level): no such action!", true);
		}

		if ($level < 2)
		{
			$this->init($id, "add_action.tpl", "<a href='".$this->mk_orb("list_actions", array("id" => $id)).LC_FORM_ACTIONS_FORM_ACTIONS_CHANGE_ACTION);
			$this->vars(array("name"									=> $row["name"], 
												"comment"								=> $row["comment"], 
												"email_selected"				=> ($row["type"] == 'email' ? "CHECKED" : ""),
												"move_filled_selected"	=> ($row["type"] == 'move_filled' ? "CHECKED" : ""),
												"join_list_selected"		=> ($row["type"] == 'join_list' ? "CHECKED" : ""),
												"action_id"							=> $id,
												"reforb"								=> $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 1))));
			return $this->parse();
		}
		else
		{
			$this->init($id, "", "<a href='".$this->mk_orb("list_actions", array("id" => $id)).LC_FORM_ACTIONS_FORM_ACTIONS_CHANGE_ACTION);
			switch($row["type"])
			{
				case "email":
					$this->read_template("action_email.tpl");
					$this->vars(array("email" => $row["data"],
														"reforb" => $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 2))));
					return $this->parse();
					break;
				case "move_filled":
					$this->read_template("action_move_filled.tpl");
					$selarr = unserialize($row["data"]);
					$this->db_query("SELECT * FROM objects WHERE class_id = 13 AND objects.status != 0 AND last = $this->id");
					$this->vars(array("LINE" => ""));
					while ($row = $this->db_next())
					{
						$this->vars(array("cat_name"		=> $row["name"], 
															"cat_id"			=> $row["oid"], 
															"cat_comment"	=> $row["comment"], 
															"cat_checked" => ($selarr[$row["oid"]] == 1 ? "CHECKED" : ""),
															"action_id"		=> $id));
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
									$checks[$el["id"]] = $el["name"] == "" ? $el["text"] == "" ? $el["type"] : $el["text"] : $el["name"];
								if ($el["type"] == "textbox")
									$texts[$el["id"]] = $el["name"] == "" ? $el["text"] == "" ? $el["type"] : $el["text"] : $el["name"];
							}
						}
					}
					
					classload("lists");
					$li = new lists;
					$lists = $li->get_op_list();

					$this->vars(array("checkbox"	=> $this->option_list($data["checkbox"],$checks),
														"list"			=> $this->option_list($data["list"],$lists),
														"textbox"		=> $this->option_list($data["textbox"],$texts),
														"name_tb"		=> $this->option_list($data["name_tb"],$texts),
														"action_id"	=> $id,
														"reforb" => $this->mk_reforb("submit_action", array("id" => $id, "action_id" => $aid, "level" => 2))));
					return $this->parse();
					break;
			}
		}
	}
}
?>
