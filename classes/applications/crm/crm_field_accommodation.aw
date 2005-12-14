<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_field_accommodation.aw,v 1.3 2005/12/14 12:44:49 ekke Exp $
// crm_field_accommodation.aw - Majutusettev&otilde;te (valdkond) 
/*

@classinfo syslog_type=ST_CRM_FIELD_ACCOMMODATION no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default field=meta
@default method=serialize

@default group=general

	@property type type=select
	@caption Liik

	@property location type=chooser
	@caption Asukoht
	
	@property loc_fromcity type=textbox default=0 size=5
	@caption Kaugus linnast (km)

	@property languages type=chooser multiple=1
	@caption Teeninduskeeled

	@property price_level type=chooser multiple=1
	@caption Hinnaklass
	
	@property price_txt type=textbox size=5
	@caption Hinnavahemik

	@property num_rooms type=textbox size=5
	@caption Tubade arv

	@property num_beds type=textbox size=5
	@caption Voodikohtade arv

	@property has_showers type=checkbox
	@caption Pesemisv&otilde;imalus toas

	@property has_tv type=checkbox 
	@caption Teler toas
	
	@property has_sat_tv type=checkbox 
	@caption SAT-TV

	@property has_phone type=checkbox 
	@caption Telefon toas
	
	@property has_phone_service type=checkbox 
	@caption Telefoniteenus

	@property has_spneeds_rooms type=checkbox
	@caption Invatoad

	@property has_family_rooms type=checkbox
	@caption Peretoad

	@property has_allergy_rooms type=checkbox
	@caption Allergikute toad

	@property has_nonsmoker_rooms type=checkbox
	@caption Mittesuitsetajate toad

	@property has_ccards type=checkbox
	@caption Aktsepteeritakse krediitkaarte

	@property has_pets type=checkbox
	@caption Lemmikloomad lubatud

	@property has_parking type=checkbox
	@caption Parkla

	@property has_parking_safe type=checkbox
	@caption Valvega parkla

	@property has_garage type=checkbox
	@caption Garaa&#158;

	@property has_seminar_rooms type=checkbox
	@caption Seminari- ja/või konverentsiruumid


@default group=services

	@property has_extra_beds type=checkbox
	@caption Lisavoodi võimalus

	@property has_baby_beds type=checkbox
	@caption Beebivoodi võimalus

	@property has_cur_xch type=checkbox
	@caption Valuutavahetus

	@property has_wifi type=checkbox
	@caption WiFi leviala

	@property has_internet type=checkbox
	@caption Interneti kasutamise võimalus

	@property has_safety_boxes type=checkbox
	@caption Hoiulaekad

	@property has_safe type=checkbox
	@caption &#154;eif

	@property has_sauna type=checkbox
	@caption Saun

	@property has_services_beauty type=checkbox
	@caption Iluteenused

	@property has_services_heal type=checkbox
	@caption Raviteenused

	@property has_washing type=checkbox
	@caption Pesu pesemisvõimalus

	@property has_services_carrental type=checkbox
	@caption Autorent

	@property has_services_transport type=checkbox
	@caption Transporditeenus

	@property has_services_guides type=checkbox
	@caption Giiditeenus

	@property ign_spacer type=text store=no
	@caption 

	@property has_grill type=checkbox
	@caption L&otilde;kkeplats/grill

	@property has_camping_tent type=checkbox
	@caption Telkimisvõimalus

	@property has_camping_trailer type=checkbox
	@caption Haagissuvilaga peatumise võimalus

	@property has_camping_caravan type=checkbox
	@caption Karavanikohad

	@property has_camping_rentatent type=checkbox
	@caption Telkide laenutamise võimalus


@default group=catering

	@property food_breakfast_included type=checkbox
	@caption Hommikus&ouml;&ouml;k hinna sees

	@property food_breakfast_canorder type=checkbox
	@caption Hommikus&ouml;&ouml;k tellimisel

	@property food_restaurant type=checkbox
	@caption Restoran

	@property food_cafe type=checkbox
	@caption Kohvik

	@property food_bar type=checkbox
	@caption Lobby baar

	@property food_use_kitchen type=checkbox
	@caption Toidu valmistamise võimalus


@default group=active_vacation

	@property has_playground type=checkbox
	@caption Laste mänguväljak

	@property has_sporting_ground type=checkbox
	@caption Spordiväljak

	@property has_tennis type=checkbox
	@caption Tenniseväljak

	@property has_ballgames type=checkbox
	@caption Pallimängud

	@property has_horseriding type=checkbox
	@caption Ratsutamine

	@property has_rentabike type=checkbox
	@caption Jalgrattalaenutus

	@property has_rentafloatingvehicle type=checkbox
	@caption Veesõiduki laenutus

	@property has_swimming_out type=checkbox
	@caption Ujumisvõimalus (välitingimustes)

	@property has_fishing type=checkbox
	@caption Kalastamine

	@property has_rentarod type=checkbox
	@caption Kalastustarvete rent

	@property has_hiking type=checkbox
	@caption Matkarajad

	@property has_skiing type=checkbox
	@caption Suusarajad

	@property has_renttwoskis type=checkbox
	@caption Suusalaenutus



@groupinfo services caption="Lisateenused"
@groupinfo catering caption="Toitlustamine" 
@groupinfo active_vacation caption="Aktiivne puhkus" 

*/

class crm_field_accommodation extends class_base
{
	function crm_field_accommodation()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/crm/crm_field_accommodation",
			"clid" => CL_CRM_FIELD_ACCOMMODATION
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case 'type':
				$prop["options"] = array(
					'tp_hotel' => t("Hotell"),
					'tp_motel' => t("Motell"),
					'tp_guesthouse' => t("K&uuml;lalistemaja"),
					'tp_hostel' => t("Hostel"),
					'tp_camp' => t("Puhkek&uuml;la ja -laager"),
					'tp_wayhouse' => t("Puhkemaja"),
					'tp_apartment' => t("K&uuml;laliskorter"),
					'tp_homestay' => t("Kodumajutus"),
				);
			break;
			case 'location':
				$prop["options"] = array(
					'loc_city' => t("Kesklinnas"),
					'loc_outside' => t("V&auml;ljaspool kesklinna"),
					'loc_country' => t("V&auml;ljaspool linna"),
				);
			break;
			case 'price_level':
				$prop["options"] = array(
					'price_A' => t("A"),
					'price_B' => t("B"),
					'price_C' => t("C"),
					'price_D' => t("D"),
					'price_E' => t("E"),
				);
			break;
			case 'languages':
				$langs = aw_ini_get('languages.list');
				$prop["options"] = array();
				foreach ($langs as $lang)
				{
					$prop["options"][$lang['acceptlang']] = t($lang['name']);
				}
			break;
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

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
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
