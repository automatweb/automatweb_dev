<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/events_manager/events_manager.aw,v 1.2 2007/05/16 14:17:36 markop Exp $
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
	
		@layout events_top type=hbox closeable=1 area_caption=&Uuml;ldandmed width=30%:70%

		@layout events_top_left type=vbox parent=events_top closeable=1 area_caption=S&uuml;ndmuste&nbsp;Otsing
		#nupud: Lisa uus, Kustuta märgistatud sündmused (confirmation "Olete kindel, et soovite kustudada kõik valitud sündmused?"), Sündmuste väljatrükk (sisu selgub hiljem), Arhiiv (toggle. n2itab tabelis syndmusi mis toimund. nupu nimi muutub: "Uued"), Järjesta laekumise järgi (järjestab syndmused created j2rgi kahanevalt)
		#	Otsinguvorm vasakul (otsingutulemused tabelis):		
			
			@property e_find_sections type=select multiple=1 parent=events_top_left
			@caption Valdkonnad
			# seostatud valdkonnaobjektid
		
			@property e_find_editor type=select  parent=events_top_left
			@caption Toimetaja
			#	valikud: Toimetajad + Veebikasutaja
		 
			@property e_find_text type=textbox parent=events_top_left size=20
			@caption  Tekst
			#otsing syndmuste pealkirjadest, sissejuhatustest, kirjeldustest
			
			@property e_find_news type=chooser no_caption=1 parent=events_top_left
			@caption Arhiivist, Uute hulgast
			# valikud: Arhiivist, Uute hulgast
			
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
	  #nupud: Lisa uus
	
	@property organiser_table type=table no_caption=1
	@caption Korraldajate tabel
	  #objektid vastava kataloogi alt
			
@groupinfo sections caption="Valdkonnad" submit=no
@default group=sections
	@property sections_tb type=toolbar no_caption=1
	@caption  toolbar
	  #nupud: Lisa uus
			
@groupinfo editors caption="Toimetajad" submit=no
@default group=editors

	@property sections_tb type=toolbar no_caption=1
	@caption Toimetajad toolbar
	  #nupud: Lisa uus
			
@groupinfo settings caption="seaded"
@default group=settings
	
	@property event_menu type=relpicker reltype=RELTYPE_EVENT_MENU
	@caption S&uuml;ndmuste kataloog
	
	@property places_menu type=relpicker reltype=RELTYPE_PLACE_MENU
	@caption Toimumiskohtade kataloog

	@property organiser_menu type=relpicker reltype=RELTYPE_ORGANISER_MENU
	@caption Korraldajate kataloog

	@property event_menu type=relpicker reltype=RELTYPE_EVENT_MENU
	@caption Valdkondade kataloog

	@property section_menu type=relpicker reltype=RELTYPE_SECTION_MENU
	@caption Valdkondade kataloog

	@property section type=relpicker multiple=1 reltype=RELTYPE_SECTION
	@caption Valdkonnad

	@property owner type=relpicker reltype=RELTYPE_OWNER
	@caption Omanik
	
	@property languages type=relpicker multiple=1 reltype=RELTYPE_LANGUAGE
	@caption Sinu keeled
	  #valikus k6ik aktiivsed keeled. multiple.
	
	@property mapserver_url type=textbox
	@caption Kaardiserveri url

#RELTYPES

@reltype SECTION value=1 clid=CL_CRM_SECTION
@caption Valdkond

@reltype EVENT_MENU value=2 clid=CL_MENU
@caption S&uuml;ndmuste kataloog

@reltype PLACE_MENU value=3 clid=CL_MENU
@caption Toimumiskohtade kataloog

@reltype ORGANISER_MENU value=4 clid=CL_MENU
@caption Korraldajate kataloog

@reltype SECTION_MENU value=5 clid=CL_MENU
@caption Valdkondade kataloog

@reltype OWNER value=6 clid=CL_CRM_COMPANY
@caption Omanik

@reltype LANGUAGE value=7 clid=CL_LANGUAGE
@caption Keel


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
			case "events_tb":
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
					"img" => "new.gif",
					"action" => "print_events",
					"tooltip" => t("S&uuml;ndmuste v&auml;ljatr&uuml;kk"),
				));
				$arr["prop"]["vcl_inst"]->add_button(array(
					"name" => "archive",
					"img" => "new.gif",
					"action" => "show_archived",
					"tooltip" => t("Arhiiv"),
				));
				$arr["prop"]["vcl_inst"]->add_button(array(
					"name" => "sort",
					"img" => "new.gif",
					"action" => "sort_by_time",
					"tooltip" => t("J&auml;rjesta laekumise j&auml;rgi"),
				));
				break;
			case "e_find_sections":
				$section_list = new object_list();
				if(is_array($arr["obj_inst"]->prop("section")) || is_oid($arr["obj_inst"]->prop("section")))
				{
					$section_list->add($arr["obj_inst"]->prop("section"));
					$prop["options"] = $section_list->names();
				}
				break;
		
			case "e_find_editor":
				$prop["options"] = array(t("Toimetajad"),t("Veebikasutaja"));
				break;
					
			case "e_find_news":
				$prop["options"] = array(t("Arhiivist"),t("Uute hulgast"));
				break;
			case "event_table":
				$this->_get_event_table($arr);
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
			//-- set_property --//
		}
		return $retval;
	}	

	function _get_event_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_event_table($t);
		$cal_event = get_instance(CL_CALENDAR_EVENT);
		
		$ol = new object_list(array(
			"class_id" => array(CL_CALENDAR_EVENT,CL_TASK),
			"site_id" => array(),
			"lang_id" => array(),
		));
		
		foreach($ol->arr() as $o)
		{
			$sec = $o->get_first_obj_by_reltype("RELTYPE_SECTION");
			$publish = html::href(array("url" => "#" , "title" => t("Avalda") , "caption" => t("Avalda")));
			$change_url = html::obj_change_url($o , t("Muuda"));
			
			$t->define_data(array(
				"name" => $o->name(),
				"time" => date("d.m.Y" , $o->prop("start1")). "-" .date("d.m.Y" , $o->prop("end")),
				"section" => (is_object($sec))?$sec->name():"",
				"level" => $cal_event->level_options[$o->prop("level")],
				"tasks" => $publish . " " . $change_url,
			));
		}
		
		
		/*Pealkiri		calendar_event::name
Aeg		calendar_event::Toimumisaeg sortable
Valdkond		calendar_event::Valdkonnad (esimene seos)
Tase		calendar_event::Tase
Regioon		calendar_event::Toimumiskoht::Aadress::Maakond (+Linn kui on m22ratud)
Tegevused		avalda (paneb rea syndmusel Avaldatud=1), muuda (viib syndmuse muutmisvormile), klooni??
Valik		checkboxid
*/

	}

	function _init_event_table(&$t)
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
			"name" => "section",
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
		$t->define_field(array(
			"name" => "tasks",
			"caption" => t("Tegevused"),
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

//-- methods --//
}
?>
