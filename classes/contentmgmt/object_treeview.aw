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

@property show_add type=checkbox ch_value=1 field=meta method=serialize
@caption N&auml;ita toolbari

@property js_tree type=checkbox ch_value=1 field=meta method=serialize
@caption Puu puhul kasuta javascripti

@property groupfolder_acl type=checkbox ch_value=1 field=meta method=serialize
@caption &Otilde;igused piiratud grupi kataloogide j&auml;rgi

@property show_notact type=checkbox ch_value=1 field=meta method=serialize
@caption N&auml;ita mitteaktiivseid objekte

@property clids type=callback callback=get_clids group=clids store=no
@caption Klassid

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

@reltype ADD_TYPE value=2 clid=CL_OBJECT_TYPE
@caption lisatav objektit&uuml;&uuml;p

@reltype ALL_ACCESS_GRP value=3 clid=CL_GROUP
@caption projekti haldaja grupp

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
		return $this->show(array('id' => $args['alias']['target']));
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
			$act = "";
			if ($this->can("edit", $od->id()))
			{
				$act .= html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $od->id(), "section" => $od->parent()), $this->cfg["classes"][$od->class_id()]["file"]),
					"caption" => html::img(array(
						"border" => 0,
						"url" =>  aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif"
					))
				));
			}
			if ($this->can("delete", $od->id()))
			{
				if ($act != "")
				{
					$act .= " ";
				}
				$act .= html::href(array(
					"url" => $this->mk_my_orb("delete", array("id" => $od->id(), "return_url" => urlencode(aw_ini_get("baseurl").aw_global_get("REQUEST_URI")))),
					"caption" => html::img(array(
						"border" => 0,
						"url" =>  aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif"
					))
				));
			}
			$adder = $od->createdby();
			$modder = $od->modifiedby();
			$this->vars(array(
				"show" => $url,
				"name" => $od->name(),
				"target" => $target,
				"comment" => $od->comment(),
				"type" => $this->cfg["classes"][$od->class_id()]["name"],
				"add_date" => $this->time2date($od->created(), 2),
				"mod_date" => $this->time2date($od->modified(), 2),
				"adder" => $adder->name(),
				"modder" => $modder->name(),
				"icon" => image::make_img_tag(icons::get_icon_url($od->class_id(), $od->name())),
				"act" => $act
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

		$res = $this->parse();
		if ($ob->prop("show_add") && count($fld))
		{
			$res = $this->_get_add_toolbar($ob).$res;
		}
		return $res;
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
			"status" => $ob->prop("show_notact") ? array(STAT_ACTIVE, STAT_NOTACTIVE) : STAT_ACTIVE,
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

		$glo = aw_global_get("gidlist_oid");

		// check if the user is an admin
		if (!$ob->prop("groupfolder_acl"))
		{
			$is_admin = true;
		}
		else
		{
			$is_admin = false;
		}
																		
		$adm_c = $ob->connections_from(array(
			"type" => RELTYPE_ALL_ACCESS_GRP
		));
		foreach($adm_c as $adm_conn)
		{
			$adm_g = $adm_conn->prop("to");
			if (isset($glo[$adm_g]))
			{
				$is_admin = true;
			}
		}

		$conns = $ob->connections_from(array(
			"type" => RELTYPE_FOLDER
		));
		foreach($conns as $conn)
		{
			$c_o = $conn->to();
			if (!isset($this->first_folder))
			{
				$this->first_folder = $c_o->id();
			}
			
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

			if (!$igns[$c_o->id()])
			{
				$cur_ids[] = $c_o->id();
			}

			foreach($cur_ids as $c_id)
			{
				$add = $is_admin;
				if (!$is_admin)
				{
					$c_id_o = obj($c_id);
					$c_id_gr = $c_id_o->connections_from(array(
						"type" => RELTYPE_ACL_GROUP
					));
					foreach($c_id_gr as $c_id_gr_c)
					{
						if (isset($glo[$c_id_gr_c->prop("to")]))
						{
							$add = true;
							break;
						}
					}
				}

				if ($add)
				{
					$ret[$c_id] = $c_id;
				}
			}
		}

/*		if (count($ret) < 1)
		{
			$ret[$ob->parent()] = $ob->parent();
		}*/
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
				"comment" => $i_o->comment(),
				"data" => array(
					"changed" => $this->time2date($tm, 2)
				)
			));
		}
		$tv->set_selected_item($GLOBALS["tv_sel"]);

		$pms = array();
		// here's the trick. if the treeview is set to show_as_treeview for any section and we got here via an orb action in the url
		// then show the tree from the current section
		// 
		// hehe, heuristics rule ;)
		$t_c = $ob->connections_to(array(
			"type" => 8,	// RELTYPE_OBJ_TREE from menu
			"from.class_id" => CL_MENU
		));
		
		if (isset($GLOBALS["class"]) && count($t_c) > 0)
		{
			$pms["rootnode"] = aw_global_get("section");
		}
		
		return $tv->finalize_tree($pms);
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

	function _get_add_toolbar($ob)
	{
		$this->tpl_init("automatweb/menuedit");
		$this->read_template("js_add_menu.tpl");

		$types_c = $ob->connections_from(array(
			"type" => RELTYPE_ADD_TYPE
		));

		$menu = "";
		$classes = aw_ini_get("classes");

		$parent = $GLOBALS["tv_sel"] ? $GLOBALS["tv_sel"] : $this->first_folder;
		$ot = get_instance("admin/object_type");
		foreach($types_c as $c)
		{
			$c_o = $c->to();
			$this->vars(array(
				"url" => $this->mk_my_orb("new", array("parent" => $parent, "section" => $parent), $classes[$c_o->prop("type")]["file"]),
				"caption" => $classes[$c_o->prop("type")]["name"]
			));
			$menu .= $this->parse("MENU_ITEM");
		}
		$this->vars(array(
			"menu_id" => "aw_menu_0",
			"MENU_ITEM" => $menu
		));
		$this->vars(array("MENU" => $this->parse("MENU")));
		
		

		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "add",
			"tooltop" => "Uus",
			"url" => "#",
			"onClick" => "return buttonClick(event, 'aw_menu_0');",
			"img" => "new.gif",
			"imgover" => "new_over.gif",
			"class" => "menuButton",
		));
		return $this->parse().$tb->get_toolbar();
	}

	function obj_delete($arr)
	{
		error::throw_if(!$arr["id"], array(
			"id" => ERR_PARAM,
			"msg" => "object_treeview::obj_delete(): no ibject id specified!"
		));

		$o = obj($arr["id"]);
		$o->delete();
	
		return $arr["return_url"];
	}

	function get_folders_as_object_list($object)
	{
		$t_id = $object->prop("show_object_tree");
		$first_level = true;
		if (!$t_id)
		{
			$pa = $object->path();
			foreach($pa as $p_o)
			{
				$t_id = $p_o->prop("show_object_tree");
				if ($t_id)
				{
					break;
				}
			}
			$first_level = false;
		}
		$this->tree_ob = obj($t_id);
	
		$ol = new object_list();

		$folders = $this->_get_folders($this->tree_ob);
		foreach($folders as $fld)
		{
			$i_o = obj($fld);
			$parent = 0;
			if (in_array($i_o->parent(),$folders))
			{
				$parent = $i_o->parent();
			}
			
			if ($first_level)
			{
				if ($parent == 0)
				{
					$ol->add($fld);
				}
			}
			else
			{
				if ($parent == $object->id())
				{
					$ol->add($fld);
				}
			}
		}

		return $ol;
	}

	function make_menu_link($sect_obj)
	{
		$link = $this->mk_my_orb("show", array("id" => $this->tree_ob->id(), "tv_sel" => $sect_obj->id(), "section" => $sect_obj->id()));;
		return $link;
	}

	function get_yah_link($tree, $cur_menu)
	{
		return $this->mk_my_orb("show", array("id" => $tree, "tv_sel" => $cur_menu->id(), "section" => $cur_menu->id()));
	}
}
?>
