<?php
// klassile antakse ette "unix timestamp", ta konverdib
// selle ajaühikuteks, ning tagastab nende muutmiseks
// sobivad vormielemendid
class date_edit 
{
	// vormielementide nimed saavad olema kujul
	// $varname[month] $varname[day] jne.

	// kui aega ette ei anta, siis kuvame selleks kuupäeva
	// ööpäev hiljem dokumendi avamisest. See on üsna suvaline muidugi
	function date_edit($varname,$timestamp = "+24h") 
	{
		$this->init($varname,$timestamp);
	}

	function set($field,$value)
	{
		$this->$field = $value;
	}

	function init($varname,$timestamp)
	{
		$this->varname = $varname;
		if ($timestamp == "+24h") 
		{
			$timestamp = time() + (60 * 60 * 24);
		};
		$this->timestamp = $timestamp;
		$this->step = 5;
		$this->classid="";
	}

	////
	// !Sets the layout of the date editor
	// default is to show a select element
	// set_layout(array("year" => "textbox")) makes it a textbox instead
	function set_layout($args = array())
	{
		$this->layout = $args;
	}

	function configure($fields) 
	{
		// millised väljad ja millises järjekorras kuvame
		// ja mida me nende captioniteks näitame
		//    month = Kuu
		//    
		if (!is_array($fields)) 
		{
			return false;
		};
		if (isset($fields["classid"]))
		{
			$this->classid=$fields["classid"];
			unset($fields["classid"]);
		};
		$this->fields = $fields;
	}

	function gen_edit_form($varname,$timestamp,$range1 = 2001 , $range2 = 2004,$add_empty = false) 
	{
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
		if ($this->classid != "")
		{
			$clid="class=\"$this->classid\"";
		};
		if ($this->timestamp == -1)
		{
			$year = $month = $day = $hour = $minute = -1;
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
					$retval .= sprintf("<select $clid name='%s[year]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
					for ($i = $range1; $i <= $range2; $i++) 
					{
						$retval .= sprintf("<option value='%s' %s>%s</option>\n",$i,selected($i == $year),$i);
					};
					$retval .= "</select>\n";
					break;

				case "year_textbox":
					$retval .= sprintf("<input type='text' name='%s[year]' size='4' maxlength='4' value='$year'>\n",$this->varname);
					break;
					
				case "month":
					$retval .= sprintf("<select $clid name='%s[month]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
			        $mnames = array("1" => LC_M1,
						"2" => LC_M2,
						"3" => LC_M3,
						"4" => LC_M4,
						"5" => LC_M5,
						"6" => LC_M6,
						"7" => LC_M7,
						"8" => LC_M8,
						"9" => LC_M9,
						"10" => LC_M10,
						"11" => LC_M11,
						"12" => LC_M12
					);
					while(list($mk,$mv) = each($mnames)) 
					{
						$retval .= sprintf("<option value='%s' %s>%s</option>\n",$mk,selected($mk == $month),$mv);
					};
					$retval .= "</select>\n";
					break;

				case "month_textbox":
					$retval .= sprintf("<input type='text' name='%s[month]' size='2' maxlength='2' value='$month'>\n",$this->varname);
					break;
					
				case "day":
					$retval .= sprintf("<select $clid name='%s[day]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
					for ($i = 1; $i <= 31; $i++) 
					{
						$retval .= sprintf("<option value='%s' %s>%s</option>\n",$i,selected($i == $day),$i);
					};
					$retval .= "</select>\n";
					break;
				
				case "day_textbox":
					$retval .= sprintf("<input type='text' name='%s[day]' size='2' maxlength='2' value='$day'>\n",$this->varname);
					break;

				case "hour":
					$retval .= sprintf("<select $clid name='%s[hour]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
					for ($i = 0; $i <= 23; $i++) 
					{
						$retval .= sprintf("<option value='%s'%s>%02d</option>\n",$i,selected($i == $hour),$i);
					};
					$retval .= "</select> :\n";
					break;
				
				case "hour_textbox":
					$retval .= sprintf("<input type='text' name='%s[hour]' size='2' maxlength='2' value='$hour'>\n",$this->varname);
					break;

				case "minute":
					$retval .= sprintf("<select $clid name='%s[minute]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='---'>---</option>\n";
					}
					$step = ($this->minute_step) ? $this->minute_step : 1;
					for ($i = 0; $i <= 59; $i = $i + $step) 
					{
						$retval .= sprintf("<option value='%s'%s>%02d</option>\n",$i,selected($i == $minute),$i);
					};
					$retval .= "</select>\n";
					break;
				
				case "minute_textbox":
					$retval .= sprintf("<input type='text' name='%s[minute]' size='2' maxlength='2' value='$minute'>\n",$this->varname);
					break;
			}; // end switch
		}; // end while
		return $retval;
	} // end gen_edit_form

	function get_timestamp($var)
	{
		if ($var['month'] == '---' || $var['day'] == '---' || $var['year'] == '---')
		{
			return -1;
		}
		return mktime($var["hour"], $var["minute"], $var["second"], $var["month"], $var["day"], $var["year"]);
	}
}; // end class
