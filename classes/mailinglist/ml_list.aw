<?php
// $Header: /home/cvs/automatweb_dev/classes/mailinglist/Attic/ml_list.aw,v 1.31 2003/11/26 12:22:39 duke Exp $
// ml_list.aw - Mailing list
/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general
	
	@property def_user_folder type=relpicker reltype=RELTYPE_MEMBER_PARENT editonly=1 rel=1
	@caption Vali kataloog, kuhu pannakse uued liikmed 

	@property sub_form_type type=select rel=1
	@caption Vormi tüüp

	@property redir_obj type=relpicker reltype=RELTYPE_REDIR_OBJECT rel=1
	@caption Dokument millele suunata

	@property user_form_conf type=objpicker clid=CL_ML_LIST_CONF
	@caption Vali konfiguratsioon

	@property user_folders type=select multiple=1 size=15 editonly=1
	@caption Vali kataloogid, kust võetakse listi liikmed

	@property vars type=callback callback=callback_gen_list_variables editonly=1
	@caption Muutujad

	@property automatic_form type=select editonly=1
	@caption Vali vorm, mille sisestustest tehakse automaatselt liikmed

	@default group=subscribing

	@property confirm_subscribe type=checkbox ch_value=1 
	@caption Liitumiseks on vaja kinnitust

	@property confirm_subscribe_msg type=relpicker reltype=RELTYPE_ADM_MESSAGE 
	@caption Liitumise kinnituseks saadetav kiri
	
	@property confirm_unsubscribe type=checkbox ch_value=1 
	@caption Lahkumiseks on vaja kinnitust
	
	@property confirm_unsubscribe_msg type=relpicker reltype=RELTYPE_ADM_MESSAGE 
	@caption Lahkumise kinnituseks saadetav kiri

	@property member_list type=table store=no group=members
	@caption Liikmed

	@property import_textfile type=fileupload store=no group=general
	@caption Impordi liikmed tekstifailist

	@property list_status_table type=table store=no group=list_status no_caption=1
	@caption Listi staatus

	@property list_report_table type=table store=no group=list_report no_caption=1
	@caption Listi raport

	@property mail_percentage type=text store=no group=mail_report
	@caption Saadetud

	@property mail_start_date type=text store=no group=mail_report
	@caption Saatmise algus

	@property mail_last_batch type=text store=no group=mail_report
	@caption Viimane batch saadeti

	@property mail_report table type=table store=no group=mail_report no_caption=1
	@caption Meili raport

	@groupinfo members caption=Liikmed submit=no
	@groupinfo subscribing caption="Liitumine/lahkumine"
	@groupinfo raports caption="Raportid"
	@groupinfo list_status caption="Listi staatus" parent=raports submit=no
	@groupinfo list_report caption="Listi raport" parent=raports submit=no
	@groupinfo mail_report caption="Maili raport" parent=raports submit=no

	@classinfo syslog_type=ST_MAILINGLIST
	@classinfo relationmgr=yes

	@reltype MEMBER_PARENT value=1 clid=CL_MENU
	@caption listi liikmete kataloog

	@reltype REDIR_OBJECT value=2 clid=CL_DOCUMENT
	@caption ümbersuunamine

	@reltype ADM_MESSAGE value=3
	@caption administratiivne teade clid=CL_MESSAGE
	
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

		$this->dbconf=get_instance("config");
		$this->searchformid=$this->dbconf->get_simple_config("ml_search_form");
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		$conf_set = $arr["obj_inst"]->prop("user_form_conf");
		$name = $data["name"];
		# don't show these elements, if the configuration has not been chosen
		if (!$conf_set && in_array($data["name"],array("vars","automatic_form")))	
		{
			return PROP_IGNORE;
		};

		switch($data["name"])
		{
			case "user_folders":
                		$data["options"] = $this->_get_defined_user_folders($arr["obj_inst"]->prop("user_form_conf"));
				break;
	
			case "automatic_form":
				$fl = array("0" => "");
				$ll = new aw_array($this->get_forms_for_list($arr["obj_inst"]->id()));
				if ($ll->count() > 0)
				{
					$flist = new object_list(array(
						"id" => $ll->get(),
					));
					$fl = array_merge($fl,$flist->names());
				}
				$data["options"] = $fl;
				break;
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
			
			case "list_report_table":
				$this->gen_list_report_table($arr);
				break;
			
			case "mail_report":
				$this->gen_mail_report_table($arr);
				break;

			case "mail_percentage":
				$data["value"] = $this->gen_percentage($arr);
				break;

			case "mail_start_date":
			case "mail_last_batch":
				$list_id = $arr["obj_inst"]->id();
				$mail_id = $arr["request"]["mail_id"];
				$q = "SELECT * FROM ml_queue WHERE lid = ${list_id} ANd mid = ${mail_id}";
				$this->db_query($q);
				$row = $this->db_next();
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
				

		};
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			// possible race condition here, because when dealing with config forms,
			// you do not really know in which order the elements come in.

			// and since those element depend on each other directly, I think 
			// they should be one element. But right now, I _hope_ this will work
			// -- duke
			case "user_form_conf":
				$ob = $this->load_list($arr["obj_inst"]->id());
				if ($data["value"] != $arr["obj_inst"]->prop("user_form_conf"))
				{
					// if form has changed, delete all pseudo vars
					$vlist = new aw_array($arr["obj_inst"]->meta("vars"));
					foreach ($vlist->get() as $k => $v)
					{
						$this->del_pseudo_var($arr["obj_inst"]->id(),$k);
					};
				}
				break;

			case "vars":
				$vars = $data["value"];
				$obj = $this->load_list($arr["obj_inst"]->id());
	
				$vlist = new aw_array($arr["obj_inst"]->meta("vars"));
				foreach ($vlist->get() as $k => $v)
				{
					if (!isset($vars[$k]))
					{
						$this->del_pseudo_var($arr["obj_inst"]->id(),$k);
					};
				};
				// leia lisatud muutujad
				if (is_array($vars))
				{
					foreach ($vars as $k => $v)
					{
						if ($vlist->get_at($k))
						{
							$this->add_pseudo_var($arr["obj_inst"]->id(),$k);
						};
					};
				};
				break;

			case "import_textfile":	
                        	global $import_textfile;
				$imp = $import_textfile;
				if (!is_uploaded_file($import_textfile))
				{
					return PROP_OK;
				}
				//$this->list_ob = $this->get_object($arr["obj_inst"]->id(), true);
				$fld = $arr["obj_inst"]->prop("def_user_folder");
				//$fld = $this->list_ob["meta"]["def_user_folder"];
				echo "Impordin kasutajaid kataloogi $fld... <br />";
				$first = true;
				$contents = file_get_contents($imp);
				$lines = explode("\n",$contents);

				$ml_member = get_instance("mailinglist/ml_member");
				set_time_limit(0);

				foreach($lines as $line)
				{
					list($name,$addr) = explode(",",$line);
					if (is_email($addr))
					{
						print "OK - n:$name, a:$addr<br />";
						flush();
						$retval = $ml_member->subscribe_member_to_list(array(
							"name" => $name,
							"email" => $addr,
							"list_id" => $arr["obj_inst"]->id(),
						));
						usleep(500000);
					}
					else
					{
						print "IGN - n:$name, a:$addr<br />";
						flush();
					};
				};
				die();
				break;
				
			
			case "automatic_form":
					$id = $arr["obj_inst"]->id();

					$tr = $this->db_fetch_row("SELECT * FROM ml_list2automatic_form WHERE lid = '$id'");
					if (is_array($tr))
					{
						$this->db_query("UPDATE ml_list2automatic_form SET fid = '$prop[value]' WHERE lid = '$id'");
					}
					else
					{
						$this->db_query("INSERT INTO ml_list2automatic_form (lid, fid) VALUES('$id','$prop[value]')");
					}
					if ($prop["value"])
					{
						$this->update_automatic_list($id);
					}
					break;


		};
		return $retval;
	}

	function gen_member_list($arr)
	{
		$ml_list_members = $this->get_members($arr["obj_inst"]->id());
		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("mlist/member_list");
		$ml_member_inst = get_instance("mailinglist/ml_member");
		if (is_array($ml_list_members))
		{	
			foreach($ml_list_members as $key => $val)
			{
				$this->save_handle();
				// XXX: SLOW!!!
				// but until the form based member thingies do not write
				// to ml_users, there is no simpler way to do this
				list($mailto,$memberdata) = $ml_member_inst->get_member_information(array(
					"lid" => $arr["obj_inst"]->id(),
					"member" => $val["oid"],
				));
				$this->restore_handle();
				$t->define_data(array(
					"id" => $val["oid"],
					"email" => $mailto,
					"name" => $memberdata["name"],
				));	

			}
		};		
	}

	function gen_list_status_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("mlist/queue");
		$lists = $this->get_lists_and_groups(array());
		$q = "SELECT * FROM ml_queue WHERE lid = " . $arr["obj_inst"]->id();
                $this->db_query($q);
                while ($row = $this->db_next())
                {
			$listname = $lists[$row["lid"].":0"];
			$row["lid"] = "<a href='javascript:remote(0,450,270,\"".$this->mk_my_orb("queue_change",array
("id"=>$row["qid"]),"ml_list_status")."\");'>$listname</a>";
			$this->save_handle();
                        $row["mid"] = $this->db_fetch_field("SELECT name FROM objects WHERE oid='".$row["mid"]."'","n
ame")."(".$row["mid"].")";
                        $this->restore_handle();
                        if (!$row["patch_size"])
                        {
                                $row["patch_size"]="kõik";
                        };
                        $row["delay"]/=60;
                        //$row["status"]=$this->a_status[$row["status"]];
                        $row["status"]=$row["status"];
                        $row["protsent"]=$this->queue_ready_indicator($row["position"],$row["total"]);
                        $row["perf"] = sprintf("%.2f",$row["total"] / ($row["last_sent"] - $row["start_at"]) * 60);
                        $row["vali"]="<input type='checkbox' NAME='sel[]' value='".$row["qid"]."'>";
                        $t->define_data($row);
		};
	}

	function gen_list_report_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("mlist/report_mails");
		$id = $arr["obj_inst"]->id();
		$q = "
			SELECT m_objects.name as member, l_objects.name as lid, tm, subject, id, mail
			FROM ml_sent_mails
			LEFT JOIN objects AS m_objects ON m_objects.oid = ml_sent_mails.member
			LEFT JOIN objects AS l_objects ON l_objects.oid = ml_sent_mails.lid
			WHERE lid  = $id GROUP BY mail";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$row['subject'] = html::href(array(
				'url' => $this->mk_my_orb("change", array("group" => "mail_report", "id" => $id,"mail_id" => $row['mail'])),
				'caption' => $row['subject']
			));
			$t->define_data($row);
		}
	}

	function _get_defined_user_folders($conf)
	{
		if (!is_array($this->defined_user_folders))
		{
			$ufc_inst = get_instance("mailinglist/ml_list_conf");
			$this->defined_user_folders = $ufc_inst->get_folders_by_id($conf);
			// just to make sure we don't invoke that function twice
			if (!is_array($this->defined_user_folders))
			{
				$this->defined_user_folders = array();
			};
		};
		return $this->defined_user_folders;
	}

	function callback_gen_list_variables($arr)
	{
		$this->read_template("list_omadused.tpl");
		$meta = $arr["obj_inst"]->meta();
		//$meta = $args["obj"]["meta"];
		$allvars=$this->get_all_varnames(false,$arr["obj_inst"]->prop("user_form_conf"));

		foreach ($allvars as $k => $name)
		{
			$this->vars(array(
				"name" => $name,
				"checked" => checked($meta["vars"][$k]),
				"acl" => (isset($meta["vars"][$k]))? "ACL" : "",
				"vid" => $k,
				"l_acl" => (isset($meta["vars"][$k])) ? ("editacl.".$this->cfg["ext"]."?oid=".$this->get_pseudo_var($arr["obj_inst"]->id(),$k)."&file=default.xml") : "",
			));
			$varparse.=$this->parse("variable");
		};
		$this->vars(array("variable" => $varparse));
		$tmp = array(
			"type" => "text",
			"caption" => $arr["prop"]["caption"],
			"value" => $this->parse(),
		);
		return array($arr["prop"]["name"] => $tmp);
	}

	////
	//! Annab kõik listi grupid
	// tagastab array lgroup => name
	function get_list_groups($id)
	{
		$this->load_list($id);
		$far = $this->get_all_user_folders();
		$ret = array();
		foreach($far as $fid => $fn)
		{
			$ret += $this->get_objects_below(array(
				"parent" => $fid,
				"class" => CL_PSEUDO,
				"full" => true,
				"ret" => ARR_NAME
			));
		}
		return $ret;
	}

	////
	//! Tagastab igasuguste picker funktsioonide jaoks listide ja gruppide valiku
	// id näitab, mis listi näidatakse esimesena
	// checkacl- kas kasutajal peab olema send õigus
	// fullnames- kas näidata alamgruppe nii "list:grupp", muidu näitab ainult "grupp"
	// new- kas näidata iga listi all -- uus grupp -- 
	// spacer- mis lisada iga grupi ette
	// prefix-mix lisada iga rea ette
	function get_lists_and_groups($args=array()/*$checkacl=0,$fullnames=0,$id=0,$new=0,$spacer=" "*/)
	{
		extract($args);
		$ar=array();
		$lists=$this->get_all_lists($checkacl);
		
		if ($id)
		{
			//reorder array, so that $id is first
			$l2[$id]=$lists[$id];
			unset($lists[$id]);
			$lists=$l2+$lists;
		};
		//echo("<pre>");print_r($lists);echo("</pre>");//dbg

		foreach($lists as $id => $name)
		{
			$g=$this->get_list_groups($id);
			$ar["$id:0"]=$prefix.$name;
			
			if (is_array($g))
			{
				foreach ($g as $k => $v)
				if ($k)
				{
					$fn=$fullnames ? "$name:":"";
					$ar["$id:$k"]="$spacer$spacer$prefix$fn$v";
				};
			};
			
			if ($new)
			{
				$ar["$id:n"]="$spacer$spacer-- uus grupp --";
			};
			
		};
		return $ar;
	}

	////
	//! Annab kõik listid
	function get_all_lists($checkacl=0)
	{
		$alllists=array();
		$this->db_query("SELECT name,oid FROM objects WHERE class_id ='".CL_ML_LIST."' AND status != '0'");
		while ($r = $this->db_next())
		{
			$alllists[$r["oid"]]=$r["name"];
		};
		return $alllists;
	}

	// pseudomuutujate funktsioonid

	////
	//! Lisab pseudomuutuja objekti
	// klass on vist vale sest see klass on menüüde oma aga suva tgelt
	function add_pseudo_var($lid,$vid)
	{
		return $this->new_object(array("name" => "pseudo_var_$vid","parent"=>$lid, "class_id" => CL_PSEUDO, "last" => $vid),1);
	}

	////
	//! Kustutab pseudomuutuja
	function del_pseudo_var($lid,$vid)
	{
		$shoot_me=$this->get_pseudo_var($lid,$vid);
		$this->delete_object($shoot_me);
	}

	////
	//! Tagastab $vid formi elemendile vastava pseudomuutuja $lid listi all
	function get_pseudo_var($lid,$vid)
	{
		$lid=(int)$lid;
		$vid=(int)$vid;
		return $this->db_fetch_field("SELECT oid FROM objects WHERE objects.parent = '$lid' AND objects.last = '$vid' AND objects.status != 0 AND objects.class_id =".CL_PSEUDO,"oid");
	}

	// nii, see peab siis kuidagi formi käest variabled küsima

	////
	//! Tagastab kõik formi elemendid id => name 
	function get_all_varnames($id = false, $conf = false, $all_vars = true)
	{
		$ret = array();
		$fb = get_instance("formgen/form_base");
		if (!is_array($this->list_ob) && $id)
		{
			$this->load_list($id);
		}
		if ($conf)
		{
			$ufc_inst = get_instance("mailinglist/ml_list_conf");
			$this->formid = $ufc_inst->get_forms_by_id($conf);
		}

		$ar = new aw_array($this->formid);
		foreach($ar->get() as $fid)
		{
			$ml = $fb->get_form_elements(array("id" => $fid, "key" => "id", "all_data" => false));
			foreach($ml as $k => $v)
			{
				if ($this->list_ob['meta']['vars'][$k] || $all_vars)
				{
					$ret[$k] = $v;
				}
			}
		}
		return $ret;
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

	////
	//! saadab teate $id listidesse $targets(array stringidest :listinimi:grupinimi)
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

		// siin tee selline trikk, et järjesta arrayd vähe teise süsteemi järgi ringi 
		$names=array();
		$_names="";
		foreach($targets as $v)
		{
			list($shit,$lname,$gname)=explode(":",$v);
			if (!isset($names[$lname]))
			{
				$_names.=($_names!=""?",":"")."'$lname'";
			};
			$names[$lname]=1;
		};

		
		$listdata=array();
		$q="SELECT name,oid,metadata FROM objects WHERE class_id=".CL_ML_LIST." AND NAME IN($_names) AND status != 0";
		unset($names);
		unset($_names);
		$this->db_query($q);
		while ($listdta = $this->db_next())
		{
			$listdata[$listdta["name"]]=array(
				"id" => $listdta["oid"],
				"groups" => array_flip($this->get_list_groups($listdta["oid"]))
			);
		};
		

		$lists=array();
		foreach($targets as $v)
		{
			$item=explode(":",$v);

			$lid=$listdata[$item[1]]["id"];
			$gid=$item[2] ? $listdata[$item[1]]["groups"][$item[2]] : 0;

			$lists[(int)$lid]["name"]=$item[1];
			$lists[(int)$lid]["c"][(int)$gid]=$item[2]?$item[2]:" ";
		};

		// Kui on määratud mingi listi grupid ja ka list ise, siis tegelikult mõeldakse
		// listi enda all listi kõiki ülejäänud gruppe. Niisiis tuleb see muutus siin teha

		//echo("<pre>lists=");print_r($lists);echo("</pre>");//dbg
		foreach($lists as $lid => $v)
		{
			/*echo("doing $lid s=".sizeof($v["c"])."0=*".isset($v["c"]["0"])."*<pre>");print_r($v);echo("</pre><br />");*/
			if ($v["c"][0] && sizeof($v["c"])>1)//kui on list ja on ka gruppe sellest
				{
					/*echo("bljää!$lid<br />");//dbg*/
					// võta kõik grupid ja leia need, mida pole määratud
					//$allgrps=$this->get_list_groups($lid);
					$allgrps=array_flip($listdata[$v["name"]]["groups"]);

					foreach ($v["c"] as $del => $jura)
					{
						/*echo("unsetting $del<br />");*/
						unset($allgrps[$del]);
					};
					/*echo("after=");print_r($allgrps);//dbg*/
					unset($lists[$lid]["c"][0]);
					$gid=join("|",array_keys($allgrps));
					if ($gid)
						$lists[$lid]["c"][$gid]=join(",",array_values($allgrps));
				};
		};
		/*echo("<pre>lists=");print_r($lists);echo("</pre>");//dbg*/

		foreach($lists as $lid => $v)
		{
			foreach($v["c"] as $gid => $gname)
			{
				$this->vars(array(
					"lidgid" => "$lid:$gid",
					"title" => $v["name"].($gname?":".$gname :""),
					"date_edit" => $date_edit->gen_edit_form("start_at[$lid:$gid]",time()-13)
					));
				$listrida.=$this->parse("listrida");
			};
		};
		


		$this->vars(array(
			"listrida" => $listrida,
			"reforb" => $this->mk_reforb("submit_post_message",array(
				"id" => $id,
				"lists" => htmlentities(serialize($lists)),
				))
		));

		return $this->parse();
	}

	////
	//! See händleb juba õiget postitust, siis kui on valitud saatmise ajavahemikud
	function submit_post_message($args)
	{
		extract($args);
		
		
		$id=(int)$id;
		//		echo($lists);//dbg
		$lists=unserialize($lists);
		//		dbg::dump($lists);
		//echo("<pre>");//dbg
		//print_r($lists);//dbg
		// siin tekib nüüd see küsimus, et kas valitud list tähendab kogu listi või siis ainult neid liikmeid,
		// millele ei ole täpsustavat gruppi määtatud. praegu on nii, et tähendab kogu listi

		// kui on veel valitud täpsustavaid gruppe ka, siis 0 muudetakse eelmise funktsiooni poolt
		// ülejäänud gruppideks ehk kokkuvõttes saadetakse kogu grupile meil, ainut osale gruppidele on eritingimused

		load_vcl('date_edit');
		unset($aid);
		$total=0;
		$_lists = new aw_array($lists);
		foreach($_lists->get() as $lid => $v)
		{
			foreach($v["c"] as $gid => $gname)
			{
				$key="$lid:$gid";
				//echo($lid." -".$v["name"].":$gid- $gname key=$key<br />");//dbg
				$_start_at=date_edit::get_timestamp($start_at[$key]);
				$_delay=$delay[$key] * 60;
				$_patch_size=$patch_size[$key];
				//echo("$_start_at $_delay $_patch_size<br />");//dbg

				$lgroupa=explode("|",$gid);
				foreach($lgroupa as $_k => $_v)
				{
					$lgroupa[$_k]="'".(int)$_v."'";
				};
				$lgroup=join(",",$lgroupa);

				$lgroup=($lgroup && $lgroup!="'0'")?"AND lgroup IN ($lgroup)":"";
				//echo("lgroup=$lgroup");//dbg

				$count = $this->get_member_count($lid);
				//echo("count=$count<br /><br />");//dbg

				if (!isset($aid))
				{
					// tee sisestus avoidmids tabelisse
					$this->db_query("INSERT INTO ml_avoidmids (avoidmids) VALUES ('')");
					$aid=$this->db_last_insert_id();
				};
				$total++;
				$this->db_query("INSERT INTO ml_queue (lid,mid,gid,uid,aid,status,start_at,last_sent,patch_size,delay,position,total)
					VALUES ('$lid','$id','$gid','".aw_global_get("uid")."','$aid','0','$_start_at','0','$_patch_size','$_delay','0','$count')");
				
				$this->_log(ST_MAILINGLIST, SA_SEND,"saatis meili $id listi ".$v["name"].":$gname", $lid);
			};
		};
			
		$this->db_query("UPDATE ml_avoidmids SET usagec='$total' WHERE aid='$aid'");

		return aw_global_get("route_back");
	}

	function load_list($id, $nocache = false)
	{
		$this->list_ob = $this->get_object($id, $nocache);
		$ufc_inst = get_instance("mailinglist/ml_list_conf");
		$this->formid = $ufc_inst->get_forms_by_id($this->list_ob["meta"]["user_form_conf"]);
		return $this->list_ob;
	}

	function get_all_user_folders()
	{
		$ar = new aw_array($this->list_ob["meta"]["user_folders"]);
		return $ar->get();
	}

	function get_all_user_folders_defined()
	{
		$ufc_inst = get_instance("mailinglist/ml_list_conf");
		return $ufc_inst->get_folders_by_id($this->list_ob["meta"]["user_form_conf"]);
	}

	function get_mailto_element($lid = 0)
	{
		if ($lid && !is_array($this->list_ob))
		{
			$this->load_list($lid);
		}
		$ufc_inst = get_instance("mailinglist/ml_list_conf");
		$ret = $ufc_inst->get_mailto_element($this->list_ob["meta"]["user_form_conf"]);
		return $ret;
	}

	function flush_member_cache($id = false)
	{
        if ($id === false)
        {
          aw_cache_flush("ml_list::get_members");
        }
        else
        {
          aw_cache_set("ml_list::get_members", $id, false);
        }
	}

	function get_members($id)
	{
		$ret = array();

		$ob = $this->load_list($id);
		$prnts = array($this->list_ob["meta"]["def_user_folder"]);
		if (is_array($this->list_ob["meta"]["user_folders"]))
		{
			$prnts = $prnts+$this->list_ob["meta"]["user_folders"];
		};
		$ar = new aw_array($prnts);
		
		foreach($ar->get() as $prnt)
		{
			if ($prnt > 0)
			{
				$member_list = new object_list(array(
					"parent" => $prnt,
					"class_id" => CL_ML_MEMBER,
					//"status" => STAT_ACTIVE,
				));

				for($o = $member_list->begin(); !$member_list->end(); $o = $member_list->next())
				{
					$ret[$o->id()] = array(
						"oid" => $o->id(),
						"parent" => $o->parent(),
					);
				};
			};
		}
		return $ret;
	}	

	function get_member_count($id)
	{
		return count($this->get_members($id));
	}

	function get_forms_for_list($id)
	{
		$this->load_list($id);
		$ufc_inst = get_instance("mailinglist/ml_list_conf");
		return $ufc_inst->get_forms_by_id($this->list_ob["meta"]["user_form_conf"]);
	}

	function get_all_folders_for_list($id)
	{
		$this->load_list($id);
		$far = $this->get_all_user_folders();
		$ret = array();
		foreach($far as $fid => $fn)
		{
			$ret[$fid] = $fn;
			$ret += $this->get_objects_below(array(
				"parent" => $fid,
				"class" => CL_PSEUDO,
				"full" => true,
				"ret" => ARR_NAME
			));
		}
		return $ret;
	}

	////
	// !adds a brother to the list member to the list
	function add_member_to_list($arr)
	{
		extract($arr);
		global $awt;
		if (aw_global_get("uid") == "kix")
		{
			echo "amtl, line = ".__LINE__." ".$awt->get("fg")."<br />\n";
			flush();
		}
		$this->load_list($lid);
		if (aw_global_get("uid") == "kix")
		{
			echo "amtl, line = ".__LINE__." ".$awt->get("fg")."<br />\n";
			flush();
		}
		$folder = $this->list_ob["meta"]["def_user_folder"];

//		echo "try add user for list $lid base on $mid <br />";

		// check if this member exists in this list already
		$members = $this->get_members($lid);
		if (aw_global_get("uid") == "kix")
		{
			echo "amtl, line = ".__LINE__." ".$awt->get("fg")."<br />\n";
			flush();
		}
//		echo "members for list eq ".join(",", array_keys($members))." checking new member $mid<br />";
		$found = false;
		foreach($members as $_mmid => $mdat)
		{
			$checkoid = $mdat["oid"];
			if ($mdat["brother_of"])
			{
				$checkoid = $mdat["brother_of"];
			}
			if ($checkoid == $mid)
			{
//				echo "found $mid for $checkoid <br />";
				$found = true;
			}
		}
		$mdat = $this->get_object($mid);
		if (aw_global_get("uid") == "kix")
		{
			echo "amtl, line = ".__LINE__." ".$awt->get("fg")."<br />\n";
			flush();
		}

		if (!$found)
		{
			$id = $this->new_object(array(
				"parent" => $folder,
				"name" => $mdat["name"],
				"class_id" => CL_ML_MEMBER,
				"brother_of" => ($mdat["brother_of"] ? $mdat["brother_of"] : $mdat["oid"])
			));
//			echo "not found, adding $mdat[name] <br />";
			if (aw_global_get("uid") == "kix")
			{
				echo "amtl, line = ".__LINE__." ".$awt->get("fg")."<br />\n";
				flush();
			}
			$mlm = get_instance("mailinglist/ml_member");
			$mlm->update_member_name($id);
			if (aw_global_get("uid") == "kix")
			{
				echo "amtl, line = ".__LINE__." ".$awt->get("fg")."<br />\n";
				flush();
			}
		}
	}

	function remove_member_from_list($arr)
	{
		extract($arr);
		$this->load_list($lid);
		$members = $this->get_members($lid);
		foreach($members as $_mid => $mdat)
		{
			if ($mdat["brother_of"] == $mid)
			{
				$this->delete_object($mdat["oid"]);
			}
		}
	}

	function get_list_ids_by_name($name)
	{
		$ret = array();
		$lns = explode(",",$name);
		foreach($lns as $ln)
		{
			$name = substr($ln, 1);
			$ret[] = $this->db_fetch_field("SELECT oid FROM objects WHERE class_id = ".CL_ML_LIST." AND status != 0 AND name = '".$name."'","oid");
		}
		return $ret;
	}

	function update_automatic_list($id)
	{
		// get all members for the list
	    $this->list_ob = false;
		$mem = $this->get_members($id);

		$automatic_form = $this->list_ob["meta"]["automatic_form"];

		$meminf = array();
		// get all correct form entries for list
		$memstr = join(",", array_keys($mem));
		if ($memstr != "")
		{
			$this->db_query("SELECT * FROM ml_member2form_entry LEFT JOIN objects ON objects.oid = member_id WHERE member_id IN($memstr) AND form_id = '$automatic_form' AND objects.status != 0");
			while ($row = $this->db_next())
			{
				$meminf[$row["entry_id"]] = $row;
			}
		}

		// get all form entries
		$finst = get_instance("formgen/form");
		$entries = $finst->get_entries(array("id" => $automatic_form));

		$mem_inst = get_instance("mailinglist/ml_member");

		// now make members from all the form entries that are already not members
		$cr = false;
		foreach($entries as $eid => $ename)
		{
			if (!isset($meminf[$eid]))
			{
				$mem_inst->create_member(array(
					"parent" => $this->list_ob["meta"]["def_user_folder"],
					"entries" => array(
						$automatic_form => $eid
					),
					"conf" => $this->list_ob["meta"]["user_form_conf"]
				));
				$cr = true;
			}
		}
	}

	function get_default_user_folder($id)
	{
		$this->load_list($id);
		return $this->list_ob["meta"]["def_user_folder"];
	}

	function get_conf_id($id)
	{
		$this->load_list($id);
		return $this->list_ob["meta"]["user_form_conf"];
	}

	function get_all_active_varnames($id)
	{
		$ret = array();
		$row = $this->load_list($id);
		$allvars=$this->get_all_varnames();
		foreach ($allvars as $k => $name)
		{
			if ($row["meta"]["vars"][$k])
			{
				$ret[$k] = $name;
			}
		}
		return $ret;
	}

	function parse_alias($args = array())
	{
		$tobj = $this->get_object($args["alias"]["target"]);
		$sub_form_type = $tobj["meta"]["sub_form_type"];
		if (!empty($args["alias"]["relobj_id"]))
		{
			$relobj = $this->get_object($args["alias"]["relobj_id"]);
			if (!empty($relobj["meta"]["values"]["CL_ML_LIST"]["sub_form_type"]))
			{
				$sub_form_type = $relobj["meta"]["values"]["CL_ML_LIST"]["sub_form_type"];
			};
		}
		$tpl = ($sub_form_type == 0) ? "subscribe.tpl" : "unsubscribe.tpl";
		$this->read_template($tpl);
		$this->vars(array(
			"listname" => $tobj["name"],
			"reforb" => $this->mk_reforb("subscribe",array(
				"id" => $args["alias"]["target"],
				"rel_id" => $relobj["oid"],
				"section" => aw_global_get("section"),
			)),
		));
		return $this->parse();

	}

	function subscribe($args = array())
	{
		$list_id = $args["id"];
		$rel_id = $args["rel_id"];
		$rx = $this->db_fetch_row("SELECT * FROM aliases WHERE target = '$list_id' AND relobj_id = '$rel_id'");
		if (empty($rx))
		{
			die("miskit on mäda");
		};
		$list_obj = $this->get_object(array(
			"oid" => $list_id,
			"clid" => $this->clid,
		));
		// I have to check whether subscribing requires confirmation, and if so, send out the confirm message
		// subscribe confirm works like this - we still subscribe the member to the list, but make
		// her status "deactive" and generate her a confirmation code
		// confirm code is added to the metad
		if ($args["op"] == 1)
		{
			$ml_member = get_instance("mailinglist/ml_member");
			$retval = $ml_member->subscribe_member_to_list(array(
				"name" => $args["name"],
				"email" => $args["email"],
				"list_id" => $args["id"],
				"confirm_subscribe" => $list_obj["meta"]["confirm_subscribe"],
				"confirm_message" => $list_obj["meta"]["confirm_subscribe_msg"],
			));	
		};
		if ($args["op"] == 2)
		{
			$ml_member = get_instance("mailinglist/ml_member");
			$retval = $ml_member->unsubscribe_member_from_list(array(
				"email" => $args["email"],
				"list_id" => $args["id"],
			));	
		};
		$relobj = $this->get_object(array(
			"oid" => $rel_id,
			"clid" => CL_RELATION,
		));

		$mx = $relobj["meta"]["values"]["CL_ML_LIST"];
		if (!empty($mx["redir_obj"]))
		{
			$retval = $mx["redir_obj"];
		}
		elseif (!empty($list_obj["meta"]["redir_obj"]))
		{
			$retval = $list_obj["meta"]["redir_obj"];
		}
		return $this->cfg["baseurl"] . "/" . $retval;
			
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
			SELECT m_objects.name as member, l_objects.name as lid, tm, subject, id
			FROM ml_sent_mails
			LEFT JOIN objects AS m_objects ON m_objects.oid = ml_sent_mails.member
			LEFT JOIN objects AS l_objects ON l_objects.oid = ml_sent_mails.lid
			WHERE lid = '$id' AND mail = '$_mid'";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$row["member"] = "<a href='".$this->mk_my_orb("show_mail", array("id" => $id, "mail_id" => $row["id"]))."'>".$row["member"]."</a>";
			$t->define_data($row);
		}
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
		$q = "SELECT count(*) AS cnt FROM ml_sent_mails WHERE lid = '$list_id' AND mail = '$mail_id'";
		$this->db_query($q);
		$row = $this->db_next();
		$served_count = $row["cnt"];

		$url = $_SERVER["REQUEST_URI"];

		if (!headers_sent() && $served_count < $member_count)
		{
			header("Refresh: 30; url=$url");
		};
		return "Teema: ${name}, liikmeid: $member_count, saadetud: $served_count<br>";
	}

	function callback_mod_tab($arr)
	{
		// hide it, if no mail report is open
		if ($arr["id"] == "mail_report" && empty($arr["request"]["mail_id"]))
		{
			return false;
		};
	}		

	function show_mail($arr)
	{
                extract($arr);
                $this->read_template("show_mail.tpl");
                $ob = $this->get_object($id);
                $this->mk_path($ob["parent"], "<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Mailide nimekiri</a> / Vaata maili");

                $row = $this->db_fetch_row("SELECT * FROM ml_sent_mails WHERE id = '$mail_id'");
                $this->vars(array(
                        "from" => $row["mailfrom"],
                        "subject" => $row["subject"],
                        "message" => nl2br($row["message"])
                ));
                return $this->parse();
	}
};
?>
