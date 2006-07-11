<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_contestant.aw,v 1.3 2006/07/11 07:55:39 tarvo Exp $
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

@groupinfo competitions caption="V&otilde;istlused" submit=no
	@property comp_tb type=toolbar group=competitions no_caption=1
	@caption V&otilde;istluste riba

	@property comp_tbl type=table group=competitions no_caption=1
	@caption V&otilde;istluste tabel

	@property reg_button type=submit group=competitions
	@caption Registreeru

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
			case "comp_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name" => "register",
					"tooltip" => t("Registreeri"),
					"url" => "#",
					"img" => "save.gif",
				));
			break;

			case "comp_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_comp_tbl(&$t);
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
					
					$l_obj = obj($obj->prop("scm_location"));
					$e_obj = obj($obj->prop("scm_event"));
					$t_obj = obj($obj->prop("scm_tournament"));
					
					$l_url = $this->mk_my_orb("change", array(
						"class" => "scm_location",
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
					$t->define_data(array(
						"competition" => sprintf($link, $c_url, $obj->name()),
						"date" => date("d / m / Y", $obj->prop("date")),
						"register" => $obj->id(),
						"location" => sprintf($link, $l_url, $l_obj->name()),
						"event" => sprintf($link, $e_url, $e_obj->name()),
						"tournament" => sprintf($link, $t_url, $t_obj->name()),
						"organizer" => $org_company->name(),
					));
				}
			break;

			case "csontestant":
				$o = obj($this->get_contestant_person(array("contestant" => $arr["obj_inst"]->id())));
				$prop["value"] = $o->name();
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
			case "comp_tbl":
				foreach($arr["request"]["reg"] as $oid)
				{
					$obj = obj($oid);
					$obj->connect(array(
						"to" => $arr["obj_inst"]->id(),
						"type" => "RELTYPE_CONTESTANT",
					));
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

	function _gen_comp_tbl(&$t)
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
			"name" => "location",
			"caption" => t("Toimumiskoht"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Toimumisaeg"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "organizer",
			"caption" => t("Korraldaja"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "tournament",
			"caption" => t("Turniir"),
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"name" => "reg",
			"field" => "register",
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
}
?>
