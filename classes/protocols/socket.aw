<?php
// $Header: /home/cvs/automatweb_dev/classes/protocols/socket.aw,v 1.2 2008/01/31 13:55:12 kristo Exp $
// socket.aw - low level communications
// provides functions that can be used by other classes to connect to hosts
// and read/write information to/from those hosts
/*
@classinfo  maintainer=kristo
*/
class socket 
{
	var $host;
	var $port;
	var $sock;
	var $query;

	/**
		@attrib params=name api=1

		@param host required type=string
			host to connect
		@param port required type=int
			port of the host to connect
		@comment
			opens socket connection to specified host and port
	**/
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
		$this->sock = @fsockopen($host,$port,&$errno, &$errstr,5);
		if (not($this->sock))
		{
			//print "WARNING: Connection to $host:$port failed, $errstr\n";
		};
	}

	/**
		@attrib params=name api=1
		@comment
			closes opened socket connection
	**/
	function close($args = array())
	{
		if ($this->sock)
		{
			fclose($this->sock);
		}
	}

	/**
		@attrib params=pos api=1
		@param data required type=string
		@comment
			writes the data param contents into opened socket connection
	**/
	function write($data = "")
	{
		if (not($this->sock))
		{
			//print "WARNING: No open socket to write to\n";
			return 0;
		};
		
		if (not(fputs($this->sock, $data, strlen($data))))
		{
			//print "Write error<br />";
			return 0;
		}
		fflush($this->sock);
	}

	/**
		@attrib params=pos api=1
		@param blocklen optional type=int
		@comment
			reads $blocklen bytes(default is 32762) from opened connection
		@returns
			returns the data readed or NULL, if no connections opened.
	**/	
	function read($blocklen = 32762)
	{
		if (!$this->sock)
		{
			return NULL;
		}
		return fread($this->sock,$blocklen);
	}
};
?>
