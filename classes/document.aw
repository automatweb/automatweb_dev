<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/document.aw,v 2.38 2001/07/26 12:55:12 kristo Exp $
// document.aw - Dokumentide haldus. 
global $orb_defs;
$orb_defs["document"] = "xml";
session_register("s_pic_sortby");	// doku edimisel aliaste sortimine
session_register("s_pic_order");
session_register("s_link_sortby");	
session_register("s_link_order");
session_register("s_table_sortby");	
session_register("s_table_order");
session_register("s_form_sortby");	
session_register("s_form_order");
session_register("s_file_sortby");	
session_register("s_file_order");
session_register("s_graph_sortby");	
session_register("s_graph_order");
session_register("s_gallery_sortby");	
session_register("s_gallery_order");

lc_load("document");
classload("msgboard","aw_style");
// erinevad dokumentide muutmise templated.
//  kui soovid uut lisada, siis paned selle kataloogi 
//  /www/automatweb/public/templates/automatweb/documents
// ja registreerid 2ra baasis automatweb tabelis
// template 
// insert into template (type,name,filename) values (0,'minu template','mytemplate.tpl')
// tyyp 0 ongi moeldud just muutmistemplatede jaoks
// ja edasi .. votad menueditorist suvalise koha pealt Metainfo lahti .. 
// ja valid seal dropdownist selle, millist template selle ja alamsektsioonide
// jaoks kasutatakse
// ahja veel. template sees peab olema vahemalt 1 sisuv2li ja docid.
// naiteks voib vaadata ylaltoodud kataloomas asuvad ed_short.tpl faili.

// perioodihaldus.
// link mingi objekti (voi siis selle parenti) perioodide juurde on
// http://site/automatweb/periods.aw?oid=foo

// oid siis mingi menyysektsoonid ID. DUH.
class document extends aw_template
{
	function document($period = 0)
	{
		$this->tpl_init("automatweb/documents");
		$this->db_init();
		// see on selleks, kui on vaja perioodilisi dokumente naidata
		$this->period = $period;
		
		$this->style_engine = new aw_style;
		lc_load("definition");
		global $lc_document;
		if (is_array($lc_document))
		{
			$this->vars($lc_document);
		}
			
		global $basedir;

		$xml_def = $this->get_file(array("file" => "$basedir/xml/documents/defaults.xml"));
                if ($xml_def)
		{
			$this->style_engine->define_styles($xml_def);
		}
		// siia tuleks kirja panna koik dokumentide tabeli v2ljade nimed,
		// mida voidakse muuta
		// key on kasutusel selleks, et formeerida logi jaoks moistlik string
		$this->knownfields = array(
				LC_DOCUMENT_TITLE		=> "title",
		 		LC_DOCUMENT_SUBTITLE 		=> "subtitle",
				LC_DOCUMENT_AUTHOR       		=> "author",
				LC_DOCUMENT_PHOTO       		=> "photos",
				LC_DOCUMENT_KEYWORD  		=> "keywords",
				LC_DOCUMENT_NAMES       		=> "names",
				LC_DOCUMENT_LEAD        		=> "lead",
				LC_DOCUMENT_SHOW_LEAD 		=> "showlead",
				LC_DOCUMENT_THEME       		=> "content",
				LC_DOCUMENT_FRONTPAGE     		=> "esilehel",
				LC_DOCUMENT_NR1		=> "jrk1",
				LC_DOCUMENT_NR2			=> "jrk2",
				LC_DOCUMENT_NR3			=> "jrk3",
				LC_DOCUMENT_FRONT_UP	=> "esileht_yleval",
				LC_DOCUMENT_FRONT_NEWS	=> "esilehel_uudis",
				LC_DOCUMENT_TITLE_CLIK	=> "title_clickable",
				LC_DOCUMENT_TSITAAT		=> "cite",
				LC_DOCUMENT_CANAL			=> "channel",
				LC_DOCUMENT_TIME		=> "tm",
				LC_DOCUMENT_FORUM		=> "is_forum",
				LC_DOCUMENT_LINK_TEXT		=> "link_text",
				LC_DOCUMENT_LEAD_COM		=> "lead_comments",
				LC_DOCUMENT_NEW_WIN		=> "newwindow",
				LC_DOCUMENT_RIGHT		=> "yleval_paremal",
				LC_DOCUMENT_TITLE_SHOW	=> "show_title",
				LC_DOCUMENT_COPYRIGHT		=> "copyright",
				LC_DOCUMENT_LONG_TITLE		=> "long_title",
				LC_DOCUMENT_NOBREAKS		=> "nobreaks",
				LC_DOCUMENT_NO_LEFT_PANE		=> "no_left_pane",
				LC_DOCUMENT_NO_RIGHT_PANE		=> "no_right_pane",
				LC_DOCUMENT_NO_SEARCH		=> "no_search",
				LC_DOCUMENT_SHOW_CHANGED =>	"show_modified");
	}

	function set_period($period)
	{
		$this->period = $period;	
	}

	// listib kõik dokumendid
	// iseenesest kahtlane funktsioon, imho ei lähe seda vaja
	function listall()
	{
		$q = "SELECT *, objects.parent as parent FROM documents
			LEFT JOIN objects ON
			(documents.docid = objects.oid)";
		$this->db_query($q);
	}

	function list_docs($parent,$period = -1,$status = -1,$visible = -1)
	{
		global $awt;
		$awt->start("db_documents->list_docs()");
		if ($period == -1)
		{
			if ($this->period > 0)
			{
				$period = $this->period;
			}
			else
			{
				$period = $this->get_cval("activeperiod");
			};
		};
		$this->db_query("SELECT * FROM menu WHERE id = $parent");
		$row = $this->db_next();
		$sections = unserialize($row["sss"]);
		$periods = unserialize($row["pers"]);
		
		if (is_array($sections))
		{
			$pstr = join(",",$sections);
			if ($pstr != "")
			{
				$pstr = "objects.parent IN ($pstr)";
			}
			else
			{
				$pstr = "objects.parent = $parent";
			};
		}
		else
		{
			$pstr = "objects.parent = $parent";
		};

		if (is_array($periods))
		{
			$rstr = join(",",$periods);
			if ($rstr != "")
			{
				$rstr = "objects.period IN ($rstr)";
			}
			else
			{
				$rstr = "objects.period = $period";
			}
		}
		else
		{
			$rstr = "objects.period = $period";
		};
	
		// kui staatus on defineerimata, siis näitame ainult aktiivseid dokumente
		$v.= " AND objects.status = " . ($status == -1) ? 2 : $status;

		if ($row["ndocs"] > 0)
		{
			$lm = "LIMIT ".$row["ndocs"];
		};

		$q = "SELECT documents.lead AS lead,
			documents.docid AS docid,
			documents.title AS title,
			documents.*,
			objects.period AS period,
			objects.class_id as class_id,
			objects.parent as parent
			FROM documents
			LEFT JOIN objects ON
			(documents.docid = objects.oid)
			WHERE $pstr && $rstr $v
			ORDER BY objects.period DESC,objects.jrk $lm";
		$this->db_query($q);
		$awt->stop("db_documents->list_docs()");
	}

	function fetch($docid,$field = "main") 
	{
		global $awt;
		if ($this->period > 0) 
		{
			$sufix = " && objects.period = " . $this->period;
		} 
		else 
		{
			$sufix = "";
		};
		$awt->start("doc_fetch");
		$q = "SELECT documents.*,
			objects.*
			FROM documents
			LEFT JOIN objects ON
			(documents.docid = objects.oid)
			WHERE docid = '$docid' $sufix";
		$this->db_query($q);
		$awt->stop("doc_fetch");
		$data = $this->db_fetch_row();
		
		if (!$data)
		{
			// tshekime kas oli hoopis vend 2kki
			$oo = $this->get_object($docid);
			$q = "SELECT documents.*,
					documents.keywords AS keywords,
					objects.cachedirty AS cachedirty,
					objects.parent AS parent,
					objects.period AS period,
					objects.modified AS modified
				FROM documents
					LEFT JOIN objects ON
					(documents.docid = objects.oid)
				WHERE docid = '".$oo["brother_of"]."' $sufix";
			$this->db_query($q);
			$awt->stop("doc_fetch");
			$data = $this->db_fetch_row();
		}

		if (gettype($data) == "array") 
		{
			 $data["content"] = trim($data["content"]);
			 $data["lead"] = trim($data["lead"]);
			 $data["cite"] = trim($data["cite"]);
		};
		$this->dequote($data);
		if (preg_match("/<P(.*)>((&nbsp;)*)<\/P>/",$data["lead"]))
		{
			$data["lead"] = "";
		}
		$this->data = $data;
		return $data;
	}

	// see on lihtsalt wrapper backwards compatibility jaoks
	function show($docid,$text = "undef",$tpl="plain.tpl",$leadonly = -1,$secID = -1) 
	{
		$params["docid"] 		= $docid;
		$params["text"] 		= $text;
		$params["tpl"] 		= $tpl;
		$params["leadonly"] 	= $leadonly;
		$params["secID"] 		= $secID;
		return $this->gen_preview($params);
	}

