<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/sysconf.aw,v 2.1 2002/03/04 20:20:53 duke Exp $
// sysconf.aw - const.aw web frontend
global $orb_defs;
$orb_defs["sysconf"] = "xml";
classload("aw_template");
if (!defined("sysconf_loaded"))
{
define("sysconf_loaded",1);
class sysconf extends aw_template {
	function sysconf($args = array())
	{
		extract($args);
		$this->config = array();
		$this->tpl_init();
		$this->db_init();

	}

	function adm_init($args = array())
	{
		classload("aw_template");
		$this->core = new aw_template();
		$this->core->tpl_init("sysconf");
		$this->core->db_init();
	}

	////
	// !Draws the menu
	function gen_menu($args = array())
	{
		extract($args);
		global $basedir;
		load_vcl("xmlmenu");
		$xm = new xmlmenu();
		$retval = $xm->build_menu(array(
				"vars"	=> $vars,
				"xml"	=> $basedir . "/xml/sysconf_menu.xml",
				"tpl"	=> $this->template_dir . "/sysconf_menu.tpl",
				"activelist" => $activelist,
			));
		return $retval;
	}

	function edit($args = array())
	{
		extract($args);
		print $this->gen_menu(array("activelist" => array($page)));
		$this->adm_init();
		classload("objects");
		$o = new objects;
		$menus = $o->get_list();
		classload("users_user");
		$g = new users_user; 
		$g->listgroups();
		while($row = $g->db_next())
		{
			$groups[$row["gid"]] = $row["name"];
		};
		global $menu_defs_v2;
		$c = "";
		$this->core->read_template("edit.tpl");
		foreach($menu_defs_v2 as $id => $name)
		{
			$this->core->vars(array("name" => $name,
					"id" => $id,
					"pickmenu" => $this->core->picker($id,$menus),
			));
			$c .= $this->core->parse("line");
		};
			
		$this->parse_config(array("source" => join("\n",@file("/www/aw.com/files/config.ini"))));
		$this->core->vars(array(
			"rootmenu" => $this->core->picker($this->config["rootmenu"],$menus),
			"frontpage" => $this->core->picker($this->config["frontpage"],$menus),
			"sitemap" => $this->core->picker($this->config["sitemap"],$menus),
			"groups" => $this->core->picker(-1,$groups),
			"line" => $c,
			"reforb" => $this->core->mk_reforb("submit",array("page" => $page),"sysconf"),
		));

		$this->core->vars(array(
			"placeholder" => $this->core->parse("menu_" . $page),
		));
		return $this->core->parse();
	}

	function submit($args = array())
	{
		// we need to register all the configuration settings somewhere
		$this->adm_init();
		$conf = "";
		if ($args["rootmenu"])
		{
			$conf .= sprintf("%s = %s\n","rootmenu",$args["rootmenu"]);
		};
		if ($args["frontpage"])
		{
			$conf .= sprintf("%s = %s\n","frontpage",$args["frontpage"]);
		};
		$this->core->put_file(array(
			"file" => "/www/aw.com/files/config.ini",
			"content" => $conf,
		));
		return $this->core->mk_orb("config",array("page" => $args["page"]),"sysconf");
	}

	////
	// !Parsib konfiguratsiooni lahti
	// source(string) - argumentide blokk
	function parse_config($args = array())
	{
		extract($args);
		$lines = explode("\n",$source);
		if (is_array($lines))
		{
      			foreach($lines as $line)
        		{
                		list($key,$value) = explode("=",$line);
                		$key = trim($key);
                		$value = trim($value);
                		if ( (strlen($key) > 0) && (strlen($value) > 0) )
                		{
					$this->config[$key] = $value;
                		};
        		};
		};
	}

	////
	// !tagastab konfiguratsiooniblok
	function get_config()
	{
		return $this->config;
	}
};
};
?>
