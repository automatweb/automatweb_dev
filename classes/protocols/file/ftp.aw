<?php
/*

@classinfo syslog_type=ST_FTP_LOGIN

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property server type=textbox 
@caption Server

@property username type=textbox 
@caption Kasutajanimi

@property password type=password 
@caption Parool

*/

define("FTP_ERR_CONNECT", 1);
define("FTP_ERR_LOGIN", 2);
define("FTP_ERR_NOTCONNECTED", 3);

class ftp extends class_base
{
	var $handle = null;

	function ftp()
	{
		$this->init(array(
			'clid' => CL_FTP_LOGIN
		));
	}

	function is_available()
	{
		return extension_loaded("ftp");
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
		
		echo "connect, ".dbg::dump($arr)." <br />";
		if (($this->handle = ftp_connect($host)) == FALSE)
		{
			echo "err_connect! <br />";
			return FTP_ERR_CONNECT;
		}
		if (ftp_login($this->handle, $user, $pass) == FALSE)
		{
			echo "err login! <br />";
			return FTP_ERR_LOGIN;
		}
		echo "success , $this->handle <br />";
	}

	////
	// !returns a list of files in the current server in folder $folder
	function dir_list($folder)
	{
		if (!$this->handle)
		{
			echo "notkonnekted! <br />";
			return FTP_ERR_NOTCONNECTED;
		}
		$_t = ftp_nlist($this->handle, $folder);
		$arr = new aw_array($_t);
		echo "ret ".dbg::dump($_t)." folder = $folder <br />";
		return $arr->get();
	}

	////
	// !returns the contents of file $file in the current server
	function o_get($file)
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

	function get($url)
	{
		$this->last_url = $url;
		return file_get_contents($url);
	}

	function get_type()
	{
		$mt = get_instance("core/aw_mime_types");
		return $mt->type_for_file($this->last_url);
	}
}
?>
