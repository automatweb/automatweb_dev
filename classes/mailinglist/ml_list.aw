<?php
// $Header: /home/cvs/automatweb_dev/classes/mailinglist/Attic/ml_list.aw,v 1.39 2004/02/05 13:27:22 kristo Exp $
// ml_list.aw - Mailing list
/*
	@default table=objects
	@default field=meta
	@default method=serialize
	
	------------------------------------------------------------------------
	@default group=general
	
	@property def_user_folder type=relpicker reltype=RELTYPE_MEMBER_PARENT editonly=1 rel=1
	@caption Liikmete kaust

	@property sub_form_type type=select rel=1
	@caption Vormi tüüp

	@property redir_obj type=relpicker reltype=RELTYPE_REDIR_OBJECT rel=1
	@caption Dokument millele suunata

	------------------------------------------------------------------------
	@default group=member_list
		
	@property member_list_tb type=toolbar store=no no_caption=1
	@caption Listi staatuse toolbar

	@property member_list type=table store=no no_caption=1
	@caption Liikmed

	------------------------------------------------------------------------
	@default group=subscribing

	@property confirm_subscribe type=checkbox ch_value=1 
	@caption Liitumiseks on vaja kinnitust

	@property confirm_subscribe_msg type=relpicker reltype=RELTYPE_ADM_MESSAGE
	@caption Liitumise kinnituseks saadetav kiri

	@property import_textfile type=fileupload store=no
	@caption Impordi liikmed tekstifailist

	@property mass_subscribe type=textarea rows=25 store=no
	@caption Massiline liitmine

	------------------------------------------------------------------------
	@default group=unsubscribing
	
	@property confirm_unsubscribe type=checkbox ch_value=1 
	@caption Lahkumiseks on vaja kinnitust
	
	@property confirm_unsubscribe_msg type=relpicker reltype=RELTYPE_ADM_MESSAGE 
	@caption Lahkumise kinnituseks saadetav kiri
	
	@property delete_textfile type=fileupload store=no
	@caption Kustuta tekstifailis olevad aadressid
	
	@property mass_unsubscribe type=textarea rows=25 store=no 
	@caption Massiline kustutamine

	------------------------------------------------------------------------
	@default group=list_status

	@property list_status_tb type=toolbar store=no no_caption=1
	@caption Listi staatuse toolbar

	@property list_status_table type=table store=no no_caption=1
	@caption Listi staatus
	
	------------------------------------------------------------------------
	@default group=write_mail

	@property write_mail type=callback callback=callback_gen_write_mail store=no no_caption=1
	@caption Maili kirjutamine

	------------------------------------------------------------------------
	@default group=mail_report

	@property mail_subject type=text store=no 
	@caption Teema

	@property mail_percentage type=text store=no 
	@caption Saadetud

	@property mail_start_date type=text store=no 
	@caption Saatmise algus

	@property mail_last_batch type=text store=no 
	@caption Viimane batch saadeti

	@property mail_report table type=table store=no no_caption=1
	@caption Meili raport
	
	------------------------------------------------------------------------
	@default group=show_mail
	@property show_mail_subject type=text store=no
	@caption Teema

	@property show_mail_from type=text store=no
	@caption Kellelt

	@property show_mail_message type=text store=no no_caption=1
	@caption Sisu

	------------------------------------------------------------------------
	@groupinfo membership caption=Liikmed 
	@groupinfo member_list caption=Nimekiri submit=no parent=membership
	@groupinfo subscribing caption=Liitumine parent=membership
	@groupinfo unsubscribing caption=Lahkumine parent=membership
	@groupinfo raports caption=Kirjad
	@groupinfo list_status caption="Saadetud kirjad" parent=raports submit=no
	@groupinfo write_mail caption="Saada kiri" parent=raports 
	@groupinfo mail_report caption="Kirja raport" parent=raports submit=no
	@groupinfo show_mail caption="Listi kiri" parent=raports submit=no

	------------------------------------------------------------------------
	@classinfo syslog_type=ST_MAILINGLIST
	@classinfo relationmgr=yes
	@classinfo no_status=1

	@reltype MEMBER_PARENT value=1 clid=CL_MENU
	@caption listi liikmete kataloog

	@reltype REDIR_OBJECT value=2 clid=CL_DOCUMENT
	@caption ümbersuunamine

	@reltype ADM_MESSAGE value=3 clid=CL_MESSAGE
	@caption administratiivne teade 
	
*/


