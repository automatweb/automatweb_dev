<?php
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
aw_global_set("no_db_connection", 1);
$scanner = get_instance("core/msg/msg_scanner");
$scanner->scan();
?>
