<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template","timer");
$awt = new aw_timer;
$sf = new aw_template;
$sf->tpl_init("automatweb");
$sf->read_template("colorpicker.tpl");
echo $sf->parse();
?>
