<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_show.aw,v 1.250 2007/11/22 15:10:48 markop Exp $

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
	var $brother_level_from;
	var $current_login_menu_id;
	var $title_yah_arr;

	var $cache;				// cache class instance

	var $image;				// image class instance

	function site_show()
	{
		$this->init("automatweb/menuedit");
		$this->cache = get_instance("cache");
		$this->image = get_instance(CL_IMAGE);
		$this->doc = get_instance(CL_DOCUMENT);
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

		if (!empty($_GET["set_doc_content_type"]))
		{
			$_SESSION["doc_content_type"] = $_GET["set_doc_content_type"];
		}
		if (!empty($_GET["clear_doc_content_type"]))
		{
			unset($_SESSION["doc_content_type"]);
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
		$this->sel_section_real = $this->sel_section;
		$this->sel_section_obj = obj($this->sel_section);

		$this->site_title = $this->sel_section_obj->trans_get_val("name");

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

		$p = get_instance("period");
		$p->on_site_show_import_vars(array("inst" => &$this));
		$p = get_instance(CL_SITE_STYLES);
		$p->on_site_show_import_vars(array("inst" => &$this));
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
			"images" => array(),
			"has_ctx" => 0
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
				if ($key == "tpl_view")
				{
					if ($i == 0 || !$obj->prop("tpl_view_no_inherit"))
					{
						$this->properties[$key] = $obj->prop($key);
					}
				}
				else
				if ($key == "tpl_lead")
				{
					if ($i == 0 || !$obj->prop("tpl_lead_no_inherit"))
					{
						$this->properties[$key] = $obj->prop($key);
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

		if ($this->sel_section_obj->prop("has_ctx"))
		{
			$use_ctx = NULL;
			if (count($_SESSION["menu_context"]))
			{
				$use_ctx = $_SESSION["menu_context"];
			}
			else
			if (is_oid($_ctx = $this->sel_section_obj->prop("default_ctx")) && $this->can("view", $_ctx))
			{
				$_ctx = obj($_ctx);
				$use_ctx = $_ctx->name();
			}
			if ($use_ctx)
			{
				// check if we need to redirect, based on current context
				// find the first submenu with the correct context
				$ol = new object_list(array(
					"parent" => $this->sel_section,
					"class_id" => CL_MENU,
					"CL_MENU.RELTYPE_CTX.name" => $use_ctx,
					"limit" => 1,
				));
				if (!$ol->count())
				{
					// get the first submenu
					$ol = new object_list(array(
						"class_id" => CL_MENU,
						"parent" => $this->sel_section,
						"sort_by" => "objects.jrk",
						"limit" => 1
					));
				}

				if ($ol->count())
				{
					$o = $ol->begin();
					header("Location: ".obj_link($o->id()));
					die();
				}
			}
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
		$li = get_instance(CL_LAYOUT);
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
						$sub_callbacks = $si->get_sub_callbacks_after($this);
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
		$docid = null;
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
		if ($obj->class_id() != CL_PROMO && ($obj->meta("has_kwd_rels") || !empty($_GET["set_kw"])))
		{
			// list all documents that have the same kwywords as this menu.
			// so first, get this menus keywords
			$m = get_instance(CL_MENU);
			if ($_GET["set_kw"])
			{
				$kwlist = array($_GET["set_kw"]);
			}
			else
			{
				$kwlist = $m->get_menu_keywords($obj->id());
			}
			$c = new connection();
			$doclist = $c->find(array(
				"to" => $kwlist,
			));
			$docid = array();
			$non_docid = array();
			foreach($doclist as $con)
			{
				if ($con["from.class_id"] == CL_DOCUMENT)
				{
					if ($con["from.status"] == STAT_ACTIVE && $con["reltype"] == 28)
					{
						$docid[$con["from"]] = $con["from"];
					}
				}
				else
				{
					$non_docid[$con["from"]] = $con["from"];
				}
			}

			if (count($non_docid))
			{
				// fetch docs connected to THOSE
				$doclist = $c->find(array(
					"from.class_id" => CL_DOCUMENT,
					"to" => $non_docid
				));
				foreach($doclist as $con)
				{
					if ($con["from.status"] == STAT_ACTIVE)
					{
						$docid[$con["from"]] = $con["from"];
					}
				}
			}

			// if we have doc ct type set, then filter by that as well16.04.2006
			if ($_SESSION["doc_content_type"] && count($docid) > 0)
			{
				$ol = new object_list(array(
					"class_id" => CL_DOCUMENT,
					"lang_id" => array(),
					"site_id" => array(),
					"oid" => $docid,
					"doc_content_type" => $_SESSION["doc_content_type"]
				));
				$docid = $this->make_keys($ol->ids());
			}

			if ($obj->prop("use_target_audience") == 1)
			{
				if (is_array($obj->prop("select_target_audience")) && count($obj->prop("select_target_audience")))
				{
					$ta_list = new object_list();
					$ta_list->add($obj->prop("select_target_audience"));
				}
				else
				{
					// get all current target audiences
					$ta_list = new object_list(array(
						"class_id" => CL_TARGET_AUDIENCE,
						"lang_id" => array(),
						"site_id" => array(),
						"ugroup" => aw_global_get("gidlist_oid")
					));
				}
				if ($ta_list->count() && count($docid))
				{
					$ol = new object_list(array(
						"class_id" => CL_DOCUMENT,
						"lang_id" => array(),
						"site_id" => array(),
						"oid" => $docid,
						"target_audience" => $ta_list->ids()
					));
					$docid = $this->make_keys($ol->ids());
				}
			}

			if (count($docid) == 1)
			{
				$docid = reset($docid);
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

		set_time_limit(14400);

		$skipfirst = 0;

		$get_inact = false;

		$no_in_promo = false;

		$filt_lang_id = aw_global_get("lang_id");
		$filter = array();
		// no default, show list
		if ($docid < 1)
		{
			if ($obj->prop("content_all_langs"))
			{
				$filt_lang_id = array();
			}

			if ($obj->class_id() == CL_PROMO)
			{
				if ($obj->prop("show_inact") == 1)
				{
					$get_inact = true;
				}
				$skipfirst = $obj->prop("start_ndocs");
				$lm = $obj->meta("last_menus");

				$lm = array();
				$ilm = array();
				foreach($obj->connections_from(array("type" => array(6,2))) as $c)	// doc source, doc ignore
				{
					if ($c->prop("reltype") == 6)
					{
						$ilm[$c->prop("to")] = $c->prop("to");
					}
					else
					{
						$lm[$c->prop("to")] = $c->prop("to");
					}
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

				foreach($ilm as $ilm_item)	// ilm contains menus that the user wants not to get docs from
				{
					unset($sections[$ilm_item]);
				}

				$no_in_promo = 1;

				// get kws from promo
				if ($this->can("view", $_GET["set_kw"]))
				{
					$filter["CL_DOCUMENT.RELTYPE_KEYWORD"] = $_GET["set_kw"];
				}
				else
				if ($obj->prop("use_menu_keywords") && $this->sel_section_obj)
				{
					//$promo_kws = $this->sel_section_obj->connections_from(array("to.class_id" => CL_KEYWORD, "type" => "RELTYPE_KEYWORD"));
					$mi = get_instance(CL_MENU);
					$kwns = $mi->get_menu_keywords($this->sel_section_obj->id());
				}
				else
				{
					$promo_kws = $obj->connections_from(array("to.class_id" => CL_KEYWORD, "type" => "RELTYPE_KEYWORD"));
					$kwns = array();
					foreach($promo_kws as $promo_kw)
					{
						$kwns[] = $promo_kw->prop("to");
					}
				}
				if (count($kwns))
				{
					// limit by objs with those kws
					$filter["CL_DOCUMENT.RELTYPE_KEYWORD"] = $kwns;
				}

				if ($obj->prop("use_doc_content_type") && $_SESSION["doc_content_type"])
				{
					$filter["doc_content_type"] = $_SESSION["doc_content_type"];
				}

			}
			else
			{
				$gm_subs = $obj->meta("section_include_submenus");
				$gm_c = $obj->connections_from(array(
					"type" => array("RELTYPE_DOCS_FROM_MENU","RELTYPE_NO_DOCS_FROM_MENU")
				));

				if (!empty($_SESSION["doc_content_type"]))
				{
					$filter["doc_content_type"] = $_SESSION["doc_content_type"];
				}

				foreach($gm_c as $gm)
				{
					if ($gm->prop("reltype") == 24)
					{
						continue;
					}
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

				$gm_subs = $obj->meta("section_no_include_submenus");
				foreach($gm_c as $gm)
				{
					if ($gm->prop("reltype") != 24)
					{
						continue;
					}
					$gm_id = $gm->prop("to");
					unset($sections[$gm_id]);
					if ($gm_subs[$gm_id])
					{
						$ot = new object_tree(array(
							"class_id" => CL_MENU,
							"parent" => $gm_id,
							"status" => array(STAT_NOTACTIVE, STAT_ACTIVE),
							"sort_by" => "objects.parent"
						));
						foreach($ot->ids() as $_id)
						{
							unset($sections[$_id]);
						}
					}
				}
			};

			if ($obj->meta("all_pers"))
			{
				$period_instance = get_instance(CL_PERIOD);
				$periods = $this->make_keys(array_keys($period_instance->period_list(false)));
			}
			else
			{
				$periods = $obj->meta("pers");
			}

			$has_rand = false;

			if (isset($sections) && is_array($sections) && ($sections[0] !== 0) && count($sections) > 0)
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
			}
			if ($obj->prop("ndocs") == -1)
			{
				$filter["oid"] = -1;
			}

			$docid = array();
			$cnt = 0;
			if (empty($ordby))
			{
				if ($obj->meta("sort_by") != "")
				{
					$ordby = $obj->meta("sort_by");
					if ($obj->meta("sort_by") == "RAND()")
					{
						$has_rand = true;
					}
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

				if ($obj->meta("sort_by2") != "")
				{
					if ($obj->meta("sort_by2") == "RAND()")
					{
						$has_rand = true;
					}
					$ordby .= ($ordby != "" ? " , " : " ").$obj->meta("sort_by2");
					if ($obj->meta("sort_ord2") != "")
					{
						$ordby .= " ".$obj->meta("sort_ord2");
					}
					if ($obj->meta("sort_by2") == "documents.modified")
					{
						$ordby .= ", objects.created DESC";
					};
				}

				if ($obj->meta("sort_by3") != "")
				{
					if ($obj->meta("sort_by3") == "RAND()")
					{
						$has_rand = true;
					}
					$ordby .= ($ordby != "" ? " , " : " ").$obj->meta("sort_by3");
					if ($obj->meta("sort_ord3") != "")
					{
						$ordby .= " ".$obj->meta("sort_ord3");
					}
					if ($obj->meta("sort_by3") == "documents.modified")
					{
						$ordby .= ", objects.created DESC";
					};
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

			// if we are in full content trans, then we need to get all documents
			// that are either active in original lang OR active in the current tr lang
			if ($get_inact || aw_ini_get("user_interface.full_content_trans"))
			{
				$filter["status"] = array(STAT_ACTIVE,STAT_NOTACTIVE);
			}
			else
			{
				$filter["status"] = STAT_ACTIVE;
			}

			$filter["class_id"] = array(CL_DOCUMENT, CL_PERIODIC_SECTION, CL_BROTHER_DOCUMENT);
			$filter["lang_id"] = $filt_lang_id;
			$filter["sort_by"] = $ordby;
			$filter["site_id"] = array();

			// if target audience is to be used, then limid docs by that
			if ($obj->prop("use_target_audience") == 1)
			{
				// get all current target audiences
				$ta_list = new object_list(array(
					"class_id" => CL_TARGET_AUDIENCE,
					"lang_id" => array(),
					"site_id" => array(),
					"ugroup" => aw_global_get("gidlist_oid")
				));
				if ($ta_list->count())
				{
					$filter["target_audience"] = $ta_list->ids();
				}
			}

			if (!empty($arr["all_langs"]))
			{
				$filter["lang_id"] = array();
			}

			if ($no_in_promo)
			{
				$filter["no_show_in_promo"] = new obj_predicate_not(1);
			}
			if ($obj->prop("auto_period") == 1)
			{
				$filter["period"] = aw_global_get("act_per_id");
			}
			if ($has_rand)
			{
				obj_set_opt("no_cache", 1);
			}

			if (is_array($arr["date_filter"]))
			{
				$df = $arr["date_filter"];
				if ($df["day"])
				{
					$s_tm = mktime(0, 0, 0, $df["month"], $df["day"], $df["year"]);
					$filter["doc_modified"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $s_tm, $s_tm+24*3600);
				}
				else
				if ($df["week"])
				{
					$s_tm = mktime(0, 0, 0, 1, 1, $df["year"]) + $df["week"] * 24*3600*7;
					$filter["doc_modified"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $s_tm, $s_tm+24*3600*7);
				}
				else
				if ($df["month"])
				{
					$s_tm = mktime(0, 0, 0, $df["month"], 1, $df["year"]);
					$e_tm = mktime(0, 0, 0, $df["month"]+1, 1, $df["year"]);
					$filter["doc_modified"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $s_tm, $e_tm);
				}
				else
				if ($df["year"])
				{
					$s_tm = mktime(0, 0, 0, 1, 1, $df["year"]);
					$e_tm = mktime(0, 0, 0, 1, 1, $df["year"]+1);
					$filter["doc_modified"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $s_tm, $e_tm);
				}
			}

			// what is going on here: we can't limit the list as the user said, if 
			// we are going to flter the list later, because it will get less items as requested
			// so, if we are not filtering the list, limit the query, else just read the ids
			// and count the objects that should be added manually. it seems the best
			// possible saolution, although it reads too many objects that might be inactive, but it is the only way I see
			if (!$get_inact && aw_ini_get("user_interface.full_content_trans"))
			{
				unset($filter["limit"]);
				$documents = new object_list($filter);
				$nd = $obj->prop("ndocs");
				// filter the list for both-inactive docs
				$doc_ol = new object_list();
				$_tmp_cnt = 0;
				foreach($documents->ids() as $__doc_id)
				{
					$__doc_o = obj($__doc_id);
					if ($__doc_o->status() == STAT_ACTIVE || $__doc_o->meta("trans_".aw_global_get("ct_lang_id")."_status"))
					{
						$doc_ol->add($__doc_o);
						if ($nd > 0 && ++$_tmp_cnt >= $nd)
						{
							break;
						}
					}
				}
				$documents = $doc_ol;
			}
			else
			{
				$documents = new object_list($filter);
			}

			if ($has_rand)
			{
				obj_set_opt("no_cache", 0);
			}

			$rsid = aw_ini_get("site_id");

			$tc = 0;
			$done_oids = array();
			foreach($documents->arr() as $o)
			{
				if ($done_oids[$o->brother_of()])
				{
					continue;
				}
				$done_oids[$o->brother_of()] = 1;
				if ($o->site_id() != $rsid && !$o->is_brother() && !aw_ini_get("menuedit.objects_from_other_sites"))
				{
					continue;
				}

				if (aw_ini_get("user_interface.hide_untranslated") && !$o->prop_is_translated("title"))
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
			$d = get_instance(CL_DOCUMENT);
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
		$d = get_instance(CL_DOCUMENT);
		$d->set_opt("parent", $this->sel_section);
		aw_register_default_class_member("document", "parent", $this->sel_section);
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
			aw_register_default_class_member("document", "cnt_documents", sizeof($docid));

			$template = $template == "" ? "plain.tpl" : $template;
			$template2 = file_exists($this->cfg["tpldir"]."/automatweb/documents/".$template."2") ? $template."2" : $template;

			$this->vars(array("DOCUMENT_LIST" => $this->parse("DOCUMENT_LIST")));
			$this->_is_in_document_list = 1;


			$_numdocs = count($docid);
			$_curdoc = 1;
			$no_strip_lead = aw_ini_get("document.no_strip_lead");

			foreach($docid as $dk => $did)
			{
				// resets the template
				$d->_init_vars();
				aw_global_set("shown_document", $did);
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

			if ($_GET["only_document_content"] && $_GET["templ"] != "")
                        {
                                $template = $_GET["templ"];
                        }
                        else
			{
				$template = $tplmgr->get_long_template($section_id);
			}
			$awt->stop("get-long");

			if ($docid)
			{
				// if full_content_trans is set, and this document is not translated
				// to the current ct lang and redirect is set, then do the redirect
				if (aw_ini_get("user_interface.full_content_trans") &&
					aw_ini_get("user_interface.ct_notact_redirect") != "")
				{
					$doc_o = obj($docid);
					if (aw_global_get("ct_lang_id") != $doc_o->lang_id() &&
						$doc_o->meta("trans_".aw_global_get("ct_lang_id")."_status") != 1)
					{
						header("Location: ".aw_ini_get("user_interface.ct_notact_redirect"));
						die();
					}
				}
				$this->active_doc = $docid;
				$d->set_opt("cnt_documents",1);
				aw_register_default_class_member("document", "cnt_documents", 1);
				$d->set_opt("shown_document",$docid);
				aw_register_default_class_member("document", "shown_document", $docid);
				aw_global_set("shown_document", $docid);

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
		$p = array();
		if ($_GET["year"] || $_GET["month"] || $_GET["day"] || $_GET["week"])
		{
			$p = array("date_filter" => array(
				"year" => $_GET["year"],
				"month" => $_GET["month"],
				"day" => $_GET["day"],
				"week" => $_GET["week"]
			));
		}

		// Vaatame, kas selle sektsiooni jaoks on "default" dokument
		if (!isset($arr["docid"]) || $arr["docid"] < 1)
		{
			$docid = $this->get_default_document($p);
		}
		else
		{
			$docid = $arr["docid"];
		}
		$si = __get_site_instance();
		if (method_exists($si, "handle_default_document_list"))
		{
			$si->handle_default_document_list($docid);
		}
		return $this->_int_show_documents($docid);
	}

	function do_show_documents(&$arr)
	{
		$disp = !empty($GLOBALS["real_no_menus"]) || !empty($_REQUEST["only_document_content"]) || ($this->sel_section_obj->prop("no_menus") == 1 || !empty($GLOBALS["print"]) || !empty($arr["content_only"]));

		if (!$disp)
		{
			enter_function("tpl_has_var_full");
			$disp |= $this->template_has_var_full("doc_content");
			exit_function("tpl_has_var_full");
		}

		if (!$disp)
		{
			return;
		}
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

		if (!empty($GLOBALS["real_no_menus"]))
		{
			die($docc);
		}

		if (!empty($_REQUEST["only_document_content"]))
		{
			$this->read_template("main_only_document_content.tpl");
			$this->vars(array(
				"doc_content" => $docc,
			));
			$this->do_sub_callbacks(isset($arr["sub_callbacks"]) ? $arr["sub_callbacks"] : array());
			return $this->parse();
		}

		if ($this->sel_section_obj->prop("no_menus") == 1 || !empty($GLOBALS["print"]) || !empty($arr["content_only"]))
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

		if ($docc == "")
		{
			$this->vars(array(
				"empty_doc_add_menu" => $this->_get_empty_doc_menu()
			));
		}

		$this->vars_safe(array(
			"doc_content" => $docc
		));

		if ($docc != "")
		{
			$this->vars(array(
				"HAS_DOC_CONTENT" => $this->parse("HAS_DOC_CONTENT")
			));
		}
		else
		{
			$this->vars(array(
				"NO_DOC_CONTENT" => $this->parse("NO_DOC_CONTENT")
			));
		}
	}

	function do_menu_images()
	{
		$si_parent = $this->sel_section;
		$imgs = false;
		$smi = "";
		$sel_image = "";
		$sel_image_url = "";
		$sel_image_link = "";
		$sel_menu_o_img_url = "";


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
				$tmp = obj($o->meta("img_act"));
				$sel_image_link = $tmp->prop("link");
				$this->vars(array(
					"url" => $sel_image_url
				));
				$smi .= $this->parse("SEL_MENU_IMAGE");
				break;
			}
		}

		$sius = array();
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

				if (empty($dat["url"]))
				{
					continue;
				}

				if ($smi == "")
				{
					$sel_image = "<img name='sel_menu_image' src='".image::check_url($dat["url"])."' border='0'>";
					$sel_image_url = $dat["url"];
					$tmp = obj($dat["image_id"]);
					$sel_image_link = $tmp->prop("link");
				}
				$this->vars(array(
					"url" => image::check_url($dat["url"])
				));
				$smi .= $this->parse("SEL_MENU_IMAGE");
				if ($dat["url"] != "")
				{
					$this->vars(array(
						"sel_menu_image_".$nr => "<img name='sel_menu_image_".$nr."' src='".$dat["url"]."' border='0'>",
						"sel_menu_image_".$nr."_url" => $dat["url"]
					));
				}
				$sius[$nr] = $dat["url"];
			}
		}
		$smn = $this->sel_section_obj->name();
		$smc = $this->sel_section_obj->comment();
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
			"sel_menu_image_link" => $sel_image_link,
			"sel_menu_comment" => $smc,
			"sel_menu_o_img_url" => $sel_menu_o_img_url,
			"sel_menu_timing" => $sel_menu_timing
		));

		if ($smc == "")
		{
			$this->vars(array(
				"HAS_SEL_MENU_COMMENT" => "",
				"NO_SEL_MENU_COMMENT" => $this->parse("NO_SEL_MENU_COMMENT")
			));
		}
		else
		{
			$this->vars(array(
				"HAS_SEL_MENU_COMMENT" => $this->parse("HAS_SEL_MENU_COMMENT"),
				"NO_SEL_MENU_COMMENT" => ""
			));
		}
		for($i = 0; $i < aw_ini_get("menuedit.num_menu_images"); $i++)
		{
			if (!empty($sius[$i]))
			{
				$this->vars(array(
					"HAS_SEL_MENU_IMAGE_URL_".($i) => $this->parse("HAS_SEL_MENU_IMAGE_URL_".($i))
				));
			}
			else
			{
				$this->vars(array(
					"NO_SEL_MENU_IMAGE_URL_".($i) => $this->parse("NO_SEL_MENU_IMAGE_URL_".($i))
				));
			}
		}

		$has_smu = $no_smu = "";
		if ($sel_image_url != "")
		{
			$has_smu = $this->parse("HAS_SEL_MENU_IMAGE_URL");
		}
		else
		{
			$no_smu = $this->parse("NO_SEL_MENU_IMAGE_URL");
		}
		$this->vars(array(
			"HAS_SEL_MENU_IMAGE_URL" => $has_smu,
			"NO_SEL_MENU_IMAGE_URL" => $no_smu
		));

		// menu img addon (sel_menu_image_skin_url)
		$ss = get_instance(CL_SITE_STYLES);
		$ol = new object_list(array(
			"class_id" => CL_SITE_STYLES,
			"status" => STAT_ACTIVE,
			"lang_id" => "%",
		));
		$ar = $ol->arr();
		$style_ord = $ss->selected_style_ord(array(
			"oid" => key($ar)
		));
		$obj = obj(key($ar));
		$menu_img_nrs = $obj->meta("menupic_nrs");
		$menupic_nr = $menu_img_nrs[$style_ord];
		$menu_pic_final_id = false;
		foreach(array_reverse($this->path) as $menu)
		{
			$menu_obj = obj($menu);
			$loop_menu_pics = $menu_obj->prop("menu_images");
			if($loop_menu_pics[$menupic_nr]["image_id"])
			{
				$menu_pic_final_id = $loop_menu_pics[$menupic_nr]["image_id"];
				break;
			}
		}
		if($menu_pic_final_id)
		{
			$pic_obj = obj($menu_pic_final_id);
			$img_inst = get_instance(CL_IMAGE);
			$this->vars(array(
				"sel_menu_image_skin_url" => $img_inst->get_url($pic_obj->prop("file")),
			));
			$this->parse("HAS_SEL_MENU_IMAGE_URL");
		}
		else
		{
			$this->parse("NO_SEL_MENU_IMAGE_URL");
		}
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

				if (aw_ini_get("user_interface.full_content_trans"))
				{
					$link = $this->cfg["baseurl"]."/".aw_global_get("ct_lang_lc")."/".$linktext;
				}
				else
				{
					$link = $this->cfg["baseurl"]."/".$linktext;
				}
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
				if (method_exists($sfo_i, "make_menu_link"))
				{
					$link = $sfo_i->make_menu_link($ref, $sfo_o);
				}
				else
				{
					$link = $this->make_menu_link($ref, $sfo_o);
				}
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
				$ot_inst = get_instance(CL_OBJECT_TREE);
				$ot_id = $ref->prop("show_object_tree");
			}
			$this->vars(array(
				"link" => $link,
				"text" => str_replace("&nbsp;"," ",strip_tags($ref->trans_get_val("name"))),
				"comment" =>  str_replace("&nbsp;"," ",strip_tags($ref->comment())),
				"ysection" => $ref->id(),
//				"end" => (!($i + 1 < $cnt)) ? $GLOBALS["yah_end"] : "",
			));

			$show_always = false;
			if ((($ref->class_id() == CL_MENU && $ref->prop("clickable") == 1) || $ref->class_id() == CL_DOCUMENT) && $show && $ref->class_id() != CL_DOCUMENT)
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
					$ya .= $this->parse("YAH_LINK");//.(!($i + 1 < $cnt)) ? $GLOBALS["yah_end"] : "";
				}
				$this->title_yah.=" / ".str_replace("&nbsp;"," ",strip_tags($ref->trans_get_val("name")));
				$this->title_yah_arr[] = str_replace("&nbsp;"," ",strip_tags($ref->trans_get_val("name")));
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
		if (!empty($GLOBALS["tbl_sk"]))
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

		$ya.=$GLOBALS["yah_end"];

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
		$var = "set_lang_id";
		if (aw_ini_get("user_interface.full_content_trans"))
		{
			$var = "set_ct_lang_id";
			$lang_id = aw_global_get("ct_lang_id");
		}
		$langs = get_instance("languages");
		$lar = $langs->listall();
		$l = array();
		$uid = aw_global_get("uid");
		if (count($lar) < 2)
		{
			// crap, we need to insert the sel lang acharset here at least!
			$sel_lang = $langs->fetch($lang_id);
			$this->vars(array(
				"sel_charset" => $sel_lang["charset"],
				"charset" => $sel_lang["charset"],
				"se_lang_id" => $lang_id,
				"lang_code" => $sel_lang["acceptlang"]
			));
			return "";
		}
		$num = 0;
		foreach($lar as $row)
		{
			if (is_oid($row["oid"]) && !$this->can("view", $row["oid"]))
			{
				continue;
			}

			$num++;
			$grp = $row["meta"]["lang_group"];
			$grp_spec = $grp;
			if ($grp != "")
			{
				$grp_spec = "_".$grp;
			}

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

			$url = $this->cfg["baseurl"] . "/?".$var."=$row[id]";
			if ($row["meta"]["temp_redir_url"] != "" && $uid == "")
			{
				$url = $row["meta"]["temp_redir_url"];
			}
			if (aw_ini_get("user_interface.full_content_trans"))
			{
				/*$url = get_ru();
				if (strpos($url, "/".aw_global_get("ct_lang_lc")."/") === false)
				{
					$url = aw_ini_get("baseurl")."/".$row["acceptlang"];
				}
				else
				{
					$url = str_replace("/".aw_global_get("ct_lang_lc")."/", "/".$row["acceptlang"]."/", $url);
				}*/
				if (substr($_GET["class"], 0, 4) == "shop")
				{
					$url = aw_url_change_var("section", $row["acceptlang"]."/".aw_global_get("section"));
				}
				else
				{
					$url = $this->make_menu_link($this->section_obj, $row["acceptlang"]);
				}
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
			if (!isset($l[$grp]))
			{
				$l[$grp] = "";
			}
			if ($row["id"] == $lang_id)
			{
				if ($num == count($lar) && $this->is_template("SEL_LANG".$grp_spec."_END"))
				{
					$l[$grp].=$this->parse("SEL_LANG".$grp_spec."_END");
				}
				else
				if ($this->is_template("SEL_LANG".$grp_spec."_BEGIN") && $l[$grp] == "")
				{
					$l[$grp].=$this->parse("SEL_LANG".$grp_spec."_BEGIN");
				}
				else
				{
					$l[$grp].=$this->parse("SEL_LANG".$grp_spec);
				}
				$sel_lang = $row;
				$this->vars(array(
					"sel_lang_img_url" => $img_url,
					"sel_lang_sel_img_url" => $sel_img_url,
				));
			}
			else
			{
				if ($num == count($lar) && $this->is_template("LANG".$grp_spec."_END"))
				{
					$l[$grp].=$this->parse("LANG".$grp_spec."_END");
				}
				else
				if ($this->is_template("LANG".$grp_spec."_BEGIN") && $l[$grp] == "")
				{
					$l[$grp].=$this->parse("LANG".$grp_spec."_BEGIN");
				}
				else
				{
					$l[$grp].=$this->parse("LANG".$grp_spec);
				}
			}
		}
		if (!$sel_lang)
		{
			$ll = get_instance("languages");
			$sel_lang = $ll->fetch(aw_global_get("lang_id"),true);
		}

		foreach($l as $_grp => $_l)
		{
			$app = ($_grp != "" ? "_".$_grp : "");
			$this->vars(array(
				"LANG".$app => $_l,
				"SEL_LANG".$app => "",
				"SEL_LANG".$app."_BEGIN" => "",
				"LANG".$app."_BEGIN" => ""
			));
		}
		$this->vars(array(
			"sel_charset" => $sel_lang["charset"],
			"charset" => $sel_lang["charset"],
			"se_lang_id" => $lang_id,
			"lang_code" => $sel_lang["acceptlang"]
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

		if($this->can("view" , $a_parent))
		{
			$parent_obj = obj($a_parent);
			if($parent_obj->class_id() == CL_MENU && $parent_obj->prop("submenus_from_cb"))
			{
				return $a_parent;
			}
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
		return array_search($oid, $this->path_ids) !== false;
	}

	function _helper_is_in_url($oid)
	{
		if($_GET["group"] != $_SESSION["menu_item_tab"] && $_SESSION["menu_item_tab"] != $_GET["openedtab"]) return false;
		return true;
	}
	////
	// !returns the number of levels that are in the path
	// for the menu area beginning at $parent
	function _helper_get_levels_in_path_for_area($parent)
	{//arr("_helper_get_levels_in_path_for_area");
		// why is this here you ask? well, if the user has no access to the area rootmenu
		// then the rootmenu will get rewritten to the group's rootmenu, therefore
		// we need to rewrite it in the path checker functions as well
		if (!$this->can("view", $parent))
		{
			$parent = aw_ini_get("rootmenu");
		}

		$pos = array_search($parent, $this->path_ids);

		//umm... peab miski valusa h�ki vahele kirjutama selle jaoks, kui men��st v�etakse omadus, et tabid tuleks adminniliidese tabidest
		if($this->can("view" , $parent))
		{
			$parent_obj = obj($parent);
			if($parent_obj->class_id() == CL_MENU && $parent_obj->prop("submenus_from_cb"))
			{
				return 1;
			}
		}

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
				"msg" => t("site_show::do_draw_menus(): no compiled filename set!")
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
		if (file_exists($this->compiled_filename))
		{
			include_once($this->compiled_filename);
		}
		exit_function("site_show::do_draw_menus");

		$this->path_ids = $path_bak;

		$this->do_seealso_items();


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
			if (!empty($cldef["subtpl_handler"]))
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
					if ($cldef["file"] == "contentmgmt/promo_display")
					{
						$promo_done = true;
					}
					$fl = $cldef["file"];
					enter_function("mainc-$fl");
					if (!method_exists($inst, "on_get_subtemplate_content"))
					{
						error::raise(array(
							"id" => ERR_NO_SUBTPL_HANDLER,
							"msg" => sprintf(t("site_show::exec_subtemplate_handlers(): could not find subtemplate handler in %s"), $cldef["file"])
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
					$inst = get_instance(CL_PROMO);
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
		$site_title_yah = " / ".join(" / ", safe_array($this->title_yah_arr));

		$adt = "";
		if (is_oid($this->active_doc) && $this->can("view", $this->active_doc))
		{
			$adt_o = obj($this->active_doc);
			$adt = $adt_o->name();
		}

		$u = get_instance(CL_USER);
		aw_disable_acl();
		$p = obj($u->get_current_person());
		aw_restore_acl();
		$this->vars(array(
			"ss" => gen_uniq_id(),		// bannerite jaox
			"ss2" => gen_uniq_id(),
			"ss3" => gen_uniq_id(),
			"link" => "",
			"uid" => aw_global_get("uid"),
			"user" => $p->name(),
			"date" => $this->time2date(time(), 2),
			"date2" => $this->time2date(time(), 8),
			"date_timestamp" => time(),
			"date3" => date("d").". ".get_lc_month(date("n"))." ".date("Y"),
			"date4" => get_lc_weekday(date("w")).", ".get_lc_date(time(),LC_DATE_FORMAT_LONG_FULLYEAR),
			"date4_uc" => ucwords(get_lc_weekday(date("w"))).", ".get_lc_date(time(),LC_DATE_FORMAT_LONG_FULLYEAR),
			"site_title" => strip_tags($this->site_title),
			"site_title_rev" => $site_title_rev,
			"site_title_yah" => $site_title_yah,
			"active_document_title" => $adt,
			"current_period" => aw_global_get("current_period"),
			"cur_section" => aw_global_get("section"),
			"section_name" => $this->section_obj->name(),
			"meta_description" => $this->section_obj->trans_get_val("description"),
			"meta_keywords" => $this->section_obj->trans_get_val("keywords"),
			"trans_lc" => aw_global_get("ct_lang_lc")
		));

		if ($this->_is_in_document_list)
		{
			$this->vars(array("DOCUMENT_LIST2" => $this->parse("DOCUMENT_LIST2")));
			$this->vars(array("DOCUMENT_LIST3" => $this->parse("DOCUMENT_LIST3")));
		}

		if ($this->is_parent_tpl("logged", "IS_NOT_FRONTPAGE"))
		{
			if (aw_global_get("uid") != "")
			{
				$this->vars(array(
					"logged" => $this->parse("logged")
				));
			}
		}

		// insert sel images
		foreach(safe_array($this->properties["images"]) as $nr => $id)
		{
			$url = $this->image->get_url_by_id($id);
			$this->vars(array(
				"path_menu_image_".$nr."_url" => $url,
				"path_menu_image_".$nr => html::img(array(
					"url" => $url,
					"alt" => " ",
					"title" => " "
				))
			));
		}

		$isfp = $section == $frontpage && empty($_GET["class"]);
		$this->vars_safe(array(
			"IS_FRONTPAGE" => ($isfp ? $this->parse("IS_FRONTPAGE") : ""),
			"IS_FRONTPAGE2" => ($isfp ? $this->parse("IS_FRONTPAGE2") : ""),
			"IS_NOT_FRONTPAGE" => (!$isfp ? $this->parse("IS_NOT_FRONTPAGE") : ""),
			"IS_NOT_FRONTPAGE2" => (!$isfp ? $this->parse("IS_NOT_FRONTPAGE2") : ""),
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
			if ($this->is_template("JOIN_FORM"))
			{
				error::raise(array(
					"id" => "ERR_JF",
					"msg" => t("site_show::make_final_vars(): need JOIN_FORM sub back!")
				));
			}

			$cd = $cd2 = "";
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

			$this->vars_safe(array(
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

		$this->vars_safe(array(
			"LEFT_PANE" => $lp,
			"RIGHT_PANE" => $rp,
			"NO_LEFT_PANE" => "",
			"NO_RIGHT_PANE" => ""
		));

		// check if logged is outside LEFT_PANE and if it is, then parse logged again if we are logged in
		if ($this->is_parent_tpl("LEFT_PANE", "logged") && aw_global_get("uid") != "")
		{
			$this->vars_safe(array(
				"logged" => $this->parse("logged")
			));
		}

		if ($this->is_parent_tpl("RIGHT_PANE", "logged") && aw_global_get("uid") != "")
		{
			$this->vars_safe(array(
				"logged" => $this->parse("logged")
			));
		}

		if ($this->active_doc)
		{
			$this->vars(array(
				"HAS_ACTIVE_DOC" => $this->parse("HAS_ACTIVE_DOC")
			));
		}
	}

	// builds HTML popups
	function build_popups()
	{
		if (true || $_GET["print"] == 1)
		{
			return;
		}
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
	function make_menu_link($o, $lc = null)
	{
		$this->skip = false;
		$link_str = $o->trans_get_val("link");
		if ($this->can("view", $o->meta("linked_obj")))
		{
			$linked_obj = obj($o->meta("linked_obj"));
			if ($linked_obj->class_id() == CL_MENU)
			{
				$link_str = $this->make_menu_link($linked_obj);
			}
			else
			{
				$dd = get_instance("doc_display");
				$link_str = $dd->get_doc_link($linked_obj);
			}
		}
		if ($o->prop("type") == MN_PMETHOD)
		{
			// I should retrieve orb definitions for the requested class
			// to figure out which arguments it needs and then provide
			// those
			$pclass = $o->meta("pclass");
			if ($pclass)
			{
				list($_cl,$_act) = explode("/",$pclass);
				$orb = get_instance("core/orb/orb");
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
				if ($_act == "new" && !$this->can("add", $meth["values"]["parent"]))
				{
					$this->skip = true;
				}
				if ($_act == "change" && !$this->can("edit", $meth["values"]["id"]))
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

				if ($o->meta("pm_extra_params") != "")
				{
					ob_start();
					eval("?>".$o->meta("pm_extra_params"));
					$link .= ob_get_contents();
					ob_end_clean();
				}
			}
			else
			{
				$link = "";
			};
		}
		else
		if (!$this->brother_level_from && $link_str != "")
		{
			$link = $link_str;
			if (is_numeric($link)) // link is without preceding /
			{
				$link = obj_link($link);
			}
		}
		else
		{
			if ($lc === null)
			{
				$lc = aw_global_get("ct_lang_lc");
				$use_trans = true;
			}
			else
			{
				$use_trans = false;
			}
			$link = $this->cfg["baseurl"] ."/";
			if (aw_ini_get("menuedit.language_in_url"))
			{
				$link .= $lc."/";
			}
			if (aw_ini_get("menuedit.long_section_url"))
			{
				if (($use_trans ? $o->trans_get_val("alias") : $o->alias()) != "")
				{
					$link .= ($use_trans ? $o->trans_get_val("alias") : $o->alias());
				}
				else
				{
					if (aw_ini_get("menuedit.show_real_location"))
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
				if (((!$this->brother_level_from && !$o->is_brother()) || aw_ini_get("menuedit.show_real_location"))&& ($use_trans ? $o->trans_get_val("alias") : $o->alias()) != "")
				{
					if (aw_ini_get("menuedit.long_menu_aliases"))
					{
						if (aw_ini_get("ini_rootmenu"))
						{
							$tmp = aw_ini_get("rootmenu");
							aw_ini_set("rootmenu", aw_ini_get("ini_rootmenu"));
						}
						$_p = $o->path();

						if (aw_ini_get("ini_rootmenu"))
						{
							aw_ini_set("rootmenu", $tmp);
						}
						$alp = array();
						foreach($_p as $p_o)
						{
							if (($use_trans ? $p_o->trans_get_val("alias") : $p_o->alias()) != "")
							{
								$alp[] = urlencode(($use_trans ? $p_o->trans_get_val("alias") : $p_o->alias()));
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
						if(!strlen(trim(($use_trans ? $o->trans_get_val("alias") : $o->alias()))))
						{
							$link .= $o->id();
						}
						else
						{
							$link .= urlencode(($use_trans ? $o->trans_get_val("alias") : $o->alias()));
						}
					}
				}
				else
				{
					if (($o->is_brother() || $this->brother_level_from) && !aw_ini_get("menuedit.show_real_location"))
					{
						$link .= "?section=".$o->id()."&path=".join(",", $this->_cur_menu_path);
					}
					else
					{
						$oid = ($o->class_id() == 39 || aw_ini_get("menuedit.show_real_location")) ? $o->brother_of() : $o->id();
						$link .= $oid;
					}
				};
			};
		}

		$sdct = $o->prop("set_doc_content_type");
		if ($this->can("view", $sdct))
		{
			$so = obj(aw_global_get("section"));
			$su = (aw_ini_get("frontpage") == aw_global_get("section") || $so->class_id() == CL_DOCUMENT  ? $link : aw_global_get("REQUEST_URI"));
			$su = aw_url_change_var("clear_doc_content_type", null, $su);
			$su = aw_url_change_var("docid", null, $su);
			$link = aw_url_change_var("set_doc_content_type", $sdct, $su);
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
		enter_function("do_sub_callbacks");
		$this->do_sub_callbacks(isset($arr["sub_callbacks"]) ? $arr["sub_callbacks"] : array());
		exit_function("do_sub_callbacks");

		$awt->start("do-show-template");

		if (($docc = $this->do_show_documents($arr)) != "")
		{
			$awt->stop("do-show-template");
			return $docc;
		}

		$awt->stop("do-show-template");
		$this->import_class_vars($arr);

		// here we must find the menu image, if it is not specified for this menu,
		//then use the parent's and so on.
		$this->do_menu_images();
		$this->make_yah();

		$this->make_langs();

		// execute menu drawing code
		$awt->start("part2");
		$this->do_draw_menus($arr);
		$awt->stop("part2");

		// repeated here, so you can use things both ways
		$this->do_menu_images();

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
					$this->raise_error(ERR_ACL_ERR,t("Access denied and error redirects are defined.incorrectly. Please report this to the site administrator"),1);
				};

			}
		}
		$this->raise_error(ERR_MNEDIT_NOACL,sprintf(t("No ACL error messages defined! no can_view access for object %s"), $section),true);
	}

	function _init_path_vars(&$arr)
	{
		if ($this->can("view",aw_global_get("section")))
		{
			$this->section_obj = obj(aw_global_get("section"));
		}
		else
		{
			$this->section_obj = new object();
		};

		$clss = aw_ini_get("classes");

		if ($this->section_obj->class_id() && isset($clss[$this->section_obj->class_id()]) && empty($_GET["class"]))
		{
			if ($this->section_obj->class_id() != CL_MENU) // menu is a large class and this is what it is 99% of the time and it has no handler. so don't load
			{
				$obj_inst = $this->section_obj->instance();
				if (method_exists($obj_inst, "request_execute"))
				{
					$arr["text"] = $obj_inst->request_execute($this->section_obj);
				}
			}
		}

		$content_from_obj = $this->section_obj->prop("get_content_from");
		if (is_oid($content_from_obj) && $this->can("view", $content_from_obj))
		{
			$content_obj = obj($content_from_obj);
			$content_obj_inst = $content_obj->instance();
			if (method_exists($content_obj_inst, "request_execute"))
			{
				$arr["text"] = $content_obj_inst->request_execute($content_obj);
			};

		}

		// until we can have class-static variables, this actually SETS current text content
		classload("layout/active_page_data");
		active_page_data::get_text_content(isset($arr["text"]) ? $arr["text"] : "");

		// save path
		// get path from the real rootmenu so we catch props?
		if (aw_ini_get("ini_rootmenu"))
		{
			$tmp = aw_ini_get("rootmenu");
			aw_ini_set("rootmenu", aw_ini_get("ini_rootmenu"));
		}

		//if (is_object($this->section_obj))
		if (!empty($_GET["path"]))
		{
			$p_ids = explode(",", $_GET["path"]);
			$this->path = array();
			foreach($p_ids as $p_id)
			{
				if ($this->can("view", $p_id))
				{
					$this->path[] = obj($p_id);
				}
			}
		}
		else
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
			aw_ini_set("rootmenu", $tmp);
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

	function do_seealso_items()
	{
		foreach(aw_ini_get("menuedit.menu_defs") as $id => $_name)
		{
			if (!$this->can("view", $id))
			{
				continue;
			}
			foreach(explode(",", $_name) as $name)
			{
				$tmp = array();
				$subtpl = "MENU_${name}_SEEALSO_ITEM";
				if (!$this->is_template($subtpl))
				{
					continue;
				}
				$o = obj($id);
				foreach($o->connections_to(array("type" => 5, "to.lang_id" => aw_global_get("lang_id"))) as $c)
				{
					$samenu = $c->from();
					if ($samenu->status() != STAT_ACTIVE || $samenu->lang_id() != aw_global_get("lang_id"))
					{
						continue;
					}

					$link = $this->make_menu_link($samenu);
					$ord = (int)$samenu->meta("seealso_order");

					// the jrk number is in $samenu["meta"]["seealso_order"]

					if (!($samenu->meta("users_only") == 1 && aw_global_get("uid") == ""))
					{
						$this->vars(array(
							"target" => $samenu->prop("target") ? "target=\"_blank\"" : "",
							"link" => $link,
							"text" => str_replace("&nbsp;", " ", $samenu->name()),
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
		}
	}

	function __helper_menu_edit($menu)
	{
		if (!$this->prog_acl() || !empty($_SESSION["no_display_site_editing"]))
		{
			return;
		}
		if (!$this->can("admin", $menu->id()) &&
			!$this->can("add", $menu->id()) &&
			!$this->can("edit", $menu->id())
		)
		{
			return;
		}
		$pm = get_instance("vcl/popup_menu");
		$pm->begin_menu("site_edit_".$menu->id());
		if ($this->can("add", $menu->parent()))
		{
			$url = $this->mk_my_orb("new", array("parent" => $menu->parent(), "ord_after" => $menu->id(), "return_url" => get_ru(), "is_sa" => 1), CL_MENU, true);
			$pm->add_item(array(
				"text" => t("Lisa uus k&otilde;rvale"),
				"oncl" => "onClick=\"aw_popup_scroll('$url', 'aw_doc_edit',600, 400)\"",
				"link" => "javascript:void(0)"
			));
		}

		if ($this->can("add", $menu->id()))
		{
			$url = $this->mk_my_orb("new", array("parent" => $menu->id(), "ord_after" => $menu->id(), "return_url" => get_ru(), "is_sa" => 1), CL_MENU, true);
			$pm->add_item(array(
				"text" => t("Lisa uus alamkaust"),
				"oncl" => "onClick=\"aw_popup_scroll('$url', 'aw_doc_edit',600, 400)\"",
				"link" => "javascript:void(0)"
			));
		}

		if ($this->can("change", $menu->id()))
		{
			$url = $this->mk_my_orb("change", array("id" => $menu->id(), "return_url" => get_ru(), "is_sa" => 1), CL_MENU, true);
			$pm->add_item(array(
				"text" => t("Muuda"),
				"oncl" => "onClick=\"aw_popup_scroll('$url', 'aw_doc_edit',600, 400)\"",
				"link" => "javascript:void(0)"
			));
		}

		if ($this->can("admin", $menu->id()))
		{
			$url = $this->mk_my_orb("disp_manager", array("id" => $menu->id()), "acl_manager", true);
			$pm->add_item(array(
				"text" => t("Muuda &otilde;igusi"),
				"oncl" => "onClick=\"aw_popup_scroll('$url', 'aw_doc_edit_acl',800, 500)\"",
				"link" => "javascript:void(0)"
			));
		}

		if ($this->can("edit", $menu->id()))
		{
			$pm->add_item(array(
				"text" => t("Peida"),
				"link" => $this->mk_my_orb("hide_menu", array("id" => $menu->id(), "ru" => get_ru()), "menu_site_admin")
			));
			$pm->add_item(array(
				"text" => t("L&otilde;ika"),
				"link" => $this->mk_my_orb("cut_menu", array("id" => $menu->id(), "ru" => get_ru()), "menu_site_admin")
			));
		}

		if (isset($_SESSION["site_admin"]["cut_menu"]) && $this->can("view", $_SESSION["site_admin"]["cut_menu"]))
		{
			$pm->add_item(array(
				"text" => t("Kleebi"),
				"link" => $this->mk_my_orb("paste_menu", array("after" => $menu->id(), "ru" => get_ru()), "menu_site_admin")
			));
		}
		return $pm->get_menu();
	}

	function _get_empty_doc_menu()
	{
		if (!$this->prog_acl() || !aw_ini_get("config.site_editing") || !empty($_SESSION["no_display_site_editing"]))
		{
			return;
		}
		$pm = get_instance("vcl/popup_menu");
		$pm->begin_menu("site_edit_new");

		if ($this->can("add", $this->sel_section))
		{
			$url = $this->mk_my_orb("new", array("parent" => $this->sel_section, "return_url" => get_ru(), "is_sa" => 1), CL_DOCUMENT, true);
			$pm->add_item(array(
				"text" => t("Lisa uus"),
				"oncl" => "onClick=\"aw_popup_scroll('$url', 'aw_doc_edit',800, 600)\"",
				"link" => "javascript:void(0)"
			));
		}
		else
		{
			return;
		}
		if (isset($_SESSION["site_admin"]["cut_doc"]) && $this->can("view", $_SESSION["site_admin"]["cut_doc"]))
		{
			$pm->add_item(array(
				"text" => t("Kleebi"),
				"link" => $this->mk_my_orb("paste_doc", array("ru" => get_ru()), "menu_site_admin")
			));
		}
		return $pm->get_menu();

	}
}
?>
