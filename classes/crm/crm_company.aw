<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_company.aw,v 1.21 2004/06/10 13:24:19 duke Exp $
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_PERSON, on_connect_person_to_org)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_PERSON, on_disconnect_person_from_org)
HANDLE_MESSAGE_WITH_PARAM(MSG_EVENT_ADD, CL_CRM_PERSON, on_add_event_to_person)

@classinfo relationmgr=yes
@tableinfo kliendibaas_firma index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@property navtoolbar type=toolbar store=no no_caption=1 group=general,all_actions,meetings,tasks,calls editonly=1


@property name type=textbox size=30 maxlenght=255 table=objects
@caption Organisatsiooni nimi

@property comment type=textarea cols=65 rows=3 table=objects
@caption Kommentaar

@property reg_nr type=textbox size=10 maxlength=20 table=kliendibaas_firma
@caption Registri number

@property pohitegevus type=relpicker reltype=RELTYPE_TEGEVUSALAD table=kliendibaas_firma
@caption Põhitegevus

property ettevotlusvorm type=relpicker reltype=RELTYPE_ETTEVOTLUSVORM table=kliendibaas_firma
caption Õiguslik vorm

@property ettevotlusvorm type=objpicker clid=CL_CRM_CORPFORM table=kliendibaas_firma
@caption Õiguslik vorm

@property tooted type=relpicker reltype=RELTYPE_TOOTED method=serialize field=meta table=objects
@caption Tooted

@property kaubamargid type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Kaubamärgid


@property tegevuse_kirjeldus type=textarea cols=65 rows=3 table=kliendibaas_firma
@caption Tegevuse kirjeldus

@property logo type=textbox size=40 method=serialize field=meta table=objects
@caption Organisatsiooni logo(url)

@property firmajuht type=chooser orient=vertical table=kliendibaas_firma  editonly=1
@caption Kontaktisik

@default group=humanres



@default group=contacts2
@property addresslist type=text store=no 
@caption Aadress

@property human_resources type=table store=no 
@caption Inimesed

@default group=cedit

@property contact type=relpicker reltype=RELTYPE_ADDRESS table=kliendibaas_firma
@caption Vaikimisi aadress


@property phone_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_PHONE props=name
@caption Telefon

@property url_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_URL props=name
@caption Veebiaadress

@property email_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_EMAIL props=mail
@caption E-posti aadressid

@property telefax_id type=relmanager table=kliendibaas_firma reltype=RELTYPE_TELEFAX props=name
@caption Faks

@default group=tasks_overview

@property tasks_call type=text store=no no_caption=1
@caption Kõned

@property org_add_call type=releditor reltype=RELTYPE_CALL props=name,comment,start1,is_done,content store=no
@caption Lisa kõne

@default group=overview

@property org_actions type=calendar no_caption=1 group=all_actions viewtype=relative
@caption org_actions

@property org_calls type=calendar no_caption=1 group=calls viewtype=relative
@caption Kõned

@property org_meetings type=calendar no_caption=1 group=meetings viewtype=relative
@caption Kohtumised

@property org_tasks type=calendar no_caption=1 group=tasks viewtype=relative
@caption Toimetused

@property t1 type=text subtitle=1 group=jobs store=no
@caption Aktiivsed

@property jobsact type=table no_caption=1 group=jobs

@property t2 type=text subtitle=1 group=jobs store=no
@caption Mitteaktiivsed

@property jobsnotact type=table no_caption=1 group=jobs

// disabled until the functionality is coded
//@property org_toolbar type=toolbar group=customers store=no no_caption=1
//@caption Org. toolbar

//@property customer type=table group=customers store=no no_caption=1
//@caption Kliendid

@groupinfo contacts caption="Kontaktid" 
@groupinfo contacts2 caption="Kontaktid" parent=contacts submit=no
@groupinfo cedit caption="Kontaktide muutmine" parent=contacts
groupinfo humanres caption="Inimesed" submit=no
@groupinfo overview caption="Tegevused" 
@groupinfo all_actions caption="Kõik" parent=overview submit=no
@groupinfo calls caption="Kõned" parent=overview submit=no
@groupinfo meetings caption="Kohtumised" parent=overview submit=no
@groupinfo tasks caption="Toimetused" parent=overview submit=no
@groupinfo tasks_overview caption="Ülevaade" parent=overview
@groupinfo personal caption="Personal"
@groupinfo jobs caption="Tööpakkumised" parent=personal
@groupinfo relorg caption="Seotud organisatsioonid"
@groupinfo customers caption="Kliendid" parent=relorg
@groupinfo fcustomers caption="Tulevased kliendid" parent=relorg
@groupinfo partners caption="Partnerid" parent=relorg
@groupinfo fpartners caption="Tulevased partnerid" parent=relorg
@groupinfo competitors caption="Konkurendid" parent=relorg

