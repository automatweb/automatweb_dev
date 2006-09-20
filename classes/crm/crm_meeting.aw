<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_meeting.aw,v 1.79 2006/09/20 12:16:26 kristo Exp $
// kohtumine.aw - Kohtumine 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_MEETING_DELETE_PARTICIPANTS,CL_CRM_MEETING, submit_delete_participants_from_calendar);

@classinfo syslog_type=ST_CRM_MEETING confirm_save_data=1

@default table=objects

@default group=predicates

	@property predicates type=relpicker multiple=1 reltype=RELTYPE_PREDICATE store=connect table=objects field=meta method=serialize
	@caption Eeldustegevused

	@property is_goal type=checkbox ch_value=1 table=planner field=aw_is_goal 
	@caption Verstapost


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

	@property hrs_table type=table no_caption=1 store=no parent=top_bit


@layout center_bit type=hbox 
	@property center_bit_vis type=hidden store=no no_caption=1 parent=center_bit

	@layout center_bit_left type=vbox parent=center_bit 


		@layout center_bit_left_ct  type=hbox closeable=1 area_caption=Sisu parent=center_bit_left

			@property content type=textarea cols=80 rows=30 table=documents parent=center_bit_left_ct no_caption=1
			@caption Sisu

	@layout center_bit_right type=vbox parent=center_bit 

		@layout center_bit_right_top type=vbox parent=center_bit_right closeable=1 area_caption=Osapooled

			@property parts_tb type=toolbar no_caption=1 store=no parent=center_bit_right_top

			@property co_table type=table no_caption=1 store=no parent=center_bit_right_top
			@property proj_table type=table no_caption=1 store=no parent=center_bit_right_top
			@property parts_table type=table no_caption=1 store=no parent=center_bit_right_top


			@property customer type=relpicker table=planner field=customer reltype=RELTYPE_CUSTOMER parent=center_bit_right_top
			@caption Klient

			@property project type=relpicker table=planner field=project reltype=RELTYPE_PROJECT parent=center_bit_right_top
			@caption Projekt


		@layout center_bit_right_bottom type=vbox parent=center_bit_right closeable=1 area_caption=Manused

			@property files_tb type=toolbar no_caption=1 store=no parent=center_bit_right_bottom

			@property files_table type=table no_caption=1 store=no parent=center_bit_right_bottom


@layout center_bit_bottom type=vbox closeable=1 area_caption=Kokkuv&otilde;te

	@property summary type=textarea cols=80 rows=30 table=planner field=description no_caption=1 parent=center_bit_bottom
	@caption Kokkuv�te

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property is_personal type=checkbox ch_value=1 field=meta method=serialize 
@caption Isiklik

@property whole_day type=checkbox ch_value=1 field=meta method=serialize
@caption Kestab terve p�eva

@property udefch1 type=checkbox ch_value=1 user=1 field=meta method=serialize
@caption User-defined checkbox 1

@property udeftb1 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 1

@property udeftb2 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 2

@property udeftb3 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 3

@property udeftb4 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 4

@property udeftb5 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 5 

@property udeftb6 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 6 

@property udeftb7 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 7
 
@property udeftb8 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 8 

@property udeftb9 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 9 

@property udeftb10 type=textbox user=1 field=meta method=serialize
@caption User-defined textbox 10 

@property udefta1 type=textarea user=1 field=meta method=serialize
@caption User-defined textarea 1

@property udefta2 type=textarea user=1 field=meta method=serialize
@caption User-defined textarea 2

@property udefta3 type=textarea user=1 field=meta method=serialize
@caption User-defined textarea 3

@property send_bill type=checkbox ch_value=1 table=planner field=send_bill 
@caption Saata arve

@property bill_no type=text table=planner 
@caption Arve number

@property participants type=select multiple=1 table=objects field=meta method=serialize
@caption Osalejad

@property time_guess type=textbox size=5 field=meta method=serialize 
@caption Prognoositav tundide arv 	

@property time_real type=textbox size=5 field=meta method=serialize 
@caption Tegelik tundide arv

@property time_to_cust type=textbox size=5 field=meta method=serialize 
@caption Tundide arv kliendile

@property hr_price type=textbox size=5 field=meta method=serialize 
@caption Tunni hind

@property controller_disp type=text store=no 
@caption Kontrolleri v&auml;ljund

@default field=meta
@default method=serialize

@property task_toolbar type=toolbar no_caption=1 store=no group=participants
@caption Toolbar

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly,interval_hourly,interval_minutely
@caption Kordused

@property calendar_selector type=calendar_selector store=no group=calendars
@caption Kalendrid

