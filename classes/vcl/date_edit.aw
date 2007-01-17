<?php
// klassile antakse ette "unix timestamp", ta konverdib
// selle aja�hikuteks, ning tagastab nende muutmiseks
// sobivad vormielemendid
class date_edit
{
	// vormielementide nimed saavad olema kujul
	// $varname[month] $varname[day] jne.

	// kui aega ette ei anta, siis kuvame selleks kuup�eva
	// ��p�ev hiljem dokumendi avamisest. See on �sna suvaline muidugi

	function date_edit($varname = "", $timestamp = "+24h")
	{
		$this->init($varname, $timestamp);
		// default to all shown
		$this->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
			"hour" => "",
			"minute" => "",
		));
	}

	// well, you can set these but they aren't used <-- taiu
	function set($field, $value)
	{
		$this->$field = $value;
	}

	function init($varname, $timestamp)
	{
		$this->varname = $varname;
		if ($timestamp == "+24h")
		{
			$timestamp = time() + (60 * 60 * 24);
		};
		$this->timestamp = $timestamp;
		$this->step = 5;
		$this->classid = "";
	}

	////
	// !Sets the layout of the date editor
	// default is to show a select element
	// set_layout(array("year" => "textbox")) makes it a textbox instead
	
	// sets the layout flag but this isn't used in anywhere.. therefore isn't  api function <-- taiu
	function set_layout($args = array())
	{
		$this->layout = $args;
	}

	/**
		@attrib params=name api=1
		@param classid optional type=int
		@comment
	**/
	function configure($fields)
	{
		// millised v�ljad ja millises j�rjekorras kuvame
		// ja mida me nende captioniteks n�itame
		//    month = Kuu
		//
		if (!is_array($fields))
		{
			return false;
		};
		if (isset($fields["classid"]))
		{
			$this->classid = $fields["classid"];
			unset($fields["classid"]);
		};
		$this->fields = $fields;
	}

	/**
		@attrib params=pos api=1
		@param varname
		Sets the varname for the date form that is posted.
		varname[year],varname[month],varname[day],varname[hour],varname[minute]
		@param timestamp optional type=int
		Sets the time to be selected(unix timestamp). Default is current time +24h
		@param range1 optional type=int
		Sets the start year(default is 2003)
		@param range2 optional type=int
		Sets the end year(default is 2010)
		@add_empty optional type=bool
		If set tu true, adds an '---' item  and selects it(adds for everythind.. selects only for year)
		Default is false. If this is set range1 and range2 must be manually set.
		@comment
		Generates the date edit html code accorndig to options
		@returns
		The form elements html code to be printed on page
	**/
	function gen_edit_form($varname, $timestamp, $range1 = 2003, $range2 = 2010, $add_empty = false, $buttons = false)
	{
		if (is_array ($varname))
		{
			$textsize = isset ($varname["textsize"]) ? 'style="font-size: ' . (string) $varname["textsize"] . ';"' : "";
			$disabled = isset ($varname["disabled"]) ? "disabled" : "";
			$varname = $varname["name"];
		}
		else
		{
			$disabled = "";
			$textsize = "";
		};

		if ($timestamp == "+24h")
		{
			$timestamp = time() + (60 * 60 * 24);
		};
		if ($timestamp == "+48h")
		{
			$timestamp = time() + (2 * 60 * 60 * 24);
		};
		$this->varname = $varname;
		$this->timestamp = $timestamp;
		$clid = "";
		if ($this->classid != "")
		{
			$clid="class=\"$this->classid\"";
		};
		// support for ISO-8601 date format
		list($year,$month,$day) = sscanf($this->timestamp,"%4d-%2d-%2d");
		if ($this->timestamp == -1)
		{
			$year = $month = $day = $hour = $minute = -1;
		}
		elseif ($year && $month && $day)
		{

		}
		else
		{
			list($year,$month,$day,$hour,$minute) = split(" ",date("Y n j H i",$this->timestamp));
		}

		$retval = "";
		foreach( $this->fields as $k => $v)
		{
			switch($k)
			{
				case "year":
					$retval .= sprintf("<select $clid name='%s[year]' $disabled $textsize>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
					if ($range1 > $range2)
					{
						for ($i = $range1; $i >= $range2; $i--)
						{
							$retval .= sprintf("<option value='%s' %s>%s</option>\n",$i,selected($i == $year),$i);
						};
					}
					else
					{
						for ($i = $range1; $i <= $range2; $i++)
						{
							$retval .= sprintf("<option value='%s' %s>%s</option>\n",$i,selected($i == $year),$i);
						};
					}
					$retval .= "</select>\n";
					break;

				case "year_textbox":
					if ($year == -1)
					{
						$year = "";
					}
					$retval .= sprintf("<input type='text' name='%s[year]' size='4' maxlength='4' value='$year' $disabled $textsize>\n",$this->varname);
					break;

				case "month":
					$retval .= sprintf("<select $clid name='%s[month]' $disabled $textsize>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
					$mnames = array(
						"1" => t("Jaanuar"),
						"2" => t("Veebruar"),
						"3" => t("M&auml;rts"),
						"4" => t("Aprill"),
						"5" => t("Mai"),
						"6" => t("Juuni"),
						"7" => t("Juuli"),
						"8" => t("August"),
						"9" => t("September"),
						"10" => t("Oktoober"),
						"11" => t("November"),
						"12" => t("Detsember")
					);
					// wtf is this mon_for thingie?
					if(isset($this->mon_for))
					{
						$mnames = array();
						$tmp = range(1, 12);
						foreach($tmp as $val)
						{
							$mnames[$val < 10 ? "0".$val : $val] = $val < 10 ? "0".$val : $val;
						}
					}
					foreach($mnames as $mk => $mv)
					{
						$retval .= sprintf("<option value='%s' %s>%s</option>\n",$mk,selected($mk == $month && $this->timestamp != -1),$mv);
					};
					$retval .= "</select>\n";
					break;

				case "month_textbox":
					if ($month == -1)
					{
						$month = "";
					}
					$retval .= sprintf("<input type='text' name='%s[month]' size='2' maxlength='2' value='$month' $disabled $textsize>\n",$this->varname);
					break;

				case "day":
					$retval .= sprintf("<select $clid name='%s[day]' $disabled $textsize>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
					for ($i = 1; $i <= 31; $i++)
					{
						$retval .= sprintf("<option value='%s' %s>%s</option>\n",$i,selected($i == $day && $this->timestamp != -1),$i);
					};
					$retval .= "</select>\n";
					break;

				case "day_textbox":
					if ($day == -1)
					{
						$day = "";
					}
					$retval .= sprintf("<input type='text' name='%s[day]' size='2' maxlength='2' value='$day' $disabled $textsize>\n",$this->varname);
					break;

				case "hour":
					$retval .= sprintf("<select $clid name='%s[hour]' $disabled $textsize>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
					for ($i = 0; $i <= 23; $i++)
					{
						$retval .= sprintf("<option value='%s' %s>%02d</option>\n",$i,selected($i == $hour && $this->timestamp != -1),$i);
					};
					$retval .= "</select> :\n";
					break;

				case "hour_textbox":
					$retval .= sprintf("<input type='text' name='%s[hour]' size='2' maxlength='2' value='$hour' $disabled $textsize>\n",$this->varname);
					break;

				case "minute":
					$retval .= sprintf("<select $clid name='%s[minute]' $disabled $textsize>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
					$step = isset($this->minute_step) ? $this->minute_step : 1;
					for ($i = 0; $i <= 59; $i = $i + $step)
					{
						$retval .= sprintf("<option value='%s' %s>%02d</option>\n",$i,selected($i <= $minute && $i +  $step > $minute && $this->timestamp != -1),$i);
					};
					$retval .= "</select>\n";
					break;

				case "minute_textbox":
					$retval .= sprintf("<input type='text' name='%s[minute]' size='2' maxlength='2' value='$minute' $disabled $textsize>\n",$this->varname);
					break;
			}; // end switch
		}; // end while

		if (is_admin() || $buttons === true)
		{
			// make those date button images configurable
			$date_choose_img_url = aw_ini_get('date_edit.date_choose_img_url');
			if (empty($date_choose_img_url))
			{
				$date_choose_img_url = '/automatweb/images/icons/class_126.gif';
			}
			$date_clear_img_url = aw_ini_get('date_edit.date_clear_img_url');
			if (empty($date_clear_img_url))
			{
				$date_clear_img_url = '/automatweb/images/icons/delete.gif';
			}

			$retval .= "<a href='javascript:void(0)' onClick='aw_date_edit_show_cal(\"".$this->varname."\");' id='".$this->varname."' name='".$this->varname."' >"; 
			$retval .= "<img src='".aw_ini_get('baseurl').$date_choose_img_url."' border='0'></a> ";	
			$retval .= "<a href='javascript:void(0)' onClick='aw_date_edit_clear(\"".$this->varname."\");'><img src='".aw_ini_get('baseurl').$date_clear_img_url."' border=0></a>";
		}

		return $retval;
	} // end gen_edit_form
	
	/**
		@attrib params=name api=1
		@param year required type=int
		sets the year
		@param month required type=int
		sets the month
		@param day required type=int
		sets the day
		@param hour required type=int
		sets the hour
		@param minute required type=int
		sets the minute
		@param second required type=int
		sets the second
		@comment
		Generates unix timestamp according to given values
		@returns
		Returns Unix timestamp
	**/
	function get_timestamp($var)
	{
		if ($var['month'] == '---' || $var['day'] == '---' || $var['year'] == '---')
		{
			return -1;
		}
		if (!is_array($var))
		{
			return -1;
		}
		$tmp =  mktime($var["hour"], $var["minute"], $var["second"], $var["month"], $var["day"], $var["year"]);
		return $tmp;
	}
}; // end class
