<?php

define("COMP_STATUS_PROPOSED",1);
define("COMP_STATUS_CONFIRMED",2);
define("YEAR", 3600*24*370);	// sekundeid 1s aastas

classload("file");

class competitions extends aw_template
{
	function competitions()
	{
		$this->init("competitions");

		$this->status_codes = array(
			COMP_STATUS_PROPOSED => "Soovitatud",
			COMP_STATUS_CONFIRMED => "Kinnitatud",
		);

		$this->votes = array(
			"0" => "-",
			"1" => "1",
			"2" => "2",
			"3" => "3",
			"4" => "4",
			"5" => "5",
			"6" => "6",
			"7" => "7",
			"8" => "8",
			"9" => "9",
			"10" => "10"
		);

		$this->easyvotes = array(
			"0" => "-",
			"1" => "1",
			"2" => "2",
			"3" => "3",
			"4" => "4",
			"5" => "5",
		);
	}

	function is_admin_user()
	{
		if (in_array($this->cfg["admin_group"],aw_global_get("gidlist")))
		{
			return true;
		}
		return false;
	}

	function mk_list($arr)
	{
		extract($arr);

		if (!$this->is_admin_user() && $my_list != 1)		
		{
			$cons = " WHERE status > ".COMP_STATUS_PROPOSED;
			$this->read_template("list.tpl");
		}
		else
		{
			if ($my_list == 1)
			{
				$cons = " WHERE proposed_by = '".aw_global_get("uid")."'";
			}
			else
			{
				$cons = "";
			}
			$this->read_template("list_admin.tpl");
		}

		$this->db_query("SELECT * FROM competitions $cons");
		while ($row = $this->db_next())
		{
			$status = $this->status_codes[$row["status"]];
			if (time() > $row["vote_end"] && $row["vote_end"] > YEAR && $row["status"] == COMP_STATUS_CONFIRMED)
			{
				$status = "L&otilde;ppenud";
			}
			else
			if (time() > $row["end"] && $row["end"] > YEAR && $row["status"] == COMP_STATUS_CONFIRMED)
			{
				$status = "H&auml;&auml;letamisel";
			}
			else
			if (time() > $row["start"] && $row["start"] > YEAR && $row["status"] == COMP_STATUS_CONFIRMED)
			{
				$status = "K&auml;imas";
			}

			$start = "&nbsp;";
			if ($row["start"] > YEAR)
			{
				$start = $this->time2date($row["start"],2);
			}

			$end = "&nbsp;";
			if ($row["end"] > YEAR)
			{
				$end = $this->time2date($row["end"],2);
			}
			$vote_end = "&nbsp;";
			if ($row["vote_end"] > YEAR)
			{
				$vote_end = $this->time2date($row["vote_end"],2);
			}
			$this->vars(array(
				"name" => $row["name"],
				"status" => $status,
				"start" => $start,
				"end" => $end,
				"vote_end" => $vote_end,
				"proposed_by" => $row["proposed_by"],
				"id" => $row["id"],
				"view" => $this->mk_my_orb("view", array("id" => $row["id"])),
				"change" => $this->mk_my_orb("change", array("id" => $row["id"]))
			));

			$hs ="";
			if (time() > $row["start"] && $row["status"] >= COMP_STATUS_PROPOSED)
			{
				$hs = $this->parse("HAS_STARTED");
			}
			$this->vars(array(
				"HAS_STARTED" => $hs
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"submit" => $this->mk_reforb("submit_list", array()),
			"add" => $this->mk_my_orb("add", array())
		));
		return $this->parse();
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array())
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		$uid = aw_global_get("uid");
		if ($id)
		{
			// siin tuleks kontrollida ka et kas on muutmise 6igust sellele kombile
			if ($date_start["year"])
			{
				$start = mktime($date_start["hour"],$date_start["minute"],0,$date_start["month"],$date_start["day"],$date_start["year"]);
			}
			if ($date_end["year"])
			{
				$end = mktime($date_end["hour"],$date_end["minute"],0,$date_end["month"],$date_end["day"],$date_end["year"]);
			}
			if ($date_vote_end["year"])
			{
				$vote_end = 
				mktime($date_vote_end["hour"],$date_vote_end["minute"],0,$date_vote_end["month"],$date_vote_end["day"],$date_vote_end["year"]);
			}

			if ($confirmed == 1)
			{
				$ss = "status = ".COMP_STATUS_CONFIRMED.",";
			}
			else
			{
				$ss = "status = ".COMP_STATUS_PROPOSED.",";
			}

			$this->db_query("UPDATE competitions SET
				$ss
				name = '$name',
				content = '$content',
				start = '$start',
				raskus = '$raskus',
				end = '$end',
				vote_end = '$vote_end'
				WHERE id = '$id'");
		}
		else
		{
			// lisame
			$id = $this->db_fetch_field("SELECT max(id) as id FROM competitions","id")+1;
			$this->db_query("INSERT INTO competitions (id,name,content,status,proposed_by) VALUES('$id','$name','$content',".COMP_STATUS_PROPOSED.",'$uid')");
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$comp = $this->get_comp($id);
		$uid = aw_global_get("uid");
		if (!($comp["proposed_by"] == $uid || $this->is_admin_user()))
		{
			$this->raise_error("No access to change this competition", true);
		}

		$this->read_template("change.tpl");
		
		load_vcl("date_edit");
		$de = new date_edit(0);
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
			"hour" => "",
			"minute" => "",
		));
		// siin tuleb vastavalt sellele, mis staadiumis comp on, parsida vastav sub, kus saab statust m22rata jne
		if ($comp["start"] > YEAR)
		{
			$start = $this->time2date($comp["start"],2);
		}
		if ($comp["end"] > YEAR)
		{
			$end = $this->time2date($comp["end"],2);
		}
		if ($comp["vote_end"] > YEAR)
		{
			$vote_end = $this->time2date($comp["vote_end"],2);
		}
		//kas on raske v6i mitte.
		if($comp['raskus']==2)
		{
			$iisi='';
			$heavy=' selected="selected"';
		}
		else
		{
			$iisi=' selected="selected"';
			$heavy='';
		}


		$this->vars(array(
			"name" => $comp["name"],
			"start" => $start,
			"end" => $end,
			"vote_end" => $vote_end,
			"proposed_by" => $comp["proposed_by"],
			"id" => $comp["id"],
			"confirmed" => checked($comp["status"] > COMP_STATUS_PROPOSED),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"date_start" => $de->gen_edit_form("date_start",$comp["start"],2001,2005,true),
			"date_end" => $de->gen_edit_form("date_end",$comp["end"],2001,2005,true),
			"date_vote_end" => $de->gen_edit_form("date_vote_end",$comp["vote_end"],2001,2005,true),
			"content" => $comp["content"],
			"list" => $this->mk_my_orb("list", array()),
			"accepted" => ($comp["status"] == COMP_STATUS_CONFIRMED ? "Jah" : "Ei"),
			"kergem_sel" => $iisi,
			"raskem_sel" => $heavy
		));

		if ($this->is_admin_user())
		{
			$this->vars(array(
				"IS_ADMIN" => $this->parse("IS_ADMIN")
			));
		}
		else
		{
			$this->vars(array(
				"NO_ADMIN" => $this->parse("NO_ADMIN")
			));
		}
		return $this->parse();
	}

