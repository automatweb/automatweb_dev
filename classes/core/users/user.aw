<?php

/*

@classinfo syslog_type=ST_USER relationmgr=yes

@groupinfo general caption=Üldine
@groupinfo chpwd caption="Muuda parooli"
@groupinfo roles caption=Rollid
@groupinfo objects caption="Objektid ja &Otilde;igused"
	@groupinfo objects_own caption="Enda tehtud" parent=objects
	@groupinfo objects_other caption="Teiste tehtud" parent=objects
@groupinfo roles caption="Rollid"
@groupinfo groups caption=Grupid
@groupinfo jdata caption="Liitumise info"
@groupinfo stat caption=Statistika

@tableinfo users index=oid master_table=objects master_index=brother_of

@default table=users
@default group=general

@property uid field=uid type=text group=general
@caption User ID

@property logins field=logins type=text
@caption Logins

@property online store=no type=text
@caption Online

@property lastaction field=lastaction type=text
@caption Last action

@property blocked field=blocked type=checkbox ch_value=1
@caption Blokeeritud

@property name store=no type=textbox
@caption Name

@property email field=email type=textbox
@caption E-mail

@property created field=created type=date
@caption Created

@property createdby field=createdby type=text
@caption Created by

@property admin_lang store=no type=select
@caption Admin lang.

@property act_from store=no type=date_select
@caption Aktiivne alates

@property act_to store=no type=date_select
@caption Aktiivne kuni

@default group=chpwd

@property passwd field=password type=password store=no
@caption Password

@property passwd_again store=no type=password store=no
@caption Password veelkord

@property gen_pwd store=no type=text 
@caption Genereeri parool

@property genpwd store=no type=textbox 
@caption Genereeritud parool

@property resend_welcome store=no type=checkbox ch_value=1
@caption Saada tervitusmeil uuesti

@property pwd_status store=no type=text
@caption 

@default group=roles

@property roles type=text store=no no_caption=1
@caption Rollid

@default group=objects

@property objects_own type=text  store=no no_caption=1 group=objects_own
@caption Objektid

@property objects_other type=text  store=no no_caption=1 group=objects_other
@caption Objektid

@property obj_acl type=callback callback=get_acls store=no group=objects_own

@property obj_acl_other type=callback callback=get_acls store=no group=objects_other

@default group=groups

@property groups type=text  store=no no_caption=1

@default group=roles

@property roles type=text  store=no no_caption=1

@default group=jdata

@property jdata type=text  store=no
@caption Liitumise andmed

@default group=stat

@property stat type=text store=no no_caption=1
@caption Statistika

*/

