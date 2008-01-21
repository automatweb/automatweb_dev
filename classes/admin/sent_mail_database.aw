<?php
// sent_mail_database.aw - V&auml;ljasaadetud meilid
/*

HANDLE_MESSAGE(MSG_MAIL_SENT, on_send_mail)


@classinfo syslog_type=ST_SENT_MAIL_DATABASE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=mails

	@property ml_tb type=toolbar no_caption=1 store=no

	@layout mail_view type=hbox width=30%:80%

		@layout mail_left_pane type=vbox closeable=1 area_caption=Otsing parent=mail_view

			@property date_from type=date_select captionside=top store=no parent=mail_left_pane
			@caption Alates

			@property date_to type=date_select captionside=top store=no parent=mail_left_pane
			@caption Kuni

			@property s_sbt type=submit no_caption=1 store=no parent=mail_left_pane
			@caption Otsi

		@property mail_table type=table store=no no_caption=1 parent=mail_view
		
@groupinfo mails caption="Meilid"

*/

class sent_mail_database extends class_base
{
	function sent_mail_database()
	{
		$this->init(array(
			"tpldir" => "admin/sent_mail_database",
			"clid" => CL_SENT_MAIL_DATABASE
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

	function on_send_mail($arr)
	{
		// do this right. we need to find if there is a mail database set and then write mails under that
		static $mail_db;
		if ($mail_db === null)
		{
			$ol = new object_list(array(
				"class_id" => CL_SENT_MAIL_DATABASE,
				"lang_id" => array(),
				"site_id" => array()
			));
			if ($ol->count())
			{
				$mail_db = $ol->begin();
			}
			else
			{
				$mail_db = -1;
			}
		}

		if (is_object($mail_db))
		{
			$o = obj();
			$o->set_class_id(CL_AW_SENT_MAIL);
			$o->set_parent($mail_db->id());
			$o->set_name(sprintf("Meil %s aadressilt %s aadressile %s", 
				$arr["subject"],
				$arr["from"],
				$arr["to"]
			));
			$o->set_prop("from", $arr["from"]);
			$o->set_prop("to", $arr["to"]);
			$o->set_prop("subject", $arr["subject"]);
			$o->set_prop("headers", $arr["headers"]);
			$o->set_prop("arguments", $arr["arguments"]);
			$o->set_prop("content", $arr["content"]);
			$o->set_prop("app", $arr["app"]);
			$o->save();
		}
	}

	function _init_mail_table(&$t)
	{
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Millal"),
			"sortable" => 1,
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i:s"
		));
		$t->define_field(array(
			"name" => "from",
			"caption" => t("Kes"),
			"sortable" => 1,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "to",
			"caption" => t("Kellele"),
			"sortable" => 1,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "subject",
			"caption" => t("Teema"),
			"sortable" => 1,
			"align" => "center",
		));
	}

	function _get_mail_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_mail_table($t);

		$ol = new object_list(array(
			"class_id" => CL_AW_SENT_MAIL,
			"lang_id" => array(),
			"site_id" => array(),
			"parent" => $arr["obj_inst"]->id()
		));
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"created" => $o->created(),
				"from" => $o->prop("from"),
				"to" => $o->prop("to"),
				"subject" => html::href(array(
					"url" => $this->mk_my_orb("view", array("id" => $o->id(), "return_url" => get_ru()), $o->class_id()),
					"caption" => $o->prop("from")
				))
			));
		}
		$t->set_default_sortby("created");
		$t->set_default_sorder("desc");
	}
}

?>
