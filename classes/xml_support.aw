<?php
// $Version$
// xml_support.aw - XML support functions

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
?>
