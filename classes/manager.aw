<?php
// that's your basic file manager with 2 frames class. On the left side is a
// menu tree just like in /automatweb right now, on the right side we show objects
// someday this can perhaps replace the current menuedit framework
class manager extends aw_template
{
	function manager($args = array())
	{
		$this->init("manager");
		$this->root = 42527;
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
			"uid" => UID,
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
			"imgurl"    => $baseurl."/img",
			"tbgcolor" => "#C3D0DC",
		));

		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");

		$t->set_header_attribs(array(
			"class" => "manager",
			"action" => "browse",
			"parent" => $parent,
		));

		$t->define_field(array(
			"name" => "folder",
			"caption" => "&nbsp;",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
		));
		
		$t->define_field(array(
			"name" => "name",
			"caption" => "nimi",
			"talign" => "center",
			"sortable" => 1,
			"nowrap" => "1",
		));
		
		$t->define_field(array(
			"name" => "ord",
			"caption" => "jrk",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
		));
		
		$t->define_field(array(
			"name" => "active",
			"caption" => "Ak.",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
		));
		
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
			"nowrap" => "1",
		));
		
		$t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
			"nowrap" => "1",
		));
		
		$t->define_field(array(
			"name" => "action",
			"caption" => "Tegevus",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
		));

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
				"name" => "<a href='$oplink'>$row[name]</a>",
				"ord" => "<input type='textbox' name='ord[$oid]' size='2' value='$row[jrk]'>",
				"active" => "<input type='checkbox' value='2' name='active[$oid]' $active>",
				"modifiedby" => $row["modifiedby"],
				"modified" => $this->time2date($row["modified"],2),
				"action" => "<a href='$chlink'><img src='/automatweb/images/blue/obj_settings.gif' border='0'></a>",
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
				"action" => "<a href='$chlink'><img src='/automatweb/images/blue/obj_settings.gif' border='0'></a>",
			));

		}
		
		$t->sort_by(array("field" => $args["sortby"]));

		$this->vars(array(
			"menu_table" => $t->draw(),
			"menu_reforb" => $this->mk_reforb("submit_browse",array("parent" => $parent)),
			"add_menu" => $this->mk_my_orb("add_menu",array("parent" => $parent)),
			"add_file" => $this->mk_my_orb("new",array("parent" => $parent),"file"),
		));
		return $this->parse();
	}

	////
	// !Submits a menu browse view
	function submit_browse($args = array())
	{
		$this->quote($args);
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
		}
		else
		{
			$menu = array();
		};
		$this->read_template("edit_menu.tpl");
		$this->vars(array(
			"name" => $menu["name"],
			"reforb" => $this->mk_reforb("submit_menu",array("id" => $id,"parent" => $parent)),
		));
		return $this->parse();
	}

	function submit_menu($args = array())
	{
		$this->quote($args);
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
		$me->invalidate_menu_cache(true);
		return $this->mk_my_orb("edit_menu",array("id" => $id),"",false,1);
	}
}
?>
