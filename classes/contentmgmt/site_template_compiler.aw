<?php

define("OP_START_BLK", 1);
define("OP_END_BLK", 2);
define("OP_IF_VISIBLE", 3);			// params { a_parent, level, in_parent_tpl }
define("OP_SHOW_ITEM", 4);			// params { tpl (fully qualified name), has_image_tpl, no_image_tpl}

define("OP_LOOP_LIST_BEGIN", 5);	// params { a_parent, level, in_parent_tpl}

// list filter creation
define("OP_LIST_BEGIN", 6);			// params { a_parent, level, a_parent_p_fn}
define("OP_LIST_FILTER", 7);		// params { prop, value }
define("OP_LIST_END", 8);			// params {}

define("OP_LOOP_LIST_END", 9);		// params { tpl (not-fully qualified name)}

define("OP_IF_BEGIN", 10);			// params {}
define("OP_IF_COND", 11); 			// params {prop, value}
define("OP_IF_END", 12);			// params {}
define("OP_IF_ELSE", 13);			// params {}

define("OP_CHECK_SUBITEMS_SEL", 14);// params { tpl, fq_tpl }

define("OP_AREA_CACHE_CHECK", 15);	// params { a_parent,level,a_name }
define("OP_AREA_CACHE_SET", 16);	// params { a_parent,level }

define("OP_CHECK_NO_SUBITEMS_SEL", 17);// params { tpl, fq_tpl }

define("OP_SHOW_ITEM_INSERT", 18);			// params { tpl (fully qualified name), has_image_tpl, no_image_tpl}

define("OP_INSERT_SEL_IDS", 19);

define("OP_IF_OBJ_TREE", 20);		// params { a_parent, level}
define("OP_GET_OBJ_TREE_LIST", 21);	// params { a_parent, level, a_parent_p_fn}

define("OP_LIST_INIT", 22);	// params { a_parent, level, a_parent_p_fn}

