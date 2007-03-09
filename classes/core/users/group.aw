<?php

/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_GROUP, on_save_grp)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_GROUP, on_delete_grp)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_GROUP, on_add_alias_to_group)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_GROUP, on_remove_alias_from_group)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_GROUP, on_add_alias_for_group)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_TO, CL_GROUP, on_remove_alias_for_group)
*/

/*


@classinfo syslog_type=ST_GROUP relationmgr=yes no_comment=1 no_status=1

@groupinfo dyn_search caption=Otsing submit=no
@groupinfo import caption=Import
@groupinfo objects caption="Objektid ja &Otilde;igused"
@groupinfo admin_rm caption="Admin rootmen&uuml;&uuml;"
@groupinfo img caption="Pilt"
@groupinfo if_acl caption="Liidese &otilde;igused"

@tableinfo groups index=oid master_table=objects master_index=oid

@default table=groups


@default group=general
	
	@property gid field=gid type=text
	@caption Grupi ID

	@property name field=name type=textbox
	@caption Nimi

	@property priority field=priority type=textbox size=15
	@caption Prioriteet

	@property modified type=text table=objects field=modified
	@caption Muudetud

	@property mmodifiedby type=text store=no editonly=1
	@caption Kes muutis

	@property created type=text field=created table=objects
	@caption Loodud

	@property mcreatedby type=text store=no editonly=1
	@caption Kes l&otilde;i

	@property type type=select 
	@caption T&uuml;&uuml;p

	@property search_form type=relpicker reltype=RELTYPE_SEARCHFORM
	@caption Otsinguvorm

	@property grp_frontpage type=callback callback=get_grp_frontpage field=meta method=serialize table=objects
	@caption Esileht

	@property require_change_pass type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption N&otilde;ua parooli vahetust esimesel logimisel

	@property for_not_logged_on_users type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Sisselogimata kasutajatele

	@property default_acl type=callback callback=callback_get_default_acl store=no rel=1
	@caption Default ACL

@default group=dyn_search
	
	@property data type=text no_caption=1

@default group=import

	@property import type=fileupload store=no 
	@caption Impordi kasutajaid

	@property import_desc type=text store=no 

@default group=objects

	@property objects type=text store=no no_caption=1
	@caption Objektid

	@property obj_acl type=callback callback=get_acls store=no 

	@property gp_parent type=hidden field=parent table=groups
	@property gp_gid type=hidden field=gid table=groups

@default group=admin_rm

	@property admin_rootmenu2 type=callback callback=get_admin_rootmenus field=meta method=serialize table=objects
	@caption Administreerimisliidese juurkaust

@default group=img

	@property picture type=releditor reltype=RELTYPE_PICTURE rel_id=first props=file field=meta method=serialize table=objects
	@caption Pilt/foto

@default group=if_acl

	@property can_admin_interface type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kas saab administreerimiskeskkonda

	@property if_acls_set type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Liidese &otilde;igused on piiratud

	@property can_quick_add type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kas saab kasutada kiirlisamist

	@property can_bm type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kas saab kasutada j&auml;rjehoidjat

	@property can_history type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kas saab kasutada ajalugu

	@property can_search type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kas saab kasutada otsingut

	@property can_search type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kas saab kasutada otsingut

	@property default_yah_ct type=textbox field=meta table=objects method=serialize
	@caption Vaikimisi asukohariba

	@property disp_person type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kuva isikut

	@property disp_co_edit type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kuva organisatsiooni muutmisvaadet

	@property disp_co_view type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kuva organisatsiooni vaatamisvaadet

	@property disp_object_type type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kuva objektit&uuml;&uuml;pi

	@property disp_object_link type=checkbox ch_value=1 field=meta table=objects method=serialize
	@caption Kuva objekti muutmislinki

	@property editable_settings type=select ch_value=1 field=meta table=objects method=serialize multiple=1
	@caption Vali muudetavad seaded



@reltype SEARCHFORM value=1 clid=CL_FORM
@caption Otsinguvorm

@reltype MEMBER value=2 clid=CL_USER
@caption Liige

@reltype ADMIN_ROOT value=4 clid=CL_MENU
@caption Rootmen&uuml;&uuml;

@reltype ADD_TREE value=5 clid=CL_ADD_TREE_CONF
@caption Lisamise puu

@reltype PICTURE value=6 clid=CL_IMAGE
@caption Pilt


*/

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

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
	
		switch($prop['name'])
		{
			case "name":
				if ($arr["obj_inst"]->class_id() == CL_RELATION)
				{
					$c = new connection();
					list(, $c_d) = each($c->find(array("relobj_id" => $arr["obj_inst"]->id())));
					$c = new connection($c_d["id"]);

					if (!$this->can("admin", $c->prop("from")))
					{
						error::raise(array(
							"id" => "ERR_ACL",
							"msg" => sprintf(t("Teil ei ole &otilde;igust muuta objekti %s &otilde;igusi!"), $c->prop("from"))
						));
					}
					return PROP_IGNORE;
				}
				if ($prop["value"] == "" && $arr["obj_inst"]->name() != "")
				{
					$prop["value"] = $arr["obj_inst"]->name();
				}
				break;

			case "data":
				$f = get_instance(CL_FORM);
				$prop['value'] = $f->gen_preview(array(
					"id" => $arr["obj_inst"]->prop("search_form"),
					"entry_id" => $prop['value'], 
					"extraids" => array(
						"group_id" => $arr["obj_inst"]->id(),
					),
					"tpl" => "show_noform.tpl"
				));				
				break;

			case "modified";
				$prop['value'] = $this->time2date($prop['value'], 2);
				break;

			case "created";
				$prop['value'] = $this->time2date($prop['value'], 2);
				break;

			case "mcreatedby":
				$o = $arr["obj_inst"];
				$prop['value'] = $o->createdby();
				break;
				
			case "mmodifiedby":
				$o = $arr["obj_inst"];
				$prop['value'] = $o->modifiedby();
				break;
				
			case "type":
				$prop['options'] = array(
					GRP_REGULAR => t('Tavaline'),
					GRP_DYNAMIC => t("D&uuml;naamiline")
				);
				break;

			case "objects":
				$prop["value"] = $this->_get_objects($this->db_fetch_field("SELECT gid FROM groups WHERE oid = ".$arr["obj_inst"]->id(),"gid"));
				break;
		
			case "import_desc":
				$prop['value'] = t("
					Kasutajate importimise faili formaat on j&auml;rgmine:<br />
					kasutajanimi,parool,nimi,e-post,aktiivne alates,aktiivne kuni <br />
					v&auml;ljad on eraldatud komadega, iga kasutaja on eraldi real <br />
					kuup&auml;evade formaadi t&auml;pne kirjeldus on <a href=\"http://www.gnu.org/software/tar/manual/html_node/tar_109.html\">siin</a> <br />
					n&auml;ide: <br />
					kix,parool,Kristo Iila, kristo@struktuur.ee, 2003-09-17, 2005-09-17 <br />
					<br />
					v&auml;ljad nimi,email,aktiivne_alates, aktiivne kuni v&otilde;ib soovi korral &auml;ra j&auml;tta<br />
				");
				break;

			case "editable_settings":
				$o = obj(aw_global_get("uid_oid"));
				$prop["options"] = array();
				foreach($o->get_group_list() as $gid => $gd)
				{
					$prop["options"][$gid] = $gd["caption"];
				}
				break;
		}
		return PROP_OK;
	}

	function set_property(&$arr)
	{
		$prop =& $arr["prop"];
		$gid = $this->users->get_gid_for_oid($arr["obj_inst"]->id());

		if ($prop['name'] == 'data')
		{
			if (isset($arr['request']['gid']))
			{
				$gid = $arr['request']['gid']; // This avoids error upon group copy-paste
			}
			else
			{
				$gid = $this->users->get_gid_for_oid($arr["request"]["group_id"]);
			}	
			$pg = $this->users->fetchgroup($gid);
			if (isset($pg["search_form"]))
			{
				$f = get_instance(CL_FORM);
				$f->process_entry(array(
					"id" => $pg["search_form"], 
					"entry_id" => $arr["entry_id"]
				));
				$eid = $f->entry_id;

				$this->db_query("UPDATE groups SET data = '$eid' WHERE gid = '$gid'");
			
				$this->users->update_dyn_group($gid);
			}
		}
		else
		if ($prop['name'] == 'import')
		{
			global $import;
			$imp = $import;
			if (!is_uploaded_file($import))
			{
				return PROP_OK;
			}

			$us = get_instance(CL_USER);
			echo t("Impordin kasutajaid ... <br />");
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

				$row = $this->db_fetch_row("SELECT uid,oid FROM users WHERE uid = '$uid'");
				if (!is_array($row))
				{
					$uo = $us->add_user(array(
						"uid" => $uid,
						"password" => $pass,
						"email" => $email,
						"real_name" => $name
					));
				}
				else
				{
					echo "kasutaja $uid ($name) on juba olemas, lisan ainult gruppi ja ei muuda parooli!<br>";
					if (is_oid($row["oid"]) && $this->can("view", $row["oid"]))
					{
						$uo = obj($row["oid"]);
					}
				}

				if ($uo)
				{
					// add to specified group
					$this->add_user_to_group($uo,$arr["obj_inst"]);
				}

				if ($act_from)
				{
					$uo->set_prop("act_from", $act_from);
				}

				if ($act_to)
				{
					$uo->set_prop("act_to", $act_to);
				}
				$uo->save();

				echo "Importisin kasutaja $uid ... <br />\n";
				flush();
				$first = false;
			}
		}
		else
		if ($prop['name'] == "obj_acl")
		{
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
		}
		else
		if ($prop["name"] == "default_acl")
		{
			$this->_do_save_def_acl($arr);
		}

		return PROP_OK;
	}

	function _do_save_def_acl($arr)
	{
		$da = array();
		$aclids = aw_ini_get("acl.ids");
		foreach($aclids as $acln)
		{
			$da[$acln] = $arr["request"]["acl_".$acln];
		}

		if ($arr["obj_inst"]->class_id() == CL_RELATION)
		{
			// FIXME: classbase will automatically give the connection as a parameter, but 
			// currently we do this ourselves

			/*
			if ($arr["obj_inst"]->meta("conn_id"))
			{
				$c = new connection($arr["obj_inst"]->meta("conn_id"));
			}
			else
			{
			*/
				$c = new connection();
				list(, $c_d) = each($c->find(array("relobj_id" => $arr["obj_inst"]->id())));
				$c = new connection($c_d["id"]);
			/*
			}
			*/

			// now set the real acl from the connection
			$grp = $c->to();
			$this->save_acl($c->prop("from"), $grp->prop("gp_gid"), $da);
		}
		else
		{
			$arr["obj_inst"]->set_meta("default_acl", $da);
		}
	}

	function callback_mod_retval($arr)
	{
		if ($arr["request"]["edit_acl"])
		{
			$arr["args"]["edit_acl"] = $arr["request"]["edit_acl"];
		}
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
			$o = obj($row['oid']);
			$row['obj_name'] = html::href(array(
				'url' => $this->mk_my_orb('change',array(
					'id' => $row['oid'],
					'return_url' => get_ru(),
				), $o->class_id()),
				'caption' => $row['obj_name'],
			));
			$row['obj_parent'] = $ml[$row['obj_parent']];	
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
				"caption" => t("Objekti Nimi"),
				"sortable" => 1,
			));
		}

		if (!in_array("obj_parent",$exclude))
		{
			$t->define_field(array(
				"name" => "obj_parent",
				"caption" => t("Objekti Asukoht"),
				"sortable" => 1,
			));
		}

		if (!in_array("grp_name",$exclude))
		{
			$t->define_field(array(
				"name" => "grp_name",
				"caption" => t("Grupi Nimi"),
				"sortable" => 1,
			));
		}

		if (!in_array("acl",$exclude))
		{
			$t->define_field(array(
				"name" => "acl",
				"caption" => t("&Otilde;igused"),
				"sortable" => 1,
			));
		}

		return $t;
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
				'value' => sprintf(t('Muuda objekti %s &otilde;igusi'), $o->name())
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

	function callback_pre_save($arr)
	{
		if (isset($arr["request"]["name"]))
		{
			$arr["obj_inst"]->set_name($arr["request"]["name"]);
		}
	}

	function callback_mod_tab($parm)
	{
		$id = $parm['id'];
		if ($id == 'dyn_search')
		{
			$od = $this->users->fetchgroup($this->users->get_gid_for_oid($parm['obj_inst']->id()));
			if ($od["type"] != GRP_DYNAMIC)
			{
				return false;
			}
		}
		return true;
	}

	function get_admin_rootmenus($arr)
	{
		$ret = array();
		$la = get_instance("languages");
		$ll = $la->get_list(array(
			"ignore_status" => true
		));

		$meta = $arr["obj_inst"]->meta();

		$ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ADMIN_ROOT")));
		$oopts = array("" => t("--vali--")) + $ol->names();

		foreach($ll as $lid => $lname)
		{
			$opts = $oopts;
			foreach((array)$meta["admin_rootmenu2"][$lid] as $k => $v)
			{
				if (!isset($opts[$v]) && $this->can("view", $v))
				{
					$o = obj($v);
					$opts[$v] = $o->name();
				}
			}
			$ret["admin_rootmenu2[$lid]"] = array(
				"name" => "admin_rootmenu2[$lid]",
				"type" => "relpicker",
				"group" => "settings",
				"table" => "objects",
				"field" => "meta",
				"method" => "serialize",
				"multiple" => 1,
				"caption" => sprintf(t("Administreerimisliidese juurkaust (%s)"), $lname),
				"value" => $meta["admin_rootmenu2"][$lid],
				"reltype" => "RELTYPE_ADMIN_ROOT",
				"options" => $opts
			);
		}
		return $ret;
	}

	function get_grp_frontpage($arr)
	{
		$ret = array();
		$la = get_instance("languages");
		$ll = $la->get_list();

		$meta = $arr["obj_inst"]->meta();

		foreach($ll as $lid => $lname)
		{
			$ret["grp_frontpage[$lid]"] = array(
				"name" => "grp_frontpage[$lid]",
				"type" => "relpicker",
				"group" => "settings",
				"table" => "objects",
				"field" => "meta",
				"method" => "serialize",
				"caption" => sprintf(t("Esileht (%s)"), $lname),
				"value" => $meta["grp_frontpage"][$lid],
				"reltype" => "RELTYPE_ADMIN_ROOT"
			);
		}
		return $ret;
	}

	function on_save_grp($arr)
	{
		$o = obj($arr["oid"]);

		$modified = false;

		// ok, find all the groups that this group object is under
		// and make it really a part of those groups
		foreach(array_reverse($o->path()) as $p_o)
		{
			if ($p_o->id() == $arr["oid"])
			{
				continue;
			}

			if ($p_o->class_id() == CL_GROUP)
			{
				$p_gid = $this->users->get_gid_for_oid($p_o->id());

				if ($o->prop("gp_parent") != $p_gid)
				{
					$o->set_prop("gp_parent", $p_gid);
					$o->save();
					$modified = true;
				}
			}

			if ($modified)
			{
				break;
			}
		}

		if ($modified && $p_gid)
		{
			// this of course means we get to update user membership as well.
			// what we gotta do is get all users from this group and add them to the parent group
			
			unset($this->users->grpcache);
			unset($this->users->grpcache2);

			// this will also trigger alias creation and other magic that syncs back from 
			// user tables to object table
			$members = $this->users->getgroupmembers2($this->users->get_gid_for_oid($o->id()));
			if (count($members) > 0)
			{
				$this->users->add_users_to_group_rec(
					$p_gid,
					$members
				);
			}
			$c = get_instance("cache");
			$c->file_clear_pt("acl");
		}
	}

	function on_remove_alias_from_group($arr)
	{
		$uid_o = $arr["connection"]->to();
		$grp_o = $arr["connection"]->from();

		$uid = $this->users->get_uid_for_oid($uid_o->id());
		$gid = $this->users->get_gid_for_oid($grp_o->id());

		// remove user from group rec
		$this->users->remove_users_from_group_rec($gid, array($uid), false, false);

		// delete all brothers from the current group
		$user_brothers = new object_list(array(
			"parent" => $grp_o->id(),
			"brother_of" => $uid_o->id()
		));
		$user_brothers->delete();

		// delete alias from user to this group
		if (count($uid_o->connections_from(array("to" => $grp_o->id()))) > 0)
		{
			$uid_o->disconnect(array(
				"from" => $grp_o->id()
			));
		}

		// get all subgroups
		$ot = new object_tree(array(
			"parent" => $grp_o->id(),
			"class_id" => CL_GROUP
		));
		$ol = $ot->to_list();
		for($item = $ol->begin(); !$ol->end(); $item = $ol->next())
		{
			// remove all brothers from those groups
			$user_brothers = new object_list(array(
				"parent" => $item->id(),
				"brother_of" => $uid_o->id()
			));
			$user_brothers->delete();

			// remove all aliases from those groups
			foreach($item->connections_from(array("to" => $uid_o->id())) as $c)
			{
				$c->delete();
			}

			// also remove all aliases from user to the group
			if (count($uid_o->connections_from(array("to" => $item->id()))) > 0)
			{
				$uid_o->disconnect(array(
					"from" => $item->id()
				));
			}
		}
		$c = get_instance("cache");
		$c->file_clear_pt("acl");
	}

	function on_add_alias_to_group($arr)
	{
		if ($arr["connection"]->prop("reltype") == 2) //RELTYPE_MEMBER
		{
			// get the gid to what we must add the user
			$gid = $this->users->get_gid_for_oid($arr["connection"]->prop("from"));
			// get the uid to add
			$uid = $this->users->get_uid_for_oid($arr["connection"]->prop("to"));

			$group = $arr["connection"]->from();
			$user = $arr["connection"]->to();

			// we must also add an alias to the user object pointing to this group
			$user->connect(array(
				"to" => $group->id(),
				"reltype" => "RELTYPE_GRP" // from user
			));

			$this->users->add_users_to_group_rec($gid, array($uid), true, true, false);

			// do our own sync here
			// add a brother below this group
			$user->create_brother($group->id());

			// go over all parent groups
			foreach($group->path() as $p_o)
			{
				if ($p_o->id() == $group->id())
				{
					continue;
				}

				if ($p_o->class_id() == CL_GROUP)
				{

					// add a brother below all parent groups
					$user->create_brother($p_o->id());

					// add an alias to the user to all parent groups
					$p_o->connect(array(
						"to" => $user->id(),
						"reltype" => "RELTYPE_MEMBER",
					));

					// add a reverse alias to the user for all groups
					$user->connect(array(
						"to" => $p_o->id(),
						"reltype" => "RELTYPE_GRP" // from user
					));
				}
			}
		}
		$c = get_instance("cache");
		$c->file_clear_pt("acl");
	}

	function on_delete_grp($arr)
	{
		extract($arr);

		$gid = $this->users->get_gid_for_oid($oid);
		if ($gid)
		{
			// check if this is the user's default group and if so, block delete
			aw_disable_acl();
			$g_o = obj($oid);
			if ($g_o->prop("type") == 1)
			{
				die(t("Kasutaja vaikimisi gruppi ei saa kustutada, palun kustutage kasutaja objekt!"));
			}
			aw_restore_acl();

			$this->users->deletegroup($gid);
			$c = get_instance("cache");
			$c->file_clear_pt("acl");
		}
	}

	function callback_get_default_acl($arr)
	{
		$ret = array();

		if ($arr["obj_inst"]->class_id() != CL_RELATION)
		{
			$da = $arr["obj_inst"]->meta("default_acl");
		}
		else
		{
			// handle relation objects
			// FIXME: classbase will automatically give the connection as a parameter, but 
			// currently we do this ourselves
			
			$c = new connection();
			$tmp = $c->find(array("relobj_id" => $arr["obj_inst"]->id()));
			list(, $c_d) = each($tmp);
			$c = new connection($c_d["id"]);
			

			// now get the real acl from the connection
			$grp = $c->to();
			$acld = $this->get_acl_for_oid_gid($c->prop("from"), $grp->prop("gp_gid"));
			$aclids = aw_ini_get("acl.ids");
			$da = array();
			foreach($aclids as $aclid)
			{
				$da[$aclid] = ($acld[$aclid] == aw_ini_get("acl.allowed") ? 1 : 0);
			}
		}

		$ret["acl_INFO_TEXT"] = array(
			"name" => "acl_INFO_TEXT",
			"no_caption" => 1,
			"type" => "text",
			"store" => "no",
			"value" => t("Default &otilde;igused seose loomisel")
		);

		$aclids = aw_ini_get("acl.ids");
		$aclns = aw_ini_get("acl.names");
		foreach($aclids as $acln)
		{
			$ret["acl_".$acln] = array(
				"name" => "acl_".$acln,
				"caption" => $aclns[$acln],
				"type" => "checkbox",
				"ch_value" => 1,
				"store" => "no",
				"value" => $da[$acln]
			);
		}

		return $ret;
	}

	function on_add_alias_for_group($arr)
	{
		if ($arr["connection"]->prop("reltype") == RELTYPE_ACL)
		{
			// handle acl add
			$from = $arr["connection"]->from();
			$grp = $arr["connection"]->to();
			$gid = $grp->prop("gp_gid");
			
			$this->add_acl_group_to_obj($gid, $from->id());
			$this->save_acl($from->id(), $gid, $grp->meta("default_acl"));
		}
	}

	function on_remove_alias_for_group($arr)
	{
		if ($arr["connection"]->prop("reltype") == RELTYPE_ACL)
		{
			// handle acl add
			$from = $arr["connection"]->from();
			$grp = $arr["connection"]->to();
			$gid = $grp->prop("gp_gid");
			
			$this->remove_acl_group_from_obj($gid, $from->id());
		}
	}

	/** adds the user $user to group $group (storage objects)
		
		@attrib params=pos api=1
		@param user required type=object
		User object to be added into group
		@param group required type=object
		Group object to what the user will be added

		@comment
		Adds the $user to the $group.
	**/
	function add_user_to_group($user, $group)
	{
		// old tables
		$this->users->add_users_to_group_rec($group->prop("gid"), array($user->prop("uid")), false, true);


		// for each group in path from the to-add group
		foreach($group->path() as $p_o)
		{
			if ($p_o->class_id() != CL_GROUP)
			{
				continue;
			}

			// connection from user to group
			$user->connect(array(
				"to" => $p_o->id(),
				"reltype" => "RELTYPE_GRP",
			));

			// connection to group from user
			$p_o->connect(array(
				"to" => $user->id(),
				"reltype" => "RELTYPE_MEMBER",
			));

			// brother under group
			$user->create_brother($p_o->id());
		}
		$c = get_instance("cache");
		$c->file_clear_pt("acl");
	}

	/** removes user $user from group $group

		@attrib params=pos api=1
		@param user required type=object
		User object to be removed from $group
		@param group required type=object
		The group object from where the user is removed

		@comment
		Removes the $user from $group.

	**/
	function remove_user_from_group($user, $group)
	{
		$gid = $this->users->get_gid_for_oid($group->id());
		$uid = $this->users->get_uid_for_oid($user->id());

		// remove user from group rec
		$this->users->remove_users_from_group_rec($gid, array($uid), false, false);

		// delete all brothers from the current group
		$user_brothers = new object_list(array(
			"parent" => $group->id(),
			"brother_of" => $user->id()
		));
		$user_brothers->delete();

		// delete alias from user to this group
		if (count($user->connections_from(array("to" => $group->id()))) > 0)
		{
			$user->disconnect(array(
				"to" => $group->id()
			));
		}

		// get all subgroups
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

			// remove all aliases from those groups
			foreach($item->connections_from(array("to" => $user->id())) as $c)
			{
				$c->delete();
			}

			// also remove all aliases from user to the group
			if (count($user->connections_from(array("to" => $item->id()))) > 0)
			{
				$user->disconnect(array(
					"from" => $item->id()
				));
			}
		}
		$c = get_instance("cache");
		$c->file_clear_pt("acl");
	}

	/** Returns an array of user objects in the given group
		@attrib api=1
		@param group required type=object
	**/
	function get_group_members($g)
	{
		$ol = new object_list(array(
			"class_id" => CL_USER,
			"parent" => $g->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		return $ol->arr();
	}
}

?>
