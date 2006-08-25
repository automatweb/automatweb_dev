<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/customer_satisfaction_center/user_bookmarks.aw,v 1.1 2006/08/25 09:47:10 kristo Exp $
// user_bookmarks.aw - Kasutaja j&auml;rjehoidjad 
/*

@classinfo syslog_type=ST_USER_BOOKMARKS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=bms

	@property bm_tb type=toolbar store=no no_caption=1 

	@layout bm_tt type=hbox width=30%:70% closeable=1

		@property bm_tree type=treeview store=no no_caption=1 parent=bm_tt

		@property bm_table type=table store=no no_caption=1 parent=bm_tt

@groupinfo bms caption="J&auml;rjehoidja" submit=no
*/

class user_bookmarks extends class_base
{
	function user_bookmarks()
	{
		$this->init(array(
			"tpldir" => "applications/customer_satisfaction_center/user_bookmarks",
			"clid" => CL_USER_BOOKMARKS
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "bm_tb":
				$this->_bm_tb($arr);
				break;

			case "bm_tree":
				$this->_bm_tree($arr);
				break;

			case "bm_table":
				$this->_bm_table($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["objs"] = 0;
		$arr["tf"] = $_GET["tf"];
	}

	function init_bm()
	{
		$ol = new object_list(array(
			"class_id" => CL_USER_BOOKMARKS,
			"lang_id" => array(),
			"site_id" => array()
		));
		if (!$ol->count())
		{
			$o = obj();
			$o->set_class_id(CL_USER_BOOKMARKS);
			$o->set_parent(aw_ini_get("amenustart"));
			$p = get_current_person();
			$o->set_name(sprintf(t("%s j&auml;rjehoidja"), $p->name()));
			$o->save();
			return $o;
		}
		else
		{
			return $ol->begin();
		}
	}

	function _bm_tb($arr)
	{
		$pt = isset($arr["request"]["tf"]) ? $arr["request"]["tf"] : $arr["obj_inst"]->id();
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "new",
			"tooltip" => t("Uus"),
		));
		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("Kataloog"),
			"link" => html::get_new_url(CL_MENU, $pt, array("return_url" => get_ru()))
		));		
		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("Link"),
			"link" => html::get_new_url(CL_EXTLINK, $pt, array("return_url" => get_ru()))
		));		

		$ps = get_instance("vcl/popup_search");
		$tb->add_cdata($ps->get_popup_search_link(array(
			"pn" => "objs",
		)));

		$tb->add_button(array(
			"name" => "saveb",
			"action" => "save_bms",
			"img" => "save.gif",
			"tooltip" => t("Salvesta")
		));
		$tb->add_button(array(
			"name" => "delb",
			"action" => "delete_bms",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta")
		));
	}

	/**
		@attrib name=save_bms
	**/
	function save_bms($arr)
	{
		$o = obj($arr["id"]);
		$mt = $o->meta("grp_sets");
		foreach(safe_array($arr["grps"]) as $oid => $gp)
		{
			$mt[$oid] = $gp;
		}
		$o->set_meta("grp_sets", $mt);
		$o->save();
		return $arr["post_ru"];
	}

	/**
		@attrib name=delete_bms
	**/
	function delete_bms($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	function _bm_tree($arr)
	{	
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "user_bm",
			),
			"root_item" => $arr["obj_inst"],
			"ot" => new object_tree(array(
				"parent" => $arr["obj_inst"]->id(),
				"lang_id" => array(),
				"site_id" => array(),
				"class_id" => CL_MENU
			)),
			"var" => "tf",
			"icon" => icons::get_icon_url(CL_MENU)
		));
	}
	
	function _init_bm_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Objekti nimi"),
			"align" => "center"
		));
