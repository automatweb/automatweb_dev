<?php
// $Header: /home/cvs/automatweb_dev/classes/html.aw,v 2.33 2003/04/09 22:40:02 duke Exp $
// html.aw - helper functions for generating HTML
class html extends aw_template
{
	////
	// !html select
	// name(string)
	// options(array)
	// selected(int)
	// onchange(string)
	function select($args = array())
	{
		extract($args);
		$sz = $mz = $onc = "";
		// things that make one go humm.. -- duke
		if (empty($selected) && isset($value))
		{
			$selected = $value;
		};

		if (isset($size))
		{
			$sz = "size='$size' ";
		};

		if (isset($multiple))
		{
			$mz = "multiple ";
			$name .= "[]";
		};

		if (isset($selected) && is_array($selected))
		{
			$sel_array = $selected;
		}
		elseif (isset($selected))
		{
			$sel_array = array($selected);
		}
		else
		{
			$sel_array = array();
		};
		// hmhm. dunno, really. but it was in aw_template->mpicker -- duke
		$sel_array = array_flip($sel_array);

		$optstr = "";
		if (isset($options) && is_array($options))
		{
			while(list($k,$v) = each($options))
			{
				$selected = isset($sel_array[$k]) ? " selected " : "";
				$optstr .= "<option $selected value='$k'>$v</option>\n";
			};
		};

		if (!empty($onchange))
		{
			$onc = 'onChange="'.$onchange.'"';
		}
		return "<select name='$name' id='$name' $sz $mz $onc>\n$optstr</select>\n";
	}

	////
	// !html text input
	// name(string)
	// value(string)
	// size(int)
	function textbox($args = array())
	{
		extract($args);
		$size = isset($size) ? $size : 40;
		$maxlength = isset($maxlength) ? $maxlength : "";
		$value = isset($value) ? $value : "";
		$value = str_replace('"' , '&quot;',$value);
		return "<input type=\"text\" id=\"$name\" name=\"$name\" size=\"$size\" value=\"$value\" maxlength=\"$maxlength\"/>\n";
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
		$cols = isset($cols) ? $cols : 40;
		$rows = isset($rows) ? $rows : 5;
		$value = isset($value) ? $value : "";
		if (isset($richtext) && (strpos(aw_global_get("HTTP_USER_AGENT"),"MSIE") > 0) )
		{
			$args["type"] = "richtext";
			$args["width"] = $cols * 10;
			$args["height"] = $rows * 10;
			$args["value"] = str_replace("\"" , "&quot;",$args["value"]); //"
			$retval = html::richtext($args);
		}
		else
		{
			$wrap = isset($wrap) ? $wrap : "soft";
			$retval = "<textarea id='$name' name='$name' cols='$cols' rows='$rows' wrap='$wrap'>$value</textarea>\n";
		};
		return $retval;
	}

	////
	//draws nice border around html content and put cute label on it, not all browsers support this
	//caption
	//content
	function fieldset($args = array())
	{
		extract($args);
		$caption = isset($caption) ? '<legend>'.$caption.'</legend>' : '';

		return '<fieldset>'.$caption.$content.'</fieldset>';
	}

	////
	// !html iframe
	// name(string)
	// width(string)
	// height(integer)
	// src(string)  - url
	function iframe($args = array())
	{
		extract($args);
		$width = isset($width) ? $width : 300;
		$height = isset($height) ? $height : 200;
		return "<iframe src='$src' name='$name' width='$width' height='$height'></iframe>\n";
	}

	//width - popup width
	//height - popup height
	//top, left
	//options - array of id-s => names
	//selected - array
	//multiple

	function popup_objmgr($args = array())
	{
		extract($args);
		$str = '';
		if (isset($multiple))
		{
			$mz = "multiple ";
			$name .= "[]";
			$pop_type='2';
		}
		else
		{
			$pop_type='1';
		}

		if ($multiple)
		{
			if (isset($options[0]))
			{
				unset($options[0]);
			}
			$options = $this->mpicker($selected,$options);
		}
		else
		{
			$options = $this->picker($selected,$options);
		};

		if (!isset($this->got_popup_objmgr))
		{
			$this->got_popup_objmgr=1;
			$this->width = isset($width) ? $width : '';
			$this->height = isset($height) ? $height : '';

			$str.=localparse(implode('',file($this->cfg['tpldir'].'/popup_objmgr/popup_objmgr.script')),
				array(
					'params' => (isset($top) ? 'top='.$top.',' : '').(isset($left) ? 'left='.$left.',' : ''),
				)

			);
		}
		$width = isset($width) ? $width : $this->width;
		$height = isset($height) ? $height : $this->height;

		return 	$str.="<select name='".$name."' $mz id='".$name."'>\n".$options."</select>\n".
			"<input type='button' value=' + ' onClick=\""."current_element='".$name."';pop_select('".$popup_objmgr."',$width,$height);"."\" />
			".(isset($change) ? $change : '')."\n";

	}

