<?php
// fetches a CSS file
// arguments:
// name(string) - faili nimi
include("const.aw");
session_name("automatweb");
session_start();
$uid = $HTTP_SESSION_VARS["uid"];
if (strpos("/",$name) === false)
{
	//$css = join("",file(AW_PATH . "/files/$name"));
	$path = sprintf("%s/files/css/%s.css",AW_PATH,$uid);
	$css = join("",@file($path));
	print $css;
};
?>
