<?php
// trademark_manager.aw - Kaubam&auml;rgitaotluse keskkond
/*

@classinfo syslog_type=ST_TRADEMARK_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop

@default table=objects
@default group=general
@default field=meta
@default method=serialize
#GENERAL
	@property not_verified_menu type=relpicker reltype=RELTYPE_NOT_VERIFIED_MENU
	@caption Kinnitamata taotluste kaust

	@property verified_menu type=relpicker reltype=RELTYPE_VERIFIED_MENU
	@caption Kinnitatud taotluste kaust

	@property series type=relpicker reltype=RELTYPE_SERIES
	@caption Numbriseeria

	@property patent_add type=relpicker reltype=RELTYPE_ADD
	@caption Patenditaotluste lisamine

	@property utility_model_add type=relpicker reltype=RELTYPE_ADD
	@caption Kasuliku mudeli taotluste lisamine

	@property trademark_add type=relpicker reltype=RELTYPE_ADD
	@caption Kaubam&auml;rgitaotluste lisamine

	@property admins type=relpicker reltype=RELTYPE_ADMIN multiple=1
	@caption Halduskeskkonna administraatorid


#TAOTLUSED
@groupinfo name=applications caption=Taotlused
@default group=applications

	@property objects_tb type=toolbar no_caption=1 store=no

	@layout objects_lay type=hbox width=20%:80%

		@layout objects_l type=vbox parent=objects_lay

			@layout trademark_tr_l type=vbox parent=objects_l closeable=1 area_caption=Taotluste&nbsp;puu
				@property trademark_tr type=treeview no_caption=1 store=no parent=trademark_tr_l
			@layout objects_find_params type=vbox parent=objects_l closeable=1 area_caption=Objektide&nbsp;otsing
				@property trademark_find_applicant_name type=textbox store=no parent=objects_find_params captionside=top size=30
				@caption Esitaja nimi

				@property trademark_find_procurator_name type=textbox store=no size=30 parent=objects_find_params captionside=top
				@caption Voliniku nimi

				@property trademark_find_start type=date_select store=no parent=objects_find_params captionside=top
				@caption Alates

				@property trademark_find_end type=date_select store=no parent=objects_find_params captionside=top
				@caption Kuni

				@property do_find_applications type=submit store=no parent=objects_find_params captionside=top no_caption=1
				@caption Otsi
		@property objects_tbl type=table no_caption=1 store=no parent=objects_lay


#EKSPORT
@groupinfo name=export caption=Eksport
@default group=export

	@property exp_dest type=textbox
	@caption Ekspordifaili asukoht serveris

	@property exp_link type=text
	@caption Ekspordi

#RELTYPES

	@reltype NOT_VERIFIED_MENU clid=CL_MENU value=1
	@caption Kinnitamata taotluste kaust

	@reltype VERIFIED_MENU clid=CL_MENU value=2
	@caption Kinnitatud taotluste kaust

	@reltype SERIES clid=CL_CRM_NUMBER_SERIES value=3
	@caption Numbriseeria

	@reltype ADD clid=CL_DOCUMENT value=4
	@caption Taotluste lisamine

	@reltype ADMIN clid=CL_GROUP value=5
	@caption Adminn

*/

class trademark_manager extends class_base
{
	function trademark_manager()
	{
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_TRADEMARK_MANAGER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "objects_tb":
				$this->_objects_tb($arr);
				break;
			case "objects_tbl":
				$this->_objects_tbl($arr);
				break;

			case "trademark_find_applicant_name":
			case "trademark_find_procurator_name":
			case "trademark_find_start":
			case "trademark_find_end":
				$search_data = $arr["obj_inst"]->meta("search_data");
				$prop["value"] = $search_data[$prop["name"]];
				break;
			case "exp_link":
				$prop["value"] = html::href(array(
					"url" =>  $this->mk_my_orb("nightly_export"),
					"caption" => t("EKSPORDI!")
				));
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "trademark_find_applicant_name":
				$arr["obj_inst"]->set_meta("search_data" , $arr["request"]);
			break;
		}
		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

/*
- vasakus puus: Kinnitamata taotlused, Kinnitatud taotlused

*/


