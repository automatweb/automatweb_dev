<?php

// graph module base class
class graph_base extends class_base
{
	function init()
	{
		// whateva init
		parent::init(array(
			'tpldir' => 'graph',
			'clid' => CL_GRAPH_NG
		));
	}

	function get_module_name()
	{
		die(t("ERROR: graph_base::get_module_name not overriden!"));
	}

	function prop_gen($arr)
	{
		return array();
	}

	////
	// !default is one textbox for x values entry
	function generator_x_values()
	{
		return array('data_x' => array(
			'name' => 'data_x',
			'caption' => t('X-telje v&auml;&auml;rtused'),
			'type' => 'textbox',
			'table' => 'objects',
			'field' => 'meta',
			'method' => 'serialize',
			'group' => 'data'
		));
	}

	function mk_prop($arr)
	{
		return array(
			'group' => 'specific',
			'table' => 'objects',
			'method' => 'serialize',
			'field' => 'meta',
		) + $arr;
	}
}
?>
