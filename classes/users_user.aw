<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/users_user.aw,v 2.103 2004/11/22 10:45:59 kristo Exp $
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

/*

EMIT_MESSAGE(MSG_USER_LOGIN);

*/

class users_user extends aw_template 
{
	function users_user() 
	{
		$this->init("");
	}

	// users tabelis on väli config, tyypi text, kuhu saab salvestada
	// igasugu misc informatsiooni, mida pole vaja kiiresti kätte saada,
	// aga mis on siiski oluline. Järgnevad 2 funktsiooni tegelevad
	// selle handlemisega.
	////
	// !Loeb kasutaja konfiguratsiooni sisse
	// uid - kasutaja
	// key - key, mille sisu teada soovitakse
	// $data = $users->get_user_config(array(
	//		"uid" => "duke",
	//		"key" => "coolness_factor",));
	function get_user_config($args = array())
	{
		extract($args);
		$udata = $this->_get_user_config($uid);
		if (!$udata)
		{
			return false;
		};
		$retval = aw_unserialize($udata["config"]);
		// return a single key if asked
		if ($key)
		{
			$retval = $retval[$key];
			//$retval = $tmp[$key];
		}
		// otherwise the whole config block
		return $retval;
	}

	function _get_user_config($uid)
	{
		$row = aw_cache_get("users_cache",$uid);
		if (!(is_array($row)))
		{
			$q = "SELECT config FROM users WHERE uid = '$uid'";
			$this->db_query($q);
			$row = $this->db_next();
		};
		return $row;
	}

