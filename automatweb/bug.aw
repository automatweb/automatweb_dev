<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_tempalte;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_BUGTRACK))
{
	$tt->prog_acl_error("view", PRG_BUGTRACK);
}
$mainmenu[] = "BugTrack";
classload("bugtrack");
$bt = new bugtrack;
$prilist = array("1" => "1 - madalaim",
		 "2" => 2,
		 "3" => 3,
		 "4" => 4,
		 "5" => 5,
		 "6" => 6,
		 "7" => 7,
		 "8" => 8,
		 "9" => "9 - korgeim");
$statlist = array("0" => "avatud",
		  "1" => "lahendatud",
			"2" => "tagasi l&uuml;katud");
switch($op) {
	case "listall":
		$url = "";
	case "list":
		$mainmenu[] = "<a href='$url'>$url</a>";
		$bt->read_template("list.tpl");
		$bt->_list_bugs("url", $url);
		$cnt = 0;
		while($row = $bt->db_next()) {
			$cnt++;
			$sc[$row[status]] ++;
			$style = (($cnt % 2) == 1) ? "fcaption3" : "fcaption2";
			$bt->vars(array("rec" => $row[rec],
					"pri" => $row[pri],
					"id"  => $row[id],
					"style" => $style,
					"url"  => $row[url],
					"status" => ($row[status] == 0) ? "<font color='red'>Avatud</a>" : ($row[status] == 1 ? "<font color='green'>Suletud</font>" : "<font color='blue'>Tagasi l&uuml;katud</font>"),
					"pricolor" => $bt->pri[$row[pri]],
					"when" => $bt->time2date($row[tm]),
					"uid" => $row[uid],
					"title" => $row[title],
					"site"	=> $row[site]));
			$l.=$bt->parse("line");
		};
		$bt->vars(array("line" => $l));
		$title = "Nimekiri";
		$menu[] = "<a href='$PHP_SELF?op=addform&url=$url'>Lisa uus</a>";
		$menu[] = "<a href='$PHP_SELF?op=listall'>K&otilde;ik</a>";
		$bt->vars(array("total" => $cnt, "open" => $sc[0], "closed" => $sc[1], "rejected" => $sc[2]));
		$content = $bt->parse();
		break;
	case "editf":
		$mainmenu[] = "<a href='$PHP_SELF?op=list&url=$url'>$url</a>";
		$mainmenu[] = "Lisa";
		$content=$bt->edit_bugf($id);
		$menu[] = "<a href='$PHP_SELF?op=listall'>K&otilde;ik</a>";
		break;
	case "editform":
		$bt->read_template("edit.tpl");
		$bdata = $bt->get_bug($id);
		$mainmenu[] = "<a href='$PHP_SELF?op=list&url=$bdata[url]'>$bdata[url]</a>";
		$mainmenu[] = "Vaata";
		$bt->vars(array("uid" => $bdata[uid],
				"url" => $bdata[url],
				"id"  => $bdata[id],
				"prilist" => $bt->picker($bdata[pri],$prilist),
				"statlist" => $bt->picker($bdata[status],$statlist),
				"now" => $bt->time2date($bdata[tm]),
				"title" => $bdata[title],
				"text" => format_text($bdata[text]),
				"user_mail" => $bdata[sendmail2_mail],
				"checked"	=> ($bdata[sendmail2] == 1 ? "CHECKED" : "")));
		$content = $bt->parse();
		$menu[] = "<a href='$PHP_SELF?op=listall'>K&otilde;ik</a>";
		break;
	case "addform":
		$bt->read_template("add.tpl");
		$mainmenu[] = "<a href='$PHP_SELF?op=list&url=$url'>$url</a>";
		$mainmenu[] = "Lisa";
		$bt->vars(array("uid" => UID,
				"url" => $url,
				"now" => $bt->time2date(),
				"user_mail" => $user_email));
		$content = $bt->parse();
		$menu[] = "<a href='$PHP_SELF?op=listall'>K&otilde;ik</a>";
		break;
	case "add":
	  if ($maildev) {
		     $udata = $users->fetch($uid);
			   $msg = <<<EOT
$uid lisas vea/idee lehele $url, prioriteediga $pri
$text
EOT;
			   mail("dev@struktuur.ee","Uus puuk: $baseurl $title",$msg,"From: bugtrack <dev@struktuur.ee>");
		};
		$bt->report_bug($pri,$url,$title,$text,$sendmail2);
		http_refresh(0,"$PHP_SELF?op=list&url=$url");
		exit;
	case "save":
		if ($status == 1)
		{
			$bug = $bt->get_bug($id);
			$msg = "$uid m2rkis vea/idee lehel $url, prioriteediga $pri lahendatuks. kirjeldus:\n\n".$bug[text];
			mail("dev@struktuur.ee","Parandatud puuk: ".$bug[site]." ".$bug[title],$msg,"From: bugtrack <dev@struktuur.ee>");
			if ($bug[sendmail2])
			mail($bug[sendmail2_mail],"Parandatud puuk: ".$bug[site]." ".$bug[title],$msg,"From: bugtrack <dev@struktuur.ee>");
		}
		$bt->save_bug($id,$pri,$status,$sendmail2);
//		http_refresh(0,"$PHP_SELF?op=list&url=$url");
//		exit;
	case "delete":
		$bt->remove_bug($id);
		http_refresh(0,"$PHP_SELF?op=list&url=$url");
		exit;
	default:
		$content = "eh?";
};
include("admin_footer.$ext");
?>
