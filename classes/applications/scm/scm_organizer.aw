<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_organizer.aw,v 1.4 2006/07/17 09:48:43 tarvo Exp $
// scm_organizer.aw - Spordiv&otilde;istluste korraldaja 
/*

@classinfo syslog_type=ST_SCM_ORGANIZER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property organizer_person type=relpicker reltype=RELTYPE_ORGANIZER
@caption Organiseeria

@property organizer_company type=text store=no editonly=1
@caption Firmast

@groupinfo competitions caption="V&otilde;istlused" submit=no
	@default group=competitions
	
	@property competitions_tb type=toolbar no_caption=1
	@caption voistluste tuulbar

	@property competitions_tbl type=table no_caption=1
	@caption Praeguste v&otilde;istluste nimekiri

@groupinfo events caption="Spordialad" submit=no
	@default group=events

	@property events_tb type=toolbar no_caption=1
	@caption alade tuulbar

	@property events_tbl type=table no_caption=1
	@caption Spordialade nimistu

@groupinfo locations caption="Asukohad" submit=no
	@default group=locations

	@property location_tb type=toolbar no_caption=1
	@caption Asukohtade tuulbar

	@property location_tbl type=table no_caption=1
	@caption Asukohtade tabel

@groupinfo tournaments caption="V&otilde;istlussarjad" submit=no
	@property tournaments_tbl type=table no_caption=1 group=tournaments
	@caption V&otilde;istlussarjade tabel

@groupinfo score_calcs caption="Punktis&uuml;steemid" submit=no
	@property score_calc_tbl type=table no_caption=1 group=score_calcs
	@caption Punktis&uuml;steemid

@groupinfo groups caption="V&otilde;istlusklassid" submit=no
	@property groups_tbl type=table no_caption=1 group=groups
	@caption V&otilde;istlusklasside tabel

@groupinfo result_types caption="Paremusj2rjestuse tyybid" submit=no
	@property result_type_tbl type=table group=result_types no_caption=1
	@caption tabel

@reltype COMPETITION value=1 clid=CL_SCM_COMPETITION
@caption V&otilde;istlus

@reltype ORGANIZER value=2 clid=CL_CRM_PERSON
@caption Organisaator
*/

