<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_show.aw,v 1.112 2005/01/19 13:34:55 kristo Exp $

/*

This message will get posted when we are showing the site. 
parameters: 
	inst - instance to which to import the variables
	params - array of parameters passed to site_show
EMIT_MESSAGE(MSG_ON_SITE_SHOW_IMPORT_VARS);

*/

class site_show extends class_base
{
	var $path;				// the path to the selected section
	var $sel_section;		// the MENU that is selected - section can point to any object below it
	var $sel_section_real;	// the MENU that is selected - section can point to any object below it -
							// this is the real object if translation is active - damn, it seems I can't make the translation
							// thing COMPLETELY transparent after all :((((((((
	var $sel_section_obj;	// the MENU OBJECT that is selected - section can point to any object below it
	var $section_obj;		// the object instance for $section
	var $properties;		// the properties gathered from menus in the path
	var $left_pane;			// whether to show LEFT_PANE sub
	var $right_pane;		// whether to show RIGHT_PANE sub
	var $active_doc;		// if only a single document is shownm this will contain the docid
	var $site_title;		// the title for the site should be put in here

	var $cache;				// cache class instance

	var $image;				// image class instance

	function site_show()
	{
		$this->init("automatweb/menuedit");
		$this->cache = get_instance("cache");
		$this->image = get_instance("image");
		$this->doc = get_instance("document");
	}

	////
	// !generates the whole site content thingie
	// parameters:
	// text - kui see != "" , siis n2idatakse dokude asemel seda
	// docid - millist dokumenti selle sektsiooni alt naidata?
	// s.t. kui on dokumentide nimekiri ntx.
	// strip_img - kas imaged maha strippida dokudest
	// template - mis template ga menyysid n2idataxe
	// vars - array kuhu saab sisu kirjutada, et seal olevad muutujad pannaxe menyyediti template sisse
	// sub_callbacks - array template_name => funxiooninimi neid kutsutakse siis v2lja kui vastav sub on template sees olemas
	// format - the format to generate the output in
	// no_left_pane - if true, the LEFT_PANE sub is by default not shown, 
	// no_right_pane - if true, the RIGHT_PANE sub is by default not shown
	// tpldir - if set, templates are read from $tpldir/automatweb/menuedit folder
	function show($arr)
	{
		if (isset($arr["type"]) && $arr["type"] != "" && $arr["type"] != "html")
		{
			return $this->_show_type($arr);
		}

		global $awt;

		// init left pane/right pane
		$this->left_pane = (isset($arr["no_left_pane"]) && $arr["no_left_pane"] == true) ? false : true;
		$this->right_pane = (isset($arr["no_right_pane"]) && $arr["no_right_pane"] == true) ? false : true;

		//print "aa";
		//flush();

		$this->_init_path_vars($arr);

		// figure out the menu that is active
		$this->sel_section = $this->_get_sel_section(aw_global_get("section"));
		if (aw_ini_get("config.object_translation"))
		{
			$ot = get_instance("translate/object_translation");
			$this->sel_section_real = $ot->get_original($this->sel_section);
		}
		else
		{
			$this->sel_section_real = $this->sel_section;
		}
		$this->sel_section_obj = obj($this->sel_section);

		$this->site_title = $this->sel_section_obj->name();
		
		// read the left/right pane props from the sel menu
		if (!$this->sel_section_obj->prop("left_pane"))
		{
			$this->left_pane = false;
		}

		if (!$this->sel_section_obj->prop("right_pane"))
		{
			$this->right_pane = false;
		}

		$this->do_check_properties(&$arr);

		$apd = get_instance("layout/active_page_data");
		$awt->start("xshow2");
		$rv = $this->do_show_template($arr);
		$awt->stop("xshow2");
		$rv .= $apd->on_shutdown_get_styles();
		return $rv;
	}

	function _show_type($arr)
	{
		switch($arr["type"])
		{
			case "rss":
				$rss = get_instance("output/xml/rss");
				return $rss->gen_rss_feed(array(
					"period" => aw_global_get("act_per_id"),
					"parent" => aw_global_get("section")
				));
		}
	}

	function import_class_vars($arr)
	{
		if (isset($arr["vars"]) && is_array($arr["vars"]))
		{
			$this->vars($arr["vars"]);
		}

		$request_uri = aw_ini_get("baseurl").aw_global_get("REQUEST_URI");
		$pos = strpos($request_uri, "&");
		$pos2 = strpos($request_uri, "set_lang_id");
		if ($pos === false && $pos2 === false)
		{
			$printlink = $request_uri . "?print=1";
		}
		else
		{
			$printlink = $request_uri . "&print=1";
		}

		$this->vars(array(
			"sel_menu_id" => $this->sel_section,
			"sel_menu_comment" => isset($arr["comment"]) ? $arr["comment"] : "",
			"site_title" => strip_tags($this->site_title),
			"printlink" => $printlink
		));
		
		// leat each class handle it's own variable import
		post_message("MSG_ON_SITE_SHOW_IMPORT_VARS", array(
			"inst" => &$this,
			"params" => $arr
		)); 
	}
	
	function _get_sel_section($sect)
	{
		$last_menu = 0;
		$cnt = count($this->path);
		for ($i = 0; $i < $cnt; $i++)
		{
			if ($this->path[$i]->class_id() == CL_MENU)
			{
				$last_menu = $this->path[$i]->id();
			}
		}
		return $last_menu;
	}

	////
	// !Fetches the menu chain for the current object from the menu cache for further use
	// XXX: this should be moved to core - because at least one other class - document
	// uses this to figure out whether a cfgform based template should be shown -- duke
	function build_menu_chain($section)
	{
		// we will this with properties from the first element in chain who
		// has thoses
		$this->properties = array(
			"tpl_dir"  => "", // prop!
			"users_only" => 0, // prop!
			"comment" => "", // prop!
			"tpl_view" => "", // prop!
			"tpl_lead" => "",// prop!
			"show_layout" => "",
			"ip_allowed" => array(),
			"ip_denied" => array(),
			"images" => array()
		);

		$ni = aw_ini_get("menuedit.num_menu_images");

		$cnt = count($this->path);
		for ($i = $cnt-1; $i > -1; $i--)
		{
			$obj = $this->path[$i];

			foreach($this->properties as $key => $val)
			{
				if ($key == "ip_allowed")
				{
					$tipa = $obj->meta("ip_allow");
					if (is_array($tipa) && count($tipa) > 0)
					{
						$this->properties[$key] = $tipa;
					}
				}
				else
				if ($key == "ip_denied")
				{
					$tipa = $obj->meta("ip_deny");
					if (is_array($tipa) && count($tipa) > 0)
					{
						$this->properties[$key] = $tipa;
					}
				}
				else
				if ($key == "images")
				{
					$im = $obj->meta("menu_images");
					for($imn = 0; $imn < $ni; $imn++)
					{
						if (!isset($this->properties["images"][$imn]) && is_oid($im[$imn]["image_id"]))
						{
							$this->properties["images"][$imn] = $im[$imn]["image_id"];
						}
					}
				}
				else
				{
					// check whether this object has any properties that 
					// none of the previous ones had
					if (empty($this->properties[$key]) && ($obj->class_id() == CL_MENU || $key == "users_only") && $obj->prop($key))
					{
						$this->properties[$key] = $obj->prop($key);
					}
				}
			}
		}
	}

	function do_check_properties(&$arr)
	{
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
		}

