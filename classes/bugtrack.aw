<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/bugtrack.aw,v 2.3 2001/05/23 18:21:59 cvs Exp $
// generic bugtracki klass
global $orb_defs;
$orb_defs["bugtrack"] = array(
			"new"	=> array("function" => "add_bug", "params" => array()),
			"save_new" => array("function" => "report_bug", "params" => array()),
			"edit"	=> array("function" => "edit_bug", "params" => array("id")),
			"save"	=> array("function" => "save_bug", "params" => array("id")),
			"list"	=> array("function" => "bug_list", "params" => array("filt"),"opt" => array("sortby")),
			"lists"	=> array("function" => "bug_list", "params" => array("filt"),"opt" => array("sortby")),
			"delete"	=> array("function" => "remove_bug", "params" => array("id")),
			"file"	=> array("function" => "_save_bug_tofile", "params" => array("id")),
			"files"	=> array("function" => "save_all_bugs", "params" => array()),
			"comment"	=> array("function" => "show_comments", "params" => array("id")),
			"upload" => array("function" => "ftp_send", "params" => array()),
			"get" => array("function" => "get_files", "params" => array()),
);


class bugtrack extends aw_template {
	function bugtrack() {
	////
	//! bugide faili nimi
	global $basedir;
	$this->filename=$basedir."/bugs/";
	////
	//! saitide array
	$sites=array(
		"0" => "test.kirjastus.ee",
		"1" => "test.kroonika.ee",
		"2" => "www.kroonika.ee",
		"3" => "uus.nadal.ee",
		"4" => "www.nadal.ee",
		"5" => "www.seltskond.ee",
		"6" => "vibe.struktuur.ee",
		"7" => "uusvibe.struktuur.ee",
		"8" => "www.kirjastus.ee",
		"9" => "dev.struktuur.ee",
		"10" => "stat.struktuur.ee",
		"11" => "rkool.struktuur.ee",
		"12" => "ebs.struktuur.ee",
		"13" => "uus.anne.ee",
		"14" => "work.struktuur.ee",
		"15" => "www.struktuur.ee",
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
			);
	////
	// !Kõikvõimalikud staatused
	$this->reslist = array(
			"0" => "parandatud",
		  "1" => "vale bug",
			"2" => "ei paranda",
			"3" => "hiljem",
			"4" => "tuleta meelde",
			"5" => "koopia",
			"6" => "multöötab",
			);
	////
	// !Kõikvõimalikud prioroteedid
	$this->prilist = array("1" => "1 - madalaim",
		 "2" => 2,
		 "3" => 3,
		 "4" => 4,
		 "5" => 5,
		 "6" => 6,
		 "7" => 7,
		 "8" => 8,
		 "9" => "9 - korgeim");
	////
	// !Kõikvõimalikud severity astmed
	$this->sevlist = array("1" => "Blokeerib töö",
		 "2" => "Kriitiline",
		 "3" => "Suur puudus",
		 "4" => "Väike puudus",
		 "5" => "Elementaarne",
		 "6" => "Ettepanek",
			);

		// prioriteetide värvid
		$this->pri = array(
					"1" => "#dadada",
					"2" => "#dad0d0",
					"3" => "#dacaca",
					"4" => "#dac0c0",
					"5" => "#dababa",
					"6" => "#dab0b0",
					"7" => "#daaaaa",
					"8" => "#da9090",
					"9" => "#da8a8a");
		$this->db_init();
		$this->tpl_init("automatweb/bugtrack");
/*		if (!$filt) {
			session_register("filt");
			$filt="all";
		}
*/

	}
	////
	//! Näitab bugi lisamise formi
	function add_bug($ar) 
	{
		load_vcl("date_edit");
		$date_edit = new date_edit(time());
		$date_edit->configure(array(
			"day" => "",
			"month" => "",
			"year" => "",
			"hour" => "",
			"minute" => ""
			));
		$start=time();
		$this->read_template("add.tpl");
		global $uid;
		classload("users");
		$u = new users;
		$ud = $u->fetch($uid);
		$this->vars(array(
			"uid" => UID,
			"url" => "",
			"now" => $this->time2date(),
			"user_mail" => $ud["email"],
			"userlist" => $this->picker("",$this->get_userlist()),
			"sevlist" => $this->picker(0,$this->sevlist),
			"time_fixed" => $date_edit->gen_edit_form("time_fixed",$start),
			"reforb" => $this->mk_reforb("save_new",array())
			));
		return $this->parse();
	}
	////
	// ! Salvestab bugi lisamise formi
	function report_bug($ar) {
		extract($ar);
		$this->quote($text);
		$t = time();
		$uid = UID;
		extract($time_fixed);
		$timeready=mktime($hour,$minute,0,$month,$day,$year);
		$q = "INSERT INTO bugtrack (pri,url,tm,text,title,uid,sendmail2,sendmail2_mail,site,developer,timeready,severity) 
				  VALUES('$pri',
					'$url',
					$t,
					'$text',
					'$title',
					'$uid',
					'$sendmail2',
					'".$GLOBALS["user_email"]."',
					'".$GLOBALS["baseurl"]."',
					'$developer',
					'$timeready',
					'$severity')";
		$this->db_query($q);
		if ($maildev) {

			   $msg = <<<EOT
$uid lisas vea/idee lehele $url, prioriteediga $pri
$text
EOT;
				global $baseurl;
			   mail("bugtrack@struktuur.ee","Uus puuk: $baseurl $title",$msg,"From: bugtrack <bugtrack@struktuur.ee>");
		}
		$this->_log("bug","Lisas bugi $title");

		//Siin salvestatakse uus bugi ka faili
		$q = "SELECT MAX(id) AS id FROM bugtrack WHERE uid = '$uid'";
		$id = $this->db_fetch_field($q,"id");
		$this->save_bug_tofile($id);
		global $fil;
		$refr = $this->mk_orb("lists",array("filt"=>"all"));
		if ($fil) $refr = $this->mk_orb("lists",array("filt"=>"$fil"));
		http_refresh(0,"$refr");
	}

