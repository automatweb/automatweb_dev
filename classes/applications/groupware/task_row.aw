<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/task_row.aw,v 1.3 2006/05/10 10:02:15 kristo Exp $
// task_row.aw - Toimetuse rida 
/*

@classinfo syslog_type=ST_TASK_ROW relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_task_rows index=aw_oid master_table=objects master_index=brother_of

@default table=objects

@default group=general

	@property content type=textarea rows=5 cols=50 table=aw_task_rows field=aw_content
	@caption Sisu

	@property date type=date_select table=aw_task_rows field=aw_date
	@caption Kuup&auml;ev

	@property impl type=relpicker reltype=RELTYPE_IMPL table=aw_task_rows field=aw_impl store=connect multiple=1
	@caption Teostaja

	@property time_guess type=textbox size=5 table=aw_task_rows field=aw_time_guess
	@caption Prognoositud tunde

	@property time_real type=textbox size=5 table=aw_task_rows field=aw_time_real
	@caption Kulunud tunde

	@property time_to_cust type=textbox size=5 table=aw_task_rows field=aw_time_to_cust
	@caption Tunde kliendile

	@property done type=checkbox ch_value=1 table=aw_task_rows field=aw_done
	@caption Tehtud

	@property on_bill type=checkbox ch_value=1 table=aw_task_rows field=aw_on_bill
	@caption Arvele

	@property bill_id type=relpicker reltype=RELTYPE_BILL table=aw_task_rows field=aw_bill_id
	@caption Arve

@default group=comments

	@property comments type=comments
	@caption Kommentaarid

@groupinfo comments caption="Kommentaarid"

@reltype BILL value=1 clid=CL_CRM_BILL
@caption Arve

@reltype IMPL value=2 clid=CL_CRM_PERSON
@caption Teostaja
*/

class task_row extends class_base
{
	function task_row()
	{
		$this->init(array(
			"tpldir" => "applications/groupware/task_row",
			"clid" => CL_TASK_ROW
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

	function handle_stopper_stop($o, $data)
	{
		$o->set_prop("time_real", $o->prop("time_real") + $data["hours"]);
		if ($data["desc"] != "")
		{
			$o->set_prop("content", $data["desc"]);
		}
		$o->save();

		return 1;
	}
}
?>
