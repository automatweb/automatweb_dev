<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/toolbar.aw,v 2.3 2002/10/02 11:55:51 duke Exp $
// toolbar.aw - drawing toolbars
class toolbar extends aw_template
{
	function toolbar($args = array())
	{
		$this->init("toolbar");
		$this->read_template("buttons.tpl");
		$this->tb_content = "";
		$this->vars(array(
			"imgbase" => $this->cfg["baseurl"] . $args["imgbase"],
		));
	}

	////
	// !Adds a button to the toolbar
	function add_button($args = array())
	{
		$this->vars($args);
		$this->tb_content .= $this->parse("smallbutton");
	}

	////
	// !Adds a separator to the toolbar
	function add_separator($args = array())
	{
		$this->vars($args);
		$this->tb_content .= $this->parse("smallseparator");
	}

	////
	// !Allows to add custom data to the boolar
	function add_cdata($content)
	{
		$this->vars(array(
			"data" => $content,
		));
		$this->tb_content .= $this->parse("cdata");
	}

	////
	// !Returns the whole toolbar
	function get_toolbar($args = array())
	{
		$retval = $this->parse("start") . $this->tb_content . $this->parse("end");
		return $retval;
	}

};
?>
