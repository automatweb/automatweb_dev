<?php
global $orb_defs;
$orb_defs["bugtrack"] = "xml";


classload("aw_template","replicator","sql_filter");
class bugtrack extends aw_template 
{

	function bugtrack()
	{
		$this->mastersite="http://aw.struktuur.ee";
		global $sitekeys;
		$this->sitekeys=$sitekeys;
		if (!isset($sitekeys) || !is_array($sitekeys))
		{
			$this->raise_error("Sitekeys array on määramata! See tuleb panna saidi const.aw-sse",true);
		};
	
		////
		//! mis on developerite grupi id
		global $bugtrack_developergid;
		if (!isset($bugtrack_developergid) || $bugtrack_developergid=="")
		{
			$this->raise_error("developerite grupi GID on defineerimata",true);
		};
		$this->devgroupid=$bugtrack_developergid;



		////
		// !Kõikvõimalikud asjad mis on nyyd puugi asemel
		$this->itypelist = array(
				"0" => "arendus",
				"1" => "müük-marketing",
				"2" => "üldine",
				);

		////
		// !Kõikvõimalikud staatused
		$this->statlist = array(
				"0" => "kinnitamata",
				"1" => "uus",
				"2" => "määratud",
				"3" => "taasavatud",
				"4" => "lahendatud",
				"5" => "kinnitatud",
				"6" => "suletud",
				"7" => "hetkel tegelen");

		$a=array_flip($this->statlist);
		$this->stat4=$a["lahendatud"];
		$this->stat6=$a["suletud"];
		////
		// !Kõikvõimalikud tulemused
		$this->reslist = array(
				"0" => "parandamata",
				"1" => "parandatud",
				"2" => "vale bug",
				"3" => "ei paranda",
				"4" => "hiljem",
				"5" => "tuleta meelde",
				"6" => "koopia",
				"7" => "multöötab");
		////
		// !Kõikvõimalikud prioroteedid
		$this->prilist = array(
			"0" => "0 - madalaim",
			"1" => 1,
			"2" => 2,
			"3" => 3,
			"4" => 4,
			"5" => 5,
			"6" => 6,
			"7" => 7,
			"8" => 8,
			"9" => "9 - kõrgeim");
		
		////
		// !Kõikvõimalikud severity astmed
		// Okei okei,see ei ole üldse kaval aga teistmoodi ei saa seda teha. lauri

		// ?? sama numbrit ei tohi eri tekstiga eri tyybi alla panna
		// sama tekst peab iga tyybi all olema sama nr

		// Ühesõnaga -->  Erinevatele tekstidele peavad vastama erinevad numbrid ja
		// samadele tekstidele peavad vastama samad numbrid
		$this->sevlist[0] = array( //arendus
			"0" => "Ettepanek",
			"1" => "Elementaarne",
			"2" => "Väike puudus",
			"3" => "Suur puudus",
			"4" => "Kriitiline",
			"5" => "Blokeerib töö");

		$this->sevlist[1] = array( //myyk-marketing
			"0" => "Ettepanek",
			"1" => "Elementaarne",
			"2" => "Väike puudus",
			"3" => "Suur puudus",
			"4" => "Kriitiline",
			"5" => "Blokeerib töö",
			"6" => "Peletab kliendid ära");

		$this->sevlist[2] = array( //yldine
			"0" => "Ettepanek",
			"1" => "Elementaarne",
			"2" => "Väike puudus",
			"3" => "Suur puudus",
			"4" => "Kriitiline",
			"5" => "Blokeerib töö",
			"7" => "bla bla bla");


		$this->totalsevlist=array();
		foreach($this->sevlist as $nr => $arr)
		{
			foreach($arr as $k => $v)
			{
				$this->totalsevlist[$k]=$v;
			};
		};

		$this->sql_filter=new sql_filter();
		$this->sql_filter_data=array(
			"BT"=>array(
				"real"=>"bugtrack",
				"fields"=>array(
					"id"=>			array("type"=>1),
					"tüüp"=>		array("type"=>1,"real"=>"itype","select"=>$this->itypelist),
					"prioriteet"=>	array("type"=>1,"real"=>"pri","select"=>$this->prilist),
					"url"=>			array("type"=>0),
					"lisamise_aeg"=>array("type"=>2,"real"=>"tm"),
					"kirjeldus"=>	array("type"=>0,"real"=>"text"),
					"uid"=>			array("type"=>0),
					"pealkiri"=>	array("type"=>0,"real"=>"title"),
					"status"=>		array("type"=>1,"select"=>$this->statlist),
					"mail_parandamisel"=>array("type"=>1,"real"=>"sendmail2"),
					"lisaja_mail"=>	array("type"=>0,"real"=>"sendmail2_mail"),
					"site"=>		array("type"=>0),
					"tõsidus"=>		array("type"=>1,"real"=>"severity","select"=>$this->totalsevlist),
					"kellele"=>		array("type"=>0,"real"=>"developer"),
					"valmis_ajaks"=>array("type"=>2,"real"=>"timeready"),
					"töö_olek"=>	array("type"=>1,"real"=>"resol","select"=>$this->reslist),
					"mails"=>		array("type"=>0),
					"parandaja_märkus"=>array("type"=>0,"real"=>"text_result"),
				
				)));
		$this->sql_filter->set_data($this->sql_filter_data);
			

		$this->tablefields=array("id","itype","pri","url","tm","text","uid","title","status","sendmail2","sendmail2_mail","site","severity","developer","timeready","resol","mails","text_result");

		
		// prioriteetide värvid
		$this->pricolor = array(
			"0" => "#FFFFFF",
			"1" => "#FFFFFF",
			"2" => "#FFF0F0",
			"3" => "#FFEaEa",
			"4" => "#FFD0D0",
			"5" => "#FFCaCa",
			"6" => "#FFB0B0",
			"7" => "#FFAaAa",
			"8" => "#FF9090",
			"9" => "#FF8a8a"
			);

		
		// prioriteetide värvid
		$this->statcolor = array(
			"0" => "#FFFFFF",
			"1" => "#FFFFFF",
			"2" => "#FFFFFF",
			"3" => "#FFFFFF",
			"4" => "#FFFFFF",
			"5" => "#FFFFFF",
			"6" => "#FFFFFF",
			"7" => "#FF00FF",	//ma arvan et lilla sobib hästi ex
			);

		$this->m="";//menu workaround for client side
	
		$this->db_init();
		$this->tpl_init("automatweb/bugtrack");
	}


	
	// Siit hakkab visuaalne osa:

