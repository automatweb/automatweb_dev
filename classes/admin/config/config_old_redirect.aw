<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/config/config_old_redirect.aw,v 1.1 2006/10/16 21:20:28 kristo Exp $
// config_old_redirect.aw - Vanade aadresside suunamine 
/*

@classinfo syslog_type=ST_CONFIG_OLD_REDIRECT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property url_table type=table store=no no_caption=1

@groupinfo activity caption=Aktiivsus

@property activity type=table group=activity no_caption=1
@caption Aktiivsus

*/

class config_old_redirect extends class_base
{
	function config_old_redirect()
	{
		$this->init(array(
			"tpldir" => "admin/config/config_old_redirect",
			"clid" => CL_CONFIG_OLD_REDIRECT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
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

	function _init_url_table(&$t)
	{
		$t->define_field(array(
			"name" => "old",
			"caption" => t("Vana aadress"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "new",
			"caption" => t("Uus aadress"),
			"align" => "center"
		));
		$t->set_sortable(false);
	}

	function _get_url_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_url_table($t);

		$d = $arr["obj_inst"]->meta("d");
		for($i = 0; $i < 10; $i++)
		{
			$d[] = array();
		}

		foreach($d as $idx => $row)
		{
			$t->define_data(array(
				"old" => html::textbox(array(
					"name" => "d[$idx][old]",
					"value" => $row["old"]
				)),
				"new" => html::textbox(array(
					"name" => "d[$idx][new]",
					"value" => $row["new"]
				)),
			));
		}
	}

	function _set_url_table($arr)
	{
		$d = array();
		foreach(safe_array($arr["request"]["d"]) as $row)
		{
			if ($row["old"] != "")
			{
				$d[] = $row;
			}
		}
		$arr["obj_inst"]->set_meta("d", $d);
	}

	function _get_activity($arr)
	{
		// this is supposed to return a list of all active polls
		// to let the user choose the active one
		$table = &$arr["prop"]["vcl_inst"];
		$table->parse_xml_def("activity_list");

		$pl = new object_list(array(
			"class_id" => CL_CONFIG_OLD_REDIRECT
		));	
		for($o = $pl->begin(); !$pl->end(); $o = $pl->next())
		{
			$actcheck = checked($o->flag(OBJ_FLAG_IS_SELECTED));
			$act_html = "<input type='radio' name='active' $actcheck value='".$o->id()."'>";
			$row = $o->arr();
			$row["active"] = $act_html;
			$table->define_data($row);
		};
	}

	function _set_activity($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_CONFIG_OLD_REDIRECT,
		));
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->flag(OBJ_FLAG_IS_SELECTED) && $o->id() != $arr["request"]["active"])
			{
				$o->set_flag(OBJ_FLAG_IS_SELECTED, false);
				$o->save();
			}
			else
			if ($o->id() == $arr["request"]["active"] && !$o->flag(OBJ_FLAG_IS_SELECTED))
			{
				$o->set_flag(OBJ_FLAG_IS_SELECTED, true);
				$o->save();
			}
		}
	}
}
?>
