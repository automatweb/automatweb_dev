<?php
// $Header: /home/cvs/automatweb_dev/classes/html.aw,v 2.1 2002/07/23 21:14:30 duke Exp $
// html.aw - helper functions for generating HTML
class html extends aw_template
{
	////
	// !html select
	// name(string)
	// options(array)
	// selected(int)
	function select($args = array())
	{
		extract($args);
		$options = $this->picker($selected,$options);
		return "<select name='$name'>\n$options</select>\n";
	}

	////
	// !html text input
	// name(string)
	// value(string)
	// size(int)
	function text($args = array())
	{
		extract($args);
		$size = ($size) ? $size : 30;
		return "<input type='text' name='$name' size='$size' value='$value' />\n";
	}

	////
	// !Hidden field
	// name(string)
	// value(string)
	function hidden($args = array())
	{
		extract($args);
		return "<input type='hidden' name='$name' value='$value' />\n";
	}
	
	////
	// !Checkbox
	// name(string)
	// value(string)
	// checked(bool)
	function checkbox($args = array())
	{
		extract($args);
		$checked = checked($checked);
		return "<input type='checkbox' name='$name' value='$value' $checked/>\n";
	}
	
	////
	// !Submit button
	// value(string)
	function submit($args = array())
	{
		extract($args);
		return "<input type='submit' value='$value' />\n";
	}

	

}


?>
