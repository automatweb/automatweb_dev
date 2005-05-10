<?php
// $Header: /home/cvs/automatweb_dev/classes/core/help/help.aw,v 1.2 2005/05/10 13:51:43 kristo Exp $

// more ideas --- I might want to keep the help open when switching between tabs... for this I need to 
// set a cookie

class help extends aw_template
{
	function help()
	{
		$this->tpl_init("help");
	}

	/** shows a help browser for a class
		@attrib name=browser default="1"
		@param clid required type=int
	**/
	function browser($arr)
	{
		$this->read_template("browser.tpl");

		$cfgu = get_instance("cfg/cfgutils");
		if (!$cfgu->has_properties(array("clid" => $arr["clid"])))
		{
			die(t("Selle klassil puudub abiinfo"));
		};

		$props = $cfgu->load_properties(array(
			"clid" => $arr["clid"],
		));

		$clinf = aw_ini_get("classes");
		$classdat = $clinf[$arr["clid"]];

		$groups = $cfgu->get_groupinfo();

		$tree = get_instance("vcl/treeview");
		$tree->start_tree (array (
			"type" => TREE_DHTML,
			"url_target" => "helpcontent",
			//"tree_id" => "resourcetree",
			//"persist_state" => 1,
                ));

		classload("core/icons");
		$tree->add_item(0,array(
			"name" => $classdat["name"],
			"id" => "root",
			"url" => $this->mk_my_orb("browser",array("clid" => $arr["clid"])),
			"is_open" => 1,
			"iconurl" => icons::get_icon_url($arr["clid"]),
			"url_target" => "",
		));

		foreach($groups as $gkey => $gdata)
		{
			$parent = isset($gdata["parent"]) ? $gdata["parent"] : "root";
			$tree->add_item($parent,array(
				"name" => $gdata["caption"],
				"id" => $gkey,
				"url" => sprintf("javascript:read_help('%s','%s');",$arr["clid"],$gkey), 
				"url" => $this->mk_my_orb("grouphelp",array(
					"clid" => $arr["clid"],
					"grpid" => $gkey,
				))	, 
				"is_open" => 1,
				"iconurl" => "images/icons/help_topic.gif",
			));
		};

		$this->vars(array(
			// do not use the thing passed in from the URL
			"help_caption" => sprintf(t("Klassi '%s' abiinfo"),$classdat["name"]),
			"help_content_tree" => $tree->finalize_tree(),
			"retrieve_help_func" => $this->mk_my_orb("grouphelp",array(),"help"),
			"browser_caption" => t("AW abiinfo"),
		));
		die($this->parse());
	}

	/** shows help for a single group
		@attrib name=grouphelp
		@param clid required type=int
		@param grpid required
	**/
	function grouphelp($arr)
	{
		$this->read_template("grouphelp.tpl");
		$this->sub_merge = 1;
		$cfgu = get_instance("cfg/cfgutils");
		if (!$cfgu->has_properties(array("clid" => $arr["clid"])))
		{
			die(t("Selle klassil puudub abiinfo"));
		};

		$props = $cfgu->load_properties(array(
			"clid" => $arr["clid"],
			"filter" => $filter,
		));
		$groups = $cfgu->get_groupinfo();

		if (empty($groups[$arr["grpid"]]))
		{
			die(t("Sellist gruppi pole"));
		};

		$prophelp = "";
		foreach($props as $pkey => $pval)
		{
			if ($pval["group"] != $arr["grpid"])
			{
				continue;
			};
			
			$this->vars(array(
				"property_name" => $pval["caption"],
				"property_comment" => $pval["comment"],
				"propery_help" => $pval["help"],
			));

			$this->parse("PROPERTY_HELP");

		};


		$this->vars(array(
			"groupname" => $groups[$arr["grpid"]]["caption"],
		));
		die($this->parse());
	}

};
?>
