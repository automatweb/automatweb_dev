<?php

if (isset($_SERVER["DOCUMENT_ROOT"]))
{
	// server document root dir
	$doc_root = str_replace(array("\\", "//"), "/", ($_SERVER["DOCUMENT_ROOT"] . "/"));

	// site path requested in uri
	$path = $_SERVER["REQUEST_URI"];
	$path = parse_url($path);
	$path = pathinfo($path["path"]);
	$path = dirname(strrev(strstr(strrev($path["dirname"]), "automatweb")));
	$path = str_replace(array("\\", "//"), "/", ($path . "/"));

	// site dir
	$site_dir = realpath($doc_root . $path);
	$site_dir = str_replace(array("\\", "//"), "/", ($site_dir . "/"));

	if (false === $site_dir or !is_readable($site_dir . "const.aw"))
	{
		exit("Site directory not found or not readable.");
	}
}
else
{
	exit("Server variables not defined.");
}

include_once($site_dir . "const.aw");

?>
