<?php

define("OP_START_BLK", 1);
define("OP_END_BLK", 2);
define("OP_IF_VISIBLE", 3);		// params { a_parent, level, in_parent_tpl }
define("OP_SHOW_ITEM", 4);		// params { tpl (fully qualified name)}

define("OP_LOOP_LIST_BEGIN", 5);		// params { a_parent, level, in_parent_tpl}

// list filter creation
define("OP_LIST_BEGIN", 6);		// params { a_parent, level }
define("OP_LIST_FILTER", 7);	// params { key, value }
define("OP_LIST_END", 8);		// params {}

define("OP_LOOP_LIST_END", 9);	// params { tpl (not-fully qualified name)}

class site_template_compiler extends aw_template
{
	function site_template_compiler()
	{
		$this->init("automatweb/menuedit");
		$this->op_lut = array(
			1 => "OP_START_BLK",
			2 => "OP_END_BLK",
			3 => "OP_IF_VISIBLE",
			4 => "OP_SHOW_ITEM",
			5 => "OP_LOOP_LIST_BEGIN",
			6 => "OP_LIST_BEGIN",
			7 => "OP_LIST_FILTER",
			8 => "OP_LIST_END",
			9 => "OP_LOOP_LIST_END"
		);
	}

	function compile($tpl)
	{
		$this->read_template($tpl);
		$this->parse_template_parts();
		$this->compile_template_parts();
		return "<?php\n".$this->generate_code()."?>";
	}

	////
	// !this parses the template parts into data that the compiler uses
	// so this is sort of a 3-step compilation process
	function parse_template_parts()
	{
		$this->menu_areas = array();

		// get all subtemplates 
		$tpls = $this->get_subtemplates_regex("(MENU.*)");
		
		// now figure out the menu areas that are used
		$_tpls = array();
 		foreach($tpls as $tpl)
		{
			list($tpl) = explode(".", $tpl);
			$_tpls[] = $tpl;
		}
		$tpls = array_unique($_tpls);

		foreach($tpls as $tpl)
		{
			$parts = explode("_", $tpl);
			$area = $parts[1];
			$level = substr($parts[2], 1);

			$this->menu_areas[$area]["levels"][$level]["templates"][] = $parts;
			$this->menu_areas[$area]["parent"] = array_search($area, aw_ini_get("menuedit.menu_defs"));
			// figure out if the template was inside another menu template
			// 	to do that, we get the parent template and check if it has the same menu area and level -1
			$is_in_parent = false;

			$parent_tpl = $this->get_parent_template($tpl);
			if (substr($parent_tpl, 0, 5) == "MENU_")
			{
				$parts = explode("_", $parent_tpl);
				$parent_area = $parts[1];
				$parent_level = substr($parts[2], 1);
				if ($parent_area == $area && ($parent_level+1) == $level)
				{
					$is_in_parent = true;
				}
				// set this template as the parent's sub template
				$this->menu_areas[$parent_area]["levels"][$parent_level]["child_tpls"][$parent_tpl] = array(
					"area" => $area,
					"level" => $level,
					"parts" => $parts
				);
			}
			$this->menu_areas[$area]["levels"][$level]["inside_parent_menu_tpl"] |= $is_in_parent;
		}
	}

	function compile_template_parts()
	{
		// go over all the used templates found 
		// and make a list of script actions to generate code from
		$this->ops = array();
		
		$this->no_top_level_code_for = array();

		foreach($this->menu_areas as $area => $adat)
		{
			foreach($adat["levels"] as $level => $ldat)
			{
				if (isset($this->no_top_level_code_for[$area][$level]))
				{
					continue;
				}

				$this->compile_template_level($area, $adat, $level, $ldat);
			}
		}
	}

