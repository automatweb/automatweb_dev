<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/users_user.aw,v 2.9 2001/07/12 04:23:46 kristo Exp $
// jaaa, on kyll tore nimi sellel failil.

// gruppide jaoks vajalikud konstandid

// group types:
// 0 - ordinary, user added group
// 1 - user's default group
// 2 - dynamic group
// 3 - deleted user group
// 4 - group added below the users default group
define(GRP_REGULAR,0);
define(GRP_DEFAULT,1);
define(GRP_DYNAMIC,2);
define(GRP_DELETED_USER,3);
define(GRP_USERGRP,4);

define(GROUP_LEVEL_PRIORITY, 100000);
define(USER_GROUP_PRIORITY, GROUP_LEVEL_PRIORITY*1000);	// max 1000 levels of groups

classload("defs");
lc_load("users");

class users_user extends aw_template 
{
	function users_user() 
	{
		$this->db_init();
	}

	////
	// !Listib koik grupid
	function listall($gid= 0) 
	{
		if ($gid)
		{
			$q = "SELECT * FROM users
						LEFT JOIN groupmembers ON groupmembers.uid = users.uid
						WHERE groupmembers.gid = $gid AND blocked != 1
						ORDER BY users.uid";
		}
		else
		{
			$q = "SELECT * FROM users WHERE blocked != 1 ORDER BY uid";
		}
		$this->db_query($q);
	}

