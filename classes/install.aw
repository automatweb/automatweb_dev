<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/install.aw,v 2.4 2002/11/07 10:52:22 kristo Exp $
// install.aw - used for creating and configuring new sites
class install extends aw_template
{
	////
	// !Konstruktor
	function install()
	{
		$this->init("install");
	}

	function add_site($args = array())
	{
		extract($args);
		$this->read_adm_template("add_site.tpl");
		$this->vars($args);
		$ok = false;
		$this->vars(array(
			"ServerName" => $args["ServerName"],
			"ServerAddr" => ($args["ServerAddr"]) ? $args["ServerAddr"] : $this->cfg["default_ip"],
			"docroot" => $this->cfg["docroot"],
			"dbname" => $args["dbname"],
			"dbhost" => ($args["dbhost"]) ? $args["dbhost"] : $this->cfg["mysql_host"],
			"dbuser" => $args["dbuser"],
			"dbpass" => $args["dbpass"],
			"SITE_ID" => $args["SITE_ID"],
			"default_user" => ($args["default_user"]) ? $args["default_user"] : $this->cfg["default_user"],
			"default_pass" => ($args["default_pass"]) ? $args["default_pass"] : $this->cfg["default_pass"],
			"reforb" => $this->mk_reforb("add_site",array("no_reforb" => 1)),
		));

		$ok = false;
		$message = array();

		$dbhost = $this->cfg["mysql_host"];

		if ($dbname && $dbhost && $dbuser && $dbpass && $default_user && $default_pass &&
			$ServerName && $folder)
		{
			$ok = true;
		}
		else
		{
			$message[] = "kõik väljad peavad olema täidetud!";
			$ok = false;
		};

		if (sprintf("%d",$SITE_ID) == 0)
		{
			$message[] = "SITE_ID peab olema number!";
			$ok = false;
		};
		

		if ($dbname && preg_match("/\W/",$dbname))
		{
			$message[] = "dbname sisaldab keelatud märke!";
			$ok = false;
		};

		// yep, those checks are a bit anal right now. we should allow a few other
		// safe characters
		if ($dbname && preg_match("/\W/",$dbuser))
		{
			$message[] = "dbuser sisaldab keelatud märke!";
			$ok = false;
		};

		if ($dbpass && preg_match("/\W/",$dbpass))
		{
			$message[] = "dbpass sisaldab keelatud märke!";
			$ok = false;
		};

		if ($ServerName && not(preg_match("/^\w*\.\w*\.\w*$/",$ServerName)))
		{
			$message[] = "ServerName ei ole kujul aa.bb.cc või sisaldab keelatud märke!";
			$ok = false;
		};

		if ($folder && preg_match("/\W/",$folder))
		{
			$message[] = "Folderi nimi sisaldab keelatud märke!";
			$ok = false;
		};

		if (is_array($message) && not($ok))
		{
			$this->vars(array(
				"message" => join("<br>",$message),
			));
		};

		if ($ok)
		{
			$vhost_template = $this->get_file(array("file" => $this->cfg["tpldir"] . "/apache_conf/vhost.conf"));
			$vars = array(
				"date" => $this->time2date(time(),2),
				"servername" => $ServerName,
				"docroot" => $this->cfg["docroot"] . $folder,
				"logroot" => $this->cfg["logroot"] . $ServerName,
				"ip" => $this->cfg["default_ip"],
			);

			$vhost_conf = localparse($vhost_template,$vars);

			$fp = popen($this->cfg["basedir"]."/scripts/install/install","w");
			if (not($fp))
			{
				print "couldn't fork install script, check the permissions!";
				return false;
			};

			$basedir = $this->cfg["docroot"] . $folder;
			$vhost_loc = $this->cfg["vhost_folder"] . $ServerName;
			$logroot = $this->cfg["logroot"] . $ServerName;
			$admin_folder = $this->cfg["admin_folder"];
			$mysql_host = $this->cfg["mysql_host"];
			$mysql_user = $this->cfg["mysql_user"];
			$mysql_pass = $this->cfg["mysql_pass"];
			$mysql_client = $this->cfg["mysql_client"];

			$data = "";
			$data .= "##vhost-start##\n";
			$data .= $vhost_conf;
			$data .= "##vhost-end##\n";
			$data .= "basedir=$basedir\n";
			$data .= "logroot=$logroot\n";
			$data .= "ServerName=$ServerName\n";
			$data .= "admin_folder=$admin_folder\n";
			$data .= "vhost_loc=$vhost_loc\n";
			$data .= "type=default\n";
			$data .= "remote_host=" . aw_global_get("REMOTE_ADDR") . "\n";
			$data .= "mysql_host=$mysql_host\n";
			$data .= "mysql_user=$mysql_user\n";
			$data .= "mysql_pass=$mysql_pass\n";
			$data .= "mysql_client=$mysql_client\n";
			$data .= "dbname=$dbname\n";
			$data .= "dbuser=$dbuser\n";
			$data .= "dbpass=$dbpass\n";

			print "sending:<pre>";
			print $data;
			print "</pre>";
			flush();

			fputs($fp,$data);
			pclose($fp);
			print "blergh!";

			$ini_template = $this->get_file(array("file" => $this->cfg["tpldir"] . "/apache_conf/awini.tpl"));
			print "<pre>";
			print_r($ini_template);
			print "</pre>";
			$vars = array(
				"basedir" => $basedir,
				"baseurl" => "http://" . $ServerName,
				"db_user" => $dbuser,
				"db_pass" => $dbpass,
				"db_host" => $mysql_host,
				"db_base" => $dbname,
				"site_id" => $SITE_ID,
				"default_user" => $default_user,
				"default_pass" => $default_pass,
			);

			$config = localparse($ini_template,$vars);

			$this->put_file(array(
				"file" => $basedir . "/aw.ini",
				"content" => $config,
			));

			// now create the needed db connection and set it as default and then let all registerer classes init the database
//			$this->do_init_site($vars);

			exit;
			header("Location: http://$awdbname");
			exit;
		};

		return $this->parse();
	}

