<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/menu_tree.aw,v 1.4 2005/01/20 12:38:18 kristo Exp $
// menu_tree.aw - menüüpuu

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@classinfo trans=1

	@property menus type=select multiple=1 size=15 trans=1
	@caption Menüüd

	@property children_only type=checkbox ch_value=1 trans=1
	@caption Ainult alammenüüd

	@property template type=select trans=1
	@caption Template

	@property num_levels type=select
	@caption Tasemeid
*/
class menu_tree extends class_base
{
	function menu_tree()
	{
		$this->init(array(
			"clid" => CL_MENU_TREE,
		));
		$this->strip_tags = aw_ini_get("menuedit.strip_tags");
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
			case "num_levels":
				$data["options"] = array(
					0 => "K&otilde;ik",
					1 => 1,
					2 => 2,
					3 => 3,
					4 => 4,
					5 => 5
				);
				break;

			case "menus":
				$ol = new object_list(array(
					"class_id" => CL_MENU,
					"status" => array(STAT_ACTIVE,STAT_NOTACTIVE),
					new object_list_filter(array(
						"logic" => "OR",
						"conditions" => array(
							"lang_id" => aw_global_get("lang_id"),
							"type" => MN_CLIENT
						)
					)),
				));
				$menus = array();
				for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
				{
					$menus[$o->id()] = $o->path_str();
				}
				asort($menus);
				$data["options"] = $menus;
				break;

			case "template":
				$tpldir = $this->cfg["site_basedir"] . "/templates/menu_tree";
				$tpls = $this->get_directory(array(
					"dir" => $tpldir,
				));
				$data["options"] = $tpls;
				break;

		}
		return PROP_OK;
	}

	function parse_alias($args = array())
	{
		extract($args);
		$this->shown = array();

		$obj = obj($alias["target"]);
		$this->mt_obj = $obj;

		$menus = safe_array($obj->meta("menus"));
		$ol = new object_list(array(
			"oid" => $menus,
			"sort_by" => "objects.jrk"
		));
		$menus = $ol->ids();
		
		$cho = $obj->meta("children_only");
		$this->children_only = !empty($cho) ? true : false;
		$tpl = ($obj->meta("template") ? $obj->meta("template") : "menu_tree.tpl");
		$tpl = str_replace("/","",$tpl);

		$folder_list = array();
		// FIXME: this should use menu cache 
		if (is_array($menus))
		{
			$this->tpl_name = "content";
			$this->spacer = "&nbsp";
			$this->sq = 3;
			$this->add_start_from = true;
			$this->read_template("menu_tree/" . $tpl);
			
			if ($this->is_template("item_L1"))
			{
				// this type of template can have different subtemplates for different levels..
				// good if one needs to use more complex designs..
				// you can also use optional item_Ln_START and item_ln_END subtemplates,
				// if they exist, then first and last item for a level is drawn using 
				// those templates - if they exist, then they _need_ to contain variables
				// for items
				$this->layout_mode = 2;
				$this->single_tpl = 0;
			}
			elseif ($this->is_template("START"))
			{
				// this type of template has 3 subs, START, ITEM and END, 
				// START and END are simply used to start and finish a level, they
				// cannot contain variables for items
				// typical usage: nested <ul> list
				$this->layout_mode = 3;
				$this->single_tpl = 0;
			}
			else
			{
				// this type of template has a single subtemplate which is used for
				// all levels, items are aligned with spacers
				// name of the subtemplate - "content"
				$this->layout_mode = 1;
				$this->single_tpl = 1;
			};

			foreach($menus as $val)
			{
				$folder_list = array_merge($folder_list,$this->gen_rec_list(array(
						"start_from" => $val,
				)));
				$this->level = 0;
			};
		};
		$fl = join("",$folder_list);
		return $fl;
		
	}
	
	function gen_rec_list($args = array())
	{
		extract($args);
		$this->alias_stack = array();
		$this->object_list = array(); // siia satuvad koik need objektid

		$this->start_from = $args["start_from"];

		$this->_gen_rec_list(array($args["start_from"]));
		if ( (sizeof($this->object_list) == 0) && not($this->add_start_from) )
		{
			$retval = false;
		}
		else
		{
			$this->res = "";

			if ($this->add_start_from && $this->can("view", $start_from))
			{
				$_root = obj($start_from);
				if ($_root->status() == STAT_ACTIVE)
				{
					$this->object_list[$_root->parent()][$start_from] = $_root;
				}
			}

			reset($this->object_list);
			$this->level = 0;
			$this->_recurse_object_list(array(
				"parent" => (is_object($_root) ? $_root->parent() : ""),
			));

			if ($this->layout_mode == 1)
			{
				// return the stuff outside the "content" sub as well
				$this->vars(array(
					"content" => $this->res,
				));
				$retval = $this->parse();
			}
			else
			{
				$retval = $this->res;
			};
		};
		return $retval;
	}

	////
	// !Rekursiivne funktsioon, kutsutakse välja gen_rec_list seest
	function _gen_rec_list($parents = array())
	{
		$this->save_handle();
	
		$nsuo = (aw_global_get("uid") == "" && aw_ini_get("menuedit.no_show_users_only"));

		$filt = array(
			"class_id" => CL_MENU,
			"parent" => $parents,
			"status" => STAT_ACTIVE,
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"lang_id" => aw_global_get("lang_id"),
					"type" => MN_CLIENT
				)
			)),
			"sort_by" => "objects.jrk"
		);

		if (aw_global_get("uid") == "")
		{
			$filt["users_only"] = 0;
		}
		$ol = new object_list($filt);
		$_parents = array();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$name = $o->name();
			if ($this->strip_tags)
			{
				$name = strip_tags($name);
			}
			$can = true;
			if ($nsuo)
			{
				if ($o->meta("users_only") == 1)
				{
					$can = false;
				}
			}
			
			if ($can)
			{
				$_parents[] = $o->id();
				$this->object_list[$o->parent()][$o->id()] = $o;
			}
		};
		if (sizeof($_parents) > 0)
		{
			$this->_gen_rec_list($_parents);
		};
		$this->restore_handle();
	}
	
	/////
	// !Recurse and print object array
	function _recurse_object_list($args = array())
	{
		if ($args["parent"])
		{
			$parent = $args["parent"];
		}
		else
		{
			$parent = 0;
		};

		$slice = $this->object_list[$parent];
		if (!is_array($slice))
		{
			return false;
		};

		if ($this->mt_obj->prop("num_levels") > 0 && $this->mt_obj->prop("num_levels") < $this->rec_level)
		{
			return;
		}

		$this->rec_level++;

		$slicesize = sizeof($slice);
		$slicecounter = 0;
		while(list($k,$v) = each($slice))
		{
			$slicecounter++;
			$id = $v->id();
			$spacer = str_repeat($this->spacer,$this->level * $this->sq);
			$name = $spacer . $v->name();
				
			if ($this->single_tpl)
			{
				$tpl = ($this->tpl_name) ? $this->tpl_name : $this->tlist[1][0];
			}
			elseif ($this->layout_mode == 2)
			{
				$tpl = "item_L" . $this->level;
				if ( ($slicecounter == 1) && ($this->is_template($tpl . "_START")) )
				{
					$tpl .= "_START";
				}
				else
				if ( ($slicecounter == $slicesize) && ($this->is_template($tpl . "_END")) )
				{
					$tpl .= "_END";
				}
			}
			elseif ($this->layout_mode == 3)
			{
				if ($v->prop("clickable") != 1 && $this->is_template("ITEM_NOCLICK"))
				{
					$tpl = "ITEM_NOCLICK";
				}
				else
				{
					$tpl = "ITEM";
				}
			}
			else
			{
				$tpl = $this->tlist[$this->level + 1][0];
			};

			$tmpp = $v->path();

			if ($v->alias())
			{
				if (aw_ini_get("menuedit.recursive_aliases") == 0)
				{
					$id = $v->alias();
				}
				else
				{
					$id = join("/",$this->alias_stack);
					$id .= ($id == "" ? "" : "/") . $v->alias();
				};
				$id = $this->cfg["baseurl"]."/".$id;
			}
			else
			{
				$id = $this->cfg["baseurl"]."/".$id;
			};

			if ($v->prop("link") != "")
			{
				$url = $v->prop("link");
				$id = $url;
			}

			if (!is_oid($v->prop("submenus_from_obj")))
			{
				$pt = array_reverse($v->path());
				foreach($pt as $p_o)
				{
					if (is_oid($p_o->prop("submenus_from_obj")) && $this->can("view", $p_o->prop("submenus_from_obj")))
					{
						$sfo = $p_o->prop("submenus_from_obj");
						$sfo_o = obj($sfo);
						$sfo_i = $sfo_o->instance();
						$url = $sfo_i->make_menu_link($v, $sfo_o);
						break;
					}
				}
			}

			if ($this->children_only && $v->id() == $this->start_from)
			{
				// do nothing
			}
			else
			{
				// check if we have already shown this one, so let's not do it again!
				if (!isset($this->shown[$v->id()]))
				{
					$this->vars(array(
						"url" => $url,
						"oid" => $id,
						"name" => parse_obj_name($v->name()),
						"spacer" => $spacer,
					));

					$this->res .= $this->parse($tpl);
					$this->shown[$v->id()] = $id;
				}
			};

			$next_slice = $this->object_list[$v->id()];
			if (is_array($next_slice) && (sizeof($next_slice) > 0))
			{
				if ($v->alias())
				{
					array_push($this->alias_stack,$v->alias());
				};

				if ($this->layout_mode == 3)
				{
					$this->res .= $this->parse("START");
				};	
				
				$this->level++;

				$this->_recurse_object_list(array(
					"parent" => $v->id(),
				));
				
				$this->level--;

				if ($this->layout_mode == 3)
				{
					$this->res .= $this->parse("END");
				};	

				if ($v->alias())
				{
						array_pop($this->alias_stack);
				};
			};
		};

		$this->rec_level--;
	}
}
?>
