<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/php.aw,v 2.12 2002/09/26 16:09:40 kristo Exp $
// php.aw - PHP serializer
class php_serializer 
{
	function php_serialize($arr,$to_file = false)
	{
		if (!is_array($arr))
		{
			return false;
		}

		$arrname = ($this->arr_name != "" ? $this->arr_name : "arr");

		$dat = "\$".$arrname." = ".$this->req_serialize($arr,$to_file||$this->to_file).";";
		return $dat;
	}

	function set($key,$val)
	{
		$this->$key = $val;
	}

	function req_serialize($arr,$to_file)
	{
		$str ="array(";
		$td =array();
		foreach($arr as $k => $v)
		{
			if (is_array($v))
			{
				$v = $this->req_serialize($v,$to_file);
				if ($v == "array()\n")
				{
					continue;
				}
			}
			else
			{
				if (!$to_file)
				{
					$v = str_replace("\"","\\\\\"",$v);
				}
				else
				{
					$v = str_replace("\"","\\\"",$v);
				}
				$v = str_replace("\n","\\\n",$v);
				$v = str_replace("\r","\\\r",$v);
				$v = str_replace("\$","\\\\\$",$v);
				if ($v)
				{
					$v = "\"$v\"";
				}
			}
			if (!$this->for_include)
			{
				$k = str_replace("\"","\\\"",$k);
			}
			if ($this->no_index)
			{
				if ($v)
				{
					$td[] = $v."\n";
				}
			}
			else
			{
				if ($v)
				{
					$td[] = "'$k'"."=>".$v;
				}
			};
		}
		return $str.join(",\n",$td).")\n";
	}

	function php_unserialize($str)
	{
		eval($str);
		return $arr;
	}
}

?>
