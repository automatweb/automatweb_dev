<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/repeater.aw,v 2.8 2005/04/21 08:33:11 kristo Exp $
class repeater 
{
	////
	// !Konstruktor.
	// argumendid:
	// start(timestamp) - eventi algus
	// end(timestamp) - repeaterite lopp.
	function repeater($args = array())
	{
		// koigepealt moodustame yhe pika vektori
		#extract($args);
		#$this->_init($start,$end);
		lc_load("definition");
	}

	////
	// !this is used to set the window on the timeline we are interested in.
	// to handle the matters correctly we need to know when the whole timeline
	// started and ended as well as when the event itself started. There is
	// no need to know when the even ends, since it can not last longer than
	// a single day. Actually now, when I think of it, this seems as a serious
	// problem. What I want an event that spans multiple days?
	function init($rep_start,$start,$end)
	{
		$this->rep_start = $rep_start;
		// nullime kogu timeline
		$this->vector = array();
		// koigepealt on vaja leida vektori algus
		// ja lopusekundid
		list($sdd,$sdm,$sdy) = split("-",date("d-m-Y",$start));
		$st = mktime(0,0,0,$sdm,$sdd,$sdy);

		list($edd,$edm,$edy) = split("-",date("d-m-Y",$end));
		$et = mktime(23,59,59,$edm,$edd,$edy);
	
		// ja salvestame selle vektori
		$this->vector[] = array("$st" => "$et");

		$this->virgin = 1;
	}

	function show_vector()
	{
		if (!$this->virgin)
		{
			foreach($this->vector as $key => $dt)
			{
				list($start,$end) = each($dt);
				printf("%d | %s | %s<br />",$key,date("d-m-Y H:i",$start),date("d-m-Y H:i",$end));
			};
		};
	}

	function get_vector()
	{
		if (!$this->virgin)
		{
			return $this->vector;
		};
	}

	////
	//! This is used to remove the parts of the timeline we are not interested in.
	// The processing should start either at the start of the window or at the start
	// of the repeater, depending on whichever is first.
	
	// if the former is the case, we need to start the calculations from the start of the 
	// event, not from the start of the window and then skip until we are at the start of
	// the window. Otherwise we will mess up completely.

	function handle($args = array())
	{
		$this->virgin = 0;
		extract($args);
		switch($type)
		{
			// aasta
			case "4":
				if ($skip <= 1)
				{
					// do nothing
				}
				else
				{
					reset($this->vector);
					list(,$dt) = each($this->vector);
					list($st,$et) = each($dt);
					$start = date("Y",$st);
					$end = date("Y",$et);
					$newvec = array();
					for ($i = $start; $i <= $end; $i = $i+$skip)
					{
						if ($pwhen)
						{
							$mlist = explode(",",$pwhen);
							foreach($mlist as $mnum)
							{
								if (($mnum > 0) && ($mnum < 13))
								{
									$sx = mktime(0,0,0,$mnum,1,$i);
									$newstart = ($sx < $st) ? $st : $sx;
									$sy = mktime(23,59,59,$mnum + 1,0,$i);
									$newend = $sy;
									if ($sy > $st)
									{
										$newvec[] = array($newstart => $newend);
									};
								};
							};
						}
						else
						{
							$sx = mktime(0,0,0,1,1,$i);
							$newstart = ($sx < $st) ? $st : $sx;
							$newend = mktime(23,59,59,12,31,$i);
							$newvec[] = array($newstart => $newend);
						};
					};
					$this->vector = $newvec;
				};
				break;
			// kuud
			case "3":
				// siin kontekstis tähendab pwhen
				// "Iga kuu nendel nädalatel"
				// pwhen 2 on 
				// "Iga kuu nendel päevadel"
				if ($pwhen2)
				{
					$newvec = array();
					$active_days = explode(",",$pwhen2);
					foreach($this->vector as $numpair)
					{
						list($st,$et) = each($numpair);
						$cnt = $this->get_day_diff($st,$et);
						list($d1,$m1,$y1) = split("-","d-m-Y",$st);
						for ($i = 0; $i <= $cnt; $i++)
						{
							$sx = mktime(0,0,0,$m1,$d1 + $i,$y1);
							$newstart = ($st > $sx) ? $st : $sx;
							$sy = mktime(23,59,59,$m1,$d1 + $i,$y1);
							$daycode = date("j",$sx);
							if (in_array($daycode,$active_days))
							{
								if ($sy > $st)
								{
									$newvec[] = array($newstart => $sy);
								};
							};
						};
									
					}
					$this->vector = $newvec;
				}
				break;

			// nädalad
			case "2":
				$newvec = array();
				// siin kontekstis tähendab pwhen seda, et märgitud oli 
				// "Kordub iga nädala nendel päevadel:"
				if ($pwhen)
				{
					$active_days = explode(",",$pwhen);
					foreach($this->vector as $numpair)
					{
						list($st,$et) = each($numpair);
						// I need to know what day of the week this is.
						$cnt = $this->get_day_diff($st,$et);
						list($d1,$m1,$y1) = split("-",date("d-m-Y",$st));
						$wc = 0;
						for ($i = 0; $i <= $cnt; $i++)
						{
							$sx = mktime(0,0,0,$m1,$d1 + $i,$y1);
							$newstart = ($st > $sx) ? $st : $sx;
							$sy = mktime(23,59,59,$m1,$d1 + $i,$y1);
							$daycode = date("w",$sx);
							if ($daycode == 0)
							{
								$daycode = 7;
								$wc++;
							};
							if (in_array($daycode,$active_days))
							{
								if ($sy > $st)
								{
									$newvec[] = array($newstart => $sy);
								};
							};
						};
					};
					$this->vector = $newvec;
				}
				break;


			// päevad
			case "1":
				// well, that should be easy. since day has really only 1 field - skip
				// kaime kogu vektori läbi.
				$newvec = array();
				foreach($this->vector as $numpair)
				{
					list($st,$et) = each($numpair);
					// we use that format, so we can handle skips in days more correctly.
					$start = date("Ymd",$st);
					$end = date("Ymd",$et);
					list($d1,$m1,$y1) = split("-",date("d-m-Y",$st));
					list($d2,$m2,$y2) = split("-",date("d-m-Y",$et));
					if ($skip > 0)
					{
						$cnt = $this->get_day_diff($st,$et);
						for ($i = 0; $i <= $cnt; $i = $i + $skip)
						{
							$sx = mktime(0,0,0,$m1,$d1 + $i,$y1);
							$newstart = ($st > $sx) ? $st : $sx;
							$sy = mktime(23,59,59,$m1,$d1 + $i,$y1);
							$newend = $sy;
							if ($sy > $st)
							{
								$newvec[] = array($newstart => $newend);
							};
						};
					};
				};
				$this->vector = $newvec;
				break;
						
		};

	}

	function _get_year($timestamp)
	{
		list($dd,$mm,$yyyy) = split("-",date("d-m-Y",$timestamp));
		$st = mktime(0,0,0,1,1,$yyyy);
		$et = mktime(0,0,0,12,31,$yyyy);
		return array($st,$et);
	}

	function get_day_diff($time1,$time2)
	{
		$diff = $time2 - $time1;
		$days = (int)($diff / 86400);
		return $days;
	}

};
?>
