<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/task.aw,v 1.143 2006/09/25 14:34:40 kristo Exp $
// task.aw - TODO item
/*

@classinfo syslog_type=ST_TASK confirm_save_data=1

@default table=objects


@default group=general
@layout top_bit type=vbox closeable=1 area_caption=P&otilde;hiandmed

	@layout top_2way type=hbox parent=top_bit

		@layout top_2way_left type=vbox parent=top_2way

			@property name type=textbox table=objects field=name parent=top_2way_left
			@caption Nimi

			@property comment type=textbox table=objects field=comment parent=top_2way_left
			@caption Kommentaar

			@property add_clauses type=chooser store=no parent=top_2way_left multiple=1
			@caption Lisatingimused

		@layout top_2way_right type=vbox parent=top_2way

			@property start1 type=datetime_select field=start table=planner parent=top_2way_right
			@caption Algus

			@property end type=datetime_select table=planner parent=top_2way_right
			@caption L&otilde;peb

			@property deadline type=datetime_select table=planner field=deadline parent=top_2way_right
			@caption T&auml;htaeg
	
	@property hrs_table type=table no_caption=1 store=no parent=top_bit


@layout center_bit type=hbox 
	@property center_bit_vis type=hidden store=no no_caption=1 parent=center_bit

	@layout center_bit_left type=vbox parent=center_bit 


		@layout center_bit_left_ct  type=hbox closeable=1 area_caption=Sisu parent=center_bit_left

		@property content type=textarea no_caption=1 cols=80 rows=30 field=description table=planner parent=center_bit_left_ct width=100%
		@caption Sisu
		

	@layout center_bit_right type=vbox parent=center_bit 

		@layout center_bit_right_top type=vbox parent=center_bit_right closeable=1 area_caption=Osapooled no_padding=1

			@property parts_tb type=toolbar no_caption=1 store=no parent=center_bit_right_top

			@property co_table type=table no_caption=1 store=no parent=center_bit_right_top
			@property proj_table type=table no_caption=1 store=no parent=center_bit_right_top
			@property parts_table type=table no_caption=1 store=no parent=center_bit_right_top


			@property customer type=relpicker table=planner field=customer reltype=RELTYPE_CUSTOMER parent=center_bit_right_top
			@caption Klient

			@property project type=relpicker table=planner field=project reltype=RELTYPE_PROJECT parent=center_bit_right_top
			@caption Projekt


		@layout center_bit_right_bottom type=vbox parent=center_bit_right closeable=1 area_caption=Manused no_padding=1

			@property files_tb type=toolbar no_caption=1 store=no parent=center_bit_right_bottom

			@property files_table type=table no_caption=1 store=no parent=center_bit_right_bottom

@property ppa type=hidden store=no no_caption=1


@property is_done type=checkbox field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@layout personal type=hbox
@caption Kestab terve päeva

	@property whole_day type=checkbox ch_value=1 field=meta method=serialize parent=personal no_caption=1

	@property is_personal type=checkbox ch_value=1 field=meta method=serialize parent=personal no_caption=1
	@caption Isiklik

@property send_bill type=checkbox ch_value=1 table=planner field=send_bill 
@caption Saata arve

@property priority type=textbox size=5 table=planner field=priority
@caption Prioriteet

@property num_hrs_guess type=textbox size=5 field=meta method=serialize 
@caption Prognoositav tundide arv 	

@property num_hrs_real type=textbox size=5 field=meta method=serialize 
@caption Tegelik tundide arv

@property num_hrs_to_cust type=textbox size=5 field=meta method=serialize
@caption Tundide arv kliendile

@layout hr_price_layout type=hbox no_caption=1
caption Tunnihind
	
	@property hr_price type=textbox size=5 field=meta method=serialize parent=hr_price_layout
	@caption Tunnihind

	@property hr_price_currency type=select field=meta method=serialize parent=hr_price_layout
	@caption Valuuta

@layout deal_price_layout type=hbox no_caption=1
caption Kokkuleppehind
	
	@property deal_unit type=textbox size=5 field=meta method=serialize parent=hr_price_layout
	@caption &Uuml;hik

	@property deal_amount type=textbox size=5 field=meta method=serialize parent=hr_price_layout
	@caption Koogus

	@property deal_price type=textbox size=5 field=meta method=serialize parent=hr_price_layout
	@caption Kokkuleppehind


@property bill_no type=text table=planner 
@caption Arve number

@property code type=hidden size=5 table=planner field=code
@caption Kood

@property participants type=popup_search multiple=1 table=objects field=meta method=serialize clid=CL_CRM_PERSON
@caption Osalejad

@property controller_disp type=text store=no 
@caption Kontrolleri v&auml;ljund

property aliasmgr type=aliasmgr store=no
caption Seostehaldur

@default field=meta

@property task_toolbar type=toolbar no_caption=1 store=no group=participants method=serialize
@caption "Toolbar"

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly, method=serialize
@caption Kordused
@property calendar_selector type=calendar_selector store=no group=calendars method=serialize
@caption Kalendrid

@property other_selector type=multi_calendar store=no group=others no_caption=1 method=serialize
@caption Teised

@property project_selector type=project_selector store=no group=projects method=serialize
@caption Projektid

@property comment_list type=comments group=comments no_caption=1 method=serialize
@caption Kommentaarid

@property rmd type=reminder group=reminders store=no method=serialize
@caption Meeldetuletus

property participant type=callback callback=cb_participant_selector store=no group=participants no_caption=1 method=serialize
caption Osalejad

@property participant type=participant_selector store=no group=participants no_caption=1 method=serialize
@caption Osalejad

@property search_contact_company type=textbox store=no group=participants method=serialize
@caption Organisatsioon

@property search_contact_firstname type=textbox store=no group=participants method=serialize
@caption Eesnimi

@property search_contact_lastname type=textbox store=no group=participants method=serialize
@caption Perenimi

@property search_contact_code type=textbox store=no group=participants method=serialize
@caption Isikukood

@property search_contact_button type=submit store=no group=participants action=search_contacts method=serialize
@caption Otsi

@property search_contact_results type=table store=no group=participants no_caption=1 method=serialize
@caption Tulemuste tabel

@default group=other_exp
@default group=comments
//ajutiselt teine parent
	@property other_expenses type=table store=no no_caption=1 method=serialize

@default group=rows

	@property rows_tb type=toolbar store=no no_caption=1 method=serialize
	@property rows type=table store=no no_caption=1 method=serialize

@default group=resources

	@property sel_resources type=table no_caption=1 method=serialize

@default group=predicates
	@layout predicates_l type=hbox width=40%:60%
	@layout predicates_left type=vbox parent=predicates_l closeable=1 area_caption=Eeldustegevused no_padding=1

	@property predicates_tb type=toolbar parent=predicates_left no_caption=1
	@property predicates_table type=table store=no  no_caption=1 parent=predicates_left 
	
	@layout predicates_right type=vbox parent=predicates_l
	
	@property predicates multiple=1 type=relpicker multiple=1 reltype=RELTYPE_PREDICATE store=connect field=meta parent=predicates_right method=serialize
	@caption Eeldustegevused

	@property is_goal type=checkbox ch_value=1 table=planner field=aw_is_goal method=
	@caption Verstapost

@groupinfo rows caption=Read 
@groupinfo recurrence caption=Kordumine submit=no
@groupinfo calendars caption=Kalendrid
@groupinfo others caption=Teised submit_method=get
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid parent=other_exp
@groupinfo reminders caption=Meeldetuletused parent=other_exp
@groupinfo participants caption=Osalejad submit=no
@groupinfo other_exp caption="Muud kulud"  
@groupinfo resources caption="Ressursid" parent=other_exp
@groupinfo predicates caption="Eeldused" parent=other_exp

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

@reltype ATTACH value=8 clid=CL_CRM_MEMO,CL_CRM_DEAL,CL_CRM_DOCUMENT,CRM_OFFER
@caption Manus

@reltype PREDICATE value=9 clid=CL_TASK,CL_CRM_CALL
@caption Eeldustegevus

@reltype EXPENSE value=10 clid=CL_CRM_EXPENSE
@caption Muu kulu
*/

