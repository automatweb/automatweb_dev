<?php
/*
$Header: /home/cvs/automatweb_dev/classes/Attic/users.aw,v 2.190 2008/03/06 13:55:18 kristo Exp $
@classinfo  maintainer=kristo
*/
if (!headers_sent())
{
	session_register("add_state");
};

classload("users_user");
class users extends users_user
{
	function users()
	{
		$this->init("automatweb/users");
		lc_site_load("definition",&$this);
		lc_load("definition");
		$this->lc_load("users","lc_users");
	}

	/** generates the form for changing the users ($id) password
		@attrib name=change_pwd params=name is_public="1" caption="Change password"

		@param id optional
		@param error optional
	**/
	function change_pwd($arr)
	{
		extract($arr);
		if (!$id)
		{
			$id = aw_global_get("uid");
		}

		$u = $this->get_user($id);
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

	/**
		@attrib name=change_password_not_logged nologin=1 all_args=1 is_public="1"
	**/
	function change_password_not_logged($arr)
	{
		$this->read_template("changepwdnotlogged.tpl");
		$this->vars(array(
			"username" => $arr['uid'],
			"error" => $arr["error"],
		));
		return $this->parse();
	}


	/**
		@attrib name=submit_change_password_not_logged nologin=1 is_public="1"
		@param username optional
		@param old_pass optional
		@param new_pass optional
		@param new_pass_repeat optional
	**/
	function submit_change_password_not_logged($arr)
	{
		extract($arr);
		if(!$username || !$old_pass || !$new_pass || !$new_pass_repeat)
		{
			$error = t("K&otilde;ik v&auml;ljad peavad olema t&auml;idetud");
		}
		elseif($new_pass != $new_pass_repeat)
		{
			$error = t("Uus parool ja parooli kordus ei ole samad");
		}
		elseif($new_pass == $old_pass)
		{
			$error =  t("Te ei tohi panna uuesti sama vana parooli");
		}
		elseif(!is_valid("password", $old_pass))
		{
			$error = t("Vigane v&otilde;i vale parool");
		}

		elseif(!is_valid("uid", $username))
		{
			$error =  sprintf(t("Vigane kasutajanimimi %s"), $uid);
		}
		else
		{
			$auth = get_instance(CL_AUTH_SERVER_LOCAL);
			list($success, $error) = $auth->check_auth(NULL, array(
				"uid" =>  $username,
				"password" => $old_pass,
				"pwdchange" => 1,
			));
			if(!$success)
			{
				$error = t("Vana parool on vale");
			}
		}
		if($error)
		{
			return $this->mk_my_orb("change_password_not_logged", array(
				"error" => $error,
				"uid" => $username,
			), "users");
		}
		else
		if ($success)
		{
			aw_disable_acl();
				$user_obj = &obj(users::get_oid_for_uid($username));
				$logins = $user_obj->prop("logins") + 1;
				$user_obj->set_prop("logins", $logins);
				$user_obj->save();
			aw_restore_acl();

			$this->login(array(
				"uid" => $username,
				"password" => $old_pass,
			));
			$this->submit_change_pwd(array(
				"pwd" => $new_pass,
				"pwd2" => $new_pass_repeat,
				"id" => aw_global_get("uid"),
			));
		}

	}

	/** saves the uses changed password
		@attrib name=submit_change_pwd params=name 
	**/
	function submit_change_pwd($arr)
	{
		extract($arr);
		$udata = $this->get_user($id);
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
			return $this->mk_my_orb("change_pwd", array("id" => $id, "error" => "Uus parool sisaldab lubamatuid m&auml;rke<br />"));
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
			$mail = str_replace("#pwd_hash#", $this->get_change_pwd_hash_link($arr["id"]), $mail);

			send_mail($udata["email"],$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
			$jsa = $c->get_simple_config("join_send_also");
			if ($jsa != "")
			{
				send_mail($jsa,$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
			}
		}

		$this->_log(ST_USERS, SA_CHANGE_PWD, $arr['id']);

		if (!empty($arr["return_url"]))
		{
			return $arr["return_url"];
		}

		if (is_admin())
		{
			return $this->mk_my_orb("gen_list", array(), "users");
		}
		else
		{
			header("Refresh: 2;url=".$this->cfg["baseurl"]);
			die(t("Parool on edukalt vahetatud"));
		}
	}

	/** Generates an unique hash, which when used in a url can be used to let the used change his/her password
		@attrib name=send_hash params=name nologin="1" 
	**/
	function send_hash($args = array())
	{
		extract($args);

		if (!aw_ini_get("auth.md5_passwords"))
		{
			return "<font color=red>This site does not use encrypted passwords and therefore this function does not work</font>";
		};

		$this->read_template("send_hash.tpl");

		lc_site_load("users", &$this);
		$this->vars(array(
			"webmaster" => $this->cfg["webmaster_mail"],
			"reforb" => $this->mk_reforb("submit_send_hash",array("section" => aw_global_get("section"))),
		));

		return $this->parse();
	}

	/** 
		@attrib name=submit_send_hash params=name nologin="1" 
	**/
	function submit_send_hash($args = array())
	{
		extract($args);
		extract($_POST);
		if (($type == "uid") && not(is_valid("uid",$uid)))
		{
			aw_session_set("status_msg",t("Vigane kasutajanimi"));
			return $this->mk_my_orb("send_hash",array());
		};
		if (($type == "email") && !(is_email($email)))
		{
			aw_session_set("status_msg",t("Vigane e-posti aadress"));
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
				$status_msg .= t("Kasutajal $uid puudub korrektne e-posti aadress. Palun p&ouml;&ouml;rduge veebisaidi haldaja poole");
				aw_session_set("status_msg", $status_msg);
				return $this->mk_my_orb("send_hash",array());
			};

			$this->read_template("hash_send.tpl");
			lc_site_load("users", &$this);
			$this->vars(array(
				"churl" => $this->get_change_pwd_hash_link($uid),
				"email" => $this->cfg["webmaster_mail"],
				"name_wm" => $this->cfg["webmaster_name"],
				"uid" => $row["uid"],
				"host" => $host,
			));
			$msg = $this->parse();
			$from = sprintf("%s <%s>", $this->cfg["webmaster_name"], $this->cfg["webmaster_mail"]);
			send_mail(
				$row["email"],
				sprintf(t("Paroolivahetus saidil %s"), aw_global_get("HTTP_HOST")), $msg, "From: $from");
			aw_session_set(
				"status_msg",
				sprintf(t("Parooli muutmise link saadeti  aadressile <b>%s</b>. Vaata oma postkasti<br />T&auml;name!<br />"), $row["email"])
			);
		};
		return $this->mk_my_orb("send_hash",array("section" => $args["section"]));
	}

	/** Allows the user to change his/her password
		@attrib name=pwhash params=name nologin="1" default="0"

		@param k required
		@param u required
	**/
	function password_hash($args = array())
	{
		extract($args);
		$uid = $u;
		$key = $k;
		if (!(is_valid("uid",$uid)))
		{
			$this->read_adm_template("hash_results.tpl");
			lc_site_load("users", &$this);
			$this->vars(array(
				"msg" => t("Vigane kasutajanimi"),
			));
			return $this->parse();
		};

		$q = "SELECT * FROM users WHERE uid = '$uid' AND blocked = '0'";
		$this->db_query($q);
		$row = $this->db_next();
		if (not($row))
		{
			$this->read_adm_template("hash_results.tpl");
		lc_site_load("users", &$this);
			$this->vars(array(
				"msg" => t("Sellist kasutajat pole registreeritud"),
			));
			return $this->parse();
		};

		aw_disable_acl();
		$uo = obj($row["oid"]);
		aw_restore_acl();
		$pwhash = $uo->meta("password_hash");
		if ($pwhash != $key)
		{
			$this->read_adm_template("hash_results.tpl");
		lc_site_load("users", &$this);
			$this->vars(array(
				"msg" => t("Sellist v&otilde;tit pole v&auml;ljastatud"),
			));
			return $this->parse();
		};

		$ts = $uo->meta("password_hash_timestamp");

		// default expiration time is 1 hour (3600 seconds)
		if (($ts + (3600*24*400)) < time())
		{
			$this->read_adm_template("hash_results.tpl");
		lc_site_load("users", &$this);
			$this->vars(array(
				"msg" => t("See v&otilde;ti on juba aegunud")." <a href='".$this->mk_my_orb('send_hash')."'>".t("Telli uusi v&otilde;ti")."</a>"
			));
			return $this->parse();
		}

		$this->read_adm_template("hash_change_password.tpl");
		lc_site_load("users", &$this);
		$this->vars(array(
			"uid" => $uid,
			"reforb" => $this->mk_reforb("submit_password_hash",array("uid" => $uid,"pwhash" => $pwhash)),
		));
		return $this->parse();
	}

	/** Submits the password
		@attrib name=submit_password_hash params=name nologin="1"
	**/
	function submit_password_hash($args = array())
	{
		extract($args);	
		$q = "SELECT * FROM users WHERE uid = '$uid' AND blocked = 0";
		$this->db_query($q);
		$row = $this->db_next();
		if (not($row))
		{
			aw_session_set("status_msg",t("Sellist kasutajat pole registreeritud"));
			return $this->mk_my_orb("send_hash",array());
		};

		$uo = obj($row["oid"]);
		$pwhash1 = $uo->meta("password_hash");
		if ($pwhash1 != $pwhash)
		{
			aw_session_set("status_msg",t("Sellist v&otilde;tit pole v&auml;ljastatud"));
			return $this->mk_my_orb("pwhash",array("u" => $uid,"k" => $pwhash));
		};

		if (not(is_valid("password",$pass1)))
		{
			aw_session_set("status_msg",t("Parool sisaldab keelatud m&auml;rke"));
			return $this->mk_my_orb("pwhash",array("u" => $uid,"k" => $pwhash));
		};

		if ($pass1 != $pass2)
		{
			aw_session_set("status_msg",t("Paroolid peavad olema &uuml;hesugused"));
			return $this->mk_my_orb("pwhash",array("u" => $uid,"k" => $pwhash));
		};

		// tundub, et k&otilde;ik on allright. muudame parooli &auml;ra
		$newpass = md5($pass1);
		$q = "UPDATE users SET password = '$newpass' WHERE uid = '$uid'";
		$this->db_query($q);
		$this->_log(ST_USERS, SA_CHANGE_PWD, $uid);
		aw_session_set("status_msg","<b><font color=green>".t("Parool on edukalt vahetatud.")."</font></b>");
		return $this->login(array("uid" => $uid, "password" => $pass1));
	}

	function kill_user()
	{
		aw_session_set("uid","");
		session_destroy();
		header("Location: ".aw_ini_get("baseurl"));
		die();
	}

	function create_gidlists($uid)
	{
		$gidlist = array();
		$gidlist_pri = array();
		$gidlist_pri_oid = array();
		$gidlist_oid = array();
		$gl = $this->get_gids_by_uid($uid,true);
		foreach($gl as $gid => $gd)
		{
			$gidlist[(int)$gid] = (int)$gd["gid"];
			$gidlist_pri[(int)$gid] = (int)$gd["priority"];
			if ($gd["oid"])
			{
				$gidlist_pri_oid[(int)$gd["oid"]] = (int)$gd["priority"];
				$gidlist_oid[(int)$gd["oid"]] = (int)$gd["oid"];
			}
			if ($gd["name"] == $uid && $gd["type"] == 1)
			{
				aw_global_set("current_user_group", $gd);
			}
		}

		if (!empty($_SESSION["nliug"]) && !is_admin())
		{
			// get gid for oid
			$nliug_o = obj($_SESSION["nliug"]);
			$gidlist[$nliug_o->prop("gid")] = $nliug_o->prop("gid");
			$gidlist_pri[$nliug_o->prop("gid")] = $nliug_o->prop("priority");
			$gidlist_oid[$nliug_o->id()] = $nliug_o->id();
			$gidlist_pri_oid[(int)$nliug_o->id()] = (int)$nliug_o->prop("priority");
		}

		aw_global_set("gidlist", $gidlist);
		aw_global_set("gidlist_pri", $gidlist_pri);
		aw_global_set("gidlist_pri_oid", $gidlist_pri_oid);
		aw_global_set("gidlist_oid", $gidlist_oid);
	}

	function request_startup()
	{
		if (isset($_GET["set_group"]) && $this->can("view", $_GET["set_group"]))
		{
			// fetch thegroup and check if non logged users can switch to it
			$setg_o = obj($_GET["set_group"]);
			if ($setg_o->prop("for_not_logged_on_users") == 1)
			{
				$_SESSION["nliug"] = $_GET["set_group"];
				$_COOKIE["nliug"] = $_GET["set_group"];
			}
		}
		if (!empty($_GET["clear_group"]))
		{
			unset($_SESSION["nliug"]);
			unset($_COOKIE["nliug"]);
		}
		if ((!empty($_COOKIE["nliug"]) || !empty($_SESSION["nliug"])) && $_COOKIE["nliug"] != $_SESSION["nliug"] && $_COOKIE["nliug"])
		{
			$_SESSION["nliug"] = $_COOKIE["nliug"];
		}


		if (!isset($_SESSION["nliug"]))
		{
			$_SESSION["nliug"] = null;
		}

		if (($uid = aw_global_get("uid")) != "")
		{
			$this->create_gidlists($uid);
			$gidlist = aw_global_get("gidlist");
			$gidlist_pri = aw_global_get("gidlist_pri");
			if (count($gidlist) < 1)
			{
				$this->kill_user();
			}
			// get highest priority group
			$hig = 0;
			$hig_p = -1;
			$hig_w_u = 0;
			$hig_w_u_p = -1;
			foreach($gidlist_pri as $_gid => $_pri)
			{
				if ($_pri > $hig_p && $_pri != 100000000)
				{
					$hig_p = $_pri;
					$hig = $_gid;
				}
				if ($_pri > $hig_w_u_p)
				{
					$hig_w_u_p = $_pri;
					$hig_w_u = $_gid;
				}
			}

			if ($hig)
			{
				$_oid = $this->get_oid_for_gid($hig);
				if ($_oid)
				{
					aw_disable_acl();
					$o = obj($_oid);
					aw_restore_acl();
					$ar2 = $o->meta("admin_rootmenu2");
					$gf = $o->meta("grp_frontpage");
					$lang_id = aw_global_get("lang_id");
					if (is_array($ar2) && $ar2[$lang_id])
					{
						aw_ini_set("admin_rootmenu2",$ar2[$lang_id]);
						$inrm = aw_ini_get("ini_rootmenu");
						if (!$inrm)
						{
							$inrm = aw_ini_get("rootmenu");
						}
						aw_ini_set("ini_rootmenu", $inrm);
						aw_ini_set("rootmenu",is_array($ar2[$lang_id]) ? reset($ar2[$lang_id]) : $ar2[$lang_id]);
					}
					if (is_array($gf) && $gf[$lang_id])
					{
						aw_ini_set("frontpage",$gf[$lang_id]);
					}
				}
			}

			if ($hig_w_u)
			{
				$_oid = $this->get_oid_for_gid($hig_w_u);
				if ($_oid)
				{
					aw_disable_acl();
					$o = obj($_oid);
					aw_restore_acl();
					$ar2 = $o->meta("admin_rootmenu2");
					$gf = $o->meta("grp_frontpage");
					$lang_id = aw_global_get("lang_id");
					if (is_array($ar2) && $ar2[$lang_id])
					{
						aw_ini_set("admin_rootmenu2",$ar2[$lang_id]);
						$inrm = aw_ini_get("ini_rootmenu");
						if (!$inrm)
						{
							$inrm = aw_ini_get("rootmenu");
						}
						aw_ini_set("ini_rootmenu", $inrm);
						aw_ini_set("rootmenu",is_array($ar2[$lang_id]) ? reset($ar2[$lang_id]) : $ar2[$lang_id]);
					}
					if (is_array($gf) && $gf[$lang_id])
					{
						aw_ini_set("frontpage",$gf[$lang_id]);
					}
				}
			}

			if (aw_ini_get("groups.multi_group_admin_rootmenu"))
			{
				$admr = array();
				foreach($gidlist_pri as $_gid => $_pri)
				{
					$_oid = $this->get_oid_for_gid($_gid);
					aw_disable_acl();
					$o = obj($_oid);
					aw_restore_acl();
					$ar2 = $o->meta("admin_rootmenu2");
					$lang_id = aw_global_get("lang_id");
					if (is_array($ar2) && $ar2[$lang_id])
					{
						$awa = new aw_array($ar2[$lang_id]);
						foreach($awa->get() as $k => $v)
						{
							$admr[] = $v;
						}
					}
					$admr = array_unique($admr);
				}
				if (count($admr))
				{
					aw_ini_set("admin_rootmenu2",$admr);
				}
			}
		}
		else
		{
			// no user is logged in. what we need to do here is check if a not-logged-in user group exists
			// and if it does, then set the gidlist accordingly
			// if not, then create a group for them under the groups folder
			// now the only problem is how do I identify the group.
			// that's gonna be a problem, but I guess the only way is the config table.

			if (empty($_SESSION["non_logged_in_users_group"]) || !is_array($_SESSION["non_logged_in_users_group"]))
			{
				$nlg = $this->get_cval("non_logged_in_users_group");
				if (!$nlg && ($grpp = aw_ini_get("groups.tree_root")))
				{
					aw_disable_acl();
					$nlg = $this->addgroup(0, "Sisse logimata kasutajad", GRP_REGULAR, 0, 1, 0, $grpp);
					aw_restore_acl();
					$this->set_cval("non_logged_in_users_group", $nlg);
				}

				$gd = $this->fetchgroup($nlg);

				$_SESSION["non_logged_in_users_group"] = array(
					"nlg" => $nlg,
					"gd" => $gd
				);
			}
			else
			{
				$nlg = $_SESSION["non_logged_in_users_group"]["nlg"];
				$gd = $_SESSION["non_logged_in_users_group"]["gd"];
			}

			$gidlist = array($nlg => $nlg);
			$gidlist_pri = array($nlg => $gd["priority"]);
			$gidlist_oid = array($gd["oid"] => $gd["oid"]);
			$gidlist_pri_oid[(int)$gd["oid"]] = (int)$gd["priority"];
			if (!empty($_SESSION["nliug"]))
			{
				// get gid for oid
				$nliug_o = obj($_SESSION["nliug"]);
				$gidlist[$nliug_o->prop("gid")] = $nliug_o->prop("gid");
				$gidlist_pri[$nliug_o->prop("gid")] = $nliug_o->prop("priority");
				$gidlist_oid[$nliug_o->id()] = $nliug_o->id();
				$gidlist_pri_oid[(int)$nliug_o->id()] = (int)$nliug_o->prop("priority");
			}

			aw_global_set("gidlist", $gidlist);
			aw_global_set("gidlist_pri", $gidlist_pri);
			aw_global_set("gidlist_oid", $gidlist_oid);
			aw_global_set("gidlist_pri_oid", $gidlist_pri_oid);
		}

		if (!is_array(aw_global_get("gidlist")))
		{
			aw_global_set("gidlist", array());
			aw_global_set("gidlist_pri", array());
		}
	}

	function get_change_pwd_hash_link($uid)
	{
		$ts = time();
		$hash = substr(gen_uniq_id(),0,15);

		aw_disable_acl();
		$uo = obj($this->get_oid_for_uid($uid));
		$uo->set_meta("password_hash",$hash);
		$uo->set_meta("password_hash_timestamp",$ts);
		if ($uo->parent())
		{
			$uo->save();
		}
		aw_restore_acl();

		$host = aw_global_get("HTTP_HOST");
		return str_replace("orb.aw", "index.aw", str_replace("/automatweb", "", $this->mk_my_orb("pwhash",array(
			"u" => $uid,
			"k" => $hash,
			"section" => $this->get_cval("join_hash_section".aw_global_get("LC"))
		),"users",0,0)));
	}

	function on_site_init(&$dbi, $site, &$ini_opts, &$log, &$osi_vars)
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
						"auth.md5_passwords",
					)
				)
			));
			//echo "users::on_site_init got opts = <pre>", var_dump($opts),"</pre> <br />";
			$ini_opts["groups.tree_root"] = $opts["groups.tree_root"];
			$ini_opts["groups.all_users_grp"] = $opts["groups.all_users_grp"];
			$ini_opts["auth.md5_passwords"] = $opts["auth.md5_passwords"];
		}
		else
		{
			// create default group
			$this->dc = $dbi->dc;

			obj_set_opt("no_cache", 1);
			echo "adding groups... <br>\n";
			flush();

			$aug = $this->addgroup(0,"K&otilde;ik kasutajad", GRP_REGULAR,0,1000,0,$ini_opts["groups.tree_root"]);
			$ini_opts["groups.all_users_grp"] = $aug;

			$admg = $this->addgroup(0,"Administraatorid", GRP_REGULAR,0,10000,0,$ini_opts["groups.tree_root"]);
			echo "Administraatorid <br>\n";
			flush();
			$osi_vars["groups.admins"] = $this->get_oid_for_gid($admg);

			$nlg = $this->addgroup(0, "Sisse logimata kasutajad", GRP_REGULAR, 0, 1, 0, $ini_opts["groups.tree_root"]);
			$this->set_cval("non_logged_in_users_group", $nlg);
			$osi_vars["groups.not_logged"] = $this->get_oid_for_gid($nlg);

			// deny access from aw_obj_priv
			$o = obj($osi_vars["aw_obj_priv"]);
			$o->connect(array(
				"to" => $this->get_oid_for_gid($nlg),
				"reltype" => RELTYPE_ACL,
			));
			$this->save_acl($o->id(), $nlg, array());

			echo "Sisse logimata kasutajad <br>\n";
			flush();


			// give admins access to admin interface


			aw_global_set("__in_post_message", 1);
			// can not use cache here, go direct
			$adm_oid = $this->db_fetch_field("SELECT oid FROM groups WHERE gid = '$admg'", "oid");
			$admo = obj($adm_oid);
			$admo->set_prop("can_admin_interface", 1);
			$admo->save();

			$editors = $this->addgroup(0,"Toimetajad", GRP_REGULAR,0,5000,0,$ini_opts["groups.tree_root"]);
			echo "Toimetajad <br>\n";
			flush();
			$osi_vars["groups.editors"] = $this->db_fetch_field("SELECT oid FROM groups WHERE gid = '$editors'", "oid");


			/*$this->addgroup(0,"Kliendid", GRP_REGULAR,0,2500,0,$ini_opts["groups.tree_root"]);
			echo "Kliendid <br>\n";
			flush();
			$this->addgroup(0,"Partnerid", GRP_REGULAR,0,3000,0,$ini_opts["groups.tree_root"]);
			echo "Partnerid <br>\n";
			flush();*/

			// create default user
			$us = get_instance(CL_USER);
			$user_o = $us->add_user(array(
				"uid" => $site["site_obj"]["default_user"],
				"password" => $site["site_obj"]["default_user_pwd"],
				"all_users_grp" => $aug,
				"use_md5_passwords" => true,
				"obj_parent" => $ini_opts["users.root_folder"]
			));
			$user_o->set_parent($ini_opts["users.root_folder"]);
			$user_o->save();
			$this->last_user_oid = $user_o->id();
			echo "Adding users... <br>\n";
			flush();

			// add user to admin group
			$this->add_users_to_group_rec($admg,array($site["site_obj"]["default_user"]),true,true,false);
			echo "adding user to groups! <br>\n";
			flush();
			$this->_install_create_g_u_o_rel($this->last_user_oid, $this->db_fetch_field("SELECT oid FROM groups WHERE gid = '$admg'", "oid"));
			echo "administrator <br>\n";
			flush();
			$this->_install_create_g_u_o_rel($this->last_user_oid, $this->db_fetch_field("SELECT oid FROM groups WHERE gid = '$aug'", "oid"));
			echo "all users <br>\n";
			flush();
			aw_global_set("__in_post_message", 0);
			$ini_opts["auth.md5_passwords"] = 1;
		}
	}

	function _install_create_g_u_o_rel($u_oid, $g_oid)
	{
		// create objects
		$u_o = obj($u_oid);
		$u_o->create_brother($g_oid);
		$u_o->connect(array(
			"to" => $g_oid,
			"reltype" => "RELTYPE_GRP" // from user
		));

		$g_o = obj($g_oid);
		$g_o->connect(array(
			"to" => $u_o->id(),
			"reltype" => "RELTYPE_MEMBER" // from group
		));
	}

	/** sends user welcome mail to user and others
		@attrib api=1 params=name
		
		@param uid required type=string
			the user whose mail to send

		@param pass optional type=string
			if set, #password# is replaced by this, since passwords in db are hashed, we can't read it from there

		@comment
			Mail content is read from join_mail$LC in config table
	**/
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
		$mail = str_replace("#pwd_hash#", $this->get_change_pwd_hash_link($uid), $mail);

		send_mail($udata["email"],$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
		$jsa = $c->get_simple_config("join_send_also");
		if ($jsa != "")
		{
			send_mail($jsa,$c->get_simple_config("join_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
		}
	}

	/**
		@comment
			fixes umlauts etc in name.
	**/
	function fix_name($name, $space = "_")
	{
		$name = strtolower($name);
		$name = trim($name);
		$to_replace = array("&auml;","&ouml;","&uuml;","&otilde;", " ");
		$replace_with = array("a","o","u","o", $space);
		$str = "!\"@#.$%&/()[]={}?\+-`'|,;";
		$name = str_replace(preg_split("//", $str, -1 , PREG_SPLIT_NO_EMPTY), "", $name);
		$name = str_replace($to_replace, $replace_with, strtolower(htmlentities($name)));
		return $name;
	}

	/**
		@attrib params=pos
		@param first required type=string
		@param last required type=string
		@comment
		finds first available uid in format firstname.lastname[.###]
		etc: 'john.smith','johm.smith.051' ...
	**/
	function _find_username($first, $last)
	{

		$first = $this->fix_name($first);
		$last = $this->fix_name($last);
		$suffix = "";
		$count = 0;
		$user = get_instance("core/users/user");
		while(true)
		{
			$uid = $first.".".$last.$suffix;
			if(!$user->username_is_taken($uid))
			{
				return $uid;
			}
			$count++;
			$suffix = ".".str_pad ($count, 3, "0", STR_PAD_LEFT);
			if($count > 999)
			{
				return false;
			}
		}
	}

	/**
		@attrib name=id_pre_login params=name nologin=1
		@comment
		Logs user in with id-card over ssl.
	**/
	function id_pre_login($arr)
	{
		//dbg::p1($_SERVER);
		//arr($_SERVER);
		//die();
		if($_SERVER["HTTPS"] != "on")
		{
			return aw_ini_get("baseurl");
		}

		// well.. this is a nice ocsp check. this checks wheater the user's certificate is valid at current point or not
		// when this feature is turned off(ocsp service is provided as a priced service(in id-card situation at least)), the function returs 'all okay'
		$ocsp = get_instance(CL_OCSP);
		$ocsp_retval = $ocsp->OCSP_check($_SERVER["_SSL_CLIENT_CERT"], $_SERVER["SSL_CLIENT_I_DN_CN"]);
		if($ocsp_retval !== 1)
		{
			return aw_ini_get("baseurl");
		}

		classload("core/users/id_config");
		$act_inst = get_instance(CL_ID_CONFIG);
		$act = id_config::get_active();


		// this little modafocka is here beacause estonian language has freaking umlauts etc..
		$data = $this->returncertdata($_SERVER["SSL_CLIENT_CERT"]);
		$arr["firstname"] = $data["f_name"];
		$arr["lastname"] = $data["l_name"];
		$arr["ik"] = $data["pid"];

		/* for debug
		if($_SERVER["REMOTE_ADDR"] == "62.65.36.186")
		{
		}
		*/

		$arr["gender"] = ($arr["ik"][0] == 1 || $arr["ik"][0] == 3 || $arr["ik"][0] == 5)?1:2;


		if($act_inst->use_safelist())
		{
			$sl = $act_inst->get_safelist();
			if(!in_array($arr["ik"], array_keys($sl)))
			{
				return aw_ini_get("baseurl");
			}
		}

		if(!$arr["firstname"] || !$arr["lastname"] || !$arr["ik"])
		{
			$url = aw_ini_get("baseurl")."/orb.aw?class=ddoc&action=no_ddoc";
			$tpl = new aw_template();
			$tpl->init(array(
				"tpldir" => "common/digidoc/idErr"
			));
			$tpl->read_template("error.tpl");
			die($tpl->parse());
		}
		$arr["uid"] = $this->_find_username($arr["firstname"],$arr["lastname"]);
		$password = substr(gen_uniq_id(),0,8);
		aw_disable_acl();
		$ol = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"personal_id" => $arr["ik"],
			"site_id" => array(),
			"lang_id" => array(),
			"status" => new obj_predicate_not(STAT_DELETED),
			//"status" => STAT_ACTIVE,
		));
		if($ol->count() < 1 && aw_ini_get("users.id_only_existing") == "1")
		{
			aw_restore_acl();
			return aw_ini_get("baseurl");
		}
		if($ol->count() < 1)
		{
			$gr_inst = get_instance(CL_GROUP);
			$grs = $act_inst->get_ugroups();
			$user = get_instance("core/users/user");
			$u_obj = $user->add_user(array(
				"uid" => $arr["uid"],
				"password" => $password,
				"real_name" => $arr["firstname"]." ".$arr["lastname"],
			));
			// set new users user groups depending on the active id_config settings
			foreach($grs as $gr)
			{
				$gr_obj = obj($gr);
				$gr_inst->add_user_to_group($u_obj, $gr_obj);
			}

			//aw_switch_user(array("uid" => $u_obj->id()));

			$person_obj = new object();
			$person_obj->set_class_id(145);
			$person_obj->set_parent(aw_ini_get("users.root_folder"));
			$person_obj->set_name($arr["uid"]);
			$person_obj->set_prop("personal_id",$arr["ik"]);
			$person_obj->set_prop("firstname",$arr["firstname"]);
			$person_obj->set_prop("lastname",$arr["lastname"]);
			$person_obj->set_prop("gender",$arr["gender"]);
			$person_id = $person_obj->save_new();



			$o = new object($u_obj->id());
			$o->connect(array(
				"to" => $person_id,
				"type" => 2,
			));
			$o->save();
			$c = get_instance("cache");
			$c->file_clear_pt("storage_object_data");
			$c->file_clear_pt("storage_search");
			$c->file_clear_pt("acl");
		}
		else
		{
			//obj_set_opt("no_cache", 1);
			//$GLOBALS["DUKE"] = 1;
			$c = new connection();
			$conns = $c->find(array(
				"from.class_id" => CL_USER,
				"to" => $ol->ids()
			));
			$us = get_instance("users_user");


			if(count($conns) < 1)
			{
				$person_id = current($ol->ids());
				$user = get_instance("core/users/user");
				$u_obj = $user->add_user(array(
					"uid" => $arr["uid"],
					"password" => $password,
					"real_name" => $arr["firstname"]." ".$arr["lastname"],
				));


				$o = new object($u_obj->id());
				$o->connect(array(
					"to" => $person_id,
					"type" => 2,
				));
				$o->save();
				$c = get_instance("cache");
				$c->file_clear_pt("storage_object_data");
				$c->file_clear_pt("storage_search");
				$c->file_clear_pt("acl");
			}
			else
			{
				$conn = current($conns);
				$obj = new object($conn["from"]);
				$arr["uid"] = $obj->prop("name");
			}
		}
		aw_restore_acl();
		$hash = gen_uniq_id();
		$q = "INSERT INTO user_hashes (hash, hash_time, uid) VALUES('".$hash."','".(time()+60)."','".$arr["uid"]."')";
		$res = $this->db_query($q);
		return $this->login(array("hash" => $hash ,"uid" => $arr["uid"]));
	}

	/** login

		@attrib name=login params=name default="0" nologin="1" is_public="1" caption="Logi sisse"

		@param uid required
		@param password optional
		@param remote_ip optional
		@param reforb optional
		@param remote_host optional
		@param return optional
		@param hash optional
		@param server optional
		@param remote_auth optional

		@returns


		@comment
			logs the user in, if all arguments are correct and redirects to the correct url

	**/
	function login($arr = array())
	{
		// if hash is given and it is in the db
		if (!empty($arr["hash"]))
		{
			$q = "
				SELECT
					*
				FROM
					user_hashes
				WHERE
					hash = '$arr[hash]' AND
					hash_time > ".time()." AND
					uid = '$arr[uid]'
			";
	//		echo "q =- $q <br>\n\n";
		//	flush();
			$row = $this->db_fetch_row($q);
		//	echo "row = ".dbg::dump($row)."\n\n\n";
		//	flush();
			if ($row["hash"] == $arr["hash"])
			{
				// do quick login
				$_SESSION["uid"] = $arr["uid"];
				$_SESSION["login_hash_data"] = $row;
				aw_global_set("uid", $arr["uid"]);
				$oid = $this->get_oid_for_uid($arr["uid"]);
				aw_session_set("uid_oid", $oid);
				if (is_oid($oid) && $this->can("view", $oid))
				{
					$o = obj($oid);
					aw_session_set("user_adm_ui_lc", $o->prop("ui_language"));
				}
				$this->request_startup();

				// remove hash from usable hashes
				$this->db_query("DELETE FROM user_hashes WHERE hash = '$arr[hash]'");

				// remove stale hash table entries
				$this->db_query("DELETE FROM user_hashes WHERE hash_time < ".(time() - 60*24*3600));
				//echo "logged in user  \n\n\n";
				//flush();

				$url = ($t = urldecode(aw_global_get("request_uri_before_auth")))?$t:aw_ini_get("baseurl");
				if ($url == aw_ini_get("baseurl")."/login.aw")
				{
					$url = aw_ini_get("baseurl");
				}
				return $url;
			}
		}
		return parent::login($arr);
	}

	/** logout

		@attrib name=logout params=name default="0" nologin="1" is_public="1" caption="Logi v&auml;lja"

		@returns


		@comment
			logs the current user out

	**/
	function orb_logout($arr = array())
	{
		return parent::orb_logout($arr);
	}

	/**
		@comment
			converts certificates subject value to ISO-8859-1
	**/
	function certstr2utf8($str) {
		$result="";
		$encoding=mb_detect_encoding($str,"ASCII, UTF-8");
		if ($encoding=="ASCII")
		{
			$result=mb_convert_encoding($str, "ISO-8859-1", "ASCII");
		}
		else
		{
			if (substr_count($str,chr(0))>0)
			{
				$result=mb_convert_encoding($str, "ISO-8859-1", "UCS2");
			}
			else
			{
				$result=$str;
			}
		}
		return $result;
	}

	/**
		@comment
			Returns certificate info as an array in ISO-8859-1 charset
		@returns
			array(
				f_name => firstname,
				l_name => lastname,
				pid => personal id,
	**/
	function returncertdata($cert)
	{
		$data = array();
		$certstructure=openssl_x509_parse($cert);

		if (strpos($_SERVER["SSL_VERSION_LIBRARY"],"0.9.6")===false)
		{
			$data['f_name'] = $this->certstr2utf8($certstructure["subject"]["GN"]);
			$data['l_name'] = $this->certstr2utf8($certstructure["subject"]["SN"]);
			$data['pid'] = $certstructure["subject"]["serialNumber"];
		}
		else
		{
			$data['f_name'] = $certstructure["subject"]["SN"];
			$data['l_name'] = $this->certstr2utf8($certstructure["subject"]["G"]);
			$data['pid'] = $this->certstr2utf8($certstructure["subject"]["S"]);
		}
		return $data;
	}
}
?>