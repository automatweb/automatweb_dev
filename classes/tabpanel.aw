<?php
// $Id: tabpanel.aw,v 2.2 2002/11/02 23:25:47 duke Exp $
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
		$this->tabs .= $this->parse($subtpl);
	}

	////
	// !Generates and returns the tabpanel
	// content(string) - contents of active panel
	function get_tabpanel($args = array())
	{
		$this->vars(array(
			"tab" => $this->tabs,
		));

		$this->vars(array(
			"tabs" => $this->parse("tabs"),
			"content" => $args["content"],
		));
		return $this->parse();
	}
};
?>
