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
@groupinfo other_settings caption="Muud seaded"

@default table=planner

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

			@property phone type=objpicker clid=CL_CRM_PHONE mode=autocomplete parent=top_2way_left
			@comment Number millele helistati v&otilde;i helistada
			@caption Number

			@property result type=select parent=top_2way_left
			@caption K&otilde;ne tulemus

			@property new_call_date type=datetime_select table=objects field=meta method=serialize parent=top_2way_left
			@caption Uue k&otilde;ne aeg

			@property add_clauses type=chooser store=no parent=top_2way_left multiple=1
			@caption Lisatingimused

		@layout top_2way_right type=vbox parent=top_2way
			@property start1 type=datetime_select field=start table=planner parent=top_2way_right
			@caption Algus

			@property end type=datetime_select table=planner parent=top_2way_right
			@caption L&otilde;peb

			@property deadline type=datetime_select table=planner field=deadline parent=top_2way_right
			@caption T&auml;htaeg

			@property real_start type=text table=planner parent=top_2way_right
			@caption Tegelik algus

			@property real_duration type=text datatype=int table=planner parent=top_2way_right
			@caption Tegelik kestus (h)

			@property sales_assigned_salesman type=text store=no parent=top_2way_right
			@caption M&uuml;&uuml;giesindaja

			@property sales_lead_source type=text store=no parent=top_2way_right
			@caption Soovitaja/allikas

			@property sales_schedule_job type=hidden datatype=int table=planner

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

@property customer_relation type=relpicker table=planner field=customer_relation reltype=RELTYPE_CUSTOMER_RELATION group=other_settings
@caption Kliendisuhe

@default table=objects
@default field=meta
@default method=serialize

@property presentation type=relpicker reltype=RELTYPE_RESULT_PRESENTATION group=general
@comment Tulemuseks olev presentatsioon (m&auml;&auml;rab k&otilde;ne t&ouml;&ouml;riistariba nuppude konfiguratsiooni)
@caption Presentatsioon

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


// ------------------- RELATION TYPES -------------------

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

@reltype CUSTOMER_RELATION value=6 clid=CL_CRM_COMPANY_CUSTOMER_DATA
@caption Kliendisuhe

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

@reltype RESULT_PRESENTATION value=8 clid=CL_CRM_PRESENTATION
@caption Presentatsioon

@reltype RESULT_CALL value=10 clid=CL_CRM_CALL
@caption Uus k&otilde;ne

*/

