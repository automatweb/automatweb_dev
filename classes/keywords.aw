<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/keywords.aw,v 2.24 2001/05/28 00:39:28 cvs Exp $
// keywords.aw - dokumentide vıtmesınad
global $orb_defs;
$orb_defs["keywords"] = "xml";
classload("defs");

define("ARR_LISTID", 1);
define("ARR_KEYWORD", 2);

class keywords extends aw_template {
	function keywords($args = array())
	{
		$this->db_init();
		$this->tpl_init("automatweb/keywords");
	}

	////
	// !Kuvab dokude nimekirja, mis mingi kindla vıtmesınaga "seotud" on.
	// argumendid
	// oid - objekti id
	function doclist($args = array())
	{
		extract($args);
		$q = "SELECT * FROM keywords WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$keyword = $row["keyword"];
		$this->read_template("doclist.tpl");
		$this->info["site_title"] = "<a href='orb.aw?class=keywords&action=list'>Keywords</a>";
		$q = "SELECT * FROM keywords2objects WHERE keyword_id = '$id'";
		$this->db_query($q);
		$oidlist = array();
		while($row = $this->db_next())
		{
			$oidlist[] = $row["oid"];
		};
		
		$c = "";
		if (is_array($oidlist))
		{
			$objects = join(",",$oidlist);
			if ($objects != "")
			{
				$q = "SELECT * FROM objects WHERE oid IN ($objects)";
				$this->db_query($q);
				while($row = $this->db_next())
				{
					$this->vars(array(
							"id" => $row["oid"],
							"title" => $row["name"],
					));
					$c .= $this->parse("LINE");
				};
			}
		}
		$this->vars(array(
				"keyword" => $keyword,
				"LINE" => $c,
		));
		return $this->parse();
	}

	////
	// !Kuvab kasutajate nimekirja, kes mingi votmesona listis on.
	// argumendid
	// id (int) 
	function listmembers($args = array())
	{
		extract($args);
		$this->read_template("users.tpl");
		$this->info["site_title"] = "<a href='orb.aw?class=keywords&action=list'>Keywords</a>";
		$q = "SELECT users.uid AS uid,
				users.email AS email
				FROM ml_users
				LEFT JOIN users ON (ml_users.uid = users.uid)
				WHERE list_id = '$id'";
		$this->db_query($q);
		$c = "";
		while($row = $this->db_next())
		{
			$this->vars(array(
					"uid" => $row["uid"],
					"email" => $row["email"],
			));
			$c .= $this->parse("LINE");
		};
		$this->vars(array("LINE" => $c));
		return $this->parse();
	}
	////
	// !Teavitab koiki votmesonalistide liikmeid muudatustest
	function notify($args = array())
	{
		extract($args);
		$gp = $this->get_object(KEYWORD_LISTS);
		$doc = $this->get_object($id);
		$q = "SELECT keywords.list_id AS list_id,keywords.keyword AS keyword  FROM keywords2objects
			LEFT JOIN keywords ON (keywords2objects.keyword_id = keywords.id)
			WHERE oid = $id";
		$this->db_query($q);
		$lx = array();
		$kwa = array();
		$kw = "";
		while($row = $this->db_next())
		{
			$lx[] = $row;
			$kwa[] = $row["keyword"];
		};
		$kw = join(",",$kwa);
		classload("email");
		$email = new email();
		$this->info["site_header"] = "<a href='orb.aw?class=document&action=change&id=$id'>Dokument</a>";
		global $baseurl;
		foreach($lx as $row)
		//while($row = $this->db_next())
		{
			$this->save_handle();
			$q = "SELECT * FROM objects WHERE oid = '$row[list_id]'";
			$this->db_query($q);
			$ml = $this->db_next();
			// kui sellele listile pole default maili m‰‰ratud
			if (!$ml["last"])
			{
				// checkime, kas grandparentil on default list m‰‰ratud
				if ($gp["last"])
				{
					// oli. nyyd on meil default listi id k‰es. Tuleb ainult lugeda selle listi last
					#$ml["last"] = $gp["last"];
					$this->save_handle();
					$rl = $this->get_object($gp["last"]);
					$this->restore_handle();
					if ($rl)
					{
						$ml["last"] = $rl["last"];
					};

				}
			};
			$q = "SELECT * FROM ml_mails WHERE id = '$ml[last]'";
			$this->db_query($q);
			$ml = $this->db_next();
			$this->restore_handle();
			if (!$ml)
			{
				print "ERR: listi $row[last] jaoks pole default meili m‰‰ratud<br>";
			}
			else
			{
				$content = $ml["contents"];
				$content = str_replace("#url#","$baseurl/index.aw?section=$id",$content);
				$content = str_replace("#title#",$doc["name"],$content);
				$content = str_replace("#keyword#",$kw,$content);
				$email->mail_members(array(
						"list_id" => $row["list_id"],
						"name" => $ml["mail_from_name"],
						"from" => $ml["mail_from"],
						"subject" => $ml["subj"],
						"content" => $content,
						"cache"   => 1,
				));
			};
		};
	}
	
