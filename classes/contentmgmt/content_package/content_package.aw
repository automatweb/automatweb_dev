<?php
/*
@classinfo syslog_type=ST_CONTENT_PACKAGE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=instrumental
@tableinfo aw_content_package master_index=brother_of master_table=objects index=aw_oid

@default table=aw_content_package
@default group=general

	@property cp_ug type=hidden
	@caption Kasutajagrupi OID

	@property cp_sp type=hidden
	@caption Toode OID

	@property price type=textbox size=4
	@caption Hind

	@property date_start type=date_select
	@caption Tellimisaja algus

	@property date_end type=date_select
	@caption Tellimisaja l&otilde;pp

	@property duration type=textbox size=4
	@caption Paketi kasutamise aeg p&auml;evades

@groupinfo subscribers caption=Tellijad
@default group=subscribers

	@property subscribers_tlb type=toolbar no_caption=1 store=no

	@property subscribers_tbl type=table no_caption=1 store=no

	@property subscribers_tmp type=hidden store=no

@groupinfo conditions caption=Tingimused
@default group=conditions

	@property conditions_tlb type=toolbar no_caption=1 store=no

	@property conditions_tbl type=table no_caption=1 store=no

*/

class content_package extends class_base
{
	function content_package()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/content_package/content_package",
			"clid" => CL_CONTENT_PACKAGE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "date_end":
				$prop["value"] = mktime(0, 0, 0, date("m"), date("d"), date("Y") + 1);
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "subscribers_tmp":
				if($this->can("view", $arr["obj_inst"]->cp_ug) && strlen(trim($prop["value"])))
				{
					$v = explode(",", $prop["value"]);
					$g = get_instance("group");
					$g_obj = obj($arr["obj_inst"]->cp_ug);
					foreach($v as $u)
					{
						$g->add_user_to_group(obj($u), $g_obj);
					}
				}
				break;

			case "subscribers_tbl":
				foreach($arr["request"]["subscribers_tbl"] as $id => $data)
				{
					$u = obj($id);
					$u->set_status($data["status"] == 1 ? object::STAT_ACTIVE : object::STAT_NOTACTIVE);
					$u->save();
				}
				break;

