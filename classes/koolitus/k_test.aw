<?php
/*
@classinfo syslog_type=ST_K_TEST relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_test master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_test
@default group=general



@groupinfo blocks caption="Blokid"
@default group=blocks

	@property blocks_tlb type=toolbar store=no no_caption=1

	@layout blocks_split type=hbox width=25%:75%

		@layout blocks_left type=vbox parent=blocks_split area_caption=Blokid closeable=1

			@property blocks_tree type=treeview no_caption=1 store=no parent=blocks_left

		@layout blocks_right type=vbox parent=blocks_split area_caption=Blokid

			@property blocks_tbl type=table no_caption=1 store=no parent=blocks_right

		@layout questions_right type=vbox parent=blocks_split area_caption=K&uuml;simused

			@property questions_tbl type=table no_caption=1 store=no parent=questions_right


@groupinfo completion_rules caption="L&auml;bimise reeglid"
@default group=completion_rules

	@property completion_toolbar type=toolbar store=no no_caption=1

	@property completion_table type=table store=no no_caption=1



@groupinfo preview caption="Eelvaade"
@default group=preview

	@property show type=text store=no
	@caption K&uuml;simus


@groupinfo entries caption="Sisestused"
@default group=entries

	@property entries_toolbar type=toolbar store=no no_caption=1

	@layout entries_piechart type=vbox closeable=1 area_caption=Sisestuste&nbsp;jaotus&nbsp;anal&uuml;&uuml;si&nbsp;vajalikkuse&nbsp;alusel

		@property entries_piechart type=google_chart store=no no_caption=1 parent=entries_piechart

	@property entries_table type=table store=no no_caption=1

@reltype COMPLETION_RULE value=1 clid=CL_K_TEST_COMPLETION_RULE
@caption L&auml;bimise reegel

*/

