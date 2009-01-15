<?php
/*
@classinfo syslog_type=ST_AW_SITE_ENTRY relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo no_name=1
@tableinfo aw_site_list master_index=brother_of master_table=objects index=aw_oid

@default table=aw_site_list
@default group=general

@property id type=textbox
@caption Saidi ID

@property name type=textbox
@caption Nimi

@property url type=textbox
@caption URL

@property server_oid type=relpicker reltype=RELTYPE_AW_SERVER_ENTRY 
@caption Server

@property ip type=textbox
@caption IP

@property site_used type=checkbox ch_value=1
@caption Sait kasutusel

@property code_branch type=textbox
@caption Koodiversioon

@property basedir type=textbox
@caption Kaust serveris

@property updater_uid type=textbox
@caption Uuendaja UID

@property last_update type=datetime_select
@caption Viimati uuendatud

@property mail_to type=textbox table=objects field=meta
@caption Kellele meil saata, kui sait maas

@property data type=textarea rows=30 cols=80
@caption Andmed

@property critical_services type=textarea rows=30 cols=80
@caption Kriitilised teenused

@property used_class_list type=textarea rows=30 cols=80
@caption Kasutusel klassid

@reltype AW_SERVER_ENTRY value=1 clid=CL_AW_SERVER_ENTRY
@caption Server
*/

class aw_site_entry extends class_base
{
	function aw_site_entry()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/aw_site_entry",
			"clid" => CL_AW_SITE_ENTRY
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
			$this->db_query("CREATE TABLE aw_aw_site_entry(aw_oid int primary key)");
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
