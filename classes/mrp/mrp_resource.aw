<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_resource.aw,v 1.6 2005/01/25 12:30:28 voldemar Exp $
// mrp_resource.aw - Ressurss
/*

@classinfo syslog_type=ST_MRP_RESOURCE relationmgr=yes

@groupinfo grp_resource_schedule caption="Kalender"
@groupinfo grp_resource_joblist caption="Tööleht"
@groupinfo grp_resource_settings caption="Seaded"
@groupinfo grp_resource_unavailable caption="Kinnised ajad"

@default table=objects
@default field=meta
@default method=serialize

@default group=general
	@property category type=text editonly=1
	@caption Kategooria

	@property type type=select
	@caption Tüüp


@default group=grp_resource_schedule
	@property resource_calendar type=text store=no no_caption=1
	@caption Tööd


@default group=grp_resource_joblist
	@property job_list type=table store=no editonly=1
	@caption Tööleht


@default group=grp_resource_settings
	@property operator type=relpicker reltype=RELTYPE_MRP_OPERATOR
	@caption Ressursi kasutaja

	@property default_pre_buffer type=textbox
	@caption Vaikimisi eelpuhveraeg (h)

	@property default_post_buffer type=textbox
	@caption Vaikimisi järelpuhveraeg (h)

	@property global_buffer type=textbox default=4
	@caption Päeva üldpuhver (h)


@default group=grp_resource_unavailable
	@property unavailable_recur type=releditor reltype=RELTYPE_RECUR use_form=emb mode=manager
	@caption Kinnised ajad

	@property unavailable_weekends type=checkbox ch_value=1
	@caption Ei t&ouml;&ouml;ta n&auml;dalavahetustel

	@property unavailable_dates type=textarea rows=5 cols=50
	@caption Kinnised p&auml;evad (formaat: p&auml;ev.kuu p&auml;ev.kuu)



// --------------- RELATION TYPES ---------------------

@reltype MRP_OPERATOR value=1 clid=CL_MRP_RESOURCE_OPERATOR
@caption Ressursi operaator

@reltype MRP_SCHEDULE value=2 clid=CL_PLANNER
@caption Ressursi kalender

@reltype MRP_OWNER value=3 clid=CL_MRP_WORKSPACE
@caption Ressursi omanik

@reltype RECUR value=4 clid=CL_RECURRENCE
@caption Kordus

*/

### resource types
define ("MRP_RESOURCE_MACHINE", 1);
define ("MRP_RESOURCE_OUTSOURCE", 2);

### states
define ("MRP_STATUS_NEW", 1);
define ("MRP_STATUS_PLANNED", 2);
define ("MRP_STATUS_INPROGRESS", 3);
define ("MRP_STATUS_ABORTED", 4);
define ("MRP_STATUS_DONE", 5);
define ("MRP_STATUS_LOCKED", 6);
define ("MRP_STATUS_OVERDUE", 7);
define ("MRP_STATUS_DELETED", 8);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");

class mrp_resource extends class_base
{
	function mrp_resource()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_resource",
			"clid" => CL_MRP_RESOURCE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object = &$arr["obj_inst"];

//
				foreach ($this_object->connections_from(array("type" => RELTYPE_RECUR)) as $connection)
				{
					if ($connection and !$this->kd)
					{
						$e = $connection->to ();
						arr ($e->properties ());
						$this->kd = true;
						break;
					}
				}

//recur props:
// Array
// (
    // [name] => t88aeg
    // [comment] =>
    // [status] => 1
    // [time] => 18 //starttime
    // [length] => 15
    // [recur_type] => 1- p2ev|2 -ndl|4 - aasta 3-kuu
    // [interval_daily] => 1  iga x p2eva j2rel
    // [interval_weekly] =>  ...
    // [interval_monthly] =>
    // [interval_yearly] =>
    // [weekdays] =>

// kui recur type on ndl siis valitud p2evad:
// [weekdays] => Array
        // (
            // [2] => 2 - teisip2ev
            // [3] => 3 - ...
            // [4] => 4
            // [5] => 5
        // )