@reltype ETTEVOTLUSVORM value=1 clid=CL_CRM_CORPFORM
@caption Õiguslik vorm

@reltype ADDRESS value=3 clid=CL_CRM_ADDRESS
@caption Kontaktaadress

@reltype TEGEVUSALAD value=5 clid=CL_CRM_SECTOR
@caption Tegevusalad

@reltype TOOTED value=6 clid=CL_CRM_PRODUCT
@caption Tooted

@reltype CHILD_ORG value=7 clid=CL_CRM_COMPANY
@caption Tütar-organisatsioonid

@reltype WORKERS value=8 clid=CL_CRM_PERSON
@caption Töötajad

@reltype OFFER value=9 clid=CL_CRM_OFFER
@caption Pakkumine

@reltype DEAL value=10 clid=CL_CRM_DEAL
@caption Tehing

@reltype KOHTUMINE value=11 clid=CL_CRM_MEETING
@caption Kohtumine

@reltype CALL value=12 clid=CL_CRM_CALL
@caption Kõne

@reltype TASK value=13 clid=CL_TASK
@caption Toimetus

@reltype EMAIL value=15 clid=CL_ML_MEMBER
@caption E-post

@reltype URL value=16 clid=CL_EXTLINK
@caption Veebiaadress

@reltype PHONE value=17 clid=CL_CRM_PHONE
@caption Telefon

@reltype TELEFAX value=18 clid=CL_CRM_PHONE
@caption Fax

@reltype JOBS value=19 clid=CL_PERSONNEL_MANAGEMENT_JOB_OFFER
@caption T&ouml;&ouml;pakkumine

@reltype TOOPAKKUJA value=20 clid=CL_CRM_COMPANY
@caption Tööpakkuja

@reltype TOOTSIJA value=21 clid=CL_CRM_PERSON
@caption Tööotsija

@reltype CUSTOMER value=22 clid=CL_CRM_COMPANY
@caption Klient

@reltype POTENTIONAL_CUSTOMER value=23 clid=CL_CRM_COMPANY
@caption Tulevane klient

@reltype PARTNER value=24 clid=CL_CRM_COMPANY
@caption Partner

@reltype POTENTIONAL_PARTNER value=25 clid=CL_CRM_COMPANY
@caption Tulevate partner

@reltype COMPETITOR value=26 clid=CL_CRM_COMPANY
@caption Konkurent

@reltype ORDER value=27 clid=CL_SHOP_ORDER
@caption tellimus

@classinfo no_status=1
			
*/
/*
CREATE TABLE `kliendibaas_firma` (
  `oid` int(11) NOT NULL default '0',
  `firma_nim` varchar(255) default NULL,
  `reg_nr` varchar(20) default NULL,
  `ettevotlusvorm` int(11) default NULL,
  `pohitegevus` int(11) default NULL,
  `tegevuse_kirjeldus` text,
  `contact` int(11) default NULL,
  `firmajuht` int(11) default NULL,
  `korvaltegevused` text,
  `kaubamargid` text,
  `tooted` text,
  PRIMARY KEY  (`oid`),
  UNIQUE KEY `oid` (`oid`),
  KEY `teg_i` (`pohitegevus`)
) TYPE=MyISAM;
*/

class crm_company extends class_base
{
	function crm_company()
	{
		$this->init(array(
			'clid' => CL_CRM_COMPANY,
			'tpldir' => 'firma',
		));
	}
	
