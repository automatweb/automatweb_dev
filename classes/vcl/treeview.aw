<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/treeview.aw,v 1.30 2004/06/14 12:05:22 duke Exp $
// treeview.aw - tree generator
/*

	@classinfo relationmgr=yes

        @default table=objects
        @default group=general

        @property root type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
        @caption Root objekt

	@property rootcaption type=textbox field=meta method=serialize
	@caption Root objekti nimi
	
	@property icon_root type=relpicker reltype=RELTYPE_ICON field=meta method=serialize
	@caption Root objekti ikoon
        
	@property treetype type=select field=meta method=serialize
        @caption Puu tüüp

	@property icon_folder_open type=relpicker reltype=RELTYPE_ICON field=meta method=serialize
	@caption Lahtise kausta ikoon

	@property icon_folder_closed type=relpicker reltype=RELTYPE_ICON field=meta method=serialize
	@caption Kinnise kausta ikoon

	@reltype FOLDER value=1 clid=CL_MENU
	@caption root kataloog

	@reltype ICON value=2 clid=CL_ICON
	@caption ikoon
*/

define("TREE_HTML", 1);
define("TREE_JS", 2);
define("TREE_DHTML",3);

// does this tree type support loading branches on-demand?
define("LOAD_ON_DEMAND",1);

// does this tree type support persist state (using cookies)
define("PERSIST_STATE",2);

