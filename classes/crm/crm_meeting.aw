<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_meeting.aw,v 1.21 2004/12/31 09:46:56 ahti Exp $
// kohtumine.aw - Kohtumine 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_MEETING_DELETE_PARTICIPANTS,CL_CRM_MEETING, submit_delete_participants_from_calendar);

@classinfo syslog_type=ST_CRM_MEETING relationmgr=yes no_status=1

@default table=objects
@default group=general

@property info_on_object type=text store=no
@caption Osalejad

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start table=planner 
@caption Algab 

@property end type=datetime_select table=planner 
@caption L�peb

@property public type=checkbox ch_value=1 default=1 field=meta method=serialize
@caption Avalik

@property whole_day type=checkbox ch_value=1 field=meta method=serialize
@caption Kestab terve p�eva

@property content type=textarea cols=60 rows=30 table=documents
@caption Sisu

@property summary type=textarea cols=60 rows=30 table=planner field=description
@caption Kokkuv�te

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

@default field=meta
@default method=serialize

@property task_toolbar type=toolbar no_caption=1 store=no group=participants
@caption "Toolbar"

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly,
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

@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo others caption=Teised
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid
@groupinfo participants caption=Osalejad submit=no

@tableinfo documents index=docid master_table=objects master_index=brother_of
@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

*/

class crm_meeting extends class_base
{
	var $return_url;
	
	function crm_meeting()
	{
		$this->init(array(
			"clid" => CL_CRM_MEETING,
		));
	}

	function get_property($arr)
	{
		$data = &$arr['prop'];
		switch($data['name'])
		{
		
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
		//the person who added the task will be a participant, whether he likes it
		//or not
		if($arr['new'])
		{
			//
			$arr['obj_inst']->save();
			$user = get_instance('core/users/user');
			$person = new object($user->get_current_person());
			$person->connect(array(
				'reltype' => 'RELTYPE_PERSON_MEETING',
				'to' => $arr['obj_inst'],
			));
		}
		switch($data["name"])
		{
			case "public":
				$rights = array();
				if(checked($data["value"]))
				{
					$rights = array("can_view" => 1);
				}
				$nlg = $this->get_cval("non_logged_in_users_group");
				$g_oid = users::get_oid_for_gid($nlg);
				$group = obj($g_oid);
				$arr["obj_inst"]->acl_set($group, $rights);
				$arr["obj_inst"]->save();
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
					$arr["obj_inst"]->set_prop("end",$dayend);
				};
				break;
		};
		return $retval;
	}

	/**
      @attrib name=submit_delete_participants_from_calendar
      @param id required type=int acl=view
   **/
   function submit_delete_participants_from_calendar($arr)
   {
		if(is_array($arr['check']))
		{
			foreach($arr['check'] as $person_id)
			{
				$obj = new object($person_id);
				$ev = obj($arr["event_id"]);
				if ($obj->is_connected_to(array("to" => $ev->brother_of())))
				{
					$obj->disconnect(array('from'=>$ev->brother_of()));
				}
			}
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
				'search_contact_firstname' => urlencode($arr['search_contact_firstname']),
				'search_contact_lastname' => urlencode($arr['search_contact_lastname']),
				'search_contact_code' => urlencode($arr['search_contact_code']),
			),
			$arr['class']
		);
	}
}
?>
