<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/calendar_vacancy.aw,v 1.1 2004/07/23 10:58:32 duke Exp $
// calendar_vacancy.aw - Vakants 
/*

@classinfo syslog_type=ST_CALENDAR_VACANCY relationmgr=yes

@default table=objects
@default group=general

@property start type=datetime_select field=start table=planner
@caption Algab

@property end type=datetime_select table=planner
@caption Lõpeb

@tableinfo planner index=id master_table=objects master_index=brother_of

*/

class calendar_vacancy extends class_base
{
	function calendar_vacancy()
	{
		$this->init(array(
			"clid" => CL_CALENDAR_VACANCY
		));
	}

	/**
		@attrib name=reserve_slot all_args="1"

	**/
	function reserve_slot($arr)
	{
		// okey, I have the free time object id
		$vac_obj = new object($arr["id"]);
		$parent = $vac_obj->parent();
		$start = $vac_obj->prop("start");
		$end = $vac_obj->prop("end");


		$new_obj = new object();
		$new_obj->set_parent($parent);
		$new_obj->set_class_id($arr["clid"]);
		$new_obj->set_status(STAT_ACTIVE);
		$new_obj->set_prop("start1",$start);
		$new_obj->set_prop("end",$end);

		$new_obj->save();

		$pl = get_instance(CL_PLANNER);
		//$user_calendar = $pl->get_calendar_for_user();
		$user_calendar = $arr["cal_id"];

		$vac_obj->delete();

		// 1. get parent
		// 2. get times
		// 3. create a new clid object out of that with same parent and same times
		// 4. display the form to the user
		// 5. BUT. I need to display that in user calendar .. well, actually, I just need
		// 	to create an empty object and then redirect to the calendar of that active user
		//print "creating a new slot, eh<bR>";
		//arr($arr);
		//print "all done<br>";

		$ret_url = $pl->get_event_edit_link(array(
			"cal_id" => $user_calendar,
			"event_id" => $new_obj->id(),
		));

		return $ret_url;

		/*
		$ret_url = $this->mk_my_orb("change",array(
			"id" => $user_calendar,
			"group" => "add_event",
			"event_id" => $event_id,
		),CL_PLANNER);
		*/
		//http://duke.dev.struktuur.ee/automatweb/orb.aw?class=planner&action=change&id=137870&group=add_event&event_id=138595


	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}
	*/

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
