<?php
/*
@classinfo syslog_type=ST_SHOP_PURCHASE_ORDER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_shop_purcahse_orders master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_purcahse_orders
@default group=general

@property number type=textbox field=aw_number
@caption Number

@property purchaser type=relpicker reltype=RELTYPE_PURCHASER field=aw_purchaser
@caption Hankija

@property job type=relpicker reltype=RELTYPE_JOB field=aw_job
@caption T&ouml;&ouml;

@property related_orders type=relpicker multiple=1 reltype=RELTYPE_SELL_ORDER store=connect
@caption Seotud m&uuml;&uuml;gitellimused			

@property date type=date_select field=aw_date
@caption Kuup&auml;ev

@property deal_date type=date_select field=aw_deal_date
@caption Tegelemise kuup&auml;ev

@property planned_date type=date_select field=aw_planned_arrival_date
@caption Planeeritud saabumise kuup&auml;ev

@property purchaser_rep type=relpicker reltype=RELTYPE_PURCHASER_REP field=aw_purchaser_rep
@caption Hankija esindaja

@property our_rep type=relpicker reltype=RELTYPE_OUR_REP field=aw_our_rep
@caption Meie esindaja

@property trans_cost type=textbox field=aw_trans_cost datatype=int
@caption Transpordikulu

@property customs_cost type=textbox field=aw_customs_cost datatype=int
@caption Tollikulu

@property transp_type type=relpicker field=aw_transp_type reltype=RELTYPE_TRANSFER_METHOD
@caption L&auml;hetusviis

@property currency type=relpicker reltype=RELTYPE_CURRENCY automatic=1 field=aw_currency
@caption Valuuta

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE automatic=1 field=aw_warehouse
@caption Ladu

@property confirmed type=checkbox ch_value=1 field=aw_confirmed
@caption Kinnitatud

@property closed type=checkbox ch_value=1 field=aw_closed
@caption Suletud

@property taxed type=chooser field=aw_taxed
@caption Maks


@default group=articles

	@property art_toolbar type=toolbar no_caption=1 store=no

	@property articles type=table store=no no_caption=1
	@caption Artiklid


@groupinfo articles caption="Artiklid"

@reltype PURCHASER value=1 clid=CL_CRM_COMPANY
@caption Hankija

@reltype PURCHASER_REP value=2 clid=CL_CRM_PERSON
@caption Hankija esindaja

@reltype SELL_ORDER value=3 clid=CL_SHOP_SELL_ORDER
@caption M&uuml;&uuml;gitellimus

@reltype OUR_REP value=4 clid=CL_CRM_PERSON
@caption Meie esindaja

@reltype CURRENCY value=5 clid=CL_CURRENCY
@caption Valuuta

@reltype WAREHOUSE value=6 clid=CL_SHOP_WAREHOUSE
@caption Ladu

@reltype TRANSFER_METHOD value=7 clid=CL_CRM_TRANSFER_METHOD
@caption L&auml;hetusviis

@reltype PRODUCT value=8 clid=CL_SHOP_PRODUCT
@caption Artikkel

@reltype ROW value=9 clid=CL_SHOP_ORDER_ROW
@caption Rida

@reltype JOB value=10 clid=CL_MRP_CASE
@caption T&ouml;&ouml;
*/

