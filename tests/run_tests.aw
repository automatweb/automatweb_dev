<?php
if ($argc < 2)
{
	die(
		"Usage:\n\tphp ${argv[0]} /path/to/site/aw.ini [folder folder folder to run tests in]\n\n");
}
//print getcwd(); 
$aw_dir = getcwd();//"/www/dev/autotest/automatweb_dev";
$site_dir = str_replace("/automatweb_dev", "", $aw_dir);//"/www/dev/autotest";
$basedir =$aw_dir;// realpath("..")."/automatweb_dev";

include("$basedir/init.aw");
//init_config(array("ini_files" => array("$basedir/aw.ini", $argv[1])));
init_config(array(
	"cache_file" => $site_dir."/pagecache/ini.cache",
 	"ini_files" => array($aw_dir."/aw.ini",$site_dir."/aw.ini")
 ));
chdir("classes");
//echo getcwd() . "\n<br>";
classload("defs");
classload("aw_template","core/util/timer");
classload("core/obj/object", "core/error");
if($do_test || !$autotest)
{
	require_once('simpletest/unit_tester.php');
	require_once('simpletest/reporter.php');

	$awt = new aw_timer;
	
	//ob_start();
	
	if ($argc < 3)
	{
		$argc = 3;
		$argv[2] = "classes";
	}
	for($i = 2; $i < $argc; $i++)
	{
		$path = $aw_dir."/tests/".$argv[$i];//"/www/dev/autotest/automatweb_dev/tests/".$argv[$i];
	
		echo "running tests in ".$argv[$i]."... \n\n<br><br>";
	
		if (is_file($path))
		{
			$files[] = $path;
		}
		else
		{
			// get files from folder
			$p = get_instance("core/aw_code_analyzer/parser");
			$files = array();
			$p->_get_class_list($files, $path);
		}
		$suite = &new GroupTest("All tests");
		
		foreach($files as $filename)
		{
		//	if(substr_count($filename, "init.aw") > 0) continue;
			//$suite = &new GroupTest(basename($filename,".aw")."_test");
			$suite->addTestFile($filename);
			//$suite->run(new TextReporter());
			//$suite->run(new TextReporter());
		}
		$suite->run(new TextReporter());
	}
//	$log["data"] = str_replace("\n" , "" , ob_get_contents());
	echo "\n<br><br>";
}
elseif($_GET["test"])
{
	$log_array = _get_log($site_dir);
	foreach($log_array as $log)
	{
		if(is_array(unserialize($log)))
		{
			$val = unserialize($log);
			if($val["time"] == $_GET["test"])
			{
				print "Tested : ".date("d.m.Y H:i" , $val["time"]);
				print "<br>result : <br>";
				
					classload("vcl/table");
				//	get_instance("vcl/table");
				$t = new vcl_table(array(
					"layout" => "generic",
				));
				$t->define_field(array(
					"name" => "case",
					"caption" => t(""),
				));
				$t->define_field(array(
					"name" => "result",
					"caption" => t(""),
				));
				foreach($val["stuff"]["case"] as $key => $val)
				{
				  $t->define_data(array("case" => $key , "result" => $val));
				}
				print $t->draw();
				print $stuff["conc"];
//				print $val["data"];
				print "<br><br>";
			}
		}
	}
	print html::href(array("caption" => "Tagasi statistikasse" , "url" => $GLOBALS["HTTP_HOST"]));
}
else
{
	print "N�ita tabelis ";
	print html::href(array("caption" => "10" , "url" => "?show=10"));
	print " , " ;
	print html::href(array("caption" => "25" , "url" => "?show=25"));
	print " tulemust" ;
	classload("vcl/table");
//	get_instance("vcl/table");
	$t = new vcl_table(array(
		"layout" => "generic",
	));
	$t->define_field(array(
		"name" => "time",
		"caption" => t("Time"),
	));

	$t->define_field(array(
		"name" => "file",
		"caption" => t("Failid (kommititud)"),
	));

	$t->define_field(array(
		"name" => "email",
		"caption" => t("Kommittija e-mail"),
	));

	$t->define_field(array(
		"name" => "run",
		"caption" => t("Cases run"),
	));
	$t->define_field(array(
		"name" => "pass",
		"caption" => t("Passes"),
	));
	$t->define_field(array(
		"name" => "fail",
		"caption" => t("Failures"),
	));
	$t->define_field(array(
		"name" => "exc",
		"caption" => t("Exceptions"),
	));
	$log_array = _get_log($site_dir);
	//arr($log_array);
	$log_data = array();
	$done = array();
	$count = sizeof($log_array);
	$show = 5;
	if($_GET["show"])
	{
		$show = $_GET["show"];
	}
	foreach($log_array as $log)
	{
		if(is_array(unserialize($log)) && $show >= $count-1)
		{
			$val = unserialize($log);
			$color = "white";
			if($val["fail"]) $color = "red";
			else $color = "green";
			$t->define_data(array(
			        "run" => "<font color=".$color.">".$val["tested"]."</br>",
				"email" => "<font color=".$color.">".$val["email"]."</br>",
				"file" => "<font color=".$color.">".$val["file"]."</br>",
				"pass" => "<font color=".$color.">".$val["passed"]."</br>",
				"fail" => "<font color=".$color.">".$val["fail"]."</br>",
				"exc" => "<font color=".$color.">".$val["exc"]."</br>",
				"time" => "<a href='?test=".$val["time"]."'><font color=".$color.">".date("d.m.Y H:i" , $val["time"])."</br></a>",
			));
		}
		$show++;	
	}
	print $t->draw();
}


function __disable_err()
{
	aw_global_set("__from_raise_error", 1);
}

function __is_err()
{
	aw_global_set("__from_raise_error", 0);
	if ($GLOBALS["aw_is_error"] == 1)
	{
		$GLOBALS["aw_is_error"] = 0;
		return true;
	}
	return false;
}
?>
