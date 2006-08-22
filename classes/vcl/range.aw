<?php
// range VCL component

class range extends class_base
{
	function range()
	{
//		$this->init("vcl/range");
	}

	function init_vcl_property($arr)
	{
		$saved_value = $arr['obj_inst']->prop($arr['prop']['name']);
		if (!empty($saved_value))
		{
			list($from, $to) = explode('-', $saved_value);
		}
		
		$prop = $arr['prop'];
		$params = array(
			'from' => $from,
			'to' => $to,
			'prop' => $prop
		);
		$prop['value'] = $this->get_html($params);

		return array($prop['name'] => $prop);
	}

	function process_vcl_property($arr)
	{
		$arr['prop']['value'] = $arr['prop']['value']['from'].'-'.$arr['prop']['value']['to'];
	}

	function get_html($arr)
	{
		$str = html::textbox(array(
			'name' => $arr['prop']['name'].'[from]',
			'value' => $arr['from'],
			'size' => 5
			
		));
		$str .= ' - ';
		$str .= html::textbox(array(
			'name' => $arr['prop']['name'].'[to]',
			'value' => $arr['to'],
			'size' => 5
			
		));
		return $str;
	}

}
?>
