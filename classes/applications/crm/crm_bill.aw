<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_bill.aw,v 1.94 2006/09/06 14:28:29 markop Exp $
// crm_bill.aw - Arve 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_CRM_BILL, on_delete_bill)


@classinfo syslog_type=ST_CRM_BILL relationmgr=yes no_status=1 prop_cb=1 confirm_save_data=1

@default table=objects

@tableinfo aw_crm_bill index=aw_oid master_index=brother_of master_table=objects

@default group=general

	@property billp_tb type=toolbar store=no no_caption=1
	@caption Arve toolbar

	@property name type=textbox table=objects field=name
	@caption Nimi

	@property comment type=textbox table=objects field=comment
	@caption Kommentaar lisale

	@property time_spent_desc type=textbox table=aw_crm_bill field=aw_time_spent_desc
	@caption Kulunud aeg tekstina

	@property bill_no type=textbox table=aw_crm_bill field=aw_bill_no
	@caption Number

	@property customer type=popup_search table=aw_crm_bill field=aw_customer reltype=RELTYPE_CUST clid=CL_CRM_COMPANY,CL_CRM_PERSON style=autocomplete
	@caption Klient

	@property impl type=popup_search style=relpicker table=aw_crm_bill field=aw_impl reltype=RELTYPE_IMPL
	@caption Arve esitaja

	@property bill_date type=date_select table=aw_crm_bill field=aw_date
	@caption Kuup&auml;ev

	@property bill_due_date_days type=textbox table=aw_crm_bill field=aw_due_date_days size=5
	@caption Makset&auml;htaeg (p&auml;evi)

	@property bill_due_date type=date_select table=aw_crm_bill field=aw_due_date
	@caption Tasumise kuup&auml;ev

	@property bill_recieved type=date_select table=aw_crm_bill field=aw_recieved default=-1
	@caption Laekumiskuup&auml;ev

	@property bill_trans_date type=date_select table=aw_crm_bill field=aw_trans_date default=-1
	@caption Kandekuup&auml;ev

	@property state type=select table=aw_crm_bill field=aw_state
	@caption Staatus

	@property partial_recieved type=textbox field=meta method=serialize
	@caption Osaline laekumine

	@property disc type=textbox table=aw_crm_bill field=aw_discount size=5 
	@caption Allahindlus (%)

	@property sum type=text table=aw_crm_bill field=aw_sum size=5 
	@caption Summa

	@property monthly_bill type=checkbox ch_value=1 table=aw_crm_bill field=aw_monthly_bill
	@caption Kuuarve

	@property language type=relpicker automatic=1 field=meta method=serialize reltype=RELTYPE_LANGUAGE
	@caption Keel

	@property rows_different_pages type=text field=meta method=serialize
	@caption Read erinevatel lehekülgedel

	@property bill_rows type=text store=no no_caption=1
	@caption Arveread 

	@property signers type=crm_participant_search reltype=RELTYPE_SIGNER multiple=1 table=objects field=meta method=serialize style=relpicker
	@caption Allkirjastajad

	@property udef1 type=checkbox ch_value=1 field=meta method=serialize
	@caption Kasutajadefineeritud muutuja 1

	@property udef2 type=checkbox ch_value=1 field=meta method=serialize
	@caption Kasutajadefineeritud muutuja 2

	@property udef3 type=checkbox ch_value=1 field=meta method=serialize
	@caption Kasutajadefineeritud muutuja 3

	@property udef4 type=checkbox ch_value=1 field=meta method=serialize
	@caption Kasutajadefineeritud muutuja 4

	@property udef5 type=checkbox ch_value=1 field=meta method=serialize
	@caption Kasutajadefineeritud muutuja 5


@default group=preview

	@property preview type=text store=no no_caption=1

@default group=preview_add

	@property preview_add type=text store=no no_caption=1

@default group=preview_w_rows

	@property preview_w_rows type=text store=no no_caption=1

@default group=tasks

	@property bill_tb type=toolbar store=no no_caption=1

	@property bill_task_list type=table store=no no_caption=1


@groupinfo tasks caption="Toimetused" submit=no
@groupinfo preview caption="Eelvaade"
@groupinfo preview_add caption="Arve Lisa"
@groupinfo preview_w_rows caption="Eelvaade ridadega"



@reltype TASK value=1 clid=CL_TASK
@caption &Uuml;lesanne

@reltype CUST value=2 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype IMPL value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Teostaja

@reltype LANGUAGE value=4 clid=CL_LANGUAGE
@caption Keel

@reltype ROW value=5 clid=CL_BILL_ROW
@caption Rida

@reltype PROD value=6 clid=CL_SHOP_PRODUCT
@caption Toode

@reltype SIGNER value=6 clid=CL_CRM_PERSON
@caption Allkirjastaja


*/

define("BILL_SUM", 1);
define("BILL_SUM_WO_TAX", 2);
define("BILL_SUM_TAX", 3);
define("BILL_AMT", 4);

class crm_bill extends class_base
{
	function crm_bill()
	{
		$this->init(array(
			"tpldir" => "crm/crm_bill",
			"clid" => CL_CRM_BILL
		));

		$this->states = array(
			0 => t("Koostamisel"),
			1 => t("Saadetud"),
			2 => t("Makstud"),
			3 => t("Laekunud"),
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "billp_tb":
				$this->_bill_tb($arr);
				break;

			case 'bill_task_list':
				$this->_bill_task_list($arr);
				break;

			case 'bill_tb':
				$this->_billt_tb($arr);
				break;
		
			case "bill_no":
				if ($prop["value"] == "")
				{
					$i = get_instance(CL_CRM_NUMBER_SERIES);
					$prop["value"] = $i->find_series_and_get_next(CL_CRM_BILL);
				}
				break;

			case "impl":
				if (!$arr["new"] && is_oid($arr["obj_inst"]->id()))
				{
					$ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_IMPL")));
					$prop["options"] = $ol->names();
				}
				$u = get_instance(CL_USER);
				$co = obj($u->get_current_company());
				$prop["options"][$co->id()] = $co->name();
				asort($prop["options"]);
				$prop["options"] = array("" => t("--vali--")) + $prop["options"];
				break;

			case "preview":
				$this->_preview($arr);
				break;

			case "preview_w_rows":
				$arr["all_rows"] = 1;
				$this->_preview($arr);
				break;

			case "preview_add":
				$this->_preview_add($arr);
				break;

			case "state":
				$prop["options"] = $this->states;
				break;

			case "bill_rows":
				$this->_bill_rows($arr);
				break;

			case "customer":
				$i = get_instance(CL_CRM_COMPANY);
				$cust = $i->get_my_customers();
				if (count($cust))
				{
					$ol = new object_list(array("oid" => $cust));
					$prop["options"] = $ol->names();
					if (is_oid($prop["value"]) && $this->can("view", $prop["value"]) && !isset($prop["options"][$prop["value"]]))
					{
						$tmp = obj($prop["value"]);
						$prop["options"][$prop["value"]] = $tmp->name();
					}
				}
				asort($prop["options"]);
				break;

			case "sum":
				$agreement_prices = $arr["obj_inst"]->meta("agreement_price");
				if($agreement_prices[0]["price"] && strlen($agreement_prices[0]["name"]) > 0)
				{
					$sum = 0;
					foreach($agreement_prices as $agreement_price)
					{
						$sum+= $agreement_price["sum"];
					}
					$prop["value"] = $sum;
				}
				$prop["value"] = number_format($prop["value"], 2);
				$curn = $arr["obj_inst"]->prop("customer.currency.name");
				$prop["value"] .= " ".($curn == "" ? "EEK" : $curn);
				break;

			case "rows_different_pages":
				$rows_in_page = $arr["obj_inst"]->meta("rows_in_page");
				$x = 0;
				$val = "";
				$count = 0;
				foreach($rows_in_page as $key => $row)
				{
					if($row){
						$val .=html::textbox(array(
							"name" => "rows_in_page[".$key."]",
							"value" => $row,
							"size" => 3
						));
						$count++;
					}
				}
				while(3 > $x)
				{
					$val .=html::textbox(array(
						"name" => "rows_in_page[".($x+$count)."]",
						"size" => 3
					));
					$x++;
				}
				$prop["value"] = $val;
				break;
			case "bill_trans_date":
				if($prop["value"] == -1) $prop["value"] = time();
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
			case "bill_no":
				if ($prop["value"] != $arr["obj_inst"]->prop("bill_no"))
				{
					// check that no bills have the same number
					$ol = new object_list(array(
						"class_id" => CL_CRM_BILL,
						"bill_no" => $prop["value"],
						"lang_id" => array(),
						"site_id" => array(),
						"oid" => new obj_predicate_not($arr["obj_inst"]->id())
					));
					if ($ol->count())
					{
						$prop["error"] = t("Sellise numberiga arve on juba olemas!");
					//	return PROP_ERROR;
					}

					$ser = get_instance(CL_CRM_NUMBER_SERIES);
					if (!$ser->number_is_in_series(CL_CRM_BILL, $prop["value"]))
					{
						$prop["error"] = t("Number ei ole seerias!");
				//		return PROP_ERROR;
					}
				}
				break;

			case "bill_rows":
				$this->_save_rows($arr);
				break;

			case "rows_different_pages":
				$arr["obj_inst"]->set_meta("rows_in_page" , $arr["request"]["rows_in_page"]);		
				break;

			case "state":
				// if state is set to paid and payment date is -1 or same as bill date 
				if ($prop["value"] == 2 && 
					($arr["obj_inst"]->prop("bill_date") == $arr["obj_inst"]->prop("bill_recieved") ||
					 $arr["obj_inst"]->prop("bill_recieved") < 300
					)
				)
				{
					$this->_set_recv_date = time();
				}
				break;

			case "customer":
				// check if the 
				if ($this->can("view", $prop["value"]) && $arr["obj_inst"]->prop("bill_due_date_days") == 0)
				{
					$cc = get_instance(CL_CRM_COMPANY);
					$crel = $cc->get_cust_rel(obj($prop["value"]));
					if ($crel)
					{
						$this->_set_bddd = $crel->prop("bill_due_date_days");
					}
				}
		}
		return $retval;
	}	

