<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/survey/survey_manager.aw,v 1.2 2004/06/17 14:32:33 duke Exp $
// survey_manager.aw - Ankeetide haldur 
/*

@classinfo syslog_type=ST_SURVEY_MANAGER relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property survey_folder type=relpicker reltype=RELTYPE_SURVEY_FOLDER
@caption Millisesse kataloogi täidetud ankeedid salvestada?

@property use_cfgform type=relpicker reltype=RELTYPE_SURVEY_CFGFORM
@caption Millist seadete vormi kasutada?

@property redirect_to type=relpicker reltype=RELTYPE_REDIRECT_TO
@caption Kuhu ümber suunata

@default group=filled_surveys
@property filled_surveys type=table store=no no_caption=1
@caption Täidetud ankeedid

@property sweepstake1 type=table group=sweepstake
@caption Paarid

property sweepstake2 type=table group=sweepstake
caption Koodikataloog

@groupinfo filled_surveys caption="Täidetud ankeedid" submit=no
@groupinfo sweepstake caption="Loosimine"

@reltype SURVEY_FOLDER value=1 clid=CL_MENU
@caption Kataloog

@reltype SURVEY_CFGFORM value=2 clid=CL_CFGFORM
@caption Seadete vorm

@reltype REDIRECT_TO value=3 clid=CL_DOCUMENT
@caption Kuhu peale täitmist suunata

*/

class survey_manager extends class_base
{
	function survey_manager()
	{
		$this->init(array(
			"tpldir" => "applications/survey/survey_manager",
			"clid" => CL_SURVEY_MANAGER
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "filled_surveys":
				$this->make_result_table($arr);
				break;

			case "sweepstake1":
				$this->make_p_table($arr);
				break;

			case "sweepstake2":
				return PROP_IGNORE;
				break;

		};
		return $retval;
	}
	
	function make_p_table($arr)
	{
		$arr["prop"]["vcl_inst"]->define_field(array(
			"name" => "p1",
			"caption" => "Autoportaal",
		));
		
		$arr["prop"]["vcl_inst"]->define_field(array(
			"name" => "p2",
			"caption" => "Koodikataloog",
		));

		$p1 = array(758,1582,666,401,681,829,573);
		$p2 = array(727,557,951,696,890,527);

		shuffle($p1);
		shuffle($p2);

		for ($i = 0; $i <= 5; $i++)
		{
			$o1 = new object($p1[$i]);
			$o2 = new object($p2[$i]);
			$arr["prop"]["vcl_inst"]->define_data(array(
				"p1" => $o1->name(),
				"p2" => $o2->name(),
			));
		};

		$o1 = new object($p1[6]);
			
		$arr["prop"]["vcl_inst"]->define_data(array(
			"p1" => $o1->name(),
			"p2" => " mitte kedagi :(",
		));
	}

	function make_s_table($arr)
	{
		
		$arr["prop"]["vcl_inst"]->define_field(array(
			"name" => "id",
			"caption" => "ID",
		));
		
		$arr["prop"]["vcl_inst"]->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));
		$arr["prop"]["vcl_inst"]->define_field(array(
			"name" => "email",
			"caption" => "E-post",
		));
		if ($arr["prop"]["name"] == "sweepstake1")
		{
			$start = 0;
			$end = $this->size;
		}
		else
		{
			$start = $this->size+1;
			$end = sizeof($this->map)-1;
		};
		
		for ($i = $start; $i <= $end; $i++)
		{
			$ox = new object($this->map[$this->people[$i]]);
			$arr["prop"]["vcl_inst"]->define_data(array(
				"id" => $ox->id(),
				"name" => $ox->name(),
				"email" => $ox->prop("utext5"),
			));

			//$arr["vcl_inst"]->define_data(array(
			//	"id" =>

		};


	}

	function callback_pre_edit($arr)
	{
		$ol = new object_list(array(
			"parent" => $arr["obj_inst"]->prop("survey_folder"),
			"class_id" => CL_SURVEY,
		));
		$people = $ol->names();
		$this->map = array_flip($people);
		shuffle($people);
		$this->people = $people;
		$this->size = (int)(sizeof($this->people) / 2);
	}

	function make_result_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => "Loodud",
			"type" => "time",
			"format" => "H:i d-M-y",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "utext7",
			"caption" => "Tavatelefon",
		));	
		$t->define_field(array(
			"name" => "utext6",
			"caption" => "Mobiil",
		));	
		$t->define_field(array(
			"name" => "utext3",
			"caption" => "Vanus",
		));	
		/*
		$t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"type" => "time",
			"format" => "H:i d-M-y",
			"sortable" => 1,
		));
		*/
		$t->define_field(array(
			"name" => "edit",
			"caption" => "Vaata",
			"align" => "center",
		));

		$ol = new object_list(array(
			"parent" => $arr["obj_inst"]->prop("survey_folder"),
			"class_id" => CL_SURVEY,
		));

		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$t->define_data(array(
				"name" => $o->name(),
				"created" => $o->created(),
				"modified" => $o->modified(),
				"edit" => html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $o->id()),CL_SURVEY),
					"caption" => "Vaata",
				)),
				"utext7" => $o->prop("utext7"),
				"utext6" => $o->prop("utext6"),
				"utext3" => $o->prop("utext3"),
			));
		};

	}

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		// right then, this thing has to return a correct form
		if ("" == aw_global_get("uid"))
		{
			return "";
		};
		$o = new object($arr["id"]);
		$t = get_instance(CL_SURVEY);

		// try to figure out whether this user has a filled survey
		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid(aw_global_get("uid")));

		$conns = $user->connections_to(array(
			"from.class_id" => CL_SURVEY,
		));

		if (sizeof($conns) == 1)
		{
			$first = reset($conns);
			$survey_id = $first->prop("from");
			return $t->new_change(array(
				"action" => "change",
				"id" => $survey_id,
				"extraids" => array("redirect_to" => $o->prop("redirect_to")),
			));
		}
		else
		{
			return $t->new_change(array(
				"action" => "new",
				"cfgform" => $o->prop("use_cfgform"),
				"extraids" => array("redirect_to" => $o->prop("redirect_to")),
				"parent" => $o->prop("survey_folder"),
			));
		};
	}
}
?>
