<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/documents.aw,v 2.8 2001/05/22 01:07:30 kristo Exp $
classload("msgboard","aw_style");
classload("acl","styles","form","tables","extlinks","images","gallery","file");
class db_documents extends aw_template
{
	function db_documents($period = 0)
	{
		$this->tpl_init("");
		$this->db_init();
		// see on selleks, kui on vaja perioodilisi dokumente naidata
		$this->period = $period;
		// siia salvestame parsitud dokumendiheaderid ja sisud

		$this->style_engine = new aw_style;
		global $basedir;
		$xml_def = $this->get_file(array("file" => "$basedir/xml/documents/defaults.xml"));
		if ($xml_def)
		{
			$this->style_engine->define_styles($xml_def);
		}
		else
		{
			print "tags definition file not found.";	
			die;
		};
		// siia tuleks kirja panna koik dokumentide tabeli v2ljade nimed,
		// mida voidakse muuta

		// key on kasutusel selleks, et formeerida logi jaoks moistlik string
		$this->knownfields = array(
					"Pealkiri"		=> "title",
		 			"Alapealkiri"		=> "subtitle",
					"Autor"			=> "author",
					"Fotod"			=> "photos",
					"Nimed"			=> "names",
					"Lead"        		=> "lead",
					"Näita leadi"		=> "showlead",
					"Sisu"			=> "content",
					"Esilehel"		=> "esilehel",
					"jrk1"			=> "jrk1",
					"jrk2"			=> "jrk2",
					"jrk3"			=> "jrk3",
					"Esileht yleval"	=> "esileht_yleval",
					"Esilehel uudis"	=> "esilehel_uudis",
					"Tsitaat"		=> "cite",
					"Kanal"			=> "channel",
					"Kellaaeg"		=> "tm",
					"Foorum"		=> "is_forum",
					"Lingi tekst"		=> "link_text",
					"Lead comment"		=> "lead_comments",
					"Pealkiri klikitav"	=> "title_clickable",
					"Paremal"		=> "yleval_paremal");
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

	// kas selline doku on olemas?
	function exists($docid)
	{
		// @desc: kontrollib kas dokument on olemas
		$q = "SELECT * FROM documents WHERE docid = '$docid'";
		$this->db_query($q);
		$row = $this->db_next();
		return $row;
	}

	// teeb uue kirje documents tabelisse
	function create_doc($docid)
	{
		// @desc: loob uue dokumendi
		$q = "INSERT INTO documents (docid) VALUES ('$docid')";
		$this->db_query($q);
	}

	////
	// !listib dokumendid mingis sektsioonis
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
		$sections = unserialize($row[sss]);
		$periods = unserialize($row[pers]);

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

		if ($status != -1)
		{
			$v.= " AND objects.status = $status ";
		};
	
		if ($row[ndocs] > 0)
		{
			$lm = "LIMIT ".$row[ndocs];
		};

		$q = "SELECT documents.lead AS lead,
			documents.docid AS docid,
			documents.title AS title,
			documents.esilehel AS esilehel,
			documents.author AS author,
			objects.period AS period,
			objects.class_id as class_id,
			objects.parent as parent
			FROM objects
			LEFT JOIN documents ON
			(documents.docid = objects.oid)
			WHERE $pstr && $rstr $v
			ORDER BY objects.period DESC,objects.jrk $lm";
		$this->db_query($q);
		$awt->stop("db_documents->list_docs()");
	}

	function search($orderby,$sortorder,$field = "",$fval = "")
	{
		// @desc: otsib mingile tingimusele vastavaid dokumente
		if (strlen($field) > 0)
		{
			$sufix = "WHERE $field LIKE '$fval%'";
		};
		if ($orderby != "")
		{
			$orderby = "$orderby $sortorder";
		};
		$q = "SELECT * FROM documents
			LEFT JOIN objects ON
			(documents.docid = objects.oid)
			$sufix
			ORDER BY $orderby";
		$this->db_query($q);
	}

	function fetch($docid,$field = "main")
	{
		// @desc: kysib infot mingi konkreetse doku kohta, mergib ka info objektide tabelist
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
			documents.keywords AS keywords,
			objects.cachedirty AS cachedirty,
			objects.parent AS parent,
			objects.period AS period,
			objects.modified AS modified
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
//			$data["oid"] = $oo["brother_of;
//			$data["docid"] = $docid;
		}

		if (gettype($data) == "array")
		{
			 $data[content] = trim($data[content]);
			 $data[lead] = trim($data[lead]);
			 $data[cite] = trim($data[cite]);
		};
		$this->dequote($data);

