<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/aw_mail.aw,v 2.2 2001/05/24 16:02:02 duke Exp $
// Thanks to Kartic Krishnamurthy <kaygee@netset.com> for ideas and sample code
// mail.aw - Sending mail. MIME compatible

// ideaalis peaks see edaspidi toetama ka teisi mailisaatmismeetodeid
// peale PHP mail funktsiooni
define('X_MAILER',"AW Mail");
define('WARNING','This is a MIME encoded message');
define('OCTET','application/octet-stream');
define('TEXT','text/plain');
define('HTML','text/html');
define('CHARSET','iso-8859-4');
define('INLINE','inline');
define('ATTACH','attachment');
define('CRLF',"\r\n");
define('BASE64','base64');

class aw_mail {
	var $message; // siin hoiame teate erinevaid osasid
	var $mimeparts; // siin hoiame teate MIME osasid
	var $headers; // headerid
	////
	// !Konstruktor
	// argumendid
	// method(string) - mis meetodi abil meili saadame?
	function aw_mail($args = array())
	{
		$this->message = array();
		$this->headers = array();
		$this->mimeparts = array();
		$this->mimeparts[] = "";
		// by default php mail funktsioon
		$this->method = ($args["method"]) ? $args["method"] : "sendmail";
	}

	////
	// !Selle funktsiooni abil loome uue teate
	// argumendid
	// froma(string) - kellelt (aadress)
	// fromn(string) - kellelt, nimi
	// to(string) - kellele
	// cc(string) - kellele
	// subject(string) - kirja teema
	// headers(array) - additional headers
	function create($args = array())
	{
		if (is_array($args))
		{
			if ($args["body"])
			{
				$this->body = $args["body"];
				unset($args["body"]);
			};

			if (!$args["X-Mailer"])
			{
				$args["X-Mailer"] = X_MAILER;
			};

			$this->from = $args["froma"];
			if ($args["fromn"])
			{
				$from = sprintf("%s <%s>",$args["fromn"],$args["froma"]);
				unset($args["fromn"]);
				unset($args["froma"]);
			}
			else
			{
				$from = $args["froma"];
				unset($args["froma"]);
			};
			$args["from"] = $from;

			foreach($args as $name => $value)
			{
				$uname = ucfirst($name);
				$this->headers[$uname] = $value;
			};
		};
	}

	////
	// Attaches an object to our message
	// argumendid
	// data(data) - suvaline data
	// description(contenttype) - asja kirjeldus
	// encoding(string) 
	// contenttype(string)
	// disp(string)
	function attach($args = array())
	{
		extract($args);
		if (empty($data))
		{
			return false;
		};

		if (empty($contenttype))
		{
			$contenttype = OCTET;
		};

		if (empty($encoding))
		{
			$encoding = BASE64;
		};

		if ($encoding == BASE64)
		{
			$emsg = base64_encode($data);
			$emsg = chunk_split($emsg);
		}
		else
		{
			$emsg = $data;
		};
	

		if (preg_match("!^".TEXT."!i", $contenttype) && !preg_match("!;charset=!i", $contenttype))
		{
			$contenttype .= ";\r\n\tcharset=".CHARSET ;
		};

		$msg = sprintf("Content-Type: %sContent-Transfer-Encoding: %s%s%s%s",
					$contenttype.CRLF,
					$encoding.CRLF,
					(($description) ? "Content-Description: $description".CRLF:""),
					(($disp) ? "Content-Disposition: $disp".CRLF:""),
					CRLF.$emsg.CRLF);
		if ($args["body"])
		{
			$this->mimeparts[0] = $msg;
		}
		else
		{
			$this->mimeparts[] = $msg;
		};

		return sizeof($this->mimeparts);
	}

	////
	// !Attaches a file to the message
	// argumendid:
	// path(string) - teekond failini
	// description(string) - kirjeldus
	// contenttype(string) - sisu tyyp
	// encoding(string) - encoding. DUH.
	// disp(string) content-disposition
	// name(string) string, mida kasutatakse faili nimena
	function fattach($args = array())
	{
		extract($args);
		if (!$contenttype)
		{
			$contenttype = OCTET;
		};

		if (!$encoding)
		{
			$encoding = BASE64;
		};

		// read the fscking file
		$fp = fopen($path,"rb");
		if (!$fp)
		{
			print "attach failed<br>";
			return false; // fail
		}

		if (!$name)
		{
			$name = basename($path);
		};

		$contenttype .= ";\r\n\tname=".$name;
		$data = fread($fp, filesize($path));
		return $this->attach(array(
				"data" => $data,
				"description" => $description,
				"contenttype" => $contenttype,
				"encoding" => $encoding,
				"disp" => $disp,
			));
	}

		

	function build_message($args = array())
	{
		$msg = "";
		$boundary = 'AW'.chr(rand(65,91)).'------'.md5(uniqid(rand()));
		$nparts = sizeof($this->mimeparts);

		// we have more than one attach
		if (is_array($this->mimeparts) && ($nparts > 1))
		{
			$c_ver = "MIME-Version: 1.0".CRLF;
			$c_type = 'Content-Type: multipart/mixed;'.CRLF."\tboundary=\"$boundary\"".CRLF;
			$c_enc = "Content-Transfer-Encoding: 8bit".CRLF;
			$c_desc = $c_desc?"Content-Description: $c_desc".CRLF:"";
			$warning = CRLF.WARNING.CRLF.CRLF ;
			
			// Since we are here, it means we do have attachments => body must become
			// and attachment too.
			if (!empty($this->body)) {
				$this->attach(array(
					"data" => $this->body,
					"body" => 1,
					"contenttype" => TEXT,
					"encoding" => "8bit",
				));
			};

			// Now create the MIME parts of the email!
			for ($i=0; $i < $nparts; $i++)
			{
				if (!empty($this->mimeparts[$i]))
				{
					$msg .= CRLF."--".$boundary.CRLF.$this->mimeparts[$i].CRLF;
				};
			};

			$msg .= "--".$boundary."--".CRLF;
			$msg = $c_ver.$c_type.$c_enc.$c_desc.$warning.$msg;
		}
		else
		{
			if (!empty($this->body))
			{
				$msg = CRLF.$this->body.CRLF.CRLF;
			};
		};
		return $msg;
	}

	function gen_mail()
	{
		$email = "";
		foreach($this->headers as $name => $value)
		{
			$email .= sprintf("%s: %s\n",$name,$value);
		};

		$email .= $this->build_message();
		$headers = join("\n",$this->headers);
		mail($this->headers["To"],$this->headers["Subject],$email,$headers);
		//$f = popen("/usr/sbin/sendmail -f " . $this->from,"w");
		//fwrite($f,$email);
		//pclose($f);
	}

};
?>
