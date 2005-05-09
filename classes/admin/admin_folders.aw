<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/admin_folders.aw,v 1.44 2005/05/09 14:58:15 kristo Exp $
class admin_folders extends aw_template
{
	function admin_folders()
	{
		$this->init("automatweb/menuedit");

		// FIXME: damn this is a mess
		$this->lc_load("menuedit","lc_menuedit");
		lc_site_load("menuedit",$this);
		lc_load("definition");
	}

	/**  
		
		@attrib name=folders params=name default="0"
		
		@param period optional
		
		@returns
		
		
		@comment

	**/
	function show_folders($arr)
	{
		extract($arr);
		$t = get_instance("languages");
		$sf = get_instance("aw_template");
		$sf->tpl_init("automatweb");
		$sf->read_template("index_folders.tpl");
		$sf->vars(array(
			"charset" => $t->get_charset(),
			"content" => $this->gen_folders($period)
		));

		echo ($sf->parse());
		global $awt;
		if (is_object($awt))
		{
			$sums = $awt->summaries();

			echo "<!--\n";
			while(list($k,$v) = each($sums))
			{
				print "$k = $v\n";
			};
			echo " querys = ".aw_global_get("qcount")." \n";
			echo "-->\n";
		}

		echo "<!--\n";
		echo "enter_function calls = ".$GLOBALS["enter_function_calls"]." \n";
		echo "exit_function calls = ".$GLOBALS["exit_function_calls"]." \n";
		echo "-->\n";

		die();
	}

	////
	// !Listib koik objektid
	function db_listall($parent = false)
	{
		enter_function("admin_folders::db_listall");
		$where = "objects.status != 0 AND ((menu.type != ".MN_FORM_ELEMENT . " AND menu.type != ".MN_HOME_FOLDER . ") OR menu.type IS NULL)";
		$aa = "";
		if ($this->cfg["lang_menus"] == 1)
		{
			$aa.="AND (objects.lang_id=".aw_global_get("lang_id")." OR menu.type = ".MN_CLIENT." OR menu.type = ".MN_ADMIN1.")";
		}
		if ($parent)
		{
			$where .= " AND objects.parent IN (" . join(",",$parent) . ")";
		};
		$q = "SELECT objects.oid as oid, 
				objects.parent as parent,
				objects.comment as comment,
				objects.name as name,
				objects.status as status,
				objects.jrk as jrk,
				objects.alias as alias,
				objects.class_id as class_id,
				objects.brother_of as brother_of,
				objects.periodic as periodic,
				objects.metadata as metadata,
				menu.type as mtype,
				menu.link as link,
				menu.icon_id as icon_id,
				menu.admin_feature as admin_feature
			FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE (objects.class_id = ".CL_MENU." OR objects.class_id = ".CL_BROTHER." OR objects.class_id = ".CL_GROUP.")  AND $where $aa
				ORDER BY objects.parent, jrk,objects.created";
		$this->db_query($q);
		exit_function("admin_folders::db_listall");
	}