@property other_selector type=callback callback=cb_calendar_others store=no group=others
@caption Teised

@property project_selector type=project_selector store=no group=projects all_projects=1
@caption Projektid

@property comment_list type=comments group=comments no_caption=1
@caption Kommentaarid

@property participant type=callback callback=cb_participant_selector store=no group=participants no_caption=1
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

@default group=resources

	@property sel_resources type=table no_caption=1

@default group=transl
	
	@property transl type=callback callback=callback_get_transl
	@caption T&otilde;lgi

@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo others caption=Teised
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid
@groupinfo participants caption=Osalejad submit=no
@groupinfo resources caption="Ressursid" 
@groupinfo transl caption=T&otilde;lgi
@groupinfo predicates caption="Eeldused" 

@tableinfo documents index=docid master_table=objects master_index=brother_of
@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype PROJECT value=4 clid=CL_PROJECT
@caption Projekt

@reltype RESOURCE value=5 clid=CL_MRP_RESOURCE
@caption ressurss

@reltype PREDICATE value=9 clid=CL_TASK,CL_CRM_CALL,CL_CRM_MEETING
@caption Eeldustegevus

@reltype FILE value=2 clid=CL_FILE
@caption Fail

*/

class crm_meeting extends class_base
{
	var $return_url;
	
	function crm_meeting()
	{
		$this->init(array(
			"clid" => CL_CRM_MEETING,
		));

		$this->trans_props = array(
			"name", "comment", "content"
		);
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
	}
	
	function get_property($arr)
	{
		if (is_object($arr["obj_inst"]) && $arr["obj_inst"]->prop("is_personal") && aw_global_get("uid") != $arr["obj_inst"]->createdby())
		{
			if (!($arr["prop"]["name"] == "start1" || $arr["prop"]["name"] == "end" || $arr["prop"]["name"] == "deadline"))
			{
				return PROP_IGNORE;
			}
		}

		$data = &$arr['prop'];
		$i = get_instance(CL_TASK);
		switch($data['name'])
		{
			case "parts_tb":
				$i->_parts_tb($arr);
				break;

			case "co_table":
				$i->_co_table($arr);
				break;

			case "proj_table":
				$i->_proj_table($arr);
				break;

			case "parts_table":
				$i->_parts_table($arr);
				break;

			case "files_tb":
				$i->_files_tb($arr);
				break;

			case "files_table":
				$i->_files_table($arr);
				break;

			case "hrs_table":
				$this->_hrs_table($arr);
				break;

			case "add_clauses":
				$data["options"] = array(
					"status" => t("Aktiivne"),
					"is_done" => t("Tehtud"),
					"whole_day" => t("Terve p&auml;ev"),
					"is_personal" => t("Isiklik"),
					"send_bill" => t("Arvele"),
				);
				$data["value"] = array(
					"status" => $arr["obj_inst"]->prop("status") == STAT_ACTIVE ? 1 : 0,
					"is_done" => $arr["obj_inst"]->prop("is_done") ? 1 : 0,
					"whole_day" => $arr["obj_inst"]->prop("whole_day") ? 1 : 0,
					"is_personal" => $arr["obj_inst"]->prop("is_personal") ? 1 : 0,
					"send_bill" => $arr["obj_inst"]->prop("send_bill") ? 1 : 0,
				);
				break;

			case "is_done":
			case "status":
			case "whole_day":
			case "is_personal":
			case "send_bill":
			case "time_guess":
			case "time_real":
			case "time_to_cust":
			case "hr_price":
			case "bill_no":
				return PROP_IGNORE;

			case "controller_disp":
				$cs = get_instance(CL_CRM_SETTINGS);
				$pc = $cs->get_meeting_controller($cs->get_current_settings());
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

			case "name":
				if($this->mail_data)
				{
					$data["value"] = $this->mail_data["subject"];
				break;
				}
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
			case "start1":
			case "end":
				$p = get_instance(CL_PLANNER);
				$cal = $p->get_calendar_for_user();
				if ($cal)
				{
					$calo = obj($cal);
					$data["minute_step"] = $calo->prop("minute_step");
					if ($data["name"] == "end" && (!is_object($arr["obj_inst"]) || !is_oid($arr["obj_inst"]->id())))
					{
						$data["value"] = time() + $calo->prop("event_def_len")*60;
					}
				}
				else
				if ($data["name"] == "end" && $arr["new"])
				{
					$data["value"] = time() + 900;
				}
				break;

			case "sel_resources":
				$t = get_instance(CL_TASK);
				$t->_get_sel_resources($arr);
				break;

			case "project":
				return PROP_IGNORE;
				$nms = array();
				if ($this->can("view",$arr["request"]["alias_to_org"]))
				{
					$ol = new object_list(array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_PARTICIPANT" => $arr["request"]["alias_to_org"],
						"lang_id" => array(),
						"site_id" => array()
					));
				}
				else
				if (is_object($arr["obj_inst"]) && !$arr["new"] && $this->can("view", $arr["obj_inst"]->prop("customer")))
				{
					$ol = new object_list(array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_PARTICIPANT" => $arr["obj_inst"]->prop("customer"),
						"lang_id" => array(),
						"site_id" => array()
					));
					$nms = $ol->names();
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
					$nms = $ol->names();
				}