	////
	// !Listib kõik bugid. 
	function bug_list($args)
	{
		extract($args);
		global $fil;
		$fil = $filt;
		if (!session_is_registered("fil"))
		{
			session_register("fil");
		}
		
		load_vcl("table");
		global $baseurl;
		global $class;
		global $action;
		global $uid;
		global $PHP_SELF;
		global $HTTP_HOST;

		$t = new aw_table(array(
			"prefix" => "bugtrack",
			"self" => $PHP_SELF,
			"imgurl" => $baseurl . "/automatweb/images",
		));
		$t->set_header_attribs(array(
			"class" => $class,
			"action" => $action,
			"filt" => $filt,
		));
		
		//defineerime heederi array vastavalt saidile. Et kas on work v6i siis klient.
	  $headerarray=array(
					"orb.aw?class=bugtrack&action=lists&filt=my" => "Minule määratud bugid",
					"orb.aw?class=bugtrack&action=lists&filt=all" => "Kõik bugid",			
		);

		if ($this->prog_acl("add", PRG_BUGTRACK))
		{
			$headerarray["orb.aw?class=bugtrack&action=new"] = "Lisa uus";
		}
		if ($HTTP_HOST!="work.struktuur.ee")
		{
			$headerarray["orb.aw?class=bugtrack&action=upload"]="Uploadi work'i";
		} 
		else 
		{
			$headerarray["orb.aw?class=bugtrack&action=get"] = "Loe bugid failidest work'i";
		}

		$t->define_header("BugTrack",$headerarray);
		$t->parse_xml_def($this->basedir . "/xml/bugtrack/bugtrack.xml");
		
		if ($filt=="my") {
			$temp=array("developer" => $uid);
		} else {
			$temp=array();
		}
		$this->_list_buggs($temp);

		while($row = $this->db_next())
		{
			for ($i=0;$i<10;$i++)
			{
				if ($row[status]==$i) $row[status]=$this->statlist[$i];
				$row[filt]=$filt;
			}
			$t->define_data($row);
		};
		global $bgtr_sort;
		if (!($bgtr_sort) && ($args["sortby"] == "")) {
			$args["sortby"]="pri";
		} 
		$bgtr_sort = $args["sortby"];
		session_register("bgtr_sort");
		$t->sort_by(array("field" => $args["sortby"]));
		return $t->draw();
	}
	////
	// !Teeb query yle bugide
	function _list_buggs($args)
			{	
				extract($args);
				$where = "";
				if($developer)
				{
					$where = "WHERE bugtrack.developer = '$developer'";
				}
				$q = "SELECT * FROM bugtrack $where	ORDER BY pri DESC";
				$this->db_query($q);
			}
	
