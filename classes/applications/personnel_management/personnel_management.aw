<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management.aw,v 1.19 2008/02/11 17:48:04 instrumental Exp $
// personnel_management.aw - Personalikeskkond 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT relationmgr=yes r2=yes no_status=1 no_comment=1 maintainer=kristo

@default group=general
@default table=objects
@default field=meta
@default method=serialize

@groupinfo general2 caption="&Uuml;ldine" parent=general
@default group=general2

@property persons_fld type=relpicker reltype=RELTYPE_MENU
@caption Isikute kaust

@property offers_fld type=relpicker reltype=RELTYPE_MENU
@caption Tööpakkumiste kaust

@property fields type=relpicker reltype=RELTYPE_SECTORS
@caption Tegevusvaldkonnad

@property person_ot type=relpicker reltype=RELTYPE_PERSON_OT
@caption Isikute objektitüüp

@property crmdb type=relpicker reltype=RELTYPE_CRM_DB
@caption Kliendibaas

@property owner_org type=relpicker reltype=RELTYPE_OWNER_ORG
@caption Omanikorganisatsioon

@groupinfo search_conf caption="Otsingu seaded" parent=general
@default group=search_conf

@property search_conf_tbl type=table no_caption=1

-------------------TÖÖOTSIJAD-----------------------
@groupinfo employee caption="Tööotsijad" submit=no

@groupinfo employee_search caption="Otsing" parent=employee
@default group=employee_search

@property search_save type=relpicker reltype=RELTYPE_SEARCH_SAVE
@caption Varasem otsing

@property search_cv type=form sclass=applications/personnel_management/personnel_management_cv_search sform=cv_search store=no
@caption Otsi CV-sid

----------------------------------------

# @groupinfo employee_list caption="Nimekiri" parent=employee submit=no
# @default group=employee_list

# @property employee_list_toolbar type=toolbar no_caption=1

# @layout employee_list type=hbox width=15%:85%

# @property employee_list_tree type=treeview no_caption=1 parent=employee_list

# @property employee_list_table type=table no_caption=1 parent=employee_list

----------------------------------------

@groupinfo candidate caption="Kandideerijad" submit=no
@default group=candidate


@property candidate_toolbar type=toolbar no_caption=1

@layout candidate type=hbox width=15%:85%

@property candidate_tree type=treeview no_caption=1 parent=candidate

@property candidate_table type=table no_caption=1 parent=candidate

----------------------------------------
@groupinfo offers caption="T&ouml;&ouml;pakkumised" submit=no

@groupinfo offers_ parent=offers caption="Üldine" submit=no
@default group=offers_

	@property offers_toolbar type=toolbar no_caption=1

	@layout offers type=hbox width=15%:85%

		@layout offers_tree_n_search type=vbox parent=offers

			@layout offers_tree type=vbox parent=offers_tree_n_search closeable=1 area_caption=Kaustad

				@property offers_tree type=treeview no_caption=1 parent=offers_tree
			
			@layout offers_search type=vbox parent=offers_tree_n_search closeable=1 area_caption=Otsing

				@layout os_top type=vbox parent=offers_search

					@property os_pr type=textbox parent=os_top captionside=top store=no size=18
					@caption Ametikoht

					@property os_area type=textbox parent=os_top captionside=top store=no size=18
					@caption Piirkond

				@layout os_dl_layout type=vbox parent=offers_search

					@property os_dl_from type=date_select store=no parent=os_dl_layout captionside=top format=day_textbox,month_textbox,year_textbox
					@caption T&auml;htaeg alates

					@property os_dl_to type=date_select store=no parent=os_dl_layout captionside=top format=day_textbox,month_textbox,year_textbox
					@caption T&auml;htaeg kuni

				@property os_status type=chooser store=no parent=offers_search captionside=top
				@caption Staatus

				@property act_s_sbt type=submit parent=offers_search no_caption=1
				@caption Otsi

	@property offers_table type=table no_caption=1 parent=offers

	@property offers_search_results_table type=table no_caption=1 parent=offers

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
@caption Objektitüüp

@reltype OWNER_ORG value=5 clid=CL_CRM_COMPANY
@caption Omanikorganisatsioon

