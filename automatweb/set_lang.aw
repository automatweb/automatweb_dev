<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template","languages");
$id = (int)$id;
if ($uid)
{
	$t = new languages;
	$t->set_active($id);
	setcookie("lang_id",$id,time()+24*3600*1000,"/");
}
else
{
	setcookie("lang_id",$id,time()+24*3600*1000,"/");
}
$lang_id = $id;
session_register("lang_id");
$docid = 0;
$lang_id = $id;
header("Location: menuedit.html");
?>
