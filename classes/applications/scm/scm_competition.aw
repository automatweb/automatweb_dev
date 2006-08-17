<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_competition.aw,v 1.12 2006/08/17 15:45:27 tarvo Exp $
// scm_competition.aw - V&otilde;istlus 
/*

@classinfo syslog_type=ST_SCM_COMPETITION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property relation_data type=hidden

@groupinfo sub_general caption="&Uuml;ldine" parent=general
	@default group=sub_general

	@property name type=textbox maxlength=255
	@caption Nimi

	@property scm_event type=relpicker reltype=RELTYPE_EVENT editonly=1
	@caption Spordiala

	@property location type=relpicker reltype=RELTYPE_LOCATION editonly=1
	@caption Asukoht

	@property date_from type=text
	@caption Algus

	@property date_to type=date_select
	@caption L&otilde;pp

	@property scm_tournament type=relpicker reltype=RELTYPE_TOURNAMENT editonly=1 multiple=1
	@caption V&otilde;istlussari

	@property scm_group type=relpicker reltype=RELTYPE_GROUP multiple=1 editonly=1
	@caption V&otilde;istlusgrupid

@groupinfo general_settings caption="M&auml;&auml;rangud" parent=general
	@default group=general_settings

	@property scm_score_calc type=relpicker reltype=RELTYPE_SCORE_CALC editonly=1
	@caption Punktis&uuml;steem

	@property scm_group_box type=textarea cols=50 rows=6
	@caption Gruppide lisainfo

	@property scm_group_consider type=textbox size=4
	@caption Igast grupist arvesse

	@property archive type=checkbox ch_value=1
	@caption Arhiveeritud

	@property register type=select
	@caption Registreerumine

	@property group_select_type type=chooser default=year
	@caption V&otilde;istlusklassi valik
	
	@property evt_team_result_calc type=select store=no 
	@caption V&otilde;istkonna tulemuse arvutus

	@property evt_result_type type=select store=no
	@caption Paremusj&auml;rjestuse t&uuml;&uuml;p

	@property uniqe_nr type=checkbox
	@caption Unikaalne rinnanumber

@groupinfo map_gr caption="Kaart" submit=no
	@property map type=text group=map_gr
	@caption Asukohakaart

@groupinfo photo_gr caption="Foto" submit=no
	@property photo type=text group=photo_gr
	@caption Pilt kohast

@groupinfo contestants caption="Osalejad" submit=no
	@groupinfo manage_groups caption="V&otilde;istkonnad" parent=contestants
		@default group=manage_groups

		@property search_res_team type=hidden name=search_result_teams store=no

		@property teams_tb type=toolbar no_caption=1

		@property teams type=table no_caption=1
		@caption Meeskondade nimekiri

	@groupinfo manage_contestants caption="V&otilde;istlejad" parent=contestants
		@default group=manage_contestants

		@property contestants_tb type=toolbar no_caption=1

		@property search_res type=hidden name=search_result no_caption=1 store=no

		@property contestants type=table no_caption=1
		@caption V&otilde;istlejate nimekiri

@groupinfo results caption="Tulemused" submit=no
	@groupinfo view_results parent=results caption="Tulemused" submit=no
		@property results_tbl type=table group=view_results no_caption=1
		@caption Tulemuste tabel

	@groupinfo add_results parent=results caption="Sisesta tulemused"
		@property add_results_tbl type=table group=add_results no_caption=1
		@caption Tulemuste lisamine

@reltype EVENT value=1 clid=CL_SCM_EVENT
@caption Spordiala

@reltype LOCATION value=2 clid=CL_LOCATION
@caption Asukoht

@reltype TOURNAMENT value=3 clid=CL_SCM_TOURNAMENT
@caption V&otilde;istlussari

@reltype SCORE_CALC value=4 clid=CL_SCM_SCORE_CALC
@caption Punktis&uuml;steem

@reltype GROUP value=5 clid=CL_SCM_GROUP
@caption V&otilde;istlusgrupp

@reltype CONTESTANT value=6 clid=CL_SCM_CONTESTANT,CL_SCM_TEAM
@caption V&otilde;istleja

*/

class scm_competition extends class_base
{
	function scm_competition()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_competition",
			"clid" => CL_SCM_COMPETITION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "map":
				$prop["value"] = $this->_get_image($arr);
			break;
			case "photo":
				$prop["value"] = $this->_get_image($arr);
			break;

			case "date_from":
				$ts = get_instance("vcl/date_edit");
				$name = array(
					"name" => "datetime_form",
					"size" => 11,
				);
				$ts->configure(array(
					"day" => "day",
					"month" => "month",
					"year" => "year",
					"hour" => "hour",
					"minute" => "minute",
				));
				$form = $ts->gen_edit_form($name, $arr["obj_inst"]->prop("date_from"));
				$prop["value"] = $form;
			break;

