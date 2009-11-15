<?php
/*
@classinfo syslog_type=ST_K_TEST_COMPLETION_RULE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_test_completion_rule master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_test_completion_rule
@default group=general

	@property type type=chooser orient=vertical
	@caption T&uuml;&uuml;p

	@property rule type=chooser orient=vertical
	@caption Reegel

	@property rule_conf_1 type=hidden
	@property rule_conf_2 type=hidden
	@property rule_conf_3 type=hidden
	@property rule_conf_4 type=hidden

@groupinfo conditions caption="Tingimused"
@default group=conditions

	@property conditions_table type=table store=no no_caption=1

*/

class k_test_completion_rule extends class_base
{
	function k_test_completion_rule()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_test_completion_rule",
			"clid" => CL_K_TEST_COMPLETION_RULE
		));
	}

	public function _get_rule($arr)
	{
		$arr["prop"]["options"] = array(
			0 => t("Kasuta tingimuste kaarti"),
			1 => sprintf(t("%s %s bloki tulemus on %s %s"),
				html::select(array(
					"name" => "rule_conf_1",
					"options" => array(
						0 => t("V&auml;hemalt"),
						1 => t("&Uuml;limalt"),
					),
					"value" => $arr["obj_inst"]->rule_conf_1
				)),
				html::textbox(array(
					"name" => "rule_conf_2",
					"size" => 4,
					"value" => $arr["obj_inst"]->rule_conf_2
				)),
				html::select(array(
					"name" => "rule_conf_3",
					"options" => get_instance("k_test_completion_rule_condition")->type_options,
					"value" => $arr["obj_inst"]->rule_conf_3
				)),
				html::textbox(array(
					"name" => "rule_conf_4",
					"size" => 4,
					"value" => $arr["obj_inst"]->rule_conf_4
				))
			),
		);
	}

	public function _get_conditions_table($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Ploki nimi"),
		));
		$t->define_field(array(
			"name" => "type",
			"caption" => t("Tingimuse t&uuml;&uuml;p"),
		));
		$t->define_field(array(
			"name" => "result",
			"caption" => t("Tulemus"),
		));
		$inst = new k_test_completion_rule_condition();
		$conditions = $arr["obj_inst"]->get_conditions_by_block();
		foreach(obj($arr["obj_inst"]->parent())->get_blocks()->arr() as $o)
		{
			$t->define_data(array(
				"name" => parse_obj_name($o->name()),
				"type" => html::select(array(
					"name" => "conditions[".$o->id()."][type]",
					"options" => $inst->type_options,
					"value" => isset($conditions[$o->id()]) ? $conditions[$o->id()]->type : "",
				)),
				"result" => html::textbox(array(
					"name" => "conditions[".$o->id()."][value]",
					"value" => isset($conditions[$o->id()]) ? $conditions[$o->id()]->value : "",
				)),
			));
		}
	}

	public function callback_post_save($arr)
	{
		if(!empty($arr["request"]["conditions"]))
		{
			$conditions = $arr["obj_inst"]->get_conditions_by_block();
			foreach($arr["request"]["conditions"] as $block_id => $condition)
			{
				if(isset($conditions[$block_id]))
				{
					$o = $conditions[$block_id];
				}
				else
				{
					$o = obj();
					$o->set_class_id(CL_K_TEST_COMPLETION_RULE_CONDITION);
					$o->set_parent($arr["obj_inst"]->id());
					$o->completion_rule = $arr["obj_inst"]->id();
					$o->block = $block_id;
				}
				if(!empty($condition["value"]))
				{
					$o->type = $condition["type"];
					$o->value = $condition["value"];
					$o->save();
				}
				else
				{
					$o->delete();
				}
			}
		}
	}

	public function _get_type($arr)
	{
		$arr["prop"]["options"] = array(
			0 => t("Tingimused m&auml;&auml;ravad, millal on t&auml;iendav anal&uuml;&uuml;s vajalik"),
			1 => t("Tingimused m&auml;&auml;ravad, millal ei ole t&auml;iendav anal&uuml;&uuml;s vajalik"),
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "rule_conf_1":
			case "rule_conf_2":
			case "rule_conf_3":
			case "rule_conf_4":
				$retval = PROP_IGNORE;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_k_test_completion_rule(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "rule":
			case "rule_conf_1":
			case "rule_conf_2":
			case "rule_conf_3":
			case "type":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;

			case "rule_conf_4":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(10)"
				));
				return true;
		}
	}
}

?>
