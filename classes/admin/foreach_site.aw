<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/foreach_site.aw,v 1.1 2003/06/06 13:23:20 kristo Exp $
// foreach_site.aw - foreach site 

class foreach_site extends class_base
{
	function foreach_site()
	{
		$this->init(array(
			'tpldir' => 'admin/foreach_site',
		));
	}

	function exec($arr)
	{
		$this->read_template("exec.tpl");
		
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_exec")
		));
		return $this->parse();
	}

	function submit_exec($arr)
	{
		extract($arr);

		set_time_limit(0);
		
		$sites = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "get_site_list",
			"method" => "xmlrpc",
			"server" => "register.automatweb.com"
		));

		foreach($sites as $site)
		{
			$url = $site["url"];
			if ($url == "")
			{
				continue;
			}

			if (substr($url, 0, 4) != "http")
			{
				$url = "http://".$url;
			}

			
			echo "<b>exec for site $url <br>\n";

			$url = $url."/".str_replace("/automatweb","",$eurl);

			echo "complete url is $url <br><br>\n\n</b>";
			flush();

			echo "------------------------------------------------------------------------------------------------------------------------------------<Br><br>\n\n";

			preg_match("/^http:\/\/(.*)\//U",$url, $mt);
			$_url = $mt[1];

			$awt = get_instance("aw_test");
			$awt->handshake(array("host" => $_url));

			echo "do send req $url ",substr($url,strlen("http://")+strlen($_url))," <br>";
			$req = $awt->do_send_request(array(
				"host" => $_url, 
				"req" => substr($url,strlen("http://")+strlen($_url))
			));

			echo "result = $req <br>";		

			echo "------------------------------------------------------------------------------------------------------------------------------------<Br><br>\n\n";

		}
		die();
	}
}
?>