	function num($a)
	{
		return str_replace(",", ".", $a);
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["reconcile_price"] = -1;
	}

	function _init_bill_rows_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimetus"),
		));

		/*$t->define_field(array(
			"name" => "code",
			"caption" => t("Kood")
		));*/

		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuup&auml;ev"),
		));

		$t->define_field(array(
			"name" => "unit",
			"caption" => t("&Uuml;hik"),
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
		));

		$t->define_field(array(
			"name" => "amt",
			"caption" => t("Kogus"),
		));

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
		));

		$t->define_field(array(
			"name" => "prod",
			"caption" => t("Artikkel")
		));
		$t->define_field(array(
			"name" => "person",
			"caption" => t("Isik")
		));
	
		$t->define_field(array(
			"name" => "has_tax",
			"caption" => t("+KM?"),
		));

		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Vali"),
		));
	}

	function _bill_rows($arr)
	{
		classload("vcl/table");
		$t = new vcl_table();
		$this->_init_bill_rows_t($t);

		$sum = 0;

		$task_i = get_instance(CL_TASK);

		$prods = array("" => t("--vali--"));
		// get prords from co
		$u = get_instance(CL_USER);
		$co = obj($u->get_current_company());
		$wh = $co->get_first_obj_by_reltype("RELTYPE_WAREHOUSE");
		if ($wh)
		{
			$wh_i = $wh->instance();
			$pkts = $wh_i->get_packet_list(array(
				"id" => $wh->id()
			));
			foreach($pkts as $pko)
			{
				$prods[$pko->id()] = $pko->name();
			}
		}
		$rows = $this->get_bill_rows($arr["obj_inst"]);
		if (!is_oid($arr["obj_inst"]->id()))
		{
			$rows[] = array(
				"id" => -1,
				"date" => date("d.m.Y", time()),
				"amt" => 0,
				"sum" => 0
			);
		}
		$pps = get_instance("applications/crm/crm_participant_search");
		$default_row_jrk = 0;
		foreach($rows as $row)
		{
			$t_inf = $row;
			$id = $row["id"];
			$r_prods = $prods;
			if (!isset($r_prods[$t_inf["prod"]]) && $this->can("view", $t_inf["prod"]))
			{
				$prodo = obj($t_inf["prod"]);
				$r_prods[$t_inf["prod"]] = $prodo->name();
			}
			$r_pers = array("" => t("--vali--"));
			foreach($row["persons"] as $rp_id)
			{
				if ($this->can("view", $rp_id))
				{
					$rp_o = obj($rp_id);
					$r_pers[$rp_id] = $rp_o->name();
				}
			}
			//miski suva järjekorranuumbrite genereerimine... kui on vaja
			if($default_row_jrk < $t_inf["jrk"]) $default_row_jrk = $t_inf["jrk"];
			if(!$t_inf["jrk"]) $t_inf["jrk"] = $default_row_jrk;
			$default_row_jrk = $default_row_jrk + 10;

			$t->define_data(array(
			"name" => html::textbox(array(
					"name" => "rows[$id][jrk]",
					"value" => $t_inf["jrk"],
					"size" => 3
				)).html::textbox(array(
					"name" => "rows[$id][comment]",
					"value" => $t_inf["comment"],
					"size" => 41
				))."<br>".html::textarea(array(
					"name" => "rows[$id][name]",
					"value" => $t_inf["name"],
					"rows" => 5,
					"cols" => 45
				)),
				"code" => html::textbox(array(
					"name" => "rows[$id][code]",
					"value" => $t_inf["code"],
					"size" => 10
				)),
				"date" => html::textbox(array(
					"name" => "rows[$id][date]",
					"value" => $t_inf["date"],
					"size" => 8
				)),
				"unit" => html::textbox(array(
					"name" => "rows[$id][unit]",
					"value" => $t_inf["unit"],
					"size" => 3
				)),
				"price" => html::textbox(array(
					"name" => "rows[$id][price]",
					"value" => $t_inf["price"],
					"size" => 5
				)),
				"amt" => html::textbox(array(
					"name" => "rows[$id][amt]",
					"value" => $t_inf["amt"],
					"size" => 5
				)),
				"sum" => $t_inf["sum"],
				"has_tax" => html::checkbox(array(
					"name" => "rows[$id][has_tax]",
					"ch_value" => 1,
					"checked" => $t_inf["has_tax"] == 1 ? true : false
				)),
				"prod" => html::select(array(
					"name" => "rows[$id][prod]",
					"options" => $r_prods,
					"value" => $t_inf["prod"]
				))." ".html::popup(array(
					"width" => 800,
					"height" => 500,
					"scrollbars" => 1,
					"url" => $this->mk_my_orb("do_search", array("pn" => "rows[$id][prod]", "clid" => CL_SHOP_PRODUCT, "tbl_props" => array("name", "comment", "tax_rate")), "popup_search"),
					"caption" => t("Vali")
				)),
				"sel" => html::checkbox(array(
					"name" => "sel_rows[]",
					"value" => $id
				)),
				"person" => html::select(array(
					"name" => "rows[$id][person]",
					"options" => $r_pers,
					"value" => $row["persons"],
					"multiple" => 1
				)).$pps->get_popup_search_link(array(
					"pn" => "rows[$id][person]",
					"multiple" => 1,
					"clid" => array(CL_CRM_PERSON)
				))
			));
			$sum += $t_inf["sum"];
		}
		$t->set_sortable(false);

		if($arr["obj_inst"]->meta("agreement_price"))
		{
			$sum = 0;
			foreach($arr["obj_inst"]->meta("agreement_price") as $agreement_price)
			{
				$sum+= $agreement_price["sum"];
			}
		}

		if ($arr["obj_inst"]->prop("disc") > 0)
		{
			$sum -= $sum * ($arr["obj_inst"]->prop("disc") / 100.0);
		}
		$sum = $this->round_sum($sum);
		if ($arr["obj_inst"]->prop("sum") != $sum)
		{
			$arr["obj_inst"]->set_prop("sum", $sum);
			$arr["obj_inst"]->save();
		}

		//kokkuleppe hind
		$agreement_prices = $arr["obj_inst"]->meta("agreement_price");
		if(!is_array($agreement_prices[0]) && is_array($agreement_prices)) $agreement_prices = array($agreement_prices);//endiste kokkuleppehindade jaoks mis pold massiivis
		if($agreement_prices == null) $agreement_prices = array();
		if(is_array($agreement_prices[0]))
		{
			$agreement_prices[] = array();
			$x = 0;
			foreach($agreement_prices as $key => $agreement_price)
			{
				if(($agreement_price["name"] && $agreement_price["price"]) || !$done_new_line)
				{
					$t->define_data(array(
						"name" => t("Kokkuleppehind")." ".($x+1)
						."<br>".html::textarea(array(
							"name" => "agreement_price[".$x."][name]",
							"value" => $agreement_price["name"],
							"rows" => 5,
							"cols" => 45
						)),
						"code" => html::textbox(array(
							"name" => "agreement_price[".$x."][code]",
							"value" => $agreement_price["code"],
							"size" => 10
						)),
						"date" => html::textbox(array(
							"name" => "agreement_price[".$x."][date]",
							"value" => $agreement_price["date"],
							"size" => 8
						)),
						"unit" => html::textbox(array(
							"name" => "agreement_price[".$x."][unit]",
							"value" => $agreement_price["unit"],
							"size" => 3
						)),
						"price" => html::textbox(array(
							"name" => "agreement_price[".$x."][price]",
							"value" => $agreement_price["price"],
							"size" => 5
						)),
						"amt" => html::textbox(array(
							"name" => "agreement_price[".$x."][amt]",
							"value" => $agreement_price["amt"],
							"size" => 5
						)),
						"sum" => $agreement_price["sum"],
						"has_tax" => html::checkbox(array(
							"name" => "agreement_price[".$x."][has_tax]",
							"ch_value" => 1,
							"checked" => $agreement_price["has_tax"] == 1 ? true : false
						)),
						"prod" => html::select(array(
							"name" => "agreement_price[".$x."][prod]",
							"options" => $r_prods,
							"value" => $agreement_price["prod"]
						))." ".html::popup(array(
							"width" => 800,
							"height" => 500,
							"scrollbars" => 1,
							"url" => $this->mk_my_orb("do_search", array("pn" => "agreement_price[".$x."][prod]", "clid" => CL_SHOP_PRODUCT, "tbl_props" => array("name", "comment", "tax_rate")), "popup_search"),
							"caption" => t("Vali")
						)),
						"sel" => html::checkbox(array(
							"name" => "sel_rows[]",
							"value" => $id
						)),
						"person" => html::select(array(
							"name" => "agreement_price[".$x."][person]",
							"options" => $r_pers,
							"value" => $row["persons"],
							"multiple" => 1
						)).$pps->get_popup_search_link(array(
							"pn" => "agreement_price[".$x."][person]",
							"multiple" => 1,
							"clid" => array(CL_CRM_PERSON)
						))
					));
					$x++;
					if(!($agreement_price["name"] && $agreement_price["price"]))$done_new_line = 1;
				}
			}
		}
		$arr["prop"]["value"] = $t->draw();
	}

	function get_sum($bill)
	{
		$agreement = $bill->meta("agreement_price");
		if($agreement["sum"] && $agreement["price"] && strlen($agreement["name"]) > 0) return $agreement["sum"];
		if($agreement[0]["sum"] && $agreement[0]["price"] && strlen($agreement[0]["name"]) > 0) 
		{
			$sum = 0;
			foreach($agreement as $a)
			{
				$sum.= $agreement["sum"];
			}
			return $sum;
		}
		return $bill->prop("sum");
	}

	function _calc_sum($bill)
	{
		$rows = $this->get_bill_rows($bill);
		$sum = 0;
		foreach($rows as $row)
		{
			$sum+= $row["sum"];
		}

		if ($bill->prop("disc") > 0)
		{
			$sum -= $sum * ($bill->prop("disc") / 100.0);
		}

		return $this->round_sum($sum);
	}

	function round_sum($sum)
	{
		$u = get_instance(CL_USER);
		$co = $u->get_current_company();
		$co = obj($co);
		if(is_object($co) && $co->prop("round"))
		{
			$round = (double)$co->prop("round");
			$min_stuff = $sum/$round - ($sum/$round - (int)($sum/$round));
			$min_diff = $sum - $min_stuff*$round;
			$max_diff = ($sum - ($min_stuff + 1) * $round)*-1;
			if($max_diff > $min_diff) $sum = $min_stuff*$round;
			else $sum = ($min_stuff+1)*$round;
		}
		 return $sum;
	}

	function callback_pre_save($arr)
	{
		if ($this->_set_bddd)
		{
			$arr["obj_inst"]->set_prop("bill_due_date_days", $this->_set_bddd);
		}
		$arr["obj_inst"]->set_prop("sum", $this->_calc_sum($arr["obj_inst"]));
		$bt = $arr["obj_inst"]->prop("bill_date");
		$arr["obj_inst"]->set_prop("bill_due_date", 
			mktime(3,3,3, date("m", $bt), date("d", $bt) + $arr["obj_inst"]->prop("bill_due_date_days"), date("Y", $bt))
		);

		if ($this->_set_recv_date)
		{
			$arr["obj_inst"]->set_prop("bill_recieved", $this->_set_recv_date);
		}
	}

	function _preview($arr)
	{
		$arr["prop"]["value"] = $this->show(array("id" => $arr["obj_inst"]->id(), "all_rows" => $arr["all_rows"]));
	}

	function _preview_add($arr)
	{
		if($arr["obj_inst"]->meta("rows_in_page"))
		$page = 0;
		if(array_sum($arr["obj_inst"]->meta("rows_in_page")) > 0)
		{
			$this->_preview_popup(array(
				"rows_in_page" => $arr["obj_inst"]->meta("rows_in_page"),
				"page" => $page,
				"id" => $arr["obj_inst"]->id(),
			));
		}
		if($page == 0) $arr["prop"]["value"] = die($this->show_add(array("id" => $arr["obj_inst"]->id())));
	}

	/**
		@attrib name=_preview_popup
	/**/
	function _preview_popup($arr)
	{	global $id, $rows_in_page, $page;
		extract($arr);
		$row = array_shift($rows_in_page);
		$between = explode("-", $row);
		$link = $this->mk_my_orb("_preview_popup", array("id" => $id, "rows_in_page" => $rows_in_page , "page" => ($page + 1)));
		if(array_sum($rows_in_page)){
			$popup = 
			'<script name= javascript>window.open("'.$link.'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=800, width=720")</script>';
			$not_last_page = 1;
		}
		die($this->show_add(array("id" => $id, "page" => $page, "between" => $between, "not_last_page" => $not_last_page,)) . $popup);
	}

	function collocate_rows($grp_rows)
	{
		$new_rows = array();
		foreach($grp_rows as $key => $grp_row)
		{
			while(true)
			{
				if(sizeof($grp_row) > 0) $row = array_shift($grp_row);
				else break;
				$new_line = 1;
				foreach($new_rows as $n_key => $new_row)
				{
					if($new_row["price"] == $row["price"] && ($new_row["comment"] == $row["comment"] || !$row["comment"]))
					{
						$new_rows[$n_key]["sum_wo_tax"] = $new_rows[$n_key]["sum_wo_tax"] + $row["sum_wo_tax"];
						$new_rows[$n_key]["tax"] = $new_rows[$n_key]["tax"] + $row["tax"];
						$new_rows[$n_key]["sum"] = $new_rows[$n_key]["sum"] + $row["sum"];
						$new_rows[$n_key]["tot_amt"] = $new_rows[$n_key]["tot_amt"] + $row["tot_amt"];
						$new_rows[$n_key]["tot_cur_sum"] = $new_rows[$n_key]["tot_cur_sum"] + $row["tot_cur_sum"];
						$new_line = 0;
						break;
					}
				}
				$row["key"] = $n_key;
				if($new_line) $new_rows[] = $row;
			}
		}
		$grp_rows = array();
		foreach($new_rows as $key => $new_row)
		{
			$grp_rows[$new_row["key"]][$new_row["price"]] = $new_row;
		}
		return ($grp_rows);
	}

	function collocate($grp_rows)
	{
		$new_rows = array();
		foreach($grp_rows as $key => $row)
		{
			$new_line = 1;
			foreach($new_rows as $n_key => $new_row)
			{
				if($new_row["price"] == $row["price"] && ($new_row["comment"] == $row["comment"] || !$row["comment"]))
				{
					$new_rows[$n_key]["sum"] = $new_rows[$n_key]["sum"] + $row["sum"];
					$new_rows[$n_key]["amt"] = $new_rows[$n_key]["amt"] + $row["amt"];
					$new_line = 0;
					break;
				}
			}
			if($new_line) $new_rows[] = $row;
		}
		return ($new_rows);
	}

	function show($arr)
	{
		$b = obj($arr["id"]);

		$tpl = "show";
		$lc = "et";
		if ($this->can("view", $b->prop("language")))
		{
			$lo = obj($b->prop("language"));
			$lc = $lo->prop("lang_acceptlang");
		}
		$tpl .= "_".$lc;

		if ($this->read_site_template($tpl.".tpl", true) === false)
		{
			$this->read_site_template("show.tpl");
		}

		$ord = obj();
		$ord_cur = obj();
		$ord_ct_prof = "";
		if ($this->can("view", $b->prop("customer")))
		{
			$ord = obj($b->prop("customer"));
			$_ord_ct = $ord->prop("contact_person");
			if (!$_ord_ct)
			{
				$_ord_ct = $ord->prop("firmajuht");
			}
			$ord_ct = "";
			if ($this->can("view", $_ord_ct))
			{
				$ct = obj($_ord_ct);
				$ord_ct = $ct->name();

				// get profession for contact_person
				$ol = new object_list($ct->connections_from(array('type' => 'RELTYPE_RANK')));
				$ord_ct_prof = join(", ", $ol->names());
			}
			$prop = "contact";
			if ($ord->class_id() == CL_CRM_PERSON)
			{
				$prop = "address";
			}
			if ($this->can("view", $ord->prop($prop)))
			{
				//$ct = obj($ord->prop("contact"));
				//$ord_addr = $ct->name()." ".$ct->prop("postiindeks");

				$ct = obj($ord->prop($prop));
				$ap = array($ct->prop("aadress"));
				if ($ct->prop("linn"))
				{
					$ap[] = $ct->prop_str("linn");
				}
				$aps = join(", ", $ap)."<br>";
				$aps .= $ct->prop_str("maakond");
				$aps .= " ".$ct->prop("postiindeks");
				$ord_addr = $aps;//$ct->name()." ".$ct->prop("postiindeks");
				$ord_country = $ct->prop_str("riik");
			
				//riigi tõlge, kui on inglise keeles
				if($b->prop("language") && is_oid($ct->prop("riik")))
				{
					$lo = obj($b->prop("language"));
					$lc = $lo->prop("lang_acceptlang");
					$country_obj = obj($ct->prop("riik"));
					if($country_obj->prop("name_en") && $lc == "en") $ord_country = $country_obj->prop("name_en");
				}
			}

			if ($this->can("view", $ord->prop("currency")))
			{
				$ord_cur = obj($ord->prop("currency"));
			}
			$cust_no = $ord->prop("code");
		}
		$logo = "";
		$impl = obj();
		if ($this->can("view", $b->prop("impl")))
		{
			$impl = obj($b->prop("impl"));

			$ba = "";
			foreach($impl->connections_from(array("type" => "RELTYPE_BANK_ACCOUNT")) as $c)
			{
				$acc = $c->to();
				$bank = obj();
				if ($this->can("view", $acc->prop("bank")))
				{
					$bank = obj($acc->prop("bank"));
				}
				$this->vars(array(
					"bank_name" => $bank->name(),
					"acct_no" => $acc->prop("acct_no"),
					"bank_iban" => $acc->prop("iban_code")
				));

				$ba .= $this->parse("BANK_ACCOUNT");
			}

			$this->vars(array(
				"BANK_ACCOUNT" => $ba
			));
			$logo_o = $impl->get_first_obj_by_reltype("RELTYPE_ORGANISATION_LOGO");
			if ($logo_o)
			{
				$logo_i = $logo_o->instance();
				$logo = $logo_i->make_img_tag_wl($logo_o->id());
				$logo_url = $logo_i->get_url_by_id($logo_o->id());
			}

			$impl_phone = $impl->prop_str("phone_id");

			if ($this->can("view", $impl->prop("contact")))
			{
				$ct = obj($impl->prop("contact"));
				$ap = array($ct->prop("aadress"));
				if ($ct->prop("linn"))
				{
					$ap[] = $ct->prop_str("linn");
				}
				$aps = join(", ", $ap)."<br>";
				$aps .= $ct->prop_str("maakond");
				$aps .= " ".$ct->prop("postiindeks");
				$impl_addr = $aps;//$ct->name()." ".$ct->prop("postiindeks");
				if ($this->can("view", $ct->prop("riik")))
				{
					$riik = obj($ct->prop("riik"));
					//see tundub küll mõttetu...et nagu need tingimused on täidetud, siis mõni rida allpool tehakse täpselt sama
//					if( $riik->name() != $ord_country)
//					{
//						$ord_addr .= " ".$ord_country;
//					}
					$impl_phone = $riik->prop("area_code")." ".$impl_phone;
				}
			}

			if(!is_object($riik) ||  $riik->name() != $ord_country)
			{
				$ord_addr .= " ".$ord_country;
			}
			if ($this->can("view", $impl->prop("email_id")))
			{
				$mail = obj($impl->prop("email_id"));
				$impl_mail = $mail->prop("mail");
			}

		}

		$bpct = $ord->prop("bill_penalty_pct");
		if (!$bpct)
		{
			$bpct = $impl->prop("bill_penalty_pct");
		}
		$this->vars(array(
			"orderer_name" => $ord->name(),
			"orderer_code" => $cust_no,
			"orderer_corpform" => $ord->prop("ettevotlusvorm.shortname"),
			"ord_penalty_pct" => number_format($bpct, 2),
			"ord_currency_name" => $ord->prop_str("currency") == "" ? "EEK" : $ord->prop_str("currency"),
			"orderer_addr" => $ord_addr,
			"orderer_kmk_nr" => $ord->prop("tax_nr"),
			"bill_no" => $b->prop("bill_no"),
			"impl_logo" => $logo,
			"impl_logo_url" => $logo_url,
			"bill_date" => $b->prop("bill_date"),
			"payment_due_days" => $b->prop("bill_due_date_days"),
			"bill_due" => date("d.m.Y", $b->prop("bill_due_date")),
			"orderer_contact" => $ord_ct,
			"orderer_contact_profession" => $ord_ct_prof,
			"comment" => $b->prop("notes"),
			"impl_name" => $impl->name(),
			"impl_address" => $impl_addr,
			"impl_reg_nr" => $impl->prop("reg_nr"),
			"impl_kmk_nr" => $impl->prop("tax_nr"),
			"impl_phone" => $impl_phone,
			"impl_fax" => $impl->prop_str("telefax_id"),
			"impl_email" => $impl_mail,
			"impl_url" => $impl->prop_str("url_id"),
		));
		if($b->prop("udef1")) $this->vars(array("userch1_checked" => $this->parse("userch1_checked")));
		if($b->prop("udef2")) $this->vars(array("userch2_checked" => $this->parse("userch2_checked")));
		if($b->prop("udef3")) $this->vars(array("userch3_checked" => $this->parse("userch3_checked")));
		if($b->prop("udef4")) $this->vars(array("userch4_checked" => $this->parse("userch4_checked")));
		if($b->prop("udef5")) $this->vars(array("userch5_checked" => $this->parse("userch5_checked")));

		if ($ord->prop("tax_nr") != "")
		{
			$this->vars(array(
				"HAS_KMK_NR" => $this->parse("HAS_KMK_NR")
			));
		}
		if ($ord_ct_prof != "")
		{
			$this->vars(array(
				"HAS_ORDERER_CONTACT_PROF" => $this->parse("HAS_ORDERER_CONTACT_PROF")
			));
		}
		
		$rs = array();
		$sum_wo_tax = 0;
		$tax = 0;
		$sum = 0;
		
		$agreement = $b->meta("agreement_price");
		if($agreement["price"] && $agreement["name"]) $agreement = array($agreement); // kui on vanast ajast jäänud
		if($agreement[0]["price"] && strlen($agreement[0]["name"]) > 0 )//kui kokkuleppehind on täidetud, siis rohkem ridu ei ole näha
		{
			$bill_rows = $agreement;
//			$agreement_price_data = $this->get_agreement_row($agreement);
//			extract($agreement_price_data);
		}
		else
		{
			$bill_rows = $this->get_bill_rows($b);
		}	
		$brows = $bill_rows; //moment ei tea miks see topelt tuleb... igaks juhuks ei võtnud maha... hiljem käib miski reset
		$grp_rows = array();
		$tax_rows = array();
		$_no_prod_idx = -1;
		$has_nameless_rows = 0;//miski muutuja , et kui see üheks muutub, siis lisab liidab kõik read kokku
	
		foreach($brows as $row)
		{
			if ($row["is_oe"])
			{
				continue;
			}
			$cur_tax = 0;
			$cur_sum = 0;
			$tax_rate = 0;
			if (!$this->can("view", $row["prod"]) && $row["has_tax"] == 1)
			{
				$tax_rate = 0.18;
			}
			else
			if ($this->can("view", $row["prod"]))
			{
				$prod_o = obj($row["prod"]);
				$tax_rate = (double)$prod_o->prop("tax_rate.tax_amt") / 100.0;
			}

			if (!$this->can("view", $row["prod"]))
			{
				$row["prod"] = --$_no_prod_idx;
			}
			if ($tax_rate > 0)
			{
				// tax needs to be added
				$cur_sum = $row["sum"];
				$cur_tax = ($row["sum"] * $tax_rate);
				$cur_pr = $this->num($row["price"]);
			}	
			else
			{
				// tax does not need to be added, tax free it seems
				$cur_sum = $row["sum"];
				$cur_tax = 0;
				$cur_pr = $this->num($row["price"]);
			}

			if ($arr["all_rows"] == 1)
			{
				$row["prod"] = gen_uniq_id();
			}

			$tax_rows["$tax_rate"] += $cur_tax;
			$unp = $row["price"].$row["comment"];
			$grp_rows[$row["prod"]][$unp]["sum_wo_tax"] += $cur_sum;
			$grp_rows[$row["prod"]][$unp]["tax"] += $cur_tax;
			$grp_rows[$row["prod"]][$unp]["sum"] += ($cur_tax+$cur_sum);
			$grp_rows[$row["prod"]][$unp]["unit"] = $row["unit"];
			$grp_rows[$row["prod"]][$unp]["price"] = $row["price"];
			$grp_rows[$row["prod"]][$unp]["date"] = $row["date"];
			$grp_rows[$row["prod"]][$unp]["jrk"] = $row["jrk"];
			$grp_rows[$row["prod"]][$unp]["tot_amt"] += $row["amt"];
			$grp_rows[$row["prod"]][$unp]["id"] = $row["id"];
			$grp_rows[$row["prod"]][$unp]["tot_cur_sum"] += $cur_sum;
			$grp_rows[$row["prod"]][$unp]["name"] = $row["name"];
			$grp_rows[$row["prod"]][$unp]["comment"] = $row["comment"];

			if (empty($grp_rows[$row["prod"]][$unp]["comment"]))
			{
				$grp_rows[$row["prod"]][$unp]["comment"] = $row["comment"];
			}
			$sum_wo_tax += $cur_sum;
			$tax += $cur_tax;
			$sum += ($cur_tax+$cur_sum);
			$tot_amt += $row["amt"];
			$tot_cur_sum += $cur_sum;
			if(!strlen($row["comment"])>0)$has_nameless_rows = 1;
		}

		$fbr = reset($brows);
		//koondab sama nimega ja nimetud ühe hinnaga read kokku
		if(!$arr["all_rows"]) $grp_rows = $this->collocate_rows($grp_rows);
		foreach($grp_rows as $prod => $grp_rowa)
		{
			foreach($grp_rowa as $key => $grp_row)
			{
				if (!empty($grp_row["comment"]))
				{
					$desc = $grp_row["comment"];
				}
				else
				if ($this->can("view", $prod))
				{
					$po = obj($prod);
					$desc = $po->comment();
				}
				else
				{
					$desc = $grp_row["name"];
				}

				//kui vaid ühel real on nimi... et siis arve eeltvaates moodustuks nendest 1 rida
//				if(!$arr["all_rows"] && $has_nameless_rows)
//				{
//					if(!strlen($grp_row["comment"])>0 && $primary_row_is_set) break;
//					{
//						$grp_row["tot_cur_sum"] = $tot_cur_sum;
//						$grp_row["tot_amt"] = $tot_amt;
//						$primary_row_is_set = 1;
//					}
//				}
				$this->vars(array(
					"unit" => $grp_row["unit"],
					"amt" => $grp_row["tot_amt"],
					"price" => number_format(($grp_row["tot_cur_sum"] / $grp_row["tot_amt"]),2,".", " "),
					"sum" => number_format($grp_row["tot_cur_sum"], 2, ".", " "),
					"desc" => $desc,
					"date" => "" 
				));
				$rs[] = array("str" => $this->parse("ROW"), "date" => $grp_row["date"] , "jrk" => $grp_row["jrk"] , "id" => $grp_row["id"],);
			}
		}
	
		foreach($bill_rows as $row)
		{
			if (!$row["is_oe"])
			{
				continue;
			}
			$cur_tax = 0;
			$cur_sum = 0;
	
			$tax_rate = 0;
			if (!$this->can("view", $row["prod"]) && $row["has_tax"] == 1)
			{
				$tax_rate = 0.18;
			}
			else
			if ($this->can("view", $row["prod"]))
			{
				$prod_o = obj($row["prod"]);
				$tax_rate = (double)$prod_o->prop("tax_rate.tax_amt") / 100.0;
			}
		
			if ($tax_rate > 0)
			{
				// tax needs to be added
				$cur_sum = $row["sum"];
				$cur_tax = ($row["sum"] * $tax_rate);
				$cur_pr = $this->num($row["price"]);
			}	
			else
			{
				// tax does not need to be added, tax free it seems
				$cur_sum = $row["sum"];
				$cur_tax = 0;
				$cur_pr = $this->num($row["price"]);
			}
			$tax_rows[$tax_rate] += $cur_tax;
			$this->vars(array(
				"unit" => $row["unit"],
				"amt" => $row["amt"],
				"price" => number_format($cur_pr, 2, ".", " "),
				"sum" => number_format($cur_sum, 2, ".",  " "),
				"desc" => $row["name"],
				"date" => $row["date"] 
			));
	
			$rs[] = array("str" => $this->parse("ROW"), "date" => $row["date"] , "jrk" => $row["jrk"] , "id" => $grp_row["id"],);
			$sum_wo_tax += $cur_sum;
			$tax += $cur_tax;
			$sum += ($cur_tax+$cur_sum);
		}
			
		usort($rs, array(&$this, "__br_sort"));
		foreach($rs as $idx => $ida)
		{
			$rs[$idx] = $ida["str"];
		}
		
		$tax_rows_str = "";
		foreach($tax_rows as $tax_rate => $tax_amt)
		{
			if ($tax_rate > 0 || true)
			{
				$this->vars(array(
					"tax_rate" => floor($tax_rate*100.0),
					"tax" => number_format($tax_amt, 2)
				));
				$tax_rows_str .= $this->parse("TAX_ROW");
			}
		}

		$sigs = "";
		
		foreach((array)$b->prop("signers") as $signer)
		{
			if (!$this->can("view", $signer))
			{
				continue;
			}
			$signer_p = obj($signer);
			$this->vars(array(
				"signer_person" => $signer_p->name()
			));
			$sigs .= $this->parse("SIGNATURE");
		}

	//	$sum_wo_tax = $this->round_sum($sum_wo_tax);
		$sum = $this->round_sum($sum);
		$this->vars(array(
			"SIGNATURE" => $sigs,
			"TAX_ROW" => $tax_rows_str,
			"ROW" => join("", $rs),
			"total_wo_tax" => number_format($sum_wo_tax, 2,".", " "),
			"tax" => number_format($tax, 2,".", " "),
			"total" => number_format($sum, 2, ".", " "),
			"total_text" => locale::get_lc_money_text($sum, $ord_cur, $lc)
		));
		$res =  $this->parse();
		if (false && !$_GET["gen_print"])
		{
			$res = html::href(array(
				"url" => aw_url_change_var("gen_print", 1),
				"caption" => t("Prinditav arve")
			)).$res;
			return $res;
		}

		if ($_GET["openprintdialog"] == 1)
		{
			$res .= "<script language='javascript'>setTimeout('window.close()',10000);window.print();if (navigator.userAgent.toLowerCase().indexOf('msie') == -1) {window.close(); }</script>";
		}
		if ($_GET["openprintdialog_b"] == 1)
		{
			$url = aw_url_change_var("group", "preview_add", aw_url_change_var("openprintdialog", 1));
			$res .= "<script language='javascript'>setTimeout('window.location.href=\"$url\"',10000);window.print();if (navigator.userAgent.toLowerCase().indexOf('msie') == -1) {window.location.href='$url'; }</script>";
		}
		die($res);
	}

	function get_bill_rows($bill)
	{
		if (!is_oid($bill->id()))
		{
			return array();
		}
		$inf = array();
		$cons = $bill->connections_from(array("type" => "RELTYPE_ROW"));
		if (!count($cons))
		{
			// create new empty bill row
			$br = obj();
			$br->set_class_id(CL_CRM_BILL_ROW);
			$br->set_parent($bill->id());
			$br->save();
			$bill->connect(array(
				"to" => $br->id(),
				"type" => "RELTYPE_ROW"
			));
			$cons = $bill->connections_from(array("type" => "RELTYPE_ROW"));
		}
		// bill rows are objects connected and get info copied into them from task rows
		foreach($cons as $c)
		{
			$row = $c->to();
			$kmk = "";
			if ($this->can("view", $row->prop("prod")))
			{
				$prod = obj($row->prop("prod"));
				if ($this->can("view", $prod->prop("tax_rate")))
				{
					$tr = obj($prod->prop("tax_rate"));
					$kmk = $tr->prop("code");
				}
			}

			$ppl = array();
			foreach((array)$row->prop("people") as $p_id)
			{
				if ($this->can("view", $p_id))
				{
					$ppl[$p_id] = $p_id;	
				}
			}
			$rd = array(
				"amt" => $row->prop("amt"),
				"prod" => $row->prop("prod"),
				"name" => $row->prop("desc"),
				"comment" => $row->prop("comment"),
				"price" => $row->prop("price"),
				"sum" => str_replace(",", ".", $row->prop("amt")) * str_replace(",", ".", $row->prop("price")),
				"km_code" => $kmk,
				"unit" => $row->prop("unit"),
				"jrk" => $row->meta("jrk"),
				"id" => $row->id(),
				"is_oe" => $row->prop("is_oe"),
				"has_tax" => $row->prop("has_tax"),
				"date" => $row->prop("date"),
				"id" => $row->id(),
				"persons" => $ppl
			);
			$inf[] = $rd;
		}
		usort($inf, array(&$this, "__br_sort"));
		//sotrimine järjekorranumbri järgi
//		foreach ($inf as $key => $row) {
//		   $volume[$key]  = $row['jrk'];
//		   $edition[$key] = $row;
//		}
//		array_multisort($volume, SORT_ASC, $edition, SORT_DESC, $inf);
		return $inf;
	}

	function __br_sort($a, $b)
	{
		$a_date = $a["date"];
		$b_date = $b["date"];
		list($a_d, $a_m, $a_y) = explode(".", $a_date);
		list($b_d, $b_m, $b_y) = explode(".", $b_date);
		$a_tm = mktime(0,0,0, $a_m, $a_d, $a_y);
		$b_tm = mktime(0,0,0, $b_m, $b_d, $b_y);
		//echo $a["jrk"] < $b["jrk"] ? -1 :($a["jrk"] > $b["jrk"] ? 1 : ($a_tm >  $b_tm ? 1 : ($a_tm == $b_tm ? 0 : -1)));
		return  $a["jrk"] < $b["jrk"] ? -1 :
			($a["jrk"] > $b["jrk"] ? 1:
				($a_tm >  $b_tm ? 1:
					($a_tm == $b_tm ? ($a["id"] > $b["id"] ? 1 : -1): -1)
				)
			);
	}

	function show_add($arr)
	{
		$b = obj($arr["id"]);
		$bill_rows = $this->get_bill_rows($b);
		//lükkab mõned read kokku ja liidab summa , ning koguse.võibolla saaks sama funktsiooni teise sarnase asemel ka kasutada, kui seda varem teha äkki
//		$bill_rows = $this->collocate($bill_rows);
		
		//tühja kirjeldusega read välja
		foreach($bill_rows as $key => $val)
		{
			if(!(strlen($val["name"]) > 0)) unset($bill_rows[$key]);
		}
		
		
		$tpl = "show_add";
		$lc = "et";
		if ($this->can("view", $b->prop("language")))
		{
			$lo = obj($b->prop("language"));
			$lc = $lo->prop("lang_acceptlang");
		}
		$tpl .= "_".$lc;

		if ($this->read_site_template($tpl.".tpl", true) === false)
		{
			$this->read_site_template("show_add.tpl");
		}

		$ord = obj();
		$ord_cur = obj();
		if ($this->can("view", $b->prop("customer")))
		{
			$ord = obj($b->prop("customer"));
			$_ord_ct = $ord->prop("firmajuht");
			$ord_ct = "";
			if ($this->can("view", $_ord_ct))
			{
				$ct = obj($_ord_ct);
				$ord_ct = $ct->name();
			}
			if ($this->can("view", $ord->prop("contact")))
			{
				//$ct = obj($ord->prop("contact"));
				//$ord_addr = $ct->name()." ".$ct->prop("postiindeks");

				$ct = obj($ord->prop("contact"));
				$ap = array($ct->prop("aadress"));
				if ($ct->prop("linn"))
				{
					$ap[] = $ct->prop_str("linn");
				}
				$aps = join(", ", $ap)."<br>";
				$aps .= $ct->prop_str("maakond");
				$aps .= " ".$ct->prop("postiindeks");
				$ord_addr = $aps;//$ct->name()." ".$ct->prop("postiindeks");
			}

			if ($this->can("view", $ord->prop("currency")))
			{
				$ord_cur = obj($ord->prop("currency"));
			}
		}
		$logo = "";
		$impl = obj();
		if ($this->can("view", $b->prop("impl")))
		{
			$impl = obj($b->prop("impl"));

			$ba = "";
			foreach($impl->connections_from(array("type" => "RELTYPE_BANK_ACCOUNT")) as $c)
			{
				$acc = $c->to();
				$bank = obj();
				if ($this->can("view", $acc->prop("bank")))
				{
					$bank = obj($acc->prop("bank"));
				}
				$this->vars(array(
					"bank_name" => $bank->name(),
					"acct_no" => $acc->prop("acct_no"),
					"bank_iban" => $acc->prop("iban_code")
				));

				$ba .= $this->parse("BANK_ACCOUNT");
			}

			$this->vars(array(
				"BANK_ACCOUNT" => $ba
			));
			$logo_o = $impl->get_first_obj_by_reltype("RELTYPE_ORGANISATION_LOGO");
			if ($logo_o)
			{
				$logo_i = $logo_o->instance();
				$logo = $logo_i->make_img_tag_wl($logo_o->id());
				$logo_url = $logo_i->get_url_by_id($logo_o->id());
			}

			$impl_phone = $impl->prop_str("phone_id");

			if ($this->can("view", $impl->prop("contact")))
			{
				$ct = obj($impl->prop("contact"));
				$ap = array($ct->prop("aadress"));
				if ($ct->prop("linn"))
				{
					$ap[] = $ct->prop_str("linn");
				}
				$aps = join(", ", $ap)."<br>";
				$aps .= $ct->prop_str("maakond");
				$aps .= " ".$ct->prop("postiindeks");
				$impl_addr = $aps;//$ct->name()." ".$ct->prop("postiindeks");
				if ($this->can("view", $ct->prop("riik")))
				{
					$riik = obj($ct->prop("riik"));
					$impl_phone = $riik->prop("area_code")." ".$impl_phone;
				}
			}

			if ($this->can("view", $impl->prop("email_id")))
			{
				$mail = obj($impl->prop("email_id"));
				$impl_mail = $mail->prop("mail");
			}

		}

		$bpct = $ord->prop("bill_penalty_pct");
		if (!$bpct)
		{
			$bpct = $impl->prop("bill_penalty_pct");
		}

		$this->vars(array(
			"orderer_name" => $ord->name(),
			"orderer_corpform" => $ord->prop("ettevotlusvorm.shortname"),
			"ord_currency_name" => $ord->prop_str("currency") == "" ? "EEK" : $ord->prop_str("currency"),
			"ord_penalty_pct" => number_format($bpct, 2),
			"orderer_addr" => $ord_addr,
			"orderer_kmk_nr" => $ord->prop("tax_nr"),
			"bill_no" => $b->prop("bill_no"),
			"impl_logo" => $logo,
			"impl_logo_url" => $logo_url,
			"bill_date" => $b->prop("bill_date"),
			"payment_due_days" => $b->prop("bill_due_date_days"),
			"bill_due" => date("d.m.Y", $b->prop("bill_due_date")),
			"orderer_contact" => $ord_ct,
			"comment" => $b->prop("notes"),
			"impl_name" => $impl->name(),
			"impl_address" => $impl_addr,
			"impl_reg_nr" => $impl->prop("reg_nr"),
			"impl_kmk_nr" => $impl->prop("tax_nr"),
			"impl_phone" => $impl_phone,
			"impl_fax" => $impl->prop_str("telefax_id"),
			"impl_email" => $impl_mail,
			"impl_url" => $impl->prop_str("url_id"),
			"comment" => $b->comment(),
			"time_spent_desc" => $b->prop("time_spent_desc")
		));		


		$rs = array();
		$sum_wo_tax = 0;
		$tax = 0;
		$sum = 0;
		foreach($bill_rows as $key => $row)
		{
			if ($row["is_oe"])
			{
				continue;
			}
			$cur_tax = 0;
			$cur_sum = 0;
			
			if ($row["has_tax"] == 1)
			{
				// tax needs to be added
				$cur_sum = $row["sum"];
				$cur_tax = ($row["sum"] * 0.18);
				$cur_pr = $this->num($row["price"]);
			}	
			else
			{
				// tax does not need to be added, tax free it seems
				$cur_sum = $row["sum"];
				$cur_tax = 0;
				$cur_pr = $this->num($row["price"]);
			}

			if($arr["between"] && !($key+1 >= $arr["between"][0] && $key+1 <= $arr["between"][1]));
			else
			{
				$this->vars(array(
					"unit" => $row["unit"],
					"amt" => number_format($row["amt"],2), 
					"price" => number_format($row["price"], 2,".", " "),
					"sum" => number_format($cur_sum, 2,"."),
					"desc" => $row["name"],
					"date" => $row["date"] 
				));
				$rs[] = array("str" => $this->parse("ROW"), "date" => $row["date"] , "jrk" => $row["jrk"], "id" => $row["id"]);
			}
			$sum_wo_tax += $cur_sum;
			$tax += $cur_tax;
			$sum += ($cur_tax+$cur_sum);
			$unit = $row["unit"];
			$tot_amt += $row["amt"];
			$tot_cur_sum += $cur_sum;
		}
		
		foreach($bill_rows as $key => $row)
		{
			if (!$row["is_oe"])
			{
				continue;
			}
			$cur_tax = 0;
			$cur_sum = 0;
			
			if ($row["has_tax"] == 1)
			{
				// tax needs to be added
				$cur_sum = $row["sum"];
				$cur_tax = ($row["sum"] * 0.18);
				$cur_pr = $this->num($row["price"]);
			}	
			else
			{
				// tax does not need to be added, tax free it seems
				$cur_sum = $row["sum"];
				$cur_tax = 0;
				$cur_pr = $this->num($row["price"]);
			}
			$this->vars(array(
				"unit" => $row["unit"],
				"amt" => number_format($row["amt"],2),
				"price" => number_format($cur_pr, 2,".", " "),
				"sum" => number_format($cur_sum, 2, ".", " "),
				"desc" => $row["name"],
				"date" => $row["date"]
			));

			$rs[] = array("str" => $this->parse("ROW"), "date" => $row["date"] , "jrk" => $row["jrk"], "id" => $row["id"]);
			$sum_wo_tax += $cur_sum;
			$tax += $cur_tax;
			$sum += ($cur_tax+$cur_sum);
		}
		usort($rs, array(&$this, "__br_sort"));
		foreach($rs as $idx => $ida)
		{
			$rs[$idx] = $ida["str"];
		}

		$sigs = "";
		
		foreach((array)$b->prop("signers") as $signer)
		{
			if (!$this->can("view", $signer))
			{
				continue;
			}
			$signer_p = obj($signer);
			$this->vars(array(
				"signer_person" => $signer_p->name()
			));
			$sigs .= $this->parse("SIGNATURE");
		}

		if(!$arr["not_last_page"])
		{
			$this->vars(array("tot_amt" => number_format($tot_amt, 2,".", " ")));
			$total_ = $this->parse("TOTAL");
		}
		$page_no = $arr["page"] + 1;
		if(!$page_no) $page_no = 1;
		if(!($page_no > 1))
		{
			$_header = $this->parse("HEADER");
		}
		//$sum_wo_tax = $this->round_sum($sum_wo_tax);
		$sum = $this->round_sum($sum);

		$this->vars(array(
			"SIGNATURE" => $sigs,
			"ROW" => join("", $rs),
			"TOTAL" => $total_,
			"HEADER" => $_header,
			"total_wo_tax" => number_format($sum_wo_tax, 2,".", " "),
			"tax" => number_format($tax, 2,"." , " "),
			"total" => number_format($sum, 2,".", " "),
			"total_text" => locale::get_lc_money_text($sum, $ord_cur, $lc),
			"tot_amt" => number_format($tot_amt, 2,".", " "),
			"page_no" => $page_no,
		));

		$res =  $this->parse();
		if (false && !$_GET["gen_print"])
		{
			$res = html::href(array(
				"url" => aw_url_change_var("gen_print", 1),
				"caption" => t("Prinditav arve")
			)).$res;
			return $res;
		}

		if ($_GET["openprintdialog"] == 1)
		{
			$res .= "<script language='javascript'>setTimeout('window.close()',10000);window.print();window.close();if (navigator.userAgent.toLowerCase().indexOf('msie') == -1) {window.close(); }</script>";
		}

		if ($_GET["openprintdialog_b"] == 1)
		{
			$url = aw_url_change_var("group", "preview", aw_url_change_var("openprintdialog", 1));
			$res .= "<script language='javascript'>setTimeout('window.location.href=\"$url\"',10000);window.print();if (navigator.userAgent.toLowerCase().indexOf('msie') == -1) {window.location.href='$url'; }</script>";
		}
		return $res;
		die($res);
	}

	function get_bill_currency($b)
	{
		return $b->prop("customer.currency.name") == "" ? "EEK" : $b->prop("customer.currency.name");
	}

	function get_bill_sum($b, $type = BILL_SUM)
	{
		$rs = "";
		$sum_wo_tax = 0;
		$tax = 0;
		$sum = 0;
		foreach($this->get_bill_rows($b) as $row)
		{
			$cur_tax = 0;
			$cur_sum = 0;
			$cur_pr = 0;
			
			if ($this->can("view", $row["prod"]))
			{
				$set = false;
				// get tax from prod
				$prod = obj($row["prod"]);
				if ($this->can("view", $prod->prop("tax_rate")))
				{
					$tr = obj($prod->prop("tax_rate"));

					if (time() >= $tr->prop("act_from") && time() < $tr->prop("act_to"))
					{
						$cur_sum = $row["sum"];
						$cur_tax = ($row["sum"] * ($tr->prop("tax_amt")/100.0));
						$cur_pr = $this->num($row["price"]);
						$set = true;
					}
				}

				if (!$set)
				{
					// no tax
					$cur_sum = $row["sum"];
					$cur_tax = 0;
					$cur_pr = $this->num($row["price"]);
				}
			}
			else
			if ($row["has_tax"] == 1)
			{
				// tax needs to be added
				$cur_sum = $row["sum"];
				$cur_tax = ($row["sum"] * 0.18);
				$cur_pr = $this->num($row["price"]);
			}	
			else
			{
				// tax does not need to be added, tax free it seems
				$cur_sum = $row["sum"];
				$cur_tax = 0;
				$cur_pr = $this->num($row["price"]);
			}

			$sum_wo_tax += $cur_sum;
			$tax += $cur_tax;
			$sum += ($cur_tax+$cur_sum);
			$unit = $row["unit"];
			$tot_amt += $row["amt"];
			$tot_cur_sum += $cur_sum;
		}

		switch($type)
		{
			case BILL_SUM_TAX:
				return $tax;

			case BILL_SUM_WO_TAX:
				return $sum_wo_tax;

			case BILL_AMT:
				return $tot_amt;
		}
		return $sum;
	}

	function _save_rows($arr)
	{
		foreach(safe_array($arr["request"]["rows"]) as $oid => $row)
		{
			$new = false;
			if (!$this->can("edit", $oid))
			{
				$o = obj();
				$pt = $arr["obj_inst"]->id();
				if (!is_oid($pt))
				{
					$u = get_instance(CL_USER);
					$pt = $u->get_current_company();
				}
				$o->set_parent($pt);
				$o->set_class_id(CL_CRM_BILL_ROW);
				$new = true;
			}
			else
			{
				$o = obj($oid);
			}
			$o->set_prop("name", $row["name"]);
			$o->set_prop("comment", $row["comment"]);

			$o->set_prop("date", $row["date"]);
			$o->set_prop("unit", $row["unit"]);
			$o->set_meta("jrk", $row["jrk"]);
			$o->set_prop("price", str_replace(",", ".", $row["price"]));
			$o->set_prop("amt", str_replace(",", ".", $row["amt"]));
			$o->set_prop("sum", str_replace(",", ".", $row["sum"]));
			$o->set_prop("prod", $row["prod"]);
			$o->set_prop("has_tax", (int)$row["has_tax"]);
			$o->set_prop("people", $row["person"]);
			$o->save();

			if ($new)
			{
				$arr["obj_inst"]->connect(array(
					"to" => $o->id(),
					"type" => "RELTYPE_ROW"
				));
			}
		}
		
		//summa õigeks
		if(is_array($arr["request"]["agreement_price"]))
		{
			foreach($arr["request"]["agreement_price"] as $key => $agreement_price)
			{
				$arr["request"]["agreement_price"][$key]["sum"] = $arr["request"]["agreement_price"][$key]["price"]*$arr["request"]["agreement_price"][$key]["amt"];
			}
		}
		$arr["obj_inst"]->set_meta("agreement_price", $arr["request"]["agreement_price"]);
		$arr["obj_inst"]->save();
	}

	/**
		@attrib name=add_row
		@param id required type=int acl=edit
		@param retu optional
	**/
	function add_row($arr)
	{
		$bill = obj($arr["id"]);
		$rows = $this->get_bill_rows(obj($arr["id"]));
		$jrk = 0;
		foreach($rows as $row)
		{
			if($row["jrk"] > $jrk-10) $jrk = $row["jrk"]+10;
		}

		$row = obj();
		$row->set_parent($bill->id());
		$row->set_class_id(CL_CRM_BILL_ROW);
		$row->set_prop("date", date("d.m.Y", time()));
		$row->set_meta("jrk" , $jrk);
		$row->save();

		$bill->connect(array(
			"to" => $row->id(),
			"type" => "RELTYPE_ROW"
		));
		$bill->set_prop("bill_trans_date", time());
		$bill->save();
		return $arr["retu"];
	}

	/**
		@attrib name=create_bill
	**/
	function create_bill($arr)
	{
		$bill = obj($arr["id"]);
		$seti = get_instance(CL_CRM_SETTINGS);
		$sts = $seti->get_current_settings();
		$ti = get_instance(CL_TASK);
		foreach(safe_array($arr["sel"]) as $task_id)
		{
			// add all rows that are not yet billed
			foreach($ti->get_task_bill_rows(obj($task_id)) as $row)
			{
				$br = obj();
				$br->set_class_id(CL_CRM_BILL_ROW);
				$br->set_parent($bill->id());
				$br->set_prop("name", $row["name"]);
				$br->set_prop("amt", $row["amt"]);
				$br->set_prop("prod", $row["prod"]);
				$br->set_prop("price", $row["price"]);
				$br->set_prop("unit", $row["unit"]);
				$br->set_meta("jrk", $row["jrk"]);
				$br->set_prop("is_oe", $row["is_oe"]);
				$br->set_prop("has_tax", $row["has_tax"]);
				$br->set_prop("date", date("d.m.Y", $row["date"]));
				// get default prod

				if ($sts)
				{
					$br->set_prop("prod", $sts->prop("bill_def_prod"));
				}
				$br->save();

				$br->connect(array(
					"to" => $task_id,
					"type" => "RELTYPE_TASK"
				));

				if ($row["row_oid"])
				{
					$br->connect(array(
						"to" => $row["row_oid"],
						"type" => "RELTYPE_TASK_ROW"
					));
					$tr = obj($row["row_oid"]);
					$tr->set_prop("bill_id", $bill->id());
					$tr->save();
				}

				$bill->connect(array(
					"to" => $br->id(),
					"type" => "RELTYPE_ROW"
				));
			}
		}
		return $arr["post_ru"];
	}

	function callback_generate_scripts($arr)
	{
		$url = $this->mk_my_orb("get_comment_for_prod");
		return '
			function upd_notes()
			{
				set_changed();
				//aw_do_xmlhttprequest("'.$url.'&prod="+document.changeform.gen_prod.options[document.changeform.gen_prod.selectedIndex].value, notes_fetch_callb);
			}

			function notes_fetch_callb()
			{
				if (req.readyState == 4)
				{
					// only if "OK"
					if (req.status == 200) 
					{
						if (req.responseXML)
						{
							response = req.responseXML.documentElement;
							items = response.getElementsByTagName("item");

							if (items.length > 0 && items[0].firstChild != null)
							{
								value = items[0].firstChild.data;
								document.changeform.notes.value = value;
							}
						}
					} 
					else 
					{
						alert("There was a problem retrieving the XML data:\n" + req.statusText);
					}
				}
			}
		';
	}

	/**
		@attrib name=get_comment_for_prod
		@param prod optional
	**/
	function get_comment_for_prod($arr)
	{
		header("Content-type: text/xml");
		$xml = "<?xml version=\"1.0\" encoding=\"".aw_global_get("charset")."\" standalone=\"yes\"?>\n<response>\n";
		

		$empty = $xml."<item></item></response>";
		if (!$arr["prod"])
		{
			die( $empty);
		}

		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"oid" => $arr["prod"]
		));
		if (!$ol->count())
		{
			die($empty);
		}

		foreach($ol->arr() as $o)
		{
			$xml .= "<item>".$o->comment()."</item>";
		}
		$xml .= "</response>";
		die($xml);
	}

	function _bill_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_button(array(
			"name" => "new",
			"tooltip" => t("Lisa rida"),
			"img" => "new.gif",
			"url" => $this->mk_my_orb("add_row", array("id" => $arr["obj_inst"]->id(), "retu" => get_ru()))
		));

		$tb->add_menu_button(array(
			"name" => "print",
			"tooltip" => t("Prindi"),
			"img" => "print.gif"
		));
		
		$tb->add_menu_item(array(
			"parent" => "print",
			"url" => "#",
			"onClick" => "win = window.open('".$this->mk_my_orb("change", array("openprintdialog" => 1,"id" => $arr["obj_inst"]->id(), "group" => "preview"), CL_CRM_BILL)."','billprint','width=100,height=100,statusbar=yes');",
			"text" => t("Prindi arve")
		));

		$tb->add_menu_item(array(
			"parent" => "print",
			"url" => "#",
			"onClick" => "window.open('".$this->mk_my_orb("change", array("openprintdialog" => 1,"id" => $arr["obj_inst"]->id(), "group" => "preview_add"), CL_CRM_BILL)."','billprint','width=100,height=100');",
			"text" => t("Prindi arve lisa")
		));

		$tb->add_menu_item(array(
			"parent" => "print",
			"url" => "#",
			"onClick" => "window.open('".$this->mk_my_orb("change", array("openprintdialog_b" => 1,"id" => $arr["obj_inst"]->id(), "group" => "preview_add"), CL_CRM_BILL)."','billprint','width=100,height=100');",
			"text" => t("Prindi arve koos lisaga")
		));

		$tb->add_button(array(
			"name" => "reconcile",
			"tooltip" => t("Koonda read"),
			"action" => "reconcile_rows",
			// get all checked rows and check their prices, if they are different, ask the user for a new price
			"onClick" => "nfound=0;curp=-1;form=document.changeform;len = form.elements.length;for(i = 0; i < len; i++){if (form.elements[i].name.indexOf('sel_rows') != -1 && form.elements[i].checked)	{nfound++; neln = 'rows_'+form.elements[i].value+'__price_';nel = document.getElementById(neln); if (nfound == 1) { curp = nel.value; } else if(curp != nel.value) {price_diff = 1;}}}; if (price_diff) {v=prompt('Valitud ridade hinnad on erinevad, sisesta palun koondatud rea hind'); if (v) { document.changeform.reconcile_price.value = v;return true; } else {return false;} }"
		));

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta read"),
			"confirm" => t("Oled kindel et soovid read kustutada?"),
			"action" => "delete_rows"
		));
	}

	/**
		@attrib name=delete_rows
	**/
	function delete_rows($arr)
	{
		foreach($arr["sel_rows"] as $row_id)
		{
			// now, the bill row has maybe a task row connected, reset the task row's bill no
			$ro = obj($row_id);
			$tr = $ro->get_first_obj_by_reltype("RELTYPE_TASK_ROW");
			if ($tr)
			{
				$tr->set_prop("bill_id", 0);
				$tr->save();
			}
		}
		object_list::iterate_list($arr["sel_rows"], "delete");
		return $arr["post_ru"];
	}

	function _init_bill_task_list(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Toimetus"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "hrs",
			"caption" => t("Tunde"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"sortable" => 1
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _bill_task_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bill_task_list($t);

		$ol = new object_list(array(
			"class_id" => CL_TASK,
			"customer" => $arr["obj_inst"]->prop("customer"),
			"CL_TASK.RELTYPE_ROW.done" => 1,
			"CL_TASK.RELTYPE_ROW.on_bill" => 1,
			"CL_TASK.RELTYPE_ROW.bill_id" => 0,
		));
		$ti = get_instance(CL_TASK);
		foreach($ol->arr() as $task)
		{
			$rows = $ti->get_task_bill_rows($task);
			$hrs = $price = 0;
			foreach($rows as $row)
			{
				$hrs += $row["amt"];
				$price += $row["sum"];
			}
			$t->define_data(array(
				"name" => html::obj_change_url($task),
				"hrs" => number_format($hrs, 2), 
				"price" => number_format($price, 2,".", " "),
				"oid" => $task->id()
			));
		}
	}

	function _billt_tb($arr)
	{
		
	}

	function do_db_upgrade($table, $field, $q, $err)
	{
		switch($field)
		{
			case "aw_time_spent_desc":
				$this->db_add_col($table, array(
					"name" => $field,
					"type" => "varchar(255)"
				));
				return true;
			case "aw_trans_date":
				$this->db_add_col($table, array(
					"name" => $field,
					"type" => "int"
				));
				return true;
		}
	}

	/**
		@attrib name=reconcile_rows
	**/
	function reconcile_rows($arr)
	{
		// go over the $sel_rows and add the numbers to the first selected one
		if (is_array($arr["sel_rows"]) && count($arr["sel_rows"]) > 1)
		{
			$frow = obj($arr["sel_rows"][0]);
			for($i = 1; $i < count($arr["sel_rows"]); $i++)
			{
				$row_o = obj($arr["sel_rows"][$i]);
				if ($arr["reconcile_price"] != -1)
				{
					$frow->set_prop("price", $arr["reconcile_price"]);
				}
				$frow->set_prop("amt", $frow->prop("amt") + $row_o->prop("amt"));
				$frow->set_prop("sum", $frow->prop("amt") * $frow->prop("price"));
				$row_o->delete();
			}
			$frow->save();
		}
		return $arr["post_ru"];
	}

	function on_delete_bill($arr)
	{
		$o = obj($arr["oid"]);
		// get all task rows from the bill rows and 
		$ol = new object_list(array(
			"class_id" => CL_TASK_ROW,
			"lang_id" => array(),
			"site_id" => array(),
			"bill_id" => $o->id()
		));
		foreach($ol->arr() as $tr)
		{
			$tr->set_prop("bill_id", 0);
			$tr->save();
		}
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "tasks")
		{
			return false;
		}
		return true;
	}
}
?>
