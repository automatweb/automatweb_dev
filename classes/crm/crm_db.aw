<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_db.aw,v 1.54 2009/01/22 15:59:24 instrumental Exp $
// crm_db.aw - CRM database
/*
@classinfo relationmgr=yes syslog_type=ST_CRM_DB maintainer=markop prop_cb=1
@default table=objects
@default group=general

@default field=meta
@default method=serialize

@property owner_org type=relpicker reltype=RELTYPE_OWNER_ORG store=connect
@caption Omanikorganisatsioon

@property selections type=relpicker reltype=RELTYPE_SELECTIONS group=general
@caption Vaikimisi valim

@property dir_firma type=relpicker reltype=RELTYPE_FIRMA_CAT multiple=1
@caption Ettev&otilde;tete kaust

@property folder_person type=relpicker reltype=RELTYPE_ISIK_CAT
@caption T&ouml;&ouml;tajate kaust

@property dir_address type=relpicker reltype=RELTYPE_ADDRESS_CAT
@caption Aadresside kaust

@property dir_ettevotlusvorm type=relpicker reltype=RELTYPE_ETTEVOTLUSVORM_CAT
@caption &Otilde;iguslike vormide kaust

@property dir_riik type=relpicker reltype=RELTYPE_RIIK_CAT
@caption Riikide kaust

@property dir_piirkond type=relpicker reltype=RELTYPE_PIIRKOND_CAT
@caption Piirkondade kaust

@property dir_maakond type=relpicker reltype=RELTYPE_MAAKOND_CAT
@caption Maakondade kaust

@property dir_linn type=relpicker reltype=RELTYPE_LINN_CAT
@caption Linnade kaust

@property dir_tegevusala type=relpicker multiple=1 reltype=RELTYPE_TEGEVUSALA_CAT
@caption Tegevusalade kaust

@property dir_toode type=relpicker reltype=RELTYPE_TOODE_CAT
@caption Toodete kaust

@property dir_default type=relpicker reltype=RELTYPE_GENERAL_CAT
@caption Kaust, kui m&otilde;ni eelnevatest pole m&auml;&auml;ratud, siis kasutatakse seda

@property flimit type=select
@caption Kirjeid &uuml;hel lehel

@property all_ct_data type=checkbox ch_value=1 
@caption Kuva k&otilde;iki kontaktandmeid

@property show_as_on_web type=checkbox ch_value=1
@caption Kuva tabelites ainult organisatsioone, mida kuvatakse veebis

-----------------------------------------------------------------------------
@groupinfo org caption=Kataloog submit=no
@default group=org
	
	@property org_tlb type=toolbar no_caption=1 store=no

	@layout o_main type=hbox width=20%:80%
		
		@layout o_left type=vbox parent=o_main
			
			@layout o_left_top type=vbox parent=o_left closeable=1 area_caption=Kataloogi&nbsp;puu

				@property org_tree type=treeview store=no no_caption=1 parent=o_left_top
			
			@layout o_left_bottom type=vbox parent=o_left closeable=1 area_caption=Otsi&nbsp;kataloogist

				@property os_name type=textbox store=no captionside=top parent=o_left_bottom
				@caption Organisatsiooni nimi

				@property os_submit type=submit store=no parent=o_left_bottom
				@caption Otsi

		@layout o_right type=vbox parent=o_main

			@property org_tbl type=table store=no no_caption=1 parent=o_right

@reltype SELECTIONS value=1 clid=CL_CRM_SELECTION
@caption Valimid

@reltype FIRMA_CAT value=2 clid=CL_MENU
@caption Organisatsioonide kaust

@reltype ISIK_CAT value=3 clid=CL_MENU
@caption T&ouml;&ouml;tajate kaust

@reltype ADDRESS_CAT value=4 clid=CL_MENU
@caption Aadresside kaust

@reltype LINN_CAT value=5 clid=CL_MENU
@caption Linnade kaust

@reltype MAAKOND_CAT value=6 clid=CL_MENU
@caption Maakondade kaust

@reltype RIIK_CAT value=7 clid=CL_MENU
@caption Riikide kaust

@reltype TEGEVUSALA_CAT value=8 clid=CL_MENU
@caption Tegevusalade kaust

@reltype TOODE_CAT value=9 clid=CL_MENU
@caption Toodete kataloogide kaust

@reltype GENERAL_CAT value=10 clid=CL_MENU
@caption &Uuml;ldkaust

@reltype CALENDAR value=11 clid=CL_PLANNER
@caption Kalender

@reltype ETTEVOTLUSVORM_CAT value=12 clid=CL_MENU
@caption &Otilde;iguslike vormide kaust

@reltype FORMS  value=13 clid=CL_CFGFORM
@caption Sisestusvormid

@reltype METAMGR value=14 clid=CL_METAMGR
@caption Muutujad

@reltype PIIRKOND_CAT value=15 clid=CL_MENU
@caption Piirkondade kaust

@reltype OWNER_ORG value=16 clid=CL_CRM_COMPANY
@caption Omanikorganisatsioon

*/

