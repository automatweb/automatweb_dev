<?php

/*

@classinfo syslog_type=ST_GROUP relationmgr=yes

@groupinfo general caption=Üldine
@groupinfo import caption=Import
@groupinfo roles caption=Rollid
@groupinfo objects caption="Objektid ja &Otilde;igused"

@tableinfo groups index=oid master_table=objects master_index=oid

@default table=groups
@default group=general

@property gid field=gid type=text
@caption Grupi ID

@property name field=name type=textbox
@caption Nimi

@property priority field=priority type=textbox size=15
@caption Prioriteet

@property modified type=text 
@caption Muudetud

@property modifiedby type=text field=modifiedby
@caption Kes muutis

@property created type=text field=created
@caption Loodud

@property createdby type=text field=createdby
@caption Kes l&otilde;i

@property type type=text store=no
@caption T&uuml;&uuml;p

@property search_form type=relpicker reltype=RELTYPE_SEARCHFORM
@caption Otsinguvorm

@property import type=fileupload store=no group=import
@caption Impordi kasutajaid

@property import_desc type=text store=no group=import

@property roles type=text store=no group=roles no_caption=1

@default group=objects

@property objects type=text store=no no_caption=1
@caption Objektid

@property obj_acl type=callback callback=get_acls store=no


*/

define("RELTYPE_SEARCHFORM", 1);
define("RELTYPE_MEMBER", 2);
define("RELTYPE_ACL", 3);

class group extends class_base
{
	function group()
	{
		$this->init(array(
			'tpldir' => 'core/users/group',
			'clid' => CL_GROUP
		));
		$this->users = get_instance("users");
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_SEARCHFORM => "otsinguvorm",
			RELTYPE_MEMBER => "liige",
			RELTYPE_ACL => "acl"
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_SEARCHFORM)
		{
			return array(CL_FORM);
		}
		if ($args["reltype"] == RELTYPE_MEMBER)
		{
			return array(CL_USER);
		}
		if ($args["reltype"] == RELTYPE_ACL)
		{
			return array(CL_ACL);
		}
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
	
