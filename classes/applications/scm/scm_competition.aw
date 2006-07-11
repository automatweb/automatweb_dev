<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_competition.aw,v 1.3 2006/07/11 07:55:39 tarvo Exp $
// scm_competition.aw - V&otilde;istlus 
/*

@classinfo syslog_type=ST_SCM_COMPETITION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize


@property scm_event type=relpicker reltype=RELTYPE_EVENT editonly=1
@caption Spordiala

@property scm_location type=relpicker reltype=RELTYPE_LOCATION editonly=1
@caption Asukoht

@property date type=date_select 
@caption Kuup&auml;ev

@property scm_tournament type=relpicker reltype=RELTYPE_TOURNAMENT editonly=1
@caption Turniir

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

@reltype LOCATION value=2 clid=CL_SCM_LOCATION
@caption Asukoht

@reltype TOURNAMENT value=3 clid=CL_SCM_TOURNAMENT
@caption Turniir

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
				$this->_gen_res_tbl(&$t);
				$results = $this->fetch_results(array(
					"competition" => $arr["obj_inst"]->id(),
				));
			break;
			case "add_results_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_add_res_tbl(&$t);
				$conts = $this->get_contestants(array("competition" => $arr["obj_inst"]->id()));
				$event = $this->get_event(array("competition" => $arr["obj_inst"]->id()));
				$inst = get_instance(CL_SCM_EVENT);
				$res_type = $inst->get_result_type(array("event" => $event));

				$type_inst = get_instance(CL_SCM_RESULT_TYPE);
				$format = $type_inst->get_format(array("result_type" => $res_type));
				$res_inst = get_instance(CL_SCM_RESULT);
				$results = $res_inst->get_results(array(
					"competition" => $arr["obj_inst"]->id(),
					"set_contestant" => true,
				));

				foreach($conts as $oid => $name)
				{
					$format_nice = $this->_gen_format_nice(array(
						"format" => $format,
						"data" => $results,
						"contestant" => $oid,
						"competition" => $arr["obj_inst"]->id(),
					));

					$cont = get_instance(CL_SCM_CONTESTANT);
					$person = obj($cont->get_contestant_person(array("contestant" => $oid)));
					$company = obj($cont->get_contestant_company(array("contestant" => $oid)));
					$t->define_data(array(
						"contestant" => $person->name(),
						"company" => $company->name(),
						"result" => $format_nice,
					));
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
				$res_type = get_instance(CL_SCM_RESULT_TYPE);
				$res = get_instance(CL_SCM_RESULT);
				foreach($arr["request"]["res"] as $result => $data)
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
				foreach($arr["request"]["res_new"] as $competition => $tmp)
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
			fetches results for given competition
	**/
	function fetch_results($arr)
	{
		$conn = new connection();
		$conns = $conn->find(array(
			"to.class_id" => CL_SCM_RESULT,
			"from" => $arr["competition"],
		));
	}

	function _get_image($arr)
	{
		$loc_id = $arr["obj_inst"]->prop("scm_location");
		
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

	function _gen_res_tbl($t)
	{
		$t->define_field(array(
			"name" => "contestant",
			"caption" => t("V&otilde;istleja"),
		));
		$t->define_field(array(
			"name" => "result",
			"caption" => t("Tulemus"),
		));
		$t->define_field(array(
			"name" => "place",
			"caption" => t("Koht"),
		));

	}

	function _gen_add_res_tbl($t)
	{
		$t->define_field(array(
			"name" => "contestant",
			"caption" => t("V&otilde;istleja"),
			"sortable" => 1,
		));
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
		@param unregistred optional type=bool
			if set to true, only these competitions will be returned where $contestant hasn't signed in.
		@param contestant optional type=oid
			if this is set with $unregistred=true, only unregistered competitions for this contestant are returned.
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
		if(strlen($arr["contestant"]) && $arr["unregistered"])
		{
			foreach($list->arr() as $oid => $obj)
			{
				$conns = $obj->connections_from(array(
					"type" => "RELTYPE_CONTESTANT",
				));
				foreach($conns as $con)
				{
					if($con->conn["to"] == $arr["contestant"])
					{
						$list->remove($oid);
						break;
					}

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
		$c = new connection();
		$res = $c->find(array(
			"from" => $arr["competition"],
			"to.class_id" => CL_SCM_EVENT,
		));
		$res = current($res);
		return $res["to"];
	}

	function _gen_format_nice($arr)
	{
		// andmed vaadatakse läbi.. ning leitakse õige võistleja, .. kui leitakse
		foreach($arr["data"] as $result)
		{
			if($result["contestant"] == $arr["contestant"])
			{
				$correct_result = $result;
				break;
			}
		}
		// formaadi väljad käikase läbi ja tekitatakse input'id
		foreach($arr["format"] as $name => $caption)
		{
			if(strlen($correct_result["result_oid"]))
			{
				$textbox["name"] = "res[".$correct_result["result_oid"]."][".$name."]";
			}
			else
			{
				$textbox["name"] = "res_new[".$arr["competition"]."][".$arr["contestant"]."][".$name."]";
			}
			$textbox["size"] = "4";
			$textbox["value"] = $correct_result["result"][$name];
			$res .= html::textbox($textbox);
			$res .= $caption."&nbsp;&nbsp;";
		}
		return $res;
	}
}
?>