	////
	// !Handleb saidi sees t‰idetud "interests" vormi datat
	function submit_interests($args = array())
	{
		extract($args);
		classload("list");
		$list = new mlist();
		$list->remove_user_from_lists(array(
					"uid" => UID,
		));
		$list->add_user_to_lists(array(
					"uid" => UID,
					"name" => $name,
					"email" => $email,
					"list_ids" => $lists,
				));
		global $status_msg;
		$status_msg = "Muudatused on salvestatud";
		session_register("status_msg");
		$res = "?type=interests";
		if ($gotourl != "")
		{
			$res = urldecode($gotourl);
		}
		return $res;
	}
	////
	// !Handleb EBS stiilis huvideformist tulnud datat
	function submit_interests2($args = array())
	{
		extract($args);
		global $HTTP_REFERER;
		if (!is_array($check))
		{
			return $HTTP_REFERER;
		};
		$inlist = join(",",map("'%d'",$check));
		// niisiis on mul koigepealt vaja kindlaks teha millistest erinevatest kategooriatest votmesonu
		// vormist tuli
		$q = "SELECT * FROM keywords WHERE id IN ($inlist) GROUP BY category_id";
		$this->db_query($q);
		$catlist = array();
		while($row = $this->db_next())
		{
			$catlist[$row["category_id"]] = $row["category_id"];
		}
		// Nyyd on vaja teada saada koikide kasutatud kategooriate nimed
		$cat_inlist = join(",",map("'%d'",$catlist));
		$q = "SELECT * FROM keywordcategories WHERE id IN($cat_inlist)";
		$this->db_query($q);
		$catnamelist = array();
		while($row = $this->db_next())
		{
			$catnamelist[$row["id"]] = $row["name"];
		};
		// ja lopuks siis keywordide nimekiri
		$q = "SELECT * FROM keywords WHERE id IN ($inlist) ORDER BY category_id,keyword";
		$this->db_query($q);
		$kw = array();
		while($row = $this->db_next())
		{
			$kw[$row["category_id"]][] = $row["keyword"];
		};

		// ja nyyd koostame meili
		$txt = "";
		$txt .= "Nimi: $name\n";
		$txt .= "Aadress: $email\n";
		foreach($kw as $key => $val)
		{
			$txt .= "\n" . $catnamelist[$key] . "\n";
			$txt .= str_repeat("-",strlen($catnamelist[$key])) . "\n";
			foreach($val as $keyword)
			{
				$txt .= " - " . $keyword . "\n";
			};
		};
		$from = sprintf("%s <%s>",$name,$email);
		mail(KW_MAIL,KW_SUBJECT,$txt,"From: $from");
		global $baseurl,$ext;
		$retval = "$baseurl/index.$ext?section=$after";
		return $retval;
	}

	////
	// !Kuvab koikide keywordide vormi
	function list_keywords($args = array())
	{
		$this->read_template("list.tpl");
		// koigepealt uurime v‰lja lugejate arvu listides
		
		// this should probably be in the list class
		$q = "SELECT list_id,COUNT(*) AS cnt FROM ml_users GROUP BY list_id";
		$this->db_query($q);
		$members = array();
		while($row = $this->db_next())
		{
			$members[$row["list_id"]] = $row["cnt"];
		};

		$keyword_counts = array();
		$q = "SELECT keyword_id,COUNT(oid) AS cnt FROM keywords2objects GROUP BY keyword_id";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$keyword_counts[$row["keyword_id"]] = $row["cnt"];
		};

