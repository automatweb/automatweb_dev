<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/task.aw,v 1.55 2006/01/18 18:09:07 kristo Exp $
// task.aw - TODO item
/*

@classinfo syslog_type=ST_TASK relationmgr=yes no_status=1 

@default table=objects
@default group=general

@property customer type=popup_search table=planner field=customer clid=CL_CRM_COMPANY style=relpicker reltype=RELTYPE_CUSTOMER
@caption Klient

@property code type=text size=5 table=planner field=code
@caption Kood

@property project type=popup_search table=planner field=project clid=CL_PROJECT style=relpicker reltype=RELTYPE_PROJECT
@caption Projekt

@property ppa type=hidden store=no no_caption=1

@property info_on_object type=text store=no
@caption Osalejad

@property is_done type=checkbox field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start table=planner
@caption Algus

@property end type=datetime_select table=planner 
@caption L&otilde;peb

@property deadline type=datetime_select table=planner field=deadline 
@caption T&auml;htaeg

@layout personal type=hbox
@caption Kestab terve päeva


	@property whole_day type=checkbox ch_value=1 field=meta method=serialize parent=personal no_caption=1

	@property is_personal type=checkbox ch_value=1 field=meta method=serialize parent=personal no_caption=1
	@caption Isiklik

@property send_bill type=checkbox ch_value=1 table=planner field=send_bill 
@caption Saata arve

@property priority type=textbox size=5 table=planner field=priority
@caption Prioriteet

@layout num_hrs type=hbox 

	@property num_hrs_guess type=textbox size=5 field=meta method=serialize parent=num_hrs
	@caption Prognoositav tundide arv 	

	@property num_hrs_real type=textbox size=5 field=meta method=serialize parent=num_hrs
	@caption Tegelik tundide arv

	@property num_hrs_to_cust type=textbox size=5 field=meta method=serialize parent=num_hrs
	@caption Tundide arv kliendile

@property hr_price type=textbox size=5 field=meta method=serialize 
@caption Tunni hind

@property content type=textarea cols=70 rows=30 field=description table=planner
@caption Sisu

@property client_remind type=checkbox ch_value=1 table=objects field=meta method=serialize
@caption Kliendi teavitamine vajalik

@property bill_no type=text table=planner 
@caption Arve number

@property files type=text 
@caption Failid

@property participants type=popup_search multiple=1 table=objects field=meta method=serialize clid=CL_CRM_PERSON
@caption Osalejad

@property aliasmgr type=aliasmgr store=no
@caption Seostehaldur

@default field=meta
@default method=serialize

@property task_toolbar type=toolbar no_caption=1 store=no group=participants
@caption "Toolbar"

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly,
@caption Kordused
@property calendar_selector type=calendar_selector store=no group=calendars
@caption Kalendrid

@property other_selector type=multi_calendar store=no group=others no_caption=1
@caption Teised

@property project_selector type=project_selector store=no group=projects
@caption Projektid

@property comment_list type=comments group=comments no_caption=1
@caption Kommentaarid

@property rmd type=reminder group=reminders store=no
@caption Meeldetuletus

property participant type=callback callback=cb_participant_selector store=no group=participants no_caption=1
caption Osalejad

@property participant type=participant_selector store=no group=participants no_caption=1
@caption Osalejad

@property search_contact_company type=textbox store=no group=participants
@caption Organisatsioon

@property search_contact_firstname type=textbox store=no group=participants
@caption Eesnimi

@property search_contact_lastname type=textbox store=no group=participants
@caption Perenimi

@property search_contact_code type=textbox store=no group=participants
@caption Isikukood

@property search_contact_button type=submit store=no group=participants action=search_contacts
@caption Otsi

@property search_contact_results type=table store=no group=participants no_caption=1
@caption Tulemuste tabel

@default group=other_exp

	@property other_expenses type=table store=no no_caption=1

@default group=rows

	@property rows_tb type=toolbar store=no no_caption=1
	@property rows type=table store=no no_caption=1

@default group=resources

	@property sel_resources type=table no_caption=1

@groupinfo rows caption=Read 
@groupinfo recurrence caption=Kordumine submit=no
@groupinfo calendars caption=Kalendrid
@groupinfo others caption=Teised submit_method=get
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid
@groupinfo reminders caption=Meeldetuletused
@groupinfo participants caption=Osalejad submit=no
@groupinfo other_exp caption="Muud kulud" 
@groupinfo resources caption="Ressursid" 

@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

@reltype FILE value=2 clid=CL_FILE
@caption Fail

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype PROJECT value=4 clid=CL_PROJECT
@caption Projekt

@reltype RESOURCE value=5 clid=CL_MRP_RESOURCE
@caption Ressurss

@reltype BILL value=6 clid=CL_CRM_BILL
@caption Arve

@reltype ROW value=7 clid=CL_TASK_ROW
@caption Rida
*/

class task extends class_base
{
	function task()
	{
		$this->init(array(
			"tpldir" => "groupware/task",
			"clid" => CL_TASK
		));
	}

