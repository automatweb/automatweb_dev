<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/foreach_site.aw,v 1.9 2005/12/21 19:50:21 kristo Exp $
// foreach_site.aw - foreach site 

class foreach_site extends class_base
{
	function foreach_site()
	{
		$this->init(array(
			'tpldir' => 'admin/foreach_site',
		));
	}

	/**  
		
		@attrib name=exec params=name default="1"
		
		
		@returns
		
		
		@comment

	**/
	function exec($arr)
	{
		$this->read_template("exec.tpl");
		
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_exec")
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_exec params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_exec($arr)
	{
		extract($arr);

		set_time_limit(14400);
		
		// try remoting
		$sl = get_instance("install/site_list");
		$sl->_do_update_list_cache();

		$sites = $sl->get_site_list();

		$cur_site = $sites[aw_ini_get("site_id")];

		foreach($sites as $site)
		{
			if (1 != $site["site_used"])
			{
				continue;
			};
			$url = $site["url"];
			if ($url == "")
			{
				continue;
			}

			if (1 == $arr["same_code"])
			{
				$cur_code = aw_ini_get("basedir");
				// read remote code
				$inivals = $this->do_orb_method_call(array(
					"class" => "objects",
					"action" => "aw_ini_get_mult",
					"method" => "xmlrpc",
					"server" => "register.automatweb.com",
					"params" => array(
						"vals" => array(
							"basedir"
						)
					)
				));

				if ($inivals["basedir"] != $cur_code || $cur_site["server_id"] != $site["server_id"])
				{
					echo "<font color=red>skipping site $url, because it is using a different code path (remote: $site[server_id]:$inivals[basedir]  vs local: $cur_site[server_id]:$cur_code)</font> <br><br>";
					continue;
				}
			}

			if (substr($url, 0, 4) != "http")
			{
				$url = "http://".$url;
			}

			
			echo "<b>exec for site $url <br />\n";

			$url = $url."/".str_replace("/automatweb","",$eurl);

			echo "complete url is $url <br /><br />\n\n</b>";
			flush();

			echo "------------------------------------------------------------------------------------------------------------------------------------<br /><br />\n\n";

			preg_match("/^http:\/\/(.*)\//U",$url, $mt);
			$_url = $mt[1];

			$awt = get_instance("protocols/file/http");
			$awt->handshake(array("host" => $_url));

			echo "do send req $url ",substr($url,strlen("http://")+strlen($_url))," <br />";
			$req = $awt->do_send_request(array(
				"host" => $_url, 
				"req" => substr($url,strlen("http://")+strlen($_url))
			));

			echo "result = $req <br />";		

			echo "------------------------------------------------------------------------------------------------------------------------------------<br /><br />\n\n";

		}
		die();
	}
}
?>