	////
	// !Näitab bugi edimise formi.
	function edit_bug($ar) {
		extract($ar);

		$bdata = $this->get_bug($id);
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
			$start=$bdata[timeready];
			$this->vars(array("uid" => $bdata[uid],
				"url" => $bdata[url],
				"id"  => $bdata[id],
				"prilist" => $this->picker($bdata[pri],$this->prilist),
				"statlist" => $this->picker($bdata[status],$this->statlist),
				"sevlist" => $this->picker($bdata[severity],$this->sevlist),
				"userlist" => $this->picker($bdata[developer],$this->get_userlist()),
				"reslist" => $this->picker($bdata[resol],$this->reslist),
				"now" => $this->time2date($bdata[tm]),
				"title" => $bdata[title],
				"text" => format_text($bdata[text]),
				"user_mail" => $bdata[sendmail2_mail],
				"add_mail" => $bdata[mails],
				"text_result" => format_text($bdata[text_result]),
				"checked"	=> ($bdata[sendmail2] == 1 ? "CHECKED" : ""),
				"time_fixed" => $date_edit->gen_edit_form("time_fixed",$start),
				"reforb" => $this->mk_reforb("save",array("id" => $bdata[id], "ref" => "asdasdf"))
				));
			return $this->parse();
	}
	////
	// !Salvestab bugi edimise formi.
	function save_bug($ar) {
		extract ($ar);
		global $uid;

		global $fil;


		extract($time_fixed);
		$timeready=mktime($hour,$minute,0,$month,$day,$year);
		$ar["timeready"]=$timeready;
		$this->_save_bug($ar);
		$bug = $this->get_bug($id);
		// arrr. Siin voiks ju ka templatet kasutada. (duke)
		if ($status == "4")
		{
			$bleh=$bug[resol];
			$res = $this->reslist[$bleh];
			$msg = "$uid m2rkis vea/idee $url, prioriteediga $pri lahendatuks. Järeldus: ".$res."\n\n---------------------------------------------\n\nTegevuseks märgiti: \n\n". $bug[text_result]."\n---------------------------------------------\n\nVea kirjeldus oli:\n\n".$bug[text];
			mail("dev@struktuur.ee","Parandatud puuk: ".$bug[site]." ".$bug[title],$msg,"From: bugtrack <dev@struktuur.ee>");
			if ($bug[sendmail2])
			mail($bug[sendmail2_mail],"Parandatud puuk: ".$bug[site]." ".$bug[title],$msg,"From: bugtrack <dev@struktuur.ee>");
			if ($bug[mails]!="")
			{
				$mails=explode(",",$bug[mails]);
				for($i=0;$i<=count($mails);$i++)
				{
					mail($mails[$i],"Teade parandatud bugi kohta: ".$bug[site]." ".$bug[title],$msg,"From: bugtrack <dev@struktuur.ee>");
				}		
			}
		}
		global $HTTP_HOST;
		if ($HTTP_HOST=="work.struktuur.ee")
		{
			//Tundub et see v2rk ei tööta ikkagi.
			//echo $bug[site]."/bugger.aw?s=$status&i=$id";
			if ($bug[site]!="") {
					$bleh=$bug[site_id];
					readfile("$bug[site]/bugger.aw?s=$status&i=$bleh","r");
			}

		} else {
			$this->save_bug_tofile($id);
		}
		
		$filter = $filt;
		return $this->mk_orb("list",array("filt" => "$fil"));
	}

