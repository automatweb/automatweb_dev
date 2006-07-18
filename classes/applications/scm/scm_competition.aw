<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_competition.aw,v 1.5 2006/07/18 06:05:17 tarvo Exp $
// scm_competition.aw - V&otilde;istlus 
/*

@classinfo syslog_type=ST_SCM_COMPETITION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize


@property scm_event type=relpicker reltype=RELTYPE_EVENT editonly=1
@caption Spordiala

@property location type=relpicker reltype=RELTYPE_LOCATION editonly=1
@caption Asukoht

@property date type=date_select 
@caption Kuup&auml;ev

@property scm_tournament type=relpicker reltype=RELTYPE_TOURNAMENT editonly=1
@caption V&otilde;istlussari

@property scm_score_calc type=relpicker reltype=RELTYPE_SCORE_CALC editonly=1
@caption Punktis&uuml;steem

@property scm_group type=relpicker reltype=RELTYPE_GROUP multiple=1 editonly=1
@caption V&otilde;istlusgrupid

@property scm_group_box type=textarea cols=50 rows=6
@caption Gruppide lisainfo

@property scm_group_consider type=textbox size=4
@caption Igast grupist arvesse

@groupinfo map_gr caption="Kaart" submit=no
	@property map type=text group=map_gr
	@caption Asukohakaart

@groupinfo photo_gr caption="Foto" submit=no
	@property photo type=text group=photo_gr
	@caption Pilt kohast

@groupinfo contestants caption="V&otilde;istlejad" submit=no
	@property list type=table group=contestants no_caption=1
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

@reltype CONTESTANT value=6 clid=CL_SCM_CONTESTANT
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

			case "list":
				$t = &$prop["vcl_inst"];
				$this->_gen_cont_tbl(&$t);
				foreach($this->get_contestants(array("competition" => $arr["obj_inst"]->id())) as $oid => $name)
				{
					$cont = get_instance(CL_SCM_CONTESTANT);
					$person = obj($cont->get_contestant_person(array("contestant" => $oid)));
					$company = obj($cont->get_contestant_company(array("contestant" => $oid)));
					$t->define_data(array(
						"name" => $person->name(),
						"company" => $company->name(),
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
				foreach($results as $data)
				{
					$t->define_data($data);
				}
			break;
			case "add_results_tbl":
				$t = &$prop["vcl_inst"];
				$event = $this->get_event(array("competition" => $arr["obj_inst"]->id()));
				$o = obj($event);
				$event_type = $o->prop("type");
				$this->_gen_add_res_tbl(&$t, $event_type);
				$conts = $this->get_contestants(array("competition" => $arr["obj_inst"]->id()));
				$inst = get_instance(CL_SCM_EVENT);
				$res_type = $inst->get_result_type(array("event" => $event));

				$type_inst = get_instance(CL_SCM_RESULT_TYPE);
				$format = $type_inst->get_format(array("result_type" => $res_type));
				$res_inst = get_instance(CL_SCM_RESULT);

				// result field

				if($event_type == "multi_coll")
				{
					$results = $res_inst->get_teams_results(array(
						"competition" => $arr["obj_inst"]->id(),
					));
					$team_inst = get_instance(CL_SCM_TEAM);
					$cont_inst = get_instance(CL_SCM_CONTESTANT);
					foreach($results as $result)
					{
						$memb = $team_inst->get_team_members(array("team" => $result["team"]));
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
						$data[] = array(
							"team_name" => $team_obj->name(),
							"company" => $company,
							"result" => $format_nice,
						);
					}
				}
				elseif($event_type == "multi")
				{
					$results = $res_inst->get_results(array(
						"competition" => $arr["obj_inst"]->id(),
						"type" => "contestant",
						"set_team" => true,
					));
					foreach($results as  $result)
					{
						$format_nice = $this->_gen_format_nice(array(
							"format" => $format,
							"result" => $result,
							"contestant" => $result["contestant"],
							"competition" => $result["competition"],
						));

						$cont = get_instance(CL_SCM_CONTESTANT);
						$person = obj($cont->get_contestant_person(array("contestant" => $result["contestant"])));
						$company = obj($cont->get_contestant_company(array("contestant" => $result["contestant"])));
						$data[] = array(
							"contestant" => $person->name(),
							"team_name" => $result["team"],
							"company" => $company->name(),
							"result" => $format_nice,
						);
					}

				}
				elseif($event_type == "single")
				{
					$results = $res_inst->get_results(array(
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
						));

						$cont = get_instance(CL_SCM_CONTESTANT);
						$person = obj($cont->get_contestant_person(array("contestant" => $result["contestant"])));
						$company = obj($cont->get_contestant_company(array("contestant" => $result["contestant"])));
						$data[] = array(
							"contestant" => $person->name(),
							"company" => $company->name(),
							"result" => $format_nice,
						);
					}
				}
				foreach($data as $row)
				{
					$t->define_data($row);
				}
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
				/*
				arr($arr);
				return PROP_IGNORE;
				*/
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
						foreach($inst->get_team_members(array("team" => $team)) as $oid => $obj)
						{
							$list = new object_list(array(
								"class_id" => CL_SCM_RESULT,
								"CL_SCM_RESULT.RELTYPE_CONTESTANT" => $oid,
								"CL_SCM_RESULT.RELTYPE_COMPETITION" => $competition,
							));
							if($list->count() == 1)
							{
								// ehk siin on siis see koht kus on välja raalitud et result on olemas ja tulemus updeiditakse
								$obj = obj($result);
								$rev = $res_type->format_data(array(
									"source" => "result",
									"oid" => $result,
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
								// tuleb teha uus
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
		}
		return $retval;
	}	
	
	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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
		switch($event_type)
		{
			case "single":
				$res = $result_inst->get_results(array(
					"competition" => $arr["competition"],
					"type" => "contestant",
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
					$cont = obj($result["contestant"]);
					$ret[$pos_and_point[$result["contestant"]]["place"]] = array(
						"contestant" => $cont->name(),
						"points" => $pos_and_point[$result["contestant"]]["points"],
						"place" => $pos_and_point[$result["contestant"]]["place"],
						"result" => $result["raw_result"],
					);
				}
			break;

			case "multi":
				$res = $result_inst->get_results(array(
					"competition" => $arr["competition"],
					"set_team" => true,
					"type" => "contestant",
				));
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
					$team = obj($result["team_oid"]);
					$ret[$pos_and_point[$result["team_oid"]]["place"]] = array(
						"team_name" => $team->name(),
						"points" => $pos_and_point[$result["team_oid"]]["points"],
						"place" => $pos_and_point[$result["team_oid"]]["place"],
						"result" => $result["raw_result"],
					);
				}
			break;

			case "multi_coll":
				$res = $result_inst->get_teams_results(array(
					"competition" => $arr["competition"],
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
					$team = obj($result["team"]);
					$ret[$pos_and_point[$result["team"]]["place"]] = array(
						"team_name" => $team->name(),
						"points" => $pos_and_point[$result["team"]]["points"],
						"place" => $pos_and_point[$result["team"]]["place"],
						"result" => $result["raw_result"],
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

	function _gen_cont_tbl($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("V&otilde;istleja"),
		));
		$t->define_field(array(
			"name" => "company",
			"caption" => t("Firma"),
		));

	}

	function _gen_res_tbl($t, $type = "single")
	{
		if($type == "single")
		{
			$t->define_field(array(
				"name" => "contestant",
				"caption" => t("V&otilde;istleja"),
			));
		}
		if($type == "multi" || $type == "multi_coll")
		{
			$t->define_field(array(
				"name" => "team_name",
				"caption" => t("Meeskond"),
			));
		}
		$t->define_field(array(
			"name" => "company",
			"caption" => t("Firma"),
		));
		$t->define_field(array(
			"name" => "result",
			"caption" => t("Tulemus"),
		));
		$t->define_field(array(
			"name" => "place",
			"caption" => t("Koht"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "points",
			"caption" => t("Punkte"),
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

	function _comp_list_callback(&$o, $list)
	{
		$list->remove($o->id());
	}

	/**
		@attrib params=name api=1
		@param registered optional type=bool
			if set to true, only these competitions will be returned where $contestant has signed in
		@param unregistered optional type=bool
			if set to true, only these competitions will be returned where $contestant hasn't signed in.
		@param contestant optional type=oid
			this is for using with $registered and $unregistered, sets the contestant who's competitions will be returned.
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
		$list = new object_list(array(
			"class_id" => CL_SCM_COMPETITION,
		));
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
		$conn = new connection();
		$conns = $conn->find(array(
			"from" => $arr["competition"],
			"type" => 6,
		));
		foreach($conns as $id => $data)
		{
			$res[$data["to"]] = $data["to_name"];
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
		return $o->prop("scm_event");
	}

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
			$res .= html::textbox($textbox);
			$res .= $caption."&nbsp;&nbsp;";
		}
		return $res;
	}
	
	/**
	**/
	function get_teams_for_competition($arr = array())
	{
		$team = get_instance(CL_SCM_TEAM);
		foreach($team->get_teams() as $oid => $obj)
		{
			$comps = $obj->prop("competitions");
			if(in_array($arr["competition"], $comps))
			{
				$ret[$oid] = $oid;
			}
		}
		return $ret;
	}
}
?>
