<?php

$aw_dir = "{VAR:aw_dir}";
$site_dir = "{VAR:site_dir}";
$cache_file = $site_dir . "/pagecache/ini.cache";
$cfg_files = array($site_dir."/aw.ini");

require_once($aw_dir."/automatweb.aw");

try
{
	automatweb::start();
	// automatweb::$instance->mode(automatweb::MODE_DBG);
	automatweb::$instance->bc();
	automatweb::$instance->load_config_files($cfg_files, $cache_file);
	$request = aw_request::autoload();
	automatweb::$instance->set_request($request);
	automatweb::$instance->exec();
	automatweb::$result->send();
	automatweb::shutdown();
}
catch (Exception $e)
{
	if (!headers_sent())
	{
		header("HTTP/1.1 500 Server Error");
	}

	echo nl2br($e);

	try
	{
		automatweb::shutdown();
	}
	catch (Exception $se)
	{
		echo "<br/><br/>" . nl2br($e);
	}
}

?>
