<?php
ob_implicit_flush(true);

function _file_get_contents($name)
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

#chdir("../..");

$stdin = fopen("php://stdin", "r");
$stdo = fopen("php://stdout", "w");

///////////////////////////////////////////////////////////////////
// ask the user the needed info
//////////////////////////////////////////////////////////////////

echo "Hello! I am the AW class-o-maker 3000!\n";
echo "You will answer these questions:\n\n";

$continue = false;
while(!$continue)
{
	echo "Folder where the class file is (created under AWROOT/classes): ";
	$class['folder'] = trim(fgets($stdin));
	if (is_dir("classes/" . $class["folder"]))
	{
		$continue = true;
	}
	else
	{
		echo "Folder does not exist, create it (1/0): ? ";
		$answer = fgets($stdin);
		echo "\n";
		if ($answer == 1)
		{
			$continue = true;
		};
	};
};

echo "Class file (foo_bar): ";
$class['file'] = trim(fgets($stdin));

// make these automatically, then we can be sure they foillow standard and are unique
$class['def'] = "CL_".strtoupper($class['file']);
$class['syslog.type'] = "ST_".strtoupper($class['file']);

echo "Class name, users see this, so be nice (Foo bar): ";
$class['name'] = trim(fgets($stdin));

echo "Can the user add this class? (1/0): ";
$class['can_add'] = trim(fgets($stdin));

echo "Class parent folder id(s) (from classfolders.ini): ";
$class['parents'] = trim(fgets($stdin));

echo "Alias (if you leave this empty, then the class can't be added as an alias): ";
$class['alias'] = trim(fgets($stdin));

echo "Class is remoted? (1/0): ";
$class['is_remoted'] = trim(fgets($stdin));

if ($class['is_remoted'])
{
	echo "Default server to remote to (http://www.foo.ee): ";
	$class['default_remote_server'] = trim(fgets($stdin));
}


////////////////////////////////////////////////////////////////////
// check if a class by this name does not already exist!
////////////////////////////////////////////////////////////////////

$clnf = ($class['folder'] == "" ? $class['file'].".aw" : $class['folder']."/".$class['file'].".aw");
$tpnf = ($class['folder'] == "" ? $class['file'] : $class['folder']."/".$class['file']);

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

////////////////////
// write classes.ini
////////////////////

// read config/ini/classes.ini and find the largest class number in it
$clsini = _file_get_contents('config/ini/classes.ini');
/*preg_match_all("/classes\[(\d+)\]\[def\]/",$clsini, $clid_mt);
$clids = make_keys($clid_mt[1]);
$new_clid = max($clids)+1;*/

echo "\n\nRequesting new class id...\n";

$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
aw_global_set("no_db_connection", true);

$classlist = get_instance("core/class_list");
$new_clid = $classlist->register_new_class_id(array(
	"data" => $class
));

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

if ($class['is_remoted'] == 1)
{
	$new_clini .= "classes[$new_clid][is_remoted] = ".$class['default_remote_server']."\n";
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
$sysini = _file_get_contents('config/ini/syslog.ini');
preg_match_all("/syslog\.types\[(\d+)\]\[def\]/",$sysini, $sys_mt);
$sysids = make_keys($sys_mt[1]);
//$new_sysid = max($sysids)+1;
$new_sysid = $new_clid;

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
		// mkdir can only create one level of directories at a time
		// so if the folders has several levels, we need to create all of them.
		$dir = "classes";
		$dirs = explode("/", $class['folder']);
		foreach($dirs as $fld)
		{
			$dir .= "/".$fld;
			if (!is_dir($dir))
			{
				mkdir($dir,0775);
				echo "created $dir ...\n";
			}
		}
	}
}

$fc = str_replace("__classdef", $class['def'], _file_get_contents("install/class_template/classes/base.aw"));
$fc = str_replace("__tplfolder", $tpnf, $fc);
$fc = str_replace("__syslog_type", $class['syslog.type'], $fc);
$fc = str_replace("__name", $class['name'], $fc);
file_put_contents("classes/$clnf",str_replace("__classname", $class['file'], $fc));
echo "created classes/$clnf...\n";

$folder = $class['folder'] != "" ? "folder=\"".$class['folder']."\"" : "";
$fc = str_replace("__classname", $class['file'], _file_get_contents("install/class_template/xml/orb/base.xml"));
file_put_contents("xml/orb/".$class['file'].".xml",str_replace("__classfolder", $folder, $fc));
echo "created xml/orb/".$class['file'].".xml...\n";


echo "\n\nmaking ini file...\n\n";
passthru('make ini');

echo "\n\nmaking properties...\n\n";
passthru('make properties');

echo "\n\nall done! \n\n";
?>
