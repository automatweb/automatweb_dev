<?php
// $Header: /home/cvs/automatweb_dev/automatweb/index.aw,v 2.14 2003/10/22 08:55:36 duke Exp $
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
