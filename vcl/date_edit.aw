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

	function init($varname,$timestamp)
	{
		$this->varname = $varname;
		if ($timestamp == "+24h") {
			list($msec,$sec) = split(" ",microtime());
			$timestamp = $sec + (60 * 60 * 24);
		};
		$this->timestamp = $timestamp;
		$this->step = 5;
	}

	function configure($fields) {
		// millised väljad ja millises järjekorras kuvame
		// ja mida me nende captioniteks näitame
		//    month = Kuu
		//    
		if (!is_array($fields)) {
			return false;
		};
		$this->fields = $fields;
	}

	function gen_edit_form($varname,$timestamp) {
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
//		echo "ts = $timestamp deit = ",date("Y n j H i",$this->timestamp),"<Br>";
		list($year,$month,$day,$hour,$minute) = split(" ",date("Y n j H i",$this->timestamp));
		reset($this->fields);
		$retval = "";
		while(list($k,$v) = each($this->fields)) {
			switch($k) {
				case "year":
					$range1 = 2001;
					$range2 = 2004;
					$retval .= sprintf("<select name='%s[year]'>\n",$this->varname);
					for ($i = $range1; $i <= $range2; $i++) {
						$sel = ($i == $year) ? "selected" : "";
						$retval .= sprintf("<option value='%s'%s>%s</option>\n",$i,$sel,$i);
					};
					$retval .= "</select>\n";
					break;
					
				case "month":
					$retval .= sprintf("<select name='%s[month]'>\n",$this->varname);
				        $mnames = array("1" => "jaanuar",
							"2" => "veebruar",
							"3" => "märts",
							"4" => "aprill",
							"5" => "mai",
							"6" => "juuni",
							"7" => "juuli",
							"8" => "august",
							"9" => "september",
							"10" => "oktoober",
							"11" => "november",
							"12" => "detsember");
					while(list($mk,$mv) = each($mnames)) {
						$sel = ($mk == $month) ? "selected" : "";
						$retval .= sprintf("<option value='%s'%s>%s</option>\n",$mk,$sel,$mv);
					};
					$retval .= "</select>\n";
					break;
					
				case "day":
					$retval .= sprintf("<select name='%s[day]'>\n",$this->varname);
					for ($i = 1; $i <= 31; $i++) {
						$sel = ($i == $day) ? "selected" : "";
						$retval .= sprintf("<option value='%s'%s>%s</option>\n",$i,$sel,$i);
					};
					$retval .= "</select>\n";
					break;

				case "hour":
					$retval .= sprintf("<select name='%s[hour]'>\n",$this->varname);
					for ($i = 0; $i <= 23; $i++) {
						$sel = ($i == $hour) ? "selected" : "";
						$retval .= sprintf("<option value='%s'%s>%02d</option>\n",$i,$sel,$i);
					};
					$retval .= "</select> :\n";
					break;

				case "minute":
					$retval .= sprintf("<select name='%s[minute]'>\n",$this->varname);
					for ($i = 0; $i <= 59; $i = $i + 1) {
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
