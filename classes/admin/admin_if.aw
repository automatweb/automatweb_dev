<?php
/*
@classinfo syslog_type=ST_ADMIN_IF no_comment=1 no_status=1 prop_cb=1 maintainer=kristo

@default table=objects
@default group=o

	@property o_tb type=toolbar no_caption=1 store=no

	@layout o_bottom type=hbox width=30%:80%

		@layout o_bot_left type=vbox parent=o_bottom closeable=1 area_caption=Kataloogid

			@property o_tree type=treeview no_caption=1 store=no parent=o_bot_left

		@layout o_bot_right type=vbox parent=o_bottom 

			@property o_tbl type=table no_caption=1 store=no parent=o_bot_right


@default group=fu

	@property info_text type=text store=no 
	@caption Info

	@property zip_upload type=fileupload 
	@caption Laadi ZIP fail

	@property uploader type=text store=no
	@caption Lae faile	

@groupinfo o caption="Objektid" save=no submit=no
@groupinfo fu caption="Failide &uuml;leslaadimine" 
*/

class admin_if extends class_base
{
	var $use_parent;
	var $force_0_parent;

	function admin_if()
	{
		$this->init(array(
			"tpldir" => "workbench",
			"clid" => CL_ADMIN_IF
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "info_text":
				if (!empty($_SESSION["fu_tm_text"]))
				{
					$prop["value"] = $_SESSION["fu_tm_text"];
					unset($_SESSION["fu_tm_text"]);
				}
				else
				{
					return PROP_IGNORE;
				}
				break;

			case "o_tb":
				$this->_o_tb($arr);
				break;

			case "o_tree":
				$this->_o_tree($arr);
				break;

			case "o_tbl":
				$this->_o_tbl($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["parent"] = $_GET["parent"];
	}

	function callback_mod_retval($arr)
	{
		$arr["args"]["parent"] = $arr["request"]["parent"];
	}

	private function _o_tb($arr)
	{
		$parent = $arr["request"]["parent"];
		$parent = !empty($parent) ? $parent : $this->cfg["rootmenu"];
		$arr["request"]["parent"] = $parent;

		$tb =& $arr["prop"]["vcl_inst"];
		// add button only visible if the add privilege is set
		if ($this->can("add", $arr["request"]["parent"]))
		{
			$tb->add_menu_button(array(
				"name" => "new",
				"tooltip" => t("Lisa"),
			));
			
			$this->generate_new(& $tb, $arr["request"]["parent"]);
		}

		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"action" => "save_if",
			"img" => "save.gif",
		));

		$tb->add_separator();

		$tb->add_button(array(
			"name" => "cut",
			"tooltip" => t("L&otilde;ika"),
			"action" => "if_cut",
			"img" => "cut.gif",
		));

		$tb->add_button(array(
			"name" => "copy",
			"tooltip" => t("Kopeeri"),
			"action" => "if_copy",
			"img" => "copy.gif",
		));

		if (count($this->get_cutcopied_objects()) && $this->can("add", $arr["request"]["parent"]))
		{
			$tb->add_button(array(
				"name" => "paste",
				"tooltip" => t("Kleebi"),
				"action" => "if_paste",
				"img" => "paste.gif",
			));
		}

		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"confirm" => t("Kustutada valitud objektid?"),
			"action" => "if_delete",
			"img" => "delete.gif",
		));
		$tb->add_separator();

		$tb->add_button(array(
			"name" => "refresh",
			"tooltip" => t("Uuenda"),
			"url" => "javascript:window.location.reload()",
			"img" => "refresh.gif",
		));

		$tb->add_menu_button(array(
			"name" => "import",
			"tooltip" => t("Impordi"),
			"img" => "import.gif"
		));	

		$tb->add_menu_item(array(
			"parent" => "import",
			"text" => t("Impordi kaustu"),
			"title" => t("Impordi kaustu"),
			"name" => "import_menus",
			"tooltip" => t("Impordi kaustu"),
			"link" => $this->mk_my_orb("import",array("parent" => $arr["request"]["parent"]), "admin_menus"),
		));

		$tb->add_menu_item(array(
			"parent" => "import",
			"text" => t("Impordi faile"),
			"title" => t("Impordi faile"),
			"name" => "import_files",
			"tooltip" => t("Impordi faile"),
			"link" => aw_url_change_var("group", "fu")
		));

