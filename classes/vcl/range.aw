<?php
// range VCL component

class range extends class_base
{
	var $name;
	var $from;
	var $to;

	function range()
	{
//		$this->init("vcl/range");
	}

	function init_vcl_property($arr)
	{
		$saved_value = $arr['obj_inst']->prop($arr['prop']['name']);
		if (!empty($saved_value))
		{
			list($this->from, $this->to) = explode('-', $saved_value);
		}

		$this->name = $arr["property"]["name"];
		$vcl_inst = $this;
		$res = $arr["property"];
		$res["vcl_inst"] = &$vcl_inst;
		
		return array($this->name => $res);
	}

	function process_vcl_property($arr)
	{
		$arr['prop']['value'] = $arr['prop']['value']['from'].'-'.$arr['prop']['value']['to'];
	}

	function get_html($arr)
	{

		$str = html::textbox(array(
			'name' => $this->name.'[from]',
			'value' => $this->from,
			'size' => 5
			
		));
		$str .= ' - ';
		$str .= html::textbox(array(
			'name' => $this->name.'[to]',
			'value' => $this->to,
			'size' => 5
			
		));
		return $str;
	}

	function set_from($from)
	{
		$this->from = $from;
	}

	function set_to($to)
	{
		$this->to = $to;
	}

	function set_range($range)
	{
		if (is_string($range))
		{
			list($this->from, $this->to) = explode('-', $range);
			return true;
		}

		if (is_array($range))
		{
			$this->from = $range['from'];
			$this->to = $range['to'];
			return true;
		}

		return false;
	}

}
?>