class shop_purchase_order extends class_base
{
	function shop_purchase_order()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_purchase_order",
			"clid" => CL_SHOP_PURCHASE_ORDER
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		if($_GET["action"] == "new")
		{
			return;
		}
		$arr["add_art"] = 0;
		$conn = obj($arr["id"])->connections_from(array(
			"type" => "RELTYPE_ROW",
		));
		foreach($conn as $c)
		{
			$o = $c->to();
			$arr["rows"][$o->id()]["tax_rate"] = $o->prop("tax_rate");
		}
	}

	function _get_taxed($arr)
	{
		$arr["prop"]["options"] = array(0 => "K&auml;ibemaksuta", 1 => "K&auml;ibemaksuga");
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_shop_purcahse_orders(aw_oid int primary key, aw_number varchar(255), aw_purchaser int, related_sales_orders int, aw_date int, aw_planned_arrival_date int, aw_purchaser_rep int, aw_our_rep int, aw_trans_cost double, aw_transp_type varchar(255), aw_currency int, aw_warehouse int, aw_confirmed int, aw_closed int, aw_taxed int)");
			return true;
		}
		switch($f)
		{
			case "aw_customs_cost":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "double"
				));
				return true;
				break;
			case "aw_job":
			case "aw_deal_date":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
				break;
		}
	}

	function _get_warehouse($arr)
	{
		if ($arr["request"]["warehouse"])
		{
			$arr["prop"]["value"] = $arr["request"]["warehouse"];
			$arr["prop"]["options"][$arr["request"]["warehouse"]] = obj($arr["request"]["warehouse"])->name();
		}
	}

	function _get_art_toolbar(&$arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_search_button(array(
			"pn" => "add_art",
			"multiple" => 1,
			"clid" => CL_SHOP_PRODUCT,
		));
		$tb->add_save_button();
		$tb->add_delete_button();
	}

	function _set_articles(&$arr)
	{
		$tmp = $arr["request"]["add_art"];
		if($tmp)
		{
			$arts = explode(",", $tmp);
			foreach($arts as $art)
			{
				$o = obj();
				$o->set_class_id(CL_SHOP_ORDER_ROW);
				$o->set_parent($arr["obj_inst"]->id());
				$o->set_name(sprintf(t("%s rida"), $arr["obj_inst"]->name()));
				$o->set_prop("prod", $art);
				$o->save();
				$arr["obj_inst"]->connect(array(
					"to" => $o->id(),
					"type" => "RELTYPE_ROW",
				));
				$arr["obj_inst"]->connect(array(
					"to" => $art,
					"type" => "RELTYPE_PRODUCT",
				));
			}
		}
		$rows = $arr["request"]["rows"];
		if(is_array($rows))
		{
			foreach($rows as $id => $row)
			{
				$ro = obj($id);
				foreach($row as $var => $val)
				{
					if($ro->is_property($var))
					{
						$ro->set_prop($var, $val);
					}
				}
				$ro->save();
			}
		}
	}

	function _get_articles(&$arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_articles_tbl($t);
		$conn = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_ROW",
		));
		$units = get_instance(CL_UNIT)->get_unit_list(true);
		foreach($conn as $c)
		{
			$o = $c->to();
			$url = $this->mk_my_orb("do_search", array(
				"pn" => "rows[".$o->id()."][tax_rate]",
				"clid" => array(
					CL_CRM_TAX_RATE
				),
				"multiple" => 0,
			), "popup_search");
			$url = "javascript:aw_popup_scroll(\"".$url."\",\"".t("Otsi")."\",550,500)";
			$tax = html::href(array(
				"caption" => html::img(array(
					"url" => "images/icons/search.gif",
					"border" => 0
				)),
				"url" => $url
			))." ".$o->prop("tax_rate.name");
			$t->define_data(array(
				"oid" => $o->id(),
				"name" => $this->can("view", $o->prop("prod"))?html::obj_change_url(obj($o->prop("prod")), parse_obj_name($o->prop("prod.name"))):'',
				"amount" => html::textbox(array(
					"name" => "rows[".$o->id()."][amount]",
					"value" => $o->prop("amount"),
					"size" => 3,
				)),
				"required" => html::textbox(array(
					"name" => "rows[".$o->id()."][required]",
					"value" => $o->prop("required"),
					"size" => 3,
				)),
				"unit" => html::select(array(
					"name" => "rows[".$o->id()."][unit]",
					"options" => $units,
					"value" => $o->prop("unit"),
				)),
				"unit_price" => html::textbox(array(
					"name" => "rows[".$o->id()."][price]",
					"value" => $o->prop("price"),
					"size" => 3,
				)),
				"sum" => $sum,
				"tax_rate" => $tax,
				"purchaser_art_code" => html::textbox(array(
					"name" => "rows[".$o->id()."][other_code]",
					"value" => $o->prop("other_code"),
					"size" => 5,
				)),
				"gotten_amt" => html::textbox(array(
					"name" => "rows[".$o->id()."][real_amount]",
					"value" => $o->prop("real_amount"),
					"size" => 3,
				)),
			));
		}
	}

	private function _init_articles_tbl($t)
	{
		$t->define_field(array(
			"caption" => t("Artikkel"),
			"align" => "center",
			"name" => "name",
			"sortable" => 1
		));
		$t->define_field(array(
			"caption" => t("Vajadus"),
			"align" => "center",
			"name" => "required",
			"sortable" => 1
		));
		$t->define_field(array(
			"caption" => t("Kogus"),
			"align" => "center",
			"name" => "amount",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("&Uuml;hik"),
			"align" => "center",
			"name" => "unit",
			"sortable" => 1
		));
		$t->define_field(array(
			"caption" => t("&Uuml;hiku hind"),
			"align" => "center",
			"name" => "unit_price",
			"sortable" => 1
		));
		$t->define_field(array(
			"caption" => t("Summa"),
			"align" => "center",
			"name" => "sum",
			"sortable" => 1
		));
		$t->define_field(array(
			"caption" => t("Maksum&auml;&auml;r"),
			"align" => "center",
			"name" => "tax_rate",
			"sortable" => 1
		));
		$t->define_field(array(
			"caption" => t("Hankija artiklikood"),
			"align" => "center",
			"name" => "purchaser_art_code",
			"sortable" => 1
		));
		$t->define_field(array(
			"caption" => t("Saadud kogus"),
			"align" => "center",
			"name" => "gotten_amt",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel",
		));
	}

	function _set_related_orders($arr)
	{
		if($arr["prop"]["value"] != $arr["obj_inst"]->prop($arr["prop"]["name"]))
		{
			foreach($arr["prop"]["value"] as $oid)
			{
				$o = obj($oid);
				$o_val = $o->prop($arr["prop"]["name"]);
				$o_val[$arr["obj_inst"]->id()] = $arr["obj_inst"]->id();
				$o->set_prop($arr["prop"]["name"], $o_val);
				$o->save();
			}
		}
		return PROP_OK;
	}
}

?>
