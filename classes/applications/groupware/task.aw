<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/task.aw,v 1.6 2004/09/19 19:25:48 rtoomas Exp $
// task.aw - TODO item
/*

@classinfo syslog_type=ST_TASK relationmgr=yes no_status=1 

@default table=objects
@default group=general

@property info_on_object type=text store=no
@caption Osalejad

@property is_done type=checkbox field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start table=planner
@caption Algus

@property whole_day type=checkbox ch_value=1 field=meta method=serialize
@caption Kestab terve p�eva

@property content type=textarea cols=60 rows=30 field=description table=planner
@caption Sisu

@property aliasmgr type=aliasmgr store=no
@caption Seostehaldur

@default field=meta
@default method=serialize

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly,
@caption Kordused

@property calendar_selector type=callback callback=cb_calendar_selector store=no group=calendars
@caption Kalendrid

@property other_selector type=callback callback=cb_calendar_others store=no group=others
@caption Teised

@property project_selector type=callback callback=cb_project_selector store=no group=projects
@caption Projektid

@property comment_list type=comments group=comments no_caption=1
@caption Kommentaarid

@property rmd type=reminder group=reminders store=no
@caption Meeldetuletus

@groupinfo recurrence caption=Kordumine submit=no
@groupinfo calendars caption=Kalendrid
@groupinfo others caption=Teised
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid
@groupinfo reminders caption=Meeldetuletused

@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

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
			case 'info_on_object':
				if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
				{
					$conns = $arr['obj_inst']->connections_to(array(
						'type' => 10,//CRM_PERSON.RELTYPE_PERSON_TASK==10
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
		};
		return $retval;
	}
	
	function cb_project_selector($arr)
	{
		$elib = get_instance("calendar/event_property_lib");
		return $elib->project_selector($arr);
	}

	function cb_calendar_selector($arr)
	{
		$elib = get_instance("calendar/event_property_lib");
		return $elib->calendar_selector($arr);
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
		
		//the person who added the task will be a participant, whether he likes it
		//or not
		if($arr['new'])
		{
			//
			$arr['obj_inst']->save();
			$user = get_instance('core/users/user');
			$person = new object($user->get_current_person());
			$person->connect(array(
				'reltype' => 'RELTYPE_PERSON_TASK',
				'to' => $arr['obj_inst'],
			));
		}
		
		switch($data["name"])
		{
			case "project_selector":
				$elib = get_instance("calendar/event_property_lib");
				$elib->process_project_selector($arr);
				break;

			case "calendar_selector":
				$elib = get_instance("calendar/event_property_lib");
				$elib->process_calendar_selector($arr);
				break;

			case "other_selector":
				$elib = get_instance("calendar/event_property_lib");
				$elib->process_other_selector($arr);
				break;

			case "whole_day":
				if ($data["value"])
				{
					list($m,$d,$y) = explode("-",date("m-d-Y"));
					$daystart = mktime(9,0,0,$m,$d,$y);
					$dayend = mktime(17,0,0,$m,$d,$y);
					$arr["obj_inst"]->set_prop("start1",$daystart);
				};
				break;
		};
		return $retval;
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
}
?>
