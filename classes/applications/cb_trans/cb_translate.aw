<?php
/*

@classinfo syslog_type=ST_CB_TRANSLATE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@groupinfo translation caption="T&otilde;lkimine"
@default group=translation
	@property translation_tb type=toolbar no_caption=1

	@layout translation_hsplit_1 type=hbox width=15%:85%
		@layout translation_vsplit_left type=vbox closeable=1 area_caption=Klassihierarhia parent=translation_hsplit_1
			@property classtree type=treeview parent=translation_vsplit_left no_caption=1

		@layout translation_vsplit_right type=vbox closeable=1 area_caption=Tolgitavad_asjad parent=translation_hsplit_1
			@property tmp1 type=text parent=translation_vsplit_right
			@caption fafa1
	
*/

define("FLD", 1);
define("CLS", 2);
define("GRP", 3);
define("PRP", 4);
define("REL", 5);
define("TXT", 6);
define("LYT", 7);

class cb_translate extends class_base
{
	function cb_translate()
	{
		$this->init(array(
			"tpldir" => "applications/cb_translate",
			"clid" => CL_CB_TRANSLATE,
		));
		aw_global_set("output_charset", "utf-8");
	}
	
	function init_cfgu($clid = false)
	{
		if(!$this->cfgu)
		{
			$this->cfgu = get_instance("cfg/cfgutils");
		}
		if($clid)
		{
			return $this->cfgu->load_properties(array(
					"clid" => $clid,
			));
		}
	}

// get props
	function _get_translation_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "fafa",
			"caption" => "fafa",
			"img" => "save.gif",
		));
	}

	function _get_classtree($arr)
	{
		$clid = $arr["request"]["clid"]?$arr["request"]["clid"]:"fld_0";
		$arr["prop"]["vcl_inst"]->start_tree (array (
			"type" => TREE_DHTML,
			//"open_path" => array("fld_10","fld_37","fld_59"),
			//"open_path" => array("fld_1"),
			//"open_path" => array("fld_10","fld_37","fld_59","867"),
			"root_name" => iconv("iso-8859-1", "utf-8", t("AW KLASSIDE T&Otilde;LKIMINE")),
			"url_target" => "editorcontent",
			"get_branch_func" => $this->mk_my_orb("get_node",array("clid" => $clid, "parent" => " ")),
			"has_root" => 1,
			"baseurl" => aw_ini_get("baseurl"),
			"ext" => aw_ini_get("ext"),
		));
		$this->_gen_tree($arr["prop"]["vcl_inst"], $clid);
	}

