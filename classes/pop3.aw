<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/pop3.aw,v 2.7 2002/11/07 10:52:24 kristo Exp $
class pop3 extends aw_template
{
	function pop3()
	{
		$this->init("");
		lc_load("definition");
	}

	function get_messages($server,$user,$pass,$delete,$uidls)
	{
		if (!$this->connect($server))
		{
			return false;
		}

		$this->read_response();
		$this->send_command("USER $user");
		if (!$this->get_status($this->read_response()))
		{	
			$this->raise_error(ERR_POP3_INVUSER,"pop3: Invalid username $user!",true);
			return false;
		}

		$this->send_command("PASS $pass");
		if (!$this->get_status($this->read_response()))
		{	
			$this->raise_error(ERR_POP3_INVPWD,"pop3: Invalid password for user $user!",true);
			return false;
		}

		$this->send_command("STAT");
		if (!$this->get_status($res = $this->read_response()))
		{	
			$this->raise_error(ERR_POP3_STAT,"pop3:  weird error $res after STAT!",true);
			return false;
		}
		preg_match("/\+OK (.*) (.*)/",$res,$mt);
		$no_msgs = $mt[1];

		$this->send_command("UIDL");
		if (!$this->get_status($res = $this->read_response()))
		{	
			$this->raise_error(ERR_POP3_UIDL,"pop3:  weird error $res after UIDL!",true);
			return false;
		}
		$muidls = array();
		for ($i=1; $i <= $no_msgs; $i++)
		{
			preg_match("/(\d*)\s*(.*)/",$res = $this->read_response(),$br);
			$muidls[$br[1]] = $br[2];
//			echo $br[2],"<br>";
		}
		$this->read_response();	// a "." is returned after the list

		$msgs = array();
		for ($i=1; $i <= $no_msgs; $i++)
		{
			// only retrieve messages, that are not already downloaded
			$key = trim($muidls[$i]);
			if (!in_array($key,$uidls))
			//if (isset($uidls[$key]))
			{
				$msgs[] = array("msg" => $this->get_message($i), "uidl" => $muidls[$i]);
			}
			else
			{
				#echo "message nr $i not downed, uidl = '",$muidls[$i],"<br>";
				#print "idl was not set, therefore we are not skipping the message<br>";
			};

			//if ($delete)
			//	$this->send_command("DELE $i");
		}

		$this->send_command("QUIT");
		$this->read_response();

		return $msgs;
	}

	function connect($server)
	{
		$server = trim($server);
		$this->fp = fsockopen($server, 110, &$errno, &$errstr);
		if (!$this->fp)
		{
			$this->raise_error(ERR_POP3_CONNECT,"pop3: error connecting, $errno , $errstr",true);
			return false;
		}
//		echo "connected<br>\n";
//		flush();
		return true;
	}

	function read_response()
	{
		$line = fgets($this->fp, 512);
//		echo "<< :$line<br>";
//		flush();
		return $line;
	}

	function send_command($cmdstr)
	{
		fputs($this->fp, $cmdstr."\n");
//		echo ">>> :$cmdstr<br>\n";
//		flush();
	}

	function get_status($line)
	{
		if (strlen($line) > 0)
		{
			if ($line[0] == "+")
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	function get_message($num)
	{
		$this->send_command("RETR $num");
		if (!$this->get_status($res = $this->read_response()))
		{
			$this->raise_error(ERR_POP3_RETR,"pop3: imelik error $res after RETR $num !",true);
			return false;
		}
		$ret = "";
		$continue = true;
		do {
			$line = $this->read_response();
			if ($line == ".\x0d\x0a")
			{
				$continue = false;
			}
			else
			{
				if ($line[0] == ".")
				{
					$ret.=substr($line,1);
				}
				else
				{
					$ret.=$line;
				}
			}
		} while ($continue);
//	echo "message $num follows:<br>\n<pre>$ret</pre>\nBEEP!\n<br>";
//		flush();
		return $ret;
	}
}
?>
