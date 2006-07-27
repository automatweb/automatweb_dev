<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_team.aw,v 1.4 2006/07/27 23:32:14 tarvo Exp $
// scm_team.aw - Meeskond 
/*

@classinfo syslog_type=ST_SCM_TEAM relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize


@groupinfo members caption="Liikmed"
	@default group=members
	
	@property members_tb type=toolbar no_caption=1
	@caption Liikemete halduse t&ouml;&ouml;riistariba

	@property members_tbl type=table no_caption=1
	@caption Liikmete tabel

	@property search_res type=hidden name=search_result store=no no_caption=1
	@caption Otsingutulemuste hoidja

	@property members_unreg type=submit
	@caption Eemalda v&otilde;istkonnast

@groupinfo competitions caption="V&otilde;istlused" submit=no
	@groupinfo registration caption="Registreerumine" parent=competitions
		@default group=registration
		@property registration_tb type=toolbar no_caption=1

		@layout split_reg type=hbox width=15%:85% group=registration
			@property members_tree type=treeview no_caption=1 parent=split_reg
			@property members_list type=table no_caption=1 parent=split_reg

	@groupinfo registered caption="V&otilde;istlused" caption="V&otilde;istlused" parent=competitions
	
	
@reltype SCM_TEAM_MEMBER value=2 clid=CL_SCM_CONTESTANT
@caption V&otilde;istkonna liige
*/