	////
	// !html password input
	// name(string)
	// value(string)
	// size(int)
	function password($args = array())
	{
		extract($args);
		$size = isset($size) ? $size : 40;
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
		$value = isset($value) ? $value : '';
		return "<input type='hidden' id='$name' name='$name' value='$value' />\n";
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
		$checked = isset($checked) ? checked($checked) : '';
		$capt = '';
		if (empty($value))
		{
			$value = 1;
		};
		if (isset($label))
		{
			$caption = $label;
		};
		if (isset($caption))
		{
			$capt = " " . $caption;
		};
		return "<input type='checkbox' id='$name' name='$name' value='$value' $checked/> $capt\n";
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
		return "<input type='submit' name='$name' value='$value' />\n";
	}

	////
	// !Simple button
	// value(string)
	// onclick(string)
	function button($args = array())
	{
		extract($args);
		return "<input type='button' value='$value' onClick=\"".$onclick."\" />\n";
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

	////
	// !Datetime selector
	function datetime_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);
		$selector->configure(array("day" => 1,"month" => 1,"year" => 1,"hour" => 1, "minute" => 1));
		if (is_array($args['value']))
		{
			$val = mktime($args["value"]["hour"],$args["value"]["minute"],0,$args["value"]["month"],$args["value"]["day"],$args["value"]["year"]);
		}
		else
		{
			$val = $args['value'];
		}
		return $selector->gen_edit_form($args["name"], $val, 2001, 2004, true);
	}
	
	////
	// !Date selector
	function date_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);
		$selector->configure(array("day" => 1,"month" => 1,"year" => 1));
		if (is_array($args['value']))
		{
			$val = mktime(0,0,0,$args["value"]["month"],$args["value"]["day"],$args["value"]["year"]);
		}
		else
		{
			$val = $args['value'];
		}
		return $selector->gen_edit_form($args["name"], $val, 2001, 2004, true);
	}

	function img($args = array())
	{
		extract($args);
		$ret = "<img src='$url'";
		if (isset($width))
		{
			$ret.=" width='$width'";
		}
		if (isset($height))
		{
			$ret.=" height='$height'";
		}
		if (isset($border))
		{
			$ret.=" border='$border'";
		}
		return $ret.">";
	}

	function href($args = array())
	{
		extract($args);
		$target = isset($target) ? " target='$target' " : "";
		$onClick = isset($onClick) ? " onClick='$onClick' " : "";
		return "<a href='$url' $target $onClick>$caption</a>";
	}

	function richtext($args = array())
	{
		// richtext editors are inside a template
		static $rtcounter = 0;
		$rtcounter++;
		$awt = get_instance("aw_template");
		$awt->init(array("tpldir" => "html"));
		/*
		$this->init(array(
			"tpldir" => "html",
		));
		*/
		$retval = "";
		$awt->vars($args);
		$awt->read_template("ie_richtexteditor.tpl");
		if ($rtcounter == 1)
		{
			$this->rt_elements = array($args["name"]);
			#$this->read_template("ie_richtexteditor.tpl");
			#$awt->read_template("ie_richtexteditor.tpl");
			#$retval .= $this->parse("toolbar");
			$retval .= $awt->parse("toolbar");
		};
		#$retval .= $this->parse("field");
		$retval .= $awt->parse("field");
		return $retval;
	}

	////
	// !html form, 
	// params:
	// method - form method
	// action - form action
	// name - form name
	// content - html to insert between form tags
	function form($args = array())
	{
		extract($args);
		return '<form action="'.$action.'" method="'.$method.'" name="'.$name.'">'.$content.'</form>';
	}

	////
	// !html <span class='$class'>$content</span>
	function span($args = array())
	{
		extract($args);
		return '<span class="'.$class.'">'.$content.'</span>';
	}
};
?>
