<?php

class crm_bill_obj extends _int_object
{
	function set_prop($name,$value)
	{
		if($name == "bill_no")
		{
//			if(!$this->name() || strlen($this->name()) < 9)
//			{
				$this->set_name(t("Arve nr")." ".$value);
//			}
//			elseif($this->prop("bill_no") && substr_count($this->name() , $this->prop("bill_no")))
//			{
//				$this->set_name(str_replace($this->prop("bill_no"), $value , $this->name()));
//			}
		}

		parent::set_prop($name,$value);
	}

	function get_bill_print_popup_menu()
	{
		$bill_inst = get_instance(CL_CRM_BILL);
		$pop = get_instance("vcl/popup_menu");
		$pop->begin_menu("bill_".$this->id());
		$pop->add_item(Array(
			"text" => t("Prindi arve"),
			"link" => "#",
			"oncl" => "onClick='window.open(\"".$bill_inst->mk_my_orb("change", array("openprintdialog" => 1,"id" => $this->id(), "group" => "preview"), CL_CRM_BILL)."\",\"billprint\",\"width=100,height=100\");'"
		));
		$pop->add_item(Array(
			"text" => t("Prindi arve lisa"),
			"link" => "#",
			"oncl" => "onClick='window.open(\"".$bill_inst->mk_my_orb("change", array("openprintdialog" => 1,"id" => $this->id(), "group" => "preview_add"), CL_CRM_BILL)."\",\"billprintadd\",\"width=100,height=100\");'"
		));
		$pop->add_item(array(
			"text" => t("Prindi arve koos lisaga"),
			"link" => "#",
			"oncl" => "onClick='window.open(\"".$bill_inst->mk_my_orb("change", array("openprintdialog_b" => 1,"id" => $this->id(), "group" => "preview"), CL_CRM_BILL)."\",\"billprintadd\",\"width=100,height=100\");'"
		));
		return $pop->get_menu();
	}

	/** returns bill currency id
		@attrib api=1
		@returns oid
	**/
	function get_bill_currency_id()
	{
		if($this->prop("customer.currency"))
		{
			return $this->prop("customer.currency");
		}
		if($cust = $this->get_bill_customer())
		{
			$customer = obj($cust);
			return $customer->prop("currency");
		}
		$co_stat_inst = get_instance("applications/crm/crm_company_stats_impl");
		$company_curr = $co_stat_inst->get_company_currency();
		return $company_curr;
	}

	/** returns bill currency name
		@attrib api=1
		@returns string
	**/
	function get_bill_currency_name()
	{
		if($this->prop("customer.currency"))
		{
			$company_curr = $this->prop("customer.currency");
		}
		else
		{
			if($cust = $this->get_bill_customer())
			{
				$customer = obj($cust);
				$company_curr = $customer->prop("currency");
			}
			else
			{
				$co_stat_inst = get_instance("applications/crm/crm_company_stats_impl");
				$company_curr = $co_stat_inst->get_company_currency();
			}
		}
		if(is_oid($company_curr) && $this->can("view" , $company_curr))
		{
			$cu_o = obj($company_curr);
			return $cu_o->name();
		}
		return "EEK";
	}


