<?php
// $Id: tabpanel.aw,v 1.1 2002/11/04 21:03:37 duke Exp $
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
		$this->hide_one_tab = 1;
	}

	////
	// !Adds a new tab to the panel
	// active(bool) - whether to use the "selected" subtemplate for this tab
	// caption(string) - text to display as caption
	// link(string)
	function add_tab($args = array())
	{
		$subtpl = ($args["active"]) ? "sel_tab" : "tab";
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

		$this->vars(array(
			"tabs" => $tabs,
			"content" => $args["content"],
		));
		return $this->parse();
	}
};
?>