	function get_property($arr)
	{
		$data = &$arr['prop'];
		$retval = PROP_OK;
		switch($data['name'])
		{
			case "customer":
				$this->org_table(&$arr);
				break;

			case "org_toolbar":
				$vcl_inst = &$arr["prop"]["toolbar"];
				$vcl_inst->add_button(array(
					"name" => "delete",
					"img" => "delete.gif",
				));
				break;

			case "firmajuht":
				$conns = $arr["obj_inst"]->connections_from(array(
					"type" => RELTYPE_WORKERS,
				));
				foreach($conns as $conn)
				{
					$data["options"][$conn->prop("to")] = $conn->prop("to.name");
				};
				break;
			
			case "navtoolbar":
				$this->navtoolbar($arr);
				break;

			case "org_actions":
			case "org_calls":
			case "org_meetings":
			case "org_tasks":
				$this->do_org_actions(&$arr);
				break;

			case "human_resources":
				$this->do_human_resources($arr);
				break;
			case "tasks_call":
				$this->do_tasks_call($arr);
				break;

			case "addresslist":
				$this->do_addresslist($arr);
				break;
			case "jobsact":
				$this->do_jobslist($arr);
				break;
			case "jobsnotact":
				$this->do_jobs_notact_list($arr);
				break;
			
		};
		return $retval;
	}