	////
	//! Näitab bugi lisamise formi
	function orb_new($arr) 
	{
		if (!$this->prog_acl("add", PRG_BUGTRACK))
		{
			$this->prog_acl_error("add", PRG_BUGTRACK);
		};

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
		
		$this->read_template("add.tpl");

		$itypes="";
		foreach ($this->itypelist as $k => $v)
		{
			$this->vars(array(
				"itype" => $k,
				"ischecked" => $itypes?"":"checked",
				"itypename" => $v
				));
			$itypes.=$this->parse("itypes");
		};
		$plist=array();
		$plist[]="---- PROGRAMMID ----";
		foreach ($GLOBALS["programs"] as $idx => $dta)
		{
			$plist[$dta["name"]]=$dta["name"];
		};

		$plist[]="----- KLASSID ------";

		foreach ($GLOBALS["class_defs"] as $idx => $dta)
		{
			$plist[$dta["name"]]=$dta["name"];
		};

		$this->vars(array(
			"uid" => UID,
			"url" => "",
			"now" => $this->time2date(),
			"sendmail2_mail" => $this->get_user_mail(UID),
			"developerlist" => $this->multiple_option_list(array(),$this->get_userlist()),
			"severitylist0" => $this->picker(0,$this->sevlist[0]),
			"severitylist1" => $this->picker(0,$this->sevlist[1]),
			"severitylist2" => $this->picker(0,$this->sevlist[2]),
			"itypes" => $itypes,
			"sizeofitypelist" => sizeof($this->itypelist),
			"prilist" => $this->picker(1,$this->prilist),
			"time_fixed" => $date_edit->gen_edit_form("time_fixed",time()),
			"reforb" => $this->mk_reforb("submit_new",array()),
			"backlink" => $this->mk_my_orb("list",array(),"",false,true),
			"millekohta" => $this->picker("",$plist),
			));
		return $this->parse();
	}



	////
	//! Listib kõik bugid. 
	function orb_list($args)
	{
		if (!$this->prog_acl("view", PRG_BUGTRACK))
		{
			$this->prog_acl_error("view", PRG_BUGTRACK);
		};
		$this->read_template("list.tpl");
		extract($args);
		global $bugtr_filt,$bugtr_haslooked;
		session_register("bugtr_filt");
		
		if ($_setfilter)
		{
			$bugtr_filt=$setfilter;
		};

		
		if (!$bugtr_haslooked)
		{
			session_register("bugtr_haslooked");
			$bugtr_haslooked=1;
			$f=$this->get_default_filter();
			if ($f)
			{
				$bugtr_filt=$f;
			};
		};

		
		load_vcl("table");
		global $baseurl;
		global $PHP_SELF;

		$t = new aw_table(array(
			"prefix" => "bugtrack",
			"self" => $PHP_SELF,
			"imgurl" => $baseurl . "/automatweb/images",
			));

		$t->set_header_attribs(array(
			"class" => "bugtrack",
			"action" => "list",
			));
		
		$headerarray=array();
		$filterz=$this->get_filters_by_groups();
		foreach($filterz as $gid => $filters)
		{
			$gname=$filters["name"];
			unset($filters["name"]);
			$was_sel=0;
			$fs="";
			for ($i=0; $i<sizeof($filters) ;$i++)
			{
				$fid=$filters[$i]["id"];
				$fname=$filters[$i]["name"];
				//echo("fid=$fid fname=$fname<br>");//dbg
				if ($bugtr_filt==$fid) 
				{
					$was_sel=1;
					$sel="selected";
				} else
				{
					$sel="";
				};
				$this->vars(array(
					"fid" => $fid,
					"fname" => $fname,
					"sel" => $sel
					));
				$fs.=$this->parse("filter");
			};
			$this->vars(array(
				"gid" => $gid,
				"gname" => $gname, 
				"sel" => $was_sel?"":"selected", 
				"filter" => $fs));

			$headerarray["_".$gid]="</a>".$this->parse("fgroup")."<a>";

		};
		

		
		
		$headerarray=array_merge($headerarray,array(
			$this->mk_my_orb("filters",array(),"",false,true) => "Filtrid",
			));


		if ($this->prog_acl("add", PRG_BUGTRACK))
		{
			$headerarray[$this->mk_my_orb("new",array(),"",false,true)] = "Lisa uus";
		};

		if ($this->prog_acl("admin", PRG_BUGTRACK))
		{
			$headerarray['javascript:Do("delete")'] = "Kustuta";
			$headerarray['javascript:DoDelegate()'] = "Määra";
		};

		$t->define_header("BugTrack",$headerarray);
		
		$t->parse_xml_def($this->basedir . "/xml/bugtrack/bugtrack.xml");
	
		$filta=$this->sql_filter->filter_to_sql(array("filter"=>$this->get_filter($bugtr_filt)));
		


		// siin võtab kommentaaride arvud
		classload("msgboard");
		$mb=new msgboard();

		$commentarr=$mb->get_count_all("bug_%");
		$commentnewarr=$mb->get_count_new("bug_%");

		$q = "SELECT * FROM bugtrack $filta ORDER BY tm DESC";
		//echo($q);//DBG
		$this->db_query($q);
			
		while($row = $this->db_next())
		{
			$topic="bug_".$row["id"];
			$cmnt= "<a href='javascript:remote(0,500,400,\"".$this->mk_my_orb("showcomments",array("id"=> $row["id"]),"",false,true)."\");'><font color='red'>".(int)(isset($commentnewarr[$topic])?$commentnewarr[$topic]:$commentarr[$topic])." / ".(int)$commentarr[$topic]."</font></a>";

			// et ilma pealkirjata bugisid saaks ka vaadata 06.okt.2001
			if (!$row["title"])
			{
				$row["title"]="!pealkirjata!";
			};
			$row["title"]="<a href='".$this->mk_my_orb("edit",array("id" => $row["id"]),"",false,true)."'>".$row["title"]."</a>&nbsp(".$cmnt.")";
						 
			
			$row["__pribgcolor"]=$this->pricolor[$row["pri"]];
			$row["__statbgcolor"]=$this->statcolor[$row["status"]];
			//$row["developer"]=join(",",$row["developer"]);
			

			$row["vali"]="<input type='checkbox' NAME='sel[]' value='".$row["id"]."'><input type='radio' NAME='sel1' value='".$row["id"]."' OnClick='sel1_=".$row["id"]."'>";
			//tee üle läinud teist värvi
			if ($row["timeready"]<time() && $row["status"]!=$this->stat4 && $row["status"]!=$this->stat6)
			{
				$row["__trbgcolor"]=$this->pricolor["9"];
			};

			$row["status"]=$this->statlist[$row["status"]];
			$t->define_data($row);
		};

		if ($sortby)
		{
			$t->sort_by(array("field"=>$sortby));
		} else
		{
			$t->sort_by(array());
		};

		$this->vars(array(
			"table" => $t->draw(),
			"filter" =>"",
			"fgroup"=>"",
			"l_delegate" => "orb.aw?class=bugtrack&action=delegate&type=popup"/*$this->mk_my_orb("delegate",array("type" => "popup"))*/,
			"l_setfilter" => $this->mk_my_orb("list",array(),"",false,true),
			"reforb" => $this->mk_reforb("list",array(),"",false,true)
			));
		return $this->parse();;
	}

