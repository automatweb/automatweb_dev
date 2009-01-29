<?php
/*

@classinfo syslog_type=ST_MRP_WORKSPACE relationmgr=yes no_status=1 maintainer=voldemar

@groupinfo general caption="Seaded"
	@groupinfo grp_settings_def caption="Seaded" parent=general confirm_save_data=1
	@groupinfo grp_settings_salesman caption="M&uuml;&uuml;gimehe seaded" parent=general
	@groupinfo grp_users_tree caption="Kasutajate puu" parent=general submit=no
	@groupinfo grp_users_mgr caption="Kasutajate rollid" parent=general submit=no
	@groupinfo grp_resources caption="Ressursside haldus" parent=general
	@groupinfo grp_worksheet caption="T&ouml;&ouml;lehed" parent=general submit_method=get

@groupinfo grp_customers caption="Kliendid" submit=no submit_method=get
@groupinfo grp_projects caption="Projektid" confirm_save_data=1
@groupinfo grp_schedule caption="T&ouml;&ouml;voog" submit=no
@groupinfo grp_printer caption="Operaatori vaade" submit=no
	@groupinfo grp_printer_current caption="Jooksvad t&ouml;&ouml;d" parent=grp_printer submit=no
	groupinfo grp_printer_old caption="Tegemata t&ouml;&ouml;d" parent=grp_printer submit=no
	@groupinfo grp_printer_done caption="Tehtud t&ouml;&ouml;d" parent=grp_printer submit=no
	@groupinfo grp_printer_aborted caption="Katkestatud t&ouml;&ouml;d" parent=grp_printer submit=no

	@groupinfo grp_printer_in_progress caption="K&otilde;ik t&ouml;&ouml;s olevad" parent=grp_printer submit=no
	@groupinfo grp_printer_startable caption="K&otilde;ik t&ouml;&ouml;d mida oleks v&otilde;imalik alustada" parent=grp_printer submit=no
	@groupinfo grp_printer_notstartable caption="T&ouml;&ouml;d, mida ei ole veel v&otilde;imalik alustada" parent=grp_printer submit=no

@groupinfo grp_login_select_res caption="Vali kasutatav ressurss"


@default table=objects
@default field=meta
@default method=serialize


	@property rescheduling_needed type=hidden

	// elements main grouper
	@layout vsplitbox type=hbox group=grp_customers,grp_projects,grp_resources,grp_users_tree,grp_users_mgr,grp_settings_def width=20%:80%

@default group=grp_customers
	@property customers_toolbar type=toolbar store=no no_caption=1
	@layout customers_box type=vbox parent=vsplitbox
	@layout customers_tree_box type=vbox closeable=1 area_caption=Kliendid parent=customers_box
	@property customers_tree type=treeview store=no no_caption=1 parent=customers_tree_box
	@property customers_list type=table store=no no_caption=1 parent=vsplitbox
	@property customers_list_proj type=table store=no no_caption=1 parent=vsplitbox
	@property cs_result type=table no_caption=1 parent=vsplitbox

	@layout customers_search_box type=vbox closeable=1 area_caption=Klientide&nbsp;otsing parent=customers_box
		@property cs_name type=textbox view_element=1 parent=customers_search_box store=no
		@caption Nimi

		@property cs_firmajuht type=textbox view_element=1 parent=customers_search_box store=no
		@caption Kontaktisik

		@property cs_contact type=textbox view_element=1 parent=customers_search_box store=no
		@caption Aadress

		@property cs_phone type=textbox view_element=1 parent=customers_search_box store=no
		@caption Telefon

		@property cs_reg_nr type=textbox view_element=1 parent=customers_search_box store=no
		@caption Kood

		@property cs_status type=textbox view_element=1 parent=customers_search_box store=no
		@caption Staatus

		@property cs_submit type=submit value=Otsi view_element=1 parent=customers_search_box store=no
		@caption Otsi


@default group=grp_projects
	@property projects_toolbar type=toolbar store=no no_caption=1
	@layout projects_box type=vbox parent=vsplitbox
	@layout projtree_box type=vbox closeable=1 area_caption=Projektid parent=projects_box
	@property projects_tree type=text store=no no_caption=1 parent=projtree_box
	@property projects_list type=table store=no no_caption=1 parent=vsplitbox
	@property sp_result type=table no_caption=1 parent=vsplitbox

	@layout projects_search_box type=vbox closeable=1 area_caption=Projektide&nbsp;otsing parent=projects_box
		@property sp_name type=textbox view_element=1 parent=projects_search_box store=no
		@caption Number

		@property sp_comment type=textbox view_element=1 parent=projects_search_box store=no
		@caption Nimetus

		@property sp_customer type=textbox view_element=1 parent=projects_search_box store=no
		@caption Klient

		@property sp_starttime type=date_select view_element=1 parent=projects_search_box store=no default=-1
		@caption Alustamisaeg (materjalide saabumine) alates

		@property sp_due_date type=date_select view_element=1 parent=projects_search_box store=no default=-1
		@caption T&auml;htaeg alates

		@property sp_status type=select multiple=1 size=5 view_element=1 parent=projects_search_box store=no
		@caption Staatus

		@property sp_submit type=button view_element=1 parent=projects_search_box store=no
		@caption Otsi


@default group=grp_resources
	@property resources_toolbar type=toolbar store=no no_caption=1
	@layout resources_tree_box type=vbox closeable=1 area_caption=Ressursid&amp;kategooriad parent=vsplitbox
	@property resources_tree type=text store=no no_caption=1 parent=resources_tree_box
	@property resources_list type=table store=no parent=vsplitbox no_caption=1


@default group=grp_schedule
	@property master_schedule_chart type=text store=no no_caption=1

	@layout charts_1 type=hbox width=50%:50%

		@layout states_chart type=vbox area_caption=Projektid&nbsp;staatuste&nbsp;kaupa parent=charts_1 closeable=1
		
			@property states_chart type=google_chart no_caption=1 parent=states_chart store=no

		@layout clients_chart type=vbox area_caption=Projektid&nbsp;klientide&nbsp;kaupa parent=charts_1 closeable=1
		
			@property clients_chart type=google_chart no_caption=1 parent=clients_chart store=no

	@layout charts_2 type=hbox width=50%:50%

		@layout deadline_chart type=vbox area_caption=Projektid&nbsp;t&auml;htaja&nbsp;j&auml;rgi parent=charts_2 closeable=1
		
			@property deadline_chart type=google_chart no_caption=1 parent=deadline_chart store=no

	@layout schedule_search_box type=vbox closeable=1 area_caption=Otsing
		@property chart_project_hilight_gotostart type=checkbox store=no parent=schedule_search_box
		@caption Mine valitud projekti algusesse

		@property chart_search type=text store=no parent=schedule_search_box
		@caption Otsi

		@property chart_start_date type=date_select store=no parent=schedule_search_box
		@caption N&auml;idatava perioodi algus

		@property chart_submit type=submit store=no parent=schedule_search_box
		@caption N&auml;ita

@default group=grp_users_tree
	@property user_list_toolbar type=toolbar store=no no_caption=1
	@layout userlist_tree_box type=vbox closeable=1 area_caption=Kasutajad parent=vsplitbox
	@property user_list_tree type=treeview store=no no_caption=1 parent=userlist_tree_box
	@property user_list type=table store=no no_caption=1 parent=vsplitbox

@default group=grp_users_mgr
	@property user_mgr_toolbar type=toolbar store=no no_caption=1
	@layout usermgr_tree_box type=vbox closeable=1 area_caption=Osakonnad parent=vsplitbox
	@property user_mgr_tree type=treeview store=no no_caption=1 parent=usermgr_tree_box
	@property user_mgr type=table store=no no_caption=1 parent=vsplitbox


@default group=grp_settings_def
	//  elements 2-column divider
	@layout vsplitbox2 type=hbox width=50%:50%
	@layout left_column type=vbox closeable=0 area_caption=P&otilde;hiseaded parent=vsplitbox2
	@layout right_column type=vbox closeable=0 area_caption=Planeerija&nbsp;parameetrid parent=vsplitbox2

	@property name type=textbox field=name parent=left_column method=none
	@caption Nimi

	@property owner type=relpicker reltype=RELTYPE_MRP_OWNER clid=CL_CRM_COMPANY parent=left_column
	@caption Organisatsioon

	@property resources_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU parent=left_column
	@caption Ressursside kaust

	@property customers_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU parent=left_column
	@caption Klientide kaust

	@property projects_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU parent=left_column
	@caption Projektide kaust

	@property jobs_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU parent=left_column
	@caption T&ouml;&ouml;de kaust

	@property workspace_configmanager type=relpicker reltype=RELTYPE_MRP_WORKSPACE_CFGMGR clid=CL_CFGMANAGER parent=left_column
	@caption Keskkonna seadetehaldur

	@property case_header_controller type=relpicker reltype=RELTYPE_MRP_HEADER_CONTROLLER parent=left_column
	@caption Projekti headeri kontroller

	@property pv_per_page type=textbox default=30 datatype=int parent=left_column
	@caption Operaatori vaates t&ouml;id lehel

	@property projects_list_objects_perpage type=textbox default=30 datatype=int parent=left_column
	@comment Projektide vaates objekte lehel
	@caption Projekte lehel

	@property max_subcontractor_timediff type=textbox default=1 parent=left_column parent=left_column
	@comment Erinevus allhankijaga kokkulepitud aja ning planeeritud algusaja vahel, mis on lubatud hilinemise/ettej&otilde;udmise piires.
	@caption Allhanke suurim ajanihe (h)

	@property automatic_archiving_period type=textbox parent=left_column datatype=int
	@comment Kui on m&auml;&auml;ratud (nullist suurem) ajavahemik, arhiveeritakse automaatselt projektid, mille valmissaamisest on m&ouml;&ouml;dunud see ajavahemik. Positiivne t&auml;isarv.
	@caption Automaatne arhiveerimine

	@property automatic_archiving_period_unit type=text no_caption=1 parent=left_column store=no

	// @property default_global_buffer type=textbox default=4 parent=left_column
	// @comment Uutele loodavatele ressurssidele vaikimisi pandav p&auml;eva &uuml;ldpuhver.
	// @caption Vaikimisi &uuml;ldpuhver (h)


	// scheduler parameters
	@property parameter_due_date_overdue_slope type=textbox default=0.5 parent=right_column
	@caption &Uuml;le t&auml;htaja olevate projektide t&auml;htsuse t&otilde;us t&auml;htaja &uuml;letamise suurenemise suunas

	@property parameter_due_date_overdue_intercept type=textbox default=10 parent=right_column
	@caption Just t&auml;htaja &uuml;letanud projekti t&auml;htsus

	@property parameter_due_date_decay type=textbox default=0.05 parent=right_column
	@caption Projekti t&auml;htsuse langus t&auml;htaja kaugenemise suunas

	@property parameter_due_date_intercept type=textbox default=0.1 parent=right_column
	@caption Planeerimise hetkega v&otilde;rdse t&auml;htajaga projekti t&auml;htsus

	@property parameter_priority_slope type=textbox default=0.8 parent=right_column
	@caption Kliendi ja projektiprioriteedi suhtelise v&auml;&auml;rtuse t&otilde;us vrd. t&auml;htajaga

	@property parameter_schedule_length type=textbox default=2 parent=right_column
	@caption Ajaplaani ulatus (a)

	@property parameter_min_planning_jobstart type=textbox default=300 parent=right_column
	@caption Ajavahemik planeerimise alguse hetkest milles algavaid t&ouml;id ei planeerita (s)

	@property parameter_schedule_start type=textbox default=300 parent=right_column
	@caption Ajaplaani alguse vahe planeerimise alguse hetkega (s)

	@property parameter_start_priority type=textbox default=1 parent=right_column
	@comment Positiivne reaalarv v&otilde;i 0 kui algusaega ei taheta parima valimisel arvestada. Kasutatakse mitut paralleelset t&ouml;&ouml;d v&otilde;imaldavate ressursside juures t&ouml;&ouml;le kalendrist parima koha valikul. Koha kaal arvutatakse valemiga: (AlgusajaKaal X ParalleelharuVabaAjaAlgus + PikkuseKaal X ParalleelharuVabaAjaPikkus)/2
	@caption T&ouml;&ouml; algusaja kaal

	@property parameter_length_priority type=textbox default=1 parent=right_column
	@comment Vt. t&ouml;&ouml; algusaja kaalu selgitust.
	@caption T&ouml;&ouml; pikkuse kaal

	@property parameter_timescale type=textarea rows=7 cols=30 parent=right_column
	@caption Otsingutabeli ajaskaala definitsioon (Jaotuste algused, komaga eraldatud. Esimene peaks alati 0 olema.)

	@property parameter_timescale_unit type=select parent=right_column
	@caption Skaala aja&uuml;hik


@default group=grp_printer_current,grp_printer_done,grp_printer_aborted,grp_printer_in_progress,grp_printer_startable,grp_printer_notstartable
	@layout printer_legend_box type=vbox no_caption=1 closeable=0
	@property printer_legend type=text no_caption=1 parent=printer_legend_box

	@property printer_jobs_prev_link type=text store=no no_caption=1

	@property printer_jobs type=table no_caption=1

	@property printer_jobs_next_link type=text store=no no_caption=1

	@property pj_toolbar type=toolbar store=no no_caption=1
	@caption Muuda staatust

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

	@property pj_minstart type=datetime_select store=no
	@caption Arvatav j&auml;tkamisaeg

	@property pj_remaining_length type=textbox store=no
	@caption Arvatav l&otilde;petamiseks kuluv aeg (h)

	@property pj_submit type=submit store=no
	@caption Salvesta

	property pj_pre_buffer type=text store=no
	caption Eelpuhveraeg (h)

	property pj_post_buffer type=text store=no
	caption J&auml;relpuhveraeg (h)

	@layout resource_hbox type=hbox width="50%:50%"
	@caption Ressurss

	@property pj_resource type=text store=no parent=resource_hbox no_caption=1
	@caption Ressurss

	@property pj_job_comment type=text store=no parent=resource_hbox
	@caption T&ouml;&ouml; kommentaar

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

	@property pjp_title_case_wf type=text store=no subtitle=1
	@caption Projekti t&ouml;&ouml;voog

	@property pjp_case_wf type=table store=no no_caption=1


@default group=grp_login_select_res
	@property select_session_resource type=select store=no
	@caption Vali kasutatav ressurss


@default group=grp_worksheet
	@property ws_resource type=select multiple=1 store=no size=5
	@caption Ressursid

	@property ws_from type=date_select store=no
	@caption Alates

	@property ws_to type=date_select store=no
	@caption Kuni

	@property ws_sbt type=submit store=no
	@caption N&auml;ita

	@property ws_tbl type=table store=no no_caption=1

// --------------- RELATION TYPES ---------------------

@reltype MRP_FOLDER value=1 clid=CL_MENU
@caption Kaust

@reltype MRP_WORKSPACE_CFGMGR clid=CL_CFGMANAGER value=2
@caption Keskkonna seaded

@reltype MRP_OWNER clid=CL_CRM_COMPANY value=4
@caption Keskkonna omanik (Organisatsioon)

@reltype MRP_HEADER_CONTROLLER clid=CL_FORM_CONTROLLER value=5
@caption Projekti headeri kontroller

*/

require_once "mrp_header.aw";

class mrp_workspace extends class_base
{
	private $project_list_categories = array(
		"inwork" => "",
		"planned_overdue" => "",
		"overdue" => "",
		"new" => "",
		"planned" => "",
		"subcontracts" => "",
		"all" => "",
		"aborted_jobs" => "",
		"archived" => "",
		"aborted" => "",
		"onhold" => "",
		"search" => "",
		"done" => ""
	);

