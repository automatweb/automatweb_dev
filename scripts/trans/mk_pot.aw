<?php
// $Header: /home/cvs/automatweb_dev/scripts/trans/mk_pot.aw,v 1.3 2005/03/31 10:09:42 kristo Exp $
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
if (in_array("--make-aw", $argv))
{
	$i->make_aw();
}
else
{
	$i->full_scan();
}
?>
