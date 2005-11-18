<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/task.aw,v 1.35 2005/11/18 14:00:48 kristo Exp $
// task.aw - TODO item
/*

@classinfo syslog_type=ST_TASK relationmgr=yes no_status=1 

@default table=objects
@default group=general

@property customer type=popup_search table=planner field=customer clid=CL_CRM_COMPANY style=relpicker reltype=RELTYPE_CUSTOMER
@caption Klient

@property code type=text size=5 table=planner field=code
@caption Kood

@property project type=popup_search table=planner field=project clid=CL_PROJECT style=relpicker reltype=RELTYPE_PROJECT
@caption Projekt


@property info_on_object type=text store=no
@caption Osalejad

@property is_done type=checkbox field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start table=planner
@caption Algus

@property end type=datetime_select table=planner 
@caption L&otilde;peb

@property deadline type=datetime_select table=planner field=deadline 
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

@property files type=text 
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

@default group=rows

	@property rows_tb type=toolbar store=no no_caption=1
	@property rows type=table store=no no_caption=1

@groupinfo rows caption=Read 
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

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption klient

@reltype PROJECT value=4 clid=CL_PROJECT
@caption projekt
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
			case "name":
				if (is_object($arr["obj_inst"]) && $data["value"] == "")
				{
					$data["value"] = $this->_get_default_name($arr["obj_inst"]);
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
				$p = array();
				if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
				{
					$conns = $arr['obj_inst']->connections_to(array(
						'type' => array(10, 8),//CRM_PERSON.RELTYPE_PERSON_TASK==10
					));
					foreach($conns as $conn)
					{
						$obj = $conn->from();
						$p[$obj->id()] = $obj->id();
					}
				}
				$data["options"] = $this->_get_possible_participants($arr["obj_inst"]);
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
				if (is_object($arr["obj_inst"]))
				{
					$data["value"] = html::href(array(
						"url" => $this->mk_my_orb("create_bill_from_task", array("id" => $arr["obj_inst"]->id(),"post_ru" => get_ru())),
						"caption" => t("Loo uus arve")
					));
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
				if ($this->can("view",$arr["request"]["alias_to_org"]))
				{
					$ol = new object_list(array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_ORDERER" => $arr["request"]["alias_to_org"],
					));
				}
				else
				if (is_object($arr["obj_inst"]) && $this->can("view", $arr["obj_inst"]->prop("customer")))
				{
					$ol = new object_list(array(
						"class_id" => CL_PROJECT,
						"CL_PROJECT.RELTYPE_ORDERER" => $arr["obj_inst"]->prop("customer"),
					));
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

				if (is_object($arr["obj_inst"]))
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
				if ($arr["request"]["alias_to_org"])
				{
					$data["value"] = $arr["request"]["alias_to_org"];
				}

				if (is_object($arr["obj_inst"]))
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
				if (is_object($arr["obj_inst"]))
				{
					$arr["obj_inst"]->set_prop("customer", $data["value"]);
				}
				break;

			case "other_expenses":
				$this->_other_expenses($arr);
				break;

			case "files":
				$this->_get_files($arr);
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
			case "rows":
				$res = array();
				foreach(safe_array($_POST["rows"]) as $e)
				{
					if ($e["task"] != "")
					{
						list($d,$m,$y) = explode("/", $e["date"]);
						$e["date"] = mktime(0,0,0, $m, $d, $y);
						if ($e["time_to_cust"] == "")
						{
							$e["time_to_cust"] = $e["time_real"];
						}
						foreach(safe_array($e["impl"]) as $i)
						{
							if ($this->can("view", $i))
							{
								$this->add_participant($arr["obj_inst"], obj($i));
							}
						}
						$res[] = $e;
					}
				}

				$arr["obj_inst"]->set_meta("rows", $res);
				break;

			case "files":
				$this->_set_files($arr);
				break;

			case "participants":
				if (!is_oid($arr["obj_inst"]->id()))
				{
					return PROP_IGNORE;
				}

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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_pre_save($arr)
	{
		if ($arr["obj_inst"]->name() == "")
		{
			$arr["obj_inst"]->set_name($this->_get_default_name($arr["obj_inst"]));
		}
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

	function _get_files($arr)
	{
		$objs = array();

		if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
		{
			$ol = new object_list($arr["obj_inst"]->connections_from(array(
				"type" => "RELTYPE_FILE"
			)));
			$objs = $ol->arr();
		}

		$objs[] = obj();

		$types = array(
			CL_FILE => t(""),
			CL_CRM_MEMO => t("Memo"),
			CL_CRM_DOCUMENT => t("CRM Dokument"),
			CL_CRM_DEAL => t("Leping"),
			CL_CRM_OFFER => t("Pakkumine")
		);

		$u = get_instance(CL_USER);
		$impl = $u->get_current_company();
		if ($this->can("view", $impl))
		{
			$f = get_instance("applications/crm/crm_company_docs_impl");
			$fldo = $f->_init_docs_fld(obj($impl));
			$ot = new object_tree(array(
				"parent" => $fldo->id(),
				"class_id" => CL_MENU
			));
			$folders = array($fldo->id() => $fldo->name());
			$this->_req_level = 0;
			$this->_req_get_folders($ot, $folders, $fldo->id());
		}
		else
		{
			$fldo = obj();
			$folders = array();
		}

		$clss = aw_ini_get("classes");
		foreach($objs as $idx => $o)
		{
			$this->vars(array(
				"name" => $o->name(),
				"idx" => $idx,
				"types" => $this->picker($types)
			));

			if (is_oid($o->id()))
			{
				$ff = $o->get_first_obj_by_reltype("RELTYPE_FILE");
				if (!$ff)
				{
					$ff = $o;
				}
				$fi = $ff->instance();
				$fu = html::href(array(
					"url" => $fi->get_url($ff->id(), $ff->name()),
					"caption" => $ff->name()
				));
				$data[] = array(
					"name" => html::get_change_url($o->id(), array("return_url" => get_ru()), $o->name()),
					"file" => $fu,
					"type" => $clss[$o->class_id()]["name"],
					"del" => html::href(array(
						"url" => $this->mk_my_orb("del_file_rel", array(
								"return_url" => get_ru(),
								"fid" => $o->id(),
						)),
						"caption" => t("Kustuta")
					)),
					"folder" => $o->path_str(array(
						"start_at" => $fldo->id(),
						"path_only" => true
					))
				);
			}
			else
			{
				$data[] = array(
					"name" => html::textbox(array(
						"name" => "fups_d[$idx][tx_name]"
					)),
					"file" => html::fileupload(array(
						"name" => "fups_".$idx
					)),
					"type" => html::select(array(
						"options" => $types,
						"name" => "fups_d[$idx][type]"
					)),
					"del" => "",
					"folder" => html::select(array(
						"name" => "fups_d[$idx][folder]",
						"options" => $folders
					))
				);
			}
		}

		classload("vcl/table");
		$t = new vcl_table(array(
			"layout" => "generic",
		));
		
		$t->define_field(array(
			"caption" => t("Nimi"),
			"name" => "name",
		));

		$t->define_field(array(
			"caption" => t("Fail"),
			"name" => "file",
		));

		$t->define_field(array(
			"caption" => t("T&uuml;&uuml;p"),
			"name" => "type",
		));

		$t->define_field(array(
			"caption" => t("Kataloog"),
			"name" => "folder",
		));

		$t->define_field(array(
			"caption" => t(""),
			"name" => "del",
		));

		foreach($data as $e)
		{
			$t->define_data($e);
		}

		$arr["prop"]["value"] = $t->draw();
	}

	function _set_files($arr)
	{
		$t = obj($arr["request"]["id"]);
		$u = get_instance(CL_USER);
		$co = obj($u->get_current_company());
		foreach(safe_array($_POST["fups_d"]) as $num => $entry)
		{
			if (is_uploaded_file($_FILES["fups_".$num]["tmp_name"]))
			{
				$f = get_instance("applications/crm/crm_company_docs_impl");
				$fldo = $f->_init_docs_fld($co);
				if ($this->can("add", $entry["folder"]))
				{
					$fldo = obj($entry["folder"]);
				}
				if (!$fldo)
				{
					return;
				}

				if ($entry["type"] == CL_FILE)
				{
					// add file
					$f = get_instance(CL_FILE);
					$fil = $f->add_upload_image("fups_$num", $fldo->id());
					if (is_array($fil))
					{
						$t->connect(array(
							"to" => $fil["id"],
							"reltype" => "RELTYPE_FILE"
						));
					}
				}
				else
				{
					$o = obj();
					$o->set_class_id($entry["type"]);
					$o->set_name($entry["tx_name"] != "" ? $entry["tx_name"] : $_FILES["fups_$num"]["name"]);

			
					$o->set_parent($fldo->id());
					if ($entry["type"] != CL_FILE)
					{
						$o->set_prop("project", $t->prop("project"));
						$o->set_prop("task", $t->id());
						$o->set_prop("customer", $t->prop("customer"));
					}
					$o->save();

					// add file
					$f = get_instance(CL_FILE);
					$fil = $f->add_upload_image("fups_$num", $o->id());
					if (is_array($fil))
					{
						$o->connect(array(
							"to" => $fil["id"],
							"reltype" => "RELTYPE_FILE"
						));
						$t->connect(array(
							"to" => $o->id(),
							"reltype" => "RELTYPE_FILE"
						));
					}
				}
			}
		}
		return $arr["post_ru"];
	}

	function _init_rows_t(&$t)
	{
		$t->define_field(array(
			"name" => "task",
			"caption" => t("Tegevus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "impl",
			"caption" => t("Teostaja"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "time_guess",
			"caption" => t("Prognoositud tunde"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "time_real",
			"caption" => t("Kulunud tunde"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "time_to_cust",
			"caption" => t("Tunde kliendile"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "done",
			"caption" => t("<a href='javascript:void(0)' onClick='aw_sel_chb(document.changeform,\"done\")'>Tehtud</a>"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "on_bill",
			"caption" => t("<a href='javascript:void(0)' onClick='aw_sel_chb(document.changeform,\"on_bill\")'>Arvele</a>"),
			"align" => "center"
		));

		/*$t->define_field(array(
			"name" => "com",
			"caption" => t("Kommentaar"),
			"align" => "center"
		));*/
	}

	function _rows($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_rows_t($t);

		$impls = $this->_get_possible_participants($arr["obj_inst"], true, $arr["obj_inst"]->prop("participants"));

		$u = get_instance(CL_USER);
		$def_impl = $u->get_current_person();
		$def_impl = array($def_impl => $def_impl);

		$dat = safe_array($arr["obj_inst"]->meta("rows"));
		$dat[] = array();
		$dat[] = array();
		$dat[] = array();
		foreach($dat as $idx => $row)
		{
			$date_sel = "<A HREF='#'  onClick=\"var cal=new CalendarPopup();cal.select(aw_get_el('rows[$idx][date]'),'anchor".$idx."','dd/MM/yy'); return false;\"
						   NAME='anchor".$idx."' ID='anchor".$idx."'>vali</A>";

			$t->define_data(array(
				"task" => "<a name='row_".$row["date"]."'/>".html::textarea(array(
					"name" => "rows[$idx][task]",
					"value" => $row["task"],
					"rows" => 5,
					"cols" => 50
				)),
				"date" => html::textbox(array(
					"name" => "rows[$idx][date]",
					"value" => date("d/m/y",($row["date"] > 100 ? $row["date"] : $arr["obj_inst"]->prop("start1"))),
					"size" => 7
				)).$date_sel,
				"impl" => html::select(array(
					"name" => "rows[$idx][impl]",
					"options" => $impls,
					"value" => (is_array($row["impl"]) && count($row["impl"])) ? $row["impl"] : $def_impl,
					"multiple" => 1
				)),
				"time_guess" => html::textbox(array(
					"name" => "rows[$idx][time_guess]",
					"value" => $row["time_guess"],
					"size" => 5
				)),
				"time_real" => html::textbox(array(
					"name" => "rows[$idx][time_real]",
					"value" => $row["time_real"],
					"size" => 5
				)),
				"time_to_cust" => html::textbox(array(
					"name" => "rows[$idx][time_to_cust]",
					"value" => $row["time_to_cust"],
					"size" => 5
				)),
				"done" => html::checkbox(array(
					"name" => "rows[$idx][done]",
					"value" => 1,
					"checked" => $row["done"]
				)),
				"on_bill" => html::checkbox(array(
					"name" => "rows[$idx][on_bill]",
					"value" => 1,
					"checked" => $row["on_bill"]
				)),
				/*"com" => html::textbox(array(
					"name" => "rows[$idx][com]",
					"value" => $row["com"],
				)),*/
			));
		}
		$t->set_sortable(false);
	}

	function get_task_bill_rows($task)
	{
		// check if task has rows defined that go on bill
		// if, then ret those
		// if not, return data for bill

		$rows = array();
		$dat = safe_array($task->meta("rows"));
		foreach($dat as $idx => $row)
		{
			if ($row["on_bill"] == 1)
			{
				$id = $task->id()."_".$idx;
				$rows[$id] = array(
					"name" => $row["task"],
					"unit" => t("tund"),
					"date" => $row["date"],
					"price" => $task->prop("hr_price"),
					"amt" => $row["time_to_cust"],
					"sum" => str_replace(",", ".", $row["time_to_cust"]) * $task->prop("hr_price"),
					"has_tax" => 1
				);
			}
		}

		if (!count($rows))
		{
			$rows[$task->id()] = array(
				"name" => $task->name(),
				"unit" => t("tund"),
				"price" => $task->prop("hr_price"),
				"date" => $task->prop("start1"),
				"amt" => $task->prop("num_hrs_to_cust"),
				"sum" => str_replace(",", ".", $task->prop("num_hrs_to_cust")) * $task->prop("hr_price"),
				"has_tax" => 1
			);
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
				"sum" => $oe["cost"],
				"has_tax" => 1
			);
		}
		

		return $rows;
	}

	/**
		@attrib name=del_file_rel
		@param fid required 
		@param from required
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
		$b = array(
			'name' => 'create_bill',
			'img' => 'save.gif',
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

	/**
		@attrib name=start_task_timer
		@param id required type=int acl=edit
		@param post_ru required
	**/
	function start_task_timer($arr)
	{
		$o = obj($arr["id"]);
		$o->set_meta("stopper_state", "started");
		$o->set_meta("stopper_start", time());
		$o->save();

		return $arr["post_ru"];
	}

	/**
		@attrib name=stop_task_timer
		@param id required type=int acl=edit
		@param post_ru required
	**/
	function stop_task_timer($arr)
	{
		$o = obj($arr["id"]);
		$o->set_meta("stopper_total", $o->meta("stopper_total") + (time() - $o->meta("stopper_start")));
		$o->set_meta("stopper_state", "");
		$o->set_meta("stopper_start", 0);
		$o->save();

		return $arr["post_ru"];
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
		if(is_object($arr['obj_inst']) && is_oid($arr['obj_inst']->id()))
		{
			$conns = $arr['obj_inst']->connections_to(array(
				'type' => array(10, 8),//CRM_PERSON.RELTYPE_PERSON_TASK==10
			));
			foreach($conns as $conn)
			{
				$obj = $conn->from();
				$opts[$obj->id()] = $obj->name();
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
}
?>
