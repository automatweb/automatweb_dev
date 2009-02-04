<?php
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_NEW, CL_MRP_RESOURCE, on_create_resource)

@classinfo syslog_type=ST_MRP_RESOURCE relationmgr=yes no_status=1 confirm_save_data=1 maintainer=voldemar

@groupinfo grp_resource_schedule caption="Kalender"
@groupinfo grp_resource_joblist caption="T&ouml;&ouml;leht" submit=no
	@groupinfo grp_resource_joblist_todo caption="Eesseisvad t&ouml;&ouml;d" submit=no parent=grp_resource_joblist
	@groupinfo grp_resource_joblist_done caption="Tehtud t&ouml;&ouml;d" submit=no parent=grp_resource_joblist
@groupinfo grp_resource_settings caption="Seaded"
@groupinfo grp_resource_maintenance caption="Hooldus"
@groupinfo grp_resource_unavailable caption="T&ouml;&ouml;ajad"
	@groupinfo grp_resource_unavailable_work caption="T&ouml;&ouml;ajad" parent=grp_resource_unavailable
	@groupinfo grp_resource_unavailable_una caption="Kinnised ajad" parent=grp_resource_unavailable


@default table=objects
@default field=meta
@default method=serialize
	@property state type=text group=general,grp_resource_maintenance,grp_resource_settings
	@caption Ressursi staatus

@default group=general
	@property category type=text editonly=1
	@caption Kategooria

@default group=grp_resource_schedule
	@property resource_calendar type=text store=no no_caption=1
	@caption T&ouml;&ouml;d


@default group=grp_resource_joblist_todo
	@property job_list type=table store=no editonly=1 no_caption=1
	@caption T&ouml;&ouml;leht

@default group=grp_resource_joblist_done
	@property job_list_done type=table store=no editonly=1 no_caption=1
	@caption T&ouml;&ouml;leht


@default group=grp_resource_maintenance
	@property out_of_service type=checkbox store=no ch_value=1
	@caption Ressurss hoolduses

	@property maintenance_history type=comments
	@caption Hoolduskommentaarid


@default group=grp_resource_settings
	@property type type=select
	@caption T&uuml;&uuml;p

	@property thread_data type=textbox default=1
	@comment Positiivne t&auml;isarv
	@caption Samaaegseid t&ouml;id enim

	@property default_pre_buffer type=textbox
	@caption Vaikimisi eelpuhveraeg (h)

	@property default_post_buffer type=textbox
	@caption Vaikimisi j&auml;relpuhveraeg (h)

	@property global_buffer type=textbox default=14400
	@caption P&auml;eva &uuml;ldpuhver (h)


@default group=grp_resource_unavailable_work

	@property work_hrs_recur type=releditor reltype=RELTYPE_RECUR_WRK mode=manager props=name,start,end,time,length table_fields=name,start,end,time,length
	@caption T&ouml;&ouml;ajad

@default group=grp_resource_unavailable_una

	@property unavailable_recur type=releditor reltype=RELTYPE_RECUR use_form=emb mode=manager props=name,start,end,time,length,recur_type,interval_daily,interval_weekly,interval_yearly table_fields=name,start,end,time,length,recur_type
	@caption Kinnised ajad

	@property unavailable_weekends type=checkbox ch_value=1
	@caption Ei t&ouml;&ouml;ta n&auml;dalavahetustel

	@property unavailable_dates type=textarea rows=5 cols=50
	@comment Formaat: alguskuup&auml;ev.kuu, tund:minut - l&otilde;ppkuup&auml;ev.kuu, tund:minut; alguskuup&auml;ev.kuu, ...
	@caption Kinnised p&auml;evad (Formaat: <span style="white-space: nowrap;">p.k, h:m - p.k, h:m;</span><br /><span style="white-space: nowrap;">p.k, h:m - p.k, h:m;</span><br /> ...)

@groupinfo transl caption=T&otilde;lgi
@default group=transl

	@property transl type=callback callback=callback_get_transl store=no
	@caption T&otilde;lgi


// --------------- RELATION TYPES ---------------------

@reltype MRP_SCHEDULE value=2 clid=CL_PLANNER
@caption Ressursi kalender

@reltype MRP_OWNER value=3 clid=CL_MRP_WORKSPACE
@caption Ressursi omanik

@reltype RECUR value=4 clid=CL_RECURRENCE
@caption Kordus

@reltype RECUR_WRK value=5 clid=CL_RECURRENCE
@caption T&ouml;&ouml;aja kordus

*/

classload("mrp/mrp_header");

class mrp_resource extends class_base
{
	function mrp_resource()
	{
		$this->resource_states = array(
			0 => "M&auml;&auml;ramata",
			MRP_STATUS_RESOURCE_AVAILABLE => t("Vaba"),
			MRP_STATUS_RESOURCE_INUSE => t("Kasutusel"),
			MRP_STATUS_RESOURCE_OUTOFSERVICE => t("Suletud"),
			MRP_STATUS_RESOURCE_INACTIVE => t("Arhiveeritud"),
		);

		$this->states = array (
			MRP_STATUS_NEW => t("Uus"),
			MRP_STATUS_PLANNED => t("Planeeritud"),
			MRP_STATUS_INPROGRESS => t("T&ouml;&ouml;s"),
			MRP_STATUS_ABORTED => t("Katkestatud"),
			MRP_STATUS_DONE => t("Valmis"),
			MRP_STATUS_LOCKED => t("Lukustatud"),
			MRP_STATUS_PAUSED => t("Paus"),
			MRP_STATUS_DELETED => t("Kustutatud"),
			MRP_STATUS_ONHOLD => t("Plaanist v&auml;ljas"),
			MRP_STATUS_ARCHIVED => t("Arhiveeritud"),
		);

		$this->state_colours = array (
			MRP_STATUS_NEW => MRP_COLOUR_NEW,
			MRP_STATUS_PLANNED => MRP_COLOUR_PLANNED,
			MRP_STATUS_INPROGRESS => MRP_COLOUR_INPROGRESS,
			MRP_STATUS_ABORTED => MRP_COLOUR_ABORTED,
			MRP_STATUS_DONE => MRP_COLOUR_DONE,
			MRP_STATUS_PAUSED => MRP_COLOUR_PAUSED,
			MRP_STATUS_ONHOLD => MRP_COLOUR_ONHOLD,
			MRP_STATUS_ARCHIVED => MRP_COLOUR_ARCHIVED,
		);



		$this->trans_props = array(
			"name", "comment"
		);

		$this->init(array(
			"tpldir" => "mrp/mrp_resource",
			"clid" => CL_MRP_RESOURCE
		));
	}