class task extends class_base
{
	function task()
	{
		$this->init(array(
			"tpldir" => "groupware/task",
			"clid" => CL_TASK,
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
		if ($this->_proc_stop_act($arr) == 1)
		{
			$post = "<script language='javascript'>window.opener.reload();</script>";
		}

		$s = "";
		$num = 0;
		if (count(safe_array($_SESSION["crm_stoppers"])) < 1)
		{
			if ($post != "")
			{
				header("Location: ".aw_ini_get("baseurl")."/automatweb/closewin.html");
			}
			else
			{
				header("Location: ".aw_ini_get("baseurl")."/automatweb/closewin_no_r.html");
			}
			die();
		}
		$this->vars(array(
			"stop_str" => t("Stopperid"),
			"start_str" => t("Algus"),
			"el_str" => t("kulunud"),
			"p_str" => t("Paus"),
			"s_str" => t("K&auml;ivita"),
			"e_str" => t("L&otilde;peta"),
			"d_str" => t("Kustuta"),
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
				"task_name_esc" => str_replace("\"", "\\\"", trim($stopper["name"])),
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

		return $this->parse().$post;
	}
	
	function callback_get_default_group($arr)
	{
		$seti = get_instance(CL_CRM_SETTINGS);
		$sts = $seti->get_current_settings();
		if ($sts && $sts->prop("view_task_rows_open") && ($_GET["action"] != "new"))
		{
			return "rows";
		}
		return "general";
	}
	
	function callback_on_load($arr)
	{	
		if(($arr["request"]["msgid"]))
		{
			$mail = get_instance(CL_MESSAGE);
			$this->mail_data = $mail->fetch_message(Array(
				"mailbox" => "INBOX" ,
				"msgrid" => $arr["request"]["msgrid"],
				"msgid" => $arr["request"]["msgid"],
				"fullheaders" => "",
			));
		}
		$u = get_instance(CL_USER);
		$this->co = $u->get_current_company();
		$this->person = $u->get_current_person();
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
		if (!is_object($arr["obj_inst"]))
		{
			$arr["obj_inst"] = obj();
		}
		$retval = PROP_OK;
		switch($data["name"])
		{	
			case "predicates":
				return PROP_IGNORE;
				break;
			
			case "predicates_tb":
				$this->_predicates_tb($arr);
				break;
				
			case "predicates_table":
				$this->_predicates_table($arr);
				break;
				
		        case "parts_tb":
                               $this->_parts_tb($arr);
                              break;

			case "co_table":
				$this->_co_table($arr);
				break;

			case "proj_table":
				$this->_proj_table($arr);
				break;

			case "parts_table":
				$this->_parts_table($arr);
				break;

			case "hrs_table":
				$this->_hrs_table($arr);
				break;

			case "files_tb":
				$this->_files_tb($arr);
				break;

			case "files_table":
				$this->_files_table($arr);
				break;

			case "add_clauses":
				$data["options"] = array(
					"status" => t("Aktiivne"),
					"is_done" => t("Tehtud"),
					"whole_day" => t("Terve p&auml;ev"),
					"is_goal" => t("Verstapost"),
					"is_personal" => t("Isiklik"),
					"send_bill" => t("Arvele"),
				);
				$data["value"] = array(
					"status" => $arr["obj_inst"]->prop("status") == STAT_ACTIVE ? 1 : 0,
					"is_done" => $arr["obj_inst"]->prop("is_done") ? 1 : 0,
					"whole_day" => $arr["obj_inst"]->prop("whole_day") ? 1 : 0,
					"is_goal" => $arr["obj_inst"]->prop("is_goal") ? 1 : 0,
					"is_personal" => $arr["obj_inst"]->prop("is_personal") ? 1 : 0,
					"send_bill" => $arr["obj_inst"]->prop("send_bill") ? 1 : 0,
				);
				break;
			case "priority":
			case "bill_no":
			case "deal_price":
			case "deal_unit":
			case "deal_amount":
			case "num_hrs_guess":
			case "num_hrs_real":
			case "num_hrs_to_cust":
			case "is_done":
			case "status":
			case "whole_day":
			case "is_goal":
			case "is_personal":
			case "send_bill":
			case "hr_price_currency":
				return PROP_IGNORE;

			case "controller_disp":
				$cs = get_instance(CL_CRM_SETTINGS);
				$pc = $cs->get_task_controller($cs->get_current_settings());
				if ($this->can("view", $pc))
				{
					$pco = obj($pc);
					$pci = $pco->instance();
					$prop["value"] = $pci->eval_controller($pc, $arr["obj_inst"]);
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
			
			case "content":
				if($this->mail_data)
				{
					$data["value"] = sprintf(
					"From: %s\nTo: %s\nSubject: %s\nDate: %s\n\n%s",
						$this->mail_data["from"],
						$this->mail_data["to"],
						$this->mail_data["subject"],
						$this->mail_data["date"],
						$this->mail_data["content"]);
					break;
				}
				break;
				
			case "end":
				$p = get_instance(CL_PLANNER);
				$cal = $p->get_calendar_for_user();
				if ($cal)
				{
					$calo = obj($cal);
					if ($data["name"] == "end" && (!is_object($arr["obj_inst"]) || !is_oid($arr["obj_inst"]->id())))
					{
						$data["value"] = time() + $calo->prop("event_def_len")*60;
					}
				}
				else
				if ($arr["new"])
				{
					$data["value"] = time() + 900;
				}

			case "start1":
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
				if($this->mail_data)
				{
					$data["value"] = $this->mail_data["subject"];
				}
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
						"name" => $data["value"]
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
				return PROP_IGNORE;
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
								// immediately store this thingie as well so that the user will not have to save the object
								if ($arr["obj_inst"]->prop("hr_price") != $data["value"])
								{
									$arr["obj_inst"]->set_prop("hr_price", $data["value"]);
									$arr["obj_inst"]->save();
								}
								return PROP_IGNORE;
							}
						}
					}

				}
				return PROP_IGNORE;

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
				return PROP_IGNORE;
				if ($this->can("view",$arr["request"]["alias_to_org"]))
				{
					$ol = new object_list(array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_ORDERER" => $arr["request"]["alias_to_org"],
						"lang_id" => array(),
						"site_id" => array()
					));
				}
				else
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
				return PROP_IGNORE;
				$i = get_instance(CL_CRM_COMPANY);
				$cst = $i->get_my_customers();
// 				if($this->$co)
// 				{
// 					$data["value"] = $this->$co;
// 				}
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

				if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
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
				$data["onchange"] = "upd_proj_list()";
				break;

			case "other_expenses":
				$this->_other_expenses($arr);
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
			case "predicates":
				return PROP_IGNORE;
			case "add_clauses":
				$arr["obj_inst"]->set_status($prop["value"]["status"] ? STAT_ACTIVE : STAT_NOTACTIVE);
				$arr["obj_inst"]->set_prop("is_done", $prop["value"]["is_done"] ? 8 : 0);
				$arr["obj_inst"]->set_prop("whole_day", $prop["value"]["whole_day"] ? 1 : 0);
				$arr["obj_inst"]->set_prop("is_goal", $prop["value"]["is_goal"] ? 1 : 0);
				$arr["obj_inst"]->set_prop("is_personal", $prop["value"]["is_personal"] ? 1 : 0);
				$arr["obj_inst"]->set_prop("send_bill", $prop["value"]["send_bill"] ? 1 : 0);
				break;

			case "is_done":
			case "status":
			case "whole_day":
			case "is_goal":
			case "is_personal":
			case "send_bill":
				return PROP_IGNORE;

			case "sel_resources":
				$this->_set_resources($arr);
				break;

			case "rows":
				$this->_save_rows($arr);
				break;

			case "participants":
				return PROP_IGNORE;
				if (!is_oid($arr["obj_inst"]->id()))
				{
					$this->post_save_add_parts = safe_array($prop["value"]);
					return PROP_IGNORE;
				}
				$prop["value"] = $_POST["participants"];
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
				return PROP_IGNORE;
				if (isset($_POST["customer"]))
				{
					$prop["value"] = $_POST["customer"];
				}
				break;

			case "project":
				return PROP_IGNORE;
				if (isset($_POST["project"]))
				{
					$prop["value"] = $_POST["project"];
				}
				// add to proj
				if (is_oid($prop["value"]) && $this->can("view", $prop["value"]))
				{
					$this->add_to_proj = $prop["value"];
				}
				break;

			case "other_expenses":
				foreach(safe_array($_POST["exp"]) as $key => $entry)
				{
					if(is_oid($key) && $this->can("view" ,$key)){
						$obj = obj($key);
						if($obj->class_id() == CL_CRM_EXPENSE)
						{
							if($entry["name"] == "" && $entry["cost"] == "")
							{
								$cs = $arr["obj_inst"]->connections_from(array("to" => $key));
								$c = reset($cs);
								$o = $c->to();
								$o->delete();
							}
							else
							{
								$obj->set_name($entry["name"]);
								$obj->set_prop("date" , $entry["date"]);
								$obj->set_prop("cost" , $entry["cost"]);
								$obj->save();
							}
							continue;
						}
					}
					//edasi juhul kui sellist kulude objekti veel pole 
					if ($entry["name"] != "" && $entry["cost"] != "")
					{
						$row = obj();
						$row->set_parent($arr["obj_inst"]->id());
						$row->set_class_id(CL_CRM_EXPENSE);
						$row->set_name($entry["name"]);
						$row->set_prop("date", $entry["date"]);
						$row->set_prop("cost", $entry["cost"]);
						$row->save();
						$arr["obj_inst"]->connect(array(
							"to" => $row->id(),
							"type" => "RELTYPE_EXPENSE"
						));
					}
				}
				break;
				
			case "hrs_table":
				$different_customers = 0;
				if(is_oid($arr["obj_inst"]->prop("project")) && $arr["obj_inst"]->prop("customer"))
				{
					$project = obj($arr["obj_inst"]->prop("project"));
					$different_customers = 1;
					$orderers = $project->prop("orderer");
					if(!is_array($orderers)) $orderers = array($orderers);
					foreach($orderers as $orderer)
					{
						if($orderer == $arr["obj_inst"]->prop("customer")) $different_customers = 0;
					}
				}
				//if($different_customers) $prop["error"]arr("asdasd");
				$url = $this->mk_my_orb("error_popup", array(
					"text" => t("<br><br><br>Valitud Projekti ja Toimetuse kliendid erinevad"),
				));
				if($different_customers)
				{
					$prop["error"] = "<script name= javascript>window.open('".$url."','', 'toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=150, width=500')</script>";
					return PROP_ERROR;
				}
				if (!(strlen($arr["request"]["hr_price"])> 0))
				{
					$prop["error"] = t("Tunnihind sisestamata!");
					return PROP_ERROR;
				}
				break;

			case "predicates":
				return PROP_IGNORE;
		};
		return $retval;
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

		if ($arr["request"]["set_resource"] != "")
		{
			$arr["obj_inst"]->connect(array(
				"to" => $arr["request"]["set_resource"],
				"type" => "RELTYPE_RESOURCE"
			));
		}

		if ($arr["request"]["set_pred"] != "")
		{
			$pv = $arr["obj_inst"]->prop("predicates");
			if (!is_array($pv) && is_oid($pv))
			{
				$pv = array($pv => $pv);
			}	
			else
			if (!is_array($pv) && !is_oid($pv))
			{
				$pv = array();
			}
			$pv[$arr["request"]["set_pred"]] = $arr["request"]["set_pred"];
			$arr["obj_inst"]->set_prop("predicates", $arr["request"]["set_pred"]);
		}
		if ($arr["request"]["group"] == "general" && !$arr["request"]["add_clauses"]["status"])
		{
			$arr["obj_inst"]->set_status(STAT_NOTACTIVE);
		}
		if ($arr["request"]["group"] == "general" && $arr["request"]["add_clauses"]["status"])
		{
			$arr["obj_inst"]->set_status(STAT_ACTIVE);
		}
	}

