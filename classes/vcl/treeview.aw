<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/treeview.aw,v 1.10 2003/03/20 18:28:36 duke Exp $
// treeview.aw - tree generator
/*
        @default table=objects
        @default group=general

        @property root type=select field=meta method=serialize
        @caption Root objekt

	@property rootcaption type=textbox field=meta method=serialize
	@caption Root objekti nimi
	
	@property icon_root type=objpicker clid=CL_ICON field=meta method=serialize
	@caption Root objekti ikoon
        
	@property treetype type=select field=meta method=serialize
        @caption Puu tüüp

	@property icon_folder_open type=objpicker clid=CL_ICON field=meta method=serialize
	@caption Lahtise kausta ikoon

	@property icon_folder_closed type=objpicker clid=CL_ICON field=meta method=serialize
	@caption Kinnise kausta ikoon
*/
class treeview extends class_base
{
	function treeview($args = array())
	{
		$this->init(array(
			"tpldir" => "treeview",
			"clid" => CL_TREEVIEW,
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "root":
				$ob = get_instance("objects");
                                $data["options"] = $ob->get_list();
				break;

			case "treetype":
				$data["options"] = array("" => "--vali--","dhtml" => "DHTML (Ftiens)");
				break;
		};
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

		$rootobj = $this->get_object($root);
		if (!$rootobj)	
		{
			return "invalid root object";
		};
		if (isset($obj["meta"]["treetype"]))
		{
			$type = $obj["meta"]["treetype"];
		}
		else
		{
			$type = "dhtml";
		};
		$this->read_template("ftiens.tpl");
		$this->arr = array();
		
		$this->clidlist = (is_array($args["config"]["clid"])) ? $args["config"]["clid"] : CL_PSEUDO; 


		// I need a way to display all kind of documents here, and not only
		// menus. So, how on earth am I going to do that.

		// if the caller specified clid list, then we list all of those objects,
		// if not, then only menus
		
                // listib koik menyyd ja paigutab need arraysse	

		$this->ic = get_instance("icons");


                // objektipuu
                $this->rec_tree($root);

		$tr = $this->generate_tree($root);

		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $root,
			"linktarget" => isset($args["linktarget"]) ? $args["linktarget"] : "",
			"shownode" => isset($args["shownode"]) ? $args["shownode"] : "",
			"rootname" => isset($obj["meta"]["rootcaption"]) ? $obj["meta"]["rootcaption"] : $rootobj["name"],
			"rooturl" => $this->do_item_link($rootobj),
			"icon_root" => isset($obj["meta"]["icon_root"])? $this->mk_my_orb("show",array("id" => $obj["meta"]["icon_root"]),"icons") : "/automatweb/images/aw_ikoon.gif",
                ));

		$retval = $this->parse();
		return $retval;
	}

	////
	// !Public/ORB interface
	function show($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		return $this->generate(array(
			"urltemplate" => $args["urltemplate"],
			"config" => $obj["meta"],
		));
	}
	
	function rec_tree($parent)
	{
		$_objlist = $this->get_objects_by_class(array(
			"parent" => $parent,
			"class" => $this->clidlist,
		));
		
		while ($row = $this->db_next())
		{
			$row["name"] = str_replace("\"","&quot;", $row["name"]);
			$this->arr[$row["parent"]][] = $row;
			$this->save_handle();
			$this->rec_tree($row["oid"]);
			$this->restore_handle();
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
			$icon_url = ($row["class_id"] == CL_PSEUDO) ? "" : $this->ic->get_icon_url($row["class_id"],"");
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
	// !Creates a tree from an array (invokes itself recursively)
	// parent(int) - current root node
	// data(arr) - pointer to the array
	function create_tree_from_array($args = array())
	{
		if (!is_array($args["data"][$args["parent"]]))
		{
			return;
		};
		$this->read_template("ftiens.tpl");
		$tr = $this->_rec_tree_from_array(array(
			//"parent" => $args["parent"],
			"parent" => 1,
			"data" => &$args["data"],
		));
		$this->vars(array(
			"TREE" => $tr,
			"root" => 1,
			"rootname" => $args["data"][0][1]["name"],
			"rooturl" => $args["data"][0][1]["link"],
			"linktarget" => isset($args["linktarget"]) ? $args["linktarget"] : "",
			"shownode" => isset($args["shownode"]) ? $args["shownode"] : "",
		));
		return $this->parse();
	}


	function _rec_tree_from_array($args = array())
	{
		$ret = "";
		reset($args["data"][$args["parent"]]);
		while (list($key,$row) = each($args["data"][$args["parent"]]))
		{
			if (isset($args["data"][$key]) && is_array($args["data"][$key]))
			{
				$sub = $this->_rec_tree_from_array(array(
					"parent" => $key,
					"data" => &$args["data"],
				));
			}
			else
			{
				$sub = "";
			};
			
			$this->vars(array(
				"name" => $row["name"],
				"id" => $key,
				"parent" => $args["parent"],
				"iconurl" => $row["icon_url"],
				"url" => $row["link"],
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
		};
		return $ret;
	}

	////
	// !inits tree
	// params:
	//   root_name - root menu name
	//   root_url - root menu url
	//   root_icon - root menu icon
	function start_tree($arr)
	{
		$this->items = array();
		$this->tree_dat = $arr;
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
		$this->items[$parent][] = $item;
	}

	function finalize_tree()
	{
		$this->read_template("ftiens.tpl");
		// objektipuu
		$tr = $this->req_finalize_tree(0);
		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => 0,
			"rootname" => $this->tree_dat["root_name"],
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
};
?>
