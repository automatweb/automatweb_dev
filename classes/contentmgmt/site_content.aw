<?php
classload("menuedit");
class site_content extends menuedit
{
	function site_content()
	{
		$this->init(array(
			"tpldir" => "automatweb/menuedit",
		));
	}

	////
	// !da thing. draws the site
	// params: section, text, docid, strip_img, template, format, vars, no_left_pane, no_right_pane
	// niisiis. vars array peaks sisaldama mingeid pre-parsed html tkke,
	// mis vivad tulla ntx kusagilt orbi klassi seest vtm.
	// array keydeks peaksid olema variabled template sees, mis siis asendatakse
	// oma v�rtustega
	function _gen_site_html($params)
	{
		extract($params);	
		$template = isset($template) && $template != "" ? $template : "main.tpl";
		$docid = isset($docid) ? $docid : 0;

		// until we can have class-static variables, this actually SETS current text content
		classload("layout/active_page_data");
		active_page_data::get_text_content($text);

		// impordime taimeriklassi

		$this->vars(array(
			"lang_code" => aw_global_get("LC"),
		));

		$obj = $this->get_object($section);

		classload("image");
		// this checks whether the requested object belongs to any
		// class that can be shown directly - if so, check_object
		// puts the result into $this->replacement. dat kind of ugly -- duke
		$this->check_object($obj);

		if (not($text))
		{
			$text = $this->replacement;
		};

		$meta = $obj["meta"];
		
		/// Vend?
		if ($obj["class_id"] == CL_BROTHER_DOCUMENT)
		{
			$section=$obj["parent"];
			$docid=$obj["brother_of"];
		}
		
		// check whether access to that menu is denied by ACL and if so
		// redirect the user 
		if (!aw_ini_get("menuedit.no_view_acl_checks"))
		{
			if (not($this->can("view", $section)))
			{
				$this->no_access_redir($section);
			}
		}

		classload("image");
		$imc = get_instance("image");
		$pers = get_instance("period");
		$_t = aw_global_get("act_period");
		$imdata = $imc->get_image_by_id($_t["data"]["image"]);
		$this->vars(array(
			"per_string" => $_t["name"],
			"act_per_id" => $_t["id"],
			"def_per_id" => $pers->get_active_period(),
			"per_img_url" => image::check_url($imdata["url"]),
			"per_img_tag" => image::make_img_tag(image::check_url($imdata["url"])),
			"per_img_link" => ($_t["data"]["image_link"] != "" ? $_t["data"]["image_link"] : aw_ini_get("baseurl"))
		));

		// by default show both panes.
		$this->left_pane = (isset($no_left_pane) && $no_left_pane == true) ? false : true;
		$this->right_pane = (isset($no_right_pane) && $no_right_pane == true) ? false : true;

		// read all the menus and other necessary info into arrays from the database
		dbg::p("active language for menus is ".aw_global_get("lang_id")."<br>");

		$this->make_menu_caches();

		// leiame, kas on tegemist perioodilise rubriigiga
		$periodic = $this->is_periodic($section);

	
		if ($obj["class_id"] != CL_PSEUDO)
		{
			$this->sel_section = $obj["parent"];
		}
		else
		if ($obj["class_id"] == CL_BROTHER_DOCUMENT)
		{
			$bo = $this->get_object($obj["brother_of"]);
			$bo_meta = $this->get_object_metadata(array(
				"metadata" => $bo["metadata"]
			));
			if ($bo_meta["show_real_pos"])
			{
				$section = $bo["parent"];
				$this->sel_section = $bo["parent"];
			}
			else
			{
				$this->sel_section = $obj["parent"];
			}
		}
		else
		if ($obj["class_id"]  == CL_BROTHER)
		{
			$this->sel_section = $obj["brother_of"];
		}
		else
		{
			$this->sel_section = $section;
		}

		$this->vars(array(
			"sel_menu_id" => $sel_menu_id,
			"se_lang_id" => aw_global_get("lang_id")
		));

		// build the menu chain for the requested section, this simplifies at least 
		// users_only check and finding the correct template_set, probably also
		// a few other functions
		$this->build_menu_chain($this->sel_section);
		if ($this->properties["show_layout"])
		{
			return $this->do_show_layout($this->properties["show_layout"]);
		}
		
		// if the remote user is not logged in and the users_only property is set,
		// redirect the him to the correspondending error page
		if ( (aw_global_get("uid") == "") && ($this->properties["users_only"]) )
		{
			$this->users_only_redir();
		};
		// if the tpl_dir property is set, reinitialize the template class
		if (!isset($tpldir))
		{
			$tpldir = $this->properties["tpl_dir"];
		}

		// hook for site specific gen_site_html initialization
		// feel free to add other stuff here, but make sure this
		// stays _before_ the tpl_init below
		$si = __get_site_instance();
		if (is_object($si) && method_exists($si,"init_gen_site_html"))
		{
			$si->init_gen_site_html(array(
				"tpldir" => &$tpldir,
			));
		};

		if ($tpldir)
		{
			$this->tpl_init(sprintf("../%s/automatweb/menuedit",$tpldir));
		};
		
		$this->read_template($template);

		$d = get_instance("document");
		$this->doc = get_instance("document");
		
		
		// so, if the current object is not a menu,
		// just pretend that the parent is. Hm, I think that's wrong
		if (!is_array($this->mar[$sel_menu_id]))
		{
			$seobj = $this->get_object($sel_menu_id);
			$sel_menu_id = $seobj["parent"];
		}

		$sel_menu_id = $this->sel_section;
		
		// nii nyt leiame aktiivse kommentaari - kui aktiivsel menyyl on tyhi, siis parenti oma jne
		$this->dequote($this->properties["comment"]);
		$this->vars(array(
			"sel_menu_comment" => $this->properties["comment"],
		));

		if (!$this->mar[$sel_menu_id]["left_pane"])
		{
			$this->left_pane = false;
		}

		if (!$this->mar[$sel_menu_id]["right_pane"])
		{
			$this->right_pane = false;
		}

		$this->do_sub_callbacks($sub_callbacks);

		if ($obj["class_id"] == CL_PSEUDO && (($sh_id = $this->is_shop($section)) > 0) && $text == "")
		{
			// tshekime et kas 2kki on selle menyy all pood. kui on, siis joonistame selle.
			$doc_c = $this->show_documents($section,$docid);
			$shp_c = $this->do_shop($section,$sh_id);
			$this->vars(array("doc_content" => ($doc_c.$shp_c)));
			$this->read_template($template);
		}
		else
		if ($periodic && $text == "") 
		{
			$docc = $this->show_periodic_documents($section,$obj);
			if ($this->mar[$sel_menu_id]["no_menus"] == 1 || ($GLOBALS["print"]) )
			{
				// this tells site index.aw not to show the index.tpl
				// shrug, erki wants it that way
				//$this->no_index_template = true;
				return $docc;
			}

			$this->vars(array("doc_content" => $docc));
		} 
		else 
		if ($text == "")
		{
			// sektsioon pole perioodiline
			//$docc = $this->show_documents($section,$docid,$template);
			$docc = $this->show_documents($section,$docid,$xtemplate);
			if ( ($this->mar[$sel_menu_id]["no_menus"] == 1) || ($GLOBALS["print"]) )
			{
				$this->no_index_template = true;
				return $docc;
			}
			$this->vars(array("doc_content" => $docc));
			if ( (is_array($this->blocks)) && (sizeof($this->blocks) > 0) )
			{
				while(list(,$blockdata) = each($this->blocks))
				{
					$this->vars(array(
						"title" => $blockdata["title"],
						"content" => $blockdata["content"],
					));
					$vars[$blockdata["template"]] .= $this->parse($blockdata["template"]);
				};
			};
		}
		else
		{
			$this->vars(array("doc_content" => $text));
		}

		// here we must find the menu image, if it is not specified for this menu,
		//then use the parent's and so on.
		$this->do_menu_images($sel_menu_id);

		// import language constants
		lc_site_load("menuedit",$this);

		// get array with path of objects in it
		$path = $this->get_path($section,$obj);
		$this->path = $path;

		// you are here links		
		$yah = $this->make_yah($this->path);
		if ($this->site_title == "")
		{
			$this->site_title = strip_tags($this->title_yah);
		}
		if ($yah != "")
		{
			$this->vars(array("HAS_YAH" => $this->parse("HAS_YAH")));
		}
		$this->vars(array("YAH_LINK" => $yah));

		// language selecta
//		if ($this->is_template("LANG"))
//		{
			$this->make_langs();
//		}

		// write info about viewing to the syslog
		// yukk
		$mn = get_instance("menuedit");
		$mn->path = $this->path;
		$mn->menu_chain = $this->menu_chain;
		$mn->do_syslog($section);

		// right, now build the menus

		// this will contain all the menus parsed from templates
		$outputs = array();	

		$ce = false;

		$section_subitems = sizeof($this->mpr[$section]);

		$this->section = $section;

		$cd = "";
		$cd2 = "";
		$menu_defs = aw_ini_get("menuedit.menu_defs");
		$this->menu_defaults = aw_ini_get("menuedit.menu_defaults");
		if (aw_ini_get("menuedit.lang_defs"))
		{
			$menu_defs = $menu_defs[aw_global_get("lang_id")];
			$this->menu_defaults = $this->menu_defaults[aw_global_get("lang_id")];
		}
		$frontpage = $this->cfg["frontpage"];

		if (isset($menu_defs) && is_array($menu_defs))
		{
			$nx = "";
			$this->level = 0;
			reset($menu_defs);
			while (list($id,$name) = each($menu_defs))
			{
				$nx = $name;
				dbg::p("drawing $id,$name<br>");

				// SIC! check whether login menus are defined and
				// if so, overwrite the one defined in aw.ini
				if ($name == "LOGGED")
				{
					$cfg = get_instance("config");
					$_id = $cfg->get_login_menus();
					if ($_id)
					{
						$id = $_id;
					};
				};

				// so we can get the root menu of the menu area from the menu area's name quickly
				$this->menu_defs_name_map[$name] = $id;

				$this->req_draw_menu($id,$name,&$path,false);
				if ($this->sel_section == $frontpage)
				{
					$this->do_seealso_items($this->mar[$id],$name);
				}
				$blockname = sprintf("%s_L%s",$name,1);
				$blocktemplate_subs = sprintf("MENU_%s_L%s_HAS_SUBITEMS",$name,1);
				$blocktemplate_nosubs = sprintf("MENU_%s_L%s_NO_SUBITEMS",$name,1);

				if (isset($this->templates[$blocktemplate_subs]) && $this->templates[$blocktemplate_subs])
				{
					if ( $this->subitems[$blockname] > 0) 
					{
						$this->vars(array($blocktemplate_subs => $this->parse($blocktemplate_subs)));
					}
					else
					{
						$this->vars(array($blocktemplate_nosubs = $this->parse($blocktemplate_nosubs)));
					};
				};
			}
		}

		$this->do_sub_callbacks($sub_callbacks, true);

		
		$this->make_promo_boxes($obj["class_id"] == CL_BROTHER ? $obj["brother_of"] : $this->sel_section);

		if ($this->is_template("POLL"))
		{
			$this->make_poll();
		};

		$this->make_search();
		
		$this->make_banners();
	
		$this->vars(array(
			"ss" => gen_uniq_id(),		// bannerite jaox
			"ss2" => gen_uniq_id(),
			"ss3" => gen_uniq_id(),
			"link" => "",
			"section"	=> $section,
			"uid" => aw_global_get("uid"),
			"date" => $this->time2date(time(), 2),
			"date2" => $this->time2date(time(), 8),
			"date3" => date("d").". ".get_lc_month(date("n")).". ".date("Y"),
			"IS_FRONTPAGE" => ($section == $frontpage ? $this->parse("IS_FRONTPAGE") : ""),
			"IS_FRONTPAGE2" => ($section == $frontpage ? $this->parse("IS_FRONTPAGE2") : ""),
			"IS_NOT_FRONTPAGE" => ($section != $frontpage ? $this->parse("IS_NOT_FRONTPAGE") : ""),
			"IS_NOT_FRONTPAGE2" => ($section != $frontpage ? $this->parse("IS_NOT_FRONTPAGE2") : ""),
		));

		if ($_t["data"]["image"]["url"] != "")
		{
			$this->vars(array(
				"HAS_PERIOD_IMAGE" => $this->parse("HAS_PERIOD_IMAGE")
			));
		}
		
		// you can pass along values for variables from index.aw and places like that using
		// $vars array - it's pretty neat actually. - terryf
		if (is_array($vars))
		{
			$this->vars($vars);
		}

		$cd = "";
		if (aw_global_get("uid") == "")
		{
			$login = $this->parse("login");
			$this->vars(array(
				"login" => $login, 
				"login2" => $this->parse("login2"), 
				"login3" => $this->parse("login3"), 
				"logged" => "",
				"logged2" => "",
				"logged3" => "",
				"site_title" => strip_tags($this->site_title),
			));
		}
		else
		{
			$t = get_instance("users");
			$udata = $this->get_user();
			$jfar = $t->get_jf_list(isset($udata["join_grp"]) ? $udata["join_grp"] : "");
			$jfs = "";
			reset($jfar);
			while (list($fid,$name) = each($jfar))
			{
				$this->vars(array(
					"form_id" => $fid, 
					"form_name" => $name
				));
				$jfs.=$this->parse("JOIN_FORM");
			}
			$this->vars(array("JOIN_FORM" => $jfs));

			if ($this->can("edit",$section) && $this->active_doc)
			{
				$cd = $this->parse("CHANGEDOCUMENT");
				$cd2 = $this->parse("CHANGEDOCUMENT2");
			};
			$this->vars(array(
				"CHANGEDOCUMENT" => $cd,
				"CHANGEDOCUMENT2" => $cd2,
			));

			$cd = "";
			if ($this->can("add",$section))
			{
				$cd = $this->parse("ADDDOCUMENT");
			};
			$this->vars(array(
				"ADDDOCUMENT" => $cd,
			));

			// check menuedit access
			if ($this->prog_acl("view", PRG_MENUEDIT))
			{
				// so if this is the only document shown and the user has edit right
				// to it, parse and show the CHANGEDOCUMENT sub
				$this->vars(array("MENUEDIT_ACCESS" => $this->parse("MENUEDIT_ACCESS")));
			}
			else
			{
				$this->vars(array("MENUEDIT_ACCESS" => ""));
			}


			// god dammit, this sucks. aga ma ei oska seda kuidagi teisiti lahendada
			// konkreetselt sonnenjetis on logged LEFT_PANE sees,
			// www.kirjastus.ee-s on LEFT_PANE logged-i sees.
			$lp = "";
			$rp = "";
			if ($this->is_template("logged.asdasdas"))
			{
				print "logged is inside LEFT_PANE<br>";

			}
		
			$this->vars(array(
				"logged" => $this->parse("logged"), 
				"logged1" => $this->parse("logged1"),
				"logged2" => $this->parse("logged2"),
				"logged3" => $this->parse("logged3"),
				"login" => "",
				"site_title" => strip_tags($this->site_title),
			));
		};

		$cd_n = "";
		if ($this->active_doc)
		{
			$cd_n = $this->parse("CHANGEDOCUMENT_NOLOGIN");
		}
		$this->vars(array(
			"CHANGEDOCUMENT_NOLOGIN" => $cd_n
		));
		
		if ($this->left_pane)
		{
			$lp = $this->parse("LEFT_PANE");
		}
		else
		{
			$lp = $this->parse("NO_LEFT_PANE");
		}
		if ($this->right_pane)
		{
			$rp = $this->parse("RIGHT_PANE");
		}
		else
		{
			$rp = $this->parse("NO_RIGHT_PANE");
		}
		
		$this->vars(array(
			"LEFT_PANE" => $lp, 
			"RIGHT_PANE" => $rp,
			"NO_LEFT_PANE" => "",
			"NO_RIGHT_PANE" => ""
		));

		// check if logged is outside LEFT_PANE and if it is, then parse logged again if we are logged in
		if ($this->is_parent_tpl("LEFT_PANE", "logged") && aw_global_get("uid") != "")
		{
			$this->vars(array(
				"logged" => $this->parse("logged")
			));
		}
		
		if (is_array($vars))
		{
			$vars["LEFT_PROMO"] .= $this->vars["LEFT_PROMO"];
			$this->vars($vars);
		}

		// sucks.
		if ($this->mar[$section]["parent"] == 34506 || $this->mar[$this->mar[$section]["parent"]]["parent"] == 34506 || $section == $frontpage)
		{
			$this->vars(array(
				"IS_AWCOM_FRONTPAGE" => $this->parse("IS_AWCOM_FRONTPAGE"),
				"MOSTIMP" => '<span class="pealkiri2">'.$this->mar["34506"]["name"].'</span>'
			));
		}

		if ($this->mar[$section]["parent"] == 39790 || $this->mar[$this->mar[$section]["parent"]]["parent"] == 39790 || ($section == $GLOBALS["frontpage"] && $GLOBALS["lang_id"] == 4))
		{
			$this->vars(array(
				"IS_AWCOM_FRONTPAGE_ENG" => $this->parse("IS_AWCOM_FRONTPAGE_ENG"),
				"MOSTIMP" => '<span class="pealkiri2">'.$this->mar["39790"]["name"].'</span>'
			));
		}

		$eng = "";
		$neng = "";
		if ($GLOBALS["lang_id"] == 4)
		{
			$eng = $this->parse("ENG");
		}
		else
		{
			$neng = $this->parse("NOT_ENG");
		}
		$this->vars(array(
			"ENG" => $eng,
			"NOT_ENG" => $neng
		));

		// end of suckage


		if ($section == $frontpage)
		{
			$this->vars(array("IS_FRONTPAGE" => $this->parse("IS_FRONTPAGE")));
		}

		$popups = $this->build_popups();

		if (is_array($this->metaref) && (sizeof($this->metaref) > 0))
		{
			$this->set_object_metadata(array(
				"oid" => $section,
				"key" => "metaref",
				"value" => $this->metaref,
			));
		};

		$retval = $this->parse();
		return $this->parse() . $popups;
	}
	