class scm_organizer extends class_base
{
	function scm_organizer()
	{
		$this->init(array(
			"tpldir" => "applications/scm/scm_organizer",
			"clid" => CL_SCM_ORGANIZER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			// events
			case "events_tbl":
				$this->_gen_events_tbl(&$prop["vcl_inst"]);
				$inst = get_instance(CL_SCM_EVENT);
				$filt = array(
					"organizer" => $arr["obj_inst"]->id(),
				);
				foreach($inst->get_events($filt) as $id => $obj)
				{
					$prop["vcl_inst"]->define_data(array(
						"name" => $obj->name(),
						"type" => $obj->prop("type"),
						"result_type" => $obj->prop("result_type")
					));
				}
			break;
			case "events_tb":
				$tb = &$prop["vcl_inst"];

				$tb->add_button(array(
					"name" => "new_event",
					"tooltip" => t("Lisa uus spordiala"),
					"img" => "new.gif",
					"url" => $this->mk_my_orb("new",array(
						"class" => "scm_event",
						"parent" => $arr["obj_inst"]->parent(),
					)),
				));
			break;
			// res types
			case "result_type_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_res_type_tbl(&$t);
				$res_type = get_instance(CL_SCM_RESULT_TYPE);
				$filt = array(
					"organizer" => $arr["obj_inst"]->id(),
				);
				foreach($res_type->get_result_types($filt) as $oid => $obj)
				{
					$t->define_data(array(
						"name" => $obj->name(),
						"sort" => $obj->prop("sort"),
						"unit" => $obj->prop("unit"),
					));
				}

			break;
			case "result_type_h":
				$prop["value"] = CL_SCM_RESULT_TYPE;
			break;
			// locations
			case "location_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_loc_tbl(&$t);
				$loc = get_instance(CL_SCM_LOCATION);
				foreach($loc->get_locations($filt) as $oid => $obj)
				{
					$t->define_data(array(
						"name" => $obj->name(),
						"address" => $obj->prop("address"),
						"map" => "link",
						"photo" => "link"
					));
				}
			break;
			case "location_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name" => "new_location",
					"tooltip" => t("Uus asukoht"),
					"img" => "new.gif",
					"url" => $this->mk_my_orb("new", array(
						"class" => "scm_location",
						"parent" => $arr["obj_inst"]->parent(),
					)),
				));
			break;
			// competitions
			case "competitions_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_comp_tbl(&$t);
				$inst = get_instance(CL_SCM_COMPETITION);
				foreach($inst->get_competitions() as $id => $obj)
				{
					$e_obj = obj($obj->prop("scm_event"));
					$l_obj = obj($obj->prop("scm_location"));
					$t_obj = obj($obj->prop("scm_tournament"));
					$competition_url = $this->mk_my_orb("change" ,array(
						"class" => "scm_competition",
						"id" => $obj->id(),
						"return_url" => get_ru(),
					));
					$event_url = $this->mk_my_orb("change",array(
						"class" => "scm_event",
						"id" => $e_obj->id(),
						"return_url" => get_ru(),
					));
					$location_url = $this->mk_my_orb("change",array(
						"class" => "scm_location",
						"id" => $l_obj->id(),
						"return_url" => get_ru(),
					));
					$tournament_url = $this->mk_my_orb("change", array(
						"class" => "scm_tournament",
						"id" => $t_obj->id(),
						"return_url" => get_ru(),
					));
					$link = html::href(array(
						"caption" => 
							"%s",
						"url" => "%s",
					));
					$t->define_data(array(
						"name" => sprintf($link, $competition_url, $obj->name()),
						"location" => sprintf($link, $location_url, $l_obj->prop("name")),
						"event" => sprintf($link, $event_url, $e_obj->prop("name")),
						"date" => date("d / m / Y",$obj->prop("date")),
						"tournament" => sprintf($link, $tournament_url, $t_obj->prop("name")),
					));
				}
			break;
			case "competitions_tb":
				$tb = &$prop["vcl_inst"];

				$tb->add_button(array(
					"name" => "new_competition",
					"tooltip" => t("Lisa uus voistlus"),
					"img" => "new.gif",
					"url" => $this->mk_my_orb("new",array(
						"class" => "scm_competition",
						"parent" => $arr["obj_inst"]->parent(),
						"reltype" => 1, // like whotto fokk?
						"alias_to" => $arr["obj_inst"]->id(),
						"return_url" => post_ru(),
					)),
				));
			break;

			// tournaments 
			case "tournaments_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_trn_tbl(&$t);
				$t->define_data(array(
					"name" => "testasi.. static",
					"competitions" => "5",
				));
			break;

			// score calcs
			case "score_calc_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_scorecalc_tbl(&$t);
				$sc = get_instance(CL_SCM_SCORE_CALC);
				foreach($sc->algorithm_list() as $alg)
				{
					$t->define_data(array(
						"name" => $alg,
					));
				}
			break;
			// general
			case "organizer_company":
				$company = obj($this->get_organizer_company($arr = array("organizer" => $arr["obj_inst"]->id())));
				$prop["value"] = $company->name();
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
			case "result_type_unit":
				return PROP_IGNORE;
			break;
			case "event_type":
				return PROP_IGNORE;
			break;
			case "new_grp":
				return PROP_IGNORE;
			break;

		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_pre_save($arr)
	{
		$arr["obj_inst"]->set_name($arr["obj_inst"]->prop_str("organizer_person"));
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
		@param oganizer required type=oid
			scm_organizer object id.
		@comment
			fetches crm_company oid where the organizer person works.
		@returns
			oid of the crm_company.
	**/
	function get_organizer_company($arr)
	{
		$o = obj($this->get_organizer_person($arr));
		return ($s = $o->prop("work_contact"))?$s:false;
	}

	/**
		@attrib params=name api=1
		@param organizer required type=oid
			scm_organizer object id.
		@comment
			fetches crm_person oid connected to given organizer
		@returns
			crm_person's oid.
	**/
	function get_organizer_person($arr)
	{
		$obj = obj($arr["organizer"]);
		return ($o = $obj->prop("organizer_person"))?$o:false;
	}

	/**
		@comment
			generates list of all organizers.
		@returns
			array of all the organizers.
			array(
				scm_organizer oid,
				scm_organizer object_inst,
			)
	**/
	function get_organizers()
	{
		$list = new object_list(array(
			"class_id" => CL_SCM_ORGANIZER,
		));
		return $list->arr();
	}
	

	function _exclude_new($arr)
	{
		return (!$arr["request"]["add_new"])?true:false;
	}

	function _gen_loc_img_list($arr)
	{
		$conns = new connection();
		$conns = $conns->find(array(
			"from.class_id" => CL_SCM_LOCATION,
			"to.class_id" => CL_IMAGE,
			"from.parent" => $arr["obj_inst"]->id(),
		));
		foreach($conns as $conn)
		{
			$obj = obj($conn["to"]);
			$prop["options"][$conn["to"]] = $obj->name();
		}
	}

	function _gen_events_tbl($t)
	{
		$t->define_field(array(
			"caption" => t("Nimetus"),
			"name" => "name",
		));
		$t->define_field(array(
			"caption" => t("T&uuml;&uuml;p"),
			"name" => "type"
		));
		$t->define_field(array(
			"caption" => t("Paremusj&auml;rjestuse t&uuml;&uuml;p"),
			"name" => "result_type"
		));
	}

	function _gen_comp_tbl($t)
	{
		$t->define_field(array(
			"caption" => t("&Uuml;rituse nimi"),
			"name" => "name",
			"sortable" => 1,
		));
		$t->define_field(array(
			"caption" => t("Spordiala"),
			"name" => "event",
			"sortable" => 1,
		));
		$t->define_field(array(
			"caption" => t("Asukoht"),
			"name" => "location",
			"sortable" => 1,
		));
		$t->define_field(array(
			"caption" => t("Kuup&auml;ev"),
			"name" => "date",
			"sortable" => 1,
		));
		$t->define_field(array(
			"caption" => t("V&otilde;istlussari"),
			"name" => "tournament",
			"sortable" => 1,
		));

		$t->set_default_sortby("name");

	}

	function _gen_res_type_tbl($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "unit",
			"caption" => t("M&ouml;&ouml;detav &Uuml;hik"),
		));
		$t->define_field(array(
			"name" => "sort",
			"caption" => t("Sorteeritakse"),
		));

	}

	function _gen_loc_tbl($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimetus"),
		));
		$t->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
		));
		$t->define_field(array(
			"name" => "map",
			"caption" => t("Kaart"),
		));
		$t->define_field(array(
			"name" => "photo",
			"caption" => t("Foto"),
		));

	}

	function _gen_trn_tbl($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("V&otilde;istlussarja nimi"),
		));	
		$t->define_field(array(
			"name" => "competitions",
			"caption" => t("V&otilde;istlusi"),
		));

	}

	function _gen_scorecalc_tbl($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Algoritm"),
		));

	}

}
?>