	function callback_on_load ($arr)
	{
		if (!is_oid ($arr["request"]["id"]))
		{
			if (is_oid ($arr["request"]["mrp_workspace"]))
			{
				$this->workspace = obj ($arr["request"]["mrp_workspace"]);
				$this->resource_parent = $arr["request"]["mrp_parent"];
			}
			else
			{
				$this->mrp_error .= t("Uut ressurssi saab luua vaid ressursihalduskeskkonnast. ");
			}
		}
		else
		{
			$this_object = obj ($arr["request"]["id"]);
			$this->workspace = $this_object->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if (!$this->workspace)
			{
				$this->mrp_error .= t("Ressurss ei kuulu &uuml;hessegi ressursihalduss&uuml;steemi. ");
			}
		}

		if ($this->mrp_error)
		{
			echo t("Viga! ") . $this->mrp_error;
		}
	}

	function get_property($arr)
	{
		if ($this->mrp_error)
		{
			return PROP_IGNORE;
		}

		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object = &$arr["obj_inst"];

		switch($prop["name"])
		{
			case "cal_tb";
				$this->_get_cal_tb($arr);
				break;

			case "category":
				$resources_folder_id = $this->workspace->prop ("resources_folder");
				$parent_folder_id = $this_object->parent();
				$parents = "";

				while ($resources_folder_id and $parent_folder_id and ($parent_folder_id != $resources_folder_id))
				{
					$parent = obj ($parent_folder_id);
					$parents = "/" . $parent->name () . $parents;
					$parent_folder_id = $parent->parent ();
				}

				$prop["value"] = t("/Ressursid") . $parents;
				break;

			case "resource_calendar":
				### update schedule
				$schedule = get_instance (CL_MRP_SCHEDULE);
				$schedule->create (array("mrp_workspace" => $this->workspace->id()));

				$prop["value"] = $this->create_resource_calendar ($arr);
				break;

			case "thread_data":
				$prop["value"] = is_array ($this_object->prop ("thread_data")) ? count ($this_object->prop ("thread_data")) : 1;
				break;

			case "type":
				$prop["options"] = array (
					MRP_RESOURCE_SCHEDULABLE => t("Ressursi kasutust planeeritakse"),
					MRP_RESOURCE_NOT_SCHEDULABLE => t("Ressursi kasutust ei planeerita"),
					MRP_RESOURCE_SUBCONTRACTOR => t("Ressurss on allhange"),
				);
				break;

			case "state":
				list($prop["value"], $num_jobs) = $this->get_resource_state($arr["obj_inst"]);
				$prop["value"] = $this->resource_states[$prop["value"]]." (".$num_jobs.")";
				break;

			case "out_of_service":
				switch ($this_object->prop("state"))
				{
					case MRP_STATUS_RESOURCE_INUSE:
						$prop["disabled"] = true;
						break;

					case MRP_STATUS_RESOURCE_AVAILABLE:
						$prop["value"] = 0;
						break;

					case MRP_STATUS_RESOURCE_OUTOFSERVICE:
						$prop["value"] = 1;
						break;
				}
				break;

			case "job_list":
			case "job_list_done":
				### update schedule
				$schedule = get_instance (CL_MRP_SCHEDULE);
				$schedule->create (array("mrp_workspace" => $this->workspace->id()));

				$this->create_job_list_table ($arr);
				break;

			case "default_pre_buffer":
			case "default_post_buffer":
			case "global_buffer":
				$prop["value"] = $prop["value"] / 3600;
				break;
		}

		return $retval;
	}

