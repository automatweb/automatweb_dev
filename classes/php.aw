<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/php.aw,v 2.4 2002/01/16 23:02:52 duke Exp $
// php.aw - PHP serializer
class php_serializer 
{
	function set($key,$val)
	{
		$this->$key = $val;
	}

	function php_serialize($arr)
	{
		if (!is_array($arr))
		{
			//echo "php::php_serialize($arr): can only serialize arrays!";
			return false;
		}

		return "\$arr = ".$this->req_serialize($arr).";";
	}

	function req_serialize($arr)
	{
		$str ="array(\n";
		$td =array();
		foreach($arr as $k => $v)
		{
			if (is_array($v))
			{
				$v = $this->req_serialize($v);
			}
			else
			{
				$v = str_replace("\"","\\\\\"",$v);
				$v = str_replace("\n","\\\n",$v);
				$v = str_replace("\r","\\\r",$v);
				$v = str_replace("\$","\\\\\$",$v);
				$v = "\"$v\"";
			}
			$k = str_replace("\"","\\\"",$k);
			if ($this->no_index)
			{
				$td[] = $v."\n";
			}
			else
			{
				$td[] = "\"$k\""."=>".$v."\n";
			};
		}
		return $str.join(",",$td).")\n";
	}

	function php_unserialize($str)
	{
		global $awt;
		$awt->start("php::php_unserialize");
//		echo "str = <pre>$str</pre> <br>";

		eval($str);
		$awt->stop("php::php_unserialize");
		return $arr;
	}
}

?>