// set props

	/**
		@attrib name=editor
		@param clid required
		@param group optional
	**/
	function editor($arr)
	{
		aw_global_set("output_charset", "utf-8");
		// now, I have got clid .. how to I generate the bloody interface?
		$this->read_template("editor.tpl");
		$clid = $arr["clid"];

		$cfgu = get_instance("cfg/cfgutils");

// toolbar
		$tb = get_instance("vcl/toolbar");

		$tb->add_button(array(
			"name" => "save",
			"caption" => t("Salvesta"),
			"img" => "save.gif",
			"target" => "editorcontent",
			"action" => "",
		));

		$tb->add_button(array(
			"name" => "search",
			"caption" => t("Otsi"),
			"img" => "search.gif",
			"target" => "editorcontent",
			"action" => "search_trans",
		));

/*

		Actually the idea for this next thingie was that you can enter a aw url to the so called address bar and translate application finds out which translation strings are used during loading this page. then those strings are just shown to the user and he can translate these. makes it easier for dumbuser.

		The idea was that aw_url method gets the url, does pageload to that url with certain parameter. aw recognizes this parameter and collects all translate strings used. then, instead of the rendered page, aw returns those strings with vital information where they are etc.. and aw_url method does the rest.

		So, if anybody wishes, he can implement that, i just don't have time for it right now.. i'll better try to implement a search functionality.

		taiu


		$tb->add_separator();
		
		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		$cdata = html::textbox(array(
			"name" => "trans_url",
			"size" => "100%",
		));
		$cdata = t("AW://").$cdata;
		$htmlc->add_property(array(
			"name" => "trans_url",
			"no_caption" => 1,
			"type" => "text",
			"value" => $cdata,
			"post_append_text" => html::href(array(
				"url" => "javascript:submit_changeform(\"aw_url\");",
				"caption" => t("GO"),
			)),
		));
		$htmlc->finish_output(array(
			"action" => "aw_url",
			"data" => array(
				"class" => "cb_translate",
			),
			"submit" => "no",
		));
		$cdata = $htmlc->get_result(array(
			"form_only" => 1,
		));
		$tb->add_cdata($cdata);
*/

/// toolbar

		$props = $cfgu->load_properties(array(
				"clid" => $arr["clid"],
		));
		$clinf = aw_ini_get("classes");
		$clfinf = aw_ini_get("classfolders");

		$classdat = $clinf[$arr["clid"]];
		$atc = get_instance("admin/add_tree_conf");
		$tree = $atc->get_class_tree();

		$groups = $cfgu->get_groupinfo();

// checks what's current tree root
		$parent = trim($arr["clid"]);
		$parent_is_folder = false;
		if ("fld_" == substr($parent,0,4))
		{
			$arr["clid"] = substr($parent,4);
			$parent_is_folder = true;
		}

// get path from selected to root
		if(!$parent_is_folder)
		{
			$current = $clinf[$arr["clid"]]["parents"];
			$link[] = "<a style=\"color:white;\" href=\"orb.aw?class=cb_translate&action=editor&clid=".$arr["clid"]."\">".iconv("iso-8859-1", "utf-8", $clinf[$arr["clid"]]["name"].".aw")."</a>";
		}
		else
		{
			$current = $arr["clid"];
		}
		while(true)
		{
			//$path[] = $cur_point;
			$link[] = "<a style=\"color:white;\" href=\"orb.aw?class=cb_translate&action=editor&clid=fld_".$current."\">".iconv("iso-8859-1", "utf-8", $clfinf[$current]["name"])."</a>";
			//calc parent
			$current = $clfinf[$current]["parent"];
			if(!$current)
			{
				break;
			}
		}
		$link[] = "<a style=\"color:white;\" href=\"orb.aw?class=cb_translate&action=editor&clid=fld_0\">".iconv("iso-8859-1", "utf-8", "root")."</a>";

		foreach(array_reverse($link) as $el)
		{
			$path_string .= " / ".$el;
		}
		$class_tree = $tree;

// start tree
		$tree = get_instance("vcl/treeview");
		$tree->start_tree (array (
			"type" => TREE_DHTML,
			//"open_path" => array("fld_10","fld_37","fld_59"),
			//"open_path" => array("fld_1"),
			//"open_path" => array("fld_10","fld_37","fld_59","867"),
			"root_name" => iconv("iso-8859-1", "utf-8", t("AW KLASSIDE T&Otilde;LKIMINE")),
			"url_target" => "editorcontent",
			"get_branch_func" => $this->mk_my_orb("get_node",array("clid" => $arr["clid"], "parent" => " ")),
			"has_root" => 1,
			"root_id" => 0,
			"baseurl" => aw_ini_get("baseurl"),
			"ext" => aw_ini_get("ext"),
		));
		$this->_gen_tree($tree, $arr["clid"]);

		/*
		classload("core/icons");

		if($arr["clid"] && is_numeric($arr["clid"]) && !$parent_is_folder)
		{
			$tree->add_item(0,array(
				"name" => iconv(aw_global_get("charset"), "utf-8", $classdat["name"]),
				"id" => "root",
				"url" => $this->mk_my_orb("classeditor", array("clid" => $arr["clid"])),
				"is_open" => 1,
				"iconurl" => icons::get_icon_url($arr["clid"]),
				"url_target" => "editorcontent",
			));

			// get & display groups
			$target_class = obj();
			$target_class->set_class_id($arr["clid"]);
			$target_groups = $target_class->get_group_list();

			foreach($target_groups as $group_key => $group_data)
			{
				$parent = isset($group_data["parent"]) ? "grp_".$group_data["parent"] : "root";
				$tree->add_item($parent ,array(
					"name" => iconv(aw_global_get("charset"), "utf-8", $group_data["caption"]),
					"id" => "grp_".$group_key,
					"url" => $this->mk_my_orb("groupedit",array(
						"clid" => $arr["clid"],
						"grpid" => $group_key,
					)),
					"is_open" => 1,
					"iconurl" => "images/icons/help_topic.gif",
				));
			}

			// 
			$layouts = $cfgu->get_layoutinfo();
			if(count($layouts) && is_array($layouts))
			{
				$tree->add_item("root", array(
					"name" => t("Kujundusosad"),
					"id" => "layout_0",
					"is_open" => true,
					"url" => "javascript:void();",
				));

				foreach($layouts as $lkey => $ldata)
				{
					if($ldata["closeable"])
					{
						$tree->add_item("layout_0",array(
							"name" => iconv(aw_global_get("charset"), "utf-8", $ldata["area_caption"]?$ldata["area_caption"]:t("Nimetu")),
							"id" => "lyt_".$lkey,
							"url" => $this->mk_my_orb("layouttrans",array(
								"clid" => trim($arr["clid"]),
								"lid" => $lkey,
							)),
							"is_open" => 1,
							"iconurl" => "images/icons/help_topic.gif",
						));
					}
				}
			}

			// properties
			foreach($props as $pkey => $pdata)
			{
				if($pdata["type"] == "hidden")
				{
					continue;
				}
				if(!is_array($pdata["group"]))
				{
					$pdata["group"] = array($pdata["group"]);
				}
				foreach($pdata["group"] as $parent_group)
				{
					$caption = strlen($pdata["caption"])? $pdata["caption"] : $pdata["name"];
					$tree->add_item("grp_".$parent_group ,array(
						"name" => iconv(aw_global_get("charset"), "utf-8", $caption),
						"id" => $parent."_".$pkey,
						"url" => $this->mk_my_orb("proptrans",array(
							"clid" => trim($arr["clid"]),
							"grpid" => $parent_group,
							"propid" => $pkey,
						)),
						//"is_open" => 1,
						"iconurl" => icons::get_icon_url(86),
					));
				}
			}

			// reltypes
			$rels = $cfgu->get_relinfo();
			if(count($rels))
			{
				$tree->add_item("root", array(
					"id" => "rel_root",
					"name" => t("Seosed"),
					"iconurl" => "images/icons/connectionmanager.gif",
					"is_open" => true,
					"url" => "javascript:void();",
				));
				foreach($rels as $key => $rel)
				{
					if(substr($key,0,8) == "RELTYPE_")
					{
						$tree->add_item("rel_root", array(
							"name" => iconv(aw_global_get("charset"), "utf-8", html_entity_decode($rel["caption"])),
							"id" => $key,
							"url" => $this->mk_my_orb("releditor",array(
								"clid" => trim($arr["clid"]),
								"reltype" => $key,
							)),
							"iconurl" => "images/icons/connectionmanager.gif",
						));
					}
				}
			}

			// texts from code -> t() func
			// must be read from po? :S.. how the fuck can i understand which are from code which not
			$tree->add_item(0 , array(
				"name" => "varia",
				"id" => $parent."_".PI,
				"iconurl" => "images/icons/rte_align_center.gif",
				"url" => $this->mk_my_orb("lineeditor",array(
					"clid" => trim($arr["clid"]),
				)),
			));
		}

		if(strlen($arr["clid"]) && is_numeric($arr["clid"]) && $parent_is_folder)
		{
			// the class_tree that has been generated by admin_menu does not contain enough information
			// for me
			$tcnt = 0;

			foreach($class_tree as $item_id => $item_collection)
			{
				if($arr["clid"] == substr($item_id,4))
				{
					//arr($item_collection);
					foreach($item_collection as $el_id => $el_data)
					{
						$parnt = is_numeric($item_id) && $item_id == 0 ? "root" : $item_id;
						$tcnt++;

						$tree->add_item(0,array(
							"name" => iconv(aw_global_get("charset"), "utf-8", $el_data["name"]),
							"id" => $el_data["id"],
							"url" => $this->mk_my_orb("classeditor", array("clid" => $el_data["id"])),
							"is_open" => 0,
							"iconurl" => empty($el_data["clid"]) ? "" : icons::get_icon_url($el_data["clid"]),
						));

						$tree->add_item($el_data["id"],array(
							"name" => "fafa",
							"id" => $el_data["id"] + 10000,
						));
					}
				}
			}
		}
		*/
		$editor_orb_name = strlen($arr["group"])?"groupedit":"classeditor";

		$this->vars(array(
			"commit_link" => html::href(array(
				"url" => $this->mk_my_orb("commit_changes"),
				"target" => "editorcontent",
				"class" => "right_caption",
				"caption" => iconv(aw_global_get("charset") , "utf-8", t("CVS commit")),
			)),
			"apply_link" => html::href(array(
				"url" => $this->mk_my_orb("show_changes"),
				"target" => "editorcontent",
				"class" => "right_caption",
				"caption" => iconv(aw_global_get("charset") , "utf-8", t("N&auml;ita muutusi")),
			)),
			"editor_caption" => $path_string,
			"editor_content_tree" => $tree->finalize_tree(),
			"browser_caption" => t("AW t&otilde;lkimine"),
			"toolbar" => $tb->get_toolbar(),
			"editor_content" => $this->mk_my_orb($editor_orb_name ,array(
				"clid" => trim($arr["clid"]),
				"grpid" => $arr["group"],
			)),
		));
		return $this->parse();

	}

	function _gen_tree(&$tree, $start_id)
	{
		// checks what's current tree root
		$parent = trim($start_id);
		$parent_is_folder = false;
		if ("fld_" == substr($parent,0,4))
		{
			$start_id = substr($parent,4);
			$parent_is_folder = true;
		}
		
		// load cls & clsfld info
		$cls = aw_ini_get("classes");
		$clsfld = aw_ini_get("classfolders");
		// cfgutils for loading props/layouts etc
		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_properties(array(
				"clid" => $start_id,
		));

		classload("core/icons");
		if($start_id && is_numeric($start_id) && !$parent_is_folder)
		{
			$tree->add_item(0,array(
				"name" => iconv(aw_global_get("charset"), "utf-8", $cls[$start_id]["name"]),
				"id" => "root",
				"url" => $this->mk_my_orb("classeditor", array("clid" => $start_id)),
				"is_open" => 1,
				"iconurl" => icons::get_icon_url($start_id),
				"url_target" => "editorcontent",
			));

			// sets data_in_place feature
			$tree->set_feature(3);

			//groups
			$this->add_tree_group($tree, $start_id, "root");

			//layouts 
			$this->add_tree_layouts($tree, $start_id, "root");

			//properties
			$this->add_tree_property($tree, $start_id);

			//reltypes
			$this->add_tree_relations($tree, $start_id, "root");

			// texts from code -> t() func
			// must be read from po? :S.. how the fuck can i understand which are from code which not
			$tree->add_item(0 , array(
				"name" => "varia",
				"id" => $parent."_".PI,
				"iconurl" => "images/icons/rte_align_center.gif",
				"url" => $this->mk_my_orb("lineeditor",array(
					"clid" => trim($start_id),
				)),
			));
		}

		if(strlen($start_id) && is_numeric($start_id) && $parent_is_folder)
		{
			// the class_tree that has been generated by admin_menu does not contain enough information
			// for me
			$tcnt = 0;
			$atc = get_instance("admin/add_tree_conf");
			$class_tree = $atc->get_class_tree();

			foreach($class_tree as $item_id => $item_collection)
			{
				if($start_id == substr($item_id,4))
				{
					foreach($item_collection as $el_id => $el_data)
					{
						$parnt = is_numeric($item_id) && $item_id == 0 ? "root" : $item_id;
						$tcnt++;

						$tree->add_item(0,array(
							"name" => iconv(aw_global_get("charset"), "utf-8", $el_data["name"]),
							"id" => $el_data["id"],
							"url" => $this->mk_my_orb("classeditor", array("clid" => $el_data["id"])),
							"is_open" => 0,
							"iconurl" => empty($el_data["clid"]) ? "" : icons::get_icon_url($el_data["clid"]),
						));

						$tree->add_item($el_data["id"],array(
							"name" => "fafa",
							"id" => $el_data["id"] + 10000,
						));
					}
				}
			}
		}

	}


	/**
		@attrib name=get_node all_args=1

	**/
	function get_node($arr)
	{
		aw_global_set("output_charset", "UTF-8");
		$this->read_template("editor.tpl");

		$cfgu = get_instance("cfg/cfgutils");
		/*
		if (!$cfgu->has_properties(array("clid" => $arr["clid"])))
		{
			die(t("Selle klassil puudub abiinfo"));
		};
		*/

		$atc_inst = get_instance("admin/add_tree_conf");
		$atc_id = $atc_inst->get_current_conf();


		$clinf = aw_ini_get("classes");
		$classdat = $clinf[$arr["clid"]];


		$atc = get_instance("admin/add_tree_conf");
		$tree = $atc->get_class_tree();


		$class_tree = $tree;

		$tree = get_instance("vcl/treeview");
		$tree->start_tree (array (
			"type" => TREE_DHTML,
			"url_target" => "editorcontent",
		));

		classload("core/icons");

		// the class_tree that has been generated by admin_menu does not contain enough information
		// for me
		$tcnt = 0;

		$parent = trim($arr["parent"]);
		$parent_folder = 0;
		if ("fld_" == substr($parent,0,4))
		{
			$parent_folder = substr($parent,4);
			$prnt = FLD;
		}
		// veider .. parent ple int, kuigi tegelt nagu peaks olema :SS
		if(is_numeric($arr["parent"]))
		{
			$prnt = CLS;
		}
		if(substr($parent, 0, 4) == "grp_")
		{
			$parent = substr($parent, 4);
			$prnt = GRP;
		}
		if($prnt == FLD)
		{
			foreach($class_tree as $item_id => $item_collection)
			{
				if (isset($parent) && $item_id != $parent)
				{
					continue;
				};

				foreach($item_collection as $el_id => $el_data)
				{

					$parnt = is_numeric($item_id) && $item_id == 0 ? "root" : $item_id;
					$tcnt++;
					$tree->add_item(0,array(
						"name" => iconv(aw_global_get("charset"), "utf-8",$el_data["name"]),
						"id" => $el_data["id"],
						"url" => $this->mk_my_orb("classeditor",array(
							"clid" => $el_data["id"],
						)),
						"is_open" => 0,
						"iconurl" => empty($el_data["clid"]) ? "" : icons::get_icon_url($el_data["clid"]),
					));

					$tree->add_item($el_data["id"],array(
						"name" => "nameless",
						"id" => $el_data["id"] + 10000,
					));

				};
			};
		}
		// also, reading information about all groups of all classes is _really_ slow.
		if ($prnt == CLS)
		{
			// sets data_in_place feature
			$tree->set_feature(3);

			//groups
			$this->add_tree_group($tree, $parent);

			//layouts
			$this->add_tree_layouts($tree, $parent);
			
			//properties
			$this->add_tree_property($tree, $parent);
			
			//reltypes
			$this->add_tree_relations($tree, $parent);

			// texts from code -> t() func
			// must be read from po? :S.. how the fuck can i understand which are from code which not

			$tree->add_item(0 , array(
				"name" => "varia",
				"id" => $parent."_".PI,
				"iconurl" => "images/icons/rte_align_center.gif",
				"url" => $this->mk_my_orb("lineeditor",array(
					"clid" => trim($arr["parent"]),
				)),
			));
		}

		return ($tree->finalize_tree());

		$this->vars(array(
			// do not use the thing passed in from the URL
			"help_caption" => sprintf(t("Klassi '%s' abiinfo"),$classdat["name"]),
			"help_content_tree" => $tree->finalize_tree(),
			"retrieve_help_func" => $this->mk_my_orb("groupedit",array(),"help"),
			"browser_caption" => t("AW abiinfo"),
		));
		die($this->parse());
		print "<pre>";
		print_r($arr);
		print "</pre>";

	}

	// add treenodes

	function add_tree_group(&$tree, $clid, $root = false)
	{
		$this->init_cfgu($clid);
		$groups = $this->cfgu->get_groupinfo();
		foreach($groups as $gkey => $gdata)
		{
			$node_parent = isset($gdata["parent"]) ? $clid."_grp_".$gdata["parent"] : ($root === false?0:$root);
			$tree->add_item($node_parent,array(
				"name" => iconv(aw_global_get("charset"), "utf-8", $gdata["caption"]),
				"id" => $clid."_grp_".$gkey,
				"url" => $this->mk_my_orb("groupedit",array(
					"clid" => trim($clid),
					"grpid" => $gkey,
				)),
				//"is_open" => 1,
				"iconurl" => "images/icons/help_topic.gif",
			));

		}
	}

	function add_tree_property(&$tree, $clid)
	{
		$props = $this->init_cfgu($clid);
		foreach($props as $pkey => $pdata)
		{
			if($pdata["type"] == "hidden")
			{
				continue;
			}
			if(!is_array($pdata["group"]))
			{
				$pdata["group"] = array($pdata["group"]);
			}
			foreach($pdata["group"] as $parent_group)
			{
				$caption = strlen($pdata["caption"])? $pdata["caption"] : $pdata["name"];
				$tree->add_item($clid."_grp_".$parent_group ,array(
					"name" => iconv(aw_global_get("charset"), "utf-8", $caption),
					"id" => $parent."_".$pkey,
					"url" => $this->mk_my_orb("proptrans",array(
						"clid" => trim($clid),
						"grpid" => $parent_group,
						"propid" => $pkey,
					)),
					//"is_open" => 1,
					"iconurl" => icons::get_icon_url(86),
				));
			}
		}
	}

	function add_tree_relations(&$tree, $clid, $root = false)
	{
		$props = $this->init_cfgu($clid);
		$rels = $this->cfgu->get_relinfo();
		if(count($rels))
		{
			$tree->add_item(($root === false?0:$root), array(
				"id" => "rel_root",
				"name" => t("Seosed"),
				"iconurl" => "images/icons/connectionmanager.gif",
				//"is_open" => true,
				"url" => "javascript:void();",
			));
			foreach($rels as $key => $rel)
			{
				if(substr($key,0,8) == "RELTYPE_")
				{
					$tree->add_item("rel_root", array(
						"name" => iconv(aw_global_get("charset"), "utf-8", html_entity_decode($rel["caption"])),
						"id" => $key,
						"url" => $this->mk_my_orb("releditor",array(
							"clid" => trim($clid),
							"reltype" => $key,
						)),
						"iconurl" => "images/icons/connectionmanager.gif",
					));
				}
			}
		}
	}

	function add_tree_layouts(&$tree, $clid, $root = false)
	{
		$props = $this->init_cfgu($clid);
		$layouts = $this->cfgu->get_layoutinfo();
		if(count($layouts) && is_array($layouts))
		{
			$tree->add_item(($root === false?0:$root), array(
				"name" => t("Kujundusosad"),
				"id" => "layout_0",
				//"is_open" => true,
				"url" => "javascript:void();",
			));

			foreach($layouts as $lkey => $ldata)
			{
				if($ldata["closeable"])
				{
					$tree->add_item("layout_0",array(
						"name" => iconv(aw_global_get("charset"), "utf-8", $ldata["area_caption"]?$ldata["area_caption"]:t("Nimetu")),
						"id" => "lyt_".$lkey,
						"url" => $this->mk_my_orb("layouttrans",array(
							"clid" => trim($clid),
							"lid" => $lkey,
						)),
						//"is_open" => 1,
						"iconurl" => "images/icons/help_topic.gif",
					));
				}
			}
		}

	}


