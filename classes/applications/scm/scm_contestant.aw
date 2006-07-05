<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_contestant.aw,v 1.2 2006/07/05 14:52:42 tarvo Exp $
// scm_contestant.aw - V&otilde;istleja 
/*

@classinfo syslog_type=ST_SCM_CONTESTANT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property contestant type=text store=no
@caption V&otilde;istleja

@property contestants_company type=text store=no
@caption Firmast

@groupinfo competitions caption="V&otilde;istlused" submit=no
	@property comp_tb type=toolbar group=competitions no_caption=1
	@caption V&otilde;istluste riba

	@property comp_tbl type=table group=competitions no_caption=1
	@caption V&otilde;istluste tabel

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
					"img" => "prog_32.gif",
				));
			break;

			case "comp_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_comp_tbl(&$t);
				$comp = get_instance(CL_SCM_COMPETITION);
				foreach($comp->get_competitions() as $oid => $obj)
				{
					$organizer = obj($comp->get_organizer_company(array("competition" => $obj->id())));
					
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
						"organizer" => $organizer->name(),
					));
				}
			break;

			case "contestant":
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
		return $o->prop("work_contact");
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
		$conn = new connection();
		$conns = $conn->find(array(
			"from" => $arr["contestant"],
			"to.class_id" => CL_CRM_PERSON,
		));
		$conn = current($conns);
		return $conn["to"];
	}
}
?>