	/**
		@attrib api=1 all_args=1
	@param payment optional type=oid
		payment id you want to ignore
	@returns string error
	@comment
		returns sum not paid for bill
	**/
	function get_bill_needs_payment($arr)
	{
		$payment = $arr["payment"];
		$bi = get_instance(CL_CRM_BILL);
		$bill_sum = $bi->get_bill_sum($this);
		$sum = 0;
		foreach($this->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
		{
			$p = $conn->to();//echo $p->id();
			if($payment && $payment == $p->id())
			{
				if(($bill_sum - $sum) > $p->prop("sum")) // kui arve summa - juba makstud summa on suurem kui antud laekumine , siis tagastaks selle sama laekumise summa, sest rohkem vtta ju pole
				{
					return $p->prop("sum");
				}
				break;
			}
			$sum = $sum + $p->get_free_sum($this->id());
		}
		if($bill_sum < $sum)
		{
			$sum = $bill_sum;
		}
		return $bill_sum - $sum;
	}

	/** Adds payment in the given amount to the bill
		@attrib api=1 params=pos

		@param sum optional type=double 
			The sum the payment was for. defaults to the entire sum of the bill

		@param tm optional type=int
			Time for the payment. defaults to current time

		@returns
			oid of the payment object
	**/
	function add_payment($sum = 0, $tm = null)
	{
		if ($tm === null)
		{
			$tm = time();
		}
		$i = get_instance(CL_CRM_BILL);
		if(!$sum)
		{
			$sum = $i->get_bill_sum($this,BILL_SUM) - $this->prop("partial_recieved");
		}
		$p = new object();
		$p-> set_parent($this->id());
		$p-> set_name($this->name() . " " . t("laekumine"));
		$p-> set_class_id(CL_CRM_BILL_PAYMENT);
		$p-> set_prop("date", $tm);
		$p->save();
/*
		$this->connect(array(
			"to" => $p->id(),
			"type" => "RELTYPE_PAYMENT"
		));

		$p-> set_prop("sum", $sum);//see koht sureb miskiprast
		$curr = $i->get_bill_currency_id($this);
		if($curr)
		{
			$ci = get_instance(CL_CURRENCY);
			$p -> set_prop("currency", $curr);
			$rate = 1;
			if(($default_c = $ci->get_default_currency) != $curr)
			{
				$rate = $ci->convert(array(
					"sum" => 1,
					"from" => $curr,
					"to" => $default_c,
					"date" => time(),
				));
			}
			$p -> set_prop("currency_rate", $rate);
		}
		$p-> save();*/
		$p->add_bill(array(
			"sum" => $sum,
			"o" => $this,
		));
		return $p->id();
	}

	function get_bill_payments_data()
	{
		$data = array();
		foreach($this->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
		{
			$p = $conn->to();
			$data[$p->id()]["currency"] = $p->get_currency_name();
			$bill_sums = $p->meta("sum_for_bill");
			$data[$p->id()]["sum"] = $bill_sums[$this->id()];
			$data[$p->id()]["total_sum"] = $p->prop("sum");
			$data[$p->id()]["date"] = $p->prop("date");
		}

		return $data;
	}

	function get_payments_sum()
	{
		$sum = 0;
		foreach($this->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
		{
			$p = $conn->to();
			$data[$p->id()]["currency"] = $p->get_currency_name();
			$bill_sums = $p->meta("sum_for_bill");
			$sum = $sum + $bill_sums[$this->id()];
		}
		return $sum;
	}

	function get_last_payment_date()
	{
		$date = 0;
		foreach($this->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
		{
			$p = $conn->to();
			if($p->prop("date") > $date)
			{
				$date = $p->prop("date");
			}
		}
		return $date;
	}

	/** Returns bill customer id
		@attrib api=1
		@returns
			oid of the customer
	**/
	function get_bill_customer()
	{
		$id = "";
		$bi = get_instance("applications/crm/crm_bill");
		if (is_oid($this->prop("customer")) && $bi->can("view", $this->prop("customer")))
		{
			return $this->prop("customer");
		}
		else
		{
			foreach($this->connections_from(array("type" => "RELTYPE_CUST")) as $conn)
			{
				$id = $conn->prop("to");
			}
		}
		
		if(!$id)
		{
			foreach($this->connections_from(array("type" => "RELTYPE_TASK")) as $conn)
			{
				$p = $conn->to();
				$id = $p->prop("customer");
			}
		}

		if($id)
		{
			$this->set_prop("customer" , $id);
			$this->save();
		}
		return $id;
	}

	/** Adds bug comments to bill
		@attrib api=1 params=pos
		@param bugcomments required type=array
			array(bug comment id, bug comment 2 id , ...)
		@returns
			bill oid
	**/
	public function add_bug_comments($bugcomments)
	{
		$data = array();
		foreach($bugcomments as $comment)
		{
			if(!$this->can("view" , $comment))
			{
				continue;
			}
			$o = obj($comment);
			$data[$o->parent()][$o->parent()][] = $o;
//			$data[mktime(0,0,0,date("m",$o->created()),date("d",$o->created()),date("Y",$o->created()))][$o->parent()][] = $o;
		}
		
		ksort($data);
		foreach($data as $day => $day_array)
		{
			foreach($day_array as $bug => $bug_comments)
			{
				$b = obj($bug);
				if(!$this->check_if_has_other_customers($b->prop("customer")))
				{
					$this->add_bug_row($bug_comments);
				}
			}
		}

		return $this->id();
	}

	/** Adds bug comments to bill... every comment to single row
		@attrib api=1 params=pos
		@param bugcomments required type=array
			array(bug comment id, bug comment 2 id , ...)
		@returns
			bill oid
	**/
	public function add_bug_comments_single_rows($bugcomments)
	{
		$data = array();
		foreach($bugcomments as $comment)
		{
			if(!$this->can("view" , $comment))
			{
				continue;
			}
			$o = obj($comment);
			$data[$o->created()][$o->parent()][] = $o;
		}

		ksort($data);
		foreach($data as $day => $day_array)
		{
			foreach($day_array as $bug => $bug_comments)
			{
				$b = obj($bug);
				if(!$this->check_if_has_other_customers($b->prop("customer")))
				{
					$this->add_bug_row($bug_comments);
				}
			}
		}

		return $this->id();
	}


	/** Adds bug comments to bill row
		@attrib api=1 params=pos
		@param bugcomments required type=array
			array(bug comment id, bug comment 2 id , ...)
		@returns
			bill row id
	**/
	public function add_bug_row($bugcomments)
	{
		$row = new object();
		$row->set_class_id(CL_CRM_BILL_ROW);
		$row->set_name(t("Arve rida"));
		$row->set_parent($this->id());
		$row->save();

		$people = array();
		$amt = $price = $date = "";
		$u = get_instance(CL_USER);

		foreach($bugcomments as $c)
		{
			$comment = obj($c);
			$person = $u->get_person_for_uid($comment->createdby());
			if(is_object($person))
			{
				$people[$person->id()] = $person->id();
			}
			$amt+= $comment->bill_hours();
			if($err = $this->connect_bug_comment($comment->id()) || $err2 = $row->connect_bug_comment($comment->id()))
			{
				arr($err);
				arr($err2);
			}
			$comment_date = $comment->prop("date");
			if(!($date > 0) || $date < $comment_date)
			{
				$date = $comment_date;
			}
			$row->connect(array(
				"to" => $comment->id(),
				"type" => "RELTYPE_TASK_ROW"
			));
		}

		$amt = ((int)(($amt * 4)+1)) / 4;//ymardab yles 0.25listeni

		$row->set_prop("amt", $amt);
		$row->set_prop("price", $price);
		$row->set_prop("unit", t("tund"));
		$row->set_prop("people", $people);

//		$br->set_prop("has_tax", $row["has_tax"]); ?????????????

		if(is_object($comment))
		{
			if($comment->prop("parent.class_id") == CL_BUG)
			{
				$row->set_prop("price", $comment->prop("parent.hr_price"));
			}
			foreach($comment->connections_from(array("type" => "RELTYPE_PROJECT")) as $c)
			{
				$bill = obj($row->parent());
				$bill->set_project($c->prop("to"));
			}
			$row->set_prop("date", date("d.m.Y", $date));
			$row->set_name($comment->prop("parent.name"));
		}
		else
		{
			$row->set_prop("date", date("d.m.Y", time()));
		}
		$row->save();
		$this->connect(array(
			"to" => $row->id(),
			"type" => "RELTYPE_ROW"
		));
		return $row->id();
	}

	/** checks if bill has other customers...
		@attrib api=1
		@param customer type=oid
		@returns string/int
			error, if true, if not, then 0
	**/
	public function check_if_has_other_customers($customer)
	{
		if(!is_oid($customer))
		{
			return 0;
		}
		if(!$this->prop("customer"))
		{
			return 0;
		}
		if($customer != $this->prop("customer"))
		{
			return "on teised kliendid...";
		}
		return 0;
	}


	/** connects bill to a bug comment
		@attrib api=1
		@returns 
			error string if unsuccessful
	**/
	public function connect_bug_comment($c)
	{
		if(!is_oid($c))
		{
			return t("Pole piisavalt p&auml;dev id");
		}
		$obj = obj($c);
		$bug = obj($obj->parent());
		if($bug->class_id() != CL_BUG)
		{
			return t("Kommentaaril pole bugi");
		}
		$error = $this->check_if_has_other_customers($bug->prop("customer"));
		if($error)
		{
			return $error;
		}

		$this->connect(array("to"=> $bug->id(), "type" => "RELTYPE_BUG"));
		$bug->connect(array("to"=> $this->id(), "type" => "RELTYPE_BILL"));
		
		$obj ->set_prop("bill_id" , $this->id());
		$obj->save();
		return 0;
	}

	public function set_impl()
	{
		if(!$this->prop("impl"))
		{
			$u = get_instance(CL_USER);
			$this->set_prop("impl", $u->get_current_company());
			$this->save();
		}
	}

	/** sets project
		@attrib api=1 params=pos
		@param project required type=oid
			project object id
	**/
	public function set_project($project)
	{
		$this->connect(array(
			"to" => $project,
			"type" => "RELTYPE_PROJECT",
		));
	}

	/** sets customer
		@attrib api=1
		@param cust optional type=oid
			customer object id
		@param tasks optional type=array
			tasks or task rows of other expenses, array(id, id2, ..)
		@param bugs optional type=array
			bug comments , array(id, id2, ..)
		@returns string/int
			error, if true, if not, then 0
	**/
	public function set_customer($arr)
	{
		$bi = get_instance("applications/crm/crm_bill");
		if ($bi->can("view" , $arr["cust"]))
		{
			$cust = obj();
			$this->set_prop("customer", $arr["cust"]);
		}
		elseif(is_array($arr["tasks"]) && sizeof(is_array($arr["tasks"])))
		{
			$c_r_t = $arr["tasks"];
			if (is_array($c_r_t))
			{
				$c_r_t = reset($c_r_t);
			}
			$c_r_t_o = obj($c_r_t);
			if (($c_r_t_o->class_id() == CL_TASK_ROW) || ($c_r_t_o->class_id() == CL_CRM_EXPENSE))
			{
				$t_conns = $c_r_t_o->connections_to(array("from.class_id" => CL_TASK));
				$t_conn = reset($t_conns);
				if ($t_conn)
				{
					$c_r_t_o = $t_conn->from();
				}
			}
			$this->set_prop("customer", $c_r_t_o->prop("customer"));
			if(!$c_r_t_o->prop("customer"))
			{
				$cust = $c_r_t_o->get_first_obj_by_reltype("RELTYPE_CUSTOMER");
				if(is_object($cust))
				{
					$this->set_prop("customer", $cust->id());
				}
			}
		}
		
		//kui eelmiseid ei olnud v6i nad ei m6junud
		if((!(is_array($arr["tasks"]) || $bi->can("view" , $arr["cust"])) || (!$bi->can("view" , $this->prop("customer")))) && is_array($arr["bugs"]) && sizeof($arr["bugs"]))
		{
			foreach($arr["bugs"] as $bugc)
			{
				$c = obj($bugc);
				if(($c->class_id() == CL_BUG_COMMENT || $c->class_id() == CL_TASK_ROW)&& $bi->can("view" , $c->prop("parent.customer")))
				{
					$this->set_prop("customer" , $c->prop("parent.customer"));
					break;
				}
			}
		}
		
		$this->save();
		return $this->prop("customer");
	}

	private function add_row()
	{
		$br = obj();
		$br->set_class_id(CL_CRM_BILL_ROW);
		$br->set_parent($this->id());
		$br->save();
		return $br;
	}

	/** adds rows to bill
		@attrib api=1 params=name
		@param objects optional type=array
			object ids (tasks, meetings, bugs, calls, task rows etc.)
		@returns
	**/
	public function add_rows($arr)
	{
		$seti = get_instance(CL_CRM_SETTINGS);
		$co_inst = get_instance(CL_CRM_COMPANY);
		$sts = $seti->get_current_settings();
		define("DEFAULT_TAX", 0.18);
		$bug_rows = array();
		$task_rows = array();
		$tasks = array();
		foreach(safe_array($arr["objects"]) as $id)
		{
			$work = obj($id);
			switch($work->class_id())
			{
				case CL_CRM_MEETING:
				case CL_CRM_CALL:
				case CL_TASK:
					if($work->prop("deal_price"))
					{
						$agreement = $this->meta("agreement_price");
						if(!is_array($agreement))
						{
							$agreement = array();
						}
						$tax = DEFAULT_TAX;
						$deal_name = $work->name();
						$prod = "";
						if ($sts)
						{
							if(is_oid($sts->prop("bill_def_prod")) && $this->can("view",$sts->prop("bill_def_prod")))
							{
								$prod_obj = obj($sts->prop("bill_def_prod"));
								$prod = $sts->prop("bill_def_prod");
								$deal_name = $prod_obj->comment();
								$tr = obj($prod_obj->prop("tax_rate"));
								if (time() >= $tr->prop("act_from") && time() < $tr->prop("act_to"))
								{
									$tax = $tr->prop("tax_amt")/100.0;
								}
							}
						}
				
						$price = $work->prop("deal_price");
						if($work->prop("deal_has_tax"))
						{
							$price = $price / (1 + $tax);
						}
						$agreement[] = array(
							"unit" => $work->prop("deal_unit"),
							"price" => $price,
							"amt" => $work->prop("deal_amount"),
							"name" => $deal_name,
							"prod" => $prod,
							"comment" => $deal_name,
							"has_tax" => $work->prop("deal_has_tax"),
						);
						$this->set_meta("agreement_price" , $agreement);
						$this->save();
						$work->set_prop("send_bill" , 0);
						$work->save();
					//ridadele ikkagi arve kylge
						foreach($work->connections_from(array("type" => "RELTYPE_ROW")) as $c)
						{
							$row = $c->to();
							if (!$row->prop("bill_id") && $row->prop("on_bill"))
							{
								$row->set_prop("bill_id", $bill->id());
								$row->save();
							}
						}
						$work->set_billable_oe_bill_id($this->id());
						
						$tasks[] = $work->id();
					}
					break;
				case CL_TASK_ROW:
					if($work->prop("task.class_id") == CL_BUG)
					{
						$bug_rows[] = $work->id();
					}
					else
					{
						$task_rows[$work->task_id()][$work->id()] = $work->id();
					}
					$tasks[] = $work->task_id();
					break;
				case CL_CRM_EXPENSE:
					$expense = $work;
					$filt_by_row = $expense->id();
					// get task from row
					$conns = $expense->connections_to(array("from.class_id" => CL_TASK,"type" => "RELTYPE_EXPENSE"));
					$c = reset($conns);
					if ($c)
					{
						$tasks[] =  $c->prop("from");
					}

					$br = $this->add_row();
					$br->set_prop("comment", $expense->name());
					$br->set_prop("amt", 1);
					$br->set_prop("people", $expense->prop("who"));
					$br->set_prop("is_oe", 1);
					$date = $expense->prop("date");
					$br->set_prop("date", date("d.m.Y", mktime(0,0,0, $date["month"], $date["day"], $date["year"])));
					// get default prod
					if ($sts)
					{
						$br->set_prop("prod", $sts->prop("bill_def_prod"));
					}

					$sum = $co_inst->convert_to_company_currency(array(
						"sum" => $expense->prop("cost"),
						"o" => $expense,
						"company_curr" => $this->prop("customer.currency"),
					));

					$br->set_prop("price", $sum);
					$br->save();

					$expense->set_prop("bill_id", $this->id());
					$expense->save();
	
					$br->connect(array(
						"to" => $expense->id(),
						"type" => "RELTYPE_EXPENSE"
					));
					$this->connect(array(
						"to" => $br->id(),
						"type" => "RELTYPE_ROW"
					));
					break;
			}

		}

		if(sizeof($bug_rows))
		{
			$this->add_bug_comments($bug_rows);
		}

		foreach($tasks as $task)
		{
			$this->connect(array(
				"to" => $task,
				"reltype" => "RELTYPE_TASK"
			));
		}

		foreach($task_rows as $task => $rows)
		{
			$task_o = obj($task);
			foreach($rows as $row)
			{
				$row = obj($row);
				$row->set_prop("bill_id" , $this->id());
				$row->save();
				foreach($row->connections_from(array("type" => "RELTYPE_PROJECT")) as $c)
				{
					$this->set_project($c->prop("to"));
				}
				$br = $this->add_row();
				$br->set_prop("name", $row->prop("content"));
				$br->set_prop("amt", $row->prop("time_to_cust"));
//				$br->set_prop("prod", $row["prod"]);
				$br->set_prop("price", $task_o->prop("hr_price"));
				$br->set_prop("unit", t("tund"));
				$br->set_prop("has_tax", 1);
				$br->set_prop("date", date("d.m.Y", $row->prop("date")));
				$br->set_prop("people", $row->prop("impl"));
				// get default prod
		
				if ($sts)
				{
					$br->set_prop("prod", $sts->prop("bill_def_prod"));
				}
				$br->save();
				$br->connect(array(
					"to" => $task,
					"type" => "RELTYPE_TASK"
				));
				$br->connect(array(
					"to" => $row->id(),
					"type" => "RELTYPE_TASK_ROW"
				));
				$this->connect(array(
					"to" => $br->id(),
					"type" => "RELTYPE_ROW"
				));
			}
		}

//------ send bill vaja maha saada, kui k6ik on arvele l2inud
// 			if(!$task_rows_to_bill_count[$task]) $task_rows_to_bill_count[$task] = 0;
// 			$task_rows_to_bill_count[$task] ++;
// 			if($task_rows_to_bill_count[$task] == $_POST["count"][$task])
// 			{
// 				$task_o->set_prop("send_bill", 0);
// 				$task_o->save();
// 			}
		return $this->id();
	}

	/** f the bill has an impl and customer, then check if they have a customer relation and if so, then get the due days from that
		@attrib api=1
	**/
	public function set_due_date()
	{
		if (is_oid($this->prop("customer")) && is_oid($this->prop("impl")))
		{
			$cust_rel_list = new object_list(array(
				"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
				"lang_id" => array(),
				"site_id" => array(),
				"buyer" => $this->prop("customer"),
				"seller" => $this->prop("impl")
			));
			if ($cust_rel_list->count())
			{
				$cust_rel = $cust_rel_list->begin();
				$this->set_prop("bill_due_date_days", $cust_rel->prop("bill_due_date_days"));
			}

			if(!$this->prop("bill_due_date_days"))
			{
				$this->set_prop("bill_due_date_days", $this->prop("customer.bill_due_days"));
			}

			$bt = time();
			$this->set_prop("bill_due_date",
				mktime(3,3,3, date("m", $bt), date("d", $bt) + $this->prop("bill_due_date_days"), date("Y", $bt))
			);
			$this->save();
		}
	}
}

?>
