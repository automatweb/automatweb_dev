<?php
// dns.aw - various DNS related functionality, whois queries
// $Header
class dns extends aw_template
{
	function dns()
	{
		$this->init("dns");
	}

	function query_form($args = array())
	{
		$this->read_adm_template("enter_whois_query.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("query",array("no_reforb" => 1)),
		));
		return $this->parse();
	}
	
	function query($args = array())
	{
		extract($args);
		if (not($domain))
		{
			return "<b>no domain specified</b>";
		}
		else
		{
			if (strlen(preg_replace("/[\w|\d|\.]/","",$domain)) > 0)
			{
				return "<b>domain contains forbidden characters</b>";
			}
			else
			{
				$fp = popen ("/usr/bin/whois $domain", "r");
				if (not($fp))
				{
					return "<b>Couldn't connect to whois client</b>";
				};
				$data = fread($fp, 8192);
				pclose($fp);
				return "<pre>" . $data . "</pre>";
			};
			
		};
	}
	
	function dns_form($args = array())
	{
		$this->read_adm_template("enter_domain_query.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("dns_query",array("no_reforb" => 1)),
		));
		return $this->parse();
	}
	
	function dns_query($args = array())
	{
		extract($args);
		if (not($domain))
		{
			return "<b>no domain specified</b>";
		}
		else
		{
			if (not(preg_match("/^([\w|\d]*)\.[org|net|com|ee]/",$domain)))
			{
				return "<b>Invalid domain</b><br>";
			}
			else
			{
				// it's a ee domain, query eenets server
				if (preg_match("/\.ee$/",$domain))
				{
					$retval = join("",file("http://www.eenet.ee/info/index.html?otsi=$domain&formaat=pikk"));
					if (preg_match("/tulemusena leiti <b>0<\/b> kirjet./",$retval))
					{
						return "$domain on registreerimata";
					}
					else
					{
						return "$domain on registreeritud";
					};
				}
				else
				{
					$fp = fsockopen("rs.internic.net", 43, &$errno, &$errstr, 10);
					if (not($fp))
					{
						return "Cannot connect to rs.internic.net";
					};
					fputs($fp,"$domain\r\n");
					$buf = "";
					while(!feof($fp)) 
					{
						$buf .= fgets($fp,128);
					};
					fclose($fp);

					if (preg_match("/No match for/i",$buf))
					{
						return "$domain on registreerimata<br>";
					}
					else
					{
						return "$domain on registreeritud<br>";
					};
				};
			};
		};
	}
}
?>
