<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_team.aw,v 1.7 2006/08/09 15:06:55 tarvo Exp $
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

	@groupinfo registered caption="V&otilde;istlused" parent=competitions submit=no
		@property registered_tbl type=table no_caption=1 group=registered
		@property registered_list type=table no_caption=1 group=registered
	
	
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
						"sex" => (($s = $pers->prop("gender")) == 1)?t("Mees"):(($s == 2)?t("Naine"):t("Sugu m&auml;&auml;ramata")),
						"company" => ($s = $inst->get_contestant_company(array("contestant" => $oid)))?call_user_method("name", obj($s)):t("Firma &auml;&auml;ramata"),
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
					return PROP_OK;
				}
				$t = &$prop["vcl_inst"];
				$comp = obj($competition);
				$inst = get_instance(CL_SCM_COMPETITION);
				$grps = $inst->get_groups(array(
					"competition" => $competition
				));
				foreach($grps as $gr)
				{
					$o = obj($gr);
					$gr_options[$gr] = $o->name();
				}
				$this->_gen_members_reg_tbl(&$t, $gr_options);
				$t->define_header(sprintf(t("V&otilde;istluse '%s' haldamine"), $comp->name()));

				$cont_inst = get_instance(CL_SCM_CONTESTANT);
				$contestants = array_keys($inst->get_contestants(array("competition" => $competition)));
				$members = $this->get_team_members(array("team" => $arr["obj_inst"]->id()));

				foreach($members as $oid => $obj)
				{
					$reg = (in_array($oid, $contestants))?true:false;
					if($reg)
					{
						$extra_data = $this->_get_extra_data(array(
							"competition" => $competition,
							"contestant" => $oid,
						));
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

					$un = ($reg)?"ch":"";
					$chbox_js_click = "javascript:if(getElementById(\"group_".$oid."\").disabled) getElementById(\"group_".$oid."\").disabled=false; else getElementById(\"group_".$oid."\").disabled=true;";
					$chbox = html::checkbox(array(
						"name" => $un."reg[".$competition."][".$oid."]",
						"checked" => $reg,
						"onclick" => $chbox_js_click,
					));
					$hidd = ($reg)?html::hidden(array(
						"name" => "wreg[".$competition."][".$oid."]",
						"value" => 1,
					)):NULL;
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
						"registered" => $chbox.$hidd,
						"group" => $groups,
						"sex" => (($s = $p_obj->prop("gender")) == 1)?t("Mees"):(($s == 2)?t("Naine"):t("Sugu m&auml;&auml;ramata")),
						"birthday" => (!($s = $p_obj->prop("birthday")) || $s < 0)?t("Pole m&auml;&auml;ratud"):$s,
					));
					unset($extra_data);
				}
			break;

			case "registered_tbl":
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "competition",
					"caption" => t("V&otilde;istlus"),
				));
				$t->define_field(array(
					"name" => "organizer",
					"caption" => t("Korraldaja"),
				));
				
				$memb = $this->get_team_members(array(
					"team" => $arr["obj_inst"]->id(),
				));
				$inst = get_instance(CL_SCM_COMPETITION);
				$comps = $inst->get_competitions(array(
					"contestant" => array_keys($memb),
				));

				foreach($comps as $oid => $obj)
				{
					$o = ($s = $inst->get_organizer(array("competition" => $oid)))?obj($s):false;
					$arg = $arr["request"];
					$arg["competition"] = $oid;
					$url = $this->mk_my_orb("change", $arg);
					$t->define_data(array(
						"competition" => $obj->name()." (<a href=\"".$url."\">osalejad</a>)",
						"organizer" => $o->name(),
					));
				}
			break;
			
			case "registered_list":
				if(!$arr["request"]["competition"])
				{
					return PROP_IGNORE;
				}
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "name",
					"caption" => t("V&otilde;istleja"),
					"sortable" => 1,
				));
				$t->define_field(array(
					"name" => "company",
					"caption" => t("Firma"),
					"sortable" => 1,
				));
				$t->define_field(array(
					"name" => "group",
					"caption" => t("V&otilde;istlusklassid"),
				));
				$header1 = t("V&otilde;istluse '%s' osalejad");
				$header2 = t("Halda nimekirja");
				$arg = $arr["request"];
				$arg["group"] = "registration";
				$url = $this->mk_my_orb("change", $arg);
				$t->define_header(
					sprintf($header1." (<a href=\"%s\">".$header2."</a>)",
						call_user_method("name",obj($arr["request"]["competition"])),
						$url
					)
				);
				$inst =  get_instance(CL_SCM_COMPETITION);
				$contest = $inst->get_contestants(array("competition" => $arr["request"]["competition"]));
				$inst = get_instance(CL_SCM_CONTESTANT);
				foreach($contest as $oid => $data)
				{
					$obj = $data["obj"];
					$extra_data = $this->_get_extra_data(array(
						"competition" => $arr["request"]["competition"],
						"contestant" => $oid,
					));
					unset($groups);
					foreach($extra_data["groups"] as $group)
					{
						$groups[$group] = call_user_method("prop", obj($group), "abbreviation");
					}
					$t->define_data(array(
						"name" => $obj->name(),
						"company" => call_user_method("name", obj($inst->get_contestant_company(array("contestant" => $oid)))),
						"group" => $groups?join(", ", $groups):t("V&otilde;istlusklassid m&auml;&auml;ramata"),
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
		//arr($arr);
		//arr($wreg);
		//die();
		$chreg = $arr["request"]["chreg"];
		$reg = $arr["request"]["reg"];
		$groups = $arr["request"]["gr"];
		$wreg = $arr["request"]["wreg"];
		//(registering new competitors
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
		// unregistering competitors
		foreach($wreg as $competition => $data)
		{
			foreach(array_keys($data) as $contestant)
			{
				if(!$chreg[$competition][$contestant])
				{
					// siin otsime & kustutame seose
					$c = new connection();
					$c = $c->find(array(
						"from" => $competition,
						"to" => $contestant,
						"reltype" => "RELTYPE_CONTESTANT",
					));
					$c = new connection(key($c));
					$c->delete();
				}
			}
		}
		// change group changes
		foreach($chreg as $competition => $data)
		{
			foreach(array_keys($data) as $contestant)
			{
				$c = new connection();
				$extra = $this->_get_extra_data(array(
					"contestant" => $contestant,
					"competition" => $competition,
					"ret_inst" => &$c,
				));
				$extra["groups"] = $groups[$contestant];
				$c->change(array(
					"data" => aw_serialize($extra, SERIALIZE_NATIVE),
				));
				$c->save();
			}
		}
	}

	function callback_mod_retval($arr)
	{
		$url = parse_url($arr["request"]["post_ru"]);
		parse_str($url["query"]);
		$arr["args"]["competition"] = $competition;
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
	**/
	function get_teams($arr = array())
	{
		$filt["class_id"] = CL_SCM_TEAM;
		$list = new object_list($filt);
		return $list->arr();
	}

	/**
		@param competition
		@param contestant
		@comment
			theoretically should find the team according to contestant and competition
	**/
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


	
	/** DEPRECATED
		@param team required type=oid
	**/
	function get_competitions($arr = array())
	{
		$obj = obj($arr["team"]);
		return $obj->prop("competitions");
	}

	/**
		@param competition opitional type=oid
		@param team required type=oid
		@comment
			returns all team members if $competition isn't set, or members who have registered to given competition
	**/
	function get_team_members($arr = array())
	{
		if($arr["competition"])
		{
			$inst = get_instance(CL_SCM_COMPETITION);
			foreach($inst->get_contestants($arr) as $id => $data)
			{
				if($arr["team"] == $data["data"]["team"])
				{
					$ret[$id] = obj($id);
				}
			}
		}
		else
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
		$t->define_field(array(
			"name" => "company",
			"caption" => t("Firma"),
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"name" => "rem",
			"field" => "rem_contestant",
		));
		$t->set_default_sortby("name");

	}

	function _gen_members_reg_tbl($t, $gr_options)
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
			"filter" => $gr_options,
			"filter_compare" => array(&$this, "__group_filter"),
			"callback" => array(&$this, "__group_format"),
		));
		$t->define_field(array(
			"name" => "sex",
			"caption" => t("sugu"),
			"sortable" => true,
			"align" => "center",
			"filter" => array(
				"1" => t("Mees"),
				"2" => t("Naine"),
			),
			"filter_compare" => array(&$this, "__sex_filter"),
		));
		$t->define_field(array(
			"name" => "birthday",
			"caption" => t("S&uuml;nniaeg"),
			"sortable" => true,
			"align" => "center",
			"callback" => array(&$this, "__birthday_format"),
		));
	}

	function __sex_filter($key, $str, $row)
	{
		return in_array($str, $row);
	}

	function __group_format($key, $str, $row)
	{
		return count($key["options"])?html::select($key):t("Klassid m&auml;&auml;ramata");
	}

	function __group_filter($arr, $arr2, $arr3)
	{
		$flip = array_flip($arr3["group"]["options"]);
		if(in_array($flip[$arr2], $arr3["group"]["selected"]))
		{
			return true;
		}
		return false;
	}

	function __birthday_format($key, $str, $row)
	{
		if(is_numeric($key))
		{
			return date("d / m / Y", $key);
		}
		return $key;
	}
	
	/**
		@param competition
		@param contestant
		@param ret_inst optional type=bool
			if set, returning array has an reference to located connection:
			array(
				"ret_inst" => &$connectiom,
			)
		@comment
			finds right registration connection and returns the extra data attached to it
	**/
	function _get_extra_data($arr)
	{
		$c = new connection();
		$conns = $c->find(array(
			"from" => $arr["competition"],
			"to" => $arr["contestant"],
			"reltype" => "RELTYPE_CONTESTANT",
		));
		$id = key($conns);
		$c = new connection($id);
		if($arr["ret_inst"])
		{
			$arr["ret_inst"] = $c;
		}
		$extra_data = aw_unserialize($c->prop("data"));
		return $extra_data;
	}
}
?>
