<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_tree.aw,v 1.6 2005/03/22 15:32:37 kristo Exp $
// property_tree.aw - Puu komponent 
/*

@classinfo syslog_type=ST_PROPERTY_TREE relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property no_caption type=checkbox ch_value=1 
@caption Ilma tekstita

@default group=content 

	@layout hbox1 type=hbox group=content
	@property ct_toolbar type=toolbar no_caption=1 store=no parent=hbox1

	@layout vbox1 type=vbox group=content
	@layout hbox2 type=hbox group=content parent=vbox1 

	@property ct_tree type=treeview parent=hbox2 no_caption=1
	@caption Puu

	@property ct_list type=table parent=hbox2 no_caption=1
	@caption Tabel

@groupinfo content caption="Sisu" submit=no
*/

class property_tree extends class_base
{
	function property_tree()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/property_tree",
			"clid" => CL_PROPERTY_TREE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "ct_toolbar":
				$this->_ct_toolbar($arr);
				break;

			case "ct_tree":
				$this->_ct_tree($arr);
				break;

			case "ct_list":
				$this->_ct_list($arr);
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

	function _ct_tree($arr)
	{
		$ot = new object_tree(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => CL_PROPERTY_TREE_BRANCH
		));

		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"ot" => $ot,
			"var" => "ts",
			"root_item" => $arr["obj_inst"],
			"tree_opts" => array("type" => TREE_DHTML,"persist_state" => true, "tree_id" => "_ct_tree"),
		));
	}

	function _ct_toolbar($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$t->add_button(array(
			"name" => "new",
			"url" => html::get_new_url(CL_PROPERTY_TREE_BRANCH, ($arr["request"]["ts"] ? $arr["request"]["ts"] : $arr["obj_inst"]->id() ), array(
				"return_url" => get_ru()
			)),
			"tooltip" => t("Lisa uus oks"),
			"img" => "new.gif",
		));

		$t->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"img" => "save.gif",
		));

		$t->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"confirm" => t("Oled kindel et soovid valitud objekte kustutada?"),
			"action" => "ct_del",
			"img" => "delete.gif",
		));
	}

	function _init_ct_list_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _ct_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_ct_list_t($t);

		$pt = is_oid($arr["request"]["ts"]) ? $arr["request"]["ts"] : $arr["obj_inst"]->id();
		$ol = new object_list(array(
			"parent" => $pt,
			"class_id" => CL_PROPERTY_TREE_BRANCH
		));
		$t->data_from_ol($ol);
	}

	function get_visualizer_prop($el, &$pd)
	{
		// do the damn tree magic 
		$tv = get_instance(CL_TREEVIEW);

		$tree_opts = array(
			"root_url" => aw_global_get("REQUEST_URI"),	
			"type" => TREE_DHTML,
			"tree_id" => "vist".$el->id(),
			"persist_state" => true,
		);

		$tv->start_tree($tree_opts);

		$ic = get_instance("core/icons");

		$var = "demot[".$el->id()."]";

		$ot = new object_tree(array(
			"parent" => $el->id(),
			"class_id" => CL_PROPERTY_TREE_BRANCH
		));
		$ol = $ot->to_list();
		$i = get_instance(CL_PROPERTY_TREE_BRANCH);
		foreach($ol->arr() as $o)
		{
			$i->get_vis_tree_item($tv, $o, $var, $el);
		}

		$pd["type"] = "text";
		$pd["value"] = $tv->finalize_tree();
		
		if ($el->prop("no_caption") == 1)
		{
			$pd["no_caption"] = 1;
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["return_url"] = $_SERVER["REQUEST_METHOD"] == "GET" ? post_ru() : $arr["return_url"];
		$arr["ts"] = $_GET["ts"];
	}

	/**

		@attrib name=ct_del

	**/
	function ct_del($arr)
	{
		$sel = safe_array($arr["sel"]);
		if (count($sel))
		{
			$ol = new object_list(array("oid" => $sel));
			$ol->delete();
		}
		return $arr["return_url"];
	}

	function generate_get_property($arr)
	{
		$el = new object($arr["id"]);
		$sys_name = $arr["name"];
		$gpblock = "";
		$gpblock .= "\t\t\tcase \"${sys_name}\":\n";
		$gpblock .= "\t\t\t\t\$this->generate_${sys_name}(\$arr);\n";
		$gpblock .= "\t\t\t\tbreak;\n\n";
		return array(
			"get_property" => $gpblock,
			"generate_methods" => array("generate_${sys_name}"),
		);
	}
	
	function generate_method($arr)
	{
		$name = $arr["name"];
		$obj = new object($arr["id"]);
		$els = new object_tree(array(
			"parent" => $arr["id"],
			"class_id" => CL_PROPERTY_TREE_BRANCH,
		));
		$els = $els->to_list();

		$var = $name."_tf";

		$rv = "\tfunction $name(\$arr)\n";
		$rv .= "\t{\n";
		$rv .= "\t\tclassload(\"core/icons\");\n";
		$rv .= "\t\t\$var = \"$var\";\n";
		$rv .= "\t\t" . '$t = &$arr["prop"]["vcl_inst"];' . "\n";
		$rv .= "\t\t\n";
		$rv .= "\t\t\$t->start_tree(array(\n";
		$rv .= "\t\t\t\"type\" => TREE_DHTML,\n";
		$rv .= "\t\t\t\"tree_id\" => \"treee_{$name}\",\n";
		$rv .= "\t\t\t\"persist_state\" => true,\n";
		$rv .= "\t\t\t\"root_url\" => aw_url_change_var(\$var, NULL)\n";
		$rv .= "\t\t));\n";
		$rv .= "\t\t\n";
		
		$i = get_instance(CL_PROPERTY_TREE_BRANCH);
		foreach($els->arr() as $el)
		{
			$rv .= $i->do_generate_method($obj, $el, $var);
		};

		$rv .= "\t}\n\n";
		return $rv;
	}
}
?>
