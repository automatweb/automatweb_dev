<?php
// $Header: /home/cvs/automatweb_dev/classes/html.aw,v 2.13 2002/11/27 15:21:53 duke Exp $
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
		if (!$selected && $value)
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
		return "<select name='$name' id='$name' $sz $mz>\n$options</select>\n";
	}
	
	////
	// !html text input
	// name(string)
	// value(string)
	// size(int)
	function textbox($args = array())
	{
		extract($args);
		$size = ($size) ? $size : 40;
		return "<input type='text' id='$name' name='$name' size='$size' value='$value' maxlength='$maxlength'/>\n";
	}

	////
	// !html textarea
	// name(string)
	// value(string)
	// cols(int)
	// rows(int)
	// wrap(string)
	function textarea($args = array())
	{
		extract($args);
		$cols = ($cols) ? $cols : 40;
		$rows = ($rows) ? $rows : 5;
		if ($richtext && (strpos(aw_global_get("HTTP_USER_AGENT"),"MSIE") > 0) )
		{
			$args["type"] = "richtext";
			$args["width"] = $cols * 10;
			$args["height"] = $rows * 10;
			$args["value"] = str_replace("\"","&quot;",$args["value"]);
			$retval = html::richtext($args);
		}
		else
		{
			$wrap = ($wrap) ? $wrap : "soft";
			$retval = "<textarea id='$name' name='$name' cols='$cols' rows='$rows' wrap='$wrap'>$value</textarea>\n";
		};
		return $retval;
	}
	
	////
	// !html password input
	// name(string)
	// value(string)
	// size(int)
	function password($args = array())
	{
		extract($args);
		$size = ($size) ? $size : 40;
		return "<input type='password' id='$name' name='$name' size='$size' value='$value' maxlength='$maxlength'/>\n";
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
		return "$value <input type='file' id='$name' name='$name'>\n";
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
		if (!$value)
		{
			$value = 1;
		};
		return "<input type='checkbox' id='$name' name='$name' value='$value' $checked/>\n";
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

	function img($args = array())
	{
		extract($args);
		return "<img src='$url'>";
	}

	function href($args = array())
	{
		extract($args);
		$target = ($target) ? " target='$target' " : "";
		return "<a href='$url' $target>$caption</a>";
	}

	function richtext($args = array())
	{
		// richtext editors are inside a template
		static $rtcounter = 0;
		$rtcounter++;
		$this->init(array(
			"tpldir" => "html",
		));
		$retval = "";
		$this->vars($args);
		if ($rtcounter == 1)
		{
			$this->rt_elements = array($args["name"]);
			$this->read_template("ie_richtexteditor.tpl");
			$retval .= $this->parse("toolbar");
		};
		$retval .= $this->parse("field");
		return $retval;
	}



};
?>