    // [month_days] =>
    // [month_rel_weekdays] =>
    // [month_weekdays] =>
    // [start] => 1106344800 //algus
    // [end] => 1230674400 //l6pp
    // [brother_of] => 139457
    // [parent] => 139151
    // [class_id] => 286
    // [lang_id] => 1
    // [period] => 0
    // [created] => 1106421605
    // [modified] => 1106421791
    // [periodic] => 0
// )


//

		if ($arr["new"])
		{
			$this->mrp_workspace = $arr["request"]["mrp_workspace"];
		}

		switch($prop["name"])
		{
			case "category":
				### get workspace object "owning" current object
				foreach ($this_object->connections_from(array("type" => RELTYPE_MRP_OWNER)) as $connection)
				{
					if ($connection)
					{
						$workspace = $connection->to();
						break;
					}
				}

				if ($workspace)
				{
					$resources_folder_id = $workspace->prop ("resources_folder");
					$parent_folder_id = $this_object->parent ();
					$parents = "";

					while ($resources_folder_id and ($parent_folder_id != $resources_folder_id))
					{
						$parent = obj ($parent_folder_id);
						$parents = "/" . $parent->name () . $parents;
						$parent_folder_id = $parent->parent ();
					}

					$prop["value"] = "/Ressursid" . $parents;
				}
				else
				{
					$prop["value"] = "Ressurss ei kuulu ühessegi ressursihaldussüsteemi.";
				}
				break;

			case "resource_calendar":
				$prop["value"] = $this->create_resource_calendar ($arr);
				break;

			case "type":
				$prop["options"] = array (
					MRP_RESOURCE_MACHINE => "Masin",
					MRP_RESOURCE_OUTSOURCE => "Allhange",
				);
				break;

			case "job_list":
				$this->create_job_list_table ($arr);
				break;

			case "default_pre_buffer":
			case "default_post_buffer":
				$prop["value"] = $prop["value"] / 3600;
				break;
		}