		if (preg_match("/<P(.*)>((&nbsp;)*)<\/P>/",$data["lead"]))
		{
			$data["lead"] = "";
		}
		return $data;
	}

	function get_title($docid)
	{
		$q = "SELECT title FROM documents WHERE docid = '$docid'";
		return $this->db_fetch_field($q,"title");
	}

	// kas järgmised 2 funktsiooni ikka peavad ikka siin olema?
	function set_status($docid,$status)
	{
		$q = "UPDATE objects 
			SET status = $status
			WHERE oid = '$docid'";
		$this->db_query($q);
		$this->_log("document","aktiveeris dokumendi $docid");
	}

	function set_visibility($docid,$vis)
	{
                $q = "UPDATE objects
                        SET visible = $vis
                        WHERE oid = '$docid'";
                $this->db_query($q);
                $this->_log("document","muutis dokumendi $docid nähtavust");
        }
		
	// see on ka yx kahtlane funktsioon.
	function delete($docid)
	{
		$q = "DELETE FROM documents WHERE docid = '$docid'";
		$this->db_query($q);
		$q = "DELETE FROM objects WHERE oid = '$docid'";
		$this->db_query($q);
		$this->_log("document"," kustutas objekti $docid");
	}

	// see on lihtsalt wrapper backwards compatibility jaoks
	// DEPRECATED
	function show($docid,$text = "undef",$tpl="plain.tpl",$leadonly = -1,$secID = -1)
	{
		$params[docid]		= $docid;
		$params[text]		= $text;
		$params[tpl]		= $tpl;
		$params[leadonly]	= $leadonly;
		$params[secID]		= $secID;
		return $this->gen_preview($params);
	}

	// genereerib objekti nö valmiskujul
	function gen_preview($params)
	{
		$docid	= $params["docid"];
		$text		= $params["text"];
		$tpl		= ($params["tpl"]) ? $params["tpl"] : "plain.tpl";
		// selle votmega antakse ette template source, mille
		// sisse kood paigutada
		$tpls		= $params["tpls"];
		$leadonly	= $params["leadonly"];
		$strip_img	= $params["strip_img"];
		$secID		= $params["secID"];
		$boldlead	= $params["boldlead"];
		$tplsf		= $params["tplsf"];
		$notitleimg	= $params["notitleimg"];
		$showlead	= $params["showlead"];
		$no_strip_lead	= $params["no_strip_lead"];
		$doc = $params["doc"];	// kui tehakse p2ring dokude tabelisse, siis v6ib ju sealt saadud inffi kohe siia kaasa panna ka
													// s22stap yhe p2ringu.

		// kui lead_only on antud, siis loeme (vajadusel) ainult 
		// leadi cache sisu

		// miks kurat siin globaalseid muutujaid peab kasutama. I don't like it
		global $classdir;
		global $ext;
		global $awt;

		$awt->count("db_documents->gen_preview()");
		$awt->start("doc_gen_preview");
	
		$align= array("k" => "align='center'", "p" => "align='right'" , "v" => "align='left'" ,"" => "");
	
		$awt->start("gen_preview1");
		
		// küsime dokumendi kohta infot

		// muide docid on kindlasti numbriline, aliaseid kasutatakse ainult
		// menueditis.
		if (!is_array($doc))
		{
			$doc = $this->fetch($docid);
			$docid=$doc["docid"];
		}
		$this->no_right_pane = $doc["no_right_pane"];	// see on sellex et kui on laiem doku, siis menyyeditor tshekib
		$this->no_left_pane = $doc["no_left_pane"];		// neid muutujaid ja j2tab paani 2ra kui tshekitud on.
		
		$awt->stop("gen_preview1");

		if (!$doc)
		{
			// objekti polnud, bail out
			return "";
		};
		
		$awt->start("db_documents->gen_preview()::starter");
		$this->tpl_reset();
		$this->tpl_init("automatweb/documents");

		// kui tpls anti ette, siis loeme template sealt,
		// muidu failist.

		if (strlen($tpls) > 0)
		{
			$this->templates[MAIN] = $tpls;
		}
		else
		if (strlen($tplsf) > 0)
		{
			$this->read_template($tplsf);
		}
		else
		{
			$this->read_template($tpl);
		};

		$this->vars(array("imurl" => "/images/trans.gif"));

		// load localization settings and put them in the template
		lc_site_load("document");
		$this->vars($GLOBALS["lc_doc"]);

		// miski kahtlane vark siin. Peaks vist sellele ka cachet rakendama?
		if (!(strpos($doc[content], "#telekava_") === false))
		{
			return $this->telekava_doc($doc[content]);
		};

		// laeme vajalikud klassid
		$extlinks = new extlinks;
		$extlinks->db_init();

		$img = new db_images;
		$aliases = $img->get_aliases_for($docid);
		$formlist = array();
		$fc = 0;

		$retval = "";

		$used = array();
		$awt->stop("db_documents->gen_preview()::starter");

		// kas näitame ainult leadi?
		$awt->start("db_documents->gen_preview()::leadonly_bit");
		if ($leadonly > -1) 
		{
			// stripime pildid välja. ja esimese pildi salvestame cachesse
			// et seda mujalt kätte saaks
			if ($strip_img)
			{
				// otsime pilditage
				if (preg_match("/#p(\d+?)(v|k|p|)#/i",$doc[lead],$match))
				{
					// asendame
					$idata = $img->get_img_by_oid($docid,$match[1]);
					$this->li_cache[$docid] = $idata[url];
					$this->vars(array("imurl" => $idata[url]));
				}
				else
				{
					// ei leidnud, asendame voimaliku pildi url-i transparent gif-iga
					$this->vars(array("imurl" => "/images/trans.gif"));
				};
				// ja stripime leadist *koik* objektitagid välja.
				$doc[lead] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc[lead]);
			}; // $strip_img
			// viime leadi $doc[content] sisse edasiseks kasutamiseks
			// see <br> vbla ei peaks ka siin olema
			$doc[content] = "$doc[lead]<br>";
		}
		else
		// näitame tervet dokut
		{
			if (($doc[lead]) && ($doc[showlead] == 1 || $showlead == 1) )
			{
				if ($no_strip_lead != 1)
				{
					// hmmm
					$doc[lead] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc[lead]);
				};
				$txt = $doc[lead];
				if ($boldlead) {
					$txt = "<strong>" . $txt . "</strong>";
				};
				// hm. ma ei ole kindel, et igasugu html tage peaks ikka siia sisse kinni
				// kirjutama
				$txt .= "<p>$doc[content]";
				$doc[content] = $txt;
			};
		};
		$awt->stop("db_documents->gen_preview()::leadonly_bit");

		// nüüd on meil kogu näidatav tekst muutujas $doc[content]
		// ja teeme selle peal vajalikud asendused

		// asendame <foobar>aasdad</foobar> tüüpi tag-id
		// nunnult lühike imho :)
		$awt->start("db_documents->gen_preview()::style_engine_parse");
		$doc[content] = $this->style_engine->parse_text($doc[content]);

		global $baseurl,$ext;

		// see peaks ka XMLi sisse kolima, niipea kui ma välja mõtlen, kuidas stringis olevaid muutujaid
		// regexpiga asendada
		$doc[content] = preg_replace("/<loe_edasi>(.*)<\/loe_edasi>/isU","<a href='$baseurl/index.$ext/section=$docid'>\\1</a>",$doc[content]);
		$doc[content] = preg_replace("/<\?xml(.*)\/>/imsU","",$doc[content]);
		$awt->stop("db_documents->gen_preview()::style_engine_parse");

		$awt->start("db_documents->gen_preview()::notitleimg");
		// pildid pealkirjas. Suuuure. Riiiiight
		$this->vars(array("docid" => $docid));
		if ($notitleimg != 1)
		{
			$im = "";
			while (preg_match("/#(\w+?)(\d+?)(v|k|p|)#/i",$doc[title],$match)) 
			{
				switch($match[1]) 
				{
					case "p":
						$pc = 0; $pid = 0;
						reset($aliases);
						$icount++;
						$idata = $img->get_img_by_oid($docid,$match[2]);
						if ($idata) 
						{
							$this->vars(array("url" => $idata[url],"plink" => $idata[link]));
							if ($link != "")
							{
								$im = $this->parse("image_linked");
							}
							else
							{
								$im = $this->parse("image");
							}
						}; // if idata
						$doc[title] = preg_replace("/$match[0]/i",$im,$doc[title]);
						break;

					default:
						$doc[title] = preg_replace("/$match[0]/i","",$doc[title]);
						break;
				}; // switch
			}; // while
		} // if notitleimg
		else
		{
			$this->vars(array("image" => ""));
			// stripime pealkirjast pildid. bwahahahaha
			$doc[title] = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$doc[title]);
		};
		$awt->stop("db_documents->gen_preview()::notitleimg");

		$awt->start("db_documents->gen_preview()::board_last5");
		// no hea. Aga miks mitte seda universaalsemaks teha? Mikst 5? Aga kui ma tahan 6-t?
		// ne budjet tebe 6 krt. 
		if (!(strpos($doc[content], "#board_last5#") === false))
		{
			$mb = new msgboard;
			$doc[content] = str_replace("#board_last5#",$mb->mk_last5(),$doc[content]);
		}

		classload("table");
		$tbl = new table;
		$text = $doc[content]; 
		$awt->stop("db_documents->gen_preview()::board_last5");

		// see regulaaravaldis siin parsib välja koik tagid, mis on kujul 
		// #XY# voi #XYZ#, kus X on objekti tyyp (p = pilt, t = tabel)
		// Y on objekti (aliase) number selle objekti juures.
		// Z on align (v,k,p voi yldse puudu)

		// see tsykkel on voimeline töötama ka rekursiivselt, alltho ma pole
		// veel kindel, kas ja milleks see hea on

		$awt->start("db_documents->gen_preview()::alias_bs");
		$icount = 0;
		while (preg_match("/#(\w+?)(\d+?)(v|k|p|)#/i",$text,$match))
		{
			$awt->count("db_documents->gen_preview()::alias_bs");
			// $match[0] sisaldab tervet stringi
						// $match[1] on objekti tyyp (p = pilt, t = tabel) -- aname
						// $match[2] on objekti id selle objekti juures    -- anum
						// $match[3] on align				   -- aalign

			$replacement = "";
			// switch objekti asendamiseks
			switch($match[1])
			{
				// vorm
				case "f":
					// since alias numbers are generated according to order,
					// iterate until we find the one.
					// eh. ytlen otse v2lja. see systeem on katki.
					// mis siis kui m6ni vahelt 2ra kustutataxe? 
					//  .. so what? iga objekt teab, mitu aliast tema juures on ja tanu sellele
					//     saab uutele aliastele anda eelnevalt kasutamata numbreid. Probleemi pole
					// vahemalt minu vaimusilmas. Kuigi jah, moned AGAD siin tunduvad olevat
					//
					// a ikkagi, n6me on ju. aliaste tabelisse v6ix panna ka, et mis alias on, siis saax
					// need n6medad loobid siit 2ra..
					$fc = 0; $fid = 0;

					// et siis tsykkel yle koigi defineeritud aliaste
					reset($aliases);
					while (list(,$ar) = each($aliases))
					{
						if ($ar[type] == CL_FORM || $ar[type] == CL_FORM_ENTRY)	
						{
							$fc++;
						};
						if ($match[2] == $fc)
						{
							// this should be the correct one
							$fid = $ar;
							break;
						}
					};
				
					if (is_array($fid))
					{
						if ($ar[type] == CL_FORM_ENTRY)
						{
							$al_type = unserialize($ar[data]);
							$fx = new form();
							if ($al_type[type] == "change")
							{
								$replacement = $fx->gen_user_html($fid[id]);
							}
							else
							{
								$replacement = $fx->show(array("id" => $al_type[form_id], "entry_id" => $fid[id], "op_id" => $al_type[output]));
							};
						}
						else
						{
							$fx = new form();
							$replacement = $fx->gen_preview(array("id" => $fid[id], "form_action" => "/reforb.".$GLOBALS["ext"]));
						}
					}
					break;

				// pilt
				case "p":
					$awt->start("db_documents->gen_preview()::alias_bs_img");
					reset($aliases);
					$icount++;
					$awt->start("db_documents->gen_preview()::alias_bs_img_get");
					$idata = $img->get_img_by_oid($docid,$match[2]);
					$awt->stop("db_documents->gen_preview()::alias_bs_img_get");
					if ($idata) 
					{
						$this->vars(array("imgref" => $idata[url],
															"imgcaption" => $idata[comment],
															"align" => $align[$match[3]],
															"plink" => $idata["link"]));
						if ($idata["link"] != "")
						{
							$replacement = $this->parse("image_linked");
						}
						else
						{
							$replacement = $this->parse("image");
						}

						if ($icount == 1) 
						{
							$this->imcache[$docid] = $idata[url];
						};
					}; // if idata
					$awt->stop("db_documents->gen_preview()::alias_bs_img");
					break;

			// guestbook
			case "b":
				$gbc = 0; $gbid = 0;
				reset($aliases);
				while(list(,$ar) = each($aliases)) 
				{
					if ($ar[type] == CL_GUESTBOOK) 
					{
						$gbc++;
					}
					if ($match[2] == $gbc) 
					{
						$gbid = $ar[id];
						break;
					}
				}
				if ($gbid) 
				{
					classload("guestbook");
					$gb = new guestbook;
					$replacement = $gb->draw($gbid);
				}
				break;

				case "v":
				// failid
				$gc = 0; $pid = 0;
				reset($aliases);
				while (list(,$ar) = each($aliases))
				{
					if ($ar[type] == CL_FILE)
						$gc ++;

					if ($match[2] == $gc)
					{
						$gid = $ar[id];
						break;
					}
				}
				if ($gid)
				{
					// siin tuleb siis n2idata faili id'ga $gid
					$t = new file;
					$fi = $t->get_file_by_id($gid);
					if ($fi[showal] == 1)
					{
						// n2itame kohe
						// kontrollime koigepealt, kas headerid on ehk väljastatud juba.
						// dokumendi preview vaatamisel ntx on.
						if ($fi["type"] == "text/html")
						{
							if (!headers_sent())
							{
								header("Content-type: text/html");
							};
							preg_match("/<body (.*)>(.*)<\/body>/imsU",$fi[content],$map);

							// strip returns, cause the file is already a html file, so we don't need to replace newlines with <br> l8r
							$replacement = str_replace("\n","",$map[2]);
							// don't show copyright if we are showing a html file. weird but true. statistikaameti jaox
							$doc["copyright"] = 0;
						}
						else
						{
							header("Content-type: ".$fi["type"]);
							die($fi["content"]);
						}
					}
					else
					{
						if ($fi[newwindow])
						{
							$ss = "target=\"_new\"";
						}
						$comment = $fi["comment"];
						if ($comment == "")
						{
							$comment = $fi["name"];
						}
						$replacement = "<a $ss class=\"sisutekst\" href='".$GLOBALS["baseurl"]."/files.aw/id=$gid/".urlencode($fi[name])."'>$comment</a>";
					}
				};
				break;

				// link
				case "l":
					$awt->start("db_documents->gen_preview()::alias_bs_links");
					$lc = 0; $lid = 0;
					reset($aliases);
					while(list(,$ar) = each($aliases)) 
					{
						if ($ar[type] == CL_EXTLINK) 
						{
								$lc++;
						};
						if ($match[2] == $lc) 
						{
							$lid = $ar[id];
							break;
						};
					}
				
					if ($lid) 
					{
						$l = $extlinks->get_link($lid);
						// noooo. not again global var
						global $baseurl;
						$target = "";
						if ($l[newwindow])
						{
							$target = "target='_new'";
						}
						$this->vars(array(	"url" => $baseurl . "/indexx.aw?id=$l[id]",
									"caption" => $l[name],
									"target" => $target));
						$replacement = $this->parse("link");
						// print $replacement;
					};
					$awt->stop("db_documents->gen_preview()::alias_bs_links");
					break;
										// tabel	
				case "t":
					$tbl = new table;
					$tc = 0; $tid = 0;
					reset($aliases);
					while (list(,$ar) = each($aliases))
					{
						if ($ar[type] == CL_TABLE)
						{
							$tc ++;

							if ($match[2] == $tc )
							{
								$tid = $ar[id];
							};
						};
					}
				
					if ($tid)
					{
						$replacement = $tbl->show(array("id" => $tid));
					};
					break;
				// graafikud
				case "g":
					$gc = 0; $pid = 0;
					reset($aliases);
					while (list(,$ar) = each($aliases)) 
					{
						if ($ar[type] == 28)
						{
							$gc ++;
						};

						if ($match[2] == $gc) 
						{
							$gid = $ar[id];
							break;
						}
					}
				
					if ($gid) {
						// siin tuleb siis n2idata graafikut id'ga $gid
						$replacement = "<img src='graphs.aw?type=show&id=$gid'>";
					};
					break;
									
				case "y":
					// galerriid
					$gc = 0; $pid = 0;
					reset($aliases);
					while (list(,$ar) = each($aliases)) 
					{
						if ($ar[type] == CL_GALLERY)
						{
							$gc ++;
						};

						if ($match[2] == $gc) 
						{
							$gid = $ar[id];
							break;
						}
					}
					if ($gid)
					{
						// siin tuleb siis n2idata galeriid id'ga $gid
						$gal = new gallery($gid);
						$replacement = $gal->show($GLOBALS["page"]);
					};
					break;
			
				// saidi sisesed lingid
				case "s":
					$a_name = $match[2];
					$replacement = "<a name=\"$a_name\">";
					$text = preg_replace("/#$a_name#(.*)#$a_name#/","<a href=\"#$a_name\">\\1</a>",$text);
					break;

				default:
					// kaotame vigased voi tundmatud tagid
					$replacement = "";
			}; // end switch

			// ja siin see asendus toimubki
			$text = preg_replace("/$match[0]/i",$replacement,$text);
		}; // regexp tsykkel yle teksti
		$awt->stop("db_documents->gen_preview()::alias_bs");


		$awt->start("db_documents->gen_preview()::jebu");
		$doc[content] = $text;
		// see on ka eri kuradi fun asi siin
		if ($docid == 64 || $docid == 65)
		{
			$doc[content] = str_replace("\r\n\r\n","<br>",$doc[content]);
		}
		else
		{
			if (!$doc["nobreaks"])	// kui wysiwyg editori on kasutatud, siis see on 1 ja pole vaja breike lisada
			{
				$doc[content] = str_replace("\r\n","<br>",$doc[content]);
			}
		};

		$doc[content] = str_replace("\n","",$doc[content]);
		$awt->stop("db_documents->gen_preview()::jebu");
			
		$awt->start("db_documents->gen_preview()::photos");
		if ($doc[photos])
		{
			if (DOC_LINK_AUTHORS && ($this->templates[pblock]))
			{
				$x = $this->get_relations_by_field(array(	"field"    => "title",
										"keywords" => $doc[photos],
										"section"  => DOC_LINK_AUTHORS_SECTION));
				$authors = array();
				// arr!
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
				$this->vars(array("photos" => $doc[photos]));
				$pb = $this->parse("pblock");
			};
		};
		$awt->stop("db_documents->gen_preview()::photos");

		if ($GLOBALS["ekomar_search"] == 1)
		{
			preg_match("/#ekomar_form eileitud=\"(.*)\"#/",$doc[content],$mat);
			$doc[content] = preg_replace("/#ekomar_form eileitud=\"(.*)\"#/", $this->search_ekomar($mat[1]),$doc[content]);
		}
		else
		{
			if (preg_match("/#ekomar_form eileitud=\"(.*)\"#/",$doc[content],$mat))
			{
				$doc[content] = preg_replace("/#ekomar_form eileitud=\"(.*)\"#/", $this->ekomar_form($mat[1]),$doc[content]);
			}
		}

		global $s_ark, $s_name;
		$doc[content] = str_replace("#s_ark#",$s_ark,$doc[content]);
		$doc[content] = str_replace("#s_name#",$s_name,$doc[content]);
		if ($s_ark != "" && $s_name != "")
		{
			$doc[content] = preg_replace("/#ekomar_search_both#(.*)#\/ekomar_search_both#/", "\\1",$doc[content]);
			$doc[content] = preg_replace("/#ekomar_search_ark#(.*)#\/ekomar_search_ark#/", "",$doc[content]);
			$doc[content] = preg_replace("/#ekomar_search_name#(.*)#\/ekomar_search_name#/", "",$doc[content]);
		}
		else
		if ($s_ark != "" && $s_name == "")
		{
			$doc[content] = preg_replace("/#ekomar_search_both#(.*)#\/ekomar_search_both#/", "",$doc[content]);
			$doc[content] = preg_replace("/#ekomar_search_ark#(.*)#\/ekomar_search_ark#/", "\\1",$doc[content]);
			$doc[content] = preg_replace("/#ekomar_search_name#(.*)#\/ekomar_search_name#/", "",$doc[content]);
		}
		else
		if ($s_ark == "" && $s_name != "")
		{
			$doc[content] = preg_replace("/#ekomar_search_both#(.*)#\/ekomar_search_both#/", "",$doc[content]);
			$doc[content] = preg_replace("/#ekomar_search_ark#(.*)#\/ekomar_search_ark#/", "",$doc[content]);
			$doc[content] = preg_replace("/#ekomar_search_name#(.*)#\/ekomar_search_name#/", "\\1",$doc[content]);
		}
		
		if (!(strpos($doc[content],"#ekomar_failid#") === false))
		{
			$doc[content] = str_replace("#ekomar_failid#",$this->ekomar_failid(),$doc[content]);
		}

		$awt->start("db_documents->gen_preview()::misc_replaces");
		if (!(strpos($doc[content],"<kommentaaride arv>") === false) || $doc[is_forum])
		{
			// p2rime aint siis kommentaaride arvu, kui seda vaja on, 
			// strpos on kiirem kui p2ring ex
			classload("msgboard");
			$t = new msgboard;
			$nc = $t->get_num_comments($docid);
			$nc = $nc < 1 ? "0" : $nc;
			$doc[content] = str_replace("<kommentaaride arv>",$nc,$doc[content]);
		}

		$doc[content] = preg_replace("/<kommentaar>(.*)<\/kommentaar>/isU",
				"<a class=\"links\" href='$baseurl/comments.$ext?section=$docid'>\\1</a>",$doc[content]);
		
		// <mail to="bla@ee">lahe tyyp</mail>
		$doc[content] = preg_replace("/<mail to=\"(.*)\">(.*)<\/mail>/","<a class='mailto_link' href='mailto:\\1'>\\2</a>",$doc[content]);

		$doc[content] = str_replace("#current_time#",$this->time2date(time(),2),$doc[content]);

		if (!(strpos($doc[content],"#liitumisform") === false))
		{
			preg_match("/#liitumisform info=\"(.*)\"#/",$doc[content], $maat);

			// siin tuleb n2idata kasutaja liitumisformi, kuhu saab passwordi ja staffi kribada.
			// aga aint sel juhul, kui kasutaja on enne t2itnud k6ik miski grupi formid.
			$dbu = new users;
			$doc[content] = preg_replace("/#liitumisform info=\"(.*)\"#/",$dbu->get_join_form($maat[1]),$doc[content]);
		}

		// keywordide list. bijaatch!
		if (!(strpos($doc["content"],"#huvid_form") === false))
		{
			preg_match("/#huvid_form algus=\"(.*)\"#/",$doc["content"], $maat);

			classload("keywords");
			$kw = new keywords;
			$t_int_form = $kw->show_interests_form($maat[1]);

			$doc["content"] = preg_replace("/#huvid_form algus=\"(.*)\"#/",$t_int_form,$doc["content"]);
		}

		$awt->stop("db_documents->gen_preview()::misc_replaces");

		$awt->start("db_documents->gen_preview()::author");
		if ($doc[author])
		{
  		if (DOC_LINK_AUTHORS && ($this->templates[ablock]))
			{
				$x = $this->get_relations_by_field(array(	"field"    => "title",
						 				"keywords" => $doc[author],
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
				}; // while
				$author = join(", ",$authors);
				$this->vars(array("author" => $author));
			 	$ab = $this->parse("ablock");
			  }
			  else
			  {
		
				$this->vars(array("author" => $doc[author]));
			 	$ab = $this->parse("ablock");
			};
		};
		$awt->stop("db_documents->gen_preview()::author");

		$awt->start("db_documents->gen_preview()::end_inc");

		$points = $doc[num_ratings] == 0 ? 3 : $doc[rating] / $doc[num_ratings];
		$pts = "";
		for ($i=0; $i < $points; $i++)
		{
			$pts.=$this->parse("RATE");
		};

		$this->vars(array("num_comments" =>  $nc,	"docid" => $docid));

		if ($doc[is_forum])
		{
			$fr = $this->parse("FORUM_ADD");
		};

		if ($this->is_template("LANG") || $this->is_template("SEL_LANG"))
		{
			// p2rime baasist aint siis kui vaja on
			$langs = "";
			classload("languages");
			$l = new languages;
			$larr = $l->listall();
			reset($larr);
			while (list(,$v) = each($larr))
			{
				$this->vars(array("lang_id" => $v[id], "lang_name" => $v[name]));
				if ($GLOBALS["lang_id"] == $v[id])
				{
					$langs.=$this->parse("SEL_LANG");
				}
				else
				{
					$langs.=$this->parse("LANG");
				};
			}
		}

		$lc = "";
		if ($doc[lead_comments]==1)
		{
			$lc = $this->parse("lead_comments");
		};

		if ($doc[parent])
		{
			$this->db_query("SELECT * FROM menu WHERE id = ".$doc[parent]);
			$mn = $this->db_next();
		}

		$title = $doc[title];
		$this->vars(array(
					"title"		=> $title,
					"text"		=> str_replace("\r","",$doc[content]),
					"secid"		=> $secID,
					"docid"		=> $docid,
					"ablock"	=> $ab,
					"pblock"	=> $pb,
					"date"		=> $this->time2date(time(),2),
					"section"	=> $GLOBALS["section"],
					"lead_comments"	=> $lc,
					"modified"	=> $this->time2date($doc[modified],2),
					"channel"	=> $doc[channel],
					"tm"		=> $doc[tm],
					"link_text"	=> $doc[link_text],
					"subtitle"	=> $doc[subtitle],
					"RATE"		=> $pts,
					"FORUM_ADD"	=> $fr,
					"LANG"		=> $langs,
					"SEL_LANG"	=> "",
					"menu_addr"	=> $mn[link],
					"lead_br"	=> $doc[lead] != "" ? "<br>" : "",
					"doc_count" => $this->doc_count++,
					"title_target" => $doc["newwindow"] ? "target=\"_blank\"" : "",
					"title_link"	=> ($doc["link_text"] != "" ? $doc["link_text"] : $GLOBALS["doc_file"]."section=".$docid)));

		if ($leadonly > -1 && $doc[title_clickable])
		{
			$this->vars(array(	"TITLE_LINK_BEGIN" => $this->parse("TITLE_LINK_BEGIN"),
						"TITLE_LINK_END" => $this->parse("TITLE_LINK_END")));
		};

		if ($GLOBALS["uid"] != "")
		{
			$this->vars(array("EDIT" => $this->parse("EDIT")));
		}

		$sht= "";
		if ($doc["show_title"] == 1)
		{
			$sht = $this->parse("SHOW_TITLE");
		}
		$this->vars(array("SHOW_TITLE" => $sht));

		// keeleseosed
		if ($this->is_template("LANG_BRO"))
		{
			$lab = unserialize($doc["lang_brothers"]);
			$langs = "";
			if (!is_array($larr))
			{
				classload("languages");
				$l = new languages;
				$larr = $l->listall();
				reset($larr);
				while (list(,$v) = each($larr))
				{
					if ($lab[$v[id]])
					{
						$this->vars(array("lang_id" => $v[id], "lang_name" => $v[name],"section" => $lab[$v[id]]));
						if ($GLOBALS["lang_id"] == $v[id])
							$langs.=$this->parse("SLANG_BRO");
						else
							$langs.=$this->parse("LANG_BRO");
					}
				}
				$this->vars(array("LANG_BRO" => $langs));
			}
		}

		// failide ikoonid kui on template olemas, namely www.stat.ee jaox
		if ($this->is_template("FILE"))
		{
			$ftypearr = array(
				"application/pdf" => "pdf",
				"text/richtext" => "rtf",
				"application/msword" => "doc",
				"application/vnd.ms-excel" => "xls",
				"text/html" => "html",
				"image/gif" => "gif"
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

		$cr = "";
		if ($doc["copyright"])
		{
			$cr = $this->parse("COPYRIGHT");
		}
		$this->vars(array("COPYRIGHT" => $cr));

 		$retval = $this->parse();
		// ??
		$this->reset;
		$awt->stop("db_documents->gen_preview()::end_inc");
		$awt->stop("doc_gen_preview");
		return $retval;
	}

	// kysib "sarnaseid" dokusid mingi välja kaudu
	// XXX
	function get_relations_by_field($params)
	{
		$field = $params[field]; // millisest väljast otsida
		$keywords = split(",",$params[keywords]); // mida sellest väljast otsida,
		$section = $params[section]; // millisest sektsioonist otsida
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

	function get_image_from_text($docid,$text)
	{
		if (preg_match("/#(\w+?)(\d+?)(p)#/i",$text,$match))
		{
				$idata = $img->get_img_by_oid($docid,$match[2]);
		}
		else
		{
			 $idata = false;
		};
		return false;
	}

	// kahtlane funktsioon. Mulle tundub kusjuures, et seda ei kasutatagi kusagil
	function import_image($tag,$content)
	{
		$this->tags[$tag] = $content;
	}

	function save($data)
	{
		// docid on ainuke parameeter, mis *peab* olema kaasa antud
		// ja siis veel vähemalt yx teine parameeter mida muuta

		$this->quote($data);
		if ($data[content]) {$data[content] = trim($data[content]);};
		if ($data[lead]) {$data[lead] = trim($data[lead]);};
		if ($data[cite]) {$data[cite] = trim($data[cite]);};
		$docid = $data[docid];
		$olddoc = $this->fetch($data[docid]);
		$q_parts = array();
		$changed_fields = array();

		reset($this->knownfields);
		// tsykkel yle koigi "tuntud" v2ljade, salvestame ainult 
		// nende sisu, mida vormis kasutati
		while(list($fcap,$fname) = each($this->knownfields))
		{
			if (isset($data[$fname]) || $fname=="esilehel" || $fname=="esileht_yleval" || $fname=="esilehel_uudis" || $fname=="is_forum" || $fname=="lead_comments" || $fname=="showlead" || $fname=="yleval_paremal" || $fname=="title_clickable")
			{
				$q_parts[] = "$fname = '$data[$fname]'";
				// paneme väljade nimed ka kirja, et formeerida logi
				// jaoks natuke informatiivsem teade
				$changed_fields[] = $fcap;
			};
		};
		
		// see paneb siis paringu kokku. Whee.
		$q = "UPDATE documents SET " . join(",\n",$q_parts) . " WHERE docid = '$docid'"; 
		$this->db_query($q);
		
		// siia moodustame objektitabeli päringu osad
		$oq_parts = array();

		$obj_known_fields = array("name","visible","status","parent");

		// seda on järgneva päringu koostamiseks vaja, sest objektitabelis pole "title"
		// välja. On "name"
		if ($data[title])
		{
			$data[name] = $data[title];
		};

		$oq_parts[oid] = $data[docid];

		while(list($fcap,$fname) = each($obj_known_fields))
		{
			if ($data[$fname])
			{
				$oq_parts[$fname] = $data[$fname];
			};
		};

		$this->upd_object($oq_parts);
		// logime aktsioone
		$this->_log("document","muutis dokumendi $docid '$title' välju " . join(",",$changed_fields));
	}

	function show_text($header, $text)
	{
		$this->set_root("automatweb/documents");
		$this->read_template("plain.tpl");
		global $docid;
		$this->vars(array("title" => $header, "text" => $text, "image" => "","docid" => $docid));
		return $this->parse();
	}


	function gen_list()
	{
		classload ("../vcl/table");
		global $baseurl,$PHP_SELF,$sortby;
		$t = new aw_table(array("prefix" => "documents",
														"sortby" => "docid",
														"lookfor" => $lookfor,
														"imgurl" => $baseurl."/vcl/img",
														"self"   => $PHP_SELF));
		$t->parse_xml_def("../vcl/documents.xml");

		$this->listall();
		while ($row = $this->db_next())
			$t->define_data($row);

		$t->sort_by(array("field" => $sortby));
		return $t->draw();
	}

	function select_alias($docid, $entry_id)
	{
		$this->read_template("alias_type.tpl");

		$ob = $this->get_object($entry_id);
		
		$karr = array();
		$this->db_query("SELECT * FROM objects WHERE parent = ".$ob[parent]." AND class_id = 12 AND objects.status != 0");
		while ($row = $this->db_next())
			$karr[$row[oid]] = $row[name];

		$this->vars(array("docid" => $docid, "alias" => $entry_id, "op_sel" => $this->picker("", $karr),"form_id" => $ob[parent]));
		return $this->parse();
	}

	function do_search($parent,$str,$sec,$sortby,$from)
	{
		if ($sortby == "")
		{
			$sortby = "percent";
		}

		$this->tpl_init("automatweb/documents");
	
		if ($str == "")
		{
			$this->read_template("search_none.tpl");
			return $this->parse();
		}

		$this->read_template("search.tpl");

		// make list of menus
		$this->menucache = array();
		$this->db_query("SELECT objects.oid as oid, objects.parent as parent,objects.last as last,objects.status as status
										 FROM objects 
										 WHERE objects.class_id = 1 AND objects.status = 2");
		while ($row = $this->db_next())
			$this->menucache[$row[parent]][] = $row;

		// now, make a list of all menus below $parent
		$this->marr = array();
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

		$cnt = 0;
		//max number of occurrences of search string in document
		$max_count = 0;
		$docarr = array();
		$this->db_query("SELECT documents.*,objects.parent as parent, objects.modified as modified 
										 FROM documents 
										 LEFT JOIN objects ON objects.oid = documents.docid
										 WHERE (documents.title LIKE '%".$str."%' OR documents.content LIKE '%".$str."%') AND objects.status = 2 AND (documents.no_search is null OR documents.no_search = 0) $ml");
		while($row = $this->db_next())
		{
			// find number of matches in document for search string, for calculating percentage
			// if match is found in title, then multiply number by 5, to emphasize importance
			$c = substr_count(strtoupper($row[content]),strtoupper($str)) + substr_count(strtoupper($row[title]),strtoupper($str))*5;
			$max_count = max($c,$max_count);

			// find the first paragraph of text
			$co = strip_tags($row[content]);
			$co = substr($co,0,strpos($co,"\n"));

			$docarr[] = array("matches" => $c, "title" => $row[title],"section" => $row[docid],"content" => $co,"modified" => $this->time2date($row[modified],2));

			
			$cnt++;
		}

		if ($sortby == "percent")
		{
			$d2arr = array();
			reset($docarr);
			while (list(,$v) = each($docarr))
			{
				if ($max_count > 0)
				{
					$d2arr[($v[matches]*100) / $max_count][] = $v;
				}
				else
				{
					$d2arr[0][] = $v;
				}
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
				if ($max_count > 0)
				{
					$percent = substr(($v[matches]*100) / $max_count,0,4);
				}
				else
				{
					$percent = "0";
				}
				$this->vars(array("title"			=> $v[title],
													"percent"		=> $percent,
													"content"		=> preg_replace("/#(.*)#/","",$v[content]),
													"modified"	=> $v[modified],
													"section"		=> $v[section]));
				$r.= $this->parse("MATCH");
			}
			$num++;
		}

		$this->vars(array("MATCH" => $r,"s_parent" => $parent,"sstring" => $str,"section" => $sec,"matches" => $cnt,"sortby" => $sortby));

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

			mail("\"$to_name\" <".$to.">","Artikkel saidilt www.nadal.ee",$text,"From: \"$from_name\" <".$from.">\nSender: \"$from_name\" <".$from.">\nReturn-path: \"$from_name\" <".$from.">".$bcc."\n\n");
		}
	}

	function add_rating($docid, $hinne)
	{
		$hinne = $hinne+0;
		if ($hinne > 0)
			$this->db_query("UPDATE documents SET rating=rating+$hinne , num_ratings=num_ratings+1 WHERE docid = $docid");
	}

	function telekava_doc($content)
	{
		$paevad = array("0" => "#telekava_neljapaev#", "1" => "#telekava_reede#", "2" => "#telekava_laupaev#", "3" => "#telekava_pyhapaev#", "4" => "#telekava_esmaspaev#", "5" => "#telekava_teisipaev#", "6" => "#telekava_kolmapaev#");
		reset($paevad);
		while (list($num, $v) = each($paevad))
			if (strpos($content,$v) === false)
				continue;
			else
				break;

		// arvutame v2lja, et millal oli eelmine neljap2ev
		$sub_arr = array("0" => "3", "1" => "4", "2" => "5", "3" => "6", "4" => "0", "5" => "1", "6" => "2");
		$date = mktime(0,0,0,date("m"),date("d"),date("Y"));

		$d_begin = $date - $sub_arr[date("w")]*24*3600;
		$rdate = $d_begin+$num*24*3600;

		classload("tvkavad");
		$t = new tvkavad;
		return $t->kanalid_list($rdate);
	}

	function brother($id)
	{
		$this->read_template("brother.tpl");
		$sar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($arow = $this->db_next())
			$sar[$arow[parent]] = $arow[parent];

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
			$sar[$row[parent]] = $row[parent];
			$oidar[$row[parent]] = $row[oid];
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
				$noid = $this->new_object(array("parent" => $oid,"class_id" => CL_BROTHER_DOCUMENT,"status" => 1,"brother_of" => $docid,"name" => $obj[name],"comment" => $obj[comment]));
			}
		}

		return $obj[parent];
	}

	function convbr()
	{
		$this->db_query("SELECT * FROM documents LEFT JOIN objects ON objects.oid = documents.docid WHERE objects.site_id = 4");
		while ($row = $this->db_next())
		{
			$row[content] = str_replace("\r\n","<br>",$row[content]);
			$row[lead] = str_replace("\r\n","<br>",$row[lead]);
			$this->quote(&$row[content]);
			$this->quote(&$row[lead]);
			$this->save_handle();
			$this->db_query("UPDATE documents set content = '".$row[content]."', lead = '".$row[lead]."' where docid = ".$row[docid]);
			$this->restore_handle();
			echo "replaced doc ",$row[title],"<br>";
			flush();
		}
	}

	function ekomar_form($id)
	{
		$tt = new aw_template();
		$tt->sub_merge = 1;
		$tt->tpl_init("automatweb/documents");
		$tt->read_template("ekomar_form.tpl");
		$tt->vars(array("section" => $GLOBALS["section"],"notfound" => $id));
		$r = $tt->parse();
		return $r;
	}

	function search_ekomar($id)
	{
		$tt = new aw_template();
		$tt->sub_merge = 1;
		$tt->tpl_init("automatweb/documents");

		global $f_ark, $f_name;
		$ss = array();
		if (($f_ark != "")&&(strlen($f_ark)>7) && ($f_ark > 10000000 && $f_ark < 11000000))
		{
			$ss[] = "ark = '$f_ark'";
		}
		if (($f_name != "")&&(strlen($f_name)>2))
		{
			$ss[] = "name LIKE '%$f_name%'";
		}
		$sstr = join(" OR ",$ss);
		if ($sstr != "")
		{
			$tt->read_template("erkomar_co.tpl");
			$this->db_query("SELECT * FROM ekomar_cos WHERE $sstr");
			$cnt=0;
			while ($row = $this->db_next())
			{
				$cnt ++;
				$this->save_handle();
				$this->db_query("SELECT id FROM ekomar_files WHERE name LIKE '".$row[filename]."%'");
				$fr = $this->db_next();
				$this->restore_handle();

				$tt->vars(array("ark" => $row[ark], "name" => $row[name],
												"file" => $GLOBALS["baseurl"]."/ekomar.".$GLOBALS["ext"]."/id=".$fr[id]."/EKOMAR2000.XLS",
												"contact" => $row[contact],
												"phone" => $row[phone]));
				if ($fr)
					$f = $tt->parse("FILE");
				else
					$f = "";
				$tt->vars(array("FILE" => $f));
				$tt->parse("LINE");
			}
			if ($cnt == 0)
			{
				header("Location: ".$GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$GLOBALS["notfound"]."/s_name=$f_name/s_ark=$f_ark");
//				$tt->read_template("erkomar_nores.tpl");
			}
			return $tt->parse();
		}
		else
		{
			$tt->read_template("ekomar_form.tpl");
			$tt->vars(array("section" => $GLOBALS["section"],"notfound" => $id));
			if ($f_ark != "" && ($f_ark <= 10000000 || $f_ark >= 11000000))
			{
				$tt->vars(array("ERROR_ARK" => $tt->parse("ERROR_ARK")));
			}
			return $tt->parse();
		}
	}

	function ekomar_failid()
	{
		$tt = new aw_template();
		$tt->sub_merge = 1;
		$tt->tpl_init("automatweb/documents");
		$tt->read_template("ekomar_failid.tpl");

		$this->db_query("SELECT name,comment,id FROM ekomar_files");
		while ($row = $this->db_next())
		{
			$tt->vars(array("name"		=> $row[name], 
											"comment"	=> $row[comment],
											"link"		=> $GLOBALS["baseurl"]."/ekomar.".$GLOBALS["ext"]."/id=".$row[id]."/EKOMAR2000.XLS"));
			$tt->parse("LINE");
		}
		return $tt->parse();
	}
};
?>
