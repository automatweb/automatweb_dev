<?php
// $Header: /home/cvs/automatweb_dev/automatweb/index.aw,v 2.16 2005/03/20 15:38:23 kristo Exp $

if (isset($_GET["class"]))
{
	header("Location: /automatweb/orb.aw".str_replace("/automatweb/","",$_SERVER["REQUEST_URI"]));
	die();
}

include("const.aw");
include("admin_header.".aw_ini_get("ext"));
classload("core/orb/orb");
$orb = new orb();
$orb->process_request(array(
	"class" => "workbench",
	"vars" => array("action" => "gen_workbench"),
	"silent" => false,
));
?>
