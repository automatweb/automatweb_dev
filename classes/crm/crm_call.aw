<?php
// crm_call.aw - phone call
/*

@classinfo syslog_type=ST_CRM_CALL no_status=1 confirm_save_data=1 maintainer=markop prop_cb=1

@tableinfo planner index=id master_table=objects master_index=brother_of

@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid
@groupinfo participants caption=Osalejad submit=no
@groupinfo other_calls caption="Eelmised k&otilde;ned"
@groupinfo predicates caption="Eeldused"
@groupinfo customer caption="Klient" submit=no
@groupinfo other_settings caption="Muud seaded"

@default table=planner

	@property hr_schedule_job type=hidden datatype=int
	@property customer_relation type=hidden datatype=int
	@property real_maker type=hidden datatype=int
	@property result_task type=hidden datatype=int

@default group=predicates
	@property predicates type=relpicker multiple=1 reltype=RELTYPE_PREDICATE store=connect table=objects field=meta method=serialize
	@caption Eeldustegevused

	@property is_goal type=checkbox ch_value=1 table=planner field=aw_is_goal
	@caption Verstapost

@default group=general
	@property call_tools type=toolbar no_caption=1
	@caption K&otilde;ne toimingud

@layout top_bit type=vbox closeable=1 area_caption=P&otilde;hiandmed
	@layout top_2way type=hbox parent=top_bit
		@layout top_2way_left type=vbox parent=top_2way
			@property name type=textbox table=objects field=name parent=top_2way_left
			@caption Nimi

			@property comment type=textbox table=objects field=comment parent=top_2way_left
			@caption Kommentaar

			@property phone type=objpicker clid=CL_CRM_PHONE parent=top_2way_left
			@comment Number millele helistati v&otilde;i helistada
			@caption Number

			@property result type=select parent=top_2way_left
			@caption K&otilde;ne tulemus

			@property result_task_view type=text parent=top_2way_left store=no
			@caption Tulemustegevus

			@property new_call_date type=datepicker table=objects field=meta method=serialize parent=top_2way_left
			@caption Uue k&otilde;ne aeg

			@property add_clauses type=chooser store=no parent=top_2way_left multiple=1
			@caption Lisatingimused

		@layout top_2way_right type=vbox parent=top_2way
			@property start1 type=datepicker field=start table=planner parent=top_2way_right
			@caption Algus

			@property end type=datepicker table=planner parent=top_2way_right
			@caption L&otilde;peb

			@property deadline type=datepicker table=planner field=deadline parent=top_2way_right
			@caption T&auml;htaeg

			@property real_start type=text table=planner parent=top_2way_right editonly=1
			@caption Tegelik algus

			@property real_duration type=text datatype=int table=planner parent=top_2way_right editonly=1
			@caption Tegelik kestus (h)

	@property hrs_table type=table no_caption=1 store=no parent=top_bit

@layout center_bit type=hbox
	@property center_bit_vis type=hidden store=no no_caption=1 parent=center_bit

	@layout center_bit_left type=vbox parent=center_bit
		@layout center_bit_left_ct  type=hbox closeable=1 area_caption=Sisu parent=center_bit_left

	@layout center_bit_right type=vbox parent=center_bit
		@layout center_bit_right_top type=vbox parent=center_bit_right closeable=1 area_caption=Osapooled no_padding=1
		@layout center_bit_right_bottom type=vbox parent=center_bit_right closeable=1 area_caption=Manused no_padding=1

@layout content_bit type=vbox closeable=1 area_caption=Sisu
	@property content type=textarea cols=180 rows=30 field=description parent=content_bit no_caption=1 width=100%

@layout customer_bit type=vbox closeable=1 area_caption=Tellijad
	@property co_tb type=toolbar no_caption=1 store=no parent=customer_bit
	@property co_table type=table no_caption=1 store=no parent=customer_bit

@layout project_bit type=vbox closeable=1 area_caption=Projektid
	@property project_tb type=toolbar no_caption=1 store=no parent=project_bit
	@property proj_table type=table no_caption=1 store=no parent=project_bit

@layout impl_bit type=vbox closeable=1 area_caption=Osalejad
	@property impl_tb type=toolbar no_caption=1 store=no parent=impl_bit
	@property parts_table type=table no_caption=1 store=no parent=impl_bit

@layout files_bit type=vbox closeable=1 area_caption=Manused
	@property files_tb type=toolbar no_caption=1 store=no parent=files_bit
	@property files_table type=table no_caption=1 store=no parent=files_bit

@layout bills_bit type=vbox closeable=1 area_caption=Arved
	@property bills_tb type=toolbar no_caption=1 store=no parent=bills_bit
	@property bills_table type=table no_caption=1 store=no parent=bills_bit

@layout reults_bit type=vbox closeable=1 area_caption=Tulemused
	@property task_results_toolbar type=toolbar no_caption=1 store=no parent=reults_bit
	@property task_results_table type=table no_caption=1 store=no parent=reults_bit

	@property customer type=relpicker table=planner field=customer reltype=RELTYPE_CUSTOMER parent=center_bit_right_top
	@caption Klient

	@property project type=relpicker table=planner field=project reltype=RELTYPE_PROJECT parent=center_bit_right_top
	@caption Projekt

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property is_personal type=checkbox ch_value=1 field=meta method=serialize table=objects
@caption Isiklik

@property promoter type=checkbox ch_value=1 table=planner field=promoter
@caption Korraldaja

@property is_work type=checkbox ch_value=1 table=planner field=aw_is_work
@caption T&ouml;&ouml;aeg

@property bill_no type=text table=planner field=bill_no
@caption Arve number

@property hr_price type=textbox size=5 table=objects field=meta method=serialize
@caption Tunni hind

@property in_budget type=checkbox ch_value=1 table=planner field=aw_in_budget
@caption Eelarvesse

@property time_guess type=textbox size=5 field=meta method=serialize table=objects
@caption Prognoositav tundide arv

@property time_real type=textbox size=5 field=meta method=serialize table=objects
@caption Tegelik tundide arv

@property time_to_cust type=textbox size=5 field=meta method=serialize table=objects
@caption Tundide arv kliendile

@property priority type=textbox size=5 table=planner field=priority
@caption Prioriteet

@property hr_price_currency type=select field=meta method=serialize table=objects
@caption Valuuta

@property deal_unit type=textbox size=5 table=planner
@caption &Uuml;hik

@property deal_amount type=textbox size=5 table=planner
@caption Kogus

@property deal_price type=textbox size=5 table=planner
@caption Kokkuleppehind

@property deal_has_tax type=checkbox size=5 table=planner
@caption Sisestati koos k&auml;ibemaksuga

@property send_bill type=checkbox ch_value=1 table=planner field=send_bill group=other_settings
@caption Saata arve


@default table=objects
@default field=meta
@default method=serialize

@property task_toolbar type=toolbar no_caption=1 store=no group=participants
@caption Toolbar

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,weekdays,end
@caption Kordused

@property calendar_selector type=calendar_selector store=no group=calendars
@caption Kalendrid

@property project_selector type=project_selector store=no group=projects
@caption Projektid

@property comment_list type=comments group=comments no_caption=1
@caption Kommentaarid

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


@default group=other_calls
	@property other_calls type=table store=no no_caption=1


@default group=customer
	@property customer_info type=text store=no no_caption=1


// ------------------- RELATION TYPES -------------------

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype PROJECT value=4 clid=CL_PROJECT
@caption Projekt

@reltype PREDICATE value=9 clid=CL_TASK,CL_CRM_CALL,CL_CRM_MEETING
@caption Eeldustegevus

@reltype FILE value=2 clid=CL_FILE
@caption Fail

@reltype ROW value=7 clid=CL_TASK_ROW
@caption Rida

@reltype TMP1 value=8 clid=CL_CRM_COMPANY_CUSTOMER_DATA
@caption tmp1

*/

