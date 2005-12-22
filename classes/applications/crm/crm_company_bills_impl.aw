<?php

class crm_company_bills_impl extends class_base
{
	function crm_company_bills_impl()
	{
		$this->init();
	}

	function _init_bill_proj_list_t(&$t)
	{
		$t->define_field(array(
			"caption" => t("Loo arve"),
			"name" => "open",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Projekt"),
			"name" => "name",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Klient"),
			"name" => "cust",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Summa"),
			"name" => "sum",
			"align" => "center",
			"sortable" => 1
		));
	}

	function _get_bill_proj_list($arr)
	{	
		if ($arr["request"]["proj"] || $arr["request"]["cust"])
		{
			return PROP_IGNORE;
		}

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bill_proj_list_t($t);

		// get all open tasks
		$i = get_instance(CL_CRM_COMPANY);
		//$proj = $i->get_my_projects();
		$proj_i = get_instance(CL_PROJECT);
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
			"site_id" => array(),
			"lang_id" => array()
		));
		foreach($ol->ids() as $p)
		{
			$events = $proj_i->get_events(array(
				"id" => $p,
				"range" => array(
					"start" => 1,
					"end" => time() + 24*3600*365*10
				)
			));
			if (!count($events))
			{
				continue;
			}
			$evt_ol = new object_list(array(
				"class_id" => CL_TASK,
				"oid" => array_keys($events),
				"bill_no" => new obj_predicate_compare(OBJ_COMP_EQUAL, ""),
				"send_bill" => 1
			));
			if (!$evt_ol->count())
			{
				continue;
			}
			$sum = 0;
			$task_i = get_instance(CL_TASK);
			$has_rows = false;
			foreach($evt_ol->arr() as $evt)
			{
				if (!$evt->prop("send_bill"))
				{
					continue;
				}
				$rows = $task_i->get_task_bill_rows($evt);
				if (!count($rows))
				{
					continue;
				}
				$has_rows = true;
				foreach($rows as $row)
				{
					$sum += $row["sum"];
				}
			}

			if (!$has_rows)
			{
				continue;
			}
			$po = obj($p);

			$t->define_data(array(
				"name" => html::obj_change_url($po),
				"open" => html::href(array(
					"url" => aw_url_change_var("proj", $p),
					"caption" => t("Loo arve")
				)),
				"cust" => html::obj_change_url(reset($po->prop("orderer"))),
				"sum" => number_format($sum, 2)
			));
		}
	}

	function _init_bill_task_list_t(&$t)
	{
		$t->define_field(array(
			"caption" => t("Juhtumi nimi"),
			"name" => "name",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Tunde"),
			"name" => "hrs",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Tunni hind"),
			"name" => "hr_price",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Summa"),
			"name" => "sum",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_bill_task_list($arr)
	{
		if (!$arr["request"]["proj"] && !$arr["request"]["cust"])
		{
			return PROP_IGNORE;
		}
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bill_task_list_t($t);

		if ($arr["request"]["cust"])
		{
			$i = get_instance(CL_CRM_COMPANY);
			$arr["request"]["proj"] = $i->get_projects_for_customer(obj($arr["request"]["cust"]));
		}
		$proj_i = get_instance(CL_PROJECT);
		$events = array();
		$awa = new aw_array($arr["request"]["proj"]);
		foreach($awa->get() as $p)
		{
			$events += $proj_i->get_events(array(
				"id" => $p,
				"range" => array(
					"start" => 1,
					"end" => time() + 24*3600*365*10
				)
			));
		}
		$task_i = get_instance(CL_TASK);
		foreach($events as $evt)
		{
			$o = obj($evt["id"]);
			if ($o->prop("send_bill"))
			{
				if ($o->prop("bill_no") == "")
				{
					$sum = 0;
					$hrs = 0;
					// get task rows and calc sum from those
					$rows = $task_i->get_task_bill_rows($o);
					foreach($rows as $row)
					{
						$sum += $row["sum"];
						$hrs += $row["amt"];
					}

					$t->define_data(array(
						"name" => html::get_change_url($o->id(), array("return_url" => get_ru()), parse_obj_name($o->name())),
						"oid" => $o->id(),
						"hrs" => $hrs,
						"hr_price" => number_format($o->prop("hr_price"),2),
						"sum" => number_format($sum,2)
					));
				}
			}
		}
	}

	function _get_bill_tb($arr)
	{
		if (!$arr["request"]["proj"])
		{
			return PROP_IGNORE;
		}

		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_button(array(
			"name" => "create_bill",
			"img" => "save.gif",
			"tooltip" => t("Koosta arve"),
			"action" => "create_bill"
		));
	}

	function _init_bills_list_t(&$t, $r)
	{
		$t->define_field(array(
			"name" => "bill_no",
			"caption" => t("Number"),
			"sortable" => 1,
			"numeric" => 1
		));
		if ($r["group"] == "bills_monthly")
		{
			$t->define_field(array(
				"name" => "create_new",
				"caption" => t("Loo uus"),
				"sortable" => 1,
				"numeric" => 1
			));
		}
		$t->define_field(array(
			"name" => "bill_date",
			"caption" => t("Kuup&auml;ev"),
			"type" => "time",
			"format" => "d.m.Y",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "bill_due_date",
			"caption" => t("Makset&auml;htaeg"),
			"type" => "time",
			"format" => "d.m.Y",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "customer",
			"caption" => t("Klient"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "client_manager",
			"caption" => t("Kliendihaldur"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"sortable" => 1,
			"numeric" => 1
		));

		if ($r["group"] != "bills_monthly")
		{
			$t->define_field(array(
				"name" => "state",
				"caption" => t("Staatus"),
				"sortable" => 1
			));
		}
		$t->define_field(array(
			"name" => "print",
			"caption" => t("Tr&uuml;ki"),
			"sortable" => 1
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_bills_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bills_list_t($t, $arr["request"]);

		$d = get_instance("applications/crm/crm_data");
		if ($arr["request"]["group"] == "bills_monthly")
		{
			$bills = $d->get_bills_by_co($arr["obj_inst"], array("monthly" => 1));
		}
		else
		{
			$filt = array();
			if ($arr["request"]["bill_s_search"] == "")
			{
				// init default search opts
				$u = get_instance(CL_USER);
				$p = obj($u->get_current_person());
				$filt["client_mgr"] = $p->name();
				$filt["bill_date_range"] = array(
					"from" => mktime(0,0,0, date("m"), date("d"), date("Y")-1),
					"to" => time()
				);
				$filt["state"] = "0";
			}
			else
			{
				$filt["customer"] = "%".$arr["request"]["bill_s_cust"]."%";
				$filt["bill_no"] = "%".$arr["request"]["bill_s_bill_no"]."%";
				$filt["bill_date_range"] = array(
					"from" => date_edit::get_timestamp($arr["request"]["bill_s_from"]),
					"to" => date_edit::get_timestamp($arr["request"]["bill_s_to"])
				);
				$filt["client_mgr"] = "%".$arr["request"]["bill_s_client_mgr"]."%";
				$filt["state"] = $arr["request"]["bill_s_status"];
			}
			$bills = $d->get_bills_by_co($arr["obj_inst"], $filt);
		}
		$bill_i = get_instance(CL_CRM_BILL);

		if ($arr["request"]["export_hr"] == 1)
		{
			$this->_do_export_hr($bills, $arr);
		}

		foreach($bills->arr() as $bill)
		{
			$cust = "";
			$cm = "";
			if (is_oid($bill->prop("customer")) && $this->can("view", $bill->prop("customer")))
			{
				$tmp = obj($bill->prop("customer"));
				$cust = html::get_change_url($tmp->id(), array("return_url" => get_ru()), $tmp->name());
				$cm = html::obj_change_url($tmp->prop("client_manager"));
			}
			if ($arr["request"]["group"] == "bills_search")
			{
				$state = $bill_i->states[$bill->prop("state")];
			}	
			else
			{
				$state = html::select(array(
					"options" => $bill_i->states,
					"selected" => $bill->prop("state"),
					"name" => "bill_states[".$bill->id()."]"
				));
			}
			$cursum = $bill_i->get_sum($bill);

			$pop = get_instance("vcl/popup_menu");
			$pop->begin_menu("bill_".$bill->id());
			$pop->add_item(Array(
				"text" => t("Prindi arve"),
				"link" => $this->mk_my_orb("change", array("openprintdialog" => 1,"id" => $bill->id(), "group" => "preview"), CL_CRM_BILL)
			));
			$pop->add_item(Array(
				"text" => t("Prindi arve lisa"),
				"link" => $this->mk_my_orb("change", array("openprintdialog" => 1,"id" => $bill->id(), "group" => "preview_add"), CL_CRM_BILL)
			));
			$t->define_data(array(
				"bill_no" => html::get_change_url($bill->id(), array("return_url" => get_ru()), parse_obj_name($bill->prop("bill_no"))),
				"create_new" => html::href(array(
					"url" => $this->mk_my_orb("create_new_monthly_bill", array(
						"id" => $bill->id(), 
						"co" => $arr["obj_inst"]->id(),
						"post_ru" => get_ru()
						), CL_CRM_COMPANY),
					"caption" => t("Loo uus")
				)),
				"bill_date" => $bill->prop("bill_date"),
				"bill_due_date" => $bill->prop("bill_due_date"),
				"customer" => $cust,
				"state" => $state,
				"sum" => number_format($cursum, 2),
				"client_manager" => $cm,
				"oid" => $bill->id(),
				"print" => $pop->get_menu()
			));
			$sum+= $cursum;
		}

		$t->set_default_sorder("desc");
		$t->set_default_sortby("bill_no");
		$t->sort_by();
		$t->set_sortable(false);

		$t->define_data(array(
			"sum" => "<b>".number_format($sum, 2)."</b>",
			"bill_no" => t("<b>Summa</b>")
		));
	}

	function _get_bill_s_client_mgr($arr)
	{
		if ($arr["request"]["bill_s_search"] == "")
		{
			$u = get_instance(CL_USER);
			$p = obj($u->get_current_person());
			$v = $p->name();
		}
		else
		{
			$v = $arr["request"]["bill_s_client_mgr"];
		}
		$tt = t("Kustuta");
		$arr["prop"]["value"] = html::textbox(array(
			"name" => "bill_s_client_mgr",
			"value" => $v,
			"size" => 25
		))."<a href='javascript:void(0)' onClick='document.changeform.bill_s_client_mgr.value=\"\"' title=\"$tt\" alt=\"$tt\"><img title=\"$tt\" alt=\"$tt\" src='".aw_ini_get("baseurl")."/automatweb/images/icons/delete.gif' border=0></a>";
		return PROP_OK;
	}

	function _get_bill_s_status($arr)
	{
		$b = get_instance(CL_CRM_BILL);
		$arr["prop"]["options"] = array("-1" => "") + $b->states;
		if ($arr["request"]["bill_s_search"] == "")
		{
			$arr["prop"]["value"] = 0;
		}
		else
		{
			$arr["prop"]["value"] = $arr["request"]["bill_s_status"];
		}
	}

	function _get_bills_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta'),
			'action' => 'save_bill_list',
		));
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud arved'),
			"confirm" => t("Oled kindel et soovid valitud arved kustutada?"),
			'action' => 'delete_bills',
		));

		$tb->add_separator();

		$tb->add_menu_button(array(
			'name'=>'export',
			'tooltip'=> t('Ekspordi'),
			"img" => "export.gif"
		));
		
		$tb->add_menu_item(array(
			'parent'=>'export',
			'text' => t("Hansa raama"),
			'link' => aw_url_change_var("export_hr", 1)
		));
	}

	function _get_bills_mon_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta'),
			'action' => 'create_new_monthly_bill',
		));
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud arved'),
			"confirm" => t("Oled kindel et soovid valitud arved kustutada?"),
			'action' => 'delete_bills',
		));
	}

	function _get_bs_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "create_bill",
			"tooltip" => t("Loo arve"),
			"img" => "save.gif",
			"action" => "create_bill"
		));
		
	}

	function _do_export_hr($bills, $arr)
	{
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		$fn = trim(mb_strtoupper($p->prop("firstname")));

		$ct = array();
		$i = get_instance(CL_CRM_BILL);
		foreach($bills->arr() as $b)
		{
			// bill info row
			$brow = array();
			$brow[] = $b->prop("bill_no");						// arve nr
			$brow[] = date("d.m.Y", $b->prop("bill_date"));		// arve kuup 
			$brow[] = date("d.m.Y", $b->prop("bill_due_date"));	// tasumist�htaeg  
			$brow[] = 0;										// 0 (teadmata - vaikev��rtus 0)
			$brow[] = 1;										// 1 (teadmata -vaikev��rtus 1) 
			$brow[] = $b->prop("bill_due_date_days"); 			// 7(tasumistingimuse kood - v�rdusta hetkel p�evade arvuga)
			$brow[] = 7;										// 7(tasumistingimus)  
			$brow[] = "";
			$brow[] = "";
			$brow[] = "";
			$brow[] = "";
			$brow[] = "";
			$brow[] = 0;			//    0 (teadmata - vaikev��rtus 0) 
			$brow[] = "0,00";		// 0,00 (teadmata - vaikev��rtus 0,00) 
			$brow[] = 1;			// 1 (teadmata - vaikev��rtus 1)  
			$brow[] = $fn;			// OBJEKT (kasutaja eesnimi suurte t�htedega, nt TEDDI)
			$brow[] = 0;			//  0 (teadmata - vaikev��rtus 0)    
			$brow[] = $i->get_bill_currency($b);	// EEK (valuuta)  
			$brow[] = date("d.m.Y", $b->prop("bill_date"));		// arve kuup�ev
			$brow[] = 0;			// (teadmata - vaikev��rtus 0)    
			$brow[] = "15,65";		// (EURO kurss) 
			$brow[] = "1,00";		// (kursi suhe, vaikev��rtus 1,00)  
			$ct[] = join("\t", $brow);

			// customer info row
			$custr = array();

			if ($this->can("view", $b->prop("customer")))
			{
				$cust = obj($b->prop("customer"));

				$custr[] = $cust->comment();	// kliendi kood hansaraamas
				$custr[] = $cust->name();	// kliendi kood hansaraamas

				$cust_code = $cust->prop("code");
				list($cm) = explode(" ", $cust->prop_str("client_manager"));
				$cm = mb_strtoupper($cm);
			}
			else
			{
				$custr[] = "";
				$custr[] = "";
			}
			$ct[] = join("\t", $custr);
			$ct[] = join("\t", array("", "", "", ""));	// esindajad
	
			// payment row
			$pr = array();
			$pr[] = "0,00";	// (teadmata - vaikev��rtus 0,00) 
			$pr[] = str_replace(".", ",", $i->get_bill_sum($b,BILL_SUM_WO_TAX));		// 33492,03 (summa k�ibemaksuta)  
			$pr[] = "";
			$pr[] = str_replace(".", ",", $i->get_bill_sum($b,BILL_SUM_TAX));		// 6028,57 (k�ibemaks) 
			$pr[] = str_replace(".", ",", $i->get_bill_sum($b,BILL_SUM));		// 39520,60 (Summa koos k�ibemaksuga)      
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "0,00";	// (teadmata - vaikev��rtus 0,00)          
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "0,00"; //(teadmata - vaikev��rtus 0,00) 
			$pr[] = "";		//LADU (v�ib ka t�hjusega asendada)
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";		// 90000 (teadmata, v�ib ka t�hjusega asendada) 
			$pr[] = "";		// 00014 (teadmata, v�ib ka t�hjusega asendada)  
			$pr[] = "";
			$pr[] = "0";	// (teadmata - vaikev��rtus 0) 
			$pr[] = "";
			$pr[] = str_replace(".", ",", $i->get_bill_sum($b,BILL_SUM));	//39520,60 (Summa koos k�ibemaksuga)    
			$pr[] = "";
			$pr[] = "";
			$pr[] = str_replace(".", ",", $i->get_bill_sum($b,BILL_SUM_WO_TAX));		// 33492,03 (summa k�ibemaksuta)  
			$pr[] = "0";	// (teadmata - vaikev��rtus 0) 
			$pr[] = "0";	//  (teadmata - vaikev��rtus 0)   
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "";
			$pr[] = "0";	// (teadmata - vaikev��rtus 0) 
			$pr[] = "";
			$pr[] = "0";	// 0(teadmata - vaikev��rtus 0) 
			$pr[] = "0";	// (teadmata - vaikev��rtus 0) 
			$pr[] = "";	
			$pr[] =	"0";	// (teadmata - vaikev��rtus 0)  
			$pr[] = str_replace(".", ",", $i->get_bill_sum($b, BILL_AMT)); //77,00 (kogus kokku) 
			$pr[] = "0,00";	// (teadmata - vaikev��rtus 0,00)  
			$pr[] = "0,00";	// (teadmata - vaikev��rtus 0,00)  
			$pr[] = "0";		// (teadmata - vaikev��rtus 0) 
			$pr[] = "";
			$pr[] = "0";	//(teadmata - vaikev��rtus 0) 
			$pr[] = "0";	//(teadmata - vaikev��rtus 0)  
			$pr[] = "";
			$pr[] = "0"; //(teadmata - vaikev��rtus 0)			
			$ct[] = join("\t", $pr);

			$rows = $i->get_bill_rows($b);
			foreach($rows as $idx => $row)
			{
				$ri = array();
				$ri[] = "1";	// (teadmata, vaikev��rtus 1)) 
				$ri[] = $idx;	// TEST (artikli kood) 
				$ri[] = $row["amt"];	//33 (kogus) 
				$ri[] = $row["name"];	// testartikkel (toimetuse rea sisu) 
				$ri[] = str_replace(".", ",", $row["price"]);	// 555,00 (�hiku hind) 
				$ri[] = str_replace(".", ",", $row["sum"]);	// 16300,35 (rea summa km-ta) 
				$ri[] = str_replace(".", ",", $b->prop("disc")); //11,0 (ale%) 
				$ri[] = 3100;		// (konto)    
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = str_replace(".", ",", $row["sum"]);	// 16300,35 (rea summa km-ta) 
				$ri[] = "";
				$ri[] = "1";	// (k�ibemaksukood)         
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = "";
				$ri[] = $row["unit"];	//TK (�hik)     



				$ct[] = join("\t", $ri);
			}

			$ct[] = ""; // next bill
		}

		header("Content-type: text/plain");
		header('Content-Disposition: attachment; filename="arved.txt"');
		echo "format	\n";	
		echo "1	44	1	0	1	\n";
		echo "\n";
		echo "sysformat	\n";
		echo "1	1	1	1	.	,	 	\n";
		echo "\n";
		echo "commentstring	\n";
		echo "\n";
		echo "\n";
		echo "fakt1	\n";

		die(join("\n", $ct));
	}
}
?>