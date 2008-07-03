<?php

class shop_delivery_note_obj extends _int_object
{
	function create_movement($arr)
	{
		$conn = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_ROW",
		));
		$single_vars = array(
			0 => array(
				"prod_prop" => "serial_number_based",
				"err_word1" => t("Seerianumbri"),
				"err_word2" => t("seerianumber"),
				"row_prop" => "serial_no",
				"single_type" => "0",
			),
			1 => array(
				"prod_prop" => "order_based",
				"err_word1" => t("Partii numbri"),
				"err_word2" => t("partiinumber"),
				"row_prop" => "set_no",
				"single_type" => "1",
			),
		);
		$pi = get_instance(CL_SHOP_PRODUCT);
		$ufi = obj();
		$ufi->set_class_id(CL_SHOP_UNIT_FORMULA);
		$wo = $arr["obj_inst"]->prop("writeoff");
		$twh = $arr["obj_inst"]->prop("to_warehouse");
		foreach($conn as $c)
		{
			$row = $c->to();
			if(!$row->prop("unit"))
			{
				aw_session_set("dn_err", t("Igal tootel tuleb &uuml;hik m&auml;&auml;rata."));
				return false;
			}
			if(!$row->prop("amount"))
			{
				aw_session_set("dn_err",  t("Igal tootel tuleb kogus m&auml;&auml;rata."));
				return false;
			}
			if($wo)
			{
				if($twh || $row->prop("warehouse"))
				{
					aw_session_set("dn_err",  t("Mahakandmist ei saa teostada, kuna on m&auml;&auml;ratud sihtladu"));
					return false;
				}
			}
			$prod_id = $row->prop("product");
			$prod = obj($prod_id);
			$units = $pi->get_units($prod);
			foreach($units as $i=>$unit)
			{
				if(!$i && !$this->can("view", $unit))
				{
					aw_session_set("dn_err", sprintf(t("Tootel %s pole m&auml;&auml;ratud p&otilde;hi&uuml;hikut"), $prod->name()));
					return false;
				}
				if($arr["obj_inst"]->prop("from_warehouse"))
				{
					$ch_amt = $this->get_wh_amount($row, $arr["obj_inst"], true, $unit);
					if(!is_numeric($ch_amt))
					{
						aw_session_set("dn_err", sprintf(t("Tootel %s puudub l&auml;htelaos antud parameetritega laoseis"), $prod->name()));
						return false;
					}
				}
				if($unit != $row->prop("unit") && $unit && $this->can("view", $unit))
				{
					$fo = $ufi->get_formula(array(
						"from_unit" => $row->prop("unit"),
						"to_unit" => $unit,
						"product" => $prod,
					));
					if($fo)
					{
						$amt = $ufi->calc_amount(array(
							"amount" => $row->prop("amount"),
							"prod" => $prod,
							"obj" => $fo,
						));
						$amts[$row->id()][$unit] = $amt;
					}
					else
					{
						$from_unit = obj($row->prop("unit"));
						$to_unit = obj($unit);
						aw_session_set("dn_err", sprintf(t("Tootel %s puudub arvutusvalem %s -> %s"), $prod->name(), $from_unit->name(), $to_unit->name()));
						return false;
					}
				}
				elseif($unit == $row->prop("unit"))
				{
					$amts[$row->id()][$unit] = $row->prop("amount");
				}
			}
			$prod_units[$prod_id] = $units;
		}
		foreach($conn as $c)
		{
			$row = $c->to();
			$prod_id = $row->prop("product");
			$prod = obj($prod_id);
			$singles = array();
			$uses_single = 2;
			foreach($single_vars as $sv)
			{
				if($prod->prop($sv["prod_prop"]))
				{
					if(!($no = $row->prop($sv["row_prop"])))
					{
						aw_session_set("dn_err", sprintf(t("%s p&otilde;hise arvestusega tootel %s tuleb %s m&auml;&auml;rata."), $sv["err_word1"], $prod->name(), $sv["err_word2"]));
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
					elseif(!$arr["obj_inst"]->prop("from_wh"))
					{
						$o = obj();
						$o->set_class_id(CL_SHOP_PRODUCT_SINGLE);
						$o->set_parent($prod_id);
						$o->set_name($row->prop($sv["row_prop"]));
						$o->set_prop("product", $prod_id);
						$o->set_prop("type", $sv["single_type"]);
						$o->set_prop("code", $row->prop($sv["row_prop"]));
						$o->save();
						$singles[] = $o;
					}
				}
				else
				{
					$uses_single--;
				}
			}
			$params = array(
				"row" => $row,
				"units" => $prod_units[$prod_id],
				"obj_inst" => $arr["obj_inst"],
				"amounts" => $amts,
			);
			if(!$uses_single)
			{
				$this->create_movement_from_param($params);
			}
			else
			{
				foreach($singles as $single)
				{
					$params["single"] = $single->id();
					$this->create_movement_from_param($params);
				}
			}
		}
		return true;
	}

	function create_movement_from_param($arr)
	{
		$row = $arr["row"];
		$from_wh_id = $arr["obj_inst"]->prop("from_warehouse");
		if(is_oid($from_wh_id) && $from_wh_id)
		{
			$from_wh = obj($from_wh_id);
		}
		$twh = $row->prop("warehouse");
		if(is_oid($twh) && $twh)
		{
			$to_wh = obj($twh);
		}
		$to_wh_id = $arr["obj_inst"]->prop("to_warehouse");
		if(is_oid($to_wh_id) && !$to_wh)
		{
			$to_wh = obj($to_wh_id);
		}
		if($to_wh->id() == $from_wh->id())
		{
			return;
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
		$prod_id = $row->prop("product");
		$prod = obj($prod_id);
		$sid = $arr["single"];
		$pi = $prod->instance();
		foreach($wh_vars as $whv)
		{
			if(${$whv["var"]})
			{
				foreach($arr["units"] as $i=>$unit)
				{
					if($unit && $this->can("view", $unit))
					{
						$amt = $arr["amounts"][$row->id()][$unit];
						if($i === 0)
						{
							$defamt = $amt;
						}
						$amount = $pi->get_amount(array(
							"unit" => $unit,
							"prod" => $prod_id,
							"single" => $sid,
							"warehouse" => ${$whv["var"]}->id(),
						));
						if(!$amount->count())
						{
							$amto = obj();
							$amto->set_class_id(CL_SHOP_WAREHOUSE_AMOUNT);
							$amto->set_parent($prod_id);
							$amto->set_prop("warehouse", ${$whv["var"]}->id());
							$amto->set_prop("product", $prod_id);
							$amto->set_prop("single", $sid);
							$amto->set_prop("amount", ($whv["amt_mod"] * $amt));
							$amto->set_prop("unit", $unit);
							$amto->set_name(sprintf(t("%s laoseis"), $prod->name()));
							$amto->save();
						}
						else
						{
							$amto = $amount->begin();
							$amto->set_prop("amount", $amto->prop("amount") + $whv["amt_mod"] * $amt);
							$amto->save();
						}
					}
				}
			}
		}
		$mvo = obj();
		$mvo->set_class_id(CL_SHOP_WAREHOUSE_MOVEMENT);
		$mvo->set_prop("from_wh", $from_wh?$from_wh->id():null);
		$mvo->set_prop("to_wh", $to_wh?$to_wh->id():null);
		$mvo->set_prop("product", $prod_id);
		$mvo->set_prop("single", $sid);
		$mvo->set_prop("amount", $defamt);
		$mvo->set_prop("unit", $arr["units"][0]);
		$mvo->set_prop("price", $row->prop("price"));
		$mvo->set_prop("transport", $arr["obj_inst"]->prop("transport"));
		$mvo->set_prop("customs", $arr["obj_inst"]->prop("customs"));
		$mvo->set_prop("date", $arr["obj_inst"]->prop("delivery_date"));
		$mvo->set_prop("delivery_note", $arr["obj_inst"]->id());
		$mvo->set_prop("currency", $arr["obj_inst"]->prop("currency"));
		$mvo->set_parent($prod_id);
		$mvo->set_name(sprintf(t("%s liikumine"), $prod->name()));
		$mvo->save();
	}

	function get_wh_amount($row, $o, $set_chk = false, $unit = null)
	{
		if($fwh = $o->prop("from_warehouse"))
		{
			$prod = $row->prop("product");
			$po = obj($prod);
			$serial = $po->prop("serial_number_based");
			$set = $po->prop("order_based");
			if($serial)
			{
				$code = $row->prop("serial_no");
			}
			elseif($set)
			{
				$code = $row->prop("set_no");
				$set_checked = 1;
			}
			else
			{
				$nocode = 1;
			}
			if(isset($code))
			{
				if($code)
				{
					$params["singlecode"] = $code;
				}
				elseif(!$nocode)
				{
					return;
				}
			}
			$params["prod"] = $prod;
			$params["warehouse"] = $fwh;
			$params["unit"] = $unit ? $unit : $row->prop("unit");
			$pi = get_instance(CL_SHOP_PRODUCT);
			$ol = $pi->get_amount($params);
			if($ol && $ol->count() == 1)
			{
				$amount = $ol->begin();
				if($set_chk && !$set_checked && $set)
				{
					$params["singlecode"] = $row->prop("set_no");
					$ol2 = $pi->get_amount($params);
					if(!$ol2 || !$ol2->count())
					{
						return false;
					}
				}
				return $amount->prop("amount");
			}
		}
	}

	function _get_warehouse_chooser()
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_WAREHOUSE,
			"site_id" => array(),
			"lang_id" => array(),
		));
		$res = array(0 => t("--vali--")) + $ol->names();
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
}

?>
