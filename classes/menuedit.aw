<?php
// $Header: /home/cvs/automatweb_dev/classes/menuedit.aw,v 2.10 2001/05/23 18:22:35 kristo Exp $
// menuedit.aw - menuedit. heh.
global $orb_defs;
$orb_defs["menuedit"] = "xml";

// muh? mes number?
// seep see nummer mille kaudu tuntakse 2ra kui tyyp klikkid kodukataloog/SHARED_FOLDERS peale
define(SHARED_FOLDER_ID,2147483648);

session_register("cut_objects");
session_register("copied_objects");

lc_load("menuedit");
classload("cache","validator","defs");

	class menuedit extends aw_template
	{
		// me peame ju kuidagi vahet tegema, kas vaatame perioodi
		// voi lihtsalt koiki staatilisi dokumente
		function menuedit($period = 0,$pname = "")
		{
			$this->tpl_init("automatweb/menuedit");
			$this->db_init();
			$this->cache = new cache;
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

		////
		// !simpel menyy lisamise funktsioon. laienda kui soovid. Mina kasutan seda saidi seest
		// uue folderi lisamiseks kodukataloogi alla
		function add_new_menu($args = array())
		{
			// ja eeldame, et meil on v‰hemalt parent ja name olemas.
			$this->quote($args["name"]);
			$newoid = $this->new_object(array(
				"name" => $args["name"],
				"parent" => $args["parent"],
				"status" => 2,
				"class_id" => CL_PSEUDO,
			));
			$q = sprintf("INSERT INTO menu (id,type) VALUES (%d,%d)",$newoid,MN_HOME_FOLDER_SUB);
			$this->db_query($q);
			$this->_log("menuedit","Lisas kodukataloogi folderi $args[name]");
			return $newoid;

		}

		function rd($parent)
		{
			
			$this->db_query("SELECT * FROM objects WHERE parent = $parent AND class_id = 1 AND status != 0 AND objects.lang_id=".$GLOBALS["lang_id"]."");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				$this->rd($row[oid]);
				$this->restore_handle();
			}

			$this->delete_object($parent);
		}

		////
		// !Genereerib kasutaja kodukataloogi saidi sisse
		function gen_home($args = array())
		{
			$this->read_template("main.tpl");
			global $uid;
			$c = $uid . " kodukataloog";
			$this->vars(array(
				"doc_content" => $c,
			));
			return $this->parse();
		}

	// parameetrid:
	// section - millist naidata?
	// text - kui see != "" , siis n2idatakse dokude asemel seda
	// docid - millist dokumenti selle sektsiooni alt naidata?
	// s.t. kui on dokumentide nimekiri ntx.
	// strip_img - kas imaged maha strippida dokudest
	// template - mis template ga menyysid n2idataxe
	// vars - array kuhu saab sisu kirjutada, et seal
	//	olevad muutujad pannaxe menyyediti template sisse
		function gen_site_html($params)
		{
			// kontrollib sektsiooni ID-d, tagastab oige numbri kui tegemist oli
			// aliasega, voi lopetab tˆˆ, kui miskit oli valesti
			$section = $this->check_section($params["section"]);
			$params["section"] = $section;

			// koostame array vajalikest parameetritest, mis identifitseerivad cachetava objekti
			$cp = array();
			$periodic = $this->is_periodic($section,0);
			if ($periodic)
			{
				$cp[] = $GLOBALS["act_per_id"];
			};
		
			// format=rss ntx
			if (!($res = $this->cache->get($section,$cp)) || $params["format"])
			{
				// seda objekti pold caches
				$res = $this->_gen_site_html($params);
				$this->cache->set($section,$cp,$res);
				echo "<!-- no cache $section <pre>",join("-",$cp),"</pre>\n-->";
			}
			else
			{
				// kui asi on caches, siis paneme kirja et mis lehte vaadatati.
				$ch = $this->get_object_chain($section);
				reset($ch);
				while (list($k,$v) = each($ch))
				{
					$str=$v["name"]." / ".$str;
				}
				$this->do_syslog_core($str,$section);
			}

			// make sure that the banner random id's are different each time around, even when the site is cached.
			global $banner_defs;
			if (is_array($banner_defs))
			{
				reset($banner_defs);
				while (list($name,$gid) = each($banner_defs))
				{
					$res = str_replace("[ss".$gid."]",$this->gen_uniq_id(),$res);
				}
			}
			return $res;
		}
				
		function _gen_site_html($params)
		{
			$section 	= $params["section"];
			$text		= $params["text"];
			$docid 		= $params["docid"];
			$strip_img 	= $params["strip_img"];
			$template 	= $params["template"] == "" ? "main.tpl" : $params["template"];
			$homedir 	= $params["homedir"];
			$special	= $params["special"];
			$format 	= $params["format"];
			// niisiis. vars array peaks sisaldama mingeid pre-parsed html t¸kke,
			// mis vıivad tulla ntx kusagilt orbi klassi seest vtm.

			// array keydeks peaksid olema variabled template sees, mis siis asendatakse
			// oma v‰‰rtustega
			$vars 		= $params["vars"];

			$esilehel       = $params["esilehel"];

			// debuukimiseks
			global $SITE_ID;
		  	// impordime taimeriklassi
			global $awt;

			global $test;
			global $baseurl;

			$obj = $this->get_object($section);
			if ($obj["class_id"] == CL_BROTHER_DOCUMENT)
			{
				$section=$obj["parent"];
				$docid=$obj["brother_of"];
			}

			if ($format == "rss")
			{
				die($this->do_rdf($section,$obj,$format,$docid));
			}

			// main.tpl-i muutuste testimiseks ilma seda oiget main.tpl-i muutmata
			// kasutasin seda ntx skycraper b‰nneri sobitamiseks seltskonna sisse
			if ($test == 1)
			{
				$template = "main2.tpl";
			};
			// ja kaivitame selle
			$awt->start("menuedit_show");
	
			// by default show both panes.
			$this->left_pane = $params["no_left_pane"] ? false : true;
			$this->right_pane = $params["no_right_pane"] ? false : true;

			// read all the menus and other necessary info into arrays from the database
			$this->make_menu_caches();

			$this->read_template($template);

			// laeme dokumentide klassi
			classload("periods","documents");
			$d = new db_documents();
		  
			// leiame, kas on tegemist perioodilise rubriigiga
 			$periodic = $this->is_periodic($section);

			$this->sel_section = $section;

			// loome sisu
			if ($obj["class_id"] == CL_PSEUDO && $this->is_link_collection($section) && $text == "")
			{
				// tshekime et kas 2kki on selle menyy all lingikogu. kui on, siis joonistame selle.
				$this->vars(array("doc_content" => $this->do_link_collection($section)));
				$this->read_template($template);
			}
			else
			if ($obj["class_id"] == CL_PSEUDO && (($sh_id = $this->is_shop($section)) > 0) && $text == "")
			{
				// tshekime et kas 2kki on selle menyy all pood. kui on, siis joonistame selle.
				$this->vars(array("doc_content" => $this->do_shop($section,$sh_id)));
				$this->read_template($template);
			}
			else
			if ($periodic && $text == "") 
			{
				$this->vars(array("doc_content" => $this->show_periodic_documents($section,$obj,$format)));
			} 
			else 
			if ($text == "")
			{
				// sektsioon pole perioodiline
				$this->vars(array("doc_content" => $this->show_documents($section,$docid)));
	    }
			else
			{
				// text on ette antud
				$this->vars(array("doc_content" => $text));
			}

			// import language constants
			lc_site_load("menuedit");
			if (is_array($GLOBALS["lc_menuedit"]))
			{
				$this->vars($GLOBALS["lc_menuedit"]);
			}

			// get array with path of objects in it
			$path = $this->get_path($section,$obj);

			// you are here links		
			$this->vars(array("YAH_LINK" => $this->make_yah($path)));

			// write info about viewing to the syslog
			$this->do_syslog(&$this->mar,&$path,count($path)-1,$section);

			// right, now build the menus
			global $menu_defs,$rootmenu;

			// this will contain all the menus parsed from templates
			$outputs = array();	

			$ce = false;
	
			$awt->start("cycle");
			if ($GLOBALS["MENUEDIT_VER2"])
			{
				global $menu_defs_v2,$frontpage;

				$awt->start("menuedit_ver2");
				if (is_array($menu_defs_v2))
				{
					reset($menu_defs_v2);
					while (list($id,$name) = each($menu_defs_v2))
					{
						$this->req_draw_menu($id,$name,&$path,false);
						if ($this->sel_section == $frontpage)
						{
							$this->do_seealso_items($this->mar[$id],$name);
						}
					}
				}
				$awt->stop("menuedit_ver2");
			}
			else
			{
			reset($this->mar);
			global $ext;
			while (list(,$row) = each($this->mar))
			{
				// find the menu this object belongs under
				// we build the subtemplate name from the name of the menu in the definition
				$cur_menu = "";
				$level = -1;
				$pt = $row[oid];
				// pt on parent?
				while ($pt != $rootmenu && $pt > 1)
				{
					if ($this->mar[$pt][parent] == $rootmenu)
					{
						if (is_array($menu_defs[$pt]))
						{
							$cur_menu = $menu_defs[$pt];
							$cur_menu[id] = $pt;
						}
					}

					$pt = $this->mar[$pt][parent];
					$level++;
				}
				// kach mingi kahtlase v‰‰rtusega h‰kk
				$mn = "MENU_".$cur_menu[name]."_L".$level."_ITEM";	$ap = "";
				$outputs[$mn] .= "";
				// fakk. GOTO ja selle derivatiivid imevad. Ja seda kohe sitta moodi
				// we must only show menus that are marked to be shown

				if ($row[mtype] != MN_CONTENT || $level < 1)
					continue;

				if ($level > 1 && !in_array($row[parent],$path))
					continue;

				if (is_array($cur_menu))
				{
					// kas naidata menyyd, kui seal aktiivseid elemente pole? (hide_noact)
					if ($row[hide_noact])
					{
						// kaime ka alamdokud labi.
						if (!$this->has_sub_dox($row[oid]))
							continue;
					}

					// tshekime, et kui selle template sees on j2rgmise taseme template,
					// siis joonistame selle enne valmis.
					// see on sellex, et saax vibe-moodi asju teha, kus on k6ik menyyd kohe n2ha
					$mmn = "MENU_".$cur_menu[name]."_L".($level+1)."_ITEM";
					$mmmm = ""; //$mmmm2 = "";
					if ($this->is_parent_tpl($mmn, $mn))
					{
						// make next level tuu
						if (is_array($this->mpr[$row[oid]]))
						{
							reset($this->mpr[$row[oid]]);
							while (list(,$mrow) = each($this->mpr[$row[oid]]))
							{
								$ap = "";
								// niisiis. Kui menyyelemendil on link defineeritud
								// siis kasutame seda
								if ($mrow[link] != "")
								{
									$link = $mrow[link];
								}
								else
								// kui mitte, siis
								{
									$link = $GLOBALS["baseurl"] . "/";
									// kasutame 
									// a)aliast, kui see on olemas
									// b)sektsiooni id-d 
									$link .= ($mrow[alias] != "") ? $mrow[alias] : "index." . $ext . "/section=" . $mrow[oid];
								}
								// hiljem voib-olla tekib tahtmine siia mingeid muid targeteid lisada?
								$href_target = "_new";
								$target = ($mrow[target] == 1) ? sprintf("target='%s'",$href_target) : "";
								$this->vars(array(	"text" => $mrow[name],
											"link" => $link,
											"target" => $target,
											"image"		=> ($mrow[img_url] != "" ? "<img src='".$mrow[img_url]."' border='0'>" : ""),
											"section" => $mrow[oid]));
								// if this is selected
								if (in_array($mrow[oid],$path) && $mrow[clickable] == 1)
								{
									$ap="_SEL";
								};
								if (($mmmm2 == "" && $mrow[mid] == 1))
								{
									$ap.="_BEGIN";
								};
								if ($mrow[clickable] != 1)
								{
									$ap.="_SEP";
								};
								if (is_array($this->mpr[$mrow[oid]]))
								{
									$hs = $this->parse("HAS_SUBITEMS_".$cur_menu[name]);
								}
								else
								{
									$hs = $this->parse("NO_SUBITEMS_".$cur_menu[name]);
								}
								$this->vars(array("HAS_SUBITEMS_".$cur_menu[name] => $hs));
								if ($mrow[mid] == 1)
								{
									// need jobud tahavad, et keskmisi menyysid
									// n2idatakse aint valitud menyyde all.
									if (in_array($mrow[parent],$path))
									{
										$has_mid = true;
										$mmmm2.=$this->parse($mmn."_MID".$ap);
									};
								}
								else
								{
									$mmmm.=$this->parse($mmn.$ap);
								};
							} // while (tsykkel yle parentite)(next_level level)
						} // if (next_level)
						$has_items[$cur_menu["name"]] = true;
						$this->vars(array(	$mmn => $mmmm,
									$mmn."_MID" => $mmmm2));
						$ce = true;
					}
					// ja et siis esimene tase.	
					$ap = "";
					if ($row[link] != "")
					{
						$link = $row[link];
					}
					else
					{
						$link = $GLOBALS["baseurl"] . "/";
						$link .= ($row[alias] != "") ? $row[alias] : "index." . $ext . "/section=" . $row[oid];
					}
					// hiljem voib-olla tekib tahtmine siia mingeid muid targeteid lisada?
					$href_target = "_new";
					$target = ($row[target] == 1) ? sprintf("target='%s'",$href_target) : "";
					$this->vars(array(	"text" 		=> $row[name],
								"link" 		=> $link,
								"section" 	=> $row[oid],
								"target" 	=> $target,
								"image"		=> ($row[img_url] != "" ? "<img src='".$row[img_url]."' border='0'>" : "")));

					// if this is the first one
					if ($outputs[$mn] == "" && $this->is_template($mn."_BEGIN"))
					{
						$ap.="_BEGIN";
					};

					// if this is selected
					if (in_array($row[oid],$path) && $row[clickable] == 1)
					{
						$ap.="_SEL";
						$this->vars(array($mn."_TEXT" => $row[name]));
					};

					if ($row[clickable] != 1)
					{
						$ap.="_SEP";
					};

					if ($this->is_template($mn.$ap."_NOSUB") && $mmmm == "")
					{
						$ap.="_NOSUB";
					};

					if (!$this->is_template($mn.$ap))
					{
						$ap = "";
					};

					if (is_array($this->mpr[$row[oid]]))
					{
						$hs = $this->parse("HAS_SUBITEMS_".$cur_menu[name]);
					}
					else
					{
						$hs = $this->parse("NO_SUBITEMS_".$cur_menu[name]);
					}
					$this->vars(array("HAS_SUBITEMS_".$cur_menu[name] => $hs));

					if ($this->is_parent_tpl("DOC_LINK",$mn))
					{
						// make a list of all documents under the menu
						// and any other menus we must get the documents from
						$ob = " jrk ";
						if ($row[ndocs] > 0)
						{
							$ob = " objects.modified DESC ";
						}
						$sss = unserialize($row[sss]);
						$sss[$row[oid]] = $row[oid];
						$parstr = join(",",$sss);
						$this->db_query("SELECT * FROM objects WHERE status = 2 AND class_id = ".CL_DOCUMENT." AND site_id = ".$GLOBALS["SITE_ID"]." AND parent IN($parstr) ORDER BY $ob");
						$dl = "";
						$cnt = 1;
						while ($drow = $this->db_next())
						{
							if (!($row[ndocs] > 0 && $cnt > $row[ndocs]))
							{
								$this->vars(array("link" => $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$drow[oid], 
																	"title" => $drow[name]));
								$dl.=$this->parse("DOC_LINK");
							}
							$cnt ++;
						}
						$this->vars(array("DOC_LINK" => $dl));
					}
					// siin on siis koht mis tshekib, et kui me joonistame parajasti n2dala saidi
					// paremat paani, siis topime menyyde vahele
					// dokude leadid ka. issand kui kohutav.
					// EEEK!
					if ($GLOBALS["SITE_ID"] == 3 && $cur_menu[name] == "PAREM")
					{	
						$awt->start("nn_parem");
						// ja ongi nii.
						$do = new db_documents;
						// KLUK KLUK

						//$period = $per->rec_get_active_period($cur_menu[id]);
						$periods = new db_periods(190);	// yeah. vasak menyy. ja ei koti.
						$period = $periods->get_active_period(188);
						if ($this->subs[$row[oid]] > 0) {
							$did = $do->db_fetch_field("SELECT docid FROM documents LEFT JOIN objects ON objects.oid = documents.docid WHERE objects.period = '$period' AND documents.yleval_paremal = 1 AND objects.status = 2 AND objects.parent = ".$row[oid]." AND objects.lang_id=".$GLOBALS["lang_id"],"docid");
							$done = $do->gen_preview(array("docid" => $did, "tpl" => "nadal_film_side_lead.tpl","leadonly" => 1, "section" => $row[oid], 	"strip_img" => 1));
							$this->vars(array("lugu" => $done));
							$outputs[$mn] .= $this->parse($mn.$ap);
						};
						$awt->stop("nn_parem");
					} 
					else 
					if ($row[mid] == 1)
					{
						// need jobud tahavad, et keskmisi menyysid n2idatakse aint valitud menyyde all.
						if (in_array($row[parent],$path) && $this->is_template($mn.$ap."_MID") && $mmmm2 == "")
						{
							$ap.="_MID";
							$midd.=$this->parse($mn.$ap);
							$has_mid = true;
						}
						else
						{
							$ap = "";
							$outputs[$mn] .= $this->parse($mn.$ap);
						}
					}
					else
					{
						$outputs[$mn] .= $this->parse($mn.$ap);
					}
					$has_items[$cur_menu["name"]] = true;
				}
			}

			$this->vars(array("MENU_menyy1_L2_ITEM_MID" => $midd));

			if ($has_mid)
			{
				$this->vars(array("HAS_MID" => $this->parse("HAS_MID")));
			}
			$used = false;
			reset($outputs);
			while (list($k,$v) = each($outputs))
			{
				$this->vars(array($k => $v,$k."_SEL" => "",$k."_BEGIN" => "",$k."_BEGIN_SEL" => "",$k."_SEP" => "",$k."_BEGIN_SEP" => "",$k."_NOSUB" => "",$k."_SEL_NOSUB" => ""));
				if ($k == "MENU_ALUMINE_L2_ITEM")
					$used = true;
			}
		
			if (!$ce)
			{
				$this->vars(array("MENU_menyy1_L3_ITEM_MID" => "", "MENU_menyy1_L3_ITEM_MID_SEL" => "", "MENU_menyy1_L3_ITEM_MID_SEP" => "", "MENU_menyy1_L3_ITEM_MID_BEGIN" => "", "MENU_menyy1_L3_ITEM_MID_SEL_BEGIN" => "", "MENU_menyy1_L3_ITEM_MID_BEGIN_SEP" => ""));
			}

			if (is_array($has_items))
			{
				reset($has_items);
				while (list($name,) = each($has_items))
				{
					$this->vars(array("IS_MENU_".$name => $this->parse("IS_MENU_".$name)));
				}
			}
			} // end MENUEDIT_VER2

			$awt->stop("cycle");

			$this->make_promo_boxes($section);
			$this->make_poll();
			$this->make_search();
			$this->make_nadalanagu();

			$this->make_banners();

			global $sstring;
			$this->vars(array(
				"ss" => $this->gen_uniq_id(),		// bannerite jaox
				"ss2" => $this->gen_uniq_id(),
				"ss3" => $this->gen_uniq_id(),
				"link" => "",
				"section"	=> $section,
				"sstring" => $sstring,
				"uid" => $GLOBALS["uid"], 
				"date" => $this->time2date(time(), 2),
				"PRINTANDSEND" => $PRINTANDSEND,
				"CHANGEDOCUMENT" => $this->active_doc ? $this->parse("CHANGEDOCUMENT") : ""
			));
			if (is_array($vars))
			{
				$this->vars($vars);
			}

			if ($GLOBALS["uid"] != "")
			{
				classload("users");
				$t = new users;
				$udata = $t->fetch($GLOBALS["uid"]);
				$jfar = $t->get_jf_list($udata["join_grp"]);
				reset($jfar);
				while (list($fid,$name) = each($jfar))
				{
					$this->vars(array("form_id" => $fid, "form_name" => $name));
					$jfs.=$this->parse("JOIN_FORM");
				}
				$this->vars(array("JOIN_FORM" => $jfs));

				// check menuedit access
				if ($this->prog_acl("view", PRG_MENUEDIT))
				{
					$this->vars(array("MENUEDIT_ACCESS" => $this->parse("MENUEDIT_ACCESS")));
				}
				$login = $this->parse("logged");
			}
			else
			{
				$login = $this->parse("login");
			}

			$this->vars(array("login" => $login, "logged" => ""));

			$lp = "";
			$rp = "";
			if ($this->left_pane)
			{
				$lp = $this->parse("LEFT_PANE");
			}
			if ($this->right_pane)
			{
				$rp = $this->parse("RIGHT_PANE");
			}
			$this->vars(array("LEFT_PANE" => $lp, "RIGHT_PANE" => $rp));

		  $awt->stop("menuedit_show");
			$this->li_cache = $d->li_cache;
			return $this->parse();
		}

	  function is_periodic($section,$checkobj = 1) 
		{
			 $q = "SELECT periodic FROM menu WHERE id = '$section'";
			 $periodic = $this->db_fetch_field($q,"periodic");
			 // menyysektsioon ei ole perioodiline. Well, vaatame 
			 // siis, kas ehk dokument ise on?
			 if (!$periodic && $checkobj == 1) {
			 	$q = "SELECT period FROM objects WHERE oid = '$section'";
				$periodic = $this->db_fetch_field($q,"period");
			 };
			 return $periodic;
		}

		function has_sub_dox($oid)
		{
			$has_dox = $this->subs[$oid] > 0 ? 1 : 0;
			
			if (is_array($this->mpr[$oid]))
			{
				reset($this->mpr[$oid]);
				while (list(,$row) = each($this->mpr[$oid]))
				{
					$has_dox |= $this->has_sub_dox($row[oid]);
					if ($this->subs[$row[oid]] > 0)
					{
						$has_dox = 1;
					}
				}
			}

			return $has_dox;
		}

		function db_prep_listall($where = " objects.status != 0") 
		{
			global $awt;
			$awt->start("menuedit_prep_listall");
			global $act_per_id;
			if ($act_per_id) 
			{
				$sufix = "AND period = '$act_per_id'";
			} 
			else 
			{
				$sufix = "";
			};
			$q = "SELECT objects.parent AS parent,count(*) as subs
							FROM objects WHERE $where $sufix
							GROUP BY parent";
			$this->subs = array();
			$this->db_query($q);
			while($row = $this->db_next()) 
			{
				$this->subs[$row[parent]] = $row[subs];
			};
			$awt->stop("menuedit_prep_listall");
		}

		////
		// !Listib koik objektid
		function db_listall($where = " objects.status != 0",$ignore = false,$ignore_lang = false)
		{
			global $awt;
			global $SITE_ID;
			$awt->start("menuedit_listall");
			if (!$ignore)
			{
				// loeme sisse koik objektid
				$aa = "AND ((objects.site_id = '".$GLOBALS["SITE_ID"]."') OR (objects.site_id IS NULL))";
			};
			if ($GLOBALS["lang_menus"] == 1 && $ignore_lang == false)
			{
				$aa.="AND (objects.lang_id=".$GLOBALS["lang_id"]." OR menu.type = ".MN_CLIENT.")";
			}
			$q = "SELECT objects.oid as oid, 
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
					objects.alias as alias,
					objects.class_id as class_id,
					objects.brother_of as brother_of,
					menu.type as mtype,
					menu.link as link,
					menu.clickable as clickable,
					menu.target as target,
					menu.ndocs as ndocs,
					menu.img_id as img_id,
					menu.img_url as img_url,
					menu.hide_noact as hide_noact,
					menu.mid as mid,
					menu.sss as sss,
					menu.links as links,
					menu.icon_id as icon_id,
					menu.admin_feature as admin_feature,
					menu.periodic as mperiodic,
					menu.is_shop as is_shop,
					menu.shop_id as shop_id
				FROM objects 
					LEFT JOIN menu ON menu.id = objects.oid
					WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")  AND $where $aa
					ORDER BY objects.parent, jrk,objects.created";
			$this->db_query($q);
			$awt->stop("menuedit_listall");
		}

		function db_listall_lite($where = " objects.status != 0",$ignore = false,$ignore_lang = false)
		{
			global $awt;
			global $SITE_ID;
			$awt->start("menuedit_listall_lite");
			if (!$ignore)
			{
				// loeme sisse koik objektid
				$aa = "AND ((objects.site_id = '".$GLOBALS["SITE_ID"]."') OR (objects.site_id IS NULL))";
			};
			if ($GLOBALS["lang_menus"] == 1 && $ignore_lang == false)
			{
				$aa.="AND (objects.lang_id=".$GLOBALS["lang_id"]." OR menu.type = ".MN_CLIENT.")";
			}
			$q = "SELECT objects.oid as oid, 
					objects.parent as parent,
					objects.name as name,
					objects.last as last,
					objects.jrk as jrk,
					objects.alias as alias,
					objects.brother_of as brother_of,
					menu.link as link,
					menu.type as mtype,
					menu.clickable as clickable,
					menu.target as target,
					menu.ndocs as ndocs,
					menu.img_url as img_url,
					menu.hide_noact as hide_noact,
					menu.mid as mid,
					menu.is_shop as is_shop,
					menu.seealso as seealso,
					menu.shop_id as shop_id,
					menu.width as width
				FROM objects 
					LEFT JOIN menu ON menu.id = objects.oid
					WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")  AND $where $aa
					ORDER BY objects.parent, jrk,objects.created";
			$this->db_query($q);
			$awt->stop("menuedit_listall_lite");
		}

		function submit_move_docs($arr)
		{
			$tomove = array();
			reset($arr);
			while (list($k,$v) = each($arr))
			{
				if (substr($k,0,3) == "do_")
				{
					$tomove[] = substr($k,3);
				}
			}

			$ts=join(",",$tomove);
			if ($ts != "")
			{
				$this->db_query("UPDATE objects SET parent = ".$arr[dest]." WHERE oid IN ($ts)");
			}
		}

		function get_default_document($section,$ignore_global = false)
		{
			global $awt;
			$awt->count("menuedit->get_default_document()");
			$awt->start("menuedit->get_default_document()");

			if ($GLOBALS["docid"] && $ignore_global == false)
			{
				$awt->stop("menuedit->get_default_document()");
				return $GLOBALS["docid"];
			}
			if (!$section)
			{
				$awt->stop("menuedit->get_default_document()");
				return 0;
			}
	
			$obj = $this->get_object($section);	// if it is a document, use this one. 
			if ($obj[class_id] == CL_DOCUMENT)
			{
				$awt->stop("menuedit->get_default_document()");
				return $section;
			}

			if ($obj[class_id] == CL_BROTHER)
			{
				$obj = $this->get_object($obj[brother_of]);
			}

			$docid = $obj[last];
			$ar = unserialize($docid);
			if (is_array($ar))	// kuna on vaja mitme keele jaox default dokke seivida, siis uues versioonis pannaxe siia array
													// aga backward compatibility jaox tshekime, et 2kki see on integer ikkagi
			{
				$docid = $ar[$GLOBALS["lang_id"]];
			}
			if ($docid > 0)
			{
				if ($GLOBALS["lang_menus"] == 1)
				{
					$ss = "AND objects.lang_id=".$GLOBALS["lang_id"];
				}
				if ($this->db_fetch_field("SELECT status FROM objects WHERE oid = $docid AND class_id = ".CL_DOCUMENT." $ss ","status") != 2)	
				{
					// make sure that the default is not deleted
					$docid = 0;
				}
			}
			// ei olnud defaulti, peaks vist .. n‰itama nimekirja? 
			if ($docid < 1)	
			{
				$docid = array();
				$cnt = 0;
				$this->db_query("SELECT oid FROM objects WHERE (parent = $section AND status = 2 AND class_id = 7 AND objects.lang_id=".$GLOBALS["lang_id"].") OR (class_id = ".CL_BROTHER_DOCUMENT." AND status = 2 AND parent = $section) ORDER BY jrk");
				while ($row = $this->db_next())
				{
					$docid[$cnt++] = $row["oid"];
				}

				if ($cnt > 1)
				{
					// a list of documents
					$awt->stop("menuedit->get_default_document()");
					return $docid;
				}
				else
				{
					// the correct id
					$awt->stop("menuedit->get_default_document()");
					return $docid[0];
				}
			}

			$awt->stop("menuedit->get_default_document()");
			return $docid;
		}

		function do_syslog_core($log,$section)
		{
			global $uid,$artid,$sid,$mlxuid,$awt;
			$awt->start("syslog_core");
			if ($artid)	// tyyp tuli meilist, vaja kirja panna
			{
				if (is_number($artid))
				{
					$sid = (int)$sid;
					$this->db_query("SELECT * FROM ml_mails WHERE id = $sid");
					$ml_msg = $this->db_next();

					$this->db_query("SELECT ml_users.*,objects.name as name FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE id = $artid");
					if (($ml_user = $this->db_next()))
					{
						$this->_log("pageview",$ml_user[name]." (".$ml_user[mail].") tuli lehele $log meilist ".$ml_msg[subj],$section);

						// and also remember the guy
						// set a cookie, that expires in 3 years
						setcookie("mlxuid",$artid,time()+3600*24*1000,"/");
					}
				}
			}
			else
			if ($mlxuid)
			{
					$this->db_query("SELECT ml_users.*,objects.name as name FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE id = $mlxuid");
				if (($ml_user = $this->db_next()))
					$this->_log("pageview",$ml_user[name]." (".$ml_user[mail].") vaatas lehte $log",$section);
			}
			else
				$this->_log("pageview","Vaatas lehte $log",$section);
			$awt->stop("syslog_core");
		}

		function do_syslog(&$mar,&$path,$cnt,$section = 0)
		{
			// now build the string to put in syslog
			$log = "";
			for ($i=0; $i < $cnt; $i++)	
			{
				if (($i+1) == $cnt)
					$log.=$mar[$path[$i+1]][name];
				else
					$log.=$mar[$path[$i+1]][name]." / ";
			}
			$this->do_syslog_core($log,$section);
		}

		function check_section($section)
		{
			global $frontpage;
			if ($section == "")
			{
				return $frontpage < 1 ? 1 : $frontpage;
			}

			// sektsioon ei olnud numbriline
			if (!is_number($section)) 
			{
				// vaatame, kas selle nimega aliast on?
				$obj = $this->_get_object_by_alias($section);
				// nope. mingi skriptitatikas? voi cal6
				if (!$obj) 
				{
    	    $this->_log("menuedit","¸ritas accessida objekti id-ga '$section'. Kr‰kkimiskatse?");
					// neat :), kui objekti ei leita, siis saadame 404 koodi
					header ("HTTP/1.1 404 Not Found");
					printf(E_ME_NOT_FOUND);
					exit;
				} 
				else 
				{
					$section = $obj[oid];
				};
      } 
			else 
			{
				// mingi kontroll, et kui sektsioon ei eksisteeri, siis n‰itame esilehte
				if (!(($section > 0) && ($this->get_object($section)))) 
				{
					$this->_log("menuedit","¸ritas accessida olematut objekti id-ga 'section'. Suunati esilehele");
					$section = $globals["frontpage"];
				}
			};

			return $section;
		}

		function add_promo($parent)
		{
			$this->read_template("add_promo.tpl");
			classload("objects");
			$ob = new db_objects;
			$menu = $ob->get_list();
			$menu[$GLOBALS["rootmenu"]] = "Esileht";

			// kysime infot adminnitemplatede kohta
			$q = "SELECT * FROM template WHERE type = 0 ORDER BY id";
			$this->db_query($q);
			$edit_templates = array();
			while($tpl = $this->db_fetch_row()) {
				$edit_templates[$tpl[id]] = $tpl[name];
			};
			// kysime infot lyhikeste templatede kohta
			$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
			$this->db_query($q);
			$short_templates = array();
			while($tpl = $this->db_fetch_row()) {
				$short_templates[$tpl[id]] = $tpl[name];
			};

			$this->vars(array("title" => "", "promo_id" => "", "section" => $ob->option_list($parent,$menu),"right_sel" => "","left_sel" => "CHECKED","parent" => $parent,"tpl_edit" => $this->option_list(0,$edit_templates),"tpl_lead" => $this->option_list(0, $short_templates)));
			return $this->parse();
		}

		function submit_promo($arr)
		{
			$this->quote(&$arr);
			extract($arr);

			if (is_array($section))
			{
				reset($section);
				$a = array();
				while (list(,$v) = each($section))
					$a[$v]=$v;
			}

			$sets = array("section" => $a,"right" => ($right == 1 ? 1 : 0), "scroll" => ($right == 'scroll' ? 1 : 0),"tpl_lead" => $tpl_lead, "tpl_edit" => $tpl_edit);

			if ($id)
			{
				$com = $all_menus ? "all_menus" : serialize($sets);
				$this->upd_object(array("oid" => $id, "name" => $title,"last" => $type,"comment" => $com));
				$this->db_query("UPDATE menu SET tpl_lead = '$tpl_lead', tpl_edit = '$tpl_edit' , link = '$link' WHERE id = $id");
				$this->_log("promo", "Muutis promo kasti $title");
			}
			else
			{
				$id = $this->new_object(array("parent" => $parent,"name" => $title,"class_id" => CL_PROMO,"comment" => $all_menus ? "all_menus" : serialize($sets),"status" => 1,"last" => $type));
				$this->db_query("INSERT INTO menu (id,link,type,is_l3,tpl_lead,tpl_edit) VALUES($id,'$link',".MN_PROMO_BOX.",0,'$tpl_lead','$tpl_edit')");
				$this->_log("promo", "Lisas promo kasti $title");
			}
			return $id;
		}

		function change_promo($id,$newinterface)
		{
			$this->read_template("add_promo.tpl");

			if (!($row = $this->get_object($id)))
				$this->raise_error("promo->gen_change($id): No such box!", true);

			classload("objects");
			$ob = new db_objects;

			$sets = unserialize($row[comment]);

			// kysime infot adminnitemplatede kohta
			$q = "SELECT * FROM template WHERE type = 0 ORDER BY id";
			$this->db_query($q);
			$edit_templates = array();
			while($tpl = $this->db_fetch_row()) {
				$edit_templates[$tpl[id]] = $tpl[name];
			};
			// kysime infot lyhikeste templatede kohta
			$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
			$this->db_query($q);
			$short_templates = array();
			while($tpl = $this->db_fetch_row()) {
				$short_templates[$tpl[id]] = $tpl[name];
			};

			$this->db_query("SELECT * FROM menu WHERE id = $id");
			$rw = $this->db_next();

			$menu = $ob->get_list();
			$menu[0] = "";
			$menu[$GLOBALS["frontpage"]] = "Esileht";
			$this->vars(array("title"			=> $row[name], 
												"promo_id"	=> $id,
												"section"		=> $ob->multiple_option_list($sets[section],$menu),
												"all_menus"	=> checked($row[comment] == "all_menus"),
												"right_sel"	=> ($sets[right] == 1 ? "CHECKED" : ""),
												"left_sel"	=> ($sets[right] != 1 ? "CHECKED" : ""),
												"scroll_sel"	=> checked($sets[scroll]),
												"parent"		=> $row[parent],
												"link"			=> $rw[link],
												"tpl_edit" => $this->option_list($rw[tpl_edit],$edit_templates),
												"tpl_lead" => $this->option_list($rw[tpl_lead],$short_templates),
												"interface" => ($newinterface ? "new" : "")));

			return $this->parse();
		}
		


		// well, mul on vaja kuvada see asi popupi sees, niisiis tegin ma miniversiooni folders.tpl-ist
		// ja lisasin siia uue parameetri
		function gen_folders($period,$popup = 0)
		{
			if ($popup == 1)
			{
				$this->read_template("popup.tpl");
			}
			else
			{
				$this->read_template("folders.tpl");
			};

			$arr = array();
			$mpr = array();
			$this->listacl("objects.status != 0 AND objects.class_id = ".CL_PSEUDO);
			// listib koik menyyd ja paigutab need arraysse
			$this->db_listall("objects.status != 0",true);
			while ($row = $this->db_next())
			{
				if ($this->can("view",$row["oid"]))
				{
					$arr[$row["parent"]][] = $row;
					$mpr[] = $row["parent"];
				}
			}

			$this->listacl("objects.status != 0 AND objects.class_id = ".CL_PROMO);
			$this->db_query("SELECT objects.*, menu.* FROM objects
					LEFT JOIN menu ON menu.id = objects.oid
					WHERE objects.class_id = ".CL_PROMO." AND objects.status != 0");
			while ($row = $this->db_next())
			{
				if ($this->can("view",$row["oid"]))
				{
					$ar = unserialize($row["comment"]);
					$row["sections"] = $ar;
					if (is_array($ar[section]))	
					{
						// put the promo box under all the places it must be shown
						reset($ar);
						while (list($v,$sid) = each($ar["section"]))
						{
							$row["parent"] = $sid;
							$arr[$sid][] = $row;
						}
					}
				}
			}

			// objektipuu
			$tr = $this->rec_tree(&$arr, $GLOBALS["admin_rootmenu2"],$period);

			// kodukataloom
			$tr.=$this->mk_homefolder(&$arr);

			// the whole she-bang
			$arr = array();
			$this->db_listall("objects.status = 2 AND menu.type = ".MN_ADMIN1,true,true);
			while ($row = $this->db_next())
			{
				$arr[$row["parent"]][] = $row;
			}
			$tr.= $this->rec_admin_tree(&$arr, $GLOBALS["amenustart"]);

			$this->vars(array(
				"TREE" => $tr,
				"DOC" => "",
				"root" => $GLOBALS["admin_rootmenu2"],
				"uid" => $GLOBALS["uid"],
				"date" => $this->time2date(time(),2)
			));

			// perioodide tropp.
			if ($GLOBALS["per_oid"])
			{
				classload("periods");
				$dbp = new db_periods($GLOBALS["per_oid"]);
				$act_per_id = $dbp->get_active_period();
				$dbp->clist();
				$pl = array();
				// loeme k6ik perioodid sisse
				while ($row = $dbp->db_next())
				{
					if ($row["id"] == $act_per_id)
					{
						$actrec = $row["rec"];
					};
					$pl[$row["rec"]] = $row;
				}
				// leiame praegune +-3
				$ar = array();
				for ($i=$actrec-6; $i <= ($actrec+6); $i++)
				{
					if ($pl[$i]["id"] == $act_per_id)
					{
						$ar[$pl[$i]["id"]] = $pl[$i]["description"].MN_ACTIVE;
					}
					else
					{
						$ar[$pl[$i]["id"]] = $pl[$i]["description"];
					}
				}
				$ar[0] = MN_PERIODIC;
				$this->vars(array(
					"periods" => str_replace("\n","",$this->picker($period,$ar))
				));
			}
			return $this->parse();
		}

		function rec_homefolder(&$arr,$parent)
		{
			if (!is_array($arr[$parent]))
				return "";

			global $PHP_SELF,$baseurl,$ext;

			reset($arr[$parent]);
			while (list(,$row) = each($arr[$parent]))
			{
				$sub = $this->rec_tree(&$arr,$row[oid],0);
				$this->vars(array(
					"name" => $row[name],
					"id" => $row[oid],
					"parent" => $row[parent],
					"iconurl" => $row[icon_id] ? $baseurl."/icon.".$ext."?id=".$row[icon_id] : "images/ftv2doc.gif",
					"url" => "menuedit_right.".$GLOBALS["ext"]."?parent=".$row[oid]));
				$this->homefolders[$row[oid]] = $row[oid];
				if ($sub == "")
				{
					$ret.=$this->parse("DOC");
				}
				else
				{
					$ret.=$this->parse("TREE").$sub;
				};
			}
			return $ret;
		}

		////
		// !Loob kasutaja kodukataloogi
		function mk_homefolder(&$arr)
		{
			global $udata,$uid,$admin_rootmenu2,$ext,$baseurl;

			// k6igepealt loeme k6ik kodukatalooma all olevad menyyd
			$this->db_query("SELECT menu.*,objects.* FROM menu
						LEFT JOIN objects ON objects.oid = menu.id
						WHERE oid = $udata[home_folder]");
			if (!($hf = $this->db_next()))
			{
				$this->raise_error(sprintf(MN_E_NO_HOME_FOLDER,$uid),true);
			};
			
			// when we create the home folders we write down which ones are shown
			// so we won't show them again under shared folders
			$this->homefolders = array();

			$ret = $this->rec_homefolder($arr, $hf["oid"]);

			$this->vars(array(
				"name" => $hf[name],
				"id" => $hf[oid], 
				"parent" => $admin_rootmenu2,
				"iconurl" => $this->get_icon_url("homefolder",""),
				"url" => "menuedit_right.".$GLOBALS["ext"]."?parent=".$hf["oid"]));
			$hft = $this->parse("TREE");

			// now we need to make a list of all the shared folders of all the users.
			// we do that by simply scanning the array of all folders for visible menus with type MN_HOME_FOLDER_SUB
			// that should work, because if acl is checked, then only folders that are shared to this user will be visible
			// and we exclude the users own home folder menus cause they would be duplicated there otherwise
			$shared_arr = $this->get_shared_arr(&$arr,$this->homefolders);
			reset($shared_arr);
			while (list(,$v) = each($shared_arr))
			{
				$this->vars(array(
					"name"	=> $v["name"],
					"id"	=> $v["oid"],		
					"parent"=> SHARED_FOLDER_ID,
					"iconurl" => $row[icon_id] ? $baseurl."/icon.".$ext."?id=".$row[icon_id] : "images/ftv2doc.gif",
					"url"	=> "menuedit_right.".$GLOBALS["ext"]."?parent=".$v["oid"]));
				$shares.=$this->parse("DOC");
			}
	
			$this->vars(array(
				"name"=> "SHARED FOLDERS",
				"id" => SHARED_FOLDER_ID,		
				"parent" => $hf["oid"],
				"iconurl" => $this->get_icon_url("shared_folders",""),
				"url" => "menuedit_right.".$GLOBALS["ext"]."?parent=".SHARED_FOLDER_ID));
			if ($shares != "")
			{
				$shfs = $this->parse("TREE");
			}
			else
			{
				$shfs = $this->parse("DOC");
			};

			// now we need to make a list of all the groups created by this user
			classload("users_user");
			$dbu = new users_user;
			// mis need on? t‰nased keno loto vıidunumbrid?
			$dbu->listgroups(-1,-1,4);
			$grps_arr = array();
			while ($row = $dbu->db_next())
			{
				$row["oid"] = $row["gid"];
				$grps_arr[$row["parent"]][] = $row;
			}
			$dgid = $dbu->get_gid_by_uid(UID);
			$grptree = $this->rec_tree_grps(&$grps_arr, $dgid);
	
			$this->vars(array(
				"name"		=> "GROUPS",
				"id"			=> "gr_".$dgid,		
				"parent"	=> $hf[oid],
				"iconurl" => $this->get_icon_url("hf_groups",""),
				"url"			=> $this->mk_orb("mk_grpframe",array("parent" => $dgid),"groups")
			));
			if ($grptree != "")
				$grps = $this->parse("TREE");
			else
				$grps = $this->parse("DOC");

			$ret = $hft.$shfs.$shares.$grps.$grptree.$ret;

			return $ret;
		}


		function get_shared_arr(&$arr,$exclude)
		{
			$ret = array();

			reset($arr);
			while (list($parent, $v) = each($arr))
			{
				reset($v);
				while (list(,$row) = each($v))
				{
					if ($row[mtype] == MN_HOME_FOLDER_SUB && !$exclude[$row["oid"]])
					{
						$ret[] = $row;
					}
				}
			}
			return $ret;
		}

		function rec_admin_tree(&$arr,$parent)
		{
			if (!is_array($arr[$parent]))
				return "";

			global $admin_rootmenu2,$ext,$baseurl;

			reset($arr[$parent]);

			while (list(,$row) = each($arr[$parent]))
			{
				if ($row["status"] != 2)
					continue;
				if ($row["admin_feature"] && !$this->prog_acl("view", $row["admin_feature"]) && $GLOBALS["SITE_ID"] == 666)
					continue;

				$sub = $this->rec_admin_tree(&$arr,$row[oid]);

				if ($row["admin_feature"])
				{
					$sub.=$this->get_feature_tree($row["admin_feature"],$row["oid"]);
				}

				$iconurl = $row[icon_id] ? $baseurl."/icon.".$ext."?id=".$row[icon_id] : ($row["admin_feature"] ? $this->get_feature_icon_url($row["admin_feature"]) : "images/ftv2doc.gif");
				$this->vars(array(
					"name"		=> $row["name"],
					"id"			=> ($row["admin_feature"] == 4 ? "gp_" : "").$row["oid"], 
					"parent"	=> ($parent == $GLOBALS["amenustart"] ? $admin_rootmenu2 : $row["parent"]),
					"iconurl" =>  $iconurl,
					"url"			=> $row["link"] != "" ? $row["link"] : ($row["admin_feature"] ? $GLOBALS["programs"][$row["admin_feature"]]["url"] : "blank.html")));

				if ($sub == "")
				{
					$ret.=$this->parse("DOC");
				}
				else
				{
					$ret.=$this->parse("TREE").$sub;
				}
			}
			return $ret;
		}

		function get_feature_tree($feat,$parent)
		{
			switch($feat)
			{
				// grupid
				case 4:
					return $this->mk_grp_tree($parent);
			}
		}

		function mk_grp_tree($parent)
		{
			classload("groups");
			$t = new groups;
			$t->listacl("objects.class_id = ".CL_GROUP." AND objects.status = 2");
			$t->listgroups("parent","asc",0,2);
			$grar = array();
			while ($row = $t->db_next())
			{
				$grar[$row[gid]] = $row;
			}

			reset($grar);
			while (list($gid,$row) = each($grar))
			{
				// we must convert the parent member so that it actually points to the parent OBJECT not the parent group
				$row[parent] = $grar[$row[parent]][oid];

				if ($row[parent] == 0)
				{
					$row[parent] = $parent;
				}
				$grpcache[$row[parent]][] = $row;
			}
			return $this->rec_grp_tree(&$grpcache,$parent);
		}

		function rec_grp_tree(&$arr,$parent)
		{
			if (!is_array($arr[$parent]))
				return "";

			global $PHP_SELF;

			reset($arr[$parent]);
			while (list(,$row) = each($arr[$parent]))
			{
				if (!$this->can("view",$row[oid]) || $row[gid] == $GLOBALS["all_users_grp"])
					continue;

				$sub = $this->rec_grp_tree(&$arr,$row[oid]);
				$this->vars(array(
					"name" => $row[name],"id" => "gp_".$row[oid], "parent" => "gp_".$row[parent],
					"iconurl" => "images/ftv2doc.gif",
					"url"			=> $this->mk_orb("mk_grpframe",array("parent" => $row[gid]),"groups")));
				if ($sub == "")
					$ret.=$this->parse("DOC");
				else
					$ret.=$this->parse("TREE").$sub;
			}
			return $ret;
		}

		function rec_tree(&$arr,$parent,$period)
		{
			if (!is_array($arr[$parent]))
				return "";

			global $PHP_SELF,$baseurl,$ext;

			reset($arr[$parent]);
			while (list(,$row) = each($arr[$parent]))
			{
				if ($row["mtype"] != MN_HOME_FOLDER)
				{
					// tshekime et kas menyyl on submenyysid
					// kui on, siis n2itame alati
					// kui pole, siis tshekime et kas n2idatakse perioodilisi dokke
					// kui n2idatakse ja menyy on perioodiline, siis n2itame menyyd
					// kui pole perioodiline siis ei n2ita
					$sub = $this->rec_tree(&$arr,$row["oid"],$period);
					$show = true;
					if ($sub == "" && $period > 0 && $row["mperiodic"] != 1) 
						$show = false;

					if ($show)
					{
						if ($row["class_id"] == CL_PROMO)
						{
							$url = $this->get_icon_url("promo_box","");
						}
						else
						if ($row[class_id] == CL_BROTHER)
						{
							$url = $this->get_icon_url("brother","");
						}
						else
						{
							$url = $row[icon_id] ? $baseurl."/icon.".$ext."?id=".$row[icon_id] : "images/ftv2doc.gif";
						}
						$this->vars(array(
							"name" => $row["name"],
							"id" => $row["oid"],
							"parent" => $row["parent"],
							"iconurl" => $url,
							"url" => "menuedit_right.".$GLOBALS["ext"]."?parent=".$row[oid]."&period=".$period));
						if ($sub == "")
						{
							$ret.=$this->parse("DOC");
						}
						else
						{
							$ret.=$this->parse("TREE").$sub;
						}
					}
				}
			}
			return $ret;
		}

		function rec_tree_grps(&$arr,$parent)
		{
			if (!is_array($arr[$parent]))
				return "";

			reset($arr[$parent]);
			while (list(,$row) = each($arr[$parent]))
			{
				$sub = $this->rec_tree_grps(&$arr,$row["oid"]);
				$this->vars(array(
					"name" => $row["name"],
					"id" => "gr_".$row["oid"],
					"parent" => "gr_".$row["parent"],
					"iconurl" => "images/ftv2doc.gif",
					"url" => $this->mk_orb("mk_grpframe",array("parent" => $row["gid"]),"groups")));
					//$this->mk_orb("list_grps_user",array("parent" => $row[oid]),"groups"))
				$ret .= ($sub == "") ? $this->parse("DOC") : $this->parse("TREE");
			}
			return $ret;
		}

		function gen_list($parent,$period=0)
		{
			if (is_array($parent))
			{
				extract($parent);
			}
			$period = $GLOBALS["period"];
			return $this->gen_list_menus($parent,$period);
		}

		function gen_list_menus($parent,$period)
		{
			if (is_array($parent))
			{
				extract($parent);
			};	

			if (!$this->can("view", $parent))
				return $this->acl_error("view",$parent);

			// selle voiks ju ka tablegenni peale ajada.
			global $sortby;

			if ($sortby == "")
			{
				$sortby = "jrk";
			};

			global $order,$baseurl;
			if ($order == "")
			{
				$order = "ASC";
			};

			$this->read_template("menus.tpl");

			$this->vars(array("parent" => $parent,
					"addmenu" => $this->mk_orb("new", array("parent" => $parent)),
					"period"	=> $period,
					"import"	=> $this->mk_orb("import", array("parent" => $parent))));


			// vaikimisi on need t¸hjad
			$can_add = "";
			$can_paste = "";
			$can_add_promo = "";

			global $cut_objects;
			// ja l¸hikesed muutujad imevad. so...
			//$ca = $cp = $ap = "";
			if ($this->can("add",$parent))
			{
				$can_add = $this->parse("ADD_CAT");
				if (count($cut_objects) > 0)
				{
					$can_paste = $this->parse("PASTE");
				}
				$can_add_promo = $this->parse("CAN_ADD_PROMO");
			}
			
			$this->vars(array(
				"ADD_CAT"=> $can_add,
				"PASTE" => $can_paste,
				"CAN_ADD_PROMO" => $can_add_promo
			));

			$this->listacl("objects.class_id = ".CL_PSEUDO." AND objects.status != 0 AND objects.parent = $parent");

			if ($GLOBALS["lang_menus"] == 1)
			{
				$ss = "AND (objects.lang_id=".$GLOBALS["lang_id"]." OR menu.type = ".MN_CLIENT.")";
			}


			if ($period > 0)
			{
				$ss.=" AND menu.periodic=1 ";
			};

			$q = "SELECT objects.*,menu.*
				FROM objects LEFT JOIN menu ON menu.id = objects.oid
				WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_PROMO." OR objects.class_id = ".CL_BROTHER.") AND objects.status != 0 AND objects.parent = $parent $ss
				ORDER BY $sortby $order";
			$this->db_query($q);

			$cut = $this->parse("CUT");
			$nocut = $this->parse("NORMAL");

			while ($row = $this->db_next())
			{
				if (!$this->can("view",$row[oid]))
					continue;

				$r_id = ($row["class_id"] == CL_BROTHER ? $row["brother_of"] : $row["oid"]);

				$this->vars(array(
				"is_cut"				=> ($cut_objects[$row["oid"]] ? $cut : $nocut),
				"name"					=> $row[name],
				"menu_id"				=> $row[oid], 
				"menu_order"		=> $row[jrk], 
				"menu_active"		=> ($row[status] == 2 ? "CHECKED" : ""),
				"menu_active2"	=> $row[status],
				"prd1"					=> ($row[periodic] == 1 ? "CHECKED" : ""),
				"prd2"					=> $row[periodic],
				"copied"				=> $row[is_copied] == 1 ? "CHECKED" : "",
				"modified"			=> $this->time2date($row[modified],2),
				"modifiedby"		=> $row[modifiedby],
				"delete"				=> $this->mk_orb("delete", array("parent" => $parent,"id" => $row[oid],"period" => $period)),
				"r_menu_id"			=> $r_id,
				"properties"	=> $this->mk_orb("change", array("parent" => $parent,"id" => $r_id,"period" => $period))));

				$this->vars(array(
					"NFIRST" => $this->can("order",$row[oid]) ? $this->parse("NFIRST") : "",
					"CAN_ACTIVE" => $this->can("active",$row[oid]) ? $this->parse("CAN_ACTIVE") : "",
					"PERIODIC" => $this->can("periodic",$row[oid]) ? $this->parse("PERIODIC") : "",
					"CAN_CHANGE" => $this->can("edit",$row[oid]) ? $this->parse("CAN_CHANGE") : "",
					"CAN_DELETE" => $this->can("delete",$row[oid]) ? $this->parse("CAN_DELETE") : "",
					"CAN_SEL_PERIOD" => $row[periodic] == 1 ? $this->parse("CAN_SEL_PERIOD") : "",
					"CAN_ACL" => $this->can("admin",$row[oid]) ? $this->parse("CAN_ACL") : ""
				));
				$l.=$this->parse("LINE");
			} // eow
			
			$this->vars(array(
				"LINE" => $l,
				"reforb"	=> $this->mk_reforb("submit_order", array("parent" => $parent,"period" => $period,"from_menu" => 1)),
				"order1" => $sortby == "name" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg1"	=> $sortby == "name" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order2"=> $sortby == "jrk" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg2"	=> $sortby == "jrk" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order3"=> $sortby == "modifiedby" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg3"	=> $sortby == "modifiedby" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order4"=> $sortby == "modified" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg4"	=> $sortby == "modified" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order5"=> $sortby == "status" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg5"	=> $sortby == "status" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order6"=> $sortby == "periodic" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg6"	=> $sortby == "periodic" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"yah"	=> $this->mk_path($parent,"",0,false),
				"cut" => $this->mk_orb("cut_menus", array("parent" => $parent)),
				"paste" => $this->mk_orb("paste_menus", array("parent" => $parent))
			));
			return $this->parse();
		}

		// genereerib menyydest vaikese nimekirja templateditori jaoks
		function gen_picker($params)
		{
			extract($params);
			$q = "SELECT objects.*,menu.*
							FROM objects LEFT JOIN menu ON menu.id = objects.oid
							WHERE objects.class_id = ".CL_PSEUDO." AND objects.status != 0 AND objects.parent = $parent
							ORDER BY objects.jrk";
			$this->db_query($q);
			$this->read_template("popup_objects.tpl");
			$l = "";
			while($row = $this->db_next())
			{
				$this->vars(array(
					"icon"				=> $this->get_icon_url($row[class_id],$row[name]),
					"name"				=> $row[name],
					"oid"					=> $row[oid],
					"modifiedby"	=> $row[modifiedby],
					"modified"		=> $this->time2date($row[modified])
				));
				$l .= $this->parse("LINE");
			};
			$object = $this->get_object($parent);
			$this->vars(array("LINE" => $l,
					  "tpl" => $tpl,
						"source" => $object[oid],
					  "objname" => $object[name] . "(" . $object[oid] . ")"));
			return $this->parse();
		}

		function gen_list_filled_forms($parent)
		{
			classload("form_output");
			$this->read_template("filled_forms.tpl");
			
			$this->db_query("SELECT * FROM form_elements WHERE id = $parent");
			$row = $this->db_next();
			$far = unserialize($row[forms]);
			if (!is_array($far))
			{
				$far = array();
			}
			$fop = new form_output;

			$opar = array();
			reset($far);
			while (list(,$fid) = each($far))
			{
				// korjame k6ikide formide v2ljundi stiilid kokku $opar sisse
				$opar[$fid] = $fop->get_op_list(array("id" => $fid));

				$fname = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $fid", "name");
				$this->db_query("SELECT objects.* FROM form_".$fid."_entries LEFT JOIN objects ON objects.oid = form_".$fid."_entries.id WHERE objects.status != 0");
				while ($row = $this->db_next())
				{
					$this->vars(array("filler"		=> $row[createdby], 
														"hits"			=> $row[hits], 
														"form"			=> $fname,
														"modified"	=> $this->time2date($row[modified], 2), "oid" => $row[oid],
														"change"		=> $this->mk_orb("show", array("id" => $fid, "entry_id" => $row[oid]), "form"),
														"form_id"		=> $fid));
					$l.=$this->parse("LINE");
				}
			}
			reset($opar);
			while (list($fid, $ar) = each($opar))
			{
				$this->vars(array("form_id" => $fid));
				$fop = ""; $cnt=0;
				reset($ar);
				while (list($opid, $opname) = each($ar))
				{
					$this->vars(array("op_id" => $opid, "op_name" => $opname, "cnt" => $cnt));
					$fop.=$this->parse("FORM_OP");
					$cnt++;
				}
				$this->vars(array("FORM_OP" => $fop));
				$f.=$this->parse("FORM");
			}
			$this->vars(array("LINE" => $l, "FORM" => $f,
												"reforb"	=> $this->mk_reforb("change", array("id" => 0), "form")));
			return $this->parse();
		}

		function gen_list_objs($parent,$popup = 0)
		{
			if (is_array($parent))
			{
				extract($parent);
			}

			if (!$this->can("view", $parent))
				return $this->acl_error("view",$parent);

			$mtype = $this->db_fetch_field("SELECT type FROM menu WHERE id = $parent", "type");
			// et siis like. Mis tyypi see menu on? kui tegemist vormielementidega, siis...
			if ($mtype == MN_FORM_ELEMENT)
			{
				return $this->gen_list_filled_forms($parent);
			}

			$pobj = $this->get_object($parent);
			// the default document for the menu is in menu[last][$lang_id]
			$lastar = unserialize($pobj[last]);
			$default_doc = $lastar[$GLOBALS["lang_id"]];

			$period = $GLOBALS["period"];

			if ($popup == 1)
			{
				$this->read_template("popup_objects.tpl");
			}
			elseif ($period)
			{
				$this->read_template("pobjects.tpl");
			}
			else
			{
				$this->read_template("objects.tpl");
			}

			global $sortby;
			if ($sortby == "")
				$sortby = "jrk";

			global $order,$baseurl;
			if ($order == "")
				$order = "ASC";

			$types = array();
			global $class_defs;
			reset($class_defs);
			// listime ainult need objektid, mida igale poole lisada saab
			while (list($id,$ar) = each($class_defs))
			{
				if ($ar["can_add"])	
				{
					$types[$id] = $ar["name"];
				}
			}
			$this->vars(array(
				"parent" => $parent,
				"types" => $this->option_list(0,$types),
				"period" => $period
			));
			
			$this->vars(array("ADD_CAT" => $this->can("add",$parent) ? $this->parse("ADD_CAT") : ""));

			global $class_defs;
			$fentries = array();	
			$fstrs = array();
			// form entries among the objects . uuh, they get special treatment!
			// deal with all the form entries among the objecs shown
			$this->db_query("SELECT objects.oid,form_entries.form_id FROM objects LEFT JOIN form_entries ON form_entries.id = objects.oid WHERE objects.parent = $parent AND objects.status != 0 AND objects.class_id =".CL_FORM_ENTRY);
			while ($row = $this->db_next())
			{
				$fentries[$row[oid]] = $row[form_id];
				$fstrs[] = $row[oid];
				$ffound = true;
			}

			if ($ffound)
			{
				classload("form_output");
				$fop = new form_output;
				$opar = array();
				$forms = array();
				$fesstr = join(",",$fstrs);
				$this->db_query("SELECT distinct(form_id) as form_id FROM form_entries WHERE form_entries.id IN ($fesstr)");
				while ($row = $this->db_next())
				{
					$forms[] = $row["form_id"];
					$ops = $fop->get_op_list(array("id" => $row["form_id"]));
					reset($ops);
					$cnt = 0;
					$o = "";
					while (list($opid,$opname) = each($ops))
					{
						$this->vars(array("form_id" => $row["form_id"], "cnt" => $cnt, "op_id" => $opid, "op_name" => $opname));
						$o.=$this->parse("FORM_OP");
						$cnt++;
					}
					$this->vars(array("FORM_OP" => $o));
					$f.=$this->parse("FORM");
				}
				$this->vars(array("FORM" => $f));
				$fshn = $this->parse("FORMS_SHOWN");
			}

			// failide jura et pealkirjale klikkides tulex kohe lahti vastavate settingutega
			$filearr = array();
			$this->db_query("SELECT objects.*, files.newwindow FROM objects LEFT JOIN files ON files.id = objects.oid WHERE objects.status != 0 AND objects.parent = $parent");
			while ($row = $this->db_next())
			{
				$filearr[$row[oid]] = $row;
			}

			if (!$period)
			{
				$this->listacl("(objects.class_id != ".CL_PSEUDO." AND objects.class_id != ".CL_PROMO." AND objects.class_id != ".CL_BROTHER.") AND objects.status != 0 AND objects.parent = $parent AND objects.period = 0 AND objects.lang_id = ".$GLOBALS["lang_id"]);
				$this->db_query("SELECT objects.* FROM objects WHERE (objects.class_id != ".CL_PSEUDO." AND objects.class_id != ".CL_PROMO." AND objects.class_id != ".CL_BROTHER.") AND objects.status != 0 AND objects.parent = $parent AND objects.period = 0 AND objects.lang_id = ".$GLOBALS["lang_id"]." ORDER BY $sortby $order");
			}
			else
			{
				$this->listacl("objects.class_id = ".CL_PERIODIC_SECTION." AND objects.status != 0 AND objects.parent = $parent");
				$this->db_query("SELECT objects.*,documents.* FROM objects LEFT JOIN documents ON documents.docid = objects.oid WHERE objects.class_id = ".CL_PERIODIC_SECTION." AND objects.status != 0 AND objects.parent = $parent AND objects.period = $period ORDER BY $sortby $order");
			}
			$total = 0; $ffound = false;
			global $cut_objects,$copied_objects;
			$cut = $this->parse("CUT");
			$copied = $this->parse("COPIED");
			$nocut = $this->parse("NORMAL");
			while ($row = $this->db_next())
			{
				$total++;
				$this->dequote(&$row[name]);
				$inf = $class_defs[$row[class_id]];

				$target = "";
				$change = $this->mk_orb("change", array("id" => $row[oid], "parent" => $row[parent]), $inf[file]);
				if ($row["class_id"] == CL_FILE)
				{
					if ($filearr[$row["oid"]]["newwindow"] == 1)
					{
						$target = "target=\"_blank\"";
					}
					$change = $GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$row[oid]."/".urlencode($row[name]);
				}
				$this->vars(array(
					"is_cut"			=> $cut_objects[$row["oid"]] ? $cut : ($copied_objects[$row["oid"]] ? $copied : $nocut),
					"target"			=> $target,
					"name"				=> $row[name],
					"class_id"		=> $row[class_id],
					"oid"					=> $row[oid], 
					"order"				=> $row[jrk], 
					"form_id"			=> $row[class_id] == CL_FORM_ENTRY ? $fentries[$row[oid]] : 0,
					"active"			=> ($row[status] == 2 ? "CHECKED" : ""),
					"active2"			=> $row[status],
					"modified"		=> $this->time2date($row[modified],2),
					"esilehel_uudis"    => ($row[esilehel_uudis] > 0 ? "checked" : ""),
					"showlead"    => ($row[showlead] > 0 ? "checked" : ""),
					"text_ok"			=> ($row[text_ok] > 0 ? "checked" : ""),
					"pic_ok"			=> ($row[pic_ok] > 0 ? "checked" : ""),
					"modifiedby"	=> $row[modifiedby],
					"is_forum"    => ($row[is_forum] > 0 ? "checked" : ""),
					"esilehel"    => ($row[esilehel] > 0 ? "checked" : ""),
					"jrk1"				=> $row[jrk1],
					"jrk2"				=> $row[jrk2],
					"icon"				=> $this->get_icon_url($row[class_id],$row[name]),
					"type"				=> $GLOBALS["class_defs"][$row[class_id]][name],
					"change"			=> $change,
					"checked"			=> $default_doc == $row[oid] ? "CHECKED" : "",
					"link"				=> $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."/section=".$row[oid]));
				if (!$def_found)
				{
					$def_found = $default_doc == $row[oid] ? true : false;
				}
				$this->vars(array(
					"NFIRST" => $this->can("order",$row["oid"]) ? $this->parse("NFIRST") : "",
					"CAN_ACTIVE" => $this->can("active",$row["oid"]) ? $this->parse("CAN_ACTIVE") : "",
					"FE"			=> $row[class_id] == CL_FORM_ENTRY ? $this->parse("FE") : $this->parse("NFE"),
				));

				if ($row["class_id"] == CL_FORM_ENTRY)
				{
//					$fentries[] = $row["oid"];
					$ffound = true;
				}
				$l.=$this->parse("LINE");
			}

			$paste = "";
			if (count($cut_objects) > 0 || count($copied_objects) > 0)
			{
				$paste = $this->parse("PASTE");
			}
			$odata = $this->get_object($parent);
			$this->vars(array("LINE" => $l,
					"CUT"	=> "",
					"NORMAL"	=> "", 
					"PASTE"	=> $paste,
					"total"	=> verbalize_number($total),
					"objname" => $odata[name],
					"parent" => $parent,
					"FORMS_SHOWN" => $fshn,
					"reforb" => $this->mk_reforb("submit_order_doc", array("parent" => $parent,"period" => $period)),
					"order1"			=> $sortby == "name" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
					"sortedimg1"	=> $sortby == "name" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
					"order2"			=> $sortby == "jrk" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
					"sortedimg2"	=> $sortby == "jrk" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
					"order3"			=> $sortby == "modifiedby" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
					"sortedimg3"	=> $sortby == "modifiedby" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
					"order4"			=> $sortby == "modified" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
					"sortedimg4"	=> $sortby == "modified" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
					"order5"			=> $sortby == "class_id" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
					"sortedimg5"	=> $sortby == "class_id" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
					"order6"			=> $sortby == "status" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
					"sortedimg6"	=> $sortby == "status" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : ""
			));

			if (!$period && !$popup)
			{	
				$this->vars(array("default" => $this->option_list($default_doc,$this->mk_docsel($parent)),
													"checked" => $def_found ? "" : "CHECKED"));
			}

			return $this->parse();
		}

		function mk_docsel($parent = 0)
		{
			// let the user pick a default document
			// select all documents that are non-periodic and not under this menu and active
			$this->extrarr = array();
			$this->db_query("SELECT objects.oid as oid,documents.title as title ,objects.parent as parent FROM objects LEFT JOIN documents ON documents.docid = objects.oid WHERE parent != $parent AND status = 2 AND periodic = 0 AND site_id = ".$GLOBALS["SITE_ID"]." AND class_id = ".CL_DOCUMENT);
			while ($row = $this->db_next())
			{
				$this->extrarr[$row[parent]][] = array("docid" => $row[oid], "name" => substr($row[title],0,15).".aw");
			}

			$this->menucache = array();
			if ($GLOBALS["lang_menus"] == 1)
				$ss = "AND (objects.lang_id=".$GLOBALS["lang_id"]." OR menu.type= ".MN_CLIENT.")";
			$this->db_query("SELECT objects.oid as oid,
					objects.parent as parent,
					objects.name as name,
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
					WHERE ((objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.") $prsufix) AND objects.status != 0  AND ((objects.site_id = ".$GLOBALS["SITE_ID"].") OR (objects.site_id IS NULL)) $ss
					GROUP BY objects.oid
					ORDER BY objects.parent, jrk");
			// tsykkel yle menyyelementide
			while ($row = $this->db_next()) 
			{
				$sets = unserialize($row[data]);
				$row[name] = substr($row[name],0,15);
				$this->menucache[$row[parent]][] = array("data" => $row);
				if (is_array($sets[section]))
				{
					reset($sets[section]);
					while(list(,$v) = each($sets[section]))
					{
						// topime menyystruktuuri arraysse
						$row[name] = substr($row[name],0,12);
						$this->menucache[$v][] = array("data" => $row);
					}
				}
			}

			$this->docs = array("0" => "");
			// uh. leave out the forst level of objecs
			if (is_array($this->menucache[$GLOBALS["admin_rootmenu2"]]))
			{
				reset($this->menucache[$GLOBALS["admin_rootmenu2"]]);
				while (list(,$ar) = each($this->menucache[$GLOBALS["admin_rootmenu2"]]))
				{
					$this->mk_folders($ar[data][oid],"");
				}
			}
			return $this->docs;
		}

		function get_feature_icon_url($fid)
		{
			if (!$this->feature_icons_loaded)
			{
				$c = new db_config;
				$this->pr_icons = unserialize($c->get_simple_config("program_icons"));
			}
			$i = $this->pr_icons[$fid][url];
			return $i == "" ? "/images/icon_aw.gif" : $i;
		}

		function get_icon_url($clid,$name)
		{
			classload("defs");
			return get_icon_url($clid,$name);
		}

		function command_redirect($arr)
		{
			extract($arr);
			$obj = $this->get_object($oid);

			global $class_defs,$ext;
			$inf = $class_defs[$obj[class_id]];
			if (!is_array($inf))
				$this->raise_error("menuedit->command_redirect($oid): Unknown class $row[class_id]",true);

			$url = $this->mk_orb($subaction, array("id" => $oid,"parent" => $obj[parent],"period" => $period), $inf[file]);
			header("Location: $url");
			die();
		}

		function menuedit_newobj($arr)
		{
			extract($arr);

			if ($type == CL_BROTHER_DOCUMENT)
			{
				// special case, b8888888888
				header("Location: ".$this->mk_orb("add_bro", array("parent" => $parent),"document"));
				die();
			}

			global $class_defs,$ext;
			$inf = $class_defs[$type];
			if (!is_array($inf))
				$this->raise_error("menuedit->command_redirect($oid): Unknown class $row[class_id]",true);
			
			if (!$period)
				$period = 0;
			$url = $this->mk_orb("new", array("parent" => $parent, "period" => $period), $inf[file]);
			header("Location: $url");
			die();		
		}

		function add($arr)
		{
			extract($arr);

			if (!$this->can("add",$parent))
				$this->raise_error("Your access level does not allow you to do this!", true);

			global $ext;
			$this->mk_path($parent,"Lisa");
			$this->read_template("nadd.tpl");
			$q = "SELECT * FROM menu WHERE id = '$parent'";
			$this->db_query($q);
			$par_info = $this->db_fetch_row();
			if ((($parent == 1) || ($parent == 29) && $GLOBALS["SITE_ID"] < 100)) 
			{
				$classlist = $this->option_list(1,array("69" => "Klient"));
				// sektsioonid, mida saab teha kohe kliendi alla
			} 
			else
			if ($par_info[type] == MN_CLIENT) 
			{
				$classlist = $this->option_list(1,array("70" => "Sektsioon",
																							  "71" => "Adminni menyy"));
			} 
			else
			if ($par_info[type] == MN_ADMIN1) 
			{
				$classlist = $this->option_list(1,array("71" => "Adminni menyy","72" => "Dokument"));
			} 
			else
			if ($par_info[type] == MN_HOME_FOLDER || $par_info[type] == MN_HOME_FOLDER_SUB) 
			{
				$classlist = $this->option_list(1,array("75" => "Kataloog"));
			} 
			else 
			{
				$classlist = $this->option_list(1,array("70" => "Sektsioon"));
			};
			$this->vars(array("parent"  => $parent,
								        "name"    => "",
												"alias"   => "",
												"class_select" => $classlist,
												"comment" => "",
												"id"      => 0,
												"reforb" => $this->mk_reforb("submit", array("parent" => $parent))));
			return $this->parse();
		}

		function nsubmit(&$arr)
		{
			$this->quote(&$arr);
			extract($arr);
			// stripime aliasest tyhikud v2lja
			str_replace(" ","",$alias);
			// kui muudame olemasolevat menyyd, siis ........
			if ($id) 
			{
				if (!$this->can("edit",$id))
					$this->raise_error("Your access level does not allow you to do this!", true);

				// k¸sime olemasoleva info men¸¸ kohta
				$q = "SELECT objects.*,menu.* FROM objects
					LEFT JOIN menu ON menu.id = objects.oid
					WHERE oid = '$id'";
				$this->db_query($q);
				$menu = $this->db_next();

				$sar = array(); $oidar = array();
				// leiame koik selle men¸¸ vennad
				$q = "SELECT * FROM objects
					WHERE brother_of = $id AND status != 0 AND class_id = " . CL_BROTHER;
				$this->db_query($q);
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
						$noid = $this->new_object(array("parent" => $oid,"class_id" => CL_BROTHER,"status" => 1,"brother_of" => $id,"name" => $menu[name],"comment" => $menu[comment]));
						$this->db_query("INSERT INTO menu(id,link,type,is_l3,is_copied,periodic,tpl_edit,tpl_view,tpl_lead,active_period,clickable,target,mid,data,hide_noact)	
	values($noid,'$menu[link]','$menu[type]','$menu[is_l3]','$menu[is_copied]','$menu[periodic]','$menu[tpl_edit]','$menu[tpl_view]','$menu[tpl_lead]','$menu[active_period]','$menu[clickable]','$menu[target]','$menu[mid]','$menu[data]','$menu[hide_noact]')");
					}
				}

				$status = ($active == "on") ? 2 : 1;
				$act_stamp = 0;
        $deact_stamp = 0;
        if ($autoactivate == "on") 
				{
          $act_stamp = mktime($activate_at[hour],$activate_at[minute],0,$activate_at[month],
                                $activate_at[day],$activate_at[year]);
          $autoactivate = 1;
        };
        if ($autodeactivate == "on") 
				{
          $deact_stamp = mktime($deactivate_at[hour],$deactivate_at[minute],0,$deactivate_at[month],
                                $deactivate_at[day],$deactivate_at[year]);
          $autodeactivate = 1;
        };

				$charr = array("oid"      => $id,
											"name"     => $name,
											"status"   => $status,
											"autoactivate" => $autoactivate,
											"autodeactivate" => $autodeactivate,
											"activate_at" => $act_stamp,
											"deactivate_at" => $deact_stamp,
											"comment"  => $comment,
											"alias"    => $alias);
				if ($menu[class_id] == CL_PSEUDO)	// if this is a real menu then change it's name and all its brothers names
				{
					$this->db_query("UPDATE objects set name = '$name' WHERE status != 0 AND class_id = ".CL_BROTHER." AND brother_of = $id");
				}
				else
				if ($menu[class_id] == CL_BROTHER)
				{
					// don't change its' name
					unset($charr[name]);
				}

				$sar = array();
				if (is_array($sss))
				{
					reset($sss);
					while (list(,$v) = each($sss))
						$sar[$v] = $v;
				}
				$sss = serialize($sar);
				$par = array();
				if (is_array($pers))
				{
					reset($pers);
					while (list(,$v) = each($pers))
						$par[$v] = $v;
				}
				$pers = serialize($par);
				// pildi uploadimine
				global $img, $img_type;
				$tt = "";
				if ($img != "none" && $img != "")
				{
					classload("images");
					$t = new db_images;
					$im = $t->_upload(array("filename" => $img, "file_type" => $img_type, "oid" => $id));
					$tt = "img_id  = ".$im[id].",";
					$img = $t->get_img_by_id($im[id]);
					$tt.="img_url = '".$img[url]."',";
				}
				if ($number > 0)
				{
					$nn = "number = '$number',";
				}

				global $lang_id;
				// teeme seealso korda.
				if (is_array($seealso))
				{
					$sa = unserialize($menu["seealso"]);	// v6tame vana
					$sa[$lang_id] = array();							// nullime sealt k2esoleva keele 2ra

					reset($seealso);
					while (list(,$sid) = each($seealso))
					{
						$sa[$lang_id][$sid] = $sid;					// ja paneme uued itemid asemele
					}
					$seealso = serialize($sa);
				}
				else
				{
					$sa = unserialize($menu["seealso"]);	// v6tame vana
					$sa[$lang_id] = array();							// nullime sealt k2esoleva keele 2ra
					$seealso = serialize($sa);						
				}

				$this->upd_object($charr);
				$this->_log("menuedit","Muutis men&uuml;&uuml;d $name");
				$q = "UPDATE menu SET 
								tpl_edit = '$tpl_edit',
								tpl_lead = '$tpl_lead',
								tpl_view = '$tpl_view',
								hide_noact = '$hide_noact',
								link = '$link',
								clickable = '$clickable',
								target = '$target',
								ndocs = '$ndocs',
								mid = '$mid',
								is_shop = '$is_shop',
								shop_id = '$shop',
								links = '$links',
								width = '$width',
								$tt
								$nn
								sss = '$sss',
								seealso = '$seealso',
								pers = '$pers',
								admin_feature = '$admin_feature'
								WHERE id = '$id'";
				$this->db_query($q);
			} 
			else 
			{
				if (!$this->can("add",$parent))
				{
					$this->raise_error("Your access level does not allow you to do this!", true);
				}
				// teeme uue menyy
				$id = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_PSEUDO, "comment" => $comment,"status" => 1));
				$this->db_query("INSERT INTO menu (id,link,type,is_l3) VALUES($id,'$link',$class_id,0)");
				if ($class_id == MN_HOME_FOLDER_SUB)
				{
					// keelame teistel selle n2gemise sharetud folderis
					$this->deny_obj_access($id);
				}
				$this->_log("menuedit","Lisas sektsiooni $name");
			}
			return $this->mk_orb("change", array("parent" => $arr[parent],"id" => $id,"period" => $arr[period]));
		}

		function submit_order($arr)
		{
			$obj = $this->get_object($arr[parent]);
			$ar = unserialize($obj[last]);
			if ($arr[default_doc] == -1)
			{
				$ar[$GLOBALS["lang_id"]] = $arr[default_doc2];
			}
			else
			{
				// menu's default document is kept in objects last, because menus don't really need it otherwise.
				$ar[$GLOBALS["lang_id"]] = $arr[default_doc];
			}
			$this->db_query("UPDATE objects SET last = '".serialize($ar)."' WHERE oid = ".$arr[parent]);


			// ord sisaldab vormist sisestatud jarjekorranumbreid
		  // old_ord sisaldab "vanu" jarjekorranumbreid (s.t. neid, mis olid enne)
			$ord = $arr[ord];
		  $old_ord = $arr[old_ord];
			if (is_array($ord)) {
				while(list($oid,$value) = each($ord)) {
					// paringu teeme ainult siis, kui jarjekorranumbrid erinevad
					if ($old_ord[$oid] != $value) {
						$q = "UPDATE objects SET jrk = '$value' WHERE oid = '$oid'";
					  $this->db_query($q);
				  }; // if
		   	}; // while
			}; // is_array
		
			// act sisaldab vormis klikitud "aktiivsus" checkboxe	
			// old_act sisaldab "vanu" aktiivsuseindikaatoreid
			$act = $arr[act];
		  $old_act = $arr[old_act];
			if (is_array($old_act)) {
				while(list($oid,$value) = each($old_act)) {
					$_act = ($act[$oid] == "on") ? 2 : 1;
					// if status changed
					if ($value != $_act) {
						$this->upd_object(array("oid"    => $oid,
																		"status" => $_act));
					}; // if
				}; // while
			}; // if

		// prd sisaldab vormis klikitud "perioodiline" checkboxe
		// old_prd sisaldab "vanu" perioodilisusindikaatoreid
		$prd = $arr[prd];
		$old_prd = $arr[old_prd];
	  if (is_array($old_prd)) {
			while(list($oid,$value) = each($old_prd)) {
				$_prd = ($prd[$oid] == "on") ? 1 : 0;
				// if periodic flag changed
			  if ($value != $_prd) {
					 $q = "UPDATE menu
										SET periodic = '$_prd'
										WHERE id = '$oid'";
					 $this->db_query($q);
					 $q = "UPDATE objects
										SET periodic = '$_prd'
										WHERE oid = '$oid'";
					 $this->db_query($q);
				};
       };
      };

		// clk sisaldab vormis klikitud "klikitav" checkboxe
		// old_clk sisaldab "vanu" klikitavusindikaatoreid
		$clk = $arr[clk];
		$old_clk = $arr[old_clk];
	  if (is_array($old_clk)) 
		{
			while(list($oid,$value) = each($old_clk)) 
			{
				$_clk = ($clk[$oid] == "on") ? 1 : 0;
			  if ($value != $_clk)
					$this->db_query("UPDATE menu SET clickable = '$_clk' WHERE id = '$oid'");
      };
    };

		// new sisaldab vormis klikitud "uues aknas" checkboxe
		// old_new sisaldab "vanu" uueaknaindikaatoreid
		$new = $arr["new"];
		$old_new = $arr[old_new];
	  if (is_array($old_new)) 
		{
			while(list($oid,$value) = each($old_new)) 
			{
				$_new = ($new[$oid] == "on") ? 1 : 0;
			  if ($value != $_new)
					$this->db_query("UPDATE menu SET target = '$_new' WHERE id = '$oid'");
      };
    };

		// mkd sisaldab vormis klikitud "mitteaktiivne kui dokusid pole" checkboxe
		// old_mkd sisaldab "vanu" mitteaktiivne kui dokusid poleindikaatoreid
		$mkd = $arr[mkd];
		$old_mkd = $arr[old_mkd];
	  if (is_array($old_mkd)) 
		{
			while(list($oid,$value) = each($old_mkd)) 
			{
				$_mkd = ($mkd[$oid] == "on") ? 1 : 0;
			  if ($value != $_mkd)
					$this->db_query("UPDATE menu SET hide_noact = '$_mkd' WHERE id = '$oid'");
      };
    };

			// cp arrays on need elemendid, mida kopeerida soovitakse 
			$cp = $arr[cp];
			if (is_array($cp)) {
				// tyhistame koik senised kopeerimised 
				$this->db_query("UPDATE menu SET is_copied = 0");

				// kopeerime margitud elemendid
				while(list($cpk,$cpv) = each($cp)) {
					$q = "UPDATE menu
						SET is_copied = 1
						WHERE id = '$cpk'";
					$this->db_query($q);
				};
			};
			return $this->mk_orb("menu_list", array("parent" => $arr[parent],"period" => $arr[period]));
		}

		function submit_order2($arr)
		{
			$jrk = $arr[jrk];
			if (!is_array($jrk))
			{
				$jrk = $arr[ord];
			}
			$act = $arr[act];
			if (!is_array($act))
				$act = $arr[active];
			$is_forum = $arr[is_forum];
			$showlead = $arr[showlead];
			$text_ok = $arr[text_ok];
			$pic_ok = $arr[pic_ok];
			$esilehel = $arr[esilehel];
			$esilehel_uudis = $arr[esilehel_uudis];
			$jrk1 = $arr[jrk1];
			$jrk2 = $arr[jrk2];

			// saveme default dokumendi.
			if ($arr["default"] == -1)
			{
				$def_doc = $arr["default2"];
			}
			else
			{
				$def_doc = $arr["default"];
			}
			$o = $this->get_object($arr[parent]);
			$od = unserialize($o[last]);
			$od[$GLOBALS["lang_id"]] = $def_doc;
			$os = serialize($od);
			$this->upd_object(array("oid" => $arr[parent], "last" => $os));

			if (!is_array($jrk))
			{
				return;
			}
			while(list($k,$v) = each($jrk)) 
			{
				if ($act[$k] == 1) 
				{
					$part = "status = 2";
				} 
				else 
				{
					$part = "status = 1";
				};
				$q = "UPDATE objects SET jrk = '$v',$part WHERE oid = '$k'";
				$this->db_query($q);
				$dparts = array();
				if ($is_forum[$k] == 1) 
				{
					$dparts[] = " is_forum = 1 ";
				} 
				else 
				{
					$dparts[] = " is_forum = 0 ";
				};
				if ($showlead[$k] == 1) 
				{
					$dparts[] = " showlead = 1 ";
				} 
				else 
				{
					$dparts[] = " showlead = 0 ";
				};
				if ($esilehel[$k] == 1) 
				{
					$dparts[] = " esilehel = 1 ";
				} 
				else 
				{
					$dparts[] = " esilehel = 0 ";
				};
				if ($text_ok[$k] == 1) 
				{
					$dparts[] = " text_ok = 1 ";
				} 
				else 
				{
					$dparts[] = " text_ok = 0 ";
				};
				if ($pic_ok[$k] == 1) 
				{
					$dparts[] = " pic_ok = 1 ";
				} 
				else 
				{
					$dparts[] = " pic_ok = 0 ";
				};
				if ($esilehel_uudis[$k] == 1) 
				{
					$dparts[] = " esilehel_uudis = 1 ";
				} 
				else 
				{
					$dparts[] = " esilehel_uudis = 0 ";
				};
				$dparts[] = " jrk1 = '".$jrk1[$k]."' ";
				$dparts[] = " jrk2 = '".$jrk2[$k]."' ";
				if (sizeof($dparts) > 0) 
				{
					$q = "UPDATE documents SET " . join(",",$dparts) . "WHERE docid = '$k'";
					$this->db_query($q);
				};
			};
			return $this->mk_orb("obj_list", array("parent" => $arr[parent],"period" => $arr[period]));
		}

		function ndelete($arr)
		{
			extract($arr);
			if (!$this->can("delete",$id))
				$this->raise_error("Your access level does not allow you to do this!", true);

			$this->rd($id);
			$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $id","name");
			$this->_log("menuedit","Kustutas men&uuml;&uuml; $name");
			header("Location: ".$this->mk_orb("menu_list", array("parent" => $arr[parent])));
		}

		function change($arr)
		{
			extract($arr);
			global $period;
			$this->mk_path($id, "Muuda");
			if (!$this->can("edit",$id))
				$this->raise_error("Your access level does not allow you to do this!", true);

			global $basedir;
      global $ext;
      include("$basedir/vcl/date_edit.$ext");
      $d_edit = new date_edit("x");
      $d_edit->configure(array("day" => 1,"month" => 2,"year" => 3,"hour" => 4,"minute" => 5));
			$this->db_query("SELECT objects.*,objects.alias AS alias,
										   menu.type as type, menu.link as link,
											 menu.tpl_edit as tpl_edit,
											 menu.tpl_view as tpl_view,
											 menu.tpl_lead as tpl_lead,
											 menu.hide_noact as hide_noact,
											 menu.clickable as clickable,
											 menu.target as target,
											 menu.ndocs as ndocs,
											 menu.mid as mid,
											 menu.img_id as img_id,
											 menu.links as links,
											 menu.sss as sss,
											 menu.number as number,
											 menu.icon_id as icon_id,
											 menu.admin_feature as admin_feature,
											 menu.is_shop as is_shop,
											 menu.shop_id as shop_id,
											 menu.seealso as seealso,
											 menu.width as width
											 FROM objects 
											 LEFT JOIN menu ON menu.id = objects.oid
											 WHERE oid = $id");
			if (!($row = $this->db_next()))
				$this->raise_error("menuedit->gen_change_html($id): No such menu!", true);

			if ($row[class_id] == CL_PROMO)
				return $this->change_promo($id,true);

			$this->read_template("nchange.tpl");
			
			// kysime infot adminnitemplatede kohta
			$q = "SELECT * FROM template WHERE type = 0 ORDER BY id";
			$this->db_query($q);
			$edit_templates = array();
			while($tpl = $this->db_fetch_row()) {
				$edit_templates[$tpl[id]] = $tpl[name];
			};
			// kysime infot lyhikeste templatede kohta
			$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
			$this->db_query($q);
			$short_templates = array();
			while($tpl = $this->db_fetch_row()) {
				$short_templates[$tpl[id]] = $tpl[name];
			};
			// kysime infot pikkade templatede kohta
			$q = "SELECT * FROM template WHERE type = 2 ORDER BY id";
			$this->db_query($q);
			$long_templates = array();
			while($tpl = $this->db_fetch_row()) {
				$long_templates[$tpl[id]] = $tpl[name];
			};

			$sar = array();
			$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER);
			while ($arow = $this->db_next())
				$sar[$arow[parent]] = $arow[parent];

			classload("objects");
			$ob = new db_objects;
		 $activate_at = ($row[activate_at]) ? $row[activate_at] : "+24h";
      $deactivate_at = ($row[deactivate_at]) ? $row[deactivate_at] : "+48h";
			$this->dequote(&$row[name]);

			if ($row[ndocs] > 0)
				$il = $this->parse("IS_LAST");

			// kui see on adminni menyy, siis kuvame kasutajale featuuride listi, 
			// mille hulgast ta siis valida saab, et mis selle menyy alt avaneb. 
			if ($row[type] == MN_ADMIN1)
			{
				$this->vars(array("admin_feature" => $this->picker($row[admin_feature],$this->get_feature_sel())));
				$af = $this->parse("ADMIN_FEATURE");
			}
			if ($row[img_id])
			{
				classload("images");
				$t = new db_images;
				$img = $t->get_img_by_id($row[img_id]);
				$this->vars(array("image" => "<img src='".$img[url]."'>"));
			}
			classload("shop");
			$sh = new shop;
			$shs = $sh->get_list();

			$sa = unserialize($row["seealso"]);
			$oblist = $ob->get_list();
			$this->vars(array("parent"			=> $row["parent"], 
												"seealso"			=> $this->multiple_option_list($sa[$GLOBALS["lang_id"]],$oblist),
												"ADMIN_FEATURE"	=> $af,
												"name"				=> $row["name"], 
												"number"			=> $row["number"],
												"comment"			=> $row["comment"], 
												"links"				=> checked($row["links"]), 
												"id"					=> $id,
												"active"	    => ($row["status"] == 2) ? "checked" : "",
												"clickable"	    => ($row["clickable"] == 1) ? "checked" : "",
												"hide_noact"   => ($row["hide_noact"] == 1) ? "checked" : "",
												"alias"				=> $row["alias"],
												"created"			=> $this->time2date($row["created"],2),
												"target"		=> ($row["target"]) ? "checked" : "",
												"autoactivate" => ($row["autoactivate"]) ? "checked" : "",
                        "autodeactivate" => ($row["autodeactivate"]) ? "checked" : "",
                        "activate_at" => $d_edit->gen_edit_form("activate_at",$activate_at),
                        "deactivate_at" => $d_edit->gen_edit_form("deactivate_at",$deactivate_at),
												"createdby"		=> $row["createdby"],
												"modified"		=> $this->time2date($row["modified"],2),
												"modifiedby"	=> $row["modifiedby"],
												"tpl_edit" => $this->option_list($row["tpl_edit"],$edit_templates),
												"tpl_view" => $this->option_list($row["tpl_view"],$long_templates),
												"tpl_lead" => $this->option_list($row["tpl_lead"],$short_templates),
												"section"			=> $this->multiple_option_list($sets["section"],$oblist),
												"sss"					=> $this->multiple_option_list(unserialize($row["sss"]),$oblist),
												"link"				=> $row["link"],
												"sep_checked"	=> ($row["type"] == 4 ? "CHECKED" : ""),
												"mid"	=> ($row["mid"] == 1 ? "CHECKED" : ""),
												"doc_checked"	=> ($row["type"] == 6 ? "CHECKED" : ""),
												"sections"		=> $this->multiple_option_list($sar,$ob->get_list(false,true)),
												"real_id"			=> $row["brother_of"],
												"reforb"			=> $this->mk_reforb("submit",array("id" => $id, "parent" => $parent,"period" => $period)),
												"ndocs"				=> $row["ndocs"],
												"ex_menus"		=> $this->multiple_option_list($ob->get_list(false,false,$id),$ob->get_list(false,false,$id)),
												"icon"				=> $row["icon_id"] ? 
												"<img src='".$GLOBALS["baseurl"]."/icon.".$GLOBALS["ext"]."?id=".$row["icon_id"]."'>" : "",
												"IS_LAST"			=> $il,
												"shop"				=> $this->picker($row["shop_id"],$shs),
												"is_shop"			=> checked($row["is_shop"]),
												"width" => $row["width"]
												));

			$this->vars(array(
				"CAN_BROTHER" => $row["class_id"] == CL_PSEUDO ? $this->parse("CAN_BROTHER") : "",
				"IS_BROTHER" => $row["class_id"] == CL_PSEUDO ? "" : $this->parse("IS_BROTHER"),
				"IS_SHOP"	=> ($row["is_shop"] ? $this->parse("IS_SHOP") : "")
			));

			return $this->parse();
		}

		function get_feature_sel()
		{
			// @desc: tagastab array adminni featuuridest, mida sobib ette s88ta aw_template->picker funxioonile
			global $programs;
			$ret = array();
			reset($programs);
			while (list($id,$v) = each($programs))
			{
				$ret[$id] = $v[name];
			}

			return $ret;
		}

		function create_homes()
		{
			$this->db_query("SELECT * FROM users");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				$id = $this->new_object(array("parent" => 1, "name" => $row[uid], "class_id" => 1, "comment" => $row[uid]." kodukataloog"));
				$this->db_query("INSERT INTO menu (id,type) VALUES($id,".MN_HOME_FOLDER.")");
				$this->db_query("UPDATE users SET home_folder = $id WHERE uid = '$row[uid]'");
				echo "created for $row[uid] , id = $id<br>";
				flush();
				$this->restore_handle();
			}
		}


		////
		// !override the mk_path on core.aw , cause in menuedit mk_path is used in the upper frame, not in the objects frame
		// !and thus must go to a different place when clicked.
		function mk_path($oid,$text = "",$period = 0,$set = true)
		{
			global $ext;

			$ch = $this->get_object_chain($oid,false,$GLOBALS["admin_rootmenu2"]);
			reset($ch);
			while (list(,$row) = each($ch))
			{
				$path="<a target='list' href='menuedit_right.$ext?parent=".$row[oid]."&period=".$period."'>".$row[name]."</a> / ".$path;
			}

			if ($set)
			{
				$GLOBALS["site_title"] = $path.$text;
			}
			return $path;
		}

		////
		// !sets the icon ($icon_id) for menu $id
		function set_menu_icon($id,$icon_id)
		{
			$this->db_query("UPDATE menu SET icon_id = $icon_id WHERE id = $id");
		}

		////
		// !generates link collection content instead of document for menu
		function do_link_collection($parent)
		{
			$this->read_template("link_collection.tpl");

			global $ext, $baseurl;
			
			// ehitame asukoha linkit
			$p = $parent;
			$yah = array();
			while ($p)
			{
				$this->_push($this->mar[$p],"yaha");
				if ($this->mar[$p][links])
				{
					$p = 0;
				}	
				$p = $this->mar[$p][parent];
			}

			while ($v = $this->_pop("yaha"))
			{
				$url = $baseurl."/index.".$ext."/section=".$v[oid];
				$this->vars(array("url" => $url, "name" => $v[name], "oid" => $v[oid]));
				if ($y == "")
				{
					$y =$this->parse("YAH_BEGIN");
				}
				else
				{
					$y.=$this->parse("YAH");
				}
			}
			$this->vars(array("YAH" => $y, "YAH_BEGIN" => ""));

			if (is_array($this->mpr[$parent]))
			{
				$cnt = - (LINKC_MENUSPERLINE-1);
				reset($this->mpr[$parent]);
				while (list(,$ar) = each($this->mpr[$parent]))
				{
					$url = $baseurl."/index.".$ext."/section=".$ar[oid];
					$this->vars(array("url" => $url, "name" => $ar[name], "oid"=>$ar[oid]));
					$c.= $this->parse("SECTIONS_COL");

					if (($cnt % LINKC_MENUSPERLINE) == 0)
					{
						$this->vars(array("SECTIONS_COL" => $c));
						$l.=$this->parse("SECTIONS_LINE");
						$c = "";
					}
					$cnt++;
				}
				$this->vars(array("SECTIONS_COL" => $c));
				$l.=$this->parse("SECTIONS_LINE");
				$this->vars(array("SECTIONS_LINE" => $l));
			}

			$c = ""; $l = "";
			$cnt = - (LINKC_LINKSPERLINE-1);
			$this->db_query("SELECT extlinks.*,objects.comment AS comment FROM objects LEFT JOIN extlinks ON extlinks.id = objects.oid WHERE objects.class_id = ".CL_EXTLINK." AND objects.status = 2 AND objects.parent = $parent");
			while ($row = $this->db_next())
			{
				$target = "";
				if ($row[newwindow])
				{
					$target = "target='_new'";
				}
				$this->vars(array("url" => $row[url], "name" => $row[name], "text" => $row[comment], "target" => $target));
				$c.=$this->parse("LINK_COL");

				if (($cnt % LINKC_LINKSPERLINE) == 0)
				{
					$this->vars(array("LINK_COL" => $c));
					$l.=$this->parse("LINK_LINE");
					$c = "";
				}
				$cnt++;
			}
			$this->vars(array("LINK_COL" => $c));
			$l.=$this->parse("LINK_LINE");
			$this->vars(array("LINK_LINE" => $l));
			$con =  $this->parse();
			return $con;
		}

		function req_draw_menu($parent,$name,&$path,$ignore_path)
		{
			global $baseurl, $ext;
			$this->sub_merge = 1;
			$this->level++;

			$cnt = 0;

			if (!is_array($this->mpr[$parent]))
			{
				$this->level--;
				return 0;
			}
			
			$in_path = in_array($row[parent],$path);
			$parent_tpl = $this->is_parent_tpl($mn2, $mn);
			if (!(($in_path||$this->level == 1)||$parent_tpl||$ignore_path))
			{
				// don't show unless the menu is selected (in the path)
				// or the next level subtemplates are nested in this one
				// which signifies that we sould show them anyway
				// ignore all these if the meny is a 1st level menu 
				$this->level--;
				return 0;
			}
			
			// make the subtemplate names for this and the next level
			$mn = "MENU_".$name."_L".$this->level."_ITEM";
			$mn2 = "MENU_".$name."_L".($this->level+1)."_ITEM";
			$this->vars(array($mn => ""));

			// go over the menus on this level
			reset($this->mpr[$parent]);
			while (list(,$row) = each($this->mpr[$parent]))
			{
				// only show content menus
				if ($row["mtype"] != MN_CONTENT)
				{
					continue;
				}
				if ($row["hide_noact"])
				{
					// also go through the menus below this one to find out if there are any documents beneath those
					// since then we must show the menu
					if (!$this->has_sub_dox($row["oid"]))
					{
						continue;
					}
				}

				// check if this menu is THE selected menu
				if ($this->sel_section == $row["oid"] && $this->is_template("MENU_".$name."_SEEALSO_ITEM"))
				{
					$this->do_seealso_items($row,$name);
				}

				$this->vars(array($mn2 => "", $mn2."_N" => ""));
				$ap = "";
				if ($this->is_template($mn."_N"))
				{
					if (!in_array($row["parent"],$path))
					{
						continue;
					}
					else
					{
						$mn=$mn."_N";
					}
				}

				$n = $this->req_draw_menu($row["oid"], $name, &$path,$parent_tpl);

				if ($cnt == 0 && $this->is_template($mn."_BEGIN"))
				{
					$ap.="_BEGIN";	// first one of this level menus
				};
				if (in_array($row["oid"],$path) && $row["clickable"] == 1)
				{
					$ap.="_SEL";		// a selected menu
				};

				if ($row["clickable"] != 1)
				{
					$ap.="_SEP";		// non-clickable menu
				};

				if ($this->is_template($mn.$ap."_NOSUB") && $n == 0)
				{
					$ap.="_NOSUB";	// menu without subitems
				};
				// if no correct combination exists, use the default
				if (!$this->is_template($mn.$ap))
				{
					$ap = "";	
				};

				if (is_array($this->mpr[$row["oid"]]))
				{
					$hs = $this->parse("HAS_SUBITEMS_".$name);
				}
				else
				{
					$hs = $this->parse("NO_SUBITEMS_".$name);
				}
				$this->vars(array("HAS_SUBITEMS_".$name => $hs));

				if ($row["brother_of"])
				{
					$row = $this->mar[$row["brother_of"]];
				}

				if ($row["link"] != "")
				{
					$link = $row["link"];
				}
				else
				{
					$link = $baseurl."/";
					$link .= ($row["alias"] != "") ? $row["alias"] : "index." . $ext . "/section=" . $row["oid"];
				}

				$target = ($row["target"] == 1) ? sprintf("target='%s'","_new") : "";
				$this->vars(array("text" 		=> $row["name"],
													"link" 		=> $link,
													"section"	=> $row["oid"],
													"target" 	=> $target,
													"image"		=> ($row["img_url"] != "" ? "<img src='".$row["img_url"]."' border='0'>" : "")));

				$l.=$this->parse($mn.$ap);
				$this->vars(array($mn.$ap => ""));
				$cnt++;
			}
			$this->vars(array($mn => $l));
			$this->level--;
			return $cnt;
		}

	////
	// !draws MENU_$name_SEEALSO_ITEM 's for the menu given in $row
	function do_seealso_items($row,$name)
	{
		global $ext,$baseurl,$lang_id;
		$sa = unserialize($row["seealso"]);
		if (is_array($sa[$lang_id]))
		{
			reset($sa[$lang_id]);
			while (list($said,) = each($sa[$lang_id]))
			{
				$samenu = $this->mar[$said];
				if (!is_array($samenu))
				{
					// the menu was not loaded. load it.
					$this->save_handle();
					$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE objects.oid = $said");
					$samenu = $this->db_next();
					$this->mar[$said] = $samenu;
					$this->restore_handle();
				}

				if ($samenu[link] != "")
				{
					$link = $samenu[link];
				}
				else
				{
					$link = $baseurl."/";
					$link .= ($samenu[alias] != "") ? $samenu[alias] : "index." . $ext . "/section=" . $samenu[oid];
				}

				$this->vars(array(
					"target" => $samenu["target"] ? "target=\"blank\"" : "",
					"link" => $link,
					"text" => $samenu["name"]
				));
				$this->parse("MENU_".$name."_SEEALSO_ITEM");
			}
		}
	}

	////
	// !exports menu $id and all below it
	function export_menus($arr)
	{
		extract($arr);

		classload("icons");
		$i = new icons();
		$this->get_feature_icon_url(0);	// warm up the cache

		$menus = array("0" => $id);

		// ok. now we gotta figure out which menus the user wants to export. 
		// he can select just the lower menus and assume that the upper onec come along with them.
		// biyaatch 

		// kay. so we cache the menus
		$this->db_listall();
		while ($row = $this->db_next())
		{
			$this->mar[$row["oid"]] = $row;
		}

		if (!is_array($ex_menus))
		{
			return;
		}
		// this keeps all the menus that will be selected
		$sels = array();	
		// now we start going through the selected menus
		reset($ex_menus);
		while (list(,$eid) = each($ex_menus))
		{
			// and for each we run to the top of the hierarchy and also select all menus 
			// so we will gather a list of all the menus we need. groovy.
			
			$sels[$eid] = $eid;
			while ($eid != $id && $eid > 0)
			{
				$sels[$eid] = $eid;
				$eid = $this->mar[$eid][parent];
			}
		}

		// so now we have a complete list of menus to fetch. 
		// so fetchemall
		reset($sels);
		while (list(,$eid) = each($sels))
		{
			$row = $this->mar[$eid];
			if ($allactive)
			{
				$row["status"] = 2;
			}
			$this->append_exp_arr($row,&$menus,$ex_icons,$i);
		}

		/// now all menus are in the array with all the other stuff, 
		// so now export it.
		header("Content-type: aw/menuexport");
		echo serialize($menus);
		die();
	}

	function append_exp_arr($db, $menus,$ex_icons,&$i)
	{
		$ret = array();
		$ret["db"] = $db;
		if ($ex_icons)
		{
			$icon = -1;
			// admin_feature icon takes precedence over menu's icon. so include just that.
			if ($db["admin_feature"] > 0)
			{
				$icon = $this->pr_icons[$db["admin_feature"]]["id"];
				if ($icon)
				{
					$icon = $i->get($icon);
				}
			}
			else
			if ($db["icon_id"] > 0)
			{
				$icon = $i->get($db["icon_id"]);
			}
			$ret["icon"] = $icon;
		}
		$menus[$db["parent"]][] = $ret;
	}

	////
	// !shows menus importing form
	function import($arr)
	{
		extract($arr);
		$this->mk_path($parent,"Impordi men&uuml;&uuml;sid");
		$this->read_template("import.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit_import", array("parent" => $parent))));
		return $this->parse();
	}

	////
	// !does the actual menu importing bit
	function submit_import($arr)
	{
		extract($arr);
		global $fail;

		$f = fopen($fail, "r");
		$d = fread($f,filesize($fail));
		fclose($f);

		$menus = unserialize($d);
		$i_p = $menus[0];

		$this->req_import_menus($i_p, &$menus, $parent);
		header("Location: ".$GLOBALS["baseurl"]."/automatweb/".$this->mk_orb("menu_list", array("parent" => $parent)));
		return $this->mk_orb("menu_list", array("parent" => $parent));
	}

	function req_import_menus($i_p, &$menus, $parent)
	{
		if (!is_array($menus[$i_p]))
		{
			return;
		}
		$mt = $this->db_fetch_field("SELECT type FROM menu WHERE id= $parent","type");
		classload("icons");
		$i = new icons;
		reset($menus[$i_p]);
		while (list(,$v) = each($menus[$i_p]))
		{
			$db = $v["db"];
	
			$icon_id = 0;
			if (is_array($v["icon"]))
			{
				$icon_id = $i->get_icon_by_file($v["icon"]["file"]);
				if (!$icon_id)
				{
					// not in db, must add
					$icon_id = $i->add_array($v["icon"]);
				}
			}
			if ($mt == MN_HOME_FOLDER || $mt == MN_HOME_FOLDER_SUB)
			{
				$db["mtype"] = MN_HOME_FOLDER_SUB;	// so you can share them later on.
			}
			$id = $this->new_object(array("parent" => $parent,"name" => $db["name"], "class_id" => $db["class_id"], "status" => $db[status], "comment" => $db["comment"], "jrk" => $db["jrk"], "visible" => $db["visible"], "alias" => $db["alias"], "periodic" => $db["periodic"]));
			$this->db_query("INSERT INTO menu 
						 (id,link,type,is_l3,periodic,clickable,target,mid,hide_noact,ndocs,admin_feature,number,icon_id,links) 
			VALUES ($id,'".$db["link"]."','".$db["mtype"]."','".$db["is_l3"]."','".$db["periodic"]."','".$db["clickable"]."','".$db["target"]."','".$db["mid"]."','".$db["hide_noact"]."','".$db["ndocs"]."','".$db["admin_feature"]."','".$db["number"]."',$icon_id,'".$db["links"]."')");

			// tegime vanema menyy 2ra, teeme lapsed ka.
			$this->req_import_menus($db["oid"],$menus,$id);
		}
	}

	////
	// !cuts the selected objects
	function cut($arr)
	{
		extract($arr);

		$GLOBALS["cut_objects"] = array();
		if ($oid)
		{
			$GLOBALS["cut_objects"][$oid] = $oid;
		}

		if (is_array($sel))
		{
			reset($sel);
			while (list($oid,) = each($sel))
			{
				$GLOBALS["cut_objects"][$oid] = $oid;
			}
		}

		if ($from_menu)
		{
			return $this->mk_orb("menu_list", array("parent" => $parent, "period" => $period));
		}
		else
		{
			return $this->mk_orb("obj_list", array("parent" => $parent, "period" => $period));
		}
	}

	////
	// !copies the selected objects
	function copy($arr)
	{
		extract($arr);

		$GLOBALS["copied_objects"] = array();

		if ($oid)
		{
			$r = $this->serialize(array("oid" => $oid));
			if ($r != false)
			{
				$GLOBALS["copied_objects"][$oid] = $r;
			}
		}

		if (is_array($sel))
		{
			reset($sel);
			while (list($oid,) = each($sel))
			{
				$r = $this->serialize(array("oid" => $oid));
				if ($r != false)
				{
					$GLOBALS["copied_objects"][$oid] = $r;
				}
			}
		}
		
		return $this->mk_orb("obj_list", array("parent" => $parent, "period" => $period));
	}

	////
	// !pastes the cut objects 
	function paste($arr)
	{
		extract($arr);

		global $cut_objects;
		if (is_array($cut_objects))
		{
			reset($cut_objects);
			while (list(,$oid) = each($cut_objects))
			{
				$this->upd_object(array("oid" => $oid, "parent" => $parent));
			}
		}
		$GLOBALS["cut_objects"] = array();

		global $copied_objects;
		if (is_array($copied_objects))
		{
			reset($copied_objects);
			while (list($oid,$str) = each($copied_objects))
			{
				$this->unserialize(array("str" => $str, "parent" => $parent, "period" => $period));
			}
		}
		$GLOBALS["copied_objects"] = array();
		if ($from_menu)
		{
			return $this->mk_orb("menu_list", array("parent" => $parent, "period" => $period));
		}
		else
		{
			return $this->mk_orb("obj_list", array("parent" => $parent, "period" => $period));
		}
	}

	function o_delete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			reset($sel);
			while (list($ooid,) = each($sel))
			{
				$this->delete_object($ooid);
				$this->delete_aliases_of($ooid);
			}
		}
		if ($oid)
		{
			$this->delete_object($oid);
		}
		return $this->mk_orb("obj_list", array("parent" => $parent, "period" => $period));
	}

	function make_menu_caches()
	{
		global $awt;
		$awt->start("make_menu_caches()");
		// make one big array for the whole menu
		$this->mar = array();
		// see laheb ja loeb kokku, mitu last mingil sektsioonil on
		// salvestatakse $this->subs array sisse, key on objekti oidiman

		$awt->start("make_menu_caches()::qs");
		$this->db_prep_listall("objects.status = 2");
		$this->db_listall_lite("objects.status = 2");
		$awt->stop("make_menu_caches()::qs");

		$awt->start("make_menu_caches()::array_insert");
		while ($row = $this->db_next())
		{
			$awt->count("make_menu_caches()");
			$awt->start("make_menu_caches()::array_insert_insert");
			$this->mpr[$row["parent"]][] = $row;
			$this->mar[$row["oid"]] = $row;
			$awt->stop("make_menu_caches()::array_insert_insert");
		}
		$awt->stop("make_menu_caches()::array_insert");
		$awt->stop("make_menu_caches()");
	}

	function is_link_collection($section)
	{
		global $awt;
		$awt->start("is_link_collection()");
		$p = $section; 
		$links = false;
		$cnt = 0;
		while ($p && ($cnt < 20))
		{
			$cnt++;
			if ($this->mar[$p][links])
			{
				$p = 0;
				$links = true;
			}	
			$p = $this->mar[$p][parent];
		}
		$awt->stop("is_link_collection()");
		return $links;
	}

	function is_shop($section)
	{
		global $awt;
		$awt->start("is_shop()");
		$p = $section; 
		$links = false;
		$cnt = 0;
		while ($p && ($cnt < 20))
		{
			$cnt++;
			if ($this->mar[$p]["is_shop"])
			{
				$sh_id = $this->mar[$p]["shop_id"];
				$p = 0;
				$links = true;
			}	
			$p = $this->mar[$p]["parent"];
		}
		$awt->stop("is_shop()");
		if (!$links)
		{
			return false;
		}
		else
		{
			$this->left_pane = false;
			$this->right_pane = false;
			return $sh_id;
		}
	}

	function show_periodic_documents($section,$obj,$format)
	{
		global $awt;
		$awt->start("show_periodic_documents()");

		$d = new db_documents();
		// if $section is a periodic document then emulate the current period for it
		if ($obj["class_id"] == CL_PERIODIC_SECTION)
		{
			$activeperiod = $obj["period"];
		}
		else
		{
			$activeperiod = $GLOBALS["act_per_id"];
		}
		$d->set_period($activeperiod);
		$d->list_docs($section, $activeperiod,2);
		$cont = "";
		if ($d->num_rows > 1) 
		{
			$template = $this->get_lead_template($section);
			while($row = $d->db_next()) 
			{
				$d->save_handle();
				$d->set_period($row["period"]);
				$cont .= $d->gen_preview(array(
					"docid" => $row[docid],
					"tpl" => $template,
					"tpls" => $tpls,
					"leadonly" => 1,
					"section" => $section,
					"strip_img" => $strip_img,
					"doc"	=> $row));
				$d->restore_handle();
			}; // while
		} // if
		// on 1 doku
		else 
		{
			$row = $d->db_next();
			$template = $this->get_long_template($section);
			// siin teeme koigepealt kindlaks, et kas ehk sellise id-ga
			// dokumenti juba olemas ei ole
			$docid = $row[docid];
			if (($doc = $d->fetch($docid))) 
			{
				$section = $docid;
			};
			$cont = $d->gen_preview(array(
						"docid" => $section,
						"boldlead" => 1,
						"tpl" => $template,
						"tpls" => $tpls,
						"doc" => $doc));
			$this->vars(array("docid" => $section));
			$PRINTANDSEND = $this->parse("PRINTANDSEND");
		};
		$awt->stop("show_periodic_documents()");
		return $cont;
	}

	function show_documents($section,$docid)
	{
		global $awt;
		$awt->stop("show_documents()");
		classload("documents");
		$d = new db_documents();
		// Vaatame, kas selle sektsiooni jaoks on "default" dokument
		if ($docid < 1) 
		{
			$docid = $this->get_default_document($section);
		};
		$template = $this->get_long_template($section);
		if (is_array($docid)) 
		{
			$template = $this->get_lead_template($section);
			$template2 = file_exists($GLOBALS["tpldir"]."/automatweb/documents/".$template."2") ? $template."2" : $template;
			$ct = ""; 
			$dk=1;
			// I hate this. docid on dokumendi id,
			// ja seda ei peaks arrayna kasutama
			reset($docid);
			while (list(,$did) = each($docid)) 
			{
				$ct.=$d->gen_preview(array(
					"docid" => $did,
					"tpl" => ($dk & 1 ? $template : $template2),
					"leadonly" => 1,
					"section" => $section,
					"strip_img" => $strip_img,
					"tpls" => $tpls
				));
				$dk++;
			} // while
		} 
		else 
		{
			if ($docid)
			{
				$ct = $d->gen_preview(array(
					"docid" => $docid,
					"section" => $section,
					"notitleimg" => 0,
					"tpl" => $template
				));
				if ($d->no_left_pane)
				{
					$this->left_pane = false;
				}
				if ($d->no_right_pane)
				{
					$this->right_pane = false;
				}
				$PRINTANDSEND = $this->parse("PRINTANDSEND");
				$this->vars(array("docid" => $docid));
				$this->active_doc = $docid;
			}
		}
		$awt->stop("show_documents()");
		return $ct;
	}

	function get_path($section,$obj)
	{
		global $awt;
		$awt->start("get_path()");
		// now find the path through the menu
		$path = array();
		if ($obj["class_id"] == CL_PERIODIC_SECTION || $obj["class_id"] == CL_DOCUMENT)
		{
			$sec = $obj["parent"];
			$section = $obj["parent"];
		}
		else
		{
			$sec = $section; 
		}
		$cnt = 0;
		// kontrollime seda ka, et kas see "sec" yldse olemas on,
		// vastasel korral satume loputusse tsyklisse
		while ($sec && ($sec != 1)) 
		{
			$this->_push($sec);
			$sec = $this->mar[$sec]["parent"];
			$cnt++;
		}
		// now the path is in the correct order on the "root" stack

		for ($i=0; $i < $cnt; $i++) 
		{
			$path[$i+1] = $this->_pop();
		};
		// and now in the $path array
		$awt->stop("get_path()");
		return $path;
	}

	function make_yah($path)
	{
		global $awt;
		$awt->start("make_yah");
		// now build "you are here" links from the path
		$ya = "";  
		$show = false;
		$cnt = count($path);
		for ($i=0; $i < $cnt; $i++)	
		{
			if ($show)
			{
				$this->vars(array("link" => "/index.".$GLOBALS["ext"]."/section=".$this->mar[$path[$i+1]][oid],"text" => $this->mar[$path[$i+1]][name], "ysection" => $this->mar[$path[$i+1]][oid]));

				$ya.=$this->parse("YAH_LINK");
			}
			// don't show things that are before $frontpage
			if ($this->mar[$path[$i]][oid] == $GLOBALS["frontpage"])
			{
				$show = true;
			}
		}
		$awt->stop("make_yah");
		return $ya;
	}

	function make_promo_boxes($section)
	{
		global $awt;
		$awt->start("make_promo_boxes($section)");
		// see jupp siin teeb promokastid
		$doc = new db_documents;
		$template = $this->get_lead_template($section);
		$q = "SELECT objects.*, template.filename as filename,menu.link as link
				FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				LEFT JOIN template ON template.id = menu.tpl_lead
				WHERE objects.status = 2 AND objects.class_id = 22 AND (objects.site_id = ".$GLOBALS["SITE_ID"]." OR objects.site_id is null)
				ORDER by jrk";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$awt->count("make_promo_boxes($section)::rows");
			$doc->doc_count = 0;
			$ar = unserialize($row["comment"]);
			if ($ar["section"][$section] || ($row["comment"] == "all_menus" && $row["site_id"] == $GLOBALS["SITE_ID"]))
			{
				$awt->count("make_promo_boxes($section)::vis_rows");
				$awt->start("make_promo_boxes($section)::show_rows");
				// visible. so show it
				$this->save_handle();
				// get list of documents in this promo box
				$pr_c = "";
				$docid = $this->get_default_document($row["oid"],true);
				if (is_array($docid))
				{
					reset($docid);
					while (list(,$d) = each($docid))
					{
						$awt->stop("make_promo_boxes($section)::show_rows");
						$pr_c.=$doc->gen_preview(array("docid" => $d, "tpl" => $row[filename],"leadonly" => -1, "section" => $section, 	"strip_img" => false,"showlead" => 1, "boldlead" => 0,"no_strip_lead" => 1));
						$awt->start("make_promo_boxes($section)::show_rows");
					}
				}
				else
				{
					$awt->stop("make_promo_boxes($section)::show_rows");
					$pr_c.=$doc->gen_preview(array("docid" => $docid, "tpl" => $row[filename],"leadonly" => -1, "section" => $section, 	"strip_img" => false,"showlead" => 1, "boldlead" => 0,"no_strip_lead" => 1));
					$awt->start("make_promo_boxes($section)::show_rows");
				}

				$this->vars(array("title" => $row[name], "content" => $pr_c,"url" => $row[link]));
				$ap = "";
				if ($row["link"] != "")
				{
					$ap = "_LINKED";
				}
				if ($ar["scroll"] == 1)
				{
					if ($this->is_template("SCROLL_PROMO".$ap))
					{
						$scroll_promo .= $this->parse("SCROLL_PROMO".$ap);
					}
					else
					{
						$scroll_promo .= $this->parse("SCROLL_PROMO");
					}
				}
				else
				if ($ar["right"] == 1)
				{
					if ($this->is_template("RIGHT_PROMO".$ap))
					{
						$right_promo .= $this->parse("RIGHT_PROMO".$ap);
					}
					else
					{
						$right_promo .= $this->parse("RIGHT_PROMO");
					}
				}
				else
				{
					if ($this->is_template("LEFT_PROMO".$ap))
					{
						$left_promo .= $this->parse("LEFT_PROMO".$ap);
					}
					else
					{
						$left_promo .= $this->parse("LEFT_PROMO");
					}
				}
				$awt->stop("make_promo_boxes($section)::show_rows");
				$this->restore_handle();
			}
		};
		$this->vars(array(
			"LEFT_PROMO" => $left_promo,
			"RIGHT_PROMO" => $right_promo,
			"SCROLL_PROMO" => $scroll_promo,
		));
		$awt->stop("make_promo_boxes($section)");
	}

	function make_poll()
	{
		global $awt;
		$awt->start("make_poll()");
		if ($this->is_template("POLL"))
		{
			classload("poll");
			$t = new poll;
			$this->vars(array("POLL" => $t->gen_user_html()));
		}
		$awt->stop("make_poll()");
	}

	function make_search()
	{
		global $awt;
		$awt->start("make_search()");
		if ($this->is_template("SEARCH_SEL"))
		{
			classload("search_conf");
			$t = new search_conf;
			$this->vars(array("search_sel" => $this->option_list(0,$t->get_search_list())));
			$this->vars(array("SEARCH_SEL" => $this->parse("SEARCH_SEL")));
		}
		$awt->stop("make_search()");
	}

	function make_nadalanagu()
	{
		global $awt;
		$awt->start("make_nadalanagu()");
		if ($this->is_template("NADALA_NAGU"))
		{
			classload("nagu");
			$t = new nagu;
			$nagu = $t->get_active($GLOBALS["per_oid"]);
			$tmp = $nagu["content"];
			if ($nagu["num"] > 0 && is_array($tmp))
			{
				reset($tmp);
				uasort($tmp,__nagu_sort);
				reset($tmp);
				$max = $nagu["num"];
				// kui 3, siis 3
				$max = 3;
				for ($i=0; $i < $max; $i++)
				{	
					list(,$v) = each($tmp);
					$this->vars(array("pos" => $i+1, "name" => $v["eesnimi"]." ".$v["kesknimi"]." ".$v["perenimi"]));
					$l.=$this->parse("NAME");
					if ($i == 0)
					{
						$wurl = $v["imgurl"];
					}
				}
				if ($wurl == "")
				{
					$wurl = $GLOBALS["baseurl"]."/images/transa.gif";
				}
				$this->vars(array("NAME" => $l,"winnerurl" => $wurl));
				$nn = $this->parse("NADALA_NAGU");
				$this->vars(array("NADALA_NAGU" => $nn));
			}
		}
		$awt->stop("make_nadalanagu()");
	}

	function do_rdf($section,$obj,$format,$docid)
	{
		classload("rdf");
		global $baseurl,$ext;
		$rdf = new rdf(array(
			"about" => "$baseurl/index.$ext/section=$section/format=rss",
			"title" => $obj["name"],
			"description" => $obj["description"],
			"link" => "$baseurl/index.$ext/section=$section",
		));

		// read all the menus and other necessary info into arrays from the database
		$this->make_menu_caches();

		// laeme dokumentide klassi
		classload("periods","documents");
		
		// leiame, kas on tegemist perioodilise rubriigiga
		$periodic = $this->is_periodic($section);

		// loome sisu
		$d = new db_documents();
		if ($periodic) 
		{
			// if $section is a periodic document then emulate the current period for it
			if ($obj["class_id"] == CL_PERIODIC_SECTION)
			{
				$activeperiod = $obj["period"];
			}
			else
			{
				$activeperiod = $GLOBALS["act_per_id"];
			}
			$d->set_period($activeperiod);
			$d->list_docs($section, $activeperiod,2);
			$cont = "";
			if ($d->num_rows > 1) 
			{
				while($row = $d->db_next()) 
				{
					$rdf->add_item($row);
				};
			} 
			// on 1 doku
			else 
			{
				$q = "SELECT docid,title,lead,author FROM documents WHERE docid = '$section'";
				$this->db_query($q);
				$row = $this->db_next();
				$rdf->add_item($row);
			} 
		}
		else 
		{
			// sektsioon pole perioodiline
			if ($docid < 1) 
			{
				$docid = $this->get_default_document($section);
			};

			if (is_array($docid)) 
			{
				// I hate this. docid on dokumendi id,
				// ja seda ei peaks arrayna kasutama
				reset($docid);
				while (list(,$did) = each($docid)) 
				{
					$q = "SELECT * FROM documents WHERE docid = '$did'";
					$this->db_query($q);
					$row = $this->db_next();
					$rdf->add_item($row);
				} 
			} 
			else 
			{
				$q = "SELECT * FROM documents WHERE docid = '$docid'";
				$this->db_query($q);
				$row = $this->db_next();
				$rdf->add_item($row);
			}
		}

		header("Content-Type: text/xml");
		print $rdf->gen_output();
		// I know, I know, it's damn ugly
		die();
	}

	function make_banners()
	{
		global $banner_defs,$banner_server,$ext,$awt,$uid;

		if (!is_array($banner_defs))
		{
			return;
		}

		$awt->start("make_banners()");
		reset($banner_defs);
		while (list($name,$gid) = each($banner_defs))
		{
			$htmlf = $banner_server."/banner.$ext?gid=$gid&html=1";
			if ($uid != "")
			{
				$htmlf.="&aw_uid=".$uid;
			}
			$f = fopen($htmlf,"r");
			$fc = fread($f,100000);
			fclose($f);

			$fc = str_replace("[ss]","[ss".$gid."]",$fc);

			$this->vars(array("banner_".$name => $fc));
		}
		$awt->stop("make_banners()");
	}

	////
	// !generates the ui for the shop
	function do_shop($section,$shop_id)
	{
		classload("shop");
		$sh = new shop;
		$shop = $sh->get($shop_id);
		return $sh->show(array("parent" => $shop["root_menu"],"id" => $shop_id));
	}
}
?>
