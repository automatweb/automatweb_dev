<?php
// $Header: /home/cvs/automatweb_dev/classes/mailinglist/Attic/ml_queue.aw,v 1.18 2003/11/27 16:10:53 duke Exp $
// ml_queue.aw - Deals with mailing list queues

classload("messenger");
define("ML_QUEUE_NEW",0);
define("ML_QUEUE_IN_PROGRESS",1);
define("ML_QUEUE_READY",2);
define("ML_QUEUE_SENDING",3);
define("ML_QUEUE_STOPPED",4);

// see aid, mida siin pruugitakse on ml_avoidmids tabeli kirje id
class ml_queue extends aw_template
{
	////
	//! Konstruktor
	function ml_queue()
	{
		$this->init("automatweb/mlist");
		lc_load("definition");

		// queue status
		$this->a_status=array(
			ML_QUEUE_NEW => "uus",
			ML_QUEUE_IN_PROGRESS => "pooleli",
			ML_QUEUE_READY => "valmis",
			ML_QUEUE_SENDING => "hetkel saadab",
			ML_QUEUE_STOPPED => "peatatud"
		);
	}


	////
	//! Händlib "vasakus puus oleva proge" submitti
	function orb_submit_manager($arr)
	{
		$this->dbconf->set_simple_config("ml_form",$arr["form"]);
		$this->dbconf->set_simple_config("ml_search_form",$arr["searchform"]);
		$this->dbconf->set_simple_config("ml_mail_el",$arr["mailel"]);
		return $this->mk_my_orb("queue",array("manager"=>1));
	}


