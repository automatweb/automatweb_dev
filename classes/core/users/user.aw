<?php

/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_USER, on_delete_user)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_USER, on_save_user)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_USER, on_delete_alias)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_USER, on_add_alias)

EMIT_MESSAGE(MSG_USER_CREATE);

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_ML_MEMBER, on_save_addr)

*/

/*

@classinfo syslog_type=ST_USERS relationmgr=yes no_status=1

@groupinfo chpwd caption="Parool"
@groupinfo objects caption="Objektid ja &Otilde;igused"
@groupinfo objects_own caption="Enda tehtud" parent=objects
@groupinfo objects_other caption="Teiste tehtud" parent=objects
@groupinfo groups caption=Grupid
@groupinfo jdata caption="Liitumise info"
@groupinfo stat caption=Statistika
@groupinfo aclwizard caption="ACL Maag"

@tableinfo users index=oid master_table=objects master_index=brother_of

@default table=users
@default group=general

@property uid field=uid type=text group=general editonly=1
@caption Kasutajanimi

@property uid_entry store=no type=textbox group=general 
@caption Kasutajanimi

@property logins field=logins type=text store=yes 
@caption Sisselogimisi

@property lastaction field=lastaction type=text
@caption Viimane sisse logimine

@property blocked field=blocked type=checkbox ch_value=1
@caption Blokeeritud

@property real_name type=textbox table=users field=config method=serialize
@caption Nimi

@property email field=email type=textbox
@caption E-mail

@property created field=created type=date table=objects
@caption Loodud

@property createdby field=createdby type=text table=objects
@caption Looja

@property admin_lang store=no type=select
@caption Admin keel

@property base_lang type=select field=meta method=serialize table=objects
@caption Baaskeel

@property target_lang type=select field=meta method=serialize table=objects
@caption Sihtkeel

@property act_from type=date_select table=users field=config method=serialize
@caption Aktiivne alates

@property act_to type=date_select table=users field=config method=serialize
@caption Aktiivne kuni

@property password_hash type=hidden table=users field=config method=serialize
@property password_hash_timestamp type=hidden table=users field=config method=serialize

@default group=chpwd

@property passwd type=password store=no
@caption Salasõna

@property passwd_again type=password store=no
@caption Salasõna uuesti

@property password type=hidden table=users field=password store=no


@property gen_pwd store=no type=text 
@caption Genereeri parool

@property genpwd store=no type=textbox 
@caption Genereeritud parool

@property resend_welcome store=no type=checkbox ch_value=1
@caption Saada tervitusmeil

@default group=objects

@property objects_own type=text  store=no no_caption=1 group=objects_own
@caption Objektid

@property objects_other type=text  store=no no_caption=1 group=objects_other
@caption Objektid

@property obj_acl type=callback callback=get_acls store=no group=objects_own

@property obj_acl_other type=callback callback=get_acls store=no group=objects_other

@default group=groups

@property groups type=text  store=no no_caption=1

@property home_folder type=hidden field=home_folder table=users

@default group=jdata

@property jdata type=callback callback=callback_jdata  store=no no_caption=1
@caption Liitumise andmed

@default group=stat

@property stat type=text store=no no_caption=1
@caption Statistika

@default group=aclwizard

@property aclwizard_q type=text store=no
@caption Millised on kasutaja

@property aclwiz type=hidden table=objects field=meta method=serialize

@property aclwizard_a type=text store=no
@caption 

@property ui_language type=select table=objects field=meta method=serialize
@caption Liidese keel

@groupinfo userdef caption="User-defined"

@property userch1 type=checkbox ch_value=1 table=objects field=meta method=serialize group=userdef user=1
@caption User-defined checkbox 1

@property userch2 type=checkbox ch_value=1 table=objects field=meta method=serialize group=userdef user=1
@caption User-defined checkbox 2

@property userch3 type=checkbox ch_value=1 table=objects field=meta method=serialize group=userdef user=1
@caption User-defined checkbox 3

@property userch4 type=checkbox ch_value=1 table=objects field=meta method=serialize group=userdef user=1
@caption User-defined checkbox 4

@property userch5 type=checkbox ch_value=1 table=objects field=meta method=serialize group=userdef user=1
@caption User-defined checkbox 5

@property join_form_entry type=hidden table=users field=join_form_entry 

@reltype GRP value=1 clid=CL_GROUP
@caption Grupp

@reltype PERSON value=2 clid=CL_CRM_PERSON
@caption isik

@reltype EMAIL value=6 clid=CL_ML_MEMBER
@caption Email

/@reltype USER_DATA value=3
/@caption Andmed

@reltype FG_PROFILE value=7 clid=CL_FORM_ENTRY
@caption FG profiil

@reltype ACCESS_FROM_IP value=8 clid=CL_IPADDRESS
@caption ligip&auml;&auml;su aadress

@reltype JOIN_SITE value=9 clid=CL_JOIN_SITE
@caption liitumise andmed

*/

