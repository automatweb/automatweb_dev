<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/groups.aw,v 2.10 2002/09/04 17:52:32 kristo Exp $
load_vcl("table");
classload("users_user","config");

session_register("group_folders");

class groups extends users_user
{
	var $typearr = array(0 => LC_GROUPS_GROUP , 1 => LC_GROUPS_USER, 2 => LC_GROUPS_DYNGROUP);

	function groups() 
	{
		$this->init("automatweb/groups");
		lc_load("definition");
	}

	function make_tree($parent,$parent_name = "parent")
	{
		global $op,$group_folders;
		if ($op == "close")
		{
			$group_folders[$parent] = 1;
		}
		else
		{
			if ($op == "open")
			{
				$group_folders[$parent] = 0;
			};
		};

		$this->grpcache = array();

		$this->listgroups("parent","asc",0,2);
		$this->listacl("class_id = ".CL_GROUP." AND status = 2");

		while ($row = $this->db_next())
		{
			$this->grpcache[$row[parent]][] = $row;
		};

		$this->vars(array(
			"space_images" => "",
			"image"=>"<img src='".$this->cfg["baseurl"]."/images/puu_site.gif'>",
			"gid"=>0,
			"op"=>"&op=open",
			"name"=>"K&otilde;ik kasutajad",
			"type"=>"",
			"members" => "",
			"modified" => "", 
			"modifiedby"=>"",
			"parent"=>0,
			"CAN_CHANGE"=>"",
			"CAN_DELETE" =>"",
			"CHECK" => "",
			"CAN_ACL" => "",
			"CAN_PRIORITY" => ""
		));
		$ret = $this->parse("LINE");
		// now recursively show the menu
		$this->selected = $parent;
		$this->sel_level = -1;
		$this->level = -1;
		$ret.=$this->rec_menu(0,"",$parent_name);

		$this->vars(array(
			"space_images" => "",
			"image"=>"<img src='".$this->cfg["baseurl"]."/images/puu_site.gif'>",
			"gid"=>0,
			"op"=>"&op=open",
			"name"=>aw_global_get("uid"),
			"type"=>"",
			"members" => "",
			"modified" => "",
			"modifiedby"=>"",
			"parent"=>0,
			"CAN_CHANGE"=>"",
			"CAN_DELETE" =>"",
			"CHECK" => "",
			"CAN_ACL" => "",
			"CAN_PRIORITY" => ""
		));
		$ret.= $this->parse("LINE");

		$this->grpcache = array();
		$ugid = $this->get_gid_by_uid(aw_global_get("uid"));
		$this->listgroups("parent","asc",GRP_USERGRP,$ugid);
		while ($row = $this->db_next())
		{
			$this->grpcache[$row[parent]][] = $row;
		};
		$this->selected = $ugid;
		$this->sel_level = -1;
		$this->level = -1;
		$ret.=$this->rec_menu($ugid,"",$parent_name);

		// kasutajad
		$this->vars(array(
			"space_images" => "",
			"image"=>"<img src='".$this->cfg["baseurl"]."/images/puu_site.gif'>",
			"gid"=>0,
			"op"=>"&op=open",
			"name"=>"Kasutajad",
			"type"=>"",
			"members" => "",
			"modified" => "",
			"modifiedby"=>"",
			"parent"=>0,
			"CAN_CHANGE"=>"",
			"CAN_DELETE" =>"",
			"CHECK" => "",
			"CAN_ACL" => "",
			"CAN_PRIORITY" => ""
		));
		$ret.= $this->parse("LINE");

		$this->grpcache = array();

		// make list of users who you can vju
		$users = array("'".aw_global_get("uid")."'");
		$this->listacl("objects.status != 0 AND objects.class_id = ".CL_GROUP);
		$this->db_query("SELECT groups.oid,groups.gid FROM groups LEFT JOIN objects ON objects.oid = groups.oid WHERE objects.status != 0");
		while ($row = $this->db_next())
		{
			$view = $this->can("view_users", $row[oid]);
			if ($view)
			{
				// add all users of this group to list of users
				$this->save_handle();
				$ul = $this->getgroupmembers2($row[gid]);
				reset($ul);
				while (list(,$u_uid) = each($ul))
				{
					$users[$u_uid] = $u_uid;
				}
				$this->restore_handle();
			}
		}

		$this->listgroups("parent","asc",GRP_DEFAULT);
		while ($row = $this->db_next())
		{
			if (!$row["parent"])
			{
				$row["parent"] = 0;
			};
			if ($users[$row["name"]] != "")
			{
				$this->grpcache[$row["parent"]][] = $row;
			}
		}
		$this->selected = $ugid;
		$this->sel_level = -1;
		$this->level = -1;
		$ret.=$this->rec_menu(0,"",$parent_name);
		return $ret;
	}

