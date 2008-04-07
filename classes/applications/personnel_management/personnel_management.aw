<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management.aw,v 1.27 2008/04/07 11:45:46 instrumental Exp $
// personnel_management.aw - Personalikeskkond 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT relationmgr=yes r2=yes no_status=1 no_comment=1 prop_cb=1 maintainer=kristo

@default group=general
@default table=objects

	@groupinfo general2 caption="&Uuml;ldine" parent=general
	@default group=general2

		@property name type=textbox field=name
		@caption Nimi

@default field=meta
@default method=serialize

		@property persons_fld type=relpicker reltype=RELTYPE_MENU
		@caption Isikute kaust

		@property offers_fld type=relpicker reltype=RELTYPE_MENU
		@caption T&ouml;&ouml;pakkumiste kaust

		@property professions_fld type=relpicker reltype=RELTYPE_MENU
		@caption Ametinimetuste kaust

		@property shools_fld type=relpicker reltype=RELTYPE_MENU
		@caption Koolide kaust

		@property fields type=relpicker reltype=RELTYPE_SECTORS
		@caption Tegevusvaldkonnad

		@property person_ot type=relpicker reltype=RELTYPE_PERSON_OT
		@caption Isikute objektit&uuml;&uuml;p

		@property crmdb type=relpicker reltype=RELTYPE_CRM_DB
		@caption Kliendibaas

		@property owner_org type=relpicker reltype=RELTYPE_OWNER_ORG
		@caption Omanikorganisatsioon

		@property sysdefault_pm type=checkbox ch_value=1 store=no
		@caption Default personalikeskkond

		@property mandatory_controller type=relpicker reltype=RELTYPE_CFGCONTROLLER
		@caption Kohustuslikkuse kontroller

	@groupinfo search_conf caption="Otsingu seaded" parent=general
	@default group=search_conf

		@property cv_tpl type=select
		@caption CV templeit

		@property search_conf_tbl type=table
		@caption Tulemuste tabeli v&auml;ljad

	@groupinfo skill_conf caption="Oskuste seaded" parent=general
	@default group=skill_conf

		@property skill_manager type=relpicker reltype=RELTYPE_SKILL_MANAGER
		@caption Oskuste haldur
		
		@property skills_fld type=relpicker reltype=RELTYPE_MENU
		@caption Oskuste kaust

		@property drivers_license type=select multiple=1
		@caption Juhilubade kategooriad

	@groupinfo lang_conf caption="Keelte seaded" parent=general
	@default group=lang_conf

		@property lang_tb type=toolbar no_caption=1 store=no

		@property lang_tbl type=table no_caption=1 store=no

-------------------T88OTSIJAD-----------------------
@groupinfo employee caption="T&ouml;&ouml;otsijad" submit=no
@default group=employee,candidate

@property employee_tb type=toolbar no_caption=1

@property add_employee type=hidden store=no

@layout employee type=hbox width=20%:80%

	@layout employee_left type=vbox parent=employee

		@layout employee_tree type=vbox closeable=1 parent=employee_left area_caption=T&ouml;&ouml;otsijad

			@property employee_tree type=treeview no_caption=1 parent=employee_tree

		@layout employee_search type=vbox closeable=1 parent=employee_left area_caption=Otsing

			@layout employee_search_1 type=vbox parent=employee_search

				@property search_save type=relpicker reltype=RELTYPE_SEARCH_SAVE parent=employee_search_1 captionside=top search_button=1 no_edit=1 delete_button=1
				@caption Varasem otsing

			@layout isikuandmed type=vbox closeable=1 parent=employee_search area_caption=Isikuandmed

				@property cv_name type=textbox size=18 parent=isikuandmed captionside=top store=no
				@caption Nimi

				@property cv_tel type=textbox size=18 parent=isikuandmed captionside=top store=no
				@caption Telefon

				@property cv_email type=textbox size=18 parent=isikuandmed captionside=top store=no
				@caption E-post

				@property cv_addinfo type=textbox size=18 parent=isikuandmed captionside=top store=no
				@caption Lisainfo

				@property cv_gender type=chooser size=18 parent=isikuandmed captionside=top store=no
				@caption Sugu

				@layout cv_age type=hbox parent=isikuandmed

					@property cv_age_from type=textbox parent=cv_age captionside=top size=4 store=no
					@caption Vanus alates

					@property cv_age_to type=textbox parent=cv_age captionside=top size=4 store=no
					@caption Vanus kuni

			@layout haridus type=vbox parent=employee_search area_caption=Haridus closeable=1

				@property cv_edulvl type=select parent=haridus captionside=top store=no
				@caption Haridustase

				@property cv_acdeg type=select parent=haridus captionside=top store=no
				@caption Akadeemiline kraad

				@property cv_schl type=textbox size=18 parent=haridus captionside=top store=no
				@caption Kool

				@property cv_schl_area type=textbox size=18 parent=haridus captionside=top store=no
				@caption Valdkond

				@property cv_schl_stat type=chooser size=18 parent=haridus captionside=top store=no
				@caption Staatus

			@layout soovitud_t88 type=vbox parent=employee_search closeable=1 area_caption=Soovitud&nbsp;t&ouml;&ouml;

				@property cv_job type=textbox size=18 parent=soovitud_t88 captionside=top store=no
				@caption Ametinimetus

				@property cv_paywish type=textbox parent=soovitud_t88 captionside=top size=6 store=no
				@caption Palk

				@property cv_field type=textbox size=18 multiple=1 orient=vertical parent=soovitud_t88 captionside=top store=no
				@caption Tegevusala

				@property cv_type type=textbox size=18 multiple=1 parent=soovitud_t88 captionside=top store=no
				@caption T&ouml;&ouml; liik

				@property cv_location type=textbox size=18 multiple=1 orient=vertical parent=soovitud_t88 captionside=top store=no
				@caption T&ouml;&ouml;tamise piirkond

				@property cv_load type=classificator multiple=1 orient=vertical reltype=RELTYPE_JOB_WANTED_LOAD parent=soovitud_t88 captionside=top store=no
				@caption T&ouml;&ouml;koormus

			@layout oskused type=vbox parent=employee_search area_caption=Oskused closeable=1

				@property cv_personality type=textbox size=18 parent=oskused captionside=top store=no
				@caption Isikuomadused

				@property cv_mother_tongue type=select parent=oskused captionside=top store=no
				@caption Emakeel

				@property cv_lang_exp type=select multiple=1 parent=oskused captionside=top store=no
				@caption Keeleoskus

				@property cv_lang_exp_lvl type=select parent=oskused captionside=top store=no
				@caption Keeleoskuse tase

				@property cv_exps_n_lvls type=text parent=oskused no_caption=1 store=no

				@property cv_driving_licence size=5 type=text multiple=1 parent=oskused captionside=top store=no
				@caption Juhiload

			@layout t88kogemus type=vbox parent=employee_search area_caption=T&ouml;&ouml;kogemus closeable=1

				@property cv_previous_rank type=textbox size=18 parent=t88kogemus captionside=top store=no
				@caption T&ouml;&ouml;kogemuse ametinimetus

				@property cv_previous_field type=textbox size=18 parent=t88kogemus captionside=top store=no
				@caption Valdkond

				@property cv_company type=textbox size=18 parent=t88kogemus captionside=top store=no
				@caption Ettev&otilde;te

				@property cv_recommenders type=textbox size=18 parent=t88kogemus captionside=top store=no
				@caption Soovitajad

				@property cv_comments type=textbox size=18 parent=t88kogemus captionside=top store=no
				@caption Kommentaarid

			@layout cv_search_buttons type=hbox parent=employee_search

				@property cv_search_button type=submit parent=cv_search_buttons store=no
				@caption Otsi

				@property cv_search_button_save_search type=submit parent=cv_search_buttons store=no
				@caption Otsi ja salvesta

				# @property cv_search_button_save type=text parent=cv_search_buttons store=no
				# @property cv_search_button_save type=submit parent=cv_search_buttons store=no action=cv_search_and_save
				# @caption Otsi ja salvesta

	@layout employee_right type=vbox parent=employee

			@property employee_tbl type=table no_caption=1 parent=employee_right store=no

----------------------------------------

# @groupinfo employee_list caption="Nimekiri" parent=employee submit=no
# @default group=employee_list

# @property employee_list_toolbar type=toolbar no_caption=1

# @layout employee_list type=hbox width=15%:85%

# @property employee_list_tree type=treeview no_caption=1 parent=employee_list

# @property employee_list_table type=table no_caption=1 parent=employee_list

----------------------------------------

@groupinfo candidate caption="Kandideerijad" submit=no

# All the props are defined in the employee group.

----------------------------------------
@groupinfo offers caption="T&ouml;&ouml;pakkumised" submit=no

@groupinfo offers_ parent=offers caption="&Uuml;ldine" submit=no
@default group=offers_

	@property offers_toolbar type=toolbar no_caption=1

	@layout offers type=hbox width=15%:85%

		@layout offers_tree_n_search type=vbox parent=offers

			@layout offers_tree type=vbox parent=offers_tree_n_search closeable=1 area_caption=T&ouml;&ouml;pakkumised

				@property offers_tree type=treeview no_caption=1 parent=offers_tree
			
			@layout offers_search type=vbox parent=offers_tree_n_search closeable=1 area_caption=T&ouml;&ouml;pakkumiste&nbsp;otsing

				@forminfo offers_search onload=init_offers_search onsubmit=do_offers_search method=post

				@layout os_top type=vbox parent=offers_search

					@property os_pr type=textbox parent=os_top captionside=top store=no size=18 form=offers_search
					@caption Ametikoht

					@property os_county type=textbox parent=os_top captionside=top store=no size=18 form=offers_search
					@caption Maakond

					@property os_city type=textbox parent=os_top captionside=top store=no size=18 form=offers_search
					@caption Linn

				@layout os_dl_layout type=vbox parent=offers_search

					@property os_dl_from type=date_select store=no parent=os_dl_layout captionside=top format=day_textbox,month_textbox,year_textbox form=offers_search
					@caption T&auml;htaeg alates

					@property os_dl_to type=date_select store=no parent=os_dl_layout captionside=top format=day_textbox,month_textbox,year_textbox form=offers_search
					@caption T&auml;htaeg kuni

				@property os_status type=chooser store=no parent=offers_search captionside=top form=offers_search
				@caption Staatus

				@property os_sbt type=submit parent=offers_search no_caption=1 form=offers_search
				@caption Otsi

	@property offers_table type=table no_caption=1 parent=offers

