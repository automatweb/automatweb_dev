<?php

/*

@classinfo syslog_type=ST_OBJECT_TREE relationmgr=yes

@groupinfo folders caption=Kaustad
@groupinfo clids caption=Objektit&uuml;&uuml;bid

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property folders type=text store=no group=folders callback=callback_get_menus
@caption Kataloogid

@property show_folders type=checkbox ch_value=1 field=meta method=serialize
@caption N&auml;ita katalooge

@property js_tree type=checkbox ch_value=1 field=meta method=serialize
@caption Puu puhul kasuta javascripti

@property clids type=callback callback=get_clids group=clids store=no
@caption Klassid

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

*/


class object_treeview extends class_base
{
	function object_treeview()
	{
		$this->init(array(
			'tpldir' => 'contentmgmt/object_tree',
			'clid' => CL_OBJECT_TREE
		));
		$this->sub_merge = 1;
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	function show($arr)
	{
		extract($arr);
		$ob = obj($id);

		$this->read_template('show.tpl');

		// returns an array of object id's that are folders that are in the object
		$fld = $this->_get_folders($ob);

		// get all objects to show
		$ol = $this->_get_objects($ob, $fld);

		// make folders
		$this->vars(array(
			"FOLDERS" => $this->_draw_folders($ob, $ol, $fld)
		));

		$cnt = 0;
		$c = "";
		foreach($ol as $oid)
		{
			$od = obj($oid);

			$target = "";
			if ($od->class_id() == CL_EXTLINK)
			{
				$li = get_instance("links");
				list($url,$target,$caption) = $li->draw_link($oid);
			}
			else
			if ($od->class_id() == CL_FILE)
			{
				$fi = get_instance("file");
				$fd = $fi->get_file_by_id($oid);
				$url = $fi->get_url($oid,$fd["name"]);
				if ($fd["newwindow"])
				{
					$target = "target=\"_blank\"";
				}
			}
			else
			{
				$url = $this->cfg["baseurl"]."/".$oid;
			}
			classload("icons", "image");
			$this->vars(array(
				"show" => $url,
				"name" => $od->name(),
				"target" => $target,
				"type" => $this->cfg["classes"][$od->class_id()]["name"],
				"add_date" => $this->time2date($od->modified(), 2),
				"icon" => image::make_img_tag(icons::get_icon_url($od->class_id(), $od->name()))
			));

			if ($this->is_template("FILE_ODD"))
			{
				if ($cnt % 2)
				{
					$c.=$this->parse("FILE_ODD");
				}
				else
				{
					$c.=$this->parse("FILE");
				}
			}
			else
			{
				$c .= $this->parse("FILE");
			}

			$cnt++;
		}
		$this->vars(array(
			"FILE" => $c,
			"FILE_ODD" => ""
		));

		return $this->parse();
	}

	function _get_objects($ob, $folders)
	{
		$ret = array();

		// right. if the user has said, that no tree should be shown
		// then get files in all selected folders
		if (!$ob->meta('show_folders'))
		{
			$parent = $folders;
		}
		else
		{
			// if the folder is specified in the url, then show that
			// else, the first selected folder
			reset($folders);
			list(,$parent) = each($folders);

			if ($GLOBALS["tv_sel"])
			{
				$parent = $GLOBALS["tv_sel"];
			}
		}
		if (!is_array($ob->meta('clids')) || count($ob->meta('clids')) < 1)
		{
			return array();
		}

		$ol = new object_list(array(
			"parent" => $parent,
			"status" => STAT_ACTIVE,
			"class_id" => $ob->meta('clids'),
			"sort_by" => "objects.modified DESC"
		));

		return $this->make_keys($ol->ids());
	}

	function _get_folders($ob)
	{
		// go over all related menus and add subtree id's together if the user has so said. 
		$ret = array();
		
		$sub = $ob->meta("include_submenus");
   		$igns = $ob->meta("ignoreself");

		$conns = $ob->connections_from(array(
			"type" => RELTYPE_FOLDER
		));
		foreach($conns as $conn)
		{
			$c_o = $conn->to();

			$cur_ids = array();

			if ($sub[$c_o->id()])
			{
				$_ot = new object_tree(array(
					"class_id" => CL_MENU,
					"parent" => $c_o->id(),
					"status" => STAT_ACTIVE
				));
				$cur_ids = $_ot->ids();
			}

//			if (!$igns[$c_o->id()])
//			{
				$cur_ids[] = $c_o->id();
//			}

			foreach($cur_ids as $c_id)
			{
				$ret[$c_id] = $c_id;
			}
		}

		if (count($ret) < 1)
		{
			$ret[$ob->parent()] = $ob->parent();
		}
		return $ret;
	}

	function _draw_folders($ob, $ol, $folders)
	{
		if (!$ob->meta('show_folders'))
		{
			return;
		}

		classload("icons");
		// use treeview widget
		$tv = get_instance("vcl/treeview");
		$tv->start_tree(array(
			"root_name" => "",
			"root_url" => "",
			"root_icon" => "",
			"type" => ($ob->meta('js_tree') ? TREE_JS : TREE_HTML)
		));
		// now, insert all folders defined
		// but first, leave out all folders that are set don't show self
		$ar = new aw_array($ob->meta('ignoreself'));
		$ignoreself = $ar->get();

		
		foreach($folders as $fld)
		{
			$i_o = obj($fld);
			$parent = 0;
			if (in_array($i_o->parent(),$folders))
			{
				$parent = $i_o->parent();
			}

			// find modification time
			$tm = $i_o->modified();
			foreach($ol as $o_oid)
			{
				$o_o = obj($o_oid);

				if ($o_o->parent() == $fld && $o_o->modified() > $tm)
				{
					$tm = $o_o->modified();
				}
			}

			$tv->add_item($parent, array(
				"id" => $fld,
				"name" => $i_o->name(),
				"url" => aw_url_change_var("tv_sel", $fld),
				"icon" => icons::get_icon_url($i_o->class_id(), ""),
				"data" => array(
					"changed" => $this->time2date($tm, 2)
				)
			));
		}
		$tv->set_selected_item($GLOBALS["tv_sel"]);
		return $tv->finalize_tree();
	}

	function get_clids($arr)
	{
		$clids = $arr["obj_inst"]->meta("clids");

		$ret = array();
		classload("aliasmgr");
		$a = aliasmgr::get_clid_picker();
		foreach($a as $clid => $clname)
		{
			$rt = "clid_".$clid;
			$ret[$rt] = array(
				'name' => $rt,
				'caption' => $clname,
				'type' => 'checkbox',
				'ch_value' => 1,
				'store' => 'no',
				'group' => 'clids',
				'value' => ($clids[$clid] == $clid)
			);
		}
		return $ret;
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];
		if ($prop['name'] == 'clids')
		{
			$_clids = array();
			classload("aliasmgr");
			$a = aliasmgr::get_clid_picker();
			foreach($a as $clid => $clname)
			{
				$rt = "clid_".$clid;
				if (isset($arr["form_data"][$rt]) && $arr["form_data"][$rt] == 1)
				{
					$_clids[$clid] = $clid;
				}
			}
			$arr["obj_inst"]->set_meta("clids", $_clids);
		}
		if ($prop["name"] == "folders")
		{
			$arr['obj_inst']->set_meta("include_submenus",$arr["form_data"]["include_submenus"]);
			$arr['obj_inst']->set_meta("ignoreself",$arr["form_data"]["ignoreself"]);
		};

		return PROP_OK;
	}

