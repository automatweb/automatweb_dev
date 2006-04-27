<?php

define("PROCUREMENT_NEW", 0);
define("PROCUREMENT_PUBLIC", 1);
define("PROCUREMENT_INPROGRESS", 2);
define("PROCUREMENT_DONE", 3);
define("PROCUREMENT_CLOSED", 4);

class procurements_model extends class_base
{
	function procurements_model()
	{
		$this->init();
	}

	/**
		@attrib api=1
	**/
	function get_my_procurements()
	{
		$co = get_current_company();
		return new object_list(array(
			"class_id" => array(CL_PROCUREMENT),
			"lang_id" => array(),
			"site_id" => array(),
			"offerers" => $co,
			"state" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, PROCUREMENT_PUBLIC)
		));
	}

	/**
		@attrib api=1
	**/
	function get_procurements_for_co($co)
	{
		return new object_list(array(
			"class_id" => array(CL_PROCUREMENT),
			"lang_id" => array(),
			"site_id" => array(),
			"offerers" => $co
		));
	}

	/**
		@attrib api=1
	**/
	function get_requirements_from_procurement($p)
	{
		$ot = new object_tree(array(
			"class_id" => CL_MENU,
			"parent" => $p->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		return new object_list(array(
			"class_id" => CL_PROCUREMENT_REQUIREMENT,
			"parent" => $ot->ids(),
			"lang_id" => array(),
			"site_id" => array()
		));
	}

	/**
		@attrib api=1
	**/
	function get_all_offers_for_procurement($p)
	{
		$ol = new object_list(array(
			"class_id" => CL_PROCUREMENT_OFFER,
			"procurement" => $p->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		return $ol;
	}
}
?>