<?php
// noja tekalt voib selle ju taiesti vabalt adminni index.aw sisse integreerida
// voi mis?
include("const.aw");
include("admin_header.$ext");
classload("defs","orb");
$orb = new orb(array(
	"class" => $class,
	"action" => $action,
	"vars" => ($reforb == 1) ? $HTTP_POST_VARS : $HTTP_GET_VARS,
	"silent" => false,
));
$orb_data = $orb->get_data();
// et kui orb_data on link, siis teeme ümbersuunamise
// see ei ole muidugi parem lahendus. In fact, see pole üleüldse
// mingi lahendus
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
include("admin_footer.$ext");
?>