class user extends class_base
{
	function user()
	{
		$this->init(array(
			'tpldir' => 'core/users/user',
			'clid' => CL_USER
		));
		$this->users = get_instance("users");
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "name":
				return PROP_IGNORE;

			case "comment":
				return PROP_IGNORE;
			
			case "lastaction";
				$prop["value"] = $this->db_fetch_field("SELECT lastaction FROM users WHERE uid = '".$arr["obj_inst"]->prop("uid")."'", "lastaction");
				$prop['value'] = $this->time2date($prop['value'],2);
				break;

			case "uid_entry": 
				if (is_oid($arr["obj_inst"]->id()))
				{
					return PROP_IGNORE;
				}
				break;

/*			case "uid": 
				if (!is_oid($arr["obj_inst"]->id()))
				{
					return PROP_IGNORE;
				}
				break;*/

			case "created":
				$prop['value'] = $this->time2date($prop['value'],2);
				break;

			case "base_lang":
			case "target_lang":
				$l = get_instance("languages");
				$prop["options"] = $l->get_list();
				break;

			case "admin_lang":
				$l = get_instance("languages");
				$prop['options'] = $l->get_list();
				$prop['value'] = aw_global_get("admin_lang");
				break;

			case "groups":
				$prop['value'] = $this->_get_group_membership($arr["obj_inst"]->prop("uid"), $arr["obj_inst"]->id());
				break;

			case "objects_own":
				$prop["value"] = $this->_get_objects($arr["obj_inst"]->prop("uid"), true);
				break;

			case "objects_other":
				$prop["value"] = $this->_get_objects($arr["obj_inst"]->prop("uid"), false);
				break;

			case "stat":
				$prop["value"] = $this->_get_stat($arr["obj_inst"]->prop("uid"));
				break;

			case "gen_pwd":
				$prop["value"] = 
					"
						<script language=\"javascript\">
						function gp()
						{
							pwd = new String(\"\");
							for (i = 0; i < 8; i++)
							{
								rv = Math.random()*(123-97);
								rn = parseInt(rv);
								rt = rn+97;
								pwd = pwd + String.fromCharCode(rt);
							}
							document.changeform.passwd.value = pwd;
							document.changeform.passwd_again.value = pwd;
							document.changeform.genpwd.value = pwd;
						}
						</script>					
					".
					html::href(array(
					"url" => "#",
					"onClick" => "gp();",
					"caption" => t("Genereeri parool")
				));
				break;

			case "aclwizard_q":
				$mt = $arr["obj_inst"]->meta("aclwiz");
				$prop["value"] = "".html::textbox(array(
					"name" => "aclwizard[user]",
					"value" => $mt["user"],
					"size" => "15"
				))." &otilde;igused objektile ".html::textbox(array(
					"name" => "aclwizard[object]",
					"value" => $mt["object"],
					"size" => 8
				))."?";
				break;

			case "aclwizard_a":
				$mt = $arr["obj_inst"]->meta("aclwiz");
				if ($mt["user"] != "" && is_oid($mt["object"]))
				{
					$prop["value"] = $this->aclwizard_ponder(array(
						"user" => $mt["user"],
						"oid" => $mt["object"],
						"type" => $mt["type"]
					));
				}
				break;

			case "ui_language":
				$i = get_instance("core/trans/pot_scanner");
				$prop["options"] = array("" => "") + $i->get_langs();
				break;
		}
		return PROP_OK;
	}	

	function set_property(&$arr)
	{
		$prop =& $arr["prop"];
		load_vcl("date_edit");
		switch($prop['name'])
		{
			case "uid_entry":
				if (!is_oid($arr["obj_inst"]->id()))
				{
					if (strtolower($this->db_fetch_field("SELECT uid FROM users WHERE uid = '".$prop["value"]."'", "uid")) == strtolower($prop["value"]))
					{
						$prop["error"] = t("Selline kasutaja on juba olemas!");
						return PROP_FATAL_ERROR;
					}
					if (!is_valid("uid", $prop["value"]))
					{
						$prop["error"] = t("Selline kasutajanimi pole lubatud!");
						return PROP_FATAL_ERROR;
					}
				}
				break;

			case "admin_lang":
				$t = get_instance("languages");

				$admin_lang = $prop['value'];
				$admin_lang_lc = $t->get_langid($admin_lang);
				setcookie("admin_lang",$admin_lang,time()+24*3600*1000,"/");
				setcookie("admin_lang_lc",$admin_lang_lc,time()+24*3600*1000,"/");
				break;

			case "passwd_again":
				if ($prop['value'] != "")
				{
					if ($prop['value'] != $arr['request']['passwd'])
					{
						$prop["error"] = t("Paroolid pole samad!");
						return PROP_FATAL_ERROR;
					}
					else
					if (!is_valid("password", $prop['value']))
					{
						$prop["error"] = t("Parool sisaldab lubamatuid t&auml;hti v&otilde;i on liiga l&uuml;hike!");
						return PROP_FATAL_ERROR;
					}
					else
					{
						// change pwd
						$this->users->save(array(
							"uid" => $arr["obj_inst"]->prop("uid"),
							"password" => $prop['value']
						));
					}
				}
				break;
			
			case "resend_welcome":
				if ($prop['value'] == 1)
				{
					$this->users->send_welcome_mail(array(
						"uid" => $arr["obj_inst"]->prop("uid"),
						"pass" => $arr['request']['passwd']
					));
				}
				break;

			case "groups":
				$prop['value'] = $this->_set_group_membership($arr["obj_inst"]->prop("uid"), $arr["request"], $arr["obj_inst"]->id());
				break;

			case "obj_acl":
				// read all acls from request and set them
				$ea = $arr["request"]["edit_acl"];
				if ($ea)
				{
					$a = $this->acl_list_acls();
					$acl = array();
					foreach($a as $a_bp => $a_name)
					{
						$acl[$a_name] = $arr["request"]["acl_".$a_bp];
					}
					$this->save_acl($ea, $gid, $acl);
				}
				break;

			case "aclwiz":
				if ($arr["request"]["aclwizard"]["user"] != "")
				{
					$ol = new object_list(array(
						"class_id" => CL_USER,
						"name" => $arr["request"]["aclwizard"]["user"],
						"site_id" => array(),
						"lang_id" => array()
					));
					if ($ol->count() < 1)
					{
						$prop["error"] = t("Sellist kasutajat pole!");
						return PROP_FATAL_ERROR;
					}
				}
				$prop["value"] = $arr["request"]["aclwizard"];
				break;

			case "ui_language":
				if ($arr["obj_inst"]->prop("uid") == aw_global_get("uid"))
				{
					if ($prop["value"] == "")
					{
						aw_session_del("user_adm_ui_lc");
					}
					else
					{
						aw_session_set("user_adm_ui_lc", $prop["value"]);
					}
				}
				break;

			case "jdata":
				$o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_JOIN_SITE");
				$tmp = $arr["request"];
				$tmp["id"] = $o->id();
				$ji = get_instance(CL_JOIN_SITE);
				$ji->submit_update_form($tmp, array(
					"uid" => $arr["obj_inst"]->prop("uid")
				));
				break;
		}
		return PROP_OK;
	}

	function _get_group_membership($uid, $id)
	{
		$gl = $this->users->get_group_list(array("type" => array(GRP_REGULAR, GRP_DYNAMIC)));

		// get all groups this user is member of
		$groups = $this->users->getgroupsforuser($uid);
	
		$t =& $this->_start_gm_table();		
		foreach($gl as $gid => $gd)
		{
			if (is_oid($gd["oid"]) && $this->can("view", $gd["oid"]))
			{
				$tmp = obj($gd["oid"]);
				$gd["name"] = $tmp->name();
			}
			if ($gd["type"] == GRP_DYNAMIC)
			{
				$gd["type"] = t("D&uuml;naamiline");
				$gd["is_member"] = (isset($groups[$gid]) ? LC_YES : LC_NO);
			}
			else
			{
				$gd["type"] = t("Tavaline");
				$gd["is_member"] = html::checkbox(array(
					"name" => "member[$gid]",
					"value" => 1,
					"checked" => isset($groups[$gid])
				));
			}

			$t->define_data($gd);
		}

		$t->set_default_sortby("name");
		$t->sort_by();
		return $t->draw();
	}

