<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/users.aw,v 2.94 2003/10/06 14:32:25 kristo Exp $
// users.aw - User Management

load_vcl("table","date_edit");
session_register("add_state");
define("PER_PAGE", 20);

classload("users_user");
load_vcl('date_edit');
class users extends users_user
{
	function users()
	{
		$this->init("automatweb/users");
		lc_site_load("definition",&$this);
		lc_load("definition");
		$this->lc_load("users","lc_users");
	}

	////
	// !generates list of users. For internal use
	// I made this a separate function, because I'm going to need this functionality in
	// other places (e.g. messenger) besides the gen_list method in this file.

	// returns:
	//	array of users, in the form
	//	array(	"uid1" => "uid1",
	//		"uidn" => "uidn"),
	function _gen_usr_list($args = array())
	{
		extract($args);
		$retval = array();
		$this->db_query("SELECT uid FROM users WHERE blocked != 1");
		while ($row = $this->db_next())
		{
			$retval[$row["uid"]] = $row["uid"];
		}
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
		extract($arr);
		$this->read_template("list.tpl");
		unset($arr["search_click"]);
		if ($search_click == 1)
		{
			$letter = "";
			$page = 0;
			unset($arr["letter"]);
			unset($arr["page"]);
		}

		for ($i=ord('A'); $i < ord('Z'); $i++)
		{
			$_arr = $arr;
			$_arr["letter"] = chr($i);
			$this->vars(array(
				"l_url" => $this->mk_my_orb("gen_list", $_arr),
				"letter" => chr($i)
			));
			if ($letter == chr($i))
			{
				$lc.=$this->parse("SEL_LETTER");
			}
			else
			{
				$lc.=$this->parse("LETTER");
			}
		}

		$_arr = $arr;
		unset($_arr["letter"]);
		$_arr2 = $arr;
		$_arr2["search"] = 1;
		$_arr2["page"] = 0;
		$_arr3 = $arr;
		unset($_arr3["search"]);
		$_arr3["page"] = 0;

		$this->vars(array(
			"SEL_LETTER" => "",
			"LETTER" => $lc,
			"all_url" => $this->mk_my_orb("gen_list", $_arr),
			"search" => $this->mk_my_orb("gen_list", $_arr2),
			"list" => $this->mk_my_orb("gen_list", $_arr3),
			"stats" => $this->mk_my_orb("user_stats"),
			"createpwd" => $this->mk_my_orb("createpwd"),
			"import" => $this->mk_my_orb("import")
		));

		$uid_list = $this->_gen_usr_list();
		
		$let = "";
		if ($letter != "")
		{
			$let = "AND uid LIKE '".$letter."%'";
		}

		if ($search)
		{
			if ($created_from["year"])
			{
				$s_created_from = mktime(0,0,0,$created_from["month"],$created_from["day"],$created_from["year"]);
			}
			if ($created_to["year"])
			{
				$s_created_to = mktime(0,0,0,$created_to["month"],$created_to["day"],$created_to["year"]);
			}

			$let.=" AND uid LIKE '%".$s_uid."%'";
			$let.=" AND email LIKE '%".$s_email."%'";
			if ($s_created_from > 1)
			{
				$let.=" AND created > $s_created_from ";
			}
			if ($s_created_to > 1)
			{
				$let.=" AND created < $s_created_to ";
			}
			$de = new date_edit("s_created_from",time());
			$de->configure(array("year" => "","month" => "","day" => ""));

			$this->vars(array(
				"s_uid" => $s_uid,
				"s_email" => $s_email,
				"reforb" => $this->mk_reforb("gen_list", array("no_reforb" => 1, "page" => $page, "letter" => $letter,"search" => 1,"search_click" => 1)),
				"created_from" => $de->gen_edit_form("created_from", $s_created_from, 1999,2005,true),
				"created_to" => $de->gen_edit_form("created_to", $s_created_to, 1999,2005,true),
			));
			$this->vars(array(
				"IS_SEARCH" => $this->parse("IS_SEARCH"),
				"IS_SEARCH2" => $this->parse("IS_SEARCH2")
			));
		}
		else
		{
			$this->vars(array(
				"NO_SEARCH" => $this->parse("NO_SEARCH")
			));
		}

		$num_users = $this->db_fetch_field("SELECT count(uid) as cnt FROM users WHERE uid IN(".join(",",map("'%s'",$uid_list)).") AND blocked = 0 $let","cnt");

		$pages = $num_users / PER_PAGE;
		for ($i=0; $i < $pages; $i++)
		{
			$_arr = $arr;
			$_arr["page"] = $i;
			$this->vars(array(
				"from" => $i*PER_PAGE,
				"to" => min(($i+1)*PER_PAGE, $num_users),
				"link" => $this->mk_orb("gen_list", $_arr)
			));
			if ($i == $page)
			{
				$pg.=$this->parse("SEL_PAGE");
			}
			else
			{
				$pg.=$this->parse("PAGE");
			}
		}
		$this->vars(array(
			"PAGE" => $pg, 
			"SEL_PAGE" => ""
		));

		if ($page < 1)
		{
			$page = 0;
		}
		// hmpf. Huvitav, kas IN klauslil mingi suuruspiirang ka on?
		// kui kasutajaid on ntx 2000, siis see päring voib ysna jube olla

		$q = "SELECT * FROM users WHERE uid IN(".join(",",map("'%s'",$uid_list)).") AND blocked = 0 $let ORDER BY uid LIMIT ".$page*PER_PAGE.",".PER_PAGE;
		$this->db_query($q);

		$timeout = ini_get("session.gc_maxlifetime");

		while ($row = $this->db_next())
		{
			$this->vars(array(
				"uid"				=> $row["uid"], 
				"logs"				=> $row["logins"],
				"online"			=> ((time() - $row["lastaction"]) < $timeout) ? "yes" : "no",
				"last"				=> $this->time2date($row["lastaction"],2),
				"change"			=> $this->mk_orb("change", array("id" => $row["uid"])),
				"delete"			=> $this->mk_orb("delete", array("id" => $row["uid"])),
				"change_pwd"			=> $this->mk_orb("change_pwd", array("id" => $row["uid"])),
				"settings" => $this->mk_my_orb("settings", array("id" => $row["uid"])),
				"log" => $this->mk_my_orb("user_stats", array("s_uid" => $row["uid"])),
				"acl" => $this->mk_my_orb("user_acl", array("s_uid" => $row["uid"]))
			));

			$cc = ""; $cd = ""; $cpw = "";
			$cc = $this->parse("CAN_CHANGE");
			$cpw = $this->parse("CAN_PWD");

			if ($row["join_grp"] == "")
			{
				$cc = "";
			};

			$cd = $this->parse("CAN_DEL");
			$this->vars(array(
				"CAN_CHANGE" => $cc, 
				"CAN_DEL" => $cd, 
				"CAN_PWD" => $cpw
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"add"		=> $this->mk_orb("add_user", array())
		));
		$ad = "";
		if ($this->prog_acl("add", PRG_USERS))
		{
			$ad = $this->parse("ADD");
		}
		$this->vars(array("ADD" => $ad));
		return $this->parse();
	}

	////
	// !user changing from the admin interface
	function change($arr)
	{
		aw_session_set("session_filled_forms", array());
		if (!$arr["id"])
		{
			$arr["id"] = aw_global_get("uid");
		}
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
		$session_filled_forms = aw_global_get("session_filled_forms");

		// find all the forms in the selected join group 
		$this->db_query("SELECT id  FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 and forms.grp='$join_grp' AND forms.subtype = ".FSUBTYPE_JOIN);
		$jfrm = 0;
		while ($row = $this->db_next())
		{
			if (!$session_filled_forms[$row["id"]])
			{
				$jfrm = $row["id"];
				break;
			}
		}
		return $jfrm;
	}

	function do_change($arr)
	{
		extract($arr);

		$u = $this->fetch($id);
		$fs = unserialize($u["join_form_entry"]);

		// iterate over the join forms
		$jfrm = $this->get_next_jf($u["join_grp"]);

		if ($jfrm)
		{
			// show them one after another to the user
			$orb = $this->mk_my_orb("show", array("id" => $jfrm, "entry_id" => $fs[$jfrm], "extraids[redirect_after]" => urlencode($this->mk_my_orb("do_change", array("id" => $id), "users"))),"form");
			header("Location: $orb");
			return $orb;
		}
		else
		{
			// also, update users join form entries
			$this->save(array("uid" => $id, "join_form_entry" => serialize(aw_global_get("session_filled_forms")))); 

			// zero out formgen's user data cache
			$this->set_user_config(array("uid" => $id, "key" => "user_info_cache", "value" => false));

			// and when we're dont with all of them, update dyn groups and return to user list
			$this->update_dyn_user($id);

			// check if we are on the site side. if we are, redirect to the beginning. 
			if (strpos(aw_global_get("REQUEST_URI"), "automatweb") === false)
			{
				$orb = $this->mk_my_orb("change", array());
			}
			else
			{
				$orb = $this->mk_my_orb("gen_list", array());
			}
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
		if (!$id)
		{
			$id = aw_global_get("uid");
		}

		$u = $this->fetch($id);
		if (!($this->can("change", $u["oid"]) || aw_global_get("uid") == $id))
		{
			$this->raise_error(ERR_ACL, "No can_change access for user $id", true, false);
		}

		$this->read_template("changepwd.tpl");
		$this->vars(array(
			"email" => $u["email"],
			"error" => $error,
			"reforb" => $this->mk_reforb("submit_change_pwd", array("id" => $id))
		));
		return $this->parse();
	}

	////
	// !saves the uses changed password
	function submit_change_pwd($arr)
	{
		extract($arr);
		$udata = $this->fetch($id);
		if (!($this->can("change", $udata["oid"]) || aw_global_get("uid") == $id))
		{
			$this->raise_error(ERR_ACL, "No can_change access for user $id", true, false);
		}

		if ($arr["pwd"] != $arr["pwd2"])
		{
			return $this->mk_my_orb("change_pwd", array("id" => $id, "error" => LC_USERS_PASSW_NOT_SAME));
		}

		if (!is_valid("password",$pwd))
		{
			return $this->mk_my_orb("change_pwd", array("id" => $id, "error" => "Uus parool sisaldab lubamatuid märke<br />"));
		}

		if ($arr["pwd"] != "")
		{
			$this->save(array("uid" => $arr["id"], "password" => $arr["pwd"],"email" => $arr["email"]));
		}
		else
		{
			$this->save(array("uid" => $arr["id"], "email" => $arr["email"]));
		}

		if ($send_welcome_mail)
		{
			$udata = $this->get_user(array("uid" => $arr["id"]));

			// send him some email as well if the users selected to do so
			$c = get_instance("config");
			$mail = $c->get_simple_config("join_mail".aw_global_get("LC"));
			$mail = str_replace("#parool#", $arr["pwd"],$mail);
			$mail = str_replace("#kasutaja#", $arr["id"],$mail);
			$mail = str_replace("#liituja_andmed#", str_replace("\n\n","\n",$this->show_join_data(array("nohtml" => true, "user" => $arr["id"]))),$mail);
			$mail = str_replace("#pwd_hash#", $this->get_change_pwd_hash_link($arr["id"]), $mail);

			send_mail($udata["email"],$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
			$jsa = $c->get_simple_config("join_send_also");
			if ($jsa != "")
			{
				send_mail($jsa,$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
			}
		}

		$this->_log(ST_USERS, SA_CHANGE_PWD, $arr['id']);
		if (is_admin())
		{
			return $this->mk_my_orb("gen_list", array());
		}
		else
		{
			header("Refresh: 2;url=".$this->cfg["baseurl"]);
			die("Parool on edukalt vahetatud");
		}
	}

	////
	// !deletes the user
	function delete($arr)
	{
		extract($arr);
		$this->do_delete_user($id);
		header("Location: ".$this->mk_orb("gen_list", array()));
	}

	////
	// !adds the user and ssets all join form entries from site interface
	function submit_user_site($arr)
	{
		extract($arr);
		set_time_limit(0);

		global $add_state;
		$add_state["pass"] = $pass;
		$add_state["uid"] = $a_uid;
		$add_state["email"] = $email;

		if ($this->can_add($arr))
		{
			$jfs = serialize($this->get_join_form_entries($join_grp));

			$this->add(array(
				"join_form_entry" => $jfs, 
				"uid" => $add_state["uid"], 
				"password" => $add_state["pass"],
				"email" => $add_state["email"], 
				"join_grp" => $join_grp
			));			
			$this->update_dyn_user($add_state["uid"]);

			$al = $this->get_cval("useradd::autologin");

			$last_join_uid = $add_state["uid"];
			aw_session_set("last_join_uid", $last_join_uid);

			if ($al)
			{
				$uid = $add_state["uid"];
				$session = gen_uniq_id();
				aw_session_set("uid", $uid);
				aw_session_set("session", $session);
				aw_global_set("uid", $uid);
			}

			// send him some email as well
			$c = get_instance("config");
			$mail = $c->get_simple_config("join_mail".aw_global_get("LC"));
			$mail = str_replace("#parool#", $add_state["pass"],$mail);
			$mail = str_replace("#kasutaja#", $add_state["uid"],$mail);
			$mail = str_replace("#liituja_andmed#", str_replace("\n\n","\n",$this->show_join_data(array("nohtml" => true))),$mail);
			$mail = str_replace("#pwd_hash#", $this->get_change_pwd_hash_link($add_state["uid"]), $mail);

			send_mail($add_state["email"],$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
			$jsa = $c->get_simple_config("join_send_also");
			if ($jsa != "")
			{
				send_mail($jsa,$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
			}
			$add_state = "";
			aw_session_set("session_filled_forms",array());

			$this->_log(ST_USERS, SA_ADD,  $add_state["uid"]);
			return $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$after_join;
		}
		else
		{
			$add_state["level"] = 0;
			return $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=$section";
		}

		return $this->mk_orb("add_user", array("level" => 1, "join_grp" => $join_grp));
	}

	////
	// !adds the user and ssets all join form entries from admin interface
	function submit_user($arr)
	{
		extract($arr);

		global $add_state;
		$add_state["pass"] = $pass;
		$add_state["uid"] = $a_uid;
		$add_state["email"] = $email;

		if ($this->can_add($arr))
		{
			$jfs = serialize($this->get_join_form_entries($join_grp));

			$this->add(array(
				"join_form_entry" => $jfs, 
				"uid" => $add_state["uid"], 
				"password" => $add_state["pass"],
				"email" => $add_state["email"],
				"join_grp" => $join_grp
			));
			$this->update_dyn_user($add_state["uid"]);

			if ($send_welcome_mail)
			{
				// send him some email as well if the users selected to do so
				$c = get_instance("config");
				$mail = $c->get_simple_config("join_mail".aw_global_get("LC"));
				$mail = str_replace("#parool#", $add_state["pass"],$mail);
				$mail = str_replace("#kasutaja#", $add_state["uid"],$mail);
				$mail = str_replace("#liituja_andmed#", str_replace("\n\n","\n",$this->show_join_data(array("nohtml" => true, "user" => $add_state["uid"]))),$mail);
				$mail = str_replace("#pwd_hash#", $this->get_change_pwd_hash_link($add_state["uid"]), $mail);

				send_mail($add_state["email"],$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
				$jsa = $c->get_simple_config("join_send_also");
				if ($jsa != "")
				{
					send_mail($jsa,$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
				}
			}

			$a_uid = $add_state["uid"];
			$add_state = "";
			aw_session_set("session_filled_forms",array());
			$this->_log(ST_USERS, SA_ADD, $a_uid." was added from admin interface by ".aw_global_get("uid"));
			return $this->mk_my_orb("change", array(
				"id" => $this->get_oid_for_uid($a_uid)
			),"user");
		}
		else
		{
			$add_state["level"] = 0;
		}

		return $this->mk_orb("add_user", array("level" => 1, "join_grp" => $join_grp));
	}

	function can_add($arr)
	{
		global $add_state;
		$reserved = array("system");

		extract($arr);
		if (in_array($a_uid,$reserved))
		{
			return false;
		};
		$q = "SELECT * FROM users WHERE uid = '$a_uid'";
		$this->db_query($q);
		$row = $this->db_next();
		if ($row)
		{
			$add_state["error"] = LC_USERADD_ERROR_EXISTS;;
			return false;
		}

		if (!is_valid("uid",$a_uid))
		{
			$add_state["error"] = LC_USERADD_ERROR_SYMBOL;
			return false;
		}

		if ($pass != $pass2)
		{
			$add_state["error"] = LC_USERADD_ERROR_PWD;
			return false;
		}

		if (!is_valid("password", $pass))
		{
			$add_state["error"] = LC_USERADD_ERROR_PWD_SYMBOL;
			return false;
		}

		if (strlen($a_uid) < 3)
		{
			$add_state["error"] = LC_USERADD_ERROR_SHORT;
			return false;
		}

		if (strlen($pass) < 3)
		{
			$add_state["error"] = LC_USERADD_ERROR_PWD_SHORT;
			return false;
		}
		$add_state["error"] = "";
		return true;
	}

	////
	// !this da thang, users added from the admin interface will use this function extensively. w00p!
	function add_user($arr)
	{
		extract($arr);
		$this->mk_path(0,"<a href='".$this->mk_orb("gen_list", array()).LC_USERS_USERS);
		// siin hoitaxe forme, mis kasutaja on selle sessiooni jooxul t2itnud.
		global $add_state;
		$session_filled_forms = aw_global_get("session_filled_forms");

		if (!$level)
		{
			aw_session_set("session_filled_forms", array());
			$this->db_query("SELECT distinct(grp) as grp FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 and forms.subtype=".FSUBTYPE_JOIN);
			$jgrps = array();
			$found = false;
			while ($row = $this->db_next())
			{
				$jgrps[$row["grp"]] = $row["grp"];
				$found = true;
			}
			if ($found)
			{
				if (count($jgrps) == 1)
				{
					reset($jgrps);
					list(,$gp) = each($jgrps);
					header("Location: ".$this->mk_my_orb("add_user", array("level" => 1, "join_grp" => $gp)));
					die();
				}

				$this->read_template("sel_join_grp.tpl");
				$this->vars(array(
					"reforb" => $this->mk_reforb("add_user", array("level" => 1,"no_reforb" => true)),
					"join_grps" => $this->picker(0,$jgrps)
				));
				return $this->parse();
			}
			else
			{
				$this->read_template("add.tpl");
				$this->vars(array(
					"error" => $add_state["error"], 
					"uid" => $add_state["uid"],
					"email" => $add_state["email"],
					"reforb"	=> $this->mk_reforb("submit_user", array("join_grp" => $join_grp))
				));
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
				if (!$session_filled_forms[$row["id"]])
				{
					$jfrm = $row["id"];
					break;
				}
			}

			if ($jfrm)
			{
				// new approach here - user can pick an entry for the form as well now. 
				$this->read_template("show_form.tpl");
			
				$f = get_instance("formgen/form");
				$this->vars(array(
					"form" => $f->gen_preview(array("id" => $jfrm, "tpl" => "show_noform.tpl")),
					"entries" => $this->picker('',$f->get_entries(array("id" => $jfrm, "addempty" => true))),
					"reforb" => $this->mk_reforb("submit_ua_form", array("id" => $jfrm, "join_grp" => $join_grp, "no_reforb" => 1))
				));
				return $this->parse();
			}
			else
			{
				// and when we're dont with all of them, let the user select username/password
				// if email is not set, then try to read it from the entered data
				if ($add_state["email"] == "")
				{
					$email_el = $this->get_cval("users::email_element");
					$name_els = aw_unserialize($this->get_cval("users::name_elements"));
					$name_els_sep = $this->get_cval("users::name_elements_sep");

					$finst = get_instance("formgen/form");

					$sff = new aw_array($session_filled_forms);
					foreach($sff->get() as $sf_id => $sf_e_id)
					{
						$sf_els = $finst->get_elements_for_forms(array($sf_id => $sf_id));
						if (isset($sf_els[$email_el]))
						{
							$finst->load($sf_id);
							$finst->load_entry($sf_e_id);
							$add_state["email"] = $finst->get_element_value($email_el);
						}
						foreach($name_els as $nelid)
						{
							if (isset($sf_els[$nelid]))
							{
								$finst->load($sf_id);
								$finst->load_entry($sf_e_id);
								$add_state["name"] .= $finst->get_element_value($nelid).$name_els_sep;
							}
						}
					}
					aw_session_set("add_state", $add_state);
				}

				$this->read_template("add.tpl");
				$this->vars(array(
					"error" => $add_state["error"], 
					"uid" => $add_state["uid"],
					"email" => $add_state["email"],
					"name" => $add_state["name"],
					"reforb"	=> $this->mk_reforb("submit_user", array("join_grp" => $join_grp))
				));
				return $this->parse();
			}
		}
	}

	function get_join_form($after_join)
	{
		aw_global_set("no_cache_content", 1);

		// siin hoitaxe forme, mis kasutaja on selle sessiooni jooxul t2itnud.
		$session_filled_forms = aw_global_get("session_filled_forms");

		$jfs = array();
		$this->db_query("SELECT objects.*,forms.grp as grp,forms.j_mustfill as j_mustfill FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 AND objects.site_id = ".$this->cfg["site_id"]." AND forms.subtype = ".FSUBTYPE_JOIN);
		while ($row = $this->db_next())
		{
			// paneme siia arraysse aint need formid, mida PEAB t2itma, teisi v6ime ignoreerida
			if ($row["j_mustfill"] == 1)
			{
				$jfs[$row["oid"]] = array("group" => $row["grp"]);
			}
		}

		// nini nyyd on vaja tshekkida et kas k6ik vajalikud formid on t2idetud
		$groups = array();
		reset($jfs);
		// teeme gruppide nimekirja
		while (list($fid,$ar) = each($jfs))
		{
			$groups[$ar["group"]][$fid] = $session_filled_forms[$fid];
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

			if ($add_state["email"] == "")
			{
				// kui pole emailiaadressi, siis yritame seda leida liitumisformidest
				foreach($groups[$add_group] as $fid => $eid)
				{
					$f = get_instance("formgen/form");
					$f->load($fid);
					$f->load_entry($eid);
					$em = $f->get_element_value_by_name("E-mail");
					if ($em != false)
					{
						$add_state["email"] = $em;
						break;
					}
				}
			}

			// n2itame kasutaja tegemise formi
			$this->read_template("add_site.tpl");
			$this->vars(array(
				"error" => $add_state["error"], 
				"uid" => $add_state["uid"],
				"email" => $add_state["email"],
				"reforb"	=> $this->mk_reforb("submit_user_site", array("join_grp" => $add_group, "section" => aw_global_get("section"), "after_join" => $after_join))
			));
			return $this->parse();
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
		$session_filled_forms = aw_global_get("session_filled_forms");

		$ret = array();

		$this->db_query("SELECT id,grp FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 AND subtype = ".FSUBTYPE_JOIN." AND grp = '$group'");
		// teeme gruppide nimekirja
		while ($row = $this->db_next())
		{
			$ret[$row["id"]] = $session_filled_forms[$row["id"]];
		}
		return $ret;
	}

	////
	// !shows the form $fid with the entry the user entered when he/she joined
	function do_change_site($arr)
	{
		extract($arr);
		$id = aw_global_get("uid");
		if ($id == "")
		{
			return LC_USERS_NOT_LOGGED_IN;
		}
	
		if (not($fid))
		{
			$udata = $this->get_user();
			$jfar = $this->get_jf_list(isset($udata["join_grp"]) ? $udata["join_grp"] : "");
			$jfs = "";
			reset($jfar);
			list($fid,$name) = each($jfar);
		};

		$u = $this->fetch($id);
		$fs = unserialize($u["join_form_entry"]);

		$t = get_instance("formgen/form");
		return $t->gen_preview(array(
			"id" => $fid, 
			"entry_id" => $fs[$fid], 
			"reforb" => $this->mk_reforb("save_udata", array("fid" => $fid,"user_id" => $id,"section" => aw_global_get("section")))
		));
	}

	////
	// !this saves the data entered in the form and flushes all necessary caches and group memberships
	function submit_do_change_site($arr)
	{
		extract($arr);

		$u = $this->fetch($user_id);
		$fs = unserialize($u["join_form_entry"]);

		$t = get_instance("formgen/form");
		$t->process_entry(array(
			"id" => $fid,
			"entry_id" => $fs[$fid]
		));

		$fs[$fid] = $t->entry_id;

		// write the entry to the user table as well, in case it is a new entry
		$this->save(array("uid" => $user_id, "join_form_entry" => aw_serialize($fs,SERIALIZE_NATIVE))); 

		// zero out formgen's user data cache
		$this->set_user_config(array("uid" => $user_id, "key" => "user_info_cache", "value" => false));
		// and regenerate the cache with the new data
		$this->get_user_info($user_id);

		$this->update_dyn_user($user_id);

		return $this->mk_my_orb("udata", array("fid" => $fid,"section" => $section));
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
		if ($user != "")
		{
			$uuid = $user;
		}
		if ($uuid == "")
		{
			$uuid = aw_global_get("uid");
		}
		if ($uuid == "")
		{
			$uuid = aw_global_get("last_join_uid");
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
		if ($udata)
		{
			$jf = unserialize($udata["join_form_entry"]);
			{
				$f = get_instance("formgen/form");
				if (is_array($jf))
				{
					foreach($jf as $joinform => $joinentry)
					{
						if ($ops[$joinform])
						{
							$ret.=$f->show(array(
								"id" => $joinform,
								"entry_id" => $joinentry, 
								"op_id" => $ops[$joinform],
								"no_html" => $nohtml,
								"no_load_op" => $arr["no_load_op"]
							));
						}
					};
				}
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
		}
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
		$udata = $this->get_user(array("uid" => $username));
		if (!$udata)
		{
			$username = $this->db_fetch_field("SELECT uid FROM users WHERE email = '$username'","uid");
			$udata = $this->get_user(array("uid" => $username));
		}	

		$c = get_instance("config");
		$mail = $c->get_simple_config("remind_pwd_mail");
		$mail = str_replace("#parool#", $udata["password"],$mail);
		$mail = str_replace("#kasutaja#", $username,$mail);
		$mail = str_replace("#liituja_andmed#", str_replace("\n\n","\n",$this->show_join_data(array("nohtml" => true,"user" => $username,"no_load_op" => 1))),$mail);

		#$mail = str_replace("\r","",$mail);
		$mail = str_replace("\r\n","\n",$mail);

		send_mail($udata["email"],$c->get_simple_config("remind_pwd_mail_subj"),$mail,"From: ".$this->cfg["mail_from"]);
		$this->_log(ST_USERS, SA_REMIND_PWD, $username);
		return $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$after;
	}

	////
	// !this is used by formgen to retrieve the data that the user $uid entered when he joined 
	// the return value is an array(element_name => element_value). 
	// the function caches the result for better performance
	// the cache needs to be zeroed out when the user changes his/her data
	function get_user_info($uid, $ret_id = false)
	{
		// yeah. use the cached version if available for better performance
		$dat = $this->get_user_config(array("uid" => $uid, "key" => "user_info_cache"));
		if (is_array($dat) && !$ret_id)
		{
			return $dat;
		}

		$elvalues = array();
		$udata = $this->get_user(array("uid" => $uid));
		$jf = unserialize($udata["join_form_entry"]);
		if (is_array($jf))
		{
			$elvs = array();
			$f = get_instance("formgen/form");
			foreach($jf as $joinform => $joinentry)
			{
				$f->load($joinform);
				$f->load_entry($joinentry);
				$elvs = $elvs + $f->entry;
			};
			// now elvalues is array el_id => el_value
			// but we need it to be el_name => el_value
			// so we do a bigass query to find all the names of the elements
			if ($ret_id)
			{
				return $elvs;
			}
			else
			{
				$tmp = array();
				foreach($elvs as $k => $v)
				{
					if (is_number($k))
					{
						$tmp[$k] = $v;
					}
				}
				$elsss = join(",",map2("%s",$tmp));

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
			}
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
			$uid = aw_global_get("uid");
		}
		$udata = $this->get_user(array("uid" => $uid));
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
		
		$l = get_instance("languages");
		$llist = $l->listall();
		foreach($llist as $lrow)
		{
			$this->vars(array(
				"lang_name" => $lrow["name"],
				"lang_id" => $lrow["id"],
				"checked" => checked(aw_global_get("admin_lang") == $lrow["id"])
			));
			$lp.=$this->parse("LANG");
		}

		$co = get_instance("config");
		$fo = $co->get_simple_config("user_info_form");
		if ($fo)
		{
			$f = get_instance("formgen/form");
			$eid = $this->get_user_config(array(
				"uid" => $id, 
				"key" => "info_entry"
			));
			$this->vars(array(
				"form" => $f->gen_preview(array(
					"id" => $fo, 
					"entry_id" => $eid,
					"silent_errors" => true,
					"reforb" => $this->mk_reforb("submit_user_info", array("entry_id" => $eid,"u_uid" => $id),"users")
				))
			));
		}

		$act_from = $this->get_user_config(array(
			"uid" => $id, 
			"key" => "act_from"
		));
		$act_to = $this->get_user_config(array(
			"uid" => $id, 
			"key" => "act_to"
		));

		// act from / act to selectors
		load_vcl("date_edit");
		$de = new date_edit();
		$de2 = new date_edit();
		
		$this->vars(array(
			"LANG" => $lp,
			"act_from" => $de->gen_edit_form("act_from", $act_from, date("Y") - 5, date("Y") + 5, true),
			"act_to" => $de2->gen_edit_form("act_to", $act_to, date("Y") - 5, date("Y") + 5, true),
			"reforb" => $this->mk_reforb("submit_settings", array("id" => $id))
		));
		return $this->parse();
	}

	function submit_user_info($arr)
	{
		extract($arr);
		$co = get_instance("config");
		$fo = $co->get_simple_config("user_info_form");

		$f = get_instance("formgen/form");
		$f->process_entry(array("id" => $fo, "entry_id" => $entry_id));

		$this->set_user_config(array("uid" => $u_uid, "key" => "info_entry", "value" => $f->entry_id));

		return $this->mk_my_orb("settings", array("id" => $u_uid));
	}

	function show_user_info()
	{
		$co = get_instance("config");
		$fo = $co->get_simple_config("user_info_form");
		if ($fo)
		{
			$eid = $this->get_user_config(array("uid" => aw_global_get("uid"), "key" => "info_entry"));
			if ($eid)
			{
				$f = get_instance("formgen/form");
				return $f->show(array("id" => $fo, "entry_id" => $eid,"op_id" => $co->get_simple_config("user_info_op")));
			}
		}
	}

	////
	// !saves users settings
	function submit_settings($arr)
	{
		extract($arr);

		$this->set_user_config(array(
			"uid" => $id,
			"data" => array(
				"act_from" => date_edit::get_timestamp($act_from),
				"act_to" => date_edit::get_timestamp($act_to),
			),
		));

		$t = get_instance("languages");

		$admin_lang = $adminlang;
		$admin_lang_lc = $t->get_langid($admin_lang);
		setcookie("admin_lang",$admin_lang,time()*24*3600*1000,"/");
		setcookie("admin_lang_lc",$admin_lang_lc,time()*24*3600*1000,"/");

		$this->_log(ST_USERS, SA_CHANGE, aw_global_get("uid"));
		return $this->mk_my_orb("settings", array("id" => $id));
	}

	////
	// !statistics about users
	function user_stats($arr)
	{
		extract($arr);
		$this->read_template("stats.tpl");
		$this->vars(array(
			"add"		=> $this->mk_my_orb("add_user", array()),
			"search" => $this->mk_my_orb("gen_list", array("search" => 1)),
			"stats" => $this->mk_my_orb("user_stats", array()),
			"s_uid" => $s_uid,
			"syslog_url" => $this->cfg["baseurl"]."/monitor.".$this->cfg["ext"]."?filter_uid=".$s_uid,
		));
		if ($this->prog_acl("add", PRG_USERS))
		{
			$ad = $this->parse("ADD");
		}
		if ($s_uid != "")
		{
			$uo = $this->parse("USER_ONLY");
		}
		$this->vars(array(
			"USER_ONLY" => $uo,
			"ADD" => $ad,
			"NO_SEARCH" => $this->parse("IS_SEARCH")
		));

		$de = new date_edit("from");
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
			"hour" => ""
		));

		if ($from["year"])
		{
			$s_from = mktime(0,0,0,$from["month"],$from["day"],$from["year"]);
		}
		if ($to["year"])
		{
			$s_to = mktime(0,0,0,$to["month"],$to["day"],$to["year"]);
		}

		$this->vars(array(
			"join_sel" => checked($stat_type == "join"),
			"login_sel" => checked($stat_type == "login"),
			"hour_sel" => checked($stat_span == "hour"),
			"day_sel" => checked($stat_span == "day"),
			"week_sel" => checked($stat_span == "week"),
			"month_sel" => checked($stat_span == "month"),
			"year_sel" => checked($stat_span == "year"),
			"bar_sel" => checked($graph_type == "BarGraph"),
			"line_sel" => checked($graph_type == "LineGraph"),
			"pie_sel" => checked($graph_type == "PieGraph"),
			"from" => $de->gen_edit_form("from", $s_from, 1999,2005,true),
			"to" => $de->gen_edit_form("to", $s_to, 1999,2005,true),
			"reforb" => $this->mk_reforb("user_stats", array("no_reforb" => 1,"s_uid" => $s_uid))
		));

		if ($stat_type != "" && $stat_span != "")
		{
			if ($stat_type == "join")
			{
				$this->mk_join_stats(array("from" => $s_from,"to" => $s_to,"span" => $stat_span,"typestr" => $graph_type,"s_uid" => $s_uid));
			}
			else
			if ($stat_type == "login")
			{
				$this->mk_login_stats(array("from" => $s_from,"to" => $s_to,"span" => $stat_span,"typestr" => $graph_type,"s_uid" => $s_uid));
			}

			$this->vars(array(
				"STATS" => $this->parse("STATS")
			));
		}
		return $this->parse();
	}

	function mk_join_stats($arr)
	{
		extract($arr);

		$lt = array();
		if ($s_from)
		{
			$lt[] = " created > $s_from ";
		}
		if ($s_to)
		{
			$lt[] = " created < $s_to ";
		}
		if ($s_uid != "")
		{
			$lt[] = " uid = '$s_uid' ";
		}

		$dat = array();
		switch ($span)
		{
			case "hour":
				$grp = " GROUP BY users.created_hour "; 
				$get = " created_hour";
				$xtitle = "Tund";
				for ($i=0; $i < 24; $i++)
				{
					$dat[$i] = array("cnt" => 0, "span" => $i);
				}
				break;
			case "week":
				$grp = " GROUP BY users.created_week "; 
				$get = " created_week";
				$xtitle = "N&auml;dalap&auml;ev";
				for ($i=0; $i < 7; $i++)
				{
					$dat[$i] = array("cnt" => 0, "span" => $i);
				}
				break;
			case "day":
				$grp = " GROUP BY users.created_day "; 
				$get = " created_day";
				$xtitle = "P&auml;ev";
				break;
			case "month":
				$grp = " GROUP BY users.created_month "; 
				$get = " created_month";
				$xtitle = "Kuu";
				for ($i=0; $i < 12; $i++)
				{
					$dat[$i] = array("cnt" => 0, "span" => $i);
				}
				break;
			case "year":
				$grp = " GROUP BY users.created_year "; 
				$get = " created_year";
				$xtitle = "Aasta";
				break;
		}

		$wh = join("AND",$lt);
		if ($wh != "")
		{
			$wh = " WHERE ".$wh;
		}
		$max = 0;
		$min = 2000000000;

		$this->db_query("SELECT count(*) as cnt,$get as span FROM users $wh $grp");
		while ($row = $this->db_next())
		{
			$dat[$row["span"]] = $row;
			$max = max($max,$row["cnt"]);
			$min = min($min,$row["cnt"]);
		}

		$xvals = array();
		$yvals = array();
		foreach($dat as $_rsp => $row)
		{
			if ($max < 1)
			{
				$width="0";
			}
			else
			{
				$width = 200*($row["cnt"]/$max);
			}
			if ($span == "day")
			{
				$tm = $this->time2date($row["span"],8);
			}
			else
			if ($span == "week")
			{
				$tm = get_lc_weekday($row["span"]+1);
			}
			else
			if ($span == "month")
			{
				$tm = get_lc_month($row["span"]+1);
			}
			else
			{
				$tm = $row["span"];
			}
			$this->vars(array(
				"time" => $tm,
				"cnt" => $row["cnt"],
				"width" => $width
			));
			$sl.=$this->parse("STAT_LINE");
			$xvals[] = $tm;
			$data[] = $row["cnt"];
		}

		$yvals = array(0,$max);
		$this->vars(array(
			"STAT_LINE" => $sl,
			"graph" => $this->mk_my_orb("stat_chart", array(
				"xvals" => urlencode(join(",",$xvals)), 
				"yvals" => urlencode(join(",",$yvals)),
				"data" => urlencode(join(",",$data)),
				"title" => "Liitumisi",
				"xtitle" => $xtitle,
				"ytitle" => "Liitumisi",
				"typestr" => $typestr
			),"banner")
		));
	}

	function mk_login_stats($arr)
	{
		extract($arr);

		$lt = "";
		if ($s_from)
		{
			$lt .= " AND tm > $s_from ";
		}
		if ($s_to)
		{
			$lt .= " AND tm < $s_to ";
		}
		if ($s_uid)
		{
			$lt.=" AND uid = '$s_uid' ";
		}

		$dat = array();
		switch ($span)
		{
			case "hour":
				$grp = " GROUP BY created_hour "; 
				$get = " created_hour";
				$xtitle = "Tund";
				for ($i=0; $i < 24; $i++)
				{
					$dat[$i] = array("cnt" => 0, "span" => $i);
				}
				break;
			case "week":
				$grp = " GROUP BY created_week "; 
				$get = " created_week";
				$xtitle = "N&auml;dalap&auml;ev";
				for ($i=0; $i < 7; $i++)
				{
					$dat[$i] = array("cnt" => 0, "span" => $i);
				}
				break;
			case "day":
				$grp = " GROUP BY created_day "; 
				$get = " created_day";
				$xtitle = "P&auml;ev";
				break;
			case "month":
				$grp = " GROUP BY created_month "; 
				$get = " created_month";
				$xtitle = "Kuu";
				for ($i=0; $i < 12; $i++)
				{
					$dat[$i] = array("cnt" => 0, "span" => $i);
				}
				break;
			case "year":
				$grp = " GROUP BY created_year "; 
				$get = " created_year";
				$xtitle = "Aasta";
				break;
		}

		$max = 0;
		$min = 2000000000;

		$this->db_query("SELECT count(*) as cnt,$get as span FROM syslog WHERE type = 'auth' $lt $grp");
		while ($row = $this->db_next())
		{
			$dat[$row["span"]] = $row;
			$max = max($max,$row["cnt"]);
			$min = min($min,$row["cnt"]);
		}

		$xvals = array();
		$yvals = array();
		foreach($dat as $_rsp => $row)
		{
			if ($max < 1)
			{
				$width="0";
			}
			else
			{
				$width = 200*($row["cnt"]/$max);
			}
			if ($span == "day")
			{
				$tm = $this->time2date($row["span"],8);
			}
			else
			if ($span == "week")
			{
				$tm = get_lc_weekday($row["span"]+1);
			}
			else
			if ($span == "month")
			{
				$tm = get_lc_month($row["span"]+1);
			}
			else
			{
				$tm = $row["span"];
			}
			$this->vars(array(
				"time" => $tm,
				"cnt" => $row["cnt"],
				"width" => $width
			));
			$sl.=$this->parse("STAT_LINE");
			$xvals[] = $tm;
			$data[] = $row["cnt"];
		}

		$yvals = array(0,$max);
		$this->vars(array(
			"STAT_LINE" => $sl,
			"graph" => $this->mk_my_orb("stat_chart", array(
				"xvals" => urlencode(join(",",$xvals)), 
				"yvals" => urlencode(join(",",$yvals)),
				"data" => urlencode(join(",",$data)),
				"title" => "Sisse logimisi",
				"xtitle" => $xtitle,
				"ytitle" => "Logimisi",
				"typestr" => $typestr
			),"banner")
		));
	}

	function convusers()
	{
		$this->db_query("SELECT * FROM users");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->db_query("UPDATE users SET created_hour = '".(date("H",$row["created"]))."', created_day = '".(mktime(0,0,0,date("m",$row["created"]),date("d",$row["created"]),date("Y",$row["created"])))."', created_week = '".(date("w",$row["created"]))."', created_month = '  ".(date("m",$row["created"]))."', created_year = '".(date("Y",$row["created"]))."' WHERE uid = '".$row["uid"]."'");
			echo "updated $row[uid] <br />";
			flush();
			$this->restore_handle();
		}
	}

	function convsyslog()
	{
		$this->db_query("SELECT * FROM syslog");
		$cnt=0;
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->db_query("UPDATE syslog SET created_hour = '".(date("H",$row["tm"]))."', created_day = '".(mktime(0,0,0,date("m",$row["tm"]),date("d",$row["tm"]),date("Y",$row["tm"])))."', created_week = '".(date("w",$row["tm"]))."', created_month = '  ".(date("m",$row["tm"]))."', created_year = '".(date("Y",$row["tm"]))."' WHERE id = '".$row["id"]."'");
			if (($cnt % 100) == 0)
			{
				echo "updated $cnt records <br />";
				flush();
			}
			$cnt++;
			$this->restore_handle();
		}
	}

	////
	// !displays objects that the user s_uid has been assigned acls to
	function user_acl($arr)
	{
		extract($arr);
		$per_page = 50;
		$this->read_template("user_acl.tpl");
		$this->vars(array(
			"add"		=> $this->mk_my_orb("add_user", array()),
			"list" => $this->mk_my_orb("gen_list", array("search" => 0)),
			"stats" => $this->mk_my_orb("user_stats", array()),
		));
		if ($this->prog_acl("add", PRG_USERS))
		{
			$ad = $this->parse("ADD");
		}
		$this->vars(array(
			"ADD" => $ad,
			"NO_SEARCH" => $this->parse("IS_SEARCH")
		));

		$acl_list = $this->acl_list_acls();
		foreach($acl_list as $bp => $acl_name)
		{
			$this->vars(array(
				"acl_name" => $acl_name
			));
			$at .= $this->parse("ACL_TITLE");
		}
		$this->vars(array(
			"ACL_TITLE" => $at
		));

		$grp = $this->get_user_group(aw_global_get("uid"));

		$obj = get_instance("objects");
		$ol = $obj->get_list();

		$num = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM acl WHERE gid = ".$grp["gid"],"cnt");
		$pages = $num/$per_page;
		for ($i=0; $i < $pages; $i++)
		{
			$this->vars(array(
				"from" => $i*$per_page,
				"to" => min(($i+1)*$per_page,$num),
				"link" => $this->mk_my_orb("user_acl", array("s_uid" => $s_uid,"page" => $i))
			));
			if ($i == $page)
			{
				$ps.=$this->parse("SEL_PAGE");
			}
			else
			{
				$ps.=$this->parse("PAGE");
			}
		}
		$this->vars(array(
			"PAGE" => $ps,
			"SEL_PAGE" => ""
		));

		$this->acl_get_acls_for_grp($grp["gid"],$page*$per_page,$per_page);
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"oid" => $row["oid"],
				"o_name" => $ol[$row["oid"]]."/".$row["name"],
			));

			$at = "";
			foreach($acl_list as $bp => $acl_name)
			{
				$this->vars(array(
					"acl_name" => $acl_name,
					"checked" => checked($row[$acl_name] == $this->cfg["acl"]["allowed"]),
					"acl_value" => $row[$acl_name]
				));
				$at .= $this->parse("ACL_CELL");
			}
			$this->vars(array(
				"ACL_CELL" => $at
			));
			$al.=$this->parse("ACL_LINE");
		}
		$this->vars(array(
			"ACL_LINE" => $al,
			"reforb" => $this->mk_reforb("submit_user_acl", array("s_uid" => $s_uid,"page" => $page))
		));
		return $this->parse();
	}

	function submit_user_acl($arr)
	{
		extract($arr);
		$grp = $this->get_user_group(aw_global_get("uid"));

		foreach($old_acls as $oid => $odata)
		{
			$acl_list = $this->acl_list_acls();
			foreach($acl_list as $bp => $acl_name)
			{
				if (((int)$acls[$oid][$acl_name]) != ((int)$odata[$acl_name]))
				{
					$this->save_acl_masked($oid,$grp["gid"],array($acl_name => $acls[$oid][$acl_name]),array($acl_name => 1));
				}
			}
		}
		return $this->mk_my_orb("user_acl", array("s_uid" => $s_uid,"page" => $page));
	}

	////
	// !Generates an unique hash, which when used in a url can be used to let the used change
	// his/her password
	function send_hash($args = array())
	{
		extract($args);
		
		if (not(aw_ini_get("auth.md5_passwords")))
		{
			return "<font color=red>This site does not use encrypted passwords and therefore this function does not work</font>";
		};

		$this->read_template("send_hash.tpl");

		$this->vars(array(
			"webmaster" => $this->cfg["webmaster_mail"],
			"reforb" => $this->mk_reforb("submit_send_hash",array()),
		));

		return $this->parse();
	}

	function submit_send_hash($args = array())
	{
		extract($args);
		if (($type == "uid") && not(is_valid("uid",$uid)))
		{
			aw_session_set("status_msg","Vigane kasutajanimi");
			return $this->mk_my_orb("send_hash",array());
		};
		if (($type == "email") && not(is_email($email)))
		{
			aw_session_set("status_msg","Vigane e-posti aadress");
			return $this->mk_my_orb("send_hash",array());
		};
		if ($type == "uid")
		{
			$q = "SELECT * FROM users WHERE uid = '$uid' AND blocked = 0";
		}
		else
		{
			$q = "SELECT * FROM users WHERE email = '$email' AND blocked = 0";
		};
		$this->db_query($q);
		while($row = $this->db_next())
		{
			if ($type == "email")
			{
				$uid = $row["uid"];
			};
			if (not(is_email($row["email"])))
			{
				$status_msg .= "Kasutajal $uid puudub korrektne e-posti aadress. Palun pöörduge veebisaidi haldaja poole";
				aw_session_set("status_msg", $status_msg);
				return $this->mk_my_orb("send_hash",array());
			};

			$churl = $this->get_change_pwd_hash_link($uid);

			$email = $this->cfg["webmaster_mail"];
			$name_wm = $this->cfg["webmaster_name"];

			$msg = "Tere $row[uid]\n\nTeie isikliku parooli vahetamiseks kodulehel $host tuleb teil klikkida lingil\n\n$churl\n\nLingile klikkides avanab Teile parooli muutmise leht. \n\nProbleemide korral saatke e-mail $email.\n\nKõike paremat soovides,\n$name_wm";
			$from = sprintf("%s <%s>",$this->cfg["webmaster_name"],$this->cfg["webmaster_mail"]);
			send_mail($row["email"],"Paroolivahetus saidil ".aw_global_get("HTTP_HOST"),$msg,"From: $from");
			aw_session_set("status_msg","Parooli muutmise link saadeti  aadressile <b>$row[email]</b>. Vaata oma postkasti<br />Täname!<br />");
		};
		return $this->mk_my_orb("send_hash",array());
	}

	////
	// !Allows the user to change his/her password
	function password_hash($args = array())
	{	
		extract($args);
		$uid = $u;
		$key = $k;
		if (not(is_valid("uid",$uid)))
		{
			$this->read_adm_template("hash_results.tpl");
			$this->vars(array(
				"msg" => "Vigane kasutajanimi",
			));
			return $this->parse();
		};

		$q = "SELECT * FROM users WHERE uid = '$uid' AND blocked = '0'";
		$this->db_query($q);
		$row = $this->db_next();
		if (not($row))
		{
			$this->read_adm_template("hash_results.tpl");
			$this->vars(array(
				"msg" => "Sellist kasutajat pole registreeritud",
			));
			return $this->parse();
		};

		$pwhash = $this->get_user_config(array(
			"uid" => $uid,
			"key" => "password_hash",
		));

		if ($pwhash != $key)
		{	
			$this->read_adm_template("hash_results.tpl");
			$this->vars(array(
				"msg" => "Sellist võtit pole väljastatud",
			));
			return $this->parse();
		};

		$ts = $this->get_user_config(array(
			"uid" => $uid,
			"key" => "password_hash_timestamp",
		));

		// default expiration time is 1 hour (3600 seconds)
		if (($ts + (3600*24*400)) < time())
		{
			$this->read_adm_template("hash_results.tpl");
			$this->vars(array(
				"msg" => "See võti on juba aegunud <a href='".$this->mk_my_orb('send_hash')."'>Telli uusi v&otilde;ti</a>"
			));
			return $this->parse();
		}

		$this->read_adm_template("hash_change_password.tpl");

		$this->vars(array(
			"uid" => $uid,
			"reforb" => $this->mk_reforb("submit_password_hash",array("uid" => $uid,"pwhash" => $pwhash)),
		));
		return $this->parse();
	}

	function change_pwd_hash($args = array())
	{
		global $a;
		$this->quote($a);
		$q = "SELECT * FROM storage WHERE skey = '$a'";
		$this->db_query($q);
		$row = $this->db_next();
		if (!$row)
		{
			return "<span style='color: red'>Sellist võtit pole väljastatud!</span><br />";
		};

		if ($args["change"])
		{
			if ($args["pass1"] && $args["pass2"])
			{
				$_data = aw_unserialize($row["data"]);
				$newpass = md5($args["pass1"]);
				$q = "UPDATE users SET password = '$newpass' WHERE uid = '$_data[uid]'";
				$this->db_query($q);
				return $this->login(array("uid" => $_data["uid"], "password" => $args["pass1"]));
			}
			else
			{
				return "<span style='color: red'>Viga parooli sisestamisel</a>";
			};
		};

		$this->read_template("hash_change_password_plain.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("change_pwd_hash",array("no_reforb" => 1, "change" => 1,"a" => $a)),
		));
		return $this->parse();
	}

	////
	// !Submits the password
	function submit_password_hash($args = array())
	{
		extract($args);
		$q = "SELECT * FROM users WHERE uid = '$uid' AND blocked = 0";
		$this->db_query($q);
		$row = $this->db_next();
		if (not($row))
		{
			aw_session_set("status_msg","Sellist kasutajat pole registreeritud");
			return $this->mk_my_orb("send_hash",array());
		};
		
		$pwhash1 = $this->get_user_config(array(
			"uid" => $uid,
			"key" => "password_hash",
		));

		if ($pwhash1 != $pwhash)
		{
			aw_session_set("status_msg","Sellist võtit pole väljastatud");
			return $this->mk_my_orb("pwhash",array("u" => $uid,"k" => $pwhash));
		};
		
		if (not(is_valid("password",$pass1)))
		{
			aw_session_set("status_msg","Parool sisaldab keelatud märke");
			return $this->mk_my_orb("pwhash",array("u" => $uid,"k" => $pwhash));
		};

		if ($pass1 != $pass2)
		{
			aw_session_set("status_msg","Paroolid peavad olema ühesugused");
			return $this->mk_my_orb("pwhash",array("u" => $uid,"k" => $pwhash));
		};

		// tundub, et kõik on allright. muudame parooli ära
		$newpass = md5($pass1);
		$q = "UPDATE users SET password = '$newpass' WHERE uid = '$uid'";
		$this->db_query($q);
		$this->_log(ST_USERS, SA_CHANGE_PWD, $uid);
//		$this->read_adm_template("password_change_success.tpl");
//		return $this->parse();
		aw_session_set("status_msg","<b><font color=green>Parool on edukalt vahetatud</font></b>");
		return $this->login(array("uid" => $uid, "password" => $pass1));
	}

	function kill_user()
	{
		aw_session_set("uid","");
		session_destroy();
		header("Location: ".aw_ini_get("baseurl"));
		die();
	}

	function request_startup()
	{
		if (($uid = aw_global_get("uid")) != "")
		{
			$_uid = $this->db_fetch_field("SELECT uid FROM users WHERE uid = '$uid' AND blocked != 1", "uid");
			if ($_uid != $uid)
			{
				// if no such user exists, log the bastard out
				$this->kill_user();
			}
			$gidlist = array();
			$gidlist_pri = array();
			$gl = $this->get_gids_by_uid($uid,true);
			foreach($gl as $gid => $gd)
			{
				$gidlist[(int)$gid] = (int)$gd["gid"];
				$gidlist_pri[(int)$gid] = (int)$gd["priority"];
			}

			if (count($gidlist) < 1)
			{
				$this->kill_user();
			}
			aw_global_set("gidlist", $gidlist);
			aw_global_set("gidlist_pri", $gidlist_pri);
			$this->touch($uid);
		}
		if (!is_array(aw_global_get("gidlist")))
		{
			aw_global_set("gidlist", array());
			aw_global_set("gidlist_pri", array());
		}
	}

	function submit_ua_form($arr)
	{
		extract($arr);
		// mark the previous form entry
		if ($id)
		{
			// form submitted
			if (!$ex_entry)
			{
				// this also marks the session_filled_forms array
				$f = get_instance("formgen/form");
				$f->process_entry(array("id" => $id, "values" => $GLOBALS["HTTP_GET_VARS"], "entry_id" => $entry_id));
			}
		}

		$session_filled_forms = aw_global_get("session_filled_forms");

		// find all the forms in the selected join group 
		$this->db_query("SELECT id  FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE objects.status != 0 and forms.grp='$join_grp' AND forms.subtype = ".FSUBTYPE_JOIN);
		$jfrm = 0;
		while ($row = $this->db_next())
		{
			if (!$session_filled_forms[$row["id"]])
			{
				$jfrm = $row["id"];
				break;
			}
		}

		if ($jfrm)
		{
			// new approach here - user can pick an entry for the form as well now. 
			$this->read_template("show_form.tpl");
		
			$f = get_instance("formgen/form");
			$f_ref = $this->mk_reforb("process_entry", array("id" => $id,"no_reforb" => true));

			$this->vars(array(
				"form" => $f->gen_preview(array("id" => $jfrm, "tpl" => "show_noform.tpl","reforb" => $f_ref, "entry_id" => $ex_entry)),
				"entries" => $this->picker('',$f->get_entries(array("id" => $jfrm, "addempty" => true))),
				"reforb" => $this->mk_reforb("submit_ua_form", array("id" => $jfrm, "join_grp" => $join_grp,"no_reforb" => 1))
			));
			return $this->parse();
		}
		else
		{
			// and when we're dont with all of them, let the user select username/password
			if ($add_state["email"] == "")
			{
				$email_el = $this->get_cval("users::email_element");
				$name_els = aw_unserialize($this->get_cval("users::name_elements"));
				$name_els_sep = $this->get_cval("users::name_elements_sep");

				$finst = get_instance("formgen/form");

				$sff = new aw_array($session_filled_forms);
				foreach($sff->get() as $sf_id => $sf_e_id)
				{
					$sf_els = $finst->get_elements_for_forms(array($sf_id => $sf_id));
					if (isset($sf_els[$email_el]))
					{
						$finst->load($sf_id);
						$finst->load_entry($sf_e_id);
						$add_state["email"] = $finst->get_element_value($email_el);
					}
					foreach($name_els as $nelid)
					{
						if (isset($sf_els[$nelid]))
						{
							$finst->load($sf_id);
							$finst->load_entry($sf_e_id);
							$add_state["name"] .= $finst->get_element_value($nelid).$name_els_sep;
						}
					}
				}
				aw_session_set("add_state", $add_state);
			}

			$this->read_template("add.tpl");
			$this->vars(array(
				"error" => $add_state["error"], 
				"uid" => $add_state["uid"],
				"email" => $add_state["email"],
				"name" => $add_state["name"],
				"reforb"	=> $this->mk_reforb("submit_user", array("join_grp" => $join_grp))
			));
			return $this->parse();
		}
	}

	function createpwd($arr)
	{
		extract($arr);
		$this->read_template("createpwd.tpl");
		$this->mk_path(0,"Loo paroolid");

		$this->vars(array(
			"grps" => $this->picker(0,$this->get_group_picker(array("type" => array(GRP_REGULAR, GRP_DYNAMIC)))),
			"reforb" => $this->mk_reforb("submit_createpwd")
		));
		return $this->parse();
	}

	function submit_createpwd($arr)
	{
		extract($arr);
		$gm = $this->getgroupmembers2($grps);
		foreach($gm as $uid)
		{
			$pwd = substr(gen_uniq_id(),0,8);
			if (aw_ini_get("auth.md5_passwords"))
			{
				$pwd = md5($pwd);
			}
			$this->db_query("UPDATE users SET password = '$pwd' WHERE uid = '$uid'");
			echo "generated password $pwd for user $uid <br />\n";
		}
		die();
//		return $this->mk_my_orb("gen_list");
	}

	////
	// !Encrypts the passwords in the database with md5
	// don't forget to turn on auth.md5_passwords after you do that,
	// otherwise it will be impossible to log in.
	function pwconv($args = array())
	{
		print "Encrypting passwords with MD5. This may take a few moments<br />";
		flush();
		$q = "UPDATE users SET password = md5(password)";
		$this->db_query($q);
		print "Done!<br /> Don't forget to turn on auth.md5_passwords or you wont be able to log in anymore!";

		// here be dragons .. or rather the code to set the
		// variable in the site ini file

	}

	////
	// !generates a list of users that the current user has access to, returns an array suitable to be passed to picker()
	// arguments:
	//	add_empty - optional, if true, empty field is added in the beginning
	function get_user_picker($arr = array())
	{
		$ret = $this->_gen_usr_list();
		ksort($ret);
		return ($arr['add_empty'] ? array('' => '') : array()) + $ret;
	}

	function get_change_pwd_hash_link($uid)
	{
		$ts = time();
		$hash = substr(gen_uniq_id(),0,15);

		$this->set_user_config(array(
			"uid" => $uid,
			"key" => "password_hash",
			"value" => $hash,
		));

		$this->set_user_config(array(
			"uid" => $uid,
			"key" => "password_hash_timestamp",
			"value" => $ts,
		));

		$host = aw_global_get("HTTP_HOST");
		return str_replace("orb.aw", "index.aw", str_replace("/automatweb", "", $this->mk_my_orb("pwhash",array("u" => $uid,"k" => $hash),"users",0,0)));
	}

	function on_site_init(&$dbi, $site, &$ini_opts)
	{
		if ($site['site_obj']['use_existing_database'])
		{
			// fetch the neede ini opts from the base site
			$opts = $this->do_orb_method_call(array(
				"class" => "objects",
				"action" => "aw_ini_get_mult",
				"params" => array(
					"vals" => array(
						"groups.tree_root",
						"groups.all_users_grp",
					)
				)
			));
			//echo "users::on_site_init got opts = <pre>", var_dump($opts),"</pre> <br />";
			$ini_opts["groups.tree_root"] = $opts["groups.tree_root"];
			$ini_opts["groups.all_users_grp"] = $opts["groups.all_users_grp"];
		}
		else
		{
			// create parent folder for groups
			$m = get_instance("menuedit");
			// make it use the correct db connection
			$m->dc = $dbi->dc;
			// create aw objects root folder
			$aw_o_root = $m->add_new_menu(array(
				"parent" => $ini_opts["rootmenu"],
				"name" => "AW Objektd",
				"status" => 2,
				"type" => MN_CLIENT,
			));
			
			$gparent = $m->add_new_menu(array(
				"parent" => $aw_o_root,
				"name" => "Groups",
				"status" => 2,
				"type" => MN_CLIENT,
			));
			$ini_opts["groups.tree_root"] = $gparent;

			$uparent = $m->add_new_menu(array(
				"parent" => $aw_o_root,
				"name" => "Users",
				"status" => 2,
				"type" => MN_CLIENT,
			));
			$ini_opts["users.root_folder"] = $uparent;

			// create default group
			$this->dc = $dbi->dc;

			$aug = $this->addgroup(
				0,
				"K&otilde;ik kasutajad", 
				GRP_REGULAR,
				0,
				1000
			);
			$ini_opts["groups.all_users_grp"] = $aug;

			// create default user
			$this->add(array(
				"uid" => $site["site_obj"]["default_user"],
				"password" => $site["site_obj"]["default_user_pwd"],
				"all_users_grp" => $aug,
				"use_md5_passwords" => true,
				"obj_parent" => $uparent
			));
		}
		$ini_opts["auth.md5_passwords"] = 1;
	}

	function import($arr)
	{
		extract($arr);
		$this->read_template("import.tpl");
		$this->mk_path(0,"<a href='".$this->mk_my_orb("gen_list")."'>Kasutajad</a> / Impordi");


		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_import", array("gid" => $gid))
		));
		return $this->parse();
	}

	function submit_import($arr)
	{
		extract($arr);
		global $imp;

		// read the damn file
		if (is_uploaded_file($imp))
		{
			echo "Impordin kasutajaid ... <br />";
			$first = true;
			$f = fopen($imp,"r");
			while(($row = fgetcsv($f, 10000,",")))
			{
				if ($first && $first_colheaders)
				{
					$first = false;
					continue;
				}

				$uid = $row[0];
				$pass = $row[1];
				$name = $row[2];
				$email = $row[3];
				$act_to = ($row[5] == "NULL" || $row[5] == "" ? -1 : strtotime($row[5]));
				$act_from = ($row[4] == "NULL" || $row[4] == "" ? -1 : strtotime($row[4]));

				$row = $this->db_fetch_row("SELECT uid FROM users WHERE uid = '$uid'");
				if (!is_array($row))
				{
					$this->add(array(
						"uid" => $uid,
						"password" => $pass,
						"email" => $email
					));
					if ($gid)
					{
						// add to specified group
						$this->add_users_to_group_rec($gid, array($uid));
					}
				}

				if ($act_from)
				{
					$this->set_user_config(array(
						"uid" => $uid,
						"key" => "act_from",
						"value" => $act_from
					));
				}

				if ($act_to)
				{
					$this->set_user_config(array(
						"uid" => $uid,
						"key" => "act_to",
						"value" => $act_to
					));
				}

				echo "Importisin kasutaja $uid ... <br />\n";
				flush();
				$first = false;
			}
			echo "Valmis! <br />\n";
			die(html::href(array(
				"url" => $this->mk_my_orb("grp_members", array("gid" => $gid), "groups"),
				"caption" => "Tagasi"
			)));
		}
		return $this->mk_my_orb("gen_list");
	}

	////
	// !sends user welcome mail to user and others 
	// parameters:
	//	uid - the user whose mail to send
	//	pass - if set, #password# is replaced by this, 
	//	       since passwords in db are hashed, we can't read it from there
	function send_welcome_mail($arr)
	{
		extract($arr);
		$udata = $this->get_user(array(
			"uid" => $uid
		));

		// send him some email as well if the users selected to do so
		$c = get_instance("config");
		$mail = $c->get_simple_config("join_mail".aw_global_get("LC"));
		$mail = str_replace("#parool#", $pass,$mail);
		$mail = str_replace("#kasutaja#", $uid,$mail);
		$mail = str_replace("#liituja_andmed#", str_replace("\n\n","\n",$this->show_join_data(array(
			"nohtml" => true, 
			"user" => $uid
		))),$mail);
		$mail = str_replace("#pwd_hash#", $this->get_change_pwd_hash_link($uid), $mail);

		send_mail($udata["email"],$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
		$jsa = $c->get_simple_config("join_send_also");
		if ($jsa != "")
		{
			send_mail($jsa,$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
		}
	}

	function on_delete_alias($arr)
	{
		$i = get_instance("core/users/user");	
		$i->on_delete_alias($arr);
	}
}
?>