	function rec_menu($parent,$space_images,$parent_name)
	{
		global $group_folders,$orb;
		$ext = $this->cfg["ext"];

		if (!is_array($this->grpcache[$parent]))	// if no items on this level return immediately
		{
			return;
		}

		$this->level++;
		$ret = "";
		reset($this->grpcache[$parent]);
		$num_els = count($this->grpcache[$parent]);
		$cnt = 1;
		while (list(,$v) = each($this->grpcache[$parent]))
		{
			$spim = $space_images;

			if ($group_folders[$v[gid]] == 1)	// if it's closed
			{
				$op = "open";
			}
			else
			{
				$op = "close";
			}

			if (is_array($this->grpcache[$v["gid"]]))	// has subitems
			{
				if ($orb)
				{
					$image = "<img src='";
				}
				else
				{
					$image = "<a href='".$this->make_url(array($parent_name => $v["gid"], "op" => $op))."'><img src='";
				}

				$image.="/images/puu_tyhi.gif' border=0>";
			}
			else	// does not have subitems
			{
				$image = "<img src='".$this->cfg["baseurl"]."/images/puu_tyhi.gif";
				if ($orb)
				{
					$image.="' border=0>";
				}
				else
				{
					$image.="' border=0><a href='".$this->make_url(array($parent_name => $v["gid"], "op" => $op))."'>";
				}
			}

			$b = "";
			if ($this->selected == $v["gid"])
			{
				$this->seltype = $v["type"];
				$this->sel_level = $this->level;
				$b = "l";
			}

			$image.="<img src='images/ftv2folderclosed.gif' border=0>";
			if (!$orb)
			{
				$image.="</a>";
			}

			switch ($v["type"])
			{
				case "0":
					$type = LC_GROUPS_GROUP;
					break;
				case "1":
					$type = LC_GROUPS_USER;
					break;
				case "2":
					$type = LC_GROUPS_DYNGROUP;
					break;
			}

			$name = $v["name"];
			if (!$orb)
			{
				$name="<a href='".$this->make_url(array($parent_name => $v["gid"],"op" => "open"))."'>".$v["name"]."</a>";
			}
			$this->vars(array(
				"space_images"	=> $spim, 
				"image"					=> $image,
				"gid"						=> $v["gid"],
				"name"					=> $name,
				"type"					=> $type,
				"members"				=> $v["gcount"],
				"modifiedby"		=> $v["modifiedby"],
				"modified"			=> $this->time2date($v["modified"],2),
				"op"						=> "&op=open",
				"parent"				=> $this->selected,
				"priority"			=> $v["priority"],
				"level"					=> $this->level,
				"goid"					=> $v["oid"]
			));

			if ($this->pick_list)
			{
				$this->vars(array(
					"grp_check" => checked(is_array($this->pick_arr[$v["gid"]])),
					"member"		=> (is_array($this->pick_arr[$v["gid"]]) ? "1" : "0")
				));
				$che = $this->parse("CHECK");
			}

			$rt = $v["type"] == GRP_REGULAR || $v["type"] == GRP_DYNAMIC || $v["type"] == GRP_USERGRP;

			$this->vars(array(
				"CAN_CHANGE" => $rt && $this->can("edit",$v["oid"]) ? $this->parse("CAN_CHANGE") : "",
				"CAN_DELETE" => $rt && $this->can("delete",$v["oid"]) ? $this->parse("CAN_DELETE") : "",
				"CAN_ACL" => $rt && $this->can("admin",$v["oid"]) ? $this->parse("CAN_ACL") : "",
				"CHECK"			 => $che,
				"CAN_PRIORITY" => $rt && $this->can("order",$v["oid"]) ? $this->parse("CAN_PRIORITY") : ""
			));

			if ($this->can("view", $v["oid"]))
			{
				$ret.=$this->parse("LINE");
			}

			$spim.="<img src='".$this->cfg["baseurl"]."/images/puu_tyhi.gif' border=0>";

			if ($group_folders[$v["gid"]] == 0 && $this->can("view", $v["oid"]))	// if the folder is open and we can see it
			{
				$ret.=$this->rec_menu($v["gid"],$spim,$parent_name);
			}

			$cnt++;
		}
		$this->level--;
		return $ret;
	}

