<?php
// klassile antakse ette "unix timestamp", ta konverdib
// selle ajaühikuteks, ning tagastab nende muutmiseks
// sobivad vormielemendid
class date_edit {
	// vormielementide nimed saavad olema kujul
	// $varname[month] $varname[day] jne.

	// kui aega ette ei anta, siis kuvame selleks kuupäeva
	// ööpäev hiljem dokumendi avamisest. See on üsna suvaline muidugi
	function date_edit($varname,$timestamp = "+24h") {
		$this->init($varname,$timestamp);
	}

	function set($field,$value)
	{
		$this->$field = $value;
	}

	function init($varname,$timestamp)
	{
		$this->varname = $varname;
		if ($timestamp == "+24h") {
			list($msec,$sec) = split(" ",microtime());
			$timestamp = $sec + (60 * 60 * 24);
		};
		$this->timestamp = $timestamp;
		$this->step = 5;
		$this->classid="";
	}

	function configure($fields) {
		// millised väljad ja millises järjekorras kuvame
		// ja mida me nende captioniteks näitame
		//    month = Kuu
		//    
		if (!is_array($fields)) {
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
		if ($timestamp == "+24h") {
			list($msec,$sec) = split(" ",microtime());
			$timestamp = $sec + (60 * 60 * 24);
		};
		if ($timestamp == "+48h") {
			list($msec,$sec) = split(" ",microtime());
			$timestamp = $sec + (2 * 60 * 60 * 24);
		};
		$this->varname = $varname;
		$this->timestamp = $timestamp;
		if ($this->classid != "")
		{
			$clid="class=\"$this->classid\"";
		};
//		echo "ts = $timestamp deit = ",date("Y n j H i",$this->timestamp),"<Br>";
		list($year,$month,$day,$hour,$minute) = split(" ",date("Y n j H i",$this->timestamp));
		reset($this->fields);
		$retval = "";
		while(list($k,$v) = each($this->fields)) {
			switch($k) {
				case "year":
					$retval .= sprintf("<select $clid name='%s[year]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='0'>---</option>\n";
					}
					for ($i = $range1; $i <= $range2; $i++) {
						$sel = ($i == $year) ? "selected" : "";
						$retval .= sprintf("<option value='%s'%s>%s</option>\n",$i,$sel,$i);
					};
					$retval .= "</select>\n";
					break;
					
				case "month":
					$retval .= sprintf("<select $clid name='%s[month]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='0'>---</option>\n";
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
							"12" => LC_M12);
					while(list($mk,$mv) = each($mnames)) {
						$sel = ($mk == $month) ? "selected" : "";
						$retval .= sprintf("<option value='%s'%s>%s</option>\n",$mk,$sel,$mv);
					};
					$retval .= "</select>\n";
					break;
					
				case "day":
					$retval .= sprintf("<select $clid name='%s[day]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='0'>---</option>\n";
					}
					for ($i = 1; $i <= 31; $i++) {
						$sel = ($i == $day) ? "selected" : "";
						$retval .= sprintf("<option value='%s'%s>%s</option>\n",$i,$sel,$i);
					};
					$retval .= "</select>\n";
					break;

				case "hour":
					$retval .= sprintf("<select $clid name='%s[hour]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='0'>---</option>\n";
					}
					for ($i = 0; $i <= 23; $i++) {
						$sel = ($i == $hour) ? "selected" : "";
						$retval .= sprintf("<option value='%s'%s>%02d</option>\n",$i,$sel,$i);
					};
					$retval .= "</select> :\n";
					break;

				case "minute":
					$retval .= sprintf("<select $clid name='%s[minute]'>\n",$this->varname);
					if ($add_empty)
					{
						$retval.= "<option value='0'>---</option>\n";
					}
					$step = ($this->minute_step) ? $this->minute_step : 1;
					for ($i = 0; $i <= 59; $i = $i + $step) {
						$sel = ($i == $minute) ? "selected" : "";
						$retval .= sprintf("<option value='%s'%s>%02d</option>\n",$i,$sel,$i);
					};
					$retval .= "</select>\n";
					break;
			}; // end switch
		}; // end while
		return $retval;
	} // end gen_edit_form
}; // end class
