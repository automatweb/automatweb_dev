<?php
// $Id: tabpanel.aw,v 1.5 2003/03/05 17:03:51 duke Exp $
// tabpanel.aw - class for creating tabbed dialogs
class tabpanel extends aw_template
{
	////
	// !Initializes a tabpanel object
	function tabpanel($args = array())
	{
		$this->init("tabpanel");
		$this->read_template("tabs.tpl");
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
		// now link? so let's show the tab as disabled
		if (isset($args["link"]) && strlen($args["link"]) == 0)
		{
			$subtpl = "disabled_tab";
		};
		$this->vars(array(
			"caption" => $args["caption"],
			"link" => $args["link"],
		));
		$this->tabcount[$level]++;
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

		$toolbar = $args["toolbar"];
		$toolbar2 = $args["toolbar2"];

		$this->vars(array(
			//"tabs" => $tabs,
			"toolbar" => $toolbar,
//                        "toolbar2" => $toolbar2,
			"content" => $args["content"],
		));
		return $this->parse();
	}
};
?>
