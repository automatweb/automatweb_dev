<?php

/*

@classinfo syslog_type=ST_OBJECT_TREE relationmgr=yes

@groupinfo folders caption=Kaustad
@groupinfo clids caption=Objektit&uuml;&uuml;bid
@groupinfo styles caption=Stiilid
@groupinfo columns caption=Tulbad

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

@property tree_type type=chooser  field=meta method=serialize default=1
@caption Puu n&auml;itamise meetod

@property groupfolder_acl type=checkbox ch_value=1 field=meta method=serialize
@caption &Otilde;igused piiratud grupi kataloogide j&auml;rgi

@property show_notact type=checkbox ch_value=1 field=meta method=serialize
@caption N&auml;ita mitteaktiivseid objekte

@property clids type=callback callback=get_clids group=clids store=no
@caption Klassid

@property title_bgcolor type=colorpicker field=meta method=serialize group=styles
@caption Pealkirja taustav&auml;rv

@property even_bgcolor type=colorpicker field=meta method=serialize group=styles
@caption Paaris rea taustav&auml;rv

@property odd_bgcolor type=colorpicker field=meta method=serialize group=styles
@caption Paaritu rea taustav&auml;rv

@property header_css type=relpicker reltype=RELTYPE_CSS field=meta method=serialize  group=styles
@caption Pealkirja stiil

@property line_css type=relpicker reltype=RELTYPE_CSS field=meta method=serialize  group=styles
@caption Rea stiil

@property columns type=callback callback=callback_get_columns field=meta method=serialize group=columns
@caption Tulbad

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

@reltype ADD_TYPE value=2 clid=CL_OBJECT_TYPE
@caption lisatav objektit&uuml;&uuml;p

@reltype ALL_ACCESS_GRP value=3 clid=CL_GROUP
@caption projekti haldaja grupp

@reltype CSS value=4 clid=CL_GROUP
@caption css stiil

*/


class object_treeview extends class_base
{
	var $all_cols = array(
		"icon" => "Ikoon",
		"name" => "Nimi",
		"class_id" => "T&uuml;&uuml;p",
		"modified" => "Muutmise kuup&auml;ev",
		"modifiedby" => "Muutja",
		"change" => "Muuda",
		"select" => "Vali"
	);
	
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

