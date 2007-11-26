<?php
// $Header: /home/cvs/automatweb_dev/scripts/trans/mk_pot.aw,v 1.5 2007/11/26 18:56:47 kristo Exp $
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");

if (in_array("--dbg", $argv))
{
	$GLOBALS["mk_dbg"] = 1;
}

$i = get_instance("core/trans/pot_scanner");
if (in_array("--list-untranslated-strings", $argv))
{
	$i->list_untrans_strings();
}
else
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
