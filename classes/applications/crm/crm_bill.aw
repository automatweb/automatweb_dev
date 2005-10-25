<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_bill.aw,v 1.7 2005/10/25 12:22:04 kristo Exp $
// crm_bill.aw - Arve 
/*

@classinfo syslog_type=ST_CRM_BILL relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects

@tableinfo aw_crm_bill index=aw_oid master_index=brother_of master_table=objects

@default group=general

	@property bill_no type=textbox table=aw_crm_bill field=aw_bill_no
	@caption Number

	@property bill_date type=date_select table=aw_crm_bill field=aw_date
	@caption Kuup&auml;ev

	@property bill_due_date_days type=textbox table=aw_crm_bill field=aw_due_date_days size=5
	@caption Makset&auml;htaeg (p&auml;evi)

	@property bill_due_date type=date_select table=aw_crm_bill field=aw_due_date
	@caption Tasumise kuup&auml;ev

	@property bill_recieved type=date_select table=aw_crm_bill field=aw_recieved
	@caption Laekumist&auml;htaeg

	@property customer type=popup_search table=aw_crm_bill field=aw_customer reltype=RELTYPE_CUST clid=CL_CRM_COMPANY,CL_CRM_PERSON
	@caption Klient

	@property impl type=relpicker table=aw_crm_bill field=aw_impl reltype=RELTYPE_IMPL
	@caption Teostaja

	@property state type=select table=aw_crm_bill field=aw_state
	@caption Staatus

	@property notes type=textarea rows=5 cols=50 table=aw_crm_bill field=aw_notes
	@caption M&auml;rkused

	@property monthly_bill type=checkbox ch_value=1 table=aw_crm_bill field=aw_monthly_bill
	@caption Kuuarve

	@property bill_rows type=table store=no 
	@caption Arveread 

	@property disc type=textbox table=aw_crm_bill field=aw_discount size=5 
	@caption Allahindlus (%)

	@property sum type=text table=aw_crm_bill field=aw_sum size=5 
	@caption Summa


@default group=preview

	@property preview type=text store=no no_caption=1

@default group=tasks

	@property task_list type=table no_caption=1 store=no


@groupinfo tasks caption="Toimetused"
@groupinfo preview caption="Eelvaade"



@reltype TASK value=1 clid=CL_TASK
@caption &uuml;lesanne

@reltype CUST value=2 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption klient

@reltype IMPL value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption teostaja

*/

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
			case "bill_due_date":
				$prop["value"] = $arr["obj_inst"]->prop("bill_date") + (24*3600*$arr["obj_inst"]->prop("bill_due_date_days"));
				break;

			case "preview":
				$this->_preview($arr);
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
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "bill_rows":
				$inf = array();
				foreach(safe_array($arr["request"]["rows"]) as $idx => $e)
				{
					list($d,$m,$y) = explode("/", $e["date"]);
					$e["date"] = mktime(0,0,0, $m, $d, $y);
					$e["sum"] = $e["price"] * $e["amt"];
					$inf[$idx] = $e;
				}	
				$arr["obj_inst"]->set_meta("bill_inf", $inf);
				break;
		}
		return $retval;
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
			"name" => "has_tax",
			"caption" => t("Lisandub k&auml;ibemaks?"),
		));
	}

	function _bill_rows($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bill_rows_t($t);

		$sum = 0;

		$inf = safe_array($arr["obj_inst"]->meta("bill_inf"));
		$task_i = get_instance(CL_TASK);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_TASK")) as $c)
		{
			$task = $c->to();
			foreach($task_i->get_task_bill_rows($task) as $id => $row)
			{
				if (!isset($inf[$id]))
				{
					$inf[$id] = $row;
				}

				$t_inf = $inf[$id];
				$t->define_data(array(
					"name" => html::textarea(array(
						"name" => "rows[$id][name]",
						"value" => $t_inf["name"],
						"rows" => 5,
						"cols" => 50
					)),
					"date" => html::textbox(array(
						"name" => "rows[$id][date]",
						"value" => date("d/m/y", $t_inf["date"]),
						"size" => 10
					)),
					"unit" => html::textbox(array(
						"name" => "rows[$id][unit]",
						"value" => $t_inf["unit"],
						"size" => 10
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
					))
				));
				$sum += $t_inf["sum"];
			}
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
	}

	function get_sum($bill)
	{
		return $bill->prop("sum");
	}

	function _calc_sum($bill)
	{
		$inf = safe_array($bill->meta("bill_inf"));
		$sum = 0;
		foreach($inf as $row)
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
		$arr["obj_inst"]->set_prop("bill_due_date", $arr["obj_inst"]->prop("bill_date") + (24*3600*$arr["obj_inst"]->prop("bill_due_date_days")));
	}

	function _preview($arr)
	{
		$arr["prop"]["value"] = $this->show(array("id" => $arr["obj_inst"]->id()));
	}

	function show($arr)
	{
		$b = obj($arr["id"]);
		$this->read_site_template("show.tpl");

		$ord = obj();
		$ord_cur = obj();
		if ($this->can("view", $b->prop("customer")))
		{
			$ord = obj($b->prop("customer"));
			$ord_ct = $ord->prop("firmajuht");
			if ($this->can("view", $ord_ct))
			{
				$ct = obj($ord_ct);
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
					"bank_iban" => $bank->prop("iban_code")
				));

				$ba .= $this->parse("BANK_ACCOUNT");
			}

			$this->vars(array(
				"BANK_ACCOUNT" => $ba
			));
			$logo_o = $impl->get_first_obj_by_reltype("RELTYPE_ORGANISATION_LOGO");
			$logo_i = $logo_o->instance();
			$logo = $logo_i->make_img_tag_wl($logo_o->id());
			$logo_url = $logo_i->get_url_by_id($logo_o->id());

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
			$cur_tax = 0;
			$cur_sum = 0;
			
			if ($row["has_tax"] == 1)
			{
				// tax needs to be added
				$cur_sum = $row["sum"];
				$cur_tax = ($row["sum"] * 0.18);
				$cur_pr = $row["price"];
			}	
			else
			{
				// tax does not need to be added, tax free it seems
				$cur_sum = $row["sum"];
				$cur_tax = 0;
				$cur_pr = $row["price"];
			}

			$this->vars(array(
				"unit" => $row["unit"],
				"amt" => $row["amt"],
				"price" => number_format($cur_pr, 2),
				"sum" => number_format($cur_sum, 2),
				"desc" => $row["name"]
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
			"total_text" => locale::get_lc_money_text($sum, $ord_cur)
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

		die($res);
	}

	function get_bill_rows($bill)
	{
		$inf = safe_array($bill->meta("bill_inf"));
		$task_i = get_instance(CL_TASK);

		foreach($bill->connections_from(array("type" => "RELTYPE_TASK")) as $c)
		{
			$task = $c->to();
			foreach($task_i->get_task_bill_rows($task) as $id => $row)
			{
				if (!isset($inf[$id]))
				{
					$inf[$id] = $row;
				}
				
			}
		}

		return $inf;
	}
}
?>