<?php

/*

@classinfo syslog_type=ST_OBJECT_TREE relationmgr=yes

@groupinfo folders caption=Menüüd
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

*/

define("RELTYPE_FOLDER", 1);

class object_tree extends class_base
{
	function object_tree()
	{
		$this->init(array(
			'tpldir' => 'contentmgmt/object_tree',
			'clid' => CL_OBJECT_TREE
		));
		$this->sub_merge = 1;
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_FOLDER => "kataloog"
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_FOLDER)
		{
			return array(CL_PSEUDO);
		}
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
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
		$ob = $this->get_object($id);

		// make $ob['meta']['folders'] - gather all aliased menus there
		$alias_reltype = new aw_array($ob["meta"]["alias_reltype"]);
		$menu_ids = array_filter($alias_reltype->get(),create_function('$val','if ($val==RELTYPE_FOLDER) return true;'));
		$ob['meta']['folders'] = $this->make_keys(array_keys($menu_ids));

		// now, for all menus that have include_submenus defined, include submenus
		$ar = new aw_array($ob['meta']['include_submenus']);
		foreach($ar->get() as $mid)
		{
			$ob['meta']['folders'] += $this->make_keys(array_keys($this->get_objects_below(array(
				"class" => CL_PSEUDO,
				"parent" => $mid,
				"status" => STAT_ACTIVE,
				"full" => true,
				"ret" => ARR_NAME
			))));
		}

		$this->read_template('show.tpl');

		// get all objects to show
		$ol = $this->_get_objects($ob);

		// make folders
		$this->vars(array(
			"FOLDERS" => $this->_draw_folders($ob, $ol)
		));

		foreach($ol as $oid => $od)
		{
			$this->vars(array(
				"show" => $this->cfg["baseurl"]."/".$oid,
				"name" => $od['name'],
				"type" => $this->cfg["classes"][$od["class_id"]]["name"],
				"add_date" => $this->time2date($od["modified"], 2),
				"icon" => image::make_img_tag(icons::get_icon_url($od["class_id"], $od["name"]))
			));
			$this->parse("FILE");
		}

		return $this->parse();
	}

	function _get_objects($ob)
	{
		$ret = array();

		// right. if the user has said, that no tree should be shown
		// then get files in all selected folders
		if (!$ob['meta']['show_folders'])
		{
			$parent = $ob['meta']['folders'];
		}
		else
		{
			// if the folder is specified in the url, then show that
			// else, the first selected folder
			reset($ob['meta']['folders']);
			list(,$parent) = each($ob['meta']['folders']);

			if ($GLOBALS["tv_sel"])
			{
				$parent = $GLOBALS["tv_sel"];
			}
		}
		return $this->list_objects(array(
			"parent" => $parent,
			"status" => STAT_ACTIVE,
			"class" => $ob['meta']['clids'],
			"return" => ARR_ALL
		));
	}

	function _draw_folders($ob, $ol)
	{
		if (!$ob['meta']['show_folders'])
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
			"type" => ($ob['meta']['js_tree'] ? TREE_JS : TREE_HTML)
		));
		// now, insert all folders defined
		$ar = new aw_array($ob['meta']['folders']);
		foreach($ar->get() as $fld)
		{
			$i_o = $this->get_object($fld);
			$parent = 0;
			if (in_array($i_o["parent"],$ar->get()))
			{
				$parent = $i_o["parent"];
			}

			// find modification time
			$tm = $i_o["modified"];
			foreach($ol as $od)
			{
				if ($od["parent"] == $fld && $od["modified"] > $tm)
				{
					$tm = $od["modified"];
				}
			}

			$tv->add_item($parent, array(
				"id" => $fld,
				"name" => $i_o["name"],
				"url" => aw_url_change_var("tv_sel", $fld),
				"icon" => icons::get_icon_url($i_o["class_id"], ""),
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
				'value' => $arr["obj"]["meta"]["clids"][$clid] == $clid
			);
		}
		return $ret;
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];
		if ($prop['name'] == 'clids')
		{
			$arr['metadata']['clids'] = array();
			classload("aliasmgr");
			$a = aliasmgr::get_clid_picker();
			foreach($a as $clid => $clname)
			{
				$rt = "clid_".$clid;
				if (isset($arr["form_data"][$rt]) && $arr["form_data"][$rt] == 1)
				{
					$arr['metadata']['clids'][$clid] = $clid;
				}
			}
		}
		if ($prop["name"] == "folders")
		{
			$arr['metadata']["include_submenus"] = $arr["form_data"]["include_submenus"];
		};

		return PROP_OK;
	}

	function callback_get_menus($args = array())
	{
		$prop = $args["prop"];
		$nodes = array();
		$include_submenus = $args["obj"]["meta"]["include_submenus"];
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

		$alias_reltype = $args["obj"]["meta"]["alias_reltype"];
		// and from this array, I need to get the list of objects that
		// have the reltype RELTYPE_FOLDER
		if (is_array($alias_reltype))
		{
			$menu_ids = array_filter($alias_reltype,create_function('$val','if ($val==RELTYPE_FOLDER) return true;'));
		};

		if (sizeof($menu_ids) > 0)
		{
			// now get those objects and put them into table
			$q = sprintf("SELECT oid,name,status FROM objects
					LEFT JOIN menu ON (objects.oid = menu.id) WHERE oid IN (%s)",join(",",array_keys($menu_ids)));

			$this->db_query($q);
			while($row = $this->db_next())
			{
				// it shouldn't be too bad, cause get_object is cached.
				// still, it sucks.
				$this->save_handle();
				$chain = array_reverse($this->get_obj_chain(array(
					"oid" => $row["oid"],
				)), true);
				
				$path = join("/",array_slice($chain,-3));
				$this->restore_handle();
				$this->t->define_data(array(
					"oid" => $row["oid"],
					"name" => $path . "/" . $row["name"],
					"check" => html::checkbox(array(
						"name" => "include_submenus[$row[oid]]",
						"value" => $row["oid"],
						"checked" => $include_submenus[$row["oid"]],
					)),
				));
			};
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