	/**  
		
		@attrib name=show params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function show($arr)
	{
		extract($arr);
		$ob = obj($id);

		$this->read_template('show.tpl');

		$this->_insert_row_styles($ob);

		// returns an array of object id's that are folders that are in the object
		$fld = $this->_get_folders($ob);

		// get all objects to show
		$ol = $this->_get_objects($ob, $fld);

		// make folders
		$this->vars(array(
			"FOLDERS" => $this->_draw_folders($ob, $ol, $fld)
		));

		// get all related object types
		// and their cfgforms
		// and make a nice little lut from them.
		$class2cfgform = array();
		foreach($ob->connections_from(array("type" => RELTYPE_ADD_TYPE)) as $c)
		{
			$addtype = $c->to();
			if ($addtype->prop("use_cfgform"))
			{
				$class2cfgform[$addtype->prop("type")] = $addtype->prop("use_cfgform");
			}
		}

		$cnt = 0;
		$c = "";
		$sel_cols = $ob->meta("sel_columns");

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
				$fileSizeBytes = number_format(filesize($od->prop('file')),2);
				$fileSizeKBytes = number_format(filesize($od->prop('file'))/(1024),2);
				$fileSizeMBytes = number_format(filesize($od->prop('file'))/(1024*1024),2);
			}
			else
			if ($od->class_id() == CL_MENU)
			{
				$url = $this->mk_my_orb("show", array(
					"section" => $od->id(),
					"id" => $ob->id(),
					"tv_sel" => $od->id()
				));
			}
			else
			{
				$url = $this->cfg["baseurl"]."/".$oid;
			}
			classload("icons", "image");
			$act = "";
			if ($this->can("edit", $od->id()))
			{
				$fl = $this->cfg["classes"][$od->class_id()]["file"];
				if ($fl == "document")
				{
					$fl = "doc";
				}
				$act .= html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $od->id(), 
						"section" => $od->parent(),
						"cfgform" => $class2cfgform[$od->class_id()]
					), $fl),
					"caption" => html::img(array(
						"border" => 0,
						"url" =>  aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif"
					))
				));
			}
			if ($this->can("delete", $od->id()))
			{
				$delete = html::href(array(
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
				"name" => parse_obj_name($od->name()),
				"oid" => $od->id(),
				"target" => $target,
				"sizeBytes" => $fileSizeBytes,
				"sizeKBytes" => $fileSizeKBytes,
				"sizeMBytes" => $fileSizeMBytes,
				"comment" => $od->comment(),
				"type" => $this->cfg["classes"][$od->class_id()]["name"],
				"add_date" => $this->time2date($od->created(), 2),
				"mod_date" => $this->time2date($od->modified(), 2),
				"adder" => $adder->name(),
				"modder" => $modder->name(),
				"icon" => image::make_img_tag(icons::get_icon_url($od->class_id(), $od->name())),
				"act" => $act,
				"delete" => $delete,
				"comment" => $od->comment(),
				"bgcolor" => $this->_get_bgcolor($ob, $cnt)
			));

			$del = "";
			if ($this->can("delete", $od->id()))
			{
				$del = $this->parse("DELETE");
			}
			$this->vars(array(
				"DELETE" => $del
			));

			$tb = "";
			$no_tb = "";
			if ($ob->prop("show_add"))
			{
				$tb = $this->parse("HAS_TOOLBAR");
			}
			else
			{
				$no_tb = $this->parse("NO_TOOLBAR");
			}
			$this->vars(array(
				"HAS_TOOLBAR" => $tb,
				"NO_TOOLBAR" => $no_tb
			));

			// columns
			foreach($this->all_cols as $colid => $coln)
			{
				$str = "";
				if ($sel_cols[$colid] == 1)
				{
					$str = $this->parse("FILE_".$colid);
				}
				$this->vars(array(
					"FILE_".$colid => $str
				));
			}
			
			$c.=$this->parse("FILE");
			$cnt++;
		}


		$tb = "";
		$no_tb = "";
		if ($ob->prop("show_add"))
		{
			$tb = $this->parse("HEADER_HAS_TOOLBAR");
		}
		else
		{
			$no_tb = $this->parse("HEADER_NO_TOOLBAR");
		}
		$this->vars(array(
			"FILE" => $c,
			"HEADER_HAS_TOOLBAR" => $tb,
			"HEADER_NO_TOOLBAR" => $no_tb,
			"reforb" => $this->mk_reforb("submit_show", array(
				"return_url" => aw_global_get("REQUEST_URI"),
				"subact" => "0"
			))
		));

		// columns
		foreach($this->all_cols as $colid => $coln)
		{
			$str = "";
			if ($sel_cols[$colid] == 1)
			{
				$str = $this->parse("HEADER_".$colid);
			}
			$this->vars(array(
				"HEADER_".$colid => $str
			));
		}

		$res = $this->parse();
		if ($ob->prop("show_add"))
		{
			$res = $this->_get_add_toolbar($ob).$res;
		}
		return $res;
	}

	/**  
		
		@attrib name=submit_show params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_show($arr)
	{
		extract($arr);

		if ($subact == "delete")
		{
			$ol = new object_list(array("oid" => $sel));
			$ol->delete();
		}

		return $return_url;
	}

	function _get_objects($ob, $folders)
	{
		$ret = array();

		// if the folder is specified in the url, then show that
		if ($GLOBALS["tv_sel"])
		{
			$parent = $GLOBALS["tv_sel"];
		}
		else
		// right. if the user has said, that no tree should be shown
		// then get files in all selected folders
		if (!$ob->meta('show_folders'))
		{
			$parent = $folders;
		}

		if (!$parent)
		{
			// if parent can't be found. then get the objects from all the root folders
			$con = $ob->connections_from(array(
				"type" => RELTYPE_FOLDER
			));
			$parent = array();
			foreach($con as $c)
			{
				$parent[$c->prop("to")] = $c->prop("to");
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
			"sort_by" => "objects.modified DESC",
			"lang_id" => array()
		));
		$ol->sort_by_cb(array(&$this, "_obj_list_sorter"));

		return $this->make_keys($ol->ids());
	}

	function _obj_list_sorter($a, $b)
	{
		if ($a->class_id() == CL_MENU && $b->class_id() != CL_MENU)
		{
			return -1;
		}
		else
		if ($a->class_id() != CL_MENU && $b->class_id() == CL_MENU)
		{
			return 1;
		}
		else
		if ($a->class_id() != CL_MENU && $b->class_id() != CL_MENU)
		{
			return $a->modified() < $b->modified();
		}
		else
		if ($a->class_id() == CL_MENU && $b->class_id() == CL_MENU)
		{
			return $a->modified() < $b->modified();
		}
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

		// this used to give access to subfolders of given folders
		$access_by_parent = array();

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
					"status" => STAT_ACTIVE,
					"lang_id" => array()
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
					if ($access_by_parent[$c_id_o->parent()])
					{
						$add = true;
					}
				}

				if ($add)
				{
					$ret[$c_id] = $c_id;
					$access_by_parent[$c_id] = true;
				}
			}
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
			"type" => $ob->meta('tree_type')
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

		if ($prop["name"] == "columns")
		{
			$arr["obj_inst"]->set_meta("sel_columns", $arr["request"]["column"]);
		}

		if ($prop["name"] == "folders")
		{
			$arr['obj_inst']->set_meta("include_submenus",$arr["request"]["include_submenus"]);
			$arr['obj_inst']->set_meta("ignoreself",$arr["request"]["ignoreself"]);
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
				"url" => $ot->get_add_url(array("id" => $c_o, "parent" => $parent, "section" => $parent)),
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
			"tooltip" => "Uus",
			"url" => "#",
			"onClick" => "return buttonClick(event, 'aw_menu_0');",
			"img" => "new.gif",
			"class" => "menuButton",
		));

		$tb->add_button(array(
			"name" => "del",
			"tooltip" => "Kustuta",
			"url" => "#",
			"onClick" => "document.objlist.subact.value='delete';document.objlist.submit()",
			"img" => "delete.gif",
			"class" => "menuButton",
		));
		return $this->parse().$tb->get_toolbar();
	}

	/**  
		
		@attrib name=delete params=name default="0"
		
		@param id required
		@param return_url required
		
		@returns
		
		
		@comment

	**/
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

