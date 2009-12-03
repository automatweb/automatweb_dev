<?php
/*
@classinfo syslog_type=ST_K_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_manager master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_manager
@default group=general

@groupinfo questions caption="K&uuml;simused"
@default group=questions

	@property questions_tlb type=toolbar store=no no_caption=1

	@layout questions_split type=hbox width=25%:75%

		@layout questions_left type=vbox parent=questions_split area_caption=K&uuml;simuste&nbsp;kategooriad closeable=1

			@property question_categories_tree type=treeview no_caption=1 store=no parent=questions_left

		@layout questions_right type=vbox parent=questions_split 

			@property questions_categories_tbl type=table no_caption=1 store=no parent=questions_right

			@property questions_tbl type=table no_caption=1 store=no parent=questions_right


@groupinfo tests caption="Testid"
@default group=tests

	@property tests_tlb type=toolbar store=no no_caption=1

	@layout tests_split type=hbox width=25%:75%

		@layout tests_left type=vbox parent=tests_split area_caption=Testide&nbsp;kategooriad closeable=1

			@property test_categories_tree type=treeview no_caption=1 store=no parent=tests_left

		@layout tests_right type=vbox parent=tests_split area_caption=Testid

			@property tests_tbl type=table no_caption=1 store=no parent=tests_right
	

*/

class k_manager extends class_base
{
	function k_manager()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_manager",
			"clid" => CL_K_MANAGER
		));
	}

	
	protected function generate_toolbar($arr, $cat_clid, $item_clid)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$clids = array($cat_clid);
		$parent = $arr["obj_inst"]->id;
		if(automatweb::$request->arg_isset("category_id"))
		{
			$o = obj(automatweb::$request->arg("category_id"));
			$parent = $o->id;
			if($o->class_id() == $cat_clid)
			{
				$clids = array($cat_clid, $item_clid);
			}
		}
		$t->add_new_button(
			$clids,
			$parent
		);
		$t->add_delete_button();
	}
	
	private function generate_tree($arr, $clid)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_item(0, array(
			"id" => $arr["obj_inst"]->id(),
			"name" => $arr["obj_inst"]->name(),
			"url" => aw_url_change_var("category_id", $arr["obj_inst"]->id()),
		));
		$ot = new object_tree(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => $clid,
		));
		foreach($ot->to_list()->arr() as $o)
		{
			$t->add_item($o->parent(), array(
				"id" => $o->id(),
				"name" => $o->name(),
				"url" => aw_url_change_var("category_id", $o->id()),
			));
		}
		$t->set_selected_item(automatweb::$request->arg("category_id"));
	}

	public function _get_question_categories_tree($arr)
	{
		$this->generate_tree($arr, CL_K_QUESTION_CATEGORY);
	}

	public function _get_questions_tlb($arr)
	{
		$this->generate_toolbar($arr, CL_K_QUESTION_CATEGORY, CL_K_QUESTION);
	}

	public function _get_questions_categories_tbl($arr)
	{
		$parent = $arr["obj_inst"]->id();
		if (automatweb::$request->arg_isset("category_id"))
		{
			$parent = automatweb::$request->arg("category_id");
		}

		$arr["prop"]["vcl_inst"]->table_from_ol(
			new object_list(array(
				"class_id" => CL_K_QUESTION_CATEGORY,
				"parent" => $parent,
			)),
			array("name", "class_id", "created", "createdby", "modified", "modifiedby"),
			CL_K_QUESTION_CATEGORY
		);
		$arr["prop"]["vcl_inst"]->set_caption(t("K&uuml;simuste kategooriad"));
	}

	public function _get_questions_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
			"width" => "20",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center",
			"sortable" => true,
		));		
		$t->define_field(array(
			"name" => "graph",
			"caption" => t("Graafik"),
			"align" => "center",
			"sortable" => true,
		));


		if(automatweb::$request->arg_isset("category_id"))
		{
			$cat_id = automatweb::$request->arg("category_id");

			$t->set_caption(sprintf(t("K&uuml;simused kategoorias %s"), 
				obj($cat_id)->name()
			));

			$ol = new object_list(array(
				"class_id" => CL_K_QUESTION,
				"parent" => $cat_id,
				new obj_predicate_sort(array(
					"class_id" => "ASC",
				)),
			));
			foreach($ol->arr() as $o)
			{
				$t->define_data(array(
					"oid" => $o->id,
					"name" => html::obj_change_url($o),
					"type" => t("K&uuml;simus"),
					"graph" => $this->get_answer_graph_for_question($o)
				));
			}
		}
	}

	private function get_answer_graph_for_question($question)
	{
		

		$ol = new object_list(array(
			"class_id" => CL_K_TEST_ANSWER,
			"test_question.question" => $question->id()
		));

		$count_by_option = array();
		foreach($ol->arr() as $answer)
		{
			$count_by_option[$answer->prop("option_using.option")]++;
		}

		$c = get_instance("vcl/google_chart");
		$c->set_type(GCHART_PIE);
		$c->set_size(array(
			"width" => 300,
			"height" => 100,
		));

		$c->add_fill(array(
			"area" => GCHART_FILL_BACKGROUND,
			"type" => GCHART_FILL_SOLID,
			"colors" => array(
				array(
					"color" => "e1e1e1",
					"param" => 0.2,
				),
				array(
					"color" => "e1e1e1",
					"param" => 1,
				),
			),
		));

		$data = array();
		$labels = array();
		$options = $question->get_options();
		foreach($options->arr() as $option)
		{
			$data[] = $count_by_option[$option->id()];
			$labels[] = sprintf(t("%s (%s)"),
				$option->name(),
				$count_by_option[$option->id()]
			);
		}
		$c->add_data($data);
		$c->set_labels($labels);
		return $c->get_html();
	}

	public function _get_tests_tlb($arr)
	{
		$this->generate_toolbar($arr, CL_K_TEST_CATEGORY, CL_K_TEST);
	}

	public function _get_test_categories_tree($arr)
	{
		$this->generate_tree($arr, CL_K_TEST_CATEGORY);
	}

	public function _get_tests_tbl($arr)
	{
		if(automatweb::$request->arg_isset("category_id"))
		{
			$t = $arr["prop"]["vcl_inst"];
			$t->table_from_ol(
				new object_list(array(
					"parent" => automatweb::$request->arg("category_id"),
					"class_id" => CL_K_TEST
				)),
				array("name", "created", "createdby", "modified", "modifiedby"),
				CL_K_TEST
			);
			$t->set_default_sortby("created");
			$t->set_default_sorder("desc");
		}
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
			$this->db_query("CREATE TABLE aw_k_manager(aw_oid int primary key)");
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
}

?>
