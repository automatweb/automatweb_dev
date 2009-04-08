<?php
/*
@classinfo syslog_type=ST_MRP_ORDER_PRINT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_mrp_order_print master_index=brother_of master_table=objects index=aw_oid
@extends mrp/orders/mrp_order

@default table=aw_mrp_order_print
@default group=general

	@property amount type=textbox size=5 field=aw_amount group=general,price
	@caption Kogus

	@property format type=textbox  field=aw_format
	@caption Formaat

	@property deadline type=date_select field=aw_deadline
	@caption T&auml;htaeg

	@property materials type=textbox  field=aw_materials
	@caption Materjalid

@default group=grp_case_workflow

	@property workflow_errors type=text store=no no_caption=1 

	@layout vsplitbox type=hbox width=25%:75% 
		@layout left_pane type=vbox parent=vsplitbox 
			@layout general_info type=vbox parent=left_pane area_caption=Projekti&nbsp;&uuml;ldandmed closeable=1 
			@layout resource_tree type=vbox parent=left_pane area_caption=Ressursid&nbsp;kategooriate&nbsp;kaupa closeable=1
				@property resource_tree type=text store=no no_caption=1 parent=resource_tree
		@property workflow_table type=table store=no no_caption=1 parent=vsplitbox


@default group=grp_case_materials

	@property materials_table type=table store=no no_caption=1

@default group=price

	@property material_price type=table store=no no_caption=1
	@caption Materjalide hind

	@property resource_price type=table store=no no_caption=1
	@caption Ressursside hind

	@property cover_price type=table store=no no_caption=1
	@caption Katete hind

	@property tot_price type=table store=no no_caption=1
	@caption Koguhind

	@property final_price type=textbox size=10 field=aw_final_price 
	@caption L&otilde;plik hind

@default group=preview

	@property preview type=text store=no no_caption=1

@groupinfo grp_case_workflow caption="Ressursid ja t&ouml;&ouml;voog"
@groupinfo grp_case_materials caption="Materjalid"
@groupinfo price caption="Hind"
@groupinfo preview caption="Eelvaade" no_submit=1

@reltype SEL_COVER value=10 clid=CL_MRP_ORDER_COVER
@caption Kate

*/

