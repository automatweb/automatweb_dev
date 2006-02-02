<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_meeting.aw,v 1.52 2006/02/02 13:53:58 kristo Exp $
// kohtumine.aw - Kohtumine 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_MEETING_DELETE_PARTICIPANTS,CL_CRM_MEETING, submit_delete_participants_from_calendar);

@classinfo syslog_type=ST_CRM_MEETING relationmgr=yes

@default table=objects
@default group=general

@property customer type=relpicker table=planner field=customer reltype=RELTYPE_CUSTOMER
@caption Klient

@property project type=relpicker table=planner field=project reltype=RELTYPE_PROJECT
@caption Projekt

@property info_on_object type=text store=no
@caption Osalejad

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property is_personal type=checkbox ch_value=1 field=meta method=serialize 
@caption Isiklik

@property start1 type=datetime_select field=start table=planner 
@caption Algab 

@property end type=datetime_select table=planner 
@caption Lõpeb

@property whole_day type=checkbox ch_value=1 field=meta method=serialize
@caption Kestab terve päeva

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

@property content type=textarea cols=60 rows=30 table=documents
@caption Sisu

@property bill_no type=text table=planner 
@caption Arve number

@property participants type=select multiple=1 table=objects field=meta method=serialize
@caption Osalejad

@property summary type=textarea cols=60 rows=30 table=planner field=description
@caption Kokkuvõte

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

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
		switch($data['name'])
		{
			case "start1":
			case "end":
				$p = get_instance(CL_PLANNER);
				$cal = $p->get_calendar_for_user();
				if ($cal)
				{
					$calo = obj($cal);
					$data["minute_step"] = $calo->prop("minute_step");
				}
				break;

			case "sel_resources":
				$t = get_instance(CL_TASK);
				$t->_get_sel_resources($arr);
				break;

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
				if (is_object($arr["obj_inst"]) && !$arr["new"] && $this->can("view", $arr["obj_inst"]->prop("customer")))
				{
					$ol = new object_list(array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_PARTICIPANT" => $arr["obj_inst"]->prop("customer"),
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
				break;

			case "participants":
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
		
         case 'info_on_object':
				if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
				{
					$conns = $arr['obj_inst']->connections_to(array(
						'type' => 8,//CRM_PERSON.RELTYPE_PERSON_MEETING==8
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
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			case "sel_resources":
				$t = get_instance(CL_TASK);
				$t->_set_resources($arr);
				break;

			case "project":
				$data["value"] = $_POST["project"];
				// add to proj
				if (is_oid($data["value"]) && $this->can("view", $data["value"]))
				{
					$this->add_to_proj = $data["value"];
				}
				break;

			case "customer":
				$data["value"] = $_POST["customer"];
				break;

			case "participants":
				if (!is_oid($arr["obj_inst"]->id()))
				{
					$this->post_save_add_parts = safe_array($data["value"]);
					return PROP_IGNORE;
				}
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
		if (is_array($this->post_save_add_parts))
		{
			foreach(safe_array($this->post_save_add_parts) as $person)
			{
				$this->add_participant($arr["obj_inst"], obj($person));
			}
			
		}
		if($arr['new'])
		{
			//
			$user = get_instance(CL_USER);
			$person = new object($user->get_current_person());
			$person->connect(array(
				'reltype' => 'RELTYPE_PERSON_MEETING',
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
								if (is_oid($event_folder && $this->can("add", $event_folder)))
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
				'search_contact_firstname' => urlencode($arr['search_contact_firstname']),
				'search_contact_lastname' => urlencode($arr['search_contact_lastname']),
				'search_contact_code' => urlencode($arr['search_contact_code']),
				'search_contact_company' => urlencode($arr['search_contact_company']),
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
		// feega hea .. nüüd on vaja veel nimed saad
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
	}
}
?>
