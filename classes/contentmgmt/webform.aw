<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/webform.aw,v 1.36 2005/01/06 15:30:54 ahti Exp $
// webform.aw - Veebivorm 
/*

@classinfo syslog_type=ST_WEBFORM relationmgr=yes no_status=1

@default table=objects

------------- general -------------
@default group=general

@property on_init type=hidden newonly=1
@caption Initsialiseeri objekt

@property form_type type=select newonly=1 method=serialize field=meta
@caption Vormi t&uuml;&uuml;p

@property form_type_value type=text editonly=1
@caption Vormi t&uuml;&uuml;p

@property def_name type=textbox method=serialize field=meta
@caption Saatja nimi

@property def_mail type=textbox method=serialize field=meta
@caption Saatja e-mail

@property obj_name type=select multiple=1 size=3 field=meta method=serialize
@caption Millised sisestatud v&auml;&auml;rtused pannakse nimeks

@property redirect type=textbox field=meta method=serialize
@caption Kuhu suunata peale t&auml;tmist

------------- end: general -------------


------------- form -------------
@groupinfo form caption="Vorm" submit=no

@property navtoolbar type=toolbar group=form no_caption=1
@caption Toolbar

@property form type=callback callback=callback_form group=form no_caption=1
@caption Vorm

@property subaction type=hidden store=no group=form,props
@caption Subaction
------------- end: form -------------


------------- props -------------
@groupinfo props caption="Omadused" submit=no

@property availtoolbar type=toolbar group=props no_caption=1
@caption Toolbar

@property props type=callback callback=callback_props group=props no_caption=1
@caption Omadused
------------- end: props -------------


------------- styles -------------
@groupinfo styles caption="Stiilid"

@property style_folder type=relpicker reltype=RELTYPE_STYLE_FOLDER field=meta method=serialize group=styles
@caption Stiilide kaust

@property def_caption_style type=select group=styles field=meta method=serialize
@caption Vaikimisi pealkirja stiil

@property def_prop_style type=select group=styles field=meta method=serialize
@caption Vaikimisi elemendi stiil

@property def_form_style type=select group=styles field=meta method=serialize
@caption Tabeli stiil

@property styles type=callback callback=callback_styles group=styles no_caption=1
@caption Stiilid
------------- end: styles -------------

------------- preview -------------
@groupinfo preview caption="Eelvaade" submit=no

@property preview type=callback callback=callback_preview group=preview no_caption=1
@caption Eelvaade
------------- end: preview -------------


@groupinfo show_entries caption="Sisestused" submit=no


------------- entries -------------
@groupinfo entries caption="Näita sisestusi" submit=no parent=show_entries

@property entries_toolbar type=toolbar group=entries,search no_caption=1
@caption Sisestuste toolbar

@property entries type=table group=entries no_caption=1
@caption Sisestused
------------- end: entries -------------


------------- search -------------
@groupinfo search caption="Otsing" parent=show_entries submit_method=get submit=no

@property search type=text store=no no_caption=1 group=search
@caption Otsing
------------- end: search -------------


@groupinfo controllers caption="Kontrollerid" submit=no


------------- set_controllers -------------
@groupinfo set_controllers caption="Salvestamine" parent=controllers

@property set_controller_folder type=relpicker reltype=RELTYPE_CONTROLLER_FOLDER field=meta method=serialize group=set_controllers
@caption Kontrollerite kaust

@property submit_controllers type=callback callback=callback_submit_controllers group=set_controllers no_caption=1
@caption Kontrollerid
------------- end: set_controllers -------------


------------- get_controllers -------------
@groupinfo get_controllers caption="Näitamine" parent=controllers

@property get_controller_folder type=relpicker reltype=RELTYPE_CONTROLLER_FOLDER field=meta method=serialize group=get_controllers
@caption Kontrollerite kaust

@property view_controllers type=callback callback=callback_view_controllers group=get_controllers no_caption=1
@caption Kontrollerid
------------- end: get_controllers -------------


------------- relations -------------

@reltype METAMGR value=1 clid=CL_METAMGR
@caption Muutujate haldur

@reltype CFGFORM value=2 clid=CL_CFGFORM
@caption Seadete vorm

@reltype CONTROLLER value=3 clid=CL_CFGCONTROLLER
@caption Salvestamise kontroller

@reltype VIEWCONTROLLER value=4 clid=CL_CFG_VIEW_CONTROLLER
@caption N&auml;tamise kontroller

@reltype EMAIL value=5 clid=CL_ML_MEMBER
@caption Meiliaadress

@reltype OBJECT_TYPE value=6 clid=CL_OBJECT_TYPE
@caption Objekti t&uuml;&uuml;p

@reltype REGISTER value=7 clid=CL_REGISTER
@caption Register

@reltype STYLE value=8 clid=CL_CSS
@caption Stiil

@reltype STYLE_FOLDER value=9 clid=CL_MENU
@caption Stiilide kaust

@reltype CONTROLLER_FOLDER value=10 clid=CL_MENU
@caption Kontrollerite kaust

@reltype OBJECT_EXPORT value=11 clid=CL_OBJECT_EXPORT
@caption Objektide eksport

@reltype CAL_REG_FORM value=12 clid=CL_CALENDAR_REGISTRATION_FORM
@caption S&uuml;ndmuse vorm

@reltype CAL_REG_FORM_CONF value=13 clid=CL_CALENDAR_REGISTRATION_FORM_CONF
@caption S&uuml;ndmuse vormi konf

*/