class k_test extends class_base
{
	function k_test()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_test",
			"clid" => CL_K_TEST
		));
	}

	public function _get_entries_piechart($arr)
	{
		$needs_analysis = array(0, 0);
		foreach($arr["obj_inst"]->get_all_test_entries() as $entry)
		{
			$needs_analysis[$entry->result ? 1 : 0]++;
		}
		$c = $arr["prop"]["vcl_inst"];
		$c->set_type(GCHART_PIE_3D);
		$c->set_size(array(
			"width" => 600,
			"height" => 200,
		));
		$c->add_data(array(
			$needs_analysis[0],
			$needs_analysis[1]
		));
		$c->set_labels(array(
			sprintf(t("Ei vaja anal&uuml;&uuml;si (%u)"), $needs_analysis[0]),
			sprintf(t("Vajab anal&uuml;&uuml;si (%u)"), $needs_analysis[1]),
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["searched_questions"] = "";
		$arr["block_id"] = automatweb::$request->arg("block_id");
	}

	public function _get_show($arr)
	{
		$arr["prop"]["value"] = $this->show(array(
			"id" => $arr["obj_inst"]->id()
		));
	}

	public function _set_show($arr)
	{
		$test_entry = obj(automatweb::$request->arg("test_entry"));
		$test_entry->save_answer_to_question(
			automatweb::$request->arg("test_question"),
			automatweb::$request->arg("option_using")
		);
	}

	/**
		@attrib name=submit_question
	**/
	public function submit_question($arr)
	{
		$test_entry = obj(automatweb::$request->arg("test_entry"));
		$test_entry->save_answer_to_question(
			automatweb::$request->arg("test_question"),
			automatweb::$request->arg("option_using")
		);

		return aw_url_change_var("test_entry", $test_entry->id(), $arr["post_ru"]);
	}

	function _get_blocks_tlb($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		
		if (automatweb::$request->arg_isset("block_id"))
		{
			$t->add_menu_button(array(
				"name" => "new",
				"img" => "new.gif",
				"tooltip" => t("Uus k&uuml;simus")
			));
			$tree_parent = NULL;
			foreach($arr["obj_inst"]->path() as $parent)
			{
				if($parent->is_a(CL_K_MANAGER))
				{
					$tree_parent = $parent->id();
					break;
				}
			}
			if(is_oid($tree_parent))
			{
				$ot = new object_tree(array(
					"class_id" => array(CL_K_QUESTION_CATEGORY),
					"parent" => $tree_parent,
				));
				foreach($ot->to_list()->arr() as $o)
				{
					if (count($ot->level($o->id())))
					{
						$t->add_sub_menu(array(
							"parent" => $o->parent() == $tree_parent ? "new" : "new_".$o->parent(),
							"name" => "new_".$o->id(),
							"text" => parse_obj_name($o->name()),
						));
					}
					else
					{
						$t->add_menu_item(array(
							"parent" => $o->parent() == $tree_parent ? "new" : "new_".$o->parent(),
							"name" => "new_".$o->id(),
							"text" => parse_obj_name($o->name()),
							"url" => html::get_new_url(CL_K_QUESTION, $o->id(), array(
								"return_url" => get_ru(),
								"pseh" => aw_register_ps_event_handler(
									"k_test",
									"question_add_handler",
									array(
										"test_id" => $arr["obj_inst"]->id(),
										"block_id" => automatweb::$request->arg("block_id")
									),
									CL_K_QUESTION
								)
							))
						));
					}
				}
			}
			$t->add_search_button(array(
				"name" => "search_question",
				"tooltip" => t("Otsi k&uuml;simusi"),
				"pn" => "searched_questions",
				"multiple" => 1,
				"clid" => CL_K_QUESTION,
			));
		}
		else
		{
			$t->add_new_button(array(CL_K_TEST_BLOCK), $arr["obj_inst"]->id);
		}
		$t->add_delete_button();
	}

	function question_add_handler($obj_inst, $params)
	{
		$test = obj($params["test_id"]);
		$block = obj($params["block_id"]);

		$q = obj();
		$q->set_parent($test->id());
		$q->set_class_id(CL_K_TEST_QUESTION);
		$q->test = $test->id();
		$q->block = $block->id();
		$q->question = $obj_inst->id();
		$q->save();
	}

	function _get_blocks_tree($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_BLOCK,
			"parent" => $arr["obj_inst"]->id
		));

		foreach($ol->arr() as $o)
		{
			$t->add_item(0, array(
				"id" => $o->id,
				"name" => $o->name,
				"url" => aw_url_change_var("block_id", $o->id)
			));
		}

		foreach($arr["obj_inst"]->get_test_questions()->arr() as $o)
		{
			$t->add_item($o->block, array(
				"id" => $o->id,
				"name" => $o->prop("question.name"),
				"url" => aw_url_change_var("block_id", $o->block),
				"iconurl" => aw_ini_get("baseurl")."/automatweb/images/icons/qmarks.gif"
			));
		}

		$t->set_selected_item(automatweb::$request->arg("block_id"));
	}

	function _get_blocks_tbl($arr)
	{
		if (!automatweb::$request->arg_isset("block_id"))
		{
			$arr["prop"]["vcl_inst"]->table_from_ol(
				new object_list(array(
					"class_id" => CL_K_TEST_BLOCK,
					"parent" => $arr["obj_inst"]->id
				)),
				array("jrk", "name", "created", "createdby", "modified", "modifiedby"),
				CL_K_TEST_BLOCK
			);
			$arr["prop"]["vcl_inst"]->define_field(array(
				"name" => "max",
				"caption" => t("Maksimum punktid"),
				"align" => "center",
			));
			foreach($arr["prop"]["vcl_inst"]->get_data() as $idx => $data)
			{
				$data["jrk"] = html::textbox(array(
					"name" => "block_jrk[".$data["oid"]."]",
					"size" => 4,
					"value" => obj($data["oid"])->ord(),
				));
				$data["max"] = obj($data["oid"])->get_maximum_points();
				$arr["prop"]["vcl_inst"]->set_data($idx, $data);
			}
		}
		else
		{
			return PROP_IGNORE;
		}
	}

	public function _set_questions_tbl($arr)
	{
		$block = obj(automatweb::$request->arg("block_id"));
		$ol = $block->get_test_questions();
		foreach($ol->arr() as $o)
		{
			$options = $o->get_values();
			foreach($options as $option)
			{
				$user_selection = $arr["request"]["yesno"][$o->id()][$option->id()];
				if ($option->is_yes_no != $user_selection)
				{
					$option->is_yes_no = $user_selection;
					$option->save();
				}
			}
		}
	}

	public function _get_questions_tbl($arr)
	{
		load_javascript("k_test.js");
		if(!automatweb::$request->arg_isset("block_id"))
		{
			return PROP_IGNORE;
		}

		$t = $arr["prop"]["vcl_inst"];
		$t->define_chooser();
		$t->define_field(array(
			"name" => "jrk",
			"caption" => t("J&auml;rjekord"),
			"align" => "center",
			"sortable" => true,
			"sorting_field" => "jrk_num",
		));
		$t->define_field(array(
			"name" => "question",
			"caption" => t("K&uuml;simus"),
			"align" => "center",
			"sortable" => true,
		));
		$t->define_field(array(
			"name" => "options",
			"caption" => t("Valikvastuste v&auml;&auml;rtused"),
			"align" => "center",
			"valign" => "top"
		));
		$t->define_field(array(
			"name" => "ignores",
			"caption" => t("Ignoreerimine"),
			"align" => "center",
			"valign" => "top"
		));

		$block = obj(automatweb::$request->arg("block_id"));
		$ol = $block->get_test_questions();
		foreach($ol->arr() as $o)
		{
			$tbl = $this->get_options_table($o);

			$t->define_data(array(
				"oid" => $o->id(),
				"question" => $o->prop("question.name"),
				"options" => $tbl->draw(),
				"ignores" => $this->draw_ignores_table($arr["obj_inst"], $o),
				"jrk" => html::textbox(array(
					"name" => "question_jrk[".$o->id()."]",
					"size" => 4,
					"value" => $o->ord(),
				)),
				"jrk_num" => $o->ord(),
			));
		}
		$t->set_numeric_field("jrk_num");
	}

	protected function draw_ignores_table($test, $question)
	{
		$t = new vcl_table();
		$t->define_field(array(
			"name" => "rule",
			"caption" => t("Reeglid"),
			"align" => "left",
		));

		$rules = $question->get_ignore_rules();
		$rules[] = obj();
		foreach($rules as $rule)
		{
			$options = is_oid($rule->ignore_question) ? obj($rule->ignore_question)->question()->get_options()->names() : array();
			$t->define_data(array(
				"rule" => sprintf(
					t("Ignoreeri, kui k&uuml;simusele %s vastati %s"), 
					$this->get_question_picker($test, $question, $rule),
					html::select(array(
						"name" => "rule_answer_picker[".$question->id()."][".((int)$rule->id())."]",
						"id" => "rule_answer_picker_".$question->id()."_".((int)$rule->id()),
						"options" => $options,
						"value" => $rule->ignore_option
					))
				)
			));
		}
		return $t->draw();
	}

	protected function get_question_picker($test, $question, $rule)
	{
		$options = array("" => t("--vali--"));
		foreach($test->get_test_questions()->arr() as $test_q)
		{
			$options[$test_q->id()] = $test_q->prop("question.name");
		}
		return html::select(array(
			"name" => "rule_question_picker[".$question->id()."][".((int)$rule->id())."]",
			"id" => "rule_question_picker_".$question->id()."_".((int)$rule->id()),
			"options" => $options,
			"value" => $rule->ignore_question
		));
	}
	
	public function get_options_table($o)
	{
		$options = obj($o->question)->get_options();
		$values = $o->get_values();

		$tbl = new vcl_table;
		$tbl->define_field(array(
			"name" => "option",
			"caption" => t("Valikvastus"),
		));
		$tbl->define_field(array(
			"name" => "value",
			"caption" => t("V&auml;&auml;rtus"),
		));
		$tbl->define_field(array(
			"name" => "yesno",
			"caption" => t("JahEi"),
		));
		foreach($options->arr() as $option)
		{
			$option_using = isset($values[$option->id()]) ? $values[$option->id()] : obj();

			$tbl->define_data(array(
				"option" => $option->name,
				"value" => html::textbox(array(
					"name" => "options[".$o->id()."][".$option->id()."]",
					"value" => $option_using->value,
					"size" => 4,
				)),
				"yesno" => html::radiobutton(array(
					"name" => "yesno[".$o->id()."][".$option_using->id()."]",
					"value" => "1",
					"checked" => $option_using->is_yes_no == 1
				)).t("Jah").html::radiobutton(array(
					"name" => "yesno[".$o->id()."][".$option_using->id()."]",
					"value" => "0",
					"checked" => $option_using->is_yes_no == 0
				)).t("Ei")
			));
		}
		return $tbl;
	}

	public function callback_post_save($arr)
	{
		if(!empty($arr["request"]["searched_questions"]))
		{
			$question_ids = explode(",", $arr["request"]["searched_questions"]);
				
			$ol = new object_data_list(
				array(
					"class_id" => CL_K_TEST_QUESTION,
					"test" => $arr["request"]["id"],
					"question" => $question_ids,
				),
				array(
					CL_K_TEST_QUESTION => array("question", "block")
				)
			);
			$data = array();
			foreach($ol->arr() as $oid => $odata)
			{
				$data[$odata["question"]][$odata["block"]] = $oid;
			}
			
			foreach($question_ids as $question_id)
			{
				if(isset($data[$question_id]))
				{ // Existing question
					if(!isset($data[$question_id][$arr["request"]["block_id"]]))
					{	// Wrong block
						$test_question = obj(reset($data[$question_id]));
						$test_question->block = $arr["request"]["block_id"];
						$test_question->save();
					}
				}
				else
				{	// New question
					$test_question = obj();
					$test_question->set_class_id(CL_K_TEST_QUESTION);
					$test_question->set_parent($arr["request"]["id"]);
					$test_question->name = sprintf(
						t("Testi '%s' k&uuml;simus"), obj($arr["request"]["id"])->name
					);
					$test_question->test = $arr["request"]["id"];
					$test_question->block = $arr["request"]["block_id"];
					$test_question->question = $question_id;
					$test_question->save();
				}
			}
		}

		if(!empty($arr["request"]["options"]))
		{
			foreach($arr["request"]["options"] as $question_id => $option_values)
			{
				$values = obj($question_id)->get_values();
				foreach($option_values as $option_id => $value)
				{
					$value = aw_math_calc::string2float($value);
					if(isset($values[$option_id]))
					{
						if($values[$option_id]->value != $value)
						{
							$values[$option_id]->value = $value;
							$values[$option_id]->save();
						}
					}
					else
					{
						$opion_using = obj();
						$opion_using->set_class_id(CL_K_OPTION_USING);
						$opion_using->set_parent($question_id);
						$opion_using->value = $value;
						$opion_using->test_question = $question_id;
						$opion_using->option = $option_id;
						$opion_using->save();
					}
				}
			}
		}

		if(!empty($arr["request"]["rule_question_picker"]))
		{
			foreach($arr["request"]["rule_question_picker"] as $question_id => $rules)
			{
				$question = obj($question_id);
				$existing_rules = $question->get_ignore_rules();
				foreach($rules as $rule_id => $ignore_question_id)
				{
					if(is_oid($rule_id))
					{
						$this->handle_ignore_rule($arr, $existing_rules[$rule_id], $ignore_question_id);
					}
					else
					{
						$rule = obj();
						$rule->set_class_id(CL_K_IGNORE_RULE);
						$rule->set_parent($question_id);
						$rule->question = $question_id;
						$this->handle_ignore_rule($arr, $rule, $ignore_question_id);
					}
				}
			}
		}

		if(!empty($arr["request"]["block_jrk"]))
		{
			$this->handle_jrk($arr["request"]["block_jrk"]);
		}

		if(!empty($arr["request"]["question_jrk"]))
		{
			$this->handle_jrk($arr["request"]["question_jrk"]);
		}
	}

	protected function handle_jrk($arr)
	{
		foreach($arr as $oid => $jrk)
		{
			$o = obj($oid);
			$o->set_ord($jrk);
			$o->save();
		}
	}

	protected function handle_ignore_rule($arr, $rule, $question_id)
	{
		if(is_oid($question_id) && is_oid($arr["request"]["rule_answer_picker"][$rule->question][(int)$rule->id()]))
		{
			$rule->ignore_question = $question_id;
			$rule->ignore_option = $arr["request"]["rule_answer_picker"][$rule->question][(int)$rule->id()];
			$rule->save();
		}
		elseif(is_oid($rule->id()))
		{
			$rule->delete();
		}
	}

	/**
		@attrib name=get_option_picker_options params=name api=1
		@param k_test_question required type=int
	**/
	public function get_option_picker_options($arr)
	{
		die(json_encode(obj($arr["k_test_question"])->question()->get_options()->names()));
	}

	public function callback_mod_retval($arr)
	{
		if(!empty($arr["request"]["block_id"]))
		{
			$arr["args"]["block_id"] = $arr["request"]["block_id"];
		}
		if(!empty($arr["request"]["test_entry"]))
		{
			$arr["args"]["test_entry"] = $arr["request"]["test_entry"];
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

	public function _get_completion_toolbar($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->add_new_button(array(CL_K_TEST_COMPLETION_RULE), $arr["obj_inst"]->id(), 1);
		$t->add_delete_button();
	}

	public function _get_completion_table($arr)
	{
		$arr["prop"]["vcl_inst"]->table_from_ol(
			new object_list(
				$arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_COMPLETION_RULE"
				))
			),
			array("name", "createdby", "created", "modifiedby", "modified"),
			CL_K_TEST_COMPLETION_RULE
		);
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");

		if(!automatweb::$request->arg_isset("test_entry"))
		{
			$test_entry = $ob->get_new_test_entry();
		}
		else
		{
			$test_entry = obj(automatweb::$request->arg("test_entry"));
		}

		$test_question = $ob->get_next_question($test_entry);
		if(is_object($test_question))
		{
			$OPTIONS = "";
			foreach($test_question->get_values() as $o)
			{
				$this->vars(array(
					"option" => $o->prop("option.name"),
					"option_using_id" => $o->id(),
				));
				$OPTIONS .= $this->parse("OPTIONS");
			}

			$image = "";
			if ($image_id = $test_question->prop("question.q_image"))
			{
				$image = image::make_img_tag_wl($image_id);
			}

			$this->vars(array(
				"block" => $test_question->prop("block.name"),
				"question" => $test_question->prop("question.name"),
				"test_question_id" => $test_question->id(),
				"test_entry_id" => $test_entry->id(),
				"OPTIONS" => $OPTIONS,
				"image" => $image
			));
			$this->vars(array(
				"QUESTION" => $this->parse("QUESTION"),
			));
		}
		else
		{
			$this->vars(array(
				"result" => $test_entry->needs_analysis() ? t("Vajab anal&uuml;&uuml;si") : t("Ei vaja anal&uuml;&uuml;si"),
			));

			$this->vars(array(
				"RESULT" => $this->parse("RESULT"),
			));
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_question",array(
				"post_ru" => get_ru()
			))
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_k_test(aw_oid int primary key)");
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

	public function _get_entries_toolbar($arr)
	{
		$arr["prop"]["vcl_inst"]->add_delete_button();
	}

	public function _get_entries_table($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Millal"),
			"align" => "center",
			"type" => "time",
			"format" => "d.m.Y H:i",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Kes"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "total_points",
			"caption" => t("Punktisumma"),
			"align" => "center",
			"numeric" => 1
		));
		$t->define_field(array(
			"name" => "needs_analysis",
			"caption" => t("Vajab anal&uuml;&uuml;si"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
			"align" => "center"
		));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));

		$t->set_default_sortby("created");
		foreach($arr["obj_inst"]->get_all_test_entries() as $entry)
		{
			$t->define_data(array(
				"created" => $entry->created(),
				"createdby" => $entry->createdby(),
				"total_points" => $entry->total_points,
				"needs_analysis" => $entry->result ? t("Jah") : t("Ei"),
				"oid" => $entry->id(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $entry->id(),
						"return_url" => get_ru()
					), $entry->class_id()),
					"caption" => t("Vaata")
				))
			));
		}
	}
}

?>
