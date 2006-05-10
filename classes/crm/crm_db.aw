<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_db.aw,v 1.33 2006/05/10 14:15:54 kristo Exp $
// crm_db.aw - CRM database
/*
@classinfo relationmgr=yes syslog_type=ST_CRM_DB
@default table=objects
@default group=general

@default field=meta
@default method=serialize

@property selections type=relpicker reltype=RELTYPE_SELECTIONS group=general
@caption Vaikimisi valim

@property dir_firma type=relpicker reltype=RELTYPE_FIRMA_CAT
@caption Ettevõtete kaust

@property folder_person type=relpicker reltype=RELTYPE_ISIK_CAT
@caption Töötajate kaust

@property dir_address type=relpicker reltype=RELTYPE_ADDRESS_CAT
@caption Aadresside kaust

@property dir_ettevotlusvorm type=relpicker reltype=RELTYPE_ETTEVOTLUSVORM_CAT
@caption Õiguslike vormide kaust

@property dir_linn type=relpicker reltype=RELTYPE_LINN_CAT
@caption Linnade kaust

@property dir_maakond type=relpicker reltype=RELTYPE_MAAKOND_CAT
@caption Maakondade kaust

@property dir_tegevusala type=relpicker reltype=RELTYPE_TEGEVUSALA_CAT
@caption Tegevusalade kaust

@property dir_toode type=relpicker reltype=RELTYPE_TOODE_CAT
@caption Toodete kaust

@property dir_default type=relpicker reltype=RELTYPE_GENERAL_CAT
@caption Kaust, kui mõni eelnevatest pole määratud, siis kasutatakse seda

@property flimit type=select
@caption Kirjeid ühel lehel

-----------------------------------------------------------------------------
@groupinfo org caption=Organisatsioonid

@groupinfo f2 submit=no caption=Otsing parent=org
@default group=f2
	
@property orgtoolbar type=toolbar no_caption=1 group=firmad,f2,tegevusalad
@caption Org. toolbar

@layout org type=hbox width=20%:80%

@property search_form1 type=form sclass=crm/crm_org_search sform=crm_search parent=org
@caption Compound search

property search_table type=table parent=org no_caption=1

-----------------------------------------------------------------------------

@groupinfo firmad submit=no caption=Nimekiri parent=org
@default group=firmad

@property company_table type=table no_caption=1

-----------------------------------------------------------------------------

@groupinfo tegevusalad submit=no caption=Tegevusalad parent=org
@default group=tegevusalad

@layout ta type=hbox width=20%:80%
	
@property sector_tree type=treeview parent=ta no_caption=1
	
@property sector_table type=table parent=ta no_caption=1

----------------------------------------------------------

@reltype SELECTIONS value=1 clid=CL_CRM_SELECTION
@caption Valimid

@reltype FIRMA_CAT value=2 clid=CL_MENU
@caption Organisatsioonide kaust

@reltype ISIK_CAT value=3 clid=CL_MENU
@caption Töötajate kaust

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
@caption Üldkaust

@reltype CALENDAR value=11 clid=CL_PLANNER
@caption Kalender

@reltype ETTEVOTLUSVORM_CAT value=12 clid=CL_MENU
@caption Õiguslike vormide kaust

@reltype FORMS  value=13 clid=CL_CFGFORM
@caption Sisestusvormid

@reltype METAMGR value=14 clid=CL_METAMGR
@caption Muutujad

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
			case "search_table":
			case "sector_table":
			case "company_table":
				$this->company_table($arr);
				break;
			
			case "sector_tree":
				$this->sector_tree($arr);
				break;
		
			case "orgtoolbar":
				$this->org_toolbar($arr);
				break;

			case "flimit":
				$prop["options"] = array (30 => 30, 60 => 60, 100 => 100);
				break;
		}
		return  $retval;
	}

	function sector_tree($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];		
		$menu_tree = new object_tree(array(
			"parent" => $arr["obj_inst"]->prop("dir_tegevusala"),
			"class_id" => CL_CRM_SECTOR,
			"sort_by" => "objects.name",
		));
		$sectors_list = $menu_tree->to_list();
		$ids = $this->make_keys($sectors_list->ids());
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
				"oncl" => "onClick=\"aw_popup_scroll('$url', 'aw_doc_edit',600, 400)\"",
				"link" => "javascript:void(0)"
			));
			$pm->add_item(array(
				"text" => t("Kustuta"),
				"link" => $this->mk_my_orb("delete_organizations", array("id" => $arr["obj_inst"]->id(), "sel[$id]" => $id, "post_ru" => get_ru())),
			));
			$name = $name;//." ".$pm->get_menu();
			$t->add_item($parent, array(
				"id" => $id,
				"name" => $name,//strlen($name) > 20 ? substr($name, 0, 20).".." : $name,
				"url" => aw_url_change_var("teg_oid", $sect->id()),
			));
		}
		$t->set_selected_item(ifset($arr["request"], "teg_oid"));
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
			"caption" => t("Põhitegevus"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "ettevotlusvorm",
			"caption" => t("Õiguslik vorm"),
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
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function company_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_company_table(&$t);

		$all_letters = array(); //array("0-9");
		foreach(range("A", "Z") as $v)
		{
			$all_letters[] = $v;
		}
		$let = array("Ö", "Ä", "Ü", "Õ");
		foreach($let as $l)
		{
			$all_letters[] = $l;
		}
		$letter = $arr["request"]["letter"] ? urldecode($arr["request"]["letter"]) : "A";

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
			"sort_by" => "objects.name",
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
						"no_search" => 1,
				), ($val == $letter ? "<b>".$val."</b>" : $val));
			}
		}
		if($this->can("view", $arr["request"]["teg_oid"]))
		{
			$vars["pohitegevus"] = $arr["request"]["teg_oid"];
		}
		
		elseif($arr["request"]["group"] == "tegevusalad" && !$this->can("view", $arr["request"]["teg_oid"]))
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
		$ft_page = (int)$arr["request"]["ft_page"];
		$vars["limit"] = (60*$ft_page).",".(60*$ft_page+60);
		$t->table_header = $pageselector."<br />".$ps;
		$companys = new object_list($vars);
		$coms = $companys->arr();
		foreach($coms as $com)
		{
			$url = $com->prop("url_id.name");
			$url = substr($url, strpos($url, "http://"), strlen($url)+1);
			if(strlen($url) > 0)
			{
				$url = html::href(array("url" => $url, "caption" => $url, "target" => "_blank"));
			}
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

			$t->define_data(array(
				"id" => $com->id(),
				"org" => html::get_change_url($com->id(), array("return_url" => get_ru()), strlen($com->name()) ? $com->name() : t("(nimetu)")),
				"field" => $com->prop("pohitegevus.name"),
				"ettevotlusvorm" => $com->prop("ettevotlusvorm.name"),
				"address" => $com->prop("contact.name"),
				"e_mail" => $com->prop("email_id.mail"),
				"url" => $url,
				"phone" => $com->prop("phone_id.name"),
				"org_leader" => $org_leader,
				"cr_manager" => $cr_manager,
			));
		}
		$t->sort_by();
	}

	function org_toolbar(&$arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "create_event",
			"tooltip" => t("Lisa"),
			"img" => "new.gif",
		));
		$tb->add_menu_item(array(
			"parent" => "create_event",
			"text" => t("Lisa organisatsioon"),
			"url" => $this->mk_my_orb("new", array("parent" => $arr["obj_inst"]->prop("dir_firma"),"return_url" => get_ru(), "sector" => $arr["request"]["teg_oid"]), CL_CRM_COMPANY),
		));
		if($arr["request"]["group"] == "tegevusalad")
		{
			$pt = $arr["request"]["teg_oid"] ?  $arr["request"]["teg_oid"] : $arr["obj_inst"]->prop("dir_tegevusala");
			$tb->add_menu_item(array(
				"parent" => "create_event",
				"text" => t("Lisa tegevusala"),
				"url" => $this->mk_my_orb("new", array("parent" => $pt,"return_url" => get_ru()), CL_CRM_SECTOR),
			));
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
		$str .= html::select(array(
			"name" => "add_to_selection",
			"options" => $ops,
			"selected" => $selected,
		));

		$tb->add_separator(array(
			"side" => "right",
		));
		$tb->add_cdata($str, "right");
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
}
?>
