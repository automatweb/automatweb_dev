<?php
// $Id: tabpanel.aw,v 1.15 2005/12/02 05:50:27 ahti Exp $
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
		$tab_prefix = isset($args["tabgroup"]) ? $args["tabgroup"] . "_" : "";
		// 1. I'll add one optional argument - tabgroup
		// 2. I'll prepare a different subtemplate in the tabpanel template
		// 3. If no such template exists, then I ignore the tabgroup key
		if (isset($args["active"]) && $args["active"])
		{
			$subtpl = "sel_tab";
			if(!empty($args["encoding"]))
			{
				aw_global_set("output_charset", $args["encoding"]);
			}
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
		
		$use_subtpl = $tab_prefix . $subtpl . "_L" . $level;
		//$secondary = $tab_prefix . $use_subtpl;
		global $XX3;
		if ($XX3)
		{
			print "trying $use_subtpl<br>";
		};
		if (!$this->is_template($use_subtpl))
		{
			$use_subtpl = $subtpl . "_L" . $level;
			$tab_prefix = "";
		};

		if (isset($this->tabcount[$tab_prefix . $level]))
		{
			$this->tabcount[$tab_prefix . $level]++;
		}
		else
		{
			$this->tabcount[$tab_prefix . $level] = 1;
		};

		global $XX3;
		if ($XX3)
		{
			print "using " . $tab_prefix . " for " . $args["caption"] . "<br>";
		};

		// initialize properly
		if (empty($this->tabs[$tab_prefix . $level]))
		{
			$this->tabs[$tab_prefix . $level] = "";
		};


		//$this->tabs[$level] .= $this->parse($subtpl . "_L" . $level);
		$this->tabs[$tab_prefix . $level] .= $this->parse($use_subtpl);

		// so, I need a way to specify other tab groups.
	}

	////
	// !Initializes a tabpanel component
	function init_vcl_property($arr)
	{
		$prop = $arr["property"];
		$prop["vcl_inst"] = $this;


		return array($prop["name"] => $prop);
		//print "initializing tab panel<br>";



	}

	function get_html()
	{
		// this thing has to return generated html from the component
		return $this->get_tabpanel();

	}

	function configure($arr)
	{
		if (isset($arr["logo_image"]))
		{
			$this->vars(array(
				"logo_image" => $arr["logo_image"],
			));
		};

		if (isset($arr["background_image"]))
		{
			$this->vars(array(
				"background_image" => $arr["background_image"],
			));
		};
	}

	function set_style($style_name)
	{
		if ($style_name == "with_logo")
		{
			$this->read_template("tabs_with_logo.tpl");
		};
	}
	

	////
	// !Generates and returns the tabpanel
	// content(string) - contents of active panel
	function get_tabpanel($args = array())
	{
		$tabs = "";
		$panels = array();
		$this->vars(array(
			"uid" => aw_global_get("uid"),
			"time" => $this->time2date(time()),
		));
		foreach($this->tabcount as $level => $val)
		{
			if (($val > 1) || !$this->hide_one_tab)
			{
				$prefix = "";
				$lnr = $level;
				if (strpos($level,"_") !== false)
				{
					$px = strpos($level,"_") + 1;
					$prefix = substr($level,0,strpos($level,"_") + 1);
					$lnr = substr($level,$px);
				};
				$this->vars(array(
					$prefix . "tab_L" . $lnr  => $this->tabs[$level],
				));
				$this->vars(array(
					$prefix . "tabs_L" . $lnr => $this->parse($prefix . "tabs_L" . $lnr),
				));

				if ($args["panels_only"])
				{
					$r_prefix = str_replace("_","",$prefix);
					$panels[$r_prefix][] = $this->parse($prefix . "tabs_L" . $lnr);
				};
			};
		};

		if ($args["panels_only"])
		{
			return $panels;
		};


		$toolbar = isset($args["toolbar"]) ? $args["toolbar"] : "";
		$toolbar2 = isset($args["toolbar2"]) ? $args["toolbar2"] : "";

		// how do I return different subtemplates?

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
