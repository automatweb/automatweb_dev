<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/patent_office/trademark_manager.aw,v 1.16 2007/02/09 13:57:55 markop Exp $
// patent_manager.aw - Kaubam&auml;rgitaotluse keskkond 
/*

@classinfo syslog_type=ST_TRADEMARK_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

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

	@property trademark_add type=relpicker reltype=RELTYPE_ADD
	@caption Kaubam&auml;rgitaotluste lisamine


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
	@caption Kaubam&auml;rgitaotluste lisamine

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
			case "trademark_find_applicant_name":	
				$arr["obj_inst"]->set_meta("search_data" , $arr["request"]);
			break;
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
/*
- vasakus puus: Kinnitamata taotlused, Kinnitatud taotlused

*/
		

	function _get_trademark_tr($arr)
	{
		classload("core/icons");
		//$arr["prop"]["vcl_inst"] = get_instance("vcl/treeview");
		
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
		$arr["prop"]["vcl_inst"]->add_item(0, array(
			"id" => 2,
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
		$ol = new object_list();
		$filter = array(
			"class_id" => array(CL_PATENT),
			"lang_id" => array(),
			"site_id" => array(),
		);
		$data = $this_obj->meta("search_data");

		if($data["trademark_find_applicant_name"])
		{
			$filter["CL_PATENT.RELTYPE_APPLICANT.name"] = "%".$data["trademark_find_applicant_name"]."%";
		}
		if($data["trademark_find_procurator_name"])
		{
			$filter["CL_PATENT.RELTYPE_PROCURATOR.name"] = "%".$data["trademark_find_procurator_name"]."%";
		}
	
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
		$filter = array(
			"class_id" => array(CL_PATENT),
//			"parent" => $parent,
			"lang_id" => array(),
			"site_id" => array()
		);
		
		if($arr["request"]["p_id"] == "verified")
		{
			$filter["CL_PATENT.RELTYPE_TRADEMARK_STATUS.verified"] = 1;
		}
		
//		if($arr["request"]["p_id"] == "not_verified")
//		{
//			$filter["verified"] = new obj_predicate_not(1);
//		}

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
			$status = $trademark_inst->get_status($o);
			if($arr["request"]["p_id"] == "not_verified" && $status->prop("verified"))
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
			$type = $types[$o->prop("type")];
			if($o->prop("type") == 0 && $o->prop("word_mark"))
			{
				$type.= " (".$o->prop("word_mark").")";
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
			
			if(!(is_oid($o->prop("applicant")) && ($this->can("view" ,$o->prop("applicant")))))
			{
				$applicant = $o->get_first_obj_by_reltype("RELTYPE_APPLICANT");
			}
			else
			{
				$applicant = obj($o->prop("applicant"));
			}
			if(is_object($applicant))
			{
				$applicant_name = $applicant->name();
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
				$date = date("j.m.Y" , $status->prop("sent_date"));
			}
			else
			{
				$date = date("j.m.Y" , $o->created());
			}

			$t->define_data(array(
				"procurator" => $procurator,
				"nr" => $nr,
				"type" => $type,
				"applicant_name" => $applicant_name,
				"applicant_data" => $applicant_data,
				"date" => $date,
				"oid" => $o->id(),
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
- paremal tabelis: Märgi tüüp (sõnamärk, kujutismärk jne, kui sõnamärk, siis vastava tekstivälja sisu ka sulgudes), Taotluse number (sellel klikkides avaneb ka taotluse sisestusvorm, kui number puudub, siis on klikitav tekst Number puudub), Esitaja nimi, Esitaja kontaktandmed (kõik ühes väljas komaga eraldatult, aadressi pole vaja), voliniku nimi, Esitamise kuupäev, Vali tulp.		
*/
	function _init_objects_tbl(&$t)
	{
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
			"sortable" => 1
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
		$add_inst = get_instance(CL_TRADEMARK_ADD);
		$new_url = aw_ini_get("baseurl")."/".$arr["obj_inst"] ->prop("trademark_add");
		
		//$add_inst->mk_my_orb("parse_alias", array("alias" => array("target" => $arr["obj_inst"] ->prop("trademark_add")), CL_TRADEMARK_ADD));
		
		//$prop_obj->request_execute($realest_obj);
		
		//$add_inst->mk_my_orb("parse_alias" , array("alias" => array("target" => $arr["obj_inst"] -> prop("trademark_add"))));
		
		$tb->add_button(array(
			'name'=>'add_item',
			"img" => 'new.gif',
			'tooltip'=> t('Lisa taotlus'),
	//		'action' => 'new',
			'url' => $new_url,
	//		'confirm' => t(""),
			"target" => '_blank',
		));
		
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
		$patent_inst = get_instance(CL_PATENT);
		$object = obj($arr["id"]);
		if(is_oid($object->prop("verified_menu")))
		{
			$parent = $object->prop("verified_menu");
//			$num_ser = $object->prop("series");
		}
//		$ser = get_instance(CL_CRM_NUMBER_SERIES);
		foreach($arr["sel"] as $id)
		{
			$o = obj($id);
			$status = $patent_inst->get_status($o);
			$status->set_prop("verified",1);
			$status->set_name(t("Taotlus nr: ".$status->prop("nr")));
			
//			$tno = $ser->find_series_and_get_next(CL_PATENT,$num_ser);
//			$o->set_prop("nr" , $tno);
//			if($parent)
//			{
//				$status->set_parent($parent);
//			}
//			$o->save();
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

	/** 
		@attrib name=nightly_export
	**/
	function nightly_export($arr)
	{
		classload("core/date/date_calc");
		// list all patents created today
		$ol = new object_list(array(
			"class_id" => CL_PATENT,
			"lang_id" => array(),
			"site_id" => array(),
			"CL_PATENT.RELTYPE_TRADEMARK_STATUS.verified" => 1,
			"modified" => new obj_predicate_compare(OBJ_COMP_GREATER, get_day_start()) 
		));
		$xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
		$xml .= '<ENOTIF BIRTHCOUNT="'.$ol->count().'" CPCD="EE" WEEKNO="'.date("W").'" NOTDATE="'.date("Ymd").'">
';
		$tm = get_instance(CL_PATENT);

		foreach($ol->arr() as $o)
		{
			$status = $tm->get_status($o);
			$xml .= '	<BIRTH TRANTYP="ENN" INTREGN="'.sprintf("%08d", $status->prop("nr")).'" OOCD="EE" ORIGLAN="3" EXPDATE="'.date("Ymd", $o->prop("exhibition_date")).'" REGEDAT="'.date("Ymd", $o->prop("convention_date")).'" INTREGD="'.date("Ymd", $o->prop("exhibition_date")).'" DESUNDER="P">
';
				$xml .= '		<HOLGR>
';
					$xml .= "\t\t\t<NAME>\n";
						$xml .= "\t\t\t\t<NAMEL>".$o->prop("applicant.name")."</NAMEL>\n";
					$xml .= "\t\t\t</NAME>\n";

					if ($this->can("view", $o->prop("applicant")))
					{
					$appl = obj($o->prop("applicant"));
					$xml .= "\t\t\t<ADDRESS>\n";
					$adr_i = get_instance(CL_CRM_ADDRESS);
						if ($appl->class_id() == CL_CRM_PERSON)
						{
							$xml .= "\t\t\t\t<ADDRL>".$appl->prop("address.aadress")."</ADDRL>\n";
							$xml .= "\t\t\t\t<ADDRL>".$appl->prop("address.linn.name")."</ADDRL>\n";
							$xml .= "\t\t\t\t<ADDRL>".$appl->prop("address.postiindeks")."</ADDRL>\n";
//echo "aadres ".$appl->prop("address")." <br>";
//echo "riik = ".$appl->prop("address.riik")." <br>";
							if ($this->can("view", $appl->prop("address.riik")))
							{
								$xml .= "\t\t\t\t<COUNTRY>".$adr_i->get_country_code(obj($appl->prop("address.riik")))."</COUNTRY>\n";
//echo "country code from ".$appl->prop("address.riik.name")." => ".$adr_i->get_country_code(obj($appl->prop("address.riik")))." <br>";
							}
						}
						else
						{
							$xml .= "\t\t\t\t<ADDRL>".$appl->prop("contact.aadress")."</ADDRL>\n";
							$xml .= "\t\t\t\t<ADDRL>".$appl->prop("contact.linn.name")."</ADDRL>\n";
							$xml .= "\t\t\t\t<ADDRL>".$appl->prop("contact.postiindeks")."</ADDRL>\n";
							if ($this->can("view", $appl->prop("contact.riik")))
							{
								$xml .= "\t\t\t\t<COUNTRY>".$adr_i->get_country_code(obj($appl->prop("contact.riik")))."</COUNTRY>\n";
							}
						}
					$xml .= "\t\t\t</ADDRESS>\n";

					$xml .= "\t\t\t<LEGNATU>\n";
						$xml .= "\t\t\t\t<LEGNATT>".$appl->prop("ettevotlusvorm.name")."</LEGNATT>\n";
					$xml .= "\t\t\t</LEGNATU>\n";
					}
					/*if (!$this->can("view", $o->prop(""))
					{
						$xml .= "\t\t\t<CORRIND/>\n";
					}*/

				$xml .= "\t\t</HOLGR>\n";
				if ($this->can("view", $o->prop("procurator")))
				{
				$proc = obj($o->prop("procurator"));
				$xml .= "\t\t<REPGR CLID=\"".$proc->prop("code")."\">\n";
					$xml .= "\t\t\t<NAME>\n";
						$xml .= "\t\t\t\t<NAMEL>".$proc->name()."</NAMEL>\n";
					$xml .= "\t\t\t</NAME>\n";


					/*$xml .= '\t\t\t<ADDRESS>\n';
						$adr_i = get_instance(CL_CRM_ADDRESS);
						$xml .= '\t\t\t\t<ADDRL>'.$proc->prop("address.aadress").'</ADDRL>\n';
						$xml .= '\t\t\t\t<ADDRL>'.$proc->prop("address.linn.name").'</ADDRL>\n';
						$xml .= '\t\t\t\t<ADDRL>'.$proc->prop("address.postiindeks").'</ADDRL>\n';
						$xml .= '\t\t\t\t<COUNTRY>'.$adr_i->get_country_code(obj($proc->prop("address.riik"))).'</COUNTRY>\n';
					$xml .= '\t\t\t</ADDRESS>\n';*/

				$xml .= "\t\t</REPGR>\n";
				}
				$type = ""; 
				// save image to folder
				if ($this->can("view", $o->prop("reproduction")))
				{
				$im = obj($o->prop("reproduction"));
				$type = strtoupper(substr($im->name(), strrpos($im->name(), ".")));

				$fld = aw_ini_get("site_basedir")."/patent_files/";
				$fn = $fld .sprintf("%08d", $status->prop("nr")).$type;
				echo "saving file $fn <br>";
				$image_inst = get_instance(CL_FILE);
				$imd = $image_inst->get_file_by_id($im->id(), true);
				$f = fopen($fn ,"w");
				fwrite($f, $imd["content"]);
				fclose($f);
				}//tõstsin seda ettepoole, et ilma reproduktsioonita tahetakse ka tegelikult sõnalist osa näha
				$xml .= "\t\t<IMAGE NAME=\"".sprintf("%08d", $status->prop("nr"))."\" TEXT=\"".$o->prop("word_mark")."\" COLOUR=\"".($o->prop("colors") != "" ? "Y" : "N")."\" TYPE=\"".$type."\"/>\n";
				

				$xml .= "\t\t<MARTRGR>\n";
					$xml .= "\t\t\t<MARTREN>".$o->prop("element_translation")."</MARTREN>\n";
				$xml .= "\t\t</MARTRGR>\n";
				$typm = $o->prop("trademark_type");
//echo "typm = ".dbg::dump($typm)."  = <TYPMARI>".($typm["1"] == "1" ? "G" : "").($typm["0"] === "0" ? "C" : "")." <br>";
				$xml .= "\t\t<TYPMARI>".($typm["1"] == "1" ? "G" : "").($typm["0"] === "0" ? "C" : "")."</TYPMARI>\n";

				$xml .= "\t\t<MARDESGR>\n";
					if ($o->prop("trademark_character") == "")
					{
						$xml .= "\t\t\t<MARDESEN></MARDESEN>\n";
					}
					else
					{
						$xml .= "\t\t\t<MARDESEN><![CDATA[".$o->prop("trademark_character")."]]></MARDESEN>\n";
					}
				$xml .= "\t\t</MARDESGR>\n";

				$xml .= "\t\t<DISCLAIMGR>\n";
					if ($o->prop("undefended_parts") == "")
					{
						$xml .= "\t\t\t<DISCLAIMEREN></DISCLAIMEREN>\n";
					}
					else
					{
						$xml .= "\t\t\t<DISCLAIMEREN><![CDATA[".$o->prop("undefended_parts")."]]></DISCLAIMEREN>\n";
					}
				$xml .= "\t\t</DISCLAIMGR>\n";

				if ($o->prop("colors") != "")
				{
					$xml .= "\t\t<MARCOLI/>\n";
				}

				if ($o->prop("type") == 3)
				{
					$xml .= "\t\t<THRDMAR/>\n";
				}

				$xml .= "\t\t<COLCLAGR>\n";
					if ($o->prop("colors") == "")
					{
						$xml .= "\t\t\t<COLCLAEN></COLCLAEN>\n";
					}
					else
					{
						$xml .= "\t\t\t<COLCLAEN><![CDATA[".$o->prop("colors")."]]></COLCLAEN>\n";
					}
				$xml .= "\t\t</COLCLAGR>\n";

				$xml .= "\t\t<BASICGS NICEVER=\"9\">\n";
//echo dbg::dump($o->meta("products"));
					foreach(safe_array($o->meta("products")) as $k => $v)
					{
						$xml .= "\t\t\t<GSGR NICCLAI=\"".$k."\">\n";
							$xml .= "\t\t\t\t<GSTERMEN><![CDATA[".$v."]]></GSTERMEN>\n";
						$xml .= "\t\t\t</GSGR>\n";
						/*if ($this->can("view", $k))
						{
							$prod = obj($k);
							if ($this->can("view", $prod->parent()))
							{
								$parent = obj($prod->parent());
								$xml .= "\t\t\t<GSGR NICCLAI=\"".$parent->comment()."\">\n";
									if ($val == "")
									{	
										$xml .= "\t\t\t\t<GSTERMEN><![CDATA[".$val."]]></GSTERMEN>\n";
									}
								$xml .= "\t\t\t</GSGR>\n";
							}
						}*/
					}
				$xml .= "\t\t</BASICGS>\n";

				$xml .= "\t\t<BASGR>\n";
					$xml .= "\t\t\t<BASAPPGR>\n";
						$xml .= "\t\t\t\t<BASAPPD>".date("Ymd", $o->prop("exhibition_date"))."</BASAPPD>\n";
						$xml .= "\t\t\t\t<BASAPPN>".sprintf("%08d", $status->prop("nr"))."</BASAPPN>\n";
					$xml .= "\t\t\t</BASAPPGR>\n";
				$xml .= "\t\t</BASGR>\n";

				$xml .= "\t\t<PRIGR>\n";
					$xml .= "\t\t\t<PRICP>".$o->prop("convention_country")."</PRICP>\n";
//echo dbg::dump($o->prop("convention_date"));
					if ($o->prop("convention_date") > 1)
					{
						$xml .= "\t\t\t<PRIAPPD>".date("Ymd",$o->prop("convention_date"))."</PRIAPPD>\n";
					}
					$xml .= "\t\t\t<PRIAPPN>".$o->prop("convention_nr")."</PRIAPPN>\n";
				
				$xml .= "\t\t</PRIGR>\n";

				$xml .= "\t\t<DESPG>\n";
					$xml .= "\t\t\t<DCPCD>EE</DCPCD>\n";
				$xml .= "\t\t</DESPG>\n";

			$xml .= "\t</BIRTH>\n";
			$status->set_prop("exported", 1);
			$status->set_prop("export_date", time());
			$o->set_no_modify(true);
			aw_disable_acl();
			aw_disable_messages();
			$status->save();
			aw_restore_messages();
			aw_restore_acl();
		}

		$xml .= "</ENOTIF>\n";

		$fn = aw_ini_get("site_basedir")."/patent_xml/".date("Ymd").".xml";
		$f = fopen($fn, "w");
		fwrite($f, $xml);
		fclose($f);
		die("wrote $fn");
	}
}
?>