	function get_comp($id)
	{
		$this->db_query("SELECT * FROM competitions WHERE id = $id");
		return $this->db_next();
	}
	
	function vcomment($arr)
	{
		extract($arr);

		$this->read_template("vcomment.tpl");
		
		$cid=(int)$cid;
		
		$this->db_query('select id, comment from comp_solutions where id="'.$cid.'"');
		
		$row=$this->db_next();

		$this->vars(array(
			'cid' => $row['id'],
			'comment' => nl2br(htmlentities($row['comment']))
		));
		return $this->parse();
	}


	function toplist()
	{
		$this->db_query('select t3.user as lahendaja, SUM(t1.vote) as punkte, t3.id from comp_votes as t1, competitions as t2, comp_solutions as t3 where t1.sol_id=t3.id and t1.comp_id=t2.id and t2.status="2" and t2.vote_end<now() group by lahendaja order by punkte desc');

		$this->read_template('toplist.htt');
		$i=1;
		while($row=$this->db_next())
		{
			$this->vars(array(
				koht => $i,
				neim => $row['lahendaja'],
				punkte => $row['punkte']
			));
			$kala.=$this->parse('NIMED');
			$i++;
		}
		
		$this->vars(array(
		'NIMED' => $kala	
		));
		return $this->parse();

	}
	

