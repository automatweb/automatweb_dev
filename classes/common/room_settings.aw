<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room_settings.aw,v 1.25 2007/07/16 12:30:59 markop Exp $
// room_settings.aw - Ruumi seaded 
/*

@classinfo syslog_type=ST_ROOM_SETTINGS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property max_times_per_day type=textbox field=meta method=serialize
	@caption Maksimaalne aegade arv samal p&auml;eval

	@property hold_not_verifyed type=textbox field=meta method=serialize
	@caption Kinnitamata broneeringut tuleb hoida kinni x minutit

	@property related_room_folder type=relpicker multiple=1 field=meta method=serialize reltype=RELTYPE_RELATED_ROOM_FOLDER
	@caption Seotud ruumide kaust

	@property cal_show_prods type=checkbox ch_value=1 field=meta method=serialize 
	@caption Kuva valitud tooteid kalendrivaates

	@property cal_show_prod_img type=checkbox ch_value=1 field=meta method=serialize 
	@caption Kuva tootepilte kalendrivaates

	@property cal_show_prod_img_ord type=textbox size=5 field=meta method=serialize 
	@caption Tootepildi j&auml;rjekorranumber, mida kuvada

	@property cal_refresh_time type=textbox size=5 field=meta method=serialize 
	@caption Mitme minuti tagant kalendrit refreshida
	
	@property customer_menu type=relpicker field=meta method=serialize reltype=RELTYPE_MENU
	@caption Kataloog kuhu kliendid salvestada
	
	@property min_price_to_all type=checkbox ch_value=1 field=meta method=serialize 
	@caption Miinimumhind m&otilde;jub k&otilde;igile
	
@property disp_bron_len type=checkbox ch_value=1 field=meta method=serialize
@caption &Auml;ra kuva aja pikkust kalendris

@groupinfo whom caption="Kellele kehtib"
@default group=whom
	@property users type=relpicker multiple=1 store=connect reltype=RELTYPE_USER field=meta method=serialize
	@caption Kasutajad

	@property groups type=relpicker multiple=1 store=connect reltype=RELTYPE_GROUP field=meta method=serialize
	@caption Grupid

	@property persons type=relpicker multiple=1 store=connect reltype=RELTYPE_PERSON field=meta method=serialize
	@caption Isikud

	@property cos type=relpicker multiple=1 store=connect reltype=RELTYPE_COMPANY field=meta method=serialize
	@caption Organisatsioonid

	@property sects type=relpicker multiple=1 store=connect reltype=RELTYPE_SECTION field=meta method=serialize
	@caption Osakonnad

	@property profs type=relpicker multiple=1 store=connect reltype=RELTYPE_PROFESSION field=meta method=serialize
	@caption Ametinimetused

	@property everyone type=checkbox ch_value=1 table=objects field=flags
	@caption K&otilde;ik


@groupinfo colours caption="V&auml;rvid"
@default group=colours
@default field=meta 
@default method=serialize

	@property col_buffer type=colorpicker 
	@caption Puhveraja v&auml;rv kalendris

	@property col_web_halfling type=colorpicker 
	@caption Veebis poolelioleva tellimuse v&auml;rvi

	@property col_closed type=colorpicker 
	@caption Kinnise aja v�rvi

	@property col_recent type=colorpicker 
	@caption Hiljuti muudetud reserveeringud

@groupinfo settings caption="Muud seaded"
	@groupinfo settings_gen caption="Muud seaded" parent=settings
	
@default group=settings_gen

	@property comment_pos type=select
	@caption Kuva kommentaar

	@property buffer_time_string type=textbox 
	@caption Puhveraja string

	@property closed_time_string type=textbox 
	@caption Kinnise aja string

	@property bron_popup_detailed type=checkbox ch_value=1
	@caption Broneerimisaken on detailse sisuga

	@property bron_popup_immediate type=checkbox ch_value=1
	@caption Broneerimisaken avaneb kohe kui ajale klikkida

	@property bron_no_popups type=checkbox ch_value=1
	@caption Broneerimiseks ei avata popup aknaid
	
	@property cal_from_today type=checkbox ch_value=1
	@caption Ruumide kalendrid algavad t&auml;nasest, mitte n&auml;dala algusest

	@property no_cust_arrived_pop type=checkbox ch_value=1
	@caption Kliendi saabumise kinnitust pole vaja k&uuml;sida
	
	@property use_existing_person type=checkbox ch_value=1
	@caption Samanimeliste isikute puhul v&otilde;etakse aluseks olemasolev isikuobjekt

	@property bron_required_fields type=table store=no
	@caption Broneeringuobjekti kohustuslikud v&auml;ljad


@groupinfo email caption="Meiliseaded"

	@groupinfo delete_email caption="Kustutamine" parent=email
	@default group=delete_email

		@property send_del_mail type=checkbox ch_value=1
		@caption Saada kustutamise kohta meil


		@property del_mail_to type=textbox
		@caption Kellele kustutamise kohta meil saata

		@property del_mail_from type=textbox 
		@caption Meili from aadress

		@property del_mail_from_name type=textbox
		@caption Meili from nimi

		@property del_mail_subj type=textbox
		@caption Meili subjekt


		@property del_mail_legend type=text
		@caption Meili sisu legend
		
		@property del_mail_ct type=textarea rows=20 cols=50
		@caption Meili sisu

	@groupinfo uv_email caption="Kinnituse eemaldamine" parent=email
	@default group=uv_email

		@property send_uv_mail type=checkbox ch_value=1
		@caption Saada kinnituse kustutamise kohta meil

		@property uv_mail_to type=textbox
		@caption Kellele kustutamise kohta meil saata

		@property uv_mail_from type=textbox 
		@caption Meili from aadress

		@property uv_mail_from_name type=textbox
		@caption Meili from nimi


		@property uv_mail_subj type=textbox
		@caption Meili subjekt
		
		@property uv_mail_legend type=text
		@caption Meili sisu legend
		
		@property uv_mail_ct type=textarea rows=20 cols=50
		@caption Meili sisu


	@groupinfo order_email caption="Tellimusmeil" parent=email
	@default group=order_email

		@property order_mail_from type=textbox 
		@caption Meili from aadress

		@property order_mail_from_name type=textbox
		@caption Meili from nimi

		@property order_mail_subj type=textbox
		@caption Meili subjekt

		@property order_mail_legend type=text
		@caption Meili sisu legend

		@property order_mail_to type=textbox
		@caption Kellele tellimuse kohta meil saata

		@property order_mail_groups type=select multiple=1
		@caption Kasutajagrupid, kelle poolt tehtud broneeringute kohta meil saadetakse

Meili sisu peab saama t�lkida, ilmselt seadetele T�lgi vaade teha lisaks.

@groupinfo grp_settings caption="Gruppide seaded"
@default group=grp_settings

	@property grp_settings type=table store=no no_caption=1

@reltype USER value=1 clid=CL_USER
@caption Kasutaja

@reltype PERSON value=3 clid=CL_CRM_PERSON
@caption Isik

@reltype COMPANY value=4 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype SECTION value=5 clid=CL_CRM_SECTION
@caption Osakond

@reltype PROFESSION value=6 clid=CL_CRM_PROFESSION
@caption Ametinimetus

@reltype RELATED_ROOM_FOLDER value=7 clid=CL_MENU
@caption Seotud ruumide kaust

@reltype GROUP value=8 clid=CL_GROUP
@caption Grupp

@reltype MENU value=9 clid=CL_MENU
@caption Grupp

*/

