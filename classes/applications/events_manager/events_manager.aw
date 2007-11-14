<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/events_manager/events_manager.aw,v 1.18 2007/11/14 12:57:51 markop Exp $
// events_manager.aw - Kuhu minna moodul
/*

@classinfo syslog_type=ST_EVENTS_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property name type=textbox group=settings
	@caption Nimi

@default field=meta
@default method=serialize

@groupinfo events caption="S&uuml;ndmused" submit=no
@default group=events

	@property events_tb type=toolbar store=no no_caption=1
	@caption P&otilde;hitoolbar

		@layout events_top type=hbox closeable=1 width=30%:70%

		@layout events_top_left type=vbox parent=events_top closeable=1 area_caption=S&uuml;ndmuste&nbsp;Otsing
		#nupud: Lisa uus, Kustuta märgistatud sündmused (confirmation "Olete kindel, et soovite kustudada kõik valitud sündmused?"), Sündmuste väljatrükk (sisu selgub hiljem), Arhiiv (toggle. n2itab tabelis syndmusi mis toimund. nupu nimi muutub: "Uued"), Järjesta laekumise järgi (järjestab syndmused created j2rgi kahanevalt)
		#	Otsinguvorm vasakul (otsingutulemused tabelis):

			@property e_find_sectors type=select multiple=1 parent=events_top_left
			@caption Valdkonnad
			# seostatud valdkonnaobjektid

			@property e_find_editor type=select multiple=1 parent=events_top_left
			@caption Toimetaja
			#	valikud: Toimetajad + Veebikasutaja

			@property e_find_text type=textbox parent=events_top_left size=20
			@caption  Tekst
			#otsing syndmuste pealkirjadest, sissejuhatustest, kirjeldustest

			@property e_find_news type=chooser no_caption=1 parent=events_top_left
			@caption Arhiivist, Uute hulgast
			# valikud: Arhiivist, Uute hulgast

			@property event_search_button type=submit store=no
			@caption Otsi

			@property see_all type=text store=no no_caption=1
			@caption N&auml;ita k&otilde;iki s&uuml;ndmusi

		@layout events_top_right type=vbox parent=events_top

		#Tabel paremal:

			@property event_table type=table no_caption=1 parent=events_top_right
			@caption S&uumlndmuste tabel
			#	(tabelite definitsioonid vt. allpool) Vaikimisi n2idatakse ainult tulevaid ja kestvaid syndmusi (vt. toolbari nupp Arhiiv). Syndmused, mille parent on "Sündmuste kataloog". Kui pole m22ratud siis tyhi tabel.
			#?? kuskile panna otsingust v2lja saamise v6imalus -- n2ita k6iki syndmusi ??

@groupinfo places caption="Toimumiskohad" submit=no
@default group=places

	@property places_tb type=toolbar no_caption=1
	@caption Toimumiskohtade toolbar
	  #nupud: Lisa uus
	@property places_table type=table no_caption=1
	@caption Toimumiskohtade tabel
	  #objektid vastava kataloogi alt

@groupinfo similar_find caption="Sarnased s&uuml;ndmused" submit=no
@default group=similar_find

	@property similar_tb type=toolbar no_caption=1
	@caption sarnaste s&uuml;ndmuste toolbar
	  #nupud: Kustuta märgistatud sündmused (confirmation "Olete kindel, et soovite kustudada kõik valitud sündmused?")
	@property days_from_today type=select
	@caption P&auml;evi t&auml;nasest:
	  #Valikud: 1,7,15,30,60,90,180,365 default 1, kui palju p2evi tagasi otsida, sessiooni j22b valik kirja
	@property similar_table type=table no_caption=1
	@caption Leitud sarnased sündmused
	  #V6rrelda aktiivseid syndmusi pealkirja, aja ja toimumiskoha j2rgi. arvestada mahtu kuni 3000

@groupinfo organiser caption="Korraldajad" submit=no
@default group=organiser
	@property organiser_tb type=toolbar no_caption=1
	@caption Korraldajate toolbar

	@property organiser_table type=table no_caption=1
	@caption Korraldajate tabel
	  #objektid vastava kataloogi alt

@groupinfo sectors caption="Valdkonnad" submit=no
@default group=sectors
	@property sectors_tb type=toolbar no_caption=1
	@caption  toolbar

	@property sectors_table type=table no_caption=1
	@caption Valdkondade tabel
	  #nupud: Lisa uus

@groupinfo editors caption="Toimetajad" submit=no
@default group=editors

	@property editor_tb type=toolbar no_caption=1
	@caption Toimetajad toolbar

	@property editors_table type=table no_caption=1

@groupinfo settings caption="Seaded"
@default group=settings

	@property sector type=relpicker multiple=1 reltype=RELTYPE_SECTOR field=meta method=serialize table=objects
	@caption Valdkonnad

	@property owner type=relpicker reltype=RELTYPE_OWNER
	@caption Omanik

	@property languages type=relpicker multiple=1 reltype=RELTYPE_LANGUAGE
	@caption Sisu keeled

	@property editor type=relpicker reltype=RELTYPE_EDITOR
	@caption Toimetaja

	@property mapserver_url type=textbox
	@caption Kaardiserveri url

	@property similar_time type=textbox
	@caption Sarnaste sündmuste kattumisaeg (tundides)


	@property forms_caption type=text store=no subtitle=1
	@caption Vormid

		@property event_form type=relpicker reltype=RELTYPE_CFGMANAGER field=meta method=serialize table=objects
		@caption S&uuml;ndmuste seadete haldur

		@property places_form type=relpicker reltype=RELTYPE_CFGMANAGER field=meta method=serialize table=objects
		@caption Toimumiskohtade seadete haldur

		@property organiser_form type=relpicker reltype=RELTYPE_CFGMANAGER field=meta method=serialize table=objects
		@caption Korraldajate seadete haldur

		@property sector_form type=relpicker reltype=RELTYPE_CFGMANAGER field=meta method=serialize table=objects
		@caption Valdkondade seadete haldur

	@layout menus_top type=hbox closeable=1 width=30%:70% area_caption=Kataloogid
		@layout menus_top_left type=vbox parent=menus_top area_caption=Kataloogid&nbsp;lugemiseks

			@property event_menu_source type=relpicker multiple=1 reltype=RELTYPE_EVENT_MENU parent=menus_top_left
			@caption S&uuml;ndmuste kataloog

			@property places_menu_source type=relpicker multiple=1 reltype=RELTYPE_PLACE_MENU parent=menus_top_left
			@caption Toimumiskohtade kataloog

			@property organiser_menu_source type=relpicker multiple=1 reltype=RELTYPE_ORGANISER_MENU parent=menus_top_left
			@caption Korraldajate kataloog

			@property sector_menu_source type=relpicker multiple=1 reltype=RELTYPE_SECTOR_MENU parent=menus_top_left
			@caption Valdkondade kataloog

		@layout menus_top_right type=vbox parent=menus_top area_caption=Kataloogid&nbsp;kirjutamiseks

			@property event_menu type=relpicker reltype=RELTYPE_EVENT_MENU parent=menus_top_right
			@caption S&uuml;ndmuste kataloog kirjutamiseks

			@property places_menu type=relpicker reltype=RELTYPE_PLACE_MENU parent=menus_top_right
			@caption Toimumiskohtade kataloog kirjutamiseks

			@property organiser_menu type=relpicker reltype=RELTYPE_ORGANISER_MENU parent=menus_top_right
			@caption Korraldajate kataloog kirjutamiseks

			@property sector_menu type=relpicker reltype=RELTYPE_SECTOR_MENU parent=menus_top_right
			@caption Valdkondade kataloog kirjutamiseks



#RELTYPES

@reltype SECTOR value=1 clid=CL_CRM_SECTOR
@caption Valdkond

@reltype EVENT_MENU value=2 clid=CL_MENU
@caption S&uuml;ndmuste kataloog

@reltype PLACE_MENU value=3 clid=CL_MENU
@caption Toimumiskohtade kataloog

@reltype ORGANISER_MENU value=4 clid=CL_MENU
@caption Korraldajate kataloog

@reltype SECTOR_MENU value=5 clid=CL_MENU
@caption Valdkondade kataloog

@reltype OWNER value=6 clid=CL_CRM_COMPANY
@caption Omanik

@reltype LANGUAGE value=7 clid=CL_LANGUAGE
@caption Keel

@reltype CFGFORM value=8 clid=CL_CFGFORM
@caption Seadete vorm

@reltype EDITOR value=9 clid=CL_CRM_PERSON
@caption Toimetaja

@reltype CFGMANAGER value=10 clid=CL_CFGMANAGER
@caption Seadete haldur

*/

