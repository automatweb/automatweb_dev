<?php
// $Header: /home/cvs/automatweb_dev/classes/html.aw,v 2.67 2005/02/14 13:29:11 ahti Exp $
// html.aw - helper functions for generating HTML
class html extends aw_template
{
	////
	// !html select
	// name(string)
	// options(array)
	// selected(int)
	// onchange(string)
	// disabled(bool)
	function select($args = array())
	{
		extract($args);
		$disabled = ($disabled ? " disabled" : "");
		$sz = $mz = $onc = $cl = "";
		// things that make one go humm.. -- duke
		if (empty($selected) && isset($value))
		{
			$selected = $value;
		};

		if (isset($size))
		{
			$sz = "size='$size' ";
		};

		if (!empty($class))
		{
			$cl = "class=\"".$class."\"";
		}

		if (isset($multiple))
		{
			$mz = "multiple ";
			$name .= "[]";
		};

		if (isset($selected) && is_array($selected))
		{
			$sel_array = $selected;
		}
		elseif (isset($selected) && $selected !== false)
		{
			$sel_array = array($selected);
		}
		else
		{
			$sel_array = array();
		};
		// hmhm. dunno, really. but it was in aw_template->mpicker -- duke
		$sel_array = @array_flip($sel_array);

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
		return "<select name='$name' $cl id='$name' $sz $mz $onc" . $disabled . ">\n$optstr</select>\n";
	}

	////
	// !html text input
	// name(string)
	// value(string)
	// size(int)
	// disabled(bool)
	function textbox($args = array())
	{
		extract($args);
		$disabled = ($disabled ? " disabled" : "");
		$size = isset($size) ? $size : 40;
		$maxlength = isset($maxlength) ? $maxlength : "";
		$id = str_replace("[","_",$name);
		$id = str_replace("]","_",$id);
		$value = isset($value) ? $value : "";
		$value = str_replace('"' , '&quot;',$value);
		return "<input type=\"text\" id=\"$id\" name=\"$name\" size=\"$size\" value=\"$value\" maxlength=\"$maxlength\"" . $disabled . " />\n";
	}

	////
	// !html textarea
	// name(string)
	// value(string)
	// cols(int)
	// rows(int)
	// wrap(string)
	// disabled(bool)
	function textarea($args = array())
	{
		extract($args);
		$cols = isset($cols) ? $cols : 40;
		$rows = isset($rows) ? $rows : 5;
		$value = isset($value) ? $value : "";
		// now, the browser detection is best done in javascript
		if (!empty($richtext))
		{
			$args["type"] = "richtext";
			$args["width"] = $cols;
			$args["height"] = $rows;
			$args["value"] = str_replace("\"" , "&quot;",$args["value"]); //"
			$rte = get_instance("vcl/rte");
			$retval = $rte->draw_editor($args);
		}
		else
		{
			$disabled = ($disabled ? " disabled" : "");
			$wrap = isset($wrap) ? $wrap : "soft";
			$style = isset($style) ? " style='$style' " : "";
			$retval = "<textarea id='$name' name='$name' cols='$cols' rows='$rows' wrap='$wrap' $style" . $disabled . ">$value</textarea>\n";
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
		$width = isset($width) ? $width : '300';
		$height = isset($height) ? $height : '200';
		return "<iframe src='$src' name='$name' width='$width' height='$height'></iframe>\n";
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
	// disabled(bool)
	function fileupload($args = array())
	{
		extract($args);
		$disabled = ($disabled ? " disabled" : "");
		$rv = "";

		if (!empty($value))
		{
			$rv = $value . "<br />";
		}

		return $rv . "<input type='file' id='$name' name='$name'" . $disabled . " />\n";
	}

	////
	// !Checkbox
	// name(string)
	// value(string)
	// checked(bool)
	// disabled(bool)
	function checkbox($args = array())
	{
		extract($args);
		$checked = isset($checked) ? checked($checked) : '';
		$disabled = ($disabled ? " disabled" : "");
		$capt = '';
		if (empty($value))
		{
			$value = 1;
		};
		if (isset($label))
		{
			$capt .= $label;
		};
		if (isset($caption))
		{
			$capt .= " " . $caption;
		};

		$rv = "<input type='checkbox' id='$name' name='$name' value='$value' $checked " . $disabled . "/> $capt\n";
		return $rv;
	}

	////
	// !Radiobutton
	// name(string)
	// value(string)
	// checked(bool)
	// disabled(bool)
	function radiobutton($args = array())
	{
		extract($args);
		$checked = checked($checked);
		$disabled = ($disabled ? " disabled" : "");
		return "<input type='radio' name='$name' value='$value' $checked onClick='$onclick'" . $disabled . " />\n $caption";
	}

	////
	// !Submit button
	// value(string)
	function submit($args = array())
	{
		extract($args);
		if (isset($onclick))
		{
			$onclick = 'onclick="'.$onclick.'"';
		}

		return "<input id='cbsubmit' type='submit' name='$name' value='$value' class='$class' $onclick />\n";
	}

	////
	// !Simple button
	// value(string)
	// onclick(string)
	// disabled(bool)
	function button($args = array())
	{
		extract($args);
		$disabled = ($disabled ? " disabled" : "");
		return "<input type='".($type ? $type : "button")."' class='$class' value='$value' onClick=\"".$onclick."\"" . $disabled . " />\n";
	}

	////
	// !Time selector
	// disabled(bool)
	function time_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);
		$selector->set("minute_step", ($args["minute_step"] ? $args["minute_step"] : 1));
		$selector->configure(array("hour" => 1, "minute" => 1));
		list($d,$m,$y) = explode("-",date("d-m-Y"));
		$val = mktime($args["value"]["hour"], $args["value"]["minute"], 0, $m, $d, $y);

		if ($disabled)
		{
			$name = array ("name" => $args["name"], "disabled" => true);
		}
		else
		{
			$name = $args["name"];
		}

		return $selector->gen_edit_form($name, $val);
	}

