<?php

/* DEPRECATED FILE */
// ! UUSI ARENDUSI SIIA POLE M6TET TEHA
// see on ainult selleks alles veel, et tagasiulatuvat yhilduvust
// vana startup skriptiga saitide const.aw-dele pakkuda.

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
			die(dbg::dump($e));
			if (!headers_sent())
			{
				header("HTTP/1.1 500 Server Error");
			}

			echo "Server error. ";

			try
			{
				automatweb::shutdown();
			}
			catch (Exception $se)
			{
				echo "Shutdown error. ";
			}
		}

		exit;
	}
}

?>