class crm_call extends task
{
	private $mail_data = array();

	function crm_call()
	{
		$this->init(array(
			"tpldir" => "crm/call",
			"clid" => CL_CRM_CALL
		));
	}

	function parse_alias($arr = array())
	{
		// shows a phone call
		$obj = new object($arr["id"]);
		$done = $obj->prop("is_done");
		$done .= $obj->prop("name");
		return $done;
	}

	function callback_on_load($arr)
	{
		if(!empty($arr["request"]["msgid"]))
		{
			$mail = get_instance(CL_MESSAGE);
			$this->mail_data = $mail->fetch_message(Array(
				"mailbox" => "INBOX" ,
				"msgrid" => $arr["request"]["msgrid"],
				"msgid" => $arr["request"]["msgid"],
				"fullheaders" => ""
			));
		}

		$application = automatweb::$request->get_application();
		if (!empty($arr["request"]["preparing_to_call"]) and "change" === $arr["request"]["action"] and $application->is_a(CL_CRM_SALES))
		{
			$this_o = new object($arr["request"]["id"], array(), CL_CRM_CALL);
			$this_o->lock();
		}
	}

	function _get_result_task_view(&$arr)
	{
		$r = PROP_IGNORE;
		$this_o = $arr["obj_inst"];

		if ($this->can("view", $this_o->prop("result_task")))
		{
			$result_task = new object($this_o->prop("result_task"));
			$arr["prop"]["value"] = html::href(array(
				"url" => $this->mk_my_orb("change", array(
					"id" => $result_task->id(),
					"return_url" => get_ru()
				), $result_task->class_id()),
				"caption" => $result_task->prop_xml("name"),
				"title" => t("Muuda")
			));

			$r = PROP_OK;
		}

		return $r;
	}

