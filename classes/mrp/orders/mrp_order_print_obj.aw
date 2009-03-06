<?php

class mrp_order_print_obj extends mrp_order_obj
{
	private $sel_cover_list;

	function get_job_list()
	{
		// jobs from case
		return $this->get_case()->get_job_list();
	}

	function set_prop($k, $v)
	{
		if ($k == "deadline" && ($case = $this->get_case()))
		{
			$case->set_prop("due_date", $v);
			$case->save();
		}
		return parent::set_prop($k, $v);
	}

	function get_total_price()
	{
		return $this->get_total_price_for_amt($this->prop("amount"), true);
	}

	function get_total_price_for_amt($amt, $do_cov = false)
	{
		$pr = 0;
		$pr += $this->_get_mat_price($amt);
		$pr += $this->_get_res_price($amt);
		if ($do_cov)
		{
			$pr += $this->_get_cov_price($amt);
		}
		return $pr;
	}

	protected function _get_cov_price($amt)
	{
		$sum = 0;
		$sel_covers = $this->get_selected_covers();
		foreach($sel_covers as $cover)
		{
			$sum += $cover->get_price_for_order_and_amt(obj($this->id()), $amt);
		}
		return $sum;
	}

	protected function _get_mat_price($amt)
	{	
		foreach($this->get_job_list() as $job)
		{
			$material_expenses = $job->get_material_expense_list();

			foreach($material_expenses as $material_id => $row)
			{
				$mo = obj($material_id);

				$mp = $this->_get_mat_price_for_amt($this, $amt, $mo, $row);
				foreach($mp as $mp_k => $mp_v)
				{
					$sums[$mp_k] += $mp_v;
				}
			}
		}
		return join(" ", $sums);
	}

	private function _get_mat_price_for_amt($o, $amt, $mo, $expense_row)
	{
		static $cur_list;
		if ($cur_list === null)
		{
			$cur_list = get_instance(CL_CURRENCY)->get_list(RET_NAME);
		}

		$tot_mat_price = array(); 

		// calculate amount for the amount requested approximately
		$per_one = $expense_row->prop("amount") / $o->prop("amount");
		$calc_amt = $per_one * $amt;

		foreach($cur_list as $cur_id => $cur_name)
		{
			$tot_mat_price[$cur_id] += ($mo->price_get_by_currency(obj($cur_id)) * $calc_amt);
		}
		return $tot_mat_price;
	}

	protected function _get_res_price($amt)
	{
		$pricelist = obj($this->prop("mrp_pricelist"));

		$pr = 0;
		foreach($this->get_job_list() as $job)
		{
			$resource = $job->get_resource();

			$pr += $pricelist->get_price_for_resource_and_amount($resource, $amt);
		}
		return $pr;
	}

	function get_selected_covers()
	{
		if ($this->sel_cover_list === null)
		{
			$ol = new object_list($this->connections_from(array("type" => "RELTYPE_SEL_COVER")));
			$this->sel_cover_list = $ol->arr();
		}
		return $this->sel_cover_list;
	}

	function set_selected_covers($cover_list)
	{
		$cover_list = array_flip($cover_list);
		$cur = $this->get_selected_covers();
		// remove unnecessary
		foreach($cur as $cover)
		{
			if (!isset($cover_list[$cover->id()]))
			{
				$this->disconnect(array("from" => $cover->id(), "type" => "RELTYPE_SEL_COVER"));
			}
		}

		// add new
		foreach($cover_list as $id => $tmp)
		{
			if (!isset($cur[$id]))
			{
				$this->connect(array("to" => $id, "type" => "RELTYPE_SEL_COVER"));
			}
		}

		$this->sel_cover_list = null;
	}
}

?>