	function compile_template_level($area, $adat, $level, $ldat)
	{
		$end_block = false;

		// figure out if we need to determine visibility
		if ($level > 1)
		{
			$this->ops[] = array(
				"op" => OP_IF_VISIBLE,
				"params" => array(
					"a_parent" => $adat["parent"],
					"level" => $level,
					"in_parent_tpl" => $ldat["inside_parent_menu_tpl"]
				)
			);
			$this->ops[] = array(
				"op" => OP_START_BLK,
				"params" => array()
			);
			$end_block = true;
		}

		// now figure out the code for displaying
		// menu items
				

		// go over all different subtemplate
		// combos for this level
		// for each make the appropriate list
		// and then display it
		foreach($ldat["templates"] as $tdat)
		{
			$this->ops[] = array(
				"op" => OP_LIST_BEGIN,
				"params" => array(
					"a_parent" => $adat["parent"],
					"level" => $level,
					"in_parent_tpl" => $ldat["inside_parent_menu_tpl"]
				)
			);

			foreach($tdat as $tpl_opt)
			{
				$params = $this->get_list_filter_from_tpl_opt($tpl_opt);
				if ($params)
				{
					$this->ops[] = array(
						"op" => OP_LIST_FILTER,
						"params" => $params
					);
				}
			}

			$cur_tpl = join("_",$tdat);
			$cur_tpl_fqn = $this->v2_name_map[$cur_tpl];

			$this->ops[] = array(
				"op" => OP_LIST_END,
				"params" => array()
			);

			$this->ops[] = array(
				"op" => OP_LOOP_LIST_BEGIN,
				"params" => array()
			);

			// here we gotta check if we need to 
			// insert the items for the next level in between here. 
			// to do that, we need to check if the next level subtemplate is 
			// inside the current template
			if (isset($ldat["child_tpls"][$cur_tpl]))
			{
				$chd_tpl_dat = $ldat["child_tpls"][$cur_tpl];
				$chd_area = $chd_tpl_dat["area"];
				$chd_lv = $chd_tpl_dat["level"];
				$this->compile_template_level($chd_area, $this->menu_areas[$chd_area], $chd_lv, $this->menu_areas[$chd_area]["levels"][$chd_lv]);
				$this->no_top_level_code_for[$chd_area][$chd_lv] = true;
			}

			$this->ops[] = array(
				"op" => OP_SHOW_ITEM,
				"params" => array(
					"tpl" => $cur_tpl_fqn
				)
			);

			$this->ops[] = array(
				"op" => OP_LOOP_LIST_END,
				"params" => array(
					"tpl" => $cur_tpl
				)
			);
		}

		if ($end_block)
		{
			$this->ops[] = array(
				"op" => OP_END_BLK,
				"params" => array()
			);
		}
	}	

	function get_list_filter_from_tpl_opt($opt)
	{
		switch($opt)
		{
			case "BEGIN":
				return array(
					"key" => "limit",
					"value" => 1
				);
				break;

			case "SEL":
				return array(
					"key" => "oid",
					"value" => "active_page_data::get_active_section()"
				);
				break;
		}

		return false;
	}

	function dbg_show_template_ops()
	{
		foreach($this->ops as $num => $op)
		{
			echo "op $num: { op => ".$this->op_lut[$op["op"]]." , params = { ";
			foreach($op["params"] as $k => $v)
			{
				echo $k ." => ".$v.",";
			}
			echo "} }<br>\n";
		}
		die();
	}

	function generate_code()
	{
		//$this->dbg_show_template_ops();
		$code = "";
		$this->brace_level = 0;
		$this->list_name_stack = array();
		foreach($this->ops as $op)
		{
			$op_name = $this->op_lut[$op["op"]];
			$gen = "_g_".$op_name;
			if (!method_exists($this, $gen))
			{
				error::throw(array(
					"id" => ERR_TPL_COMPILER, 
					"msg" => "show_site::generate_code(): could not find generator for op $op_name ($gen)"
				));
			}

			$code .= $this->$gen($op["params"]);
		}

		return $code;
	}

	function _gi()
	{
		return str_repeat("\t", $this->brace_level);
	}

	function _g_op_start_blk($arr)
	{
		$ret = $this->_gi()."{\n";
		$this->brace_level++;
		return $ret;
	}

	function _g_op_end_blk($arr)
	{
		$this->brace_level--;
		return $this->_gi()."}\n".$this->_gi()."\n";
	}

	function _g_op_if_visible($arr)
	{
		if ($arr["in_parent_tpl"])
		{
			if ($arr["level"] == 2)
			{
				// if the level == 2 and the tpl is in parent, then 
				// it is always shown, because the previous level is 1 and that is always visible.
				// but we can't optimize this out completely, because
				// the next, deeper levels might need this info, so we just set it to true
				$ret  = $this->_gi()."if ((\$this->menu_levels_visible[".$arr["a_parent"]."][".$arr["level"]."] = true) || true)\n";
			}
			else	
			// > 2
			{
				$ret  = $this->_gi()."\$path_level_cnt = \$this->_helper_get_levels_in_path_for_area(".$arr["a_parent"].");\n";
				$ret .= $this->_gi()."if ((\$this->menu_levels_visible[".$arr["a_parent"]."][".$arr["level"]."] = ((\$path_level_cnt+1 >= ".$arr["level"]." ) || (\$this->menu_levels_visible[".$arr["a_parent"]."][".($arr["level"]-1)."]))))\n";
			}
		}
		else
		{
			$ret  = $this->_gi()."\$path_level_cnt = \$this->_helper_get_levels_in_path_for_area(".$arr["a_parent"].");\n";
			$ret .= $this->_gi()."if (\$path_level_cnt+1 >= ".$arr["level"]." )\n";
		}
		return $ret;
	}

