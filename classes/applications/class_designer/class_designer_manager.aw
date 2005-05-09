<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/class_designer_manager.aw,v 1.2 2005/05/09 09:34:46 kristo Exp $
// class_designer_manager.aw - Klasside brauser 
/*

@classinfo syslog_type=ST_CLASS_DESIGNER_MANAGER relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@default group=mgr

	@property mgr_tb type=toolbar no_caption=1

	@layout mgr_hbox type=hbox width=20%:80%

	@property mgr_tree type=treeview no_caption=1 parent=mgr_hbox
	@property mgr_tbl type=table no_caption=1 parent=mgr_hbox

@default group=rels

	@property rels_tb type=toolbar no_caption=1

	@layout rels_hbox type=hbox width=20%:80%

	@property rels_tree type=treeview no_caption=1 parent=rels_hbox
	@property rels_tbl type=table no_caption=1 parent=rels_hbox

@groupinfo mgr caption="Manager" submit=no
@groupinfo rels caption="Seosed" submit=no
*/

class class_designer_manager extends class_base
{
	function class_designer_manager()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/class_designer_manager",
			"clid" => CL_CLASS_DESIGNER_MANAGER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "mgr_tb":
				$this->_mgr_tb($arr);
				break;

			case "mgr_tree":
				$this->_mgr_tree($arr);
				break;

			case "mgr_tbl":
				$this->_mgr_tbl($arr);
				break;

			case "rels_tb":
				$this->_rels_tb($arr);
				break;

			case "rels_tree":
				$this->_mgr_tree($arr);
				break;

			case "rels_tbl":
				$this->_rels_tbl($arr);
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
	}

	function _mgr_tb($arr)
	{
		$t =& $arr["prop"]["toolbar"];

		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"caption" => t('Lisa'),
			"url" => html::get_new_url(
				CL_CLASS_DESIGNER, 
				$arr["obj_inst"]->id(), 
				array(
					"return_url" => get_ru(),
					"register_under" => $_GET["tf"]
				)
			)
		));
	}

	function _mgr_tree($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$t->start_tree(array(
			"tree_id" => "class_mgr_tree",
			"persist_state" => true,
			"type" => TREE_DHTML
		));

		$clsf = aw_ini_get("classfolders");
		foreach($clsf as $id => $inf)
		{
			$t->add_item((int)$inf["parent"], array(
				"name" => $arr["request"]["tf"] == $id ? "<b>".$inf["name"]."</b>" : $inf["name"],
				"id" => $id,
				"url" => aw_url_change_var("tf", $id)
			));
		}
	}

	function _init_mgr_tree(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "add",
			"caption" => t("Lisa"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "design",
			"caption" => t("Disaini"),
			"align" => "center"
		));
	}

	function _mgr_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_mgr_tree($t);

		$ol = new object_list(array(
			"class_id" => CL_CLASS_DESIGNER,
			"lang_id" => array(),
			"site_id" => array()
		));
		$designed = array();
		foreach($ol->arr() as $designer)
		{
			$designed[$designer->prop("reg_class_id")] = $designer->id();
		}

		$tf = $arr["request"]["tf"];
		$clss = aw_ini_get("classes");
		foreach($clss as $clid => $cld)
		{
			$show = false;
			if ($cld["parents"] == "" && !$tf)
			{
				$show = true;
			}
			else
			{
				$parents = $this->make_keys(explode(",", $cld["parents"]));
				if ($parents[$tf])
				{
					$show = true;
				}
			}

			if (!$show)
			{
				continue;
			}

			$design = "";
			if ($designed[$clid])
			{
				$design = html::get_change_url($designed[$clid], array("return_url" => get_ru()), "Disaini");
			}

			$t->define_data(array(
				"name" => $cld["name"],
				"add" => html::get_new_url($clid, $arr["obj_inst"]->parent(), array("return_url" => get_ru()), t("Lisa objekt")),
				"design" => $design
			));
		}
	}

	function _rels_tb($arr)
	{
		$t =& $arr["prop"]["toolbar"];

		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"caption" => t('Lisa'),
			"url" => html::get_new_url(
				CL_CLASS_DESIGNER, 
				$arr["obj_inst"]->id(), 
				array(
					"return_url" => get_ru(),
					"register_under" => $_GET["tf"]
				)
			)
		));
	}

	function _init_rels_tree(&$t)
	{
		$t->define_field(array(
			"name" => "class_name",
			"caption" => t("Klassi nimi"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "rel_name",
			"caption" => t("Seose nimi"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "rel_to",
			"caption" => t("Seos klassiga"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Vali"),
			"align" => "center",
		));
	}

	function _rels_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_rels_tree($t);

		$ol = new object_list(array(
			"class_id" => CL_CLASS_DESIGNER,
			"lang_id" => array(),
			"site_id" => array()
		));
		$designed = array();
		foreach($ol->arr() as $designer)
		{
			$designed[$designer->prop("reg_class_id")] = $designer->id();
		}

		$tf = $arr["request"]["tf"];
		$clss = aw_ini_get("classes");
		foreach($clss as $clid => $cld)
		{
			$show = false;
			if ($cld["parents"] == "" && !$tf)
			{
				$show = true;
			}
			else
			{
				$parents = $this->make_keys(explode(",", $cld["parents"]));
				if ($parents[$tf])
				{
					$show = true;
				}
			}

			if (!$show)
			{
				continue;
			}

			$sel = "";
			if ($designed[$clid])
			{
				$sel = html::get_change_url(
					$designed[$clid], 
					array(
						"return_url" => get_ru(),
						"group" => "relations",
						"relations_mgr" => "new"
					),
					t("Lisa seos")
				);
			}

			$t->define_data(array(
				"class_name" => $cld["name"],
				"rel_name" => "",
				"rel_to" => "",
				"sel" => $sel
			));

			// rels for class
			if ($designed[$clid])
			{
				$rels = array();
				$d_o = obj($designed[$clid]);
				foreach($d_o->connections_from(array("reltype" => "RELTYPE_RELATION")) as $c)
				{
					$rel_o = $c->to();
					$rels[] = array(
						"caption" => $rel_o->name(),
						"clid" => $rel_o->prop("r_class_id")
					);
				}
			}
			else
			{
				$cu = get_instance("cfg/cfgutils");
				$ps = $cu->load_properties(array("clid" => $clid, "file" => basename($cld["file"])));
				$rels = $cu->get_relinfo();
			}
			foreach($rels as $rel)
			{
				$rel_to = array();
				foreach(safe_array($rel["clid"]) as $r_clid)
				{
					$rel_to[] = $clss[$r_clid]["name"];
				}

				$t->define_data(array(
					"class_name" => "",
					"rel_name" => $rel["caption"],
					"rel_to" => join(", ", $rel_to),
					"sel" => ""
				));
			}
		}
		$t->set_sortable(false);
	}
}
?>
