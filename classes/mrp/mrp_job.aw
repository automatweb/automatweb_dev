<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_job.aw,v 1.54 2005/04/08 08:10:08 voldemar Exp $
// mrp_job.aw - Tegevus
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_MRP_JOB, on_delete_job)

@classinfo syslog_type=ST_MRP_JOB relationmgr=yes no_status=1 confirm_save_data=1

@tableinfo mrp_job index=oid master_table=objects master_index=oid

@groupinfo data caption="Andmed"
@groupinfo workflow caption="Töövoog"



@default group=general
	@property name type=text
	@caption Nimi

	@property comment type=textarea table=objects field=comment
	@caption Kommentaar

@default group=workflow
	@property job_toolbar type=toolbar no_caption=1 store=no

	@property workflow_errors type=text store=no no_caption=1

@default table=mrp_job
	@property started type=text
	@caption Alustatud

	@property finished type=text
	@caption Lõpetatud

	@property resource type=text
	@caption Ressurss

	@property project type=hidden
	@caption Projekt

	@property exec_order type=hidden
	@caption Töö jrk. nr.

	@property starttime type=text
	@caption Plaanitud töösseminekuaeg

	@property planned_length type=text
	@caption Planeeritud kestus (h)

	@property state type=text
	@caption Staatus


@default group=data
	@property length type=textbox
	@caption Töö pikkus (h)

	@property pre_buffer type=textbox
	@caption Eelpuhveraeg (h)

	@property post_buffer type=textbox
	@caption Järelpuhveraeg (h)

	@property minstart type=datetime_select
	@comment Enne seda kuupäeva, kellaaega ei lubata tööd alustada
	@caption Varaseim alustusaeg

	@property prerequisites type=textbox
	@comment Komaga eraldatud
	@caption Eeldustööd


@default table=objects
@default field=meta
@default method=serialize
	@property advised_starttime type=datetime_select
	@comment Allhankijaga kokkulepitud aeg, millal töö alustada.
	@caption Soovitav algusaeg



// --------------- RELATION TYPES ---------------------

@reltype MRP_RESOURCE value=1 clid=CL_MRP_RESOURCE
@caption Tööks kasutatav ressurss

@reltype MRP_PROJECT value=2 clid=CL_MRP_CASE
@caption Projekt

//@reltype MRP_PRIORITY value=3 clid=CL_PRIORITY
//@caption Töö prioriteet


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

	PRIMARY KEY  (`oid`),
	UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

### resource types
define ("MRP_RESOURCE_SCHEDULABLE", 1);
define ("MRP_RESOURCE_NOT_SCHEDULABLE", 2);
define ("MRP_RESOURCE_SUBCONTRACTOR", 3);

### states
define ("MRP_STATUS_NEW", 1);
define ("MRP_STATUS_PLANNED", 2);
define ("MRP_STATUS_INPROGRESS", 3);
define ("MRP_STATUS_ABORTED", 4);
define ("MRP_STATUS_DONE", 5);
define ("MRP_STATUS_LOCKED", 6);
define ("MRP_STATUS_PAUSED", 7);
define ("MRP_STATUS_DELETED", 8);
define ("MRP_STATUS_ONHOLD", 9);
define ("MRP_STATUS_ARCHIVED", 10);

define ("MRP_STATUS_RESOURCE_AVAILABLE", 10);
define ("MRP_STATUS_RESOURCE_INUSE", 11);
define ("MRP_STATUS_RESOURCE_OUTOFSERVICE", 12);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");