class events_manager extends class_base
{
	function events_manager()
	{
		$this->init(array(
			"tpldir" => "applications/events_manager/events_manager",
			"clid" => CL_EVENTS_MANAGER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "editor":
				return PROP_IGNORE;
				break;
			case "events_tb":
				$this->_get_events_tb($arr);
				break;
			case "e_find_sectors":
				$sector_list = new object_list();
				if(is_array($arr["obj_inst"]->prop("sector")) || is_oid($arr["obj_inst"]->prop("sector")))
				{
					$sector_list->add($arr["obj_inst"]->prop("sector"));
					$prop["options"] = $sector_list->names();
				}
				break;

			case "e_find_editor":
				$groups_list = new object_list(array(
					"class_id" => CL_GROUP,
					"lang_id" => array(),
					"site_id" => array(),
					"type" => new obj_predicate_not(1),
				));
				$prop["options"] = $groups_list->names();
//				$prop["options"] = array(t("Toimetajad"),t("Veebikasutaja"));
				break;

			case "e_find_news":
				$prop["options"] = array(t("Arhiivist"),t("Kestvatest"),t("Uute hulgast"));
				break;
			case "event_table":
				$this->_get_event_table($arr);
				break;

			case "places_table":
				$this->_get_places_table($arr);
				break;
			case "see_all":
				return PROP_IGNORE;
				$prop["value"] = html::href(array(
					"caption" => t("N&auml;ita k&otilde;iki!"),
					"url" => $this->mk_my_orb("change" , array("group" => "events" , "id" => $arr["obj_inst"]->id(), "see_all" => 1)),
				));
				break;
			case "days_from_today":
				if($_SESSION["events_manager"]["dft"])
				{
					$prop["options"]["value"] = $_SESSION["events_manager"]["dft"];
				}
				$prop["options"] = array(1=> 1,7=> 7,15=> 15,30=> 30,60=> 60,90=> 90,180=> 180,365=> 365);
				$prop["onchange"] = "javascript:document.changeform.submit();";
				break;

			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "e_find_news":
				unset($arr["request"]["rawdata"]);
				$arr["obj_inst"]->set_meta("search_data" , $arr["request"]);
				break;
			case "days_from_today":
				$_SESSION["events_manager"]["dft"] = $arr["request"]["days_from_today"];
				break;
			//-- set_property --//
		}
		return $retval;
	}

	function _get_places_table($arr)
	{
		if(!$arr["obj_inst"]->prop("places_menu_source"))
		{
			print t("Toimumiskohtade kataloog m&auml;&auml;ramata");
			return;
		}
		$ol = new object_list(array(
			"site_id" => array(),
			"lang_id" => array(),
			"parent" => $arr["obj_inst"]->prop("places_menu_source"),
		));
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_places_table($t);
	
		$cfg = $this->get_cgf_from_manager("places" , CL_SCM_LOCATION);

		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"name" => (!$this->can("edit" , $o->id()))?$o->name():
					 html::get_change_url($o->id(), array("cfgform" => $cfg),($o->name()?$o->name():"(".t("Nimetu").")")),
					//html::obj_change_url($o->id()),
				"comment" => $o->prop("comment"),
				"oid" => $o->id(),
			));
		}
	}

	function get_cgf_from_manager($type , $clid)
	{
		if(is_oid($this->obj_inst->prop($type."_form")))
		{
			$cfg_loader = new object($this->obj_inst->prop($type."_form"));
			$mxt = $cfg_loader->meta("use_form");
			$forms = $mxt[$clid];
			$gx = aw_global_get("gidlist_pri_oid");
			$found_form = false;
			if (is_array($gx) && is_array($forms))
			{
				// start from group with highest priority
				arsort($gx);
				foreach($gx as $grp_oid => $grp_pri)
				{
					if ($forms[$grp_oid] && empty($found_form))
					{
						$found_form = $forms[$grp_oid];
						$this->tmp_cfgform = $found_form;
						//arr($found_form);
					};
				};
			}
			return $found_form;
		}
		else return null;
	}

	function _get_organiser_table($arr)
	{
		if(!$arr["obj_inst"]->prop("organiser_menu_source"))
		{
			print t("Korraldajate kataloog m&auml;&auml;ramata");
			return;
		}
		$ol = new object_list(array(
			"site_id" => array(),
			"lang_id" => array(),
			"parent" => $arr["obj_inst"]->prop("organiser_menu_source"),
		));
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_organiser_table($t);
		$cfg = $this->get_cgf_from_manager("organiser" , CL_CRM_COMPANY);

		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"name" => (!$this->can("edit" , $o->id()))?$o->name():
					 html::get_change_url($o->id(), array("cfgform" => $cfg),($o->name()?$o->name():"(".t("Nimetu").")")),
					//html::obj_change_url($o->id()),
				"address" => $o->prop("contact.name"),
				"oid" => $o->id(),
			));
		}
	}

	function _get_sectors_table($arr)
	{
		if(!$arr["obj_inst"]->prop("sector_menu_source"))
		{
			print t("Valdkondade kataloog m&auml;&auml;ramata");
			return;
		}
		$ol = new object_list(array(
			"site_id" => array(),
			"lang_id" => array(),
			"parent" => $arr["obj_inst"]->prop("sector_menu_source"),
		));
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sectors_table($t);
		$cfg = $this->get_cgf_from_manager("sector" , CL_CRM_SECTOR);
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"name" => (!$this->can("edit" , $o->id()))?$o->name():
					 html::get_change_url($o->id(), array("cfgform" => $cfg),($o->name()?$o->name():"(".t("Nimetu").")")),
					//html::obj_change_url($o->id()),
				//"address" => $o->prop("contact.name"),
			));
		}
	}

	function _get_editors_table($arr)
	{
	//kasutaja vaja
		$u = get_instance(CL_USER);
		$ol = new object_list(array(
			"site_id" => array(),
			"lang_id" => array(),
			"class_id" => CL_CRM_PERSON,
			"parent" => $arr["obj_inst"]->id(),
		));
		foreach(
			$arr["obj_inst"]->connections_from(array(
				"type" => "RELTYPE_EDITOR",
			))
			as $c)
		{
			$ol->add($c->to());
		}

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_editors_table($t);
		foreach($ol->arr() as $o)
		{
			$gro = $c = $org = $org_c = null;
			$cons = $o->connections_from(array(
				"type" => "RELTYPE_WORK",
			));
			$org_c = reset($cons);
			if($org_c) $org = $org_c->prop("to");

			if($this->can("view" , $org))
			{
				$co = obj($org);
				$c = $this->can("edit" , $co->id()) ? html::obj_change_url($co) :  $co->name();
			}

			$user_list = new object_list(array("class_id" => CL_USER, "lang_id" => array(), "CL_USER.RELTYPE_PERSON.id"=>$o->id()));
			if(sizeof($user_list->arr()))
			{
				$user = reset($user_list->arr());
			}
			if(is_object($user))
			{
				 $gro = $u->get_highest_pri_grp_for_user($user->prop("uid"));
			}
			$t->define_data(array(
				"name" => (!$this->can("edit" , $o->id())) ? $o->name() : html::obj_change_url($o->id()),
				"user" => is_object($user)?$user->prop("uid"):"",
				"oid" => $o->id(),
				"group" => is_object($gro)?$gro->name():"",
				"company" => $c,
			));
		}
	}


	function _get_places_tb($arr)
	{
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus"),
			"action" => "add_new_place",
		));
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta m&auml;rgistatud s&uuml;ndmused"),
			"action" => "delete_events",
			"confirm" => t("Olete kindel, et soovite kustudada kõik valitud toimumiskohad?"),
		));
	}

	function _get_sectors_tb($arr)
	{
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus"),
			"action" => "add_new_sector",
		));
	}

	function _get_organiser_tb($arr)
	{
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus"),
			"action" => "add_new_organiser",
		));
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta m&auml;rgistatud korraldajad"),
			"action" => "delete_events",
			"confirm" => t("Olete kindel, et soovite kustudada kõik valitud korraldajad?"),
		));
	}

	function _get_editor_tb($arr)
	{
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus"),
			"action" => "add_new_editor",
		));
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta m&auml;rgistatud toimetajad"),
			"action" => "delete_events",
			"confirm" => t("Olete kindel, et soovite kustudada kõik valitud toimetajad?"),
		));


		$url = $this->mk_my_orb("do_search", array(
			"pn" => "editor",
			"id" => $arr["obj_inst"]->id(),
			"multiple" => 1,
			"clid" => CL_CRM_PERSON,
		), "popup_search");

		$arr["prop"]["vcl_inst"]->add_button(array(
			"tooltip" => t("Otsi"),
			"name" => "search",
			"img" => "search.gif",
//			"action" => "remove_images",
			"url" => "javascript:aw_popup_scroll('$url','".t("Otsi")."',550,500)"
		));




	}

	function _get_event_list(&$arr)
	{
		$filter = array(
			"class_id" => array(CL_CALENDAR_EVENT),
			"site_id" => array(),
			"lang_id" => array(),
		);

		if(sizeof($arr["obj_inst"]->meta("search_data")) > 1)
		{
			$search_data = $arr["obj_inst"]->meta("search_data");
			if(is_array($search_data["e_find_sectors"]) && sizeof($search_data["e_find_sectors"]))
			{
				$filter["CL_CALENDAR_EVENT.RELTYPE_SECTOR.id"] = $search_data["e_find_sectors"];
			}
			if($search_data["e_find_editor"])
			{
				//möh?
			}
			if($search_data["e_find_text"])
			{
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array(
						"name" => "%".$search_data["e_find_text"]."%",
						"title" => "%".$search_data["e_find_text"]."%",
						"description" => "%".$search_data["e_find_text"]."%",
					)
				));
//				$filter["name"] = "%".$search_data["e_find_text"]."%";
				//kui kalendrisündmuseobjektiasjadkorda saab , siis lisab juurde
			}
			if($search_data["e_find_news"] == 2)
			{
				$filter["start1"] = new obj_predicate_compare(OBJ_COMP_GREATER, time());
			}
			elseif($search_data["e_find_news"] == 1)
			{
				$filter[] = new object_list_filter(array(
					"logic" => "AND",
					"conditions" => array(
						"start1" => new obj_predicate_compare(OBJ_COMP_LESS, time()),
						"end" => new obj_predicate_compare(OBJ_COMP_GREATER, time()),
					)
				));
			}
			else
			{
				$filter["end"] = new obj_predicate_compare(OBJ_COMP_LESS, time());
			}

			$arr["obj_inst"]->set_meta("search_data", null);
			$arr["obj_inst"]->save();
		}//siia peaks jõudma kui midagi pole valitud
		elseif(!$arr["request"]["archived"] && $arr["request"]["action"] != "similar_find" && !$arr["dft"])
		{
			if(!$arr["obj_inst"]->prop("event_menu_source"))
			{
				return new object_list();
			}
			if(!$arr["request"]["see_all"])
			{
				$filter["end"] = new obj_predicate_compare(OBJ_COMP_GREATER, time());
			}
			$filter["parent"] = $arr["obj_inst"]->prop("event_menu_source");
		}
		if($arr["dft"])
		{
			$filter["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, time()- 24*3600*$arr["dft"] , time());
		}

		if($arr["request"]["archived"] == 1)
		{
			$filter["end"] = new obj_predicate_compare(OBJ_COMP_LESS, time());
		}
		if($arr["request"]["archived"] == -1)
		{
			$filter["start1"] = new obj_predicate_compare(OBJ_COMP_GREATER, time());
		}

		$filter["sort_by"] = "objects.created ASC";

		return new object_list($filter);
	}

	function _get_event_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$this->_init_event_table($t,$arr);
		$cal_event = get_instance(CL_CALENDAR_EVENT);

		//selle va gruppide otsingu pärast peab peale listi tegemist asju välja pookima hakkama
		$sd = $arr["obj_inst"]->meta("search_data");
		if(is_array($sd["e_find_editor"]) && sizeof($sd["e_find_editor"]))
		{
			$users = new object_list(array(
				"class_id" => CL_USER,
				"lang_id" => array(),
				"site_id" => array(),
				"status" => array(),
			//	"CL_USER.RELTYPE_GRP" => $sd["e_find_editor"],
			));
			$ua = $users->names();
		}
		$ol = $this->_get_event_list($arr);

		if($arr["request"]["sort_by_created"] == 1)
		{
			$ol->sort_by(array(
				"prop" => "created",
				"order" => "desc"
			));
		}

		$t->set_sortable(false);
		foreach($ol->arr() as $o)
		{
			if(is_array($ua) && !in_array($o->createdby() , $ua))
			{
				continue;
			}

			$publish = "";
			$sec = $o->get_first_obj_by_reltype("RELTYPE_SECTOR");
			if(!$o->prop("published"))
			{
				$publish = html::href(array(
					"url" => $this->mk_my_orb("publish" , array("id" => $o->id(),"post_ru" => post_ru())),
					"title" => t("Avalda"),
					"caption" => t("Avalda"),
				));
			}
			$change_url = (!$this->can("edit" , $o->id()))?"":html::obj_change_url($o , t("Muuda"));

			$make_copy = html::href(array(
				"url" => $this->mk_my_orb("make_copy" , array("id" => $o->id(),"post_ru" => post_ru())),
				"title" => t("Tee koopia"),
				"caption" => t("Tee koopia"),
			));

			$translated = "";
			if(is_array($o->meta("translations")))
			{
				$langs = array();
				foreach($o->meta("translations") as $lang => $trans)
				{
					$langs[$lang] = $GLOBALS["cfg"]["languages"]["list"][$lang]["name"];
					foreach($trans as $prop => $val)
					{
						if(!$val) unset($langs[$lang]);
						break;
					}
				}
//				foreach ($langs as $l)
//				{
					$translated = join (", " ,$langs);
//				}
			}

			$t->define_data(array(
				"name" => (!$this->can("edit" , $o->id()))?$o->name():
					// html::get_change_url($o->id(), array("cfgform" => $arr["obj_inst"]->prop("event_form")),$o->name()?$o->name():t("(Nimetu)")),
					html::obj_change_url($o->id()),
				"time" => date("d.m.Y" , $o->prop("start1")). "-" .date("d.m.Y" , $o->prop("end")),
				"sector" => (is_object($sec))?$sec->name():"",
				"level" => $cal_event->level_options[$o->prop("level")],
				"tasks" => $make_copy . " " . $publish . " " . $change_url,
				"oid" => $o->id(),
				"region" => $o->prop("location.address.maakond.name") ." ".$o->prop("location.address.linn.name"),
				"translated" => $translated,
			));
		}
	}

	function _get_similar_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_event_table($t,$arr);
		$cal_event = get_instance(CL_CALENDAR_EVENT);
		$arr["dft"] = ($_SESSION["events_manager"]["dft"]) ? $_SESSION["events_manager"]["dft"] : 1;
		$ol = $this->_get_similar_event_list($arr);

		$t->set_sortable(false);
		foreach($ol->arr() as $o)
		{
			$sec = $o->get_first_obj_by_reltype("RELTYPE_SECTOR");
			$publish = html::href(array("url" => "#" , "title" => t("Avalda") , "caption" => t("Avalda")));
			$change_url = html::obj_change_url($o , t("Muuda"));

			$t->define_data(array(
				"name" => (!$this->can("edit" , $o->id()))?$o->name():html::obj_change_url($o->id()),
				"time" => date("d.m.Y" , $o->prop("start1")). "-" .date("d.m.Y" , $o->prop("end")),
				"sector" => (is_object($sec))?$sec->name():"",
				"level" => $cal_event->level_options[$o->prop("level")],
				"oid" => $o->id(),
			));
		}
	}

	function _get_similar_event_list($arr)
	{
		$days = $arr["dft"];
//		$GLOBALS["DUKE"] = 1;
		enter_function("events::sql");
		$q = "
			SELECT
			objects.oid as oid,
			objects.name as name,
			objects.parent as parent,
			objects.brother_of as brother_of,
			objects.status as status,
			objects.class_id as class_id,
			objects.acldata as acldata,
			objects.parent as parent,
 			planner.start as start,
 			planner.end as end,
 			planner.ucheck5 as location
			FROM
			objects  LEFT JOIN planner ON planner.id = objects.brother_of
			WHERE
			objects.class_id = 819
			AND planner.start  <  ".(time() + 86400 * $days)."
			AND planner.end  > ".time()."
			AND  objects.status > 0
			ORDER BY planner.ucheck5, planner.`start` DESC;
		";

		$hrs = $arr["obj_inst"]->prop("similar_time") * 3600;
		$ol = new object_list();
		$this->db_query($q);
		$last = array();
		while($w = $this->db_next())
		{
			if($w["location"] == $last["location"] && ($last["start"] < $w["end"] + $hrs) && ($last["end"] + $hrs > $w["start"]))
			{
				$ol->add($w["oid"]);
				$ol->add($last["oid"]);
			}
			$last = $w;
		}
		exit_function("events::sql");
/*
		enter_function("events::ol");
		$filter = array(
			"class_id" => array(CL_CALENDAR_EVENT),
			"site_id" => array(),
			"lang_id" => array(),
			"start1" => new obj_predicate_compare(OBJ_COMP_LESS, (time() + 86400 * $days)),
			"end" => new obj_predicate_compare(OBJ_COMP_GREATER, (time())),
		);
		$ol = new object_list($filter);
		//arr($ol->names());
		exit_function("events::ol");
*/		return $ol;
	}

	function _get_events_tb(&$arr)
	{
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus"),
			"action" => "add_new_event"
		));
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta m&auml;rgistatud s&uuml;ndmused"),
			"action" => "delete_events",
			"confirm" => t("Olete kindel, et soovite kustudada kõik valitud s&uuml;ndmused?"),
		));
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "print",
			"img" => "print.gif",
			"action" => "print_events",
			"tooltip" => t("S&uuml;ndmuste v&auml;ljatr&uuml;kk"),
		));

		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "archive",
			"img" => "archive.gif",
			"url" => aw_url_change_var("archived",($arr["request"]["archived"] == 1 ? -1 : 1)),
			"tooltip" => $arr["request"]["archived"] == 1 ? t("Uued"):t("Arhiiv"),
		));

		//kui uuest vajutada mis juhtub?
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "sort",
			"img" => "down_r_arr.png",
			"url" => aw_url_change_var("sort_by_created",($arr["request"]["sort_by_created"] == 1 ? 1 : 1)),
			"tooltip" => ($arr["request"]["sort_by_created"] == 1 ? t("J&auml;rjesta laekumise j&auml;rgi") : t("J&auml;rjesta laekumise j&auml;rgi")),
		));
		if(!$arr["request"]["see_all"] && $arr["request"]["just_saved"])
		{
			$arr["prop"]["vcl_inst"]->add_button(array(
				"name" => "see_all",
				"img" => "class_31.gif",
				"url" => $this->mk_my_orb("change" , array("group" => "events" , "id" => $arr["obj_inst"]->id(), "see_all" => 1)),
				"tooltip" => "Algseis (kestvad ja tulekul s&uuml;ndmused)",
			));
		}
	}

	function _get_similar_tb(&$arr)
	{
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta m&auml;rgistatud s&uuml;ndmused"),
			"action" => "delete_events",
			"confirm" => t("Olete kindel, et soovite kustudada kõik valitud s&uuml;ndmused?"),
		));
	}

	function _init_places_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "comment",
			"caption" => t("Kirjeldus"),
			"align" => "center"
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _init_organiser_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "address",
			"caption" => t("Aadress"),
			"align" => "center"
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _init_sectors_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1,
		));
