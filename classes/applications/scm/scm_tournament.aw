<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_tournament.aw,v 1.3 2006/07/18 06:05:17 tarvo Exp $
// scm_tournament.aw - V&otilde;istlussari
/*

@classinfo syslog_type=ST_SCM_TOURNAMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@groupinfo competitions caption="V&otilde;istlused" submit=no
	@property comp_toolbar no_caption=1 type=toolbar group=competitions
	@caption T&ouml;&ouml;riistariba

	@property comp_table no_caption=1 type=table group=competitions
	@caption V&ouml;istlused


*/

class scm_tournament extends class_base
{
	function scm_tournament()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_tournament",
			"clid" => CL_SCM_TOURNAMENT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "comp_toolbar":
				$url = $this->mk_my_orb("new", array(
					"class" => "scm_competition",
					"return_url" => get_ru(),
					"parent" => $arr["obj_inst"]->parent(),
				));
				$prop["vcl_inst"]->add_button(array(
					"name" => "add_competition",
					"tooltip" => t("Lisa uus võistlus"),
					"img" => "new.gif",
					"url" => $url,
				));
				$popup_search = get_instance("vcl/popup_search");
				arr($popup_search->get_popup_search_link(array(
					"pn" => "search_result",
					"clid" => CL_SCM_COMPETITION,
				)));
				$url = "#";
				$prop["vcl_inst"]->add_button(array(
					"name" => "search_competition",
					"tooltip" => t("Otsi olemasolevaid v&otilde;istlusi"),
					"img" => "search.gif",
					"url" => $url,
				));
			break;
			case "comp_table":
				$prop["vcl_inst"] = $this->_gen_competitions_table($prop["vcl_inst"]);

				$list = new object_list(array(
					"class_id" => CL_SCM_COMPETITION,
					"CL_SCM_COMPETITION.RELTYPE_TOURNAMENT" => $arr["obj_inst"]->id(),
				));
				foreach($list->arr() as $oid => $val)
				{
					$obj = obj($val->oid);
					$prop["vcl_inst"]->define_data(array(
						"nimi" => $obj->name(),
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
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

		
	function _gen_competitions_table($t)
	{
		$t->define_field(array(
			"name" => "nimi",
			"caption" => t("Võistluse nimi"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "location",
			"caption" => t("Asukoht"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "event",
			"caption" => t("Spordiala"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "start_time",
			"caption" => t("V&otilde;istluse aeg"),
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Olek"),
			"sortable" => true,
		));
		$t->define_chooser(array(
			"name" => "rem_comp",
			"field" => "rem_comp",
		));
		return $t;
	}
	
	function get_tournaments()
	{
		$list = new object_list(array(
			"class_id" => CL_SCM_TOURNAMENT,
		));
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
}
?>
