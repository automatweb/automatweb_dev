<?php
// $Version$
// xml_support.aw - XML support functions
// the goal is to gather all xml related functions which do not belong
// to any class in this file.

// formeerib xml andmestruktuuri identifikaator
classload("defs");
function xml_gen_header($version = "1.0") 
{
	return "<" . "?xml version='$version'?" . ">\r\n";
}


// formeerib xml tagi nimega $name ja parameetritega arrayst data
// name(string) - tagi nimi
// attribs(array) - atribuudid ja nende väärtused
function xml_open_tag($name, $level = 0, $attribs = array()) 
{
	$attr_str = ( sizeof($attribs) > 0 ) ? join(" ",map2(" %s='%s'",$attribs)) : "";
	$prefix = str_repeat("        ",$level);
	return sprintf("%s<%s%s>\r\n",$prefix,$name,$attr_str);
}

function xml_close_tag($name, $level = 0) 
{
	$prefix = str_repeat("        ",$level);
	return sprintf("%s</%s>\r\n",$prefix,$name);
}

function xml_complete_tag($name, $data, $level = 0) 
{
	$prefix = str_repeat("        ",$level);
	return sprintf("%s<%s>%s</%s>\r\n",$prefix,$name,$data,$name);
}

function rpc_create_struct($data)
{
	$res = "";
	static $level = 0;
	if (is_array($data))
	{
		$level++;
		$res .= str_repeat("\t",$level) . "<struct>\n";
		foreach($data as $key => $val)
		{
			$level++;
			$res .= str_repeat("\t",$level) . "<member>\n";
			$res .= str_repeat("\t",$level + 1);
			$res .= "<name>$key</name>\n";
			$int = (string)(int)$val;
			$res .= str_repeat("\t",$level + 1);
			$res .= "<value>";
			if (is_array($val))
			{
				$res .= "\n";
				$level++;
				$res .= rpc_create_struct($val);
				$level--;
				$res .= str_repeat("\t",$level + 1);
			}
			elseif ($int === $val)
			{
				$res .= "<i4>$val</i4>";
			}
			else
			{
				$res .= $val;
			};
			$res .= "</value>\n";
			$res .= str_repeat("\t",$level) . "</member>\n";
			$level--;
		};
		$res .= str_repeat("\t",$level) . "</struct>\n";
		$level--;
	};
	return $res;
};

// now we have to parse it out again somehow
function rpc_extract_struct($data)
{
	$parser = xml_parser_create();
	xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
	xml_parse_into_struct($parser,$data,&$values,&$tags); 
	xml_parser_free($parser); 
	return $values;
}

function _rpc_extract_struct(&$arr)
{
	$result = array();
	$name = "";
	static $in_value;
	static $i = 0;

	$continue = $i < sizeof($arr);

	while ($continue)
	{
		$token = $arr[$i];

		if ($in_value && ($token["type"] == "complete") )
		{
			$result[$name] = $token["value"];
			$in_value = false;
		};

		if ($in_value && ($token["tag"] == "struct") )
		{
			$in_value = false;
			$result[$name] = _rpc_extract_struct(&$arr);
		}

		if ($token["tag"] == "member")
		{
			if ($token["type"] == "open")
			{
				//print "w00p!";

			};
		};

		if ( ($token["tag"] == "name") && ($token["type"] == "complete") )
		{
			$name = $token["value"];
		};

		if ( ($token["tag"] == "value") && ($token["type"] == "complete") )
		{
			$result[$name] = $token["value"];
		};

		if ( ($token["tag"] == "value") && ($token["type"] == "open") )
		{
			$in_value = true;
		};

		$i++;
		$continue = $i < sizeof($arr);
	};
	
	return $result;
};
?>
