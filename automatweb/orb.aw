<?php

// $Header: /home/cvs/automatweb_dev/automatweb/orb.aw,v 2.5 2001/12/18 00:18:48 kristo Exp $
// noja tekalt voib selle ju taiesti vabalt adminni index.aw sisse integreerida
// voi mis?
include("const.aw");
include("admin_header.$ext");
classload("defs","orb");
$t = new aw_template;
$t->db_init();

if (!$t->prog_acl("view", PRG_MENUEDIT))
{
	include("sorry.aw");
	exit;
}

$merged = array_merge($HTTP_GET_VARS,$HTTP_POST_VARS);
$orb = new orb(array(
	"class" => $class,
	"action" => $action,
	"vars" => $merged,
	"silent" => false,
));
$orb_data = $orb->get_data();
// et kui orb_data on link, siis teeme ümbersuunamise
// see ei ole muidugi parem lahendus. In fact, see pole üleüldse
// mingi lahendus
if ($no_redir)
{
	$reforb = 0;
};
if (substr($orb_data,0,5) == "http:")
{
	$reforb = 1;
};
if ($reforb)
{
	header("Location: $orb_data");
	print "\n\n";
	exit;
};
$content = $orb_data;
$info = $orb->get_info();
include("admin_footer.$ext");
?>
