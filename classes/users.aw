<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/users.aw,v 2.140 2005/04/05 13:52:32 kristo Exp $
// users.aw - User Management

load_vcl("table","date_edit");
if (!headers_sent())
{
	session_register("add_state");
};
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

	/**  
		
		@attrib name=do_change params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
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

	/** generates the form for changing the users ($id) password 
		
		@attrib name=change_pwd params=name is_public="1" caption="Change password" default="0"
		
		@param id optional
		@param error optional
		
		@returns
		
		
		@comment

	**/
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
			$error = "Kõik väljad peavad olema täidetud";
		}
		elseif($new_pass != $new_pass_repeat)
		{
			$error = "Uus parool ja parooli kordus ei ole samad";
		}
		elseif($new_pass == $old_pass)
		{
			$error =  "Te ei tohi panna uuesti sama vana parooli";
		}
		elseif(!is_valid("password", $old_pass))
		{
			$error = "Vigane v&otilde;i vale parool";
		}
		
		elseif(!is_valid("uid", $username))
		{
			$error =  "Vigane kasutajanimimi $uid";
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
				$error = "Vana parool on vale";
			}
		}
		if($error)
		{
			return $this->mk_my_orb("change_password_not_logged", array(
				"error" => $error,
				"uid" => $username,
			), "users");
			die();
		}
		elseif ($success)
		{
			//sellepärast ,et me teda uuesti parooli muutmisele ei saadaks paneme talle ühe logini kirja
			//$q = "UPDATE users SET logins = logins+1 WHERE uid = '$username'";
			
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
		
		@attrib name=submit_change_pwd params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

		if (!empty($arr["return_url"]))
		{
			return $arr["return_url"];
		}
		
		if (is_admin())
		{
			return $this->mk_my_orb("gen_list", array());
		}
		else
		{
			header("Refresh: 2;url=".$this->cfg["baseurl"]);
			die(t("Parool on edukalt vahetatud"));
		}
	}

	/** adds the user and ssets all join form entries from site interface 
		
		@attrib name=submit_user_site params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
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

			$us = get_instance(CL_USER);
			$us->add_user(array(
				"join_form_entry" => $jfs, 
				"uid" => $add_state["uid"], 
				"password" => $add_state["pass"],
				"email" => $add_state["email"], 
				"join_grp" => $join_grp
			));			

			$si = __get_site_instance();
			if (method_exists($si, "on_add_user_site"))
			{
				$si->on_add_user_site($add_state["uid"]);
			}

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
			return $this->cfg["baseurl"]."/".$after_join;
		}
		else
		{
			$add_state["level"] = 0;
			return $this->cfg["baseurl"]."/".$section;
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
			if (defined("LC_USERADD_ERROR_EXISTS"))
			{
				$te = LC_USERADD_ERROR_EXISTS;
			}
			else
			{
				$te = "Sellise kasutajanimega kasutaja on juba olemas!";
			}
			$add_state["error"] = $te;
			return false;
		}

		if (!is_valid("uid",$a_uid))
		{
			if (defined("LC_USERADD_ERROR_SYMBOL"))
			{
				$te = LC_USERADD_ERROR_SYMBOL;
			}
			else
			{
				$te = "Kasutajanimes tohivad sisalduda ainult t&auml;hed, numbrid ja alakriips!";
			}
			$add_state["error"] = $te;
			return false;
		}

		if ($pass != $pass2)
		{
			if (defined("LC_USERADD_ERROR_PWD"))
			{
				$te = LC_USERADD_ERROR_PWD;
			}
			else
			{
				$te = "Sisestatud paroolid on erinevad!";
			}
			$add_state["error"] = $te;
			return false;
		}

		if (!is_valid("password", $pass))
		{
			if (defined("LC_USERADD_ERROR_PWD_SYMBOL"))
			{
				$te = LC_USERADD_ERROR_PWD_SYMBOL;
			}
			else
			{
				$te = "Parool tohib sisaldada ainult numbreid, t&auml;hti ja alakriipsu!";
			}
			$add_state["error"] = $te;
			return false;
		}

		if (strlen($a_uid) < 3)
		{
			if (defined("LC_USERADD_ERROR_SHORT"))
			{
				$te = LC_USERADD_ERROR_SHORT;
			}
			else
			{
				$te = "Kasutajanimes peab olema v&auml;hemalt 3 t&auml;hte!";
			}
			$add_state["error"] = $te;
			return false;
		}

		if (strlen($pass) < 3)
		{
			if (defined("LC_USERADD_ERROR_PWD_SHORT"))
			{
				$te = LC_USERADD_ERROR_PWD_SHORT;
			}
			else
			{
				$te = "Paroolis peab olema v&auml;hemalt 3 t&auml;hte!";
			}
			$add_state["error"] = $te;
			return false;
		}
		$add_state["error"] = "";
		return true;
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
					$f = get_instance(CL_FORM);
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

	/** shows the form $fid with the entry the user entered when he/she joined 
		
		@attrib name=udata params=name is_public="1" caption="Edit information" default="0"
		
		@param fid optional type=int
		
		@returns
		
		
		@comment

	**/
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

		$t = get_instance(CL_FORM);
		return $t->gen_preview(array(
			"id" => $fid, 
			"entry_id" => $fs[$fid], 
			"reforb" => $this->mk_reforb("save_udata", array("fid" => $fid,"user_id" => $id,"section" => aw_global_get("section")))
		));
	}

	/** this saves the data entered in the form and flushes all necessary caches and group memberships 
		
		@attrib name=save_udata params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_do_change_site($arr)
	{
		extract($arr);

		$u = $this->fetch($user_id);
		$fs = unserialize($u["join_form_entry"]);

		$t = get_instance(CL_FORM);
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
				$f = get_instance(CL_FORM);
				if (is_array($jf))
				{
					foreach($jf as $joinform => $joinentry)
					{
						if ($ops[$joinform] && $this->can("view", $joinentry))
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
	/** this actually sends the reminder-email 
		
		@attrib name=pwd_remind params=name nologin="1" 

	**/
	function pwd_remind($arr)
	{
		extract($arr);
		$this->read_template("pwd_remind_form.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_pwd_remind", array("after" => $matches[1]))
		));

		return $this->parse();
	}

	/** this actually sends the reminder-email 
		
		@attrib name=submit_pwd_remind params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
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
		$mail = $c->get_simple_config("remind_pwd_mail".aw_global_get("LC"));
		$mail = str_replace("#parool#", $udata["password"],$mail);
		$mail = str_replace("#kasutaja#", $username,$mail);
		$mail = str_replace("#liituja_andmed#", str_replace("\n\n","\n",$this->show_join_data(array("nohtml" => true,"user" => $username,"no_load_op" => 1))),$mail);

		#$mail = str_replace("\r","",$mail);
		$mail = str_replace("\r\n","\n",$mail);

		send_mail($udata["email"],$c->get_simple_config("remind_pwd_mail_subj".aw_global_get("LC")),$mail,"From: ".$this->cfg["mail_from"]);
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
			$f = get_instance(CL_FORM);
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

	/**  
		
		@attrib name=submit_user_info params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_user_info($arr)
	{
		extract($arr);
		$co = get_instance("config");
		$fo = $co->get_simple_config("user_info_form");

		$f = get_instance(CL_FORM);
		$f->process_entry(array("id" => $fo, "entry_id" => $entry_id));

		$this->set_user_config(array("uid" => $u_uid, "key" => "info_entry", "value" => $f->entry_id));

		return $this->mk_my_orb("settings", array("id" => $u_uid));
	}

	/**  
		
		@attrib name=show_user_info params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function show_user_info()
	{
		$co = get_instance("config");
		$fo = $co->get_simple_config("user_info_form");
		if ($fo)
		{
			$eid = $this->get_user_config(array("uid" => aw_global_get("uid"), "key" => "info_entry"));
			if ($eid)
			{
				$f = get_instance(CL_FORM);
				return $f->show(array("id" => $fo, "entry_id" => $eid,"op_id" => $co->get_simple_config("user_info_op")));
			}
		}
	}

	/** Generates an unique hash, which when used in a url can be used to let the used change 
		
		@attrib name=send_hash params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment
		his/her password

	**/
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
			"reforb" => $this->mk_reforb("submit_send_hash",array("section" => aw_global_get("section"))),
		));

		return $this->parse();
	}

	/**  
		
		@attrib name=submit_send_hash params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_send_hash($args = array())
	{
		extract($args);
		extract($_POST);
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

			$this->read_template("hash_send.tpl");
			$this->vars(array(
				"churl" => $this->get_change_pwd_hash_link($uid),
				"email" => $this->cfg["webmaster_mail"],
				"name_wm" => $this->cfg["webmaster_name"],
				"uid" => $row["uid"],
				"host" => $host,
			));
			$msg = $this->parse();
			$from = sprintf("%s <%s>", $this->cfg["webmaster_name"], $this->cfg["webmaster_mail"]);
			send_mail($row["email"], "Paroolivahetus saidil ".aw_global_get("HTTP_HOST"), $msg, "From: $from");
			aw_session_set("status_msg", "Parooli muutmise link saadeti  aadressile <b>$row[email]</b>. Vaata oma postkasti<br />Täname!<br />");
		};
		return $this->mk_my_orb("send_hash",array("section" => $args["section"]));
	}

	/** Allows the user to change his/her password 
		
		@attrib name=pwhash params=name nologin="1" default="0"
		
		@param k required
		@param u required
		
		@returns
		
		
		@comment

	**/
	function password_hash($args = array())
	{	
		extract($args);
		$uid = $u;
		$key = $k;
		if (not(is_valid("uid",$uid)))
		{
			$this->read_adm_template("hash_results.tpl");
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
			$this->vars(array(
				"msg" => t("Sellist kasutajat pole registreeritud"),
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
				"msg" => t("Sellist võtit pole väljastatud"),
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
				"msg" => t("See võti on juba aegunud")." <a href='".$this->mk_my_orb('send_hash')."'>".t("Telli uusi v&otilde;ti")."</a>"
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

	/**  
		
		@attrib name=change_pwd_hash params=name nologin="1" default="0"
		
		@param pass1 required
		@param pass2 required
		@param a required
		@param change optional
		
		@returns
		
		
		@comment

	**/
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

	/** Submits the password 
		
		@attrib name=submit_password_hash params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
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
		}
		aw_global_set("gidlist", $gidlist);
		aw_global_set("gidlist_pri", $gidlist_pri);
		aw_global_set("gidlist_pri_oid", $gidlist_pri_oid);
		aw_global_set("gidlist_oid", $gidlist_oid);
	}

	function request_startup()
	{
		if (($uid = aw_global_get("uid")) != "")
		{
			/*$_uid = $this->db_fetch_field("SELECT uid FROM users WHERE uid = '$uid' AND blocked != 1", "uid");
			if ($_uid != $uid)
			{
				// if no such user exists, log the bastard out
				$this->kill_user();
			}*/

			$this->create_gidlists($uid);
			$gidlist = aw_global_get("gidlist");
			$gidlist_pri = aw_global_get("gidlist_pri");
			if (count($gidlist) < 1)
			{
				$this->kill_user();
			}
			$this->touch($uid);

			// get highest priority group
			$hig = 0;
			$hig_p = -1;
			foreach($gidlist_pri as $_gid => $_pri)
			{
				if ($_pri > $hig_p && $_pri < 100000000)
				{
					$hig_p = $_pri;
					$hig = $_gid;
				}
			}

			if ($hig)
			{
				$_oid = $this->get_oid_for_gid($hig);
				if ($_oid)
				{
					obj_set_opt("no_auto_translation", 1);
					aw_disable_acl();
					$o = obj($_oid);
					aw_restore_acl();
					$ar2 = $o->meta("admin_rootmenu2");
					$gf = $o->meta("grp_frontpage");
					$lang_id = aw_global_get("lang_id");
					if (is_array($ar2) && $ar2[$lang_id])
					{
						aw_ini_set("","admin_rootmenu2",$ar2[$lang_id]);
						aw_ini_set("","ini_rootmenu", $GLOBALS["cfg"]["__default"]["rootmenu"]);
						aw_ini_set("","rootmenu",$ar2[$lang_id]);
					}
					if (is_array($gf) && $gf[$lang_id])
					{
						aw_ini_set("","frontpage",$gf[$lang_id]);
					}
					obj_set_opt("no_auto_translation", 0);
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

	/**  
		
		@attrib name=createpwd params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=submit_createpwd params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/** Encrypts the passwords in the database with md5 
		
		@attrib name=pwconv params=name default="0"
		
		
		@returns
		
		
		@comment
		don't forget to turn on auth.md5_passwords after you do that,
		otherwise it will be impossible to log in.

	**/
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
			$admo = obj($this->get_oid_for_gid($admg));
			$admo->set_prop("can_admin_interface", 1);
			$admo->save();

			$editors = $this->addgroup(0,"Toimetajad", GRP_REGULAR,0,5000,0,$ini_opts["groups.tree_root"]);
			echo "Toimetajad <br>\n";
			flush();
			$osi_vars["groups.editors"] = $this->get_oid_for_gid($editors);

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
			$this->_install_create_g_u_o_rel($this->last_user_oid, $this->get_oid_for_gid($admg));
			echo "administrator <br>\n";
			flush();
			$this->_install_create_g_u_o_rel($this->last_user_oid, $this->get_oid_for_gid($aug));
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


	/** login
		
		@attrib name=login params=name default="0" nologin="1" is_public="1" caption="Logi sisse"
		
		@param uid required
		@param password optional
		@param remote_ip optional
		@param reforb optional
		@param remote_host optional
		@param return optional
		@param hash optional
		
		@returns
		
		
		@comment
			logs the user in, if all arguments are correct and redirects to the correct url

	**/
	function login($arr)
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
			$row = $this->db_fetch_row($q);
			if ($row["hash"] == $arr["hash"])
			{
				// do quick login
				$_SESSION["uid"] = $arr["uid"];
				aw_global_set("uid", $arr["uid"]);
				aw_session_set("uid_oid", $this->get_oid_for_uid($arr["uid"]));
				$this->request_startup();

				// remove hash from usable hashes
				$this->db_query("DELETE FROM user_hashes WHERE hash = '$arr[hash]'");

				// remove stale hash table entries
				$this->db_query("DELETE FROM user_hashes WHERE hash_time < ".(time() - 60*24*3600));
				return;
			}
		}
		return parent::login($arr);
	}

	/** logout
		
		@attrib name=logout params=name default="0" nologin="1" is_public="1" caption="Logi v&amp;auml;lja"
		
		@returns
		
		
		@comment
			logs the current user out

	**/
	function orb_logout($arr = array())
	{
		return parent::orb_logout($arr);
	}

	/** event list
		@attrib name=events nologin="1"

		@param user required
		@param password required
		@param limit optional

	**/
	function events($arr)
	{
		// first, check that user

		$user = $arr["user"];
		$pass = $arr["password"];
		$this->quote($user);
		$this->quote($pass);

		$q = "SELECT count(*) AS cnt FROM users WHERE uid = '$user' AND password = '$pass'";
		$row = $this->db_fetch_row($q);
		if ($row["cnt"] == 0)
		{
			return false;
		};

		// if limit is given, then return the events that are in the range between
		// now and now+limit (seconds)

		// if not give, simply return all upcoming events up to the end of the range
		$limit = (int)$arr["limit"];

		$cal_id = $this->get_user_config(array(
			"uid" => $arr["user"],
			"key" => "user_calendar",
		));

		$res = array();
		if (empty($cal_id))
		{
			return false;

		};
		$pl = get_instance(CL_PLANNER);

		// XXX: I do not have the means to ask for next n event, so I'm working
		// around it by asking events for the current and next week and the 
		// filtering out the upcoming events

		// XXX: _init_event_source needs to be rewritten

		$evts = $pl->_init_event_source(array(
			"id" => $cal_id,
			"type" => "week",
			"flatlist" => 1,
		));
		$evts = $evts + $pl->_init_event_source(array(
			"id" => $cal_id,
			"type" => "week",
			"flatlist" => 1,
			"date" => date("d-m-Y",time() + 86400 * 7),
		));

		$now = time();

		$clinf = aw_ini_get("classes");
		
		foreach($evts as $item)
		{
			$use = false;
			if ($item["start"] >= $now)
			{
				if ($limit == 0)
				{
					$use = true;
				};
				if ($limit > 0 && $item["start"] <= $now + $limit)
				{
					$use = true;
				};
			};
			if (!$use)
			{
				continue;
			};
			$res[] = array(
				// XXX: name should not contain the icon
				"event" => strip_tags($item["name"]),
				"type" => $clinf[$item["class_id"]]["name"],
				"start" => $item["start"],
				"event_url" => $item["link"],
				"icon_url" => $item["event_icon_url"],
			);
		};		
		return $res;
	}
}
?>
