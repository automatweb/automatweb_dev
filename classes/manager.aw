<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/manager.aw,v 2.5 2002/12/02 11:18:52 kristo Exp $
// that's your basic file manager with 2 frames class. On the left side is a
// menu tree just like in /automatweb right now, on the right side we show objects
// someday this can perhaps replace the current menuedit framework
class manager extends aw_template
{
	function manager($args = array())
	{
		$this->init("manager");
		$this->root = $this->cfg["root"];
		$this->lc_load("manager","lc_manager");

	}

	////
	// !Frameset
	function frames($args = array())
	{
		$this->read_template("frameset.tpl");
		$this->vars(array(
			"left" => $this->mk_my_orb("tree",array()),
			"right" => $this->mk_my_orb("browse",array()),
		));
		return $this->parse();
	}

	////
	// !Left side, dynamic menu?
	function tree($args = array())
	{
		extract($args);
		$this->read_template("tree.tpl");
		// gen menu!
		$mc = get_instance("menu_cache");
                $mc->make_caches();
                $this->subs =  $mc->get_ref("subs");
                $this->mar =  $mc->get_ref("mar");
                $this->mpr =  $mc->get_ref("mpr");
		$this->tree = "";
		$this->_traverse($this->root);
		$this->vars(array(
			"root" => $this->root,
			"uid" => aw_global_get("uid"),
			"rooturl" => $this->mk_my_orb("browse",array("parent" => $this->root)),
			"TREE" => $this->tree,
		));
		return $this->parse();
	}