	////
	//! suunab kommentaaridesse
	function orb_popupshowcomments($arr)
	{
		$refr = "/automatweb/comments.aw?section=bug_".$arr["id"]."&forum_id=".$GLOBALS["bugtrack_forum"];
		http_refresh(0,$refr);
	}

	////
	//! Näitab developeri määramise akent
	function orb_popupdelegate($arr)
	{
		extract($arr);
		$bug=$this->get_bug($arr["id"]);
		
		$this->read_template("delegate.tpl");
		$this->vars(array(
			"id" => $id,
			"title" => $bug["title"],
			"userlist" => $this->multiple_option_list(array_flip(explode(",",$bug["developer"])),$this->get_userlist($bug["developer"])),
			"statuslist" => $this->picker($bug["status"],$this->statlist),
			"reforb"=> $this->mk_reforb("submit_delegate",array("id" => $id)),
			));
		return $this->parse();
	}


	

	////
	// !Näitab bugi editimise formi.
	function orb_edit($arr)
	{
		extract($arr);
		/*global $ln_dbg;
		session_register("ln_dbg");
		if ($ln_dbg) echo("ln_dbg set");*/
		$bug = $this->get_bug($id);
		if (!$this->prog_acl("admin", PRG_BUGTRACK)  && ($bug["uid"]!=UID || !UID))
		{
			$this->prog_acl_error("admin", PRG_BUGTRACK);
		};

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
		$this->read_template("edit.tpl");
		
		// Et see kes bugi sisestas saaks seda muuta
		if (UID==$bug["uid"])
		{
			$this->vars(array(
				"txt"=>$bug["text"],
				));
			$text=$this->parse("text");
		} else
		{
			$text=format_text($bug["text"]);
		};
		$this->vars(array("uid" => $bug["uid"],
			"iframesrc" => "/automatweb/comments.aw?section=bug_".$id."&forum_id=".$GLOBALS["bugtrack_forum"]."&type=flat",
			"url" => $bug["url"],
			"id"  => $bug["id"],
			"prilist" => $this->picker($bug["pri"],$this->prilist),
			"statuslist" => $this->picker($bug["status"],$this->statlist),
			"severitylist" => $this->picker($bug["severity"],$this->totalsevlist),
			"developerlist" => $this->multiple_option_list(array_flip(explode(",",$bug["developer"])),$this->get_userlist($bug["developer"])),
			"resollist" => $this->picker($bug["resol"],$this->reslist),
			"now" => $this->time2date($bug["tm"]),
			"title" => $bug["title"],
			"text" => $text,
			"sendmail2_mail" => $bug["sendmail2_mail"],
			"mails" => "", // ei näe eelmisi vaid saab JUURDE panna
			"text_result" => $bug["text_result"],
			"sendmail2"	=> ($bug["sendmail2"] == 1 ? "CHECKED" : ""),
			"time_fixed" => $date_edit->gen_edit_form("time_fixed",$bug["timeready"]),
			"reforb" => $this->mk_reforb("submit_edit",array("id" => $bug["id"], "ref" => "hmm??")),
			"backlink" => $this->mk_my_orb("list",array(),"",false,true)
			));
		return $this->parse();
	}




