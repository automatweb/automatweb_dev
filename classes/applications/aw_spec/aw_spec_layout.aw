<?php
/*
@classinfo syslog_type=ST_AW_SPEC_LAYOUT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_spec_layouts master_index=brother_of  master_table=objects index=aw_oid

@default table=aw_spec_layouts
@default group=general

@property layout_type type=select field=aw_layout_type
@caption Layoudi t&uuml;&uuml;p

@property parent_layout_name type=relpicker field=aw_parent_layout_name reltype=RELTYPE_PARENT_LAYOUT
@caption Parent layout


@reltype PARENT_LAYOUT value=1 clid=CL_AW_SPEC_LAYOUT
@caption Parent layout
*/

class aw_spec_layout extends class_base
{
	function aw_spec_layout()
	{
		$this->init(array(
			"tpldir" => "applications/aw_spec/aw_spec_layout",
			"clid" => CL_AW_SPEC_LAYOUT
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_spec_layouts(aw_oid int primary key, aw_layout_type varchar(255), aw_parent_layout_name int)");
			return true;
		}
	}

	function _get_layout_type($arr)
	{
		$arr["prop"]["options"] = aw_spec_group_obj::spec_layout_type_picker();
	}

	function get_embed_prop($o, $arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_table($t);
		$t->set_caption(sprintf(t("Sisesta klassi %s grupi %s layoudi %s omadused"), obj(obj($o->parent())->parent())->name(), obj($o->parent())->name(), $o->name()));

		$data = $o->spec_property_list();
		$data[-1] = obj();
		$data[-2] = obj();
		$data[-3] = obj();
		$data[-4] = obj();
		$data[-5] = obj();

		$prop_type_picker = $o->spec_prop_type_picker();	

		foreach($data as $idx => $g_obj)
		{
			$t->define_data(array(
				"layout_name" => html::textbox(array(
					"name" => "gp_data[".$idx."][prop_name]",
					"value" => $g_obj->name(),
				)),
				"layout_type" => html::select(array(
					"name" => "gp_data[".$idx."][prop_type]",
					"value" => $g_obj->prop("prop_type"),
					"options" => $prop_type_picker
				)),
				"layout_desc" => html::textarea(array(
					"name" => "gp_data[".$idx."][prop_desc]",
					"value" => $g_obj->prop("prop_desc"),
					"rows" => 3,
					"cols" => 30
				)),
			));
		}
	}

	private function _init_table($t)
	{
		$t->define_field(array(
			"name" => "layout_name",
			"caption" => t("Omaduse nimi"),
		));
		$t->define_field(array(
			"name" => "layout_type",
			"caption" => t("Omaduse t&uuml;&uuml;p"),
		));
		$t->define_field(array(
			"name" => "layout_desc",
			"caption" => t("Omaduse kirjeldus"),
		));
		$t->set_sortable(false);
	}

	function set_embed_prop($o, $arr)
	{
		$o->set_spec_property_list($arr["request"]["gp_data"]);
	}

	function get_overview($o, $t, $prnt_num)
	{
		$prop_type_picker = $o->spec_prop_type_picker();	

		$num = 0;
		foreach($o->spec_property_list() as $idx => $g_obj)
		{
			$np = aw_spec::format_chapter_num($prnt_num, ++$num);

			$str .= aw_spec::format_doc_entry(
				$np,
				sprintf(t("Omadus: %s"), $g_obj->name()),
				sprintf(t("T&uuml;&uuml;p: %s<br>"), $prop_type_picker[$g_obj->prop("prop_type")]).nl2br($g_obj->prop("prop_desc"))
			);
		}
		return $str;
	}
}

?>