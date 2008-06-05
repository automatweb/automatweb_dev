<?php
/*
@classinfo syslog_type=ST_SHOP_DELIVERY_NOTE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=robert
@tableinfo aw_shop_delivery_note master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_delivery_note
@default group=general

	@property number type=textbox
	@caption Number

	@property delivery_date type=date_select
	@caption Kuup&auml;ev

	@property from_warehouse type=relpicker reltype=RELTYPE_FROM_WAREHOUSE
	@caption Laost
	
	@property to_warehouse type=relpicker reltype=RELTYPE_TO_WAREHOUSE
	@caption Lattu

	@property customer type=relpicker reltype=RELTYPE_CUST
	@caption Klient

	@property impl type=relpicker reltype=RELTYPE_IMPL
	@caption Hankija

	@property transport type=textbox
	@caption Transport

	@property customs type=textbox
	@caption Toll

	@property currency type=relpicker reltype=RELTYPE_CURRENCY
	@caption Valuuta

	@property approved type=checkbox ch_value=1
	@caption Kinnitatud

	@property articles_tb store=no no_caption=1 type=toolbar

	@property articles_tbl store=no no_caption=1 type=table

@groupinfo bills caption=Arved
@default group=bills

	@property bills_tb store=no no_caption=1 type=toolbar

	@property bills_tbl store=no no_caption=1 type=table

@groupinfo articles caption=Artiklid
@default group=articles

@reltype FROM_WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption Algladu

@reltype TO_WAREHOUSE value=2 clid=CL_SHOP_WAREHOUSE
@caption Sihtladu

@reltype CURRENCY value=3 clid=CL_CURRENCY
@caption Valuuta

@reltype BILL value=4 clid=CL_CRM_BILL
@caption Arve

@reltype PRODUCT value=5 clid=CL_SHOP_PRODUCT
@caption Artikkel

@reltype CUST value=6 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype IMPL value=7 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Teostaja
*/

