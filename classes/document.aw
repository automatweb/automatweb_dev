<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/document.aw,v 2.125 2002/11/07 12:41:04 duke Exp $
// document.aw - Dokumentide haldus. 

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
		
		$this->style_engine = get_instance("aw_style");
		$this->lc_load("document","lc_document");
		lc_load("definition");
			
		// this takes less than 0.1 seconds btw
		$xml_def = $this->get_file(array("file" => $this->cfg["basedir"]."/xml/documents/defaults.xml"));
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
			"no_last" => "no_last",
			"Dok. cache" =>  "dcache",
		);

		// nini. siia paneme nyt kirja v2ljad, mis dokumendi metadata juures kirjas on
		$this->metafields = array("show_print","show_last_changed","show_real_pos","referer","refopt","dcache");

		// for referer checks
		$this->refopts = array("Ignoreeri","N?ita","?ra n?ita");

		// siin on kirjas need v?ljad, mida arhiveeritakse
		$this->archive_fields = array("title","lead","content");

		lc_site_load("document",$this);

		if (isset($GLOBALS["lc_document"]) && is_array($GLOBALS["lc_document"]))
		{
			$this->vars($GLOBALS["lc_document"]);
		}
	}

	////
	// !Sets period to use
	function set_period($period)
	{
		$this->period = $period;	
	}

	// listib k?ik dokumendid
	// iseenesest kahtlane funktsioon, imho ei l?he seda vaja
	function listall()
	{
		$q = "SELECT *, objects.parent as parent FROM documents
			LEFT JOIN objects ON
			(documents.docid = objects.oid)";
		$this->db_query($q);
	}

	////
	// !Listib dokud mingi men?? all
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
	
		// kui staatus on defineerimata, siis n?itame ainult aktiivseid dokumente
		$v.= " AND objects.status = " . (($status == -1) ? 2 : $status);

		if ($row["ndocs"] > 0)
		{
			$lm = "LIMIT ".$row["ndocs"];
		};

		if ($ordby == "")
		{
			$ordby = "objects.period DESC, objects.jrk ASC, objects.modified DESC";
		}
		$q = "SELECT documents.lead AS lead,
			documents.docid AS docid,
			documents.title AS title,
			documents.*,
			objects.period AS period,
			objects.class_id as class_id,
			objects.parent as parent,
			objects.period AS period
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
		if (is_array($docid))
		{
			extract($docid);
		}

		if (not($this->can("view",$docid)))
		{
			$this->data = false;
			return false;
		}

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
			$q = "SELECT objects.*,documents.*,objects.period AS period FROM objects LEFT JOIN documents ON objects.brother_of = documents.docid WHERE objects.oid = $docid AND status != 0 $sufix";
			$this->db_query($q);
		}
		$data = $this->db_next();

		if (gettype($data) == "array") 
		{
			$data["content"] = trim($data["content"]);
			$data["lead"] = trim($data["lead"]);
			$data["cite"] = trim($data["cite"]);
			$data["meta"] = aw_unserialize($data["metadata"]);
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
		$baseurl = $this->cfg["baseurl"];
		$stitle = $this->cfg["stitle"]; 
		$ext = $this->cfg["ext"];
		$rdf = get_instance("rdf",array(
			"about" => "$baseurl/index.$ext/format=rss",
			"link" => "$baseurl/index.$ext",
			"title" => $stitle,
			"description" => $this->cfg["publisher"],
    ));

		extract($args);
		$rootmenu = $this->cfg["rootmenu"];
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
	// !genereerib objekti n? valmiskujul
	// sellest saab wrapper j?rgnevale funktsioonile
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
		
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];

		// check if the menu had a form selected as a template - the difference is that then the template is not a filename
		// but a number
		if (is_number($tpl))
		{
			return $this->do_form_show($params);
		}

		
		// k?sime dokumendi kohta infot
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
		
		// if oid is in the arguments check whether that object is attached to 
		// this document and display it instead of document
		$mk_compat = true;
		$oid = aw_global_get("oid");
		if ($oid)
		{
			$q = "SELECT * FROM aliases WHERE source = '$docid' AND target = '$oid' AND type =" . CL_FILE;
			$this->db_query($q);
			$row = $this->db_next();
			if ($row)
			{
				$fi = get_instance("file");
				$fl = $fi->get_file_by_id($oid);
				$doc["content"] = $fl["content"];
				$doc["lead"] = "";
				$doc["title"] = "";
				$doc["meta"]["show_print"] = 1;
				$mk_compat = false;
			};
		}


		if ($doc["meta"])
		{
			$meta = $doc["meta"];
		}
		else
		{
			$meta = $this->get_object_metadata(array("oid" => $doc["brother_of"]));
		};

		// kas on vaja rakendada refereridel p?hinevat kontrolli?
		if ($meta["refopt"] > 0)
		{
			$referer = aw_global_get("referer");
			$docref = explode(",",$meta["referer"]);
			$match = in_array($referer,$docref);

			$this->referer = $meta["referer"];
			$this->refopt = $meta["refopt"];

			// kui referer matchib ja on k?stud mitte n?idata, siis
			// dropime v?lja
			if ($match && ($meta["refopt"] == 2))
			{
				return false;
			};

			// kui referer ei matchi ja on k?stud n?idata, siis
			// dropime ka v?lja
			if (not($match) && ($meta["refopt"] == 1))
			{
				return false;
			}

		}

		$si = __get_site_instance();
		//hook for site specific document parsing
		if ($si)
		{
			$si->parse_document(&$doc);
		};

		
		//$meta = $doc["meta"];
		if ($meta["show_last_changed"])
		{
			$doc["content"] .= "<p><font size=1><i>Viimati muudetud:&nbsp;&nbsp;</i>" . $this->time2date($doc["modified"],4) . "</font>";
		};
	

		$this->tpl_reset();
		
		$this->tpl_init("automatweb/documents");
		
		// see on sellex et kui on laiem doku, siis menyyeditor tshekib
		// neid muutujaid ja j2tab paani 2ra kui tshekitud on.
		$this->no_right_pane = $doc["no_right_pane"];
		$this->no_left_pane = $doc["no_left_pane"];

	
		// use special template for printing if one is set in the cfg file
		if (aw_global_get("print") && ($this->cfg["print_tpl"]) )
		{
			$tpl = $this->cfg["print_tpl"];
		}

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

		if ( ($meta["show_print"]) && (not($print)) && $leadonly != 1)
		{
			if ($this->cfg["print_cap"] != "")
			{
				$pc = localparse($this->cfg["print_cap"],array(
					"link" => $this->mk_my_orb("print", array("section" => $docid)),
					"docid" => $docid
				));
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
				$request_uri = aw_global_get("REQUEST_URI");
				$pos = strpos($request_uri, "&");
				if ($pos === false)
				{ 
					$link = $request_uri . "?print=1";
				}
				else
				{
					$link = $request_uri . "&print=1";
				}
				$this->vars(array(
					"docid" => $docid,
					//"printlink" => $this->mk_my_orb("print",array("section" => $docid,"oid" => $oid),"document",0,1),
					"printlink" => $link,
				));
				aw_global_set("no_menus",1);
				$_tmp = $this->parse("PRINTANDSEND");
				$this->vars(array("PRINTANDSEND" => $_tmp));
			};
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

		if ($mk_compat)
		{
			$this->mk_ns4_compat(&$doc["lead"]);
			$this->mk_ns4_compat(&$doc["content"]);
		}

		// miski kahtlane vark siin. Peaks vist sellele ka cachet rakendama?
		if (!(strpos($doc["content"], "#telekava_") === false))
		{
			return $this->telekava_doc($doc["content"]);
		}

		// ut 6pilaste tabel
		if (strpos($doc["content"], "#ut_opilased#") !== false)
		{
			$ust = get_instance("ut_stat_table");
			$doc["content"] = str_replace("#ut_opilased#", $ust->draw($section), $doc["content"]);
		}

		$doc["content"] = str_replace("#nool#", '<IMG SRC="{VAR:baseurl}/img/icon_nool.gif" WIDTH="21" HEIGHT="9" BORDER=0 ALT="">', $doc["content"]);

		// in_archive disappears if we move around in archives
		// so we need another way to determine whether this document belongs to the active
		// period

		$db_periods = get_instance("periods",$this->cfg["per_oid"]);
		$act_per = $db_periods->get_active_period($this->cfg["per_oid"]);
		$this->title = $doc["title"];

		//if (aw_global_get("in_archive"))
		if ($doc["period"] != $act_per)
		{
			$doc["content"] = str_replace("#poolita#", "",$doc["content"]);
		}
		else
		{
			if (!(($pp = strpos($doc["content"],"#poolita#")) === false))
			{
				$def = "<br><B>Edasi loe ajakirjast!</b></font>";
				if ($this->cfg["poolita_text"] != "")
				{
					$def = $this->cfg["poolita_text"];
				}
				$doc["content"] = substr($doc["content"],0,$pp).$def;
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
		// kui vaja on n?idata ainult dokumendi leadi, siis see tehakse siin
 		if ($leadonly > -1) 
		{
			// stripime pildid v?lja. 
			if ($strip_img) 
			{
				// ja stripime leadist *koik* objektitagid v?lja.
				$this->vars(array("imurl" => "/images/trans.gif"));
				$doc["lead"] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc["lead"]);
			};
			// damn, that did NOT make any sense at all - terryf
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
						$img = get_instance("image");
						$idata = $img->get_img_by_oid($docid,$match[1]);
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
			if(aw_global_get("uid") != "")
			{
				$socket = fsockopen("aw.struktuur.ee", 10020,$errno,$errstr,10);
				fputs($socket,"NIMI ".aw_global_get("uid")."\n");
				fclose($socket);
			}
		}

		// create keyword links unless we are in print mode, since you cant click
		// on links on the paper they dont make sense there :P
		if ($this->cfg["keyword_relations"] && not($print) && $params["keywords"])
		{
			$this->create_keyword_relations(&$doc["content"]);
			$this->create_keyword_relations(&$doc["lead"]);
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
			$mb = get_instance("msgboard");
			$doc["content"] = str_replace("#board_last5#",$mb->mk_last5(),$doc["content"]);
		}

		// used in am - shows all documents whose author field == current documents title field
		if (!(strpos($doc["content"], "#autori_dokumendid#") === false))
		{
			$doc["content"] = str_replace("#autori_dokumendid#",$this->author_docs($doc["title"]),$doc["content"]);
		}

		// #top# link - viib doku yles
		$top_link = $this->parse("top_link");
		$doc["content"] = str_replace("#top#", $top_link,$doc["content"]);

		// noja, mis fucking "undef" see siin on?
		// damned if I know , v6tax ta 2kki 2ra siis? - terryf 
		$al = get_instance("aliasmgr");

		if (!isset($text) || $text != "undef") 
		{
			$al->parse_oo_aliases($doc["docid"],&$doc["content"],array("templates" => &$this->templates,"meta" => &$meta));
			$doc["content"] = $this->parse_aliases(array(
		    "oid" => $docid,
        "text" => $doc["content"],
      ));

			// this damn ugly-ass hack is here because we need to be able to put the last search value
			// from form_table to document title
			if (aw_global_get("set_doc_title") != "")
			{
				$doc["title"] = aw_global_get("set_doc_title");
				aw_global_set("set_doc_title","");
			}

			$this->vars($al->get_vars());
		}; 

		if (!$doc["nobreaks"])	// kui wysiwyg editori on kasutatud, siis see on 1 ja pole vaja breike lisada
		{
			$doc["content"] = str_replace("\r\n","<br>",$doc["content"]);
		}

		$pb = "";
		if ($doc["photos"])
		{
			if ($this->cfg["link_authors"] && ($this->templates["pblock"]))
			{
				$x = $this->get_relations_by_field(array(
					"field"    => "name",
					"keywords" => strip_tags($doc["photos"]),
					"section"  => $this->cfg["link_authors_section"]
				));
				$authors = array();
				while(list($k,$v) = each($x)) 
				{
					if ($this->cfg["link_default_link"] != "")
					{
						if ($v) 
						{
							$authors[] = sprintf("<a href='/index.$ext?section=%s'>%s</a>",$v,$k);
						} 
						else 
						{
							$authors[] = sprintf("<a href='%s'>%s</a>",$this->cfg["link_default_link"],$k);
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
		$t = get_instance("msgboard");
		$nc = $t->get_num_comments($doc["docid"]);
		$nc = $nc < 1 ? "0" : $nc;
		$doc["content"] = str_replace("#kommentaaride arv#",$nc,$doc["content"]);
		*/

		// <mail to="bla@ee">lahe tyyp</mail>
 		$doc["content"] = preg_replace("/<mail to=\"(.*)\">(.*)<\/mail>/","<a class='mailto_link' href='mailto:\\1'>\\2</a>",$doc["content"]);
		$doc["content"] = str_replace(LC_DOCUMENT_CURRENT_TIME,$this->time2date(time(),2),$doc["content"]);

		if (!(strpos($doc["content"],"#liitumisform") === false))
		{
			$qt = false;
			if (!preg_match("/#liitumisform info=\"(.*)\"#/",$doc["content"], $maat))
			{
				preg_match("/#liitumisform info=&quot;(.*)&quot;#/",$doc["content"], $maat);
				$qt = true;
			}

			// siin tuleb n2idata kasutaja liitumisformi, kuhu saab passwordi ja staffi kribada.
			// aga aint sel juhul, kui kasutaja on enne t2itnud k6ik miski grupi formid.
			$dbu = get_instance("users");
			if ($qt)
			{
				$doc["content"] = preg_replace("/#liitumisform info=&quot;(.*)&quot;#/",$dbu->get_join_form($maat[1]),$doc["content"]);
			}
			else
			{
				$doc["content"] = preg_replace("/#liitumisform info=\"(.*)\"#/",$dbu->get_join_form($maat[1]),$doc["content"]);
			}
		}
				
		$ab = "";
		// I hate the next block of code
		if ($doc["author"]) 
		{
			if ($this->cfg["link_authors"] && isset($this->templates["ablock"])) 
			{
				$x = $this->get_relations_by_field(array(
					"field"    => "name",
					"keywords" => $doc["author"],
					"section"  => $this->cfg["link_authors_section"]
				));
				$authors = array();
				while(list($k,$v) = each($x)) 
				{
					if ($this->cfg["link_default_link"] != "")
					{
						if ($v)
						{
							$authors[] = sprintf("<a href='%s'>%s</a>",document::get_link($v),$k);
						} 
						else 
						{
							$authors[] = sprintf("<a href='%s'>%s</a>",$this->cfg["link_default_link"],$k);
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
				"comm_link" => $this->mk_my_orb("show_threaded",array("board" => $docid,"section" => aw_global_get("section")),"forum"),
			));
			$forum = get_instance("forum");
			$fr = $forum->add_comment(array("board" => $docid));

			if ($num_comments > 0)
			{
				$this->vars(array("FORUM_ADD_SUB" => $this->parse("FORUM_ADD_SUB")));
			}
			$this->vars(array("FORUM_ADD_SUB_ALWAYS" => $this->parse("FORUM_ADD_SUB_ALWAYS")));
		}
		else
		{
			$this->vars(array("FORUM_ADD_SUB_ALWAYS" => ""));
			$this->vars(array("FORUM_ADD_SUB" => ""));
		}

		$langs = "";
		$l = get_instance("languages");
		$larr = $l->listall();
		reset($larr);
		while (list(,$v) = each($larr))
		{
			$this->vars(array("lang_id" => $v["id"], "lang_name" => $v["name"]));
			if (aw_global_get("lang_id") == $v["id"])
			{
				$langs.=$this->parse("SEL_LANG");
			}
			else
			{
				$langs.=$this->parse("LANG");
			}
		}

		$lc = "";
		if ($doc["lead_comments"]==1)
		{
			$lc = $this->parse("lead_comments");
		}

		if ($doc["parent"])
		{
			$mcache = get_instance("menu_cache");
			$mn = $mcache->get_cached_menu($doc["parent"]);
			$this->vars(array(
				"parent_name" => $mn["name"]
			));
		}

		if (!isset($this->doc_count))
		{
			$this->doc_count = 0;
		}

		$title = $doc["title"];
		if ($this->cfg["capitalize_title"])
		{
			// switch to estonian locale
			$old_loc = setlocale(LC_CTYPE,0);	
			setlocale(LC_CTYPE, 'et_EE');

			$title = strtoupper($title);

			// switch back to estonian
			setlocale(LC_CTYPE, $old_loc);
		}
		classload("image");
		$this->vars(array(
			"title"	=> $title,
			"menu_image" => image::check_url($mn["img_url"]),
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

		if ($title != "")
		{
			$this->vars(array(
				"TITLE_NOT_EMPTY" => $this->parse("TITLE_NOT_EMPTY")
			));
		}
		else
		{
			$this->vars(array(
				"TITLE_NOT_EMPTY" => ""
			));
		}

		if ($leadonly > -1 && $doc["title_clickable"])
		{
			$this->vars(array("TITLE_LINK_BEGIN" => $this->parse("TITLE_LINK_BEGIN"), "TITLE_LINK_END" => $this->parse("TITLE_LINK_END")));
		}

		if ($doc["channel"] != "")
		{
			$this->vars(array("HAS_CHANNEL" => $this->parse("HAS_CHANNEL")));
		}

		$this->vars(array(
			"SHOW_TITLE" 	=> ($doc["show_title"] == 1) ? $this->parse("SHOW_TITLE") : "",
			"EDIT" 		=> ($this->prog_acl("view",PRG_MENUEDIT)) ? $this->parse("EDIT") : "",
			"SHOW_MODIFIED" => ($doc["show_modified"]) ? $this->parse("SHOW_MODIFIED") : "",
			"COPYRIGHT"	=> ($doc["copyright"]) ? $this->parse("COPYRIGHT") : "",
			"logged" => (aw_global_get("uid") != "" ? $this->parse("logged") : "")
			));
		
		// keeleseosed
		if ($this->is_template("LANG_BRO"))
		{
			$lab = unserialize($doc["lang_brothers"]);
			$langs = "";
			$l = get_instance("languages");
			$larr = $l->listall();
			reset($larr);
			while (list(,$v) = each($larr))
			{
				if ($lab[$v["id"]])
				{
					$this->vars(array("lang_id" => $v["id"], "lang_name" => $v["name"],"section" => $lab[$v["id"]]));
					if (aw_global_get("lang_id") == $v["id"])
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
			classload("file");
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
							"url" => file::get_url($ar["target"],$fif["name"]),
							"im" => $im == "" ? "fil" : $im
						));
 
						$fff.=$this->parse("FILE");
					}
				}
			}
			$this->vars(array("FILE" => $fff));
		}
 

		$retval = $this->parse();

		if (aw_global_get("print") && $this->cfg["remove_links_from_print"])
		{
			$retval = preg_replace("/<a(.*)>/U", "", $retval);
			$retval = str_replace("</a>", "", $retval);
		}
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
				$q = "SELECT docid,title,keywords FROM documents WHERE ".join(" OR ",$qparts);
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
	
	// kysib "sarnaseid" dokusid mingi v?lja kaudu
	// XXX
	function get_relations_by_field($params) 
	{
		$field = $params["field"]; // millisest v?ljast otsida
		$keywords = split(",",$params["keywords"]); // mida sellest v?ljast otsida,
																		// comma separated listi
		$section = $params["section"]; // millisest sektsioonist otsida
		// kui me midagi ei otsi, siis pole siin midagi teha ka enam. GET OUT!
		if (!is_array($keywords)) 
		{
			return false;
		} 
		else 
		{
			// moodustame p?ringu dokude (v6i menyyde) saamiseks, mis vastavad meile
			// vajalikule tingimusele
			$retval = array();
			while(list($k,$v) = each($keywords)) 
			{
				// fields may contain HTML and we don't want that
				$v = trim(strip_tags($v));
				if (is_array($section))
				{
					$prnt = "parent IN (".join(",",$section).")";
				}
				else
				{
					$prnt = "parent = $section";
				}
				$q = "SELECT oid FROM objects
							WHERE $prnt AND $field LIKE '%$v%' AND objects.status = 2 AND objects.class_id = ".CL_PSEUDO;
//				$this->db_query($q);
//					echo "vv = <pre>",var_dump($this->db_next()),"</pre> q = $q  <br>";
				$retval[$v] = $this->db_fetch_field($q,"oid");
				if (!$retval[$v])
				{
					$q = "SELECT oid FROM objects
								WHERE $prnt AND $field LIKE '%$v%' AND objects.status = 2 AND objects.class_id = ".CL_DOCUMENT;
					$retval[$v] = $this->db_fetch_field($q,"oid");
				}
			}; // eow
//			echo "retval = <pre>", var_dump($retval),"</pre> <br>";
			return $retval;
		}; // eoi
	}

	////
	// !Salvestab dokumendi
	function save($data) 
	{
		// id (docid) on ainuke parameeter, mis *peab* olema kaasa antud
		// ja siis veel v?hemalt yx teine parameeter mida muuta

		// fetchime vana dokumendi, et seda arhiivi salvestada
		$old = $this->fetch($data["id"]);
		// $data["archive"] means that "archive" checkbox was checked and that we should make 
		// a copy of the current document to the archive

		// $data["version"] means that we are working on a archive copy of a document and
		// should therefore save the changes to archive and not to the document table
		if (aw_ini_get("archive.use"))
		{
			$arc = get_instance("archive");
		};

		if (aw_ini_get("archive.use") && ($data["archive"]) )
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
		
		if (aw_ini_get("archive.use") && ($data["version"]) )
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
			$this->_log("document","muutis dokumenti <a href='".$this->cfg["baseurl"]."/automatweb/".$this->mk_orb("change", array("id" => $id))."'>'".$data["title"]."'</a> arhiivikoopiat");
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
			$kw = get_instance("keywords");
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
				// paneme v?ljade nimed ka kirja, et formeerida logi
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
					if ($ts)
					{
						$modified = $ts;
					}
				}
			}
		}
		$q_parts[] = "modified = $modified";

		// see paneb siis paringu kokku. Whee.
		if ($old["brother_of"])
		{
			$q = "UPDATE documents SET " . join(",\n",$q_parts) . " WHERE docid = '".$old["brother_of"]."'"; 
			$this->db_query($q);
		}
		
		// siia moodustame objektitabeli p?ringu osad
		$oq_parts = array();

		$obj_known_fields = array("name","visible","status","parent");

		// seda on j?rgneva p?ringu koostamiseks vaja, sest objektitabelis pole "title"
		// v?lja. On "name"
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

		if (not(preg_match("/\W/",$data["alias"])))
		{
			$oq_parts["alias"] = $data["alias"];
		};

		if ($modified)
		{
			$oq_parts["modified"] = $modified;
		};

		$this->upd_object($oq_parts);

		// uuendame vennastatud dokude nimed ka
		if ($old["brother_of"])
		{
			$this->db_query("UPDATE objects SET name='".$data["name"]."' WHERE brother_of = '".$old["brother_of"]."'");
		}

		// nih, updateme objekti metadata ka 2ra.
		foreach($this->metafields as $m_name => $m_key)
		{
			$this->set_object_metadata(array("oid" => $id, "key" => $m_key, "value" => $data[$m_key]));
		}
		
		if ($this->cfg["use_dcache"] && $data["dcache"])
		{
			$preview = $this->gen_preview(array("docid" => $id));
			$this->quote($preview);
			$q = "UPDATE documents SET dcache = '$preview'  WHERE docid = '$id'";
			$this->db_query($q);
		};

		// and if the user has checked the checkbox, we should generate the static pages for the document, the parent menu
		// and all the document's brothers
		if ($data["gen_static"])
		{
			$this->gen_static_doc($id);
		}

		$this->flush_cache();

		// logime aktsioone
		$this->_log("document","muutis dokumenti <a href='".$this->cfg["baseurl"]."/automatweb/".$this->mk_orb("change", array("id" => $id))."'>'".$data["title"]."'</a>");

		return $this->mk_my_orb("change", array("id" => $id,"section" => $data["section"]),"",false,true);
	}

	////
	// !Send a link to someone
	function send_link()
	{
		global $from_name, $from, $section, $copy,$to_name, $to,$comment;

		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		$SITE_ID = $this->cfg["site_id"];

		if ($SITE_ID == 5)
		{
			$text = "$from_name ($from) soovitab teil vaadata Pere ja Kodu saidile ".$baseurl.",\nt?psemalt linki ".$baseurl."/index.$ext?section=$section\n\n$from_name kommentaar lingile: $comment\n";

			if ($copy != "")
				$bcc = "\nCc: $copy ";

			mail("\"$to_name\" <".$to.">","Artikkel saidilt ".$baseurl,$text,"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");
		}
		else
		if ($SITE_ID == 17)
		{
			$text = "$from_name ($from) soovitab teil vaadata Ida-Viru investeerimisportaali ".$baseurl.",\nt?psemalt linki ".$baseurl."/index.$ext?section=$section \n\n$from_name kommentaar lingile: $comment\n";

			if ($copy != "")
				$bcc = "\nCc: $copy ";

			mail("\"$to_name\" <".$to.">","Artikkel saidilt ".$baseurl,$text,"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");
		}
		else
		if ($SITE_ID == 71)
		{
			$text = "$from_name ($from) soovitab teil vaadata saiti ".$baseurl.",\nt?psemalt linki ".$baseurl."/index.$ext?section=$section \n\n$from_name kommentaar lingile: $comment\n";

			if ($copy != "")
				$bcc = "\nCc: $copy ";

			mail("\"$to_name\" <".$to.">","Artikkel saidilt ".$baseurl,$text,"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");
		}
		else
		{
			$text = "$from_name ($from) soovitab teil vaadata N?dala saidile www.nadal.ee,\nt?psemalt linki http://www.nadal.ee/index.$ext?section=$section\n\n$from_name kommentaar lingile: $comment\n";

			if ($copy != "")
			{
				$bcc = "\nCc: $copy ";
			}


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

		$t = get_instance("tvkavad");
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

		$ob = get_instance("objects");

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
				{
					$not_changed[$v] = $v;
				}
				else
				{
					$added[$v] = $v;
				}
				$a[$v]=$v;
			}
		}
		$deleted = array();
		reset($sar);
		while (list($oid,) = each($sar))
		{
			if (!$a[$oid])
			{
				$deleted[$oid] = $oid;
			}
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
				$noid = $this->new_object(array(
					"parent" => $oid,
					"class_id" => CL_BROTHER_DOCUMENT,
					"status" => 1,
					"brother_of" => $docid,
					"name" => $obj["name"],
					"comment" => $obj["comment"]
				));
			}
		}

		return $obj["parent"];
	}

	function add($arr)
	{
		extract($arr);
		$per_oid= $this->cfg["per_oid"];
		global $period;

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
		} 
		else 
		{
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
		if ($period) 
		{
			$q = "INSERT INTO menu (id,type,periodic) VALUES ('$lid','99','1')";
		} 
		else 
		{
			$q = "INSERT INTO menu (id,type) VALUES ('$lid','99')";
		};
		$defaults = $this->fetch($parent);
		$flist = array();
		$vlist = array();
		while(list($k,$v) = each($this->knownfields)) 
		{
			if ($v != "dcache")
			{
				$flist[] = $v;
			};
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
					if ($this->cfg["site_id"] == 9)
					{
						$vlist[] = "'1'";
					}
					else
					{
						$vlist[] = "'" . $defaults[$v] . "'";
					}
					break;
				case "dcache":	
					break;
				default:
					$vlist[] = "'" . $defaults[$v] . "'";
			};
		};

		$flist[] = "lang_id";
		$vlist[] = "'".aw_global_get("lang_id")."'";

		if (is_array($flist) && (sizeof($flist) > 0)) 
		{
			$part1 = "," . join(",",$flist);
			$part2 = "," . join(",",$vlist);
		} 
		else 
		{
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
		$baseurl = $this->cfg["baseurl"];
		// hmpf, imho voiks see veidi globaalsem kontroll olla, kui
		// ainult siin, selles yhes funktsioonid
		if (!$this->prog_acl("view",PRG_DOCEDIT))
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
		// menyy, mille juures on m??ratud edimistemplate
		$_tpl = $this->get_edit_template($oob["parent"]);
		if (is_number($_tpl))
		{
			$arr["template"] = $_tpl;
			return $this->do_form_change($arr);
		}
		global $DBG;
		if ($DBG)
		{
			printf("using %s/%s<br>",$this->template_dir,$_tpl);
		};
		$this->read_template($_tpl);

		// $version indicates that we should load an archived copy of this document
		if ($version)
		{
			$arc = get_instance("archive");
			$document = $arc->checkout(array("oid" => $id,"version" => $version));
			$addcap = "<span style='color: red'>(arhiivikoopia)</a>";
		}
		else
		{
			$document = $this->fetch($id);
			$addcap = "";
		};
		$this->mk_path($document["parent"],"<a href='/automatweb/orb.".$this->cfg["ext"]."?class=document&action=change&id=$id'>" . LC_DOCUMENT_CHANGE_DOC . $addcap . "</a>",$document["period"]);
		
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
			$t = get_instance("languages");
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

// this will seriously fuck up shit, because document.lang_id will override objects.lang_id :(
//		$l = get_instance("languages");
//		$l->set_active($document["lang_id"],true);

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
		$kw = get_instance("keywords");
		$keywords = $kw->get_keywords(array(
			"oid" => $id,
		));

		$t = get_instance("languages");

		$meta = $this->get_object_metadata(array("oid" => $id));

		$conf = get_instance("config");
		$df = $conf->get_simple_config("docfolders".aw_global_get("LC"));
		if ($df != "")
		{
			$xml = get_instance("xml");
			$_df = $xml->xml_unserialize(array("source" => $df));
			$this->vars(array(
				"docfolders" => $this->picker($oob["parent"],$_df)
			));
		}
		$is_ie = false;
		if (!(strpos(aw_global_get("HTTP_USER_AGENT"),"MSIE") === false))
		{
			$is_ie = true;
		}

    $this->vars(array("title" => ($is_ie ? str_replace("\"","&quot;",$document["title"]) : $document["title"]),
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
										  "lead"    => ($is_ie ? str_replace("\"","&quot;",trim($document["lead"])) : $document["lead"]),
											"alias" => $document["alias"],
											"content" => ($is_ie ? str_replace("\"","&quot;",trim($document["content"])) : $document["content"]),
											"channel"	=> trim($document["channel"]),
											"nobreaks"	=> $document["nobreaks"],
											"tm"			=> trim($document["tm"]),
											"subtitle"			=> trim($document["subtitle"]),
											"link_text" => trim($document["link_text"]),
											"reforb"	=> $this->mk_reforb("save", array("id" => $id,"section" => $section,"version" => $version)),
											"id" => $id,
											"docid" => $id,
											"previewlink" => $previewlink,
											"weburl" => $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$document["docid"],
											"lburl"		=> $this->mk_my_orb("sellang", array("id" => $id)),
											"long_title"	=> $document["long_title"],
											"menurl"		=> $this->mk_my_orb("sel_menus",array("id" => $id)),
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
											"referer" => $meta["referer"],
											"refopts" => $this->picker($meta["refopt"],$this->refopts),
											"dcache" => checked($meta["dcache"]),
											));


		// detect browser and if it's compatible, use WYSIWYG editor
		if (!(strpos(aw_global_get("HTTP_USER_AGENT"),"MSIE") === false))
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
		$this->mk_path(0,"Arhiiv");

		$arc = get_instance("archive");
		$t = new aw_table(array(
			"prefix" => "mailbox",
			"tbgcolor" => "#C3D0DC",
		));
		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
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
			"caption" => "Kuup?ev",
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

		$t->set_default_sortby("date");

		$contents = $arc->get(array("oid" => $args["docid"]));
		$obj = $this->get_object($args["docid"]);
		$this->mk_path($obj["parent"],"Arhiiv"); 
		//$meta = $arc->serializer->php_unserialize($obj["meta"]);
		$meta = $obj["meta"];

		$current = $this->fetch($args["docid"]);

		$ctitle = strip_tags($current["title"]);

		$t->define_data(array(
			"name" => sprintf("<a href='%s'><b>%s</b></a>",$this->mk_orb("change",array("id" => $args["docid"])),$ctitle),
			"uid" => '<b>' . $current["modifiedby"] . '</b>',
			"date" => '<b>' . $this->time2date($current["modified"],9) . '</b>',
			"check" => sprintf("<b><input type='radio' name='default' value='active' checked></b>"),
		));

		if (is_array($contents))
		{
			foreach($contents as $key => $val)
			{
				$name = ($meta["archive"][$key]["name"]) ? $meta["archive"][$key]["name"] : "(nimetu)";
				$name = strip_tags($name);
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

		
		$t->sort_by();
		$this->vars(array(
			"docid" => $docid,
			"arc_table" => $t->draw(),
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

			$arc = get_instance("archive");

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

	function sel_lang_bros($arr)
	{
		extract($arr);
		$this->read_template("lang_bros.tpl");
		$this->sub_merge = 1;

		global $sstring,$slang_id;

		$document = $this->fetch($id);
		$this->mk_path($document["parent"],LC_DOCUMENT_LANG);

		$lang_brothers = unserialize($document["lang_brothers"]);
		$t = get_instance("languages");
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

		$this->vars(array(
			"reforb" => $this->mk_reforb("seb_s",array("id" => $id)),
			"sstring"	=> $sstring,
			"menurl"	=> $this->mk_orb("sel_menus",array("id" => $id)),
			"weburl"	=> $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$id,
			"change"	=> $this->mk_orb("change", array("id" => $id)),
			"lburl"		=> $this->mk_orb("sellang", array("id" => $id))
		));

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
		{
			$this->extrarr[$row["parent"]][] = array("docid" => $row["docid"], "name" => $row["title"].".".$this->cfg["ext"]);
		}

		$this->docs = array("0" => "");
		$this->mk_folders($this->cfg["admin_rootmenu2"],"");


		reset($this->docs);
		while (list($k,$v) = each($this->docs))
		{
			$this->vars(array("name" => $v, "selurl" => $this->mk_orb("set_lang_bro", array("id" => $id, "bro" => $k,"sstring" => $sstring, "slang_id" => $slang_id)),"id" => $k));
			if ($lang_brothers[$slang_id] == $k)
			{
				$mt.=$this->parse("MATCH_SEL");
			}
			else
			{
				$mt.=$this->parse("MATCH");
			}
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("seb_s",array("id" => $id)),
			"sstring"	=> $sstring,
			"MATCH" => $mt,
			"MATCH_SEL" => ""
		));
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
			{
				$sstring = "|||||||||||||||||||||||||||";
			}

			$ko = " AND documents.title LIKE '%$sstring%' AND documents.content LIKE '%$sstring2%' ";
		}

		$this->mk_menucache(aw_global_get("lang_id"));

		// dokumentide list
		$this->extrarr = array();
		$prd = ($arr["period"]) ? $arr["period"] : 0;
		$sub_sel = false;
		$this->db_query("SELECT documents.*,documents.is_forum as is_forum,documents.esilehel as esilehel,documents.esilehel_uudis as esilehel_uudis,
										 documents.showlead as showlead, objects.status as status,objects.parent as parent,
										 objects.jrk as jrk, objects.modified as modified, objects.modifiedby as modifiedby
										 FROM documents
										 LEFT JOIN objects ON objects.brother_of = documents.docid
										 WHERE objects.period = $prd AND objects.lang_id=".aw_global_get("lang_id")." and site_id = ".aw_global_get("site_id")." $ko
										 ORDER BY objects.parent,jrk");
		while ($row = $this->db_next()) 
		{
			$this->extrarr[$row["parent"]][] = array("docid" => $row["docid"], "name" => $row["title"].".".$this->cfg["ext"]);
			$this->docarr[$row["docid"]] = $row;
		}
			
		$this->docs = array();
		$this->mk_folders($this->cfg["admin_rootmenu2"],"");

		reset($this->docs);
		while (list($k,$v) = each($this->docs))
		{
			$row = $this->docarr[$k];
			$this->vars(array(
				"doc_id"		=> $row["docid"],
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
				"link"				=> "<a href='".$this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$row["docid"]."'>url</a>",
				"doc_default"	=> ($this->sel_doc == $row["docid"] ? "CHECKED" : ""),
				"gee"			=> $dcnt & 1 ? "" : "_g"));

				$dd = $this->parse("D_DELETE");
				$dc = $this->parse("D_CHANGE");
				$da = $this->parse("D_ACL");
				$on2.= $this->parse("ONAME2");

				$this->vars(array(
					"D_DELETE" => $dd,
					"D_CHANGE" => $dc,
					"D_ACL" => $da
				));
				$this->parse("FLINE");
				if ($this->sel_doc == $row["docid"])
				{
					$sub_sel = true;
				}
				$dcnt++;
		}

		$ob = get_instance("objects");
	 
		$this->vars(array(
			"default_doc" => $default_doc,
			"dest"				=> $dest,
			"doc_default"	=> ($sub_sel == false ? "CHECKED" : "" ),
			"ONAME2" => $on2,
			"period" => $arr["period"]
		));
		return $this->parse();
	}

		function mk_folders($parent,$str)
		{
			if (!is_array($this->menucache[$parent]))
			{
				return;
			}

			reset($this->menucache[$parent]);
			while(list(,$v) = each($this->menucache[$parent]))
			{
				$name = $v["data"]["name"];
				if ($v["data"]["parent"] == 1)
				{
					$words = explode(" ",$name);
					if (count($words) == 1)
					{
						$name = $words[0][0].$words[0][1];
					}
					else
					{
						reset($words);
						$mstr = "";
						while(list(,$v3) = each($words))
						{
							$mstr.=$v3[0];
						}
						$name = $mstr;
					}
				}

				$sep = ($str == "" ? "" : " / ");
				$tstr = $str.$sep.$name;

				if (is_array($this->extrarr[$v["data"]["oid"]]))
				{
					reset($this->extrarr[$v["data"]["oid"]]);
					while (list(,$v2) = each($this->extrarr[$v["data"]["oid"]]))
					{
						$this->docs[$v2["docid"]] = $tstr." / ".$v2["name"];
					}
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
		{
			$sar[$arow["parent"]] = $arow["parent"];
		}

		$ob = get_instance("objects");
		$ol = $ob->get_list(true);

		$conf = get_instance("config");
		$df = $conf->get_simple_config("docfolders".aw_global_get("LC"));
		$xml = get_instance("xml");
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

		$this->vars(array(
			"docid" => $id,"sections"		=> $this->multiple_option_list($sar,$ndf),
			"reforb"	=> $this->mk_reforb("submit_menus",array("id" => $id)),
			"menurl"	=> $this->mk_orb("sel_menus",array("id" => $id)),
			"weburl"	=> $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$id,
			"change"	=> $this->mk_orb("change", array("id" => $id)),
			"lburl"		=> $this->mk_orb("sellang", array("id" => $id))
		));
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
				{
					$not_changed[$v] = $v;
				}
				else
				{
					$added[$v] = $v;
				}
				$a[$v]=$v;
			}
		}
		$deleted = array();
		reset($sar);
		while (list($oid,) = each($sar))
		{
			if (!$a[$oid])
			{
				$deleted[$oid] = $oid;
			}
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
		$row["lang_id"] = aw_global_get("lang_id");
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
	function do_search($arr = array())
	{
		extract($arr);
		if ($sortby == "")
		{
			$sortby = "percent";
		}

		$str = trim($str);
		$this->tpl_init("automatweb/documents");
		// kas ei peaks checkkima ka teiste argumentide oigsust?
		$ostr = $str;
		$this->quote(&$str);

		// otsingustringi polnud, redirect veateatele. Mdx, kas selle
		// asemel ei voiks ka mingit custom dokut kasutada?
		if ($str == "")
		{
			$this->read_template("search_none.tpl");
			return $this->parse();
		}

		$this->read_template("search.tpl");

		if (($bs = aw_ini_get("search.baseurl")) != "")
		{
			$this->cfg["baseurl"] = $bs;
			$this->vars(array("baseurl" => $bs));
		}

		// genereerime listi koikidest menyydest, samasugune kood on ka
		// mujal olemas, kas ei voiks seda kuidagi yhtlustada?

		// besides, mulle tundub, et otsingutulemusi kuvades taidetakse seda koodi
		// 2 korda .. teine kord menueditis. Tyhi too?
		if ($this->cfg["lang_menus"] == 1)
		{
			$ss = " AND (objects.lang_id = ".aw_global_get("lang_id")." OR menu.type = ".MN_CLIENT.")";
		}
		else
		{
			$ss = "";
		};

		$sc = get_instance("search_conf");
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
			if (aw_global_get("uid") == "" && $search_group["no_usersonly"] == 1)
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
		{
			$ml.=" ORDER BY objects.modified DESC";
		}

		// oh crap. siin peab siis failide seest ka otsima. 
		$mtfiles = array();
		$this->db_query("SELECT id FROM files WHERE files.showal = 1 AND files.content LIKE '%$str%' ");
		while ($row = $this->db_next())
		{
			$mtfiles[] = $row["id"];
		}

		$docids = array();

		$fstr = join(",",$mtfiles);
		if ($fstr != "")
		{
			// nyyd leiame k6ik aliased, mis vastavatele failidele tehtud on
			// and we need that because .. ?
			$this->db_query("SELECT * FROM aliases WHERE target IN ($fstr)");
			while ($row = $this->db_next())
			{
				$docids[$row["source"]] = $row["source"];
				//$faliases[] = $row["source"];
			}
			// nyyd on $faliases array dokumentidest, milles on tehtud aliased matchivatele failidele.
			/*
			if (is_array($faliases))
			{
				$fasstr = "OR documents.docid IN (".join(",",$faliases).")";
			}
			*/
		}

		// nini. otsime tabelite seest ka.
		$mts = array();
		$this->db_query("SELECT id FROM aw_tables WHERE contents LIKE '%$str%'");
		while ($row = $this->db_next())
		{
			$mts[] = $row["id"];
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
				$docids[$row["source"]] = $row["source"];
				//$mtals[$row["source"]] = $row["source"];
			}

			/*

			$mts = join(",",$mtals);
			if ($mts != "")
			{
				// see on siis nimekiri dokudest, kuhu on tehtud aliased tabelitest, mis matchisid
				$mtalsstr = "OR documents.docid IN (".$mts.")";
			}
			*/
			//echo "ms = $mtalsstr<br>";
		}

		$cnt = 0;
		//max number of occurrences of search string in document
		$max_count = 0;
		$docarr = array();

		if ( sizeof($docids) > 0 )
		{
			$docidstr = " OR documents.docid IN (" . join(",",$docids) . ")";
		};

		$mc = get_instance("menu_cache");
		$mc->make_caches();

		$plist = join(",",$parent_list);
		if ($ostr[0] == "\"")
		{
			$str = substr($str, 2,strlen($str)-4);
			// search concrete quoted string
			$docmatch = "documents.title LIKE '%".$str."%' OR documents.content LIKE '%".$str."%' OR documents.author LIKE '%".$str."%'";
			if ($this->cfg["use_dcache"])
			{
				$docmatch .= " OR documents.dcache LIKE '%" . $str . "%'";
			};
		}
		else
		{
			// search all words
			$wds = explode(" ",$str);
			$docmatcha = array();
			$docmatcha[] = join(" AND ",$this->map("documents.title LIKE '%%%s%%'",$wds));
			$docmatcha[] = join(" AND ",$this->map("documents.content LIKE '%%%s%%'",$wds));
			$docmatcha[] = join(" AND ",$this->map("documents.author LIKE '%%%s%%'",$wds));
			if ($this->cfg["use_dcache"])
			{
				$docmatcha[] = join(" AND ",$this->map("documents.dcache LIKE '%%%s%%'",$wds));
			};
			$docmatch = join(" OR ", $this->map("(%s)",$docmatcha));
		}
		$q = "SELECT documents.*,objects.parent as parent, objects.modified as modified, objects.parent as parent 
										 FROM documents 
										 LEFT JOIN objects ON objects.oid = documents.docid
										 WHERE ($docmatch) AND objects.status = 2 AND objects.lang_id = ".aw_global_get("lang_id")." AND objects.site_id = " . $this->cfg["site_id"] . " AND (documents.no_search is null OR documents.no_search = 0) $ml";
		dbg("search_q = $q <br>");
		$si = __get_site_instance();
		$this->db_query($q);
		while($row = $this->db_next())
		{
			if (not($this->can("view",$row["docid"])) || !is_array($mc->get_cached_menu($row["parent"])))
			{
				continue;
			};
			// find number of matches in document for search string, for calculating percentage
			// if match is found in title, then multiply number by 5, to emphasize importance
			
			// hook for site specific document parsing
			$si->parse_search_result_document(&$row);

			$c = substr_count(strtoupper($row["content"]),strtoupper($str)) + substr_count(strtoupper($row["title"]),strtoupper($str))*5;
			$max_count = max($c,$max_count);

			// find the first paragraph of text or lead if it contains something
			if ($row["lead"] != "")
			{
				$co = strip_tags($row["lead"]);
				$co = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$co);
			}
			else
			{
				$p1 = strpos($row["content"],"<BR>");
				$p2 = strpos($row["content"],"</P>");
				$pos = min($p1,$p2);
				$co = substr($row["content"],0,$pos);
				$co = strip_tags($co);
				$co = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$co);
			}
			// to hell with html in titles
			$row["title"] = strip_tags($row["title"]);
			$title = ($row["title"]) ? $row["title"] : "(nimetu)";
			$docarr[] = array(
				"matches" => $c, 
				"title" => $title,
				"section" => $row["docid"],
				"content" => $co,
				"modified" => $this->time2date($row["modified"],5),
				"tm" => $row["tm"],
				"parent" => $row["parent"]
			);
			$cnt++;
			
		}

		if ($sortby == "percent")
		{
			$d2arr = array();
			reset($docarr);
			while (list(,$v) = each($docarr))
			{
				if ($max_count == 0)
				{
					$d2arr[100][] = $v;
				}
				else
				{
					$d2arr[($v[matches]*100) / $max_count][] = $v;
				}
			}

			krsort($d2arr,SORT_NUMERIC);

			$docarr = array();
			reset($d2arr);
			while (list($p,$v) = each($d2arr))
			{
				reset($v);
				while (list(,$v2) = each($v))
				{
					$docarr[] = $v2;
				}
			}

		}

		$per_page = 10;

		$mned = get_instance("menuedit");

		if (aw_ini_get("search.rewrite_urls"))
		{
			$exp = get_instance("export");
			$exp->init_settings();
		}

		$num = 0;
		reset($docarr);
		while (list(,$v) = each($docarr))
		{
			if ($num >= $from && $num < ($from + $per_page))	// show $per_page matches per screen
			{
				if ($max_count == 0)
				{
					$sstr = 100;
				}
				else
				{
					$sstr = substr(($v["matches"]*100) / $max_count,0,4);
				}

				$sec = $v["section"];
				if ($mc->subs[$v["parent"]] == 1)
				{
					// if it is the only document under the menu, make link to the menu instead
					$sec = $v["parent"];
				}

				if (aw_ini_get("search.rewrite_urls"))
				{
					$sec = $exp->rewrite_link(document::get_link($sec));
					$sec = $this->cfg["baseurl"]."/".$exp->get_hash_for_url($sec,aw_global_get("lang_id"));
					$sec = str_replace("_","/",$sec);
				}

				$this->vars(array("title"			=> strip_tags($v["title"]),
													"percent"		=> $sstr,
													"content"		=> preg_replace("/#(.*)#/","",$v["content"]),
													"modified"	=> $v["tm"] == "" ? $v["modified"] : $v["tm"],
													"section"		=> $sec));
				$r.= $this->parse("MATCH");
			}
			$num++;
		}

		$this->vars(array(
			"MATCH" => $r,
			"s_parent" => $parent,
			"sstring" => urlencode($str),
			"sstringn" => $str, 
			"section" => $section,
			"matches" => $cnt,
			"sortby" => $sortby
		));

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
				$this->vars(array(
					"from" => $i*$per_page,
					"page_from" => $i*$per_page,
					"page_to" => min(($i+1)*$per_page,$cnt)
				));
				if ($i*$per_page == $from)
				{
					$pg.=$this->parse("SEL_PAGE");
				}
				else
				{
					$pg.=$this->parse("PAGE");
				}
			}
		}
		$this->vars(array(
			"PREVIOUS" => $prev,
			"NEXT" => $next,
			"PAGE" => $pg,
			"SEL_PAGE" => "",
			"from" => $from,
			"section" => $section
		));
		$ps = $this->parse("PAGESELECTOR");
		$this->vars(array("PAGESELECTOR" => $ps));

		$this->_log("document", "otsis stringi $str , alamjaotusest nr $parent, leiti $cnt dokumenti");
		$this->db_query("INSERT INTO searches(str,s_parent,numresults,ip,tm) VALUES('$str','$parent','$cnt','".aw_global_get("REMOTE_ADDR")."','".time()."')");

		$retval = $this->parse();
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

		$ob = get_instance("objects");
		$obl = $ob->get_list(false,false,$this->cfg["rootmenu"]);
		$conf = get_instance("config");
		$df = $conf->get_simple_config("docfolders".aw_global_get("LC"));
		if ($df != "")
		{
			$xml = get_instance("xml");
			$_df = $xml->xml_unserialize(array("source" => $df));
		}

		if (!$parent)
		{
			reset($_df);
			list($parent,$parent) = each($_df);
		}

		$lang_id = aw_global_get("lang_id");

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
		$SITE_ID = $this->cfg["site_id"];
		extract($args);
		$id = (int)$id;
		$q = "SELECT documents.author as author, objects.oid as oid, objects.name as name, documents.modified as modified FROM objects LEFT JOIN keywordrelations ON (keywordrelations.id = objects.oid) LEFT JOIN documents ON (documents.docid = objects.oid) WHERE status = 2 AND class_id IN (" . CL_DOCUMENT . "," . CL_PERIODIC_SECTION . ") AND site_id = '$SITE_ID' AND keywordrelations.keyword_id = '$id' ORDER BY documents.modified DESC";
		$retval = "";
		load_vcl("table");

		$tt = new aw_table(array(
			"prefix" => "keywords",
			"tbgcolor" => "#C3D0DC",
		));

		$tt->parse_xml_def($this->cfg["site_basedir"]."/xml/generic_table.xml");
		$tt->define_field(array(
			"name" => "name",
			"caption" => "Pealkiri",
			"talign" => "center",
			"sortable" => 1,
		));
		$tt->define_field(array(
			"name" => "modified",
			"caption" => "Kuup&auml;ev",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
		));
		$tt->define_field(array(
			"name" => "modifiedby",
			"caption" => "Autor",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
		));
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->save_handle();
			$x = $this->get_relations_by_field(array(
				"field"    => "name",
				"keywords" => $row["author"],
				"section"  => $this->cfg["link_authors_section"]
			));
			$authors = array();
			while(list($k,$v) = each($x)) 
			{
				if ($this->cfg["link_default_link"] != "")
				{
					if ($v)
					{
						$authors[] = sprintf("<a href='%s'>%s</a>",document::get_link($v),$k);
					} 
					else 
					{
						$authors[] = sprintf("<a href='%s'>%s</a>",$this->cfg["link_default_link"],$k);
					};
				}
				else
				{
					$authors[] = $k;
				}
			}; // while
			$author = join(", ",$authors);
			$this->restore_handle();

			$tt->define_data(array(
				"name" => sprintf("<a href='%s'>%s</a>",document::get_link($row["oid"]),$row["name"]),
				"modified" => $this->time2date($row["modified"],8),
				"modifiedby" => $author,
			));
		};
		$tt->sort_by();
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

		$f = get_instance("formgen/form");
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

		$f = get_instance("formgen/form");
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

		$f = get_instance("formgen/form");

		$tx = $f->show(array(
			"id" => $f->get_form_for_entry($meta["entry_id"]),
			"entry_id" => $meta["entry_id"],
			"op_id" => $tpl
		));
		
		$al = get_instance("aliasmgr");
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
		
		// detailed search
		$mp = $this->register_parser(array(
					"reg" => "/(#)search_conf(#)/i",
					));

		$this->register_sub_parser(array(
					"class" => "search_conf",
					"reg_id" => $mp,
					"function" => "search",
					));
		
		// change password
		$mp = $this->register_parser(array(
					"reg" => "/(#)password_form(#)/i",
					));

		$this->register_sub_parser(array(
					"class" => "users",
					"reg_id" => $mp,
					"function" => "change_pwd_hash",
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
	// and yes, NS4 is a steaming pile of crap and should die. Mozilla is so much better
	function mk_ns4_compat(&$text)
	{
		if ( (substr_count($text,"<P>") > 1) || (substr_count($text,"<p>") > 1) )
		{
			$text = str_replace("</p>","<br /><br />",$text);	
			$text = str_replace("</P>","<br /><br />",$text);	
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
			$keywords[$row["keyword"]] = sprintf(" <a href='%s' title='%s'>%s</a> ",$this->mk_my_orb("lookup",array("id" => $row["keyword_id"],"section" => $docid),"document"),"LINK",$row["keyword"]);
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

	////
	// !lets the user send a document to someone else
	function send($arr)
	{
		extract($arr);
		$data = $this->fetch($section);
		$this->read_template("email.tpl");
		$this->vars(array(
			"docid" => $section,
			"section" => $section,
			"doc_name" => $data["title"],
			"reforb" => $this->mk_reforb("submit_send", array("section" => $section))
		));
		return $this->parse();
	}

	////
	// !actually sends the document as a link via e-mail
	function submit_send($arr)
	{
		extract($arr);
		$this->read_template("doc_mail.tpl");
		$this->vars(array(
			"from_name" => $from_name,
			"from" => $from,
			"section" => $section,
			"comment" => $comment
		));

		if ($copy != "")
		{
			$bcc = "\nCc: $copy ";
		}

		mail("\"$to_name\" <".$to.">",str_replace("\n","",str_replace("\r","",$this->parse("title"))),$this->parse("mail"),"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");

		$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $section ","name");
		$this->_log("document", "$from_name  $from saatis dokumendi <a href='".$this->cfg["baseurl"]."/?section=".$section."'>$name</a> $to_name $to  'le",$section);

		return $this->cfg["baseurl"]."/?section=".$section;
	}

	function feedback($arr)
	{
		extract($arr);
		$feedback = get_instance("feedback");
		$inf = $this->fetch($section);
		$this->read_template("feedback.tpl");
		$this->vars(array(
			"uid" => aw_global_get("uid")
		));
		$tekst = "";
		while(list($k,$v) = each($feedback->tekst)) 
		{
			$tekst .= "<tr><td align='right'><input type='radio' name='tekst' value='$k'></td><td align=\"left\" class=\"text2\">$v</td></tr>";
		};

		$kujundus = "";
		while(list($k,$v) = each($feedback->kujundus)) 
		{
			$kujundus .= "<tr><td align='right'><input type='radio' name='kujundus' value='$k'></td><td align=\"left\" class=\"text2\">$v</td></tr>";
		};
		
		$struktuur = ""; $tehnika = ""; $ala = "";	
		while(list($k,$v) = each($feedback->struktuur)) 
		{
			$struktuur .= "<tr><td align='right'><input type='radio' name='struktuur' value='$k'></td><td align=\"left\" class=\"text2\">$v</td></tr>";
		};
		
		while(list($k,$v) = each($feedback->ala)) 
		{
			$ala .= "<tr><td align='right'><input type='radio' name='ala' value='$k'></td><td align=\"left\" class=\"text2\">$v</td></tr>";
		};
		
		while(list($k,$v) = each($feedback->tehnika)) 
		{
			$tehnika .= "<tr><td align='right'><input type='checkbox' name='tehnika[]'  value='$k'></td><td align=\"left\" class=\"text2\">$v</td></tr>";
		};
			
   	$this->vars(array(
			"docid" => $section,
			"tekst" => $tekst,
			"kujundus" => $kujundus,
			"struktuur" => $struktuur,
			"ala" => $ala,
			"tehnika" => $tehnika,
			"title" => $inf["title"],
			"reforb" => $this->mk_reforb("submit_feedback", array("docid" => $section))
		));
		return $this->parse();
	}

	function submit_feedback($arr)
	{
		extract($arr);
		$inf = $this->fetch($docid);
		$feedback = get_instance("feedback");
		$arr["title"] = $inf["title"];
		$feedback->add_feedback($arr);
		$this->_log("document", "$eesnimi $perenimi , email:$mail saatis feedbacki", $docid);
		return $this->mk_my_orb("thanks", array("section" => $docid,"eesnimi" => $eesnimi));
	}

	function thanks($arr)
	{
		extract($arr);
		$this->read_template("feedback_thanks.tpl");
		$this->vars(array(
			"eesnimi" => $eesnimi
		));
		return $this->parse();
	}

	function do_print($arr)
	{
		extract($arr);
		$dat = $this->get_record("objects","oid",$section);
		$this->_log("document", "Printis dokumendi $dat[name] ",$section);
		echo($this->gen_preview(array(
			"docid" => $section,
			"tpl" => "print.tpl"
		)));
		aw_shutdown();
		die();
	}

	function author_docs($author)
	{
		$lsu = aw_ini_get("menuedit.long_section_url");

		$this->db_query("
			SELECT docid,title 
			FROM documents 
				LEFT JOIN objects ON objects.oid = documents.docid 
			WHERE author = '$author' AND objects.status = 2
			ORDER BY objects.created DESC
		");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$num_comments = $this->db_fetch_field("SELECT count(*) AS cnt FROM comments WHERE board_id = '$row[docid]'","cnt");
			$this->restore_handle();

			if ($lsu)
			{
				$link = $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$row["docid"];
			}
			else
			{
				$link = $this->cfg["baseurl"]."/".$row["docid"];
			}

			$this->vars(array(
				"link" => $link,
				"comments" => $num_comments,
				"title" => strip_tags($row["title"]),
				"comm_link" => $this->mk_my_orb("show_threaded",array("board" => $row["docid"]),"forum"),
			));
			$hc = "";
			if ($num_comments > 0)
			{
				$hc = $this->parse("HAS_COMM");
			}

			$this->vars(array("HAS_COMM" => $hc));

			$c.=$this->parse("AUTHOR_DOC");
		}
		return $c;
	}

	function get_link($docid)
	{
		$lsu = aw_ini_get("menuedit.long_section_url");
		$bu = aw_ini_get("baseurl");
		if ($lsu)
		{
			return $bu."/?section=$docid";
		}
		else
		{
			return $bu."/".$docid;
		}
	}

	////
	// !generates static pages fot the document ($id) , the document's parent menu and the documents brothers and menus
	// uses the settings set in the general static site settings for generation
	function gen_static_doc($id)
	{
		echo "genereerin staatilisi lehek&uuuml;lgi, oodake palun .... <br><br>\n\n";
		echo "\n\r<br>";
		echo "\n\r<br>";
		flush();
		$exp = get_instance("export");
		$exp->init_settings();

		$obj = $this->get_object($id);

		// doc parent
		$exp->fetch_and_save_page($this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?section=".$obj["parent"], $obj["lang_id"], true);

		$exp->exp_reset();

		// doc 
		$exp->fetch_and_save_page($this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?section=".$id, $obj["lang_id"], true);
		// print doc
		$exp->fetch_and_save_page($this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?section=".$id."&print=1", $obj["lang_id"], true);
		die("<Br><br>Valmis, tagasi dokumendi muutmise juurde saab <a href='".$this->mk_my_orb("change", array("id" => $id))."'>siit</a>");
	}

	////
	// !Generates a list of name => type fields for the search engine
	function search_callback_get_fields(&$fields,$args)
	{
		$fields = array();
		$fields["name"] = array(
			"type" => "textbox",
			"caption" => "Pealkiri",
			"value" => $args["name"],
		);

		$fields["lead"] = array(
			"type" => "textbox",
			"caption" => "Lead",
			"value" => $args["lead"],
		);

		$fields["content"] = array(
			"type" => "textbox",
			"caption" => "Sisu",
			"value" => $args["content"],
		);
		
		$fields["author"] = array(
			"type" => "textbox",
			"caption" => "Autor",
			"value" => $args["author"],
		);

		$periods = get_instance("periods");

		$mlist = $periods->period_list($args["period"]);

		$fields["period"] = array(
			"type" => "multiple",
			"caption" => "Periood",
			"options" => $mlist,
			"selected" => $args["period"],
		);

		$fields["alias"] = "n/a";
		$fields["class_id"] = "n/a";
	}

	function search_callback_get_query($args,$parts)
	{
		if ($args["lead"])
		{
			$parts["lead"] = " documents.lead LIKE '%$args[lead]%' ";
		};
		
		if ($args["author"])
		{
			$parts["author"] = " documents.lead LIKE '%$args[author]%' ";
		};
		
		if ($args["content"])
		{
			$parts["content"] = " documents.content LIKE '%$args[content]%' ";
		};

		if (is_array($args["period"]))
		{
			$parts["content"] = " documents.period IN (" . join(",",$args["period"]) . ") ";
		};

		$where = join(" AND ",$parts);
		$q = "SELECT * FROM documents LEFT JOIN objects ON documents.docid = objects.oid WHERE $where";
		return $q;
	}

	function search_callback_modify_data($row,$args)
	{
		$url = $this->mk_my_orb("change",array("id" => $row["oid"]),"document");
		$row["name"] = "<a href='$url'>$row[name]</a>";
	}

	function docsearch($args = array())
	{
		$search = get_instance("search");
		$this->read_template("docsearch.tpl");
		$args["clid"] = "document";
		$os = $this->mk_my_orb("search",array("parent" => $args["parent"]),"search");
		$url = $this->mk_my_orb("docsearch",array("parent" => $args["parent"]));
		$this->mk_path($args["parent"],"<a href='$os'>Objektiotsing</a> / <a href='$url'>Dokumentide otsing</a>");
		$form = $search->show($args);
		$results = $search->get_results();
		$this->vars(array(
			"form" => $form,
			"reforb" => $this->mk_reforb("docsearch",array("no_reforb" => 1,"search" => 1, "parent" => $args["parent"])),
			"table" => $results,
		));
		return $this->parse();
	}

	function get_properties($args = array())
	{
		$fields = array();
		$fields["showlead"] = array(
			"type" => "checkbox",
			"caption" => "Näita leadi",
			"value" => $args["showlead"],
			"store" => "table",
			"table" => "documents",
			"idfield" => "docid",
		);
		$fields["title_clickable"] = array(
			"type" => "checkbox",
			"caption" => "Pealkiri klikitav",
			"value" => $args["title_clickable"],
			"store" => "table",
			"table" => "documents",
			"idfield" => "docid",
		);
		$fields["no_right_pane"] = array(
			"type" => "checkbox",
			"caption" => "Ilma parema paanita",
			"value" => $args["no_right_pane"],
			"store" => "table",
			"table" => "documents",
			"idfield" => "docid",
		);
		$fields["is_forum"] = array(
			"type" => "checkbox",
			"caption" => "Omab foorumit",
			"value" => $args["is_forum"],
			"store" => "table",
			"table" => "documents",
			"idfield" => "docid",
		);
		return $fields;
	}
};
?>