class ml_list extends class_base
{
	function ml_list()
	{
		$this->init(array(
			"tpldir" => "automatweb/mlist",
			"clid" => CL_ML_LIST,
		));
		lc_load("definition");
	}


	/** saadab teate $id listidesse $targets(array stringidest :listinimi:grupinimi)
		
		@attrib name=post_message
		
		@param id required 
		@param targets optional 
		
	**/
	function post_message($args)
	{
		extract($args);
		$this->mk_path(0,"<a href='".aw_global_get("route_back")."'>Tagasi</a>&nbsp;/&nbsp;Saada teade");

		$this->read_template("post_message.tpl");

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

		$id = (int)$id;//teate id

		// yes, it works only for one list from now on. 
		// first char is ":", strip it
		$target = substr($targets[0],1);
		$this->quote($target);

		// that's just horrible. lookup by name. oh well

		$q = "SELECT oid FROM objects WHERE class_id=".CL_ML_LIST." AND NAME = '$target' AND status != 0";
		$this->db_query($q);
		$listdata = $this->db_next();

		$listrida = "";

		$this->vars(array(
			"title" => $target,
			"date_edit" => $date_edit->gen_edit_form("start_at",time()-13)
		));
		$listrida.=$this->parse("listrida");
		
		$this->vars(array(
			"listrida" => $listrida,
			"reforb" => $this->mk_reforb("submit_post_message",array(
				"id" => $id,
				"list_id" => $listdata["oid"],
			)),
		));

		return $this->parse();
	}