/*		$t->define_field(array(
			"name" => "link",
			"caption" => t("Link"),
			"align" => "center"
		));*/
		$t->define_field(array(
			"name" => "group",
			"caption" => t("Grupp"),
			"align" => "center"
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _bm_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bm_table($t);

		$pt = isset($arr["request"]["tf"]) ? $arr["request"]["tf"] : $arr["obj_inst"]->id();
		$ol = new object_list(array(
			"parent" => $pt,
			"lang_id" => array(),
			"site_id" => array()
		));
		$mt = $arr["obj_inst"]->meta("grp_sets");
		foreach($ol->arr() as $o)
		{
			$link = "";
			$grp = "";
			if ($o->class_id() == CL_EXTLINK)
			{
				$link = $o->prop("url");
			}
			else
			if ($o->class_id() != CL_MENU)
			{
				$tmp = obj();
				$tmp->set_class_id($o->class_id());
				$gl = $tmp->get_group_list();
				$inf = array();	
				foreach($gl as $nm => $dat)
				{
					$inf[$nm] = $dat["caption"];
				}
				$grp = html::select(array(
					"options" => $inf,
					"name" => "grps[".$o->id()."]",
					"value" => $mt[$o->id()]
				));
			}
			$t->define_data(array(
				"name" => html::obj_change_url($o),
				"oid" => $o->id(),
				"link" => $link,
				"group" => $grp
			));
		}
	}

	/**
		@attrib name=pm_lod
		@param url optional 
	**/
	function pm_lod($arr)
	{
		$bm = $this->init_bm();

		$pm = get_instance("vcl/popup_menu");
		$pm->begin_menu("wallabi");
		$pm->add_item(array(
			"text" => t("Pane j&auml;rjehoidjasse"),
			"link" => $this->mk_my_orb("add_to_bm", array("url" => $arr["url"]))
		));
		$pm->add_item(array(
			"text" => t("Eemalda j&auml;rjehoidjast"),
			"link" => $this->mk_my_orb("remove_from_bm", array("url" => $arr["url"]))
		));
		$pm->add_item(array(
			"text" => t("Toimeta j&auml;rjehoidjat"),
			"link" => html::get_change_url($bm->id(), array("return_url" => $arr["url"], "group" => "bms"))
		));
		$pm->add_separator();

		// now, add items from the bum
		$ot = new object_tree(array(
			"parent" => $bm->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		$list = $ot->to_list();
		$mt = $bm->meta("grp_sets");
		foreach($list->arr() as $li)
		{
			$pt = null;
			if ($li->parent() != $bm->id())
			{
				$pt = "mn".$li->parent();
			}
			if ($li->class_id() == CL_MENU)
			{
				$pm->add_sub_menu(array(
					"name" => "mn".$li->id(),
					"text" => $li->name()
				));
			}
			else
			if ($li->class_id() == CL_EXTLINK)
			{
				$pm->add_item(array(
					"text" => $li->name(),
					"link" => $li->prop("url"),
					"parent" => $pt
				));
			}
			else
			{
				$grp = $mt[$li->id()];
				$ga = "";
				if ($grp != "")
				{
					$gl = $li->get_group_list();
					$ga = " - ".$gl[$grp]["caption"];
				}
				$pm->add_item(array(
					"text" => $li->name().$ga,
					"link" => html::get_change_url($li->id(), array("return_url" => $arr["url"], "group" => $grp)),
					"parent" => $pt
				));
			}
		}
		die($pm->get_menu());
	}

	function callback_post_save($arr)
	{
		if ($arr["request"]["objs"] != "")
		{
			foreach(explode(",",$arr["request"]["objs"]) as $add)
			{
				$o = obj($add);
				$o->create_brother($arr["request"]["tf"] ? $arr["request"]["tf"] : $arr["obj_inst"]->id());
			}
		}
	}

	/**
		@attrib name=add_to_bm
		@param url optional
	**/
	function add_to_bm($arr)
	{
		$bm = $this->init_bm();
		$lo = obj();
		$lo->set_class_id(CL_EXTLINK);
		$lo->set_parent($bm->id());

		// parse id from url and get object and stuff
		$bits = parse_url($arr["url"]);
		$q = $bits["query"];
		parse_str($q, $td);
		if ($this->can("view", $td["id"]))
		{
			$t = obj($td["id"]);
			$nm = $t->name();
			if ($td["group"] != "")
			{
				$gl = $t->get_group_list();
				$nm .= " - ".$gl[$td["group"]]["caption"];
			}
			$lo->set_name($nm);
		}
		$lo->set_prop("url", $arr["url"]);
		$lo->save();
		return $arr["url"];
	}

	/**
		@attrib name=remove_from_bm
		@param url optional
	**/
	function remove_from_bm($arr)
	{
		$bm = $this->init_bm();
		$ot = new object_tree(array(
			"parent" => $bm->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		$list = $ot->to_list();
		foreach($list->arr() as $item)
		{
			if ($item->class_id() == CL_EXTLINK && $item->prop("url") == $arr["url"])
			{
				$item->delete();
			}
		}
		return $arr["url"];
	}
}
?>
