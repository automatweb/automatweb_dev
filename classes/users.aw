<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/users.aw,v 2.18 2001/08/08 06:08:10 kristo Exp $
classload("users_user","config","form");

load_vcl("table");

session_register("add_state");

global $orb_defs;

// you know what that means
$orb_defs["users"] = "xml";

class users extends users_user
{
	function users()
	{
		$this->db_init();
		$this->tpl_init("automatweb/users");
		lc_load("definition");
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

	function rpc_getuser($args = array())
	{
		$uid = $args[0];
		$q = "SELECT * FROM users WHERE uid = '$uid'";
		$this->db_query($q);
		$row = $this->db_next();
		$block = array();
		$block["uid"] = $row["uid"];
		$block["email"] = $row["email"];
		$block["logins"] = $row["logins"];
		$block["created"] = $row["created"];
		return $block;
	}

	function get_user_config($args = array())
	{
		extract($args);
		$udata = $this->_get_user_config($uid);
		if (!$udata)
		{
			return false;
		};
		// yeah. me salvestame selle info xml-is
		classload("xml");
		$xml = new xml(array("ctag" => "config"));
		$config = $xml->xml_unserialize(array(
					"source" => $udata["config"],
				));
		return $config[$key];
	}

	function _get_user_config($uid)
	{
		$q = "SELECT config FROM users WHERE uid = '$uid'";
		$this->db_query($q);
		return $this->db_next();
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
		$this->quote($args);
		extract($args);
		// loeme vana konfi sisse
		$old = $this->_get_user_config($uid);
		if (!$old)
		{
			return false;
		};
		classload("xml");
		$xml = new xml(array("ctag" => "config"));
		$config = $xml->xml_unserialize(array(
					"source" => $old["config"],
				));
		$config[$key] = $value;
		$newconfig = $xml->xml_serialize($config);
		$this->quote($newconfig);
		$q = "UPDATE users SET config = '$newconfig' WHERE uid = '$uid'";
		$this->db_query($q);
		return true;
	}
			

	function gen_select_list($gid,$all,$pickable = true)
	{
		$pg = $this->fetchgroup($gid);
		$can_edit = $this->can("edit",$pg[oid]);

		$this->read_template("sel_list.tpl");

		global $lookfor, $sortby;
		$t = new aw_table(array("prefix" => "users","sortby" => $sortby,"lookfor" => $lookfor,"imgurl" => $GLOBALS["baseurl"]."/vcl/img", "self" => $GLOBALS["PHP_SELF"].($gid ? "?gid=$gid" : "")));
		$t->parse_xml_def($GLOBALS["basedir"]."/xml/users/pickable.xml");

		$members = $this->getgroupmembers2($gid);

		if ($all)
			$this->listall();
		else
			$this->listall($gid);
		while ($row = $this->db_next())
		{
			$row[online] = $row[online] == 1 ? LC_YES : LC_NO;
			$row[uuid] = $row[uid];		// blah, gotta rename it, because "uid" is a used global variable :(

			// check if user is a member
			$vl = isset($members[$row[uid]]) ? "CHECKED" : "";
			$v2 = isset($members[$row[uid]]) ? 1 : 0;

			// um_$uid shows if the user is in the group or not
			// and us_$uid lets you change it
			if ($can_edit && $pickable)
				$row[check] = "<input type='hidden' NAME='um_".$row[uid]."' VALUE='$v2'><input type='checkbox' NAME='us_".$row[uid]."' VALUE='1' $vl>";
			else
				$row[check] = "";
			$t->define_data($row);
		}
		$t->sort_by(array("field" => $sortby));
		$this->vars(array("table"		=> $t->draw(),
											"gid"			=> $gid,
											"all"			=> $all,
											"urlgrp"	=> $this->make_url(array("parent" => $gid,"all" => 0,"groups" => 0)),
											"urlall"	=> $this->make_url(array("parent"	=> $gid,"all" => 1,"groups" => 0)),
											"urlgrps"	=> $this->make_url(array("parent"	=> $gid,"all" => 0,"groups" => 1)),
											"from"		=> $GLOBALS["REQUEST_URI"]));
		$this->vars(array("CAN_EDIT"=> ($can_edit && $pickable ? $this->parse("CAN_EDIT") : ""),
											"CAN_EDIT_2"=> ($can_edit && $pickable ? $this->parse("CAN_EDIT_2") : "")));
		return $this->parse();
	}

	////
	// !generates list of users. For internal use
	// I made this a separate function, because I'm going to need this functionality in
	// other places (e.g. messenger) besides the gen_list method in this file.

	// returns:
	//	array of users, in the form
	//	array(	"uid1" => acl_array,
	//		"uidn" => acl_array),

	// where acl_array can be either empty (in case we don't care about explicit
	// acl information) or contains the following values
	//		can_view,
	//		can_change,
	//		can_del
	// which are set to boolean true
	
	function _gen_usr_list($args = array())
	{
		extract($args);
		global $uid;
		$retval[$uid] = array(	"can_change" 	=> true,
					"can_view"	=> true,
					// no we don't let you suicide
					// um, why not exactly? - terryf
					"can_del"	=> false);	

		$this->listacl("objects.status != 0 AND objects.class_id = ".CL_GROUP);
	
		$q = "SELECT groups.oid,groups.gid
			FROM groups
			LEFT JOIN objects ON (objects.oid = groups.oid)
			WHERE objects.status != 0";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$can_change = $this->can("change_users", $row["oid"]);
			$can_view = $this->can("view_users", $row["oid"]);
			$can_del = $this->can("delete_users", $row["oid"]);
//			echo "grp $row[name] , oid = $row[oid] , can_change = $can_change <br>";
			if ($can_change || $can_view || $can_del)
			{
				// add all users of this group to list of users
				$this->save_handle();
				$ul = $this->getgroupmembers2($row["gid"]);
				reset($ul);
				while (list(,$u_uid) = each($ul))
				{
					$retval[$u_uid] = array();
					$retval[$u_uid]["can_view"] = ($can_view) ? true : false;
					$retval[$u_uid]["can_change"] = ($can_change) ? true : false;
					$retval[$u_uid]["can_del"] = ($can_del) ? true : false;
				}
				$this->restore_handle();
			}
		};
		return $retval;
	}