// trans methods

	/** manages single class or classfolder editing
		@attrib name=lineeditor
		@param clid required
		@param saved_langs optional
	**/
	function lineeditor($arr)
	{
		$charset_from_local = "iso-8859-1";
		aw_global_set("output_charset", "UTF-8");
		
		/*
		$this->read_template("linetrans.tpl");
		$this->sub_merge = 1;
		*/		

		$pot_scanner = get_instance("core/trans/pot_scanner");
		$languages = $pot_scanner->get_langs();

		$cls = aw_ini_get("classes");
		$aw_location = $cls[trim($arr["clid"])]["file"];
		$po = split("[/]",$aw_location);
		$po_file = $po[count($po)-1];
		$langs_info = aw_ini_get("languages.list");
		foreach($langs_info as $lang)
		{
			$mod_lang[$lang["acceptlang"]] = $lang;
		}
		
		$this->cb_htmlc = get_instance("cfg/htmlclient");
		$this->cb_htmlc->start_output(array(
			"template" => "default",
		));

		$title = sprintf("Klass '%s' tekstid.", $cls[$arr["clid"]]["name"]);
		$this->cb_htmlc->add_property(array(
			"name" => "general_title",
			"type" => "text",
			"no_caption" => 1,
			"value" => $title,
			"textsize" => "17px;",
		));
	

		foreach($languages as $key => $language)
		{
			$file_location = aw_ini_get("basedir")."/lang/trans/".$language."/po/".$po_file.".po";
			if(!$this->check_langfile($language, $po_file.".po"))
			{
				$no_writable_langs[] = $mod_lang[$language]["name"]."(".$language.")";
				$no_writable_short[] = $language;
				continue;
			}
	
			$lang = $mod_lang[$language];
			$class_po_file = $pot_scanner->parse_po_file($file_location);
			foreach($class_po_file as $entry)
			{
				$header = $entry["headers"][0];
				if(is_numeric(trim(end(split(":", trim(substr($header,strpos($header, " "))))))))
				{
					$data_to_be_shown[$entry["msgid"]][$language]["caption"] = iconv($lang["charset"], "utf-8", $entry["msgstr"]);
					$data_to_be_shown[$entry["msgid"]][$language]["lang_name"] = iconv($lang["charset"], "utf-8", $lang["name"]);
				}

			}
		}
		foreach($data_to_be_shown as $text => $data)
		{
			/*
			$this->vars(array(
				"text" => iconv($charset_from_local, "utf-8", $text),
			));
			$tmp .= $this->parse("SUB_TRANSLATE");
			*/

			$this->cb_htmlc->add_property(array(
				"name" => "txt_".++$i,
				"type" => "text",
				"caption" => t("Tekst"),
				"value" => iconv($charset_from_local, "utf-8", $text),
			));
			foreach($data as $lang => $inf)
			{
				$this->cb_htmlc->add_property(array(
					"name" => "vars[".$lang."][".iconv($charset_from_local, "utf-8", $text)."]",
					"type" => "textbox",
					"caption" => iconv($charset_from_local, "utf-8", $inf["lang_name"]),
					"value" => $inf["caption"],
					"size" => 80,
				));
				/*
				$this->vars(array(
						"lang_short" => iconv($charset_from_local, "utf-8", $lang),
						"lang_name" => $inf["lang_name"],
						"lang_caption" => $inf["caption"],
				));
				$tmp .= $this->parse("LANG_TRANSLATE");
				*/
			}
			// shows languages which don't have an entry, but hide languges that haven't write access
			foreach(array_diff(array_diff($no_writable_short, $languages), array_keys($data)) as $l)
			{
				foreach($langs_info as $lang_tmp)
				{
					if($lang_tmp["acceptlang"] == $l)
					{
						break;
					}
				}
				
				$this->cb_htmlc->add_property(array(
					"name" => "vars[".$l."][".$text."]",
					"type" => "textbox",
					"caption" => iconv($charset_from_local, "utf-8", $lang_tmp["name"]),
					"size" => 80,
				));
				/*
				$this->vars(array(
						"lang_short" => iconv($charset_from_local, "utf-8", $l),
						"lang_name" => $lang_tmp["name"],
						"lang_caption" => "",
				));
				$tmp .= $this->parse("LANG_TRANSLATE");
				*/
			}
			$this->cb_htmlc->add_property(array(
				"name" => "sp_".$i,
				"no_caption" => 1,
				"value" => "&nbsp;",
			));

		}

		if(count($no_writable_langs))
		{
			$this->gen_error(sprintf("J&auml;rgmiste keelte t&otilde;lkefailidel ei ole kirjutus&otilde;igust. Palun kontrollige &uuml;le: %s", join(", ", $no_writable_langs)));
		}
		$this->cb_htmlc->finish_output(array(
			"action" => "submit_editor",
			"method" => "POST",
			"data" => array(
				"class" => "cb_translate",
				"clid" => trim($arr["clid"]),
				"text" => 1,
			),
		));
		
		$save_msg = array();
		if(strlen($arr["saved_langs"]))
		{
			foreach(split(",", $arr["saved_langs"]) as $lng)
			{
				$save_msg[] = $mod_lang[$lng]["name"]."(".$lng.")";
			}
		}
		return $this->cb_htmlc->get_result(array(
			"save_message" => count($save_msg)?sprintf("Salvestati j&auml;rgmiste keelte tekstid: %s",join(", ", $save_msg)):null,
		));
	}

	/** manages single class or classfolder editing
		@attrib name=releditor
		@param reltype required
		@param clid required
		@param saved_langs optional
	**/
	function releditor($arr)
	{
		$charset_from_local = "iso-8859-1";
		aw_global_set("output_charset", "UTF-8");

		/*
		$this->read_template("reltrans.tpl");
		$this->sub_merge = 1;
		*/

		$cu = get_instance("cfg/cfgutils");
		$cu->load_properties(array(
			"clid" => $arr["clid"],
			"load_trans" => 0,
		));
		$rels = $cu->get_relinfo(array("clid" => $arr["clid"]));

		$pot_scanner = get_instance("core/trans/pot_scanner");
		$languages = $pot_scanner->get_langs();

		$cls = aw_ini_get("classes");
		$aw_location = $cls[trim($arr["clid"])]["file"];
		$po = split("[/]",$aw_location);
		$po_file = $po[count($po)-1];
		$langs_info = aw_ini_get("languages.list");

		foreach($langs_info as $lang_info)
		{
			$mod_lang[$lang_info["acceptlang"]] = $lang_info;
		}

		$this->cb_htmlc = get_instance("cfg/htmlclient");
		$this->cb_htmlc->start_output(array(
			"template" => "default",
		));

		// gen title
		$title = sprintf("Klassi '%s' seos '%s' ('%s' t&uuml;&uuml;pi)", iconv($charset_from_local, "UTF-8", $cls[$arr["clid"]]["name"]), "<b>".iconv($charset_from_local, "UTF-8", $rels[$arr["reltype"]]["caption"])."</b>", $arr["reltype"]);

		$this->cb_htmlc->add_property(array(
			"name" => "general_title",
			"type" => "text",
			"no_caption" => 1,
			"value" => $title,
			"textsize" => "17px;",
		));

		foreach($languages as $key => $language)
		{
			unset($caption);
			$file_location = aw_ini_get("basedir")."/lang/trans/".$language."/po/".$po_file.".po";
			if(!$this->check_langfile($language, $po_file.".po"))
			{
				$no_writable_langs[] = $mod_lang[$language]["name"]."(".$language.")";
				continue;
			}
			$class_po_file = $pot_scanner->parse_po_file($file_location);
			$lang = $mod_lang[$language];
			$charset_from = $lang["charset"];
			foreach($class_po_file as $po)
			{
				//$comp = "Seose ".iconv($charset_from_local, $lang["charset"], $rels[$arr["reltype"]]["caption"])." (".$arr["reltype"].") tekst";
				$comp = "Seose ".htmlentities($rels[$arr["reltype"]]["caption"], $charset["from"])." (".$arr["reltype"].") tekst";
				if($po["msgid"] == $comp)
				{
					$caption = $po["msgstr"];
				}
			}

			$this->cb_htmlc->add_property(array(
				"name" => "langname_".$language,
				"type" => "text",
				"caption" => t("Keel"),
				"value" => iconv($charset_from_local, "utf-8", $lang["name"]),
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][caption]",
				"type" => "textbox",
				"caption" => t("Seose nimi"),
				"size" => 50,
				"value" => iconv($charset_from, "utf-8", $caption),
			));
			$this->cb_htmlc->add_property(array(
				"name" => "sp_".$language,
				"no_caption" => 1,
				"value" => "&nbsp;",
			));
			/*
			$this->vars(array(
					"lang_short" => iconv($charset_from_local, "utf-8", $language),
					"lang_name" => iconv($charset_from_local, "utf-8", $lang["name"]),
					"lang_caption" => iconv($charset_from, "utf-8", $caption),
			));
			$this->parse("SUB_TRANSLATE");
			*/
		}

		if(count($no_writable_langs))
		{
			$this->gen_error(sprintf("J&auml;rgmiste keelte t&otilde;lkefailidel ei ole kirjutus&otilde;igust. Palun kontrollige &uuml;le: %s", join(", ", $no_writable_langs)));
		}
		$this->cb_htmlc->finish_output(array(
			"action" => "submit_editor",
			"method" => "POST",
			"data" => array(
				"class" => "cb_translate",
				"clid" => trim($arr["clid"]),
				"reltype" => $arr["reltype"],
				"caption" => iconv($charset_from_local, "UTF-8", $rels[$arr["reltype"]]["caption"]),
			),
		));
		
		$save_msg = array();
		if(strlen($arr["saved_langs"]))
		{
			foreach(split(",", $arr["saved_langs"]) as $lng)
			{
				$save_msg[] = $mod_lang[$lng]["name"]."(".$lng.")";
			}
		}
		return $this->cb_htmlc->get_result(array(
			"save_message" => count($save_msg)?sprintf("Salvestati j&auml;rgmiste keelte tekstid: %s",join(", ", $save_msg)):null,
		));