	function callback_post_save($arr)
	{
		if (is_oid($arr["request"]["predicates"]) && $this->can("view", $arr["request"]["predicates"]))
		{
			$arr["obj_inst"]->connect(array(
				"to" => $arr["request"]["predicates"],
				"reltype" => "RELTYPE_PREDICATE"
			));
		}
		if ($this->can("view", $_POST["predicates"]))
		{
			$arr["obj_inst"]->connect(array(
				"to" => $_POST["predicates"],
				"type" => "RELTYPE_PREDICATE"
			));
		}
		if ($_POST["predicates"] > 0)
		{
			foreach(explode(",", $_POST["predicates"]) as $pred)
			{
				$arr["obj_inst"]->connect(array(
					"to" => $pred,
					"type" => "RELTYPE_PREDICATE"
				));
			}
		}
		if ($arr["request"]["participants_h"] > 0)
		{
			$this->post_save_add_parts = explode(",", $arr["request"]["participants_h"]);
		}
		if ($_POST["participants_h"] > 0)
		{
			$this->post_save_add_parts = explode(",", $_POST["participants_h"]);
		}

		if ($this->can("view", $_POST["orderer_h"]))
		{
			$arr["obj_inst"]->connect(array(
				"to" => $_POST["orderer_h"],
				"type" => "RELTYPE_CUSTOMER"
			));
		}
		if ($_POST["project_h"] > 0)
		{
			foreach(explode(",", $_POST["project_h"]) as $proj)
			{
				$arr["obj_inst"]->connect(array(
					"to" => $proj,
					"type" => "RELTYPE_PROJECT"
				));
				$arr["obj_inst"]->create_brother($proj);
			}
		}
		if ($_POST["files_h"] > 0)
		{
			foreach(explode(",", $_POST["files_h"]) as $proj)
			{
				$arr["obj_inst"]->connect(array(
					"to" => $proj,
					"type" => "RELTYPE_FILE"
				));
			}
		}
		
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
			$this->add_participant($arr["obj_inst"], get_current_person());
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
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "cost",
			"caption" => t("Hind")
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev")
		));