	function get_property($arr)
	{
		$prop =&$arr["prop"];
		if ($prop["name"] == "tree_type")
		{
			$prop["options"] = array(
				TREE_HTML => "HTML",
				TREE_JS => "Javascript",
				TREE_DHTML => "DHTML"
			);
		}
		return PROP_OK;
	}

	function _insert_row_styles($o)
	{
		$style = "textmiddle";
		if ($o->prop("line_css"))
		{
			$style = "st".$o->prop("line_css");
			active_page_data::add_site_css_style($o->prop("line_css"));
		}

		$header_css = "textmiddle";
		if ($o->prop("header_css"))
		{
			$header_css = "st".$o->prop("header_css");
			active_page_data::add_site_css_style($o->prop("header_css"));
		}

		$header_bg = "#E0EFEF";
		if ($o->prop("title_bgcolor"))
		{
			$header_bg = "#".$o->prop("title_bgcolor");
		}

		$this->vars(array(
			"css_class" => $style,
			"header_css_class" => $header_css,
			"header_bgcolor" => $header_bg
		));
	}

	function _get_bgcolor($ob, $line)
	{
		$ret = "";
		if (($line % 2) == 1)
		{
			$ret = $ob->prop("odd_bgcolor");
			if ($ret == "")
			{
				$ret = "#EFF7F7";
			}
		}
		else
		{
			$ret = $ob->prop("even_bgcolor");
			if ($ret == "")
			{
				$ret = "#FFFFFF";
			}
		}
		return $ret;
	}

	function callback_get_columns($arr)
	{
		$cols = $arr["obj_inst"]->meta("sel_columns");

		$ret = array();

		foreach($this->all_cols as $colid => $coln)
		{

			$rt = "column[".$colid."]";
			$ret[$rt] = array(
				'name' => $rt,
				'caption' => $coln,
				'type' => 'checkbox',
				'ch_value' => 1,
				'store' => 'no',
				'group' => 'columns',
				'value' => $cols[$colid]
			);
		}
		return $ret;
	}
}
?>