// 		$t->define_field(array(
// 			"name" => "address",
// 			"caption" => t("Aadress"),
// 			"align" => "center"
// 		));
	}

	function _init_editors_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "company",
			"caption" => t("Organisatsioon"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "user",
			"caption" => t("Kasutajanimi"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "group",
			"caption" => t("Kasutajagrupp"),
			"align" => "center"
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _init_event_table(&$t,$arr)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Pealkiri"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "sector",
			"caption" => t("Valdkond"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "level",
			"caption" => t("Tase"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "region",
			"caption" => t("Regioon"),
			"align" => "center"
		));
		if($arr["request"]["group"] != "similar_find")
		{
			$t->define_field(array(
				"name" => "tasks",
				"caption" => t("Tegevused"),
				"align" => "center"
			));
		}
		$t->define_field(array(
			"name" => "translated",
			"caption" => t("T&otilde;lgitud"),
			"align" => "center"
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	/**
		@attrib name=delete_events is_public="1" caption="Change"
	**/
	function delete_events($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
                return $arr["post_ru"];
	}

	/**
		@attrib name=add_new_event is_public="1" caption="Change"
	**/
	function add_new_event($arr)
	{
		$event = new object();
		$event->set_class_id(CL_CALENDAR_EVENT);
		$manager_obj = obj($arr["id"]);
		if($this->can("add" , $manager_obj->prop("event_menu")))
		{
			$event->set_parent($manager_obj->prop("event_menu"));
		}
		else
		{
			$event->set_parent($arr["id"]);
		}
		$event->set_prop("start1" , time());
		$event->set_prop("end" , time());
		$event->save();
		return html::get_change_url($event->id(),array("return_url" => $arr["post_ru"]));
	}

	/**
		@attrib name=add_new_place is_public="1" caption="Change"
	**/
	function add_new_place($arr)
	{
		$o = obj($arr["id"]);
		if(!$o->prop("places_menu"))
		{
			print t("Toimumiskohtade kataloog m&auml;&auml;ramata");
			return $arr["post_ru"];
		}
		$event = new object();
		$event->set_class_id(CL_SCM_LOCATION);
		$event->set_parent($o->prop("places_menu"));
		$event->save();
		return html::get_change_url($event->id(),array("return_url" => $arr["post_ru"]));
	}

	/**
		@attrib name=add_new_organiser is_public="1" caption="Change"
	**/
	function add_new_organiser($arr)
	{
		$o = obj($arr["id"]);
		if(!$o->prop("organiser_menu"))
		{
			print t("Toimumiskohtade kataloog m&auml;&auml;ramata");
			return $arr["post_ru"];
		}
		$organiser = new object();
		$organiser->set_class_id(CL_CRM_COMPANY);
		$organiser->set_parent($o->prop("organiser_menu"));
		$organiser->save();
		return html::get_change_url($organiser->id(),array("return_url" => $arr["post_ru"]));
	}

	/**
		@attrib name=add_new_sector is_public="1" caption="Change"
	**/
	function add_new_sector($arr)
	{
		$o = obj($arr["id"]);
		if(!$o->prop("sector_menu"))
		{
			print t("Valdkondade kataloog m&auml;&auml;ramata");
			return $arr["post_ru"];
		}
		$section = new object();
		$section->set_class_id(CL_CRM_SECTOR);
		$section->set_parent($o->prop("sector_menu"));
		$section->save();
		return html::get_change_url($section->id(),array("return_url" => $arr["post_ru"]));
	}

	/**
		@attrib name=add_new_editor is_public="1" caption="Change"
	**/
	function add_new_editor($arr)
	{
		$editor = new object();
		$editor->set_class_id(CL_CRM_PERSON);
		$editor->set_parent($arr["id"]);
		$editor->save();
		$man = obj($arr["id"]);
		$man->connect(array("to"=> $editor->id(), "type" => "RELTYPE_EDITOR"));
		return html::get_change_url($editor->id(),array("return_url" => $arr["post_ru"]));
	}

	/**
		@attrib name=publish is_public="1" all_args=1
	**/
	function publish($arr)
	{
		$event = obj($arr["id"]);
		$event->set_prop("published" , 1);
		$event->save();
		return $arr["post_ru"];
	}

	/**
		@attrib name=make_copy is_public="1" all_args=1
	**/
	function make_copy($arr)
	{
		$event = obj($arr["id"]);

		$o = new object();
		$o->set_class_id(CL_CALENDAR_EVENT);
		$o->set_parent($event->parent());
		$o->set_name($event->name());

		$o->set_prop("published" , 0);
		$o->set_prop("start1" , $event->prop("start1"));
		$o->set_prop("end" , $event->prop("end"));
		$o->set_prop("front_event" , $event->prop("front_event"));
		$o->set_prop("level" , $event->prop("level"));
		$o->set_prop("organizer" , $event->prop("organizer"));
		$o->set_prop("location" , $event->prop("location"));
		$o->set_prop("sector" , $event->prop("sector"));
		$o->set_prop("description" , $event->prop("description"));
		$o->set_prop("short_description" , $event->prop("short_description"));
		$o->set_prop("title" , $event->prop("title"));

		$o->save();
		$o->set_meta($event->meta());
		$o->save();

		foreach($event->connections_from(array("type" => "RELTYPE_EVENT_TIME")) as $c)
		{
			$t = $c->to();
			$nt = new object();
			$nt->set_parent($o->id());
			$nt->set_class_id(CL_EVENT_TIME);
			$nt->set_name($nt->name() . " - ".t("toimumisaeg"));

			$nt->set_prop("start" , $t->prop("start"));
			$nt->set_prop("end" , $t->prop("end"));
			$nt->set_prop("location" , $t->prop("location"));
			$nt->save();

			$nt->connect(array("to" => $t->prop("location") , "reltype" => 1));

			$o->connect(array("to" => $nt->id() , "reltype" => 9));
		}

		return $arr["post_ru"];
	}


	/**
		@attrib name=events_xml is_public="1" all_args=1
	**/
	function events_xml($arr)
	{

		$events = $this->get_xml_events($arr);

		$ret = '<?xml version="1.0" encoding="UTF-8" ?>
<!-- tase = (0->"Yleriigiline", 1->"Kohalik syndmus", 2->"Syndmus valismaal") -->
<syndmused count="'.$events->count().'">';

		foreach(array_reverse($events->arr()) as $o)
		{
			$ret.= $this->to_xml($o);
		}

		$ret.= '
</syndmused>';

		header ("Content-Type: text/xml");
		die($ret);
	}

	function to_xml($o)
	{
		$sectors = $o->connections_from(array(
			"type" => "SECTOR",
		));
		$n = 0;
		$sa = array(); // valdkondade objektid
		foreach($sectors as $c)
		{
			$s = $c->to();
			$sa[$n]["name"] = $s->name();
			$sa[$n]["id"] = $this->teemad[$s->name()];
			$n ++;
		}
		$mail = $phone = $url = $address = "";
		if(is_oid($o->prop("organizer")) && $this->can("view" , $o->prop("organizer")))
		{
			$organiser = obj($o->prop("organizer"));
			if($organiser->class_id() == CL_CRM_PERSON)
			{
				$email = $organiser->prop("email.mail");
				$phone = $organiser->prop("phone.name");
				$url = $organiser -> prop("url.name");
				$address = $organiser->prop("address.name");
			}
			else
			{
				$email = $organiser->prop("email_id.mail");
				$phone = $organiser->prop("phone_id.name");
				$url = $organiser -> prop("url_id.name");
				$address = $organiser->prop("contact.name");
			}
		}

		$ret = '
	<syndmus>
		<id>'.$o->id().'</id>
		<pealkiri><![CDATA[ '.$o->name().' ]]></pealkiri>
		<sissejuhatus><![CDATA[ '.$o->prop("title").' ]]></sissejuhatus>
		<kirjeldus><![CDATA[ '.$o->prop("description").' ]]></kirjeldus>
		<koht id="'.$o->prop("location").'"><![CDATA[ '.$o->prop("location.name").' ]]></koht>
		<maakond id="'.$o->prop("location.address.maakond").'">'.$o->prop("location.address.maakond.name").'</maakond>
		<linn id="'.$o->prop("location.address.linn").'">'.$o->prop("location.address.linn.name").'</linn>
		<riik id="'.$o->prop("location.address.riik").'">'.$o->prop("location.address.riik.name").'</riik>
		<teema1 id="0">'.$sa[0]["name"].'</teema1>
		<teema2 id="0">'.$sa[1]["name"].'</teema2>
		<teema3 id="0">'.$sa[2]["name"].'</teema3>
		<tase>'.$o->prop("level").'</tase>
		<asukoht_vabatekst><![CDATA[  ]]></asukoht_vabatekst>
		<kontakt_nimi><![CDATA[ '.$o->prop("organizer.name").' ]]></kontakt_nimi>
		<kontakt_email><![CDATA[ '.$email.' ]]></kontakt_email>
		<kontakt_tel><![CDATA[ '.$phone.' ]]></kontakt_tel>
		<kontakt_url><![CDATA[ '.$url.' ]]></kontakt_url>
		<kontakt_aadress><![CDATA[ '.$address.' ]]></kontakt_aadress>
		<timestamp>'.date("Y-m-d H:i:s" , $o->modified()).'</timestamp>
		<algallikas>'.$o->prop("utextbox1").'</algallikas>
 		'.$this->times_xml($o).'
	</syndmus>';

		return $ret;
	}

	function times_xml($o)
	{
		$times = $o->connections_from(array("type" => "RELTYPE_EVENT_TIME"));
		$ret = '<ajad count="'.count($times).'">';

		foreach($times as $c)
		{
			$t = $c->to();
			$ret.='
			<aeg>
				<id>'.$t->id().'</id>
				<algus>'.date("Y-m-d H:i:s" , $t->prop("start")).'</algus>
				<lopp>'.date("Y-m-d H:i:s" , $t->prop("end")).'</lopp>
				<markus><![CDATA[  ]]></markus>
				<timestamp>'.date("Y-m-d H:i:s" , $t->modified()).'</timestamp>
			</aeg>';
		}
		$ret.='
		</ajad>';
		return $ret;
	}

	function get_xml_events($arr)
	{
		extract($arr);
		$filter = array(
			"class_id" => array(CL_CALENDAR_EVENT),
			"site_id" => array(),
			"lang_id" => array(),
			"sort_by" => "objects.created ASC",
			"limit" => 100,
		);
		if($starttimestamp)
		{
			$start = mktime(substr($starttimestamp, 8, 2),substr($starttimestamp, 10, 2),substr($starttimestamp, 12, 2),substr($starttimestamp, 4, 2),substr($starttimestamp, 6, 2),substr($starttimestamp, 0, 4));
		}
		else
		{
			$start = time() - 24*3600;
		}

		$filter[] = new object_list_filter(array(
			"logic" => "OR",
			"conditions" => array(
				"CL_CALENDAR_EVENT.RELTYPE_EVENT_TIME.modified" => new obj_predicate_compare(OBJ_COMP_GREATER, $start),
				"modified" => new obj_predicate_compare(OBJ_COMP_GREATER, $start)
			)
		));

		$countys = array(
			1 => "Harjumaa",
			2 => "Hiiumaa",
			3 => "Ida-Virumaa",
			4 => "Jõgevamaa",
			5 => "Järvamaa",
			6 => "Läänemaa",
			7 => "Lääne-Virumaa",
			8 => "Põlvamaa",
			9 => "Pärnumaa",
			10 => "Raplamaa",
			11 => "Saaremaa",
			12 => "Tartumaa",
			13 => "Valgamaa",
			14 => "Viljandimaa",
			15 => "Võrumaa",
		);

		$citys = array(
			1 => "Haapsalu",
			2 => "Jõgeva",
			3 => "Kuressaare",
			4 => "Kärdla",
			5 => "Narva",
			6 => "Paide",
			7 => "Põlva",
			8 => "Pärnu",
			9 => "Rakvere",
			10 => "Rapla",
			11 => "Tallinn",
			12 => "Tartu",
			13 => "Valga",
			14 => "Viljandi",
			15 => "Võru",
			16 => "Jõhvi",
			17 => "Kohtla-Järve",
			18 => "Otepää",
		);

		$this->teemad = array(
			1 => "muusika",
			2 => "klassikaline muusika",
			88=> "vanamuusika" ,
			3=> "pärimusmuusika" ,
			102 => "orkestrimuusika",
			100 => "koorimuusika",
			4 => "jazzmuusika",
			5 => "rock-/popmuusika",
			29 => "alternatiivmuusika",
			32 => "festival",
			6 => "teater",
			8 => "draama" ,
			30 => "komöödia",
			10 => "muusikal",
			9 => "ooper",
			95 => "operett",
			12 => "lasteetendus",
			98 => "koguperelavastus",
			13 => "vabaõhuetendus",
			33 => "alternatiivteater",
			31 => "festival",
			7 => "draama",
			34 => "tants",
			35 => "klassikaline ballett",
			36 => "kaasaegne tants",
			84 => "rahvatants",
			37 => "showtants",
			38 => "tsirkus",
			39 => "festival",
			14 => "film ja foto",
			46 => "dokumentaalfilm",
			48 => "kunstiline film",
			47 => "animafilm",
			114 => "näitus",
			115 => "workshop",
			49 => "festival",
			15 => "kunst",
			43 => "näitus",
			45 => "performance",
			90 => "workshop",
			44 => "festival",
			16 => "kirjandus",
			135 => "raamatuesitlus",
			105 => "pärimuskultuur",
			109 => "rahvatants",
			110 => "pärimusmuusika",
			111 => "käsitöö",
			112 => "rahvakalender",
			113 => "festival",
			17 => "loengud",
			40 => "seminar",
			42 => "konverents",
			91 => "teadus",
			134 => "vestlusõhtu",
			41 => "kursused",
			93 => "sport",
			106 => "näitused",
			116 => "ajalugu",
			117 => "fotograafia",
			118 => "kirjandus",
			119 => "kunst ja arhitektuur",
			120 => "loodus",
			121 => "teadus ja tehnika",
			122 => "teater ja muusika",
			22 => "varia",
			18 => "meelelahutus",
			136 => "laat",
			85 => "muuseumid",
			123 => "festivalid",
		);

//		if($maakond)
//		{
//			$filter["locaton.address.maakond.name"] = $countys[$maakond];
//			$filter[] = new object_list_filter(array(
//				"logic" => "OR",
//				"conditions" => array(
//					"CL_CALENDAR_EVENT.RELTYPE_EVENT_TIME.location.address.maakond.name" =>  $countys[$maakond],
//					"CL_CALENDAR_EVENT.location.address.maakond.name" =>  $countys[$maakond],
//				)
//			));
//		}

//		if($linn)
//		{
		//	$filter["locaton.address.linn.name"] = $citys[$linn];
//			$filter[] = new object_list_filter(array(
//				"logic" => "OR",
//				"conditions" => array(
//					"CL_CALENDAR_EVENT.RELTYPE_EVENT_TIME.location.address.linn.name" =>  $citys[$linn],
//					"CL_CALENDAR_EVENT.location.address.linn.name" =>  $citys[$linn],
//				)
//			));
//		}


		if($teema)
		{
			$filter["CL_CALENDAR_EVENT.RELTYPE_SECTOR.kood"] = $teema;

		}
//keel? est (vaikimisi), eng. Kuvatakse vastavalt eesti või ingliskeelsed sündmused.

		$ol =  new object_list($filter);

		//kuna tuli probleeme, siis peab kirvemeetodil praakima valed linnad ja maakonnad välja
		if($linn || $maakond)
		{

			foreach($ol->arr() as $o)
			{
				$del = 1;
				$adr = array();
				if(is_oid($o->prop("location.address")))
				{
					$adr[] = obj($o->prop("location.address"));
				}
				foreach($o->connections_from(array("type" => "RELTYPE_EVENT_TIME")) as $c)
				{
					$t = $c->to();
					if(is_oid($t->prop("location.address")))
					{
						$adr[] = obj($t->prop("location.address"));
					}
				}

				foreach($adr as $address)
				{
					if($linn && $address->prop("linn.name") == $citys[$linn])
					{
						$del = 0;
						break;
					}

					if($maakond && $address->prop("maakond.name") == $countys[$maakond])
					{
						$del = 0;
						break;
					}
				}
				if($del)
				{
					$ol->remove($o->id());
				}
			}
		}
		return $ol;
	}
}
?>
