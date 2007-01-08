<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room_settings.aw,v 1.7 2007/01/08 14:52:43 kristo Exp $
// room_settings.aw - Ruumi seaded 
/*

@classinfo syslog_type=ST_ROOM_SETTINGS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property max_times_per_day type=textbox field=meta method=serialize
@caption Maksimaalne aegade arv samal p&auml;eval

@property hold_not_verifyed type=textbox field=meta method=serialize
@caption Kinnitamata broneeringut tuleb hoida kinni x minutit

@groupinfo whom caption="Kellele kehtib"
@default group=whom
	@property users type=relpicker multiple=1 store=connect reltype=RELTYPE_USER field=meta method=serialize
	@caption Kasutajad

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
	@caption Kinnise aja värvi

	@property col_by_grp type=table store=no
	@caption Broneeringu tegijate gruppide v&auml;rvid

@groupinfo settings caption="Muud seaded"
	@groupinfo settings_gen caption="Muud seaded" parent=settings
	@groupinfo settings_grp caption="Broneerimisaegade seaded" parent=settings
	
@default group=settings_gen

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

	@property bron_required_fields type=table store=no
	@caption Broneeringuobjekti kohustuslikud v&auml;ljad

@default group=settings_grp

	@property grp_bron_time_table type=table store=no no_caption=1

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
			$oids = $room->prop("settings");
		}
		$u = get_instance(CL_USER);
		$curp = $u->get_current_person();
		$curco = $u->get_current_company();
		$cd = get_instance("applications/crm/crm_data");
		$cursec = $cd->get_current_section();
		$curprof = $cd->get_current_profession();

		$ol = new object_list(array(
			"class_id" => CL_ROOM_SETTINGS,
			"lang_id" => array(),
			"oid" => $oids,
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_SETTINGS.RELTYPE_USER" => aw_global_get("uid_oid"),
					"CL_CRM_SETTINGS.RELTYPE_PERSON" => $curp,
					"CL_CRM_SETTINGS.RELTYPE_COMPANY" => $curco,
					"CL_CRM_SETTINGS.RELTYPE_SECTION" => $cursec,
					"CL_CRM_SETTINGS.RELTYPE_PROFESSION" => $curprof,
					"CL_CRM_SETTINGS.everyone" => 1
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

				if ($o->is_connected_to(array("to" => $curco)))
				{
					$has_co = $o;
				}
				if ($o->is_connected_to(array("to" => $curp)))
				{
					$has_p = $o;
				}
				if ($o->is_connected_to(array("to" => aw_global_get("uid_oid"))))
				{
					$has_u = $o;
				}
				if ($o->prop("everyone"))
				{
					$has_all = $o;
				}
			}

			if ($has_u)
			{
				return $has_u;
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

	function _init_col_by_grp(&$t)
	{
		$t->define_field(array(
			"name" => "grp",
			"caption" => t("Grupp"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "col",
			"caption" => t("V&auml;rv"),
			"align" => "center"
		));
	}

	function _get_col_by_grp($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_col_by_grp($t);

		$ol = new object_list(array(
			"class_id" => CL_GROUP,
			"lang_id" => array(),
			"site_id" => array(),
			"type" => "0"
		));
		$cols = $arr["obj_inst"]->meta("grp_cols");
		foreach($ol->arr() as $o)
		{
			$tx = "<a href=\"javascript:colorpicker('c_".$o->id()."_')\">".t("Vali")."</a>";


			$t->define_data(array(
				"grp" => html::obj_change_url($o),
				"col" => html::textbox(array(
					"name" => "c[".$o->id()."]",
					"size" => 7,
					"value" => $cols[$o->id()],
				))." ".$tx
			));
		}
	}

	function _set_col_by_grp($arr)
	{
		$arr["obj_inst"]->set_meta("grp_cols", $arr["request"]["c"]);
	}

	function _init_grp_bron_time_t(&$t)
	{
		$t->define_field(array(
			"name" => "grp",
			"caption" => t("Grupp"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "from",
			"caption" => t("Alates"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "from_ts",
			"caption" => t("Aja&uuml;hik"),
			"align" => "center"
		));	
		$t->define_field(array(
			"name" => "to",
			"caption" => t("Kuni"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "to_ts",
			"caption" => t("Aja&uuml;hik"),
			"align" => "center"
		));
	}

	function _get_grp_bron_time_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_grp_bron_time_t($t);

		$opts = array(
			"min" => t("Minut"),
			"hr" => t("Tund"),
			"day" => t("P&auml;ev")
		);

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
			));
		}
	}

	function _set_grp_bron_time_table($arr)
	{
		$arr["obj_inst"]->set_meta("grp_bron_tm", $arr["request"]["d"]);
	}
}
?>
