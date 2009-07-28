<?php
/*
@classinfo syslog_type=ST_SHOP_DELIVERY_METHOD relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=instrumental
@tableinfo aw_shop_delivery_method master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_delivery_method
@default group=general

	@property jrk field=jrk type=textbox size=4 table=objects
	@caption J&auml;rjekord

	@property type type=chooser field=aw_type orient=vertical
	@caption T&uuml;&uuml;p

	@property price type=textbox size=4 field=aw_price
	@caption Hind

	@property enabled type=checkbox field=aw_enabled
	@caption Vaikimisi lubatud

@groupinfo matrix caption="Maatriks"
	@groupinfo matrix_show caption="Maatriks" parent=matrix
	@default group=matrix_show

		@property matrix type=table store=no no_caption=1

	@groupinfo matrix_settings caption="Maatriksi seaded" parent=matrix
	@default group=matrix_settings

		@property matrix_cols type=text subtitle=1 store=no
		@caption Maatriksi veerud

			@property matrix_customer_categories type=relpicker reltype=RELTYPE_CUSTOMER_CATEGORY multiple=1 store=connect
			@caption Kliendikategooriad

#			@property matrix_customer_relations
#			@caption Kliendisuhted

		@property matrix_rows type=text subtitle=1 store=no
		@caption Maatriksi read

			@property matrix_product_categories type=relpicker reltype=RELTYPE_PRODUCT_CATEGORY multiple=1 store=connect
			@caption Tootekategooriad

#### RELTYPES

@reltype CUSTOMER_CATEGORY value=1 clid=CL_CRM_CATEGORY
@caption Kliendikategooria, mida maatriksi veeruna kuvatakse

@reltype PRODUCT_CATEGORY value=2 clid=CL_SHOP_PRODUCT_CATEGORY
@caption Tootekategooria, mida maatriksi reana kuvatakse

*/

class shop_delivery_method extends class_base
{
	public function shop_delivery_method()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_delivery_method",
			"clid" => CL_SHOP_DELIVERY_METHOD
		));
	}

	public static function get_type_options()
	{
		return array(
			1 => t("Lisandub iga toote hinnale eraldi"),
			2 => t("Lisandub kogu tellimuse hinnale"),
		);
	}

	public function _get_type($arr)
	{
		$arr["prop"]["options"] = $this->get_type_options();
	}

	public function _get_matrix($arr)
	{
		$matrix = array();
		$odl = new object_data_list(
			array(
				"class_id" => CL_SHOP_DELIVERY_METHOD_CONDITIONS,
				"delivery_method" => $arr["obj_inst"]->id(),
				"lang_id" => array(),
				"site_id" => array(),
			),
			array(
				CL_SHOP_DELIVERY_METHOD_CONDITIONS => array("row", "col", "enable"),
			)
		);
		foreach($odl->arr() as $cond)
		{
			$matrix[$cond["row"]][$cond["col"]] = $cond["enable"] ? 1 : 2;
		}

		shop_price_list::draw_matrix(array(
			"table_inst" => &$arr["prop"]["vcl_inst"],
			"obj_inst" => &$arr["obj_inst"],
			"matrix_data" => $matrix,
			"data_cell_callback" => array(&$this, "draw_matrix_cell"),
		));
	}

	public function draw_matrix_cell($oid, $field, $matrix)
	{
		return html::select(array(
			"name" => "matrix[".$oid."][".(substr($field["name"], -5) == "_self" ? substr($field["name"], 0, -5) : $field["name"])."]",
			"value" => ifset($matrix, $oid, (substr($field["name"], -5) == "_self" ? substr($field["name"], 0, -5) : $field["name"])),
			"options" => array(
				"0" => t("--Vali--"),
				"1" => t("Lubatud"),
				"2" => t("Keelatud"),
			),
		));
	}

	public function _set_matrix($arr)
	{
		$data = $arr["prop"]["value"];

		$odl = new object_data_list(
			array(
				"class_id" => CL_SHOP_DELIVERY_METHOD_CONDITIONS,
				"delivery_method" => $arr["obj_inst"]->id(),
				"lang_id" => array(),
				"site_id" => array(),
			),
			array(
				CL_SHOP_DELIVERY_METHOD_CONDITIONS => array("row", "col", "enable"),
			)
		);

		$delete = array();
		foreach($odl->arr() as $cond_id => $cond)
		{
			if(!empty($data[$cond["row"]][$cond["col"]]))
			{
				if($data[$cond["row"]][$cond["col"]] == 1 && !$cond["enable"] || $data[$cond["row"]][$cond["col"]] == 2 && $cond["enable"])
				{
					// Conditions have changed!
					$change[$cond_id] = array(
						"enable" => $data[$cond["row"]][$cond["col"]],
					);
				}
				unset($data[$cond["row"]][$cond["col"]]);
			}
			else
			{
				// No such conditions any more!
				$delete[$cond_id] = $cond_id;
			}
		}

		if(count($change))
		{
			$ol = new object_list(array(
				"oid" => array_keys($change),
				"lang_id" => array(),
				"site_id" => array(),
			));
			foreach($ol->arr() as $oid => $o)
			{
				$o->set_prop("enable", $change[$oid]["enable"]);
				$o->save();
			}
		}

		foreach($data as $row => $data)
		{
			foreach($data as $col => $val)
			{
				if(!empty($val))
				{
					$o = obj();
					$o->set_parent($arr["obj_inst"]->id());
					$o->set_class_id(CL_SHOP_DELIVERY_METHOD_CONDITIONS);
					$o->set_prop("delivery_method", $arr["obj_inst"]->id());
					$o->set_prop("row", $row);
					$o->set_prop("col", $col);
					$o->set_prop("enable", $val == 1 ? 1 : 0);
					$o->save();
				}
			}
		}

		if(count($delete) > 0)
		{
			$ol = new object_list(array(
				"oid" => $delete,
				"lang_id" => array(),
				"site_id" => array(),
			));
			$ol->delete();
		}
	}

	public function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	public function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	public function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_shop_delivery_method(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_type":
			case "aw_enabled":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;

			case "aw_price":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "decimal(14,4)"
				));
				return true;
		}
	}
}

?>
