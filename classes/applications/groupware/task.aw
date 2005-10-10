<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/task.aw,v 1.19 2005/10/10 08:23:56 kristo Exp $
// task.aw - TODO item
/*

@classinfo syslog_type=ST_TASK relationmgr=yes no_status=1 

@default table=objects
@default group=general

@property customer type=popup_search table=planner field=customer clid=CL_CRM_COMPANY
@caption Klient

@property code type=text size=5 table=planner field=code
@caption Kood

@property project type=popup_search table=planner field=project clid=CL_PROJECT
@caption Projekt


@property info_on_object type=text store=no
@caption Osalejad

@property is_done type=checkbox field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start table=planner
@caption Algus

@property end type=datetime_select table=planner 
@caption L&otilde;peb

@property deadline type=datetime_select table=objects field=meta method=serialize
@caption T&auml;htaeg

@property whole_day type=checkbox ch_value=1 field=meta method=serialize
@caption Kestab terve päeva

@property send_bill type=checkbox ch_value=1 table=planner field=send_bill default=1
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

@property files type=relmanager reltype=RELTYPE_FILE field=meta method=serialize  props=name,file,comment table_fields=name 
@caption Failid

@property participants type=select multiple=1 table=objects field=meta method=serialize
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

@groupinfo recurrence caption=Kordumine submit=no
@groupinfo calendars caption=Kalendrid
@groupinfo others caption=Teised submit_method=get
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid
@groupinfo reminders caption=Meeldetuletused
@groupinfo participants caption=Osalejad submit=no
@groupinfo other_exp caption="Muud kulud" 

@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

@reltype FILE value=2 clid=CL_FILE
@caption fail

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

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "participants":
				$opts = array();
				$p = array();
				if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
				{
					$conns = $arr['obj_inst']->connections_to(array(
						'type' => array(10, 8),//CRM_PERSON.RELTYPE_PERSON_TASK==10
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
				$data["options"] = array("" => t("--Vali--")) + $opts;	
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
				if ($data["value"] == "" && is_object($arr["obj_inst"]))
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
				if ($data["value"] != "")
				{
					$data["value"] = html::get_change_url($data["value"], array("return_url" => get_ru()), $data["value"]);
				}
				else
				{
					return PROP_IGNORE;
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
				$data["options"] = array("" => "") + $ol->names();
				if (!isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$tmp->id()] = $tmp->name();
				}
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

				if ($_GET["alias_to_org"])
				{
					$data["value"] = $_GET["alias_to_org"];
					//$arr["obj_inst"]->set_prop($customer, $data["value"]);
				}

				if (!isset($data["options"][$data["value"]]) && $this->can("view", $data["value"]))
				{
					$tmp = obj($data["value"]);
					$data["options"][$tmp->id()] = $tmp->name();
				}
				break;

			case "other_expenses":
				$this->_other_expenses($arr);
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
			case "participants":
				if (!is_oid($arr["obj_inst"]->id()))
				{
					return PROP_IGNORE;
				}
				$pl = get_instance(CL_PLANNER);
				foreach(safe_array($prop["value"]) as $person)
				{
					$p = obj($person);
					$p->connect(array(
						"to" => $arr["obj_inst"]->id(),
						"reltype" => "RELTYPE_PERSON_TASK"
					));

					// also add to their calendar
					if (($cal = $pl->get_calendar_for_person($p)))
					{
						$pl->add_event_to_calendar(obj($cal), $arr["obj_inst"]);
					}
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

	function callback_post_save($arr)
	{
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
}
?>