@groupinfo offers_conf parent=offers caption="Seaded"
@default group=offers_conf

	@property default_offers_cfgform type=relpicker reltype=RELTYPE_DEFAULT_OFFERS_CFGFORM
	@caption Default seadete vorm

----------------------------------------

@groupinfo actions caption="Tegevused" submit=no
@default group=actions

@property treeview3 type=text no_caption=1 default=asd

----------------------------------------

@groupinfo clients caption="Kliendid" submit=no
@default group=clients

@property treeview4 type=text no_caption=1 default=asd

---------------RELATION DEFINTIONS-----------------

@reltype MENU value=1 clid=CL_MENU
@caption Kaust

@reltype CRM_DB value=2 clid=CL_CRM_DB
@caption Kliendibaas

@reltype SECTORS value=3 clid=CL_METAMGR
@caption Tegevusvaldkonnad

@reltype PERSON_OT value=4 clid=CL_OBJECT_TYPE
@caption Objektit&uuml;&uuml;p

@reltype OWNER_ORG value=5 clid=CL_CRM_COMPANY
@caption Omanikorganisatsioon

@reltype SEARCH_SAVE value=6 clid=CL_PERSONNEL_MANAGEMENT_CV_SEARCH_SAVED
@caption Otsingu salvestus

@reltype DEFAULT_OFFERS_CFGFORM value=7 clid=CL_CFGFORM
@caption T&ouml;&ouml;pakkumiste default-seadetevorm

@reltype JOB_WANTED_LOAD value=8 clid=CL_META
@caption Soovitud t&ouml;&ouml;koormus

@reltype SKILL_MANAGER value=9 clid=CL_CRM_SKILL_MANAGER
@caption Oskuste haldur

@reltype CFGCONTROLLER value=10 clid=CL_CFGCONTROLLER
@caption Kontroller

*/

class personnel_management extends class_base
{
	function personnel_management()
	{
		$this->init(array(
			"clid" => CL_PERSONNEL_MANAGEMENT,
			"tpldir" => "applications/personnel_management/personnel_management",
		));
		
		$this->search_vars = array(
			"name" => "Nimi",
			"age" => "Vanus",
			"gender" => "Sugu",
			"apps" => "Kandideerimised",
			"modtime" => "Muutmise aeg",
			"change" => "Muuda"
		);
	}

	function callback_on_load($arr)
	{
		if(!$arr["new"])
		{
			if($this->can("view", $arr["request"]["id"]))
			{
				$obj = obj($arr["request"]["id"]);
				if($this->can("view", $obj->prop("owner_org")))
				{
					$this->owner_org = $obj->prop("owner_org");
				}
				if($this->can("view", $obj->prop("persons_fld")))
				{
					$this->persons_fld = $obj->prop("persons_fld");
				}
				if($this->can("view", $obj->prop("offers_fld")))
				{
					$this->offers_fld = $obj->prop("offers_fld");
				}
			}
		}
	}

	function callback_mod_tab($arr)
	{
		if(!$arr["new"] && $this->owner_org)
		{
			if($arr["id"] == "actions")
			{
				$arr["link"] = $this->mk_my_orb("change", array("id" => $this->owner_org, "group" => "overview"), CL_CRM_COMPANY);
			}
			elseif($arr["id"] == "clients")
			{
				$arr["link"] = $this->mk_my_orb("change", array("id" => $this->owner_org, "group" => "relorg"), CL_CRM_COMPANY);
			}
		}
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		get_instance("core/icons");
		$person_language_inst = get_instance(CL_CRM_PERSON_LANGUAGE);

		if(is_oid($arr["request"]["search_save"]))
		{	// If 'Varasem otsing' is selected.
			$sso = obj($arr["request"]["search_save"]);
			$arr["request"] += $sso->meta();
		}

		switch($prop["name"])
		{
			case "drivers_license":
				$prop["options"] = get_instance(CL_CRM_PERSON)->drivers_licence_original_categories;
				break;

			case "search_save":
				$u = obj(user::get_current_user());
				$ssol = new object_list(array(
					"class_id" => CL_PERSONNEL_MANAGEMENT_CV_SEARCH_SAVED,
					"parent" => array(),
					"status" => array(),
					"createdby" => $u->name(),
				));
				$prop["options"] = array(0 => t("--vali--")) + $ssol->names();
				$prop["onchange"] = "submit_changeform();";
				$prop["value"] = $arr["request"]["search_save"];
				break;

			case "cv_search_button_save_search":
				$prop["onclick"] = "aw_get_el('cv_search_saved_name').value=prompt('".t("Palun sisestage salvestatava otsingu nimi:")."');";
				break;

			case "employee_tb":
				if($arr["request"]["group"] == "candidate" && is_oid($arr["request"]["ofr_id"]))
					$this->candidate_toolbar($arr);
				if($arr["request"]["group"] == "employee")
					$this->employee_tb($arr);
				break;

			case "employee_tbl":
				if($arr["request"]["group"] == "candidate")
					$this->candidate_table($arr);
				if($arr["request"]["group"] == "employee")
					$this->employee_tbl($arr);
				break;

			case "employee_tree":
				if($arr["request"]["group"] == "candidate")
					$this->candidate_tree($arr);
				if($arr["request"]["group"] == "employee")
					$this->employee_tree($arr);
				break;

			case "cv_lang_exp_lvl":
				$prop["options"][0] = t("--vali--");
				$prop["options"] += $person_language_inst->lang_lvl_options;

				$s = $arr['request'][$prop["name"]];
				$this->dequote(&$s);
				$prop['value'] = $s;
				break;

			case "sysdefault_pm":
				if($arr["obj_inst"]->id() == $this->get_sysdefault())
					$prop["value"] = $prop["ch_value"];
				break;

			case "cv_gender":
				$prop["options"] = array(
					0 => t("K&otilde;ik"),
					1 => t("Mees"),
					2 => t("Naine"),
				);
			case "cv_tel":
			case "cv_email":
			case "cv_addinfo":
			case "cv_schl":
			case "cv_schl_area":
			case "cv_search_button":
			case "cv_name":
			case "cv_company":
			case "cv_job":
			case "cv_paywish":
			case "cv_paywish2":
			case "cv_field":
			case "cv_previous_field":
			case "cv_type":
			case "cv_location":
			case "cv_load":
			case "cv_personality":
			case "cv_comments":
			case "cv_recommenders":
			case "cv_age_from":
			case "cv_age_to":
			case "cv_previous_rank":

			case "os_pr":
			case "os_county":
			case "os_city":
			case "os_dl_from":
				$s = $arr['request'][$prop["name"]];
				$this->dequote(&$s);
				$prop['value'] = $s;
				break;
				
			case "os_dl_to":
				// Default is 1 month forward.
				$prop["value"] = mktime(0, 0, 0, date("m") + 1, date("d"), date("Y"));
				break;

			case "cv_driving_licence":
				$cats = get_instance(CL_CRM_PERSON)->drivers_licence_categories;
				foreach($cats as $k => $c)
				{
					$prop["value"] .= html::checkbox(array(
						"name" => "cv_driving_licence[".$k."]",
						"value" => $k,
						"checked" => $arr["request"]["cv_driving_licence"][$k] == $k,
						"caption" => t($c),
					))."&nbsp;";
				}
				break;

			case "cv_mother_tongue":
			case "cv_lang_exp":
				$options = array();
				$options[0] = t("--vali--");
				$options += $this->get_languages();
				$prop["options"] = $options;

				$s = $arr['request'][$prop["name"]];
				$this->dequote(&$s);
				$prop['value'] = $s;
				break;

			case "cv_edulvl":
				$person_inst = get_instance(CL_CRM_PERSON);
				$prop["options"] = $person_inst->edulevel_options;

				$s = $arr['request'][$prop["name"]];
				$this->dequote(&$s);
				$prop['value'] = $s;
				break;

			case "cv_acdeg":
				$person_inst = get_instance(CL_CRM_PERSON);
				$prop["options"] = $person_inst->academic_degree_options;

				$s = $arr['request'][$prop["name"]];
				$this->dequote(&$s);
				$prop['value'] = $s;
				break;

			case "cv_schl_stat":
				$prop["options"] = array(
					0 => t("K&otilde;ik"),
					1 => t("Omandamisel"),
					2 => t("L&otilde;petanud"),
				);

				$s = $arr['request'][$prop["name"]];
				$this->dequote(&$s);
				$prop['value'] = $s;
				break;

			case "offers_table":
				if($arr["request"]["os_sbt"])
					$this->offers_table_srch($arr);
				else
					$this->offers_table($arr);
				break;

			case "employee_list_tree":
				return PROP_IGNORE;
				$this->employee_list_tree($arr);
				break;

			case "os_status":
				$prop["options"] = array(
					"0" => t("K&otilde;ik"),
					"2" => t("Aktiivsed"),
					"1" => t("Mitteaktiivne"),
				);
				$prop["value"] = $arr['request'][$prop["name"]];
				break;
		}
		return $retval;
	}

	function _get_cv_tpl($arr)
	{
		$arr["prop"]["options"] = crm_person::get_cv_tpl();
	}

	function _get_cv_exps_n_lvls($arr)
	{
		$skill_manager_inst = get_instance(CL_CRM_SKILL_MANAGER);
		$skill_manager_id = $arr["obj_inst"]->prop("skill_manager");
		$skills = $skill_manager_inst->get_skills(array("id" => $skill_manager_id));

		$ret = "";
		foreach($skills[$skill_manager_id] as $id => $data)
		{
			$options = array(0 => t("--vali--"));
			$disabled_options = array();
			$this->add_skill_options(&$skills, &$options, &$disabled_options, $id, 0);
			$skill = obj($id);
			$ol = new object_list(array(
				"class_id" => CL_META,
				"parent" => $skill->prop("lvl_meta"),
				"status" => object::STAT_ACTIVE,
				"lang_id" => array(),
				"sort_by" => "jrk",
			));
			// Need to add caption manually.
			$ret .= $data["name"]."<br />";
			$ret .= html::select(array(
				"name" => "cv_exp[".$id."]",
				"options" => $options,
				"disabled_options" => $disabled_options,
				"selected" => $arr["request"]["cv_exp"][$id],
			))."<br />";
			// Need to add caption manually.
			$ret .= t("Tase")."<br />";
			$ret .= html::select(array(
				"name" => "cv_exp_lvl[".$id."]",
				"options" => array(0 => t("--vali--")) + $ol->names(),
				"caption" => t("Tase"),
				"multiple" => 1,
				"size" => 3,
				"selected" => $arr["request"]["cv_exp_lvl"][$id],
			))."<br />";
		}
		$arr["prop"]["value"] = $ret;
	}

