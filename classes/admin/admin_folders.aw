<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/admin_folders.aw,v 1.1 2003/04/21 07:59:31 duke Exp $
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
				menu.periodic as mperiodic,
				menu.is_shop as is_shop,
				menu.shop_id as shop_id
			FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")  AND $where $aa
				ORDER BY objects.parent, jrk,objects.created";
		$this->db_query($q);
	}

	// well, mul on vaja kuvada see asi popupi sees, niisiis tegin ma miniversiooni folders.tpl-ist
	// ja lisasin siia uue parameetri
	// no, that $popup is really not needed anymore anylonger
	function gen_folders($period,$popup = 0)
	{
		if ($this->cfg["site_id"] == 88)
		{
			$this->read_template("folders_no_periods.tpl");
		}
		else
		{
			$this->read_template("folders.tpl");
		};

		$arr = array();
		$mpr = array();
		get_instance("icons");

		$this->x_mpr = array();
		$this->listacl("objects.status != 0 AND objects.class_id = ".CL_PSEUDO);
		// listib koik menyyd ja paigutab need arraysse
		$this->db_listall("objects.status != 0 AND menu.type != ".MN_FORM_ELEMENT,true);
		while ($row = $this->db_next())
		{
			$row["name"] = str_replace("\"","&quot;", $row["name"]);
			if ($this->can("view",$row["oid"]) || 
			    $row["oid"] == $this->cfg["admin_rootmenu2"]
			)
			{
				$arr[$row["parent"]][] = $row;
				$mpr[] = $row["parent"];
			}
			else
			{
				$this->x_mpr[$row['oid']] = $row;
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

		// objektipuu
		$tr = $this->rec_tree(&$arr, $this->cfg["admin_rootmenu2"],$period);

		// kodukataloom
		$tr.=$this->mk_homefolder(&$arr);

		// the whole she-bang
		$arr = array();
		$this->db_listall("objects.status = 2 AND menu.type = ".MN_ADMIN1,true,true);
		while ($row = $this->db_next())
		{
			$arr[$row["parent"]][] = $row;
		}
		$tr.= $this->rec_admin_tree(&$arr, $this->cfg["amenustart"]);

		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $this->cfg["admin_rootmenu2"],
			"uid" => aw_global_get("uid"),
			"date" => $this->time2date(time(),2)
		));

		// perioodide tropp.
		if ($this->cfg["per_oid"])
		{
			$dbp = get_instance("periods",$this->cfg["per_oid"]);
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
						$ar[$pl[$i]["id"]] = $pl[$i]["description"].MN_ACTIVE;
					}
					else
					{
						$ar[$pl[$i]["id"]] = $pl[$i]["description"];
					}
				}
			}
			$ar[0] = MN_PERIODIC;
			$this->vars(array(
				"periods" => str_replace("\n","",$this->picker($period,$ar))
			));
		}
		$this->vars(array(
			"rooturl" => $this->mk_my_orb("right_frame", array("parent" => $this->cfg["admin_rootmenu2"]),"admin_menus")
		));
		return $this->parse();
	}

	function rec_homefolder(&$arr,$parent)
	{
		if (!is_array($arr[$parent]))
		{
			return "";
		}

		$baseurl = $this->cfg["baseurl"];
		$ret = "";
		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			$sub = $this->rec_tree(&$arr,$row["oid"],0);
			$this->vars(array(
				"name" => $row["name"],
				"id" => $row["oid"],
				"parent" => $row["parent"],
				"iconurl" => $row["icon_id"] != "" ? $baseurl."/automatweb/icon.".$this->cfg["ext"]."?id=".$row["icon_id"] : $baseurl."/automatweb/images/ftv2doc.gif",
				"url" => $this->mk_my_orb("right_frame",array("parent" => $row["oid"]),"admin_menus"),
				//"url" => "javascript:go_go(".$row["oid"].",'')",
			));
			$this->homefolders[$row["oid"]] = $row["oid"];
			if ($sub == "")
			{
				$ret.=$this->parse("DOC");
			}
			else
			{
				$ret.=$this->parse("TREE").$sub;
			};
		}
		return $ret;
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

		// k6igepealt loeme k6ik kodukatalooma all olevad menyyd
		$this->db_query("SELECT menu.*,objects.* FROM menu
					LEFT JOIN objects ON objects.oid = menu.id
					WHERE oid = ".$udata["home_folder"]);
		if (!($hf = $this->db_next()))
		{
			$this->raise_error(ERR_MNEDIT_NOFOLDER,sprintf(MN_E_NO_HOME_FOLDER,$uid),true);
		};
		
		// when we create the home folders we write down which ones are shown
		// so we won't show them again under shared folders
		$this->homefolders = array();

		$ret = $this->rec_homefolder($arr, $hf["oid"]);

		$this->vars(array(
			"name" => $hf["name"],
			"id" => $hf["oid"], 
			"parent" => $admin_rootmenu2,
			"iconurl" => icons::get_icon_url("homefolder",""),
			"url" => $this->mk_my_orb("right_frame",array("parent" => $hf["oid"]),"admin_menus")
			//"url" => "javascript:go_go(".$hf["oid"].",'')",
		));
		$hft = $this->parse("TREE");

		// now we need to make a list of all the shared folders of all the users.
		// we do that by simply scanning the array of all folders for visible menus with type MN_HOME_FOLDER_SUB
		// that should work, because if acl is checked, then only folders that are shared to this user will be visible
		// and we exclude the users own home folder menus cause they would be duplicated there otherwise
		$shared_arr = $this->get_shared_arr(&$arr,$this->homefolders);
		$shares = "";
		reset($shared_arr);
		while (list(,$v) = each($shared_arr))
		{
			$this->vars(array(
				"name"	=> $v["name"],
				"id"	=> $v["oid"],		
				"parent"=> SHARED_FOLDER_ID,
				"iconurl" => $row["icon_id"] ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : $baseurl."/automatweb/images/ftv2doc.gif",
				"url"	=> $this->mk_my_orb("right_frame", array("parent" => $v["oid"]),"admin_menus"),
				//"url" => "javascript:go_go(".$v["oid"].",'')",
			));
			$shares.=$this->parse("DOC");
		}

		$this->vars(array(
			"name"=> "SHARED FOLDERS",
			"id" => SHARED_FOLDER_ID,		
			"parent" => $hf["oid"],
			"iconurl" => icons::get_icon_url("shared_folders",""),
			"url" => $this->mk_my_orb("right_frame",array("parent" => SHARED_FOLDER_ID),"admin_menus"),
			//"url" => "javascript:go_go(".SHARED_FOLDER_ID.",'')",
		));
		if ($shares != "")
		{
			$shfs = $this->parse("TREE");
		}
		else
		{
			$shfs = $this->parse("DOC");
		};

		// now we need to make a list of all the groups created by this user
		$dbu = get_instance("users_user");
		$dbu->listgroups(-1,-1,4);
		$grps_arr = array();
		while ($row = $dbu->db_next())
		{
			$row["oid"] = $row["gid"];
			$grps_arr[$row["parent"]][] = $row;
		}
		$dgid = $dbu->get_gid_by_uid($uid);
		$grptree = $this->rec_tree_grps(&$grps_arr, $dgid);

		$this->vars(array(
			"name"		=> "GROUPS",
			"id"			=> "gr_".$dgid,
			"parent"	=> $hf["oid"],
			"iconurl" => icons::get_icon_url("hf_groups",""),
			"url"			=> $this->mk_orb("mk_grpframe",array("parent" => $dgid),"groups")
		));
		if ($grptree != "")
		{
			$grps = $this->parse("TREE");
		}
		else
		{
			$grps = $this->parse("DOC");
		}

		$ret = $hft.$shfs.$shares.$grps.$grptree.$ret;

		return $ret;
	}


	function get_shared_arr(&$arr,$exclude)
	{
		$ret = array();

		reset($arr);
		while (list($parent, $v) = each($arr))
		{
			reset($v);
			while (list(,$row) = each($v))
			{
				if (isset($row["mtype"]) && $row["mtype"] == MN_HOME_FOLDER_SUB && !$exclude[$row["oid"]])
				{
					$ret[] = $row;
				}
			}
		}
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
			if ($row["status"] != 2)
			{
				continue;
			}
			if ($row["admin_feature"] && !$this->prog_acl("view", $row["admin_feature"]) && ($this->cfg["acl"]["check_prog"]))
			{
				continue;
			}

			$sub = $this->rec_admin_tree(&$arr,$row["oid"]);

			if ($row["admin_feature"])
			{
				$sub.=$this->get_feature_tree($row["admin_feature"],$row["oid"]);
			}

			$iconurl = isset($row["icon_id"]) && $row["icon_id"] != "" ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : ($row["admin_feature"] ? $this->get_feature_icon_url($row["admin_feature"]) : "");
			$blank = $this->mk_my_orb("blank");
			$this->vars(array(
				"name"		=> $row["name"],
				"id"			=> ($row["admin_feature"] == 4 ? "gp_" : "").$row["oid"], 
				"parent"	=> ($parent == $this->cfg["amenustart"] ? $admin_rootmenu2 : $row["parent"]),
				"iconurl" =>  $iconurl,
				"url"			=> $row["link"] != "" ? $row["link"] : ($row["admin_feature"] ? $this->cfg["programs"][$row["admin_feature"]]["url"]: $blank)));

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
		$t->listacl("objects.class_id = ".CL_GROUP." AND objects.status = 2");
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
			if (!$this->can("view",$row["oid"]) || $row["gid"] == aw_ini_get("groups.all_users_grp"))
			{
				continue;
			}

			$sub = $this->rec_grp_tree(&$arr,$row["oid"]);
			$this->vars(array(
				"name" => $row["name"],"id" => "gp_".$row["oid"], "parent" => "gp_".$row["parent"],
				"iconurl" => $this->cfg["baseurl"]."/automatweb/images/ftv2doc.gif",
				"url" => $this->mk_orb("mk_grpframe",array("parent" => $row["gid"]),"groups")
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
						"url" => $this->mk_my_orb("right_frame",array("parent" => $row["oid"], "period" => $period),"admin_menus")
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

	function rec_tree_grps(&$arr,$parent)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			$sub = $this->rec_tree_grps(&$arr,$row["oid"]);
			$this->vars(array(
				"name" => $row["name"],
				"id" => "gr_".$row["oid"],
				"parent" => "gr_".$row["parent"],
				"iconurl" => $this->cfg["baseurl"]."/automatweb/images/ftv2doc.gif",
				"url" => $this->mk_orb("mk_grpframe",array("parent" => $row["gid"]),"groups")
			));
			$ret .= ($sub == "") ? $this->parse("DOC") : $this->parse("TREE");
		}
		return $ret;
	}

	function get_feature_icon_url($fid)
	{
		return aw_ini_get("icons.server")."/prog_".$fid.".gif";
	}
}
?>
