<?php
// dns.aw - various DNS related functionality, whois queries
// $Header
class dns extends aw_template
{
	function dns()
	{
		$this->tpl_init("dns");
		$this->db_init();
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
		$this->quote($args);
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
}
?>
