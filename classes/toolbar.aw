<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/toolbar.aw,v 2.4 2002/10/15 19:35:51 duke Exp $
// toolbar.aw - drawing toolbars
class toolbar extends aw_template
{
	function toolbar($args = array())
	{
		$this->init("toolbar");
		$this->read_template("buttons.tpl");
		// need to support 2 toolbars on one page.
		// and this is how we do it now.
		$this->tb_content = "";
		$this->tb_content2 = "";
		extract($args);
		if (!$imgbase)
		{
			$imgbase = "/automatweb/images/icons";
		};

		$this->vars(array(
			"imgbase" => $this->cfg["baseurl"] . $imgbase,
		));
	}

	////
	// !Adds a button to the toolbar
	function add_button($args = array())
	{
		$this->vars($args);
		$this->tb_content .= $this->parse("smallbutton");
		$args["name"] .= "1";
		$this->vars($args);
		$this->tb_content2 .= $this->parse("smallbutton");
	}

	////
	// !Adds a separator to the toolbar
	function add_separator($args = array())
	{
		$this->vars($args);
		$this->tb_content .= $this->parse("smallseparator");
		$this->tb_content2 .= $this->parse("smallseparator");
	}

	////
	// !Allows to add custom data to the boolar
	function add_cdata($content)
	{
		$this->vars(array(
			"data" => $content,
		));
		$this->tb_content .= $this->parse("cdata");
		$this->tb_content2 .= $this->parse("cdata");
	}

	////
	// !Returns the whole toolbar
	function get_toolbar($args = array())
	{
		$retval = $this->parse("start") . $this->tb_content . $this->parse("end");
		return $retval;
	}
	
	////
	// !Returns the whole second toolbar
	function get_toolbar2($args = array())
	{
		$retval = $this->parse("start") . $this->tb_content2 . $this->parse("end");
		return $retval;
	}

};
?>
