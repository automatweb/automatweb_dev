<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/users_user.aw,v 2.49 2003/01/20 14:25:50 kristo Exp $
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

class users_user extends aw_template 
{
	function users_user() 
	{
		$this->init("");
	}

	////
	// !Saadab alerdi (ebaonnestunud sisselogimine, vms) aadressile config.alert_addr
	function send_alert($msg)
	{
		$subject = sprintf(aw_ini_get("config.alert_subject"),aw_global_get("HTTP_HOST"));
		$msg = "IP: ".aw_global_get("REMOTE_ADDR")."\nTeade:" . $msg;
		mail(aw_ini_get("config.alert_addr"),$subject,$msg,aw_ini_get("config.alert_from"));
	}

	////
	// !do query to list all users. ig gid is set, only users from that group will be listed
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
		$users = array("'".aw_global_get("uid")."'");
		$this->listacl("objects.status != 0 AND objects.class_id = ".CL_GROUP);
		$this->db_query("SELECT groups.oid,groups.gid FROM groups LEFT JOIN objects ON objects.oid = groups.oid WHERE objects.status != 0");
		while ($row = $this->db_next())
		{
			$view = $this->can("view_users", $row["oid"]);
			if ($view)
			{
				// add all users of this group to list of users
				$this->save_handle();
				$ul = $this->getgroupmembers2($row["gid"]);
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
		// For perfomance reasons, touch only once per minute.
		$last_touch = aw_global_get("last_touch");
		if (($last_touch + 60) < $t)
		{
			$q = "UPDATE users SET lastaction = '$t' WHERE uid = '$user'";
			$this->db_query($q);
			aw_session_set("last_touch",$t);
		};
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
		extract($params);
		$ip	= $params["remote_ip"];
		$host	= $params["remote_host"];
		$t = time();
		$msg = "";

		// by default eeldame, et kasutaja on jobu ja ei saa
		// sisse logida
		$success	= false;
		$load_user 	= true;

		// eelnevad kommentaarid kaivad ka parooli kontrollimise kohta
		if (!is_valid("password",$password))
		{
			$msg = "Vigane v&otilde;i vale parool";
			$this->send_alert($msg);
			$this->_log(ST_USERS, SA_LOGIN_FAILED, $msg);
			$load_user = false;
		}
		else
		if (!is_valid("uid",$uid))
		{
			$msg = "Vigane kasutajanimimi $uid";
			$this->send_alert($msg);
			$this->_log(ST_USERS, SA_LOGIN_FAILED, $msg);
			$load_user = false;
		};

		if ($load_user)
		{
			$q = "SELECT * FROM users WHERE uid = '$uid' AND blocked = 0";
			$this->db_query($q);
			$udata = $this->db_next();
		};

		if (is_array($udata))
		{
			if (aw_ini_get("auth.md5_passwords"))
			{
				if (md5($password) == $udata["password"])
				{
					$success = true;
				};
			}
			else
			if ($password == $udata["password"])
			{
				$success = true;
			}
			else
			{
				$msg = sprintf(E_USR_WRONG_PASS,$uid,"");
				$this->send_alert($msg);
				$this->_log(ST_USERS, SA_LOGIN_FAILED, $msg);
			};
		}
		else
		{
			$msg = "Sellist kasutajat pole $uid";
			$this->send_alert($msg);
			$this->_log(ST_USERS, SA_LOGIN_FAILED, $msg);
			session_unregister("uid");
			aw_global_set("uid","");
			$uid = "";
		};

		$this->msg = $msg;

		// all checks complete, result in $success, process it
		if (!$success)
		{
			session_unregister("uid");
			aw_global_set("uid", "");
			$uid = "";
			// suck. 
			global $verbosity;
			if ($verbosity = 1)
			{
				$msg = "Vigane kasutajanimi või parool";
			}
			header("Refresh: 1;url=".$this->cfg["baseurl"]."/login.".$this->cfg["ext"]);
			print $msg;
			exit;
		};
		
		// njah. Mitte ei taha. Aga midagi yle ka ei jaa. Logime vaese bastardi sisse
		// HUZZAH!
		$q = "UPDATE users
					SET	logins = logins+1,
					ip = '$ip',
					lastaction = $t,
					online = 1
					WHERE uid = '$uid'";
		$this->db_query($q);
		$this->_log(ST_USERS, SA_LOGIN, $uid);
		if (aw_ini_get("TAFKAP"))
		{
			setcookie("tafkap",$uid,strtotime("+7 years"));
		};
		session_register("uid");
		aw_global_set("uid", $uid);

		// now that we got the whether he can log in bit cleared, try to find an url to redirect to
		// 1st try to find the group based url and if that fails, then the everyone's url and then just the baseurl.
		// wow. is this graceful degradation or what!
		$this->url = $this->find_group_login_redirect($uid);
		if (!$this->url)
		{
			$this->url = $this->get_cval("after_login");
		}
		$this->url = (strlen($this->url) > 0) ? $this->url : $this->cfg["baseurl"];
		$this->login_successful = true;
		if ($this->url[0] == "/")
		{
			$this->url = $this->cfg["baseurl"].$this->url;
		}
		return $this->url;
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
    aw_global_set("uid","");
    aw_session_set("uid","");
		$this->_log(ST_USERS, SA_LOGOUT ,$uid);
	}

	////
	// !Logib välja. Orb-i versioon
	function orb_logout($args)
	{
		extract($args);
		$this->logout(aw_global_get("uid"));
		session_destroy();
		return $this->cfg["baseurl"];
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
		{
			if ($k != "uid")
			{
				if ($k == "password")
				{
					if (aw_ini_get("auth.md5_passwords"))
					{
						$v = md5($v);
					}
				};
				$sets[] = " $k = '$v' ";
			}
		};
		$sets = join(",", $sets);

		$q = "UPDATE users SET $sets WHERE uid = '".$data["uid"]."'";
		$this->db_query($q);
		$this->_log(ST_USERS, SA_CHANGE, $data['uid']);
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

		$q = "UPDATE groups SET modified = ".time().", modifiedby = '".aw_global_get("uid")."', $sets WHERE gid = '".$data["gid"]."'";
		$this->db_query($q);
		$this->_log(ST_GROUPS, SA_CHANGE, $data['gid']);
	}

	///
	// FIXME: positsioneeritud parameetrid sakivad
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
			LEFT JOIN users ON users.uid = groupmembers.uid
			$ss 
			GROUP BY groups.gid
			$sufix";
		$this->db_query($q);
	}
	