	// ORB submit funktsioonid

	////
	// ! orb_new submit funktsioon
	function orb_submit_new($arr)
	{
		if (!$this->prog_acl("add", PRG_BUGTRACK))
		{
			$this->prog_acl_error("add", PRG_BUGTRACK);
		};
		global $baseurl;
		extract($arr);

		
		//print_r($this->sitekeys);
		//echo("damn mastersite=*".$this->mastersite."*
		//key=*".$this->sitekeys[$this->mastersite]."*<br>");
		//fuckinf word wrap
		$rc = new replicator_client($this->mastersite."/automatweb/bugreplicate.aw",$this->sitekeys[$this->mastersite]);
	
		//$this->quote($text);
		//$arr["text"]=$text;
		$arr=array_merge($arr,array(
			"tm"=>time(),
			"uid"=>UID,
			"developer"=>join(",",is_array($developer)?$developer:array()),
			"timeready"=>mktime($time_fixed["hour"],$time_fixed["minute"],0,$time_fixed["month"],$time_fixed["day"],$time_fixed["year"]),
			"sendmail2_mail"=>$this->get_user_mail(UID),
			"site"=>$baseurl,
			/*"developer_mail"=>$this->get_user_mail($arr["developer"])*/
			));

		$req=$rc->query("new_bug",$arr,1);
//		$this->q_insert($arr);//dbg
		if ($req["error"] || !$req["id"])
		{
			$this->_log("error","bugtrack::replicate VIGA bugi lisamisel ");
			die("VIGA bugi lisamisel ".$req["error"]);
		};

		// pane kohalikku tabelisse 
		if ($baseurl!=$this->mastersite)
		{
			//echo("localid".$req["id"]);//dbg
			$arr["id"]=$req["id"];
			$this->q_insert($arr);
		};

		// saada bugtrack@ 
		if ($maildev)
		{
			$link="  ".$baseurl."/orb.aw?class=bugtrack&action=edit&id=".$req["id"]."  ";
			$msg = stripslashes(UID." lisas ".$this->itypelist[$itype]." idee lehele $url, prioriteediga $pri\nArendaja:".$arr["developer"]."\n $text\n\n$link");
			@mail("bugtrack@struktuur.ee","Uus ".$this->itypelist[$itype]."idee:  $baseurl $title ",$msg,"From: bugtrack <bugtrack@struktuur.ee>");
		};

		// logi 
		$this->_log("bug","Lisas ".$this->itypelist[$itype]."idee $title");

		// mk_my_orb($fun,$arr=array(),$cl_name="",$force_admin = false,$use_orb = array())
		return $this->mk_my_orb("edit",array("id"=> $req["id"]),"",false,true);
		
	}


	////
	// ! saadab parandamise kohta meili
	function send_fixed_mail($bug)
	{
		extract($bug);
		
		if ($status == $this->stat4)
		{
			$this->read_template("mailmsg.tpl");
					
			$this->vars(array_merge($bug,array(
				"thisuid"=>UID,
				"timeready"=>date("H:i:s Y.m.d",$timeready),
				"tm"=>date("H:i:s Y.m.d",$tm),
				"status"=>$this->statlist[$status],
				"resol"=>$this->reslist[$resol],
				"itype" => $this->itypelist[$itype],
				"severity"=>$this->sevlist[$severity]
			)));
		
			$msg=stripslashes($this->parse());
			

			$subject=stripslashes("Parandatud puuk: $site $title");
		
			mail("bugtrack@struktuur.ee",$subject,$msg,"From: bugtrack <bugtrack@struktuur.ee>");
		
			if ($bug["sendmail2"])
				@mail($bug["sendmail2_mail"],$subject,$msg,"From: bugtrack <bugtrack@struktuur.ee>");
			
			if ($bug["mails"]!="")
			{
				$mails=explode(",",$bug["mails"]);
				for($i=0;$i<=count($mails);$i++)
				{
					@mail($mails[$i],$subject,$msg,"From: bugtrack <bugtrack@struktuur.ee>");
				};
			};
		};

	}

	////
	// ! orb_edit submit funktsioon
	function orb_submit_edit($arr)
	{
		global $baseurl;
		
		extract($arr);

		$bug = $this->get_bug($id);
		// enda pandud bugi saab kaa muuta
		if (!$this->prog_acl("admin", PRG_BUGTRACK) && ($bug["uid"]!=UID || !UID))
		{
			$this->prog_acl_error("admin", PRG_BUGTRACK);
		};

		$uusmails=$bug["mails"].($mails?($bug["mails"]?",":"").$mails:"");
		$developer=join(",",is_array($developer)?$developer:array());
		$arr=array_merge($arr,array(
			"timeready" => mktime($time_fixed["hour"],$time_fixed["minute"],0,$time_fixed["month"],$time_fixed["day"],$time_fixed["year"]),
			"mails" => $uusmails,
			"developer" => $developer,
			));
		
		//echo("developer=".$arr["developer"]);//dbg

		// master updateb saidis ja sait masteris
		$rc=($baseurl==$this->mastersite)?
			new replicator_client($bug["site"]."/automatweb/bugreplicate.aw",$this->sitekeys[$bug["site"]]):
			new replicator_client($this->mastersite."/automatweb/bugreplicate.aw",$this->sitekeys[$this->mastersite]);

		$req=$rc->query("update_bug",$arr,1);
		

		if ($req["error"])
		{
			$this->_log("error","bugtrack::replicate VIGA bugi uuendamisel");
			die("VIGA bugi uuendamisel ".$req["error"]);
		};

		// järjekord peab nii olema, muidu läheb syncist välja kui tekib viga
		// update kohalikus tabelis kui puuk ise pole masterist pandud
		if ($bug["site"]!=$this->mastersite)
			$this->q_update($arr);

		
		$wasntfixed=$bug["status"]==$this->stat4?0:1;
		extract($bug=array_merge($bug,$arr));
		
		if ($wasntfixed)
		{
			$this->send_fixed_mail($bug);
		};

		// logi 
		$this->_log("bug","Muutis bugi ".$bug["title"]);
		
		return $this->mk_my_orb("edit",array("id" => $id),"",false,true);
		
	}

