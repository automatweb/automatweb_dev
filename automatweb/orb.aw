<?php
// $Header: /home/cvs/automatweb_dev/automatweb/orb.aw,v 2.7 2002/06/10 15:50:45 kristo Exp $
include("const.aw");

$vars = array_merge($HTTP_POST_VARS,$HTTP_GET_VARS,$AW_GET_VARS);

if ($fastcall == 1)
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
$t->db_init();
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
if ((substr($content,0,5) == "http:" || $reforb == 1) && !$no_redir)
{
	header("Location: $content");
	print "\n\n";
	exit;
};

$info = $orb->get_info();

include("admin_footer.".aw_ini_get("ext"));
?>
