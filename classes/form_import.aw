<?php

global $orb_defs;
$orb_defs["form_import"] = "xml";

class form_import extends form_base
{
	function form_import()
	{
		$this->form_base();
		$this->sub_merge = 1;
	}

	////
	// !shows form entries import form
	function import_form_entries($arr)
	{
		extract($arr);
	}

	function import_chain_entries($arr)
	{
		extract($arr);
	}
}
?>