	////
	// !Listib koik grupid, kontrollib ka n2gemis6igust, tagastab array 
	function listall_acl() 
	{
		$users = array("'".$GLOBALS["uid"]."'");
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
					if ($view)
					{
						$users[] = "'".$u_uid."'";
					}
				}
				$this->restore_handle();
			}
		}
	
		$ret = array();
		$uss = join(",",$users);
		if ($uss != "")
		{
			$this->db_query("SELECT uid FROM users WHERE uid IN($uss) AND blocked = 0");
			while ($row = $this->db_next())
			{
				$ret[$row["uid"]] = $row["uid"];
			}
		}
		return $ret;
	}

	////
	// !Kinky - touch user. Anyway. Seab users tabelis lastaction valja hetke timestambiks
	// Saab kasutada selleks, et teha kindlaks kui kaua kasutaja on idle istunud
	function touch($user) 
	{
		$t = time();
		$q = "UPDATE users SET lastaction = '$t' WHERE uid = '$user'";
		$this->db_query($q);
	}

	////
	// !Tagastab koigi registreeritud kasutajate arvu
	function count() 
	{
		$q = "SELECT count(*) AS total,SUM(online) AS online FROM users";
		$this->db_query($q);
		$row = $this->db_fetch_row();
		return $row;
	}	

	////
	// !Logib kasutaja sisse
	function login($params = array())
	{
		global $uid;
		$uid		= $params["uid"];
		$password	= $params["password"];
		// need on selleks, et ei peaks global $REMOTE_ADDR tegema, vms
		$ip		= $params["remote_ip"];
		$host		= $params["remote_host"];

		$t 		= time();
		$msg		= "";
		$success	= true;

		// 2 voiks ka konstandiga asendada, 
		// samuti voiks kontrollida, kas kasutajanimi (parool) liiga pikk ei ole
		// ja keelatud tähti ei sisalda
		// is_valid_uid funktsioon ntx?
		if (strlen($uid) < 2)
		{
			$msg = sprintf(E_USR_UID_TOO_SHORT,$uid,$password);
			$this->send_alert($msg);
			$this->_log("auth",$msg);
			$success = false;
		}
		// eelnevad kommentaarid kaivad ka parooli kontrollimise kohta
		elseif (strlen($password) < 2)
		{
			$msg = sprintf(E_USR_PASS_TOO_SHORT,$uid,$password);
			$this->send_alert($msg);
			$this->_log("auth",$msg);
			$success = false;
		}
		elseif (!is_valid("uid",$uid))
		{
			$msg = "Vigane kasutajanimimi $uid";
			$this->send_alert($msg);
			$this->_log("auth",$msg);
			$success = false;
		};

		if ($success)
		{
			$q = "SELECT * FROM users WHERE uid = '$uid' AND blocked = 0";
			$this->db_query($q);
			$udata = $this->db_next();
		};
		if ($udata)
		{
		}
		else
		{
			$msg = "Sellist kasutajat pole $uid";
			$this->send_alert($msg);
			$this->_log("auth",$msg);
			$success = false;
			session_unregister("uid");
			unset($uid);
			return false;
		};

		if (($password != $udata["password"]))
		{
			// vale parool
			$msg = sprintf(E_USR_WRONG_PASS,$uid,$password);
			$this->send_alert($msg);
			$this->_log("auth",$msg);
			$success = false;
		};
		if ($success)
		{
			// njah. Mitte ei taha. Aga midagi yle ka ei jaa. Logime vaese bastardi sisse
			// HUZZAH!
			$q = "UPDATE users
				SET 	logins = logins+1,
			    	ip = '$ip',
			    		lastaction = $t,
			    		online = 1
		      		WHERE uid = '$uid'";
			$this->db_query($q);
			$this->_log("auth",USR_LOGGED_IN);
			if (defined("TAFKAP"))
			{
				setcookie("tafkap",$uid,strtotime("+7 years"));
			};
		};
		$this->msg = $msg;
		// caller voib kontrollida - if (!$users->login("fubar"))
		//				login failed	
		if ($success && $params["reforb"])
		{
			global $baseurl;
			session_register("uid");
			classload("config");
			$t = new db_config;
			$url = $t->get_simple_config("after_login");
			if ($url == "")
			{
				$url = $baseurl;
			}
			return $url;
		}
		else
		{
			return $success;
		};
	}

	////
	// !Logib kasutaja valja
	function logout($uid) 
	{
		$t = time();
		$q = "UPDATE users
			SET 	online = 0,
				lastaction = $t
			WHERE uid = '$uid'";
		$this->db_query($q);
		$this->_log("auth",USR_LOGGED_OUT);
	}

	// Kuvab messengeri jaoks folderi valimise vormi
	// kusjuures, see oli mingi nuriidee, seda funktsiooni pole vaja enam
	function pick_folder($args = array())
	{
		classload("menuedit_light");
		$mnl = new menuedit_light();
		$chooser = $mnl->gen_rec_list(array(
			"start_from" => 220,
			"tpl" => "objects/chooser.tpl",
			"start_tpl" => "object",
			"single_tpl" => true,
		));
		$this->read_template("pick_folder.tpl");
		$this->vars(array(
			"variants" => $chooser,
		));
		return $this->parse();
	}

	////
	// !Logib välja. Orb-i versioon
	function orb_logout($args)
	{
		extract($args);
		$this->logout($uid);
		session_destroy();
		global $baseurl;
		return $baseurl;
	}
	

	////
	//! Genereerib sisselogitud kasutaja kodukataloogi
	function gen_homedir($args = array())
	{
		global $udata;

		$tpl = ($args["tpl"]) ? $args["tpl"] : "homefolder.tpl";
		$this->read_template($tpl);

		// koigepealt teeme kodukataloogi id kindlaks
		$this->db_query("SELECT menu.*,objects.* FROM menu
					LEFT JOIN objects ON objects.oid = menu.id
					WHERE oid = $udata[home_folder]");
		$hf = $this->db_next();
		$result = array();
		$startfrom = ($parent == 0) ? $hf["oid"] : $parent;
		$thisone = $this->get_object($parent);
		$prnt = $this->get_object($thisone["parent"]);
		$up = "";
		if ($parent)
		{
			if ($prnt["oid"] != $hf["oid"])
			{
				$this->vars(array(
					"id" => "id=$prnt[oid]",
				));
			};
			$this->vars(array(
				"name" => $prnt["name"],
			));
			$up = $this->parse("up");
		}
		$q = "SELECT objects.*,menu.* FROM objects
			LEFT JOIN menu ON (objects.oid = menu.id)
			WHERE objects.parent = '$startfrom'";
		$this->db_query($q);
		$folders = "";
		$cnt = 0;
		while($row = $this->db_next())
		{
			$cnt++;
			$this->vars(array(
				"name" => $row["name"],
				"id" => $row["oid"],
				#"iconurl" => "images/ftv2doc.gif",
				"iconurl" => get_icon_url($row["class_id"],0),
			));
			switch ($row["class_id"])
			{
				case CL_PSEUDO:
					$tpl = "folder";
					break;
				default:
					$tpl = "doc";
					break;
			};
			$folders .= $this->parse($tpl);
		};
		$delete = "";
		if ($cnt > 0)
		{
			$delete = $this->parse("delete");
		};

		$this->vars(array("folder" => $folders,
				  "doc" => $docs,
				  "name" => $thisone["name"],
				  "parent" => $startfrom,
				  "delete" => $delete,
				  "total" => $cnt,
				  "up" => $up,
				 ));
		return $this->parse();
	}

	////
	// Küsib info mingit kasutaja kohta
	// DEPRECATED. core->get_user on parem

	function fetch($uid) 
	{
		return $this->get_user(array(
			"uid" => $uid,
		));
	}

	////
	// !Salvestab kasutaja info. Ma ei tea kui relevantne see on, sest osa andmed hoitakse ju enivei
	// hoopis vormides
	// suht relevantne on , kasutaja tabelis on ka sitax inffi.
	function save($data) 
	{
		$this->quote($data);
		$sets = array();	

		reset($data);
		while (list($k,$v) = each($data))
			if ($k != "uid")
				$sets[] = " $k = '$v' ";
		$sets = join(",", $sets);

		$q = "UPDATE users SET $sets WHERE uid = '".$data[uid]."'";
		$this->db_query($q);
		$this->_log("user",$GLOBALS["uid"] . " muutis kasutaja \"$uid\" andmeid");
	}

	function savegroup($data) 
	{
		$this->quote($data);
		$sets = array();	

		reset($data);
		while (list($k,$v) = each($data))
		{
			if ($k != "gid")
			{
				$sets[] = " $k = '$v' ";
			}
		}
		$sets = join(",", $sets);

		$q = "UPDATE groups SET modified = ".time().", modifiedby = '".$GLOBALS["uid"]."', $sets WHERE gid = '".$data[gid]."'";
		$this->db_query($q);
		$this->_log("group",$GLOBALS["uid"] . " muutis grupi \"$data[gid]\" andmeid");
	}

	function listgroups($gorderby = -1,$gsorder = -1,$type = -1,$type2 = -1,$parent=-1) 
	{
		if ($gorderby != -1) 
		{
			$field = ($gorderby == "gcount") ? "gcount" : "groups.$gorderby";
			$sufix = " ORDER BY $field $gsorder ";
		} 
		else 
		{
			$sufix = "";
		};
		if ($type != -1)
		{
			$ss = " WHERE groups.type = $type ";
		}
		else
		{
			$ss = " WHERE groups.type != 3 ";
		}
		if ($type2 != -1)
		{
			$ss.=" OR groups.type = $type2 ";
		}
		if ($parent != -1)
		{
			$ss.=" AND groups.parent = $parent ";
		}
		$q = "SELECT groups.*,count(groupmembers.gid) AS gcount
			FROM groups
			LEFT JOIN groupmembers on (groups.gid = groupmembers.gid)
			$ss
			GROUP BY groups.gid
			$sufix";
		$this->db_query($q);
	}

	function addgroup($parent,$gname,$type=0,$data = 0,$priority = USER_GROUP_PRIORITY,$search_form = 0) 
	{
		$this->quote($gname);
		global $uid;

		$pg = $this->fetchgroup($parent);

		$t = time();
		$oid = $this->new_object(array("name" => $gname, "class_id" => CL_GROUP, "status" => 2, "parent" => $pg[oid]));

		$q = "INSERT INTO groups (name,created,createdby,modified,modifiedby,type,data,parent,priority,oid,search_form)
			VALUES('$gname',$t,'$uid',$t,'$uid','$type','$data',$parent,$priority,$oid,$search_form)";
		$this->db_query($q);
		$this->_log("group",$GLOBALS["uid"] . " lisas uue grupi - $gname");
		return $this->db_fetch_field("SELECT MAX(gid) AS gid FROM groups", "gid");
	}

	function fetchgroup($gid) 
	{
		$q = "SELECT *,count(groupmembers.gid) AS gcount FROM groups 
					LEFT JOIN groupmembers on (groups.gid = groupmembers.gid)
					WHERE groups.gid = '$gid'
					GROUP BY groups.gid";
		$this->db_query($q);
		$retval = $this->db_fetch_row();
		return $retval;
	}

	function getgroupmembers($name)
	{
		$this->db_query("SELECT groupmembers.* FROM groupmembers
					LEFT JOIN users ON users.uid = groupmembers.uid
					LEFT JOIN groups ON groupmembers.gid = groups.gid
					WHERE groups.name = '$name' AND users.blocked < 1");
		$ret = array();
		while($row = $this->db_next())
		{
			$ret[$row["uid"]] = $row["uid"];
		}
		return $ret;
	}

	////
	// !Grupi kasutjate nimekiri, returns array[uid] = uid
	function getgroupmembers2($gid)
	{
		$this->db_query("SELECT groupmembers.*,users.join_form_entry as join_form_entry FROM groupmembers 
										 LEFT JOIN users ON users.uid = groupmembers.uid
										 WHERE gid = $gid AND users.blocked < 1");
		$ret = array();
		while ($row = $this->db_next())
		{
			$ret[$row["uid"]] = $row[uid];
		}

		return $ret;
	}

	// removes the user from this group and all below it
	function remove_users_from_group_rec($gid,$users,$checkdyn = false)
	{
		$this->remove_users_from_group($gid,$users,$checkdyn);

		$grps = array();
		$this->getgroupsbelow($gid,&$grps);
		reset($grps);
		while(list(,$v) = each($grps))
		{
			$this->remove_users_from_group($v,$users,$checkdyn);
		}
	}

	function remove_users_from_group($gid,$users,$checkdyn = false) {
		if (is_array($users)) 
		{
			if ($checkdyn)
			{
				$grp = $this->fetchgroup($gid);
				$dyn = $grp[type] == 2 ? true : false;
			}

			$garr = $this->get_grp_parent_grps($gid);
			$garr[$gid] = $gid;
			$gstr = join(",",$garr);

			while(list(,$k) = each($users)) 
			{
				// if checking is on, and the group is dynamic
				// then update user record so that he will not be reinserted into that group again
				if ($checkdyn && $dyn)
				{
					$user = $this->fetch($k);
					$udata = unserialize($user[exclude_grps]);
					$udata[$gid] = 1;
					$this->save(array("uid" => $k, "exclude_grps" => serialize($udata)));
				}

				$q = "DELETE FROM groupmembers WHERE uid = '$k' AND gid IN ($gstr)";
				$this->db_query($q);
			};
		};
		$this->_log("group","Kustutas grupist $gid kasutajad ".join(",",$users));
	}

	function is_member($uuid, $gid)
	{
		$this->db_query("SELECT * FROM groupmembers WHERE uid='$uuid' AND gid=$gid");
		return $this->db_next();
	}

	// recursively addss users to group, iow adds the user to this group and all above it
	function add_users_to_group_rec($gid,$users,$permanent = false,$check = true)
	{
		$grps = array();
		$this->getgroupsabove($gid,&$grps);
		reset($grps);
		while(list(,$v) = each($grps))
		{
			$this->add_users_to_group($v,$users,$permanent,$check);
		}
	}

	function add_users_to_group($gid,$users,$permanent = 0,$check = false) {
		$t = time();
		global $uid;
		if (is_array($users)) 
		{
			$garr = $this->get_grp_parent_grps($gid);
			$garr[$gid] = $gid;
			while(list(,$k) = each($users)) 
			{
				$permanent = $permanent ? 1 : 0;

				reset($garr);
				while (list(,$v) = each($garr))
				{
					if ($check)
						// if checking is on and suer is already a member, take next user
						if ($this->is_member($k,$v))
							continue;

					$q = "INSERT INTO groupmembers (gid,uid,created,createdby,permanent) VALUES('$v','$k','$t','$uid',$permanent)";
					$this->db_query($q);
				}
			};
		};
		$this->_log("group","Lisas gruppi $gid kasutajad ".join(",",$users));
	}

	function getgroupsforuser($uid)
	{
		$this->db_query("SELECT * FROM groupmembers WHERE uid = '$uid'");
		$ret = array();
		while ($row = $this->db_next())
			$ret[$row[gid]] = $row[uid];

		return $ret;
	}

	function add($data) 
	{
		extract($data);
		$t = time();

		// kodukataloom
		$hfid = $this->new_object(array("parent" => 1, "name" => $uid, "class_id" => 1, "comment" => $uid." kodukataloog"),false);
		$this->db_query("INSERT INTO menu (id,type) VALUES($hfid,".MN_HOME_FOLDER.")");

		// teeme kasutaja
		$this->db_query("INSERT INTO users (uid,password,created,join_form_entry,email,home_folder,join_grp) VALUES('$uid','$password',$t,'$join_form_entry','$email',$hfid,'$join_grp')");

		// teeme default grupi
		$oid = $this->new_object(array("name" => $uid, "class_id" => CL_USER_GROUP, "status" => 2));
		$this->db_query("INSERT INTO groups (name,createdby,created,type,priority,oid)	VALUES('$uid','system',$t,1,".USER_GROUP_PRIORITY.",$oid)");
		$gid = $this->db_fetch_field("SELECT gid FROM groups WHERE name = '$uid' AND type = 1","gid");

		// lisame kasutaja default grupi liikmex
		$this->db_query("INSERT INTO groupmembers (gid,uid,created) VALUES ('$gid','$uid',$t)");

		// lisame kasutaja k6ikide kasutajate grupi liikmex
		global $all_users_grp;
		if ($all_users_grp)
		{
			$this->db_query("INSERT INTO groupmembers(gid,uid,created) VALUES($all_users_grp,'$uid',$t)");
		}

		// anname kodukataloomale k6ik 6igused
		$this->create_obj_access($hfid,$uid);
		// ja v6tame teistelt k6ik 6igused kodukataloomale 2ra
		$this->deny_obj_access($hfid);

		$this->_log("user",$GLOBALS["uid"]." lisas kasutaja $uid");
	}

	function deletegroup($gid)
	{
		if (!is_array($this->grpcache))
			$this->mk_grpcache();

		$this->delete_object($this->grpcache[$gid][oid]);
		$this->db_query("DELETE FROM groups WHERE gid = $gid");
		$this->db_query("DELETE FROM groupmembers WHERE gid = $gid");
//		$this->db_query("DELETE FROM groupmembergroups WHERE parent = $gid OR child = $gid");
		$this->db_query("DELETE FROM acl WHERE gid = $gid");

		if (!is_array($this->grpcache[$gid]))
			return;

		reset($this->grpcache[$gid]);
		while (list(,$v) = each($this->grpcache[$gid]))
			$this->deletegroup($v[gid]);
	}

	function get_gid_by_uid($uid)
	{
		return $this->db_fetch_field("SELECT gid FROM groups WHERE name = '$uid' AND type = 1","gid");
	}

	function update_dyn_group($gid)
	{
		// rite. here we must do the search and update group membership

		// slurp information about the group and it's members.
		$gr = $this->fetchgroup($gid);

		// et like otsimisvorm
		$sfid = $gr[search_form];

		// now do the search
		$f = new form();
		$f->load($sfid);
		$matches = $f->search($gr[data]);

		// find all the users that have those join form entries that match

		// make a list of all users
		$users = array();
		$this->db_query("SELECT * FROM users WHERE uid != '' ");
		while ($row = $this->db_next())
		{
			// make sure we don't addd users that are set as not to be added to this group. 
			$udata = unserialize($row[exclude_grps]);
			if (!$udata[$gid])
			{
				$jf = unserialize($row[join_form_entry]);
				if (is_array($jf))
				{
					reset($jf);
					while (list($fid, $eid) = each($jf))
					{
						$users[$eid] = $row[uid];
					}
				}
			}
		}

		$cmembers = $this->getgroupmembers2($gid);

		$toadd = array();

		reset($matches);
		while (list($fid,$ar) = each($matches))
		{
			reset($ar);
			while (list(,$eid) = each($ar))
			{
				$u_uid = $users[$eid];
				if (!$cmembers[$u_uid])
				{
					$toadd[$u_uid] = $u_uid;
				}
				unset($cmembers[$u_uid]);
			}
		}

		// now toadd contains users to add and $cmembers contains users to remove

		// but before removing, we must check if the user-group relationship is marked as permanent
		// and if it is, we shouldn't remove it

		$ttr = array();
		$perm = $this->getpermanentmembers($gid);
		reset($cmembers);
		while (list(,$v) = each($cmembers))
		{
			if ($perm[$v] != $v)
			{
				$ttr[] = $v;
			}
		}

		$this->remove_users_from_group_rec($gid,$ttr);

		$this->add_users_to_group_rec($gid,$toadd);

		// oh fuck this was a bitch.
	}

	// this gets called, when user changes his/her information on joins
	// so we can check into which groups he/she belongs to
	function update_dyn_user($uid)
	{
		// ok, this is the shitty part, because we must do all the searches for all the groups
		// because the user might belong in any of them
		$user = $this->fetch($uid);

		$ugrps = $this->getgroupsforuser($uid);

		$toadd = array();
		$toremove = array();

		$f = new form();

		$this->listgroups(-1,-1,2);
		while($group = $this->db_next())
		{
			// do the search for the group
			if (!$group[search_form])
			{
				continue;
			}

			$f->load($group[search_form]);
			$mt = $f->search($group[data]);

			// check if the user is in the result set
			$jfs = unserialize($user[join_form_entry]);

			$in = false;
			reset($mt);
			while(list($efid, $ar) = each($mt))
			{
				while(list(,$v) = each($ar))
				{
					if ($jfs[$efid] == $v)
					{
						$in = true;
					}
				}
			}

			if ($in)
			{
				// ok, in the result, that means, user must be in $group
				if (!$ugrps[$group[gid]])
				{
					$toadd[] = $group[gid];
				}
			}
			else
			{
				// ok, not in the result, that means, user must not be in $group
				if ($ugrps[$group[gid]])
				{
					// user already in group, remove
					$toremove[] = $group[gid];
				}
			}
		}

		$auid = array();
		$auid[] = $uid;

		$excludes = unserialize($user[exclude_grps]);

		// ok, here we add the user to all the groups he should be in
		// but we must first check if the user must not be in that group
		reset($toadd);
		while(list(,$v) = each($toadd))
		{
			if (!$excludes[$v])
			{
				$this->add_users_to_group_rec($v,$auid);
			}
		}

		// here we remove the suer from all the groups we should, but we should check if
		// the connection is permanent and not remove the user, if this is the case
		$perms = $this->getpermanentconnections($uid);

		reset($toremove);
		while(list(,$v) = each($toremove))
		{
			if ($perms[$v] != $v)
				$this->remove_users_from_group_rec($v,$auid);
		}
	}

	function getpermanentmembers($gid)
	{
		$ret = array();
		$this->db_query("SELECT * FROM groupmembers WHERE gid = $gid AND permanent = 1");
		while ($row = $this->db_next())
			$ret[$row[uid]] = $row[uid];

		return $ret;
	}

	function getpermanentconnections($uid)
	{
		$ret = array();
		$this->db_query("SELECT * FROM groupmembers WHERE uid = '$uid' AND permanent = 1");
		while ($row = $this->db_next())
			$ret[$row[gid]] = $row[gid];

		return $ret;
	}

	function getgroupsbelow($gid,&$arr)
	{
		if (!is_array($this->grpcache))
			$this->mk_grpcache();

		if (!is_array($this->grpcache[$gid]))
			return;

		reset($this->grpcache[$gid]);
		while (list(,$v) = each($this->grpcache[$gid]))
		{
			$arr[] = $v[gid];
			$this->getgroupsbelow($v[gid],&$arr);
		}
	}

	function getgroupsabove($gid,&$arr)
	{
		if (!is_array($this->grpcache2))
			$this->mk_grpcache();

		while ($gid != 0)
		{
			if ($this->grpcache2[$gid][type] == GRP_DEFAULT)
			{
				// ignore the user group so you won't add lotsa people ya don't need into it.
				$gid = 0;
			}
			else
			{
				$arr[] = $gid;
				$gid = $this->grpcache2[$gid][parent];
			}
		}
	}

	function mk_grpcache()
	{
		// make a list of all groups, for use later
		// right now update only normal groups, not dyngroups
		$this->grpcache = array();
		$this->grpcache2 = array();
		$this->db_query("SELECT * FROM groups WHERE type != 3");
		while ($row = $this->db_next())
		{
			$this->grpcache[$row[parent]][] = $row;
			$this->grpcache2[$row[gid]] = $row;
		}
	}

	function get_member_groups_for_gid($gid)
	{
		$ret = array();
		$this->db_query("SELECT * FROM groupmembergroups WHERE parent = $gid");
		while ($row = $this->db_next())
			$ret[$row[child]] = $row;

		return $ret;
	}

	function add_grpgrp_relation($parent,$child)
	{
		$this->db_query("INSERT INTO groupmembergroups(parent,child,created,createdby)
					VALUES($parent,$child,".time().",'".$GLOBALS["uid"]."')");

		$uarr = $this->getgroupmembers2($child);
		$this->add_users_to_group_rec($parent,$uarr);
	}

	function remove_grpgrp_relation($parent,$child)
	{
		$this->db_query("DELETE FROM groupmembergroups
					WHERE parent = $parent AND child = $child");

		$uarr = $this->getgroupmembers2($child);
		$this->remove_users_from_group_rec($parent,$uarr);
	}

	function get_grp_parent_grps($gid)
	{
		$ret = array();
		$this->db_query("SELECT * FROM groupmembergroups WHERE child = $gid");
		while ($row = $this->db_next())
		{
			// we must also recursively find the parent groups of this group
			$this->save_handle();
			$tar = $this->get_grp_parent_grps($row[parent]);
			$this->restore_handle();
			$ret[$row[parent]] = $row[parent];
			while (list($k,) = each($tar))
				$ret[$k] = $k;
		}

		return $ret;
	}

	function get_grp_level($gid)
	{
		// @desc: returns the level the group is at in the groups hierarchy
		$gar = array();
		$this->getgroupsabove($gid,$gar);
		return count($gar);
	}

	function check_environment(&$sys, $fix = false)
	{
		$op_table = array(
			"name" => "users", 
			"fields" => array(
				"uid" => array("name" => "uid", "length" => 50, "type" => "string", "flags" => ""),
				"password" => array("name" => "password", "length" => 32, "type" => "string", "flags" => ""),
				"created" => array("name" => "created", "length" => 11, "type" => "int", "flags" => ""),
				"createdby" => array("name" => "createdby", "length" => 50, "type" => "string", "flags" => ""),
				"modified" => array("name" => "modified", "length" => 11, "type" => "int", "flags" => ""),
				"modifiedby" => array("name" => "modifiedby", "length" => 50, "type" => "string", "flags" => ""),
				"logins" => array("name" => "logins", "length" => 11, "type" => "int", "flags" => ""),
				"ip" => array("name" => "ip", "length" => 100, "type" => "string", "flags" => ""),
				"blockedby" => array("name" => "blockedby", "length" => 50, "type" => "string", "flags" => ""),
				"lang_id" => array("name" => "lang_id", "length" => 11, "type" => "int", "flags" => ""),
				"online" => array("name" => "online", "length" => 11, "type" => "int", "flags" => ""),
				"lastaction" => array("name" => "lastaction", "length" => 11, "type" => "int", "flags" => ""),
				"join_form_entry" => array("name" => "join_form_entry", "length" => 65535, "type" => "blob", "flags" => ""),
				"mailbox_conf" => array("name" => "mailbox_conf", "length" => 65535, "type" => "blob", "flags" => ""),
				"exclude_grps" => array("name" => "exclude_grps", "length" => 65535, "type" => "blob", "flags" => ""),
				"blocked" => array("name" => "blocked", "length" => 11, "type" => "int", "flags" => ""),
				"email" => array("name" => "email", "length" => 255, "type" => "string", "flags" => ""),
				"home_folder" => array("name" => "home_folder", "length" => 11, "type" => "int", "flags" => ""),
				"join_grp" => array("name" => "join_grp", "length" => 200, "type" => "string", "flags" => ""),
				"msg_inbox" => array("name" => "msg_inbox", "length" => 20, "type" => "int", "flags" => ""),
				"messenger" => array("name" => "messenger", "length" => 65535, "type" => "blob", "flags" => ""),
				"config" => array("name" => "config", "length" => 65535, "type" => "blob", "flags" => ""),
			)
		);

		$op2_table = array(
			"name" => "groups", 
			"fields" => array(
				"gid" => array("name" => "gid", "length" => 11, "type" => "int", "flags" => ""),
				"name" => array("name" => "name", "length" => 255, "type" => "string", "flags" => ""),
				"createdby" => array("name" => "createdby", "length" => 50, "type" => "string", "flags" => ""),
				"created" => array("name" => "created", "length" => 11, "type" => "int", "flags" => ""),
				"modified" => array("name" => "modified", "length" => 11, "type" => "int", "flags" => ""),
				"modifiedby" => array("name" => "modifiedby", "length" => 50, "type" => "string", "flags" => ""),
				"type" => array("name" => "type", "length" => 11, "type" => "int", "flags" => ""),
				"data" => array("name" => "data", "length" => 11, "type" => "int", "flags" => ""),
				"parent" => array("name" => "parent", "length" => 11, "type" => "int", "flags" => ""),
				"priority" => array("name" => "priority", "length" => 11, "type" => "int", "flags" => ""),
				"oid" => array("name" => "oid", "length" => 11, "type" => "int", "flags" => ""),
				"search_form" => array("name" => "search_form", "length" => 11, "type" => "int", "flags" => ""),
			)
		);

		$op3_table = array(
			"name" => "groupmembers", 
			"fields" => array(
				"gid" => array("name" => "gid", "length" => 11, "type" => "int", "flags" => ""),
				"uid" => array("name" => "uid", "length" => 50, "type" => "string", "flags" => ""),
				"createdby" => array("name" => "createdby", "length" => 50, "type" => "string", "flags" => ""),
				"created" => array("name" => "created", "length" => 11, "type" => "int", "flags" => ""),
				"permanent" => array("name" => "permanent", "length" => 11, "type" => "int", "flags" => ""),
			)
		);

		$ret = $sys->check_admin_templates("automatweb/users", array());
		$ret.= $sys->check_site_templates("automatweb/users", array("homefolder.tpl"));
		$ret.= $sys->check_db_tables(array($op_table,$op2_table,$op3_table),$fix);

		return $ret;
	}
};
?>