	function gen_folders($period)
	{
		enter_function("admin_folders::gen_folders");
		$this->read_template("folders.tpl");

		$arr = array();
		$mpr = array();

		get_instance("core/icons");
	
		// x_mpr is used to store items for which we have no view access
		$this->x_mpr = array();

		$treeitems = array();

		$this->period = $period;

		global $awt;

		$this->tree = get_instance("vcl/treeview");

		$treetype = aw_ini_get("menuedit.treetype");

		$rn = empty($this->use_parent) ? $this->cfg["admin_rootmenu2"] : $this->use_parent;

		// FIXME: orders mk_my_orb to use empty arguments
		$this->use_empty = true;

		$this->tree->start_tree(array(
			"type" => !empty($treetype) ? constant($treetype) : TREE_JS,
			"url_target" => "list",
			"root_url" => $this->mk_my_orb("right_frame", array("parent" => $this->cfg["admin_rootmenu2"],"period" => $this->period),"admin_menus"),
			"root_name" => t("<b>AutomatWeb</b>"),
			"has_root" => empty($this->use_parent) ? true : false,
			"tree_id" => "ad_folders",
			//"persist_state" => true,
			"get_branch_func" => $this->mk_my_orb("gen_folders",array("period" => $this->period, "parent" => "0"),"workbench"),
		));

		$awt->start("menu-list");
		if ($this->tree->has_feature(LOAD_ON_DEMAND))
		{
			// siin tuleb teha 2-faasiline lähenemine
			$this->db_listall(array($rn,$this->cfg["amenustart"]));
			$other_parents = array();
			while ($row = $this->db_next())
			{
				// don't check acl for items we don't really care about
				if ($this->can("view",$row["oid"]) || $row["oid"] == $this->cfg["admin_rootmenu2"])
				{
					$arr[$row["parent"]][] = $row;
					$mpr[] = $row["parent"];
					if ($this->resolve_item(&$row))
					{
						$this->tree->add_item($row["parent"],$row);
					};
					$other_parents[] = $row["id"];
				}
				else
				{
					//$this->x_mpr[$row['oid']] = 1;
				}
			}

			if (sizeof($other_parents) > 0)
			{
				$this->db_listall($other_parents);
				while ($row = $this->db_next())
				{
					// don't check acl for items we don't really care about
					if ($this->can("view",$row["oid"]) || $row["oid"] == $this->cfg["admin_rootmenu2"])
					{
						$arr[$row["parent"]][] = $row;
						$mpr[] = $row["parent"];
						if ($this->resolve_item(&$row))
						{
							$this->tree->add_item($row["parent"],$row);
						};
					}
					else
					{
						//$this->x_mpr[$row['oid']] = 1;
					}
				}
			};
		}
		else
		{
			$awt->start("menu-map");
			$this->db_listall();
			while ($row = $this->db_next())
			{
				if ($this->can("view",$row["oid"]) || $row["oid"] == $this->cfg["admin_rootmenu2"])
				{
					$arr[$row["parent"]][] = $row;
					$mpr[] = $row["parent"];
					if ($this->resolve_item(&$row))
					{
						$this->tree->add_item($row["parent"],$row);
					};
				}
				else
				{
					$this->x_mpr[$row['oid']] = 1;
				}
			};
		}
		$awt->stop("menu-list");


		// list groups as well..
		if (empty($this->use_parent))
		{
			$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_GROUP." AND status != 0");
			while ($row = $this->db_next())
			{
				if ($this->can("view",$row["oid"]) || $row["oid"] == $this->cfg["admin_rootmenu2"])
				{
					$arr[$row["parent"]][] = $row;
					if ($this->resolve_item(&$row))
					{
						if ($this->can("view",$row["oid"]) || $row["oid"] == $this->cfg["admin_rootmenu2"])
						{
							$this->tree->add_item($row["parent"],$row);
						}
					};
					$mpr[] = $row["parent"];
				}
				else
				{
					$this->x_mpr[$row['oid']] = 1;
				}
			}
		};
		$awt->stop("menu-map");

		// here we will make the parent of all objects that don't have parents in the tree,
		// but have them in the excluded list, to be the root
		// why is this? well, because then the folders that are somewhere deep in the tree and that the user
		// has can_view acces for, but not their parent folders, can still see them

		foreach($arr as $prnt => $data)
		{
			foreach($data as $d_idx => $d_row)
			{
				if (isset($this->x_mpr[$d_row["parent"]]))
				{
					$d_row["parent"] = $this->cfg["admin_rootmenu2"];
					$arr[$this->cfg["admin_rootmenu2"]][] = $d_row;
					if ($this->resolve_item(&$d_row))
					{
						if ($this->can("view", $d_row["oid"]))
						{
							$this->tree->add_item($this->cfg["admin_rootmenu2"],$d_row);
						}
					};
				}
			}
		}

		$this->_x_shown[$this->cfg["admin_rootmenu2"]] = true;

		// kodukataloom

		if (empty($this->use_parent))
		{
			$awt->start("home-folder");
			$tr.=$this->mk_homefolder(&$arr);
			$awt->stop("home-folder");


			// shortcuts for the programs
			$this->sufix = "ad";
			$awt->start("admin-folder");
			$tr.= $this->rec_admin_tree(&$arr, $this->cfg["amenustart"]);
			$awt->stop("admin-folder");
		};

		$awt->start("tree-gen");
		$res = $this->tree->finalize_tree(array("rootnode" => $rn));
		$awt->stop("tree-gen");

		if (!empty($this->use_parent))
		{
			print $res;
			exit;
		};

		$t = get_instance("languages");
		$this->vars(array(
			"TREE" => $res,
			"uid" => aw_global_get("uid"),
			"date" => $this->time2date(time(),2),
			"charset" => $t->get_charset(),

		));

