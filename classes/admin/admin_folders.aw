<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/admin_folders.aw,v 1.13 2003/09/25 12:35:31 duke Exp $
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
		die($sf->parse());
	}

	////
	// !Listib koik objektid
	function db_listall($where = " objects.status != 0",$ignore = false,$ignore_lang = false)
	{
		$aa = "";
		if (!$ignore)
		{
			// loeme sisse koik objektid
			$aa = "AND ((objects.site_id = '".aw_ini_get("site_id")."') OR (objects.site_id IS NULL))";
		};
		if ($this->cfg["lang_menus"] == 1 && $ignore_lang == false)
		{
			$aa.="AND (objects.lang_id=".aw_global_get("lang_id")." OR menu.type = ".MN_CLIENT.")";
		}
		$q = "SELECT objects.oid as oid, 
				objects.parent as parent,
				objects.comment as comment,
				objects.name as name,
				objects.created as created,
				objects.createdby as createdby,
				objects.modified as modified,
				objects.modifiedby as modifiedby,
				objects.last as last,
				objects.status as status,
				objects.jrk as jrk,
				objects.alias as alias,
				objects.class_id as class_id,
				objects.brother_of as brother_of,
				objects.metadata as metadata,
				objects.periodic as periodic,
				menu.type as mtype,
				menu.link as link,
				menu.clickable as clickable,
				menu.target as target,
				menu.ndocs as ndocs,
				menu.img_id as img_id,
				menu.img_url as img_url,
				menu.hide_noact as hide_noact,
				menu.mid as mid,
				menu.sss as sss,
				menu.links as links,
				menu.icon_id as icon_id,
				menu.admin_feature as admin_feature,
				menu.periodic as mperiodic
			FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")  AND $where $aa
				ORDER BY objects.parent, jrk,objects.created";
		$this->db_query($q);
	}

	// I need some other and better way to build the tree
	function gen_folders($period)
	{

		if (aw_ini_get("menuedit.folders_v2"))
		{
			$this->read_template("folders_v2.tpl");
		}
		else
		{
			$this->read_template("folders.tpl");
		};

		$arr = array();
		$mpr = array();

		$this->treeitems = array();

		get_instance("icons");
	
		// x_mpr is used to store items for which we have no view access
		$this->x_mpr = array();
		$this->listacl("objects.status != 0 AND objects.class_id = ".CL_PSEUDO);
		// listib koik menyyd ja paigutab need arraysse
		$this->db_listall("objects.status != 0 AND menu.type != ".MN_FORM_ELEMENT,true);
		while ($row = $this->db_next())
		{
			$row["name"] = parse_obj_name($row["name"]);
			if ($this->can("view",$row["oid"]) || $row["oid"] == $this->cfg["admin_rootmenu2"])
			{
				$arr[$row["parent"]][] = $row;
				$mpr[] = $row["parent"];
			}
			else
			{
				$this->x_mpr[$row['oid']] = 1;
			}
		}

		// list groups as well..
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_GROUP." AND status != 0");
		while ($row = $this->db_next())
		{
			$row["name"] = parse_obj_name($row["name"]);
			if ($this->can("view",$row["oid"]) || $row["oid"] == $this->cfg["admin_rootmenu2"])
			{
				$arr[$row["parent"]][] = $row;
				$mpr[] = $row["parent"];
			}
			else
			{
				$this->x_mpr[$row['oid']] = 1;
			}
		}

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
				}
			}
		}

		// and, of course, the drawing function has to be recursive


		// objektipuu
		$tr = $this->rec_tree(&$arr, $this->cfg["admin_rootmenu2"],$period);

		// kodukataloom

		$tr.=$this->mk_homefolder(&$arr);

		// shortcuts for the programs
		$tr.= $this->rec_admin_tree(&$arr, $this->cfg["amenustart"]);

		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $this->cfg["admin_rootmenu2"],
			"uid" => aw_global_get("uid"),
			"date" => $this->time2date(time(),2),
			"rooturl" => $this->mk_my_orb("right_frame", array("parent" => $this->cfg["admin_rootmenu2"]),"admin_menus"),
		));

		// perioodide tropp.
		if ($this->cfg["per_oid"])
		{
			// temp workaround
			$tb = get_instance("toolbar");
			$dbp = get_instance("period",$this->cfg["per_oid"]);
			$act_per_id = $dbp->get_active_period();
			$dbp->clist();
			$pl = array();
			$actrec = 0;
			$rc = 0;
			// loeme k6ik perioodid sisse
			while ($row = $dbp->db_next())
			{
				$rc++;
				if ($row["id"] == $act_per_id)
				{
					$actrec = $rc;
				};
				$pl[$rc] = $row;
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
			$ar[0] = "Mitteperioodilised";
			$this->vars(array(
				"periods" => str_replace("\n","",$this->picker($period,$ar))
			));
			$tb->add_cdata(html::select(array(
				"name" => "period",
				"options" => $ar,
				"selected" => isset($period) ? $period : 0,
			)));
			$tb->add_button(array(
				"name" => "refresh",
				"tooltip" => "Reload",
				"url" => "javascript:document.pform.submit();",
				"imgover" => "refresh_over.gif",
				"img" => "refresh.gif",
			));
			$tb->add_cdata($this->mk_reforb("folders",array("no_reforb" => 1)));
			$this->vars(array(
				"toolbar" => $tb->get_toolbar(),
			));
			$this->vars(array(
				"has_toolbar" => $this->parse("has_toolbar"),
			));
		}
		return $this->parse();
	}

	////
	// !Loob kasutaja kodukataloogi
	function mk_homefolder(&$arr)
	{
		$udata = $this->get_user();
		$uid = aw_global_get("uid");
		$admin_rootmenu2 = $this->cfg["admin_rootmenu2"];
		$ext = $this->cfg["ext"];
		$baseurl = $this->cfg["baseurl"];

		$hf = new object($udata["home_folder"]);

		// if the home directory does not exist, then raise an error .. but
		// if we try to load an non-existing object, then the object class
		// will yell anyway
	
			// $this->raise_error(ERR_MNEDIT_NOFOLDER,sprintf(MN_E_NO_HOME_FOLDER,$uid),true);
	
		$ret = $this->rec_tree($arr, $hf->id(),0);

		$this->vars(array(
			"name" => $hf->name(),
			"id" => $hf->id(), 
			"parent" => $admin_rootmenu2,
			"iconurl" => icons::get_icon_url("homefolder",""),
			"url" => $this->mk_my_orb("right_frame",array("parent" => $hf->id()),"admin_menus")
		));

		$hft = $this->parse("TREE");
		$ret = $hft.$ret;

		return $ret;
	}


	function rec_admin_tree(&$arr,$parent)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

		$admin_rootmenu2 = $this->cfg["admin_rootmenu2"];
		$ext = $this->cfg["ext"];
		$baseurl = $this->cfg["baseurl"];

		reset($arr[$parent]);
		$ret = "";
		while (list(,$row) = each($arr[$parent]))
		{
			if ($row["status"] != STAT_ACTIVE)
			{
				continue;
			}

			if ($row["admin_feature"] && !$this->prog_acl("view", $row["admin_feature"]) && ($this->cfg["acl"]["check_prog"]))
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
			if (empty($row["link"]) && $row["admin_feature"] && empty($this->cfg["programs"][$row["admin_feature"]]["url"]))
			{
				continue;
			};

			$sub = $this->rec_admin_tree(&$arr,$row["oid"]);

			if ($row["admin_feature"])
			{
				$sub.=$this->get_feature_tree($row["admin_feature"],$row["oid"]);
			}

			$iconurl = !empty($row["icon_id"]) ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : ($row["admin_feature"] ? $this->get_feature_icon_url($row["admin_feature"]) : "");
			// as far as I know, this works everywhere
			$blank = "about:blank";
			$this->vars(array(
				"name"		=> $row["name"],
				"id"			=> ($row["admin_feature"] == 4 ? "gp_" : "").$row["oid"], 
				"parent"	=> ($parent == $this->cfg["amenustart"] ? $admin_rootmenu2 : $row["parent"]),
				"iconurl" =>  $iconurl,
				"url" => !empty($row["link"]) ? $row["link"] : ($row["admin_feature"] ? $this->cfg["programs"][$row["admin_feature"]]["url"]: $blank)));

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

	function get_feature_tree($feat,$parent)
	{
		switch($feat)
		{
			// grupid
			case 4:
				return $this->mk_grp_tree($parent);
		}
	}

	function mk_grp_tree($parent)
	{
		$t = get_instance("groups");
		$t->listgroups("parent","asc",0,2);
		$grar = array();
		while ($row = $t->db_next())
		{
			$grar[$row["gid"]] = $row;
		}

		reset($grar);
		while (list($gid,$row) = each($grar))
		{
			// we must convert the parent member so that it actually points to
			// the parent OBJECT not the parent group
			$puta = isset($row["parent"]) ? $row["parent"] : 0;
			$row["parent"] = isset($grar[$puta]["oid"]) ? $grar[$puta]["oid"] : 0;

			if ($row["parent"] == 0)
			{
				$row["parent"] = $parent;
			}
			$grpcache[$row["parent"]][] = $row;
		}
		$ret = $this->rec_grp_tree(&$grpcache,$parent);
		return $ret;
	}

	function rec_grp_tree(&$arr,$parent)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

		$ret = "";
		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			if (!$this->can("view",$row["oid"]) /*|| $row["gid"] == aw_ini_get("groups.all_users_grp")*/)
			{
				continue;
			}

			$sub = $this->rec_grp_tree(&$arr,$row["oid"]);
			$this->vars(array(
				"name" => $row["name"],"id" => "gp_".$row["oid"], "parent" => "gp_".$row["parent"],
				"iconurl" => $this->cfg["baseurl"]."/automatweb/images/ftv2doc.gif",
				"url" => $this->mk_my_orb("right_frame",array("parent" => $row["oid"]),"admin_menus")
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

	function rec_tree(&$arr,$parent,$period)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

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

				if ($show)
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
					else
					{
						$url = $row["icon_id"] > 0 ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : "";
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
				}
			}
		}
		return $ret;
	}

	function get_feature_icon_url($fid)
	{
		return aw_ini_get("icons.server")."/prog_".$fid.".gif";
	}
}
?>
