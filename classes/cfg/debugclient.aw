<?php
class debugclient extends aw_template
{
	var $formdata;
	function debugclient($arr = array())
	{
		$this->init("");

		$this->config = array();
		$this->proplist = array();
		$this->tabs = array();
		//print "debug client init done!";
	}

	function configure($arr = array())
	{
		/*
		print "configuring:\n";
		print_r($arr);
		*/
		$this->config = $arr;

	}

	function add_property($arr = array())
	{
		//print "adding property:\n";
		if ($arr["vcl_inst"])
		{
			unset($arr["vcl_inst"]);
		}		
		$this->proplist[$arr["name"]] = $arr;
		//print_r($arr);
	}

	function finish_output($arr = array())
	{
		$this->formdata = array(
			"class" => $arr["data"]["orb_class"],
			"action" => $arr["action"],
			"group" => $arr["data"]["group"],
			"id" => $arr["data"]["id"],
			"method" => $arr["method"],
			"data" => $arr["data"],
		);
		$this->focus_el = $arr["focus"];
		print "Finishing output:\n";
		print_r($arr);
		/*
		print_r($this->proplist);
		*/

	}

	function add_tab($arr = array())
	{
		//print "adding tab:\n";
		//print_r($arr);
		$this->tabs[$arr["id"]] = $arr;
	}

	function get_result($arr = array())
	{
		//print "getting result:\n";
		//print_r($arr);
	}
};
?>