/*
		if(count($no_writable_langs))
		{
			$this->vars(array(
				"caption" => join(", ", $no_writable_langs),
			));
			$this->parse("FILE_ACCESS_ERROR");
		}

		$title = t("Klassi")." '".$cls[$arr["clid"]]["name"]."' ".t("seos")." '".$rels[$arr["reltype"]]["caption"]."'";
		$title .= " ('".$arr["reltype"]."' ".t("t&uuml;&uuml;pi").")";
		$this->vars(array(
			"title" => iconv($charset_from_local, "utf-8", $title),
			"caption" => $rels[$arr["reltype"]]["caption"],
			"reforb" => $this->mk_reforb("submit_editor",array(
				"clid" => $arr["clid"],
				"reltype" => $arr["reltype"],
			)),
		));
		//return die($this->parse());
		return $this->parse();
*/
	}

	/** manages single class or classfolder editing
		@attrib name=classeditor
		@param clid required
		@param saved_langs optional
	**/
	function classeditor($arr)
	{
		$charset_from_local = "iso-8859-1";
		aw_global_set("output_charset", "UTF-8");
		$obj_is_folder = false;
		if(substr($arr["clid"],0,4) == "fld_")
		{
			$obj_is_folder = true;
			$arr["clid"] = substr($arr["clid"],4);
		}
		/*
		$this->read_template("proptrans.tpl");
		$this->sub_merge = 1;
		*/
		$pot_scanner = get_instance("core/trans/pot_scanner");
		$languages = $pot_scanner->get_langs();

		if(!$obj_is_folder)
		{
			$cls = aw_ini_get("classes"); //$cls["classes"];
		}
		else
		{
			
			$clsfld = aw_ini_get("classfolders"); //$clsfld["classfolders"];
		}
		$langs_info = aw_ini_get("languages.list");
		foreach($langs_info as $lang_info)
		{
			$mod_lang[$lang_info["acceptlang"]] = $lang_info;
		}
				
		$this->cb_htmlc = get_instance("cfg/htmlclient");
		$this->cb_htmlc->start_output(array(
			"template" => "default",
		));

		// gen title

		// gen title
		$cls = aw_ini_get("classes");
		$title = sprintf("%s '%s' nimi", $obj_is_folder?t("Kausta"):t("Klassi"), "<b>".($obj_is_folder?$clsfld[$arr["clid"]]["name"]:$cls[$arr["clid"]]["name"])."</b>");
		$this->cb_htmlc->add_property(array(
			"name" => "general_title",
			"type" => "text",
			"no_caption" => 1,
			"value" => $title,
			"textsize" => "17px;",
		));
	
		foreach($languages as $key => $language)
		{
			$charset_from = $mod_lang[$language]["charset"];
			unset($caption,$comment,$help);
			$ini_po_loc = aw_ini_get("basedir")."/lang/trans/".$language."/po/aw.ini.po";
			if(!$this->check_langfile($language, "aw.ini.po"))
			{
				$no_writable_langs[] = $mod_lang[$language]["name"]."(".$language.")";
				continue;
			}
			$ini_po_file = $pot_scanner->parse_po_file($ini_po_loc);
			foreach($ini_po_file as $po)
			{
				$cls_start = "Klassi ".$cls[$arr["clid"]]["name"]." (".$arr["clid"].") ";
				// class
				if($cls_start."nimi" == $po["msgid"] && $obj_is_folder == false)
				{
					$caption = $po["msgstr"];
				}
				if($cls_start."comment" == $po["msgid"] && $obj_is_folder == false)
				{
					$comment = $po["msgstr"];
				}
				if($cls_start."help" == $po["msgid"] && $obj_is_folder == false)
				{
					$help = $po["msgstr"];
				}

				$fld_start = "Klassi kataloogi ".$clsfld[$arr["clid"]]["name"]." (".$arr["clid"].") ";
				// dir
				if($fld_start."nimi" == $po["msgid"] && $obj_is_folder == true)
				{
					$caption = $po["msgstr"];
				}
				if($fld_start."comment" == $po["msgid"] && $obj_is_folder == true)
				{
					$comment = $po["msgstr"];
				}
				if($fld_start."help" == $po["msgid"] && $obj_is_folder == true)
				{
					$help = $po["msgstr"];
				}
			}

			foreach($langs_info as $lang)
			{
				if($lang["acceptlang"] == $language)
				{
					break;
				}
			}

			$this->cb_htmlc->add_property(array(
				"name" => "langname_".$language,
				"type" => "text",
				"caption" => t("Keel"),
				"value" => iconv($charset_from_local, "utf-8", $mod_lang[$language]["name"]),
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][caption]",
				"type" => "textbox",
				"caption" => t("Nimi"),
				"value" => iconv($charset_from, "utf-8", $caption),
				"size" => 50,
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][comment]",
				"type" => "textarea",
				"caption" => t("Kommentaar"),
				"value" => iconv($charset_from, "utf-8", $comment),
				"cols" => 50,
				"rows" => 5,
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][help]",
				"type" => "textarea",
				"caption" => t("Abitekst"),
				"value" => iconv($charset_from, "utf-8", $help),
				"cols" => 50,
				"rows" => 10,
			));
			$this->cb_htmlc->add_property(array(
				"name" => "sp_".$language,
				"no_caption" => 1,
				"value" => "&nbsp;",
			));
/*
			$this->vars(array(
				"lang_short" => iconv($charset_from_local, "utf-8", $language),
				"lang_name" => iconv($charset_from_local, "utf-8", $lang["name"]),
				"lang_caption" => iconv($charset_from, "utf-8", $caption),
				"lang_comment" => iconv($charset_from, "utf-8", $comment),
				"lang_help" => iconv($charset_from, "utf-8", $help),
			));
			$this->parse("SUB_TRANSLATE");
*/
		}

		if(count($no_writable_langs))
		{
			$this->gen_error(sprintf("J&auml;rgmiste keelte t&otilde;lkefailidel ei ole kirjutus&otilde;igust. Palun kontrollige &uuml;le: %s", join(", ", $no_writable_langs)));
		}
		$this->cb_htmlc->finish_output(array(
			"action" => "submit_editor",
			"method" => "POST",
			"data" => array(
				"class" => "cb_translate",
				"clid" => ($obj_is_folder)?"fld_".$arr["clid"]:"".$arr["clid"],
				"caption" => ($obj_is_folder)?iconv($charset_from_local, "utf-8",$clsfld[$arr["clid"]]["name"]):iconv($charset_from_local, "utf-8",$cls[$arr["clid"]]["name"]),
			),
		));
		
		$save_msg = array();
		if(strlen($arr["saved_langs"]))
		{
			foreach(split(",", $arr["saved_langs"]) as $lng)
			{
				$save_msg[] = $mod_lang[$lng]["name"]."(".$lng.")";
			}
		}
		return $this->cb_htmlc->get_result(array(
			"save_message" => count($save_msg)?sprintf("Salvestati j&auml;rgmiste keelte tekstid: %s",join(", ", $save_msg)):null,
		));
/*
		if(count($no_writable_langs))
		{
			$this->vars(array(
				"caption" => join(", ", $no_writable_langs),
			));
			$this->parse("FILE_ACCESS_ERROR");
		}

		// gen title
		$cls = aw_ini_get("classes");
		$title = ($obj_is_folder)?t("Kaust")." '".$clsfld[$arr["clid"]]["name"]."'":t("Klass")." '".$cls[$arr["clid"]]["name"]."'";
		$this->vars(array(
			"caption" => ($obj_is_folder)?iconv($charset_from_local, "utf-8",$clsfld[$arr["clid"]]["name"]):iconv($charset_from_local, "utf-8",$cls[$arr["clid"]]["name"]),
			"title" => iconv($charset_from_local, "utf-8", $title),
			"reforb" => $this->mk_reforb("submit_editor",array(
				"clid" => ($obj_is_folder)?"fld_".$arr["clid"]:"".$arr["clid"],
			)),
		));

		return ($this->parse());
		//return die($this->parse());
*/
	}

	/**
		@attrib name=groupedit
		@param clid required type=int
		@param grpid required
		@param saved_langs optional
	**/
	function groupedit($arr)
	{
		$charset_from_local = "iso-8859-1";
		$pot_scanner = get_instance("core/trans/pot_scanner");
		$languages = $pot_scanner->get_langs();
		aw_global_set("output_charset", "UTF-8");
		/*
		$this->read_template("proptrans.tpl");
		$this->sub_merge = 1;
		*/
		$cfgu = get_instance("cfg/cfgutils");

		$props = $cfgu->load_properties(array(
			"clid" => $arr["clid"],
			"filter" => $filter,
			"load_trans" => 0,
		));
		$groups = $cfgu->get_groupinfo();
		$cls = aw_ini_get("classes");
		$aw_location = $cls[trim($arr["clid"])]["file"];
		$po = split("[/]",$aw_location);
		$po_file = $po[count($po)-1];

		
		$this->cb_htmlc = get_instance("cfg/htmlclient");
		$this->cb_htmlc->start_output(array(
			"template" => "default",
		));

		$title = sprintf(t("Klass '%s', Grupp '%s'"), $cls[$arr["clid"]]["name"], "<b>".$groups[$arr["grpid"]]["caption"]."</b>");

		$this->cb_htmlc->add_property(array(
			"name" => "general_title",
			"type" => "text",
			"no_caption" => 1,
			"value" => $title,
			"textsize" => "17px;",
		));


		$langs_info = aw_ini_get("languages.list");
		foreach($langs_info as $lang_info)
		{
			$mod_lang[$lang_info["acceptlang"]] = $lang_info;
		}
		foreach($languages as $key => $language)
		{
			$file_location = aw_ini_get("basedir")."/lang/trans/".$language."/po/".$po_file.".po";
			$class_po_file = $pot_scanner->parse_po_file($file_location);
			$charset_from = $mod_lang[$language]["charset"];
			if(!$this->check_langfile($language, $po_file.".po"))
			{
				$no_writable_langs[] = $mod_lang[$language]["name"]."(".$language.")";
				continue;
			}
			unset($caption,$comment,$help);
			$comp = "Grupi ".$groups[$arr["grpid"]]["caption"]." (".$arr["grpid"].") ";
			foreach($class_po_file as $po)
			{
				if($po["msgid"] == $comp."pealkiri")
				{
					$caption = $po["msgstr"];
				}
				if($po["msgid"] == $comp."comment")
				{
					$comment = $po["msgstr"];
				}
				if($po["msgid"] == $comp."help")
				{
					$help = $po["msgstr"];
				}
			}
			foreach($langs_info as $lang)
			{
				if($lang["acceptlang"] == $language)
				{
					break;
				}
			}

			// new
			$this->cb_htmlc->add_property(array(
				"name" => "langname_".$language,
				"type" => "text",
				"caption" => t("Keel"),
				"value" => iconv($charset_from_local, "utf-8", $lang["name"]),
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][caption]",
				"type" => "textbox",
				"caption" => t("Grupi nimi"),
				"size" => 50,
				"value" => iconv($charset_from, "utf-8", $caption),
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][comment]",
				"type" => "textarea",
				"cols" => 50,
				"rows" => 5,
				"caption" => t("Kommentaar"),
				"value" => iconv($charset_from, "utf-8", $comment),
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][help]",
				"type" => "textarea",
				"cols" => 50,
				"rows" => 10,
				"caption" => t("Abitekst"),
				"value" => iconv($charset_from, "utf-8", $help),
			));
			$this->cb_htmlc->add_property(array(
				"name" => "sp_".$language,
				"no_caption" => 1,
				"value" => "&nbsp;",
			));
			/// new
