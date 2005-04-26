<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_workspace.aw,v 1.110 2005/04/26 14:21:26 duke Exp $
// mrp_workspace.aw - Ressursihalduskeskkond
/*

@classinfo syslog_type=ST_MRP_WORKSPACE relationmgr=yes no_status=1 confirm_save_data=1

@groupinfo grp_customers caption="Kliendid" submit=no
@groupinfo grp_projects caption="Projektid"
@groupinfo grp_search caption="Otsing"
	@groupinfo grp_search_proj caption="Otsi projekte" submit_method=get parent=grp_search
	@groupinfo grp_search_cust caption="Otsi kliente" submit_method=get parent=grp_search

@groupinfo grp_schedule caption="Kalender" submit=no
@groupinfo grp_printer caption="Operaatori vaade" submit=no
	@groupinfo grp_printer_current caption="Jooksvad t&ouml;&ouml;d" parent=grp_printer submit=no
	groupinfo grp_printer_old caption="Tegemata t&ouml;&ouml;d" parent=grp_printer submit=no
	@groupinfo grp_printer_done caption="Tehtud t&ouml;&ouml;d" parent=grp_printer submit=no
	@groupinfo grp_printer_aborted caption="Katkestatud t&ouml;&ouml;d" parent=grp_printer submit=no

	@groupinfo grp_printer_in_progress caption="K&otilde;ik t&ouml;&ouml;s olevad" parent=grp_printer submit=no
	@groupinfo grp_printer_startable caption="K&otilde;ik t&ouml;&ouml;d mida oleks v&otilde;imalik alustada" parent=grp_printer submit=no
	@groupinfo grp_printer_notstartable caption="T&ouml;&ouml;d, mida ei ole veel v&otilde;imalik alustada" parent=grp_printer submit=no


@groupinfo grp_settings caption="Seaded"
	@groupinfo grp_settings_def caption="Seaded" parent=grp_settings
	@groupinfo grp_settings_salesman caption="M&uuml;&uuml;gimehe seaded" parent=grp_settings
	@groupinfo grp_users_tree caption="Kasutajate puu" parent=grp_settings submit=no
	@groupinfo grp_users_mgr caption="Kasutajate rollid" parent=grp_settings submit=no
	@groupinfo grp_resources caption="Ressursside haldus" parent=grp_settings

@groupinfo grp_login_select_res caption="Vali kasutatav ressurss"


@default table=objects
@default field=meta
@default method=serialize


	@property rescheduling_needed type=hidden


@default group=general
	@property test type=text store=no no_caption=1

@default group=grp_customers
	@property box type=text no_caption=1 store=no group=grp_customers,grp_projects,grp_resources,grp_users_tree,grp_users_mgr
	@layout vsplitbox type=hbox group=grp_customers,grp_projects,grp_resources,grp_users_tree,grp_users_mgr
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

	@property chart_legend type=text store=no
	@caption Legend

	@property chart_project_hilight_gotostart type=checkbox store=no
	@caption Mine valitud projekti algusesse

	@property chart_search type=text store=no
	@caption Otsi

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

	@property max_subcontractor_timediff type=textbox default=1
	@comment Erinevus allhankijaga kokkulepitud aja ning planeeritud algusaja vahel, mis on lubatud hilinemise/ettejõudmise piires.
	@caption Allhanke suurim ajanihe (h)

	// @property default_global_buffer type=textbox default=4
	// @comment Uutele loodavatele ressurssidele vaikimisi pandav päeva üldpuhver.
	// @caption Vaikimisi üldpuhver (h)



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

	@property parameter_start_priority type=textbox default=1
	@comment Positiivne reaalarv või 0 kui algusaega ei taheta parima valimisel arvestada. Kasutatakse mitut paralleelset tööd võimaldavate ressursside juures tööle kalendrist parima koha valikul. Koha kaal arvutatakse valemiga: (AlgusajaKaal X ParalleelharuVabaAjaAlgus + PikkuseKaal X ParalleelharuVabaAjaPikkus)/2
	@caption Töö algusaja kaal

	@property parameter_length_priority type=textbox default=1
	@comment Vt. töö algusaja kaalu selgitust.
	@caption Töö pikkuse kaal

	@property parameter_timescale type=textarea
	@caption Otsingutabeli ajaskaala definitsioon (Jaotuste algused, komaga eraldatud. Esimene peaks alati 0 olema.)
	@property parameter_timescale_unit type=select
	@caption Skaala ajaühik

@default group=grp_printer_current,grp_printer_done,grp_printer_aborted,grp_printer_in_progress,grp_printer_startable,grp_printer_notstartable

	@property printer_legend type=text
	@caption Legend

	@property printer_jobs type=table no_caption=1

	@property pj_toolbar type=toolbar store=no no_caption=1
	@caption Muuda staatust

	property pj_project type=text store=no
	caption Projekt

	// these are shown when a job is selected
	@property pj_case_header type=text no_caption=1 store=no

	@property pj_errors type=text store=no
	@caption Vead

	@layout comment_hbox type=hbox width=40%:60%
	@caption Kommentaar

	@property pj_change_comment type=textarea rows=5 cols=50 store=no parent=comment_hbox no_caption=1
	@caption Kommentaar

	@property pj_change_comment_history type=text store=no parent=comment_hbox no_caption=1

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

@default group=grp_login_select_res

	@property select_session_resource type=select store=no
	@caption Vali kasutatav ressurss

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


class mrp_workspace extends class_base
{
	var $pj_colors = array(
		"done" => "#BADBAD",
		"can_start" => "#eff6d5",
		"can_not_start" => "#ffe1e1",
		"resource_in_use" => "#ecd995",
	);

	function mrp_workspace()
	{
		$this->resource_states = array(
			0 => "M&auml;&auml;ramata",
			MRP_STATUS_RESOURCE_AVAILABLE => t("Vaba"),
			MRP_STATUS_RESOURCE_INUSE => t("Kasutusel"),
			MRP_STATUS_RESOURCE_OUTOFSERVICE => t("Suletud"),
		);

		$this->states = array (
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
			"tpldir" => "mrp/mrp_workspace",
			"clid" => CL_MRP_WORKSPACE
		));

		$this->import = get_instance(CL_MRP_PRISMA_IMPORT);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

/* dbg */ //finish all jobs in progress and set_all_resources_available
// if ($_GET["mrp_set_all_resources_available"])
// {
		// $resources_folder = $this_object->prop ("resources_folder");
		// $resource_tree = new object_tree(array(
			// "parent" => $resources_folder,
			// "class_id" => array(CL_MENU, CL_MRP_RESOURCE),
			// "sort_by" => "objects.jrk",
		// ));
	// $list = new object_list (array (
		// "class_id" => CL_MRP_JOB,
		// "state" => MRP_STATUS_INPROGRESS,
	// ));

	// $jj = $list->arr();
	// $j = get_instance(CL_MRP_JOB);

	// foreach ($jj as $job_id => $job)
	// {
		// echo "job id: " . $job->id() ."<br>";
		// $arr = array("id"=>$job_id);
		// $ud = parse_url($j->done($arr));
		// $pars = array();
		// parse_str($ud["query"], $pars);
		// $this->dequote($pars["errors"]);
		// $errs = unserialize($pars["errors"]);
		// echo "done: [" . implode(",", $errs) . "]<br><br>";
	// }

	// $list = $resource_tree->to_list();
	// $list->filter (array (
		// "class_id" => CL_MRP_RESOURCE,
	// ));
	// $list = $list->arr();

	// foreach ($list as $res_id => $r)
	// {
		// echo "res id: " . $res_id ."<br>";
		// $r->set_prop("state", MRP_STATUS_RESOURCE_AVAILABLE);
		// $r->save();
		// echo "state set to: [" . MRP_STATUS_RESOURCE_AVAILABLE . "]<br><br>";
	// }