	function callback_get_menus($args = array())
	{
		$prop = $args["prop"];
		$nodes = array();

		// now I have to go through the process of setting up a generic table once again
		load_vcl("table");
		$this->t = new aw_table(array(
			"prefix" => "ot_menus",
			"layout" => "generic"
		));
		$this->t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
		));
		$this->t->define_field(array(
			"name" => "check",
			"caption" => "k.a. alammenüüd",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));
		$this->t->define_field(array(
			"name" => "ignoreself",
			"caption" => "&auml;ra n&auml;ita peamen&uuml;&uuml;d",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));

		$include_submenus = $args["obj_inst"]->meta("include_submenus");
		$ignoreself = $args["obj_inst"]->meta("ignoreself");


		$conns = $args["obj_inst"]->connections_from(array(
			"type" => RELTYPE_FOLDER
		));

		foreach($conns as $conn)
		{
			$c_o = $conn->to();

			$this->t->define_data(array(
				"oid" => $c_o->id(),
				"name" => $c_o->path_str(array(
					"max_len" => 3
				)) . "/" . $c_o->name(),
				"check" => html::checkbox(array(
					"name" => "include_submenus[".$c_o->id()."]",
					"value" => $c_o->id(),
					"checked" => $include_submenus[$c_o->id()],
				)),
				"ignoreself" => html::checkbox(array(
					"name" => "ignoreself[".$c_o->id()."]",
					"value" => $c_o->id(),
					"checked" => $ignoreself[$c_o->id()],
				)),
			));
		};
		
		$nodes[$prop["name"]] = array(
			"type" => "text",
			"caption" => $prop["caption"],
			"value" => $this->t->draw(),
		);
		return $nodes;
	}
}
?>
