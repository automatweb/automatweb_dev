<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/repeater.aw,v 2.4 2001/07/26 16:49:57 duke Exp $
class repeater {
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
				printf("%d | %s | %s<br>",$key,date("d-m-Y H:i",$start),date("d-m-Y H:i",$end));
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
				// siin kontekstis t�hendab pwhen
				// "Iga kuu nendel n�dalatel"
				// pwhen 2 on 
				// "Iga kuu nendel p�evadel"
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

			// n�dalad
			case "2":
				$newvec = array();
				// siin kontekstis t�hendab pwhen seda, et m�rgitud oli 
				// "Kordub iga n�dala nendel p�evadel:"
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


			// p�evad
			case "1":
				// well, that should be easy. since day has really only 1 field - skip
				// kaime kogu vektori l�bi.
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
// siia peab moodustuma array koigist objektidest
/*
[aasta]->[kuu]->[p�ev] .. hmmm?
nope, pigem tuleks v�lja arvutada vahemikud, mil eventid esinevad.
ja siis vastavalt sellele neid kas splittida voi joinida.

// tyybid tuleks ette anda kindlas jarjekorras. aastad enne, siis kuud.

step 1)
	koigepealt loome hiigelsuure timegapi eventi algusp�evast
	kuni repeaterite lopuni.

step 2)
	Impordime repeateri tyypi year (8,4)
	tsykkel yle koigi aastate, alates eventi algusest kuni

<terryf> mmkay. j2relikult seal on 365*2036 riad?
<terryf> mmkay. j2relikult seal on 365*36 riad?
<duke9> eiei
<terryf> ai ok
<terryf> jah
<terryf> seal on aint korduse p2evad
<duke9> tegemist on arvupaaridega
<duke9> ntx .. koigepealt on 14.juuni.2001 - 31.dets.2037
<duke9> timestampidena
<duke9> siis .. kui oli defineeritud repeater tyypi aasta
<duke9> ja ntx skip = 1 (ehk iga aasta)
<duke9> well
<duke9> siis ei ole vaja midagi teha
<duke9> aga kui oli skip 2 (yle yhe aasta)
<terryf> aaaa
<terryf> ok
<duke9> siis tuleb selle arvupaari asemele (2037-2001) / 2 
<terryf> nyyd ma saan aru
<duke9> arvupaari
<terryf> jaja. kaval
<duke9> mis siis algavad vastava aasta esimese sekundiga .. ja lopevad viimasega
<duke9> vmt.
<terryf> mhmh. a see k6lab p2ris eduliselt
<duke9> nuh .. ja kui aasta tyypi repeaterit yldse polnud, siis me ei tee midagi. .. eks
<duke9> edasi tulevad kuud.
<duke9> kaime need vektorid labi
<duke9> ja kui vaja, siis teeme jalle tykkideks.
<duke9> kui ntx ainult repeater tyypi paev .. ja skip on 1
<duke9> siis .. on meil ikkagi tegemist yheainsa arvupaariga
<duke9> seega .. ysna effektiivne?
<duke9> samas .. kui skip on 2 .. 
<duke9> siis on neid arvupaare .. ikka paris palju
<duke9> (2037 - 2001) * 365 / 2
<duke9> ehk 6570
<duke9> samas timestamp on palju .. 4 baiti?
<terryf> a nuh, kui sa nad kuidagi mingisse lahedasse andmestruktuuri torkad, kust kiirelt vahemikku leida saab, siis pole hullu ju..
<duke9> noh .. jah
<duke9> ja pealegi .. see 6570 on worst case scenario
<terryf> mhmh
<duke9> repeater, mis kestab igavesti (yeah, right) iga 2 p�eva tagant
<duke9> koik teised votavad vahem ruumi.
<terryf> ei, v2ga kuul
<terryf> t6esti. I'm impressed
<duke9> ja noh. kui meil 64 bitised arvutid levima hakkavad, siis voib uue algoritmi valja moelda.
<duke9> hm. thank you. I guess.

*/
?>
