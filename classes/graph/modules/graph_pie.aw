<?php

classload('graph/graph_base');
class graph_pie extends graph_base
{
	function graph_pie()
	{
		$this->init();
	}

	function get_module_name()
	{
		return "Tort";
	}
}
?>