class scm_team extends class_base
{
	function scm_team()
	{
		$this->init(array(
			"tpldir" => "applications/scm//scm_team",
			"clid" => CL_SCM_TEAM
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "members_tb":
				$tb = &$prop["vcl_inst"];

				$url = $this->mk_my_orb("new",array(
					"parent" => $arr["obj_inst"]->id(),
					"alias_to" => $arr["obj_inst"]->id(),
					"class" => "scm_contestant",
					"reltype" => 2,
					"id" => $arr["obj_inst"]->id(),
					"return_url" => get_ru(),
				));

				$tb->add_button(array(
					"name" => "add_member",
					"tooltip" => t("Lisa liige"),
					"img" => "new.gif",
					"url" => $url,
				));
				$popup_search = get_instance("vcl/popup_search");
				$search_butt = $popup_search->get_popup_search_link(array(
					"pn" => "search_result",
					"clid" => CL_SCM_CONTESTANT,
				));
				$prop["vcl_inst"]->add_cdata($search_butt);
			break;
			
			case "members_tbl":
				$t = &$prop["vcl_inst"];
				$this->_gen_members_tbl(&$t);
				foreach($this->get_team_members(array("team" => $arr["obj_inst"]->id())) as $oid => $obj)
				{
					$inst = get_instance(CL_SCM_CONTESTANT);
					$pers = obj($inst->get_contestant_person(array("contestant" => $oid)));
					$t->define_data(array(
						"name" => $obj->name(),
						"sex" => ($pers->prop("gender") == 1)?t("Mees"):t("Naine"),
						"rem_contestant" => $oid,
					));
				}
			break;

			case "registration_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name" => "bah",
					"img" => "new.gif",
				));
			break;

			case "members_tree":
				$t = get_instance("vcl/treeview");
				classload("core/icons");
				$t->start_tree(array(
					"type" => TREE_DHTML,
					"has_root" => 1,
					"root_name" => t("Organisaatorid"),
					"root_url" => "#",
					"root_icon" => icons::get_icon_url(CL_CRM_PERSON),
					"tree_id" => "reg_tree",
					"persist_state" => 1,
					"get_branch_func" => $this->mk_my_orb("gen_tree_branch", array(
						"self_id" => $arr["obj_inst"]->id(),
						"group" => $arr["request"]["group"],
						"parent" => " ",
					)),
				));
				$this->_gen_reg_tree(&$t);
				$prop["type"] = "text";
				$prop["value"] = $t->finalize_tree();
			break;

			case "members_list":
				if(!($competition = $arr["request"]["competition"]))
				{
					$prop["value"] = t("Palun vali v&otilde;istlus");
				}
				$t = &$prop["vcl_inst"];
				$this->_gen_members_reg_tbl(&$t);
				$comp = obj($competition);
				$t->define_header(sprintf(t("V&otilde;istluse '%s' haldamine"), $comp->name()));

				$inst = get_instance(CL_SCM_COMPETITION);
				$cont_inst = get_instance(CL_SCM_CONTESTANT);
				$contestants = array_keys($inst->get_contestants(array("competition" => $competition)));
				$members = $this->get_team_members(array("team" => $arr["obj_inst"]->id()));

				$grps = $inst->get_groups(array(
					"competition" => $competition
				));
				foreach($grps as $gr)
				{
					$o = obj($gr);
					$gr_options[$gr] = $o->name();
				}
				foreach($members as $oid => $obj)
				{
					$reg = (in_array($oid, $contestants))?true:false;
					if($reg)
					{
						$c = new connection();
						$conns = $c->find(array(
							"from" => $competition,
							"to" => $oid,
						));
						// siin ta tegelikult ei peaks esimest võtma vaid mingi funktsioon peaks otsima kas ta on selle tiimiga seotud.. vist??.. sest ta võib olla niisama üksiküritaja ka ju. vot vot.. ja kui ongi üksiküritaja, siis peaks siit loop'ist välja minema. samas see get_team_members võiks kohe selle välja raalida ja õige data ka kaasa anda!!! 
						$id = key($conns);
						$c = new connection($id);
						$extra_data = aw_unserialize($c->prop("data"));
					}
					$url = $this->mk_my_orb("change",array(
						"class" => "scm_contestant",
						"id" => $oid,
						"return_url" => get_ru(),
					));
					$link = html::href(array(
						"url" => $url,
						"caption" => $obj->name(),
					));

					$un = ($reg)?"un":"";
					$chbox_js_click = "javascript:if(getElementById(\"group_".$oid."\").disabled) getElementById(\"group_".$oid."\").disabled=false; else getElementById(\"group_".$oid."\").disabled=true;";
					$chbox = html::checkbox(array(
						"name" => $un."reg[".$competition."][".$oid."]",
						"checked" => $reg,
						"value" => "-1",
						"onclick" => $chbox_js_click,
					));
					$groups = array(
						"name" => "gr[".$oid."]",
						"disabled" => !$reg,
						"id" => "group_".$oid,
						"options" => $gr_options,
						"multiple" => 1,
						"selected" => $extra_data["groups"],
						"size" => 3,
					);
					$p_obj = obj($cont_inst->get_contestant_person(array("contestant" => $oid)));
					$t->define_data(array(
						"contestant" => $link,
						"registered" => $chbox,
						"group" => html::select($groups),
						"sex" => ($p_obj->prop("gender") == 1)?t("Mees"):t("Naine"),
						"birthday" => (!($s = $p_obj->prop("birthday")) || $s < 0)?t("Pole m&auml;&auml;ratud"):$s,
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
			case "members_tbl":
				$request = $arr["request"];

				// otsingust tulevate liikmete sidumine meeskonna liikmeks
				$sr = (strlen($request["search_result"]))?split(",", $request["search_result"]):NULL;
				$list = array_keys($this->get_team_members($arr["obj_inst"]->id()));
				foreach($sr as $contestant)
				{
					if(!in_array($contestant, $list))
					{
						$arr["obj_inst"]->connect(array(
							"type" => "RELTYPE_SCM_TEAM_MEMBER",
							"to" => $contestant,
						));
					}
				}
			break;

			case "members_unreg":
				$rem = count($r = $arr["request"]["rem"])?$r:false;
				foreach($rem as $contestant)
				{
					$arr["obj_inst"]->disconnect(array(
						"from" => $contestant,
					));
				}
			break;
		}
		return $retval;
	}	

	function callback_pre_save($arr)
	{
		arr($arr);
		die();
		$unreg = $arr["request"]["unreg"];
		$reg = $arr["request"]["reg"];
		$groups = $arr["request"]["gr"];
		// registering new competitors
		foreach($reg as $competition => $data)
		{
			foreach(array_keys($data) as $contestant)
			{
				$extra_data = array(
					"team" => $arr["obj_inst"]->id(),
					"groups" => $groups[$contestant],
					"competition" => $competition,
					"contestant" => $contestant,
				);

				$obj = obj($competition);
				// well, this is where i need to connect the contestant to competition and add some extra data to the connection data property.
				$c = new connection(array(
					"from" => $competition,
					"to" => $contestant,
					"reltype" => 6,
					"data" => aw_serialize($extra_data, SERIALIZE_NATIVE),
				));
				$c->save();
			}
		}
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

	/**
		@param team
		@param contestant_id
		@param groups
		@param new optional type=oid
			default is true.
			if is true, no connection is loaded and the data given is returned as must.
			if an connection object oid is given.. then this connection is loaded and old extra info is overwritten.. new data is returned
		@comment
			generates connections extra info to put to the RELTYPE_CONTESTANT relations..
	**/
	function set_relation_data($arr)
	{
		return aw_serialize($arr);
	}
	function get_relation_data($oid)
	{
		$c = new connection($oid);
		return $c->prop("data");
	}

	/**
	**/
	function get_teams($arr = array())
	{
		$filt["class_id"] = CL_SCM_TEAM;
		$list = new object_list($filt);
		return $list->arr();
	}

	function get_team($arr = array())
	{
		$inst = get_instance(CL_SCM_CONTESTANT);
		$comp_inst = get_instance(CL_SCM_COMPETITION);
		$comp_teams = $comp_inst->get_teams_for_competition(array("competition" => $arr["competition"]));
		//arr($inst->get_teams(array("contestant" => $arr["contestant"])));
		foreach($inst->get_teams(array("contestant" => $arr["contestant"])) as $team)
		{
			if(in_array($team, $comp_teams))
			{
				return $team;
			}
		}
	}

	function get_competitions($arr = array())
	{
		$obj = obj($arr["team"]);
		return $obj->prop("competitions");
	}

	/**
		@param team required type=oid
	**/
	function get_team_members($arr = array())
	{
		$c =  new connection();
		$conns = $c->find(array(
			"from" => $arr["team"],
			"to.class_id" => CL_SCM_CONTESTANT,
		));
		foreach($conns as $data)
		{
			$ret[$data["to"]] = obj($data["to"]);
		}
		return $ret;
	}


	// not api

	function _gen_reg_tree($t)
	{
		$inst = get_instance(CL_SCM_ORGANIZER);
		classload("core/icons");
		foreach($inst->get_organizers(array("only_with_competitions" => true)) as $oid => $obj)
		{
			$t->add_item(0, array(
				"id" => "org_".$oid,
				"name" => $obj->name(),
				"url" => "#",
				"iconurl" => icons::get_icon_url(CL_CRM_PERSON),
			));
			$t->add_item("org_".$oid, array(
				"id" => "tmp",
				"name" => $obj->name(),
			));

		}
	}

	/**
		@attrib name=gen_tree_branch all_args=1
	**/
	function gen_tree_branch($arr)
	{
		$self_id = $arr["self_id"];
		$group = $arr["group"];
		$parent = trim($arr["parent"]);
		$split = split("[.]", $parent);
		$parent = $split[0];
		if(substr($parent, 0, 3) == "org")
		{
			$inst = get_instance(CL_SCM_COMPETITION);
			$comps = $inst->get_competitions(array("organizer" => ($organizer = substr($parent, 4))));
			foreach($comps as $oid => $obj)
			{
				$evt = ($s = $inst->get_event(array("competition" => $oid)))?$s:false;
				if(in_array($evt, $evts))
				{
					continue;
				}
				if($evt)
				{
					$evts[] = $evt;
					$evt_obj = obj($evt);
					$tree[] = array(
						"id" => "evt_".$evt.".org_".$organizer,
						"name" => $evt_obj->name(),
					);
				}
				else
				{
					$no_evts[] = $oid;
				}
			}
			// for competitions where the event hasn't specified yet.. oh god i hate this treeview thingie, "someone" should write it to ajax!!
			if(count($no_evts))
			{
				$tree[] = array(
					"id" => "evt_no.org_".$organizer,
					"name" => t("Spordiala m&auml;&auml;ramata"),
				);
			}
		}
		elseif(substr($parent, 0, 3) == "evt")
		{
			$inst = get_instance(CL_SCM_COMPETITION);
			$comps = $inst->get_competitions(array("organizer" => substr($split[1], 4)));
			foreach($comps as $oid => $obj)
			{
				if(($evt_oid = ($s = $inst->get_event(array("competition" => $oid)))?$s:"no") != substr($parent, 4))
				{
					continue;
				}
				$tree[] = array(
					"id" => "cmp_".$oid,
					"name" => $obj->name(),
					"url" => $this->mk_my_orb("change", array(
						"id" => $self_id,
						"competition" => $oid,
						"group" => $group,
					)),
				);
			}
		}

		classload("core/icons");
		$t = get_instance("vcl/treeview");
		$t->start_tree(array(
			"type" => TREE_DHTML,
			"branch" => 1,
			"tree_id" => "reg_tree",
		));
		foreach($tree as $data)
		{
			$t->add_item(0, array(
				"id" => $data["id"],
				"name" => $data["name"],
				"url" => ($data["url"])?$data["url"]:"#",
			));
			if(substr($parent, 0, 3) != "evt")
			{
				$t->add_item($data["id"], array(
					"id" => PI,
				));
			}
		}

		die($t->finalize_tree());
	}
	
	function _gen_members_tbl($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "sex",
			"caption" => t("Sugu"),
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"name" => "rem",
			"field" => "rem_contestant",
		));
		$t->set_default_sortby("name");

	}

	function _gen_members_reg_tbl($t)
	{
		$t->define_field(array(
			"name" => "contestant",
			"caption" => t("Liige"),
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "registered",
			"caption" => t("Registreerunud"),
			"sortable" => true,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "group",
			"caption" => t("V&otilde;istlusklass"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "sex",
			"caption" => t("sugu"),
			"sortable" => true,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "birthday",
			"caption" => t("S&uuml;nniaeg"),
			"sortable" => true,
			"align" => true,
			"callback" => array(&$this, "__birthday_format"),
		));
	}

	function __birthday_format($key, $str, $row)
	{
		if(is_numeric($key))
		{
			return date("d / m / Y", $key);
		}
		return $key;
	}
}
?>
