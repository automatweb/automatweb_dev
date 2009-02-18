<?php
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_MRP_JOB, on_delete_job)

@classinfo syslog_type=ST_MRP_JOB relationmgr=yes no_status=1 confirm_save_data=1 maintainer=voldemar

@tableinfo mrp_job index=oid master_table=objects master_index=oid
@tableinfo mrp_schedule index=oid master_table=objects master_index=oid

@groupinfo data caption="Andmed"
@groupinfo workflow caption="T&ouml;&ouml;voog"
@groupinfo materials caption="Materjalid"


@default group=general
	@property name type=text
	@caption Nimi

	@property comment type=textarea table=objects field=comment
	@caption Kommentaar

@default group=workflow
	@property job_toolbar type=toolbar no_caption=1 store=no

	@property workflow_errors type=text store=no no_caption=1

@default table=mrp_schedule
	@property starttime type=text
	@caption Plaanitud t&ouml;&ouml;sseminekuaeg

	@property planned_length type=text
	@caption Planeeritud kestus (h)


@default table=mrp_job
	@property started type=text
	@caption Alustatud

	@property finished type=text
	@caption L&otilde;petatud

	@property resource type=text
	@caption Ressurss

	@property project type=hidden
	@caption Projekt

	@property exec_order type=hidden
	@caption T&ouml;&ouml; jrk. nr.

	@property state type=text
	@caption Staatus

@default group=materials

	@property materials_sel_tbl type=table no_caption=1

	@property materials_tbl type=table no_caption=1

@default group=data
	@property length type=textbox
	@caption T&ouml;&ouml; pikkus (h)

	@property pre_buffer type=textbox
	@caption Eelpuhveraeg (h)

	@property post_buffer type=textbox
	@caption J&auml;relpuhveraeg (h)

	@property minstart type=datetime_select
	@comment Enne seda kuup&auml;eva, kellaaega ei alustata t&ouml;&ouml;d
	@caption Varaseim alustusaeg

	@property remaining_length type=textbox
	@comment Arvatav ajakulu t&ouml;&ouml; j&auml;reloleva osa tegemiseks
	@caption L&otilde;petamiseks kuluv aeg (h)

	@property prerequisites type=text
	@caption Eeldust&ouml;&ouml;d


@default table=objects
@default field=meta
@default method=serialize
	@property advised_starttime type=datetime_select
	@comment Allhankijaga kokkulepitud aeg, millal t&ouml;&ouml; alustada.
	@caption Soovitav algusaeg



// --------------- RELATION TYPES ---------------------

@reltype MRP_RESOURCE value=1 clid=CL_MRP_RESOURCE
@caption T&ouml;&ouml;ks kasutatav ressurss

@reltype MRP_PROJECT value=2 clid=CL_MRP_CASE
@caption Projekt

*/

/*

CREATE TABLE `mrp_job` (
  `oid` int(11) NOT NULL default '0',
  `length` int(10) unsigned NOT NULL default '0',
  `planned_length` int(10) unsigned NOT NULL default '0',
  `resource` int(11) unsigned default NULL,
  `exec_order` smallint(5) unsigned NOT NULL default '1',
  `project` int(11) unsigned default NULL,
  `minstart` int(10) unsigned default NULL,
  `starttime` int(10) unsigned default NULL,
  `prerequisites` char(255) default NULL,
  `state` tinyint(2) unsigned default '1',
  `pre_buffer` int(10) unsigned default NULL,
  `post_buffer` int(10) unsigned default NULL,
  `started` int(10) unsigned default NULL,
  `finished` int(10) unsigned default NULL,
  `remaining_length` int(10) unsigned default NULL,

	PRIMARY KEY  (`oid`)
) TYPE=MyISAM;

CREATE TABLE `mrp_schedule` (
	`oid` int(11) NOT NULL default '0',
	`planned_length` int(10) unsigned NOT NULL default '0',
	`starttime` int(10) unsigned default NULL,

	PRIMARY KEY  (`oid`)
) TYPE=MyISAM;

*/

classload("mrp/mrp_header");

class mrp_job extends class_base
{
	var $mrp_error = false;