define("RELTYPE_GRP", 1);


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

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_GRP => "grupp",
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_GRP)
		{
			return array(CL_GROUP);
		}
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "comment":
				return PROP_IGNORE;
			
			case "online":
				$timeout = ini_get("session.gc_maxlifetime");
				$prop['value'] = ((time() - $arr["objdata"]["lastaction"]) < $timeout) ? LC_YES : LC_NO;
				break;

			case "lastaction";
				$prop['value'] = $this->time2date($prop['value'],2);
				break;

			case "name":
				$prop['value'] = $this->users->get_user_config(array(
					"uid" => $arr["objdata"]["uid"],
					"key" => "real_name",
				));
				break;
			
			case "created":
				$prop['value'] = $this->time2date($prop['value'],2);
				break;

			case "admin_lang":
				$l = get_instance("languages");
				$prop['options'] = $l->get_list();
				$prop['value'] = aw_global_get("admin_lang");
				break;

			case "act_from":
				$prop['value'] = $this->users->get_user_config(array(
					"uid" => $arr["objdata"]["uid"],
					"key" => "act_from",
				));
				break;
			
			case "act_to":
				$prop['value'] = $this->users->get_user_config(array(
					"uid" => $arr["objdata"]["uid"],
					"key" => "act_to",
				));
				break;

			case "pwd_status":
				$prop['value'] = aw_global_get("status_msg");
				break;

			case "groups":
				$prop['value'] = $this->_get_group_membership($arr["objdata"]["uid"], $arr["obj"]["oid"]);
				break;

			case "roles":
				$prop['value'] = $this->_get_roles($arr["objdata"]["uid"]);
				break;

			case "objects_own":
				$prop["value"] = $this->_get_objects($arr["objdata"]["uid"], true);
				break;

			case "objects_other":
				$prop["value"] = $this->_get_objects($arr["objdata"]["uid"], false);
				break;

			case "stat":
				$prop["value"] = $this->_get_stat($arr["objdata"]["uid"]);
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
					"caption" => "Genereeri parool"
				));
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
			case "name":
				$this->users->set_user_config(array(
					"uid" => $arr["objdata"]["uid"],
					"key" => "real_name",
					"value" => $prop['value']
				));
				break;

			case "admin_lang":
				$t = get_instance("languages");

				$admin_lang = $prop['value'];
				$admin_lang_lc = $t->get_langid($admin_lang);
				setcookie("admin_lang",$admin_lang,time()*24*3600*1000,"/");
				setcookie("admin_lang_lc",$admin_lang_lc,time()*24*3600*1000,"/");
				break;

			case "act_from":
				$this->users->set_user_config(array(
					"uid" => $arr["objdata"]["uid"],
					"key" => "act_from",
					"value" => date_edit::get_timestamp($prop['value'])
				));
				break;

			case "act_to":
				$this->users->set_user_config(array(
					"uid" => $arr["objdata"]["uid"],
					"key" => "act_to",
					"value" => date_edit::get_timestamp($prop['value'])
				));
				break;

			case "passwd_again":
				if ($prop['value'] != "")
				{
					if ($prop['value'] != $arr['form_data']['passwd'])
					{
						aw_session_set("status_msg", "Paroolid pole samad!");
					}
					else
					if (!is_valid("password", $prop['value']))
					{
						aw_session_set("status_msg", "Parool sisaldab lubamatuid t&auml;hti!");
					}
					else
					{
						// change pwd
						$this->users->save(array(
							"uid" => $arr["objdata"]["uid"], 
							"password" => $prop['value']
						));
					}
				}
				break;
			
			case "resend_welcome":
				if ($prop['value'] == 1)
				{
					$this->users->send_welcome_mail(array(
						"uid" => $arr["objdata"]["uid"],
						"pass" => $arr['form_data']['password']
					));
				}
				break;

			case "groups":
				$prop['value'] = $this->_set_group_membership($arr["objdata"]["uid"], $arr["form_data"], $arr["obj"]["oid"]);
				break;

			case "obj_acl":
				// read all acls from request and set them
				$ea = $arr["form_data"]["edit_acl"];
				if ($ea)
				{
					$a = $this->acl_list_acls();
					$acl = array();
					foreach($a as $a_bp => $a_name)
					{
						$acl[$a_name] = $arr["form_data"]["acl_".$a_bp];
					}
					$this->save_acl($ea, $gid, $acl);
				}
				break;
		}
		return PROP_OK;
	}

	function _get_group_membership($uid, $id)
	{
		$gl = $this->users->get_group_list(array("type" => array(GRP_REGULAR, GRP_DYNAMIC)));
		// now, get all RELTYPE_GRP aliases and remove all others from this list
		/*		$als = $this->get_aliases(array(
			"oid" => $this->users->get_oid_for_uid($uid),
			"reltype" => RELTYPE_GRP
		));
		$_gl = array();
		foreach($als as $alias)
		{
			$_gid = $this->users->get_gid_for_oid($alias["target"]);
			$_gl[$_gid] = $gl[$_gid];		
		}
		$gl = $_gl;*/

		// get all groups this user is member of
		$groups = $this->users->getgroupsforuser($uid);
	
		$t =& $this->_start_gm_table();		

		foreach($gl as $gid => $gd)
		{
			if ($gd["type"] == GRP_DYNAMIC)
			{
				$gd["type"] = "D&uuml;naamiline";
				$gd["is_member"] = (isset($groups[$gid]) ? LC_YES : LC_NO);
			}
			else
			{
				$gd["type"] = "Tavaline";
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
		// now, get all RELTYPE_GRP aliases and remove all others from this list
		$als = $this->get_aliases(array(
			"oid" => $id,
			"reltype" => RELTYPE_GRP
		));
		$_gl = array();
		$_groups = array();
		foreach($als as $alias)
		{
			$_gid = $this->users->get_gid_for_oid($alias["target"]);
			$_gl[$_gid] = $gl[$_gid];
			$_groups[$_gid] = $groups[$_gid];
		}
		$gl = $_gl;
		$groups = $_groups;

		// now, go over both lists and get rid of the dyn groups
		$_member = array();
		foreach($member as $gid => $is)
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
				$this->users->remove_users_from_group_rec($gid, array($uid));
			}
		}

		// now, add to all groups
		foreach($member as $gid => $is)
		{
			if ($is && !$groups[$gid])
			{
				$this->users->add_users_to_group_rec($gid, array($uid));
			}
		}
	}

	function &_start_gm_table()
	{
		load_vcl("table");
		$t = new aw_table(array("layout" => "generic","prefix" => "uglist"));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "is_member",
			"caption" => "Liige?",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "priority",
			"caption" => "Prioriteet",
			"sortable" => 1,
			"numeric" => 1,
			"align" => "center"
		));
		
		$t->define_field(array(
			"name" => "gcount",
			"caption" => "Mitu liiget",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
			"sortable" => 1,
			"align" => "center"
		));

		$df = aw_ini_get("config.dateformats");
		$t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"sortable" => 1,
			"type" => "time",
			"format" => $df[2]
		));

		$t->define_field(array(
			"name" => "type",
			"caption" => "T&uuml;&uuml;p",
			"sortable" => 1,
			"align" => "center"
		));

		return $t; 
	}

	////
	// !this will automatically get called when an object of this type is deleted
	function delete_hook($arr)
	{
		extract($arr);
		// now we must find to what user this object pointed to and remove the user from the parent group
		$obj = $this->get_object($oid);

		// get the user
		$uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $obj[brother_of]", "uid");

		// unless the object is the original user, in which case, we must delete it and all it's brothers and block the user

		if ($obj["brother_of"] == $obj["oid"])
		{
			// block user and delete all objects
			$this->users->do_delete_user($uid);

			$this->delete_brothers_of($oid);
		}
		else
		{
			// remove the user from this and all groups above
			$gid = $this->db_fetch_field("SELECT gid FROM groups WHERE oid = $obj[parent]", "gid");

			$this->users->remove_users_from_group_rec($gid, array($uid), true);
		}
	}

	////
	// !this will get automatically called if an object of this type is cut-pasted
	// params:
	//	oid
	//	new_parent
	function cut_hook($arr)
	{
		extract($arr);

		// check if it is original or brother
		// if original, do nothing
		// if brother, find new group and change group membership

		$obj = $this->get_object($oid);
		if ($obj["brother_of"] != $obj["oid"])
		{
			// old parent group
			$o_gid = $this->db_fetch_field("SELECT gid FROM groups WHERE oid = $obj[parent]", "gid");

			// new gid
			$n_gid = $this->db_fetch_field("SELECT gid FROM groups WHERE oid = $new_parent", "gid");

			// get the user
			$uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $obj[brother_of]", "uid");

			if ($o_gid)
			{
				$this->users->remove_users_from_group_rec($o_gid, array($uid));
			}
			if ($n_gid)
			{
				$this->users->add_users_to_group_rec($n_gid, array($uid));
			}
		}		
	}

	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;

		$this->quote(&$row);
		$id = $this->new_object($row);
	
		if ($id)
		{
			// also, update group membership
			// new gid
			$n_gid = $this->db_fetch_field("SELECT gid FROM groups WHERE oid = $parent", "gid");

			// get the user
			$uid = $this->db_fetch_field("SELECT uid FROM users WHERE oid = $row[brother_of]", "uid");

			if ($n_gid)
			{
				$this->users->add_users_to_group_rec($n_gid, array($uid));
			}
			return true;
		}
		return false;
	}

	function _get_roles($uid)
	{
		// get user gid
		$gid = $this->users->get_gid_by_uid($uid);

		// show roles table by group
		$gr = get_instance("core/users/group");
		return $gr->_get_roles($gid);
	}

	function _get_objects($uid, $own)
	{
		// get all groups this user is a member of
		$grps = $this->users->getgroupsforuser($uid);
		// now, get all the folders that have access set for these groups
		$dat = $this->acl_get_acls_for_groups(array("grps" => array_keys($grps)));

		$g = get_instance("core/users/group");
	
		$ml = $this->get_menu_list();		

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
			$row['obj_parent'] = $ml[$row['obj_parent']];
			$row["acl"] = html::href(array(
				"caption" => "Muuda",
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

	function callback_mod_retval($arr)
	{
		if ($arr["form_data"]["edit_acl"])
		{
			$arr["args"]["edit_acl"] = $arr["form_data"]["edit_acl"];
		}
	}

	function get_acls($arr)
	{
		$acls = array();
		$ea = $arr["request"]["edit_acl"];
		if ($ea)
		{
			$o = $this->get_object($ea);
			$acls["acl_desc"] = array(
				'name' => "acl_desc",
				'type' => 'text',
				'store' => 'no',
				'group' => 'objects',
				'value' => 'Muuda objekti '.$o[name].' &otilde;igusi'
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

/*	function on_delete_alias($arr)
	{
		extract($arr);
		// if the alias to delete is acl, then we must remove this group from the acl.
		$a_o = $this->get_object($alias);
		if ($a_o["class_id"] == CL_ACL)
		{
			$a = get_instance("acl_class");
			$a->remove_group_from_acl($a_o[OID], $this->users->get_gid_for_oid($id));
		}
	}*/

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
			'caption' => 'Nr',
		));
		$t->define_field(array(
			'name' => 'tm',
			'caption' => 'Millal',
			'sortable' => 1,
			'numeric' => 1,
			'type' => 'time',
			'format' => $df[2],
			'nowrap' => 1
		));
		$t->define_field(array(
			'name' => 'uid',
			'caption' => 'Kes',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'ip',
			'caption' => 'IP',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'type',
			'caption' => 'T&uuml;&uuml;p',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'act_id',
			'caption' => 'Tegevus',
			'sortable' => 1,
		));
		if (aw_ini_get("syslog.has_site_id"))
		{
			$t->define_field(array(
				'name' => 'site_id',
				'caption' => 'Saidi ID',
				'sortable' => 1,
			));
		}
		$t->define_field(array(
			'name' => 'oid',
			'caption' => 'OID',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'action',
			'caption' => 'Mida',
			'sortable' => 1,
		));
		return $t;
	}

	function add($arr)
	{
		return $this->users->add_user($arr);
	}
}
?>
