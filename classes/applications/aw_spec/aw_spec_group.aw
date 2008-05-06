<?php
/*
@classinfo syslog_type=ST_AW_SPEC_GROUP relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo

@default table=objects
@default group=general

*/

class aw_spec_group extends class_base
{
	function aw_spec_group()
	{
		$this->init(array(
			"tpldir" => "applications/aw_spec/aw_spec_group",
			"clid" => CL_AW_SPEC_GROUP
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function get_embed_prop($o, $arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_table($t);
		$t->set_caption(sprintf(t("Sisesta klassi %s grupi %s layoudid"), obj($o->parent())->name(), $o->name()));

		$data = $o->spec_layout_list();
		$data[-1] = obj();
		$data[-2] = obj();
		$data[-3] = obj();
		$data[-4] = obj();
		$data[-5] = obj();

		$layout_picker = $this->_get_layout_picker($o);	
		$layout_type_picker = $o->spec_layout_type_picker();	

		foreach($data as $idx => $g_obj)
		{
			$t->define_data(array(
				"layout_name" => html::textbox(array(
					"name" => "gp_data[".$idx."][layout_name]",
					"value" => $g_obj->name(),
				)),
				"parent_layout_name" => html::select(array(
					"name" => "gp_data[".$idx."][parent_layout_name]",
					"value" => $g_obj->prop("parent_layout_name"),
					"options" => $layout_picker
				)),
				"layout_type" => html::select(array(
					"name" => "gp_data[".$idx."][layout_type]",
					"value" => $g_obj->prop("layout_type"),
					"options" => $layout_type_picker
				)),
			));
		}
	}

	private function _init_table($t)
	{
		$t->define_field(array(
			"name" => "layout_name",
			"caption" => t("Layoudi nimi"),
		));
		$t->define_field(array(
			"name" => "layout_type",
			"caption" => t("Layoudi t&uuml;&uuml;p"),
		));
		$t->define_field(array(
			"name" => "parent_layout_name",
			"caption" => t("Layoudi parent"),
		));
		$t->set_sortable(false);
	}

	private function _get_layout_picker($o)
	{
		$rv = array("" => t("--vali--"));
		foreach($o->spec_layout_list() as $idx => $g_obj)
		{
			$rv[$idx] = $g_obj->name();
		}
		return $rv;
	}

	function set_embed_prop($o, $arr)
	{
		$o->set_spec_layout_list($arr["request"]["gp_data"]);
	}


	function get_tree_items($tree, $o, $pt, $g_pt = "")
	{
		$has_items = false;
		foreach($o->spec_layout_list() as $cl_oid => $cl)
		{
			$has_cb = false;
			$t = obj($cl_oid);
			if (method_exists($t->instance(), "get_tree_items"))
			{
				$has_cb = true;
			}
			if ((int)$cl->prop("parent_layout_name") == (int)$g_pt)
			{
				$has_items = true;
				$id = $pt."_".$cl_oid;
				$tree->add_item($pt, array(
					"id" => $id,
					"url" => aw_url_change_var("disp2", $cl_oid),
					"name" => $_GET["disp2"] == $cl_oid ? "<b>".$cl->name()."</b>" : $cl->name()
				));

				if (!$this->get_tree_items($tree, $o, $id, $cl_oid))
				{
					if ($has_cb)
					{
						$t->instance()->get_tree_items($tree, $t, $id);
					}
				}
			}
		}
		return $has_items;
	}

	function get_overview($o, $t)
	{
		$t = new vcl_table();
		$this->_init_table($t);
		$type_picker = $o->spec_layout_type_picker();
		$layout_picker = $this->_get_layout_picker($o);	

		$rows = false;
		foreach($o->spec_layout_list() as $idx => $g_obj)
		{
			$t->define_data(array(
				"layout_name" => $g_obj->name(),
				"parent_layout_name" => $layout_picker[$g_obj->prop("parent_layout_name")],
				"layout_type" => $layout_type_picker[$g_obj->prop("layout_type")],
			));

			$t->define_data(array(
				"parent_layout_name" => $g_obj->instance()->get_overview($g_obj, $t)
			));
			$rows = true;
		}
		if (!$rows)
		{
			return null;
		}
		$t->set_sortable(false);
		return $t->draw();
	}
}

?>
