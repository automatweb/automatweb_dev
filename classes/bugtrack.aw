<?php

define("MAIL_ADD", "lisas ");
define("MAIL_CHANGE", "muutis ");

session_register("search_opts");

classload("replicator");
class bugtrack extends aw_template 
{
	function bugtrack()
	{
		$this->init("automatweb/bugtrack");

		$this->sitekeys=$this->cfg["sitekeys"];
		if (!isset($this->sitekeys) || !is_array($this->sitekeys))
		{
			$this->raise_error(ERR_BT_NOKEYS,"Sitekeys array on määramata! See tuleb panna saidi const.aw-sse",true);
		};
	
		////
		//! mis on developerite grupi id
		$bugtrack_developergid = $this->get_cval("bugtrack_developergid");
		if (!$bugtrack_developergid)
		{
			$this->raise_error(ERR_BT_NOGRP,"developerite grupi GID on defineerimata",true);
		}
		$this->devgroupid=$bugtrack_developergid;
		$this->admgroupid=$this->get_cval("bugtrack_admgid");

		////
		// !Kõikvõimalikud asjad mis on nyyd puugi asemel
		$this->bugtypes = array(
			"0" => "Bug",
			"1" => "Featuur",
			"2" => "Muu",
		);

		////
		// !Kõikvõimalikud staatused
		$this->statlist = array(
			"" => "",
			"1" => "uus",
			"2" => "määratud",
			"3" => "taasavatud",
			"4" => "lahendatud",
			"5" => "koopia",
			"6" => "suletud",
			"7" => "mult&ouml;&ouml;tab",
			"8" => "ei paranda",
			"9" => "VajaRohkemInfot"
		);

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
		$this->sevlist = array( 
			"0" => "Väike puudus",
			"1" => "Suur puudus",
			"2" => "Kriitiline",
			"3" => "Blokeerib töö"
		);

		$this->sql_filter=get_instance("sql_filter");
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
					"hours" => array("type" => 1),
					"percent" => array("type" => 1)
				)));
		$this->sql_filter->set_data($this->sql_filter_data);
			
		$this->tablefields=array(
			"id",
			"itype",
			"pri",
			"url",
			"tm",
			"text",
			"uid",
			"title",
			"status",
			"sendmail2",
			"sendmail2_mail",
			"site",
			"severity",
			"developer",
			"timeready",
			"resol",
			"mails",
			"text_result",
			"hours",
			"percent"
		);

		
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
	}


	// Siit hakkab visuaalne osa:

	/**  
		
		@attrib name=new params=name default="0"
		
		
		@returns
		
		
		@comment
		! Näitab bugi lisamise formi

	**/
	function orb_new($arr) 
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
		
		$this->read_template("add.tpl");

		$this->mk_header("Lisa bug");

		foreach($this->bugtypes as $typ => $val)
		{
			$this->vars(array(
				"name" => $val,
				"val" => $typ,
				"first" => checked($bt == "")
			));
			$bt.=$this->parse("BUG_TYPE");
		}

		$this->vars(array(
			"uid" => aw_global_get("uid"),
			"url" => "",
			"now" => $this->time2date(),
			"sendmail2_mail" => $this->get_user_mail(aw_global_get("uid")),
			"developerlist" => $this->multiple_option_list(array(),$this->get_userlist()),
			"severitylist" => $this->picker(0,$this->sevlist),
			"time_fixed" => $date_edit->gen_edit_form("time_fixed",time()),
			"reforb" => $this->mk_reforb("submit_new",array()),
			"backlink" => $this->mk_my_orb("list",array(),"",false,true),
			"millekohta" => $this->picker("",$this->mk_plist()),
			"BUG_TYPE" => $bt
		));
		return $this->parse();
	}



	/**  
		
		@attrib name=list params=name default="0"
		
		@param sortby optional
		@param setfilter optional
		@param _setfilter optional
		@param page optional default="0"
		@param search_sess optional
		
		@returns
		
		
		@comment
		! Listib kõik bugid.

	**/
	function orb_list($args)
	{
		$this->read_template("list.tpl");
		extract($args);
		global $bugtr_filt;
		session_register("bugtr_filt");
		
		if ($_setfilter)
		{
			$bugtr_filt=$setfilter;
		};

		if (!$bugtr_filt)
		{
			$bugtr_filt = 0;
		}

		$this->mk_header("Bugide nimekiri");
		
		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "bugtrack",
		));
		$t->parse_xml_def($this->cfg["basedir"] . "/xml/bugtrack/bugtrack.xml");
	
		if ($search_sess )
		{
			$filta = $this->get_search_filter($search_sess);
		}
		else
		{
			$filta=$this->sql_filter->filter_to_sql(array("filter"=>$this->get_filter($bugtr_filt)));
		}

		// make pageselector
		$num = $this->db_fetch_field("SELECT count(*) as cnt FROM bugtrack $filta ORDER BY tm DESC","cnt");
		$per_page = $this->cfg["bugs_per_page"];
		$num_pages = $num / $per_page;
		global $action;
		for ($i=0; $i < $num_pages; $i++)
		{
			$ar = $args;
			$ar["page"] = $i;
			$ar["sort_order"] = $_so;
			$ar["search"] = 1;
			$this->vars(array(
				"link" => $this->mk_my_orb($action,$ar,"",false,true),
				"from" => $i*$per_page,
				"to" => min(($i+1)*$per_page,$num)
			));
			if ($page == $i)
			{
				$p.=$this->parse("SEL_PAGE");
			}
			else
			{
				$p.=$this->parse("PAGE");
			}
		}
		$this->vars(array(
			"PAGE" => $p,
			"SEL_PAGE" => ""
		));

		$q = "SELECT * FROM bugtrack $filta LIMIT ".($page*$per_page).",$per_page";
		$this->db_query($q);
			
		while($row = $this->db_next())
		{
			// et ilma pealkirjata bugisid saaks ka vaadata 06.okt.2001
			if (!$row["title"])
			{
				$row["title"]="!pealkirjata!";
			};
			$row["title"]="<a href='".$this->mk_my_orb("edit",array("id" => $row["id"]),"",false,true)."'>".$row["title"]."</a>";
						 
			$row["__pribgcolor"]=$this->pricolor[min($row["pri"],9)];
			$row["__statbgcolor"]=$this->statcolor[$row["status"]];

			$row["vali"]="<input type='checkbox' NAME='sel[]' value='".$row["id"]."'>";
			//tee üle läinud teist värvi
			if ($row["timeready"]<time() && $row["status"]!=$this->stat4 && $row["status"]!=$this->stat6)
			{
				$row["__trbgcolor"]=$this->pricolor["9"];
			};

			$row["severity"] = $this->sevlist[$row["severity"]];
			$t->define_data($row);
		};

		$t->sort_by();
		$this->vars(array(
			"table" => $t->draw(),
			"filter" =>"",
			"fgroup"=>"",
			"l_delegate" => "orb.aw?class=bugtrack&action=delegate&type=popup",
			"l_setfilter" => $this->mk_my_orb("list",array(),"",false,true),
			"reforb" => $this->mk_reforb("list",array(),"",false,true)
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=delegate params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment
		! Näitab developeri määramise akent

	**/
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


	/** Näitab bugi editimise formi. 
		
		@attrib name=edit params=name default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function orb_edit($arr)
	{
		extract($arr);

		$bug = $this->get_bug($id);

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

		if ($this->is_admin($bug))
		{
			$this->read_template("edit.tpl");
		}
		else
		{
			$this->read_template("edit_view.tpl");
		}
		
		if (!$this->is_admin($bug))
		{
			unset($this->statlist[6]);
		}

		$this->mk_header("Muuda bugi");

		$comm = $this->get_comments_for_bug($id);
		foreach($comm as $row)
		{
			$this->vars(array(
				"m_uid" => $row["uid"],
				"m_date" => $this->time2date($row["tm"],2),
				"m_text" => create_links(format_text($row["comment"]))
			));
			$l.=$this->parse("COMMENT");
		}

		$this->vars(array(
			"uid" => $bug["uid"],
			"now" => $this->time2date($bug["tm"]),
			"reforb" => $this->mk_reforb("submit_edit",array("id" => $bug["id"], "ref" => "hmm??")),
			"backlink" => $this->mk_my_orb("list",array(),"",false,true),
			"BUG_TYPE" => $this->bugtypes[$bug["itype"]],
			"millekohta" => $this->picker("",$this->mk_plist()),
			"url" => $bug["url"],
			"priority" => $bug["pri"],
			"developerlist" => $this->multiple_option_list(array_flip(explode(",",$bug["developer"])),$this->get_userlist($bug["developer"])),
			"mails" => $bug["mails"],
			"severitylist" => $this->picker($bug["severity"],$this->sevlist),
			"severity" => $this->sevlist[$bug["severity"]],
			"time_fixed" => $date_edit->gen_edit_form("time_fixed",$bug["timeready"]),
			"time_fixed_v" => $this->time2date($bug["timeready"],2),
			"title" => $bug["title"],
			"m_text" => create_links(format_text($bug["text"])),
			"hours" => $bug["hours"],
			"statuses" => $this->picker($bug["status"],$this->statlist),
			"COMMENT" => $l,
			"percent" => $bug["percent"]
		));
		return $this->parse();
	}




	// ORB submit funktsioonid

	/** orb_new submit funktsioon 
		
		@attrib name=submit_new params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function orb_submit_new($arr)
	{
		extract($arr);

		$rc = new replicator_client($this->cfg["mastersite"]."/automatweb/bugreplicate.aw",$this->sitekeys[$this->cfg["mastersite"]]);
	
		$arr=array_merge($arr,array(
			"tm"=>time(),
			"uid"=>aw_global_get("uid"),
			"developer"=>join(",",is_array($developer)?$developer:array()),
			"timeready"=>mktime($time_fixed["hour"],$time_fixed["minute"],0,$time_fixed["month"],$time_fixed["day"],$time_fixed["year"]),
			"sendmail2_mail"=>$this->get_user_mail(aw_global_get("uid")),
			"site"=>$this->cfg["baseurl"],
			"devloper_mail" => $this->get_user_mail(join(",",is_array($developer)?$developer:array()))
		));

		$req=$rc->query("new_bug",$arr,1);

		if ($req["error"] || !$req["id"])
		{
			$this->raise_error(ERR_BT_EADD,"VIGA bugi lisamisel ".$req["error"],true);
		};

		// pane kohalikku tabelisse 
		if ($this->cfg["baseurl"] != $this->cfg["mastersite"])
		{
			$arr["id"]=$req["id"];
			$this->q_insert($arr);
		};

		$this->update_mail($req["id"],MAIL_ADD);

		// logi 
		$this->_log(ST_BUG, SA_ADD,$this->bugtypes[$arr["bug_type"]]." $title");

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
				"thisuid"=>aw_global_get("uid"),
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
			{
				@mail($bug["sendmail2_mail"],$subject,$msg,"From: bugtrack <bugtrack@struktuur.ee>");
			}
			
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

	/** orb_edit submit funktsioon 
		
		@attrib name=submit_edit params=name default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function orb_submit_edit($arr)
	{
		extract($arr);

		$bug = $this->get_bug($id);
		
		$developer=join(",",is_array($developer)?$developer:array());
		$arr=array_merge($arr,array(
			"developer" => $developer,
		));
		if (is_array($time_fixed))
		{
			$arr["timeready"] = mktime($time_fixed["hour"],$time_fixed["minute"],0,$time_fixed["month"],$time_fixed["day"],$time_fixed["year"]);
		}
		
		// master updateb saidis ja sait masteris
		$rc=($this->cfg["baseurl"] == $this->cfg["mastersite"])?
			new replicator_client($bug["site"]."/automatweb/bugreplicate.".$this->cfg["ext"],$this->sitekeys[$bug["site"]]):
			new replicator_client($this->cfg["mastersite"]."/automatweb/bugreplicate.".$this->cfg["ext"],$this->sitekeys[$this->cfg["mastersite"]]);

		$req=$rc->query("update_bug",$arr,1);
		
		if ($req["error"])
		{
			$this->raise_error(ERR_BT_EREPLICATE,"bugtrack::replicate VIGA bugi uuendamisel ".$req["error"],true);
		};

		// järjekord peab nii olema, muidu läheb syncist välja kui tekib viga
		// update kohalikus tabelis kui puuk ise pole masterist pandud
		if ($bug["site"] != $this->cfg["mastersite"])
		{
			$this->q_update($arr);
		}

		$this->update_mail($id,MAIL_CHANGE);

		// logi 
		$this->_log(ST_BUG, SA_CHANGE,$bug["title"]);
		
		return $this->mk_my_orb("edit",array("id" => $id),"",false,true);
	}

	/**  
		
		@attrib name=submit_delegate params=name default="0"
		
		@param id required
		@param developer required
		@param status required
		
		@returns
		
		
		@comment
		! orb delegate sumbit funktsioon

	**/
	function orb_submit_delegate($arr)
	{
		extract($arr);
		$bug = $this->get_bug($id);

		$developer=$arr["developer"]=join(",",is_array($arr["developer"])?$arr["developer"]:array());

		// master updateb saidis ja sait masteris
		$rc=($this->cfg["baseurl"] == $this->cfg["mastersite"])?
			new replicator_client($bug["site"]."/automatweb/bugreplicate.aw",$this->sitekeys[$bug["site"]]):
			new replicator_client($this->cfg["mastersite"]."/automatweb/bugreplicate.aw",$this->sitekeys[$this->cfg["mastersite"]]);

		$req=$rc->query("update_bug",$arr,1);

		if ($req["error"])
		{
			$this->_log(ST_BUG, SA_RAISE_ERROR,"bugtrack::replicate VIGA bugi uuendamisel");
			die("VIGA bugi uuendamisel ".$req["error"]);
		};


		// update kohalikus tabelis kui puuk ise pole masterist pandud
		if ($bug["site"]!=$this->cfg["mastersite"])
		{
			$this->q_update($arr);
		}
		
		if ($bug["status"]!=$this->stat4)
		{
			$this->send_fixed_mail(array_merge($bug,$arr));
		}

		// logi 
		$this->_log(ST_BUG, SA_ASSIGN,"Määras bugi ".$bug["title"]." ".$developer."-le");

		$GLOBALS["reforb"]=0;// ära redirecti siin midagi krt.
		//echo "",," br";
		die("<script language=\"Javascript\">
		
		window.opener.location=\"".$this->mk_my_orb("list",array(),"",false,true)."\";
		window.close();
		</script>");
	}


	/** Kustutab bugi 
		
		@attrib name=delete params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function orb_delete($arr) 
	{
		extract($arr);
		if (is_array($sel))
		{
			foreach($sel as $id)
			{
				$buk = $this->get_bug($id);
				$this->_log(ST_BUG, SA_DELETE,$buk["title"]);
				
				// kustuta kohalikust tabelist kui puuk ise pole masterist pandud
				if ($buk["site"]!=$this->cfg["mastersite"])
				{
					$this->q_delete($arr);
				}

				// master kustutab saidist ja sait masterist
				$rc=($this->cfg["baseurl"] == $this->cfg["mastersite"])?
					new replicator_client($buk["site"]."/automatweb/bugreplicate.aw",$this->sitekeys[$buk["site"]]):
					new replicator_client($this->cfg["mastersite"]."/automatweb/bugreplicate.aw",$this->sitekeys[$this->cfg["mastersite"]]);

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
		$pri = (int)$pri;
		$q="INSERT INTO bugtrack (id,pri,url,tm,text,title,uid,sendmail2_mail,site,developer,timeready,severity,developer_mail,itype,mails,status) 
				  VALUES('$id','$pri',
					'$url',
					'$tm',
					'$text',
					'$title',
					'$uid',
					'$sendmail2_mail',
					'$site',
					'$developer',
					'$timeready',
					'$severity',
					'$developer_mail',
					'$bug_type',
					'$mails',
					1)";
		return $this->db_query($q,false);
	}

	function q_update($arr)
	{
		$id=(int)$arr["id"];
		unset($arr["id"]);

		$comm = $arr["text"];
		unset($arr["text"]);

		// ok, now add comment to comments table
		if ($comm == "")
		{
			// if comment is empty check what has changed 
			$bug = $this->get_bug($id);
			if ($bug["status"] != $arr["status"])
			{
				$comm .= "*** ".$arr["r_uid"]." muutis statust: ".$this->statlist[$bug["status"]]." -> ".$this->statlist[$arr["status"]]."\n";
			}
			if ($bug["pri"] != $arr["pri"] && $arr["pri"] != "")
			{
				$comm .= "*** ".$arr["r_uid"]." muutis prioriteeti: ".$bug["pri"]." -> ".$arr["pri"]."\n";
			}
			if ($bug["developer"] != $arr["developer"])
			{
				$comm .= "*** ".$arr["r_uid"]." m22ras ymber: ".$bug["developer"]." -> ".$arr["developer"]."\n";
			}
			if ($bug["severity"] != $arr["severity"] && $arr["severity"] != "")
			{
				$comm .= "*** ".$arr["r_uid"]." muutis t6sidust: ".$this->sevlist[$bug["severity"]]." -> ".$this->sevlist[$arr["severity"]]."\n";
			}
			if ($bug["timeready"] != $arr["timeready"] && $arr["timeready"] != "")
			{
				$comm .= "*** ".$arr["r_uid"]." muutis valmis ajaks: ".$this->time2date($bug["timeready"],2)." -> ".$this->time2date($arr["timeready"],2)."\n";
			}
			if ($bug["percent"] != $arr["percent"] && $arr["percent"] != "")
			{
				$comm .= "*** ".$arr["r_uid"]." muutis valmiduse astet: ".$bug["percent"]."% -> ".$arr["percent"]."%\n";
			}
		}

		if ($comm != "")
		{
			$this->db_query("INSERT INTO bugtrack_comments(bug_id,uid,tm,comment) VALUES('$id','".$arr["r_uid"]."','".time()."','".$comm."')");
		}

		$fields=array_flip($this->tablefields);
		foreach($arr as $k => $v )
		{
			if ($fields[$k])
			{
				$q.=($q?",":"")." $k = '$v'";
			}
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
		{
			foreach($sela as $k)
			{
				if ($k && !$users[$k])
				{
					$users[$k]=$k;
				}
			};
		}
		return $users;
	}
	
	function get_user_mail($uid)
	{
		if (!isset($this->cl_users))
		{
			$this->cl_users = get_instance("users");
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
		if ((stristr(aw_global_get("REQUEST_URI"),"/automatweb")!=false))
		{
			$GLOBALS["site_title"]=$what;
		} 
		else
		{
			$this->m='<table border=0 width="100%" cellspacing=1 cellpadding=0 bgcolor="#FFFFFF"><tr><td align="left" class="header1">'.$what.'</td></tr></table>';
		};
	}

	/**  
		
		@attrib name=filter_edit params=name default="0"
		
		@param id required
		@param change_part optional
		@param is_change_part optional
		
		@returns
		
		
		@comment
		! Näitab filtri muutmise akent

	**/
	function orb_filter_edit($arr)
	{
		extract($arr);
//		$this->__set_site_title("&nbsp;/&nbsp;<a href='".$this->mk_my_orb("filters",array(),"",false,true)."'>Filtrid</a>&nbsp;/&nbsp;Muuda filtrit");
		
		return $this->m.$this->sql_filter->do_filter_edit(array(
			"is_change_part" => $arr["is_change_part"],
			"change_part" => $arr["change_part"],
			"filter" => $this->get_filter($id),
			"reforb_func" => "submit_filter_edit",
			"reforb_edit_func" => "filter_edit",
			"reforb_class" => "bugtrack",
			"reforb_arr" => array("id" => $id),
			"reforb" => $this->mk_reforb("submit_filter_edit",array("id" => $id)),
			"header" => $this->mk_header("Muuda filtrit")
			));
	}

	// paneb valitud filtrid cut olekusse
	/**  
		
		@attrib name=filters_cut params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=filters_paste params=name default="0"
		
		
		@returns
		
		
		@comment
		eemaldab cut olekus filtrid gruppidest ja lisab nad target gruppi

	**/
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
				$this->users=get_instance("users");
			};
			$this->bugtr_filters=$this->users->get_user_config(array("uid" => aw_global_get("uid"), "key" => "bugtr_filters"));
			$this->bugtr_filters["0"] = array(
				"name" => "DEFAULT: minu lahtised",
				"nump" => 1,
				"p0" => array(
					"field" => "bugtrack.developer",
					"op" => "LIKE",
					"join" => "and",
					"val" => aw_global_get("uid"),
					"type" => "",
				),
				"p1" => array(
					"field" => "bugtrack.status",
					"op" => " != ",
					"join" => "and",
					"val" => 6,
					"type" => "",
				)
			);
			$this->bugtr_filters["-1"] = array(
				"name" => "DEFAULT: k&otilde;ik",
				"nump" => 1,
				"p0" => array(
					"field" => "bugtrack.developer",
					"op" => "LIKE",
					"join" => "and",
					"val" => "%",
					"type" => "",
				)
			);
			if ($g)
			{
				$this->bugtr_fgroups=$this->users->get_user_config(array("uid" => aw_global_get("uid"), "key" => "bugtr_fgroups"));
				$this->bugtr_fgroups[1]["p"][] = "0";
				$this->bugtr_fgroups[1]["p"][] = "-1";
			}
		};
	}

	function __save_filters($g=0)
	{
		if (!isset($this->users))
		{
			$this->users=get_instance("users");
		};
		unset($this->bugtr_filters["0"]);
		unset($this->bugtr_filters["-1"]);
		$this->users->set_user_config(array("uid" => aw_global_get("uid"), "key" => "bugtr_filters", "value" => $this->bugtr_filters));

		if ($g)
		{
			$tmp = $this->bugtr_fgroups[1]["p"];
			foreach($tmp as $k => $v)
			{
				if ($v == 0 || $v == -1)
				{
					unset($this->bugtr_fgroups[1]["p"][$k]);
				}
			}
			$this->users->set_user_config(array("uid" => aw_global_get("uid"), "key" => "bugtr_fgroups", "value" => $this->bugtr_fgroups));
		}
	}

	// kustutab yhe filtri grupi seest
	/**  
		
		@attrib name=filters_del params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=filters_down params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function orb_filters_down($arr)
	{
		return $this->orb_filters_move(array_merge($arr,array("delta"=>"1")));
	}

	/**  
		
		@attrib name=filters_up params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=submit_filter_edit params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function orb_submit_filter_edit($arr)
	{
		extract($arr);
		$arr["filter"]=$this->get_filter($id);
		
		$this->bugtr_filters[$id]=$this->sql_filter->do_submit_filter_edit($arr);

		$this->__save_filters(0);
		return $this->mk_my_orb("filter_edit",array("id" => $id),"",false,true);
	}

	/**  
		
		@attrib name=filter_edit_down params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function orb_filter_edit_down($arr)
	{
		return $this->orb_filter_edit_move(array_merge($arr,array("delta"=>"1")));
	}

	/**  
		
		@attrib name=filter_edit_up params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=filters_export params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=filters_import params=name default="0"
		
		@param gid optional
		
		@returns
		
		
		@comment

	**/
	function orb_filters_import($arr)
	{
		extract($arr);
		$this->read_template("filters_import.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_filters_import",array("gid" => $gid)),
		));

		return $this->parse();
	}

	/**  
		
		@attrib name=submit_filters_import params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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
		/*echo("enne:<pre>");print_r($this->bugtr_filters);echo("</pre><br />");
		echo("enne:<pre>");print_r($this->bugtr_fgroups);echo("</pre><br />");*/
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
		/*echo("pärast:<pre>");print_r($this->bugtr_filters);echo("</pre><br />");
		echo("pärast:<pre>");print_r($this->bugtr_fgroups);echo("</pre><br />");*/
		$this->__save_filters(1);
		die("<script language='javascript'>window.close();</script>");
	}

	/**  
		
		@attrib name=filters params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function orb_filters($arr)
	{
		$this->read_template("filters.tpl");
		//$this->__set_site_title("&nbsp;/&nbsp;Filtrid");

		$this->mk_header("Filtrid");

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
			//echo("gname=$gname<br />");//dbg
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

	/**  
		
		@attrib name=filter_edit_change_part params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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
	/**  
		
		@attrib name=filter_edit_add params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function orb_filter_edit_add($arr)
	{
		extract($arr);
		$arr["filter"]=$this->get_filter($id);

		$this->bugtr_filters[$id]=$this->sql_filter->do_filter_edit_add($arr);

		$this->__save_filters(0);
		
		return $this->mk_my_orb("filter_edit",array("id" => $id),"",false,true);
	}

	// filtrile yhe tingimuse kustutamine
	/**  
		
		@attrib name=filter_edit_del params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=filters_newgroup params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=filters_delgroup params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function orb_filters_delgroup($arr)
	{
		extract($arr);
		$this->__load_filters(1);

		if (!is_array($this->bugtr_fgroups))
		{
			$this->bugtr_fgroups=array();
		};
		
		if (is_array($this->bugtr_fgroups[$gid]["p"]))
		{
			foreach($this->bugtr_fgroups[$gid]["p"] as $v)
			{
				unset($this->bugtr_filters[$v]);
			};
		}

		unset($this->bugtr_fgroups[$gid]);
		
		$this->__save_filters(1);

		return $this->mk_my_orb("filters",array(),"",false,true);
	}

	/**  
		
		@attrib name=filters_rengroup params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=filters_new params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function orb_filters_new($arr)
	{
		extract($arr);
		$this->__load_filters(1);

		if (!is_array($this->bugtr_filters) || sizeof($this->bugtr_filters)==0)
		{
			$this->bugtr_filters[1]=array("name"=>"uus","nump"=>0,"shit"=>1);
		} 
		else
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
			$this->users=get_instance("users");
		};
		$df=$this->users->get_user_config(array("uid" => aw_global_get("uid"), "key" => "bugtr_deffilter"));
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
				{
					foreach($gdata["p"] as $order => $fid)
					{
						$out[$gid][]=array("id"=>$fid,"name"=>$this->bugtr_filters[$fid]["name"]);
					};
				}
			};
		};
		return $out;
	}

	////
	// !returns the filter with id = $fid
	// if fid is not specified, returns the default filter - open bugs for the logged in user
	function get_filter($fid)
	{
		if (!$fid)
		{
			$fid = "0";
		}
		$this->__load_filters(1);
		return $this->bugtr_filters[$fid];
	}

	function update_mail($id,$ch_type)
	{
		$link="  ".$this->cfg["baseurl"]."/orb.aw?class=bugtrack&action=edit&id=".$id."  ";

		$bug = $this->get_bug($id);

		$msg = "UID         : ".aw_global_get("uid")." @ ".$this->time2date($bug["tm"],2)."\n";
		$msg.= "Tyyp        : ".$this->bugtypes[$bug["itype"]]."\n";
		$msg.= "ID          : ".$id."\n";
		$msg.= "Mille kohta : ".$bug["url"]."\n";
		$msg.= "Prioriteet  : ".$bug["pri"]."\n";
		$msg.= "Kellele     : ".$bug["developer"]."\n";
		$msg.= "T6sidus     : ".$this->sevlist[$bug["severity"]]."\n";
		$msg.= "Valmis ajaks: ".$this->time2date($bug["timeready"],2)."\n";
		$msg.= "Kulub       : ".$bug["hours"]." tundi\n";
		$msg.= "Percent     : ".$bug["percent"]."%\n";
		$msg.= "Staatus     : ".$this->statlist[$bug["status"]]."\n";
		$msg.= "Pealkiri    : ".$bug["title"]."\n";
		$msg.= "Tekst       : \n".$bug["text"]."\n\n";

		$coms = $this->get_comments_for_bug($id);
		foreach($coms as $row)
		{
			$msg.= "-------- ".$row["uid"]." @ ".$this->time2date($row["tm"],2)." lisas kommentaari: \n".$row["comment"]."\n\n";
		}
		$msg.="\nLink: ".$link."\n";
		$msg = stripslashes($msg);

		$title = "[AW bugtrack] ID: $id - ".aw_global_get("uid")." $ch_type ".$this->bugtypes[$bug["itype"]]." : $baseurl $bug[title] ";

		@mail("bugtrack@struktuur.ee",$title,$msg,"From: bugtrack <bugtrack@struktuur.ee>");
		// get the developer's email 
		$ml = $this->get_user_mail($bug["developer"]);
		if ($ml != "")
		{
			@mail($ml,$title,$msg,"From: bugtrack <bugtrack@struktuur.ee>");
		}

		// siit bugi panijale l2heb meil
		if ($bug["sendmail2_mail"] != "")
		{
			@mail($bug["sendmail2_mail"],$title,$msg,"From: bugtrack <bugtrack@struktuur.ee>");
		}
		// CC list ka
		if ($bug["mails"] != "")
		{
			$adds = explode(",",$bug["mails"]);
			foreach($adds as $ad)
			{
				@mail($ad,$title,$msg,"From: bugtrack <bugtrack@struktuur.ee>");
			}
		}
	}

	function mk_plist()
	{
		$plist=array();
		$plist[]="---- PROGRAMMID ----";
		$prog = aw_ini_get("programs");
		foreach ($prog as $idx => $dta)
		{
			$plist[$dta["name"]]=$dta["name"];
		};

		$plist[]="----- KLASSID ------";
		foreach (aw_ini_get("classes") as $idx => $dta)
		{
			$plist[$dta["name"]]=$dta["name"];
		};

		return $plist;
	}

	function is_admin($bug)
	{
		if (in_array($this->admgroupid,aw_global_get("gidlist")) || aw_global_get("uid") == $bug["uid"])
		{
			return true;
		}
		return false;
	}

	function get_comments_for_bug($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM bugtrack_comments WHERE bug_id = $id ORDER BY tm ASC");
		while ($row = $this->db_next())
		{
			$ret[] = $row;
		}
		return $ret;
	}

	function get_list_headerarr($bugtr_filt,$filters = true)
	{
		$headerarray=array();
		if ($filters)
		{
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
					//echo("fid=$fid fname=$fname<br />");//dbg
					if ($bugtr_filt==$fid) 
					{
						$was_sel=1;
						$sel="selected";
					} 
					else
					{
						$sel="";
					};
					$this->tpl->vars(array(
						"fid" => $fid,
						"fname" => $fname,
						"sel" => $sel
					));
					$fs.=$this->tpl->parse("filter");
				};
				$this->tpl->vars(array(
					"gid" => $gid,
					"gname" => $gname, 
					"sel" => $was_sel?"":"selected", 
					"filter" => $fs
				));

				$headerarray["_".$gid]="</a>".$this->tpl->parse("fgroup")."<a>";
			};
		}
		
		$headerarray=array_merge($headerarray,array(
			$this->mk_my_orb("filters",array(),"",false,true) => "Filtrid",
		));

		$headerarray[$this->mk_my_orb("new",array(),"",false,true)] = "Lisa uus";

		$headerarray['javascript:Do("delete")'] = "Kustuta";
		$headerarray['javascript:DoDelegate()'] = "Määra";

		$headerarray[$this->mk_my_orb("search", array(),"",false,true)] = "Otsi";
		$headerarray[$this->mk_my_orb("user_settings", array(),"",false,true)] = "M&auml;&auml;rangud";
		$headerarray[$this->mk_my_orb("list_errors", array(),"",false,true)] = "Vead";
		$headerarray[$this->mk_my_orb("list", array(),"",false,true)] = "Bugide nimekiri";
		$headerarray[$this->mk_my_orb("show_types", array(),"",false,true)] = "Vigade t&uuml;&uuml;bid";

		return $headerarray;
	}

	/**  
		
		@attrib name=search params=name default="0"
		
		@param search_sess optional
		@param search optional
		@param page optional default="0"
		
		@returns
		
		
		@comment

	**/
	function orb_search($arr)
	{
		// search code here.
		extract($arr);
		$this->read_template("search.tpl");

		$this->mk_header("Otsi buge");
		global $search_opts;
		$opts = array();
		if ($search_sess)
		{
			$opts = $search_opts[$search_sess];
		}

		$this->vars(array(
			"id" => $opts["id"],
			"types" => $this->multiple_option_list($opts["type"],$this->bugtypes),
			"url" => $opts["url"],
			"pri" => $opts["pri"],
			"developer" => $this->multiple_option_list($opts["developer"],$this->get_userlist()),
			"from" => $this->multiple_option_list($opts["from"],$this->get_userlist()),
			"cc" => $opts["cc"],
			"hours" => $opts["hours"],
			"severity" => $this->multiple_option_list($opts["severity"],$this->sevlist),
			"status" => $this->multiple_option_list($opts["status"],$this->statlist),
			"title" => $opts["title"],
			"text" => $opts["text"],
			"filter_groups" => $this->picker(0,$this->get_filter_grp_picker()),
			"list" => $this->mk_my_orb("list", array()),
			"reforb" => $this->mk_reforb("submit_search", array("sess" => $search_sess,"search" => 1))
		));

		if ($search)
		{
			$bt = get_instance("bugtrack");
			$this->vars(array(
				"table" => $bt->orb_list(array("search_sess" => $search_sess,"page" => $page))
			));
		}
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_search params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_orb_search($arr)
	{
		extract($arr);
		
		global $search_opts;
		if (!$sess)
		{
			$sess = gen_uniq_id();
		}

		$arr["type"] = $this->make_keys($arr["type"]);
		$arr["developer"] = $this->make_keys($arr["developer"]);
		$arr["from"] = $this->make_keys($arr["from"]);
		$arr["severity"] = $this->make_keys($arr["severity"]);
		$arr["status"] = $this->make_keys($arr["status"]);

		$search_opts[$sess] = $arr;

		if ($save_as_filter == 1)
		{
			// seivime p2ringu filtrina 2ra
			$this->__load_filters(1);
	
			// koostame filtri
			// ungh this is gonna suck, a ma ei tea kuidas seda muudmoodi teha ka. 
			// ok, here we go. 
			$f_ps = array();
			$cnt = 0;
			if ($arr["id"])
			{
				$f_ps["p".$cnt] = array(
					"field" => "bugtrack.id",
					"op" => "=",
					"join" => "and",
					"val" => $arr["id"]
				);
				$cnt++;
			}
			if (is_array($arr["type"]) && count($arr["type"]) > 0)
			{
				// since we can't group filter parts, we do and search for the reverse set
				$rev = array();
				foreach($this->bugtypes as $typ => $desc)
				{
					if (!isset($arr["type"][$typ]))
					{
						$rev[$typ] = $typ;
					}
				}

				foreach($rev as $tid)
				{
					$f_ps["p".$cnt] = array(
						"field" => "bugtrack.itype",
						"op" => "!=",
						"join" => "and",
						"val" => $tid
					);
					$cnt++;
				}
			}

			if ($arr["url"] != "")
			{
				$f_ps["p".$cnt] = array(
					"field" => "bugtrack.url",
					"op" => "LIKE",
					"join" => "and",
					"val" => $arr["url"]
				);
				$cnt++;
			}
			if ($arr["pri"])
			{
				$f_ps["p".$cnt] = array(
					"field" => "bugtrack.pri",
					"op" => "=",
					"join" => "and",
					"val" => $arr["pri"]
				);
				$cnt++;
			}
			if (is_array($arr["developer"]) && count($arr["developer"]) > 0)
			{
				// developeridest teeme ka tagurpidi seti - kuigi kui nyt uus developer lisatakse, siis see filter
				// l2heb katki, a see on praegu ainuke variant :(

				$dl = $this->get_userlist();
				$rev = array();
				foreach($dl as $us)
				{
					if (!isset($arr["developer"][$ud]))
					{
						$rev[$us] = $us;
					}
				}

				foreach($rev as $tid)
				{
					$f_ps["p".$cnt] = array(
						"field" => "bugtrack.developer",
						"op" => "NOT LIKE",
						"join" => "and",
						"val" => $tid
					);
					$cnt++;
				}
			}
			if ($arr["cc"] != "")
			{
				$f_ps["p".$cnt] = array(
					"field" => "bugtrack.sendmail2_mail",
					"op" => "LIKE",
					"join" => "and",
					"val" => $arr["cc"]
				);
				$cnt++;
			}

			if (is_array($arr["severity"]) && count($arr["severity"]) > 0)
			{
				// since we can't group filter parts, we do and search for the reverse set
				$rev = array();
				foreach($this->sevlist as $typ => $desc)
				{
					if (!isset($arr["severity"][$typ]))
					{
						$rev[$typ] = $typ;
					}
				}

				foreach($rev as $tid)
				{
					$f_ps["p".$cnt] = array(
						"field" => "bugtrack.severity",
						"op" => "!=",
						"join" => "and",
						"val" => $tid
					);
					$cnt++;
				}
			}
			if ($arr["hours"] != "")
			{
				$f_ps["p".$cnt] = array(
					"field" => "bugtrack.hours",
					"op" => "=",
					"join" => "and",
					"val" => $arr["hours"]
				);
				$cnt++;
			}

			if (is_array($arr["status"]) && count($arr["status"]) > 0)
			{
				// since we can't group filter parts, we do and search for the reverse set
				$rev = array();
				foreach($this->statlist as $typ => $desc)
				{
					if (!isset($arr["status"][$typ]))
					{
						$rev[$typ] = $typ;
					}
				}

				foreach($rev as $tid)
				{
					$f_ps["p".$cnt] = array(
						"field" => "bugtrack.status",
						"op" => "!=",
						"join" => "and",
						"val" => $tid
					);
					$cnt++;
				}
			}
			if ($arr["title"] != "")
			{
				$f_ps["p".$cnt] = array(
					"field" => "bugtrack.title",
					"op" => "LIKE",
					"join" => "and",
					"val" => $arr["title"]
				);
				$cnt++;
			}
			if ($arr["text"] != "")
			{
				$f_ps["p".$cnt] = array(
					"field" => "bugtrack.text",
					"op" => "LIKE",
					"join" => "and",
					"val" => $arr["text"]
				);
				$cnt++;
			}

			$f_ps["name"] = $filter_name;
			$f_ps["nump"] = $cnt;

			$numf = count($this->bugtr_filters);
			$this->bugtr_filters[$numf] = $f_ps;
			$this->bugtr_fgroups[$filter_group]["p"][] = $numf;

			$this->__save_filters(1);
		}
		return $this->mk_my_orb("search", array("search_sess" => $sess,"search" => 1),"",false,true);
	}

	function get_search_filter($sess)
	{
		global $search_opts;
		$opts = $search_opts[$sess];

		$cons = array();
		if ($opts["id"])
		{
			$cons[] = " id = '$opts[id]' ";
		}
		if (is_array($opts["type"]) && count($opts["type"]) > 0)
		{
			$cons[] = " itype IN (".join(",",map("%s",$opts["type"])).") ";
		}
		if ($opts["url"] != "")
		{
			$cons[] = "url LIKE '%".$opts["url"]."%' ";
		}
		if ($opts["pri"] != "")
		{
			$cons[] = "pri = '".$opts["pri"]."' ";
		}
		if (is_array($opts["developer"]) && count($opts["developer"]) > 0)
		{
			$cons[] = " (".join(" OR ",map("developer LIKE '%%%s%%'",$opts["developer"])).") ";
		}
		if (is_array($opts["from"]) && count($opts["from"]) > 0)
		{
			$cons[] = " (".join(" OR ",map("uid LIKE '%%%s%%'",$opts["from"])).") ";
		}
		if ($opts["cc"] != "")
		{
			$cons[] = "sendmail2_mail LIKE '%".$opts["cc"]."%' ";
		}
		if (is_array($opts["severity"]) && count($opts["severity"]) > 0)
		{
			$cons[] = " severity IN (".join(",",map("%s",$opts["severity"])).") ";
		}
		if ($opts["hours"] != "")
		{
			$cons[] = "hours = '".$opts["hours"]."' ";
		}
		if (is_array($opts["status"]) && count($opts["status"]) > 0)
		{
			$cons[] = " status IN (".join(",",map("%s",$opts["status"])).") ";
		}
		if ($opts["title"] != "")
		{
			$cons[] = "title LIKE '%".$opts["title"]."%' ";
		}
		if ($opts["text"] != "")
		{
			$cons[] = "text LIKE '%".$opts["text"]."%' ";
		}

		$conss = join(" AND ",$cons);
		if ($conss != "")
		{
			return "WHERE $conss ";
		}
		return "";
	}

	function get_filter_grp_picker()
	{
		$ret = array();
		$this->__load_filters(1);
		foreach ($this->bugtr_fgroups as $nr => $dat)
		{
			$ret[$nr] = $dat["name"];
		}
		return $ret;
	}

	/**  
		
		@attrib name=list_errors params=name default="0"
		
		@param groupby optional
		@param sortby optional
		@param sort_order optional
		
		@returns
		
		
		@comment

	**/
	function orb_list_errors($arr)
	{
		extract($arr);
		if (!$groupby)
		{
			$groupby = "type_id";
		}

		$this->read_template("err_list.tpl");
		$this->mk_header("Vigade nimekiri");

		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "bugtrack",
		));
		$t->define_header("BugTrack",$this->get_list_headerarr(0,false));
		$t->parse_xml_def($this->cfg["basedir"] . "/xml/bugtrack/errors.xml");

		$this->db_query("SELECT *,count(*) as cnt FROM bugtrack_errors GROUP BY $groupby");
		while ($row = $this->db_next())
		{
			$row["message"] = "<a href='".$this->mk_my_orb("show_error", array("id" => $row["id"]))."'>".$row["message"]."</a>";
			$row["type_id"] = "<a href='".$this->mk_my_orb("show_type", array("type_id" => $row["type_id"]))."'>".$row["type_id"]."</a>";
			$row["site"] = "<a href='".$this->mk_my_orb("show_site", array("site" => $row["site"]))."'>".$row["site"]."</a>";
			$t->define_data($row);
		}

		$t->sort_by();
		$this->vars(array(
			"grpby_id" => checked($groupby == "id"),
			"grpby_type_id" => checked($groupby == "type_id"),
			"grpby_site" => checked($groupby == "site"),
			"table" => $t->draw(),
			"reforb" => $this->mk_reforb("list_errors", array("no_reforb" => true))
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=add_error params=name nologin="1" default="0"
		
		@param site_url required
		@param err_type required
		@param err_msg required
		@param err_content required
		@param err_uid required
		
		@returns
		
		
		@comment

	**/
	function add_error($arr)
	{
		$this->quote(&$arr);
		extract($arr);
		
		$this->db_query("INSERT INTO bugtrack_errors (type_id,message,site,content,tm,err_uid) 
																					VALUES ('$err_type','$err_msg','$site_url','$err_content','".time()."','$err_uid')");

	}

	/**  
		
		@attrib name=show_error params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function orb_show_error($arr)
	{
		extract($arr);
		$this->read_template("show_error.tpl");

		$this->mk_header("Vaata viga");
		$err = $this->get_error($id);
	
		$this->vars(array(
			"tm" => $this->time2date($err["tm"],2),
			"back" => $this->mk_my_orb("list_errors"),
			"uid" => $err["err_uid"],
			"site" => $err["site"],
			"type_id" => $err["type_id"],
			"message" => $err["message"],
			"content" => format_text($err["content"])
		));
		return $this->parse();
	}

	function get_error($id)
	{
		$this->db_query("SELECT * FROM bugtrack_errors WHERE id = '$id'");
		return $this->db_next();
	}

	/**  
		
		@attrib name=show_type params=name default="0"
		
		@param type_id required
		
		@returns
		
		
		@comment

	**/
	function orb_show_error_type($arr)
	{
		extract($arr);
		$this->read_template("error_type.tpl");

		$this->mk_header("Vea t&uuml;&uuml;p");

		$cnt = $this->db_fetch_field("SELECT count(*) as cnt FROM bugtrack_errors WHERE type_id = '$type_id'","cnt");

		$this->db_query("SELECT count(*) as cnt,site FROM bugtrack_errors WHERE type_id = '$type_id' GROUP BY site");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"site" => $row["site"],
				"site_cnt" => $row["cnt"]
			));
			$l.=$this->parse("SITE_CNT");
		}

		load_vcl("table");

		$t = new aw_table(array(
			"prefix" => "bugtrack",
		));
		$t->parse_xml_def($this->cfg["basedir"] . "/xml/bugtrack/err_type_list.xml");

		$this->db_query("SELECT * FROM bugtrack_errors WHERE type_id = '$type_id'");
		while ($row = $this->db_next())
		{
			$row["message"] = "<a href='".$this->mk_my_orb("show_error", array("id" => $row["id"]))."'>".$row["message"]."</a>";
			$t->define_data($row);
		}

		$t->sort_by();
		$this->vars(array(
			"SITE_CNT" => $l,
			"cnt" => $cnt,
			"table" => $t->draw(),
			"back" => $this->mk_my_orb("list_errors", array("groupby" => "type_id"))
		));

		return $this->parse();
	}

	/**  
		
		@attrib name=show_site params=name default="0"
		
		@param site required
		
		@returns
		
		
		@comment

	**/
	function orb_show_site_errors($arr)
	{
		extract($arr);
		$this->read_template("err_site.tpl");

		$this->mk_header("Saidi vead");
		$cnt = $this->db_fetch_field("SELECT count(*) as cnt FROM bugtrack_errors WHERE site = '$site'","cnt");

		$this->db_query("SELECT count(*) as cnt,type_id FROM bugtrack_errors WHERE site = '$site' GROUP BY type_id");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"type" => $row["type_id"],
				"type_cnt" => $row["cnt"]
			));
			$l.=$this->parse("TYPE_CNT");
		}

		load_vcl("table");

		$t = new aw_table(array(
			"prefix" => "bugtrack",
		));
		$t->parse_xml_def($this->cfg["basedir"] . "/xml/bugtrack/err_site_list.xml");

		$this->db_query("SELECT * FROM bugtrack_errors WHERE site = '$site'");
		while ($row = $this->db_next())
		{
			$row["message"] = "<a href='".$this->mk_my_orb("show_error", array("id" => $row["id"]))."'>".$row["message"]."</a>";
			$t->define_data($row);
		}

		$t->sort_by();
		$this->vars(array(
			"TYPE_CNT" => $l,
			"cnt" => $cnt,
			"table" => $t->draw(),
			"back" => $this->mk_my_orb("list_errors", array("groupby" => "site"))
		));

		return $this->parse();
	}

	/**  
		
		@attrib name=show_types params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function orb_show_types($arr)
	{
		extract($arr);
		$this->read_template("error_types.tpl");

		$this->mk_header("Vigade t&uuml;&uuml;bid");

		$nc = array();
		$this->db_query("SELECT type_id,count(*) as cnt FROM bugtrack_err_type_comments GROUP BY type_id");
		while ($row = $this->db_next())
		{
			$nc[$row["type_id"]] = $row["cnt"];
		}

		$errs = aw_ini_get("errors");
		foreach($errs as $tid => $tdat)
		{
			$this->vars(array(
				"type_id" => $tid,
				"type_name" => $tdat["name"],
				"comment" => $this->mk_my_orb("err_type_comment", array("type_id" => $tid)),
				"num_comments" => (int)$nc[$tid]
			));
			$l.=$this->parse("TYPE_LINE");
		}

		$this->vars(array(
			"TYPE_LINE" => $l,
			"back" => $this->mk_my_orb("list_errors")
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=err_type_comment params=name default="0"
		
		@param type_id required
		
		@returns
		
		
		@comment

	**/
	function orb_err_type_comment($arr)
	{
		extract($arr);
		$this->read_template("err_type_comment.tpl");

		$this->mk_header("Kommenteeri vea t&uuml;&uuml;pi");
		$this->db_query("SELECT * FROM bugtrack_err_type_comments WHERE type_id = $type_id ");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"user" => $row["user"],
				"tm" => $this->time2date($row["tm"],2),
				"comment" => format_text($row["comment"])
			));
			$l.=$this->parse("COMMENT");
		}

		$errs = aw_ini_get("errors");
		$this->vars(array(
			"COMMENT" => $l,
			"type_id" => $type_id,
			"type_name" => $errs[$type_id]["name"],
			"reforb" => $this->mk_reforb("submit_err_type_comment", array("type_id" => $type_id)),
			"back" => $this->mk_my_orb("show_types")
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_err_type_comment params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function orb_submit_err_type_comment($arr)
	{
		extract($arr);

		$this->db_query("INSERT INTO bugtrack_err_type_comments(type_id,tm,user,comment) values('$type_id','".time()."','".aw_global_get("uid")."','".$comment."')");

		return $this->mk_my_orb("err_type_comment", array("type_id" => $type_id),"",false,true);
	}

	////
	// !creates the bugtrack header that should be visible everywhere
	function mk_header($loc)
	{
		$this->tpl = new aw_template;
		$this->tpl->tpl_init("automatweb/bugtrack");
		$this->tpl->read_template("header.tpl");

		$this->tpl->vars(array(
			"l_setfilter" => $this->mk_my_orb("list",array(),"",false,true),
		));

		global $bugtr_filt;
		$ha = $this->get_list_headerarr($bugtr_filt);
		$this->tpl->vars(array(
			"content" => join(" | ",map2("<a href='%s'>%s</a>", $ha)),
			"location" => $loc
		));
		$hd = $this->tpl->parse();
		$this->vars(array(
			"header" => $hd
		));
		return $hd;
	}
}
?>