class room_settings extends class_base
{
	function room_settings()
	{
		$this->init(array(
			"tpldir" => "common/room_settings",
			"clid" => CL_ROOM_SETTINGS
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "del_mail_legend":
				$prop["value"] = t("#ord# - tellimuse sisu");
				break;
			case "uv_mail_legend":
				$prop["value"] = t("#ord# - tellimuse sisu<br>#reason# - kinnituse eemaldamise p&ouml;hjus");
				break;			
			case "order_mail_groups":
				$ol = new object_list(array(
					"class_id" => CL_GROUP,
					"type" => "0",
					"lang_id" => array(),
					"site_id" => array()
				));
				$prop["options"] = $ol->names();
				break;
			case "order_mail_legend":
				$prop["value"] = t("sisu tuleb common/room/preview.tpl failist");
				break;
			case "comment_pos":
				$prop["options"] = array("Alt tekstina" , "Broneerija nime j&auml;rele");
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function callback_generate_scripts()
	{
		$cplink = $this->mk_my_orb("colorpicker",array(),"css");
		return	"var element = 0;\n".
			"function set_color(clr) {\n".
			"document.getElementById(element).value=clr;\n".
			"}\n".
			"function colorpicker(el) {\n".
			"element = el;\n".
			"aken=window.open('$cplink','colorpickerw','height=220,width=310');\n".
			"aken.focus();\n".
			"};\n";

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
		@param $room optional
		room id
	**/
	function get_current_settings($room)
	{
		if(is_oid($room) && $this->can("view" , $room))
		{
			$room = obj($room);
		}
		$oids = safe_array($room->prop("settings"));
		$objs = array();
		foreach($oids as $set_oid)
		{
			$objs[] = obj($set_oid);
		}

		foreach($objs as $settings)
		{
			if (in_array(aw_global_get("uid_oid"), $settings->prop("users")))
			{
				return $settings;
			}
		}

		foreach($objs as $settings)
		{
			if (count(array_intersect($settings->prop("groups"), aw_global_get("gidlist_oid"))))
			{
				return $settings;
			}
		}

		foreach($objs as $settings)
		{
			$pers = $settings->prop("persons");
			if (is_array($pers) && count($pers))
			{
				$cur_p = get_current_person();
				if (in_array($cur_p->id(), $pers))
				{
					return $settings;
				}
			}
		}

		foreach($objs as $settings)
		{
			$cos = $settings->prop("cos");
			if (is_array($cos) && count($cos))
			{
				$cur_co = get_current_company();
				if (in_array($cur_co->id(), $cos))
				{
					return $settings;
				}
			}
		}

		foreach($objs as $settings)
		{
			$sects = $settings->prop("sects");
			if (is_array($sects) && count($sects))
			{
				$cd = get_instance("applications/crm/crm_data");
				$cursec = $cd->get_current_section();
				if (in_array($cursec->id(), $sects))
				{
					return $settings;
				}
			}
		}

		foreach($objs as $settings)
		{
			$profs = $settings->prop("profs");
			if (is_array($profs) && count($profs))
			{
				$cd = get_instance("applications/crm/crm_data");
				$curprof = $cd->get_current_profession();
				if (in_array($curprof->id(), $profs))
				{
					return $settings;
				}
			}
		}

		foreach($objs as $settings)
		{
			if ($settings->prop("everyone"))
			{
				return $settings;
			}
		}
		return null;

		$u = get_instance(CL_USER);
		$curp = $u->get_current_person();
		$curco = $u->get_current_company();
		$cd = get_instance("applications/crm/crm_data");
		$cursec = $cd->get_current_section();
		$curprof = $cd->get_current_profession();
		$cur_grps = aw_global_get("gidlist_oid");

		$ol = new object_list(array(
			"class_id" => CL_ROOM_SETTINGS,
			"lang_id" => array(),
			"oid" => $oids,
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_ROOM_SETTINGS.RELTYPE_USER" => aw_global_get("uid_oid"),
					"CL_ROOM_SETTINGS.RELTYPE_PERSON" => $curp,
					"CL_ROOM_SETTINGS.RELTYPE_COMPANY" => $curco,
					"CL_ROOM_SETTINGS.RELTYPE_SECTION" => $cursec,
					"CL_ROOM_SETTINGS.RELTYPE_PROFESSION" => $curprof,
					"CL_ROOM_SETTINGS.RELTYPE_GROUP" => $cur_grps,
					"CL_ROOM_SETTINGS.everyone" => 1
				)
			))
		));

		if ($ol->count() > 1)
		{
			// the most accurate setting SHALL Prevail!
			$has_co = $has_p = $has_u = $has_all = $has_sec = $has_prof = false;
			foreach($ol->arr() as $o)
			{
				if ($cursec && $o->is_connected_to(array("to" => $cursec)))
				{
					$has_sec = $o;
				}
				if ($curprof && $o->is_connected_to(array("to" => $curprof)))
				{
					$has_prof = $o;
				}

				if ($curco && $o->is_connected_to(array("to" => $curco)))
				{
					$has_co = $o;
				}
				if ($curp && $o->is_connected_to(array("to" => $curp)))
				{
					$has_p = $o;
				}

				if (aw_global_get("uid_oid") && $o->is_connected_to(array("to" => aw_global_get("uid_oid"))))
				{
					$has_u = $o;
				}
				if ($o->prop("everyone"))
				{
					$has_all = $o;
				}

				if (count(array_intersect($o->prop("groups"), $cur_grps)))
				{
					$has_grp = $o;
				}
			}

			if ($has_u)
			{
				return $has_u;
			}
			if ($has_grp)
			{
				return $has_grp;
			}
			if ($has_p)
			{
				return $has_p;
			}
			if ($has_prof)
			{
				return $has_prof;
			}
			if ($has_sec)
			{
				return $has_sec;
			}
			if ($has_co)
			{
				return $has_co;
			}
			if ($has_all)
			{
				return $has_all;
			}
		}

		if ($ol->count())
		{
			return $ol->begin();
		}
	}

	function _init_bron_req_t(&$t)
	{
		$t->define_field(array(
			"caption" => t("Omadus"),
			"align" => "center",
			"name" => "prop"
		));

		$t->define_field(array(
			"caption" => t("N&otilde;utud"),
			"align" => "center",
			"name" => "req"
		));
	}

	function _get_bron_required_fields($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bron_req_t($t);

		$tmp = obj();
		$tmp->set_class_id(CL_RESERVATION);
		$props = $tmp->get_property_list();
	
		$req = $arr["obj_inst"]->meta("bron_req_fields");
		foreach($props as $pn => $pd)
		{
			$t->define_data(array(
				"prop" => $pd["caption"]." ($pn)",
				"req" => html::checkbox(array(
					"name" => "d[$pn][req]",
					"value" => 1,
					"checked" => $req[$pn]["req"] == 1
				))
			));
		}
	}

	function _set_bron_required_fields($arr)
	{
		$arr["obj_inst"]->set_meta("bron_req_fields", $arr["request"]["d"]);
	}

	function _init_grp_settings_t(&$t)
	{
		$t->define_field(array(
			"name" => "grp",
			"caption" => t("Grupp"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "bron_tm",
			"caption" => t("Broneerimisaegade seaded"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "from",
			"caption" => t("Alates"),
			"align" => "center",
			"parent" => "bron_tm"
		));
		$t->define_field(array(
			"name" => "from_ts",
			"caption" => t("Aja&uuml;hik"),
			"align" => "center",
			"parent" => "bron_tm"
		));	
		$t->define_field(array(
			"name" => "to",
			"caption" => t("Kuni"),
			"align" => "center",
			"parent" => "bron_tm"
		));
		$t->define_field(array(
			"name" => "to_ts",
			"caption" => t("Aja&uuml;hik"),
			"align" => "center",
			"parent" => "bron_tm"
		));

		$t->define_field(array(
			"name" => "col",
			"caption" => t("Broneeringu tegijate gruppide v&auml;rvid"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "ask_cust_arrived",
			"caption" => t("K&uuml;sida kliendi saabumise kinnitust"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "confirmed_default",
			"caption" => t("Broneeringud vaikimisi kinnitatud"),
			"align" => "center"
		));
	}

	function _get_grp_settings($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_grp_settings_t($t);

		$opts = array(
			"min" => t("Minut"),
			"hr" => t("Tund"),
			"day" => t("P&auml;ev")
		);
		$cols = $arr["obj_inst"]->meta("grp_cols");
		$settings = $arr["obj_inst"]->meta("grp_settings");

		$d = $arr["obj_inst"]->meta("grp_bron_tm");
		$ol = new object_list(array(
			"class_id" => CL_GROUP,
			"type" => "0",
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"grp" => html::obj_change_url($o),
				"from" => html::textbox(array(
					"name" => "d[".$o->id()."][from]",
					"value" => $d[$o->id()]["from"],
					"size" => 5
				)),
				"to" => html::textbox(array(
					"name" => "d[".$o->id()."][to]",
					"value" => $d[$o->id()]["to"],
					"size" => 5
				)),
				"from_ts" => html::select(array(
					"name" => "d[".$o->id()."][from_ts]",
					"value" => $d[$o->id()]["from_ts"],
					"options" => $opts
				)),
				"to_ts" => html::select(array(
					"name" => "d[".$o->id()."][to_ts]",
					"value" => $d[$o->id()]["to_ts"],
					"options" => $opts
				)),
				"col" => html::textbox(array(
					"name" => "c[".$o->id()."]",
					"size" => 7,
					"value" => $cols[$o->id()],
				))." "."<a href=\"javascript:colorpicker('c_".$o->id()."_')\">".t("Vali")."</a>",
				"ask_cust_arrived" => html::checkbox(array(
					"name" => "e[".$o->id()."][ask_cust_arrived]",
					"value" => 1,
					"checked" => $settings[$o->id()]["ask_cust_arrived"]
				)),
				"confirmed_default" => html::checkbox(array(
					"name" => "e[".$o->id()."][confirmed_default]",
					"value" => 1,
					"checked" => $settings[$o->id()]["confirmed_default"]
				)),
			));
		}
	}

	function _set_grp_settings($arr)
	{
		$arr["obj_inst"]->set_meta("grp_bron_tm", $arr["request"]["d"]);
		$arr["obj_inst"]->set_meta("grp_cols", $arr["request"]["c"]);
		$arr["obj_inst"]->set_meta("grp_settings", $arr["request"]["e"]);
	}

	function get_verified_default_for_group($settings)
	{
		$grp_settings = $settings->meta("grp_settings");
		$gl = aw_global_get("gidlist_pri_oid");
		arsort($gl);
		$gl = array_keys($gl);
		$grp = $gl[1];
		
		if (count($gl) == 1)
		{
			$grp = $gl[0];
		}
		
		return $grp_settings[$grp]["confirmed_default"];
	}
}
?>