	var $pj_colors = array(
		"done" => "#BADBAD",
		"can_start" => "#eff6d5",
		"can_not_start" => "#ffe1e1",
		"resource_in_use" => "#ecd995",
		"search_result" => "#a255ff"
	);

	var $active_resource_states = array(
		MRP_STATUS_RESOURCE_AVAILABLE,
		MRP_STATUS_RESOURCE_OUTOFSERVICE,
		MRP_STATUS_RESOURCE_INUSE
	);

	function mrp_workspace()
	{
		$this->project_list_categories = array(
			"inwork" => t("Hetkel t&ouml;&ouml;s"),
			"planned_overdue" => t("Planeeritud &uuml;le t&auml;htaja"),
			"overdue" => t("&Uuml;le t&auml;htaja"),
			"new" => t("Uued"),
			"planned" => t("Plaanisolevad"),
			"all" => t("K&otilde;ik projektid"),
			"archived" => t("Arhiveeritud"),
			"subcontracts" => t("Allhanket&ouml;&ouml;d"),
			"aborted" => t("Katkestatud"),
			"aborted_jobs" => t("Katkestatud t&ouml;&ouml;d"),
			"onhold" => t("Plaanist v&auml;ljas"),
			"search" => t("Otsingutulemused"),
			"done" => t("Valmis")
		);

		$this->resource_states = array(
			0 => "M&auml;&auml;ramata",
			MRP_STATUS_RESOURCE_AVAILABLE => t("Vaba"),
			MRP_STATUS_RESOURCE_INUSE => t("Kasutusel"),
			MRP_STATUS_RESOURCE_OUTOFSERVICE => t("Suletud"),
			MRP_STATUS_RESOURCE_INACTIVE => t("Arhiveeritud")
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
			MRP_STATUS_ARCHIVED => t("Arhiveeritud")
		);

		$this->state_colours = array (
			MRP_STATUS_NEW => MRP_COLOUR_NEW,
			MRP_STATUS_PLANNED => MRP_COLOUR_PLANNED,
			MRP_STATUS_INPROGRESS => MRP_COLOUR_INPROGRESS,
			MRP_STATUS_ABORTED => MRP_COLOUR_ABORTED,
			MRP_STATUS_DONE => MRP_COLOUR_DONE,
			MRP_STATUS_PAUSED => MRP_COLOUR_PAUSED,
			MRP_STATUS_ONHOLD => MRP_COLOUR_ONHOLD,
			MRP_STATUS_ARCHIVED => MRP_COLOUR_ARCHIVED
		);

		$this->init(array(
			"tpldir" => "mrp/mrp_workspace",
			"clid" => CL_MRP_WORKSPACE
		));

		$this->import = get_instance(CL_MRP_PRISMA_IMPORT);
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

	function callback_pre_edit ($arr)
	{
		$this_object = $arr["obj_inst"];

		if (!empty($arr["group"]) and $arr["group"] === "grp_projects")
		{
			if (isset($arr["list_request"]))
			{
				$this->list_request = $arr["list_request"];
			}
			elseif (!empty($arr["request"]["sp_search"]))
			{
				$this->list_request = "search";
			}
			else
			{
				$this->list_request = empty($arr["request"]["mrp_tree_active_item"]) ? "planned" : $arr["request"]["mrp_tree_active_item"];
			}

			$list = new object_list (array (
				"class_id" => CL_MRP_CASE,
				"state" => MRP_STATUS_PLANNED,
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
			));

			$this->projects_planned_count = $list->count();

			$list = new object_list (array (
				"class_id" => CL_MRP_CASE,
				"state" => MRP_STATUS_INPROGRESS,
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
			));
			$this->projects_in_work_count = $list->count();

			$applicable_states = array ( // also used below for getting limited lists
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
			$this->projects_overdue_count = $list->count();

			$list = new object_list (array (
				"class_id" => CL_MRP_CASE,
				"state" => $applicable_states,
				"planned_date" => new obj_predicate_prop (OBJ_COMP_GREATER, "due_date"),
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
			));
			$this->projects_planned_overdue_count = $list->count();

			$list = new object_list (array (
				"class_id" => CL_MRP_CASE,
				"state" => MRP_STATUS_NEW,
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
			));
			$this->projects_new_count = $list->count();

			$list = new object_list (array (
				"class_id" => CL_MRP_CASE,
				"state" => MRP_STATUS_DONE,
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
			));
			$this->projects_done_count = $list->count();

			$list = new object_list (array (
				"class_id" => CL_MRP_CASE,
				"state" => MRP_STATUS_ABORTED,
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
			));
			$this->projects_aborted_count = $list->count();

			$list = new object_list (array (
				"class_id" => CL_MRP_CASE,
				"state" => MRP_STATUS_ONHOLD,
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
			));
			$this->projects_onhold_count = $list->count();

/* very slow and gives little useful info. disabled for now.
			$list = new object_list (array (
				"class_id" => CL_MRP_CASE,
				"state" => MRP_STATUS_ARCHIVED,
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
			));
			$this->projects_archived_count = $list->count();
 */
			$list = new object_list (array (
				"class_id" => CL_MRP_CASE,
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
			));
			$this->projects_all_count = $list->count();

			$list = new object_list (array (
				"class_id" => CL_MRP_JOB,
				"state" => MRP_STATUS_ABORTED,
				"parent" => $this_object->prop ("jobs_folder"),
				// "createdby" => aw_global_get('uid'),
			));
			$this->jobs_aborted_count = $list->count();

			$list = $this->_get_subcontract_job_list($this_object);
			$this->jobs_subcontracted_count = $list->count();

			### project list args
			#### limit
			$perpage = $this_object->prop ("projects_list_objects_perpage") ? $this_object->prop ("projects_list_objects_perpage") : 30;
			$limit = ((isset($arr["request"]["ft_page"]) ? (int) $arr["request"]["ft_page"] : 0) * $perpage) . "," . $perpage;

			#### sort
			switch ($this->list_request)
			{
				case "inwork":
					$sort_by = "mrp_case.due_date"; // default sort order
					break;

				case "planned_overdue":
				case "overdue":
				case "new":
				case "planned":
				case "all":
				case "done":
				default:
					$sort_by = "mrp_case.starttime"; // default sort order
					break;
			}

			$sort_order = (isset($arr["request"]["sort_order"]) and "desc" === $arr["request"]["sort_order"]) ? "desc" : "asc";
			$tmp = NULL;

			if (isset($arr["request"]["sortby"]))
			{
				switch ($arr["request"]["sortby"])
				{
					case "starttime":
						$sort_by = "mrp_case.starttime {$sort_order}";
						break;

					case "planned_date":
						$sort_by = "mrp_case_schedule.planned_date {$sort_order}";
						$tmp = new obj_predicate_compare (OBJ_COMP_GREATER, 0);//!!! temporary. acceptable solution needed. projects with planned_date NULL not retrieved.
						break;

					case "due_date":
						$sort_by = "mrp_case.due_date {$sort_order}";
						break;

					case "priority":
						$sort_by = "mrp_case.project_priority {$sort_order}";
						break;
				}
			}

			#### common args
			$args = array(
				"class_id" => CL_MRP_CASE,
				"limit" => $limit,
				"parent" => $this_object->prop ("projects_folder"),
				// "createdby" => aw_global_get('uid'),
				"sort_by" => $sort_by,
				"planned_date" => $tmp,//!!! to enable sorting by planned_date which is in mrp_case_schedule table
			);

			### get list
			if (strstr($this->list_request, "archived_"))
			{
				$tmp = explode("_", $this->list_request);

				if (3 == count($tmp))
				{
					$year = $tmp[1];
					$month = $tmp[2];
					unset($args["limit"]);
					$args["state"] = MRP_STATUS_ARCHIVED;
					$args["starttime"] = new obj_predicate_compare (
						OBJ_COMP_BETWEEN,
						mktime(0,0,0,$month,1,$year),
						mktime(0,0,0,((12 == $month) ? 1 : ($month + 1)),1,((12 == $month) ? ($year + 1) : $year))
					);

					$this->projects_list_objects = new object_list ($args);
					$this->projects_list_objects_count = $this->projects_list_objects->count();
					$args["limit"] = $limit;
					$this->projects_list_objects = new object_list ($args);
				}
			}
			else
			{
				switch ($this->list_request)
				{
					case "all":
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->projects_all_count;
						break;

					case "planned":
						$args["state"] = MRP_STATUS_PLANNED;
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->projects_planned_count;
						break;

					case "inwork":
						$args["state"] = MRP_STATUS_INPROGRESS;
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->projects_in_work_count;
						break;

					case "planned_overdue":
						$args["state"] = $applicable_states;
						$args["planned_date"] = new obj_predicate_prop (OBJ_COMP_GREATER, "due_date");
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->projects_planned_overdue_count;
						break;

					case "overdue":
						$args["due_date"] = new obj_predicate_compare (OBJ_COMP_LESS, time());
						$args["state"] = $applicable_states;
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->projects_overdue_count;
						break;

					case "new":
						$args["state"] = MRP_STATUS_NEW;
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->projects_new_count;
						break;

					case "done":
						$args["state"] = MRP_STATUS_DONE;
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->projects_done_count;
						break;

					case "aborted":
						$args["state"] = MRP_STATUS_ABORTED;
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->projects_aborted_count;
						break;

					case "onhold":
						$args["state"] = MRP_STATUS_ONHOLD;
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->projects_onhold_count;
						break;

					case "aborted_jobs":
						$applicable_sortorders = array (
							"due_date",
						);

						if (in_array($arr["request"]["sortby"], $applicable_sortorders))
						{
							$args["CL_MRP_JOB.project(CL_MRP_CASE).due_date"] = new obj_predicate_compare (OBJ_COMP_GREATER, 0);//!!! temporary. acceptable solution needed. projects with planned_date NULL not retrieved.
							$args["sort_by"] = "mrp_case_826_project.due_date {$sort_order}";
						}
						else
						{
							unset($args["sort_by"]);
						}

						unset($args["planned_date"]);
						$args["class_id"] = CL_MRP_JOB;
						$args["state"] = MRP_STATUS_ABORTED;
						$args["parent"] = $this_object->prop ("jobs_folder");
						$this->projects_list_objects = new object_list ($args);
						$this->projects_list_objects_count = $this->jobs_aborted_count;
						break;

					case "subcontracts":
						$this->projects_list_objects = $this->_get_subcontract_job_list($this_object, $limit);
						$this->projects_list_objects_count = $this->jobs_subcontracted_count;
						break;
				}
			}
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object = $arr["obj_inst"];

		### require remaining_length and minstart when job was aborted_jobs
		if (is_oid (aw_global_get ("mrp_printer_aborted")))
		{
			if ($prop["name"] === "pj_remaining_length")
			{
				$job = obj (aw_global_get ("mrp_printer_aborted"));
				$len  = floor ($job->prop("remaining_length") / 3600);
				$prop["value"] = $len;
				return PROP_OK;
			}
			elseif ($prop["name"] === "pj_minstart")
			{
				$prop["value"] = time ();
				return PROP_OK;
			}
			elseif ($prop["name"] === "pj_submit")
			{
				return PROP_OK;
			}
			else
			{
				return PROP_IGNORE;
			}
		}
		else
		{
			if (($prop["name"] === "pj_remaining_length") or ($prop["name"] === "pj_minstart") or ($prop["name"] === "pj_submit"))
			{
				return PROP_IGNORE;
			}
		}

		if (substr($prop["name"], 0, 3) === "pjp")
		{
			if (empty($arr["request"]["pj_job"]))
			{
				return PROP_IGNORE;
			}

			// get prop from project
			if (empty($prop["subtitle"]))
			{
				$job = obj($arr["request"]["pj_job"]);
				$proj = obj($job->prop("project"));
				$rpn = substr($prop["name"], 4);
				$prop["value"] = $proj->prop($rpn);

				if ($prop["name"] === "pjp_case_wf")
				{
					$this->_pjp_case_wf($arr);
					return PROP_OK;
				}

				$retv = $this->import->get_prop_value($prop, $rpn);
				if ($retv != PROP_OK)
				{
					return $retv;
				}
			}
		}

		if (substr($prop["name"], 0, 3) === "pj_")
		{
			if (empty($arr["request"]["pj_job"]))
			{
				return PROP_IGNORE;
			}

			// get prop from job
			if (empty($prop["subtitle"]))
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
							$prop["value"] = html::obj_change_url($tmp);
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

					case "job_comment":
						$prop["value"] = $job->comment();
						break;

					default:
						$prop["value"] = $job->prop($rpn);
						if ($prop["value"] == "" && $prop["name"] !== "pj_change_comment")
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
			case "sp_submit":
				$uri = automatweb::$request->get_uri();
				$uri->set_arg(array(
					"sp_search",
					"sp_name",
					"sp_starttime[day]",
					"sp_starttime[month]",
					"sp_starttime[year]",
					"sp_due_date[day]",
					"sp_due_date[month]",
					"sp_due_date[year]",
					"sp_customer",
					"sp_status"
				), null);
				$prop["onclick"] = 'window.location=\'' . $uri->get() .
					'&sp_search=1&sp_name=\'+document.forms[\'changeform\'].sp_name.value+\''.
					'&sp_starttime[day]=\'+document.forms[\'changeform\'][\'sp_starttime[day]\'][\'value\']+\''.
					'&sp_starttime[month]=\'+document.forms[\'changeform\'][\'sp_starttime[month]\'][\'value\']+\''.
					'&sp_starttime[year]=\'+document.forms[\'changeform\'][\'sp_starttime[year]\'][\'value\']+\''.
					'&sp_due_date[day]=\'+document.forms[\'changeform\'][\'sp_due_date[day]\'][\'value\']+\''.
					'&sp_due_date[month]=\'+document.forms[\'changeform\'][\'sp_due_date[month]\'][\'value\']+\''.
					'&sp_due_date[year]=\'+document.forms[\'changeform\'][\'sp_due_date[year]\'][\'value\']+\''.
					'&sp_status=\'+document.forms[\'changeform\'].sp_status.value+\''.
					'&sp_customer=\'+document.forms[\'changeform\'].sp_customer.value'
				;
				break;

			// project search parameters
			case "sp_status":
				$prop["options"] = array("" => t("K&otilde;ik")) + $this->states;
				$prop["value"] = isset($arr["request"]["sp_status"]) ? $arr["request"]["sp_status"] : array();
				break;

			case "sp_name":
				$prop["value"] = isset($arr["request"]["sp_name"]) ? $arr["request"]["sp_name"] : "";
				break;

			case "sp_comment":
				$prop["value"] = isset($arr["request"]["sp_comment"]) ? $arr["request"]["sp_comment"] : "";
				break;

			case "sp_customer":
				$prop["value"] = isset($arr["request"]["sp_customer"]) ? $arr["request"]["sp_customer"] : "";
				break;

			case "sp_starttime":
				$prop["value"] = isset($arr["request"]["sp_starttime"]) ? $arr["request"]["sp_starttime"] : -1;
				break;

			case "sp_due_date":
				$prop["value"] = isset($arr["request"]["sp_due_date"]) ? $arr["request"]["sp_due_date"] : -1;
				break;

			case "projects_toolbar":
				$this->create_projects_toolbar ($arr);
				break;

			case "projects_tree":
				$this->create_projects_tree ($arr);
				break;

			case "projects_list":
				if (empty($arr["request"]["sp_search"]))
				{
					$tree_active_item = isset($arr["request"]["mrp_tree_active_item"]) ? $arr["request"]["mrp_tree_active_item"] : null;
					switch ($tree_active_item)
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

						case "all":
						case "planned":
						case "inwork":
						case "planned_overdue":
						case "overdue":
						case "subcontracts":
							### update schedule
							$schedule = get_instance (CL_MRP_SCHEDULE);
							$schedule->create (array("mrp_workspace" => $this_object->id()));

						default:
							$this->create_projects_list ($arr);
							break;
					}
				}
				else
				{
					$retval = PROP_IGNORE;
				}
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

				if (aw_global_get("mrp_errors"))
				{
					$retval = PROP_ERROR;
					$prop["error"] = aw_global_get("mrp_errors");
					aw_session_del("mrp_errors");
				}
				break;
			case "resources_tree":
				$this->create_resources_tree ($arr);
				break;
			case "resources_list":
				$this->create_resources_list ($arr);
				break;

			### customers tab
			case "cs_submit":
				$prop["onclick"] = "document.forms.changeform.cat.value='';";
				break;
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
			case "states_chart":
				$c = &$arr["prop"]["vcl_inst"];
				$c->set_type(GCHART_PIE_3D);
				$c->set_size(array(
					"width" => 500,
					"height" => 100,
				));
				$c->add_fill(array(
					"area" => GCHART_FILL_BACKGROUND,
					"type" => GCHART_FILL_SOLID,
					"colors" => array(
						"color" => "e9e9e9",
					),
				));
				$data = array();
				$labels = array();
				$colors = array();
				$ol = new object_list(array(
					"class_id" => CL_MRP_CASE,
					"parent" => $this_object->prop("projects_folder"),
				));
				foreach($ol->arr() as $o)
				{
					if(!isset($data[$o->state]))
					{
						$data[$o->state] = 1;
					}
					else
					{
						$data[$o->state]++;
					}
					$colors[$o->state] = strtolower(preg_replace("/[^0-9A-Za-z]/", "", $this->state_colours[$o->state]));
					$labels[$o->state] = $this->states[$o->state]." (".$data[$o->state].")";
				}
				$c->set_colors($colors);
				$c->add_data($data);
				$c->set_labels($labels);
				$c->set_title(array(
					"text" => t("Projekid staatuste kaupa"),
					"color" => "666666",
					"size" => 11,
				));
				break;

			case "clients_chart":
				$c = &$arr["prop"]["vcl_inst"];
				$c->set_type(GCHART_PIE_3D);
				$c->set_size(array(
					"width" => 500,
					"height" => 100,
				));
				$c->add_fill(array(
					"area" => GCHART_FILL_BACKGROUND,
					"type" => GCHART_FILL_SOLID,
					"colors" => array(
						"color" => "e9e9e9",
					),
				));
				$data = array();
				$labels = array();
				$ol = new object_list(array(
					"class_id" => CL_MRP_CASE,
					"parent" => $this_object->prop("projects_folder"),
				));
				foreach($ol->arr() as $o)
				{
					$key = $o->customer;
					$name = $o->prop("customer.name");
					if(!isset($data[$key]))
					{
						$data[$key] = 1;
					}
					else
					{
						$data[$key]++;
					}
					$labels[$key] = $name." (".$data[$key].")";
				}
				$labels[0] = sprintf(t("(M&Auml;&Auml;RAMATA) (%u)"), $data[0]);
				$c->set_colors(array("2222ff"));
				$c->add_data($data);
				$c->set_labels($labels);
				$c->set_title(array(
					"text" => t("Projekid klientide kaupa"),
					"color" => "666666",
					"size" => 11,
				));
				break;

			case "deadline_chart":
				$c = &$arr["prop"]["vcl_inst"];
				$c->set_type(GCHART_PIE_3D);
				$c->set_size(array(
					"width" => 500,
					"height" => 100,
				));
				$c->add_fill(array(
					"area" => GCHART_FILL_BACKGROUND,
					"type" => GCHART_FILL_SOLID,
					"colors" => array(
						"color" => "e9e9e9",
					),
				));
				$data = array();
				$labels = array();
				$ol = new object_list(array(
					"class_id" => CL_MRP_CASE,
					"state" => array(
						MRP_STATUS_INPROGRESS,
						MRP_STATUS_PLANNED,
					),
					"parent" => $this_object->prop("projects_folder"),
				));
				$names = array(
					0 => t("Graafikus"),
					1 => t("&Uuml;le t&auml;htaja"),
				);
				$colors_ = array(
					0 => "00ff00",
					1 => "ff0000",
				);
				foreach($ol->arr() as $o)
				{
					$key = $o->planned_date > $o->due_date ? 1 : 0;
					$name = $o->prop("customer.name");
					if(!isset($data[$key]))
					{
						$data[$key] = 1;
					}
					else
					{
						$data[$key]++;
					}
					$colors[$key] = $colors_[$key];
					$labels[$key] = $names[$key]." (".$data[$key].")";
				}
				$c->set_colors($colors);
				$c->add_data($data);
				$c->set_labels($labels);
				$c->set_title(array(
					"text" => t("Projekid t&auml;htaja j&auml;rgi"),
					"color" => "666666",
					"size" => 11,
				));
				break;

			case "master_schedule_chart":
				### update schedule
				$schedule = get_instance (CL_MRP_SCHEDULE);
				$schedule->create (array("mrp_workspace" => $this_object->id()));

				$prop["value"] = $this->create_schedule_chart($arr);
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
				if ($arr["request"]["action"] == "view")
				{
					return PROP_IGNORE;
				}
				$plan_url = $this->mk_my_orb("create", array(
					"return_url" => get_ru(),
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
			case "automatic_archiving_period_unit":
				$prop["value"] = t("P&auml;ev(a) peale projekti valmimist");
				break;

			case "parameter_timescale_unit":
				$prop["options"] = array (
					"86400" => t("P&auml;ev"),
					"60" => t("Minut"),
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
				if (!empty($arr["request"]["pj_job"]))
				{
					return PROP_IGNORE;
				}

				### update schedule
				$schedule = get_instance (CL_MRP_SCHEDULE);
				$schedule->create (array("mrp_workspace" => $this_object->id()));

				$this->_printer_jobs($arr);
				break;

			case "printer_legend":
				if (!empty($arr["request"]["pj_job"]))
				{
					return PROP_IGNORE;
				}

				$prop["value"] = "<table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td>";
				$prop["value"]  .= "<span style='font-size: 11px; padding: 5px; background: ".$this->pj_colors["done"]."'>".t("Valmis")."</span>&nbsp;&nbsp;";
				$prop["value"] .= "<span style='font-size: 11px; padding: 5px; background: ".$this->pj_colors["can_start"]."'>".t("V&otilde;ib alustada")."</span>&nbsp;&nbsp;";
				$prop["value"] .= "<span style='font-size: 11px; padding: 5px; background: ".$this->pj_colors["can_not_start"]."'>".t("Ei saa alustada/t&ouml;&ouml;s")."</span>&nbsp;&nbsp;";
				$prop["value"] .= "<span style='font-size: 11px; padding: 5px; background: ".$this->pj_colors["resource_in_use"]."'>".t("Eeldust&ouml;&ouml; tehtud")."</span>&nbsp;&nbsp;";
				$prop["value"] .= "<span style='font-size: 11px; padding: 5px; background: ".$this->pj_colors["search_result"]."'>".t("Otsingu tulemus")."</span>&nbsp;&nbsp;";
				$prop["value"] .= "</td><td align=right>";
				$prop["value"] .= "<span style='font-size: 11px;'>Projekt: <input size=6 type=text name=do_pv_proj_s>";
				$prop["value"] .= "<a href='#' onclick='changed=0;document.changeform.submit()'>Otsi</a></span>";
				$prop["value"] .= "</td><td align=right>";
				$prop["value"] .= "<span style='font-size: 11px;'>Vali ressurss: <select onchange='xchanged=1;submit_changeform(\"\");' name=pj_use_resource>";
				$resids = $this->get_cur_printer_resources(array(
					"ws" => $arr["obj_inst"],
					"ign_glob" => true
				));
				$res_ol = new object_list();
				if (count($resids))
				{
					$res_ol = new object_list(array("oid" => $resids,"sort_by" => "objects.name"));
				}
				$empty_selection = array();
				if (count($resids) > 1)
				{
					$empty_selection = array("0" => t("K&otilde;ik ressursid"));
				}
				$prop["value"] .= $this->picker(aw_global_get("mrp_operator_use_resource"), $empty_selection + $res_ol->names());
				$prop["value"] .= "</select>";

				$prop["value"] .= "</td></tr></table>";
				break;

			case "sp_name":
			case "sp_comment":
			case "sp_customer":
			case "cs_name":
			case "cs_firmajuht":
			case "cs_contact":
			case "cs_phone":
			case "cs_reg_nr":
				if (isset($arr["request"][$prop["name"]]))
				{
					$prop["value"] = $arr["request"][$prop["name"]];
				}
				break;

			case "sp_starttime":
			case "sp_due_date":
				if (isset($arr["request"][$prop["name"]]))
				{
					$prop["value"] = date_edit::get_timestamp($arr["request"][$prop["name"]]);
				}
				break;

			case "sp_result":
				$this->_sp_result($arr);
				break;

			case "cs_result":
				$this->_cs_result($arr);
				break;

			case "sp_status":
				if (isset($arr["request"][$prop["name"]]))
				{
					$prop["options"] = array(
						MRP_STATUS_DONE => $this->states[MRP_STATUS_DONE],
						MRP_STATUS_ABORTED => $this->states[MRP_STATUS_ABORTED],
						MRP_STATUS_PLANNED => $this->states[MRP_STATUS_PLANNED],
						MRP_STATUS_ARCHIVED => $this->states[MRP_STATUS_ARCHIVED]
					);
					$prop["value"] = $arr["request"][$prop["name"]];
				}
				break;

			case "select_session_resource":
				$resids = $this->get_cur_printer_resources(array(
					"ws" => $arr["obj_inst"],
					"ign_glob" => true
				));
				if (count($resids) === 1)
				{
					$resid = reset($resids);
					if ($resid)
					{
						aw_session_set("mrp_operator_use_resource", $resid);
					}
					else
					{
						aw_session_del("mrp_operator_use_resource");
					}
					// aaaand redirect
					header("Location: ".$this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => "grp_printer_current")));
					die();
				}
				elseif (count($resids) > 0)
				{
					$ol = new object_list(array("oid" => $resids));
				}
				else
				{
					$ol = new object_list();
				}

				$prop["options"] = /*array("" => "") +*/ $ol->names();
				$prop["value"] = aw_global_get("mrp_operator_use_resource");
				break;

			case "chart_search":
				$this->_chart_search($arr);
				break;

			case "printer_jobs_next_link":
				if (!empty($arr["request"]["pj_job"]) || $arr["request"]["group"] === "grp_printer_notstartable" || $arr["request"]["group"] === "grp_printer_startable")
				{
					return PROP_IGNORE;
				}
				$page = isset($arr["request"]["printer_job_page"]) ? (int) $arr["request"]["printer_job_page"] : 0;
				$prop["value"] = html::href(array(
					"url" => aw_url_change_var("printer_job_page", $page+1),
					"caption" => t("J&auml;rgmine lehek&uuml;lg")
				));
				break;

			case "printer_jobs_prev_link":
				if (!empty($arr["request"]["pj_job"]) || $arr["request"]["group"] === "grp_printer_notstartable" || $arr["request"]["group"] === "grp_printer_startable")
				{
					return PROP_IGNORE;
				}

				if (empty($arr["request"]["printer_job_page"]))
				{
					return PROP_IGNORE;
				}
				$prop["value"] = html::href(array(
					"url" => aw_url_change_var("printer_job_page", $arr["request"]["printer_job_page"]-1),
					"caption" => t("Eelmine lehek&uuml;lg")
				));
				break;

			### worksheets tab
			case "ws_resource":
				$res_list = $this->get_cur_printer_resources(array("ws" => $arr["obj_inst"]));
				if (count($res_list))
				{
					$ol = new object_list(array(
						"oid" => $res_list,
						"site_id" => array(),
						"lang_id" => array(),
					));
					$prop["options"] = $ol->names();
				}
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "ws_from":
			case "ws_to":
				$prop["value"] = date_edit::get_timestamp($arr["request"][$prop["name"]]);
				break;

			case "ws_tbl":
				$this->_ws_tbl($arr);
				break;
		}
		return $retval;
	}

	function set_property ($arr = array ())
	{
		$this_object = $arr["obj_inst"];
		$prop =& $arr["prop"];
		$retval = PROP_OK;

		if ( (substr($prop["name"], 0, 9) === "parameter") and ($this_object->prop ($prop["name"]) != $prop["value"]) )
		{
			### post rescheduling msg
			$this_object->set_prop("rescheduling_needed", 1);
		}

		switch ($prop["name"])
		{
			case "printer_legend":
				if (isset($arr["request"]["pj_use_resource"]))
				{
					if ($arr["request"]["pj_use_resource"])
					{
						aw_session_set("mrp_operator_use_resource", $arr["request"]["pj_use_resource"]);
					}
					else
					{
						aw_session_del("mrp_operator_use_resource");
					}
				}
				break;

			case "projects_list":
				$retval = $this->save_custom_form_data ($arr);
				$applicable_lists = array (
					"planned_overdue",
					"overdue",
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

			### settings tab
			case "automatic_archiving_period":
				$requested_period = (int) abs($prop["value"]);
				$saved_period = (int) $this_object->prop("automatic_archiving_period");
				$time = mktime(28,0,0, date("m"), date("d"), date("Y")); // four a.m. next day

				if ($requested_period and !$saved_period)
				{
					### add archiving scheduler
					$scheduler = get_instance("scheduler");
					$event = $this->mk_my_orb("archive_projects", array("id" => $this_object->id()));

					$scheduler->add(array(
						"event" => $event,
						"time" => $time,
						"uid" => aw_global_get("uid"),
						"auth_as_local_user" => true,
					));
				}
				elseif (!$requested_period and $saved_period)
				{
					### delete archiving scheduler
					$scheduler = get_instance("scheduler");
					$event = $this->mk_my_orb("archive_projects", array("id" => $this_object->id()));
					$scheduler->remove(array(
						"event" => $event,
					));
				}
				elseif ($requested_period and $saved_period and $requested_period != $saved_period)
				{
					### delete old event
					$scheduler = get_instance("scheduler");
					$event = $this->mk_my_orb("archive_projects", array("id" => $this_object->id()));
					$scheduler->remove(array(
						"event" => $event,
					));

					### add new
					$scheduler->add(array(
						"event" => $event,
						"time" => $time,
						"uid" => aw_global_get("uid"),
						"auth_as_local_user" => true,
					));
				}
				break;

			case "max_subcontractor_timediff":
				$prop["value"] = round ($prop["value"] * 3600);
				break;
		}

		return $retval;
	}

	function callback_mod_retval ($arr)
	{
		// delete justsaved msg where needed
		$applicable_groups =  array(
			"grp_printer",
			"grp_printer_current",
			"groupinfo grp_printer_old",
			"grp_printer_done",
			"grp_printer_aborted",
			"grp_printer_in_progress",
			"grp_printer_startable",
			"grp_schedule",
			"grp_projects",
			"grp_printer_notstartable"
		);
		if (!empty($arr["args"]["group"]) and in_array($arr["args"]["group"], $applicable_groups))
		{
			unset($arr["args"]["just_saved"]);
		}

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

		$_SESSION["mrp"]["do_pv_proj_s"] = $arr["request"]["do_pv_proj_s"];
	}

	function callback_pre_save ($arr)
	{
		if (is_oid (aw_global_get ("mrp_printer_aborted")))
		{
			$minstart= mktime (0, 0, 0, $arr["request"]["pj_minstart"]["month"], $arr["request"]["pj_minstart"]["day"], $arr["request"]["pj_minstart"]["year"]);
			$job = obj (aw_global_get ("mrp_printer_aborted"));
			$job->set_prop ("remaining_length", (int) ($arr["request"]["pj_remaining_length"]*3600));
			$job->set_prop ("minstart", (int) ($minstart));
			$job->save ();
			aw_session_del ("mrp_printer_aborted");
		}
	}

	// method only needed in self::create_resources_tree() method archived res removal backwards compatible version
	function cb_remove_inactive_res(&$o, $parent)
	{
		if (CL_MRP_RESOURCE == $o->class_id() and MRP_STATUS_RESOURCE_INACTIVE == $o->prop("state"))
		{
			$this->mrp_remove_resources_from_tree[] = $o->id();
		}
	}

	function create_resources_tree ($arr = array())
	{
		$this_object = $arr["obj_inst"];
		$applicable_states = array(
			MRP_STATUS_RESOURCE_INACTIVE,
		);

		### resource tree
		$resources_folder = $this_object->prop ("resources_folder");
		$resource_tree = new object_tree(array(
			"parent" => $resources_folder,
			"class_id" => array(CL_MENU, CL_MRP_RESOURCE),
			"sort_by" => "objects.jrk",
			// "CL_MRP_RESOURCE.state" => new obj_predicate_not(array($applicable_states)), // archived res removal std version
		));

		// archived res removal backwards compatible version
		$this->mrp_remove_resources_from_tree = array();
		$resource_tree->foreach_cb(array(
			"func" => array(&$this, "cb_remove_inactive_res"),
			"save" => false,
		));

		if (count($this->mrp_remove_resources_from_tree))
		{
			$resource_tree->remove($this->mrp_remove_resources_from_tree);
		}
		// END archived res removal backwards compatible version

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
		$table = $arr["prop"]["vcl_inst"];
		$this_object = $arr["obj_inst"];

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

		$parent_o = new object($parent);
		$owner = $arr["obj_inst"]->prop("RELTYPE_MRP_OWNER.name");
		$table->set_caption(sprintf(t("%s ressursid kategoorias '%s'"), $owner, $parent_o->name()));

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
			"sortable" => 1,
			"numeric" => 1
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
				$operators[] = html::obj_change_url($person);
			}

			$table->define_data (array (
				"name" => html::obj_change_url($resource),
				"order" => $resource->ord (),
				"operator" => join(",",$operators),
				"status" => $this->resource_states[$resource->prop("state")],
				"resource_id" => $resource->id(),
			));
		}
	}

	function create_resources_toolbar ($arr = array())
	{
		$toolbar = $arr["prop"]["toolbar"];
		$this_object = $arr["obj_inst"];

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
			"return_url" => get_ru(),
			"mrp_workspace" => $this_object->id (),
			"mrp_parent" => $parent,
			"parent" => $parent,
		), "mrp_resource");
		$add_category_url = $this->mk_my_orb("new", array(
			"return_url" => get_ru(),
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
			"tooltip" => t("Arhiveeri"),
			"action" => "delete",
			"confirm" => t("Arhiveerida k&otilde;ik valitud ressursid?"),
		));
	}

