<?php

/*

@classinfo syslog_type=ST_FTP_LOGIN

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property server type=textbox field=meta method=serialize
@caption Server

@property username type=textbox field=meta method=serialize
@caption Kasutajanimi

@property password type=password field=meta method=serialize
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
			'tpldir' => 'protocols/file/ftp',
			'clid' => CL_FTP_LOGIN
		));
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
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
