<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_workspace.aw,v 1.45 2005/03/16 10:58:01 kristo Exp $
// mrp_workspace.aw - Ressursihalduskeskkond
/*

EMIT_MESSAGE(MSG_MRP_RESCHEDULING_NEEDED)

@classinfo syslog_type=ST_MRP_WORKSPACE relationmgr=yes no_status=1

@groupinfo grp_customers caption="Kliendid" submit=no
@groupinfo grp_projects caption="Projektid"
@groupinfo grp_search caption="Otsing"
	@groupinfo grp_search_proj caption="Otsi projekte" submit_method=get parent=grp_search
	@groupinfo grp_search_cust caption="Otsi kliente" submit_method=get parent=grp_search

@groupinfo grp_schedule caption="Kalender" submit=no
@groupinfo grp_printer caption="Operaatori vaade" submit=no
	@groupinfo grp_printer_current caption="Jooksvad t&ouml;&ouml;d" parent=grp_printer submit=no
	@groupinfo grp_printer_old caption="Tegemata asjad" parent=grp_printer submit=no
	@groupinfo grp_printer_done caption="Tehtud asjad" parent=grp_printer submit=no

@groupinfo grp_settings caption="Seaded"
	@groupinfo grp_settings_def caption="Seaded" parent=grp_settings
	@groupinfo grp_settings_salesman caption="M&uuml;&uuml;gimehe seaded" parent=grp_settings
	@groupinfo grp_users_tree caption="Kasutajate puu" parent=grp_settings submit=no
	@groupinfo grp_users_mgr caption="Kasutajate rollid" parent=grp_settings submit=no
	@groupinfo grp_resources caption="Ressursside haldus" parent=grp_settings



@default table=objects
@default field=meta
@default method=serialize

@default group=general
	@property configuration type=relpicker reltype=RELTYPE_MRP_CONFIGURATION
	@caption Ressursihalduse seaded

	@property configuration type=relpicker reltype=RELTYPE_MRP_WORKSPACE_CFGMGR
	@caption Keskkonna seaded


@default group=grp_customers
	@property box type=text no_caption=1 store=no group=grp_customers,grp_projects,grp_resources,grp_users_tree,grp_users_mgr
	@property vsplitbox type=text no_caption=1 store=no wrapchildren=1 group=grp_customers,grp_projects,grp_resources,grp_users_tree,grp_users_mgr
	@property customers_toolbar type=toolbar store=no no_caption=1 parent=box
	@property customers_tree type=treeview store=no no_caption=1 parent=vsplitbox
	@property customers_list type=table store=no no_caption=1 parent=vsplitbox
	@property customers_list_proj type=table store=no no_caption=1 parent=vsplitbox


@default group=grp_projects
	@property projects_toolbar type=toolbar store=no no_caption=1 parent=box
	@property projects_tree type=text store=no no_caption=1 parent=vsplitbox
	@property projects_list type=table store=no no_caption=1 parent=vsplitbox

	@property legend type=text store=no no_caption=1

@default group=grp_search_proj
	@property sp_name type=textbox
	@caption Number

	@property sp_comment type=textbox
	@caption Kirjeldus

	@property sp_starttime type=datetime_select
	@caption Alustamisaeg (meterjalide saabumine)

	@property sp_due_date type=datetime_select
	@caption T&auml;htaeg

	@property sp_customer type=textbox
	@caption Klient

	@property sp_status type=chooser multiple=1 
	@caption Staatus

	@property sp_submit type=submit value=Otsi
	@caption Otsi

	@property sp_result type=table no_caption=1

@default group=grp_search_cust
	@property cs_name type=textbox
	@caption Nimi

	@property cs_firmajuht type=textbox
	@caption Kontaktisik

	@property cs_contact type=textbox
	@caption Aadress

	@property cs_phone type=textbox
	@caption Telefon

	@property cs_reg_nr type=textbox
	@caption Kood

	@property cs_submit type=submit value=Otsi
	@caption Otsi

	@property cs_result type=table no_caption=1

@default group=grp_resources
	@property resources_toolbar type=toolbar store=no no_caption=1 parent=box
	@property resources_tree type=text store=no no_caption=1 parent=vsplitbox
	@property resources_list type=table store=no no_caption=1 parent=vsplitbox


@default group=grp_schedule
	@property replan type=text
	@caption Planeeri

	@property chart_navigation type=text store=no no_caption=1
	@property master_schedule_chart type=text store=no no_caption=1

	@property chart_project_hilight type=select store=no
	@caption Valitud projekt

	@property chart_project_hilight_gotostart type=checkbox store=no
	@caption Mine valitud projekti algusesse

	@property chart_start_date type=date_select store=no
	@caption N&auml;idatava perioodi algus

	@property chart_submit type=submit store=no
	@caption N&auml;ita

@default group=grp_users_tree
	@property user_list_toolbar type=toolbar store=no no_caption=1 parent=box
	@property user_list_tree type=treeview store=no no_caption=1 parent=vsplitbox
	@property user_list type=table store=no no_caption=1 parent=vsplitbox

@default group=grp_users_mgr
	@property user_mgr_toolbar type=toolbar store=no no_caption=1 parent=box
	@property user_mgr_tree type=treeview store=no no_caption=1 parent=vsplitbox
	@property user_mgr type=table store=no no_caption=1 parent=vsplitbox


@default group=grp_settings_def
	@property owner type=relpicker reltype=RELTYPE_MRP_OWNER clid=CL_CRM_COMPANY
	@caption Organisatsioon

	@property resources_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU
	@caption Ressursside kaust

	@property customers_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU
	@caption Klientide kaust

	@property projects_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU
	@caption Projektide kaust

	@property jobs_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU
	@caption Tööde kaust

	@property workspace_configmanager type=relpicker reltype=RELTYPE_MRP_WORKSPACE_CFGMGR clid=CL_CFGMANAGER
	@caption Keskkonna seadetehaldur

	@property case_header_controller type=relpicker reltype=RELTYPE_MRP_HEADER_CONTROLLER
	@caption Projekti headeri kontroller

	@property title_sceduler_parameters type=text store=no subtitle=1
	@caption Planeerija parameetrid

	@property parameter_due_date_overdue_slope type=textbox default=0.5
	@caption Üle tähtaja olevate projektide tähtsuse tõus tähtaja ületamise suurenemise suunas

	@property parameter_due_date_overdue_intercept type=textbox default=10
	@caption Just tähtaja ületanud projekti tähtsus

	@property parameter_due_date_decay type=textbox default=0.05
	@caption Projekti tähtsuse langus tähtaja kaugenemise suunas

	@property parameter_due_date_intercept type=textbox default=0.1
	@caption Planeerimise hetkega võrdse tähtajaga projekti tähtsus

	@property parameter_priority_slope type=textbox default=0.8
	@caption Kliendi ja projektiprioriteedi suhtelise väärtuse tõus vrd. tähtajaga

	@property separator type=text store=no no_caption=1

	@property parameter_schedule_length type=textbox default=2
	@caption Ajaplaani ulatus (a)

	@property parameter_min_planning_jobstart type=textbox default=300
	@caption Ajavahemik planeerimise alguse hetkest milles algavaid t&ouml;id ei planeerita (s)

	@property parameter_schedule_start type=textbox default=300
	@caption Ajaplaani alguse vahe planeerimise alguse hetkega (s)

	@property parameter_timescale type=textarea
	@caption Otsingutabeli ajaskaala definitsioon (Jaotuste algused, komaga eraldatud. Esimene peaks alati 0 olema.)
	@property parameter_timescale_unit type=select
	@caption Skaala ajaühik

@default group=grp_printer_current,grp_printer_old,grp_printer_done
	@property printer_jobs type=table no_caption=1

	@property printer_legend type=text 
	@caption Legend

	// these are shown when a job is selected
	@property pj_case_header type=text no_caption=1 store=no

	@property pj_toolbar type=toolbar store=no no_caption=1
	@caption Muuda staatust


	@property pj_change_comment type=textarea rows=5 cols=50 store=no
	@caption Kommentaar

	@property pj_title_job_data type=text store=no subtitle=1
	@caption T&ouml;&ouml; andmed

	@property pj_starttime type=text store=no
	@caption Algus

	@property pj_length type=text store=no
	@caption Plaanitud kestus (h)

	property pj_pre_buffer type=text store=no
	caption Eelpuhveraeg (h)

	property pj_post_buffer type=text store=no
	caption Järelpuhveraeg (h)

	@property pj_resource type=text store=no
	@caption Ressurss

	@property pj_project type=text store=no
	@caption Projekt

	@property pj_state type=text store=no
	@caption Staatus

	@property pjp_title_proj_data type=text store=no subtitle=1
	@caption Projekti andmed

	@property pjp_format type=text store=no
	@caption Formaat

	@property pjp_sisu_lk_arv type=text store=no
	@caption Sisu lk arv

	@property pjp_kaane_lk_arv type=text store=no
	@caption Kaane lk arv

	@property pjp_sisu_varvid type=text store=no
	@caption Sisu v&auml;rvid

	@property pjp_sisu_varvid_notes type=text store=no
	@caption Sisu v&auml;rvid Notes

	@property pjp_sisu_lakk_muu type=text store=no
	@caption Sisu lakk/muu

	@property pjp_kaane_varvid type=text store=no
	@caption Kaane v&auml;rvid

	@property pjp_kaane_varvid_notes type=text store=no
	@caption Kaane v&auml;rvid Notes

	@property pjp_kaane_lakk_muu type=text store=no
	@caption Kaane lakk/muu

	@property pjp_sisu_paber type=text store=no
	@caption Sisu paber

	@property pjp_kaane_paber type=text store=no
	@caption Kaane paber

	@property pjp_trykiarv type=text store=no
	@caption Tr&uuml;kiarv

	@property pjp_trykise_ehitus type=text store=no
	@caption Tr&uuml;kise ehitus

	@property pjp_kromaliin type=text store=no
	@caption Kromalin

	@property pjp_makett type=text store=no
	@caption Makett

	@property pjp_naidis type=text store=no
	@caption N&auml;idis

	@property pjp_plaate type=text store=no
	@caption Plaate

// --------------- RELATION TYPES ---------------------

@reltype MRP_FOLDER value=1 clid=CL_MENU
@caption Kaust

@reltype MRP_CONFIGURATION clid=CL_MRP_CONFIGURATION value=3
@caption Ressursihalduse seaded

@reltype MRP_WORKSPACE_CFGMGR clid=CL_CFGMANAGER value=2
@caption Keskkonna seaded

@reltype MRP_OWNER clid=CL_CRM_COMPANY value=4
@caption Keskkonna omanik (Organisatsioon)

@reltype MRP_HEADER_CONTROLLER clid=CL_FORM_CONTROLLER value=5
@caption Projekti headeri kontroller
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
define ("MRP_STATUS_OVERDUE", 7);
define ("MRP_STATUS_DELETED", 8);
define ("MRP_STATUS_ONHOLD", 9);

define ("MRP_STATUS_RESOURCE_AVAILABLE", 10);
define ("MRP_STATUS_RESOURCE_INUSE", 11);
define ("MRP_STATUS_RESOURCE_OUTOFSERVICE", 12);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");
define ("MRP_COLOUR_PLANNED_OVERDUE", "#FBCEC1");
define ("MRP_COLOUR_OVERDUE", "#FF1111");

class mrp_workspace extends class_base
{
	var $pj_colors = array(
		"done" => "#BADBAD",
		"can_start" => "#eff6d5",
		"can_not_start" => "#ffe1e1"
	);

	function mrp_workspace()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_workspace",
			"clid" => CL_MRP_WORKSPACE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		if (substr($prop["name"], 0, 3) == "pjp")
		{
			if (!$arr["request"]["pj_job"])
			{
				return PROP_IGNORE;
			}
			// get prop from project
			if ($prop["subtitle"] != 1)
			{
				$job = obj($arr["request"]["pj_job"]);
				$proj = obj($job->prop("project"));
				$prop["value"] = $proj->prop(substr($prop["name"], 4));
				if ($prop["value"] == "")
				{
					return PROP_IGNORE;
				}
			}
		}

		if (substr($prop["name"], 0, 3) == "pj_")
		{
			if (!$arr["request"]["pj_job"])
			{
				return PROP_IGNORE;
			}
			// get prop from job
			if ($prop["subtitle"] != 1)
			{
				$job = obj($arr["request"]["pj_job"]);
				$rpn = substr($prop["name"], 3);
				switch($rpn)
				{
					case "toolbar":
						$this->_do_pj_toolbar($arr, $job);
						return PROP_OK;

					case "starttime":
						$prop["value"] = date("d.m.Y H:i", $job->prop("starttime"));
						break;

					case "length":
						$len  = sprintf("%02d", floor($job->prop("length") / 3600)).":";
						$len .= sprintf("%02d", floor(($job->prop("length") % 3600) / 60));
						$prop["value"] = $len;
						break;

					case "pre_buffer":
						$len  = sprintf("%02d", floor($job->prop("pre_buffer") / 3600)).":";
						$len .= sprintf("%02d", floor(($job->prop("post_buffer") % 3600) / 60));
						$prop["value"] = $len;
						break;

					case "post_buffer":
						$len  = sprintf("%02d", floor($job->prop("post_buffer") / 3600)).":";
						$len .= sprintf("%02d", floor(($job->prop("post_buffer") % 3600) / 60));
						$prop["value"] = $len;
						break;

					case "project":
					case "resource":
						$tmp = obj($job->prop($rpn));
						$prop["value"] = html::get_change_url(
							$tmp->id(),
							array(
								"return_url" => urlencode(aw_global_get("REQUEST_URI"))
							),
							$tmp->name()
						);
						break;

					case "state":
						$j = get_instance(CL_MRP_JOB);
						$prop["value"] = $j->states[$job->prop($rpn)];
						break;

					case "case_header":
						$c_o = obj($job->prop("project"));
						$c_i = $c_o->instance();
						$prop["value"] = $c_i->get_header(array("obj_inst" => $c_o));
						break;

					default:
						$prop["value"] = $job->prop($rpn);
						if ($prop["value"] == "" && $prop["name"] != "pj_change_comment")
						{
							return PROP_IGNORE;
						}
						break;
				}

				if ($prop["value"] == "")
				{
					$prop["value"] = "&nbsp;";
				}
			}
		}

		switch($prop["name"])
		{
			### projects tab
			case "projects_toolbar":
				$this->create_projects_toolbar ($arr);
				break;
			case "projects_tree":
				$this->create_projects_tree ($arr);
				break;
			case "projects_list":
				if ($arr["request"]["mrp_projects_show"] == "subcontracts")
				{
					$this->create_jobs_list ($arr);
				}
				else
				{
					$this->create_projects_list ($arr);
				}
				break;
			case "legend":
				$prop["value"] = '<div style="display: block; margin: 4px;"><span style="width: 25px; height: 15px; margin-right: 5px; background-color: ' . MRP_COLOUR_PLANNED_OVERDUE . '; border: 1px solid black;">&nbsp;</span> &Uuml;le t&auml;htaja planeeritud</div>';
				$prop["value"] .= '<div style="display: block; margin: 4px;"><span style="width: 25px; height: 15px; margin-right: 5px; background-color: ' . MRP_COLOUR_OVERDUE . '; border: 1px solid black;">&nbsp;</span> &Uuml;le t&auml;htaja l&auml;inud</div>';
				break;

			### users tab
			case "users_toolbar":
				$this->create_users_toolbar ($arr);
				break;
			case "users_tree":
				$this->create_users_tree ($arr);
				break;
			case "users_list":
				$this->create_users_list ($arr);
				break;

			### resources tab
			case "resources_toolbar":
				$this->create_resources_toolbar ($arr);
				break;
			case "resources_tree":
				$this->create_resources_tree ($arr);
				break;
			case "resources_list":
				$this->create_resources_list ($arr);
				break;

			### customers tab
			case "customers_toolbar":
				$this->create_customers_toolbar ($arr);
				break;
			case "customers_tree":
				$this->create_customers_tree ($arr);
				break;
			case "customers_list":
				return $this->create_customers_list ($arr);
				break;
			case "customers_list_proj":
				return $this->create_customers_list_proj ($arr);
				break;

			### schedule tab
			case "master_schedule_chart":
				$prop["value"] = $this->create_schedule_chart ($arr);
				break;

			case "chart_navigation":
				$prop["value"] = $this->create_chart_navigation ($arr);
				break;

			case "chart_start_date":
				$prop["value"] = empty ($arr["request"]["mrp_chart_start"]) ? $this->get_week_start () : $arr["request"]["mrp_chart_start"];
				break;

			case "chart_project_hilight":
				if (is_oid ($arr["request"]["mrp_hilight"]))
				{
					$options = array ();
					$prop["value"] = $arr["request"]["mrp_hilight"];
				}
				else
				{
					$options = array ("0" => " ");
				}

				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => new obj_predicate_not (MRP_STATUS_DELETED),//!!! peab olema not deleted and not new
					"parent" => $this_object->prop ("projects_folder"),
					// "createdby" => aw_global_get('uid'),
				));

				for ($project =& $list->begin (); !$list->end (); $project =& $list->next ())
				{
					$options[$project->id ()] = $project->name ();
				}

				$prop["options"] = $options;
				break;

			case "parameter_timescale_unit":
				$prop["options"] = array (
					"86400" => "Päev",
					"60" => "Minut",
					"1" => "Sekund",
				);
				break;

			case "replan":
				$plan_url = $this->mk_my_orb("create", array(
					"return_url" => urlencode(aw_global_get('REQUEST_URI')),
					"mrp_workspace" => $this_object->id (),
				), "mrp_schedule");
				$plan_href = html::href(array(
					"caption" => "[Planeeri]",
					"url" => $plan_url,
					)
				);
				$prop["value"] = $plan_href;
				break;

			case "user_list_toolbar":
				$this->_user_list_toolbar($arr);
				break;

			case "user_list_tree":
				$this->_user_list_tree($arr);
				break;

			case "user_list":
				$this->_user_list($arr);
				break;

			case "user_mgr_toolbar":
				$this->_user_mgr_toolbar($arr);
				break;

			case "user_mgr_tree":
				$this->_user_mgr_tree($arr);
				break;

			case "user_mgr":
				$this->_user_mgr($arr);
				break;

			case "printer_jobs":
				if ($arr["request"]["pj_job"])
				{
					return PROP_IGNORE;
				}
				$this->_printer_jobs($arr);
				break;

			case "printer_legend":
				if ($arr["request"]["pj_job"])
				{
					return PROP_IGNORE;
				}
				$prop["value"]  = "<span style='padding: 5px; background: ".$this->pj_colors["done"]."'>Valmis</span>&nbsp;&nbsp;";
				$prop["value"] .= "<span style='padding: 5px; background: ".$this->pj_colors["can_start"]."'>T&ouml;&ouml;s / v&otilde;ib alustada</span>&nbsp;&nbsp;";
				$prop["value"] .= "<span style='padding: 5px; background: ".$this->pj_colors["can_not_start"]."'>Ei saa alustada</span>&nbsp;&nbsp;";
				break;

			case "sp_name":
			case "sp_comment":
			case "sp_customer":
			case "cs_name":
			case "cs_firmajuht":
			case "cs_contact":
			case "cs_phone":
			case "cs_reg_nr":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "sp_starttime":
			case "sp_due_date":
				$prop["value"] = date_edit::get_timestamp($arr["request"][$prop["name"]]);
				break;

			case "sp_result":
				$this->_sp_result($arr);
				break;

			case "cs_result":
				$this->_cs_result($arr);
				break;

			case "sp_status":
				$prop["options"] = array(
					MRP_STATUS_DONE => "Tehtud",
					MRP_STATUS_ABORTED => "Katkestatud",
					MRP_STATUS_PLANNED => "Avatud"
				);
				$prop["value"] = $arr["request"][$prop["name"]];
				break;
		}
		return $retval;
	}

	function set_property ($arr = array ())
	{
		$prop =& $arr["prop"];
		$retval = PROP_OK;

		switch ($prop["name"])
		{
			case "projects_list":
			case "resources_list":
				$this->save_custom_form_data ($arr);
				break;
		}

		return $retval;
	}

	function callback_mod_retval ($arr)
	{
		### gantt chart start selection
		if ($arr["request"]["chart_start_date"])
		{
			$month = (int) $arr["request"]["chart_start_date"]["month"];
			$day = (int) $arr["request"]["chart_start_date"]["day"];
			$year = (int) $arr["request"]["chart_start_date"]["year"];
			$mrp_chart_start = mktime (0, 0, 0, $month, $day, $year);
			$arr["args"]["mrp_chart_start"] = $mrp_chart_start;
		}

		### gantt chart project hilight
		if ($arr["request"]["chart_project_hilight"])
		{
			$arr["args"]["mrp_hilight"] = $arr["request"]["chart_project_hilight"];
		}

		### gantt chart start move to project start
		if ($arr["request"]["chart_project_hilight_gotostart"])
		{
			$project_id = false;

			if (is_oid ($arr["args"]["mrp_hilight"]))
			{
				$project_id = $arr["args"]["mrp_hilight"];
			}

			if (is_oid ($arr["request"]["chart_project_hilight"]))
			{
				$project_id = $arr["request"]["chart_project_hilight"];
			}

			if ($project_id)
			{
				$this_object = obj($arr["args"]["id"]);
				$list = new object_list (array (
					"class_id" => CL_MRP_JOB,
					"state" => new obj_predicate_not (MRP_STATUS_DELETED),
					"parent" => $this_object->prop ("jobs_folder"),
					"exec_order" => 1,
					"project" => $project_id,
				));
				if ($list->count())
				{
					$first_job = $list->begin ();
					$project_start = $first_job->prop ("starttime");
					$project_start = mktime (0, 0, 0, date ("m", $project_start), date ("d", $project_start), date("Y", $project_start));
					$arr["args"]["mrp_chart_start"] = $project_start;
				}
			}
			else
			{
				//!!!error: no project selected for hiliting
			}
		}
	}

	function create_resources_tree ($arr = array())
	{
		$this_object =& $arr["obj_inst"];

		// ### "command" tree
		// $url_resources_show_all = $this->mk_my_orb("change", array(
			// "id" => $this_object->id (),
			// "group" => "grp_projects",
			// "mrp_resources_show" => "all",
		// ), "mrp_workspace");

		// $tree = get_instance("vcl/treeview");
		// $tree->start_tree(array(
			// "type" => TREE_DHTML,
			// "tree_id" => "commandtree",
			// "persist_state" => 1,
		// ));
		// $tree->add_item (0, array(
			// "name" => "Kõik ressursid",
			// "id" => 1,
			// "url" => $url_resources_show_all,
		// ));
		// $arr["prop"]["value"] = $tree->finalize_tree ();

		### resource tree
		$resources_folder = $this_object->prop ("resources_folder");
		$resource_tree = new object_tree(array(
			"parent" => $resources_folder,
			"class_id" => array(CL_MENU, CL_MRP_RESOURCE),
			"sort_by" => "objects.jrk",
		));

		classload("vcl/treeview");
		$tree = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "resourcetree",
				"persist_state" => true,
			),
			"root_item" => obj ($resources_folder),
			"ot" => $resource_tree,
			"var" => "mrp_tree_active_item",
			"node_actions" => array (
				CL_MRP_RESOURCE => "change",
			),
		));

		$arr["prop"]["value"] .= $tree->finalize_tree ();
	}

	function create_resources_list ($arr = array())
	{
		$table =& $arr["prop"]["vcl_inst"];
		$this_object =& $arr["obj_inst"];

		if (is_oid ($arr["request"]["mrp_tree_active_item"]))
		{
			$parent = obj ($arr["request"]["mrp_tree_active_item"]);

			if ($parent->class_id () != CL_MENU)
			{
				$parent = $parent->parent ();
			}
			else
			{
				$parent = $parent->id ();
			}
		}
		else
		{
			$parent = $this_object->prop ("resources_folder");
		}

		$table->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "operator",
			"caption" => "Operaator",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "modify",
			"caption" => "Ava",
		));
		$table->define_field(array(
			"name" => "order",
			"caption" => "Jrk.",
			"callback" => array (&$this, "order_field_callback"),
			"callb_pass_row" => false,
			"sortable" => 1
		));

		$table->define_chooser(array(
			"name" => "selection",
			"field" => "resource_id",
		));

		$table->set_default_sortby("order");
		$table->set_default_sorder("asc");

		$object_list = new object_list(array(
			"class_id" => CL_MRP_RESOURCE,
			"parent" => $parent,
		));

		$resources = $object_list->arr();

		$res2p = $this->get_workers_for_resources($object_list->ids());

		foreach ($resources as $resource)
		{
			$resource_id = $resource->id ();
			$change_url = $this->mk_my_orb ("change", array(
				"id" => $resource_id,
				"return_url" => urlencode (aw_global_get ('REQUEST_URI')),
			), "mrp_resource");

			$operators = array();

			foreach(safe_array($res2p[$resource_id]) as $person)
			{
				$operators[] = html::get_change_url(
					$person->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					$person->name()
				);
			}

			$table->define_data (array (
				"modify" => html::href (array (
					"caption" => "Ava",
					"url" => $change_url,
					)
				),
				"order" => $resource->ord (),
				"name" => $resource->name(),
				"operator" => join(",",$operators),
				"status" => $resource->prop("status"),
				"resource_id" => $resource_id,
			));
		}
	}

	function create_resources_toolbar ($arr = array())
	{
		$toolbar =& $arr["prop"]["toolbar"];
		$this_object =& $arr["obj_inst"];

		if (is_oid ($arr["request"]["mrp_tree_active_item"]))
		{
			$parent = obj ($arr["request"]["mrp_tree_active_item"]);

			if ($parent->class_id () != CL_MENU)
			{
				$parent = $parent->parent ();
			}
			else
			{
				$parent = $parent->id ();
			}
		}
		else
		{
			$parent = $this_object->prop ("resources_folder");
		}

		$add_resource_url = $this->mk_my_orb("new", array(
			"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			"mrp_workspace" => $this_object->id (),
			"parent" => $parent,
		), "mrp_resource");
		$add_category_url = $this->mk_my_orb("new", array(
			"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			"parent" => $parent,
		), "menu");

		$toolbar->add_menu_button(array(
			"name" => "add",
			"img" => "new.gif",
			"tooltip" => "Lisa uus",
		));
		$toolbar->add_menu_item(array(
			"parent" => "add",
			"text" => "Ressurss",
			"link" => $add_resource_url,
		));
		$toolbar->add_menu_item(array(
			"parent" => "add",
			"text" => "Ressurssikategooria",
			"link" => $add_category_url,
		));

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "cut",
			"tooltip" => "L&otilde;ika",
			"action" => "cut_resources",
			"img" => "cut.gif",
		));

		$toolbar->add_button(array(
			"name" => "copy",
			"tooltip" => "Kopeeri",
			"action" => "copy_resources",
			"img" => "copy.gif",
		));

		if (count(safe_array($_SESSION["mrp_workspace"]["cut_resources"])) > 0 ||
			count(safe_array($_SESSION["mrp_workspace"]["copied_resources"])) > 0)
		{
			$toolbar->add_button(array(
				"name" => "paste",
				"tooltip" => "Kleebi",
				"action" => "paste_resources",
				"img" => "paste.gif",
			));
		};

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta",
			"action" => "delete",
			"confirm" => "Kustutada kõik valitud ressursid?",
		));
	}

	function create_projects_toolbar ($arr = array())
	{
		$toolbar =& $arr["prop"]["toolbar"];
		$this_object =& $arr["obj_inst"];
		$add_project_url = $this->mk_my_orb("new", array(
			"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			"mrp_workspace" => $this_object->id (),
			"parent" => $this_object->prop ("projects_folder"),
		), "mrp_case");
		$toolbar->add_button(array(
			"name" => "add",
			"img" => "new.gif",
			"tooltip" => "Lisa uus projekt",
			"url" => $add_project_url,
		));
		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta valitud projekt(id)",
			"confirm" => "Kustutada kõik valitud projektid?",
			"action" => "delete",
		));
	}

	function create_projects_tree ($arr = array())
	{
		$this_object =& $arr["obj_inst"];
		$projects_folder = $this_object->prop ("projects_folder");

		$url_projects_all = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "all",
		), "mrp_workspace");
		$url_projects_planned = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "planned",
		), "mrp_workspace");
		$url_projects_in_work = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "inwork",
		), "mrp_workspace");
		$url_projects_overdue = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "overdue",
		), "mrp_workspace");
		$url_projects_planned_overdue = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "planned_overdue",
		), "mrp_workspace");
		$url_projects_new = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "new",
		), "mrp_workspace");
		$url_projects_done = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "done",
		), "mrp_workspace");
		$url_projects_aborted = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "aborted",
		), "mrp_workspace");
		$url_subcontract_jobs = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "subcontracts",
		), "mrp_workspace");

///!!! appdeitida need count listid table listide j2rgi
		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"state" => MRP_STATUS_PLANNED,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_planned = $list->count ();

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"state" => MRP_STATUS_INPROGRESS,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_in_work = $list->count ();

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"due_date" => new obj_predicate_compare (OBJ_COMP_LESS, time()),
			//"CL_MRP_CASE.RELTYPE_MRP_PROJECT_JOB.starttime" => new obj_predicate_compare(OBJ_COMP_GREATER, new obj_predicate_prop("due_date")),
			"finished_date" => 0,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_overdue = $list->count ();

		$od = $this->get_proj_overdue($this_object);
		$count_projects_planned_overdue = count ($od);

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"state" => MRP_STATUS_NEW,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_new = $list->count ();

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"state" => MRP_STATUS_DONE,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_done = $list->count ();

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"state" => MRP_STATUS_ABORTED,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_aborted = $list->count ();

		$tree = get_instance("vcl/treeview");
		$tree->start_tree (array (
				"type" => TREE_DHTML,
				"tree_id" => "resourcetree",
				"persist_state" => 1,
		));

		$tree->add_item (0, array (
			"name" => "Kõik plaanisolevad" . "(" . $count_projects_planned . ")",
			"id" => "planned",
			"url" => $url_projects_planned,
		));

		$tree->add_item (0, array (
			"name" => "Hetkel töösolevad" . "(" . $count_projects_in_work . ")",
			"id" => "inwork",
			"url" => $url_projects_in_work,
		));

		$tree->add_item (0, array (
			"name" => "Planeeritud üle tähtaja" . "(" . $count_projects_planned_overdue . ")",
			"id" => "planned_overdue",
			"url" => $url_projects_planned_overdue,
		));

		$tree->add_item (0, array (
			"name" => "Üle tähtaja" . "(" . $count_projects_overdue . ")",
			"id" => "overdue",
			"url" => $url_projects_overdue,
		));

		$tree->add_item (0, array (
			"name" => "Uued" . "(" . $count_projects_new . ")",
			"id" => "new",
			"url" => $url_projects_new,
		));

		$tree->add_item (0, array (
			"name" => "Valmis" . "(" . $count_projects_done . ")",
			"id" => "done",
			"url" => $url_projects_done,
		));

		$tree->add_item (0, array (
			"name" => "Katkestatud" . "(" . $count_projects_aborted . ")",
			"id" => "aborted",
			"url" => $url_projects_aborted,
		));

		$tree->add_item (0, array (
			"name" => "Kõik projektid",
			"id" => "all",
			"url" => $url_projects_all,
		));

		$tree->add_item (0, array (
			"name" => "Allhanket&ouml;&ouml;d",
			"id" => "subcontracts",
			"url" => $url_subcontract_jobs,
		));

		$active_node = empty ($arr["request"]["mrp_projects_show"]) ? "planned" : $arr["request"]["mrp_projects_show"];
		$tree->set_selected_item ($active_node);
		$arr["prop"]["value"] = $tree->finalize_tree ();
	}

	function create_projects_list ($arr = array ())
	{
		$table =& $arr["prop"]["vcl_inst"];
		$this_object =& $arr["obj_inst"];
		if (isset($arr["list_request"]))
		{
			$list_request = $arr["list_request"];
		}
		else
		{
			$list_request = $arr["request"]["mrp_projects_show"] ? $arr["request"]["mrp_projects_show"] : "planned";
		}

		$table->define_field (array (
			"name" => "customer",
			"caption" => "Klient",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "name",
			"caption" => "Projekt",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
			"numeric" => 1
		));
		$table->define_field (array (
			"name" => "starttime",
			"caption" => "Materjalide saabumine",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "planned_date",
			"caption" => "Planeeritud valmimine",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "due_date",
			"caption" => "Tähtaeg",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));

		switch ($list_request)
		{
			case "inwork":
			case "planned_overdue":
			case "overdue":
			case "new":
			case "planned":
			case "aborted":
				$table->define_field(array( //!!! teha sortableks, arvestada et prioriteet on float. v6imalus: <span style="display: none;">$priority</span><input ... value="$priority">. jama: sorter teeb integeriks kui numeric field
					"name" => "priority",
					"chgbgcolor" => "bgcolour_overdue",
					"caption" => "Prioriteet",
					"callback" => array (&$this, "priority_field_callback"),
					"callb_pass_row" => false,
					"sortable" => 1,
				));
				break;

			case "all":
			case "done":
				$table->define_field (array (
					"name" => "priority",
					"chgbgcolor" => "bgcolour_overdue",
					"caption" => "Prioriteet",
					"sortable" => 1,
				));
				break;
		}

		$table->define_field(array(
			"name" => "sales_priority",
			"caption" => "Müügi prioriteet",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "modify",
			"chgbgcolor" => "bgcolour_overdue",
			"caption" => "Ava",
		));

		if ($list_request != "search")
		{
		$table->define_chooser(array(
			"name" => "selection",
			"field" => "project_id",
		));
		}
		$table->set_default_sortby ("modified");
		$table->set_default_sorder ("desc");
		$table->draw_text_pageselector (array (
			"records_per_page" => 50,
		));


		switch ($list_request)
		{
			case "all":
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"parent" => $this_object->prop ("projects_folder"),
					// "createdby" => aw_global_get('uid'),
				));
				break;

			case "planned":
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => MRP_STATUS_PLANNED,
					"parent" => $this_object->prop ("projects_folder"),
					// "createdby" => aw_global_get('uid'),
				));
				break;

			case "inwork":
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => MRP_STATUS_INPROGRESS,
					"parent" => $this_object->prop ("projects_folder"),
					// "createdby" => aw_global_get('uid'),
				));
				break;

			case "planned_overdue":
				// $this->db_query("SELECT `oid` FROM `mrp_case` WHERE `planned_date`>`due_date`");//!!! lisada k6igile querydele join obj tabeliga et saada ainult selle workspace'i asju, siin ja scheduleris
			///!!! pyyda teha object listina
				// $list = new object_list (array (
					// "class_id" => CL_MRP_CASE,
					// "state" => MRP_STATUS_PLANNED,
					// "planned_date" => new obj_predicate_compare (OBJ_COMP_GREATER, projectduedate),///!!!
					// "parent" => $this_object->prop ("projects_folder"),
					//// "createdby" => aw_global_get('uid'),
				// ));
				// echo dbg::dump($this->get_proj_overdue($this_object));
				$od = $this->get_proj_overdue($this_object);
				if (count($od) == 0)
				{
					$list = new object_list();
				}
				else
				{
					$list = new object_list (array (
						"oid" => $od
					));
				}
				break;

			case "overdue":
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"due_date" => new obj_predicate_compare (OBJ_COMP_LESS, time()),
					"state" => new obj_predicate_not (MRP_STATUS_DONE),
					"parent" => $this_object->prop ("projects_folder"),
					// "createdby" => aw_global_get('uid'),
				));
				break;

			case "new":
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => MRP_STATUS_NEW,
					"parent" => $this_object->prop ("projects_folder"),
					// "createdby" => aw_global_get('uid'),
				));
				break;

			case "done":
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => MRP_STATUS_DONE,
					"parent" => $this_object->prop ("projects_folder"),
					// "createdby" => aw_global_get('uid'),
				));
				break;

			case "aborted":
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => MRP_STATUS_ABORTED,
					"parent" => $this_object->prop ("projects_folder"),
					// "createdby" => aw_global_get('uid'),
				));
				break;

			case "search":
				$list = $arr["search_res"];
				break;
		}

		$projects = $list->arr ();

		foreach ($projects as $project_id => $project)
		{
			$priority = $project->prop ("project_priority");
			$change_url = $this->mk_my_orb("change", array(
				"id" => $project_id,
				"return_url" => urlencode (aw_global_get ('REQUEST_URI')),
				"group" => "grp_case_schedule",
			), "mrp_case");

			### get planned project finishing date
			$planned_date = $project->prop ("planned_date");

			if (!$planned_date)
			{
				$connections = $project->connections_from (array ("type" => RELTYPE_MRP_PROJECT_JOB, "class_id" => CL_MRP_JOB));
				$jobs = count ($connections);

				$list = new object_list (array (
					"class_id" => CL_MRP_JOB,
					"state" => MRP_STATUS_PLANNED,
					"parent" => $this_object->prop ("jobs_folder"),
					"exec_order" => $jobs,
					"project" => $project_id,
				));
				$last_job = $list->begin ();
				$planned_date = is_object ($last_job) ? date (MRP_DATE_FORMAT, ($last_job->prop ("planned_length") + $last_job->prop ("starttime"))) : "-";
			}

			### get project customer
			$customer = $project->get_first_obj_by_reltype("RELTYPE_MRP_CUSTOMER");

			### do request specific operations
			switch ($list_request)
			{
				case "inwork":
				case "planned_overdue":
				case "overdue":
				case "new":
					break;

				case "planned":
					### hilight for planned overdue
					$bg_colour = ($project->prop ("due_date") < $planned_date) ? MRP_COLOUR_PLANNED_OVERDUE : false;

					### hilight for overdue
					$bg_colour = ($project->prop ("due_date") <= time ()) ? MRP_COLOUR_OVERDUE : $bg_colour;
					break;

				case "all":
				case "done":
					break;
			}

			### define data for html table row
			$definition = array (
				"modify" => html::href (array (
					"caption" => "Ava",
					"url" => $change_url,
					)
				),
				"customer" => (is_object($customer) ? $customer->name () : ""),
				"name" => $project->name (),
				"priority" => $priority,
				"sales_priority" => $project->prop ("sales_priority"),
				"starttime" => date (MRP_DATE_FORMAT, $project->prop ("starttime")),
				"due_date" => date (MRP_DATE_FORMAT, $project->prop ("due_date")),
				"planned_date" => date (MRP_DATE_FORMAT, $planned_date),
				"project_id" => $project_id,
				"bgcolour_overdue" => $bg_colour,
			);

			if (!$bg_colour)
			{
				unset ($definition["bgcolour_overdue"]);
			}

			$table->define_data($definition);
		}
	}

	function create_jobs_list ($arr = array ())
	{
		$table =& $arr["prop"]["vcl_inst"];
		$this_object =& $arr["obj_inst"];

		$table->define_field (array (
			"name" => "customer",
			"caption" => "Klient",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "project",
			"caption" => "Projekt",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "resource",
			"caption" => "Teostaja",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		// $table->define_field(array(
			// "name" => "planned_date",
			// "type" => "time",
			// "format" => MRP_DATE_FORMAT,
			// "numeric" => 1,
			// "caption" => "Kokkulepitud algusaeg",
			// "chgbgcolor" => "bgcolour_overdue",
			// "sortable" => 1,
		// ));
		$table->define_field(array(
			"name" => "scheduled_date",
			"type" => "time",
			"format" => MRP_DATE_FORMAT,
			"numeric" => 1,
			"caption" => "Planeeritud algusaeg",
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "modify",
			"chgbgcolor" => "bgcolour_overdue",
			"caption" => "Ava",
		));

		$table->set_default_sortby ("scheduled_date");
		$table->set_default_sorder ("asc");
		$table->draw_text_pageselector (array (
			"records_per_page" => 50,
		));

		$resource_tree = new object_tree (array (
			"parent" => $this_object->prop ("resources_folder"),
			"class_id" => array (CL_MENU, CL_MRP_RESOURCE),
		));
		$list = $resource_tree->to_list ();
		$list->filter (array (
			"class_id" => CL_MRP_RESOURCE,
			"type" => MRP_RESOURCE_SUBCONTRACTOR,
		), true);
		$subcontractors = $list->ids ();

		if (!empty ($subcontractors))
		{
			$applicable_states = array (
				MRP_STATUS_NEW,
				MRP_STATUS_PLANNED,
			);

			$list = new object_list (array (
				"class_id" => CL_MRP_JOB,
				"state" => $applicable_states,
				"resource" => $subcontractors,
				"parent" => $this_object->prop ("jobs_folder"),
			));
			$jobs = $list->arr ();
		}
		else
		{
			$jobs = array ();
		}

		foreach ($jobs as $job_id => $job)
		{
			$project_id = $job->prop ("project");
			$resource_id = $job->prop ("resource");

			if (!is_oid ($project_id) or !is_oid ($resource_id))
			{
				continue;
			}

			$project = obj ($project_id);
			$resource = obj ($resource_id);
			$change_url = $this->mk_my_orb("change", array(
				"id" => $job_id,
				"return_url" => urlencode (aw_global_get ('REQUEST_URI')),
			), "mrp_job");

			### get project customer
			$customer = $project->get_first_obj_by_reltype("RELTYPE_MRP_CUSTOMER");

			### hilight for planned overdue
			$bg_colour = ($job->prop ("starttime") > $planned_date) ? MRP_COLOUR_PLANNED_OVERDUE : false;

			### define data for html table row
			$definition = array (
				"customer" => (is_object ($customer) ? $customer->name () : ""),
				"project" => $project->name (),
				"resource" => $resource->name (),
				"scheduled_date" => $job->prop ("starttime"),
				// "planned_date" => "",
				"modify" => html::href (array (
					"caption" => "Ava",
					"url" => $change_url,
					)
				),
				"bgcolour_overdue" => $bg_colour,
			);

			if (!$bg_colour)
			{
				unset ($definition["bgcolour_overdue"]);
			}

			$table->define_data($definition);
		}
	}

	function create_schedule_chart ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$chart = get_instance ("vcl/gantt_chart");
		$columns = (int) ($arr["request"]["mrp_chart_length"] ? $arr["request"]["mrp_chart_length"] : 7);
		$range_start = (int) ($arr["request"]["mrp_chart_start"] ? $arr["request"]["mrp_chart_start"] : $this->get_week_start ());
		$range_end = (int) ($range_start + $columns * 86400);
		$hilighted_project = (int) ($arr["request"]["mrp_hilight"] ? $arr["request"]["mrp_hilight"] : false);
		$hilighted_jobs = array ();

		switch ($columns)
		{
			case 1:
				$subdivisions = 24;
				break;

			default:
				$subdivisions = 3;
		}

		### add row dfn-s, resource names
		$toplevel_categories = new object_list (array (
			"class_id" => CL_MENU,
			"parent" => $this_object->prop ("resources_folder"),
		));

		for ($category =& $toplevel_categories->begin (); !$toplevel_categories->end (); $category =& $toplevel_categories->next ())
		{
			$id = $category->id ();
			$chart->add_row (array (
				"name" => $id,
				"type" => "separator",
			));

			$resource_tree = new object_tree(array(
				"parent" => $id,
				"class_id" => array (CL_MRP_RESOURCE),
			));
			$resources = $resource_tree->to_list();

			for ($resource =& $resources->begin (); !$resources->end (); $resource =& $resources->next ())
			{
				$id = $resource->id ();
				$chart->add_row (array (
					"name" => $id,
					"title" => $resource->name (),
					"uri" => html::get_change_url ($id)
				));
			}
		}

		### get job id-s for hilighted project if requested
		if ($hilighted_project)
		{
			$list = new object_list (array (
				"class_id" => CL_MRP_JOB,
				"parent" => $this_object->prop ("jobs_folder"),
				"project" => $hilighted_project,
			));
			$hilighted_jobs = $list->ids ();
		}

		### get jobs in requested range & add bars
		// $list = new object_list (array (
			// "class_id" => CL_MRP_JOB,
			// "parent" => $this_object->prop ("jobs_folder"),
			// "state" => new obj_predicate_not (MRP_STATUS_DELETED),
			// "starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, $range_start, $range_end),
		// ));
		$res = $this->db_fetch_array (
			"SELECT `planned_length` FROM `mrp_job` ".
			"WHERE `state`!=" . MRP_STATUS_DELETED . " AND ".
			"`length` > 0 ".
			"ORDER BY `planned_length` DESC ".
			"LIMIT  1".
		"");
		$max_length = isset ($res[0]["planned_length"]) ? $res[0]["planned_length"] : 0;

		$jobs = $this->db_fetch_array (
			"SELECT `oid` FROM `mrp_job` ".
			"WHERE `state`!=" . MRP_STATUS_DELETED . " AND ".
			"`length` > 0 AND ".
			"`starttime` > " . ($range_start - $max_length) . " AND ".
			"`starttime` < " . $range_end . " AND ".
			"`resource` != 0 AND ".
			"`resource` IS NOT NULL".
		"");

		foreach ($jobs as $job)
		// for ($job =& $list->begin (); !$list->end (); $job =& $list->next ())
		{
			if ($this->can ("view", $job["oid"]))
			{
				$job = obj ($job["oid"]);
				$project_id = $job->prop ("project");
				$project = obj ($project_id);
				$applicable_states = array (
					MRP_STATUS_PLANNED,
					MRP_STATUS_INPROGRESS,
					MRP_STATUS_OVERDUE,
					MRP_STATUS_DONE,
				);

				if (!in_array ($project->prop ("state"), $applicable_states))
				{
					continue;
				}

				$length = $job->prop ("planned_length");
				$resource_id = $job->prop ("resource");
				$resource = obj ($resource_id);
				$start = $job->prop ("starttime");
				$type = in_array ($job->id (), $hilighted_jobs) ? "hilighted" : "";
				$job_name = $project->name () . " - " . $resource->name ();

				$bar = array (
					"row" => $resource_id,
					"start" => $start,
					"type" => $type,
					"length" => $length,
					"uri" => aw_url_change_var ("mrp_hilight", $project_id),
					"title" => $job_name . " (" . date (MRP_DATE_FORMAT, $start) . " - " . date (MRP_DATE_FORMAT, $start + $length) . ")"
/* dbg */ . " [res: " . $resource_id . " job: " . $job->id () . " proj: " . $project_id . "]"
				);

				$chart->add_bar ($bar);
			}
		}

		### config
		$chart->configure_chart (array (
			"chart_id" => "master_schedule_chart",
			"style" => "aw",
			"start" => $range_start,
			"end" => $range_end,
			"columns" => $columns,
			"subdivisions" => $subdivisions,
			"timespans" => $subdivisions,
			"width" => 950,
		));

		### define columns
		$i = 0;
		$days = array ("P", "E", "T", "K", "N", "R", "L");

		while ($i < $columns)
		{
			$day_start = ($range_start + ($i * 86400));
			$day = date ("w", $day_start);
			$date = date ("j/m/Y", $day_start);
			$uri = aw_url_change_var ("mrp_chart_length", 1);
			$uri = aw_url_change_var ("mrp_chart_start", $day_start, $uri);
			$chart->define_column (array (
				"col" => ($i + 1),
				"title" => $days[$day] . " - " . $date,
				"uri" => $uri,
			));
			$i++;
		}

		return $chart->draw_chart ();
	}

	function create_chart_navigation ($arr)
	{
		$start = (int) ($arr["request"]["mrp_chart_start"] ? $arr["request"]["mrp_chart_start"] : time ());
		$columns = (int) ($arr["request"]["mrp_chart_length"] ? $arr["request"]["mrp_chart_length"] : 7);
		$start = ($columns == 7) ? $this->get_week_start ($start) : $start;
		$period_length = $columns * 86400;
		$length_nav = array ();
		$start_nav = array ();

		for ($days = 1; $days < 8; $days++)
		{
			if ($columns == $days)
			{
				$length_nav[] = $days;
			}
			else
			{
				$length_nav[] = html::href (array (
					"caption" => $days,
					"url" => aw_url_change_var ("mrp_chart_length", $days),
				));
			}
		}

		$start_nav[] = html::href (array (
			"caption" => "<<",
			"title" => "5 tagasi",
			"url" => aw_url_change_var ("mrp_chart_start", ($start - 5*$period_length)),
		));
		$start_nav[] = html::href (array (
			"caption" => "Eelmine",
			"url" => aw_url_change_var ("mrp_chart_start", ($start - $period_length)),
		));
		$start_nav[] = html::href (array (
			"caption" => "Täna",
			"url" => aw_url_change_var ("mrp_chart_start", $this->get_week_start ()),
		));
		$start_nav[] = html::href (array (
			"caption" => "Järgmine",
			"url" => aw_url_change_var ("mrp_chart_start", ($start + $period_length + 1)),
		));
		$start_nav[] = html::href (array (
			"caption" => ">>",
			"title" => "5 edasi",
			"url" => aw_url_change_var ("mrp_chart_start", ($start + 5*$period_length)),
		));

		$navigation = '&nbsp;&nbsp;Periood: ' . implode (" ", $start_nav) . ' &nbsp;&nbsp;Päevi perioodis: ' . implode (" ", $length_nav);

		if (is_oid ($arr["request"]["mrp_hilight"]))
		{
			$project = obj ($arr["request"]["mrp_hilight"]);
			$deselect = html::href (array (
				"caption" => "Kaota valik",
				"url" => aw_url_change_var ("mrp_hilight", ""),
			));
			$navigation .= ' &nbsp;&nbsp;Valitud projekt: <span style="color: #CC0000;">' . $project->name () . '</span> (' . $deselect . ')';
		}

		return $navigation;
	}

	function save_custom_form_data ($arr = array ())
	{
		foreach ($arr["request"] as $name => $value)
		{
			$prop = explode ("-", $name);
			$name = $prop[0];
			$oid = $prop[1];

			if (!is_oid ($oid))
			{
				continue;
			}

			switch ($name)
			{
				case "mrp_project_priority":
					$project = obj ($oid);
					$project->set_prop ("project_priority", $this->safe_settype_float ($value));
					$project->save ();
					break;

				case "mrp_resource_order":
					$resource = obj ($oid);
					$resource->set_ord ((int) $value);
					$resource->save ();
					break;
			}
		}
	}

	/**
		@attrib name=delete
	**/
	function delete ($arr)
	{
		$sel = $arr["selection"];

		if (is_array ($sel))
		{
			$ol = new object_list (array (
				"oid" => array_keys ($sel),
			));

			for ($o = $ol->begin (); !$ol->end (); $o = $ol->next ())
			{
				if ($this->can ("delete", $o->id()))
				{
					$o->delete ();
				}
			}
		}

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_workspace");
		return $return_url;
	}

	function get_proj_overdue($this_object)
	{
		$ret = array();
		$this->db_query("
			SELECT
				objects.oid
			FROM
				objects
				LEFT JOIN mrp_case on mrp_case.oid = objects.oid
				LEFT JOIN aliases ON (aliases.source = objects.oid AND aliases.reltype = 3)
				LEFT JOIN mrp_job ON mrp_job.oid = aliases.target
			WHERE
				objects.status > 0 AND
				objects.class_id = ".CL_MRP_CASE." AND
				objects.parent = " . $this_object->prop ("projects_folder") . " AND
				mrp_job.starttime > mrp_case.due_date
		");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["oid"];
		}
		return $ret;
	}

	function get_week_start ($time = false)
	{
		if (!$time)
		{
			$time = time ();
		}

		$date = getdate ($time);
		$wday = $date["wday"] ? ($date["wday"] - 1) : 6;
		$week_start = $time - ($wday * 86400 + $date["hours"] * 3600 + $date["minutes"] * 60 + $date["seconds"]);
		return $week_start;
	}

	function _user_list_toolbar($arr)
	{
		$o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");
		if ($o)
		{
			$tmp = $arr["obj_inst"];
			$arr["obj_inst"] = $o;
			$co = $o->instance();
			$co->callback_on_load($arr);
			$co->do_contact_toolbar(&$arr["prop"]['toolbar'],&$arr);

			$tb =& $arr["prop"]["vcl_inst"];
			$tb->remove_button("Kone");
			$tb->remove_button("Kohtumine");
			$tb->remove_button("Toimetus");
			$tb->remove_button("Search");

			$arr["obj_inst"] = $tmp;
		}
	}

	function _user_list_tree($arr)
	{
		$this->_delegate_co_v($arr, "_do_unit_listing_tree");
	}

	function _user_list($arr)
	{
		$this->_delegate_co_v($arr, "do_human_resources");
	}

	function _delegate_co_v($arr, $fun)
	{
		$o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");
		if ($o)
		{
			$tmp = $arr["obj_inst"];
			$arr["obj_inst"] = $o;
			$co = $o->instance();
			$co->callback_on_load($arr);
			$co->$fun($arr);
			$arr["obj_inst"] = $tmp;
		}
	}

	/** handler for person list delete. forwards to crm_company

		@attrib name=submit_delete_relations

	**/
	function submit_delete_relations($arr)
	{
		$this->_delegate_co($arr, "submit_delete_relations");
		return urldecode($arr["return_url"]);
	}

	function callback_mod_reforb(&$arr)
	{
		$arr['unit'] = $_GET["unit"];
		$arr['category'] = $_GET["category"];
		if ($_GET["group"] != "grp_search_proj" && $_GET["group"] != "grp_search_cust")
		{
		$arr['return_url'] = urlencode(aw_global_get('REQUEST_URI'));
		}

		$arr['cat'] = $_GET["cat"];
		$arr['pj_job'] = $_GET["pj_job"];
		$arr['mrp_tree_active_item'] = $_GET["mrp_tree_active_item"];
//!!! if true?
		if (true || is_oid($_GET["pj_job"]) || $_GET["group"] == "grp_printer")
		{
			aw_register_header_text_cb(array(&$this, "make_aw_header"));
		}

		// if no back link is set, make the yah empty
		if (!$_GET["return_url"])
		{
			aw_global_set("hide_yah", true);
		}
	}

	/** cuts the selected person objects

		@attrib name=cut_p

	**/
	function cut_p($arr)
	{
		return $this->_delegate_co($arr, "cut_p");
	}

	/** copies the selected person objects

		@attrib name=copy_p

	**/
	function copy_p($arr)
	{
		return $this->_delegate_co($arr, "copy_p");
	}

	/** pastes the cut/copied person objects

		@attrib name=paste_p

	**/
	function paste_p($arr)
	{
		return $this->_delegate_co($arr, "paste_p");
	}

	function _delegate_co($arr, $fun)
	{
		$oo = obj($arr["id"]);
		$o = $oo->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");
		if ($o)
		{
			$arr["id"] = $o->id();

			$o = $o->instance();
			$o->callback_on_load($arr);
			return $o->$fun($arr);
		}
	}

	function _user_mgr_toolbar($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"caption" => t("Salvesta"),
			"action" => "submit_user_mgr_save"
		));
	}

	function _user_mgr_tree($arr)
	{
		$this->_delegate_co_v($arr, "_do_unit_listing_tree");
		// remove all professions from the tree
		$tv =& $arr["prop"]["vcl_inst"];

		foreach($tv->get_item_ids() as $id)
		{
			$item = $tv->get_item($id);
			if ($item["class_id"] == CL_CRM_PROFESSION)
			{
				$tv->remove_item($id);
			}
		}
	}

	function _init_user_mgr(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "sel_resource",
			"caption" => t("Vali ressurss"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "all_resources",
			"caption" => t("N&auml;ita k&otilde;ikide ressurside t&ouml;id"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "dept_resources",
			"caption" => t("N&auml;ita osakonna ressurside t&ouml;id"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center"
		));
	}

	function _user_mgr($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_user_mgr($t);

		if (!is_oid($arr["request"]["unit"]))
		{
			return;
		}

		$all_resources = $arr["obj_inst"]->meta("umgr_all_resources");
		$dept_resources = $arr["obj_inst"]->meta("umgr_dept_resources");

		$resource_tree = new object_tree(array(
			"parent" => $arr["obj_inst"]->prop ("resources_folder"),
			"class_id" => array(CL_MENU, CL_MRP_RESOURCE),
		));
		$l = $resource_tree->to_list();
		$resources = array("" => "");
		foreach($l->arr() as $o)
		{
			if ($o->class_id() == CL_MRP_RESOURCE)
			{
				$resources[$o->id()] = $o->name();
			}
		}

		$prof2res = array();
		$ol = new object_list(array(
			"class_id" => CL_MRP_RESOURCE_OPERATOR,
			"parent" => $arr["obj_inst"]->id()
		));
		foreach($ol->arr() as $o)
		{
			$prof2res[$o->prop("profession")] = $o->prop("resource");
		}

		$unit = obj($arr["request"]["unit"]);
		foreach($unit->connections_from(array("type" => "RELTYPE_PROFESSIONS")) as $c)
		{
			$t->define_data(array(
				"name" => $c->prop("to.name"),
				"sel_resource" => html::select(array(
					"name" => "prof2res[".$c->prop("to")."]",
					"options" => $resources,
					"value" => $prof2res[$c->prop("to")]
				)),
				"all_resources" => html::checkbox(array(
					"name" => "all_resources[".$c->prop("to")."]",
					"value" => 1,
					"checked" => $all_resources[$c->prop("to")],
				)),
				"dept_resources" => html::checkbox(array(
					"name" => "dept_resources[".$c->prop("to")."]",
					"value" => 1,
					"checked" => $dept_resources[$c->prop("to")],
				)),
				"change" => html::get_change_url($c->prop("to"), array(), "Muuda")
			));
		}
	}

	/**

		@attrib name=submit_user_mgr_save

	**/
	function submit_user_mgr_save($arr)
	{
		$arr["return_url"] = urldecode($arr["return_url"]);

		if (!is_oid($arr["unit"]) || !$this->can("view", $arr["unit"]))
		{
			return $arr["return_url"];
		}

		$unit = obj($arr["unit"]);

		// get all professions for selected unit
		$professions = new object_list($unit->connections_from(array(
			"type" => "RELTYPE_PROFESSION"
		)));

		// get existing operators for the selected unit
		$operators = new object_list(array(
			"class_id" => CL_MRP_RESOURCE_OPERATOR,
			"parent" => $arr["id"],
			"profession" => $professions->ids(),
			"unit" => $arr["unit"]
		));
		$existing_rels = array();
		foreach($operators->arr() as $o)
		{
			$existing_rels[$o->prop("profession")] = array(
				"res" => $o->prop("resource"),
				"rel" => $o
			);
		}

		// create new rels for new ones
		// modify exitsing ones
		foreach(safe_array($arr["prof2res"]) as $prof => $res)
		{
			if (!is_oid($prof) || !is_oid($res))
			{
				continue;
			}

			if (!isset($existing_rels[$prof]))
			{
				// create new
				$rel = obj();
				$rel->set_class_id(CL_MRP_RESOURCE_OPERATOR);
				$rel->set_parent($arr["id"]);
				$prof_o = obj($prof);
				$res_o = obj($res);
				$rel->set_name("ametinimetus ".$prof_o->name()." => ressurss ".$res_o->name());
				$rel->set_prop("profession", $prof);
				$rel->set_prop("resource", $res);
				$rel->set_prop("unit", $arr["unit"]);
				$rel->save();
			}
			else
			if ($existing_rels[$prof]["res"] != $res)
			{
				// change cur
				$rel = $existing_rels[$prof]["rel"];
				$rel->set_prop("resource", $res);
				$rel->save();
			}

			unset($existing_rels[$prof]);
		}

		// delete deleted ones
		foreach($existing_rels as $prof => $rel)
		{
			if (empty($prof2res[$prof]))
			{
				$rel["rel"]->delete();
			}
		}

		$o = obj($arr["id"]);
		$o->set_meta("umgr_all_resources", $arr["all_resources"]);
		$o->set_meta("umgr_dept_resources", $arr["dept_resources"]);
		$o->save();

		// cleverly return
		return $arr["return_url"];
	}

	function create_customers_toolbar($arr)
	{
		$co = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");
		if (!$co)
		{
			return;
		}

		$t =& $arr["prop"]["vcl_inst"];

		$t->add_menu_button(array(
			"name" => "add_menu",
			"tooltip" => t("Uus"),
		));

		$t->add_menu_item(array(
			"parent" => "add_menu",
			"text" => t('Lisa kategooria'),
			"link" => html::get_new_url(CL_CRM_CATEGORY, $co->id(), array(
				"alias_to" => ($arr["request"]["cat"] ? $arr["request"]["cat"] : $co->id()),
				"reltype" => ($arr["request"]["cat"] ? 2 : 30),
				"return_url" => urlencode(aw_global_get("REQUEST_URI"))
			)),
		));

		if (false && is_oid($arr["request"]["cat"]))
		{
			$t->add_menu_item(array(
				"parent" => "add_menu",
				"text" => t('Lisa klient'),
				"link" => html::get_new_url(CL_CRM_COMPANY, $co->id(), array(
					"alias_to" => ($arr["request"]["cat"] ? $arr["request"]["cat"] : $co->id()),
					"reltype" => 3,
					"return_url" => urlencode(aw_global_get("REQUEST_URI"))
				))
			));
		}


		/*$t->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"img" => "delete.gif",
			"action" => "delete_customers"
		));*/
	}

	function create_customers_tree($arr)
	{
		$co = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");
		if (!$co)
		{
			return;
		}

		$t =& $arr["prop"]["vcl_inst"];
		classload("core/icons");

		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$nm = $c->prop("to.name");

			$t->add_item(0, array(
				"id" => $c->prop("to"),
				"name" => ($_GET["cat"] == $c->prop("to") ? "<b>".$nm."</b>" : $nm),
				"url" => aw_url_change_var("cat", $c->prop("to")),
			));
			$this->_req_create_customers_tree($c->to(), $t);
		}
	}

	function _req_create_customers_tree($co, &$t)
	{
		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$nm = $c->prop("to.name");
			$t->add_item($co->id(), array(
				"id" => $c->prop("to"),
				"name" => ($_GET["cat"] == $c->prop("to") ? "<b>".$nm."</b>" : $nm),
				"url" => aw_url_change_var("cat", $c->prop("to")),
			));
			$this->_req_create_customers_tree($c->to(), $t);
		}

		foreach($co->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
		{
			$to = $c->to();
			$nm = $c->prop("to.name");
			$t->add_item($co->id(), array(
				"id" => $c->prop("to"),
				"name" => ($_GET["cust"] == $c->prop("to") ? "<b>".$nm."</b>" : $nm),
				"url" => aw_url_change_var("cust", $c->prop("to")),
				"iconurl" => icons::get_icon_url($to->class_id())
			));
		}
	}

	function _init_cust_list_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "address",
			"caption" => "Aadress",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "phone",
			"caption" => "Telefon",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "email",
			"caption" => "E-mail",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "priority",
			"caption" => "Prioriteet",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_chooser(array(
			"name" => "select",
			"field" => "oid"
		));
	}

	function create_customers_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		if (!is_oid($arr["request"]["cust"]) && is_oid($arr["request"]["cat"]))
		{
			$this->_init_cust_list_t($t);

			// get customers from cat
			$cat = obj($arr["request"]["cat"]);
			foreach($cat->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
			{
				$addr = "";

				$cust = $c->to();

				$t->define_data(array(
					"name" => html::get_change_url($c->prop("to"), array("return_url" => urlencode(aw_global_get("REQUEST_URI"))), $c->prop("to.name")),
					"address" => $cust->prop_str("contact"),
					"phone" => $cust->prop_str("phone_id"),
					"email" => $cust->prop_str("email_id"),
					"oid" => $cust->id(),
					"priority" => $cust->prop("priority")
				));
			}
			return PROP_OK;
		}
		return PROP_IGNORE;
	}

	function _init_cust_list_proj_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Number",
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "comment",
			"caption" => "Kommentaar",
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "start",
			"caption" => "Algus",
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => "T&auml;htaeg",
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "planned",
			"caption" => "Planeeritud",
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
	}

	function create_customers_list_proj($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		if (is_oid($arr["request"]["cust"]))
		{
			$this->_init_cust_list_proj_t($t);

			$cust = obj($arr["request"]["cust"]);
			$ol = new object_list(array(
				"class_id" => CL_MRP_CASE,
				"customer" => $cust->id()
			));
			foreach($ol->arr() as $case)
			{
				$t->define_data(array(
					"name" => html::get_change_url($case->id(), array("return_url" => urlencode(aw_global_get("REQUEST_URI"))), $case->name()),
					"comment" => $case->comment(),
					"start" => $case->prop("starttime"),
					"end" => $case->prop("due_date"),
					"planned" => $case->prop("planned_date")
				));
			}
			$t->set_default_sortby("name");
			$t->sort_by();
			return PROP_OK;
		}
		return PROP_IGNORE;
	}

	/** imports given project from prisma db

		@attrib name=import_project

		@param id required type=int

	**/
	function import_project($arr)
	{
		$i = get_instance(CL_MRP_PRISMA_IMPORT);
		$id = $i->import_project($arr["id"]);

		header("Location: ".html::get_change_url($id)."&return_url=".urlencode(html::get_change_url(aw_ini_get("prisma.ws"))."&group=grp_projects"));
		die();
	}

	function _init_printer_jobs_t(&$t)
	{
		$t->define_field(array(
			"name" => "tm",
			"caption" => t("Algus"),
			"type" => "time",
			"align" => "center",
			"format" => "d.m.Y H:i",
			"numeric" => 1,
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "length",
			"caption" => t("Pikkus"),
			"align" => "center",
			"numeric" => 1,
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "tm_end",
			"caption" => t("L&otilde;pp"),
			"type" => "time",
			"align" => "center",
			"format" => "d.m.Y H:i",
			"numeric" => 1,
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"chgbgcolor" => "bgcol",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "customer",
			"caption" => t("Klient"),
			"align" => "center",
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "project",
			"caption" => t("Projekt"),
			"align" => "center",
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "resource",
			"caption" => t("Ressurss"),
			"align" => "center",
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "worker",
			"caption" => t("Teostaja"),
			"align" => "center",
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "job",
			"caption" => t("Ava"),
			"align" => "center",
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));
	}

	function _printer_jobs($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_printer_jobs_t($t);

		$res = $this->get_cur_printer_resources(array(
			"ws" => $arr["obj_inst"]
		));

		classload("date_calc");
		$jobs = $this->get_next_jobs_for_resources(array(
			"resources" => $res,
			"limit" => 30,
			"minstart" => ($arr["request"]["group"] == "grp_printer_current" || $arr["request"]["group"] == "grp_printer" ? get_day_start() : NULL),
			"maxend" => ($arr["request"]["group"] == "grp_printer_current" || $arr["request"]["group"] == "grp_printer" ? NULL : get_day_start()),
			"onlydone" => ($arr["request"]["group"] == "grp_printer_done" ? true : false)
		));

		$workers = $this->get_workers_for_resources($res);

		$mrp_job = get_instance(CL_MRP_JOB);
		$mrp_case = get_instance(CL_MRP_CASE);

		foreach($jobs as $job)
		{
			if (!is_oid($job->prop("project")) || !$this->can("view", $job->prop("project")))
			{
				continue;
			}
			$res = obj($job->prop("resource"));
			$proj = obj($job->prop("project"));

			$workers_str = array();
			foreach(safe_array($workers[$res->id()]) as $person)
			{
				$workers_str[] = html::get_change_url($person->id(), array(), $person->name());
			}

			$len  = sprintf("%02d", floor($job->prop("length") / 3600)).":";
			$len .= sprintf("%02d", floor(($job->prop("length") % 3600) / 60));

			$custo = "";
			$cust = $proj->get_first_obj_by_reltype("RELTYPE_MRP_CUSTOMER");
			if (is_object($cust))
			{
				$custo = html::get_change_url($cust->id(), array(
						"return_url" => urlencode(aw_global_get("REQUEST_URI"))
					),
					$cust->name()
				);
			}

			if ($job->prop("state") == MRP_STATUS_DONE)
			{
				// dark green
				$bgcol = $this->pj_colors["done"];
			}
			else
			if ($job->prop("state") == MRP_STATUS_INPROGRESS || $mrp_case->can_start_job(array("project" => $job->prop("project"), "job" => $job->id())))
			{
				// light green
				$bgcol = $this->pj_colors["can_start"];
			}
			else
			{
				// light red
				$bgcol = $this->pj_colors["can_not_start"];
			}
			$t->define_data(array(
				"tm" => $job->prop("starttime"),
				"tm_end" => $job->prop("starttime") + $job->prop("length"),
				"length" => $len,
				"job" => html::href(array(
					"caption" => t("Ava"),
					"url" => aw_url_change_var(array(
						"pj_job" =>  $job->id(),
						"return_url" => urlencode(aw_global_get("REQUEST_URI"))
					)),
				)),
				"resource" => html::get_change_url($res->id(), array(
						"return_url" => urlencode(aw_global_get("REQUEST_URI"))
					),
					$res->name()
				),
				"worker" => join(", ",$workers_str),
				"project" => html::get_change_url($proj->id(), array(
						"return_url" => urlencode(aw_global_get("REQUEST_URI"))
					),
					$proj->name()
				),
				"customer" => $custo,
				"status" => $mrp_job->states[$job->prop("state")],
				"bgcol" => $bgcol
			));
		}

		$t->set_default_sortby("tm");
		$t->sort_by();
	}

	function get_cur_printer_resources_desc($arr)
	{
		// get person
		$u = get_instance(CL_USER);
		$person = obj($u->get_current_person());

		// get professions for person
		$profs = new object_list($person->connections_from(array(
			"type" => "RELTYPE_RANK"
		)));

		// if current person has no rank, return all resources
		if (!$profs->count())
		{
			return "";
		}

		// get resource operators for professions
		$ops = new object_list(array(
			"profession" => $profs->ids(),
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_MRP_RESOURCE_OPERATOR
		));

		// get resources
		$ret = array();
		foreach($ops->arr() as $op)
		{
			if (is_oid($op->prop("resource")) && $this->can("view", $op->prop("resource")))
			{
				$reso = obj($op->prop("resource"));
				$ret[] = $reso->name();
			}
		}

		// if no resources are given, check if the current user should have
		// all resources displayed, the department's resources displayed
		// or none
		$ws = $arr["ws"];

		$all_res = $ws->meta("umgr_all_resources");
		foreach($profs->arr() as $prof)
		{
			if ($all_res[$prof->id()] == 1)
			{
				return "K&otilde;ik ressursid";
			}
		}

		$dept_res = $ws->meta("umgr_dept_resources");
		foreach($profs->arr() as $prof)
		{
			if ($dept_res[$prof->id()] == 1)
			{
				return "Osakonna ressursid";
			}
		}

		return join(", ", $ret);
	}

	function get_cur_printer_resources($arr)
	{
		// get person
		$u = get_instance(CL_USER);
		$person = obj($u->get_current_person());

		// get professions for person
		$profs = new object_list($person->connections_from(array(
			"type" => "RELTYPE_RANK"
		)));

		// if current person has no rank, return all resources
		if (!$profs->count())
		{
			return array();
		}

		// get resource operators for professions
		$ops = new object_list(array(
			"profession" => $profs->ids(),
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_MRP_RESOURCE_OPERATOR
		));

		// get resources
		$ret = array();
		foreach($ops->arr() as $op)
		{
			if (is_oid($op->prop("resource")) && $this->can("view", $op->prop("resource")))
			{
				$ret[$op->prop("resource")] = $op->prop("resource");
			}
		}

		// if no resources are given, check if the current user should have
		// all resources displayed, the department's resources displayed
		// or none
		if (count($res) == 0)
		{
			$ws = $arr["ws"];

			$all_res = $ws->meta("umgr_all_resources");
			$dept_res = $ws->meta("umgr_dept_resources");

			foreach($profs->arr() as $prof)
			{
				if ($all_res[$prof->id()] == 1)
				{
					// return all resources
					$ol = new object_list(array(
						"class_id" => CL_MRP_RESOURCE,
						"lang_id" => array(),
						"site_id" => array()
					));
					return $this->make_keys($ol->ids());
				}
				else
				if ($dept_res[$prof->id()] == 1)
				{
					// get the user's department
					foreach($person->connections_from(array("RELTYPE_SECTION")) as $c)
					{
						$sect = $c->to();

						// get all resources for section
						$ol = new object_list(array(
							"class_id" => CL_MRP_RESOURCE_OPERATOR,
							"unit" => $sect->id()
						));
						foreach($ol->arr() as $o)
						{
							$ret[$o->prop("resource")] = $o->prop("resource");
						}
					}
				}
			}
		}

		return $ret;
	}

	/** returns array of job objects for the given professions in time order

		@comment
			resources - array of recource id's to return jobs for
			limit - limit number of returned data
			ws - workspace object
	**/
	function get_next_jobs_for_resources($arr)
	{
		if (!is_array($arr["resources"]) || count($arr["resources"]) == 0)
		{
			return array();
		}
		if (empty($arr["minstart"]))
		{
			$arr["minstart"] = 100;
		}
		$filt = array(
			"resource" => $arr["resources"],
			"limit" => $arr["limit"],
			"class_id" => CL_MRP_JOB,
			"site_id" => array(),
			"lang_id" => array(),
			"starttime" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $arr["minstart"]),
//			"status" => new obj_predicate_not(MRP_STATUS_DONE)
		);

		if ($arr["onlydone"])
		{
			$filt["status"] = MRP_STATUS_DONE;
		}

		if ($arr["maxend"] > 100 )
		{
			$filt["starttime"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, 100, $arr["maxend"]);
		}

		$jobs = new object_list($filt);
		$ret = array();
		foreach($jobs->arr() as $o)
		{
			if (is_oid($o->prop("resource")) && $this->can("view", $o->prop("resource")))
			{
				$ret[] = $o;
			}
		}
		return $ret;
	}

	/** reverse lookup, from resources to persons

		@comment
			res - array of resource id's to look up
	**/
	function get_workers_for_resources($res)
	{
		$persons = array();
		$profs = array();

		$ops = new object_list(array(
			"class_id" => CL_MRP_RESOURCE_OPERATOR,
			"resource" => $res,
			"site_id" => array(),
			"lang_id" => array()
		));
		foreach($ops->arr() as $op)
		{
			// get professions for resources
			$prof = $op->prop("profession");
			$persons[$op->prop("resource")][$prof] = $prof;
			$profs[$prof] = $prof;
		}

		if (!count($profs))
		{
			return array();
		}

		// get persons for professions
		$prof2person = array();
		$c = new connection();
		$conns = $c->find(array(
			"from.class_id" => CL_CRM_PERSON,
			"type" => 7,
			"to.oid" => $profs
		));
		foreach($conns as $con)
		{
			$prof2person[$con["to"]][$con["from"]] = $con["from"];
		}

		$ret = array();
		foreach($persons as $resource => $profs)
		{
			foreach($profs as $prof)
			{
				foreach(safe_array($prof2person[$prof]) as $person)
				{
					$ret[$resource][$person] = obj($person);
				}
			}
		}
		return $ret;
	}

	function _do_pj_toolbar($arr, $job)
	{
		$tmp = $arr["obj_inst"];
		$arr["obj_inst"] = $job;
		$j = get_instance(CL_MRP_JOB);
		$j->create_job_toolbar($arr);

		$arr["obj_inst"] = $tmp;
	}

	/**
		@attrib name=start
		@param id required type=int
	**/
	function start ($arr)
	{
		$tmp = $arr;
		$j = get_instance(CL_MRP_JOB);
		$arr["id"] = $arr["pj_job"];
		$j->start($arr);

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer",
			"pj_job" => $tmp["pj_job"]
		));
	}

	/**
		@attrib name=done
		@param id required type=int
	**/
	function done ($arr)
	{
		$tmp = $arr;
		$j = get_instance(CL_MRP_JOB);
		$arr["id"] = $arr["pj_job"];
		$j->done($arr);

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer",
			"pj_job" => $tmp["pj_job"]
		));
	}

	/**
		@attrib name=abort
		@param id required type=int
	**/
	function abort ($arr)
	{
		$tmp = $arr;
		$j = get_instance(CL_MRP_JOB);
		$arr["id"] = $arr["pj_job"];
		$j->abort($arr);

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer",
			"pj_job" => $tmp["pj_job"]
		));
	}

	/**
		@attrib name=pause
		@param id required type=int
	**/
	function pause ($arr)
	{
		$tmp = $arr;
		$j = get_instance(CL_MRP_JOB);
		$arr["id"] = $arr["pj_job"];
		$j->pause($arr);

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer",
			"pj_job" => $tmp["pj_job"]
		));
	}

	/**
		@attrib name=scontinue
		@param id required type=int
	**/
	function scontinue ($arr)
	{
		$tmp = $arr;
		$j = get_instance(CL_MRP_JOB);
		$arr["id"] = $arr["pj_job"];
		$j->scontinue($arr);

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer",
			"pj_job" => $tmp["pj_job"]
		));
	}

	/**
		@attrib name=end_shift
		@param id required type=int
	**/
	function end_shift ($arr)
	{
		$tmp = $arr;
		$j = get_instance(CL_MRP_JOB);
		$arr["id"] = $arr["pj_job"];
		$j->end_shift($arr);

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer",
			"pj_job" => $tmp["pj_job"]
		));
	}

	function mrp_log($proj, $job, $msg, $comment = '')
	{
		$this->db_query("
			INSERT INTO
				mrp_log(
					project_id,job_id,uid,tm,message,comment
				)
				values(
					".((int)$proj).",".((int)$job).",'".aw_global_get("uid")."',".time().",'$msg','$comment'
				)
		");
	}

	function safe_settype_float ($value)
	{
		$parts1 = explode (",", $value, 2);
		$parts2 = explode (".", $value, 2);
		$parts = (count ($parts2) == 1) ? $parts1 : $parts2;
		$value = (float) ((isset ($parts[0]) ? ((int) $parts[0]) : 0) . "." . (isset ($parts[1]) ? ((int) $parts[1]) : 0));
		return $value;
	}

	/**

		@attrib name=cut_resources

	**/
	function cut_resources($arr)
	{
		$_SESSION["mrp_workspace"]["cut_resources"] = safe_array($arr["selection"]);
		return urldecode($arr["return_url"]);
	}

	/**

		@attrib name=copy_resources

	**/
	function copy_resources($arr)
	{
		$_SESSION["mrp_workspace"]["copied_resources"] = safe_array($arr["selection"]);
		return urldecode($arr["return_url"]);
	}

	/**

		@attrib name=paste_resources

	**/
	function paste_resources($arr)
	{
		foreach(safe_array($_SESSION["mrp_workspace"]["cut_resources"]) as $resource)
		{
			if (is_oid($resource) && $this->can("edit", $resource))
			{
				$o = obj($resource);
				$o->set_parent($arr["mrp_tree_active_item"]);
				$o->save();
			}
		}
		unset($_SESSION["mrp_workspace"]["cut_resources"]);

		foreach(safe_array($_SESSION["mrp_workspace"]["copied_resources"]) as $resource)
		{
			if (is_oid($resource) && $this->can("view", $resource) && $this->can("add", $arr["mrp_tree_active_item"]))
			{
				$o = obj($resource);
				$o->set_parent($arr["mrp_tree_active_item"]);
				$o->save_new();
			}
		}
		unset($_SESSION["mrp_workspace"]["copied_resources"]);

		return urldecode($arr["return_url"]);
	}

	function callback_on_load($arr)
	{
		if ($this->can("view", 17639))
	{
			$this->cfgmanager = 17639;
		}
	}

	function priority_field_callback ($row)
	{
		$cellcontents = html::textbox (array (
			"name" => "mrp_project_priority-" . $row["project_id"],
			"size" => "2",
			"value" => $row["priority"],
		));
		return $cellcontents;
	}

	function order_field_callback ($row)
	{
		$cellcontents = 	html::textbox (array (
			"name" => "mrp_resource_order-" . $row["resource_id"],
			"size" => "2",
			"value" => $row["order"],
		));
		return $cellcontents;
	}

	function make_aw_header()
	{
		// current user name, logout link
		$us = get_instance(CL_USER);

		$p_id = $us->get_current_person();
		if (!$p_id)
		{
			return "";
		}

		$person = obj($p_id);
		$hdr = "<span style=\"font-size: 18px; color: red;\">".$person->prop("name")." | ".html::href(array(
				"url" => $this->mk_my_orb("logout", array(), "users"),
				"caption" => t("Logi v&auml;lja")
			))."  | ".$this->get_cur_printer_resources_desc(array("ws" => obj(aw_ini_get("prisma.ws"))))." | </span>";

		return $hdr;
	}

	function _sp_result($arr)
	{
		if (empty($arr["request"]["MAX_FILE_SIZE"]))
		{
			$results = new object_list();
		}
		else
		{
			$filt = array(
				"class_id" => CL_MRP_CASE,
				"name" => "%".$arr["request"]["sp_name"]."%",
				"comment" => "%".$arr["request"]["sp_comment"]."%",
			);
			$st = date_edit::get_timestamp($arr["request"]["sp_starttime"]);
			if ($st > 100)
			{
				$filt["starttime"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $st);
			}

			$dd = date_edit::get_timestamp($arr["request"]["sp_due_date"]);
			if ($dd > 100)
			{
				$filt["due_date"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $dd);
			}

			if ($arr["request"]["sp_customer"] != "")
			{
				$filt["CL_MRP_CASE.customer.name"] = "%".$arr["request"]["sp_customer"]."%";
			}

			if ($arr["request"]["sp_status"])
			{
				$filt["state"] = $arr["request"]["sp_status"];
			}
			$results = new object_list($filt);
		}

		$arr["list_request"] = "search";
		$arr["search_res"] = $results;
		$this->create_projects_list($arr);
	}

	function _cs_result($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_cust_list_t($t);

		if (empty($arr["request"]["MAX_FILE_SIZE"]))
		{
			$results = new object_list();
		}
		else
		{
			$filt = array(
				"class_id" => CL_CRM_COMPANY,
				"name" => "%".$arr["request"]["cs_name"]."%",
				"reg_nr" => "%".$arr["request"]["cs_reg_nr"]."%",
			);
			if ($arr["request"]["cs_firmajuht"] != "")
			{
				$filt["CL_CRM_COMPANY.firmajuht.name"] = "%".$arr["request"]["cs_firmajuht"]."%";
			}
			if ($arr["request"]["cs_contact"] != "")
			{
				$filt["CL_CRM_COMPANY.contact.name"] = "%".$arr["request"]["cs_contact"]."%";
			}
			if ($arr["request"]["cs_phone"] != "")
			{
				$filt["CL_CRM_COMPANY.phone.name"] = "%".$arr["request"]["cs_phone"]."%";
			}
			$results = new object_list($filt);
		}

		foreach($results->arr() as $cust)
		{
			$t->define_data(array(
				"name" => html::get_change_url($cust->id(), array("return_url" => urlencode(aw_global_get("REQUEST_URI"))), $cust->name()),
				"address" => $cust->prop_str("contact"),
				"phone" => $cust->prop_str("phone_id"),
				"email" => $cust->prop_str("email_id"),
				"oid" => $cust->id(),
				"priority" => $cust->prop("priority")
		));
		}
	}
}

?>