	function gen_pick_list()
	{
		global $parent,$all,$oid,$groups;
		$this->read_template("pick_list.tpl");

		// tell the tree generator that this is a pickable list and also the member groups
		$this->pick_list = true;
		$this->pick_arr = $this->get_acl_groups_for_obj($oid);

		$this->vars(array("LINE" => $this->make_tree($parent),"parent" => $parent));
		$this->vars(array("grp_level"	=> $this->sel_level+1));

		$t = new users;
		$pg = $this->fetchgroup($parent);

		$ng = new groups;
		$this->vars(array(
			"CAN_ADD"		=> ($seltype == 0 || $seltype == 2) && ($this->can("add",$pg["oid"]) || $parent < 1) ? $this->parse("CAN_ADD") : "",
			"from"			=> aw_global_get("REQUEST_URI"),
			"reforb"  => $this->mk_reforb("submit_acl_groups", array("oid" => $oid,"user" => $user)),
			"oid"				=> $oid,
			"file" => $GLOBALS["file"]
		));
		return $this->parse();
	}

	function submit_acl_groups($arr = array())
	{
		classload("acl");
		$acl = new acl;
		$acl->submit_acl_groups($arr);
		return "/?orb=1&class=acl&action=edit&oid=" . $arr["oid"];
	}
	
