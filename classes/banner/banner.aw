<?php
// $Header: /home/cvs/automatweb_dev/classes/banner/Attic/banner.aw,v 1.1 2002/11/04 00:47:50 kristo Exp $

// act_type's:
// 0 - always active
// 1 - from date to date
// 2 - on weekday from time to time
// 3 - every day from time to time


// use the banner software like this:
// banner.aw?html=1&gid=[gid]
//  -- outputs the html for a client, with [ss] in place of session id
//	-- optionally also aw_uid=[uid] - to connect the logged in user with the banner buid 
//
// banner.aw?click=1&bid=[bid]
//  -- redirects to url of banner [bid]
//
// banner.aw?click=1&gid=[gid]&ss=[ss]
//  -- finds the banner by session id [ss] and redirects to it's url
//
// banner.aw?bid=[bid]
//  -- displays the banner [bid]
//
// banner.aw?gid=[gid]&ss=[sd]&noview=[0|1]
//  -- selects a random banner for the location [gid] , uses session id [ss] to remember the banner shown and if noview == 1, does not record a banner_view
//
// banner.aw?gid=[gid]&ss=[ss]&htmlb=1
//  -- used when showing banners with IFRAME's. selects a random banner from group [gid] and it can be any kind of banner at all. flash or html or whateva.

class banner extends aw_template
{
	function banner()
	{
		$this->init("banner");
		$this->sub_merge = 1;
		lc_load("definition");
		$this->lc_load("banner","lc_banner");
	}

	////
	// !common part of add function between admin and user side
	function add_core($parent)
	{
		$ca = $this->get_clientarr();
		$ob = get_instance("objects");

		$pr_arr = $this->get_profile_arr();
		$sel_pr_arr = array();

		$buy_arr = $this->get_buyer_arr();

		$this->vars(array(
			"grp" => $this->picker($client,$ca),
			"buyer" => $this->multiple_option_list(array(),$buy_arr),
			"parent"	=> $this->picker($parent, $ob->get_list()),
			"profiles" => $this->multiple_option_list($sel_pr_arr, $pr_arr)
		));
	}