	////
	// !Muudab bugi staatust. Kasutab seda bugger.aw mis peaks igas saidis olema.
	function update_status($id=0,$status=0) {
		$q="Update bugtrack SET status=$status WHERE id=$id";
		$this->db_query($q);
	}

	////
	// !Salvestab bugi edimise või lisamise formi.
	function _save_bug($ar) {
		extract($ar);
		$q = "UPDATE bugtrack
			SET pri = '$pri',
			    status = '$status',
					sendmail2 = '$sendmail2',
					severity = '$severity',
					developer = '$developer',
					timeready = '$timeready',
					resol = '$resolution',
					text_result = '$text_result',
					mails = '$add_mail'
			WHERE id = '$id'";
		$this->db_query($q);	
		global $HTTP_HOST;
		$this->_log("bug","Muutis bugi $title");
	}
	////
	// !Annab ühe bugi andmed
	function get_bug($id) {
		return $this->get_record("bugtrack","id",$id);
	}
	////
	// !Kustutab bugi
	function remove_bug($ar) {
		extract($ar);
		$buk = $this->get_bug($id);
		$this->_log("bug","Kustutas bugi ".$buk[title]);
		$this->dele_record("bugtrack","id",$id);
		$refr= $this->mk_orb("lists",array("filt"=>"all"));
		http_refresh(0,"$refr");
	}
	////
	// !Listib bugid vastavalt millelegi hmpf.. huvitav kas seda yldse keegi kasutab
	function _list_bugs($field="",$condition="") {
		if ($field == "")
			$q = "SELECT * FROM bugtrack ORDER BY status ASC,pri ASC, tm";
		else
			$q = "SELECT * FROM bugtrack WHERE $field = '$condition'	ORDER BY status ASC,pri DESC, tm";
		$this->db_query($q);
	}
	////
	// !Teeb useritest array.
	function get_userlist()
	{
		$this->db_query("SELECT uid FROM users where blocked != 1");
		while ($row=$this->db_next())
		{
			$uid=$row[uid];
			$users[$uid]=$uid;
		}
		return $users;
	}
	////
	//! Et saaks urli pidi ka yhekaupa salvestada. A seda pole eriti vaja vist. 4 debbugging only.
	function _save_bug_tofile($ar)
	{
		extract($ar);
		$this->save_bug_tofile($id);
	}

	////
	// !Salvestab bugi faili. 
	function save_bug_tofile($id) {
		//Faili nimi kuhu salvestatakse bugisid
		//Koosneb bugiid_siteid .. work in progress...
		$bug = $this->get_bug($id);
		$fsite=md5($bug[site]);
		$filename=$this->filename.$id."__".$fsite;
		if (!file_exists($filename)) 
		{
			$fp=fopen($filename,"w+");
		} else $fp = fopen($filename,"r+");
		ftruncate ($fp,0);
		rewind($fp);
		$buffer = serialize($bug);
		fwrite($fp,$buffer);
		fclose($fp);
	}

