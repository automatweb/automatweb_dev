<?php
//$vars = array_merge($HTTP_POST_VARS,$HTTP_GET_VARS,$AW_GET_VARS,$_GET,$_POST);
// _GET, _POST and friends were implemented in php 4.1.0
// right now, heaven is on 4.0.6, so I have to implement an workaround 
if (!is_array($_GET) || (sizeof($_GET) == 0))
{
	$_GET = $HTTP_GET_VARS;
};

if (!is_array($_POST))
{
	$_POST = $HTTP_POST_VARS;
};

//$vars = array_merge($_GET,$_POST,$AW_GET_VARS);
if (!is_array($AW_GET_VARS))
{
	$AW_GET_VARS = array();
};
$vars = $_GET + $_POST + $AW_GET_VARS;

$class = $vars["class"];

// I'll burn in hell for this
if (!$class)
{
	$class = $vars["alias"];
};

$action = $vars["action"];

if ($vars["fastcall"] == 1)
{
	session_name("automatweb");
	session_start();
	// loadime klassi
	classload("core/fastcall_base");
	if (!class_exists("class_base"))
	{
		classload("class_base");
	};
	// instantseerime
	classload($class);
	$inst = new $class;
	// ja ongi k6ik
	die($inst->$action($vars));
}

include("site_header.".aw_ini_get("ext"));

classload("orb");
$orb = new orb();
$orb->process_request(array(
	"class" => $class,
	"action" => $action,
	"reforb" => $vars["reforb"],
	"user"	=> 1,
	"vars" => $vars,
	"silent" => false,
));
$content = $orb->get_data();

// et kui orb_data on link, siis teeme ümbersuunamise
// see ei ole muidugi parem lahendus. In fact, see pole üleüldse
// mingi lahendus
if (substr($content,0,5) == "http:" || $vars["reforb"] == 1)
{
	header("Location: $content");
	print "\n\n";
	exit;
};
?>
