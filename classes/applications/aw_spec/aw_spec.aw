<?php
/*
@classinfo syslog_type=ST_AW_SPEC relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@default group=spec

	@property spec_tb type=toolbar no_caption=1 store=no
	
	@layout v_split type=hbox width=20%:80%
		
		@layout tree_border type=vbox parent=v_split area_caption=Sisukord closeable=1

			@property spec_tree type=treeview store=no no_caption=1 parent=tree_border

		@property class_list type=table store=no no_caption=1 parent=v_split
		@property relation_list type=table store=no no_caption=1 parent=v_split
		@property group_list type=table store=no no_caption=1 parent=v_split
		@property prop_list type=table store=no no_caption=1 parent=v_split
		
		@layout spec_border type=vbox parent=v_split area_caption=Sisu closeable=1

			@property spec_editor type=textarea rows=30 cols=80 richtext=1 store=no no_caption=1 parent=spec_border
			@property spec_overview type=text store=no no_caption=1 parent=spec_border

		
@default group=spec_view

	@property view_tb type=toolbar no_caption=1 store=no
	@property view_ct type=text no_caption=1 store=no

@groupinfo spec caption="Koosta" 
@groupinfo spec_view caption="&Uuml;levaade" 
*/

class aw_spec extends class_base
{
	function aw_spec()
	{
		$this->init(array(
			"tpldir" => "applications/aw_spec/aw_spec",
			"clid" => CL_AW_SPEC
		));

		$this->tree_struct = array(
			array(0, "intro", t("Sissejuhatus"), "spec_editor"),
				array("intro", "intro_whom", t("Kellele"), "spec_editor"),
				array("intro", "intro_why", t("Miks"), "spec_editor"),
			array(0, "conf", t("Seadistatavus")),
				array("conf", "conf_what", t("Mida seadistada"), "spec_editor"),
				array("conf", "intro_how", t("Kuidas"), "spec_editor"),
				array("conf", "intro_who", t("Kes"), "spec_editor"),
			array(0, "classes", t("Klassid")),
				array("classes", "classes_who", t("Kellele"), "spec_editor"),
				array("classes", "classe_why", t("Miks"), "spec_editor"),
				array("classes", "classes_classes", t("Klassid"), "class_list"),
				array("classes", "classes_rels", t("Seosed"), "relation_list"),
				array("classes", "classes_ui", t("Kasutajaliides"), "spec_editor"),
			array(0, "groups", t("Omaduste grupid"), "group_list"),
			array(0, "props", t("Omaduste loetelu"), "prop_list"),
			array(0, "prev", t("Eeskujud"), "spec_editor"),
			array(0, "bl", t("&Auml;riloogika"), "spec_overview"),
				array("bl", "classes_ucase", t("Kasutajalood"), "spec_editor"),
				array("bl", "classes_principles", t("S&uuml;steemi toimimisp&otilde;him&otilde;tted"), "spec_editor"),
				array("bl", "classes_examples", t("N&auml;ited"), "spec_editor"),
		);
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["disp"] = $_GET["disp"];
	}

	function callback_mod_retval($arr)
	{
		$arr["args"]["disp"] = $arr["request"]["disp"];
	}

	function _get_spec_tb($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
	}

	function _get_spec_tree($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		
		$disp = $this->_get_disp($arr);

		foreach($this->tree_struct as $item)
		{
			$t->add_item($item[0], array(
				"id" => $item[1],
				"url" => aw_url_change_var("disp", $item[1]),
				"name" => $disp == $item[1] ? "<b>".$item[2]."</b>" : $item[2]
			));
		}
	}

	function _init_class_list_table($t)
	{
		$t->define_field(array(
			"name" => "class_name",
			"caption" => t("Klassi nimi"),
		));
		$t->define_field(array(
			"name" => "class_desc",
			"caption" => t("Klassi kirjeldus"),
		));
	}

	function _get_class_list($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}

		$disp = $this->_get_disp($arr);

