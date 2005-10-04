<?php
// $Header: /home/cvs/automatweb_dev/automatweb/orb.aw,v 2.21 2005/10/04 10:48:59 kristo Exp $
if (!ini_get("safe_mode"))
{
	set_time_limit(0);
}
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

$vars = array();
if (is_array($_GET))
{
	$vars = $vars + $_GET;
};
if (is_array($_POST))
{
	$vars = $vars + $_POST;
};
if (isset($_AW_GET_VARS) && is_array($_AW_GET_VARS))
{
	$vars = $vars + $_AW_GET_VARS;
};

$class = $vars["class"];
$action = $vars["action"];

if (empty($class) && isset($vars["alias"]))
{
	$class = $vars["alias"];
};

if (array_key_exists("fastcall", $vars) && $vars["fastcall"] == 1)
{
	// loadime klassi
	classload("fastcall_base");
	classload($class);
	// instants
	$inst = new $class;
	die($inst->$action($vars));
}
include("admin_header.".aw_ini_get("ext"));


classload("defs","core/orb/orb");
$t = new aw_template;
$t->init("");
if (!$t->prog_acl_auth("view", PRG_MENUEDIT))
{
	$t->auth_error();
}

// actually, here we should find the program that get's executed somehow and do prog_acl for that. 
// but there seems to be no sure way to do that unfortunately. 

$orb = new orb();
$orb->process_request(array(
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
	if (headers_sent())
	{
		print html::href(array(
			"url" => $content,
			"caption" => t("Kliki siia jätkamiseks"),
		));		
	}
	else
	{
		header("Location: $content");
		print "\n\n";
	};
	exit;
};

$info = $orb->get_info();

include("admin_footer.".aw_ini_get("ext"));
?>