	////
	//! Näitab queuet
	// kui manager=1 siis näitab ka mänegeri
	// kui show on "list" siis näitab ainult listiga $fid seotud itemeid
	// kui show on "mail" siis näitab ainult meiliga $fid seotud itemeid
	function orb_queue($arr)
	{
		extract($arr);
		$this->read_template("manager.tpl");

		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "ml_queue",
		));
		
		if ($show && $show != "all")
		{
			$fid=(int)$fid;
			$name=$this->db_fetch_field("SELECT name FROM objects WHERE oid='$fid'","name");
		};

		$queue_back = aw_global_get("queue_back");
		if ($queue_back)
		{
			$headerarray=array( $queue_back => "Tagasi");
		} 
		else
		{
			$headerarray=array();
		};

		$headerarray['javascript:Do("queue_delete")']="Kustuta";
		$headerarray['javascript:Do("queue_send_now")']="Saada kohe";
		
		switch ($show)
		{
			case "list":
				$headerarray[$this->mk_my_orb("queue",array("show"=>"all","fid"=>$fid))]="Kõik";
				$filt=" WHERE lid = '$fid'";
				$title="(list $name)";
				break;

			case "mail":
				$headerarray[$this->mk_my_orb("queue",array("show"=>"all","fid"=>$fid))]="Kõik";
				$filt=" WHERE mid = '$fid'";
				$title="(meil $name)";
				break;

			case "all":
			default:
				$filt=$title="";
				break;
		};

		$t->define_header("Queue $title",$headerarray);
		$t->parse_xml_def($this->cfg["basedir"] . "/xml/mlist/queue.xml");

		$ml = get_instance("mailinglist/ml_list");
		$lists = $ml->get_lists_and_groups(array());//võta kõik listide & gruppide nimed, et polex vaja iga kord queryda

		$q = "SELECT * FROM ml_queue $filt";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			//echo("<pre>");print_r($row);echo("</pre>");//dbg
			$listname = $lists[$row["lid"].":0"];
			$groupids=explode("|",$row["gid"]);
			$gnames=array();
			foreach ($groupids as $v)
			{
				if ($v != "0" && $v)
				{
					$gnames[]=$lists[$row["lid"].":".$v];
				};
			};

			$row["lid"] = "<a href='javascript:remote(0,450,270,\"".$this->mk_my_orb("queue_change",array("id"=>$row["qid"]))."\");'>$listname</a>";
			if (sizeof($gnames)>0)
			{
				$row["lid"] .= ":".join(",",$gnames);
			};

			$this->save_handle();
			$row["mid"] = $this->db_fetch_field("SELECT name FROM objects WHERE oid='".$row["mid"]."'","name")."(".$row["mid"].")";
			$this->restore_handle();
			if (!$row["patch_size"])
			{
				$row["patch_size"]="kõik";
			};
			$row["delay"]/=60;
			$row["status"]=$this->a_status[$row["status"]];
			$row["protsent"]=$this->queue_ready_indicator($row["position"],$row["total"]);
			$row["vali"]="<input type='checkbox' NAME='sel[]' value='".$row["qid"]."'>";
			$t->define_data($row);
		};

		$t->sort_by();
		$queue=$t->draw();

		if ($manager)
		{
			$title="Meililistid";

			$fb=get_instance("formgen/form_base");
			$flist=$fb->get_list(FTYPE_ENTRY);
			$sflist=$fb->get_list(FTYPE_SEARCH);

			$this->vars(array(
				"form" => $this->picker($this->formid,$flist),
				"searchform" => $this->picker($this->searchformid,$sflist),
				"mailel" => $this->picker($this->mailel,$ml->get_all_varnames()),
			));
			$mparse=$this->parse("MGR");
		};

		$this->vars(array(
			"QUEUE" => $queue,
			"MGR" => $mparse,
			"reforb" => $this->mk_reforb("submit_manager",array(
					"show" => $show,
					"fid" => $fid,
					"manager"=> $manager)),
			));
		return $this->parse();
	}

	////
	//! Näitab queue itemi $id muutmist (reschedulemist)
	function orb_queue_change($arr)
	{
		extract($arr);
		$this->db_query("SELECT * FROM ml_queue WHERE qid='$id'");
		$r=$this->db_next();

		$this->read_template("queue_change.tpl");
		if ($r["status"]==2)
		{
			$this->vars(array("teade" => "Juba täidetud queue objekti ei saa muuta !"));
			$viga = $this->parse("viga");
		} 
		else
		{
			load_vcl("date_edit");
			$date_edit = new date_edit(time());
			$date_edit->configure(array(
				"day" => "",
				"month" => "",
				"year" => "",
				"hour" => "",
				"minute" => "",
				"classid" => "small_button",
			));

			$r["settime"] = $date_edit->gen_edit_form("settime",$r["last_sent"]?$r["last_sent"]:time());
			$r["delay"] /= 60;
			$r["reforb"] = $this->mk_reforb("submit_queue_change",array(
				"id" => $id,
				"ostatus" => $r["status"]
				));
			$this->vars($r);
			$sisu = $this->parse("sisu");
		};

		$this->vars(array(
			"sisu" => $sisu,
			"viga" => $viga
		));

		return $this->parse();
	}

	////
	//! Händleb queue itemi reschedulemist
	function orb_submit_queue_change($arr)
	{
		extract($arr);
		if ($ostatus=="0") //kui on uus, siis muuda algusaega
		{
			$field="start_at";
		} 
		else // muidu muuda viimase saatmise aega
		{
			$field="last_sent";
		};

		switch($timing)
		{
			default:
			case "same":
				$sls="";
				break;

			case "now":
				$sls=",$field='".(time()-$delay*60-1)."' ";
				break;

			case "set":
				$sls=",$field='".mktime($settime["hour"],$settime["minute"],0,$settime["month"],$settime["day"],$settime["year"])."' ";
				break;
		};
		$delay*=60;
		$this->db_query("UPDATE ml_queue SET delay='$delay', patch_size='$patch_size' $sls WHERE qid='$id'");

		$GLOBALS["reforb"]=0;// see on selleks, et ta ei hakkaks kuhugi suunama vaid prindiks skripti välja
		die("<script language='JavaScript'>opener.history.go(0);window.close();</script>");
	}

	////
	//! Kustutab queue itemi $id
	function orb_queue_delete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			$q="";
			foreach($sel as $v)
			{
				$q.=($q?",":"")."'".(int)$v."'";
			};
			$this->db_query("DELETE FROM ml_queue WHERE qid IN ($q)");
		};
		if ($from_mlm)
		{
			return $this->mk_my_orb("change", array("id" => $id), "ml_list_status");
		}
		else
		{
			return $this->mk_my_orb("queue",array(
				"id" => $id,
				"show" => $show,
				"fid" => $fid,
				"manager" => $manager
			));
		}
	}

	////
	//! Märgib itemi $id kohe saatmiseks
	function orb_queue_send_now($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			$q="";
			foreach($sel as $v)
			{
				$q.=($q?",":"")."'".(int)$v."'";
			};
			$delta=time()-2;
			$w="UPDATE ml_queue SET start_at=$delta-delay WHERE qid IN ($q) AND status=0";
			$this->db_query($w);
			$w="UPDATE ml_queue SET last_sent=$delta-delay WHERE qid IN ($q) AND status!=0";
			$this->db_query($w);
		};
		if ($from_mlm)
		{
			return $this->mk_my_orb("change", array("id" => $id), "ml_list_status");
		}
		else
		{
			return $this->mk_my_orb("queue",array(
				"id" => $id,
				"show" => $show,
				"fid" => $fid,
				"manager" => $manager
			));
		}
	}

	////
	//! teeb progress bari
	// tegelt saax seda pitidega teha a siis tekib iga progress bari kohta oma query <img src=
	// see olex overkill kui on palju queue itemeid
	function queue_ready_indicator($osa,$kogu)
	{
		if (!$kogu)
		{
			$p=100;
		} 
		else
		{
			$p=(int)((int)$osa * 100 / (int)$kogu);
		};
		$not_p=100-$p;
		//echo("qri($osa,$kogu)=$p");//dbg
		
		// tekst pane sinna, kus on rohkem ruumi.
		if ($p>$not_p)
		{
			$p1t="<span Style='font-size:10px;font-face:verdana;'><font color='white'>".$p."%</font></span>";
		} 
		else
		{
			$p2t="<span Style='font-size:10px;font-face:verdana;'><font color='black'>".$p."%</font></span>";
		};
		// kommentaar on selleks, et sorteerimine töötaks (hopefully)
		return "<!-- $p --><table bgcolor='#CCCCCC' Style='height:12;width:100%'><tr><td width=\"$p%\" bgcolor=\"blue\">$p1t</td><td width=\"$not_p%\">$p2t</td></tr></table>";
	}

	// vähendab avoidmids tabelis usagec välja ja kui see on 0, siis võtab selle kirje tabelist ära
	function increase_avoidmids_ready($aid)
	{
		$this->save_handle();
		$r=$this->db_fetch_field("SELECT usagec FROM ml_avoidmids WHERE aid='$aid'","usagec");
		//decho("<i>inc_avoidmids $aid r=$r</i><br />");
		if ($r<=1)
		{
			//$this->db_query("DELETE FROM ml_avoidmids WHERE aid='$aid'");
		} 
		else
		{
			$this->db_query("UPDATE ml_avoidmids SET usagec=usagec-1 WHERE aid='$aid'");
		};
		$this->restote_handle();
	}

	////
	//! Processes all active mailing list queues
	// Invoked from the scheduler
	function process_queue($arr)
	{
		set_time_limit(0);
		$sched = get_instance("scheduler");
		$sched->add(array(
			"event" => $this->mk_my_orb("process_queue", array(), "", false, true),
			"time" => time()+120,	// every 2 minutes
		));
		echo "adding scheduler ! <br />\n";
		flush();
		//decho("process_queue:<br />");//dbg
		$tm=time();
		// võta need, mida pole veel üldse saadetud või on veel saata & aeg on alustada
		$this->db_query("SELECT * FROM ml_queue WHERE status IN (0,1) AND start_at<='$tm'");
		echo "select <br />\n";
		flush();
		while ($r = $this->db_next())
		{
			$qid=(int)$r["qid"];
			//decho("doing item $qid<br />");flush();//dbg
			// vaata kas see item on ikka lahti (ntx seda skripti võib kogemata 2 tk korraga joosta)

			// preload members
			$ml_list_inst = get_instance("mailinglist/ml_list");
			$this->ml_list_members = $ml_list_inst->get_members($lid);

			// so here I need to detect whether the last run was interrupted?
			// how do I do that? some kind of cache?
			$this->save_handle();

			$patch_size = $r["patch_size"];

			$all_at_once = false;

			if ($r["delay"] && $patch_size)
			{

			}
			else
			{
				// everything at once
				$patch_size = sizeof($this->ml_list_members);
				$all_at_once = true;
			};

			$tm=time();
			// vaata, kas on aeg saata
			if (!$r["last_sent"] || ($tm-$r["last_sent"]) >= $r["delay"] || $all_at_once)
			{
				//decho("saadan  meili<br />");flush();//dbg
				$this->save_handle();
				//lukusta queue item
				$this->mark_queue_locked($qid);
				// okey. since there is a chance that, the processing is interrupted
				// _after_ the queue has been locked, I need to minimize the time
				// during which the queue remains locked.
				$stat = ML_QUEUE_IN_PROGRESS;
				$c = 0;
				while ($c < $patch_size && $stat == ML_QUEUE_IN_PROGRESS)
				{
					// 1 pooleli (veel meile) 2 valmis (meili ei leitud enam)
					$stat = $this->do_queue_item($r);
					$c++;
				};
				//decho("saadetud<br />");flush();//dbg
				
				// Kui on valmis, siis peab näitur näitama 100%
				// Ta ei pruugi kunagi 100% muidu jõuda kui on valmis, sest neile liikmetele,
				// mis on mingi teise sama meili samal saatmisel tekkinud queue itemi poolt
				// saadetud ta uuesti meili ei saada, sest kes tahax saada kax sama meili.
				// panjatna, ex?
				if (ML_QUEUE_READY == $stat)
				{
					//$pos=" ,position=total ";
					$this->increase_avoidmids_ready($r["aid"]);
					$this->mark_queue_finished($qid);
				} 
				else
				{
					// unlock it
					$this->db_query("UPDATE ml_queue SET status = $stat WHERE qid = '$qid'");
					//$pos="";
				};

				$this->restore_handle();
			};
		};
		die("die");
		return "";//hmhm
	}

	////
	//! Protsessib queue itemist $r järgmise liikme
	function do_queue_item($r)
	{
		//decho("<b>do_queue_item::</b><br />");
		extract($r);
		// vali järgmine meililisti liige tabelist
		$gidin = false;
		if ($gid != "0" &&  $gid)
		{
			$gidin=explode("|",$gid);
		} 
		$avoidmids=explode(",", $this->db_fetch_field("SELECT avoidmids FROM ml_avoidmids WHERE aid='$aid'","avoidmids"));
		// process until the member has been found or the member list has been exhausted
		$ok=0;
		while (!$ok)
		{
			// find next member to use
			$found = false;
			foreach($this->ml_list_members as $_mid => $_mdat)
			{
				// mis trikk selle gid-iga on? I don't like it.
				if (!in_array($_mid, $avoidmids) && (!is_array($gidin) || (in_array($_mdat["parent"], $gidin))))
				{
					$found = true;
					$member = $_mdat;
					break;
				}
			}
			echo ("found member $_mid <br />");

			if (!$found)// kui enam liikmeid ei ole
			{
				//decho("liikmed otsas<br />");
				return ML_QUEUE_READY;
			}
				
			// gets its value from the ml_queue thingie
			$member_obj = new object($member["oid"]);
			$avoidmessages = $member_obj->meta("avoidmessages");
			if ($avoidmessages[$mid])
			{
				$ok = 0;
			} 
			else
			{
				$this->send_message($mid,$member["oid"],$r);
				$ok = 1;
			};

			// pane siia tabelisse kirja, et sellele liikmele enam ei saadaks
			$aavoidmids = $avoidmids;
			if (!$avoidmids)
			{
				$aavoidmids = array();// et ei tekiks seda tobedat 0 => "" entryt
			};
			//dprint_r($aavoidmids);
			// this member has been served, don't send again! hohumm ... ml_avoidmids
			// will be emptied at the end so I have no way to figure out which members
			// got the message and which didn't. So, what to do, what to do?
			$aavoidmids[] = $member["oid"];
			$avoidmids = join(",",$aavoidmids);
			//decho("pärast avoidmids=$avoidmids<br />");//dbg

			$q="UPDATE ml_avoidmids SET avoidmids='$avoidmids' WHERE aid='$aid'";
			$this->db_query($q);
			
			$tm = time();
			$q = "UPDATE ml_queue SET position=position+1,last_sent='$tm' WHERE qid='$qid'";
			$this->db_query($q);
		};
		//decho("<b>out of do_queue_item</b><br />");
		return ML_QUEUE_IN_PROGRESS;
	}

	function replace_tags($text,$data)
	{
		$nohtml=strip_tags($text);
		preg_match_all("/#(.+?)#/e",$nohtml,$matches);
		if (is_array($matches) && is_array($matches[1]))
		{
			foreach($matches[1] as $v)
			{
				$this->used_variables[$v]=1;
				$text = preg_replace("/#$v#/",$data[$v]?$data[$v]:"",$text);
				//decho("matced $v<br />");
			};
		};
		return $text;
	}

	////
	//! Saadab meili $mid liikmele $member .$r on queue itemi andmed
	function send_message($mid,$member,$r=array())
	{
		//decho("sending msg $mid to $member<br />");
		$lid=$r["lid"];

		// võta meil
		if (!isset($this->d))
		{
			$this->d = get_instance("messenger/mail_message");
		};

		$msg = $this->d->msg_get(array("id" => $mid));

		$msg_meta = $this->get_object_metadata(array(
			"oid" => $mid,
			"key" => "msg",
		));

		$ml_member_inst = get_instance("mailinglist/ml_member");
		list($mailto,$memberdata) = $ml_member_inst->get_member_information(array(
			"lid" => $lid,
			"member" => $member,
		));

		$l = array();

		// oh, so what's that? another sorry ass attempt to avoid race condition?
		if (aw_cache_get("ml_queue::send_message::mails::$mid::$lid", $mailto))
		{
			return;
		}
		aw_cache_set("ml_queue::send_message::mails::$mid::$lid", $mailto, true);

		echo "yeah <br />";
		
		// tee listi obj
		// but I don't see it being used anywhere
		if (!isset($this->ml))
		{
			$this->ml=get_instance("mailinglist/ml_list");
		};

		$data=array();
		$stamp_list = new object_list(array(
			"class_id" => CL_ML_STAMP, 
		));
		for($o = $stamp_list->begin(); !$stamp_list->end(); $o = $stamp_list->next())
		{
			$data[$o->name()] = $o->prop("content");
		};

		// use all variables. 
		$data = $data + $memberdata;
		$data["sendtime"]=$this->time2date(time(),2);
		
		$this->used_variables=array();

		$message = $msg["message"];
		$message = str_replace("#member_id#",$member,$message);
		$message = str_replace("#mail_id#",$mid,$message);

		$message = $this->replace_tags($message,$data);
		$subject = $this->replace_tags($msg["subject"],$data);
		$mfrom = $this->replace_tags($msg["mfrom"],$data);

		$used_vars=array_keys($this->used_variables);

		//$message=preg_replace("/#(.+?)#/e","\$data[\"\\1\"]",$msg["message"]);
		//$subject=preg_replace("/#(.+?)#/e","\$data[\"\\1\"]",$msg["subject"]);
		$mailfrom=preg_replace("/#(.+?)#/e","\$data[\"\\1\"]",$msg["mfrom"]);

		$this->quote($message);
		$this->quote($subject);

		// pane logi tablasse kirja meili saatmine
		
		$this->db_query("INSERT INTO ml_sent_mails (mail,member,uid,lid,tm,vars,message,subject,mailfrom) VALUES ('$mid','$member','".aw_global_get("uid")."','$lid','".time()."',',".join(",",$used_vars).",','$message','$subject','$mailfrom')");

		//tee awm objekt
		if (!isset($this->awm))
		{
			$this->awm=get_instance("aw_mail");
		};

		$this->awm->clean();
		//decho("msg[type]=$msg[type] html=".($msg["type"] & MSG_HTML)."<br />");
		$is_html=$msg["type"] & MSG_HTML;

		// compatiblity with old messenger .. yikes
		$messenger = get_instance("messenger");
		$froma = $mfrom != "" ? $mfrom : $messenger->get_default_froma($msg_meta["identity"]);
		$fromn = $mfrom != "" ? "" : $messenger->get_default_fromn($msg_meta["identity"]);
		echo "froma = $froma , fromn = $fromn  <br />";
		$this->awm->create_message(array(
			"froma" => $froma,
			"fromn" => $fromn,
			"subject" => $subject,
			"To" => $mailto,
			"Sender"=>"duke@struktuur.ee",
			"body" => $is_html?strip_tags(strtr($message,array("<br />"=>"\r\n","<br />"=>"\r\n","</p>"=>"\r\n","</p>"=>"\r\n"))):$message,
		));

		if ($is_html)
		{
			$this->awm->htmlbodyattach(array("data"=>$message));
		};

		// kopeeritud messenger.aw rida 1265
		// why, why, why, Delilah?
		$this->get_objects_by_class(array("class" => CL_FILE,"parent" => $mid,));
		while($row = $this->db_next())
		{
			$this->save_handle();
			$q = "SELECT * FROM files WHERE id = '$row[oid]'";
			$this->db_query($q);
			$row2 = $this->db_next();
			$this->restore_handle();
			$basename = basename($row2["file"]);
			$prefix = substr($basename,0,1);
			$fname = $this->cfg["site_basedir"] . "/files/$prefix/$basename";
			if (file_exists($fname))
			{
				$this->awm->fattach(array(	
					"path" => $fname,
					"name" => $row["name"],
					"disp" => "attachment; filename=\"" . $row["name"] . "\"",
					"contenttype" => $row2["type"],
				));
			};

		};
		$this->awm->gen_mail();
		//decho("<b>SENT!</b>");//dbg
	}

	function mark_queue_finished($qid)
	{
		$this->save_handle();
		$this->db_query("UPDATE ml_queue SET status = 2, position=total WHERE qid = '$qid'");
		$this->restore_handlE();
	}

	function mark_queue_locked($qid)
	{
		$this->save_handle();
		$this->db_query("UPDATE ml_queue SET status = 3 WHERE qid = '$qid'");
		$this->restore_handlE();
	}
		

};
?>