	////
	// !Kirjutab kasutaja konfiguratsioonis mingi key yle
	// uid - kasutaja
	// key - võtme nimi
	// value - key väärtus. intenger, string, array, whatever
	// $users->set_user_config(array(
	//		"uid" => "duke",
	//		"key" => "coolness_factor",
	//		"value" => "99",));
	function set_user_config($args = array())
	{
		extract($args);
		// loeme vana konfi sisse
		$old = $this->_get_user_config($uid);
		if (!$old)
		{
			return false;
		};
		$config = aw_unserialize($old["config"]);
		if (is_array($data))
		{
			$config = array_merge($config,$data);
		}
		else
		{
			$config[$key] = $value;
		};
		$newconfig = aw_serialize($config);
		//if (($row = aw_cache_get("users_cache", $uid)))
		//{
			$row["config"] = $newconfig;
			aw_cache_set("users_cache", $uid, $row);
		//}
		$this->quote($newconfig);
		$q = "UPDATE users SET config = '$newconfig' WHERE uid = '$uid'";
		$this->db_query($q);
		return true;
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
	// !Logib kasutaja sisse
	function login($params = array())
	{
		global $uid;
		extract($params);
		$ip	= $params["remote_ip"];
		$host	= $params["remote_host"];
		$t = time();
		$msg = "";
		
		$do_auth = true;

		// eelnevad kommentaarid kaivad ka parooli kontrollimise kohta
		if (!is_valid("password",$password))
		{
			$msg = "Vigane v&otilde;i vale parool";
			$this->_log(ST_USERS, SA_LOGIN_FAILED, $msg);
			$load_user = false;
			$do_auth = false;
		}
		else
		if (!is_valid("uid",$uid))
		{
			$msg = "Vigane kasutajanimimi $uid";
			$this->_log(ST_USERS, SA_LOGIN_FAILED, $msg);
			$load_user = false;
			$do_auth = false;
		};
		
		$auth = get_instance(CL_AUTH_CONFIG);
		if ($do_auth && ($auth_id = $auth->has_config()))
		{
			list($success, $msg) = $auth->check_auth($auth_id, array(
				"uid" => $uid,
				"password" => $password
			));
		}
		else
		{
			$auth = get_instance(CL_AUTH_SERVER_LOCAL);
			list($success, $msg) = $auth->check_auth(NULL, array(
				"uid" => $uid,
				"password" => $password
			));
		}

		$this->msg = $msg;
	
		// all checks complete, result in $success, process it
		if (!$success)
		{
			$this->_log(ST_USERS, SA_LOGIN_FAILED, $msg);

			session_unregister("uid");
			aw_global_set("uid", "");
			$uid = "";
			// suck. 
			global $verbosity;
			if ($verbosity = 1)
			{
				$msg = "Vigane kasutajanimi või parool";
			}

			$_msg = aw_ini_get("users.login_failed_msg");
			if ($_msg != "")
			{
				$msg = $_msg;
			}			
			header("Refresh: 1;url=".$this->cfg["baseurl"]."/login.".$this->cfg["ext"]);
			print $msg;
			exit;
		};
		
		//If user logs on first time and there is setting in .ini file then he/she must chane password before login is compleated
		
		// njah. Mitte ei taha. Aga midagi yle ka ei jaa. Logime vaese bastardi sisse
		// HUZZAH!
		
		aw_disable_acl();
		$user_obj = &obj(users::get_oid_for_uid($uid));
		$logins = $user_obj->prop("logins") + 1;
		if ($logins < 2)
		{
			$user_obj->set_prop("logins", $logins);
			$user_obj->save(); 
		}
		aw_restore_acl();
		
		$this->_log(ST_USERS, SA_LOGIN, $uid);
		if (aw_ini_get("TAFKAP"))
		{
			setcookie("tafkap",$uid,strtotime("+7 years"));
		};
		
		setcookie("nocache",1);
		$_SESSION["uid"] = $uid;
		aw_global_set("uid", $uid);
		aw_session_set("uid_oid", $this->get_oid_for_uid($uid));

		$userconfig = $this->get_user_config(array(
			'uid' => aw_global_get("uid"),
		));
		
		aw_session_set('user_calendar', $userconfig['user_calendar']);
		aw_session_set('kliendibaas', $userconfig['kliendibaas']);	
		if (is_array($userconfig['aliasmgr_hist']))
		{
			aw_session_set('aliasmgr_obj_history',$aliasmgr_hist);
		}


		// init acl
		$this->request_startup();

		// notify listeners
		post_message("MSG_USER_LOGIN", array("uid" => $uid));

		// now that we got the whether he can log in bit cleared, try to find an url to redirect to
		// 1st is the url that was requested before the user was forced to login.
		// 2nd try to find the group based url and if that fails, then the everyone's url and then just the baseurl.
		// wow. is this graceful degradation or what!
		$this->url = aw_global_get("request_uri_before_auth");
		if (!$this->url)
		{
			$this->url = $this->find_group_login_redirect($uid);
		};
		if (!$this->url)
		{
			$la = get_instance("languages");
			$ld = $la->fetch(aw_global_get("lang_id"));

			$this->url = $this->get_cval("after_login_".$ld["acceptlang"]);
			if (!$this->url)
			{
				$this->url = $this->get_cval("after_login");
			}
		}
		$this->url = (strlen($this->url) > 0) ? $this->url : ($return != "" ? $return : $this->cfg["baseurl"]);
		$this->login_successful = true;
		if ($this->url[0] == "/")
		{
			$this->url = $this->cfg["baseurl"].$this->url;
		}

		return $this->url;
	}

	function require_password_change($uid)
	{
		$user_inst = get_instance(CL_USER);
		$gid_obj = $user_inst->get_highest_pri_grp_for_user($uid);
		if(is_object($gid) && $gid_obj->prop("require_change_pass"))
		{
			return true;
		}
	}
	
	function is_first_login($uid)
	{
		$user = &obj(users::get_oid_for_uid($uid));
		if(!$user->prop("logins"))
		{
			return true;
		}	
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
		if ($GLOBALS["redir_to"] != "")
		{
			return $GLOBALS["redir_to"];
		}
		else
		{
			return $this->cfg["baseurl"];
		}
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
		$q = "SELECT groups.gid as gid,groups.oid,count(groupmembers.gid) AS gcount
			FROM groups
			LEFT JOIN groupmembers on (groups.gid = groupmembers.gid)
			LEFT JOIN users ON users.uid = groupmembers.uid
			$ss 
			GROUP BY groups.gid,groups.oid
			$sufix";
		$this->db_query($q);
		$tmp = array();
		while ($row = $this->db_next())
		{
			$tmp[] = $row["gid"];
		}

		if (count($tmp) > 0)
		{
			$q = "select * from groups where gid in (".join(",", $tmp).")";
			$this->db_query($q);
		}

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
		$q = "
			SELECT groups.gid as gid,count(groupmembers.gid) AS gcount
			FROM groups
			LEFT JOIN groupmembers on (groups.gid = groupmembers.gid)
			LEFT JOIN users ON users.uid = groupmembers.uid
			WHERE $w_type
			GROUP BY groups.gid,groups.oid
		";
		$this->db_query($q);

		$tmp = array();
		while ($row = $this->db_next())
		{
			$tmp[] = $row["gid"];
		}

		if (count($tmp) > 0)
		{
			$q = "select * from groups where gid in (".join(",", $tmp).")";
			$this->db_query($q);
		}
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

	////
	// !returns list of groups, group_id => group_data
	// argumendid:
	// 	type(int or array) - mis tyypi gruppe listida?
	function get_group_list($arr)
	{
		$ret = array();
		$this->list_groups($arr);
		while ($row = $this->db_next())
		{
			$ret[$row["gid"]] = $row;
		}
		return $ret;
	}

	function addgroup($parent,$gname,$type=0,$data = 0,$priority = USER_GROUP_PRIORITY,$search_form = 0, $obj_parent = 0) 
	{
		$this->quote($gname);
		$uid = aw_global_get("uid");

		$pg = $this->fetchgroup($parent);

		$t = time();
		if (!$pg["oid"])
		{
			$pg["oid"] = aw_ini_get("groups.tree_root");
		}

		if ($obj_parent)
		{
			$pg["oid"] = $obj_parent;
		}

		$o = obj();
		$o->set_class_id(CL_GROUP);
		$o->set_name($gname);
		$o->set_status(STAT_ACTIVE);
		$o->set_parent($pg["oid"]);

		$o->set_prop("name", $gname);
		$o->set_prop("type", $type);
		$o->set_prop("gp_parent", $parent);
		$o->set_prop("priority", $priority);

		$gid = $this->db_fetch_field("SELECT MAX(gid) AS gid FROM groups", "gid")+1;
		$o->set_prop("gp_gid", $gid);

		$oid = $o->save();

		$this->_log(ST_GROUPS, SA_ADD, $gname);
		return $gid;
	}

	function fetchgroup($gid) 
	{
		$q = "SELECT groups.oid,groups.gid,count(groupmembers.gid) AS gcount,groups.priority FROM groups 
					LEFT JOIN groupmembers on (groups.gid = groupmembers.gid)
					WHERE groups.gid = '$gid'
					GROUP BY groups.gid,groups.oid,groups.priority";
		$this->db_query($q);
		$retval = $this->db_fetch_row();
		return $retval;
	}

	////
	// !Grupi kasutjate nimekiri, returns array[uid] = uid
	function getgroupmembers2($gid)
	{
		$this->db_query("SELECT groupmembers.*,users.join_form_entry as join_form_entry FROM groupmembers 
										 LEFT JOIN users ON users.uid = groupmembers.uid
										 WHERE gid = '$gid' AND users.blocked < 1");
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

	////
	// !adds an user
	// parameters:
	//	uid 
	//	password
	//	email
	//	join_grp
	//	join_form_entry
	//	use_md5_passwords - optional,if true, encodes password with md5, regardless of the default
	//	all_users_group - if set, overrides the system default
	//  no_add_user
	function add($data) 
	{
		extract($data);
		$t = time();
		if (!isset($data["obj_parent"]))
		{
			$obj_parent = max(aw_ini_get("users.root_folder"),1);
		}

		// kodukataloom
		aw_disable_acl();
		$o = obj();
		$o->set_parent(1);
		$o->set_name($uid);
		$o->set_class_id(CL_MENU);
		$o->set_comment($uid." kodukataloog");
		$o->set_prop("type", MN_HOME_FOLDER);
		$hfid = $o->save();
		aw_restore_acl();
		$this->hfid = $hfid;

		if (aw_ini_get("auth.md5_passwords") || $use_md5_passwords)
		{
			$password = md5($password);
		};

		// teeme default grupi
		aw_disable_acl();
		// in the bloody eau database the object with oid 1 is the groups folder. bloody hell.
		// this really needs a better solution :(
		$gid = $this->addgroup(0, $uid, GRP_DEFAULT, 0, USER_GROUP_PRIORITY, 0, (aw_ini_get("site_id") == 65 ? 5 : 1));
		aw_restore_acl();

		// lisame kasutaja default grupi liikmex
		$this->db_query("INSERT INTO groupmembers (gid,uid,created) VALUES ('$gid','$uid',$t)");

		// lisame kasutaja k6ikide kasutajate grupi liikmex
		if (!$all_users_grp)
		{
			$all_users_grp = aw_ini_get("groups.all_users_grp");
		}
		if ($all_users_grp)
		{
			$this->db_query("INSERT INTO groupmembers(gid,uid,created) VALUES($all_users_grp,'$uid',$t)");
		}

		// anname kodukataloomale k6ik 6igused
		$this->create_obj_access($hfid,$uid);
		// ja v6tame teistelt k6ik 6igused kodukataloomale 2ra
		$this->deny_obj_access($hfid);

		// kasutajaobjektile alati k6ik 6igused
		$this->create_obj_access($user_oid,$uid);

		// set username to all "uid" fields in all filled join forms
		$usjfe = new aw_array(aw_unserialize($join_form_entry));
		foreach($usjfe->get() as $usfid => $usfeid)
		{
			if ($usfid && $usfeid)
			{
				$f = get_instance("formgen/form");
				$f->load($usfid);
				$f->load_entry($usfeid);
				if (($el = $f->get_element_by_name("uid")))
				{
					$this->db_query("UPDATE form_".$usfid."_entries SET el_".$el->get_id()." = '$uid', ev_".$el->get_id()." = '$uid' WHERE id = '$usfeid'");
				}
			}
		}

		$this->_log(ST_USERS, SA_ADD, $uid);
	}

	function deletegroup($gid)
	{
		if (!is_array($this->grpcache))
		{
			$this->mk_grpcache();
		}

		aw_disable_acl();
		$tmp = obj($this->grpcache[$gid]["oid"]);
		if ($tmp->status() > 0)
		{
			$tmp->delete();
		}
		aw_restore_acl();
		$this->db_query("DELETE FROM groups WHERE gid = $gid");
		$this->db_query("DELETE FROM groupmembers WHERE gid = $gid");
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
				$this->add_users_to_group_rec($v,$auid, false, true, false);
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
				$this->remove_users_from_group_rec($v,$auid, false, true);
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

		$count = 0;
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

			if (++$count > 100)
			{
				error::throw(array(
					"id" => ERR_GROUP_HIER,
					"msg" => "Error in group hierarchy, count of 100 exceeded! probably offending group - $gid"
				));
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

	function find_group_login_redirect($uuid)
	{
		$c = get_instance("config");
		$ra = $c->get_grp_redir();

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
		$q = "SELECT groupmembers.gid AS gid, groups.priority as priority,groups.oid as oid FROM groupmembers
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

	function do_delete_user($uid)
	{
		$this->save(array(
			"uid" => $uid, 
			"blocked" => 1, 
			"blockedby" => aw_global_get("uid")
		));
		$this->savegroup(array(
			"gid" => $this->get_gid_by_uid($uid),
			"type" => 3
		));
		$this->_log(ST_USERS, SA_BLOCK_USER, aw_global_get("uid")." blocked user $uid");
	}

	function get_gid_for_oid($oid)
	{
		if (!($ret = aw_cache_get("get_gid_for_oid", $oid)))
		{
			$ret = $this->db_fetch_field("SELECT gid FROM groups WHERE oid = '$oid'", "gid");
			aw_cache_set("get_gid_for_oid", $oid, $ret);
		}
		return $ret;
	}

	function get_oid_for_gid($gid)
	{
		if (!($ret = aw_cache_get("get_oid_for_gid", $gid)))
		{
			$ret = $this->db_fetch_field("SELECT oid FROM groups WHERE gid = '$gid'", "oid");
			aw_cache_set("get_oid_for_gid", $gid, $ret);
		}
		return $ret;
	}

	function get_uid_for_oid($oid)
	{
		if (!($ret = aw_cache_get("get_uid_for_oid", $oid)))
		{
			$ret = $this->db_fetch_field("SELECT uid FROM users WHERE oid = '$oid'", "uid");
			aw_cache_set("get_uid_for_oid", $oid, $ret);
		}
		return $ret;
	}

	function get_oid_for_uid($uid)
	{
		if (!($ret = aw_cache_get("get_oid_for_uid", $uid)))
		{
			$ret = $this->db_fetch_field("SELECT oid FROM users WHERE uid = '$uid'", "oid");
			aw_cache_set("get_oid_for_uid", $uid, $ret);
		}
		return $ret;
	}

	//// 
	// !Returns user information
	// parameters:
	//	uid - required, the user to fetch
	//	field - optional, if set, only this field's value is returned, otherwise the whole record
	function get_user($args = false)
	{
		if (!is_array($args))
		{
			$uid = aw_global_get("uid");
		}
		else
		{
			extract($args);
		}
		if ($uid == "")
		{
			return false;
		}
		if (!is_array(($row = aw_cache_get("users_cache",$uid))))
		{
			$q = "SELECT * FROM users WHERE uid = '$uid'";
			$row = $this->db_fetch_row($q);
			aw_cache_set("users_cache",$uid,$row);
		}

		if (isset($field))
		{
			$row = $row[$field];
		}
		else
		{
			if (isset($row))
			{
				// inbox defauldib kodukataloogile, kui seda määratud pole
				$row["msg_inbox"] = isset($row["msg_inbox"]) ? $row["msg_inbox"] : $row["home_folder"];
			}
		}
		return $row;
	}
};
?>
