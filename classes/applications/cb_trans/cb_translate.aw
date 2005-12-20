<?php
class cb_translate extends aw_template
{
	function cb_translate()
	{
		$this->tpl_init("applications/cb_translate");
	}

	/**
		@attrib name=editor
		@param clid required type=int 
	**/
	function editor($arr)
	{
		// now, I have got clid .. how to I generate the bloody interface?
		$this->read_template("editor.tpl");
		$clid = $arr["clid"];

		$cfgu = get_instance("cfg/cfgutils");
                if (!$cfgu->has_properties(array("clid" => $arr["clid"])))
                {
                        die(t("Selle klassil puuduvad omadused"));
                };
	
		$tb = get_instance("vcl/toolbar");

		$tb->add_button(array(
			"name" => "save",
			"caption" => t("Salvesta"),
			"img" => "save.gif",
			"target" => "editorcontent",
			"action" => "",
		));

                $props = $cfgu->load_properties(array(
                        "clid" => $arr["clid"],
                ));

                $clinf = aw_ini_get("classes");
                $classdat = $clinf[$arr["clid"]];

                $groups = $cfgu->get_groupinfo();

                $tree = get_instance("vcl/treeview");
                $tree->start_tree (array (
                        "type" => TREE_DHTML,
                        "url_target" => "editorcontent",
                        //"tree_id" => "resourcetree",
                        //"persist_state" => 1,
                ));

		classload("core/icons");
		$tree->add_item(0,array(
			"name" => $classdat["name"],
			"id" => "root",
			"url" => $this->mk_my_orb("editor",array("clid" => $arr["clid"])),
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
				"url" => $this->mk_my_orb("groupedit",array(
                                        "clid" => $arr["clid"],
                                        "grpid" => $gkey,
                                ))      ,
                                "is_open" => 1,
                                "iconurl" => "images/icons/help_topic.gif",
                        ));
                };

		$this->vars(array(
		// do not use the thing passed in from the URL
                        "editor_caption" => sprintf(t("Klassi '%s' tõlkimine"),$classdat["name"]),
                        "editor_content_tree" => $tree->finalize_tree(),
                        "browser_caption" => t("AW tõlkimine"),
			"toolbar" => $tb->get_toolbar(),
                ));



		return $this->parse();

	}

	/**
		@attrib name=groupedit
		@param clid required type=int
		@param grpid required
	**/
	function groupedit($arr) 
	{
		$this->read_template("groupedit.tpl");
		$this->sub_merge = 1;
		$cfgu = get_instance("cfg/cfgutils");
		if (!$cfgu->has_properties(array("clid" => $arr["clid"])))
                {
                        die(t("Selle klassil pole omadusi"));
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
				"property_id" => $pkey,
				"property_type" => $pval["type"],
                                "property_name" => $pval["caption"],
                                "property_comment" => $pval["comment"],
                                "propery_help" => $pval["help"],
                        ));

                        $this->parse("PROPERTY_TRANSLATE");
                };
		$this->vars(array(
			"groupname" => $groups[$arr["grpid"]]["caption"],
			"reforb" => $this->mk_reforb("submit_editor",array(
				"clid" => $arr["clid"],
				"grpid" => $arr["grpid"],
			)),
		));
                die($this->parse());


	}

	/**
		@attrib name=submit_editor
	**/
	function submit_editor($arr)
	{
		arr($arr);
		// now I just need to write the bloody thing
		return $this->mk_my_orb("groupedit",array("clid" => $arr["clid"],"grpid" => $arr["grpid"]));

	}

};
?>
