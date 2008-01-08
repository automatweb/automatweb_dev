<?php
// crm_bill_payment.aw - Laekumine
/*

@classinfo syslog_type=ST_CRM_BILL_PAYMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects

@tableinfo aw_crm_bill_payment index=aw_oid master_index=brother_of master_table=objects

@default group=general

@default table=aw_crm_bill_payment

@property date type=date_select table=aw_crm_bill_payment field=aw_date
@caption Kuup&auml;ev

@property payment_type type=chooser field=aw_payment_type
@caption Tasumisviis

@property sum type=textbox field=aw_sum 
@caption Summa

@property currency type=relpicker reltype=RELTYPE_CURRENCY field=aw_currency 
@caption Valuuta

@property currency_rate type=textbox field=currency_rate field=aw_currency_rate 
@caption Valuutakurss

@property bills type=table store=no
@caption Arved

@reltype CURRENCY value=1 clid=CL_CURRENCY
@caption valuuta


- Kuupäev
- Tasumisviis (Ülekandega, sularahas)
- Valuuta
- Valuutakurss
Arvete nimekiri, mis selle laekumisega on seotud


*/

class crm_bill_payment extends class_base
{
	function crm_bill_payment()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_bill_payment",
			"clid" => CL_CRM_BILL_PAYMENT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "bills":
				$ol = new object_list(array(
					"class_id" => CL_CRM_BILL,
					"lang_id" => array(),
					"CL_CRM_BILL.RELTYPE_PAYMENT.id" => $arr["obj_inst"]->id(),
				));
				$bi = get_instance(CL_CRM_BILL);
				foreach($ol -> arr() as $o)
				{
					 $prop["value"] .= "\n".t("Arve nr:").html::obj_change_url($o->id(),$o->prop("bill_no")).", ".$o->prop("customer.name").",  ".$bi->get_bill_sum($o)." ".$bi->get_bill_currency($o);
				}
				break;
			case "payment_type":
				$prop["options"] = array(0 => t("&Uuml;lekandega"), 1 => t("Sularahas"));
				break;
		}

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

	function _get_bill_payments_tb($arr)
	{
		$_SESSION["create_bill_ru"] = get_ru();
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Lisa'),
			'url' => html::get_new_url(CL_CRM_BILL, $arr["obj_inst"]->id(), array("return_url" => get_ru()))
		));

		//if ($arr["request"]["proj"])
		//{
			$tb->add_button(array(
				"name" => "create_bill",
				"img" => "save.gif",
				"tooltip" => t("Koosta arve"),
				"action" => "create_bill"
			));
		//}
		$tb->add_button(array(
			"name" => "search_bill",
			"img" => "search.gif",
			"tooltip" => t("Otsi"),
	//		"action" => "search_bill"
			"url" => "javascript:aw_popup_scroll('".$this->mk_my_orb("search_bill", array("openprintdialog" => 1,))."','Otsing',550,500)",
		));
	}

	function _init_bills_list_t(&$t, $r)
	{
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"type" => "time",
			"format" => "d.m.Y",
			"numeric" => 1,
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "type",
			"caption" => t("Tasumisviis"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "currency",
			"caption" => t("Valuuta"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "currency_rate",
			"caption" => t("Valuutakurss"),
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_bill_payments_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bills_list_t($t, $arr["request"]);

		$t->set_caption(t("Laekumised"));

		$ol = new object_list(array(
			"class_id" => array(CL_CRM_BILL_PAYMENT),
			"lang_id" => array(),
		));

		$sum = 0;

		foreach($ol->arr() as $o)
		{
//			$sum = $sum + 
			
			$t->define_data(array(
				"date" => $o->prop("date"),
				"oid" => $o->id(),
				"type" => $o->prop("type") ? t("Sularahas") : t("&Uuml;lekandega"),
				"currency" => $o->prop("currency.name"),
				"currency_rate" => $o->prop("currency_rate"),
			));
			$sum+= $cursum;
		}

		$t->set_default_sorder("desc");
		$t->set_default_sortby("date");
		$t->sort_by();
		$t->set_sortable(false);

//vajalik vaid siis kui mingi summa teema ka ikka tuleb, mis oleks loogiline
		$t->define_data(array(
			"sum" => "<b>".number_format($sum, 2)."</b>",
			"bill_no" => t("<b>Summa</b>")
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "" && $t == "aw_crm_bill_payment")
		{
			$this->db_query("CREATE TABLE aw_crm_bill_payment(aw_oid int primary key,
				aw_date int,
				aw_payment_type int,
				aw_sum double,
				aw_currency int,
				aw_currency_rate double
			)");
			return true;
		}
		return false;
	}


}

?>
