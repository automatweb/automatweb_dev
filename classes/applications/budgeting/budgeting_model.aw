<?php

class budgeting_model extends core
{
	function budgeting_model()
	{
		$this->init();
	}

	function apply_taxes_on_money_transfer($transfer)
	{
		// get level and make transfer to next level, then relaunch
		
		$n_tax_from_acct = $transfer->prop("to_acct");
		$n_tax_to_acct = $this->get_next_propagation_level_from_acct($transfer->prop("to_acct"), $transfer);

		foreach($n_tax_to_acct as $tf_data)
		{
			echo "from account ".$transfer->prop("to_acct.name")." to ".$tf_data["to_acct"]." amt = ".$tf_data["amount"]." <br>\n";
			flush();
			$to = $this->create_money_transfer(obj($transfer->prop("to_acct")), obj($tf_data["to_acct"]), $tf_data["amount"]);
			$this->apply_taxes_on_money_transfer($to);
		}
	}

	function create_money_transfer($from_acct, $to_acct, $amt, $data = null)
	{
		$from_balance = $this->get_account_balance($from_acct);
		$to_balance = $this->get_account_balance($to_acct);

		error::raise_if($from_balance < $amt, array(
			"id" => "ERR_NO_MONEY",
			"msg" => sprintf(t("Kontol %s ei ole piisavalt raha! Vaja on %s, kontol on %s"), $from_acct, $from_balance, $amt)
		));

		$this->start_transaction();
		$this->set_account_balance($to_acct, $to_balance+$amt);
		$this->set_account_balance($from_acct, $from_balance-$amt);
echo "set account balance $to_acct => ".($to_balance+$amt)." <br>";
echo "set account balance $from_acct => ".($from_balance-$amt)." <br>";
		if (!$this->end_transaction())
		{
			error::raise(array(
				"id" => "ERR_TRANSACTION_FAILED",
				"msg" => sprintf(t("Transaktsioonis esines viga %s "), join(",", $this->errmsg))
			));
		}

		$fo = obj($from_acct);

		$to = obj();
		$to->set_parent($fo->id());
		$to->set_class_id(CL_BUDGETING_TRANSFER);
		$to->set_name(sprintf(t("Kanne kontolt %s kontole %s %s"), "", "", date("d.m.Y H:i:s")));
		$to->set_prop("from_acct", $from_acct->id());
		$to->set_prop("to_acct", $to_acct->id());
		$to->set_prop("amount", $amt);
		$to->set_prop("when", time());
		$to->save();
echo "created transaction ".$to->id()." <br>";
		return $to;
	}

	function start_transaction()
	{
		// this needs to halt all other threads until it is done and disable errors
		$tmpdir = aw_ini_get("server.tmpdir");
		$this->trans_lock_file = $tmpdir."/aw_transaction_lock";
		clearstatcache();
		
		while (file_exists($this->trans_lock_file) && filemtime($this->trans_lock_file) > (time() - 4))
		{
			sleep(1);
		}

		@unlink($this->trans_lock_file);
		touch($this->trans_lock_file);

		$this->_trans = aw_global_get("__from_raise_error");
		aw_global_set("__from_raise_error", 1);
	}

	function end_transaction()
	{
		unlink($this->trans_lock_file);
		// this can let other threads do transactions again and restore errors

		aw_global_set("__from_raise_error", $this->_trans);

		// also, if an error occurred, then return failed status
		return !$GLOBALS["aw_is_error"];
	}

	function get_account_balance($acct)
	{
		$o = obj($acct);
		return $o->prop("balance");
	}

	function set_account_balance($acct, $val)
	{
		$o = obj($acct);
		$rv = $o->prop("balance");
		$o->set_prop("balance", $val);
		$o->save();
		return $rv;
	}

	function get_next_propagation_level_from_acct($acct_id, $transfer)
	{
		// switch for account type and hardwire the next levels
		$acct_o = obj($acct_id);
		$rv = array();
		switch($acct_o->class_id())
		{
			case CL_CRM_CATEGORY:
				$from_place = array("area_".$acct_id);
				if ($this->can("view", $transfer->prop("in_project")))
				{
					$po = obj($transfer->prop("in_project"));
					$impl = $po->get_first_obj_by_reltype("RELTYPE_ORDERER");
					if ($impl)
					{
						// now get category for customer
						$conns = $impl->connections_to(array(
							"from.class_id" => CL_CRM_CATEGORY,
							"type" => "RELTYPE_CUSTOMER"
						));
						if (count($conns))
						{
							$c = reset($conns);
							$cat = $c->from();
							$cats = array();
							while($cat->class_id() != CL_CRM_COMPANY && count($conns))
							{
								$cats[] = $cat->id();
								$conns = $cat->connections_to(array(
									"from.class_id" => CL_CRM_CATEGORY,
									"type" => "RELTYPE_CATEGORY"
								));
								$c = reset($conns);
								if ($c)
								{
									$cat = $c->from();
								}
							}

							if (!$cats[0])
							{
								$cats[0] = $cat->id();
							}
							if ($cats[0])
							{
								$from_place[] = "area_".$cats[0];
								$rv[] = array(
									"to_acct" => $cats[0],
									"amount" => $transfer->prop("amount")
								);
							}
						}
					}
				}
				break;

			case CL_CRM_COMPANY:
				$from_place = array("cust_".$acct_id, "projects_".$acct_id);
				if ($this->can("view", $transfer->prop("in_project")))
				{
					$po = obj($transfer->prop("in_project"));
					$impl = $po->get_first_obj_by_reltype("RELTYPE_ORDERER");
					if ($impl)
					{
						$from_place[] = "cust_".$impl->id();
						$rv[] = array(
							"to_acct" => $impl->id(),
							"amount" => $transfer->prop("amount")
						);
					}
				}
				break;

			case CL_PROJECT:
				$from_place = array("proj_".$acct_id);
				break;

			case CL_TASK:
				$from_place = array("task_".$acct_id);
				break;

			case CL_CRM_PERSON:
				$from_place = array("person_".$acct_id);
				break;

			case CL_BUDGETING_FUND:
				$from_place = array("fund_".$acct_id);
				break;

			case CL_SHOP_PRODUCT:
				$from_place = array("prod_".$acct_id);
				break;

			case CL_BUDGETING_ACCOUNT:
				$from_place = array("acct_".$acct_id);
				break;

		}

		if ($from_place != "")
		{
			// get all taxes that go from the category
			$ol = new object_list(array(
				"class_id" => CL_BUDGETING_TAX,
				"lang_id" => array(),
				"site_id" => array(),
				"from_place" => $from_place
			));
			foreach($ol->arr() as $tax)
			{	
				$rv[] = array(
					"to_acct" => $tax->prop("to_acct"),
					"amount" => $this->calculate_amount_to_transfer_from_tax($tax, $acct_o)
				);
			}
		}

		return $rv;
	}

	function calculate_amount_to_transfer_from_tax($tax, $from_acct_o)
	{
		if (substr($tax->prop("amount"), -1) == "%")
		{
			return $this->get_account_balance($from_acct_o->id()) * ((double)$tax->prop("amount") / 100.0); 
		}
		return $tax->prop("amount");
	}
}

?>