/*
			$this->vars(array(
					"lang_short" => iconv($charset_from_local, "utf-8", $language),
					"lang_name" => iconv($charset_from_local, "utf-8", $lang["name"]),
					"lang_caption" => iconv($charset_from, "utf-8", $caption),
					"lang_comment" => iconv($charset_from, "utf-8", $comment),
					"lang_help" => iconv($charset_from, "utf-8", $help),
			));
			$this->parse("SUB_TRANSLATE");
*/
		};


		if(count($no_writable_langs))
		{
			$this->gen_error(sprintf("J&auml;rgmiste keelte t&otilde;lkefailidel ei ole kirjutus&otilde;igust. Palun kontrollige &uuml;le: %s", join(", ", $no_writable_langs)));
			$this->vars(array(
				"caption" => join(", ", $no_writable_langs),
			));
			$this->parse("FILE_ACCESS_ERROR");
		}
		$this->cb_htmlc->finish_output(array(
			"action" => "submit_editor",
			"method" => "POST",
			"data" => array(
				"class" => "cb_translate",
				"clid" => trim($arr["clid"]),
				"grpid" => $arr["grpid"],
				"caption" => iconv($charset_from_local, "utf-8", $groups[$arr["grpid"]]["caption"]),
			),
		));
		
		$save_msg = array();
		if(strlen($arr["saved_langs"]))
		{
			foreach(split(",", $arr["saved_langs"]) as $lng)
			{
				$save_msg[] = $mod_lang[$lng]["name"]."(".$lng.")";
			}
		}
		return $this->cb_htmlc->get_result(array(
			"save_message" => count($save_msg)?sprintf("Salvestati j&auml;rgmiste keelte tekstid: %s",join(", ", $save_msg)):null,
		));