		// perioodide tropp.
		// temp workaround
		$tb = get_instance("vcl/toolbar");
		if ($this->cfg["per_oid"])
		{
			$per_oid = $this->cfg["per_oid"];
			$dbp = get_instance(CL_PERIOD, $per_oid);
			$act_per_id = $dbp->get_active_period();
			//$dbp->clist(-1);
			$pl = array();
			$actrec = 0;
			$rc = 0;
			$period_list = new object_list(array(
				"class_id" => CL_PERIOD,
				"sort_by" => "objects.jrk DESC",
			));

			for ($period_obj = $period_list->begin(); !$period_list->end(); $period_obj = $period_list->next())
			// loeme k6ik perioodid sisse
			//while ($row = $dbp->db_next())
			{
				$rc++;
				if ($period_obj->prop("per_id") == $act_per_id)
				{
					$actrec = $rc;
				};
				$pl[$rc] = array(
					"id" => $period_obj->prop("per_id"),
					"name" => $period_obj->name(),
				);
			}
			// leiame praegune +-3
			$ar = array();
			for ($i=$actrec-6; $i <= ($actrec+6); $i++)
			{
				if (isset($pl[$i]))
				{
					if ($pl[$i]["id"] == $act_per_id)
					{
						$ar[$pl[$i]["id"]] = $pl[$i]["name"].MN_ACTIVE;
					}
					else
					{
						$ar[$pl[$i]["id"]] = $pl[$i]["name"];
					}
				}
			}
			$ar[0] = t("Mitteperioodilised");
			$tb->add_cdata(html::select(array(
				"name" => "period",
				"options" => $ar,
				"selected" => !empty($this->period) ? $this->period : 0,
			)));
		}

