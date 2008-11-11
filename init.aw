<?php
/*
@classinfo  maintainer=kristo
*/

if (!defined("AW_DIR"))
{
	// script for old site startup
	function init_config($args)
	{
		$cache_file = $args["cache_file"];
		$cfg_files = $args["ini_files"];

		foreach ($cfg_files as $file)
		{
			$file = dirname($file) . "/automatweb.aw";
			if (is_readable($file))
			{
				require_once($file);
				break;
			}
		}

		try
		{
			automatweb::start();
			automatweb::$instance->mode(automatweb::MODE_DBG);
			automatweb::$instance->bc();
			automatweb::$instance->load_config_files($cfg_files, $cache_file);
			$request = new aw_request(true);
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

		exit;
	}
}

?>