	function _get_customer_info(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$cro = new crm_company_customer_data();
		$cro->form_only = true;
		$cro->no_form = true;
		$arr["prop"]["value"] = $cro->view(array(
			"id" => $this_o->prop("customer_relation"),
			"group" => "sales_data"
		));
		return PROP_OK;
	}

	function _get_call_tools(&$arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$this_o = $arr["obj_inst"];

		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"action" => "submit",
			"img" => "save.gif"
		));

		if ($this_o->prop("real_duration") < 1)
		{
			if ($this_o->can_start())
			{ // call hasn't started yet
				$tb->add_button(array(
					"name" => "start",
					"tooltip" => t("Alusta k&otilde;net"),
					"action" => "start",
					"img" => "start.gif"
				));
			}
			elseif($this_o->prop("real_start") > 2)
			{ // end call button
				$tb->add_button(array(
					"name" => "end",
					"tooltip" => t("L&otilde;peta k&otilde;ne ja salvesta andmed"),
					"action" => "end",
					"img" => "stop.gif"
				));
			}
		}
		return PROP_OK;
	}

	function _get_start1(&$arr)
	{
		// $arr["prop"]["onblur"] = date("d.m.Y H:i", $arr["prop"]["value"]);
		return PROP_OK;
	}

	function _get_real_start(&$arr)
	{
		if (isset($arr["prop"]["value"]) and $arr["prop"]["value"] > 1)
		{
			$arr["prop"]["value"] = date("d.m.Y H:i", $arr["prop"]["value"]);
		}
		return PROP_OK;
	}

	function _get_real_duration(&$arr)
	{
		$arr["prop"]["value"] = number_format($arr["prop"]["value"]/60, 2, ".", " ");
		return PROP_OK;
	}

	function _get_result(&$arr)
	{
		$r = PROP_IGNORE;
		if ($arr["obj_inst"]->prop("real_start") > 1)
		{
			$arr["prop"]["options"] = array("" => "") + $arr["obj_inst"]->result_names();
			$arr["prop"]["onchange"] = "crmCallProcessResult(this);";
			$r = PROP_OK;
		}
		return $r;
	}

	function _get_phone(&$arr)
	{
		$r = PROP_OK;
		if (empty($arr["value"]) and isset($arr["request"]["phone_id"]) and is_oid($arr["request"]["phone_id"]))
		{
			$phone = obj($arr["request"]["phone_id"], array(), CL_CRM_PHONE);
			$arr["prop"]["value"] = $phone->name();
			$r = PROP_OK;
		}
		return $r;
	}

	function _get_new_call_date(&$arr)
	{
		$r = PROP_IGNORE;
		if ($arr["obj_inst"]->prop("real_start") > 1)
		{
			$r = PROP_OK;
		}
		return $r;
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
			case "comment":
				if ($data["type"] !== "textarea")
				{
					$data["type"] = "textarea";
					$data["rows"] = 2;
					$data["cols"] = 30;
				}
				break;
			case "co_tb":
			case "project_tb":
			case "impl_tb":
			case 'task_toolbar' :
				$fun = "_get_".$data['name'];
				$this->$fun($arr);
				break;

			case "parts_tb":
			case "co_table":
			case "proj_table":
			case "parts_table":
			case "files_tb":
			case "files_table":
			case "bills_tb":
			case "bills_table":
			case "hrs_table":
			case "other_calls":
				$fun = "_".$data['name'];
				$this->$fun($arr);
				break;

			case "add_clauses":
			case "is_done":
			case "status":
			case "is_personal":
			case "time_guess":
			case "time_real":
			case "time_to_cust":
			case "bill_no":
			case "hr_price":
			case "is_work":
			case "priority":
			case "hr_price_currency":
			case "in_budget":
			case "deal_unit":
			case "deal_amount":
			case "deal_price":
			case "deal_has_tax":
			case "promoter":
			case "project":
			case "customer":
				return PROP_IGNORE;
			case "name":
				if(count($this->mail_data))
				{
					$data["value"] = $this->mail_data["subject"];
				}

				if(!empty($arr["request"]["title"]) && !empty($arr["new"]))
				{
					$data["value"] = $arr["request"]["title"];
				}

				if(!empty($arr["request"]["participants"]) && !empty($arr["new"]))
				{
					$_SESSION["event"]["participants"] = explode("," , $arr["request"]["participants"]);
				}
				break;
			case "content":
				$data["style"] = "width: 100%";
				if(count($this->mail_data))
				{
					$data["value"] = sprintf(
					"From: %s\nTo: %s\nSubject: %s\nDate: %s\n\n%s",
						$this->mail_data["from"],
						$this->mail_data["to"],
						$this->mail_data["subject"],
						$this->mail_data["date"],
						$this->mail_data["content"]);
				}
				break;


			case "start1":
			case "end":
				$p = get_instance(CL_PLANNER);
				$cal = $p->get_calendar_for_user();
				if ($cal)
				{
					$calo = obj($cal);
					$data["minute_step"] = $calo->prop("minute_step");
					if ($data["name"] === "end" && (!is_object($arr["obj_inst"]) || !is_oid($arr["obj_inst"]->id())))
					{
						$data["value"] = time() + $calo->prop("event_def_len")*60;
					}
				}
				elseif ($data["name"] ===  "end" && $arr["new"])
				{
					$data["value"] = time() + 900;
				}

				if (!empty($arr["new"]))
				{
					if(isset($arr["request"]["date"]) and $day = $arr["request"]["date"])
					{
						$da = explode("-", $day);
						$data["value"] = mktime(date('h',$data["value"]), date('i', $data["value"]), 0, $da[1], $da[0], $da[2]);
					}
				}
				break;

			case "search_contact_company":
			case "search_contact_firstname":
			case "search_contact_lastname":
			case "search_contact_code":
				if ($arr["request"]["class"] !== "planner")
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
			case "hrs_table":
				$this->save_add_clauses($arr);
				break;
			case "parts_table":
				$this->save_parts_table($arr);
				break;
			case "is_done":
			case "status":
			case "is_personal":
			case "is_work":
			case "add_clauses":
			case "promoter":
			case "customer":
			case "project":
				return PROP_IGNORE;

			case "new_call_date":
				$v = datepicker::get_timestamp($data["value"]);
				$application = automatweb::$request->get_application();

				if ($application->is_a(CL_CRM_SALES))
				{
					if (isset($arr["request"]["result"]) and crm_call_obj::RESULT_CALL == $arr["request"]["result"] and $v <= time())
					{
						if ($v < 2)
						{
							$arr["prop"]["error"] = t("Uue k&otilde;ne aeg peab olema m&auml;&auml;ratud");
						}
						else
						{
							$arr["prop"]["error"] = t("Uue k&otilde;ne aeg ei saa olla minevikus");
						}
						return PROP_FATAL_ERROR;
					}
				}
				else
				{
					if ($v > time())
					{
						// create a new call from the current one
						$o = new object();
						$o->set_class_id(CL_CRM_CALL);
						$o->set_parent($arr["obj_inst"]->parent());
						foreach($arr["obj_inst"]->properties() as $pn => $pv)
						{
							if($o->is_property($pn))
							{
								$o->set_prop($pn , $pv);
							}
						}
						$o->save();
						foreach($arr["obj_inst"]->connections_from(array()) as $c)
						{
							$o->connect(array(
								'type' => $c->prop("reltype"),
								'to' => $c->prop("to"),
							));
						}
						foreach($arr["obj_inst"]->connections_to(array()) as $c)
						{
							$from = obj($c->prop("from"));
							$from->connect(array(
								'type' => $c->prop("reltype"),
								'to' => $o->id(),
							));
						}
					}
					elseif ($v > 300)
					{
						$data["error"] = t("Uee k&otilde;ne aeg ei tohi olla minevikus!");
						return PROP_FATAL_ERROR;
					}
				}
				break;

			case "end":
				if(isset($arr["request"]["start1"]) and date_edit::get_timestamp($arr["request"]["start1"]) > date_edit::get_timestamp($data["value"]))
				{
					$data["value"] = $arr["request"]["start1"];
					$arr["request"]["end"] = $arr["request"]["start1"];
				}
				break;
		}

		return $retval;
	}

	function _set_phone(&$arr)
	{
		$r = PROP_OK;
		if (empty($arr["prop"]["value"]) and isset($arr["request"]["phone_id"]) and is_oid($arr["request"]["phone_id"]))
		{
			$arr["prop"]["value"] = $arr["request"]["phone_id"];
		}
		return $r;
	}

	function _set_result(&$arr)
	{
		if (isset($arr["request"]["action"]) and "end" === $arr["request"]["action"] and empty($arr["prop"]["value"]))
		{
			$arr["prop"]["error"] = t("Tulemus peab olema m&auml;&auml;ratud");
			return PROP_FATAL_ERROR;
		}
		return PROP_OK;
	}

	function _set_comment(&$arr)
	{
		$this_o = $arr["obj_inst"];
		$val = $arr["prop"]["value"];
		if (strlen($val) > 1 and $val !== $this_o->comment() and $this_o->prop("customer_relation"))
		{
			$comm = new forum_comment();
			$commdata = $this_o->name() . ":\n" . $val;
			if (strlen($commdata["comment"]))
			{
				$comm->submit(array(
					"parent" => $this_o->prop("customer_relation"),
					"commtext" => $commdata,
					"return" => "id"
				));
			}
		}
		return PROP_OK;
	}

	function handle_stopper_stop($arr)
	{
		if(!$this->can("view", $arr["oid"]))
		{
			if(!strlen($arr["data"]["name"]["value"]) || !strlen($arr["data"]["part"]["value"]) || !strlen($arr["data"]["project"]["value"]))
			{
				return t("Nimi, osaleja ja projekt peavad olema t&auml;idetud!");
			}
		}
		if(!$this->can("view", $arr["data"]["project"]["value"]))
		{
			$cc = get_current_company();
			$np = new object();
			$np->set_class_id(CL_PROJECT);
			$np->set_parent($cc->id());
			$np->set_name($arr["data"]["project"]["value"]);
			$np->save();
			$arr["data"]["project"]["value"] = $np->id();
		}
		if(!$this->can("view", $arr["data"]["part"]["value"]))
		{
			$cc = get_current_company();
			$np = new object();
			$np->set_class_id(CL_CRM_PERSON);
			$np->set_parent($cc->id());
			$np->set_name($arr["data"]["part"]["value"]);
			$np->save();
			$arr["data"]["part"]["value"] = $np->id();
		}

		if(!$this->can("view", $arr["oid"]))
		{
			$o = new object();
			$o->set_parent($arr["data"]["project"]["value"]);
			$o->set_name($arr["data"]["name"]["value"]);
			$o->set_class_id(CL_CRM_CALL);
			$o->set_prop("start1", $arr["first_start"]);
			$o->save();
			$person = obj($arr["data"]["part"]["value"]);
			$person->connect(array(
				"to" => $o->id(),
				"type" => "RELTYPE_PERSON_CALL",
			));
			$o->connect(array(
				"to" => $arr["data"]["project"]["value"],
				"type" => "RELTYPE_PROJECT",
			));

			$arr["oid"] = $o->id();
		}
		$o = obj($arr["oid"]);
		$o->set_prop("time_real", $o->prop("time_real") + $arr["hours"]);
		$o->set_prop("time_to_cust", $o->prop("time_to_cust") + $arr["hours"]);
		$o->set_prop("is_done", $arr["data"]["isdone"]["value"]?1:0);
//		$o->set_prop("send_bill", $arr["data"]["tobill"]["value"]?1:0);
		$o->set_prop("content", $arr["data"]["desc"]["value"]);
		$o->set_prop("end", time());
		$o->save();
	}

	function add_participant(object $task, object $person)
	{
		$pl = get_instance(CL_PLANNER);
		$person->connect(array(
			"to" => $task->id(),
			"type" => "RELTYPE_PERSON_CALL"
		));

		// also add to their calendar
		if (($cal = $pl->get_calendar_for_person($person)))
		{
			$pl->add_event_to_calendar(obj($cal), $task);
		}
	}

	function new_change($arr)
	{
		aw_session_set('org_action',aw_global_get('REQUEST_URI'));
		return parent::new_change($arr);
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["participants"] = 0;
		$arr["participants_h"] = 0;
		$arr["orderer_h"] = isset($_GET["alias_to_org"]) ? $_GET["alias_to_org"] : 0;
		$arr["project_h"] = isset($_GET["set_proj"]) ? $_GET["set_proj"] : 0;
		$arr["files_h"] = 0;

		if ($_GET["action"] === "new")
		{
			$arr["add_to_cal"] = isset($_GET["add_to_cal"]) ? $_GET["add_to_cal"] : null;
			$arr["alias_to_org"] = isset($_GET["alias_to_org"]) ? $_GET["alias_to_org"] : null;
			$arr["reltype_org"] = isset($_GET["reltype_org"]) ? $_GET["reltype_org"] : null;
			$arr["set_pred"] = isset($_GET["set_pred"]) ? $_GET["set_pred"] : null;
			$arr["set_resource"] = isset($_GET["set_resource"]) ? $_GET["set_resource"] : null;
		}

		if (is_oid(automatweb::$request->arg("phone_id")))
		{
			$arr["phone_id"] = automatweb::$request->arg("phone_id");
		}
	}

	function callback_generate_scripts($arr)
	{
		$this_o = $arr["obj_inst"];
		$task = get_instance(CL_TASK);
		$scripts = $task->callback_generate_scripts($arr);
		$result_call = crm_call_obj::RESULT_CALL;
		$result_presentation = crm_call_obj::RESULT_PRESENTATION;
		$result_refused = crm_call_obj::RESULT_REFUSED;
		$result_noanswer = crm_call_obj::RESULT_NOANSWER;
		$result_busy = crm_call_obj::RESULT_BUSY;
		$result_hungup = crm_call_obj::RESULT_HUNGUP;
		$result_outofservice = crm_call_obj::RESULT_OUTOFSERVICE;
		$result_invalidnr = crm_call_obj::RESULT_INVALIDNR;
		$result_voicemail = crm_call_obj::RESULT_VOICEMAIL;
		$result_newnumber = crm_call_obj::RESULT_NEWNUMBER;
		$result_disconnected = crm_call_obj::RESULT_DISCONNECTED;
		$redirect_to_presentation = $this_o->is_in_progress() ? "true" : "false";

		$scripts .= <<<EOS
// hide and show elements according to call result
crmCallProcessResult(document.getElementById("result"), true);

function crmCallProcessResult(resultElem, init)
{
	if (resultElem)
	{
		if (resultElem.value == {$result_call})
		{ // show new call date dateselect
			$("input[name='new_call_date[date]']").parent().parent().parent().parent().css("display", "");
		}
		else if (resultElem.value == {$result_presentation})
		{
			$("input[name='new_call_date[date]']").parent().parent().parent().parent().css("display", "none");
			// $("a[href='javascript:submit_changeform('end');']").parent().css("display", "none"); // hide end call btn

			if ($("input[name='result_task']").attr("value") == 0)
			{ // hide 'end call' button
				$("a[href='javascript:submit_changeform('end');']").parent().css("display", "none");
			}

			if (!init && {$redirect_to_presentation})
			{
				//redirect to presentation view
				submit_changeform("submit");
			}
		}
		else
		{
			$("input[name='new_call_date[date]']").parent().parent().parent().parent().css("display", "none");
		}
	}
}
EOS;
		return $scripts;
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
			"customer" => $arr["obj_inst"]->prop("customer"),
			"brother_of" => new obj_predicate_prop("id")
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
				"MSG_MEETING_DELETE_PARTICIPANTS",
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

	private function get_new_parent($parent)
	{
		if($this->can("add" , $parent))
		{
			return $parent;
		}
		$company = get_current_company();
		return $company->id();
	}

	/**
		@attrib name=quick_add all_args=1
	**/
	function quick_add($arr)
	{
		$company = get_current_company();
		if($arr["bug_content"] || $arr["name"])
		{
			$o = new object();
			$o->set_class_id(CL_CRM_CALL);
			$o->set_parent($this->get_new_parent($arr["parent"]));
			$o->set_name($arr["name"]);
			foreach($arr as $key => $val)
			{
				switch($key)
				{
					case "start1":
					case "end":
						$o->set_prop($key , date_edit::get_timestamp($val));
						break;
					case "hr_price":
					case "content":
					case "promoter":
						$o->set_prop($key , $val);
						break;
				}
			}

			if($arr["customer"])
			{
				$customers = new object_list(array(
					"class_id" => CL_CRM_COMPANY,
					"site_id" => array(),
					"lang_id" => array(),
					"name" => $arr["customer"],
					"limit" => 1,
				));
				$customer = reset($customers->arr());
				if(!$customer)
				{
					$customer = obj($company->add_customer($arr["customer"]));
				}
				if(is_object($customer))
				{
					$o->add_customer($customer->id());
				}
			}

			if($arr["customer_person"] && is_object($customer))
			{
				$customer_persons = new object_list(array(
					"class_id" => CL_CRM_PERSON,
					"site_id" => array(),
					"lang_id" => array(),
					"name" => $arr["customer_person"],
					"limit" => 1,
				));
				$customer_person = reset($customer_persons->ids());
				if(!$customer_person)
				{
					$customer_person = $customer->add_worker_data(array(
						"worker" => $arr["customer_person"],
					));
				}
				$o->add_participant($customer_person);
			}

			if($arr["project"])
			{
				if(is_object($customer))
				{
					$projects = $customer->get_projects_as_customer();
					foreach($projects->names() as $id => $name)
					{
						if($arr["project"] == $name)
						{
							$project = $id;
							break;
						}
					}
					if(!$project)
					{
						$project = $customer->add_project_as_customer($arr["project"]);
					}
				}
				else
				{
					$projects = new object_list(array(
						"class_id" => CL_PROJECT,
						"site_id" => array(),
						"lang_id" => array(),
						"name" => $arr["project"],
						"limit" => 1,
					));
					$project = reset($projects->ids());
				}

				if($project)
				{
					$o->add_project($project);
				}
			}

			$u = get_instance(CL_USER);
			$p =$u->get_current_person();

			$data["person"] = $p;
			$data["time_real"] = round(((date_edit::get_timestamp($arr["end"]) - date_edit::get_timestamp($arr["start1"])) / 3600) , 2);
			$data["time_to_cust"] = (((int)(($data["time_real"] - 0.001)*4)) + 1) / 4;
			$o->set_participant_data($data);
			$o->save();
			$res = "<script language='javascript'>window.close();</script>";
			die($res);
		}
		$co_inst = get_instance(CL_CRM_COMPANY);
		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();

		$htmlc->add_property(array(
			"name" => "name",
			"type" => "textbox",
			"caption" => t("L&uuml;hikirjeldus"),
		));

		$htmlc->add_property(array(
			"name" => "start1",
			"type" => "datetime_select",
			"caption" => t("Algus"),
			"value" => time() - 15*60,
		));

		$htmlc->add_property(array(
			"name" => "end",
			"type" => "datetime_select",
			"caption" => t("L&otilde;pp"),
			"value" => time(),
		));

		$htmlc->add_property(array(
			"name" => "hr_price",
			"type" => "textbox",
			"caption" => t("Tunnihind"),
		));

		$htmlc->add_property(array(
			"name" => "promoter",
			"type" => "select",
			"caption" => t("K&otilde;ne suund"),
			"options" => array("1" => t("Tuli sisse") , "0" => t("L&auml;ks v&auml;lja")),
		));

		$htmlc->add_property(array(
			"name" => "content",
			"type" => "textarea",
			"caption" => t("Sisu"),
			"rows" => 10,
			"cols" => 60,
		));

		$htmlc->add_property(array(
			"name" => "klient",
			"type" => "text",
			"caption" => t("Klient"),
			"subtitle" => 1
		));

		$htmlc->add_property(array(
			"name" => "customer",
			"type" => "textbox",
			"caption" => t("Organisatsioon"),
			"autocomplete_class_id" => array(CL_CRM_COMPANY),
		));

		$htmlc->add_property(array(
			"name" => "customer_person",
			"type" => "textbox",
			"caption" => t("Isik"),
			"autocomplete_source" => "/automatweb/orb.aw?class=crm_company&action=worker_options_autocomplete_source",
			"autocomplete_params" => array("customer"),
		));

		$htmlc->add_property(array(
			"name" => "project",
			"type" => "textbox",
			"caption" => t("Projekt"),
			"autocomplete_source" => "/automatweb/orb.aw?class=crm_company&action=proj_autocomplete_source",
			"autocomplete_params" => array("customer","project"),
		));

		$htmlc->add_property(array(
			"name" => "sub",
			"type" => "button",
			"value" => t("Lisa uus K&otilde;ne!"),
			"onclick" => "changeform.submit();",
			"caption" => t("Lisa uus K&otilde;ne!")
		));
		$data = array(
			"orb_class" => $_GET["class"]?$_GET["class"]:$_POST["class"],
			"reforb" => 0,
			"parent" => $_GET["parent"],
		);
		$htmlc->finish_output(array(
			"action" => "quick_add",
			"method" => "POST",
			"data" => $data,
			"submit" => "no"
		));

		$content = $htmlc->get_result();
		return $content;
	}

	/**
      @attrib name=start all_args=1
      @param id required type=int acl=view
      @param phone_id optional type=int acl=view
	**/
	function start($arr)
	{
		$this_o = new object($arr["id"]);

		if (!empty($arr["phone_id"]))
		{
			$phone = obj($arr["phone_id"], array(), CL_CRM_PHONE);
			$this_o->make($phone);
		}
		else
		{
			$this_o->make();
		}

		$return_url = !empty($arr["post_ru"]) ? aw_url_change_var("phone", null, $arr["post_ru"]) : $this->mk_my_orb("change", array("id" => $arr["id"]), "crm_call");
		return $return_url;
	}

	/**
      @attrib name=end all_args=1
      @param id required type=int acl=view
	**/
	function end($arr)
	{
		$this_o = new object($arr["id"]);
		$r = $this->submit($arr);
		if ($this->data_processed_successfully())
		{
			$this_o->end();

			$application = automatweb::$request->get_application();
			if ($application->is_a(CL_CRM_SALES))
			{
				// return to calls list
				$r = $arr["return_url"];
			}
		}
		return $r;
	}

	function submit($arr = array())
	{
		$r = parent::submit($arr);
		if ("submit" === $arr["action"] and $this->data_processed_successfully())
		{
			$this_o = new object($arr["id"]);
			$application = automatweb::$request->get_application();
//			$role = $application->get_current_user_role();
			if ($application->is_a(CL_CRM_SALES) and $this_o->prop("real_duration") < 1 and crm_call_obj::RESULT_PRESENTATION == $this_o->prop("result"))
			{
				$result_task = new object($this_o->prop("result_task"));

				if (!$result_task->is_a(CL_CRM_PRESENTATION))
				{ // no existing presentation, create
					$customer_relation = obj($this_o->prop("customer_relation"), array(), CL_CRM_COMPANY_CUSTOMER_DATA);
					$result_task = $application->create_presentation($customer_relation);
					$this_o->set_prop("result_task", $result_task->id());
					$this_o->save();
				}

				// jump to presentation
				$r = html::get_change_url($result_task->id(), array("return_url" => $arr["post_ru"]));
			}
		}
		return $r;
	}
}
?>
