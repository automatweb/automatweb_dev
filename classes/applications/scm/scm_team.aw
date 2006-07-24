<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/scm/scm_team.aw,v 1.3 2006/07/24 11:43:35 tarvo Exp $
// scm_team.aw - Meeskond 
/*

@classinfo syslog_type=ST_SCM_TEAM relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property competitions type=relpicker reltype=RELTYPE_COMPETITION multiple=1
@caption Osaletud v&otilde;istlused

@groupinfo members caption="Liikmed"
	@default group=members
	
	@property members_tb type=toolbar no_caption=1

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
	

	
@reltype COMPETITION value=1 clid=CL_SCM_COMPETITION
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
				$tb->add_button(array(
					"name" => "add_member",
					"tooltip" => t("Lisa liige"),
					"img" => "new.gif",
				));
				$tb->add_button(array(
					"name" => "search_member",
					"tooltip" => t("Otsi v&otilde;istlejaid"),
					"img" => "search.gif",
				));
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
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "contestant",
					"caption" => t("Liige"),
					"sortable" => true,
				));
				$t->define_field(array(
					"name" => "sex",
					"caption" => t("sugu"),
					"sortable" => true,
				));
				$t->define_field(array(
					"name" => "birthday",
					"caption" => t("S&uuml;nniaeg"),
				));
				$t->define_chooser(array(
					"name" => "unreg",
					"field" => "unreg",
				));

				foreach($this->get_team_members(array("team" => $arr["obj_inst"]->id())) as $oid => $obj)
				{
					$url = $this->mk_my_orb("change",array(
						"class" => "scm_contestant",
						"id" => $oid,
						"return_url" => get_ru(),
					));
					$link = html::href(array(
						"url" => $url,
						"caption" => $obj->name(),
					));

					$t->define_data(array(
						"contestant" => $link,
						"unreg" => $oid,
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
	**/
	function get_team_members($arr = array())
	{
		$cont = get_instance(CL_SCM_CONTESTANT);
		foreach($cont->get_contestants() as $oid => $obj)
		{
			$teams = $obj->prop("teams");
			if(in_array($arr["team"], $teams))
			{
				$ret[$oid] = $obj;
			}
		}
		return $ret;
	}


	// not api

	function _gen_reg_tree($t)
	{
		$inst = get_instance(CL_SCM_ORGANIZER);
		classload("core/icons");
		foreach($inst->get_organizers() as $oid => $obj)
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
				$evt = $inst->get_event(array("competition" => $oid));
				if(in_array($evt, $evts))
				{
					continue;
				}
				$evts[] = $evt;
				$evt_obj = obj($evt);
				$tree[$oid] = array(
					"id" => "evt_".$evt.".org_".$organizer,
					"name" => $evt_obj->name(),
				);
			}
		}
		elseif(substr($parent, 0, 3) == "evt")
		{
			$inst = get_instance(CL_SCM_COMPETITION);
			$comps = $inst->get_competitions(array("organizer" => substr($split[1], 4)));
			foreach($comps as $oid => $obj)
			{
				if(($evt_oid = $inst->get_event(array("competition" => $oid))) != substr($parent, 4))
				{
					continue;
				}
				$evt_obj = obj($evt_oid);
				$tree[$oid] = array(
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
		foreach($tree as $oid => $data)
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
}
?>