		$t = $arr["prop"]["vcl_inst"];
		$this->_init_class_list_table($t);
		$data = $arr["obj_inst"]->meta($disp);
		$data[] = array();
		$data[] = array();
		$data[] = array();
		$data[] = array();
		$data[] = array();
		foreach($data as $idx => $dr)
		{
			$t->define_data(array(
				"class_desc" => html::textarea(array(
					"name" => "class_list[".$idx."][class_desc]",
					"value" => $dr["class_desc"],
					"rows" => 5,
					"cols" => 60
				)),
				"class_name" => html::textbox(array(
					"name" => "class_list[".$idx."][class_name]",
					"value" => $dr["class_name"],
				)),
			));
		}
		$t->set_sortable(false);
	}

	function _set_class_list($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}
		$disp = $this->_get_disp($arr);
		$v = array();
		foreach(safe_array($arr["request"]["class_list"]) as $idx => $row)
		{
			if ($row["class_name"] != "")
			{
				$v[$idx] = $row;
			}
		}
		$arr["obj_inst"]->set_meta($disp, $v);
	}

	function _get_spec_editor($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}

		$arr["prop"]["value"] = $arr["obj_inst"]->meta($this->_get_disp($arr));
	}

	function _set_spec_editor($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}
		$arr["obj_inst"]->set_meta($this->_get_disp($arr), $arr["request"]["spec_editor"]);
	}

	function _get_spec_overview($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}

		$disp = $this->_get_disp($arr);
		$vals = array();
		foreach($this->tree_struct as $item)
		{
			if ((string)$item[0] == (string)$disp)
			{
				$vals[] = $item;
			}
		}

		$str = "";
		foreach($vals as $val)
		{
			$str .= "<b>".$val[2]."</b><br>";
			$str .= $arr["obj_inst"]->meta($val[1]);
			$str .= "<br><hr><br>";
		}
		$arr["prop"]["value"] = $str;
	}

	private function _get_disp($arr)
	{
		$rv = $arr["request"]["disp"];
		if (!$rv)
		{
			foreach($this->tree_struct as $item)
			{
				if (isset($item[3]))
				{
					return $item[1];
				}
			}
		}
		return $rv;
	}

	private function _get_row_data($arr)
	{
		$disp = $this->_get_disp($arr);
		foreach($this->tree_struct as $item)
		{
			if ($item[1] == $disp)
			{
				return $item;
			}
		}
		throw aw_exception("no row found for disp $disp!");
	}

	private function _is_visible($arr)
	{
		$rd = $this->_get_row_data($arr);
		if ($rd[3] != $arr["prop"]["name"])
		{
			return false;
		}
		return true;
	}

	function _init_relation_list_table($t)
	{
		$t->define_field(array(
			"name" => "rel_from",
			"caption" => t("Seos kust"),
		));
		$t->define_field(array(
			"name" => "rel_name",
			"caption" => t("Seose nimi"),
		));
		$t->define_field(array(
			"name" => "rel_to",
			"caption" => t("Seos kuhu"),
		));
	}

	function _get_relation_list($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}
		
		$disp = $this->_get_disp($arr);

		$t = $arr["prop"]["vcl_inst"];
		$this->_init_relation_list_table($t);

		$class_picker = $this->_get_class_picker($arr["obj_inst"]);		

		$data = $arr["obj_inst"]->meta($disp);
		$data[] = array();
		$data[] = array();
		$data[] = array();
		$data[] = array();
		$data[] = array();
		foreach($data as $idx => $dr)
		{
			$t->define_data(array(
				"rel_from" => html::select(array(
					"name" => "rel_data[".$idx."][rel_from]",
					"value" => $dr["rel_from"],
					"options" => $class_picker
				)),
				"rel_name" => html::textbox(array(
					"name" => "rel_data[".$idx."][rel_name]",
					"value" => $dr["rel_name"],
				)),
				"rel_to" => html::select(array(
					"name" => "rel_data[".$idx."][rel_to]",
					"value" => $dr["rel_to"],
					"options" => $class_picker
				)),
			));
		}
		$t->set_sortable(false);
	}

	function _set_relation_list($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}
		$disp = $this->_get_disp($arr);
		$v = array();
		foreach(safe_array($arr["request"]["rel_data"]) as $idx => $row)
		{
			if ($row["rel_name"] != "")
			{
				$v[$idx] = $row;
			}
		}
		$arr["obj_inst"]->set_meta($disp, $v);
	}

	private function _get_class_picker($o)
	{
		$clss = aw_ini_get("classes");
		$rv = array("" => t("--vali--"));
		foreach($clss as $clid => $cle)
		{
			$rv[$clid] = $cle["name"];
		}
		foreach(safe_array($o->meta("classes_classes")) as $idx => $row)
		{
			$rv["new_".$idx] = $row["class_name"];
		}
		return $rv;
	}

	private function _init_group_list_table($t)
	{	
		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
		));
		$t->define_field(array(
			"name" => "group_name",
			"caption" => t("Grupi nimi"),
		));
		$t->define_field(array(
			"name" => "parent_group_name",
			"caption" => t("Parent grupp"),
		));
	}

	function _get_group_list($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}
		
		$disp = $this->_get_disp($arr);

		$t = $arr["prop"]["vcl_inst"];
		$this->_init_group_list_table($t);

		$group_picker = $this->_get_group_picker($arr["obj_inst"]);		
		$class_picker = array("" => t("--vali--"));
		foreach(safe_array($arr["obj_inst"]->meta("classes_classes")) as $idx => $row)
		{
			$class_picker["new_".$idx] = $row["class_name"];
		}

		$data = $arr["obj_inst"]->meta($disp);
		$data[] = array();
		$data[] = array();
		$data[] = array();
		$data[] = array();
		$data[] = array();
		foreach($data as $idx => $dr)
		{
			$t->define_data(array(
				"class" => html::select(array(
					"name" => "gp_data[".$idx."][class]",
					"value" => $dr["class"],
					"options" => $class_picker
				)),
				"group_name" => html::textbox(array(
					"name" => "gp_data[".$idx."][group_name]",
					"value" => $dr["group_name"],
				)),
				"parent_group_name" => html::select(array(
					"name" => "gp_data[".$idx."][parent_group_name]",
					"value" => $dr["parent_group_name"],
					"options" => $group_picker
				)),
			));
		}
		$t->set_sortable(false);
	}

	function _set_group_list($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}
		$disp = $this->_get_disp($arr);
		$v = array();
		foreach(safe_array($arr["request"]["gp_data"]) as $idx => $row)
		{
			if ($row["group_name"] != "")
			{
				$v[$idx] = $row;
			}
		}
		$arr["obj_inst"]->set_meta($disp, $v);
	}

	private function _get_group_picker($o)
	{
		$rv = array("" => t("--vali--"));
		foreach(safe_array($o->meta("groups")) as $idx => $row)
		{
			$rv[$idx] = $row["group_name"];
		}
		return $rv;
	}

	private function _init_prop_list_table($t)
	{
		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
		));
		$t->define_field(array(
			"name" => "group_name",
			"caption" => t("Grupp"),
		));
		$t->define_field(array(
			"name" => "prop_name",
			"caption" => t("Omadus"),
		));
		$t->define_field(array(
			"name" => "prop_type",
			"caption" => t("T&uuml;&uuml;p"),
		));
		$t->define_field(array(
			"name" => "prop_desc",
			"caption" => t("Kirjeldus"),
		));
	}

	private function _get_type_picker()
	{
		return array(
			"" => t("--vali--"),
			"textbox" => t("Textbox"),
			"textarea" => t("Textarea"),
			"treeview" => t("Puu"),
			"table" => t("Tabel"),
			"relpicker" => t("Relpicker"),
			"toolbar" => t("Toolbar")
		);
	}

	function _get_prop_list($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}
		
		$disp = $this->_get_disp($arr);

		$t = $arr["prop"]["vcl_inst"];
		$this->_init_prop_list_table($t);

		$group_picker = $this->_get_group_picker($arr["obj_inst"]);		
		$class_picker = array("" => t("--vali--"));
		foreach(safe_array($arr["obj_inst"]->meta("classes_classes")) as $idx => $row)
		{
			$class_picker["new_".$idx] = $row["class_name"];
		}

		$type_picker = $this->_get_type_picker();

		$data = $arr["obj_inst"]->meta($disp);
		$data[] = array();
		$data[] = array();
		$data[] = array();
		$data[] = array();
		$data[] = array();
		foreach($data as $idx => $dr)
		{
			$t->define_data(array(
				"class" => html::select(array(
					"name" => "gp_data[".$idx."][class]",
					"value" => $dr["class"],
					"options" => $class_picker
				)),
				"group_name" => html::select(array(
					"name" => "gp_data[".$idx."][group]",
					"value" => $dr["group"],
					"options" => $group_picker
				)),
				"prop_name" => html::textbox(array(
					"name" => "gp_data[".$idx."][prop_name]",
					"value" => $dr["prop_name"],
				)),
				"prop_type" => html::select(array(
					"name" => "gp_data[".$idx."][type]",
					"value" => $dr["type"],
					"options" => $type_picker
				)),
				"prop_desc" => html::textarea(array(
					"name" => "gp_data[".$idx."][prop_desc]",
					"value" => $dr["prop_desc"],
					"rows" => 4,
					"cols" => 20
				)),
			));
		}
		$t->set_sortable(false);
	}

	function _set_prop_list($arr)
	{
		if (!$this->_is_visible($arr))
		{
			return PROP_IGNORE;
		}
		$disp = $this->_get_disp($arr);
		$v = array();
		foreach(safe_array($arr["request"]["gp_data"]) as $idx => $row)
		{
			if ($row["prop_name"] != "")
			{
				$v[$idx] = $row;
			}
		}
		$arr["obj_inst"]->set_meta($disp, $v);
	}

	function _get_view_tb($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "pdf",
			"url" => $this->mk_my_orb("get_pdf", array("id" => $arr["obj_inst"]->id())),
			"image" => "pdf.gif",
			"tooltip" => t("PDF")
		));
	}

	/**
		@attrib name=get_pdf
		@param id required
	**/
	function get_pdf($arr)
	{
		$str = $this->_get_overview(obj($arr["id"]));
		header("Content-type: application/pdf");
		die(get_instance("html2pdf")->convert(array("source" => $str)));
	}

	function _get_overview($o)
	{
		$str = "";
		foreach($this->tree_struct as $val)
		{
			if (!isset($val[3]))
			{
				continue;
			}
			$fn = "_get_ovr_".$val[3];
			$tmp = $this->$fn($o, $val);		
			if ($tmp === false)
			{
				continue;
			}
			$str .= "<b>".$val[2]."</b><br>";
			$str .= $tmp;
			$str .= "<br><hr><br>";
		}

		return $str;
	}

	function _get_view_ct($arr)
	{
		$arr["prop"]["value"] = $this->_get_overview($arr["obj_inst"]);
	}

	function _get_ovr_spec_overview($o, $val)
	{
		return null;
	}

	function _get_ovr_spec_editor($o, $val)
	{
		return $o->meta($val[1]);
	}

	function _get_ovr_class_list($o, $val)
	{
		$t = new aw_table();
		$this->_init_class_list_table($t);
		foreach(safe_array($o->meta($val[1])) as $row)
		{
			$t->define_data($row);
		}
		return $t->draw();
	}

	function _get_ovr_relation_list($o, $val)
	{
		$t = new aw_table();
		$this->_init_relation_list_table($t);
		$class_picker = $this->_get_class_picker($o);		
		foreach(safe_array($o->meta($val[1])) as $row)
		{
			$t->define_data(array(
				"rel_from" => $class_picker[$row["rel_from"]],
				"rel_name" => $row["rel_name"],
				"rel_to" => $class_picker[$row["rel_to"]],
			));
		}
		return $t->draw();
	}

	function _get_ovr_group_list($o, $val)
	{
		$t = new aw_table();
		$this->_init_group_list_table($t);
		$group_picker = $this->_get_group_picker($o);		
		$class_picker = array("" => t("--vali--"));
		foreach(safe_array($o->meta("classes_classes")) as $idx => $row)
		{
			$class_picker["new_".$idx] = $row["class_name"];
		}
		foreach(safe_array($o->meta($val[1])) as $row)
		{
			$t->define_data(array(
				"class" => $class_picker[$row["class"]],
				"group_name" => $row["group_name"],
				"parent_group_name" => $group_picker[$row["parent_group_name"]],
			));
		}
		return $t->draw();
	}

	function _get_ovr_prop_list($o, $val)
	{
		$t = new aw_table();
		$this->_init_prop_list_table($t);
		$group_picker = $this->_get_group_picker($o);		
		$class_picker = array("" => t("--vali--"));
		foreach(safe_array($o->meta("classes_classes")) as $idx => $row)
		{
			$class_picker["new_".$idx] = $row["class_name"];
		}

		$type_picker = $this->_get_type_picker();

		foreach(safe_array($o->meta($val[1])) as $row)
		{
			$t->define_data(array(
				"class" => $class_picker[$row["class"]],
				"group" => $group_picker[$row["group"]],
				"prop_name" => $row["prop_name"],
				"prop_desc" => nl2br($row["prop_desc"]),
				"type" => $type_picker[$row["type"]],
			));
		}
		return $t->draw();
	}
}

?>