			case "teams_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name" => "add_team",
					"img" => "new.gif",
					"tooltip" => t("Lisa uus meeskond"),
					"url" => $this->mk_my_orb("prep_new_team", array(
						"id" => $arr["obj_inst"]->id(),
						"return_url" => get_ru(),
					)),
				));

				$popup_search = get_instance("vcl/popup_search");
				$search_butt = $popup_search->get_popup_search_link(array(
					"pn" => "search_result_teams",
					"clid" => CL_SCM_TEAM,
				));

				$tb->add_cdata($search_butt);
				$tb->add_button(array(
					"name" => "remove_team",
					"tooltip" => t("Eemalda meeskonnad"),
					"img" => "delete.gif",
					"action" => "unregister_team",
				));

				$this->_gen_groups_change_toolbar_addon(array(
					"tb" => &$tb,
					"competition" => $arr["obj_inst"]->id(),
				));
			break;

			case "teams":
				$evt = $this->get_event(array(
					"competition" => $arr["obj_inst"]->id(),
				));
				$e = $evt?obj($evt):false;
				if($e && $e->prop("type") == "single")
				{
					$prop["value"] = "<font color=\"#FF0000\">".t("V&otilde;istlus ei ole v&otilde;istkondlik")."</font>";
				}
				$t = &$prop["vcl_inst"];

				$team_inst = get_instance(CL_SCM_TEAM);
				$teams = $this->get_teams(array(
					"competition" => $arr["obj_inst"]->id(),
				));
				foreach($teams as $tid => $obj)
				{
				
					$extra_data = $this->get_extra_data(array(
						"competition" => $arr["obj_inst"]->id(),
						"team" => $tid,
					));
					$t_cid[$tid] = $extra_data["conn"]->id();
					$groups = array_flip($extra_data["data"]["groups"]);			
					foreach($groups as $gid => $null)
					{
						$n_groups[$tid][$gid] = call_user_method("prop", obj($gid), "abbreviation");
						$grps[$gid] = $n_groups[$tid][$gid];
					}
				}
				$this->_gen_teams_tbl(&$t, $grps);

				foreach($teams as $tid => $obj)
				{
					$memb = $team_inst->get_team_members(array(
						"team" => $tid,
						"competition" => $arr["obj_inst"]->id(),
					));
					$company = $this->_get_multi_company_name($memb);
					$url = $this->mk_my_orb("change", array(
						"class" => "scm_team",
						"id" => $tid,
						"return_url" => get_ru(),
					));
					$team_name = html::href(array(
						"caption" => $obj->name(),
						"url" => $url,
					));
					$t->define_data(array(
						"team" => $team_name." (<a href=\"#\">Liikmed</a>)",
						"selected" => $tid.".".$t_cid[$tid],
						"company" => $company,
						"groups" => $n_groups[$tid],
					));

				}

			break;
			case "contestants_tb":
				$tb = &$prop["vcl_inst"];
				$et = ($evt = $arr["obj_inst"]->prop("scm_event"))?call_user_method("prop", obj($evt), "type"):false;
				if($et == "single")
				{
					$tb->add_button(array(
						"name" => "add_contestant",
						"tooltip" => t("Lisa uus v&otilde;istleja"),
						"img" => "new.gif",
						"url" => $this->mk_my_orb("new", array(
							"class" => "scm_contestant",
							"parent" => $this->get_organizer(array("competitions" => ($id = $arr["obj_inst"]->id()))),
							"alias_to" => $id,
							"reltype" => 6,
							"return_url" => get_ru(),
						)),
					));
				}
				elseif($et == "multi" || $et == "multi_coll")
				{
					$tb->add_menu_button(array(
						"name" => "new",
						"img" => "new.gif",
						"tooltip" => t("Lisa uus v&otilde;istkonnaliige"),
					));
					$team_inst = get_instance(CL_SCM_TEAM);
					$teams = $team_inst->get_teams();
					foreach($teams as $oid => $obj)
					{
						$teams_n[$oid] = $obj->name();
					}
					asort($teams_n);
					foreach($teams_n as $oid => $name)
					{
						$tb->add_menu_item(array(
							"parent" => "new",
							"name" => "team.".$oid,
							"text" => $name,
							"url" => $this->mk_my_orb("add_contestant_to_team_and_register", array(
								"team" => $oid,
								"competition" => $arr["obj_inst"]->id(),
								"return_url" => get_ru(),
							)),
						));
					}
				}
				
				$evt = $this->get_event(array("competition" => $arr["obj_inst"]->id()));
				$e = $evt?obj($evt):false;
				$clid = ($e && $e->prop("type") == "single")?CL_SCM_CONTESTANT:CL_SCM_TEAM;
				if($clid == CL_SCM_CONTESTANT)
				{
					$popup_search = get_instance("vcl/popup_search");
					$search_butt = $popup_search->get_popup_search_link(array(
						"pn" => "search_result",
						"clid" => $clid,
					));

					$tb->add_cdata($search_butt);
				}
				$tb->add_button(array(
					"name" => "save_state",
					"tooltip" => t("Salvesta"),
					"img" => "save.gif",
					"url" => "#",
					"onClick" => "javascript:submit_changeform()",
				));
				$tb->add_button(array(
					"name" => "delete",
					"tooltip" => t("Eemalda v&otilde;istluselt"),
					"img" => "delete.gif",
					"action" => "unregister_cnt",
				));

				$this->_gen_groups_change_toolbar_addon(array(
					"tb" => &$tb,
					"competition" => $arr["obj_inst"]->id(),
				));
			break;
			case "contestants":
				$t = &$prop["vcl_inst"];
				$cont = get_instance(CL_SCM_CONTESTANT);
				$contestants = $this->get_contestants(array(
					"competition" => $arr["obj_inst"]->id(),
					"ret_inst" => true,
				));
				foreach($contestants as $oid => $data)
				{
					$t_oid = $data["data"]["team"];
					if($t_oid)
					{
						$filters["teams"][$t_oid] = call_user_method("name", obj($t_oid));
					}
					foreach($data["data"]["groups"] as $g_oid)
					{
						$filters["groups"][$g_oid] = call_user_method("prop", obj($g_oid), "abbreviation");
					}
				}
				$evt = $this->get_event(array("competition" => $arr["obj_inst"]->id()));
				$e = $evt?obj($evt):false;

				$team = ($e && $e->prop("type") == "single")?false:true;
				// gen table structure
				$this->_gen_cont_tbl(&$t, $filters, $team);
				// get event type (single, multi, multi_coll) 
				$event = $this->get_event(array(
					"competition" => $arr["obj_inst"]->id(),
				));
				$event_type = ($event)?call_user_method("prop", obj($event), "type"):false;


				foreach($contestants as $oid => $data)
				{
					// siin peaks välja raalima kas on olemas seoses võistlusklassi info, kui pole sis selle seosesse kirjutama
					$person = obj($cont->get_contestant_person(array("contestant" => $oid)));

					// gender
					if(!($sex = $person->prop("gender")))
					{
						$sex = ($pid)?(!($pid[0]&1)?1:2):false;
					}
					// date of birth
					if(($s = $person->prop("birthday")))
					{
						$dob = $s;
					}
					elseif(($pid = $person->prop("personal_id")))
					{
						$a = array(
							1 => 18,
							2 => 18,
							3 => 19,
							4 => 19,
							5 => 20,
							6 => 20,
						);
						$dob = mktime(0,0,0, substr($pid, 3, 2), substr($pid, 5, 2),$a[$pid[0]].substr($pid, 1, 2));
					}
					else
					{
						$dob = false;
					}
					
					// relation check 
					// whell... this basically does following:
					// checks if connection between competition and contestant has data that holds group info
					// if not, tries to figure out itself in which groups the contestant should be.. and puts them into these,
					// if needed, user can change the groups manually later...
					if(count($data["data"]["groups"]))
					{
						$groups = $data["data"]["groups"];
					}
					else
					{
						// siin peaks ta nüüd kudagi gruppidesse jaotama
						$cmp_groups = $this->get_groups(array(
							"competition" => $arr["obj_inst"]->id(),
						));
						unset($groups);
						foreach($cmp_groups as $group)
						{
							$o = obj($group);
							$ch = true;
							if(($s = $arr["obj_inst"]->prop("group_select_type")) == "year" && $dob)
							{
								$ch = (((date("Y", $dob) >= $o->prop("age_from")) || !$o->prop("age_from")) && ((date("Y", $dob) <= $o->prop("age_to")) || !$o->prop("age_to")))?$ch:false;
							}
							elseif($s == "age" || $dob)
							{
								$age = $this->get_age($dob);
								$ch = ((($age >= $o->prop("age_from") || !$o->prop("age_from"))) && ($age <= $o->prop("age_to") || !$o->prop("age_to")))?$ch:false;
							}
							$ch = (($o->prop("female") && $sex == 2) || ($o->prop("male") && $sex == 1))?$ch:false;
							if($ch)
							{
								$groups[] = $group;
							}
						}
						$data["data"]["groups"] = $groups;
						$data["connection"]->change(array(
							"data" => aw_serialize($data["data"], SERIALIZE_NATIVE),
						));
					}
					
					// group names
					unset($ngroups);
					foreach($groups as $group)
					{
						$ngroups[] = call_user_method("prop", obj($group), "abbreviation");
					}

					// finds out if he/she belongs to any team...
					if(strlen($data["data"]["team"]))
					{
						$team = obj($data["data"]["team"]);
					}

					// contacts
					if(($ph = $person->prop("phone")) || $person->prop("email"))
					{
						$ph = $ph?obj($ph):false;
						$em = ($em = $person->prop("email"))?obj($em):false;
						$contact = $ph?$ph->name()." (".$ph->prop("type").")":t("Telefoninumber puudub");
						$contact .= ",<br/>";
						$contact .= $em?$em->prop("mail"):t("E-mailiaadress puudub");
					}
					else
					{
						$contact = t("Andmed puuduvad");
					}
					$company = obj($cont->get_contestant_company(array(
						"contestant" => $oid
					)));

					$id = ($event_type == "multi" || $event_type == "multi_coll")?$data["id"]:$data["data"]["id"];

					// links
					$team_name = html::href(array(
						"caption" => $team->name(),
						"url" => $this->mk_my_orb("change", array(
							"class" => "scm_team",
							"id" => $team->id(),
							"return_url" => get_ru(),
						)),
					));
					$t->define_data(array(
						"name" => html::href(array(
							"caption" => $person->prop("lastname").", ".$person->prop("firstname"),
							"url" => $this->mk_my_orb("change", array(
								"class" => "scm_contestant",
								"id" => $oid,
								"return_url" => get_ru(),
							)),
						)),
						"company" => $company->name(),
						"sex" => (($s = $sex) == 1)?t("Mees"):(($s == 2)?t("Naine"):t("Sugu m&auml;&auml;ramata")),
						"birthday" => $dob,
						"team" => ($event_type == "multi" || $event_type == "multi_coll")?$team_name:t("-"),
						"groups" => $ngroups,
						"id" => $id.".".$oid.".".$data["connection"]->id(),
						"contact" => $contact,
						"sel_contestants" => $oid.".".$data["connection"]->id(),
					));
				}
				
			break;
			case "results_tbl":
				$t = &$prop["vcl_inst"];
				$event = obj($this->get_event(array("competition" => $arr["obj_inst"]->id())));
				$this->_gen_res_tbl(&$t, $event->prop("type"));
				$results = $this->fetch_results(array(
					"competition" => $arr["obj_inst"]->id(),
				));

				$event = $this->get_event(array("competition" => $arr["obj_inst"]->id()));
				$inst = get_instance(CL_SCM_EVENT);
				$res_type = $inst->get_result_type(array("event" => $event));

				$type_inst = get_instance(CL_SCM_RESULT_TYPE);
				$format = $type_inst->get_format(array("result_type" => $res_type));

				foreach($results as $data)
				{
					$data["result"] = $this->_gen_format_caption(array(
						"result" => $data["result_arr"],
						"format" => $format,
					));
					$t->define_data($data);
				}
			break;
			case "add_results_tbl":

				$archive = ($arr["obj_inst"]->prop("archive"));

				$t = &$prop["vcl_inst"];
				$event = $this->get_event(array("competition" => $arr["obj_inst"]->id()));
				$o = obj($event);
				$event_type = $o->prop("type");
				$this->_gen_add_res_tbl(&$t, $event_type);
				$conts = $this->get_contestants(array("competition" => $arr["obj_inst"]->id()));
				$inst = get_instance(CL_SCM_EVENT);
				$res_type = $inst->get_result_type(array("event" => $event));
				if(!$res_type)
				{
					return PROP_IGNORE;
				}

				$type_inst = get_instance(CL_SCM_RESULT_TYPE);
				$format = $type_inst->get_format(array("result_type" => $res_type));
				$res_inst = get_instance(CL_SCM_RESULT);

				// result field

				if($event_type == "multi_coll")
				{
					$results = $this->gen_list(array(
						"competition" => $arr["obj_inst"]->id(),
						"type" => "team",
					));
					$team_inst = get_instance(CL_SCM_TEAM);
					$cont_inst = get_instance(CL_SCM_CONTESTANT);
					foreach($results as $result)
					{
						$memb = $team_inst->get_team_members(array(
							"team" => $result["team"],
							"competition" => $arr["obj_inst"]->id(),
						));
						unset($team_companys);
						foreach($memb as $oid => $obj)
						{
							$team_companys[$cont_inst->get_contestant_company(array("contestant" => $oid))] = 1;
						}
						
						$company = (count($team_companys) > 1)?t("Segav&otilde;istkond"):call_user_method("name", obj(key($team_companys)));
						$format_nice = $this->_gen_format_nice(array(
							"format" => $format,
							"result" => $result,
							"team" => $result["team"],
							"competition" => $result["competition"],
						));
						$team_obj = obj($result["team"]);
						$team_name = $team_obj->name();
						$data[] = array(
							"team_name" => html::href(array(
								"caption" => $team_name,
								"url" => $this->mk_my_orb("change", array(
									"class" => "scm_team",
									"id" => $result["team"],
									"return_url" => get_ru(),
								)),
							)), 
							"company" => $company,
							"result" => $format_nice,
						);
					}
				}
				elseif($event_type == "multi")
				{
					$results = $this->gen_list(array(
						"competition" => $arr["obj_inst"]->id(),
						"type" => "contestant",
						"set_team" => true,
					));
					if(count($results))
					{
						foreach($results as  $result)
						{
							$format_nice = $this->_gen_format_nice(array(
								"format" => $format,
								"result" => $result,
								"contestant" => $result["contestant"],
								"competition" => $result["competition"],
								"disable" => $archive,
							));

							$cont = get_instance(CL_SCM_CONTESTANT);
							$person = obj($cont->get_contestant_person(array("contestant" => $result["contestant"])));
							$company = obj($cont->get_contestant_company(array("contestant" => $result["contestant"])));
							$data[] = array(
								"contestant" => $person->prop("lastname").", ".$person->prop("firstname"),
								"team_name" => $result["team"],
								"company" => $company->name(),
								"result" => $format_nice,
								"id" => $result["id"],
							);
						}
					}

				}
				elseif($event_type == "single")
				{
					$results = $this->gen_list(array(
						"competition" => $arr["obj_inst"]->id(),
						"set_contestant" => true,
						"type" => "contestant",
					));
					foreach($results as  $result)
					{
						$format_nice = $this->_gen_format_nice(array(
							"format" => $format,
							"result" => $result,
							"contestant" => $result["contestant"],
							"competition" => $result["competition"],
							"disable" => $archive,
						));

						$cont = get_instance(CL_SCM_CONTESTANT);
						$person = obj($cont->get_contestant_person(array("contestant" => $result["contestant"])));
						$company = obj($cont->get_contestant_company(array("contestant" => $result["contestant"])));
						$p_name = $person->prop("lastname").", ".$person->prop("firstname");
						$data[] = array(
							"contestant" => html::href(array(
								"caption" => $p_name,
								"url" => $this->mk_my_orb("change", array(
									"class" => "scm_contestant",
									"id" => $person->id(),
									"return_url" => get_ru(),
								)),
							)),
							"company" => $company->name(),
							"result" => $format_nice,
							"id" => $result["id"],
						);
					}
				}
				foreach($data as $row)
				{
					$t->define_data($row);
				}
			break;

			case "register":
				$prop["options"] = array(
					"0" => t("Avalik"),
					"1" => t("Piiratud"),
					"2" => t("Registreerumine l&otilde;ppenud"),
				);
			break;

			case "group_select_type":
				$prop["options"] = array(
					"year" => t("S&uuml;nniaasta alusel"),
					"age" => t("Vanuse alusel"),
				);
			break;

			case "evt_result_type":
				$o = obj($arr["obj_inst"]->id());
				if(!$o->prop("scm_event"))
				{
					return PROP_IGNORE;
				}
				$prop["options"] = array(
					"tere" => "tere",
				);
			break;

			case "evt_team_result_calc":
				$o = obj($arr["obj_inst"]->id());
				if(!($event = $o->prop("scm_event")))
				{
					return PROP_IGNORE;
				}
				elseif(($type = call_user_method("prop", obj($event), "type")) == "single" || $type == "multi_coll")
				{
					return PROP_IGNORE;
				}
			break;
			// to override original name prop
			case "name":
				$prop["value"] = $arr["obj_inst"]->name();
			break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
			case "add_results_tbl":
				$res_type = get_instance(CL_SCM_RESULT_TYPE);
				$res = get_instance(CL_SCM_RESULT);
				foreach($arr["request"]["res_contestant"] as $result => $data)
				{
					$obj = obj($result);
					$rev = $res_type->format_data(array(
						"source" => "result",
						"oid" => $result,
						"data" => $data,
						"reverse" => true,
					));
					$obj->set_prop("result", $rev);
					$obj->save();
				}
				foreach($arr["request"]["res_new_contestant"] as $competition => $tmp)
				{
					foreach($tmp as $contestant => $data)
					{
						$rev = $res_type->format_data(array(
							"source" => "competition",
							"oid" => $competition,
							"data" => $data,
							"reverse" => true,
						));
						$id = $res->add_result(array(
							"competition" => $competition,
							"contestant" => $contestant,
							"result" => $rev,
						));
					}
				}
				foreach($arr["request"]["res_team"] as $competition => $data)
				{
					foreach($data as $team => $result_data)
					{
						//siin on nüüd olemas result object.. ja team
						// ühesõnaga ma saan team'i liikmed kätte.. ja neil on selle võistlusega vb juba tulemus kellegil olemas, ma pean välja raalima kas on ja kui on siis apdeitima.. kui pole sis uue tegema. IMEB!!
						// a seda ka et vähemalt ühel liikmel on olemas result objek.. mudu ta siia üldse ei jõuaks, vist!!
						$inst = get_instance(CL_SCM_TEAM);
						$members = $inst->get_team_members(array(
							"team" => $team,
							"competition" => $competition,
						));
						foreach($members as $oid => $obj)
						{
							$list = new object_list(array(
								"class_id" => CL_SCM_RESULT,
								"CL_SCM_RESULT.RELTYPE_CONTESTANT" => $oid,
								"CL_SCM_RESULT.RELTYPE_COMPETITION" => $competition,
							));
							if($list->count())
							{
								// ehk siin on siis see koht kus on välja raalitud et result on olemas ja tulemus updeiditakse
								$obj = $list->begin();;
								$rev = $res_type->format_data(array(
									"source" => "result",
									"oid" => $obj->id(),
									"data" => $result_data,
									"reverse" => true,
								));
								$id = current($list->ids());
								$obj = obj($id);
								$obj->set_prop("result", $rev);
								$obj->set_prop("team", $team);
								$obj->save();
							}
							else
							{
								// tuleb teha uus(teoorias ei tohiks asi siia üldse jõuda, sest result objekt peaks juba olemas olema!!)
								$rev = $res_type->format_data(array(
									"source" => "competition",
									"oid" => $competition,
									"data" => $result_data,
									"reverse" => true,
								));
								$id = $res->add_result(array(
									"competition" => $competition,
									"contestant" => $oid,
									"team" => $team,
									"result" => $rev,
								));
							}
						}

					}
				}
				foreach($arr["request"]["res_new_team"] as $competition => $data)
				{
					foreach($data as $team => $result)
					{
						$inst = get_instance(CL_SCM_TEAM);
						foreach($inst->get_team_members(array("team" => $team)) as $oid => $obj)
						{
							$rev = $res_type->format_data(array(
								"source" => "competition",
								"oid" => $competition,
								"data" => $result,
								"reverse" => true,
							));
							$id = $res->add_result(array(
								"competition" => $competition,
								"contestant" => $oid,
								"team" => $team,
								"result" => $rev,
							));
						}
					}
				}
			break;
			case "name":
				$arr["obj_inst"]->set_name($prop["value"]);
			break;
		}
		return $retval;
	}	
	
	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_pre_save($arr)
	{
		// getting event_type 
		$et = ($evt = $arr["obj_inst"]->prop("scm_event"))?call_user_method("prop", obj($evt), "type"):false;

		// checking and setting dha start and end times of dha compeititon
		if(($from = $arr["request"]["datetime_form"]) && ($date_to = $arr["request"]["date_to"]))
		{
			$start = mktime($from["hour"], $from["minute"], 0, $from["month"], $from["day"], $from["year"]);
			if($start > ($end = mktime(23, 59, 59, $date_to["month"], $date_to["day"], $date_to["year"])))
			{
				$end = mktime(23, 59, 59, $from["month"], $from["day"], $from["year"]);
			}
			$arr["obj_inst"]->set_prop("date_from", $start);
			$arr["obj_inst"]->set_prop("date_to", $end);
		}

		// new contestants added through search popup
		if($arr["request"]["search_result"])
		{
			$this->_add_contestant_from_search(&$arr);
		}

		// here we save new contestant id's
		$this->_update_contestant_ids(&$arr, $et);

		// registering new teams to competition 
		if($arr["request"]["search_result_teams"])
		{
			$this->_register_teams(&$arr);
		}

	}



	function _add_contestant_from_search($arr)
	{
		$res = split(",", $arr["request"]["search_result"]);
		// loops over every selected contestant and connects them to competition
		foreach($res as $contestant)
		{
			$data = array(
				"contestant" => $contestant,
				"competition" => $arr["obj_inst"]->id(),
			);
			$arr["obj_inst"]->connect(array(
				"to" => $contestant,
				"type" => "RELTYPE_CONTESTANT",
				"extra" => aw_serialize($data, SERIALIZE_NATIVE),
			));
		}
	}

	function _update_contestant_ids($arr, $et = "single")
	{
		foreach($arr["request"]["contestant_ids"] as $relid => $ids)
		{
			$c = new connection($relid);
			$data = aw_unserialize($c->prop("data"));
			if($et == "single")
			{
				$data["id"] = reset($ids);
			}
			else
			{
				foreach($ids as $c_oid => $c_id)
				{
					$data["members"][$c_oid] = $c_id;
				}
			}
			$c->change(array(
				"data" => aw_serialize($data, SERIALIZE_NATIVE),
			));
		}

	}

	function _register_teams($arr)
	{
		$spl = split(",", $arr["request"]["search_result_teams"]);
		foreach($spl as $team)
		{
			unset($data);
			$t_inst = get_instance(CL_SCM_TEAM);
			$t_inst->register_team(array(
				"team" => $team,
				"competition" => $arr["id"],
			));
		}
	}

	function get_rel_data($rel)
	{
		return array(
			"data" => aw_unserialize(call_user_method("prop", ($conn = new connection($rel)), "data")),
			"conn" => $conn,
		);
	}

	function save_rel_data($arr)
	{
		$arr["conn"]->change(array(
			"data" => aw_serialize($arr["data"], SERIALIZE_NATIVE),
		));
	}

	/**
		@comment
			fetches results, calculates team results, sorts them in right order and bla bla bla
	**/
	function fetch_results($arr)
	{
		$event_id = $this->get_event(array("competition" => $arr["competition"]));
		$event = obj($event_id);
		$event_type = $event->prop("type");
		$result_inst = get_instance(CL_SCM_RESULT);
		$cnt_inst = get_instance(CL_SCM_CONTESTANT);
		$team_inst = get_instance(CL_SCM_TEAM);
		switch($event_type)
		{
			case "single":
				$res = $this->gen_list(array(
					"competition" => $arr["competition"],
					"type" => "contestant",
					"set_groups" => true,
				));
				foreach($res as $result)
				{
					$to_calc[$result["contestant"]] = $result["raw_result"];
				}
				$pos_and_point = $this->_get_places_and_points(array(
					"data" => $to_calc,
					"competition" => $arr["competition"],
				));
				foreach($res as $result)
				{
					$pers = obj($cnt_inst->get_contestant_person(array(
						"contestant" => $result["contestant"],
					)));
					$cont_url = $this->mk_my_orb("change", array(
						"class" => "scm_contestant",
						"id" => $result["contestant"],
						"return_url" => get_ru(),
					));
					$ret[$pos_and_point[$result["contestant"]]["place"]] = array(
						"contestant" => html::href(array(
							"caption" => $pers->prop("lastname").", ".$pers->prop("firstname"),
							"url" => $cont_url,
						)),
						"company" => call_user_method("name", obj($cnt_inst->get_contestant_company(array(
						"contestant" => $result["contestant"],
						)))),
						"points" => $pos_and_point[$result["contestant"]]["points"],
						"place" => $pos_and_point[$result["contestant"]]["place"],
						"result" => $result["raw_result"],
						"result_arr" => $result["result"],
						"id" => strlen($result["id"])?$result["id"]:t("-"),

					);
				}
			break;

			case "multi":
				$res = $this->gen_list(array(
					"competition" => $arr["competition"],
					"set_team" => true,
					"type" => "contestant",
				));
				if(count($res))
				{
					foreach($res as $result)
					{
						$team_res[$result["team_oid"]][] = $result;
						$team_raw[$result["team_oid"]][] = $result["raw_result"];
					}
					$event = get_instance(CL_SCM_EVENT);
					$calc_fun = $event->get_team_result_calc_fun(array("event" => $event_id));
					foreach($team_raw as $team_oid => $results)
					{
						$team_tot_raw[$team_oid] = $event->$calc_fun($results);
					}
					$pos_and_point = $this->_get_places_and_points(array(
						"data" => $team_tot_raw,
						"competition" => $arr["competition"],
					));
					foreach($res as $result)
					{

						$memb = $team_inst->get_team_members(array(
							"team" => $result["team_oid"],
							"competition" => $arr["competition"],
						));
						$company = $this->_get_multi_company_name($memb);	

						$team = obj($result["team_oid"]);
						$ret[$pos_and_point[$result["team_oid"]]["place"]] = array(
							"team_name" => html::href(array(
								"caption" => $team->name(),
								"url" => $this->mk_my_orb("change", array(
									"class" => "scm_team",
									"id" => $result["team_oid"],
									"return_url" => get_ru(),
								)),
							)),
							"points" => $pos_and_point[$result["team_oid"]]["points"],
							"place" => $pos_and_point[$result["team_oid"]]["place"],
							"result" => $result["raw_result"],
							"result_arr" => $result["result"],
							"company" => $company,
						);
					}
				}
			break;

			case "multi_coll":
				$res = $this->gen_list(array(
					"competition" => $arr["competition"],
					"type" => "team",
				));
				foreach($res as $result)
				{
					$to_calc[$result["team"]] = $result["raw_result"];
				}
				$pos_and_point = $this->_get_places_and_points(array(
					"data" => $to_calc,
					"competition" => $arr["competition"],
				));
				foreach($res as $result)
				{

					$memb = $team_inst->get_team_members(array(
						"team" => $result["team_oid"],
						"competition" => $arr["competition"],
					));
					$company = $this->_get_multi_company_name($memb);

					$team = obj($result["team"]);
					$ret[$pos_and_point[$result["team"]]["place"]] = array(
						"team_name" => html::href(array(
							"caption" => $team->name(),
							"url" => $this->mk_my_orb("change", array(
								"class" => "scm_team",
								"id" => $result["team"],
								"return_url" => get_ru(),
							)),
						)),
						"points" => $pos_and_point[$result["team"]]["points"],
						"place" => $pos_and_point[$result["team"]]["place"],
						"result" => $result["raw_result"],
						"result_arr" => $result["result"],
						"company" => $company,
					);
				}

			break;
		}
		return $ret;
	}

	function _get_places_and_points($arr = array())
	{
		$score_calc = get_instance(CL_SCM_SCORE_CALC);
		$comp = obj($arr["competition"]);
		$res = $score_calc->calc_results(array(
			"data" => $arr["data"],
			"score_calc" => $comp->prop("scm_score_calc"),
			"competition" => $arr["competition"],
		));
		return $res;
	}
	function _get_image($arr)
	{
		$loc_id = $arr["obj_inst"]->prop("location");
		
		if(!$loc_id)
		{
			return t("Võistluse toimumise asukoht m&auml;&auml;ramata");
		}
		$loc = obj($loc_id);
		$img_inst = get_instance(CL_IMAGE);
		$img_id = $loc->prop($arr["prop"]["name"]);
		if(!$img_id)
		{
			return t("Pilt on m&auml;&auml;ramata");
		}
		$img_inf = $img_inst->get_image_by_id($img_id);
		return html::img(array(
			"url" => $img_inf["url"],
		));
	}

	function _gen_cont_tbl($t, $filters = array(), $team = true)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("V&otilde;istleja"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "company",
			"caption" => t("Firma"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "sex",
			"caption" => t("Sugu"),
			"sortable" => true,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "birthday",
			"caption" => t("S&uuml;nniaeg"),
			"sortable" => true,
			"callback" => array(&$this, "__dob_format"),
			"align" => "center",
		));
		if($team)
		{
			$t->define_field(array(
				"name" => "team",
				"caption" => t("Meeskond"),
				"sortable" => true,
				"align" => "center",
				"filter" => $filters["teams"],
				"filter_compare" => array(&$this, "__team_filter"),
			));
		}
		$t->define_field(array(
			"name" => "groups",
			"caption" => t("V&otilde;istlusklassid"),
			"filter" => $filters["groups"],
			"filter_compare" => array(&$this, "__group_filter"),
			"callback" => array(&$this, "__group_format"),
		));
		$t->define_field(array(
			"name" => "id",
			"caption" => t("S&auml;rgi number"),
			"callback" => array(&$this, "__id_textbox"),
			"sortable" => true,
			"numeric" => true,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "contact",
			"caption" => t("Kontaktandmed"),
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "sel_contestants",
		));

	}
	
	function __team_filter($key, $str, $foo)
	{
		return ($foo["team"] == $str)?true:false;
	}

	function __group_format($key)
	{
		return (is_array($key) && count($key))?join(", ", $key):t("V&otilde;istlusklass m&auml;&auml;ramata");
	}

	function __group_filter($key, $str, $foo)
	{
		return in_array($str, $foo["groups"]);
	}

	function __dob_format($str)
	{
		return date("d / m / Y", $str)." (".$this->get_age($str)."a)";
	}

	function __id_textbox($str)
	{
		$str = split("[.]", $str);
		$html = html::textbox(array(
			"name" => "contestant_ids[".$str[2]."][".$str[1]."]",
			"value" => $str[0],
			"size" => 4,
		));
		return $html;
	}

	function _gen_res_tbl($t, $type = "single")
	{
		if($type == "single")
		{
			$t->define_field(array(
				"name" => "contestant",
				"caption" => t("V&otilde;istleja"),
				"sortable" => true,
			));
			$t->define_field(array(
				"name" => "id",
				"caption" => t("V&otilde;istleja nr"),
				"sortable" => true,
				"align" => "center",
			));
		}
		if($type == "multi" || $type == "multi_coll")
		{
			$t->define_field(array(
				"name" => "team_name",
				"caption" => t("Meeskond"),
				"sortable" => true,
			));
		}

		$t->define_field(array(
			"name" => "company",
			"caption" => t("Firma"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "result",
			"caption" => t("Tulemus"),
		));

		$t->define_field(array(
			"name" => "group",
			"caption" => t("V&otilde;istlusklassid"),
			"align" => "center",
			"filter" => array(
				1 => "essa",
			),
		));

		$t->define_field(array(
			"name" => "group_place",
			"caption" => t("Koht v&otilde;istlusklassis"),
			"sortable" => true,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "place",
			"caption" => t("Koht"),
			"sortable" => true,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "points",
			"caption" => t("Punkte"),
			"align" => "center",
		));
		$t->set_default_sortby("place");

	}

	function _gen_add_res_tbl($t, $event_type = "single")
	{
		if($event_type == "single" || $event_type == "multi")
		{
			$t->define_field(array(
				"name" => "contestant",
				"caption" => t("V&otilde;istleja"),
				"sortable" => 1,
			));
			$t->define_field(array(
				"name" => "id",
				"caption" => t("V&otilde;istleja nr"),
				"sortable" => true,
				"align" => "center",
			));
		}
		if($event_type == "multi" || $event_type == "multi_coll")
		{
			$t->define_field(array(
				"name" => "team_name",
				"caption" => t("V&otilde;istkond"),
				"sortable" => 1,
			));
		}
		$t->define_field(array(
			"name" => "company",
			"caption" => t("Firma"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "result",
			"caption" => t("Tulemus"),
		));

	}

	function _gen_teams_tbl($t, $groups)
	{
		$t->define_field(array(
			"name" => "team",
			"caption" => t("Meeskond"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "company",
			"caption" => t("Firma"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "groups",
			"caption" => t("V&otilde;istlusklassid"),
			"filter" => $groups,
			"filter_compare" => array(&$this, "__group_filter"),
			"callback" => array(&$this, "__group_format"),
		));
		$t->define_chooser(array(
			"name" => "selector",
			"field" => "selected",
		));
	}
	
	function _comp_list_callback(&$o, $list)
	{
		$list->remove($o->id());
	}

	
	/**
		@attrib params=name
		@param competition required type=oid
		@param team optional type=oid
		@param contestant optional type=oid
		@comment
			fetches extra data from connection.
			at least of the optional parameters(team,contestant) must be set.
		@returns
			false if parameters are wrong or if any connections isn't found.
			else array in exact same format as #get_rel_data ,
			so you can save the same array with #save_rel_data
	**/
	function get_extra_data($arr)
	{
		if(!$arr["competition"] || (!$arr["team"] && !$arr["contestant"]))
		{
			return false;
		}
		$to = strlen($arr["team"])?$arr["team"]:$arr["contestant"];
		$c = new connection();
		$conns = $c->find(array(
			"from" => $arr["competition"],
			"to" => $to,
			"type" => 6,
		));
		if($conns && count($conns))
		{
			return $this->get_rel_data(key($conns));
		}
		else
		{
			return false;
		}
	}
	/**
		@attrib params=name api=1
		@param registered optional type=bool
			if set to true, only these competitions will be returned where $contestant has signed in
		@param unregistered optional type=bool
			if set to true, only these competitions will be returned where $contestant hasn't signed in.
		@param contestant optional type=oid
			this is for using with $registered and $unregistered, sets the contestant who's competitions will be returned.
		@param organizer optional type=oid
			returns only the competitions created by given organizer
		@param state type=string
			3 options:
			+ archive
			+ current
			+ all(default)
		@comment
			generates list of competitions.
		@returns
			array of competitions:
			array(
				scm_competition oid,
				scm_competition object_inst,
			)
			
	**/
	function get_competitions($arr = array())
	{
		$state = strlen($arr["state"])?$arr["state"]:0;
		$ol_filt["class_id"] = CL_SCM_COMPETITION;
		if($arr["state"] == "archive")
		{
			$ol_filt["archive"] = 1;
		}
		elseif($arr["state"] == "current")
		{
			$ol_filt["archive"] = 0;
		}
		$ol_filt["CL_SCM_COMPETITION.RELTYPE_CONTESTANT"] = ($arr["contestant"])?$arr["contestant"]:NULL;
		$list = new object_list($ol_filt);
		/*
		if(strlen($arr["contestant"]) && ($arr["unregistered"] || $arr["registered"]))
		{
			foreach($list->arr() as $oid => $obj)
			{
				$conns = $obj->connections_from(array(
					"type" => "RELTYPE_CONTESTANT",
				));
				unset($persons);
				foreach($conns as $con)
				{
					if($con->conn["to"] == $arr["contestant"] && $arr["unregistered"])
					{
						$list->remove($oid);
						break;
					}
					$persons[] = $con->conn["to"];
				}
				if($arr["registered"] && !in_array($arr["contestant"], $persons))
				{
					$list->remove($oid);
				}
			}
		}
		*/
		if(strlen($arr["organizer"]))
		{
			$obj = obj($arr["organizer"]);
			$conns = $obj->connections_from(array(
				"type" => "RELTYPE_COMPETITION",
				"class" => CL_SCM_COMPETITION,
			));
			foreach($conns as $data)
			{
				$o = $data->to();
				$orgs[] = $o->id();;
			}
			foreach($list->arr() as $oid => $obj)
			{
				if(!in_array($oid, $orgs))
				{
					$list->remove($oid);
				}
			}
		}
		return $list->arr();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//

	/**
		@attrib params=name api=1
		@param competition required type=int
			the competition object id
		@comment
			fetches the organizer objects name
		@returns
			scm_organizer oid for given competition
	**/
	function get_organizer($arr = array())
	{
		$conn = new connection();
		$conns = $conn->find(array(
			"from.class_id" => CL_SCM_ORGANIZER,
			"to" => $arr["competition"],
		));
		if(!count($conns))
		{
			return false; // no organizer connected to competition
		}
		$org = current($conns);
		return $org["from"];
	}

	/**
		@attrib params=name api=1
		@param competition required type=int
			the competitions object id
		@param ret_inst optional type=bool
			returns connection instance
		@comment
			fetches contestants for given competition
		@returns
			returns array of scm_contestant object id's who are registred to this competition
			array(
				object_id => object_name
			)
	**/
	function get_contestants($arr = array())
	{
		if(!$arr["competition"])
		{
			return false;
		}
		$event = ($evt = call_user_method("prop", obj($arr["competition"]), "scm_event"))? obj($evt):false;
		$t = $event?$event->prop("type"):false;
		if($t && ($t == "single"))
		{
			$conn = new connection();
			$conns = $conn->find(array(
				"from" => $arr["competition"],
				"to.class_id" => CL_SCM_CONTESTANT,
				"type" => 6,
			));
			foreach($conns as $id => $data)
			{
				$res[$data["to"]] = array(
					"obj" => $arr["ret_inst"]?obj($data["to"]):NULL,
					"data" => aw_unserialize($data["data"]),
					"connection" => new connection($id),
				);
			}
		}
		elseif($t && ($t == "multi" || $t == "multi_coll"))
		{
			// so, i have to find teams that are registred, and team members registred to this competition
			$conn = new connection();
			$conns = $conn->find(array(
				"from" => $arr["competition"],
				"to.class_id" => CL_SCM_TEAM,
				"type" => 6,
			));
			foreach($conns as $id => $data)
			{
				$extra_data = aw_unserialize($data["data"]);
				foreach($extra_data["members"] as $member => $members_id)
				{
					$res[$member] = array(
						"obj" => $arr["ret_inst"]?obj($member):NULL,
						"data" => aw_unserialize($data["data"]),
						"connection" => new connection($id),
						"team" => $data["to"],
						"id" => $members_id,
					);
					$res[$member]["data"]["team"] = $data["to"];
				}
				// here should be data concerning registered members, their id's.. teams group .. etc
				// array(
				// groups = array(
				//	CL_SCM_GROUP oid
				// )
				// members = array(
				//	CL_SCM_CONTESTANT oid => shirt_id,
				// )
				// anything else?
			}
		}
		else
		{
			return false;
		}
		return $res;
	}

	/**
		@attrib params=name api=1
		@param competition required type=int
			competitions object id
		@comment
			gets event for given competition
		@returns
			event object id
	**/
	function get_event($arr = array())
	{
		$o = obj($arr["competition"]);
		return ($s = $o->prop("scm_event"))?$s:false;
	}
	
	function _gen_groups_change_toolbar_addon($arr)
	{
		$tb = &$arr["tb"];
		$competition = $arr["competition"];
		$options = $this->_gen_class_list(array("competition" => $competition));
		$options[0] = t("-Vali v&otilde;istlusklass-");
		asort($options);

		$select = html::select(array(
			"name" => "tb_select_option",
			"options" => $options,
		));
		$select2 = html::select(array(
			"name" => "tb_select_action",
			"options" => array(
				1 => ($t = $arr["caption"]["assign"])?$t:t("M&auml;&auml;ra v&otilde;istlejad klassi"),
				2 => ($t = $arr["caption"]["unassign"])?$t:t("Eemalda v&otilde;istlejad klassist"),
			),
		));
		$tb->add_cdata($select2, true);
		$tb->add_cdata($select, true);
		$tb->add_button(array(
			"name" => "save2",
			"img" => "prog_20.gif",
			"side" => true,
			"action" => "change_grp",
		));
	}

	function _gen_format_caption($arr)
	{
		$result = $arr["result"];
		foreach($arr["format"] as $name => $caption)
		{
			$ret[] = $result[$name]?$result[$name]." ".$caption:NULL;
		}

		return join(" ", $ret);
	}

	/**
	**/
	function _gen_format_nice($arr)
	{
		$result = $arr["result"];
		$type = (strlen($arr["team"])?"team":"contestant");
		// formaadi väljad käikase läbi ja tekitatakse input'id
		if($type == "team")
		{
			$for_team = "[".$arr["team"]."]";
		}
		else
		{
			$for_cont = "[".$arr["contestant"]."]";
		}
		foreach($arr["format"] as $name => $caption)
		{
			if(strlen($result["result_oid"]))
			{
				$textbox["name"] = "res_".$type."[".(($type == "team")?$arr["competition"]:$result["result_oid"])."]".$for_team."[".$name."]";
			}
			else
			{
				$textbox["name"] = "res_new_".$type."[".$arr["competition"]."]".$for_team.$for_cont."[".$name."]";
			}
			$textbox["size"] = "4";
			$textbox["value"] = $result["result"][$name];
			$textbox["disabled"] = $arr["disable"];
			$res .= html::textbox($textbox);
			$res .= $caption."&nbsp;&nbsp;";
		}
		return $res;
	}
	

	/**
	**/
	function get_location($arr = array())
	{
		$obj = obj($arr["competition"]);
		return ($s = ($obj->prop("location")))?$s:false;
	}

	function get_date($arr = array())
	{
		$obj = obj($arr["competition"]);
		return $obj->prop("date");
	}

	/**
	**/
	function get_groups($arr)
	{
		$obj = obj($arr["competition"]);
		return ($s = $obj->prop("scm_group"))?$s:false;
	}

	function get_age($from, $to = "")
	{
		$birthday_rec = getdate($from);
		$now_rec = getdate($to?$to:time());
		$age = $now_rec["year"] - $birthday_rec["year"];
		$age = ($now_rec["mon"] < $birthday_rec["mon"])?$age--:(($now_rec["mday"] <= $birthday_rec["mday"])?$age--:$age);
		return $age;
	}

	function _gen_class_list($arr)
	{
		$o = obj($arr["competition"]);
		foreach($o->prop("scm_group") as $group)
		{
			$o = obj($group);
			$ret[$group] = $o->prop("abbreviation");
		}
		return $ret;
	}

	/**
		@comment
			takes basically same arguments what scm_result::get_results
	**/
	function gen_list($arr)
	{
		$res_inst = get_instance(CL_SCM_RESULT);
		if($arr["type"] == "team")
		{
			$arr["set_team"] = true;
			$ret = $res_inst->get_results($arr);
		}
		else
		{
			foreach($this->get_contestants($arr) as $oid => $data)
			{
				$arr["contestant"] = $oid;
				$res = $res_inst->get_results($arr);
				$ret[] = current($res);
			}
		}

		return $ret;
	}

	function get_teams($arr)
	{
		$c = new connection();
		$conns = $c->find(array(
			"from" => $arr["competition"],
			"to.class_id" => CL_SCM_TEAM,
			"type" => 6,
		));
		foreach($conns as $cid => $data)
		{
			$ret[$data["to"]] = obj($data["to"]);
		}
		return $ret;
	}

	/**
		@param members required type=array
			array(
				CL_SCM_CONTESTANT oid => CL_SCM_CONTESTANT object
			)
		@comment
			finds out in which company contestants work
		@returns
			if all work in one company, returns its name.
			if members are in different company's, returns 'segavõistkond'.
			if argument has 0 members, according text is returned
	**/
	function _get_multi_company_name($memb)
	{
		$cnt_inst = get_instance(CL_SCM_CONTESTANT);
		if(!count($memb))
		{
			return t("&Uuml;htegi liiget pole registreerunud");
		}
		foreach($memb as $oid => $obj)
		{
			$team_companys[$cnt_inst->get_contestant_company(array("contestant" => $oid))] = 1;
		}
		$company = (count($team_companys) > 1)?t("Segav&otilde;istkond"):call_user_method("name", obj(key($team_companys)));
		return $company;
	}

	/**
		@attrib name=change_grp params=name all_args=1 default=0
	**/
	function change_grp($arr)
	{
		$arr["sel"] = strlen($arr["selector"])?$arr["selector"]:$arr["sel"];
		$group = $arr["tb_select_option"];
		if(!$group || !count($arr["sel"]))
		{
			return $arr["post_ru"];
		}
		$action = $arr["tb_select_action"];
		$sel = $arr["sel"];
		$et = ($evt = call_user_method("prop", obj($arr["id"]), "scm_event"))?(call_user_method("prop", obj($evt), "type")):false;
		// for individual competitions
		if($et && $et == "single")
		{
			foreach($sel as $oid_and_id)
			{
				list($oid, $cid) = split("[.]", $oid_and_id);

				$data = $this->get_rel_data($cid);
				if($action  == 1)
				{
					$data["data"]["groups"][] = $group;
					$data["data"]["groups"] = array_unique($data["data"]["groups"]);
				}
				else
				{
					$flp = array_flip($data["data"]["groups"]);
					unset($flp[$group]);
					$data["data"]["groups"] = array_flip($flp);
				}
				$this->save_rel_data($data);
			}
		}
		elseif($et && ($et == "multi" || $et == "multi_coll"))
		{
			// have to figure out teams.. 
			foreach($sel as $oid_and_id)
			{
				list($oid, $cid) = split("[.]", $oid_and_id);
				$obj = obj($oid);
				// this figures out from where the change came from(from teams tab or from contestants tab)
				if($obj->class_id() == CL_SCM_TEAM)
				{
					$teams[] = $oid;
				}
				else
				{
					$data = $this->get_rel_data($cid);
					$teams[] = $data["data"]["team"];
				}
			}
			$teams = array_unique($teams);
			$conn = new connection();
			foreach($teams as $team)
			{
				$conns = $conn->find(array(
					"from" => $arr["id"],
					"to" => $team,
					"type" => 6,
				));
				$data = $this->get_rel_data(key($conns));
				if($action == 1)
				{
					$data["data"]["groups"][] = $group;
					$data["data"]["groups"] = array_unique($data["data"]["groups"]);
				}
				else
				{
					$flp = array_flip($data["data"]["groups"]);
					unset($flp[$group]);
					$data["data"]["groups"] = array_flip($flp);
				}
				$this->save_rel_data($data);
			}
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=unregister_cnt params=name all_args=1 default=0
	**/
	function unregister_cnt($arr)
	{
		// get the event type
		$et = ($evt = call_user_method("prop", obj($arr["id"]), "scm_event"))?(call_user_method("prop", obj($evt), "type")):false;
		if($et == "single")
		{
			$obj = obj($arr["id"]);
			foreach($arr["sel"] as $id)
			{
				list($oid, $cid) = split("[.]", $id);
				$obj->disconnect(array(
					"from" => $oid,
				));
			}
		}
		elseif($et == "multi" || $et == "multi_coll")
		{
			foreach($arr["sel"] as $id)
			{
				list($oid, $cid) = split("[.]", $id);
				$data = $this->get_rel_data($cid);
				foreach($data["data"]["members"] as $member => $id)
				{
					if($member == $oid)
					{
						unset($data["data"]["members"][$member]);
					}
				}
				$this->save_rel_data($data);
			}
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=unregister_team all_args=1 params=name
	**/
	function unregister_team($arr)
	{
		$obj = obj($arr["id"]);
		foreach($arr["selector"] as $id)
		{
			list($gid, $cid) = split("[.]", $id);
			$obj->disconnect(array(
				"from" => $gid,
			));
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=prep_new_team all_args=1 params=name
	**/
	function prep_new_team($arr)
	{
		$obj = obj();
		$obj->set_class_id(CL_SCM_TEAM);
		$org = $this->get_organizer(array(
			"competition" => $arr["id"],
		));
		$obj->set_parent($org);
		$id = $obj->save_new();
		$t_inst = get_instance(CL_SCM_TEAM);
		$t_inst->register_team(array(
			"competition" => $arr["id"],
			"team" => $id,
		));

		$url = $this->mk_my_orb("change", array(
			"class" => "scm_team",
			"id" => $id,
			"return_url" => $arr["return_url"],
		));
		return $url;
	}

	/**
		@attrib name=add_contestant_to_team_and_register all_args=1 params=name
	**/
	function add_contestant_to_team_and_register($arr)
	{
		// making new contestant
		$org = $this->get_organizer(array(
			"competition" => $arr["competition"],
		));
		$obj = obj();
		$obj->set_parent($org);
		$obj->set_class_id(CL_SCM_CONTESTANT);
		$cnt_id = $obj->save_new();
		
		// connecting contestant to team
		$t_obj = obj($arr["team"]);
		$t_obj->connect(array(
			"to" => $cnt_id,
			"type" => 2,
		));

		// checking if team is already registered, if not, registers team.
		$c = new connection();
		$conns = $c->find(array(
			"from" => $arr["competition"],
			"to" => $arr["team"],
			"type" => 6,
		));
		if(count($conns))
		{
			$data = $this->get_rel_data(key($conns));
			$data["data"]["members"][$cnt_id] = "";
			$this->save_rel_data($data);
		}
		else
		{
			// register this team to competition
			$team_inst = get_instance(CL_SCM_TEAM);
			$team_inst->register_team(array(
				"team" => $arr["team"],
				"competition" => $arr["competition"],
				"members" => array(
					$cnt_id => obj($cnt_id),
				),
			));
		}

		$url = $this->mk_my_orb("change", array(
			"class" => "scm_contestant",
			"id" => $cnt_id,
			"return_url" => $arr["return_url"],
		));
		return $url;
	}
}
?>
