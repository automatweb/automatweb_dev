<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/treeview.aw,v 1.61 2006/03/29 08:00:41 tarvo Exp $
// treeview.aw - tree generator
/*

	@classinfo relationmgr=yes syslog_type=ST_TREEVIEW

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
define("TREE_DHTML", 3);
define("TREE_DHTML_WITH_CHECKBOXES", 4);
define("TREE_DHTML_WITH_BUTTONS", 5);

// does this tree type support loading branches on-demand?
define("LOAD_ON_DEMAND",1);

// does this tree type support persist state (using cookies)
define("PERSIST_STATE",2);

// for load on demand, to show that subelemenets are loaded and no load-on-demand is used anymore
define("DATA_IN_PLACE",3);

class treeview extends class_base
{
	var $only_one_level_opened = 0;

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
						"item_name_length" => $pr["item_name_length"],
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

		$this->ic = get_instance("core/icons");


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
		else
		{
			$url = $this->cfg["baseurl"] . "/" . $row["oid"];
		};
		return $url;
	}

	function set_root_name($name)
	{
		$this->has_root = true;
		$this->tree_dat["root_name"] = $name;
	}

	function set_root_icon($name)
	{
		$this->has_root = true;
		$this->tree_dat["root_icon"] = $name;
	}

	function set_root_url($name)
	{
		$this->has_root = true;
		$this->tree_dat["root_url"] = $name;
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
	//	type - TREE_HTML|TREE_DHTML|TREE_DHTML_WITH_CHECKBOXES|TREE_DHTML_WITH_BUTTONS 
	//	persist_state - tries to remember tree state in kuuki
	// separator - string separator to use for separating checked node id-s, applies when type is TREE_DHTML_WITH_CHECKBOXES or TREE_DHTML_WITH_BUTTONS. defaults to ","
	// checked_nodes - tree node id-s that are checked initially, applies when type is TREE_DHTML_WITH_CHECKBOXES
	// checkbox_data_var - name for variable that will contain posted data of what was checked/unchecked. applicable only when tree type is TREE_DHTML_WITH_CHECKBOXES or TREE_DHTML_WITH_BUTTONS. defaults to tree_id
	// data_in_place - for load on demand tree, if this is set(to '1'), no load on demand is used this point forward for that branch
	function start_tree($arr)
	{
		$this->auto_open = (is_array($arr["open_path"]) && count($arr["open_path"]))?$arr["open_path"]:false;
		$this->items = array();
		$this->tree_type = empty($arr["type"]) ? TREE_DHTML : $arr["type"];
		$this->tree_dat = $arr;
		$this->item_name_length = empty($arr["item_name_length"]) ? false : $arr["item_name_length"];
		$this->has_root = empty($arr["has_root"]) ? false : $arr["has_root"];
		$this->tree_id = empty($arr["tree_id"]) ? false : $arr["tree_id"];
		$this->get_branch_func = empty($arr["get_branch_func"]) ? false : $arr["get_branch_func"];
		$this->branch = empty($arr["branch"]) ? false : true;
		$this->root_id = trim($arr["root_id"]);
		if(($this->tree_type == TREE_DHTML) && !empty($this->get_branch_func) && $arr["data_in_place"]== "1")
		{
			$this->set_feature(DATA_IN_PLACE);
		}
		if (($this->tree_type == TREE_DHTML or $this->tree_type == TREE_DHTML_WITH_CHECKBOXES or $this->tree_type == TREE_DHTML_WITH_BUTTONS) && !empty($this->get_branch_func))
		{
			$this->features[LOAD_ON_DEMAND] = 1;
		}

		if (($this->tree_type == TREE_DHTML or $this->tree_type == TREE_DHTML_WITH_CHECKBOXES or $this->tree_type == TREE_DHTML_WITH_BUTTONS) && !empty($this->tree_id) && $arr["persist_state"])
		{
			$this->features[PERSIST_STATE] = 1;
		}

		if ($this->tree_type == TREE_DHTML_WITH_CHECKBOXES)
		{
			$this->separator = empty($arr["separator"]) ? "," : $arr["separator"];
			$this->checked_nodes = $arr["checked_nodes"];
			$this->checkbox_data_var = empty ($arr["checkbox_data_var"]) ? $arr["tree_id"] : $arr["checkbox_data_var"];
		}

		if ($this->tree_type == TREE_DHTML_WITH_BUTTONS)
		{
			$this->separator = empty($arr["separator"]) ? "," : $arr["separator"];
			$this->checkbox_data_var = empty ($arr["checkbox_data_var"]) ? $arr["tree_id"] : $arr["checkbox_data_var"];
		}

		$this->open_nodes = array();
	}

	function set_branch_func($fc)
	{
		$this->get_branch_func = $fc;
		if (($this->tree_type == TREE_DHTML or $this->tree_type == TREE_DHTML_WITH_CHECKBOXES or $this->tree_type == TREE_DHTML_WITH_BUTTONS) && !empty($this->get_branch_func))
		{
			$this->features[LOAD_ON_DEMAND] = 1;
		}
	}

	function set_feature($feat, $val = 1)
	{
		$this->features[$feat] = $val;
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
	//      iconurl - the url of the icon
	//      target - the target frame of the link
	//      checkbox_status - 1|0 i.e. checked or unchecked. applicable only when dhtml tree with checkboxes is used
	function add_item($parent, $item)
	{
		// dhtml tree (sometimes) needs to know information about
		// a specific node and for this it needs to access
		// that node directly.
		if($this->item_name_length)
		{
			$item["caption"]= substr($item["caption"], 0, $this->item_name_length).(strlen($item["caption"]) > 20 ? "..." : "");
		}
		$this->itemdata[$item["id"]] = $item;
		$this->items[$parent][] = &$this->itemdata[$item["id"]];
		if (!empty($item["is_open"]))
		{
			$this->open_nodes[] = $item["id"];
		};
	}

	function get_item_ids()
	{
		return array_keys($this->itemdata);
	}

	function get_item($id)
	{
		return $this->itemdata[$id];
	}

	function remove_item($id)
	{
		unset($this->itemdata[$id]);
		foreach($this->items as $k => $d)
		{
			foreach($d as $k2 => $v)
			{
				if ($v["id"] == $id)
				{
					unset($this->items[$k][$k2]);
					return;
				}
			}
		}
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

		if ($this->tree_type == TREE_DHTML_WITH_CHECKBOXES)
		{
			return $this->dhtml_checkboxes_finalize_tree ();
		}

		if ($this->tree_type == TREE_DHTML_WITH_BUTTONS)
		{
			return $this->dhtml_buttons_finalize_tree ();
		}

		$this->read_template("ftiens.tpl");
		// objektipuu
		$tr = $this->req_finalize_tree ($this->rootnode);
		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $this->rootnode,
			"rootname" => $this->tree_dat["root_name"],
			"linktarget" => $this->tree_dat["url_target"],
			"rooturl" => $this->tree_dat["root_url"],
			"icon_root" => ($this->tree_dat["root_icon"] != "" ) ? $this->tree_dat["root_icon"] : "/automatweb/images/aw_ikoon.gif",
		));
		return $this->parse ();
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
			if (!empty($row["iconurl"]))
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

	function html_finalize_tree ()
	{
		$this->read_template("html_tree.tpl");
		$ml = array();
		$this->draw_html_tree($this->rootnode, &$ml);

		$this->vars(array(
			"colspan" => 10
		));
		return $this->parse("TREE_BEGIN").join("\n", $ml).$this->parse("TREE_END");
	}

	function dhtml_finalize_tree ()
	{
		$level = 0;
		$this->rv = "";
		$this->set_parse_method("eval");
		$this->read_template("dhtml_tree.tpl");


		$this->r_path = array();
		// now figure out the paths to selected nodse

		// ja nagu sellest veel küllalt poleks .. I can have multiple opened nodes. yees!
		if ($this->has_feature(PERSIST_STATE) && !$this->has_feature(LOAD_ON_DEMAND))
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

		if (sizeof($this->open_nodes) > 0)
		{
			$this->r_path = $this->r_path + $this->open_nodes;
		};

		$t = get_instance("languages");

		$level = $_REQUEST["called_by_js"]?$_COOKIE[$this->tree_id."_level"]:1;
		if(!strlen($this->auto_open))
		{
			$this->auto_open = is_array(explode("^",$_COOKIE[$this->tree_id])) ? join(",",map("'%s'",explode("^",$_COOKIE[$this->tree_id]))) : "";
		}
		else
		{
			foreach($this->auto_open as $item)
			{
				$this->auto_open_tmp .= ",'".$item."'";
			}
			$this->auto_open = "''".$this->auto_open_tmp;
		}
		$this->vars(array(
			"target" => $this->tree_dat["url_target"],
			"open_nodes" => $this->auto_open,
			"level" => !strlen($level)?1:$level,
			"load_auto" => isset($_REQUEST["load_auto"])?$_REQUEST["load_auto"]:"true",
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
			'only_one_level_opened' => $this->only_one_level_opened,
		));

		return $this->parse();
	}

	function _req_add_loaded_flag($items, $arr = array())
	{
		foreach($items as $parent => $item)
		{
			if(strlen($item["name"]) && strlen($item["id"]))
			{
				$arr[] = $item["id"];
			}
			else
			{
				$arr = $this->_req_add_loaded_flag($item, $arr);
			}
		}
		return $arr;
	}

	function dhtml_checkboxes_finalize_tree ()
	{
		$level = 0;
		$this->rv = "";
		$this->set_parse_method("eval");
		$this->read_template("dhtml_checkboxes_tree.tpl");

		$this->r_path = array();
		// now figure out the paths to selected nodes

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
		$checked_nodes = is_array ($this->checked_nodes) ? implode ($this->separator, $this->checked_nodes) : "";

		$this->vars (array(
			"target" => $this->tree_dat["url_target"],
			"open_nodes" => is_array($opened_nodes) ? join(",",map("'%s'",$opened_nodes)) : "",
			"tree_id" => $this->tree_id,
			"charset" => $t->get_charset(),
			"separator" => $this->separator,
			"checked_nodes" => $checked_nodes,
			"checkbox_data_var" => $this->checkbox_data_var,
		));

		$rv = $this->draw_dhtml_tree_with_checkboxes ($this->rootnode);

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
			'only_one_level_opened' => $this->only_one_level_opened,
		));

		return $this->parse();
	}

	function dhtml_buttons_finalize_tree ()
	{
		$level = 0;
		$this->rv = "";
		$this->set_parse_method("eval");
		$this->read_template("dhtml_buttons_tree.tpl");

		$this->r_path = array();
		// now figure out the paths to selected nodes

		// ja nagu sellest veel küllalt poleks .. I can have multiple opened nodes. yees!
		if ($this->has_feature(PERSIST_STATE) && !$this->has_feature(LOAD_ON_DEMAND))
		{
			$opened_nodes = explode("^",$_COOKIE[$this->tree_id]);
			$r_path = array();

			foreach($opened_nodes as $open_node)
			{
				$rp = $this->_get_r_path($open_node);
				$r_path = array_merge($r_path,$rp);
			}

			$this->r_path = array_unique($r_path);
		}

		$t = get_instance("languages");
		$this->vars (array(
			"target" => $this->tree_dat["url_target"],
			"open_nodes" => is_array($opened_nodes) ? join(",",map("'%s'",$opened_nodes)) : "",
			"tree_id" => $this->tree_id,
			"charset" => $t->get_charset(),
			"separator" => $this->separator,
			"checkbox_data_var" => $this->checkbox_data_var,
		));

		$rv = $this->draw_dhtml_tree_with_buttons ($this->rootnode);

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

		$this->vars(array(
			"TREE_NODE" => $rv,
			"HAS_ROOT" => $root,
			"persist_state" => $this->has_feature(PERSIST_STATE),
			'only_one_level_opened' => $this->only_one_level_opened,
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

	function draw_dhtml_tree ($parent)
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
			// subress will be empty string, if draw_dhtml_tree finds no
			// elements under the requested node
				
			$in_path = in_array($item["id"],$this->r_path);

			if (!empty($item["iconurl"]))
			{
				$iconurl = $item["iconurl"];
			}
			elseif ($in_path)
			{
				// XXX: make it possible to set open/closed icons from the code
				$iconurl = $this->cfg["baseurl"] . "/automatweb/images/open_folder.gif";
			}
			else
			{
				$iconurl = $this->cfg["baseurl"] . "/automatweb/images/closed_folder.gif";
			};

			$name = $item["name"];
			if ($item["id"] == $this->selected_item)
			{
				// XXX: Might want to move this into the template
				$name = "<strong>$name</strong>";
			};

			$url_target = !isset($item["url_target"]) ? $this->tree_dat["url_target"] : $item["url_target"];
			
			$has_data = "0";
			if($this->has_feature(DATA_IN_PLACE) == 1)
			{
				$has_data = "1";
			}
			$this->vars(array(
				"name" => $name,
				"id" => $item["id"],
				"has_data" => $has_data,
				"iconurl" => $iconurl,
				"url" => $item["url"],
				// spacer is only used for purely aesthetic reasons - to make
				// source of the page look better
				"spacer" => str_repeat("    ",$this->level),
				"menu_level" => $this->level,
				"target" => $url_target,
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
					"display" => $in_path ? "block" : "none",
					"data_loaded" => $in_path ? "true" : "false",
					"node_image" => $in_path ? $this->cfg["baseurl"] . "/automatweb/images/minusnode.gif" : $this->cfg["baseurl"] . "/automatweb/images/plusnode.gif",
					"menu_level" => $this->level,
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

	function draw_dhtml_tree_with_checkboxes ($parent)
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
			$subres = $this->draw_dhtml_tree_with_checkboxes ($item["id"]);

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
			}

			$checkbox_status = "undefined";

			if (($item["checkbox"] === 0) or ($item["checkbox"] === 1))
			{
				if ( (is_array ($this->checked_nodes)) and (in_array ($item["id"], $this->checked_nodes)) )
				{
					$checkbox_status = "checked";
					array_push ($this->checked_nodes, $item["id"]);
				}
				else
				{
					$checkbox_status = "unchecked";
					$keys = array_keys ($this->checked_nodes, $item["id"]);

					foreach ($keys as $key)
					{
						unset ($this->checked_nodes[$key]);
					}
				}
			}
			else
			{
				if ( (is_array ($this->checked_nodes)) and (in_array ($item["id"], $this->checked_nodes)) )
				{
					$checkbox_status = "checked";
				}
				else
				{
					$checkbox_status = "unchecked";
				}
			}

			$this->vars(array(
				"name" => $name,
				"id" => $item["id"],
				"iconurl" => $iconurl,
				"url" => $item["url"],
				// spacer is only used for purely aesthetic reasons - to make
				// source of the page look better
				"spacer" => str_repeat("    ",$this->level),
				'menu_level' => $this->level,
				"checkbox_status" => $checkbox_status,
			));


			if (empty($subres))
			{
				// fill them with emptyness
				$this->vars(array(
					"SUB_NODES" => "",
				));

				if ($checkbox_status == "undefined")
				{
					$tpl = "SINGLE_NODE";
				}
				else
				{
					$tpl = "SINGLE_NODE_CHECKBOX";
				}
			}
			else
			{
				$this->vars(array(
					"SINGLE_NODE" => $subres,
					"display" => in_array($item["id"],$this->r_path) ? "block" : "none",
					"data_loaded" => in_array($item["id"],$this->r_path) ? "true" : "false",
					"node_image" => in_array($item["id"],$this->r_path) ? $this->cfg["baseurl"] . "/automatweb/images/minusnode.gif" : $this->cfg["baseurl"] . "/automatweb/images/plusnode.gif",
					'menu_level' => $this->level,
				));
				$tmp = $this->parse("SUB_NODES");
				$this->vars(array(
					"SUB_NODES" => $tmp,
				));

				$tpl = "TREE_NODE";
			}

			$result .= $this->parse($tpl);

		}
		$this->level--;
		return $result;

	}

	function draw_dhtml_tree_with_buttons ($parent)
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
			$subres = $this->draw_dhtml_tree_with_buttons ($item["id"]);

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
			}

			$checkbox_status = "undefined";

			if ($item["checkbox"] == "button")
			{
				$checkbox_status = "button";
			}

			$this->vars(array(
				"name" => $name,
				"id" => $item["id"],
				"iconurl" => $iconurl,
				"url" => $item["url"],
				// spacer is only used for purely aesthetic reasons - to make
				// source of the page look better
				"spacer" => str_repeat ("    ", $this->level),
				'menu_level' => $this->level,
				"checkbox_status" => $checkbox_status,
			));


			if (empty($subres))
			{
				// fill them with emptyness
				$this->vars(array(
					"SUB_NODES" => "",
				));

				if ($checkbox_status == "button")
				{
					$tpl = "SINGLE_NODE_BUTTON";
				}
				else
				{
					$tpl = "SINGLE_NODE";
				}
			}
			else
			{
				$this->vars(array(
					"SINGLE_NODE" => $subres,
					"display" => in_array($item["id"],$this->r_path) ? "block" : "none",
					"data_loaded" => in_array($item["id"],$this->r_path) ? "true" : "false",
					"node_image" => in_array($item["id"],$this->r_path) ? $this->cfg["baseurl"] . "/automatweb/images/minusnode.gif" : $this->cfg["baseurl"] . "/automatweb/images/plusnode.gif",
					'menu_level' => $this->level,
				));
				$tmp = $this->parse("SUB_NODES");
				$this->vars(array(
					"SUB_NODES" => $tmp,
				));

				$tpl = "TREE_NODE";
			}

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
	// no_urls - if set, urls for nodes won't be generated
	//	target_url - url for link of menu items. optional.
	//	var - variable name. links in the tree will be made with
	//		aw_url_change_var($var, $item->id(), $url) - the $var variable will
	//		contain the active tree item
	//	node_actions - array:  clid=>"action_name". This is for specifying different actions for different classes
	// checkbox_class_filter - array of class id-s, objects of these classes will have checkboxed/buttoned tree nodes. Applicable only when tree type is TREE_DHTML_WITH_CHECKBOXES or TREE_DHTML_WITH_BUTTONS.
	// no_root_item - bool - if true, the single root item is not inserted into the tree
	function tree_from_objects($arr)
	{
		extract($arr);
		$tv = get_instance(CL_TREEVIEW);
		$aw_classes = get_class_picker (array ("field" => "def"));

		if (!isset($target_url))
		{
			$target_url = null;
		}

		$class_id = $arr["root_item"]->class_id ();
		$class_name = strtolower (substr ($aw_classes[$class_id], 3));

		if ( (is_array ($node_actions)) and ($node_actions[$class_id]) )
		{
			$tree_opts["root_url"] = $this->mk_my_orb ($node_actions[$class_id], array(
				"id" => $o->id (),
				"return_url" => get_ru(),
			), $class_name);
		}
		else
		{
			$tree_opts["root_url"] = aw_url_change_var ($var, $arr["root_item"]->id(), $target_url);
		}

		$class_id = $root_item->class_id ();
		$class_name = strtolower (substr ($aw_classes[$class_id], 3));

		if ( (is_array ($node_actions)) and ($node_actions[$class_id]) )
		{
			$url = $this->mk_my_orb ($node_actions[$class_id], array(
				"id" => $o->id (),
				"return_url" => get_ru(),
			), $class_name);
		}
		else
		{
			$url = aw_url_change_var ($var, $root_item->id(), $target_url);
		}

		$tv->start_tree($tree_opts);
		if (!$arr["no_root_item"])
		{
			$tv->add_item(0,array(
				"name" => parse_obj_name($root_item->name()),
				"id" => $root_item->id(),
				"url" => $url,
			));
		}

		$ic = get_instance("core/icons");

		$ol = $ot->to_list();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$oname = parse_obj_name($o->name());
			if(isset($arr["tree_opts"]["item_name_length"]))
			{
				$oname = substr($oname, 0, $arr["tree_opts"]["item_name_length"]).(strlen($oname) > 20 ? "..." : "");
			}
			$oid = $o->id();
			$class_id = $o->class_id();

			if ($var && $_GET[$var] == $oid)
			{
				$oname = "<b>".$oname."</b>";
			}

			if ( ($tv->tree_type == TREE_DHTML_WITH_CHECKBOXES) and is_array ($arr["checkbox_class_filter"]) and in_array ($class_id, $arr["checkbox_class_filter"]) )
			{
				if (is_array ($this->checked_nodes) and in_array ($oid, $this->checked_nodes))
				{
					$checkbox_status = 1;
				}
				else
				{
					$checkbox_status = 0;
				}
			}
			elseif ( ($tv->tree_type == TREE_DHTML_WITH_BUTTONS) and is_array ($arr["checkbox_class_filter"]) and in_array ($class_id, $arr["checkbox_class_filter"]) )
			{
				$checkbox_status = "button";
			}
			else
			{
				$checkbox_status = "undefined";
			}

			$class_name = strtolower (substr ($aw_classes[$class_id], 3));

			if ( (is_array ($node_actions)) and ($node_actions[$class_id]) )
			{
				$url = html::get_change_url($oid, array("return_url" => get_ru()));
			}
			else
			{
				$url = aw_url_change_var ($var, $oid, $target_url);
			}

			$parent = $o->parent();
			if ($arr["no_root_item"] && $parent == $root_item->id())
			{
				$parent = 0;
			}
			if (!$arr["icon"])
			{
				$icon = (($class_id == CL_MENU) ? NULL : $ic->get_icon_url($class_id,""));
			}	
			else
			{
				$icon = $arr["icon"];
			}
			$tv->add_item($parent,array(
				"name" => $oname,
				"id" => $oid,
				"url" => $url,
				"iconurl" => $icon,
				"checkbox" => $checkbox_status,
			));
		}
		return $tv;
	}

	//	  only_one_level_opened - set to 1 if you want to show one tree depth at a time
	function set_only_one_level_opened($value)
	{
		$this->only_one_level_opened = $value;
	}
};
?>
