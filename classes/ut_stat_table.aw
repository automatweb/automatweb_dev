<?php

class ut_stat_table extends aw_template
{
	function ut_stat_table()
	{
		$this->init("ut_stat_table");
	}

	function draw($section)
	{
		if (is_array($section))
		{
			extract($section);
		}
		$this->read_template("table.tpl");

		$datamap = array();

		$this->db_query("
			SELECT  
				ut_tudengid.oppevorm as oppevorm,
				ut_tudengid.oppeaste as oppeaste,
				ut_struktuurid.osakond as teaduskond,
				ut_oppekavad.nimetus as oppekava
			FROM ut_tudengid 
			LEFT JOIN ut_struktuurid ON ut_struktuurid.id = ut_tudengid.struktuur 
			LEFT JOIN ut_oppekavad ON ut_oppekavad.id = ut_tudengid.oppekava
			WHERE ut_struktuurid.osakond != '' AND ut_struktuurid.osakond IS NOT NULL
			GROUP BY oppevorm,oppeaste, ut_struktuurid.osakond,ut_oppekavad.nimetus
			ORDER BY ut_struktuurid.osakond
		");
		while ($row = $this->db_next())
		{
			$datamap[$row["teaduskond"]][$row["oppevorm"]][$row["oppeaste"]][] = $row["oppekava"];
		}

		foreach($datamap as $teaduskond => $tkdat)
		{
			$res1 = "";
			$res2 = "";
			$res3 = "";
			$res4 = "";
			foreach($tkdat as $oppevorm => $ovdat)
			{
				foreach($ovdat as $oppeaste => $opkdat)
				{
					foreach($opkdat as $oppekava)
					{
						$this->vars(array(
							"link" => $this->mk_my_orb("list", array(
								"teaduskond" => urlencode($teaduskond),
								"oppevorm" => urlencode($oppevorm),
								"oppeaste" => urlencode($oppeaste),
								"oppekava" => urlencode($oppekava),
								"section" => $section
							)),
							"name" => $oppekava
						));
						if ($oppevorm == "statsionaar")
						{
							if ($oppeaste == "bakalaureus")
							{
								$res1 .= $this->parse("RES_LINK");
							}
							else
							{
								$res2 .= $this->parse("RES_LINK2");
							}
						}
						else
						{
							if ($oppeaste == "bakalaureus")
							{
								$res3 .= $this->parse("RES_LINK3");
							}
							else
							{
								$res4 .= $this->parse("RES_LINK4");
							}
						}
					}
				}
			}
			$this->vars(array(
				"teaduskond_nimi" => $teaduskond,
				"RES_LINK" => $res1,
				"RES_LINK2" => $res2,
				"RES_LINK3" => $res3,
				"RES_LINK4" => $res4
			));
			$l.=$this->parse("TEADUSKOND_LINE");
		}
		$this->vars(array(
			"TEADUSKOND_LINE" => $l
		));
		return $this->parse();
	}

	function orb_list($arr)
	{
		extract($arr);
		$this->read_template("list.tpl");

		$q = "
			SELECT
				CONCAT(ut_tudengid.pnimi,', ',ut_tudengid.enimi) as nimi,
				ut_tudengid.aasta as aasta
			FROM ut_tudengid
			LEFT JOIN ut_struktuurid ON ut_struktuurid.id = ut_tudengid.struktuur 
			LEFT JOIN ut_oppekavad ON ut_oppekavad.id = ut_tudengid.oppekava
			WHERE 
				ut_struktuurid.osakond = '$teaduskond' AND 
				ut_tudengid.oppevorm = '$oppevorm' AND
				ut_tudengid.oppeaste = '$oppeaste' AND
				ut_oppekavad.nimetus = '$oppekava'
			GROUP BY nimi
			ORDER BY nimi
		";
		
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"cnt" => ++$cnt,
				"nimi" => $row["nimi"], 
				"aasta" => $row["aasta"]
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"teaduskond" => $teaduskond,
			"oppeaste" => $oppeaste
		));
		return $this->parse();
	}
}
?>