	////
	// !Asks for the neccessary information for the site to be created
	function init_site($args = array())
	{
		$q = "SELECT COUNT(*) AS cnt FROM users";
		$this->db_query($q);
		$row = $this->db_next();
		$default_user = $this->cfg["default_user"];
		$default_pass = $this->cfg["default_pass"];
		if ($row["cnt"] == 0)
		{
			print "executing initialization queries!";
			// those queries suck. They should be encapsulated in their own classes
			// but for now they will do
			flush();
			
			$q = "INSERT INTO objects (oid,parent,name,class_id,status) VALUES (1,0,'root',1,2)";
			$this->db_query($q);

			$q = "INSERT INTO objects (oid,parent,name,class_id,status) VALUES (2,1,'admins',37,2)";
			$this->db_query($q);
			
			// teeme kodukataloogi
			$hfid = $this->new_object(array("parent" => 1, "name" => $default_user, "class_id" => 1, "comment" => $default_user." kodukataloog"),false);
			$this->db_query("INSERT INTO menu (id,type) VALUES($hfid,".MN_HOME_FOLDER.")");

			$default_pass = md5($default_pass);

			$q = "INSERT INTO users (uid,password,home_folder) VALUES ('$default_user','$default_pass',$hfid)";
			$this->db_query($q);

			$q = "INSERT INTO groups (gid,name,type,priority) VALUES (1,'$default_user',1,100000000)";
			$this->db_query($q);

			$q = "INSERT INTO groupmembers (gid,uid) values(1,'$default_user')";
			$this->db_query($q);

			$q = "INSERT INTO groups (gid,name,type,priority) VALUES (2,'admins',0,1)";
			$this->db_query($q);

			$q = "INSERT INTO groupmembers (gid,uid) VALUES (2,'$default_user')";
			$this->db_query($q);

			$q = "INSERT INTO acl VALUES(1,2,2,1)";
			$this->db_query($q);

			// now we have to create a few content menus


			return true;

		}
		else
		{
			return false;
		};
		//$this->read_adm_template("init_site.tpl");
		//return $this->parse();
	}

	function do_init_site($vars)
	{
		// set the default connection to the new database
		$this->db_connect(array(
			"username" => $vars["db_user"],
			"server" => $vars["db_host"],
			"base" => $vars["db_base"],
			"password" => $vars["db_pass"],
			"driver" => "mysql"
		));

		foreach($this->cfg["init_classes"] as $class)
		{
			echo "class = $class <br>";
			$inst = get_instance($class);
			$inst->db_init();
			if (method_exists($inst, "on_site_init"))
			{
				echo "call on_site_init for class $class <br>";
				$inst->on_site_init(&$this, $vars);
			}
		}
	}
};
?>
