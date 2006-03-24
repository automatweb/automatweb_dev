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

	
	/**
	@attrib api=1 params=name
		
	@param host required type=string
		The FTP server address.
		This parameter shouldn't have any trailing slashes and shouldn't be prefixed with ftp://
	@param user required type=string
	@param pass required type=string
	@param timeout optional type=int default=10;
		it does nothing
	
	@returns 
		FTP_ERR_CONNECT - if cant connect
		FTP_ERR_LOGIN - if cant login

	@examples
		$ftp_inst = get_instance("protocols/file/ftp");
		$ftp_inst->connect(array(
			"host" => "media.struktuur.ee",
			"user" => "keegi",
			"pass" => "kalakala",
		));
		$files = $ftp_inst->dir_list("files/);
		foreach($files as $file)
		{
			$fdat = $ftp_inst->get_file($file);
			$ftp_inst->delete($file);
		}
		if ($ftp_inst->cd(array("path" => "cool_files/") == true) echo 'this folder really does exist';
		$ftp_inst->disconnect();
	
	@comment creates connection to ftp server
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

	/**
	@attrib api=1
	@comment closes FTP connection
	
	@examples ${connect}
	**/
	function disconnect()
	{
		if ($this->verbose)
		{
			echo "closing connection ";
		};
		ftp_close($this->handle);
	}

	/**
	@attrib api=1 params=pos
		
	@param folder required
		The directory to be listed.
		This parameter can also include arguments, eg. ftp_nlist($conn_id, "-la /your/dir");
		Note that this parameter isn't escaped so there may be some issues with filenames 
		containing spaces and other characters
	
	@returns an array of filenames in the current server in folder $folder on success
		FTP_ERR_NOTCONNECTED if not connected

	@examples ${connect}
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

	/**
	@attrib api=1 params=pos
		
	@param file required type=string
		The remote file path
	@returns 
		contents of file $file in the current server
		FTP_ERR_NOTCONNECTED - if not connected
		FALSE if there is no file with that name
	
	@examples ${connect}
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
	
	/**
	@attrib api=1 params=pos
		
	@param remote_file required type=string
		The remote file path
	@param file required type=string
		The local file
	@returns 
		FTP_ERR_NOTCONNECTED - if not connected
		TRUE on success
		FALSE on failure.
	
	@examples
	@comments 
		puts file to the current server
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
	
	/**
	@attrib api=1 params=name
		
	@param file required type=string
		The file to delete
	@returns
		FTP_ERR_NOTCONNECTED - if not connected
		TRUE on success
		FALSE on failure
	
	@comments
		deletes $file on the current server
	@examples ${connect}
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

	/**
	@attrib api=1 params=name
		
	@param path required type=string
		The target directory
	@returns
		FTP_ERR_NOTCONNECTED - if not connected
		TRUE on success
		FALSE on failure
	
	@comments
		changes the directory on the current server to $path
	@examples ${connect}
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
	
	/**
	@attrib api=1 params=pos
		
	@param url required type=string
		The target directory
	@returns
		string / contents of file
		FALSE on failure
	
	@comments
		Reads entire file into a string
	**/
	function get($url)
	{
		$this->last_url = $url;
		return file_get_contents($url);
	}
	
	/**
	@attrib api=1
	
	@returns string / file type
		FALSE , if there is no info for this extension
	
	@comments
		returns type of file last used with function get()
	**/
	function get_type()
	{
		$mt = get_instance("core/aw_mime_types");
		return $mt->type_for_file($this->last_url);
	}
}
?>