define("OP_HAS_LUGU", 23);	// params { a_parent, level, a_parent_p_fn}

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
			9 => "OP_LOOP_LIST_END",
			10 => "OP_IF_BEGIN",
			11 => "OP_IF_COND", 
			12 => "OP_IF_END",
			13 => "OP_IF_ELSE",
			14 => "OP_CHECK_SUBITEMS_SEL",
			15 => "OP_AREA_CACHE_CHECK",
			16 => "OP_AREA_CACHE_SET",
			17 => "OP_CHECK_NO_SUBITEMS_SEL",
			18 => "OP_SHOW_ITEM_INSERT",
			19 => "OP_INSERT_SEL_IDS",
			20 => "OP_IF_OBJ_TREE",
			21 => "OP_GET_OBJ_TREE_LIST",
			22 => "OP_LIST_INIT",
			23 => "OP_HAS_LUGU",
		);
	}

	function compile($path, $tpl)
	{
		enter_function("site_template_compiler::compile");

		$this->tpl_init($path);

		$this->read_template($tpl);
		$this->tplhash = md5($path.$tpl);

		$this->parse_template_parts();
		$this->compile_template_parts();
		$code =  "<?php\n".$this->generate_code()."?>";

		exit_function("site_template_compiler::compile");
		return $code;
	}

	////
	// !this parses the template parts into data that the compiler uses
	// so this is sort of a 3-step compilation process
	function parse_template_parts()
	{
		$this->menu_areas = array();

		// get all subtemplates 
		$tpls = $this->get_subtemplates_regex("(MENU_.*)");
		
		// now figure out the menu areas that are used
		$_tpls = array();
 		foreach($tpls as $tpl)
		{
			list($tpl) = explode(".", $tpl);
			$_tpls[] = $tpl;
		}
		$tpls = array_unique($_tpls);

		$mdefs = aw_ini_get("menuedit.menu_defs");
		if (aw_ini_get("menuedit.lang_defs") == 1)
		{
			$mdefs = $mdefs[aw_global_get("lang_id")];
		}
		
		foreach($tpls as $tpl)
		{
			$parts = explode("_", $tpl);
			$area = $parts[1];
			$level = substr($parts[2], 1);

			if (!$this->_mf_srch($area, $mdefs))
			{
				continue;
			}

			$this->menu_areas[$area]["levels"][$level]["templates"][] = $parts;
			$this->menu_areas[$area]["parent"] = $this->_mf_srch($area, $mdefs);
			foreach($parts as $part)
			{
				$this->menu_areas[$area]["levels"][$level]["all_opts"][$part] = $part;
			}

			// check if it has HAS_IMAGE subtemplate or NO_IMAGE subtemplate
			if ($this->is_parent_tpl("HAS_IMAGE", $tpl))
			{
				$this->menu_areas[$area]["levels"][$level]["has_image_tpl"] = 1;
			}
			if ($this->is_parent_tpl("NO_IMAGE", $tpl))
			{
				$this->menu_areas[$area]["levels"][$level]["no_image_tpl"] = 1;
			}
			if ($this->is_parent_tpl("HAS_LUGU", $tpl))
			{
				$this->menu_areas[$area]["levels"][$level]["has_lugu"] = 1;
			}

			// figure out if the template was inside another menu template
			// 	to do that, we get the parent template and check if it has the same menu area and level -1
			$is_in_parent = false;

			$parent_tpls = $this->get_parent_templates($tpl);
			foreach($parent_tpls as $parent_tpl)
			{
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
				else
				if (substr($parent_tpl, 0, strlen("HAS_SUBITEMS")) == "HAS_SUBITEMS" || substr($parent_tpl, 0, strlen("NO_SUBITEMS")) == "NO_SUBITEMS")
				{
					// fetch the parent templates for that and try again
					$parent_tpls2 = $this->get_parent_templates($parent_tpl);
					foreach($parent_tpls2 as $parent_tpl2)
					{
						if (substr($parent_tpl2, 0, 5) == "MENU_")
						{
							$parts = explode("_", $parent_tpl2);
							$parent_area = $parts[1];
							$parent_level = substr($parts[2], 1);
							if ($parent_area == $area && ($parent_level+1) == $level)
							{
								$is_in_parent = true;
							}
							// set this template as the parent's sub template
							$this->menu_areas[$parent_area]["levels"][$parent_level]["child_tpls"][$parent_tpl2] = array(
								"area" => $area,
								"level" => $level,
								"parts" => $parts
							);
						}
					}
				}

				if ($found)
				{
				}
				$this->menu_areas[$area]["levels"][$level]["inside_parent_menu_tpl"] |= $is_in_parent;
			}
		}

		// HAS_SUBITEMS_AREA_L1_SEL check - these will go after each level is inserted in the template
		$tpls = $this->get_subtemplates_regex("(HAS_SUBITEMS.*)");
		
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
			$area = $parts[2];
			$p_fqname = $this->v2_name_map[$tpl];
	
			$has_inside = false;

			// check if the no subitems tpl has any menu templates inside it
			foreach($this->v2_name_map as $shname => $fqname)
			{
				if (strlen($fqname) > strlen($p_fqname))
				{
					if (substr($fqname, 0, strlen($p_fqname)) == $p_fqname)
					{
						$has_inside = true;
					}
				}
			}

			$level = substr($parts[3], 1)+1;


			if ($has_inside)
			{
				$this->menu_areas[$area]["levels"][$level]["has_subitems_sel_check"] = true;
				$this->menu_areas[$area]["levels"][$level]["has_subitems_sel_check_tpl"] = $tpl;
				$this->menu_areas[$area]["levels"][$level]["has_subitems_sel_check_tpl_fq"] = $this->v2_name_map[$tpl];
			}
			else
			{
				$this->menu_areas[$area]["levels"][($level-1)]["has_subitems_sel_check_after_item"] = true;
				$this->menu_areas[$area]["levels"][($level-1)]["has_subitems_sel_check_tpl"] = $tpl;
				$this->menu_areas[$area]["levels"][($level-1)]["has_subitems_sel_check_tpl_fq"] = $this->v2_name_map[$tpl];
			}
		}


		// NO_SUBITEMS_AREA_L1 check - these will go after each level is inserted in the template
		$tpls = $this->get_subtemplates_regex("(NO_SUBITEMS.*)");
		
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
			$area = $parts[2];

			$p_fqname = $this->v2_name_map[$tpl];
	
			$has_inside = false;

			// check if the no subitems tpl has any menu templates inside it
			foreach($this->v2_name_map as $shname => $fqname)
			{
				if (strlen($fqname) > strlen($p_fqname))
				{
					if (substr($fqname, 0, strlen($p_fqname)) == $p_fqname)
					{
						$has_inside = true;
					}
				}
			}

			$level = substr($parts[3], 1)+1;
			
			if ($has_inside)
			{
				$this->menu_areas[$area]["levels"][$level]["no_subitems_sel_check"] = true;
				$this->menu_areas[$area]["levels"][$level]["no_subitems_sel_check_tpl"] = $tpl;
				$this->menu_areas[$area]["levels"][$level]["no_subitems_sel_check_tpl_fq"] = $this->v2_name_map[$tpl];
			}
			else
			{
				$this->menu_areas[$area]["levels"][($level-1)]["no_subitems_sel_check_after_item"] = true;
				$this->menu_areas[$area]["levels"][($level-1)]["no_subitems_sel_check_tpl"] = $tpl;
				$this->menu_areas[$area]["levels"][($level-1)]["no_subitems_sel_check_tpl_fq"] = $this->v2_name_map[$tpl];
			}
		}
	}

	function _mf_srch($area, $defs)
	{
		foreach($defs as $mdid => $md)
		{
			if (in_array($area, explode(",", $md)))
			{
				return $mdid;
			}
		}
		return false;
	}

	function compile_template_parts()
	{
		// go over all the used templates found 
		// and make a list of script actions to generate code from
		$this->ops = array();
		
		$this->no_top_level_code_for = array();

		$this->ops[] = array(
			"op" => OP_INSERT_SEL_IDS,
			"params" => array(
				"data" => $this->menu_areas
			)
		);

		$this->req_level = 0;

		foreach($this->menu_areas as $area => $adat)
		{
			if ($area == "LOGGED")
			{
				$adat["a_parent_p_fn"] = "\$this->_helper_get_login_menu_id()";
			}
			else
			{
				$adat["a_parent_p_fn"] = $adat["parent"];
			}

			ksort($adat["levels"]);
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
		if (!is_array($ldat["templates"]))
		{
			return;
		}

		$this->req_level ++;

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


		if ($this->req_level == 1 && aw_ini_get("template_compiler.no_menu_area_cache") != 1)
		{		
			$this->ops[] = array(
				"op" => OP_AREA_CACHE_CHECK,
				"params" => array(
					"a_parent" => $adat["parent"],
					"level" => $level,
					"a_name" => $area
				)
			);
		}

		// now figure out the code for displaying
		// menu items

		// go over all different subtemplate
		// combos for this level
		// for each make the appropriate list
		// and then display it
		// 
		// that was all nice and dandy, except for the littel detail that it *didn't fucking work*
		// problem was - if you had a _sel template, then the selected menu has to use that
		// and it might be in the middle somewhere, and then the different-list-for-each-option-combo broke down
		// so now we do the one-list-per-area-level and then if's to match the correct template to the current obj

		// if the menu has the object_tree property set, the list has to come from the object tree, so check

		$this->ops[] = array(
			"op" => OP_LIST_INIT,
			"params" => array(
				"a_parent" => $adat["parent"],
				"a_parent_p_fn" => $adat["a_parent_p_fn"],
				"level" => $level,
				"in_parent_tpl" => $ldat["inside_parent_menu_tpl"]
			)
		);

		$this->ops[] = array(
			"op" => OP_IF_OBJ_TREE,
			"params" => array(
				"a_parent" => $adat["parent"],
				"level" => $level
			)
		);

		$this->ops[] = array(
			"op" => OP_START_BLK,
			"params" => array()
		);

		$this->ops[] = array(
			"op" => OP_LIST_BEGIN,
			"params" => array(
				"a_parent" => $adat["parent"],
				"a_parent_p_fn" => $adat["a_parent_p_fn"],
				"level" => $level,
				"in_parent_tpl" => $ldat["inside_parent_menu_tpl"]
			)
		);

		$this->ops[] = array(
			"op" => OP_LIST_END,
			"params" => array()
		);

		$this->ops[] = array(
			"op" => OP_END_BLK,
			"params" => array()
		);

		$this->ops[] = array(
			"op" => OP_IF_ELSE,
			"params" => array()
		);
		
		$this->ops[] = array(
			"op" => OP_START_BLK,
			"params" => array()
		);

		$this->ops[] = array(
			"op" => OP_GET_OBJ_TREE_LIST,
			"params" => array(
				"a_parent" => $adat["parent"],
				"a_parent_p_fn" => $adat["a_parent_p_fn"],
				"level" => $level,
				"in_parent_tpl" => $ldat["inside_parent_menu_tpl"]
			)
		);

		$this->ops[] = array(
			"op" => OP_END_BLK,
			"params" => array()
		);


		$this->ops[] = array(
			"op" => OP_LOOP_LIST_BEGIN,
			"params" => array()
		);

		$tt = $ldat["templates"];		
		usort($tt, create_function('$a, $b','$ca = count($a); $cb = count($b); if ($ca == $cb) { return 0; }else if ($ca > $cb) { return -1; }else{return 1;}'));
		// right, now we gots to figure out the template
		// we do that by trying to match the current object to any template on this area on this level
		foreach($tt as $idx => $tdat)
		{
			if ($idx > 0)
			{
				$this->ops[] = array(
					"op" => OP_IF_ELSE,
					"params" => array()
				);
			}

			$cur_tpl = join("_",$tdat);
			$cur_tpl_fqn = $this->v2_name_map[$cur_tpl];

			$this->ops[] = array(
				"op" => OP_IF_BEGIN,
				"params" => array()
			);

			$no_display_item = false;
			$has_sel = false;
			foreach($tdat as $tpl_opt)
			{
				$params = $this->get_if_filter_from_tpl_opt($tpl_opt, $ldat["all_opts"]);
				if ($params)
				{
					$params["a_parent"] = $adat["parent"];
					$params["level"] = $level;
					$this->ops[] = array(
						"op" => OP_IF_COND,
						"params" => $params
					);
					$no_display_item |= $params["no_display_item"];
				}
				if ($tpl_opt == "SEL")
				{
					$has_sel = true;
				}
			}

			$this->ops[] = array(
				"op" => OP_IF_END,
				"params" => array()
			);
			
			$this->ops[] = array(
				"op" => OP_START_BLK,
				"params" => array()
			);

			if (!$no_display_item)
			{
				// here we gotta check if we need to 
				// insert the items for the next level in between here. 
				// to do that, we need to check if the next level subtemplate is 
				// inside the current template
				if (isset($ldat["child_tpls"][$cur_tpl]))
				{
					$chd_tpl_dat = $ldat["child_tpls"][$cur_tpl];
					$chd_area = $chd_tpl_dat["area"];
					$chd_lv = $chd_tpl_dat["level"];
					if (!($chd_area == $area && $chd_lv == $level))
					{
						$this->compile_template_level($chd_area, $this->menu_areas[$chd_area], $chd_lv, $this->menu_areas[$chd_area]["levels"][$chd_lv]);
					}
					$this->no_top_level_code_for[$chd_area][$chd_lv] = true;
				}

				$this->ops[] = array(
					"op" => OP_HAS_LUGU,
					"params" => array(
						"tpl" => $cur_tpl_fqn,
						"has_image_tpl" => $ldat["has_image_tpl"],
						"no_image_tpl" => $ldat["no_image_tpl"],
					)
				);
				
				$this->ops[] = array(
					"op" => OP_SHOW_ITEM,
					"params" => array(
						"tpl" => $cur_tpl_fqn,
						"has_image_tpl" => $ldat["has_image_tpl"],
						"no_image_tpl" => $ldat["no_image_tpl"],
					)
				);

				if ($ldat["has_subitems_sel_check_after_item"])
				{
					$this->ops[] = array(
						"op" => OP_CHECK_SUBITEMS_SEL,
						"params" => array(
							"tpl" => $ldat["has_subitems_sel_check_tpl"],
							"fq_tpl" => $cur_tpl_fqn.".".$ldat["has_subitems_sel_check_tpl"],
							"a_parent" => $adat["parent"],
							"level" => $level+1
						)
					);
				}

				if ($ldat["no_subitems_sel_check_after_item"]  && $has_sel)
				{
					$this->ops[] = array(
						"op" => OP_CHECK_NO_SUBITEMS_SEL,
						"params" => array(
							"tpl" => $ldat["no_subitems_sel_check_tpl"],
							"fq_tpl" => $ldat["no_subitems_sel_check_tpl_fq"]//$cur_tpl_fqn.".".$ldat["no_subitems_sel_check_tpl"],
						)
					);
				}

				$this->ops[] = array(
					"op" => OP_SHOW_ITEM_INSERT,
					"params" => array(
						"tpl" => $cur_tpl_fqn,
						"has_image_tpl" => $ldat["has_image_tpl"],
						"no_image_tpl" => $ldat["no_image_tpl"],
					)
				);
			}

			$this->ops[] = array(
				"op" => OP_END_BLK,
				"params" => array()
			);
		}

		if ($this->req_level == 1  && aw_ini_get("template_compiler.no_menu_area_cache") != 1)
		{
			$this->ops[] = array(
				"op" => OP_AREA_CACHE_SET,
				"params" => array(
					"a_parent" => $adat["parent"],
					"level" => $level,
					"a_name" => $area
				)
			);
		}

		$this->ops[] = array(
			"op" => OP_LOOP_LIST_END,
			"params" => array(
				"tpl" => $cur_tpl
			)
		);

		if ($ldat["has_subitems_sel_check"])
		{
			$this->ops[] = array(
				"op" => OP_CHECK_SUBITEMS_SEL,
				"params" => array(
					"tpl" => $ldat["has_subitems_sel_check_tpl"],
					"fq_tpl" => $ldat["has_subitems_sel_check_tpl_fq"],
				)
			);
		}

		if ($ldat["no_subitems_sel_check"])
		{
			$this->ops[] = array(
				"op" => OP_CHECK_NO_SUBITEMS_SEL,
				"params" => array(
					"tpl" => $ldat["no_subitems_sel_check_tpl"],
					"fq_tpl" => $ldat["no_subitems_sel_check_tpl_fq"]
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

		$this->req_level --;
	}	

	function get_if_filter_from_tpl_opt($opt, $all_opts)
	{
		switch($opt)
		{
			case "BEGIN":
				return array(
					"prop" => "loop_counter",
					"value" => "0"
				);
				break;

			case "END":
				return array(
					"prop" => "loop_counter",
					"value" => "list_end"
				);
				break;

			case "SEL":
				return array(
					"prop" => "oid",
					"value" => "is_in_path"
				);
				break;

			case "SEP":
				return array(
					"prop" => "clickable",
					"value" => "0"
				);
				break;

			case "MID":
				return array(
					"prop" => "mid",
					"value" => "1"
				);
				break;

			case "NOTACT":
				return array(
					"prop" => "level_selected",
					"value" => "not_in_path",
					"no_display_item" => true
				);
				break;

			case "FPONLY":
				return array(
					"prop" => "frontpage",
					"value" => "1"
				);
				break;
			default:
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
					"msg" => "show_site::generate_code(): could not find generator for op $op_name ($gen) op = ".$op["op"]
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
		$inst_name = $dat["inst_name"];
		$fun_name = $dat["fun_name"];

		$ret  = $this->_gi()."\$this->vars(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"text\" => ".$o_name."->name(),\n";
		$ret .= $this->_gi()."\"link\" => ".$inst_name."->".$fun_name."($o_name),\n";
		$ret .= $this->_gi()."\"target\" => (".$o_name."->prop(\"target\") ? \"target=\\\"_blank\\\"\" : \"\"),\n";
		$ret .= $this->_gi()."\"section\" => ".$o_name."->id(),\n";
		$ret .= $this->_gi()."\"colour\" => ".$o_name."->prop(\"color\"),\n";
		$ret .= $this->_gi()."\"comment\" => ".$o_name."->comment(),\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
	
		if ($arr["has_image_tpl"] || $arr["no_image_tpl"])
		{
			$ret .= $this->_gi()."\$has_images = false;\n";
		}
		
		// do menu images
		$n_img = aw_ini_get("menuedit.num_menu_images");
		$ret .= $this->_gi()."\$img = ".$o_name."->meta(\"menu_images\");\n";
		$ret .= $this->_gi()."if (is_array(\$img) && count(\$img) > 0)\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		
		$ret .= $this->_gi()."for(\$i = 0; \$i < $n_img; \$i++)\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;

		$ret .= $this->_gi()."if (\$img[\$i][\"image_id\"])\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;

		$ret .= $this->_gi()."\$img[\$i][\"url\"] = \$this->image->get_url_by_id(\$img[\$i][\"image_id\"]);\n";
	
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";

		$ret .= $this->_gi()."if (!empty(\$img[\$i][\"url\"]))\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\$imgurl = image::check_url(\$img[\$i][\"url\"]);\n";
		$ret .= $this->_gi()."\$this->vars(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"menu_image_\".\$i => \"<img src='\".\$imgurl.\"'>\",\n";
		$ret .= $this->_gi()."\"menu_image_\".\$i.\"_url\" => \$imgurl\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		if ($arr["has_image_tpl"] || $arr["no_image_tpl"])
		{
			$ret .= $this->_gi()."\$has_images = true;\n";
		}
			
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";
		
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";
		
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";

		if ($arr["has_image_tpl"])
		{
			$ret .= $this->_gi()."if (\$has_images)\n";
			$ret .= $this->_gi()."{\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\$this->vars(array(\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\"HAS_IMAGE\" => \$this->parse(\"".$arr["tpl"].".HAS_IMAGE\")\n";
			$this->brace_level--;
			$ret .= $this->_gi()."));\n";
			$this->brace_level--;
			$ret .= $this->_gi()."}\n";
			$ret .= $this->_gi()."else\n";
			$ret .= $this->_gi()."{\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\$this->vars(array(\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\"HAS_IMAGE\" => \"\"\n";
			$this->brace_level--;
			$ret .= $this->_gi()."));\n";
			$this->brace_level--;
			$ret .= $this->_gi()."}\n";
		}

		if ($arr["no_image_tpl"])
		{
			$ret .= $this->_gi()."if (!\$has_images)\n";
			$ret .= $this->_gi()."{\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\$this->vars(array(\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\"NO_IMAGE\" => \$this->parse(\"".$arr["tpl"].".NO_IMAGE\")\n";
			$this->brace_level--;
			$ret .= $this->_gi()."));\n";
			$this->brace_level--;
			$ret .= $this->_gi()."}\n";
			$ret .= $this->_gi()."else\n";
			$ret .= $this->_gi()."{\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\$this->vars(array(\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\"NO_IMAGE\" => \"\"\n";
			$this->brace_level--;
			$ret .= $this->_gi()."));\n";
			$this->brace_level--;
			$ret .= $this->_gi()."}\n";
		}

		return $ret;
	}

	function _g_op_show_item_insert($arr)
	{
		end($this->list_name_stack);
		$dat = current($this->list_name_stack);
		$content_name = $dat["content_name"];
		$o_name = $dat["o_name"];

		$ret = "";
		// TODO: this could be optimized out for non - login menus
		$ret .= $this->_gi()."if (!(\$this->skip || (".$o_name."->prop(\"users_only\") && aw_global_get(\"uid\") == \"\")))\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi().$content_name." .= \$this->parse(\"".$arr["tpl"]."\");\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";
		return $ret;
	}

	function _g_op_list_begin($arr)
	{
		end($this->list_name_stack);
		$dat = current($this->list_name_stack);
		$list_name = $dat["list_name"];

		$ret  .= $this->_gi()."\$__list_filter = array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"parent\" => \$parent_obj->id(),\n";
		$ret .= $this->_gi()."\"class_id\" => array(CL_PSEUDO,CL_BROTHER),\n";
		$ret .= $this->_gi()."\"status\" => STAT_ACTIVE,\n";

		$ret .= $this->_gi()."new object_list_filter(array(\n";

		$this->brace_level++;
		$ret .= $this->_gi()."\"logic\" => \"OR\",\n";
		$ret .= $this->_gi()."\"conditions\" => array(\n";

		$this->brace_level++;
		$ret .= $this->_gi()."\"lang_id\" => aw_global_get(\"lang_id\"),\n";
		$ret .= $this->_gi()."\"type\" => MN_CLIENT\n";
		$this->brace_level--;

		$ret .= $this->_gi().")\n";
	
		$this->brace_level--;
		$ret .= $this->_gi().")),\n";
		$ret .= $this->_gi()."\"lang_id\" => array(),\n";
		$ret .= $this->_gi()."\"sort_by\" => (\$parent_obj->prop(\"sort_by_name\") ? \"objects.name\" : \"objects.jrk,objects.created\"),\n";
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
		$inst_name = $dat["inst_name"];
		$fun_name = $dat["fun_name"];

		$ret = "";
		$this->brace_level--;
		$ret .= $this->_gi().");\n";	


		$ret .= $this->_gi()."if (aw_global_get(\"uid\") == \"\")\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi()."; \$__list_filter[\"users_only\"] = 0;\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";

		$ret .= $this->_gi()."$list_name = new object_list(\$__list_filter);\n";

		$ret .= $this->_gi()."$inst_name =& \$this;\n";		
		$ret .= $this->_gi()."$fun_name = \"make_menu_link\";\n";
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
		$loop_counter_name = $dat["loop_counter_name"];

		$ret = "";
		$ret .= $this->_gi().$content_name." = \"\";\n";
		$ret .= $this->_gi()."for(".$o_name." =& ".$list_name."->begin(), ".$loop_counter_name." = 0; !".$list_name."->end(); ".$o_name." =& ".$list_name."->next(),".$loop_counter_name."++)\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;

			// add hide_noact check
			$ret .= $this->_gi()."if (".$o_name."->prop(\"hide_noact\") == 1)\n";
			$ret .= $this->_gi()."{\n";
				$this->brace_level++;
				$ret .= $this->_gi()."if (aw_global_get(\"act_per_id\") > 1)\n";
				$ret .= $this->_gi()."{\n";
					$this->brace_level++;
					$ret .= $this->_gi()."\$_tmp = ".$o_name."->meta(\"active_documents_p\");\n";
					$ret .= $this->_gi()."if (!is_array(\$_tmp[aw_global_get(\"act_per_id\")]) || count(\$_tmp[aw_global_get(\"act_per_id\")]) < 1)\n";
					$ret .= $this->_gi()."{\n";
						$this->brace_level++;
						$ret .= $this->_gi()."continue;\n";
						$this->brace_level--;
					$ret .= $this->_gi()."}\n";
					$this->brace_level--;
				$ret .= $this->_gi()."}\n";

		$ret .= $this->_gi()."else\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;

		$ret .= $this->_gi()."\$_tmp = ".$o_name."->meta(\"active_documents\");\n";
		$ret .= $this->_gi()."if (!is_array(\$_tmp) || count(\$_tmp) < 1)\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi()."continue;\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";

		return $ret;
	}

	function _g_op_loop_list_end($arr)
	{
		// pop one item off the list name stack
		$dat = array_pop($this->list_name_stack);
		$content_name = $dat["content_name"];
		$this->last_list_dat = $dat;

		$this->brace_level--;
		$ret  = $this->_gi()."}\n";
		$ret .= $this->_gi()."\$this->vars(array(\"".$arr["tpl"]."\" => ".$content_name."));\n";
		return $ret;
	}

	function _g_op_if_begin($arr)
	{
		$ret = $this->_gi()."if (";
		return $ret;
	}

	function _g_op_if_cond($arr)
	{
		end($this->list_name_stack);
		$dat = current($this->list_name_stack);
		$o_name = $dat["o_name"];
		$loop_counter_name = $dat["loop_counter_name"];
		$list_name = $dat["list_name"];

		if ($arr["prop"] == "level_selected")
		{
			if ($arr["value"] == "not_in_path")
			{
				$ret = "((\$this->_helper_get_levels_in_path_for_area(".$arr["a_parent"].") >= ".$arr["level"].") && !\$this->_helper_is_in_path(".$o_name."->id()) && \$this->_helper_is_in_path(".$o_name."->parent())) && ";
			}
		}
		else
		if ($arr["prop"] == "loop_counter")
		{
			if ($arr["value"] == "list_end")
			{
				$ret = "(".$loop_counter_name." == (".$list_name."->count()-1)) && ";
			}
			else
			{
				$ret = "(".$loop_counter_name." == ".$arr["value"].") && ";
			}
		}
		else
		if ($arr["prop"] == "oid")
		{
			if ($arr["value"] == "is_in_path")
			{
				$ret = "(\$this->_helper_is_in_path(".$o_name."->id())) && ";
			}
			else
			{
				$ret = "(".$o_name."->id() == ".$arr["value"].") && ";
			}
		}
		else
		{
			$ret = "(".$o_name."->prop(\"".$arr["prop"]."\") == \"".$arr["value"]."\") && ";
		}
		return $ret;
	}

	function _g_op_if_end($arr)
	{
		$ret = " true )\n";
		return $ret;
	}

	function _g_op_if_else($arr)
	{
		$ret = $this->_gi()."else\n";
		return $ret;
	}

	function _g_op_check_subitems_sel($arr)
	{
		$ret = "";
		if (isset($arr["a_parent"]))
		{
			$content_name = "\$content_".$arr["a_parent"]."_".$arr["level"];
		}
		else
		{
			$dat = $this->last_list_dat;
			$list_name = $dat["list_name"];
			$content_name = $dat["content_name"];
		}

		$ret .= $this->_gi()."if (".$content_name." != \"\")\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\$this->vars(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"".$arr["tpl"]."\" => \$this->parse(\"".$arr["fq_tpl"]."\")\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";

		$ret .= $this->_gi()."else\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\$this->vars(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"".$arr["tpl"]."\" => \"\"\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";

		return $ret;
	}

	function _g_op_check_no_subitems_sel($arr)
	{
		$ret = "";
		$dat = $this->last_list_dat;
		$list_name = $dat["list_name"];
		$content_name = $dat["content_name"];
		if ($content_name == "")
		{
			// get it from the current stack
			end($this->list_name_stack);
			$dat = current($this->list_name_stack);
			$list_name = $dat["list_name"];
			$content_name = $dat["content_name"];
		}

		$ret .= $this->_gi()."if (".$content_name." == \"\")\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\$this->vars(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"".$arr["tpl"]."\" => \$this->parse(\"".$arr["fq_tpl"]."\")\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";

		$ret .= $this->_gi()."else\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\$this->vars(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"".$arr["tpl"]."\" => \"\"\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";
		return $ret;
	}

	function _g_op_area_cache_check($arr)
	{
		// assumes cache inst of $this->cache
		$content_name = "\$content_".$arr["a_parent"]."_".$arr["level"];

		$res = "";
		$res .= $this->_gi()."if ((".$content_name." = \$this->cache->file_get_ts(\"site_show_menu_area_cache_tpl_".$this->tplhash."_lid_\".aw_global_get(\"lang_id\").\"_section_\".aw_global_get(\"section\").\"_".$arr["a_name"]."_level_".$arr["level"]."_uid_\".aw_global_get(\"uid\"),\$this->_helper_get_objlastmod())) == \"\")\n";
		$res .= $this->_gi()."{\n";
		$this->brace_level++;
		return $res;
	}

	function _g_op_area_cache_set($arr)
	{
		$dat = current($this->list_name_stack);
		$content_name = $dat["content_name"];
		$cache_name = $dat["cache_name"];

		$res = "";

		$res .= $this->_gi()."if (".$cache_name.")\n";
		$res .= $this->_gi()."{\n";
		$this->brace_level++;
		$res .= $this->_gi()."\$this->cache->file_set(\"site_show_menu_area_cache_tpl_".$this->tplhash."_lid_\".aw_global_get(\"lang_id\").\"_section_\".aw_global_get(\"section\").\"_".$arr["a_name"]."_level_".$arr["level"]."_uid_\".aw_global_get(\"uid\"), ".$content_name.");\n";
		$this->brace_level --;
		$res .= $this->_gi()."}\n";
		$this->brace_level --;
		$res .= $this->_gi()."}\n";

		return $res;
	}

	function _g_op_insert_sel_ids($arr)
	{
		$res = "";

		$dat = $arr["data"];
		foreach($dat as $area => $adat)
		{
			if (!$adat["parent"])
			{
				continue;
			}

			$res .= $this->_gi()."if (\$this->_helper_get_levels_in_path_for_area(".$adat["parent"].") > 0)\n";
			$res .= "{\n";
			$this->brace_level++;

			$res .= $this->_gi()."\$vars = array();\n";

			foreach($adat["levels"] as $level => $ldat)
			{
				$res .= $this->_gi()."\$tmp = \$this->_helper_find_parent(".$adat["parent"].", ".($level+1).");\n";
				$res .= $this->_gi()."if (\$tmp)\n";
				$res .= $this->_gi()."{\n";
				$this->brace_level++;

				$res .= $this->_gi()."\$vars[\"sel_menu_".$area."_L".$level."_id\"] = \$tmp;\n";
				$res .= $this->_gi()."\$tmp_o = obj(\$tmp);\n";
				$res .= $this->_gi()."\$vars[\"sel_menu_".$area."_L".$level."_text\"] = \$tmp_o->name();\n";
				$res .= $this->_gi()."\$vars[\"sel_menu_".$area."_L".$level."_colour\"] = \$tmp_o->prop(\"color\");\n";
				$res .= $this->_gi()."\$tmp_im = \$tmp_o->meta(\"menu_images\");\n";
				// insert image urls
				$ni = aw_ini_get("menuedit.num_menu_images");
				for($i = 0; $i < $ni; $i++)
				{
					$res .= $this->_gi()."if (\$tmp_im[$i][\"image_id\"])\n";
					$res .= $this->_gi()."{\n";
					$this->brace_level++;

					$res .= $this->_gi()."\$tmp_im[$i][\"url\"] = \$this->image->get_url_by_id(\$tmp_im[$i][\"image_id\"]);\n";
	
					$this->brace_level--;
					$res .= $this->_gi()."}\n";
					$res .= $this->_gi()."\$vars[\"sel_menu_".$area."_L".$level."_image_".$i."_url\"] = image::check_url(\$tmp_im[".$i."][\"url\"]);\n";
					$res .= $this->_gi()."\$vars[\"sel_menu_".$area."_L".$level."_image_".$i."\"] = image::make_img_tag(image::check_url(\$tmp_im[".$i."][\"url\"]));\n";
				}
				$this->brace_level--;
				$res .= $this->_gi()."}\n";

			}

			$res .= $this->_gi()."\$this->vars(\$vars);\n";

			$this->brace_level--;
			$res .= $this->_gi()."}\n";
		}

		return $res;
	}

	function _g_op_if_obj_tree($arr)
	{
		$ret = "";

		$o_name = "\$o_".$arr["a_parent"]."_".$arr["level"];

		$p_v_name = "\$ot_".$arr["a_parent"]."_".($arr["level"] > 0 ? $arr["level"]-1 : $arr["level"]);
		
		$add = "";
		if ($arr["level"] > 0)
		{
			$add = " || (".$p_v_name.")";
		}
	
		$ret .= $this->_gi()."if (!(\$parent_obj->prop(\"show_object_tree\") $add))\n";
		return $ret;
	}

	function _g_op_get_obj_tree_list($arr)
	{
		$ret = "";

		end($this->list_name_stack);
		$dat = current($this->list_name_stack);
		$list_name = $dat["list_name"];
		$inst_name = $dat["inst_name"];
		$fun_name = $dat["fun_name"];
		$cache_name = $dat["cache_name"];
		$p_v_name = "\$ot_".$arr["a_parent"]."_".$arr["level"];

		$ret .= $this->_gi()."\$o_treeview = get_instance(\"contentmgmt/object_treeview\");\n";
		$ret .= $this->_gi().$list_name." = \$o_treeview->get_folders_as_object_list(\$parent_obj);\n";

		$ret .= $this->_gi()."$inst_name =& \$o_treeview;\n";		
		$ret .= $this->_gi()."$fun_name = \"make_menu_link\";\n";		
		$ret .= $this->_gi().$p_v_name." = true;\n";
		$ret .= $this->_gi().$cache_name." = false;\n";

		return $ret;
	}

	function _g_op_list_init($arr)
	{
		// we can include constants in the code, this will 
		// get executed in aw ...

		// insert new list item in the list name stack
		$list_name = "\$list_".$arr["a_parent"]."_".$arr["level"];
		$o_name = "\$o_".$arr["a_parent"]."_".$arr["level"];
		$content_name = "\$content_".$arr["a_parent"]."_".$arr["level"];
		$loop_counter_name = "\$i_".$arr["a_parent"]."_".$arr["level"];
		$inst_name = "\$inst_".$arr["a_parent"]."_".$arr["level"];
		$fun_name = "\$fun_".$arr["a_parent"]."_".$arr["level"];
		$cache_name = "\$use_cache_".$arr["a_parent"]."_".$arr["level"];

		array_push($this->list_name_stack, array(
			"list_name" => $list_name,
			"o_name" => $o_name,
			"content_name" => $content_name,
			"loop_counter_name" => $loop_counter_name,
			"inst_name" => $inst_name,
			"fun_name" => $fun_name,
			"cache_name" => $cache_name
		));


		$ret = "";

		// also set the area as visible, because if we get here in execution, it is visible.
		$ret .= $this->_gi()."\$this->menu_levels_visible[".$arr["a_parent"]."][".$arr["level"]."] = 1;\n";

		if ($arr["level"] == 1)
		{
			$ret .= $this->_gi()."if (\$this->can(\"view\", ".$arr["a_parent_p_fn"].") && \$this->object_exists(".$arr["a_parent_p_fn"]."))\n";
			$ret .= $this->_gi()."{\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\$parent_obj = new object(".$arr["a_parent_p_fn"].");\n";
			$this->brace_level--;
			$ret .= $this->_gi()."}\n";
			$ret .= $this->_gi()."else\n";
			$ret .= $this->_gi()."{\n";
			$this->brace_level++;
			$ret .= $this->_gi()."\$parent_obj = new object(aw_ini_get(\"rootmenu\"));\n";
			$this->brace_level--;
			$ret .= $this->_gi()."}\n";
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
				$ret .= $this->_gi()."\$parent_obj = ".$parent_o_name.";\n";
			}
			else
			{
				$ret .= $this->_gi()."if (\$this->can(\"view\", \$this->_helper_find_parent(".$arr["a_parent"].",".$arr["level"].")))\n";
				$ret .= $this->_gi()."{\n";
				$this->brace_level++;

				$ret .= $this->_gi()."\$parent_obj = new object(\$this->_helper_find_parent(".$arr["a_parent"].",".$arr["level"]."));\n";
				$this->brace_level--;
				$ret .= $this->_gi()."}\n";
				$ret .= $this->_gi()."else\n";
				$ret .= $this->_gi()."{\n";
				$this->brace_level++;
				$ret .= $this->_gi()."\$parent_obj = new object(aw_ini_get(\"rootmenu\"));\n";
				$this->brace_level--;
				$ret .= $this->_gi()."}\n";
			}
		}

		$ret .= $this->_gi().$cache_name." = true;\n";
		return $ret;
	}
	
	function _g_op_has_lugu($arr)
	{
		end($this->list_name_stack);
		$dat = current($this->list_name_stack);
		$o_name = $dat["o_name"];
		
		$ret = "";
		
		$ret .= $this->_gi()."\$has_lugu = \"\";\n";
		$ret .= $this->_gi()."if (".$o_name."->meta(\"show_lead\") && (!aw_ini_get(\"menuedit.show_lead_in_menu_only_active\") || \$this->_helper_is_in_path(".$o_name."->id())))\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\$xdat = new object_list(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"parent\" => ".$o_name."->id(),\n";
		$ret .= $this->_gi()."\"status\" => STAT_ACTIVE,\n";
		$ret .= $this->_gi()."\"period\" => aw_global_get(\"act_per_id\"),\n";
		$ret .= $this->_gi()."\"class_id\" => array(CL_PERIODIC_SECTION, CL_DOCUMENT),\n";
		$ret .= $this->_gi()."\"sort_by\" => \"objects.jrk\",\n";
		$ret .= $this->_gi()."\"limit\" => (int)aw_ini_get(\"menuedit.show_lead_in_menu_count\")\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		$ret .= $this->_gi()."for(\$o =& \$xdat->begin(); !\$xdat->end(); \$o =& \$xdat->next())\n";
		$ret .= $this->_gi()."{\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\$done = \$this->doc->gen_preview(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"docid\" => \$o->id(), \n";
		$ret .= $this->_gi()."\"tpl\" => \"nadal_film_side_lead.tpl\",\n";
		$ret .= $this->_gi()."\"leadonly\" => 1, \n";
		$ret .= $this->_gi()."\"section\" => ".$o_name."->id(),\n";
		$ret .= $this->_gi()."\"strip_img\" => 0\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		$ret .= $this->_gi()."\$this->vars(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"lugu\" => \$done\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		$ret .= $this->_gi()."\$has_lugu .= \$this->parse(\"HAS_LUGU\");\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";
		$this->brace_level--;
		$ret .= $this->_gi()."}\n";
		$ret .= $this->_gi()."\$this->vars(array(\n";
		$this->brace_level++;
		$ret .= $this->_gi()."\"HAS_LUGU\" => \$has_lugu\n";
		$this->brace_level--;
		$ret .= $this->_gi()."));\n";
		
		return $ret;
	}
}
?>
