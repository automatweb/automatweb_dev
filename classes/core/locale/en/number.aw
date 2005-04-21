<?php
// $Header: /home/cvs/automatweb_dev/classes/core/locale/en/number.aw,v 1.4 2005/04/21 08:48:49 kristo Exp $
// en.aw - english localization
class number
{
	function get_lc_number($number)
	{
		$number = (int)$number;
		$singles = array("","one","two","three","four","five","six","seven","eight","nine");
		$jargud1 = array(" million "," thousand "," ");

		// check if there is a dot in the number and if so, separate the last part

		$special = array(
			"10" => "ten",
			"11" => "eleven",
			"12" => "twelve",
			"13" => "thirteen",
			"14" => "fourteen",
			"15" => "fifteen",
			"16" => "sixteen",
			"17" => "seventeen",
			"18" => "eighteen",
			"19" => "nineteen",
			"20" => "twenty",
			"30" => "thirty",
			"40" => "forty",
			"50" => "fifty",
			"60" => "sixty",
			"70" => "seventy",
			"80" => "eighty",
			"90" => "ninety",
		);
		
		$res = "";
		if (preg_match("/([0-9]{0,3}?)([0-9]{0,3}?)([0-9]{1,3}?)$/",$number,$m))
		{
			foreach(array_splice($m,1) as $jrk => $token)
			{
				if ((int)$token === 0)
				{
					continue;
				};

				$pieces = explode(":", wordwrap((int)$token, 1, ":", 1));
				$size = count($pieces);
			
				// hundreds first and get rid of them too
				if ($size == 3)
				{
					$res .= $singles[reset($pieces)] . " hundred ";
					array_shift($pieces);
					$size--;
				};

				if ($size == 2)
				{
					$newtoken = $pieces[0] . $pieces[1];
					if (isset($special[$newtoken]))
					{
						$res .= $special[$newtoken];
					}
					else
					{
						$res .= $special[$pieces[0]."0"] . " ";
						$res .= $singles[end($pieces)];
					};
				}
				else
				{
					$res .= $singles[end($pieces)];
				};

				//$res .= end($pieces) == 1 ? $jargud1[$jrk] : $jargud2[$jrk];
				$res .= $jargud1[$jrk];
			};
		}
		else
		{
			return "ENOCLUE";
		}
		return $res;
	}
	
	function get_lc_sum($number)
	{
		$lastpart = substr($number,strpos($number,".")+1);
		$number = str_replace(",","",$number);
		$res = $this->get_lc_number($number);

		$currency1 = aw_global_get("currency1");
		$currency2 = aw_global_get("currency2");

		if (!empty($lastpart))
		{
			$res .= " $currency1 and " . $lastpart . " " .$currency2;
		};

		return $res;

	}
};
?>
