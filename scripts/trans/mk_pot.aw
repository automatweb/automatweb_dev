<?php
// $Header: /home/cvs/automatweb_dev/scripts/trans/mk_pot.aw,v 1.1 2005/03/21 11:07:09 kristo Exp $
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");

$i = get_instance("core/trans/pot_scanner");
$i->full_scan();
?>
