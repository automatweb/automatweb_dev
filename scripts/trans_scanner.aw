<?php
// $Header: /home/cvs/automatweb_dev/scripts/trans_scanner.aw,v 1.1 2003/08/29 11:51:35 duke Exp $
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
$scanner = get_instance("translate/scanner");
// run, scanner, run!
$scanner->run();
?>