	function _get_trademark_tr($arr)
	{
		classload("core/icons");

		$arr["prop"]["vcl_inst"]->start_tree (array (
			"type" => TREE_DHTML,
			"has_root" => 1,
			"tree_id" => "offers_tree",
			"persist_state" => 1,
			"root_name" => t("Taotlused"),
			"root_url" => "#",
//			"get_branch_func" => $this->mk_my_orb("get_tree_stuff",array(
//				"clid" => $arr["clid"],
//				"group" => $arr["request"]["group"],
//				"oid" => $arr["obj_inst"]->id(),
//				"set_retu" => get_ru(),
//				"parent" => " ",
//			)),
		));

		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => 1,
			"name" => t('Kinnitatud'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "verified",
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(1, array(
			"id" => 11,
			"name" => t('Kaubam&auml;rk'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "verified",
				"p_cl" => "tm"
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(1, array(
			"id" => 12,
			"name" => t('Patent'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "verified",
				"p_cl" => "pat"
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(1, array(
			"id" => 13,
			"name" => t('Kasulik mudel'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "verified",
				"p_cl" => "um"
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(1, array(
			"id" => 14,
			"name" => t('T&ouml;&ouml;stusdisain'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "verified",
				"p_cl" => "ind"
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(1, array(
			"id" => 15,
			"name" => t('EP patent'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "verified",
				"p_cl" => "epat"
			)),
		));

		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => 2,
			"name" => t('Arhiiv'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "archive",
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(2, array(
			"id" => 21,
			"name" => t('Kaubam&auml;rk'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "archive",
				"p_cl" => "tm"
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(2, array(
			"id" => 22,
			"name" => t('Patent'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "archive",
				"p_cl" => "pat"
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(2, array(
			"id" => 23,
			"name" => t('Kasulik mudel'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "archive",
				"p_cl" => "um"
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(2, array(
			"id" => 24,
			"name" => t('T&ouml;&ouml;stusdisain'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "archive",
				"p_cl" => "ind"
			)),
		));
		$arr["prop"]["vcl_inst"]->add_item(2, array(
			"id" => 25,
			"name" => t('EP patent'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "archive",
				"p_cl" => "epat"
			)),
		));

		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => 3,
			"name" => t('Kinnitamata'),
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "applications",
				"p_id" => "not_verified",
			)),
		));
	}

	function search_applications($this_obj)
	{
		$data = $this_obj->meta("search_data");
		$ol = new object_list();
		$applicant_name = empty($data["trademark_find_applicant_name"]) ? null : "%".$data["trademark_find_applicant_name"]."%";
		$procurator_name = empty($data["trademark_find_procurator_name"]) ? null : "%".$data["trademark_find_procurator_name"]."%";
		$filter = array(
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array (
					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array (
							"class_id" => array(CL_PATENT_PATENT),
							"CL_PATENT_PATENT.RELTYPE_APPLICANT.name" => $applicant_name,
							"CL_PATENT_PATENT.RELTYPE_PROCURATOR.name" => $procurator_name,
						)
					)),
					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array (
							"class_id" => array(CL_UTILITY_MODEL),
							"CL_UTILITY_MODEL.RELTYPE_APPLICANT.name" => $applicant_name,
							"CL_UTILITY_MODEL.RELTYPE_PROCURATOR.name" => $procurator_name,
						)
					)),
					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array (
							"class_id" => array(CL_PATENT),
							"CL_PATENT.RELTYPE_APPLICANT.name" => $applicant_name,
							"CL_PATENT.RELTYPE_PROCURATOR.name" => $procurator_name,
						)
					)),
					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array (
							"class_id" => array(CL_INDUSTRIAL_DESIGN),
							"CL_INDUSTRIAL_DESIGN.RELTYPE_APPLICANT.name" => $applicant_name,
							"CL_INDUSTRIAL_DESIGN.RELTYPE_PROCURATOR.name" => $procurator_name,
						)
					)),
					new object_list_filter(array(
						"logic" => "AND",
						"conditions" => array (
							"class_id" => array(CL_EURO_PATENT_ET_DESC),
							"CL_EURO_PATENT_ET_DESC.RELTYPE_APPLICANT.name" => $applicant_name,
							"CL_EURO_PATENT_ET_DESC.RELTYPE_PROCURATOR.name" => $procurator_name,
						)
					)),
				)
			)),
			"lang_id" => array(),
			"site_id" => array()
		);

 		if((date_edit::get_timestamp($data["trademark_find_start"]) > 1)|| (date_edit::get_timestamp($data["trademark_find_end"]) > 1))
 		{
 			if(date_edit::get_timestamp($data["trademark_find_start"]) > 1)
 			{
 				$from = date_edit::get_timestamp($data["trademark_find_start"]);
 			}
 			else
 			{
 				$from = 1;
 			}
 			if(date_edit::get_timestamp($data["trademark_find_end"]) > 1)
 			{
 				$to = date_edit::get_timestamp($data["trademark_find_end"])+(24*3600);
 			}
 			else
 			{
 				$to = time()*66;
 			}
 		 	$filter["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, ($from - 1), ($to + 1));
 		}
		$ol = new object_list($filter);
		return $ol;
	}

	function _objects_tbl($arr)
	{
		$verified = ($arr["request"]["p_id"] === "verified") ? 1 : null;
		$cl = $arr["request"]["p_cl"];
		$three_months_ago = time() - 3*30*86400;

		if ($arr["request"]["p_id"] === "archive")
		{
			$age = new obj_predicate_compare(OBJ_COMP_LESS, $three_months_ago);
			$verified = 1;
		}
		elseif ($verified)
		{
			$age = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $three_months_ago);
		}

		if ("tm" === $cl)
		{
			$filter = array(
				"class_id" => array(CL_PATENT),
				"CL_PATENT.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
				"CL_PATENT.RELTYPE_TRADEMARK_STATUS.verified_date" => $age,
				"lang_id" => array(),
				"site_id" => array()
			);
		}
		elseif ("pat" === $cl)
		{
			$filter = array(
				"class_id" => array(CL_PATENT_PATENT),
				"CL_PATENT_PATENT.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
				"CL_PATENT_PATENT.RELTYPE_TRADEMARK_STATUS.verified_date" => $age,
				"lang_id" => array(),
				"site_id" => array()
			);
		}
		elseif ("um" === $cl)
		{
			$filter = array(
				"class_id" => array(CL_UTILITY_MODEL),
				"CL_UTILITY_MODEL.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
				"CL_UTILITY_MODEL.RELTYPE_TRADEMARK_STATUS.verified_date" => $age,
				"lang_id" => array(),
				"site_id" => array()
			);
		}
		elseif ("ind" === $cl)
		{
			$filter = array(
				"class_id" => array(CL_INDUSTRIAL_DESIGN),
				"CL_INDUSTRIAL_DESIGN.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
				"CL_INDUSTRIAL_DESIGN.RELTYPE_TRADEMARK_STATUS.verified_date" => $age,
				"lang_id" => array(),
				"site_id" => array()
			);
		}
		elseif ("epat" === $cl)
		{
			$filter = array(
				"class_id" => array(CL_EURO_PATENT_ET_DESC),
				"CL_EURO_PATENT_ET_DESC.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
				"CL_EURO_PATENT_ET_DESC.RELTYPE_TRADEMARK_STATUS.verified_date" => $age,
				"lang_id" => array(),
				"site_id" => array()
			);
		}
		else
		{
			$filter = array(
				new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array (
						new object_list_filter(array(
							"logic" => "AND",
							"conditions" => array (
								"class_id" => array(CL_PATENT_PATENT),
								"CL_PATENT_PATENT.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
								"CL_PATENT_PATENT.RELTYPE_TRADEMARK_STATUS.verified_date" => $age
							)
						)),
						new object_list_filter(array(
							"logic" => "AND",
							"conditions" => array (
								"class_id" => array(CL_UTILITY_MODEL),
								"CL_UTILITY_MODEL.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
								"CL_UTILITY_MODEL.RELTYPE_TRADEMARK_STATUS.verified_date" => $age
							)
						)),
						new object_list_filter(array(
							"logic" => "AND",
							"conditions" => array (
								"class_id" => array(CL_PATENT),
								"CL_PATENT.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
								"CL_PATENT.RELTYPE_TRADEMARK_STATUS.verified_date" => $age
							)
						)),
						new object_list_filter(array(
							"logic" => "AND",
							"conditions" => array (
								"class_id" => array(CL_INDUSTRIAL_DESIGN),
								"CL_INDUSTRIAL_DESIGN.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
								"CL_INDUSTRIAL_DESIGN.RELTYPE_TRADEMARK_STATUS.verified_date" => $age
							)
						)),
						new object_list_filter(array(
							"logic" => "AND",
							"conditions" => array (
								"class_id" => array(CL_EURO_PATENT_ET_DESC),
								"CL_EURO_PATENT_ET_DESC.RELTYPE_TRADEMARK_STATUS.verified" => $verified,
								"CL_EURO_PATENT_ET_DESC.RELTYPE_TRADEMARK_STATUS.verified_date" => $age
							)
						)),
					)
				)),
				"lang_id" => array(),
				"site_id" => array()
			);
		}

		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_objects_tbl($t);

		if(!$arr["request"]["p_id"])
		{
			$filter = null;
		}

		//otsingust
		if(sizeof($arr["obj_inst"]->meta("search_data")) > 1)
		{
			$ol = $this->search_applications($arr["obj_inst"]);
			$arr["obj_inst"]->set_meta("search_data", null);
			$arr["obj_inst"]->save();
		}
		else
		{
			$ol = new object_list($filter);
		}
		$ol->sort_by(array(
			"prop" => "created",
			"order" => "desc"
		));


		$trademark_inst = get_instance(CL_PATENT);
		$person_inst = get_instance(CL_CRM_PERSON);
		$types = $trademark_inst->types;

		foreach($ol->arr() as $o)
		{
			$re = $trademark_inst->is_signed($o->id());
			$status = $trademark_inst->get_status($o);
			if($arr["request"]["p_id"] == "not_verified" && ($status->prop("verified") || (!($re["status"] == 1))))
			{
				continue;
			}
			$procurator = $type = $nr = $applicant_name = $applicant_data = $applicant = "";
			$procurator = $o->prop_str("procurator");
			if($this->can("view" , $o->prop("warrant")))
			{
				$file_inst = get_instance(CL_FILE);
				$procurator = html::href(array(
					"caption" => $procurator,
					"url" => "#",//html::get_change_url($o->id(), array("return_url" => $arr["post_ru"])),
					"onclick" => 'javascript:window.open("'.$file_inst->get_url($o->prop("warrant")).'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
				));
			}

			if (CL_PATENT === $o->class_id())
			{
				$type = $types[$o->prop("type")];
				if($o->prop("type") == 0 && $o->prop("word_mark"))
				{
					$type.= " (".$o->prop("word_mark").")";
				}
			}

			$nr_str = t("Number puudub");
			if($status->prop("nr"))
			{
				$nr_str = $status->prop("nr");
			}
			$nr = html::href(array(
				"caption" => $nr_str,
				"url" => "#",//html::get_change_url($o->id(), array("return_url" => $arr["post_ru"])),
				"onclick" => 'javascript:window.open("'.aw_ini_get("baseurl").'/'.$o->id().'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
			));

//			if(!(is_oid($o->prop("applicant")) && ($this->can("view" ,$o->prop("applicant")))))
//			{
				$applicant = $o->get_first_obj_by_reltype("RELTYPE_APPLICANT");
//			}
//			else
//			{
//				$applicant = obj($o->prop("applicant"));
//			}
			if(is_object($applicant))
			{
				$applicant_name = $trademark_inst->get_applicants_str($o);//$applicant->name();
				$applicant_data = "";
				if($applicant->class_id() == CL_CRM_PERSON)
				{
					$applicant_data = $person_inst->get_short_description($applicant->id());
				}
				else
				{
					$stuff = array();
					$stuff[] = html::obj_change_url($applicant);
					if(is_object($a_phone = $applicant->get_first_obj_by_reltype("RELTYPE_PHONE")))
					{
						$stuff[] = $a_phone->name();
					}

					if(is_object($a_mail = $applicant->get_first_obj_by_reltype("RELTYPE_EMAIL")))
					{
						$stuff[] = $a_mail->name();
					}
					$applicant_data = join("," , $stuff);
				}
			}

			if($status->prop("sent_date"))
			{
				$date = $status->prop("sent_date");
			}
			else
			{
				$date = $o->created();
			}

			$retval = "";
			if($re["status"] == 1)
			{
				$signatures_url = $this->mk_my_orb("change", array("group" => "signatures", "id" => $re["ddoc"]), CL_DDOC);
				$retval = html::href(array(
					"url" => $signatures_url,
					"target" => "new window",
					//"url" => "#",
					"caption" => t("Allkirjad"),
					// "title" => $title,
					//"onclick" => 'javascript:window.open("'.$signatures_url.'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
				));
			}

			try
			{
				$class = aw_ini_get("classes." . $o->class_id() . ".name");
			}
			catch (Exception $e)
			{
				$class = "N/A";
			}

			$t->define_data(array(
				"class" => $class,
				"procurator" => $procurator,
				"nr" => $nr,
				"type" => $type,
				"applicant_name" => $applicant_name,
				"applicant_data" => $applicant_data,
				"date" => $date,
				"oid" => $o->id(),
				"signatures" => $retval,
				"verify" => ($status->prop("verified")) ? "" : html::href(array(
					"caption" => t("Kinnita"),
					"url" => "#",
					"onclick" => 'javascript:window.open("'.
						$this->mk_my_orb("verify",array(
							"popup" => 1,
							"sel" => array($o->id() => $o->id()),
							"id" => $arr["obj_inst"]->id(),
						))
					.'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
				)),
			));
		}
	}

/*
- paremal tabelis: M2rgi tyyp (s5nam2rk, kujutism2rk jne, kui s6nam2rk, siis vastava tekstiv2lja sisu ka sulgudes), Taotluse number (sellel klikkides avaneb ka taotluse sisestusvorm, kui number puudub, siis on klikitav tekst Number puudub), Esitaja nimi, Esitaja kontaktandmed (k6ik yhes v2ljas komaga eraldatult, aadressi pole vaja), voliniku nimi, Esitamise kuup2ev, Vali tulp.
*/
	function _init_objects_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "class",
			"caption" => t("Taotluse t&uuml;&uuml;p"),
			"align" => "center",
			"sortable" => 1,
			"filter" => "automatic"
		));

		$t->define_field(array(
			"name" => "type",
			"caption" => t("M&auml;rgi t&uuml;&uuml;p"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "nr",
			"caption" => t("Taotluse number"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "applicant_name",
			"caption" => t("Esitaja nimi"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "applicant_data",
			"caption" => t("Esitaja kontaktandmed"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "procurator",
			"caption" => t("Voliniku nimi"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "date",
			"caption" => t("Esitamise kuup&auml;ev"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y",
		));

		$t->define_field(array(
			"name" => "signatures",
			"caption" => t("Allkirjad"),
			"align" => "center",
//			"sortable" => 1
		));

		$t->define_chooser(array(
			"caption" => t("Vali"),
			"field" => "oid",
			"name" => "sel"
		));

		if(!($_GET["p_id"] == "verified"))
		{
			$t->define_field(array(
				"name" => "verify",
				"caption" => t("Kinnita"),
			));
		}
	}

	function _objects_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			"name" => "add_item",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus")
		));

		if (is_oid($arr["obj_inst"] ->prop("trademark_add")))
		{
			$add_trademark_url = aw_ini_get("baseurl")."/".$arr["obj_inst"] ->prop("trademark_add");
			$tb->add_menu_item(array(
				"parent" => "add_item",
				"text" => t("Kaubam&auml;rgitaotlus"),
				"link" => $add_trademark_url,
				"target" => "_blank"
			));
		}

		if (is_oid($arr["obj_inst"] ->prop("patent_add")))
		{
			$add_patent_url = aw_ini_get("baseurl")."/".$arr["obj_inst"] ->prop("patent_add");
			$tb->add_menu_item(array(
				"parent" => "add_item",
				"text" => t("Patenditaotlus"),
				"link" => $add_patent_url,
				"target" => "_blank"
			));
		}

		if (is_oid($arr["obj_inst"] ->prop("utility_model_add")))
		{
			$add_utility_model_url = aw_ini_get("baseurl")."/".$arr["obj_inst"] ->prop("utility_model_add");
			$tb->add_menu_item(array(
				"parent" => "add_item",
				"text" => t("Kasuliku mudeli taotlus"),
				"link" => $add_utility_model_url,
				"target" => "_blank"
			));
		}

		if (is_oid($arr["obj_inst"] ->prop("industrial_design_add")))
		{
			$add_industrial_design_url = aw_ini_get("baseurl")."/".$arr["obj_inst"] ->prop("industrial_design_add");
			$tb->add_menu_item(array(
				"parent" => "add_item",
				"text" => t("T&ouml;&ouml;stusdisaini taotlus"),
				"link" => $add_industrial_design_url,
				"target" => "_blank"
			));
		}

		if (is_oid($arr["obj_inst"] ->prop("euro_patent_et_desc_add")))
		{
			$add_industrial_design_url = aw_ini_get("baseurl")."/".$arr["obj_inst"] ->prop("euro_patent_et_desc_add");
			$tb->add_menu_item(array(
				"parent" => "add_item",
				"text" => t("EP patendi taotlus"),
				"link" => $add_euro_patent_et_desc_url,
				"target" => "_blank"
			));
		}

		$tb->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta'),
			'url' => "",
	//		'action' => 'delete_procurements',
	//		'confirm' => t(""),
		));
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta'),
			'action' => 'delete_applications',
			'confirm' => t("Kas oled kindel et soovid valitud taotlused kustudada?"),
		));
		$tb->add_button(array(
			'name' => 'refresh',
			'img' => 'refresh.gif',
			'tooltip' => t('V&auml;rskenda'),
			'url' => "",
		//	'action' => 'delete_procurements',
		//	'confirm' => t(""),
		));
		$tb->add_button(array(
			'name' => 'verify',
			'img' => 'restore.gif',
			'tooltip' => t('Kinnita'),
			'url' => "",
			'action' => 'verify',
		//	'confirm' => t(""),
		));
	}