@reltype SEARCH_SAVE value=6 clid=CL_BLAH
@caption Otsingu salvestus

@reltype DEFAULT_OFFERS_CFGFORM value=7 clid=CL_CFGFORM
@caption Tööpakkumiste default-seadetevorm

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
			"show" => "Vaata",
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
		switch($prop["name"])
		{
			case "os_pr":
			case "os_area":
			case "os_dl_from":
			case "os_dl_to":
				$s = $arr['request'][$prop["name"]];
				$this->dequote(&$s);
				$prop['value'] = $s;
				break;

			case "search_conf_tbl":
				$this->_get_search_conf_tbl($arr);
				break;

			case "employee_list_toolbar":
				$this->employee_list_toolbar($arr);
				break;

			case "candidate_toolbar":
				$this->candidate_toolbar($arr);
				break;

			case "offers_toolbar":
				$this->offers_toolbar($arr);
				break;

			case "employee_list_table":
				$this->employee_list_table($arr);
				break;

			case "candidate_table":
				$this->candidate_table($arr);
				break;

			case "offers_table":
				$this->offers_table($arr);
				break;

			case "employee_list_tree":
				return PROP_IGNORE;
				$this->employee_list_tree($arr);
				break;

			case "candidate_tree":
				$this->candidate_tree($arr);
				break;

			case "offers_tree":
				$this->offers_tree($arr);
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
			"candidate" => t("Kandideerijate otsingu tulemused")
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
		$t->add_item(0, array(
			"id" => $this->offers_fld,
			"name" => $arr["request"]["fld_id"] == $this->offers_fld ? "<b>".t("Aktiivsed tööpakkumised")."</b>" : t("Aktiivsed tööpakkumised"),
			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => $this->offers_fld)),
		));
		$objs = new object_tree(array(
			"parent" => $this->offers_fld,
			"class_id" => array(CL_ADMIN_IF, CL_MENU, CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
			"sort_by" => "objects.name",
			"status" => STAT_ACTIVE,
		));
		$obx = $objs->to_list();
		$img_inst = get_instance(CL_IMAGE);
		foreach($obx->arr() as $ob)
		{
			$id = $ob->id();
			$childs = new object_list(array(
				"parent" => $id,
				"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			));
			$cnt = $childs->count();
			$str = $cnt > 0 ? " (".$cnt.")" : "";
			$id_tag = ($ob->class_id() == CL_PERSONNEL_MANAGEMENT_JOB_OFFER) ? "job_id" : "fld_id";
			$t->add_item($ob->parent(), array(
				"id" => $id,
				"name" => ($arr["request"]["fld_id"] == $id || $arr["request"]["job_id"] == $id) ? "<b>".$ob->name().$str."</b>" : $ob->name().$str,
				"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], $id_tag => $id)),
				"iconurl" => $img_inst->get_url_by_id($id),
			));
		}
	}

	function offers_tree($arr)
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
		$counties = new object_list(array(
			"parent" => array(),
			"class_id" => CL_CRM_COUNTY,
			"lang_id" => array(),
			"site_id" => array(),
		));
		$county_inst = get_instance(CL_CRM_COUNTY);
		$county_tot = 0;
		foreach($counties->arr() as $county)
		{
			$cnt_county = $county_inst->get_job_offers(array(
				"id" => $county->id(),
				"parent" => $this->offers_fld,
			))->count();
			if($cnt_county == 0)
			{
				continue;
			}
			$str_county = " (".$cnt_county.")";
			$t->add_item("location", array(
				"id" => $county->id(),
				"name" => $arr["request"]["fld_id"] == $county->id() ? "<b>".$county->name().$str_county."</b>" : $county->name().$str_county,
				"url" => $this->mk_my_orb("change", array("id" => $arr["request"]["id"], "group" => $arr["request"]["group"], "fld_id" => $county->id(), "county_id" => $county->id())),
			));
			$county_tot++;
		}
		if($county_tot > 0)
		{
			$str_loc = " (".$county_tot.")";
			$t->add_item(0, array(
				"id" => "location",
				"name" => $arr["request"]["fld_id"] == "location" ? "<b>".t("Asukoht").$str_loc."</b>" : t("Asukoht").$str_loc,
				"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], "fld_id" => "location")),
			));
		}
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
	}

	function employee_list_toolbar($arr)
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
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "add",
			"caption" => t("Lisa"),
			"img" => "new.gif",
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta kandidaadid"),
			"action" => "delete_rels",
		));
	}
	
	function offers_toolbar($arr)
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
			"text" => t("Tööpakkumine"),
			"link" => html::get_new_url(CL_PERSONNEL_MANAGEMENT_JOB_OFFER, $pt, array(
				"return_url" => get_ru(),
				"personnel_management_id" => $arr["obj_inst"]->id(),
				"county_id" => $arr["request"]["county_id"],
			)),
			"href_id" => "add_bug_href"
		));
		$tb->add_menu_item(array(
			"parent" => "add",
			"text" => t("Kategooria"),
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

	function employee_list_table($arr)
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

	function birthday($birthday)
	{
		list($year, $month, $day) = explode("-", $birthday);
		$year_diff = date("Y") - $year;
		$month_diff = date("m") - $month;
		$day_diff = date("d") - $day;
		if ($month_diff < 0)
			$year_diff--;
		elseif ($month_diff == 0 && $day_diff < 0)
			$year_diff--;

		return $year_diff;	
	}

	function candidate_table_dir($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
		));
		$ol = new object_list(array(
			"class_id" => array(CL_ADMIN_IF, CL_MENU, CL_PERSONNEL_MANAGEMENT_JOB_OFFER),
			"parent" => $arr["request"]["fld_id"],
		));
		foreach($ol->arr() as $obj)
		{
			$id_tag = ($obj->class_id() == CL_PERSONNEL_MANAGEMENT_JOB_OFFER) ? "job_id" : "fld_id";
			$t->define_data(array(
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => $arr["request"]["group"], $id_tag => $obj->id())),
					"caption" => $obj->name(),
				)),
			));
		}
	}

	function candidate_table($arr)
	{
		if($this->can("view", $arr["request"]["fld_id"]))
		{
			return $this->candidate_table_dir($arr);
		}
		$t = &$arr["prop"]["vcl_inst"];
		$vars = $this->search_vars;
		$gender = array(1 => "mees", "naine");
		$conf = $arr["obj_inst"]->meta("search_conf_tbl");
		$conf = $conf["candidate"];
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
		foreach($conf as $name => $d)
		{
			if(!$d["disabled"])
			{
				$t->define_field(array(
					"name" => $name,
					"caption" => t($vars[$name]),
					"align" => "center",
				));
			}
		}
		if($this->can("view", $arr["request"]["job_id"]))
		{
			$job = get_instance(CL_PERSONNEL_MANAGEMENT_JOB_OFFER);
			$objs = $job->get_candidates(array(
				"id" => $arr["request"]["job_id"],
				"status" => 2,
			));
			foreach($objs->arr() as $obj)
			{
				$t->define_data(array(
					"id" => $obj->id(),
					"name" => $obj->name(),
					"age" => $this->birthday($obj->prop("birthday")),
					"gender" => t($gender[$obj->prop("gender")]),
					"applications" => "",
					"modtime" => date("Y-m-d H:i:s", $obj->prop("modified")),
					"show" => "",
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
			"caption" => t("Tähtaeg"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function offers_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_offers_table(&$t);

		$toopakkujad_ids = array();
		$toopakkujad = array();

		if(!is_oid($arr["request"]["county_id"]))
		{
			$fld_id = $this->can("view", $arr["request"]["fld_id"]) ? $arr["request"]["fld_id"] : $this->offers_fld; 

			$objs = new object_list(array(
				"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
				"parent" => $fld_id,
			));
		}
		else
		{
			$county_inst = get_instance(CL_CRM_COUNTY);
			$objs = $county_inst->get_job_offers(array(
				"id" => $arr["request"]["county_id"],
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
        		$end = t("Määramata");
        	}
			$t->define_data(array(
				"id" => $obj->id(),
				"name" => html::get_change_url($obj->id(), array("return_url" => get_ru()), $obj->name()),
				"profession" => $obj->prop("profession.name"),
				"org" => html::get_change_url($obj->prop("company"), array("return_url" => get_ru()), $obj->prop("company.name")),
				"location" => $obj->prop("location.name"),
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
}
?>