	/**
		@attrib name=stopper_pop
		@param id optional
		@param s_action optional
		@param type optional
		@param name optional
		@param desc optional
	**/
	function stopper_pop($arr)
	{
		$this->read_template("stopper_pop.tpl");
		$this->_proc_stop_act($arr);

		$s = "";
		$num = 0;
		if (count(safe_array($_SESSION["crm_stoppers"])) < 1)
		{
			header("Location: ".aw_ini_get("baseurl")."/automatweb/closewin_no_r.html");
			die();
		}
		$this->vars(array(
			"stop_str" => t("Stopperid"),
			"start_str" => t("Algus"),
			"el_str" => t("kulunud"),
			"p_str" => t("Paus"),
			"s_str" => t("K&auml;ivita"),
			"e_str" => t("L&otilde;peta"),
			"d_str" => t("Kustuta")
		));
		foreach(safe_array($_SESSION["crm_stoppers"]) as $_id => $stopper)
		{
			if ($stopper["state"] == "running")
			{
				$el = (time() - $stopper["start"]) + $stopper["base"];
			}
			else
			{
				$el = $stopper["base"];
			}
			$elapsed_hr = (int)($el / 3600);
			$elapsed_min = (int)(($el - $elapsed_hr * 3600) / 60);
			$elapsed_sec = (int)($el - ($elapsed_hr * 3600 + $elapsed_min * 60));
			$this->vars(array(
				"task_type" => $stopper["type"],
				"task_name" => $stopper["name"],
				"time" => date("d.m.Y H:i:s", $stopper["start"]),
				"elapsed" => sprintf("%02d:%02d:%02d",$elapsed_hr,$elapsed_min, $elapsed_sec),
				"number" => $num++,
				"start" => $stopper["start"],
				"el_hr" => $elapsed_hr,
				"el_min" => $elapsed_min,
				"el_sec" => $elapsed_sec,
				"pause_url" => $this->mk_my_orb("stopper_pop", array(
					"id" => $_id,
					"s_action" => "pause"
				)),
				"start_url" => $this->mk_my_orb("stopper_pop", array(
					"id" => $_id,
					"s_action" => "start"
				)),
				"stop_url" => $this->mk_my_orb("stopper_pop", array(
					"id" => $_id,
					"s_action" => "stop"
				)),
				"del_url" => $this->mk_my_orb("stopper_pop", array(
					"id" => $_id,
					"s_action" => "del"
				)),
			));

			if ($stopper["state"] == "running")
			{
				$this->vars(array(
					"PAUSE" => $this->parse("PAUSE"),
					"RUNNER" => $this->parse("RUNNER"),
					"PAUSER" => "",
					"START" => ""
				));
			}
			else
			{
				$this->vars(array(
					"PAUSE" => "",
					"START" => $this->parse("START"),
					"RUNNER" => "",
					"PAUSER" => $this->parse("PAUSER"),
				));
			}

			$s .= $this->parse("STOPPER");
		}

		$this->vars(array(
			"STOPPER" => $s
		));

		return $this->parse();
	}
	function get_property($arr)
	{
		$data = &$arr["prop"];
		if (is_object($arr["obj_inst"]) && $arr["obj_inst"]->prop("is_personal") && aw_global_get("uid") != $arr["obj_inst"]->createdby())
		{
			if (!($arr["prop"]["name"] == "start1" || $arr["prop"]["name"] == "end" || $arr["prop"]["name"] == "deadline"))
			{
				return PROP_IGNORE;
			}
		}

		$retval = PROP_OK;
		switch($data["name"])
		{
			case "start1":
			case "end":
			case "deadline":
				$p = get_instance(CL_PLANNER);
				$cal = $p->get_calendar_for_user();
				if ($cal)
				{
					$calo = obj($cal);
					$data["minute_step"] = $calo->prop("minute_step");
				}
				break;

			case "sel_resources":
				$this->_get_sel_resources($arr);
				break;

			case "name":
				if (is_object($arr["obj_inst"]) && $data["value"] == "")
				{
					$data["value"] = $this->_get_default_name($arr["obj_inst"]);
				}
				if ($arr["new"])
				{
					$data["post_append_text"] = " <a href='#' onClick='document.changeform.ppa.value=1;document.changeform.submit();'>".t("Stopper")."</a>";
				}
				else
				if (is_object($arr["obj_inst"]))
				{
					$url = $this->mk_my_orb("stopper_pop", array(
						"id" => $arr["obj_inst"]->id(),
						"s_action" => "start",
						"type" => t("Toimetus"),
						"name" => urlencode($data["value"])
					));
					$data["post_append_text"] = " <a href='#' onClick='aw_popup_scroll(\"$url\",\"aw_timers\",320,400)'>".t("Stopper")."</a>";
					if ($arr["request"]["stop_pop"] == 1)
					{
						$data["post_append_text"] .= "<script language='javascript'>aw_popup_scroll(\"$url\",\"aw_timers\",320,400)</script>";
					}
				}
				break;

			case "deadline":
				if (!is_object($arr["obj_inst"]) || $arr["new"])
				{
					$data["value"] = time();
				}
				break;

			case "rows_tb":
				$this->_rows_tb($arr);
				break;

			case "rows":
				$this->_rows($arr);
				break;

			case "participants":
				$data["options"] = $this->_get_possible_participants($arr["obj_inst"]);
				$p = array();
				if ($this->can("view", $arr["request"]["alias_to_org"]))
				{
					$ao = obj($arr["request"]["alias_to_org"]);
					if ($ao->class_id() == CL_CRM_PERSON)
					{
						$p[$ao->id()] = $ao->id();
						if (!isset($data["options"][$ao->id()]))
						{
							$data["options"][$ao->id()] = $ao->name();
						}
					}
				}

				if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
				{
					$conns = $arr['obj_inst']->connections_to(array(
						'type' => array(10, 8),//CRM_PERSON.RELTYPE_PERSON_TASK==10
					));
					foreach($conns as $conn)
					{
						$obj = $conn->from();
						$p[$obj->id()] = $obj->id();
						if (!isset($data["options"][$obj->id()]))
						{
							$data["options"][$obj->id()] = $obj->name();
						}
					}
				}
				$data["value"] = $p;
				break;

			case "code":
				if (is_object($arr["obj_inst"]))
				{
					$pj = $arr["obj_inst"]->prop("project");
					if ($this->can("view", $pj))
					{
						$proj = obj($pj);
						$data["value"] = $proj->prop("code");
					}
				}
				break;

			case "hr_price":
				// get first person connected as participant and read their hr price
				if ($data["value"] == "" && is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
				{
					$conns = $arr['obj_inst']->connections_to(array());
					foreach($conns as $conn)
					{
						if($conn->prop('from.class_id')==CL_CRM_PERSON)
						{
							$pers = $conn->from();
							// get profession
							$rank = $pers->prop("rank");
							if (is_oid($rank) && $this->can("view", $rank))
							{
								$rank = obj($rank);
								$data["value"] = $rank->prop("hr_price");
								break;
							}
						}
					}

				}
				break;

			case "bill_no":
				// small conversion - if set, create a relation instead and clear, so that we can have multiple
				if ($this->can("view", $data["value"] ))
				{
					$arr["obj_inst"]->connect(array(
						"to" => $data["value"],
						"type" => "RELTYPE_BILL"
					));
					$arr["obj_inst"]->set_prop("bill_no", "");
					$arr["obj_inst"]->save();
					$data["value"] = "";
				}

				if (!$arr["new"] && is_object($arr["obj_inst"]))
				{
					$cs = $arr["obj_inst"]->connections_from(array("type" => "RELTYPE_BILL"));
					if (!count($cs))
					{
						$ol = new object_list();
					}
					else
					{
						$ol = new object_list($cs);
					}
					$data["value"] = html::obj_change_url($ol->arr());
				}

				if ($data["value"] == "" && is_object($arr["obj_inst"]) && !$arr["new"])
				{
					$data["value"] = html::href(array(
						"url" => $this->mk_my_orb("create_bill_from_task", array("id" => $arr["obj_inst"]->id(),"post_ru" => get_ru())),
						"caption" => t("Loo uus arve")
					));
				}
				break;

			case 'info_on_object':
				if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
				{
					$conns = $arr['obj_inst']->connections_to(array(
						'type' => array(10, 8),//CRM_PERSON.RELTYPE_PERSON_TASK==10
					));
					foreach($conns as $conn)
					{
						$obj = $conn->from();
						//isik
						$data['value'].= html::href(array(
								'url' => html::get_change_url($obj->id()),
								'caption' => $obj->name(),
						));
						//isiku default firma
						if(is_oid($obj->prop('work_contact')))
						{
							$company = new object($obj->prop('work_contact'));
							$data['value'] .= " ".html::href(array(
									'url' => html::get_change_url($company->id()),
									'caption' => $company->name(),
							));
						}
						//isiku ametinimetused...
						$conns2 = $obj->connections_from(array(
							'type' => 'RELTYPE_RANK',
						));
						$professions = '';
						foreach($conns2 as $conn2)
						{
							$professions.=', '.$conn2->prop('to.name');
						}
						if(strlen($professions))
						{
							$data['value'].=$professions;
						}
						//isiku telefonid
						$conns2 = $obj->connections_from(array(
							'type' => 'RELTYPE_PHONE'
						));
						$phones = '';
						foreach($conns2 as $conn2)
						{
							$phones.=', '.$conn2->prop('to.name');
						}
						if(strlen($phones))
						{
							$data['value'].=$phones;
						}
						//isiku emailid
						$conns2 = $obj->connections_from(array(
							'type' => 'RELTYPE_EMAIL',
						));
						$emails = '';
						foreach($conns2 as $conn2)
						{
							$to_obj = $conn2->to();
							$emails.=', '.$to_obj->prop('mail');
						}
						if(strlen($emails))
						{
							$data['value'].=$emails;
						}						
						$data['value'].='<br>';
					}
				}
			break;

			case 'task_toolbar' :
			{
				$tb = &$data['toolbar'];
				$tb->add_button(array(
					'name' => 'del',
					'img' => 'delete.gif',
					'tooltip' => t('Kustuta valitud'),
					'action' => 'submit_delete_participants_from_calendar',
				));

				$tb->add_separator();

				$tb->add_button(array(
					'name' => 'Search',
					'img' => 'search.gif',
					'tooltip' => t('Otsi'),
					'url' => aw_url_change_var(array(
						'show_search' => 1,
					)),
				));

				$tb->add_button(array(
					'name' => 'save',
					'img' => 'save.gif',
					'tooltip' => t('Salvesta'),
					"action" => "save_participant_search_results"
				));

				$tb->add_button(array(
					'name' => 'csv',
					'img' => 'ftype_xls.gif',
					'tooltip' => 'CSV',
					"url" => aw_url_change_var("get_csv_file", 1)
				));

				$this->return_url=aw_global_get('REQUEST_URI');
				break;
			}

			case "project":
				if ($this->can("view",$arr["request"]["alias_to_org"]))
				{
					$ol = new object_list(array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_ORDERER" => $arr["request"]["alias_to_org"],
					));
				}
				else
				if (is_object($arr["obj_inst"]) && $this->can("view", $arr["obj_inst"]->prop("customer")))
				{
					$ol = new object_list(array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_ORDERER" => $arr["obj_inst"]->prop("customer"),
					));
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

				$data["options"] = array("" => "") + $ol->names();

				if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
				{
					foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PROJECT")) as $c)
					{
						$data["options"][$c->prop("to")] = $c->prop("to.name");
					}
				}

				if ($arr["request"]["set_proj"])
				{
					$data["value"] = $arr["request"]["set_proj"];
				}

				if (!isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$tmp->id()] = $tmp->name();
				}

				asort($data["options"]);
				break;

			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$cst = $i->get_my_customers();
				if (!count($cst))
				{
					$data["options"] = array("" => "");
				}
				else
				{
					$ol = new object_list(array("oid" => $cst));
					$data["options"] = array("" => "") + $ol->names();
				}
				if ($this->can("view", $arr["request"]["alias_to_org"]))
				{
					$ao = obj($arr["request"]["alias_to_org"]);
					if ($ao->class_id() == CL_CRM_PERSON)
					{
						$u = get_instance(CL_USER);
						$data["value"] = $u->get_company_for_person($ao->id());
					}
					else
					{
						$data["value"] = $arr["request"]["alias_to_org"];
					}
				}

				if (is_object($arr["obj_inst"]) && !$arr["new"])
				{
					foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
					{
						$data["options"][$c->prop("to")] = $c->prop("to.name");
					}
				}

				if (!isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$tmp->id()] = $tmp->name();
				}

