<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/socket.aw,v 2.7 2004/11/01 20:20:34 kristo Exp $
// socket.aw - low level communications
// provides functions that can be used by other classes to connect to hosts
// and read/write information to/from those hosts
class socket 
{
	var $host;
	var $port;
	var $sock;
	var $query;
	function socket($args = array())
	{
		if ( ($args["host"]) && ($args["port"]) )
		{
			$this->socket = $this->open($args);
		}
	}

	function open($args = array())
	{
		extract($args);
		$this->sock = fsockopen($host,$port,&$errno, &$errstr,5);
		if (not($this->sock))
		{
			print "WARNING: Connection to $host:$port failed, $errstr\n";
			echo dbg::process_backtrace(debug_backtrace());
		};
	}

	function close($args = array())
	{
		if ($this->sock)
		{
			fclose($this->sock);
		}
	}

	function write($data = "")
	{
		if (not($this->sock))
		{
			print "WARNING: No open socket to write to\n";
			return 0;
		};
		
		if (not(fputs($this->sock, $data, strlen($data))))
		{
			print "Write error<br />";
			return 0;
		}
		fflush($this->sock);
	}

	function read($blocklen = 32762)
	{
		return fread($this->sock,$blocklen);
	}
};
?>
