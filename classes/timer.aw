<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/timer.aw,v 2.1 2001/05/24 21:11:40 duke Exp $
// klass taimerite jaoks
class aw_timer {
	var $timers; // siin sailitame koiki taimereid
	function aw_timer($precision = 4) {
		// precision - mitu kohta peale koma
		$this->precision = $precision;
		$this->timers = array();
		$this->start("__global");
	}
	
	function count($name) {
		if ($this->counters[$name])
		{
			$this->counters[$name]++;
		}
		else
		{
			$this->counters[$name] = 1;
		};
	}

	function start($name) {
		// kui sellenimeline taimer on olemas
		if ($this->is_defined($name)) {
			if ($this->is_running($name)) {
				// kui taimer juba käib, siis me ei tee midagi
				return true;
			} else {
				$this->timers[$name][started] = $this->get_time();
				$this->timers[$name][running] = 1;
			};
		// sellist taimerit pole olemas
		} else {
			$this->timers[$name][running] = 1;
			$this->timers[$name][started] = $this->get_time();
			$this->timers[$name][elapsed] = 0;
		};
	}

	function stop($name) {
		if ($this->is_defined($name)) {
			if ($this->is_running($name)) {
				$this->timers[$name][elapsed] += ($this->get_time() - $this->timers[$name][started]);
				$this->timers[$name][running] = 0;
			} else {
				return false;
			};
		} else {
			return false;
		};
	}

	// peatab koik taimerid ja tagastab nad arrays
	//  $arr[taimerinimi] = kulutatud_aeg
	function summaries() {
		krsort($this->timers);
		while(list($timer,) = each($this->timers)) {
			$this->stop($timer);
		};
		reset($this->timers);
		$retval = array();
		$fstr = "%0." . $this->precision . "f";
		while(list($timer,$val) = each($this->timers)) {
			$retval[$timer] = sprintf($fstr,$val[elapsed]);
		};
		if (is_array($this->counters))
		{
		reset($this->counters);
		while(list($counter,$value) = each($this->counters))
		{
			$xval = "counter_" . $counter;
			$retval[$xval] = $value;
		};
		};
		return $retval;
	}

	// tagastab aja epohhi algusest sekundites
	function get_time() {
		list($micro,$sec) = split(" ",microtime());
		return $sec + $micro;
	}

	// kas taimer töötab?
	function is_running($name) {
		return ($this->timers[$name][running] == 1);
	}
		

	// kas taimer on defineeritud?
	function is_defined($name) {
		return ($this->timers[$name]);
	}
}
?>
