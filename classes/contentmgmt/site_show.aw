<?php

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

		// init left pane/right pane
		$this->left_pane = (isset($arr["no_left_pane"]) && $arr["no_left_pane"] == true) ? false : true;
		$this->right_pane = (isset($arr["no_right_pane"]) && $arr["no_right_pane"] == true) ? false : true;

		//print "aa";
		//flush();


		$this->section_obj = obj(aw_global_get("section"));

		if ($this->section_obj->class_id())
		{
			$obj_inst = $this->section_obj->instance();
			if (method_exists($obj_inst, "request_execute"))
			{
				$arr["text"] = $obj_inst->request_execute($this->section_obj);
			}
		}

		// until we can have class-static variables, this actually SETS current text content
		classload("layout/active_page_data");
		active_page_data::get_text_content(isset($arr["text"]) ? $arr["text"] : "");

		// save path
		$this->path = $this->section_obj->path();
		$this->path_ids = array();
		foreach($this->path as $p_obj)
		{
			$this->path_ids[] = $p_obj->id();
		}


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
		return $this->do_show_template($arr).$apd->on_shutdown_get_styles();
	}

	function show_type($arr)
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
		$this->vars(array(
			"sel_menu_id" => $this->sel_section,
			"sel_menu_comment" => isset($arr["comment"]) ? $arr["comment"] : "",
			"site_title" => strip_tags($this->site_title),
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
			"ip_denied" => array()
		);

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
				{
					// check whether this object has any properties that 
					// none of the previous ones had
					if (empty($this->properties[$key]) && $obj->class_id() == CL_MENU && $obj->prop($key))
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
			foreach($allowed as $ipid => $t)
			{
				$ipo = obj($ipid);

				if ($ipa->match($ipo->prop("addr"), $cur_ip))
				{
					$deny = false;
				}
			}

			if ($deny)
			{
				$this->no_access_redir($this->section_obj->id());
			}
			else
			{
				return;
			}
		}

		if (count($denied) > 0)
		{
			$deny = false;
			foreach($denied as $ipid => $t)
			{
				$ipo = obj($ipid);

				if ($ipa->match($ipo->prop("addr"), $cur_ip))
				{
					$deny = true;
				}
			}

			if ($deny)
			{
				$this->no_access_redir($this->section_obj->id());
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

		// if it is a document, use this one. 
		if (($obj->class_id() == CL_DOCUMENT) || ($obj->class_id() == CL_PERIODIC_SECTION))
		{
			return $obj->id();	// most important not to change this, it is!
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


		// no default, show list
		if ($docid < 1)	
		{
			if ($obj->class_id() == CL_PROMO)
			{
				$lm = $obj->meta("last_menus");
				$lm_sub = $obj->meta("src_submenus");
				if (is_array($lm) && ($lm[0] !== 0))
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
							$sections = $sections + $ot->ids();
						}
					}
				}
				else
				{
					$sections = array($obj->id());
				};
			}
			else
			{
				$gm_subs = $obj->meta("section_include_submenus");
				$gm_c = $obj->connections_from(array(
					"type" => RELTYPE_DOCS_FROM_MENU
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
						  $sections = $ot->ids();
						  /*
						$_sm_list = $this->get_menu_list(false, false, $gm_id);
						foreach($_sm_list as $_sm_i => $ttt)
						{
							$sections[$_sm_i] = $_sm_i;
						}
						*/
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
				$filter["parent"] = $sections;
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

			$filter["status"] = STAT_ACTIVE;
			$filter["class_id"] = array(CL_DOCUMENT, CL_PERIODIC_SECTION, CL_BROTHER_DOCUMENT);
			$filter["lang_id"] = aw_global_get("lang_id");
			$filter["sort_by"] = $ordby;

			$documents = new object_list($filter);

			for($o = $documents->begin(); !$documents->end(); $o = $documents->next())
			{
				if (!($no_fp_document && $o->prop("esilehel") == 1))
				{
					// oh. damn. this is sneaky. what if the brother is not active - we gits to check for that and if it is, then
					// use the brother
					if ($o->class_id() != CL_DOCUMENT)
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
		$d = get_instance("document");
		$d->set_opt("parent", $this->sel_section);
		$ct = "";

		// oleks vaja teha voimalus feedbacki tegemiseks. S.t. doku voib 
		// lisaks enda sisule tekitada veel mingeid datat, mida siis menuedit
		// voiks paigutada saidi raami sisse. Related links .. voi nimekiri
		// mingitest artiklis esinevatest asjadest. You name it.
		$blocks = array();

		if (is_array($docid)) 
		{
			$template = $this->get_lead_template($this->section_obj->id());
			
			// I need to  know that for the public method menus
			// christ, this sucks ass, we really should put that somewhere else! - terryf
			$d->set_opt("cnt_documents",sizeof($docid));
		
			$template = $template == "" ? "plain.tpl" : $template;
			$template2 = file_exists($this->cfg["tpldir"]."/automatweb/documents/".$template."2") ? $template."2" : $template;

			$this->vars(array("DOCUMENT_LIST" => $this->parse("DOCUMENT_LIST")));

			foreach($docid as $dk => $did)
			{
				$d->_init_vars();

				$ct .= $d->gen_preview(array(
					"docid" => $did,
					"tpl" => ($dk & 1 ? $template2 : $template),
					"leadonly" => 1,
					"section" => $this->section_obj->id(),
					"strip_img" => false,
					"keywords" => 1,
					"no_strip_lead" => aw_global_get("document.no_strip_lead")
				));
			}
		} 
		else 
		{
			$template = $this->get_long_template($this->section_obj->id());

			if ($docid)
			{
				$this->active_doc = $docid;
				$d->set_opt("cnt_documents",1);
				$d->set_opt("shown_document",$docid);

				$ct = $d->gen_preview(array(
					"docid" => $docid,
					"section" => $this->section_obj->id(),
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
					"section" => $this->section_obj->id(),
				));
				$this->vars(array(
					"PRINTANDSEND" => $this->parse("PRINTANDSEND")
				));

				if ($d->title != "")
				{
					$this->site_title = " / ".$d->title;
				}
				
				if (is_array($d->blocks))
				{
					$blocks = $blocks + $d->blocks;
				}
			}
		}
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

		if ($this->sel_section_obj->prop("no_menus") == 1 || $GLOBALS["print"])
		{
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
		$ya = "";
		$cnt = count($this->path);

		$this->title_yah = "";
		$alias_path = array();

		// this is used to make sure path starts at rootmenu+1 levels, to not show
		// "left menu" or similar in path
		$show = false;

		$prev = false;
		$show_obj_tree = false;

		for ($i=0; $i < $cnt; $i++)
		{
			if (!aw_ini_get("menuedit.long_menu_aliases"))
			{
				$alias_path = array();
			}

			$ref = $this->path[$i];

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

			if ($ref->prop("clickable") == 1 && $show)
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
		
		foreach($lar as $row)
		{
			$this->vars(array(
				"name" => $row["name"],
				"lang_id" => $row["id"],
				"lang_url" => $this->cfg["baseurl"] . "/?set_lang_id=$row[id]",
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
		$this->vars(array(
			"LANG" => $l,
			"SEL_LANG" => "",
			"SEL_LANG_BEGIN" => "",
			"LANG_BEGIN" => "",
			"sel_charset" => $sel_lang["charset"],
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

		// see if the object has translations
		$conn = $obj->connections_from(array(
			"type" => RELTYPE_TRANSLATION
		));
		if (count($conn) < 1)
		{
			// if it has none, then try to figure out if it is a translated object
			$conn = $obj->connections_to(array(
				"type" => RELTYPE_TRANSLATION
			));
			if (count($conn) > 0)
			{
				// if it has connections pointing to it, then it is, so get the translations from the original
				// we need to do this, because the previous query must ever only return 0 or 1 connections
				reset($conn);
				list(,$f_conn) = each($conn);
				$obj = obj($f_conn->prop("from"));
				$conn = $obj->connections_from(array(
					"type" => RELTYPE_TRANSLATION
				));
			}
		}

		$l_inst = get_instance("languages");
		$lref = $l_inst->get_list(array(
			"key" => "id",
			"all_data" => true
		));

		// now $conn contains all the translation relations from the original obj to the translated objs
		$lang2trans = array(
			$obj->lang() => $obj->id()
		);
		foreach($conn as $c)
		{
			$lang2trans[$lref[$c->prop("to.lang_id")]["acceptlang"]] = $c->prop("to");
		}

		$lang_id = aw_global_get("LC");
		$ldat = $l_inst->get_list(array(
			"key" => "acceptlang",
			"all_data" => true
		));
		$l = "";
		foreach($ldat as $lc => $ld)
		{
			if ($lc == aw_global_get("LC"))
			{
				continue;
			}

			$name = $ld["name"];
			$url = $this->cfg["baseurl"] . "/" . $lang2trans[$lc];

			if (!$lang2trans[$lc])
			{
				$url = $this->mk_my_orb("show_trans", array("set_lang_id" => $ld["id"], "section" => $obj->id()), "object_translation");
			}
		
			$this->vars(array(
				"name" => $name,
				"lang_url" => $url,
			));

			if ($lc == $lang_id)
			{
				$l .= $this->parse("SEL_LANG");
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
				$cfg = get_instance("config");
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
				$last_mod = $this->db_fetch_field("SELECT MAX(modified) as m FROM objects", "m");
				$this->cache->file_set("objlastmod", $last_mod);
			}
			// also compiled menu template
			$last_mod = max($last_mod, @filemtime($this->compiled_filename));
		}
		return $last_mod;
	}

	function do_draw_menus($arr)
	{
		if (!isset($this->compiled_filename) || $this->compiled_filename == "")
		{
			error::throw(array(
				"id" => ERR_NO_COMPILED,
				"msg" => "site_show::do_draw_menus(): no compiled filename set!"
			));
		}
	
		enter_function("site_show::do_draw_menus");
		include_once($this->compiled_filename);
		exit_function("site_show::do_draw_menus");
	}

	function exec_subtemplate_handlers($arr)
	{
		// go over all class defs and check if that class is the handler for any subtemplates
		foreach($this->cfg["classes"] as $clid => $cldef)
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

				if (count($ask_content) > 0)
				{
					$inst = get_instance($cldef["file"]);
					if (!method_exists($inst, "on_get_subtemplate_content"))
					{
						error::throw(array(
							"id" => ERR_NO_SUBTPL_HANDLER,
							"msg" => "site_show::exec_subtemplate_handlers(): could not find subtemplate handler in ".$cldef["file"]
						));
					}
					$inst->on_get_subtemplate_content(array(
						"inst" => &$this,
						"content_for" => $ask_content
					));
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

			$this->vars(array(
				"banner_".$name => $fc
			));
		}
	}

	function make_final_vars()
	{
		$section = $this->section_obj->id();
		$frontpage = $this->cfg["frontpage"];

		$this->vars(array(
			"ss" => gen_uniq_id(),		// bannerite jaox
			"ss2" => gen_uniq_id(),
			"ss3" => gen_uniq_id(),
			"link" => "",
			"uid" => aw_global_get("uid"),
			"date" => $this->time2date(time(), 2),
			"date2" => $this->time2date(time(), 8),
			"date3" => date("d").". ".get_lc_month(date("n")).". ".date("Y"),
			"IS_FRONTPAGE" => ($section == $frontpage ? $this->parse("IS_FRONTPAGE") : ""),
			"IS_FRONTPAGE2" => ($section == $frontpage ? $this->parse("IS_FRONTPAGE2") : ""),
			"IS_NOT_FRONTPAGE" => ($section != $frontpage ? $this->parse("IS_NOT_FRONTPAGE") : ""),
			"IS_NOT_FRONTPAGE2" => ($section != $frontpage ? $this->parse("IS_NOT_FRONTPAGE2") : ""),
			"site_title" => strip_tags($this->site_title),
		));

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
			"class_id" => CL_HTML_POPUP
		));
		for ($o = $pl->begin(); !$pl->end(); $o = $pl->next())
		{
			$ar = new aw_array($o->meta("menus"));
			foreach($ar->get() as $key => $val)
			{
				if ($val == $this->sel_section)
				{
					$popups .= sprintf("window.open('%s','popup','top=0,left=0,toolbar=0,location=0,menubar=0,scrollbars=0,width=%s,height=%s');", $o->meta("url"), $o->meta("width"), $o->meta("height"));
				}
			}
		}
		$retval = (strlen($popups) > 0) ? "<script language='Javascript'>$popups</script>" : "";
		return $retval;
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
					//$_sec = aw_global_get("section");
					$_sec = $_REQUEST["section"];
					if ($_sec)
					{
						$values["section"] = $_sec;
					};
					$link = $this->mk_my_orb($_act,$values,$_cl,$o->meta("pm_url_admin"),!$o->meta("pm_url_menus"));
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
					$link .= "index.".$this->cfg["ext"]."?section=".$o->id().$this->add_url;
				}
			}
			else
			{
				if ($o->alias() != "")
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
					$link .= $o->alias();
				}
				else
				{
					$oid = ($o->class_id() == 39) ? $o->brother_of() : $o->id();
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
		$fn = aw_ini_get("cache.page_cache")."/compiled_menu_template-".str_replace("/","_",str_replace(".","_",$tpl))."-".aw_global_get("lang_id");
		if (file_exists($fn) && is_readable($fn) && filectime($fn) > filectime($tpl))
		{
			return $fn;
		}
		return false;
	}

	////
	// !compiles the template and saves the code in a cache file, returns the cache file
	function cache_compile_template($path, $tpl)
	{
		$co = get_instance("contentmgmt/site_template_compiler");
		$code = $co->compile($path, $tpl);

		$tpl = $path."/".$tpl;
		$fn = "compiled_menu_template-".str_replace("/","_",str_replace(".","_",$tpl))."-".aw_global_get("lang_id");

		$ca = get_instance("cache");
		$ca->file_set($fn, $code);

		return aw_ini_get("cache.page_cache")."/".$fn;
	}

	function do_show_template($arr)
	{
		$tpldir = "../".str_replace($this->cfg["site_basedir"]."/", "", $this->cfg["tpldir"])."/automatweb/menuedit";

		if (isset($arr["tpldir"]) && $arr["tpldir"] != "")
		{
			$this->tpl_init(sprintf("../%s/automatweb/menuedit",$arr["tpldir"]));
			$tpldir = "../".$arr["tpldir"]."/automatweb/menuedit";;
		}

		$arr["tpldir"] = $tpldir;
		// right. now, do the template compiler bit
		if (!($this->compiled_filename = $this->get_cached_compiled_filename($arr)))
		{
			$this->compiled_filename = $this->cache_compile_template($tpldir, $arr["template"]);
		}


		$this->read_template($arr["template"]);

		// import language constants
		lc_site_load("menuedit",$this);

		$this->do_sub_callbacks(isset($arr["sub_callbacks"]) ? $arr["sub_callbacks"] : array());
		
		if (($docc = $this->do_show_documents($arr)) != "")
		{
			return $docc;
		}
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
		$this->do_draw_menus($arr);

		$this->do_sub_callbacks(isset($arr["sub_callbacks"]) ? $arr["sub_callbacks"] : array(), true);

		$this->exec_subtemplate_handlers($arr);

		$this->make_banners();

		$this->make_final_vars();		

		return $this->parse().$this->build_popups();
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
}
?>
