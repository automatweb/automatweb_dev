<?php
global $orb_defs;
$orb_defs["bugtrack"] = array(
			"new"	=> array("function" => "orb_new", "params" => array()),
			"submit_new" => array("function" => "orb_submit_new", "params" => array()),
			"edit"	=> array("function" => "orb_edit", "params" => array("id")),
			"submit_edit"	=> array("function" => "orb_submit_edit", "params" => array("id")),
			"list"	=> array("function" => "orb_list", "params" => array(),"opt" => array("sortby","setfilt","setpage")),
			"delete"	=> array("function" => "orb_delete", "params" => array("id")),
			"popupfilter" => array("function" => "orb_popupfilter", "params" => array(),"opt"=>array("setfilt","value","op","expr","field","sendupdate")),
			"delegate" => array("function" => "orb_popupdelegate", "params" => array("id")),
			"submit_delegate" => array("function" => "orb_submit_delegate", "params" => array("id","developer","status")),
			"showcomments" => array("function" => "orb_popupshowcomments", "params" => array("id"))
);


classload("aw_template","replicator");
class bugtrack extends aw_template 
{

	function bugtrack()
	{
		$this->mastersite="work.struktuur.ee";
		global $sitekeys;
		$this->sitekeys=$sitekeys;
	
		////
		//! mis on developerite grupi id
		global $bugtrack_developergid;
		$this->devgroupid=$bugtrack_developergid;


		////
		// !Kõikvõimalikud staatused
		$this->statlist = array(
				"0" => "kinnitamata",
				"1" => "uus",
				"2" => "määratud",
				"3" => "taasavatud",
				"4" => "lahendatud",
				"5" => "kinnitatud",
				"6" => "suletud");
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
		$this->sevlist = array(
			"0" => "Ettepanek",
			"1" => "Elementaarne",
			"2" => "Väike puudus",
			"3" => "Suur puudus",
			"4" => "Kriitiline",
			"5" => "Blokeerib töö");

		////
		// !Tabeli väljad filtri jaoks
		$this->tablefields=array("id","pri","url","tm","text","uid","title","status","sendmail2","sendmail2_mail","site","severity","developer","timeready","resol","mails","text_result","developer_mail");
		// 1 on integer
		// 0 on string
		$this->tablefieldtypes=array(1,1,0,1,0,0,0,1,1,0,0,1,0,1,1,0,0,0);
		
		// millised valikud filtris <select alla tulevad
		$this->tablefieldoptions=array(
			"pri"=>array_values($this->prilist),
			"severity"=>array_values($this->sevlist),
			"status"=>array_values($this->statlist),
			"resol"=>array_values($this->reslist)
			);

		// milliseid nimesid filtris näidatakse
		$this->tablefields2names_=array(
			"tm"=>"lisatud",
			"uid"=>"kes",
			"title"=>"pealkiri",
			"sendmail2"=>"saada mail",
			"sendmail2_mail"=>"lisaja email",
			"developer"=>"kellele",
			"timeready"=>"valmis",
			"text_result"=>"tulemus"
			);

		

		foreach ($this->tablefields as $k)
		{
			if ($this->tablefields2names_[$k])
			{
				$this->tablefields2names[$k]=$this->tablefields2names_[$k];
			}
			else 
			{
				$this->tablefields2names[$k]=$k;
			};
		};

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
			"minute" => ""
			));
		
		$this->read_template("add.tpl");

		$this->vars(array(
			"uid" => UID,
			"url" => "",
			"now" => $this->time2date(),
			"sendmail2_mail" => $GLOBALS["user_email"],
			"developerlist" => $this->picker("",$this->get_userlist()),
			"severitylist" => $this->picker(0,$this->sevlist),
			"prilist" => $this->picker(1,$this->prilist),
			"time_fixed" => $date_edit->gen_edit_form("time_fixed",time()),
			"reforb" => $this->mk_reforb("submit_new",array())
			));
		return $this->parse();
	}


	////
	// ! teeb pakitud filtrist query
	function decode_filt($a)
	{
		$fa=unserialize($a);
		if (is_array($fa))
		foreach ($fa as $k => $v)
		{
			list($k,$op,$expr)=explode("`Ÿ",$k);
			// see on siin selleks, et ei saaks päringusse jura panna
			$xlate=array(" "=>"-- ","'"=>"-- ");
			$k=strtr($k,$xlate);
			$op=strtr($op,$xlate);
			$expr=strtr($expr,$xlate);
			$v=strtr($v,"'","`");
			$filta.=($filta?" $op ":"WHERE ")."$k $expr '$v'";
		};
		return $filta;
	}

	////
	//! võtab filtrist kõik väljaga $fieldname seotud võrdlused ära
	function filt_delbyfield(&$a,$fieldname)
	{
		$fa=unserialize($a);
		if (is_array($fa))
		{
			$uus=array();
			foreach ($fa as $k => $v)
			{
				list ($k2,$op,$expr)=explode("`Ÿ",$k);
				if ($k2 != $fieldname)
					$uus[$k]=$v;
			};
			$a=serialize($uus);

		};
	}

	////
	//! lisab filtrile võrdluse
	function filt_add(&$a,$field,$op,$expr,$val)
	{
		$fa=unserialize($a);
		is_array($fa)?"":$fa=array();
		$fa["$field`Ÿ$op`Ÿ$expr"]=$val;
		$a=serialize($fa);
	}

	////
	//! Listib kõik bugid. 
	function orb_list($args)
	{
		if (!$this->prog_acl("view", PRG_BUGTRACK))
		{
			$this->prog_acl_error("view", PRG_BUGTRACK);
		};
		extract($args);
		global $bugtr_filt,$bugtr_haslooked,$bugtr_page;
		session_register("bugtr_filt","bugtr_page");
		

		// default vaade on oma bugid,järjestatud aja järgi ja kiirete lehekylg
		if (!$bugtr_haslooked)
		{
			session_register("bugtr_haslooked");
			$bugtr_haslooked=1;
			$setfilt="my";
			$sortby="timeready";
			$bugtr_page="today";
		};

		if (!$bugtr_page)
		{
			$bugtr_page="today";
		};

		if ($setpage)
		{
			$bugtr_page=$setpage;
		};

		
		switch ($setfilt)
		{
			case "my":
				$this->filt_delbyfield(&$bugtr_filt,"developer");
				$this->filt_add(&$bugtr_filt,"developer","and","=",UID);
				break;
			case "allusers":
				$this->filt_delbyfield(&$bugtr_filt,"developer");
				break;
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
		
		
		switch ($bugtr_page)
		{
			case "today":
				$page="kiired";
				$compare="<=";
				$headerarray[$this->mk_orb("list",array("setpage"=>"others"))]="[aega on]";
				break;
			case "others":
				$page="aega on";
				$compare=">";
				$headerarray[$this->mk_orb("list",array("setpage"=>"today"))]="[kiired]";
				break;
		}


		$headerarray=array_merge($headerarray,array(
			$this->mk_orb("list",array("setfilt"=>"my")) => "Minule",
			$this->mk_orb("list",array("setfilt"=>"allusers")) => "Kõigile",
			"javascript:remote(0,400,210,\"".$this->mk_orb("popupfilter",array())."\");" => "Filter"
			));


		if ($this->prog_acl("add", PRG_BUGTRACK))
		{
			$headerarray[$this->mk_orb("new",array())] = "Lisa uus";
		};

		$t->define_header("BugTrack ($page)",$headerarray);
		$t->parse_xml_def($this->basedir . "/xml/bugtrack/bugtrack.xml");
	
		$filta=$this->decode_filt($bugtr_filt);

		// a mis aeg see siin on?? see on tööpäeva lõpu aeg
		// need bugid mis on vaja hiljem parandada (näit. järgmine päev kell 01:00)
		// jõuab öösel kah teha, järelikult neid "kiirete"  all ei näita
		// tegelikult v6iks siia panna ka time()+24 tundi
		$aeg=mktime(23,59,59,date("m",time()),date("d",time()),date("Y",time()));
		$realfilter=($filta?"$filta AND":"WHERE")." timeready $compare $aeg";
		$q = "SELECT * FROM bugtrack $realfilter	ORDER BY pri DESC";
		$this->db_query($q);
			
		while($row = $this->db_next())
		{
			$row["status"]=$this->statlist[$row["status"]];
			$row["__pribgcolor"]=$this->pricolor[$row["pri"]];

			//tee üle läinud teist värvi
			if ($row["timeready"]<time())
			{
				$row["__trbgcolor"]=$this->pricolor["9"];
			};
			$t->define_data($row);
		};

		if ($sortby)
		{
			$t->sort_by(array("field"=>$sortby));
		} else
		{
			$t->sort_by(array());
		};

		return $t->draw();
	}

	////
	//! suunab kommentaaridesse
	function orb_popupshowcomments($arr)
	{
		$refr = "comments.aw?section=bug_".$arr["id"];
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
			"userlist" => $this->picker($bug["developer"],$this->get_userlist($bug["developer"])),
			"statuslist" => $this->picker($bug["status"],$this->statlist),
			"loc" => $this->mk_orb("submit_delegate",array("id"=>$id))
			));
		return $this->parse();
	}


	////
	//! Näitab filtreerimise popup akent
	function orb_popupfilter($arr)
	{
		global $bugtr_filt;
		session_register("bugtr_filt");
		extract($arr);
		switch ($setfilt)
		{
			case "add":
				$this->filt_add(&$bugtr_filt,$field,$op,$expr,$value);
				break;
			case "clear":
				$bugtr_filt="";
				break;
		};

		$optionarr=array();
		foreach($this->tablefields as $f)
		{
			if ($this->tablefieldoptions[$f])
				$sisu="'".join("','",$this->tablefieldoptions[$f])."'";
			else 
				$sisu="";
			$optionarr[]="new Array($sisu)";
		};

		if ($sendupdate)
		{
			$sendupdate="window.opener.location='".$this->mk_orb("list",array())."';";
		};

		$filta=$this->decode_filt($bugtr_filt);

		$this->read_template("filter.tpl");
		$this->vars(array(
			"fieldlist" => $this->picker("",$this->tablefields2names),
			"filta" => $filta,
			"sendupdate" => $sendupdate,
			"ftypes" => join(",",$this->tablefieldtypes),
			"foptions" => join(",",$optionarr),
			"sendupdateurl" =>$this->mk_orb("popupfilter",array("sendupdate"=>"1")),
			"clearurl" => $this->mk_orb("popupfilter",array("sendupdate"=>"1","setfilt"=>"clear"))
			));
		
		return $this->parse();
	}

	////
	// !Näitab bugi editimise formi.
	function orb_edit($arr)
	{
		extract($arr);
		$bug = $this->get_bug($id);
		// et saaks enda pandud puuke muuta
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
			"minute" => ""
			));
		$this->read_template("edit.tpl");
		$this->vars(array("uid" => $bug["uid"],
			"url" => $bug["url"],
			"id"  => $bug["id"],
			"prilist" => $this->picker($bug["pri"],$this->prilist),
			"statuslist" => $this->picker($bug["status"],$this->statlist),
			"severitylist" => $this->picker($bug["severity"],$this->sevlist),
			"developerlist" => $this->picker($bug["developer"],$this->get_userlist($bug["developer"])),
			"resollist" => $this->picker($bug["resol"],$this->reslist),
			"now" => $this->time2date($bug["tm"]),
			"title" => $bug["title"],
			"text" => format_text($bug["text"]),
			"sendmail2_mail" => $bug["sendmail2_mail"],
			"mails" => "", // ei näe eelmisi vaid saab JUURDE panna
			"text_result" => format_text($bug["text_result"]),
			"sendmail2"	=> ($bug["sendmail2"] == 1 ? "CHECKED" : ""),
			"time_fixed" => $date_edit->gen_edit_form("time_fixed",$bug["timeready"]),
			"reforb" => $this->mk_reforb("submit_edit",array("id" => $bug["id"], "ref" => "hmm??"))
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
		global $SERVER_NAME;
		extract($arr);
		$rc = new replicator_client($this->mastersite."/automatweb/bugreplicate.aw",$this->sitekeys[$this->mastersite]);
	
//		$this->quote($text);
//		$arr["text"]=$text;
		
		$arr=array_merge($arr,array(
			"tm"=>time(),
			"uid"=>UID,
			"timeready"=>mktime($time_fixed["hour"],$time_fixed["minute"],0,$time_fixed["month"],$time_fixed["day"],$time_fixed["year"]),
			"sendmail2_mail"=>$this->get_user_mail(UID),
			"site"=>$SERVER_NAME,
			"developer_mail"=>$this->get_user_mail($arr["developer"])
			));

		$req=$rc->query("new_bug",$arr,1);

		if ($req["error"] || !$req["id"])
		{
			$this->_log("error","bugtrack::replicate VIGA bugi lisamisel");
			die("VIGA bugi lisamisel");
		};

		// pane kohalikku tabelisse kui ei ole work
		if ($SERVER_NAME!=$this->mastersite)
		{
			$arr["id"]=$req["id"];
			$this->q_insert($arr);
		};

		// saada bugtrack@ 
		if ($maildev)
		{
			$msg = UID." lisas vea/idee lehele $url, prioriteediga $pri\n $text";
			@mail("bugtrack@struktuur.ee",sprintf(LC_BUG_BIG_PUUK,$SERVER_NAME,$title),$msg,"From: bugtrack <bugtrack@struktuur.ee>");
		};

		// logi 
		$this->_log("bug",sprintf(LC_BUG_BUG_ADDED,$title));


		return $this->mk_orb("list",array());
		
	}


	////
	// ! orb_edit submit funktsioon
	function orb_submit_edit($arr)
	{
		global $SERVER_NAME;
		extract($arr);
		$bug = $this->get_bug($id);
		// enda pandud bugi saab kaa muuta
		if (!$this->prog_acl("admin", PRG_BUGTRACK) && ($bug["uid"]!=UID || !UID))
		{
			$this->prog_acl_error("admin", PRG_BUGTRACK);
		};

		$devmail=$bug["developer_mail"];
		if ($arr["developer"]!=$bug["developer"])
		{
			$devmail=$this->get_user_mail($arr["developer"]);
		};

		$uusmails=$bug["mails"].($mails?($bug["mails"]?",":"").$mails:"");
		$arr=array_merge($arr,array(
			"timeready"=>mktime($time_fixed["hour"],$time_fixed["minute"],0,$time_fixed["month"],$time_fixed["day"],$time_fixed["year"]),
			"mails"=>$uusmails,
			"developer_mail"=>$devmail
			));
		
///		echo("developer=".$arr["developer"]." mail=".$arr["developer_mail"]);//DBG
		// update kohalikus tabelis kui puuk ise pole masterist pandud
		if ($bug["site"]!=$this->mastersite)
			$this->q_update($arr);

		// master updateb saidis ja sait masteris
		$rc=($SERVER_NAME==$this->mastersite)?
			new replicator_client($bug["site"]."/automatweb/bugreplicate.aw",$this->sitekeys[$bug["site"]]):
			new replicator_client($this->mastersite."/automatweb/bugreplicate.aw",$this->sitekeys[$this->mastersite]);

		$req=$rc->query("update_bug",$arr,1);

		if ($req["error"])
		{
			$this->_log("error","bugtrack::replicate VIGA bugi uuendamisel");
			die(LC_BUG_MISTAKE);
		};

		extract($bug=array_merge($bug,$arr));
		
		if ($status == "4")
		{
			$res = $this->reslist[$resol];
			$this->read_template("mailmsg.tpl");
					
			$this->vars(array_merge($bug,array(
				"thisuid"=>UID,
				"timeready"=>date("H:i:s Y.m.d",$timeready),
				"tm"=>date("H:i:s Y.m.d",$tm),
				"status"=>$bt->statlist[$status],
				"resol"=>$bt->reslist[$resol],
				"severity"=>$bt->sevlist[$severity]
			)));
		
			$msg = $this->parse();

			$subject=sprintf(LC_BUG_FIXED_BUG,$site,$title);
			@mail("dev@struktuur.ee",$subject,$msg,"From: bugtrack <dev@struktuur.ee>");
			if ($bug[sendmail2])
				@mail($bug[sendmail2_mail],$subject,$msg,"From: bugtrack <dev@struktuur.ee>");
			
			if ($bug["mails"]!="")
			{
				$mails=explode(",",$bug["mails"]);
				for($i=0;$i<=count($mails);$i++)
				{
					@mail($mails[$i],$subject,$msg,"From: bugtrack <dev@struktuur.ee>");
				};
			};
		};

		// logi 
		$this->_log("bug",LC_BUG_CHANGED_BUG4.$bug["title"]);

		return $this->mk_orb("list",array());
		
	}

	////
	//! orb delegate sumbit funktsioon
	function orb_submit_delegate($arr)
	{
		if (!$this->prog_acl("admin", PRG_BUGTRACK))
		{
			$this->prog_acl_error("admin", PRG_BUGTRACK);
		};
		global $SERVER_NAME;
		extract($arr);
		$bug = $this->get_bug($id);

		// pane uus developer_mail kui developer muutus
		if ($arr["developer"]!=$bug["developer"])
		{
			$arr["developer_mail"]=$this->get_user_mail($arr["developer"]);
		};

		// update kohalikus tabelis kui puuk ise pole masterist pandud
		if ($bug["site"]!=$this->mastersite)
			$this->q_update($arr);
		
		// master updateb saidis ja sait masteris
		$rc=($SERVER_NAME==$this->mastersite)?
			new replicator_client($bug["site"]."/automatweb/bugreplicate.aw",$this->sitekeys[$bug["site"]]):
			new replicator_client($this->mastersite."/automatweb/bugreplicate.aw",$this->sitekeys[$this->mastersite]);

		$req=$rc->query("update_bug",$arr,1);

		if ($req["error"])
		{
			$this->_log("error","bugtrack::replicate VIGA bugi uuendamisel");
			die("LC_BUG_MISTAKE");
		};

		// logi 
		$this->_log("bug",LC_BUG_NOMI_BUGI.$bug["title"].$developer."-le");

		// see on jura aga muudmoodi ei saanud minumeelest teha
		return "<script language=\"Javascript\">
		window.opener.location=\"".$this->mk_orb("list",array())."\";
		window.close();
		</script>";
	}


	////
	// !Kustutab bugi
	function orb_delete($arr) 
	{
		if (!$this->prog_acl("delete", PRG_BUGTRACK))
		{
			$this->prog_acl_error("delete", PRG_BUGTRACK);
		};
		global $SERVER_NAME;
		extract($arr);
		$buk = $this->get_bug($id);
		$this->_log("bug",LC_BUG_ERASED_BUG.$buk[title]);
		
		

		// kustuta kohalikust tabelist kui puuk ise pole masterist pandud
		if ($buk["site"]!=$this->mastersite)
			$this->q_delete($arr);

		// master kustutab saidist ja sait masterist
		$rc=($SERVER_NAME==$this->mastersite)?
			new replicator_client($buk["site"]."/automatweb/bugreplicate.aw",$this->sitekeys[$buk["site"]]):
			new replicator_client($this->mastersite."/automatweb/bugreplicate.aw",$this->sitekeys[$this->mastersite]);

		$req=$rc->query("delete_bug",$arr,0);		

		// võiks hoopis selline lipp olla, mille järgi orb saab aru kas return on url
		// või mitte. muidu ta võtab ainult http:/ aga mk_orb annab relatiivse urli		
		http_refresh(0,$this->mk_orb("list",array()));
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
		return $this->db_query("INSERT INTO bugtrack (id,pri,url,tm,text,title,uid,sendmail2,sendmail2_mail,site,developer,timeready,severity,developer_mail,alertsent) 
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
					'0')",false);
	}

	function q_update($arr)
	{
		$id=(int)$arr["id"];
		unset($arr["id"]);

		// tunneb ära ainult need väljad mis tabelis on et mingit kala ei tekiks		
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
		if (!$this->devgroupid)
			die(LC_BUG_MISTAKE_NOT_GROUPID);
			
		$this->db_query("SELECT users.uid FROM users,groupmembers where blocked != 1 AND groupmembers.gid=$this->devgroupid AND groupmembers.uid=users.uid");
		while ($row=$this->db_next())
		{
			$users[$row["uid"]]=$row["uid"];
		};
		// see on selleks, et äkki näiteks worki tabelis pole seda inimest, kellele
		// puuk on määratud, siis seal ei näitaks <select is muidu seda inimest yldse
		if ($sel && !$users[$sel])
			$users[$sel]=$sel;
		return $users;
	}
	
	function get_user_mail($uid)
	{
		classload("users");
		$u = new users;
		$ud = $u->fetch($uid);
//		echo("get_user_mail($uid)=".$ud["email"]);
		return $ud["email"];
	}
}
?>
