<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_score_calc.aw,v 1.6 2006/08/21 19:03:17 tarvo Exp $
// scm_score_calc.aw - Punktis&uuml;steem 
/*

@classinfo syslog_type=ST_SCM_SCORE_CALC relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property score_calculator type=select
@caption Punktis&uuml;steem

@groupinfo algorithm caption="Algoritm"
	@default group=algorithm

	@property max_points type=textbox size=5
	@caption Maksimumpunktid

	@property points_step type=textbox size=5
	@caption Samm

	@property points_exception type=textbox size=20
	@caption Erandid

	@property points_others type=textbox size=5
	@caption &Uuml;lej&auml;&auml;nud v&otilde;istlejad

@groupinfo manual_points caption="Kohapunktid"
	@default group=manual_points

	@property man_count type=textbox size=5
	@caption Punkte saavad x esimest

	@property man_points type=text
	@caption Punktid

*/

class scm_score_calc extends class_base
{
	function scm_score_calc()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_score_calc",
			"clid" => CL_SCM_SCORE_CALC
		));
		$this->_set_data();
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "score_calculator":
				foreach($algorithms = $this->algorithm_list() as $fun_name => $caption)
				{
					$prop["options"][$fun_name] = $caption;
				}
			break;

			case "man_count":
				
			break;

			case "man_points":
				$count = $arr["obj_inst"]->prop("man_count");
				for($i = 1; $i <= $count; $i++)
				{
					$textbox = html::textbox(array(
						"name" => "point[".$i."]",
						"size" => "5",
					));
					$html .= sprintf(t("Koht nr %s:"), $i). $textbox."<br/>";
				}
				$html .= t("&Uuml;lej&auml;&auml;nud:").html::textbox(array(
					"name" => "point[0]",
					"size" => "5",
				));
				$prop["value"] = $html;
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

	function algorithm_list()
	{
		return $this->data;
	}

	function get_score_calcs()
	{
		$list = new object_list(array(
			"class_id" => CL_SCM_SCORE_CALC,
		));
		return $list->arr();
	}

	/* algoritmide funktsioonid */

	/*
		pm siin on siis need funktsioonid kuhu tuleb edasi anda tulemused, ning välja lastakse punktid

		ok.. olen competitionis. seal on mul keretäis jama. kuidas seda ilusaks saada?
		+ kõigepealt tuleks vastavalt result type'ile sorteerida äki? .. a vb pean seda siin samas tegema?.. siin poleks hea!!
		+ siia peaks andma:
		array(
			id,
			raw_result,
		)
		jeah.. siin ei tohiks üldse tegelt mingit sortimist teha!!!!!
	*/

	/**
		@comment
			first place	5p
			second place	3p
			third place	1p
			others		0p
	**/
	function _first_three_for_shootout($place)
	{
		$point = 5;
		$step = 2;
		return (($s = ($point - (($place - 1) * $step))) > 0)?$s:0;
	}
	
	/**
		@comment
			first		10p
			second		8p
			third		6p
			fourth		4p
			fifth		2p
			orhers 		0p
	**/
	function _first_five_for_breath($place)
	{
		$point = 10;
		$step = 2;
		return (($s = ($point - (($place - 1) * $step))) > 0)?$s:0;
	}

	/**
		@comment
			first		15
			second		11
			third		7
			fourth		3
			others		0
	**/
	function _first_three_step_five($place)
	{
		$point = 15;
		$step = 4;
		return (($s = ($point - (($place - 1) * $step))) > 0)?$s:0;
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

	function _set_data()
	{
		$this->data = array(
			"_first_five_for_breath" => t("Esimesed 5(10p alates)"),
			"_first_three_step_five" => t("Esimesed 3 saavad(15/4)"),
			"_first_three_for_shootout" => t("Esimesed kolm saavad punktid (laskmine)"),
		);

	}

	function get_score_calc($arr = array())
	{
		$obj = obj($arr["score_calc"]);
		$u= strlen($s = $obj->prop("score_calculator"))?$s:false;
		return $u;
	}

	/**
		@param data required type=array
		@param score_calc required type=oid
		@param competition required type=oid
		@comment
			sorts results
	**/
	function calc_results($arr)
	{
		// at first.. we must sort the array accordingly to result_type.sort
		// then, we loop over the results starting from first place.. and ask points for each place for its function
		$event_inst = get_instance(CL_SCM_EVENT);
		$competition_inst = get_instance(CL_SCM_COMPETITION);
		$res_type_inst = get_instance(CL_SCM_RESULT_TYPE);

		$res_type = $event_inst->get_result_type(array(
			"event" => $competition_inst->get_event(array(
				"competition" => $arr["competition"]
			))
		));
		$sorted = $res_type_inst->sort_results(array(
			"data" => $arr["data"],
			"result_type" => $res_type,
		));
		$arr["score_calc"] = call_user_method("prop", obj($arr["competition"]), "scm_score_calc");
		$fun = $this->get_score_calc($arr);
		foreach($sorted as $id => $place)
		{
			$ret[$id] = array(
				"place" => $place,
				"points" => $this->$fun($place),
			);
		}
		return $ret;
	}
}
?>