class crm_db extends class_base
{
	function crm_db()
	{
		$this->init(array(
			"clid" => CL_CRM_DB,
		));
	}	
		
	function get_property(&$arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "flimit":
				$prop["options"] = array (30 => 30, 60 => 60, 100 => 100);
				break;
		}
		return  $retval;
	}

	function _get_org_tree($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_only_one_level_opened(1);
		if(isset($_GET["branch_id"]))
		{
			$t->set_selected_item($_GET["branch_id"]);
		}

		$roots = array(
			"all" => t("K&otilde;ik organisatsioonid"),
			"on_web" => t("Kuvatavad organisatsioonid"),
			"not_on_web" => t("Mittekuvatavad organisatsioonid"),
		);
		foreach($roots as $k => $v)
		{
			$t->add_item(0, array(
				"id" => $k,
				"name" => $v,
				"url" => aw_url_change_var(array(
					"branch_id" => $k,
				))
			));
		}
		$t->add_item("on_web", array(
			"id" => "on_web_all",
			"name" => t("K&otilde;ik organisatsioonid"),
			"url" => aw_url_change_var(array(
				"branch_id" => "on_web_all",
			))
		));
		$t->add_item("not_on_web", array(
			"id" => "not_on_web_all",
			"name" => t("K&otilde;ik organisatsioonid"),
			"url" => aw_url_change_var(array(
				"branch_id" => "not_on_web_all",
			))
		));

		$item_count = array();
		$sa = new aw_array($arr["obj_inst"]->prop("dir_tegevusala"));
		$sectors_list = new object_list();
		foreach($sa->get() as $parent)
		{
			$menu_tree = new object_tree(array(
				"parent" => $parent,
				"class_id" => CL_CRM_SECTOR,
				"sort_by" => "objects.jrk,objects.name",
			));
			$sectors_list->add($menu_tree->to_list());
		}
		$ids = $this->make_keys($sectors_list->ids());
		foreach($sectors_list->arr() as $oid => $sect)
		{
			$org_count = $this->_get_retated_orgs($sect->id());
			$item_count[$sect->parent()] = $item_count[$sect->parent()] + $org_count;
			$item_count[$sect->id()] = $item_count[$sect->id()] + $org_count;
		}
		
		foreach($sectors_list->arr() as $oid => $sect)
		{
			$id = $sect->id();
			$parent = isset($ids[$sect->parent()]) ? $sect->parent() : 0 ;
			$name = $sect->name();
			$pm = get_instance("vcl/popup_menu");
			$pm->begin_menu("site_edit_".$id);
			$url = $this->mk_my_orb("change", array("id" => $id, "return_url" => get_ru(), "is_sa" => 1), CL_CRM_SECTOR, true);
			$pm->add_item(array(
				"text" => t("Muuda"),
				//"oncl" => "onClick=\"aw_popup_scroll('$url', 'aw_doc_edit',600, 400)\"",
				"link" => html::get_change_url($id, array("return_url" => get_ru()))//"javascript:void(0)"
			));
			$pm->add_item(array(
				"text" => t("Kustuta"),
				"link" => $this->mk_my_orb("delete_organizations", array("id" => $arr["obj_inst"]->id(), "sel[$id]" => $id, "post_ru" => get_ru())),
			));
			$name = $name." (".$item_count[$id].") ".$pm->get_menu();
			$t->add_item($parent, array(
				"id" => $id,
				"name" => $name,//strlen($name) > 20 ? substr($name, 0, 20).".." : $name,
				"url" => aw_url_change_var("branch_id", $sect->id()),
			));
		}
	}

	function _get_retated_orgs($s)
	{
		$org_list = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"lang_id" => array(),
			"site_id" => array(),
			"CL_CRM_COMPANY.RELTYPE_TEGEVUSALAD.id" => $s,
		));
		return $org_list->count();
	}

	function _init_company_table(&$t)
	{
		$t->set_default_sortby("fname");	
		$t->define_field(array(
			"name" => "org",
			"caption" => t("Organisatsioon"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "field",
			"caption" => t("P&otilde;hitegevus"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "ettevotlusvorm",
			"caption" => t("&Otilde;iguslik vorm"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
			"sortable" => 1,
		));	
		$t->define_field(array(
			"name" => "e_mail",
			"caption" => t("E-post"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "url",
			"caption" => t("WWW"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "phone",
			"caption" => t("Telefon"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "org_leader",
			"caption" => t("Juht"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "cr_manager",
			"caption" => t("Kliendihaldur"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "changed",
			"caption" => t("Muudetud"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function _get_org_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_company_table(&$t);

		$all_letters = array(); //array("0-9");
		foreach(range("A", "Z") as $v)
		{
			$all_letters[] = $v;
		}
		// Although I don't think this is any better...
		$let = array(html_entity_decode("&Ouml;"), html_entity_decode("&Auml;"), html_entity_decode("&Uuml;"), html_entity_decode("&Otilde;"));
		foreach($let as $l)
		{
			$all_letters[] = $l;
		}
		$letter = isset($arr["request"]["letter"]) ? urldecode($arr["request"]["letter"]) : "A";

		$perpage = 20;
		if ($arr["obj_inst"]->prop("flimit") != "")
		{
			$perpage = $arr["obj_inst"]->prop("flimit");
		};

		$pageselector = "";
		$id = $arr["obj_inst"]->id();

		

		$vars = array(
			"parent" => $arr["obj_inst"]->prop("dir_firma"),
			"class_id" => CL_CRM_COMPANY,
			"sort_by" => "objects.jrk,objects.name",
		);
		if($arr["request"]["group"] == "firmad")
		{
			$vars["name"] = $letter."%";
			foreach($all_letters as $val)
			{
				$pageselector .= "&nbsp;&nbsp;".html::get_change_url($id, array(
						"group" => "firmad",
						//"ft_page" => $arr["request"]["ft_page"],
						"letter" => urlencode($val),
						"return_url" => get_ru(),
						"no_search" => 1,
				), ($val == $letter ? "<b>".$val."</b>" : $val));
			}
		}
		if(isset($_GET["branch_id"]) && $this->can("view", $_GET["branch_id"]))
		{
			$vars["pohitegevus"] = $_GET["branch_id"];
		}
		
		elseif($arr["request"]["group"] == "tegevusalad" && !$this->can("view", $_GET["branch_id"]))
		{
			return;
			$vars["CL_CRM_COMPANY.pohitegevus(CL_CRM_SECTOR).name"] = new obj_predicate_compare(OBJ_COMP_NULL); 
		}

		$companys = new object_list($vars);
		$t->d_row_cnt = $companys->count();
		$ps = "";
		if ($t->d_row_cnt > $perpage)
		{
			$ps .= $t->draw_text_pageselector(array(
				"records_per_page" => $perpage
			));
		};
		$ft_page = isset($arr["request"]["ft_page"]) ? (int)$arr["request"]["ft_page"] : 0;
		$vars["limit"] = (60*$ft_page).",".(60*$ft_page+60);
		$t->set_header($pageselector."<br />".$ps);
		if($arr["obj_inst"]->show_as_on_web && $companys->count() > 0 && is_oid($arr["obj_inst"]->owner_org))
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
				"buyer" => $companys->ids(),
				"seller" => $arr["obj_inst"]->owner_org,
				"show_in_webview" => 1,
				"lang_id" => array(),
				"site_id" => array(),
			));
			if($ol->count() > 0)
			{
				// START - this should be done with RELTYPE_BUYER(CL_CRM_COMPANY_CUSTOMER_DATA).oid, but that doesn't seem to work. -kaarel 11.01.2009
				$conns = connection::find(array(
					"from.class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
					"from" => $ol->ids(),
					"reltype" => "RELTYPE_BUYER",
					"to" => $companys->ids(),
				));
				$ids = array();
				if(count($conns) > 0)
				{
					foreach($conns as $conn)
					{
						$ids[] = $conn["to"];
					}
					$vars["oid"] = $arr["request"]["group"] == "not_on_web" ? new obj_predicate_not($ids) : $ids;
					// END
//					$vars["RELTYPE_BUYER(CL_CRM_COMPANY_CUSTOMER_DATA).oid"] = new obj_predicate_not($ol->ids());
				}
				$companys = count($ids) > 0 || $arr["request"]["group"] == "not_on_web" ? new object_list($vars) : new object_list();
			}
			else
			{
				$companys = new object_list();
			}
		}
		else
		{
			$companys = new object_list($vars);
		}
		$coms = $companys->arr();
		foreach($coms as $com)
		{
			$ol = $com->prop("firmajuht");
			$org_leader = "";
			if($this->can("view", $ol))
			{
				$obj = obj($ol);
				$org_leader = html::get_change_url($ol, array("return_url" => get_ru()), $obj->name());
			}
			$cr_manager = "";
			$crm = $com->prop("client_manager.name");
			if(strlen($crm) > 0)
			{
				$cr_manager = html::get_change_url($com->prop("client_manager"), array("return_url" => get_ru()), $com->prop("client_manager.name"));
			}

			if (!$arr["obj_inst"]->prop("all_ct_data") && $this->can("view", $com->prop("email_id")))
			{
				$eml = $com->prop("email_id.mail");
			}
			else
			{
				$phc = $com->connections_from(array("type" => "RELTYPE_EMAIL"));
				$pha = array();
				foreach($phc as $ph_con)
				{
					$ph_o = $ph_con->to();
					$pha[] = $ph_o->prop("mail");
				}
				$eml = join(", ", $pha);
			}

			if (!$arr["obj_inst"]->prop("all_ct_data") && $this->can("view", $com->prop("phone_id")))
			{
				$phs = $com->prop("phone_id.name");
			}
			else
			{
				$phc = $com->connections_from(array("type" => "RELTYPE_PHONE"));
				$pha = array();
				foreach($phc as $ph_con)
				{
					$pha[] = $ph_con->prop("to.name");
				}
				$phs = join(", ", $pha);
			}
			
			if (!$arr["obj_inst"]->prop("all_ct_data") && $this->can("view", $com->prop("url_id")))
			{
				$url = $com->prop("url_id.name");
				$url = substr($url, strpos($url, "http://"), strlen($url)+1);
				if(strlen($url) > 0)
				{
					$url = html::href(array("url" => $url, "caption" => $url, "target" => "_blank"));
				}
			}
			else
			{
				$phc = $com->connections_from(array("type" => "RELTYPE_URL"));
				$pha = array();
				foreach($phc as $ph_con)
				{
					$tu = $ph_con->prop("to.name");
					$tu = substr($tu, strpos($tu, "http://"), strlen($tu)+1);
					if(strlen($tu) > 0)
					{
						$tu = html::href(array("url" => $tu, "caption" => $tu, "target" => "_blank"));
					}
					$pha[] = $tu;
				}
				$url = join(", ", $pha);
			}

			if (!$arr["obj_inst"]->prop("all_ct_data") && $this->can("view", $com->prop("contact")))
			{
				$cts = $com->prop("contact.name");
			}
			else
			{
				$phc = $com->connections_from(array("type" => "RELTYPE_ADDRESS"));
				$pha = array();
				foreach($phc as $ph_con)
				{
					$pha[] = $ph_con->prop("to.name");
				}
				$cts = join(", ", $pha);
			}
			
			$t->define_data(array(
				"id" => $com->id(),
				"org" => html::get_change_url($com->id(), array("return_url" => get_ru()), strlen($com->name()) ? $com->name() : t("(nimetu)")),
				"field" => $com->prop_str("pohitegevus"),
				"ettevotlusvorm" => $com->prop_str("ettevotlusvorm"),
				"address" => $cts,
				"e_mail" => $eml,
				"url" => $url,
				"phone" => $phs,
				"org_leader" => $org_leader,
				"cr_manager" => $cr_manager,
				"changed" => date("Y.m.d H:i" , $com->modified()),
				"created" => date("Y.m.d H:i" , $com->created()),
			));
		}
		if (!isset($_GET["sortby"]))
		{
			$t->set_sortable(false);
		}
		else
		{
			$t->sort_by();
		}
	}

	function _get_org_tlb(&$arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "create_event",
			"tooltip" => t("Lisa"),
			"img" => "new.gif",
		));

		$df = $arr["obj_inst"]->prop("dir_firma");
		if ($this->can("view", $df))
		{
			$df = array($df);
		}

		foreach(safe_array($df) as $pt)
		{
			$pt = obj($pt);
			$tb->add_menu_item(array(
				"parent" => "create_event",
				"text" => sprintf(t("Lisa organisatsioon (%s)"), $pt->name()),
				"url" => $this->mk_my_orb("new", array("parent" => $pt->id(),"return_url" => get_ru(), "sector" => $_GET["branch_id"]), CL_CRM_COMPANY),
			));
		}
		if($arr["request"]["group"] == "tegevusalad" || $arr["request"]["group"] == "org")
		{
			if (isset($_GET["branch_id"]) && $this->can("add", $_GET["branch_id"]))
			{
				$tb->add_menu_item(array(
					"parent" => "create_event",
					"text" => t("Lisa tegevusala"),
					"url" => $this->mk_my_orb("new", array("parent" => $_GET["branch_id"], "return_url" => get_ru()), CL_CRM_SECTOR),
				));
			}
			else
			{
				$ar = new aw_array($arr["obj_inst"]->prop("dir_tegevusala"));
				foreach($ar->get() as $pt)
				{
					$pto = obj($pt);
					$tb->add_menu_item(array(
						"parent" => "create_event",
						"text" => sprintf(t("Lisa tegevusala %s"), $pto->name()),
						"url" => $this->mk_my_orb("new", array("parent" => $pt,"return_url" => get_ru()), CL_CRM_SECTOR),
					));
				}
			}
		}
		$pl = get_instance(CL_PLANNER);
		$cal_id = $pl->get_calendar_for_user(array(
			"uid" => aw_global_get("uid"),
		));
		if(!empty($cal_id))	
		{
			$tb->add_button(array(
				"name" => "user_calendar",
				"tooltip" => t("Kasutaja kalender"),
				"url" => html::get_change_url($cal_id, array("group" => "views", "return_url" => get_ru())),
				"img" => "icon_cal_today.gif",
			));
		}
		$tb->add_separator();
		$tb->add_menu_button(array(
			"name" => "go_navigate",
			"tooltip" => t("Ava valim"),
			"img" => "iother_shared_folders.gif",
		));
		$tb->add_separator();
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"action" => "delete_organizations",
			"confirm" => t("Kustutada valitud organisatsioonid?"),
			"img" => "delete.gif",
		));
		$tb->add_separator();
		if(is_oid($arr["obj_inst"]->owner_org))
		{
			if($arr["request"]["group"] == "not_on_web" || !$arr["obj_inst"]->show_as_on_web)
			{
				$tb->add_button(array(
					"name" => "show_on_web",
					"tooltip" => t("Kuva veebis"),
					"action" => "show_on_web",
				));
			}
			if($arr["request"]["group"] != "not_on_web")
			{
				$tb->add_button(array(
					"name" => "hide_on_web",
					"tooltip" => t("&Auml;ra kuva veebis"),
					"action" => "hide_on_web",
				));
			}
		}
		
		$conns = $arr["obj_inst"]->connections_from(array(
			"class" => CL_CRM_SELECTION,
			"sort_by" => "to.name",
		));

		$ops = array();
		$ops[0] = t("-- vali valim --");

		foreach($conns as $conn)
		{
			$to = $conn->prop("to");
			$name = $conn->prop("to.name");
			$ops[$to] = $name;
			$tb->add_menu_item(array(
				"parent" => "go_navigate",
				"text" => $name,
				"url" => html::get_change_url($to),
			));
		};
		$str = html::select(array(
			"name" => "add_to_selection",
			"options" => $ops,
			"selected" => isset($selected) ? $selected : array(),
		));
		$tb->add_cdata($str, "right");
		$tb->add_separator(array(
			"side" => "right",
		));
		$tb->add_button(array(
			"name" => "go_add",
			"tooltip" => t("Lisa valitud valimisse"),
			"action" => "copy_to_selection",
			"confirm" => t("Paiguta valitud organisatsioonid sellesse valimisse?"),
			"img" => "import.gif",
			"side" => "right",
		));
	}	

	/**  
		
		@attrib name=delete_organizations params=name all_args="1" 
		
	**/
	function delete_organizations($arr)
	{
		foreach(safe_array($arr["sel"]) as $obj_id)
		{
			if($this->can("delete", $obj_id))
			{
				$o = obj($obj_id);
				$o->delete();
			}
		}
		return urldecode($arr["post_ru"]);
	}
	
	/**  
		
		@attrib name=copy_to_selection params=name all_args="1" 
		
	**/
	function copy_to_selection($arr)
	{
		$selinst = get_instance(CL_CRM_SELECTION);
		$selinst->add_to_selection(array(
			"add_to_selection" => $arr["add_to_selection"],
			"sel" => $arr["sel"],
		));
		unset($arr["MAX_FILE_SIZE"]);
		unset($arr["action"]);
		unset($arr["reforb"]);
		unset($arr["sel"]);
		return $this->mk_my_orb("change", $arr);
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_mod_retval($arr)
	{
		$args = &$arr["args"];
		// no I need add all those things in search_form1 do my request vars
		if (is_array($arr["request"]["search_form1"]))
		{
			$args["search_form1"] = $arr["request"]["search_form1"];
		}
	}

	/**
		@attrib name=show_on_web all_args=1
	**/
	public function show_on_web($arr)
	{
		$ids = safe_array($arr["sel"]);
		if(count($ids) == 0 || !is_oid($arr["id"]) || !is_oid(obj($arr["id"])->owner_org))
		{
			return $arr["post_ru"];
		}
		$seller = obj($arr["id"])->owner_org;
		
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
			"lang_id" => array(),
			"site_id" => array(),
			"buyer" => $ids,
			"seller" => $seller,
		));

		foreach($ol->arr() as $o)
		{
			if(!$o->show_in_webview)
			{
				$o->show_in_webview = 1;
				$o->save();
			}
			unset($ids[$o->buyer]);
		}

		foreach(array_keys($ids) as $id)
		{
			$o = obj();
			$o->set_class_id(CL_CRM_COMPANY_CUSTOMER_DATA);
			$o->set_parent($seller);
			$o->seller = $seller;
			$o->buyer = $id;
			$o->show_in_webview = 1;
			$o->save();
		}

		return $arr["post_ru"];
	}

	/**
		@attrib name=hide_on_web all_args=1
	**/
	public function hide_on_web($arr)
	{
		$ids = safe_array($arr["sel"]);
		if(count($ids) == 0 || !is_oid($arr["id"]) || is_oid(obj($arr["id"])->owner_org))
		{
			return $arr["post_ru"];
		}
		$seller = obj($arr["id"])->owner_org;
		
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
			"lang_id" => array(),
			"site_id" => array(),
			"buyer" => $ids,
			"seller" => $seller,
		));

		$ids = array_flip($ids);

		foreach($ol->arr() as $o)
		{
			if($o->show_in_webview)
			{
				$o->show_in_webview = 0;
				$o->save();
			}
			unset($ids[$o->buyer]);
		}

		return $arr["post_ru"];
	}
}
?>