/*
		if(count($no_writable_langs))
		{
			$this->vars(array(
				"caption" => join(", ", $no_writable_langs),
			));
			$this->parse("FILE_ACCESS_ERROR");
		}

		// gen title
		$title = t("Klass")." '".$cls[$arr["clid"]]["name"]."'";
		$title .= ", ".t("Grupp")." '".$groups[$arr["grpid"]]["caption"]."'";
		$this->vars(array(
			"caption" => iconv($charset_from_local, "utf-8", $groups[$arr["grpid"]]["caption"]),
			"title" => iconv(aw_global_get("charset"), "utf-8", $title),
			"reforb" => $this->mk_reforb("submit_editor",array(
				"clid" => $arr["clid"],
				"grpid" => $arr["grpid"],
			)),
		));
		return ($this->parse());
		//return die($this->parse());
*/
	}


	/**
		@attrib name=proptrans
		@param clid required
		@param propid required
		@param grpid required
		@param saved_langs optional
	**/
	function proptrans($arr)
	{
		$charset_from_local = "iso-8859-1";
		aw_global_set("output_charset", "UTF-8");
		$pot_scanner = get_instance("core/trans/pot_scanner");
		$languages = $this->get_use_languages();
		$this->read_template("proptrans.tpl");
		$this->sub_merge = 1;
		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_properties(array(
				"clid" => trim($arr["clid"]),
				"load_trans" => 0,
		));

		$cls = aw_ini_get("classes");
		$aw_location = $cls[trim($arr["clid"])]["file"];
		$po = split("[/]",$aw_location);
		$po_file = $po[count($po)-1];

		$groups = $cfgu->get_groupinfo();
		$langs_info = aw_ini_get("languages.list");
		
		$this->cb_htmlc = get_instance("cfg/htmlclient");
		$this->cb_htmlc->start_output(array(
			"template" => "default",
		));

		$title = sprintf("Klass '%s', Grupp '%s', Omadus '%s'('%s' t&uuml;&uuml;pi)", $cls[$arr["clid"]]["name"], $groups[$arr["grpid"]]["caption"], "<b>".$props[$arr["propid"]]["caption"]."</b>", $props[$arr["propid"]]["type"]);

		$this->cb_htmlc->add_property(array(
			"name" => "general_title",
			"type" => "text",
			"no_caption" => 1,
			"value" => $title,
			"textsize" => "17px;",
		));


		foreach($langs_info as $lang_info)
		{
			$mod_lang[$lang_info["acceptlang"]] = $lang_info;
		}
		foreach($languages as $key => $language)
		{
			$file_location = aw_ini_get("basedir")."/lang/trans/".$language."/po/".$po_file.".po";
			$class_po_file = $pot_scanner->parse_po_file($file_location);
			unset($caption,$comment,$help);
			$lang = $mod_lang[$language];

			if(!$this->check_langfile($language, $po_file.".po"))
			{
				$no_writable_langs[] = $mod_lang[$language]["name"]."(".$language.")";
				continue;
			}
			foreach($class_po_file as $po)
			{
				if($language == aw_global_get("LC"))
				{
					$correct_prop_name = ($po["msgid"] == "Omaduse  (".$arr["propid"].") caption")?$po["msgstr"]:$correct_prop_name;
				}
				if(strstr($po["msgid"], "Omaduse") && strstr($po["msgid"], $arr["propid"]) && strstr($po["msgid"], "caption"))
				{
					$caption = $po["msgstr"];
				}
				if(strstr($po["msgid"], "Omaduse") && strstr($po["msgid"], $arr["propid"]) && strstr($po["msgid"], "kommentaar"))
				{
					$comment = $po["msgstr"];
				}
				if(strstr($po["msgid"], "Omaduse") && strstr($po["msgid"], $arr["propid"]) && strstr($po["msgid"], "help"))
				{
					$help = $po["msgstr"];
				}
			}
			$charset_from = $lang["charset"];
			$langdir = aw_ini_get("basedir")."/lang/trans/".$language."/po/".$arr["proptrans"].".po";

			// new
			$this->cb_htmlc->add_property(array(
				"name" => "langname_".$language,
				"type" => "text",
				"caption" => t("Keel"),
				"value" => iconv($charset_from_local, "utf-8", $lang["name"]),
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][caption]",
				"type" => "textbox",
				"caption" => t("Omaduse nimi"),
				"value" => iconv($charset_from, "utf-8", $caption),
				"size" => 50,
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][comment]",
				"type" => "textarea",
				"caption" => t("Kommentaar"),
				"value" => iconv($charset_from, "utf-8", $comment),
				"rows" => 5,
				"cols" => 50,
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][help]",
				"type" => "textarea",
				"caption" => t("Abitekst"),
				"value" => iconv($charset_from, "utf-8", $help),
				"rows" => 10,
				"cols" => 50,
			));
			$this->cb_htmlc->add_property(array(
				"name" => "sp_".$language,
				"no_caption" => 1,
				"value" => "&nbsp;",
			));
			/// new

			$this->vars(array(
				"lang_name" => iconv($charset_from_local, "utf-8", $lang["name"]),
				"lang_short" => iconv($charset_from_local, "utf-8", $language),
				"lang_caption" => iconv($charset_from, "utf-8", $caption),
				"lang_comment" => iconv($charset_from, "utf-8", $comment),
				"lang_help" => iconv($charset_from, "utf-8", $help),
			));

			$this->parse("SUB_TRANSLATE");
		};

		$this->cb_htmlc->add_property(array(
			"type" => "hidden",
			"name" => "forward",
			"value" => 1,
		));

		$this->cb_htmlc->add_property(array(
			"name" => "save",
			"caption" => t("Salvesta"),
			"type" => "button",
			"class" => "sbtbutton",
			"onclick" => "cf = document.forms.changeform; cf.forward.value=0; submit_changeform('');",
		));

		$this->cb_htmlc->add_property(array(
			"no_caption" => 1,
			"value" => t("Salvesta ja edasi"),
			"type" => "submit",
			"name" => "continue",
		));

		if(count($no_writable_langs))
		{
			$this->gen_error(sprintf("J&auml;rgmiste keelte t&otilde;lkefailidel ei ole kirjutus&otilde;igust. Palun kontrollige &uuml;le: %s", join(", ", $no_writable_langs)));
			$this->vars(array(
				"caption" => join(", ", $no_writable_langs),
			));
			$this->parse("FILE_ACCESS_ERROR");
		}
		$this->cb_htmlc->finish_output(array(
			"action" => "submit_editor",
			"method" => "POST",
			"data" => array(
				"class" => "cb_translate",
				"clid" => trim($arr["clid"]),
				"grpid" => $arr["grpid"],
				"propid" => $arr["propid"],
				"caption" => iconv($charset_from_local, "utf-8", $props[$arr["propid"]]["caption"]),
			),
		));
		
		$save_msg = array();
		if(strlen($arr["saved_langs"]))
		{
			foreach(split(",", $arr["saved_langs"]) as $lng)
			{
				$save_msg[] = $mod_lang[$lng]["name"]."(".$lng.")";
			}
		}
		return $this->cb_htmlc->get_result(array(
			"save_message" => count($save_msg)?sprintf("Salvestati j&auml;rgmiste keelte tekstid: %s",join(", ", $save_msg)):null,
		));

		/*
		// gen title
		$title = t("Klass")." '".$cls[$arr["clid"]]["name"]."'";
		$title .= ", ".t("Grupp")." '".$groups[$arr["grpid"]]["caption"]."'";
		$title .= ", ".t("Omadus")." '".$props[$arr["propid"]]["caption"]."'";
		$title .= " ('".$props[$arr["propid"]]["type"]."' ".t("t&uuml;&uuml;pi").")";

		$this->vars(array(
			"caption" => iconv($charset_from_local, "utf-8", $props[$arr["propid"]]["caption"]),
			"title" => iconv($charset_from_local, "utf-8", $title),
			"reforb" => $this->mk_reforb("submit_editor",array(
				"clid" => trim($arr["clid"]),
				"grpid" => $arr["grpid"],
				"propid" => $arr["propid"],
			)),
		));
		return ($this->parse());
		*/
	}

	function get_use_languages()
	{
		$pot_scanner = get_instance("core/trans/pot_scanner");
		$languages = $pot_scanner->get_langs();
		$uo = obj(aw_global_get("uid_oid"));
		$use_langs = $uo->prop("target_lang");
		if(count($use_langs))
		{
			$li = get_instance("core/languages");
			$langs = array();
			foreach($use_langs as $id=>$lang)
			{
				$lid = $li->get_langid($id);
				if($languages[$lid])
				{
					$langs[$lid] = $lid;
				}
			}
			$languages = $langs;
		}
		return $languages;
	}

	/**
		@attrib name=layouttrans
		@param clid required
		@param lid required
		@param saved_langs optional
	**/
	function layouttrans($arr)
	{
		$charset_from_local = "iso-8859-1";
		aw_global_set("output_charset", "UTF-8");
		$pot_scanner = get_instance("core/trans/pot_scanner");
		$languages = $pot_scanner->get_langs();
		
		/*
		$this->read_template("layouttrans.tpl");
		$this->sub_merge = 1;
		*/

		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_properties(array(
				"clid" => trim($arr["clid"]),
				"load_trans" => 0,
		));

		$cls = aw_ini_get("classes");
		$aw_location = $cls[trim($arr["clid"])]["file"];
		$po = split("[/]",$aw_location);
		$po_file = $po[count($po)-1];

		$layouts = $cfgu->get_layoutinfo();

		$langs_info = aw_ini_get("languages.list");
		
		$this->cb_htmlc = get_instance("cfg/htmlclient");
		$this->cb_htmlc->start_output(array(
			"template" => "default",
		));

		// gen title
		$title = sprintf("Klass '%s', Kujudusosa '%s' ('%s' t&uuml;&uuml;pi)", iconv($charset_from_local, "UTF-8", $cls[$arr["clid"]]["name"]), "<b>".iconv($charset_from_local, "UTF-8", $layouts[$arr["lid"]]["area_caption"])."</b>", $layouts[$arr["lid"]]["type"]);
		$this->cb_htmlc->add_property(array(
			"name" => "general_title",
			"type" => "text",
			"no_caption" => 1,
			"value" => $title,
			"textsize" => "17px;",
		));

		foreach($langs_info as $lang_info)
		{
			$mod_lang[$lang_info["acceptlang"]] = $lang_info;
		}
		foreach($languages as $key => $language)
		{
			$lang = $mod_lang[$language];
			$file_location = aw_ini_get("basedir")."/lang/trans/".$language."/po/".$po_file.".po";
			unset($caption,$comment,$help);

			// we check if the translation file is writable or not
			if(!$this->check_langfile($language, $po_file.".po"))
			{
				$no_writable_langs[] = $mod_lang[$language]["name"]."(".$language.")";
				continue;
			}

			$class_po_file = $pot_scanner->parse_po_file($file_location);
			foreach($class_po_file as $po)
			{
				if($po["msgid"] == "Kujundusosa ".htmlentities(iconv($charset_from_local, $lang["charset"], $layouts[$arr["lid"]]["area_caption"]))." (".$arr["lid"].") pealkiri")
				{
					$caption = $po["msgstr"];
					break;
				}
			}

			$this->cb_htmlc->add_property(array(
				"name" => "langname_".$language,
				"type" => "text",
				"caption" => t("Keel"),
				"value" => iconv($charset_from_local, "utf-8", $lang["name"]),
			));
			$this->cb_htmlc->add_property(array(
				"name" => "vars[".$language."][pealkiri]",
				"type" => "textbox",
				"caption" => t("Kujundusosa nimi"),
				"value" => iconv($lang["charset"], "utf-8", $caption),
				"size" => 50,
			));
			$this->cb_htmlc->add_property(array(
				"name" => "sp_".$language,
				"no_caption" => 1,
				"value" => "&nbsp;",
			));
		};

		if(count($no_writable_langs))
		{
			$this->gen_error(sprintf("J&auml;rgmiste keelte t&otilde;lkefailidel ei ole kirjutus&otilde;igust. Palun kontrollige &uuml;le: %s", join(", ", $no_writable_langs)));
		}
		$this->cb_htmlc->finish_output(array(
			"action" => "submit_editor",
			"method" => "POST",
			"data" => array(
				"class" => "cb_translate",
				"clid" => trim($arr["clid"]),
				"lid" => $arr["lid"],
				"caption" => str_replace("&#44;", ",", iconv($charset_from_local, "utf-8", $layouts[$arr["lid"]]["area_caption"])),
			),
		));
		
		$save_msg = array();
		if(strlen($arr["saved_langs"]))
		{
			foreach(split(",", $arr["saved_langs"]) as $lng)
			{
				$save_msg[] = $mod_lang[$lng]["name"]."(".$lng.")";
			}
		}
		return $this->cb_htmlc->get_result(array(
			"save_message" => count($save_msg)?sprintf("Salvestati j&auml;rgmiste keelte tekstid: %s",join(", ", $save_msg)):null,
		));
	}

	/**
		@attrib name=show_changes
	**/
	function show_changes()
	{
		aw_global_set("output_charset", "utf-8");
		
		/*
		$this->read_template("show_changes.tpl");
		$this->sub_merge = 1;
		*/

		$data = aw_unserialize(core::get_cval("trans_changes"));
		$langs_info = aw_ini_get("languages.list");
		foreach($langs_info as $lang)
		{
			$mod_lang[$lang["acceptlang"]] = $lang;
		}
		$this->cb_htmlc = get_instance("cfg/htmlclient");
		$this->cb_htmlc->start_output(array(
			"template" => "default",
		));
		classload("vcl/table");
		$t = new aw_table();
		$t->define_field(array(
			"name" => "lang",
			"caption" => t("Keel"),
			"chgbgcolor" => "write_err",
		));
		$t->define_field(array(
			"name" => "object",
			"caption" => t("Element"),
			"chgbgcolor" => "write_err",
		));
		$t->define_field(array(
			"name" => "from",
			"caption" => t("Ennem"),
			"chgbgcolor" => "write_err",
		));
		$t->define_field(array(
			"name" => "to",
			"caption" => t("P&auml;rast"),
			"chgbgcolor" => "write_err",
		));

		$any_non_writable = false;
		foreach($data as $change)
		{
			$writable = true;
			if(!$this->check_langfile($change["lang"], basename($change["file"], ".po").".aw", true))
			{
				$writable = false;
				$any_non_writable = true;
			}
			$change["charset"] = $mod_lang[$change["lang"]]["charset"];
			$change["langname"] = $mod_lang[$change["lang"]]["name"];
			// check for type of object
			$header = $change["headers"][0];
			$this->vars(array(
				"bgcolor" => $writable?"FFFFFF":"F11111",
				"bgcolor_lang" => $writable?"FF0000":"F11111",
				"lang" => iconv($change["charset"], "utf-8",$change["langname"]),
				"object" => iconv($change["charset"], "utf-8", $change["msgid"]),
				"prev" => (strlen($change["prev_contents"]) < 1)?iconv(aw_global_get("charset"), "utf-8", sprintf("<i>%s</i>", t("tekst puudus"))):iconv($change["charset"], "utf-8", nl2br($change["prev_contents"])),
				"new" => (strlen($change["contents"]) < 1)?iconv(aw_global_get("charset"), "utf-8", sprintf("<i>%s</i>", t("tekst eemaldati"))):iconv($change["charset"], "utf-8", nl2br($change["contents"])),
			));
			$this->parse("SUB_CHANGE");
			$is = true;

			// new approach
			$t->define_data(array(
				"lang" => iconv($change["charset"], "utf-8",$change["langname"]),
				"object" => iconv($change["charset"], "utf-8", $change["msgid"]),
				"from" => (strlen($change["prev_contents"]) < 1)?iconv(aw_global_get("charset"), "utf-8", sprintf("<i>%s</i>", t("tekst puudus"))):iconv($change["charset"], "utf-8", nl2br($change["prev_contents"])),
				"to" => (strlen($change["contents"]) < 1)?iconv(aw_global_get("charset"), "utf-8", sprintf("<i>%s</i>", t("tekst eemaldati"))):iconv($change["charset"], "utf-8", nl2br($change["contents"])),
				"write_err" => !$writable?"#F11111":"",
			));
		}
		if($any_non_writable)
		{
			$this->gen_error(t("punasega t&auml;histatud ridu ei ole v&otilde;imalik t&otilde;lkefailide kirjutus&otilde;iguste puudumise t&otilde;ttu salvestada!"));
		}
		
		$this->cb_htmlc->add_property(array(
			"name" => "changetable",
			"type" => "text",
			"no_caption" => 1,
			"value" => $t->draw(),
		));
		$this->cb_htmlc->finish_output(array(
			"action" => "apply_changes",
			"method" => "POST",
			"data" => array(
				"class" => "cb_translate",
			),
		));
		return $this->cb_htmlc->get_result();

		/*
		$this->vars(array(
			"caption" => iconv(aw_global_get("charset"), "utf-8",t("Sooritatud muudatused")),
		));
		if($is)
		{
			$this->vars(array(
				"apply_link" => html::href(array(
					"url" => $this->mk_my_orb("apply_changes"),
					"target" => "editorcontent",
					"class" => "apply",
					"caption" => iconv(aw_global_get("charset") , "utf-8", t("Kinnita muudatused")),
				)),
			));
		}
		else
		{
			$this->vars(array(
				"nochange" => iconv(aw_global_get("charset"), "utf-8",t("muudatusi pole")),
			));
			$this->parse("NO_CHANGE");
		}
		return die($this->parse());
		*/
	}

	function gen_error($str)
	{
		$this->cb_htmlc->vars(array(
			"error_text" => $str,
		));
		$this->cb_htmlc->error = $this->cb_htmlc->parse("ERROR");
	}

	/**
		@attrib name=actual_commit
	**/
	function actual_commit($arr)
	{
		// tell me someone how to make it work?:S
		/*
		$files = unserialize(core::get_cval("trans_applyed"));
		$files = join($files," ");
		$ini = aw_ini_get("server.cvs");
		chdir(aw_ini_get("basedir"));
		//$ini = "/data/www/tarvo/automatweb_dev/t.sh";
		$line = $ini." -d :pserver:automatweb@dev.struktuur.ee:/home/cvs update init.aw 2>&1";
		arr($line);
		arr(shell_exec($line));
		//$c = popen($line,"w");
		//fwrite($c, "urukuai");
		//pclose($c);
		core::set_cval("trans_applyed","");
		*/
		return $this->mk_my_orb("classeditor", array("clid" => "fld_1"),"",1);
	}

	/**
		@attrib name=commit_changes
	**/
	function commit_changes()
	{
		/*
		$this->read_template("commit.tpl");
		$this->sub_merge = 1;
		$arr = unserialize(stripslashes(core::get_cval("trans_applyed")));
		if(is_array($arr) && count($arr) > 0)
		{
			foreach($arr as $file)
			{
				$this->vars(array(
					"filename" => $file,
				));
				$this->parse("SUB_TRANSLATE");
			}
			$this->parse("TAREA");
		}
		else
		{
			$nofiles = true;
			$this->vars(array(
				"filename" => t("Muudetud faile ei ole!"),
			));
			$this->parse("SUB_TRANSLATE");
		}
		$link = html::href(array(
			//"url" => $this->mk_my_orb("actual_commit"),
			"url" => "javascript:submit_changeform();",
			"caption" => iconv(aw_global_get("charset") , "utf-8", t("CVS commit")),
			"target" => "editorcontent",
		));
		$this->vars(array(
			"seletus" => t("Seletus"),
			"failid" => t("Commitimisele minevad failid"),
			"commit_link" => $nofiles?"":$link,
			"reforb" => $this->mk_reforb("actual_commit"),
		));

		return die($this->parse());
		*/
	}

	/**
		@attrib name=apply_changes
	**/
	function apply_changes()
	{
		$t=get_instance("core/trans/pot_scanner");

		$data = aw_unserialize(core::get_cval("trans_changes"));
		$unwr_changes = array();
		foreach($data as $change)
		{
			$aw_file = aw_ini_get("basedir")."/lang/trans/".$change["lang"]."/aw/".basename($change["file"], ".po").".aw";
			if(is_writable($aw_file))
			{
				$files[] = $aw_file;
				$lang[$aw_file] = $change["lang"];
				$files_to_commit[] = "lang/trans/".$change["lang"]."/aw/".basename($change["file"], ".po").".aw";
				$files_to_commit[] = "lang/trans/pot/".basename($change["file"], ".po").".pot";
				$files_to_commit[] = "lang/trans/".$change["lang"]."/po/".basename($change["file"]);
			}
			else
			{
				$unwr_changes[] = $change;
			}
		}
		$files = array_unique($files);
		foreach($files as $file)
		{
			$t->_make_aw_from_po(aw_ini_get("basedir")."/lang/trans/".$lang[$file]."/po/".basename($file, ".aw").".po",$file);
		}


		/* well, this jack shit actually sets the flag for files that are translated, but not yet commited. but because this commit stuff is really pain in the butt.. i ignore this right now
		// reads peviously made changes from conf
		$not_applyed = unserialize(stripslashes(core::get_cval("trans_applyed")));
		$not_applyed = array_merge($not_applyed, $files_to_commit);
		$not_applyed = array_unique($not_applyed);
		// writes into config, writes files to be commited
		core::set_cval("trans_applyed", serialize(addslashes($not_applyed)));
		*/
		core::set_cval("trans_changes", aw_serialize($unwr_changes, SERIALIZE_NATIVE));
		
		return $this->mk_my_orb("show_changes");
	}

	/**
		@attrib name=submit_editor
	**/
	function submit_editor($arr)
	{
		$arr["caption"] = htmlentities($arr["caption"], ENT_COMPAT, "UTF-8");
		//die(arr($arr));
		$langs_info = aw_ini_get("languages.list");
		if(strlen(trim($arr["propid"])))
		{
			$save_type = PRP;
		}
		elseif(strlen(trim($arr["grpid"])))
		{
			$save_type = GRP;
		}
		elseif(strlen(trim($arr["reltype"])))
		{
			$save_type = REL;
		}
		elseif(trim($arr["text"]) == 1)
		{
			$save_type = TXT;
		}
		elseif(trim($arr["lid"]))
		{
			$save_type = LYT;
		}
		elseif(is_numeric(trim($arr["clid"])))
		{
			$save_type = CLS;
		}
		elseif(substr(trim($arr["clid"]),0,4) == "fld_")
		{
			$save_type = FLD;
		}
		$tmp = array(
			PRP => array(
				"caption" =>
					"caption",
				"comment" => "kommentaar",
				"help" => "help",
				"strid_start" => "Omaduse",
				"id" => $arr["propid"],
			),
			LYT => array(
				"pealkiri" => "pealkiri",
				"strid_start" => "Kujundusosa",
				"id" => $arr["lid"],
			),
			GRP => array(
				"caption" =>
					"pealkiri",
				"comment" => "comment",
				"help" => "help",
				"strid_start" => "Grupi",
				"id" => $arr["grpid"],
			),
			CLS => array(
				"caption" =>
					"nimi",
				"comment" => "comment",
				"help" => "help",
				"strid_start" => "Klassi",
				"id" => $arr["clid"],
			),
			FLD => array(
				"caption" =>
					"nimi",
				"comment" => "comment",
				"help" => "help",
				"strid_start" => "Klassi kataloogi",
				"id" => substr($arr["clid"], 4),
			),
			REL => array(
				"caption" =>
					"tekst",
				"strid_start" => "Seose",
				"id" => $arr["reltype"],
			),
			TXT => array(
				"id" => $arr["line"],
			),
		);
		$cls = aw_ini_get("classes");

		$pot_scanner = get_instance("core/trans/pot_scanner");
		foreach($arr["vars"] as $lang => $vars)
		{
			foreach($langs_info as $lang_info)
			{
				if($lang_info["acceptlang"] == $lang)
				{
					$charset_to = $lang_info["charset"];
					break;
				}
			}
			if(in_array($save_type, array(PRP, GRP, REL, LYT)))
			{
				$file = aw_ini_get("basedir")."/lang/trans/".$lang."/po/".end(split("/",$cls[$arr["clid"]]["file"])).".po";
				if(is_file($file))
				{
					// pean vist faili sisse lugema ja muutusi kontrollima ?:S
					$po_contents = $pot_scanner->parse_po_file($file);

					$var_already_set = array();
					// for groups, properties and class help.. other class and folder texts are in other po file
					foreach($po_contents as $entry_no => $entry)
					{
						foreach($vars as $varname => $var)
						{
							$comp = $tmp[$save_type]["strid_start"]." ".$arr["caption"]." (".$tmp[$save_type]["id"].") ".$tmp[$save_type][$varname];
							if($comp == iconv($charset_to, "utf-8", $entry["msgid"]))
							{
								$var_already_set[$varname] = $var;
								if(iconv($charset_to, "utf-8", $entry["msgstr"]) != trim($var))
								{
									$change_log[] = array(
										"entry_no" => $entry_no,
										"file" => $file,
										"headers" => $entry["headers"],
										"msgid" => $entry["msgid"],
										"lang" => $lang,
										"var" => $varname,
										"contents" => iconv("utf-8", $charset_to, $var),
										"prev_contents" => $entry["msgstr"],
										"file_exists" => 1,
										"var_exists" => 1,
									);
								}
							}
						}
					}
					// check those those vars which dont have an entry in the po file
					if(count(array_diff($vars,$var_already_set)) > 0)
					{
						foreach(array_diff($vars,$var_already_set) as $varname => $var)
						{
							if(strlen(trim($var)) > 0)
							{
								$change_log[] = array(
									"lang" => $lang,
									"file" => $file,
									"var" => $varname,
									"file_exists" => 1,
									"contents" => iconv("utf-8", $charset_to, $var),
									"var_exists" => 0,
								);
							}
						}
					}
				}
				else
				{
					foreach($vars  as $varname => $var)
					{
						if(strlen(trim($var)) > 0)
						{
							$change_log[] = array(
								"lang" => $lang,
								"file" => $file,
								"var" => $varname,
								"file_exists" => 0,
								"contents" => iconv("utf-8", $charset_to, $var),
							);
						}
					}
				}
			}
			if($save_type == CLS || $save_type == FLD)
			{
				// check for classfolders and class text changes
				$file = aw_ini_get("basedir")."/lang/trans/".$lang."/po/aw.ini.po";
				if(is_file($file))
				{
					$po_contents = $pot_scanner->parse_po_file($file);
					$var_already_set = array();
					foreach($po_contents as $entry_no => $entry)
					{
						foreach($vars as $varname => $var)
						{
							$comp = $tmp[$save_type]["strid_start"]." ".$arr["caption"]." (".$tmp[$save_type]["id"].") ".$tmp[$save_type][$varname];
							if(iconv($charset_to, "utf-8", $entry["msgid"]) == $comp)
							{
								$var_already_set[$varname] = $var;
								if(iconv($charset_to, "utf-8", $entry["msgstr"]) != $var)
								{
									$change_log[] = array(
										"entry_no" => $entry_no,
										"file" => $file,
										"headers" => $entry["headers"],
										"msgid" => $entry["msgid"],
										"lang" => $lang,
										"var" => $varname,
										"contents" => iconv("utf-8", $charset_to, $var),
										"prev_contents" => $entry["msgstr"],
										"file_exists" => 1,
										"var_exists" => 1,
									);
								}
							}
						}
					}
					// check those those vars which dont have an entry in the po file
					if(count(array_diff($vars,$var_already_set)) > 0)
					{
						foreach(array_diff($vars,$var_already_set) as $varname => $var)
						{
							if(strlen(trim($var)) > 0)
							{
								$change_log[] = array(
									"lang" => $lang,
									"file" => $file,
									"var" => $varname,
									"file_exists" => 1,
									"contents" => iconv("utf-8", $charset_to, $var),
									"var_exists" => 0,
								);
							}
						}
					}
				}
				// fail on puudu, kontrollib kas vaja teha
				else
				{
					foreach($vars  as $varname => $var)
					{
						if(strlen(trim($var)) > 0)
						{
							$change_log[] = array(
								"lang" => $lang,
								"file" => $file,
								"var" => $varname,
								"file_exists" => 0,
								"contents" => iconv("utf-8", $charset_to, $var),
							);
						}
					}
				}
			}
			if($save_type == TXT)
			{
				$file = aw_ini_get("basedir")."/lang/trans/".$lang."/po/".end(split("/",$cls[$arr["clid"]]["file"])).".po";
				if(is_file($file))
				{
					$po_contents = $pot_scanner->parse_po_file($file);
					// goes trough every text in class(sent from form)
					foreach($vars as $msgid => $msgstr)
					{
						$var_exists = 0;
						$msgid = htmlentities($msgid, ENT_COMPAT, "UTF-8");
						// goes trough po file contents
						foreach($po_contents as $entry_no => $entry)
						{
							// filters out right line
							if(trim($entry["msgid"]) == trim($msgid))
							{
								$var_exists = 1;
								// checks if msgstr has been changed
								if(iconv($charset_to, "utf-8", $entry["msgstr"]) != trim($msgstr))
								{
									$change_log[] = array(
										"entry_no" => $entry_no,
										"file" => $file,
										"headers" => $entry["headers"],
										"msgid" => $msgid,
										"lang" => $lang,
										"var" => $msgid,
										"contents" => iconv("utf-8", $charset_to, $msgstr),
										"prev_contents" => $entry["msgstr"],
										"file_exists" => 1,
										"var_exists" => 1,
									);
								}
							}
						}
						if($var_exists == 0)
						{
							if(strlen(trim($msgstr)) > 0)
							{
								$headers = $this->_get_line_headers($tmp[CLS]["id"]);
								$change_log[] = array(
									"headers" => $headers[$msgid],
									"lang" => $lang,
									"file" => $file,
									"msgid" => $msgid,
									"var" => $msgid,
									"file_exists" => 1,
									"contents" => iconv("utf-8", $charset_to, $msgstr),
									"var_exists" => 0,
								);
							}
						}
					}
				}
				else
				{
					foreach($vars  as $msgid => $msgstr)
					{
						$msgid = htmlentities($msgid, ENT_COMPAT, "UTF-8");
						if(strlen(trim($msgstr)) > 0)
						{
							$headers = $this->_get_line_headers($tmp[CLS]["id"]);
							$change_log[] = array(
								"headers" => $headers[$msgid],
								"file" => $file,
								"lang" => $lang,
								"msgid" => $msgid,
								"var" => $msgid,
								"contents" => iconv("utf-8", $charset_to, $msgstr),
								"var_exists" => 0,
							);
						}
					}
				}
			}
		}
		//die(arr($change_log));
		if(count($change_log) > 0)
		{
			foreach($change_log as $change_nr => $change)
			{
				if(in_array($save_type, array(GRP, PRP, REL, TXT, LYT)))
				{
					$file_location = aw_ini_get("basedir")."/lang/trans/".$change["lang"]."/po/".end(split("/",$cls[$arr["clid"]]["file"])).".po";
				}
				if(in_array($save_type, array(CLS, FLD)))
				{
					$file_location = aw_ini_get("basedir")."/lang/trans/".$change["lang"]."/po/aw.ini.po";
				}

				if($change["file_exists"] == 1)
				{
					$file = $pot_scanner->parse_po_file($file_location);
				}
				else
				{
					$file = array();
				}
				if($change["file_exists"] == 1 && $change["var_exists"] == 1)
				{
					foreach($file as $entry_no => $entry)
					{
						if($entry_no == $change["entry_no"])
						{
							$file[$entry_no] = array(
								"headers" => $change["headers"],
								"msgid" => $change["msgid"],
								"msgstr" => $change["contents"],
							);
						}
					}
				}
				if($change["var_exists"] == 0)
				{
					unset($end);
					if($change["var"] == "comment")
					{
						$end = "_comment";
					}
					if($change["var"] == "help")
					{
						$end = "_help";
					}
					$cls = aw_ini_get("classes");
					switch($save_type)
					{
						case PRP:
							$header = "# classes/".$cls[trim($arr["clid"])]["file"].".".aw_ini_get("ext").":group_".$arr["grpid"].$end;
							$msgid = "Omaduse ".$arr["caption"]." (".$arr["propid"].") ".$tmp[PRP][$change["var"]];
							break;
						case GRP:
							$header = "# classes/".$cls[trim($arr["clid"])]["file"].".".aw_ini_get("ext").":group_".$arr["grpid"].$end;
							$msgid = "Grupi ".$arr["caption"]." (".$arr["grpid"].") ".$tmp[GRP][$change["var"]];
							break;
						case CLS:
							$header = "#: aw.ini:class_".trim($arr["clid"]);
							$msgid = "Klassi ".$arr["caption"]." (".trim($arr["clid"]).") ".$tmp[CLS][$change["var"]];
							break;
						case FLD:
							$header = "# aw.ini:classfolder_".substr(trim($arr["clid"]),4);
							$msgid = "Klassi kataloogi ".$arr["caption"]." (".substr(trim($arr["clid"]),4).") ".$tmp[FLD][$change["var"]];
							break;
						case REL:
							$header = "# classes/".$cls[trim($arr["clid"])]["file"].".".aw_ini_get("ext").":rel_".$arr["reltype"];
							$msgid = "Seose ".$arr["caption"]." (".$arr["reltype"].") ".$tmp[REL][$change["var"]];
							break;
						case LYT:
							$header = "# classes/".$cls[trim($arr["clid"])]["file"].".".aw_ini_get("ext").":layout_".$arr["lid"];
							$msgid = "Kujundusosa ".$arr["caption"]." (".$arr["lid"].") ".$tmp[LYT][$change["var"]];
							break;
						case TXT:
							$header = $change["headers"];
							$msgid = $change["msgid"];
							break;
					}
					$change_log[$change_nr]["msgid"] = $msgid;
					$change_log[$change_nr]["headers"] = array($header);
					$file[] = array(
						"headers" => $change_log[$change_nr]["headers"],
						"msgid" => $change_log[$change_nr]["msgid"],
						"msgstr" => $change["contents"],
					);
				}
				$pot_scanner->write_po_file(array("location" => $file_location, "contents" => $file));

				// reads peviously made changes from conf
				$not_applyed = aw_unserialize(core::get_cval("trans_changes"));

				// checks if changes have to be added or overwritten
				foreach($change_log as $new_change)
				{
					$was_set = false;
					foreach($not_applyed as $old_nr => $old_change)
					{
						if($old_change["msgid"] == $new_change["msgid"] && $old_change["lang"] == $new_change["lang"])
						{
							$was_set = true;
							$not_applyed[$old_nr] = $new_change;
						}
					}
					if(!$was_set)
					{
						$not_applyed[] = $new_change;
					}
				}
				// writes into config
				$to_be_applyed = aw_serialize($not_applyed, SERIALIZE_NATIVE);
				core::set_cval("trans_changes", $to_be_applyed);
				$return_params["saved_langs"][$change["lang"]] = $change["lang"];
				//core::set_cval("trans_changes", "");
			}
		}
		else
		{
			//arr("mitte muhvigi pole muudetud");
		}
		$return_params["clid"] = $arr["clid"];
		$return_params["saved_langs"] = is_array($return_params["saved_langs"])?join(",", $return_params["saved_langs"]):"";
		switch($save_type)
		{
			case FLD:
				$return_params["action"] = "classeditor";
				$return_params["clid"] = "fld_".$tmp[FLD]["id"];
				break;
			case CLS:
				$return_params["action"] = "classeditor";
				$return_params["clid"] = $tmp[CLS]["id"];
				break;
			case GRP:
				$return_params["action"] = "groupedit";
				$return_params["clid"] = $tmp[CLS]["id"];
				$return_params["grpid"] = $tmp[GRP]["id"];
				break;
			case PRP:
				$return_params["action"] = "proptrans";
				$return_params["grpid"] = $tmp[GRP]["id"];
				$return_params["propid"] = $tmp[PRP]["id"];
				break;
			case REL:
				$return_params["action"] = "releditor";
				$return_params["reltype"] = $tmp[REL]["id"];
				break;
			case TXT:
				$return_params["action"] = "lineeditor";
				$return_params["clid"] = $tmp[CLS]["id"];
				break;
			case LYT:
				$return_params["action"] = "layouttrans";
				$return_params["clid"] = $tmp[CLS]["id"];
				$return_params["lid"] = $tmp[LYT]["id"];
				break;
		}
		if($save_type == PRP && $arr["forward"])
		{
			$languages = $this->get_use_languages();
			$pot_scanner = get_instance("core/trans/pot_scanner");
			$cls = aw_ini_get("classes");
			$aw_location = $cls[trim($arr["clid"])]["file"];
			$po = split("[/]",$aw_location);
			$po_file = $po[count($po)-1];
			$useprop = array();
			$cfgu = get_instance("cfg/cfgutils");
			$props = $cfgu->load_properties(array(
					"clid" => $arr["clid"],
			));
			foreach($languages as $language)
			{
				$look = 0;
				$file_location = aw_ini_get("basedir")."/lang/trans/".$language."/po/".$po_file.".po";
				$lines = $pot_scanner->parse_po_file($file_location);
				foreach($lines as $id => $data)
				{
					$tmp = explode(":", $data["headers"][0]);
					$prop = substr(trim($tmp[2]),5);
					if($look && !$data["msgstr"] && strstr($data["msgid"], "Omaduse") && strstr($data["msgid"], "caption") && !$useprop[$language] && $props[$prop]["type"]!="hidden")
					{
						$useprop[$language] = array(
							"id" => $id,
							"prop" => $prop,
						);
					}
					if(strstr($data["msgid"], "Omaduse") && strstr($data["msgid"], $arr["propid"]) && strstr($data["msgid"], "caption"))
					{
						$look = 1;
					}
				}
			}
			if(count($useprop))
			{
				$high = array("id" => 0);
				foreach($languages as $language)
				{
					if($useprop[$language]["id"] > $high["id"])
					{
						$high = $useprop[$language];
					}
				}
				$prop = $high["prop"];
				$return_params["propid"] = $prop;
				$return_params["grpid"] = $props[$prop]["group"];
			}
		}
		$url = $this->mk_my_orb($return_params["action"], $return_params,"",1);
		return $url;

	}

	function _get_line_headers($class)
	{
		$pot_scanner = get_instance("core/trans/pot_scanner");
		$languages = $pot_scanner->get_langs();
		$cls = aw_ini_get("classes");
		foreach($languages as $key => $language)
		{
			$file_location = aw_ini_get("basedir")."/lang/trans/".$language."/po/".end(split("/",$cls[$class]["file"])).".po";
			if(!is_file($file_location))
			{
				continue;
			}
			$po_file = $pot_scanner->parse_po_file($file_location);
			foreach($po_file as $entry_no => $entry)
			{
				$header = $entry["headers"][0];
				if(is_numeric(trim(end(split(":", trim(substr($header,strpos($header, " "))))))))
				{
					$return[$entry["msgid"]] = trim($header);
				}
			}
		}
		return array_unique($return);
	}

	function check_langfile($lang, $file, $aw = false)
	{
		
		if(is_writable(aw_ini_get("basedir")."/lang/trans/".$lang."/".($aw?"aw":"po")."/".$file))
		{
			return true;
		}
		else
		{
			 return false;
		}
	}

	/**
		@attrib name=aw_url params=name all_args=1
		@param trans_url required string
	**/
	function aw_url($arr)
	{
		$url = parse_url($arr);
		$site_url = parse_url(aw_ini_get("baseurl"));
		if($url["host"] != $site_url["host"])
		{
			return false;
		}
		
		
		return $arr["trans_url"];
	}

	/**
		@attrib name=search_trans params=name all_args=1
	**/
	function search_trans($arr)
	{
		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output(array(
			"template" => "default",
		));
		$htmlc->add_property(array(
			"name" => "string",
			"caption" => t("Otsitav s&otilde;na"),
			"type" => "textbox",
		));
		$htmlc->finish_output(array(
			"action" => "fafa",
			"method" => "POST"
		));
		$ret = $htmlc->get_result(array(
			"form_only" => 1,
		));
		return $ret;
	}

};
?>