	////
	// !Right side, content
	function browse($args = array())
	{
		extract($args);
		$this->read_template("browse.tpl");
		$parent = $parent ? $parent : $this->root;
		$this->_make_yah($parent);
		
		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "manager_menus",
			"tbgcolor" => "#C3D0DC",
		));
		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
		$t->define_field(array(
			"name" => "folder",
			"caption" => "&nbsp;",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => LC_MANAGER_COLS_NAME,
			"talign" => "center",
			"sortable" => 1,
			"nowrap" => "1",
		));

		if ($this->prog_acl("view", PRG_MENUEDIT))
		{

			$t->define_field(array(
				"name" => "ord",
				"caption" => LC_MANAGER_COLS_ORD,
				"talign" => "center",
				"align" => "center",
				"nowrap" => "1",
			));
		
			$t->define_field(array(
				"name" => "active",
				"caption" => LC_MANAGER_COLS_ACT,
				"talign" => "center",
				"align" => "center",
				"nowrap" => "1",
			));

		}
		
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => LC_MANAGER_COLS_MODIFIEDBY,
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
			"nowrap" => "1",
		));
		
		$t->define_field(array(
			"name" => "modified",
			"caption" => LC_MANAGER_COLS_MODIFIED,
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
			"nowrap" => "1",
		));

		if ($this->prog_acl("view", PRG_MENUEDIT))
		{

			$t->define_field(array(
				"name" => "action",
				"caption" => LC_MANAGER_COLS_SETTINGS,
				"talign" => "center",
				"align" => "center",
				"nowrap" => "1",
			));
			
			$t->define_field(array(
				"name" => "delete",
				"caption" => LC_MANAGER_COLS_DELETE,
				"talign" => "center",
				"align" => "center",
				"nowrap" => "1",
			));

		}	
		$q = "SELECT * FROM objects WHERE class_id = 1 AND status != 0 AND parent = '$parent' ORDER BY jrk,name";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$oid = $row["oid"];
			$chlink = $this->mk_my_orb("edit_menu",array("id" => $row["oid"]));
			$oplink = $this->mk_my_orb("browse",array("parent" => $row["oid"]));
			$active = checked($row["status"] == 2);
			$t->define_data(array(
				"folder" => "<img src='/automatweb/images/ftv2folderclosed.gif'>",
				"name" => "&nbsp;<a href='$oplink'>$row[name]</a>",
				"ord" => "<input type='textbox' name='ord[$oid]' size='2' value='$row[jrk]'>",
				"active" => "<input type='checkbox' value='2' name='active[$oid]' $active>",
				"modifiedby" => $row["modifiedby"],
				"modified" => $this->time2date($row["modified"],2),
				"action" => "<a href='$chlink'><img src='/automatweb/images/blue/obj_settings.gif' alt='" . LC_MANAGER_HINT_SETTINGS ."' title='" . LC_MANAGER_HINT_SETTINGS . "' border='0'></a>", 
				"delete" => "<input type='checkbox' name='del[$row[oid]]' value='$row[oid]'>",
			));
		};

		$q = "SELECT * FROM objects WHERE class_id = " . CL_FILE . " AND parent = '$parent' ORDER BY name";
		$this->db_query($q);
		$awf = get_instance("file");
		while($row = $this->db_next())
		{
			$oid = $row["oid"];
			$chlink = $this->mk_my_orb("change",array("id" => $row["oid"]),"file");
			$dlink = $awf->get_url($row["oid"],$row["name"]);
			$active = checked($row["status"] == 2);
			$t->define_data(array(
				"folder" => "<img src='" . get_icon_url($row["class_id"],$row["name"]) . "'>",
				"name" => "<a href='$dlink'>$row[name]</a>",
				"ord" => "<input type='textbox' name='ord[$oid]' size='2' value='$row[jrk]'>",
				"active" => "<input type='checkbox' value='2' name='active[$oid]' $active>",
				"modifiedby" => $row["modifiedby"],
				"modified" => $this->time2date($row["modified"],2),
				"action" => "<a href='$chlink'><img src='/automatweb/images/blue/obj_settings.gif' alt='" . LC_MANAGER_HINT_SETTINGS . "' title='" . LC_MANAGER_HINT_SETTINGS ."' border='0'></a>", 
				"delete" => "<input type='checkbox' name='del[$row[oid]]' value='$row[oid]'>",
			));

		}

		// sort by name by default
		$t->set_default_sortby("name");
		$t->sort_by();

		$this->vars(array(
			"menu_table" => $t->draw(),
			"menu_reforb" => $this->mk_reforb("submit_browse",array("parent" => $parent)),
			"add_menu" => $this->mk_my_orb("add_menu",array("parent" => $parent)),
			"add_file" => $this->mk_my_orb("new",array("parent" => $parent),"file"),
		));

		if ($this->prog_acl("view", PRG_MENUEDIT))
		{
			$this->vars(array("ADMIN" => $adm));
		};
		return $this->parse();
	}

	////
	// !Submits a menu browse view
	function submit_browse($args = array())
	{
		extract($args);
		if (is_array($ord))
		{
			foreach($ord as $key => $val)
			{
				$val = (int)$val;
				$act = ($active[$key] == 2) ? 2 : 1;
				$q = "UPDATE objects SET jrk = '$val',status = '$act' WHERE oid = '$key'";
				$this->db_query($q,true);
			}
		};
		return $this->mk_my_orb("browse",array("parent" => $parent),"",false,1);
	}

	////
	//  !Deletes selected objects
	function delete($args = array())
	{
		extract($args);
		if (is_array($del))
		{
			$del_obj = join(",",$del);
			$q = "UPDATE objects SET status = 0  WHERE oid IN ($del_obj)";
			$this->db_query($q);
		}
		return $this->mk_my_orb("browse",array("parent" => $parent),"",false,1);
	}


	////
	// !Traverse a single level
	function _traverse($parent)
	{
		$baseurl = aw_ini_get("baseurl");
		foreach($this->mpr[$parent] as $key => $row)
		{
			if ($row["status"] != 2)
			{
				continue;
			}

			$iconurl = isset($row["icon_id"]) && $row["icon_id"] != "" ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : $baseurl."/automatweb/images/ftv2doc.gif";
			$subs = $this->subs[$row["oid"]];
			$this->vars(array(
				"id" => $row["oid"],
				"parent" => $row["parent"],
				"name" => $row["name"],
				"url" => $this->mk_my_orb("browse",array("parent" => $row["oid"]),"",false,1),
				"iconurl" => $iconurl,
			));
			$this->tree .= ($subs > 0) ? $this->parse("TREE") : $this->parse("DOC");
			if (is_array($this->mpr[$row["oid"]]))
			{
				$this->_traverse($row["oid"]);
			};
		}
	}

	////
	// !Make yah
	function _make_yah($id)
	{
		$chain = $this->get_object_chain($id,false,$this->root);
		$yah = "";
		$rm = $this->get_object($this->root);
		$chain = array($rm["oid"] => $rm) + $chain;
		if (is_array($chain))
		{
			foreach($chain as $oid => $obj)
			{
				$this->vars(array(
					"yah_link" => $this->mk_my_orb("browse",array("parent" => $obj["oid"])),
					"yah_name" => $obj["name"],
				));
				$yah .= $this->parse("YAH");
			};
		}
		$this->vars(array("YAH" => $yah));
	}

			

	////
	// !Show the form for editing or adding a new menu
	function edit_menu($args = array())
	{
		extract($args);
		if ($id)
		{
			$menu = $this->get_object($id);
			$caption = LC_MANAGER_EDIT_FOLDER . ": $menu[name]";
                        $prnt = $menu["parent"];
		}
		else
		{
			$menu = array();
			$caption = LC_MANAGER_ADD_FOLDER;
                        $prnt = $parent;
		};
		$this->read_template("edit_menu.tpl");
		$this->_make_yah($prnt,$caption);
		$this->vars(array(
			"name" => $menu["name"],
			"reforb" => $this->mk_reforb("submit_menu",array("id" => $id,"parent" => $parent)),
		));
		return $this->parse();
	}

	function submit_menu($args = array())
	{
		extract($args);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"status" => 2,
				"class_id" => CL_PSEUDO,
			));
			$this->db_query("INSERT INTO menu (id,type) VALUES($id,70)");
		};
		$me = get_instance("menuedit");
		$me->invalidate_menu_cache(array($id));
		return $this->mk_my_orb("edit_menu",array("id" => $id),"",false,1);
	}
}
?>