	function _set_group_membership($uid, $form_data, $id)
	{
		$member = $form_data["member"];

		// now update group membership.
		// get the groups that the user is member of
		$groups = $this->users->getgroupsforuser($uid);

		// get a list of all groups, so we can throw out the dynamic groups
		$gl = $this->users->get_group_list(array("type" => array(GRP_REGULAR, GRP_DYNAMIC)));


		// now, go over both lists and get rid of the dyn groups
		$_member = array();
		$_tm = new aw_array($member);
		foreach($_tm->get() as $gid => $is)
		{
			if ($gl[$gid]["type"] != GRP_DYNAMIC)
			{
				$_member[$gid] = $is;
			}
		}
		$member = $_member;

		$_groups = array();
		foreach($groups as $gid => $is)
		{
			if ($gl[$gid]["type"] != GRP_DYNAMIC)
			{
				$_groups[$gid] = $is;
			}
		}
		$groups = $_groups;

		// now, remove user from all removed groups
		foreach($groups as $gid => $is)
		{
			if ($member[$gid] != 1 && $is && isset($gl[$gid]))
			{
				$this->users->remove_users_from_group_rec($gid, array($uid), false, false);

				$group = obj($this->users->get_oid_for_gid($gid));
				$user = obj($this->users->get_oid_for_uid($uid));

				// do the group add trick
				// now, delete the user from the group
				if ($group->is_connected_to(array("to" => $user->id())))
				{
					$group->disconnect(array(
						"from" => $user->id()
					));
				}

				// delete user bros
				$ol = new object_list(array(
					"parent" => $group->id(),
					"brother_of" => $user->id()
				));
				$ol->delete();

				// get all groups below the removed group
				$ot = new object_tree(array(
					"parent" => $group->id(),
					"class_id" => CL_GROUP
				));
				$ol = $ot->to_list();
				for($item = $ol->begin(); !$ol->end(); $item = $ol->next())
				{
					// remove all brothers from those groups
					$user_brothers = new object_list(array(
						"parent" => $item->id(),
						"brother_of" => $user->id()
					));
					$user_brothers->delete();

					// remove all aliases from those groups to this user
					if ($item->is_connected_to(array("to" => $user->id())))
					{
						$item->disconnect(array(
							"from" => $user->id()
						));
					}

					// also remove all aliases from user to the group
					if (count($user->connections_from(array("to" => $item->id()))) > 0)
					{
						$user->disconnect(array(
							"from" => $item->id()
						));
					}
				}
			}
		}

		// now, add to all groups
		foreach($member as $gid => $is)
		{
			if ($is && !$groups[$gid])
			{
				$this->users->add_users_to_group_rec($gid, array($uid), true, true, false);

				$group = obj($this->users->get_oid_for_gid($gid));
				$_o = $this->users->get_oid_for_uid($uid);
				$user = obj($_o);

				// get groups
				$grps = $group->path();
				foreach($grps as $p_o)
				{
					if ($p_o->class_id() == CL_GROUP)
					{
						$user->connect(array(
							"to" => $p_o->id(),
							"reltype" => "RELTYPE_GRP",
						));

						// add reverse alias to group
						$p_o->connect(array(
							"to" => $user->id(),
							"reltype" => "RELTYPE_MEMBER" // from group
						));

						$user->create_brother($p_o->id());
					}
				}
			}
		}

		$c = get_instance("cache");
		$c->file_clear_pt("acl");
	}

	function &_start_gm_table()
	{
		load_vcl("table");
		$t = new aw_table(array("layout" => "generic","prefix" => "uglist"));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "is_member",
			"caption" => t("Liige?"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "priority",
			"caption" => t("Prioriteet"),
			"sortable" => 1,
			"numeric" => 1,
			"align" => "center"
		));
		
		$t->define_field(array(
			"name" => "gcount",
			"caption" => t("Mitu liiget"),
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"sortable" => 1,
			"align" => "center"
		));