		// if the tpl_dir property is set, reinitialize the template class
		if (!isset($arr["tpldir"]))
		{
			$arr["tpldir"] = $this->properties["tpl_dir"];
		}

		// hook for site specific gen_site_html initialization
		// feel free to add other stuff here, but make sure this
		// stays _before_ the tpl_init below
		$si = __get_site_instance();
		if (is_object($si) && method_exists($si,"init_gen_site_html"))
		{
			$si->init_gen_site_html(array(
				"tpldir" => &$arr["tpldir"],
				"template" => &$arr["template"],
			));
		}

		if ($this->properties["comment"])
		{
			$arr["comment"] = $this->properties["comment"];
		}

		if (count($this->properties["ip_allowed"]) > 0 || count($this->properties["ip_denied"]))
		{
			$this->do_check_ip_access(array(
				"allowed" => $this->properties["ip_allowed"],
				"denied" => $this->properties["ip_denied"]
			));
		}
	}

	////
	// !checks if the current IP has access
	// parameters:
	//	allowed - array of addresses allowed
	//	denied - array of addresses denied
	//
	// algorithm:
	// if count(allowed) > 0 , then deny everything else, except allowed
	// if count(denied) > 0, then allow everyone, except denied
	function do_check_ip_access($arr)
	{
		extract($arr);
		$cur_ip = aw_global_get("REMOTE_ADDR");

		$ipa = get_instance("syslog/ipaddress");

		if (count($allowed) > 0)
		{
			$deny = true;
			$has_ip = false;
			foreach($allowed as $ipid => $t)
			{
				if (is_oid($ipid) && $this->can("view", $ipid))
				{
					$has_ip = true;
					$ipo = obj($ipid);

					if ($ipa->match($ipo->prop("addr"), $cur_ip))
					{
						$deny = false;
					}
				}
			}

			if ($deny && $has_ip)
			{
				$this->no_ip_access_redir($this->section_obj->id());
			}
			else
			{
				return;
			}
		}

		if (count($denied) > 0)
		{
			$deny = false;
			$has_ip = false;
			foreach($denied as $ipid => $t)
			{
				if (is_oid($ipid) && $this->can("view", $ipid))
				{
					$ipo = obj($ipid);
					$has_ip = true;

					if ($ipa->match($ipo->prop("addr"), $cur_ip))
					{
						$deny = true;
					}
				}
			}

			if ($deny && $has_ip)
			{
				$this->no_ip_access_redir($this->section_obj->id());
			}
			else
			{
				return;
			}
		}
	}

	////
	// !Checks whether the section or one of it's parents is marked as "users_only
	function users_only_redir()
	{
		$url = $this->get_cval("orb_err_mustlogin_".aw_global_get("LC"));
		if (!$url)
		{
			$url = $this->get_cval("orb_err_mustlogin");
		}
		aw_session_set("request_uri_before_auth",aw_global_get("REQUEST_URI"));
		header("Location: ".$this->cfg["baseurl"]."/$url");
		exit;
	}

	function do_show_layout($lid)
	{
		$li = get_instance("layout");
		return $li->show(array(
			"id" => $lid
		));
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

	function get_default_document($arr = array())
	{
		if (isset($arr["docid"]) && $arr["docid"])
		{
			return $arr["docid"];
		}

		if (isset($arr["obj"]))
		{
			$obj = $arr["obj"];
		}
		else
		{
			$obj = $this->section_obj;
		}

		if (!is_oid($obj->id()))
		{
			return false;
		}

		// if it is a document, use this one. 
		if (($obj->class_id() == CL_DOCUMENT) || ($obj->class_id() == CL_PERIODIC_SECTION) || $obj->class_id() == CL_BROTHER_DOCUMENT)
		{
			return $obj->id();	// most important not to change this, it is!
		}

		if ($obj->is_brother())
		{
			$obj = $obj->get_original();
		}


		// if any keywords for the menu are set, we must show all the documents that match those keywords under the menu
		if ($obj->meta("has_kwd_rels"))
		{
			$docid = array();

			$q = "
				SELECT distinct(keywordrelations.id) as id FROM keyword2menu
				LEFT JOIN keywordrelations ON keywordrelations.keyword_id = keyword2menu.keyword_id
				LEFT JOIN objects ON keywordrelations.id = objects.oid
				WHERE keyword2menu.menu_id = '".$obj->id()."' AND objects.status = 2";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$docid[] = $row["id"];
			}
			return $docid;
		}

		if ($docid > 0)
		{
			$check = obj($docid);
			$ok = $check->class_id() == CL_DOCUMENT && $check->status() == STAT_ACTIVE;
			if ($this->cfg["lang_menus"] == 1)
			{
				$ok &= $check->lang() == aw_global_get("LC");
			}
			if (!$ok)
			{
				$docid = 0;
			}
		}

		set_time_limit(0);

		$skipfirst = 0;

		$get_inact = false;

		$no_in_promo = false;

		// no default, show list
		if ($docid < 1)	
		{
			if ($obj->class_id() == CL_PROMO)
			{
				if ($obj->prop("show_inact") == 1)
				{
					$get_inact = true;
				}
				$skipfirst = $obj->prop("start_ndocs");
				$lm = $obj->meta("last_menus");

				$lm = array();
				foreach($obj->connections_from(array("type" => "RELTYPE_DOC_SOURCE")) as $c)
				{
					$lm[$c->prop("to")] = $c->prop("to");
				}

				$lm_sub = $obj->meta("src_submenus");
				if (is_array($lm) && count($lm) > 0 && ($lm[0] !== 0))
				{
					$sections = $lm;
					foreach($sections as $_sm)
					{
						if ($lm_sub[$_sm])
						{
							// include submenus in document sources
							$ot = new object_tree(array(
								"class_id" => CL_MENU,
								"parent" => $_sm,
								"status" => array(STAT_NOTACTIVE, STAT_ACTIVE),
								"sort_by" => "objects.parent"
							));
							$sections = $sections + $this->make_keys($ot->ids());
						}
					}
				}
				else
				{
					$sections = array($obj->id());
				};
				$no_in_promo = 1;
			}
			else
			{
				$gm_subs = $obj->meta("section_include_submenus");
				$gm_c = $obj->connections_from(array(
					"type" => 9 // RELTYPE_DOCS_FROM_MENU
				));
				foreach($gm_c as $gm)
				{
					$gm_id = $gm->prop("to");
					$sections[$gm_id] = $gm_id;
					if ($gm_subs[$gm_id])
					{
						$ot = new object_tree(array(
							"class_id" => CL_MENU,
							"parent" => $gm_id,
							"status" => array(STAT_NOTACTIVE, STAT_ACTIVE),
							"sort_by" => "objects.parent"
						));
						$sections += $this->make_keys($ot->ids());
					}
				}
			};

			if ($obj->meta("all_pers"))
			{
				$period_instance = get_instance("period");
				$periods = $this->make_keys(array_keys($period_instance->period_list(false)));
			}
			else
			{
				$periods = $obj->meta("pers");
			}

			$filter = array();

			if (is_array($sections) && ($sections[0] !== 0) && count($sections) > 0)
			{
				$nol = true;
				$filter["parent"] = $sections;
				if (aw_ini_get("config.use_last"))
				{
					$filter["no_last"] = new obj_predicate_not(1);
				}
			}
			else
			{
				$filter["parent"] = $obj->id();
			};
			if ($obj->prop("ndocs") > 0)
			{
				$filter["limit"] = $obj->prop("ndocs"); 
			};

			$docid = array();
			$cnt = 0;
			if ($ordby == "")
			{
				if ($obj->meta("sort_by") != "")
				{
					$ordby = $obj->meta("sort_by");
					if ($obj->meta("sort_ord") != "")
					{
						$ordby .= " ".$obj->meta("sort_ord");
					}
					if ($obj->meta("sort_by") == "documents.modified")
					{
						$ordby .= ", objects.created DESC";
					};
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

			if (strpos($ordby,"planner.start") !== false)
			{
				$filter[] = new object_list_filter(array(
					"non_filter_classes" => CL_DOCUMENT
				));
			}

			if ($get_inact)
			{
				$filter["status"] = array(STAT_ACTIVE,STAT_NOTACTIVE);
			}
			else
			{
				$filter["status"] = STAT_ACTIVE;
			}
			$filter["class_id"] = array(CL_DOCUMENT, CL_PERIODIC_SECTION, CL_BROTHER_DOCUMENT);
			$filter["lang_id"] = aw_global_get("lang_id");
			$filter["sort_by"] = $ordby;
			$filter["site_id"] = array();

			if ($arr["all_langs"])
			{
				$filter["lang_id"] = array();
			}

			if ($no_in_promo)
			{
				$filter["no_show_in_promo"] = new obj_predicate_not(1);
			}

			$documents = new object_list($filter);

			$rsid = aw_ini_get("site_id");
			
			$tc = 0;
			for($o = $documents->begin(); !$documents->end(); $o = $documents->next())
			{
				if ($o->site_id() != $rsid && !$o->is_brother())
				{
					continue;
				}
				if ($skipfirst > 0 && $tc < $skipfirst)
				{
					$tc++;
					continue;
				}


				if (!($no_fp_document && $o->prop("esilehel") == 1))
				{
					// oh. damn. this is sneaky. what if the brother is not active - we gits to check for that and if it is, then
					// use the brother
					if ($o->class_id() != CL_DOCUMENT && $this->can("view", $o->brother_of()))
					{
						$bo = obj($o->brother_of());
						if ($bo->status() != STAT_ACTIVE)
						{
							$docid[$cnt++] = $o->id();
						}
						else
						{
							$docid[$cnt++] = $o->brother_of();
						}
					}
					else
					{
						$docid[$cnt++] = $o->id();
					}
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

	function show_periodic_documents(&$arr)
	{
		if ($this->section_obj->class_id() == CL_PERIODIC_SECTION || $this->section_obj->class_id() == CL_DOCUMENT)
		{
			$docid = $this->section_obj->id();
		}
		else
		{
			$activeperiod = aw_global_get("act_per_id");
			$d = get_instance("document");
			$d->set_period($activeperiod);
			$d->list_docs($this->section_obj->id(), $activeperiod,2);
	
			if ($d->num_rows() > 1)
			{
				$docid = array();
				while($row = $d->db_next()) 
				{
					$docid[] = $row["docid"];
				}
			} 
			else 
			if ($d->num_rows() == 1)
			{
				$row = $d->db_next();
				$docid = $row["docid"];
			}
		}
		return isset($docid) ? $this->_int_show_documents($docid) : "";
	}

	function _int_show_documents($docid)
	{
		global $awt;
		$awt->start("int-show-doc");
		$d = get_instance("document");
		$d->set_opt("parent", $this->sel_section);
		$ct = "";

		// oleks vaja teha voimalus feedbacki tegemiseks. S.t. doku voib 
		// lisaks enda sisule tekitada veel mingeid datat, mida siis menuedit
		// voiks paigutada saidi raami sisse. Related links .. voi nimekiri
		// mingitest artiklis esinevatest asjadest. You name it.
		$blocks = array();

		$section_id = $this->section_obj->id();
		$tplmgr = get_instance("templatemgr");
		if (is_array($docid)) 
		{
			$template = $tplmgr->get_lead_template($section_id);
			
			// I need to  know that for the public method menus
			// christ, this sucks ass, we really should put that somewhere else! - terryf
			$d->set_opt("cnt_documents",sizeof($docid));
		
			$template = $template == "" ? "plain.tpl" : $template;
			$template2 = file_exists($this->cfg["tpldir"]."/automatweb/documents/".$template."2") ? $template."2" : $template;

			$this->vars(array("DOCUMENT_LIST" => $this->parse("DOCUMENT_LIST")));

			$_numdocs = count($docid);
			$_curdoc = 1;
			$no_strip_lead = aw_global_get("document.no_strip_lead");
			foreach($docid as $dk => $did)
			{
				// resets the template
				$d->_init_vars();

				$ct .= $d->gen_preview(array(
					"docid" => $did,
					"tpl" => ($dk & 1 ? $template2 : $template),
					"leadonly" => 1,
					"section" => $section_id,
					"strip_img" => false,
					"keywords" => 1,
					"no_strip_lead" => $no_strip_lead,
					"not_last_in_list" => ($_curdoc < $_numdocs)
				));
				$_curdoc++;
			}
		} 
		else 
		{
			$awt->start("get-long");
			$template = $tplmgr->get_long_template($section_id);
			$awt->stop("get-long");

			if ($docid)
			{
				$this->active_doc = $docid;
				$d->set_opt("cnt_documents",1);
				$d->set_opt("shown_document",$docid);

				$awt->start("gen-preview");
				$ct = $d->gen_preview(array(
					"docid" => $docid,
					"section" => $section_id,
					"no_strip_lead" => aw_ini_get("document.no_strip_lead"),
					"notitleimg" => 0,
					"tpl" => $template,
					"keywords" => 1,
					"boldlead" => aw_ini_get("document.boldlead"),
					"no_acl_checks" => aw_ini_get("menuedit.no_view_acl_checks"),
				));

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
					"section" => $section_id,
				));
				$this->vars(array(
					"PRINTANDSEND" => $this->parse("PRINTANDSEND")
				));

				if ($d->title != "")
				{
					$this->site_title = $d->title;
				}
				
				if (is_array($d->blocks))
				{
					$blocks = $blocks + $d->blocks;
				}
				$awt->stop("gen-preview");
			}
		}
		$awt->stop("int-show-doc");
		upd_instance("document",$d);

		$vars = array();
		if ( (is_array($blocks)) && (sizeof($blocks) > 0) )
		{
			while(list(,$blockdata) = each($blocks))
			{
				$this->vars(array(
					"title" => $blockdata["title"],
					"content" => $blockdata["content"],
				));
				$vars[$blockdata["template"]] .= $this->parse($blockdata["template"]);
			};
		};
		$this->vars($vars);

		return $ct;
	}

	function show_documents(&$arr)
	{
		// Vaatame, kas selle sektsiooni jaoks on "default" dokument
		if (!isset($arr["docid"]) || $arr["docid"] < 1) 
		{
			$docid = $this->get_default_document();
		}
		else
		{
			$docid = $arr["docid"];
		}

		return $this->_int_show_documents($docid);
	}

	function do_show_documents(&$arr)
	{
		if ($this->sel_section_obj->prop("periodic") && $arr["text"] == "")
		{
			$docc = $this->show_periodic_documents($arr);
		}
		else
		if ($arr["text"] == "")
		{
			$docc = $this->show_documents($arr);
		}
		else
		{
			$docc = $arr["text"];
		}

		if ($GLOBALS["real_no_menus"] == 1)
		{
			die($docc);
		}

		if ($this->sel_section_obj->prop("no_menus") == 1 || $GLOBALS["print"] || 1 == $arr["content_only"])
		{
			if (aw_ini_get("menuedit.print_template"))
			{
				$this->read_template(aw_ini_get("menuedit.print_template"));
				$this->vars(array(
					"doc_content" => $docc,
				));
				return $this->parse();
			}
			return $docc;
		}

		$this->vars(array(
			"doc_content" => $docc
		));
	}

	function do_menu_images()
	{
		$si_parent = $this->sel_section;
		$imgs = false;
		$smi = "";
		$sel_image = "";


		$cnt = count($this->path);
		for($i = $cnt-1; $i > -1; $i--)
		{
			$o = $this->path[$i];
			if ($o->prop("images_from_menu"))
			{
				$o = obj($o->prop("images_from_menu"));
			}

			if (is_array($o->meta("menu_images")) && count($o->meta("menu_images")) > 0)
			{
				$imgs = true;
				break;
			}

			$img_act_url = "";
			if ($o->meta("img_act"))
			{
				$img_act_url = $this->image->get_url_by_id($o->meta("img_act"));
			}

			if ($img_act_url == "" && $o->meta("img_act_url") != "")
			{
				$img_act_url = $o->meta("img_act_url");
			}
			
			if ($img_act_url != "")
			{
				$sel_image_url = image::check_url($img_act_url);
				$sel_image = "<img name='sel_menu_image' src='".$sel_image_url."' border='0'>";
				$this->vars(array(
					"url" => $sel_image_url
				));
				$smi .= $this->parse("SEL_MENU_IMAGE");
				break;
			}
		}

		if ($imgs)
		{
			$imgar = $o->meta("menu_images");
			$smi = "";
			foreach($imgar as $nr => $dat)
			{
				if ($dat["image_id"])
				{
					$dat["url"] = $this->image->get_url_by_id($dat["image_id"]);
				}

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
		$smn = $this->sel_section_obj->name();
		if (aw_ini_get("menuedit.strip_tags"))
		{
			$smn = strip_tags($smn);
		}

		$smn_nodoc = $smn;
		if ($this->active_doc)
		{
			$smn_nodoc = "";
		}

		$sel_menu_timing = 6;
		if ($imgs)
		{
			if ($this->sel_section_obj->meta("img_timing"))
			{
				$sel_menu_timing = $this->sel_section_obj->meta("img_timing");
			}
		}

		$this->vars(array(
			"SEL_MENU_IMAGE" => $smi,
			"sel_menu_name" => $smn,
			"sel_menu_name_no_doc" => $smn_nodoc,
			"sel_menu_image" => $sel_image,
			"sel_menu_image_url" => $sel_image_url,
			"sel_menu_o_img_url" => $sel_menu_o_img_url,
			"sel_menu_timing" => $sel_menu_timing
		));
	}

	////
	// !build "you are here" links from the path
	function make_yah()
	{
		$path = $this->path;

		$ya = "";
		$cnt = count($path);

		$this->title_yah = "";
		$alias_path = array();

		// this is used to make sure path starts at rootmenu+1 levels, to not show
		// "left menu" or similar in path
		$show = false;

		$prev = false;
		$show_obj_tree = false;

		$sfo = NULL;

		for ($i=0; $i < $cnt; $i++)
		{
			if (!aw_ini_get("menuedit.long_menu_aliases"))
			{
				$alias_path = array();
			}

			$ref = $path[$i];

			if ($ref->alias())
			{
				if (sizeof($alias_path) == 0)
				{
					$use_aliases = true;
				};

				if ($use_aliases)
				{
					array_push($alias_path,$ref->alias());
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
				$link = $this->cfg["baseurl"]."/".$ref->id();
			};

			if ($ref->prop("link") != "")
			{
				$link = $ref->prop("link");
			}

			if ($show_obj_tree)
			{
				$link = $ot_inst->get_yah_link($ot_id, $ref);
			}

			if (is_oid($sfo))
			{
				$sfo_o = obj($sfo);
				$sfo_i = $sfo_o->instance();
				$link = $sfo_i->make_menu_link($ref, $sfo_o);
			}

			if (is_oid($ref->prop("submenus_from_obj")) && $this->can("view", $ref->prop("submenus_from_obj")))
			{
				$sfo = $ref->prop("submenus_from_obj");
			}

			// now. if the object in the path is marked to use site tree as
			// the displayer, then get the link from that
			if ($ref->prop("show_object_tree"))
			{
				$show_obj_tree = true;
				$ot_inst = get_instance("contentmgmt/object_treeview");
				$ot_id = $ref->prop("show_object_tree");
			}

			$this->vars(array(
				"link" => $link,
				"text" => str_replace("&nbsp;"," ",strip_tags($ref->name())),
				"ysection" => $ref->id()
			));

			if (($ref->prop("clickable") == 1 || $ref->class_id() == CL_SHOP_PRODUCT) && $show)
			{
				if ($this->is_template("YAH_LINK_BEGIN") && $ya == "")
				{
					$ya .= $this->parse("YAH_LINK_BEGIN");
				}
				else
				if ($this->is_template("YAH_LINK_END") && $i == ($cnt-1))
				{
					$ya .= $this->parse("YAH_LINK_END");
				}
				else
				if ($this->is_template("YAH_LINK_REVERSE"))
				{
					$ya = $this->parse("YAH_LINK_REVERSE").$ya;
				}
				else
				{
					$ya .= $this->parse("YAH_LINK");
				}
				$this->title_yah.=" / ".$ref->name();
				$this->title_yah_arr[] = $ref->name();
			}

			if ($prev && $prev->id() == $this->cfg["rootmenu"])
			{
				$show = true;
			}
			$prev = $ref;
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

		if ($this->site_title == "")
		{
			$this->site_title = strip_tags($this->title_yah);
		}

		$this->vars(array(
			"YAH_LINK" => $ya,
			"YAH_LINK_END" => "",
			"YAH_LINK_BEGIN" => "",
			"YAH_LINK_REVERSE" => ""
		));

		if ($ya != "")
		{
			$this->vars(array(
				"HAS_YAH" => $this->parse("HAS_YAH")
			));
		}
	}

	function make_langs()
	{
		$lang_id = aw_global_get("lang_id");
		$langs = get_instance("languages");
		$lar = $langs->listall();
		$l = "";
		$uid = aw_global_get("uid");

		if (count($lar) < 2)
		{
			// crap, we need to insert the sel lang acharset here at least!
			$sel_lang = $langs->fetch(aw_global_get("lang_id"));
			$this->vars(array(
				"sel_charset" => $sel_lang["charset"],
				"charset" => $sel_lang["charset"],
				"se_lang_id" => $lang_id,
				"lang_code" => $sel_lang["acceptlang"]
			));
			return "";
		}		

		foreach($lar as $row)
		{
			$sel_img_url = "";
			$img_url = "";
			
			// if the language has an image
			if ($row["meta"]["lang_img"])
			{
				if ($lang_id == $row["id"] && $row["meta"]["lang_img_act"])
				{
					$sel_img_url = $this->image->get_url_by_id($row["meta"]["lang_img_act"]);
				}

				$img_url = $this->image->get_url_by_id($row["meta"]["lang_img"]);
			}

			$url = $this->cfg["baseurl"] . "/?set_lang_id=$row[id]";
			if ($row["meta"]["temp_redir_url"] != "" && $uid == "")
			{
				$url = $row["meta"]["temp_redir_url"];
			}
			$this->vars(array(
				"name" => $row["name"],
				"lang_id" => $row["id"],
				"lang_url" => $url,
				"link" => $url,
				"target" => "",
				"img_url" => $img_url,
				"sel_img_url" => $sel_img_url
			));
			if ($row["id"] == $lang_id)
			{
				if ($this->is_template("SEL_LANG_BEGIN") && $l == "")
				{
					$l.=$this->parse("SEL_LANG_BEGIN");
				}
				else
				{
					$l.=$this->parse("SEL_LANG");
				}
				$sel_lang = $row;
				$this->vars(array(
					"sel_lang_img_url" => $img_url,
					"sel_lang_sel_img_url" => $sel_img_url,
				));
			}
			else
			{
				if ($this->is_template("LANG_BEGIN") && $l == "")
				{
					$l.=$this->parse("LANG_BEGIN");
				}
				else
				{
					$l.=$this->parse("LANG");
				}
			}
		}

		if (!$sel_lang)
		{
			$ll = get_instance("languages");
			$sel_lang = $ll->fetch(aw_global_get("lang_id"));
		}
		$this->vars(array(
			"LANG" => $l,
			"SEL_LANG" => "",
			"SEL_LANG_BEGIN" => "",
			"LANG_BEGIN" => "",
			"sel_charset" => $sel_lang["charset"],
			"charset" => $sel_lang["charset"],
			"se_lang_id" => $lang_id,
			"lang_code" => $sel_lang["acceptlang"]
		));
	}

	////
	// !basically works like the above thingie .. but only shows languages
	// into which the current document has been translated to
	function make_context_langs()
	{
		if ($this->active_doc)
		{
			$obj = obj($this->active_doc);
		}
		else
		{
			$obj = $this->section_obj;
		}

		// make language id => acceptlang lut
		$l_inst = get_instance("languages");
		$lref = $l_inst->get_list(array(
			"key" => "id",
			"all_data" => true
		));

		// ok, if the obj has any connections FROM, of type RELTYPE_TRANSLATION
		// it is the original, so get all the relations and mark the translations
		// IF it does NOT, then find connection of type RELTYPE_ORIGINAL from the object
		// if there is ONE, then ot points to the original. 
		// if there are none, then the object is not translated
		$c = new connection();
		$conn = $c->find(array(
			"from" => $obj->id(),
			"type" => RELTYPE_TRANSLATION
		));
		if (count($conn) == 0)
		{
			$conn = $c->find(array(
				"from" => $obj->id(),
				"type" => RELTYPE_ORIGINAL
			));
			error::raise_if(count($conn) > 1, array(
				"id" => ERR_TRANS,
				"msg" => "site_show::make_context_langs(): found more than one RELTYPE_ORIGINAL translation from object ".$obj->id()
			));
			
			if (count($conn) == 1)
			{
				reset($conn);
				list(,$f_conn) = each($conn);
				$conn = $c->find(array(
					"from" => $f_conn["to"],
					"type" => RELTYPE_TRANSLATION
				));

				$orig_id = $f_conn["to"];
				$orig_lang = $lref[$f_conn["to.lang_id"]]["acceptlang"];
			}
			else
			{
				$conn = array();
				$orig_id = $obj->id();
				$orig_lang = $obj->lang();
			}
		}
		else
		{
			$orig_id = $obj->id();
			$orig_lang = $obj->lang();
		}

		// now $conn contains all the translation relations from the original obj to the translated objs
		$lang2trans = array(
			$orig_lang => $orig_id
		);
		foreach($conn as $c)
		{
			$lang2trans[$lref[$c["to.lang_id"]]["acceptlang"]] = $c["to"];
		}

		$lang_id = aw_global_get("LC");
		$l_inst->init_cache(true);
		$ldat = $l_inst->get_list(array(
			"key" => "acceptlang",
			"all_data" => true
		));
		$l = "";
		foreach($ldat as $lc => $ld)
		{
			$name = $ld["name"];
			$url = $this->cfg["baseurl"] . "/" . $lang2trans[$lc]."?set_lang_id=".$ld["id"];

			if (!$lang2trans[$lc])
			{
				$url = $this->mk_my_orb("show_trans", array("set_lang_id" => $ld["id"], "section" => $obj->id()), "object_translation");
			}

			$sel_img_url = "";
			$img_url = "";

			// if the language has an image
			if ($ld["meta"]["lang_img"])
			{
				if ($lc == $lang_id && $ld["meta"]["lang_img_act"])
				{
					$sel_img_url = $this->image->make_img_tag(
						$this->image->get_url_by_id($ld["meta"]["lang_img_act"]),
						$name
					);
				}

				$img_url = $this->image->make_img_tag(
					$this->image->get_url_by_id($ld["meta"]["lang_img"]),
					$name
				);
			}

			$this->vars(array(
				"name" => $name,
				"lang_url" => $url,
				"img_url" => $img_url,
				"sel_img_url" => $sel_img_url
			));

			if ($lc == $lang_id)
			{
				$l .= $this->parse("SEL_LANG");
				$this->vars(array(
					"sel_lang_img_url" => $img_url,
					"sel_lang_sel_img_url" => $sel_img_url,
				));
				$sel_lang = $ld;
			}
			else
			{
				$l.=$this->parse("LANG");
			}
		}

		$this->vars(array(
			"LANG" => $l,
			"SEL_LANG" => "",
			"sel_charset" => $sel_lang["charset"],
			"lang_fp_text" => $sel_lang["meta"]["fp_text"],
			"se_lang_id" => aw_global_get("lang_id")
		));
	}

	///////////////////////////////////////////////
	// template compiler runtime functions

	////
	// !finds the actual parent from the current path
	// based on the area parent and the menu level
	// this can assume that the level is in the path
	// because that is checked in OP_IF_VISIBLE
	// and we get here only if that returns true
	function _helper_find_parent($a_parent, $level)
	{
		if (!$this->can("view", $a_parent))
		{
			$a_parent = aw_ini_get("rootmenu");
		}

		if ($level == 1)
		{
			return $a_parent;
		}
		$pos = array_search($a_parent, $this->path_ids);
		return $this->path_ids[$pos+($level-1)];
	}

	function _helper_is_in_path($oid)
	{
		return array_search($oid, $this->path_ids);
	}

	////
	// !returns the number of levels that are in the path
	// for the menu area beginning at $parent
	function _helper_get_levels_in_path_for_area($parent)
	{
		// why is this here you ask? well, if the user has no access to the area rootmenu
		// then the rootmenu will get rewritten to the group's rootmenu, therefore
		// we need to rewrite it in the path checker functions as well
		if (!$this->can("view", $parent))
		{
			$parent = aw_ini_get("rootmenu");
		}

		$pos = array_search($parent, $this->path_ids);
		if ($pos === NULL || $pos === false)
		{
			return 0;
		}

		// now, the trick here is, of course that if the menu area parent is in the path
		// all the ones that follow, are also in that menu area so to return the level, 
		// we just count the number of things in the path after the start pos of the menu area
		return count($this->path) - ($pos+1);
	}

	function _helper_get_login_menu_id()
	{
		if (!$this->current_login_menu_id)
		{
			if (aw_global_get("uid") == "")
			{
				$this->current_login_menu_id = array_search("LOGGED", aw_ini_get("menuedit.menu_defs"));
			}
			else
			{
				$cfg = get_instance(CL_CONFIG_LOGIN_MENUS);
				$_id = $cfg->get_login_menus();
				if ($_id > 0)
				{
					$this->current_login_menu_id = $_id;
				}
				else
				{
					$this->current_login_menu_id = array_search("LOGGED", aw_ini_get("menuedit.menu_defs"));
				}
			}
		}
		return $this->current_login_menu_id;
	}

	function _helper_get_objlastmod()
	{
		static $last_mod;
		if (!$last_mod)
		{
			if (($last_mod = $this->cache->file_get("objlastmod")) === false)
			{
				$add = "";
				if (aw_ini_get("site_show.objlastmod_only_menu"))
				{
					$add = " WHERE class_id = ".CL_MENU;
				}
				$last_mod = $this->db_fetch_field("SELECT MAX(modified) as m FROM objects".$add, "m");
				$this->cache->file_set("objlastmod", $last_mod);
			}
			// also compiled menu template
			$last_mod = max($last_mod, @filemtime($this->compiled_filename));
		}
		return $last_mod;
	}

	function do_draw_menus($arr, $filename = NULL, $tpldir = NULL, $tpl = NULL)
	{
		if (aw_ini_get("config.menus_not_translated"))
		{
			obj_set_opt("no_auto_translation", 1);
		}

		if ($filename == NULL)
		{
			$filename = $this->compiled_filename;
		}
		else
		{
			$this->read_template("../../".$tpldir."/".$tpl,true);
		}
		
		if ($filename == "")
		{
			error::raise(array(
				"id" => ERR_NO_COMPILED,
				"msg" => "site_show::do_draw_menus(): no compiled filename set!"
			));
		}
	
		// fake paths for default menus
		$path_bak = $this->path_ids;

		$menu_defaults = aw_ini_get("menuedit.menu_defaults");
		if (aw_ini_get("menuedit.lang_defs"))
		{
			$menu_defaults = $menu_defaults[aw_global_get("lang_id")];
		}
		if (is_array($menu_defaults) && aw_global_get("section") == aw_ini_get("frontpage"))
		{
			foreach($menu_defaults as $_mar => $_mid)
			{
				if ($this->can("view", $_mid))
				{
					$tmp = obj($_mid);
					$this->path = $tmp->path();
					$this->path_ids = array();
					foreach($this->path as $p_obj)
					{
						$this->path_ids[] = $p_obj->id();
					}
				}
			}
		}

		enter_function("site_show::do_draw_menus");
		include_once($this->compiled_filename);
		exit_function("site_show::do_draw_menus");

		if (aw_ini_get("config.menus_not_translated"))
		{
			obj_set_opt("no_auto_translation", 0);
		}

		$this->path_ids = $path_bak;
		if ($filename !== NULL)
		{
			return $this->parse();
		}
	}

	function exec_subtemplate_handlers($arr)
	{
		// go over all class defs and check if that class is the handler for any subtemplates
		$promo_done = false;
		$tmp = aw_ini_get("classes");
		foreach($tmp as $clid => $cldef)
		{
			if ($cldef["subtpl_handler"] != "")
			{
				$handler_for = explode(",", $cldef["subtpl_handler"]);
				$ask_content = array();
				foreach($handler_for as $tpl)
				{
					if ($this->is_template($tpl))
					{
						$ask_content[] = $tpl;
					}
				}

				global $awt;
				$awt->start("mainc");

				if (count($ask_content) > 0)
				{
					$inst = get_instance($cldef["file"]);
					if ($cldef["file"] == "contentmgmt/promo")
					{
						$promo_done = true;
					}
					$fl = $cldef["file"];
					enter_function("mainc-$fl");
					if (!method_exists($inst, "on_get_subtemplate_content"))
					{
						error::raise(array(
							"id" => ERR_NO_SUBTPL_HANDLER,
							"msg" => "site_show::exec_subtemplate_handlers(): could not find subtemplate handler in ".$cldef["file"]
						));
					}
					$inst->on_get_subtemplate_content(array(
						"inst" => &$this,
						"content_for" => $ask_content,
						"request" => $_REQUEST
					));
					exit_function("mainc-$fl");
				}
				$awt->stop("mainc");
			}
		}

		if (!$promo_done)
		{
			// check if there are any promo templates, cause this call makes at least one query and this is faster
			// also, we don't need to check for the default promo templates
			// cause those are checked for earlier. 
			$pa = aw_ini_get("promo.areas");
			if (is_array($pa) && count($pa) > 0)
			{
				$has_tpl = false;
				foreach($pa as $pid => $pd)
				{
					if ($this->is_template($pd["def"]."_PROMO"))
					{
						$has_tpl = true;
					}
				}

				if ($has_tpl)
				{
					$awt->start("after-mainc-promo");
					$inst = get_instance("contentmgmt/promo");
					$inst->on_get_subtemplate_content(array(
						"inst" => &$this,
					));
					$awt->stop("after-mainc-promo");
				}
			}
		}
	}

	function is_in_path($s)
	{
		foreach($this->path as $o)
		{
			if ($o->id() == $s)
			{
				return true;
			}
		}
		return false;
	}

	function make_banners()
	{
		$banner_defs = aw_ini_get("menuedit.banners");

		if (!is_array($banner_defs))
		{
			return;
		}
		
		$banner_server = aw_ini_get("menuedit.banner_server");
		$ext = $this->cfg["ext"];
		$uid = aw_global_get("uid");

		reset($banner_defs);
		while (list($name,$gid) = each($banner_defs))
		{
			$htmlf = $banner_server."/banner.$ext?gid=$gid&ba_html=1";
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

			$this->vars(array(
				"banner_".$name => $fc
			));
		}
	}

	function make_final_vars()
	{
		$section = $this->section_obj->id();
		$frontpage = $this->cfg["frontpage"];

		// site_title_rev - shows two levels in reverse order
		$pcnt = count($this->title_yah_arr);
		$site_title_rev = ($pcnt > 0 ? strip_tags($this->title_yah_arr[$pcnt-1])." / " : "").($pcnt > 1 ? strip_tags($this->title_yah_arr[$pcnt-2])." / " : "");
		$site_title_yah = " / ". ($pcnt > 0 ? strip_tags($this->title_yah_arr[$pcnt-2])." / " : "").($pcnt > 1 ? strip_tags($this->title_yah_arr[$pcnt-1]) : "");

		$adt = "";
		if (is_oid($this->active_doc) && $this->can("view", $this->active_doc))
		{
			$adt_o = obj($this->active_doc);
			$adt = $adt_o->name();
		}

		$this->vars(array(
			"ss" => gen_uniq_id(),		// bannerite jaox
			"ss2" => gen_uniq_id(),
			"ss3" => gen_uniq_id(),
			"link" => "",
			"uid" => aw_global_get("uid"),
			"date" => $this->time2date(time(), 2),
			"date2" => $this->time2date(time(), 8),
			"date_timestamp" => time(),
			"date3" => date("d").". ".get_lc_month(date("n")).". ".date("Y"),
			"date4" => get_lc_weekday(date("w")).", ".get_lc_date(time(),LC_DATE_FORMAT_LONG_FULLYEAR),
			"date4_uc" => ucwords(get_lc_weekday(date("w"))).", ".get_lc_date(time(),LC_DATE_FORMAT_LONG_FULLYEAR),
			"IS_FRONTPAGE" => ($section == $frontpage ? $this->parse("IS_FRONTPAGE") : ""),
			"IS_FRONTPAGE2" => ($section == $frontpage ? $this->parse("IS_FRONTPAGE2") : ""),
			"IS_NOT_FRONTPAGE" => ($section != $frontpage ? $this->parse("IS_NOT_FRONTPAGE") : ""),
			"IS_NOT_FRONTPAGE2" => ($section != $frontpage ? $this->parse("IS_NOT_FRONTPAGE2") : ""),
			"site_title" => strip_tags($this->site_title),
			"site_title_rev" => $site_title_rev,
			"site_title_yah" => $site_title_yah,
			"active_document_title" => $adt,
			"current_period" => aw_global_get("current_period"),
			"cur_section" => aw_global_get("section")
		));

		// insert sel images
		foreach(safe_array($this->properties["images"]) as $nr => $id)
		{
			$url = $this->image->get_url_by_id($id);
			$this->vars(array(
				"path_menu_image_".$nr."_url" => $url,
				"path_menu_image_".$nr => html::img(array(
					"url" => $url
				))
			));
		}
		
		if (aw_global_get("uid") == "")
		{
			$this->vars(array(
				"login" => $this->parse("login"), 
				"login2" => $this->parse("login2"), 
				"login3" => $this->parse("login3"), 
				"logged" => "",
				"logged2" => "",
				"logged3" => "",
			));
			$cd_n = "";
			if ($this->active_doc)
			{
				$cd_n = $this->parse("CHANGEDOCUMENT_NOLOGIN");
			}
			$this->vars(array(
				"CHANGEDOCUMENT_NOLOGIN" => $cd_n
			));
		}
		else
		{
			if ($this->is_template("JOIN_FORM"))
			{
				error::raise(array(
					"id" => "ERR_JF",
					"msg" => "site_show::make_final_vars(): need JOIN_FORM sub back!"
				));
			}

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

			// check if template exists, cause prog_acl makes some queries
			if ($this->is_template("MENUEDIT_ACCESS"))
			{
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
			}

			// god dammit, this sucks. aga ma ei oska seda kuidagi teisiti lahendada
			// konkreetselt sonnenjetis on logged LEFT_PANE sees,
			// www.kirjastus.ee-s on LEFT_PANE logged-i sees.
			$lp = "";
			$rp = "";

			$this->vars(array(
				"logged" => $this->parse("logged"), 
				"logged1" => $this->parse("logged1"),
				"logged2" => $this->parse("logged2"),
				"logged3" => $this->parse("logged3"),
				"login" => "",
			));
		};

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

		if ($this->is_parent_tpl("RIGHT_PANE", "logged") && aw_global_get("uid") != "")
		{
			$this->vars(array(
				"logged" => $this->parse("logged")
			));
		}
	}

	// builds HTML popups
	function build_popups()
	{
		// that sucks. We really need to rewrite that
		// I mean we always read information about _all_ the popups
		$pl = new object_list(array(
			"status" => STAT_ACTIVE,
			"class_id" => CL_HTML_POPUP,
			"site_id" => array(),
		));
		if (count($pl->ids()) > 0)
		{
			$t = get_instance(CL_HTML_POPUP);
		};
		foreach($pl->arr() as $o)
		{
			$o_id = $o->id();
			if ($o->prop("only_once") && $_SESSION["popups_shown"][$o_id] == 1)
			{
				continue;
			}

			$sh = false;
			foreach($o->connections_from(array("type" => "RELTYPE_FOLDER")) as $c)
			{
				if ($c->prop("to") == $this->sel_section)
				{
					//$popups .= sprintf("window.open('%s','htpopup','top=0,left=0,toolbar=0,location=0,menubar=0,scrollbars=0,width=%s,height=%s');", $url, $o->meta("width"), $o->meta("height"));
					$popups .= $t->get_popup_data($o);
					$sh = true;
					$_SESSION["popups_shown"][$o_id] = 1;
				}
			}

			$inc_submenus = $o->meta("section_include_submenus");

			if (!$sh && is_array($inc_submenus) && count($inc_submenus) > 0)
			{
				$path = obj($this->sel_section);
				$path = $path->path();

				foreach($path as $p_o)
				{
					if ($inc_submenus[$p_o->parent()])
					{
						//$popups .= sprintf("window.open('%s','htpopup','top=0,left=0,toolbar=0,location=0,menubar=0,scrollbars=0,width=%s,height=%s');", $url, $o->meta("width"), $o->meta("height"));
						$popups .= $t->get_popup_data($o);
						$_SESSION["popups_shown"][$o_id] = 1;
					}
				}
			}
		}
		return $popups;
	}

	////
	// !Creates a link for the menu
	function make_menu_link($o)
	{
		$this->skip = false;
		if ($o->prop("type") == MN_PMETHOD)
		{
			// I should retrieve orb definitions for the requested class
			// to figure out which arguments it needs and then provide
			// those
			$pclass = $o->meta("pclass");
			if ($pclass)
			{
				list($_cl,$_act) = explode("/",$pclass);
				$orb = get_instance("orb");
				if ($_cl == "periods")
				{
					$_cl = "period";
				};
				$pobject = $o->meta("pobject");
				$pgroup = $o->meta("pgroup");
				$meth = $orb->get_public_method(array(
					"id" => $_cl,
					"action" => $_act,
					"obj" => (!empty($pobject) ? $pobject : false),
					"pgroup" =>  (!empty($pgroup) ? $pgroup : false),
				));
	
				// check acl
				if ($_act == "new" && !$this->can("add", aw_global_get("section")))
				{
					$this->skip = true;
				}
				if ($_act == "change" && !$this->can("edit", aw_global_get("section")))
				{
					$this->skip = true;
				}
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

				if ($_cl == "menu")
				{
					$values["parent"] = aw_global_get("section");
				}

				if (not($err))
				{
					//$_sec = aw_global_get("section");
					$_sec = $_REQUEST["section"];
					if ($_sec)
					{
						$values["section"] = $_sec;
					};
					$link = $this->mk_my_orb($_act,$values,($_cl == "document" ? "doc" : $_cl),$o->meta("pm_url_admin"),!$o->meta("pm_url_menus"));
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
		else if ($o->prop("link") != "")
		{
			$link = $o->prop("link");
		}
		else
		{
			$link = $this->cfg["baseurl"] ."/";
			if (aw_ini_get("menuedit.long_section_url"))
			{
				if ($o->alias() != "")
				{
					$link .= $o->alias();
				}
				else
				{
					if (aw_ini_get("menuedit.menuedit.show_real_location"))
					{
						$link .= "index.".$this->cfg["ext"]."?section=".$o->brother_of().$this->add_url;
					}
					else
					{
						$link .= "index.".$this->cfg["ext"]."?section=".$o->id().$this->add_url;
					}
				}
			}
			else
			{
				if ($o->alias() != "")
				{
					if (aw_ini_get("menuedit.long_menu_aliases"))
					{
						if (aw_ini_get("ini_rootmenu"))
						{
							$tmp = aw_ini_get("rootmenu");
							aw_ini_set("", "rootmenu", aw_ini_get("ini_rootmenu"));
						}
						$_p = $o->path();

						if (aw_ini_get("ini_rootmenu"))
						{
							aw_ini_set("", "rootmenu", $tmp);
						}
						$alp = array();
						foreach($_p as $p_o)
						{
							if ($p_o->alias() != "")
							{
								$alp[] = $p_o->alias();
							}
						}
						
						$link .= join("/",$alp);
						if (sizeof($tmp) > 0)
						{
							$link .= "/";
						};
					}
					else
					{
						//the alias seems to consist only of a space or two
						//i'm gonna show the id, if the strlen(trim(alias)) is 0
						if(!strlen(trim($o->alias())))
						{
							$link .= $o->id();
						}
						else
						{
							$link .= $o->alias();
						}
					}
				}
				else
				{
					$oid = ($o->class_id() == 39 || aw_ini_get("menuedit.show_real_location")) ? $o->brother_of() : $o->id();
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
			$exp = get_instance("export/export");
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
	// !returns the file where the generated code for the template is, if it is in the cache
	function get_cached_compiled_filename($arr)
	{
		$tpl = $arr["tpldir"]."/".$arr["template"];
		
		$what_to_replace = array('/','.','\\',':');
		$str_part = str_replace($what_to_replace, '_', $tpl);

		$fn = $this->cache->get_fqfn("compiled_menu_template-".$str_part."-".aw_global_get("lang_id"));
		if (@file_exists($fn) && @is_readable($fn) && @filectime($fn) > @filectime($tpl))
		{
			return $fn;
		}
		return false;
	}

	////
	// !compiles the template and saves the code in a cache file, returns the cache file
	function cache_compile_template($path, $tpl, $mdefs = NULL, $no_cache = false)
	{
		$co = get_instance("contentmgmt/site_template_compiler");
		$code = $co->compile($path, $tpl, $mdefs, $no_cache);
		$tpl = $path."/".$tpl;
		
		$what_to_replace = array("\\","/",".",":");
		$str_part = str_replace($what_to_replace, "_", $tpl);
		$fn = "compiled_menu_template-".$str_part."-".aw_global_get('lang_id');
		
		$ca = get_instance("cache");
		$ca->file_set($fn, $code);

		return $ca->get_fqfn($fn);
	}

	function do_show_template($arr)
	{
		global $awt;

		$tpldir = str_replace($this->cfg["site_basedir"]."/", "", $this->cfg["tpldir"])."/automatweb/menuedit";

		if (isset($arr["tpldir"]) && $arr["tpldir"] != "")
		{
			$this->tpl_init(sprintf("../%s/automatweb/menuedit",$arr["tpldir"]));
			$tpldir = $arr["tpldir"]."/automatweb/menuedit";;
		}


		$arr["tpldir"] = $tpldir;
		// right. now, do the template compiler bit
		$awt->start("build-popups");
		if (!($this->compiled_filename = $this->get_cached_compiled_filename($arr)))
		{
			$this->compiled_filename = $this->cache_compile_template($tpldir, $arr["template"]);
		}
		$awt->stop("build-popups");


		$this->read_template($arr["template"]);

		// import language constants
		lc_site_load("menuedit",$this);

		$this->do_sub_callbacks(isset($arr["sub_callbacks"]) ? $arr["sub_callbacks"] : array());

		$awt->start("do-show-template");
		if (($docc = $this->do_show_documents($arr)) != "")
		{
			return $docc;
		}

		$awt->stop("do-show-template");
		$this->import_class_vars($arr);

		// here we must find the menu image, if it is not specified for this menu,
		//then use the parent's and so on.
		$this->do_menu_images();
		$this->make_yah();

		if (aw_ini_get("menuedit.context_langs") == 1)
		{
			$this->make_context_langs();
		}
		else
		{
			$this->make_langs();
		};
		

		// execute menu drawing code
		$awt->start("part2");
		$this->do_draw_menus($arr);
		$awt->stop("part2");

		$awt->start("part3");
		$this->do_sub_callbacks(isset($arr["sub_callbacks"]) ? $arr["sub_callbacks"] : array(), true);
		$awt->stop("part3");

		$awt->start("part4");
		$this->exec_subtemplate_handlers($arr);
		$awt->stop("part4");

		$awt->start("part5");
		$this->make_banners();
		$awt->stop("part5");

		$awt->start("part6");
		$this->make_final_vars();		
		$awt->stop("part6");
	
		$rv = $this->parse();


		$rv .= $this->build_popups();
		return $rv;
	}
	
	function no_ip_access_redir($o)
	{
		die(t("Sellelt aadressilt pole lubatud seda lehte vaadata, vabandame.<br>Aadress: ".aw_global_get("REMOTE_ADDR")."<br>Leht: ".$o));
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

	function _init_path_vars(&$arr)
	{
		$this->section_obj = obj(aw_global_get("section"));

		$clss = aw_ini_get("classes");

		if ($this->section_obj->class_id() && isset($clss[$this->section_obj->class_id()]) && !$_GET["class"])
		{
			$obj_inst = $this->section_obj->instance();
			if (method_exists($obj_inst, "request_execute"))
			{
				$arr["text"] = $obj_inst->request_execute($this->section_obj);
			}
		}

		$content_from_obj = $this->section_obj->prop("get_content_from");
		if (is_oid($content_from_obj))
		{
			$content_obj = obj($content_from_obj);
			$content_obj_inst = $content_obj->instance();
			if (method_exists($content_obj_inst, "request_execute"))
			{
				$arr["text"] = $content_obj_inst->request_execute($content_obj);
			};

		};

		// until we can have class-static variables, this actually SETS current text content
		classload("layout/active_page_data");
		active_page_data::get_text_content(isset($arr["text"]) ? $arr["text"] : "");

		// save path
		// get path from the real rootmenu so we catch props?
		if (aw_ini_get("ini_rootmenu"))
		{
			$tmp = aw_ini_get("rootmenu");
			aw_ini_set("", "rootmenu", aw_ini_get("ini_rootmenu"));
		}

		//if (is_object($this->section_obj))
		if (is_oid($this->section_obj->id()))
		{
			$this->path = $this->section_obj->path();
		}
		else
		{
			$this->path = array();
		};

		if (aw_ini_get("ini_rootmenu"))
		{
			aw_ini_set("", "rootmenu", $tmp);
		}

		$pfp = aw_ini_get("shop.prod_fld_path");

		$this->path_ids = array();
		foreach($this->path as $p_obj)
		{
			$this->path_ids[] = $p_obj->id();
			if ($pfp && $p_obj->id() == $pfp && !aw_global_get("class"))
			{
				// uh-oh. we are in shop menu but not in shop mode. redirect
				$url = $this->mk_my_orb("show_items", array("section" => aw_global_get("section"), "id" => aw_ini_get("shop.prod_fld_path_oc")), "shop_order_center");
				header("Location: $url");
				die();
			}
		}
	}

	/** compiles and displays a template containing menu subs

		@comment
		
			parameters:
				template required - template with path, example: contentmgmt/foo/blah.tpl
				mdefs - optional -	if set, defines new menu areas for the template compiler. 
									format is the same as in the ini file

	**/
	function do_show_menu_template($arr)
	{
		extract($arr);
		if (!isset($mdefs))
		{
			$mdefs = NULL;
		}
		$this->_init_path_vars($arr);
		$tpl_dir = dirname($template);
		$tpl_fn = basename($template);

		$cname = $this->cache_compile_template($tpl_dir, $tpl_fn, $mdefs, true);
		$tmp = $this->do_draw_menus(array(), $cname, $tpl_dir, $tpl_fn);
		return $tmp;
	}
}
?>
