<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/php.aw,v 2.8 2002/06/10 15:50:54 kristo Exp $
// php.aw - PHP serializer
class php_serializer 
{
	function php_serialize($arr,$to_file = false)
	{
		if (!is_array($arr))
		{
			//echo "php::php_serialize($arr): can only serialize arrays!";
			return false;
		}

		$arrname = ($this->arr_name != "" ? $this->arr_name : "arr");

		return "\$".$arrname." = ".$this->req_serialize($arr,$to_file).";";
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
				// $v = "\"$v\"";
				$v = "\"$v\"";
			}
			if (!$this->for_include)
			{
				$k = str_replace("\"","\\\"",$k);
			}
			if ($this->no_index)
			{
				$td[] = $v."\n";
			}
			else
			{
				//$td[] = "\"$k\""."=>".$v."\n";
				$td[] = "'$k'"."=>".$v;
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