		$tb->add_button(array(
			"name" => "preview",
			"tooltip" => t("Eelvaade"),
			"target" => "_blank",
			"url" => obj_link($arr["request"]["parent"]),
			"img" => "preview.gif",
		));
		$file_manager = get_instance("admin/file_manager");
		$file_manager->add_zip_button(array("tb" => $tb));
	}

	private function get_cutcopied_objects()
	{
		$sel_objs = aw_global_get("cut_objects");
		if (!is_array($sel_objs))
		{
			$sel_objs = array();
		}
		$t = aw_global_get("copied_objects");
		if (!is_array($t))
		{
			$t = array();
		}
		$sel_objs+=$t;
		return $sel_objs;
	}

	private function _o_tree($arr)
	{
		$tree =& $arr["prop"]["vcl_inst"];

		classload("core/icons");
		$rn = empty($this->use_parent) ? $this->cfg["admin_rootmenu2"] : $this->use_parent;

		$this->period = isset($arr["request"]["period"]) ? $arr["request"]["period"] : null;
		$admrm = $this->cfg["admin_rootmenu2"];
		if (is_array($admrm))
		{
			$admrm = reset($admrm);
		}
		$this->curl = isset($arr["request"]["curl"]) ? $arr["request"]["curl"] : get_ru();
		$this->selp = isset($arr["request"]["selp"]) ? $arr["request"]["selp"] : $arr["request"]["parent"];
		$tree->start_tree(array(
			"type" => TREE_DHTML,
			"has_root" => $this->use_parent ? 0 : 1,
			"tree_id" => "admin_if",
			"persist_state" => 1,
			"root_name" => t("<b>AutomatWeb</b>"),
			"root_url" => aw_url_change_var("parent", $admrm, $this->curl),
			"get_branch_func" => $this->mk_my_orb("gen_folders",array("selp" => $this->selp, "curl" => $this->curl, "period" => $this->period, "parent" => "0")),
		));

		$has_items = array();
		if (is_array($rn) && count($rn) >1)
		{
			foreach($rn as $rn_i)
			{
				if (isset($has_items[$rn_i]) && $this->can("view", $rn_i))
				{
					continue;
				}
				$has_items[$rn_i] = 1;
				$rn_o = obj($rn_i);
				$tree->add_item(0,array(
					"id" => $rn_i,
					"parent" => 0,
					"name" => parse_obj_name($rn_o->trans_get_val("name")),
					"iconurl" => icons::get_icon_url($rn_o),
					"url" => aw_url_change_var("parent", $rn_o->id(), $this->curl)
				));
			}
			$this->force_0_parent= true;
		}
		else
		{
			if (is_array($rn))
			{	
				$rn = reset($rn);
			}
		}
		$filt = array(
			"class_id" => array(CL_MENU, CL_BROTHER, CL_GROUP),
			"parent" => $rn,
			"CL_MENU.type" => new obj_predicate_not(array(MN_FORM_ELEMENT, MN_HOME_FOLDER)),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
						"lang_id" => aw_global_get("lang_id"),
						"CL_MENU.type" => array(MN_CLIENT, MN_ADMIN1)
				)
			)),
			"site_id" => array(),
			"lang_id" => array(),
			"sort_by" => "objects.parent,objects.jrk,objects.created"
		);
		$ol = new object_list($filt);

		$second_level_parents = array();
		foreach($ol->arr() as $menu)
		{
			if (isset($has_items[$menu->id()]))
			{
				continue;
			}
			$rs = $this->resolve_item_new($menu);
			if ($rs !== false)
			{	
				$tree->add_item($rs["parent"], $rs);
				$has_items[$menu->id()] = 1;
				// also, gather all id's of objects that were inserted in the tree, so that
				// we can also get their submenus so that the tree know is they have subitems
				$second_level_parents[$rs["id"]] = $rs["id"];
			}
		}

		if (count($second_level_parents))
		{
			$ol = new object_list(array(
				"class_id" => array(CL_MENU, CL_BROTHER, CL_GROUP),
				"parent" => $second_level_parents,
				"CL_MENU.type" => new obj_predicate_not(array(MN_FORM_ELEMENT, MN_HOME_FOLDER)),
				new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array(
							"lang_id" => aw_global_get("lang_id"),
							"CL_MENU.type" => array(MN_CLIENT, MN_ADMIN1)
					)
				)),
				"site_id" => array(),
				"sort_by" => "objects.parent,objects.jrk,objects.created"
			));
			foreach($ol->arr() as $menu)
			{
				if (isset($has_items[$menu->id()]))
				{
					continue;
				}
				$rs = $this->resolve_item_new($menu);
				if ($rs !== false)
				{	
					$tree->add_item($rs["parent"], $rs);
					$has_items[$menu->id()] = 1;
				}
			}
		}

		$this->tree =& $tree;
		if (empty($this->use_parent))
		{
			$this->mk_home_folder_new();

			// shortcuts for the programs
			$this->sufix = "ad";
			$this->mk_admin_tree_new();
		};

		if (!isset($set_by_p))
		{
			$set_by_p = null;
		}
		$tree->set_rootnode($this->force_0_parent || (empty($this->use_parent) && $set_by_p) ? 0 : $rn);
	}

	/**
		@attrib name=gen_folders
		@param period optional
		@param parent optional
		@param curl optional
		@param selp optional
	**/
	function gen_folders($arr)
	{
		$t = get_instance("vcl/treeview");
		$this->use_parent = (int)$arr["parent"];
		$this->_o_tree(array(
			"prop" => array(
				"vcl_inst" => &$t
			),
			"request" => $arr
		));
		die($t->finalize_tree());
	}

	private function resolve_item_new($m)
	{
		enter_function("admin_folders::resolve_item_new");
		$arr = array("parent" => $m->parent());
		$arr["id"] = $m->id();
		if ($this->period > 0 && $m->prop("periodic") != 1)
		{
			exit_function("admin_folders::resolve_item_new");
			return false;
		};
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];

		$iconurl = "";
		if ($m->class_id() == CL_PROMO)
		{
			$iconurl = icons::get_icon_url("promo_box","");
		}
		else
		if ($m->class_id() == CL_BROTHER)
		{
			$iconurl = icons::get_icon_url("brother","");
		}
		else
		if ($m->prop("admin_feature") > 0)
		{
			$iconurl = icons::get_feature_icon_url($m->prop("admin_feature"));
		};

		if ($this->can("view", $m->meta("sel_icon")))
		{
			$im = get_instance(CL_IMAGE);
			$iconurl = $im->get_url_by_id($m->meta("sel_icon"));
		}

		// if all else fails ..
		$arr["iconurl"] = $iconurl;

		if ($m->prop("admin_feature"))
		{
			$prog = aw_ini_get("programs");
			$arr["url"] = $prog[$m->prop("admin_feature")]["url"];
		}
		else
		{
			$arr["url"] = aw_url_change_var("parent", $arr["id"], $this->curl);
		};
	
		if (empty($arr["url"]))
		{
			$arr["url"] = "about:blank";
		};
		$arr["name"] = parse_obj_name($m->trans_get_val("name"));
		if ($this->selp == $m->id())
		{
			$arr["name"] = "<b>".$arr["name"]."</b>";
		}
				
		// tshekime et kas menyyl on submenyysid
		// kui on, siis n2itame alati
		// kui pole, siis tshekime et kas n2idatakse perioodilisi dokke
		// kui n2idatakse ja menyy on perioodiline, siis n2itame menyyd
		// kui pole perioodiline siis ei n2ita
		$rv = true;
		
		/*if ($this->period > 0)
		{
			if (!$this->tree->node_has_children($arr["id"]) && ($arr["periodic"] == 0))
			{
				//$rv = false;
			};
		};*/
		exit_function("admin_folders::resolve_item_new");
		return $rv ? $arr : false;
	}

	private function mk_home_folder_new()
	{
		$us = get_instance(CL_USER);
		if (!$this->can("view", $us->get_current_user()))
		{
			return;
		}
		$cur_oid = $us->get_current_user();
		$ucfg = new object($cur_oid);
		if (!$this->can("view", $ucfg->prop("home_folder")) || !is_oid($ucfg->prop("home_folder")))
		{
			return;
		}
		$hf = new object($ucfg->prop("home_folder"));
		// add home folder
		$rn = empty($this->use_parent) ? $this->cfg["admin_rootmenu2"] : $this->use_parent;
		$this->tree->add_item(is_array($rn) ? reset($rn) : $rn,array(
			"id" => $hf->id(),
			"parent" => $this->force_0_parent ? 0 : (is_array($rn) ? reset($rn) : $rn),
			"name" => parse_obj_name($hf->trans_get_val("name")),
			"iconurl" => icons::get_icon_url("homefolder",""),
			"url" => aw_url_change_var("parent",$hf->id(), $this->curl),
		));
		$ol = new object_list(array(
			"class_id" => array(CL_MENU, CL_BROTHER, CL_GROUP),
			"parent" => $hf->id(),
			"CL_MENU.type" => new obj_predicate_not(array(MN_FORM_ELEMENT, MN_HOME_FOLDER)),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
						"lang_id" => aw_global_get("lang_id"),
						"CL_MENU.type" => array(MN_CLIENT, MN_ADMIN1)
				)
			)),
			"site_id" => array(),
			"sort_by" => "objects.parent,objects.jrk,objects.created"
		));
		foreach($ol->arr() as $menu)
		{
			$rs = $this->resolve_item_new($menu);
			if ($rs !== false)
			{	
				$this->tree->add_item($rs["parent"], $rs);
			}
		}

	}

	private function mk_admin_tree_new()
	{
		// make this one level only, so we save a lot on the headaches
		$ol = new object_list(array(
			"class_id" => CL_MENU,
			"parent" => aw_ini_get("amenustart"),
			"status" => STAT_ACTIVE,
			"CL_MENU.type" => MN_ADMIN1,
			"site_id" => array(),
			"lang_id" => array(),
			"sort_by" => "objects.parent,objects.jrk,objects.created"
		));
		$rn = empty($this->use_parent) ? $this->cfg["admin_rootmenu2"] : $this->use_parent;
		$rn = is_array($rn) ? reset($rn) : $rn;
		if ($this->force_0_parent)
		{
			$rn = 0;
		}
		$tmp = $this->period;
		$this->period = null;
		foreach($ol->arr() as $menu)
		{
			$rs = $this->resolve_item_new($menu);
			if ($rs !== false)
			{	
				$rs["id"] .= "ad";
				$rs["parent"] = $rn;
				$this->tree->add_item($rs["parent"], $rs);
			}
		}
		$this->period = $tmp;
	}

	private function setup_rf_table(&$t)
	{
		$t->define_field(array(
			"name" => "icon",
			"align" => "center",
			"chgbgcolor" => "cutcopied" ,
			"width" => "22"
		));

		$t->define_field(array(
			"name" => "name",
			"align" => "left",
			"talign" => "center",
			"sortable" => 1,
			"chgbgcolor" => "cutcopied",
			"caption" => t("Nimi")
		));
		
		$t->define_field(array(
			"name" => "jrk",
			"align" => "center",
			"width" => 10,
			"talign" => "center",
			"chgbgcolor" => "cutcopied",
			"caption" => t("Jrk"),
			"numeric" => "yea",
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Aktiivne"),
			"width" => 10,
			"align" => "center",
			"talign" => "center",
			"chgbgcolor" => "cutcopied",
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"width" => 50,
			"align" => "center",
			"talign" => "center",
			"sortable" => 1,
			"chgbgcolor" => "cutcopied",
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"width" => 100,
			"align" => "center",
			"talign" => "center",
			"type" => "time",
			"format" => "d-M-y / H:i",
			"sortable" => 1,
			"numeric" => 1,
			"chgbgcolor" => "cutcopied",
		));
		
		$t->define_field(array(
			"name" => "class_id",
			"caption" => t("T&uuml;&uuml;p"),
			"width" => 100,
			"align" => "center",
			"talign" => "center",
			"sortable" => 1,
			"chgbgcolor" => "cutcopied",
		));

		$t->define_field(array(
			"name" => "java",
			"caption" => t("Tegevus"),
			"width" => 30,
			"align" => "center",
			"talign" => "center",
			"chgbgcolor" => "cutcopied",
		));

		$t->define_chooser(array(
			"name" => "sel",
			"chgbgcolor" => "cutcopied",
			"field" => "oid"
		));
	}

	private function _o_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->setup_rf_table($t);


		get_instance("core/icons");
		aw_global_set("date","");

		$lang_id = aw_global_get("lang_id");
		$site_id = $this->cfg["site_id"];
		$parent = $arr["request"]["parent"];
		$parent = !empty($parent) ? $parent : $this->cfg["rootmenu"];
		if (!$this->can("view", $parent))
		{
//			return;
		}
		$menu_obj = new object($parent);

		if (!isset($period))
		{
			$period = null;
		}

		if ($menu_obj->is_brother())
		{
			$menu_obj = $menu_obj->get_original();
			$parent = $menu_obj->id();
		}

		$sel_objs = $this->get_cutcopied_objects();

		$la = get_instance("languages");
		$lar = $la->get_list();

		$ps = "";

		$current_period = aw_global_get("current_period");

		if (!$menu_obj->prop("all_pers"))
		{
			if (!empty($period))
			{
				$ps = " AND ((objects.period = '$period') OR (objects.class_id = ".CL_MENU." AND objects.periodic = 1)) ";
			}
			// if no period is set in the url, BUT the menu is periodic, then only show objects from the current period
			// this fucks shit up. basically, a periodic menu can have non-periodic submenus
			// in that case there really is no way of seeing them 
			else
			{
				$ps = " AND (period = 0 OR period IS NULL OR class_id IN (".CL_USER."))";
			};
		}

		// do not show relation objects in the list. hm, I wonder whether
		// I'll burn in hell for this --duke
		$cls = " AND objects.class_id != " . CL_RELATION;

		// would be nice if we would only query the fields we actually need, otherwise
		// we just spend a lot of memory on nothing when handling long object lists.
		// BUT doing this right now would break the custom object list thingie ... -- duke

		// by the way, mk_my_orb is pretty expensive and all those calls to it
		// here take up to 10% of the time used to create the page -- duke

		$sby = $sby2 = "";
		if (!empty($_GET["sortby"]))
		{
			if ($_GET["sortby"] == "hidden_jrk")
			{
				$sby = " ORDER BY jrk ".$_GET["sort_order"];
				$sby2 = " ORDER BY c,jrk ".$_GET["sort_order"];
			}
			else
			{
				$sby = " ORDER BY ".$_GET["sortby"]." ".$_GET["sort_order"];
				$sby2 = " ORDER BY c,".$_GET["sortby"]." ".$_GET["sort_order"];
			}
			$sortby = $_GET["sortby"];
			$GLOBALS["sort_order"] = $_GET["sort_order"];
		}

		$per_page = 100;

		$ft_page = isset($GLOBALS["ft_page"]) ? $GLOBALS["ft_page"] : null;
		$lim = "LIMIT ".($ft_page * $per_page).",".$per_page;

		$where = "objects.parent = '$parent' AND
				(lang_id = '$lang_id' OR m.type = ".MN_CLIENT." OR objects.class_id IN(".CL_PERIOD .",".CL_USER.",".CL_GROUP.",".CL_MSGBOARD_TOPIC.",".CL_LANGUAGE."))
				 AND
				status != 0
				$cls $ps ";

		$query = "FROM objects
				LEFT JOIN menu m ON m.id = objects.oid
			WHERE
				$where ";