	function _get_lang_tb($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_new_button(array(
			"clids" => CL_LANGUAGE,
			"pt" => $arr["obj_inst"]->id(),
		));
		$t->add_save_button();
	}

	function _get_lang_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "lang",
			"caption" => t("Keel"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Kasuta keelt personalikeskkonnas"),
			"align" => "center",
		));
		$odl = new object_data_list(
			array(
				"class_id" => CL_LANGUAGE,
				"parent" => array(),
				"lang_id" => array(),
				"site_id" => array(),
				"status" => array(),
			),
			array(
				CL_LANGUAGE => array("name"),
			)
		);
		$lang_tbl = $this->get_lang_conf(array("id" => $arr["obj_inst"]->id()));
		foreach($odl->arr() as $oid => $odata)
		{
			$t->define_data(array(
				"lang" => $odata["name"],
				"sel" => html::checkbox(array(
					"name" => "lang_tbl[".$oid."]",
					"value" => 1,
					"checked" => $lang_tbl[$oid],
				)),
			));
		}
		$t->sort_by(array(
			"field" => "jrk",
			"sorder" => "ASC",
		));
	}

	function employee_tree($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$mcaps = array(
			"area" => "Piirkonnad",
			"county" => "Maakonnad",
			"city" => "Linnad",
		);
		$clids = array(
			"county" => CL_CRM_COUNTY,
			"city" => CL_CRM_CITY,
			"area" => CL_CRM_AREA,
		);
		$total = 0;
		foreach($mcaps as $k => $d)
		{
			$objs = new object_list(array(
				"parent" => array(),
				"class_id" => $clids[$k],
				"lang_id" => array(),
				"site_id" => array(),
			));
			$tot = 0;
			foreach($objs->arr() as $o)
			{
				$cnt = $o->get_residents(array(
					"parent" => $this->persons_fld,
					"personnel_management" => $arr["obj_inst"]->id(),
					"by_jobwish" => 1,
				))->count();
				if($cnt == 0)
				{
					continue;
				}
				$str = " (".$cnt.")";
				$t->add_item($k, array(
					"id" => $o->id(),
					"name" => $arr["request"]["fld_id"] == $o->id() ? "<b>".$o->name().$str."</b>" : $o->name().$str,
					"url" => $this->mk_my_orb("change", array("id" => $arr["request"]["id"], "group" => $arr["request"]["group"], "fld_id" => $o->id(), $k."_id" => $o->id())),
				));
				$tot += $cnt;
			}
			if($tot > 0)
			{
				$str = " (".$tot.")";
				$t->add_item("location", array(
					"id" => $k,
					"name" => $arr["request"]["fld_id"] == $k ? "<b>".t($d).$str."</b>" : t($d).$str,
					"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => $k)),
				));
			}
			$total += $tot;
		}
		// OVERALL
		if($total > 0)
		{
			$t->add_item(0, array(
				"id" => "location",
				"name" => $arr["request"]["fld_id"] == "location" ? "<b>".t("Asukoht")."</b>" : t("Asukoht"),
				"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => "location")),
			));
		}
	}

	function table_flds($arr, $fn, $fld)
	{
		$fi = $arr["request"]["fld_id"];
		$t = &$arr["prop"]["vcl_inst"];
		$caps = array(
			"location" => "Asukoht",
			"county" => "Maakond",
			"city" => "Linn",
			"area" => "Piirkond",
		);
		$mcaps = array(
			"city" => "Linnad",
			"county" => "Maakonnad",
			"area" => "Piirkonnad",
		);
		$clids = array(
			"county" => CL_CRM_COUNTY,
			"city" => CL_CRM_CITY,
			"area" => CL_CRM_AREA,
		);
		$t->define_field(array(
			"name" => "name",
			"caption" => t($caps[$fi]),
			"align" => "center",
		));
		if($arr["request"]["fld_id"] == "location")
		{
			foreach($mcaps as $k => $d)
			{
				$cnt_tot = 0;
				$objs = new object_list(array(
					"parent" => array(),
					"class_id" => $clids[$k],
					"lang_id" => array(),
					"site_id" => array(),
				));
				foreach($objs->arr() as $o)
				{
					$cnt_ol = $o->$fn(array(
						"parent" => $this->$fld,
						"personnel_management" => $arr["obj_inst"]->id(),
						"by_jobwish" => ($fn == "get_residents") ? 1 : 0,
					));
					$cnt = $cnt_ol->count();
					$cnt_tot += $cnt;
				}
				if($cnt_tot > 0)
				{
					$t->define_data(array(
						"name" => html::href(array(
							"url" => $this->special_url($arr, array(
								"fld_id" => $k,
							)),
							"caption" => t($d)." (".$cnt_tot.")",
						)),
					));
				}
			}
		}
		else
		{
			$objs = new object_list(array(
				"parent" => array(),
				"class_id" => $clids[$fi],
				"lang_id" => array(),
				"site_id" => array(),
			));
			foreach($objs->arr() as $o)
			{
				$cnt_ol = $o->$fn(array(
					"parent" => $this->$fld,
					"personnel_management" => $arr["obj_inst"]->id(),
					"by_jobwish" => ($fn == "get_residents") ? 1 : 0,
				));
				if($arr["request"]["group"] != "candidate")
				{
					$cnt = $cnt_ol->count();
				}
				else
				{
					$cnt = 0;
					foreach($cnt_ol->arr() as $cnt_o)
					{
						$cnt += $cnt_o->get_candidates()->count();
					}
				}
				if($cnt == 0)
				{
					continue;
				}
				$str = " (".$cnt.")";
				$t->define_data(array(
					"name" => html::href(array(
						"url" => $this->special_url($arr, array(
							"fld_id" => $o->id(),
							$fi."_id" => $o->id(),
						)),
						"caption" => $o->name().$str,
					)),
				));
			}
		}
	}

	function employee_tb($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_new_button(array(CL_CRM_PERSON), $this->persons_fld);
		$t->add_search_button(array(
			"pn" => add_employee,
			"multiple" => 1,
			"clid" => CL_CRM_PERSON,
		));
		$t->add_delete_button();
		$t->add_menu_button(array(
			"name" => "add2list",
			"img" => "important.png",
			"tooltip" => t("Lisa listi"),
		));
		$lists = new object_list(array(
			"class_id" => CL_CRM_CATEGORY,
			"parent" => array(),
			"sort_by" => "name",
		));
		foreach($lists->arr() as $list)
		{
			$t->add_menu_item(array(
				"parent" => "add2list",
				"name" => "add2list_".$list->id(),
				"text" => $list->name(),
				"action" => "add2list",
				"onClick" => "aw_get_el('list_id').value=".$list->id().";"
			));
		}
	}

	function employee_tbl($arr)
	{
		if($arr["request"]["fld_id"])
		{
			if(!is_oid($arr["request"]["fld_id"]))
			{
				return $this->table_flds($arr, "get_residents", "persons_fld");
			}
			// If it's oid, it has to be one of those.
			$oid = is_oid($arr["request"]["county_id"]) ? $arr["request"]["county_id"] : (is_oid($arr["request"]["city_id"]) ? $arr["request"]["city_id"] : $arr["request"]["area_id"]);

			$t = &$arr["prop"]["vcl_inst"];
			$vars = $this->search_vars;
			$gender = array(1 => "mees", "naine", "" => "m&auml;&auml;ramata");
			$conf = $arr["obj_inst"]->meta("search_conf_tbl");
			$conf = $conf["employee"];
			$t->define_chooser(array(
				"field" => "id",
				"name" => "sel",
			));
			foreach($vars as $name => $caption)
			{
				if(!$conf[$name]["disabled"])
				{
					$t->define_field(array(
						"name" => $name,
						"caption" => t($caption),
						"align" => "center",
						"sortable" => 1,
					));
				}
			}
			if($this->can("view", $oid))
			{
				$o = obj($oid);
				$objs = $o->get_residents(array(
					"parent" => $this->persons_fld,
					"personnel_management" => $arr["obj_inst"]->id(),
					"by_jobwish" => 1,
				));
				foreach($objs->arr() as $obj)
				{
					unset($apps);
					foreach($obj->get_applications(array("parent" => $this->offers_fld, "status" => object::STAT_ACTIVE))->names() as $app_id => $app_name)
					{
						$apps .= (strlen($apps) > 0) ? ", " : "";
						$apps .= html::href(array(
							"caption" => $app_name,
							"url" => $this->mk_my_orb("change", array("id" => $app_id, "return_url" => get_ru()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
						));
					}
					$t->define_data(array(
						"id" => $obj->id(),
						"name" => html::href(array(
							"url" => $this->mk_my_orb("show_cv", array("id" => $obj->id(), "cv" => "cv/".basename($arr["obj_inst"]->prop("cv_tpl")), "die" => "1"), CL_CRM_PERSON),
							"caption" => $obj->name(),
						)),
						"age" => $obj->get_age(),
						"gender" => t($gender[$obj->prop("gender")]),
						"apps" => $apps,
						"modtime" => date("Y-m-d H:i:s", $obj->prop("modified")),
						"change" => html::get_change_url($obj->id(), array("return_url" => get_ru()), t("Muuda")),
					));
				}
			}
		}
		else
		if($arr["request"]["cv_search_button"] || $arr["request"]["cv_search_button_save_search"] || is_oid($arr["request"]["search_save"]))
		{
			$t = &$arr["prop"]["vcl_inst"];
			$vars = $this->search_vars;
			$gender = array(1 => "mees", "naine");
			$conf = $arr["obj_inst"]->meta("search_conf_tbl");
			$conf = $conf["employee"];
			$t->define_chooser(array(
				"field" => "id",
				"name" => "sel",
			));
			foreach($vars as $name => $caption)
			{
				if(!$conf[$name]["disabled"])
				{
					$t->define_field(array(
						"name" => $name,
						"caption" => t($caption),
						"align" => "center",
						"sortable" => 1,
					));
				}
			}

			if(is_oid($arr["request"]["search_save"]))
			{
				$sso = obj($arr["request"]["search_save"]);
				$arr["request"] += $sso->meta();
			}
			$res = $this->search_employee($arr);
			foreach($res as $person)
			{
				unset($apps);
				$obj = obj($person["oid"]);
				foreach($obj->get_applications(array("parent" => $this->offers_fld, "status" => object::STAT_ACTIVE))->names() as $app_id => $app_name)
				{
					$apps .= (strlen($apps) > 0) ? ", " : "";
					$apps .= html::href(array(
						"caption" => $app_name,
						"url" => $this->mk_my_orb("change", array("id" => $app_id, "return_url" => get_ru()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
					));
				}
				$t->define_data(array(
					"id" => $person["oid"],
					"name" => html::href(array(
						"url" => $this->mk_my_orb("show_cv", array("id" => $obj->id(), "cv" => "cv/".basename($arr["obj_inst"]->prop("cv_tpl")), "die" => "1"), CL_CRM_PERSON),
						"caption" => $person["name"],
					)),
					"age" => $obj->get_age(),
//					"age" => $person["birthday"],
					"gender" => t($gender[$person["gender"]]),
					"apps" => $apps,
					"modtime" => date("Y-m-d H:i:s", $person["modified"]),
					"change" => html::get_change_url($person["oid"], array("return_url" => get_ru()), t("Muuda")),
				));
			}
		}
	}

	function search_employee($arr)
	{
		$o = $arr["obj_inst"];

		$r = &$arr["request"];
		$odl_prms["class_id"] = CL_CRM_PERSON;
		$odl_prms[] = new object_list_filter(array(
			"logic" => "OR",
			"conditions" => array(
				"parent" => $this->persons_fld,
				"CL_CRM_PERSON.RELTYPE_PERSONNEL_MANAGEMENT" => $arr["obj_inst"]->id(),
			)
		));

		if($r["cv_age_from"] && $r["cv_age_to"])
		{
			// Why would you store the birthday in YYYY-MM-DD format in a varchar(20) field?????
			$odl_prms["birthday"] = new obj_predicate_compare(
				OBJ_COMP_BETWEEN_INCLUDING,
				((date("Y") - $r["cv_age_to"]).date("-m-d")),
				((date("Y") - $r["cv_age_from"]).date("-m-d"))
			);
		}
		else
		{
			if($r["cv_age_from"])
			{
				// Why would you store the birthday in YYYY-MM-DD format in a varchar(20) field?????
				$odl_prms["birthday"] = new obj_predicate_compare(
					OBJ_COMP_LESS_OR_EQ,
					((date("Y") - $r["cv_age_from"]).date("-m-d"))
				);
			}
			if($r["cv_age_to"])
			{
				// Why would you store the birthday in YYYY-MM-DD format in a varchar(20) field?????
				$odl_prms["birthday"] = new obj_predicate_compare(
					OBJ_COMP_GREATER_OR_EQ,
					((date("Y") - $r["cv_age_to"]).date("-m-d"))
				);
			}
		}
		if($r["cv_age_from"] || $r["cv_age_to"])
		{
			$odl_prms[] = new object_list_filter(array(
				"logic" => "AND",
				"conditions" => array(
					"birthday" => new obj_predicate_not(""),
				)
			));
			$odl_prms[] = new object_list_filter(array(
				"logic" => "AND",
				"conditions" => array(
					"birthday" => new obj_predicate_not("-1"),
				)
			));
			$odl_prms[] = new object_list_filter(array(
				"logic" => "AND",
				"conditions" => array(
					"birthday" => new obj_predicate_not("NULL"),
				)
			));
		}

		if($r["cv_name"])
		{
			$odl_prms["name"] = "%".$r["cv_name"]."%";
		}
		if($r["cv_tel"])
		{
			$odl_prms[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_PHONE.name" => "%".$r["cv_tel"]."%",
					"CL_CRM_PERSON.RELTYPE_PHONE.number_without_crap" => "%".$r["cv_tel"]."%",
				)
			));
		}
		if($r["cv_email"])
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_EMAIL.mail"] = "%".$r["cv_email"]."%";
		}
		if($r["cv_addinfo"])
		{
			$odl_prms["notes"] = "%".$r["cv_addinfo"]."%";
		}
		if(in_array($r["cv_gender"], array(1, 2)))
		{
			$odl_prms["gender"] = $r["cv_gender"];
		}
		// HARIDUS
		if($r["cv_edulvl"])
		{
			$odl_prms["edulevel"] = $r["cv_edulvl"];
		}
		if($r["cv_acdeg"])
		{
			$odl_prms["academic_degree"] = $r["cv_acdeg"];
		}
		if($r["cv_schl"] && is_oid($o->prop("shools_fld")))
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_EDUCATION.RELTYPE_SCHOOL.name"] = "%".$r["cv_schl"]."%";
			$odl_prms["CL_CRM_PERSON.RELTYPE_EDUCATION.RELTYPE_SCHOOL.parent"] = $o->prop("shools_fld");
		}
		if($r["cv_schl_area"])
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_EDUCATION.RELTYPE_FIELD.name"] = "%".$r["cv_schl_area"]."%";
		}
		if($r["cv_schl_stat"])
		{
			// 1 - Jah
			// 0 - Ei
			$odl_prms["CL_CRM_PERSON.RELTYPE_EDUCATION.in_progress"] = 2 - $r["cv_schl_stat"];
		}
		// SOOVITUD T88
		if($r["cv_paywish"] && $r["cv_paywish2"])
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.pay"] = new obj_predicate_compare(
				OBJ_COMP_BETWEEN_INCLUDING,
				$r["cv_paywish"],
				$r["cv_paywish2"]
			);
			$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.pay2"] = new obj_predicate_compare(
				OBJ_COMP_BETWEEN_INCLUDING,
				$r["cv_paywish"],
				$r["cv_paywish2"]
			);
		}
		else
		{
			if($r["cv_paywish"])
			{
				$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.pay"] = new obj_predicate_compare(
					OBJ_COMP_GREATER_OR_EQ,
					$r["cv_paywish"]
				);
				/*
				// The lower limit might not be set.
				$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.pay2"] = new obj_predicate_compare(
					OBJ_COMP_GREATER_OR_EQ,
					$r["cv_paywish"]
				);
				*/
			}
			if($r["cv_paywish2"])
			{
				// The upper limit might not be set.
				$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.pay"] = new obj_predicate_compare(
					OBJ_COMP_LESS_OR_EQ,
					$r["cv_paywish2"]
				);
				$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.pay2"] = new obj_predicate_compare(
					OBJ_COMP_LESS_OR_EQ,
					$r["cv_paywish2"]
				);
			}
		}
		if($r["cv_job"])
		{
			$odl_prms[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_WORK_WANTED.professions" => "%".$r["cv_job"]."%",
					"CL_CRM_PERSON.RELTYPE_WORK_WANTED.RELTYPE_PROFESSION.name" => "%".$r["cv_job"]."%",
				),
			));
		}
		if($r["cv_field"])
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.RELTYPE_FIELD.name"] = "%".$r["cv_field"]."%";
		}
		if($r["cv_type"])
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.RELTYPE_JOB_TYPE.name"] = "%".$r["cv_type"]."%";
		}
		if($r["cv_location"])
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.RELTYPE_LOCATION.name"] = "%".$r["cv_location"]."%";
		}
		if($r["cv_load"])
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_WORK_WANTED.load"] = $r["cv_load"];
		}
		// OSKUSED
		if($r["cv_mother_tongue"])
		{
			$odl_prms["mlang"] = $r["cv_mother_tongue"];
		}
		if($r["cv_lang_exp"])
		{
			if(count($r["cv_lang_exp"]) != 1 || $r["cv_lang_exp"][0] != 0)
				$odl_prms["CL_CRM_PERSON.RELTYPE_LANGUAGE_SKILL.RELTYPE_LANGUAGE"] = $r["cv_lang_exp"];
		}
		if($r["cv_lang_exp_lvl"])
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_LANGUAGE_SKILL.talk"] = new obj_predicate_compare(
				OBJ_COMP_GREATER_OR_EQ,
				$r["cv_lang_exp_lvl"]
			);
			$odl_prms["CL_CRM_PERSON.RELTYPE_LANGUAGE_SKILL.understand"] = new obj_predicate_compare(
				OBJ_COMP_GREATER_OR_EQ,
				$r["cv_lang_exp_lvl"]
			);
			$odl_prms["CL_CRM_PERSON.RELTYPE_LANGUAGE_SKILL.write"] = new obj_predicate_compare(
				OBJ_COMP_GREATER_OR_EQ,
				$r["cv_lang_exp_lvl"]
			);
		}
		if($r["cv_exp"])
		{
			foreach($r["cv_exp"] as $id => $data)
			{
				//	This is the
				//		0 => t("--vali--") 
				//			thing.
				if($data == 0)
					continue;

				$skill_ol_prms = array(
					"class_id" => CL_CRM_SKILL_LEVEL,
					"CL_CRM_SKILL_LEVEL.RELTYPE_SKILL" => $data,
					"parent" => array(),
					"site_id" => array(),
					"lang_id" => array(),
				);
				if($r["cv_exp_lvl"][$id][0] == 0)
				{
					unset($r["cv_exp_lvl"][$id][0]);
				}
				$skill_ol_prms["CL_CRM_SKILL_LEVEL.RELTYPE_LEVEL"] = $r["cv_exp_lvl"][$id];

				$skill_ol = new object_list($skill_ol_prms);
				if(count($skill_ol->ids()) == 0)
					return array();

				$odl_prms["CL_CRM_PERSON.RELTYPE_SKILL_LEVEL"] = $skill_ol->ids();
			}
		}
		if($r["cv_driving_licence"])
		{
			$vals = array();
			foreach($r["cv_driving_licence"] as $c)
			{
				$vals[] = "%".strtolower($c)."%";
			}
			$odl_prms["drivers_license"] = $vals;
		}
		// T88KOGEMUS
		if($r["cv_previous_rank"])
		{
			$odl_prms[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_PREVIOUS_JOB.RELTYPE_PROFESSION.name" => "%".$r["cv_previous_rank"]."%",
					//"CL_CRM_PERSON.RELTYPE_CURRENT_JOB.RELTYPE_PROFESSION.name" => "%".$r["cv_previous_rank"]."%",
				),
			));
		}
		if($r["cv_previous_field"])
		{
			$odl_prms[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_PREVIOUS_JOB.RELTYPE_FIELD.name" => "%".$r["cv_previous_field"]."%",
					"CL_CRM_PERSON.RELTYPE_CURRENT_JOB.RELTYPE_FIELD.name" => "%".$r["cv_previous_field"]."%",
				),
			));
		}
		if($r["cv_company"])
		{
			$odl_prms[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_WORK.name" => "%".$r["cv_company"]."%",
					"CL_CRM_PERSON.RELTYPE_ORG_RELATION.org.name" => "%".$r["cv_company"]."%",
					"CL_CRM_PERSON.RELTYPE_PREVIOUS_JOB.org.name" => "%".$r["cv_company"]."%",
					"CL_CRM_PERSON.RELTYPE_CURRENT_JOB.org.name" => "%".$r["cv_company"]."%",
					"CL_CRM_PERSON.RELTYPE_COMPANY_RELATION.org.name" => "%".$r["cv_company"]."%",
					// Dunno if keepin' the company's name in the 'name' field of additional education object is the best idea...
					"CL_CRM_PERSON.RELTYPE_ADD_EDUCATION.name" => "%".$r["cv_company"]."%",
				),
			));
		}
		if($r["cv_recommenders"])
		{
			$odl_prms[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_RECOMMENDATION.person.name" => "%".$r["cv_recommenders"]."%",
					"CL_CRM_PERSON.RELTYPE_RECOMMENDATION.person.RELTYPE_ORG_RELATION.org.name" => "%".$r["cv_recommenders"]."%",
					"CL_CRM_PERSON.RELTYPE_RECOMMENDATION.person.RELTYPE_CURRENT_JOB.org.name" => "%".$r["cv_recommenders"]."%",
					"CL_CRM_PERSON.RELTYPE_RECOMMENDATION.person.RELTYPE_PREVIOUS_JOB.org.name" => "%".$r["cv_recommenders"]."%",
					"CL_CRM_PERSON.RELTYPE_RECOMMENDATION.person.RELTYPE_WORK.name" => "%".$r["cv_recommenders"]."%",
					"CL_CRM_PERSON.RELTYPE_RECOMMENDATION.person.RELTYPE_COMPANY_RELATION.org.name" => "%".$r["cv_recommenders"]."%",
				),
			));
		}
		if($r["cv_comments"])
		{
			$odl_prms["CL_CRM_PERSON.RELTYPE_COMMENT.commtext"] = "%".$r["cv_comments"]."%";
		}
		$odl = new object_data_list(
			$odl_prms,
			array(
				CL_CRM_PERSON => array(
					"oid" => "oid",
					"name" => "name",
					"gender" => "gender",
					"birthday" => "birthday",
					"modified" => "modified",
				),
			)
		);
		return $odl->list_data;
	}

	function _get_search_conf_tbl($arr)
	{
		$conf = $arr["obj_inst"]->meta("search_conf_tbl");
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "table",
			"caption" => t("Tabel"),
		));
		$vars = $this->search_vars;
		foreach($vars as $name => $caption)
		{
			$t->define_field(array(
				"name" => $name,
				"caption" => t($caption),
			));
		}

		// Tables the configuration applies for
		$tables = array(
			"candidate" => t("Kandideerijate otsingu tulemused"),
			"employee" => t("T&ouml;&ouml;otsijate otsingu tulemused"),
		);

		foreach($tables as $id => $caption)
		{
			$data = array("table" => t($caption));
			foreach($vars as $name => $_caption)
			{
				$data[$name] = html::hidden(array(
						"name" => "search_conf_tbl[".$id."][".$name."][caption]",
						"value" => $_caption,
					)).
				html::checkbox(array(
					"name" => "search_conf_tbl[".$id."][".$name."][disabled]",
					"value" => 1,
					"checked" => $conf[$id][$name]["disabled"] == 1 ? false : true,
				));
			}
			$t->define_data($data);
		}
	}

	function employee_list_tree($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_item(0, array(
			"id" => 3,
			"name" => t("Element"),
		));
	}

	function candidate_tree($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$objs = new object_tree(array(
			"parent" => $this->offers_fld,
			"class_id" => array(CL_MENU, CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
			"sort_by" => "objects.class_id, objects.name",
			"status" => object::STAT_ACTIVE,
		));
		$obx = $objs->to_list()->arr();
		// First we'll run through the job offer objs.
		foreach($obx as $ob)
		{
			if($ob->class_id() != CL_PERSONNEL_MANAGEMENT_JOB_OFFER)
			{
				continue;
			}
			$cnt_cands = $ob->get_candidates()->count();
			if($cnt_cands == 0)
			{
				continue;
			}
			$str = " (".$cnt_cands.")";
			$t->add_item($ob->parent(), array(
				"id" => $ob->id(),
				"name" => $arr["request"]["fld_id"] == $ob->id() ? "<b>".$ob->name().$str."</b>" : $ob->name().$str,
				"url" => $this->special_url($arr, array(
					"fld_id" => $ob->id(),
					"ofr_id" => $ob->id(),
				)),
				"iconurl" => icons::get_icon_url($ob->class_id()),
			));
			$cnt[$ob->parent()] += $cnt_cands;
			$total += $cnt_cands;
		}
		// Now we'll run through the dirs.
		foreach($obx as $ob)
		{
			if($ob->class_id() == CL_PERSONNEL_MANAGEMENT_JOB_OFFER || !$cnt[$ob->id()])
			{
				continue;
			}
			$str = " (".$cnt[$ob->id()].")";
			$t->add_item($ob->parent(), array(
				"id" => $ob->id(),
				"name" => $arr["request"]["fld_id"] == $ob->id() ? "<b>".$ob->name().$str."</b>" : $ob->name().$str,
				"url" => $this->special_url($arr, array(
					"fld_id" => $ob->id(),
				)),
			));
		}
		$t->add_item(0, array(
			"id" => $this->offers_fld,
			"name" => $arr["request"]["fld_id"] == $this->offers_fld ? "<b>".t("Aktiivsed t&ouml;&ouml;pakkumised")." (".$total.")</b>" : t("Aktiivsed t&ouml;&ouml;pakkumised")." (".$total.")",
			"url" => $this->special_url($arr, array(
				"fld_id" => $this->offers_fld,
			)),
		));
		// ASUKOHT
		$mcaps = array(
			"area" => "Piirkonnad",
			"county" => "Maakonnad",
			"city" => "Linnad",
		);
		$clids = array(
			"county" => CL_CRM_COUNTY,
			"city" => CL_CRM_CITY,
			"area" => CL_CRM_AREA,
		);
		$total = 0;
		foreach($mcaps as $k => $d)
		{
			$objs = new object_list(array(
				"parent" => array(),
				"class_id" => $clids[$k],
				"lang_id" => array(),
				"site_id" => array(),
			));
			$tot = 0;
			foreach($objs->arr() as $o)
			{
				$ofrs = $o->get_job_offers(array(
					"parent" => $this->offers_fld,
					"status" => object::STAT_ACTIVE,
				));
				$cnt = 0;
				foreach($ofrs->arr() as $ofr)
				{
					$cnt_cands = $ofr->get_candidates()->count();
					if($cnt_cands == 0)
					{
						continue;
					}
					$str = " (".$cnt_cands.")";
					$t->add_item($o->id(),array(
						"id" => $o->id()."_".$ofr->id(),
						"name" => $arr["request"]["fld_id"] == $ofr->id() ? "<b>".$ofr->name().$str."</b>" : $ofr->name().$str,
						"url" => $this->special_url($arr, array(
							"fld_id" => $ofr->id(),
							"ofr_id" => $ofr->id(),
						)),
					"iconurl" => icons::get_icon_url($ob->class_id()),
					));
					//$cnt += $cnt_cands;
					$cnt++;
				}
				if($cnt == 0)
				{
					continue;
				}
				$str = " (".$cnt.")";
				$t->add_item($k, array(
					"id" => $o->id(),
					"name" => $arr["request"]["fld_id"] == $o->id() ? "<b>".$o->name().$str."</b>" : $o->name().$str,
					"url" => $this->mk_my_orb("change", array("id" => $arr["request"]["id"], "group" => $arr["request"]["group"], "fld_id" => $o->id(), $k."_id" => $o->id())),
				));
				$tot += $cnt;
			}
			if($tot > 0)
			{
				$str = " (".$tot.")";
				$t->add_item("location", array(
					"id" => $k,
					"name" => $arr["request"]["fld_id"] == $k ? "<b>".t($d).$str."</b>" : t($d).$str,
					"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => $k)),
				));
			}
			$total += $tot;
		}
		// OVERALL
		if($total > 0)
		{
			$t->add_item(0, array(
				"id" => "location",
				"name" => $arr["request"]["fld_id"] == "location" ? "<b>".t("Asukoht")."</b>" : t("Asukoht"),
				"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => "location")),
			));
		}
	}

	function _get_offers_tree($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$objs = new object_tree(array(
			"parent" => $this->offers_fld,
			"class_id" => CL_MENU,
			"sort_by" => "objects.name",
		));
		$obj = obj($this->offers_fld);
		$childs = new object_list(array(
			"parent" => $this->offers_fld,
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
		));
		$cnt = $childs->count();
		$str = $cnt > 0 ? " ($cnt)" : "";
		$obx = $objs->to_list();
		$t->add_item(0, array(
			"id" => $this->offers_fld,
			"name" => $arr["request"]["fld_id"] == $this->offers_fld ? "<b>".$obj->name().$str."</b>" : $obj->name().$str,
			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => $this->offers_fld)),
		));
		foreach($obx->arr() as $ob)
		{
			$id = $ob->id();
			$childs = new object_list(array(
				"parent" => $id,
				"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			));
			$cnt = $childs->count();
			$str = $cnt > 0 ? " ($cnt)" : "";
			$t->add_item($ob->parent(), array(
				"id" => $id,
				"name" => $arr["request"]["fld_id"] == $id ? "<b>".$ob->name().$str."</b>" : $ob->name().$str,
				"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => $id)),
			));
		}
		// ASUKOHT
		$mcaps = array(
			"area" => "Piirkonnad",
			"county" => "Maakonnad",
			"city" => "Linnad",
		);
		$clids = array(
			"county" => CL_CRM_COUNTY,
			"city" => CL_CRM_CITY,
			"area" => CL_CRM_AREA,
		);
		$total = 0;
		foreach($mcaps as $k => $d)
		{
			$objs = new object_list(array(
				"parent" => array(),
				"class_id" => $clids[$k],
				"lang_id" => array(),
				"site_id" => array(),
			));
			$tot = 0;
			foreach($objs->arr() as $o)
			{
				$cnt = $o->get_job_offers(array(
					"parent" => $this->offers_fld,
				))->count();
				if($cnt == 0)
				{
					continue;
				}
				$str = " (".$cnt.")";
				$t->add_item($k, array(
					"id" => $o->id(),
					"name" => $arr["request"]["fld_id"] == $o->id() ? "<b>".$o->name().$str."</b>" : $o->name().$str,
					"url" => $this->mk_my_orb("change", array("id" => $arr["request"]["id"], "group" => $arr["request"]["group"], "fld_id" => $o->id(), $k."_id" => $o->id())),
				));
				$tot += $cnt;
			}
			if($tot > 0)
			{
				$str = " (".$tot.")";
				$t->add_item("location", array(
					"id" => $k,
					"name" => $arr["request"]["fld_id"] == $k ? "<b>".t($d).$str."</b>" : t($d).$str,
					"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => $k)),
				));
			}
			$total += $tot;
		}
		// OVERALL
		if($total > 0)
		{
			$t->add_item(0, array(
				"id" => "location",
				"name" => $arr["request"]["fld_id"] == "location" ? "<b>".t("Asukoht")."</b>" : t("Asukoht"),
				"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => "location")),
			));
		}
	}

	function _get_employee_list_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "add",
			"caption" => t("Lisa"),
			"img" => "new.gif",
		));
	}

	function candidate_toolbar($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "add",
			"caption" => t("Lisa"),
			"img" => "new.gif",
			"url" => $this->mk_my_orb("new", array("ofr_id" => $arr["request"]["ofr_id"], "parent" => $this->persons_fld, "return_url" => get_ru()), CL_CRM_PERSON),
		));
		$t->add_search_button(array(
			"pn" => add_employee,
			"multiple" => 1,
			"clid" => CL_CRM_PERSON,
		));
		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta kandidaadid"),
			"action" => "delete_cands",
		));
	}
	
	function _get_offers_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "add",
			"tooltip" => t("Uus"),
		));
		$arr["request"]["fld_id"] = $arr["request"]["fld_id"] == "location" ? $this->offers_fld : $arr["request"]["fld_id"];
		$arr["request"]["fld_id"] = $arr["request"]["fld_id"] == $arr["request"]["county_id"] ? $this->offers_fld : $arr["request"]["fld_id"];
		$pt = $arr["request"]["fld_id"] ? $arr["request"]["fld_id"] : $this->offers_fld;
		$tb->add_menu_item(array(
			"parent" => "add",
			"text" => t("T&ouml;&ouml;pakkumine"),
			"link" => html::get_new_url(CL_PERSONNEL_MANAGEMENT_JOB_OFFER, $pt, array(
				"return_url" => get_ru(),
				"personnel_management_id" => $arr["obj_inst"]->id(),
				"county_id" => $arr["request"]["county_id"],
			)),
			"href_id" => "add_bug_href"
		));
		$tb->add_menu_item(array(
			"parent" => "add",
			"text" => t("Kaust"),
			"link" => html::get_new_url(CL_MENU, $pt, array("return_url" => get_ru())),
		));
		$tb->add_button(array(
			"name" => "cut",
			"caption" => t("L&otilde;ika"),
			"img" => "cut.gif",
			"action" => "cut_offers",
		));
		if (count($_SESSION["aw_jobs"]["cut_offers"]))
		{
			$tb->add_button(array(
				"name" => "paste",
				"caption" => t("Kleebi"),
				"img" => "paste.gif",
				"action" => "paste_offers",
			));
		}
		$tb->add_button(array(
			"name" => "save",
			"caption" => t("Salvesta"),
			"img" => "save.gif",
			"action" => "save_offers",
		));
		$tb->add_delete_button();

	}

	function _get_employee_list_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "offer",
			"caption" => t("T&ouml;&ouml;pakkumine"),
		));

		$ol = new object_list(array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_CANDIDATE,
			"lang_id" => array(),
		));
		foreach($ol->arr() as $cand)
		{
			$offer = obj($cand->parent());
			$t->define_data(array(
				"name" => html::obj_change_url($cand),
				"offer" => html::obj_change_url($offer)
			));
		}
		
	}

	function candidate_table_flds($arr)
	{
		if($arr["request"]["fld_id"])
		{
			if(!is_oid($arr["request"]["fld_id"]))
			{
				return $this->table_flds($arr, "get_job_offers", "offers_fld");
			}

			$t = &$arr["prop"]["vcl_inst"];
			$t->define_field(array(
				"name" => "icon",
				"align" => "center",
				"width" => "40",
			));
			$t->define_field(array(
				"name" => "name",
				"caption" => t("Nimi"),
				"align" => "left",
			));
			
			$oid = is_oid($arr["request"]["county_id"]) ? $arr["request"]["county_id"] : (is_oid($arr["request"]["city_id"]) ? $arr["request"]["city_id"] : $arr["request"]["area_id"]);

			if(is_oid($oid))
			// It's by location.
			{
				$o = obj($oid);
				$objs = $o->get_job_offers(array(
					"parent" => $this->offers_fld,
				));
				$obx = $objs->arr();
			}
			else
			{
				$objs = new object_tree(array(
					"parent" => $arr["request"]["fld_id"],
					"class_id" => array(CL_MENU, CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
					"sort_by" => "objects.class_id, objects.name",
					"status" => object::STAT_ACTIVE,
				));
				$obx = $objs->to_list()->arr();
			}
			// First we'll run through the job offer objs.
			foreach($obx as $ob)
			{
				if($ob->class_id() != CL_PERSONNEL_MANAGEMENT_JOB_OFFER)
				{
					continue;
				}
				$cnt_cands = $ob->get_candidates()->count();
				$cnt[$ob->parent()] += $cnt_cands;
				$total += $cnt_cands;
				if($cnt_cands == 0 || ($ob->parent() != $arr["request"]["fld_id"] && !is_oid($oid)))
				{
					continue;
				}
				$str = " (".$cnt_cands.")";
				$t->define_data(array(
					"name" => html::href(array(
						"url" => $this->special_url($arr, array(
							"fld_id" => $ob->id(),
							"ofr_id" => $ob->id(),
						)),
						"caption" => $ob->name().$str,
					)),
					"icon" => html::img(array(
						"url" => icons::get_icon_url($ob->class_id()),
					)),
					"class_id" => $ob->class_id(),
				));
			}
			// Now we'll run through the dirs if there are any.
			foreach($obx as $ob)
			{
				if($ob->class_id() == CL_PERSONNEL_MANAGEMENT_JOB_OFFER || !$cnt[$ob->id()] || ($ob->parent() != $arr["request"]["fld_id"] && !is_oid($oid)))
				{
					continue;
				}
				$str = " (".$cnt[$ob->id()].")";
				$t->define_data(array(
					"name" => html::href(array(
						"url" => $this->special_url($arr, array(
							"fld_id" => $ob->id(),
						)),
						"caption" => $ob->name().$str,
					)),
					"icon" => html::img(array(
						"url" => icons::get_icon_url($ob->class_id()),
					)),
					"class_id" => $ob->class_id(),
				));
			}
			$t->sort_by(array(
				"field" => "class_id",
				"sorder" => "desc",
			));
		}
		else
		if($arr["request"]["cv_search_button"] || $arr["request"]["cv_search_button_save_search"] || is_oid($arr["request"]["search_save"]))
		{
			$t = &$arr["prop"]["vcl_inst"];
			$vars = $this->search_vars;
			$gender = array(1 => "mees", "naine");
			$conf = $arr["obj_inst"]->meta("search_conf_tbl");
			$conf = $conf["employee"];
			$t->define_chooser(array(
				"field" => "id",
				"name" => "sel",
			));
			foreach($vars as $name => $caption)
			{
				if(!$conf[$name]["disabled"])
				{
					$t->define_field(array(
						"name" => $name,
						"caption" => t($caption),
						"align" => "center",
						"sortable" => 1,
					));
				}
			}

			if(is_oid($arr["request"]["search_save"]))
			{
				$sso = obj($arr["request"]["search_save"]);
				$arr["request"] += $sso->meta();
			}
			$res = $this->search_employee($arr);
			foreach($res as $person)
			{
				$apps = "";
				$obj = obj($person["oid"]);
				foreach($obj->get_applications(array("parent" => $this->offers_fld, "status" => object::STAT_ACTIVE))->names() as $app_id => $app_name)
				{
					$apps .= (strlen($apps) > 0) ? ", " : "";
					$apps .= html::href(array(
						"caption" => $app_name,
						"url" => $this->mk_my_orb("change", array("id" => $app_id, "return_url" => get_ru()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
					));
				}
				// We only display persons with that have active applications.
				if(empty($apps))
					continue;

				$t->define_data(array(
					"id" => $person["oid"],
					"name" => html::href(array(
						"url" => $this->mk_my_orb("show_cv", array("id" => $obj->id(), "cv" => "cv/".basename($arr["obj_inst"]->prop("cv_tpl")), "die" => "1"), CL_CRM_PERSON),
						"caption" => $person["name"],
					)),
					"age" => $obj->get_age(),
//					"age" => $person["birthday"],
					"gender" => t($gender[$person["gender"]]),
					"apps" => $apps,
					"modtime" => date("Y-m-d H:i:s", $person["modified"]),
					"change" => html::get_change_url($person["oid"], array("return_url" => get_ru()), t("Muuda")),
				));
			}
		}
	}

	function candidate_table($arr)
	{
		if(!is_oid($arr["request"]["ofr_id"]))
		{
			return $this->candidate_table_flds($arr);
		}
		$t = &$arr["prop"]["vcl_inst"];
		$vars = $this->search_vars;
		$gender = array(1 => "mees", "naine", "" => "m&auml;&auml;ramata");
		$conf = $arr["obj_inst"]->meta("search_conf_tbl");
		$conf = $conf["candidate"];
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
		foreach($vars as $name => $caption)
		{
			if(!$conf[$name]["disabled"])
			{
				$t->define_field(array(
					"name" => $name,
					"caption" => t($caption),
					"align" => "center",
					"sortable" => 1,
				));
			}
		}
		if($this->can("view", $arr["request"]["ofr_id"]))
		{
			$job = obj($arr["request"]["ofr_id"]);
			$objs = $job->get_candidates(array(
				"status" => object::STAT_ACTIVE,
			));
			foreach($objs->arr() as $obj)
			{
				unset($apps);
				foreach($obj->get_applications(array("parent" => $this->offers_fld, "status" => object::STAT_ACTIVE))->names() as $app_id => $app_name)
				{
					$apps .= (strlen($apps) > 0) ? ", " : "";
					$apps .= html::href(array(
						"caption" => $app_name,
						"url" => $this->mk_my_orb("change", array("id" => $app_id, "return_url" => get_ru()), CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
					));
				}
				$t->define_data(array(
					"id" => $obj->id(),
					"name" => html::href(array(
						"url" => $this->mk_my_orb("show_cv", array("id" => $obj->id(), "cv" => "cv/".basename($arr["obj_inst"]->prop("cv_tpl")), "die" => "1"), CL_CRM_PERSON),
						"caption" => $obj->name(),
					)),
					"age" => $obj->get_age(),
					"gender" => t($gender[$obj->prop("gender")]),
					"apps" => $apps,
					"modtime" => date("Y-m-d H:i:s", $obj->prop("modified")),
					"change" => html::get_change_url($obj->id(), array("return_url" => get_ru()), t("Muuda")),
				));
			}
		}
	}

	function _init_offers_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "profession",
			"caption" => t("Ametikoht"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "org",
			"caption" => t("Organisatsioon"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "location",
			"caption" => t("Asukoht"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("T&auml;htaeg"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Aktiivne"),
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function offers_table_srch($arr)
	{
		$r = &$arr["request"];
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_offers_table(&$t);

		$ol_arr = array("class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER);
		if($r["os_status"] == 1 || $r["os_status"] == 2)
		{
			$ol_arr["status"] = $r["os_status"];
		}		
		$ol = new object_list($ol_arr);
		foreach($ol->arr() as $obj)
		{
			unset($prof);
			unset($org);
			unset($loc);
			if($r["os_pr"])
			{
				if(!$this->can("view", $obj->prop("profession")))
					continue;
				$pr = obj($obj->prop("profession"));
				$prof = $pr->name();
				if(strpos(strtolower($pr->name()), strtolower($r["os_pr"])) === false)
					continue;
			}
			if($r["os_county"])
			{
				$ok = false;
				foreach($obj->connections_from(array("type" => 5, "class_id" => CL_CRM_COUNTY)) as $conn)
				{
					if(strpos(strtolower($conn->conn["to.name"]), strtolower($r["os_county"])) === false)
						continue;
					else
						$ok = true;
				}
				if(!$ok)
					continue;
			}
			if($r["os_city"])
			{
				$ok = false;
				foreach($obj->connections_from(array("type" => 5, "class_id" => CL_CRM_CITY)) as $conn)
				{
					if(strpos(strtolower($conn->conn["to.name"]), strtolower($r["os_city"])) === false)
						continue;
					else
						$ok = true;
				}
				if(!$ok)
					continue;
			}
			if($r["os_dl_from_time"])
			{
				if($obj->prop("end") < $r["os_dl_from_time"])
					continue;
			}
			if($r["os_dl_to_time"])
			{
				if($obj->prop("end") > $r["os_dl_to_time"])
					continue;
			}
			if($this->can("view", $obj->prop("profession")))
			{
				$pr = obj($obj->prop("profession"));
				$prof = html::obj_change_url($pr);
			}
			if($this->can("view", $obj->prop("company")))
			{
				$org = obj($obj->prop("company"));
				$org = html::obj_change_url($org);
			}
			// Location
			$loc = $obj->prop("loc_area.name");
			if(strlen($loc) > 0)
			{
				$loc .= ", ";
			}
			$loc .= $obj->prop("loc_county.name");
			if(strlen($loc) > 0)
			{
				$loc .= ", ";
			}
			$loc .= $obj->prop("loc_city.name"); 

			$end = $obj->prop("end") ? get_lc_date($obj->prop("end")) : t("M&auml;&auml;ramata");
			$t->define_data(array(
				"name" => html::obj_change_url($obj),
				"profession" => $prof,
				"org" => $org,
				"location" => $loc,
				"end" => $end,
				"status" => html::hidden(array(
					"name" => "old[status][".$obj->id()."]",
					"value" => $obj->status() == STAT_ACTIVE ? 2 : 1,
				)).html::checkbox(array(
					"name" => "new[status][".$obj->id()."]",
					"value" => 2,
					"checked" => $obj->status() == STAT_ACTIVE ? true : false,
				)),
				"id" => $obj->id(),
			));
		}
	}

	function offers_table($arr)
	{
		if(!is_oid($arr["request"]["fld_id"]) && $arr["request"]["fld_id"])
		{
			return $this->table_flds($arr, "get_job_offers", "offers_fld");
		}
		$oid = is_oid($arr["request"]["county_id"]) ? $arr["request"]["county_id"] : (is_oid($arr["request"]["city_id"]) ? $arr["request"]["city_id"] : $arr["request"]["area_id"]);

		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_offers_table(&$t);

		$toopakkujad_ids = array();
		$toopakkujad = array();

		if(!is_oid($oid))
		{
			$fld_id = $this->can("view", $arr["request"]["fld_id"]) ? $arr["request"]["fld_id"] : $this->offers_fld; 

			$objs = new object_list(array(
				"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
				"parent" => $fld_id,
			));
		}
		else
		{
			$o = obj($oid);
			$objs = $o->get_job_offers(array(
				"parent" => $this->offers_fld,
			));
		}

		foreach ($objs->arr() as $obj)
		{
			if($obj->prop("end"))
			{
        		$end = get_lc_date($obj->prop("end"));
        	}
        	else
        	{
        		$end = t("m&auml;&auml;ramata");
        	}
			
			// Location
			$loc = $obj->prop("loc_area.name");
			if(strlen($loc) > 0 && strlen($obj->prop("loc_county.name")) > 0)
			{
				$loc .= ", ";
			}
			$loc .= $obj->prop("loc_county.name");
			if(strlen($loc) > 0 && strlen($obj->prop("loc_city.name")) > 0)
			{
				$loc .= ", ";
			}
			$loc .= $obj->prop("loc_city.name");

			$t->define_data(array(
				"id" => $obj->id(),
				"name" => html::get_change_url($obj->id(), array("return_url" => get_ru()), $obj->name()),
				"profession" => $obj->prop("profession.name"),
				"org" => html::get_change_url($obj->prop("company"), array("return_url" => get_ru()), $obj->prop("company.name")),
				"location" => $loc,
				"end" => $end,
				"created" => $obj->created(),
				"status" => html::hidden(array(
					"name" => "old[status][".$obj->id()."]",
					"value" => $obj->status() == STAT_ACTIVE ? 2 : 1,
				)).html::checkbox(array(
					"name" => "new[status][".$obj->id()."]",
					"value" => 2,
					"checked" => $obj->status() == STAT_ACTIVE ? true : false,
				)),
			));
		}
		$t->set_default_sortby("created");
		$t->set_default_sorder("desc");
		$t->sort_by();
	}
	
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "cv_search_button_save_search":
			case "cv_search_button_save":
				if(strlen($arr["request"]["cv_search_saved_name"]) > 0)
				{
					$this->cv_save_search($arr);
				}
				break;

			case "add_employee":
				$this->_set_add_empolyee($arr);
				break;

			case "lang_tbl":
				$arr["obj_inst"]->set_meta("lang_tbl", $arr["request"]["lang_tbl"]);
				break;

			case "sysdefault_pm":
				$ol = new object_list(array(
					"class_id" => $this->clid,
					"lang_id" => array(),
				));
				foreach ($ol->arr() as $item)
				{
					if ($item->flag(OBJ_FLAG_IS_SELECTED) && $item->id() != $arr["obj_inst"]->id() || $prop["value"] != $prop["ch_value"])
					{
						$item->set_flag(OBJ_FLAG_IS_SELECTED, false);
						$item->save();
					}
					elseif ($item->id() == $arr["obj_inst"]->id() && !$item->flag(OBJ_FLAG_IS_SELECTED) && $prop["value"] == $prop["ch_value"])
					{
						$item->set_flag(OBJ_FLAG_IS_SELECTED, true);
						$item->save();
					};
				};
				break;

			case "search_conf_tbl":
				foreach($prop["value"] as $id => $data)
				{
					foreach($data as $name => $v)
					{
						$meta[$id][$name]["disabled"] = 1 - $v["disabled"];
					}
				}
				$arr["obj_inst"]->set_meta("search_conf_tbl", $meta);
				break;

			case "offers_table":
				$this->save_offers($arr["request"]);
				break;
		}
		
		return $retval;
	}	

	function _set_add_empolyee($arr)
	{
		if($arr["request"]["group"] == "employee")
		{
			$ps = explode(",", $arr["prop"]["value"]);
			foreach($ps as $p)
			{
				$po = obj($p);
				if($po->parent() != $this->persons_fld)
				{
					$po->connect(array(
						"to" => $arr["obj_inst"]->id(),
						"reltype" => "RELTYPE_PERSONNEL_MANAGEMENT",
					));
				}
			}
		}
		if($arr["request"]["group"] == "candidate" && $this->can("view", $arr["request"]["ofr_id"]))
		{
			$o = obj($arr["request"]["ofr_id"]);
			$ids = $o->get_candidates()->ids();
			$ps = explode(",", $arr["prop"]["value"]);
			foreach($ps as $p)
			{
				if($this->can("view", $p))
				{
					$p = obj($p);
					if(!in_array($p->id(), $ids))
					{
						$c = new object;
						$c->set_class_id(CL_PERSONNEL_MANAGEMENT_CANDIDATE);
						$c->set_status(object::STAT_ACTIVE);
						$c->set_parent($o->id());
						$c->set_name($p->name()." kandidatuur kohale ".$o->name());
						$c->set_prop("person", $p->id());
						$c->save();

						// Job offer to candidate.
						$o->connect(array(
							"to" => $c->id(),
							"reltype" => "RELTYPE_CANDIDATE",
						));
						// Candidate to person.
						$c->connect(array(
							"to" => $p->id(),
							"reltype" => "RELTYPE_PERSON",
						));
					}
				}
			}
		}
	}

	function parse_alias($arr)
	{
		$obj = obj($arr["id"]);
		$this->read_template("show.tpl");
		$objs = new object_list(array(
			"parent" => $obj->prop("offers_fld"),
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
		));
		$offers = "";
		foreach($objs->arr() as $ob)
		{
			$this->vars(array(
				"profession" => html::href(array(
					"url" => obj_link($ob->prop("profession")),
					"caption" => $ob->prop("profession.name"),
				)),
				"company" => $ob->prop("company.name"),
				"location" => $ob->prop("location.name"),
				"field" => $ob->prop("field.name"),
				"end" => get_lc_date($ob->prop("end")),
			));
			$offers .= $this->parse("OFFER");
		}
		$this->vars(array(
			"count" => $objs->count(),
			"OFFER" => $offers,
		));
		return $this->parse();
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["fld_id"] = $_GET["fld_id"];
		$arr["ofr_id"] = $_GET["ofr_id"];
		$arr["list_id"] = 0;
		$arr["cv_search_saved_name"] = "";
	}

	/**
		@attrib name=cut_offers
	**/
	function cut_offers($arr)
	{
		$_SESSION["aw_jobs"]["cut_offers"] = $arr["sel"];
		return $arr["post_ru"];
	}

	/**
		@attrib name=paste_offers
	**/
	function paste_offers($arr)
	{
		foreach(safe_array($_SESSION["aw_jobs"]["cut_offers"]) as $ofid)
		{
			$ofo = obj($ofid);
			$ofo->set_parent($arr["fld_id"]);
			$ofo->save();
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=save_offers
	**/
	function save_offers($arr)
	{
		foreach($arr["old"]["status"] as $oid => $old_status)
		{
			if($arr["new"]["status"][$oid] != $old_status)
			{
				$o = obj($oid);
				$o->set_prop("status", ($arr["new"]["status"][$oid] == 2 ? 2 : 1));
				$o->save();
			}
		}
		return $arr["post_ru"];
	}

	function callback_mod_retval($arr)
	{
		if($arr["request"]["search_save"])
		{
			$arr["args"]["search_save"] = $arr["request"]["search_save"];
		}

		if($arr["request"]["os_sbt"])
		{
			$arr["args"]["os_pr"] = $arr["request"]["os_pr"];
			$arr["args"]["os_county"] = $arr["request"]["os_county"];
			$arr["args"]["os_city"] = $arr["request"]["os_city"];
			$arr["args"]["os_dl_from"] = $arr["request"]["os_dl_from"];
			$arr["args"]["os_dl_from_time"] = mktime(0, 0, 0, $arr["request"]["os_dl_from"]["month"], $arr["request"]["os_dl_from"]["day"], $arr["request"]["os_dl_from"]["year"]);
			$arr["args"]["os_dl_to"] = $arr["request"]["os_dl_to"];
			$arr["args"]["os_dl_to_time"] = mktime(0, 0, 0, $arr["request"]["os_dl_to"]["month"], $arr["request"]["os_dl_to"]["day"], $arr["request"]["os_dl_to"]["year"]);
			$arr["args"]["os_status"] = $arr["request"]["os_status"];
			$arr["args"]["os_sbt"] = $arr["request"]["os_sbt"];
		}

		if($arr["request"]["cv_search_button"] || $arr["request"]["cv_search_button_save_search"])
		{
			$arr["args"]["cv_search_button_save_search"] = $arr["request"]["cv_search_button_save_search"];
			$arr["args"]["cv_search_button"] = $arr["request"]["cv_search_button"];
			$arr["args"]["cv_name"] = $arr["request"]["cv_name"];
			$arr["args"]["cv_company"] = $arr["request"]["cv_company"];
			$arr["args"]["cv_job"] = $arr["request"]["cv_job"];
			$arr["args"]["cv_paywish"] = $arr["request"]["cv_paywish"];
			$arr["args"]["cv_paywish2"] = $arr["request"]["cv_paywish2"];
			$arr["args"]["cv_field"] = $arr["request"]["cv_field"];
			$arr["args"]["cv_previous_field"] = $arr["request"]["cv_previous_field"];
			$arr["args"]["cv_type"] = $arr["request"]["cv_type"];
			$arr["args"]["cv_location"] = $arr["request"]["cv_location"];
			$arr["args"]["cv_load"] = $arr["request"]["cv_load"];
			$arr["args"]["cv_personality"] = $arr["request"]["cv_personality"];
			$arr["args"]["cv_comments"] = $arr["request"]["cv_comments"];
			$arr["args"]["cv_recommenders"] = $arr["request"]["cv_recommenders"];
			$arr["args"]["cv_mother_tongue"] = $arr["request"]["cv_mother_tongue"];
			$arr["args"]["cv_lang_exp"] = $arr["request"]["cv_lang_exp"];
			$arr["args"]["cv_lang_exp_lvl"] = $arr["request"]["cv_lang_exp_lvl"];
			$arr["args"]["cv_exp"] = $arr["request"]["cv_exp"];
			$arr["args"]["cv_exp_lvl"] = $arr["request"]["cv_exp_lvl"];
			$arr["args"]["cv_gender"] = $arr["request"]["cv_gender"];
			$arr["args"]["cv_age_from"] = $arr["request"]["cv_age_from"];
			$arr["args"]["cv_age_to"] = $arr["request"]["cv_age_to"];
			$arr["args"]["cv_previous_rank"] = $arr["request"]["cv_previous_rank"];
			$arr["args"]["cv_driving_licence"] = $arr["request"]["cv_driving_licence"];
			$arr["args"]["cv_tel"] = $arr["request"]["cv_tel"];
			$arr["args"]["cv_email"] = $arr["request"]["cv_email"];
			$arr["args"]["cv_addinfo"] = $arr["request"]["cv_addinfo"];
			$arr["args"]["cv_edulvl"] = $arr["request"]["cv_edulvl"];
			$arr["args"]["cv_acdeg"] = $arr["request"]["cv_acdeg"];
			$arr["args"]["cv_schl"] = $arr["request"]["cv_schl"];
			$arr["args"]["cv_schl_area"] = $arr["request"]["cv_schl_area"];
			$arr["args"]["cv_schl_stat"] = $arr["request"]["cv_schl_stat"];
		}
	}

	/** Returns the oid of the site-wide default personnel management
		@attrib api=1 params=name

		@returns
			The oid of the system default personnel management for the class or false if no personnel management object exists.

		@examples
			$pm_inst = get_instance(CL_PERSONNEL_MANAGEMENT);
			if (($pm_oid = $pm_inst->get_sysdefault()) !== false)
			{
				print "default personnel management  is ".$pm_oid."<br>";
			}
	**/
	function get_sysdefault()
	{
		// 2 passes, because I need to know which element is active before
		// doing the table
		$active = false;
		$ol = new object_list(array(
			"class_id" => $this->clid,
			"lang_id" => array(),
			"flags" => array(
				"mask" => OBJ_FLAG_IS_SELECTED,
				"flags" => OBJ_FLAG_IS_SELECTED
			)
		));
		if (sizeof($ol->ids()) > 0)
		{
			$first = $ol->begin();
			$active = $first->id();
		};
		if($active)
		{
			return $active;
		}
		else
		{
			// If none of those is default, we return the first one
			$ol = new object_list(array(
				"class_id" => $this->clid,
				"lang_id" => array(),
				"sort_by" => "oid",
			));
			if(sizeof($ol->ids()) > 0)
			{
				$first = $ol->begin();
				$active = $first->id();
			}
		}
		return $active;
	}

	/**
		@attrib name=add2list
	**/
	function add2list($arr)
	{
		$person = new object();
		$person->set_class_id(CL_CRM_PERSON);

		foreach($arr["sel"] as $id)
		{
			$person->add_person_to_list(array(
				"id" => $id,
				"list_id" => $arr["list_id"],
			));
		}

		return $arr["post_ru"];
	}

	/**
		@attrib name=get_udef_skills
	**/
	function get_udef_skills($arr)
	{
		$o = obj($arr["id"]);
		return $o->meta("udef_skills");
	}

	function get_lang_conf($arr)
	{
		if(!is_oid($arr["id"]))
		{
			$arr["id"] = personnel_management::get_sysdefault();
		}
		$o = obj($arr["id"]);
		return $o->meta("lang_tbl");
	}


	/** Returns the array of languages allowed in the personnel management.
		@attrib name=get_languages params=name api=1

		@param id optional type=oid
			The oid of the personnel management object. If not set, system default is used.
	**/
	function get_languages($arr)
	{
		$odl = new object_data_list(
			array(
				"class_id" => CL_LANGUAGE,
				"parent" => array(),
				"status" => array(),
				"site_id" => array(),
				"lang_id" => array(),
				"sort_by" => "jrk",
				"oid" => array_keys(personnel_management::get_lang_conf($arr)),
			),
			array(
				CL_CRM_LANGUAGE => array("oid" => "oid", "name" => "name"),
			)
		);
		foreach($odl->arr() as $odle)
		{
			$options[$odle["oid"]] = $odle["name"];
		}
		return $options;
	}

	function special_url($arr, $ps)
	{
		$p = array(
			"id" => $arr["request"]["id"],
			//"return_url" => get_ru(),
			"group" => $arr["request"]["group"],
		);
		if($arr["request"]["return_url"])
			$p["return_url"] = $arr["request"]["return_url"];
		foreach($ps as $k => $v)
		{
			$p[$k] = $v;
		}
		return $this->mk_my_orb("change", $p);
	}

	/**
		@attrib name=delete_cands
	**/
	function delete_cands($arr)
	{
		$o = obj($arr["ofr_id"]);
		foreach($o->connections_from(array("type" => "RELTYPE_CANDIDATE")) as $conn)
		{
			$to = $conn->to();
			if(in_array($to->prop("person"), $arr["sel"]))
				$to->delete();
		}
		return $arr["post_ru"];
	}

	function cv_save_search($arr)
	{
		$o = new object;
		$o->set_class_id(CL_PERSONNEL_MANAGEMENT_CV_SEARCH_SAVED);
		$o->set_parent($arr["obj_inst"]->id());
		$o->set_name($arr["request"]["cv_search_saved_name"]);
		unset($arr["request"]["cv_search_saved_name"]);
		unset($arr["request"]["cv_search_button_save_search"]);
		foreach($arr["request"] as $k => $v)
		{
			// All the search properties start with cv_
			if(substr($k, 0, 3) == "cv_")
			{
				$o->set_meta($k, $v);
			}
		}
		$o->save();
		$arr["request"]["search_save"] = $o->id();
	}

	function add_skill_options($skills, $options, $disabled_options, $id, $lvl)
	{
		foreach($skills[$id] as $sid => $sdata)
		{
			if($sdata["subheading"])
				$disabled_options[] = $sid;

			$str = "";
			for($i = 0; $i < $lvl; $i++)
			{
				$str .= "- ";
			}

			$options[$sid] = $str.$sdata["name"];
			$this->add_skill_options(&$skills, &$options, &$disabled_options, $sid, $lvl + 1);
		}
	}
}
?>
