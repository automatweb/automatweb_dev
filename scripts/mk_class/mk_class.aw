<?php


function file_get_contents($name)
{
	$f = @fopen($name, "r");
	if (!$f)
	{
		echo "\nERROR: file $name not found!\n\n";
		exit(1);
	}
	$fc = fread($f, filesize($name));
	fclose($f);
	return $fc;
}

function file_put_contents($name, $fc)
{
	$f = fopen($name, "w");
	$fc = fwrite($f, $fc);
	fclose($f);
}

if ($_SERVER["argc"] < 2)
{
	echo("usage: mk_class new_class_name new_class_def [folder]\n");
	echo("  REQUIRED:\n");
	echo("	  new_class_name: the name of the class and the file of the class\n");
	echo("	  new_class_def: the class def in ini file (CL_BLAH)\n");
	echo("  OPTIONAL:\n");
	echo("    folder: the folder where the class file will be created, under AWROOT/classes\n");
	exit(1);
}

chdir("../..");
$class = $_SERVER["argv"][1];
$class_def = $_SERVER["argv"][2];
$class_folder = $_SERVER["argv"][3];

$clnf = ($class_folder == "" ? $class.".aw" : $class_folder."/".$class.".aw");
$tpnf = ($class_folder == "" ? $class : $class_folder."/".$class);

if (file_exists("classes/$clnf"))
{
	echo "\nERROR: file classes/$clnf already exists!\n\n";
	exit(1);
}

if (file_exists("xml/orb/$class.xml"))
{
	echo "\nERROR: file xml/orb/$class.xml already exists!\n\n";
	exit(1);
}

if (file_exists("templates/$tpnf/change.tpl"))
{
	echo "\nERROR: file templates/$tpnf/change.tpl already exists!\n\n";
	exit(1);
}

if (file_exists("templates/$tpnf/show.tpl"))
{
	echo "\nERROR: file templates/$tpnf/show.tpl already exists!\n\n";
	exit(1);
}

echo "\nmaking class $clnf...\n";

$fc = str_replace("__classdef", $class_def, file_get_contents("install/class_template/classes/base.aw"));
$fc = str_replace("__tplfolder", $tpnf, $fc);
file_put_contents("classes/$clnf",str_replace("__classname", $class, $fc));
echo "\tcreated classes/$clnf...\n";

$folder = $class_folder != "" ? "folder=\"".$class_folder."\"" : "";
$fc = str_replace("__classname", $class, file_get_contents("install/class_template/xml/orb/base.xml"));
file_put_contents("xml/orb/$class.xml",str_replace("__classfolder", $folder, $fc));
echo "\tcreated xml/orb/$class.xml...\n";

$sp = explode("/", $tpnf);
$dir = "templates";
foreach($sp as $v)
{
	$dir.="/".$v;
	@mkdir($dir, 0775);
	echo "\tcreated directory $dir...\n";
}

$fc = str_replace("__classdef", $class_def, file_get_contents("install/class_template/templates/base/change.tpl"));
file_put_contents("templates/$tpnf/change.tpl",str_replace("__classname", $class, $fc));
echo "\tcreated templates/$tpnf/change.tpl...\n";

$fc = str_replace("__classdef", $class_def, file_get_contents("install/class_template/templates/base/show.tpl"));
file_put_contents("templates/$tpnf/show.tpl",str_replace("__classname", $class, $fc));
echo "\tcreated templates/$tpnf/show.tpl...\n";

echo "all done! \n\n";
?>