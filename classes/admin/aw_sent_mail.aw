<?php
// aw_sent_mail.aw - Saadetud meil
/*

@classinfo syslog_type=ST_AW_SENT_MAIL relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_sent_mails index=aw_oid master_table=objects master_index=brother_of

@default table=aw_sent_mails
@default group=general

@property app type=textbox field=aw_app
@caption Aplikatsioon

@property from type=textbox field=aw_from
@caption Kellelt

@property to type=textbox field=aw_to
@caption Kellele

@property subject type=textbox field=aw_subject
@caption Teema

@property headers type=textarea field=aw_headers rows=3 cols=80
@caption Headerid

@property arguments type=textarea field=aw_arguments rows=3 cols=80
@caption Argumendid

@property content type=textarea field=aw_content rows=50 cols=80
@caption Sisu


*/

class aw_sent_mail extends class_base
{
	function aw_sent_mail()
	{
		$this->init(array(
			"tpldir" => "admin/aw_sent_mail",
			"clid" => CL_AW_SENT_MAIL
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
			$this->db_query("CREATE TABLE aw_sent_mails(aw_oid int primary key, aw_app varchar(255), aw_from varchar(255), aw_to varchar(255), aw_subject varchar(255), aw_headers text, aw_arguments text, aw_content mediumtext)");
			return true;
		}
	}
}

?>