	/** See händleb juba õiget postitust, siis kui on valitud saatmise ajavahemikud
		
		@attrib name=submit_post_message
		
		
	**/
	function submit_post_message($args)
	{
		extract($args);
		
		
		$id=(int)$id;
		load_vcl('date_edit');
		unset($aid);
		$total=0;

		$list_id = $args["list_id"];
		$_start_at = date_edit::get_timestamp($start_at);
		$_delay = $delay * 60;
		$_patch_size = $patch_size;

		/*
		if (!isset($aid))
		{
			// tee sisestus avoidmids tabelisse
			$this->db_query("INSERT INTO ml_avoidmids (avoidmids) VALUES ('')");
			$aid=$this->db_last_insert_id();
		};
		*/

		$count = $this->get_member_count($list_id);
		$total++;

		// mark the queue as "processing" - 5
		$this->db_query("INSERT INTO ml_queue (lid,mid,gid,uid,aid,status,start_at,last_sent,patch_size,delay,position,total)
			VALUES ('$list_id','$id','$gid','".aw_global_get("uid")."','$aid','5','$_start_at','0','$_patch_size','$_delay','0','$count')");

		$qid = $this->db_last_insert_id();
		
		$mlq = get_instance("mailinglist/ml_queue");
		$mlq->preprocess_messages(array(
			"mail_id" => $id,
			"list_id" => $list_id,
			"qid" => $qid,
		));

		// now I should mark the queue as "ready to send" or 0
		$q = "UPDATE ml_queue SET status = 0 WHERE qid = '$qid'";
		$this->db_query($q);
		
		$this->_log(ST_MAILINGLIST, SA_SEND,"saatis meili $id listi ".$v["name"].":$gname", $lid);
			
		//$this->db_query("UPDATE ml_avoidmids SET usagec='$total' WHERE aid='$aid'");

		return aw_global_get("route_back");
	}

	/** (un)subscribe an address from(to) a list 
		
		@attrib name=subscribe nologin="1" 
		
		@param id required type=int 
		@param rel_id required type=int 
		
	**/
	function subscribe($args = array())
	{
		$list_id = $args["id"];
		$rel_id = $args["rel_id"];
		$rx = $this->db_fetch_row("SELECT * FROM aliases WHERE target = '$list_id' AND relobj_id = '$rel_id'");
		if (empty($rx))
		{
			die("miskit on mäda");
		};

		$list_obj = new object($list_id);

		// I have to check whether subscribing requires confirmation, and if so, send out the confirm message
		// subscribe confirm works like this - we still subscribe the member to the list, but make
		// her status "deactive" and generate her a confirmation code
		// confirm code is added to the metad
		$ml_member = get_instance(CL_ML_MEMBER);

		if ($args["op"] == 1)
		{
			$retval = $ml_member->subscribe_member_to_list(array(
				"name" => $args["name"],
				"email" => $args["email"],
				"list_id" => $list_obj->id(),
				"confirm_subscribe" => $list_obj->prop("confirm_subscribe"),
				"confirm_message" => $list_obj->prop("confirm_subscribe_msg"),
			));	
		};
		if ($args["op"] == 2)
		{
			$retval = $ml_member->unsubscribe_member_from_list(array(
				"email" => $args["email"],
				"list_id" => $list_obj->id(),
			));	
		};

		$relobj = new object($rel_id);

		$mx1 = $relobj->meta("values");
		$mx = $mx1["CL_ML_LIST"];

		if (!empty($mx["redir_obj"]))
		{
			$retval = $this->cfg["baseurl"] . "/" . $mx["redir_obj"];
		}
		elseif ($list_obj->prop("redir_obj") != "")
		{
			$retval = $this->cfg["baseurl"] . "/" . $list_obj->prop("redir_obj");
		}
		return $retval;
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;

		switch($data["name"])
		{
			case "sub_form_type":
				$data["options"] = array(
					"0" => "liitumine",
					"1" => "lahkumine",
				);
				break;

			case "member_list":
				$this->gen_member_list($arr);
				break;
	
			case "list_status_table":
				$this->gen_list_status_table($arr);
				break;
			
			case "mail_report":
				$this->gen_mail_report_table($arr);
				break;

			case "mail_percentage":
				$data["value"] = $this->gen_percentage($arr);
				break;

			case "mail_subject":
				$data["value"] = $this->gen_mail_subject($arr);
				break;

			case "mail_start_date":
			case "mail_last_batch":
				$list_id = $arr["obj_inst"]->id();
				$mail_id = $arr["request"]["mail_id"];
				$row = $this->db_fetch_row("SELECT * FROM ml_queue WHERE lid = ${list_id} ANd mid = ${mail_id}");
				if ($data["name"] == "mail_start_date")
				{
					$data["value"] = $this->time2date($row["start_at"],2);
				}
				else
				{
					if ($row["last_sent"] == 0)
					{
						$data["value"] = "Midagi pole veel saadetud";
					}
					else
					{
						$data["value"] = $this->time2date($row["last_sent"],2);
					};
				};
				break;

			case "member_list_tb":
				$this->gen_member_list_tb($arr);
				break;
			
			case "list_status_tb":
				$this->gen_list_status_tb($arr);
				break;

			case "show_mail_subject":
			case "show_mail_from":
			case "show_mail_message":
				$data["value"] = $this->gen_ml_message_view($arr);
				break;
				

		};
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "import_textfile":	
                        	global $import_textfile;
				$imp = $import_textfile;
				if (!is_uploaded_file($import_textfile))
				{
					return PROP_OK;
				}
				$contents = file_get_contents($imp);
				$this->mass_subscribe(array(
					"list_id" => $arr["obj_inst"]->id(),
					"text" => $contents,
				));
				break;
	
			case "delete_textfile":
                        	global $delete_textfile;
				$imp = $delete_textfile;
				if (!is_uploaded_file($delete_textfile))
				{
					return PROP_OK;
				}
				$contents = file_get_contents($imp);
				$this->mass_unsubscribe(array(
					"list_id" => $arr["obj_inst"]->id(),
					"text" => $contents,
				));
				break;
				

			case "mass_subscribe":
				$this->mass_subscribe(array(
					"list_id" => $arr["obj_inst"]->id(),
					"text" => $data["value"],
				));
				break;

			case "mass_unsubscribe":
				$this->mass_unsubscribe(array(
					"list_id" => $arr["obj_inst"]->id(),
					"text" => $data["value"],
				));
				break;

			case "write_mail":
				$this->submit_write_mail($arr);
				break;
		};
		return $retval;
	}