	function get_resource_state($resource)
	{
		if (!is_oid($resource->id()))
		{
			return;
		}
		if ($resource->prop("state") == MRP_STATUS_RESOURCE_OUTOFSERVICE)
		{
			return array(MRP_STATUS_RESOURCE_OUTOFSERVICE, 0);
		}
		if ($resource->prop("state") == MRP_STATUS_RESOURCE_INACTIVE)
		{
			return array(MRP_STATUS_RESOURCE_INACTIVE, 0);
		}
		$max_jobs = max(1, count($resource->prop("thread_data")));
		$cur_jobs = $this->db_fetch_field("
			SELECT
				count(j.oid) AS cnt
			FROM
				mrp_job j
				LEFT JOIN objects o ON o.oid = j.oid
			WHERE
				j.resource = ".$resource->id()." AND
				o.status > 0 AND
				j.state IN (".MRP_STATUS_INPROGRESS.",".MRP_STATUS_PAUSED.")
		", "cnt");
		if ($cur_jobs >= $max_jobs)
		{
			return array(MRP_STATUS_RESOURCE_INUSE, $cur_jobs);
		}
		return array(MRP_STATUS_RESOURCE_AVAILABLE, $cur_jobs);
	}

	function callback_mod_reforb ($arr)
	{
		if ($this->workspace)
		{
			$arr["mrp_workspace"] = $this->workspace->id ();
		}

		if ($this->resource_parent)
		{
			$arr["mrp_parent"] = $this->resource_parent;
		}
	}

	function set_property ($arr = array ())
	{
		if ($this->mrp_error)
		{
			return PROP_FATAL_ERROR;
		}

		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object = &$arr["obj_inst"];

		### post rescheduling msg where necessary
		switch ($prop["name"])
		{
			case "thread_data":
				if (count ($this_object->prop ($prop["name"])) != $prop["value"] && $this->workspace->is_property("rescheduling_needed"))
				{
					$this->workspace->set_prop("rescheduling_needed", 1);
				}
				break;

			case "global_buffer":
				if ($this_object->prop ($prop["name"]) != $prop["value"] && $this->workspace->is_property("rescheduling_needed"))
				{
					$this->workspace->set_prop("rescheduling_needed", 1);
				}
				break;
		}

		switch ($prop["group"])
		{
			case "grp_resource_unavailable_work":
			case "grp_resource_unavailable_una":
			case "grp_resource_unavailable":
				if($this->workspace->is_property("rescheduling_needed"))
				{
					$this->workspace->set_prop("rescheduling_needed", 1);
				}
				break;
		}

		switch ($prop["name"])
		{
			case "default_pre_buffer":
			case "default_post_buffer":
			case "global_buffer":
				$prop["value"] = round ($prop["value"] * 3600);
				break;

			case "thread_data":
				$thread_data = $this_object->prop ("thread_data");
				$concurrent_threads = isset ($thread_data[0]["state"]) ? count ($thread_data) : false;
				$desired_count = ($prop["value"] < 1) ? 1 : (int) $prop["value"];

				if ($concurrent_threads != $desired_count)
				{
					if (!$concurrent_threads)
					{
						$thread_data = array_fill (0, $desired_count, array (
							"state" => MRP_STATUS_RESOURCE_AVAILABLE,
							"job" => NULL,
						));
					}
					elseif ($desired_count > $concurrent_threads)
					{
						$new_threads = $desired_count - $concurrent_threads;
						$thread_data = array_merge ($thread_data, array_fill ($concurrent_threads, $new_threads, array (
							"state" => MRP_STATUS_RESOURCE_AVAILABLE,
							"job" => NULL,
						)));
					}
					elseif ($desired_count < $concurrent_threads)
					{
						$thread_data = array_slice ($thread_data, 0, $desired_count);
					}

					$prop["value"] = $thread_data;
				}
				else
				{
					return PROP_IGNORE;
				}
				break;

			case "maintenance_history":
				if (strlen(trim($prop["value"]["comment"])) < 2)
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "work_hrs_recur":
				if (($arr["request"]["work_hrs_recur_action"] != "delete") and is_array ($prop["value"]))
				{
					$prop["value"]["recur_type"] = RECUR_DAILY;
					$prop["value"]["interval_daily"] = 1;
				}

			case "unavailable_recur":
				if (($arr["request"]["work_hrs_recur_action"] != "delete") and ($arr["request"]["unavailable_recur_action"] != "delete") and is_array ($prop["value"]))
				{
					$applicable_types = array (
						RECUR_DAILY,
						RECUR_WEEKLY,
						RECUR_YEARLY,
					);

					if (!in_array ($prop["value"]["recur_type"], $applicable_types))
					{
						$prop["error"] .= t("Seda t&uuml;&uuml;pi kordust ei saa kasutada. ") . MRP_NEWLINE;
					}

					### validate
					if (empty ($prop["value"]["time"]))
					{
						$prop["value"]["time"] = "00:00";
					}

					$time = explode (":", $prop["value"]["time"]);
					$time_h = abs ((int) $time[0]);
					$time_min = abs ((int) $time[1]);

					### check for user errors
					if ((23 < $time_h) or (59 < $time_min) or (count ($time) < 2))
					{
						$prop["error"] .= t("Viga kellaaja m&auml;&auml;rangus. ") . MRP_NEWLINE;
					}

					$interval_daily = $prop["value"]["interval_daily"] ? $prop["value"]["interval_daily"] : 1;
					$interval_weekly = $prop["value"]["interval_weekly"] ? $prop["value"]["interval_daily"] : 1;
					$interval_yearly = $prop["value"]["interval_yearly"] ? $prop["value"]["interval_daily"] : 1;

					if (
						((RECUR_DAILY == $prop["value"]["recur_type"]) and ((24*$interval_daily) < $prop["value"]["length"]))
						or ((RECUR_WEEKLY == $prop["value"]["recur_type"]) and ((24*7*$interval_weekly) < $prop["value"]["length"]))
						or ((RECUR_YEARLY == $prop["value"]["recur_type"]) and ((24*365*$interval_yearly) < $prop["value"]["length"]))
					)
					{
						$prop["error"] .= t("Pikkus ei saa olla suurem kui korduse periood. ") . MRP_NEWLINE;
					}

					if (empty ($prop["value"]["length"]))
					{
						$prop["error"] .= t("Pikkus ei saa olla null. ");
					}

					$start =  mktime(0, 0, 0, $prop["value"]["start"]["month"], $prop["value"]["start"]["day"], $prop["value"]["start"]["year"]);
					$end =  mktime(1, 0, 0, $prop["value"]["end"]["month"], $prop["value"]["end"]["day"], $prop["value"]["end"]["year"]);

					if ($start >= $end)
					{
						$prop["error"] .= t("'Alates' peab olema varasem aeg kui 'Kuni'. ") . MRP_NEWLINE;
					}

					if (!empty ($prop["error"]))
					{
						return PROP_ERROR;
					}

					$prop["value"]["time"] = $time_h . ":" . $time_min;
				}
				break;

			case "out_of_service":
				switch ($this_object->prop("state"))
				{
					case MRP_STATUS_RESOURCE_INUSE:
						if ($prop["value"] == 1)
						{
							$prop["error"] = t("Ressurss on kasutusel. Ei saa hooldusse panna. ");
							$retval = PROP_ERROR;
						}
						break;

					case MRP_STATUS_RESOURCE_AVAILABLE:
						if ($prop["value"] == 1)
						{
							$this_object->set_prop("state", MRP_STATUS_RESOURCE_OUTOFSERVICE);
						}
						break;

					case MRP_STATUS_RESOURCE_OUTOFSERVICE:
						if ($prop["value"] == 0)
						{
							$this_object->set_prop("state", MRP_STATUS_RESOURCE_AVAILABLE);
						}
						break;
				}
				break;

			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;
		}

		return $retval;
	}

	function callback_post_save ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$this->workspace->save ();

		### connect newly created obj. to workspace from which the req. was made
		if ($arr["new"] and is_oid ($arr["request"]["mrp_workspace"]))
		{
			$workspace = obj ($arr["request"]["mrp_workspace"]);
			$parent = is_oid ($arr["request"]["mrp_parent"]) ? $arr["request"]["mrp_parent"] : $workspace->prop ("resources_folder");
			$this_object->connect (array (
				"to" => $workspace,
				"reltype" => "RELTYPE_MRP_OWNER",
			));
			$this_object->set_parent ($parent);
			$this_object->set_prop ("state", MRP_STATUS_RESOURCE_AVAILABLE);
			$this_object->save ();
		}
	}

