<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_result.aw,v 1.4 2006/08/09 15:06:55 tarvo Exp $
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
		$obj = obj($arr["result"]);
		$comp = $obj->get_first_obj_by_reltype("RELTYPE_COMPETITION");
		return $comp->id();
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
		$c = $arr["team"]?obj($arr["team"]):NULL;
		$name = $a->name()." (".$b->name();
		$name = $arr["team"]?$name." / ".$c->name().")":$name.")";
		$obj->set_name($name);
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
			$conns = $c->find(array(
				"from" => $arr["competition"],
				"to" => $arr["contestant"]?$arr["contestant"]:"%",
			));
			$list = new object_list();
			foreach($conns as $cid => $cdata)
			{
				$cmp = $arr["competition"];
				$cnt = $cdata["to"];
				$extra_data = aw_unserialize($cdata["data"]);
				$list = new object_list(array(
					"class_id" => CL_SCM_RESULT,
					"CL_SCM_RESULT.RELTYPE_CONTESTANT" => $cnt,
					"CL_SCM_RESULT.RELTYPE_COMPETITION" => $cmp,
				));
				if(!$list->count())
				{
					$id = $this->add_result(array(
						"competition" => $cmp,
						"contestant" => $cnt,
					));
					$list->add($id);
				}
				$obj = $list->begin();
				$to_format[] = array(
					"competition" => $cmp,
					"contestant" => $cnt,
					"result" => $obj->id(),
					"team_oid" => $arr["set_team"]?$extra_data["team"]:NULL,
					"team" => $arr["set_team"]?"tiimi nimi":NULL,
				);
			}
		}
		elseif($arr["type"] == "team")
		{
			$cmp = get_instance(CL_SCM_COMPETITION);
			foreach($cmp->get_contestants(array("competition" => $arr["competition"])) as $oid => $data)
			{
				$cnt = $oid;
				$cmp = $arr["competition"];
				$list = new object_list(array(
					"class_id" => CL_SCM_RESULT,
					"CL_SCM_RESULT.RELTYPE_CONTESTANT" => $cnt,
					"CL_SCM_RESULT.RELTYPE_COMPETITION" => $cmp,				
				));
				if(!$list->count())
				{
					$id = $this->add_result(array(
						"contestant" => $cnt,
						"competition" => $cmp,
						"team" => $data["data"]["team"],
					));
					$list->add($id);
				}
				if(!in_array($data["data"]["team"], $teams_already_been))
				{
					$obj = $list->begin();
					$to_format[] = array(
						"result" => $obj->id(),
						"team" => $data["data"]["team"],
						"competition" => $data["data"]["competition"],
						"team_oid" => $data["data"]["team"],
					);
				}
				$teams_already_been[] = $data["data"]["team"];
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
