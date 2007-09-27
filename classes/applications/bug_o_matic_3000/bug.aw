<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug.aw,v 1.87 2007/09/27 10:56:26 robert Exp $
//  bug.aw - Bugi

define("BUG_STATUS_CLOSED", 5);

/*

@classinfo syslog_type=ST_BUG relationmgr=yes no_comment=1 no_status=1 r2=yes

@tableinfo aw_bugs index=aw_id master_index=brother_of master_table=objects

@property created type=hidden table=objects field=created

@default group=general
@default table=aw_bugs

@property bug_tb type=toolbar no_caption=1 save=no



@layout name type=vbox closeable=1 area_caption=L&uuml;hikirjeldus

	
	@property name type=textbox table=objects parent=name no_caption=1

@layout settings_wrap type=vbox closeable=1 area_caption=M&auml;&auml;rangud
@layout settings type=hbox parent=settings_wrap

	@layout settings_col1 type=vbox parent=settings
		@property bug_status type=select parent=settings_col1 captionside=top
		@caption Staatus

		@property bug_feedback_p type=relpicker reltype=RELTYPE_FEEDBACK_P parent=settings_col1 captionside=top field=aw_bug_feedback_p
		@caption Tagasiside kellelt

		@property bug_priority type=select parent=settings_col1 captionside=top
		@caption Prioriteet

		@property who type=crm_participant_search style=relpicker reltype=RELTYPE_MONITOR table=aw_bugs field=who parent=settings_col1 captionside=top
		@caption Kellele

	@layout settings_col2 type=vbox parent=settings


		@property bug_type type=classificator store=connect reltype=RELTYPE_BUGTYPE parent=settings_col2 captionside=top
		@caption T&uuml;&uuml;p

		@property bug_severity type=select parent=settings_col2 captionside=top
		@caption T&otilde;sidus

		@property bug_class type=select parent=settings_col2 captionside=top
		@caption Klass

		@property bug_property type=select parent=settings_col2 captionside=top field=aw_bug_property
		@caption Klassi omadus

	@layout settings_col3 type=vbox parent=settings

		@property monitors type=relpicker reltype=RELTYPE_MONITOR multiple=1 size=5 store=connect parent=settings_col3 captionside=top
		@caption J&auml;lgijad

		@property deadline type=date_select default=-1 parent=settings_col3 captionside=top
		@caption T&auml;htaeg

	@property vb_d1 type=hidden store=no no_caption=1 parent=settings

@layout url type=vbox closeable=1 area_caption=URL

	@property bug_url type=textbox size=80 no_caption=1 parent=url
	@caption URL

@layout content type=hbox width=20%:80%

	@layout bc type=vbox parent=content closeable=1 area_caption=Sisu

		@property bug_content type=textarea rows=23 cols=60 parent=bc captionside=top no_caption=1
		@caption Sisu

		@property bug_content_comm type=textarea rows=18 cols=60 parent=bc store=no editonly=1 captionside=top
		@caption Lisa kommentaar

	@layout content_right type=vbox parent=content

		@layout data type=vbox parent=content_right closeable=1 area_caption=Tundide&nbsp;arv

			@layout data_time type=hbox parent=data width=40%:40%:20%

				@property num_hrs_guess type=textbox size=5 parent=data_time captionside=top
				@caption Prognoositav

				@property num_hrs_real type=textbox size=5 parent=data_time captionside=top
				@caption Tegelik

				@property num_hrs_to_cust type=textbox size=5 parent=data_time captionside=top
				@caption Kliendile

		@layout data_cust type=vbox parent=content_right closeable=1 area_caption=Klient

			@layout data_cust_hb type=hbox parent=data_cust width=40%:40%:20%

				@property customer type=relpicker reltype=RELTYPE_CUSTOMER parent=data_cust_hb captionside=top
				@caption Organisatsioon

				@property customer_unit type=relpicker reltype=RELTYPE_CUSTOMER_UNIT parent=data_cust_hb captionside=top
				@caption &Uuml;ksus

				@property customer_person type=relpicker reltype=RELTYPE_CUSTOMER_PERSON parent=data_cust_hb captionside=top
				@caption Isik

		@layout data_ord type=vbox parent=content_right closeable=1 area_caption=Tellija

			@layout data_ord_hb type=hbox parent=data_ord width=40%:40%:20%

				@property orderer type=relpicker reltype=RELTYPE_ORDERER parent=data_ord_hb captionside=top
				@caption Organisatsioon

				@property orderer_unit type=relpicker reltype=RELTYPE_ORDERER_UNIT parent=data_ord_hb captionside=top
				@caption &Uuml;ksus

				@property orderer_person type=relpicker reltype=RELTYPE_ORDERER_PERSON parent=data_ord_hb captionside=top
				@caption Isik

		@layout data_r_bot type=vbox parent=content_right closeable=1 area_caption=Andmed

			@property project type=relpicker reltype=RELTYPE_PROJECT  parent=data_r_bot captionside=top
			@caption Projekt

			@property bug_component type=textbox parent=data_r_bot captionside=top
			@caption Komponent


			@property fileupload type=releditor reltype=RELTYPE_FILE rel_id=first use_form=emb parent=data_r_bot captionside=top
			@caption Fail

			@property bug_predicates type=textbox parent=data_r_bot captionside=top field=aw_bug_predicates
			@caption Eeldusbugid

			@property bug_mail type=textbox parent=data_r_bot captionside=top size=15
			@caption Bugmail CC



@default group=cust

	@property team type=relpicker reltype=RELTYPE_TEAM field=aw_team
	@caption Tiim

	@property ocurrence type=select field=aw_ocurrence
	@caption Vea esinemine

	@property density type=select field=aw_density
	@caption Vea sagedus

	@property cust_responsible type=relpicker reltype=RELTYPE_CUST_RESPONSIBLE field=aw_cust_responsible
	@caption Kliendipoolne vastutaja

	@property cust_status type=select field=aw_cust_status
	@caption Kliendipoolne staatus

	@property cust_tester type=relpicker reltype=RELTYPE_CUST_TESTER field=aw_cust_tester
	@caption Kliendipoolne testija

	@property cust_solution type=textarea rows=10 cols=50 field=aw_cust_solution
	@caption Kliendipoolne lahendus

	@property cust_live_date type=date_select field=aw_cust_live_date
	@caption Kasutusvalmis

	@property cust_crit type=textarea rows=10 cols=50 field=aw_cust_crit
	@caption Vastuv&otilde;tu kriteeriumid

	@property cust_budget type=textbox field=aw_cust_budget size=5
	@caption Eelarve


@default group=problems

	@property problems_tb type=toolbar no_caption=1 store=no

	@property problems_table type=table no_caption=1 store=no

@groupinfo cust caption="Kliendi andmed"
@groupinfo problems caption="Probleemid"

@reltype MONITOR value=1 clid=CL_CRM_PERSON
@caption J&auml;lgija

@reltype FILE value=2 clid=CL_FILE
@caption Fail

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY
@caption Klient

@reltype PROJECT value=4 clid=CL_PROJECT
@caption Projekt

@reltype BUGTYPE value=5 clid=CL_META
@caption Bugi t&uuml;&uuml;p

@reltype COMMENT value=6 clid=CL_BUG_COMMENT
@caption Kommentaar

@reltype TEAM value=7 clid=CL_PROJECT_TEAM
@caption Tiim

@reltype CUST_RESPONSIBLE value=8 clid=CL_CRM_PERSON
@caption Kliendipoolne vastutaja

@reltype CUST_TESTER value=9 clid=CL_CRM_PERSON
@caption Kliendipoolne testija

@reltype FEEDBACK_P value=10 clid=CL_CRM_PERSON
@caption Tagasiside isik

@reltype CUSTOMER_UNIT value=11 clid=CL_CRM_SECTION
@caption Kliendi &uuml;ksus

@reltype CUSTOMER_PERSON value=12 clid=CL_CRM_PERSON
@caption Kliendi isik

@reltype ORDERER value=13 clid=CL_CRM_COMPANY
@caption Tellija

@reltype ORDERER_UNIT value=14 clid=CL_CRM_SECTION
@caption Tellija &uuml;ksus

@reltype ORDERER_PERSON value=15 clid=CL_CRM_PERSON
@caption Tellija isik

@reltype FROM_PROBLEM value=16 clid=CL_CUSTOMER_PROBLEM_TICKET
@caption Probleem

*/

