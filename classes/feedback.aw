<?php
class db_feedback extends aw_template 
{
	function db_feedback() 
	{
		$this->init("");
		$this->tekst[0] = "Nii nagu vaja, lihtne ja arusaadav.";
		$this->tekst[1] = "Arusaadav, kuid konarlik. Vajab toimetamist.";
		$this->tekst[2] = "Liiga keeruline või tehniline.";
		$this->tekst[3] = "Liiga pealiskaudne või mittemidagiütlev.";

		$this->kujundus[0] = "Laitmatu kujundus.";
		$this->kujundus[1] = "Liiga hall ja üksluine kujundus.";
		$this->kujundus[2] = "Liiga pealetükkiv kujundus.";

		$this->struktuur[0] = "Laitmatu menüüde loogika.";
		$this->struktuur[1] = "Menüüde loogika hästi taibatav.";
		$this->struktuur[2] = "Menüüde loogika kehvasti taibatav.";

		$this->tehnika[1] = "Sait on piisaval määral kiire.";
		$this->tehnika[2] = "Sait on liiga aeglane.";
		$this->tehnika[4] = "Olen saanud ühe veateate (ERROR).";
		$this->tehnika[8] = "Olen saanud rohkem kui ühe veateate.";

		$this->ala[0] = "Tehnika ja tootmine";
		$this->ala[1] = "Arvutid ja Internet";
		$this->ala[2] = "Müük ja turundus";
		$this->ala[3] = "Muu";
	}
	
	function add_feedback($data) 
	{
		$this->quote($data);
		extract($data);
		$msg = "
Dokument: http://aw.struktuur.ee/?section=$docid\n
Pealkirjaga: $title\n
\n
Arvamust avaldas:\n
Nimi: $eesnimi $perenimi
$gender
E-post: $mail
Koduleht: $homepage
Tegevusala: " . $this->ala[$ala] . "

Tekst: " . $this->tekst[$tekst] . "
Kujundus: " . $this->kujundus[$kujundus] . "
Struktuur: " . $this->struktuur[$struktuur] . "
Tehnika: 
";
		$tsum = 0;
		if (is_array($tehnika)) 
		{
			while(list($k,$v) = each($tehnika)) 
			{
				$msg .= $this->tehnika[$v] . "\n";
				$tsum = $tsum + $v;
			};
		};

		$msg .= "\nTäpsustav tekst:\n" . $more . "\n";
		if ($wantsnews1) 
		{
			$msg .= "\nSoovib dokumendi teemaga seonduvat e-uudiskirja\n";
		};
		if ($wantsnews2) 
		{
			$msg .= "\nSoovib teadet e-postile, kui toimuvad suuremad muudatused Struktuur Meedia kodulehel.\n";
		};
		if ($wantsfeedback) 
		{
			$msg .= "\nSoovin oma arvamusele/ettepanekule personaalset tagasisidet\n";
		};
		$headers = "From: $eesnimi $perenimi <$mail>";
		$t = time();
		$q = "INSERT INTO feedback (docid,time,tekst,kujundus,
					struktuur,tehnika,ala, more, gender,
					eesnimi, perenimi, mail, homepage,
					wantsnews1, wantsnews2, wantsfeedback)
					VALUES('$docid','$t','$tekst','$kujundus','$struktuur',
						'$tsum','$ala','$more','$gender',
						'$eesnimi','$perenimi','$mail',
						'$homepage','$wantsnew1','$wantsnew2',
						'$wantsfeedback')";
		$this->db_query($q);
		mail("content@struktuur.ee","FB-SA-007 \"$title\"",$msg,"$headers");
	}
};
?>
