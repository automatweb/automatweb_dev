<?php
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
$scanner = get_instance("core/docgen/docgen_analyzer");
$scanner->make_orb_defs_from_doc_comments();
?>
