<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/php.aw,v 2.7 2002/03/07 19:13:32 duke Exp $
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

		return "\$arr = ".$this->req_serialize($arr,$to_file).";";
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
			$k = str_replace("\"","\\\"",$k);
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
		global $awt;
		if (is_object($awt))
		{
			$awt->start("php::php_unserialize");
			$awt->count("php::unser");
		};
		eval($str);
		if (is_object($awt))
		{
			$awt->stop("php::php_unserialize");
		};
		return $arr;
	}
}

?>