		$tb->add_button(array(
			"name" => "refresh",
			"tooltip" => t("Reload"),
			"url" => "javascript:document.pform.submit();",
			"img" => "refresh.gif",
		));
		$tb->add_button(array(
			"name" => "logout",
			"tooltip" => t("Logi v&auml;lja"),
			"url" => $this->mk_my_orb("logout", array(), "users"),
			"img" => "logout.gif",
			"target" => "_top"
		));
		$tb->add_cdata($this->mk_reforb("folders",array("no_reforb" => 1)));
		$this->vars(array(
			"toolbar" => $tb->get_toolbar(array("no_target" => true)),
		));
		$this->vars(array(
			"has_toolbar" => $this->parse("has_toolbar"),
		));
		exit_function("admin_folders::gen_folders");
		return $this->parse();
	}

	// resolves icons, link .. and whatelse for a single menu item
	// I'll do icons first	
	function resolve_item(&$arr)
	{
		enter_function("admin_folders::resolve_item");
		$arr["id"] = $arr["oid"];
		if ($this->period > 0 && $arr["periodic"] != 1)
		{
			return false;
		};
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];

		if ($arr["class_id"] == CL_PROMO)
		{
			$iconurl = icons::get_icon_url("promo_box","");
		}
		else
		if ($arr["class_id"] == CL_BROTHER)
		{
			$iconurl = icons::get_icon_url("brother","");
		}
		else
		if ($arr["admin_feature"] > 0)
		{
			$iconurl = icons::get_feature_icon_url($arr["admin_feature"]);
		};

		if (!is_array($arr["metadata"]))
		{
			$arr["metadata"] = aw_unserialize($arr["metadata"]);
		}
		
		if (is_oid($arr["metadata"]["sel_icon"]) && $this->can("view", $arr["metadata"]["sel_icon"]))
		{
			$im = get_instance(CL_IMAGE);
			$iconurl = $im->get_url_by_id($arr["metadata"]["sel_icon"]);
		}

		// if all else fails ..
		$arr["iconurl"] = $iconurl;

		if (empty($arr["linkxxx"]))
		{
			$prog = aw_ini_get("programs");
			if ($arr["admin_feature"])
			{
				$arr["url"] = $prog[$arr["admin_feature"]]["url"];
			}
			else
			{
				$arr["url"] = $this->mk_my_orb("right_frame",array("parent" => $arr["id"], "period" => $this->period),"admin_menus");
			};
		}
		else
		{
			$arr["url"] = $arr["link"];
		};

		if (empty($arr["url"]))
		{
			$arr["url"] = "about:blank";
		};
		$arr["name"] = parse_obj_name($arr["name"]);
				
		// tshekime et kas menyyl on submenyysid
		// kui on, siis n2itame alati
		// kui pole, siis tshekime et kas n2idatakse perioodilisi dokke
		// kui n2idatakse ja menyy on perioodiline, siis n2itame menyyd
		// kui pole perioodiline siis ei n2ita
		$rv = true;

		if ($this->period > 0)
		{
			if (!$this->tree->node_has_children($arr["id"]) && ($arr["periodic"] == 0))
			{
				$rv = false;
			};
		};
		exit_function("admin_folders::resolve_item");
		return $rv;
	}

	////
	// !Loob kasutaja kodukataloogi
	function mk_homefolder(&$arr)
	{
		enter_function("admin_folders::mk_homefolder");
		$uid = aw_global_get("uid");
		$admin_rootmenu2 = $this->cfg["admin_rootmenu2"];
		$ext = $this->cfg["ext"];
		$baseurl = $this->cfg["baseurl"];

		$us = get_instance(CL_USER);
		if (!$this->can("view", $us->get_current_user()))
		{
			return;
		}
		$ucfg = new object($us->get_current_user());
		if (!$this->can("view", $ucfg->prop("home_folder")))
		{
			exit_function("admin_folders::mk_homefolder");
			return;
		}
		$hf = new object($ucfg->prop("home_folder"));

		$ret = $this->rec_tree($arr, $hf->id(),0);

		$this->tree->add_item($admin_rootmenu2, array(
			"id" => $hf->id(),
			"name" => $hf->name(),
			"parent" => $admin_rootmenu2,
			"iconurl" => icons::get_icon_url("homefolder",""),
			"url" => $this->mk_my_orb("right_frame",array("parent" => $hf->id()),"admin_menus"),
		));
		exit_function("admin_folders::mk_homefolder");
	}

	function rec_admin_tree(&$arr,$parent)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}
		enter_function("admin_folders::rec_admin_tree");

		$admin_rootmenu2 = $this->cfg["admin_rootmenu2"];
		$ext = $this->cfg["ext"];
		$baseurl = $this->cfg["baseurl"];

		$prog = aw_ini_get("programs");

		reset($arr[$parent]);
		$ret = "";
		while (list(,$row) = each($arr[$parent]))
		{
			if (!$this->can("view", $row["oid"]))
			{
				continue;
			}

			if ($row["status"] != STAT_ACTIVE)
			{
				continue;
			}
			

			// ugly-ass hack, but can't really think of anything else right now
			if ($row["admin_feature"] == PRG_GROUPS && aw_ini_get("groups.tree_root"))
			{
				$row["link"] = $this->mk_my_orb("right_frame", array("parent" => aw_ini_get("groups.tree_root")), "admin_menus");
			}
			if ($row["admin_feature"] == PRG_USERS && aw_ini_get("users.root_folder"))
			{
				$row["link"] = $this->mk_my_orb("right_frame", array("parent" => aw_ini_get("users.root_folder")), "admin_menus");
			}
			
			// ignore programs with no program url
			if (empty($row["link"]) && $row["admin_feature"] && empty($prog[$row["admin_feature"]]["url"]))
			{
				continue;
			};
			

			$this->rec_admin_tree(&$arr,$row["oid"]);

			$row_o = obj($row["oid"]);
			if (is_oid($row_o->meta("sel_icon")) && $this->can("view", $row_o->meta("sel_icon")))
			{
				$im = get_instance(CL_IMAGE);
				$iconurl = $im->get_url_by_id($row_o->meta("sel_icon"));
			}
			else
			{
				$iconurl = $row["admin_feature"] ? icons::get_feature_icon_url($row["admin_feature"]) : "";
			}

			// as far as I know, this works everywhere
			$blank = "about:blank";

			$px = ($row["parent"] == $this->cfg["amenustart"] ? $admin_rootmenu2 : $row["parent"] . $this->sufix);
			$id = $row["oid"] . $this->sufix;

			$this->tree->add_item($px,array(
				"id" => $id,
				"name" => $row["name"],
				"parent" => $px,
				"iconurl" => $iconurl,
				"url" => !empty($row["link"]) ? $row["link"] : ($row["admin_feature"] ? $prog[$row["admin_feature"]]["url"]: $blank),
			));
		}
		exit_function("admin_folders::rec_admin_tree");
		return $ret;
	}

	function rec_tree(&$arr,$parent,$period)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

		if ($this->_x_rt[$parent])
		{
			return "";
		}
		enter_function("admin_folders::rec_tree");
		$this->_x_rt[$parent] = true;

		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];

		$ret = "";
		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			if (!isset($row["mtype"]) || $row["mtype"] != MN_HOME_FOLDER)
			{
				// tshekime et kas menyyl on submenyysid
				// kui on, siis n2itame alati
				// kui pole, siis tshekime et kas n2idatakse perioodilisi dokke
				// kui n2idatakse ja menyy on perioodiline, siis n2itame menyyd
				// kui pole perioodiline siis ei n2ita
				$sub = $this->rec_tree(&$arr,$row["oid"],$period);
				$show = true;
				if ($sub == "" && $period > 0 && $row["periodic"] != 1) 
				{
					$show = false;
				}

				if ($show && !$this->_x_shown[$row["oid"]])
				{
					if ($row["class_id"] == CL_PROMO)
					{
						$url = icons::get_icon_url("promo_box","");
					}
					else
					if ($row["class_id"] == CL_BROTHER)
					{
						$url = icons::get_icon_url("brother","");
					}
					$this->vars(array(
						"name" => $row["name"],
						"id" => $row["oid"],
						"parent" => $row["parent"],
						"iconurl" => $url,
						"url" => $this->mk_my_orb("right_frame",array("parent" => $row["oid"], "period" => $period),"admin_menus"),
					));
					if ($sub == "")
					{
						$ret.=$this->parse("DOC");
					}
					else
					{
						$ret.=$this->parse("TREE").$sub;
					}
					$this->_x_shown[$row["oid"]] = true;
				}
			}
		}
		exit_function("admin_folders::rec_tree");
		return $ret;
	}

}
?>
