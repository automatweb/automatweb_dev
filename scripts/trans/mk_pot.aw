<?php
// $Header: /home/cvs/automatweb_dev/scripts/trans/mk_pot.aw,v 1.2 2005/03/21 12:50:39 kristo Exp $
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");

$i = get_instance("core/trans/pot_scanner");
if (in_array("--warn-only", $argv))
{
	$i->warning_scan();
}
else
{
	$i->full_scan();
}
?>
