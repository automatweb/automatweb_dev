<?php
// $Header: /home/cvs/automatweb_dev/automatweb/index.aw,v 2.19 2008/04/17 12:20:09 kristo Exp $
if (isset($_GET["class"]))
{
	header("Location: /automatweb/orb.aw".str_replace("/automatweb/","",$_SERVER["REQUEST_URI"]));
	die();
}
include("const.aw");
include("admin_header.".aw_ini_get("ext"));
$if = get_instance(CL_ADMIN_IF);
header("Location: ".$if->redir(array()));
?>