		$df = aw_ini_get("config.dateformats");
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"sortable" => 1,
			"type" => "time",
			"format" => $df[2]
		));

		$t->define_field(array(
			"name" => "type",
			"caption" => t("T&uuml;&uuml;p"),
			"sortable" => 1,
			"align" => "center"
		));

		return $t; 
	}

	////
	// !this will get automatically called if an object of this type is cut-pasted
	// params:
	//	oid
	//	new_parent
	// damn - must figure out a way to do this via storage messages... must remember what is changed for object or somesuch..
	function cut_hook($arr)
	{
		extract($arr);

		// check if it is original or brother
		// if original, do nothing
		// if brother, find new group and change group membership

		$obj = obj($oid);
		if ($new_parent == $obj->parent())
		{
			return;
		}

		if ($obj->is_brother())
		{
			// old parent group
			$o_gid = $this->users->get_gid_for_oid($obj->parent());

			// new gid
			$n_gid = $this->users->get_gid_for_oid($new_parent);

			// get the user
			$uid = $this->users->get_uid_for_oid($obj->brother_of());

			if ($o_gid)
			{
				$this->users->remove_users_from_group_rec($o_gid, array($uid), false, false);

				// get the parent obj for the user's brother
				// and remove the user from that group
				$real_user = $obj->get_original();
				$grp_o = obj($obj->parent());


				// sync manually here.
				// remove alias from user to group
				if ($real_user->is_connected_to(array("to" => $grp_o->id())))
				{
					$real_user->disconnect(array(
						"from" => $grp_o->id()
					));
				}

				// remove alias from group to user
				if ($grp_o->is_connected_to(array("to" => $real_user->id())))
				{
					$grp_o->disconnect(array(
						"from" => $real_user->id()
					));
				}

				// go over all the groups below this one and remove all aliases to this user
				// and also all user brothers
				$ot = new object_tree(array(
					"parent" => $grp_o->id(),
					"class_id" => CL_GROUP
				));
				
				$ol = $ot->to_list();
				for($grp_o = $ol->begin(); !$ol->end(); $grp_o = $ol->next())
				{
					// get all connections from the group to the user object
					foreach($grp_o->connections_from(array("to" => $real_user->id())) as $c)
					{
						$c->delete();
					}

					// disconnect user from group as well
					if ($real_user->is_connected_to(array("to" => $grp_o->id())))
					{
						$real_user->disconnect(array(
							"from" => $grp_o->id()
						));
					}
	
					// get all objects below that point to the current user
					$inside_ol = new object_list(array(
						"parent" => $grp_o->id(),
						"class_id" => CL_USER,
						"brother_of" => $real_user->id()
					));
					$inside_ol->delete();
				}
			}

			if ($n_gid)
			{
				$this->users->add_users_to_group_rec($n_gid, array($uid), false, true, false);

				// get groups
				$group = obj($new_parent);
				$user = $obj->get_original();

				$grps = $group->path();
				foreach($grps as $p_o)
				{
					if ($p_o->class_id() == CL_GROUP)
					{
						$user->connect(array(
							"to" => $p_o->id(),
							"reltype" => "RELTYPE_GRP",
						));

						// add reverse alias to group
						$p_o->connect(array(
							"to" => $user->id(),
							"reltype" => "RELTYPE_MEMBER" // from group
						));

						if ($p_o->id() != $group->id())
						{
							$user->create_brother($p_o->id());
						}
					}
				}
			}
		}		
	}

	// must not be deleting these, most important it is!
	function _serialize($arr)
	{
		extract($arr);
		$ob = obj($oid);
		if (is_object($ob))
		{
			return aw_serialize($ob->fetch(), SERIALIZE_NATIVE);
		}
		return false;
	}

	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		
		$old_oid = $row["oid"];
		$u = obj($old_oid);

		// get the parent group
		$n = obj($parent);
		$path = $n->path();
		foreach(array_reverse($path) as $p_i)
		{
			if ($p_i->class_id() == CL_GROUP)
			{
				$g = get_instance(CL_GROUP);
				$g->add_user_to_group($u, $p_i);
				return 0;
			}
		}
		return -1;
	}

	function _get_objects($uid, $own)
	{
		// get all groups this user is a member of
		$grps = $this->users->getgroupsforuser($uid);
		// now, get all the folders that have access set for these groups
		$dat = $this->acl_get_acls_for_groups(array("grps" => array_keys($grps)));

		$g = get_instance(CL_GROUP);
	
		$t =& $g->_init_obj_table(array(
			"exclude" => array("grp_name")
		));
		foreach($dat as $row)
		{
			if ($own && $row["createdby"] != $uid)
			{
				continue;
			}
			if (!$own && $row["createdby"] == $uid)
			{
				continue;
			}
			if (!$this->can("view", $row["oid"]))
			{
				continue;
			}
			if (!is_oid($row["oid"]) || $row['status'] == STAT_DELETED)
			{
				continue;
			};
			$o = obj($row["oid"]);
			$row["acl"] = html::href(array(
				"caption" => t("Muuda"),
				"url" => aw_url_change_var("edit_acl", $row["oid"])
			));
			$t->define_data($row);
		}
		$t->set_default_sortby("obj_name");
		$t->sort_by();
		return $t->draw(array(
			"has_pages" => true,
			"records_per_page" => 100,
			"pageselector" => "text"
		));
	}

	function callback_on_load($arr)
	{
		$o = obj($arr["request"]["id"]);
		if ($o->id() != $o->brother_of())
		{
			header("Location: ".aw_url_change_var("id", $o->brother_of()));
			die();
		}
	}
	

	function callback_mod_retval($arr)
	{
		if ($arr["request"]["edit_acl"])
		{
			$arr["args"]["edit_acl"] = $arr["request"]["edit_acl"];
		}
	}

	function get_acls($arr)
	{
		$acls = array();
		$ea = $arr["request"]["edit_acl"];
		if ($ea)
		{
			$o = obj($ea);
			$acls["acl_desc"] = array(
				'name' => "acl_desc",
				'type' => 'text',
				'store' => 'no',
				'group' => 'objects',
				'value' => sprintf(t('Muuda objekti %s  &otilde;igusi'), $o->name())
			);
			$acls["edit_acl"] = array(
				'name' => "edit_acl",
				'type' => 'hidden',
				'store' => 'no',
				'value' => $ea
			);

			// get active acl 
			$act_acl = $this->get_acl_for_oid_gid($ea, $this->users->get_gid_for_oid($arr["request"]["id"]));

			$a = $this->acl_list_acls();
			foreach($a as $a_bp => $a_name)
			{
				$rt = "acl_".$a_bp;
				$acls[$rt] = array(
					'name' => $rt,
					'caption' => $a_name,
					'type' => 'checkbox',
					'ch_value' => 1,
					'store' => 'no',
					'group' => 'objects',
					'value' => $act_acl[$a_name]
				);
			}
		}
		return $acls;
	}

	function on_delete_user_bro($arr)
	{
		extract($arr);

		// check if we are deleting the real thing
		$o = obj($oid);
		// get the parent obj for the user's brother
		// and remove the user from that group
		$o = obj($oid);
		$real_user = $o->get_original();

		if (!is_oid($o->parent()))
		{
			return;
		}
		$grp_o = obj($o->parent());

		$gid = $this->users->get_gid_for_oid($o->parent());
		$uid = $this->users->get_uid_for_oid($o->brother_of());
		if ($gid)
		{
			$this->users->remove_users_from_group_rec(
				$gid, 
				array($uid),
				false,
				false
			);

			// sync manually here.
			// remove alias from user to group
			if ($real_user->is_connected_to(array("to" => $grp_o->id())))
			{
				$real_user->disconnect(array(
					"from" => $grp_o->id()
				));
			}

			// remove alias from group to user
			if ($grp_o->is_connected_to(array("to" => $real_user->id())))
			{
				$grp_o->disconnect(array(
					"from" => $real_user->id()
				));
			}

			// go over all the groups below this one and remove all aliases to this user
			// and also all user brothers
			$ot = new object_tree(array(
				"parent" => $o->parent(),
				"class_id" => CL_GROUP
			));
				
			$ol = $ot->to_list();
			for($grp_o = $ol->begin(); !$ol->end(); $grp_o = $ol->next())
			{
				// get all connections from the group to the user object
				foreach($grp_o->connections_from(array("to" => $real_user->id())) as $c)
				{
					$c->delete();
				}

				// disconnect user from group as well
				if ($real_user->is_connected_to(array("to" => $grp_o->id())))
				{
					$real_user->disconnect(array(
						"from" => $grp_o->id()
					));
				}
	
				// get all objects below that point to the current user
				$inside_ol = new object_list(array(
					"parent" => $grp_o->id(),
					"class_id" => CL_USER,
					"brother_of" => $real_user->id()
				));
				$inside_ol->delete();
			}
		}
		$c = get_instance("cache");
		$c->file_clear_pt("acl");
	}

	function on_delete_alias($arr)
	{
		// now, if the alias deleted was a group alias, then 
		// remove the user from that goup and do all the other movements
		if ($arr["connection"]->prop("reltype") == RELTYPE_GRP)
		{
			$user = $arr["connection"]->from();
			$group = $arr["connection"]->to();

			$uid = $this->users->get_uid_for_oid($user->id());
			$gid = $this->users->get_gid_for_oid($group->id());

			$this->users->remove_users_from_group_rec(
				$gid,
				array($uid),
				false,	// checkdyn
				false	// normalize
			);

			// now, delete the user from the group
			if ($group->is_connected_to(array("to" => $user->id())))
			{
				$group->disconnect(array(
					"from" => $user->id()
				));
			}

			// delete user bros
			$ol = new object_list(array(
				"parent" => $group->id(),
				"brother_of" => $user->id()
			));
			$ol->delete();

			// get all groups below the removed group
			$ot = new object_tree(array(
				"parent" => $group->id(),
				"class_id" => CL_GROUP
			));
			$ol = $ot->to_list();
			for($item = $ol->begin(); !$ol->end(); $item = $ol->next())
			{
				// remove all brothers from those groups
				$user_brothers = new object_list(array(
					"parent" => $item->id(),
					"brother_of" => $user->id()
				));
				$user_brothers->delete();

				// remove all aliases from those groups to this user
				if ($item->is_connected_to(array("to" => $user->id())))
				{
					$item->disconnect(array(
						"from" => $user->id()
					));
				}

				// also remove all aliases from user to the group
				if (count($user->connections_from(array("to" => $item->id()))) > 0)
				{
					$user->disconnect(array(
						"from" => $item->id()
					));
				}
			}
		}
		$c = get_instance("cache");
		$c->file_clear_pt("acl");
	}

	function _get_stat($uid)
	{
		$t =& $this->_init_stat_table();
		$ts = aw_ini_get('syslog.types');
		$as = aw_ini_get('syslog.actions');
		$q = "SELECT * FROM syslog WHERE uid = '$uid' ORDER BY tm DESC LIMIT 4000";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$row['type'] = $ts[$row['type']]['name'];
			$row['act_id'] = $as[$row['act_id']]['name'];
			list($row['ip'],) = inet::gethostbyaddr($row['ip']);
			$t->define_data($row);
		}
		$t->set_default_sortby('tm');
		$t->set_default_sorder('DESC');
		$t->sort_by();
		return $t->draw(array(
			"has_pages" => true,
			"records_per_page" => 200,
			"pageselector" => "text"
		));
	}

	function _init_stat_table()
	{
		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'user',
			'layout' => 'generic'
		));

		$df = aw_ini_get('config.dateformats');
		$t->define_field(array(
			'name' => 'rec',
			'caption' => t('Nr'),
		));
		$t->define_field(array(
			'name' => 'tm',
			'caption' => t('Millal'),
			'sortable' => 1,
			'numeric' => 1,
			'type' => 'time',
			'format' => $df[2],
			'nowrap' => 1
		));
		$t->define_field(array(
			'name' => 'uid',
			'caption' => t('Kes'),
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'ip',
			'caption' => t('IP'),
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'type',
			'caption' => t('T&uuml;&uuml;p'),
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'act_id',
			'caption' => t('Tegevus'),
			'sortable' => 1,
		));
		if (aw_ini_get("syslog.has_site_id"))
		{
			$t->define_field(array(
				'name' => 'site_id',
				'caption' => t('Saidi ID'),
				'sortable' => 1,
			));
		}
		$t->define_field(array(
			'name' => 'oid',
			'caption' => t('OID'),
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'action',
			'caption' => t('Mida'),
			'sortable' => 1,
		));
		return $t;
	}

	function on_add_alias($arr)
	{
		if ($arr["connection"]->prop("reltype") == RELTYPE_GRP)
		{
			// it was a group alias, add the suer to the group and all below it
			$user = $arr["connection"]->from();
			$group = $arr["connection"]->to();

			$uid = $this->users->get_uid_for_oid($user->id());
			$gid = $this->users->get_gid_for_oid($group->id());
		
			$this->users->add_users_to_group_rec(
				$gid,
				array($uid),
				true,
				true,
				false
			);

			// get groups
			$grps = $group->path();
			foreach($grps as $p_o)
			{
				if ($p_o->class_id() == CL_GROUP)
				{
					$user->connect(array(
						"to" => $p_o->id(),
						"reltype" => "RELTYPE_GRP",
					));

					// add reverse alias to group
					$p_o->connect(array(
						"to" => $user->id(),
						"reltype" => "RELTYPE_MEMBER" // from group
					));


					$user->create_brother($p_o->id());

				}
			}
			$c = get_instance("cache");
			$c->file_clear_pt("acl");
		}
		else
		if ($arr["connection"]->prop("reltype") == 7 )// FG_PROFILE
		{
			// set join form entry
			$u = $arr["connection"]->from();
			$jfe = safe_array(aw_unserialize($u->prop("join_form_entry")));
			$f = get_instance(CL_FORM);
			$eid = $arr["connection"]->prop("to");
			$fid = $f->get_form_for_entry($eid);

			$jfe[$fid] = $eid;
			$u->set_prop("join_form_entry", aw_serialize($jfe, SERIALIZE_NATIVE));
			$u->save();
		}
	}

	function callback_pre_save($arr)
	{
		if ($arr["new"])
		{
			$arr["obj_inst"]->set_prop("uid", $arr["request"]["uid_entry"]);
		}

		$arr["obj_inst"]->set_name($arr["obj_inst"]->prop("uid"));
	}

	function callback_post_save($arr)
	{
		$go_to = false;
		if ($arr["new"])
		{
			$this->users->add(array(
				"uid" => $arr["obj_inst"]->prop("uid"),
				"password" => generate_password(),
				"email" => $arr["request"]["email"],
				"join_grp" => "",
				"join_form_entry" => "",
				"user_oid" => $arr["obj_inst"]->id(),
				"no_add_user" => true
			));
			$arr["obj_inst"]->set_prop("home_folder", $this->users->hfid);
			$arr["obj_inst"]->save();

			// add user to all users grp if we are not under that
			$aug = aw_ini_get("groups.all_users_grp");
			$aug_oid = $this->users->get_oid_for_gid($aug);
			if (is_oid($aug_oid) && $aug_oid != $arr["obj_inst"]->parent())
			{
				$aug_o = obj($aug_oid);
				$arr["obj_inst"]->connect(array(
					"to" => $aug_o->id(),
					"reltype" => "RELTYPE_GRP",
				));

				// add reverse alias to group
				$aug_o->connect(array(
					"to" => $arr["obj_inst"]->id(),
					"reltype" => "RELTYPE_MEMBER" // from group
				));

				//$arr["obj_inst"]->create_brother($aug_o->id());
			}
				
			post_message_with_param(
				MSG_USER_CREATE,
				$this->clid,
				array(
					"user_oid" => $arr["obj_inst"]->id(),
				)
			);

			// now, we also must check if the user was added under a group
			$parent = obj($arr["obj_inst"]->parent());
			if ($parent->class_id() == CL_GROUP)
			{
				// we have to move the object to a new loacation
				$rm = aw_ini_get("users.root_folder");
				if (!$rm)
				{
					$rm = aw_ini_get("users.rootmenu");
				}
				$arr["obj_inst"]->set_parent($rm);
				$arr["obj_inst"]->save();

				// and do the add to group thing
				$uid = $arr["obj_inst"]->prop("uid");
				$gid = $this->users->get_gid_for_oid($parent->id());

				$user = $arr["obj_inst"];

				$user->connect(array(
					"to" => $parent->id(),
					"reltype" => "RELTYPE_GRP",
				));

				// add reverse alias to group
				$parent->connect(array(
					"to" => $user->id(),
					"reltype" => "RELTYPE_MEMBER" // from group
				));

				$this->users->add_users_to_group_rec(
					$gid,
					array($uid),
					true,
					true,
					false
				);

				// get groups
				$grps = $parent->path();
				foreach($grps as $p_o)
				{
					if ($p_o->class_id() == CL_GROUP)
					{
						$user->connect(array(
							"to" => $p_o->id(),
							"reltype" => "RELTYPE_GRP",
						));

						// add reverse alias to group
						$p_o->connect(array(
							"to" => $user->id(),
							"reltype" => "RELTYPE_MEMBER", // from group
						));

						$last_bro = $user->create_brother($p_o->id());
						if ($p_o->id() == $parent->id())
						{
							$go_to = $last_bro;
						}
					}
				}
			}
		}

		// now, find the correct brother
		if ($go_to)
		{
			header("Location: ".$this->mk_my_orb("change", array("id" => $go_to), "user"));
			die();
		}
	}

	function on_save_user($arr)
	{
		$o = obj($arr["oid"]);

		// create email object
		$umail = $o->prop("email");
		$uname = $o->prop("real_name");

		obj_set_opt("no_cache", 1);
		if($mail = $o->get_first_obj_by_reltype("RELTYPE_EMAIL"))
		{
			if ($mail->class_id() != CL_ML_MEMBER)
			{
				$o->disconnect(array("from" => $mail->id(), "type" => "RELTYPE_EMAIL"));
				$this->on_save_user($arr);
				return;
			}
			obj_set_opt("no_cache", 0);
			$mail->set_prop("mail", $umail);
			$mail->set_prop("name", $uname);
			$mail->set_name($uname." &lt;".$umail."&gt;");
			$mail->save();
		}
		else
		{
			obj_set_opt("no_cache", 0);
			$mail = new object();
			$mail->set_class_id(CL_ML_MEMBER);
			$mail->set_parent($o->id());
			$mail->set_prop("mail", $umail);
			$mail->set_prop("name", $uname);
			$mail->set_name($uname." &lt;".$umail."&gt;");
			$mail->save();
			$o->connect(array(
				"to" => $mail->id(),
				"reltype" => "RELTYPE_EMAIL",
			));
		}
	}

	/** returns the user object for the given uid

		@attrib api=1
	**/
	function get_obj_for_uid($uid)
	{
		$oid = $this->users->get_oid_for_uid($uid);
		if (is_oid($oid) && $this->can("view", $oid))
		{
			return obj($oid);
		}
		return NULL;
	}

	function get_current_user()
	{
		return $this->users->get_oid_for_uid(aw_global_get("uid"));
	}

	/** returns the oid of the CL_CRM_PERSON object that's attached to the current user
	**/
	function get_current_person()
	{
		if (aw_global_get("uid") == "")
		{
			return false;
		}

		$oid = $this->users->get_oid_for_uid(aw_global_get("uid"));
		if (!$oid)
		{
			return false;
		}

		$u = obj($oid);
		return $this->get_person_for_user($u);
	}

	function get_person_for_user($u)
	{
		obj_set_opt("no_cache", 1);
		$person_c = $u->connections_from(array(
			"type" => "RELTYPE_PERSON",
		));
		$person_c = reset($person_c);
		obj_set_opt("no_cache", 0);

		if (!$person_c)
		{
			// create new person next to user
			$p = obj();
			$p->set_class_id(CL_CRM_PERSON);
			$p->set_parent($u->parent());

			$rn = $u->prop("real_name");

			$uid = $u->prop("uid");

			$p_n = ($rn != "" ? $rn : $uid);
			$p->set_name($p_n);

			if ($rn != "")
			{
				list($fn, $ln) = explode(" ", $rn);
			}			
			else
			{
				list($fn, $ln) = explode(".", $uid);
			}

			$p->set_prop("firstname", $fn);
			$p->set_prop("lastname", $ln);

			aw_disable_acl();
			$p->save();
			aw_restore_acl();
			// now, connect user to person
			$u->connect(array(
				"to" => $p->id(),
				"reltype" => 2
			));
			return $p->id();
		}
		else
		{
			return $person_c->prop("to");
		}
	}

	function get_company_for_person($person)
	{
		obj_set_opt("no_cache", 1);
		$p_o = obj($person);
		$org_c = reset($p_o->connections_from(array(
			"type" => "RELTYPE_WORK",
		)));
		obj_set_opt("no_cache", 0);
		if (!$org_c)
		{
			$uo = obj(aw_global_get("uid_oid"));
			// create new person next to user
			$p = obj();
			$p->set_class_id(CL_CRM_COMPANY);
			$p->set_parent($p_o->parent());
			$p->set_name("CO ".$uo->prop("real_name")." ".aw_global_get("uid"));
			aw_disable_acl();
			$p->save();
			aw_restore_acl();
			// now, connect user to person
			$p_o->connect(array(
				"to" => $p->id(),
				"reltype" => "RELTYPE_WORK" // from crm_person
			));
			return $p->id();
		}

		return $org_c->prop("to");
	}

	/** returns the CL_CRM_COMPANY that is connected to the current logged in user
	**/ 
	function get_current_company()
	{
		$person = $this->get_current_person();
		if ($person)
		{
			return $this->get_company_for_person($person);
		}
		return false;
	}

	/** creates a new user object and returns the object

		@param uid required
		@param email optional
		@param password optional
		@param real_name optional
	**/
	function add_user($arr)
	{
		extract($arr);
		error::raise_if(empty($uid), array(
			"id" => ERR_NO_UID,
			"msg" => sprintf(t("users::add_user(%s): no uid specified"), $arr)
		));

		if (empty($password))
		{
			$password = generate_password();
		}

		$o = obj();
		$o->set_name($uid);
		$o->set_class_id(CL_USER);
		$pt = aw_ini_get("users.root_folder");
		if ($pt < 2)
		{
			$pt = aw_ini_get("users.rootmenu");
		}
		$o->set_parent($pt);
		$o->set_prop("uid", $uid);
		$o->set_prop("password", $password);
		$o->set_prop("email", $email);
		$o->set_prop("real_name", $arr["real_name"]);
		$o->save();

		$this->users->add(array(
			"uid" => $uid,
			"password" => $password,
			"email" => $o->prop("email"),
			"join_grp" => $arr["join_grp"],
			"join_form_entry" => $arr["join_form_entry"],
			"user_oid" => $o->id(),
			"no_add_user" => true,
			"all_users_grp" => ($all_users_grp ? $all_users_grp : aw_ini_get("groups.all_users_grp")),
			"use_md5_passwords" => ($use_md5_passwords ? $use_md5_passwords : aw_ini_get("auth.md5_passwords")),
			"obj_parent" => $obj_parent
		));

		// we need to do this like this, cause the functions in users class are really badly done.
		$this->users->save(array(
			"uid" => $uid,
			"password" => $password,
			"home_folder" => $this->users->hfid
		));

		// add user to all users grp if we are not under that
		$aug = aw_ini_get("groups.all_users_grp");
		$aug_oid = $this->users->get_oid_for_gid($aug);
		if ($aug_oid != $o->parent())
		{
			$aug_o = obj($aug_oid);
			$o->connect(array(
				"to" => $aug_o->id(),
				"reltype" => "RELTYPE_GRP" // from user
			));

			// add reverse alias to group
			$aug_o->connect(array(
				"to" => $o->id(),
				"reltype" => "RELTYPE_MEMBER" // from group
			));
		}

		return $o;
	}

	function aclwizard_ponder($arr)
	{
		extract($arr);
		// user, oid
		$tmp = $this->db_fetch_field("SELECT brother_of FROM objects WHERE oid = '$oid'", "brother_of");
		if ($tmp != "" && $tmp != $oid)
		{
			$str = sprintf(t("Objekt $oid on vend, &otilde;igusi loetakse objekti %s kaudu.<br>"), $tmp);
			$oid = $tmp;
		}

		// check if the object is deleted or under a deleted object
		list($isd, $dat) = $this->_aclw_is_del($oid);
		if ("del" == $isd)
		{
			return $str.t("Objekt on kustutatud. Pole &otilde;igusi!");
		}
		else
		if ("not" == $isd)
		{
			return $str.t("Objekti pole ega pole kunagi olnud! Pole &otilde;igusi!");
		}
		else
		if ("delp" == $isd)
		{
			return $str.sprintf(t("Objekti &uuml;lemobjekt (%s) on kustutatud. Pole &otilde;igusi!"), $dat);
		}

		// find the controlling acl - select all gids that user belongs to
		// order by priority desc
		// go over objects in path
		// if acl is set, match is there. 
		$ca = $this->_aclw_get_controlling_acl($user, $oid);
		if ($ca === false)
		{
			return $str.t("Objektile pole sellele kasutaja gruppidele &otilde;igusi m&auml;&auml;ratud, kehtib default.<br>N&auml;gemis&otilde;igus ainult.");
		}

		$o_str = "";
		if ($this->can("view", $ca["oid"]))
		{
			$o = obj($ca["oid"]);
			$o_str = html::href(array(
				"url" => $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()),
				"caption" => $o->path_str()
			));
		}
		else
		{
			$o_str = $this->db_fetch_field("select name from objects where oid = '$ca[oid]'", "name")." (oid = $ca[oid])";
		}

		if ($this->can("view", $oid))
		{
			$ro = obj($oid);
			$ro_str = html::href(array(
				"url" => $this->mk_my_orb("change", array("id" => $ro->id()), $ro->class_id()),
				"caption" => $ro->path_str()
			));
		}
		else
		{
			$ro_str = $this->db_fetch_field("select name from objects where oid = '$oid'", "name")." (oid = $oid)";
		}

		$g_o = obj($this->users->get_oid_for_gid($ca["gid"]));
		
		$grpstr = html::href(array(
			"url" => $this->mk_my_orb("change", array("id" => $g_o->id()), $g_o->class_id()),
			"caption" => $g_o->path_str()
		));
		return $str.sprintf(t("Info objekti %s &otilde;iguste kohta: <br><br> &Otilde;igusi m&auml;&auml;rab &otilde;igus-seos objekti %s ja grupi %s vahel.<br><br>Sellele seosele m&auml;&auml;ratud &otilde;igused on j&auml;rgnevad:<br>%s"), $ro_str, $o_str, $grpstr, $this->_aclw_acl_string($ca["acl"]));
	}

	function _aclw_is_del($oid)
	{
		if (!$this->db_fetch_field("SELECT oid FROM objects WHERE oid = '$oid'", "oid"))
		{
			return array("not");
		}

		$parent = $oid;
		while ($parent)
		{
			$dat = $this->db_fetch_row("SELECT parent,status FROM objects WHERE oid = '$parent'");
			if ($dat["status"] == STAT_DELETED)
			{
				if ($parent == $oid)
				{
					return array("del");
				}
				else
				{
					return array("delp", $parent);
				}
			}
			$parent = $dat["parent"];
		}

		return array("ok");
	}

	function _aclw_get_controlling_acl($user, $oid)
	{
		if ($user == "")
		{
			$nlg = $this->get_cval("non_logged_in_users_group");
			$this->db_query("
				SELECT 
					groups.gid as gid, 
					groups.priority as pri
				FROM 
					groupmembers 
					LEFT JOIN groups ON groupmembers.gid = groups.gid
				WHERE
					groups.gid = '$nlg'
				ORDER BY groups.priority DESC
			");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				$parent = $oid;
				while ($parent)
				{
					$adat = $this->db_fetch_row("SELECT * FROM acl WHERE oid = '$parent' AND gid = '$row[gid]'");
					if (is_array($adat))
					{
						return $adat;
					}
	
					$parent = $this->db_fetch_field("SELECT parent FROM objects WHERE oid = '$parent'", "parent");
				}
				$this->restore_handle();
			}
		}
		else
		{
			$this->db_query("
				SELECT 
					groups.gid as gid, 
					groups.priority as pri
				FROM 
					groupmembers 
					LEFT JOIN groups ON groupmembers.gid = groups.gid
				WHERE
					groupmembers.uid = '$user'
				ORDER BY groups.priority DESC
			");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				$parent = $oid;
				while ($parent)
				{
					$adat = $this->db_fetch_row("SELECT * FROM acl WHERE oid = '$parent' AND gid = '$row[gid]'");
					if (is_array($adat))
					{
						return $adat;
					}
	
					$parent = $this->db_fetch_field("SELECT parent FROM objects WHERE oid = '$parent'", "parent");
				}
				$this->restore_handle();
			}
		}

		return false;
	}

	function _aclw_acl_string($int)
	{
		$ids = aw_ini_get("acl.ids");
		$names = aw_ini_get("acl.names");

		$str = array();
		foreach($ids as $bp => $name)
		{
			$cn = $int & (1 << $bp);
			$str[] = $names[$name]." => ".($cn ? "Jah" : "Ei");
		}

		return join("<br>", $str);
	}

	/** displays a form to let the user to change her password
		@attrib name=change_pwd
		
	**/
	function change_pwd()
	{
		print "changing tha password, eh?";
		// I need to return a class_base generated form
	}
	
	/**

		@attrib api=1

		@comment
			This returns object list of group objects that $uid belongs to
	**/
	function get_groups_for_user($uid)
	{
		if(!is_valid("uid", $uid))
		{
			return array();
		}
		$grups_list = array();
		$tmp = users::get_oid_for_uid($uid);
		if (is_oid($tmp) && $this->can("view", $tmp))
		{
			$user_obj = obj($tmp);
			$grups_list = new object_list(
				$user_obj->connections_from(array(
					"type" => "RELTYPE_GRP",
				))
			);
		}
		return $grups_list;
	}
	
	//This returns group object whith highest priority $uid belongs to
	function get_highest_pri_grp_for_user($uid)
	{
		$groups = &$this->get_groups_for_user($uid);
		if(!$groups)
		{
			return false;
		}
		$groups->sort_by(array(
        	"prop" => "priority",
        	"order" => "desc"
    	));
    	return $groups->begin();
	}

	function username_is_taken($uid)
	{
		if (trim($this->db_fetch_field("SELECT uid FROM users WHERE uid LIKE '$uid'", "uid")) != "")
		{
			return true;
		}
		return false;
	}

	function on_save_addr($arr)
	{
		obj_set_opt("no_cache", 1);
		$ml_m = obj($arr["oid"]);
		$c = reset($ml_m->connections_to(array("from.class_id" => CL_USER, "type" => 6)));
		obj_set_opt("no_cache", 0);

		if (!$c)
		{
			return;
		}
		$user = $c->from();

		$rn = $user->prop("real_name");

		if ($user->prop("email") != $ml_m->prop("mail") || $rn != $ml_m->prop("name"))
		{
			$user->set_prop("email", $ml_m->prop("mail"));
			$user->set_prop("real_name", $ml_m->prop("name"));
			$user->save();
		}
	}

	/** handler for user delete. does the delete itself and deletes all other needed objects as well.
		all in all, a very final function. 
	**/
	function on_delete_user($arr)
	{
		obj_set_opt("no_cache", 1);

		$u = get_instance("users");

		aw_disable_acl();
		$user = obj($arr["oid"]);
		if ($user->is_brother())
		{
			return $this->on_delete_user_bro($arr);
		}
		aw_restore_acl();

		// final delete home folder
		$home_folder = $this->db_fetch_field("SELECT home_folder FROM users WHERE oid = ".$user->id(), "home_folder");
		if (is_oid($home_folder) && $this->_object_ex($home_folder))
		{
			aw_disable_acl();
			$hf = obj($home_folder);
			$hf->delete(true);
			aw_restore_acl();
		}

		// final delete default group
		$def_gid = $u->get_gid_by_uid($user->prop("uid"));
		$def_gid_oid = $u->get_oid_for_gid($def_gid);
		if (is_oid($def_gid_oid) && $this->_object_ex($def_gid_oid))
		{
			aw_disable_acl();
			$def_gid_o = obj($def_gid_oid);
			$def_gid_o->delete(true);
			aw_restore_acl();
		}

		if ($def_gid)
		{
			// delete all acl's from the acl table for this user
			$this->db_query("DELETE FROM acl WHERE gid = $def_gid");
		}

		// remove from all groups
		$this->db_query("DELETE FROM groupmembers WHERE uid = '".$user->prop("uid")."'");

		// remove groupmembers from def group
		if ($def_gid)
		{
			$this->db_query("DELETE FROM groupmembers WHERE gid = $def_gid");
		}

		// final delete person
		$person_c = $user->connections_from(array(
			"type" => "RELTYPE_PERSON",
		));
		foreach($person_c as $p_c)
		{
			aw_disable_acl();
			$person = obj($p_c->prop("to"));
			$person->delete(true);
			aw_restore_acl();
		}

		// user's e-mail object
		$mail_c = $user->connections_from(array(
			"type" => "RELTYPE_EMAIL"
		));
		foreach($mail_c as $m_c)
		{
			aw_disable_acl();
			$mail = obj($m_c->prop("to"));
			$mail->delete(true);
			aw_restore_acl();
		}

		// final delete all brothers
		// final delete user object
		aw_disable_acl();
		$user->delete(true);
		aw_restore_acl();

		obj_set_opt("no_cache", 0);

		$c = get_instance("cache");
		$c->full_flush();
	}

	function _object_ex($oid)
	{
		return $this->db_fetch_field("SELECT oid FROM objects WHERE oid = '$oid'", "oid") ? true : false;
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "jdata")
		{
			if (!is_oid($arr["obj_inst"]->id()) || !count($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_JOIN_SITE"))))
			{
				return false;
			}
		}
		return true;
	}

	function callback_jdata($arr)
	{
		$o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_JOIN_SITE");
		$i = get_instance(CL_JOIN_SITE);

		return $i->get_elements_from_obj($o, array(
			"err_return_url" => aw_ini_get("baseurl").aw_global_get("REQUEST_URI"),
			"uid" => $arr["obj_inst"]->prop("uid")
		));
	}
}
?>
