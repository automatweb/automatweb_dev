<?php
// $Header: /home/cvs/automatweb_dev/automatweb/index.aw,v 2.17 2006/09/06 12:52:13 kristo Exp $

if (isset($_GET["class"]))
{
	header("Location: /automatweb/orb.aw".str_replace("/automatweb/","",$_SERVER["REQUEST_URI"]));
	die();
}

include("const.aw");
include("admin_header.".aw_ini_get("ext"));
/*classload("core/orb/orb");
$orb = new orb();
$orb->process_request(array(
	"class" => "workbench",
	"vars" => array("action" => "gen_workbench"),
	"silent" => false,
));*/

$if = get_instance(CL_ADMIN_IF);
header("Location: ".$if->redir(array()));
?>
