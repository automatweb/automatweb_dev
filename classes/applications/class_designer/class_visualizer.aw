<?php
class class_visualizer extends class_base
{
	function class_visualizer()
	{
		$this->init("");
	}

	/**
		@attrib name=view default=1
		@param id optional type=int
		@param group optional

	**/
	function view($arr)
	{
		$obj_id = $arr["id"];
		if (empty($obj_id) && is_oid(aw_global_get("class")))
		{
			$obj_id = aw_global_get("class");
		};
		$this->cls_id = $obj_id;
		$o = new object($obj_id);
		if ($o->class_id() != CL_CLASS_DESIGNER)
		{
			die(t("I'm so depessed"));
		};
		$tree = new object_tree(array(
			"parent" => $o->id(),
			"class_id" => CL_PROPERTY_GROUP,
		));
		$tlist = $tree->to_list();
		$group = $arr["group"];
		//arr($tlist);
                $cli = get_instance("cfg/htmlclient");
		$cf = get_instance(CL_CLASS_DESIGNER);
		$items = $cf->elements;
		$clinf = aw_ini_get("classes");
		// kui gruppi pole, siis vali esimene
		// XXX: ühendada see algoritm sellega, mis tehakse classbases
		//$group = $arr["group"];
		$groupitems = array();
		//$active_groups = array();
		foreach($tlist->arr() as $xo)
		{
			$parent_obj = new object($xo->parent());
			if ($parent_obj->class_id() == CL_PROPERTY_GROUP)
			{
				$groupitems[$xo->parent()]["items"][] = array(
					"caption" => $xo->name(),
					"id" => $xo->id(),
				);
			}
			else
			{
				if (empty($group))
				{
					$group = $xo->id();
				};
				$groupitems[$o->id()]["items"][] = array(
					"caption" => $xo->name(),
					"id" => $xo->id(),
				);
			};
		};
		
		// URL-ist anti grupp. Kui sellel grupil on lapsi, mille tüübiks on ka grupp, siis me ka näitame neid
	
		if (is_oid($group))
		{
			$active_groups = array($group);
			$children = new object_list(array(
				"parent" => $group,
				"class_id" => CL_PROPERTY_GROUP,
			));
			if ($children->count() > 0)
			{
				$use_group_o = $children->begin();
				$use_group = $use_group_o->id();
				$active_groups[] = $use_group;
			}
			else
			{
				$grp_obj = new object($group);
				$parent_obj = new object($grp_obj->parent());
				if ($parent_obj->class_id() == CL_PROPERTY_GROUP)
				{
					$active_groups[] = $parent_obj->id(); 
				};
				$use_group = $group;
			};
		};

		foreach($groupitems as $key => $dat)
		{
			foreach($dat["items"] as $gd)
			{
				if ($key == $o->id())
				{
					$level = 1;
					$tab_parent = "";
				}
				else
				{
					$level = 2;
					$tab_parent = $key;
				};

				if ($level == 2 && !in_array($tab_parent,$active_groups))
				{
					continue;
				};

				// aga teise taseme grupid lisame ainult siis, kui nad on aktiivsed, eh?

				$cli->add_tab(array(
					"id" => $gd["id"],
					"caption" => $gd["caption"],
					"active" => in_array($gd["id"],$active_groups),
					"parent" => $tab_parent,
					"level" => $level,
					"link" => $this->mk_my_orb("view",array(
						//"id" => $o->id(),
						"group" => $gd["id"],
						"class" => $this->cls_id,
					)),
				));
			};

		};

		$elements = new object_tree(array(
			"parent" => $use_group,
		));

		$elements = $elements->to_list();
		foreach($elements->arr() as $el)
		{
			$clid = $el->class_id();
			if (in_array($clid,$items))
			{
				$eltype = $clinf[$clid]["def"];
				$eltype = strtolower(str_replace("CL_PROPERTY_","",$eltype));
				$propdata = array(
					"name" => $el->name(),
					"caption" => $el->name(),
					"type" => $eltype,
				);
				if ($clid == CL_PROPERTY_CHOOSER)
				{
					$propdata["multiple"] = $el->prop("multiple");
					$propdata["orient"] = $el->prop("orient") == 1 ? "vertical" : "";
					$propdata["options"] = explode("\n",$el->prop("options"));
				}
				else
				{
					$ti = get_instance($clid);
					if (method_exists($ti, "get_visualizer_prop"))
					{
						$ti->get_visualizer_prop($el, $propdata);
					}
				}
				$cli->add_property($propdata);
			};
		};
		/* so what's the use of this thing anyway? -- ahz
		// I need to invoke htmlclient directly
		foreach($tmp as $ta)
		{
			$cli->add_property($ta);
		};
		*/
		$cli->finish_output(array(
			"action" => "submit",
			"data" => array(
				//"id" => $o->id(),
				"group" => $use_group,
				"class" => get_class($this),
				"class" => $this->cls_id,
			),
		));
		$cont = $cli->get_result();

		return $cont;

	}

	/**
		@attrib name=submit
	**/
	function submit($arr)
	{

		// XXX: create a proper list of properties
		$cl_obj = new object($arr["class"]);		
		if (is_oid($arr["id"]))
		{
			$clx = new object($arr["id"]);
		}
		else
		{
			$clx = new object();
			$clx->set_class_id($cl_obj->prop("reg_class_id"));
			$clx->set_parent($arr["parent"]);
			$clx->set_status(STAT_ACTIVE);
		};
		foreach($arr as $key => $val)
		{
			if (is_numeric($key))
			{
				$clx->set_meta($key,$val);
			};
		};

		$clx->set_name($arr[$cl_obj->prop("object_name")]);
		$clx->save();

		$rv = $this->mk_my_orb("change",array("class" => $arr["class"],"group" => $arr["group"],"id" => $clx->id()),$arr["class"]);
		//print $rv;
		return $rv;

	}

