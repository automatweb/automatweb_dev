<?php
/*
@classinfo syslog_type=ST_PERSON_SKILL_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@tableinfo aw_person_skill_manager master_index=brother_of master_table=objects index=aw_oid

@default table=aw_person_skill_manager
@default group=general

@property company type=relpicker reltype=RELTYPE_COMPANY table=objects field=meta method=serialize
@caption Default tasemed


@groupinfo skills caption="Oskused"
@default group=skills
	@property skills_tb type=toolbar no_caption=1 store=no 

	@property skills_tbl type=table store=no no_caption=1


@groupinfo workers caption="T&ouml;&ouml;tajad"
@default group=workers
	@property workers_tb type=toolbar no_caption=1 store=no 

	@layout workers_layout type=hbox width=20%:80%

		@property workers_tree type=treeview store=no parent=workers_layout no_caption=1
		
		@property workers_tbl type=table store=no parent=workers_layout no_caption=1

@reltype COMPANY value=2 clid=CL_CRM_COMPANY
@caption Tasemed

*/

class person_skill_manager extends class_base
{
	function person_skill_manager()
	{
		$this->init(array(
			"tpldir" => "applications/crm/person_skill_manager",
			"clid" => CL_PERSON_SKILL_MANAGER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_person_skill_manager(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}

	function _get_skills_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => html::get_new_url(CL_PERSON_SKILL, $arr["obj_inst"]->id(), array("return_url" => get_ru())),
		));
		$tb->add_delete_button();
		$tb->add_save_button();
	}

	function _get_workers_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => html::get_new_url(
				CL_PERSON_HAS_SKILL,
				$arr["request"]["cat"]?$arr["request"]["cat"]:$arr["obj_inst"]->id(),
				 array("alias_to" => $arr["request"]["cat"], "reltype" => 53, "return_url" => get_ru())
			),
		));
		$tb->add_delete_button();
		$tb->add_save_button();
	}

	function _get_workers_tree($arr)
	{
		if (!$this->can("view", $arr["obj_inst"]->prop("company")))
		{
			die("Tubade kaust on valimata, palun valige see <a href='/automatweb/orb.aw?class=person_skill_manager&action=change&id=".$arr["obj_inst"]->id()."'>siit</a>");
		}

		$org = obj($arr["obj_inst"]->prop("company"));

		$tree_inst = &$arr['prop']['vcl_inst'];
		$node_id = 0;
		$i = get_instance(CL_CRM_COMPANY);
		$i->active_node = (int)$arr['request']['unit'];
		if(is_oid($arr['request']['cat']))
		{
			$i->active_node = $arr['request']['cat'];
		}
		$i->generate_tree(array(
			'tree_inst' => &$tree_inst,
			'obj_inst' => $org,
			'node_id' => &$node_id,
			'conn_type' => 'RELTYPE_SECTION',
			'attrib' => 'unit',
			'leafs' => true,
			'show_people' =>1 ,
		));

		$nm = t("K&otilde;ik t&ouml;&ouml;tajad");
		$tree_inst->add_item(0, array(
			"id" => CRM_ALL_PERSONS_CAT,
			"name" => $arr["request"]["cat"] == CRM_ALL_PERSONS_CAT ? "<b>".$nm."</b>" : $nm,
			"url" => aw_url_change_var(array(
				"cat" =>  CRM_ALL_PERSONS_CAT,
				"unit" =>  NULL,
			))
		));

/*		if ($_SESSION["crm"]["people_view"] == "edit")
		{
			classload("core/icons");
			$tree_inst->set_root_name($arr["obj_inst"]->name());
			$tree_inst->set_root_icon(icons::get_icon_url(CL_CRM_COMPANY));
			$tree_inst->set_root_url(aw_url_change_var("cat", NULL, aw_url_change_var("unit", NULL)));
		}*/
	}

	function _get_workers_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "skill",
			"caption" => t("P&auml;devus"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "acquired",
			"caption" => t("Omandatud"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "lost",
			"caption" => t("kaotatud"),
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
		if($this->can("view", $arr["request"]["unit"]))
		{
			$cat = obj($arr["request"]["cat"]);
			if($cat->class_id() == CL_CRM_PERSON)
			{
				$skills = $cat->get_skills();
				foreach($skills->arr() as $skill)
				{
					$t->define_data(array(
						"name" => html::get_change_url($skill->id(),array("return_url" => get_ru()),$skill->name()),
						"oid" => $skill->id(),
						"skill" =>  html::get_change_url($skill->prop("skill"),array("return_url" => get_ru()),$skill->prop("skill.name")),
						"acquired" => $skill->prop("skill_acquired")?date("d.m.Y", $skill->prop("skill_acquired")):"",
						"lost" => $skill->prop("skill_lost")?date("d.m.Y", $skill->prop("skill_lost")):"",
					));
				}
			}
			
		}
	}


	function _get_skills_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
/*		$t->define_field(array(
			"name" => "comment",
			"caption" => t("Kommentaar"),
			"sortable" => 1,
		));
*/
		$ol = $arr["obj_inst"]->get_all_skills();
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"name" => html::get_change_url($o->id(),array("returl_url" => get_ru()),$o->name()),
				"oid" => $o->id()
			));
		}
	}

}

?>