	////
	//! orb delegate sumbit funktsioon
	function orb_submit_delegate($arr)
	{
		if (!$this->prog_acl("admin", PRG_BUGTRACK))
		{
			$this->prog_acl_error("admin", PRG_BUGTRACK);
		};
		global $baseurl;
		extract($arr);
		$bug = $this->get_bug($id);


		$developer=$arr["developer"]=join(",",is_array($arr["developer"])?$arr["developer"]:array());

		// master updateb saidis ja sait masteris
		$rc=($baseurl==$this->mastersite)?
			new replicator_client($bug["site"]."/automatweb/bugreplicate.aw",$this->sitekeys[$bug["site"]]):
			new replicator_client($this->mastersite."/automatweb/bugreplicate.aw",$this->sitekeys[$this->mastersite]);

		$req=$rc->query("update_bug",$arr,1);

		if ($req["error"])
		{
			$this->_log("error","bugtrack::replicate VIGA bugi uuendamisel");
			die("VIGA bugi uuendamisel ".$req["error"]);
		};


		// update kohalikus tabelis kui puuk ise pole masterist pandud
		if ($bug["site"]!=$this->mastersite)
			$this->q_update($arr);
		
		if ($bug["status"]!=$this->stat4)
		$this->send_fixed_mail(array_merge($bug,$arr));

		// logi 
		$this->_log("bug","Määras bugi ".$bug["title"]." ".$developer."-le");

		$GLOBALS["reforb"]=0;// ära redirecti siin midagi krt.
		//echo "",," br";
		die("<script language=\"Javascript\">
		
		window.opener.location=\"".$this->mk_my_orb("list",array(),"",false,true)."\";
		window.close();
		</script>");
	}


	////
	// !Kustutab bugi
	function orb_delete($arr) 
	{
		if (!$this->prog_acl("admin", PRG_BUGTRACK)  && ($bug["uid"]!=UID || !UID))
		{
			$this->prog_acl_error("admin", PRG_BUGTRACK);
		};
		global $baseurl;
		extract($arr);
		if (is_array($sel))
		{
			foreach($sel as $id)
			{

				$buk = $this->get_bug($id);
				$this->_log("bug","Kustutas bugi ".$buk[title]);
				
				// kustuta kohalikust tabelist kui puuk ise pole masterist pandud
				if ($buk["site"]!=$this->mastersite)
					$this->q_delete($arr);

				// master kustutab saidist ja sait masterist
				$rc=($baseurl==$this->mastersite)?
					new replicator_client($buk["site"]."/automatweb/bugreplicate.aw",$this->sitekeys[$buk["site"]]):
					new replicator_client($this->mastersite."/automatweb/bugreplicate.aw",$this->sitekeys[$this->mastersite]);

				$cnt++;
				$req=$rc->query("delete_bug",array("id" => $id),1);
			};
		};
		
		return $this->mk_my_orb("list",array(),"",false,true);
	}

	// vajalikud queryd

	function q_getid()
	{
		return 1+$this->db_fetch_field("SELECT MAX(id) AS id FROM bugtrack","id");
	}

	function q_delete($arr)
	{
		return $this->db_query("DELETE FROM bugtrack where id='".$arr["id"]."'",false);
	}

	function q_insert($arr)
	{
		extract($arr);
		$this->quote($text);
		$q="INSERT INTO bugtrack (id,pri,url,tm,text,title,uid,sendmail2,sendmail2_mail,site,developer,timeready,severity,developer_mail,alertsent,itype) 
				  VALUES('$id','$pri',
					'$url',
					'$tm',
					'$text',
					'$title',
					'$uid',
					'$sendmail2',
					'$sendmail2_mail',
					'$site',
					'$developer',
					'$timeready',
					'$severity',
					'$developer_mail',
					'0','$itype')";
//				  echo($q);
		return $this->db_query($q,false);
	}

	function q_update($arr)
	{
		$id=(int)$arr["id"];
		unset($arr["id"]);
		unset($arr["alertsent"]); //seda kah muuta ei saa
		
		$fields=array_flip($this->tablefields);
		foreach($arr as $k => $v )
		{
			if ($fields[$k])
				$q.=($q?",":"")." $k = '$v'";
		};
		return $this->db_query("UPDATE bugtrack set $q where id=$id",false);
	}

	
	////
	// !Annab ühe bugi andmed
	function get_bug($id) 
	{
		return $this->get_record("bugtrack","id",$id);
	}
	
	
	
	////
	// !Teeb useritest array.
	function get_userlist($sel="")
	{
		$this->db_query("SELECT users.uid FROM users,groupmembers where blocked != 1 AND groupmembers.gid=$this->devgroupid AND groupmembers.uid=users.uid ORDER BY uid");
		while ($row=$this->db_next())
		{
			$users[$row["uid"]]=$row["uid"];
		};
		// see on selleks, et äkki näiteks worki grupis pole seda inimest, kellele
		// puuk on määratud, siis seal ei näitaks <select is muidu
		$sela=explode(",",$sel);
		if (is_array($sela))
		foreach($sela as $k)
		{
			if ($k && !$users[$k])
				$users[$k]=$k;
		};
		return $users;
	}
	
	function get_user_mail($uid)
	{
		if (!isset($this->cl_users))
		{
			classload("users");
			$this->cl_users = new users;
		};
		$ud = $this->cl_users->fetch($uid);
		//echo("get_user_mail($uid)=".$ud["email"]);
		return $ud["email"];
	}

	// filtrite stuff algab siit
	// ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

	function __set_site_title($title)
	{
		$what="<a href='".$this->mk_my_orb("list",array(),"",false,true)."'>BugTrack</a>".$title;
		if ((stristr($GLOBALS["REQUEST_URI"],"/automatweb")!=false))
		{
			$GLOBALS["site_title"]=$what;
		} else
		{
			$this->m='<table border=0 width="100%" cellspacing=1 cellpadding=0 bgcolor="#FFFFFF"><tr><td align="left" class="header1">'.$what.'</td></tr></table>';
		};
	}

	////
	//! Näitab filtri muutmise akent
	function orb_filter_edit($arr)
	{
		extract($arr);
		$this->__set_site_title("&nbsp;/&nbsp;<a href='".$this->mk_my_orb("filters",array(),"",false,true)."'>Filtrid</a>&nbsp;/&nbsp;Muuda filtrit");

		return $this->m.$this->sql_filter->do_filter_edit(array(
			"is_change_part" => $arr["is_change_part"],
			"change_part" => $arr["change_part"],
			"filter" => $this->get_filter($id),
			"reforb_func" => "submit_filter_edit",
			"reforb_edit_func" => "filter_edit",
			"reforb_class" => "bugtrack",
			"reforb_arr" => array("id" => $id),
			"reforb" => $this->mk_reforb("submit_filter_edit",array("id" => $id))
			));
	}

	// paneb valitud filtrid cut olekusse
	function orb_filters_cut($arr)
	{
		extract($arr);
		if (!is_array($sel) || !sizeof($sel))
		{
			return $this->mk_my_orb("filters",array(),"",false,true);
		};
		$this->__load_filters(1);
		unset($this->bugtr_fgroups[$gid]["cut"]);
		foreach($sel as $idx)
		{
			$this->bugtr_fgroups[$gid]["cut"][]=$idx;
		};
		$this->__save_filters(1);

		return $this->mk_my_orb("filters",array(),"",false,true);
	}

	// eemaldab cut olekus filtrid gruppidest ja lisab nad target gruppi
	function orb_filters_paste($arr)
	{
		extract($arr);

		$this->__load_filters(1);

		$cutarray=array();
		// Otsi välja, millisest grupist need cut filtrid olid
		foreach ($this->bugtr_fgroups as $gd => $gdata)
		{
			if (isset($gdata["cut"]))//Siit.
			{
				$cutarray_f=array_flip($cutarray=$gdata["cut"]);
				//võta nad siit grupist ära

				if (is_array($gdata["p"]))
				{
					$neworder/*_box_sk*/=array();
					foreach($gdata["p"] as $priority => $fid)
					{
						if (!isset($cutarray_f[$fid]) )
						{
							$neworder[]=$fid;
						};
					};
					$this->bugtr_fgroups[$gd]["p"]=$neworder/*_box_sk*/;
				};
				unset($this->bugtr_fgroups[$gd]["cut"]);
				break;
			};
		};
		// nüüd pane nad sinna gruppi, mille kohal vajutati paste
		foreach($cutarray as $s => $fid)
		{
			$this->bugtr_fgroups[$gid]["p"][]=$fid;
		};
		$this->__save_filters(1);

		return $this->mk_my_orb("filters",array(),"",false,true);
	}

	function __load_filters($g=0)
	{
		if (!isset($this->bugtr_filters) || !isset($this->bugtr_fgroups))
		{
			if (!isset($this->users))
			{
				classload("users");
				$this->users=new users();
			};
			$this->bugtr_filters=$this->users->get_user_config(array("uid" => UID, "key" => "bugtr_filters"));
			if ($g)
				$this->bugtr_fgroups=$this->users->get_user_config(array("uid" => UID, "key" => "bugtr_fgroups"));
		};
	}

	function __save_filters($g=0)
	{
		if (!isset($this->users))
		{
			classload("users");
			$this->users=new users();
		};
		$this->users->set_user_config(array("uid" => UID, "key" => "bugtr_filters", "value" => $this->bugtr_filters));

		if ($g)
			$this->users->set_user_config(array("uid" => UID, "key" => "bugtr_fgroups", "value" => $this->bugtr_fgroups));
	}

	// kustutab yhe filtri grupi seest
	function orb_filters_del($arr)
	{
		extract($arr);
		if (!is_array($sel) || !sizeof($sel))
		{
			return $this->mk_my_orb("filters",array(),"",false,true);
		};

		$this->__load_filters(1);
		
		foreach ($sel as $i)
		{
			unset($this->bugtr_filters[$i]);
		};

		$sel=array_flip($sel);
		$blah2=array();
		//echo("<pre>");print_r($this->bugtr_fgroups[$gid]["p"]);echo("</pre>");//dbg
		foreach($this->bugtr_fgroups[$gid]["p"] as $priority => $fid)
		{
			if (!isset($sel[$fid]))
			{
				$blah2[]=$this->bugtr_fgroups[$gid]["p"][$priority];
			};
		};//blah! järjekord jäheb vist valeks nii
		$this->bugtr_fgroups[$gid]["p"]=$blah2;//vot tak
		//echo("<pre>");print_r($this->bugtr_fgroups[$gid]["p"]);echo("</pre>");//dbg

		$this->__save_filters(1);
		
		return $this->mk_my_orb("filters",array(),"",false,true);
	}

	function orb_filters_down($arr)
	{
		return $this->orb_filters_move(array_merge($arr,array("delta"=>"1")));
	}

	function orb_filters_up($arr)
	{
		return $this->orb_filters_move(array_merge($arr,array("delta"=>"-1")));
	}

	//liigutab filtrit grupis  yles/alla
	function orb_filters_move($arr)
	{
		extract($arr);
		$this->__load_filters(1);
		
		//echo("gname=$gname delta=$delta");//dbg
		if (!($gname==0 && $delta==-1) && !($gname==sizeof($this->bugtr_fgroups[$gid]["p"])-1 && $delta==1))
		{
			$_1=$gname;
			$_2=$gname+$delta;

			$save=$this->bugtr_fgroups[$gid]["p"][$_1];//vaheta
			$this->bugtr_fgroups[$gid]["p"][$_1]=$this->bugtr_fgroups[$gid]["p"][$_2];
			$this->bugtr_fgroups[$gid]["p"][$_2]=$save;
		};
		//print_r($this->bugtr_fgroups[$gid]["p"]);//dbg


		$this->__save_filters(1);
		return $this->mk_my_orb("filters",array(),"",false,true);
	}

	function orb_submit_filter_edit($arr)
	{
		extract($arr);
		$arr["filter"]=$this->get_filter($id);
		
		$this->bugtr_filters[$id]=$this->sql_filter->do_submit_filter_edit($arr);

		$this->__save_filters(0);
		return $this->mk_my_orb("filter_edit",array("id" => $id),"",false,true);
	}

	function orb_filter_edit_down($arr)
	{
		return $this->orb_filter_edit_move(array_merge($arr,array("delta"=>"1")));
	}

	function orb_filter_edit_up($arr)
	{
		return $this->orb_filter_edit_move(array_merge($arr,array("delta"=>"-1")));
	}

	//liigutab filtri tingimus yles/alla
	function orb_filter_edit_move($arr)
	{
		extract($arr);
		$arr["filter"]=$this->get_filter($id);

		$this->bugtr_filters[$id]=$this->sql_filter->do_filter_edit_move($arr);

		$this->__save_filters(0);
		return $this->mk_my_orb("filter_edit",array("id" => $id),"",false,true);
	}

	function orb_filters_export($arr)
	{
		extract($arr);
		$this->__load_filters(1);
		
		$export=array();
		if (is_array($sel))
		foreach ($sel as $i)
		{
			$export[]=$this->bugtr_filters[$i];
		};
		header("Content-type: x-automatweb/filter-export");
		header('Content-disposition: root_access; filename="filters_export.txt"');
		echo(serialize($export));
		die();

	}

	function orb_filters_import($arr)
	{
		extract($arr);
		$this->read_template("filters_import.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_filters_import",array("gid" => $gid)),
		));