	// This should eventually replace the previous function
	// argumendid:
	// type(int or array) - mis tyypi gruppe listida?
	function list_groups($args = array())
	{
		extract($args);
		if (is_array($type))
		{
			$w_type = join(" OR ",map("type = '%d'",$type));
		}
		else
		{
			$w_type = " type = $type";
		};
		$q = "SELECT * FROM groups WHERE $w_type";
		$this->db_query($q);
	}

	// argumendid:
	// type(int or array) - mis tyypi gruppe listida?
	function get_group_picker($arr)
	{
		$ret = array();
		$this->list_groups($arr);
		while ($row = $this->db_next())
		{
			$ret[$row["gid"]] = $row["name"];
		}
		return $ret;
	}

	function addgroup($parent,$gname,$type=0,$data = 0,$priority = USER_GROUP_PRIORITY,$search_form = 0) 
	{
		$this->quote($gname);
		$uid = aw_global_get("uid");

		$pg = $this->fetchgroup($parent);

		$t = time();
		if (!$pg["oid"])
		{
			$pg["oid"] = aw_ini_get("groups.tree_root");
		}
		$oid = $this->new_object(array("name" => $gname, "class_id" => CL_GROUP, "status" => 2, "parent" => $pg["oid"]));

		$q = "INSERT INTO groups (name,created,createdby,modified,modifiedby,type,data,parent,priority,oid,search_form)
			VALUES('$gname',$t,'$uid',$t,'$uid','$type','$data',$parent,$priority,$oid,$search_form)";
		$this->db_query($q);
		$this->_log(ST_GROUPS, SA_ADD, $gname);
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

	function remove_users_from_group($gid,$users,$checkdyn = false) 
	{
		if (is_array($users)) 
		{
			if ($checkdyn)
			{
				$grp = $this->fetchgroup($gid);
				$dyn = $grp["type"] == 2 ? true : false;
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
					$udata = unserialize($user["exclude_grps"]);
					$udata[$gid] = 1;
					$this->save(array("uid" => $k, "exclude_grps" => serialize($udata)));
				}

				$q = "DELETE FROM groupmembers WHERE uid = '$k' AND gid IN ($gstr)";
				$this->db_query($q);

				//magistrali korral võtan javaga yhendust
				if($this->cfg["site_id"]==12)
				{
					$acl_server_socket = fsockopen("127.0.0.1", 10000,$errno,$errstr,10);
					//teatan, et grupist $gid kustutati user $k 
					$str="1 ".$this->cfg["site_id"]." ".$k." ".$gid."\n";
					fputs($acl_server_socket,$str);
					fclose($acl_server_socket);
				}
			};
		};
		$this->_log(ST_GROUPS, SA_CHANGE_GRP_USERS, "Kustutas grupist $gid kasutajad ".join(",",$users));

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

	function add_users_to_group($gid,$users,$permanent = 0,$check = false) 
	{
		$t = time();
		$uid = aw_global_get("uid");
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
					{
						// if checking is on and suer is already a member, take next user
						if ($this->is_member($k,$v))
						{
							continue;
						}
					}

					$q = "INSERT INTO groupmembers (gid,uid,created,createdby,permanent) VALUES('$v','$k','$t','$uid',$permanent)";
					$this->db_query($q);
				}

				//magistrali korral võtan javaga yhendust
				if($this->cfg["site_id"]==12)
				{
					$acl_server_socket = fsockopen("127.0.0.1", 10000,$errno,$errstr,10);
					//teatan, et grupile $v lisadi user $uid
					$str="0 ".$this->cfg["site_id"]." ".$uid." ".$gid."\n";
					fputs($acl_server_socket,$str);
					fclose($acl_server_socket);
				}
			};
		};
		$this->_log(ST_GROUPS, SA_CHANGE_GRP_USERS,"Lisas gruppi $gid kasutajad ".join(",",$users));
	}

	function getgroupsforuser($uid)
	{
		$this->db_query("SELECT * FROM groupmembers WHERE uid = '$uid'");
		$ret = array();
		while ($row = $this->db_next())
		{
			$ret[$row["gid"]] = $row["uid"];
		}

		return $ret;
	}

	function add($data) 
	{
		extract($data);
		$t = time();

		// kodukataloom
		$hfid = $this->new_object(array("parent" => 1, "name" => $uid, "class_id" => 1, "comment" => $uid." kodukataloog"),false);
		$this->db_query("INSERT INTO menu (id,type) VALUES($hfid,".MN_HOME_FOLDER.")");


		if (aw_ini_get("auth.md5_passwords"))
		{
			$password = md5($password);
		};

		// teeme kasutaja
		$this->db_query("INSERT INTO users (uid,password,created,join_form_entry,email,home_folder,join_grp,created_hour,created_day,created_week,created_month,created_year,logins) VALUES('$uid','$password',$t,'$join_form_entry','$email',$hfid,'$join_grp','".date("H",$t)."','".date("d",$t)."','".date("w",$t)."','".date("m",$t)."','".date("Y",$t)."','0')");

		// teeme default grupi
		$oid = $this->new_object(array("name" => $uid, "class_id" => CL_USER_GROUP, "status" => 2));
		$this->db_query("INSERT INTO groups (name,createdby,created,type,priority,oid)	VALUES('$uid','system',$t,1,".USER_GROUP_PRIORITY.",$oid)");
		$gid = $this->db_fetch_field("SELECT gid FROM groups WHERE name = '$uid' AND type = 1","gid");

		// lisame kasutaja default grupi liikmex
		$this->db_query("INSERT INTO groupmembers (gid,uid,created) VALUES ('$gid','$uid',$t)");

		// lisame kasutaja k6ikide kasutajate grupi liikmex
		$all_users_grp = aw_ini_get("groups.all_users_grp");
		if ($all_users_grp)
		{
			$this->db_query("INSERT INTO groupmembers(gid,uid,created) VALUES($all_users_grp,'$uid',$t)");
		}

		// anname kodukataloomale k6ik 6igused
		$this->create_obj_access($hfid,$uid);
		// ja v6tame teistelt k6ik 6igused kodukataloomale 2ra
		$this->deny_obj_access($hfid);

		$this->_log(ST_USERS, SA_ADD, $uid);
	}

	function deletegroup($gid)
	{
		if (!is_array($this->grpcache))
		{
			$this->mk_grpcache();
		}

		$this->delete_object($this->grpcache[$gid]["oid"]);
		$this->db_query("DELETE FROM groups WHERE gid = $gid");
		$this->db_query("DELETE FROM groupmembers WHERE gid = $gid");
//		$this->db_query("DELETE FROM groupmembergroups WHERE parent = $gid OR child = $gid");
		$this->db_query("DELETE FROM acl WHERE gid = $gid");

		$this->_log(ST_USERS, SA_DELETE_GRP, $this->grpcache[$gid]["name"]);

		
		if (!is_array($this->grpcache[$gid]))
		{
			return;
		}

		reset($this->grpcache[$gid]);
		while (list(,$v) = each($this->grpcache[$gid]))
		{
			$this->deletegroup($v[gid]);
		}
	}

	function get_gid_by_uid($uid)
	{
		return $this->db_fetch_field("SELECT gid FROM groups WHERE name = '$uid' AND type = ".GRP_DEFAULT,"gid");
	}

	function update_dyn_group($gid)
	{
		// rite. here we must do the search and update group membership

		// slurp information about the group and it's members.
		$gr = $this->fetchgroup($gid);

		// et like otsimisvorm
		$sfid = $gr["search_form"];

		// now do the search
		$f = get_instance("formgen/form");
		$f->load($sfid);
		// FIXME: new search func needed
		$matches = $f->search($gr["data"]);

		// find all the users that have those join form entries that match

		// make a list of all users
		$users = array();
		// uid == "" oli kunagi kasutusel selleks, et saaks sisselogimata kasutajat gruppidesse panna ja talle õigusi anda.
		$this->db_query("SELECT * FROM users WHERE uid != '' ");
		while ($row = $this->db_next())
		{
			// make sure we don't addd users that are set as not to be added to this group. 
			$udata = unserialize($row["exclude_grps"]);
			if (!$udata[$gid])
			{
				$jf = unserialize($row["join_form_entry"]);
				if (is_array($jf))
				{
					reset($jf);
					while (list($fid, $eid) = each($jf))
					{
						$users[$eid] = $row["uid"];
					}
				}
			}
		}

		$cmembers = $this->getgroupmembers2($gid);

		$toadd = array();

		reset($matches);
		while (list(,$eid) = each($matches))
		{
			$u_uid = $users[$eid];
			if (!$cmembers[$u_uid])
			{
				$toadd[$u_uid] = $u_uid;
			}
			unset($cmembers[$u_uid]);
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

		$f = get_instance("formgen/form");

		$this->listgroups(-1,-1,2);
		while($group = $this->db_next())
		{
			// do the search for the group
			if (!$group["search_form"])
			{
				continue;
			}

			$f->load($group["search_form"]);
			// FIXME: new search func needed
			$mt = $f->search($group["data"]);

			// check if the user is in the result set
			$jfs = unserialize($user["join_form_entry"]);


			$in = false;
			$efid = $f->search_form;

			while(list(,$v) = each($mt))
			{
				if ($jfs[$efid] == $v)
				{
					$in = true;
				}
			}

			if ($in)
			{
				// ok, in the result, that means, user must be in $group
				if (!$ugrps[$group["gid"]])
				{
					$toadd[] = $group["gid"];
				}
			}
			else
			{
				// ok, not in the result, that means, user must not be in $group
				if ($ugrps[$group["gid"]])
				{
					// user already in group, remove
					$toremove[] = $group["gid"];
				}
			}
		}

		$auid = array();
		$auid[] = $uid;

		$excludes = unserialize($user["exclude_grps"]);

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
			{
				$this->remove_users_from_group_rec($v,$auid);
			}
		}
	}

	function getpermanentmembers($gid)
	{
		$ret = array();
		$this->db_query("SELECT * FROM groupmembers WHERE gid = $gid AND permanent = 1");
		while ($row = $this->db_next())
		{
			$ret[$row["uid"]] = $row["uid"];
		}

		return $ret;
	}

	function getpermanentconnections($uid)
	{
		$ret = array();
		$this->db_query("SELECT * FROM groupmembers WHERE uid = '$uid' AND permanent = 1");
		while ($row = $this->db_next())
		{
			$ret[$row["gid"]] = $row["gid"];
		}

		return $ret;
	}

	function getgroupsbelow($gid,&$arr)
	{
		if (!is_array($this->grpcache))
		{
			$this->mk_grpcache();
		}

		if (!is_array($this->grpcache[$gid]))
		{
			return;
		}

		reset($this->grpcache[$gid]);
		while (list(,$v) = each($this->grpcache[$gid]))
		{
			$arr[] = $v["gid"];
			$this->getgroupsbelow($v["gid"],&$arr);
		}
	}

	function getgroupsabove($gid,&$arr)
	{
		if (!is_array($this->grpcache2))
		{
			$this->mk_grpcache();
		}

		while ($gid != 0)
		{
			if ($this->grpcache2[$gid]["type"] == GRP_DEFAULT)
			{
				// ignore the user group so you won't add lotsa people ya don't need into it.
				$gid = 0;
			}
			else
			{
				$arr[] = $gid;
				$gid = $this->grpcache2[$gid]["parent"];
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
			$this->grpcache[$row["parent"]][] = $row;
			$this->grpcache2[$row["gid"]] = $row;
		}
	}

	function get_member_groups_for_gid($gid)
	{
		$ret = array();
		$this->db_query("SELECT * FROM groupmembergroups WHERE parent = $gid");
		while ($row = $this->db_next())
		{
			$ret[$row["child"]] = $row;
		}

		return $ret;
	}

	function add_grpgrp_relation($parent,$child)
	{
		$this->db_query("INSERT INTO groupmembergroups(parent,child,created,createdby)
					VALUES($parent,$child,".time().",'".aw_global_get("uid")."')");

		$this->_log(ST_GROUPS, SA_GRP_ADD_SUBGRP, "lisas grupi $parent sisse grupi $child");
		$uarr = $this->getgroupmembers2($child);
		$this->add_users_to_group_rec($parent,$uarr);
	}

	function remove_grpgrp_relation($parent,$child)
	{
		$this->db_query("DELETE FROM groupmembergroups
					WHERE parent = $parent AND child = $child");

		$this->_log(ST_GROUPS, SA_GRP_DEL_SUBGRP, "kustutas grupi $parent seest grupi $child");
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
			$tar = $this->get_grp_parent_grps($row["parent"]);
			$this->restore_handle();
			$ret[$row["parent"]] = $row["parent"];
			while (list($k,) = each($tar))
			{
				$ret[$k] = $k;
			}
		}

		return $ret;
	}

	////
	// returns the level the group is at in the groups hierarchy
	function get_grp_level($gid)
	{
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

	function find_group_login_redirect($uuid)
	{
		$c = get_instance("config");
		$ec = $c->get_simple_config("login_grp_redirect");
		$ra = aw_unserialize($ec);

		// kuna kasutaja pole veel sisse loginud, siis pole globallset gidlisti olemas, see tuleb leida
		$gidlist = $this->get_gids_by_uid($uuid);
		if (is_array($gidlist))
		{
			$d_gid = 0;
			$d_pri = 0;
			$d_url = "";
			foreach($gidlist as $gid)
			{
				if ($ra[$gid]["pri"] >= $d_pri && $ra[$gid]["url"] != "")
				{
					$d_gid = $gid;
					$d_pri = $ra[$gid]["pri"];
					$d_url = $ra[$gid]["url"];
				}
			}
			if ($d_url != "")
			{
				return $d_url;
			}
		}
		return false;
	}

	////
	// !tagastab array grupi id'dest, kuhu kasutaja kuulub
	// This function shouldn't be in the core, I think, since it's called only once,
	// from the site_header

	// - yeah. good point. - terryf
	// - can we store users groups inside her session? -- duke
	// - well. what if somebody else changes them, then they will not change for the user before logging in again - terryf
	// - I'm sure there are ways around _that_. Why I want this? It just seems utterly meaningless
	//   to read the group data at every request. 99% of the time, this data does not change.
	// - name one - terryf.
	// - sure. We read users table too at each request. That table has at least one column
	//   for metadata. So, let's use that. If someone changes any groups then AW sets a flag
	//   in that field - do_update_group_data = 1 for example - and sets its only for the users
	//   who belong to any of those groups.
	// - nope, we actually don't always query the users table. aw.com does, but that's in site::on_page()
	//   and it is done by messenger. but then again, maybe we should - then all users that get blocked while
	//   being logged in will get kicked out. ok, fair enough - let's make it do that. feel free to implement this. :) - terryf
	//   :* -- duke
	// btw, this query is very ineffective - uses a filesort - that is caused by ORDER BY priority
	// so - will anything break if I take it out? We will see.
	function get_gids_by_uid($uid, $ret_all = false)
	{
		$q = "SELECT groupmembers.gid AS gid, groups.priority as priority FROM groupmembers
			LEFT JOIN groups ON (groupmembers.gid = groups.gid) WHERE uid = '$uid'";
		$this->db_query($q);
		$retval = array();
		while($row = $this->db_next())
		{
			if ($ret_all)
			{
				$retval[(int)$row["gid"]] = $row;
			}
			else
			{
				$retval[(int)$row["gid"]] = (int)$row["gid"];
			}
		};

		return $retval;
	}
};
?>
