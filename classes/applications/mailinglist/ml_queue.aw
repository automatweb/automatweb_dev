<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/mailinglist/ml_queue.aw,v 1.14 2005/02/01 12:37:36 ahti Exp $
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


	/**  
		
		@attrib name=submit_manager params=name 
		
		
		@returns
		
		
		@comment
		! Händlib "vasakus puus oleva proge" submitti

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
		! Näitab queuet
		kui manager=1 siis näitab ka mänegeri
		kui show on "list" siis näitab ainult listiga $fid seotud itemeid
		kui show on "mail" siis näitab ainult meiliga $fid seotud itemeid

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

		$ml = get_instance(CL_ML_LIST);
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
		! Näitab queue itemi $id muutmist (reschedulemist)

	**/
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

	/**  
		
		@attrib name=submit_queue_change params=name 
		
		
		@returns
		
		
		@comment
		! Händleb queue itemi reschedulemist

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

		$GLOBALS["reforb"]=0;// see on selleks, et ta ei hakkaks kuhugi suunama vaid prindiks skripti välja
		die("<script language='JavaScript'>opener.history.go(0);window.close();</script>");
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

	/**  
		
		@attrib name=queue_send_now params=name 
		
		
		@returns
		
		
		@comment
		! Märgib itemi $id kohe saatmiseks

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
		$old = time() - 20 * 60;
		// võta need, mida pole veel üldse saadetud või on veel saata & aeg on alustada
		$this->db_query("SELECT * FROM ml_queue WHERE (status IN (0,1) AND start_at <= '$tm') OR (status = 3 AND position < total AND last_sent < $old)");
		echo "select <br />\n";
		flush();
		while ($r = $this->db_next())
		{
			$qid = (int)$r["qid"];
			$lid = (int)$r["lid"];
			//decho("doing item $qid<br />");flush();//dbg
			// vaata kas see item on ikka lahti (ntx seda skripti võib kogemata 2 tk korraga joosta)

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
				$patch_size = $r["total"];
				$all_at_once = true;
			};

			$tm = time();
			// 
			// vaata, kas on aeg saata
			if (!$r["last_sent"] || ($tm-$r["last_sent"]) >= $r["delay"] || $all_at_once)
			{
				//echo("saadan  meili<br />");flush();//dbg
				$this->save_handle();
				//lukusta queue item
				$this->mark_queue_locked($qid);
				// okey. since there is a chance that, the processing is interrupted
				// _after_ the queue has been locked, I need to minimize the time
				// during which the queue remains locked.
				$stat = ML_QUEUE_IN_PROGRESS;
				$c = 0;
				//arr($qid);
				$qx = "SELECT ml_sent_mails.*,messages.type AS type FROM ml_sent_mails LEFT JOIN messages ON (ml_sent_mails.mail = messages.id) WHERE qid = '$qid' AND (mail_sent IS NULL OR mail_sent = 0)";
				//echo "qx = $qx <br>";
				$this->db_query($qx);
				$msg_data = true;
				while ($c < $patch_size && $stat == ML_QUEUE_IN_PROGRESS && $msg_data)
				{
					$msg_data = $this->db_next();
					if ($msg_data)
					{
						//arr($msg_data);
						$this->save_handle();
						// 1 pooleli (veel meile) 2 valmis (meili ei leitud enam)
						echo "sending queue item..<br />\n";
						$stat = $this->do_queue_item($msg_data);

						// yo, increase the counter
						$tm = time();
						$q = "UPDATE ml_queue SET position=position+1, last_sent='$tm' WHERE qid='$qid'";
						$this->db_query($q);
						$this->restore_handle();
					};
					$c++;
				};


				$q = "SELECT total-position AS remaining FROM ml_queue WHERE qid = '$qid'";
				$this->db_query($q);
				$row = $this->db_next();
				if ($row["remaining"] == 0)
				{
					$stat = ML_QUEUE_READY;
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
					//$this->increase_avoidmids_ready($r["aid"]);
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
		die("die<br />\n");
		return "";//hmhm
	}

	////
	//! Protsessib queue itemist $r järgmise liikme
	function do_queue_item($msg)
	{
		$this->awm->clean();
		if (!is_oid($msg["mail"]) || !$this->can("view", $msg["mail"]))
		{
			echo "couldn't send mail<br />\n";
			return;
		}
		$subject = $msg["subject"];
		$msg_obj = new object($msg["mail"]);
		$is_html = $msg_obj->prop("html_mail") == 1024;
		//$is_html=$msg["type"] & MSG_HTML;
		$message = nl2br($msg["message"]);

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

		//arr($is_html);
		// compatiblity with old messenger .. yikes
		echo "from = {$msg["mailfrom"]}  <br />";
		$this->awm->create_message(array(
			"froma" => $msg["mailfrom"],
			"subject" => $subject,
			"To" => $msg["target"],
			"Sender"=>"bounces@struktuur.ee",
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
		$this->awm->gen_mail();
		echo "<b>SENT!</b><br />\n";
		$t = time();
		$q = "UPDATE ml_sent_mails SET mail_sent = 1,tm = '$t' WHERE id = " . $msg["id"];
		$this->db_query($q);

		return ML_QUEUE_IN_PROGRESS;
	}

	function replace_tags($text,$data)
	{
		$nohtml = $text;
		preg_match_all("/#(.+?)#/e", $nohtml, $matches);
		if (is_array($matches) && is_array($matches[1]))
		{
			foreach($matches[1] as $v)
			{
				$this->used_variables[$v] = 1;
				$text = preg_replace("/#$v#/", $data[$v] ? $data[$v] : "", $text);
				//decho("matced $v<br />");
			};
		};
		return $text;
	}

	////
	//! Saadab meili $mid liikmele $member .$r on queue itemi andmed
	function send_message($msg)
	{
		//tee awm objekt
	}

	function preprocess_messages($arr)
	{
		// 1) retrieves the messase
		// I need mid (mail_id)
		// I need lid (list_id)
		if (!isset($this->d))
		{
			$this->d = get_instance(CL_MESSAGE);
		};

		$msg = $this->d->msg_get(array("id" => $arr["mail_id"]));

		$ml_list_inst = get_instance(CL_ML_LIST);
		$list_obj = new object($arr["list_id"]);
		$def_user_folder = $list_obj->prop("def_user_folder");
		// 2) retrieves a list of mailing list users
		$source_obj = &obj($def_user_folder);
		$member_list = $ml_list_inst->get_members_ol($list_obj);
		$this->read_template("send_mail.tpl");
		// 3) calls preprocess_one_message for each one
		set_time_limit(0);
		$ret = "";
		$sents = array();
		foreach($member_list->arr() as $member)
		{
			// skip used addresses
			if(in_array($member->prop("mail"), $sents))
			{
				continue;
			}
			$sents[] = $member->prop("mail");
			$this->vars(array(
				"name" => $member->prop("name"),
				"mail" => $member->prop("mail"),
			));
			$this->preprocess_one_message(array(
				"name" => $member->prop("name"),
				"mail" => $member->prop("mail"),
				"mail_id" => $arr["mail_id"],
				"member_id" => $member->id(),
				"list_id" => $arr["list_id"],
				"msg" => $msg,
				"qid" => $arr["qid"],
			));
			$ret .= $this->parse("item");
		};
		$this->vars(array(
			"item" => $ret,
		));
		echo $this->parse();
	}

	function preprocess_one_message($arr)
	{
		$users = get_instance("users");
		// 1) replaces variables in the message
		// 2) store to ml_sent_mails (which has a default value of '0' in mail_sent values
		// use all variables. 
		//print "<tr><td>".$arr["name"]."</td><td>".$arr["mail"]."</td></tr>\n";
		$vars = md5(uniqid(rand(), true));
		$data = array(
			"name" => $arr["name"],
			"mail" => $arr["mail"],
			"member_id" => $arr["member_id"],
			"mail_id" => $arr["mail_id"],
			"subject" => $arr["msg"]["subject"],
			"traceid" => "?t=$vars",
		);
		
		$this->used_variables = array();
		$obj = obj($arr["member_id"]);
		$user = reset($obj->connections_to(array(
			"type" => 6,
			"from.class_id" => CL_USER,
		)));
		if(is_object($user))
		{
			$data["username"] = $user->prop("from.name");
			$data["name"] = $users->get_user_config(array(
				"uid" => $user->prop("from.name"),
				"key" => "real_name",
			));
		}
		$message = preg_replace("#\#pea\#(.*?)\#/pea\##si", '<div class="doc-title">\1</div>', $arr["msg"]["message"]);
		$message = preg_replace("#\#ala\#(.*?)\#/ala\##si", '<div class="doc-titleSub">\1</div>', $message);
		$message = $this->replace_tags($message, $data);
		$subject = $this->replace_tags($arr["msg"]["subject"], $data);
		$mailfrom = $this->replace_tags($arr["msg"]["mfrom"], $data);
		$mailfrom = trim($mailfrom);
		$subject = trim($subject);
		$mailfrom = $arr["msg"]["meta"]["mfrom_name"] . " <" . $mailfrom . ">";
		//$used_vars = array_keys($this->used_variables);

		$mid = $arr["mail_id"];
		$member_id = $arr["member_id"];
		$lid = $arr["list_id"];
		
		$this->quote($message);
		$this->quote($subject);
		//$vars = join(",", $used_vars);
		//$this->quote($vars);
		$qid = $arr["qid"];
		$target = $arr["name"] . " <" . $arr["mail"] . ">";
		$this->quote($target);

		$mid = $arr["mail_id"];
		// there is an additional field mail_sent in that table with a default value of 0
		$this->db_query("INSERT INTO ml_sent_mails (mail,member,uid,lid,tm,vars,message,subject,mailfrom,qid,target) VALUES ('$mid','$member','".aw_global_get("uid")."','$lid','".time()."','$vars','$message','$subject','$mailfrom','$qid','$target')");

		// 3) process queue then only retrieves messages from that table where mail_sent is set
		// to 0


	}

	function mark_queue_finished($qid)
	{
		$this->save_handle();
		$this->db_query("UPDATE ml_queue SET status = 2, position=total WHERE qid = '$qid'");
		$this->restore_handle();
	}

	function mark_queue_locked($qid)
	{
		$this->save_handle();
		$this->db_query("UPDATE ml_queue SET status = 3 WHERE qid = '$qid'");
		$this->restore_handlE();
	}
		

};
?>
