<?php
// $Header: /home/cvs/automatweb_dev/classes/html.aw,v 2.3 2002/10/31 12:20:44 duke Exp $
// html.aw - helper functions for generating HTML
class html extends aw_template
{
	function draw($arg)
	{
		$arg = new aw_array($arg);
		$args = $arg->get();
		$type = $args["type"];
		if (method_exists($this,$type))
		{
			$retval = $this->$type($args);
		}
		else
		{
			// draw the text for undefined elements
			$retval = $this->text($args);
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

		if ($size)
		{
			$sz = "size='$size' ";
		};

		if ($multiple)
		{
			$mz = "multiple ";
			$name .= "[]";
		};
		if (is_array($selected))
		{
			$options = $this->mpicker($selected,$options);
		}
		else
		{
			$options = $this->picker($selected,$options);
		};
		return "<select name='$name' $sz $mz>\n$options</select>\n";
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
	// !File upload
	function fileupload($args = array())
	{
		extract($args);
		return "$value <input type='file' name='$name'>\n";
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
	// !Radiobutton
	// name(string)
	// value(string)
	// checked(bool)
	function radiobutton($args = array())
	{
		extract($args);
		$checked = checked($checked);
		return "<input type='radio' name='$name' value='$value' $checked/>\n $caption";
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
