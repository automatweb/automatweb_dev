<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_bill.aw,v 1.28 2006/03/08 14:03:32 kristo Exp $
// crm_bill.aw - Arve 
/*

@classinfo syslog_type=ST_CRM_BILL relationmgr=yes no_comment=1 no_status=1 prop_cb=1 confirm_save_data=1

@default table=objects

@tableinfo aw_crm_bill index=aw_oid master_index=brother_of master_table=objects

@default group=general

	@property billp_tb type=toolbar store=no no_caption=1
	@caption Arve toolbar

	@property name type=textbox table=objects field=name
	@caption Nimi

	@property bill_no type=textbox table=aw_crm_bill field=aw_bill_no
	@caption Number

	@property customer type=popup_search table=aw_crm_bill field=aw_customer reltype=RELTYPE_CUST clid=CL_CRM_COMPANY,CL_CRM_PERSON
	@caption Klient

	@property impl type=popup_search style=relpicker table=aw_crm_bill field=aw_impl reltype=RELTYPE_IMPL
	@caption Arve esitaja

	@property bill_date type=date_select table=aw_crm_bill field=aw_date
	@caption Kuup&auml;ev

	@property bill_due_date_days type=textbox table=aw_crm_bill field=aw_due_date_days size=5
	@caption Makset&auml;htaeg (p&auml;evi)

	@property bill_due_date type=date_select table=aw_crm_bill field=aw_due_date
	@caption Tasumise kuup&auml;ev

	@property bill_recieved type=date_select table=aw_crm_bill field=aw_recieved
	@caption Laekumiskuup&auml;ev

	@property state type=select table=aw_crm_bill field=aw_state
	@caption Staatus

	@property disc type=textbox table=aw_crm_bill field=aw_discount size=5 
	@caption Allahindlus (%)

	@property sum type=text table=aw_crm_bill field=aw_sum size=5 
	@caption Summa

	@property monthly_bill type=checkbox ch_value=1 table=aw_crm_bill field=aw_monthly_bill
	@caption Kuuarve

	@property language type=relpicker automatic=1 field=meta method=serialize reltype=RELTYPE_LANGUAGE
	@caption Keel

	@property bill_rows type=text store=no 
	@caption Arveread 

@default group=preview

	@property preview type=text store=no no_caption=1

@default group=preview_add

	@property preview_add type=text store=no no_caption=1

@default group=tasks

	@property bill_tb type=toolbar store=no no_caption=1

	@property bill_proj_list type=table store=no no_caption=1
	@property bill_task_list type=table store=no no_caption=1


@groupinfo tasks caption="Toimetused" submit=no
@groupinfo preview caption="Eelvaade"
@groupinfo preview_add caption="Arve Lisa"



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
			2 => t("Makstud")
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

			case 'bill_proj_list':
			case 'bill_task_list':
			case 'bill_tb':
				static $bills_impl;
				if (!$bills_impl)
				{
					$bills_impl = get_instance("applications/crm/crm_company_bills_impl");
				}
				$fn = "_get_".$prop["name"];
				return $bills_impl->$fn($arr);
		
			case "bill_no":
				if ($prop["value"] == "")
				{
					$i = get_instance(CL_CRM_NUMBER_SERIES);
					$prop["value"] = $i->find_series_and_get_next(CL_CRM_BILL);
				}
				break;

			case "impl":
				if (!$arr["new"])
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
				$prop["value"] = number_format($prop["value"], 2);
				$prop["value"] .= " ".$arr["obj_inst"]->prop("customer.currency.name");
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
						return PROP_ERROR;
					}

					$ser = get_instance(CL_CRM_NUMBER_SERIES);
					if (!$ser->number_is_in_series(CL_CRM_BILL, $prop["value"]))
					{
						$prop["error"] = t("Number ei ole seerias!");
						return PROP_ERROR;
					}
				}
				break;

			case "bill_rows":
				$this->_save_rows($arr);
				break;
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
			"name" => "has_tax",
			"caption" => t("Lisandub k&auml;ibemaks?"),
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
			$t->define_data(array(
				"name" => html::textarea(array(
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
					"value" => $t_inf["date"] > 100 ? date("d/m/y", $t_inf["date"]) : "",
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
				"sum" => html::textbox(array(
					"name" => "rows[$id][sum]",
					"value" => $t_inf["sum"],
					"size" => 5
				)),
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
					"url" => $this->mk_my_orb("do_search", array("pn" => "rows[$id][prod]", "clid" => CL_SHOP_PRODUCT), "popup_search"),
					"caption" => t("Vali")
				))

			));
			$sum += $t_inf["sum"];
		}
		$t->set_sortable(false);

		if ($arr["obj_inst"]->prop("disc") > 0)
		{
			$sum -= $sum * ($arr["obj_inst"]->prop("disc") / 100.0);
		}
		if ($arr["obj_inst"]->prop("sum") != $sum)
		{
			$arr["obj_inst"]->set_prop("sum", $sum);
			$arr["obj_inst"]->save();
		}

		$arr["prop"]["value"] = $t->draw();
	}

	function get_sum($bill)
	{
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

		return $sum;
	}

	function callback_pre_save($arr)
	{
		$arr["obj_inst"]->set_prop("sum", $this->_calc_sum($arr["obj_inst"]));
		$bt = $arr["obj_inst"]->prop("bill_date");
		$arr["obj_inst"]->set_prop("bill_due_date", 
			mktime(3,3,3, date("m", $bt), date("d", $bt) + $arr["obj_inst"]->prop("bill_due_date_days"), date("Y", $bt))
		);
	}

	function _preview($arr)
	{
		$arr["prop"]["value"] = $this->show(array("id" => $arr["obj_inst"]->id()));
	}

	function _preview_add($arr)
	{
		$arr["prop"]["value"] = $this->show_add(array("id" => $arr["obj_inst"]->id()));
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
			$tpl .= "_".$lc;
		}

		$this->read_site_template($tpl.".tpl");

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
					$impl_phone = $riik->prop("area_code")." ".$impl_phone;
				}
			}

			if ($this->can("view", $impl->prop("email_id")))
			{
				$mail = obj($impl->prop("email_id"));
				$impl_mail = $mail->prop("mail");
			}

		}

		$this->vars(array(
			"orderer_name" => $ord->name(),
			"orderer_code" => $cust_no,
			"ord_currency_name" => $ord->prop_str("currency"),
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
		));		


		$rs = "";
		$sum_wo_tax = 0;
		$tax = 0;
		$sum = 0;
		$brows = $this->get_bill_rows($b);
		$grp_rows = array();

		$_no_prod_idx = -1;
		foreach($brows as $row)
		{
			if ($row["is_oe"])
			{
				continue;
			}
			$cur_tax = 0;
			$cur_sum = 0;

			if (!$this->can("view", $row["prod"]))
			{
				$row["prod"] = --$_no_prod_idx;
			}
			
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

			$grp_rows[$row["prod"]]["sum_wo_tax"] += $cur_sum;
			$grp_rows[$row["prod"]]["tax"] += $cur_tax;
			$grp_rows[$row["prod"]]["sum"] += ($cur_tax+$cur_sum);
			$grp_rows[$row["prod"]]["unit"] = $row["unit"];
			$grp_rows[$row["prod"]]["tot_amt"] += $row["amt"];
			$grp_rows[$row["prod"]]["tot_cur_sum"] += $cur_sum;
			$grp_rows[$row["prod"]]["name"] = $row["name"];
			$sum_wo_tax += $cur_sum;
			$tax += $cur_tax;
			$sum += ($cur_tax+$cur_sum);
			$tot_amt += $row["amt"];
			$tot_cur_sum += $cur_sum;
		}

		$fbr = reset($brows);
		foreach($grp_rows as $prod => $grp_row)
		{
			if ($this->can("view", $prod))
			{
				$po = obj($prod);
				$desc = $po->comment();
			}
			else
			{
				$desc = $grp_row["name"];
			}
			$this->vars(array(
				"unit" => $grp_row["unit"],
				"amt" => $grp_row["tot_amt"],
				"price" => (int)($grp_row["tot_cur_sum"] / $grp_row["tot_amt"]),
				"sum" => number_format($grp_row["tot_cur_sum"], 2),
				"desc" => $desc,
				"date" => "" 
			));
			$rs .= $this->parse("ROW");
		}

		foreach($this->get_bill_rows($b) as $row)
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
				"amt" => $row["amt"],
				"price" => number_format($cur_pr, 2),
				"sum" => number_format($cur_sum, 2),
				"desc" => $row["name"],
				"date" => $row["date"] > 100 ? date("d.m.Y", $row["date"]) : "" 
			));

			$rs .= $this->parse("ROW");
			$sum_wo_tax += $cur_sum;
			$tax += $cur_tax;
			$sum += ($cur_tax+$cur_sum);
		}

		$this->vars(array(
			"ROW" => $rs,
			"total_wo_tax" => number_format($sum_wo_tax, 2),
			"tax" => number_format($tax, 2),
			"total" => number_format($sum, 2),
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

			$rd = array(
				"amt" => $row->prop("amt"),
				"prod" => $row->prop("prod"),
				"name" => $row->prop("name"),
				"price" => $row->prop("price"),
				"sum" => str_replace(",", ".", $row->prop("amt")) * str_replace(",", ".", $row->prop("price")),
				"km_code" => $kmk,
				"unit" => $row->prop("unit"),
				"is_oe" => $row->prop("is_oe"),
				"has_tax" => $row->prop("has_tax"),
				"date" => $row->prop("date"),
				"id" => $row->id()
			);
			$inf[] = $rd;
		}
		return $inf;
	}

	function show_add($arr)
	{
		$b = obj($arr["id"]);

		$tpl = "show_add";
		$lc = "et";
		if ($this->can("view", $b->prop("language")))
		{
			$lo = obj($b->prop("language"));
			$lc = $lo->prop("lang_acceptlang");
			$tpl .= "_".$lc;
		}

		$this->read_site_template($tpl.".tpl");

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

		$this->vars(array(
			"orderer_name" => $ord->name(),
			"ord_currency_name" => $ord->prop_str("currency"),
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
		));		


		$rs = "";
		$sum_wo_tax = 0;
		$tax = 0;
		$sum = 0;
		foreach($this->get_bill_rows($b) as $row)
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

			$this->vars(array(
				"unit" => $row["unit"],
				"amt" => $row["amt"],
				"price" => number_format($row["price"], 2),
				"sum" => number_format($cur_sum, 2),
				"desc" => $row["name"],
				"date" => $row["date"] > 1000 ? date("d.m.Y", $row["date"]) : "" 
			));
			$rs .= $this->parse("ROW");

			$sum_wo_tax += $cur_sum;
			$tax += $cur_tax;
			$sum += ($cur_tax+$cur_sum);
			$unit = $row["unit"];
			$tot_amt += $row["amt"];
			$tot_cur_sum += $cur_sum;
		}

		foreach($this->get_bill_rows($b) as $row)
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
				"amt" => $row["amt"],
				"price" => number_format($cur_pr, 2),
				"sum" => number_format($cur_sum, 2),
				"desc" => $row["name"],
				"date" => $row["date"] > 100 ? date("d.m.Y", $row["date"]) : "" 
			));

			$rs .= $this->parse("ROW");
			$sum_wo_tax += $cur_sum;
			$tax += $cur_tax;
			$sum += ($cur_tax+$cur_sum);
		}

		$this->vars(array(
			"ROW" => $rs,
			"total_wo_tax" => number_format($sum_wo_tax, 2),
			"tax" => number_format($tax, 2),
			"total" => number_format($sum, 2),
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
			$res .= "<script language='javascript'>setTimeout('window.close()',10000);window.print();window.close();if (navigator.userAgent.toLowerCase().indexOf('msie') == -1) {window.close(); }</script>";
		}
		die($res);
	}

	function get_bill_currency($b)
	{
		return $b->prop("customer.currency.name");
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
			if ($this->can("edit", $oid))
			{
				$o = obj($oid);
				$o->set_prop("name", $row["name"]);

				if (trim($row["date"]) == "")
				{
					$row["date"] = -1;
				}
				else
				{
					list($d,$m,$y) = explode("/", $row["date"]);
					$row["date"] = mktime(0,0,0, $m, $d, $y);
				}

				$o->set_prop("date", $row["date"]);
				$o->set_prop("unit", $row["unit"]);
				$o->set_prop("price", $row["price"]);
				$o->set_prop("amt", $row["amt"]);
				$o->set_prop("sum", $row["sum"]);
				$o->set_prop("prod", $row["prod"]);
				$o->set_prop("has_tax", $row["has_tax"]);
				$o->save();
			}
		}
	}

	/**
		@attrib name=add_row
		@param id required type=int acl=edit
		@param retu optional
	**/
	function add_row($arr)
	{
		$bill = obj($arr["id"]);
		
		$row = obj();
		$row->set_parent($bill->id());
		$row->set_class_id(CL_CRM_BILL_ROW);
		$row->set_prop("date", time());
		$row->save();

		$bill->connect(array(
			"to" => $row->id(),
			"type" => "RELTYPE_ROW"
		));

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
				$br->set_prop("is_oe", $row["is_oe"]);
				$br->set_prop("has_tax", $row["has_tax"]);
				$br->set_prop("date", $row["date"]);
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
			"onClick" => "window.open('".$this->mk_my_orb("change", array("openprintdialog" => 1,"id" => $arr["obj_inst"]->id(), "group" => "preview_add"), CL_CRM_BILL)."','billprint','width=100,height=100')",
			"text" => t("Prindi arve lisa")
		));

		$tb->add_menu_item(array(
			"parent" => "print",
			"url" => "#",
			"onClick" => "window.open('".$this->mk_my_orb("change", array("openprintdialog_b" => 1,"id" => $arr["obj_inst"]->id(), "group" => "preview"), CL_CRM_BILL)."','billprint','width=100,height=100')",
			"text" => t("Prindi arve koos lisaga")
		));
	}
}
?>