	////
	// !generates list of users
	function gen_list($arr)
	{
		if (!$this->prog_acl("view", PRG_USERS))
		{
			$this->prog_acl_error("view", PRG_USERS);
		}
		$this->read_template("list.tpl");

		global $lookfor, $sortby,$uid;
		$users = $this->_gen_usr_list();
		
		$uid_list = array_keys($users);
		
		// hmpf. Huvitav, kas IN klauslil mingi suuruspiirang ka on?
		// kui kasutajaid on ntx 2000, siis see päring voib ysna jube olla
			$q = sprintf("SELECT * FROM users WHERE uid IN(%s) AND blocked = 0",join(",",map("'%s'",$uid_list)));
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"uid"				=> $row["uid"], 
					"logs"				=> $row["logins"],
					"online"			=> $row["online"] == 1 ? LC_YES : LC_NO,
					"last"				=> $this->time2date($row["lastaction"],2),
					"change"			=> $this->mk_orb("change", array("id" => $row["uid"])),
					"delete"			=> $this->mk_orb("delete", array("id" => $row["uid"])),
					"change_pwd"			=> $this->mk_orb("change_pwd", array("id" => $row["uid"])),
					"settings" => $this->mk_my_orb("settings", array("id" => $row["uid"]))
				));
				$cc = ""; $cd = ""; $cpw = "";
				if ($users[$row["uid"]]["can_change"])
				{
					$cc = $this->parse("CAN_CHANGE");
					$cpw = $this->parse("CAN_PWD");
				}
				if ($users[$row["uid"]]["can_del"])
				{
					$cd = $this->parse("CAN_DEL");
				}
				$this->vars(array("CAN_CHANGE" => $cc, "CAN_DEL" => $cd, "CAN_PWD" => $cpw));
				$l.=$this->parse("LINE");
			}
		$this->vars(array(
			"LINE" => $l,
			"add"		=> $this->mk_orb("add_user", array())));
		$ad = "";
		if ($this->prog_acl("add", PRG_USERS))
		{
			$ad = $this->parse("ADD");
		}
		$this->vars(array("ADD" => $ad));
		return $this->parse();
	}

	////
	// !generates a list of visible users, using an user-defined template 
	function gen_plain_list($args = array())
	{
		extract($args);
		$users = $this->_gen_usr_list();
		$this->read_template($tpl);
		$c = "";
		foreach($users as $uuid => $acl)
		{
			$this->vars(array(
				"uid" => $uuid,
				"online" => "n/a",
			));
			$c .= $this->parse("line");
		};
		$this->vars(array("line" => $c));
		return $this->parse();
	}

	
	////
	// !deletes the objects from the users home folder that are selected
	function del_objects($arr)
	{
		extract($arr);
		if (is_array($delete))
		{
			reset($delete);
			while (list(,$id) = each($delete))
			{
				$this->delete_object($id);
			}
		}
		return $this->mk_site_orb(array("action" => "gen_home_dir", "id" => $parent));
	}

	////
	//! Genereerib sisselogitud kasutaja kodukataloogi
	function gen_home_dir($args = array())
	{
		global $udata,$baseurl,$class_defs;
		$parent = $args["id"];

		$tpl = ($args["tpl"]) ? $args["tpl"] : "homefolder.tpl";
		$this->read_template($tpl);

		$result = array();
		$startfrom = (!$parent) ? $udata["home_folder"] : $parent;

		$grps_by_parent = array();
		$grps = array();
	
		// we always start from the home folder
		$fldr = $udata["home_folder"];

		do
		{
			$groups = $this->get_objects_below(array(
						"parent" => $fldr,
						"class" => CL_PSEUDO,
				));

			foreach($groups as $key => $val)
			{
				$grps_by_parent[$val["parent"]][$key] = $val;
				$grps[$key] = $val["parent"];
			};

			$fldr = array_keys($groups);

		} while(sizeof($groups) > 0);

		$current = $startfrom;
		
		while ($udata["home_folder"] != $current)
		{
			$path[$current] = 1;
			$current = $grps[$current];
		}
		
		$path[$udata["home_folder"]] = 1;

		// security check. if the requested is outside or above the
		// users home folder, we will show him the home folder.

		// this should be done differently of course, by checking the
		// ACL of the respective document, but for now, we will settle
		// to this.
		if (!$grps[$startfrom])
		{
			$startfrom = $udata["home_folder"];
		};

		// we need a small cycle that passes all the oids-s and 
		// parents until we do find 

		$this->path = $path;
		$this->folders = "";
		$this->active = $startfrom;
		$this->_show_hf_folder($grps_by_parent,$udata["home_folder"]);

		
		// print sizeof($grps_by_parent[$udata["home_folder"]]);
		
		// we will always have to display the first level,
		// and then find out the parents of the currently
		// opened folder, and then we have to track the parents
		// back up to the home folder
	
		$thisone = $this->get_object($startfrom);
		$prnt = $this->get_object($thisone["parent"]);

		$q = "SELECT name,oid,class_id,name FROM objects
			WHERE objects.parent = '$startfrom' and objects.status != 0 and objects.class_id != 1";
		$this->db_query($q);

		$folders = "";
		$cnt = 0;
		while($row = $this->db_next())
		{
			$class = $class_defs[$row["class_id"]]["file"];
			$preview = $this->mk_my_orb("preview", array("id" => $row["oid"]),$class);
			$cnt++;
			switch ($row["class_id"])
			{
				case CL_PSEUDO:
					$tpl = "folder";
					break;
				
				case CL_FILE:
					$preview = $GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$row["oid"]."/".$row["name"];
					$iconurl = get_icon_url(CL_FILE,$row["name"]);
					$tpl = "doc";
					break;

				default:
					$iconurl = get_icon_url($row["class_id"],0);
					$tpl = "doc";
					break;
			};
			$this->vars(array(
				"name" => ($row["name"]) ? $row["name"] : "(nimetu)",
				"id" => $row["oid"],
				"iconurl" => $iconurl,
				"color" => ($cnt % 2) ? "#FFFFFF" : "#EEEEEE",
				"f_click" => $this->mk_my_orb("gen_home_dir", array("id" => $row["oid"])),
				"preview" => $preview,
				"change" => $this->mk_my_orb("change", array("id" => $row["oid"]),$class)
			));

			$folders .= $this->parse($tpl);
		};
		$delete = "";
		if ($cnt > 0)
		{
			$delete = $this->parse("delete");
		};

		$this->vars(array(
			"folder" => $folders,
			"doc" => "",
			"name" => $thisone["name"],
			"parent" => $startfrom,
			"delete" => $delete,
			"total" => $cnt,
			"folders" => $this->folders,
			"reforb1" => $this->mk_reforb("del_objects", array("parent" => $startfrom)),	// delete form
			"home_f" => $this->mk_my_orb("gen_home_dir"),
		));
		return $this->parse();
	}
	
	////
	// !for internal use
	function _show_hf_folder($items,$section)
	{
		static $indent = 0;
		$indent++;
		static $cnt = 0;
		if (!is_array($items))
		{
			$indent--;
			return;
		};

		while(list($key,$val) = each($items[$section]))
		{
			$cnt++;
			$this->vars(array(
					"id" => $val["oid"],
					"indent" => str_repeat("&nbsp;",$indent * 3),
					"name" => $val["name"],
					"color" => ($cnt % 2) ? "#EEEEEE" : "#FFFFFF",
				));
			$tpl = ($this->active == $key) ? "activefolder" : "folders";
			$this->folders .= $this->parse($tpl);
			if ( (is_array($items[$key])) && ($this->path[$key]))
			{
				$this->_show_hf_folder($items,$key);
			};
		}
		$indent--;
	}

	////
	// !creates a new folder int the users home folder
	function submit_add_folder($arr)
	{
		extract($arr);
		$id = $this->new_object(array("parent" => $parent, "class_id" => CL_PSEUDO, "status" => 2, "name" => $name));
		$this->db_query("INSERT INTO menu(id,type) values($id,".MN_HOME_FOLDER_SUB.")");
		global $status_msg;
		$status_msg = LC_USERS_FOLDER_ADDED;
		session_register("status_msg");

		return $this->mk_my_orb("gen_home_dir", array("id" => $parent));
	}

	////
	// !user changing from the admin interface
	function change($arr)
	{
		global $session_filled_forms;
		$session_filled_forms = array();

		$this->do_change($arr);
	}

	function get_jf_list($join_grp)
	{
		$ret = array();
		$this->db_query("SELECT id,j_name  FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 and forms.grp='$join_grp' AND forms.subtype = ".FSUBTYPE_JOIN." ORDER BY forms.j_order");
		while ($row = $this->db_next())
		{
			$ret[$row["id"]] = $row["j_name"];
		}
		return $ret;
	}

	function get_next_jf($join_grp)
	{
		global $session_filled_forms;

		// find all the forms in the selected join group 
		$this->db_query("SELECT id  FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 and forms.grp='$join_grp' AND forms.subtype = ".FSUBTYPE_JOIN);
		$jfrm = 0;
		while ($row = $this->db_next())
		{
			if (!$session_filled_forms[$row[id]])
			{
				$jfrm = $row[id];
				break;
			}
		}
		return $jfrm;
	}

	function do_change($arr)
	{
		extract($arr);

		$u = $this->fetch($id);
		$fs = unserialize($u[join_form_entry]);

		// iterate over the join forms
		$jfrm = $this->get_next_jf($u[join_grp]);

		if ($jfrm)
		{
			// show them one after another to the user
			$orb = $this->mk_orb("show", array("id" => $jfrm, "entry_id" => $fs[$jfrm], "extraids[redirect_after]" => urlencode($this->mk_orb("do_change", array("id" => $id), "users"))),"form");
			header("Location: $orb");
			return $orb;
		}
		else
		{
			// also, update users join form entries
			$this->save(array("uid" => $id, "join_form_entry" => serialize($GLOBALS["session_filled_forms"]))); 

			// zero out formgen's user data cache
			$this->set_user_config(array("uid" => $id, "key" => "user_info_cache", "value" => false));

			// and when we're dont with all of them, update dyn groups and return to user list
			$this->update_dyn_user($id);
			$orb = $this->mk_orb("gen_list", array());
			header("Location: $orb");
			return $orb;
		}
	}

	////
	// !generates the form for changing the users ($id) password
	function change_pwd($arr)
	{
		extract($arr);
		$this->mk_path(0,"<a href='".$this->mk_orb("gen_list", array()).LC_USERS_USERS);
		$u = $this->fetch($id);
		$this->read_template("changepwd.tpl");
		$this->vars(array("email" => $u[email],
				"error" => $error,
				"reforb" => $this->mk_reforb("submit_change_pwd", array("id" => $id))));
		return $this->parse();
	}

	////
	// !generates form for changing the password inside the site
	function user_change_pwd()
	{
		global $uid;
		$this->read_template("changeuserpwd.tpl");
		return $this->parse();
	}

	////
	// !saves the uses changed password
	function submit_change_pwd($arr)
	{
		extract($arr);
		if ($arr[pwd] != $arr[pwd2])
		{
			return $this->mk_orb("change_pwd", array("id" => $id, "error" => LC_USERS_PASSW_NOT_SAME));
		}

		if ($arr[pwd] != "")
			$this->save(array("uid" => $arr[id], "password" => $arr[pwd],"email" => $arr[email]));
		else
			$this->save(array("uid" => $arr[id], "email" => $arr[email]));

		return $this->mk_orb("gen_list", array());
	}

	////
	// !deletes the user
	function delete($arr)
	{
		extract($arr);
		$this->save(array("uid" => $id, "blocked" => 1, "blockedby" => UID));
		$this->savegroup(array("gid" => $this->get_gid_by_uid($id),"type" => 3));
		header("Location: ".$this->mk_orb("gen_list", array()));
	}

	////
	// !adds the user and ssets all join form entries from site interface
	function submit_user_site($arr)
	{
		extract($arr);

		global $add_state;
		$add_state[pass] = $pass;
		$add_state[uid] = $a_uid;
		$add_state[email] = $email;

		if ($this->can_add($arr))
		{
			$jfs = serialize($this->get_join_form_entries($join_grp));

			$this->add(array("join_form_entry" => $jfs, "uid" => $add_state[uid], "password" => $add_state[pass],"email" => $add_state[email], "join_grp" => $join_grp));
			$this->update_dyn_user($add_state[uid]);

			global $last_join_uid;
			$last_join_uid = $add_state["uid"];
			$uid = $add_state["uid"];
      $session = $this->gen_uniq_id();
			session_register("uid","session");
			$GLOBALS["uid"] = $uid;
			session_register("last_join_uid");

			// send him some email as well
			classload("config");
			$c = new config;
			$mail = $c->get_simple_config("join_mail");
			$mail = str_replace("#parool#", $add_state["pass"],$mail);
			$mail = str_replace("#kasutaja#", $add_state["uid"],$mail);
			$mail = str_replace("#liituja_andmed#", str_replace("\n\n","\n",$this->show_join_data(array("nohtml" => true))),$mail);

			mail($add_state["email"],$c->get_simple_config("join_mail_subj"),$mail,"From: ".MAIL_FROM);
			$add_state = "";
			$GLOBALS["session_filled_forms"] = array();

			return $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$after_join;
		}
		else
		{
			$add_state[level] = 0;
			return $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=$section";
		}

		return $this->mk_orb("add_user", array("level" => 1, "join_grp" => $join_grp));
	}

	////
	// !adds the user and ssets all join form entries from admin interface
	function submit_user($arr)
	{
		extract($arr);

		global $add_state;
		$add_state[pass] = $pass;
		$add_state[uid] = $a_uid;
		$add_state[email] = $email;

		if ($this->can_add($arr))
		{
			$jfs = serialize($this->get_join_form_entries($join_grp));

			$this->add(array("join_form_entry" => $jfs, "uid" => $add_state[uid], "password" => $add_state[pass],"email" => $add_state[email]));
			$this->update_dyn_user($add_state[uid]);

			$add_state = "";
			$GLOBALS["session_filled_forms"] = array();
			return $this->mk_orb("gen_list", array());
		}
		else
		{
			$add_state[level] = 0;
		}

		return $this->mk_orb("add_user", array("level" => 1, "join_grp" => $join_grp));
	}

	function check_chars($str)
	{
		$len = strlen($str);
		for ($i=0; $i < $len; $i++)
		{
			$c = substr($str,$i,1);
			if (!strstr("1234567890qwertyuiopasdfghjklzxcvbnm_",$c))
				return false;
		}

		return true;
	}

	function can_add($arr)
	{
		global $add_state;

		extract($arr);
		$q = "SELECT * FROM users WHERE uid = '$a_uid'";
		$this->db_query($q);
		$row = $this->db_next();
		if ($row)
		{
			$add_state[error] = "<b><font color='red'>Selline kasutaja juba on, vali uus kasutajanimi.</font></b>";
			return false;
		}

		if (!is_valid("uid",$a_uid))
		{
			$add_state[error] = "<b><font color='red'>Kasutajanimes tohib kasutada ainult t&auml;hti, numbreid ja allkriipsu.</font></b>";
			return false;
		}

		if ($pass != $pass2)
		{
			$add_state[error] = "<b><font color='red'>Paroolid ei kattu!</font></b>";
			return false;
		}

		if (!is_valid("password", $pass))
		{
			$add_state[error] = "<b><font color='red'>Paroolis tohib kasutada ainult t&auml;hti, numbreid ja allkriipsu</font></b>";
		return false;
		}

		if (strlen($a_uid) < 3)
		{
			$add_state[error] = "<b><font color='red'>Kasutajanimi peab olema v&auml;hemalt 3 t&auml;he pikkune.</font></b>";
			return false;
		}

		if (strlen($pass) < 3)
		{
			$add_state[error] = "<b><font color='red'>Parool peab olema v&auml;hemalt 3 t&auml;he pikkune.</font></b>";
			return false;
		}
		$add_state[error] = "";
		return true;
	}

	////
	// !this da thang, users added from the admin interface will use this function extensively. w00p!
	function add_user($arr)
	{
		extract($arr);
		$this->mk_path(0,"<a href='".$this->mk_orb("gen_list", array()).LC_USERS_USERS);
		// siin hoitaxe forme, mis kasutaja on selle sessiooni jooxul t2itnud.
		global $session_filled_forms,$add_state;

		if (!$level)
		{
			$session_filled_forms = array();
			$this->db_query("SELECT distinct(grp) as grp FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 and forms.subtype=".FSUBTYPE_JOIN);
			$jgrps = array();
			$found = false;
			while ($row = $this->db_next())
			{
				$jgrps[$row[grp]] = $row[grp];
				$found = true;
			}
			if ($found)
			{
				$this->read_template("sel_join_grp.tpl");
				$this->vars(array("reforb" => $this->mk_reforb("add_user", array("level" => 1)),
													"join_grps" => $this->picker(0,$jgrps)));
				return $this->parse();
			}
			else
			{
				$this->read_template("add.tpl");
				$this->vars(array("error" => $add_state[error], "uid" => $add_state[uid],"email" => $add_state[email],
													"reforb"	=> $this->mk_reforb("submit_user", array("join_grp" => $join_grp))));
				return $this->parse();
			}
		}
		else
		{

			// find all the forms in the selected join group 
			$this->db_query("SELECT id  FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 and forms.grp='$join_grp' AND forms.subtype = ".FSUBTYPE_JOIN);
			$jfrm = 0;
			while ($row = $this->db_next())
			{
				if (!$session_filled_forms[$row[id]])
				{
					$jfrm = $row[id];
					break;
				}
			}

			if ($jfrm)
			{
				// show them one after another to the user
				$orb = $this->mk_orb("show", array("id" => $jfrm, "extraids[redirect_after]" => urlencode($this->mk_orb("add_user", array("level" => 1, "join_grp" => $join_grp), "users"))),"form");
				header("Location: $orb");
				return $orb;
			}
			else
			{
				// and when we're dont with all of them, let the user select username/password
				$this->read_template("add.tpl");
				$this->vars(array("error" => $add_state[error], "uid" => $add_state[uid],"email" => $add_state[email],
													"reforb"	=> $this->mk_reforb("submit_user", array("join_grp" => $join_grp))));
				return $this->parse();
			}
		}
	}

	function get_join_form($after_join)
	{
		// siin hoitaxe forme, mis kasutaja on selle sessiooni jooxul t2itnud.
		global $session_filled_forms;

/*		classload("config");
		$t = new db_config;
		$jfs = unserialize($t->get_simple_config("user_add_forms"));*/
		$jfs = array();
		$this->db_query("SELECT objects.*,forms.grp as grp,forms.j_mustfill as j_mustfill FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 AND objects.site_id = ".$GLOBALS["SITE_ID"]." AND forms.subtype = ".FSUBTYPE_JOIN);
		while ($row = $this->db_next())
		{
			// paneme siia arraysse aint need formid, mida PEAB t2itma, teisi v6ime ignoreerida
			if ($row["j_mustfill"] == 1)
			{
				$jfs[$row["oid"]] = array("group" => $row["grp"]);
			}
		}

//			echo "<pre>",var_dump($jfs)."</pre>";
		// nini nyyd on vaja tshekkida et kas k6ik vajalikud formid on t2idetud
		$groups = array();
		reset($jfs);
		// teeme gruppide nimekirja
		while (list($fid,$ar) = each($jfs))
		{
			$groups[$ar["group"]][$fid] = $session_filled_forms[$fid];
//				echo "fid $fid ", $session_filled_forms[$fid],"<br>";
		}

		// k2ime grupid l2bi ja tshekime, et kas m6ni on tervenisti t2idetud
		$group_filled = false;
		reset($groups);
		while (list($group,$ar) = each($groups))
		{
			$all_filled = true;
			reset($ar);
			while (list($fid,$filled) = each($ar))
			{
				if (!$filled)
				{
					$all_filled = false;
				}
			}

			if ($all_filled)
			{
				// leidsime grupi, kus k6ik on t2idetud
				$group_filled = true;
				$add_group = $group;
				break;
			}
		}

		if ($group_filled)
		{
			global $add_state;
			// n2itame kasutaja tegemise formi
			$this->read_template("add_site.tpl");
			$this->vars(array("error" => $add_state[error], "uid" => $add_state[uid],"email" => $add_state[email],
												"reforb"	=> $this->mk_reforb("submit_user_site", array("join_grp" => $add_group, "section" => $GLOBALS["section"], "after_join" => $after_join))));
			return $this->parse();
//			return $this->gen_add_form($GLOBALS["baseurl"]."/refcheck.".$GLOBALS["ext"],$add_group);
		}
		else
		{
			// kribame et mine ja t2ida k6ik regimisformid 2ra
			$this->read_template("add_not_all_forms.tpl");
			return $this->parse();
		}
	}

	//// 
	// !tagastab nimekirja formi sisestustest, mis on kasutaja t2itnud ja mis kuuluvad kasutaja liitumisformide gruppi $group
	function get_join_form_entries($group)
	{
		global $session_filled_forms;

		$ret = array();

		$this->db_query("SELECT id,grp FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 AND subtype = ".FSUBTYPE_JOIN." AND grp = '$group'");
		// teeme gruppide nimekirja
		while ($row = $this->db_next())
		{
			$ret[$row[id]] = $session_filled_forms[$row[id]];
		}
		return $ret;
	}

	function do_change_site($fid)
	{
		$id = $GLOBALS["uid"];
		if ($id == "")
		{
			return LC_USERS_NOT_LOGGED_IN;
		}

		// zero out formgen's user data cache
		$this->set_user_config(array("uid" => $id, "key" => "user_info_cache", "value" => false));

		$this->update_dyn_user($id);
		$u = $this->fetch($id);
		$fs = unserialize($u[join_form_entry]);

		$t = new form;
		return $t->gen_preview(array("id" => $fid, "entry_id" => $fs[$fid], "extraids" => array("redirect_after" => $GLOBALS["baseurl"]."/?special=2&fid=$fid")));
	}

	////
	// !shows the data the user entered when he joined if the user is logged in
	// if he is not logged in, but just joined, then the session contains the variable $last_join_uid and then that will be used
	// the for outputs for the data will be taken from config where when selecting join forms the user can select the output for all join forms
	// paramtaters:
	//  nohtml - if true, the output generated can be sent to an email
	//  user - the username for whom to show the data
	function show_join_data($arr)
	{
		extract($arr);
		if (!$tpl)
		{
			$tpl = "show_join_data.tpl";
		}
		global $uid,$last_join_uid;
		$uuid = $uid;
		if ($uuid == "")
		{
			$uuid = $user;
		}
		if ($uuid == "")
		{
			$uuid = $last_join_uid;
		}
		if ($uuid == "")
		{
			return "";
		}

		// now get all the join forms for thew users join group and show dem!
		$ops = array();
		$aps = "";
		if ($second)
		{
			$aps = "2";
		}
		$this->db_query("SELECT id,j_op".$aps." FROM forms WHERE subtype = ".FSUBTYPE_JOIN);
		while ($row = $this->db_next())
		{
			$ops[$row["id"]] = $row["j_op".$aps];
		}

		$udata = $this->get_user(array("uid" => $uuid));
		$jf = unserialize($udata["join_form_entry"]);
		if (is_array($jf))
		{
			$f = new form();
			foreach($jf as $joinform => $joinentry)
			{
				$ret.=$f->show(array("id" => $joinform,"entry_id" => $joinentry, "op_id" => $ops[$joinform],"no_html" => $nohtml));
			};
		};
		if ($nohtml)
		{
			$this->read_template("show_join_data_nohtml.tpl");
		}
		else
		{
			$this->read_template($tpl);
		}
		$this->vars(array(
			"username" => $uuid,
			"password" => $udata["password"]
		));
		$ret.=$this->parse();
		return $ret;
	}

	////
	// !shows the form where the user can enter his/her password and then sends a pre-defined email to the user
	function pwd_remind($arr)
	{
		extract($arr);
		$this->read_template("pwd_remind_form.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_pwd_remind", array("after" => $matches[1]))
		));

		return $this->parse();
	}

	////
	// !this actually sends the reminder-email
	function submit_pwd_remind($arr)
	{
		extract($arr);
		$udata = $this->fetch($username);
		if (!$udata)
		{
			$username = $this->db_fetch_field("SELECT uid FROM users WHERE email = '$username'","uid");
			$udata = $this->fetch($username);
		}	

		classload("config");
		$c = new config;
		$mail = $c->get_simple_config("remind_pwd_mail");
		$mail = str_replace("#parool#", $udata["password"],$mail);
		$mail = str_replace("#kasutaja#", $username,$mail);
		$mail = str_replace("#liituja_andmed#", str_replace("\n\n","\n",$this->show_join_data(array("nohtml" => true,"user" => $username))),$mail);

		mail($udata["email"],$c->get_simple_config("remind_pwd_mail_subj"),$mail,"From: ".MAIL_FROM);

		return $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$after;
	}

	////
	// !this is used by formgen to retrieve the data that the user $uid entered when he joined 
	// the return value is an array(element_name => element_value). 
	// the function caches the result for better performance
	// the cache needs to be zeroed out when the user changes his/her data
	function get_user_info($uid)
	{
		// yeah. use the cached version if available for better performance
		$dat = $this->get_user_config(array("uid" => $uid, "key" => "user_info_cache"));
		if (is_array($dat))
		{
			return $dat;
		}

		$elvalues = array();
		$udata = $this->get_user(array("uid" => $uid));
		$jf = unserialize($udata["join_form_entry"]);
		if (is_array($jf))
		{
			$elvs = array();
			$f = new form();
			foreach($jf as $joinform => $joinentry)
			{
				$f->load($joinform);
				$f->load_entry($joinentry);
				$elvs = $elvs + $f->entry;
			};
			// now elvalues is array el_id => el_value
			// but we need it to be el_name => el_value
			// so we do a bigass query to find all the names of the elements
			$tmp = array();
			foreach($elvs as $k => $v)
			{
				if (is_number($k) && is_number($v))
				{
					$tmp[$k] = $v;
				}
			}
			$elsss = join(",",$this->map2("%s",$tmp));

			if ($elsss != "")
			{
				$this->db_query("SELECT oid,name FROM objects WHERE oid IN($elsss)");
				while ($row = $this->db_next())
				{
					$elvalues[$row["name"]] = $elvs[$row["oid"]];
				}
			}
			$elvalues["E-mail"] = $udata["email"];
			// but we could also just cache this info in the users table
			$this->set_user_config(array("uid" => $uid, "key" => "user_info_cache", "value" => $elvalues));
		};
		return $elvalues;
	}

	function check_environment(&$sys, $fix = false)
	{
		$ret = $sys->check_admin_templates("automatweb/users", array("sel_list.tpl","list.tpl","changepwd.tpl","sel_join_grp.tpl","add.tpl"));
		$ret.= $sys->check_orb_defs(array("users"));
		return $ret;
	}

	function get_join_entries($uid = "")
	{
		if ($uid == "")
		{
			$uid = $GLOBALS["uid"];
		}
		$udata = $this->fetch($uid);
		$ar = unserialize($udata["join_form_entry"]);
		if (!is_array($ar))
		{
			$ar = array();
		}
		return $ar;
	}

	////
	// !showus user selectable settings
	function settings($arr)
	{
		extract($arr);
		$this->read_template("user_settings.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("gen_list", array())."'>Kasutajad</a> / Muuda kasutaja $id m&auml;&auml;ranguid");
		
		classload("languages");
		$l = new languages;
		$llist = $l->listall();
		foreach($llist as $lrow)
		{
			$this->vars(array(
				"lang_name" => $lrow["name"],
				"lang_id" => $lrow["id"],
				"checked" => checked($GLOBALS["admin_lang"] == $lrow["id"])
			));
			$lp.=$this->parse("LANG");
		}

		classload("currency");
		$cu = new currency;
		$cul = $cu->get_list();
	
		$ccur = $this->get_user_config(array("uid" => $id, "key" => "user_currency"));

		foreach($cul as $cuid => $cuname)
		{
			$this->vars(array(
				"cur_name" => $cuname,
				"cur_id" => $cuid,
				"checked" => checked($cuid == $ccur)
			));
			$ccr .= $this->parse("CUR");
		}

		classload("config");
		$co = new db_config;
		$fo = $co->get_simple_config("user_info_form");
		if ($fo)
		{
			classload("form");
			$f = new form;
			$eid = $this->get_user_config(array("uid" => $id, "key" => "info_entry"));
			$this->vars(array("form" => $f->gen_preview(array("id" => $fo, "entry_id" => $eid,"silent_errors" => true,"reforb" => $this->mk_reforb("submit_user_info", array("entry_id" => $eid,"u_uid" => $id),"users")))));
		}
		$this->vars(array(
			"LANG" => $lp,
			"CUR" => $ccr,
			"reforb" => $this->mk_reforb("submit_settings", array("id" => $id))
		));
		return $this->parse();
	}

	function submit_user_info($arr)
	{
		extract($arr);
		classload("form");
		classload("config");
		$co = new db_config;
		$fo = $co->get_simple_config("user_info_form");

		$f = new form;
		$f->process_entry(array("id" => $fo, "entry_id" => $entry_id));

		$this->set_user_config(array("uid" => $u_uid, "key" => "info_entry", "value" => $f->entry_id));

		return $this->mk_my_orb("settings", array("id" => $u_uid));
	}

	function show_user_info()
	{
		classload("config");
		$co = new db_config;
		$fo = $co->get_simple_config("user_info_form");
		if ($fo)
		{
			classload("form");
			$f = new form;
			$eid = $this->get_user_config(array("uid" => $GLOBALS["uid"], "key" => "info_entry"));
			if ($eid)
			{
				return $f->show(array("id" => $fo, "entry_id" => $eid,"op_id" => $co->get_simple_config("user_info_op")));
			}
		}
	}

	////
	// !saves users settings
	function submit_settings($arr)
	{
		extract($arr);

		$this->set_user_config(array("uid" => $id, "key" => "user_currency", "value" => $currency));

		classload("languages");
		$t = new languages;

		$admin_lang = $adminlang;
		$admin_lang_lc = $t->get_langid($admin_lang);
		setcookie("admin_lang",$admin_lang,time()*24*3600*1000,"/");
		setcookie("admin_lang_lc",$admin_lang_lc,time()*24*3600*1000,"/");

		return $this->mk_my_orb("settings", array("id" => $id));
	}
}
?>
