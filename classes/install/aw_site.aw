<?php

/*

@classinfo syslog_type=ST_SITE relationmgr=yes

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

@property use_existing_database type=checkbox ch_value=1
@caption Kasuta olemasolevat andmebaasi

@property select_db type=select
@caption Vali andmebaas

@property select_parent_folder type=select
@caption Vali kataloog olemasolevas baasis, kuhu alla sait lisatakse

@property use_existing_templates type=checkbox ch_value=1
@caption Vali olemasolevad templated

@property select_tpl_sites type=select multiple=1 size=10
@caption Vali saidid, mille templatesid valida saad

@property select_tpl_folders type=select multiple=1 size=20
@caption Vali templatede kataloogid, mis uude saiti kopeerida

@property select_layout type=relpicker reltype=RELATION_LAYOUT 
@caption Vali uue saidi default layout

@property gen_site type=checkbox ch_value=1
@caption Genereeri sait!

@property site_errmsg type=text
@caption Miks saiti ei saa genereerida

@property site_warnmsg type=text
@caption Hoiatused:

*/

define("RELATION_LAYOUT",1);

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

			case "site_warnmsg":
				if ($this->warning_str == "")
				{
					return PROP_IGNORE;
				}
				$prop['value'] = $this->warning_str;
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
				break;

			case "select_db":
				if ($arr['obj']['meta']['use_existing_database'] == 1)
				{
					if (!is_array($this->server_site_list))
					{
						if ($this->get_server_site_list() == PROP_IGNORE)
						{
							return PROP_IGNORE;
						}
					}

					$sl = $this->server_site_list;

					if (!is_array($sl))
					{
						$this->err_str = "Ei saanud lugeda serveri saitide nimekirja! (vali andmebaas)";
						return PROP_IGNORE;
					}

					$prop['options'] = array("" => "");
					foreach($sl as $sid => $sd)
					{
						$prop['options'][$sd['url']] = $sd['name'];
					}
				}
				else
				{
					return PROP_IGNORE;
				}
				break;

			case "select_parent_folder":
				if ($arr['obj']['meta']['use_existing_database'] == 1 && $arr['obj']['meta']['select_db'] != "")
				{
					// get list of folders for the site
					$serv = str_replace("http://","",$arr['obj']['meta']['select_db']);
					$flds = $this->do_orb_method_call(array(
						"class" => "objects",
						"action" => "get_list",
						"params" => array(
							"rootobj" => 0
						),
						"method" => "xmlrpc",
						"server" => $serv,
						"no_errors" => true
					));
					if (!is_array($flds))
					{
						$this->err_str = "Ei saanud lugeda kataloogide nimekirja saidist $serv!";
					}
					$prop['options'] = $flds;
				}
				else
				{
					return PROP_IGNORE;
				}
				break;

			case "select_tpl_sites":
				if ($arr['obj']['meta']['use_existing_templates'] != 1)
				{
					return PROP_IGNORE;
				}

				if (!is_array($this->server_site_list))
				{
					if ($this->get_server_site_list() == PROP_IGNORE)
					{
						return PROP_IGNORE;
					}
				}

				$sl = $this->server_site_list;

				if (!is_array($sl))
				{
					$this->err_str = "Ei saanud lugeda serveri saitide nimekirja! (vali templatede sait)";
					return PROP_IGNORE;
				}

				$prop['options'] = array("" => "");
				foreach($sl as $sid => $sd)
				{
					$prop['options'][$sd['url']] = $sd['name'];
				}
				break;

			case "select_tpl_folders":
				$isar = is_array($arr['obj']['meta']['select_tpl_sites']) && count($arr['obj']['meta']['select_tpl_sites']) > 0;
				if ($arr['obj']['meta']['use_existing_templates'] != 1 || !$isar)
				{
					return PROP_IGNORE;
				}

				// now get list for all selected sites
				$fl = array();
				foreach($arr['obj']['meta']['select_tpl_sites'] as $sn)
				{
					$sn = str_replace("http://","",$sn);


					$_t = $this->do_orb_method_call(array(
						"class" => "templatemgr",
						"action" => "get_template_folder_list",
						"method" => "xmlrpc",
						"server" => $sn,
						"no_errors" => true
					));


					if (is_array($_t))
					{
						foreach($_t as $folder)
						{
							// make folder names more prettier for the user
							$prop['options'][$folder] = $folder;
						}
					}
				}
				break;

			case "select_layout":
				if (!($arr['obj']['meta']['use_existing_database'] && $arr['obj']['meta']['select_db'] == "http://aw.struktuur.ee"))
				{
					return PROP_IGNORE;
				}
				break;
		}
		return PROP_OK;
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($ob['meta']['gen_site'])
		{
			$site = $this->get_site_def($id);
		
			if (!$this->is_site_ok($site))
			{
				$this->raise_error(ERR_SITE_CFG, "errir in site config: $this->err_str",true,false);
			}
				
			//echo "generating site, params: <Br>";
			//echo "site data : ".dbg::dump($site)." <br>";
			echo "Loon saiti! \n<Br>HOIATUS! Saidi loomine v&otilde;tab paar minutit aega!<br><br>\n";
			flush();

			$ini_opts = array();

			aw_global_set("__is_install",1);

			// now, do the actual thing. 

			// start logger
			$log = get_instance("install/aw_site_gen_log");
			$log->start_log(array(
				"parent" => $ob['parent'],
				"name" => "Saidi ".$site["url"]." loomise log"
			));

			// first, create site folders
			$this->create_site_folders($site, $ini_opts, &$log);

			// create site name in nameserver if it does not exist
			$this->create_site_name($site, $ini_opts, &$log);

			// get new site_id for site
			$this->get_site_id($site, $ini_opts, &$log);

			// now, create database
			$this->create_site_database($site, $ini_opts, &$log);

			// now let each class that is registered handle the install process
			// each class gets an instance of a dummy class, where it can set 
			// properties and then those properties will get written to the ini file
			$this->do_init_classes($site, $ini_opts, &$log);

			// now write options to the ini file
			$this->create_ini_file($site, $ini_opts, &$log);

			$log->finish_log();

			// now restart webserver
			//echo "restarting webserver ... <br>\n";
			aw_global_set("__is_install", 0);
			flush();
			touch("/tmp/ap_reboot");
			echo "Valmis! sait on kasutatav 30 sekundi p&auml;rast!<br>\n<a href='".$this->mk_my_orb("change", array(
				"id" => $log->cur_log_id,
				"group" => "view"
				),"aw_site_gen_log")."'>Saidi loomise logi</a>\n";
			flush();
			die();
		}
	}

	function _do_add_folder($fld, &$log)
	{
		$si = get_instance("install/su_exec");
		$si->add_cmd("mkdir ".$fld);
		$si->add_cmd("chmod 777 ".$fld);
		$si->exec();
		$stat = "OK";
		if (!is_dir($fld))
		{
			$stat = "Kataloogi ei ole!";
		}
		$log->add_line(array(
			"uid" => "System",
			"msg" => "Lisas kataloogi",
			"comment" => $fld,
			"result" => $stat
		));
	}

	function create_site_folders($site, &$ini_opts, &$log)
	{
		//echo "Loon katalooge .... <br>\n";
		//flush();

		// generate the script that creates the folders for the site

		// create the needed folders
		$this->_do_add_folder($site["docroot"], &$log);
		$ini_opts['site_basedir'] = $site['docroot'];

		$this->_do_add_folder($site['docroot']."/archive", &$log);

		$this->_do_add_folder($site['docroot']."/files", &$log);

		$this->_do_add_folder($site['docroot']."/pagecache", &$log);
		$ini_opts['cache.page_cache'] = "\${site_basedir}/pagecache";

		$this->_do_add_folder($site['docroot']."/public", &$log);

		$this->_do_add_folder($site['docroot']."/templates", &$log);
		$ini_opts['tpldir'] = "\${site_basedir}/templates";

		// now copy base templates to the just-created templates folder
		$si = get_instance("install/su_exec");
		$si->add_cmd("copy -r ".$this->cfg["basedir"]."/install/site_template/templates/* ".$site['docroot']."/templates/");
		$si->add_cmd("find ".$site['docroot']."/templates/ -type f -exec chmod 666 {} \;");
		$si->exec();
		$log->add_line(array(
			"uid" => "System",
			"msg" => "Kopeeris default kujunduse",
			"comment" => "",
			"result" => "OK"
		));

		

		// now, if the user said, that we gots to copy some foldres from other sites, then do that as well
		if ($site['site_obj']['use_existing_templates'] == 1)
		{
			$this->do_copy_existing_templates($site);
		}

		$this->_do_add_folder($site['logroot'], &$log);

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
			
		$si = get_instance("install/su_exec");
		$si->add_cmd("copy $vhost_file_name ".$site["vhost_file"]);
		$si->exec();
		$stat = "OK";
		if (!file_exists($site['vhost_file']))
		{
			$stat = "Faili ei ole!";
		}
		$log->add_line(array(
			"uid" => "System",
			"msg" => "Tegi virtualhost konfi!",
			"comment" => $site["vhost_file"],
			"result" => $stat
		));

		// create the link for the automatweb folder
		$si = get_instance("install/su_exec");
		$si->add_cmd("ln -s ".($this->cfg["basedir"]."/automatweb")." ".$site["docroot"]."/public/automatweb");
		$si->exec();
		$stat = "OK";
		if (!is_link($site["docroot"]."/public/automatweb"))
		{
			$stat = "Linki ei ole!";
		}
		$log->add_line(array(
			"uid" => "System",
			"msg" => "Linkis automatweb kataloogi",
			"comment" => $site["docroot"]."/public/automatweb",
			"result" => $stat
		));

		// copy the default code files to public folder
		// files: index.aw . login.aw , orb.aw, reforb.aw, site.aw, site_header.aw, site_footer.aw
		$si = get_instance("install/su_exec");
		$si->add_cmd("copy -r ".$this->cfg["basedir"]."/install/site_template/public/* ".$site["docroot"]."/public/");
		$si->add_cmd("chmod 666 $site[docroot]/public/*aw");
		$si->add_cmd("chmod 777 $site[docroot]/public/css");
		$si->add_cmd("chmod 777 $site[docroot]/public/img");
		$si->add_cmd("chmod 666 $site[docroot]/public/img/*");
		$si->add_cmd("chmod 666 $site[docroot]/public/css/*");
		$si->exec();
		$log->add_line(array(
			"uid" => "System",
			"msg" => "Kopeeris saidi koodi",
			"comment" => "",
			"result" => "OK"
		));

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

		$si = get_instance("install/su_exec");
		$si->add_cmd("copy $constaw_file_name ".$site["docroot"]."/public/const.aw");
		$si->exec();
		$stat = "OK";
		if (!file_exists($site["docroot"]."/public/const.aw"))
		{
			$stat = "Faili ei ole!";
		}
		$log->add_line(array(
			"uid" => "System",
			"msg" => "Tegi const.aw faili",
			"comment" => $site["docroot"]."/public/const.aw",
			"result" => $stat
		));
	}

	function do_init_classes($site, &$ini_opts, &$log)
	{
		// ok, fuck it, we fake the site_id so that the objects all get the correct site_id
		$osid = aw_ini_get("site_id");
		aw_global_set("real_site_id", $osid);
		$GLOBALS["cfg"]["__default"]["site_id"] = $ini_opts["site_id"];

		// connect to the site database
		$dbi = get_instance("class_base");
		$dbi->db_connect(array(
			'driver' => 'mysql',
			'server' => $ini_opts['db.host'],
			'base' => $ini_opts['db.base'],
			'username' => $ini_opts['db.user'],
			'password' => $ini_opts['db.pass']
		));

		// right. now we must somehow assume the identity of the new site. 
		// quiestion is, how the hell do we do that?
		// ok, what the hell, right now just update objects.site_id to have the new id after doing stuff
		// createdby uids will still be incorrect, but fuck that

		$clss = aw_ini_get("install.init_classes");
		foreach($clss as $class)
		{
			$inst = get_instance($class);
			if (method_exists($inst, "on_site_init"))
			{
				//echo "call on_site_init for class $class <br>\n";
				//flush();
				$inst->on_site_init($dbi, $site, $ini_opts, $log);
			}
		}

		$dbi->db_query("UPDATE objects SET lang_id = 1");

		$GLOBALS["cfg"]["__default"]["site_id"] = $osid;
	}

	function create_site_name($site, &$ini_opts, &$log)
	{
		//echo "Muudan nimeserveri konfiguratsiooni...<br>\n";
		//flush();

		$mgr_server = $this->get_dns_manager_for_url($site["url"]);
		//echo "mgr_server = $mgr_server <Br>";
		if ($mgr_server !== false)
		{
			//echo "doing rpc call to change $site[url] 's ip to ",aw_ini_get("install.default_ip")," <br>";
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
		$ini_opts["baseurl"] = "http://".$site['url'];
		$ini_opts["stitle"] = $site['url'];
		$log->add_line(array(
			"uid" => "System",
			"msg" => "Konfigureeris nimeserveri",
			"comment" => aw_ini_get("install.default_ip"),
			"result" => "OK"
		));
	}

	function create_site_database($site, &$ini_opts, &$log)
	{
		if ($site['site_obj']['use_existing_database'])
		{
			//echo "reading database access data from the existing site<br>\n";
			flush();

			$db_dat = $this->do_orb_method_call(array(
				"class" => "objects",
				"action" => "get_db_pwd",
				"method" => "xmlrpc",
				"server" => str_replace("http://","",$site['site_obj']['select_db']),
				"no_errors" => 1
			));

			$ini_opts['db.user'] = $db_dat['user'];
			$ini_opts['db.host'] = $db_dat['host'];
			$ini_opts['db.base'] = $db_dat['base'];
			$ini_opts['db.pass'] = $db_dat['pass'];
			//echo "got db inf = <pre>", var_dump($db_dat),"</pre> <br>";
		}
		else
		{
			//echo "Loon andmebaasi...<br>\n";
			flush();
			//echo "creating database .. <br>";
			$dbi = get_instance("class_base");
			$dbi->db_connect(array(
				'driver' => 'mysql',
				'server' => aw_ini_get('install.mysql_host'),
				'base' => 'mysql',
				'username' => aw_ini_get('install.mysql_user'),
				'password' => aw_ini_get('install.mysql_pass')
			));

			// create database
			$q = "CREATE DATABASE $site[db_name]";
			//echo "exec $q <br>";
			$dbi->db_query($q);

			// grant permission
			$q = "
				GRANT ALL PRIVILEGES 
					ON $site[db_name].* 
					TO $site[db_user]@".aw_ini_get("install.mysql_client")." 
					IDENTIFIED BY '$site[db_pwd]'
			";
			//echo "exec $q <Br>";
			$dbi->db_query($q);

			$ini_opts['db.user'] = $site['db_user'];
			$ini_opts['db.host'] = aw_ini_get("install.mysql_host");
			$ini_opts['db.base'] = $site['db_name'];
			$ini_opts['db.pass'] = $site['db_pwd'];
			$log->add_line(array(
				"uid" => aw_ini_get('install.mysql_user'),
				"msg" => "L&otilde;i saidi andmebaasi",
				"comment" => $site["db_name"],
				"result" => "OK"
			));
		}
	}


	function is_site_ok($site)
	{
		// check if the site base folder exists
		if (is_dir($site['docroot']))
		{
			$this->err_str = "Saidi baaskataloog on juba olemas! ($site[docroot]) ";
			return false;
		}

		// check if the log folder exists
		if (is_dir($site['logroot']))
		{
			$this->err_str = "Saidi logide kataloog on juba olemas! ($site[logroot]) ";
			return false;
		}

		// check if the site vhost file exists
		if (file_exists($site['vhost_file']))
		{
			$this->err_str = "Saidi apache konfiguratsioon on juba olemas! ($site[vhost_file]) ";
			return false;
		}

		// if we selected that we want to use templates, then make sure we have selected some
		if ($site['site_obj']['use_existing_templates'])
		{
			if (!is_array($site['site_obj']['select_tpl_sites']) || count($site['site_obj']['select_tpl_sites']) < 1)
			{
				$this->err_str = "Saidid, kust templatesid kopeerida on valimata! ";
				return false;
			}
			else
			if (!is_array($site['site_obj']['select_tpl_folders']) || count($site['site_obj']['select_tpl_folders']) < 1)
			{
				$this->err_str = "Kataloogid, kust templatesid kopeerida on valimata! ";
				return false;
			}
		}

		// if we are writing to an existing database, check if we have selected all the necessary stuff
		// and then if we can access the database (if the code version is big enough to support xmlrpc)
		if ($site['site_obj']['use_existing_database'])
		{
			if ($site['site_obj']['select_db'] == "")
			{
				$this->err_str = "Kasutatav andmebaas on valimata!";
				return false;
			}

			if (!$site['site_obj']['select_parent_folder'])
			{
				$this->err_str = "Uue saidi root kataloog olemasolevas andmebaasis on valimata!";
				return false;
			}

			// try to fetch db login data
			$db_dat = $this->do_orb_method_call(array(
				"class" => "objects",
				"action" => "get_db_pwd",
				"method" => "xmlrpc",
				"server" => str_replace("http://","",$site['site_obj']['select_db']),
				"no_errors" => 1
			));
			if (!is_array($db_dat))
			{
				$this->err_str = "Sait on liiga vana koodiversiooniga et selle p&otilde;hjal uut luua!";
				return false;
			}
		}
		else
		{
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
				if ($db['name'] == $site['db_name'])
				{
					$found = true;
					break;
				}
			}

			if ($found)
			{
				$this->err_str = "Saidi andmebaas on juba olemas! ($site[db_name]) ";
				return false;
			}
		}
		

		// check if we can manage the nameserver for the url 
		// if we can not, then check if the domain name exists
		if (!$this->is_managed_nameserver($site['url']))
		{
			$ip = gethostbyname($site['url']);
			if ($ip == $site['url'])
			{
				$this->err_str = "Saidi domeeni nimeserver ei ole Automatwebi poolt hallatav ja saidi domeeni pole registreeritud! ($site[url]) ";
				return false;
			}
			if ($ip != aw_ini_get("install.default_ip"))
			{
				$this->err_str = "Saidi domeeni nimeserver ei ole Automatwebi poolt hallatav ja saidi domeen viitab valele IP aadressile! (vajalik = ".aw_ini_get("install.default_ip")." domeeni ip = $ip)";
				return false;
			}
		}
		else
		{
			if (($_ip = gethostbyname($site['url'])) != $site['url'])
			{
				$this->warning_str = "Saidi domeen on juba registreeritud! (domeen = $site[url] ip = $_ip) Kui selle domeeni pealt k&auml;ib m&otilde;ni teine sait, siis seda ei saa p&auml;rast uue saidi loomist kasutada!";
			}
		}

		// check if a site by that url is already defined in the site db
		$site_id = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "get_site_id_by_url",
			"params" => array(
				'url' => $site['url']
			),
			"method" => "xmlrpc",
			"server" => "register.automatweb.com"
		));
		if ($site_id)
		{
			$this->err_str = "Saitide registris on juba sait, mille url on $site[url] !";
			return false;
		}

		// check if a server is defined in the server db
		$server_id = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "get_server_id_by_ip",
			"params" => array(
				"ip" => aw_ini_get("install.default_ip"),
			),
			"method" => "xmlrpc",
			"server" => "register.automatweb.com"
		));
		if (!$server_id)
		{
			$this->err_str = "IP aadressi ".aw_ini_get("install.default_ip")." jaoks pole serverite registris kirjet!";
			return false;
		}

		// check if we got root access
		$sue = get_instance("install/su_exec");
		if (!$sue->is_ok())
		{
			$this->err_str = "Ei saa root kasutaja &otilde;iguseid kasutada!";
			return false;
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
		$ob['meta']['site_url'] = str_replace("http://","",$ob['meta']['site_url']);
		$ob['meta']['site_url'] = str_replace("/","",$ob['meta']['site_url']);

		$site = array();
		$site['url'] = $ob['meta']['site_url'];
		$site['docroot'] = aw_ini_get('install.docroot').$ob['meta']['site_url'];
		$site['logroot'] = aw_ini_get('install.logroot').$ob['meta']['site_url'];
		$site['vhost_file'] = aw_ini_get('install.vhost_folder').$ob['meta']['site_url'];
		$site['server_ip'] = aw_ini_get('install.default_ip');
		$site['admin_folder'] = aw_ini_get('install.admin_folder');
		$site['db_name'] = str_replace(".","",$ob['meta']['site_url']);
		$site['db_user'] = substr(md5(str_replace(".","",$ob['meta']['site_url'])), 0, 15);
		$site['db_pwd'] = generate_password();
		$site['site_obj'] = $ob['meta'];
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
	
	function create_ini_file($site, &$ini_opts, &$log)
	{
		//echo "ini_opts = <pre>", var_dump($ini_opts),"</pre> <br>";
		// create temp ini file, then use su_exec to copy it to the correct place
		$tmpnam = tempnam(aw_ini_get("server.tmpdir"),"aw_install_ini");

		$fc = join("\n", map2('%s = %s', $ini_opts));
		$this->put_file(array(
			'file' => $tmpnam,
			"content" => $fc
		));

		$sue = get_instance("install/su_exec");
		$sue->add_cmd("copy $tmpnam ".$site['docroot']."/aw.ini");
		$sue->add_cmd("chmod 666 $site[docroot]/aw.ini");
		$sue->exec();
		$status = "OK";
		if (!file_exists($site['docroot']."/aw.ini"))
		{
			$status = "Faili ei ole!";
		}
		$log->add_line(array(
			"uid" => "System",
			"msg" => "Tegi ini faili",
			"comment" => $site['docroot']."/aw.ini",
			"result" => $status
		));
	}

	function get_site_id($site, &$ini_opts, &$log)
	{		
		$server_id = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "get_server_id_by_ip",
			"params" => array(
				"ip" => aw_ini_get("install.default_ip"),
			),
			"method" => "xmlrpc",
			"server" => "register.automatweb.com"
		));
		//echo "got server_id = $server_id <br>";

		$site_id = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "update_site",
			"params" => array(
				"name" => $site['url'],
				"url" => "http://".$site['url'],
				"server_id" => $server_id,
				"ip" => aw_ini_get("install.default_ip"),
				"site_used" => true,
				"code_branch" => "HEAD",
			),
			"method" => "xmlrpc",
			"server" => "register.automatweb.com"
		));
		//echo "got site id $site_id <br>";
		$ini_opts["site_id"] = $site_id;
		$log->add_line(array(
			"uid" => aw_global_get("uid"),
			"msg" => "K&uuml;sis uue saidi id",
			"comment" => $site_id,
			"result" => "OK"
		));
	}

	function get_server_site_list()
	{
		// get current server id
		$server_id = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "get_server_id_by_ip", 
			"params" => array(
				"ip" => aw_ini_get("install.default_ip")
			),
			"method" => "xmlrpc",
			"server" => "register.automatweb.com",
			"no_errors" => true
		));
		if (!$server_id)
		{
			$this->err_str = "Ei saanud lugeda serveri id'd! (vali andmebaas)";
			return PROP_IGNORE;
		}

		// ok, here we must figoure out a list of sites in that server 
		$this->server_site_list = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "get_site_list", 
			"params" => array(
				"server_id" => $server_id
			),
			"method" => "xmlrpc",
			"server" => "register.automatweb.com",
			"no_errors" => true
		));
	}

	function do_copy_existing_templates($site)
	{
		// get list of all folders
		$fmap = array();
		foreach($site['site_obj']['select_tpl_sites'] as $sn)
		{
			$sn = str_replace("http://","",$sn);
			$_t = $this->do_orb_method_call(array(
				"class" => "objects",
				"action" => "aw_ini_get_mult",
				"method" => "xmlrpc",
				"server" => $sn,
				"params" => array(
					"vals" => array(
						"site_basedir"
					)
				),
				"no_errors" => true
			));
			if (is_array($_t))
			{
				foreach($_t as $base_folder)
				{
					$fmap[] = $base_folder;
				}
			}
		}
		$sue = get_instance("install/su_exec");
		foreach($site['site_obj']['select_tpl_folders'] as $from_fld)
		{
			$to_fld = $from_fld;
			foreach($fmap as $base)
			{
				$to_fld = str_replace($base, "", $to_fld);
			}

			$to_fld = $site['docroot']."/".$to_fld;

			$sue->add_cmd("mkdir $to_fld");
			$sue->add_cmd("copy $from_fld/*tpl $to_fld/");
			//echo "added cmd mkdir $to_fld <br>\n";
			//echo "addes cmd copy $from_fld/*tpl $to_fld/ <br>\n";
			flush();
		}
		$sue->exec();
	}

	function callback_get_rel_types()
	{
		return array(
			RELATION_LAYOUT => "saidi layout"
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELATION_LAYOUT)
		{
			return array(CL_LAYOUT);
		}
	}
}
?>
