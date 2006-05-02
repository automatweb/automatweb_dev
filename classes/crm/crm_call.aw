<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_call.aw,v 1.49 2006/05/02 14:07:02 kristo Exp $
// crm_call.aw - phone call
/*

@classinfo syslog_type=ST_CRM_CALL relationmgr=yes no_status=1 confirm_save_data=1

@default table=planner
@default group=general

@property customer type=relpicker table=planner field=customer reltype=RELTYPE_CUSTOMER 
@caption Klient

@property project type=relpicker table=planner field=project reltype=RELTYPE_PROJECT
@caption Projekt

@property info_on_object type=text store=no
@caption Osalejad

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property is_personal type=checkbox ch_value=1 field=meta method=serialize table=objects
@caption Isiklik

@property start1 type=datetime_select field=start 
@caption Algus

@property end type=datetime_select field=end 
@caption Lõpp

@property send_bill type=checkbox ch_value=1 table=planner field=send_bill 
@caption Saata arve

@property bill_no type=text table=planner 
@caption Arve number

@layout num_hrs type=hbox 

	@property time_guess type=textbox size=5 field=meta method=serialize table=objects
	@caption Prognoositav tundide arv 	

	@property time_real type=textbox size=5 field=meta method=serialize  table=objects
	@caption Tegelik tundide arv

	@property time_to_cust type=textbox size=5 field=meta method=serialize  table=objects
	@caption Tundide arv kliendile

@property content type=textarea cols=80 rows=30 field=description
@caption Sisu

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

@default table=objects
@default field=meta
@default method=serialize

@property task_toolbar type=toolbar no_caption=1 store=no group=participants
@caption "Toolbar"

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,weekdays,end
@caption Kordused

@property calendar_selector type=calendar_selector store=no group=calendars
@caption Kalendrid

@property project_selector type=project_selector store=no group=projects
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

@default group=other_calls

	@property new_call_date type=datetime_select store=no
	@caption Uue k&otilde;ne aeg

	@property other_calls type=table store=no no_caption=1

@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid
@groupinfo participants caption=Osalejad submit=no
@groupinfo other_calls caption="Eelmised k&otilde;ned" 

@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

@reltype CALLER value=2 clid=CL_CRM_PERSON
@caption Helistaja

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype PROJECT value=4 clid=CL_PROJECT
@caption Projekt

*/

class crm_call extends class_base
{
	function crm_call()
	{
		$this->init(array(
			"tpldir" => "crm/call",
			"clid" => CL_CRM_CALL
		));
	}

