<?php

class project_teams_impl extends class_base
{
	function project_teams_impl()
	{
		$this->init();
	}

	function _get_team_team_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		if ($arr["request"]["team"] == "")
		{
			$tb->add_button(array(
				"name" => "new",
				"img" => "new.gif",
				"tooltip" => t("Tiim"),
				"url" => $this->mk_my_orb("new", array(
					"parent" => $arr["obj_inst"]->id(), 
					"return_url" => get_ru(),
					"alias_to" => $arr["obj_inst"]->id(),
					"reltype" => 21
				), CL_PROJECT_TEAM)
			));
		}

		if ($arr["request"]["team"] != "all_parts")
		{
			$tb->add_button(array(
				"name" => "delete",
				"img" => "delete.gif",
				"action" => "del_team_mem",
				"tooltip" => t("Kustuta"),
			));
		}

		$tb->add_separator();

		$tb->add_button(array(
			"name" => "copy",
			"img" => "copy.gif",
			"action" => "copy_team_mem",
			"tooltip" => t("Kopeeri"),
		));

		if (is_array($_SESSION["proj_team_member_copy"]) && count($_SESSION["proj_team_member_copy"]))
		{
			$tb->add_button(array(
				"name" => "paste",
				"img" => "paste.gif",
				"action" => "paste_team_mem",
				"tooltip" => t("Kleebi"),
			));
		}
	}

	function _get_team_team_tree($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		classload("core/icons");
		$nm = t("Tiimid");
		if ($arr["request"]["team"] == "")
		{
			$nm = "<b>".$nm."</b>";
		}
		$tb->add_item(0, array(
			"name" => $nm,
			"id" => "teams",
			"url" => aw_url_change_var("team", null),
			"iconurl" => icons::get_icon_url(CL_MENU)
		));

		// list all teams from project
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_TEAM")) as $c)
		{
			$nm = $c->prop("to.name");
			if ($arr["request"]["team"] == $c->prop("to"))
			{
				$nm = "<b>".$nm."</b>";
			}
			$tb->add_item("teams", array(
				"name" => $nm,
				"id" => $c->prop("to"),
				"url" => aw_url_change_var("team", $c->prop("to")),
				"iconurl" => icons::get_icon_url(CL_PROJECT_TEAM)
			));
		}
		$nm = t("Projekti meeskond");
		if ($arr["request"]["team"] == "all_parts")
		{
			$nm = "<b>".$nm."</b>";
		}
		$tb->add_item(0, array(
			"name" => $nm,
			"id" => "parts",
			"url" => aw_url_change_var("team", "all_parts"),
			"iconurl" => icons::get_icon_url(CL_MENU)
		));
	}

	function _get_team_team_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$person_list = array();
		if ($arr["request"]["team"] == "all_parts")
		{
			$p = get_instance(CL_PROJECT);
			$person_list = $p->get_team($arr["obj_inst"]);
		}
		else
		if ($this->can("view", $arr["request"]["team"]))
		{
			$to  = obj($arr["request"]["team"]);
			foreach($to->connections_from(array("type" => "RELTYPE_TEAM_MEMBER")) as $c)
			{
				$person_list[$c->prop("to")] = $c->prop("to");
			}
		}
		$co = get_instance(CL_CRM_COMPANY);
		$co->display_persons_table($person_list, &$t);
	}
}
?>