		return $retval;
	}

	function callback_mod_reforb ($arr)
	{
		if ($this->mrp_workspace)
		{
			$arr["mrp_workspace"] = $this->mrp_workspace;
		}
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "default_pre_buffer":
			case "default_post_buffer":
				$prop["value"] = round ($prop["value"] * 3600);
				break;
		}
		return $retval;
	}

	function callback_post_save ($arr)
	{
		$this_object = $arr["obj_inst"];

		### connect newly created obj. to workspace from which the req. was made
		if ($arr["new"] and is_oid ($arr["request"]["mrp_workspace"]))
		{
			$workspace = obj ($arr["request"]["mrp_workspace"]);
			$projects_folder = $workspace->prop ("resources_folder");
			$this_object->connect (array (
				"to" => $workspace,
				"reltype" => RELTYPE_MRP_OWNER,
			));
			// $this_object->set_parent ($resources_folder);
			// $this_object->save ();
		}
	}

	function create_job_list_table ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "project",
			"caption" => "Projekt",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "name",
			"caption" => "Töö",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "starttime",
			"caption" => "Alustamisaeg",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "modify",
			"caption" => "Ava",
		));

		$table->set_default_sortby ("starttime");
		$table->set_default_sorder ("asc");
		$table->draw_text_pageselector (array (
			"records_per_page" => 50,
		));

		$list = new object_list(array(
			"class_id" => CL_MRP_JOB,
			"resource" => $this_object->id (),
			"starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, time (), mktime (23, 59, 59)),
		));
		$jobs = $list->arr ();

		foreach ($jobs as $job_id => $job)
		{
			$starttime = date (MRP_DATE_FORMAT, $job->prop ("starttime"));
			// $project = is_oid ($job->prop ("project")) ? obj ($job->prop ("project")) : NULL;
			$project = is_object ($project) ? $project->name () : "...";

			$change_url = $this->mk_my_orb ("change", array (
				"id" => $job_id,
				"return_url" => urlencode (aw_global_get ('REQUEST_URI')),
				"group" => "",
			), "mrp_job");

			$table->define_data (array (
				"modify" => html::href (array (
					"caption" => "Ava",
					"url" => $change_url,
					)),
				"project" => $project,
				"name" => $job->name (),
				"starttime" => $starttime,
			));
		}
	}

	function create_resource_calendar ($arr)
	{
		$this_object =& $arr["obj_inst"];

		classload("vcl/calendar");
		$calendar = new vcalendar (array ("tpldir" => "mrp_calendar"));
		$calendar->init_calendar (array ());
		$calendar->configure (array (
			"overview_func" => array (&$this, "get_overview"),
		));
		$range = $calendar->get_range (array (
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"],
		));
		$start = $range["start"];
		$end = $range["end"];

		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"resource" => $this_object->id (),
			"starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, $start, $end),
		));

		if ($list->count () > 0)
		{
			for ($job =& $list->begin(); !$list->end(); $job =& $list->next())
			{
				//$project = is_oid ($job->prop ("project")) ? obj ($job->prop ("project")) : NULL;
				$project = is_object ($project) ? $project->name () : "...";
				$calendar->add_item (array (
					"timestamp" => $job->prop ("starttime"),
					"data" => array(
						"name" => $job->prop ("name"),
						"link" => $this->mk_my_orb ("change",array ("id" => $job->id ()), "mrp_job"),
					),
				));
			}
		}

		return $calendar->get_html ();
	}

	function get_overview ($arr = array())
	{
		$start = time() - (24*3600*60);
		$end = time() + (24*3600*60);

		for($i = $start; $i < $end; $i += (24*3600))
		{
			$ret[$i] = aw_url_change_var("viewtype", "week", aw_url_change_var("date", date("d", $i)."-".date("m", $i)."-".date("Y", $i)));
		}
		return $ret;
	}

	function get_unavailable_times ($resource, $workspace_id)
	{
		$ret = array ();

		if ($resource->prop ("unavailable_weekends"))
		{
			$this->_get_weekends ($ret, $workspace_id);
		}

		$this->_get_dates ($ret, $resource->prop ("unavailable_dates"));

		foreach ($resource->connections_from (array ("type" => "RELTYPE_RECUR")) as $c)
		{
			$this->_get_recur ($ret, $c->to ());
		}

		ksort ($ret);
		return $ret;
	}

	function _get_weekends(&$ret, $workspace_id)
	{
		### get workspace object "owning" current object
		$workspace = obj ($workspace_id);
		$weeks = ceil ($workspace->prop ("parameter_schedule_length") * 52);
		$wd = date("w");
		$sat = mktime(0,0,0, date("m"), date("d") + (($wd == 0) ? -1 : 6 - $wd), date("Y"));

		for ($i = 0; $i < $weeks; $i++)
		{
			$tm = $sat + ($i * 7 * 24 * 3600);
			$ret[$tm] = 24 * 3600;

			$tm = $sat + ($i * 7 * 24 * 3600) + 24 * 3600;
			$ret[$tm] = 24 * 3600;
		}
	}

	function _get_dates(&$ret, $dates)
	{
		$parts = preg_split('/\s+/', $dates);
		foreach($parts as $part)
		{
			list($day, $mon) = explode(".", $part);

			$tm = mktime(0,0,0, $mon, $day, date("Y"));
			$ret[$tm] = 24 * 3600;

			$tm = mktime(0,0,0, $mon, $day, date("Y")+1);
			$ret[$tm] = 24 * 3600;
		}
	}

	function _get_recur(&$ret, $recur)
	{
		$rec = get_instance(CL_RECURRENCE);
		$tmp =  $rec->get_event_range(array(
			"id" => $recur->id(),
			"start" => time(),
			"end" => mktime(0,0,0, date("m"), date("d"), date("Y")+2)
		));
		foreach($tmp as $time => $row)
		{
			$ret[$time] = $row["recur_end"] - $time;
		}
	}
}

?>
