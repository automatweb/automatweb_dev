<?php

class crm_document_base extends class_base
{
	function crm_document_base()
	{
		$this->init();
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "creator":
			case "reader":
				$u = get_instance("users");
				$ui = get_instance(CL_USER);
				$c_uid = $arr["obj_inst"]->createdby();
				if ($c_uid != "")
				{
					$ps = obj($ui->get_person_for_user(obj($u->get_oid_for_uid($c_uid))));
					$co = obj($ui->get_company_for_person($ps));
				}
				else
				{
					$co = obj($ui->get_current_company());
					$ps = obj($ui->get_current_person());
				}

				$c = get_instance(CL_CRM_COMPANY);
				$prop["options"] = $c->get_employee_picker($co);
	
				if ($prop["value"] == "" && $ps)
				{
					$prop["value"] = $ps->id();
				}
				break;

			case "project":
				$i = get_instance(CL_CRM_COMPANY);
				$myp = $i->get_my_projects();
				if (!count($myp))
				{
					$ol = new object_list();
				}
				else
				{
					$ol = new object_list(array("oid" => $myp));
				}
				$prop["options"] = array("" => "") + $ol->names();
				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}
				asort($prop["options"]);
				break;

			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$ol = new object_list(array("oid" => $i->get_my_customers()));
				$prop["options"] = array("" => "") + $ol->names();
				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}
				asort($prop["options"]);
				break;

			case "task":
				$i = get_instance(CL_CRM_COMPANY);
				$tsk = $i->get_my_tasks();
				if (count($tsk))
				{
					$ol = new object_list(array("oid" => $tsk));
					$prop["options"] = array("" => "") + $ol->names();
				}
				else
				{
					$prop["options"] = array("" => "");
				}
				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}
				asort($prop["options"]);
				break;

			case "parts_tb":
				$this->_get_parts_tb($arr);
				break;

			case "acts":
				$this->_acts($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$retval = PROP_OK;

		$prop = &$arr["prop"];
		switch($prop["name"])
		{
			case "acts":
				$this->_save_acts($arr);
				break;
		}
		return $retval;
	}	

	function _get_parts_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		/*$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Lisa isik"),
			"action" => "add_persons_to_flow"
		));*/
		/*$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Eemalda isik"),
			"action" => "del_persons_from_flow"
		));*/
	}

	function _init_acts_t(&$t)
	{
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "actor",
			"caption" => t("Tegija"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "action",
			"caption" => t("Tegevus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "ord",
			"caption" => t("Jrk"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "predicate",
			"caption" => t("Eeldustegevus(ed)"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "done",
			"caption" => t("Tehtud"),
			"align" => "center"
		));
/*	$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));*/
	}

	function _acts($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_acts_t($t);

		$acts = $arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ACTION"));
		$acts[] = null;
		$acts[] = null;
		$acts[] = null;
		$null_idx = 0;
		$cur_numer = 0;

		$calinst = get_instance(CL_PLANNER);

		foreach($acts as $act_c)
		{
			if ($act_c === null)
			{
				$idx = $null_idx--;
				$act = obj();
				$act->set_class_id(CL_CRM_DOCUMENT_ACTION);
				$cur_numer++;
			}
			else
			{
				$idx = $act_c->prop("to");
				$act = $act_c->to();
				$cur_numer = $act->prop("ord");
			}

			$actor = $act->prop_str("actor");
			if ($actor != "")
			{
				$aco = obj($act->prop("actor"));
				$actor .= " ".$aco->prop_str("work_contact");
			}
			$actor = html::select(array(
				"name" => "a[$idx][actor]",
				"options" => array($act->prop("actor") => $actor),
				"value" => $act->prop("actor")
			));
			$popl = $this->mk_my_orb("do_search", array(
				"id" => $act->id(), 
				"pn" => "a[$idx][actor]",
				"clid" => CL_CRM_PERSON
			), "crm_participant_search");

			$actor .= " ".html::href(array(
				"url" => "javascript:aw_popup_scroll(\"$popl\",\"Otsing\",550,500)",
				"caption" => t("Otsi")
			));
			$pred_num = array();
			foreach(safe_array($act->prop("predicate")) as $pred)
			{
				if ($this->can("view", $pred))
				{
					$predo = obj($pred);
					$pred_num[] = $predo->prop("ord");
				}
			}

			$t->define_data(array(
				"date" => $calinst->draw_datetime_edit("a[$idx][date]", ($act->prop("date") > 100 ? $act->prop("date") : time())),
				"actor" => $actor,
				"action" => html::textbox(array(
					"name" => "a[$idx][action]",
					"value" => $act->prop("aw_action"),
				)),
				"ord" => html::textbox(array(
					"name" => "a[$idx][ord]",
					"value" => $cur_numer,
					"size" => 3
				)),
				"predicate" => html::textbox(array(
					"name" => "a[$idx][predicate]",
					"value" => join(",", $pred_num),
					"size" => 3
				)),
				"done" => html::checkbox(array(
					"name" => "a[$idx][done]",
					"value" => 1,
					"checked" => ($act->prop("is_done") == 1 ? true : false)
				)),
				"oid" => $act->id()
			));
		}
		$t->set_sortable(false);
	}

	function _save_acts($arr)
	{
		foreach(safe_array($arr["request"]["a"]) as $_oid => $row)
		{
			if (!is_oid($_oid))
			{
				$o = obj();
				$o->set_parent($arr["obj_inst"]->id());
				$o->set_class_id(CL_CRM_DOCUMENT_ACTION);

				if ($row["actor"] == "" && $row["action"] == "")
				{
					continue;
				}
			}
			else
			{
				$cos = $arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ACTION", "to" => $_oid));
				$co = reset($cos);
				$o = $co->to();
				if ($row["actor"] == 0 && $row["action"] == "")
				{
					$o->delete();
					continue;
				}
			}

			$o->set_prop("date", date_edit::get_timestamp($row["date"]));
			$o->set_prop("actor", $row["actor"]);
			$o->set_prop("aw_action", $row["action"]);
			$o->set_name($row["action"]);
			$o->set_prop("ord", $row["ord"]);
			$preds = array();
			foreach(explode(",", trim($row["predicate"])) as $pred)
			{
				$preds[] = $this->_find_pred_oid($arr["obj_inst"], $pred);
			}
			$o->set_prop("predicate", $preds);
			$o->set_prop("is_done", $row["done"]);
			$o->set_prop("document", $arr["obj_inst"]->id());
			$o->save();
			$arr["obj_inst"]->connect(array(
				"type" => "RELTYPE_ACTION",
				"to" => $o->id()
			));
		}
	}

	function _find_pred_oid($o, $pred)
	{
		if ($pred == "")
		{
			return -1;
		}
		$res = $o->connections_from(array("type" => "RELTYPE_ACTION", "to.jrk" => $pred));
		if (count($res))
		{
			$c = reset($res);
			return $c->prop("to");
		}
		return -1;
	}
}
?>
