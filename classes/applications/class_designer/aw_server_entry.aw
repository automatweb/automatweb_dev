<?php
/*
@classinfo syslog_type=ST_AW_SERVER_ENTRY relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_server_list master_index=brother_of master_table=objects index=aw_oid

@default table=aw_server_list
@default group=general

@property id type=textbox
@caption Serveri ID

@property name type=textbox
@caption Nimi

@property ip type=textbox
@caption IP

@property customer type=relpicker reltype=RELTYPE_CUSTOMER field=aw_customer
@caption Klient

@property contact type=relpicker reltype=RELTYPE_CONTACT field=aw_contact
@caption Kontaktisik


@property comment type=textarea rows=30 cols=80
@caption Kommentaar

@reltype CUSTOMER value=1 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype CONTACT value=2 clid=CL_CRM_PERSON
@caption Kontaktisik

*/

class aw_server_entry extends class_base
{
	function aw_server_entry()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/aw_server_entry",
			"clid" => CL_AW_SERVER_ENTRY
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
			$this->db_query("CREATE TABLE aw_aw_server_entry(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_customer":
			case "aw_contact":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}

?>