	function get_class_groups($arr)
	{
		extract($arr);
		$o = new object($obj_id);
		if ($o->class_id() != CL_CLASS_DESIGNER)
		{
			die(t("this will not work"));
		};
		$tree = new object_tree(array(
			"parent" => $o->id(),
			"class_id" => CL_PROPERTY_GROUP,
			"site_id" => array(),
			"lang_id" => array(),
		));
		$tlist = $tree->to_list();
		$group = $arr["group"];
		//arr($tlist);
                $cli = get_instance("cfg/htmlclient");
		$cf = get_instance(CL_CLASS_DESIGNER);
		$items = $cf->elements;
		$clinf = aw_ini_get("classes");
		// kui gruppi pole, siis vali esimene
		// XXX: ühendada see algoritm sellega, mis tehakse classbases
		//$group = $arr["group"];
		$groupitems = array();
		//$active_groups = array();
		foreach($tlist->arr() as $xo)
		{
			$parent_obj = new object($xo->parent());
			$name = $xo->id();
			if ($parent_obj->class_id() == CL_PROPERTY_GROUP)
			{
				$groupitems[$name]["parent"] = $xo->parent();
			};
			$groupitems[$name]["caption"] = $xo->name();
		};

		if ($o->prop("relationmgr") == 1)
		{
			$groupitems["relationmgr"] = array(
				"caption" => t("Seostehaldur"),
				//"no_form" => 1,
				"submit" => "no",
			);
			if($_REQUEST["srch"] == 1)
			{
				$groupitems["relationmgr"]["submit_method"] = "get";
			}
		};

		return $groupitems;
	}

	function get_group_properties($arr)
	{
		$element_tree = new object_tree(array(
			"parent" => $arr["id"],
			"class_id" => array(CL_PROPERTY_GROUP,CL_PROPERTY_GRID),
		));

		$o = new object($arr["id"]);
		
		$els = $element_tree->to_list();
		$grid2grp = array();
		foreach($els->arr() as $el)
		{
			if (CL_PROPERTY_GRID == $el->class_id())
			{
				$grid2grp[$el->id()] = $el->parent();
			};
		};

		$element_tree = new object_tree(array(
			"parent" => $arr["id"],
		));
		$elements = $element_tree->to_list();
		$cf = get_instance(CL_CLASS_DESIGNER);
		$items = $cf->elements;
		$clinf = aw_ini_get("classes");

		$rv = array();

		foreach($elements->arr() as $el)
		{
			$clid = $el->class_id();
			if (in_array($clid,$items) && $clid != CL_PROPERTY)
			{
				$eltype = $clinf[$clid]["def"];
				$eltype = strtolower(str_replace("CL_PROPERTY_","",$eltype));
				$sysname = strtolower(preg_replace("/\s/","_",$el->name()));
				
				$p_o = new object($el->parent());

				$group = $grid2grp[$el->parent()];

				if ($p_o->class_id() == CL_PROPERTY)
				{
					$group = $grid2grp[$p_o->parent()];
				};

				$propdata = array(
					//"name" => $el->name(),
					//"name" => $el->id(),
					"name" => $sysname,
					"caption" => $el->name(),
					"type" => $eltype,
					"group" => $group,
					"table" => "objects",
					"field" => "meta",
					"method" => "serialize",
				);

				if ($p_o->class_id() == CL_PROPERTY_GRID)
				{
					$grid_type = $p_o->prop("grid_type") == 0 ? "vbox" : "hbox";
					$prop_parent = $grid_type . $p_o->id();
				}
				else
				{
					$prop_parent = false;
				};

				if ($p_o->class_id() == CL_PROPERTY)
				{
					$grandparent = new object($p_o->parent());
					$grid_type = $grandparent->prop("grid_type") == 0 ? "vbox" : "hbox";
					$prop_parent = $grid_type . $grandparent->id();
				};


				if ($clid == CL_PROPERTY_CHOOSER)
				{
					$propdata["multiple"] = $el->prop("multiple");
					$propdata["orient"] = $el->prop("orient") == 1 ? "vertical" : "";
					$propdata["options"] = explode("\n",$el->prop("options"));
				}
				else
				{
					$ti = get_instance($clid);
					if (method_exists($ti, "get_visualizer_prop"))
					{
						$ti->get_visualizer_prop($el, $propdata);
					}
				}

				if ($prop_parent)
				{
					$propdata["parent"] = $prop_parent;
				};

				$rv[$sysname] = $propdata;
			};
		};

		if ($o->prop("relationmgr") == 1)
		{
			$rv["relationmgr"] = array(
				"name" => "relationmgr",
				"type" => "relationmgr",
				"caption" => t("Seostehaldur"),
				"store" => "no",
				"group" => "relationmgr",
			);
		};

		return $rv;

	}

	function get_layouts($arr)
	{
		$element_tree = new object_tree(array(
			"parent" => $arr["class_id"],
			"class_id" => array(CL_PROPERTY_GROUP,CL_PROPERTY_GRID),
		));

		$els = $element_tree->to_list();

		$i = 0;
		$rv = array();
		foreach($els->arr() as $el)
		{
			if ($el->class_id() == CL_PROPERTY_GRID)
			{
				$p_o = new object($el->parent());
				$i++;
				$i = $el->id();
				$rv["hbox" . $i] = array(
					"type" => "hbox",
					//"group" => $this->_valid_id($p_o->name()),
					"group" => $p_o->id(),
				);

			};
		};
		return $rv;
		


	}
	
	function _valid_id($src)
	{
		return strtolower(preg_replace("/\s/","_",$src));


	}

	
};
?>