	function mrp_job ()
	{
		$this->states = array(
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

		$this->init(array(
			"tpldir" => "mrp/mrp_job",
			"clid" => CL_MRP_JOB,
		));
	}

	function callback_on_load ($arr)
	{
		if (!is_oid ($arr["request"]["id"]))
		{
			$this->mrp_error .= t("Uut t&ouml;&ouml;d saab luua vaid ressursihalduskeskkonnas. ");
		}
		else
		{
			$this_object = obj($arr["request"]["id"]);
			$project_id = $this_object->prop ("project");
			$resource_id = $this_object->prop ("resource");

			if ( $this->can("view", $project_id) and $this->can("view", $resource_id) )
			{
				$this->project = obj ($project_id);
				$this->resource = obj ($resource_id);
				$this->workspace = $this->project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

				if (!$this->workspace or !$this->project or !$this->resource)
				{
					$this->mrp_error .= t("T&ouml;&ouml;l puudub ressurss, projekt v&otilde;i ressursihalduskeskkond. ");
				}
			}
			else
			{
				if (is_oid($project_id))
				{
					$this->mrp_error .= t("T&ouml;&ouml;l puudub ressurss. ");
				}
				elseif (is_oid($resource_id))
				{
					$this->mrp_error .= t("T&ouml;&ouml;l puudub projekt. ");
				}
				else
				{
					$this->mrp_error .= t("T&ouml;&ouml;l puudub ressurss ja projekt. ");
				}
			}
		}

		if ($this->mrp_error)
		{
			echo t("Viga! ") . $this->mrp_error;
		}
	}

	function get_property ($arr)
	{
		if ($this->mrp_error)
		{
			return PROP_IGNORE;
		}

		$this_object =& $arr["obj_inst"];
		$prop =& $arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "name":
				$project_name = $this->project->name () ? $this->project->name () : "...";
				$resource_name = $this->resource->name () ? $this->resource->name () : "...";
				$prop["value"] = $project_name . " - " . $resource_name;
				break;

			case "prerequisites":
				$prerequisites = explode (",", $prop["value"]);

				foreach ($prerequisites as $prerequisite_oid)
				{
					$prerequisite = obj ($prerequisite_oid);
					$prerequisite_orders[] = $prerequisite->prop ("exec_order");
				}

				$prop["value"] = implode (",", $prerequisite_orders);
				break;

			case "resource":
				if (is_object($this->resource))
				{
					$prop["value"] = html::get_change_url(
						$this->resource->id(),
						array("return_url" => get_ru()),
						$this->resource->name ()
					);
				}
				break;

			case "workflow_errors":
				if (!empty ($arr["request"]["errors"]))
				{
					$errors = $arr["request"]["errors"];
					$this->dequote ($errors);
					$errors = unserialize ($errors);

					if (!empty ($errors))
					{
						$prop["value"] = ' <div style="color: #DF0D12; margin: 5px;">' . t('Esinenud t&otilde;rked: ') . implode (". ", $errors) . '.</div>';
						unset ($arr["request"]["errors"]);
					}
				}
				break;

			case "length":
			case "planned_length":
			case "pre_buffer":
			case "post_buffer":
			case "remaining_length":
				$prop["value"] = round (($prop["value"] / 3600), 2);
				break;

			case "advised_starttime":
				if ($this->resource->prop("type") != MRP_RESOURCE_SUBCONTRACTOR)
				{
					return PROP_IGNORE;
				}
				break;

			case "state":
				$prop["value"] = $this->states[$prop["value"]] ? $this->states[$prop["value"]] : t("M&auml;&auml;ramata");
				break;

			case "starttime":
				$prop["value"] = $prop["value"] ? date(MRP_DATE_FORMAT, $prop["value"]) : t("Planeerimata");
				break;

			case "started":
				$prop["value"] = $prop["value"] ? date(MRP_DATE_FORMAT, $prop["value"]) : t("T&ouml;&ouml;d pole veel alustatud");
				break;

			case "finished":
				$prop["value"] = ($this_object->prop ("state") == MRP_STATUS_DONE) ? date(MRP_DATE_FORMAT, $prop["value"]) : t("T&ouml;&ouml;d pole veel l&otilde;petatud");
				break;

			case "job_toolbar":
				$this->create_job_toolbar ($arr);
				break;

			case "materials_sel_tbl":
				$this->create_materials_sel_tbl($arr);
				break;

			case "materials_tbl":
				$this->create_materials_tbl($arr);
				break;
		}

		return $retval;
	}

	function set_property ($arr = array())
	{
		if ($this->mrp_error)
		{
			return PROP_FATAL_ERROR;
		}

		$this_object =& $arr["obj_inst"];
		$prop =& $arr["prop"];
		$retval = PROP_OK;

		### post rescheduling msg where necessary
		$applicable_planning_states = array(
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PAUSED,
			MRP_STATUS_PLANNED,
		);

		switch ($prop["name"])
		{
			case "length":
			case "pre_buffer":
			case "post_buffer":
			case "prerequisites":
			case "minstart":
				if ( in_array ($this_object->prop ("state"), $applicable_planning_states) and ($this_object->prop ($prop["name"]) != $prop["value"]) )
				{
					$this->workspace->set_prop("rescheduling_needed", 1);
				}
				break;
		}


		switch($prop["name"])
		{
			case "advised_starttime":
				if ($this->resource->prop("type") != MRP_RESOURCE_SUBCONTRACTOR)
				{
					return PROP_IGNORE;
				}
				break;

			case "length":
			case "pre_buffer":
			case "post_buffer":
				$prop["value"] = round ($prop["value"] * 3600);
				break;

			case "materials_tbl":
				$this->save_materials($arr);
				break;
		}

		return $retval;
	}

	function callback_post_save ($arr)
	{
		$this->workspace->save ();
	}

