<?php
class class_visualizer extends aw_template
{
	function class_visualizer()
	{
		$this->init("");
	}

	/**
		@attrib name=view default=1
		@param id required type=int
		@param group optional

	**/
	function view($arr)
	{
		$o = new object($arr["id"]);
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
						"id" => $o->id(),
						"group" => $gd["id"],
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
				if ($clid == CL_PROPERTY_TABLE)
				{
					$t = new vcl_table();
					$table_items = new object_list(array(
						"parent" => $el->id(),
					));
					foreach($table_items->arr() as $table_item)
					{
						$sortable = $table_item->prop("sortable");
						$celldata = array(
							"name" => $table_item->name(),
							"caption" => $table_item->name(),
							"width" => $table_item->prop("width"),
							//"sortable" => (1 == $table_item->prop("sortable")) ? 1 : 0,
						);
						if ($sortable)
						{
							$celldata["sortable"] = 1;
						};
						$t->define_field($celldata);
					};
					$propdata["vcl_inst"] = $t;
				}
				else
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
				"id" => $o->id(),
				"group" => $use_group,
				"class" => get_class($this),
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
		print "see ei tee veel midagi :(";
		print "<br />";
		return $this->mk_my_orb("view",array("id" => $arr["id"],"group" => $arr["group"]),get_class($this));

	}
};
?>