/*		$filter = array(
			"parent" => $parent,
			new object_list_filter(array(
				"logic" => "OR",
				"non_filter_classes" => CL_MENU,
				"conditions" => array(
					"lang_id" => $lang_id,
					"class_id" => array(CL_PERIOD, CL_USER, CL_GROUP, CL_MSGBOARD_TOPIC),
					"type" => MN_CLIENT
				)
			))
		);
		$GLOBALS["DUKE"] = 1;
		$ob = new object_list($filter);
		die();*/

		// make pageselector.
		// total count
		$q = "SELECT count(*) as cnt $query $sby";
		$t->d_row_cnt = $this->db_fetch_field($q, "cnt");
		if ($t->d_row_cnt > $per_page)
		{
			$t->define_pageselector(array(
				"type" => "lb",
				"records_per_page" => $per_page,
				"d_row_cnt" => $t->d_row_cnt
			));
			$pageselector = $t->draw_lb_pageselector(array(
				"records_per_page" => $per_page
			));
		}

		$q = "SELECT objects.* , IF(class_id=".CL_MENU.",1,2) as c  $query $sby2 $lim";
		$this->db_query($q);

		// perhaps this should even be in the config file?
		//$containers = array(CL_MENU,CL_BROTHER,CL_PROMO,CL_GROUP,CL_MSGBOARD_TOPIC);
		$containers = get_container_classes();

		$num_records = 0;

		$this->set_parse_method("eval");
