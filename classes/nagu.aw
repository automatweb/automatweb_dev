<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/nagu.aw,v 2.1 2001/05/25 22:53:32 duke Exp $

classload("periods","images", "msgboard","config");

class nagu extends aw_template
{
	// need kuuluvad keelekonstantide alla ja on mdx seal ka defineeritud
	var $kuud = array(1 => "Jaanuar", 2 => "Veebruar", 3 => "M&auml;rts", 4 => "Aprill", 5 => "Mai", 6 => "Juuni",7 => "Juuli", 8 => "August", 9 => "September", 10 => "Oktoober", 11 => "November", 12 => "Detsember");

	function nagu()
	{
		$this->tpl_init("nagu");
		$this->db_init();
	}

	function list_n2od($oid)
	{
		$this->read_template("n2gu_list.tpl");

		$t = new db_periods($oid);
		$t->clist();
		while ($row = $t->db_next())
		{
			$this->vars(array("name" => $row[description],"id" => $row[id]));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l));
		return $this->parse();
	}

	function list_tyybid($id)
	{
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_NAGU." AND name='$id'");
		if (!$nagu = $this->db_next())
		{
			$noid = $this->new_object(array("name" => $id, "class_id" => CL_NAGU));
			// v6tame eelmise n2dala textid
			$t = new db_periods($GLOBALS["per_oid"]);
			$per = $t->get_prev($id,$GLOBALS["per_oid"]);
			$enagu = $this->get($per);
			$nagu[text2] = $enagu[text2];
			$nagu[text3] = $enagu[text3];
			$ns = serialize($nagu);
			$this->upd_object(array("oid" => $noid, "comment" => $ns));
		}

		$this->vars(array("id" => $id));
		$nagu = unserialize($nagu[comment]);

		$this->read_template("list_tyybid.tpl");
		for ($i=0; $i < $nagu[num]; $i++)
		{
			$c = $nagu[content][$i];
			$this->vars(array("num" => $i, "nimi" => $c[eesnimi]." ".$c[kesknimi]." ".$c[perenimi], "imgurl" => $c[imgurl],"sugu" => ($c[sugu] == "m" ? "Mees" : "Naine"),"synd" => $c[byear]."-".$this->kuud[$c[bmonth]]."-".$c[bday],"fid" => $i));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l,"id" => $id));
		return $this->parse();
	}

	function change($id,$fid)
	{
		$this->read_template("change.tpl");

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_NAGU." AND name='$id'");
		if (!$nagu = $this->db_next())
			$this->new_object(array("name" => $id, "class_id" => CL_NAGU));

		$nagu = unserialize($nagu[comment]);

		$t = new db_config;
		$con = unserialize($t->get_simple_config("nagu_ooc"));
		$tmp = $con[content];
		uasort($tmp,__con_sort);
		reset($tmp);
		while (list($k,$v) = each($tmp))
			$ar[$k] = $v[name];

		$c = $nagu[content][$fid];
		$this->vars(array("fid" => $fid, 
											"eesnimi" => $c[eesnimi],
											"kesknimi" => $c[kesknimi],
											"perenimi" => $c[perenimi], 
											"imgurl" => $c[imgurl],
											"man" => ($c[sugu] == "m" ? "CHECKED" : ""),
											"woman" => ($c[sugu] == "n" ? "CHECKED" : ""),
											"byear" => $c[byear], 
											"bmonth" => $this->option_list($c[bmonth],$this->kuud), 
											"bday" => $this->option_list($c[bday],$this->mk_paevad()),
											"occ" => $this->multiple_option_list($c[occ],$ar),
											"id" => $id,
											"estonian" => $c[estonian] == 1 ? "CHECKED" : ""));
		return $this->parse();
	}

	function submit($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_NAGU." AND name='$id'");
		$nagu = $this->db_next();
		$nagu_oid = $nagu[oid];
		$nagu = unserialize($nagu[comment]);

		$text = str_replace("\\","",$text);
		$text2 = str_replace("\\","",$text2);
		$text3 = str_replace("\\","",$text3);
		$text = str_replace("'","\"",$text);
		$text2 = str_replace("'","\"",$text2);
		$text3 = str_replace("'","\"",$text3);

		if ($type == "textonly")
		{
			$nagu[text] = $text;
			$nagu[text2] = $text2;
			$nagu[text3] = $text3;
		}
		else
		{
			if ($fid == -1)
				$fid = ++$nagu[num];

			$nagu[content][$fid][eesnimi] = $forname;
			$nagu[content][$fid][kesknimi] = $midname;
			$nagu[content][$fid][perenimi] = $surname;
			$nagu[content][$fid][sugu] = $gender;
			$nagu[content][$fid][byear] = $byear % 2050;
			$nagu[content][$fid][bmonth] = $bmonth;
			$nagu[content][$fid][bday] = $bday;
			$nagu[content][$fid][estonian] = $estonian;

			if (is_array($occ))
			{
				reset($occ);
				$a = array();
				while (list(,$v) = each($occ))
					$a[$v]=$v;
			}
			$nagu[content][$fid][occ] = $a;

			global $img,$img_type;
			if ($img != "none")
			{
				if ($nagu[content][$fid][imgurl] != "")
				{
					// change
					$t = new db_images;
					$ar = $t->_replace(array("filename" => $img, "file_type" => $img_type,"poid" => $nagu[content][$fid][imgid]));
				}
				else
				{
					// add
					$t = new db_images;
					$ar = $t->_upload(array("filename" => $img, "file_type" => $img_type,"oid" => $id));
				}
				$pid = $ar[id];
				$img = $t->get_img_by_id($pid);
				$nagu[content][$fid][imgid] = $img[id];
				$nagu[content][$fid][imgurl] = $img[url];
			}
		}

		$ns = serialize($nagu);
		$this->db_query("update objects set comment = '$ns' WHERE oid = $nagu_oid");
		return $fid;
	}

	function get_active($oid)
	{
		$t = new db_periods($oid);
		$per = $t->get_active_period();

		$nagu = $this->get($per);
		return $nagu;
	}

	function get($per)
	{
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_NAGU." AND name='$per'");
		$nagu = $this->db_next();
		return unserialize($nagu[comment]);
	}

	function get_prev_winner($oid,$actper)
	{
		$t = new db_periods($oid);
		$per = $t->get_prev($actper,$oid);

		$nagu = $this->get($per);
		$big = 0;
		$win = 0;
		for ($i=0; $i < $nagu[num]; $i++)
		{
			if ($nagu[content][$i][votes] > $big)
			{
				$big = $nagu[content][$i][votes];
				$win = $i;
			}
		}
		return $nagu[content][$win];
	}

	function show($oid)
	{
		$this->read_template("show.tpl");
		$t = new db_periods($oid);
		$per = $t->get_active_period();
		$nagu = $this->get($per);

		$winner = $this->get_prev_winner($oid,$per);
		if ($winner[imgurl] == "")
		{
			$winner[imgurl] = $GLOBALS["baseurl"]."/images/transa.gif";
		}

		$this->vars(array("text" => str_replace("#nimi#", $winner[nimi], str_replace("\n", "<Br>",$nagu[text])), "winnerurl" => $winner[imgurl]));

		$com = new msgboard;

		$votes_total = 0;
		for ($i=0; $i < $nagu[num]; $i++)
			$votes_total += $nagu[content][$i][votes];

		$_tmp = $nagu[content];
		for ($i=0; $i < $nagu[num]; $i++)
		{
			$tmp = $_tmp[$i];
			$percent = sprintf("%2.2f%%",$votes_total > 0 ? ($tmp[votes] / $votes_total)*100.0 : 0);
			
			if ($tmp[imgurl] == "")
			{
				$tmp[imgurl] = $GLOBALS["baseurl"]."/images/transa.gif";
			}
			$this->vars(array("num"					=> $i, 
												"imgurl"			=> $tmp[imgurl], 
												"sel"					=> ($i == 0 ? "CHECKED" : ""),
												"name"				=> ($tmp[eesnimi]." ".$tmp[kesknimi]." ".$tmp[perenimi]),
												"period"			=> $per, 
												"num_comments" => $com->get_num_comments("nn_".$per."_".$i),
												"percent"			=> $percent));
			$p1.= $this->parse("PERSON1");
			$p2.=$this->parse("PERSON2");
		}
		$imu = $nagu[content][0][imgurl];
		if ($imu == "")
		{
			$imu = $GLOBALS["baseurl"]."/images/transa.gif";
		}
		$this->vars(array("PERSON1" => $p1, "PERSON2" => $p2,"text2" => str_replace("\n", "<br>",$nagu[text2]),"text3" => str_replace("\n", "<br>",$nagu[text3]),"firsturl" => $imu,"sel_section" => "nn_".$per."_0"));

		return $this->parse();
	}

	function submit_vote($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		if ($section != "")
		{
			$t = new db_periods($GLOBALS["per_oid"]);
			$per = $t->get_active_period();
			$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_NAGU." AND name='$per'");
			$nagu = $this->db_next();
			$nagu_oid = $nagu[oid];
			$nagu = unserialize($nagu[comment]);

			$nagu[content][$vote][votes]++;
			$ns = serialize($nagu);
			$this->db_query("update objects set comment = '$ns' WHERE oid = $nagu_oid");

			$arr[parent] = 0;
			$t = new msgboard;
			$t->submit_add($arr);
			$this->flush_cache();
			return true;
		}
		else
		{
			return false;
		}
	}

	function change_ooc($id)
	{
		$this->read_template("change_ooc.tpl");
		
		$t = new db_config;
		$con = unserialize($t->get_simple_config("nagu_ooc"));

		for ($i=0; $i < $con[num]+1; $i++)
		{
			$this->vars(array("text" => $con[content][$i][name],"ord" => $con[content][$i][ord],"num" => $i));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l,"id" => $id));
		return $this->parse();
	}

	function submit_ooc($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		$t = new db_config;
		$con = unserialize($t->get_simple_config("nagu_ooc"));

		$cnt = 0;
		for ($i=0; $i < $con[num]+1; $i++)
		{
			$var = "text_".$i;
			$var2 = "ord_".$i;
			if (!($i== $con[num] && $$var == ""))
			{
				$con2[content][$cnt][name] = $$var;
				$con2[content][$cnt][ord] = $$var2;
				$con2[arr][$cnt] = $$var;
				$cnt++;
			}
		}
		$con2[num]=$cnt;

		$ar = serialize($con2);
		$t->set_simple_config("nagu_ooc",$ar);
	}

	function add($id)
	{
		$t = new db_config;
		$con = unserialize($t->get_simple_config("nagu_ooc"));

		$this->read_template("change.tpl");
		$this->vars(array("id" => $id, 
											"eesnimi" => "","kesknimi" => "","perenimi" => "", "man" =>"", "woman" => "", "byear" => "", "bday" => $this->option_list(0,$this->mk_paevad()), "occ" => $this->multiple_option_list(array(),$con[arr]), "imgurl" => "", "fid" => -1,"bmonth" => $this->option_list(0,$this->kuud),"estonian" => ""));
		return $this->parse();
	}

	function delete($id, $fid)
	{
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_NAGU." AND name='$id'");
		$nagu = $this->db_next();
		$nagu_oid = $nagu[oid];
		$nagu = unserialize($nagu[comment]);

		for ($i=$fid; $i < $nagu[num]; $i++)
			$nagu[content][$i] = $nagu[content][$i+1];

		$nagu[num]--;
		$ns = serialize($nagu);
		$this->db_query("update objects set comment = '$ns' WHERE oid = $nagu_oid");
	}

	function delete_ooc($nid)
	{
		$t = new db_config;
		$con = unserialize($t->get_simple_config("nagu_ooc"));

		for ($i=$nid; $i < $con[num]; $i++)
			$con[content][$i] = $con[content][$i+1];

		$con[num]--;
		$ns = serialize($con);
		$t->set_simple_config("nagu_ooc",$ns);
	}

	function texts($id)
	{
		$this->read_template("texts.tpl");

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_NAGU." AND name='$id'");
		$nagu = $this->db_next();
		$nagu_oid = $nagu[oid];
		$nagu = unserialize($nagu[comment]);

		$this->vars(array("text" => $nagu[text], "text2" => $nagu[text2],"text3" => $nagu[text3],"id" => $id));
		return $this->parse();
	}

	function mk_paevad()
	{
		for ($i=1; $i < 32; $i++)
		{
			if ($i < 10)
				$d = "0".$i;
			else
				$d = $i;
			$parr[$d] = $d;
		}
		return $parr;
	}
}

function __nagu_sort($a,$b)
{
	$v1 = ($a[votes]) ? $a[votes] : 0;
	$v2 = ($b[votes]) ? $b[votes] : 0;
	print "<!-- comparing $a[eesnimi] to $b[perenimi] -->\n";
	if ($v1 == $v2)
	{
		$retval = 0;
	}
	elseif($v1 > $v2)
	{
		$retval = -1;
	}
	else
	{
		$retval = 1;
	};
	return $retval;
}
		

function __con_sort($a,$b)
{
   if ($a[ord] == $b[ord]) return 0;
   return ($a[ord] < $b[ord]) ? -1 : 1;
}

?>