	function _init_job_list_table(&$table, $times = false)
	{

		/*||
			Ava | Staatus |
		    Projekti nr. | Klient |
			Projekti nimetus | Tr&uuml;kitud (ehk algus ehk esimese t&ouml;&ouml; t&ouml;&ouml;sse minek) [dd-kuu-yyyy] |
			T&auml;htaeg [dd-kuu-yyyy] |
			Tr&uuml;kiarv: |
			T&uuml;kiarv Notes: ||*/

		$table->define_field(array(
			"name" => "modify",
			"caption" => t("Ava"),
			"align" => "center"
		));
		$table->define_field(array(
			"name" => "state",
			"caption" => t("Staatus"),
			"sortable" => 1,
			"align" => "center"
		));
		$table->define_field(array(
			"name" => "proj_nr",
			"caption" => t("Projekti nr."),
			"sortable" => 1,
			"align" => "center"
		));

		$table->define_field(array(
			"name" => "client",
			"caption" => t("Klient"),
			"sortable" => 1,
			"align" => "center"
		));

		$table->define_field(array(
			"name" => "proj_com",
			"caption" => t("Projekti nimetus"),
			"sortable" => 1,
			"align" => "center"
		));

		$table->define_field(array(
			"name" => "starttime",
			"caption" => t("Tr&uuml;kitud"),
			"sortable" => 1,
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d-m-Y"
		));

		$table->define_field(array(
			"name" => "deadline",
			"caption" => t("T&auml;htaeg"),
			"sortable" => 1,
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d-m-Y"
		));

		if($times)
		{
			$table->define_field(array(
				"name" => "planned_length",
				"caption" => t("Planeeritud kestus"),
				"sortable" => 1,
				"align" => "center",
				"numeric" => 1,
			));

			$table->define_field(array(
				"name" => "real_length",
				"caption" => t("Tegelik kestus"),
				"sortable" => 1,
				"align" => "center",
				"numeric" => 1,
			));

			$table->define_field(array(
				"name" => "deviation",
				"caption" => t("H&auml;lve"),
				"sortable" => 1,
				"align" => "center",
			));
		}

		$table->define_field(array(
			"name" => "trykiarv",
			"caption" => t("Tr&uuml;kiarv"),
			"sortable" => 1,
			"align" => "center",
			"numeric" => 1,
		));


		$table->define_field(array(
			"name" => "trykiarv_notes",
			"caption" => t("Tr&uuml;kiarv Notes"),
			"sortable" => 1,
			"align" => "center"
		));
	}

	function create_job_list_table ($arr, $for_workspace = false)
	{
		$this_object =& $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];
		$done = $arr["prop"]["ame"] === "job_list_done" || $for_workspace;
		$this->_init_job_list_table($table, $done);

		$caption = sprintf($for_workspace ? t("Ressursi '%s' t&ouml;&ouml;d") : ($done ? t("Ressursi '%s' tehtud t&ouml;&ouml;d") : t("Ressursi '%s' eelseisvad t&ouml;&ouml;d")), parse_obj_name($this_object->name()));
		$table->set_caption($caption);

		$table->set_default_sortby ("starttime");
		$table->set_default_sorder ("asc");
		$table->draw_text_pageselector (array (
			"records_per_page" => 50,
		));

		### states for resource joblist
		if($for_workspace)
		{
			$applicable_project_states = $applicable_states = array(
				MRP_STATUS_DONE,
				MRP_STATUS_PLANNED,
				MRP_STATUS_PAUSED,
				MRP_STATUS_INPROGRESS,
			);
		}
		elseif($done)
		{
			$applicable_states = array(
				MRP_STATUS_DONE,
			);
			$applicable_project_states = array(
				MRP_STATUS_DONE,
				MRP_STATUS_PLANNED,
				MRP_STATUS_PAUSED,
				MRP_STATUS_INPROGRESS,
			);
		}
		else
		{
			$applicable_project_states = $applicable_states = array(
				MRP_STATUS_PLANNED,
				MRP_STATUS_PAUSED,
				MRP_STATUS_INPROGRESS,
			);
		}

		$list = new object_data_list(
			array(
				"class_id" => CL_MRP_JOB,
				"resource" => $this_object->id (),
				"state" => $applicable_states,
				// "starttime" => new obj_predicate_compare (OBJ_COMP_LESS, (time () + 886400)),
				new obj_predicate_sort(array("starttime" => "ASC")),
				"CL_MRP_JOB.project(CL_MRP_CASE).state" => $applicable_project_states,
			),
			array(
				CL_MRP_JOB => array("project", "exec_order", "state", "starttime", "RELTYPE_MRP_RESOURCE.name", "length"),
			)
		);
		$jobs = $list->arr();

		foreach($jobs as $oid => $o)
		{
			if(!$this->can("view", $o["project"]))
			{
				unset($jobs[$oid]);
			}
		}