		return $this->parse();
	}

	function orb_submit_filters_import($arr)
	{
		global $fail;
		extract($arr);
		if (!is_uploaded_file($fail))
		{
			die("Käi jala ega see pole miski o<piip>ia ega xx<piip>digitali tehtud eks");
		};
		$stuff=join("",file($fail));
		$array=@unserialize($stuff);
		if (!is_array($array))
		{
			die("Tühi fail või miski muu kala filtri importimisel");
		};
		$this->__load_filters(1);
		/*echo("enne:<pre>");print_r($this->bugtr_filters);echo("</pre><br>");
		echo("enne:<pre>");print_r($this->bugtr_fgroups);echo("</pre><br>");*/
		foreach($array as $f)
		{
			$f["leia"]=1;
			$this->bugtr_filters[]=$f;
			$id=0;
			// ehh see on miski imelik kood siin a ma ei oska paremat teha
			foreach( $this->bugtr_filters as $nr => $g)
			if ($g["leia"])
			{
				unset($this->bugtr_filters[$nr]["leia"]);
				$id=$nr;
				break;
			};
			$this->bugtr_fgroups[$gid]["p"][]=$id;
		};
		/*echo("pärast:<pre>");print_r($this->bugtr_filters);echo("</pre><br>");
		echo("pärast:<pre>");print_r($this->bugtr_fgroups);echo("</pre><br>");*/
		$this->__save_filters(1);
		die("<script language='javascript'>window.close();</script>");
	}

	function orb_filters($arr)
	{
		$this->read_template("filters.tpl");
		$this->__set_site_title("&nbsp;/&nbsp;Filtrid");
		//$GLOBALS["site_title"]="<a href='".$this->mk_my_orb("list",array())."'>BugTrack</a>;

		$fparse="";
		$filterz=$this->get_filters_by_groups();

		//print_r($filterz);

		foreach($filterz as $gid => $filters)
		{
			$gname=$filters["name"];
			// Get array of cut filters in this group and flip it
			$cutarray_f=$this->bugtr_fgroups[$gid]["cut"];
			$cutarray_f=array_flip(is_array($cutarray_f)?$cutarray_f:array());
			
			unset($filters["name"]);
			//echo("gname=$gname<br>");//dbg
			$fs="";
			for ($i=0; $i<sizeof($filters) ; $i++)
			{
				$this->vars(array(
					"fid"=> $filters[$i]["id"],
					"gid" => $gid,
					"bgcolor" => isset($cutarray_f[$filters[$i]["id"]])?"#EEEEEE":"#FFFFFF",
					"l_edit" => $this->mk_my_orb("filter_edit",array(),"",false,true),
					"fname" => $filters[$i]["name"]?$filters[$i]["name"]:"!nimeta!",
					"idx" => $i,
					"sql" => $this->sql_filter->filter_to_sql(array(
						"filter"=>$this->get_filter($filters[$i]["id"]),
						"noeval"=>1,
						"fake"=>1
						))
					));
				$fs.=$this->parse("filter");
			};

			$this->vars(array(
				"filter" => $fs,
				"gname" => $gname,
				"kustutauus" => $gid ?"Kustuta" :"Uus",
				"l_kustutauus" => $gid ?"filters_delgroup":"uus",
				"l_import" => $this->mk_my_orb("filters_import",array("gid" => $gid)),
				"gid" => $gid));
			$fparse.=$this->parse("fgroup");
		};

		$this->vars(array(
			"fgroup" => $fparse,
			"reforb" => $this->mk_reforb("filters",array("gid"=>" ","gname"=>" ")),
			));
		return $this->m.$this->parse();
	}

	function orb_filter_edit_change_part($arr)
	{
		extract($arr);
		if (!is_array($sel))
		{
			return $this->mk_my_orb("filter_edit",array("id" => $id),"",false,true);
		};
		$chgnum=$sel[0];
		return $this->mk_my_orb("filter_edit",array("change_part"=> $chgnum,"is_change_part"=>1, "id" => $id),"",false,true);
	}

	// filtrile yhe tingimuse lisamine
	function orb_filter_edit_add($arr)
	{
		extract($arr);
		$arr["filter"]=$this->get_filter($id);

		$this->bugtr_filters[$id]=$this->sql_filter->do_filter_edit_add($arr);

		$this->__save_filters(0);
		
		return $this->mk_my_orb("filter_edit",array("id" => $id),"",false,true);
	}

	// filtrile yhe tingimuse kustutamine
	function orb_filter_edit_del($arr)
	{
		extract($arr);
		if (!is_array($sel) || !sizeof($sel))
		{
			return $this->mk_my_orb("filter_edit",array("id" => $id),"",false,true);
		};
		
		$arr["filter"]=$this->get_filter($id);

		$this->bugtr_filters[$id]=$this->sql_filter->do_filter_edit_del($arr);

		$this->__save_filters(0);
		
		return $this->mk_my_orb("filter_edit",array("id" => $id),"",false,true);
	}

	function orb_filters_newgroup($arr)
	{
		extract($arr);

		$this->__load_filters(1);
		if (!is_array($this->bugtr_fgroups))
		{
			$this->bugtr_fgroups=array();
		};

		$this->bugtr_fgroups[]=array("name"=>$gname,"p"=>array());

		$this->__save_filters(1);

		return $this->mk_my_orb("filters",array(),"",false,true);
	}

	function orb_filters_delgroup($arr)
	{
		extract($arr);
		$this->__load_filters(1);

		if (!is_array($this->bugtr_fgroups))
		{
			$this->bugtr_fgroups=array();
		};
		
		if (is_array($this->bugtr_fgroups[$gid]["p"]))
			foreach($this->bugtr_fgroups[$gid]["p"] as $v)
			{
				unset($this->bugtr_filters[$v]);
			};

		unset($this->bugtr_fgroups[$gid]);
		
		$this->__save_filters(1);

		return $this->mk_my_orb("filters",array(),"",false,true);
	}

	function orb_filters_rengroup($arr)
	{
		extract($arr);
		$this->__load_filters(1);

		if (!is_array($this->bugtr_fgroups))
		{
			$this->bugtr_fgroups=array();
		};
		$this->bugtr_fgroups[$gid]["name"]=$gname;

		$this->__save_filters(1);

		return $this->mk_my_orb("filters",array(),"",false,true);
	}

	function orb_filters_new($arr)
	{
		extract($arr);
		$this->__load_filters(1);

		if (!is_array($this->bugtr_filters) || sizeof($this->bugtr_filters)==0)
		{
			$this->bugtr_filters[1]=array("name"=>"uus","nump"=>0,"shit"=>1);
		} else
		{
			$this->bugtr_filters[]=array("name"=>"uus","nump"=>0,"shit"=>1);
		};
		foreach($this->bugtr_filters as $k => $v)
		{
			if ($v["shit"])
			{
				unset($this->bugtr_filters[$k]["shit"]);
				$id=$k;
				break;
			};
		};
		$this->bugtr_fgroups[$gid]["p"][]=$id;

		$this->__save_filters(1);
		return $this->mk_my_orb("filter_edit",array("id"=>$id),"",false,true);
	}


	function get_default_filter()
	{
		if (!isset($this->users))
		{
			classload("users");
			$this->users=new users();
		};
		$df=$this->users->get_user_config(array("uid" => UID, "key" => "bugtr_deffilter"));
		return $df? $df : 0;
	}

	// tagastab array
	function get_filters_by_groups()
	{
		$this->__load_filters(1);

		//echo("filters=<pre>");print_r($this->bugtr_filters);echo("</pre>*");//dbg
		$out=array();
		if (is_array($this->bugtr_fgroups))
		{
			//register all groups and fill in their filters
			foreach($this->bugtr_fgroups as $gid => $gdata)
			{
				$out[$gid]["name"]=$gdata["name"];
				if (is_array($gdata["p"]))
				foreach($gdata["p"] as $order => $fid)
				{
					$out[$gid][]=array("id"=>$fid,"name"=>$this->bugtr_filters[$fid]["name"]);
				};
			};
		};
		return $out;
	}

	function get_filter($fid)
	{
		$this->__load_filters(1);
		return $this->bugtr_filters[$fid];
	}
}
?>
