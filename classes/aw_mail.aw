<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/aw_mail.aw,v 2.9 2001/06/20 05:21:52 duke Exp $
// Thanks to Kartic Krishnamurthy <kaygee@netset.com> for ideas and sample code
// mail.aw - Sending and parsing mail. MIME compatible

// I am not too happy with the structure of this class. Parts of need to be redesigned and rewritten
// badly
// Minu unistus ( :) ) on selline, et kohe peale parsimist, voiks sellesama klassi abil
// teate kohe ka v�lja saata

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
define('CRLF',"\n");
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
		return $this->clean($args);
	}

	////
	// !Resets the object
	function clean($args = array())
	{
		$this->message = array();
		$this->headers = array();
		$this->mimeparts = array();
		$this->mimeparts[] = "";
		// by default php mail funktsioon
		$this->method = ($args["method"]) ? $args["method"] : "sendmail";
	}

	////
	// !Returns a decoded MIME part
	// argumendid:
	// part - osa number voi nimi
	function get_part($args = array())
	{
		extract($args);
		$block = array();
		if ($part == "body")
		{
			$block["headers"] = $this->headers;
			$block["body"] = $this->body;
		}
		else
		{
			$partnum = $part - 1;
			if (!is_array($this->mimeparts[$part]))
			{
				return false;
			};
			$block = $this->mimeparts[$part];
		};
			
		switch($block["headers"]["Content-Transfer-Encoding"])
		{
			case "base64":
				$content = base64_decode($block["body"]);
				break;

			case "doubleROT13":
				// $content = your_decoding_function_call_here
				break;

			default:
				$content = $block["body"];
		};
		$block["body"] = $content;
		return $block;
	}

	////
	// !Parses a MIME formatted data block (e.g. email message)
	// arguments:
	// data(string) - body of the message
	// returns the number of attaches found
	function parse_message($args = array())
	{
		// First we pass the whole message through our parser
		$res = $this->_parse_block(array(
					"data" => $args["data"],
					));

		$this->headers = $res["headers"];

		// Do we have a multipart message?
		if (preg_match("/^multipart\/mixed/i",$this->headers["Content-Type"]))
		{
			if (!$this->headers["Boundary"])
			{
				preg_match("/boundary=(.*)$/i",$this->headers["Content-Type"],$matches);
				$this->headers["Boundary"] = $matches[1];
			};
			$separator = "--" . $this->headers["Boundary"]; // feel free to consult the RFC to understand this
			$msg_parts = explode($separator,$res["body"]);
			$count = sizeof($msg_parts);
			// we should always get at least 4 parts and it is safe to ignore first and last, since
			// they do not contain anything of importance
			// second will contain the body of the message, and starting from the third are the attaches
			for ($i = 1; $i <= ($count - 2); $i++)
			{
				$block = $this->_parse_block(array(
						"data" => $msg_parts[$i],
						));
				$headers2 = $block["headers"];

				// kui see on esimene blokk, siis jarelikult on meil tegemist tekstiga, ja seda pole vaja
				// mime-partiks teha
				if ($i == 1)
				{
					$xheaders = array_merge($this->headers,$headers2);
					$this->headers = $xheaders;
					$this->body = $block["body"];
					$this->mimeparts[0] = array(
								"headers" => $xheaders,
								"body" => $block["body"],
							);
				}
				else
				{
					$this->mimeparts[] = $block;
				}
			};
			$this->nparts = $count - 3;
		}
		else
		{
			// nope, it was a single-part message. 
			$this->mimeparts[0] = $res;
			$this->body = $res["body"];
			$this->nparts = 0;
		}
		return $this->nparts;
	}

	////
	// !Mime parser for internal use
	// arguments:
	// data(string) - body of the message
	function _parse_block($args = array())
	{
		extract($args);
		$in_headers = true;
	
		$_headers = array();
		$headers = array();
		$body = "";

		// strip the whitespace from the beginning
		$data = preg_replace("/^\s+?/","",$data);
		
		// I'm not sure whether this is correct either.
		$data = preg_replace("/\r/","",$data);

		// split the data to individual lines
		// actually, I don't like this one not a single bit, but since I do not know the internals
		// of PHP very well, I don't know whether parsing the string until the next linefeed
		// is found, is very effective. So right now, I'll leave it as it is.

		$lines = preg_split("/\n/",$data);

		$i = 0;

		foreach($lines as $num => $line)
		{
			#print "#";
			// If we find an empty line, then we have all the headers and can continue with 
			if ((preg_match("/^$/",$line)) && ($in_headers))
			{
				$in_headers = false;
			};

			if ($in_headers)
			{
				// If the line starts with whitespace, then we will add it to the last header
				if (preg_match("/^\s/",$line))
				{
					$last = array_pop($_headers);
					$last .= " " . trim($line);
					array_push($_headers,$last);
				}
				// otherwise we just add it to the end of the headers array
				else
				{
					array_push($_headers,$line);
				};
			}	
			// when we get here, then this means that we have reached the body of the message.
			// no further actions, we just fill the $body variable.
			else
			{
				$body .= $line . "\n";
			};
		}; // foreach

		// Now we will fetch all other more or less useful data out of the headers and store it in separate
		// variables

		$content_type = $boundary = $content_name = $content_encoding = "";
	
		foreach($_headers as $line)
		{
			if (preg_match("/^Content-Type: (.*); (.*)$/i",$line,$matches))
			{
				$headers["Content-Type"] = $matches[1];
				if (preg_match("/boundary=\"(.*)\"/i",$matches[2],$bmatch))
				{
					$headers["Boundary"] = $bmatch[1];
				}
				elseif (preg_match("/boundary=(.*)/i",$matches[2],$bmatch))
				{
					$headers["Boundary"] = $bmatch[1];
				};
	 
				if (preg_match("/name=\"(.*)\"/i",$matches[2],$nmatch))
				{
					$headers["Content-Name"] = $nmatch[1];
				};
				
				if (preg_match("/name=(.*)/i",$matches[2],$nmatch))
				{
					$headers["Content-Name"] = $nmatch[1];
				};
			}
			elseif (preg_match("/^Date: (.*)$/i",$line,$mt))
			{
				$headers["Date"] = $mt[1];
			}
			else
			{
				preg_match("/^(.+?): (.*)$/",$line,$matches);
				$headers[$matches[1]] = $matches[2];
			};
		};

		$result = array(
			"headers" => $headers,
			"body" => $body,
			);

		return $result;
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
	function create_message($args = array())
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

		if ($args["body"])
		{
			if ($description)
			{
				$this->headers["Content-Description"] = $description;
			};

			if ($disp)
			{
				$this->headers["Content-Disposition"] = $disp;	
			};		

			//$this->headers["Content-Type"] = $contenttype;
			$this->headers["Content-Transfer-Encoding"] = $encoding;
			$this->mimeparts[0] = "\n\n" . $emsg . "\n\n";
		}
		else
		{	
			$msg = sprintf("Content-Type: %sContent-Transfer-Encoding: %s%s%s%s",
						$contenttype.CRLF,
						$encoding.CRLF,
						(($description) ? "Content-Description: $description".CRLF:""),
						(($disp) ? "Content-Disposition: $disp".CRLF:""),
						CRLF.$emsg.CRLF);
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
			//$c_ver = "MIME-Version: 1.0".CRLF;
			$this->headers["MIME-Version"] = "1.0";
			$this->headers["Content-Type"] = "multipart/mixed;\n\tboundary=\"$boundary\"";
			$this->headers["Content-Transfer-Encoding"] = "8bit";
			if ($c_desc)
			{
				$this->headers["Content-Description"] = $c_desc;
			};
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
			$msg = $warning.$msg;
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
		$headers = "";

		$email .= $this->build_message();
		$to = $this->headers["To"];
		$subject = $this->headers["Subject"];
		unset($this->headers["To"]);
		unset($this->headers["Subject"]);
		foreach($this->headers as $name => $value)
		{
			$headers .= sprintf("%s: %s\n",$name,$value);
		}
		mail($to,$subject,$email,$headers);
		
	}

};
?>
