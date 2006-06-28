<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/mailinglist/ml_queue.aw,v 1.27 2006/06/28 14:42:22 markop Exp $
// ml_queue.aw - Deals with mailing list queues

define("ML_QUEUE_NEW",0);
define("ML_QUEUE_IN_PROGRESS",1);
define("ML_QUEUE_READY",2);
define("ML_QUEUE_SENDING",3);
define("ML_QUEUE_STOPPED",4);
define("ML_QUEUE_PROCESSING",5);

/*
	@default field=meta
	@default method=serialize

	@tableinfo ml_queue index=qid master_table=objects master_index=brother_of

	@property q_status type=textbox
	@caption Staatus

*/

// see aid, mida siin pruugitakse on ml_avoidmids tabeli kirje id
class ml_queue extends aw_template
{
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
			ML_QUEUE_STOPPED => "peatatud",
			ML_QUEUE_PROCESSING => "maili genereeritakse",
		);
	}

	/**  
		@attrib name=submit_manager params=name 
		@returns
		@comment
		! H�ndlib "vasakus puus oleva proge" submitti
	**/
	function orb_submit_manager($arr)
	{
		$this->dbconf->set_simple_config("ml_form",$arr["form"]);
		$this->dbconf->set_simple_config("ml_search_form",$arr["searchform"]);
		$this->dbconf->set_simple_config("ml_mail_el",$arr["mailel"]);
		return $this->mk_my_orb("queue",array("manager"=>1));
	}

	/**
		@attrib name=queue params=name 
		
		@param id optional
		@param fid optional
		@param show optional
		@param sortby optional
		@param sort_order optional
		@param manager optional
		
		@returns
		
		@comment
		! N�itab queuet
		kui manager=1 siis n�itab ka m�negeri
		kui show on "list" siis n�itab ainult listiga $fid seotud itemeid
		kui show on "mail" siis n�itab ainult meiliga $fid seotud itemeid
	**/
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
				$headerarray[$this->mk_my_orb("queue",array("show"=>"all","fid"=>$fid))]="K�ik";
				$filt=" WHERE lid = '$fid'";
				$title="(list $name)";
				break;

			case "mail":
				$headerarray[$this->mk_my_orb("queue",array("show"=>"all","fid"=>$fid))]="K�ik";
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

		$ml = get_instance(CL_ML_LIST);
		$lists = $ml->get_lists_and_groups(array());//v�ta k�ik listide & gruppide nimed, et polex vaja iga kord queryda

		$q = "SELECT * FROM ml_queue $filt";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			//("<pre>");print_r($row);echo("</pre>");//dbg
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
				$row["patch_size"]="k�ik";
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
			$title = "Meililistid";

			$fb = get_instance("formgen/form_base");
			$flist = $fb->get_list(FTYPE_ENTRY);
			$sflist = $fb->get_list(FTYPE_SEARCH);

			$this->vars(array(
				"form" => $this->picker($this->formid, $flist),
				"searchform" => $this->picker($this->searchformid, $sflist),
				"mailel" => $this->picker($this->mailel, $ml->get_all_varnames()),
			));
			$mparse = $this->parse("MGR");
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

	/**  
		@attrib name=queue_change params=name 
		
		@param id required
		
		@returns
		
		@comment
		! N�itab queue itemi $id muutmist (reschedulemist)
	**/
	function orb_queue_change($arr)
	{
		extract($arr);
		$this->db_query("SELECT * FROM ml_queue WHERE qid='$id'");
		$r=$this->db_next();
	echo "SELECT * FROM ml_queue WHERE qid='$id'";arr($r);
		$this->read_template("queue_change.tpl");
		if ($r["status"]==2)
		{
			$this->vars(array("teade" => "Juba t�idetud queue objekti ei saa muuta !"));
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

	/**  
		@attrib name=submit_queue_change params=name 
		
		@returns
		
		@comment
		! H�ndleb queue itemi reschedulemist
	**/
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
		$GLOBALS["reforb"]=0;// see on selleks, et ta ei hakkaks kuhugi suunama vaid prindiks skripti v�lja
        die("<script ".
           "language='JavaScript'>opener.history.go(0);window.close();</script>");
	}

	/**  
		@attrib name=queue_delete params=name 
		
		@returns
		
		@comment
		! Kustutab queue itemi $id
	**/
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
		};echo "DELETE FROM ml_queue WHERE qid IN ($q)";
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

	/**  
		@attrib name=queue_send_now params=name 
		
		@returns
		
		@comment
		! M�rgib itemi $id kohe saatmiseks
	**/
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
			$this->db_query($w);echo $w;
			$w="UPDATE ml_queue SET last_sent=$delta-delay WHERE qid IN ($q) AND status!=0";echo '<br>'.$w;
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
		// kommentaar on selleks, et sorteerimine t��taks (hopefully)
		return "<!-- $p --><table bgcolor='#CCCCCC' Style='height:12;width:100%'><tr><td width=\"$p%\" bgcolor=\"blue\">$p1t</td><td width=\"$not_p%\">$p2t</td></tr></table>";
	}

	/**  
		@attrib name=process_queue params=name nologin="1" 
		
		@returns
		
		@comment
		! Processes all active mailing list queues
		Invoked from the scheduler
	**/
	function process_queue($arr)
	{
		set_time_limit(0);
		$sched = get_instance("scheduler");
		$sched->add(array(
			"event" => $this->mk_my_orb("process_queue", array(), "", false, true),
			"time" => time()+120,	// every 2 minutes
		));
		$this->awm = get_instance("protocols/mail/aw_mail");
		echo "adding scheduler ! <br />\n";
		flush();
		//decho("process_queue:<br />");//dbg
		$tm = time();
		$old = time() - 2 * 60;
		// v�ta need, mida pole veel �ldse saadetud v�i on veel saata & aeg on alustada
		$this->db_query("SELECT * FROM ml_queue WHERE (status IN (0,1) AND start_at <= '$tm') OR (status = 3 AND position < total AND last_sent < $old)");
		echo "select <br />\n";
//		echo "SELECT * FROM ml_queue WHERE (status IN (0,1) AND start_at <= '$tm') OR (status = 3 AND position < total AND last_sent < $old)";
		flush();


		// enne k�ik queued massiivi �ra... miskip�rast on tunne, et muidu hakkavad p�ringud �ksteist segama
		$queue_rows = array();
		while ($queue_row = $this->db_next())
		{
			$queue_rows[] = $queue_row;
		}

		foreach ($queue_rows as $r)
		{
	//echo "queue = ".dbg::dump($r)." <br>\n";
	//		flush();
			$qid = (int)$r["qid"];
			$lid = (int)$r["lid"];
			echo("doing item $qid<br />");
			flush();//dbg
			// vaata kas see item on ikka lahti (ntx seda skripti v�ib kogemata 2 tk korraga joosta)
			// so here I need to detect whether the last run was interrupted?
			// how do I do that? some kind of cache?
			$this->save_handle();

			$patch_size = $r["patch_size"];

			$all_at_once = false;

			if (!($r["delay"] && $patch_size))
			{
				// everything at once
				$patch_size = $r["total"];
				$all_at_once = true;
			};

			//kontrollib, et 'kki vahepeal m�ni teine queue on otsa jooksnud ja konkreetset maili juba saatma hakanud.... siis on admed muutunud ju... lukusatatud ju pole... ja alguses k�ik �ra lukustada pole ka hea m�te... 
			$test_qid = $r["qid"];
			$test_q = $this->db_fetch_row("SELECT * FROM ml_queue WHERE qid = '$test_qid'");
			arr($test_q);
			
			$tm = time();
			$old = time() - 2 * 60;//kui n��d 2 minutit m��das viimase maili saatmisest, siis v�ib suht kindel olla, et eelmine queue on pange pand
			
			if(
			   !($test_q["status"] == 1)
			&& !($test_q["status"] == 0)
			&& !($test_q["status"] == 3
				&& $test_q["position"] < $test_q["total"]
				&& $test_q["last_sent"] < $old
				)
			)
			continue;
			//v�ibolla pold ka eelnevast kasu, igaks juhuks uuendab viimati saadetud aja �ra, isegi kui miskit pole saadetud, et oleks n�ha, et miski on selle maili saatmisega tegelenud v�hemalt
			$update_q = "UPDATE ml_queue SET last_sent='$tm' WHERE qid='$qid'";
			$this->db_query($update_q);echo $update_q;

			// vaata, kas on aeg saata
			if (!$r["last_sent"] || ($tm-$r["last_sent"]) >= $r["delay"] || $all_at_once)
			{
				echo("saadan  meili<br />");
				flush();//dbg
				$this->save_handle();
				//lukusta queue item
				$this->mark_queue_locked($qid);
				// okey. since there is a chance that, the processing is interrupted
				// _after_ the queue has been locked, I need to minimize the time
				// during which the queue remains locked.
				$stat = 1/*ML_QUEUE_IN_PROGRESS*/;
				$c = 0;
				$qx = "SELECT ml_sent_mails.*,messages.type AS type FROM ml_sent_mails LEFT JOIN messages ON (ml_sent_mails.mail = messages.id) WHERE qid = '$qid' AND (mail_sent IS NULL OR mail_sent = 0)";
				echo "qx = $qx <br>";
				flush();
				$this->db_query($qx);
				$msg_data = true;
				while ($c < $patch_size && $stat == ML_QUEUE_IN_PROGRESS && $msg_data)
				{
					$msg_data = $this->db_next();
					if ($msg_data)
					{
						$this->save_handle();
						// 1 pooleli (veel meile) 2 valmis (meili ei leitud enam)
						echo "sending queue item..<br />\n";
						flush();
						$stat = $this->do_queue_item($msg_data);

						// yo, increase the counter
						$tm = time();
						$q = "UPDATE ml_queue SET position=position+1, last_sent='$tm' WHERE qid='$qid'";
						$this->db_query($q);echo $q;
						$this->restore_handle();
					};
					$c++;
				};

				$q = "SELECT total-position AS remaining FROM ml_queue WHERE qid = '$qid'";
				echo $q;
				$this->db_query($q);
				$row = $this->db_next();
				if ($row["remaining"] <= 0)
				{
					$stat = 2/*ML_QUEUE_READY*/;
				}
				else
				if ($stat === null)
				{
					$stat = 1;
				}
				//decho("saadetud<br />");flush();//dbg

				// Kui on valmis, siis peab n�itur n�itama 100%
				// Ta ei pruugi kunagi 100% muidu j�uda kui on valmis, sest neile liikmetele,
				// mis on mingi teise sama meili samal saatmisel tekkinud queue itemi poolt
				// saadetud ta uuesti meili ei saada, sest kes tahax saada kax sama meili.
				// panjatna, ex?
				if (ML_QUEUE_READY == $stat)
				{
					//$pos=" ,position=total ";
					//$this->increase_avoidmids_ready($r["aid"]);
					$this->mark_queue_finished($qid);
				} 
				else
				{
					// unlock it
					$this->db_query("UPDATE ml_queue SET status = $stat WHERE qid = '$qid'");
					echo "UPDATE ml_queue SET status = $stat WHERE qid = '$qid'";//$pos="";
				};

				$this->restore_handle();
			};
		};
		die(t("done\n<br>"));
		return "";//hmhm
	}

	////
	//! Protsessib queue itemist 		echo $mailfrom;$r j�rgmise liikme
	function do_queue_item($msg)
	{
	echo "queue item: ".dbg::dump($msg)." <br>";
		$this->awm->clean();
		if (!is_oid($msg["mail"]) || !$this->can("view", $msg["mail"]))
		{
			echo "couldn't send mail<br />\n";
			return;
		}
		
		$msg_obj = new object($msg["mail"]);
		$is_html = $msg_obj->prop("html_mail") == 1024;
		//$is_html=$msg["type"] & MSG_HTML;
		$subject = $msg["subject"];
		$message = $msg["message"];
//		if($is_html) // && strpos("<br />" ,$message) !== true && strpos("<br>", $message) !== true)
//		{
//			$message = nl2br($message);
//		}
		$c_title = $msg_obj->prop("msg_contener_title");
		$c_content = $msg_obj->prop("msg_contener_content");
		$al = get_instance("aliasmgr");
		$al->parse_oo_aliases($msg["mail"], &$message);
		$tpl = $msg_obj->meta("template_selector");
		if(is_oid($tpl) && $this->can("view", $tpl))
		{
			$o = new object($tpl);
			$template = $o->prop("content");
			$tsubject = $o->prop("subject");
			if(!empty($tsubject))
			{
				$subject = $tsubject;
			}
			
			$template = str_replace("#title#", $c_title, $template);
			$template = str_replace("#container#", $c_content, $template);
			$message = str_replace("#content#", $message, $template);
		}
		// compatiblity with old messenger .. yikes
		echo "from = {$msg["mailfrom"]}  <br />\n";
		flush();
		echo dbg::dump($msg);
		$this->awm->create_message(array(

			"froma" => $msg["mailfrom"],
			"subject" => $msg["subject"],
			"To" => $msg["target"],
			//"Sender"=>"bounces@struktuur.ee",
			"body" => $message,
			//"body" => $is_html ? strip_tags(strtr($message,array("<br />" => "\r\n", "<br />" => "\r\n", "</p>" => "\r\n", "</p>" => "\r\n"))) : $message,
		));
		if ($is_html)
		{
			$this->awm->htmlbodyattach(array(
				"data" => $message,
			));
		};
		$conns = $msg_obj->connections_from(array(
			"type" => "RELTYPE_ATTACHMENT",
		));
		$mimeregistry = get_instance("core/aw_mime_types");
		foreach($conns as $conn)
		{
			$to_o = $conn->to();
			// XXX: is this check correct?
			if ($to_o->prop("file") == "")
			{
				continue;
			};
			$realtype = $mimeregistry->type_for_file($to_o->name());
			$this->awm->fattach(array(
				"path" => $to_o->prop("file"),
				"contenttype"=> $mimeregistry->type_for_file($to_o->name()),
				"name" => $to_o->name(),
			));
		};
		echo "abuut to gen mail <br>\n";
		flush();
		$this->awm->gen_mail();
		$t = time();
		$q = "UPDATE ml_sent_mails SET mail_sent = 1,tm = '$t' WHERE id = " . $msg["id"];
		$this->db_query($q);
		echo "<b>SENT!</b><br />\n";
		flush();	
		return ML_QUEUE_IN_PROGRESS;
	}

	////
	//! Saadab meili $mid liikmele $member .$r on queue itemi andmed
	function send_message($msg)
	{
		//tee awm objekt
	}

	function mark_queue_finished($qid)
	{
		$this->save_handle();
		$this->db_query("UPDATE ml_queue SET status = 2, position=total WHERE qid = '$qid'");
		$this->restore_handle();
		echo "UPDATE ml_queue SET status = 2, position=total WHERE qid = '$qid'";
	}

	function mark_queue_locked($qid)
	{
		$this->save_handle();
		$this->db_query("UPDATE ml_queue SET status = 3 WHERE qid = '$qid'");
		$this->restore_handle();
		echo "UPDATE ml_queue SET status = 3 WHERE qid = '$qid'";
	}

};
?>
