<?php
// $Header: /home/cvs/automatweb_dev/classes/html.aw,v 2.2 2002/10/15 20:35:37 duke Exp $
// html.aw - helper functions for generating HTML
class html extends aw_template
{
	function draw($args = array())
	{
		$type = $args["type"];
		if (method_exists($this,$type))
		{
			$retval = $this->$type($args);
		}
		else
		{
			// draw the textbox for undefined elements
			$retval = $this->textbox($args);
		};
		return $retval;
	}
	////
	// !html select
	// name(string)
	// options(array)
	// selected(int)
	function select($args = array())
	{
		extract($args);
		if ($value)
		{
			$selected = $value;
		};
		$options = $this->picker($selected,$options);
		return "<select name='$name'>\n$options</select>\n";
	}

	////
	// !html text input
	// name(string)
	// value(string)
	// size(int)
	function textbox($args = array())
	{
		extract($args);
		$size = ($size) ? $size : 30;
		return "<input type='text' name='$name' size='$size' value='$value' maxlength='$maxlength'/>\n";
	}

	////
	// !Simple text
	function text($args = array())
	{
		return $args["value"];
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

	////
	// !Time selector
	function time_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);
		$selector->configure(array("hour" => 1, "minute" => 1));
		list($d,$m,$y) = explode("-",date("d-m-Y"));
		$val = mktime($args["value"]["hour"],$args["value"]["minute"],0,$m,$d,$y);
		return $selector->gen_edit_form($args["name"], $val);
	}

};
?>
