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

function make_keys($arr)
{
	$ret = array();
	if (is_array($arr))
	{
		foreach($arr as $v)
		{
			$ret[$v] = $v;
		}
	}
	return $ret;
}

/*if ($_SERVER["argc"] < 2)
{
	echo("usage: mk_class new_class_name new_class_def [folder]\n");
	echo("  REQUIRED:\n");
	echo("	  new_class_name: the name of the class and the file of the class\n");
	echo("	  new_class_def: the class def in ini file (CL_BLAH)\n");
	echo("  OPTIONAL:\n");
	echo("    folder: the folder where the class file will be created, under AWROOT/classes\n");
	exit(1);
}*/

chdir("../..");

$stdin = fopen("php://stdin", "r");
$stdo = fopen("php://stdout", "w");

///////////////////////////////////////////////////////////////////
// ask the user the needed info
//////////////////////////////////////////////////////////////////

echo "Hello! I am the AW class-o-maker 3000!\n";
echo "You will answer these questions:\n\n";

echo "Class def (CL_FOO_BAR): ";
$class['def'] = trim(fgets($stdin));

echo "Folder where the class file is (created under AWROOT/classes): ";
$class['folder'] = trim(fgets($stdin));

echo "Class file (foo_bar): ";
$class['file'] = trim(fgets($stdin));

echo "Class name, users see this, so be nice (Foo bar): ";
$class['name'] = trim(fgets($stdin));

echo "Can the user add this class? (1/0): ";
$class['can_add'] = trim(fgets($stdin));

echo "Class parent folder id(s) (from classfolders.ini): ";
$class['parents'] = trim(fgets($stdin));

echo "Alias (if you leave this empty, then the class can't be added as an alias): ";
$class['alias'] = trim(fgets($stdin));

echo "Syslog type (ST_FOO , goes to syslog.ini): ";
$class['syslog.type'] = trim(fgets($stdin));


////////////////////////////////////////////////////////////////////
// check if a class by this name does not already exist!
////////////////////////////////////////////////////////////////////

$clnf = ($class['folder'] == "" ? $class['file'].".aw" : $class['folder']."/".$class['file'].".aw");
$tpnf = ($class['folder'] == "" ? $class['file'] : $class['folder']."/".$class['name']);

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

////////////////////////////////////////////////////////////////////
// now the hard bit - ini file parsing and modifying
////////////////////////////////////////////////////////////////////

echo "\n\nParsing and writing config/ini/classes.ini...\n";

////////////////////
// write classes.ini
////////////////////

// read config/ini/classes.ini and find the largest class number in it
$clsini = file_get_contents('config/ini/classes.ini');
preg_match_all("/classes\[(\d+)\]\[def\]/",$clsini, $clid_mt);
$clids = make_keys($clid_mt[1]);
$new_clid = max($clids)+1;

echo "...got new class_id = $new_clid ... \nwriting to classes.ini:...\n";

$new_clini  = "\nclasses[$new_clid][def] = ".$class['def']."\n";
$new_clini .= "classes[$new_clid][name] = ".$class['name']."\n";
if ($class['folder'] != '')
{
	$cl_fname = $class['folder']."/".$class['file'];
}
else
{
	$cl_fname = $class['file'];
}

$new_clini .= "classes[$new_clid][file] = ".$cl_fname."\n";
$new_clini .= "classes[$new_clid][can_add] = ".$class['can_add']."\n";
if ($class['parents'] != '')
{
	$new_clini .= "classes[$new_clid][parents] = ".$class['parents']."\n";
}
if ($class['alias'] != '')
{
	$new_clini .= "classes[$new_clid][alias] = ".$class['alias']."\n";
}

$fp = fopen('config/ini/classes.ini','a');
fputs($fp, $new_clini);
fclose($fp);

echo $new_clini;
echo "\n";

///////////////////////////////////
// write syslog.ini
///////////////////////////////////

echo "parsing and adding to config/ini/syslog.ini..\n";

// read and find the biggest number
$sysini = file_get_contents('config/ini/syslog.ini');
preg_match_all("/syslog\.types\[(\d+)\]\[def\]/",$sysini, $sys_mt);
$sysids = make_keys($sys_mt[1]);
$new_sysid = max($sysids)+1;

$first_match = false;
$inserted = false;

$new_sysini = array();
$syslines = explode("\n", $sysini);
foreach($syslines as $sl)
{
	if (trim($sl) != '')
	{
		if (!$first_match)
		{
			// check if we found the first line
			if (strpos($sl, "syslog.types[") !== false)
			{
				$first_match = true;
			}
		}
		else
		{
			if (strpos($sl, "syslog.types") === false && !$inserted)
			{
				// if we reached the end of types definitions, then add the new typedef to the end
				$new_sysini[] = "syslog.types[".$new_sysid."][def] = ".$class['syslog.type'];
				echo "wrote...syslog.types[".$new_sysid."][def] = ".$class['syslog.type']."\n";
				$new_sysini[] = "syslog.types[".$new_sysid."][name] = ".$class['name'];
				echo "wrote...syslog.types[".$new_sysid."][name] = ".$class['name']."\n";
				$new_sysini[] = "";
				$inserted = true;
			}
		}
	}

	// also add the new type to the end of SA_ADD and SA_CHANGE
	if (strpos($sl, "syslog.actions[1][types]") !== false)
	{
		$sl = trim($sl).",".$new_sysid;
	}
	if (strpos($sl, "syslog.actions[3][types]") !== false)
	{
		$sl = trim($sl).",".$new_sysid;
	}
	$new_sysini[] = $sl;
}

file_put_contents('config/ini/syslog.ini',join("\n",$new_sysini)); 

echo "\n";


///////////////////////////////////////////////////////
// now create the actual class files
///////////////////////////////////////////////////////

echo "\nmaking class $clnf...\n\n";

if ($class['folder'] != "")
{
	// check if the directory exists
	if (!is_dir("classes/".$class['folder']))
	{
		mkdir("classes/".$class['folder'],0775);
		echo "created classes/".$class['folder']."...\n";
	}
}

$fc = str_replace("__classdef", $class['def'], file_get_contents("install/class_template/classes/base.aw"));
$fc = str_replace("__tplfolder", $tpnf, $fc);
$fc = str_replace("__syslog_type", $class['syslog.type'], $fc);
file_put_contents("classes/$clnf",str_replace("__classname", $class['file'], $fc));
echo "created classes/$clnf...\n";

$folder = $class['folder'] != "" ? "folder=\"".$class['folder']."\"" : "";
$fc = str_replace("__classname", $class['file'], file_get_contents("install/class_template/xml/orb/base.xml"));
file_put_contents("xml/orb/".$class['file'].".xml",str_replace("__classfolder", $folder, $fc));
echo "created xml/orb/".$class['file'].".xml...\n";


echo "\n\nmaking ini file...\n\n";
passthru('make ini');

echo "\n\nmaking properties...\n\n";
passthru('make properties');

echo "\n\nall done! \n\n";
?>