### colours (CSS colour definition)
define ("MRP_COLOUR_NEW", "#05F123");
define ("MRP_COLOUR_PLANNED", "#5B9F44");
define ("MRP_COLOUR_INPROGRESS", "#FF9900");
define ("MRP_COLOUR_ABORTED", "#FF13F3");
define ("MRP_COLOUR_DONE", "#996600");
define ("MRP_COLOUR_PAUSED", "#AFAFAF");
define ("MRP_COLOUR_ONHOLD", "#9900CC");
define ("MRP_COLOUR_ARCHIVED", "#0066CC");
define ("MRP_COLOUR_HILIGHTED", "#FFE706");
define ("MRP_COLOUR_PLANNED_OVERDUE", "#FBCEC1");
define ("MRP_COLOUR_OVERDUE", "#DF0D12");


class mrp_job extends class_base
{
	var $mrp_error = false;

	function mrp_job ()
	{
		$this->states = array(
			MRP_STATUS_NEW => t("Uus"),
			MRP_STATUS_PLANNED => t("Planeeritud"),
			MRP_STATUS_INPROGRESS => t("Töös"),
			MRP_STATUS_ABORTED => t("Katkestatud"),
			MRP_STATUS_DONE => t("Valmis"),
			MRP_STATUS_LOCKED => t("Lukustatud"),
			MRP_STATUS_PAUSED => t("Paus"),
			MRP_STATUS_DELETED => t("Kustutatud"),
			MRP_STATUS_ONHOLD => t("Plaanist väljas"),
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
			$this->mrp_error .= t("Uut tööd saab luua vaid ressursihalduskeskkonnas. ");
		}
		else
		{
			$this_object = obj($arr["request"]["id"]);
			$project_id = $this_object->prop ("project");
			$resource_id = $this_object->prop ("resource");

			if ( is_oid($project_id) and is_oid($resource_id) )
			{
				$this->project = obj ($project_id);
				$this->resource = obj ($resource_id);
				$this->workspace = $this->project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

				if (!$this->workspace or !$this->project or !$this->resource)
				{
					$this->mrp_error .= t("Tööl puudub ressurss, projekt või ressursihalduskeskkond. ");
				}
			}
			else
			{
				$this->mrp_error .= t("Tööl puudub ressurss või projekt. ");
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

			case "resource":
				if (is_object($this->resource))
				{
					$prop["value"] = html::get_change_url(
						$this->resource->id(),
						array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
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
						$prop["value"] = ' <div style="color: #DF0D12; margin: 5px;">' . t('Esinenud tõrked: ') . implode (". ", $errors) . '.</div>';
						unset ($arr["request"]["errors"]);
					}
				}
				break;

			case "length":
			case "planned_length":
			case "pre_buffer":
			case "post_buffer":
				$prop["value"] = round (($prop["value"] / 3600), 2);
				break;

			case "advised_starttime":
				if ($this->resource->prop("type") != MRP_RESOURCE_SUBCONTRACTOR)
				{
					return PROP_IGNORE;
				}
				break;

			case "state":
				$prop["value"] = $this->states[$prop["value"]] ? $this->states[$prop["value"]] : t("Määramata");
				break;

			case "starttime":
				$prop["value"] = $prop["value"] ? date(MRP_DATE_FORMAT, $prop["value"]) : t("Planeerimata");
				break;

			case "started":
				$prop["value"] = $prop["value"] ? date(MRP_DATE_FORMAT, $prop["value"]) : t("Tööd pole veel alustatud");
				break;

			case "finished":
				$prop["value"] = ($this_object->prop ("state") == MRP_STATUS_DONE) ? date(MRP_DATE_FORMAT, $prop["value"]) : t("Tööd pole veel lõpetatud");
				break;

			case "job_toolbar":
				$this->create_job_toolbar ($arr);
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
			//"img" => "new.gif",
			"tooltip" => t("Alusta"),
			"action" => "start",
			// "confirm" => t("Oled kindel et soovid t&ouml;&ouml;d alustada?"),
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
			//"img" => "done.gif",
			"tooltip" => t("Valmis"),
			"action" => "done",
			"confirm" => t("Oled kindel et soovid t&ouml;&ouml;d l&otilde;petada?"),
			"disabled" => $disabled_inprogress,
		));
		$toolbar->add_button(array(
			"name" => "pause",
			//"img" => "pause.gif",
			"tooltip" => t("Paus"),
			"action" => "pause",
			"disabled" => $disabled_inprogress,
		));
		$toolbar->add_button(array(
			"name" => "end_shift",
			//"img" => "end_shift.gif",
			"confirm" => t("Lõpeta vahetus ja logi v&auml;lja?"),
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
			//"img" => "continue.gif",
			"tooltip" => t("J&auml;tka"),
			"action" => $action,
			"disabled" => $disabled,
		));

		$toolbar->add_button(array(
			"name" => "abort",
			//"img" => "abort.gif",
			"tooltip" => t("Katkesta"),
			//"action" => "abort",
			"url" => "#",
			"confirm" => t("Katkesta t&ouml;&ouml;?"),
			"onClick" => "if (document.changeform.pj_change_comment.value.replace(/\\s+/, '') != '') { submit_changeform('abort') } else { alert('" . t("Kommentaar peab olema t&auml;idetud!") . "'); }",
			"disabled" => $disabled_inprogress,
		));
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
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("Töö id vale");
			$errors = urlencode(serialize($errors));
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
			$errors[] = t("Projekt pole töös ega planeeritud");
		}