	////
	// !Imports members from a text file / or text block
	// text(string) - member list, comma separated
	// list_id(id) - which list?
	function mass_subscribe($arr)
	{
		$lines = explode("\n",$arr["text"]);
		$list_obj = new object($arr["list_id"]);
		$fld = $list_obj->prop("def_user_folder");
		$fld_obj = new object($fld);
		$name = $fld_obj->name();
		echo "Impordin kasutajaid kataloogi $fld / $name... <br />";
		set_time_limit(0);
		$ml_member = get_instance(CL_ML_MEMBER);
		$cnt = 0;
		if (sizeof($lines) > 0)
		{
			foreach($lines as $line)
			{
				if (strlen($line) == 0)
				{
					continue;
				};
				list($name,$addr) = explode(",",$line);
				$name = trim($name);
				$addr = trim($addr);
				if (is_email($addr))
				{
					print "OK - n:$name, a:$addr<br />";
					flush();
					$cnt++;
					$retval = $ml_member->subscribe_member_to_list(array(
						"name" => $name,
						"email" => $addr,
						"list_id" => $list_obj->id(),
					));
					usleep(500000);
				}
				else
				{
					print "IGN - n:$name, a:$addr<br />";
					flush();
				};
			};
		};
		print "Importisin $cnt aadressi<br>";
	}

	////
	// !Mass unsubscribe of addresses
	function mass_unsubscribe($arr)
	{
		$lines = explode("\n",$arr["text"]);
		$list_obj = new object($arr["list_id"]);
		$fld = $list_obj->prop("def_user_folder");
		$fld_obj = new object($fld);
		$name = $fld_obj->name();
		echo "Kustutan kasutajaid kataloogist $fld / $name... <br />";
		set_time_limit(0);
		$ml_member = get_instance(CL_ML_MEMBER);
		$cnt = 0;
		if (sizeof($lines) > 0)
		{
			foreach($lines as $line)
			{
				if (strlen($line) == 0)
				{
					continue;
				};
				// no, this is different, no explode. I need to extract an email address from the
				// line
				preg_match("/(\S*@\S*)/",$line,$m);
				$addr = $m[1];
				if (is_email($addr))
				{
					print "OK a:$addr<br />";
					flush();
					$cnt++;
					$retval = $ml_member->unsubscribe_member_from_list(array(
						"email" => $addr,
						"list_id" => $list_obj->id(),
					));
					usleep(500000);
				}
				else
				{
					print "IGN - a:$addr<br />";
					flush();
				};
			};
		};
		print "Kustutasin $cnt aadressi<br>";
	}

