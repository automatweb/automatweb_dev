<?php
ob_implicit_flush(true);

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
	$f = @fopen($name, "w");
	if (!$f)
	{
		echo "\nERROR: could not create file $name!\n\n";
		exit(1);
	}

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

echo "\nmaking class $clnf...\n\n";

if ($class_folder != "")
{
	// check if the directory exists
	if (!is_dir("classes/$class_folder"))
	{
		mkdir("classes/$class_folder",0775);
		echo "created classes/$class_folder...\n";
	}
}
$fc = str_replace("__classdef", $class_def, file_get_contents("install/class_template/classes/base.aw"));
$fc = str_replace("__tplfolder", $tpnf, $fc);
file_put_contents("classes/$clnf",str_replace("__classname", $class, $fc));
echo "created classes/$clnf...\n";

$folder = $class_folder != "" ? "folder=\"".$class_folder."\"" : "";
$fc = str_replace("__classname", $class, file_get_contents("install/class_template/xml/orb/base.xml"));
file_put_contents("xml/orb/$class.xml",str_replace("__classfolder", $folder, $fc));
echo "created xml/orb/$class.xml...\n";

echo "making properties...\n\n";
passthru('make properties');

echo "\n\nall done! \n\n";
?>