	/**
		@attrib name=delete_applications
	**/
	function delete_applications($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	/**
		@attrib name=verify all_args=1
	**/
	function verify($arr)
	{
		$trademark_inst = get_instance(CL_PATENT);

		foreach($arr["sel"] as $id)
		{
			$o = obj($id);
			$status = $trademark_inst->get_status($o);
			$status->set_prop("verified", 1);
			$status->set_name(t("Taotlus nr: ".$status->prop("nr")));
			$status->save();
		}

		if($arr["popup"])
		{
			die('<script type="text/javascript">
				window.opener.location.reload();
				window.close();
				</script>'
			);
		}
		else
		{
			return $arr["post_ru"];
		}
	}

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

	function __application_sorter($a, $b)
	{
		$as = $a->get_first_obj_by_reltype("RELTYPE_TRADEMARK_STATUS");
		$bs = $b->get_first_obj_by_reltype("RELTYPE_TRADEMARK_STATUS");
		if(is_object($as) && is_object($bs))
		{
			return  $as->prop("nr") - $bs->prop("nr");
		}
		else
		{
			return  $a->id() - $b->id();
		}
	}

	/**
		@attrib name=nightly_export nologin="1"
	**/
	function nightly_export($arr)
	{
		classload("core/date/date_calc");

		$clidx = array(
			CL_PATENT => "kaubam2rgid_",
			CL_PATENT_PATENT => "patendid_",
			CL_UTILITY_MODEL => "kasulikudmudelid_",
			CL_INDUSTRIAL_DESIGN => "t88stusdisainid_",
			CL_EURO_PATENT_ET_DESC => "europatendid_"
		);
		$clidx2 = array(
			CL_PATENT => "CL_PATENT",
			CL_PATENT_PATENT => "CL_PATENT_PATENT",
			CL_UTILITY_MODEL => "CL_UTILITY_MODEL",
			CL_INDUSTRIAL_DESIGN => "CL_INDUSTRIAL_DESIGN",
			CL_EURO_PATENT_ET_DESC => "CL_EURO_PATENT_ET_DESC"
		);

		// list all intellectual prop objs created yesterday
		$verified = 1;
		$age = new obj_predicate_compare(OBJ_COMP_BETWEEN,(get_day_start()-(24*3600)) ,  get_day_start());

		// parse objs
		$xml_data = array(); // array of DOMDocuments grouped by aw class id

		foreach ($clidx as $clid => $value)
		{
			$filter = array(
				"class_id" => $clid,
				$clidx2[$clid] . ".RELTYPE_TRADEMARK_STATUS.verified" => $verified,
				$clidx2[$clid] . ".RELTYPE_TRADEMARK_STATUS.modified" => $age,
				"lang_id" => array(),
				"site_id" => array()
			);

			$ol = new object_list($filter);
			$ol->sort_by_cb(array(&$this, "__application_sorter"));

			foreach($ol->arr() as $o)
			{
				// get xml from ip obj
				$inst = $o->instance();
				$xml_data[$clid]["data"] .= str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "", $inst->get_po_xml($o)->saveXML());
				$xml_data[$clid]["count"] += 1;

				// indicate that object has been exported
				$status = $inst->get_status($o);
				$status->set_no_modify(true);
				$status->set_prop("exported", 1);
				$status->set_prop("export_date", time());
				$o->set_no_modify(true);
				aw_disable_acl();
				aw_disable_messages();
				$status->save();
				aw_restore_messages();
				aw_restore_acl();
			}
		}

		foreach ($xml_data as $clid => $data)
		{
			// xml header and contents
			$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-4\"?>\n";
			$xml .= '<ENOTIF BIRTHCOUNT="'.$data["count"].'" CPCD="EE" WEEKNO="'.date("W").'" NOTDATE="'.date("Ymd").'">' . "\n";
			$xml .= $data["data"];
			$xml .= "</ENOTIF>\n";

			// write file
			$cl = $clidx[$clid];// file name prefix
			$fn = aw_ini_get("site_basedir")."/patent_xml/" . $cl . date("Ymd") . ".xml";
			$f = fopen($fn, "w");
			fwrite($f, iconv("UTF-8", "ISO-8859-4", $xml));
			fclose($f);
			echo "wrote {$fn}\n";
		}

		exit("Done.");
	}

	//replace reserved characters
	function rere($string)
	{
		$string = str_replace("&" , "&amp;" , $string);
		$string = str_replace("<" , "&lt;" , $string);
		$string = str_replace(">" , "&gt;" , $string);
		$string = str_replace("%" , "&#37;" , $string);
		$string = str_replace('"' , " &quot;" , $string);
		$string = str_replace("'" , "&apos;" , $string);
		$string = iconv("iso-8859-4", "UTF-8", $string);
		return $string;
	}
}
?>