class treeview extends class_base
{
	function treeview($args = array())
	{
		$this->init(array(
			"tpldir" => "treeview",
			"clid" => CL_TREEVIEW,
		));

		$this->features = array();
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "treetype":
				$data["options"] = array("" => "--vali--","dhtml" => "DHTML (Ftiens)");
				break;
		};
	}
	
	function init_vcl_property($arr)
	{
		$pr = $arr["property"];
		$this->start_tree(array(
                        "type" => TREE_DHTML,
                        "tree_id" => $pr["name"], // what if there are multiple trees
                        "persist_state" => 1,
                ));
		$pr["vcl_inst"] = $this;
		return array($pr["name"] => $pr);
	}

	function get_html()
	{
		$rv = $this->finalize_tree();
		return $rv;
	}

	
	////
	// !Generates a tree. Should be used from _inside_ the code, because
	// this can accept arguments that could be harmful when used through ORB	
	function generate($args = array())
	{
		// generates the tree
		extract($args);
		$root = $args["config"]["root"];
		$this->urltemplate = isset($args["urltemplate"]) ? $args["urltemplate"] : "";
		$this->config = $args["config"];

		$rootobj = obj($root);
		$treetype = $rootobj->meta("treetype");
		if (!empty($treetype))
		{
			$type = $treetype;
		}
		else
		{
			$type = "dhtml";
		};
		$this->read_template("ftiens.tpl");
		$this->arr = array();
		
		$this->clidlist = (is_array($args["config"]["clid"])) ? $args["config"]["clid"] : CL_MENU; 


		// I need a way to display all kind of documents here, and not only
		// menus. So, how on earth am I going to do that.

		// if the caller specified clid list, then we list all of those objects,
		// if not, then only menus
		
		// listib koik menyyd ja paigutab need arraysse	

		$this->ic = get_instance("icons");


		// objektipuu
		$this->rec_tree($root);

		$tr = $this->generate_tree($root);

		$icon_root = $rootobj->meta("icon_root");

		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $root,
			"linktarget" => isset($args["linktarget"]) ? $args["linktarget"] : "",
			"shownode" => isset($args["shownode"]) ? $args["shownode"] : "",
			"rootname" => $rootobj->meta("rootcaption"),
			"rooturl" => $this->do_item_link($rootobj),
			"icon_root" => !empty($icon_root)? $this->mk_my_orb("show",array("id" => $icon_root),"icons") : "/automatweb/images/aw_ikoon.gif",
		));

		$retval = $this->parse();
		return $retval;
	}

	/** Public/ORB interface 
		
		@attrib name=show params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function show($args = array())
	{
		extract($args);
		$obj = obj($id);
		return $this->generate(array(
			"urltemplate" => $args["urltemplate"],
			"config" => $obj->meta(),
		));
	}
	
	function rec_tree($parent)
	{
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => $this->clidlist
		));
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$row = $o->fetch();
			$row["name"] = str_replace("\"","&quot;", $row["name"]);
			$this->arr[$row["parent"]][] = $row;
			$this->rec_tree($row["oid"]);
		}

		return;
	}

	function generate_tree($parent)
	{
		if (!is_array($this->arr[$parent]))
		{
			return;
		};
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];

		$ret = "";
		reset($this->arr[$parent]);
		while (list(,$row) = each($this->arr[$parent]))
		{
			// tshekime et kas menyyl on submenyysid
			// kui on, siis n2itame alati
			// kui pole, siis tshekime et kas n2idatakse perioodilisi dokke
			// kui n2idatakse ja menyy on perioodiline, siis n2itame menyyd
			// kui pole perioodiline siis ei n2ita
			if (isset($this->arr[$row["oid"]]) && is_array($this->arr[$row["oid"]]))
			{
				$sub = $this->generate_tree($row["oid"]);;
			}
			else
			{
				$sub = "";
			};
			$icon_url = ($row["class_id"] == CL_MENU) ? "" : $this->ic->get_icon_url($row["class_id"],"");
			$url = $this->do_item_link(&$row);
			$this->vars(array(
				"name" => $row["name"],
				"id" => $row["oid"],
				"parent" => $row["parent"],
				"iconurl" => $icon_url,
				"url" => $url,
				"targetframe" => "right",
			));
			if ($sub == "")
			{
				$ret.=$this->parse("DOC");
			}
			else
			{
				$ret.=$this->parse("TREE").$sub;
			}
		}
		return $ret;
	}

	function do_item_link($row)
	{
		if (isset($row["link"]) && $row["link"])
		{
			$url = $row["link"];
		}
		elseif ($this->urltemplate)
		{
			$url = sprintf($this->urltemplate,$row["oid"]);
		}
		else
		{
			$url = $this->cfg["baseurl"] . "/" . $row["oid"];
		};
		return $url;
	}

	////
	// !inits tree
	// params:
	//   root_name - root menu name
	//   root_url - root menu url
	//   root_icon - root menu icon
	//   has_root - whether to draw to the root node. trees that load branches on demand don't
	//		need to draw the rootnode for branches.
	//   tree_id  - set to an unique id, if you want the tree to persist it's state
	//	type - TREE_HTML|TREE_JS|TREE_DHTML , defaults to TREE_JS
	//	persist_state - tries to remember tree state in kuuki
	function start_tree($arr)
	{
		$this->items = array();
		$this->tree_type = empty($arr["type"]) ? TREE_JS : $arr["type"];
		$this->tree_dat = $arr;
		$this->has_root = empty($arr["has_root"]) ? false : $arr["has_root"];
		$this->tree_id = empty($arr["tree_id"]) ? false : $arr["tree_id"];
		$this->get_branch_func = empty($arr["get_branch_func"]) ? false : $arr["get_branch_func"];

		if ($this->tree_type == TREE_DHTML && !empty($this->get_branch_func))
		{
			$this->features[LOAD_ON_DEMAND] = 1;
		};

		if ($this->tree_type == TREE_DHTML && !empty($this->tree_id) && $arr["persist_state"])
		{
			$this->features[PERSIST_STATE] = 1;
		};


	}

	function has_feature($feature)
	{
		return isset($this->features[$feature]) ? 1 : 0;
	}

	////
	// !adds item to the tree
	// params:
	//    parent - the parent of the item to be added
	//    item - array of item data: 
	//      id - id of the item
	//      name - the name of the item
	//      url - the link for the item
	//      icon - the url of the icon
	//      target - the target frame of the link
	function add_item($parent, $item)
	{
		// dhtml tree (sometimes) needs to know information about
		// a specific node and for this it needs to access
		// that node directly. 
		$this->itemdata[$item["id"]] = $item;
		$this->items[$parent][] = &$this->itemdata[$item["id"]];
	}

	function set_selected_item($id)
	{
		$this->selected_item = $id;
	}

	function node_has_children($id)
	{
		return is_array($this->items[$id]) && sizeof($this->items[$id]) > 0;
	}

	////
	// !draws the tree
	// rootnode - from which node should drawing start (defaults to 0)
	function finalize_tree($arr = array())
	{

		$this->rootnode = empty($arr["rootnode"]) ? 0 : $arr["rootnode"];

		if ($this->tree_type == TREE_HTML)
		{
			return $this->html_finalize_tree();
		}

		if ($this->tree_type == TREE_DHTML)
		{
			return $this->dhtml_finalize_tree();
		}

		$this->read_template("ftiens.tpl");
		// objektipuu
		$tr = $this->req_finalize_tree($this->rootnode);
		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $this->rootnode,
			"rootname" => $this->tree_dat["root_name"],
			"linktarget" => $this->tree_dat["url_target"],
			"rooturl" => $this->tree_dat["root_url"],
			"icon_root" => ($this->tree_dat["root_icon"] != "" ) ? $this->tree_dat["root_icon"] : "/automatweb/images/aw_ikoon.gif",
		));
		return $this->parse();
	}

	function req_finalize_tree($parent)
	{
		if (!isset($this->items[$parent]) || !is_array($this->items[$parent]))
		{
			return '';
		}

		$ret = '';
		foreach($this->items[$parent] as $row)
		{
			$sub = $this->req_finalize_tree($row['id']);
			if (isset($row["iconurl"]))
			{
				$row["icon"] = $row["iconurl"];
			};
			$this->vars(array(
				'name' => $row['name'],
				'id' => $row['id'],
				'parent' => $parent,
				'iconurl' => $row['icon'] == '' ? $this->cfg['baseurl'].'/automatweb/images/aw_ikoon.gif' : $row['icon'],
				'url' => $row['url'],
				'targetframe' => $row['target'],
			));
			if ($sub == "")
			{
				$ret.=$this->parse('DOC');
			}
			else
			{
				$ret.=$this->parse('TREE').$sub;
			}
		}
		return $ret;
	}

	function html_finalize_tree()
	{
		$this->read_template("html_tree.tpl");
		$ml = array();
		$this->draw_html_tree($this->rootnode, &$ml);
		
		$this->vars(array(
			"colspan" => 10
		));
		return $this->parse("TREE_BEGIN").join("\n", $ml).$this->parse("TREE_END");
	}

	function dhtml_finalize_tree()
	{
		$level = 0;
		$this->rv = "";
		$this->set_parse_method("eval");
		$this->read_template("dhtml_tree.tpl");


		$this->r_path = array();
		// now figure out the paths to selected nodse

		// ja nagu sellest veel küllalt poleks .. I can have multiple opened nodes. yees!
		if ($this->has_feature(PERSIST_STATE))
		{
			$opened_nodes = explode("^",$_COOKIE[$this->tree_id]);
			$r_path = array();
			foreach($opened_nodes as $open_node)
			{
				$rp = $this->_get_r_path($open_node);
				$r_path = array_merge($r_path,$rp);
			};
			$this->r_path = array_unique($r_path);
		};

		$t = get_instance("languages");
		$this->vars(array(
			"target" => $this->tree_dat["url_target"],
			"open_nodes" => is_array($opened_nodes) ? join(",",map("'%s'",$opened_nodes)) : "",
			"tree_id" => $this->tree_id,
			"charset" => $t->get_charset()
		));

		$rv = $this->draw_dhtml_tree($this->rootnode);

		$root = "";
		if ($this->has_root)
		{
			$this->vars(array(
				"rootname" => $this->tree_dat["root_name"],
				"rooturl" => $this->tree_dat["root_url"],
				"icon_root" => ($this->tree_dat["root_icon"] != "" ) ? $this->tree_dat["root_icon"] : "/automatweb/images/aw_ikoon.gif",
			));
			if ($this->get_branch_func)
			{
				$this->vars(array(
					"get_branch_func" => $this->get_branch_func,
				));
			};
			$root .= $this->parse("HAS_ROOT");
		};

		// so, by default all items below the second level are hidden, but I should be able to
		// make them visible based on my selected item. .. oh god, this is SO going to be not
		// fun

		// so, how do I figure out the path to the root node .. and if I do, then that's the
		// same thing I'll have to give as an argument when using the on-demand feature

		$this->vars(array(
			"TREE_NODE" => $rv,
			"HAS_ROOT" => $root,
			"persist_state" => $this->has_feature(PERSIST_STATE),
		));

		return $this->parse();


	}

	// figures out the path from an item to the root of the tree
	function _get_r_path($id)
	{
		$item = $this->itemdata[$id];
		$rpath = array();
		while(!empty($item))
		{
			$rpath[] = $item["id"];
			$item = in_array($item["parent"],$rpath) ? false : $this->itemdata[$item["parent"]];
		};
		return $rpath;
	}

	function draw_dhtml_tree($parent)
	{
		$data = $this->items[$parent];

		if (!is_array($data))
		{
			return "";
		};

		$this->level++;
		$result = "";

		foreach($data as $item)
		{
			$subres = $this->draw_dhtml_tree($item["id"]);

			if (isset($item["iconurl"]))
			{
				$iconurl = $item["iconurl"];
			}
			elseif (in_array($item["id"],$this->r_path))
			{
				$iconurl = $this->cfg["baseurl"] . "/automatweb/images/open_folder.gif";
			}
			else
			{
				$iconurl = $this->cfg["baseurl"] . "/automatweb/images/closed_folder.gif";
			};

			$name = $item["name"];
			if ($item["id"] == $this->selected_item)
			{
				$name = "<strong>$name</strong>";
			};

			$this->vars(array(
				"name" => $name,
				"id" => $item["id"],
				"iconurl" => $iconurl,
				"url" => $item["url"],
				// spacer is only used for purely aesthetic reasons - to make
				// source of the page look better
				"spacer" => str_repeat("    ",$this->level),
			));


			if (empty($subres))
			{
				// fill them with emptyness
				$this->vars(array(
					"SUB_NODES" => "",
				));

				$tpl = "SINGLE_NODE";
			}
			else
			{
				$this->vars(array(
					"SINGLE_NODE" => $subres,
					"display" => in_array($item["id"],$this->r_path) ? "block" : "none",
					"data_loaded" => in_array($item["id"],$this->r_path) ? "true" : "false",
					"node_image" => in_array($item["id"],$this->r_path) ? $this->cfg["baseurl"] . "/automatweb/images/minusnode.gif" : $this->cfg["baseurl"] . "/automatweb/images/plusnode.gif",
				));
				$tmp = $this->parse("SUB_NODES");
				$this->vars(array(
					"SUB_NODES" => $tmp,
				));

				$tpl = "TREE_NODE";
			};

			$result .= $this->parse($tpl);
					
		}
		$this->level--;
		return $result;

	}

	function draw_html_tree($parent, &$ml)
	{
		$this->level++;
		$data = array();
		$ids = new aw_array();
		$counts = array();

		// get all menus for this level
		if (is_array($this->items[$parent]))
		{
			$data = $this->items[$parent];
		}

		foreach($data as $row)
		{
			$counts[$row["id"]] = count($this->items[$row["id"]]);
		}

		$num = 0;
		$cnt = count($data);
		foreach($data as $row)
		{
			if ($cnt-1 == $num && $this->level == 1)
			{
				$this->first_level_menu_is_last = true;
			}
			else
			if ($this->level == 1)
			{
				$this->first_level_menu_is_last = false;
			}

			$this->vars(array(
				"link" => $row["url"],
				"name" => $row["name"],
				"section" => $row['id']
			));
			$this->vars($row["data"]);

			$sel = "";
			if ($this->selected_item == $row['id'])
			{
				$sel = "_SEL";
			}
			if ($counts[$row['id']])
			{
				$ms = $this->parse("MENU".$sel);
			}
			else
			{
				$ms = $this->parse("MENU_NOSUBS".$sel);
			}

			if ($this->level > 1)
			{
				$ms .= $this->parse("INFO");
			}

			// if the first level menu on this line is the last in it's level, then the first image must be empty
			if ($this->level == 1)
			{
				$str = "";
			}
			else
			if ($this->first_level_menu_is_last)
			{
				$str = $this->parse("FTV_BLANK");
			}
			else
			{
				$str = $this->parse("FTV_VERTLINE");
			}

			if ($counts[$row['id']])
			{
				$str .= str_repeat($this->parse("FTV_VERTLINE"), max(0,$this->level-2));
				if ($cnt-1 == $num)
				{
					$str.= $this->parse("FTV_PLASTNODE");
				}
				else
				{
					if (isset($this->items[$row['id']]))
					{
						$str.= $this->parse("FTV_MNODE");
					}
					else
					{
						
						$str.= $this->parse("FTV_PNODE");
					}
				}
			}
			else
			{
				$str .= str_repeat($this->parse("FTV_VERTLINE"), max(0,$this->level-2));
				if ($cnt-1 == $num)
				{
					$str.= $this->parse("FTV_LASTNODE");
				}
				else
				{
					$str.= $this->parse("FTV_NODE");
				}
			}

			$this->vars(array(
				"str" => $str,
				"colspan" => (10-$this->level),
				"ms" => $ms
			));
			$ml[] = $this->parse("FTV_ITEM");

			// now check if this menu is in the oc for the active menu 
			// and if so, then recurse to the next level
			if (isset($this->items[$row["id"]]))
			{
				$this->draw_html_tree($row['id'], &$ml);
			}
			$num++;
		}
		$this->level--;
	}

	////
	// !takes an object_tree and returns a treeview 
	// the treeview will have all the objects in the object_tree
	// as tree items.
	// parameters:
	//	tree_opts - options to pass to the treeview constructor
	//	root_item - object instance that contains the root item
	//	ot - object_tree instance that contains the needed objects
	//	var - variable name. links in the tree will be made with
	//		aw_url_change_var($var, $item->id()) - the $var variable will
	//		contain the active tree item
	function tree_from_objects($arr)
	{
		extract($arr);
		$tv = get_instance(CL_TREEVIEW);
		$tree_opts["root_url"] = aw_url_change_var($var, $arr["root_item"]->id());
		
		$tv->start_tree($tree_opts);
		$tv->add_item(0,array(
			"name" => parse_obj_name($root_item->name()),
			"id" => $root_item->id(),
			"url" => aw_url_change_var($var, $root_item->id()),
		));

		$ic = get_instance("icons");

		$ol = $ot->to_list();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$tv->add_item($o->parent(),array(
				"name" => parse_obj_name($o->name()),
				"id" => $o->id(),
				"url" => aw_url_change_var($var, $o->id()),
				"icon" => ($o->class_id() == CL_MENU) ? "" : $ic->get_icon_url($o->class_id(),"")
			));
		}

		return $tv;
	}	
};
?>
