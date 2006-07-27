<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_contestant.aw,v 1.6 2006/07/27 23:32:14 tarvo Exp $
// scm_contestant.aw - V&otilde;istleja 
/*

@classinfo syslog_type=ST_SCM_CONTESTANT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property contestant type=relpicker reltype=RELTYPE_CONTESTANT
@caption V&otilde;istleja

@property contestants_company type=text store=no editonly=1
@caption Firmast

@groupinfo register caption="Registreeri v&otilde;istlustele" submit=no
	@property reg_tbl type=table group=register no_caption=1
	@caption V&otilde;istluste tabel

	@property reg_button type=submit group=register
	@caption Registreeru

@groupinfo competitions caption="Minu v&otilde;istlused" submit=no
	
	@default group=competitions

	@property comp_tbl type=table no_caption=1
	@caption voistlused

	@property comp_caption type=text store=no
	@caption V&otilde;istlus

	@property sel_teams type=select store=no
	@caption Minu meeskonnad

	@property teams_submit type=submit
	@caption Salvesta

	@property unreg_button type=submit group=competitions
	@caption Eemalda registratsioon

@reltype CONTESTANT value=1 clid=CL_CRM_PERSON
@caption V&otilde;istleja

*/

class scm_contestant extends class_base
{
	function scm_contestant()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_contestant",
			"clid" => CL_SCM_CONTESTANT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "reg_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_tbl(&$t);
				// add special table fields
				$t->define_chooser(array(
					"name" => "reg",
					"field" => "register",
				));


				// insert data
				$comp = get_instance(CL_SCM_COMPETITION);
				$org = get_instance(CL_SCM_ORGANIZER);
				$filt = array(
					"contestant" => $arr["obj_inst"]->id(),
					"unregistered" => true,
				);
				foreach($comp->get_competitions($filt) as $oid => $obj)
				{
					$org_oid  = obj($comp->get_organizer(array("competition" => $obj->id())));
					$org_company = obj($org->get_organizer_company(array("organizer" => $org_oid)));
					
					$l_obj = obj($obj->prop("location"));
					$e_obj = obj($obj->prop("scm_event"));
					$t_obj = obj($obj->prop("scm_tournament"));
					
					$l_url = $this->mk_my_orb("change", array(
						"class" => "location",
						"id" => $l_obj->id(),
						"return_url" => get_ru(),
					));
					$e_url = $this->mk_my_orb("change", array(
						"class" => "scm_event",
						"id" => $e_obj->id(),
						"return_url" => get_ru(),
					));
					$t_url = $this->mk_my_orb("change", array(
						"class" => "scm_tournament",
						"id" => $t_obj->id(),
						"return_url" => get_ru(),
					));
					$c_url = $this->mk_my_orb("change", array(
						"class" => "scm_competition",
						"id" => $obj->id(),
						"return_url" => get_ru(),
					));

					$link = html::href(array(
						"url" => "%s",
						"caption" => 
							"%s",
					));

					$event = obj($comp->get_event(array("competition" => $oid)));
					$team = $event->prop("type");
					$team_str = ($team == "single")?t("Ei"):t("Jah");

					$t->define_data(array(
						"competition" => sprintf($link, $c_url, $obj->name()),
						"date" => date("d / m / Y", $obj->prop("date")),
						"register" => $obj->id(),
						"location" => sprintf($link, $l_url, $l_obj->name()),
						"event" => sprintf($link, $e_url, $e_obj->name()),
						"tournament" => sprintf($link, $t_url, $t_obj->name()),
						"organizer" => $org_company->name(),
						"team" => $team_str,
					));
				}
			break;

			case "comp_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_tbl(&$t);
				// add special table fields
				$t->define_chooser(array(
					"name" => "unreg",
					"field" => "unregister",
				));

				// insert data
				$filt = array(
					"contestant" => $arr["obj_inst"]->id(),
					"registered" => true,
				);
				$comp = get_instance(CL_SCM_COMPETITION);
				foreach($comp->get_competitions($filt) as $oid => $obj)
				{
					$event = obj($comp->get_event(array("competition" => $oid)));
					$team = $event->prop("type");
					if($team == "single")
					{
						$team_str = t("Ei");
					}
					else
					{
						$url = $this->mk_my_orb("change", array(
							"set_team" => $oid,
							"group" => $arr["request"]["group"],
							"id" => $arr["request"]["id"],
							"return_url" => $arr["request"]["return_url"],
						));
						$missing = "<font color=\"red\">".t("Meeskonnad m&auml;&auml;ramata")."</font>";
						// checks if there are any teams assigned
						$has_team = false;
						foreach($this->get_teams(array("contestant" => $arr["obj_inst"])) as $team)
						{
							$team_obj = obj($team);
							$sel_competitions = $team_obj->prop("competitions");
							if(in_array($oid, $sel_competitions))
							{
								$has_team = true;
								break;
							}
						}
						//
						$add = html::href(array(
							"url" => $url,
							"caption" => t("M&auml;&auml;ra meeskond"),
						));
						$team_str = t("Jah").($has_team?"":"<br/>".$missing)."<br/>".$add;
					}
					$t->define_data(array(
						"competition" => $obj->name(),
						"team" => $team_str,
						"unregister" => $oid,
					));
				}
			break;

