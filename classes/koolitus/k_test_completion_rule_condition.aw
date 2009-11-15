<?php
/*
@classinfo syslog_type=ST_K_TEST_COMPLETION_RULE_CONDITION relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_test_completion_rule_condition master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_test_completion_rule_condition
@default group=general

	@property block type=relpicker reltype=RELTYPE_BLOCK
	@caption Plokk

	@property completion_rule type=relpicker reltype=RELTYPE_COMPLETION_RULE
	@caption L&auml;bimise tingimus

	@property type type=select
	@caption T&uuml;&uuml;p

	@property value type=textbox
	@caption V&auml;&auml;rtus

### RELTYPES

@reltype BLOCK value=1 clid=CL_K_TEST_BLOCK
@caption Plokk

@reltype COMPLETION_RULE value=2 clid=CL_K_TEST_COMPLETION_RULE
@caption L&auml;bimise tingimus

*/

class k_test_completion_rule_condition extends class_base
{
	const TYPE_GREATER = 1;
	const TYPE_LESS = 2;
	const TYPE_GREATER_OR_EQ = 3;
	const TYPE_LESS_OR_EQ = 4;
	function k_test_completion_rule_condition()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_test_completion_rule_condition",
			"clid" => CL_K_TEST_COMPLETION_RULE_CONDITION
		));
		$this->type_options = array(
			self::TYPE_GREATER => t(">"),
			self::TYPE_LESS => t("<"),
			self::TYPE_GREATER_OR_EQ => t(">="),
			self::TYPE_LESS_OR_EQ => t("<="),
		);
	}

	public function _get_type($arr)
	{
		$arr["prop"]["options"] = $this->type_options;
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
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
			$this->db_query("CREATE TABLE aw_k_test_completion_rule_condition(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "block":
			case "completion_rule":
			case "type":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;

			case "value":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(10)"
				));
				return true;
		}
	}
}

?>
