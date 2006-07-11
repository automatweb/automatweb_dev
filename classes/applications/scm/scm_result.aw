<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_result.aw,v 1.2 2006/07/11 07:55:39 tarvo Exp $
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
				$this->get_results(array(
					"contestant" => "582",
				));
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
		@param competition optional type=int
			the competition object id which results you want
		@param contestant optional type=int
			the contestants id which results you want
		@param set_competition optional type=bool
		@param set_contestant optional type=bool
		@comment
			fetches results
		@returnes
			array of results
	**/
	function get_results($arr)
	{
		$arr["class_id"] = CL_SCM_RESULT;
		if(strlen($arr["contestant"]))
		{
			$c= new connection();
			$res = $c->find(array(
				"to" => $arr["contestant"],
				"type" => 1,
			));
			if(strlen($arr["competition"]))
			{
				foreach($res as $relid => $data)
				{
					$nres = $c->find(array(
						"from" => $data["from"],
						"type" => 2,
						"to" => $arr["competition"],
					));
					$fres = array_merge($fres, $nres);
				}
			}
			else
			{
				$fres = $res;
			}
		}
		if(strlen($arr["competition"]) && !strlen($arr["contestant"]))
		{
			$c= new connection();
			$fres = $c->find(array(
				"to" => $arr["competition"],
				"type" => 2,
			));

		}
		// siin tehakse veidi mugavamale kujule viimist
		foreach($fres as $data)
		{
			$ret_data["result_oid"] = $data["from"];
			$competition = $this->get_competition(array("result" => $data["from"]));
			if($arr["set_competition"])
			{
				$ret_data["competition"] = $competition;
			}
			if($arr["set_contestant"])
			{
				$ret_data["contestant"] = $this->get_contestant(array("result" => $data["from"]));
			}
			// siin läheb mingi haige tulemuse formattimine lahti.. see tuleks siit ära kolida
			$comp_inst = get_instance(CL_SCM_COMPETITION);
			$res_obj = obj($data["from"]);
			$raw_result = $res_obj->prop("result");
			$event = $comp_inst->get_event(array("competition" => $competition));
			$event_inst = get_instance(CL_SCM_EVENT);
			$rtype = obj($event_inst->get_result_type(array("event" => $event)));
			$rtype_inst = get_instance(CL_SCM_RESULT_TYPE);
			$fun = $rtype_inst->units[$rtype->prop("unit")]["fun"];
			$res = $rtype_inst->$fun($raw_result);
			$ret_data["result"] = $res;
			$ret_data["fun"] = $fun;

			$ret[] = $ret_data;
		}
		return $ret;

	}
}
?>
