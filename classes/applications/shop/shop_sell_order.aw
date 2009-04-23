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

@property job type=relpicker reltype=RELTYPE_JOB field=aw_job
@caption T&ouml;&ouml;

@property related_orders type=relpicker multiple=1 reltype=RELTYPE_PURCHASE_ORDER store=connect
@caption Seotud ostutellimused			

@property date type=date_select field=aw_date
@caption Kuup&auml;ev

@property deal_date type=date_select field=aw_deal_date
@caption Tegelemise kuup&auml;ev

@property planned_date type=date_select field=aw_planned_send_date
@caption Planeeritud saatmise kuup&auml;ev

@property buyer_rep type=relpicker reltype=RELTYPE_BUYER_REP field=aw_buyer_rep
@caption Tellija esindaja

@property our_rep type=relpicker reltype=RELTYPE_OUR_REP field=aw_our_rep
@caption Meie esindaja

@property trans_cost type=textbox field=aw_trans_cost
@caption Transpordikulu

@property customs_cost type=textbox field=aw_customs_cost datatype=int
@caption Tollikulu

@property transp_type type=relpicker field=aw_transp_type reltype=RELTYPE_TRANSFER_METHOD
@caption L&auml;hetusviis

@property currency type=relpicker reltype=RELTYPE_CURRENCY automatic=1 field=aw_currency
@caption Valuuta

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE automatic=1 field=aw_warehouse
@caption Ladu

@property order_status type=chooser default=0 field=aw_status
@caption Staatus

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

@reltype TRANSFER_METHOD value=7 clid=CL_CRM_TRANSFER_METHOD
@caption L&auml;hetusviis

@reltype PRODUCT value=8 clid=CL_SHOP_PRODUCT
@caption Artikkel

@reltype ROW value=9 clid=CL_SHOP_ORDER_ROW
@caption Rida

@reltype JOB value=10 clid=CL_MRP_JOB
@caption T&ouml;&ouml;
*/

class shop_sell_order extends class_base
{
	function shop_sell_order()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_sell_order",
			"clid" => CL_SHOP_SELL_ORDER
		));

		get_instance(CL_SHOP_PURCHASE_ORDER);

		$this->states = array(
			ORDER_STATUS_INPROGRESS => t("Koostamisel"),
			ORDER_STATUS_CONFIRMED => t("Kinnitatud"),
			ORDER_STATUS_CANCELLED => t("Katkestatud"),
			ORDER_STATUS_SENT => t("Saadetud"),
			ORDER_STATUS_CLOSED => t("T&auml;idetud"),
		);
	}

	function callback_mod_reforb($arr)
	{
		return get_instance(CL_SHOP_PURCHASE_ORDER)->callback_mod_reforb(&$arr);
	}

	function _get_taxed($arr)
	{
		return PROP_IGNORE;
		$arr["prop"]["options"] = array(0 => "K&auml;ibemaksuta", 1 => "K&auml;ibemaksuga");
	}

	function _get_order_status($arr)
	{
		$arr["prop"]["options"] = $this->states;
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_shop_sell_orders(aw_oid int primary key, aw_number varchar(255), aw_purchaser int, related_purcahse_orders int, aw_date int, aw_planned_send_date int, aw_buyer_rep int, aw_our_rep int, aw_trans_cost double, aw_transp_type varchar(255), aw_currency int, aw_warehouse int, aw_taxed int)");
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
			case "aw_status":
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

	function _get_art_toolbar($arr)
	{
		return get_instance(CL_SHOP_PURCHASE_ORDER)->_get_art_toolbar($arr);
	}

	function _get_articles($arr)
	{
		return get_instance(CL_SHOP_PURCHASE_ORDER)->_get_articles($arr);
	}

	function _set_articles($arr)
	{
		return get_instance(CL_SHOP_PURCHASE_ORDER)->_set_articles($arr);
	}

	function _set_related_orders($arr)
	{
		return get_instance(CL_SHOP_PURCHASE_ORDER)->_set_related_orders($arr);
	}
}
?>