	function create_projects_toolbar ($arr = array())
	{
		$toolbar = $arr["prop"]["toolbar"];
		$this_object = $arr["obj_inst"];
		$add_project_url = $this->mk_my_orb("new", array(
			"return_url" => get_ru(),
			"mrp_workspace" => $this_object->id (),
			"parent" => $this_object->prop ("projects_folder")
		), "mrp_case");
		$toolbar->add_button(array(
			"name" => "add",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus projekt"),
			"url" => $add_project_url
		));
		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud projekt(id)"),
			"confirm" => t("Kustutada k&otilde;ik valitud projektid?"),
			"action" => "delete"
		));
		$toolbar->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta"),
			"action" => ""
		));
	}

	function create_projects_tree ($arr = array())
	{
		$this_object = $arr["obj_inst"];
		$projects_folder = $this_object->prop ("projects_folder");
		$open_path = NULL;
		$url = automatweb::$request->get_uri();
		$url->unset_arg(array(
			"ft_page",
			"sp_search",
			"sp_name",
			"sp_starttime",
			"sp_customer",
			"sp_due_date",
			"sp_status"
		));

		if (!empty($arr["request"]["mrp_tree_active_item"]) and strstr($arr["request"]["mrp_tree_active_item"], "archived_"))
		{
			$tmp = explode("_", $arr["request"]["mrp_tree_active_item"]);

			if (3 == count($tmp))
			{
				$open_path = array("archived", "archived_" . $tmp[1], "archived_" . $tmp[1] . "_" . $tmp[2]);
			}
		}

		$tree = get_instance("vcl/treeview");
		$tree->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "projecttree",
			"persist_state" => 0,
			"has_root" => 1,
			"open_path" => $open_path,
			"root_url" => aw_url_change_var (array(
				"mrp_tree_active_item" => "all",
				"ft_page" => 0
			)),
			"root_name" => t("K&otilde;ik projektid") . " (" . $this->projects_all_count . ")",
			"get_branch_func" => $this->mk_my_orb("get_projects_subtree", array(
				"id" => $this_object->id(),
				"url" => aw_global_get("REQUEST_URI"),
				// "url" => aw_global_get("REQUEST_URI"),
				// "parent" => "",
			)) . "&parent=",//!!! ilmselt ajutine muudatus prisma serveri jaoks -- mkmyorb on seal arvatavasti vana vms.
		));
		$tree->set_only_one_level_opened (true);

		$url->set_arg("mrp_tree_active_item", "planned");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["planned"] . " (" . $this->projects_planned_count . ")",
			"id" => "planned",
			"parent" => 0,
			"url" => $url->get()
		));

		$url->set_arg("mrp_tree_active_item", "inwork");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["inwork"] . " (" . $this->projects_in_work_count . ")",
			"id" => "inwork",
			"parent" => 0,
			"url" => $url->get()
		));

		$url->set_arg("mrp_tree_active_item", "planned_overdue");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["planned_overdue"] . " (" . $this->projects_planned_overdue_count . ")",
			"id" => "planned_overdue",
			"parent" => 0,
			"url" => $url->get()
		));

		$url->set_arg("mrp_tree_active_item", "overdue");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["overdue"] . " (" . $this->projects_overdue_count . ")",
			"id" => "overdue",
			"parent" => 0,
			"url" => $url->get()
		));

		$url->set_arg("mrp_tree_active_item", "new");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["new"] . " (" . $this->projects_new_count . ")",
			"id" => "new",
			"parent" => 0,
			"url" => $url->get()
		));

		$url->set_arg("mrp_tree_active_item", "aborted");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["aborted"] . " (" . $this->projects_aborted_count . ")",
			"id" => "aborted",
			"parent" => 0,
			"url" => $url->get()
		));

		$url->set_arg("mrp_tree_active_item", "onhold");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["onhold"] . " (" . $this->projects_onhold_count . ")",
			"id" => "onhold",
			"parent" => 0,
			"url" => $url->get()
		));

		$url->set_arg("mrp_tree_active_item", "done");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["done"] . " (" . $this->projects_done_count . ")",
			"id" => "done",
			"parent" => 0,
			"url" => $url->get()
		));

		$tree->add_item (0, array (
			"name" => $this->project_list_categories["archived"],// . " (" . $this->projects_archived_count . ")",
			"id" => "archived",
			"parent" => 0,
			"url" => "javascript: void(0);",
			// "url" => aw_url_change_var (array(
				// "mrp_tree_active_item" => "archived",
				// "ft_page" => 0
			// )),
		));

		if ($this_object->prop("automatic_archiving_period"))
		{
			$tree->add_item ("archived", array (
				"id" => "dummy",
				"parent" => "archived",
			));
		}

		$url->set_arg("mrp_tree_active_item", "subcontracts");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["subcontracts"] . " (" . $this->jobs_subcontracted_count . ")",
			"parent" => 0,
			"id" => "subcontracts",
			"url" => $url->get()
		));

		$url->set_arg("mrp_tree_active_item", "aborted_jobs");
		$tree->add_item (0, array (
			"name" => $this->project_list_categories["aborted_jobs"] . " (" . $this->jobs_aborted_count . ")",
			"parent" => 0,
			"id" => "aborted_jobs",
			"url" => $url->get()
		));

		if ("search" !== $this->list_request)
		{
			$active_node = empty ($arr["request"]["mrp_tree_active_item"]) ? "planned" : $arr["request"]["mrp_tree_active_item"];
			$active_node = (isset($arr["request"]["mrp_tree_active_item"]) and "all" === $arr["request"]["mrp_tree_active_item"]) ? 0 : $active_node;
			$tree->set_selected_item ($active_node);
		}
		$arr["prop"]["value"] = $tree->finalize_tree(0);
	}

	function create_projects_list ($arr = array ())
	{
		$table = $arr["prop"]["vcl_inst"];
		$this_object = $arr["obj_inst"];
		$table->name = "projects_list_" . $this->list_request;

		$table->define_field (array (
			"name" => "customer",
			"caption" => t("Klient"),
			"chgbgcolor" => "bgcolour_overdue",
			// "sortable" => 1,
		));
		$table->define_field (array (
			"name" => "name",
			"caption" => t("Pro&shy;jekt"),
			"chgbgcolor" => "bgcolour_overdue",
			// "sortable" => 1,
			"numeric" => 1
		));
		$table->define_field (array (
			"name" => "title",
			"caption" => t("Pro&shy;jekti nimi"),
			"chgbgcolor" => "bgcolour_overdue",
			"sortable" => 1,
		));

		$no_plan_lists = array (
			"onhold",
			"new",
		);

		switch ($this->list_request)
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
					"caption" => t("Materjalide<br />saabumine"),
					"chgbgcolor" => "bgcolour_overdue",
					"type" => "time",
					"format" => MRP_DATE_FORMAT,
					"sortable" => 1,
				));
				$table->define_field(array(
					"name" => "planned_date",
					"caption" => t("Planeeritud<br />valmimine"),
					"chgbgcolor" => "bgcolour_overdue",
					"type" => "time",
					"format" => MRP_DATE_FORMAT,
					"sortable" => (in_array($this->list_request, $no_plan_lists)) ? 0 : 1,
				));
				$table->define_field(array(
					"name" => "due_date",
					"caption" => t("T&auml;htaeg"),
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
			"caption" => t("MP"),
			"tooltip" => t("M&uuml;&uuml;gi prioriteet"),
			"chgbgcolor" => "bgcolour_overdue",
			// "sortable" => 1,
		));

		if ($this->list_request !== "search")
		{
			$table->define_chooser(array(
				"name" => "selection",
				"field" => "project_id",
			));
		}

		switch ($this->list_request)
		{
			case "all":
			case "planned":
			case "inwork":
			case "planned_overdue":
			case "overdue":
			case "new":
			case "done":
			case "aborted":
			case "onhold":
				$list = $this->projects_list_objects;
				break;

			case "search":
				$list = $arr["search_res"];
				break;
		}

		$caption = $this->project_list_categories[$this->list_request];

		if (strstr($this->list_request, "archived_"))
		{
			$list = $this->projects_list_objects;

			// archive period caption
			$period_parts = explode("_", $this->list_request);
			$caption .= " ";
			$period_parts = array_reverse($period_parts);
			foreach ($period_parts as $part)
			{
				if ("archived" !== $part)
				{
					$caption .= "." . $part;
				}
			}
		}

		$jobs_folder = $this_object->prop ("jobs_folder");
		$return_url = get_ru();

		$projects = $list->arr ();
		$bg_colour = "";

		foreach ($projects as $project_id => $project)
		{
			$priority = $project->prop ("project_priority");
			$act = "change";

			if (!$this->can("edit", $project_id))
			{
				$act = "view";
			}

			$change_url = $this->mk_my_orb($act, array(
				"id" => $project_id,
				"return_url" => $return_url,
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
					"parent" => $jobs_folder,
					"exec_order" => $jobs,
					"project" => $project_id,
				));
				$last_job = $list->begin ();
				$planned_date = is_object ($last_job) ? date (MRP_DATE_FORMAT, ($last_job->prop ("planned_length") + $last_job->prop ("starttime"))) : "-";
			}

			### get project customer
			$customer = $project->get_first_obj_by_reltype("RELTYPE_MRP_CUSTOMER");

			### do request specific operations
			if ("inwork" === $this->list_request or "planned" === $this->list_request)
			{
				### hilight for planned overdue
				$bg_colour = ($project->prop ("due_date") < $planned_date) ? MRP_COLOUR_PLANNED_OVERDUE : "";

				### hilight for overdue
				$bg_colour = ($project->prop ("due_date") <= time ()) ? MRP_COLOUR_OVERDUE : $bg_colour;
			}

			### define data for html table row
			$data = array (
				"name" => html::href (array (
					"caption" => $project->name(),
					"url" => $change_url,
					)
				),
				"customer" => (is_object($customer) ? $customer->name () : ""),
				"priority" => $priority,
				"sales_priority" => $project->prop ("sales_priority"),
				"title" => substr(wordwrap($project->comment(), 25, "...", true), 0, 26),
				"starttime" => $project->prop ("starttime"),
				"due_date" => $project->prop ("due_date"),
				"planned_date" => $planned_date,
				"project_id" => $project_id,
				"bgcolour_overdue" => $bg_colour,
			);

			if (!$bg_colour)
			{
				unset ($data["bgcolour_overdue"]);
			}

			$table->define_data($data);
		}

		switch ($this->list_request)
		{
			case "inwork":
				$table->set_default_sortby ("due_date");
				break;

			case "planned_overdue":
			case "overdue":
			case "new":
			case "planned":
			case "all":
			case "done":
			default:
				$table->set_default_sortby ("starttime");
				break;
		}

		$table->set_caption(sprintf(t("Projektid: %s"), $caption));

		$coloured_lists = array(
			"inwork",
			"planned",
			"search"
		);
		if (in_array($this->list_request, $coloured_lists))
		{
			$table->set_footer('<div style="display: block; margin: 4px;"><span style="height: 15px; margin-right: 3px; background-color: ' . MRP_COLOUR_PLANNED_OVERDUE . '; border: 1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> '.t("&Uuml;le t&auml;htaja planeeritud").' <span style="height: 15px; margin-right: 3px; margin-left: 25px; background-color: ' . MRP_COLOUR_OVERDUE . '; border: 1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> '.t("&Uuml;le t&auml;htaja l&auml;inud").'</div>');
		}

		$table->set_default_sorder ("asc");
		$table->define_pageselector (array (
			"type" => "lbtxt",
			"d_row_cnt" => $this->projects_list_objects_count,
			"records_per_page" => $this_object->prop("projects_list_objects_perpage") ? $this_object->prop("projects_list_objects_perpage") : 30,
		));
	}

	function create_subcontract_jobs_list ($arr = array ())
	{
		$table = $arr["prop"]["vcl_inst"];
		$this_object = $arr["obj_inst"];

		$table->define_field (array (
			"name" => "customer",
			"caption" => t("Klient"),
			"chgbgcolor" => "bgcolour_overdue",
			// "sortable" => 1,
		));
		$table->define_field (array (
			"name" => "project",
			"caption" => t("Projekt"),
			"chgbgcolor" => "bgcolour_overdue",
			// "sortable" => 1,
		));
		$table->define_field (array (
			"name" => "resource",
			"caption" => t("Ressurss"),
			"chgbgcolor" => "bgcolour_overdue",
			// "sortable" => 1,
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
		$table->define_pageselector (array (
			"type" => "lbtxt",
			"d_row_cnt" => $this->projects_list_objects_count,
			"records_per_page" => $this_object->prop("projects_list_objects_perpage") ? $this_object->prop("projects_list_objects_perpage") : 30,
		));
		$table->set_caption($this->project_list_categories[$this->list_request]);

		$jobs = $this->projects_list_objects->arr();

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
				"project" => html::obj_change_url($project),
				"resource" => html::obj_change_url($resource),
				"scheduled_date" => $job->prop ("starttime"),
				"modify" => html::obj_change_url($job, t("Ava")),
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
		$table = $arr["prop"]["vcl_inst"];
		$this_object = $arr["obj_inst"];

		$table->define_field (array (
			"name" => "customer",
			"caption" => t("Klient"),
			// "sortable" => 1,
		));
		$table->define_field (array (
			"name" => "project",
			"caption" => t("Projekt"),
			// "sortable" => 1,
		));
		$table->define_field (array (
			"name" => "resource",
			"caption" => t("Ressurss"),
			// "sortable" => 1,
		));
		$table->define_field(array(
			"name" => "due_date",
			"type" => "time",
			"format" => MRP_DATE_FORMAT,
			"numeric" => 1,
			"caption" => t("Projekti t&auml;htaeg"),
			"sortable" => 1,
		));
		$table->define_field (array (
			"name" => "minstart",
			"caption" => t("Vara&shy;seim j&auml;tka&shy;mis&shy;aeg"),
		));
		$table->define_field (array (
			"name" => "reschedule",
			"caption" => t("Tagasta planeerimisse"),
		));

		$table->define_field(array(
			"name" => "abort_comment",
			"caption" => t("Katkestamise kommentaar"),
			// "sortable" => 1
		));

		$table->define_field(array(
			"name" => "modify",
			"caption" => t("Ava"),
		));

		$table->set_caption($this->project_list_categories[$this->list_request]);
		$table->set_default_sortby ("due_date");
		$table->set_default_sorder ("asc");
		$table->define_pageselector (array (
			"type" => "lbtxt",
			"d_row_cnt" => $this->projects_list_objects_count,
			"records_per_page" => $this_object->prop("projects_list_objects_perpage") ? $this_object->prop("projects_list_objects_perpage") : 30,
		));

		$jobs = $this->projects_list_objects->arr ();

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
				"project" => html::obj_change_url($project),
				"resource" => html::get_change_url(
					$resource->id(),
					array("return_url" => get_ru()),
					$resource->name ()
				),
				"due_date" => $project->prop ("due_date"),
				"modify" => html::obj_change_url($job, t("Ava")),
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
				"abort_comment" => $this->get_abort_comment_from_job($job)
			);

			$table->define_data($definition);
		}
	}

	function create_schedule_chart ($arr)
	{
		$time =  time();
		$this_object = $arr["obj_inst"];
		$chart = get_instance ("vcl/gantt_chart");
		$columns = (int) (isset($arr["request"]["mrp_chart_length"]) ? $arr["request"]["mrp_chart_length"] : 7);
		$range_start = (int) (isset($arr["request"]["mrp_chart_start"]) ? $arr["request"]["mrp_chart_start"] : $this->get_week_start ());
		$range_end = (int) ($range_start + $columns * 86400);
		$hilighted_project = (int) (isset($arr["request"]["mrp_hilight"]) ? $arr["request"]["mrp_hilight"] : false);
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
		$toplevel_categories->add(new object($this_object->prop ("resources_folder")));

		$mrp_schedule = get_instance(CL_MRP_SCHEDULE);

		for ($category = $toplevel_categories->begin(); !$toplevel_categories->end(); $category = $toplevel_categories->next())
		{
			$id = $category->id();

			if ($id !== $this_object->prop ("resources_folder"))
			{
				$chart->add_row (array (
					"name" => $id,
					"title" => $category->name(),
					"type" => "separator",
				));
			}

			$resource_tree = new object_tree(array(
				"parent" => $id,
				"class_id" => array (CL_MRP_RESOURCE),
				"sort_by" => "objects.jrk",
			));
			$resources = $resource_tree->to_list();

			for ($resource = $resources->begin (); !$resources->end (); $resource = $resources->next ())
			{
				$chart->add_row (array (
					"name" => $resource->id(),
					"title" => $resource->name(),
					"uri" => html::get_change_url(
						$resource->id(),
						array("return_url" => get_ru())
					)
				));

				if (empty($arr["request"]["chart_customer"]))
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
								"colour" => MRP_COLOUR_UNAVAILABLE,
								"url" => "#",
								"layer" => 2,
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

		// ### get jobs in requested range & add bars
		// $res = $this->db_fetch_array (
			// "SELECT MAX(schedule.planned_length), MAX(job.finished-job.started) FROM mrp_job as job ".
			// "LEFT JOIN mrp_schedule schedule ON schedule.oid = job.oid " .
			// "WHERE job.state !=" . MRP_STATUS_DELETED . " AND ".
			// "job.length > 0 AND ".
			// "job.resource > 0 ".
		// "");
		// rsort ($res[0]);
		// $max_length = reset ($res[0]);
		$jobs = array ();

		### job states that are shown in chart past
		$applicable_states = array (
			MRP_STATUS_DONE,
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PAUSED,
		);

		if (!empty($arr["request"]["chart_customer"]))
		{
			$this->db_query (
			"SELECT job.oid,job.project,job.state,job.started,job.finished,job.resource,job.exec_order,schedule.*,o.metadata " .
			"FROM " .
				"mrp_job as job " .
				"LEFT JOIN objects o ON o.oid = job.oid " .
				"LEFT JOIN mrp_schedule schedule ON schedule.oid = job.oid " .
				"LEFT JOIN aliases a_job ON (a_job.source = o.oid AND a_job.reltype = 2) " .
				"LEFT JOIN aliases a_case ON (a_case.source = a_job.target AND a_case.reltype = 2) " .
				"LEFT JOIN objects o_cust ON o_cust.oid = a_case.target " .
			"WHERE " .
				"job.state IN (" . implode (",", $applicable_states) . ") AND " .
				"o_cust.name like '%{$arr["request"]["chart_customer"]}%' AND " .
				"o.status > 0 AND " .
				"o.parent = " . $this_object->prop ("jobs_folder") . " AND " .
				"((!(job.started < ".$range_start.")) OR ((job.state = " . MRP_STATUS_DONE . " AND job.finished > ".$range_start.") OR (job.state != " . MRP_STATUS_DONE . " AND ".$time." > ".$range_start."))) AND " .
				"job.started < ".$range_end." AND " .
				"job.project > 0 AND " .
				"job.length > 0 AND " .
				"job.resource > 0 " .
			"");

			while ($job = $this->db_next())
			{
				if ($this->can("view", $job["oid"]))
				{
					$metadata = aw_unserialize ($job["metadata"]);
					$job["paused_times"] = $metadata["paused_times"];
					$jobs[] = $job;
				}
			}

			// $filt = array (
				// "class_id" => CL_MRP_JOB,
				// "state" => $applicable_states,
				// "parent" => $this_object->prop ("jobs_folder"),
				// "started" => new obj_predicate_compare (OBJ_COMP_BETWEEN, ($range_start - $max_length), $range_end),
				// "resource" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
				// "length" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
				// "project" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
			// );

			// ### filter by customer as well
			// $filt["CL_MRP_JOB.RELTYPE_MRP_PROJECT.RELTYPE_MRP_CUSTOMER.name"] = $arr["request"]["chart_customer"];
			// $list = new object_list ($filt);
			// $list_jobs = $list->arr ();
		}
		else
		{
			$this->db_query (
			"SELECT job.oid,job.project,job.state,job.started,job.finished,job.resource,job.exec_order,schedule.*,o.metadata " .
			"FROM " .
				"mrp_job as job " .
				"LEFT JOIN objects o ON o.oid = job.oid " .
				"LEFT JOIN mrp_schedule schedule ON schedule.oid = job.oid " .
			"WHERE " .
				"job.state IN (" . implode (",", $applicable_states) . ") AND " .
				"o.status > 0 AND " .
				"o.parent = '" . $this_object->prop ("jobs_folder") . "' AND " .
				"((!(job.started < ".$range_start.")) OR ((job.state = " . MRP_STATUS_DONE . " AND job.finished > ".$range_start.") OR (job.state != " . MRP_STATUS_DONE . " AND ".$time." > ".$range_start."))) AND " .
				"job.started < ".$range_end." AND " .
				"job.project > 0 AND " .
				"job.length > 0 AND " .
				"job.resource > 0 " .
			"");

			while ($job = $this->db_next())
			{
				if ($this->can("view", $job["oid"]))
				{
					$metadata = aw_unserialize ($job["metadata"]);
					$job["paused_times"] = $metadata["paused_times"];
					$jobs[] = $job;
				}
			}
		}

		### job states that are shown in chart future
		$applicable_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_ABORTED,
		);

		if (!empty($arr["request"]["chart_customer"]))
		{
			$this->db_query (
			"SELECT job.oid,job.project,job.state,job.started,job.finished,job.resource,job.exec_order,schedule.*,o.metadata " .
			"FROM " .
				"mrp_job as job " .
				"LEFT JOIN objects o ON o.oid = job.oid " .
				"LEFT JOIN mrp_schedule schedule ON schedule.oid = job.oid " .
				"LEFT JOIN aliases a_job ON (a_job.source = o.oid AND a_job.reltype = 2) " .
				"LEFT JOIN aliases a_case ON (a_case.source = a_job.target AND a_case.reltype = 2) " .
				"LEFT JOIN objects o_cust ON o_cust.oid = a_case.target " .
			"WHERE " .
				"job.state IN (" . implode (",", $applicable_states) . ") AND " .
				"o_cust.name like '%".$arr["request"]["chart_customer"]."%' AND " .
				"o.status > 0 AND " .
				"o.parent = " . $this_object->prop ("jobs_folder") . " AND " .
				"schedule.starttime < ".$range_end." AND " .
				"schedule.starttime > ".$time." AND " .
				"((!(schedule.starttime < ".$range_start.")) OR ((schedule.starttime + schedule.planned_length) > ".$range_start.")) AND " .
				"job.project > 0 AND " .
				"job.length > 0 AND " .
				"job.resource > 0 " .
			"");

			while ($job = $this->db_next())
			{
				if ($this->can("view", $job["oid"]))
				{
					$metadata = aw_unserialize ($job["metadata"]);
					$job["paused_times"] = $metadata["paused_times"];
					$jobs[] = $job;
				}
			}

			// $filt = array (
				// "class_id" => CL_MRP_JOB,
				// "parent" => $this_object->prop ("jobs_folder"),
				// "state" => $applicable_states,
				// "starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, ($range_start - $max_length), $range_end),
				// "starttime" => new obj_predicate_compare (OBJ_COMP_GREATER, time ()),
				// "resource" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
				// "length" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
			// );

			// ### filter by customer as well
			// $filt["CL_MRP_JOB.RELTYPE_MRP_PROJECT.RELTYPE_MRP_CUSTOMER.name"] = $arr["request"]["chart_customer"];
			// $list = new object_list ($filt);
			// $list_jobs = array_merge ($list->arr (), $list_jobs);


			// #//!!! arrayks konvertimine, et yhtiks db_queryga saadud asjaga, kui kliendiga koos p2ring tehtud pole seda enam vaja.
			// foreach ($list_jobs as $list_job)
			// {
				// $jobs[] = array (
					// "oid" => $list_job->id (),
					// "paused_times" => $list_job->meta ("paused_times"),
					// "project" => $list_job->prop ("project"),
					// "state" => $list_job->prop ("state"),
					// "started" => $list_job->prop ("started"),
					// "finished" => $list_job->prop ("finished"),
					// "planned_length" => $list_job->prop ("planned_length"),
					// "starttime" => $list_job->prop ("starttime"),
					// "resource" => $list_job->prop ("resource"),
					// "exec_order" => $list_job->prop ("exec_order"),
				// );
			// }
			// #//!!! END arrayks konvertimine
		}
		else
		{
			$this->db_query (
			"SELECT job.oid,job.project,job.state,job.started,job.finished,job.resource,job.exec_order,schedule.*,o.metadata " .
			"FROM " .
				"mrp_job as job " .
				"LEFT JOIN objects o ON o.oid = job.oid " .
				"LEFT JOIN mrp_schedule schedule ON schedule.oid = job.oid " .
			"WHERE " .
				"job.state IN (" . implode (",", $applicable_states) . ") AND " .
				"o.status > 0 AND " .
				"o.parent = '" . $this_object->prop ("jobs_folder") . "' AND " .
				"schedule.starttime < ".$range_end." AND " .
				"schedule.starttime > ".$time." AND " .
				"((!(schedule.starttime < ".$range_start.")) OR ((schedule.starttime + schedule.planned_length) > ".$range_start.")) AND " .
				"job.project > 0 AND " .
				"job.length > 0 AND " .
				"job.resource > 0 " .
			"");

			while ($job = $this->db_next())
			{
				if ($this->can("view", $job["oid"]))
				{
					$metadata = aw_unserialize ($job["metadata"]);
					$job["paused_times"] = $metadata["paused_times"];
					$jobs[] = $job;
				}
			}
		}

		foreach ($jobs as $job)
		{
			if (!$this->can("view", $job["project"]))
			{
				continue;
			}

			$project = obj ($job["project"]);

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
			switch ($job["state"])
			{
				case MRP_STATUS_DONE:
					$start = $job["started"];
					$length = $job["finished"] - $job["started"];
// /* dbg */ echo date(MRP_DATE_FORMAT, $start) . "-" . date(MRP_DATE_FORMAT, $start + $length) . "<br>";
					break;

				case MRP_STATUS_PLANNED:
					$start = $job["starttime"];
					$length = $job["planned_length"];
					break;

				case MRP_STATUS_PAUSED:
				case MRP_STATUS_INPROGRESS:
					$start = $job["started"];
					$length = (($start + $job["planned_length"]) < $time) ? ($time - $start) : $job["planned_length"];
					break;
			}

			$resource = obj ($job["resource"]);
			$job_name = $project->name () . "-" . $job["exec_order"] . " - " . $resource->name ();

			### set bar colour
			$colour = $this->state_colours[$job["state"]];
			$colour = in_array ($job["oid"], $hilighted_jobs) ? MRP_COLOUR_HILIGHTED : $colour;

			$bar = array (
				"id" => $job["oid"],
				"row" => $resource->id (),
				"start" => $start,
				"colour" => $colour,
				"length" => $length,
				"layer" => 0,
				"uri" => aw_url_change_var ("mrp_hilight", $project->id ()),
				"title" => $job_name . " (" . date (MRP_DATE_FORMAT, $start) . " - " . date (MRP_DATE_FORMAT, $start + $length) . ")"
/* dbg */ . " [res:" . $resource->id () . " t&ouml;&ouml;:" . $job["oid"] . " proj:" . $project->id () . "]"
			);

			$chart->add_bar ($bar);

			### add paused bars
			foreach(safe_array($job["paused_times"]) as $pd)
			{
				if ($pd["start"] && $pd["end"])
				{
					$bar = array (
						"row" => $resource->id (),
						"start" => $pd["start"],
						"nostartmark" => true,
						"layer" => 1,
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
			"caption" => t("Voop&otilde;hine tootmisgraafik"),
			"footer" => $this->draw_colour_legend(),
			"subdivisions" => $subdivisions,
			"timespans" => $subdivisions,
			"width" => 850,
			"row_height" => 10,
			"navigation" => $this->create_chart_navigation($arr)
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
		$start = (int) (isset($arr["request"]["mrp_chart_start"]) ? $arr["request"]["mrp_chart_start"] : time ());
		$columns = (int) (isset($arr["request"]["mrp_chart_length"]) ? $arr["request"]["mrp_chart_length"] : 7);
		$start = ($columns === 7) ? $this->get_week_start ($start) : $start;
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
			"url" => aw_url_change_var ("mrp_chart_start", ($this->get_time_days_away (5*$columns, $start, -1))),
		));
		$start_nav[] = html::href (array (
			"caption" => t("Eelmine"),
			"url" => aw_url_change_var ("mrp_chart_start", ($this->get_time_days_away ($columns, $start, -1))),
		));
		$start_nav[] = html::href (array (
			"caption" => t("T&auml;na"),
			"url" => aw_url_change_var ("mrp_chart_start", $this->get_week_start ()),
		));
		$start_nav[] = html::href (array (
			"caption" => t("J&auml;rgmine"),
			"url" => aw_url_change_var ("mrp_chart_start", ($this->get_time_days_away ($columns, $start))),
		));
		$start_nav[] = html::href (array (
			"caption" => t(">>"),
			"title" => t("5 edasi"),
			"url" => aw_url_change_var ("mrp_chart_start", ($this->get_time_days_away (5*$columns, $start))),
		));

		$navigation = sprintf(t('&nbsp;&nbsp;Periood: %s &nbsp;&nbsp;P&auml;evi perioodis: %s'), implode (" ", $start_nav) ,implode (" ", $length_nav));

		if (isset($arr["request"]["mrp_hilight"]) and $this->can("view", $arr["request"]["mrp_hilight"]))
		{
			$project = obj ($arr["request"]["mrp_hilight"]);
			$deselect = html::href (array (
				"caption" => t("Kaota valik"),
				"url" => aw_url_change_var ("mrp_hilight", ""),
			));
			$change_url = html::obj_change_url ($project);
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

			if (2 === count($prop))
			{
				$name = $prop[0];
				$oid = $prop[1];

				if ($this->can("edit", $oid))
				{
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
								$applicable_states = array (
									MRP_STATUS_ABORTED,
								);
								$job = obj ($oid);

								if (in_array ($job->prop ("state"), $applicable_states))
								{
									$job->set_prop ("state", MRP_STATUS_PLANNED);
									$job->save ();
								}
							}
							break;

						case "mrp_resource_order":
							$resource = obj ($oid);
							$resource->set_ord ((int) $value);
							$resource->save ();
							break;
					}
				}
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
			$errors = NULL;
			$res_e = array();
			$jobs_e = array();

			for ($o = $ol->begin (); !$ol->end (); $o = $ol->next ())
			{
				if (CL_MRP_RESOURCE == $o->class_id() and MRP_STATUS_RESOURCE_INUSE != $o->prop("state"))
				{
					$unfinished_jobs = false;
					$c = new connection();
					$c_data = $c->find(array(
						"from.class_id" => CL_MRP_JOB,
						"type" => RELTYPE_MRP_RESOURCE,
						"to" => $o->id()
					));
					$resource_job_ids = array();

					foreach ($c_data as $c_arr)
					{
						$resource_job_ids[] = $c_arr["from"];
					}

					if (count($resource_job_ids))
					{
						$applicable_states = array(
							MRP_STATUS_DONE
						);
						$list = new object_list (array (
							"oid" => $resource_job_ids,
							"class_id" => CL_MRP_JOB,
							"state" => new obj_predicate_not($applicable_states)
						));

						if (0 < $list->count())
						{
							$unfinished_jobs = true;
						}
					}

					if ($unfinished_jobs)
					{
						$unfinished_jobs = array();

						foreach ($list->ids() as $job_id)
						{
							$unfinished_jobs[] = html::get_change_url($job_id, array(), $job_id);
						}

						$res_e[] = $o->name() . " [l&otilde;petamata t&ouml;&ouml;d: " . implode(",", $unfinished_jobs) . "]";
					}
					else
					{
						$o->set_prop("state", MRP_STATUS_RESOURCE_INACTIVE);
						$o->save();
					}
				}
				elseif ($this->can ("delete", $o->id()))
				{
					$o->delete ();
				}
			}

			if (count($res_e))
			{
				$errors .= t("Ei saa arhiveerida, sest on l&otilde;petamata t&ouml;id: "). implode(",", $res_e);
				aw_session_set("mrp_errors", $errors);
			}
		}

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" =>  ($arr["return_url"]),
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

    // @attrib name=get_time_days_away
	// @param days required type=int
	// @param direction optional type=int
	// @param time optional
	// @returns UNIX timestamp for time of day start $days away from day start of $time
	// @comment DST safe if cumulated error doesn't exceed 12h. If $direction is negative, time is computed for days back otherwise days to.
	function get_time_days_away ($days, $time = false, $direction = 1)
	{
		if (false === $time)
		{
			$time = time ();
		}

		$time_daystart = mktime (0, 0, 0, date ("m", $time), date ("d", $time), date("Y", $time));
		$day_start = ($direction < 0) ? ($time_daystart - $days*86400) : ($time_daystart + $days*86400);
		$nodst_hour = (int) date ("H", $day_start);

		if ($nodst_hour)
		{
			if ($nodst_hour < 13)
			{
				$dst_error = $nodst_hour;
				$day_start = $day_start - $dst_error*3600;
			}
			else
			{
				$dst_error = 24 - $nodst_hour;
				$day_start = $day_start + $dst_error*3600;
			}
		}

		return $day_start;
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

		if ($nodst_hour)
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

			$i = get_instance("applications/crm/crm_company_people_impl");
			$i->_get_contact_toolbar($arr);

			$tb = $arr["prop"]["vcl_inst"];
			$tb->remove_button("Kone");
			$tb->remove_button("Kohtumine");
			$tb->remove_button("Toimetus");
			$tb->remove_button("Search");
			$tb->remove_button("important");
			$tb->remove_button(0);
			$tb->remove_button(1);
			$tb->remove_button(2);
			$tb->remove_button(3);

			$arr["obj_inst"] = $tmp;
		}
	}

	function _user_list_tree($arr)
	{
		$this->_delegate_co_v($arr, "_get_unit_listing_tree");
	}

	function _user_list($arr)
	{
		$this->_delegate_co_v($arr, "_get_human_resources");
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

			$i = get_instance("applications/crm/crm_company_people_impl");
			$i->$fun($arr);
			$arr["obj_inst"] = $tmp;
		}
	}

	/** handler for person list delete. forwards to crm_company

		@attrib name=submit_delete_relations

	**/
	function submit_delete_relations($arr)
	{
		$this->_delegate_co($arr, "submit_delete_relations");
		return $arr["return_url"];
	}

	function callback_mod_reforb(&$arr)
	{
		if (isset($_GET["unit"]))
		{
			$arr['unit'] = $_GET["unit"];
		}

		if (isset($_GET["category"]))
		{
			$arr['category'] = $_GET["category"];
		}

		$group = isset($_GET["group"]) ? $_GET["group"] : "";

		if ($group !== "grp_search" && $group !== "grp_search_proj" && $group !== "grp_search_cust")
		{
			$arr['return_url'] = get_ru();
		}

		if (isset($_GET["cat"]))
		{
			$arr['cat'] = $_GET["cat"];
		}

		if (isset($_GET["pj_job"]))
		{
			$arr['pj_job'] = $_GET["pj_job"];
		}

		if (isset($_GET["mrp_tree_active_item"]))
		{
			$arr['mrp_tree_active_item'] = $_GET["mrp_tree_active_item"];
		}

		aw_register_header_text_cb(array(&$this, "make_aw_header"));

		if ($group !== "grp_worksheet")
		{
			$arr["post_ru"] = post_ru();
		}

		if ($group === "grp_worksheet")
		{
			$arr["return_url"] = NULL;
		}
	}

	/** cuts the selected person objects

		@attrib name=cut_p

	**/
	function cut_p($arr)
	{
		return $this->_delegate_co($arr, "cut_p");
	}

	/** marks persons as important

		@attrib name=mark_p_as_important

	**/
	function mark_p_as_important($arr)
	{
		return $this->_delegate_co($arr, "mark_p_as_important");
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
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"caption" => t("Salvesta"),
			"action" => "submit_user_mgr_save"
		));
	}

	function _user_mgr_tree($arr)
	{
		$this->_delegate_co_v($arr, "_get_unit_listing_tree");
		// remove all professions from the tree
		$tv = $arr["prop"]["vcl_inst"];
		$tv->remove_item(CRM_ALL_PERSONS_CAT);

		foreach($tv->get_item_ids() as $id)
		{
			$item = $tv->get_item($id);
			if ((int) $item["class_id"] === CL_CRM_PROFESSION)
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
		$t = $arr["prop"]["vcl_inst"];
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
		$t->set_caption(sprintf(t("Ametikohad osakonnas '%s'"), $unit->name()));

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
		if (!$this->can("view", $arr["unit"]))
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
			if ($arr["all_resources"][$k] != $v)
			{
				$oldal[$k] = $arr["all_resources"][$k];
			}
		}

		foreach(safe_array($arr["all_resources"]) as $k => $v)
		{
			if ($arr["all_resources"][$k] != $arr["old_all_resources"][$k])
			{
					$oldal[$k] = $arr["all_resources"][$k];
			}
		}

		$o->set_meta("umgr_all_resources", $oldal);

		$oldal = safe_array($o->meta("umgr_dept_resources"));
		foreach(safe_array($arr["old_dept_resources"]) as $k => $v)
		{
			if ($arr["dept_resources"][$k] != $v)
			{
				$oldal[$k] = $arr["dept_resources"][$k];
			}
		}
		foreach(safe_array($arr["dept_resources"]) as $k => $v)
		{
			if ($arr["dept_resources"][$k] != $arr["old_dept_resources"][$k])
			{
					$oldal[$k] = $arr["dept_resources"][$k];
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

		$t = $arr["prop"]["vcl_inst"];

		$t->add_menu_button(array(
			"name" => "add_menu",
			"tooltip" => t("Uus"),
		));

		$t->add_menu_item(array(
			"parent" => "add_menu",
			"text" => t('Lisa kategooria'),
			"link" => html::get_new_url(CL_CRM_CATEGORY, $co->id(), array(
				"alias_to" => (empty($arr["request"]["cat"]) ? $co->id() : $arr["request"]["cat"]),
				"reltype" => (empty($arr["request"]["cat"]) ? 30 : 2),
				"return_url" => get_ru()
			)),
		));

		// if (false && is_oid($arr["request"]["cat"]))
		if (isset($arr["request"]["cat"]) and is_oid($arr["request"]["cat"]))
		{
			$t->add_menu_item(array(
				"parent" => "add_menu",
				"text" => t('Lisa klient'),
				"link" => html::get_new_url(CL_CRM_COMPANY, $co->id(), array(
					"alias_to" => ($arr["request"]["cat"] ? $arr["request"]["cat"] : $co->id()),
					"reltype" => 3,
					"return_url" => get_ru()
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
		if (!is_object($co))
		{
			return;
		}

		$t = $arr["prop"]["vcl_inst"];
		classload("core/icons");

		$t->set_root_name(t("Kliendid"));
		$t->set_root_url(aw_url_change_var(array(
			"cat" => null,
			"cust" => null
		), false, get_ru()));

		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$nm = $c->prop("to.name");
			$t->add_item(0, array(
				"id" => $c->prop("to"),
				"name" => (isset($arr["request"]["cat"]) and $arr["request"]["cat"] == $c->prop("to")) ? "<b>".$nm."</b>" : $nm,
				"url" => aw_url_change_var(array(
					"cat" => $c->prop("to"),
					"cust" => null
				), false, get_ru()),
			));
			$this->_req_create_customers_tree($c->to(), $t);
		}

		if (isset($arr["request"]["cs_name"]))
		{
			$t->set_selected_item(0); // doesn't work here. cat&cust select clearing for search request solved by adding js in cs_submit get_prop call.
		}
		elseif (!empty($arr["request"]["cust"]))
		{
			$t->set_selected_item($arr["request"]["cust"]);
		}
		elseif (!empty($arr["request"]["cat"]))
		{
			$t->set_selected_item($arr["request"]["cat"]);
		}
	}

	function _req_create_customers_tree($co, &$t)
	{
		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$nm = $c->prop("to.name");
			$t->add_item($co->id(), array(
				"id" => $c->prop("to"),
				"name" => (isset($arr["request"]["cat"]) and $arr["request"]["cat"] == $c->prop("to")) ? "<b>".$nm."</b>" : $nm,
				"url" => aw_url_change_var(array(
					"cat" => $c->prop("to"),
					"cust" => null
				), false, get_ru()),
			));
			$this->_req_create_customers_tree($c->to(), $t);
		}

		foreach($co->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
		{
			$nm = $c->prop("to.name");
			$t->add_item($co->id(), array(
				"id" => $c->prop("to"),
				"name" => (isset($arr["request"]["cust"]) and $arr["request"]["cust"] == $c->prop("to")) ? "<b>".$nm."</b>" : $nm,
				"url" => aw_url_change_var(array(
					"cust" => $c->prop("to"),
					"cat" => null
				), false, get_ru()),
				"iconurl" => icons::get_icon_url($c->prop("to.class_id"))
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
		$retval = PROP_IGNORE;

		if (empty($arr["request"]["cust"]) and !isset($arr["request"]["cs_name"]))
		{
			$t = $arr["prop"]["vcl_inst"];
			$this->_init_cust_list_t($t);

			if (isset($arr["request"]["cat"]) and is_oid($arr["request"]["cat"]))
			{
				// get customers from cat
				$cat = obj($arr["request"]["cat"]);
				$t->set_caption(sprintf(t("Kliendid kategoorias '%s'"), $cat->name()));

				foreach($cat->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
				{
					$addr = "";

					$cust = $c->to();

					$t->define_data(array(
						"name" => html::get_change_url($c->prop("to"), array("return_url" => get_ru()), $c->prop("to.name")),
						"address" => $cust->prop_str("contact"),
						"phone" => $cust->prop_str("phone_id"),
						"email" => $cust->prop_str("email_id"),
						"oid" => $cust->id(),
						"priority" => $cust->prop("priority")
					));
				}
			}

			$retval = PROP_OK;
		}

		return $retval;
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
		if (isset($arr["request"]["cust"]) and is_oid($arr["request"]["cust"]) and !isset($arr["request"]["cs_name"]))
		{
			$t = $arr["prop"]["vcl_inst"];
			$this->_init_cust_list_proj_t($t);

			$cust = obj($arr["request"]["cust"]);
			$ol = new object_list(array(
				"class_id" => CL_MRP_CASE,
				"customer" => $cust->id()
			));
			foreach($ol->arr() as $case)
			{
				$t->define_data(array(
					"name" => html::obj_change_url($case),
					"comment" => $case->comment(),
					"start" => $case->prop("starttime"),
					"end" => $case->prop("due_date"),
					"planned" => $case->prop("planned_date")
				));
			}
			$t->set_default_sortby("name");
			$t->set_caption(sprintf(t("Kliendi '%s' projektid"), $cust->name()));
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

	function _init_printer_jobs_t(&$t, $grp)
	{
		if ("grp_printer_done" == $grp)
		{
			$t->define_field(array(
				"name" => "tm_end",
				"caption" => t("L&otilde;pp"),
				"type" => "time",
				"align" => "center",
				"format" => "d.m.y H:i",
				"numeric" => 1,
				"chgbgcolor" => "bgcol",
			));
		}
		else
		{
			$t->define_field(array(
				"name" => "tm",
				"caption" => t("Algus"),
				"type" => "time",
				"align" => "center",
				"format" => "d.m.y H:i",
				"numeric" => 1,
				"chgbgcolor" => "bgcol",
				"sortable" => 1,
				"nowrap" => 1
			));
		}

		$t->define_field(array(
			"name" => "length",
			"caption" => t("Pikkus"),
			"align" => "center",
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
                        "name" => "proj_comment",
                        "caption" => t("Projekti nimi"),
                        "align" => "center",
                        "chgbgcolor" => "bgcol",
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
		));
	}

	function _printer_jobs($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$grp = $arr["prop"]["group"];
		$this->_init_printer_jobs_t($t, $grp);

		$res = $this->get_cur_printer_resources(array(
			"ws" => $arr["obj_inst"]
		));

		$per_page = $arr["obj_inst"]->prop("pv_per_page");
		$proj_states = false;
		$page = isset($arr["request"]["printer_job_page"]) ? (int) $arr["request"]["printer_job_page"] : 0;
		$limit = ($page*$per_page).",".$per_page;

		switch ($arr["request"]["group"])
		{
			case "grp_printer_done":
				$states = array(MRP_STATUS_DONE);
				$default_sortby = "mrp_job.started";
				if (empty($arr["request"]["sort_order"]))
				{
					$arr["request"]["sort_order"] = "desc";
				}

				if (isset($arr["request"]["sortby"]) and $arr["request"]["sortby"] === "tm")
				{
					$arr["request"]["sortby"] = "started";
				}
				break;

			case "grp_printer_aborted":
				$states = array(MRP_STATUS_ABORTED);
				$default_sortby = "mrp_job.started";
				break;

			case "grp_printer":
			case "grp_printer_current":
				$default_sortby = "mrp_schedule.starttime";
				$states = array(MRP_STATUS_PLANNED,MRP_STATUS_INPROGRESS,MRP_STATUS_PAUSED);
				$proj_states = array(MRP_STATUS_NEW,MRP_STATUS_PLANNED,MRP_STATUS_INPROGRESS,MRP_STATUS_PAUSED);
				break;

			case "grp_printer_in_progress":
				$default_sortby = "mrp_schedule.starttime";
				$states = array(MRP_STATUS_INPROGRESS,MRP_STATUS_PAUSED);
				break;

			case "grp_printer_startable":
				$default_sortby = "mrp_schedule.starttime";
				$states = array(MRP_STATUS_PLANNED,MRP_STATUS_INPROGRESS,MRP_STATUS_PAUSED);
				$proj_states = array(MRP_STATUS_NEW,MRP_STATUS_PLANNED,MRP_STATUS_INPROGRESS,MRP_STATUS_PAUSED);
				$limit = (((int)$arr["request"]["printer_job_page"])*$per_page).",200";
				break;

			case "grp_printer_notstartable":
				$default_sortby = "mrp_schedule.starttime";
                                $states = array(MRP_STATUS_PLANNED,MRP_STATUS_PAUSED);
				$proj_states = array(MRP_STATUS_NEW,MRP_STATUS_PLANNED,MRP_STATUS_INPROGRESS,MRP_STATUS_PAUSED);
				$limit = (((int)$arr["request"]["printer_job_page"])*$per_page).",200";
				break;
		}

		$sby = isset($arr["request"]["sortby"]) ? $arr["request"]["sortby"] : "";
		if ($sby === "")
		{
			$sby = $default_sortby;
		}
		else
		{
			// map to db table
			switch($sby)
			{
				case "started":
					$sby = "mrp_job.started";
					break;

				case "tm":
					$sby = "mrp_schedule.starttime";
					break;

				case "length":
					$sby = "mrp_job.length";
					break;

				case "status":
					$sby = "mrp_job.state";
					break;

				case "project":
					$sby = "CAST(objects_826_project.name AS UNSIGNED)";
					break;

				case "job_comment":
					$sby = "objects.comment";
					break;

				case "resource":
					$sby = "mrp_job.resource";
					break;

				case "customer":
					$sby = "objects_828_customer.name";
					break;
			}
		}

		if ($sby !== "" && !empty($arr["request"]["sort_order"]))
		{
			$sort_order = strtoupper($arr["request"]["sort_order"]);
			$sort_order = "ASC" === $sort_order ? "ASC" : "DESC";
			$sby .= " " . $sort_order;
		}

		classload("core/date/date_calc");

		// now, if the session contans [mrp][do_pv_proj_s] then we must get a list of all the jobs in the current view
		// then iterate them until we find a job with the requested project
		// and then figure out the page number and finally, redirect the user to that page.
		// this sort of sucks, but I can't figure out how to do the count in sql..
		if (!empty($_SESSION["mrp"]["do_pv_proj_s"]))
		{
			// this needs to get done abit differently - we need to find all the jobs
			// that are part of this project for the current resource(s)
			// and if none are under the current tab, then switch to another tab,
			// where they can be found
			// so, list the jobs
			// but first we need the oid of the project
			$proj2oid = new object_list(array("name" => $_SESSION["mrp"]["do_pv_proj_s"], "class_id" => CL_MRP_CASE));
			if ($proj2oid->count() && count($res))
			{
				$s_proj = $proj2oid->begin();

				$q = "SELECT * FROM mrp_job WHERE
					project = '".$s_proj->id()."' AND
					resource IN (".join(",", $res).")";
				$this->db_query($q);

				$f_j_state = NULL;
				$view = "";
				while ($row = $this->db_next())
				{
					if ($f_j_state === NULL)
					{
						$f_j_state = $row["state"];
					}

					if (in_array($row["state"], $states))
					{
						$view = $_GET["group"];
					}
				}

				if ($view === "")
				{
					switch($f_j_state)
					{
						case MRP_STATUS_DONE:
							$view = "grp_printer_done";
							break;

						case MRP_STATUS_ABORTED:
							$view = "grp_printer_aborted";
							break;

						default:
							$view = "grp_printer";
					}
				}

				if ($view !== $_GET["group"])
				{
					header("Location: ".aw_url_change_var("group", $view));
					die();
				}

				$find_proj = $_SESSION["mrp"]["do_pv_proj_s"];
				unset($_SESSION["mrp"]["do_pv_proj_s"]);
				$_SESSION["mrp"]["pv_s_hgl"] = $s_proj->id();
				$jobs = $this->get_next_jobs_for_resources(array(
					"resources" => $res,
					"states" => $states,
					"sort_by" => $sby,
					"proj_states" => $proj_states
				));

				$count = 0;
				foreach($jobs as $job)
				{
					$count++;
					$proj = obj($job->prop("project"));
					if ($proj->name() == $find_proj)
					{
						$page = floor($count / $per_page);
						header("Location: ".aw_url_change_var("printer_job_page", $page));
						die();
					}
				}
			}
		}

		$jobs = $this->get_next_jobs_for_resources(array(
			"resources" => $res,
			"limit" => $limit,
			"states" => $states,
			"sort_by" => $sby,
			"proj_states" => $proj_states
		));

		$workers = $this->get_workers_for_resources($res);

		$mrp_job = get_instance(CL_MRP_JOB);
		$mrp_case = get_instance(CL_MRP_CASE);

		$cnt = 0;
		foreach($jobs as $job)
		{
			if (!$this->can("view", $job->prop("project")))
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
					$workers_str[] = html::obj_change_url($person);
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
					$custo = html::obj_change_url($cust);
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
			elseif ($job->prop("state") == MRP_STATUS_INPROGRESS)
			{
				$bgcol = $this->pj_colors["can_not_start"];
			}
			elseif ($mrp_job->can_start(array("job" => $job->id())))
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

			if ($arr["request"]["group"] === "grp_printer_startable" && $bgcol == $this->pj_colors["can_not_start"])
			{
				continue;
			}

			if ($arr["request"]["group"] === "grp_printer_notstartable" && ($bgcol == $this->pj_colors["can_start"] || $cnt > 5))
			{
				continue;
			}

			if ($job->prop("project") == $_SESSION["mrp"]["pv_s_hgl"])
			{
				$bgcol = $this->pj_colors["search_result"];
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
				case "grp_printer_startable":
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
				$resource_str = html::obj_change_url($res);
			}

			$project_str = $proj->name();
			$proj_com = $proj->comment();
			if ($this->can("edit", $proj->id()))
			{
				$proj_com = html::get_change_url(
					$proj->id(),
					array("return_url" => get_ru()),
					parse_obj_name($proj->comment())
				);
			}

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
					"caption" => "<span style=\"font-size: 15px;\">".t("Ava")."</span>",
					"url" => aw_url_change_var(array(
						"pj_job" =>  $job->id(),
						"return_url" => get_ru()
					)),
				)),
				"resource" => $resource_str,
				"worker" => join(", ",$workers_str),
				"project" => $project_str,
				"project_id" => $proj->id(),
				"proj_pri" => $proj->prop("project_priority"),
				"proj_comment" => $proj_com,
				"customer" => $custo,
				"status" => $state,
				"bgcol" => $bgcol,
				"job_comment" => $comment
			));
		}

		if ("grp_printer_done" === $grp)
		{
			$t->set_default_sortby("tm_end");
			if (aw_global_get("sortby") == "tm")
			{
				aw_global_set("sortby", "tm_end");
			}
		}
		else
		{
			$t->set_default_sortby("tm");
		}

		if (isset($arr["request"]["sort_order"]) and $arr["request"]["sort_order"] === "desc")
		{
			$t->set_default_sorder("desc");
		}

		$t->sort_by();
		$t->set_sortable(false);
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
			if ($this->can("view", $op->prop("resource")))
			{
				$reso = obj($op->prop("resource"));

				if (in_array($reso->prop("state"), $this->active_resource_states))
				{
					$ret[] = $reso->name();
				}
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
			if ($this->can("view", $op->prop("resource")))
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

					foreach ($ol->arr() as $res_o)
					{
						if (!in_array($res_o->prop("state"), $this->active_resource_states))
						{
							$ol->remove($res_o);
						}
					}

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

		foreach ($ret as $res_oid)
		{
			$res_o = new object($res_oid);

			if (!in_array($res_o->prop("state"), $this->active_resource_states))
			{
				unset($ret[$res_oid]);
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
	private function get_next_jobs_for_resources($arr)
	{
		if (!isset($arr["resources"]) || !is_array($arr["resources"]) || count($arr["resources"]) === 0)
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
			"starttime" => new obj_predicate_compare(OBJ_COMP_GREATER, -1),
//!!!
			"sort_by" => isset($arr["sort_by"]) ? $arr["sort_by"] : "mrp_schedule.starttime",
//!!!
		);

		if (!empty($arr["states"]))
		{
			$filt["state"] = $arr["states"];
		}
//!!!
		$filt["CL_MRP_JOB.project(CL_MRP_CASE).name"] = "%";
		// this also does or is null, cause the customer can be null
		// $filt["CL_MRP_JOB.project(CL_MRP_CASE).customer.name"] = new obj_predicate_not(1);

		if ($arr["proj_states"])
		{
			$filt["CL_MRP_JOB.project(CL_MRP_CASE).state"] = $arr["states"];
		}
//!!!
		$jobs = new object_list($filt);
		$ret = array();
		foreach($jobs->arr() as $o)
		{
			if ($this->can("view", $o->prop("resource")))
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
			"confirm" => t("Oled kindel et soovid kommentaari salvestada?"),
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
		$job_id = $arr["id"];

		$ud = parse_url($j->abort($arr));
		$pars = array();
		parse_str($ud["query"], $pars);
		$this->dequote($pars["errors"]);
		$errs = unserialize($pars["errors"]);

		if (is_array($errs) && count($errs))
		{
			aw_session_set("mrpws_err", $errs);
		}

		aw_session_set ("mrp_printer_aborted", $job_id);

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
		$this->quote(&$comment);
		$this->db_query("INSERT INTO mrp_log (
					project_id,job_id,uid,tm,message,comment
				)
				values(
					".((int)$proj).",".((int)$job).",'".aw_global_get("uid")."',".time().",'$msg','$comment'
				)
		");
	}

	function safe_settype_float ($value)
	{
		$separators = ".,";
		$int = (int) preg_replace ("/\s*/S", "", strtok ($value, $separators));
		$dec = preg_replace ("/\s*/S", "", strtok ($separators));
		return (float) ("{$int}.{$dec}");
	}

	/**

		@attrib name=cut_resources

	**/
	function cut_resources($arr)
	{
		$_SESSION["mrp_workspace"]["cut_resources"] = safe_array($arr["selection"]);
		return $arr["return_url"];
	}

	/**

		@attrib name=copy_resources

	**/
	function copy_resources($arr)
	{
		$_SESSION["mrp_workspace"]["copied_resources"] = safe_array($arr["selection"]);
		return $arr["return_url"];
	}

	/**

		@attrib name=paste_resources

	**/
	function paste_resources($arr)
	{
		foreach(safe_array($_SESSION["mrp_workspace"]["cut_resources"]) as $resource)
		{
			if ($this->can("edit", $resource))
			{
				$o = obj($resource);
				$o->set_parent($arr["mrp_tree_active_item"]);
				$o->save();
			}
		}
		unset($_SESSION["mrp_workspace"]["cut_resources"]);

		foreach(safe_array($_SESSION["mrp_workspace"]["copied_resources"]) as $resource)
		{
			if ($this->can("view", $resource) && $this->can("add", $arr["mrp_tree_active_item"]))
			{
				$o = obj($resource);
				$o->set_parent($arr["mrp_tree_active_item"]);
				$o->save_new();
			}
		}
		unset($_SESSION["mrp_workspace"]["copied_resources"]);

		return $arr["return_url"];
	}

	function callback_on_load($arr)
	{
		$o = obj($arr["request"]["id"]);
		if ($this->can("view", $o->prop("workspace_configmanager")))
		{
			$this->cfgmanager = $o->prop("workspace_configmanager");
		}
	}

	function callback_get_cfgmanager($arr)
	{
		$o = obj($arr["request"]["id"]);
		if ($this->can("view", $o->prop("workspace_configmanager")))
		{
			return $o->prop("workspace_configmanager");
		}
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] === "grp_login_select_res")
		{
			return false;
		}

		if ($arr["request"]["group"] === "grp_login_select_res")
		{
			unset($arr["classinfo"]["relationmgr"]);
			return false;
		}
		return true;
	}

	function priority_field_callback ($row)
	{
		$applicable_lists = array (
			"planned",
			"planned_overdue",
			"overdue",
			"inwork",
		);

		if (in_array($this->list_request, $applicable_lists))
		{
			$cellcontents = html::textbox (array (
				"name" => "mrp_project_priority-" . $row["project_id"],
				"size" => "5",
				"textsize" => "12px",
				"value" => $row["priority"],
			));
		}
		else
		{
			$cellcontents = $row["priority"];
		}

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
		$retval = PROP_IGNORE;

		if (!empty($arr["request"]["sp_search"]))
		{
			$filt = array("class_id" => CL_MRP_CASE);

			// parse search args
			/// name
			if (!empty($arr["request"]["sp_name"]))
			{
				$filt["name"] = "%".$arr["request"]["sp_name"]."%";
			}

			/// comment
			if (!empty($arr["request"]["sp_comment"]))
			{
				$filt["comment"] = "%".$arr["request"]["sp_comment"]."%";
			}

			/// customer
			if (!empty($arr["request"]["sp_customer"]))
			{
				$filt["CL_MRP_CASE.customer.name"] = "%".$arr["request"]["sp_customer"]."%";
			}

			/// min due date
			if (!empty($arr["request"]["sp_due_date"]))
			{
				$tmp = date_edit::get_timestamp($arr["request"]["sp_due_date"]);
				if ($tmp > 100)
				{
					$filt["due_date"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $tmp);
				}
			}

			/// min starttime
			if (!empty($arr["request"]["sp_starttime"]))
			{
				$tmp = date_edit::get_timestamp($arr["request"]["sp_starttime"]);
				if ($tmp > 100)
				{
					$filt["starttime"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $tmp);
				}
			}

			/// prj status
			if (!empty($arr["request"]["sp_status"]))
			{
				$filt["state"] = $arr["request"]["sp_status"];
			}

			// create objlist
			if (1 === count($filt))
			{
				$results = new object_list();
			}
			else
			{
				$results = new object_list($filt);
			}

			$this->list_request = "search";
			$this->projects_list_objects_count = $results->count();
			$arr["search_res"] = $results;
			$this->create_projects_list($arr);
			$retval = PROP_OK;
		}

		return $retval;
	}

	function _cs_result($arr)
	{
		$retval = PROP_IGNORE;

		if (isset($arr["request"]["cs_name"]))
		{
			$t = $arr["prop"]["vcl_inst"];
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
					$filt["CL_CRM_COMPANY.firmajuht(CL_CRM_PERSON).name"] = "%".$arr["request"]["cs_firmajuht"]."%";
				}
				if ($arr["request"]["cs_contact"] != "")
				{
					$filt["CL_CRM_COMPANY.firmajuht(CL_CRM_PERSON).name"] = "%".$arr["request"]["cs_contact"]."%";
				}
				if ($arr["request"]["cs_phone"] != "")
				{
					$filt["CL_CRM_COMPANY.phone_id(CL_CRM_PHONE).name"] = "%".$arr["request"]["cs_phone"]."%";
				}
				$results = new object_list($filt);
			}

			$t->set_caption(t("Klientide otsingu tulemused"));

			$csn = array();
			foreach($results->arr() as $cust)
			{
				if (isset($csn[$cust->name()]))
				{
					continue;
				}
				$csn[$cust->name()] = 1;
				$t->define_data(array(
					"name" => html::obj_change_url($cust),
					"address" => $cust->prop_str("contact"),
					"phone" => $cust->prop_str("phone_id"),
					"email" => $cust->prop_str("email_id"),
					"oid" => $cust->id(),
					"priority" => $cust->prop("priority")
				));
			}
			$retval = PROP_OK;
		}

		return $retval;
	}

	function draw_colour_legend ()
	{
		$dfn = "";
		$rows = "";
		$i = 1;
		$state_colours = $this->state_colours;
		$state_colours["hilighted"] = MRP_COLOUR_HILIGHTED;
		$state_colours["unavailable"] = MRP_COLOUR_UNAVAILABLE;
		$states = $this->states;
		$states["hilighted"] = t("Valitud projekt");
		$states["unavailable"] = t("Kinnine aeg");

		foreach ($state_colours as $state => $colour)
		{
			$name = $states[$state];
			$dfn .= '<td class="awmenuedittabletext" style="background-color: ' . $colour . '; width: 30px;">&nbsp;</td><td class="awmenuedittabletext" style="padding: 0px 15px 0px 6px;">' . $name . '</td>';

			if (!(($i++)%5))
			{
				$rows .= '<tr>' . $dfn . '</tr>';
				$dfn = "";
			}
		}

		$rows .= '<tr>' . $dfn . '</tr>';
		return '<table cellspacing="4" cellpadding="0">' . $rows . '</table>';
	}

	function _get_subcontract_job_list($this_object, $limit = NULL)
	{
		if (empty($this->subcontractor_resource_ids))
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
			$this->subcontractor_resource_ids = $list->ids ();
		}

		if (!empty ($this->subcontractor_resource_ids))
		{
			$applicable_states = array (
				MRP_STATUS_NEW,
				MRP_STATUS_PLANNED,
			);
			$sort_by = NULL;

			if ($limit)
			{
				$sort_order = ("desc" == $arr["request"]["sort_order"]) ? "desc" : "asc";
				$sort_by = "mrp_case.starttime"; // default sort order
				$tmp = NULL;

				switch ($arr["request"]["sortby"])
				{
					case "scheduled_date": // for aborted jobs list
					default:
						$sort_by = "mrp_schedule.starttime {$sort_order}";
						$tmp = new obj_predicate_compare (OBJ_COMP_GREATER, 0);//!!! temporary. acceptable solution needed. jobs with starttime NULL not retrieved.
						break;
				}
			}

			$list = new object_list (array (
				"class_id" => CL_MRP_JOB,
				"state" => $applicable_states,
				"resource" => $this->subcontractor_resource_ids,
				"parent" => $this_object->prop ("jobs_folder"),
				"starttime" => $tmp,// !!!
				"limit" => $limit,
				"sort_by" => $sort_by,
			));
			return $list;
		}
		else
		{
			return new object_list();
		}
	}

	protected function _chart_search($arr)
	{
		if (isset($arr["request"]["mrp_hilight"]) and $this->can("view", $arr["request"]["mrp_hilight"]) and empty($arr["request"]["chart_project_hilight"]))
		{
			$o = obj($arr["request"]["mrp_hilight"]);
			$arr["request"]["chart_project_hilight"] = $o->name();
		}

		$str  = t("<i>Valitud projekt</i>");
		$str .= " ";
		$str .= html::textbox(array(
			"name" => "chart_project_hilight",
			"value" => isset($arr["request"]["chart_project_hilight"]) ? $arr["request"]["chart_project_hilight"] : "",
			"size" => 6
		));
		$str .= " ";
		$str .= t("<i>Klient</i>");
		$str .= " ";
		$str .= html::textbox(array(
			"name" => "chart_customer",
			"value" => isset($arr["request"]["chart_customer"]) ? $arr["request"]["chart_customer"] : ""
		));

		$spl = $this->mk_my_orb("cust_search_pop", array("id" => $arr["obj_inst"]->id()));
		$str .= " <a href='javascript:void(0)' onClick='aw_popup_scroll(\"$spl\",\"_spop\",300,400)'>Otsi kliente</a>";
		$str .= "<script language='javascript'>function setLink(n) { document.changeform.chart_customer.value = n; document.changeform.submit();}</script>";
		$arr["prop"]["value"] = $str;
	}

	/**

		@attrib name=cust_search_pop

		@param s_name optional
		@param s_content optional
	**/
	function cust_search_pop($arr)
	{
		$s_name = $arr["s_name"];
		$s_content = $arr["s_content"];
		$this->read_template("csp.tpl");
		if (!(empty($s_name) and empty($s_content)))
		{
			$t = new aw_table(array(
				"layout" => "generic"
			));
			$t->define_field(array(
				"name" => "name",
				"caption" => t("Nimetus"),
				"sortable" => 1
			));
			$t->define_field(array(
				"name" => "pick",
				"caption" => t("Vali see"),
			));

			$sres = new object_list(array(
				"class_id" => CL_CRM_COMPANY,
				"name" => "%".$s_name."%",
			));
			for ($o = $sres->begin(); !$sres->end(); $o = $sres->next())
			{
				$name = strip_tags($o->name());
				$name = str_replace("'","",$name);

				$row["pick"] = html::href(array(
					"url" => 'javascript:ss("'.str_replace("'", "&#39;", $o->name()).'")',
					"caption" => t("Vali see")
				));
				$row["name"] = html::obj_change_url($o);
				$t->define_data($row);

			}

			$t->set_default_sortby("name");
			$t->sort_by();
			$this->vars(array("LINE" => $t->draw()));
		}
		else
		{
			$s_name = "%";
			$s_content = "%";
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("cust_search_pop", array("reforb" => 0)),
			"s_name"	=> $s_name,
			"doc_sel" => checked($s_class_id != "item"),
		));
		exit($this->parse());
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

	function _pjp_case_wf($arr)
	{
		$case = get_instance(CL_MRP_CASE);
		$arr["no_edit"] = 1;
		$job = obj($arr["request"]["pj_job"]);
		$arr["obj_inst"] = obj($job->prop("project"));
		$case->create_workflow_table($arr);
	}

	function _ws_tbl($arr)
	{
		if (!$arr["request"]["MAX_FILE_SIZE"])//??? milleks?
		{
			return;
		}

		$t = $arr["prop"]["vcl_inst"];
		$res = get_instance(CL_MRP_RESOURCE);
		$res->_init_job_list_table($t);
		$t->define_field(array(
			"name" => "resource",
			"caption" => t("Resurss"),
			"sortable" => 1,
			"align" => "center"
		));
		$t->set_default_sortby ("starttime");
		$t->set_default_sorder ("asc");

		$applicable_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_PAUSED,
			MRP_STATUS_INPROGRESS
		);

		$from = date_edit::get_timestamp($arr["request"]["ws_from"]);
		$to = date_edit::get_timestamp($arr["request"]["ws_to"]) + 24 * 3600;

		$list = new object_list(array(
			"class_id" => CL_MRP_JOB,
			"resource" => $arr["request"]["ws_resource"],
			"state" => $applicable_states,
			"starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, $from, $to),
		));

		$res->draw_job_list_table_from_list($t, $list);
	}

	function get_abort_comment_from_job($job)
	{
		$hist = safe_array($job->meta("change_comment_history"));
		return $hist[0]["text"]." (".$hist[0]["uid"].")";
	}

	/**
		@attrib name=archive_projects
		@param id required type=int
	**/
	function archive_projects($arr)
	{
		// aw_switch_user("struktuur");//!!! prismas vist pole seda kasutajat. yldiselt peaks see olema ja seda siin kasutama. [voldemar 9/14/06]
		// aw_switch_user("kix");//!!! ajutiselt v2lja -- prismas probleem aw_switch_user-iga [voldemar 9/14/06]

		if (!$this->can("view", $arr["id"]))
		{
			$scheduler->add(array(
				"event" => $event,
				"time" => time() + 86400,
				"uid" => aw_global_get("uid"),
				"auth_as_local_user" => true,
			));
			exit (1);
		}

		$this_object = obj($arr["id"]);
		$aap = $this_object->prop("automatic_archiving_period");

		if (!$aap)
		{
			exit (2);
		}

		### archive projects finished before now minus autoarchive period
		$a_time = time() - $aap * 86400;

		$projects = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"state" => MRP_STATUS_DONE,
			"parent" => $this_object->prop ("projects_folder"),
			"finished" => new obj_predicate_compare(OBJ_COMP_BETWEEN, 10, $a_time),
		));

//!!! ajutine
foreach ($projects->arr() as $project)
{
		$project->set_prop("archived", time());
		$project->set_prop("state", MRP_STATUS_ARCHIVED);
		$project->save();
}

/* ajutiselt v2lja -- prismas ei t88ta objlist->set_prop miskip2rast. [voldemar 9/14/06]
		$projects->set_prop("archived", time());
		$projects->set_prop("state", MRP_STATUS_ARCHIVED);
		$projects->save();

END ajutine
*/

		### add next archiving event to scheduler
		$scheduler = get_instance("scheduler");
		$event = $this->mk_my_orb("archive_projects", array("id" => $this_object->id()));

		$scheduler->add(array(
			"event" => $event,
			"time" => time() + 86400,
			"uid" => aw_global_get("uid"),
			"auth_as_local_user" => true,
		));

		exit (0);
	}

	/**
		@attrib name=get_projects_subtree
		@param id required type=int
		@param parent required
		@param url required
	**/
	function get_projects_subtree($arr)
	{
		if (strstr($arr["parent"], "archived"))
		{
			$url = $arr["url"];
			$period_data = explode("_", $arr["parent"]);
			$bottom_level = isset($period_data[1]);
			$this_object = obj($arr["this"]);

			$tree = get_instance("vcl/treeview");
			$tree->start_tree (array (
				"type" => TREE_DHTML,
				"tree_id" => "projecttree",
				"has_root" => false,
				"persist_state" => 0,
			));
			$tree->set_only_one_level_opened (true);

			# get items
			if ($bottom_level)
			{ ## by month
				$period = 0;
				$end = 12;
			}
			else
			{ ## by year
				$period = 2003;
				$end = date("Y");
			}

			while (++$period <= $end)
			{
				$start_month = $bottom_level ? $period : 1;
				$start_year = $bottom_level ? $period_data[1] : $period;
				$end_month = $bottom_level ? ((12 == $start_month) ? 1 : ($start_month + 1)) : 1;
				$end_year = $bottom_level ? ((12 == $start_month) ? ($start_year + 1) : $start_year) : ($start_year + 1);

				$list = new object_list (array (
					"class_id" => CL_MRP_CASE,
					"state" => MRP_STATUS_ARCHIVED,
					"starttime" => new obj_predicate_compare (
						OBJ_COMP_BETWEEN,
						mktime(0,0,0,$start_month,1,$start_year),
						mktime(0,0,0,$end_month,1,$end_year)
					),
					"parent" => $this_object->prop ("projects_folder"),
				));
				$count = $list->count();

				if ($count)
				{
					$id = implode("_", $period_data) . "_" . $period;

					$tree->add_item ($arr["parent"], array (
						"name" => $period . " ({$count})",
						"id" => $id,
						"parent" => $arr["parent"],
						"url" => $bottom_level ? aw_url_change_var ("mrp_tree_active_item", $id, aw_url_change_var ("ft_page", 0, $url)) : "javascript: void(0);",
					));

					if (!$bottom_level)
					{
						$tree->add_item ($id, array (
							"id" => "dummy" . $id,
							"parent" => $id,
						));
					}
				}
			}

			preg_match ("/mrp_tree_active_item=(archived_[^\&]+)/", $url, $active_node);

			if ($active_node[1])
			{
				$tree->set_selected_item ($active_node[1]);
			}

			exit ($tree->finalize_tree(array("rootnode" => $arr["parent"])));
		}
	}
}


/***************************** MISC. SCRIPTS *********************************/
#set all  PLANNED projects INPROGRESS that have jobs INPROGRESS, DONE, PAUSED or ABORTED
// UPDATE mrp_case, mrp_job, objects SET mrp_case.state=3 WHERE
// mrp_case.oid=mrp_job.oid AND
// mrp_case.oid=objects.oid AND
// objects.status > 0 AND
// objects.parent = this_object->prop(projects_folder) AND
// mrp_case.state=2 AND
// mrp_job.state in (3,5,7,4)
//    lisada parenti kontrollimine

// http://mailprisma/automatweb/orb.aw?class=mrp_schedule&action=create&copyjobstoschedule=1&mrp_workspace=1259
// /* COPY JOBS FROM mrp_job TO mrp_schedule */
// /* dbg */ if ($_GET["copyjobstoschedule"]==1){
// /* dbg */ $this->db_query ("SELECT mrp_job.oid FROM mrp_job LEFT JOIN objects ON objects.oid = mrp_job.oid WHERE objects.status > 0");
// /* dbg */ while ($job = $this->db_next ()) {
// /* dbg */ $this->save_handle(); $this->db_query ("insert into mrp_schedule (oid) values ({$job["oid"]})"); $this->restore_handle(); $i++;} echo $i." t88d."; exit;
// /* dbg */ }

// http://mailprisma/automatweb/orb.aw?class=mrp_schedule&action=create&copyprojectstoschedule=1&mrp_workspace=1259
// /* COPY PROJECTS FROM mrp_case TO mrp_case_schedule */
// /* dbg */ if ($_GET["copyprojectstoschedule"]==1){
// /* dbg */ $this->db_query ("SELECT mrp_case.oid FROM mrp_case LEFT JOIN objects ON objects.oid = mrp_case.oid WHERE objects.status > 0");
// /* dbg */ while ($job = $this->db_next ()) {
// /* dbg */ $this->save_handle(); $this->db_query ("insert into mrp_case_schedule (oid) values ({$job["oid"]})"); $this->restore_handle(); $i++;} echo $i." projekti."; exit;
// /* dbg */ }


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

?>
