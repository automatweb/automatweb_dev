<?php
// $Header: /home/cvs/automatweb_dev/classes/core/serializers/php_file.aw,v 1.1 2008/02/21 19:14:45 kristo Exp $
// php.aw - PHP serializer
/*
@classinfo  maintainer=kristo
*/
class php_serializer_file
{
	var $no_index = false;

	function php_serialize($arr)
	{
		if (!is_array($arr))
		{
			return false;
		}

		$arrname = (isset($this->arr_name) && $this->arr_name != "" ? $this->arr_name : "arr");

		$dat = "\$".$arrname." = ".$this->req_serialize($arr).";";
		return $dat;
	}

	function set($key,$val)
	{
		$this->$key = $val;
	}

	function req_serialize($arr)
	{
		$str ="array(";
		$td = array();
		foreach($arr as $k => $v)
		{
			if (is_array($v))
			{
				$v = $this->req_serialize($v);
			}
			else
			{
				$v = "'".str_replace("'","\'", str_replace("\\","\\\\", $v))."'";
			}

			$td[] = "'$k'"."=>".$v;
		}
		return $str.join(",\n",$td).")\n";
	}

	function php_unserialize($str)
	{
		@eval($str);
		if (!is_array($arr))
		{
			@eval(stripslashes($str));
		}
		return $arr;
	}
}
?>