	function create_job_toolbar ($arr = array())
	{
		$toolbar =& $arr["prop"]["toolbar"];
		$this_object =& $arr["obj_inst"];

		### start button
		if ( ($this_object->prop ("state") == MRP_STATUS_PLANNED) and ($this->can_start(array("job" => $this_object->id()))) )
		{
			$disabled = false;
		}
		else
		{
			$disabled = true;
		}

		$toolbar->add_button(array(
			"name" => "start",
			"tooltip" => t("Alusta"),
			"action" => "start",
			"confirm" => t("Oled kindel et soovid t&ouml;&ouml;d alustada?"),
			"disabled" => $disabled,
		));

		### done, abort, pause, end_shift buttons
		if ($this_object->prop ("state") == MRP_STATUS_INPROGRESS)
		{
			$disabled_inprogress = false;
		}
		else
		{
			$disabled_inprogress = true;
		}

		$toolbar->add_button(array(
			"name" => "done",
			"tooltip" => t("Valmis"),
			"action" => "done",
			"confirm" => t("Oled kindel et soovid t&ouml;&ouml;d l&otilde;petada?"),
			"disabled" => $disabled_inprogress,
		));
		$toolbar->add_button(array(
			"name" => "pause",
			"tooltip" => t("Paus"),
			"action" => "pause",
			"disabled" => $disabled_inprogress,
			"confirm" => t("Oled kindel et soovid t&ouml;&ouml;d pausile panna?"),
		));
		$toolbar->add_button(array(
			"name" => "end_shift",
			"confirm" => t("L&otilde;peta vahetus ja logi v&auml;lja?"),
			"tooltip" => t("Vahetuse l&otilde;pp"),
			"action" => "end_shift",
			"disabled" => $disabled_inprogress,
		));

		### continue button
		if ($this_object->prop("state") == MRP_STATUS_PAUSED)
		{
			$disabled = false;
			$action = "scontinue";
		}
		elseif ($this_object->prop("state") == MRP_STATUS_ABORTED)
		{
			$disabled = false;
			$action = "acontinue";
		}
		else
		{
			$disabled = true;
		}

		$toolbar->add_button(array(
			"name" => "continue",
			"tooltip" => t("J&auml;tka"),
			"action" => $action,
			"disabled" => $disabled,
			"confirm" => t("Oled kindel et soovid t&ouml;&ouml;d j&auml;tkata?"),
		));

		$toolbar->add_button(array(
			"name" => "abort",
			"tooltip" => t("Katkesta"),
			//"action" => "abort",
			"url" => "#",
			"confirm" => t("Katkesta t&ouml;&ouml;?"),
			"onClick" => "if (document.changeform.pj_change_comment.value.replace(/\\s+/, '') != '') { submit_changeform('abort') } else { alert('" . t("Kommentaar peab olema t&auml;idetud!") . "'); }",
			"disabled" => $disabled_inprogress,
		));
	}

	function state_changed($job, $com)
	{
		$ws = get_instance(CL_MRP_WORKSPACE);
		$com_txt = "T&ouml;&ouml; ".$job->name()." staatus muudeti ".$this->states[$job->prop("state")];
		$ws->mrp_log($job->prop("project"), $job->id(), $com_txt, $com);
		$this->add_comment($job->id(), $com_txt." ".$com);
	}

