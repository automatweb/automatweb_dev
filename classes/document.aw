<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/document.aw,v 2.92 2002/03/20 14:02:23 duke Exp $
// document.aw - Dokumentide haldus. 

lc_load("document");
classload("msgboard","aw_style","form_base");
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
		$this->init("automatweb/documents");
		// see on selleks, kui on vaja perioodilisi dokumente naidata
		$this->period = $period;
		
		$this->style_engine = new aw_style;
		lc_load("definition");
		global $lc_document;
		if (is_array($lc_document))
		{
			$this->vars($lc_document);
		}
			
		// this takes less than 0.1 seconds btw
		$xml_def = $this->get_file(array("file" => aw_ini_get("basedir")."/xml/documents/defaults.xml"));
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
				LC_DOCUMENT_SHOW_CHANGED =>	"show_modified",
			"Esileht vasak" => "frontpage_left",
			"Esileht keskel" => "frontpage_center",
			"Esileht keskel all" => "frontpage_center_bottom",
			"Esileht parem" => "frontpage_right",
			"Jrk Esileht vasak" => "frontpage_left_jrk",
			"Jrk Esileht keskel" => "frontpage_center_jrk",
			"Jrk Esileht keskel all" => "frontpage_center_bottom_jrk",
			"Jrk Esileht parem" => "frontpage_right_jrk",
			"no_last" => "no_last"
		);

		// nini. siia paneme nyt kirja v2ljad, mis dokumendi metadata juures kirjas on
		$this->metafields = array("show_print","show_last_changed");

		// siin on kirjas need väljad, mida arhiveeritakse
		$this->archive_fields = array("title","lead","content");
		lc_site_load("document",$this);
		if (isset($GLOBALS["lc_doc"]) && is_array($GLOBALS["lc_doc"]))
		{
			$this->vars($GLOBALS["lc_doc"]);
		}
	}

	////
	// !Sets period to use
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

	////
	// !Listib dokud mingi menüü all
	function list_docs($parent,$period = -1,$status = -1,$visible = -1)
	{
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
		$row = $this->get_menu($parent);
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
		$v.= " AND objects.status = " . (($status == -1) ? 2 : $status);

		if ($row["ndocs"] > 0)
		{
			$lm = "LIMIT ".$row["ndocs"];
		};

		if ($ordby == "")
		{
			$ordby = "objects.jrk";
		}
		$q = "SELECT documents.lead AS lead,
			documents.docid AS docid,
			documents.title AS title,
			documents.*,
			objects.period AS period,
			objects.class_id as class_id,
			objects.parent as parent
			FROM documents
			LEFT JOIN objects ON
			(documents.docid = objects.brother_of)
			WHERE $pstr && $rstr $v
			ORDER BY $ordby $lm";
		$this->db_query($q);
	}

	////
	// !Fetces a document from the database
	function fetch($docid) 
	{
		if ($this->period > 0) 
		{
			$sufix = " && objects.period = " . $this->period;
		} 
		else 
		{
			$sufix = "";
		};

		if ($docid)
		{
			$q = "SELECT objects.*,documents.* FROM objects LEFT JOIN documents ON objects.brother_of = documents.docid WHERE objects.oid = $docid $sufix";
			$this->db_query($q);
		}
		$data = $this->db_next();

		if (gettype($data) == "array") 
		{
			$data["content"] = trim($data["content"]);
			$data["lead"] = trim($data["lead"]);
			$data["cite"] = trim($data["cite"]);
		};
		$this->dequote($data);
		$this->data = $data;
		return $data;
	}

	////
	// !Generates a RSS feed from all documents under a menu. Or from all articles
	// in the current period, if a menu is not specified
	function gen_rss_feed($args = array())
	{
		classload("rdf");
		$baseurl = aw_ini_get("baseurl");
		global $stitle;
		$ext = aw_ini_get("ext");
		$rdf = new rdf(array(
			"about" => "$baseurl/index.$ext/format=rss",
			"link" => "$baseurl/index.$ext",
			"title" => $stitle,
			"description" => PUBLISHER,
    ));

		extract($args);
		$rootmenu = aw_ini_get("rootmenu");
		$parent = (int)$parent;
		if ($parent && ($parent != $rootmenu))
		{
			$pstr = " AND objects.parent = '$parent' ";
		}
		else
		{
			$pstr = "";
		};
		$q = "SELECT documents.docid AS docid,title,lead,author,objects.modified AS modified FROM documents
			LEFT JOIN objects ON objects.oid = documents.docid
			WHERE objects.period = '$period' AND objects.status = 2 $pstr";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$row["title"] = strip_tags($row["title"]);
			$rdf->add_item($row);
		};
		header("Content-Type: text/xml");
		print $rdf->gen_output();
		die();
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
		global $print;	
		$this->print = $print;
		$tpl = isset($params["tpl"]) ? $params["tpl"] : "plain.tpl";
		!isset($leadonly) ? $leadonly = -1 : "";
		!isset($strip_img) ? $strip_img = 0 : "";
		!isset($notitleimg) ? $notitleimg = 0 : "";
		
		global $awt;
		$baseurl = aw_ini_get("baseurl");
		$ext = aw_ini_get("ext");
		$awt->start("document::gen_preview");

		// check if the menu had a form selected as a template - the difference is that then the template is not a filename
		// but a number
		if (is_number($tpl))
		{
			return $this->do_form_show($params);
		}

		// küsime dokumendi kohta infot
		// muide docid on kindlasti numbriline, aliaseid kasutatakse ainult
		// menueditis.
		if (!isset($doc) || !is_array($doc))
		{
			$doc = $this->fetch($docid);
			// I hope this won't break anything. but now when you click on a brother document
			// you sould still be left under the menu where the brother document is.
			//	$docid = $doc["docid"];
		};
			
		// if there is no document with that id, then bail out
		if (!isset($doc))
		{
			return false;
		};

		$meta = $this->get_object_metadata(array("oid" => $doc["brother_of"]));
		
		if ($meta["show_last_changed"])
		{
			$doc["content"] .= "<p><font size=1><i>Viimati muudetud:&nbsp;&nbsp;</i>" . $this->time2date($doc["modified"],4) . "</font>";
		};
	
		if ( ($meta["show_print"]) && (not($print)) && $leadonly != 1)
		{
			if ($this->cfg["print_cap"] != "")
			{
				$pc = localparse($this->cfg["print_cap"],array("link" => "/section=$docid" . "&print=1","docid" => $docid));
				if ($this->cfg["pc_bottom"])
				{
					$doc["content"] .= $pc;
				}
				else
				{
					if (!($doc["showlead"] == 1 || $showlead == 1))
					{
						$doc["content"] = $pc . $doc["content"];
					}
					else
					{
						$doc["lead"] = $pc . $doc["lead"];
					}
				};
			}
			else
			{
				$this->vars(array("docid" => $docid));
				$_tmp = $this->parse("PRINTANDSEND");
				$this->vars(array("PRINTANDSEND" => $_tmp));
			};
		};


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

		// I don't think we should do that here
		// $this->add_hit($docid);

		$this->mk_ns4_compat(&$doc["lead"]);
		$this->mk_ns4_compat(&$doc["content"]);

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

		// vaatame kas vaja poolitada - kui urlis on show_all siis n2itame tervet, muidu n2itame kuni #edasi# linkini
		if ($GLOBALS["show_all"])
		{
			$doc["content"] = str_replace("#edasi#", "",$doc["content"]);
			if (!(($pp = strpos($doc["content"],"#edasi1#")) === false))
			{
				$doc["content"] = substr($doc["content"],$pp+8);
			}
		}
		else
		{
			if (!(($pp = strpos($doc["content"],"#edasi#")) === false))
			{
				$doc["content"] = substr($doc["content"],0,$pp)."<br><B><a href='".$baseurl."/index.".$ext."/section=$docid/show_all=1'>Loe edasi</a></b></font>";
			}

			if (!(($pp = strpos($doc["content"],"#edasi1#")) === false))
			{
				$doc["content"] = substr($doc["content"],0,$pp)."<br><B><a href='".$baseurl."/index.".$ext."/section=$docid/show_all=1'>Loe edasi</a></b></font>";
			}
		}

		// laeme vajalikud klassid
		classload("images");
		$img = new db_images;

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
			$doc["content"] = $doc["lead"];
		} 
		else 
		{
			if (($doc["lead"]) && ($doc["showlead"] == 1 || $showlead == 1) )
			{
				if ($this->is_template("image_pos"))
				{
					if (preg_match("/#p(\d+?)(v|k|p|)#/i",$doc["lead"],$match)) 
					{
						// asendame 
						$idata = $img->get_img_by_oid2($docid,$match[1]);
						if (!$idata)
						{
							$idata = $img->get_img_by_oid($docid,$match[1]);
						}
						$this->vars(array(
							"imgref" => $idata["url"]
						));
						$this->vars(array("image_pos" => $this->parse("image_pos")));
						$doc["lead"] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc["lead"]);
					}
				}
				if ($no_strip_lead != 1)
				{
					$doc["lead"] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc["lead"]);
				}
				$txt = "";

				if ($boldlead) 
				{
					$txt = "<b>";
				};

				if ($doc["lead"] != "" && $doc["lead"] != "&nbsp;")
				{
					if ($this->cfg["lead_splitter"] != "")
					{
						$txt .= $doc["lead"] . $this->cfg["lead_splitter"];
					}
					else
					{
						$txt .= $doc["lead"] . "<br>";
					}
				}

				// whaat?
				if ($this->cfg["no_lead_splitter"])
				{
					$txt.=$this->cfg["no_lead_splitter"];
				}

				if ($boldlead) 
				{
					$txt .= "</b>";
				};

				$txt .= ($this->cfg["doc_lead_break"] && $no_doc_lead_break != 1 ? "<br>" : "")."$doc[content]";
				$doc["content"] = $txt;
			};
		};

		// all the style magic is performed inside the style engine
		$doc["content"] = $this->style_engine->parse_text($doc["content"]); 
		
		$doc["content"] = preg_replace("/<loe_edasi>(.*)<\/loe_edasi>/isU","<a href='$baseurl/index.$ext/section=$docid'>\\1</a>",$doc["content"]);
		// sellel real on midagi pistmist WYSIWYG edimisvormiga
		// and this also means that we can't have xml inside the document. sniff.
		$doc["content"] = preg_replace("/<\?xml(.*)\/>/imsU","",$doc["content"]); 

		$this->docid = $docid;
		$this->source = $doc["content"];

		$this->register_parsers();
		$this->create_relative_links(&$doc["content"]);
		// viimati muudetud dokude listi rida
		if (preg_match("/#viimati_muudetud num=\"(.*)\"#/",$doc["content"], $matches))
		{
			$doc["content"] = str_replace("#viimati_muudetud num=\"".$matches[1]."\"#",$this->get_last_doc_list($matches[1]),$doc["content"]);
		}

		if (strpos($doc["content"], "#chat#") !== false)
		{
			$doc["content"] = str_replace("#chat#", "<applet codebase=\"http://aw.struktuur.ee/risto/arco/\" code=Klient.class height=37 width=77></applet>",$doc["content"]);
			if($GLOBALS["uid"] != "")
			{
				$socket = fsockopen("aw.struktuur.ee", 10020,$errno,$errstr,10);
				fputs($socket,"NIMI ".$login."\n");
				fclose($socket);
			}
		}

		// create keyword links unless we are in print mode, since you cant click
		// on links on the paper they dont make sense there :P
		if ($this->cfg["keyword_relations"] && not($print))
		{
			$this->create_keyword_relations(&$doc["content"]);
		}
	
		// v6tame pealkirjast <p> maha
		$doc["title"] = preg_replace("/<p>(.*)<\/p>/is","\\1",$doc["title"]);

		if ($notitleimg != 1)
		{
			$doc["title"] = $this->parse_aliases(array(
							"text"	=> $doc["title"],
							"oid"	=> $doc["docid"],
					));
		}
		else
		{
			$doc["title"] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc["title"]);
		}

		// this is useless. do we use that code anywhere?
		if (!(strpos($doc["content"], "#board_last5#") === false))
		{
			$mb = new msgboard;
			$doc["content"] = str_replace("#board_last5#",$mb->mk_last5(),$doc["content"]);
		}

		// #top# link - viib doku yles
		$top_link = $this->parse("top_link");
		$doc["content"] = str_replace("#top#", $top_link,$doc["content"]);

		// noja, mis fucking "undef" see siin on?
		// damned if I know , v6tax ta 2kki 2ra siis? - terryf 
		classload("aliasmgr");
		$al = new aliasmgr();

		if (!isset($text) || $text != "undef") 
		{
			$al->parse_oo_aliases($doc["docid"],&$doc["content"],array("templates" => &$this->templates,"meta" => &$meta));
			$doc["content"] = $this->parse_aliases(array(
                                "oid" => $docid,
                                "text" => $doc["content"],
                        ));

			$this->vars($al->get_vars());
		}; 

		if (!$doc["nobreaks"])	// kui wysiwyg editori on kasutatud, siis see on 1 ja pole vaja breike lisada
		{
			$doc["content"] = str_replace("\r\n","<br>",$doc["content"]);
		}

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

		/*
		classload("msgboard");
		$t = new msgboard;
		$nc = $t->get_num_comments($doc["docid"]);
		$nc = $nc < 1 ? "0" : $nc;
		$doc["content"] = str_replace("#kommentaaride arv#",$nc,$doc["content"]);
		*/

		// <mail to="bla@ee">lahe tyyp</mail>
    		$doc["content"] = preg_replace("/<mail to=\"(.*)\">(.*)<\/mail>/","<a class='mailto_link' href='mailto:\\1'>\\2</a>",$doc["content"]);
		$doc["content"] = str_replace(LC_DOCUMENT_CURRENT_TIME,$this->time2date(time(),2),$doc["content"]);

		if (!(strpos($doc["content"],"#liitumisform") === false))
		{
			preg_match("/#liitumisform info=\"(.*)\"#/",$doc["content"], $maat);

			// siin tuleb n2idata kasutaja liitumisformi, kuhu saab passwordi ja staffi kribada.
			// aga aint sel juhul, kui kasutaja on enne t2itnud k6ik miski grupi formid.
			classload("users");
			$dbu = new users;
			$doc["content"] = preg_replace("/#liitumisform info=\"(.*)\"#/",$dbu->get_join_form($maat[1]),$doc["content"]);
		}
				
		$ab = "";
		// I hate the next block of code
		if ($doc["author"]) 
		{
			if (DOC_LINK_AUTHORS && isset($this->templates["ablock"])) 
			{
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

		$fr = "";
		
		if ($doc["is_forum"] && (not($print)) )
		{
			// calculate the amount of comments this document has
			$num_comments = $this->db_fetch_field("SELECT count(*) AS cnt FROM comments WHERE board_id = '$docid'","cnt");
			$this->vars(array(
				"num_comments" => sprintf("%d",$num_comments),
				"comm_link" => $this->mk_my_orb("show_threaded",array("board" => $docid),"forum"),
			));
			classload("forum");
			$forum = new forum();
			$fr = $forum->add_comment(array("board" => $docid));
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
			$mn = $this->get_menu($doc["parent"]);
		}

		if (!isset($this->doc_count))
		{
			$this->doc_count = 0;
		}

		$title = $doc["title"];
		$this->vars(array(
			"title"	=> $title,
			"menu_image" => $mn["img_url"],
			"text"  => $doc["content"],
			"secid" => isset($secID) ? $secID : 0,
			"docid" => $docid,
			"ablock"   => isset($ab) ? $ab : 0,
			"pblock"   => isset($pb) ? $pb : 0,
			"date"     => $this->time2date(time(),2),
			"section"  => $GLOBALS["section"],
			"lead_comments" => $lc,
			"modified"	=> $this->time2date($doc["modified"],2),
			"date2"	=> $this->time2date($doc["modified"],8),
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

		$parent_obj = $this->get_object($doc["parent"]);
		$this->vars(array(
			"parent_name" => $parent_obj["name"]
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
			"logged" => ($GLOBALS["uid"] != "" ? $this->parse("logged") : "")
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
 		if ($this->is_template("FILE"))
		{
			$aliases = $this->get_aliases_for($doc["docid"]);
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
		$awt->stop("document::gen_preview");
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
							WHERE parent = $section AND $field LIKE '$v' AND objects.status = 2";
				$retval[$v] = $this->db_fetch_field($q,"docid");
			}; // eow
			return $retval;
		}; // eoi
	}

	////
	// !Salvestab dokumendi
	function save($data) 
	{
		// id (docid) on ainuke parameeter, mis *peab* olema kaasa antud
		// ja siis veel vähemalt yx teine parameeter mida muuta

		// fetchime vana dokumendi, et seda arhiivi salvestada
		$old = $this->fetch($data["id"]);
		// $data["archive"] means that "archive" checkbox was checked and that we should make 
		// a copy of the current document to the archive

		// $data["version"] means that we are working on a archive copy of a document and
		// should therefore save the changes to archive and not to the document table
		if (defined("ARCHIVE"))
		{
			classload("archive");
			$arc = new archive();
		};

		if (defined("ARCHIVE") && ($data["archive"]) )
		{
			// teeme arhiivi, kui seda pole
			// kallis vaataja, ma tean, et sulle meeldib jargnev rida kohe sitta moodi

			if (not($arc->exists(array("oid" => $data["id"]))))
			{
				$arc->add(array("oid" => $data["id"]));
			};

			$arc_data = array();

			foreach($this->archive_fields as $afield)
			{
				$arc_data[$afield] = $data[$afield];
			};

			$arc_name = ($data["archive_name"]) ? $data["archive_name"] : $data["title"];

			$arc->commit(array(
						"oid" => $data["id"],
						"ser_content" => $old,
						"name" => $arc_name,
						"data" => $arc_data,
						"class_id" => CL_DOCUMENT,
			));
		};
		
		if (defined("ARCHIVE") && ($data["version"]) )
		{
			$actual = array_merge($old,$data);
			$arc_data = array();
			foreach($this->archive_fields as $afield)
			{
				$arc_data[$afield] = $data[$afield];
			};
			$arc_name = ($data["archive_name"]) ? $data["archive_name"] : $data["title"];
			$arc->commit(array(
						"oid" => $data["id"],
						"ser_content" => $actual,
						"name" => $arc_name,
						"version" => $data["version"],
						"data" => $arc_data,
						"class_id" => CL_DOCUMENT,
			));
			
			// logime aktsioone
			$this->_log("document","muutis dokumenti <a href='".$GLOBALS["baseurl"]."/automatweb/".$this->mk_orb("change", array("id" => $id))."'>'".$data["title"]."'</a> arhiivikoopiat");
			// laena mulle bensiini ja tikke, vanemuine
			return $this->mk_my_orb("change", array("id" => $data["id"],"section" => $data["section"],"version" => $data["version"]));
		};

		// go on with our usual business
		
		$this->quote($data);
		$user = $data["user"];
		if ($data["content"]) {$data["content"] = trim($data["content"]);};
		if ($data["lead"]) {$data["lead"] = trim($data["lead"]);};
		if ($data["cite"]) {$data["cite"] = trim($data["cite"]);};
		if ($data["keywords"] || $data["link_keywords"])
		{
			classload("keywords");
			$kw = new keywords;
			if ($data["keywords"])
			{
				$kw->update_keywords(array(
							"keywords" => $data["keywords"],
							"oid" => $data["id"],
				));
			}
			else
			{
				$kw->update_relations(array(
						"id" => $data["id"],
						"data" => $data["content"],
				));
				// also update keyword brother docs
				$kw->update_menu_keyword_bros(array("doc_ids" => array($data["id"])));
			};

		};

		if ($data["clear_styles"] == 1)
		{
			$data["content"] = strip_tags($data["content"], "<b>,<i>,<u>,<br>,<p>");
			$data["lead"] = strip_tags($data["lead"], "<b>,<i>,<u>,<br>,<p>");
		}

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
			if (isset($data[$fname]) || $fname=="esilehel" || $fname=="esileht_yleval" || $fname=="esilehel_uudis" || $fname=="is_forum" || $fname=="lead_comments" || $fname=="showlead" || $fname=="yleval_paremal" || $fname == "show_title" || $fname=="copyright" || $fname == "show_modified" || $fname == "title_clickable" || $fname == "newwindow" || $fname == "no_right_pane" || $fname == "no_left_pane" || $fname == "no_search" || $fname == "frontpage_left" || $fname == "frontpage_center" || $fname == "frontpage_center_bottom" || $fname == "frontpage_right" || $fname == "no_last")  
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
			list($day,$mon,$year) = explode("/",$data["tm"]);

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
		$q = "UPDATE documents SET " . join(",\n",$q_parts) . " WHERE docid = '".$old["brother_of"]."'"; 
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

		if ($data["docfolder"])
		{
			$oq_parts["parent"] = $data["docfolder"];
		}

		$this->upd_object($oq_parts);
		$this->db_query("UPDATE objects SET name='".$data["name"]."' WHERE brother_of = '".$old["brother_of"]."'");

		// nih, updateme objekti metadata ka 2ra.
		foreach($this->metafields as $m_name => $m_key)
		{
			$this->set_object_metadata(array("oid" => $id, "key" => $m_key, "value" => $data[$m_key]));
		}

		$this->flush_cache();

		// logime aktsioone
		$this->_log("document","muutis dokumenti <a href='".$GLOBALS["baseurl"]."/automatweb/".$this->mk_orb("change", array("id" => $id))."'>'".$data["title"]."'</a>");

		return $this->mk_my_orb("change", array("id" => $id,"section" => $data["section"]));
	}

	function select_alias($docid, $entry_id)
	{
		$this->read_template("alias_type.tpl");

		$fb = new form_base;
		$form = $fb->get_form_for_entry($entry_id);

		$opl = $fb->get_op_list($form);

		$this->vars(array(
			"op_sel" => $this->picker("", $opl[$form]),
			"reforb" => $this->mk_reforb("submit_select_alias", array("docid" => $docid, "alias" => $entry_id, "form_id" => $form))
		));
		return $this->parse();
	}

	function submit_select_alias($arr)
	{
		extract($arr);
		$this->add_alias($docid,$alias,serialize(array("type" => $type, "output" => $output, "form_id" => $form_id)));
		return $this->mk_my_orb("change", array("id" => $docid));
	}

	////
	// !Send a link to someone
	function send_link()
	{
		global $from_name, $from, $baseurl, $ext, $section, $copy,$to_name, $to,$comment, $SITE_ID;

		if ($SITE_ID == 5)
		{
			$text = "$from_name ($from) soovitab teil vaadata Pere ja Kodu saidile ".$GLOBALS["baseurl"].",\ntäpsemalt linki ".$GLOBALS["baseurl"]."/index.$ext?section=$section\n\n$from_name kommentaar lingile: $comment\n";

			if ($copy != "")
				$bcc = "\nCc: $copy ";

			mail("\"$to_name\" <".$to.">","Artikkel saidilt ".$GLOBALS["baseurl"],$text,"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");
		}
		else
		if ($SITE_ID == 9)
		{
			$text = "$from_name ($from) soovitab teil vaadata Struktuur Meedia saidile ".$GLOBALS["baseurl"].",\ntäpsemalt linki ".$GLOBALS["baseurl"]."/index.$ext?section=$section\n\n$from_name kommentaar lingile: $comment\n";

			if ($copy != "")
				$bcc = "\nCc: $copy ";

			mail("\"$to_name\" <".$to.">","Artikkel saidilt ".$GLOBALS["baseurl"],$text,"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");
		}
		else
		if ($SITE_ID == 17)
		{
			$text = "$from_name ($from) soovitab teil vaadata Ida-Viru investeerimisportaali ".$GLOBALS["baseurl"].",\ntäpsemalt linki ".$GLOBALS["baseurl"]."/index.$ext?section=$section \n\n$from_name kommentaar lingile: $comment\n";

			if ($copy != "")
				$bcc = "\nCc: $copy ";

			mail("\"$to_name\" <".$to.">","Artikkel saidilt ".$GLOBALS["baseurl"],$text,"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");
		}
		else
		if ($SITE_ID == 71)
		{
			$text = "$from_name ($from) soovitab teil vaadata saiti ".$GLOBALS["baseurl"].",\ntäpsemalt linki ".$GLOBALS["baseurl"]."/index.$ext?section=$section \n\n$from_name kommentaar lingile: $comment\n";

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

	////
	// !buu
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

		$ret = $this->submit_add(array(
			"section" => $section,
			"period" => $period,
			"parent" => $parent,
			"user" => $user
		));
		header("Location: ".$ret);
		die();
	}

	function submit_add($arr)
	{
		$this->quote(&$arr);
		extract($arr);
		if ($docfolder)
		{
			$parent = $docfolder;
		}
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
				case "showlead":
					$vlist[] = "'1'";
					break;
				case "tm":
					$vlist[] = "'".date("d/m/y")."'";
					break;
				case "no_right_pane":
					if ($GLOBALS["SITE_ID"] == 9)
					{
						$vlist[] = "'1'";
					}
					else
					{
						$vlist[] = "'" . $defaults[$v] . "'";
					}
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

		$this->set_object_metadata(array("oid" => $lid, "key" => "show_print", "value" => 1));

		return $this->mk_my_orb("change", array("id" => $lid));
	}

	////
	// !Displays the document edit form
	function change($arr)
	{
		global $baseurl;
		// hmpf, imho voiks see veidi globaalsem kontroll olla, kui
		// ainult siin, selles yhes funktsioonid
		if (!$this->prog_acl("view",PRG_MENUEDIT) && !$this->prog_acl("view",PRG_DOCEDIT))
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

		// jargnev funktsioon kaib rekursiivselt mooda menyysid, kuni leitakse
		// menyy, mille juures on määratud edimistemplate
		$_tpl = $this->get_edit_template($oob["parent"]);
		if (is_number($_tpl))
		{
			$arr["template"] = $_tpl;
			return $this->do_form_change($arr);
		}

		$this->read_template($_tpl);

		// $version indicates that we should load an archived copy of this document
		if ($version)
		{
			classload("archive");
			$arc = new archive();
			$document = $arc->checkout(array("oid" => $id,"version" => $version));
			$addcap = "<span style='color: red'>(arhiivikoopia)</a>";
		}
		else
		{
			$document = $this->fetch($id);
			$addcap = "";
		};
		$this->mk_path($document["parent"],"<a href='/automatweb/orb.aw?class=document&action=change&id=$id'>" . LC_DOCUMENT_CHANGE_DOC . $addcap . "</a>",$document["period"]);
		
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
						$this->vars(array(
							"lang_name" => $v["name"], 
							"chbrourl"	=> $this->mk_my_orb("change", array("id" => $row["docid"])),
							"bro_name"	=> $row["title"],
						));
						$db.=$this->parse("DOC_BROS");
					}
				}
			}
			$this->vars(array("DOC_BROS" => $db));
		}

		$GLOBALS["lang_id"] = $document["lang_id"];

		$alilist = array();
		$jrk = array(
			"Jrk" => "Jrk",
			"-10" => "-10", 
			"-9" => "-9", 
			"-8" => "-8", 
			"-7" => "-7", 
			"-6" => "-6",
			"-5" => "-5", 
			"-4" => "-4", 
			"-3" => "-3", 
			"-2" => "-2", 
			"-1" => "-1",
			"0" => "0", 
			"1" => "1", "2" => "2", "3" => "3",  "4" => "4", "5"  => "5",
								 "6" => "6", "7" => "7", "8" => "8",  "9" => "9", "10" => "10");
		$addfile = $this->mk_my_orb("new",array("id" => $id, "parent" => $document["parent"]),"file");
		$previewlink = "";

		$return_url = $this->mk_my_orb("change", array("id" => $id));
		$this->vars(array(
			"aliasmgr_link" => $this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr"),
		));
		classload("keywords");
		$kw = new keywords();
		$keywords = $kw->get_keywords(array(
									"oid" => $id,
		));

		classload("languages");
		$t = new languages;

		$meta = $this->get_object_metadata(array("oid" => $id));

		$conf = new config;
		$df = $conf->get_simple_config("docfolders".$GLOBALS["LC"]);
		if ($df != "")
		{
			$xml = new xml;
			$_df = $xml->xml_unserialize(array("source" => $df));
			$this->vars(array(
				"docfolders" => $this->picker($oob["parent"],$_df)
			));
		}

    $this->vars(array("title" => str_replace("\"","&quot;",$document["title"]),
											"jrk1"  => $this->picker($document["jrk1"],$jrk),
										  "jrk2"  => $this->picker($document["jrk2"],$jrk),
										  "jrk3"  => $this->picker($document["jrk3"],$jrk),
										  "allparemal" => checked($document["allparemal"] == 1),
										  "esilehel" => checked($document["esilehel"] == 1),
										  "showlead" => checked($document["showlead"] == 1),
										  "esilehel_uudis" => checked($document["esilehel_uudis"] == 1),
											"yleval_paremal" => checked($document["yleval_paremal"] == 1),
											"esileht_yleval" => checked($document["esileht_yleval"] == 1),
											"show_modified" => checked($document["show_modified"] == 1),
											"is_forum" => checked($document["is_forum"] == 1),
											"copyright" => checked($document["copyright"] == 1),
											"lead_comments" => checked($document["lead_comments"] == 1),
											"show_title" => checked($document["show_title"] == 1),
											"show_print" => checked($meta["show_print"]),
											"show_last_changed" => checked($meta["show_last_changed"]),
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
											"reforb"	=> $this->mk_reforb("save", array("id" => $id,"section" => $section,"version" => $version)),
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
											"frontpage_left" => checked($document["frontpage_left"]==1),
											"frontpage_center" => checked($document["frontpage_center"]==1),
											"frontpage_center_bottom" => checked($document["frontpage_center_bottom"]==1),
											"frontpage_right" => checked($document["frontpage_right"]==1),
											"frontpage_left_jrk" => $document["frontpage_left_jrk"],
											"frontpage_center_jrk" => $document["frontpage_center_jrk"],
											"frontpage_center_bottom_jrk" => $document["frontpage_center_bottom_jrk"],
											"frontpage_right_jrk" => $document["frontpage_right_jrk"],
											"no_last" => checked($document["no_last"]),
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


	function delete($arr)
	{
		extract($arr);
		global $period;
		$this->delete_object($id);
		header("Location: ".$this->mk_orb("obj_list", array("parent" => $parent,"period" => $period), "menuedit"));
	}

	////
	// !Handles the document archive
	function show_archive($args = array())
	{
		extract($args);
		$this->read_template("archive.tpl");
		$GLOBALS["site_title"] = "Arhiiv";

		classload("archive");
		$arc = new archive();
		$t = new aw_table(array(
					"prefix" => "mailbox",
					"imgurl"    => $GLOBALS["baseurl"]."/images",
          "tbgcolor" => "#C3D0DC",
				));

		$t->parse_xml_def($GLOBALS["basedir"]."/xml/generic_table.xml");

		$t->set_header_attribs(array(
					"class" => "document",
					"action" => "archive",
					"docid" => $args["docid"],
		));

		$t->define_field(array(
								"name" => "name",
								"caption" => "Nimi",
								"talign" => "center",
								"nowrap" => 1,
								"sortable" => 1,
		));
		
		$t->define_field(array(
								"name" => "uid",
								"caption" => "Muutja",
								"talign" => "center",
								"align" => "center",
								"nowrap" => 1,
								"sortable" => 1,
		));
		
		$t->define_field(array(
								"name" => "date",
								"caption" => "Kuupäev",
								"talign" => "center",
								"nowrap" => 1,
								"sortable" => 1,
		));
		
		$t->define_field(array(
								"name" => "pick",
								"caption" => "Vali",
								"talign" => "center",
								"nowrap" => 1,
								"align" => "center",
		));
		
		$t->define_field(array(
								"name" => "check",
								"caption" => "Default",
								"talign" => "center",
								"align" => "center",
								"nowrap" => 1,
		));

		$contents = $arc->get(array("oid" => $args["docid"]));
		$obj = $this->get_object($args["docid"]);
		$this->mk_path($obj["parent"],"Arhiiv"); 
		$meta = $arc->serializer->php_unserialize($obj["meta"]);

		$current = $this->fetch($args["docid"]);

		$t->define_data(array(
			"name" => sprintf("<a href='%s'><b>%s</b></a>",$this->mk_orb("change",array("id" => $args["docid"])),$current["title"]),
			"uid" => '<b>' . $current["modifiedby"] . '</b>',
			"date" => '<b>' . $this->time2date($current["modified"],9) . '</b>',
			"check" => sprintf("<b><input type='radio' name='default' value='active' checked></b>"),
		));

		if (is_array($contents))
		{
			foreach($contents as $key => $val)
			{
				$name = ($meta["archive"][$key]["name"]) ? $meta["archive"][$key]["name"] : "(nimetu)";
				$link = sprintf("<a href='%s'>%s</a>",$this->mk_orb("change",array("id" => $args["docid"],"version" => $key)),$name);
				$t->define_data(array(
						"name" => $link,
						"uid" => $meta["archive"][$key]["uid"],
						"date" => $this->time2date($val[FILE_MODIFIED],9),
						"check" => sprintf("<input type='radio' name='default' value='%d'>",$key),
						"pick" => sprintf("<input type='checkbox' name='check[%d]' value=1>",$key),

				));
			}
		};

		$t->sort_by(array("field" => ($args["sortby"]) ? $args["sortby"] : "date"));

		$this->vars(array(
			"docid" => $docid,
			"arc_table" => $t->draw(),
			"preview"	=> $this->mk_my_orb("preview", array("id" => $docid)),
			"menurl"	=> $this->mk_orb("sel_menus",array("id" => $docid)),
			"docid" => $docid,
			"reforb" => $this->mk_reforb("submit_archive",array("id" => $docid)),
		));
		return $this->parse();
	}

	////
	// !Submits the archive page
	// id(int) - dokumendi id, millega tegemist on
	// default(mixed) - aktiveeritava versiooni number voi 'active', kui valikut
	// ei muudetud
	function submit_archive($args = array())
	{
		extract($args);
		// now, what we do if default is not active, is to copy the current
		// document from the objects table to archive and also copy the requested
		// copy from archive to the documents table
		if ($delete)
		{
			if (is_array($check))
			{
				foreach($check as $key => $val)
				{


				}
			}
		}


		if ($default != "active")
		{
			$old = $this->fetch($id);

			foreach($this->archive_fields as $afield)
			{
				$arc_data[$afield] = $old[$afield];
			};

			$arc_name = $old["title"];

			classload("archive");
			$arc = new archive();

			$arc->commit(array(
						"oid" => $id,
						"ser_content" => $old,
						"name" => $arc_name,
						"data" => $arc_data,
						"class_id" => CL_DOCUMENT,
			));
			// now the current document is archived, let's restore and older version
			$document = $arc->checkout(array("oid" => $id,"version" => $default));
			$q = "SELECT * FROM documents WHERE docid = '$id'";
			$this->db_query($q);
			// we do this, because we need to know what fields where are in the document table
			$row = $this->db_next();
			$values = array();
			foreach($row as $key => $val)
			{
				if (not(is_number($key)) && ($key != "docid") && ($key != "rec") && $document[$key])
				{
					$values[$key] = " $key = '$document[$key]'";
				};
			};
			$q = sprintf("UPDATE documents SET %s WHERE docid = '%d'",join(",",$values),$id);
			$this->db_query($q);
			$this->_log("document","aktiveeris dokumendi $id arhiiviversiooni");

		};

		return $this->mk_my_orb("archive",array("docid" => $id));
	}

	function preview($arr)
	{
		extract($arr);
		$this->dmsg("a");
		if ($user)
		{
			$this->read_template("preview_user.tpl");
		}
		else
		{
			$this->read_template("preview.tpl");
		}
		$this->dmsg("b");

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
		$al = $this->get_object($alias);
		$obj = $this->get_object($id);
		if ($al["class_id"] == CL_FORM_ENTRY)	// form_entry
		{
			// we must let the user select whether he wants to view or edit the entry
			$this->mk_path($al["parent"],"<a href='pickobject.$ext?docid=$docid&parent=".$al["parent"]."'>Tagasi</a> / Vali aliase t&uuml;&uuml;p");
			return $this->select_alias($id, $alias);
		} 
		elseif ($al["class_id"] == CL_OBJECT_CHAIN)
		{
			classload("object_chain");
			$oc = new object_chain();
			$oc->expl_chain(array("id" => $alias,"parent" => $id));
			header("Location: ".$this->mk_orb("list_aliases",array("id" => $id),"aliasmgr"));
		}
		else
		{
			$this->add_alias($id,$alias);
			header("Location: ".$this->mk_orb("list_aliases",array("id" => $id),"aliasmgr"));
		}
	}

	function list_docs_a($arr)
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
													"doc_title"	=> strip_tags($v),
													"doc_title_s"	=> strip_tags(str_replace("\"","\\\"",$row["title"])),
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
		$ol = $ob->get_list(true);

		classload("config");
		$conf = new config;
		$df = $conf->get_simple_config("docfolders".$GLOBALS["LC"]);
		$xml = new xml;
		$_df = $xml->xml_unserialize(array("source" => $df));

		$ndf = array();

		foreach($_df as $dfid => $dfname)
		{
			$ndf[$dfid] = $ol[$dfid];
		}
		
		if (count($ndf) < 2)
		{
			$ndf = $ol;
		}

		$this->vars(array("docid" => $id,"sections"		=> $this->multiple_option_list($sar,$ndf),
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
				$noid = $this->new_object(array("parent" => $oid,"class_id" => CL_BROTHER_DOCUMENT,"status" => $obj["status"],"brother_of" => $id,"name" => $obj["name"],"comment" => $obj["comment"],"period" => $obj["period"]));
			}
		}

		return $this->mk_orb("sel_menus",array("id" => $id));
	}

	////
	// !deletes alias $id of document $docid and returns to editing the document
	function del_alias($arr)
	{
		extract($arr);
		$ids = explode(";",$id);
		foreach($ids as $real_id)
		{
			$this->delete_alias($docid,$real_id);
		};
		header("Location: ".$this->mk_orb("list_aliases", array("id" => $docid),"aliasmgr"));
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
													"brother" => $this->mk_orb("create_bro", array("parent" => $parent, "id" => $row["oid"], "s_name" => $s_name, "s_content" => $s_content,"period" => $GLOBALS["period"])),
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

	////
	// !creates a brother of document $id under menu $parent 
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
				$noid = $this->new_object(array(
					"parent" => $parent,
					"class_id" => CL_BROTHER_DOCUMENT,
					"status" => 2,
					"brother_of" => $id,
					"name" => $obj["name"],
					"comment" => $obj["comment"],
					"subclass" => $subclass
				));
			}
		}
		if ($no_header)
		{
			return $noid;
		}
		else
		{
			header("Location: ".$this->mk_orb("add_bro", array("parent" => $parent, "s_name" => $s_name,"s_content" => $s_content)));
		}
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

		$row["oid"] = 0;
		$row["parent"] = $parent;
		$row["lang_id"] = $GLOBALS["lang_id"];
		$this->quote(&$row);
		$id = $this->new_object($row);

		$this->upd_object(array("oid" => $id, "brother_of" => $id));

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

	////
	// !Performs a search from all documents
	// parent - alates millisest sektsioonist otsida
	// str - mida otsida
	// section - section id, mille sisu asemel otsingutulemusi naidatakse
	// sortby - mille jargi otsingu tulemusi sortida
	// from - alates millisest vastusest naitama hakatakse?
	function do_search($parent,$str,$sec,$sortby,$from)
	{
		if ($sortby == "")
		{
			$sortby = "percent";
		}

		$this->tpl_init("automatweb/documents");
		// kas ei peaks checkkima ka teiste argumentide oigsust?
		$this->quote(&$str);

		// otsingustringi polnud, redirect veateatele. Mdx, kas selle
		// asemel ei voiks ka mingit custom dokut kasutada?
		if ($str == "")
		{
			$this->read_template("search_none.tpl");
			return $this->parse();
		}

		$this->read_template("search.tpl");

		// genereerime listi koikidest menyydest, samasugune kood on ka
		// mujal olemas, kas ei voiks seda kuidagi yhtlustada?

		// besides, mulle tundub, et otsingutulemusi kuvades taidetakse seda koodi
		// 2 korda .. teine kord menueditit. Tyhi too?
		if ($GLOBALS["lang_menus"] == 1)
		{
			$ss = " AND (objects.lang_id = ".$GLOBALS["lang_id"]." OR menu.type = ".MN_CLIENT.")";
		}
		else
		{
			$ss = "";
		};

		classload("search_conf");
		$sc = new search_conf;
		$search_groups = $sc->get_groups();
		$search_group = $search_groups[$parent];

		$this->menucache = array();
		$this->db_query("SELECT objects.oid as oid, objects.parent as parent,objects.last as last,objects.status as status,objects.metadata as metadata,objects.name as name
										 FROM objects LEFT JOIN menu ON menu.id = objects.oid
										 WHERE objects.class_id = 1 AND objects.status = 2 $ss");
		$parent_list = array();
		while ($row = $this->db_next())
		{
			// peame tshekkima et kui tyyp pole sisse loginud et siis ei otsitaks users only menyyde alt
			$can = true;
			if ($GLOBALS["uid"] == "" && $search_group["no_usersonly"] == 1)
			{
				$meta = $this->get_object_metadata(array("metadata" => $row["metadata"]));
				if ($meta["users_only"] == 1)
				{
					$can = false;
				}
			}
			if ($can)
			{
				$this->menucache[$row["parent"]][] = $row;
				$this->mmc[$row["oid"]] = $row;
			}
		}
		// find the parent menus based on the search menu group id
		$parens = $sc->get_menus_for_grp($parent);
		$this->darr = array();
		$this->marr = array();
		if (is_array($parens))
		{
			foreach($parens as $_parent)
			{
				dbg("parent = $_parent ,name = ".$this->mmc[$_parent]["name"]."<br>");
				if ($this->can("view",$_parent) && is_array($this->mmc[$_parent]))
				{
					$this->marr[] = $_parent;
					// list of default documents
					$this->rec_list($_parent,$this->mmc[$_parent]["name"]);
				};
			}
		};

		$ml = join(",",$this->marr);
		$ml2 = join(",",$this->darr);
		if ($ml != "")
		{
			$ml = " AND objects.parent IN ($ml) ";
		}

		if ($ml2 != "")
		{
			$ml.= " AND objects.oid IN ($ml2) ";
		}
	
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
			$mtals = array();
			$this->db_query("SELECT * FROM aliases WHERE target IN ($mtsstr)");
			while ($row = $this->db_next())
			{
				$mtals[$row[source]] = $row[source];
			}

			$mts = join(",",$mtals);
			if ($mts != "")
			{
				// see on siis nimekiri dokudest, kuhu on tehtud aliased tabelitest, mis matchisid
				$mtalsstr = "OR documents.docid IN (".$mts.")";
			}
			//echo "ms = $mtalsstr<br>";
		}

		$cnt = 0;
		//max number of occurrences of search string in document
		$max_count = 0;
		$docarr = array();

		$plist = join(",",$parent_list);
		$q = "SELECT documents.*,objects.parent as parent, objects.modified as modified 
										 FROM documents 
										 LEFT JOIN objects ON objects.oid = documents.docid
										 WHERE (documents.title LIKE '%".$str."%' OR documents.content LIKE '%".$str."%' $fasstr $mtalsstr) AND objects.status = 2 AND objects.lang_id = ".$GLOBALS["lang_id"]." AND (documents.no_search is null OR documents.no_search = 0) $ml";
		dbg("search_q = $q <br>");
		$this->db_query($q);
		while($row = $this->db_next())
		{
			if (not($this->can("view",$row["docid"])))
			{
				continue;
			};
			// find number of matches in document for search string, for calculating percentage
			// if match is found in title, then multiply number by 5, to emphasize importance
			$c = substr_count(strtoupper($row[content]),strtoupper($str)) + substr_count(strtoupper($row[title]),strtoupper($str))*5;
			$max_count = max($c,$max_count);

			// find the first paragraph of text
			$p1 = strpos($row[content],"<BR>");
			$p2 = strpos($row[content],"</P>");
			$pos = min($p1,$p2);
			$co = substr($row[content],0,$pos);
			$co = strip_tags($co);
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
				$this->vars(array("title"			=> strip_tags($v[title]),
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

		$this->db_query("INSERT INTO searches(str,s_parent,numresults,ip,tm) VALUES('$str','$parent','$cnt','".$GLOBALS["REMOTE_ADDR"]."','".time()."')");

		return $this->parse();
	}

	function rec_list($parent,$pref = "")
	{
		if (!is_array($this->menucache[$parent]))
		{
			return;
		}

		reset($this->menucache[$parent]);
		while(list(,$v) = each($this->menucache[$parent]))
		{
			if ($v["status"] == 2)
			{
				$this->marr[] = $v["oid"];
				if ($v["last"] > 0)
				{
					$this->darr[] = $v["last"];
				}
				dbg("name: ".$pref."/".$v["name"]." id = ".$v["oid"]." <br>");
				$this->rec_list($v["oid"],$pref."/".$v["name"]);
			}
		}
	}

	function list_user_docs($arr)
	{
		extract($arr);
		$this->read_template("user_docs.tpl");

		$ob = new objects;
		$obl = $ob->get_list(false,false,$GLOBALS["rootmenu"]);
		$conf = new config;
		$df = $conf->get_simple_config("docfolders".$GLOBALS["LC"]);
		if ($df != "")
		{
			$xml = new xml;
			$_df = $xml->xml_unserialize(array("source" => $df));
		}

		if (!$parent)
		{
			reset($_df);
			list($parent,$parent) = each($_df);
		}

		global $lang_id;

		$_df = array(0 => "Vaata / Liiguta") + $_df;
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_DOCUMENT." AND status != 0 AND parent = '$parent' AND lang_id = '$lang_id'");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"name" => $row["name"],
				"created" => $this->time2date($row["created"],3),
				"active" => checked($row["status"] == 2),
				"status" => $row["status"],
				"change" => $this->mk_my_orb("change", array("id" => $row["oid"],"section" => $GLOBALS["doc_edit_section"])),
				"parent_name" => $obl[$row["parent"]],
				"docid" => $row["oid"],
				"ord" => $row["jrk"]
			));
			$rd = $this->parse("ROW");
		}

		$this->vars(array(
			"ROW" => $rd,
			"reforb" => $this->mk_reforb("submit_user_docs",array("section" => $GLOBALS["section"])),
			"docfolders" => $this->picker(0,$_df)
		));

		return $this->parse();
	}

	function submit_user_docs($arr)
	{
		extract($arr);

		if (is_array($old_act) && $save != "")
		{
			foreach($old_act as $docid => $stat)
			{
				$this->upd_object(array("oid" => $docid, "status" => ($act[$docid] == 1 ? 2 : 1)));
			}
		}

		if (is_array($ord) && $save != "")
		{
			foreach($ord as $docid => $_ord)
			{
				$this->upd_object(array("oid" => $docid, "jrk" => $_ord));
			}
		}

		if (is_array($sel) && $move != "")
		{
			foreach($sel as $docid => $one)
			{
				if ($one == 1)
				{
					$this->upd_object(array("oid" => $docid, "parent" => $parent));
				}
			}
		}
		return $this->mk_my_orb("list_user_docs",array("parent" => $parent,"section" => $section));
	}

	function lookup($args = array())
	{
		global $SITE_ID;
		extract($args);
		$id = (int)$id;
		$q = "SELECT * FROM objects LEFT JOIN keywordrelations ON (keywordrelations.id = objects.oid) WHERE status = 2 AND class_id IN (" . CL_DOCUMENT . "," . CL_PERIODIC_SECTION . ") AND site_id = '$SITE_ID' AND keywordrelations.keyword_id = '$id' ORDER BY modified DESC";
		$retval = "";
		load_vcl("table");

		$tt = new aw_table(array(
			"prefix" => "keywords",
			"imgurl"    => $GLOBALS["baseurl"]."/img",
			"tbgcolor" => "#C3D0DC",
		));

		$tt->parse_xml_def($GLOBALS["site_basedir"]."/xml/generic_table.xml");
		$tt->set_header_attribs(array(
			"id" => $id,
			"class" => "document",
			"action" => "lookup",
		));
		$tt->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
			"sortable" => 1,
		));
		$tt->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
		));
		$tt->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
		));
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$tt->define_data(array(
				"name" => sprintf("<a href='/?section=%d'>%s</a>",$row["oid"],$row["name"]),
				"modified" => $this->time2date($row["modified"],2),
				"modifiedby" => $row["modifiedby"],
			));
		};
		$tt->sort_by(array("field" => $sortby));
		return $tt->draw();
	}

	function get_last_doc_list($num)
	{
		$tp = "";
		$this->db_query("SELECT objects.oid as oid ,name,objects.modified as modified FROM objects LEFT JOIN documents ON documents.docid = objects.brother_of WHERE (class_id = ".CL_DOCUMENT." OR class_id = ".CL_PERIODIC_SECTION.") AND status = 2 ORDER BY objects.modified DESC LIMIT $num");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"title" => $row["name"],
				"docid" => $row["oid"],
				"modified" => $this->time2date($row["modified"],2)
			));
			$tp.=$this->parse("lchanged");
		}
		$this->vars(array("lchanged" => ""));
		return $tp;
	}

	////
	// !if the menu is set to have a form as document editing template, then we do the editing bit here
	function do_form_change($arr)
	{
		extract($arr);
		$this->read_template("edit_form.tpl");
		$obj = $this->get_object($id);
		$meta = $this->get_object_metadata(array(
			"metadata" => $obj["metadata"]
		));

		$this->mk_path($obj["parent"],"Muuda dokumenti");

		// mida me siis teeme? n2itame formi, mis seal muud ikka. formi entry id paneme doku metadatasse ntx. 

		classload("form");
		$f = new form;
		$f->load($template);
		$co = $f->gen_preview(array(
			"entry_id" => $meta["entry_id"],
			"tpl" => "show_noform.tpl"
		));
		$this->vars(array(
			"id" => $id,
			"aliasmgr_link" => $this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr"),
			"content" => $co,
			"menurl"		=> $this->mk_my_orb("sel_menus",array("id" => $id)),
			"reforb" => $this->mk_reforb("save_form", array("id" => $id,"period" => $period))
		));
		return $this->parse();
	}

	function save_form($arr)
	{
		extract($arr);

		$obj = $this->get_object($id);
		$meta = $this->get_object_metadata(array(
			"metadata" => $obj["meta"]
		));

		classload("form");
		$f = new form;
		$f->process_entry(array(
			"id" => $this->get_edit_template($obj["parent"]),
			"entry_id" => $meta["entry_id"],
		));

		$this->upd_object(array(
			"oid" => $id,
			"name" => $f->entry_name
		));

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "entry_id",
			"value" => $f->entry_id
		));
		return $this->mk_my_orb("change", array("id" => $id,"period" => $period));
	}

	function do_form_show($params)
	{
		extract($params);

		$obj = $this->get_object($docid);
		$meta = $this->get_object_metadata(array(
			"metadata" => $obj["metadata"]
		));

		classload("form");
		$f = new form;

		$tx = $f->show(array(
			"id" => $f->get_form_for_entry($meta["entry_id"]),
			"entry_id" => $meta["entry_id"],
			"op_id" => $tpl
		));
		
		classload("aliasmgr");
		$al = new aliasmgr();
		$al->parse_oo_aliases($docid,&$tx,array());

	}

	function register_parsers()
	{
		
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

		if (defined("PIKK"))
		{
			$class = "events3";
		}
		else
		{
			$class = "events";
		};
		$this->register_sub_parser(array(
					"class" => $class,
					"reg_id" => $mp,
					"function" => "parse_alias",
		));
	}

	function parse_alias($args = array())
	{
		extract($args);
		$d = $alias;
		if ($meta[$d["target"]])
		{
			$replacement = "<a href='/?class=objects&action=show&id=$d[target]'>$d[name]</a>";
		}
		elseif ($alias["aliaslink"] == 1)
		{
			$replacement = sprintf("<a href='/?section=%d'>%s</a>",$d["target"],$d["name"]);
		}
		else
		{
			$replacement = $this->gen_preview(array("docid" => $d["target"]));
		};
		return $replacement;


	}
	
	////
	// !Makes a slice of text NS4 compatible - e.g. makes it look ok.
	// and yes, NS4 is a steaming pile of crap and should die. NS6 is so much better
	function mk_ns4_compat(&$text)
	{
		if ( (substr_count($text,"<P>") > 1) || (substr_count($text,"<p>") > 1) )
		{
			$text = str_replace("</p>","<br><br>",$text);	
			$text = str_replace("</P>","<br><br>",$text);	
		}
		else
		{
			$text = str_replace("</P>","",$text);	
			$text = str_replace("</p>","",$text);	
		}
		
		$text = str_replace("<p>","",$text);
		$text = str_replace("<P>","",$text);
	}

	////
	// !Creates relative links inside the text
	function create_relative_links(&$text)
	{
		// linkide parsimine
		while (preg_match("/(#)(\d+?)(#)(.*)(#)(\d+?)(#)/U",$text,$matches))
		{
			$text = str_replace($matches[0],"<a href='#" . $matches[2] . "'>$matches[4]</a>",$text);
		};

		while(preg_match("/(#)(s)(\d+?)(#)/",$text,$matches))
		{
			$text = str_replace($matches[0],"<a name='" . $matches[3] . "'> </a>",$text);
		};
	}

	////
	// !Creates keyword relations
	// this should be toggled with a preference in site config
	function create_keyword_relations(&$text)
	{
		// FIXME: check whether that query is optimal
		$q = "SELECT keywords.keyword AS keyword,keyword_id FROM keywordrelations 
			LEFT JOIN keywords ON (keywordrelations.keyword_id = keywords.oid) 
			WHERE keywordrelations.id = '$this->docid'";
		$this->db_query($q);
		$keywords = array();
		while($row = $this->db_next())
		{
			$keywords[$row["keyword"]] = sprintf(" <a href='%s' title='%s'>%s<sup><b>*</b></sup></a> ",$this->mk_my_orb("lookup",array("id" => $row["keyword_id"],"section" => $docid),"document"),"LINK",$row["keyword"]);
		}

		if (is_array($keywords))
		{
			// performs the actual search and replace
			foreach ($keywords as $k_key => $k_val)
			{
				$k_key = str_replace("/","\/",$k_key);
				$text = preg_replace("/\b$k_key\b/i",$k_val," " . $text . " ");
			};
		}
	}
};
?>