		$q = "SELECT * FROM keywords ORDER BY keyword";
		$this->db_query($q);
		$c = "";
		$this->info["site_title"] = "<a href='orb.aw?class=keywords&action=list'>Keywords</a>";
		while($row = $this->db_next())
		{
			if ($members[$row["list_id"]])
			{
				$people_count = $members[$row["list_id"]];
			}
			else
			{
				$people_count = 0;
			};

			if ($keyword_counts[$row["id"]])
			{
				$doc_count = $keyword_counts[$row["id"]];
			}
			else
			{
				$doc_count = 0;
			};

			$this->vars(array(
				"keyword" => $row["keyword"],
				"people_count" => $people_count,
				"doc_count" => $doc_count,
				"id" => $row["id"],
				"list_id" => $row["list_id"],
			));
			$c .= $this->parse("LINE");
		};
		$this->vars(array("LINE" => $c,
				"reforb" => $this->mk_reforb("delete_keywords",array())));
		return $this->parse();
	}
	
	////
	// !Kustutab keywordide listist tulnud andmete pohjal keyworde 
	function delete_keywords($args = array())
	{
		extract($args);
		if (is_array($check))
		{
			foreach($check as $key => $val)
			{
				$q = "DELETE FROM keywords2objects WHERE keyword_id = '$key'";
				$this->db_query($q);

				$q = "DELETE FROM keywords WHERE id = '$key'";
				$this->db_query($q);
			};
		}
		return $this->mk_my_orb("list",array());
	}

	////
	// !Tagastab mingi objekti juurde lisatud vıtmesınad
	// argumendid:
	// oid (int) - objekti id
	function get_keywords($args = array())
	{
		extract($args);
		$q = "SELECT * FROM keywords2objects WHERE oid = '$oid'";
		$this->db_query($q);
		$idlist = array();
		$result = "";
		while($row = $this->db_next())
		{
			$idlist[] = $row["keyword_id"];
		};
		
		if (sizeof($idlist) > 0)
		{
			$ids = join(",",$idlist);
			$q = sprintf("SELECT keyword FROM keywords WHERE id IN ('%s')",$ids);
			$this->db_query($q);
			$resarray = array();
			while($row = $this->db_next())
			{
				$resarray[] = $row["keyword"];
			};
			$result = join(",",$resarray);
		};
		return $result;
	}

	////
	// !Tagastab koik registreeritud votmesonad
	// argumendid: type = ARR_LISTID - array index is list id, default
	// type = ARR_KEYWORD - array index is keyword
	// $beg = only return keywords that begin with this
	function get_all_keywords($args = array())
	{
		$beg = $args["beg"];	// the returned keywords must match this one on the beginning
													// if it's an array then they must match any of them
		$begar = array();
		if (is_array($beg))
		{
			foreach($beg as $b)
			{
				$begar[$b] = strlen($b);
			}
		}
		else
		{
			$begar[$beg] = strlen($beg);
		}

		global $strip_keyword_grps;

		$q = "SELECT list_id,keyword FROM keywords ORDER BY keyword";
		$this->db_query($q);
		$resarray = array();
		while($row = $this->db_next())
		{
			$match = false;
			foreach($begar as $beg => $blen)
			{
				if (substr($row["keyword"],0,$blen) == $beg)
				{
					$match = true;
					break;
				}
			}

			if ($match)
			{
				if ($strip_keyword_grps)
				{
					$row["keyword"] = preg_replace("/(.*\/)/","",$row["keyword"]);
				}

				if ($args["type"] == ARR_KEYWORD)
				{
					$resarray[$row["keyword"]] = $row["keyword"];
				}
				else
				{
					$resarray[$row["list_id"]] = $row["keyword"];
				}
			}
		};
		return $resarray;
	}
			
			
	////
	// !Seda kutsutakse dokude salvestamise juurest v‰lja.
	// Uuendab mingi dokuga (objektiga) seotud keywordide nimekirja
	// argumendid:
	// keywords (string) - komadega eraldatud m‰rksınade nimekiri
	// oid (int) - objekti (dokumendi id) millega m‰rksınad siduda
	function update_keywords($args = array())
	{
		extract($args);
		$this->quote($keywords);
		$keywordlist = explode(",",$keywords);
		$klist = array();
		$ids = array();
		$cids = array();
		// vaja leida koigi votmesınade ID-d. Kui ei ole, siis tekitame uue
		foreach($keywordlist as $val)
		{
			$keyword = trim($val);
			$klist[] = $keyword;
			$arg = join(",",map("'%s'",$klist));
		};
		$q = sprintf("SELECT * FROM keywords WHERE keyword IN (%s)",$arg);
		$this->db_query($q);	
		while($row = $this->db_next())
		{
			$ids[$row["keyword"]] = $row["id"];
			$cids[$row["keyword"]] = $row["category_id"];
		};
	
		// teeme kindlaks koik votmesonad, millel polnud ID-d (uued)
		// loome ka uue listi votmesona jaoks
		classload("lists");
		$lists = new lists();

		foreach($klist as $val)
		{
			if (strpos($val,"/") > 0)
			{
				list($category,$keyword) = explode("/",$val);
				$q = "SELECT * FROM keywordcategories WHERE name = '$category'";
				$this->db_query($q);
				$row = $this->db_next();
				if (!$row)
				{
					$q = "SELECT MAX(id) AS id FROM keywordcategories";
					$this->db_query($q);
					$row = $this->db_next();
					$catid = $row["id"];
					$catid++;
					$q = "INSERT INTO keywordcategories (id,name) VALUES ('$catid','$category')";
					$this->db_query($q);
				}
				else
				{
					$catid = $row["id"];
				};
			}
			else
			{		
				$keyword = $val;
			};
			// kui keywordi pole defineeritud, siis loome uue
			if (!$ids[$val])
			{
				// well, it looks almost like mysql_insert_id does not work always, so we screw around a little
				$q = "SELECT MAX(id) AS id FROM keywords";
				$this->db_query($q);
				$row = $this->db_next();
				$newid = $row["id"];
				$newid++;
				$this->save_handle();
				$list_id = $lists->create_list(array(
								"parent" => KEYWORD_LISTS,
								"name" => $keyword,
								"comment" => "automaagiliselt loodud list",
							));
				$this->restore_handle();
				$q = "INSERT INTO keywords (id,list_id,keyword,category_id) VALUES ('$newid','$list_id','$keyword','$catid')";
				$this->db_query($q);
				$ids[$val] = $newid;

			}
			// keyword oli, aga kategooria on muutunud
			elseif ($cids[$val] != $catid1)
			{
				$q = sprintf("UPDATE keywords SET category_id = '%d',keyword = '%s' WHERE id = '%d'",$catid,$keyword,$ids[$val]);
				$this->db_query($q);
			};
			// otherwise pole midagi vaja teha
				
				
				

		};

		// n¸¸d peaksid koik votmesonad baasis kajastatud olema

		// votame vanad seosed maha
		$q = "DELETE FROM keywords2objects WHERE oid = '$oid'";
		$this->db_query($q);

		// ja loome uued

		foreach($klist as $val)
		{
			$q = sprintf("INSERT INTO keywords2objects (oid,keyword_id) VALUES ('%d','%s')",$oid,$ids[$val]);
			$this->db_query($q);
		}

		// and we should be done now
	}
	

	// see peaks vist tegelikult hoopis mujal klassis olema
	function _get_user_data($args = array())
	{
		classload("users_user","form");
		$u = new users_user();
		$udata = $u->get_user(array(
				"uid" => UID,
			));
		$jf = unserialize($udata["join_form_entry"]);
		$eesnimi = $perenimi = "";
		if (is_array($jf))
		{
			$f = new form();
			foreach($jf as $joinform => $joinentry)
			{ 
				$f->load($joinform);
				$f->load_entry($joinentry);
				$el = $f->get_element_by_name("Eesnimi");
				if ($el->entry)
				{
					$eesnimi = $el->entry;
				};
				$el = $f->get_element_by_name("Perekonnanimi");
				if ($el->entry)
				{	
					$perenimi = $el->entry;
				};
				$el = $f->get_element_by_name("Ees_ja_perekonnanimi");
				if ($el->entry)
				{
					$nimi = $el->entry;
				};
			};
		};
		$res = array();
		$res["Eesnimi"] = $eesnimi;
		$res["Perenimi"] = $perenimi;
		$res["Nimi"] = $nimi;
		$res["Email"] = $udata["email"];
		return $res;
	}

	function show_interests_form($beg = "",$section = 0)
	{
		if ($beg != "")
		{
			$beg = explode(",",$beg);
		}
		$this->read_template("keywords.tpl");
		$udata = $this->_get_user_data();
		classload("list");
		$mlist = new mlist();
		$kw = new keywords();
		$act = $mlist->get_user_lists(array(
					"uid" => UID,
					));
		global $REQUEST_URI,$ext;
		$udata = $this->_get_user_data();
		$name = ($udata["Nimi"]) ? $udata["Nimi"] : $udata["Eesnimi"] . " " . $udata["Perenimi"];
		$this->vars(array(
				"name" => $name,
				"email" => $udata["Email"],
				"keywords" => $this->multiple_option_list($act,$kw->get_all_keywords(array("beg" => $beg))),
				"reforb" => $this->mk_reforb("submit_interests", array("gotourl" => urlencode("/index.$ext?section=$section")))
		));
		return $this->parse();
	}

	function show_interests_form2($args = array())
	{
		extract($args);
		$this->read_template("keywords2.tpl");
		$udata = $this->_get_user_data();
		classload("list");
		$mlist = new mlist();
		$kw = new keywords();
		$act = $mlist->get_user_lists(array(
				"uid" => UID,
			));
		global $ext;
		$kwlist = $this->get_all_keywords(array("beg" => $beg));
		$ret = "";
		foreach($kwlist as $k => $v)
		{
			$this->vars(array(
					"checked" => ($act[$k]) ? "checked" : "",
					"id" => $k,
					"keyword" => $v,
			));
			$ret .= $this->parse("keywords");
		};
		$name = ($udata["Nimi"]) ? $udata["Nimi"] : $udata["Eesnimi"] . " " . $udata["Perenimi"];
		$this->vars(array(
				"name" => $name,
				"email" => $udata["Email"],
				"keywords" => $ret,
				"reforb" => $this->mk_reforb("submit_interests",array("gotourl" => urlencode("/index.$ext?section=$section"))),
			));
		return $this->parse();
	}

	function show_categories($args = array())
	{
		extract($args);
		$this->read_template("categories.tpl");
		$q = "SELECT * FROM keywordcategories ORDER BY name";
		$this->db_query($q);
		$c = "";
		while($row = $this->db_next())
		{
			$this->vars(array(
					"id" => $row["id"],
					"name" => $row["name"],	
			));
			$c .= $this->parse("line");
		};
		$this->vars(array("line" => $c,
				  "reforb" => $this->mk_reforb("select_keywords",array("after" => $after))));
		return $this->parse();
	}

	function select_keywords($args = array())
	{
		extract($args);
		$this->read_template("pick_keywords.tpl");
		print "after = $after<br>";
		global $HTTP_REFERER;
		$udata = $this->_get_user_data();
		$name = ($udata["Nimi"]) ? $udata["Nimi"] : $udata["Eesnimi"] . " " . $udata["Perenimi"];
		if (!is_array($category))
		{
			$retval = $HTTP_REFERER;
		}
		else
		{
			$c = "";
			foreach($category as $key => $val)
			{
				$q = "SELECT * FROM keywordcategories WHERE id = '$val'";
				$this->db_query($q);
				$row = $this->db_next();
				$this->vars(array("category" => $row["name"]));
				$d = "";
				$q = "SELECT * FROM keywords WHERE category_id = '$val' ORDER BY keyword";
				$this->db_query($q);
				while($row = $this->db_next())
				{
					$this->vars(array(
							"keyword" => $row["keyword"],
							"id" => $row["id"],
					));
					$d .= $this->parse("line.subline");
				};
				$this->vars(array("subline" => $d));
				$c .= $this->parse("line");
			}
			$this->vars(array("line" => $c,
					  "name" => $name,
					  "reforb" => $this->mk_reforb("submit_interests2",array("after" => $after)),
					  "email"=> $udata["Email"]));
			$retval = $this->parse();
		};
		return $retval;
	}
};
?>
