<?php
/*
@classinfo syslog_type=ST_D1 relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=d1
@tableinfo aw_d1 master_index=brother_of master_table=objects index=aw_oid

@default table=aw_d1
@default group=general

@property num_rows type=textbox field=aw_num_rows
@caption Ridade arv

*/

class d1 extends class_base
{
	function d1()
	{
		$this->init(array(
			"tpldir" => "applications/d1/d1",
			"clid" => CL_D1
		));
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
			$this->db_query("CREATE TABLE aw_d1(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_num_rows":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}

?>
