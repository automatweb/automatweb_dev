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

// XXX: what exactly is the point of those constants?
define("FTP_ERR_CONNECT", 1);
define("FTP_ERR_LOGIN", 2);
define("FTP_ERR_NOTCONNECTED", 3);

class ftp extends class_base
{
	var $handle = null;
	var $verbose = false;

	function ftp($arr = array())
	{
		$this->init(array(
			'clid' => CL_FTP_LOGIN
		));
		if (!empty($arr["verbose"]))
		{
			$this->verbose = $arr["verbose"];
		};
	}

	function is_available()
	{
		return extension_loaded("ftp");
	}

	/** creates connection to ftp server
		@attrib api=1
		@param host required
		@param user required
		@param pass required
		@param timeout optional

		@comment timeout defaults to 10 seconds
	**/
	function connect($arr)
	{
		extract($arr);
		if (!isset($timeout))
		{
			$timeout = 10;
		}
	
		if ($this->verbose)
		{
			echo "connect, ".dbg::dump($arr)." <br />";
		};
		if (($this->handle = ftp_connect($host)) == FALSE)
		{
			if ($this->verbose)
			{
				echo "err_connect! <br />";
			};
			return FTP_ERR_CONNECT;
		}
		if (ftp_login($this->handle, $user, $pass) == FALSE)
		{
			if ($this->verbose)
			{
				echo "err login! <br />";
			};
			return FTP_ERR_LOGIN;
		}
		if ($this->verbose)
		{
			echo "success , $this->handle <br />";
		};
	}

	/** closes FTP connection 
		@attrib api=1
	**/
	function disconnect()
	{
		if ($this->verbose)
		{
			echo "closing connection ";
		};
		ftp_close($this->handle);
	}

	/** returns a list of files in the current server in folder $folder
		@attrib api=1
		@param folder required
	**/
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

	/** returns the contents of file $file in the current server
		
		@attrib api=1
		@param file required

	**/
	function get_file($file)
	{
		if (!$this->handle)
		{
			return FTP_ERR_NOTCONNECTED;
		}

		$fn = tempnam(aw_ini_get("server.tmpdir"), "aw_ftp");
		$res = @ftp_get($this->handle, $fn, $file, FTP_BINARY);

		if ($res)
		{
			$fc = file_get_contents($fn);
			unlink($fn);
			return $fc;
		}
		else
		{
			if ($this->verbose)
			{
				echo "cannot find $file on servr ";
			};
			return false;
		};

	}

	/** uploads $content to $remote_file on the current server

		@attrib api=1
		@param file required
		@param content required

	**/
	function put_file($remote_file,$content)
	{
		if (!$this->handle)
		{
			return FTP_ERR_NOTCONNECTED;
		};
		$fn = tempnam(aw_ini_get("server.tmpdir"), "aw_ftp");

		$fh = fopen($fn,"w");
		fwrite($fh,$content);
		fclose($fh);

		$res = ftp_put($this->handle,$remote_file,$fn,FTP_BINARY);
		unlink($fn);
		return $res;
	}
	
	/** deletes $file on the current server
		@attrib api=1
		@param file required
	**/
	function delete($arr)
	{
		if (!$this->handle)
		{
			return FTP_ERR_NOTCONNECTED;
		}
		if (ftp_delete($this->handle, $arr['file']))
		{
			return true;
		}
		return false;
	}

	/** changes the directory on the current server to $path
		@attrib api=1
		@param path required
	**/
	function cd($arr)
	{
		if (!$this->handle)
		{
			return FTP_ERR_NOTCONNECTED;
		}
		if (ftp_chdir($this->handle, $arr['path']))
		{
			return true;
		}
		return false;
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