	function gen_member_list_tb($arr)
	{
		$toolbar = &$arr["prop"]["toolbar"];
		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => "Kustuta",
			"url" => "javascript:document.changeform.action.value='delete_members';document.changeform.submit();",		
			"img" => "delete.gif",
		));		
		$toolbar->add_separator();
		$toolbar->add_cdata(html::href(array(
			"url" => $this->mk_my_orb("export_members",array("id" => $arr["obj_inst"]->id(),"filename" => $arr["obj_inst"]->name() . " liikmed.txt")),
			"caption" => "ekspordi liikmed",
		)));
	}

	function export_members($arr)
	{
		$members = $this->get_members($arr["id"]);
		$ml_member_inst = get_instance(CL_ML_MEMBER);

		$ser = "";
		foreach($members as $key => $val)
		{
			list($mailto,$memberdata) = $ml_member_inst->get_member_information(array(
				"lid" => $arr["id"],
				"member" => $val["oid"],
			));
			$ser .= $memberdata["name"];
			$ser .= ",";
			$ser .= $mailto;
			$ser .= "\n";
		};
		header("Content-Type: text/plain");
		header("Content-length: " . strlen($ser));
		header("Content-Disposition: filename=liikmed.txt");
		print $ser;
		exit;
	}

	function gen_member_list($arr)
	{
		$perpage = 100;
		$ft_page = (int)$GLOBALS["ft_page"];
		$ml_list_members = $this->get_members($arr["obj_inst"]->id(),$perpage * $ft_page +1,$perpage * ($ft_page + 1));
		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("mlist/member_list");
		$t->d_row_cnt = $this->member_count;
		$pageselector = "";

                if ($t->d_row_cnt > $perpage)
                {
                        $pageselector = $t->draw_lb_pageselector(array(
                                "records_per_page" => $perpage
                        ));
                };
	
		$t->table_header = $pageselector;
		$ml_member_inst = get_instance(CL_ML_MEMBER);

		if (is_array($ml_list_members))
		{	
			foreach($ml_list_members as $key => $val)
			{
				list($mailto,$memberdata) = $ml_member_inst->get_member_information(array(
					"lid" => $arr["obj_inst"]->id(),
					"member" => $val["oid"],
				));
				$t->define_data(array(
					"id" => $val["oid"],
					"email" => $mailto,
					"name" => $memberdata["name"],
					"check" => html::checkbox(array(
						"name" => "sel[]",
						"value" => $val["oid"],
					)),
				));	

			}
		};		
	}

	function delete_members($arr)
	{
		if (is_array($arr["sel"]))
		{
			foreach($arr["sel"] as $member_id)
			{
				$member_obj = new object($member_id);
				$member_obj->delete();
			};
		};
		return $this->mk_my_orb("change",array("id" => $arr["id"],"group" => "membership"));
	}

	function gen_list_status_tb($arr)
	{
		$toolbar = &$arr["prop"]["toolbar"];
		$toolbar->add_button(array(
			"name" => "new",
			"tooltip" => "Uus kiri",
			"url" => $this->mk_my_orb("change",array("id" => $arr["obj_inst"]->id(),"group" => "write_mail")),
			"img" => "new.gif",
		));

		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => "Kustuta",
			"url" => "javascript:document.changeform.action.value='delete_queue_items';document.changeform.submit();",		
			"img" => "delete.gif",
		));		
	}

	function gen_list_status_table($arr)
	{
		$mq = get_instance("mailinglist/ml_queue");
		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("mlist/queue");
		$q = "SELECT ml_queue.* FROM ml_queue LEFT JOIN objects ON (ml_queue.mid = objects.oid) WHERE objects.status != 0 && lid = " . $arr["obj_inst"]->id() . " ORDER BY start_at DESC";
                $this->db_query($q);
                while ($row = $this->db_next())
                {
			$mail_obj = new object($row["mid"]);
			if ($row["status"] != 2)
			{
				$stat_str = $mq->a_status[$row["status"]];
				$status_str = "<a href='javascript:remote(0,450,270,\"".$this->mk_my_orb("queue_change",array
	("id"=>$row["qid"]))."\");'>$stat_str</a>";
			}
			else
			{
				$status_str = $mq->a_status[$row["status"]];
			};
				
			$row['subject'] = html::href(array(
					'url' => $this->mk_my_orb("change", array("group" => "mail_report", "id" => $arr["obj_inst"]->id(),"mail_id" => $row['mid'])),
					'caption' => $mail_obj->name(),
				));
			
			//$row["mid"] = $mail_obj->name();
                        if (!$row["patch_size"])
                        {
                                $row["patch_size"]="kõik";
                        };
                        $row["delay"]/=60;
                        $row["status"] = $status_str;
                        $row["protsent"]=$this->queue_ready_indicator($row["position"],$row["total"]);
                        $row["perf"] = sprintf("%.2f",$row["total"] / ($row["last_sent"] - $row["start_at"]) * 60);
                        $row["vali"]= html::checkbox(array(
						"name" => "sel[]",
						"value" => $row["qid"],
			));
                        $t->define_data($row);
		};
	}
	
	function delete_queue_items($arr)
	{
		if (is_array($arr["sel"]))
		{
			$q = sprintf("DELETE FROM ml_queue WHERE qid IN (%s)",join(",",$arr["sel"]));
			$this->db_query($q);
		};
		return $this->mk_my_orb("change",array("id" => $arr["id"],"group" => "raports"));
	}

	// --------------------------------------------------------------------
	// messengerist saatmise osa

	////
	//! Messenger kutsub välja kui on valitud liste targetiteks
	// vajab targets ja id
	function route_post_message($args = array())
	{
		extract($args);
		$url=$this->mk_my_orb("post_message",array("id" => $id, "targets" => $targets),"",1);
		$sched = get_instance("scheduler");
		$sched->add(array(
			"event" => $this->mk_my_orb("process_queue", array(), "ml_queue", false, true),
			"time" => time()+120,	// every 2 minutes
		));
		return $url;
	}

	function get_members($id,$from = 0, $to = 0)
	{
		$ret = array();
		$list_obj = new object($id);

		$member_list = new object_list(array(
			"parent" => $list_obj->prop("def_user_folder"),
			"class_id" => CL_ML_MEMBER,
		));

		$cnt = 0;

		$this->member_count = sizeof($member_list->ids());

		for($o = $member_list->begin(); !$member_list->end(); $o = $member_list->next())
		{
			$cnt++;
			if (0 == $to || (0 != $from && 0 != $to && between($cnt,$from,$to)))
			{
				$ret[$o->id()] = array(
					"oid" => $o->id(),
					"parent" => $o->parent(),
				);
			};
		};

		return $ret;
	}	

	function get_member_count($id)
	{
		return count($this->get_members($id));
	}


	function parse_alias($args = array())
	{
		$tobj = new object($args["alias"]["target"]);
		$sub_form_type = $tobj->prop("sub_form_type");
		if (!empty($args["alias"]["relobj_id"]))
		{
			$relobj = new object($args["alias"]["relobj_id"]);
			$meta = $relobj->meta("values");
			if (!empty($meta["CL_ML_LIST"]["sub_form_type"]))
			{
				$sub_form_type = $meta["CL_ML_LIST"]["sub_form_type"];
			};
		}
		$tpl = ($sub_form_type == 0) ? "subscribe.tpl" : "unsubscribe.tpl";
		$this->read_template($tpl);
		$this->vars(array(
			"listname" => $tobj->name(),
			"reforb" => $this->mk_reforb("subscribe",array(
				"id" => $args["alias"]["target"],
				"rel_id" => $relobj->id(),
				"section" => aw_global_get("section"),
			)),
		));
		return $this->parse();

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

	////
	// !This will generate a raport for a single mail sent to a list.
	// Ungh, shouldn't this be a separate class then?
	function gen_mail_report_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("mlist/report");
		$_mid = $arr["request"]["mail_id"];
		$id = $arr["obj_inst"]->id();
		$q = "
			SELECT target, tm, subject, id
			FROM ml_sent_mails
			WHERE lid = '$id' AND mail = '$_mid' AND mail_sent = 1 ORDER BY tm DESC limit 50";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$tgt = htmlspecialchars($row["target"]);
			$row["member"] = "<a href='".$this->mk_my_orb("change", array("id" => $id, "group" => "show_mail", "mail_id" => $arr["request"]["mail_id"], "s_mail_id" => $row["id"]))."'>".$tgt."</a>";
			$t->define_data($row);
		}
	}

	function gen_mail_subject($arr)
	{
		$mail_id = $arr["request"]["mail_id"];
		$mail_obj = new object($mail_id);
		return $mail_obj->name();
	}

	function gen_percentage($arr)
	{
		// how many members does this list have?
		$list_id = $arr["obj_inst"]->id();
		$_members = $this->get_members($list_id);
		$member_count = sizeof($_members);

		$mail_id = $arr["request"]["mail_id"];

		$mail_obj = new object($mail_id);
		$name = $mail_obj->name();
		// how many members have been served?	
		$row = $this->db_fetch_row("SELECT count(*) AS cnt FROM ml_sent_mails WHERE lid = '$list_id' AND mail = '$mail_id'");
		$served_count = $row["cnt"];

		$row2 = $this->db_fetch_row("SELECT total,position FROM ml_queue WHERE lid = '$list_id' AND mid = '$mail_id'");
		$served_count = $row2["position"];
		$member_count = $row2["total"];

		$url = $_SERVER["REQUEST_URI"];

		if (!headers_sent() && $served_count < $member_count)
		{
			$refresh_rate = 30;
			header("Refresh: $refresh_rate; url=$url");
			$str = " , värskendan iga ${refresh_rate} sekundi järel";
		};
		return "Liikmeid: $member_count, saadetud: $served_count $str";
	}

	function callback_mod_tab($arr)
	{
		// hide it, if no mail report is open
		if ($arr["id"] == "mail_report" && empty($arr["request"]["mail_id"]))
		{
			return false;
		};
		if ($arr["id"] == "show_mail" && empty($arr["request"]["s_mail_id"]))
		{
			return false;
		};
		if ($arr["id"] == "mail_report")
		{
			$arr["link"] .= "&mail_id=" . $arr["request"]["mail_id"];
		};
		if ($arr["id"] == "write_mail" && $arr["request"]["group"] != "write_mail")
		{
			return false;
		};
	}		

	function gen_ml_message_view($arr)
	{
		$mail_id = $arr["request"]["s_mail_id"];
		if (!is_array($this->msg_view_data))
		{
                	$this->msg_view_data = $this->db_fetch_row("SELECT * FROM ml_sent_mails WHERE id = '$mail_id'");
		};

		$rv = "";

		switch($arr["prop"]["name"])
		{
			case "show_mail_from":
				$rv = htmlspecialchars($this->msg_view_data["mailfrom"]);
				break;

			case "show_mail_subject":
				$rv = $this->msg_view_data["subject"];
				break;

			case "show_mail_message":
				$rv = nl2br($this->msg_view_data["message"]);

				break;
		};
		return $rv;
	}

	function callback_gen_write_mail($arr)
	{
		// haudi, haudi. now I have to create the form of mail writer into here somehow	
		$writer = get_instance(CL_MESSAGE);
		$writer->init_class_base();
		$all_props = $writer->get_active_properties(array(
                                "group" => "general",
		));
		// would be nice to have some other and better method to do this
		$filtered_props = array();
		foreach($all_props as $id => $prop)
		{
			if ($id == "mfrom" || $id == "name" || $id == "html_mail" || $id == "message")
			{
				$filtered_props[$id] = $prop;
			};
		};

		$xprops = $writer->parse_properties(array(
				"properties" => $filtered_props,
				"name_prefix" => "emb",
		));

		return $xprops;
	}

	function submit_write_mail($arr)
	{
		$msg_data = $arr["request"]["emb"];
		// 1. create an object. for this I need to know the parent
		// for starters I'll use the one from the list object itself
		$msg_data["parent"] = $arr["obj_inst"]->parent();
		$msg_data["subgroup"] = "send";
		$msg_data["mto"] = $arr["obj_inst"]->id();

		$writer = get_instance(CL_MESSAGE);
		$writer->init_class_base();
		$writer->id_only = true;
		// it does it's own redirecting .. duke
		$message_id = $writer->submit($msg_data);
	}
};
?>
