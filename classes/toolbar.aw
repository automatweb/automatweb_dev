<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/toolbar.aw,v 2.1 2002/09/25 22:45:08 duke Exp $
// toolbar.aw - drawing toolbars
class toolbar extends aw_template
{
	function toolbar($args = array())
	{
		$this->init("toolbar");
		$this->read_template("buttons.tpl");
	}

	////
	// !Generates a button with mouse-over effect
	// url - what to do if the button is clicked
	// name - name of the button (must be a valid identifier)
	// tooltip - text of the tooltip
	// img - url of the default image
	// imgover - url of the mouse-over image
	function gen_button($args = array())
	{
		$this->vars($args);
		return $this->parse("button");
	}
	
	function gen_separator($args = array())
	{
		$this->vars($args);
		return $this->parse("separator");
	}

};
?>