	function stats_start($job)
	{
		$case = $job->prop("project");
		$res = $job->prop("resource");
		$job_id = $job->id();
		$uid = aw_global_get("uid");
		$start = time();
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		$person_name = $p->name();

		$cnt = $this->db_fetch_field("SELECT count(*) as cnt FROM mrp_stats
			WHERE
				case_oid = $case AND
				resource_oid = $res AND
				job_oid = $job_id AND
				uid = '$uid'",
			"cnt");
		if ($cnt == 0)
		{
			$this->db_query("INSERT INTO mrp_stats(
				case_oid, resource_oid, job_oid, uid, start, end, length, last_start, person_name
			)
			VALUES(
				$case, $res, $job_id, '$uid', $start, NULL, 0, $start, '$person_name'
			)");
		}
		else
		{
			// start after pause
			$this->db_query("UPDATE mrp_stats SET
					last_start = $start
				WHERE
					case_oid = $case AND resource_oid = $res AND job_oid = $job_id AND uid = '$uid'");
		}
	}

	function stats_done($job)
	{
		$case = $job->prop("project");
		$res = $job->prop("resource");
		$job_id = $job->id();
		$uid = aw_global_get("uid");
		$tm = time();
		$q = "SELECT * FROM mrp_stats WHERE
			case_oid = $case AND resource_oid = $res AND job_oid = $job_id AND uid = '$uid'";
		$row = $this->db_fetch_row($q);
		if (!$row)
		{
			$fp = fopen(aw_ini_get("site_basedir")."/files/mrp_stats.txt", "a");
			fwrite($fp, date("d.m.Y H:i:s").": stats_done($job_id): no row for $case, $res, $job_id, $uid\n");
			fclose($fp);
			return;
		}
		$this->db_query("UPDATE mrp_stats SET
				end = $tm, length = length + ($tm - last_start), last_start = null
			WHERE
				case_oid = $case AND resource_oid = $res AND job_oid = $job_id AND uid = '$uid'");
	}


/**
	@attrib name=start
	@param id required type=int
**/
	function start ($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => $arr["return_url"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("T&ouml;&ouml; id vale");
			$errors = (serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_project_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_INPROGRESS,
		);
		$applicable_job_states = array (
			MRP_STATUS_PLANNED,
		);

		if (!in_array ($project->prop ("state"), $applicable_project_states))
		{
			$errors[] = t("Projekt pole t&ouml;&ouml;s ega planeeritud");
		}

		if (!in_array ($this_object->prop ("state"), $applicable_job_states))
		{
			$errors[] = t("T&ouml;&ouml; pole planeeritud");
		}

		### check if prerequisites are done
		$prerequisites = trim($this_object->prop ("prerequisites")) ? explode(",", $this_object->prop("prerequisites")) : array();
		$prerequisites_done = true;

		foreach ($prerequisites as $prerequisite_oid)
		{
			$prerequisite_oid = (int) $prerequisite_oid;

			if (is_oid ($prerequisite_oid))
			{
				$prerequisite = obj ($prerequisite_oid);

				if (((int) $prerequisite->prop ("state")) != MRP_STATUS_DONE)
				{
					$prerequisites_done = false;
					$errors[] = t("Eeldust&ouml;&ouml;d tegemata");
					break;
				}
			}
			else
			{
				$errors[] = t("Eeldust&ouml;&ouml; definitsioon on katki");
				break;
			}
		}

		### reserve resource
		$mrp_resource = get_instance(CL_MRP_RESOURCE);
		$resource_is_reserved = $mrp_resource->start_job(array(
			"resource" => $this_object->prop("resource"),
			"job" => $this_object->id (),
		));

		if ($resource_is_reserved === false)
		{
			$errors[] = t("Ressurss kinni");
		}

		### if no errors, save
		if ($errors)
		{
			### free resource and exit
			$mrp_resource->stop_job(array(
				"resource" => $this_object->prop("resource"),
				"job" => $this_object->id (),
			));
			$errors = (serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### start project if first job
			if ($project->prop ("state") != MRP_STATUS_INPROGRESS)
			{
				$mrp_case = get_instance(CL_MRP_CASE);
				$project_start = $mrp_case->start(array("id" => $project->id ()));

				$project_errors = parse_url($project_start);
				$project_errors = explode("&", $project_errors["query"]);
				$project_errors = unserialize(urldecode($project_errors["errors"]));

				if ($project_errors)
				{
					$errors[] = t("Projekti alustamine eba&otilde;nnestus");
					$errors = array_merge($errors, $project_errors);

					### free resource and exit
					$mrp_resource->stop_job(array(
						"resource" => $this_object->prop("resource"),
						"job" => $this_object->id (),
					));
					$errors = (serialize($errors));
					return aw_url_change_var ("errors", $errors, $return_url);
				}
			}

			### set project state & progress
			$progress = time () + $this_object->prop ("planned_length");
			$project->set_prop ("progress", $progress);

			### start job
			$this_object->set_prop ("state", MRP_STATUS_INPROGRESS);
			$this_object->set_prop ("started", time ());

			### log
			$this->state_changed($this_object, $arr["pj_change_comment"]);
			$this->stats_start($this_object);

			### all went well, save and say OK
			aw_disable_acl();
			$this_object->save ();
			$project->save ();
			aw_restore_acl();

			return $return_url;
		}
	}

/**
	@attrib name=done
	@param id required type=int
**/
	function done ($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => $arr["return_url"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("T&ouml;&ouml; id vale");
			$errors = (serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_states))
		{
			$errors[] = t("T&ouml;&ouml; staatus sobimatu");
		}

		### ...
		if (empty ($errors))
		{
			### set resource as free
			$mrp_resource = get_instance(CL_MRP_RESOURCE);
			$resource_freed = $mrp_resource->stop_job(array(
				"resource" => $this_object->prop("resource"),
				"job" => $this_object->id (),
			));

			if (!$resource_freed)
			{
				$errors[] = t("Ressursi vabastamine eba&otilde;nnestus");
				error::raise(array(
					"msg" => sprintf (t("Ressursi vabastamine eba&otilde;nnestus. Job: %s, res: %s"), $this_object->id (), $this_object->prop("resource")),
					"fatal" => false,
					"show" => false,
				));
			}
			else
			{
				### finish job
				$time = time ();
				$this_object->set_prop ("state", MRP_STATUS_DONE);
				$this_object->set_prop ("finished", $time);
				aw_disable_acl();
				$this_object->save ();
				aw_restore_acl();

				### post rescheduling msg
				$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

				if ($workspace)
				{
					$workspace->set_prop("rescheduling_needed", 1);
					aw_disable_acl();
					$workspace->save();
					aw_restore_acl();
				}
				else
				{
					$errors[] = t("Ressursihalduskeskkond defineerimata.");
				}

				### log job change
				$this->state_changed($this_object, $arr["pj_change_comment"]);
				$this->stats_done($this_object);

				### finish project if this was the last job
				$list = new object_list (array (
					"class_id" => CL_MRP_JOB,
					"project" => $project->id (),
					"state" => MRP_STATUS_DONE,
				));
				$done_jobs = (int) $list->count ();

				$list = new object_list (array (
					"class_id" => CL_MRP_JOB,
					"project" => $project->id (),
				));
				$all_jobs = (int) $list->count ();

				if ($done_jobs == $all_jobs)
				{
					### finish project
					$mrp_case = get_instance(CL_MRP_CASE);
					$project_finish = $mrp_case->finish(array("id" => $project->id ()));

					$project_errors = parse_url($project_finish);
					$project_errors = explode("&", $project_errors["query"]);
					$project_errors = unserialize(urldecode($project_errors["errors"]));

					if ($project_errors)
					{
						$project_state = $project->prop ("state");

						$errors[] = t(sprintf ("Projekti l&otilde;petamine eba&otilde;nnestus. Projekti staatus oli '%s'", $project_state));
						$errors = array_merge($errors, $project_errors);
					}
				}
				else
				{
					### update progress
					$project->set_prop ("progress", time ());
					aw_disable_acl();
					$project->save ();
					aw_restore_acl();
				}

// /* dbg */ $tmp_resource = obj ($this_object->prop("resource"));
// /* dbg */ if ($tmp_resource->prop ("state") != MRP_STATUS_RESOURCE_AVAILABLE) {
// /* dbg */ send_mail ("ve@starman.ee", "!VIGA @ MRP", __FILE__ . " " . __LINE__ . "\n ressurss kinni j22nd \n job id:" . $this_object->id ());
// /* dbg */ }
			}
		}

		$errors = (serialize($errors));
		return aw_url_change_var ("errors", $errors, $return_url);
	}

/**
	@attrib name=abort
	@param id required type=int
**/
	function abort ($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => $arr["return_url"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("T&ouml;&ouml; id vale");
			$errors = (serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PAUSED,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_states))
		{
			$errors[] = t("T&ouml;&ouml; pole tegemisel");
		}

		### ...
		if (empty ($errors))
		{
			### set resource as free
			$mrp_resource = get_instance(CL_MRP_RESOURCE);
			$resource_freed = $mrp_resource->stop_job(array(
				"resource" => $this_object->prop("resource"),
				"job" => $this_object->id (),
			));

			if (!$resource_freed)
			{
				$errors[] = t("Ressursi vabastamine eba&otilde;nnestus");
				error::raise(array(
					"msg" => sprintf (t("Ressursi vabastamine eba&otilde;nnestus. Job: %s, res: %s"), $this_object->id (), $this_object->prop("resource")),
					"fatal" => false,
					"show" => false,
				));
			}
			else
			{
				### abort job
				$this_object->set_prop ("state", MRP_STATUS_ABORTED);
				aw_disable_acl();
				$this_object->save ();
				aw_restore_acl();

				### post rescheduling msg
				$project = $this_object->get_first_obj_by_reltype("RELTYPE_MRP_PROJECT");
				$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

				if ($workspace)
				{
					$workspace->set_prop("rescheduling_needed", 1);
					aw_disable_acl();
					$workspace->save();
					aw_restore_acl();
				}
				else
				{
					$errors[] = t("Ressursihalduskeskkond defineerimata.");
				}

				### log event
				$this->state_changed($this_object, $arr["pj_change_comment"]);
				$this->stats_done($this_object);

// /* dbg */ $tmp_resource = obj ($this_object->prop("resource"));
// /* dbg */ if ($tmp_resource->prop ("state") != MRP_STATUS_RESOURCE_AVAILABLE) {
// /* dbg */ send_mail ("ve@starman.ee", "!VIGA @ MRP", __FILE__ . " " . __LINE__ . "\n ressurss kinni j22nd \n job id:" . $this_object->id ());
// /* dbg */ }
			}
		}

		$errors = urlencode(serialize($errors));
		return aw_url_change_var ("errors", $errors, $return_url);
	}

/**
	@attrib name=pause
	@param id required type=int
**/
	function pause($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => $arr["return_url"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("T&ouml;&ouml; id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_states))
		{
			$errors[] = t("T&ouml;&ouml; pole tegemisel");
		}

		### ...
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### pause job
			$this_object->set_prop ("state", MRP_STATUS_PAUSED);

			### save paused times for job
			$pt = safe_array($this_object->meta("paused_times"));
			$pt[] = array("start" => time(), "end" => NULL);
			$this_object->set_meta("paused_times" , $pt);

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);

			### save project&job
			aw_disable_acl();
			$this_object->save ();
			$project->save ();
			aw_restore_acl();

			### log event
			$this->state_changed($this_object, $arr["pj_change_comment"]);
			$this->stats_done($this_object);

			return $return_url;
		}
	}

/**
	@attrib name=scontinue
	@param id required type=int
**/
	function scontinue($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => $arr["return_url"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("T&ouml;&ouml; id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_project_states = array (
			MRP_STATUS_INPROGRESS,
		);
		$applicable_job_states = array (
			MRP_STATUS_PAUSED,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_job_states))
		{
			$errors[] = t("T&ouml;&ouml; pole pausil");
		}

		if (!in_array ($project->prop ("state"), $applicable_project_states))
		{
			$errors[] = t("Projekt pole j&auml;tkatav");
		}

		### ...
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### continue job
			$this_object->set_prop ("state", MRP_STATUS_INPROGRESS);

			### save paused times for job
			$pt = safe_array($this_object->meta("paused_times"));
			$pt[count($pt)-1]["end"] = time();
			$this_object->set_meta("paused_times" , $pt);

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);

			aw_disable_acl();
			$this_object->save ();
			$project->save ();
			aw_restore_acl();

			### log event
			$this->state_changed($this_object, $arr["pj_change_comment"]);
			$this->stats_start($this_object);

			return $return_url;
		}
	}

/**
	@attrib name=acontinue
	@param id required type=int
**/
	function acontinue($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => $arr["return_url"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("T&ouml;&ouml; id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_project_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_INPROGRESS,
		);
		$applicable_job_states = array (
			MRP_STATUS_ABORTED,
		);

		if (!in_array ($project->prop ("state"), $applicable_project_states))
		{
			$errors[] = t("Projekt pole j&auml;tkatav");
		}

		if (!in_array ($this_object->prop ("state"), $applicable_job_states))
		{
			$errors[] = t("T&ouml;&ouml; pole katkestatud");
		}

		### check if prerequisites are done
		$prerequisites = trim($this_object->prop ("prerequisites")) ? explode(",", $this_object->prop("prerequisites")) : array();
		$prerequisites_done = true;

		foreach ($prerequisites as $prerequisite_oid)
		{
			$prerequisite_oid = (int) $prerequisite_oid;

			if (is_oid ($prerequisite_oid))
			{
				$prerequisite = obj ($prerequisite_oid);

				if ($prerequisite->class_id() != CL_MRP_JOB)
				{
					continue;
				}

				if (((int) $prerequisite->prop ("state")) != MRP_STATUS_DONE)
				{
					$prerequisites_done = false;
					$errors[] = t("Eeldust&ouml;&ouml;d tegemata");
					break;
				}
			}
			else
			{
				$errors[] = t("Eeldust&ouml;&ouml; definitsioon on katki");
				break;
			}
		}

		### reserve resource
		$mrp_resource = get_instance(CL_MRP_RESOURCE);
		$resource_is_reserved = $mrp_resource->start_job(array(
			"resource" => $this_object->prop("resource"),
			"job" => $this_object->id (),
		));

		if ($resource_is_reserved === false)
		{
			$errors[] = t("Ressurss kinni");
		}

		### if no errors, save
		if ($errors)
		{
			### free resource and exit
			$mrp_resource->stop_job(array(
				"resource" => $this_object->prop("resource"),
				"job" => $this_object->id (),
			));
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### continue job
			$this_object->set_prop ("state", MRP_STATUS_INPROGRESS);

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);
			$project->set_prop ("state", MRP_STATUS_INPROGRESS);

			aw_disable_acl();
			$this_object->save ();
			$project->save ();
			aw_restore_acl();

			### log event
			$this->state_changed($this_object, $arr["pj_change_comment"]);
			$this->stats_start($this_object);

			return $return_url;
		}
	}

/**
	@attrib name=end_shift
	@param id required type=int
**/
	function end_shift($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => $arr["return_url"],
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("T&ouml;&ouml; id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_states))
		{
			$errors[] = t("T&ouml;&ouml; pole tegemisel");
		}

		### ...
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### pause job
			$this_object->set_prop ("state", MRP_STATUS_PAUSED);

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);

			aw_disable_acl();
			$this_object->save ();
			$project->save ();
			aw_restore_acl();

			### log event
			$this->state_changed($this_object, $arr["pj_change_comment"]);
			$this->stats_done($this_object);

			### log out user
			$u = get_instance("users");
			$u->logout();

			return $return_url;
		}
	}

	function job_prerequisites_are_done($arr)
	{
		if (is_oid ($arr["job"]))
		{
			$job = obj ($arr["job"]);
		}
		else
		{
			return false;
		}

		if (trim ($job->prop ("prerequisites")))
		{
			$prerequisites = explode (",", $job->prop ("prerequisites"));
			$applicable_states = array (
				MRP_STATUS_DONE,
			);

			foreach ($prerequisites as $prerequisite_oid)
			{
				if (!$this->can("view", $prerequisite_oid))
				{
					continue;
				}

				$prerequisite = obj ($prerequisite_oid);

				if (!in_array ($prerequisite->prop ("state"), $applicable_states))
				{
					return false;
				}
			}
		}

		return true;
	}

/**
    @attrib name=can_start
	@param job required type=int
**/
	function can_start ($arr)
	{
		if (is_oid ($arr["job"]))
		{
			$job = obj ($arr["job"]);
		}
		else
		{
			return false;
		}

		### check if project is ready to go on
		if ($this->can("view", $job->prop("project")))
		{
			$project = obj($job->prop("project"));
		}

		if (!$project)
		{
			return false;
		}

		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);

		if (!in_array ($project->prop ("state"), $applicable_states))
		{
			return false;
		}
		### check if job can start
		$applicable_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_ABORTED,
		);