				$data["options"] = array("" => "") + $nms;

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

				if (!isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$tmp->id()] = $tmp->name();
				}

				asort($data["options"]);
				if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
				{
					$arr["obj_inst"]->set_prop("customer", $data["value"]);
				}
				$data["onchange"] = "upd_proj_list()";
				break;

			case "participants":
				return PROP_IGNORE;
				$opts = array();
				$p = array();
				if ($this->can("view", $arr["request"]["alias_to_org"]))
				{
					$ao = obj($arr["request"]["alias_to_org"]);
					if ($ao->class_id() == CL_CRM_PERSON)
					{
						$p[$ao->id()] = $ao->id();
					}
				}
				if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
				{
					$conns = $arr['obj_inst']->connections_to(array(
						'type' => array( 8),//CRM_PERSON.RELTYPE_PERSON_MEETING==10
					));
					foreach($conns as $conn)
					{
						$obj = $conn->from();
						$opts[$obj->id()] = $obj->name();
						$p[$obj->id()] = $obj->id();
					}
				}
				// also add all workers for my company
				$u = get_instance(CL_USER);
				$co = $u->get_current_company();
				$w = array();
				$i = get_instance(CL_CRM_COMPANY);
				$i->get_all_workers_for_company(obj($co), &$w);
				foreach($w as $oid)
				{
					$o = obj($oid);
					$opts[$oid] = $o->name();
				}

				$i = get_instance(CL_CRM_COMPANY);
				uasort($opts, array(&$i, "__person_name_sorter"));

				$data["options"] = array("" => t("--Vali--")) + $opts;	
				$data["value"] = $p;
				break;
		

			case 'task_toolbar' :
			{
				$tb = &$data['toolbar'];
				$tb->add_button(array(
					'name' => 'del',
					'img' => 'delete.gif',
					'tooltip' => t('Kustuta valitud'),
					'action' => 'submit_delete_participants_from_calendar',
					"confirm" => t("Oled kindel, et tahad valitud osalejad eemaldada?"),
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
								break;
							}
						}
					}

				}
				break;
		}
	}
	

	function parse_alias($arr)
	{
		$target = new object($arr["alias"]["target"]);
		return html::href(array(
			//"url" => aw_ini_get("baseurl") . "/" . $target->id(),
			"url" => $this->mk_my_orb("change",array("id" => $target->id()),$target->class_id(),true,true),
			"caption" => $target->name(),
		));
	}

	function cb_participant_selector($arr)
	{
		$elib = get_instance('calendar/event_property_lib');
		return $elib->participant_selector($arr);
	}

	
	function cb_calendar_others($arr)
	{
		$elib = get_instance("calendar/event_property_lib");
		return $elib->calendar_others($arr);
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "add_clauses":
				$arr["obj_inst"]->set_status($data["value"]["status"] ? STAT_ACTIVE : STAT_NOTACTIVE);
				$arr["obj_inst"]->set_prop("is_done", $data["value"]["is_done"] ? 8 : 0);
				$arr["obj_inst"]->set_prop("whole_day", $data["value"]["whole_day"] ? 1 : 0);
				$arr["obj_inst"]->set_prop("is_personal", $data["value"]["is_personal"] ? 1 : 0);
				$arr["obj_inst"]->set_prop("send_bill", $data["value"]["send_bill"] ? 1 : 0);
				break;

			case "is_done":
			case "status":
			case "whole_day":
			case "is_personal":
			case "send_bill":
				return PROP_IGNORE;

			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			case "sel_resources":
				$t = get_instance(CL_TASK);
				$t->_set_resources($arr);
				break;

			case "project":
				return PROP_IGNORE;
				if (isset($_POST["project"]))
				{
					$data["value"] = $_POST["project"];
				}
				// add to proj
				if (is_oid($data["value"]) && $this->can("view", $data["value"]))
				{
					$this->add_to_proj = $data["value"];
				}
				break;

			case "customer":
				return PROP_IGNORE;
				if (isset($_POST["customer"]))
				{
					$data["value"] = $_POST["customer"];
				}
				break;

			case "participants":
				return PROP_IGNORE;
				if (!is_oid($arr["obj_inst"]->id()))
				{
					$this->post_save_add_parts = safe_array($data["value"]);
					return PROP_IGNORE;
				}
				$prop["value"] = $_POST["participants"];
				$pl = get_instance(CL_PLANNER);
				foreach(safe_array($data["value"]) as $person)
				{
					$p = obj($person);
					$p->connect(array(
						"to" => $arr["obj_inst"]->id(),
						"reltype" => "RELTYPE_PERSON_MEETING"
					));

					// also add to their calendar
					if (($cal = $pl->get_calendar_for_person($p)))
					{
						$pl->add_event_to_calendar(obj($cal), $arr["obj_inst"]);
					}
				}
				break;

			case "other_selector":
				$elib = get_instance("calendar/event_property_lib");
				$elib->process_other_selector($arr);
				break;
			
			case "whole_day":
				if ($data["value"])
				{
					$start = $arr["obj_inst"]->prop("start1");

					list($m,$d,$y) = explode("-",date("m-d-Y",$start));
					$daystart = mktime(9,0,0,$m,$d,$y);
					$dayend = mktime(17,0,0,$m,$d,$y);

					$arr["obj_inst"]->set_prop("start1",$daystart);
					$arr["obj_inst"]->set_prop("end",$dayend);
				};
				break;
		};
		return $retval;
	}

	function callback_post_save($arr)
	{
		if ($_POST["participants_h"] > 0)
		{
			$this->post_save_add_parts = explode(",", $_POST["participants_h"]);
		}

		if (is_array($this->post_save_add_parts))
		{
			foreach(safe_array($this->post_save_add_parts) as $person)
			{
				$this->add_participant($arr["obj_inst"], obj($person));
			}
			
		}
		if(!empty($arr['new']))
		{
			$this->add_participant($arr["obj_inst"], get_current_person());
		}
		if ($this->add_to_proj)
		{
			$arr["obj_inst"]->create_brother($this->add_to_proj);
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

		$pl = get_instance(CL_PLANNER);
		$pl->post_submit_event($arr["obj_inst"]);
	}
		

	/**
	@attrib name=submit_delete_participants_from_calendar
	@param id required type=int acl=view
	  @param group optional
	  @param check required
	**/
	function submit_delete_participants_from_calendar($arr)
	{
		if(is_array($arr["check"]))
		{
			foreach($arr["check"] as $person_id)
			{
				$obj = new object($person_id);
				if($obj->class_id() == CL_CRM_PERSON)
				{
					$ev = obj($arr["event_id"] ? $arr["event_id"] : $arr["id"]);
					if ($obj->is_connected_to(array("to" => $ev->brother_of())))
					{
						$obj->disconnect(array("from" => $ev->brother_of()));
						// also, remove from that person's calendar

						$person_i = $obj->instance();
						if ($_user = $person_i->has_user($obj))
						{
							$cals = $_user->connections_to(array(
								"from.class_id" => CL_PLANNER,
								"type" => "RELTYPE_CALENDAR_OWNERSHIP"
							));
							foreach($cals as $cal_con)
							{
								$cal = $cal_con->from();
								$event_folder = $cal->prop("event_folder");
								if (is_oid($event_folder) && $this->can("add", $event_folder))
								{
									// get brother
									$bl = new object_list(array(
										"brother_of" => $arr["event_id"],
										"site_id" => array(),
										"lang_id" => array(),
										"parent" => $event_folder
									));
									if ($bl->count())
									{
										$bro = $bl->begin();
										if ($bro->id() != $bro->brother_of())
										{
											$bro->delete();
										}
										else
										{
											// now, if we hit the original, then we have a problem
											// we still need to delete it, but we must turn it into a brother of the next one in line
											// if there is one. if not, then there's really nothing we can do.
											// so, list all brothers for this object
											$bl = new object_list(array(
												"brother_of" => $arr["event_id"],
												"site_id" => array(),
												"lang_id" => array(),
												"oid" => new obj_predicate_compare(OBJ_COMP_GREATER, $arr["event_id"])
											));
											if ($bl->count())
											{
												$nreal = $bl->begin();
												$nreal->originalize();
												$bro->delete();
											}
										}
									}
								}
							}
						}
					}
				}
				else
				{
					$obj->delete();
				}
			}
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}

	/**
		@attrib name=search_contacts
	**/
	function search_contacts($arr)
	{
		return $this->mk_my_orb('change',array(
				'id' => $arr['id'],
				'group' => $arr['group'],
				'search_contact_firstname' => ($arr['search_contact_firstname']),
				'search_contact_lastname' => ($arr['search_contact_lastname']),
				'search_contact_code' => ($arr['search_contact_code']),
				'search_contact_company' => ($arr['search_contact_company']),
				"return_url" => $arr["return_url"]
			),
			$arr['class']
		);
	}
	
	function request_execute($o)
	{
		return $this->show2(array("id" => $o->id()));
	}

	function show2($arr)
	{
		$ob = new object($arr["id"]);
		$cform = $ob->meta("cfgform_id");
		// feega hea .. n��d on vaja veel nimed saad
		$cform_obj = new object($cform);
		$output_form = $cform_obj->prop("use_output");
		if (is_oid($output_form))
		{
			$t = get_instance(CL_CFGFORM);
			$props = $t->get_props_from_cfgform(array("id" => $output_form));
		}
		else
		{
			$props = $this->load_defaults();
		}

		$htmlc = get_instance("cfg/htmlclient",array("template" => "webform.tpl"));
		$htmlc->start_output();

		foreach($props as $propname => $propdata)
		{
		  	$value = $ob->prop($propname);
			if ($propdata["type"] == "datetime_select")
			{
				if ($value == -1)
				{
					continue;
				};
				$value = date("d-m-Y H:i", $value);
			};

			if (!empty($value))
			{
			   $htmlc->add_property(array(
			      "name" => $propname,
			      "caption" => $propdata["caption"],
			      "value" => nl2br($value),
			      "type" => "text",
			   ));
			};
		};
		$htmlc->finish_output(array("submit" => "no"));

		$html = $htmlc->get_result(array(
			"form_only" => 1
		));
	
		return $html;
	}

	function new_change($arr)
	{
		aw_session_set('org_action',aw_global_get('REQUEST_URI'));
		return parent::new_change($arr);
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
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	function add_participant($task, $person)
	{
		$pl = get_instance(CL_PLANNER);
		$person->connect(array(
			"to" => $task->id(),
			"reltype" => "RELTYPE_PERSON_MEETING"
		));

		// also add to their calendar
		if (($cal = $pl->get_calendar_for_person($person)))
		{
			$pl->add_event_to_calendar(obj($cal), $task);
		}
	}

	function callback_mod_reforb($arr)
	{
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

	function callback_pre_save($arr)
	{
		$len = $arr["obj_inst"]->prop("end") - $arr["obj_inst"]->prop("start1");
		$hrs = floor($len / 900) / 4;
		
		// write length to time fields if empty
		if ($arr["obj_inst"]->prop("time_to_cust") == "")
		{
			$arr["obj_inst"]->set_prop("time_to_cust", $hrs);
		}
		if ($arr["request"]["set_resource"] != "")
		{
			$arr["obj_inst"]->connect(array(
				"to" => $arr["request"]["set_resource"],
				"type" => "RELTYPE_RESOURCE"
			));
		}

		if ($arr["obj_inst"]->prop("time_real") == "")
		{
			$arr["obj_inst"]->set_prop("time_real", $hrs);
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
	}

	function callback_generate_scripts($arr)
	{
		$task = get_instance(CL_TASK);
		return $task->callback_generate_scripts($arr);
	}

	function _hrs_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "time_guess",
			"caption" => t("Prognoositav tundide arv"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "time_real",
			"caption" => t("Tegelik tundide arv"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "time_to_cust",
			"caption" => t("Tundide arv kliendile"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "hr_price",
			"caption" => t("Tunnihind"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "bill_no",
			"caption" => t("Arve number"),
			"align" => "center"
		));

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
			"time_guess" => html::textbox(array(
				"name" => "time_guess",
				"value" => $arr["obj_inst"]->prop("time_guess"),
				"size" => 5
			)),
			"time_real" => html::textbox(array(
				"name" => "time_real",
				"value" => $arr["obj_inst"]->prop("time_real"),
				"size" => 5
			)),
			"time_to_cust" => html::textbox(array(
				"name" => "time_to_cust",
				"value" => $arr["obj_inst"]->prop("time_to_cust"),
				"size" => 5
			)),
			"hr_price" => html::textbox(array(
				"name" => "hr_price",
				"value" => $arr["obj_inst"]->prop("hr_price"),
				"size" => 5
			)),
			"bill_no" => $bno,
		));
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
}
?>