class mrp_order_print extends mrp_order
{
	function mrp_order_print()
	{
		$this->init(array(
			"tpldir" => "mrp/orders/mrp_order_print",
			"clid" => CL_MRP_ORDER_PRINT
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
		}

		return $retval;
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
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_mrp_order_print(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_amount":
			case "aw_tiraazh":
			case "aw_deadline":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;

			case "aw_format":
			case "aw_materials":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(255)"
				));
				return true;

			case "aw_final_price":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "double"
				));
				return true;
		}
	}

	private function _fwd_case($arr)
	{
		$case = $arr["obj_inst"]->get_case();	
		$tmp = array(
			"prop" => &$arr["prop"],
			"obj_inst" => $case,
			"request" => $arr["request"]
		);
		get_instance(CL_MRP_CASE)->get_property($tmp);
	}

	function _get_workflow_errors($arr)
	{
		$this->_fwd_case($arr);
	}

	function _get_resource_tree($arr)
	{
		$this->_fwd_case($arr);
	}

	function _get_workflow_table($arr)
	{
		$this->_fwd_case($arr);
		$t = $arr["prop"]["vcl_inst"];
		$t->remove_field("minstart");
		$t->remove_field("pre_buffer");
		$t->remove_field("post_buffer");
		$t->remove_field("prerequisites");
		$t->remove_field("comment");
		$t->remove_field("length");
		$t->define_field(array(
			"name" => "sales_comment",
			"caption" => t("M&uuml;&uuml;gi kommentaar"),
			"tooltip" => t("M&uuml;&uuml;gi kommentaar"),
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"tooltip" => t("Hind"),
			"align" => "right",
			"numeric" => 1
		));

		foreach($t->get_data() as $id => $item)
		{
			$jo = obj($item["job_id"]);
			$item["sales_comment"] = html::textbox(array(
				"name" => "sc[".$jo->id()."]",
				"value" => $jo->sales_comment
			));
			$item["price"] = number_format($arr["obj_inst"]->get_price_for_job($jo), 2);
			$t->set_data($id, $item);
		}
	}

	private function _fwd_case_set($arr)
	{
		$case = $arr["obj_inst"]->get_case();	
		$tmp = array(
			"prop" => &$arr["prop"],
			"obj_inst" => $case,
			"request" => $arr["request"]
		);
		get_instance(CL_MRP_CASE)->set_property($tmp);
	}

	function _set_workflow_errors($arr)
	{
		$this->_fwd_case_set($arr);
	}

	function _set_resource_tree($arr)
	{
		$this->_fwd_case_set($arr);
	}

	function _set_workflow_table($arr)
	{
		foreach(safe_array($arr["request"]["sc"]) as $id => $comm)
		{
			$jo = obj($id);
			if ($jo->sales_comment != $comm)
			{
				$jo->sales_comment = $comm; 
				$jo->save();
			}
		}

		foreach(safe_array($arr["request"]["selection"]) as $id  => $id2)
		{
			if ($id && $id == $id2)
			{
				obj($id)->delete();
			}
		}
	}

	function callback_post_save($arr)
	{
		if ($arr["request"]["group"] == "grp_case_workflow")
		{
			$case = $arr["obj_inst"]->get_case();	
			$tmp = array(
				"prop" => &$arr["prop"],
				"obj_inst" => $case,
				"request" => $arr["request"]
			);
			get_instance(CL_MRP_CASE)->callback_post_save($tmp);
		}
	}

	private function _init_materials_table($t)
	{
		$t->define_field(array(
			"name" => "material",
			"caption" => t("Materjal"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "unit",
			"caption" => t("&Uuml;hik"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "amount",
			"caption" => t("Kogus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "sales_price",
			"caption" => t("M&uuml;&uuml;gihind"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "purchase_price",
			"caption" => t("Ostuhind"),
			"align" => "center",
		));
	}

	function _get_materials_table($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_materials_table($t);

		$mj = get_instance(CL_MRP_JOB);

		$cur_list = get_instance(CL_CURRENCY)->get_list(RET_NAME);

		// put all jobs in table and for each job list all possible materials and let the user pick some
		foreach($arr["obj_inst"]->get_job_list() as $job)
		{
			$material_expenses = $job->get_material_expense_list();

			// for each job list all possible materials
			$materials = $job->get_resource()->get_possible_materials();
			foreach($materials as $material)
			{
				$amt = 0;
				$unit = 0;
				$price = array();
				$purchase_price = 0;
				if (isset($material_expenses[$material->product]))
				{
					$amt = $material_expenses[$material->product]->amount;
					$unit = $material_expenses[$material->product]->unit;
			
					// get price for set amount/unit
					
					foreach($cur_list as $cur_id => $cur_name)
					{
						$price[] = ($material->product()->price_get_by_currency(obj($cur_id)) * $amt)." ".$cur_name;
					}
					$purchase_price = $material->product()->purchase_price * $amt;
				}
				
				$t->define_data(array(
					"job" => $job->name(),
					"material" => $material->product()->name(),
					"unit" => $mj->get_materials_unitselect($material->product(), $unit, $job->id()),
					"amount" => html::textbox(array("name" => "jobs[".$job->id()."][amount][".$material->product."]", "size" => 5, "value" => $amt)),
					"sales_price" => join(" ", $price),
					"purchase_price" => $purchase_price
				));
			}
		}

		$t->set_rgroupby(array("job" => "job"));
	}

	function _set_materials_table($arr)
	{
		$mj = get_instance(CL_MRP_JOB);

		// put all jobs in table and for each job list all possible materials and let the user pick some
		foreach($arr["obj_inst"]->get_job_list() as $job)
		{
			$job->save_materials(array(
				"obj_inst" => $job,
				"request" => $arr["request"]["jobs"][$job->id()]
			));
		}
	}

	function _get_material_price($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->set_caption(t("Materjalide hind vastavalt kogustele"));
		$amt = $arr["obj_inst"]->amount;
		$data = array();
		if ($amt > 1000)
		{
			$from = max(-3, (-floor($amt / 1000))+1);
			$to = abs($from);

			$t->define_field(array(
				"name" => "material",
				"caption" => t("Materjal"),
				"align" => "right",
				"width" => "20%"
			));

			$sums = array();
			for($i = $from; $i <= $to; $i++)
			{
				$v = $amt + ($i * 1000);
				$t->define_field(array(
					"name" => $v,
					"caption" => $v,
					"align" => "right",
					"width" => "10%"
				));
				$sums[$v] = array();
			}
			foreach($arr["obj_inst"]->get_job_list() as $job)
			{
				$material_expenses = $job->get_material_expense_list();

				foreach($material_expenses as $material_id => $row)
				{
					$mo = obj($material_id);
					$data = array("material" => $mo->name());
					for($i = $from; $i <= $to; $i++)
					{
						$v = $amt + ($i * 1000);
						$mp = $this->_get_mat_price_for_amt($arr["obj_inst"], $v, $mo, $row);
						foreach($mp as $mp_k => $mp_v)
						{
							$sums[$v][$mp_k] += $mp_v;
						}
						$tmp = join(" ", $mp);
						if ($v == $amt)
						{
							$tmp = html::strong($tmp);
						}
						$data[$v] = $tmp;
					}
					$t->define_data($data);
				}
			}
			foreach($sums as $k => $v)
			{
				$sums[$k] = join(" ", $v);
			}
			$sums[$amt] = html::strong($sums[$amt]);
			$sums["material"] = html::strong("Kokku");
			$t->define_data($sums);
		}

		$t->set_sortable(false);
	}
	
	private function _get_mat_price_for_amt($o, $amt, $mo, $expense_row)
	{
		$mj = get_instance(CL_MRP_JOB);

		$cur_list = get_instance(CL_CURRENCY)->get_list(RET_NAME);

		$tot_mat_price = array(); 

		// calculate amount for the amount requested approximately
		$per_one = $expense_row->amount / $o->amount;
		$calc_amt = $per_one * $amt;

		foreach($cur_list as $cur_id => $cur_name)
		{
			$tot_mat_price[$cur_id] += ($mo->price_get_by_currency(obj($cur_id)) * $calc_amt);
		}
		return array(reset($tot_mat_price));
		

		// put all jobs in table and for each job list all possible materials and let the user pick some
		foreach($o->get_job_list() as $job)
		{
			$material_expenses = $job->get_material_expense_list();

			// for each job list all possible materials
			$materials = $job->get_resource()->get_possible_materials();
			foreach($materials as $material)
			{
				$amt = 0;
				$unit = 0;
				$price = array();
				$purchase_price = 0;
				if (isset($material_expenses[$material->product]))
				{
					$amt = $material_expenses[$material->product]->amount;
					$unit = $material_expenses[$material->product]->unit;
			
					// get price for set amount/unit
					
					foreach($cur_list as $cur_id => $cur_name)
					{
						$tot_mat_price[$cur_id] += ($material->product()->price_get_by_currency(obj($cur_id)) * $amt);
					}
				}
			}
		}
		return array(reset($tot_mat_price));
		return $tot_mat_price;
	}

	function _get_resource_price($arr)
	{	
		$t = $arr["prop"]["vcl_inst"];
		$t->set_caption(t("Resursside kasutuse hind vastavalt kogustele"));
		$amt = $arr["obj_inst"]->amount;
		$data = array();
		if ($amt > 1000)
		{
			$t->define_field(array(
				"name" => "resource",
				"caption" => t("Resurss"),
				"align" => "right",
				"width" => "20%"
			));
			$from = max(-3, (-floor($amt / 1000))+1);
			$to = abs($from);
			$sums = array();
			for($i = $from; $i <= $to; $i++)
			{
				$v = $amt + ($i * 1000);
				$t->define_field(array(
					"name" => $v,
					"caption" => $v,
					"align" => "right",
					"width" => "10%"
				));
				$sums[$v] = 0;
			}

			
			foreach($arr["obj_inst"]->get_job_list() as $job)
			{
				$resource = $job->get_resource();
				$data = array("resource" => $resource->name);
				for($i = $from; $i <= $to; $i++)
				{
					$v = $amt + ($i * 1000);
					$tmp = $this->_get_resource_price_for_amt_and_resource($arr["obj_inst"], $v, $resource);
					$sums[$v] += $tmp;
					if ($v == $amt)
					{
						$tmp = html::strong($tmp);
					}
					$data[$v] = $tmp;
				}
				$t->define_data($data);
			}
			$sums[$amt] = html::strong($sums[$amt]);
			$sums["resource"] = html::strong("Kokku");
			$t->define_data($sums);
		}
		$t->set_sortable(false);
	}

	private function _get_resource_price_for_amt_and_resource($o, $v, $resource)
	{
		// get pricelist and go over all resources in the job list and calc prices for those
		$pricelist = $o->mrp_pricelist();
		$pr = 0;
		$pr += $pricelist->get_price_for_resource_and_amount($resource, $v);

		return $pr;
	}

	function _get_cover_price($arr)
	{	
		$t = $arr["prop"]["vcl_inst"];
		$t->set_caption(t("Katete hind vastavalt kogustele"));
		$amt = $arr["obj_inst"]->amount;
		$data = array();
		if ($amt > 1000)
		{
			$from = max(-3, (-floor($amt / 1000))+1);
			$to = abs($from);
			$t->define_field(array(
				"name" => "cover",
				"caption" => t("Kate"),
				"align" => "right",
				"width" => "20%"
			));
			$sums = array();
			for($i = $from; $i <= $to; $i++)
			{
				$v = $amt + ($i * 1000);
				$t->define_field(array(
					"name" => $v,
					"caption" => $v,
					"align" => "right",
					"width" => "10%"
				));
				$sums[$v] = 0;
			}

			$sel_covers = $arr["obj_inst"]->get_selected_covers();

			foreach($arr["obj_inst"]->workspace()->get_all_covers() as $cover)
			{
				$data = array(
					"cover" => $cover->name()." ".html::checkbox(array(
						"name" => "sel_covers[".$cover->id()."]",
						"value" => 1,
						"checked" => isset($sel_covers[$cover->id()])
					))
				);
				for($i = $from; $i <= $to; $i++)
				{
					$v = $amt + ($i * 1000);
					$tmp = $this->_get_cover_price_for_amt($arr["obj_inst"], $v, $cover);
					if (isset($sel_covers[$cover->id()]))
					{
						$sums[$v] += $tmp;
					}
					if ($v == $amt)
					{
						$tmp = html::strong($tmp);
					}
					$data[$v] = $tmp;
				}
				$t->define_data($data);
			}

			$t->set_sortable(false);
			$sums[$amt] = html::strong($sums[$amt]);
			$sums["cover"] = html::strong("Kokku");
			$t->define_data($sums);
		}
	}

	private function _get_cover_price_for_amt($o, $v, $cover)
	{
		return $cover->get_price_for_order_and_amt($o, $v);
	}

	function _set_cover_price($arr)
	{
		$arr["obj_inst"]->set_selected_covers(array_keys($arr["request"]["sel_covers"]));
	}

	function _get_tot_price($arr)
	{	
		$t = $arr["prop"]["vcl_inst"];
		$t->set_caption(t("Kogu hind vastavalt kogustele"));
		$amt = $arr["obj_inst"]->amount;
		$data = array();
		if ($amt > 1000)
		{
			$from = max(-3, (-floor($amt / 1000))+1);
			$to = abs($from);
			$t->define_field(array(
				"name" => "a",
				"caption" => "",
				"align" => "right",
				"width" => "20%"
			));

			for($i = $from; $i <= $to; $i++)
			{
				$v = $amt + ($i * 1000);
				$t->define_field(array(
					"name" => $v,
					"caption" => $v,
					"align" => "right",
					"width" => "10%"
				));
				$tmp = $this->_get_tot_price_for_amt($arr["obj_inst"], $v);
				if ($v == $amt)
				{
					$tmp = html::strong($tmp);
				}
				$data[$v] = $tmp;
			}
		}
		$t->define_data($data);
	}

	private function _get_tot_price_for_amt($o, $v)
	{
		return $o->get_total_price_for_amt($v, true);
	}

	function _get_preview($arr)
	{
		$this->read_template("preview.tpl");

		// logo and other data from seller/buyer
		$this->_preview_insert_co_data($arr["obj_inst"]->workspace()->owner_co(), "seller");
		$this->_preview_insert_co_data($arr["obj_inst"]->customer(), "orderer");

		$this->vars(array(
			"price" => $arr["obj_inst"]->get_total_price(),
			"name" => $arr["obj_inst"]->name()
		));

		$arr["prop"]["value"] = $this->parse();
	}

	private function _preview_insert_co_data($o, $prefix)
	{
		$d = array();
		foreach($o->properties() as $k => $v)
		{
			$d[$prefix."_".$k] = $v;
		}
		if ($this->can("view", $o->logo))
		{
			$d[$prefix."_logo"] = html::img(array(
				"url" => obj_link($o->logo)
			));
		}
		$this->vars($d);
	}
}

?>
