<?php

class crm_bill_obj extends _int_object
{
	function set_prop($name,$value)
	{
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

	function get_bill_currency_id()
	{
		if($this->prop("customer.currency"))
		{
			return $this->prop("customer.currency");
		}
		$co_stat_inst = get_instance("applications/crm/crm_company_stats_impl");
		$company_curr = $co_stat_inst->get_company_currency();
		return $company_curr;
	}

	function get_bill_currency_name()
	{
		if($this->prop("customer.currency"))
		{
			$company_curr = $b->prop("customer.currency");
		}
		else
		{
			$co_stat_inst = get_instance("applications/crm/crm_company_stats_impl");
			$company_curr = $co_stat_inst->get_company_currency();
		}
		if(is_oid($company_curr) && $this->can("view" , $company_curr))
		{
			$cu_o = obj($company_curr);
			return $cu_o->name();
		}
		return "EEK";
	}

}

?>
