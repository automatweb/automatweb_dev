<?php
// $Id: tabpanel.aw,v 1.7 2004/01/13 16:24:33 kristo Exp $
// tabpanel.aw - class for creating tabbed dialogs
class tabpanel extends aw_template
{
	////
	// !Initializes a tabpanel object
	function tabpanel($args = array())
	{
		$this->init("tabpanel");
		$tpl = isset($args["tpl"]) ? $args["tpl"] . ".tpl" : "tabs.tpl";
		$this->read_template($tpl);
		$this->tabs = array();
		$this->tabcount = array();
		$this->hide_one_tab = 0;
	}

	////
	// !Adds a new tab to the panel
	// active(bool) - whether to use the "selected" subtemplate for this tab
	// caption(string) - text to display as caption
	// link(string)
	function add_tab($args = array())
	{
		if (isset($args["active"]) && $args["active"])
		{
			$subtpl = "sel_tab";
		}
		else
		{
			$subtpl = "tab";
		};

		if (isset($args["disabled"]) && $args["disabled"])
		{
			$subtpl = "disabled_tab";
		};

		if (isset($args["level"]) && $args["level"])
		{
			$level = $args["level"];
		}
		else
		{
			$level = 1;
		};

		// no link? so let's show the tab as disabled
		if (isset($args["link"]) && strlen($args["link"]) == 0)
		{
			$subtpl = "disabled_tab";
		};
		$this->vars(array(
			"caption" => $args["caption"],
			"link" => $args["link"],
		));
		if (isset($this->tabcount[$level]))
		{
			$this->tabcount[$level]++;
		}
		else
		{
			$this->tabcount[$level] = 1;
		};

		// initialize properly
		if (empty($this->tabs[$level]))
		{
			$this->tabs[$level] = "";
		};
		
		$this->tabs[$level] .= $this->parse($subtpl . "_L" . $level);
	}

	////
	// !Generates and returns the tabpanel
	// content(string) - contents of active panel
	function get_tabpanel($args = array())
	{
		$tabs = "";
		foreach($this->tabcount as $level => $val)
		{
			if (($val > 1) || !$this->hide_one_tab)
			{
				$this->vars(array(
					"tab_L" . $level  => $this->tabs[$level],
				));
				$this->vars(array(
					"tabs_L" . $level => $this->parse("tabs_L" . $level),
				));
			};
		};

		$toolbar = isset($args["toolbar"]) ? $args["toolbar"] : "";
		$toolbar2 = isset($args["toolbar2"]) ? $args["toolbar2"] : "";

		$this->vars(array(
			//"tabs" => $tabs,
			"toolbar" => $toolbar,
//                        "toolbar2" => $toolbar2,
			"content" => $args["content"],
		));
		return $this->parse();
	}

	////
	// !returns an instance of tabpanel, with the given tabs loaded.
	// usage:
	//	$tb = tabpanel::simple_tabpanel(array(
	//		"panel_props" => array("tpl" => "headeronly"),
	//		"var" => "cool_tab",
	//		"default" => "entities",
	//		"opts" => array(
	//			"entities" => "Olemid",
	//			"processes" => "Protsessid
	//		)
	//	));
	//	this will create a tabpanel with two tabs, active is derived from the "cool_tab" variable in the url
	//	links are made using aw_url_change_var($var, key_for_tab)
	//	the default tab is in the "default" parameter
	//	the tabpanel is created, using the options in the "panel_props" parameter
	function simple_tabpanel($arr)
	{
		if (!isset($_GET[$arr["var"]]) || empty($_GET[$arr["var"]]))
		{
			$_GET[$arr["var"]] = $arr["default"];
		}

		$tb = new tabpanel($arr["panel_props"]);
		foreach($arr["opts"] as $k => $v)
		{
			$tb->add_tab(array(
				"link" => aw_url_change_var($arr["var"], $k),
				"caption" => $v,
				"active" => ($_GET[$arr["var"]] == $k)
			));
		}

		return $tb;
	}
};
?>
