<?php
include("const.aw");
include("admin_header.$ext");

// check referer
/*global $HTTP_REFERER;
if (substr($HTTP_REFERER,0,strlen($baseurl)) != $baseurl)
{
	$sf->raise_error(sprintf(E_ORB_REFFERER,$HTTP_REFERER),true);
}*/

// broker request

if (!isset($class))
{
	$sf->raise_error(E_ORB_CLASS_UNDEF,true);
}

if (!isset($action))
{
	$sf->raise_error(E_ORB_ACTION_UNDEF,true);
}

if ($class == "")
{
	$sf->raise_error(sprintf(E_ORB_CLASS_NOT_FOUND,$class),true);
}

// loadime vastava klassi
classload($class);
if (!class_exists($class))
{
	$sf->raise_error(sprintf(E_ORB_CLASS_NOT_FOUND,$class),true);
}

if (!is_array($orb_defs[$class]))
{
	$sf->raise_error(sprintf(E_ORB_CLASS_UNDEF,$class),true);
}
	
// loome 6ige objekti
$t = new $class;

// leiame actionile vastava funktsiooni
$fun = $orb_defs[$class][$action];
if (!is_array($fun))
{
	$sf->raise_error(sprintf(E_ORB_CLASS_ACTION_UNDEF,$action,$class),true);
}

// ja kutsume funktsiooni v2lja, andex parameetrix k6ik formist saadud variaablid
$fname = $fun["function"];
if (!method_exists($t,$fname))
{
	$sf->raise_error(sprintf(E_ORB_METHOD_NOT_FOUND,$class,$action),true);
}

// reforbi funktsioon peab tagastama aadressi, kuhu edasi minna
$url = $t->$fname($HTTP_POST_VARS);

// ja suuname 6igesse kohta
header("Location: $url");
?>