	function view($arr)
	{
		extract($arr);
		$this->read_template("view.tpl");

		$row = $this->get_comp($id);

		if ($row["start"] > YEAR)
		{
			$start = $this->time2date($row["start"],2);
		}
		if ($row["end"] > YEAR)
		{
			$end = $this->time2date($row["end"],2);
		}
		if ($row["vote_end"] > YEAR)
		{
			$vote_end = $this->time2date($row["vote_end"],2);
		}
		if($row['raskus']==2)
		{
			$raskus='Raskem';
		}
		else
		{
			$raskus='Kergem';
		}
		
		//voting jaoks siuke muutuja
		$r_aste=$row['raskus'];

		$this->vars(array(
			"name" => $row["name"],
			"content" => $row["content"],
			"proposed_by" => $row["proposed_by"],
			"commentlink" => $row["id"],
			"start" => $start,
			"end" => $end,
			"vote_end" => $vote_end,
			"list" => $this->mk_my_orb("list", array()),
			"reforb" => $this->mk_reforb("submit_view", array("id" => $id)),
			"raskus" => $raskus
		));

		if ($this->is_active($row))
		{
			$ent = $this->get_user_solution($id);

			$this->vars(array(
				"comment" => $ent["comment"],
				"entry" => $ent["url"],
			));
			if ($ent["file_id"])
			{
				$this->vars(array(
					"ENTRY" => $this->parse("ENTRY")
				));
			}
			$this->vars(array(
				"IN_PROGRESS" => $this->parse("IN_PROGRESS")
			));
		}
		else
		if ($this->is_voting($row))
		{
			$uid = aw_global_get("uid");
			// loeme k6ik selle v6istluse entryd ja logitud kasutaja poolt neile antud h22led. sql ruulib.
			$this->db_query("SELECT comp_solutions.*,comp_votes.vote as vote FROM comp_solutions LEFT JOIN comp_votes ON (comp_votes.sol_id = comp_solutions.id AND comp_votes.user = '$uid') WHERE comp_solutions.comp_id = $id");
			
			if($r_aste==2)
			{
				$vote_array=$this->votes;
			}
			else
			{
				$vote_array=$this->easyvotes;
			}

			
			while ($row = $this->db_next())
			{echo 
				//v2listame subjektiivsed arvamused, eks :)
				$this->vars(array(
					//"user" => $row["user"],
					"user" => 'v&otilde;istleja',
					"when" => $this->time2date($row["tm"],2),
					//"when" => $row["tm"],
					"sol_url" => $row["url"],
					"commentlink" => $row["id"],
					"sol_id" => $row["id"],
					"votes" => $this->picker($row["vote"], $vote_array)
				));

				$cv = "";
				if ($row["user"] != $uid)
				{
					$cv = $this->parse("CAN_VOTE");
				}
				$this->vars(array("CAN_VOTE" => $cv));
				$l.=$this->parse("IN_VOTING.VOTE_LINE");
			}
			$this->vars(array(
				"VOTE_LINE" => $l
			));
			$this->vars(array(
				"IN_VOTING" => $this->parse("IN_VOTING"),
			));
		}
		else
		if ($this->is_closed($row))
		{
			$uid = aw_global_get("uid");
			$cnt = 1;
			// loeme k6ik selle v6istluse entryd ja summeerime nende hinded kokku
			$this->db_query("SELECT comp_solutions.*,sum(comp_votes.vote) as vote FROM comp_solutions LEFT JOIN comp_votes ON comp_votes.sol_id = comp_solutions.id WHERE comp_solutions.comp_id = $id  GROUP BY comp_votes.sol_id ORDER BY vote DESC");
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"user" => $row["user"],
					"when" => $this->time2date($row["tm"],2),
					"sol_url" => $row["url"],
					"commentlink" => $row["id"],
					"vote" => $row["vote"],
					"cnt" => $cnt++
				));

