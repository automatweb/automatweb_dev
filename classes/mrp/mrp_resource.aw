<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_resource.aw,v 1.1 2004/11/15 16:03:39 voldemar Exp $
// mrp_resource.aw - Ressurss
/*

@classinfo syslog_type=ST_MRP_RESOURCE relationmgr=yes

@groupinfo grp_resource_schedule caption="Tööd"
@groupinfo grp_resource_load caption="Koormus" parent="grp_resource_schedule"
@groupinfo grp_resource_joblist caption="Tööleht" parent="grp_resource_schedule"
@groupinfo grp_resource_settings caption="Seaded"


@default table=objects
@default field=meta
@default method=serialize

@default group=general
	@property category type=text editonly=1
	@caption Kategooria

	@property type type=select
	@caption Tüüp

@default group=grp_resource_schedule
	@default group=grp_resource_load
		@property load type=table store=no editonly=1
		@caption Tööleht

	@default group=grp_resource_joblist
		@property job_list type=table store=no editonly=1
		@caption Tööleht

@default group=grp_resource_settings
	//@property operator type= ???



// --------------- RELATION TYPES ---------------------

@reltype MRP_OPERATOR value=1 clid=CL_USER
@caption Ressursi operaator

@reltype MRP_SCHEDULE value=2 clid=CL_PLANNER
@caption Ressursi kalender

@reltype MRP_OWNER value=3 clid=CL_MRP_WORKSPACE
@caption Ressursi omanik

*/

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

			case "job_list":
				$this->create_job_list_table ($arr);
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
		$table->draw_text_pageselector (array(
			"records_per_page" => 50,
		));

		$list = new object_list(array(
			"class_id" => CL_MRP_JOB,
			"resource" => $this_object->id (),
			"starttime" => new obj_predicate_compare (OBJ_COMP_GREATER, time ()),
		));
		$jobs = $list->arr ();

		foreach ($jobs as $job_id => $job)
		{
			$time = getdate ($job->prop ("starttime"));
			$starttime = $time["mday"] . "/" . $time["mon"] . "/" . $time["year"] . " " . $time["hours"] . "." . $time["minutes"];
			$project = is_oid ($job->prop ("project")) ? obj ($job->prop ("project")) : NULL;
			$project = is_object ($project) ? $project->name () : "...";

			$change_url = $this->mk_my_orb("change", array(
				"id" => $job_id,
				"return_url" => urlencode(aw_global_get('REQUEST_URI')),
				"group" => "",
			), "mrp_job");

			$table->define_data(array(
				"modify" => html::href(array(
					"caption" => "Ava",
					"url" => $change_url,
					)),
				"project" => $project,
				"name" => $job->name (),
				"starttime" => $starttime,
			));
		}
	}
}
?>