				asort($data["options"]);
				if (is_object($arr["obj_inst"]) && $arr["obj_inst"]->class_id() == CL_TASK)
				{
					$arr["obj_inst"]->set_prop("customer", $data["value"]);
				}
				break;

			case "other_expenses":
				$this->_other_expenses($arr);
				break;

			case "files":
				$this->_get_files($arr);
				break;

			case "search_contact_company":
			case "search_contact_firstname":
			case "search_contact_lastname":
			case "search_contact_code":
				if ($arr["request"]["class"] != "planner")
				{
					$data["value"] = $arr["request"][$data["name"]];
				}
				break;

			case "search_contact_results":
				$p = get_instance(CL_PLANNER);
				$data["value"] = $p->do_search_contact_results_tbl($arr["request"]);
				break;
		};
		return $retval;
	}
	
	function cb_calendar_others($arr)
	{
		$elib = get_instance("calendar/event_property_lib");
		return $elib->calendar_others($arr);
	}


	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			case "sel_resources":
				$this->_set_resources($arr);
				break;

			case "rows":
				$this->_save_rows($arr);
				break;

			case "files":
				$this->_set_files($arr);
				break;

			case "participants":
				if (!is_oid($arr["obj_inst"]->id()))
				{
					$this->post_save_add_parts = safe_array($prop["value"]);
					return PROP_IGNORE;
				}

				$p = array();
				$conns = $arr['obj_inst']->connections_to(array(
					'type' => array(10, 8),//CRM_PERSON.RELTYPE_PERSON_TASK==10
				));
				foreach($conns as $conn)
				{
					$obj = $conn->from();
					$p[$obj->id()] = $obj->id();
				}

				foreach(safe_array($prop["value"]) as $person)
				{
					$this->add_participant($arr["obj_inst"], obj($person));
				}

				foreach($p as $k)
				{
					if ($k != "")
					{
						if (!in_array($k, $prop["value"]))
						{
							$po = obj($k);
							if ($po->is_connected_to(array("to" => $arr["obj_inst"]->id())))
							{
								$po->disconnect(array("from" => $arr["obj_inst"]->id()));
							}
						}
					}
				}
				if ($prop["value"] == "")
				{
					$u = get_instance(CL_USER);
					$po = obj($u->get_current_person());
					$po->connect(array(
						"to" => $arr["obj_inst"]->id(),
						"reltype" => 10
					));
				}

				break;

			case "code":
				$pj = $arr["obj_inst"]->prop("project");
				if ($this->can("view", $pj))
				{
					$proj = obj($pj);
					$prop["value"] = $proj->prop("code");
					$arr["obj_inst"]->set_prop("code", $proj->prop("code"));
				}
				break;

			case "other_selector":
				$elib = get_instance("calendar/event_property_lib");
				$elib->process_other_selector($arr);
				break;

			case "whole_day":
				if ($prop["value"])
				{
					// ahaa! võtab terve päeva!
					$start = $arr["obj_inst"]->prop("start1");
					list($m,$d,$y) = explode("-",date("m-d-Y",$start));
					$daystart = mktime(9,0,0,$m,$d,$y);
					$dayend = mktime(17,0,0,$m,$d,$y);
					$arr["obj_inst"]->set_prop("start1",$daystart);
					$arr["obj_inst"]->set_prop("end",$dayend);
				};
				break;

			case "customer":
				$prop["value"] = $_POST["customer"];
				break;

			case "project":
				$prop["value"] = $_POST["project"];
				// add to proj
				if (is_oid($prop["value"]) && $this->can("view", $prop["value"]))
				{
					$this->add_to_proj = $prop["value"];
				}
				break;

			case "other_expenses":
				$set = array();
				foreach(safe_array($_POST["exp"]) as $entry)
				{
					if ($entry["exp"] != "" && $entry["cost"] != "")
					{
						$set[] = $entry;
					}
				}
				$arr["obj_inst"]->set_meta("other_expenses", $set);
				break;
		};
		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_mod_retval($arr)
	{
		$arr["args"]["stop_pop"] = $arr["request"]["ppa"];
	}

	function callback_pre_save($arr)
	{
		if ($arr["obj_inst"]->name() == "")
		{
			$arr["obj_inst"]->set_name($this->_get_default_name($arr["obj_inst"]));
		}
	}

	function callback_post_save($arr)
	{
		if (is_array($this->post_save_add_parts))
		{
			foreach(safe_array($this->post_save_add_parts) as $person)
			{
				$this->add_participant($arr["obj_inst"], obj($person));
			}
			
		}
		//the person who added the task will be a participant, whether he likes it
		//or not
		if(!empty($arr['new']))
		{
			$user = get_instance(CL_USER);
			$person = new object($user->get_current_person());
			$person->connect(array(
				'reltype' => 'RELTYPE_PERSON_TASK',
				'to' => $arr['obj_inst'],
			));
		}

		if ($this->add_to_proj)
		{
			$arr["obj_inst"]->create_brother($this->add_to_proj);
		}

		$pl = get_instance(CL_PLANNER);
		$pl->post_submit_event($arr["obj_inst"]);
	}
	
	function request_execute($obj)
	{
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $obj->name(),
			"time" => date("d-M-y H:i",$obj->prop("start1")),
			"content" => nl2br($obj->prop("content")),
		));
		return $this->parse();
	}

	function cb_participant_selector($arr)
	{
		$elib = get_instance('calendar/event_property_lib');
		return $elib->participant_selector($arr);
	}

	function _init_other_exp_t(&$t)
	{
		$t->define_field(array(
			"name" => "exp",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "cost",
			"caption" => t("Hind")
		));
	}

	function _other_expenses($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_other_exp_t($t);
		
		$dat = safe_array($arr["obj_inst"]->meta("other_expenses"));
		$dat[] = array();
		$dat[] = array();
		$dat[] = array();

		$nr = 1;
		foreach($dat as $exp)
		{
			$t->define_data(array(
				"exp" => html::textbox(array(
					"name" => "exp[$nr][exp]",
					"value" => $exp["exp"]
				)),
				"cost" => html::textbox(array(
					"name" => "exp[$nr][cost]",
					"size" => 5,
					"value" => $exp["cost"]
				))
			));
			$nr++;
		}

		$t->set_sortable(false);
	}

	/**
		@attrib name=search_for_proj
		@param retf optional
	**/
	function search_for_proj($arr)
	{
		
	}

	function _get_files($arr)
	{
		$objs = array();

		if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
		{
			$ol = new object_list($arr["obj_inst"]->connections_from(array(
				"type" => "RELTYPE_FILE"
			)));
			$objs = $ol->arr();
		}

		$objs[] = obj();

		$types = array(
			CL_FILE => t(""),
			CL_CRM_MEMO => t("Memo"),
			CL_CRM_DOCUMENT => t("CRM Dokument"),
			CL_CRM_DEAL => t("Leping"),
			CL_CRM_OFFER => t("Pakkumine")
		);

		$u = get_instance(CL_USER);
		$impl = $u->get_current_company();
		if ($this->can("view", $impl))
		{
			$f = get_instance("applications/crm/crm_company_docs_impl");
			$fldo = $f->_init_docs_fld(obj($impl));
			$ot = new object_tree(array(
				"parent" => $fldo->id(),
				"class_id" => CL_MENU
			));
			$folders = array($fldo->id() => $fldo->name());
			$this->_req_level = 0;
			$this->_req_get_folders($ot, $folders, $fldo->id());
		}
		else
		{
			$fldo = obj();
			$folders = array();
		}

		$clss = aw_ini_get("classes");
		foreach($objs as $idx => $o)
		{
			$this->vars(array(
				"name" => $o->name(),
				"idx" => $idx,
				"types" => $this->picker($types)
			));

			if (is_oid($o->id()))
			{
				$ff = $o->get_first_obj_by_reltype("RELTYPE_FILE");
				if (!$ff)
				{
					$ff = $o;
				}
				$fi = $ff->instance();
				$fu = html::href(array(
					"url" => $fi->get_url($ff->id(), $ff->name()),
					"caption" => $ff->name()
				));
				$data[] = array(
					"name" => html::get_change_url($o->id(), array("return_url" => get_ru()), $o->name()),
					"file" => $fu,
					"type" => $clss[$o->class_id()]["name"],
					"del" => html::href(array(
						"url" => $this->mk_my_orb("del_file_rel", array(
								"return_url" => get_ru(),
								"fid" => $o->id(),
						)),
						"caption" => t("Kustuta")
					)),
					"folder" => $o->path_str(array(
						"start_at" => $fldo->id(),
						"path_only" => true
					))
				);
			}
			else
			{
				$data[] = array(
					"name" => html::textbox(array(
						"name" => "fups_d[$idx][tx_name]"
					)),
					"file" => html::fileupload(array(
						"name" => "fups_".$idx
					)),
					"type" => html::select(array(
						"options" => $types,
						"name" => "fups_d[$idx][type]"
					)),
					"del" => "",
					"folder" => html::select(array(
						"name" => "fups_d[$idx][folder]",
						"options" => $folders
					))
				);
			}
		}

		classload("vcl/table");
		$t = new vcl_table(array(
			"layout" => "generic",
		));
		
		$t->define_field(array(
			"caption" => t("Nimi"),
			"name" => "name",
		));

		$t->define_field(array(
			"caption" => t("Fail"),
			"name" => "file",
		));

		$t->define_field(array(
			"caption" => t("T&uuml;&uuml;p"),
			"name" => "type",
		));

		$t->define_field(array(
			"caption" => t("Kataloog"),
			"name" => "folder",
		));

		$t->define_field(array(
			"caption" => t(""),
			"name" => "del",
		));

		foreach($data as $e)
		{
			$t->define_data($e);
		}

		$arr["prop"]["value"] = $t->draw();
	}

	function _set_files($arr)
	{
		$t = obj($arr["request"]["id"]);
		$u = get_instance(CL_USER);
		$co = obj($u->get_current_company());
		foreach(safe_array($_POST["fups_d"]) as $num => $entry)
		{
			if (is_uploaded_file($_FILES["fups_".$num]["tmp_name"]))
			{
				$f = get_instance("applications/crm/crm_company_docs_impl");
				$fldo = $f->_init_docs_fld($co);
				if ($this->can("add", $entry["folder"]))
				{
					$fldo = obj($entry["folder"]);
				}
				if (!$fldo)
				{
					return;
				}

				if ($entry["type"] == CL_FILE)
				{
					// add file
					$f = get_instance(CL_FILE);
					$fil = $f->add_upload_image("fups_$num", $fldo->id());
					if (is_array($fil))
					{
						$t->connect(array(
							"to" => $fil["id"],
							"reltype" => "RELTYPE_FILE"
						));
					}
				}
				else
				{
					$o = obj();
					$o->set_class_id($entry["type"]);
					$o->set_name($entry["tx_name"] != "" ? $entry["tx_name"] : $_FILES["fups_$num"]["name"]);

			
					$o->set_parent($fldo->id());
					if ($entry["type"] != CL_FILE)
					{
						$o->set_prop("project", $t->prop("project"));
						$o->set_prop("task", $t->id());
						$o->set_prop("customer", $t->prop("customer"));
					}
					$o->save();

					// add file
					$f = get_instance(CL_FILE);
					$fil = $f->add_upload_image("fups_$num", $o->id());
					if (is_array($fil))
					{
						$o->connect(array(
							"to" => $fil["id"],
							"reltype" => "RELTYPE_FILE"
						));
						$t->connect(array(
							"to" => $o->id(),
							"reltype" => "RELTYPE_FILE"
						));
					}
				}
			}
		}
		return $arr["post_ru"];
	}

	function _init_rows_t(&$t)
	{
		$t->define_field(array(
			"name" => "task",
			"caption" => t("Tegevus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "impl",
			"caption" => t("Teostaja"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "time_guess",
			"caption" => t("Prognoositud tunde"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "time_real",
			"caption" => t("Kulunud tunde"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "time_to_cust",
			"caption" => t("Tunde kliendile"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "done",
			"caption" => t("<a href='javascript:void(0)' onClick='aw_sel_chb(document.changeform,\"done\")'>Tehtud</a>"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "on_bill",
			"caption" => t("<a href='javascript:void(0)' onClick='aw_sel_chb(document.changeform,\"on_bill\")'>Arvele</a>"),
			"align" => "center"
		));

		/*$t->define_field(array(
			"name" => "com",
			"caption" => t("Kommentaar"),
			"align" => "center"
		));*/
	}

	function _rows($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_rows_t($t);

		$impls = $this->_get_possible_participants($arr["obj_inst"], true, $arr["obj_inst"]->prop("participants"));

		$u = get_instance(CL_USER);
		$def_impl = $u->get_current_person();
		$def_impl = array($def_impl => $def_impl);

		$cs = $arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ROW"));
		$cs[] = NULL;
		$cs[] = NULL;
		$cs[] = NULL;
		$null_idx = 0;
		foreach($cs as $ro)
		{
			if ($ro === null)
			{
				$idx = $null_idx--;
				$row = obj();
			}
			else
			{
				$idx = $ro->prop("to");
				$row = $ro->to();
			}
			$date_sel = "<A HREF='#'  onClick=\"var cal=new CalendarPopup();cal.select(aw_get_el('rows[$idx][date]'),'anchor".$idx."','dd/MM/yy'); return false;\"
						   NAME='anchor".$idx."' ID='anchor".$idx."'>".t("vali")."</A>";

			$is = (is_array($row->prop("impl")) && count($row->prop("impl"))) ? $row->prop("impl") : $def_impl;
			foreach(safe_array($is) as $is_id)
			{
				if (!isset($impls[$is_id]))
				{
					$iso = obj($is_id);
					$impls[$is_id] = $iso->name();
				}
			}
			$bno = "";
			if ($row->prop("bill_id"))
			{
				$bo = obj($row->prop("bill_id"));
				$bno = $bo->prop("bill_no");
			}
			$t->define_data(array(
				"task" => "<a name='row_".$idx."'/>".html::textarea(array(
					"name" => "rows[$idx][task]",
					"value" => $row->prop("content"),
					"rows" => 5,
					"cols" => 50
				)),
				"date" => html::textbox(array(
					"name" => "rows[$idx][date]",
					"value" => date("d/m/y",($row->prop("date") > 100 ? $row->prop("date") : $arr["obj_inst"]->prop("start1"))),
					"size" => 7
				)).$date_sel,
				"impl" => html::select(array(
					"name" => "rows[$idx][impl]",
					"options" => $impls,
					"value" => $is,
					"multiple" => 1
				)),
				"time_guess" => html::textbox(array(
					"name" => "rows[$idx][time_guess]",
					"value" => $row->prop("time_guess"),
					"size" => 5
				)),
				"time_real" => html::textbox(array(
					"name" => "rows[$idx][time_real]",
					"value" => $row->prop("time_real"),
					"size" => 5
				)),
				"time_to_cust" => html::textbox(array(
					"name" => "rows[$idx][time_to_cust]",
					"value" => $row->prop("time_to_cust"),
					"size" => 5
				)),
				"done" => html::checkbox(array(
					"name" => "rows[$idx][done]",
					"value" => 1,
					"checked" => $row->prop("done")
				)),
				"on_bill" => ($row->prop("bill_id") ? sprintf(t("Arve nr %s"), $bno) : html::checkbox(array(
					"name" => "rows[$idx][on_bill]",
					"value" => 1,
					"checked" => $row->prop("on_bill")
				))),
			));
		}
		$t->set_sortable(false);
	}

	function get_task_bill_rows($task, $only_on_bill = true, $bill_id = null)
	{
		// check if task has rows defined that go on bill
		// if, then ret those
		// if not, return data for bill

		$rows = array();
		//$dat = safe_array($task->meta("rows"));
		foreach($task->connections_from(array("type" => "RELTYPE_ROW")) as $c)
		{
			$row = $c->to();
			$idx = $row->id();
			if (($row->prop("on_bill") == 1 || !$only_on_bill) && ($bill_id === null || $row->prop("bill_id") == $bill_id))
			{
				$id = $task->id()."_".$idx;
				$rows[$id] = array(
					"name" => $row->prop("task"),
					"unit" => t("tund"),
					"date" => $row->prop("date"),
					"price" => $task->prop("hr_price"),
					"amt" => $row->prop("time_to_cust"),
					"sum" => str_replace(",", ".", $row->prop("time_to_cust")) * $task->prop("hr_price"),
					"has_tax" => 1,
					"on_bill" => 1
				);
			}
		}

		if (!count($rows))
		{
			// add the main task to the first bill only
			$add = true;
			if ($bill_id !== null)
			{
				$conns = $task->connections_from(array("type" => "RELTYPE_BILL", "order_by" => "to.id"));
				$bc = reset($conns);
				if ($bill_id != $bc->prop("to"))
				{
					$add = false;
				}
			}

			if ($add)
			{
				$rows[$task->id()] = array(
					"name" => $task->name(),
					"unit" => t("tund"),
					"price" => $task->prop("hr_price"),
					"date" => $task->prop("start1"),
					"amt" => $task->prop("num_hrs_to_cust"),
					"sum" => str_replace(",", ".", $task->prop("num_hrs_to_cust")) * $task->prop("hr_price"),
					"has_tax" => 1,
					"on_bill" => 1
				);
			}
		}

		// add other expenses rows
		foreach(safe_array($task->meta("other_expenses")) as $idx => $oe)
		{
			$id = $task->id()."_oe_".$idx;
			$rows[$id] = array(
				"name" => $oe["exp"],
				"unit" => "",
				"price" => $oe["cost"],
				"amt" => 1,
				"sum" => $oe["cost"],
				"has_tax" => 1,
				"is_oe" => true,
				"on_bill" => 1
			);
		}
		

		return $rows;
	}

	/**
		@attrib name=del_file_rel
		@param fid required 
		@param from required
		@param return_url optional 
	**/
	function del_file_rel($arr)
	{
		$f = obj($arr["fid"]);
		$ff = $f->get_first_obj_by_reltype("RELTYPE_FILE");
		if ($ff)
		{
			$ff->delete();
		}
		$f->delete();
		return $arr["return_url"];
	}

	function _req_get_folders($ot, &$folders, $parent)
	{
		$this->_req_level++;
		$objs = $ot->level($parent);
		foreach($objs as $o)
		{
			$folders[$o->id()] = str_repeat("&nbsp;&nbsp;&nbsp;", $this->_req_level).$o->name();
			$this->_req_get_folders($ot, $folders, $o->id());
		}
		$this->_req_level--;
	}

	function _rows_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$b = array(
			'name' => 'create_bill',
			'img' => 'save.gif',
			'tooltip' => t('Loo arve'),
		);

		if ($arr["obj_inst"]->prop("bill_no") != "")
		{
			$b["url"] = html::get_change_url($arr["obj_inst"]->prop("bill_no"), array("return_url" => get_ru()));
		}
		else
		{
			$b['action'] = 'create_bill_from_task';
		}
		$tb->add_button($b);
	}

	/**
		@attrib name=create_bill_from_task
		@param id required type=int acl=view
		@param post_ru required
	**/
	function create_bill_from_task($arr)
	{
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();

		$task = obj($arr["id"]);

		$i = get_instance(CL_CRM_COMPANY);
		return $i->create_bill(array(
			"id" => $co,
			"proj" => $task->prop("project"),
			"cust" => $task->prop("customer"),
			"sel" => array($task->id() => $task->id()),
			"post_ru" => $arr["post_ru"]
		));
	}

	function _get_default_name($o)
	{	
		$n = $o->prop_str("project");
		if ($n == "")
		{
			$n = $o->prop_str("customer");
			if ($n == "")
			{
				$uid = $o->createdby();
				if ($uid != "")
				{	
					$u = get_instance("users");
					$u_o = obj($u->get_oid_for_uid($uid));

					$u = get_instance(CL_USER);
					$p = obj($u->get_person_for_user($u_o));
					$n = sprintf(t("%s toimetus"), $p->name());
				}
			}
		}
		return $n;
	}

	function _get_possible_participants($o, $proj_only = false, $sel = array())
	{
		$opts = array();
		if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
		{
			$conns = $arr['obj_inst']->connections_to(array(
				'type' => array(10, 8),//CRM_PERSON.RELTYPE_PERSON_TASK==10
			));
			foreach($conns as $conn)
			{
				$obj = $conn->from();
				$opts[$obj->id()] = $obj->name();
			}
		}
		// also add all workers for my company
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();
		$w = array();
		$i = get_instance(CL_CRM_COMPANY);
		$w = array_keys($i->get_employee_picker(obj($co), false, true));
		foreach($w as $oid)
		{
			$t = obj($oid);
			$opts[$oid] = $t->name();
		}
		asort($opts);

		if ($proj_only)
		{
			// filter by project participants
			if ($this->can("view", $o->prop("project")))
			{
				$p = obj($o->prop("project"));
				$p_p = array();
				foreach($p->connections_from(array("type" => "RELTYPE_PARTICIPANT")) as $c)
				{
					$p_p[$c->prop("to")] = $c->prop("to");
				}

				foreach($opts as $k => $v)
				{
					if (!isset($p_p[$k]) && !isset($sel[$k]))
					{
						unset($opts[$k]);
					}
				}
			}
		}

		return array("" => t("--vali--")) + $opts;
	}

	function add_participant($task, $person)
	{
		$pl = get_instance(CL_PLANNER);
		$person->connect(array(
			"to" => $task->id(),
			"reltype" => "RELTYPE_PERSON_TASK"
		));

		// also add to their calendar
		if (($cal = $pl->get_calendar_for_person($person)))
		{
			$pl->add_event_to_calendar(obj($cal), $task);
		}
	}

	function _init_sel_res_t(&$t)
	{
		$t->define_field(array(
			"name" => "cal",
			"caption" => t("Kalender"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "events",
			"caption" => t("Staatus"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Vali"),
			"align" => "center"
		));
	}

	function _get_sel_resources($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sel_res_t($t);

		// get resources from my company
		$co = get_instance(CL_CRM_COMPANY);
		$res = $co->get_my_resources();

		$sel_res = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_RESOURCE")));
		$sel_ids = array_flip($sel_res->ids());
		foreach($res->arr() as $r)
		{
			// get events for the resource 
			$avail = true;
			$evstr = "";
			$ri = $r->instance();
			$events = $ri->get_events_for_range(
				$r, 
				$arr["obj_inst"]->prop("start1"), 
				$arr["obj_inst"]->prop("end")
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
					$r, 
					$arr["obj_inst"]->prop("start1"), 
					$arr["obj_inst"]->prop("end")
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
					$r, 
					$arr["obj_inst"]->prop("start1"), 
					$arr["obj_inst"]->prop("end")
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

			$t->define_data(array(
				"name" => html::obj_change_url($r),
				"cal" => html::get_change_url($r->id(), array("return_url" => get_ru(), "group" => "grp_resource_schedule"), t("Vaata")),
				"sel" => html::checkbox(array(
					"name" => "sel[".$r->id()."]",
					"value" => 1,
					"checked" => isset($sel_ids[$r->id()]) ? true : false
				)),
				"events" => ($avail ? t("Ressurss on vaba") : $evstr)
			));
		}
	}

	function _set_resources($arr)
	{
		$sel_res = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_RESOURCE")));
		$sel_ids = array_flip($sel_res->ids());

		$sbt = safe_array($arr["request"]["sel"]);
		foreach($sbt as $_id => $one)
		{
			if (!isset($sel_ids[$_id]))
			{
				$arr["obj_inst"]->connect(array(
					"to" => $_id,
					"type" => "RELTYPE_RESOURCE"
				));
			}
		}

		foreach($sel_ids as $_id => $b)
		{
			if (!isset($sbt[$_id]))
			{
				$arr["obj_inst"]->disconnect(array(
					"from" => $_id
				));
			}
		}
	}

	function new_change($arr)
	{
		aw_session_set('org_action',aw_global_get('REQUEST_URI'));
		return parent::new_change($arr);
	}

	function stopper_is_running($task_id)
	{
		return $_SESSION["crm_stoppers"][$task_id]["state"] == "running";
	}

	function get_stopper_time($task_id)
	{
		$elapsed = time() - $_SESSION["crm_stoppers"][$task_id]["start"];
		return $_SESSION["crm_stoppers"][$task_id]["base"] + $elapsed;
	}

	function _proc_stop_act($arr)
	{
		if ($arr["s_action"] == "del")
		{
			unset($_SESSION["crm_stoppers"][$arr["id"]]);
		}
		else
		if ($arr["s_action"] == "pause")
		{
			$elapsed = time() - $_SESSION["crm_stoppers"][$arr["id"]]["start"];
			$_SESSION["crm_stoppers"][$arr["id"]]["base"] += $elapsed;
			$_SESSION["crm_stoppers"][$arr["id"]]["state"] = "paused";
		}
		else
		if ($arr["s_action"] == "stop")
		{
			// stop timer and write row to task
			$stopper = $_SESSION["crm_stoppers"][$arr["id"]];
			$u = get_instance(CL_USER);
			$cp = obj($u->get_current_person());
			$elapsed = (time() - $stopper["start"]) + $stopper["base"];
			$el_hr = (int)($elapsed / 3600);
			$el_min = (int)(($elapsed - $el_hr * 3600) / 60);
			if ($el_min < 15)
			{
				$el_hr += 0.25;
			}
			else
			if ($el_min < 30)
			{
				$el_hr += 0.5;
			}
			else
			if ($el_min < 45)
			{
				$el_hr += 0.75;
			}
			$o = obj($arr["id"]);
			$row = obj();
			$row->set_parent($o->id());
			$row->set_class_id(CL_TASK_ROW);
			$row->set_prop("content", $arr["desc"]);
			$row->set_prop("date", $stopper["start"]);
			$row->set_prop("impl", array($cp->id() => $cp->id()));
			$row->set_prop("time_real", $el_hr);
			$row->set_prop("done", 1);
			$row->save();
			$o->connect(array(
				"to" => $row->id(),
				"type" => "RELTYPE_ROW"
			));
			unset($_SESSION["crm_stoppers"][$arr["id"]]);
		}
		else
		if ($arr["s_action"] == "start")
		{
			// pause all running timers
			foreach((array)$_SESSION["crm_stoppers"] as $k => $stopper)
			{
				if ($stopper["state"] == "running" && $k != $arr["id"])
				{
					$elapsed = time() - $stopper["start"];
					$_SESSION["crm_stoppers"][$k]["base"] += $elapsed;
					$_SESSION["crm_stoppers"][$k]["state"] = "paused";
				}
			}

			$k = $arr["id"];
			if ($_SESSION["crm_stoppers"][$k]["state"] != "running")
			{
				$_SESSION["crm_stoppers"][$k]["start"] = time();
			}

			if (isset($arr["type"]))
			{
				$_SESSION["crm_stoppers"][$k]["type"] = $arr["type"];
			}
			if (isset($arr["name"]))
			{
				$_SESSION["crm_stoppers"][$k]["name"] = $arr["name"];
			}
			$_SESSION["crm_stoppers"][$k]["state"] = "running";
		}
	}

	/**
		@attrib name=search_contacts
	**/
	function search_contacts($arr)
	{
		return $this->mk_my_orb('change',array(
				'id' => $arr['id'],
				'group' => $arr['group'],
				'search_contact_company' => urlencode($arr['search_contact_company']),
				'search_contact_firstname' => urlencode($arr['search_contact_firstname']),
				'search_contact_lastname' => urlencode($arr['search_contact_lastname']),
				'search_contact_code' => urlencode($arr['search_contact_code']),
			),
			$arr['class']
		);
	}

	/**

		@attrib name=save_participant_search_results

	**/
	function save_participant_search_results($arr)
	{
		$p = get_instance(CL_PLANNER);
		return $p->save_participant_search_results($arr);
	}

	function callback_mod_tab($arr)
	{
		if ($arr["obj_inst"]->prop("is_personal") && aw_global_get("uid") != $arr["obj_inst"]->createdby())
		{
			if ($arr["id"] != "general")
			{
				return false;
			}
		}
		return true;
	}

	function _save_rows($arr)
	{
		$res = array();
		// go over existing rows and save info for those
		// add new rows that are without oid
		// I think rows should not be deleted. or we can add that later
		$task = obj($arr["request"]["id"]);

		foreach(safe_array($_POST["rows"]) as $_oid => $e)
		{
			if (!is_oid($_oid))
			{
				if ($e["task"] == "")
				{
					continue;
				}
				$o = obj();
				$o->set_class_id(CL_TASK_ROW);
				$o->set_parent($arr["request"]["id"]);

			}
			else
			{
				$cs = $task->connections_from(array("to" => $_oid));
				$c = reset($cs);
				$o = $c->to();
				if ($e["task"] == "")
				{
					$o->delete();
					continue;
				}
			}

			list($d,$m,$y) = explode("/", $e["date"]);
			$o->set_prop("date", mktime(0,0,0, $m, $d, $y));
			if ($e["time_to_cust"] == "")
			{
				$e["time_to_cust"] = $e["time_real"];
			}

			foreach(safe_array($e["impl"]) as $i)
			{
				if ($this->can("view", $i))
				{
					$this->add_participant($task, obj($i));
				}
			}

			$o->set_prop("content", $e["task"]);
			$o->set_prop("impl", $e["impl"]);
			$o->set_prop("time_guess", $e["time_guess"]);
			$o->set_prop("time_real", $e["time_real"]);
			$o->set_prop("time_to_cust", $e["time_to_cust"]);
			$o->set_prop("done", $e["done"]);
			$o->set_prop("on_bill", $e["on_bill"]);
			$o->save();

			if (!is_oid($_oid))
			{
				$task->connect(array(
					"to" => $o->id(),
					"type" => "RELTYPE_ROW"
				));
			}
		}
	}
}
?>
