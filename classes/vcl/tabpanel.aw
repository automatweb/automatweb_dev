<?php
// $Id: tabpanel.aw,v 1.4 2003/01/17 12:34:09 duke Exp $
// tabpanel.aw - class for creating tabbed dialogs
class tabpanel extends aw_template
{
	////
	// !Initializes a tabpanel object
	function tabpanel($args = array())
	{
		$this->init("tabpanel");
		$this->read_template("tabs.tpl");
		$this->tabs = "";
		$this->tabcount = 0;
		$this->hide_one_tab = 0;
	}

	////
	// !Adds a new tab to the panel
	// active(bool) - whether to use the "selected" subtemplate for this tab
	// caption(string) - text to display as caption
	// link(string)
	function add_tab($args = array())
	{
		if ($args["active"])
		{
			$subtpl = "sel_tab";
		}
		else
		{
			$subtpl = "tab";
		};
		// now link? so let's show the tab as disabled
		if (strlen($args["link"]) == 0)
		{
			$subtpl = "disabled_tab";
		};
		$this->vars(array(
			"caption" => $args["caption"],
			"link" => $args["link"],
		));
		$this->tabcount++;
		$this->tabs .= $this->parse($subtpl);
	}

	////
	// !Generates and returns the tabpanel
	// content(string) - contents of active panel
	function get_tabpanel($args = array())
	{
		$tabs = "";
		if (($this->tabcount > 1) || !$this->hide_one_tab)
		{
			$this->vars(array(
				"tab" => $this->tabs,
			));
			$tabs = $this->parse("tabs");
		};

		$toolbar = $args["toolbar"];
		$toolbar2 = $args["toolbar2"];

		$this->vars(array(
			"tabs" => $tabs,
			"toolbar" => $toolbar,
//                        "toolbar2" => $toolbar2,
			"content" => $args["content"],
		));
		return $this->parse();
	}
};
?>
