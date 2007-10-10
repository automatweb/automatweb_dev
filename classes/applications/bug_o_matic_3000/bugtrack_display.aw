<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bugtrack_display.aw,v 1.1 2007/10/10 12:47:07 robert Exp $
// bugtrack_display.aw - Ülesannete kuvamine 
/*

@classinfo syslog_type=ST_BUGTRACK_DISPLAY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class bugtrack_display extends class_base
{
	function bugtrack_display()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/bugtrack_display",
			"clid" => CL_BUGTRACK_DISPLAY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
	

	/**
	@attrib name=orders all_args=1
	**/
	function orders($arr)
	{
		$this->read_template("show.tpl");
		
		$this->set_tabs($arr['id']);

		classload("vcl/table");
		$t = new aw_table();
		$this->init_bug_table($t);
		
		$this->vars(array(
			"content" => $t->draw()
		));

		return $this->parse();
	}

	/**
	@attrib name=sbugs all_args=1
	**/
	function solved_bugs($arr)
	{
		$this->read_template("show.tpl");
		
		$this->set_tabs($arr['id']);

		classload("vcl/table");
		$t = new aw_table();
		$this->init_bug_table($t);

		$this->vars(array(
			"content" => $t->draw()
		));

		return $this->parse();
	}

	/**
	@attrib name=apps all_args=1
	**/
	function manage_apps($arr)
	{
		$this->read_template("show.tpl");
		
		$this->set_tabs($arr['id']);

		return $this->parse();
	}

	/**
	@attrib name=groups all_args=1
	**/
	function manage_groups($arr)
	{
		$this->read_template("show.tpl");
		
		$this->set_tabs($arr['id']);

		return $this->parse();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////
	function set_tabs($id)
	{
		$tp = get_instance("vcl/tabpanel");
		$tp->add_tab(array(
			"caption" => t("Uus &uuml;lesanne"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $id,
				"cfgform_id" => 777,
				"section" => aw_global_get("section")
			), CL_BUG)
		));
		$tp->add_tab(array(
			"link" => $this->mk_my_orb("bugs",array(
				"section" => aw_global_get("section"),
				"id" => $id
			)),
			"caption" => t("&Uuml;lesanded"),
		));
		$tp->add_tab(array(
			"caption" => t("Arendustellimused"),
			"link" => $this->mk_my_orb("orders",array(
				"section" => aw_global_get("section"),
				"id" => $id
			)),
		));
		$tp->add_tab(array(
			"caption" => t("Lahendatud"),
			"link" => $this->mk_my_orb("sbugs",array(
				"section" => aw_global_get("section"),
				"id" => $id
			)),
		));
		$tp->add_tab(array(
			"caption" => t("Rakendused"),
			"link" => $this->mk_my_orb("apps",array(
				"section" => aw_global_get("section"),
				"id" => $id
			)),
		));
		$tp->add_tab(array(
			"caption" => t("Rakendused"),
			"link" => $this->mk_my_orb("groups",array(
				"section" => aw_global_get("section"),
				"id" => $id
			)),
		));
        	$this->vars(array(
			"tabs" => $tp->get_tabpanel()
		));
	}

	function init_bug_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("L&uuml;hikirjeldus")
		));
		$t->define_field(array(
			"name" => "type",
			"caption" => t("T&uuml;&uuml;p")
		));
		$t->define_field(array(
			"name" => "cdate",
			"caption" => t("Loomise kuup&auml;ev")
		));
		$t->define_field(array(
			"name" => "ddate",
			"caption" => t("T&auml;htaeg")
		));
		$t->define_field(array(
			"name" => "pdate",
			"caption" => t("Prognoos")
		));
		$t->define_field(array(
			"name" => "priority",
			"caption" => t("Prioriteet")
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus")
		));
		$t->define_field(array(
			"name" => "creator",
			"caption" => t("Looja")
		));
		$t->define_field(array(
			"name" => "changer",
			"caption" => t("Muutja")
		));

	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	/**
	@attrib name=bugs all_args=1
	**/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		classload("vcl/table");
		$t = new aw_table();
		$this->init_bug_table($t);
		$this->set_tabs($arr['id']);
		$bugs = new object_list(array(
			"class_id" => CL_BUG,
			"parent" => $arr["id"]
		));

		$this->vars(array(
			"name" => $ob->prop("name"),
			"content" => $t->draw()
		));
		return $this->parse();
	}

//-- methods --//
}
?>