	function callback_pre_edit($arr)
	{
		// initialize
		$pl = get_instance(CL_PLANNER);
		$this->cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));
	}
	
	function do_jobs_notact_list($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => "Ametikoht",
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "deadline",
			"caption" => "Tähtaeg",
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "kandideerijad",
			"caption" => "Kandidaadid",
			"sortable" => "1",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"caption" => "X",
		));
		
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_JOBS)) as $job)
		{
			$job = &obj($job->prop("to"));
			
			if($job->prop("deadline") < time())
			{
				$table->define_data(array(
					"ametikoht" => html::href(array(
						"caption" => $job->name(),
						"url" => $this->mk_my_orb("change", array("id" => $job->id()), "job_offer"),
				)),
					"deadline" => get_lc_date($job->prop("deadline"), LC_DATE_FORMAT_LONG_FULLYEAR),
					"kandideerijad" => html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $job->id(), "group" => "kandideerinud"), "job_offer"),
						"caption" => "Vaata kandidaate",
					)), 
				));
			}
		}
	}
	
	function do_jobslist($arr)
	{
		
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "ametikoht",
			"caption" => "Ametikoht",
			"sortable" => "1",
			"width" => "200",
		));
		
		$table->define_field(array(
			"name" => "deadline",
			"caption" => "Tähtaeg",
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "kandideerijad",
			"caption" => "Kandidaadid",
			"sortable" => "1",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"caption" => "X",
		));
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_JOBS)) as $job)
		{
			$job = &obj($job->prop("to"));
			
			if($job->prop("deadline")>time())
			{
				$table->define_data(array(
					"ametikoht" => html::href(array(
						"caption" => $job->name(),
						"url" => $this->mk_my_orb("change", array("id" => $job->id()), "job_offer"),
				)),
					"deadline" => get_lc_date($job->prop("deadline"), LC_DATE_FORMAT_LONG_FULLYEAR),
					"kandideerijad" => html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $job->id(), "group" => "kandideerinud"), "job_offer"),
						"caption" => "Vaata kandidaate",
					)), 
				));
			}
		}
		
	}
	
	function do_human_resources($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
                        'name' => 'name',
                        'caption' => 'Nimi',
                        'sortable' => '1',
			'callback' => array(&$this, 'callb_human_name'),
			'callb_pass_row' => true,
                ));
		$t->define_field(array(
                        'name' => 'phone',
                        'caption' => 'Telefon',
                        'sortable' => '1',
                ));
		$t->define_field(array(
                        'name' => 'email',
                        'caption' => 'E-post',
                        'sortable' => '1',
                ));
		$t->define_field(array(
                        'name' => 'rank',
                        'caption' => 'Ametinimetus',
                        'sortable' => '1',
                ));
		$t->define_field(array(
                        'name' => 'lastaction',
                        'caption' => 'Viimane tegevus',
                        'sortable' => '1',
                ));
		$t->define_field(array(
			'name' => 'new_call',
			'align' => 'center',
		));
		$t->define_field(array(
			'name' => 'new_meeting',
			'align' => 'center',
		));
		$t->define_field(array(
			'name' => 'new_task',
			'align' => 'center',
		));
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_WORKERS,
		));

		$crmp = get_instance(CL_CRM_PERSON);

		// http://intranet.automatweb.com/automatweb/orb.aw?class=planner&action=change&alias_to_org=87521&reltype_org=RELTYPE_ISIK_KOHTUMINE&id=46394&clid=224&group=add_event&title=Kohtumine:%20Anti%20Veeranna&parent=46398

		// to get those adding links work, I need 
		// 1. id of my calendar
		// 2. relation type
		// alias_to_org oleks isiku id
		// reltype_org oleks vastava seose id

		$pl = get_instance(CL_PLANNER);
		$cal_id = $pl->get_calendar_for_user();

		// XXX: I should check whether $this->cal_id exists and only include those entries
		// when it does.

		// call : rel=9 : clid=CL_CRM_CALL
		// meeting : rel=8 : clid=CL_CRM_MEETING
		// task : rel=10 : clid=CL_TASK
		foreach($conns as $conn)
		{
			$idat = $crmp->fetch_all_data($conn->prop("to"));
			$pdat = $crmp->fetch_person_by_id(array(
				"id" => $conn->prop("to"),
				"cal_id" => $cal_id,
			));

			$tdata = array(
				"name" => $conn->prop("to.name"),
				"id" => $conn->prop("to"),
				"phone" => $pdat["phone"],
				"rank" => $pdat["rank"],
			);

			if ($cal_id)
			{
				$tdata["email"] = html::href(array(
					"url" => "mailto:" . $pdat["email"],
					"caption" => $pdat["email"],
				));
				$tdata["new_task"] = html::href(array(
					"caption" => "Uus toimetus",
					"url" => $pdat["add_task_url"],
				));
				$tdata["new_call"] = html::href(array(
					"caption" => "Uus kõne",
					"url" => $pdat["add_call_url"],
				));
				$tdata["new_meeting"] = html::href(array(
					"caption" => "Uus kohtumine",
					"url" => $pdat["add_meeting_url"],
				));
			};

			$t->define_data($tdata);
		};

	}

	function callb_human_name($arr)
	{
		return html::href(array(
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["id"],
				"return_url" => urlencode(aw_global_get("REQUEST_URI")),
			),CL_CRM_PERSON),
			"caption" => $arr["name"],
		));
	}

	function do_tasks_call($arr)
	{
		$prop = &$arr["prop"];
		$obj = $arr["obj_inst"];
		$conns = $obj->connections_from(array(
			"type" => RELTYPE_CALL,
		));
		$rv = "";
		foreach($conns as $conn)
		{
			$target_obj = $conn->to();
			$inst = $target_obj->instance();
			if (method_exists($inst,"request_execute"))
			{
				$rv .= $inst->request_execute($target_obj);
			};
		};
		$prop["value"] = $rv;
	}
	
	function do_addresslist($arr)
	{
		$prop = &$arr["prop"];
		$obj = $arr["obj_inst"];
		$conns = $obj->connections_from(array(
			"type" => RELTYPE_ADDRESS,
		));
		$rv = "";
		foreach($conns as $conn)
		{
			$target_obj = $conn->to();
			$inst = $target_obj->instance();
			if (method_exists($inst,"request_execute"))
			{
				$rv .= $inst->request_execute($target_obj);
			};
		};
		$prop["value"] = $rv;
	}
	
	function do_org_actions($arr)
	{
		// whee, this thing includes project and that uses properties, so we gots
		// to do this here or something. damn, we need to do the reltype
		// loading in get_instance or something
		$cfgu = get_instance("cfg/cfgutils");
		$cfgu->load_class_properties(array(
			"file" => "project",
			"clid" => 239
		));

		$ob = $arr["obj_inst"];
		$args = array();
		switch($arr["prop"]["name"])
		{
			case "org_calls":
				$args["type"] = RELTYPE_CALL;
				break;
			
			case "org_meetings":
				$args["type"] = RELTYPE_KOHTUMINE;
				break;
			
			case "org_tasks":
				$args["type"] = RELTYPE_TASK;
				break;
		};
		$conns = $ob->connections_from($args);
		$t = &$arr["prop"]["vcl_inst"];

		$arr["prop"]["vcl_inst"]->configure(array(
			"overview_func" => array(&$this,"get_overview"),
		));

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => !empty($arr["request"]["viewtype"]) ? $arr["request"]["viewtype"] : $arr["prop"]["viewtype"],
		));

		$start = $range["start"];
		$end = $range["end"];

		$overview_start = $range["overview_start"];

		$classes = $this->cfg["classes"];

		$return_url = urlencode(aw_global_get("REQUEST_URI"));
		$planner = get_instance(CL_PLANNER);

		// gather a list of events to show
		$evts = array();

		// XXX: optimize the hell out of it. I have the range, I should use 
		// it.
		foreach($conns as $conn)
		{
			$evts[$conn->prop("to")] = $conn->prop("to");
		};

		$prj = get_instance(CL_PROJECT);
		$evts = $evts + $prj->get_events_for_participant(array(
			"id" => $arr["obj_inst"]->id(),
			"clid" => $this->relinfo[$args["type"]]["clid"],
		));

		$this->overview = array();
		classload("icons");

		foreach($evts as $obj_id)
		{
			$item = new object($obj_id);
			// relative needs last n and next m items, those might be 
			// outside of the current range
			if ($range["viewtype"] != "relative" && $item->prop("start1") < $overview_start)
			{
				continue;
			};
			
			$icon = icons::get_icon_url($item);
		
			$link = $planner->get_event_edit_link(array(
				"cal_id" => $this->cal_id,
				"event_id" => $item->id(),
				"return_url" => $return_url,
			));

			if ($item->prop("start1") > $start)
			{
				$t->add_item(array(
					"timestamp" => $item->prop("start1"),
					"data" => array(
						"name" => $item->name(),
						"link" => $link,
						"modifiedby" => $item->prop("modifiedby"),
						"icon" => $icon,
					),
				));
			};

			if ($item->prop("start1") > $overview_start)
			{
				$this->overview[$item->prop("start1")] = 1;
			};
		}
	}

	function get_overview($arr = array())
	{
		return $this->overview;
	}

	// Invoked when a connection is created from person to organization
	// .. this will then create the opposite connection.
	function on_connect_person_to_org($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_COMPANY)
		{
			$target_obj->connect(array(
				"to" => $conn->prop("from"),
				"reltype" => 8,
			));
		};
	}

	// Invoked when a connection from person to organization is removed
	// .. this will then remove the opposite connection as well
	function on_disconnect_person_from_org($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_COMPANY)
		{
			$target_obj->disconnect(array(
				"from" => $conn->prop("from"),
			));
		};
	}

	// If an event is added to a person, then this method
	// makes that event appear in any organization
	// calendars that the person has a "workplace" connection
	// with.
        function on_add_event_to_person($arr)
        {
                $event_obj = new object($arr["event_id"]);
                $typemap = array(
			CL_CRM_MEETING => 11,
                        CL_CRM_CALL => 12,
			CL_TASK => 13,
                );

                $reltype = $typemap[$event_obj->class_id()];
                if (empty($reltype))
                {
                        return false;
                };

                $per_obj = new object($arr["source_id"]);

                $conns = $per_obj->connections_to(array(
                        "type" => 8,
                ));

                foreach($conns as $conn)
                {
                        $org_obj = $conn->from();
                        $org_obj->connect(array(
                                "to" => $arr["event_id"],
                                "reltype" => $reltype,
                        ));
                }
        }

	
	function navtoolbar(&$args)
	{
		$toolbar = &$args["prop"]["toolbar"];
		$users = get_instance("users");

                $crm_db_id = $users->get_user_config(array(
                        "uid" => aw_global_get("uid"),
                        "key" => "kliendibaas",
                ));

		// hm, I dunno but there seems to be a conflict here. Because you set the folders
		// through the crm_db class, which means that they can be different for each user
		if (empty($crm_db_id))
		{
			$parents[RELTYPE_JOBS] = $parents[RELTYPE_ETTEVOTLUSVORM] = $parents[RELTYPE_WORKERS] = $parents[RELTYPE_ADDRESS] = $parents[RELTYPE_TEGEVUSALAD] = $args['obj_inst']->parent();
		}
		else
		{
			$crm_db = new object($crm_db_id);
			$default_dir = $crm_db->prop("dir_default");
			$parents[RELTYPE_ADDRESS] = $crm_db->prop("dir_address") == "" ? $default_dir : $crm_db->prop('dir_address');
			$parents[RELTYPE_TEGEVUSALAD] = $crm_db->prop("dir_tegevusala") == "" ? $default_dir : $crm_db->prop('dir_address');
			$parents[RELTYPE_WORKERS] = $crm_db->prop("dir_isik") == "" ? $default_dir : $crm_db->prop('dir_isik');
			$parents[RELTYPE_ETTEVOTLUSVORM] = $crm_db->prop("dir_ettevotlusvorm") == "" ? $default_dir : $crm_db->prop('dir_ettevotlusvorm');
		};

		if (!empty($this->cal_id))
		{
			$user_calendar = new object($this->cal_id);
			$parents[RELTYPE_CALL] = $parents[RELTYPE_OFFER] = $parents[RELTYPE_KOHTUMINE] = $parents[RELTYPE_DEAL] = $parents[RELTYPE_TASK] = $user_calendar->prop('event_folder');
		}

		$toolbar->add_menu_button(array(
			"name" => "main_menu",
			"tooltip" => "Uus",
		));

		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "calendar_sub",
			"text" => $this->cfg["classes"][CL_PLANNER]["name"],
		));
		
		$toolbar->add_sub_menu(array(
			"parent" => "main_menu",
			"name" => "firma_sub",
			"text" => $this->cfg["classes"][$this->clid]["name"],
		));

		$alist = array(RELTYPE_WORKERS,RELTYPE_TEGEVUSALAD,RELTYPE_ADDRESS,RELTYPE_ETTEVOTLUSVORM,RELTYPE_JOBS);
		foreach($alist as $key => $val)
		{
			$clids = $this->relinfo[$val]["clid"];
			if (is_array($clids))
			{
				foreach($clids as $clid)
				{
					$classinf = $this->cfg["classes"][$clid];

					$url = $this->mk_my_orb('new',array(
						'alias_to' => $args['obj_inst']->id(),
						'reltype' => $val,
						'title' => $classinf["name"].' : '.$args['obj_inst']->name(),
						'parent' => $parents[$val],
						'return_url' => urlencode(aw_global_get('REQUEST_URI')),
					),$clid);

					$has_parent = isset($parents[$val]) && $parents[$val];
					$disabled = $has_parent ? false : true;
					$toolbar->add_menu_item(array(
						"parent" => "firma_sub",
						"text" => 'Lisa '.$classinf["name"],
						"link" => $has_parent ? $url : "",
						"title" => $has_parent ? "" : "Kataloog määramata",
						"disabled" => $has_parent ? false : true,
					));
				};
			};
		};

		// aha, I need to figure out which objects can be added to that relation type

		// basically, I need to create a list of relation types that are of any
		// interest to me and then get a list of all classes for those

		$action = array(RELTYPE_OFFER,RELTYPE_DEAL,RELTYPE_KOHTUMINE,RELTYPE_CALL,RELTYPE_TASK);

		foreach($action as $key => $val)
		{
			$clids = $this->relinfo[$val]["clid"];
			$reltype = $this->relinfo[$val]["value"];
			if (is_array($clids))
			{
				foreach($clids as $clid)
				{
					$classinf = $this->cfg["classes"][$clid];
					$url = $this->mk_my_orb('new',array(
						// alright then. so what do those things to? 
						// they add a relation between the object created through
						// the planner and this object


						// can I do that with messages instead? and if I can, how
						// on earth am I going to do that?

						// I'm adding an event object to a calendar, how do I know
						// that I will have to attach it to an organization as well?
						
						// Maybe I should attach it directly to the organization and
						// then send a message somehow that it should be put in my
						// calendar as well .. hm that actually does sound
						// like a solution.
						'alias_to_org' => $args['obj_inst']->id(),
						'reltype_org' => $reltype,
						'class' => 'planner',
						'id' => $this->cal_id,
						'group' => 'add_event',
						'clid' => $clid,
						'action' => 'change',
						'title' => urlencode($classinf["name"].': '.$args['obj_inst']->name()),
						'parent' => $parents[$reltype],
						'return_url' => urlencode(aw_global_get('REQUEST_URI')),
					));
					$has_parent = isset($parents[$val]) && $parents[$val];
					$disabled = $has_parent ? false : true;
					$toolbar->add_menu_item(array(
						"parent" => "calendar_sub",
						"title" => $has_parent ? "" : "Kalender või kalendri sündmuste kataloog määramata",
						"text" => "Lisa ".$classinf["name"],
						"disabled" => $has_parent ? false : true,
						"link" => $has_parent ? $url : "",
					));
				};
			};
		};
			
		if (!empty($this->cal_id))	
		{
			$toolbar->add_button(array(
				"name" => "user_calendar",
				"tooltip" => "Kasutaja kalender",
				"url" => $this->mk_my_orb('change', array('id' => $this->cal_id,'return_url' => urlencode(aw_global_get('REQUEST_URI')),),'planner'),
				"onClick" => "",
				"img" => "icon_cal_today.gif",
				"class" => "menuButton",
			));
		}
		
	}

	////
	// !Listens to MSG_EVENT_ADD broadcasts and creates
	// connections between a CRM_PERSON and a CRM_COMPANY
	// if an event is added to a person.
	function register_humanres_event($arr)
	{
		$event_obj = new object($arr["event_id"]);
		$typemap = array(
			CL_CRM_CALL => 12,
			CL_TASK => 13,
			CL_CRM_MEETING => 11,
		);

		$reltype = $typemap[$event_obj->class_id()];
		if (empty($reltype))
		{
			return false;
		};

		$per_obj = new object($arr["person_id"]);

		$conns = $per_obj->connections_to(array(
			"type" => 8,
		));

		foreach($conns as $conn)
		{
			$org_obj = $conn->from();
			$org_obj->connect(array(
				"to" => $arr["event_id"],
				"reltype" => $reltype,
			));
		}
	}

	function org_table(&$arr)
	{
		$tf = &$arr["prop"]["vcl_inst"];
		$tf->define_field(array(
                        "name" => "name",
                        "caption" => "Organisatsioon",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "pohitegevus",
                        "caption" => "Põhitegevus",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "corpform",
                        "caption" => "Õiguslik vorm",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "address",
                        "caption" => "Aadress",
                        "sortable" => 1,
                ));
	
                $tf->define_field(array(
                        "name" => "email",
                        "caption" => "E-post",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "url",
                        "caption" => "WWW",
                        "sortable" => 1,
                ));
                $tf->define_field(array(
                        "name" => "phone",
                        "caption" => 'Telefon',
                ));

                $tf->define_field(array(
                        "name" => "ceo",
                        "caption" => "Juht",
                        "sortable" => 1,
                ));

		$tf->define_chooser(array(
                        "field" => "id",
                        "name" => "sel",
                ));

		$orgs = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_CUSTOMER,
		));
		
		foreach($orgs as $org)
		{
			$o = $org->to();
			// aga ülejäänud on kõik seosed!
			$vorm = $tegevus = $contact = $juht = $juht_id = $phone = $url = $mail = "";
			if (is_oid($o->prop("ettevotlusvorm")))
			{
				$tmp = new object($o->prop("ettevotlusvorm"));
				$vorm = $tmp->name();
			};

			if (is_oid($o->prop("pohitegevus")))
			{
				$tmp = new object($o->prop("pohitegevus"));
				$tegevus = $tmp->name();
			};
			
			if (is_oid($o->prop("contact")))
			{
				$tmp = new object($o->prop("contact"));
				$contact = $tmp->name();
			};

			if (is_oid($o->prop("firmajuht")))
			{
				$juht_obj = new object($o->prop("firmajuht"));
				$juht = $juht_obj->name();
				$juht_id = $juht_obj->id();
			};

			if (is_oid($o->prop("phone_id")))
			{
				$ph_obj = new object($o->prop("phone_id"));
				$phone = $ph_obj->name();
			};
			
			if (is_oid($o->prop("url_id")))
			{
				$url_obj = new object($o->prop("url_id"));
				$url = $url_obj->prop("url");
			};

			if (is_oid($o->prop("email_id")))
			{
				$mail_obj = new object($o->prop("email_id"));
				$mail = html::href(array(
					"url" => "mailto:" . $mail_obj->prop("mail"),
					"caption" => $mail_obj->prop("mail"),
				));

			};

			$tf->define_data(array(
				"id" => $o->id(),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $o->id(),
					),$o->class_id()),
					"caption" => $o->name(),
				)),
				"reg_nr" => $o->prop("reg_nr"),
				"pohitegevus" => $tegevus,
				"corpform" => $vorm,
				"address" => $contact,
				"ceo" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $juht_id,
					),CL_CRM_PERSON),
					"caption" => $juht,
				)),
				"phone" => $phone,
				"url" => html::href(array(
					"url" => $url,
					"caption" => $url,
				)),
				"email" => $mail,
			));
		}
	}



}
?>