		if(count($jobs) > 0)
		{
			$this->draw_job_list_table_from_list($table, $jobs, $done);
		}
	}

	function draw_job_list_table_from_list(&$table, $jobs, $times)
	{
		$perpage = 20;
		if(count($jobs) > $perpage)
		{
			$s = isset($_GET["ft_page"]) ? $_GET["ft_page"] * $perpage : 0;
			$table->define_pageselector(array(
				"type" => "lbtxt",
				"records_per_page" => $perpage,
				"d_row_cnt" => count($jobs),
				"no_recount" => true,
			));
			$jobs = array_slice($jobs, $s, $perpage, true);
		}
		foreach($jobs as $oid => $job)
		{
			### get project and client name
			$project = $client = "";

			$p = obj($job["project"]);
			$project = html::get_change_url($p->id(), array("return_url" => get_ru()), ($p->name() . "-" . $job["exec_order"]));

			if ($this->can("view", $p->prop("customer")))
			{
				$c = obj($p->prop("customer"));
				$client = html::get_change_url($c->id(), array("return_url" => get_ru()), $c->name());
			}

			### colour job status
			$state = '<span style="color: ' . $this->state_colours[$job["state"]] . ';">' . $this->states[$job["state"]] . '</span>';
			$change_url = html::get_change_url($oid, array("return_url" => get_ru()));

			$data = array (
				"modify" => html::href (array (
					"caption" => t("Ava"),
					"url" => $change_url,
					)),
				"project" => $project,
				"proj_nr" => html::obj_change_url($p),
				"proj_com" => $p->comment(),
				"state" => $state,
				"starttime" => $job["starttime"],
				"client" => $client,
				"deadline" => $p->prop("due_date"),
				"trykiarv" => $p->prop("trykiarv"),
				"trykiarv_notes" => $p->prop("trykiarv_notes"),
				"resource" => $job["RELTYPE_MRP_RESOURCE.name"],
			);
			if($times || $for_workspace)
			{
				$planned_length = (float) ($job["length"] / 3600);

				$data += array(
					"planned_length" => round($planned_length, 2),
				);
				if(MRP_STATUS_DONE == $job["state"])
				{
					// ARVUTA TEGELIK
					$this->db_query("SELECT * FROM mrp_stats WHERE job_oid = ".$oid);
					$real_len = 0;
					while ($row = $this->db_next())
					{
						$real_len += $row["length"];
					}
					$real_len = $real_len/3600;
					$deviation_float = (float) ($real_len - $planned_length);
					$deviation_percent = $deviation_float / $planned_length * 100;
					$data += array(
						"real_length" => round($real_len, 2),
						"deviation" => sprintf(t("%.2f (%.2f%%)"), round($deviation_float, 2), round($deviation_percent, 2)),
					);
				}
			}
			$table->define_data ($data);
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
			"full_weeks" => true,
		));
		$range = $calendar->get_range (array (
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"],
		));
		$start = $range["start"];
		$end = $range["end"];

		### states for resource joblist
		$applicable_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_PAUSED,
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_DONE,
		);

		$list = new object_list(array(
			"class_id" => CL_MRP_JOB,
			"state" => $applicable_states,
			"resource" => $this_object->id (),
			"starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, $start, $end),
		));

		$this->cal_items = array();
		if ($list->count () > 0)
		{
			for ($job =& $list->begin(); !$list->end(); $job =& $list->next())
			{
/* dbg */ if (!is_oid ($job->prop ("project"))) { echo "project is not an object. job:" . $job->id () . " proj:" . $job->prop ("project") ."<br>"; }
				if (!$this->can("view", $job->prop("project")))
				{
					continue;
				}

				### show only applicable projects' jobs
				$project = obj ($job->prop ("project"));
				$applicable_states = array (
					MRP_STATUS_PLANNED,
					MRP_STATUS_PAUSED,
					MRP_STATUS_INPROGRESS,
					MRP_STATUS_DONE,
				);

				if (in_array ($project->prop ("state"), $applicable_states))
				{
					$project_name = $project->name () ? $project->name () : "...";

					### set timestamp according to state
					$timestamp = ($job->prop ("state") == MRP_STATUS_DONE) ? $job->prop ("started") : $job->prop ("starttime");

					### colour job status
					$state = '<span style="color: ' . $this->state_colours[$job->prop ("state")] . ';">' . $this->states[$job->prop ("state")] . '</span>';

					### ...
					$calendar->add_item (array (
						"timestamp" => $timestamp,
						"data" => array(
							"name" => '<span  style="white-space: nowrap;">' . $project_name . "-" . $job->prop ("exec_order") . " [" . $state . "]</span>",
							"link" => html::get_change_url($job->id(), array("return_url" => get_ru()))   /*$this->mk_my_orb ("change",array ("id" => $job->id ()), "mrp_job")*/,
						),
					));
					$this->cal_items[$timestamp] = html::get_change_url($job->id(), array("return_url" => get_ru()));
				}
			}
		}
		$list = new object_list(array(
			"class_id" => array(CL_CRM_MEETING, CL_TASK),
			"CL_TASK.RELTYPE_RESOURCE" => $this_object->id(),
		));
		foreach($list->arr() as $task)
		{
			$calendar->add_item (array (
				"item_start" => $task->prop("start1"),
				"item_end" => $task->prop("end"),
				"data" => array(
					"name" => $task->name(),
					"link" => html::get_change_url($task->id(), array("return_url" => get_ru())),
				),
			));
			$this->cal_items[$task->prop("start1")] = html::get_change_url($task->id(), array("return_url" => get_ru()));
		}

		return $calendar->get_html ();
	}

	function get_overview ($arr = array())
	{
		/*$start = time() - (24*3600*60);
		$end = time() + (24*3600*60);

		for($i = $start; $i < $end; $i += (24*3600))
		{
			$ret[$i] = aw_url_change_var("viewtype", "week", aw_url_change_var("date", date("d", $i)."-".date("m", $i)."-".date("Y", $i)));
		}*/

		return $this->cal_items;
	}

	function get_unavailable_periods ($resource, $start, $end)
	{
// /* dbg */ if ($resource->id () == 6670  ) {
// /* dbg */ $this->mrpdbg=1;
// /* dbg */ }

		$period_start = $start;
		$period_end = $end;
		$unavailable_dates = array ();
		$dates = $resource->prop ("unavailable_dates");
		$dates = explode (";", $dates);
		$separators = " ,.:/|-\\";
		$period_start_year = date ("Y", $period_start);
		foreach ($dates as $date)
		{
			$start_day = (int) strtok ($date, $separators);
			$start_mon = (int) strtok ($separators);
			$start_hour = (int) strtok ($separators);
			$start_min = (int) strtok ($separators);
			$end_day = (int) strtok ($separators);
			$end_mon = (int) strtok ($separators);
			$end_hour = (int) strtok ($separators);
			$end_min = (int) strtok ($separators);
			$in_period_range = true;
			$year = $period_start_year;

			while ($in_period_range)
			{
				$start = mktime ($start_hour, $start_min, 0, $start_mon, $start_day, $year);
				$end = mktime ($end_hour, $end_min, 0, $end_mon, $end_day, $year);

				if ($start < $period_end)
				{
					if ($start < $end)
					{
						$unavailable_dates[$start] = max ($end, $unavailable_dates[$start]);
					}
				}
				else
				{
					$in_period_range = false;
				}

				$year++;
			}
		}

		foreach ($unavailable_dates as $start => $end)
		{
			if ($end <= $period_start)
			{
				unset ($unavailable_dates[$start]);
			}
		}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "unavailable_dates:";
// /* dbg */ arr ($unavailable_dates);
// /* dbg */ }

		return $unavailable_dates;
	}

	function get_recurrent_unavailable_periods ($resource, $start, $end)
	{
// /* dbg */ if ($resource->id () == 6670  ) {
// /* dbg */ $this->mrpdbg=1;
// /* dbg */ }

		### unavailable recurrences
		$recurrent_unavailable_periods = array ();
		$start = mktime (0, 0, 0, date ("m", $start), date ("d", $start), date("Y", $start));
		$end = mktime (0, 0, 0, date ("m", $end), date ("d", $end), date("Y", $end));

		if ($resource->prop ("unavailable_weekends"))
		{
			$weekend_start = $this->get_week_start ($start) + (5 * 86400);
			$weekend_length = 172800;
			$recurrent_unavailable_periods[] = array (
				"length" => $weekend_length,
				"start" => $weekend_start,
				"time" => 0,
				"end" => $end,
				"max_span" => $end + $weekend_length,
				"interval" => 604800,
			);
		}

		foreach ($resource->connections_from (array ("type" => "RELTYPE_RECUR")) as $connection)
		{
			$recurrence = $connection->to ();

			if ( !(($recurrence->prop ("start") > $end) or ($recurrence->prop ("end") < $start)) )
			{
				switch ($recurrence->prop ("recur_type"))
				{
					case RECUR_DAILY: //day
						$interval = $recurrence->prop ("interval_daily");
						$interval = round (($interval ? $interval : 1) * 86400);
						break;

					case RECUR_WEEKLY: //week
						$interval = $recurrence->prop ("interval_weekly");
						$interval = round (($interval ? $interval : 1) * 86400 * 7);
						break;

					case RECUR_YEARLY: //year
						$interval = $recurrence->prop ("interval_yearly");
						$interval = round (($interval ? $interval : 1) * 86400 * 365);
						break;

					default:
						continue;
				}

				$recurrence_starttime = $recurrence->prop ("time");
				$recurrence_starttime = explode (":", $recurrence_starttime);
				$recurrence_starttime_hours = $recurrence_starttime[0] ? (int) $recurrence_starttime[0] : 0;
				$recurrence_starttime_minutes = $recurrence_starttime[1] ? (int) $recurrence_starttime[1] : 0;
				$recurrence_starttime = $recurrence_starttime_hours * 3600 + $recurrence_starttime_minutes * 60;

				$recurrent_unavailable_periods[] = array (
					"length" => round ($this->safe_settype_float ($recurrence->prop ("length")) * 3600),
					"start" => $recurrence->prop ("start"),
					"time" => $recurrence_starttime,
					"end" => $recurrence->prop ("end"),
					"max_span" => $recurrence->prop ("end") + $time + $length,
					"interval" => $interval,
				);
			}
		}


		### add workhours (available recurrences)
		$recurrent_available_periods = array ();

		foreach ($resource->connections_from (array ("type" => "RELTYPE_RECUR_WRK")) as $connection)
		{
			$recurrence = $connection->to ();

			if ( !(($recurrence->prop ("start") > $end) or ($recurrence->prop ("end") < $start)) )
			{
				$interval = 86400;
				list ($recurrence_time_hours, $recurrence_time_minutes) = explode (":", $recurrence->prop ("time"), 2);
				$recurrence_time = abs ((int) $recurrence_time_hours) * 3600 + abs ((int) $recurrence_time_minutes) * 60;
				$recurrence_length = round ($this->safe_settype_float ($recurrence->prop ("length")) * 3600);

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "recurrent_available_period time:" . $recurrence_time . "<br>";
// /* dbg */ }

				$recurrent_available_periods[] = array (
					"length" => $recurrence_length,
					"start" => $recurrence->prop ("start"),
					"time" => $recurrence_time,
					"end" => $recurrence->prop ("end"),
					"interval" => $interval,
				);
			}
		}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "recurrent_available_periods:";
// /* dbg */ arr ($recurrent_available_periods);
// /* dbg */ }

		### transmute recurrently available periods to unavailables
		### throw away erroneous definitions
		foreach ($recurrent_available_periods as $key => $available_period)
		{
			if ( ($available_period["start"] >= $available_period["end"]) or ($available_period["length"] > 86400) or ($available_period["length"] < 1) )
			{
				unset ($recurrent_available_periods[$key]);
			}
		}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "recurrent_available_periods after errorcheck:";
// /* dbg */ arr ($recurrent_available_periods);
// /* dbg */ exit;
// /* dbg */ }

		### find combinations of available periods
		$combination_breakpoints = array ($start, $end);

		foreach ($recurrent_available_periods as $available_period)
		{
			if (($available_period["start"] > $start) and ($available_period["start"] < $end))
			{
				$combination_breakpoints[] = $available_period["start"];
			}

			if (($available_period["end"] > $start) and ($available_period["end"] < $end))
			{
				$combination_breakpoints[] = $available_period["end"];
			}
		}

		### make unavailable recurrence definitions according to these combinations
		usort ($recurrent_available_periods, array ($this, "sort_recurrences_by_start"));
		sort ($combination_breakpoints, SORT_NUMERIC);
		$interval = 86400;

		foreach ($combination_breakpoints as $bp_key => $breakpoint)
		{
			if (isset ($combination_breakpoints[$bp_key + 1]))
			{
				$combination_start = $breakpoint;
				$combination_end = $combination_breakpoints[$bp_key + 1];
				$combination = array ();

				foreach ($recurrent_available_periods as $available_period)
				{
					if ( ($available_period["start"] <= $combination_start) and ($available_period["end"] >= $combination_end) )
					{
						$combination[] = $available_period;
					}
				}

				usort ($combination, array ($this, "sort_recurrences_by_time"));

				foreach ($combination as $key => $available_period)
				{
					$time = ($available_period["time"] + $available_period["length"]) % $interval;

					if (isset ($combination[$key + 1]))
					{
						$end_time = $combination[$key + 1]["time"];
					}
					else
					{
						$end_time = $combination[0]["time"];
					}

					if ($end_time > $time)
					{
						$length = $end_time - $time;
					}
					else
					{
						$length = $end_time + ($interval - $time);
					}

					$recurrent_unavailable_periods[] = array (
						"length" => $length,
						"start" => $combination_start,
						"end" => $combination_end,
						"time" => $time,
						"max_span" => $recurrence->prop ("end") + $time + $length,
						"interval" => $interval,
					);
				}
			}
		}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "return recurrent_unavailable_periods:";
// /* dbg */ foreach ($recurrent_unavailable_periods as $key => $value){ echo "<hr>";
// /* dbg */ echo "length: " . ($value["length"]/3600).  "h<br>";
// /* dbg */ echo "start: " . date (MRP_DATE_FORMAT, $value["start"]).  "<br>";
// /* dbg */ echo "end: " . date (MRP_DATE_FORMAT, $value["end"]).  "<br>";
// /* dbg */ echo "time: " . date ("H.i", mktime(0,0,0,1,1,2005) + $value["time"]).  "<br>";
// /* dbg */ echo "interval: " . ($value["interval"]/3600).  "h<br>";}
// /* dbg */ }

		return $recurrent_unavailable_periods;
	}

	function get_week_start ($time = false) //!!! somewhat dst safe (safe if error doesn't exceed 12h)
	{
		if (!$time)
		{
			$time = time ();
		}

		$date = getdate ($time);
		$wday = $date["wday"] ? ($date["wday"] - 1) : 6;
		$week_start = $time - ($wday * 86400 + $date["hours"] * 3600 + $date["minutes"] * 60 + $date["seconds"]);
		$nodst_hour = (int) date ("H", $week_start);

		if ($nodst_hour === 0)
		{
			$week_start = $week_start;
		}
		else
		{
			if ($nodst_hour < 13)
			{
				$dst_error = $nodst_hour;
				$week_start = $week_start - $dst_error*3600;
			}
			else
			{
				$dst_error = 24 - $nodst_hour;
				$week_start = $week_start + $dst_error*3600;
			}
		}

		return $week_start;
	}

	function sort_recurrences_by_start ($recurrence1, $recurrence2)
	{
		if ($recurrence1["start"] > $recurrence2["start"])
		{
			$result = 1;
		}
		elseif ($recurrence1["start"] < $recurrence2["start"])
		{
			$result = -1;
		}
		else
		{
			$result = 0;
		}

		return $result;
	}

	function sort_recurrences_by_time ($recurrence1, $recurrence2)
	{
		if ($recurrence1["time"] > $recurrence2["time"])
		{
			$result = 1;
		}
		elseif ($recurrence1["time"] < $recurrence2["time"])
		{
			$result = -1;
		}
		else
		{
			$result = 0;
		}

		return $result;
	}

