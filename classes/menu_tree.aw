<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/menu_tree.aw,v 2.10 2003/04/23 12:02:43 duke Exp $
// menu_tree.aw - menüüpuu

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property menus type=select multiple=1 size=15
	@caption Menüüd

	@property children_only type=checkbox value=1 ch_value=1
	@caption Ainult alammenüüd

	@property template type=select 
	@caption Template

*/
class menu_tree extends class_base
{
	function menu_tree()
	{
		$this->init(array(
			"clid" => CL_MENU_TREE,
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
			case "menus":
				$ob = get_instance("objects");
				$menus = $ob->get_list();
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
		$obj = $this->get_object($alias["target"]);
		$menus = $obj["meta"]["menus"];
		$this->children_only = !empty($obj["meta"]["children_only"]) ? true : false;
		$tpl = ($obj["meta"]["template"]) ? $obj["meta"]["template"] : "menu_tree.tpl";
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

			if ($this->add_start_from)
			{
				$_root = $this->get_object($start_from);
				$this->object_list[$_root["parent"]][$start_from] = $_root;
			}

			reset($this->object_list);
			$this->level = 0;
			$this->_recurse_object_list(array(
				"parent" => $_root["parent"],
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
		$plist = join(",",$parents);
		$q = sprintf("SELECT %s,parent,name,class_id,alias,menu.link FROM objects 
				LEFT JOIN menu ON (objects.%s = menu.id)
				WHERE class_id = '%d' AND parent IN (%s) AND status = 2 AND lang_id = %d
				ORDER BY jrk",
				OID,OID,CL_PSEUDO,$plist,aw_global_get("lang_id"));
		$this->db_query($q);
		$_parents = array();
		while($row = $this->db_next())
		{
			// hmm?
			$this->dequote($row);
			$this->dequote($row);
			$_parents[] = $row[OID];
			$this->object_list[$row["parent"]][$row[OID]] = $row;
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
		$slicesize = sizeof($slice);
		$slicecounter = 0;
		while(list($k,$v) = each($slice))
		{
			$slicecounter++;
			$id = $v[OID];
			$spacer = str_repeat($this->spacer,$this->level * $this->sq);
			$name = $spacer . $v["name"];
				
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
				$tpl = "ITEM";
			}
			else
			{
				$tpl = $this->tlist[$this->level + 1][0];
			};
			if ($v["alias"])
			{
				$id = join("/",$this->alias_stack);
				$id .= ($id == "" ? "" : "/") . $v["alias"];
				$id = $this->cfg["baseurl"]."/".$id;
			}
			else
			{
				$id = $this->cfg["baseurl"]."/".$id;
			};

			if ($v["link"] != "")
			{
				$url = $_v_l;
				$id = $url;
			}

			if ($this->children_only && $v[OID] == $this->start_from)
			{
				// do nothing
			}
			else
			{
				$this->vars(array(
					"url" => $url,
					"oid" => $id,
					"name" => parse_obj_name($v["name"]),
					"spacer" => $spacer,
				));

				$this->res .= $this->parse($tpl);
			};

			$next_slice = $this->object_list[$v[OID]];
			if (is_array($next_slice) && (sizeof($next_slice) > 0))
			{
				if ($v["alias"])
				{
					array_push($this->alias_stack,$v["alias"]);
				};

				if ($this->layout_mode == 3)
				{
					$this->res .= $this->parse("START");
				};	
				
				$this->level++;

				$this->_recurse_object_list(array(
					"parent" => $v[OID],
				));
				
				$this->level--;

				if ($this->layout_mode == 3)
				{
					$this->res .= $this->parse("END");
				};	

				if ($v["alias"])
				{
						array_pop($this->alias_stack);
				};
			};
		};
	}
}
?>