		if (!in_array ($job->prop ("state"), $applicable_states))
		{
			return false;
		}

		### check if resource is available
		$resource = obj($job->prop("resource"));
		$applicable_states = array (
			NULL,
			MRP_STATUS_RESOURCE_AVAILABLE,
		);

		/*
		if (!in_array ($resource->prop ("state"), $applicable_states))
		{
			return false;
		}
		*/

		### get max number of threads for resource
		$max_jobs = max(1, count($resource->prop("thread_data")));

		### get number of jobs using resource
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

		### compare
		if ($cur_jobs >= $max_jobs)
		{
			return false;
		}

		### check if all prerequisite jobs are done
		if (!$this->job_prerequisites_are_done(array("job" => $job->id())))
		{
			return false;
		}

		return true;
	}

	function on_delete_job ($arr)
	{
		$job = obj ((int) $arr["oid"]);

		### job states that require freeing resource
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PAUSED,
		);

		if (in_array ($job->prop ("state"), $applicable_states))
		{
			### free resource
			$mrp_resource = get_instance(CL_MRP_RESOURCE);
			$mrp_resource->stop_job(array(
				"resource" => $job->prop("resource"),
				"job" => $job->id (),
			));
		}

		$job->set_prop ("state", MRP_STATUS_DELETED);
		aw_disable_acl();
		$job->save ();
		aw_restore_acl();

		### get project for deleted job
		$project = $job->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");

		if (!$project)
		{
			return;
		}

		### set successive jobs' prerequisites equal to deleted job's prerequisites
		$prerequisites = $job->prop ("prerequisites");
		$prerequisites = empty($prerequisites) ? array() : explode (",", $prerequisites);

		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"project" => $project->id (),
			"state" => new obj_predicate_not (MRP_STATUS_DELETED),
		));
		$other_jobs = $list->arr ();

		foreach ($other_jobs as $other_job)
		{
			$other_job_prerequisites = $other_job->prop ("prerequisites");
			$other_job_prerequisites = empty($other_job_prerequisites) ? array() : explode (",", $other_job_prerequisites);

			if (in_array ($job->id (), $other_job_prerequisites))
			{
				$successor_prerequisites = array_merge ($other_job_prerequisites, $prerequisites);
				$successor_prerequisites = array_unique ($successor_prerequisites);

				### remove deleted job from prerequisites
				$keys = array_keys ($successor_prerequisites, $job->id ());

				foreach ($keys as $key)
				{
					unset ($successor_prerequisites[$key]);
				}

				### ...
				$successor_prerequisites = implode (",", $successor_prerequisites);
				$other_job->set_prop ("prerequisites", $successor_prerequisites);
				aw_disable_acl();
				$other_job->save ();
				aw_restore_acl();
			}
		}

		### correct project's job order
		$this->do_orb_method_call (array (
			"action" => "order_jobs",
			"class" => "mrp_case",
			"params" => array (
				"oid" => $project->id ()
			)
		));

		$applicable_planning_states = array(
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);

		if (in_array ($project->prop ("state"), $applicable_planning_states))
		{
			### post rescheduling msg
			$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				$workspace->set_prop("rescheduling_needed", 1);
				aw_disable_acl();
				$workspace->save();
				aw_restore_acl();
			}
			else
			{
				return t("Ressursihalduskeskkond defineerimata.");
			}
		}
	}

	function add_comment($job, $comment)
	{
		if (trim($comment) != "")
		{
		$job = obj($job);
		$hist = safe_array($job->meta("change_comment_history"));
		array_unshift($hist, array(
			"tm" => time(),
			"uid" => aw_global_get("uid"),
			"text" => trim($comment)
		));
		$job->set_meta("change_comment_history", $hist);

			aw_disable_acl();
			$workspace_i = get_instance(CL_MRP_WORKSPACE);
			$workspace_i->mrp_log($job->prop("project"), $job->id(), t("Lisas kommentaari"), $comment);

			$job->save();
			aw_restore_acl();
		}
	}

	function init_materials_tbl($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Materjal"),
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "amount",
			"caption" => t("Kogus"),
			"align"=> "center",
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "unit",
			"caption" => t("&Uuml;hik"),
			"align"=> "center",
			"chgbgcolor" => "color",
		));
		$t->set_rgroupby(array("category" => "category"));
	}

	function create_materials_sel_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->init_materials_tbl(&$t);
		$t->set_caption(sprintf(t("Tooteretsepti x poolt t&ouml;&ouml;s %s kasutatavad materjalid"), $arr["obj_inst"]->name()));
		$conn = $arr["obj_inst"]->connections_to(array(
			"from.class_id" => CL_MATERIAL_EXPENSE,
		));
		foreach($conn as $c)
		{
			$prod = $c->from()->prop("product");
			$po = obj($prod);
			$unitselect = $this->get_materials_unitselect($po, $c->from()->prop("unit"));
			$t->define_data(array(
				"name" => html::obj_change_url($po),
				"amount" => html::textbox(array(
					"name" => "amount[".$prod."]",
					"size" => 4,
					"value" => $c->from()->prop("amount"),
				)),
				"unit" => $unitselect,
				"category" => ($cat = $po->get_first_obj_by_reltype("RELTYPE_CATEGORY")) ? $cat->name() : "",
				"color" => "#EAEAEA",
			));
		}
	}

	function create_materials_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->init_materials_tbl(&$t);
		$res = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MRP_RESOURCE");
		if($res)
		{
			$t->set_caption(sprintf(t("Ressursil %s kasutatavad materjalid"), $res->name()));
			$conn = $res->connections_to(array(
				"from.class_id" => CL_MATERIAL_EXPENSE_CONDITION,
			));
			$conn2 = $arr["obj_inst"]->connections_to(array(
				"from.class_id" => CL_MATERIAL_EXPENSE,
			));
			foreach($conn2 as $c)
			{
				$prod = $c->from()->prop("product");
				$has_ids[$prod] = $prod;
			}
			foreach($conn as $c)
			{
				$prod = $c->from()->prop("product");
				if($has_ids[$prod])
				{
					continue;
				}
				$po = obj($prod);
				$unitselect = $this->get_materials_unitselect($po);
				$t->define_data(array(
					"name" => html::obj_change_url($po),
					"amount" => html::textbox(array(
						"name" => "amount[".$prod."]",
						"size" => 4,
						"value" => 0,
					)),
					"unit" => $unitselect,
					"category" => ($cat = $po->get_first_obj_by_reltype("RELTYPE_CATEGORY")) ? $cat->name() : "",
					"color" => "#EAEAEA",
				));
			}
		}
	}

	function get_materials_unitselect($po, $value = null)
	{
		$units = $po->instance()->get_units($po);
		foreach($units as $i => $unit)
		{
			if(!$unit)
			{
				unset($units[$i]);
			}
			else
			{
				$unitopts[$unit] = obj($unit)->name();
			}
		}
		if(count($units) == 1)
		{
			$unitselect = obj(reset($units))->name().html::hidden(array(
				"name" => "unit[".$prod."]",
				"value" => reset($units),
			));
		}
		elseif(count($units))
		{
			$unitselect = "";
			foreach($unitopts as $unit => $name)
			{
				$unitselect .= html::radiobutton(array(
					"name" => "unit[".$po->id()."]",
					"value" => $unit,
					"checked" => $value ? ($value == $unit) : ($unit == $units[0]),
				)).$name." ";
			}
		}
		else
		{
			$unitselect = "-";
		}
		return $unitselect;
	}

	function save_materials($arr)
	{
		$res = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MRP_RESOURCE");
		if($res)
		{
			$conn = $res->connections_to(array(
				"from.class_id" => CL_MATERIAL_EXPENSE_CONDITION,
			));
			$conn2 = $arr["obj_inst"]->connections_to(array(
				"from.class_id" => CL_MATERIAL_EXPENSE,
			));
			foreach($conn2 as $c)
			{
				$o = $c->from();
				$prod = $o->prop("product");
				$prods[$prod] = $o->id();
			}
			foreach($conn as $c)
			{
				$prod = $c->from()->prop("product");
				if(!$prods[$prod] && $arr["request"]["amount"][$prod])
				{
					$o = obj();
					$o->set_class_id(CL_MATERIAL_EXPENSE);
					$o->set_parent($arr["obj_inst"]->id());
					$o->set_name(sprintf(t("%s kulu %s jaoks"), obj($prod)->name(), $arr["obj_inst"]->name()));
					$o->set_prop("product", $prod);
					if($arr["request"]["unit"][$prod])
					{
						$o->set_prop("unit", $arr["request"]["unit"][$prod]);
					}
					$o->set_prop("amount", $arr["request"]["amount"][$prod]);
					$o->set_prop("job", $arr["obj_inst"]->id());
					$o->save();
				}
				else
				{
					if($prods[$prod] && !$arr["request"]["amount"][$prod])
					{
						$eo = obj($prods[$prod]);
						$eo->delete();
					}
					elseif($prods[$prod])
					{
						$eo = obj($prods[$prod]);
						$eo->set_prop("unit", $arr["request"]["unit"][$prod]);
						$eo->set_prop("amount", $arr["request"]["amount"][$prod]);
						$eo->save();
					}
				}
			}
			$arr["obj_inst"]->save();
			$conn = $arr["obj_inst"]->connections_to(array(
				"from.class_id" => CL_MATERIAL_MOVEMENT_RELATION,
			));
			foreach($arr["request"]["unit"] as $prod => $unit)
			{
				if(!$arr["request"]["amount"][$prod])
				{
					continue;
				}
				$data[$prod] = array(
					"unit" => $unit,
					"amount" => $arr["request"]["amount"][$prod],
				);
			}
			if(!count($conn))
			{
				$o = obj();
				$o->set_class_id(CL_MATERIAL_MOVEMENT_RELATION);
				$o->set_parent($arr["obj_inst"]->id());
				$o->set_name(sprintf(t("Materjali liikumisseos t&ouml;&ouml;ga %s"), $arr["obj_inst"]->name()));
				$o->set_prop("job", $arr["obj_inst"]->id());
				$o->save();
				$o->create_dn($o, $data);
			}
			else
			{
				foreach($conn as $c)
				{
					$c->from()->update_dn_rows($c->from(), $data);
				}
			}
		}
	}
}

?>
