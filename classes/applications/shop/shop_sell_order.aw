<?php
/*
@classinfo syslog_type=ST_SHOP_SELL_ORDER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_shop_sell_orders master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_sell_orders
@default group=general

@property number type=textbox field=aw_number
@caption Number

@property purchaser type=relpicker reltype=RELTYPE_PURCHASER field=aw_purchaser
@caption Tellija

@property related_purchase_orders type=relpicker multiple=1 reltype=RELTYPE_PURCHASE_ORDER store=connect
@caption Seotud ostutellimused			

@property date type=date_select field=aw_date
@caption Kuup&auml;ev

@property planned_send_date type=date_select field=aw_planned_send_date
@caption Planeeritud saatmise kuup&auml;ev

@property buyer_rep type=relpicker reltype=RELTYPE_BUYER_REP field=aw_buyer_rep
@caption Tellija esindaja

@property our_rep type=relpicker reltype=RELTYPE_OUR_REP field=aw_our_rep
@caption Meie esindaja

@property trans_cost type=textbox field=aw_trans_cost
@caption Transpordikulu

@property transp_type type=textbox field=aw_transp_type
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

@reltype BUYER_REP value=2 clid=CL_CRM_PERSON
@caption Hankija esindaja

@reltype PURCHASE_ORDER value=3 clid=CL_SHOP_PURCHASE_ORDER
@caption Ostutellimus

@reltype OUR_REP value=4 clid=CL_CRM_PERSON
@caption Meie esindaja

@reltype CURRENCY value=5 clid=CL_CURRENCY
@caption Valuuta

@reltype WAREHOUSE value=6 clid=CL_SHOP_WAREHOUSE
@caption Ladu
*/

class shop_sell_order extends class_base
{
	function shop_sell_order()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_sell_order",
			"clid" => CL_SHOP_SELL_ORDER
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _get_taxed($arr)
	{
		$arr["prop"]["options"] = array(0 => "K&auml;ibemaksuta", 1 => "K&auml;ibemaksuga");
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_shop_sell_orders(aw_oid int primary key, aw_number varchar(255), aw_purchaser int, related_purcahse_orders int, aw_date int, aw_planned_send_date int, aw_buyer_rep int, aw_our_rep int, aw_trans_cost double, aw_transp_type varchar(255), aw_currency int, aw_warehouse int, aw_confirmed int, aw_closed int, aw_taxed int)");
			return true;
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

	function _get_art_toolbar($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_search_button();
	}

	function _get_articles($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_articles_tbl($t);
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
			"caption" => t("Tellija artiklikood"),
			"align" => "center",
			"name" => "buyer_art_code",
			"sortable" => 1
		));
		$t->define_field(array(
			"caption" => t("Saadud kogus"),
			"align" => "center",
			"name" => "gotten_amt",
			"sortable" => 1
		));
	}
}
?>
