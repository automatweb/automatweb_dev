<?php

classload('graph/graph_base');
class graph_line extends graph_base
{
	function graph_line()
	{
		$this->init();
	}

	function get_module_name()
	{
		return "Jooned";
	}

	function prop_gen()
	{
		$ret = array(
			'num_y_values' => $this->mk_prop(array(
				'type' => 'textbox',
				'size' => '4',
				'caption' => 'Mitu rida Y teljele',
			))
		);
		#dbg::dump($ret);
		return $ret;
		#return array();
	}
}
?>