				$l.=$this->parse("CLOSED.VOTE_LINE");
			}
			$this->vars(array(
				"VOTE_LINE" => $l
			));
			$this->vars(array(
				"CLOSED" => $this->parse("CLOSED"),
			));
		}
		return $this->parse();
	}

	function is_active($row)
	{
		if ($row["status"] == COMP_STATUS_CONFIRMED && time() > $row["start"] && time() < $row["end"])
		{
			return true;
		}
		return false;
	}

	function is_voting($row)
	{
		if ($row["status"] == COMP_STATUS_CONFIRMED && time() > $row["end"] && time() < $row["vote_end"])
		{
			return true;
		}
		return false;
	}

	function is_closed($row)
	{
		if ($row["status"] == COMP_STATUS_CONFIRMED && time() > $row["vote_end"])
		{
			return true;
		}
		return false;
	}

	function get_user_solution($comp_id)
	{
		$this->db_query("SELECT * FROM comp_solutions WHERE comp_id = $comp_id AND user = '".aw_global_get("uid")."'");
		return $this->db_next();
	}

	function submit_view($arr)
	{
		extract($arr);

		$uid = aw_global_get("uid");
		$row = $this->get_comp($id);
		if ($this->is_active($row))
		{
			// uploadime tsippe
			global $entry, $entry_type,$entry_name;
			$sol = $this->get_user_solution($id);

			if ($entry != "" && $entry != "none" && is_uploaded_file($entry))
			{
				$fp = fopen($entry, "r");
				$fc = fread($fp,filesize($entry));
				fclose($fp);

				$f = new file;
				$fdat = $f->put(array("store" => "fs", "filename" => $entry_name, "type" => $entry_type,"content" => $fc));
				$furl = aw_ini_get("baseurl")."/files.".aw_ini_get("ext")."/id=".$fdat["id"]."/".urlencode($entry_name);
			}

			if ($sol)
			{
				//uuenda
				$this->db_query("UPDATE comp_solutions SET url = '$furl', file_id = '".$fdat["id"]."', comment = '$comment' WHERE id = ".$sol["id"]);
			}
			else
			{
				// lisa
				$sid = $this->db_fetch_field("SELECT max(id) as id FROM comp_solutions", "id")+1;

				$this->db_query("INSERT INTO comp_solutions(id,tm,comp_id,user,url,comment,file_id)
					VALUES($sid,unix_timestamp(now()),$id,'$uid','$furl','$comment','".$fdat["id"]."')");
			}
		}
		else
		if ($this->is_voting($row))
		{
			// v6udime
			if (is_array($votes))
			{
				foreach($votes as $vid => $vval)
				{
					$this->db_query("SELECT * FROM comp_votes WHERE sol_id = $vid AND user = '$uid'");
					$row = $this->db_next();
					if ($row)
					{
						$this->db_query("UPDATE comp_votes SET vote = '$vval' WHERE sol_id = '$vid' AND user = '$uid'");
					}
					else
					{
						$this->db_query("INSERT INTO comp_votes(comp_id,sol_id,vote,user)
							VALUES($id,'$vid','$vval','$uid')");
					}
				}
			}
		}

		return $this->mk_my_orb("view", array("id" => $id));
	}
}
?>