<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/vcl/calendar_selector.aw,v 1.2 2004/12/01 12:13:20 kristo Exp $
class calendar_selector extends core
{
	function calendar_selector()
	{
		$this->init("");
	}


	function init_vcl_property($arr)
	{
		$brlist = new object_list(array(
			"brother_of" => $arr["obj_inst"]->id(),
			// ignore site id's for this list
			"site_id" => array(),
		));

		foreach($brlist->arr() as $o)
		{
			$plrlist[$o->parent()] = $o->id();
		};

		$all_props = array();
	
		$planners = new object_list(array(
			"class_id" => CL_PLANNER,
			"sort_by" => "name",
			"status" => STAT_ACTIVE,
			"site_id" => array(),
		));

		$propname = $arr["property"]["name"];

		foreach($planners->arr() as $planner)
		{
			$event_folder = $planner->prop("event_folder");
			if ($event_folder != 0)
			{
				$id = $planner->id();
				$all_props["${propname}${id}"] = array(
					"type" => "checkbox",
					"name" => "${propname}[${id}]",
					"caption" => html::href(array(
						"url" => $this->mk_my_orb("change",array("id" => $id),CL_PLANNER),
						"caption" => "<font color='black'>" . $planner->name() . "</font>",
					)),
					"ch_value" => $id,
					"value" => isset($plrlist[$event_folder]) ? $id : 0,
				);
			};
		};
		return $all_props;
	}



	function process_vcl_property($arr)
	{
		$event_obj  = $arr["obj_inst"];
		// 1) retrieve all connections that this event has to projects
		// 2) remove those that were not explicitly checked in the form
		// 3) create new connections which did not exist before

		// urk .. I need all brothers of the event object.

		$brlist = new object_list(array(
			"brother_of" => $event_obj->id(),
		));

		$plrlist = array();

		foreach($brlist->arr() as $o)
		{
			$id = $o->id();
			if ($id != $event_obj->id())
			{
				$plrlist[$o->parent()] = $id;
			};
		};

		$all_props = array();

		$new_ones = array();
		if (is_array($arr["prop"]["value"]))
		{
			$new_ones = $arr["prop"]["value"];
		};

		foreach($plrlist as $plid => $evid)
		{
			if (!$new_ones[$plid])
			{
				$ev_obj = new object($evid);
				$ev_obj->delete();
			};
			unset($new_ones[$plid]);
		};

		// now new_ones sisaldab nende kalendrite id-sid, millega ma pean seose looma
		foreach($new_ones as $plid)
		{
			$plr_obj = new object($plid);
			$bro = $event_obj->create_brother($plr_obj->prop("event_folder"));
		};

	}
	
	////
	// !Returns a list of planners that have event folders ..


};
?>