//		$t->define_field(array(
//			"name" => "on_bill",
//			"caption" => t("Arvele")
//		));
		$t->define_field(array(
			"name" => "bill",
			"caption" => t("Arve nr.")
		));	
	}

	function _other_expenses($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_other_exp_t($t);
		
		$dat = safe_array($arr["obj_inst"]->meta("other_expenses"));
// 		$dat = array();
		$dat[] = array();
		$dat[] = array();
		$dat[] = array();
		$nr = 1;
		
		$cs = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_EXPENSE",
		));
		foreach ($cs as $key => $ro)
		{
			$ob = $ro->to();
			if($ob->class_id() == CL_CRM_EXPENSE)
			{
					$bno = "";
					if ($this->can("view", $ob->prop("bill_id")))
					{
						$bo = obj($ob->prop("bill_id"));
						$bno = $bo->prop("bill_no");
					}
					$onbill = "";
					if ($ob->prop("bill_id"))
					{
						$onbill = sprintf(t("Arve nr %s"), $bno);
					}
				$t->define_data(array(
					"name" => html::textbox(array(
						"name" => "exp[".$ob->id()."][name]",
						"value" => $ob->name(),
					)),
					"cost" => html::textbox(array(
						"name" => "exp[".$ob->id()."][cost]",
						"size" => 5,
						"value" => $ob->prop("cost"),
					)),
					"date" => html::date_select(array(
						"name" => "exp[".$ob->id()."][date]",
						"value" => $ob->prop("date"),
					)),
					"on_bill" => html::checkbox(array(
						"name" => "exp[".$ob->id()."][on_bill]",
						"value" => 1,
						"checked" => $checked,
					)),
					"bill" => $onbill
					,
				));
	//			$nr++;
			}
		}
		foreach($dat as $exp)
		{
			$t->define_data(array(
				"name" => html::textbox(array(
					"name" => "exp[$nr][name]",
					"value" => $exp["exp"]
				)),
				"cost" => html::textbox(array(
					"name" => "exp[$nr][cost]",
					"size" => 5,
					"value" => $exp["cost"]
				)),
				"date" => html::date_select(array(
					"name" => "exp[$nr][date]",
				)),
			));
			$nr++;
		}
		$t->set_sortable(false);
	}

	

	/**
		@attrib name=error_popup
		@param text optional
	**/
	function error_popup($arr)
	{
		return $arr["text"];
	}

	/**
		@attrib name=search_for_proj
		@param retf optional
	**/
	function search_for_proj($arr)
	{
		
	}



	function _init_rows_t(&$t, $impl_filt = NULL)
	{
		$selected = "";
		$seti = get_instance(CL_CRM_SETTINGS);
			$sts = $seti->get_current_settings();
			if ($sts && $sts->prop("default_task_rows_bills_filter"))
			{
				$settings_inst = get_instance("applications/crm/crm_settings");
				$selected = $settings_inst->bills_filter_options[$sts->prop("default_task_rows_bills_filter")];
			}
		
		$t->define_field(array(
			"name" => "ord",
			"caption" => t("Jrk"),
			"align" => "center",
			"callback" =>  array(&$this, "__ord_format"),
			"callb_pass_row" => true,
			"numeric" => 1,
		));

	/*	$t->define_field(array(
			"name" => "id",
//			"caption" => t("Jrk"),
//			"align" => "center",
		"callb_pass_row" => true,
		"numeric" => 1,
		));
*/
		$t->define_field(array(
			"name" => "task",
			"caption" => t("Tegevus"),
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"sortable" => 1,
			"chgbgcolor" => "col",
			"callback" =>  array(&$this, "__date_format"),
			"callb_pass_row" => true,
		));

		$t->define_field(array(
			"name" => "impl",
			"caption" => t("Teostaja"),
			"align" => "center",
			"filter" => $impl_filt,
			"filter_compare" => array(&$this, "__impl_filt_comp")
		));

		$t->define_field(array(
			"name" => "time",
			"caption" => t("Tunde"),
			"align" => "left",
			"nowrap" => 1,
		));

		/*$t->define_field(array(
			"name" => "time_real",
			"caption" => t("Kulunud tunde"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "time_to_cust",
			"caption" => t("Tunde kliendile"),
			"align" => "center"
		));*/

		$t->define_field(array(
			"name" => "done",
			"caption" => t("<a href='javascript:void(0)' onClick='aw_sel_chb(document.changeform,\"done\")'>Tehtud</a>"),
			"align" => "center",
			"filter" => array(
				t("Jah"),
				t("Ei")
			),
			"filter_compare" => array(&$this, "__done_filt_comp"),
		));

		

		$t->define_field(array(
			"name" => "on_bill",
			"caption" => t("<a href='javascript:void(0)' onClick='aw_sel_chb(document.changeform,\"on_bill\")'>Arvele</a>"),
			"align" => "center",
			"filter_options" => array("selected" => $selected),
			"filter" => array(
				t("Jah"),
				t("Ei"),
				t("Arvel"),
				t("Arveta")
			),
			"filter_compare" => array(&$this, "__bill_filt_comp")
		));

		$t->define_field(array(
			"name" => "comments",
			"caption" => html::img(array("url" => aw_ini_get("baseurl")."/automatweb/images/forum_add_new.gif", "border" => 0)),
			"align" => "center",
			"filter" => array(
				t("Jah"),
				t("Ei")
			),
			"filter_compare" => array(&$this, "__com_filt_comp")
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function __com_filt_comp($key, $str, $row)
	{
		if (!is_oid($row["oid"]))
		{
			return true;
		}
		if ($str == t("Jah") && !$row["comments_cnt"])
		{
			return false;
		}
		if ($str == t("Ei") && $row["comments_cnt"])
		{
			return false;
		}
		return true;
	}

	function __done_filt_comp($key, $str, $row)
	{
		if (!is_oid($row["oid"]))
		{
			return true;
		}
		if ($str == t("Jah") && !$row["done_val"])
		{
			return false;
		}
		if ($str == t("Ei") && $row["done_val"])
		{
			return false;
		}
		return true;
	}

	function __bill_filt_comp($key, $str, $row)
	{
		if (!is_oid($row["oid"]))
		{
			return true;
		}
		if ($str == t("Arveta") && $row["bill_val"] != "billed")
		{
			return true;
		}
		else
		if ($str == t("Arveta"))
		{
			return false;
		}
		
		if ($str == t("Arvel") && $row["bill_val"] != "billed")
		{
			return false;
		}
		if ($str == t("Jah") && ($row["bill_val"] == 0 || $row["bill_val"] == "billed"))
		{
			return false;
		}
		if ($str == t("Ei") && ($row["bill_val"] == 1 || $row["bill_val"] == "billed"))
		{
			return false;
		}
		return true;
	}

	function __impl_filt_comp($key, $str, $row)
	{
		if (!is_oid($row["oid"]))
		{
			return true;
		}
		return in_array($str, $row["impl_val"]);
	}

	function _rows($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$impls = $this->_get_possible_participants($arr["obj_inst"], true, $arr["obj_inst"]->prop("participants"));
		$this->_init_rows_t($t, array_values($impls));

		$u = get_instance(CL_USER);
		$def_impl = $u->get_current_person();
		$o_def_impl = array($def_impl => $def_impl);
//		if($arr["obj_inst"]->id() > 0)	$cs = $arr["obj_inst"]->connections_from(array(
//				"type" => "RELTYPE_ROW",
//			));
//		else $cs = array();	
		foreach ($cs as $key => $ro)
		{
			$ob = $ro->to();
			if($ob->class_id() == CL_TASK_ROW)
			{
				if($ob->prop("done") || !strlen($ob->prop("date")))
				{
					$data_done[] = $ro;
				}
				else
				{
					$data[] = $ro;
				}
			}
			else
			{
				if($ob->prop("is_done") || !strlen($ob->prop("start1")))
				{
					$data_done[] = $ro;
				}
				else
				{
					$data[] = $ro;
				}
			}
		}
		ksort($data);
		ksort($data_done);
		$cs = array_merge($data, $data_done);

		$null_idx = 0;
		$comm = get_instance(CL_COMMENT);
		$ank_idx = 1;

		$rows_object_list = new object_list();
		foreach($cs as $ro)
		{
			$row = $ro->to();
			$rows_object_list->add($row);
		}
		//$rows_object_list->sort_by(array("prop" => "ord","order" => "desc"));
		$row_ids = $rows_object_list->ids();
		$row_ids[] = NULL;
		$row_ids[] = NULL;
		$row_ids[] = NULL;
		$not_sorted=true;
		foreach($row_ids as $ro)
		{
			if ($ro === NULL)
			{
				$idx = $null_idx--;
				$row = obj();
				$def_impl = $o_def_impl;
				if($not_sorted)	$t->sort_by(array(
					"field" => array("ord", "date" , "id"),
					"order" => array("asc", "asc" , "asc"),
				));
				$t->set_sortable(false);
				$not_sorted = false;
			}
			else
			{
				$idx = $ro;
				$row = obj($ro);
				$def_impl = array();
			}
			$ank_idx++;
			$date_sel = "<A HREF='#'  onClick=\"var cal=new CalendarPopup();cal.select(aw_get_el('rows[$idx][date]'),'anchor".$ank_idx."','dd/MM/yy'); return false;\"
						   NAME='anchor".$idx."' ID='anchor".$ank_idx."'>".t("vali")."</A>";

			$comments = "";
			$comments_cnt = 0;
			if (is_oid($idx))
			{
				$comments_cnt = $comm->get_comment_count(array("parent" => $idx));
				$comments = html::popup(array(
					"width" => 800,
					"height" => 500,
					"scrollbars" => 1,
					"url" => html::get_change_url($idx, array("group" => "comments")),
					"caption" => sprintf(t("%s (%s)"), html::img(array("url" => aw_ini_get("baseurl")."/automatweb/images/forum_add_new.gif", "border" => 0)), $comments_cnt)
				));
			}

			$is = (is_array($row->prop("impl")) && count($row->prop("impl"))) ? $row->prop("impl") : $def_impl;
			$is_str = array();
			foreach(safe_array($is) as $is_id)
			{
				$iso = obj($is_id);
				if (!isset($impls[$is_id]))
				{
					$impls[$is_id] = $iso->name();
				}
				$is_str[] = $iso->name();
			}
			$bno = "";
			if ($this->can("view", $row->prop("bill_id")))
			{
				$bo = obj($row->prop("bill_id"));
				$bno = $bo->prop("bill_no");
			}
			$pref = "";
			if ($row->class_id() == CL_CRM_MEETING)
			{
				$date = date("d/m/y",($row->prop("start1") > 100 ? $row->prop("start1") : time()));
				$d_comp = date("Ymd",$row->prop("start1") > 100 ? $row->prop("start1") : time());
				$i = $row->instance();
				$i->get_property($argb);
				//$impls = $pr["options"];
				$is = $pr["value"];
				$pref = html::obj_change_url($row->id())." <br>".date("d.m.Y H:i", $row->prop("start1"))." - ".date("d.m.Y H:i", $row->prop("end"))."<br>";
			}
			else
			{
				$date = date("d/m/y",($row->prop("date") > 100 ? $row->prop("date") : time()));
				$d_comp =  date("Ymd",($row->prop("date") > 100 ? $row->prop("date") : time()));
				$app = "";
			}
			
			if($d_comp < date("Ymd",time()))
			{
				$col = "red";
			}
			elseif($d_comp == date("Ymd", time()))
			{
				$col = "yellow";
			}
			else
			{
				$col = "white";
			}
			if($ro === null || ( $row->class_id() == CL_TASK_ROW && $row->prop("done") || ($row->class_id() == CL_CRM_MEETING && $row->prop("is_done"))))
			{
				$col = "white";
			}

			$pr = array("name" => "participants");
			$argb = array(
				"obj_inst" => $row,
				"request" => $arr["request"],
				"prop" => &$pr
			);

			$stopper = "";
			if ($idx > 0)
			{
				$url = $this->mk_my_orb("stopper_pop", array(
					"id" => $idx,
					"s_action" => "start",
					"type" => t("Toimetus"),
					"name" => $data["value"]
				));
				$stopper = " <a href='#' onClick='aw_popup_scroll(\"$url\",\"aw_timers\",320,400)'>".t("Stopper")."</a>";
			}
			else
			{
				$url = $this->mk_my_orb("stopper_pop", array(
					"id" => $arr["obj_inst"]->id(),
					"s_action" => "start",
					"type" => t("Toimetus"),
					"name" => $arr["obj_inst"]->name()
				));
				$stopper = " <a href='#' onClick='aw_popup_scroll(\"$url\",\"aw_timers\",320,400)'>".t("Stopper")."</a>";
			}

			$onbill = "";
			$bv = "";
			if ($row->prop("bill_id"))
			{
				$onbill = sprintf(t("Arve nr %s"), $bno);
				$bv = "billed";
			}
			else
			if ($row->prop("bill_no"))
			{
				$onbill = sprintf(t("Arve nr %s"), $row->prop("bill_no"));
				$bv = "billed";
			}
			else
			{
				$onbill = html::checkbox(array(
					"name" => "rows[$idx][on_bill]",
					"value" => 1,
					"checked" => ($row->class_id() == CL_CRM_MEETING ? $row->prop("send_bill") : $row->prop("on_bill"))
				));
				$bv = ($row->class_id() == CL_CRM_MEETING ? $row->prop("send_bill") : $row->prop("on_bill"));
			}
			$t->define_data(array(
				"idx" => $idx,
				"ord_val" => $row->prop("ord"),
				"date_val" => $date,
				"date_sel" => $date_sel,
				"ord" => $row->prop("ord"),
				"id" => $row->id(),
				"task" => $pref."<a name='row_".$idx."'></a>".html::textarea(array(
					"name" => "rows[$idx][task]",
					"value" => $row->prop("content"),
					"rows" => 5,
					"cols" => 45
				)).$app,
				"date" => $row->prop("date"),
				"impl" => html::select(array(
					"name" => "rows[$idx][impl]",
					"options" => $impls,
					"value" => $is,
					"multiple" => 1
				)),
				"impl_val" => $is_str,
				"time" => html::textbox(array(
					"name" => "rows[$idx][time_guess]",
					"value" => $row->prop("time_guess"),
					"size" => 3
				))." - Prognoos<br>".
				html::textbox(array(
					"name" => "rows[$idx][time_real]",
					"value" => $row->prop("time_real"),
					"size" => 3
				))." - Kulunud<br>".
				html::textbox(array(
					"name" => "rows[$idx][time_to_cust]",
					"value" => $row->prop("time_to_cust"),
					"size" => 3
				))." - Kliendile<br>".$stopper,
				"done" => html::checkbox(array(
					"name" => "rows[$idx][done]",
					"value" => 1,
					"checked" => $row->class_id() == CL_CRM_MEETING ? $row->prop("is_done") : $row->prop("done")
				)),
				"done_val" => $row->class_id() == CL_CRM_MEETING ? $row->prop("is_done") : $row->prop("done"),
				"on_bill" => $onbill,
				"bill_val" => $bv,
				"comments" => $comments,
				"comments_cnt" => $comments_cnt,
				"oid" => $row->id(),
				"col" => $col
			));
		}
	}

	function __ord_format($val)
	{
		return html::textbox(array(
					"name" => "rows[".$val["idx"]."][ord]",
					"value" => $val["ord_val"],
					"size" => 3,
		));
	}
	function __date_format($val)
	{
		return html::textbox(array(
				"name" => "rows[".$val["idx"]."][date]",
				"value" => $val["date_val"],
				"size" => 7
			)).$val["date_sel"];
	}
	function __id_format($val)
	{
		return " ";
	}


	function get_task_bill_rows($task, $only_on_bill = true, $bill_id = null)
	{
		// check if task has rows defined that go on bill
		// if, then ret those
		// if not, return data for bill

		$rows = array();
		//$dat = safe_array($task->meta("rows"));
		if ($task->brother_of() != $task->id())
		{
			$task = obj($task->brother_of());
		}
		foreach($task->connections_from(array("type" => "RELTYPE_ROW")) as $c)
		{
			$row = $c->to();
			$idx = $row->id();
			if (($row->prop("send_bill") || $row->prop("on_bill") == 1 || !$only_on_bill) && ($bill_id === null || $row->prop("bill_id") == $bill_id || $row->prop("bill_no") == $bill_id))
			{
				$id = $task->id()."_".$idx;
				$rows[$id] = array(
					"name" => $row->prop("content"),
					"unit" => t("tund"),
					"date" => $row->class_id() == CL_CRM_MEETING ? $row->prop("start1") : $row->prop("date"),
					"price" => $task->prop("hr_price"),
					"amt" => $row->prop("time_to_cust"),
					"amt_real" => $row->prop("time_real"),
					"amt_guess" => $row->prop("time_guess"),
					"sum" => str_replace(",", ".", $row->prop("time_to_cust")) * $task->prop("hr_price"),
					"has_tax" => 1,
					"on_bill" => 1,
					"bill_id" => $row->prop("bill_id") ? $row->prop("bill_id") : $row->prop("bill_no"),
					"impl" => $row->prop("impl"),
					"row_oid" => $row->id(),
					"is_done" => $row->class_id() == CL_CRM_MEETING ? $row->prop("is_done") : $row->prop("done")
				);
			}
		}
		if (!count($rows) )
		{
			// add the main task to the first bill only
			$add = true;
			if ($bill_id !== null)
			{
				$conns = $task->connections_from(array("type" => "RELTYPE_BILL", "order_by" => "to.id"));
				$bc = reset($conns);
				if ($bc && $bill_id != $bc->prop("to"))
				{
					if ($bill_id != $bc->prop("to"))
					{
						$add = false;
					}
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
					"amt_real" => $task->prop("num_hrs_real"),
					"amt_guess" => $task->prop("num_hrs_guess"),
					"sum" => str_replace(",", ".", $task->prop("num_hrs_to_cust")) * $task->prop("hr_price"),
					"has_tax" => 1,
					"on_bill" => 1,
					"impl" => $task->prop("participants")
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
				"amt_real" => 1,
				"amt_guess" => 1,
				"sum" => $oe["cost"],
				"has_tax" => 1,
				"is_oe" => true,
				"on_bill" => 1
			);
		}

		foreach ($task->connections_from(array("type" => "RELTYPE_EXPENSE")) as $key => $ro)
		{
			$ob = $ro->to();
			if($ob->class_id() == CL_CRM_EXPENSE)
			{
				$id = $task->id()."_oe_".$ob->id();
				$rows[$id] = array(
					"name" => $ob->name(),
					"unit" => "",
					"price" => $ob->prop("cost"),
					"amt" => 1,
					"amt_real" => 1,
					"amt_guess" => 1,
					"sum" => $ob->prop("cost"),
					"has_tax" => 1,
					"is_oe" => true,
					"on_bill" => 1
				);
			}
		}
		return $rows;
	}

	/**
		@attrib name=del_file_rel
		@param fid required 
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

		$tb->add_button(array(
			"name" => "new_meeting",
			"img" => "new.gif",
			"tooltip" => t("Kohtumine"),
			"url" => $this->mk_my_orb(
				"new", 
				array(
					"parent" => $arr["obj_inst"]->parent(),
					"return_url" => get_ru(),
					"alias_to" => $arr["obj_inst"]->id(),
					"reltype" => 7,
					"alias_to_org" => $arr["obj_inst"]->prop("customer"),
					"set_proj" => $arr["obj_inst"]->prop("project")
				),
				CL_CRM_MEETING
			)
		));

		$b = array(
			'name' => 'create_bill',
			'img' => 'create_bill.jpg',
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

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta read"),
			"action" => "delete_task_rows"
		));
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

		if(is_object($o) && is_oid($o->id()))
		{
			$conns = $o->connections_to(array(
				'type' => array(10, 8),//CRM_PERSON.RELTYPE_PERSON_TASK==10
			));
			foreach($conns as $conn)
			{
				$obj = $conn->from();
				$opts[$obj->id()] = $obj->name();
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
			$i = $o->instance();
			$rv = $i->handle_stopper_stop($o, array(
				"desc" => $arr["desc"],
				"start" => $stopper["start"],
				"hours" => $el_hr
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
		return $rv;
	}

	/**
		@attrib name=search_contacts
	**/
	function search_contacts($arr)
	{
		return $this->mk_my_orb('change',array(
				'id' => $arr['id'],
				'group' => $arr['group'],
				'search_contact_company' => ($arr['search_contact_company']),
				'search_contact_firstname' => ($arr['search_contact_firstname']),
				'search_contact_lastname' => ($arr['search_contact_lastname']),
				'search_contact_code' => ($arr['search_contact_code']),
				"return_url" => $arr["return_url"]
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
		$max_row = 0;
		$max_ord = 0;
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
				$o->save();
				$is_mod = true;
			}
			else
			{
				$cs = $task->connections_from(array("to" => $_oid));
				$c = reset($cs);
				$o = $c->to();
				/*if ($e["task"] == "")
				{
					$o->delete();
					continue;
				}*/
				$is_mod = false;
			}

			list($d,$m,$y) = explode("/", $e["date"]);
			$_tm = mktime(0,0,0, $m, $d, $y);
			if ($o->class_id() == CL_CRM_MEETING)
			{
				if (date("d.m.Y", $o->prop("start1")) != date("d.m.Y", $_tm))
				{
					if ($o->prop("end") < $_tm)
					{
						$len = $o->prop("end") - $o->prop("start1");
						$o->set_prop("end", $_tm + $len);
					}

					$o->set_prop("start1", $_tm);
					$is_mod = true;
				}
			}
			else
			{
				if ($o->prop("date") != $_tm)
				{
					$o->set_prop("date", $_tm);
					$is_mod = true;
				}
			}
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

			if ($o->prop("content") != $e["task"])
			{
				$o->set_prop("content", $e["task"]);
				$is_mod = true;
			}
			
			if ($o->class_id() == CL_CRM_MEETING)
			{
				$mti = $o->instance();
				$pr = array(
					"name" => "participants",
					"value" => $this->make_keys($e["impl"]),
				);
				$_POST["participants"] = $this->make_keys($e["impl"]);
				$mti->set_property(array(
					"obj_inst" => $o,
					"request" => $arr["request"],
					"prop" => $pr
				));
				$is_mod = true;
			}
			else
			{
				if ($o->prop("impl") != $this->make_keys($e["impl"]))
				{
					$o->set_prop("impl", $e["impl"]);
					$is_mod = true;
				}
			}

			$e["time_guess"] = str_replace(",", ".", $e["time_guess"]);
			if ($o->prop("time_guess") != $e["time_guess"])
			{
				$o->set_prop("time_guess", $e["time_guess"]);
				$is_mod = true;
			}

			//järjekorra seadmine
			if($e["ord"] > $max_ord) $max_ord = $e["ord"];
			if($e["ord"] == null)
			{
				$e["ord"] = 10+$max_ord;
				$max_ord = $e["ord"];
			}
			if ($o->prop("ord") != $e["ord"])
			{
				$o->set_prop("ord", $e["ord"]);
				$is_mod = true;
			}

			$e["time_real"] = str_replace(",", ".", $e["time_real"]);
			if ($o->prop("time_real") != $e["time_real"])
			{
				$o->set_prop("time_real", $e["time_real"]);
				$is_mod = true;
			}

			$e["time_to_cust"] = str_replace(",", ".", $e["time_to_cust"]);
			if ($o->prop("time_to_cust") != $e["time_to_cust"])
			{
				$o->set_prop("time_to_cust", $e["time_to_cust"]);
				$is_mod = true;
			}

			if ($o->class_id() == CL_CRM_MEETING)
			{
				$o->set_prop("is_done", $e["done"] ? 8 : 0);
				$is_mod = true;
			}	
			else
			{
				if ((int)$o->prop("done") != (int)$e["done"])
				{
					$o->set_prop("done", (int)$e["done"]);
					$is_mod = true;
				}
			}

			if ($o->class_id() != CL_CRM_MEETING)
			{
				if ((int)$o->prop("on_bill") != (int)$e["on_bill"])
				{
					$o->set_prop("on_bill", (int)$e["on_bill"]);
					if ($o->is_property("to_bill_date"))
					{
						$o->set_prop("to_bill_date", time());
					}
					$is_mod = true;
				}
			}
			else
			{
				$o->set_meta("on_bill", (int)$e["on_bill"]);
				$o->set_prop("send_bill", (int)$e["on_bill"]);
			}

			if ($is_mod)
			{
				$o->save();
			}

			$task->connect(array(
				"to" => $o->id(),
				"type" => "RELTYPE_ROW"
			));
		}
	}

	/**

      @attrib name=submit_delete_participants_from_calendar
      @param id required type=int acl=view

	**/
	function submit_delete_participants_from_calendar($arr)
	{
		post_message_with_param(
			MSG_MEETING_DELETE_PARTICIPANTS,
			CL_CRM_MEETING,
			&$arr
		);
		return $arr['post_ru'];
	}

	function callback_mod_reforb($arr)
	{
		$arr["predicates"] = 0;
		$arr["post_ru"] = post_ru();
		$arr["participants_h"] = 0;
		$arr["orderer_h"] = 0;
		$arr["project_h"] = 0;
		$arr["files_h"] = 0;
		if ($_GET["action"] == "new")
		{
			$arr["add_to_cal"] = $_GET["add_to_cal"];
			$arr["alias_to_org"] = $_GET["alias_to_org"];
			$arr["reltype_org"] = $_GET["reltype_org"];
			$arr["set_pred"] = $_GET["set_pred"];
			$arr["set_resource"] = $_GET["set_resource"];
		}
	}

	function _req_get_s_folders($fld, $fldo, &$folders, $parent)
	{
		$this->_lv++;
		foreach($fld as $dat)
		{
			if ($dat["parent"] === $parent)
			{
				$folders[$fldo->id().":".$dat["id"]] = str_repeat("&nbsp;&nbsp;&nbsp;", $this->_lv).iconv("utf-8", aw_global_get("charset")."//IGNORE", $dat["name"]);
				$this->_req_get_s_folders($fld, $fldo, $folders, $dat["id"]);
			}
		}
		$this->_lv--;
	}

	/**
		@attrib name=get_proj_for_cust
		@param cust optional
	**/
	function get_proj_for_cust($arr)
	{
		if (!$arr["cust"])
		{
			$i = get_instance(CL_CRM_COMPANY);
			$prj = $i->get_my_projects();
			if (!count($prj))
			{
				$ol = new object_list();
			}
			else
			{
				$ol = new object_list(array("oid" => $prj, "lang_id" => array(), "site_id" => array()));
			}
		}
		else
		{
			$filt = array(
				"class_id" => CL_PROJECT,
				"CL_PROJECT.RELTYPE_PARTICIPANT" => $arr["cust"],
			);
			$ol = new object_list($filt);
		}
		header("Content-type: text/xml");
		$xml = "<?xml version=\"1.0\" encoding=\"".aw_global_get("charset")."\" standalone=\"yes\"?>\n<response>\n";
	
		foreach($ol->names() as $id => $n)
		{
			$xml .= "<item><value>$id</value><text>$n</text></item>";
		}
		$xml .= "</response>";
		die($xml);
	}
	
	function callback_generate_scripts($arr)
	{
		$url = $this->mk_my_orb("get_proj_for_cust");
		return '
			function upd_proj_list()
			{
				set_changed();
				aw_do_xmlhttprequest("'.$url.'&cust="+document.changeform.customer.options[document.changeform.customer.selectedIndex].value, proj_fetch_callb);
			}

			function proj_fetch_callb()
			{
				if (req.readyState == 4)
				{
					// only if "OK"
					if (req.status == 200) 
					{
						response = req.responseXML.documentElement;
						items = response.getElementsByTagName("item");
						aw_clear_list(document.changeform.project);
						aw_add_list_el(document.changeform.project, "", "'.t("--vali--").'");

						for(i = 0; i < items.length; i++)
						{
							value = items[i].childNodes[0].firstChild.data;
							text = items[i].childNodes[1].firstChild.data;
							aw_add_list_el(document.changeform.project, value, text);
						}
					} 
					else 
					{
						alert("There was a problem retrieving the XML data:\n" + req.statusText);
					}
				}
			}
		';
	}

	/**
		@attrib name=delete_task_rows
	**/
	function delete_task_rows($arr)
	{
		foreach(safe_array($arr["sel"]) as $s)
		{
			$o = obj($s);
			$o->delete();
		}
		return $arr["post_ru"];
	}

	function handle_stopper_stop($o, $inf)
	{
		$u = get_instance(CL_USER);
		$cp = obj($u->get_current_person());

		$row = obj();
		$row->set_parent($o->id());
		$row->set_class_id(CL_TASK_ROW);
		$row->set_prop("content", $inf["desc"]);
		$row->set_prop("date", $inf["start"]);
		$row->set_prop("impl", array($cp->id() => $cp->id()));
		$row->set_prop("time_real", $inf["hours"]);
		$row->set_prop("time_to_cust", $inf["hours"]);
		$row->set_prop("done", 1);
		$row->save();
		$o->connect(array(
			"to" => $row->id(),
			"type" => "RELTYPE_ROW"
		));
	}

	function _hrs_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "priority",
			"caption" => t("Prioriteet"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "num_hrs_guess",
			"caption" => t("Prognoositav tundide arv"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "num_hrs_real",
			"caption" => t("Tegelik tundide arv"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "num_hrs_to_cust",
			"caption" => t("Tundide arv kliendile"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "hr_price",
			"caption" => t("Tunnihind"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "deal_price",
			"caption" => t("Kokkuleppehind"),
			"align" => "right"
		));
		$t->define_field(array(
			"name" => "hr_price_currency",
			"caption" => t("Valuuta"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "bill_no",
			"caption" => t("Arve number"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "code",
			"caption" => t("Kood"),
			"align" => "center"
		));

		$curr_object_list = new object_list(array(
			"class_id" => CL_CURRENCY,
			"lang_id" => array(),
			"site_id" => array()
		));
		$curs = array();
		foreach($curr_object_list->arr() as $curr)
		{
			$curs[$curr->id()] = $curr->name();
		}
		$u = get_instance(CL_USER);
		$company = obj($u->get_current_company());
		if(!$arr["obj_inst"]->prop("hr_price_currency") && $arr["obj_inst"]->class_id())
		{
			$arr["obj_inst"]->set_prop("hr_price_currency", $company->prop("currency"));
		}


		// small conversion - if set, create a relation instead and clear, so that we can have multiple
		if ($this->can("view", $arr["obj_inst"]->prop("bill_no") ))
		{
			$arr["obj_inst"]->connect(array(
				"to" => $arr["obj_inst"]->prop("bill_no"),
				"type" => "RELTYPE_BILL"
			));
			$arr["obj_inst"]->set_prop("bill_no", "");
			$arr["obj_inst"]->save();
		}

		$bno = "";
		if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
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
			$bno = html::obj_change_url($ol->arr());
		}

		if ($bno == "" && is_object($arr["obj_inst"]) && !$arr["new"])
		{
			$bno = html::href(array(
				"url" => $this->mk_my_orb("create_bill_from_task", array("id" => $arr["obj_inst"]->id(),"post_ru" => get_ru())),
				"caption" => t("Loo uus arve")
			));
		}

		$t->define_data(array(
			"priority" => html::textbox(array(
				"name" => "priority",
				"value" => $arr["obj_inst"]->prop("priority"),
				"size" => 5
			)),
			"num_hrs_guess" => html::textbox(array(
				"name" => "num_hrs_guess",
				"value" => $arr["obj_inst"]->prop("num_hrs_guess"),
				"size" => 5
			)),
			"num_hrs_real" => html::textbox(array(
				"name" => "num_hrs_real",
				"value" => $arr["obj_inst"]->prop("num_hrs_real"),
				"size" => 5
			)),
			"num_hrs_to_cust" => html::textbox(array(
				"name" => "num_hrs_to_cust",
				"value" => $arr["obj_inst"]->prop("num_hrs_to_cust"),
				"size" => 5
			)),
			"hr_price" => html::textbox(array(
				"name" => "hr_price",
				"value" => $arr["obj_inst"]->prop("hr_price"),
				"size" => 5
			)),
			"deal_price" => t("Hind")." ".html::textbox(array(
				"name" => "deal_price",
				"value" => $arr["obj_inst"]->prop("deal_price"),
				"size" => 5
			))."<br>".t("Kogus")." ".html::textbox(array(
				"name" => "deal_amount",
				"value" => $arr["obj_inst"]->prop("deal_amount"),
				"size" => 5
			))."<br>".t("&Uuml;hik")." ".html::textbox(array(
				"name" => "deal_unit",
				"value" => $arr["obj_inst"]->prop("deal_unit"),
				"size" => 5
			))."<br>",
			"hr_price_currency" => html::select(array(
				"name" => "hr_price_currency",
				"options" => $curs,
				"value" => $arr["obj_inst"]->prop("hr_price_currency"),
			)),
			"bill_no" => $bno,
			"code" => $arr["obj_inst"]->prop("code")
		));
	}

	function _parts_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "new",
			"tooltip" => t("Uus"),
		));

		$tb->add_sub_menu(array(
			"parent" => "new",
			"name" => "cust",
			"text" => t("Tellija"),
		));
		
		$tb->add_menu_item(array(
			"parent" => "cust",
			"text" => t("Organisatsioon"),
			"link" => html::get_new_url(CL_CRM_COMPANY, $arr["obj_inst"]->parent(), array(
				"return_url" => get_ru(),
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 3 // RELTYPE_CUSTOMER
			)),
		));
		$tb->add_menu_item(array(
			"parent" => "cust",
			"text" => t("Isik"),
			"link" => html::get_new_url(CL_CRM_PERSON, $arr["obj_inst"]->parent(), array(
				"return_url" => get_ru(),
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 3 // RELTYPE_CUSTOMER
			)),
		));

		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("Projekt"),
			"link" => html::get_new_url(CL_PROJECT, $arr["obj_inst"]->parent(), array(
				"return_url" => get_ru(),
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 4 // RELTYPE_PROJECT
			)),
		));

		$tb->add_sub_menu(array(
			"parent" => "new",
			"name" => "part",
			"text" => t("Osaleja"),
		));
		
		if ($arr["obj_inst"]->prop("customer"))
		{
			$tb->add_menu_item(array(
				"parent" => "part",
				"text" => sprintf(t("Lisa isik organisatsiooni %s"), $arr["obj_inst"]->prop("customer.name")),
				"link" => html::get_new_url(CL_CRM_PERSON, $arr["obj_inst"]->prop("customer"), array(
					"return_url" => get_ru(), 
					"add_to_task" => $arr["obj_inst"]->id(),
					"add_to_co" => $arr["obj_inst"]->prop("customer"),
				))
			));
		}

		$cur_co = get_current_company();
		$tb->add_menu_item(array(
			"text" => sprintf(t("Lisa isik organisatsiooni %s"), $cur_co->name()),
			"parent" => "part",
			"link" => html::get_new_url(CL_CRM_PERSON, $cur_co->id(), array(
				"return_url" => get_ru(), 
				"add_to_task" => $arr["obj_inst"]->id(),
				"add_to_co" => $cur_co->id()
			))
		));

		$tb->add_menu_button(array(
			"name" => "search",
			"tooltip" => t("Otsi"),
			"img" => "search.gif"
		));

		$url = $this->mk_my_orb("do_search", array("pn" => "orderer_h", "clid" => array(
			CL_CRM_PERSON,
			CL_CRM_COMPANY
		)), "popup_search");
		$tb->add_menu_item(array(
			"parent" => "search",
			"text" => t("Tellija"),
			"link" => "javascript:aw_popup_scroll('$url','".t("Otsi")."',550,500)",
		));
		$url = $this->mk_my_orb("do_search", array("pn" => "project_h", "clid" => CL_PROJECT, "multiple" => 1), "popup_search");
		$tb->add_menu_item(array(
			"parent" => "search",
			"text" => t("Projekt"),
			"link" => "javascript:aw_popup_scroll('$url','".t("Otsi")."',550,500)",
		));
		$cur = get_current_company();
		$s = array("co" => array($cur->id() => $cur->id()));
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
		{
			$s["co"][$c->prop("to")] = $c->prop("to");
		}
		$url = $this->mk_my_orb("do_search", array("pn" => "participants_h", "clid" => CL_CRM_PERSON,"multiple" => 1, "s" => $s), "crm_participant_search");
		$tb->add_menu_item(array(
			"parent" => "search",
			"text" => t("Osaleja"),
			"link" => "javascript:aw_popup_scroll('$url','".t("Otsi")."',550,500)",
		));

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_rels"
		));
	}

	function _init_co_table(&$t)
	{
		$t->define_chooser(array(
			"name" => "sel_ord",
			"field" => "oid"
		));
		$t->define_field(array(
			"name" => "orderer",
			"caption" => t("Tellija"),
			"sortable" => 1,
			"width" => "40%"
		));
		$t->define_field(array(
			"name" => "phone",
			"caption" => t("Telefon"),
			"sortable" => 1,
			"width" => "35%"
		));
		$t->define_field(array(
			"name" => "contact",
			"caption" => t("Kontaktisik"),
			"sortable" => 1,
			"width" => "25%"
		));
	}

	function _co_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_co_table($t);

		if (!is_oid($arr["obj_inst"]->id()))
		{
			return;
		}
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
		{
			$c = $c->to();
			$t->define_data(array(
				"oid" => $c->id(),
				"orderer" => html::obj_change_url($c),
				"phone" => html::obj_change_url($c->prop("phone_id")),
				"contact" => html::obj_change_url($c->prop("contact_person"))
			));
		}
	}

	function _init_proj_table(&$t)
	{
		$t->define_chooser(array(
			"name" => "sel_proj",
			"field" => "oid"
		));
		$t->define_field(array(
			"name" => "project",
			"caption" => t("Projekt"),
			"sortable" => 1,
			"width" => "40%"
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"sortable" => 1,
			"width" => "35%"
		));
		$t->define_field(array(
			"name" => "deadline",
			"caption" => t("L&otilde;ppt&auml;htaeg"),
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y",
			"width" => "25%"
		));
	}

	function _proj_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_proj_table($t);

		if (!is_oid($arr["obj_inst"]->id()))
		{
			return;
		}
		$p = get_instance(CL_PROJECT);
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PROJECT")) as $c)
		{
			$c = $c->to();
			$t->define_data(array(
				"oid" => $c->id(),
				"project" => html::obj_change_url($c),
				"status" => $p->states[$c->prop("state")],
				"deadline" => $c->prop("deadline")
			));
		}
	}

	function _init_parts_table(&$t)
	{
		$t->define_chooser(array(
			"name" => "sel_part",
			"field" => "oid"
		));
		$t->define_field(array(
			"name" => "part",
			"caption" => t("Osaleja"),
			"sortable" => 1,
			"width" => "40%"
		));
		$t->define_field(array(
			"name" => "prof",
			"caption" => t("Ametinimetus"),
			"sortable" => 1,
			"width" => "35%"
		));
		$t->define_field(array(
			"name" => "phone",
			"caption" => t("Telefon"),
			"sortable" => 1,
			"width" => "25%"
		));
	}

	function _parts_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_parts_table($t);

		if (!is_oid($arr["obj_inst"]->id()))
		{
			return;
		}
		$p = get_instance(CL_PROJECT);
		$types = array(10, 8);
		if ($arr["obj_inst"]->class_id() == CL_CRM_CALL)
		{
			$types = 9;
		}
		if ($arr["obj_inst"]->class_id() == CL_CRM_MEETING)
		{
			$types = 8;
		}
		foreach($arr["obj_inst"]->connections_to(array("type" => $types)) as $c)
		{
			$c = $c->from();
			$t->define_data(array(
				"oid" => $c->id(),
				"part" => html::obj_change_url($c),
				"prof" => html::obj_change_url($c->prop("rank")),
				"phone" => html::obj_change_url($c->prop("phone"))
			));
		}
	}

	function _files_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "nemw",
			"tooltip" => t("Uus"),
		));

		// insert folders where to add
		$u = get_instance(CL_USER);
		if ($arr["obj_inst"] && $this->can("view", $arr["obj_inst"]->prop("customer")))
		{
			$impl = $arr["obj_inst"]->prop("customer");
			$impl_o = obj($impl);
			if (!$impl_o->get_first_obj_by_reltype("RELTYPE_DOCS_FOLDER"))
			{
				$impl = $u->get_current_company();
			}
		}
		else
		{
			$impl = $u->get_current_company();
		}
		if ($this->can("view", $impl))
		{
			$implo = obj($impl);
			$f = get_instance("applications/crm/crm_company_docs_impl");
			$fldo = $f->_init_docs_fld($implo);
			$ot = new object_tree(array(
				"parent" => $fldo->id(),
				"class_id" => CL_MENU
			));
			$folders = array($fldo->id() => $fldo->name());
			$tb->add_sub_menu(array(
				"parent" => "nemw",
				"name" => "mainf",
				"text" => $fldo->name(),
			));
			$this->_add_fa($tb, "mainf", $fldo->id());
			$this->_req_level = 0;
			$this->_req_get_folders_tb($ot, $folders, $fldo->id(), $tb, "mainf");
		}

		$url = $this->mk_my_orb("do_search", array("pn" => "files_h", "clid" => array(
			CL_FILE,CL_CRM_MEMO,CL_CRM_DOCUMENT,CL_CRM_DEAL,CL_CRM_OFFER
		), "multiple" => 1), "popup_search");
		$tb->add_button(array(
			"name" => "search",
			"img" => "search.gif",
			"url" => "javascript:aw_popup_scroll('$url','".t("Otsi")."',550,500)"
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_rels"
		));
	}

	function _req_get_folders_tb($ot, &$folders, $parent, &$tb, $parent_nm)
	{
		$this->_req_level++;
		$objs = $ot->level($parent);
		foreach($objs as $o)
		{
			$tb->add_sub_menu(array(
				"parent" => $parent_nm,
				"name" => "fd".$o->id(),
				"text" => $o->name(),
			));
			$this->_add_fa($tb, "fd".$o->id(), $o->id());
			$this->_req_get_folders_tb($ot, $folders, $o->id(), $tb, "fd".$o->id());
		}
		$this->_req_level--;
	}

	function _add_fa(&$tb, $pt_n, $pt)
	{
		$types = array(
			CL_FILE => t(""),
			CL_CRM_MEMO => t("Memo"),
			CL_CRM_DOCUMENT => t("CRM Dokument"),
			CL_CRM_DEAL => t("Leping"),
			CL_CRM_OFFER => t("Pakkumine")
		);
		foreach($types as $clid => $nm)
		{
			$tb->add_menu_item(array(
				"parent" => $pt_n,
				"text" => $nm,
				"link" => html::get_new_url($clid, $pt, array(
					"return_url" => get_ru(), 
					"alias_to" => $_GET["id"],
					"reltype" => 2
				)),
			));
		}
	}

	function _init_files_table(&$t)
	{
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Manuse nimi"),
			"sortable" => 1,
			"width" => "40%"
		));
		$t->define_field(array(
			"name" => "type",
			"caption" => t("T&uuml;&uuml;p"),
			"sortable" => 1,
			"width" => "35%"
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Viimane muutja"),
			"sortable" => 1,
			"width" => "25%"
		));
	}

	function _files_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_files_table($t);

		if (!is_oid($arr["obj_inst"]->id()))
		{
			return;
		}
		$clss = aw_ini_get("classes");
		$u = get_instance(CL_USER);
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_FILE")) as $c)
		{
			$c = $c->to();
			$m = $c->modifiedby();
			$m = $u->get_person_for_uid($m);
			$t->define_data(array(
				"oid" => $c->id(),
				"name" => html::obj_change_url($c),
				"type" => $clss[$c->class_id()]["name"],
				"modifiedby" => html::obj_change_url($m)
			));
		}
	}

	/**
		@attrib name=delete_rels
	**/
	function delete_rels($arr)
	{
		$o = obj($arr["id"]);
		$o = obj($o->brother_of());
		if (is_array($arr["sel_ord"]) && count($arr["sel_ord"]))
		{
			foreach(safe_array($arr["sel_ord"]) as $item)
			{
				$o->disconnect(array(
					"from" => $item,
				));
			}
			// now we need to get the first orderer and set that as the new default orderer
			$ord = $o->get_first_obj_by_reltype("RELTYPE_CUSTOMER");
			if ($ord && $o->prop("customer") != $ord->id())
			{
				$o->set_prop("customer", $ord->id());
				$o->save();
			}
			else
			if (!$ord)
			{
				$o->set_prop("customer", 0);
				$o->save();
			}
		}

		if (is_array($arr["sel_proj"]) && count($arr["sel_proj"]))
		{
			foreach(safe_array($arr["sel_proj"]) as $item)
			{
				$o->disconnect(array(
					"from" => $item,
				));
			}
			// now we need to get the first orderer and set that as the new default orderer
			$ord = $o->get_first_obj_by_reltype("RELTYPE_PROJECT");
			if ($ord && $o->prop("project") != $ord->id())
			{
				$o->set_prop("project", $ord->id());
				$o->save();
			}
			else
			if (!$ord)
			{
				$o->set_prop("project", 0);
				$o->save();
			}
		}	

		if (is_array($arr["sel_part"]) && count($arr["sel_part"]))
		{
			$arr["check"] = $arr["sel_part"];
			$arr["event_id"] = $arr["id"];
			post_message_with_param(
				MSG_MEETING_DELETE_PARTICIPANTS,
				CL_CRM_MEETING,
				&$arr
			);
		}

		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			foreach(safe_array($arr["sel"]) as $item)
			{
				$o->disconnect(array(
					"from" => $item,
				));
			}
		}
		return $arr["post_ru"];
	}
	
	function _init_predicates_table(&$t)
	{
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
		$t->define_field(array(
			"name" => "predicates",
			"caption" => t("Eeldustegevus"),
			"sortable" => 1,
			"width" => "80%"
		));
	}

	function _predicates_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_predicates_table($t);

		if (!is_oid($arr["obj_inst"]->id()))
		{
			return;
		}
		foreach($arr["obj_inst"]->connections_from(array("type" => 9)) as $c)
		{
			$c = $c->to();
			$t->define_data(array(
				"oid" => $c->id(),
				"predicates" => html::obj_change_url($c),
			));
		}
		return $t->draw();
	}
	
	
	function _predicates_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "new",
			"tooltip" => t("Uus"),
		));

		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("Toimetus"),
			"link" => html::get_new_url(CL_TASK, $arr["obj_inst"]->parent(), array(
				"return_url" => get_ru(),
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 9
			)),
		));
		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("K&otilde;ne"),
			"link" => html::get_new_url(CL_CRM_CALL, $arr["obj_inst"]->parent(), array(
				"return_url" => get_ru(),
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 9
			)),
		));
		
		$url = $this->mk_my_orb("do_search", array(
				"pn" => "predicates",
				"clid" => array(
					CL_TASK,
					CL_CRM_CALL
				),"multiple"=>1,
				),"popup_search");

		$tb->add_button(array(
			"name" => "search",
			"tooltip" => t("Otsi"),
			"img" => "search.gif",
			"url" => "javascript:aw_popup_scroll('$url','".t("Otsi")."',550,500)",
		));

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_rels"
		));
	}
}
?>