	function req_draw_menu($parent,$name,&$path,$ignore_path)
	{
		// FIXME: don't really need to do that every time
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		$menu_check_acl = aw_ini_get("menuedit.menu_check_acl") ? aw_ini_get("menuedit.menu_check_acl") : false;
		$this->sub_merge = 1;

		if (isset($this->mar[$parent]) &&  
			isset($this->mar[$parent]["meta"]) && 
			is_array($this->mar[$parent]["meta"]) && 
			isset($this->mar[$parent]["meta"]["show_periods"]) && 
			isset($this->mar[$parent]["meta"]["show_period_count"])
			)
		{
			if ($this->mar[$parent]["meta"]["show_periods"] && $this->mar[$parent]["meta"]["show_period_count"])
			{
				// now we replace the list of menus with list of periods
				// oh, god, I hate myself
				$this->mpr[$parent] = array();
				$per = get_instance("period");
				$perlist = new aw_array($per->list_periods(0));
				$counter = 0;
				foreach($perlist->get() as $key => $val)
				{
					$counter++;
					$this->mpr[$parent][] = array(
						"name" => $val["name"],
						"comment" => $val["comment"],
						"type" => MN_CONTENT,
						"link" => $this->mk_my_orb("show",array("period" => $val["id"]),"contents"),
					);
					if ($counter >= $this->mar[$parent]["meta"]["show_period_count"])
					{
						break;
					};
				};
			};
		}


		$this->level++;

		// needed to make creating links containing hiearchical aliases work
		if (not(is_array($this->menu_aliases)))
		{
			$this->menu_aliases = array();
		}

		// I don't care about the first level menus
		if ($this->level > 0)
		{
			if (isset($this->mar[$parent]["alias"]) && $this->mar[$parent]["alias"])
			{
				array_push($this->menu_aliases,$this->mar[$parent]["alias"]);
			}
			else
			{
				array_push($this->menu_aliases,"n/a");
			};
		};

		$cnt = 0;

		if (!isset($this->mpr[$parent]) || !is_array($this->mpr[$parent]))
		{
			$this->level--;
			array_pop($this->menu_aliases);
			return 0;
		}
		
		$check_acl = false;

		if (is_array($menu_check_acl))
		{
			if (in_array($parent,$menu_check_acl))
			{
				$check_acl = true;
			};
		}
		
		// make the subtemplate names for this and the next level
		$mn = "MENU_".$name."_L".$this->level."_ITEM";
		$mn2 = "MENU_".$name."_L".($this->level+1)."_ITEM";

		$this->vars(array(
			"sel_menu_".$name."_L".$this->level."_cnt" => 0,
		));

		$in_path = in_array($parent,$path);
		if (is_array($this->menu_defaults[$name]))
		{
			if ($this->menu_defaults[$name][$parent] == $parent)
			{
				if (!in_array($this->menu_defs_name_map[$name], $path))
				{
					$in_path = true;
				}
			}
		}
		else
		{
			if ($this->menu_defaults[$name] == $parent)
			{
				if (!in_array($this->menu_defs_name_map[$name], $path))
				{
					$in_path = true;
				}
			}
		}

		$parent_tpl = $this->is_parent_tpl($mn2, $mn);
		if (!( ($in_path||$this->level == 1) || ($parent_tpl && $in_path) || $ignore_path))
		{
			// don't show unless the menu is selected (in the path)
			// or the next level subtemplates are nested in this one
			// which signifies that we sould show them anyway
			// ignore all these if the meny is a 1st level menu 
			$this->level--;
			array_pop($this->menu_aliases);
			return 0;
		}
		$this->vars(array(
			$mn => ""
		));

		$no_mid = false;
		// go over the menus on this level
		$l = "";
		$l_mid = "";

		reset($this->mpr[$parent]);

		$tmp = $this->mpr[$parent];
		if (isset($this->mar[$parent]["meta"]["sort_by_name"]) && $this->mar[$parent]["meta"]["sort_by_name"])
		{
			uasort($tmp, create_function('$a,$b','if ($a["name"] > $b["name"]) { return 1;} else if ($a["name"] < $b["name"]) { return -1;} else {return 0;}'));
		}

		$second = false;
		$second_n = false;

		// find out how many menus do we have so we know when to use
		// the _END moficator
		$total = sizeof($tmp) - 1;

		while (list(,$row) = each($tmp))
		{
			$bro = false;
			$row["mtype"] = $row["type"];
			// here we fake the brother menus
			if ($row["class_id"] == CL_BROTHER)
			{
				$trow = $this->mar[$row["brother_of"]];
				$trow["parent"] = $row["parent"];
				$trow["oid"] = $row["oid"];
				$row = $trow;
				$bro = true;
			}
			
			if ($row["oid"] == $this->section)
			{
				$this->subitems[$name . "_L" . $this->level]  = sizeof($this->mpr[$row["oid"]]);
			}

			// make submenus_from_menu work
			if (($smfm = $row['meta']['submenus_from_menu']))
			{
				// copy all submenus
				if (is_array($this->mpr[$smfm]))
				{
					foreach($this->mpr[$smfm] as $_idx => $_dat)
					{
						$_dat["parent"] = $row['oid'];
						$this->mpr[$row['oid']][] = $_dat;
					}
				}
			}

			// it's already uncompressed, use it
			$meta = $row["meta"];

			// see on siis n䤡la parema paani leadide n�tamine
			// nme h䫫. FIX ME.
			if (isset($meta["show_lead"]) && $meta["show_lead"])
			{
				$activeperiod = aw_global_get("act_per_id");
				$this->save_handle();
				$q = "SELECT objects.oid,documents.* AS lead FROM objects LEFT JOIN documents ON (objects.oid = documents.docid) WHERE parent = $row[oid] AND status = 2 AND objects.period = '$activeperiod' AND class_id = " . CL_PERIODIC_SECTION;
				$this->db_query($q);
				$xdat = $this->db_next();

				if (!$xdat)
				{
					continue;
				};

				$done = $this->doc->gen_preview(array("docid" => $xdat["oid"], "tpl" => "nadal_film_side_lead.tpl","leadonly" => 1, "section" => $row["oid"],    "strip_img" => 0));

				$this->vars(array("lugu" => $done));
				
				$this->restore_handle();
			};

			// only show content menus
			if ($row["mtype"] != MN_CONTENT && $row["mtype"] != MN_CLIENT && $row["mtype"] != MN_HOME_FOLDER_SUB && $row["mtype"] != MN_PMETHOD)
			{
				continue;
			}

			if ($row["hide_noact"] || aw_ini_get("menuedit.all_menus_makdp") == true)
			{
				// also go through the menus below this one to find out if there are any documents beneath those
				// since then we must show the menu
				if (!$this->has_sub_dox($row["oid"]))
				{
					continue;
				}
			}

			if ($this->sel_section == $row["oid"] && $this->is_template("MENU_".$name."_SEEALSO_ITEM"))
			{
				$this->do_seealso_items($row,$name);
				// center menus as used in www.stat.ee 
				if (!$this->active_doc)
				{
					$this->do_center_menu($row["oid"]);
				}
			}

			$is_sel = false;
			if (is_array($this->menu_defaults[$name]))
			{
				if ($this->menu_defaults[$name][$row["oid"]] == $row["oid"])
				{
					if (!in_array($this->menu_defs_name_map[$name], $path))
					{
						$is_sel = true;
					}
				}
			}
			else
			{
				if ($this->menu_defaults[$name] == $row["oid"])
				{
					if (!in_array($this->menu_defs_name_map[$name], $path))
					{
						$is_sel = true;
					}
				}
			}

			if (in_array($row["oid"], $path) || $is_sel)
			{
				$this->vars(array(
					"sel_menu_".$name."_L".$this->level."_cnt" => $cnt,
					"sel_menu_".$name."_L".$this->level."_name" => $row["name"],
					"sel_menu_".$name."_L".$this->level."_id" => $row["oid"],
					"sel_menu_".$name."_L".$this->level."_color" => $meta["color"],
					"sel_menu_".$name."_L".$this->level."_comment" => $row["comment"]
				));
				$this->sel_menus[$name][$this->level] = $row["oid"];
			}

			$this->vars(array($mn2."_N" => ""));
			$ap = "";

			if ($this->is_template($mn."_N"))
			{
				$cont = true;
				if (is_array($this->menu_defaults[$name]))
				{
					if ($this->menu_defaults[$name][$row["parent"]] == $row["parent"])
					{
						if (!in_array($this->menu_defs_name_map[$name], $path))
						{
							$cont = false;
						}
					}
				}
				else
				{
					if ($this->menu_defaults[$name] == $row["parent"])
					{
						if (!in_array($this->menu_defs_name_map[$name], $path))
						{
							$cont = false;
						}
					}
				}
				if (!in_array($row["parent"],$path) && $cont)
				{
					continue;
				}
				else
				{
					$mn=$mn."_N";
				}
			}

			if ($this->is_parent_tpl("MENU_".$name."_L".($this->level+1)."_ITEM2","MENU_".$name."_L".($this->level)."_ITEM_N2"))
			{
				$this->vars(array(
					"MENU_".$name."_L".($this->level+1)."_ITEM2" => "",
				));
			}

			$n = $this->req_draw_menu($row["oid"], $name, &$path,$parent_tpl);

			if ($check_acl)
			{
				// sellele menle pole oigusi, me ei n�ta seda
				if (not($this->can("view",$row["oid"])))
				{
					continue;
				};
			};
			
			if ($cnt == $total && $this->is_template($mn."_END"))
			{
				$ap.="_END";	// last one of this level menus
			}
			elseif ($cnt == 0 && $this->is_template($mn."_BEGIN"))
			{
				$ap.="_BEGIN";	// first one of this level menus
			};
			
			$this_selected = false;
			if (in_array($row["oid"],$path) && $row["clickable"] == 1)
			{
				$ap.="_SEL";		// a selected menu
				$this_selected = true;
			};

			// if the current menu is set as the default active menu for this menu area
			// and the selected path does not go through the root of this menu area, then 
			// we should mark this menu as active
			if (is_array($this->menu_defaults[$name]))
			{
				if ($this->menu_defaults[$name][$row["oid"]] == $row["oid"])
				{
					if (!in_array($this->menu_defs_name_map[$name], $path))
					{
						$ap.="_SEL";		// a selected menu
						$this_selected = true;
					}
				}
			}
			else
			{
				if ($this->menu_defaults[$name] == $row["oid"])
				{
					if (!in_array($this->menu_defs_name_map[$name], $path))
					{
						$ap.="_SEL";		// a selected menu
						$this_selected = true;
					}
				}
			}

			if ($row["clickable"] != 1)
			{
				$ap.="_SEP";		// non-clickable menu
			};

			$is_mid = false;
/*			if ($row["mid"] == 1 && !in_array($row["parent"],$path))
			{
				// keskel olevad menyyd peavad ignoreerima seda et neid igaljuhul n2idatakse
				$no_mid = true;
				continue;
			}*/
			if ($row["mid"] == 1 && !aw_ini_get("menuedit.ignore_mids"))
			{
				$ap.="_MID";		// menu in center
				$is_mid = true;
			};

			if ($this->is_template($mn.$ap."_NOSUB") && $n == 0)
			{
				$ap.="_NOSUB";	// menu without subitems
			};
			// if no correct combination exists, use the default
			if ($GLOBALS["DBUG2"] == 1)
			{
				echo "try for template $mn $ap <br>";
			}
			if (!$this->is_template($mn.$ap))
			{
				$ap = "";	
			};

			if ($row["brother_of"])
			{
				$row = $this->mar[$row["brother_of"]];
			}

			$link = $this->make_menu_link(&$row);

			$target = ($row["target"] == 1) ? sprintf("target='%s'","_blank") : "";

			$imgurl2 = "";
			if ($this_selected)
			{
				if ($meta["img_act_url"] != "")
				{
					$imgurl = $meta["img_act_url"];
				}
				else
				{
					$imgurl = $row["img_url"];
				}
				$imgurl2 = image::check_url($imgurl);
			}
			else
			{
				$imgurl = $row["img_url"];
			}

			if ($imgurl != "")
			{
				$imgurl = sprintf("<img src='%s' border='0'>",image::check_url($imgurl));
			}
			else
			{
				$imgurl = "";
			};

			if ($meta["images_from_menu"])
			{
				$meta["menu_images"] = $this->mar[$meta["images_from_menu"]]["meta"]["menu_images"];
				$meta["num_menu_images"] = $this->mar[$meta["images_from_menu"]]["meta"]["num_menu_images"];
			}

			$num_menu_images = aw_ini_get("menuedit.num_menu_images");
			$has_image = false;
			if (isset($meta["menu_images"]) && is_array($meta["menu_images"]))
			{
				$imgar = $meta["menu_images"];
			}
			else
			{
				$imgar = array();
			}
			for ($_i=0; $_i < $num_menu_images; $_i++)
			{
				if (isset($imgar[$_i]["url"]) && $imgar[$_i]["url"] != "")
				{
					$imgurl = image::check_url($imgar[$_i]["url"]);
					$this->vars(array(
						"menu_image_".$_i => "<img src='".$imgurl."'>",
						"menu_image_".$_i."_url" => $imgurl
					));
					if (in_array($row["oid"], $path))
					{
						$this->vars(array(
							"sel_menu_".$name."_L".$this->level."_image_".$_i."_url" => $imgurl,
							"sel_menu_".$name."_L".$this->level."_image_".$_i => "<img src='".$imgurl."' border='0'>",
						));
					}
					$has_image = true;
				}
				else
				{
					$this->vars(array(
						"menu_image_".$_i => "",
						"menu_image_".$_i."_url" => ""
					));
				}
			}

			$this->vars(array(
				"text" 		=> $row["name"],
				"link" 		=> $link,
				"comment" 	=> $row["comment"],
				"section"	=> $row["oid"],
				"target" 	=> $target,
				"image"		=> $imgurl,
				"cnt" => $cnt,
				"sel_image_url" => $imgurl2,
				"color" => isset($meta["color"]) ? $meta["color"] : ""
			));

			if ($has_image)
			{
				$_hi = "";
				if ($this->is_template("HAS_IMAGE"))
				{
					if ($this->is_template($mn.$ap))
					{
						$_hi =  $this->parse($mn.$ap.".HAS_IMAGE");
					}
					else
					{
						$_hi =  $this->parse($mn.".HAS_IMAGE");
					}
				}
				$this->vars(array(
					"HAS_IMAGE" => $_hi,
					"NO_IMAGE" => ""
				));
			}
			else
			{
				$_hi = "";
				if ($this->is_template("NO_IMAGE"))
				{
					$_hi = $this->parse($mn.$ap.".NO_IMAGE");
				}
				$this->vars(array(
					"HAS_IMAGE" => "",
					"NO_IMAGE" => $_hi 
				));
			}

			if (isset($this->mpr[$row["oid"]]) && is_array($this->mpr[$row["oid"]]))
			{
				$hs = "";
				if ($this->is_template($mn.$ap.".HAS_SUBITEMS_".$name))
				{
					$this->parse($mn.$ap.".HAS_SUBITEMS_".$name);
				}
				$hsl = "";
				if ($this->is_template($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level))
				{
					$hsl = $this->parse($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level);
				}
				if (in_array($row["oid"],$path))	// this menu is selected
				{
					$_tmp = "";
					//if ($this->is_template($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL"))
					list(,$_basename) = explode(".",$mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL");
					if ($this->is_template($_basename))
					{
						$_tmp = $this->parse($_basename);
					}
					else
					if ($this->is_template($ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL"))
					{
						//$_tmp = $this->parse($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL");
						$_tmp = $this->parse($ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL");
					}
					$this->vars(array(
							"HAS_SUBITEMS_".$name."_L".$this->level."_SEL" => $_tmp
					));
					if ($this->is_template($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL_MID"))
					{
						$_hm = false;
						foreach($this->mpr[$row["oid"]] as $_row)
						{
							if ($row["mid"] == 1)
							{
								$_hm = true;
							}
						}
						if ($_hm)
						{
							$hslm = $this->parse($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL_MID");
						}
					}
					$this->vars(array(
						"HAS_SUBITEMS_".$name."_L".$this->level."_SEL_MID" => $hslm
					));
				}
			}
			else
			{
				$hs = "";
				if ($this->is_template($mn.$ap.".NO_SUBITEMS_".$name))
				{
					$hs = $this->parse($mn.$ap.".NO_SUBITEMS_".$name);
				}
				$hsl = "";
				if ($this->is_template($mn.$ap.".NO_SUBITEMS_".$name."_L".$this->level))
				{
					$hsl = $this->parse($mn.$ap.".NO_SUBITEMS_".$name."_L".$this->level);
				}
			}
			$this->vars(array(
				"HAS_SUBITEMS_".$name => $hs,
				"NO_SUBITEMS_".$name => "",
				"HAS_SUBITEMS_".$name."_L".$this->level => $hsl,
				"NO_SUBITEMS_".$name."_L".$this->level => "",
			));


			// ok, menyyd ei n2idata juhul, kui ta pole selektitud ja template MENU_BLAH_L5666_ITEM_SELONLY on defineeritud
			$istplso = $this->is_template($mn."_SELONLY");
			$issel = $this->sel_section != $row["parent"];
			$selonly = !($istplso && $issel);
			// - va juhul kui sel tasemel, mis aktiivne on pol yhtegi menyyd, siis n2idatakse eelmise taseme omi
			if ($this->mar[$this->sel_section]["parent"] == $row["parent"])
			{
				// see on aktiivne tase - 1
				if (!isset($this->mpr[$this->sel_section]) || !is_array($this->mpr[$this->sel_section]))
				{
					$selonly = true;
				}
			}

			$noshowu = aw_global_get("uid") == "" && $meta["users_only"] && aw_ini_get("menuedit.no_show_users_only") == true;
			// v6i menyy nimi on tyhi, v6i menyyle on 8eldud et users only ja kasutraja pole sisse loginud const.aw sees 
			// on defineeritud $no_show_users_only
			if ($selonly && $row["name"] != "" && !$noshowu && !$this->skip)
			{
				if ($this->is_template($mn.$ap))
				{
					if ($is_mid)
					{
						$l_mid.=$this->parse($mn.$ap);
						if ($GLOBALS["DBUG"] == 1)
						{
							echo "parse is_mid $mn $ap <br>";
						}
					}
					else
					{
						$l.=$this->parse($mn.$ap);
						if ($GLOBALS["DBUG"] == 1)
						{
							echo "parse $mn $ap <br>";
						}
					}
				}
			}
			$this->vars(array(
				$mn.$ap => "",
			));

			if (!$no_mid)
			{
				$this->vars(array($mn."_MID" => $l_mid));
			};

			if ($this->is_template($mn."2"))
			{
				if ($this->is_template($mn."2".$ap))
				{
					$l2.=$this->parse($mn."2".$ap);
				}
				$this->vars(array($mn."2".$ap => ""));
				$second = true;
			}

			if ($this->is_template($mn."_N2") && $this->sel_section == $row["parent"])
			{
				if ($this->is_template($mn."_N2".$ap))
				{
					$l2.=$this->parse($mn."_N2".$ap);
				}
				$this->vars(array($mn."_N2".$ap => ""));
				$second_n = true;
			}

			// ok, here's the tricky bit
			// if the next level subtemplate is nested in this levels subtemplate, then we must clear
			// the variable in the template parser for the next level 
			// cause if we don't and the next item on this level has no subitems
			// it will get this item's parsed submenus below it. 
			if ($parent_tpl)
			{
				$this->vars(array(
					"MENU_".$name."_L".($this->level+1)."_ITEM" => "",
				));
			}
			$cnt++;
		}

		$this->vars(array(
				$mn."_SEL" => "",
		));
		$this->vars(array($mn => $l));

		if (!$no_mid)
		{
			$this->vars(array($mn."_MID" => $l_mid));
		}

		if ($second)
		{
			$this->vars(array($mn."2" => $l2));
		}

		if ($second_n)
		{
			$this->vars(array($mn."_N2" => $l2));
		}
		$this->level--;
		array_pop($this->menu_aliases);
		return $cnt;
	}
	
	////
	// !draws MENU_$name_SEEALSO_ITEM 's for the menu given in $row
	function do_seealso_items($row,$name)
	{
		$menus = $this->get_aliases_of(array(
			"oid" => $row["oid"],
			// XXX, this relation type is defined in "menu.aw", but I feel bad
			// about including one big-ass file just to get one constant. - duke
			//
			// actually, this is not the only place that has problems with that. so, 
			// maybe we should define those somewhere else, where every class has access if they want?
			// like menu.aw.const - defines constants for menu.aw class, and const_load("menu") includes that?
			// whadaya think?
			"reltype" => 5,
			"lang_id" => aw_global_get("lang_id"),
		));

		$tmp = array();
		$subtpl = "MENU_${name}_SEEALSO_ITEM";

		$mc = get_instance("menu_cache");
	
		// get_aliases_of ensures that return value is always array
		foreach($menus as $said => $foo)
		{
			$samenu = $mc->get_cached_menu($said);
			if ($samenu["status"] != STAT_ACTIVE)
			{
				continue;
			}

			$link = $this->make_menu_link($samenu);
			$ord = (int)$samenu["meta"]["seealso_order"];

			// the jrk number is in $samenu["meta"]["seealso_order"]

			if (!($samenu["meta"]["users_only"] == 1 && aw_global_get("uid") == ""))
			{
				$this->vars(array(
					"target" => $samenu["target"] ? "target=\"_blank\"" : "",
					"link" => $link,
					"text" => str_replace("&nbsp;", " ", $samenu["name"]),
				));
				$tmp[$ord] .= $this->parse($subtpl);
			}
		}

		// make sure, they are in correct order
		ksort($tmp);
		$this->vars(array(
			$subtpl => join("",$tmp),
		));
	}



	function make_yah($path)
	{
		// now build "you are here" links from the path
		$ya = "";  
		$show = false;
		$cnt = count($path);

		$this->title_yah = "";
		$alias_path = array();

		for ($i=0; $i < $cnt; $i++)	
		{
			if (!aw_ini_get("menuedit.long_menu_aliases"))
			{
				$alias_path = array();
			}

			if ($show)
			{
				$ref = $this->mar[$path[$i+1]];
				if ($ref["alias"])
				{
					if (sizeof($alias_path) == 0)
					{
						$use_aliases = true;
					};

					if ($use_aliases)
					{
						array_push($alias_path,$ref["alias"]);
					};

					if ($use_aliases)
					{
						$linktext = join("/",$alias_path);
					};

					$link = $this->cfg["baseurl"]."/".$linktext;
				}
				else
				{
					$use_aliases = false;
					$link = $this->cfg["baseurl"]."/".$this->mar[$path[$i+1]]["oid"];
				};

				if ($this->mar[$path[$i+1]]["link"] != "")
				{
					$link = $this->mar[$path[$i+1]]["link"];
				}

				$this->vars(array(
					"link" => $link,
					"text" => str_replace("&nbsp;"," ",strip_tags($this->mar[$path[$i+1]]["name"])), 
					"ysection" => $this->mar[$path[$i+1]]["oid"]
				));

				$check_subs = ($this->subs[$this->mar[$path[$i+1]]["oid"]] > 0) || aw_ini_get("menuedit.yah_no_subs");

				if ($this->mar[$path[$i+1]]["clickable"] == 1 && $check_subs)
				{
					$ya.=$this->parse("YAH_LINK");
					$this->title_yah.=" / ".$this->mar[$path[$i+1]]["name"];
				}
			}
			// don't show things that are before $frontpage
			if (isset($path[$i]) && isset($this->mar[$path[$i]]) && $this->mar[$path[$i]]["oid"] == $this->cfg["rootmenu"])
			{
				$show = true;
			}
		}

		// form table yah links get made here. 
		// basically the session contains a vriable fg_table_sessions that has all the possible yah links for 
		// all shown tables (and yeah, I know it is gonna be friggin huge. 
		// and no, I can't remove the old ones, cause the user might have other windows open
		// and if I remove all the other ones from the array, he will lose the yah link in other windows
		if ($GLOBALS["tbl_sk"] != "")
		{
			$tbld = aw_global_get("fg_table_sessions");
			$ar = new aw_array($tbld[$GLOBALS["tbl_sk"]]);
			foreach($ar->get() as $url)
			{
				preg_match_all("/restrict_search_yah\[\]=([^&$]*)/",$url,$mt);
				$this->vars(array(
					"link" => $url,
					"text" => urldecode($mt[1][count($mt[1])-1])
				));
				if (urldecode($mt[1][count($mt[1])-1]) != "")
				{
					$ya.=$this->parse("YAH_LINK");
				}
			}
		}
		return $ya;
	}
			
	////
	// !Creates a link for the menu
	function make_menu_link(&$row)
	{
		$this->skip = false;
		if ($row["mtype"] == MN_PMETHOD)
		{
			// I should retrieve orb definitions for the requested class
			// to figure out which arguments it needs and then provide
			// those
			$pclass = $row["meta"]["pclass"];
			if ($pclass)
			{
				list($_cl,$_act) = explode("/",$pclass);
				$orb = get_instance("orb");
				$meth = $orb->get_public_method(array(
					"id" => $_cl,
					"action" => $_act,
				));
				$values = array();
				$err = false;
				$mv = new aw_array($meth["values"]);
				$mr = new aw_array($meth["required"]);
				foreach($mr->get() as $key => $val)
				{
					if (in_array($key,array_keys($mv->get())))
					{
						$values[$key] = $meth["values"][$key];
					}
					else
					{
						$err = true;
					};
				};

				$mo = new aw_array($meth["optional"]);
				foreach($mo->get() as $key => $val)
				{
					if (in_array($key,array_keys($mv->get())))
					{
						$values[$key] = $meth["values"][$key];
					}
				};
				if (not($err))
				{
					$_sec = aw_global_get("section");
					if ($_sec)
					{
						$values["section"] = $_sec;
					};
					$link = $this->mk_my_orb($_act,$values,$_cl,$row["meta"]["pm_url_admin"],!$row["meta"]["pm_url_menus"]);
				}
				else
				{
					$this->skip = true;
				};
			}
			else
			{
				$link = "";
			};
		}
		else if ($row["link"] != "")
		{
			$link = $row["link"];
		}
		else
		{
			$link = $this->cfg["baseurl"] ."/";
			if (aw_ini_get("menuedit.long_section_url"))
			{
				if ($row["alias"] != "")
				{
					$link .= $row["alias"];
				}
				else
				{
					$link .= "index.".$this->cfg["ext"]."?section=".$row["oid"].$this->add_url;
				}
			}
			else
			{
				if ($row["alias"] != "")
				{
					if (aw_ini_get("menuedit.long_menu_aliases"))
					{
						$tmp = array();
						if (!is_array($this->menu_aliases))
						{
							$this->menu_aliases = array();
						};
						foreach($this->menu_aliases as $_al)
						{
							if ($_al != "n/a")
							{
								$tmp[] = $_al;
							}
						}
						$link .= join("/",$tmp);
						if (sizeof($tmp) > 0)
						{
							$link .= "/";
						};
					}
					$link .= $row["alias"];
				}
				else
				{
					$oid = ($row["class_id"] == 39) ? $row["brother_of"] : $row["oid"];
					$link .= $oid;
				};
			};
		}


		// this here bullshit is so that the static ut site will work
		// basically, rewrite_links is set if we are doing searching or some other non-static action
		// it is set in site_header.aw
		// and we need to make all links go to the static site
		// and this is where the magic happens
		// also in document::do_search and search_conf::search
		if (aw_global_get("rewrite_links"))
		{
			$exp = get_instance("export");
			if (!$exp->is_external($link))
			{
				$_link = $link;
				if (strpos($link, $this->cfg["baseurl"]) === false)
				{
					$link = $this->cfg["baseurl"].$link;
				}
				$exp->fn_type = aw_ini_get("search.rewrite_url_type");
				$link = $exp->rewrite_link($link);
				if (strpos($link, "class=search_conf") === false || strpos($link, "action=search") === false)
				{
					$link = $exp->add_session_stuff($link, aw_global_get("lang_id"));
					$_tl = $link;
					$link = $this->cfg["baseurl"]."/".$exp->get_hash_for_url(str_replace($this->cfg["baseurl"],"",$link),aw_global_get("lang_id"));
//					echo "made hash for link $_tl = $link <br>";
				}
				else
				{
					$link = str_replace($this->cfg["baseurl"],aw_ini_get("search.baseurl"),$link);
				};
				
				if ($link != $this->cfg["baseurl"]."/index.".$this->cfg["ext"] && 
						$link != $this->cfg["baseurl"]."/" &&
						$link != $this->cfg["baseurl"])
				{
					$exp->fn_type = aw_ini_get("search.rewrite_url_type");
					$link = $exp->rewrite_link($link);
					if (strpos($link, "class=search_conf") === false || strpos($link, "action=search") === false)
					{
						$link = $exp->add_session_stuff($link, aw_global_get("lang_id"));
						$_tl = $link;
						$link = $this->cfg["baseurl"]."/".$exp->get_hash_for_url(str_replace($this->cfg["baseurl"],"",$link),aw_global_get("lang_id"));
	//					echo "made hash for link $_tl = $link <br>";
					}
					else
					{
						$link = str_replace($this->cfg["baseurl"],aw_ini_get("search.baseurl"),$link);
					}
				}
			}
		}
		return $link;
	}
	
	////
	// !See jupp siin teeb promokasti
	function make_promo_boxes($section)
	{
		$doc = get_instance("document");

		# reset period, or we don't see contents of promo boxes under periodic menus:
		$doc->set_period(0);

		$right_promo = "";
		$left_promo = "";
		$scroll_promo = "";

		$template = $this->get_lead_template($section);
		if ($this->cfg["lang_menus"])
		{
			$lai = "AND objects.lang_id = ".aw_global_get("lang_id");
		}
		if (aw_ini_get("menuedit.promo_lead_only"))
		{
			$leadonly = 1;
		}
		else
		{
			$leadonly = -1;
		};
		$q = "SELECT objects.*, template.filename as filename,menu.link as link,objects.metadata as metadata
				FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				LEFT JOIN template ON template.id = menu.tpl_lead
				WHERE objects.status = 2 AND objects.class_id = ".CL_PROMO." AND (objects.site_id = ".$this->cfg["site_id"]." OR objects.site_id is null) $lai
				ORDER by jrk";
		$this->db_query($q);
		$promos = array();
		$gidlist = aw_global_get("gidlist");
		while ($row = $this->db_next())
		{
			if (not($row["filename"]))
			{
				$row["filename"] = aw_ini_get("promo.default_tpl");
				if (not($row["filename"]))
				{
					continue;
				}
			};
			$meta = $this->get_object_metadata(array("metadata" => $row["metadata"]));

			$found = false;

			if (!is_array($meta["groups"]) || count($meta["groups"]) < 1)
			{
				$found = true;
			}
			else
			{
				foreach($meta["groups"] as $gid)
				{
					if (isset($gidlist[$gid]) && $gidlist[$gid] == $gid)
					{
						$found = true;
					}
				}
			}

			$doc->doc_count = 0;

			$show_promo = false;


			if ($meta["all_menus"])
			{
				$show_promo = true;
			}
			else
			if (isset($meta["section"][$section]) && $meta["section"][$section])
			{
				$show_promo = true;
			}
			else
			if (isset($meta["section_include_submenus"]))
			{
				if (is_array($meta["section_include_submenus"]) && in_array($section,$this->path) )
				{
					// here we need to check, whether any of the parent menus for
					// this menu has been assigned a promo box and has been told
					// that it should be shown in all submenus as well
					$intersect = array_intersect($this->path,$meta["section_include_submenus"]);
					if (sizeof($intersect) > 0)
					{
						$show_promo = true;
					}
				}
			}

			if ($found == false)
			{
				$show_promo = false;
			};
			
			// this line decides, whether we should show this promo box here or not.
			// now, how do I figure out whether the promo box is actually in my path?
			if ($show_promo)
			{
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
						if ($row["filename"])
						{
							$cont = $doc->gen_preview(array(
								"docid" => $d,
								"tpl" => $row["filename"],
								"leadonly" => $leadonly,
								"section" => $section,
								"strip_img" => false,
								"showlead" => 1,
								"boldlead" => 1,
								"no_strip_lead" => 1,
								"no_acl_checks" => aw_ini_get("menuedit.no_view_acl_checks"),
							));
							$pr_c .= str_replace("\r","",str_replace("\n","",$cont));
						}
					}
				}
				else
				{
					$pr_c.=$doc->gen_preview(array(
						"docid" => $docid, 
						"tpl" => $row["filename"],
						"leadonly" => $leadonly, 
						"section" => $section, 	
						"strip_img" => false,
						"showlead" => 1, 
						"boldlead" => 0,
						"no_strip_lead" => 1,
						"no_acl_checks" => aw_ini_get("menuedit.no_view_acl_checks"),
					));
				}

				dbg::p($pr_c);

				$this->vars(array(
					"comment" => $row["comment"],
					"title" => $row["name"], 
					"content" => $pr_c,
					"url" => $row["link"],
					"link_caption" => $meta["link_caption"]
				));

				// which promo to use? we need to know this to use
				// the correct SHOW_TITLE subtemplate
				$templates = array(
					"scroll" => "SCROLL_PROMO",
					"0" => "LEFT_PROMO",
					"1" => "RIGHT_PROMO",
					"2" => "UP_PROMO",
					"3" => "DOWN_PROMO",
				);
	
				$use_tpl = $templates[$meta["type"]];
				if (!$use_tpl)
				{
					$use_tpl = "LEFT_PROMO";
				};

				if ($meta["no_title"] != 1)
				{
					$this->vars(array("SHOW_TITLE" => $this->parse($use_tpl . ".SHOW_TITLE")));
				}
				else
				{
					$this->vars(array("SHOW_TITLE" => ""));
				}
				$ap = "";
				if ($row["link"] != "")
				{
					$ap = "_LINKED";
				}
				if ($this->used_promo_tpls[$use_tpl] != 1)
				{
					$ap.="_BEGIN";
					$this->used_promo_tpls[$use_tpl] = 1;
				}

				if ($this->is_template($use_tpl . $ap))
				{
					$promos[$use_tpl] .= $this->parse($use_tpl . $ap);
					$this->vars(array($use_tpl . $ap => ""));
				}
				else
				{
					$promos[$use_tpl] .= $this->parse($use_tpl);
					$this->vars(array($use_tpl => ""));
				};
				// nil the variables that were imported for promo boxes
				// if we dont do that we can get unwanted copys of promo boxes
				// in places we dont want them
				$this->vars(array("title" => "", "content" => "","url" => ""));
				$this->restore_handle();
			}
		};

		$this->vars($promos);
	}

	function make_poll()
	{
		$t = get_instance("poll");
		$this->vars(array("POLL" => $t->gen_user_html()));
	}

	function make_search()
	{
		if ($this->is_template("SEARCH_SEL"))
		{
			global $section;
			$id = (int)$section;
			if (!$id)
			{
				$id=$this->cfg["frontpage"];
			}
			$t = get_instance("search_conf");
			$def = $GLOBALS["HTTP_GET_VARS"]["parent"] ? $GLOBALS["HTTP_GET_VARS"]["parent"] : $section;
			$sl = $t->get_search_list(&$def);
			$this->vars(array(
				"search_sel" => $this->option_list($def,$sl),
				"section" => $id,
				"str" => htmlentities($GLOBALS["HTTP_GET_VARS"]["str"])
			));
			$this->vars(array("SEARCH_SEL" => $this->parse("SEARCH_SEL")));
		}
	}
	
	function make_menu_caches($where = "objects.status = 2")
	{
		$mc = get_instance("menu_cache");
		$mc->make_caches();
		upd_instance("menu_cache",$mc);
		$this->subs =  $mc->get_ref("subs");
		$this->mar =  $mc->get_ref("mar");
		$this->mpr =  $mc->get_ref("mpr");
	}

	
	function is_periodic($section,$checkobj = 1) 
	{
		$mn = $this->get_object($section);
		if ($mn["class_id"] != CL_PSEUDO)
		{
			$mn = $this->get_object($mn["parent"]);
		}
		$periodic = $mn["periodic"];
		// menyysektsioon ei ole perioodiline. Well, vaatame 
		// siis, kas ehk dokument ise on?
		if (!$mn && !$periodic && $checkobj == 1)
		{
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
				$has_dox |= $this->has_sub_dox($row["oid"]);
				if ($this->subs[$row["oid"]] > 0)
				{
					$has_dox = 1;
				}
			}
		}

		return $has_dox;
	}
	
	////
	// !Redirect the user if he/she didn't have the right to view that section
	function no_access_redir($section)
	{
		$c = get_instance("config");
		$ec = $c->get_simple_config("errors");
		$ra = aw_unserialize($ec);
			
		$gidlist = aw_global_get("gidlist");
		if (is_array($gidlist))
		{
			$d_gid = 0;
			$d_pri = 0;
			$d_url = "";
			foreach($gidlist as $gid)
			{
				if ($ra[$gid]["pri"] >= $d_pri && $ra[$gid]["url"] != "")
				{
					$d_gid = $gid;
					$d_pri = $ra[$gid]["pri"];
					$d_url = $ra[$gid]["url"];
				}
			}
			
			if ($d_url != "")
			{
				if ($d_url != aw_global_get("REQUEST_URI"))
				{
					header("Location: $d_url");
					die();
				}
				else
				{
					$this->raise_error(ERR_ACL_ERR,"Access denied and error redirects are defined.incorrectly. Please report this to the site administrator",1);
				};
					
			}
		}
		$this->raise_error(ERR_MNEDIT_NOACL,"No ACL error messages defined! no can_view access for object $section",true);
	}
	
	////
	// !Checks whether the section or one of it's parents is marked as "users_only
	function users_only_redir()
	{
		$dbc = get_instance("config");
		$url = $dbc->get_simple_config("orb_err_mustlogin");
		aw_session_set("request_uri_before_auth",aw_global_get("REQUEST_URI"));
		header("Location: ".$this->cfg["baseurl"]."/$url");
		// exit from inside the class, yuck.
		exit;
	}
	
	function do_menu_images($sel_menu_id)
	{
		$si_parent = $sel_menu_id;
		$imgs = false;
		$smi = "";
		$sel_image = "";

		while ($sel_image == "" && $si_parent)
		{
			$sel_menu_meta = $this->mar[$si_parent]["meta"];
			if ($sel_menu_o_img_url == "")
			{
				$sel_menu_o_img_url = image::check_url($this->mar[$si_parent]["img_url"]);
			}
			$imfm = $sel_menu_meta["images_from_menu"];
			if ($imfm)
			{
				if (!isset($this->mar[$imfm]))
				{
					$mc = get_instance("menu_cache");
					$this->mar[$imfm] = $mc->get_cached_menu($imfm);
				}
				$sel_menu_meta["menu_images"] = $this->mar[$imfm]["meta"]["menu_images"];
			}

			if (is_array($sel_menu_meta["menu_images"]) && count($sel_menu_meta["menu_images"]) > 0)
			{
				$imgs = true;
				break;
			}

			if ($sel_menu_meta["img_act_url"] != "")
			{
				$sel_image = "<img name='sel_menu_image' src='".image::check_url($sel_menu_meta["img_act_url"])."' border='0'>";
				$sel_image_url = image::check_url($sel_menu_meta["img_act_url"]);
				$this->vars(array(
					"url" => image::check_url($sel_menu_meta["img_act_url"])
				));
				$smi .= $this->parse("SEL_MENU_IMAGE");
				break;
			}
			$si_parent = $this->mar[$si_parent]["parent"];
		}

		if ($imgs)
		{
			$imgar = $sel_menu_meta["menu_images"];
			$smi = "";
			foreach($imgar as $nr => $dat)
			{
				if ($smi == "")
				{
					$sel_image = "<img name='sel_menu_image' src='".image::check_url($dat["url"])."' border='0'>";
					$sel_image_url = $dat["url"];
				}
				$this->vars(array(
					"url" => image::check_url($dat["url"])
				));
				$smi .= $this->parse("SEL_MENU_IMAGE");
				if ($dat["url"] != "")
				{
					$this->vars(array(
						"sel_menu_image_".$nr => "<img name='sel_menu_image_".$nr."' src='".$dat["url"]."' border='0'>"
					));
				}
			}
		}
		$smn = $this->mar[$sel_menu_id]["name"];
		if (aw_ini_get("menuedit.strip_tags"))
		{
			$smn = strip_tags($smn);
		}

		$smn_nodoc = $smn;
		if ($this->active_doc)
		{
			$smn_nodoc = "";
		}
		$this->vars(array(
			"SEL_MENU_IMAGE" => $smi,
			"sel_menu_name" => $smn,
			"sel_menu_name_no_doc" => $smn_nodoc,
			"sel_menu_image" => $sel_image,
			"sel_menu_image_url" => $sel_image_url,
			"sel_menu_o_img_url" => $sel_menu_o_img_url,
			"sel_menu_id" => $sel_menu_id,
			"sel_menu_timing" => $sel_menu_meta["img_timing"] ? $sel_menu_meta["img_timing"] : 6 
		));
		if ($this->site_title == "")
		{
			$this->site_title = $this->mar[$sel_menu_id]["name"];
		}
	}
	
	function is_link_collection($section)
	{
		$p = $section; 
		$links = false;
		$cnt = 0;
		while ($p && ($cnt < 20))
		{
			$cnt++;
			if (isset($this->mar[$p]["links"]) && $this->mar[$p]["links"])
			{
				$p = 0;
				$links = true;
			}	
			$p = $this->mar[$p]["parent"];
		}
		return $links;
	}

	function is_shop($section)
	{
		$p = $section; 
		$links = false;
		$cnt = 0;
		while ($p && ($cnt < 20))
		{
			$cnt++;
			if (!is_array($this->mar[$p]))
			{
				//$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE oid = $p");
				// no point in overwriting it
				if (not($this->mar[$p]))
				{
					$this->mar[$p] = $this->get_menu($p);
				};
			}

			if (isset($this->mar[$p]["is_shop"]) && $this->mar[$p]["is_shop"] == 1)
			{
				$sh_id = $this->mar[$p]["shop_id"];
				$p = 0;
				$links = true;
			}	
			isset($this->mar[$p]["parent"]) ? $p = $this->mar[$p]["parent"] : $p = 0;
		}
		if (!$links)
		{
			return false;
		}
		else
		{
			// so, if that's a shop, we turn off the right pane. right.	
			$this->right_pane = false;
			return $sh_id;
		}
	}

	function show_periodic_documents($section,$obj)
	{
		$d = get_instance("document");
		$cont = "";
		// if $section is a periodic document then emulate the current period for it and show the document right away
		$d->set_opt("parent",$section);
		if ($obj["class_id"] == CL_PERIODIC_SECTION)
		{
			$template = $this->get_long_template($section);
			$activeperiod = $obj["period"];
			$cont = $d->gen_preview(array(
				"docid" => $section,
				"boldlead" => 1,
				"keywords" => 1,
				"tpl" => $template
			));
			$this->vars(array("docid" => $section));
			$this->active_doc = $section;
			$d->set_opt("shown_document",$section);
			$PRINTANDSEND = $this->parse("PRINTANDSEND");
		}
		else
		{
			$activeperiod = aw_global_get("act_per_id");
			$d->set_period($activeperiod);
			$d->list_docs($section, $activeperiod,2);
	
			// I need to  know that for the public method menus
			$d->set_opt("cnt_documents",$d->num_rows());


			if ($d->num_rows() > 1)		// the database driver sets this
			{
				$this->vars(array("DOCUMENT_LIST" => $this->parse("DOCUMENT_LIST")));
				$template = $this->get_lead_template($section);
				while($row = $d->db_next()) 
				{
					$d->save_handle();
					$d->set_period($row["period"]);
					$cont .= $d->gen_preview(array(
						"docid" => $row["docid"],
						"tpl" => $template,
						"leadonly" => 1,
						"section" => $section,
						"keywords" => 1,
						"doc"	=> $row));
					$d->restore_handle();
					$d->_init_vars();
				}; // while
			} // if
			// on 1 doku
			else 
			{
				$row = $d->db_next();
				$template = $this->get_long_template($section);
				$d->set_opt("shown_document",$row["docid"]);
				$cont = $d->gen_preview(array(
					"docid" => $row["docid"],
					"boldlead" => 1,
					"keywords" => 1,
					"tpl" => $template
				));
				$this->vars(array("docid" => $row["docid"]));
				$this->active_doc = $row["docid"];
				$PRINTANDSEND = $this->parse("PRINTANDSEND");
			}
		}
		upd_instance("document",$d);
		return $cont;
	}

	function show_documents($section,$docid,$template = "")
	{
		$d = get_instance("document");
		// Vaatame, kas selle sektsiooni jaoks on "default" dokument
		if ($docid < 1) 
		{
			$docid = $this->get_default_document($section);
		};
		$ct = "";

		$jumpbox = false;
		if ($this->is_template("JUMPBOX") && ($this->mar[$section]["meta"]["multi_doc_style"]))
		{
			$jumpbox = true;
		};


		// oleks vaja teha voimalus feedbacki tegemiseks. S.t. doku voib 
		// lisaks enda sisule tekitada veel mingeid datat, mida siis menuedit
		// voiks paigutada saidi raami sisse. Related links .. voi nimekiri
		// mingitest artiklis esinevatest asjadest. You name it.
		$this->blocks = array();

		$template = $this->get_long_template($section);

		$metaref = array();
		
		$d->set_opt("parent",$section);

		if (is_array($docid)) 
		{
			$template = $this->get_lead_template($section);
			
			// I need to  know that for the public method menus
			$d->set_opt("cnt_documents",sizeof($docid));
		
			$template = $template == "" ? "plain.tpl" : $template;
			$template2 = file_exists($this->cfg["tpldir"]."/automatweb/documents/".$template."2") ? $template."2" : $template;
			$ct = ""; 
			$dk=1;
			// I hate this. docid on dokumendi id,
			// ja seda ei peaks arrayna kasutama
			reset($docid);
			while (list(,$did) = each($docid)) 
			{
				$d->_init_vars();

				$tpl = "";
				if ($jumpbox)
				{
					$oid = aw_global_get("oid");
					if ($oid)
					{
						$tpl = ($oid == $did) ? "ACTIVE_LINK" : "LINK";
					}
					else
					{
						if ($done)
						{
							$tpl = "LINK";
						}
						else
						{
							$tpl = "ACTIVE_LINK";
							$done = true;
						};
                                        }


					$dobj = $this->get_object($did);
					$this->vars(array(
						"text" => $dobj["name"],
						"link" => aw_global_get("baseurl") . "/section=$section" . "/oi
						d=$did",
					));
					$JMP .= $this->parse($tpl);
				}
				else
				{
					$ct.=$d->gen_preview(array(
						"docid" => $did,
						"tpl" => ($dk & 1 ? $template : $template2),
						"leadonly" => 1,
						"section" => $section,
						"strip_img" => false,
						"keywords" => 1,
						"no_strip_lead" => aw_global_get("document.no_strip_lead")
					));
				};

				if ($tpl == "ACTIVE_LINK")
				{
					$ct =$d->gen_preview(array(
						"docid" => $did,
						"section" => $section,
						"strip_img" => false,
						"tpl" => $longtpl,
						"keywords" => 1,
						"no_strip_lead" => aw_global_get("document.no_strip_lead")
					));
				};

				if ($d->referer)
				{
					$metaref[] = $d->referer;
				}
				$dk++;
			} // while
			if ($jumpbox)
			{
				$this->vars(array(
					"LINK" => $JMP,
				));

				$this->vars(array(
					"JUMPBOX" => $this->parse("JUMPBOX"),
				));
			};

		} 
		else 
		{
			// kui docid on 0, siis leiame default doku
			if ($docid == 0)
			{
				$pobj = $this->get_object($section);
				$lastar = unserialize($pobj["last"]);
				// this is wrong, lang_id should be used
				if (is_array($lastar))
				{
					list(,$default_doc) = each($lastar);
					$docid = $default_doc;
				}
			};

			if ($docid)
			{
				$d->set_opt("cnt_documents",1);
				$d->set_opt("shown_document",$docid);

				$ct = $d->gen_preview(array(
					"docid" => $docid,
					"section" => $section,
					"no_strip_lead" => aw_ini_get("document.no_strip_lead"),
					"notitleimg" => 0,
					"tpl" => $template,
					"keywords" => 1,
					"boldlead" => aw_ini_get("document.boldlead"),
					"no_acl_checks" => aw_ini_get("menuedit.no_view_acl_checks"),
				));

				if ($d->referer)
				{
					$metaref[] = $d->referer;
				};

				if ($d->no_left_pane)
				{
					$this->left_pane = false;
				}

				if ($d->no_right_pane)
				{
					$this->right_pane = false;
				}

				$this->vars(array(
					"docid" => $docid,
					"section" => $section,
				));
				
				$PRINTANDSEND = $this->parse("PRINTANDSEND");

				$this->vars(array(
					"section" => $section,
					"docid" => $docid,
					"PRINTANDSEND" => $PRINTANDSEND
				));

				$this->active_doc = $docid;

				if ($d->title != "")
				{
					$this->site_title = " / ".$d->title;
				}
				
				if (is_array($d->blocks))
				{
					$this->blocks = $this->blocks + $d->blocks;
				};
			}
		}
		$this->metaref = $metaref;
		upd_instance("document",$d);
		return $ct;
	}
	
	function make_banners()
	{
		$banner_defs = aw_ini_get("menuedit.banners");
		$banner_server = aw_ini_get("menuedit.banner_server");
		$ext = $this->cfg["ext"];
		$uid = aw_global_get("uid");

		if (!is_array($banner_defs))
		{
			return;
		}

		reset($banner_defs);
		while (list($name,$gid) = each($banner_defs))
		{
			$htmlf = $banner_server."/banner.$ext?gid=$gid&html=1";
			if ($uid != "")
			{
				$htmlf.="&aw_uid=".$uid;
			}
			// duhhh!! always check whether fopen succeeds!!
			$f = @fopen($htmlf,"r");
			if ($f)
			{
				$fc = fread($f,100000);
				fclose($f);

				$fc = str_replace("[ss]","[ss".$gid."]",$fc);
			};

			$this->vars(array("banner_".$name => $fc));
		}
	}
	
	function do_center_menu($oid)
	{
		$mpar = $this->mpr[$oid];
		if (is_array($mpar))
		{
			foreach($mpar as $mprow)
			{
				if ($mprow["mid"])
				{
					if ($this->has_sub_dox($mprow["oid"]))
					{
						if ($mprow["link"] != "")
						{
							$link = $mprow["link"];
						}
						else
						if ($mprow["alias"] != "")
						{
							$link = $mprow["alias"];
						}
						else
						{
							$link = $this->cfg["baseurl"]."/".$mprow["oid"];
						}
						$this->vars(array(
							"link" => $link,
							"target" => ($mprow["target"] ? "target=\"_blank\"" : ""),
							"text" => str_replace("&nbsp;"," ",strip_tags($mprow["name"]))
						));
						$mmd.=$this->parse("CENTER_MENU");
					}
				}
			}
			$this->vars(array("CENTER_MENU" => $mmd));
		}
	}


	function do_sub_callbacks($sub_callbacks, $after = false)
	{
		if ($after)
		{
			$sub_callbacks = false;
			if (function_exists("__get_site_instance"))
			{
				$si =&__get_site_instance();
				if (is_object($si))
				{
					if (method_exists($si, "get_sub_callbacks_after"))
					{
						$sub_callbacks = $si->get_sub_callbacks_after();
					}
				}
			}
		}

		if (is_array($sub_callbacks))
		{
			// ok, check if the new and better OO (TM) way exists
			if (function_exists("__get_site_instance"))
			{
				$si =&__get_site_instance();
				if (is_object($si))
				{
					foreach($sub_callbacks as $sub => $fun)
					{
						if ($this->is_template($sub))
						{
							if (method_exists($si, $fun))
							{
								$si->$fun(&$this);
							}
							else
							{
								if (function_exists($fun))
								{
									$fun(&$this);
								}
							}
						}
					}
				}
				else
				{
					foreach($sub_callbacks as $sub => $fun)
					{
						if ($this->is_template($sub))
						{
							$fun(&$this);
						}
					}
				}
			}
			else
			{
				foreach($sub_callbacks as $sub => $fun)
				{
					if ($this->is_template($sub))
					{
						$fun(&$this);
					}
				}
			}
		}
	}



	function do_show_layout($lid)
	{
		$li = get_instance("layout");
		return $li->show(array(
			"id" => $lid
		));
	}


	function _get_template_filename($id)
	{
		$q = "SELECT filename FROM template WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		return $row["filename"];
	}


	// builds HTML popups
	function build_popups()
	{
		// that sucks. We really need to rewrite that
		// I mean we always read information about _all_ the popups
		$q = "SELECT * FROM objects WHERE status = 2 AND class_id = " . CL_HTML_POPUP;
		$this->db_query($q);
		$popups = "";
		while($row = $this->db_next())
		{
			$meta = aw_unserialize($row["meta"]);
			if (is_array($meta["menus"]))
			{
				foreach($meta["menus"] as $key => $val)
				{
					if ($val == $this->sel_section)
					{
						$popups .= "window.open('$meta[url]','popup','top=0,left=0,toolbar=0,location=0,menubar=0,scrollbars=0,width=$meta[width],height=$meta[height]');";
					};
				};
			};
		};
		$retval = (strlen($popups) > 0) ? "<script language='Javascript'>$popups</script>" : "";
		dbg::p("l = " . strlen($retval));
		return $retval;
	}

	////
	// !generates the ui for the shop
	function do_shop($section,$shop_id)
	{
		$sh = get_instance("shop/shop");
		$ret = $sh->show(array("section" => $section,"id" => $shop_id));
		$this->vars(array("shop_menus" => $sh->shop_menus));
		return $ret;
	}

	function make_langs()
	{
		$lang_id = aw_global_get("lang_id");
		$langs = get_instance("languages");
		$lar = $langs->listall();
		$l = "";
		foreach($lar as $row)
		{
			$this->vars(array(
				"name" => $row["name"],
				"lang_id" => $row["id"]
			));
			if ($row["id"] == $lang_id)
			{
				$l.=$this->parse("SEL_LANG");
				$sel_lang = $row;
			}
			else
			{
				$l.=$this->parse("LANG");
			}
		}
		$this->vars(array(
			"LANG" => $l,
			"SEL_LANG" => "",
			"sel_charset" => $sel_lang["charset"]
		));
	}

	function get_path($section,$obj)
	{
		// now find the path through the menu
		$path = array();
		if ($obj["class_id"] != CL_PSEUDO)
		{
			$sec = $obj["parent"];
			$section = $obj["parent"];
		}
		else
		{
			$sec = $section; 
		}
		$cnt = 0;
		$tmp = array();
		// kontrollime seda ka, et kas see "sec" yldse olemas on,
		// vastasel korral satume loputusse tsyklisse
		while ($sec && ($sec != 1)) 
		{
			array_push($tmp,$sec);
			if (!isset($this->mar[$sec]))
			{
				$mc = get_instance("menu_cache");
				$this->mar[$sec] = $mc->get_cached_menu($sec);
			}
			$sec = $this->mar[$sec]["parent"];
			$cnt++;
			if ($cnt > 1000)
			{
				$this->raise_error(ERR_MNED_HIER, "Error in object hierarchy, $sec is it's own parent!", true);
			}
		}
		// now the path is in the correct order on the "root" stack

		for ($i=0; $i < $cnt; $i++) 
		{
			#$path[$i+1] = $this->_pop();
			$path[$i+1] = array_pop($tmp);
		};
		// and now in the $path array
		return $path;
	}
	
	function get_default_document($section,$ignore_global = false)
	{
		// the following line looks so wrong
		// /me vaatab syytult lakke ja teeb n2gu nagu ei saax midagi aru - terryf
		if (isset($GLOBALS["docid"]) && $GLOBALS["docid"] && $ignore_global == false)
		{
			return $GLOBALS["docid"];
		}

		if (!$section)
		{
			return 0;
		}

		$obj = $this->get_object($section);
	
		// if it is a document, use this one. 
		if (($obj["class_id"] == CL_DOCUMENT) || ($obj["class_id"] == CL_PERIODIC_SECTION))
		{
			return $section;
		}

		if ($obj["class_id"] == CL_BROTHER)
		{
			$obj = $this->get_object($obj["brother_of"]);
		}


		// if any keywords for the menu are set, we must show all the documents that match those keywords under the menu
		if ($obj["meta"]["has_kwd_rels"])
		{
			$docid = array();

			$q = "
				SELECT distinct(keywordrelations.id) as id FROM keyword2menu
				LEFT JOIN keywordrelations ON keywordrelations.keyword_id = keyword2menu.keyword_id
				LEFT JOIN objects ON keywordrelations.id = objects.oid
				WHERE keyword2menu.menu_id = '$obj[oid]' AND objects.status = 2";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$docid[] = $row["id"];
			}
			return $docid;
		}

		$docid = $obj["last"];
		$ar = unserialize($docid);


		// kuna on vaja mitme keele jaox default dokke seivida, siis uues versioonis pannaxe
		// siia array aga backward compatibility jaox tshekime, et 2kki see on integer ikkagi
		if (is_array($ar))
		{
			$docid = $ar[aw_global_get("lang_id")];
		}
		if ($docid > 0)
		{
			if ($this->cfg["lang_menus"] == 1)
			{
				$ss = "AND objects.lang_id=".aw_global_get("lang_id");
			}
			if ($this->db_fetch_field("SELECT status FROM objects WHERE oid = $docid AND class_id = ".CL_DOCUMENT." $ss ","status") != 2)	
			{
				// make sure that the default is not deleted
				$docid = 0;
			}
		}


		// ei olnud defaulti, peaks vist .. n�tama nimekirja? 
		if ($docid < 1)	
		{
			// avoid unneccessary query if the menu is already in the cache
			if ($this->mar[$section])
			{
				$me_row = $this->mar[$section];
			}
			else
			{
				$me_row = $this->get_menu($section);
			};

			if ($me_row["class_id"] == CL_PROMO)
			{
				if (is_array($me_row["meta"]["last_menus"]) && ($me_row["meta"]["last_menus"][0] !== 0))
				{
					$sections = $me_row["meta"]["last_menus"];
				}
				else
				{
					$sections = array($section);
				};
			}
			else
			{
				$sections = $me_row["meta"]["sss"];
			};

			if ($me_row["meta"]["all_pers"])
			{
				$period_instance = get_instance("period");
				$periods = $this->make_keys(array_keys($period_instance->period_list(false)));
			}
			else
			{
				$periods = $me_row["meta"]["pers"];
			}

			if (is_array($sections) && ($sections[0] !== 0))
			{
				$pstr = join(",",$sections);
				if ($pstr != "")
				{
					$pstr = "objects.parent IN ($pstr)";
					//$ordby = "objects.modified DESC";
					$lsas = " AND (documents.no_last != 1 OR documents.no_last is null ) ";
				}
				else
				{
					$pstr = "objects.parent = '$obj[oid]'";
				};
			}
			else
			{
				$pstr = "objects.parent = '$obj[oid]'";
			};
			if ($me_row["ndocs"] > 0)
			{
				$lm = "LIMIT ".$me_row["ndocs"];
			};

			$docid = array();
			$cnt = 0;
			if ($ordby == "")
			{
				if ($me_row["meta"]["sort_by"] != "")
				{
					$ordby = $me_row["meta"]["sort_by"];
					if ($me_row["meta"]["sort_ord"] != "")
					{
						$ordby .= " ".$me_row["meta"]["sort_ord"];
					}
				}
				else
				{
					$ordby = aw_ini_get("menuedit.document_list_order_by");
				}
			}

			if ($ordby == "")
			{
				$ordby = "objects.jrk";
			}

			$no_fp_document = aw_ini_get("menuedit.no_fp_document");
			if (!isset($no_fp_document))
			{
				//$this->cfg["no_fp_document"] = false;
				// ????
				$no_fp_document = false;
			}
			$q = "SELECT objects.oid as oid,objects.class_id AS class_id, objects.brother_of AS brother_of, documents.esilehel as esilehel FROM objects LEFT JOIN documents ON documents.docid = objects.brother_of WHERE (($pstr AND status = 2 AND class_id in (7,29) AND objects.lang_id=".aw_global_get("lang_id").") OR (class_id = ".CL_BROTHER_DOCUMENT." AND status = 2 AND $pstr)) $lsas ORDER BY $ordby $lm";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				if (!($no_fp_document && $row["esilehel"] == 1))
				{
					$docid[$cnt++] = ($row["class_id"] == CL_DOCUMENT) ? $row["oid"] : $row["brother_of"];
				}
			}
			if ($cnt > 1)
			{
				// a list of documents
				return $docid;
			}
			else
			if ($cnt == 1)
			{
				// the correct id
				return $docid[0];
			}
			else
			{
				return false;
			}
		}

		return $docid;
	}
	
	////
	// !Fetches the menu chain for the current object from the menu cache for further use
	// XXX: this should be moved to core - because at least one other class - document
	// uses this to figure out whether a cfgform based template should be shown -- duke
	function build_menu_chain($section)
	{
		$parent = $section;
		// this is used to store the whole menu chain
		$this->menu_chain = array();
		
		// this is where we store the full path to the object
		$this->path = array();
	
		// we will this with properties from the first element in chain who
		// has thoses
		$this->properties = array(
			"tpl_dir"  => "",
			"users_only" => 0,
			"comment" => "",
			"tpl_view" => "",
			"ftpl_view" => "",
			"tpl_lead" => "",
			"ftpl_lead" => "",
			"tpl_edit" => "",
			"ftpl_edit" => "",
			"tpl_edit_cfgform" => "",
			"show_layout" => ""
		);
	
		while($parent)
		{
			$obj = $this->mar[$parent];
			// if the object was not in the cache (which probably means it was not a menu)
			// fetch it directly from the database
			if (not($obj))
			{
				$obj = $this->get_object($parent);
			};

			// only use metadata from menus
			$is_menu = ($obj["class_id"] == CL_PSEUDO);

			if (is_array($obj))
			{
				array_unshift($this->path,$obj["oid"]);
				$this->menu_chain[$obj["oid"]] = $obj;
				// check whether this object has any properties that 
				// none of the previous ones had
				$_dat = (is_array($obj["meta"])) ? array_merge($obj,$obj["meta"]) : $obj;
				$intersect = array_intersect(array_keys($_dat),array_keys($this->properties));
				foreach($intersect as $val)
				{
					if ($is_menu && not($this->properties[$val]))
					{
						//print "<!-- found $val at $obj[oid] -->\n";
						$this->properties[$val] = $_dat[$val];
					};
				};
			};
			// also, check whether the parent of the current object is alreay handled
			// and if so, just drop out of the cycle
			$parent = (isset($this->menu_chain[$obj["parent"]]) && $this->menu_chain[$obj["parent"]]) ? false : $obj["parent"];
		};
		/*
		  print "<!--";
		  print_r($this->properties);
		  print "-->";
		*/

	}
	
	function check_object($obj)
	{
		switch($obj["class_id"])
		{
			case CL_EXTLINK:
				$t = get_instance("links");
				$link = $t->get_link($obj["oid"]);
				//list($url,$target,$caption) = $t->draw_link($obj["oid"]);
				header("Location: $link[url]");
				//$replacement = sprintf("<a href='%s' %s>%s</a>",$url,$target,$caption);
				exit;
				break;

			case CL_IMAGE:
				$t = get_instance("image");
				$idata = $t->get_image_by_id($obj["oid"]);
				$this->replacement = sprintf("<img src='%s'><br>%s",$idata["url"],$idata["comment"]);

				if ($this->raw)
				{
					print $this->replacement;
					exit;
				};
				break;

			case CL_FILE:
				$t = get_instance("file");
				die($t->show($obj["oid"]));
				break;

			case CL_TABLE:
				$t = get_instance("table");
				$this->replacement = $t->show(array("id" => $obj["oid"],"align" => $align));
				if ($this->raw)
				{	
					print $this->replacement;
					exit;
				};
				break;

			case CL_SITE_THREEPANE:
				$t = get_instance("site/site_threepane");
				print $t->show(array("id" => $obj["oid"]));
				exit;
				break;

			default:
				$this->replacement = "";

		};
	}


};
?>