	////
	// !Datetime selector
	// disabled(bool)
	function datetime_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);
		$selector->set("minute_step", ($args["minute_step"] ? $args["minute_step"] : 1));
		$set = array();
		if (!empty($args["day"]) && $args["day"] == "text")
		{
			$set["day_textbox"] = 1;
		}
		else
		{
			$set["day"] = 1;
		};
		if (!empty($args["month"]) && $args["month"] == "text")
		{
			$set["month_textbox"] = 1;
		}
		else
		{
			$set["month"] = 1;
		};
		$set["year"] = 1;
		$set["hour"] = 1;
		$set["minute"] = 1;
		/*	unset($set["day"]);
			"year" =>1,
			"hour" => 1,
			"minute" => 1,
			*/

		$selector->configure($set);
		if (is_array($args['value']))
		{
			$val = mktime($args["value"]["hour"], $args["value"]["minute"], 0, $args["value"]["month"], $args["value"]["day"], $args["value"]["year"]);
		}
		else
		{
			$val = $args['value'];
		}

		if ($disabled)
		{
			$name = array ("name" => $args["name"], "disabled" => true);
		}
		else
		{
			$name = $args["name"];
		}

		return $selector->gen_edit_form($name, $val, 2003, 2010, true);
	}

	////
	// !Date selector
	// disabled(bool)
	function date_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);
		$selector->configure(array("day" => 1, "month" => 1, "year" => 1));
		if (is_array($args["value"]))
		{
			$val = mktime(0, 0, 0, $args["value"]["month"], $args["value"]["day"], $args["value"]["year"]);
		}
		else
		{
			$val = $args["value"];
		}
		$year_from = isset($args["year_from"]) ? $args["year_from"] : date("Y") - 5;
		$year_to = isset($args["year_to"]) ? $args["year_to"] : date("Y") + 5;

		if ($disabled)
		{
			$name = array ("name" => $args["name"], "disabled" => true);
		}
		else
		{
			$name = $args["name"];
		}

		return $selector->gen_edit_form($name, $val, $year_from, $year_to, true);
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
		if(isset($alt))
		{
			$ret.=" alt='$alt'";
		}
		if(isset($title))
		{
			$ret.=" title='$title'";
		}
		if(isset($class))
		{
			$ret.=" class='$class'";
		}
		return $ret.">";
	}

	/*
		$args
			url - url, kuhu peale klikki peaks browser suuna võtma
			target - kus freimis peax avanema
			onClick - onClick aktsioon
			title - Kui mouse hoverib peal, siis mis info juttu näidata
			caption - tekst mida näeb kasutaja
	*/
	function href($args = array())
	{
		extract($args);
		$target = isset($target) ? " target='$target' " : "";
		$onClick = isset($onClick) ? " onClick='$onClick' " : "";
		$title = isset($title) ? " alt='$title' title='$title' " : "";
		return "<a href='$url' $target $title $onClick>$caption</a>";
	}

	////
	// 
	//
	//
	function popup($arr = array())
	{
		extract($arr);
		$arr["onClick"] = "javascript:window.open('$url', '".$target.
		"', 'toolbar=".($toolbar ? "yes" : "no").
		",directories=".($directories ? "yes" : "no").
		",status=".($status ? "yes" : "no").
		",location=".($location ? "yes" : "no").
		",resizable=".($resizable ? "yes" : "no").
		",scrollbars=".($scrollbars ? "yes" : "no").
		",menubar=".($menubar ? "yes" : "no").
		",height=".($height ? $height : 400).
		",width=".($width ? $width : 400).
		"');return false;'";
		if($no_link)
		{
			return $arr["onClick"];
		}
		return html::href($arr);
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

	function get_change_url($oid, $params = array(), $caption = false)
	{
		if (!$this->can("view", $oid))
		{
			return "";
		}
		$obj = &obj($oid);
		$params["id"] = $obj->id();
		$retval = $this->mk_my_orb("change", $params, $obj->class_id());
		if($caption)
		{
			$retval = html::href(array(
				"url" => $retval,
				"caption" => $caption
			));
		}
		return $retval;
	}

	function get_new_url($class_id, $parent, $params = array(), $caption = false)
	{
		$params["parent"] = $parent;
		$retval =  $this->mk_my_orb("new", $params, $class_id);
		if($caption)
		{
			$retval = html::href(array(
				"url" => $retval,
				"caption" => $caption
			));
		}
		return $retval;
	}
};
?>
