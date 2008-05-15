<?php
/*
@classinfo syslog_type=ST_PACKAGE_CLIENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@tableinfo aw_package_client master_index=brother_of master_table=objects index=aw_oid

@default table=aw_package_client
@default group=general

*/

class package_client extends class_base
{
	function package_client()
	{
		$this->init(array(
			"tpldir" => "applications/package_management/package_client",
			"clid" => CL_PACKAGE_CLIENT
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
			$this->db_query("CREATE TABLE aw_package_client(aw_oid int primary_key)");
			return true;
		}

		switch($f)
		{
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}
}

?>
