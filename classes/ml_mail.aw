<?php

	global $orb_defs;
	$orb_defs["ml_mail"] = "xml";

	classload("config","form_base","ml_list","messenger");
	class ml_mail extends aw_template
	{
		function ml_mail()
		{
			$this->tpl_init("automatweb/mlist");
			$this->db_init();
			lc_load("definition");

			$this->dbconf=new db_config();
			$this->formid=$this->dbconf->get_simple_config("ml_form");
			$this->searchformid=$this->dbconf->get_simple_config("ml_search_form");
			$this->mgr = new messenger(array("fast"));

			// queue status
			$this->a_status=array(
				"0" => "uus",
				"1" => "pooleli",
				"2" => "valmis",
				"3" => "hetkel saadab",
				"4" => "peatatud"
				);
		}


		function orb_new($arr)
		{
			extract($arr);
			$oid = $this->new_object(array(
					"parent" => $parent,
					"name" => "meil",
					"class_id" => CL_ML_MAIL,false));
		
			$this->db_query("INSERT INTO messages (id) VALUES ('$oid')");
			
			$this->_log("mlist","lisas meili $oid");
			return $this->mk_my_orb("change",array("id" => $oid));
		}


		function orb_submit_change($arr)
		{
			extract($arr);
			$q = "UPDATE messages SET 
				message = '$message',
				subject = '$subject',
				mfrom = '$mfrom',
				mtargets1 = '$mtargets1'	WHERE id = '$id'";
			$this->db_query($q);
			$this->_log("mlist","lisas meili $id");
			return $this->mk_my_orb("change",array("id" => $id));
		}


		function orb_change($ar)
		{
			extract($ar);

			classload("msg_sql");
			$d=new msg_sql_driver();

			$msg=$d->msg_get(array("id" => $id));

			$this->mk_path($msg["parent"],"Muuda meili");

			$this->read_template("ml_mail_change.tpl");

			$fb=new form_base();
			
			$els=$fb->get_elements_for_forms(array($this->formid));
			$elstring=join("&nbsp;",map("#%s#",$els));

			$this->vars(array(
				"l_saada" => $this->mk_my_orb("send",array("id" => $id)),
				"l_queue" => $this->mk_my_orb("queue",array("fid" => $id,"show" => "mail","back" => "mail")),
				"message" => $msg["message"],
				"mtargets1" => $msg["mtargets1"],
				"mfrom" => $msg["mfrom"],
				"subject" => $msg["subject"],
				"elements" => $elstring,
				"reforb" => $this->mk_reforb("submit_change",array("id"=>$id))
				));
			return $this->parse();
		}


		function orb_submit_send($arr)
		{
			extract($arr);

			$start_at=mktime($start_at["hour"],$start_at["minute"],0,$start_at["month"],$start_at["day"],$start_at["year"]);
			$delay=$delay * 60;
			if (is_array($lists))
			{
				foreach($lists as $l)
				{
					$l=(int)$l;
					$count=$this->db_fetch_field("SELECT COUNT(*) AS count FROM ml_list2member,objects WHERE lid = '$l' AND ml_list2member.mid=objects.oid AND objects.status != '0'","count");

					$this->db_query("INSERT INTO ml_queue (lid,mid,status,start_at,last_sent,patch_size,delay,position,total)
						VALUES ('$l','$id','0','$start_at','0','$patch_size','$delay','0','$count')");
					$lname=$this->db_fetch_field("SELECT name FROM objects WHERE oid = '$l'","name");
					$this->_log("mlist","saatis meili $id listi $lname");
				};

			};

			if (isset($back))
			{
				return $back;
			};

			return $this->mk_my_orb("send",array("id" => $id));
		}


		function orb_send($ar)
		{
			extract($ar);


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

			$ob=$this->get_object($id);
			$this->mk_path($ob["parent"],"Saada meil");

			$this->read_template("ml_mail_send.tpl");
			if ($no_upper_bar != "1")
			{
				$this->vars(array(
					"l_muuda" => $this->mk_my_orb("change",array("id" => $id)),
					"l_queue" => $this->mk_my_orb("queue",array("fid" => $id, "show" => "mail","back" => "mails")),
					));
				$ubar=$this->parse("UBAR");
			};


			$alllists=array();
			$this->db_query("SELECT name,oid FROM objects WHERE class_id ='".CL_ML_LIST."' AND status != '0'");
			while ($r = $this->db_next())
			{
				$alllists[$r["oid"]]=$r["name"];
			};

			
				
			$this->vars(array(
				"listsel" => $this->multiple_option_list(array(),$alllists),
				"date_edit" => $date_edit->gen_edit_form("start_at",time()+2*60),
				"reforb" => $this->mk_reforb("submit_send",array(
					"id" => $id,
					"back" => $back,
					)),
				"UBAR" => $ubar,
				));
			return $this->parse();
		}


		function orb_delete($ar)
		{
			extract($ar);

			$this->delete_object($id);

			$this->db_query("DELETE FROM messages where id='$id'");

			$this->_log("mlist","kustutas meili $id");

			$url=$this->mk_my_orb("mlist",array("parent" => $parent),"menuedit");
			header("Location:$url");
		}

		function orb_submit_manager($arr)
		{
			$this->dbconf->set_simple_config("ml_form",$arr["form"]);
			$this->dbconf->set_simple_config("ml_search_form",$arr["searchform"]);
			return $this->mk_my_orb("queue",array("manager" => 1));
		}

		function orb_queue($arr)
		{
			extract($arr);
			load_vcl("table");
			global $PHP_SELF;

			$this->read_template("queue.tpl");
			

			$t = new aw_table(array(
				"prefix" => "ml_queue",
				"self" => $PHP_SELF,
				"imgurl" => $baseurl . "/automatweb/images",
			));
			
			$t->set_header_attribs(array(
				"class" => "ml_mail",
				"action" => "queue",
				"show" => $show,
				"fid" => $fid,
				"back" => $back,
				"manager" => $manager
			));

			if ($show && $show != "all")
			{
				$fid=(int)$fid;
				$name=$this->db_fetch_field("SELECT name FROM objects WHERE oid='$id'","name");
			};

			$headerarray=array();
			switch ($back)
			{
				case "listf":
					$headerarray[$this->mk_orb("obj_list",array("parent"=>$fid),"menuedit")]="Tagasi";
					break;
				case "list":
					$headerarray[$this->mk_orb("change",array("id"=>$fid),"ml_list")]="Tagasi";
					break;
				case "mail":
					$headerarray[$this->mk_orb("change",array("id"=>$fid))]="Tagasi";
					break;
				case "mails":
					$headerarray[$this->mk_orb("send",array("id"=>$fid))]="Tagasi";
					break;
			};
			switch ($show)
			{
				case "list":
					$headerarray[$this->mk_orb("queue",array("show"=>"all","fid"=>$fid,"back"=>$back))]="Kõik";
					$filt=" WHERE lid = '$fid'";
					$title="(list $name)";
					break;

				case "mail":
					$headerarray[$this->mk_orb("queue",array("show"=>"all","fid"=>$fid,"back"=>$back))]="Kõik";
					$filt=" WHERE mid = '$fid'";
					$title="(meil $name)";
					break;

				case "all":
				default:
					$filt="";
					break;
			};

			$t->define_header("Queue $title",$headerarray);
			$t->parse_xml_def($this->basedir . "/xml/mlist/queue.xml");

			$q="SELECT * FROM ml_queue $filt";
			$this->db_query($q);

			while ($row = $this->db_next())
			{
				$this->save_handle();
				$row["lid"]=$this->db_fetch_field("SELECT name FROM objects WHERE oid='".$row["lid"]."'","name");
				$row["mid"]=$this->db_fetch_field("SELECT name FROM objects WHERE oid='".$row["mid"]."'","name");
				$this->restore_handle();
				if (!$row["patch_size"])
				{
					$row["patch_size"]="kõik";
				};
				$row["status"]=$this->a_status[$row["status"]];
				$row["protsent"]=$this->queue_ready_indicator($row["position"],$row["total"]);
				$t->define_data($row);
			};

			if ($sortby)
			{
				$t->sort_by(array("field"=>$sortby));
			} else
			{
				$t->sort_by(array());
			};

			$queue=$t->draw();

			if ($manager)
			{
				global $title;
				$title="Meililistid";
	
				$fb=new form_base();
				$flist=$fb->get_list(FTYPE_ENTRY);
				$sflist=$fb->get_list(FTYPE_SEARCH);

				$this->vars(array(
					"form" => $this->picker($this->formid,$flist),
					"searchform" => $this->picker($this->searchformid,$sflist),
					"reforb" => $this->mk_reforb("submit_manager",array()),
				));
				$manager=$this->parse("MGR");
			};

			$this->vars(array(
				"QUEUE" => $queue,
				"MGR" => $manager));
			return $this->parse();
		}

		// tekitab tabelist sikuse väikse progress bari
		function queue_ready_indicator($osa,$kogu)
		{
			$p=(int)((int)$osa * 100 / (int)$kogu);
			$not_p=100-$p;
			
			// tekst pane sinna, kus on rohkem ruumi.
			if ($p>$not_p)
			{
				$p1t="<span Style='font-size:10px;font-face:verdana;'><font color='white'>".$p."%</font></span>";
			} else
			{
				$p2t="<span Style='font-size:10px;font-face:verdana;'><font color='black'>".$p."%</font></span>";
			};
			// kommentaar on selleks, et sorteerimine töötaks (hopefully)
			return "<!-- $p --><table bgcolor='#CCCCCC' Style='height:12;width:100%'><tr><td width=\"$p%\" bgcolor=\"blue\">$p1t</td><td width=\"$not_p%\">$p2t</td></tr></table>";
		}

		// täidab meili queuet
		function process_queue($arr)
		{
			echo("process_queue:<br>");//dbg
			$tm=time();
			$awm=0;
			// võta need, mida pole veel üldse saadetud või on veel saata & aeg on alustada
			$this->db_query("SELECT * FROM ml_queue WHERE status IN (0,1) AND start_at<='$tm'");
			while ($r = $this->db_next())
			{
				$qid=(int)$r["qid"];
				echo("doing item $qid<br>");flush();//dbg
				// kui on vaja vahet pidada ja ei tohi kõiki korraga saata
				if ($r["delay"] && $r["patch_size"])
				{
					$tm=time();
					// vaata, kas on aeg saata
					if (!$r["last_sent"] || ($tm-$r["last_sent"]) >= $r["delay"])
					{
						if (!$awm)
						{
								classload("aw_mail");
								echo("aw mail loaded<br>");//dbg
								$awm=1;
						};
						echo("saadan alates ".($r["position"]+1)."(incl) ".$r["patch_size"]." meili");flush();//dbg
						$this->save_handle();
						//lukusta queue item
						$this->db_query("UPDATE ml_queue SET status = 3 WHERE qid = '$qid'");
						$this->send_list_message($r,$r["position"]+1,$r["patch_size"]);
						echo("peale send_list_message<br>");flush();//dbg
						if ($r["patch_size"]+$r["position"]>=$r["total"])
						{
							$stat="2";//valmis
						} else
						{
							$stat="1";//pooleli
						};
						//lukust lahti
						$this->db_query("UPDATE ml_queue SET status = $stat WHERE qid = '$qid'");
						$this->restore_handle();
					} else //dbg
					{
						$veel=$r["delay"]-($tm-$r["last_sent"]);
						echo("järgmise batchini on veel $veel sekundit");flush();
					};
				} else
				{
						if (!$awm)
						{
								classload("aw_mail");
								echo("aw mail loaded<br>");//dbg
								$awm=1;
						};
					$this->save_handle();
					//lukusta queue item
					$this->db_query("UPDATE ml_queue SET status = 3 WHERE qid = '$qid'");
					$this->send_list_message($r,$r["position"]+1,0);
					//saadetud
					$this->db_query("UPDATE ml_queue SET status = 2 WHERE qid = '$qid'");
					$this->restore_handle();
				};
			};
			return "";
		}

		function send_list_message($r,$first,$num)
		{
			extract($r);
			$position=$first;
			$first--;
			if (!$num)
			{
				$num=65535;
			};
			$limit=" LIMIT $first,$num";
			// võta meil

			$d=new msg_sql_driver();

			$msg=$d->msg_get(array("id" => $mid));// mid on message id

			// tee vorm
			$f=new form();
			$f->load($this->formid);
			
			//võta need elemendid, mis on siin listis kasutusel
			$row = $this->get_object($lid);
			$last=unserialize($row["last"]);
			$vars=unserialize($last["vars"]);
			
			//tee awm objekt
			$awm=new aw_mail();
			// võta listi liikmed
			$q="SELECT mid FROM ml_list2member WHERE lid = '$lid' ORDER BY mid $limit";
			echo("q=$q<br>");//dbg
			$this->db_query($q);//mid on member id
			while ($m = $this->db_next())
			{
				echo("liige ".$m["mid"]."<br>");flush();//dbg
				$f->load_entry($m["mid"]);
				$liige=$f->get_element_values();
				echo("Andmed:<pre>");print_r($liige);echo("</pre><br>");flush();//dbg
				// miks ei võiks kõiki muutujaid kohe kasutada??
				$l=array();
				foreach ($vars as $k => $v)
				{
					echo("k=$k");//dbg
					$el=$f->get_element_by_id($k);
					$n=$el->get_el_name();
					$l[$n]=$liige[$n];
				};

				$l["time"]=$this->time2date(time(),2);

				$mtargets1=preg_replace("/#(.+?)#/e","\$l[\"\\1\"]",$msg["mtargets1"]);
				$message=preg_replace("/#(.+?)#/e","\$l[\"\\1\"]",$msg["message"]);
				$subject=preg_replace("/#(.+?)#/e","\$l[\"\\1\"]",$msg["subject"]);
				echo("clean<br>");flush();//dbg
				$awm->clean();
				echo("create<br>");flush();//dbg
				$awm->create_message(array(
					"froma" => $msg["mfrom"],
					"Subject" => $subject,
					"To" => $mtargets1,
					"body" => $message));
				echo("gen mail<br>");flush();//dbg
				$awm->gen_mail();
				echo("done gen mail<br>");flush();//dbg
				$position++;
			};
			$tm=time();
			$position--;
			$this->db_query("UPDATE ml_queue SET position = '$position', last_sent = '$tm' WHERE qid='$qid'");
		}
	};
?>