class webform extends class_base
{
	function webform()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/webform",
			"clid" => CL_WEBFORM,
		));
		$this->n_props = array("checkboxes", "radiobuttons");
		$this->trans_names = array(
			"text" => t("Tekst"),
			"textbox" => t("V&auml;ike tekstikast"),
			"classificator" => t("Valikv&auml;li"),
			"date_select" => t("Kuup&auml;evavalik"),
			"textarea" => t("Suur tekstikast"),
			"hidden" => t("Peidetud v&auml;li"),
			"submit" => t("Saada nupp"),
			"reset" => t("T&uuml;hista nupp"),
		);
		$this->def_props = array(
			"firstname" => "Eesnimi",
			"lastname" => "Perekonnanimi",
			"co_name" => "Organisatsioon",
			"address" => "Aadress",
			"phone" => "Telefon",
			"fax" => "Faks",
			"email" => "E-post",
		);
		$this->no_props = $this->make_keys(array("status", "name", "comment", "register_id", "person_id"));
	}
	
	function callback_on_load($arr)
	{
		$this->cfgform_i = get_instance(CL_CFGFORM);
		if(is_oid($arr["request"]["id"]) && $this->can("view", $arr["request"]["id"]))
		{
			$obj_inst = obj($arr["request"]["id"]);
			if($this->cfgform = $obj_inst->get_first_obj_by_reltype("RELTYPE_CFGFORM"))
			{
				$this->cfgform_i->_init_cfgform_data($this->cfgform);
			}
			if(!$arr["new"])
			{
				if($obj_inst->prop("form_type") != CL_CALENDAR_REGISTRATION_FORM )
				{
					$this->p_clid = CL_REGISTER_DATA;
				}
				else
				{
					$this->p_clid = CL_CALENDAR_REGISTRATION_FORM;
				}
				if($obj_inst->prop("form_type") != $this->p_clid)
				{
					$obj_inst->set_prop("form_type", $this->p_clid);
					$obj_inst->save();
				}
			}
		}
	}
	
	function callback_mod_tab($arr)
	{
		if($arr["id"] == "show_entries")
		{
			if($arr["obj_inst"]->prop("form_type") == CL_REGISTER_DATA)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	function callback_pre_save($arr)
	{
		if($this->cfgform)
		{
			$this->cfgform_i->callback_pre_save(array(
				"obj_inst" => &$this->cfgform,
				"request" => array("subclass" => $this->p_clid),
			));
			$this->cfgform->save();
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "form_type_value":
				$prop["value"] = $arr["obj_inst"]->prop("form_type") != CL_CALENDAR_REGISTRATION_FORM ? t("Tavaline vorm") : t("Sündmusele registreerimine");
				break;
				
			case "form_type":
				$prop["options"] = array(
					CL_REGISTER_DATA => t("Tavaline vorm"),
					CL_CALENDAR_REGISTRATION_FORM => t("Sündmusele registreerimine"),
				);
				break;
				
			case "search":
				$register = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_REGISTER");
				$s = get_instance(CL_REGISTER_SEARCH);
				$prop["value"] = $s->show(array(
					"id" => $register->prop("search_o"),
					"no_form" => 1,
				));
				break;
				
			case "entries_toolbar":
				$this->entries_toolbar($arr);
				break;
				
			case "def_caption_style":
			case "def_prop_style":
			case "def_form_style":
				$this->get_rel_props(array(
					"obj_inst" => $arr["obj_inst"],
					"prop" => "style",
				));
				$prop["options"] = array(0 => "-- Vali --") + $this->all_rels;
				break;
				
			case "obj_name":
				$prop["options"] = array("-- vali --");
				foreach(safe_array($this->cfgform_i->prplist) as $key => $val)
				{
					$prop["options"][$key] = $val["caption"];
				}
			case "def_name":
			case "def_mail":
			case "redirect":
				if($arr["obj_inst"]->prop("form_type") != CL_REGISTER_DATA)
				{
					return PROP_IGNORE;
				}	
				break;
				
			case "entries":
				$register = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_REGISTER");
				$register_i = get_instance(CL_REGISTER);
				$register_i->do_data_tbl(array(
					"obj_inst" => $register,
					"prop" => &$arr["prop"],
					"request" => &$arr["request"],
				));
				break;
				
			case "search":
				$register = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_REGISTER");
				$s = get_instance(CL_REGISTER_SEARCH);
				$prop["value"] = $s->show(array(
					"id" => $register->prop("search_o"),
					"no_form" => 1,
				));
				break;
				
			case "navtoolbar":
				$tb = &$prop["vcl_inst"];

				$tb->add_button(array(
					"name" => "save",
					"tooltip" => "Salvesta",
					"url" => "javascript:submit_changeform()",
					"img" => "save.gif",
				));
				
				$tb->add_button(array(
					"name" => "delete",
					"tooltip" => "Kustuta valitud omadused",
					"url" => "javascript:document.changeform.subaction.value='delete';submit_changeform();",
					"img" => "delete.gif",
					"confirm" => "Oled kindel, et tahad antud omadused kustutada?",
				));
				break;
				
			case "availtoolbar":
				$this->cfgform_i->gen_availtoolbar($arr);
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
			case "on_init":
				if(!$arr["new"])
				{
					return PROP_IGNORE;
				}
				$this->_on_init($arr);
				break;
				
			case "view_controllers":
				if($this->cfgform)
				{
					$this->cfgform->set_meta("view_controllers", $arr["request"]["view_controllers"]);
				}
				break;
			
			case "submit_controllers":
				if($this->cfgform)
				{
					$this->cfgform->set_meta("controllers", $arr["request"]["controllers"]);
				}
				break;
				
			case "props":
				if($this->cfgform)
				{
					$this->add_new_properties($arr);
					if(($metamgr = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_METAMGR")) && ($object_type = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE")))
					{
						$classificator = $object_type->meta("classificator");
						foreach(safe_array($this->cfgform_i->cfg_proplist) as $key => $val)
						{
							if($this->cfgform_i->all_props[$key]["type"] == "classificator" && !$classificator[$key])
							{
								$no = obj();
								$no->set_class_id(CL_META);
								$no->set_status(STAT_ACTIVE);
								$no->set_parent($metamgr->id());
								$no->set_name($val["name"]);
								$no->save();
								$classificator[$key] = $no->id();
							}
						}
						$object_type->set_meta("classificator", $classificator);
						$object_type->save();
					}
				}
				break;
				
			case "form":
				if($this->cfgform)
				{
					$this->_save_form($arr);
				}
				break;
			case "entries":
				$awa = safe_array($arr["request"]["select"]);
				foreach($awa as $k => $v)
				{
					if ($k == $v)
					{
						$o = obj($k);
						$o->delete();
					}
				}
				break;
			case "styles":
				$arr["obj_inst"]->set_meta("styles", safe_array($arr["request"]["style"]));
				$arr["obj_inst"]->set_meta("m_styles", safe_array($arr["request"]["m_style"]));
				$arr["obj_inst"]->save();
				break;
		}
		return $retval;
	}
	
	function _save_form($arr)
	{
		if(!$object_type = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE"))
		{
			return PROP_IGNORE;
		}
		$classificator = $object_type->meta("classificator");
		$clf_type = $arr["request"]["clf_type"];
		$this->cfgform_i->save_layout(array(
			"obj_inst" => &$this->cfgform,
			"request" => &$arr["request"],
		));
		$prplist = $this->cfgform_i->cfg_proplist;
		foreach(safe_array($arr["request"]["prp_opts"]) as $key => $val)
		{
			if($prplist[$key])
			{
				foreach($val as $key2 => $val2)
				{
					$prplist[$key][$key2] = $arr["request"]["prp_opts"][$key][$key2];
				}
			}
		}
		if($arr["request"]["subaction"] == "delete")
		{
			$mark = $arr["request"]["mark"];
			foreach(safe_array($mark) as $pkey => $val)
			{
				if(is_oid($classificator[$pkey]) && $this->can("delete", $classificator[$pkey]))
				{
					$meta = obj($classificator[$pkey]);
					$meta->delete();
				}
				unset($classificator[$pkey]);
				unset($clf_type[$key]);
			}
		}
		foreach(safe_array($arr["request"]["prp_metas"]) as $key => $val)
		{
			$prps = explode(";", $val);
			if($prplist[$key] && !empty($prps) && is_oid($classificator[$key]) && $this->can("view", $classificator[$key]))
			{
				$e_prp_list = new object_list(array(
					"parent" => $classificator[$key],
					"class_id" => CL_META,
					"status"=> STAT_ACTIVE,
				));
				$e_prps = $e_prp_list->names();
				foreach($prps as $prp)
				{
					$prp = trim($prp);
					if(in_array($prp, $e_prps) || empty($prp))
					{
						continue;
					}
					$no = obj();
					$no->set_class_id(CL_META);
					$no->set_status(STAT_ACTIVE);
					$no->set_parent($classificator[$key]);
					$no->set_name($prp);
					$no->save();
				}
			}
		}
		$object_type->set_meta("clf_type", $clf_type);
		$object_type->set_meta("classificator", $classificator);
		$object_type->save();
		$this->cfgform_i->cfg_proplist = $prplist;
	}
	
	function _on_init($arr)
	{
		$arr["obj_inst"]->save();
		$this->p_clid = $arr["request"]["form_type"];
		$form = obj();
		$form->set_name(($this->p_clid == CL_REGISTER_DATA ? "Registri andmed " : "Sündmuse vorm ").$arr["obj_inst"]->id());
		$form->set_parent($arr["obj_inst"]->id());
		$form->set_class_id($this->p_clid);
		$form->set_status(STAT_ACTIVE);
		$form->save();
		$cfgform = obj();
		$cfgform->set_parent($arr["obj_inst"]->id());
		$cfgform->set_class_id(CL_CFGFORM);
		$cfgform->set_name("Seadete vorm ".$arr["obj_inst"]->id());
		$cfgform->set_status(STAT_ACTIVE);
		$cfgform->save();
		// well, seems that this is the only way -- ahz
		$this->cfgform_i->set_property(array(
			"new" => 1,
			"prop" => array("name" => "subclass"),
			"request" => array("subclass" => $this->p_clid),
		));
		// so, we to reverse the property adding of cfgform also -- ahz
		$cfgform->set_prop("subclass", $this->p_clid);
		$cfgform->set_meta("cfg_groups", array("data" => array("caption" => "Andmed")));
		$cfgform->save();
		$arr["obj_inst"]->connect(array(
			"to" => $cfgform->id(),
			"reltype" => "RELTYPE_CFGFORM",
		));
		
		$object_type = obj();
		$object_type->set_parent($arr["obj_inst"]->id());
		$object_type->set_class_id(CL_OBJECT_TYPE);
		$object_type->set_name("Objekti tp ".$arr["obj_inst"]->id());
		$object_type->set_status(STAT_ACTIVE);
		$object_type->set_prop("use_cfgform", $cfgform->id());
		$object_type->set_prop("type", $this->p_clid);
		$object_type->save();
		$arr["obj_inst"]->connect(array(
			"to" => $object_type->id(),
			"reltype" => "RELTYPE_OBJECT_TYPE",
		));
		$object_type->connect(array(
			"to" => $cfgform->id(),
			"reltype" => "RELTYPE_OBJECT_CFGFORM",
		));
		
		$metamgr = obj();
		$metamgr->set_parent($arr["obj_inst"]->id());
		$metamgr->set_class_id(CL_METAMGR);
		$metamgr->set_name("Muutujate haldus ".$arr["obj_inst"]->id());
		$metamgr->set_status(STAT_ACTIVE);
		$metamgr->save();
		$arr["obj_inst"]->connect(array(
			"to" => $metamgr->id(),
			"reltype" => "RELTYPE_METAMGR",
		));
		if($arr["request"]["form_type"] == CL_REGISTER_DATA)
		{
			$nlg = $this->get_cval("non_logged_in_users_group");
			$g_oid = users::get_oid_for_gid($nlg);
			$group = obj($g_oid);
			$dir = obj();
			$dir->set_parent($arr["obj_inst"]->parent());
			$dir->set_class_id(CL_MENU);
			$dir->set_name("Sisestused ".$arr["obj_inst"]->id());
			$dir->set_status(STAT_ACTIVE);
			$dir->save();
			$dir->acl_set($group, array("can_add" => 1, "can_view" => 1));
			$dir->save();
			
			$register = obj();
			$register->set_parent($arr["obj_inst"]->parent());
			$register->set_class_id(CL_REGISTER);
			$register->set_name("Register ".$arr["obj_inst"]->id());
			$register->set_status(STAT_ACTIVE);
			$register->set_prop("data_cfgform", $cfgform->id());
			$register->set_prop("default_cfgform", 1);
			$register->set_prop("data_rootmenu", $dir->id());
			$register->set_prop("show_all", 1);
			$register->set_prop("per_page", 100);
			$register->save();
			$arr["obj_inst"]->connect(array(
				"to" => $register->id(),
				"reltype" => "RELTYPE_REGISTER",
			));
			$form->connect(array(
				"to" => $register->id(),
				"reltype" => "RELTYPE_REGISTER",
			));
			$register->connect(array(
				"to" => $dir->id(),
				"reltype" => "RELTYPE_MENU",
			));
			$register->connect(array(
				"to" => $cfgform->id(),
				"reltype" => "RELTYPE_CFGFORM",
			));
			
			$register_search = obj();
			$register_search->set_parent($register->id());
			$register_search->set_class_id(CL_REGISTER_SEARCH);
			$register_search->set_status(STAT_ACTIVE);
			$register_search->set_name("Registri otsing ".$arr["obj_inst"]->id());
			$register_search->set_prop("per_page", 100);
			$register_search->set_prop("register", $register->id());
			$register_search->prop("show_all_in_empty_search", 1);
			$register_search->prop("show_date", 1);
			$register_search->save();
			$register_search->connect(array(
				"to" => $register->id(),
				"reltype" => "RELTYPE_REGISTER",
			));
			$register->set_prop("search_o", $register_search->id());
			$register->save();
			$register->connect(array(
				"to" => $register_search->id(),
				"reltype" => "RELTYPE_SEARCH",
			));
			
			$object_export = obj();
			$object_export->set_class_id(CL_OBJECT_EXPORT);
			$object_export->set_parent($arr["obj_inst"]->id());
			$object_export->set_status(STAT_ACTIVE);
			$object_export->set_name("Objekti eksport ".$arr["obj_inst"]->id());
			$object_export->set_prop("object_type", $object_type->id());
			$object_export->set_prop("root_folder", $dir->id());
			$object_export->set_prop("csv_separator", ",");
			$object_export->save();
			$arr["obj_inst"]->connect(array(
				"to" => $object_export->id(),
				"reltype" => "RELTYPE_OBJECT_EXPORT",
			));
			$object_export->connect(array(
				"to" => $dir->id(),
				"reltype" => "RELTYPE_FOLDER",
			));
			$object_export->connect(array(
				"to" => $object_type->id(),
				"reltype" => "RELTYPE_OBJECT_TYPE",
			));
		}
		$set_controllers = array(
			array(
				"name" => "Määra saatja aadressiks",
				"formula" => 'aw_global_set("global_name", $prop["value"]);',
				"errmgs" => "send_from_mail",
			),
			array(
				"name" => "*elemendinimi* peab olema täidetud",
				"formula" => 'if($prop["value"] == ""){$retval = PROP_ERROR;}',
				"errmsg" => "%caption peab olema täidetud",
			),
			array(
				"name" => "*elemendinimi* peab olema valitud",
				"formula" => 'if(empty($prop["value"])){$retval = PROP_ERROR;}',
				"errmsg" => "%caption peab olema valitud",
			),
			array(
				"name" => "Kontrolli e-maili õigsust",
				"formula" => 'if(!is_email($prop["value"])){$retval = PROP_ERROR;}',
				"errmsg" => "%caption sisestatud e-mailiaadress pole korrektne",
			),
			array(
				"name" => "Kuva sisestaja IP ja host aadress",
				"formula" => 'if(empty($prop["value"])){$request[$prop["name"]] = "IP: ".$_SERVER["REMOTE_ADDR"];}',
			),
			//Host: ".$_SERVER["REMOTE_HOST"]."\n
		);
		$get_controllers = array(
			array(
				"name" => "Kuva sisestaja IP ja host aadress",
				"formula" => '$value = $arr["obj_inst"]->prop($prop["name"]);if(!empty($value)){$prop["type"] = "text";$prop["value"] = nl2br($value);}',
			),
		);
		$i = 0;
		foreach($set_controllers as $key => $val)
		{
			$controller = obj();
			$controller->set_class_id(CL_CFGCONTROLLER);
			$controller->set_parent($arr["obj_inst"]->id());
			$controller->set_name($val["name"]);
			$controller->set_prop("formula", $val["formula"]);
			$controller->set_prop("errmsg", $val["errmsg"]);
			$controller->save();
			$arr["obj_inst"]->connect(array(
				"to" => $controller->id(),
				"reltype" => "RELTYPE_CONTROLLER",
			));
			$i++;
		}
		$i = 0;
		foreach($get_controllers as $key => $val)
		{
			$controller = obj();
			$controller->set_class_id(CL_CFG_VIEW_CONTROLLER);
			$controller->set_parent($arr["obj_inst"]->id());
			$controller->set_name($val["name"]);
			$controller->set_prop("formula", $val["formula"]);
			$controller->save();
			$arr["obj_inst"]->connect(array(
				"to" => $controller->id(),
				"reltype" => "RELTYPE_VIEWCONTROLLER",
			));
			$i++;
		}
	}
	
	function entries_toolbar($arr)
	{
		$register = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_REGISTER");
		$object_export = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OBJECT_EXPORT");
		$register_search = $register->prop("search_o");
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "search",
			"tooltip" => t("Otsi"),
			"url" => html::get_change_url($arr["obj_inst"]->id(), array(
				"group" => "search",
			)),
			"img" => "search.gif",
		));
		$tb->add_button(array(
			"name" => "change",
			"tooltip" => t("Muuda otsingu seadeid"),
			"url" => html::get_change_url($register->prop("search_o"), array(
				"return_url" => urlencode(html::get_change_url($arr["obj_inst"]->id(), array(
					"group" => $arr["request"]["group"],
				))),
			)),
			"img" => "../blue/obj_settings.gif",
		));
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta valitud sisestused"),
			"action" => "remove_entries",
			"img" => "delete.gif",
			"confirm" => t("Oled kindel, et tahad valitud sisestused kustutada?"),
		));
		$tb->add_button(array(
			"name" => "export",
			"tooltip" => t("Ekspordi objektid"),
			"url" => html::get_change_url($object_export->id(), array(
				"group" => "mktbl",
				"return_url" => urlencode(html::get_change_url($arr["obj_inst"]->id(), array(
					"group" => $arr["request"]["group"],
				))),
			)),
			"img" => "ftype_xls.gif",
		));
	}
	
	function callback_props($arr)
	{
		$prplist = safe_array($this->cfgform_i->prplist);
		$this->read_template("avail_props.tpl");
		$prop_order = array(
			"textbox" => 0,
			"textarea" => 1,
			"text" => 2,
			"classificator" => 3,
			"hidden" => 4,
			"date_select" => 5,
			"submit" => 6,
			"reset" => 7,
		);
		$def_props = array();
		if($this->p_clid == CL_CALENDAR_REGISTRATION_FORM)
		{
			$def_props = $this->def_props;
		}
		$prp_count = array();
		$c_props = $def_props + $this->no_props;
		foreach($prplist as $key => $prop)
		{
			if(!array_key_exists($key, $c_props))
			{
				$prp_count[$prop["type"]]++;
			}
		}
		$show_props = array();
		$ext_count = array();
		// these props won't go to heaven
		foreach($this->cfgform_i->all_props as $key => $prop)
		{
			if(!array_key_exists($prop["type"], $prop_order))
			{
				continue;
			}
			if(!in_array($prop_order[$prop["type"]], $show_props) && !array_key_exists($key, $c_props))
			{
				$show_props[$prop_order[$prop["type"]]] = $prop;
			}
			if(!array_key_exists($key, $c_props))
			{
				$ext_count[$prop["type"]]++;
			}
		}
		$sc = "";
		$vrs = array();
		$cnt = 0;
		foreach($def_props as $key => $prop)
		{
			if(!array_key_exists($key, $prplist))
			{
				$this->vars(array(
					"prp_name" => $prop,
					"prp_key" => $key,
				));
				$sc .= $this->parse("def_prop");
				$cnt++;
			}
		}
		$vrs["def_prop"] = $sc; 
		if($cnt > 0)
		{
			$vrs["d_prp"] = $this->parse("d_prp");
		}
		if(!empty($show_props))
		{
			$vrs["av_props"] = $this->parse("av_props");
		}
		$sc = "";
		ksort($show_props);
		foreach($show_props as $prop)
		{
			$this->vars(array(
				"prp_name" => $this->trans_names[$prop["type"]],
				"prp_type" => $prop["type"],
				"prp_used" => (int)$prp_count[$prop["type"]],
				"prp_unused" => ((int)$ext_count[$prop["type"]] - (int)$prp_count[$prop["type"]]),
			));
			$sc .= $this->parse("avail_property");
		}
		$this->vars(array(
			"avail_property" => $sc,
		) + $vrs);
		$this->vars(array(
			"avail" => $this->parse("avail"),
		));
		$item["value"] = $this->parse();
		return array($item);
	}

	function add_new_properties($arr)
	{
		$target = $arr["request"]["target"];
		// first check, whether a group with that id exists
		$_tgt = $this->cfgform->meta("cfg_groups");
		if (isset($_tgt[$target]))
		{
			$this->cfgform_i->_init_cfgform_data($this->cfgform);
			// and now I just have to modify the proplist, eh?
			$prplist = $this->cfgform_i->prplist;
			$prp_count = array();
			$prplist = safe_array($this->cfgform_i->prplist);
			$highest = 0;
			foreach($prplist as $key => $prop)
			{
				$prp_count[$prop["type"]]++;
				if($prop["ord"] > $highest)
				{
					$highest = $prop["ord"];
				}
			}
			$ext_count = array();
			foreach($this->cfgform_i->all_props as $key => $prop)
			{
				$ext_count[$prop["type"]]++;
			}
			$mark = $arr["request"]["mark"];
			foreach(safe_array($mark) as $pkey => $pval)
			{
				if(array_key_exists($pkey, $this->cfgform_i->all_props))
				{
					$prplist[$pkey] = array(
						"name" => $pkey,
						"ord" => $highest++,
						"caption" => $this->def_props[$pkey],
						"group" => $target,
						"type" => $this->cfgform_i->all_props[$pkey]["type"],
					);
					continue;
				}
				$count = (int)$ext_count[$pkey] - (int)$prp_count[$pkey];
				$pval = (int)$pval;
				if($count > 0 && !empty($pval))
				{
					// now, lets count the real ammount of thing we'll add
					$pval = $pval > $count ? $count : $pval;
					for($c = $pval; $c > 0; $c--)
					{
						foreach($this->cfgform_i->all_props as $key => $val)
						{
							if($c <= 0)
							{
								break;
							}
							if(in_array($key, $this->no_props))
							{
								continue;
							}
							if(!array_key_exists($key, $prplist) && $val["type"] == $pkey)
							{
								$prplist[$key] = array(
									"name" => $key,
									"ord" => $highest++,
									//"caption" => $this->all_props[$pkey]["caption"],
									"group" => $target,
									"type" => $val["type"],
								);
								$c--;
							}
						}
					}
				}
			}
			$this->cfgform_i->cfg_proplist = $prplist;
		}
	}

	function callback_form($arr)
	{
		$this->read_template("layout.tpl");
		$used_props = $by_group = array();

		if (is_array($this->cfgform_i->grplist))
		{
			foreach($this->cfgform_i->grplist as $key => $val)
			{
				// we should not have numeric group id-s
				// actually it's more about a few ghosts I had lying 
				// around, and this will get rid of them but we
				// really don't NEED numeric group id-s
				// /me does the jedi mind trick - duke
				if (!is_numeric($key))
				{
					$by_group[$key] = array();
				};
			};
		};

		if (is_array($this->cfgform_i->prplist))
		{
			foreach($this->cfgform_i->prplist as $property)
			{
				if (!empty($property["group"]))
				{
					if (!is_array($property["group"]))
					{
						$by_group[$property["group"]][] = $property;
					}
					else
					{
						list(,$first) = each($property["group"]);
						$by_group[$first][] = $property;
					}
				}
			}
		}
		$c = "";
		$cnt = 0;
		$capt_opts = array(
			0 => "Vasakul",
			"right" => "Paremal",
			"top" => "Peal",
			"bottom" => "All",
			//"in" => "Sees",
		);
		$prp_types = array(
			"" => "-- vali --",
			"mselect" => "Mitmerealine rippmenüü",
			"select" => "Rippmenüü",
			"checkboxes" => "Märkeruut",
			"radiobuttons" => "Raadionupp",
		);
		$object_type = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE");
		$metamgr = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_METAMGR");
		$clf_type = $object_type->meta("clf_type");
		$classificator = $object_type->meta("classificator");
		$prp_orient = array(0 => "horisontaalne", "vertical" => "vertikaalne");
		foreach($by_group as $key => $proplist)
		{
			$this->vars(array(
				"grp_caption" => $this->cfgform_i->grplist[$key]["caption"]." ($key)",
				"grpid" => $key,
			));
			$sc = "";
			foreach($proplist as $property)
			{
				$cnt++;
				$prpdata = $this->cfgform_i->all_props[$property["name"]];
				if (!$prpdata)
				{
					continue;
				};
				$used_props[$property["name"]] = 1;
				$this->vars(array(
					"bgcolor" => $cnt % 2 ? "#EEEEEE" : "#FFFFFF",
					"prp_caption" => $property["caption"],
					"prp_type" => $this->trans_names[$prpdata["type"]],
					"prp_key" => $prpdata["name"],
					"prp_order" => $property["ord"],
					"capt_ord" => html::select(array(
						"name" => "prp_opts[".$prpdata["name"]."][wf_capt_ord]",
						"style" => "border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC;",
						"options" => $capt_opts,
						"selected" => $property["wf_capt_ord"],
					)),
				));
				if($prpdata["type"] == "classificator")
				{
					$metas = new object_list(array(
						"parent" => $classificator[$prpdata["name"]],
						"class_id" => CL_META,
						"status" => STAT_ACTIVE,
						"sort_by" => "objects.name DESC",
					));
					$prp_metas = implode("; ", $metas->names());
					$this->vars(array(
						"clf_type" => html::select(array(
							"name" => "clf_type[".$prpdata["name"]."]",
							"options" => $prp_types,
							"selected" => $clf_type[$prpdata["name"]],
						)),
						"metamgr_link" => html::get_change_url($metamgr->id(), array(
							"group" => "manager",
							"meta" => $classificator[$prpdata["name"]],
						)),
						"predefs" => $prp_metas,
					));
					if(in_array($clf_type[$prpdata["name"]], $this->n_props))
					{
						$this->vars(array(
							"v_order" => html::select(array(
								"name" => "prp_opts[".$prpdata["name"]."][orient]",
								"options" => $prp_orient,
								"selected" => $property["orient"],
							)),
						));
						$this->vars(array(
							"ordering" => $this->parse("ordering"),
						));
					}
					else
					{
						$this->vars(array(
							"v_order" => "",
							"ordering" => "",
						));
					}
					$this->vars(array(
						"clf1" => $this->parse("clf1"),
					));
				}
				else
				{
					$this->vars(array(
						"clf_type" => "",
						"prp_metas" => "",
						"metamgr_link" => "",
						"clf1" => "",
						"predefs" => "",
					));
				}
				$sc .= $this->parse("property");
			}
			$this->vars(array(
				"property" => $sc,
			));
			$c .= $this->parse("group");
		};

		$this->vars(array(
			"group" => $c,
		));

		$item = $arr["prop"];
		$item["value"] = $this->parse();
		return array($item);
	}
	
	function get_rel_props($arr)
	{
		$prop = $arr["prop"];
		$vars = array(
			"get_controllers" => array(
				"rel" => "VIEWCONTROLLER",
				"obj" => "CFG_VIEW_CONTROLLER",
			),
			"set_controllers" => array(
				"rel" => "CONTROLLER",
				"obj" => "CFGCONTROLLER",
			),
			"style" => array(
				"rel" => "STYLE",
				"obj" => "CSS",
			),
		);
		$rels = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_".$vars[$prop]["rel"],
		));
		$this->all_rels = array();
		$folder_id = $arr["obj_inst"]->prop($prop."_folder");
		if(!empty($folder_id))
		{
			$objs = new object_list(array(
				"parent" => $folder_id,
				"class_id" => constant("CL_".$vars[$prop]["obj"]),
			));
			$this->all_rels = $this->all_rels + $objs->names();
		}
		foreach($rels as $rel)
		{
			$this->all_rels[$rel->prop("to")] = $rel->prop("to.name");
		}
		asort($this->all_rels);
		if($prop == "style")
		{
			classload("layout/active_page_data");
			foreach($this->all_rels as $key => $val)
			{
				active_page_data::add_site_css_style($key);
			}
		}
	}
	
	function callback_styles($arr)
	{
		$sel_styles = safe_array($arr["obj_inst"]->meta("styles"));
		$m_styles = safe_array($arr["obj_inst"]->meta("m_styles"));
		$this->get_rel_props(array(
			"obj_inst" => $arr["obj_inst"],
			"prop" => "style",
		));
		$this->all_rels = array(0 => "-- Vali --") + $this->all_rels;
		$props = array();
		$props["error"] = array(
			"name" => "m_style[error]",
			"caption" => "Veateate stiil",
			"type" => "select",
			"options" => $this->all_rels,
			"selected" => $m_styles["error"],
		);
		foreach($this->cfgform_i->prplist as $key => $val)
		{
			$props[$key."_capt"] = array(
				"name" => "style[$key][caption]",
				"caption" => $val["caption"]." pealkirja stiil",
				"type" => "select",
				"options" => $this->all_rels,
				"selected" => $sel_styles[$key]["caption"],
			);
			$props[$key] = array(
				"name" => "style[$key][prop]",
				"caption" => $val["caption"]." elemendi stiil",
				"type" => "select",
				"options" => $this->all_rels,
				"selected" => $sel_styles[$key]["prop"],
			);
		}
		return $props;
		//foreach($this->cfg_proplist
	}
	
	function callback_preview($arr)
	{
		return array("prop1" => array(
			"name" => "prop1",
			"type" => "text",
			"no_caption" => 1,
			"value" => $this->show(array(
				"id" => $arr["obj_inst"]->id(),
				"group" => $arr["request"]["group"],
			)),
		));
	}

	function callback_view_controllers($arr)
	{
		$controllers = $this->cfgform->meta("view_controllers");
		$retval = array();
		$this->get_rel_props(array(
			"obj_inst" => $arr["obj_inst"],
			"prop" => "get_controllers",
		));
		foreach ($this->cfgform_i->prplist as $prop)
		{
			$retval[] = array(
				"name" => "view_controllers[".$prop["name"]."]",
				"caption" => $prop["caption"],
				"type" => "select",
				"multiple" => 1,
				"size" => 3,
				"value" => $controllers[$prop["name"]],
				"options" => $this->all_rels,
			);
		}
		return  $retval;
	}
	
	function callback_submit_controllers($arr)
	{
		$controllers = $this->cfgform->meta("controllers");
		$retval = array();
		$this->get_rel_props(array(
			"obj_inst" => $arr["obj_inst"],
			"prop" => "set_controllers",
		));
		foreach ($this->cfgform_i->prplist as $prop)
		{
			$retval[] = array(
				"name" => "controllers[".$prop["name"]."]",
				"caption" => $prop["caption"],
				"type" => "select",
				"multiple" => 1,
				"size" => 3,
				"value" => $controllers[$prop["name"]],
				"options" => $this->all_rels,
			);
		}
		return  $retval;
	}
	
	function parse_alias($arr)
	{
		$id = $arr["alias"]["target"];
		if(is_oid($id) && $this->can("view", $id))
		{
			return $this->show(array("id" => $id));
		}
	}

	function show($arr)
	{
		$this->read_template("show_form.tpl");
		$obj_inst = obj($arr["id"]);
		$ftype = $obj_inst->prop("form_type");
		$this->get_rel_props(array(
			"obj_inst" => $obj_inst,
			"prop" => "style",
		));
		$object_type = $obj_inst->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE");
		$errors = aw_global_get("wf_errors");
		$values = aw_global_get("wf_data");
		
		if(strpos(strtolower($_SERVER["REQUEST_URI"]), "/automatweb") !== false)
		{
			$section = html::get_change_url($arr["id"], array(
				"group" => $arr["group"],
			));
		}
		else
		{
			$section = aw_ini_get("baseurl")."/".aw_global_get("section");
		}
		$vrs = array();
		if($ftype == CL_CALENDAR_REGISTRATION_FORM)
		{
			if(!empty($arr["ef"]))
			{
				$form_conf = obj($arr["ef"]);
			}
			else
			{
				$form_conf = $obj_inst->get_first_obj_by_reltype("RELTYPE_CAL_REG_FORM_CONF");
			}
			if(is_object($form_conf))
			{
				$event = $form_conf->get_first_obj_by_reltype("RELTYPE_EVENT");
				
				$form_conf_i = $form_conf->instance();
				
				$form_conf_i->read_template("show.tpl");
		
				$form_conf_i->_insert_event_inf($event, $form_conf);
		
				if ($form_conf->prop("max_pers") && $form_conf_i->get_count_for_event($event) > $form_conf->prop("max_pers"))
				{
					return $form_conf_i->parse();
				}
				$ef_id = $form_conf->id();
				if($form_conf->prop("show_content") == 1)
				{
					$this->_insert_event_inf($event, $form_conf);
				}
			}
		}
		$rval = $this->draw_cfgform_from_ot(array(
			"ot" => $object_type->id(),
			"reforb" => array(
				"class" => $ftype != CL_CALENDAR_REGISTRATION_FORM ? "webform" : "calendar_registration_form_conf",
				"return_url" => $section,
				"id" => $ftype != CL_CALENDAR_REGISTRATION_FORM ? $arr["id"] : $ef_id,
			),
			"errors" => $errors,
			"values" => $values,
			"obj_inst" => $obj_inst,
			"action" => $ftype != CL_CALENDAR_REGISTRATION_FORM ? "save_form_data" : "submit_register",
		));
		aw_session_del("wf_errors");
		aw_session_del("wf_data");
		return $rval;
	}
	
	function draw_cfgform_from_ot($arr)
	{
		$object_type = obj($arr["ot"]);
		$clf_type = $object_type->meta("clf_type");
		$cfgform_i = get_instance(CL_CFGFORM);
		$els = $cfgform_i->get_props_from_ot($arr);
		$cfgform = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CFGFORM");
		$sel_styles = safe_array($arr["obj_inst"]->meta("styles"));
		$errs = safe_array($arr["errors"]);
		$all_props = safe_array($cfgform->meta("cfg_proplist"));
		$ret = array();
		$no_sbt = true;
		foreach($els as $pn => $pd)
		{
			if($pd["type"] == "submit")
			{
				$no_sbt = false;
			}
			if (isset($errs[$pn]))
			{
				$ret[$pn."_err"] = array(
					"name" => $pn."_err",
					"type" => "text",
					"store" => "no",
					"value" => '<font color="red" class="'.$sel_styles["error"].'">'.$errs[$pn]["msg"].'</font>',
					"no_caption" => 1,
				);
			}
			$ret[$pn] = $pd;
		}
		$els = $ret;
		// special case n shit
		if($no_sbt)
		{
			$els["submit"] = array(
				"name" => "submit",
				"type" => "submit",
				"caption" => "Saada",
			);
		}
		$ftype = $arr["obj_inst"]->prop("form_type");
		$inst = empty($ftype) ? CL_REGISTER_DATA : $ftype;
		$rd = get_instance($inst);
		$els = $rd->parse_properties(array(
			"properties" => $els,
		));
		
		$def_caption_style = $arr["obj_inst"]->prop("def_caption_style");
		$def_prop_style = $arr["obj_inst"]->prop("def_prop_style");
		if(!empty($def_caption_style) or !empty($def_prop_style))
		{
			foreach($els as $key => $val)
			{
				if(!empty($def_caption_style))
				{
					$els[$key]["style"]["caption"] = $def_caption_style;
				}
				if(!empty($def_prop_style))
				{
					$els[$key]["style"]["prop"] = $def_prop_style;
				}
			}
		}
		$tmp = $els;
		foreach($tmp as $key => $val)
		{
			// some goddamn thing messes up the element captions, reorder them
			//$els[$key]["caption"] = $all_props[$key]["caption"];
			$els[$key]["capt_ord"] = $all_props[$key]["wf_capt_ord"];
			
			// treat all text properties as an ordinary text property
			if($all_props[$key]["type"] == "text" && empty($all_props[$key]["wf_capt_ord"]))
			{
				$els[$key]["subtitle"] = 1;
			}
			else
			{
				unset($els[$key]["subtitle"]);
			}
			if(is_array($sel_styles[$key]))
			{
				if(!empty($sel_styles[$key]["caption"]))
				{
					$els[$key]["style"]["caption"] = $sel_styles[$key]["caption"];
				}
				if(!empty($sel_styles[$key]["prop"]))
				{
					$els[$key]["style"]["prop"] = $sel_styles[$key]["prop"];
				}
			}
			if($val["type"] == "hidden")
			{
				$arr["reforb"][$key] = "";
			}
			if(in_array($clf_type[$key], $this->n_props))
			{
				$els[$key]["orient"] = $all_props[$key]["orient"];
			}
			if($all_props[$key]["type"] == "reset" || $all_props[$key]["type"] == "submit")
			{
				$els[$key]["class"] = $els[$key]["style"]["prop"];
			}
			if($val["type"] == "select")
			{
				foreach(safe_array($val["options"]) as $k => $v)
				{
					if(is_oid($k) && $this->can("view", $k))
					{
						$obj = obj($k);
						$value = $obj->comment();
						if($value == 1)
						{
							$els[$key]["selected"][$k] = $k;
							if($val["multiple"] != 1)
							{
								break;
							}
						}
					}
				}
			}
			if($val["type"] == "chooser")
			{
				foreach(safe_array($val["options"]) as $k => $v)
				{
					if(is_oid($k) && $this->can("view", $k))
					{
						$obj = obj($k);
						$value = $obj->comment();
						if($value == 1)
						{
							if($val["multiple"] == 1)
							{
								$els[$key]["value"][$k] = 1;
							}
							else
							{
								$els[$key]["value"] = $k;
								break;
							}
						}
					}
				}
			}
		}
		classload("cfg/htmlclient");
		$htmlc = new htmlclient(array(
			"template" => "real_webform.tpl",
			"styles" => safe_array($arr["obj_inst"]->meta("m_styles")),
		));
		$htmlc->start_output();
		foreach($els as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1,
		));
		$this->vars(array(
			"faction" => $arr["action"],
			"form" => $html,
			"webform_form" => "st".$arr["obj_inst"]->prop("def_form_style"),
			"reforb" => $this->mk_reforb($arr["action"], $arr["reforb"]),
		));
		return $this->parse();
	}
	
	/**

		@attrib name=save_form_data nologin=1 all_args=1
		
		@param id required type=int acl=view
		@param return_url optional
	**/
	function save_form_data($arr)
	{
		$obj_inst = obj($arr["id"]);
		$redirect = $obj_inst->prop("redirect");
		$rval = (strpos(strtolower($redirect), "http://") !== false ? $redirect : (substr($redirect, 0, 1) == "/" ?  aw_ini_get("baseurl").$redirect : aw_ini_get("baseurl")."/".$redirect));
		if(!$object_type = $obj_inst->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE"))
		{
			return $rval;
		}
		if(!$cfgform = $obj_inst->get_first_obj_by_reltype("RELTYPE_CFGFORM"))
		{
			return $rval;
		}
		
		$prplist = safe_array($cfgform->meta("cfg_proplist"));
		$is_valid = $this->validate_data(array(
			"cfgform_id" => $cfgform->id(),
			"request" => &$arr,
		));
		if(!empty($is_valid))
		{
			aw_session_set("no_cache", 1);
			aw_session_set("wf_errors", $is_valid);
			aw_session_set("wf_data", $arr);
			return $arr["return_url"];
		}
		else
		{
			$register = $obj_inst->get_first_obj_by_reltype("RELTYPE_REGISTER");
			$o = obj();
			$o->set_class_id(CL_REGISTER_DATA);
			$o->set_parent($register->prop("data_rootmenu"));
			$o->set_status(STAT_ACTIVE);
			$o->set_meta("cfgform_id", $cfgform->id());
			$o->set_meta("object_type", $object_type->id());
			//$o->save();
			$cls = get_instance(CL_CLASSIFICATOR);
			$relprops = $this->get_properties_by_type(array(
				"clid" => CL_REGISTER_DATA,
				"type" => "classificator",
			));
			foreach($arr as $key => $val)
			{
				if(!array_key_exists($key, $prplist))
				{
					unset($arr[$key]);
					continue;
				}
				// goddamn conversion...
				if($prplist[$key]["type"] == "date_select")
				{
					$val = mktime(0, 0, 0, $val["month"], $val["day"], $val["year"]);
				}
				if($prplist[$key]["type"] == "classificator")
				{
					$cls->process_vcl_property(array(
						"obj_inst" => $o,
						"prop" => array(
							"name" => $key,
							"value" => $val,
							"reltype" => $relprops[$key]["reltype"],
							"store" => "connect",
						),
						"clid" => CL_REGISTER_DATA,
					));
				}
				$o->set_prop($key, $val);
			}
			$body = "";
			// this stuff we won't translate
			$no_trans = array("submit", "reset", "text");
			// lets translate this stuff to real things
			foreach($arr as $key => $val)
			{
				if($prplist[$key]["type"] == "date_select")
				{
					$arr[$key] = $val["day"].".".$val["month"].".".$val["year"];
				}
				if($prplist[$key]["type"] == "classificator")
				{
					list($choices,,) = $cls->get_choices(array(
						"clid" => CL_REGISTER_DATA,
						"name" => $key,
						"obj_inst" => $o,
					));
					$choices = $choices->names();
					$vals = array();
					$val = is_array($val) ? $val : array($val);
					foreach($val as $valx)
					{
						$vals[] = $choices[$valx];
					}
					$arr[$key] = implode(", ", $vals);
				}
				if(!in_array($prplist[$key]["type"], $no_trans))
				{
					$body .= $prplist[$key]["caption"].": ".$arr[$key]."\n";
				}
			}
			$name = "";
			foreach(safe_array($obj_inst->prop("obj_name")) as $key => $val)
			{
				$name .= " ".$arr[$key];
			}
			$o->set_name(trim($name));
			$o->set_prop("register_id", $register->id());
			$o->save();
			$emails = $obj_inst->connections_from(array(
				"type" => "RELTYPE_EMAIL",
			));
			$nm = aw_global_get("global_name");
			if(!empty($nm))
			{
				$prx = array(
					"froma" => $nm,
				);
			}
			else
			{
				$prx = array(
					"fromn" => $obj_inst->prop("def_name"),
					"froma" => $obj_inst->prop("def_mail"),
				);
			}
			$awm = get_instance("protocols/mail/aw_mail");
			foreach($emails as $eml)
			{
				$email = $eml->to();
				$awm->create_message(array(
					"subject" => $obj_inst->name(),
					"to" => $email->prop("mail"),
					"body" => $body,
				) + $prx);
				$awm->gen_mail();
			}
		}
		return $rval;
	}
	
	function _insert_event_inf($e, $o)
	{
		$start = $e->prop("start");
		$end = $e->prop("end");
		$this->vars(array(
			"ev_title" => $e->name(),
			"ev_start" => locale::get_lc_date($start, LC_DATE_FORMAT_LONG_FULLYEAR)." ".date("H:i",$end),
			"ev_end" => locale::get_lc_date($end, LC_DATE_FORMAT_LONG_FULLYEAR)." ".date("H:i",$end),
			"ev_content" => nl2br($e->prop("content"))
		));
		$ct = $this->parse("SHOW_CONTENT");
		$this->vars(array(
			"SHOW_CONTENT" => $ct
		));
	}

	/**  
		
		@attrib name=remove_entries	
		@param id required type=int acl="view"
		@param group optional
		@param select required

	**/
	function remove_entries($arr)
	{
		if(is_array($arr["select"]))
		{
			foreach($arr["select"] as $val)
			{
				if(is_oid($val) && $this->can("delete", $val))
				{
					$obj = obj($val);
					if($obj->class_id() == CL_REGISTER_DATA)
					{
						$obj->delete();
					}
				}
			}
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}
}
?>
