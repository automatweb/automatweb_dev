<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/keywords.aw,v 2.52 2004/11/09 17:13:07 sven Exp $
// keywords.aw - dokumentide võtmesõnad
/*
@tableinfo keywords index=id master_table=keywords master_index=brother_of
@classinfo syslog_type=ST_KEYWORDS relationmgr=yes status=no no_status=1

@default table=keywords
@default group=general

@property keyword type=textbox
@caption Märksõna
*/

define("ARR_LISTID", 1);
define("ARR_KEYWORD", 2);
class keywords extends class_base
{
	function keywords()
	{
		$this->init(array(
			"clid" => CL_KEYWORD,
		));
	}

	
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($data["name"])
		{
			case 'name':
				return PROP_IGNORE;
			break;
			case 'comment':
				return PROP_IGNORE;
			break;
		}
	}

	/**
		@attrib name=show_documents nologin=1
		@param id required
	**/
	function show_documents($id)
	{
		extract($id);
		$conn = new connection();
		$conns = $conn->find(array(
			"from.class_id" => CL_DOCUMENT,
			"to" => $id,
		));
		
		if($conns)
		{
			foreach ($conns as $item)
			{
				$from[] = $item["from"];
			}
			
			$docs = new object_list(array(
				"oid" => $from,
				"class_id" => CL_DOCUMENT,
			));
			
			if($docs->count() > 0)
			{
				
				$this->read_template("/automatweb/keywords/doclist2.tpl");
				//$this->submerge = 1;
				foreach ($docs->arr() as $doc)
				{
					$this->vars(array(
						"date" => get_lc_date($doc->prop("modified")),
						"title" => $doc->prop("title"),
						"author" => $doc->prop("author"),
						"id" => $doc->id(),
					));
					$line.= $this->parse("LINE");			
				}
				$key_obj = &obj($id);
				$this->vars(array(
					"LINE" => $line,
					"keyword" => $key_obj->prop("keyword"),
				));
				
				$retval = $this->parse();
			}
		}
		return $retval;
	}
	
	/** Kuvab keywordi lisamise vormi 
		
		@attrib name=new params=name default="0"
		
		@param parent required acl="add"
		
		@returns
		
		
		@comment

	**/
	/*function add($args = array())
	{
		extract($args);
		$this->read_template("change.tpl");
		$this->mk_path($parent,"Lisa võtmesõna");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent,"add" => 1)),
		));
		return $this->parse();
	}*/

	/** Kuvab keywordi muutmise vormi 
		
		@attrib name=change params=name default="0"
		
		@param id required acl="edit;view"
		
		@returns
		
		
		@comment

	**//*
	function change($args = array())
	{
		extract($args);
		$this->read_template("change.tpl");
		$obj = new object($id);
		$this->mk_path($obj->parent(),"Muuda võtmesõma");
		$kw = $this->db_fetch_field("SELECT keyword FROM keywords WHERE oid = '$id'","keyword");
		$this->vars(array(
			"keyword" => $kw,
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}*/

	/** Submitib keywordi 
		
		@attrib name=submit params=name default="0"
		
		
		@returns
		
		
		@comment

	**//*
	function submit($args = array())
	{
		extract($args);
		if ($add)
		{
			$o = obj();
			$o->set_parent($parent);
			$o->set_class_id(CL_KEYWORD);
			$o->set_status(STAT_ACTIVE);
			$o->set_name($keyword);
			$id = $o->save();
			$q = "INSERT INTO keywords (keyword,oid,type) VALUES ('$keyword','$id','1')";
			$this->db_query($q);
		}
		else
		{
			$o = obj($id);
			$o->set_name($keyword);
			$o->save();
			$q = "UPDATE keywords SET 
				keyword = '$keyword'
				WHERE oid = '$id'";
			$this->db_query($q);
		}
		return $this->mk_my_orb("change",array("id" => $id));
	}
	*/
	////
	// Uuendab dokuga lingitud keywordide nimekrija
	function update_relations($args = array())
	{
		extract($args);
		$q = "DELETE FROM keywordrelations WHERE id = '$id'";
		$this->db_query($q);
		$q = "SELECT keywords.oid AS oid,keyword FROM keywords LEFT JOIN objects ON (keywords.oid = objects.oid) WHERE objects.status = 2";
		$this->db_query($q);
		$data = " $data ";
		while($row = $this->db_next())
		{
			$row["keyword"] = str_replace("/","\/",$row["keyword"]);
			if (preg_match("/$row[keyword]/i",$data))
			{
				$this->save_handle();
				$q = "INSERT INTO keywordrelations (id,keyword_id) VALUES ('$id','$row[oid]')";
				$this->db_query($q);
				$this->restore_handle();
			}
		}
	}

	/** Kuvab dokude nimekirja, mis mingi kindla võtmesõnaga "seotud" on. 
		
		@attrib name=doclist params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment
		argumendid
		oid - objekti id

	**/
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

	/** Kuvab kasutajate nimekirja, kes mingi votmesona listis on. 
		
		@attrib name=listmembers params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment
		argumendid
		id (int)

	**/
	function listmembers($args = array())
	{
		extract($args);
		$this->read_template("users.tpl");
		$this->info["site_title"] = "<a href='orb.".$this->cfg["ext"]."?class=keywords&action=list'>Keywords</a>";
		$q = "SELECT users.uid AS uid,
				users.email AS email,
				ml_users.name AS name,
				ml_users.tm AS tm,
				ml_users.mail AS mail
				FROM ml_users
				LEFT JOIN users ON (ml_users.uid = users.uid)
				WHERE list_id = '$id'";
		$this->db_query($q);
		$c = "";
		while($row = $this->db_next())
		{
			$this->vars(array(
				"uid" => $row["uid"],
				"email" => $row["mail"],
				"name" => $row["name"],
				"tm" => ($row["tm"]) ? $this->time2date($row["tm"],2) : "(info puudub)",
			));
			$c .= $this->parse("LINE");
		};
		$this->vars(array("LINE" => $c));
		return $this->parse();
	}
	/** Teavitab koiki votmesonalistide liikmeid muudatustest 
		
		@attrib name=notify params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function notify($args = array())
	{
		extract($args);
		$gp = obj($this->cfg["list"]);
		$doc = new object($id);
		$q = "SELECT keywords.list_id AS list_id,keywords.keyword AS keyword  FROM keywords2objects
			LEFT JOIN keywords ON (keywords2objects.keyword_id = keywords.id)
			WHERE keywords.oid = $id";
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
		$email = get_instance("email");
		$this->info["site_header"] ="<a href='orb.".$this->cfg["ext"]."?class=document&action=change&id=$id'>Dokument</a>";
		$baseurl = $this->cfg["baseurl"];
		foreach($lx as $row)
		//while($row = $this->db_next())
		{
			$this->save_handle();
			$q = "SELECT * FROM objects WHERE oid = '$row[list_id]'";
			$this->db_query($q);
			$ml = $this->db_next();
			// kui sellele listile pole default maili määratud
			if (!$ml["last"])
			{
				// checkime, kas grandparentil on default list määratud
				if (is_oid($gp->last()))
				{
					// oli. nyyd on meil default listi id käes. Tuleb ainult lugeda selle listi last
					#$ml["last"] = $gp["last"];
					$this->save_handle();
					$rl = obj($gp->last());
					$this->restore_handle();
					if ($rl)
					{
						$ml["last"] = $rl->last();
					};

				}
			};
			$q = "SELECT * FROM ml_mails WHERE id = '$ml[last]'";
			$this->db_query($q);
			$ml = $this->db_next();
			$this->restore_handle();
			if (!$ml)
			{
				print sprintf(LC_KEYWORDS_ERR_NO_DEFAULT,$row[last]);
			}
			else
			{
				$content = $ml["contents"];
				$content = str_replace("#url#","$baseurl/index.".$this->cfg["ext"]."?section=$id",$content);
				$content = str_replace("#title#",$doc->name(),$content);
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
	
	/** Handleb saidi sees täidetud "interests" vormi datat 
		
		@attrib name=submit_interests params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_interests($args = array())
	{
		extract($args);
		classload("list");
		$list = new mlist();
		$list->remove_user_from_lists(array(
			"uid" => aw_global_get("uid"),
		));
		$list->add_user_to_lists(array(
			"uid" => aw_global_get("uid"),
			"name" => $name,
			"email" => $email,
			"list_ids" => $lists,
		));
		global $status_msg;
		$status_msg = LC_KEYWORDS_CHANGES_SAVED;
		session_register("status_msg");
		$res = "?type=interests";
		if ($gotourl != "")
		{
			$res = urldecode($gotourl);
		}
		return $this->cfg["baseurl"] . $res;
	}
	
	/** Handleb EBS stiilis huvideformist tulnud datat 
		
		@attrib name=submit_interests2 params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
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
		$lists = array();
		while($row = $this->db_next())
		{
			$kw[$row["category_id"]][] = $row["keyword"];
			$lists[$row["keyword"]] = $row["list_id"];
		};

		// vahepeal kui on defineeritud m2rks6nu valinud tyypide grupp, siis paneme ta sinna gruppi ka
		if ($this->cfg["dyn_group"])
		{
			$usu = get_instance("users_user");
			$usu->add_users_to_group($this->cfg["dyn_group"], array(aw_global_get("uid")),0,true);
		}

		// ja nyyd koostame meili
		$txt = "";
		$txt .= sprintf(LC_KEYWORDS_NAME,$name);
		$txt .= sprintf(LC_KEYWORDS_ADDRESS,$email);
		$uid = aw_global_get("uid");
		foreach($kw as $key => $val)
		{
			$txt .= "\n" . $catnamelist[$key] . "\n";
			$txt .= str_repeat("-",strlen($catnamelist[$key])) . "\n";
			foreach($val as $keyword)
			{
				$txt .= " - " . $keyword . "\n";
				$lid = $lists[$keyword];
			};
		};
		classload("list");
		$list = new mlist();
		$list->remove_user_from_lists(array(
			"uid" => aw_global_get("uid"),
		));
		$list->add_user_to_lists(array(
			"uid" => aw_global_get("uid"),
			"name" => $name,
			"email" => $email,
			"list_ids" => $lists,
		));
		$from = sprintf("%s <%s>",$name,$email);
		send_mail(KW_MAIL,KW_SUBJECT,$txt,"From: $from");
		$retval = $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?section=$after";
		return $retval;
	}

	/** Kuvab koikide keywordide vormi 
		
		@attrib name=list params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function list_keywords($args = array())
	{
		$this->read_template("list.tpl");
		// koigepealt uurime välja lugejate arvu listides
		
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

		$q = "SELECT *,keywordcategories.name AS cname FROM keywords
			LEFT JOIN keywordcategories ON (keywords.category_id = keywordcategories.id)
			ORDER BY category_id,keyword";
		$this->db_query($q);
		$c = "";
		$last = "";
		$this->info["site_title"] = "<a href='orb.".$this->cfg["ext"]."?class=keywords&action=list'>Keywords</a>";
		while($row = $this->db_next())
		{
			if ($last != $row["cname"])
			{
				$this->vars(array("title" => $row["cname"]));
				$c .= $this->parse("HEADER");
				$last = $row["cname"];
			};
				
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
		$this->vars(array(
			"LINE" => $c,
			"reforb" => $this->mk_reforb("delete_keywords",array())
		));
		return $this->parse();
	}
	
	/** Kustutab keywordide listist tulnud andmete pohjal keyworde 
		
		@attrib name=delete_keywords params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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
	// !Tagastab mingi objekti juurde lisatud võtmesõnad
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

		$q = "SELECT list_id,keyword,id FROM keywords ORDER BY keyword";
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
				if ($this->cfg["strip_grps"])
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
	// !Seda kutsutakse dokude salvestamise juurest välja.
	// Uuendab mingi dokuga (objektiga) seotud keywordide nimekirja
	// argumendid:
	// keywords (string) - komadega eraldatud märksõnade nimekiri
	// oid (int) - objekti (dokumendi id) millega märksõnad siduda
	function update_keywords($args = array())
	{
		return;	// FIXME: this is fucked - old lists are gone and this probably does not work anyway
		extract($args);
		$keywordlist = explode(",",$keywords);
		$categories = array();
		$klist = array();
		$ids = array();
		$cids = array();
		// vaja leida koigi votmesõnade ID-d. Kui ei ole, siis tekitame uue
		foreach($keywordlist as $val)
		{
			$keyword = trim($val);
			if (strpos($keyword,"/") > 0)
			{
				list($category,$keyword) = explode("/",$keyword);
				$categories[$keyword] = $category;
			};
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
		//$lists = get_instance("lists");

		foreach($klist as $val)
		{
			$keyword = trim($val);
			#if (strpos($val,"/") > 0)
			if ($categories[$keyword])
			{
				//list($category,$keyword) = explode("/",$val);
				#$keyword = $val;
				$category = $categories[$keyword];
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
			};
			// kui keywordi pole defineeritud, siis loome uue
			if (!$ids[$keyword])
			{
				// well, it looks almost like mysql_insert_id does not work always, so we screw around a little
				$q = "SELECT MAX(id) AS id FROM keywords";
				$this->db_query($q);
				$row = $this->db_next();
				$newid = $row["id"];
				$newid++;
				$this->save_handle();
				$list_id = $lists->create_list(array(
					"parent" => $this->cfg["list"],
					"name" => $keyword,
					"comment" => LC_KEYWORDS_AUTOMAG_LIST,
				));
				$this->restore_handle();
				$q = "INSERT INTO keywords (id,list_id,keyword,category_id) VALUES ('$newid','$list_id','$keyword','$catid')";
				$this->db_query($q);
				$ids[$val] = $newid;

			}
			// keyword oli, aga kategooria on muutunud
			elseif ($cids[$val] != $catid)
			{
				$q = sprintf("UPDATE keywords SET category_id = '%d',keyword = '%s' WHERE id = '%d'",$catid,$keyword,$ids[$val]);
				$this->db_query($q);
			};
			// otherwise pole midagi vaja teha
		};

		// nüüd peaksid koik votmesonad baasis kajastatud olema

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
		$that = get_instance("users");
		$udata = $that->get_user();
		$jf = unserialize($udata["join_form_entry"]);
		$eesnimi = $perenimi = "";
		if (is_array($jf))
		{
			$f = get_instance("formgen/form");
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

	function parse_aliases($args = array())
	{
		extract($args);
		$retval = "";
		if (preg_match("/_form algus=\"(.*)\" go=\"(.*)\"/",$matches[2], $maat))
		{
			$retval = $this->show_interests_form($maat[1],$maat[2]);
		}
		elseif (preg_match("/_check algus=\"(.*)\" go=\"(.*)\"/",$matches[2], $maat))
		{
			$retval = $this->show_interests_form2(array(
				"beg" => $maat[1],
				"section" => $maat[2],
			));
		}
		elseif (preg_match("/_kategooriad go=\"(.*)\"/",$matches[2], $maat))
		{
			$retval = $this->show_categories(array("after" => $maat[1]));
		};
		return $retval;
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
		$act = $mlist->get_user_lists(array(
			"uid" => aw_global_get("uid"),
		));
		$udata = $this->_get_user_data();
		$name = ($udata["Nimi"]) ? $udata["Nimi"] : $udata["Eesnimi"] . " " . $udata["Perenimi"];
		$this->vars(array(
			"name" => $name,
			"email" => $udata["Email"],
			"keywords" => $this->multiple_option_list($act,$this->get_all_keywords(array("beg" => $beg))),
			"reforb" => $this->mk_reforb("submit_interests", array("gotourl" => urlencode("/index.".$this->cfg["ext"]."?section=$section")))
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
		$kw = get_instance("keywords");
		$act = $mlist->get_user_lists(array(
			"uid" => aw_global_get("uid"),
		));
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
			"reforb" => $this->mk_reforb("submit_interests",array("gotourl" => urlencode("/index.".$this->cfg["ext"]."?section=$section"))),
		));
		return $this->parse();
	}

	function show_categories($args = array())
	{
		classload("list");
		$mlist = new mlist();
		$kw = get_instance("keywords");
		$act = $mlist->get_user_lists(array(
			"uid" => aw_global_get("uid"),
		));

		$cats = join(",",array_keys($act));
				
		$cids = array();
  	if ($cats != "")
		{
			$q = "SELECT category_id FROM keywords WHERE list_id IN ($cats)";
			$this->db_query($q);

			while($row = $this->db_next())
			{
				$cids[$row["category_id"]] = 1;
			};
		}

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
				"checked" => ($cids[$row["id"]]) ? "checked" : "",
			));
			$c .= $this->parse("line");
		};
		$this->vars(array(
			"line" => $c,
			"reforb" => $this->mk_reforb("select_keywords",array("after" => $after))
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=select_keywords params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function select_keywords($args = array())
	{
		extract($args);
		classload("list");
		$mlist = new mlist();
		$act = $mlist->get_user_lists(array(
			"uid" => aw_global_get("uid"),
		));
		$this->read_template("pick_keywords.tpl");
		$udata = $this->_get_user_data();
		$name = ($udata["Nimi"]) ? $udata["Nimi"] : $udata["Eesnimi"] . " " . $udata["Perenimi"];
		if (!is_array($category))
		{
			$retval = aw_global_get("HTTP_REFERER");
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
						"checked" => ($act[$row["list_id"]]) ? "checked" : "",
						"keyword" => $row["keyword"],
						"id" => $row["id"],
					));
					$d .= $this->parse("line.subline");
				};
				$this->vars(array("subline" => $d));
				$c .= $this->parse("line");
			}
			$this->vars(array(
				"line" => $c,
				"name" => $name,
				"reforb" => $this->mk_reforb("submit_interests2",array("after" => $after)),
				"email"=> $udata["Email"]
			));
			$retval = $this->parse();
		};
		return $retval;
	}

	////
	// !uuendab menyyde all olevad keywordidega tehtud dokude vendi. 
	// parameetrid:
	// $menu_ids = array -	kui see on m22ratud, siis uuendatakse aint nende menyyde all olevaid doku vendi, 
	//											kui pole siis k6ikide menyyde omi
	// $doc_ids = array -		kui see on m22ratud, siis uuendatakse aint nende dokude vendi, kui pole, siis k6ikide dokude
	function update_menu_keyword_bros($arr)
	{
		extract($arr);

		if (!is_array($menu_ids))
		{
			$menu_ids = array();
			$this->db_query("SELECT oid FROM objects WHERE class_id = ".CL_MENU." AND status != 0");
			while ($row = $this->db_next())
			{
				$menu_ids[] = $row["oid"];
			}
		}

		if (!is_array($doc_ids))
		{
			$doc_ids = array();
			$this->db_query("SELECT oid FROM objects WHERE (class_id = ".CL_DOCUMENT." OR class_id = ".CL_PERIODIC_SECTION.") AND status != 0");
			while ($row = $this->db_next())
			{
				$doc_ids[] = $row["oid"];
			}
		}

		$menuss = join(",",$menu_ids);
		$docss = join(",",$doc_ids);


		// select all the keywords for the menus
		$kwds = array();
		$this->db_query("SELECT * FROM keyword2menu WHERE menu_id IN ($menuss)");
		while ($row = $this->db_next())
		{
			$kwds[$row["menu_id"]][] = $row["keyword_id"];
		}

		// fetch all the brother docs for all the menus that are created by this function
		$bros = array();
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_BROTHER_DOCUMENT." AND parent IN ($menuss) AND status != 0 AND subclass = ".SC_BROTHER_DOC_KEYWORD." AND brother_of IN ($docss)");
		while ($row = $this->db_next())
		{
			$bros[$row["parent"]][$row["brother_of"]] = $row;
		}

		$d = get_instance("document_brother");

		// now go through all the menus and for each menu check the brother documents that are created from keywords and
		// delete/create them as necessary
		foreach($menu_ids as $mid)
		{
			$m_bros = $bros[$mid];
			$m_kwds = $kwds[$mid];

			// if there are no brothers and no keywords, dont process this menu
			if (!is_array($m_bros) && !is_array($m_kwds))
			{
				continue;
			}

			if (!is_array($m_bros))
			{
				$m_bros = array();
			}

			// figure out what docs must be brothered under this menu
			if (!is_array($m_kwds))
			{
				$m_kwds = array();
			}
			$m_kwdsstr = join(",",$m_kwds);
			$to_brother = array();
			if ($m_kwdsstr != "")
			{
				$this->db_query("SELECT * FROM keywordrelations WHERE keyword_id IN ($m_kwdsstr) AND id IN ($docss)");
				while ($row = $this->db_next())
				{
					$to_brother[$row["id"]] = $row["id"];
				}
			}

			// now figure out the difference in what objects exist under the menu and what sould exist. 
			// first the ones we need to add
			foreach($to_brother as $did => $did)
			{
				if (!$m_bros[$did])
				{
					// it isn't there so we must add it
					$d->create_bro(array("id" => $did, "parent" => $mid,"subclass" => SC_BROTHER_DOC_KEYWORD,"no_header" => true));
				}
			}

			// now the ones we need to delete
			foreach($m_bros as $o_did => $bdata)
			{
				if ($to_brother[$o_did] != $o_did)
				{
					$tmp = obj($bdata["oid"]);
					$tmp->delete();
				}
			}
		}
	}

	////
	// !returns an array of keywords suitable for feeding to a multiple select box
	function get_keyword_picker()
	{
		$kwds = array();
		$this->db_query("SELECT distinct(keyword) as keyword,objects.parent,objects.oid as oid FROM keywords LEFT JOIN objects ON objects.oid = keywords.oid");
		while ($row = $this->db_next())
		{
			$kwds[$row["oid"]] = $row;
		}

		$ob = get_instance("objects");
		$menus = $ob->get_list();

		$ret = array();
		foreach($kwds as $kid => $kdata)
		{
			$ret[$kid] = $menus[$kdata["parent"]]."/".$kdata["keyword"];
		}
		return $ret;
	}
	/*
	function set_property($arr)
	{
		
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "name":
				
			break;
		};
		return $retval;
	}*/
	
	function callback_pre_save($arr)
	{
		$arr["obj_inst"]->set_prop("name", $arr["request"]["keyword"]);
	}
	
	//This function finds and shows documents related to keyword

};
?>
