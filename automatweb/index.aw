<?php
// $Header: /home/cvs/automatweb_dev/automatweb/index.aw,v 2.15 2004/11/26 14:05:25 kristo Exp $

if (isset($_GET["class"]))
{
	header("Location: /automatweb/orb.aw".str_replace("/automatweb/","",$_SERVER["REQUEST_URI"]));
	die();
}

include("const.aw");
include("admin_header.".aw_ini_get("ext"));
classload("orb");
$orb = new orb();
$orb->process_request(array(
	"class" => "workbench",
	"vars" => array("action" => "gen_workbench"),
	"silent" => false,
));
?>
