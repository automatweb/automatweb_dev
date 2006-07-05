<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_score_calc.aw,v 1.2 2006/07/05 14:52:42 tarvo Exp $
// scm_score_calc.aw - Punktis&uuml;steem 
/*

@classinfo syslog_type=ST_SCM_SCORE_CALC relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property score_calculator type=select
@caption Punktis&uuml;steem
*/

class scm_score_calc extends class_base
{
	function scm_score_calc()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_score_calc",
			"clid" => CL_SCM_SCORE_CALC
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "score_calculator":
				foreach($algorithms = $this->_gen_algorithm_list() as $fun_name => $caption)
				{
					$prop["options"][$fun_name] = $caption;
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
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}


	/**
		@comment
			here you define the algorithm's.
	**/
	function _gen_algorithm_list()
	{
		$ret = array(
			"_calc_smallwalk" => t("100 meetri käimine"),
			"_calc_shootout" => t("Pistongipüstoli laskmine"),
		);
		return $ret;
	}

	function algorithm_list()
	{
		return $this->_gen_algorithm_list();
	}

	function get_score_calcs()
	{
		$list = new object_list(array(
			"class_id" => CL_SCM_SCORE_CALC,
		));
		return $list->arr();
	}
	
	/* algoritmide funktsioonid */

	function _calc_smallwalk()
	{
	}

	function _calc_shootout()
	{
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
}
?>