	function _g_op_show_item($arr)
	{
		// get the latest list name / o name from the stack
		end($this->list_name_stack);
		$dat = current($this->list_name_stack);
		$list_name = $dat["list_name"];
		$o_name = $dat["o_name"];
		$content_name = $dat["content_name"];

		$ret  = $this->_gi()."\$this->vars(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"text\" => ".$o_name."->name(),\n";
		$ret .= $this->_gi()."\"link\" => \$this->cfg[\"baseurl\"].\"/\".".$o_name."->id(),\n";
		$ret .= $this->_gi()."\"target\" => (".$o_name."->prop(\"target\") ? \"target=\\\"_blank\\\"\" : \"\"),\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		$ret .= $this->_gi().$content_name." .= \$this->parse(\"".$arr["tpl"]."\");\n";
		return $ret;
	}

	function _g_op_list_begin($arr)
	{
		// we can include constants in the code, this will 
		// get executed in aw ...

		// insert new list item in the list name stack
		$list_name = "\$list_".$arr["a_parent"]."_".$arr["level"];
		$o_name = "\$o_".$arr["a_parent"]."_".$arr["level"];
		$content_name = "\$content_".$arr["a_parent"]."_".$arr["level"];

		array_push($this->list_name_stack, array(
			"list_name" => $list_name,
			"o_name" => $o_name,
			"content_name" => $content_name
		));

		$ret  = $this->_gi()."$list_name = new object_list(array(\n";
		$this->brace_level++;
		if ($arr["level"] == 1)
		{
			$ret .= $this->_gi()."\"parent\" => ".$arr["a_parent"].",\n";
		}
		else
		{
			// here find_parent will fail for menus that are shown even if they are not in the path
			// BUT! we don't need to get their id from the path anyway, 
			// because we are inside a loop that has the parent object as the current object!
			// so, we just get it from that!
			if ($arr["in_parent_tpl"])
			{
				$parent_o_name = "\$o_".$arr["a_parent"]."_".($arr["level"]-1);
				$ret .= $this->_gi()."\"parent\" => ".$parent_o_name."->id(),\n";
			}
			else
			{
				$ret .= $this->_gi()."\"parent\" => \$this->_helper_find_parent(".$arr["a_parent"].",".$arr["level"]."),\n";
			}
		}
		$ret .= $this->_gi()."\"class_id\" => CL_PSEUDO,\n";
		$ret .= $this->_gi()."\"status\" => STAT_ACTIVE,\n";
		$ret .= $this->_gi()."\"sort_by\" => \"objects.jrk,objects.created\",\n";
		return $ret;
	}

	function _g_op_list_filter($arr)
	{
		return $this->_gi()."\"".$arr["key"]."\" => ".$arr["value"].",\n";
	}

	function _g_op_list_end($arr)
	{
		end($this->list_name_stack);
		$dat = current($this->list_name_stack);
		$list_name = $dat["list_name"];

		$this->brace_level--;
		$ret  = $this->_gi()."));\n";
		return $ret;
	}

	function _g_op_loop_list_begin($arr)
	{
		// get the latest list name / o name from the stack
		end($this->list_name_stack);
		$dat = current($this->list_name_stack);
		$list_name = $dat["list_name"];
		$o_name = $dat["o_name"];
		$content_name = $dat["content_name"];

		$ret  = $this->_gi().$content_name." = \"\";\n";
		$ret .= $this->_gi()."for(".$o_name." =& ".$list_name."->begin(); !".$list_name."->end(); ".$o_name." =& ".$list_name."->next())\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		return $ret;
	}

	function _g_op_loop_list_end($arr)
	{
		// pop one item off the list name stack
		$dat = array_pop($this->list_name_stack);
		$content_name = $dat["content_name"];

		$this->brace_level--;
		$ret  = $this->_gi()."}\n";
		$ret .= $this->_gi()."\$this->vars(array(\"".$arr["tpl"]."\" => ".$content_name."));\n";
		return $ret;
	}
}
?>
