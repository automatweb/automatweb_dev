<?php
include("const.aw");
include("admin_header.$ext");
$site_title = "Autom@tWeb";
$count = $users->count();
$tmp = new aw_template();
$tmp->tpl_init("automatweb");
$tmp->read_template("content.tpl");
$tmp->vars(array("online" => $count[online],
                "total"  => $count[total]));
$content = $tmp->parse();
include("admin_footer.$ext");
?>