		switch($prop['name'])
		{
			case "comment":
				return PROP_IGNORE;

			case "modified";
				$prop['value'] = $this->time2date($prop['value'], 2);
				break;

			case "created";
				$prop['value'] = $this->time2date($prop['value'], 2);
				break;

			case "type":
				$prop['value'] = ($prop['value'] == GRP_DYNAMIC ? "D&uuml;naamiline" : "Tavaline");
				break;

			case "roles":
				$prop['value'] = $this->_get_roles($this->db_fetch_field("SELECT gid FROM groups WHERE oid = ".$arr["obj"]["oid"],"gid"));
				break;

			case "objects":
				$prop["value"] = $this->_get_objects($this->db_fetch_field("SELECT gid FROM groups WHERE oid = ".$arr["obj"]["oid"],"gid"));
				break;
		
			case "import_desc":
				$prop['value'] = "
					Kasutajate importimise faili formaat on j&auml;rgmine:<br>
					uid,password,nimi,email,aktiivne alates, aktiivne kuni <br>
					v&auml;ljad on eraldatud komadega, iga kasutaja on eraldi real <br>
					kuup&auml;evade formaadi t&auml;pne kirjeldus on <a href=\"http://www.gnu.org/manual/tar-1.12/html_chapter/tar_7.html\">siin</a> <Br>
					n&auml;ide: <br>
					kix,parool,Kristo Iila, kristo@struktuur.ee, 2003-09-17, 2005-09-17 <Br>
					<br>
					v&auml;ljad nimi,email,aktiivne_alates, aktiivne kuni v&otilde;ib soovi korral &auml;ra j&auml;tta<br>
				";
				break;
		}
		return PROP_OK;
	}

	function set_property(&$arr)
	{
		$prop =& $arr["prop"];
		$gid = $this->users->get_gid_for_oid($arr["obj"]["oid"]);

		if ($prop['name'] == 'import')
		{
			global $import;
			$imp = $import;
			if (!is_uploaded_file($import))
			{
				return PROP_OK;
			}
			echo "Impordin kasutajaid ... <Br>";
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
					$this->users->add(array(
						"uid" => $uid,
						"password" => $pass,
						"email" => $email
					));
					if ($gid)
					{
						// add to specified group
						$this->users->add_users_to_group_rec($gid, array($uid));
					}
				}

				if ($act_from)
				{
					$this->users->set_user_config(array(
						"uid" => $uid,
						"key" => "act_from",
						"value" => $act_from
					));
				}

				if ($act_to)
				{
					$this->users->set_user_config(array(
						"uid" => $uid,
						"key" => "act_to",
						"value" => $act_to
					));
				}

				echo "Importisin kasutaja $uid ... <Br>\n";
				flush();
				$first = false;
			}
		}
		else
		if ($prop['name'] == "obj_acl")
		{
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
		}

		return PROP_OK;
	}

	function callback_mod_retval($arr)
	{
		if ($arr["form_data"]["edit_acl"])
		{
			$arr["args"]["edit_acl"] = $arr["form_data"]["edit_acl"];
		}
	}

	function _get_roles($gid)
	{
		$roles = array();

		$acl = get_instance("acl_class");
		$acls = $acl->get_acls_for_group($gid);
		foreach($acls as $acl_oid)
		{
			$roles[] = $acl->get_roles_for_acl($acl_oid);
		}

		$t =& $this->_init_roles_table();

		
		foreach(array_unique($roles) as $role)
		{
			$r_o = $this->get_object($role);
			$r_o["name"] = html::href(array(
				"url" => $this->mk_my_orb("change", array("id" => $role), "role"),
				"caption" => $r_o["name"]
			));
			$r_o["del"] = html::href(array(
				'url' => $this->mk_my_orb("remove_role_from_group", array("role_id" => $role, "gid" => $gid)),
				'caption' => "Eelmalda"
			));
			$t->define_data($r_o);
		}
		$t->set_default_sortby("name");
		$t->sort_by();
		return $t->draw();
	}

	function &_init_roles_table()
	{
		load_vcl("table");
		$t = new aw_table(array("layout" => "generic"));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
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
			"name" => "del",
			"caption" => "Eelmalda",
		));

		return $t;
	}

	function _get_objects($gid)
	{
		// now, get all the folders that have access set for these groups
		$dat = $this->acl_get_acls_for_groups(array("grps" => array($gid)));
		
		$t =& $this->_init_obj_table(array(
			"exclude" => array("grp_name")
		));

		$ml = $this->get_menu_list();		

		foreach($dat as $row)
		{
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

	function &_init_obj_table($arr)
	{
		if (!isset($arr["exclude"]))
		{
			$arr["exclude"] = array();
		}
		extract($arr);

		load_vcl("table");
		$t = new aw_table(array("layout" => "generic"));

		if (!in_array("obj_name",$exclude))
		{
			$t->define_field(array(
				"name" => "obj_name",
				"caption" => "Objekti Nimi",
				"sortable" => 1,
			));
		}

		if (!in_array("obj_parent",$exclude))
		{
			$t->define_field(array(
				"name" => "obj_parent",
				"caption" => "Objekti Asukoht",
				"sortable" => 1,
			));
		}

		if (!in_array("grp_name",$exclude))
		{
			$t->define_field(array(
				"name" => "grp_name",
				"caption" => "Grupi Nimi",
				"sortable" => 1,
			));
		}

		if (!in_array("acl",$exclude))
		{
			$t->define_field(array(
				"name" => "acl",
				"caption" => "&Otilde;igused",
				"sortable" => 1,
			));
		}

		return $t;
	}

	////
	// !override alias adding
	function callback_on_addalias($arr)
	{
		$tar = explode(",", $arr["alias"]);
		foreach($tar as $alias)
		{
			$arr["alias"] = $alias;
			$this->on_addalias($arr);
		}
	}

	function on_addalias($arr)
	{
		// now, if the alias is of type RELTYPE_MEMBER, then we must add the user to the group
		if ($arr["reltype"] == RELTYPE_MEMBER)
		{
			// get the gid to what we must add the user
			$gid = $this->users->get_gid_for_oid($arr["id"]);
			// get the uid to add
			$uid = $this->users->get_uid_for_oid($arr["alias"]);

			$this->users->add_users_to_group_rec($gid, array($uid), true, true);
		}
		
		// if alias is acl, then we gots to add the group to that acl object
		if ($arr["reltype"] == RELTYPE_ACL)
		{
			$this->normalize_acl_group_aliases();
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

	////
	// !removes role $role_id from group $gid 
	// - does this, by finding all acl objects that contain this group and that role, then removing the group from those acls
	function remove_role_from_group($arr)
	{
		extract($arr);
		
		$acl = get_instance("acl_class");
		$acls = $acl->get_acls_for_group($gid);
		foreach($acls as $acl_oid)
		{
			$role = $acl->get_roles_for_acl($acl_oid);
			if ($role == $role_id)
			{
				$acl->remove_group_from_acl($acl_oid, $gid);
			}
		}
		$u = get_instance("users");
		return $this->mk_my_orb("change", array("id" => $u->get_oid_for_gid($gid), "group" => "roles"));
	}

	////
	// !this gets called when alias is deleted
	function on_delete_alias($arr)
	{
		extract($arr);
		// if the alias to delete is acl, then we must remove this group from the acl.
		$a_o = $this->get_object($alias);
		if ($a_o["class_id"] == CL_ACL)
		{
			$a = get_instance("acl_class");
			$a->remove_group_from_acl($a_o[OID], $this->users->get_gid_for_oid($id));
		}
		else
		if ($a_o["class_id"] == CL_USER)
		{
//			echo "remove users from group , , id = $id gid = ".$this->users->get_gid_for_oid($id)." alias = $alias, uid = ".$this->users->get_uid_for_oid($alias)." <br>";
			$this->users->remove_users_from_group_rec(
				$this->users->get_gid_for_oid($id),
				array($this->users->get_uid_for_oid($alias))
			);
		}
	}

	function callback_on_submit_relation_list($arr)
	{
		// here we gots to remove the group from all acls that are not in alias list
		$this->normalize_acl_group_aliases();
	}

	function normalize_acl_group_aliases()
	{
		return;
		// get all groups
		$go = $this->list_objects(array(
			"class" => CL_GROUP,
			"return" => ARR_ALL
		));

		$a = get_instance("acl_class");

		foreach($go as $gobj)
		{
			// get acls for this group
			$acls = $a->get_acls_for_group($this->users->get_gid_for_oid($gobj["oid"]));
			// get all current aliases for acls
			$als = $this->get_aliases(array(
				"oid" => $gobj["oid"],
				"reltype" => RELTYPE_ACL
			));
			
			// now, add all acls that are not aliases
			foreach($acls as $acl)
			{
				$found = false;
				foreach($als as $al)
				{
					if ($al["id"] == $acl)
					{
						$found = true;
					}
				}

				if (!$found)
				{
					core::addalias(array(
						"id" => $gobj["oid"],
						"alias" => $acl,
						"reltype" => RELTYPE_ACL,
						"no_cache" => true
					));
				}
			}

			// now, remove all aliases that are not in $acls
			foreach($als as $al)
			{
				if (!isset($acls[$al["id"]]))
				{
					$this->delete_alias($gobj["oid"], $al["id"], true);
				}
			}
		}		
	}

	function callback_pre_save($arr)
	{
		$arr["coredata"]["name"] = $arr["form_data"]["name"];
	}

	function on_delete_hook($oid)
	{
		$gid = $this->users->get_gid_for_oid($oid);
		if ($gid)
		{
			$this->users->deletegroup($gid);
		}
	}
}
?>