	function submit_group($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		if (!$parent)
		{
			$parent = 0;
		}

		if ($gid)	// change
		{
			$pg = $this->fetchgroup($gid);
			if (!$this->can("edit",$pg["oid"]))
			{
				$this->acl_error("edit",$pg["oid"]);
			}

			if ($type == 0)
			{
				// normal group, save
				$this->savegroup(array("gid" => $gid,"name" => $name));
				return true;
			}
			else
			{
				if ($level != 0)
				{
					// dyn. group, save too
					$c = new db_config;
					$fid = $c->get_simple_config("user_search_form");
					$f = new form($fid);
					$eid = $f->process_entry($entry_id);

					if (!$parent) $parent=0;

					$this->savegroup(array("gid" => $gid,"name" => $name, "data" => $eid));
					$this->update_dyn_group($gid);
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		else
		{					// add
			if ($parent > 0)
			{
				$pg = $this->fetchgroup($parent);
				if (!$this->can("add",$pg["oid"]))
				{
					$this->acl_error("add",$pg["oid"]);
				}
			}

			if ($type == 0)
			{
				// normal group, jizt add it
				$this->addgroup($parent,$name,0,0,0);
				return true;
			}
			else
			{
				if (!$level)
				{
					// dyn. group, go to level 2
					return false;
				}
				else
				{
					// really add the dyn. group
					$c = new db_config;
					$fid = $c->get_simple_config("user_search_form");
					$f = new form($fid);
					$eid = $f->process_entry();

					if (!$parent) $parent=0;

					$gid = $this->addgroup($parent,$name, 2,$eid,$grp_level*GROUP_LEVEL_PRIORITY);
					$this->update_dyn_group($gid);
					return true;
				}
			}
		}
	}

	function update_grp_members($arr)
	{
		$members = $this->getgroupmembers2($arr["gid"]);

		$toadd = array();
		$toremove = array();

		reset($arr);
		while (list($k,$v) = each($arr))
		{
			if (substr($k,0,3) == "um_")
			{
				$uuid = substr($k,3);
				$mem = $members[$uuid] == $uuid ? 1 : 0;

				if ($mem != $arr["us_".$uuid])	// if membership is the same, as previously, don't change it
				{
					// if not, then do change it
					if (isset($arr["us_".$uuid]))
					{
						// if user was set to be a member then add the dude
						$toadd[] = $uuid;
					}
					else
					{
						// if user was removed, then guess what
						$toremove[] = $uuid;
					}
				}
			}
		}

		// here we must add the user to this group and all the groups above it
		$this->add_users_to_group_rec($arr["gid"],$toadd,true,true);

		// we must find all groups below this one and remove the user from all those groups and this one 
		// and we must check that if the group we are removing the user from is a dynamic group, then we
		// must also update the users' record, that he must not be reinserted into that group
		$this->remove_users_from_group_rec($arr["gid"],$toremove,true);
	}

	function update_priorities($arr)
	{
		reset($arr);
		while (list($k,$v) = each($arr))
		{
			if (substr($k,0,3) == "gp_")
			{
				$this->savegroup(array("gid" => substr($k,3), "priority" => $v));
			}
		}
	}

	function submit_grp_groups($arr)
	{
		reset($arr);
		while (list($k,$v) = each($arr))
		{
			if (substr($k,0,3) == "gm_")
			{
				// check if membership has changed
				$gid = substr($k,3);
				if ($gid != $arr["parent"])	// don't let the user add a group into itself
				{
					$var = "gs_".$gid;
					$nv = $arr[$var];
					if ($nv < 1)
						$nv = 0;
					if ($nv != $v)
					{
						// it has changed, now check if groups were added or deleted
						if ($nv == 1)
						{
							// group was added
							// add relation and make all users of the added group members of the parent group
							$this->add_grpgrp_relation($arr["parent"],$gid);
						}
						else
						{
							// group was removed
							// delete relation and remove all members of the removed group from the members of the parent group
							$this->remove_grpgrp_relation($arr["parent"],$gid);
						}
					}
				}
			}
		}
	}

	function list_grps_user($arr)
	{
		extract($arr);
		$this->dmsg("entered list_grps_user");
		if (!$parent)
		{
			$parent = $this->db_fetch_field("SELECT gid FROM groups WHERE type = ".GRP_DEFAULT." AND name = '".aw_global_get("uid")."'","gid");
		}

		$this->read_template("list_grps_user.tpl");

		$level = $this->get_grp_level($parent);

		$this->listgroups(-1,-1,-1,-1,$parent);
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"name" => $row["name"],
				"gid" => $row["gid"],
				"priority" => $row["priority"],
				"level" => $level+1,
				"type" => "Grupp",
				"members" => $row["gcount"], 
				"modifiedby" => $row["modifiedby"], 
				"modified" => $this->time2date($row["modified"],2), 
				"goid" => $row["oid"],
				"change"	=> $this->mk_my_orb("change_user_grp", array("id" => $row["gid"], "parent" => $parent)),
				"delete"	=> $this->mk_my_orb("delete_user_grp", array("id" => $row["gid"], "parent" => $parent)),
				"grpmembers"	=> $this->mk_my_orb("user_grp_members", array("id" => $row["gid"])),
				"acl" => $this->mk_my_orb("edit", array("oid" => $row["oid"]), "acl")
			));
			$l.=$this->parse("LINE");
		}

		$this->vars(array(
			"addgrp" => $this->mk_my_orb("add_user_grp",array("parent" => $parent)),"LINE" => $l,
			"reforb"	=> $this->mk_reforb("submit_user_grp_priorities", array("parent" => $parent))
		));

		return $this->parse();
	}