	////
	// !Salvestab bugid faili. 
	function save_all_bugs() {
		$this->db_query("SELECT id FROM bugtrack");
		//Faili nimi kuhu salvestatakse bugisid
		while($row = $this->db_next())
		{
				$this->save_handle();
				$this->save_bug_tofile($row["id"]);
				$this->restore_handle();
		};
	}

	
	////
	//! work.struktuur.ee jaoks. et saada buggeri kataloogist failid ja nende sisu baasi.
	function get_files() 
	{
		global $HTTP_HOST;
		if ($HTTP_HOST=="work.struktuur.ee")
		{
				$bugdir="/home/bugger/";
				$files=$this->getfilelist($bugdir);	
				for ($i=0;$i<count($files);$i++) 
				{
						$filename=$bugdir.$files[$i];
						$fp = fopen ($filename,"r");
						$buffer="";
						while (!feof($fp))
						{
							$buffer .= fgetc($fp);
						}
						fclose($fp);
						$br = unserialize($buffer);
						if (is_array($br))
						{
								//$br on nyyd array kus on bug sees. :)
								//what else do we have:
								//$br[site] -- site kust asi pärit on
								//$br[id] -- id selles pärit olevas saidis, saidis
								//nyyd vaja tsekkida kas sellise id'ga buuk on baasis olemas juba vastavale saidile
								
									$q = "SELECT id FROM bugtrack WHERE site='$br[site]' AND site_id='$br[id]'";
									$this->db_query($q);
									$row=$this->db_next();
									$this->quote(&$br);
									if ($row["id"]!="")
									{
										$q = "UPDATE bugtrack SET pri='$br[pri]', url='$br[url]',	tm='$br[tm]', text='$br[text]', title='$br[title]', uid='$br[uid]',	sendmail2='$br[sendmail2]', sendmail2_mail='$br[sendmail2_mail]', site='$br[site]', developer='$br[developer]', timeready='$br[timeready]', severity='$br[severity]', status='$br[status]', resol='$br[resol]', mails='$br[mails]' WHERE id='$row[id]'";
										$this->db_query($q);
									} else {
										$q = "INSERT INTO bugtrack (pri,url,tm,text,title,uid,sendmail2,sendmail2_mail,site,developer,timeready,severity,status,resol,mails,site_id) 
													VALUES('$br[pri]',
													'$br[url]',
													'$br[tm]',
													'$br[text]',
													'$br[title]',
													'$br[uid]',
													'$br[sendmail2]',
													'$br[sendmail2_mail]',
													'$br[site]',
													'$br[developer]',
													'$br[timeready]',
													'$br[severity]',
													'$br[status]',
													'$br[resol]',
													'$br[mails]',
													'$br[id]'
													)";
										$this->db_query($q);
									}
									system("rm -rf $filename");
							}
					}
			}
	}


	//Pask. Miks kurat ei saa full pathi öelda dest failile. 
	// sest ftp protokoll ei näe seda ette
	////
	// !Saadab värgi ära work.struktuur.ee'sse.
	// besides, ftp-ga failide saatmine voiks ka kuidagi kapseldatud olla
	function ftp_send()
	{
		global $HTTP_HOST;
		if (!strpos($HTTP_HOST,"work.struktuur")) 
		{
				$files=$this->getfilelist($this->filename);
				$ftp_server="www.struktuur.ee";	
				$ftp_user="bugger";
				$ftp_pass="bug34zilla";
				$conn_id = ftp_connect("$ftp_server"); 
				$login_result = ftp_login($conn_id, "$ftp_user", "$ftp_pass"); 
				if ((!$conn_id) || (!$login_result)) 
				{ 
						die; 
				}
				$this->read_template("send.tpl");
				$ln = "";
				//Send files
				for($i=0;$i<count($files);$i++) 
				{
					$ff=$this->filename.$files[$i];
					$destination_file=$files[$i];
					//print("<B>Sending </B>".$ff." to ".$destination_file."<BR>");
					$this->vars(array(
						"file" => $ff,
						"dest" => $destination_file
					));
					$upload = ftp_put($conn_id, $destination_file, $ff, FTP_BINARY); 
					if (!$upload===true) {
						//$this->raise_error("Saatmine eba6nnestus",true);
						$this->vars(array("Fok" => "<FONT COLOR=red>Error</FONT>"));
					} else {
						system("rm -rf $ff");
						$this->vars(array("Fok" => "OK"));
					}
					$ln.=$this->parse("LINE");
				}
				// close the FTP stream 
				ftp_quit($conn_id); 
				$this->vars(array("LINE" => $ln));
				return $this->parse();
				//header("Location: ".$this->mk_orb("list",array("filt" => "all")));
		} 
	}

	////
	//! V6tab etteantud kataloogist failide nimed arraysse.
	function getfilelist($dir=".") 
	{
		// gah. shellikäskude väljakutsumine on paha. Imho.
		$fp = popen( "/bin/ls $dir", "r" );
		while($line = fgets($fp,300))
		{
			$line=substr($line,0,strlen($line)-1);
			if (!is_dir("$dir".$line)) 
			{					
				//printf("%s",$line);
				$files[]="$line";
			}
		}
		pclose($fp);
		return $files;
	}	
};
?>