		if (!in_array ($this_object->prop ("state"), $applicable_job_states))
		{
			$errors[] = t("Töö pole planeeritud");
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
					$errors[] = t("Eeldustööd tegemata");
					break;
				}
			}
			else
			{
				$errors[] = t("Eeldustöö definitsioon on katki");
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
					$errors[] = t("Projekti alustamine ebaõnnestus");
					$errors = array_merge($errors, $project_errors);

					### free resource and exit
					$mrp_resource->stop_job(array(
						"resource" => $this_object->prop("resource"),
						"job" => $this_object->id (),
					));
					$errors = urlencode(serialize($errors));
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
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

			### all went well, save and say OK
			$this_object->save ();
			$project->save ();

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
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("Töö id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_states))
		{
			$errors[] = t("Töö staatus sobimatu");
		}

		### ...
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### finish job
			$this_object->set_prop ("state", MRP_STATUS_DONE);
			$this_object->set_prop ("finished", time ());
			$this_object->save ();

			### set resource as free
			$mrp_resource = get_instance(CL_MRP_RESOURCE);
			$resource_freed = $mrp_resource->stop_job(array(
				"resource" => $this_object->prop("resource"),
				"job" => $this_object->id (),
			));

			if (!$resource_freed)
			{
				$errors[] = t("Ressursi vabastamine ebaõnnestus");
			}

			### post rescheduling msg
			$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				$workspace->set_prop("rescheduling_needed", 1);
				$workspace->save();
			}
			else
			{
				$errors[] = t("Ressursihalduskeskkond defineerimata.");
			}

			### log job change
			$ws = get_instance (CL_MRP_WORKSPACE);
			$ws->mrp_log(
				$this_object->prop ("project"),
				$this_object->id (),
				"T&ouml;&ouml; ".$this_object->name() . " staatus muudeti ".$this->states[$this_object->prop("state")],
				$arr["pj_change_comment"]
			);

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

					$errors[] = t(sprintf ("Projekti lõpetamine ebaõnnestus. Projekti staatus oli '%s'", $project_state));
					$errors = array_merge($errors, $project_errors);
				}
			}
			else
			{
				### update progress
				$project->set_prop ("progress", time ());
				$project->save ();
			}

			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
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
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("Töö id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PAUSED,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_states))
		{
			$errors[] = t("Töö pole tegemisel");
		}

		### ...
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### abort job
			$this_object->set_prop ("state", MRP_STATUS_ABORTED);
			$this_object->save ();

			### set resource as free
			$mrp_resource = get_instance(CL_MRP_RESOURCE);
			$resource_freed = $mrp_resource->stop_job(array(
				"resource" => $this_object->prop("resource"),
				"job" => $this_object->id (),
			));

			if (!$resource_freed)
			{
				$errors[] = t("Ressursi vabastamine ebaõnnestus");
			}

			### post rescheduling msg
			$project = $this_object->get_first_obj_by_reltype("RELTYPE_MRP_PROJECT");
			$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				$workspace->set_prop("rescheduling_needed", 1);
				$workspace->save();
			}
			else
			{
				$errors[] = t("Ressursihalduskeskkond defineerimata.");
			}

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
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
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("Töö id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_states))
		{
			$errors[] = t("Töö pole tegemisel");
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

			$this_object->save ();

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);
			$project->save ();

			### log event
			$ws = get_instance (CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

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
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("Töö id vale");
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
			$errors[] = t("Töö pole pausil");
		}

		if (!in_array ($project->prop ("state"), $applicable_project_states))
		{
			$errors[] = t("Projekt pole jätkatav");
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

			$this_object->save ();

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);
			$project->save ();

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

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
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("Töö id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_project_states = array (
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);
		$applicable_job_states = array (
			MRP_STATUS_ABORTED,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_job_states))
		{
			$errors[] = t("Töö pole katkestatud");
		}

		if (!in_array ($project->prop ("state"), $applicable_project_states))
		{
			$errors[] = t("Projekt pole jätkatav");
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
			$this_object->save ();

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);
			$project->save ();

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

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
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_job");

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			$errors[] = t("Töö id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		$project = $this_object->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (!in_array ($this_object->prop ("state"), $applicable_states))
		{
			$errors[] = t("Töö pole tegemisel");
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
			$this_object->save ();

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);
			$project->save ();

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti Vahetuse l&otilde;pp", $arr["pj_change_comment"]);

			### log out user
			$u = get_instance("users");
			$u->orb_logout();

			return $return_url;
		}
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
		$project = $job->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
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

		if (!in_array ($resource->prop ("state"), $applicable_states))
		{
			return false;
		}

		### check if all prerequisite jobs are done
		if (trim ($job->prop ("prerequisites")))
		{
			$prerequisites = explode (",", $job->prop ("prerequisites"));
			$applicable_states = array (
				MRP_STATUS_DONE,
			);

			foreach ($prerequisites as $prerequisite_oid)
			{
				$prerequisite = obj ($prerequisite_oid);

				if (!in_array ($prerequisite->prop ("state"), $applicable_states))
				{
					return false;
				}
			}
		}

		return true;
	}

	function on_delete_job ($arr)
	{
		$job_id = (int) $arr["oid"];
		$job = obj ($job_id);
		$job->set_prop ("state", MRP_STATUS_DELETED);
		$job->save ();

		### get project for deleted job
		$project = $job->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");

		if (!$project)
		{
			return;
		}

		### set successive jobs' prerequisites equal to deleted job's prerequisites
		$prerequisites = explode (",", $job->prop ("prerequisites"));
		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"project" => $project->id (),
			"state" => new obj_predicate_not (MRP_STATUS_DELETED),
		));

		for ($successive_job =& $list->begin (); !$list->end (); $successive_job =& $list->next ())
		{
			$successor_prerequisites = explode (",", $successive_job->prop ("prerequisites"));

			if (in_array ($job_id, $successor_prerequisites))
			{
				$successor_prerequisites = array_merge ($successor_prerequisites, $prerequisites);
				$successor_prerequisites = array_unique ($successor_prerequisites);
				$keys = array_keys ($successor_prerequisites, $job_id);
				unset ($successor_prerequisites[$keys[0]]);
				$successor_prerequisites = implode (",", $successor_prerequisites);
				$successive_job->set_prop ("prerequisites", $successor_prerequisites);
			}
		}

		### correct project's job order if project wasn't deleted
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
				$workspace->save();
			}
			else
			{
				return t("Ressursihalduskeskkond defineerimata.");
			}
		}
	}
}

?>