			case "conditions_tbl":
				foreach($arr["request"]["conditions_tbl"] as $id => $data)
				{
					$u = obj($id);
					$u->set_prop("price", $data["price"]);
					$u->set_prop("acl_change", isset($data["acls"]["acl_change"]) ? 1 : 0);
					$u->set_prop("acl_add", isset($data["acls"]["acl_add"]) ? 1 : 0);
					$u->set_prop("acl_admin", isset($data["acls"]["acl_admin"]) ? 1 : 0);
					$u->set_prop("acl_delete", isset($data["acls"]["acl_delete"]) ? 1 : 0);
					$u->set_prop("acl_view", isset($data["acls"]["acl_view"]) ? 1 : 0);
					$u->save();
				}
				break;
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_content_package(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "price":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "double"
				));
				return true;

			case "date_start":
			case "date_end":
			case "duration":
			case "cp_ug":
			case "cp_sp":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function _get_subscribers_tlb($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_new_button(array(CL_USER), $arr["obj_inst"]->id());
		$t->add_search_button(array(
			"pn" => "subscribers_tmp",
			"multiple" => 1,
			"clid" => CL_USER,
		));
		$t->add_save_button();
		$t->add_button(array(
			"name" => "remove_from_group",
			"tooltip" => t("Eemalda tellijad paketist"),
			"img" => "delete.gif",
			"action" => "remove_from_group",
		));
	}

	function init_subscribers_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		$t->set_caption(sprintf(t("Paketi \"%s\" tellijad", $arr["obj_inst"]->name())));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "date_start",
			"caption" => t("Kasutusaja algus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "date_end",
			"caption" => t("Kasutusaja l&otilde;pp"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "countdown",
			"caption" => t("Kasutusaega j&auml&auml;nud"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Aktiivne"),
			"align" => "center",
		));
		return $t;
	}

	function _get_subscribers_tbl($arr)
	{
		if(!$this->can("view", $arr["obj_inst"]->cp_ug))
		{
			return false;
		}

		$t = $this->init_subscribers_tbl($arr);
		foreach(get_instance("group")->get_group_members(obj($arr["obj_inst"]->cp_ug)) as $u)
		{
			$t->define_data(array(
				"oid" => $u->id(),
				"name" => $u->name(),
				"date_start" => date("Y"),
				"date_end" => date("Y"),
				"countdown" => 2,
				"status" => html::checkbox(array(
					"name" => "subscribers_tbl[".$u->id()."][status]",
					"value" => 1,
					"checked" => $u->status() == object::STAT_ACTIVE,
				)).html::hidden(array(
					"name" => "subscribers_tbl[".$u->id()."][old_status]",
					"value" => $u->status(),
				)),
			));
		}
	}

	/**
		@attrib name=remove_from_group api=1 params=name

		@param id required type=oid
			The oid of the content package.

		@param sel optional type=array
			Users to be removed from the content package.

	**/
	function remove_from_group($arr)
	{
		$o = obj($arr["id"]);
		if($this->can("view", $o->cp_ug) && is_array($arr["sel"]))
		{
			$g = get_instance("group");
			$g_obj = obj($o->cp_ug);
			foreach($arr["sel"] as $u)
			{
				$g->remove_user_from_group(obj($u), $g_obj);
			}
		}
		return $arr["post_ru"];
	}

	function init_conditions_tbl($arr, $name)
	{
		$t = &$arr["prop"]["vcl_inst"];

		$t->set_caption(sprintf(t("Sisupaketi \"".$name."\" sisutingimused")));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "acl",
			"caption" => t("ACL"),
			"align" => "center",
		));
		return $t;
	}

	function _get_conditions_tbl($arr, $use_get = false)
	{
		if($use_get && !$this->can("view", $_GET["contpack"]))
		{
			return false;
		}
		elseif($use_get)
		{
			$contpack = $_GET["contpack"];
		}
		else
		{
			$contpack = $arr["obj_inst"]->id();
		}

		$t = $this->init_conditions_tbl($arr, obj($contpack)->name());
		$odl = new object_data_list(
			array(
				"class_id" => CL_CONTENT_ITEM,
				"lang_id" => array(),
				"content_package" => $contpack,
			),
			array(
				CL_CONTENT_ITEM => array("name", "price", "acl_change", "acl_add", "acl_admin", "acl_delete", "acl_view", "objects"),
			)
		);
		if($_GET["kristole"] == 1)
		{
			$d = reset($odl->arr());
			arr($d);
			arr(obj($d["oid"])->prop("objects"));
			exit;
		}
		foreach($odl->arr() as $id => $od)
		{
			$t->define_data(array(
				"oid" => $id,
				"name" => html::obj_change_url($id),
				"price" => html::textbox(array(
					"name" => "conditions_tbl[".$id."][price]",
					"value" => $od["price"],
					"size" => 4,
				)),
				"acl" => html::checkbox(array(
					"name" => "conditions_tbl[".$id."][acls][acl_change]",
					"caption" => t("M"),
					"value" => 1,
					"checked" => $od["acl_change"],
				)).html::checkbox(array(
					"name" => "conditions_tbl[".$id."][acls][acl_add]",
					"caption" => t("L"),
					"value" => 1,
					"checked" => $od["acl_add"],
				)).html::checkbox(array(
					"name" => "conditions_tbl[".$id."][acls][acl_admin]",
					"caption" => t("ACL"),
					"value" => 1,
					"checked" => $od["acl_admin"],
				)).html::checkbox(array(
					"name" => "conditions_tbl[".$id."][acls][acl_delete]",
					"caption" => t("K"),
					"value" => 1,
					"checked" => $od["acl_delete"],
				)).html::checkbox(array(
					"name" => "conditions_tbl[".$id."][acls][acl_view]",
					"caption" => t("V"),
					"value" => 1,
					"checked" => $od["acl_view"],
				)),
			));
		}
	}

	function _get_conditions_tlb($arr, $use_url = false)
	{
		$contpack = $use_url ? $_GET["contpack"] : $arr["obj_inst"]->id();
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "new",
			"tooltip" => t("Lisa uus sisutingimus"),
			"img" => "new.gif",
			"url" => $this->mk_my_orb("new", array("parent" => $arr["obj_inst"]->id(), "contpack" => $contpack, "return_url" => get_ru()), CL_CONTENT_ITEM),
		));
		$t->add_save_button();
		$t->add_delete_button();
	}

	/**

		@attrib name=update_acl_for_usergroup api=1 params=name

		@param id required type=oid/array

	**/
	function update_acl_for_usergroup($arr)
	{
		return get_instance("content_package_obj")->update_acl_for_usergroup($arr);
	}

	/**

		@attrib name=remove_acl_for_objects api=1 params=name

		@param id required type=oid/array

		@param oid required type=oid/array

	**/
	function remove_acl_for_objects($arr)
	{
		return get_instance("content_package_obj")->remove_acl_for_objects($arr);
	}

	/**

		@attrib name=add_subscriber api=1 params=name

		@param user type=oid acl=view

		@param content_package type=oid acl=view

	**/
	function add_subscriber($arr)
	{
		return get_instance("content_package_obj")->add_subscriber($arr);
	}
}

?>