class shop_delivery_note extends class_base
{
	function shop_delivery_note()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_delivery_note",
			"clid" => CL_SHOP_DELIVERY_NOTE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "approved":
				if($prop["value"] && !($arr["obj_inst"]->prop("approved")))
				{
					$ret = $this->create_movement($arr);
					if(!$ret)
					{
						$prop["error"] = $this->err;
						$retval = PROP_FATAL_ERROR;
					}
				}
				break;
		}

		return $retval;
	}

	function _get_articles_tb($arr)
	{
		if($arr["new"])
		{
			return ;
		}
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"img" => "delete.gif",
			"tooltip" => t("Eemalda valitud artiklid"),
			"name" => "delete_article_rels",
			"action" => "del_article_rels",
		));
	}

	function _init_articles_tbl($t)
	{
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));
		$t->define_field(array(
			"caption" => t("Artiklikood"),
			"align"=> "center",
			"name" => "code",
		));
		$t->define_field(array(
			"caption" => t("Nimetus"),
			"align"=> "center",
			"name" => "name",
		));
		$t->define_field(array(
			"caption" => t("KM kood"),
			"align"=> "center",
			"name" => "km_code",
		));
		$t->define_field(array(
			"caption" => t("Ribakood"),
			"align"=> "center",
			"name" => "barcode",
		));
		$t->define_field(array(
			"caption" => t("Kirjeldus"),
			"align"=> "center",
			"name" => "description",
		));
		$t->define_field(array(
			"caption" => t("Seerianumber"),
			"align"=> "center",
			"name" => "serial_no",
		));
		$t->define_field(array(
			"caption" => t("Partiinumber"),
			"align"=> "center",
			"name" => "set_no",
		));
		$t->define_field(array(
			"caption" => t("Ladu"),
			"align"=> "center",
			"name" => "warehouse",
		));
		$t->define_field(array(
			"caption" => t("Ostuhind"),
			"align"=> "center",
			"name" => "purchase_price",
		));
		$t->define_field(array(
			"caption" => t("P&otilde;hihind"),
			"align"=> "center",
			"name" => "base_price",
		));
		$t->define_field(array(
			"caption" => t("P&otilde;hihind KM-ga"),
			"align"=> "center",
			"name" => "base_price_tax",
		));
		$t->define_field(array(
			"caption" => t("Hind"),
			"align"=> "center",
			"name" => "price",
		));
		$t->define_field(array(
			"caption" => t("Omahinna summa"),
			"align"=> "center",
			"name" => "ourprice_sum",
		));
		$t->define_field(array(
			"caption" => t("&Uuml;hik"),
			"align"=> "center",
			"name" => "unit",
		));
		$t->define_field(array(
			"caption" => t("Kogus"),
			"align"=> "center",
			"name" => "amount",
		));
		$t->define_field(array(
			"caption" => t("Summa"),
			"align"=> "center",
			"name" => "sum",
		));
	}

	function _get_articles_tbl($arr)
	{
		if($arr["new"])
		{
			return ;
		}
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_articles_tbl(&$t);
		$t->define_data(array(
			"code" => html::select(array(
				"name" => "addcode",
				"options" => $this->_get_article_code_chooser(),
			)),
			"add" => t("<strong>Lisa uus</strong>")
		));
		$data = $arr["obj_inst"]->meta("articles");
		$conn = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_PRODUCT",
		));
		if($to = $arr["obj_inst"]->prop("to_warehouse"))
		{
			$warehouse = $to;
		}
		elseif($from = $arr["obj_inst"]->prop("from_warehouse"))
		{
			$warehouse = $from;
		}
		if($this->can("view",$warehouse))
		{
			$who = obj($warehouse);
			$calc_type = $who->prop("status_calc_type");
		}
		$pi = get_instance(CL_SHOP_PRODUCT);
		foreach($conn as $c)
		{
			$prod = $c->to();
			$id = $prod->id();
			$totalprices[$prod->id()] = $data[$id]["price"]*$data[$id]["amount"];
		}
		$total = 0;
		foreach($totalprices as $id=>$price)
		{
			$total += $price;
		}
		$other_prices = $arr["obj_inst"]->prop("customs") + $arr["obj_inst"]->prop("transport");
		$warehouse_list = $this->_get_warehouse_chooser();
		$pi = get_instance(CL_SHOP_PRODUCT);
		foreach($conn as $c)
		{
			$prod = $c->to();
			$id = $prod->id();
			$price = $pi->calc_price($prod);;
			$amount = $data[$id]["amount"];
			if($total>0 && $amount>0)
			{
				$ourprice_sum = round((($data[$id]["price"]*$amount/$total) * $other_prices)/$amount + $data[$id]["price"], 2);
			}
			else
			{
				$ourprice_sum = ($p = $data[$id]["price"])?$p:0;
			}
			$prod_unit_list = $pi->get_units($prod);
			$unit_list = array();
			foreach($prod_unit_list as $i=>$unit)
			{
				if($this->can("view", $unit))
				{
					$uo = obj($unit);
					$unit_list[$unit] = $uo->name();
				}
			}
			$art = array(
				"oid" => $id,
				"code" => $prod->prop("code"),
				"name" => html::obj_change_url($prod, $prod->name()),
				"barcode" => $prod->prop("barcode"),
				"description" => $prod->comment(),
				"serial_no" => $prod->prop("serial_number_based")?html::textbox(array(
					"name" => "articles[".$id."][serial_no]",
					"size" => 5,
					"value" => $data[$id]["serial_no"],
				)):'',
				"set_no" => $prod->prop("order_based")?html::textbox(array(
					"name" => "articles[".$id."][set_no]",
					"size" => 5,
					"value" => $data[$id]["set_no"],
					"autocomplete_source" => $this->mk_my_orb("articles_set_no_autocomplete_source"),
					"autocomplete_params" => array("articles[".$id."][set_no]"),
				)):'',
				"warehouse" => html::select(array(
					"name" => "articles[".$id."][warehouse]",
					"value" => $data[$id]["warehouse"] ? $data[$id]["warehouse"] : $warehouse,
					"options" => $warehouse_list,
				)),
				"purchase_price" => ($calc_type == 2) ? $pi->get_fifo_price($prod) : $pi->get_last_purchase_price($prod),
				"base_price" => $price,
				"base_price_tax" => round($price*1.18, 2),
				"price" => html::textbox(array(
					"name" => "articles[".$id."][price]",
					"size" => 4,
					"value" => $data[$id]["price"],
				)),
				"ourprice_sum" => $ourprice_sum,
				"unit" => html::select(array(
					"name" => "articles[".$id."][unit]",
					"value" => $data[$id]["unit"],
					"options" => $unit_list,
				)),
				"amount" => html::textbox(array(
					"name" => "articles[".$id."][amount]",
					"size" => 3,
					"value" => $data[$id]["amount"],
				)),
				"sum" => $totalprices[$id],
				"add" => t("<strong>Saatelehe read</strong>"),
			);
			$t->define_data($art);
		}
		$t->set_rgroupby(array("add"=>"add"));
	}

	function _get_warehouse_chooser()
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_WAREHOUSE,
			"site_id" => array(),
			"lang_id" => array(),
		));
		$res = $ol->names();
		natcasesort($res);
		return $res;	
	}

	function _get_article_code_chooser()
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"site_id" => array(),
			"lang_id" => array(),
		));
		$res = array();
		foreach($ol->arr() as $o)
		{
			if($code = $o->prop("code"))
			{
				$res[$o->id()] = $code;
			}
		}
		$res[0] = " ".t("--vali--");
		natcasesort($res);
		return $res;
	}

	function _set_articles_tbl($arr)
	{
		$data = $arr["request"]["articles"];
		$add = $arr["request"]["addcode"];
		if($add && !$data[$add])
		{
			$data[$add] = array();
			$arr["obj_inst"]->connect(array(
				"to" => $add,
				"type" => "RELTYPE_PRODUCT",
			));
		}
		foreach($data as $id=>$row)
		{
			$vars = array("amount", "price");
			foreach($vars as $var)
			{
				$data[$id][$var] = str_replace(",",".",$row[$var]);
			}
		}
		$arr["obj_inst"]->set_meta("articles", $data);
		$arr["obj_inst"]->save();
	}

	function _get_bills_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_CRM_BILL), $arr["obj_inst"]->id(), 4);
		$tb->add_search_button(array(
			"pn" => "add_bill",
			"clid" => CL_CRM_BILL,
			"multiple" => 1,
		));
		$tb->add_delete_rels_button();
	}

	function _get_bills_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel",
		));
		$t->define_field(array(
			"name" => "number",
			"caption" => t("Number"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
		));
		$conn = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_BILL",
		));
		foreach($conn as $c)
		{
			$bill = $c->to();
			$t->define_data(array(
				"number" => html::obj_change_url($c->to(), ($no = $bill->prop("bill_no"))?$no:t("(Puudub)")),
				"date" => date('d.m.Y', $bill->prop("bill_date")),
				"oid" => $bill->id(),
			));
		}
	}

	function _set_bills_tb($arr)
	{
		if($add = $arr["request"]["add_bill"])
		{
			$tmp = explode(",", $add);
			foreach($tmp as $bill)
			{
				$arr["obj_inst"]->connect(array(
					"to" => $bill,
					"type" => "RELTYPE_BILL",
				));
			}
		}
	}


	//this function should create a new movement object for each row
	//then update warehouse_amount objects so the amounts are correct
	//all warehouse_amounts (with different units) have to be changed
	//the new amounts for different units are calculated using unit calculation formulas
	function create_movement($arr)
	{
		$from_wh_id = $arr["obj_inst"]->prop("from_warehouse");
		if(is_oid($from_wh_id))
		{
			$from_wh = obj($from_wh_id);
		}
		$to_wh_id = $arr["obj_inst"]->prop("to_warehouse");
		if(is_oid($to_wh_id))
		{
			$to_wh = obj($to_wh_id);
		}
		$rowdata = $arr["obj_inst"]->meta("articles");
		$single_vars = array(
			0 => array(
				"prod_prop" => "serial_number_based",
				"err_word1" => "Seerianumbri",
				"err_word2" => "seerianumber",
				"row_prop" => "serial_no",
				"single_type" => "0",
			),
			1 => array(
				"prod_prop" => "order_based",
				"err_word1" => "Partii numbri",
				"err_word2" => "partiinumber",
				"row_prop" => "set_no",
				"single_type" => "1",
			),
		);
		$pi = get_instance(CL_SHOP_PRODUCT);
		foreach($rowdata as $prod_id => $row)
		{
			if(!$row["unit"])
			{
				$this->err = t("Igal tootel tuleb &uuml;hik m&auml;&auml;rata.");
				return false;
			}
		}
		foreach($rowdata as $prod_id => $row)
		{
			$prod = obj($prod_id);
			$singles = array();
			foreach($single_vars as $sv)
			{
				if($prod->prop($sv["prod_prop"]))
				{
					if(!($no = $row[$sv["row_prop"]]))
					{
						$this->err = t($sv["err_word1"]." p&otilde;hise arvestusega tootel tuleb ".$sv["err_word2"]." m&auml;&auml;rata.");
						return false;
					}
					$find_ol = new object_list(array(
						"class_id" => CL_SHOP_PRODUCT_SINGLE,
						"code" => $no,
						"type" => $sv["single_type"],
						"product" => $prod_id,
					));
					if($find_ol->count())
					{
						$singles[] = $find_ol->begin();
					}
					else
					{
						$o = obj();
						$o->set_class_id(CL_SHOP_PRODUCT_SINGLE);
						$o->set_parent($prod_id);
						$o->set_name($row[$sv["row_prop"]]);
						$o->set_prop("product", $prod_id);
						$o->set_prop("type", $sv["single_type"]);
						$o->set_prop("code", $row[$sv["row_prop"]]);
						$o->save();
						$singles[] = $o;
					}
				}
			}
			if(!count($singles))
			{
				$singles = array(0=>null);
			}
			$wh_vars = array(
				0 => array(
					"amt_mod" => -1,
					"var" => "from_wh",
				),
				1 => array(
					"amt_mod" => 1,
					"var" => "to_wh",
				),
			);
			foreach($singles as $single)
			{
				$sid = $single?$single->id():null;
				foreach($wh_vars as $whv)
				{
					if(${$whv["var"]})
					{
						$amount = $pi->get_amount(array(
							"unit" => $row["unit"],
							"prod" => $prod_id,
							"single" => $sid,
							"warehouse" => ${$whv["var"]}->id(),
						));
						if(!$amount->count())
						{
							$amt = obj();
							$amt->set_class_id(CL_SHOP_WAREHOUSE_AMOUNT);
							$amt->set_parent($prod_id);
							$amt->set_prop("warehouse", ${$whv["var"]}->id());
							$amt->set_prop("product", $prod_id);
							$amt->set_prop("single", $sid);
							$amt->set_prop("amount", $whv["amt_mod"]*$row["amount"]);
							$amt->set_prop("unit", $row["unit"]);
							$amt->set_name(sprintf(t("%s laoseis"), $prod->name()));
							$amt->save();
						}
						else
						{
							$amt = $amount->begin();
							$amt->set_prop("amount", $amt->prop("amount") + $whv["amt_mod"]*$row["amount"]);
							$amt->save();
						}
					}
				}
				$mvo = obj();
				$mvo->set_class_id(CL_SHOP_WAREHOUSE_MOVEMENT);
				$mvo->set_prop("from_wh", $from_wh?$from_wh->id():null);
				$mvo->set_prop("to_wh", $to_wh?$to_wh->id():null);
				$mvo->set_prop("product", $prod_id);
				$mvo->set_prop("single", $sid);
				$mvo->set_prop("amount", $row["amount"]);
				$mvo->set_prop("unit", $row["unit"]);
				$mvo->set_prop("price", $row["price"]);
				$mvo->set_prop("transport", $arr["obj_inst"]->prop("transport"));
				$mvo->set_prop("customs", $arr["obj_inst"]->prop("customs"));
				$mvo->set_prop("date", $arr["obj_inst"]->prop("delivery_date"));
				$mvo->set_prop("delivery_note", $arr["obj_inst"]->id());
				$mvo->set_parent($prod_id);
				$mvo->set_name(sprintf(t("%s liikumine"), $prod->name()));
				$mvo->save();
			}
		}
		return true;
	}

	/**
	@attrib name=del_article_rels all_args=1
	**/
	function del_article_rels($arr)
	{
		$o = obj($arr["id"]);
		$data = $o->meta("articles");
		foreach($arr["sel"] as $oid)
		{
			$o->disconnect(array(
				"from" => $oid,
			));
			unset($data[$oid]);
		}
		$o->set_meta("articles", $data);
		return $arr["post_ru"];
	}

	/**
	@attrib name=articles_set_no_autocomplete_source all_args=1
	**/
	function articles_set_no_autocomplete_source($arr)
	{
		return array();
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["add_bill"] = 0;
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
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_shop_delivery_note(aw_oid int primary key)");
			return true;
		}
		switch($f)
		{
			case "number":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(255)"
				));
				return true;
				break;
			case "transport":
			case "customs":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "float"
				));
				return true;
				break;

			case "delivery_date":
			case "from_warehouse":
			case "to_warehouse":
			case "currency":
			case "approved":
			case "customer":
			case "impl":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int",
				));
				return true;
				break;
		}
		return false;
	}
}

?>
