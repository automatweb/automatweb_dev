<?php

define("FTP_ERR_CONNECT", 1);
define("FTP_ERR_LOGIN", 2);
define("FTP_ERR_NOTCONNECTED", 3);

class ftp
{
	var $handle = null;

	function ftp($arr = false)
	{
		if (is_array($arr))
		{
			$this->connect($arr);
		}
	}

	function is_available()
	{
		return function_exists("ftp_connect");
	}

	////
	// !connects to ftp host
	// parameters
	//	host - ftp server
	//	user - ftp username
	//	pass - ftp password
	//  timeout - optional, defaults to 10 seconds
	function connect($arr)
	{
		extract($arr);
		if (!isset($timeout))
		{
			$timeout = 10;
		}
		
		if (($this->handle = ftp_connect($host)) == FALSE)
		{
			return FTP_ERR_CONNECT;
		}
		if (ftp_login($this->handle, $user, $pass) == FALSE)
		{
			return FTP_ERR_LOGIN;
		}
	}

	////
	// !returns a list of files in the current server in folder $folder
	function dir_list($folder)
	{
		if (!$this->handle)
		{
			return FTP_ERR_NOTCONNECTED;
		}
		$arr = new aw_array(ftp_nlist($this->handle, $folder));
		return $arr->get();
	}

	////
	// !returns the contents of file $file in the current server
	function get($file)
	{
		if (!$this->handle)
		{
			return FTP_ERR_NOTCONNECTED;
		}

		// save the damn thing to a temp file for now.
		$fn = tempnam(aw_ini_get("server.tmpdir"), "aw_ftp");
		$fh = fopen($fn, "w");
		$res = ftp_fget($this->handle, $fh, $file);
		fclose($fh);

		$fc = file_get_contents($fn);
		unlink($fn);
		return $fc;
	}
}
?>