//		$this->read_template('js_popup_menu.tpl');
		$clss = aw_ini_get("classes");

		$trans = false;
		if (aw_ini_get("user_interface.full_content_trans"))
		{
			$trans = true;
		}

		while ($row = $this->db_next())
		{
			if (!$this->can("view", $row["oid"]))
			{
				continue;
			}
			$row_o = obj($row["oid"]);
			$can_change = $this->can("edit", $row["oid"]);
			$can_delete = $this->can("delete", $row["oid"]);
			$can_admin = $this->can("admin", $row["oid"]);
			$comment = strip_tags($row["comment"]);

			$row["is_menu"] = 0;
			if (in_array($row["class_id"],$containers))
			{
				$chlink = aw_url_change_var("parent", $row["oid"]);
				$row["is_menu"] = 1;
			}
			else
			{
				$grp = null;
				if ($trans && aw_global_get("ct_lang_id") != $row_o->lang_id())
				{
					$grp = "transl";
				}
				if ($can_change)
				{
					// make mp3 playable when clicked in admin
					if ($row["class_id"] == CL_MP3)
					{
						$chlink = "JavaScript: void(0)";
					}
					else
					{
						$chlink = $this->mk_my_orb("change", array("id" => $row["oid"], "period" => $period, "group" => $grp),$row["class_id"]);
					}
				}
				else
				{
					$chlink = $this->mk_my_orb("view", array("id" => $row["oid"], "period" => $period, "group" => $grp),$row["class_id"]);
				}
			}
			// make mp3 playable when clicked in admin
			if ($row["class_id"] == CL_MP3)
			{
				$s_play_url = str_replace("automatweb/","",$this->mk_my_orb("play", array("id" => $row["oid"]),"mp3", false,true,"/"))."/".str_replace("/","_","fail.mp3");
				$s_mp3_onclick = 'myRef = window.open("'.$s_play_url.'","AW MP3 M&auml;ngija","left="+((screen.width/2)-(350/2))+",top="+screen.height/5+",width=350,height=150,toolbar=0,resizable=0,location=0,directories=0,status=0,menubar=0,scrollbars=0")';
				$caption = parse_obj_name($row_o->trans_get_val("name"));
				$row["name"] = '<a href="'.$chlink.'" title="'.$comment.'" onClick=\''.$s_mp3_onclick.'\'>'.$caption."</a>";
			}
			else
			{
				$caption = parse_obj_name($row_o->trans_get_val("name"));
				$row["name"] = '<a href="'.$chlink.'" title="'.$comment.'">'.$caption."</a>";
			}

			if (isset($sel_objs[$row["oid"]]))
			{
				$row["cutcopied"] = "#E2E2DB";
			}
			else
			{
				$row["cutcopied"] = "#FCFCF4";
			}

			$row["lang_id"] = $lar[$row["lang_id"]];
			$row["java"] = $this->get_popup_data(array(
				"obj" => obj($row["oid"]),
				"period" => $period
			));
			$iu = icons::get_icon_url($row["class_id"],$row["name"]);
			$iconcomm = "Objekti id on ".$row["oid"];
			$row["icon"] = '<img alt="'.$iconcomm.'" title="'.$iconcomm.'" src="'.$iu.'">';

			$row["class_id"] = $clss[$row["class_id"]]["name"];

			if ($row["oid"] != $row["brother_of"])
			{
				$row["class_id"] .= " (vend)";
			}
						
			$row["hidden_jrk"] = $row["jrk"];
			if ($can_change)
			{
				$row["jrk"] = "<input type=\"hidden\" name=\"old[jrk][".$row["oid"]."]\" value=\"".$row["jrk"]."\"><input type=\"text\" name=\"new[jrk][".$row["oid"]."]\" value=\"".$row["jrk"]."\" class=\"formtext\" size=\"3\">";
			}

			$row["status_val"] = $row["status"];

			if ($can_change)
			{
				$row["status"] = "<input type=\"hidden\" name=\"old[status][".$row["oid"]."]\" value=\"".$row["status"]."\"><input type=\"checkbox\" name=\"new[status][".$row["oid"]."]\" value=\"2\" ".checked($row["status"] == 2).">";
			}
			else
			{
				$row["status"] = $row["status"] == 1 ? t("Mitteaktiivne") : t("Aktiivne");
			}

			if ($can_change)
			{
				$row["select"] = "<input type=\"checkbox\" name=\"sel[".$row["oid"]."]\" value=\"1\">";
			}
			else
			{
				$row["select"] = "&nbsp;";
			}

			$row["change"] = $can_change ? "<a href=\"$chlink\"><img src=\"".$this->cfg["baseurl"]."/automatweb/images/blue/obj_settings.gif\" border=\"0\"></a>" : "";
			
			$t->define_data($row);
			$num_records++;
		}

		$sortby = $_GET["sortby"];

		if($sortby == "status")
		{
			$sortby = "status_val";	
		}
		
		if (empty($sortby))
		{
			$sortby = "hidden_jrk";
		};

		if (isset($sortby) && $sortby == "jrk")
		{
			$sortby = "hidden_jrk";
		};

		if (empty($GLOBALS["sort_order"]))
		{
			$GLOBALS["sort_order"] = "asc";
		};

		$t->set_default_sortby(array("is_menu", "name"));
		$t->set_default_sorder("desc");
		
		$t->set_numeric_field("hidden_jrk");

		if($sortby == "name")
		{
			$t->sort_by(array(
				"field" => array("is_menu", "name"),
				"sorder" => array("is_menu" => "desc", $sortby => $GLOBALS["sort_order"]),
			));
		}
		else
		{
			// if document order is set from folder then use it
			if ($menu_obj->prop("doc_ord_apply_to_admin")==1 && !isset($_GET["sort_order"])  )
			{
				$a_sort_fields = new aw_array($menu_obj->meta("sort_fields"));
				$a_sort_order = new aw_array($menu_obj->meta("sort_order"));
				
				$a_fields = array("is_menu");
				foreach($a_sort_fields->get() as $key => $val)
				{
					$a_field = split  ( "\.", $val);
					$a_fields[] = $a_field[1];
				}
				
				$a_sorder = array("is_menu" => "desc");
				$i=1;
				foreach($a_sort_order->get() as $key => $val)
				{
					$a_sorder[$a_fields[$i]] = strtolower($val);
					$i++;	
				}
				
				$t->sort_by(array(
					"field" => $a_fields,
					"sorder" => $a_sorder
				));
			}
			else
			{
				$t->sort_by(array(
					"field" => array("is_menu", $sortby, "name"),
					"sorder" => array("is_menu" => "desc", $sortby => $GLOBALS["sort_order"],"name" => "asc")
				));
			}
		}
		$t->set_sortable(false);
	}

	function get_popup_data($args = array())
	{
		$obj = $args["obj"];
		$id = $obj->id();
		$parent = $obj->parent();
		$clid = $obj->class_id();
		$period = $args["period"];

		$pm = get_instance("vcl/popup_menu");
		$pm->begin_menu("aif_".$obj->id());

		$pm->add_item(array(
			"text" => t("Ava"),
			"link" => aw_url_change_var("parent", $id)
		));

		$grp = null;
		if (aw_ini_get("user_interface.full_content_trans")  && aw_global_get("ct_lang_id") != $obj->lang_id())
		{
			$grp = "transl";
		}

		if ($this->can("edit", $id))
		{
			$pm->add_item(array(
				"link" => $this->mk_my_orb("change", array(
					"id" => $id, 
					"parent" => $parent,
					"period" => $period,
					"return_url" => get_ru(),
					"group" => $grp
				), $clid,true,true),
				"text" => t("Muuda")
			));

			$pm->add_item(array(
				"link" => $this->mk_my_orb("if_cut", array(
					"reforb" => 1, 
					"id" => $id, 
					"parent" => $parent,
					"sel[$id]" => "1",
					"return_url" => get_ru()
				), "admin_if",true,true),
				"text" => t("L&otilde;ika")
			));
		}

		$pm->add_item(array(
			"link" => $this->mk_my_orb("if_copy", array("reforb" => 1, "id" => $id, "parent" => $parent,"sel[$id]" => "1","period" => $period), "admin_if",true,true),
			"text" => t("Kopeeri")
		));

		if ($this->can("delete", $id))
		{
			$delurl = $this->mk_my_orb("if_delete", array("ret_id" => $_GET["id"], "reforb" => 1, "id" => $id, "parent" => $parent,"sel[$id]" => "1","period" => $period), "admin_if",true,true);
			$delurl = "javascript:if(confirm('".t("Kustutada valitud objektid?")."')){window.location='$delurl';};";

			$pm->add_item(array(
				"link" => $delurl,
				"text" => t("Kustuta")
			));
		}

		return $pm->get_menu();
	}
	
	function generate_new($tb, $i_parent)
	{
		$atc = get_instance(CL_ADD_TREE_CONF);
		
		// although fast enough allready .. caching makes it 3 times as fast
		$c = get_instance("cache");
		$tree = $c->file_get("newbtn_tree_cache_".aw_global_get("uid"));
		$tree = unserialize($tree);
		
		if(!is_array($tree))
		{
			$tree = $atc->get_class_tree(array(
				"az" => 1,
				"docforms" => 1,
				// those are for docs menu only
				"parent" => "--pt--",
				"period" => "--pr--",
			));
			$c->file_set("newbtn_tree_cache_".aw_global_get("uid"), serialize($tree));
		}
		
		foreach($tree as $item_id => $item_collection)
		{
			foreach($item_collection as $el_id => $el_data)
			{
				$parnt = $item_id == "root" ? "new" : $item_id;
				
				if ($el_data["clid"])
				{
					$url = $this->mk_my_orb("new",array("parent" => $i_parent),$el_data["clid"]);
					$url = str_replace(aw_ini_get("baseurl")."/automatweb/orb.aw", "", $url);
					$tb->add_menu_item(array(
						"name" => $el_data["id"],
						"parent" => $parnt,
						"text" => $el_data["name"],
						//"url" => str_replace (aw_ini_get("baseurl"), "", $this->mk_my_orb("new",array("parent" => "--pt--"),$el_data["clid"])),
						"url" => $url,
					));
				}
				else
				if ($el_data["link"])
				{
					$url = str_replace(aw_ini_get("baseurl")."/automatweb/orb.aw", "", $el_data["link"]);
					//$url = str_replace("--pt--", $arr["parent"], $el_data["link"]);
					$url =  str_replace("--pt--", $i_parent, str_replace("--pr--", $arr["period"], $url));
					// docs menu has links ..
					$tb->add_menu_item(array(
						"name" => $el_data["id"],
						"parent" => $parnt,
						"text" => $el_data["name"],
						"url" => $url,
					));
				}
				else
				{
					$tb->add_sub_menu(array(
						"name" => $el_data["id"],
						"parent" => $parnt,
						"text" => $el_data["name"],
					));
					};
				if ($el_data["separator"])
				{
					$tb->add_menu_separator(array(
						"parent" => $parnt,
					));
				}
			};
		};
	}
	
	/**
		@attrib name=save_if
	**/
	function save_if($arr)
	{
		extract($arr);
		if (is_array($old))
		{
			foreach($old as $column => $coldat)
			{
				foreach($coldat as $oid => $oval)
				{
					$val = $new[$column][$oid];
					if ($column == "status" && $val == 0)
					{
						$val = 1;
					}
					if ($val != $oval)
					{
						if ($this->can("edit", $oid) && $this->can("view", $oid))
						{
							$o = obj($oid);
							if ($column == "jrk")
							{
								$o->set_ord((int)$val);
							}
							else
							{
								$o->set_prop($column, $val);
							}
							if($all_trans_status != 0 && $column == "status")
							{
								foreach($o->meta("translations") as $lid => $ldata)
								{
									$o->set_meta("trans_".$lid."_status", ($val - 1));
								}
							}
							$o->save();
						}
					}
				}
			}
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=if_cut all_args=1
	**/
	function if_cut($arr)
	{
		extract($arr);

		$cut_objects = array();
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$cut_objects[$oid] = $oid;
			}
		}

		aw_session_set("cut_objects",$cut_objects);

		if (!empty($arr['return_url']))
		{
			return $arr['return_url'];
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=if_copy all_args=1
	**/
	function if_copy($arr)
	{
		extract($arr);
		return $this->mk_my_orb("copy_feedback", array("parent" => $parent, "period" => $period, "sel" => $sel), "admin_menus");
	}

	/** pastes the cut objects 
		@attrib name=if_paste params=name default="0" all_args=1
	**/
	function if_paste($arr)
	{
		extract($arr);

		$cut_objects = aw_global_get("cut_objects");
		$copied_objects = aw_global_get("copied_objects");

		$cache = get_instance("cache");
		$langs = get_instance("languages");

		$clss = aw_ini_get("classes");
		if (is_array($cut_objects))
		{
			reset($cut_objects);
			while (list(,$oid) = each($cut_objects))
			{
				if ($oid != $parent)
				{
					// so, let the object update itself when it is being cut-pasted, if it so desires
					$o = obj($oid);
					if ($clss[$o->class_id()]["file"] != "")
					{
						$inst = $o->instance();
						if (method_exists($inst, "cut_hook"))
						{
							$inst->cut_hook(array(
								"oid" => $oid,
								"new_parent" => $parent
							));
						}
					}
					
					$o->set_parent($parent);
					if ($period)
					{
						$o->set_period($period);
					}

					$o->set_lang($langs->get_langid());
					$o->save();
				}
			}
		}
		aw_session_set("cut_objects",array());

		$conns = $obj_id_map = array();
		$msgs = array();
		if (is_array($copied_objects))
		{
			foreach($copied_objects as $oid => $xml)
			{
				$oid = object::from_xml($xml, $parent);
			}
		}

		aw_session_set("copied_objects",array());
		$_SESSION["cut_objects"] = false;
		$_SESSION["copied_objects"] = false;
		if (!empty($arr['return_url']))
		{
			return $arr['return_url'];
		}
		return $arr["post_ru"];
	}

	/**  
		@attrib name=if_delete params=name default="0" all_args=1
	**/
	function if_delete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			$ol = new object_list(array(
				"oid" => array_keys($sel),
				"site_id" => array(),
				"lang_id" => array()
			));
			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				if ($this->can("delete", $o->id()))
				{
					$o->delete();
				}
			}
		}
		if ($arr["post_ru"])
		{
			return $arr["post_ru"];
		}
		return $this->mk_my_orb("change", array(
			"id" => $arr["ret_id"],
			"parent" => $arr["parent"],
			"period" => $arr["period"],
			"group" => "o"
		));
	}

	/**
		@attrib name=redir
		@param parent optional
	**/
	function redir($arr)
	{
		if (!empty($_SESSION["cur_admin_if"]))
		{
			return html::get_change_url($_SESSION["cur_admin_if"], array("group" => "o", "parent" => isset($arr["parent"]) ? $arr["parent"] : ""));
		}
		$ol = new object_list(array(
			"class_id" => CL_ADMIN_IF,
			"lang_id" => array(),
			"site_id" => array()
		));
		if ($ol->count())
		{
			$o = $ol->begin();
		}
		else
		{
			$o = obj();
			$o->set_parent(aw_ini_get("amenustart"));
			$o->set_class_id(CL_ADMIN_IF);
			$o->set_name(t("Administreerimisliides"));
			aw_disable_acl();
			$o->save();
			aw_restore_acl();
		}

		$_SESSION["cur_admin_if"] = $o->id();
		return  html::get_change_url($o->id(), array("group" => "o", "parent" => isset($arr["parent"]) ? $arr["parent"] : null));
	}

	/** returns the admin if id
		@attrib api=1
	**/
	function find_admin_if_id()
	{
		if (!empty($_SESSION["cur_admin_if"]))
		{
			return $_SESSION["cur_admin_if"];
		}
		$ol = new object_list(array(
			"class_id" => CL_ADMIN_IF,
			"lang_id" => array(),
			"site_id" => array()
		));
		if ($ol->count())
		{
			$o = $ol->begin();
		}
		else
		{
			$o = obj();
			$o->set_parent(aw_ini_get("amenustart"));
			$o->set_class_id(CL_ADMIN_IF);
			$o->set_name(t("Administreerimisliides"));
			aw_disable_acl();
			$o->save();
			aw_restore_acl();
		}

		$_SESSION["cur_admin_if"] = $o->id();
		return $o->id();
	}

	function callback_mod_tab($arr)
	{
		if ($arr["request"]["integrated"] == 1 && $arr["id"] == "o")
		{
			return false;
		}
		if ($arr["request"]["group"] == "fu" && $arr["id"] == "fu")
		{
			return true;
		}
		if ($arr["id"] != "o")
		{
			return false;
		}
		return true;
	}

	function insert_texts(&$t)
	{
		$t->vars(array(
			"logout_text" => t("Logi v&auml;lja"),
			"location_text" => t("Asukoht:"),
			"footer_l1" => sprintf(t("AutomatWeb&reg; on Struktuur Meedia registreeritud kaubam&auml;rk. K&otilde;ik &otilde;igused kaitstud, &copy; 1999-%s."), date("Y")),
			"footer_l2" => t("Palun k&uuml;lasta meie kodulehek&uuml;lgi:"),
			"st" => t("Seaded")
		));
	}

	function _set_zip_upload($arr)
	{
		if (is_uploaded_file($_FILES["zip_upload"]["tmp_name"]))
		{
			$zip = $_FILES["zip_upload"]["tmp_name"];
			// unzip the damn thing
			if (extension_loaded("zip"))
			{
				$folder = aw_ini_get("server.tmpdir")."/".gen_uniq_id();
				mkdir($folder, 0777);
				$tn = $folder;
				$zip = zip_open($zip);
				while ($zip_entry = zip_read($zip)) 
				{
					zip_entry_open($zip, $zip_entry, "r");
					$fn = $folder."/".zip_entry_name($zip_entry);
					$files[] = $fn;
					$fc = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					$this->put_file(array(
						"file" => $fn,
						"content" => $fc
					));
				}
			}
			else
			{
				$zf = escapeshellarg($zip);
				$zip = aw_ini_get("server.unzip_path");
				$tn = aw_ini_get("server.tmpdir")."/".gen_uniq_id();
				mkdir($tn,0777);
				$cmd = $zip." -d $tn $zf";
				$op = shell_exec($cmd);


				$files = array();
				if ($dir = @opendir($tn)) 
				{
					while (($file = readdir($dir)) !== false) 
					{
						if (!($file == "." || $file == ".."))
						{
							$files[] = $tn."/".$file;
						}
					}  
					closedir($dir);
				}
			}

			foreach($files as $file)
			{
				$fuc = get_instance(CL_FILE_UPLOAD_CONFIG);
				if (!$fuc->can_upload_file(array("folder" => $arr["request"]["parent"], "file_name" => $file, "file_size" => filesize($file))))
				{
					continue;
				}
				$fi = get_instance(CL_FILE);
				$mt = get_instance("core/aw_mime_types");
				$rv = $fi->save_file(array(
					"name" => basename($file),
					"type" => $mt->type_for_file($file),
					"content" => file_get_contents($file),
					"parent" => $arr["request"]["parent"]
				));
				$s = sprintf(t("Leidsin faili %s, l&otilde;in AW objekti %s<br>\n"), basename($file), html::obj_change_url($rv));
				echo $s;
				$_SESSION["fu_tm_text"] .= $s;
				flush();
				@unlink($fp);
			}
			@rmdir($tn);
			echo "<script language=javascript>window.location='".$arr["request"]["post_ru"]."'</script>";
		}
	}

	function _get_uploader($arr)
	{
		if (!$arr["request"]["parent"])
		{
			$arr["request"]["parent"] = aw_ini_get("rootmenu");
		}
		$_SESSION["fu_parent"] = $arr["request"]["parent"];
		$this->read_template("flash_uploader.tpl");
		$this->lc_load("menuedit", "lc_menuedit");
		$this->vars(array(
			"uploadurl" => urlencode($this->mk_my_orb("handle_upload", array("parent" => $arr["request"]["parent"]))),
			"redir_to" =>  urlencode(get_ru()),
		));
		$arr["prop"]["value"] = $this->parse();
	}

	/**
		@attrib name=handle_upload
		@param parent required
	**/
	function handle_upload($arr)
	{
		if (!$arr["parent"])
		{
			$arr["parent"] = aw_ini_get("rootmenu");
		}
		if (is_uploaded_file($_FILES["Filedata"]["tmp_name"]))
		{
			$fuc = get_instance(CL_FILE_UPLOAD_CONFIG);
			if (!$fuc->can_upload_file(array("folder" => $arr["parent"], "file_name" => $_FILES["Filedata"]["name"], "file_size" => $_FILES["Filedata"]["size"])))
			{
				continue;
			}
			$fi = get_instance(CL_FILE);
			$mt = get_instance("core/aw_mime_types");
			$rv = $fi->save_file(array(
				"name" => $_FILES["Filedata"]["name"],
				"type" => $_FILES["Filedata"]["type"],
				"content" => file_get_contents($_FILES["Filedata"]["tmp_name"]),
				"parent" => $arr["parent"]
			));
			$s = sprintf(t("Leidsin faili %s, l&otilde;in AW objekti %s<br>\n"), $_FILES["Filedata"]["name"], html::obj_change_url($rv));
			$_SESSION["fu_tm_text"] .= $s;
		}
	}
}
?>
