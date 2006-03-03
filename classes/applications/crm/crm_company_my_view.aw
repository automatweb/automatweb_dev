<?php

class crm_company_my_view extends class_base
{
	function crm_company_my_view()
	{
		$this->init("crm");
	}

	function _get_my_view($arr)
	{
		$this->read_template("my_view.tpl");

		/*
			Teha Avalehe vaade, kus on näha tänased ja homsed sündmused, 
			mulle lisatud failid, foorumi viimased teemad, 
		*/

		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		return $this->parse();
	}
}

?>