<?php

/*

@classinfo syslog_type=ST_SITE

@groupinfo general caption=Üldine

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property site_url type=textbox
@caption Saidi url

@property default_user type=textbox
@caption Default kasutaja

@property default_user_pwd type=password
@caption Default kasutaja parool

@property gen_site type=checkbox ch_value=1
@caption Genereeri sait!

@property site_errmsg type=text
@caption Miks saiti ei saa genereerida

*/

class aw_site extends class_base
{
	function aw_site()
	{
		$this->init(array(
			'tpldir' => 'install/aw_site',
			'clid' => CL_SITE
		));
	}

	function get_property(&$arr)
	{
		$prop =&$arr['prop'];
		switch($prop['name'])
		{
			case "comment":
			case "alias":
			case "status":
			case "jrk":
				return PROP_IGNORE;
				break;

			case "site_errmsg":
				if ($this->err_str == "")
				{
					return PROP_IGNORE;
				}
				$prop['value'] = $this->err_str;
				break;
			case "gen_site":
				if ($arr['obj']['oid'])
				{
					$site = $this->get_site_def($arr['obj']['oid']);
					if (!$this->is_site_ok($site))
					{
						return PROP_IGNORE;
					}
				}
	
				if ($arr['obj']['meta']['site_url'] == '')
				{
					$this->err_str = "Saidi url on m&auml;&auml;ramata!";
					return PROP_IGNORE;
				}
		}
		return PROP_OK;
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($ob['meta']['gen_site'])
		{
			// unset the gen flag
			unset($ob['meta']['gen_site']);
			$this->upd_object(array(
				'oid' => $id,
				'metadata' => $ob['meta']
			));

			$site = $this->get_site_def($id);
		
			if (!$this->is_site_ok($site))
			{
				die("ERROR in site config: $this->err_str <Br>\n");
			}
				
			echo "generating site, params: <Br>";
			echo "site data : ".dbg::dump($site)." <br>";


			// now, do the actual thing. 

			// first, create site folders
			$this->create_site_folders($site);

			// create site name in nameserver if it does not exist
			$this->create_site_name($site);

			// now, create database
			$this->create_site_database($site);

			// now let each class that is registered handle the install process
			// each class gets an instance of a dummy class, where it can set 
			// properties and then those properties will get written to the ini file
			$this->do_init_classes($site);
		}
	}

	function create_site_folders($site)
	{
		// generate the script that creates the folders for the site
		$si = get_instance("install/su_exec");

		// create the needed folders
		$si->add_cmd("mkdir ".$site['docroot']);
		$si->add_cmd("chmod 775 ".$site['docroot']);

		$si->add_cmd("mkdir ".$site['docroot']."/archive");
		$si->add_cmd("chmod 777 ".$site['docroot']."/archive");

		$si->add_cmd("mkdir ".$site['docroot']."/files");
		$si->add_cmd("chmod 777 ".$site['docroot']."/files");

		$si->add_cmd("mkdir ".$site['docroot']."/pagecache");
		$si->add_cmd("chmod 777 ".$site['docroot']."/pagecache");

		$si->add_cmd("mkdir ".$site['docroot']."/public");
		$si->add_cmd("chmod 775 ".$site['docroot']."/public");

		$si->add_cmd("mkdir ".$site['docroot']."/templates");
		$si->add_cmd("chmod 775 ".$site['docroot']."/templates");

		// create apache vhost file
		$vhost_template = $this->get_file(array("file" => $this->cfg["tpldir"] . "/apache_conf/vhost.conf"));
		$vars = array(
			"date" => $this->time2date(time(),2),
			"servername" => $site['url'],
			"docroot" => $site['docroot'],
			"logroot" => $site['logroot'],
			"ip" => aw_ini_get("install.default_ip"),
		);
		$vhost_conf = localparse($vhost_template,$vars);
		$vhost_file_name = aw_ini_get("server.tmpdir")."/aw_install_vhost.conf";
		$this->put_file(array(
			"file" => $vhost_file_name,
			"content" => $vhost_conf
		));
			
		$si->add_cmd("copy $vhost_file_name ".$site["vhost_file"]);

		// create the link for the automatweb folder
		$si->add_cmd("ln -s ".($this->cfg["basedir"]."/automatweb")." ".$site["docroot"]."/public/automatweb");

		// copy the default code files to public folder
		// files: index.aw . login.aw , orb.aw, reforb.aw, site.aw, site_header.aw, site_footer.aw
		$si->add_cmd("copy ".$this->cfg["basedir"]."/install/site_template/public/*aw ".$site["docroot"]."/public/");

		// now, make the const.aw file from the template
		$constaw_template = $this->get_file(array("file" => $this->cfg["tpldir"] . "/apache_conf/const.aw.tpl"));
		$vars = array(
			"aw_dir" => $this->cfg["basedir"],
			"site_dir" => $site["docroot"]
		);
		$constaw = localparse($constaw_template,$vars);
		$constaw_file_name = aw_ini_get("server.tmpdir")."/aw_install_const.aw";
		$this->put_file(array(
			"file" => $constaw_file_name,
			"content" => $constaw
		));
		$si->add_cmd("copy $constaw_file_name ".$site["docroot"]."/public/const.aw");
			
		// right, now exec the script 
		echo "execd = <pre>".$si->exec()."</pre> <br>";
	}