class crm_call extends task
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
			"time" => date("d-M-y H:i", $obj->prop("start1")),
			"content" => nl2br(create_links($obj->prop("content"))),
		));
		return $this->parse();
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

	function _get_call_tools(&$arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$this_o = $arr["obj_inst"];

		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"action" => "submit",
			"img" => "save.gif",
		));

		if ($this_o->prop("real_duration") < 1)
		{
			if ($this_o->prop("real_start") < 2)
			{ // call hasn't started yet
				$tb->add_button(array(
					"name" => "start",
					"tooltip" => t("Alusta k&otilde;net"),
					"action" => "start",
					"img" => "nool1.gif",
				));
			}
			else
			{ // end call button
				$tb->add_button(array(
					"name" => "end",
					"tooltip" => t("L&otilde;peta k&otilde;ne ja salvesta andmed"),
					"action" => "end",
					"img" => "class_223.gif",
				));
			}
		}
		return PROP_OK;
	}

	function _get_sales_assigned_salesman(&$arr)
	{
		$name = $arr["obj_inst"]->prop("customer_relation.salesman.name");
		$arr["prop"]["value"] = $name;
		return PROP_OK;
	}

	function _get_sales_lead_source(&$arr)
	{
		$name = $arr["obj_inst"]->prop("customer_relation.sales_lead_source.name");
		$arr["prop"]["value"] = $name;
		return PROP_OK;
	}

	function _get_new_call_date(&$arr)
	{
		$arr["prop"]["year_from"] = date("Y");
		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES) and $arr["prop"]["value"] < 2)
		{
			$arr["prop"]["value"] = time();
		}
		return PROP_OK;
	}

	function _get_start1(&$arr)
	{
		$arr["prop"]["onblur"] = date("d.m.Y H:i", $arr["prop"]["value"]);
		return PROP_OK;
	}

	function _get_real_start(&$arr)
	{
		if ($arr["prop"]["value"] > 1)
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
		$arr["prop"]["options"] = array("" => "") + $arr["obj_inst"]->result_names();
		$arr["prop"]["onchange"] = "crmCallProcessResult(this);";
		return PROP_OK;
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
		$i = get_instance(CL_TASK);
		switch($data['name'])
		{
			case "comment":
				$data["type"] = "textarea";
				$data["rows"] = 2;
				$data["cols"] = 30;
				break;

			case "co_tb":
				$this->_get_co_tb($arr);
				break;

			case "project_tb":
				$this->_get_project_tb($arr);
				break;
			case "impl_tb":
				$this->_get_impl_tb($arr);
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

			case "files_tb":
				$this->_files_tb($arr);
				break;

			case "files_table":
				$this->_files_table($arr);
				break;

			case "bills_tb":
				$this->_bills_tb($arr);
				break;

			case "bills_table":
				$this->_bills_table($arr);
				break;

			case "hrs_table":
				$this->_hrs_table($arr);
				break;

			case "add_clauses":
				return PROP_IGNORE;

				$has_work_time = $arr["obj_inst"]->has_work_time();
				$data["options"] = array(
					"status" => t("Aktiivne"),
					"is_done" => t("Tehtud"),
					"is_personal" => t("Isiklik"),
					"send_bill" => t("Arvele"),
//					"is_work" => t("T&ouml;&ouml;aeg"),
				);
				$data["value"] = array(
					"status" => $arr["obj_inst"]->prop("status") == STAT_ACTIVE ? 1 : 0,
					"is_done" => $arr["obj_inst"]->prop("is_done") ? 1 : 0,
					"is_personal" => $arr["obj_inst"]->prop("is_personal") ? 1 : 0,
					"send_bill" => $arr["obj_inst"]->prop("send_bill") ? 1 : 0,
//					"is_work" => $arr["obj_inst"]->prop("is_work") ? 1 : 0,
				);
				if(!$has_work_time)
				{
					$data["options"]["is_work"] = t("T&ouml;&ouml;aeg");
				}
				break;

			case "is_done":
			case "status":
			case "is_personal":
//			case "send_bill":
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
				return PROP_IGNORE;

			case "other_calls":
				$this->_other_calls($arr);
				break;

			case "name":
				if($this->mail_data)
				{
					$data["value"] = $this->mail_data["subject"];
				}
				if($arr["request"]["title"] && $arr["new"])
				{
					$data["value"] = $arr["request"]["title"];
				}
				if($arr["request"]["participants"] && $arr["new"])
				{
					$_SESSION["event"]["participants"] = explode("," , $arr["request"]["participants"]);
				}
				break;
			case "content":
				$data["style"] = "width: 100%";
				if($this->mail_data)
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
				if (is_object($arr["obj_inst"]))
				{
					if($this->can("view", $arr["obj_inst"]->prop("customer")))
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
				if ($data["name"] ==  "end" && $arr["new"])
				{
					$data["value"] = time() + 900;
				}
				if ($arr["new"])
				{
					if($day = $arr["request"]["date"])
					{
						$da = explode("-", $day);
						$data["value"] = mktime(date('h',$data["value"]), date('i', $data["value"]), 0, $da[1], $da[0], $da[2]);
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
			case "hrs_table":
				$this->save_add_clauses($arr);
				break;
			case "add_clauses":
					return PROP_IGNORE;

				$arr["obj_inst"]->set_status($data["value"]["status"] ? STAT_ACTIVE : STAT_NOTACTIVE);
				$arr["obj_inst"]->set_prop("is_done", $data["value"]["is_done"] ? 8 : 0);
				$arr["obj_inst"]->set_prop("is_personal", $data["value"]["is_personal"] ? 1 : 0);
//				$arr["obj_inst"]->set_prop("send_bill", $data["value"]["send_bill"] ? 1 : 0);
//				$arr["obj_inst"]->set_prop("is_work", $data["value"]["is_work"] ? 1 : 0);
				if($data["value"]["is_work"])
				{
					$rowdata = array("time_real" => $arr["obj_inst"]->prop("time_real"),
					"time_to_cust" => $arr["obj_inst"]->prop("time_to_cust"));
					$arr["obj_inst"]->set_primary_row($rowdata);
				}

				break;
			case "parts_table":
				$this->save_parts_table($arr);
				break;
			case "is_done":
			case "status":
			case "is_personal":
//			case "send_bill":
			case "is_work":
			case "promoter":
				return PROP_IGNORE;

			case "new_call_date":
				$v = date_edit::get_timestamp($data["value"]);
				$application = automatweb::$request->get_application();

				if ($application->is_a(CL_CRM_SALES))
				{
					if (crm_call_obj::RESULT_CALL == $arr["request"]["result"] and $v <= time())
					{
						$arr["prop"]["error"] = t("Uue k&otilde;ne aeg peab olema m&auml;&auml;ratud");
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

			case "customer":
			case "project":
				return PROP_IGNORE;

			case "end":
				if(date_edit::get_timestamp($arr["request"]["start1"]) > date_edit::get_timestamp($data["value"]))
				{

					$data["value"] = $arr["request"]["start1"];
					$arr["request"]["end"] = $arr["request"]["start1"];
				}
				break;
		};
		return $retval;
	}

	function _set_phone(&$arr)
	{
		return PROP_IGNORE;
	}

	function _get_phone(&$arr)
	{
		$phone = obj($arr["prop"]["value"]);
		$arr["prop"]["value"] = $phone->name();
		return PROP_OK;
	}

	function _set_result(&$arr)
	{
		if ("end" === $arr["request"]["action"] and empty($arr["prop"]["value"]))
		{
			$arr["prop"]["error"] = t("Tulemus peab olema m&auml;&auml;ratud");
			return PROP_FATAL_ERROR;
		}
		return PROP_OK;
	}

/*
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

		foreach(explode(",", $_POST["participants_h"]) as $person)
		{
			if ($this->can("view", $person))
			{
				$this->add_participant($arr["obj_inst"], obj($person));
			}
		}

		$pl = get_instance(CL_PLANNER);
		$pl->post_submit_event($arr["obj_inst"]);
		if(!empty($arr['new']))
		{
			$this->add_participant($arr["obj_inst"], get_current_person());
		}

		if ($this->can("view", $_POST["orderer_h"]))
		{
			$arr["obj_inst"]->connect(array(
				"to" => $_POST["orderer_h"],
				"type" => "RELTYPE_CUSTOMER"
			));
			$arr["obj_inst"]->set_prop("customer" , $_POST["orderer_h"]);
			$arr["obj_inst"]->save();
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

		if($_SESSION["add_to_task"])
		{
			if(is_oid($_SESSION["add_to_task"]["project"]))
			{
				 $arr["obj_inst"]->connect(array(
					"to" => $_SESSION["add_to_task"]["project"],
					"type" => "RELTYPE_PROJECT"
				));
			}
			if(is_oid($_SESSION["add_to_task"]["customer"]))
			{
				$arr["obj_inst"]->connect(array(
					"to" => $_SESSION["add_to_task"]["customer"],
					"type" => "RELTYPE_CUSTOMER"
				));
			}
			if(is_oid($_SESSION["add_to_task"]["impl"]))
			{
				$this->add_participant($arr["obj_inst"], obj($_SESSION["add_to_task"]["impl"]));
			}
			unset($_SESSION["add_to_task"]);
		}

	}
*/

	function stopper_autocomplete($requester, $params)
	{
		switch($requester)
		{
			case "part":
				$l = new object_list(array(
					"class_id" => CL_CRM_PERSON,
				));
				foreach($l->arr() as $obj)
				{
					$ret[$obj->id()] = $obj->name();
				}
			break;
			case "project":

				if(strlen($params["part"]))
				{
					$parts = split(",", $params["part"]);

					$c = new connection();
					$conns = $c->find(array(
						"from.class_id" => CL_PROJECT,
						"to" => $parts,
					));
					foreach($conns as $conn)
					{
						$p = obj($conn["from"]);
						$ret[$p->id()] = $p->name();
					}
				}
				else
				{
					$l = new object_list(array(
						"class_id" => CL_PROJECT,
					));
					foreach($l->arr() as $obj)
					{
						$ret[$obj->id()] = $obj->name();
					}

				}
			break;
			default:
				$ret = array();
				break;
		}
		return $ret;
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

	function gen_stopper_addon($arr)
	{
		$props = array(
			array(
				"name" => "name",
				"type" => "textbox",
				"caption" => t("Nimi"),
			),
			array(
				"name" => "part",
				"type" => "textbox",
				"caption" => t("Osaleja"),
				"autocomplete" => true,
			),
			array(
				"name" => "project",
				"type" => "textbox",
				"caption" => t("Projekt"),
				"autocomplete" => true,
			),
			array(
				"name" => "isdone",
				"type" => "checkbox",
				"caption" => t("Tehtud"),
				"ch_value" => 1,
				"value" => 1,
			),
			array(
				"name" => "tobill",
				"type" => "checkbox",
				"caption" => t("Arvele"),
			),
			array(
				"name" => "desc",
				"type" => "textarea",
				"caption" => t("Kirjeldus"),
			),
		);
		return $props;
	}

	function add_participant($task, $person)
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
		$arr["participants_h"] = 0;
		$arr["orderer_h"] = $_GET["alias_to_org"] ? $_GET["alias_to_org"] : 0;
		$arr["project_h"] = $_GET["set_proj"] ? $_GET["set_proj"] : 0;
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

	function callback_generate_scripts($arr)
	{
		$task = get_instance(CL_TASK);
		$scripts = $task->callback_generate_scripts($arr);
		$result_call = crm_call_obj::RESULT_CALL;
		$result_presentation = crm_call_obj::RESULT_PRESENTATION;
		$result_refused = crm_call_obj::RESULT_REFUSED;
		$scripts .= <<<EOS
// hide and show elements according to call result
crmCallProcessResult(document.getElementById("result"));

function crmCallProcessResult(resultElem)
{
	if (resultElem)
	{
		if (resultElem.value == {$result_call})
		{
			$("select[name='new_call_date[day]']").parent().parent().parent().parent().css("display", "");
		}
		else if (resultElem.value == {$result_presentation})
		{
			if ($("#presentation").attr("value") == 0)
			{
				$("a[href='javascript:submit_changeform('end');']").parent().css("display", "none");
			}
			$("select[name='new_call_date[day]']").parent().parent().parent().parent().css("display", "none");
		}
		else if (resultElem.value == {$result_refused})
		{
			$("select[name='new_call_date[day]']").parent().parent().parent().parent().css("display", "none");
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
/*
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
			"bill_no" => $bno,
		));
	}
*/
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

	function do_db_upgrade($tbl, $field, $q, $err)
	{
		if ("planner" === $tbl)
		{
			$i = get_instance(CL_TASK);
			return $i->do_db_upgrade($tbl, $field);
		}
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
      @param phone_id required type=int acl=view
	**/
	function start($arr)
	{
		$this_o = new object($arr["id"]);
		$phone = obj($arr["phone_id"], array(), CL_CRM_PHONE, true);
		$this_o->make($phone);
		$arr["action"] = "change";
		unset($arr["phone_id"]);
		return $this->mk_my_orb("change", $arr);
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
				$crm_sales = new crm_sales();
				$arr2 = $arr;
				$arr2["call_id"] = $this_o->id();
				$arr2["id"] = $application->id();
				$arr2["action"] = "end_call";
				$r = $crm_sales->end_call($arr2);
			}
		}
		return $r;
	}

	function submit($arr = array())
	{
		$existing_presentation = is_oid($arr["presentation"]);
		$r = parent::submit($arr);
		if ("submit" === $arr["action"] and $this->data_processed_successfully())
		{
			$this_o = new object($arr["id"]);
			$application = automatweb::$request->get_application();
			if ($application->is_a(CL_CRM_SALES) and $this_o->prop("real_duration") < 1)
			{
				if ($this_o->prop("result") == crm_call_obj::RESULT_PRESENTATION and !$existing_presentation)
				{ // jump to presentation
					$presentation = $this_o->get_first_obj_by_reltype("RELTYPE_RESULT_PRESENTATION");
					$r = html::get_change_url($presentation->id(), array("return_url" => $arr["post_ru"]));
				}
				elseif ($this_o->prop("result") == crm_call_obj::RESULT_CALL)
				{ // require next call date
				}
			}
		}
		return $r;
	}
}
?>
