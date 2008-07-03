<?php
/*
@classinfo syslog_type=ST_SHOP_PRODUCT_CATEGORY relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_shop_product_category master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_product_category
@default group=general

@property desc type=textarea rows=10 cols=50 field=aw_desc
@caption Kirjeldus

@property images type=relpicker reltype=RELTYPE_IMAGE multiple=1 store=connect 
@caption Pildid

@property unit_formula type=relpicker reltype=RELTYPE_UNIT_FORMULA store=connect multiple=1
@caption &Uuml;hikute valemid

@property doc type=relpicker reltype=RELTYPE_DOC field=aw_doc
@caption Dokument

@property folders_tb type=toolbar store=no no_caption=1

@property folders type=table store=no no_caption=1 

@reltype IMAGE value=1 clid=CL_IMAGE
@caption Pilt

@reltype DOC value=2 clid=CL_FILE
@caption Dokument

@reltype DISPLAY_FOLDER value=3 clid=CL_MENU
@caption Kuvamise kaust

@reltype UNIT_FORMULA value=4 clid=CL_SHOP_UNIT_FORMULA
@caption &Uuml;hikute valem
*/

class shop_product_category extends class_base
{
	function shop_product_category()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_product_category",
			"clid" => CL_SHOP_PRODUCT_CATEGORY
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["add_folder"] = 0;
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_shop_product_category(aw_oid int, aw_desc text, aw_doc int)");
			return true;
		}
	}

	function _get_folders_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_search_button(array(
			"pn" => "add_folder",
			"clid" => CL_MENU,
			"multiple" => 1,
		));
		$tb->add_save_button();
	}

	function _get_folders($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "delete",
			"caption" => t("Eemalda"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "display",
			"caption" => t("Kuvamise kaust"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "default",
			"caption" => t("Salvestamise kaust"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"width" => "70%",
		));
		$df = $arr["obj_inst"]->meta("disp_fld");
		$def = $arr["obj_inst"]->meta("def_fld");
		foreach($df as $f)
		{
			$fo = obj($f);
			$t->define_data(array(
				"delete" => html::checkbox(array(
					"name" => "del_disp[".$f."]",
					"value" => 1,
				)),
				"display" => html::checkbox(array(
					"name" => "display[".$f."]",
					"value" => 1,
					"checked" => $arr["obj_inst"]->is_connected_to(array(
						"to" => $f,
					)),
				)),
				"default" => html::radiobutton(array(
					"name" => "def_fld",
					"value" => $f,
					"checked" => ($f == $def),
				)),
				"name" => $fo->name(),
			));
		}
	}

	function _set_folders($arr)
	{
		$df = $arr["obj_inst"]->meta("disp_fld");
		if($fs = $arr["request"]["add_folder"])
		{
			$tmp = explode(",", $fs);
			foreach($tmp as $f)
			{
				$df[$f] = $f;
			}
		}
		if(count($arr["request"]["del_disp"]))
		{
			foreach($arr["request"]["del_disp"] as $f)
			{
				unset($df[$f]);
				$arr["obj_inst"]->disconnect(array(
					"from" => $f,
				));
			}
		}
		foreach($df as $f)
		{
			if($arr["request"]["display"][$f])
			{
				$arr["obj_inst"]->connect(array(
					"to" => $f,
					"type" => "RELTYPE_DISPLAY_FOLDER"
				));
			}
			elseif($arr["obj_inst"]->is_connected_to(array("to" => $f)))
			{
				$arr["obj_inst"]->disconnect(array(
					"from" => $f,
				));
			}
		}
		$arr["obj_inst"]->set_meta("def_fld", $arr["request"]["def_fld"]);
		$arr["obj_inst"]->set_meta("disp_fld", $df);
		$arr["obj_inst"]->save();
	}
}

?>