			case "sel_teams":
				if(empty($arr["request"]["set_team"]))
				{
					return PROP_IGNORE;
				}
				$prop["name"] = "sel_teams[".$arr["request"]["set_team"]."]";
				$prop["options"][-1] = t("-Vali meeskond-");
				foreach($this->get_teams(array("contestant" => $arr["obj_inst"])) as $team)
				{
					$team_obj = obj($team);
					$sel_competitions = $team_obj->prop("competitions");
					if(in_array($arr["request"]["set_team"], $sel_competitions))
					{
						$prop["selected"][] = $team;
					}
					$prop["options"][$team] = $team_obj->name();
				}
			break;
			case "comp_caption":
				if(empty($arr["request"]["set_team"]))
				{
					return PROP_IGNORE;
				}
				$obj = obj($arr["request"]["set_team"]);
				$prop["value"] = $obj->name();
			break;
			case "teams_submit":
				if(empty($arr["request"]["set_team"]))
				{
					return PROP_IGNORE;
				}
			break;

			case "contestants_company":
				$o = obj($this->get_contestant_company(array("contestant" => $arr["obj_inst"]->id())));
				$prop["value"] = $o->name();
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
			case "reg_tbl":
				foreach($arr["request"]["reg"] as $oid)
				{
					$obj = obj($oid);
					$obj->connect(array(
						"to" => $arr["obj_inst"]->id(),
						"type" => "RELTYPE_CONTESTANT",
					));
				}
			break;

			case "sel_teams":
				if(!strlen($prop["value"]))
				{
					return PROP_IGNORE;
				}
				$all_teams = $this->get_teams(array("contestant" => $arr["obj_inst"]->id()));

				$competition = key($prop["value"]);
				$save_team = current($prop["value"]);
				unset($prop["value"][$competition][-1]);
				foreach($all_teams as $team)
				{
					$obj = obj($team);
					$comps = $obj->prop("competitions");
					if($team == $save_team && !in_array($competition, $comps))
					{
						$comps[] = $competition;
					}
					elseif($team != $save_team && in_array($competition, $comps))
					{
						foreach($comps as $k => $v)
						{
							if($v == $competition)
							{
								unset($comps[$k]);
							}
						}
					}
					$obj->set_prop("competitions", $comps);
					$obj->save();
				}
			break;

			case "comp_tbl":
				if(count($arr["request"]["unreg"]))
				{
					$conn = new connection();
					$conns = $conn->find(array(
						"to" => $arr["obj_inst"]->id(),
						"from" => $arr["request"]["unreg"],
						"type" => "6",
					));
					foreach($conns as $cid => $conn)
					{
						$cobj = new connection($cid);
						$cobj->delete();
					}
				}
			break;
		}
		return $retval;
	}	

	function callback_pre_save($arr)
	{
		$arr["obj_inst"]->set_name($arr["obj_inst"]->prop_str("contestant"));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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

	function _gen_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "competition",
			"caption" => t("V&otilde;istlus"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "event",
			"caption" => t("Spordiala"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "team",
			"caption" => t("Meeskondlik"),
			"sortable" => 1,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "location",
			"caption" => t("Toimumiskoht"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Toimumisaeg"),
			"sortable" => 1,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "organizer",
			"caption" => t("Korraldaja"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "tournament",
			"caption" => t("V&otilde;istlussari"),
			"sortable" => 1,
		));

	}

	/**
		@param contestant required type=int
			csm_contestant object id
		@comment
			fetches company
		@returns
			crm_company object id
	**/
	function get_contestant_company($arr = array())
	{
		$o = obj($this->get_contestant_person($arr));
		return ($s = $o->prop("work_contact"))?$s:false;
	}

	/**
		@param contestant required type=int
			csm_contestant object id
		@comment
			fetches person
		@returns
			crm_person object id
	**/
	function get_contestant_person($arr = array())
	{
		$obj = obj($arr["contestant"]);
		return ($s = $obj->prop("contestant"))?$s:false;
	}

	/**
		@attrib api=1
		@comment
			generates list of contestant objects
		@returns
			array of contestants.
			array(
				scm_contestant object_id
				scm_contestant object_inst
			)
	**/
	function get_contestants()
	{
		$list = new object_list(array(
			"class_id" => CL_SCM_CONTESTANT,
		));
		return $list->arr();
	}

	function get_teams($arr = array())
	{
		$obj = obj($arr["contestant"]);
		$teams = $obj->prop("teams");
		return $teams;
	}
}
?>