	function do_init_classes($site)
	{
		
	}

	function create_site_name($site)
	{
		$mgr_server = $this->get_dns_manager_for_url($site["url"]);
		if ($mgr_server !== false)
		{
			$this->do_orb_method_call(array(
				"class" => "dns_server_manager",
				"action" => "add_or_update_site",
				"params" => array(
					"domain" => $site["url"],
					"ip" => aw_ini_get("install.default_ip")
				),
				"method" => "xmlrpc",
				"server" => $mgr_server
			));
		}
	}

	function create_site_database($site)
	{
		// create database
		// grant permission
		// import db schema
	}

	function is_site_ok($site)
	{
		// check if the site base folder exists
		if (is_dir($site['docroot']))
		{
			$this->err_str = "Site base folder exists, will not overwrite! ($site[docroot]) ";
			return false;
		}

		// check if the log folder exists
		if (is_dir($site['logroot']))
		{
			$this->err_str = "Site log folder exists, will not overwrite! ($site[logroot]) ";
			return false;
		}

		// check if the site vhost file exists
		if (file_exists($site['vhost_file']))
		{
			$this->err_str = "Site vhost file exists, will not overwrite! ($site[vhost_file]) ";
			return false;
		}

		// check if the database exists
		$dbi = get_instance("class_base");
		$dbi->db_connect(array(
			'driver' => 'mysql',
			'server' => aw_ini_get('install.mysql_host'),
			'base' => 'mysql',
			'username' => aw_ini_get('install.mysql_user'),
			'password' => aw_ini_get('install.mysql_pass')
		));
		$dbi->db_list_databases();

		$found = false;
		while ($db = $dbi->db_next_database())
		{
			if ($db == $site['db_name'])
			{
				$found = true;
				break;
			}
		}

		if ($found)
		{
			$this->err_str = "Site database exists, will not overwrite! ($site[db_name]) ";
			return false;
		}
		

		// check if we can manage the nameserver for the url 
		// if we can not, then check if the domain name exists
		if (!$this->is_managed_nameserver($site['url']))
		{
			$ip = gethostbyname($site['url']);
			if ($ip == $site['url'])
			{
				$this->err_str = "Site domain is hosted by an unmanaged nameserver and the domain name does not exist! ($site[url]) ";
				return false;
			}
			if ($ip != aw_ini_get("install.default_ip"))
			{
				$this->err_str = "Site domain refers to a different server than this one (cururent server = ".aw_ini_get("install.default_ip")." ip for domain = $ip)";
				return false;
			}
		}
		else
		{
			if (gethostbyname($site['url']) != $site['url'])
			{
				$this->err_str = "Site domain already exists! ($site[url]) ";
				return false;
			}
		}
		return true;
	}

	function is_managed_nameserver($url)
	{
		if ($this->get_dns_manager_for_url($url) === false)
		{
			return false;
		}
		return true;
	}

	function get_site_def($id)
	{
		$ob = $this->get_object($id);	
		$site = array();
		$site['url'] = $ob['meta']['site_url'];
		$site['docroot'] = aw_ini_get('install.docroot').$ob['meta']['site_url'];
		$site['logroot'] = aw_ini_get('install.logroot').$ob['meta']['site_url'];
		$site['vhost_file'] = aw_ini_get('install.vhost_folder').$ob['meta']['site_url'];
		$site['server_ip'] = aw_ini_get('install.default_ip');
		$site['admin_folder'] = aw_ini_get('install.admin_folder');
		$site['db_name'] = str_replace(".","",$ob['meta']['site_url']);
		$site['db_pwd'] = generate_password();
		return $site;
	}

	function get_dns_manager_for_url($url)
	{
		$dns = get_instance("dns");
		$ns = $dns->get_record_NS(array(
			"domain" => $url,
		));
		// try to send an xmlrpc call to all returned nameservers
		// if it succeeds, then assume that we can manage the nameserver
		foreach($ns as $server)
		{
			$res = $this->do_orb_method_call(array(
				"class" => "dns_server_manager",
				"action" => "can_manage_server",
				"params" => array("server" => $server),
				"method" => "xmlrpc",
				"server" => $server,
				"no_errors" => true
			));
			if ($res == true)
			{
				return $server;
			}
		}
		return false;
	}
}
?>