	function add_user_grp($arr)
	{
		extract($arr);
		$this->mk_path(0,sprintf(LC_GROUPS_GROUP_ADD,$this->mk_orb("list_grps_user",array("parent" => $parent))));
		$this->read_template("add_user_grp.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_user_grp",array("parent" => $parent,"level" => $this->get_grp_level($parent)))
		));
		return $this->parse();
	}

	function change_user_grp($arr)
	{
		extract($arr);
		$this->mk_path(0,sprintf(LC_GROUPS_GROUP_CHANGE,$this->mk_orb("list_grps_user",array("parent" => $parent))));
		$this->read_template("change_user_grp.tpl");

		$gp = $this->fetchgroup($id);

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_user_grp",array("parent" => $parent,"level" => $this->get_grp_level($parent),"id" => $id)),
			"name" => $gp["name"], 
			"priority" => $gp["priority"]
		));
		return $this->parse();
	}

	function submit_user_grp($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->savegroup(array("name" => $name, "priority" => $priority,"gid" => $id));
		}
		else
		{
			$id = $this->addgroup($parent,$name,GRP_USERGRP,0,$level*GROUP_LEVEL_PRIORITY);
			$grp = $this->fetchgroup($id);
			// access is granted to the current user, when object is created
			// deny access to everybody else
//			$this->deny_obj_access($grp[oid]);
		}
		return $this->mk_my_orb("list_grps_user",array("parent" => $parent));
	}

	function do_grp_members($id)
	{
		$gp = $this->fetchgroup($id);

		// we must only show users who are in groups to which the suer has can_copy access
		$users = array(aw_global_get("uid") => "\"".aw_global_get("uid")."\"");
		$this->listacl("objects.status != 0 AND objects.class_id = ".CL_GROUP);
		$this->db_query("SELECT groups.oid,groups.gid FROM groups LEFT JOIN objects ON objects.oid = groups.oid WHERE objects.status != 0");
		while ($row = $this->db_next())
		{
			if ($this->can("copy", $row[oid]))
			{
				// add all users of this group to list of users
				$this->save_handle();
				$ul = $this->getgroupmembers2($row["gid"]);
				reset($ul);
				while (list(,$u_uid) = each($ul))
				{
					$users[$u_uid] = "\"".$u_uid."\"";
				}
				$this->restore_handle();
			}
		}

		$members = array();
		$this->listall($id);
		while ($row = $this->db_next())
		{
			if ($users[$row["uid"]])
			{
				$members[$row["uid"]] = "\"".$row["uid"]."\"";
			}
		}

		$this->vars(array(
			"members" => join(",",$members),
			"users"		=> join(",",$users)
		));
	}

	function grp_members($arr)
	{
		extract($arr);
		$gp = $this->fetchgroup($gid);
		if (!$this->can("copy",$gp["oid"]))
		{
			return "";
		}
		$this->mk_path(0,"Muuda grupi ".$gp["name"]." liikmeid");
		$this->read_template("user_grp_members.tpl");
		$this->do_grp_members($gid);
		$this->vars(array("reforb"	=> $this->mk_reforb("submit_grp_members", array("gid" => $gid))));
		return $this->parse();
	}

	function user_grp_members($arr)
	{
		extract($arr);
		$gp = $this->fetchgroup($id);
		if (!$this->can("copy",$gp["oid"]))
		{
			return "";
		}
		$this->mk_path(0,"<a href='".$this->mk_orb("list_grps_user",array("parent" => $gp["parent"]))."'>Grupid</a> / Muuda liikmeid");
		$this->read_template("user_grp_members.tpl");
		$this->do_grp_members($id);
		$this->vars(array("reforb"	=> $this->mk_reforb("submit_user_grp_members", array("id" => $id))));
		return $this->parse();
	}

	function do_submit_gp_members($arr)
	{
		extract($arr);

		if (!is_array($members))
		{
			$members = array();
		}

		$pmembers = $this->getgroupmembers2($id);

		$users = array(aw_global_get("uid") => aw_global_get("uid"));
		$this->listacl("objects.status != 0 AND objects.class_id = ".CL_GROUP);
		$this->db_query("SELECT groups.oid,groups.gid FROM groups LEFT JOIN objects ON objects.oid = groups.oid WHERE objects.status != 0");
		while ($row = $this->db_next())
		{
			if ($this->can("copy", $row["oid"]))
			{
				// add all users of this group to list of users
				$this->save_handle();
				$ul = $this->getgroupmembers2($row["gid"]);
				reset($ul);
				while (list(,$u_uid) = each($ul))
				{
					$users[$u_uid] = $u_uid;
				}
				$this->restore_handle();
			}
		}

		$toadd = array();
		$toremove = array();

		reset($pmembers);
		while (list(,$v) = each($pmembers))
		{
			// only do bad things to users we have access to 
			if ($users[$v] != "")
			{
				if (!in_array($v,$members))
				{
					// if the user is not in the selected users, remove him
					$toremove[] = $v;
				}
			}
		}

		if (is_array($members))
		{
			reset($members);
			while (list(,$v) = each($members))
			{
				// only do bad things to users we have access to 
				if ($users[$v] != "")
				{
					if (!in_array($v,$pmembers))
					{
						// if the user is in the selected users, but not in the group, add him
						$toadd[] = $v;
					}
				}
			}
		}

		// here we must add the user to this group and all the groups above it
		$this->add_users_to_group_rec($id,$toadd,true,true);

		// we must find all groups below this one and remove the user from all those groups and this one 
		// and we must check that if the group we are removing the user from is a dynamic group, then we
		// must also update the users' record, that he must not be reinserted into that group
		$this->remove_users_from_group_rec($id,$toremove,true);

		// check if the logged in user is still a member of his own group.
		if (!$this->is_member(aw_global_get("uid"),$this->get_gid_by_uid(aw_global_get("uid"))))
		{
			// if not, add him to the group
			$this->add_users_to_group($id,array(aw_global_get("uid")));
		}
	}

	function submit_user_grp_members($arr)
	{
		extract($arr);
		$this->do_submit_gp_members($arr);
		$gp = $this->fetchgroup($id);
		return $this->mk_my_orb("list_grps_user", array("parent" => $gp["parent"]));
	}

	function submit_grp_members($arr)
	{
		extract($arr);
		$arr["id"] = $arr["gid"];
		$this->do_submit_gp_members($arr);
		return $this->mk_orb("grp_members", array("gid" => $arr["gid"]));
	}

	function delete_user_grp($arr)
	{
		extract($arr);
		$this->deletegroup($id);
		header("Location: ".$this->mk_my_orb("list_grps_user", array("parent" => $parent)));
	}

	function submit_user_grp_priorities($arr)
	{
		extract($arr);

		if (is_array($gp))
		{
			reset($gp);
			while (list($gid,$pri) = each($gp))
			{
				$this->savegroup(array("gid" => $gid, "priority" => $pri));
			}
		}

		return $this->mk_my_orb("list_grps_user", array("parent" => $parent));
	}

	// hm. listib koik grupid?
	function list_grps($arr)
	{
		extract($arr);

		$this->dmsg("entered list_grps");
		$this->read_template("list_grps.tpl");

		$this->listacl("objects.class_id = ".CL_GROUP);
		$this->listgroups(-1,-1,-1,-1,$parent);
		while ($row = $this->db_next())
		{
			if ($row["parent"] != $parent)
			{
				continue;
			}

			$this->vars(array(
				"name"				=> $row["name"], 
				"gid"					=> $row["gid"], 
				"type"				=> $this->typearr[$row["type"]], 
				"members"			=> $row["gcount"], 
				"modifiedby"	=> $row["modifiedby"], 
				"modified"		=> $this->time2date($row["modified"], 2), 
				"priority"		=> $row["priority"],
				"oid"					=> $row["oid"],
				"change"			=> $this->mk_orb("change", array("gid" => $row["gid"],"parent" => $row["parent"])),
				"delete"			=> $this->mk_orb("delete", array("gid" => $row["gid"], "parent" => $parent)),
				"chmembers"		=> $this->mk_orb("grp_members", array("gid" => $row["gid"]))
			));
			$cc = "";
			$cd = "";
			$ca = "";
			$nf = "";
			if ($this->can("edit", $row["oid"]))
			{
				$cc = $this->parse("CAN_CHANGE");
			}
			if ($this->can("delete", $row["oid"]))
			{
				$cd = $this->parse("CAN_DELETE");
			}
			if ($this->can("admin", $row["oid"]))
			{
				$ca = $this->parse("CAN_ACL");
			}
			if ($this->can("order", $row["oid"]))
			{
				$nf = $this->parse("NFIRST");
			}
			$this->vars(array("CAN_CHANGE" => $cc, "CAN_DELETE" => $cd, "CAN_ACL" => $ca, "NFIRST" => $nf));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l, 
			"addgrp" => $this->mk_orb("add", array("parent" => $parent)),
			"reforb" => $this->mk_reforb("submit_priorities", array("parent" => $parent))
		));
		
		$gp = $this->fetchgroup($parent);
		$add = true;
		if ($parent == 0)
		{
			$add = $this->prog_acl("add", PRG_GROUPS);
		}
		else
		{
			$add = $this->can("add", $gp["oid"]);
		}
		if ($add)
		{
			$ac = $this->parse("ADD_CAT");
		}

		$yah = "";
		$yaha = array();
		$this->getgroupsabove($parent,&$yaha);
		reset($yaha);
		while (list(,$gid) = each($yaha))
		{
			$yah="<a target=\"list\" href='".$this->mk_orb("mk_grpframe",array("parent" => $gid))."'>".$this->grpcache2[$gid]["name"]."</a> / ".$yah;
		}
		$yah = "<a target=\"list\" href='".$this->mk_orb("mk_grpframe",array("parent" => 0))."'>Grupid</a> / ".$yah;
		$this->vars(array("yah" => $yah));
		$this->vars(array("ADD_CAT" => $ac));
		$GLOBALS["site_title"] = $yah;

		return $this->parse();
	}

	function submit_priorities($arr)
	{
		extract($arr);

		if (is_array($priority))
		{
			reset($priority);
			while (list($gid,$p) = each($priority))
			{
				$this->savegroup(array("gid" => $gid, "priority" => $p));
			}
		}
		return $this->mk_orb("list_grps", array("parent" => $parent));
	}

	function change($arr)
	{
		extract($arr);
		global $level;

		$pg = $this->fetchgroup($gid);
		if (!$this->can("edit",$pg["oid"]))
		{
			$this->acl_error("edit",$pg["oid"]);
		}

		if (!$level)
		{
			$this->read_template("change_grp.tpl");

			$this->vars(array(
				"name"				=> $pg["name"],
				"type"				=> $pg["type"],
				"modifiedby"	=> $pg["modifiedby"],
				"modified"		=> $this->time2date($pg["modified"],2),
				"gcount"			=> $pg["gcount"],
				"gid"					=> $gid,
				"reforb"			=> $this->mk_reforb("submit_grp", array("gid" => $gid))
			));
			return $this->parse();
		}
		else
		{
			// since we are on level 2, it is a dyn group.
			$fid = $pg["search_form"];

			$f = new form();

			global $name;
			return $f->gen_preview(array("id" => $fid,"entry_id" => $pg["data"], "reforb" => $this->mk_reforb("submit_grp", array("parent" => $parent, "level" => 1, "search_form" => $fid, "name" => $name,"type" => 2, "gid" => $gid))));
		}
	}

	function submit_grp($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		if (!$parent)
		{
			$parent = 0;
		}

		if ($gid)	// change
		{
			$pg = $this->fetchgroup($gid);
			if (!$this->can("edit",$pg["oid"]))
			{
				$this->acl_error("edit",$pg["oid"]);
			}

			$this->savegroup(array("gid" => $gid,"name" => $name));

			if (!$level)
			{
				if ($pg["type"] != 0)
				{
					return $this->mk_orb("change", array("parent" => $pg["parent"],"gid" => $gid, "level" => 1,"name" => $name));
				}
			}
			else
			{
				// save dyn grp
				$f = new form();
				$f->process_entry(array("id" => $pg["search_form"], "entry_id" => $entry_id));
				$eid = $f->entry_id;

				if (!$parent) 
				{
					$parent=0;
				}

				$this->savegroup(array("gid" => $gid,"name" => $name, "data" => $eid));
				$this->update_dyn_group($gid);
			}
			return $this->mk_orb("list_grps", array("parent" => $pg["parent"]));
		}
		else
		{					
			// add
			if ($parent > 0)
			{
				$pg = $this->fetchgroup($parent);
				if (!$this->can("add",$pg["oid"]))
				{
					$this->acl_error("add",$pg["oid"]);
				}
			}

			if ($type == 0)
			{
				// normal group, jizt add it
				if ($pg["type"] == GRP_USERGRP || $pg["type"] == GRP_DEFAULT)
				{
					$this->addgroup($parent,$name,GRP_USERGRP,0,0);
				}
				else
				{
					$this->addgroup($parent,$name,0,0,0);
				}
				return $this->mk_orb("list_grps", array("parent" => $parent));
			}
			else
			{
				if (!$level)
				{
					// dyn. group, go to level 2
					$retval = $this->mk_orb("add",array("parent" => $parent, "name" => $name, "level" => 1,"search_form" => $search_form));
					return $retval;
				}
				else
				{
					// really add the dyn. group
					$f = new form();
					$f->process_entry(array("id" => $search_form));

					if (!$parent) 
					{
						$parent=0;
					}

					$gid = $this->addgroup($parent,$name, 2,$f->entry_id,$grp_level*GROUP_LEVEL_PRIORITY,$search_form);
					$this->update_dyn_group($gid);
					return $this->mk_orb("list_grps", array("parent" => $parent));
				}
			}
		}
	}

	// seda jälle kasutab orb
	function add($arr)
	{
		extract($arr);
		if ($parent > 0)
		{
			$pg = $this->fetchgroup($parent);
			if (!$this->can("add",$pg["oid"]))
			{
				$this->acl_error("add",$pg["oid"]);
			}
		}

		global $level;
		if (!$level)
		{
			// make list of search forms
			$this->db_query("SELECT objects.* FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE type = ".FTYPE_SEARCH." AND objects.status != 0 and site_id = ".$this->cfg["site_id"]);
			$sfs = array();
			while ($row = $this->db_next())
			{
				$sfs[$row["oid"]] = $row["name"];
			}
			$this->read_template("add_grp.tpl");
			$this->vars(array(
				"parent" => $parent,
				"search_forms" => $this->picker(0,$sfs),
				"reforb"	=> $this->mk_reforb("submit_grp",array("parent" => $parent))
			));
			return $this->parse();
		}
		else
		{
			global $name;
			$f = new form;
			return $f->gen_preview(array("id" => $search_form,"reforb" => $this->mk_reforb("submit_grp", array("parent" => $parent, "level" => 1, "search_form" => $search_form, "name" => $name,"type" => 2))));
		}
	}

	function delete_grp($arr)
	{
		extract($arr);
		$this->deletegroup($gid);
		header("Location: ".$this->mk_orb("list_grps", array("parent" => $parent)));
	}

	function mk_grpframe($arr)
	{
		extract($arr);
		if (!$this->prog_acl("view", PRG_GROUPS))
		{
			$this->prog_acl_error("view", PRG_GROUPS);
		}
		$this->read_template("frameset.tpl");
		$this->vars(array(
			"topframe"	=> $this->mk_orb("list_grps", array("parent" => $parent)),
			"bottframe"	=> $this->mk_orb("grp_members", array("gid" => $parent))
		));
		die($this->parse());
	}
};
?>
