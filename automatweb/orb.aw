<?php
// $Header: /home/cvs/automatweb_dev/automatweb/orb.aw,v 2.10 2003/03/18 12:15:24 duke Exp $
include("const.aw");

//$vars = array_merge($HTTP_POST_VARS,$HTTP_GET_VARS,$AW_GET_VARS,$_GET,$_POST);
// _GET, _POST and friends were implemented in php 4.1.0
// right now, heaven is on 4.0.6, so I have to implement an workaround
if (!is_array($_GET))
{
        $_GET = $HTTP_GET_VARS;
};

if (!is_array($_POST))
{
        $_POST = $HTTP_POST_VARS;
};

$vars = array_merge($_GET,$_POST,$AW_GET_VARS);

$class = $vars["class"];
$action = $vars["action"];

if ($vars["fastcall"] == 1)
{
	// loadime klassi
	classload("fastcall_base");
	classload($class);
	// instants
	$inst = new $class;
	die($inst->$action($vars));
}
include("admin_header.".aw_ini_get("ext"));

classload("defs","orb");
$t = new aw_template;
$t->init("");
if (!$t->prog_acl_auth("view", PRG_MENUEDIT))
{
	$t->auth_error();
}

// actually, here we should find the program that get's executed somehow and do prog_acl for that. 
// but there seems to be no sure way to do that unfortunately. 

$orb = new orb(array(
	"class" => $class,
	"action" => $action,
	"vars" => $vars,
	"silent" => false,
));
$content = $orb->get_data();
// et kui orb_data on link, siis teeme ümbersuunamise
// see ei ole muidugi parem lahendus. In fact, see pole üleüldse
// mingi lahendus
if ((substr($content,0,5) == "http:" || (isset($vars["reforb"]) && ($vars["reforb"] == 1))) && !$vars["no_redir"])
{
	header("Location: $content");
	print "\n\n";
	exit;
};

$info = $orb->get_info();

include("admin_footer.".aw_ini_get("ext"));
?>
