<?php
if (POP3_LOADED != 1) {
define(POP3_LOADED,1);

	class pop3 extends aw_template
	{
		function pop3()
		{
			$this->db_init();
		}

		function get_messages($server,$user,$pass,$delete,$uidls)
		{
			if (!$this->connect($server))
				return false;

			$this->read_response();
			$this->send_command("USER $user");
			if (!$this->get_status($this->read_response()))
			{	
				$this->raise_error("pop3: Invalid username $user!",true);
				return false;
			}

			$this->send_command("PASS $pass");
			if (!$this->get_status($this->read_response()))
			{	
				$this->raise_error("pop3: Invalid password for user $user!",true);
				return false;
			}

			$this->send_command("STAT");
			if (!$this->get_status($res = $this->read_response()))
			{	
				$this->raise_error("pop3:  weird error $res after STAT!",true);
				return false;
			}
			preg_match("/\+OK (.*) (.*)/",$res,$mt);
			$no_msgs = $mt[1];

			$this->send_command("UIDL");
			if (!$this->get_status($res = $this->read_response()))
			{	
				$this->raise_error("pop3:  weird error $res after UIDL!",true);
				return false;
			}
			$muidls = array();
			for ($i=1; $i <= $no_msgs; $i++)
			{
				preg_match("/(\d*)\s*(.*)/",$res = $this->read_response(),$br);
				$muidls[$br[1]] = $br[2];
//				echo $br[2],"<br>";
			}
			$this->read_response();	// a "." is returned after the list

			$msgs = array();
			for ($i=1; $i <= $no_msgs; $i++)
			{
				// only retrieve messages, that are not already downloaded
				if (!isset($uidls[$muidls[$i]]))
				{
					$msgs[] = array("msg" => $this->get_message($i), "uidl" => $muidls[$i]);
//					echo "message nr $i not downed, uidl = '",$muidls[$i],"<br>";
				}

				if ($delete)
					$this->send_command("DELE $i");
			}

			$this->send_command("QUIT");
			$this->read_response();

			return $msgs;
		}

		function connect($server)
		{
			$this->fp = fsockopen($server, 110, &$errno, &$errstr, 20);
			if (!$this->fp)
			{
				$this->raise_error("pop3: error connecting, $errno , $errstr",true);
				return false;
			}
//			echo "connected<br>\n";
//			flush();
			return true;
		}

		function read_response()
		{
			$line = fgets($this->fp, 512);
//			echo "<< :$line<br>";
//			flush();
			return $line;
		}

		function send_command($cmdstr)
		{
			fputs($this->fp, $cmdstr."\n");
//			echo ">>> :$cmdstr<br>\n";
//			flush();
		}

		function get_status($line)
		{
			if (strlen($line) > 0)
			{
				if ($line[0] == "+")
					return true;
				else
					return false;
			}

			return false;
		}

		function get_message($num)
		{
			$this->send_command("RETR $num");
			if (!$this->get_status($res = $this->read_response()))
			{
				$this->raise_error("pop3: imelik error $res after RETR $num !",true);
				return false;
			}
			$ret = "";
			$continue = true;
			do {
				$line = $this->read_response();
				if ($line == ".\x0d\x0a")
					$continue = false;
				else
				{
					if ($line[0] == ".")
						$ret.=substr($line,1);
					else
						$ret.=$line;
				}
			} while ($continue);
//			echo "message $num follows:<br>\n<pre>$ret</pre>\nBEEP!\n<br>";
//			flush();
			return $ret;
		}
	}
}
?>