/**
    @attrib name=start_job
	@param resource required type=int
	@param job required type=int
**/
	function start_job ($arr)
	{
		if (is_oid ($arr["resource"]) and is_oid ($arr["job"]))
		{
			$resource = obj ($arr["resource"]);
		}
		else
		{
			return false;
		}

		/*switch ($resource->prop ("state"))
		{
			case MRP_STATUS_RESOURCE_AVAILABLE:
				$thread_data = $resource->prop ("thread_data");

				if (!is_array($thread_data))
				{
					$thread_data = array ();
					for ($i = 1; $i <= max(1, $resource->prop("thread_data")); $i++)
					{
						$thread_data[$i] = array("state" => MRP_STATUS_RESOURCE_AVAILABLE);
					}
				}

				$started = false;
				$last_thread = true;

				foreach ($thread_data as $key => $thread)
				{
					if ( ($thread["state"] == MRP_STATUS_RESOURCE_AVAILABLE) and ($started === false) )
					{
						$thread_data[$key]["state"] = MRP_STATUS_RESOURCE_INUSE;
						$thread_data[$key]["job"] = $arr["job"];
						$started = $key;
					}
					elseif ( ($thread["state"] == MRP_STATUS_RESOURCE_AVAILABLE) and ($started !== false) )
					{
						$last_thread = false;
						break;
					}
				}

				if ($last_thread)
				{
					$resource->set_prop ("state", MRP_STATUS_RESOURCE_INUSE);
				}

				$resource->set_prop ("thread_data", $thread_data);
				aw_disable_acl();
				$resource->save ();
				aw_restore_acl();*/
				//return $started;
				$max_jobs = max(1, count($resource->prop("thread_data")));
				$cur_jobs = $this->db_fetch_field("
					SELECT
						count(j.oid) AS cnt
					FROM
						mrp_job j
						LEFT JOIN objects o ON o.oid = j.oid
					WHERE
						j.resource = ".$resource->id()." AND
						o.status > 0 AND
						j.state IN (".MRP_STATUS_INPROGRESS.",".MRP_STATUS_PAUSED.")
				", "cnt");
				// compare
				if ($cur_jobs >= $max_jobs)
				{
					return false;
				}
				return true;
/*			default:
				return false;
		}*/
	}

/**
    @attrib name=stop_job
	@param resource required type=int
	@param job required type=int
**/
	function stop_job ($arr)
	{
		if (is_oid ($arr["resource"]) and is_oid ($arr["job"]))
		{
			$resource = obj ($arr["resource"]);
		}
		else
		{
			return false;
		}

		$thread_data = $resource->prop ("thread_data");

		foreach ($thread_data as $key => $thread)
		{
			if ($thread["job"] == $arr["job"])
			{
				$thread_data[$key]["state"] = MRP_STATUS_RESOURCE_AVAILABLE;
				$thread_data[$key]["job"] = NULL;
				break;
			}
		}

		$resource->set_prop ("thread_data", $thread_data);
		$resource->set_prop ("state", MRP_STATUS_RESOURCE_AVAILABLE);
		aw_disable_acl();
		$resource->save ();
		aw_restore_acl();
		return true;
	}

	function on_create_resource ($arr)
	{
		$resource = obj ($arr["oid"]);

		### set state
		$resource->set_prop ("state", MRP_STATUS_RESOURCE_AVAILABLE);

		### init thread_data
		$thread_data = array(1 => array ("state" => MRP_STATUS_RESOURCE_AVAILABLE, "job" => NULL));
		$resource->set_prop ("thread_data", $thread_data);

		aw_disable_acl();
		$resource->save ();
		aw_restore_acl();
	}

	function safe_settype_float ($value)
	{
		$separators = ".,";
		$int = (int) preg_replace ("/\s*/S", "", strtok ($value, $separators));
		$dec = preg_replace ("/\s*/S", "", strtok ($separators));
		return (float) ("{$int}.{$dec}");
	}

	function get_events_for_range($resource, $start, $end)
	{
		$applicable_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_PAUSED,
			MRP_STATUS_INPROGRESS,
		);

		$list = new object_list(array(
			"class_id" => CL_MRP_JOB,
			"state" => $applicable_states,
			"resource" => $resource->id (),
			"starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, $start, $end),
		));

		$ret = array();
		if ($list->count () > 0)
		{
			for ($job =& $list->begin(); !$list->end(); $job =& $list->next())
			{
				if (!$this->can("view", $job->prop("project")))
				{
					continue;
				}

				### show only applicable projects' jobs
				$project = obj ($job->prop ("project"));
				$applicable_states = array (
					MRP_STATUS_PLANNED,
					MRP_STATUS_PAUSED,
					MRP_STATUS_INPROGRESS,
					MRP_STATUS_DONE,
				);

				if (in_array ($project->prop ("state"), $applicable_states))
				{
					$project_name = $project->name () ? $project->name () : "...";

					### set timestamp according to state
					$timestamp = ($job->prop ("state") == MRP_STATUS_DONE) ? $job->prop ("started") : $job->prop ("starttime");

					$ret[] = array(
						"start" => $timestamp,
						"end" => $timestamp + $job->prop("planned_length"),
						"name" => $job->name()
					);
				}
			}
		}
		$list = new object_list(array(
			"class_id" => array(CL_CRM_MEETING, CL_TASK),
			"CL_TASK.RELTYPE_RESOURCE" => $resource->id(),
		));
		foreach($list->arr() as $task)
		{
			if ($task->prop("start1") > $end || $task->prop("end") < $start)
			{
				continue;
			}
			$ret[] = array(
				"start" => $task->prop("start1"),
				"end" => $task->prop("end"),
				"name" => $task->name()
			);
		}

		return $ret;
	}

	function is_available_for_range($resource, $start, $end)
	{
		$avail = true;
		$evstr = "";
		$ri = $resource->instance();
		$events = $ri->get_events_for_range(
			$resource,
			$start,
			$end
		);
		if (count($events))
		{
			$avail = false;
			$evstr = t("Ressurss on valitud aegadel kasutuses:<br>");
			foreach($events as $event)
			{
				$evstr .= date("d.m.Y H:i", $event["start"])." - ".
						  date("d.m.Y H:i", $event["end"])."  ".$event["name"]."<br>";
			}
		}

		if ($avail)
		{
			$una = $ri->get_unavailable_periods(
				$resource,
				$start,
				$end
			);

			if (count($una))
			{
				$avail = false;
				$evstr = t("Ressurss ei ole valitud aegadel kasutatav!<br>Kinnised ajad:<br>");
				foreach($una as $event)
				{
					$evstr .= date("d.m.Y H:i", $event["start"])." - ".
							  date("d.m.Y H:i", $event["end"]).": ".$event["name"];
				}
			}
		}

		if ($avail)
		{
			$una = $ri->get_recurrent_unavailable_periods(
				$resource,
				$start,
				$end
			);
			if (count($una))
			{
				$avail = false;
				$evstr = t("Ressurss ei ole valitud aegadel kasutatav!<br>Kinnised ajad:<br>");
				foreach($una as $event)
				{
					$evstr .= date("d.m.Y H:i", $event["start"])." - ".
							  date("d.m.Y H:i", $event["end"])."<br>";
				}
			}
		}

		if ($avail)
		{
			return true;
		}
		return $evstr;
	}

	function _get_cal_tb($arr)
	{
		$tb =&  $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$pl = get_instance(CL_PLANNER);
		$cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		$clids = array(CL_TASK => 13, CL_CRM_MEETING => 11, CL_CRM_CALL => 12/*, CL_CRM_OFFER => 9*/);
		$clss = aw_ini_get("classes");

		$u = get_instance(CL_USER);
		$cur_co = $u->get_current_company();

		foreach($clids as $clid => $relt)
		{
			$url = $this->mk_my_orb('new',array(
				'add_to_cal' => $cal_id,
				'clid' => $clid,
				'title' => $clss[$clid]["name"],
				'parent' => $arr["obj_inst"]->id(),
				'return_url' => get_ru(),
				"set_resource" => $arr["obj_inst"]->id()
			), $clid);
			$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text' => $clss[$clid]["name"],
				'link' => $url
			));
		}
	}

	function callback_mod_tab($arr)
	{
		$trc = aw_ini_get("user_interface.trans_classes");

		if ($arr["id"] == "transl" && (aw_ini_get("user_interface.content_trans") != 1 && !$trc[$this->clid]))
		{
			return false;
		}
		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}
}

?>
