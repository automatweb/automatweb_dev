<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_result.aw,v 1.3 2006/07/17 09:48:43 tarvo Exp $
// scm_result.aw - Tulemus 
/*

@classinfo syslog_type=ST_SCM_RESULT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property result type=textbox
@caption Tulemus

@property competition type=text
@caption V&otilde;istlus

@property contestant type=text
@caption V&otilde;istleja

@property team type=hidden
@caption Meeskond

@reltype CONTESTANT value=1 clid=CL_SCM_CONTESTANT
@caption V&otilde;istleja

@reltype COMPETITION value=2 clid=CL_SCM_COMPETITION
@caption V&otilde;istlus

*/

class scm_result extends class_base
{
	function scm_result()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_result",
			"clid" => CL_SCM_RESULT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "contestant":
				$a = obj($this->get_contestant(array("result" => $arr["obj_inst"]->id())));
				$prop["value"] = $a->name();

			break;
			case "competition":
				$a = obj($this->get_competition(array("result" => $arr["obj_inst"]->id())));
				$prop["value"] = $a->name();
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
		}
		return $retval;
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
	function get_contestant($arr = array())
	{
		$c = new connection();
		$res = $c->find(array(
			"from" => $arr["result"],
			"to.class_id" => CL_SCM_CONTESTANT,
		));
		$res = current($res);
		return $res["to"];
	}

	function get_competition($arr = array())
	{
		$c = new connection();
		$res = $c->find(array(
			"from" => $arr["result"],
			"to.class_id" => CL_SCM_COMPETITION,
		));
		$res = current($res);
		return $res["to"];
	}

	/**
		@param result optional
		@param competition required
			competition oid to connect to
		@param contestant required
			contestant oid to connect to
		@param team optional
			sets the team 
		@comment
			adds result
	**/
	function add_result($arr = array())
	{
		if(empty($arr["competition"]) || empty($arr["contestant"]))
		{
			return false;
		}
		$obj = obj();
		$obj->set_class_id(CL_SCM_RESULT);
		$obj->set_parent($arr["competition"]);
		$a = obj($arr["competition"]);
		$b = obj($arr["contestant"]);
		$obj->set_name($a->name()." - ".$b->name());
		$obj->set_prop("result", $arr["result"]);
		$obj->set_prop("team", $arr["team"]);
		$obj->connect(array(
			"to" => $arr["competition"],
			"type" => "RELTYPE_COMPETITION",
		));
		$obj->connect(array(
			"to" => $arr["contestant"],
			"type" => "RELTYPE_CONTESTANT",
		));
		
		$id = $obj->save_new();

		return $id;
	}

	function get_teams_results($arr = array())
	{
		return $this->get_results(array(
			"competition" => $arr["competition"],
			"type" => "team",
			"set_team" => true,
		));
	}

	/**
		@attrib params=name
		@param competition required type=int
			the competition object id which results you want
		@param type optional type=string
			options:
				contestant,team
			if only $competition is set, then returns wheater contestants results or team's results
		@param contestant optional type=int
			the contestants id which results you want
		@param team optional type=int
			the team's id which results you want
		@param set_contestant optional type=bool
			includes contestant in returning array .. when dealing with individual contestants
		@param set_team optional type=bool
			includes team in returning array.. when dealing with teams
		@comment
			fetches results
			the $competition param can be together with $contestant or $team!. if both are set, $contestant is preferred
		@returnes
			array of results
	**/
	function get_results($arr)
	{
		if($arr["type"] == "contestant")
		{
			$c = new connection();
			$list = new object_list(array(
				"class_id" => CL_SCM_RESULT,
				"CL_SCM_RESULT.RELTYPE_CONTESTANT" => "%",
				"CL_SCM_RESULT.RELTYPE_COMPETITION" => $arr["competition"],
			));
			$inst = get_instance(CL_SCM_TEAM);
			foreach($list->ids() as $id)
			{
				$cont = $this->get_contestant(array("result" => $id));
				$team = $inst->get_team(array(
					"contestant" => $cont,
					"competition" => $arr["competition"],
				));
				$o = $arr["set_team"]?obj($team):false;
				$to_format[] = array(
					"competition" => $arr["competition"],
					"contestant" => $cont,
					"result" => $id,
					"team" => ($arr["set_team"])?$o->name():NULL,
					"team_oid" => ($arr["set_team"])?$team:NULL,
				);
			}
		}
		elseif($arr["type"] == "team")
		{
			$team = get_instance(CL_SCM_TEAM);
			foreach($team->get_teams() as $oid => $obj)
			{
				$comps = $obj->prop("competitions");
				if(in_array($arr["competition"], $comps))
				{
					// this sets one contestant from each team to an array.. for the connection search
					$list = new object_list(array(
						"class_id" => CL_SCM_RESULT,
						"CL_SCM_RESULT.RELTYPE_CONTESTANT" => key($team->get_team_members(array("team" => $oid))),
						"CL_SCM_RESULT.RELTYPE_COMPETITION" => $arr["competition"],
					));
					$result_id = current($list->ids());
					$to_format[] = array(
						"result" => $result_id,
						"team" => $oid,
						"competition" => $arr["competition"],
					);
				}
			}
		}
		return $this->_helper($to_format);
	}

	/**
		@param result
		@param competition
		@param contestant
		@param team
	**/
	function _helper($arr = array())
	{
		foreach($arr as $data)
		{
			unset($ret_data);
			$ret_data["result_oid"] = $data["result"];
			$ret_data["competition"] = $data["competition"];
			if(strlen($data["contestant"]))
			{
				$ret_data["contestant"] = $data["contestant"];
			}
			if(strlen($data["team"]))
			{
				$ret_data["team"] = $data["team"];
				$ret_data["team_oid"] = $data["team_oid"];
			}

			// siin läheb mingi haige tulemuse formattimine lahti.. see tuleks siit ära kolida
			$comp_inst = get_instance(CL_SCM_COMPETITION);
			$res_obj = obj($data["result"]);
			$raw_result = $res_obj->prop("result");
			$event = $comp_inst->get_event(array("competition" => $data["competition"]));
			$event_inst = get_instance(CL_SCM_EVENT);
			$rtype = obj($event_inst->get_result_type(array("event" => $event)));
			$rtype_inst = get_instance(CL_SCM_RESULT_TYPE);
			$fun = $rtype_inst->units[$rtype->prop("unit")]["fun"];
			$res = $rtype_inst->$fun($raw_result);
			$ret_data["result"] = $res;
			$ret_data["raw_result"] = $raw_result;
			$ret_data["fun"] = $fun;
			$ret[] = $ret_data;
		}
		return $ret;
	}
}
?>