	////
	// !genereerib objekti nö valmiskujul
	// sellest saab wrapper järgnevale funktsioonile
	// params: docid, text, tpl, tpls, leadonly, strip_img, secID, boldlead, tplsf, notitleimg, showlead, no_stip_lead, doc
	// tpls - selle votmega antakse ette template source, mille sisse kood paigutada
	// doc - kui tehakse p2ring dokude tabelisse, siis v6ib ju sealt saadud inffi kohe siia kaasa panna ka
	//       s22stap yhe p2ringu.
	function gen_preview($params) 
	{
		extract($params);
		$tpl = isset($params["tpl"]) ? $params["tpl"] : "plain.tpl";
		!isset($leadonly) ? $leadonly = -1 : "";
		!isset($strip_img) ? $strip_img = 0 : "";
		!isset($notitleimg) ? $notitleimg = 0 : "";
		
		global $classdir,$baseurl,$ext,$awt;

		$awt->count("db_documents->gen_preview()");
		$awt->start("doc_gen_preview");

		$align= array("k" => "align=\"center\"", "p" => "align=\"right\"" , "v" => "align=\"left\"" ,"" => "");

		$awt->start("gen_preview1");
	
		// küsime dokumendi kohta infot
		// muide docid on kindlasti numbriline, aliaseid kasutatakse ainult
		// menueditis.
		if (!isset($doc) || !is_array($doc))
		{
			$doc = $this->fetch($docid);
			$docid = $doc["docid"];
		};
		if (!isset($doc))
		{
			// objekti polnud, bail out
			return false;
		};
		
		$awt->start("db_documents->gen_preview()::starter");
		$this->tpl_reset();
		$this->tpl_init("automatweb/documents");
		
		$this->no_right_pane = $doc["no_right_pane"];	// see on sellex et kui on laiem doku, siis menyyeditor tshekib
		$this->no_left_pane = $doc["no_left_pane"];		// neid muutujaid ja j2tab paani 2ra kui tshekitud on.

		// kui tpls anti ette, siis loeme template sealt,
		// muidu failist.
		if (isset($tpls) && strlen($tpls) > 0) 
		{
			$this->templates["MAIN"] = $tpls;
		} 
		else
		if (isset($tplsf) && strlen($tplsf) > 0) 
		{
			$this->read_template($tplsf);
		} 
		else 
		{
			$this->read_template($tpl);
		};
		$this->vars(array("imurl" => "/images/trans.gif"));

		// load localization settings and put them in the template
		lc_site_load("document",$this);
		if (isset($GLOBALS["lc_doc"]) && is_array($GLOBALS["lc_doc"]))
		{
			$this->vars($GLOBALS["lc_doc"]);
		 }
		// leiame kategooria cache jaoks
		// vastavalt sellele kas kysiti leadi voi kogu asja
		
		$this->add_hit($docid);

		// miski kahtlane vark siin. Peaks vist sellele ka cachet rakendama?
		if (!(strpos($doc["content"], "#telekava_") === false))
		{
			return $this->telekava_doc($doc["content"]);
		}

		// vaatame kas vaja poolitada kui arhiivis oleme
		if ($GLOBALS["in_archive"])
		{
			$doc["content"] = str_replace("#poolita#", "",$doc["content"]);
		}
		else
		{
			if (!(($pp = strpos($doc["content"],"#poolita#")) === false))
			{
				$doc["content"] = substr($doc["content"],0,$pp)."<br><B>Edasi loe ajakirjast!</b></font>";
			}
		}

		// laeme vajalikud klassid
		classload("acl","form","table","extlinks","images","gallery");

		$tbl = new table;
		$img = new db_images;
		$retval = "";
		$used = array();

		$awt->stop("db_documents->gen_preview()::starter");
		$awt->start("db_documents->gen_preview()::leadonly_bit");

		// kui vaja on näidata ainult dokumendi leadi, siis see tehakse siin
 		if ($leadonly > -1) 
		{
			// we have some really stupid code here, me thinks
			// stripime pildid välja. ja esimese pildi salvestame cachesse
			// et seda mujalt kätte saaks
			if ($strip_img) 
			{
				// otsime pilditage 
		 		if (preg_match("/#p(\d+?)(v|k|p|)#/i",$doc["lead"],$match)) 
				{
					// asendame 
					$idata = $img->get_img_by_oid($docid,$match[1]);
					$this->li_cache[$docid] = $idata["url"];
				} 
				else 
				{
					// ei leidnud, asendame voimaliku pildi url-i transparent gif-iga
					$this->vars(array("imurl" => "/images/trans.gif"));
				};
				// ja stripime leadist *koik* objektitagid välja.
				$doc["lead"] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc["lead"]);
			};
			$doc["content"] = "$doc[lead]<br>";
		} 
		else 
		{
			if (($doc["lead"]) && ($doc["showlead"] == 1 || $showlead == 1) )
			{
				if ($no_strip_lead != 1)
				{
					$doc["lead"] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc["lead"]);
				}
				$txt = "";

				if ($boldlead) 
				{
					$txt = "<b>";
				};
				$txt .= $doc["lead"];
				if ($boldlead) 
				{
					$txt .= "</b>";
				};
				$txt .= "<p>$doc[content]";
				$doc["content"] = $txt;
			};
		};
		$awt->stop("db_documents->gen_preview()::leadonly_bit");

		// all the style magic is performed inside the style engine
		$doc["content"] = $this->style_engine->parse_text($doc["content"]); 
		
		$doc["content"] = preg_replace("/<loe_edasi>(.*)<\/loe_edasi>/isU","<a href='$baseurl/index.$ext/section=$docid'>\\1</a>",$doc["content"]);
		// sellel real on midagi pistmist WYSIWYG edimisvormiga
		$doc["content"] = preg_replace("/<\?xml(.*)\/>/imsU","",$doc["content"]); 

		$mp = $this->register_parser(array(
					"reg" => "/(#)(\w+?)(\d+?)(v|k|p|)(#)/i",
					));

		// pildid
		$this->register_sub_parser(array(
					"idx" => 2,
					"match" => "p",
					"class" => "images",
					"reg_id" => $mp,
					"function" => "parse_alias",
					"templates" => array("image","image_linked","image_inplace"),
				));
		// välised lingid
		$this->register_sub_parser(array(
					"idx" => 2,
					"match" => "l", // L
					"class" => "extlinks",
					"reg_id" => $mp,
					"function" => "parse_alias",
					"reset" => "reset_aliases",
					"templates" => array("link"),
				));
		// tabelid	
		$this->register_sub_parser(array(
					"idx" => 2,
					"match" => "t", // L
					"class" => "table",
					"reg_id" => $mp,
					"function" => "parse_alias",
				));

		// guestbuugid
		$this->register_sub_parser(array(
					"idx" => 2,
					"match" => "b",
					"class" => "guestbook",
					"reg_id" => $mp,
					"function" => "parse_alias",
				));
		
		// failid
		$this->register_sub_parser(array(
					"idx" => 2,
					"match" => "v",
					"class" => "file",
					"reg_id" => $mp,
					"function" => "parse_alias",
				));
		
		// vormid
		$this->register_sub_parser(array(
					"idx" => 2,
					"match" => "f",
					"class" => "form",
					"reg_id" => $mp,
					"function" => "parse_alias",
				));

		// vormi p2rjad
		$this->register_sub_parser(array(
					"idx" => 2,
					"match" => "c",
					"class" => "form_chain",
					"reg_id" => $mp,
					"function" => "parse_alias",
				));
		
		// graafikud
		$this->register_sub_parser(array(
					"idx" => 2,
					"match" => "g",
					"class" => "graph",
					"reg_id" => $mp,
					"function" => "parse_alias",
				));
		// galeriid
		$this->register_sub_parser(array(
					"idx" => 2,
					"match" => "y",
					"class" => "gallery",
					"reg_id" => $mp,
					"function" => "parse_alias",
				));
		
		// keywordide list. bijaatch!
		$mp = $this->register_parser(array(
					"reg" => "/(#)huvid(.+?)(#)/i",
					));

		$this->register_sub_parser(array(
					"class" => "keywords",
					"reg_id" => $mp,
					"function" => "parse_aliases",
					));

		// liituja info. bijaatch!
		$mp = $this->register_parser(array(
					"reg" => "/(#)liituja_andmed(#)/i",
					));

		$this->register_sub_parser(array(
					"class" => "users",
					"reg_id" => $mp,
					"function" => "show_join_data",
					));

		// parooli meeldetuletus. bijaatch!
		$mp = $this->register_parser(array(
					"reg" => "/#parooli_meeldetuletus edasi=\"(.*)\"#/i",
					));

		$this->register_sub_parser(array(
					"class" => "users",
					"reg_id" => $mp,
					"function" => "pwd_remind",
					));
		
		// eventsitega seonduv kamm
		$mp = $this->register_parser(array(
					"reg" => "/(#)event_(.+?)(#)/i",
					));

		$this->register_sub_parser(array(
					"class" => "events2",
					"reg_id" => $mp,
					"function" => "parse_alias",
					));

		// linkide parsimine
		while (preg_match("/(#)(\d+?)(#)(.*)(#)(\d+?)(#)/",$doc["content"],$matches))
		{
			$doc["content"] = str_replace($matches[0],"<a href='#" . $matches[2] . "'>$matches[4]</a>",$doc["content"]);
		};

		while(preg_match("/(#)(s)(\d+?)(#)/",$doc["content"],$matches))
		{
			$doc["content"] = str_replace($matches[0],"<a name='" . $matches[3] . "'> </a>",$doc["content"]);
		};
	

		if ($notitleimg != 1)
		{
			$doc["title"] = $this->parse_aliases(array(
							"text"	=> $doc["title"],
							"oid"	=> $docid,
					));
		}
		else
		{
			$doc["title"] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc["title"]);
		}

		if (!(strpos($doc["content"], "#board_last5#") === false))
		{
			$mb = new msgboard;
			$doc["content"] = str_replace("#board_last5#",$mb->mk_last5(),$doc["content"]);
		}

		// noja, mis fucking "undef" see siin on?
		if (!isset($text) || $text != "undef") 
		{
			$doc["content"] = $this->parse_aliases(array(
							"text"	=> $doc["content"],
							"oid"	=> $docid,
					));
		}; 

		if (!$doc["nobreaks"])	// kui wysiwyg editori on kasutatud, siis see on 1 ja pole vaja breike lisada
		{
			$doc["content"] = str_replace("\r\n","<br>",$doc["content"]);
		}
		$doc["content"] = str_replace("\n","",$doc["content"]);

		$pb = "";
		if ($doc["photos"])
		{
			if (DOC_LINK_AUTHORS && ($this->templates["pblock"]))
			{
				$x = $this->get_relations_by_field(array("field"    => "title",
						 "keywords" => $doc["photos"],
						 "section"  => DOC_LINK_AUTHORS_SECTION));
				$authors = array();
				global $ext;
				while(list($k,$v) = each($x)) 
				{
					if (DOC_LINK_DEFAULT_LINK != "")
					{
						if ($v) 
						{
							$authors[] = sprintf("<a href='/index.$ext?section=%s'>%s</a>",$v,$k);
						} 
						else 
						{
							$authors[] = sprintf("<a href='%s'>%s</a>",DOC_LINK_DEFAULT_LINK,$k);
						};
					}
					else
					{
						$authors[] = $k;
					}
				};
				$author = join(", ",$authors);
				$this->vars(array("photos" => $author));
			 	$pb = $this->parse("pblock");
			} 
			else 
			{
				$this->vars(array("photos" => $doc["photos"]));
			 	$pb = $this->parse("pblock");
			};
		};

		classload("msgboard");
		$t = new msgboard;
		$nc = $t->get_num_comments($docid);
		$nc = $nc < 1 ? "0" : $nc;
		$doc["content"] = str_replace("#kommentaaride arv#",$nc,$doc["content"]);

		$doc["content"] = preg_replace("/#kommentaar#(.*)#\/kommentaar#/isU","<a class=\"links\" href='$baseurl/comments.$ext?section=$docid'>\\1</a>",$doc["content"]);

	// <mail to="bla@ee">lahe tyyp</mail>
    $doc["content"] = preg_replace("/<mail to=\"(.*)\">(.*)<\/mail>/","<a class='mailto_link' href='mailto:\\1'>\\2</a>",$doc["content"]);
		
		$doc["content"] = str_replace(LC_DOCUMENT_CURRENT_TIME,$this->time2date(time(),2),$doc["content"]);

	classload("users");
	if (!(strpos($doc["content"],"#liitumisform") === false))
	{
		preg_match("/#liitumisform info=\"(.*)\"#/",$doc["content"], $maat);
 
      // siin tuleb n2idata kasutaja liitumisformi, kuhu saab passwordi ja staffi kribada.
      // aga aint sel juhul, kui kasutaja on enne t2itnud k6ik miski grupi formid.
		$dbu = new users;
		$doc["content"] = preg_replace("/#liitumisform info=\"(.*)\"#/",$dbu->get_join_form($maat[1]),$doc["content"]);
	}
				
	$ab = "";
	if ($doc["author"]) 
	{
		if (DOC_LINK_AUTHORS && isset($this->templates["ablock"])) 
		{
			// YYY
			$x = $this->get_relations_by_field(array(
				"field"    => "title",
				"keywords" => $doc["author"],
				"section"  => DOC_LINK_AUTHORS_SECTION
			));
			$authors = array();
			global $ext;
			while(list($k,$v) = each($x)) 
			{
				if (DOC_LINK_DEFAULT_LINK != "")
				{
					if ($v)
					{
						$authors[] = sprintf("<a href='/index.$ext?section=%s'>%s</a>",$v,$k);
					} 
					else 
					{
						$authors[] = sprintf("<a href='%s'>%s</a>",DOC_LINK_DEFAULT_LINK,$k);
					};
				}
				else
				{
					$authors[] = $k;
				}
			}; // while
			$author = join(", ",$authors);
			$this->vars(array("author" => $author));
			$ab = $this->parse("ablock");
		} 
		else 
		{
			$this->vars(array("author" => $doc["author"]));
			$ab = $this->parse("ablock");
		};
	};

		$points = $doc["num_ratings"] == 0 ? 3 : $doc["rating"] / $doc["num_ratings"];
		$pts = "";
		for ($i=0; $i < $points; $i++)
			$pts.=$this->parse("RATE");

		$this->vars(array("num_comments" =>  $nc,"docid" => $docid));

		$fr = "";
		if ($doc["is_forum"])
		{
			$fr = $this->parse("FORUM_ADD");
		}

		$langs = "";
		classload("languages");
		$l = new languages;
		$larr = $l->listall();
		reset($larr);
		while (list(,$v) = each($larr))
		{
			$this->vars(array("lang_id" => $v["id"], "lang_name" => $v["name"]));
			if ($GLOBALS["lang_id"] == $v["id"])
				$langs.=$this->parse("SEL_LANG");
			else
				$langs.=$this->parse("LANG");
		}

		$lc = "";
		if ($doc["lead_comments"]==1)
			$lc = $this->parse("lead_comments");

		if ($doc["parent"])
		{
			$this->db_query("SELECT * FROM menu WHERE id = ".$doc["parent"]);
			$mn = $this->db_next();
		}

		if (!isset($this->doc_count))
		{
			$this->doc_count = 0;
		}

		$title = $doc["title"];
		$this->vars(array(
			"title"	=> $title,
			"text"  => $doc["content"],
			"secid" => isset($secID) ? $secID : 0,
			"docid" => $docid,
			"ablock"   => isset($ab) ? $ab : 0,
			"pblock"   => isset($pb) ? $pb : 0,
			"date"     => $this->time2date(time(),2),
			"section"  => $GLOBALS["section"],
			"lead_comments" => $lc,
			"modified"	=> $this->time2date($doc["modified"],2),
			"channel"		=> $doc["channel"],
			"tm"				=> $doc["tm"],
			"link_text"	=> $doc["link_text"],
			"subtitle"	=> $doc["subtitle"],
			"RATE"			=> $pts,
			"FORUM_ADD" => $fr,
			"LANG" => $langs,
			"SEL_LANG" => "",
			"menu_addr"	=> $mn["link"],
			"lead_br"	=> $doc["lead"] != "" ? "<br>" : "",
			"doc_count" => $this->doc_count++,
			"title_target" => $doc["newwindow"] ? "target=\"_blank\"" : "",
			"title_link"  => ($doc["link_text"] != "" ? $doc["link_text"] : (isset($GLOBALS["doc_file"]) ? $GLOBALS["doc_file"] :  "index.".$ext."/")."section=".$docid),
		));

		if ($leadonly > -1 && $doc["title_clickable"])
		{
			$this->vars(array("TITLE_LINK_BEGIN" => $this->parse("TITLE_LINK_BEGIN"), "TITLE_LINK_END" => $this->parse("TITLE_LINK_END")));
		}

		$this->vars(array(
			"SHOW_TITLE" 	=> ($doc["show_title"] == 1) ? $this->parse("SHOW_TITLE") : "",
			"EDIT" 		=> ($this->prog_acl("view",PRG_MENUEDIT)) ? $this->parse("EDIT") : "",
			"SHOW_MODIFIED" => ($doc["show_modified"]) ? $this->parse("SHOW_MODIFIED") : "",
			"COPYRIGHT"	=> ($doc["copyright"]) ? $this->parse("COPYRIGHT") : "",
			));
		
		// keeleseosed
		if ($this->is_template("LANG_BRO"))
		{
			$lab = unserialize($doc["lang_brothers"]);
			$langs = "";
			classload("languages");
			$l = new languages;
			$larr = $l->listall();
			reset($larr);
			while (list(,$v) = each($larr))
			{
				if ($lab[$v["id"]])
				{
					$this->vars(array("lang_id" => $v["id"], "lang_name" => $v["name"],"section" => $lab[$v["id"]]));
					if ($GLOBALS["lang_id"] == $v["id"])
					{
						$langs.=$this->parse("SLANG_BRO");
					}
					else
					{
						$langs.=$this->parse("LANG_BRO");
						// tshekime et kui sellel dokul pole m22ratud muutmise kuup2eva, siis vaatame kas m6nel seotud dokul on
						// ja kui on, siis kasutame seda
						if ($tm == "")
						{
							$tm = $this->db_fetch_field("SELECT tm FROM documents WHERE docid = ".$lab[$v["id"]],"tm");
						};
					};
				};
			}
			
			$this->vars(array("LANG_BRO" => $langs));
		}; // keeleseosed
		
		// I kinda hate this part, mime registry should really be somewhere else
		// failide ikoonid kui on template olemas, namely www.stat.ee jaox
		$aliases = $this->get_aliases_for($docid);
 		if ($this->is_template("FILE"))
		{
			$ftypearr = array(
				"application/pdf" => "pdf",
				"text/richtext" => "rtf",
				"application/msword" => "doc",
				"application/vnd.ms-excel" => "xls",
				"text/html" => "html",
				"image/gif" => "gif",
      			);
			reset($aliases);
			while (list(,$ar) = each($aliases))
			{
				if ($ar["type"] == CL_FILE)
				{
					$this->db_query("SELECT objects.name as name, files.type AS type,objects.comment as comment FROM objects LEFT JOIN files ON files.id = objects.oid WHERE objects.oid = ".$ar["target"]);
					$fif = $this->db_next();
 
					$im = $ftypearr[$fif["type"]];
					if ($im != "" && $im != "html")
					{
						$this->vars(array(
							"url" => $GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$ar["target"]."/".urlencode($fif["name"]),
							"im" => $im == "" ? "fil" : $im
						));
 
						$fff.=$this->parse("FILE");
					}
				}
			}
			$this->vars(array("FILE" => $fff));
		}
 

		$retval = $this->parse();
		return $retval;
	}

	function get_relations($docid) 
	{
		// kysiti votmesonu dokumendi kohta
		$doc = $this->fetch($docid);
		$keywords = split(",",$doc["keywords"]);
		if (is_array($keywords)) 
		{
			$qparts = array();
			while(list($k,$v) = each($keywords)) 
			{
				$v = trim($v);
				$qparts[] = " keywords LIKE '%$v%' ";
			};
			if (is_array($qparts) && (sizeof($qparts) > 0)) 
			{
				$q = "SELECT docid,title,keywords FROM documents WHERE ". 
					join(" OR ",$qparts);
				$this->db_query($q);
				$retval = array();
				while($row = $this->db_next()) 
				{
					$retval[$row["docid"]] = $row;
				};
			};
		};
		return $retval;
	}
	
	// kysib "sarnaseid" dokusid mingi välja kaudu
	// XXX
	function get_relations_by_field($params) 
	{
		$field = $params["field"]; // millisest väljast otsida
		$keywords = split(",",$params["keywords"]); // mida sellest väljast otsida,
																		// comma separated listi
		$section = $params["section"]; // millisest sektsioonist otsida
		// kui me midagi ei otsi, siis pole siin midagi teha ka enam. GET OUT!
		if (!is_array($keywords)) 
		{
			return false;
		} 
		else 
		{
			// moodustame päringu dokude saamiseks, mis vastavad meile
			// vajalikule tingimusele
			$retval = array();
			while(list($k,$v) = each($keywords)) 
			{
				$v = trim($v);
				$q = "SELECT docid FROM documents
							LEFT JOIN objects ON (documents.docid = objects.oid)
							WHERE parent = $section AND $field LIKE '$v'";
				$retval[$v] = $this->db_fetch_field($q,"docid");
			}; // eow
			return $retval;
		}; // eoi
	}

	function save($data) 
	{
		// docid on ainuke parameeter, mis *peab* olema kaasa antud
		// ja siis veel vähemalt yx teine parameeter mida muuta
		$this->quote($data);
		$user = $data["user"];
		if ($data["content"]) {$data["content"] = trim($data["content"]);};
		if ($data["lead"]) {$data["lead"] = trim($data["lead"]);};
		if ($data["cite"]) {$data["cite"] = trim($data["cite"]);};
		if ($data["keywords"])
		{
			classload("keywords");
			$kw = new keywords;
			$kw->update_keywords(array(
						"keywords" => $data["keywords"],
						"oid" => $data["id"],
			));
		};

		if ($data["status"] == 0)
		{
			$data["status"] = 1;
		}

		$id = $data["id"];
		$olddoc = $this->fetch($id);
		$q_parts = array();
		$changed_fields = array();

		reset($this->knownfields);
		// tsykkel yle koigi "tuntud" v2ljade, salvestame ainult 
		// nende sisu, mida vormis kasutati
		while(list($fcap,$fname) = each($this->knownfields)) 
		{
			if (isset($data[$fname]) || $fname=="esilehel" || $fname=="esileht_yleval" || $fname=="esilehel_uudis" || $fname=="is_forum" || $fname=="lead_comments" || $fname=="showlead" || $fname=="yleval_paremal" || $fname == "show_title" || $fname=="copyright" || $fname == "show_modified" || $fname == "title_clickable" || $fname == "newwindow" || $fname == "no_right_pane" || $fname == "no_left_pane" || $fname == "no_search")  
			{
				$q_parts[] = "$fname = '$data[$fname]'";
				// paneme väljade nimed ka kirja, et formeerida logi
				// jaoks natuke informatiivsem teade
				$changed_fields[] = $fcap;
			};
		};
		
		// siin paneme muutmise kuup2eva ka kirja. trikk on sellest et dokul on v2li "tm", kuhu saab k2sici kirjutada muutmise kuup2eva. 
		// vot. nyt kui seal on midagi, siis teeme sellest timestampi ja paneme selle documents::modified sisse kirja. 
		// kui tm on aga tyhi, siis paneme documents::tm sisse praeguse kellaaja.
		$modified = time();
		if ($data["tm"] != "")
		{
			list($day,$mon,$year) = explode("/",$row["tm"]);

			$ts = mktime(0,0,0,$mon,$day,$year);
			if ($ts)
			{
				$modified = $ts;
			}
			else
			{
				// 2kki on punktidega eraldatud
				list($day,$mon,$year) = explode(".",$row["tm"]);
				$ts = mktime(0,0,0,$mon,$day,$year);
				if ($ts)
				{
					$modified = $ts;
				}
				else
				{
					// 2kki on hoopis - 'ga eraldatud?
					list($day,$mon,$year) = explode("-",$row["tm"]);
					$ts = mktime(0,0,0,$mon,$day,$year);
				}
			}
		}
		$q_parts[] = "modified = $modified";

		// see paneb siis paringu kokku. Whee.
		$q = "UPDATE documents SET " . join(",\n",$q_parts) . " WHERE docid = '$id'"; 
		$this->db_query($q);
		
		// siia moodustame objektitabeli päringu osad
		$oq_parts = array();

		$obj_known_fields = array("name","visible","status","parent");

		// seda on järgneva päringu koostamiseks vaja, sest objektitabelis pole "title"
		// välja. On "name"
		if ($data["title"]) 
		{
			$data["name"] = $data["title"];
		};

		$oq_parts["oid"] = $id;

		while(list($fcap,$fname) = each($obj_known_fields)) 
		{
			if ($data[$fname]) 
			{
				$oq_parts[$fname] = $data[$fname];
			};
		};

		$this->upd_object($oq_parts);
		// logime aktsioone
		$this->_log("document","muutis dokumenti <a href='".$GLOBALS["baseurl"]."/automatweb/".$this->mk_orb("change", array("id" => $id))."'>'".$data["title"]."'</a>");

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function select_alias($docid, $entry_id)
	{
		$this->read_template("alias_type.tpl");

		$ob = $this->get_object($entry_id);
		
		$karr = array();
		$this->db_query("SELECT * FROM objects WHERE parent = ".$ob["parent"]." AND class_id = 12 AND objects.status != 0");
		while ($row = $this->db_next())
			$karr[$row["oid"]] = $row["name"];

		$this->vars(array("docid" => $docid, "alias" => $entry_id, "op_sel" => $this->picker("", $karr),"form_id" => $ob["parent"]));
		return $this->parse();
	}

	function send_link()
	{
		global $from_name, $from, $baseurl, $ext, $section, $copy,$to_name, $to,$SITE_ID;

		if ($SITE_ID == 5)
		{
			$text = "$from_name ($from) soovitab teil vaadata Pere ja Kodu saidile ".$GLOBALS["baseurl"].",\ntäpsemalt linki ".$GLOBALS["baseurl"]."/index.$ext?section=$section\n\n$from_name kommentaar lingile: $comment\n";

			if ($copy != "")
				$bcc = "\nCc: $copy ";

			mail("\"$to_name\" <".$to.">","Artikkel saidilt ".$GLOBALS["baseurl"],$text,"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");
		}
		else
		{
			$text = "$from_name ($from) soovitab teil vaadata Nädala saidile www.nadal.ee,\ntäpsemalt linki http://www.nadal.ee/index.$ext?section=$section\n\n$from_name kommentaar lingile: $comment\n";

			if ($copy != "")
				$bcc = "\nCc: $copy ";

			mail("\"$to_name\" <".$to.">",LC_DOCUMENT_ART_FROM_NADAL,$text,"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");
		}
	}

	function add_rating($docid, $hinne)
	{
		$hinne = $hinne+0;
		if ($hinne > 0)
		{
			$this->db_query("UPDATE documents SET rating=rating+$hinne , num_ratings=num_ratings+1 WHERE docid = $docid");
		}
	}

	function telekava_doc($content)
	{
		$paevad = array("0" => "#telekava_neljapaev#", "1" => "#telekava_reede#", "2" => "#telekava_laupaev#", "3" => "#telekava_pyhapaev#", "4" => "#telekava_esmaspaev#", "5" => "#telekava_teisipaev#", "6" => "#telekava_kolmapaev#");
		reset($paevad);
		while (list($num, $v) = each($paevad))
		{
			if (strpos($content,$v) === false)
			{
				continue;
			}
			else
			{
				break;
			}
		}

		// arvutame v2lja, et millal oli eelmine neljap2ev
		$sub_arr = array("0" => "3", "1" => "4", "2" => "5", "3" => "6", "4" => "0", "5" => "1", "6" => "2");
		$date = mktime(0,0,0,date("m"),date("d"),date("Y"));

		$d_begin = $date - $sub_arr[date("w")]*24*3600;
		$rdate = $d_begin+$num*24*3600;

		$t = new tvkavad;
		return $t->kanalid_list($rdate);
	}

	function brother($id)
	{
		$this->read_template("brother.tpl");
		$sar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($arow = $this->db_next())
		{
			$sar[$arow["parent"]] = $arow["parent"];
		}

		classload("objects");
		$ob = new db_objects;

		$this->vars(array("docid" => $id,"sections"		=> $this->multiple_option_list($sar,$ob->get_list())));
		return $this->parse();
	}

	function submit_brother($arr)
	{
		extract($arr);

		$obj = $this->get_object($docid);

		$sar = array(); $oidar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $docid AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($row = $this->db_next())
		{
			$sar[$row["parent"]] = $row["parent"];
			$oidar[$row["parent"]] = $row["oid"];
		}

		$not_changed = array();
		$added = array();
		if (is_array($sections))
		{
			reset($sections);
			$a = array();
			while (list(,$v) = each($sections))
			{
				if ($sar[$v])
					$not_changed[$v] = $v;
				else
					$added[$v] = $v;
				$a[$v]=$v;
			}
		}
		$deleted = array();
		reset($sar);
		while (list($oid,) = each($sar))
		{
			if (!$a[$oid])
				$deleted[$oid] = $oid;
		}

		reset($deleted);
		while (list($oid,) = each($deleted))
		{
			$this->delete_object($oidar[$oid]);
		}
		reset($added);
		while(list($oid,) = each($added))
		{
			if ($oid != $id)	// no recursing , please
			{
				$noid = $this->new_object(array("parent" => $oid,"class_id" => CL_BROTHER_DOCUMENT,"status" => 1,"brother_of" => $docid,"name" => $obj["name"],"comment" => $obj["comment"]));
			}
		}

		return $obj["parent"];
	}

	function add($arr)
	{
		extract($arr);

		global $per_oid,$period;

		$this->mk_path($parent,LC_DOCUMENT_ADD_DOC);
		$this->tpl_init("automatweb/documents");
		$this->read_template("nadd.tpl");
		$par_data = $this->get_object($parent);
		$section = $par_data["name"];
		if ($period > 0) 
		{
			classload("periods");
			$periods = new db_periods($per_oid);
			$pdata = $periods->get($period);
			$pername = $pdata["description"];			
		} else {
			$period = 0;
			$pername = "staatiline";
		};
		$this->vars(array("section" => $section,
				  "period"  => $period,
				  "parent"  => $parent,
				  "pername" => $pername,
					"reforb"	=> $this->mk_reforb("submit_add", array("parent" => $parent, "period" => $period, "user" => $user))));
		return $this->parse();
	}

	function submit_add($arr)
	{
		$this->quote(&$arr);
		extract($arr);
		if ($period) 
		{
			$data["class_id"] = CL_PERIODIC_SECTION;
			$data["period"] = $period;
		} else {
			$data["class_id"] = CL_DOCUMENT;
		};
		$data["name"] = $name;
		$data["parent"] = $parent;
		$data["status"] = 1;
		$o_data = $this->get_object($parent);
		$this->period = $data["period"];
		$lid = $this->new_object($data);
		$this->upd_object(array("oid" => $lid, "brother_of" => $lid));	// dokument on enda vend ka
		// me peame selle dokumendi ka menyys registreerima
		if ($period) {
			$q = "INSERT INTO menu (id,type,periodic) VALUES ('$lid','99','1')";
		} else {
			$q = "INSERT INTO menu (id,type) VALUES ('$lid','99')";
		};
		$defaults = $this->fetch($parent);
		$flist = array();
		$vlist = array();
		while(list($k,$v) = each($this->knownfields)) {
			$flist[] = $v;
			switch($v)
			{
				case "title":
					$defaults[$v] = ($name) ? $name : "";
					$vlist[] = "'" . $defaults[$v] . "'";
					break;
				case "copyright":
					$vlist[] = "'1'";
					break;
				case "show_title":
					$vlist[] = "'1'";
					break;
				case "show_modified":
					$vlist[] = "'1'";
					break;
				case "title_clickable":
					$vlist[] = "'1'";
					break;
				default:
					$vlist[] = "'" . $defaults[$v] . "'";
			};
		};
		if (is_array($flist) && (sizeof($flist) > 0)) {
			$part1 = "," . join(",",$flist);
			$part2 = "," . join(",",$vlist);
		} else {
			$part1 = "";
			$part2 = "";
		};
		$q = "INSERT INTO documents (docid $part1) VALUES ('$lid' $part2)";
		$this->db_query($q);

		$this->id = $lid;
		return $this->mk_my_orb("change", array("id" => $lid));
	}

	function change($arr)
	{
		global $baseurl;
		if (!$this->prog_acl("view",PRG_MENUEDIT))
		{
			header("Location: $baseurl");
			exit;
		}
		extract($arr);

		$oob = $this->get_object($id);
		if ($oob["class_id"] == CL_BROTHER_DOCUMENT)
		{
			$id = $oob["brother_of"];
		}

		$document = $this->fetch($id);
		$this->mk_path($document["parent"],LC_DOCUMENT_CHANGE_DOC,$document["period"]);
		
		// kui class_id on 1, siis jarelikult me muudame
		// mingi sektsiooni defaulte
		if ($document["class_id"] == 1) 
		{
			$mcap = LC_DOCUMENT_SEKTS_DEF;
		} 
		else 
		{
			$mcap = LC_DOCUMENT_DOC;
		};

		$_tpl = $this->get_edit_template($document["parent"]);
		$this->read_template($_tpl);

		// keelte valimise asjad
		if ($this->is_template("DOC_BROS"))
		{
			$lang_brothers = unserialize($document["lang_brothers"]);
			classload("languages");
			$t = new languages;
			$ar = $t->listall();
			reset($ar);
			while (list(,$v) = each($ar))
			{
				if ($v["id"] != $document["lang_id"])
				{
					if ($lang_brothers[$v["id"]])
					{
						$this->db_query("SELECT documents.title,documents.docid FROM documents WHERE documents.docid = ".$lang_brothers[$v["id"]]);
						$row = $this->db_next();
						$this->vars(array("lang_name" => $v["name"], 
															"chbrourl"	=> $this->mk_my_orb("change", array("id" => $row["docid"])),
															"bro_name"	=> $row["title"]));
						$db.=$this->parse("DOC_BROS");
					}
				}
			}
			$this->vars(array("DOC_BROS" => $db));
		}

		$GLOBALS["lang_id"] = $document["lang_id"];
		$alilist = array();
		$jrk = array("1" => "1", "2" => "2", "3" => "3",  "4" => "4", "5"  => "5",
								 "6" => "6", "7" => "7", "8" => "8",  "9" => "9", "10" => "10");
		$addfile = $this->mk_my_orb("new",array("id" => $id, "parent" => $document["parent"]),"file");
		$previewlink = "";

		$this->vars(array(
			"addtable"	=> $this->mk_my_orb("add_doc", array("id" => $id, "parent" => $document["parent"]),"table"),
			"addfile"		=> $addfile,
			"add_img"	=> $this->mk_my_orb("new", array("parent" => $id),"images"),
			"addlink"	=> $this->mk_my_orb("new", array("docid" => $id, "parent" => $document["parent"]),"links"),
			"addform"		=> $this->mk_my_orb("new", array("parent" => $document["parent"],"alias_doc" => $id),"form"),
			"addgb"			=> $this->mk_my_orb("new", array("parent" => $document["parent"], "docid" => $id), "guestbook"),
			"addgraph"	=> $this->mk_my_orb("new", array("parent" => $document["parent"],"alias_doc" => $id),"graph"),
			"addgallery"	=> $this->mk_my_orb("new", array("parent" => $document["parent"],"alias_doc" => $id),"gallery"),
			"addchain" => $this->mk_my_orb("new", array("parent" => $document["parent"],"alias_doc" => $id),"form_chain")
		));
		// see sordib ja teeb aliaste nimekirja. ja see ei meeldi mulle. but what can ye do, eh?
		$this->mk_alias_lists($id);
		classload("keywords");
		$kw = new keywords();
		$keywords = $kw->get_keywords(array(
									"oid" => $id,
		));

		classload("languages");
		$t = new languages;
    $this->vars(array("title" => str_replace("\"","&quot;",$document["title"]),
											"jrk1"  => $this->picker($document["jrk1"],$jrk),
										  "jrk2"  => $this->picker($document["jrk2"],$jrk),
										  "jrk3"  => $this->picker($document["jrk3"],$jrk),
										  "allparemal" => ($document["allparemal"] == 1) ? "checked" : "",
										  "esilehel" => ($document["esilehel"] == 1) ? "checked" : "",
										  "showlead" => ($document["showlead"] == 1) ? "checked" : "",
										  "esilehel_uudis" => ($document["esilehel_uudis"] == 1) ? "checked" : "",
											"yleval_paremal" => ($document["yleval_paremal"] == 1) ? "checked" : "",
											"esileht_yleval" => ($document["esileht_yleval"] == 1) ? "checked" : "",
											"show_modified" => ($document["show_modified"] == 1) ? "checked" : "",
											"is_forum" => ($document["is_forum"] == 1) ? "checked" : "",
											"copyright" => ($document["copyright"] == 1) ? "checked" : "",
											"lead_comments" => ($document["lead_comments"] == 1) ? "checked" : "",
											"show_title" => ($document["show_title"] == 1) ? "checked" : "",
											"keywords" => $keywords,
											"title_clickable" => checked($document["title_clickable"]),
											"newwindow" => checked($document["newwindow"]),
											"author"  => $document["author"],
											"photos"  => $document["photos"],
											"periood"  => ($document["period"] > 0) ? $pdata["description"] : "staatiline",
											"status"  => $this->option_list($document["status"],array("2" => "Jah","1" => "Ei")),
											"visible" => $this->option_list($document["visible"],array("1" => "Jah","0" => "Ei")),
											"keywords"  => $document["keywords"],
										  "lead"    => str_replace("\"","&quot;",trim($document["lead"])),
											"content" => str_replace("\"","&quot;",trim($document["content"])),
											"channel"	=> trim($document["channel"]),
											"nobreaks"	=> $document["nobreaks"],
											"tm"			=> trim($document["tm"]),
											"subtitle"			=> trim($document["subtitle"]),
											"link_text" => trim($document["link_text"]),
											"reforb"	=> $this->mk_reforb("save", array("id" => $id)),
											"id" => $id,
											"docid" => $id,
											"previewlink" => $previewlink,
											"weburl" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$document["docid"],
											"lburl"		=> $this->mk_my_orb("sellang", array("id" => $id)),
											"long_title"	=> $document["long_title"],
											"menurl"		=> $this->mk_my_orb("sel_menus",array("id" => $id)),
											"preview"	=> $this->mk_my_orb("preview", array("id" => $id)),
											"cstatus"	=> checked($document["status"] == 2),
											"no_search" => checked($document["no_search"]),
											"no_left_pane" => checked($document["no_left_pane"]),
											"no_right_pane" => checked($document["no_right_pane"]),
											"charset" => $t->get_charset(),
											));


		// detect browser and if it's compatible, use WYSIWYG editor
		global $HTTP_USER_AGENT;
		if (!(strpos($HTTP_USER_AGENT,"MSIE") === false))
		{
			// IE
			$brows = $this->parse("IE");
		}
		else
		{
			// other
			$brows = $this->parse("NOT_IE");
		}
		$this->vars(array("IE" => $brows, "NOT_IE" => ""));
		return $this->parse();
	}

	function mk_alias_lists($id)
	{
		global $user;	// this is 1 if we come from the site side. we will kick ass there.

		$this->vars(array("url" => $this->mk_my_orb("change",array("id" => $id))));

		global $ext,$s_pic_sortby,$s_pic_order,$baseurl,$pic_sortby,$pic_order,
					 $link_sortby,$link_order,$s_link_sortby,$s_link_order,
					 $form_sortby,$form_order,$s_form_sortby,$s_form_order,
					 $file_sortby,$file_order,$s_file_sortby,$s_file_order,
					 $graph_sortby,$graph_order,$s_graph_sortby,$s_graph_order,
					 $gallery_sortby,$gallery_order,$s_gallery_sortby,$s_gallery_order,
					 $table_sortby,$table_order,$s_table_sortby,$s_table_order,
					 $chain_sortby,$chain_order,$s_chain_sortby,$s_chain_order;
		if ($pic_sortby)
		{
			$s_pic_sortby = $pic_sortby;
		}
		if ($pic_order)
		{
			$s_pic_order = $pic_order;
		}
		if ($link_sortby)
		{
			$s_link_sortby = $link_sortby;
		}
		if ($link_order)
		{
			$s_link_order = $link_order;
		}
		if ($form_sortby)
		{
			$s_form_sortby = $form_sortby;
		}
		if ($form_order)
		{
			$s_form_order = $form_order;
		}
		if ($chain_sortby)
		{
			$s_chain_sortby = $chain_sortby;
		}
		if ($chain_order)
		{
			$s_chain_order = $chain_order;
		}
		if ($table_sortby)
		{
			$s_table_sortby = $table_sortby;
		}
		if ($table_order)
		{
			$s_table_order = $table_order;
		}
		if ($file_sortby)
		{
			$s_file_sortby = $file_sortby;
		}
		if ($file_order)
		{
			$s_file_order = $file_order;
		}
		if ($graph_sortby)
		{
			$s_graph_sortby = $graph_sortby;
		}
		if ($graph_order)
		{
			$s_graph_order = $graph_order;
		}
		if ($gallery_sortby)
		{
			$s_gallery_sortby = $gallery_sortby;
		}
		if ($gallery_order)
		{
			$s_gallery_order = $gallery_order;
		}

		$upimg = "<img src='".$baseurl."/images/up.gif' border=0>";
		$downimg = "<img src='".$baseurl."/images/down.gif' border=0>";

		$img = new db_images;
		$img->list_by_object($id,0,$s_pic_sortby,$s_pic_order);
		$imglist = array();
		$images_count = 0;
		while($row = $img->db_next()) 
		{
			$this->vars(array("name"				=> $row["name"], 
												"modified"		=> $this->time2date($row["modified"],2), 
												"modifiedby"	=> $row["modifiedby"],
												"alias"				=> "#p".$row["idx"]."#",
												"comment"			=> $row["comment"],
												"id"					=> $row["oid"],
												"pic_order"		=> $pic_order == "ASC" ? "DESC" : "ASC",
												"ch_img"			=> $this->mk_my_orb("change", array("id" => $row["oid"]),"images"),
												"del_img"			=> $this->mk_my_orb("delete", array("id" => $row["oid"]),"images"),
												"pic_name_img"		=> $s_pic_sortby == "name" ? ($s_pic_order == "DESC" ? $upimg : $downimg ): "",
												"pic_comment_img"	=> $s_pic_sortby == "comment" ? ($s_pic_order == "DESC" ? $upimg : $downimg ): "",
												"pic_alias_img"		=> $s_pic_sortby == "alias" ? ($s_pic_order == "DESC" ? $upimg : $downimg ): "",
												"pic_modifiedby_img"		=> $s_pic_sortby == "modifiedby" ? ($s_pic_order == "DESC" ? $upimg : $downimg ): "",
												"pic_modified_img"		=> $s_pic_sortby == "modified" ? ($s_pic_order == "DESC" ? $upimg : $downimg ): ""
												));
			$l.=$this->parse("IMG_LINE");
			$imglist[] = sprintf("<a href='".$this->mk_my_orb("change", array("id" => $row["oid"]),"images")."'>#p%d#</a>",
							$row["idx"]);
			$images_count++;
		};
		$this->vars(array("IMG_LINE" => $l,"imglist" => join(",",$imglist)));
		if ($images_count > 0)
		{
			$hi = $this->parse("HAS_IMAGES");
		}
		else
		{
			$hi = $this->parse("NO_IMAGES");
		}
		$this->vars(array("HAS_IMAGES" => $hi, "NO_IMAGES" => ""));

		if (!$s_link_sortby)
		{
			$s_link_sortby = "oid";
		};
		$_sby = "extlinks." . $s_link_sortby;
		$links = $this->get_aliases_for($id,CL_EXTLINK,$_sby, $s_link_order,array("extlinks" => "extlinks.id = objects.oid"));
		$lc = 0;
		$linklist = array();
		reset($links);
		while (list(,$v) = each($links))
		{
			$lc++;
			$this->vars(array("name"								=> $v["name"], 
												"modified"						=> $this->time2date($v["modified"],2), 
												"modifiedby"					=> $v["modifiedby"],
												"address"							=> $v["url"],
												"alias"								=> "#l".$lc."#",
												"id"									=> $v["id"],
												"comment"							=> $v["descript"],
												"ch_link"							=> $this->mk_my_orb("change", array("id" => $v["id"],"docid" => $id),"links"),
												"del_link"						=> $this->mk_my_orb("delete", array("id" => $v["id"],"parent" => $id),"links"),
												"link_order"					=> $s_link_order == "ASC" ? "DESC" : "ASC",
												"link_name_img"				=> $s_link_sortby == "name" ?				($s_link_order == "DESC" ? $upimg : $downimg ): "",
												"link_comment_img"		=> $s_link_sortby == "comment" ?		($s_link_order == "DESC" ? $upimg : $downimg ): "",
												"link_modifiedby_img"	=> $s_link_sortby == "modifiedby" ? ($s_link_order == "DESC" ? $upimg : $downimg ): "",
												"link_modified_img"		=> $s_link_sortby == "modified" ?		($s_link_order == "DESC" ? $upimg : $downimg ): ""));
			$ll.=$this->parse("LINK_LINE");
			$linklist[] = "[<a href=\"".$this->mk_my_orb("change", array("id" => $v["id"],"docid" => $id),"links")."\">#l".$lc."#</a> - $v[name]]";
		}
		$this->vars(array("LINK_LINE" => $ll,"linklist" => join(",",$linklist)));
		if ($lc > 0)
		{
			$hl = $this->parse("HAS_LINKS");
		}
		else
		{
			$hl = $this->parse("NO_LINKS");
		}
		$this->vars(array("HAS_LINKS" => $hl, "NO_LINKS" => ""));

		$tables = $this->get_aliases_for($id,CL_TABLE,$s_table_sortby, $s_table_order);
		$tc = 0;
		$tblist = array();
		reset($tables);
		while (list(,$v) = each($tables))
		{
			$tc++;
			$this->vars(array("name"								=> $v["name"], 
												"modified"						=> $this->time2date($v["modified"],2), 
												"modifiedby"					=> $v["modifiedby"],
												"alias"								=> "#t".$tc."#","comment" => $v["comment"],
												"id"									=> $v["id"],
												"ch_table"						=> $this->mk_my_orb("change", array("id" => $v["id"]),"table"),
												"del_table"						=> $this->mk_my_orb("delete", array("id" => $v["id"]),"table"),
												"table_order"					=> $s_table_order == "ASC" ? "DESC" : "ASC",
												"table_name_img"			=> $s_table_sortby == "name" ?				($s_table_order == "DESC" ? $upimg : $downimg ): "",
												"table_comment_img"		=> $s_table_sortby == "comment" ?		($s_table_order == "DESC" ? $upimg : $downimg ): "",
												"table_modifiedby_img"=> $s_table_sortby == "modifiedby" ? ($s_table_order == "DESC" ? $upimg : $downimg ): "",
												"table_modified_img"	=> $s_table_sortby == "modified" ?		($s_table_order == "DESC" ? $upimg : $downimg ): ""));
			$tl.=$this->parse("TABLE_LINE");
			$tblist[] = sprintf("<a href='".$this->mk_my_orb("change", array("id" => $v["id"], "parent" => $v["parent"]),"table")."'>#t%d#</a> <i>(Nimi: $v[name])</i>",$tc);
		}
		$this->vars(array("TABLE_LINE" => $tl,"tblist" => join(",",$tblist)));
		if ($tc > 0)
		{
			$ht = $this->parse("HAS_TABLES");
		}
		else
		{
			$ht = $this->parse("NO_TABLES");
		}
		$this->vars(array("HAS_TABLES" => $ht, "NO_TABLES" => ""));

		$forms = $this->get_aliases_for($id,CL_FORM,$s_form_sortby, $s_form_order);
		$fc = 0;
		$formlist = array();
		reset($forms);
		while (list(,$v) = each($forms))
		{
			$fc++;
			$this->vars(array("name"								=> $v["name"], 
												"modified"						=> $this->time2date($v["modified"],2), 
												"modifiedby"					=> $v["modifiedby"],
												"alias"								=> "#f".$fc."#","comment" => $v["comment"],
												"id"									=> $v["id"],
												"ch_form"							=> $this->mk_my_orb("change", array("id" => $v["id"]),"form"),
												"form_order"					=> $s_form_order == "ASC" ? "DESC" : "ASC",
												"form_name_img"				=> $s_form_sortby == "name" ?				($s_form_order == "DESC" ? $upimg : $downimg ): "",
												"form_comment_img"		=> $s_form_sortby == "comment" ?		($s_form_order == "DESC" ? $upimg : $downimg ): "",
												"form_modifiedby_img"	=> $s_form_sortby == "modifiedby" ? ($s_form_order == "DESC" ? $upimg : $downimg ): "",
												"form_modified_img"		=> $s_form_sortby == "modified" ?		($s_form_order == "DESC" ? $upimg : $downimg ): ""));
			$ffl.=$this->parse("FORM_LINE");
			$formlist[] = sprintf("<a href='".$this->mk_my_orb("change", array("id" => $v["id"], "parent" => $v["parent"]),"form")."'>#f%d#</a> <i>(Nimi: $v[name])</i>",$fc);
		}
		$this->vars(array("FORM_LINE" => $ffl,"formlist" => join(",",$formlist)));
		if ($fc > 0)
		{
			$hf = $this->parse("HAS_FORMS");
		}
		else
		{
			$hf = $this->parse("NO_FORMS");
		}
		$this->vars(array("HAS_FORMS" => $hf, "NO_FORMS"));

		$ffl = "";
		$chains = $this->get_aliases_for($id,CL_FORM_CHAIN,$s_chain_sortby, $s_chain_order);
		$cc = 0;
		$chainlist = array();
		reset($chains);
		while (list(,$v) = each($chains))
		{
			$cc++;
			$this->vars(array("name"								=> $v["name"], 
												"modified"						=> $this->time2date($v["modified"],2), 
												"modifiedby"					=> $v["modifiedby"],
												"alias"								=> "#c".$cc."#","comment" => $v["comment"],
												"id"									=> $v["id"],
												"ch_chain"							=> $this->mk_my_orb("change", array("id" => $v["id"]),"form_chain"),
												"chain_order"					=> $s_chain_order == "ASC" ? "DESC" : "ASC",
												"chain_name_img"				=> $s_chain_sortby == "name" ?	($s_chain_order == "DESC" ? $upimg : $downimg ): "",
												"chain_comment_img"		=> $s_chain_sortby == "comment" ?		($s_chain_order == "DESC" ? $upimg : $downimg ): "",
												"chain_modifiedby_img"	=> $s_chain_sortby == "modifiedby" ? ($s_chain_order == "DESC" ? $upimg : $downimg ): "",
												"chain_modified_img"		=> $s_chain_sortby == "modified" ?	($s_chain_order == "DESC" ? $upimg : $downimg ): ""));
			$ffl.=$this->parse("CHAIN_LINE");
			$chainlist[] = sprintf("<a href='".$this->mk_my_orb("change", array("id" => $v["id"], "parent" => $v["parent"]),"form_chain")."'>#c%d#</a> <i>(Nimi: $v[name])</i>",$cc);
		}
		$this->vars(array("CHAIN_LINE" => $ffl,"chainlist" => join(",",$chainlist)));
		if ($cc > 0)
		{
			$hc = $this->parse("HAS_CHAINS");
		}
		else
		{
			$hc = $this->parse("NO_CHAINS");
		}
		$this->vars(array("HAS_CHAINS" => $hc, "NO_CHAINS"));


		$files = $this->get_aliases_for($id,CL_FILE,$s_file_sortby, $s_file_order);
		$fic = 0;
		$filelist = array();
		reset($files);
		while (list(,$v) = each($files))
		{
			$fic++;
			$this->vars(array("name"								=> $v["name"], 
												"modified"						=> $this->time2date($v["modified"],2), 
												"modifiedby"					=> $v["modifiedby"],
												"alias"								=> "#v".$fic."#","comment" => $v["comment"],
												"id"									=> $v["id"],
												"ch_file"							=> $this->mk_my_orb("change", array("id" => $v["id"], "doc" => $id),"file"),
												"file_order"					=> $s_file_order == "ASC" ? "DESC" : "ASC",
												"file_name_img"				=> $s_file_sortby == "name" ?				($s_file_order == "DESC" ? $upimg : $downimg ): "",
												"file_comment_img"		=> $s_file_sortby == "comment" ?		($s_file_order == "DESC" ? $upimg : $downimg ): "",
												"file_modifiedby_img"	=> $s_file_sortby == "modifiedby" ? ($s_file_order == "DESC" ? $upimg : $downimg ): "",
												"file_modified_img"		=> $s_file_sortby == "modified" ?		($s_file_order == "DESC" ? $upimg : $downimg ): ""));
			$fl.=$this->parse("FILE_LINE");
			$filelist[] = "<a href='".$this->mk_my_orb("change", array("id" => $v["id"], "doc" => $id),"file")."'>#v".$fic."#</a> <i>(Nimi: $v[name])</i>";
		}
		$this->vars(array("FILE_LINE" => $fl,"filelist" => join(",",$filelist)));
		if ($fic > 0)
		{
			$hf = $this->parse("HAS_FILES");
		}
		else
		{
			$hf = $this->parse("NO_FILES");
		}
		$this->vars(array("HAS_FILES" => $hf, "NO_FILES" => ""));

		$graphs = $this->get_aliases_for($id,CL_GRAPH,$s_graph_sortby, $s_graph_order);
		$gc = 0;
		$graphlist = array();
		reset($graphs);
		while (list(,$v) = each($graphs))
		{
			$gc++;
			$this->vars(array("name"								=> $v["name"], 
												"modified"						=> $this->time2date($v["modified"],2), 
												"modifiedby"					=> $v["modifiedby"],
												"alias"								=> "#g".$gc."#","comment" => $v["comment"],
												"id"									=> $v["id"],
												"ch_graph"						=> $this->mk_my_orb("change", array("id" => $v["id"]),"graph"),
												"graph_order"					=> $s_graph_order == "ASC" ? "DESC" : "ASC",
												"graph_name_img"				=> $s_graph_sortby == "name" ?				($s_graph_order == "DESC" ? $upimg : $downimg ): "",
												"graph_comment_img"		=> $s_graph_sortby == "comment" ?		($s_graph_order == "DESC" ? $upimg : $downimg ): "",
												"graph_modifiedby_img"	=> $s_graph_sortby == "modifiedby" ? ($s_graph_order == "DESC" ? $upimg : $downimg ): "",
												"graph_modified_img"		=> $s_graph_sortby == "modified" ?		($s_graph_order == "DESC" ? $upimg : $downimg ): ""));
			$gl.=$this->parse("GRAPH_LINE");
			$graphlist[] = sprintf("<a href='".$this->mk_my_orb("change", array("id" => $v["id"], "parent" => $v["parent"]),"graph")."'>#g%d#</a> <i>(Nimi: $v[name])</i>",$gc);
		}
		$this->vars(array("GRAPH_LINE" => $gl,"graphlist" => join(",",$graphlist)));
		if ($gc > 0)
		{
			$hg = $this->parse("HAS_GRAPHS");
		}
		else
		{
			$hg = $this->parse("NO_GRAPHS");
		}
		$this->vars(array("HAS_GRAPHS" => $hg, "NO_GRAPHS" => ""));

		$galleries = $this->get_aliases_for($id,CL_GALLERY,$s_gallery_sortby, $s_gallery_order);
		$galc = 0;
		$gallist = array();
		reset($galleries);
		while (list(,$v) = each($galleries))
		{
			$galc++;
			$this->vars(array("name"								=> $v["name"], 
												"modified"						=> $this->time2date($v["modified"],2), 
												"modifiedby"					=> $v["modifiedby"],
												"alias"								=> "#y".$galc."#","comment" => $v["comment"],
												"id"									=> $v["id"],
												"ch_gallery"					=> $this->mk_orb("admin", array("id" => $v["id"]),"gallery"),
												"gallery_order"					=> $s_gallery_order == "ASC" ? "DESC" : "ASC",
												"gallery_name_img"				=> $s_gallery_sortby == "name" ?				($s_gallery_order == "DESC" ? $upimg : $downimg ): "",
												"gallery_comment_img"		=> $s_gallery_sortby == "comment" ?		($s_gallery_order == "DESC" ? $upimg : $downimg ): "",
												"gallery_modifiedby_img"	=> $s_gallery_sortby == "modifiedby" ? ($s_gallery_order == "DESC" ? $upimg : $downimg ): "",
												"gallery_modified_img"		=> $s_gallery_sortby == "modified" ?		($s_gallery_order == "DESC" ? $upimg : $downimg ): ""));
			$gal.=$this->parse("GALLERY_LINE");
			$gallist[] = sprintf("<a href='".$this->mk_orb("admin", array("id" => $v["id"]),"gallery")."'>#y%d#</a> <i>(Nimi: $v[name])</i>",$galc);
		}
		$this->vars(array("GALLERY_LINE" => $gal,"gallist" => join(",",$gallist)));
		if ($galc > 0)
		{
			$hg = $this->parse("HAS_GALLERIES");
		}
		else
		{
			$hg = $this->parse("NO_GALLERIES");
		}
		$this->vars(array("HAS_GALLERIES" => $hg, "NO_GALLERIES" => ""));

		$gbs = $this->get_aliases_for($id,CL_GUESTBOOK,$s_gb_sortby, $s_gb_order);
		$gbc = 0;
		$gblist = array();
		reset($gbs);
		while (list(,$v) = each($gbs))
		{
			$gbc++;
			$this->vars(array("name"								=> $v["name"], 
												"modified"						=> $this->time2date($v["modified"],2), 
												"modifiedby"					=> $v["modifiedby"],
												"alias"								=> "#b".$gbc."#","comment" => $v["comment"],
												"id"									=> $v["id"]));
			$gbl.=$this->parse("GB_LINE");
			$gblist[] = sprintf("<a href='".$this->mk_my_orb("change", array("id" => $v["id"], "docid" => $id),"guestbook")."'>#b%d#</a> <i>(Nimi: $v[name])</i>",$gbc);
		}
		$this->vars(array("GB_LINE" => $gal,"gblist" => join(",",$gblist)));
		if ($gbc > 0)
		{
			$hg = $this->parse("HAS_GUESTBOOKS");
		}
		else
		{
			$hg = $this->parse("NO_GUESTBOOKS");
		}
		$this->vars(array("HAS_GUESTBOOKS" => $hg, "NO_GUESTBOOKS"));

		$this->vars(array("add_image"	=> $this->mk_my_orb("new", array("parent" => $id), "images")));
	}


	function delete($arr)
	{
		extract($arr);
		global $period;
		$this->delete_object($id);
		header("Location: ".$this->mk_orb("obj_list", array("parent" => $parent,"period" => $period), "menuedit"));
	}

	function preview($arr)
	{
		extract($arr);
		if ($user)
		{
			$this->read_template("preview_user.tpl");
		}
		else
		{
			$this->read_template("preview.tpl");
		}

		$obj = $this->get_object($id);
		$this->mk_path($obj["parent"],LC_DOCUMENT_PREW);

		if (!$user)
		{
			global $HTTP_HOST,$tpldirs,$tpldir;
			$tpldir = $tpldirs[$HTTP_HOST];
		}
		$template = $this->get_long_template($id);
		classload("document");
		$t = new document;
		$content = $t->gen_preview(array("docid" => $id, "tpl" => $template,"leadonly" => false, "stripimg" => false));

		if (!$user)
		{
			// nice :)
			// basically each site has preview_frame.tpl , that mimics the site , and sets the right style and width for the
			// preview and has {VAR:content} which gets replaced by the document contents
			$awt = new aw_template;
			$awt->tpl_init("automatweb/documents");
			$awt->db_init();
			$awt->read_template("preview_frame.tpl");
			$awt->vars(array("content" => $content));
			$content = $awt->parse();
		}
		$this->vars(array("content" => $content,
											"id"			=> $id,
											"preview"	=> $this->mk_orb("preview", array("id" => $id)),
											"menurl"	=> $this->mk_orb("sel_menus",array("id" => $id)),
											"weburl"	=> $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$id,
											"change"	=> $this->mk_orb("change", array("id" => $id)),
											"lburl"		=> $this->mk_orb("sellang", array("id" => $id))));
		return $this->parse();
	}

	function sel_lang_bros($arr)
	{
		extract($arr);
		$this->read_template("lang_bros.tpl");
		$this->sub_merge = 1;

		global $sstring,$slang_id;

		$document = $this->fetch($id);
		$this->mk_path($document["parent"],LC_DOCUMENT_LANG);

		$lang_brothers = unserialize($document["lang_brothers"]);
		classload("languages");
		$t = new languages;
		$ar = $t->listall();
		reset($ar);
		$first = true;
		while (list(,$v) = each($ar))
		{
			if ($v["id"] != $document["lang_id"])
			{
				$this->vars(array("lang_id" => $v["id"], "lang_name" => $v["name"],"sel" => ($slang_id == $v["id"] || ($first && $slang_id < 1) ? "CHECKED" : "")));
				$this->parse("LANGUAGE");
				$first = false;
			}
		}

		$this->vars(array("reforb" => $this->mk_reforb("seb_s",array("id" => $id)),
											"sstring"	=> $sstring,
											"preview"	=> $this->mk_orb("preview", array("id" => $id)),
											"menurl"	=> $this->mk_orb("sel_menus",array("id" => $id)),
											"weburl"	=> $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$id,
											"change"	=> $this->mk_orb("change", array("id" => $id)),
											"lburl"		=> $this->mk_orb("sellang", array("id" => $id))));

		if ($slang_id < 1)
		{
			return $this->parse();
		}


		$this->mk_menucache($slang_id);

		// selektime otsingule vastavad dokud
		$this->extrarr = array();
		$this->db_query("SELECT documents.*,objects.* FROM documents
										 LEFT JOIN objects ON objects.brother_of = documents.docid
										 WHERE objects.lang_id=".$slang_id." AND documents.title LIKE '%$sstring%'
										 ORDER BY objects.parent,jrk");
		while ($row = $this->db_next()) 
			$this->extrarr[$row["parent"]][] = array("docid" => $row["docid"], "name" => $row["title"].".aw");

		$this->docs = array("0" => "");
		$this->mk_folders($GLOBALS["admin_rootmenu"],"");


		reset($this->docs);
		while (list($k,$v) = each($this->docs))
		{
			$this->vars(array("name" => $v, "selurl" => $this->mk_orb("set_lang_bro", array("id" => $id, "bro" => $k,"sstring" => $sstring, "slang_id" => $slang_id)),"id" => $k));
			if ($lang_brothers[$slang_id] == $k)
				$mt.=$this->parse("MATCH_SEL");
			else
				$mt.=$this->parse("MATCH");
		}

		$this->vars(array("reforb" => $this->mk_reforb("seb_s",array("id" => $id)),
											"sstring"	=> $sstring,
											"MATCH" => $mt,
											"MATCH_SEL" => ""));
		return $this->parse();
	}

	function seb_s($arr)
	{
		return $this->mk_orb("sellang", array("id" => $arr["id"],"slang_id" => $arr["slang_id"],"sstring" => $arr["sstring"]));
	}

	function set_lang_bro($arr)
	{
		extract($arr);
		$document = $this->fetch($id);

		// updateme keelte seosed
		$lb = unserialize($this->db_fetch_field("SELECT lang_brothers FROM documents WHERE docid = ".$id,"lang_brothers"));
		$lb[$slang_id] = $bro;
		$lbs = serialize($lb);
		$this->db_query("UPDATE documents SET lang_brothers = '$lbs' WHERE docid = $id");

		$lb = unserialize($this->db_fetch_field("SELECT lang_brothers FROM documents WHERE docid = ".$bro,"lang_brothers"));
		$lb[$document["lang_id"]] = $id;
		$lbs = serialize($lb);
		$this->db_query("UPDATE documents SET lang_brothers = '$lbs' WHERE docid = $bro");

		header("Location: ".$this->mk_orb("change", $arr));
	}

	function addalias($arr)
	{
		extract($arr);
		$al = $this->fetch($alias);
		if ($al["class_id"] == 8)	// form_entry
		{
			// we must let the user select whether he wants to view or edit the entry
			$this->mk_path($al["parent"],"<a href='pickobject.$ext?docid=$docid&parent=".$al["parent"]."'>Tagasi</a> / Vali aliase t&uuml;&uuml;p");
			return $this->select_alias($id, $alias);
		} 
		else 
		{
			$this->add_alias($id,$alias);
			header("Location: ".$this->mk_orb("change",array("id" => $id)));
		}
	}

	function list_docs($arr)
	{
		global $search,$sstring,$sstring2;

		$this->read_template("list_docs.tpl");
		$this->sub_merge = 1;

		if ($search)
		{
			$this->vars(array("sstring" => $sstring,"sstring2" => $sstring2));
			$this->parse("SEARCH");
			if ($sstring == "" && $sstring2 == "")
				$sstring = "|||||||||||||||||||||||||||";

			$ko = " AND documents.title LIKE '%$sstring%' AND documents.content LIKE '%$sstring2%' ";
		}

		$this->mk_menucache($GLOBALS["lang_id"]);

		// dokumentide list
		$this->extrarr = array();
		$prd = ($arr["period"]) ? $arr["period"] : 0;
		$sub_sel = false;
		$this->db_query("SELECT documents.*,documents.is_forum as is_forum,documents.esilehel as esilehel,documents.esilehel_uudis as esilehel_uudis,
										 documents.showlead as showlead, objects.status as status,objects.parent as parent,
										 objects.jrk as jrk, objects.modified as modified, objects.modifiedby as modifiedby
										 FROM documents
										 LEFT JOIN objects ON objects.brother_of = documents.docid
										 WHERE objects.period = $prd AND objects.lang_id=".$GLOBALS["lang_id"]." and site_id = ".$GLOBALS["SITE_ID"]." $ko
										 ORDER BY objects.parent,jrk");
		while ($row = $this->db_next()) 
		{
			$this->extrarr[$row["parent"]][] = array("docid" => $row["docid"], "name" => $row["title"].".aw");
			$this->docarr[$row["docid"]] = $row;
		}
			
		$this->docs = array();
		$this->mk_folders($GLOBALS["admin_rootmenu"],"");

		reset($this->docs);
		while (list($k,$v) = each($this->docs))
		{
			$row = $this->docarr[$k];
			$this->vars(array("doc_id"		=> $row["docid"],
													"doc_title"	=> $v,
													"doc_title_s"	=> str_replace("\"","\\\"",$row["title"]),
													"jrk"			  => $row["jrk"],
													"modifiedby"	=> $row["modifiedby"],
													"modified"		=> $this->time2date($row["modified"],2),
													"active"			=> ($row["status"] > 0 ? "checked" : ""),
													"is_forum"    => ($row["is_forum"] > 0 ? "checked" : ""),
													"esilehel"    => ($row["esilehel"] > 0 ? "checked" : ""),
													"jrk1"				=> $row["jrk1"],
													"jrk2"				=> $row["jrk2"],
													"esilehel_uudis"    => ($row["esilehel_uudis"] > 0 ? "checked" : ""),
													"showlead"    => ($row["showlead"] > 0 ? "checked" : ""),
													"text_ok"    => ($row["text_ok"] > 0 ? "checked" : ""),
													"pic_ok"    => ($row["pic_ok"] > 0 ? "checked" : ""),
													"link"				=> "<a href='".$GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$row["docid"]."'>url</a>",
													"doc_default"	=> ($this->sel_doc == $row["docid"] ? "CHECKED" : ""),
													"gee"			=> $dcnt & 1 ? "" : "_g"));

				$dd = $this->parse("D_DELETE");
				$dc = $this->parse("D_CHANGE");
				$da = $this->parse("D_ACL");
				$on2.= $this->parse("ONAME2");

				$this->vars(array("D_DELETE" => $dd,
													"D_CHANGE" => $dc,
													"D_ACL" => $da));
				$this->parse("FLINE");
				if ($this->sel_doc == $row["docid"])
					$sub_sel = true;
				$dcnt++;
		}

		classload("objects");
		$ob = new db_objects;
	 
		$this->vars(array("default_doc" => $default_doc,
											"dest"				=> $dest,
											"doc_default"	=> ($sub_sel == false ? "CHECKED" : "" ),
											"ONAME2" => $on2,
											"period" => $arr["period"]));
		return $this->parse();
	}

		function mk_folders($parent,$str)
		{
			if (!is_array($this->menucache[$parent]))
				return;

			reset($this->menucache[$parent]);
			while(list(,$v) = each($this->menucache[$parent]))
			{
				$name = $v["data"]["name"];
				if ($v["data"]["parent"] == 1)
				{
					$words = explode(" ",$name);
					if (count($words) == 1)
						$name = $words[0][0].$words[0][1];
					else
					{
						reset($words);
						$mstr = "";
						while(list(,$v3) = each($words))
							$mstr.=$v3[0];
						$name = $mstr;
					}
				}

				$sep = ($str == "" ? "" : " / ");
				$tstr = $str.$sep.$name;

				if (is_array($this->extrarr[$v["data"]["oid"]]))
				{
					reset($this->extrarr[$v["data"]["oid"]]);
					while (list(,$v2) = each($this->extrarr[$v["data"]["oid"]]))
						$this->docs[$v2["docid"]] = $tstr." / ".$v2["name"];
				}

				$this->mk_folders($v["data"]["oid"],$tstr);
			}
		}

	function mk_menucache($slang_id)
	{
		// cacheme menyyd
		$this->db_query("SELECT objects.oid as oid, 
														objects.parent as parent,
														objects.comment as comment,
														objects.name as name,
														objects.created as created,
														objects.createdby as createdby,
														objects.modified as modified,
														objects.modifiedby as modifiedby,
														objects.last as last,
														objects.status as status,
														objects.jrk as jrk,
														objects.class_id as class_id,
														menu.type as mtype,
														menu.periodic as mperiodic,
														menu.is_copied as is_copied,
														menu.data as data,
														menu.clickable as clickable,
														menu.hide_noact as hide_noact,
														menu.target as target
											FROM objects 
											LEFT JOIN menu ON menu.id = objects.oid
											WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.") AND objects.status != 0  AND (objects.lang_id=".$slang_id." OR menu.type= ".MN_CLIENT.")
											GROUP BY objects.oid
											ORDER BY objects.parent, menu.is_l3,jrk");
		// tsykkel yle menyyelementide
		while ($row = $this->db_next()) 
		{
			$sets = unserialize($row["data"]);
			$this->menucache[$row["parent"]][] = array("data" => $row);
			if (is_array($sets["section"]))
			{
				reset($sets["section"]);
				while(list(,$v) = each($sets["section"]))
				{
					// topime menyystruktuuri arraysse
					$this->menucache[$v][] = array("data" => $row);
				}
			}
		}
	}

	function sel_menus($arr)
	{
		extract($arr);
		$obj = $this->get_object($id);
		$this->mk_path($obj["parent"],LC_DOCUMENT_PREW);

		$this->read_template("nbrother.tpl");
		$sar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($arow = $this->db_next())
			$sar[$arow["parent"]] = $arow["parent"];

		classload("objects");
		$ob = new db_objects;

		$this->vars(array("docid" => $id,"sections"		=> $this->multiple_option_list($sar,$ob->get_list(true)),
											"reforb"	=> $this->mk_reforb("submit_menus",array("id" => $id)),
											"preview"	=> $this->mk_orb("preview", array("id" => $id)),
											"menurl"	=> $this->mk_orb("sel_menus",array("id" => $id)),
											"weburl"	=> $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$id,
											"change"	=> $this->mk_orb("change", array("id" => $id)),
											"lburl"		=> $this->mk_orb("sellang", array("id" => $id))));
		return $this->parse();
	}

	function submit_menus($arr)
	{
		extract($arr);

		$obj = $this->get_object($id);

		$sar = array(); $oidar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($row = $this->db_next())
		{
			$sar[$row["parent"]] = $row["parent"];
			$oidar[$row["parent"]] = $row["oid"];
		}

		$not_changed = array();
		$added = array();
		if (is_array($sections))
		{
			reset($sections);
			$a = array();
			while (list(,$v) = each($sections))
			{
				if ($sar[$v])
					$not_changed[$v] = $v;
				else
					$added[$v] = $v;
				$a[$v]=$v;
			}
		}
		$deleted = array();
		reset($sar);
		while (list($oid,) = each($sar))
		{
			if (!$a[$oid])
				$deleted[$oid] = $oid;
		}

		reset($deleted);
		while (list($oid,) = each($deleted))
		{
			$this->delete_object($oidar[$oid]);
		}
		reset($added);
		while(list($oid,) = each($added))
		{
			if ($oid != $id)	// no recursing , please
			{
				$noid = $this->new_object(array("parent" => $oid,"class_id" => CL_BROTHER_DOCUMENT,"status" => 1,"brother_of" => $id,"name" => $obj["name"],"comment" => $obj["comment"]));
			}
		}

		return $this->mk_orb("sel_menus",array("id" => $id));
	}

	////
	// !deletes alias $id of document $docid and returns to editing the document
	function del_alias($arr)
	{
		extract($arr);
		$this->delete_alias($docid,$id);
		header("Location: ".$this->mk_orb("change", array("id" => $docid)));
	}

	function add_bro($arr)
	{
		extract($arr);
		$this->mk_path($parent, LC_DOCUMENT_BROD_DOC);
		$this->read_template("search_doc.tpl");
		global $s_name, $s_content,$SITE_ID;
		if ($s_name != "" || $s_content != "")
		{
			$se = array();
			if ($s_name != "")
			{
				$se[] = " name LIKE '%".$s_name."%' ";
			}
			if ($s_content != "")
			{
				$se[] = " content LIKE '%".$s_content."%' ";
			}
			$this->db_query("SELECT documents.title as name,objects.oid FROM objects LEFT JOIN documents ON documents.docid=objects.oid WHERE objects.status != 0 AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) AND (objects.class_id = ".CL_DOCUMENT." OR objects.class_id = ".CL_PERIODIC_SECTION." ) AND ".join("AND",$se));
			while ($row = $this->db_next())
			{
				$this->vars(array("name" => $row["name"], "id" => $row["oid"],
													"brother" => $this->mk_orb("create_bro", array("parent" => $parent, "id" => $row["oid"], "s_name" => $s_name, "s_content" => $s_content)),
													"change" => $this->mk_orb("change", array("parent" => $parent, "id" => $row["oid"]))));
				$l.=$this->parse("LINE");
			}
			$this->vars(array("LINE" => $l));
		}
		else
		{
			$s_name = "%";
			$s_content = "%";
		}
		$this->vars(array("reforb" => $this->mk_reforb("add_bro", array("reforb" => 0,"parent" => $parent)),
											"s_name"	=> $s_name,
											"s_content"	=> $s_content));
		return $this->parse();
	}

	function create_bro($arr)
	{
		extract($arr);
		//check if this brother does not already exist
		$this->db_query("SELECT * FROM objects WHERE parent = $parent AND class_id = ".CL_BROTHER_DOCUMENT." AND brother_of = $id AND status != 0");
		if (!($row = $this->db_next()))
		{
			$obj = $this->get_object($id);
			if ($obj["parent"] != $parent)
			{
				$noid = $this->new_object(array("parent" => $parent,"class_id" => CL_BROTHER_DOCUMENT,"status" => 2,"brother_of" => $id,"name" => $obj["name"],"comment" => $obj["comment"]));
			}
		}
		header("Location: ".$this->mk_orb("add_bro", array("parent" => $parent, "s_name" => $s_name,"s_content" => $s_content)));
	}

	function _serialize($arr)
	{
		extract($arr);
		$this->db_query("SELECT documents.*,objects.* FROM documents LEFT JOIN objects ON objects.oid = documents.docid WHERE docid = $oid");
		$row = $this->db_next();

		$al = $this->get_aliases_for($oid);
		return serialize(array("row" => $row, "aliases" => $al));
	}

	function _unserialize($arr)
	{
		extract($arr);

		$ar = unserialize($str);
	
		$row = $ar["row"];

		$row["parent"] = $parent;
		$id = $this->new_object($row);

		reset($this->knownfields);
		while(list($fcap,$fname) = each($this->knownfields)) 
		{
			$this->quote(&$row[$fname]);
			$q_parts[] = "'$row[$fname]'";
			$s_parts[] = "$fname";
		};
		
		// see paneb siis paringu kokku. Whee.
		$q = "INSERT INTO documents(docid,".join(",",$s_parts).") VALUES($id," . join(",",$q_parts) . ")"; 
		$this->db_query($q);

		$al = $ar["aliases"];
		reset($al);
		while (list(,$arow) = each($al))
		{
			$this->add_alias($id,$arow["target"], $arow["data"]);
		}
		return true;
	}

	function do_search($parent,$str,$sec,$sortby,$from)
	{
		if ($sortby == "")
			$sortby = "percent";

		$this->tpl_init("automatweb/documents");
		$this->quote(&$str);

		if ($str == "")
		{
			$this->read_template("search_none.tpl");
			return $this->parse();
		}

		$this->read_template("search.tpl");

		// make list of menus
		if ($GLOBALS["lang_menus"] == 1)
			$ss = " AND objects.lang_id = ".$GLOBALS["lang_id"];

		$this->menucache = array();
		$this->db_query("SELECT objects.oid as oid, objects.parent as parent,objects.last as last,objects.status as status
										 FROM objects 
										 WHERE objects.class_id = 1 AND objects.status = 2 $ss");
		while ($row = $this->db_next())
		{
			$this->menucache[$row[parent]][] = $row;
		}
		// now, make a list of all menus below $parent
		$this->marr = array($parent);
		// list of default documents
		$this->darr = array();
		$this->rec_list($parent);

		$ml = join(",",$this->marr);
		$ml2 = join(",",$this->darr);
		if ($ml != "")
			$ml = " AND objects.parent IN ($ml) ";

		if ($ml2 != "")
			$ml.= " AND objects.oid IN ($ml2) ";
	
		if ($sortby == "time")
			$ml.=" ORDER BY objects.modified DESC";

		// oh crap. siin peab siis failide seest ka otsima. 
		$mtfiles = array();
		$this->db_query("SELECT id FROM files WHERE files.showal = 1 AND files.content LIKE '%$str%' ");
		while ($row = $this->db_next())
		{
			$mtfiles[] = $row[id];
		}
		$fstr = join(",",$mtfiles);
		if ($fstr != "")
		{
			// nyyd leiame k6ik aliased, mis vastavatele failidele tehtud on
			$this->db_query("SELECT * FROM aliases WHERE target IN ($fstr)");
			while ($row = $this->db_next())
			{
				$faliases[] = $row[source];
			}
			// nyyd on $faliases array dokumentidest, milles on tehtud aliased matchivatele failidele.
			if (is_array($faliases))
				$fasstr = "OR documents.docid IN (".join(",",$faliases).")";
		}

		// nini. otsime tabelite seest ka.
		$mts = array();
		$this->db_query("SELECT id FROM aw_tables WHERE contents LIKE '%$str%'");
		while ($row = $this->db_next())
		{
			$mts[] = $row[id];
		}

		$mtsstr = join(",",$mts);
		if ($mtsstr != "")
		{
			// nyyd on teada k6ik tabelid, ksu string sisaldub
			// leiame k6ik aliased, mis on nendele tabelitele tehtud
			$this->db_query("SELECT * FROM aliases WHERE target IN ($mtsstr)");
			while ($row = $this->db_next())
			{
				$mtals[$row[source]] = $row[source];
			}

			// see on siis nimekiri dokudest, kuhu on tehtud aliased tabelitest, mis matchisid
			$mtalsstr = "OR documents.docid IN (".join(",",$mtals).")";
			//echo "ms = $mtalsstr<br>";
		}

		$cnt = 0;
		//max number of occurrences of search string in document
		$max_count = 0;
		$docarr = array();

//Mingi imelik echo oli.  
/*
		echo "ot: SELECT documents.*,objects.parent as parent, objects.modified as modified 
										 FROM documents 
										 LEFT JOIN objects ON objects.oid = documents.docid
										 WHERE (documents.title LIKE '%".$str."%' OR documents.content LIKE '%".$str."%' $fasstr $mtalsstr) AND objects.status = 2 $ml";
*/
		$this->db_query("SELECT documents.*,objects.parent as parent, objects.modified as modified 
										 FROM documents 
										 LEFT JOIN objects ON objects.oid = documents.docid
										 WHERE (documents.title LIKE '%".$str."%' OR documents.content LIKE '%".$str."%' $fasstr $mtalsstr) AND objects.status = 2 AND (documents.no_search is null OR documents.no_search = 0) $ml");
		while($row = $this->db_next())
		{
			// find number of matches in document for search string, for calculating percentage
			// if match is found in title, then multiply number by 5, to emphasize importance
			$c = substr_count(strtoupper($row[content]),strtoupper($str)) + substr_count(strtoupper($row[title]),strtoupper($str))*5;
			$max_count = max($c,$max_count);

			// find the first paragraph of text
			$co = strip_tags($row[content]);
			$co = substr($co,0,strpos($co,"\n"));
			$co = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$co);
			$docarr[] = array("matches" => $c, "title" => $row[title],"section" => $row[docid],"content" => $co,"modified" => $this->time2date($row[modified],5),"tm" => $row[tm]);
			
			$cnt++;
		}

		if ($sortby == "percent")
		{
			$d2arr = array();
			reset($docarr);
			while (list(,$v) = each($docarr))
			{
				if ($max_count == 0)
					$d2arr[100][] = $v;
				else
					$d2arr[($v[matches]*100) / $max_count][] = $v;
			}

			krsort($d2arr,SORT_NUMERIC);

			$docarr = array();
			reset($d2arr);
			while (list($p,$v) = each($d2arr))
			{
				reset($v);
				while (list(,$v2) = each($v))
					$docarr[] = $v2;
			}

		}

		$per_page = 10;

		$num = 0;
		reset($docarr);
		while (list(,$v) = each($docarr))
		{
			if ($num >= $from && $num < ($from + $per_page))	// show $per_page matches per screen
			{
				if ($max_count == 0)
					$sstr = 100;
				else
					$sstr = substr(($v[matches]*100) / $max_count,0,4);
				$this->vars(array("title"			=> $v[title],
													"percent"		=> $sstr,
													"content"		=> preg_replace("/#(.*)#/","",$v[content]),
													"modified"	=> $v[tm] == "" ? $v[modified] : $v[tm],
													"section"		=> $v[section]));
				$r.= $this->parse("MATCH");
			}
			$num++;
		}

		$this->vars(array("MATCH" => $r,"s_parent" => $parent,"sstring" => urlencode($str),"sstringn" => $str, "section" => $sec,"matches" => $cnt,"sortby" => $sortby));

		// make prev page / next page
		if ($cnt > $per_page)
		{
			if ($from > 0)
			{
				$this->vars(array("from" => $from-$per_page));
				$prev = $this->parse("PREVIOUS");
			}
			if ($from+$per_page <= $cnt)
			{
				$this->vars(array("from" => $from+$per_page));
				$next = $this->parse("NEXT");
			}

			for ($i=0; $i < $cnt / $per_page; $i++)
			{
				$this->vars(array("from" => $i*$per_page,"page_from" => $i*$per_page,"page_to" => ($i+1)*$per_page));
				if ($i*$per_page == $from)
					$pg.=$this->parse("SEL_PAGE");
				else
					$pg.=$this->parse("PAGE");
			}
		}
		$this->vars(array("PREVIOUS" => $prev,"NEXT" => $next,"PAGE" => $pg,"SEL_PAGE" => "","from" => $from));
		$ps = $this->parse("PAGESELECTOR");
		$this->vars(array("PAGESELECTOR" => $ps));
		return $this->parse();
	}
	function rec_list($parent)
	{
		if (!is_array($this->menucache[$parent]))
			return;

		reset($this->menucache[$parent]);
		while(list(,$v) = each($this->menucache[$parent]))
		{
			if ($v[status] == 2)
			{
				$this->marr[] = $v[oid];
				if ($v[last] > 0)
					$this->darr[] = $v[last];
				$this->rec_list($v[oid]);
			}
		}
	}
};
?>