define("BUG_OPEN", 1);
define("BUG_INPROGRESS", 2);
define("BUG_DONE", 3);
define("BUG_TESTED", 4);
define("BUG_CLOSED", 5);
define("BUG_INCORRECT", 6);
define("BUG_NOTREPEATABLE", 7);
define("BUG_NOTFIXABLE", 8);
define("BUG_WONTFIX", 9);
define("BUG_FEEDBACK", 10);
define("BUG_FATALERROR", 11);

class bug extends class_base
{
	function bug()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/bug",
			"clid" => CL_BUG
		));

		$this->bug_statuses = array(
			BUG_OPEN => t("Lahtine"),
			BUG_INPROGRESS => t("Tegemisel"),
			BUG_DONE => t("Valmis"),
			BUG_TESTED => t("Testitud"),
			BUG_CLOSED => t("Suletud"),
			BUG_INCORRECT => t("Vale teade"),
			BUG_NOTREPEATABLE => t("Kordamatu"),
			BUG_NOTFIXABLE => t("Parandamatu"),
			BUG_WONTFIX => t("Ei paranda"),
			BUG_FEEDBACK => t("Vajab tagasisidet"),
			BUG_FATALERROR => t("Fatal error"),
		);

		$this->occurrences = array(
			1 => t("Esmakordne"),
			2 => t("Korduv")
		);

		$this->densities = array(
			1 => t("&Uuml;ksikjuht"),
			2 => t("Puudutab suurt osa"),
			3 => t("Puudutab k&otilde;iki")
		);
	}

	function callback_mod_reforb($arr)
	{
		$arr["from_problem"] = $_GET["from_problem"];
	}

	function callback_on_load($arr)
	{
		$this->cx = get_instance("cfg/cfgutils");
		$pt = $arr["request"]["parent"] ? $arr["request"]["parent"] : $arr["request"]["id"];

		$parent = new object($pt);
		$props = $parent->properties();
		$cx_props = $this->cx->load_properties(array(
			"clid" => $parent->class_id(),
			"filter" => array(
				"group" => "general",
			),
		));
		$this->parent_options = array();
		$els = array("who", "monitors", "project", "customer");
		foreach($els as $el)
		{
			$this->parent_options[$el] = array();
			$objs = $parent->connections_from(array(
				"type" => $cx_props[$el]["reltype"],
			));
			foreach($objs as $obj)
			{
				$this->parent_options[$el][$obj->prop("to")] = $obj->prop("to.name");
			}
		}
		$this->parent_data = array(
			"who" => $props["who"],
			"bug_class" => $props["bug_class"],
			"monitors" => $props["monitors"],
			"project" => $props["project"],
			"customer" => $props["customer"],
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		if($arr["new"] && !empty($this->parent_data[$prop["name"]]))
		{
			$prop["value"] = $this->parent_data[$prop["name"]];
		}
		switch($prop["name"])
		{
			case "customer_unit":
				if ($this->can("view", $arr["obj_inst"]->prop("customer")))
				{
					$co = obj($arr["obj_inst"]->prop("customer"));
				}
				else
				if ($arr["request"]["from_problem"])
				{
					$tmp = obj($arr["request"]["from_problem"]);
					$co = obj($tmp->prop("customer"));
				}
				else
				{
					$co = get_current_company();
				}
				$co_i = $co->instance();
				$sects = $co_i->get_all_org_sections($co);
				$prop["options"] = array("" => t("--vali--"));
				if (count($sects))
				{
					$ol = new object_list(array("oid" => $sects, "lang_id" => array(), "site_id" => array()));
					$prop["options"] += $ol->names();
				}
				$p = get_current_person();
				if ($arr["new"])
				{
					if ($arr["request"]["from_problem"])
					{
						$tmp = obj($arr["request"]["from_problem"]);
						$prop["value"] = $tmp->prop("orderer_unit");
					}
					else
					{
						$prop["value"] = $p->prop("org_section");
					}
				}
				break;

			case "customer_person":
				return $this->_get_customer_person($arr);

			case "orderer":
				return $this->_get_orderer($arr);

			case "orderer_unit":
				return $this->_get_orderer_unit($arr);

			case "orderer_person":
				return $this->_get_orderer_person($arr);

			case "deadline":
				if ($arr["request"]["from_req"])
				{
					$r = obj($arr["request"]["from_req"]);
					$prop["value"] = $r->prop("planned_time");
				}
				break;

			case "team":
				if ($this->can("view", $arr["obj_inst"]->prop("project")))
				{
					$po = obj($arr["obj_inst"]->prop("project"));
					$opts = array("" => t("--vali--"));
					foreach($po->connections_from(array("type" => "RELTYPE_TEAM")) as $c)
					{
						$opts[$c->prop("to")] = $c->prop("to.name");
					}
					$prop["options"] = $opts;
				}
				break;

			case "ocurrence":
				$prop["options"] = $this->occurrences;
				break;

			case "density":
				$prop["options"] = $this->densities;
				break;

			case "cust_status":
				$prop["options"] = $this->bug_statuses;
				break;

			case "cust_responsible":
			case "cust_tester":
				if ($this->can("view", $arr["obj_inst"]->prop("project")))
				{
					$opts = array("" => t("--vali--"));
					$pi = get_instance(CL_PROJECT);
					$team = $pi->get_team(obj($arr["obj_inst"]->prop("project")));
					foreach($team as $team_id)
					{
						$mem = obj($team_id);
						$opts[$team_id] = $mem->name();
					}
					$prop["options"] = $opts;
				}
				break;

			case "name":
				if (is_oid($arr["obj_inst"]->id()))
				{
					$u = get_instance(CL_USER);
					$p = $u->get_person_for_uid($arr["obj_inst"]->createdby());
					$crea = sprintf(t("Looja: %s / %s"), $p->name(), date("d.m.Y H:i", $arr["obj_inst"]->created()));
				}
				else
				if ($arr["request"]["from_req"])
				{
					$r = obj($arr["request"]["from_req"]);
					$prop["value"] = $r->name();
				}
				else
				if ($arr["request"]["from_problem"])
				{
					$r = obj($arr["request"]["from_problem"]);
					$prop["value"] = $r->name();
				}

				$link = html::href(array(
					"caption" => t("Link"),
					"url" => obj_link($arr["obj_inst"]->id())
				));
				$prop["post_append_text"] = " #".$arr["obj_inst"]->id()." $link ".sprintf(t("Vaade avatud: %s"), date("d.m.Y H:i"))." ".$crea;
				break;

			case "bug_content":
				if (!$arr["new"])
				{
					$prop["value"] = "<br>".$this->_get_comment_list($arr["obj_inst"])."<br>";
					$prop["type"] = "text";
				}
				if ($arr["request"]["from_req"])
				{
					$r = obj($arr["request"]["from_req"]);
					$prop["value"] = $r->prop("desc");
				}
				if ($arr["request"]["from_problem"])
				{
					$r = obj($arr["request"]["from_problem"]);
					$prop["value"] = $r->prop("content");
				}
				break;

			case "bug_status":
				$prop["options"] = $this->bug_statuses;
				break;

			case "bug_priority":
			case "bug_severity":
				$prop["options"] = $this->get_priority_list();
				if ($arr["request"]["from_req"])
				{
					$r = obj($arr["request"]["from_req"]);
					$prop["value"] = (int)($r->prop("pri")/2);
				}
				break;


			case "bug_feedback_p":
				if ($arr["obj_inst"]->prop("bug_status") != BUG_FEEDBACK)
				{
					return PROP_IGNORE;
				}

			case "who":
			case "monitors":
				if ($arr["new"] || true)
				{
					foreach($this->parent_options[$prop["name"]] as $key => $val)
					{
						$key_o = obj($key);
						if ($key_o->class_id() == CL_CRM_PERSON)
						{
							$tmp[$key] = $val;
						}
					}
					// also, the current person
					$u = get_instance(CL_USER);
					$p = obj($u->get_current_person());
					$tmp[$p->id()] = $p->name();

					if ($prop["multiple"] == 1 && $arr["new"])
					{
					//	$prop["value"] = $this->make_keys(array_keys($tmp));
						$prop["value"] = array($p->id(), $p->id());
					}

					// find tracker for the bug and get people list from that
					$po = obj($arr["request"]["parent"] ? $arr["request"]["parent"] : $arr["request"]["id"]);
					$pt = $po->path();
					foreach($pt as $pi)
					{
						if ($pi->class_id() == CL_BUG_TRACKER)
						{
							$bt = $pi->instance();
							foreach($bt->get_people_list($pi) as $pid => $pnm)
							{
								$tmp[$pid] = $pnm;
							}
						}
					}
					$prop["options"] = array("" => t("--vali--")) + $tmp;
				}
				if ($this->can("view", $prop["value"]) && !isset($prop["options"][$prop["value"]]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}

				if (is_array($prop["value"]))
				{
					foreach($prop["value"] as $val)
					{
						if ($this->can("view", $val))
						{
							$tmp = obj($val);
							$prop["options"][$tmp->id()] = $tmp->name();
						}
					}
				}

				if ($arr["request"]["from_req"])
				{
					$r = obj($arr["request"]["from_req"]);
					$prop["options"][$r->prop("req_p")] = $r->prop("req_p.name");
				}
				break;

			case "bug_class":
				$prop["options"] = array("" => "") + $this->get_class_list();
				break;

			case "project":
				if (is_object($arr["obj_inst"]) && $this->can("view", $arr["obj_inst"]->prop("customer")))
				{
					$filt = array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_ORDERER" => $arr["obj_inst"]->prop("customer"),
						"lang_id" => array(),
						"site_id" => array()
					);
					$ol = new object_list($filt);
				}
				else
				{
					$i = get_instance(CL_CRM_COMPANY);
					$prj = $i->get_my_projects();
					if (!count($prj))
					{
						$ol = new object_list();
					}
					else
					{
						$ol = new object_list(array("oid" => $prj));
					}
				}

				$prop["options"] = array("" => t("--vali--")) + $ol->names();

				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}

				if ($arr["request"]["set_proj"])
				{
					$prop["value"] = $arr["request"]["set_proj"];
				}

				if($arr["new"])
				{
					foreach($this->parent_options[$prop["name"]] as $key => $val)
					{
						$prop["options"][$key] = $val;
					}
				}

				if ($arr["request"]["from_req"])
				{
					$r = obj($arr["request"]["from_req"]);
					$prop["value"] = $r->prop("project");
				}
				break;

			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$cst = $i->get_my_customers();
				if (!count($cst))
				{
					$prop["options"] = array("" => t("--vali--"));
				}
				else
				{
					$ol = new object_list(array("oid" => $cst, "lang_id" => array(), "site_id" => array()));
					$opts = array();
					foreach($ol->arr() as $_co)
					{
						$nm = $_co->prop("short_name");
						if ($nm == "")
						{
							$nm = $_co->name();
							if (strlen($nm) > 15)
							{
								$nm = substr($nm, 0, 15)."...";
							}
						}
						$opts[$_co->id()] = $nm;
					}
					$prop["options"] = array("" => t("--vali--")) + $opts;
				}

				if ($this->can("view", $arr["request"]["alias_to_org"]))
				{
					$ao = obj($arr["request"]["alias_to_org"]);
					if ($ao->class_id() == CL_CRM_PERSON)
					{
						$u = get_instance(CL_USER);
						$prop["value"] = $u->get_company_for_person($ao->id());
					}
					else
					{
						$prop["value"] = $arr["request"]["alias_to_org"];
					}
				}
				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$tmp->id()] = $tmp->name();
				}

				if($arr["new"])
				{
					foreach($this->parent_options[$prop["name"]] as $key => $val)
					{
						$prop["options"][$key] = $val;
					}
				}
				if ($arr["request"]["from_req"])
				{
					$r = obj($arr["request"]["from_req"]);
					$prop["value"] = $r->prop("req_co");
				}
				break;

			case "num_hrs_real":
				$url = $this->mk_my_orb("stopper_pop", array(
					"id" => $arr["obj_inst"]->id(),
					"s_action" => "start",
					"type" => $this->clid,
					"source_id" => $arr["obj_inst"]->id(),
					"name" => $arr["obj_inst"]->name()
				), CL_TASK);
				$prop["post_append_text"] = " <a href='javascript:void(0)' onClick='aw_popup_scroll(\"$url\",\"aw_timers\",800,600)'>".t("Stopper")."</a>";
				break;

			case "bug_url":
				$prop["post_append_text"] = " <a href='javascript:void(0)' onClick='window.location=document.changeform.bug_url.value'>Ava</a>";
				break;

			case "bug_property":
				if ($arr["obj_inst"]->prop("bug_class"))
				{
					$prop["options"] = $this->_get_property_picker($arr["obj_inst"]->prop("bug_class"));
				}
				break;

			case "bug_tb":
				$this->_bug_tb($arr);
				break;

			case "problems_table":
				return $this->_get_problems_table($arr);
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "name":
				if (!$this->can("view", $arr["request"]["who"]))
				{
					$prop["error"] = t("Kellele ei tohi olla t&uuml;hi!");
					return PROP_FATAL_ERROR;
				}

				if ($arr["request"]["bug_status"] == BUG_FATAL_ERROR)
				{
					return PROP_OK;
				}
				classload("core/date/date_calc");
				$ev = date_edit::get_timestamp($arr["request"]["deadline"]);
				if ($ev == $arr["obj_inst"]->prop("deadline"))
				{
					return PROP_OK;
				}
				else
				if ($ev > 300 && $ev < get_day_start())
				{
					$prop["error"] = t("T&auml;htaeg ei tohi olla minevikus!");
					return PROP_FATAL_ERROR;
				}
				$bt = get_instance(CL_BUG_TRACKER);
				$arr["obj_inst"]->set_prop("who", $arr["request"]["who"]);
				$estend = $bt->get_estimated_end_time_for_bug($arr["obj_inst"]);
				$ovr1 = $bt->get_last_estimation_over_deadline_bugs();

				$opv = $arr["obj_inst"]->prop("deadline");
				$opri = $arr["obj_inst"]->prop("bug_priority");
				$osev = $arr["obj_inst"]->prop("bug_severity");
				$arr["obj_inst"]->set_prop("deadline", $ev);
				$arr["obj_inst"]->set_prop("bug_priority", $arr["request"]["bug_priority"]);
				$arr["obj_inst"]->set_prop("bug_severity", $arr["request"]["bug_severity"]);
				$arr["obj_inst"]->set_prop("bug_status", $arr["request"]["bug_status"]);
				$estend = $bt->get_estimated_end_time_for_bug($arr["obj_inst"]);
				$ovr2 = $bt->get_last_estimation_over_deadline_bugs();

				$arr["obj_inst"]->set_prop("deadline", $opv);
				$arr["obj_inst"]->set_prop("bug_priority", $opri);
				$arr["obj_inst"]->set_prop("bug_severity", $osevri);

				$n_ovr = array();
				foreach($ovr2 as $item)
				{
					if (!isset($ovr1[$item->id()]) && $item->id() != $arr["obj_inst"]->id())
					{
						$n_ovr[] = $item;
					}
				}

				if (count($n_ovr) && false) // && false on temp lahendus, eks terryf vaatab &uuml;le kui puhkuselt tuleb
				{
					$nms = array();
					foreach($n_ovr as $item)
					{
						$nms[] = html::obj_change_url($item);
					}
					$prop["error"] = sprintf(t("Selliste parameetritega ei saa salvestada, kuna see l&uuml;kkaks j&auml;rgnevad bugid &uuml;le t&auml;htaja: %s!"), join("<br>", $nms));
					return PROP_FATAL_ERROR;
				}

				if ($ev > 100 && $estend > ($ev+24*3600))
				{
					$prop["error"] = sprintf(t("Bugi ei ole v&otilde;imalik valmis saada enne %s!"), date("d.m.Y H:i", $estend));
					return PROP_FATAL_ERROR;
				}
				break;

			case "bug_content":
				if (!$arr["new"])
				{
					$prop["value"] = $arr["obj_inst"]->prop("bug_content");
				}
				break;

			case "bug_predicates":
				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"])
				{
					$com = sprintf(t("Eeldusbugid muudeti %s => %s"), $old, $prop["value"]);
					$this->add_comments[] = $com;
				}
				break;

			case "bug_content_comm":
				if (trim($prop["value"]) != "" && !$arr["new"])
				{
					// save comment
					//$this->_add_comment($arr["obj_inst"], $prop["value"]);
					$this->add_comments[] = $prop["value"];
				}
				break;

			case "bug_status":
				$this->_ac_old_state = $arr["obj_inst"]->prop("bug_status");
				$this->_ac_new_state = $prop["value"];
				if (!$arr["new"])
				{
					$retval = $this->_handle_status_change(
						$this->_ac_old_state,
						$this->_ac_new_state,
						$arr["obj_inst"],
						$prop
					);
				}

				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"] && !$arr["new"])
				{
					$com = sprintf(t("Staatus muudeti %s => %s"), $this->bug_statuses[$old], $this->bug_statuses[$prop["value"]]);
					$this->add_comments[] = $com;
				}
				break;

			case "bug_priority":
				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"] && !$arr["new"])
				{
					$com = sprintf(t("Prioriteet muudeti %s => %s"), $old, $prop["value"]);
					//$this->_add_comment($arr["obj_inst"], $com);
					$this->add_comments[] = $com;
				}
				break;

			case "num_hrs_real":
				$prop["value"] = str_replace(",", ".", $prop["value"]);
				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"])
				{
					$com = sprintf(t("Tegelik tundide arv muudeti %s => %s"), $old, $prop["value"]);
					$this->add_comments[] = $com;
					$this->_acc_add_wh = $prop["value"] - $old;
				}
				break;

			case "num_hrs_guess":
				$prop["value"] = str_replace(",", ".", $prop["value"]);
				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"])
				{
					$com = sprintf(t("Prognoositud tundide arv muudeti %s => %s"), $old, $prop["value"]);
					$this->add_comments[] = $com;
				}
				break;

			case "num_hrs_to_cust":
				$prop["value"] = str_replace(",", ".", $prop["value"]);
				if (($old = $arr["obj_inst"]->prop($prop["name"])) != $prop["value"])
				{
					$com = sprintf(t("Tunde kliendile arv muudeti %s => %s"), $old, $prop["value"]);
					$this->add_comments[] = $com;
				}
				break;

			case "who":
				$nv = "";
				if ($this->can("view", $prop["value"]))
				{
					$nvo = obj($prop["value"]);
					$nv = $nvo->name();
				}

				if (($old = $arr["obj_inst"]->prop_str($prop["name"])) != $nv && !$arr["new"])
				{
					$com = sprintf(t("Kellele muudeti %s => %s"), $old, $nv);
					//$this->_add_comment($arr["obj_inst"], $com);
					$this->add_comments[] = $com;
				}
				break;

			case "bug_class":
				$clss = aw_ini_get("classes");
				$old = $clss[(int)$arr["obj_inst"]->prop($prop["name"])]["name"];
				$nv = $clss[(int)$prop["value"]]["name"];
				if ($old != $nv && !$arr["new"])
				{
					$com = sprintf(t("Klass muudeti %s => %s"), $old, $nv);
					//$this->_add_comment($arr["obj_inst"], $com);
					$this->add_comments[] = $com;
				}
				break;

			case "bug_feedback_p":
				if ($arr["obj_inst"]->prop("bug_status") != BUG_FEEDBACK)
				{
					return PROP_IGNORE;
				}

				if ($this->_set_feedback)
				{
					$prop["value"] = $this->_set_feedback;
				}

				$nv = "";
				if ($this->can("view", $prop["value"]))
				{
					$nvo = obj($prop["value"]);
					$nv = $nvo->name();
				}

				if (($old = $arr["obj_inst"]->prop_str($prop["name"])) != $nv && !$arr["new"])
				{
					$com = sprintf(t("Tagaiside kellelt muudeti %s => %s"), $old, $nv);
					$this->add_comments[] = $com;
				}
				break;
		}
		return $retval;
	}

	function notify_monitors($bug, $comment)
	{
		$monitors = $bug->prop("monitors");

		// if the status is right, then add the creator of the bug to the list
		$states = array(
			BUG_TESTED,
			BUG_INCORRECT,
			BUG_NOTREPEATABLE,
			BUG_NOTFIXABLE,
			BUG_WONTFIX,
			BUG_FEEDBACK
		);
		$u = get_instance(CL_USER);
		$us = get_instance("users");
		if (true || in_array($bug->prop("bug_status"), $states))
		{
			$crea = $bug->createdby();
			$monitors[] = $u->get_person_for_user(obj($us->get_oid_for_uid($crea)));
		}

		// add who to the list of mail recievers
		$monitors[] = $bug->prop("who");

		// I should add a way to send CC-s to arbitraty e-mail addresses as well
		$notify_addresses = array();
		$bt = $this->_get_bt($bug);
		if ($bt && $bt->prop("def_notify_list") != "")
		{
			$notify_addresses[] = $bt->prop("def_notify_list");
		}

		foreach(array_unique($monitors) as $person)
		{
			if(!$this->can("view", $person))
			{
				continue;
			}
			$person_obj = obj($person);
			// don't send to the current user, cause, well, he knows he's just done it.
			if ($person == $u->get_current_person())
			{
				continue;
			}
			$email = $person_obj->prop("email");

			if (is_oid($email))
			{
				$email_obj = new object($email);
				$addr = $email_obj->prop("mail");
				if (is_email($addr))
				{
					$notify_addresses[] = $addr;
				};
			};
		};

		$addrs = explode(",",$bug->prop("bug_mail"));
		foreach($addrs as $addr)
		{
			if (is_email($addr))
			{
				$notify_addresses[] = $addr;
			};
		};

		if (sizeof($notify_addresses) == 0)
		{
			return false;
		};

		foreach(array_unique($notify_addresses) as $adr)
		{
			$oid = $bug->id();
			$name = $bug->name();
			$uid = aw_global_get("uid");

			$msgtxt = t("Bug") . ": " . $this->mk_my_orb("change",array("id" => $oid)) . "\n";
			$msgtxt .= t("Summary") . ": " . $name . "\n";
			$msgtxt .= t("URL") . ": " . $bug->prop("bug_url") . "\n";
			$msgtxt .= "-------------\n\nNew comment from " . $uid . " at " . date("Y-m-d H:i") . "\n";
			$msgtxt .= strip_tags($comment)."\n";
			$msgtxt .= strip_tags($this->_get_comment_list($bug, "desc", false));

			send_mail($adr,"Bug #" . $oid . ": " . $name . " : " . $uid . " lisas kommentaari",$msgtxt,"From: automatweb@automatweb.com");
		}
	}

	function get_sort_priority($bug)
	{
		$sp_lut = array(
			BUG_OPEN => 100,
			BUG_INPROGRESS => 110,
			BUG_DONE => 70,
			BUG_TESTED => 60,
			BUG_CLOSED => 50,
			BUG_INCORRECT => 40,
			BUG_NOTREPEATABLE => 40,
			BUG_NOTFIXABLE => 40,
			BUG_FATALERROR => 200,
			BUG_FEEDBACK => 130
		);
		$rv = $sp_lut[$bug->prop("bug_status")] + $bug->prop("bug_priority");
		// also, if the bug has a deadline, then we need to up the priority as the deadline comes closer
		if (($dl = $bug->prop("deadline")) > 200)
		{

			// deadline in the next 24 hrs = +3
			if ($dl < (time() - 24*3600))
			{
				$rv++;
			}
			// deadline in the next 48 hrs +2
			if ($dl < (time() - 48*3600))
			{
				$rv++;
			}
			// has deadline = +1
			$rv++;
		}

		//if customer priority set, up the bug's priority
		if($cust_priority = $bug->prop("customer.cust_priority"))
		{
			$cust_priority = ($cust_priority>99999)?99999:$cust_priority;
			$rv += 1.0 - ((double)1.0/((double)100000.0 + (double)$cust_priority));
		}

		$rv += 1.0 - ((double)1.0/((double)1000000.0 - (double)$bug->id()));

		return $rv;
	}

	/**
		@attrib params=name name=handle_bug_change_status
		@param bug required type=oid
		@param status required type=int
	**/
	function hadle_bug_change_status($arr)
	{
		$o = obj($arr["bug"]);
		$o->set_prop("bug_status", $arr["status"]);
		die();
	}
	
	/**
		@attrib name=get_autocomplete
		@comment
			bug name autokompliit
	**/
	function get_autocomplete()
	{
		header ("Content-Type: text/html; charset=" . aw_global_get("charset"));
		$cl_json = get_instance("protocols/data/json");

		$errorstring = "";
		$error = false;
		$autocomplete_options = array();

		$option_data = array(
			"error" => &$error,// recommended
			"errorstring" => &$errorstring,// optional
			"options" => &$autocomplete_options,// required
			"limited" => false,// whether option count limiting applied or not. applicable only for real time autocomplete.
		);

		$ol = new object_list(array(
			"class_id" => CL_BUG,
		));
		foreach($ol->arr() as $oid => $el)
		{
			$obj = new object($oid);
			$autocomplete_options[$obj->name()] = $obj->name();
		}

		exit($cl_json->encode($option_data));
	}

	function stopper_autocomplete($requester, $arr)
	{
		switch($requester)
		{
			case "parent":
				$ol = new object_list(array(
					"class_id" => CL_BUG,
				));
				foreach($ol->arr() as $oid => $obj)
				{
					$ret[$oid] = $obj->name();
				}
				break;
		}
		return $ret;
	}

	function gen_stopper_addon($arr)
	{
		
		$props = array(
			array(
				"name" => "name",
				"type" => "textbox",
				"caption" => t("Nimi"),
			),
			array(
				"name" => "status",
				"type" => "select",
				"options" => $this->bug_statuses,
				"caption" => t("Staatus"),
			),
			array(
				"name" => "contents",
				"type" => "textarea",
				"caption" => t("Sisu"),
			),
			array(
				"name" => "parent",
				"type" => "textbox",
				"caption" => t("Vanem-bugi"),
				"autocomplete" => true,
			),
			array(
				"name" => "deadline",
				"type" => "date_select",
				"caption" => t("T&auml;htaeg"),
			),
		);
		return $props;
	}
	function gen_existing_stopper_addon($arr)
	{
		$o = obj($arr["oid"]);
		$props = array(
			array(
				"name" => "contents",
				"type" => "textarea",
				"caption" => t("Kommentaar"),
			),
			array(
				"name" => "status",
				"type" => "select",
				"options" => $this->bug_statuses,
				"caption" => t("Staatus"),
				"selected" => $o->prop("bug_status"),
			),
		);
		return $props;
	}

	function handle_stopper_stop($inf)
	{
		/*
			props to take from parent bug
			customer_unit,
			customer_person,
			orderer,
			orderer_unit,
			orderer_person,
			monitors, ??
			project,
			customer,
			
			bug_class, ?????
		*/
		if(!$this->can("view", $inf["oid"]))
		{
			if(!strlen($inf["data"]["name"]["value"]) || !$this->can("view", $inf["data"]["parent"]["value"]) || $inf["data"]["deadline"]["value"] == -1)
			{
				return t("Nimi, vanem-bugi ja t&auml;htaeg peavad olema seatud!");
			}
		}
		if(!$this->can("view", $inf["oid"]))
		{
			$parent = obj($inf["data"]["parent"]["value"]);
			// props from parent
			$pfp = array("bug_priority", "who", "bug_severity", "monitors", "bug_class", "customer", "customer", "customer_unit", "customer_person", "orderer", "orderer_unit", "orderer_person");

			$o = new object();
			$o->set_parent($inf["data"]["parent"]["value"]);
			$o->set_name($inf["data"]["name"]["value"]);
			$o->set_class_id(CL_BUG);
			$o->set_prop("bug_content", $inf["data"]["contents"]["value"]);
			$o->set_prop("deadline", $arr["data"]["deadline"]["value"]);

			foreach($pfp as $pprop)
			{
				$o->set_prop($pprop, $parent->prop($pprop));
			}
			$o->save();
			$inf["oid"] = $o->id();
			unset($inf["data"]["contents"]["value"]);
		}

		$bug = obj($inf["oid"]);

		$inf["desc"] = $inf["data"]["contents"]["value"];
		$inf["desc"] .= sprintf(t("\nTegelik tundide arv muudeti %s => %s"), $bug->prop("num_hrs_real"), $bug->prop("num_hrs_real")+$inf["hours"]);
		$bug->set_prop("num_hrs_real", $bug->prop("num_hrs_real") + $inf["hours"]);
		if(array_key_exists($inf["data"]["status"]["value"], $this->bug_statuses))
		{
			$bug->set_prop("bug_status", $inf["data"]["status"]["value"]);
		}
		$bug->save();

		if (trim($inf["desc"]) != "")
		{
			$this->_add_comment($bug, $inf["desc"], null, null, $inf["hours"]);
		}
	}

	function _get_comment_list($o, $so = "asc", $nl2br = true)
	{
		$this->read_template("comment_list.tpl");

		$ol = new object_list(array(
			"class_id" => CL_BUG_COMMENT,
			"parent" => $o->id(),
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "objects.created $so"
		));
		$com_str = "";
		foreach($ol->arr() as $com)
		{
			$comt = create_links(htmlspecialchars($com->comment()));
			$comt = preg_replace("/(>http:\/\/dev.struktuur.ee\/cgi-bin\/viewcvs\.cgi\/[^<\n]*)/ims", ">Diff", $comt);

			if ($nl2br)
			{
				$comt = nl2br($comt);
			}

			$comt = $this->_split_long_words($comt);

			// replace #675656 with link to bug
			$comt = preg_replace("/#([0-9]+)/ims", "<a href='http://intranet.automatweb.com/\\1'>#\\1</a>", $comt);


//			$comt = $this->parse_commited_msg($comt);

			$this->vars(array(
				"com_adder" => $com->createdby(),
				"com_date" => date("d.m.Y H:i", $com->created()),
				"com_text" => $comt
			));
			$com_str .= $this->parse("COMMENT");
		}

		$main_c = "<b>".$o->createdby()." @ ".date("d.m.Y H:i", $o->created())."</b><br>".$this->_split_long_words(nl2br(create_links(htmlspecialchars($o->prop("bug_content")))));
		$this->vars(array(
			"main_text" => $so == "asc" ? $main_c : "",
			"main_text_after" => $so == "asc" ? "" : $main_c,
			"COMMENT" => $com_str
		));
		return $this->parse();
	}

	function _split_long_words($comt)
	{
		// split words and check for > 70 chars
		$words = preg_split("/\s+/", strip_tags(trim($comt)));
		foreach($words as $word)
		{
			if (strlen($word) > 70)
			{
				$o_w = $word;
				$n_w = "";
				$l = strlen($word);
				for ($i = 0; $i < $l; $i++)
				{
					if (($i % 70 == 0) && $i > 1)
					{
						$n_w .= "<br>";
					}
					$n_w .= $word[$i];
				}
				$comt = str_replace($o_w, $n_w, $comt);
				$comt = str_replace("\"".$n_w, "\"".$o_w, $comt);
			}
		}

		return $comt;
	}

	function _add_comment($bug, $comment, $old_state = null, $new_state = null, $add_wh = null, $notify = true)
	{
		if (!is_oid($bug->id()))
		{
			return;
		}
		// email any persons interested in status changes of that bug
		if ($notify)
		{
			$this->notify_monitors($bug, $comment);
		}

		$o = obj();
		$o->set_parent($bug->id());
		$o->set_class_id(CL_BUG_COMMENT);
		$o->set_comment(trim($comment));
		$o->set_prop("prev_state", $old_state);
		$o->set_prop("new_state", $new_state);
		$o->set_prop("add_wh", $add_wh);
		$o->save();
		$bug->connect(array(
			"to" => $o->id(),
			"type" => "RELTYPE_COMMENT"
		));
	}

	function get_priority_list()
	{
		$res = array();
		$res[1] = "1 (Madalaim)";
		$res[2] = "2";
		$res[3] = "3";
		$res[4] = "4";
		$res[5] = "5 (K&otilde;rgeim)";
		return $res;
	}

	function get_status_list()
	{
		return $this->bug_statuses;
	}

	function get_class_list()
	{
		return get_class_picker();
	}

	function callback_pre_save($arr)
	{
	}

	function callback_post_save($arr)
	{
		if (is_array($this->add_comments) && count($this->add_comments))
		{
			$this->_add_comment($arr["obj_inst"], join("\n", $this->add_comments), $this->_ac_old_state, $this->_ac_new_state, $this->_acc_add_wh);
		}

		if ($arr["new"])
		{
			$this->notify_monitors($arr["obj_inst"], $arr["obj_inst"]->prop("bug_content"));
			// if this is a new bug, then parse the content and create sub/subsub bugs from it
			$this->_parse_add_bug_content($arr["obj_inst"]);
			if ($arr["request"]["from_problem"])
			{
				$arr["obj_inst"]->connect(array(
					"to" => $arr["request"]["from_problem"],
					"type" => "RELTYPE_FROM_PROBLEM"
				));
			}
		}
	}

	function parse_commited_msg($msg)
	{

		$row =  explode("\n" , $msg);
		//arr($row);
		$result = array("diff" => $row[0], "files" =>  str_replace("<br />" , "" ,$row[6]), "bug" => str_replace("<br />" , "" , $row[8]));
		$time_arr = explode(":" , $row[9]);
		if($time_arr[1])
		{
			$result["time"] = $time_arr[1];
		}

		$by1 = strpos($row[1], 'by') + 3;//arr($by1);
		$by2 = strpos($row[1], ' ', $by1+5);//arr($by2);
		$result["by"] = substr($row[1], $by1, $by2-$by1 );
	//	arr($row[1]);
	//	arr($result);

		$msg = $result["bug"]." ".$result["diff"]."\n".t("Failid: ").$result["files"];
		if($result["time"]) $msg.="\n".t("Aeg:").$result["time"];
		return $msg;
	}

	/**
		@attrib name=handle_commit nologin=1
		@param bugno required type=int
		@param msg optional
		@param set_fixed optional
		@param time_add optional
	**/
	function handle_commit($arr)
	{
		aw_disable_acl();
		$bug = obj($arr["bugno"]);
		$msg = trim($this->hexbin($arr["msg"]));

		$orig_msg = $msg;
		$msg = $this->parse_commited_msg($msg);

		$com = false;
		$ostat = $nstat = $bug->prop("bug_status");
		if ($arr["set_fixed"] == 1)
		{
			$msg .= "\nStaatus muudeti ".$this->bug_statuses[$bug->prop("bug_status")]." => ".$this->bug_statuses[BUG_DONE]."\n";
			$bug->set_prop("bug_status", BUG_DONE);
			$nstat = BUG_DONE;
			$save = true;
			$com = true;
		}

		if ($arr["time_add"])
		{
			$ta = $arr["time_add"];
			// parse time
			$hrs = 0;
			if ($ta[strlen($ta)-1] == "m")
			{
				$hrs = ((double)$ta) / 60.0;
			}
			else
			{
				$hrs = (double)$ta;
			}
			// round to 0.25
			$hrs = floor($hrs * 4.0+0.5)/4.0;
			$msg .= sprintf(t("\nTegelik tundide arv muudeti %s => %s"), $bug->prop("num_hrs_real"), $bug->prop("num_hrs_real")+$hrs);
			$bug->set_prop("num_hrs_real", $bug->prop("num_hrs_real") + $hrs);
			$add_wh = $hrs;
			$save = true;
			$com = true;
		}

		// get the cvs uid to aw uid map and switch user if the map has it
		$bt = $this->_get_bt($bug);
		if ($bt)
		{
			$uid_map = $bt->prop("cvs2uidmap");
			if (preg_match("/cvs commit by ([^ ]+) in/imsU", $orig_msg, $mt))
			{
				$cvs_uid = $mt[1];
				foreach(explode("\n", $uid_map) as $map_line)
				{
					list($map_cvs_uid, $map_aw_uid) = explode("=", $map_line);
					if ($map_cvs_uid == $cvs_uid)
					{
						aw_switch_user(array("uid" => trim($map_aw_uid)));
					}
				}
			}
		}

		if ($save)
		{
			$bug->save();
		}

		$this->_add_comment($bug, $msg, $ostat, $nstat, $add_wh, $com);
		aw_restore_acl();
		die(sprintf(t("Added comment to bug %s"), $arr["bugno"]));
	}

	function do_db_upgrade($tbl, $f)
	{
		switch($f)
		{
			case "aw_bug_property":
			case "aw_bug_predicates":
				$this->db_add_col($tbl, array(
					"name" => $f,
					"type" => "varchar",
					"length" => 255
				));
				return true;

			case "aw_team":
			case "aw_ocurrence":
			case "aw_density":
			case "aw_team":
			case "aw_cust_responsible":
			case "aw_cust_status":
			case "aw_cust_tester":
			case "aw_cust_live_date":
			case "aw_bug_feedback_p":
			case "project":
			case "who":
			case "deadline":
			case "customer":
			case "customer_unit":
			case "customer_person":
			case "orderer":
			case "orderer_unit":
			case "orderer_person":
			case "fileupload":
				$this->db_add_col($tbl, array(
					"name" => $f,
					"type" => "int",
				));
				return true;

			case "aw_cust_solution":
			case "aw_cust_crit":
				$this->db_add_col($tbl, array(
					"name" => $f,
					"type" => "text",
				));
				return true;

			case "aw_cust_budget":
			case "num_hrs_guess":
			case "num_hrs_real":
			case "num_hrs_to_cust":
				$this->db_add_col($tbl, array(
					"name" => $f,
					"type" => "double",
				));
				return true;
		}
	}

	function _get_property_picker($clid)
	{
		$o = obj();
		$o->set_class_id($clid);
		$ret = array("" => "");
		$props = $o->get_property_list();
		foreach($o->get_group_list() as $gn => $gc)
		{
			$ret["grp_".$gn] = $gc["caption"];
			foreach($props as $pn => $pd)
			{
				if ($pd["group"] == $gn)
				{
					$ret["prop_".$pn] = "&nbsp;&nbsp;&nbsp;".substr($pd["caption"], 0, 20);
				}
			}
		}
		return $ret;
	}

	function request_execute($o)
	{
		header("Location: ".$this->mk_my_orb("change", array("id" => $o->id()), "bug", true));
		die();
	}

	function _bug_tb($arr)
	{
		if (!is_oid($arr["obj_inst"]->id()))
		{
			return;
		}
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			"name" => "new",
			"tooltip" => t("Uus arendus&uuml;lesanne"),
		));

		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("Samale tasemele"),
			"link" => html::get_new_url(CL_BUG, $arr["obj_inst"]->parent(), array("return_url" => $arr["request"]["return_url"])),
			"href_id" => "add_bug_href"
		));
		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("Sisse"),
			"link" => html::get_new_url(CL_BUG, $arr["obj_inst"]->id(), array("return_url" => $arr["request"]["return_url"])),
			"href_id" => "add_bug_hrefp"
		));
	}

	function _parse_add_bug_content($o)
	{
		$c = $o->prop("bug_content");
		if (strpos($c, "1)") === false)
		{
			return;
		}

		$ls = explode("\n", $c);
		$bugs = array();
		foreach($ls as $line)
		{
			if (trim($line) == "")
			{
				continue;
			}

			if (preg_match("/([0-9\.]+)\)/imsU", $line, $mt))
			{
				if ($cur_b != "")
				{
					$bugs[$cur_num] = $cur_b;
				}
				$cur_num = $mt[1];
				$cur_b = str_replace($mt[0], "", $line);
			}
			else
			{
				$cur_b .= $line;
			}
		}
		if ($cur_b != "")
		{
			$bugs[$mt[1]] = $cur_b;
		}

		ksort($bugs);
		foreach($bugs as $pt => $ct)
		{
			if (strpos($pt, ".") === false)
			{
				$parent = $o->id();
			}
			else
			{
				// find the parent by the sub-bug number
				$parts = explode(".", $pt);
				foreach($num2bug as $num => $bug_id)
				{
					$tparts = explode(".", $num);
					if (count($tparts) == (count($parts) - 1) && substr($pt, 0, strlen($num)) == $num)
					{
						$parent = $bug_id;
					}
				}
			}

			$b = obj();
			$b->set_parent($parent);
			$b->set_class_id(CL_BUG);
			$b->set_name(substr($ct, 0, 50));
			$b->set_prop("bug_content", $ct);
			$b->set_prop("bug_status", $o->prop("bug_status"));
			$b->set_prop("bug_priority", $o->prop("bug_priority"));
			$b->set_prop("who", $o->prop("who"));
			$b->set_prop("bug_type", $o->prop("bug_type"));
			$b->set_prop("bug_class", $o->prop("bug_class"));
			$b->set_prop("bug_severity", $o->prop("bug_severity"));
			$b->set_prop("monitors", $o->prop("monitors"));
			$b->set_prop("deadline", $o->prop("deadline"));
			$b->set_prop("num_hrs_guess", $o->prop("num_hrs_guess"));
			$b->set_prop("num_hrs_real", $o->prop("num_hrs_real"));
			$b->set_prop("num_hrs_to_cust", $o->prop("num_hrs_to_cust"));
			$b->set_prop("customer", $o->prop("customer"));
			$b->set_prop("project", $o->prop("project"));
			$b->set_prop("bug_component", $o->prop("bug_component"));
			$b->set_prop("bug_mail", $o->prop("bug_mail"));
			$b->set_prop("bug_property", $o->prop("bug_property"));
			$b->save();
			$num2bug[$pt] = $b->id();
		}
	}

	function _get_bt($o)
	{
		$pt = $o->path();
		foreach($pt as $pt_o)
		{
			if ($pt_o->class_id() == CL_BUG_TRACKER)
			{
				return $pt_o;
			}
		}
		return null;
	}

	function _get_customer_person($arr)
	{
		// list all ppl for the selected co
		if ($arr["new"])
		{
			if ($arr["request"]["from_problem"])
			{
				$pr = obj($arr["request"]["from_problem"]);
				$cust = $pr->prop("customer");
			}
		}
		else
		{
			$cust = $arr["obj_inst"]->prop("customer");
			$unit = $arr["obj_inst"]->prop("customer_unit");
		}
		if ($this->can("view", $cust) && $this->can("view", $unit))
		{
			// get all ppl for the section
			$sect = get_instance(CL_CRM_SECTION);
			$work_ol = $sect->get_section_workers($unit, true);
			$arr["prop"]["options"] = array("" => t("--vali--")) + $work_ol->names();
		}
		else
		if ($this->can("view", $cust))
		{
			$co = get_instance(CL_CRM_COMPANY);
			$arr["prop"]["options"] = $co->get_employee_picker(obj($cust), true);
		}
	}

	function _get_orderer($arr)
	{
		if ($arr["new"])
		{
			if ($arr["request"]["from_problem"])
			{
				$pr = obj($arr["request"]["from_problem"]);
				$cust = $pr->prop("orderer_co");
			}
		}

		if ($cust)
		{
			$arr["prop"]["value"] = $cust;
		}

		if (!is_array($arr["prop"]["options"]))
		{
			$arr["prop"]["options"] = array("" => t("--vali--"));
		}

		if (!isset($arr["prop"]["options"][$arr["prop"]["value"]]))
		{
			$tmp = obj($arr["prop"]["value"]);
			$arr["prop"]["options"][$arr["prop"]["value"]] = $tmp->name();
		}
	}

	function _get_orderer_unit($arr)
	{
		$prop =& $arr["prop"];
		if ($this->can("view", $arr["obj_inst"]->prop("orderer")))
		{
			$co = obj($arr["obj_inst"]->prop("orderer"));
		}
		else
		{
			$co = get_current_company();
		}
		$co_i = $co->instance();
		$sects = $co_i->get_all_org_sections($co);
		$prop["options"] = array("" => t("--vali--"));
		if (count($sects))
		{
			$ol = new object_list(array("oid" => $sects, "lang_id" => array(), "site_id" => array()));
			$prop["options"] += $ol->names();
		}
		$p = get_current_person();
		if ($arr["new"])
		{
			if ($arr["request"]["from_problem"])
			{
				$tmp = obj($arr["request"]["from_problem"]);
				$prop["value"] = $tmp->prop("orderer_unit");
			}
			else
			{
				$prop["value"] = $p->prop("org_section");
			}
		}
	}

	function _get_orderer_person($arr)
	{
		// list all ppl for the selected co
		if ($arr["new"])
		{
			if ($arr["request"]["from_problem"])
			{
				$pr = obj($arr["request"]["from_problem"]);
				$cust = $pr->prop("orderer_co");
				$unit = $pr->prop("orderer_unit");
			}
		}
		else
		{
			$cust = $arr["obj_inst"]->prop("orderer");
			$unit = $arr["obj_inst"]->prop("orderer_unit");
		}
		if ($this->can("view", $cust) && $this->can("view", $unit))
		{
			// get all ppl for the section
			$sect = get_instance(CL_CRM_SECTION);
			$work_ol = $sect->get_section_workers($unit, true);
			$arr["prop"]["options"] = array("" => t("--vali--")) + $work_ol->names();
		}
		else
		if ($this->can("view", $cust))
		{
			$co = get_instance(CL_CRM_COMPANY);
			$arr["prop"]["options"] = $co->get_employee_picker(obj($cust), true);
		}
	}

	function _get_problems_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_FROM_PROBLEM")));
		$t->table_from_ol($ol, array("name", "createdby", "created", "orderer_co", "orderer_unit", "customer", "project", "requirement", "from_dev_order", "from_bug"), CL_CUSTOMER_PROBLEM_TICKET);
	}

	function _handle_status_change($old, $new, $bug, &$prop)
	{
		$retval = PROP_OK;
		if($new == BUG_STATUS_CLOSED && $old != BUG_STATUS_CLOSED)
		{
			$canclose = 0;
			if(aw_global_get("uid") == $bug->createdby())
			{
				$canclose = 1;
			}
			else
			{
				$u = get_instance(CL_USER);
				$user = obj($u->get_current_user());
				$conn = $user->connections_from(array(
					"type" => RELTYPE_GRP
				));
				$bugtrack = obj($bug->parent());
				$agroups = $bugtrack->connections_from(array(
					"type" => RELTYPE_AGROUP
				));
				foreach($conn as $c)
				{
					foreach($agroups as $agroup)
					{
						if($c->conn["to"] == $agroup->conn["to"])
						{
							$canclose = 1;
						}
					}
				}
			}
			if(!$canclose)
			{
				$retval = PROP_FATAL_ERROR;
				$prop["error"] = t("Puuduvad �igused bugi sulgeda!");
			}
		}

		if ($new == BUG_FEEDBACK && $old != BUG_FEEDBACK)
		{
			// set the creator as the feedback from person
			$u = get_instance(CL_USER);
			$p = $u->get_person_for_uid($bug->createdby());
			$bug->set_prop("bug_feedback_p", $p->id());
			$this->_set_feedback = $p->id();
		}

		return $retval;
	}

	function handle_stopper_start($o)
	{
		if ($o->prop("bug_status") == BUG_OPEN)
		{
			$o->set_prop("bug_status",  BUG_INPROGRESS);
			$o->save();
		}
	}
}
?>
