<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug_comment.aw,v 1.3 2006/12/12 10:19:57 kristo Exp $
// bug_comment.aw - Bugi kommentaar 
/*

@classinfo syslog_type=ST_BUG_COMMENT relationmgr=yes no_status=1 prop_cb=1

@tableinfo aw_bug_comments index=aw_oid master_index=brother_of master_table=objects

@default table=objects
@default group=general

@property comment type=textarea rows=5 cols=50 table=objects field=comment
@caption Kommentaar

@property prev_state type=select table=aw_bug_comments field=aw_prev_state
@caption Eelmine staatus

@property new_state type=select table=aw_bug_comments field=aw_new_state
@caption Uus staatus

@property add_wh type=textbox size=5 table=aw_bug_comments field=aw_add_wh
@caption Lisandunud t&ouml;&ouml;tunnid

*/

class bug_comment extends class_base
{
	function bug_comment()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/bug_comment",
			"clid" => CL_BUG_COMMENT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "prev_state":
			case "new_state":
				$bi = get_instance(CL_BUG);
				$prop["options"] = array("" => t("--vali--"))+ $bi->get_status_list();
				break;
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

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_bug_comments (aw_oid int primary key, aw_prev_state int, aw_new_state int, aw_add_wh double)");
		}
	}
}
?>