	function request_execute($obj)
	{
		classload("core/icons");
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $obj->name(),
			"icon" => icons::get_icon_url($obj),
			"time" => date("d-M-y H:i",$obj->prop("start1")),
			"content" => nl2br(create_links($obj->prop("content"))),
		));
		return $this->parse();
	}

	function parse_alias($arr)
	{
		// shows a phone call
		$obj = new object($arr["id"]);
		$done = $obj->prop("is_done");
		$done .= $obj->prop("name");
		return $done;
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

		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data['name'])
		{
			case "new_call_date":
				$data["value"] = -1;
				break;

			case "other_calls":
				$this->_other_calls($arr);
				break;

			case "name":	
				if($this->mail_data)
				{
					$data["value"] = $this->mail_data["subject"];
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
			
			case "project":
				$nms = array();
				if ($this->can("view",$arr["request"]["alias_to_org"]))
				{
					$ol = new object_list(array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_PARTICIPANT" => $arr["request"]["alias_to_org"],
					));
				}
				else
				if (is_object($arr["obj_inst"]))
				{
					if($this->can("view", $arr["obj_inst"]->prop("customer")))
					{
						$ol = new object_list(array(
							"class_id" => CL_PROJECT,
							"CL_PROJECT.RELTYPE_PARTICIPANT" => $arr["obj_inst"]->prop("customer"),
						));
						$nms = $ol->names();
					}
					else
					{
						$nms = array();
					}
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

			case "start1":
			case "end":
				$p = get_instance(CL_PLANNER);
				$cal = $p->get_calendar_for_user();
				if ($cal)
				{
					$calo = obj($cal);
					$data["minute_step"] = $calo->prop("minute_step");
				}

				if ($data["name"] ==  "end" && $arr["new"])
				{
					$data["value"] = time() + 900;
				}
				break;

			case 'info_on_object':
				$crm_person = get_instance(CL_CRM_PERSON);
				if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
				{
					$conns = $arr['obj_inst']->connections_to(array(
						'type' => 8,
					));
					$tmp = $arr['obj_inst']->connections_to(array(
						'type' => 9,
					));
					foreach($tmp as $tc)
					{
						$conns[] = $tc;
					}

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

					$u = get_instance(CL_USER);
					$cur_co = obj($u->get_current_company());
					$prms = array(
						"id" => $arr["obj_inst"]->id(),
						"pn" => "participants",
						"clid" => CL_CRM_PERSON,
						"multiple" => 1,
					);
					if ($arr["obj_inst"]->prop("customer.name") != "" || $cur_co->name() != "")
					{
						$prms["MAX_FILE_SIZE"] = 1;
						$prms["s"] = array("search_co" => $arr["obj_inst"]->prop("customer.name").",".$cur_co->name());
					}

					$url = $this->mk_my_orb("do_search", $prms, "crm_participant_search");
					$data["value"] .= html::href(array(
						"url" => "javascript:aw_popup_scroll(\"$url\",\"Otsing\",550,500)",
						"caption" => "<img src='".aw_ini_get("baseurl")."/automatweb/images/icons/search.gif' border=0>",
						"title" => t("Otsi")
					));
					$pm = get_instance("vcl/popup_menu");
					$pm->begin_menu("call_add_p");
					if ($this->can("view", $arr["obj_inst"]->prop("customer")))
					{	
						$pm->add_item(array(
							"text" => sprintf(t("Lisa isik organisatsiooni %s"), $arr["obj_inst"]->prop("customer.name")),
							"link" => html::get_new_url(CL_CRM_PERSON, $arr["obj_inst"]->prop("customer"), array(
								"return_url" => get_ru(), 
								"add_to_task" => $arr["obj_inst"]->id(),
								"add_to_co" => $arr["obj_inst"]->prop("customer"),
							))
						));
					}

					$cur_co = get_current_company();
					$pm->add_item(array(
						"text" => sprintf(t("Lisa isik organisatsiooni %s"), $cur_co->name()),
						"link" => html::get_new_url(CL_CRM_PERSON, $cur_co->id(), array(
							"return_url" => get_ru(), 
							"add_to_task" => $arr["obj_inst"]->id(),
							"add_to_co" => $cur_co->id()
						))
					));

					$data["value"] .= $pm->get_menu(array(
						"icon" => "new.gif"
					));
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
		}
		return $retval;
	}


	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "new_call_date":
				$v = date_edit::get_timestamp($data["value"]);
				if ($v > time())
				{
					// create a new call from the current one
					$i = get_instance(CL_CRM_CALL);
					$dat = array(
						"class" => "crm_call",
						"action" => "new",
						"parent" => $arr["obj_inst"]->parent(),
					);
					foreach($arr["obj_inst"]->properties() as $pn => $pv)
					{
						$dat[$pn] = $pv;
					}
					$dat["start1"] = $v;
					$dat["is_done"] = 0;
					$i->submit($dat);
				}
				else
				if ($v > 300)
				{
					$data["error"] = t("Uee k&otilde;ne aeg ei tohi olla minevikus!");
					return PROP_FATAL_ERROR;
				}
				break;
		};
		return $retval;
	}

	function callback_post_save($arr)
	{	//the person who added the task will be a participant, whether he likes it
		//or not
		if($arr['new'])
		{
			//
			$user = get_instance(CL_USER);
			$person = new object($user->get_current_person());
			$person->connect(array(
				'reltype' => 'RELTYPE_PERSON_CALL',
				'to' => $arr['obj_inst'],
			));
		}

		foreach(explode(",", $_POST["participants"]) as $person)
		{
			if ($this->can("view", $person))
			{
				$this->add_participant($arr["obj_inst"], obj($person));
			}
		}

		$pl = get_instance(CL_PLANNER);
		$pl->post_submit_event($arr["obj_inst"]);

	}


	function add_participant($task, $person)
	{
		$pl = get_instance(CL_PLANNER);
		$person->connect(array(
			"to" => $task->id(),
			"reltype" => "RELTYPE_PERSON_CALL"
		));

		// also add to their calendar
		if (($cal = $pl->get_calendar_for_person($person)))
		{
			$pl->add_event_to_calendar(obj($cal), $task);
		}
	}

	function cb_participant_selector($arr)
	{
		$elib = get_instance('calendar/event_property_lib');
		return $elib->participant_selector($arr);
	}

	function new_change($arr)
	{
		aw_session_set('org_action',aw_global_get('REQUEST_URI'));
		return parent::new_change($arr);
	}

	/**
		@attrib name=search_contacts
	**/
	function search_contacts($arr)
	{
		return $this->mk_my_orb('change',array(
				'id' => $arr['id'],
				'group' => $arr['group'],
				'search_contact_firstname' => $arr['search_contact_firstname'],
				'search_contact_lastname' => $arr['search_contact_lastname'],
				'search_contact_code' => $arr['search_contact_code'],
				'search_contact_company' => $arr['search_contact_company'],
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
		$arr["post_ru"] = post_ru();
		$arr["participants"] = 0;
		if ($_GET["action"] == "new")
		{
			$arr["add_to_cal"] = $_GET["add_to_cal"];
			$arr["alias_to_org"] = $_GET["alias_to_org"];
			$arr["reltype_org"] = $_GET["reltype_org"];
		}
	}

	function callback_generate_scripts($arr)
	{
		$task = get_instance(CL_TASK);
		return $task->callback_generate_scripts($arr);
	}

	function _init_other_class_t(&$t)
	{	
		$t->define_field(array(
			"name" => "when",
			"caption" => t("Millal"),
			"align" => "center",
			"type" => "time",
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "content",
			"caption" => t("Sisu"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "ed",
			"caption" => t("Vaata"),
			"align" => "center"
		));
	}

	function _other_calls($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_other_class_t($t);

		if (!$arr["obj_inst"]->prop("customer"))
		{
			return;
		}

		// get all previous calls to the same customer
		$ol = new object_list(array(
			"class_id" => CL_CRM_CALL,
			"lang_id" => array(),
			"site_id" => array(),
			"customer" => $arr["obj_inst"]->prop("customer")
		));
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"when" => $o->prop("start1"),
				"content" => nl2br($o->prop("content")),
				"ed" => html::obj_change_url($o)
			));
		}
		$t->set_default_sortby("when");
	}
};
?>
