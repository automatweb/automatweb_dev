<?php
class tabpanel extends aw_template
{
	function tabpanel($args = array())
	{
		$this->init("tabpanel");
		$this->read_template("tabs.tpl");
		$this->tabs = "";
	}

	function add_tab($args = array())
	{
		$subtpl = ($args["active"]) ? "sel_tab" : "tab";
		$this->vars(array(
			"caption" => $args["caption"],
			"link" => $args["link"],
		));
		$this->tabs .= $this->parse($subtpl);
	}

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