	////
	// !generates form for adding a banner on admin side
	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path($parent, LC_ADD_BANNER);
		$this->add_core($parent);
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array())
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		global $fail, $fail_type;

		if ($id)
		{
			// save
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment, "status" => ($act == 1 ? 2 : 1),"parent" => $parent));

			if ($fail != "none" && $fail != "")
			{
				$f = fopen($fail,"r");
				$fc = fread($f, filesize($fail));
				fclose($f);
				$this->quote(&$fc);
				$this->quote(&$fc);
				$this->db_query("UPDATE banners SET fail = '$fc' , fail_type = '$fail_type' WHERE id = $id");
			}

			$this->db_query("UPDATE banners SET url = '$url' , active='$act', probability='$probability',max_views='$max_views', max_clicks='$max_clicks', max_views_user = '$max_views_user', max_clicks_user='$max_clicks_user', b_url = '$b_url',html = '$html' WHERE id = $id");
		}
		else
		{
			// add
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_BANNER, "name" => $name, "status" => ($act == 1 ? 2 : 1), "comment" => $comment));

			if ($fail != "none" && $fail != "")
			{
				$f = fopen($fail,"r");
				$fc = fread($f, filesize($fail));
				fclose($f);
				$this->quote(&$fc);
				$this->quote(&$fc);
			}

			$this->db_query("INSERT INTO banners(id, fail, fail_type, url,active,probability,max_views,max_clicks,max_clicks_user,max_views_user,b_url,html) VALUES($id,'$fc','$fail_type','$url','$act','$probability','$max_views','$max_clicks','$max_clicks_user','$max_views_user','$b_url','$html')");
		}

		$this->db_query("DELETE FROM banner2client WHERE bid = $id");
		if (is_array($grp))
		{
			reset($grp);
			while (list(,$cl) = each($grp))
			{
				$this->db_query("INSERT INTO banner2client(bid,clid) values($id,$cl)");
			}
		}

		$this->db_query("DELETE FROM banner2profile WHERE bid = $id");
		if (is_array($profiles))
		{
			reset($profiles);
			while (list(,$v) = each($profiles))
			{
				if ($v)
				{
					$this->db_query("INSERT INTO banner2profile (bid, profile_id) VALUES($id,$v)");
				}
			}
		}

		$this->db_query("DELETE FROM banner2buyer WHERE bid = $id");
		if (is_array($buyer))
		{
			reset($buyer);
			while (list(,$v) = each($buyer))
			{
				if ($v)
				{
					$this->db_query("INSERT INTO banner2buyer (bid, buyer_id) VALUES($id,$v)");
				}
			}
		}

		if ($site)
		{
			return $this->mk_orb("change_site", array("id" => $id));
		}
		else
		{
			return $this->mk_orb("change", array("id" => $id));
		}
	}

	function change_core($ba)
	{
		$id = $ba["id"];
		$views = $this->db_fetch_field("SELECT COUNT(*) AS cnt FROM banner_views WHERE bid = $id","cnt");
		$clics = $this->db_fetch_field("SELECT COUNT(*) AS cnt FROM banner_clicks WHERE bid = $id","cnt");
		if ($views > 0)
		{
			$ctr = ((double)$clics / (double)$views)*100.0;
		}
		
		$ca = $this->get_clientarr();

		$this->db_query("SELECT * FROM banner2client WHERE bid = $id");
		while ($b = $this->db_next())
		{
			$bs[$b["clid"]] = $b["clid"];
		}

		$ob = get_instance("objects");

		load_vcl("date_edit");
		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
			"hour" => "",
			"minute" => ""
		));

		$pr_arr = $this->get_profile_arr();
		$pr_arr[0] = "";
		$sel_pr_arr = $this->get_profiles_for_banner($id);

		$this->vars(array(
			"buyer"		=> $this->multiple_option_list($this->get_buyer_arr_for_banner($id),$this->get_buyer_arr()),
			"name"		=> $ba["name"], 
			"comment" => $ba["comment"], 
			"act"			=> checked($ba["status"] == 2),
			"grp"			=> $this->multiple_option_list($bs,$ca),
			"profiles" => $this->multiple_option_list($sel_pr_arr, $pr_arr),
			"url"			=> $ba["url"],
			"image"		=> $this->get_noview_url($id),
			"views"		=> $views,
			"clics"		=> $clics,
			"max_views"		=> $ba["max_views"],
			"max_clicks"	=> $ba["max_clicks"],
			"max_views_user"		=> $ba["max_views_user"],
			"max_clicks_user"	=> $ba["max_clicks_user"],
			"ctr"			=> $ctr,
			"stats"		=> $this->mk_orb("showstats", array("id" => $id)),
			"parent"	=> $this->picker($ba["parent"], $ob->get_list()),
			"probability" => $ba["probability"],
			"periods" => $this->mk_orb("sel_banner_periods", array("id" => $id)),
			"b_url" => $ba["b_url"],
			"html" => htmlentities($ba["html"])
		));
		$this->parse("CHANGE");
	}

	function change($arr)
	{
		extract($arr);
		$ba = $this->get($id);
		$this->mk_path($ba["parent"], "Muuda bannerit");
		$this->read_template("add.tpl");

		$this->change_core($ba);
		$this->vars(array(
			"reforb"	=> $this->mk_reforb("submit", array("id" => $id))
		));

		return $this->parse();
	}

	function get_noview_url($id,$height = 0)
	{
		if ($height)
		{
			$h = "height=\"$height\"";
		}
		return "<img src='".$this->cfg["baseurl"]."/banner.".$this->cfg["ext"]."?bid=$id&noview=1&ss=".$this->gen_uniq_id()."' $h>";
	}

	function get($id)
	{
		$this->db_query("SELECT objects.*,banners.* FROM banners LEFT JOIN objects ON objects.oid = banners.id WHERE banners.id = $id");
		return $this->db_next();
	}

	////
	// !randomly selects a banner from the specified group
	// easier said than done though.
	function get_grp($gid,$buid)
	{
		$baids = array();
		$cnt = 0;
		$t = time();
		// selektime k6ik bannerid selle kliendi kohta. 
		$bbs = array();
		$this->db_query("SELECT bid FROM banner2client WHERE clid = '$gid'");
		while ($row = $this->db_next())
		{
			$bbs[] = $row["bid"];
		}
		$bbs = join(",",$bbs);
		if ($bbs == "")
		{
			$this->error_banner();
		}

		// teeme nimekirja profiilidest, mille peale kasutaja on klikkinud.
		$profiles = array();
		$this->db_query("SELECT * FROM banner_user2profiles WHERE buid = '$buid'");
		while ($row = $this->db_next())
		{
			$profiles[] = $row["profile_id"];
		}
		$pr_str = join(",",$profiles);
		if ($pr_str != "")
		{
			// we end up here if the user has some profiles attached to him. we must select all banners that suit him here, cause
			// we must detect if no banners suit him and then ignore profiles. bijaatch. 
			// of course we must only select banners that match for this client.

			// ok, so we select all banners for this client that have profiles that this user has. 
			$this->db_query("
				SELECT banners.id as id FROM banners 
				LEFT JOIN banner2profile ON banner2profile.bid = banners.id
				WHERE
				 banner2profile.profile_id IN($pr_str)
				AND banners.id IN ($bbs)
			");

			$has_prof = false;
			// now we check if any banners match and record the matches. 
			// if some match, then use only the matches
			// if not, fall back to using all banners
			$profs = array();
			while ($row = $this->db_next())
			{
				$has_prof = true;
				$profs[] = $row["id"];
			}

			if ($has_prof)
			{
				$bbs = join(",",$profs);
			}
		}

		// selektime k6ik bannerid, mis vastavad muudele tingimustele ja millel on selle kasutaja viewsid v2hem, kui m22ratud. 
		// kuid kui vastuseks ei tule yhtegi bannerit, siis ignome seda tingimust. bijaatch. this is hell on earth. 

		// phuq. nii ei saa seda teha, k6ikide matchivate banneirte counte on vaja selle aksutaja kohta ju! GAAH.
		
/*		$user_clicks = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM banner_clicks WHERE buid = '$buid'","cnt");
		$user_views = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM banner_views WHERE buid = '$buid'","cnt");

		// something like:
		SELECT COUNT(*) AS cnt FROM banner_views WHERE bid IN (SELECT bid FROM banner2client WHERE clid = '$clid') AND buid = '$buid' GROUP BY bid
		// would be needed. but. damnit this is gonna be SOOOOOOOOOOOOO slow. 

		$this->db_query("
			SELECT banners.id as id FROM banners
			WHERE 
				(banners.max_clicks_user > $user_clicks OR (banners.max_clicks_user is null OR banners.max_clicks_user = 0))
				AND (banners.max_views_user > $user_views OR (banners.max_views_user is null OR banners.max_views_user = 0))
				AND banners.id IN ($bbs)
		");
		$vbs = array();
		while ($row = $this->db_next())
		{
			$vbs[] = $row["id"];
		}

		$vbs_str = join(",",$vbs);
		if ($vbs_str != "")
		{
			$bbs = $vbs_str;
		}*/

		$this->db_query("
		SELECT 
			banners.act_type as act_type, 
			banners.act_from as act_from,
			banners.act_to as act_to,
			banners.wday as wday,
			banners.wday_from as wday_from,
			banners.wday_to as wday_to,
			banners.time_from as time_from,
			banners.time_to as time_to,
			banners.id as id,
			banners.probability as pb 
		FROM banners 
		LEFT JOIN objects ON objects.oid = banners.id
		WHERE 
		banners.id IN ($bbs) 
		AND objects.status = 2 
		AND (clicks <= max_clicks OR (max_clicks is null OR max_clicks = 0)) 
		AND (views <= max_views OR (max_views is null OR max_views = 0)) 
		");

		// no filter according to visible time and if we have none left, ignore all deactivation rules
		$t = time();
		$dow = date("w");
		$dow = $dow == 0 ? 7 : $dow;
		$tm = date("H")*3600+date("i")*60;
		$hb = false;
		$bans = array();
		$f_bans = array();
		while ($row = $this->db_next())
		{
			$bans[] = $row;
			
			if ($row["act_type"] == 0)	// always on
			{
				$hb = true;
				$f_bans[] = $row;
			}
			else
			if ($row["act_type"] == 1)	// date to date
			{
				if ($t > $row["act_from"] && $t < $row["act_to"])
				{
					$f_bans[] = $row;
					$hb = true;
				}
			}
			else
			if ($row["act_type"] == 2)	// weekday time to time
			{
				$wd = unserialize($row["wday"]);
				if ($wd[$dow] == $dow && $tm > $row["wday_from"] && $tm < $row["wday_to"])
				{
					$f_bans[] = $row;
					$hb = true;
				}
			}
			else
			if ($row["act_type"] == 3)	// time to time
			{
				if ($tm > $row["time_from"] && $tm < $row["time_to"])
				{
					$f_bans[] = $row;
					$hb = true;
				}
			}
		}

		// if some banners matched the criteria, pick among those only.
		if ($hb)
		{
			$bans = $f_bans;
		}

		$found = false;
		reset($bans);
		while (list(,$row) = each($bans))
		{
			$baids[$cnt++] = $row;
			if ($row["pb"] > 0)
			{
				$found = true;
			}
		}
		if ($cnt > 0)
		{
			srand ((double) microtime() * 10000000);
			if ($found == false)
			{
				$bid = $baids[array_rand($baids)]["id"];
				if (!$bid)
				{
					$this->error_banner();
				}
			}
			else
			{
				// select the banner by it's probability. 
				// so. we pick a number between one and 100
				// and if that number is <= the banners probability, we pick that banner
				$num = 0;
				$found = false;
				while (!$found)
				{
					$r = rand(0,101);
					if ($r <= $baids[$num]["pb"] )
					{
						$bid = $baids[$num]["id"];
						$found = true;
					}
					$num = ($num+1)%$cnt;
				}
			}
			return $this->get($bid);
		}
		else
		{
			if (!$bid)
			{
				$this->error_banner();
			}
		}
	}

	////
	// !adds a record to the banner_ids table to identify shown banners l8r
	function add_view($bid,$ss,$noview,$clid,$buid)
	{
		$this->db_query("INSERT INTO banner_ids(ss,bid,tm,clid) values('$ss','$bid',".time().",'$clid')");
		if (!$noview)
		{
			$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
			if ($ip == "" || !(strpos($ip,"unknown") === false))
			{
				$ip = aw_global_get("REMOTE_ADDR");
			}
			// ok. a prize for anybody (except terryf and duke) who figures out why the next line is like it is :)
			$day = mktime(8,42,17,date("n"),date("d"),date("Y"));
			$this->db_query("INSERT INTO banner_views(tm,bid,ip,clid,buid,day,dow,hr) VALUES(".time().",$bid,'$ip','$clid','$buid','$day','".date("w")."','".date("G")."')");
			$this->db_query("UPDATE banners SET views=views+1 WHERE id = $bid");
		}
	}

	////
	// !called when user clicks on banner, finds the correct banner according to the session id
	// also cleans the banner_ids table of all views older than 48 hours	
	function find_url($ss,$clid,$buid)
	{
		$bid = $this->db_fetch_field("SELECT bid FROM banner_ids WHERE ss = '$ss' AND clid = '$clid'","bid");
		if (!$bid)
		{
			$this->error_click();
		}
		// no need to do this every time. let's say it has a 1% probability of happening.
		srand ((double) microtime() * 10000000);
		$pb = rand(1,101);
		if ($pb == 69)
		{
			$this->db_query("DELETE FROM banner_ids WHERE tm < ".(time()-(48*3600)));
		}

		$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
		if ($ip == "" || !(strpos($ip,"unknown") === false))
		{
			$ip = aw_global_get("REMOTE_ADDR");
		}

		$day = mktime(8,42,17,date("n"),date("d"),date("Y"));
		$this->db_query("INSERT INTO banner_clicks(tm,bid,ip,clid,buid,day,dow,hr,refferer) VALUES(".time().",$bid,'$ip','$clid','$buid','$day','".date("w")."','".date("G")."','".aw_global_get("HTTP_REFERER")."')");
		$this->db_query("UPDATE banners SET clicks=clicks+1 WHERE id = $bid");

		$this->db_query("SELECT * FROM banners WHERE id = $bid");
		return $this->db_next();
	}

	function proc_banner($arr)
	{
		extract($arr);
		if ($html)
		{
			// tagastame baasi kirjutatud htmli banneri naitamisex.
			if (!$gid)
			{
				die(LC_ERROR_NO_ID);
			}
			die($this->db_fetch_field("SELECT html FROM banner_clients WHERE id = $gid","html"));
		}
		else
		if ($click)
		{
			// redirect
			if ($bid)
			{
				$ba = $this->get($bid);
				header("Location: ".$ba["url"]);
				die();
			}

			if ($gid)
			{
				$udata = $this->check_cookie($aw_uid);
				$ret = $this->find_url($ss,$gid,$udata["buid"]);
				$this->click_set_profile($ret["id"],$udata);
				header("Location: ".$ret["url"]);
				die();
			}
		}
		else
		{
			// show
			$ba = array();
			if ($bid)
			{
				$ba = $this->get($bid);
				if (!$ba)
				{
					$this->error_banner();
				}
			}

			if ($gid)
			{
				$udata = $this->check_cookie($aw_uid);

				$ba = $this->get_grp($gid,$udata["buid"]);
				if (!$ba)
				{
					$this->error_banner();
				}
				$this->add_view($ba["id"],$ss,$noview,$gid,$udata["buid"]);
			}

			if ($ba["b_url"] != "")
			{
				header("Location: ".$ba["b_url"]);
			}
			else
			{
				header("Content-type: ".$ba["fail_type"]);
				die($ba["fail"]);
			}
		}
	}

	function check_cookie($aw_uid)
	{
		global $aw_banner_uid,$aw_banner_check;

		$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
		if ($ip == "" || !(strpos($ip,"unknown") === false))
		{
			$ip = aw_global_get("REMOTE_ADDR");
		}

		if (!isset($aw_banner_uid))
		{
			if (!isset($aw_banner_check))
			{
				// no cookies set at all. so the user is either visiting for the first time or
				// has cookies disabled.

				// make check cookie, so we generate user id's only for people who have cookies enabled.
				setcookie("aw_banner_check",1,time()+24*3600*1000,"/");
			}
			else
			{
				// found a check cookie. so the user is here for the first time and has cookies enabled.
				// generate a random uid, but do not write it to the database. 
				// the reason for that is, that if there are several banners on one page and the user
				// views the page for the forst time, he gets several uids generated. 
				// we avoud that by not writing the uid to the database now, but only the next time when the user returns and has a uid
				// and that is not in the database, it will be written there. 

				$buid = $this->gen_uniq_id();
				setcookie("aw_banner_uid",$buid,time()+24*3600*1000,"/");
				setcookie("aw_banner_check");	// erase check cookie.
				$GLOBALS["aw_banner_uid"] = $buid;
				$budata["buid"] = $buid;
			}
		}
		else
		{
			$this->db_query("SELECT * FROM banner_users WHERE buid = '$aw_banner_uid'");
			$budata = $this->db_next();
			if (!$budata)
			{
				$this->db_query("INSERT INTO banner_users (buid,ip,created,aw_uid) VALUES('$aw_banner_uid','$ip','".time()."','$aw_uid')");
				$budata["buid"] = $aw_banner_uid;
			}
			if ($aw_uid != "")
			{
				echo "aw_uid = $aw_uid <br>";
				$this->db_query("UPDATE banner_users SET aw_uid = $aw_uid WHERE buid = '$aw_banner_uid'");
			}
		}

		return $budata;
	}

	////
	// !shows detailed statistics for banner $id
	function showstats($arr)
	{
		extract($arr);
		$ba = $this->get($id);
		$this->mk_path($ba["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>LC_CHANGE_BANNER</a> / Detailne statistika");

		// viimase 7 p2eva stats, nagu phpAdsis
		$this->read_template("stat_detail.tpl");

		$year = date("Y");
		$month = date("n");
		$day = date("d");

		$m_vs = 0;
		$m_cs = 0;

		for ($i=6; $i >= 0; $i--)
		{
			$start = mktime(0,0,0,$month, $day-$i, $year);
			$end = mktime(0,0,0,$month, ($day-$i)+1, $year);
	
			$v = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM banner_views WHERE tm > $start AND tm < $end AND bid = $id", "cnt");
			$c = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM banner_clicks WHERE tm > $start AND tm < $end AND bid = $id","cnt");

			$views[$start] = $v;
			$clics[$start] = $c;

			$t_vs+=$v;
			$t_cs+=$c;

			$m_vs = max($m_vs, $v);
			$m_cs = max($m_cs, $c);
		}

		reset($views);
		reset($clics);
		while (list($day,$v) = each($views))
		{
			list(,$c) = each($clics);

			if ($m_vs > 0)
			{
				$vlen = floor(((double)$v/(double)$m_vs)*200);
			}
			else
			{
				$vlen = 0;
			}
			if ($m_cs > 0)
			{
				$clen = floor(((double)$c/(double)$m_cs)*200);
			}
			else
			{
				$clen = 0;
			}

			$this->vars(array(
				"date" => $this->time2date($day,3), 
				"views" => $v, 
				"clics" => $c,
				"vlen" => $vlen,
				"clen" => $clen,
				"daily"	=> $this->mk_orb("day_stat", array("day" => $day, "banner" => $id))
			));
			$this->parse("CLICKS_LINE");
			$this->parse("VIEWS_LINE");
		}
		$this->vars(array(
			"t_clics" => $t_cs, 
			"t_views" => $t_vs, 
			"a_clics" => floor(($t_cs/7)*100.0)/100.0, 
			"a_views" => floor(($t_vs/7)*100.0)/100.0,
			"image"		=> $this->get_noview_url($id)
		));
		return $this->parse();
	}

	function get_clientarr()
	{
		$ret = array();
		$this->listacl("objects.status != 0 AND objects.class_id =  ".CL_BANNER_CLIENT);
		$this->db_query("SELECT objects.*,banner_clients.* FROM objects LEFT JOIN banner_clients ON banner_clients.id = objects.oid WHERE objects.status != 0 AND objects.class_id =  ".CL_BANNER_CLIENT." ORDER BY oid");
		while ($row = $this->db_next())
		{
			if ($this->can("view", $row["oid"]))
			{
				$ret[$row["id"]] = $row["name"];
			}
		}
		return $ret;
	}

	function activate($arr)
	{
		extract($arr);
		if ($active)
		{
			$this->upd_object(array("oid" => $banner, "status" => 2));
		}
		else
		{
			$this->upd_object(array("oid" => $banner, "status" => 1));
		}
		header("Location: ".$this->mk_orb("list_banners", array("client" => $client)));
	}

	function delete($arr)
	{
		extract($arr);
		$this->delete_object($id);
		header("Location: ".$this->mk_orb("list_banners", array("client" => $client)));
	}

	////
	// !shows a transparent gif, used if any errors occur while showing banner
	function error_banner()
	{
		header("Content-type: image/gif");
		readfile($this->cfg["baseurl"]."/images/transa.gif");
		die();
	}

	////
	// !redirects to the site, used when any errors occur when the user clicks on a banner
	function error_click()
	{
		header("Location: ".$this->cfg["baseurl"]);
		die();
	}

	////
	// !lets the user select the form for creating banner profiles.
	function config($arr)
	{
		$this->read_template("config.tpl");
		
		$c = get_instance("config");
		$sel = $c->get_simple_config("banner_profile_form");

		$this->db_query("SELECT * FROM objects LEFT JOIN forms ON forms.id = objects.oid WHERE status != 0 AND class_id = ".CL_FORM);
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"name" => $row["name"], 
				"id" => $row["oid"],
				"sel" => checked($sel == $row["oid"])
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_config", array()),
			"LINE" => $l,
			"t_views" => $this->db_fetch_field("SELECT COUNT(*) as cnt FROM banner_views","cnt"),
			"t_clicks" => $this->db_fetch_field("SELECT COUNT(*) as cnt FROM banner_clicks","cnt"),
			"t_profiles" => $this->db_fetch_field("SELECT COUNT(*) as cnt FROM objects where class_id = ".CL_BANNER_PROFILE." AND status != 0","cnt"),
			"t_banners" => $this->db_fetch_field("SELECT COUNT(*) as cnt FROM objects where class_id = ".CL_BANNER." AND status != 0","cnt"),
			"t_clients" => $this->db_fetch_field("SELECT COUNT(*) as cnt FROM objects where class_id = ".CL_BANNER_CLIENT." AND status != 0","cnt"),
			"t_busers" => $this->db_fetch_field("SELECT COUNT(*) as cnt FROM banner_users","cnt")
		));
		return $this->parse();
	}

	////
	// !saves the form the user selected from banner config
	function submit_config($arr)
	{
		extract($arr);
		$c = get_instance("config");
		$c->set_simple_config("banner_profile_form",$sel);
		return $this->mk_orb("config", array());
	}

	////
	// !returns an array of all the profiles in the system
	function get_profile_arr()
	{
		$ret = array();
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_BANNER_PROFILE." AND status != 0 ");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}

	////
	// !returns an array of all the profiles for banner $bid
	function get_profiles_for_banner($bid)
	{
		$this->db_query("SELECT banner2profile.profile_id as pr_id FROM banner2profile LEFT JOIN objects ON objects.oid = banner2profile.profile_id WHERE bid = $bid AND objects.status != 0");
		$ret = array();
		while ($row = $this->db_next())
		{
			$ret[$row["pr_id"]] = $row["pr_id"];
		}
		return $ret;
	}

	////
	// !this gets called when the user clicks on a banner. it records that the user has clicked on the profiles for the banner. 
	function click_set_profile($banner_id,$budata)
	{
		$prs = $this->get_profiles_for_banner($banner_id);
		$prs_str = join(",",$prs);
		if ($prs_str != "")
		{
			$profarr = array();
			$this->db_query("SELECT * FROM banner_user2profiles WHERE buid = '".$budata["buid"]."' AND profile_id IN ($prs_str)");
			while ($row = $this->db_next())
			{
				$profarr[$row["profile_id"]] = $row["profile_id"];
			}

			reset($prs);
			while (list(,$v) = each($prs))
			{
				if (!$profarr[$v])
				{
					$this->db_query("INSERT INTO banner_user2profiles (buid,profile_id) VALUES('".$budata["buid"]."',$v)");
				}
			}
		}
	}

	////
	// !shows a list of banners to banner buyer $id, site side
	function buyer_banners($arr)
	{
		extract($arr);

		$this->read_template("site_buyer_banners.tpl");

		$bstr = join(",",$this->get_banners_for_buyer($id));
		if ($bstr != "")
		{
			$this->listacl("objects.class_id = ".CL_BANNER." AND objects.status != 0 AND objects.oid IN ($bstr)");
			$this->db_query("SELECT banners.*,objects.* FROM objects LEFT JOIN banners ON banners.id = objects.oid WHERE objects.class_id = ".CL_BANNER." AND objects.status != 0 AND oid IN ($bstr)");
			while ($row = $this->db_next())
			{
				if ($this->can("view", $row["oid"]))
				{
					$this->vars(array(
						"id" => $row["oid"],
						"name" => $row["name"], 
						"status" => checked($row["status"]==2),
						"o_status" => $row["status"],
						"modifiedby" => $row["modifiedby"],
						"modified" => $this->time2date($row["modified"],2),
						"img" => $this->get_noview_url($row["oid"],30),
						"img_large" => $this->cfg["baseurl"]."/banner.".$this->cfg["ext"]."?bid=".$row["oid"]."&noview=1&ss=".$this->gen_uniq_id(),
						"change" => $this->mk_orb("change_site", array("id" => $row["oid"])),
						"delete" => $this->mk_orb("delete_site", array("id" => $row["oid"],"cl_id" => $id)),
						"stats" => $this->mk_orb("showstats", array("id" => $row["oid"]))
					));
					$cc = "";
					if ($this->can("edit", $row["oid"]))
					{
						$cc = $this->parse("CHANGE");
					}
					$cd = "";
					if ($this->can("delete", $row["oid"]))
					{
						$cd = $this->parse("DELETE");
					}
					$this->vars(array("DELETE" => $cd, "CHANGE" => $cc));
					$this->parse("LINE");
				}
			}
		}
		$this->vars(array(
			"add" => $this->mk_orb("add_site",array()),
			"reforb" => $this->mk_reforb("submit_status_site", array("id" => $id))
		));
		return $this->parse();
	}

	////
	// !saves the status of banners
	function submit_status_site($arr)
	{
		extract($arr);
		$g_id = $id;
		if (is_array($old_act))
		{
			reset($old_act);
			while (list($id, $status) = each($old_act))
			{
				$status--;
				$n_stat = $act[$id];
				if ($n_stat != $status)
				{
					$this->upd_object(array("oid" => $id, "status" => ($n_stat == 1 ? 2 : 1)));
				}
			}
		}
		return $this->mk_orb("buyer_banners", array("id" => $g_id));
	}

	////
	// !generates the html for changing the banner $id on user side
	function change_site($arr)
	{
		extract($arr);
		$ba = $this->get($id);
		$this->read_template("site_add.tpl");

		$this->change_core($ba);
		$this->vars(array(
			"reforb"	=> $this->mk_reforb("submit_site", array("id" => $id,"site" => 1)),
			"path" => "<a href='".$this->mk_orb("sel_buyer_redirect",array("fun" => "buyer_banners", "r_class" => "banner"),"banner_buyer")."'>LC_BANNERS</a> / Muuda bannerit"
		));

		return $this->parse();
	}

	////
	// !generates form for adding a banner on user side
	function add_site($arr)
	{
		extract($arr);
		$this->read_template("site_add.tpl");
		$this->add_core($parent);
		$this->vars(array(
			"path" => "<a href='".$this->mk_orb("buyer_banners", array())."'>LC_BANNERS</a> / Lisa banner",
			"reforb" => $this->mk_reforb("submit_site", array("site" => 1))
		));
		return $this->parse();
	}

	////
	// !deletes banner $id from the user side
	function delete_site($arr)
	{
		extract($arr);
		$this->delete_object($id);
		header("Location: ".$this->mk_orb("buyer_banners", array("id" => $cl_id)));
		die();
	}

	function do_stat_by_hr($clb)
	{
		$clbs = join(",",$clb);
		if ($clbs != "")
		{
			$views = array();
			$this->db_query("SELECT count(*) as cnt,hr FROM banner_views WHERE bid IN ($clbs) GROUP BY hr");
			while ($row = $this->db_next())
			{
				$views[$row["hr"]] = $row["cnt"];
				$t_views += $row["cnt"];
			}

			$clicks = array();
			$this->db_query("SELECT count(*) as cnt,hr FROM banner_clicks WHERE bid IN ($clbs) GROUP BY hr");
			while ($row = $this->db_next())
			{
				$clicks[$row["hr"]] = $row["cnt"];
				$t_clicks += $row["cnt"];
			}

			for ($i=0; $i < 24; $i++)
			{
				$this->vars(array(
					"hr" => $i,
					"views" => $views[$i],
					"clicks" => $clicks[$i]
				));
				$this->parse("LINE");
				$xvs[] = $i;
				$m_cnt = max($views[$i],$m_cnt);
				$gviews[] = max($views[$i],1);
				$gclicks[] = max($clicks[$i],1);
			}

			$this->vars(array(
				"t_views" => $t_views,
				"t_clicks" => $t_clicks,
				"a_views" => (double)$t_views / 24.0,
				"a_clicks" => (double)$t_clicks / 24.0,
				"chart" => $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$xvs)), "yvals" => urlencode(join(",",array(0,$m_cnt))), "data" => urlencode(join(",",$gviews)),"title" => "Tundide statistika", "xtitle" => "Tund", "ytitle" => "Vaatamisi","data2" => urlencode(join(",",$gclicks))),"banner")
			));
		}
	}

	function do_stats_by_dow($clb)
	{
		$clbs = join(",",$clb);

		if ($clbs != "")
		{
			$views = array();
			$this->db_query("SELECT count(*) as cnt,dow FROM banner_views WHERE bid IN ($clbs) GROUP BY dow");
			while ($row = $this->db_next())
			{
				$views[$row["dow"]] = $row["cnt"];
				$t_views += $row["cnt"];
			}

			$clicks = array();
			$this->db_query("SELECT count(*) as cnt,dow FROM banner_clicks WHERE bid IN ($clbs) GROUP BY dow");
			while ($row = $this->db_next())
			{
				$clicks[$row["dow"]] = $row["cnt"];
				$t_clicks += $row["cnt"];
			}

			$days = array(0 => LC_SUNDAY, 1 => LC_MONDAY, 2 => LC_TUESDAY, 3 => LC_WEDNESDAY, 4 => LC_THURSDAY, 5 => LC_FRIDAY, 6 => LC_SATURDAY);
			
			for ($i=0; $i < 7; $i++)
			{
				$this->vars(array(
					"day" => $days[$i],
					"views" => $views[$i],
					"clicks" => $clicks[$i]
				));
				$this->parse("LINE");
				$gviews[] = ($views[$i] == 0 ? 1 : $views[$i]);
				$gclicks[] = ($clicks[$i] == 0 ? 1 : $clicks[$i]);
				$m_cnt = max($m_cnt,$views[$i]);
			}

			$this->vars(array(
				"t_views" => $t_views,
				"t_clicks" => $t_clicks,
				"a_views" => (double)$t_views / 7.0,
				"a_clicks" => (double)$t_clicks / 7.0,
				"chart" => $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",array(LC_SUNDAY1,LC_MONDAY1,LC_TUESDAY1,LC_WENSDAY1,LC_THURSDAY1,LC_FRIDAY1,LC_SATURDAY1))), "yvals" => urlencode(join(",",array(0,$m_cnt))), "data" => urlencode(join(",",$gviews)),"title" => LC_WEEKDAYS_STATISTICS, "xtitle" => LC_DAY, "ytitle" => LC_LOOKS,"data2" => urlencode(join(",",$gclicks))),"banner")
			));
		}
	}

	function do_stats_by_day($clb)
	{
		$clbs = join(",",$clb);

		if ($clbs != "")
		{
			$views = array();
			$this->db_query("SELECT count(*) as cnt,day FROM banner_views WHERE bid IN ($clbs) GROUP BY day");
			while ($row = $this->db_next())
			{
				$views[$row["day"]] = $row["cnt"];
				$t_views += $row["cnt"];
				$v_days++;
			}

			$clicks = array();
			$this->db_query("SELECT count(*) as cnt,day FROM banner_clicks WHERE bid IN ($clbs) GROUP BY day");
			while ($row = $this->db_next())
			{
				$clicks[$row["day"]] = $row["cnt"];
				$t_clicks += $row["cnt"];
				$c_days++;
			}

			$m_cnt = 0;
			reset($views);
			while (list($day,$v_cnt) = each($views))
			{
				$this->vars(array(
					"day" => $this->time2date($day,3),
					"views" => $v_cnt,
					"clicks" => $clicks[$day]
				));
				$this->parse("LINE");
				$days[] = $this->time2date($day,3);
				$gviews[] = $v_cnt;
				$gclicks[] = $clicks[$day];
				$m_cnt = max($m_cnt,$v_cnt);
			}

			$a_vs = ($v_days > 0 ? (double)$t_views / (double)$v_days : 0);
			$a_cs = ($c_days > 0 ? (double)$t_clicks / (double)$c_days : 0);

			$this->vars(array(
				"t_views" => $t_views,
				"t_clicks" => $t_clicks,
				"a_views" => $a_vs,
				"a_clicks" => $a_cs,
				"chart" => $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$days)), "yvals" => urlencode(join(",",array(0,$m_cnt))), "data" => urlencode(join(",",$gviews)),"title" => "Päevade statistika", "xtitle" => "Päev", "ytitle" => "Vaatamisi","data2" => urlencode(join(",",$gclicks))),"banner")
			));
		}
	}

	function do_stat_by_profile($clb)
	{
		$clbs = join(",",$clb);
		// and find out all the profiles for those banners
		if ($clbs != "")
		{
			// get stats for all banners
			$this->db_query("SELECT COUNT(*) AS cnt, bid FROM banner_views WHERE bid IN ($clbs) GROUP BY bid");
			while ($row = $this->db_next())
			{
				$views[$row["bid"]] = $row["cnt"];
				$t_views += $row["cnt"];
			}

			$this->db_query("SELECT COUNT(*) AS cnt, bid FROM banner_clicks WHERE bid IN ($clbs) GROUP BY bid");
			while ($row = $this->db_next())
			{
				$clicks[$row["bid"]] = $row["cnt"];
				$t_clicks += $row["cnt"];
			}

			// and now add all the banner stats together for the profiles
			$pro_views = array();
			$this->db_query("SELECT bid,profile_id,objects.name FROM banner2profile LEFT JOIN objects ON objects.oid = banner2profile.profile_id WHERE bid IN ($clbs) AND objects.status != 0");
			while ($row = $this->db_next())
			{
				$pro_names[$row["profile_id"]] = $row["name"];
				$pro_views[$row["profile_id"]] += $views[$row["bid"]];
				$pro_clicks[$row["profile_id"]] += $clicks[$row["bid"]];
			}

			$xvs = array();
			$gviews = array();
			$gclicks = array();
			reset($pro_views);
			while (list($pro_id,$views) = each($pro_views))
			{
				$clicks = $pro_clicks[$pro_id];
				$this->vars(array(
					"profile" => $pro_names[$pro_id],
					"views" => $views,
					"clicks" => $clicks
				));
				$this->parse("LINE");
				$xvs[] = $prow_names[$pro_id];
				$gviews[] = $views;
				$gclicks[] = $clicks;
				$m_cnt = max($views,$m_cnt);
				$found = true;
			}
			$this->vars(array(
				"t_views" => $t_views,
				"t_clicks" => $t_clicks,
				"chart" => $found ? $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$xvs)), "yvals" => urlencode(join(",",array(0,$m_cnt))), "data" => urlencode(join(",",$gviews)),"title" => "Profiilide statistika", "xtitle" => "Profiil", "ytitle" => "Vaatamisi","data2" => urlencode(join(",",$gclicks))),"banner") : $this->cfg["baseurl"].'/images/transa.gif'
			));
		}
	}

	////
	// !lets the user select periods for the banner
	function sel_banner_periods($arr)
	{
		extract($arr);
		$this->read_template("sel_periods.tpl");
		$ba = $this->get($id);
		$this->mk_path($ba["parent"], "<a href='".$this->mk_orb("change", array("id" => $id))."'>LC_CHANGE_BANNER</a> / Vali perioodid");

		load_vcl("date_edit");
		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
			"hour" => "",
			"minute" => ""
		));
		$de2 = new date_edit(time());
		$de2->configure(array(
			"hour" => "",
			"minute" => ""
		));


		$wdays = array("7" => LC_SUNDAY, "1" => LC_MONDAY, "2" => LC_TUESDAY, "3" => LC_WEDNESDAY, "4" => LC_THURSDAY, "5" => LC_FRIDAY, "6" => LC_SATURDAY);

		$this->vars(array(
			"act_from" => $de->gen_edit_form("act_from", $ba["act_from"]),
			"act_to" => $de->gen_edit_form("act_to", $ba["act_to"]),
			"reforb" => $this->mk_reforb("submit_periods", array("id" => $id)),
			"name" => $ba["name"],
			"image" => $this->get_noview_url($id),
			"wday" => $this->multiple_option_list(unserialize($ba["wday"]), $wdays),
			"wday_from" => $de2->gen_edit_form("wday_from", $ba["wday_from"]-3600*3),
			"wday_to" => $de2->gen_edit_form("wday_to", $ba["wday_to"]-3600*3),
			"time_from" => $de2->gen_edit_form("time_from", $ba["time_from"]-3600*3),
			"time_to" => $de2->gen_edit_form("time_to", $ba["time_to"]-3600*3),
			"type_0" => checked($ba["act_type"] == 0),
			"type_1" => checked($ba["act_type"] == 1),
			"type_2" => checked($ba["act_type"] == 2),
			"type_3" => checked($ba["act_type"] == 3),
			"path" => "<a href='".$this->mk_orb("change_site", array("id" => $id))."'>Muuda bannerit</a> / Muuda perioode"
		));
		return $this->parse();
	}

	////
	// !saves the banners' periods
	function submit_periods($arr)
	{
		extract($arr);
		$af = mktime($act_from["hour"],$act_from["minute"],0,$act_from["month"],$act_from["day"],$act_from["year"]);
		$at = mktime($act_to["hour"],$act_to["minute"],0,$act_to["month"],$act_to["day"],$act_to["year"]);
		$w_t = $wday_to["hour"]*3600+$wday_to["minute"]*60;
		$w_f = $wday_from["hour"]*3600+$wday_from["minute"]*60;
		$t_t = $time_to["hour"]*3600+$time_to["minute"]*60;
		$t_f = $time_from["hour"]*3600+$time_from["minute"]*60;

//		echo "wday_from = ", $wday_from["hour"], ", w_f = $w_f<br>";
		$wd = array();
		if (is_array($wday))
		{
			reset($wday);
			while (list(,$d) = each($wday))
			{
				$wd[$d] = $d;
			}
		}
		$wds = serialize($wd);
		$this->db_query("UPDATE banners SET act_type = '$type', wday = '$wds' , wday_from = '$w_f',wday_to = '$w_t',time_from = '$t_f' , time_to = '$t_t', act_from = '$af',act_to = '$at' WHERE id = $id");

		return $this->mk_orb("sel_banner_periods", array("id" => $id));
	}

	function get_buyer_arr()
	{
		$ret = array();
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_BANNER_BUYER." AND status != 0");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}

	function get_buyer_arr_for_banner($bid)
	{
		$ret = array();
		$this->db_query("SELECT buyer_id FROM banner2buyer WHERE bid = $bid");
		while ($row = $this->db_next())
		{
			$ret[$row["buyer_id"]] = $row["buyer_id"];
		}
		return $ret;
	}

	function get_banners_for_buyer($id)
	{
		$bid=array();
		$this->db_query("SELECT bid FROM banner2buyer LEFT JOIN objects ON objects.oid = banner2buyer.bid WHERE objects.status != 0 AND banner2buyer.buyer_id = $id");
		while ($row = $this->db_next())
		{
			$bid[] = $row["bid"];
		}
		return $bid;
	}

	////
	// !generates a brchart for the daily statistics
	function stat_chart($arr)
	{
		extract($arr);
		$xv = explode(",",$xvals);
		$yv = explode(",",$yvals);
		classload("tt_bar","tt_line","tt_pie");
		if (!$typestr)
		{
			$typestr = "BarGraph";
		}
		$Im = new $typestr(1,40,1);
		$Im->GraphBase(500,350);

		if ($typestr != "PieGraph")
		{
			$Im->parseData($xv,$yv);
		}

		$Im->title($title,"000000");
		if ($typestr != "PieGraph")
		{
			$Im->grid(5);
			$Im->xaxis($xv,$xtitle,"000000");
			$Im->yaxis($yv,$ytitle,"000000","000000");
		}
		$da = explode(",",$data);
		$c = "00ff00";
		if ($data2 != "")
		{
			$da2 = explode(",",$data2);
			$da = array("ydata_0" => $da,"ydata_1" => $da2);

			$c = array("ycol_0" => $c, "ycol_1" => "ff0000");
		}
		if ($data3 != "")
		{
			$da3 = explode(",",$data3);
			$da["ydata_2"] = $da3;

			$c["ycol_2"] = "0000ff";
		}
		if ($data4 != "")
		{
			$da4 = explode(",",$data4);
			$da["ydata_3"] = $da4;

			$c["ycol_3"] = "ff00ff";
		}

		if ($typestr != "PieGraph")
		{
			$Im->makeGraph($da,$c);
		}
		else
		{
			$Im->parsedata(array("labels" => $xvals,"data" => $data));
			$Im->create();
		}

		$image=$Im->getImage();
		header("Content-type: image/png");
		imagepng($image);
		imagedestroy($image);
		die;				
	}

	////
	// !generates a list of users. why would anybody want to see it? damned if I know, but a man's gotta do whut a man's gotta do
	function show_users($arr)
	{
		extract($arr);
	
		$this->read_template("user_list.tpl");
		$this->mk_path("Banneri kasutajad");

		$num = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM banner_users","cnt");
		$per_page = $this->cfg["users_per_page"];
		$np = $num / $per_page;

		for ($i = 0; $i < $np; $i++)
		{
			$this->vars(array(
				"url" => $this->mk_orb("show_users", array("page" => $i)),
				"from" => $i*$per_page, 
				"to" => ($i+1)*$per_page > $num ? $num : ($i+1)*$per_page
			));
			if ($i == $page)
			{
				$p.=$this->parse("SEL_PAGE");
			}
			else
			{
				$p.=$this->parse("PAGE");
			}
		}
		$this->vars(array("PAGE" => $p,"SEL_PAGE" => ""));

		$this->db_query("SELECT * FROM banner_users LIMIT ".($page*$per_page).",".$per_page);
		while ($row = $this->db_next())
		{
			$ip = $row["ip"];
			$this->vars(array(
				"buid" => $row["buid"],
				"aw_uid" => $row["aw_uid"],
				"ip" => $ip,
				"banners" => $this->mk_orb("user_banners", array("buid" => $row["buid"],"page" => $page)),
				"profiles" => $this->mk_orb("user_profiles", array("buid" => $row["buid"],"page" => $page)),
				"history" => $this->mk_orb("user_history", array("buid" => $row["buid"],"page" => $page))
			));
			$this->parse("LINE");
		}
		return $this->parse();
	}

	////
	// !shows a list of banners seen and clicked on by user $buid, on page $page of list
	function user_banners($arr)
	{
		extract($arr);
		$this->read_template("user_banners.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("show_users", array("page" => $page))."'>Nimekiri</a> / Kasutaja bannerid");
		
		$this->db_query("SELECT COUNT(*) AS cnt, banners.*,objects.name as name FROM banner_views LEFT JOIN banners ON banners.id = banner_views.bid  LEFT JOIN objects ON objects.oid = banner_views.bid WHERE banner_views.buid = '$buid' GROUP BY bid");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"views" => $row["cnt"],
				"banner" => $this->get_noview_url($row["id"])
			));
			$bs[] = $row["name"];
			$m_vs = max($m_vs,$row["cnt"]);
			$t_vs += $row["cnt"];
			$dat[] = $row["cnt"];
			$this->parse("LINE");
		}

		$bs2 = array();
		$dat2 = array();
		$this->db_query("SELECT COUNT(*) AS cnt, banners.*,objects.name as name FROM banner_clicks LEFT JOIN banners ON banners.id = banner_clicks.bid  LEFT JOIN objects ON objects.oid = banner_clicks.bid WHERE banner_clicks.buid = '$buid' GROUP BY bid");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"clicks" => $row["cnt"],
				"banner" => $this->get_noview_url($row["id"])
			));
			$bs2[] = $row["name"];
			$m_cs = max($m_cs,$row["cnt"]);
			$t_cs += $row["cnt"];
			$dat2[] = $row["cnt"];
			$this->parse("CLINE");
		}

		$this->vars(array(
			"chart" => $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$bs)),
																									 "yvals" => urlencode(join(",",array(0,$m_vs))),
																									 "data"  => urlencode(join(",",$dat)),
																									 "title" => LC_USER_LOOKS,
																									 "xtitle" => LC_BANNER,
																									 "ytitle" => LC_LOOKS),"banner"),
			"t_views" => $t_vs,
			"cchart" => $this->mk_orb("stat_chart", array("xvals" => urlencode(join(",",$bs2)),
																									 "yvals" => urlencode(join(",",array(0,$m_cs))),
																									 "data"  => urlencode(join(",",$dat2)),
																									 "title" => LC_USERS_BANNER_KLIK,
																									 "xtitle" => LC_BANNER,
																									 "ytitle" => LC_KLIKS),"banner"),
			"t_clicks" => $t_cs
		));
		if ($t_cs > 0)
		{
			$this->parse("C_GRAPH");
		}
		return $this->parse();
	}

	////
	// !generates a list of profiles user $buid has
	function user_profiles($arr)
	{
		extract($arr);
		$this->read_template("user_profiles.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("show_users", array("page" => $page))."'>Nimekiri</a> / Kasutaja profiilid");

		$this->db_query("SELECT profile_id,objects.name AS name FROM banner_user2profiles LEFT JOIN objects ON objects.oid = banner_user2profiles.profile_id WHERE buid = '$buid' AND objects.status != 0");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"profile" => $row["name"]
			));
			$this->parse("LINE");
		}
		return $this->parse();
	}

	////
	// !shows a history of user $buid
	function user_history($arr)
	{
		extract($arr);
		$this->read_template("user_history.tpl");
		$this->mk_path(0,"<a href='".$this->mk_orb("show_users", array("page" => $page))."'>Nimekiri</a> / Kasutaja ajalugu");

		$his = array();
		$this->db_query("SELECT banner_views.*,objects.name as name FROM banner_views LEFT JOIN objects ON objects.oid = banner_views.bid WHERE buid = '$buid'");
		while ($row = $this->db_next())
		{
			$row["view"] = true;
			$his[$row["tm"]] = $row;
		}

		$this->db_query("SELECT banner_clicks.*,objects.name as name FROM banner_clicks LEFT JOIN objects ON objects.oid = banner_clicks.bid WHERE buid = '$buid'");
		while ($row = $this->db_next())
		{
			$row["click"] = true;
			$his[$row["tm"]] = $row;
		}

		ksort($his);

		$ca = $this->get_clientarr();

		reset($his);
		while (list(,$row) = each($his))
		{
			list($ip,) = aw_gethostbyaddr($row["ip"]);
			$this->vars(array(
				"act" => ($row["view"] ? LC_LOOKED_BANNER : LC_KLIKKED_BANNER),
				"time" => $this->time2date($row["tm"],2),
				"banner" => $row["name"],
				"ip" => $ip,
				"loc" => $ca[$row["clid"]]
			));
			$this->parse("LINE");
		}
		return $this->parse();
	}

	////
	// !shows a list of profiles and stats for them
	function show_profiles($arr)
	{
		extract($arr);
		$this->read_template("profile_list.tpl");
		$this->mk_path(0,"Profiilid");

		$this->db_query("SELECT COUNT(*) as cnt,profile_id FROM banner_user2profiles GROUP BY profile_id");
		while ($row = $this->db_next())
		{
			$prn[$row["profile_id"]] = $row["cnt"];
		}

		$this->db_query("SELECT COUNT(*) as cnt,profile_id FROM banner2profile GROUP BY profile_id");
		while ($row = $this->db_next())
		{
			$pan[$row["profile_id"]] = $row["cnt"];
		}

		$this->db_query("SELECT * FROM objects WHERE status != 0 AND class_id = ".CL_BANNER_PROFILE);
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"name" => $row["name"],
				"createdby" => $row["createdby"],
				"created" => $this->time2date($row["created"],2),
				"num_users" => $prn[$row["oid"]],
				"num_banners" => $pan[$row["oid"]],
				"bulist" => $this->mk_orb("show_users", array("profile" => $row["oid"]))
			));
			$this->parse("LINE");
		}
		return $this->parse();
	}
}
?>
