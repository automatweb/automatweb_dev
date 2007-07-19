<?php
// what this script does, is take the starting file from the command line, reads that file
// and replaces all include commands in that file with the contents of the file that is included
$basedir = realpath(".");
include("$basedir/init.aw");
$stderr = fopen('php://stderr', 'w');

if ($_SERVER["argc"] < 1 || !file_exists($_SERVER["argv"][1]))
{
	echo "usage: php -q mk_ini.aw aw.ini.root \n\n";
	echo "\toutputs the ini file with the include directives replaced with the file contents\n\n";
	exit(1);
}

$basedir = dirname($_SERVER["argv"][1]);
aw_ini_set("basedir", $basedir);

$res = parse_config($_SERVER["argv"][1]);

if ($res === false)
{
	exit(1);
}
else
{
	echo "######################################################################\n";
	echo "# THIS IS AN AUTOMATICALLY GENERATED FILE!!!                         #\n";
	echo "# DO NOT EDIT THIS!!                                                 #\n";
	echo "#                                                                    #\n";
	echo "# Instead, edit aw.ini.root and/or the files included from it.       #\n";
	echo "# after editing, to regenerate this file execute cd \$AWROOT;make ini #\n";
	echo "######################################################################\n\n\n";
	echo join("\n", $res);
}
?>
