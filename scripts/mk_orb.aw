<?php
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
aw_global_set("no_db_connection", true);
$scanner = get_instance("core/orb/orb_gen");
$scanner->make_orb_defs_from_doc_comments();
?>