// }
// /* dbg */

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
				$rpn = substr($prop["name"], 4);
				$prop["value"] = $proj->prop($rpn);

				$retv = $this->import->get_prop_value($prop, $rpn);
				if ($retv != PROP_OK)
				{
					return $retv;
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
					case "errors":
						$errs = aw_global_get("mrpws_err");
						if (is_array($errs) && count($errs))
						{
							$prop["value"] = "<span style='color: #FF0000; font-size: 20px'>".join("<br>", $errs)."</span>";
							aw_session_del("mrpws_err");
							return PROP_OK;
						}
						return PROP_IGNORE;
						break;

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
						$tmp = obj($job->prop($rpn));
						if ($this->can("edit", $tmp->id()))
						{
						$prop["value"] = html::href(array(
							"url" => $this->mk_my_orb("change", array(
								"id" => $tmp->id(),
								"return_url" => get_ru()
							)),
							"caption" => "<span style='font-size:20px'>" . $tmp->name() . "</span>"
						));
						}
						else
						{
							$prop["value"] = $tmp->name();
						}
						break;

					case "resource":
						$tmp = obj($job->prop($rpn));
						if ($this->can("edit", $tmp->id()))
						{
						$prop["value"] = html::get_change_url(
							$tmp->id(),
							array(
								"return_url" => urlencode(aw_global_get("REQUEST_URI"))
							),
							$tmp->name()
						);
						}
						else
						{
							$prop["value"] = $tmp->name();
						}
						break;

					case "state":
						$j = get_instance(CL_MRP_JOB);
						$prop["value"] = "<span style='padding: 5px; background: ".$this->state_colours[$job->prop($rpn)]."'>".$j->states[$job->prop($rpn)]."<span>";
						break;

					case "case_header":
						$c_o = obj($job->prop("project"));
						$c_i = $c_o->instance();
						$prop["value"] = $c_i->get_header(array("obj_inst" => $c_o));
						break;

					case "change_comment_history":
						$txt = array();
						$cnt = 0;
						foreach(safe_array($job->meta("change_comment_history")) as $comment_hist_item)
						{
							$txt[] = date("d.m.Y H:i", $comment_hist_item["tm"]).": ".$comment_hist_item["text"]." (".$comment_hist_item["uid"].")";
							if ($cnt++ > 4)
							{
								break;
							}
						}
						$prop["value"] = join("<br>", $txt);
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
				switch ($arr["request"]["mrp_tree_active_item"])
				{
					case "subcontracts":
						### update schedule
						$schedule = get_instance (CL_MRP_SCHEDULE);
						$schedule->create (array("mrp_workspace" => $this_object->id()));

						$this->create_subcontract_jobs_list ($arr);
						break;

					case "aborted_jobs":
						$this->create_aborted_jobs_list ($arr);
						break;

					default:
						### update schedule
						$schedule = get_instance (CL_MRP_SCHEDULE);
						$schedule->create (array("mrp_workspace" => $this_object->id()));

						$this->create_projects_list ($arr);
						break;
				}
				break;

			case "legend":
				$prop["value"] = '<div style="display: block; margin: 4px;"><span style="width: 25px; height: 15px; margin-right: 5px; background-color: ' . MRP_COLOUR_PLANNED_OVERDUE . '; border: 1px solid black;">&nbsp;&nbsp;&nbsp;</span> '.t("&Uuml;le t&auml;htaja planeeritud").'</div>';
				$prop["value"] .= '<div style="display: block; margin: 4px;"><span style="width: 25px; height: 15px; margin-right: 5px; background-color: ' . MRP_COLOUR_OVERDUE . '; border: 1px solid black;">&nbsp;&nbsp;&nbsp;</span> '.t("&Uuml;le t&auml;htaja l&auml;inud").'</div>';
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
				### update schedule
				$schedule = get_instance (CL_MRP_SCHEDULE);
				$schedule->create (array("mrp_workspace" => $this_object->id()));

				$prop["value"] = $this->create_schedule_chart ($arr);
				break;

			case "chart_navigation":
				$prop["value"] = $this->create_chart_navigation ($arr);
				break;

			case "chart_legend":
				$prop["value"] = $this->draw_colour_legend ();
				break;

			case "chart_start_date":
				$prop["value"] = empty ($arr["request"]["mrp_chart_start"]) ? $this->get_week_start () : $arr["request"]["mrp_chart_start"];
				break;

			// case "chart_project_hilight":
				// if (is_oid ($arr["request"]["mrp_hilight"]))
				// {
					// $options = array ();
					// $prop["value"] = $arr["request"]["mrp_hilight"];
				// }
				// else
				// {
					// $options = array ("0" => " ");
				// }

				// $applicable_states = array (
					// MRP_STATUS_PLANNED,
					// MRP_STATUS_DONE,
					// MRP_STATUS_ARCHIVED,
					// MRP_STATUS_INPROGRESS,
				// );

				// $list = new object_list (array (
					// "class_id" => CL_MRP_CASE,
					// "state" => $applicable_states,
					// "parent" => $this_object->prop ("projects_folder"),
				// ));

				// for ($project =& $list->begin (); !$list->end (); $project =& $list->next ())
				// {
					// $options[$project->id ()] = $project->name ();
				// }

				// $prop["options"] = $options;
				// break;

			case "replan":
				$plan_url = $this->mk_my_orb("create", array(
					"return_url" => urlencode(aw_global_get('REQUEST_URI')),
					"mrp_workspace" => $this_object->id (),
					"mrp_force_replan" => 1,
				), "mrp_schedule");
				$plan_href = html::href(array(
					"caption" => t("[Planeeri]"),
					"url" => $plan_url,
					)
				);
				$prop["value"] = $plan_href;
				break;

			### settings tab
			case "parameter_timescale_unit":
				$prop["options"] = array (
					"86400" => t("Päev"),
					"60" => t("Minut"),
					"1" => t("Sekund"),
				);
				break;

			case "max_subcontractor_timediff":
				$prop["value"] = round (($prop["value"] / 3600), 2);
				break;

			### users tab
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

				### update schedule
				$schedule = get_instance (CL_MRP_SCHEDULE);
				$schedule->create (array("mrp_workspace" => $this_object->id()));

				$this->_printer_jobs($arr);
				break;

			case "printer_legend":
				if ($arr["request"]["pj_job"])
				{
					return PROP_IGNORE;
				}
				$prop["value"]  = "<span style='padding: 5px; background: ".$this->pj_colors["done"]."'>".t("Valmis")."</span>&nbsp;&nbsp;";
				$prop["value"] .= "<span style='padding: 5px; background: ".$this->pj_colors["can_start"]."'>".t("V&otilde;ib alustada")."</span>&nbsp;&nbsp;";
				$prop["value"] .= "<span style='padding: 5px; background: ".$this->pj_colors["can_not_start"]."'>".t("Ei saa alustada/t&ouml;&ouml;s")."</span>&nbsp;&nbsp;";
				$prop["value"] .= "<span style='padding: 5px; background: ".$this->pj_colors["resource_in_use"]."'>".t("Eeldust&ouml;&ouml; tehtud")."</span>&nbsp;&nbsp;";
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
					MRP_STATUS_DONE => t("Tehtud"),
					MRP_STATUS_ABORTED => t("Katkestatud"),
					MRP_STATUS_PLANNED => t("Avatud")
				);
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "select_session_resource":
				$resids = $this->get_cur_printer_resources(array(
					"ws" => $arr["obj_inst"],
					"ign_glob" => true
				));
				$ol = new object_list(array("oid" => $resids));

				$prop["options"] = array("" => "") + $ol->names();
				$prop["value"] = aw_global_get("mrp_operator_use_resource");
				break;

			case "chart_search":
				$this->_chart_search($arr);
				break;
		}
		return $retval;
	}

	function set_property ($arr = array ())
	{
		$this_object =& $arr["obj_inst"];
		$prop =& $arr["prop"];
		$retval = PROP_OK;

		if ( (substr($prop["name"], 0, 9) == "parameter") and ($this_object->prop ($prop["name"]) != $prop["value"]) )
		{
			### post rescheduling msg
			$this_object->set_prop("rescheduling_needed", 1);
		}

		switch ($prop["name"])
		{
			case "projects_list":
				$retval = $this->save_custom_form_data ($arr);
				$applicable_lists = array (
					"inwork",
					"planned_overdue",
					"overdue",
					"planned",
					"subcontracts",
					"aborted_jobs"
				);

				if ( in_array ($arr["request"]["mrp_tree_active_item"], $applicable_lists) or empty ($arr["request"]["mrp_tree_active_item"]) )
				{
					$this_object->set_prop("rescheduling_needed", 1);
				}
				break;

			case "resources_list":
				$retval = $this->save_custom_form_data ($arr);
				break;

			case "max_subcontractor_timediff":
				$prop["value"] = round ($prop["value"] * 3600);
				break;

			case "select_session_resource":
				if ($prop["value"])
				{
					aw_session_set("mrp_operator_use_resource", $prop["value"]);
				}
				else
				{
					aw_session_del("mrp_operator_use_resource");
				}
				// aaaand redirect
				header("Location: ".$this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => "grp_printer_current")));
				die();
				break;
		}

		return $retval;
	}

	function callback_mod_retval ($arr)
	{
		$arr["args"]["mrp_tree_active_item"] = $arr["request"]["mrp_tree_active_item"];

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
			$ol = new object_list(array(
				"class_id" => CL_MRP_CASE,
				"name" => $arr["request"]["chart_project_hilight"]
			));
			if ($ol->count())
			{
				$tmp = $ol->begin();
				$arr["args"]["mrp_hilight"] = $tmp->id();
			}
			$arr["args"]["chart_project_hilight"] = $arr["request"]["chart_project_hilight"];
		}

		if ($arr["request"]["chart_customer"])
		{
			$arr["args"]["chart_customer"] = $arr["request"]["chart_customer"];
		}

		### gantt chart start move to project start
		if ($arr["request"]["chart_project_hilight_gotostart"])
		{
			$project_id = false;

			if (is_oid ($arr["args"]["mrp_hilight"]))
			{
				$project_id = $arr["args"]["mrp_hilight"];
			}

			if ($project_id)
			{
				$project = obj ($project_id);

				switch ($project->prop ("state"))
				{
					case MRP_STATUS_PLANNED:
						$starttime_prop = "starttime";
						break;
					case MRP_STATUS_INPROGRESS:
					case MRP_STATUS_DONE:
					case MRP_STATUS_ARCHIVED:
						$starttime_prop = "started";
						break;
				}

				$this_object = obj($arr["args"]["id"]);
				$list = new object_list (array (
					"class_id" => CL_MRP_JOB,
					"project" => $project_id,
					"parent" => $this_object->prop ("jobs_folder"),
					"length" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
					"resource" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
					$starttime_prop => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
				));
				$list->sort_by (array(
					"prop" => $starttime_prop,
					"order" => "asc" ,
				));
				$first_job = $list->begin();

				if (is_object ($first_job))
				{
					$project_start = $first_job->prop ($starttime_prop);
					$project_start = mktime (0, 0, 0, date ("m", $project_start), date ("d", $project_start), date("Y", $project_start));
					$arr["args"]["mrp_chart_start"] = $project_start;
				}
			}
		}
	}

	function create_resources_tree ($arr = array())
	{
		$this_object =& $arr["obj_inst"];

		### resource tree
		$resources_folder = $this_object->prop ("resources_folder");
		$resource_tree = new object_tree(array(
			"parent" => $resources_folder,
			"class_id" => array(CL_MENU, CL_MRP_RESOURCE),
			"sort_by" => "objects.jrk",
		));


/* dbg */
if ($_GET['show_thread_data'] == 1)
{
	$list = $resource_tree->to_list();
	$list = $list->arr();

	foreach ($list as $res_id => $r)
	{
		if ($r->class_id() == CL_MRP_RESOURCE)
		{
			### fix
			// $thread_data = $r->prop("thread_data");
			// $available = false;

			// foreach ($thread_data as $key => $value)
			// {
				// if ($value["state"] == MRP_STATUS_RESOURCE_AVAILABLE)
				// {
					// $available = true;
				// }
			// }

			// if ($available)
			// {
				// $r->set_prop("state", MRP_STATUS_RESOURCE_AVAILABLE);
				// $r->save();
			// }
			// else
			// {
				// $r->set_prop("state", MRP_STATUS_RESOURCE_INUSE);
				// $r->save();
			// }

			### show
			echo "res id: " . $res_id ." state:" .$r->prop("state");
			arr($r->prop("thread_data"));
		}
	}
}
/* dbg */

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
			$active_item = obj ($arr["request"]["mrp_tree_active_item"]);

			if ($active_item->class_id () != CL_MENU)
			{
				$parent = $active_item->parent ();
			}
			else
			{
				$parent = $active_item->id ();
			}
		}
		else
		{
			$parent = $this_object->prop ("resources_folder");
		}

		$table->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "operator",
			"caption" => t("Operaator"),
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "order",
			"caption" => t("Jrk."),
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
			$operators = array();

			foreach(safe_array($res2p[$resource->id()]) as $person)
			{
				$operators[] = html::get_change_url(
					$person->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					$person->name()
				);
			}

			$table->define_data (array (
				"name" => html::get_change_url(
					$resource->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					$resource->name()
				),
				"order" => $resource->ord (),
				"operator" => join(",",$operators),
				"status" => $this->resource_states[$resource->prop("state")],
				"resource_id" => $resource->id(),
			));
		}
	}

	function create_resources_toolbar ($arr = array())
	{
		$toolbar =& $arr["prop"]["toolbar"];
		$this_object =& $arr["obj_inst"];

		if (is_oid ($arr["request"]["mrp_tree_active_item"]))
		{
			$active_item = obj ($arr["request"]["mrp_tree_active_item"]);

			if ($active_item->class_id () != CL_MENU)
			{
				$parent = $active_item->parent ();
			}
			else
			{
				$parent = $active_item->id ();
			}
		}
		else
		{
			$parent = $this_object->prop ("resources_folder");
		}

		$add_resource_url = $this->mk_my_orb("new", array(
			"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			"mrp_workspace" => $this_object->id (),
			"mrp_parent" => $parent,
			"parent" => $parent,
		), "mrp_resource");
		$add_category_url = $this->mk_my_orb("new", array(
			"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			"parent" => $parent,
		), "menu");

		$toolbar->add_menu_button(array(
			"name" => "add",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus"),
		));
		$toolbar->add_menu_item(array(
			"parent" => "add",
			"text" => t("Ressurss"),
			"link" => $add_resource_url,
		));
		$toolbar->add_menu_item(array(
			"parent" => "add",
			"text" => t("Ressurssikategooria"),
			"link" => $add_category_url,
		));

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "cut",
			"tooltip" => t("L&otilde;ika"),
			"action" => "cut_resources",
			"img" => "cut.gif",
		));

		$toolbar->add_button(array(
			"name" => "copy",
			"tooltip" => t("Kopeeri"),
			"action" => "copy_resources",
			"img" => "copy.gif",
		));

		if (count(safe_array($_SESSION["mrp_workspace"]["cut_resources"])) > 0 ||
			count(safe_array($_SESSION["mrp_workspace"]["copied_resources"])) > 0)
		{
			$toolbar->add_button(array(
				"name" => "paste",
				"tooltip" => t("Kleebi"),
				"action" => "paste_resources",
				"img" => "paste.gif",
			));
		};

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta"),
			"action" => "delete",
			"confirm" => t("Kustutada kõik valitud ressursid?"),
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
			"tooltip" => t("Lisa uus projekt"),
			"url" => $add_project_url,
		));
		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud projekt(id)"),
			"confirm" => t("Kustutada kõik valitud projektid?"),
			"action" => "delete",
		));
	}

	function create_projects_tree ($arr = array())
	{
		$this_object =& $arr["obj_inst"];
		$projects_folder = $this_object->prop ("projects_folder");

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
			"state" => new obj_predicate_not (array (MRP_STATUS_DONE, MRP_STATUS_ARCHIVED)),
			"due_date" => new obj_predicate_compare (OBJ_COMP_LESS, time()),
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_overdue = $list->count ();

		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);
		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"state" => $applicable_states,
			"planned_date" => new obj_predicate_prop (OBJ_COMP_GREATER, "due_date"),
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_planned_overdue = $list->count ();

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

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"state" => MRP_STATUS_ONHOLD,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_onhold = $list->count ();

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"state" => MRP_STATUS_ARCHIVED,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_archived = $list->count();

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_all = $list->count();

		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"state" => MRP_STATUS_ABORTED,
			"parent" => $this_object->prop ("jobs_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_jobs_aborted = $list->count ();

		$list = $this->_get_subcontract_job_list($this_object);
		$count_jobs_subcontracted = $list->count();

		$tree = get_instance("vcl/treeview");
		$tree->start_tree (array (
				"type" => TREE_DHTML,
				"tree_id" => "resourcetree",
				"persist_state" => 1,
		));

		$tree->add_item (0, array (
			"name" => t("Plaanisolevad") . " (" . $count_projects_planned . ")",
			"id" => "planned",
			"url" => aw_url_change_var ("mrp_tree_active_item", "planned"),
		));

		$tree->add_item (0, array (
			"name" => t("Hetkel töös") . " (" . $count_projects_in_work . ")",
			"id" => "inwork",
			"url" => aw_url_change_var ("mrp_tree_active_item", "inwork"),
		));

		$tree->add_item (0, array (
			"name" => t("Planeeritud üle tähtaja") . " (" . $count_projects_planned_overdue . ")",
			"id" => "planned_overdue",
			"url" => aw_url_change_var ("mrp_tree_active_item", "planned_overdue"),
		));

		$tree->add_item (0, array (
			"name" => t("Üle tähtaja") . " (" . $count_projects_overdue . ")",
			"id" => "overdue",
			"url" => aw_url_change_var ("mrp_tree_active_item", "overdue"),
		));

		$tree->add_item (0, array (
			"name" => t("Uued") . " (" . $count_projects_new . ")",
			"id" => "new",
			"url" => aw_url_change_var ("mrp_tree_active_item", "new"),
		));

		$tree->add_item (0, array (
			"name" => t("Katkestatud") . " (" . $count_projects_aborted . ")",
			"id" => "aborted",
			"url" => aw_url_change_var ("mrp_tree_active_item", "aborted"),
		));

		$tree->add_item (0, array (
			"name" => t("Plaanist väljas") . " (" . $count_projects_onhold . ")",
			"id" => "onhold",
			"url" => aw_url_change_var ("mrp_tree_active_item", "onhold"),
		));

		$tree->add_item (0, array (
			"name" => t("Valmis") . " (" . $count_projects_done . ")",
			"id" => "done",
			"url" => aw_url_change_var ("mrp_tree_active_item", "done"),
		));

		$tree->add_item (0, array (
			"name" => t("Arhiveeritud") . " (" . $count_projects_archived . ")",
			"id" => "archived",
			"url" => aw_url_change_var ("mrp_tree_active_item", "archived"),
		));

		$tree->add_item (0, array (
			"name" => t("Kõik projektid") . " (" . $count_projects_all . ")",
			"id" => "all",
			"url" => aw_url_change_var ("mrp_tree_active_item", "all"),
		));

		$tree->add_item (0, array (
			"name" => t("Allhanket&ouml;&ouml;d") . " (" . $count_jobs_subcontracted . ")",
			"id" => "subcontracts",
			"url" => aw_url_change_var ("mrp_tree_active_item", "subcontracts"),
		));

		$tree->add_item (0, array (
			"name" => t("Katkestatud t&ouml;&ouml;d") . " (" . $count_jobs_aborted . ")",
			"id" => "aborted_jobs",
			"url" => aw_url_change_var ("mrp_tree_active_item", "aborted_jobs"),
		));

		$active_node = empty ($arr["request"]["mrp_tree_active_item"]) ? "planned" : $arr["request"]["mrp_tree_active_item"];
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
			$list_request = $arr["request"]["mrp_tree_active_item"] ? $arr["request"]["mrp_tree_active_item"] : "planned";
		}

		$table->define_field (array (
			"name" => "customer",
			"caption" => t("Klient"),
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "name",
			"caption" => t("Pro&shy;jekt"),
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
			"numeric" => 1
		));

		switch ($list_request)
		{
			case "inwork":
			case "planned_overdue":
			case "overdue":
			case "new":
			case "planned":
			case "aborted":
			case "onhold":
				$table->define_field (array (
					"name" => "starttime",
					"caption" => t("Materjalide saabumine"),
					"chgbgcolor" => "bgcolour_overdue",
					"type" => "time",
					"format" => MRP_DATE_FORMAT,
					"sortable" => 1,
				));
				$table->define_field(array(
					"name" => "planned_date",
					"caption" => t("Planeeritud valmimine"),
					"chgbgcolor" => "bgcolour_overdue",
					"type" => "time",
					"format" => MRP_DATE_FORMAT,
					"sortable" => 1,
				));
				$table->define_field(array(
					"name" => "due_date",
					"caption" => t("Tähtaeg"),
					"type" => "time",
					"format" => MRP_DATE_FORMAT,
					"chgbgcolor" => "bgcolour_overdue",
					"sortable" => 1,
				));
				$table->define_field(array(
					"name" => "priority",
					"chgbgcolor" => "bgcolour_overdue",
					"caption" => t("Prio&shy;ri&shy;teet"),
					"callback" => array (&$this, "priority_field_callback"),
					"callb_pass_row" => false,
					"sortable" => 1,
				));
				break;

			case "all":
			case "done":
			case "archived":
				$table->define_field (array (
					"name" => "priority",
					"chgbgcolor" => "bgcolour_overdue",
					"caption" => t("Prio&shy;ri&shy;teet"),
					"sortable" => 1,
				));
				break;
		}

		$table->define_field(array(
			"name" => "sales_priority",
			"caption" => t("Müü&shy;gi prio&shy;ri&shy;teet"),
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));

		if ($list_request != "search")
		{
			$table->define_chooser(array(
				"name" => "selection",
				"field" => "project_id",
			));
		}

		$table->set_default_sortby ("due_date");
		$table->set_default_sorder ("asc");
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
				$applicable_states = array (
					MRP_STATUS_INPROGRESS,
					MRP_STATUS_PLANNED,
				);
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => $applicable_states,
					"planned_date" => new obj_predicate_prop (OBJ_COMP_GREATER, "due_date"),
					"parent" => $this_object->prop ("projects_folder"),
					// "createdby" => aw_global_get('uid'),
				));
				break;

			case "overdue":
				$applicable_states = array (
					MRP_STATUS_INPROGRESS,
					MRP_STATUS_PLANNED,
				);
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"due_date" => new obj_predicate_compare (OBJ_COMP_LESS, time()),
					"state" => $applicable_states,
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

			case "archived":
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => MRP_STATUS_ARCHIVED,
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

			case "onhold":
				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => MRP_STATUS_ONHOLD,
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
				"group" => "grp_case_workflow",
			), "mrp_case");

			### get planned project finishing date
			$planned_date = $project->prop ("planned_date");

			if (!$planned_date)
			{
				$connections = $project->connections_from (array ("type" => "RELTYPE_MRP_PROJECT_JOB", "class_id" => CL_MRP_JOB));
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
				case "planned_overdue":
				case "overdue":
				case "new":
					break;

				case "inwork":
				case "planned":
					### hilight for planned overdue
					$bg_colour = ($project->prop ("due_date") < $planned_date) ? MRP_COLOUR_PLANNED_OVERDUE : false;

					### hilight for overdue
					$bg_colour = ($project->prop ("due_date") <= time ()) ? MRP_COLOUR_OVERDUE : $bg_colour;
					break;

				case "all":
				case "done":
				case "archived":
					break;
			}

			### define data for html table row
			$definition = array (
				"name" => html::href (array (
					"caption" => $project->name(),
					"url" => $change_url,
					)
				),
				"customer" => (is_object($customer) ? $customer->name () : ""),
				"priority" => $priority,
				"sales_priority" => $project->prop ("sales_priority"),
				"starttime" => $project->prop ("starttime"),
				"due_date" => $project->prop ("due_date"),
				"planned_date" => $planned_date,
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

	function create_subcontract_jobs_list ($arr = array ())
	{
		$table =& $arr["prop"]["vcl_inst"];
		$this_object =& $arr["obj_inst"];

		$table->define_field (array (
			"name" => "customer",
			"caption" => t("Klient"),
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "project",
			"caption" => t("Projekt"),
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "resource",
			"caption" => t("Ressurss"),
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "scheduled_date",
			"type" => "time",
			"format" => MRP_DATE_FORMAT,
			"numeric" => 1,
			"caption" => t("Planeeritud algusaeg"),
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "advisedstart",
			"caption" => t("Soovitav algusaeg"),
		));
		$table->define_field(array(
			"name" => "modify",
			"chgbgcolor" => "bgcolour_overdue",
			"caption" => t("Ava"),
		));

		$table->set_default_sortby ("scheduled_date");
		$table->set_default_sorder ("asc");
		$table->draw_text_pageselector (array (
			"records_per_page" => 50,
		));

		$jobs_l = $this->_get_subcontract_job_list($this_object);
		$jobs = $jobs_l->arr();

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

			### get project customer
			$customer = $project->get_first_obj_by_reltype("RELTYPE_MRP_CUSTOMER");

			### hilight for planned overdue
			if ( ($this_object->prop ("advised_starttime") > time() ) and (abs($job->prop ("starttime") - $job->prop ("advised_starttime")) > $this_object->prop ("max_subcontractor_timediff")) )
			{
				$bg_colour = MRP_COLOUR_PLANNED_OVERDUE;
			}
			else
			{
				$bg_colour = false;
			}

			### define data for html table row
			$definition = array (
				"customer" => (is_object ($customer) ? $customer->name () : ""),
				"project" => html::get_change_url(
					$project->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					$project->name ()
				),
				"resource" => html::get_change_url(
					$resource->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					$resource->name ()
				),
				"scheduled_date" => $job->prop ("starttime"),
				"modify" => html::get_change_url(
					$job->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					t("Ava")
				),
				"bgcolour_overdue" => $bg_colour,
				"advisedstart" => '<span style="white-space: nowrap;">' . html::datetime_select(array(
					"name" => "mrp_job_advisedstart-" . $job_id,
					"value" => $job->prop ("advised_starttime"),
					"day" => "text",
					"month" => "text",
					"textsize" => "11px",
					)
				) . '</span>',
			);

			if (!$bg_colour)
			{
				unset ($definition["bgcolour_overdue"]);
			}

			$table->define_data($definition);
		}
	}

	function create_aborted_jobs_list ($arr = array ())
	{
		$table =& $arr["prop"]["vcl_inst"];
		$this_object =& $arr["obj_inst"];

		$table->define_field (array (
			"name" => "customer",
			"caption" => t("Klient"),
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "project",
			"caption" => t("Projekt"),
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "resource",
			"caption" => t("Ressurss"),
			"sortable" => 1,
		));
		$table->define_field(array(
			"name" => "due_date",
			"type" => "time",
			"format" => MRP_DATE_FORMAT,
			"numeric" => 1,
			"caption" => t("Projekti tähtaeg"),
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "minstart",
			"caption" => t("Vara&shy;seim jätka&shy;mis&shy;aeg"),
		));
		$table->define_field (array (
			"name" => "reschedule",
			"caption" => t("Tagasta planeerimisse"),
		));
		$table->define_field(array(
			"name" => "modify",
			"caption" => t("Ava"),
		));

		$table->set_default_sortby ("due_date");
		$table->set_default_sorder ("asc");
		$table->draw_text_pageselector (array (
			"records_per_page" => 50,
		));

		$applicable_states = array (
			MRP_STATUS_ABORTED,
		);

		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"state" => $applicable_states,
			"parent" => $this_object->prop ("jobs_folder"),
		));
		$jobs = $list->arr ();

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

			### get project customer
			$customer = $project->get_first_obj_by_reltype("RELTYPE_MRP_CUSTOMER");

			### define data for html table row
			$definition = array (
				"customer" => (is_object ($customer) ? $customer->name () : ""),
				"project" => html::get_change_url(
					$project->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					$project->name ()
				),
				"resource" => html::get_change_url(
					$resource->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					$resource->name ()
				),
				"due_date" => $project->prop ("due_date"),
				"modify" => html::get_change_url(
					$job->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					t("Ava")
				),
				"reschedule" => html::checkbox(array(
					"name" => "mrp_job_reschedule-" . $job_id,
					)
				),
				"minstart" => '<span style="white-space: nowrap;">' . html::datetime_select(array(
					"name" => "mrp_job_minstart-" . $job_id,
					"value" => (($job->prop ("minstart")) ? $job->prop ("minstart") : time()),
					"day" => "text",
					"month" => "text",
					"textsize" => "11px",
					)
				) . '</span>',
			);

			$table->define_data($definition);
		}
	}

	function create_schedule_chart ($arr)
	{
		$time =  time();
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


		$mrp_schedule = get_instance(CL_MRP_SCHEDULE);

		for ($category =& $toplevel_categories->begin (); !$toplevel_categories->end (); $category =& $toplevel_categories->next ())
		{
			$id = $category->id ();
			$chart->add_row (array (
				"name" => $id,
				"title" => $category->name(),
				"type" => "separator",
			));

			$resource_tree = new object_tree(array(
				"parent" => $id,
				"class_id" => array (CL_MRP_RESOURCE),
				"sort_by" => "objects.jrk",
			));
			$resources = $resource_tree->to_list();

			for ($resource =& $resources->begin (); !$resources->end (); $resource =& $resources->next ())
			{
				$chart->add_row (array (
					"name" => $resource->id(),
					"title" => $resource->name(),
					"uri" => html::get_change_url(
						$resource->id(),
						array("return_url" => urlencode(aw_global_get("REQUEST_URI")))
					)
				));

				if (!$arr["request"]["chart_customer"])
				{
					### add reserved times for resources, cut off past
					$reserved_times = $mrp_schedule->get_unavailable_periods_for_range(array(
						"mrp_resource" => $resource->id(),
						"mrp_start" => $range_start,
						"mrp_length" => $range_end - $range_start
					));
					foreach($reserved_times as $rt_start => $rt_end)
					{
						if ($rt_end > $time)
						{
							$rt_start = ($rt_start < $time) ? $time : $rt_start;
							$chart->add_bar(array(
								"row" => $resource->id(),
								"start" => $rt_start,
								"length" => $rt_end - $rt_start,
								"nostartmark" => true,
								"colour" => MRP_COLOUR_PAUSED,
								"url" => "#",
								"title" => sprintf(t("Kinnine aeg %s - %s"), date(MRP_DATE_FORMAT, $rt_start), date(MRP_DATE_FORMAT, $rt_end))
							));
						}
					}
				}
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
		$res = $this->db_fetch_array (
			"SELECT MAX(job.planned_length), MAX(job.finished-job.started) FROM mrp_job as job ".
			"WHERE job.state !=" . MRP_STATUS_DELETED . " AND ".
			"job.length > 0 AND ".
			"job.resource > 0 ".
		"");
		rsort ($res[0]);
		$max_length = reset ($res[0]);

		### job states that are shown in chart past
		$applicable_states = array (
			MRP_STATUS_DONE,
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PAUSED,
		);

		$filt = array (
			"class_id" => CL_MRP_JOB,
			"state" => $applicable_states,
			"parent" => $this_object->prop ("jobs_folder"),
			"started" => new obj_predicate_compare (OBJ_COMP_BETWEEN, ($range_start - $max_length), $range_end),
			"resource" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
			"length" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
		);

		if ($arr["request"]["chart_customer"])
		{
			// filter by customer as well
			$filt["CL_MRP_JOB.RELTYPE_MRP_PROJECT.RELTYPE_MRP_CUSTOMER.name"] = $arr["request"]["chart_customer"];
		}
		$list = new object_list ($filt);
		$jobs = $list->arr ();

		### job states that are shown in chart future
		$applicable_states = array (
			MRP_STATUS_PLANNED,
		);

		$filt = array (
			"class_id" => CL_MRP_JOB,
			"parent" => $this_object->prop ("jobs_folder"),
			"state" => $applicable_states,
			"starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, ($range_start - $max_length), $range_end),
			"starttime" => new obj_predicate_compare (OBJ_COMP_GREATER, time ()),
			"resource" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
			"length" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
		);

		if ($arr["request"]["chart_customer"])
		{
			// filter by customer as well
			$filt["CL_MRP_JOB.RELTYPE_MRP_PROJECT.RELTYPE_MRP_CUSTOMER.name"] = $arr["request"]["chart_customer"];
		}

		$list = new object_list ($filt);
		$jobs = array_merge ($list->arr (), $jobs);

		foreach ($jobs as $job)
		{
			$project = obj ($job->prop ("project"));

			### project states that are shown in chart
			$applicable_states = array (
				MRP_STATUS_PLANNED,
				MRP_STATUS_INPROGRESS,
				MRP_STATUS_DONE,
				MRP_STATUS_ARCHIVED,
			);

			if (!in_array ($project->prop ("state"), $applicable_states))
			{
				continue;
			}

			### get start&length according to job state
			switch ($job->prop ("state"))
			{
				case MRP_STATUS_DONE:
					$start = $job->prop ("started");
					$length = $job->prop ("finished") - $job->prop ("started");
// /* dbg */ echo date(MRP_DATE_FORMAT, $start) . "-" . date(MRP_DATE_FORMAT, $start + $length) . "<br>";
					break;

				case MRP_STATUS_PLANNED:
					$start = $job->prop ("starttime");
					$length = $job->prop ("planned_length");
					break;

				case MRP_STATUS_PAUSED:
				case MRP_STATUS_INPROGRESS:
					$start = $job->prop ("started");
					$length = (($start + $job->prop ("planned_length")) < $time) ? ($time - $start) : $job->prop ("planned_length");
					break;
			}

			$resource = obj ($job->prop ("resource"));
			$job_name = $project->name () . "-" . $job->prop ("exec_order") . " - " . $resource->name ();

			### set bar colour
			$colour = $this->state_colours[$job->prop ("state")];
			$colour = in_array ($job->id (), $hilighted_jobs) ? MRP_COLOUR_HILIGHTED : $colour;

			$bar = array (
				"id" => $job->id (),
				"row" => $resource->id (),
				"start" => $start,
				"colour" => $colour,
				"length" => $length,
				"uri" => aw_url_change_var ("mrp_hilight", $project->id ()),
				"title" => $job_name . " (" . date (MRP_DATE_FORMAT, $start) . " - " . date (MRP_DATE_FORMAT, $start + $length) . ")"
/* dbg */ . " [res:" . $resource->id () . " töö:" . $job->id () . " proj:" . $project->id () . "]"
			);

			$chart->add_bar ($bar);

			### add paused bars
			foreach(safe_array($job->meta("paused_times")) as $pd)
			{
				if ($pd["start"] && $pd["end"])
				{
					$bar = array (
						"row" => $resource->id (),
						"start" => $pd["start"],
						"nostartmark" => true,
						"colour" => $this->state_colours[MRP_STATUS_PAUSED],
						"length" => ($pd["end"] - $pd["start"]),
						"uri" => aw_url_change_var ("mrp_hilight", $project->id ()),
						"title" => $job_name . ", paus (" . date (MRP_DATE_FORMAT, $pd["start"]) . " - " . date (MRP_DATE_FORMAT, $pd["end"]) . ")"
					);

					$chart->add_bar ($bar);
				}
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
			"row_height" => 10,
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
			"caption" => t("<<"),
			"title" => t("5 tagasi"),
			"url" => aw_url_change_var ("mrp_chart_start", ($start - 5*$period_length)),
		));
		$start_nav[] = html::href (array (
			"caption" => t("Eelmine"),
			"url" => aw_url_change_var ("mrp_chart_start", ($start - $period_length)),
		));
		$start_nav[] = html::href (array (
			"caption" => t("Täna"),
			"url" => aw_url_change_var ("mrp_chart_start", $this->get_week_start ()),
		));
		$start_nav[] = html::href (array (
			"caption" => t("Järgmine"),
			"url" => aw_url_change_var ("mrp_chart_start", ($start + $period_length + 1)),
		));
		$start_nav[] = html::href (array (
			"caption" => t(">>"),
			"title" => t("5 edasi"),
			"url" => aw_url_change_var ("mrp_chart_start", ($start + 5*$period_length)),
		));

		$navigation = sprintf(t('&nbsp;&nbsp;Periood: %s &nbsp;&nbsp;Päevi perioodis: %s'), implode (" ", $start_nav) ,implode (" ", $length_nav));

		if (is_oid ($arr["request"]["mrp_hilight"]))
		{
			$project = obj ($arr["request"]["mrp_hilight"]);
			$deselect = html::href (array (
				"caption" => t("Kaota valik"),
				"url" => aw_url_change_var ("mrp_hilight", ""),
			));
			$change_url = html::get_change_url ($project->id(), array("return_url" => urlencode(aw_global_get("REQUEST_URI"))), parse_obj_name($project->name ()));
			$navigation .= t(' &nbsp;&nbsp;Valitud projekt: ') . $change_url . ' (' . $deselect . ')';
		}

		return $navigation;
	}

	function save_custom_form_data ($arr = array ())
	{
		$retval = PROP_OK;

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

				case "mrp_job_minstart":
					$job = obj ($oid);
					$minstart = mktime ($value["hour"], $value["minute"], 0, $value["month"], $value["day"], $value["year"]);
					$job->set_prop ("minstart", $minstart);
					$job->save ();
					break;

				case "mrp_job_advisedstart":
					$job = obj ($oid);
					$advised_starttime = mktime ($value["hour"], $value["minute"], 0, $value["month"], $value["day"], $value["year"]);
					$job->set_prop ("advised_starttime", $advised_starttime);
					$job->save ();
					break;

				case "mrp_job_reschedule":
					if ($value)
					{
						$job = obj ($oid);
						$job->set_prop ("state", MRP_STATUS_PLANNED);
						$job->save ();
					}
					break;

				case "mrp_resource_order":
					$resource = obj ($oid);
					$resource->set_ord ((int) $value);
					$resource->save ();
					break;
			}
		}

		return $retval;
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
				(mrp_job.starttime + mrp_job.length) > mrp_case.due_date
		");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["oid"];
		}
		return $ret;
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
		aw_register_header_text_cb(array(&$this, "make_aw_header"));

		// if no back link is set, make the yah empty
		if (!$_GET["return_url"])
		{
			aw_global_set("hide_yah", true);
		}

		$arr["post_ru"] = post_ru();
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
			"sort_by" => "objects.jrk",
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
				)).html::hidden(array(
					"name" => "old_all_resources[".$c->prop("to")."]",
					"value" => $all_resources[$c->prop("to")]
				)),
				"dept_resources" => html::checkbox(array(
					"name" => "dept_resources[".$c->prop("to")."]",
					"value" => 1,
					"checked" => $dept_resources[$c->prop("to")],
				)).html::hidden(array(
					"name" => "old_dept_resources[".$c->prop("to")."]",
                                        "value" => $dept_resources[$c->prop("to")]
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
		$oldal = safe_array($o->meta("umgr_all_resources"));
		foreach(safe_array($arr["old_all_resources"]) as $k => $v)
		{
			if ($arr["all_resources"] != $v)
			{
				$oldal[$k] = $arr["all_resources"][$k];
			}
		}
		foreach(safe_array($arr["all_resources"]) as $k => $v)
                {
                        if ($arr["all_resources"] != $arr["old_all_resources"])
                        {
                                $oldal[$k] = $arr["all_resources"][$k];
                        }
                }

		$o->set_meta("umgr_all_resources", $oldal);

		$oldal = safe_array($o->meta("umgr_dept_resources"));
		foreach(safe_array($arr["old_dept_resources"]) as $k => $v)
		{
			if ($arr["dept_resources"] != $v)
			{
				$oldal[$k] = $arr["dept_resources"][$v];
			}
		}
		$o->set_meta("umgr_dept_resources", $oldal);
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
			"caption" => t("Nimi"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "phone",
			"caption" => t("Telefon"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "email",
			"caption" => t("E-mail"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "priority",
			"caption" => t("Prioriteet"),
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
			"caption" => t("Number"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "comment",
			"caption" => t("Kommentaar"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "start",
			"caption" => t("Algus"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("T&auml;htaeg"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "planned",
			"caption" => t("Planeeritud"),
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
			"name" => "project",
			"caption" => t("Projekt"),
			"align" => "center",
			"chgbgcolor" => "bgcol",
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this, "pj_project_field_callback"),
			"callb_pass_row" => true
		));

		$t->define_field(array(
			"name" => "customer",
			"caption" => t("Klient"),
			"align" => "center",
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "job_comment",
			"caption" => t("Kommentaar"),
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

		/*$t->define_field(array(
			"name" => "worker",
			"caption" => t("Teostaja"),
			"align" => "center",
			"chgbgcolor" => "bgcol",
			"sortable" => 1
		));*/

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

		$limit = 50;
		switch ($arr["request"]["group"])
		{
			case "grp_printer_done":
				$states = array(MRP_STATUS_DONE);
				$default_sortby = "tm_end";
				break;

			case "grp_printer_aborted":
				$states = array(MRP_STATUS_ABORTED);
				$default_sortby = "tm";
				break;

			case "grp_printer":
			case "grp_printer_current":
				$default_sortby = "tm";
				$states = array(MRP_STATUS_PLANNED,MRP_STATUS_INPROGRESS,MRP_STATUS_LOCKED,MRP_STATUS_PAUSED,MRP_STATUS_ONHOLD);
				break;

			case "grp_printer_in_progress":
				$default_sortby = "tm";
				$states = array(MRP_STATUS_INPROGRESS);
				break;

			case "grp_printer_startable":
				$default_sortby = "tm";
				$states = array(MRP_STATUS_PLANNED,MRP_STATUS_INPROGRESS,MRP_STATUS_LOCKED,MRP_STATUS_PAUSED,MRP_STATUS_ONHOLD);
				$limit = 100;
				break;

			case "grp_printer_notstartable":
				$default_sortby = "tm";
				$states = array(MRP_STATUS_PLANNED,MRP_STATUS_INPROGRESS,MRP_STATUS_LOCKED,MRP_STATUS_PAUSED,MRP_STATUS_ONHOLD);
				$limit = 100;
				break;
		}

		classload("date_calc");
		$jobs = $this->get_next_jobs_for_resources(array(
			"resources" => $res,
			"limit" => $limit,
//			"minstart" => ($arr["request"]["group"] == "grp_printer_current" || $arr["request"]["group"] == "grp_printer" ? get_day_start() : NULL),
//			"maxend" => ($arr["request"]["group"] == "grp_printer_current" || $arr["request"]["group"] == "grp_printer" ? NULL : get_day_start()),
			"states" => $states
		));

		$workers = $this->get_workers_for_resources($res);

		$mrp_job = get_instance(CL_MRP_JOB);
		$mrp_case = get_instance(CL_MRP_CASE);

		$cnt = 0;
		foreach($jobs as $job)
		{
			if (!is_oid($job->prop("project")) || !$this->can("view", $job->prop("project")))
			{
				continue;
			}
			$cnt++;
			$res = obj($job->prop("resource"));
			$proj = obj($job->prop("project"));

			$workers_str = array();
			foreach(safe_array($workers[$res->id()]) as $person)
			{
				if ($this->can("edit", $person->id()))
				{
					$workers_str[] = html::get_change_url($person->id(), array(), $person->name());
				}
				else
				{
					$workers_str[] = $person->name();
				}
			}

			$custo = "";
			$cust = $proj->get_first_obj_by_reltype("RELTYPE_MRP_CUSTOMER");
			if (is_object($cust))
			{
				if ($this->can("edit", $cust->id()))
				{
					$custo = html::get_change_url($cust->id(), array(
						"return_url" => urlencode(aw_global_get("REQUEST_URI"))
					),
					$cust->name()
					);
				}
				else
				{
					$custo = $cust->name();
				}
			}

			### set colours
			if ($job->prop("state") == MRP_STATUS_DONE)
			{
				// dark green
				$bgcol = $this->pj_colors["done"];
			}
			else
			if ($mrp_job->can_start(array("job" => $job->id())))
			{
				// light green
				$bgcol = $this->pj_colors["can_start"];
			}
			else
			{
				if ($mrp_job->job_prerequisites_are_done(array("job" => $job->id())))
				{
					$bgcol = $this->pj_colors["resource_in_use"];
				}
				else
				{
					// light red
					$bgcol = $this->pj_colors["can_not_start"];
				}
			}

			if ($arr["request"]["group"] == "grp_printer_startable" && $bgcol != $this->pj_colors["can_start"])
			{
				continue;
			}

			if ($arr["request"]["group"] == "grp_printer_notstartable" && ($bgcol == $this->pj_colors["can_start"] || $cnt > 5))
			{
				continue;
			}
			
			$state = '<span style="color: ' . $this->state_colours[$job->prop ("state")] . ';">' . $this->states[$job->prop ("state")] . '</span>';

			### get length, end and start according to job state
			switch ($arr["request"]["group"])
			{
				case "grp_printer_done":
					$start = $job->prop("started");
					$end = $job->prop("finished");
					$length = $job->prop("finished") - $job->prop("started");
					break;

				case "grp_printer_aborted":
					$start = $job->prop("started");
					$end = "...";//!!! lugeda logist v kuskilt abortimise aeg
					$length = 0;//!!!
					break;

				case "grp_printer":
				case "grp_printer_current":
					$start = $job->prop("starttime");
					$end = $job->prop("starttime") + $job->prop("length");
					$length = $job->prop("length");
					break;
			}


			$len  = sprintf ("%02d", floor($length / 3600)).":";
			$len .= sprintf ("%02d", floor(($length % 3600) / 60));

			$resource_str = $res->name();
			if ($this->can("edit", $res->id()))
			{
				$resource_str = html::get_change_url($res->id(), array(
						"return_url" => urlencode(aw_global_get("REQUEST_URI"))
					),
					$res->name()
				);
			}

			$project_str = $proj->name();

			$comment = $job->comment();
			if (strlen($comment) > 20)
			{
				$comment = html::href(array(
					"url" => "javascript:void(0)",
					"caption" => substr($comment, 0, 20),
					"title" => $comment
				));
			}
			### ...
			$t->define_data(array(
				"tm" => $start,
				"tm_end" => $end,
				"length" => $len,
				"job" => html::href(array(
					"caption" => t("Ava"),
					"url" => aw_url_change_var(array(
						"pj_job" =>  $job->id(),
						"return_url" => urlencode(aw_global_get("REQUEST_URI"))
					)),
				)),
				"resource" => $resource_str,
				"worker" => join(", ",$workers_str),
				"project" => $project_str,
				"project_id" => $proj->id(),
				"proj_pri" => $proj->prop("project_priority"),
				"customer" => $custo,
				"status" => $state,
				"bgcol" => $bgcol,
				"job_comment" => $comment
			));
		}

		$t->set_default_sortby($default_sortby);
		$t->sort_by();
	}

	function get_cur_printer_resources_desc($arr)
	{
		if (aw_global_get("mrp_operator_use_resource"))
		{
			$o = obj(aw_global_get("mrp_operator_use_resource"));
			return $o->name();
		}
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
				return t("K&otilde;ik ressursid");
			}
		}

		$dept_res = $ws->meta("umgr_dept_resources");
		foreach($profs->arr() as $prof)
		{
			if ($dept_res[$prof->id()] == 1)
			{
				return t("Osakonna ressursid");
			}
		}

		return join(", ", $ret);
	}

	function get_cur_printer_resources($arr)
	{
		if (aw_global_get("mrp_operator_use_resource") && !$arr["ign_glob"])
		{
			return array(aw_global_get("mrp_operator_use_resource") => aw_global_get("mrp_operator_use_resource"));
		}

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
			//"starttime" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $arr["minstart"]),
			"sort_by" => "mrp_job.starttime"
		);

		if ($arr["states"])
		{
			$filt["state"] = $arr["states"];
		}

		/*if ($arr["maxend"] > 100 )
		{
			$filt["starttime"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, 100, $arr["maxend"]);
		}*/

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

		$arr["prop"]["toolbar"]->add_button(array(
			"name" => "save_comment",
			"tooltip" => t("Salvesta kommentaar"),
			"action" => "save_pj_comment",
		));
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

		$ud = parse_url($j->start($arr));
		$pars = array();
		parse_str($ud["query"], $pars);
		$this->dequote($pars["errors"]);
		$errs = unserialize($pars["errors"]);

		if (is_array($errs) && count($errs))
		{
			aw_session_set("mrpws_err", $errs);
		}

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer_current",
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

		$ud = parse_url($j->done($arr));
		$pars = array();
		parse_str($ud["query"], $pars);
		$this->dequote($pars["errors"]);
		$errs = unserialize($pars["errors"]);

		if (is_array($errs) && count($errs))
		{
			aw_session_set("mrpws_err", $errs);
		}

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer_current",
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

		$ud = parse_url($j->abort($arr));
		$pars = array();
		parse_str($ud["query"], $pars);
		$this->dequote($pars["errors"]);
		$errs = unserialize($pars["errors"]);

		if (is_array($errs) && count($errs))
		{
			aw_session_set("mrpws_err", $errs);
		}

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer_current",
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

		$ud = parse_url($j->pause($arr));
		$pars = array();
		parse_str($ud["query"], $pars);
		$this->dequote($pars["errors"]);
		$errs = unserialize($pars["errors"]);

		if (is_array($errs) && count($errs))
		{
			aw_session_set("mrpws_err", $errs);
		}

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer_current",
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

		$ud = parse_url($j->scontinue($arr));
		$pars = array();
		parse_str($ud["query"], $pars);
		$this->dequote($pars["errors"]);
		$errs = unserialize($pars["errors"]);

		if (is_array($errs) && count($errs))
		{
			aw_session_set("mrpws_err", $errs);
		}

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer_current",
			"pj_job" => $tmp["pj_job"]
		));
	}

	/**
		@attrib name=acontinue
		@param id required type=int
	**/
	function acontinue ($arr)
	{
		$tmp = $arr;
		$j = get_instance(CL_MRP_JOB);
		$arr["id"] = $arr["pj_job"];

		$ud = parse_url($j->acontinue($arr));
		$pars = array();
		parse_str($ud["query"], $pars);
		$this->dequote($pars["errors"]);
		$errs = unserialize($pars["errors"]);

		if (is_array($errs) && count($errs))
		{
			aw_session_set("mrpws_err", $errs);
		}

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer_current",
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

		$ud = parse_url($j->end_shift($arr));
		$pars = array();
		parse_str($ud["query"], $pars);
		$this->dequote($pars["errors"]);
		$errs = unserialize($pars["errors"]);

		if (is_array($errs) && count($errs))
		{
			aw_session_set("mrpws_err", $errs);
		}

		return $this->mk_my_orb("change", array(
			"id" => $tmp["id"],
			"group" => "grp_printer_current",
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

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "grp_login_select_res")
		{
			return false;
		}
		return true;
	}

	function priority_field_callback ($row)
	{
		$cellcontents = html::textbox (array (
			"name" => "mrp_project_priority-" . $row["project_id"],
			"size" => "2",
			"textsize" => "12px",
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
			))."  | ".$this->get_cur_printer_resources_desc(array("ws" => obj(aw_ini_get("prisma.ws"))))." </span>";

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

	function draw_colour_legend ()
	{
		$dfn = "";
		$rows = "";
		$i = 1;
		$state_colours = $this->state_colours;
		$state_colours["hilighted"] = "#FFE706";
		$states = $this->states;
		$states["hilighted"] = t("Valitud projekt");


		foreach ($state_colours as $state => $colour)
		{
			$name = $states[$state];
			$dfn .= '<td class="awmenuedittabletext" style="background-color: ' . $colour . '; width: 30px;">&nbsp;</td><td class="awmenuedittabletext" style="padding: 0px 15px 0px 6px;">' . $name . '</td>';

			if (!(($i++)%3))
			{
				$rows .= '<tr>' . $dfn . '</tr>';
				$dfn = "";
			}
		}

		$rows .= '<tr>' . $dfn . '</tr>';
		return '<table cellspacing="4" cellpadding="0">' . $rows . '</table>';
	}

	function _get_subcontract_job_list($this_object)
	{
		$resource_tree = new object_tree (array (
			"parent" => $this_object->prop ("resources_folder"),
			"class_id" => array (CL_MENU, CL_MRP_RESOURCE),
			"sort_by" => "objects.jrk",
		));
		$list = $resource_tree->to_list ();
		$list->filter (array (
			"class_id" => CL_MRP_RESOURCE,
			"type" => MRP_RESOURCE_SUBCONTRACTOR,
		));
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
			return $list;
		}
		return new object_list();
	}

	function _chart_search($arr)
	{
		if ($arr["request"]["mrp_hilight"] && !$arr["request"]["chart_project_hilight"])
		{
			$o = obj($arr["request"]["mrp_hilight"]);
			$arr["request"]["chart_project_hilight"] = $o->name();
		}
		$str  = t("Valitud projekt");
		$str .= " ";
		$str .= html::textbox(array(
			"name" => "chart_project_hilight",
			"value" => $arr["request"]["chart_project_hilight"],
			"size" => 6
		));
		$str .= " ";
		$str .= t("Klient");
		$str .= " ";
		$str .= html::textbox(array(
			"name" => "chart_customer",
			"value" => $arr["request"]["chart_customer"]
		));
		$arr["prop"]["value"] = $str;
	}

	/**

		@attrib name=save_pj_comment

	**/
	function save_pj_comment($arr)
	{
		$job = get_instance(CL_MRP_JOB);
		$job->add_comment($arr["pj_job"], $arr["pj_change_comment"]);
		return $arr["post_ru"];
	}

	function pj_project_field_callback($row)
	{
		if ($this->can("edit", $row["project_id"]))
		{
			return html::get_change_url(
				$row["project_id"], 
				array(
					"return_url" => get_ru()
				),
				$row["project"]
			);
		}
		return $row["project"];
	}
}

#set all  PLANNED projects INPROGRESS that have jobs INPROGRESS, DONE, PAUSED or ABORTED
// UPDATE mrp_case, mrp_job, objects SET mrp_case.state=3 WHERE
// mrp_case.oid=mrp_job.oid AND
// mrp_case.oid=objects.oid AND
// objects.status > 0 AND
// objects.parent = this_object->prop(projects_folder) AND
// mrp_case.state=2 AND
// mrp_job.state in (3,5,7,4)
//    lisada parenti kontrollimine

?>
