<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/expp/expp_journal_management.aw,v 1.6 2005/10/11 23:58:57 dragut Exp $
// expp_journal_management.aw - V&auml;ljaannete haldus 
/*

@classinfo syslog_type=ST_EXPP_JOURNAL_MANAGEMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property code type=textbox field=meta method=serialize
	@caption Kood

@groupinfo organisation_general_information caption="Ettev&otilde;tte &uuml;ldandmed"
@default group=organisation_general_information

	@groupinfo general_info caption="&Uuml;ldandmed" parent=organisation_general_information
	@default group=general_info

		@property organisation type=releditor reltype=RELTYPE_ORGANISATION rel_id=first field=meta method=serialize props=name,contact,code,phone_id,url_id,email_id,telefax_id,logo
		@caption Organisatsioon

		

	@groupinfo design caption="Kujundus" parent=organisation_general_information
	@default group=design

		@property design_image type=releditor use_form=emb reltype=RELTYPE_DESIGN_IMAGE rel_id=first field=meta method=serialize
		@caption Pilt

		@property frame_color type=textbox field=meta method=serialize
		@caption Raami toon

		@property main_color type=textbox field=meta method=serialize
		@caption P&otilde;hitoon

@groupinfo publications caption="V&auml;ljaanded"
@default group=publications

	@groupinfo publications_general_info caption="V&auml;ljaannete &uuml;ldinfo" parent=publications
	@default group=publications_general_info

		@property publications_name type=text 
		@caption V&auml;ljaande nimi

		@property publications_description type=text
		@caption V&auml;ljaande kirjeldus

		@property order_composition_information type=textarea field=meta method=serialize
		@property Tellimuse vormistamise informatsioon

		@property cover_image type=releditor use_form=emb reltype=RELTYPE_COVER_IMAGE rel_id=first field=meta method=serialize
		@caption Esikaane pilt

	@groupinfo publications_list caption="V&auml;ljaanded" parent=publications
	@default group=publications_list

		@property publications_table type=releditor reltype=RELTYPE_PUBLICATION field=meta method=serialize mode=manager props=name
		@caption V&auml;ljaanded

	@groupinfo general_images caption="Pildid" parent=publications
	@default group=general_images

		@property general_images type=releditor reltype=RELTYPE_GENERAL_IMAGE field=meta method=serialize mode=manager props=name,ord,status,file,dimensions,comment,author,alt,link,file_show table_fields=name,ord table_edit_fields=ord
		$caption Pildid

	@groupinfo general_files caption="Failid" parent=publications
	@default group=general_files
	
	        @property general_files type=releditor reltype=RELTYPE_GENERAL_FILE field=meta method=serialize mode=manager props=file,ord,type,comment,file_url,newwindow,status table_fields=name,ord table_edit_fields=ord
        	@caption Failid

	@groupinfo general_links caption="Lingid" parent=publications
	@default group=general_links

		@property general_links type=releditor reltype=RELTYPE_GENERAL_LINK field=meta method=serialize mode=manager props=name,url,ord,docid,hits,alt,newwindowd table_fields=name,ord table_edit_fields=ord parent=self
		@caption Lingid

        @groupinfo general_documents caption="Dokumendid" parent=publications
        @default group=general_documents

                @property general_documents type=releditor reltype=RELTYPE_GENERAL_DOCUMENT field=meta method=serialize mode=manager props=title,author,,lead,content
                @caption Dokumendid

	@groupinfo general_polls caption="Kiirk&uuml;sitlused" parent=publications
	@default group=general_polls

		@property general_polls type=releditor reltype=RELTYPE_GENERAL_POLL field=meta method=serialize mode=manager props=name,question,answers,status
		@caption Kiirk&uuml;sitlused

	@groupinfo general_webforms caption="Veebivormid" parent=publications
	@default group=general_webforms

		property general_webform type=releditor reltype=RELTYPE_GENERAL_WEBFORM field=meta method=serialize mode=manager props=name,status
		caption Veebivorm

		@property general_webforms_toolbar type=toolbar no_caption=1
		@caption Veebivormide t&ouml;&ouml;riistariba

		@property general_webforms_table type=table no_caption=1
		@caption Veebivormid

	@groupinfo general_forum caption="Foorum" parent=publications
	@default group=general_forum

		@property general_forum type=text  
		@caption Foorum

@groupinfo stats caption="Statistika"
@default group=stats

	@property stats type=text
	@caption Statistika

@reltype ORGANISATION value=1 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype DESIGN_IMAGE value=2 clid=CL_IMAGE
@caption Kujunduse pilt

@reltype COVER_IMAGE value=3 clid=CL_IMAGE
@caption Esikaane pilt

@reltype CRM_SECTION value=4 clid=CL_CRM_SECTION
@caption &Uuml;ksus/Toode

@reltype PUBLICATION value=5 clid=CL_EXPP_PUBLICATION,CL_CRM_SECTION
@caption V&auml;ljaanne

@reltype PUBLICATION_IMAGE value=6 clid=CL_IMAGE
@caption V&auml;ljaande pilt

@reltype GENERAL_MINI_GALLERY value=7 clid=CL_MINI_GALLERY
@caption &Uuml;ldine minigalerii

@reltype GENERAL_FILE value=8 clid=CL_FILE
@caption &Uuml;ldine fail

@reltype GENERAL_LINK value=9 clid=CL_EXTLINK
@caption &Uuml;ldine link

@reltype GENERAL_IMAGE value=10 clid=CL_IMAGE
@caption &Uuml;ldine pilt 

@reltype GENERAL_FORUM value=11 clid=CL_FORUM_V2
@caption &Uuml;ldine foorum

@reltype GENERAL_WEBFORM value=12 clid=CL_WEBFORM
@caption &Uuml;ldine veebivorm

@reltype GENERAL_POLL value=13 clid=CL_POLL
@caption &Uuml;ldine kiirk&uuml;sitlus

@reltype GENERAL_DOCUMENT value=14 clid=CL_DOCUMENT
@caption &Uuml;ldine dokument

*/

class expp_journal_management extends class_base
{
	function expp_journal_management()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/clients/expp/expp_journal_management",
			"clid" => CL_EXPP_JOURNAL_MANAGEMENT
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
			case "stats":
				$prop['value'] = t("Siia tuleb statistika");
				break;
			case "publications_name":
			case "publications_description":
				$prop['value'] = t("V&auml;&auml;rtus tuleb Reggy-st, ei ole v&otilde;imalik muuta");
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
			case "organisation":
			case "design_image":
			case "cover_image":
			case "publications_table":
			case "general_images":
			case "general_files":
			case "general_links":
			case "general_documents":
			case "general_polls":
			case "general_webform":
				$prop['obj_parent'] = $arr['obj_inst']->id();
				break;	

		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

        function callback_post_save($arr)
        {
                $cache_inst = get_instance("cache");
                $cache_inst->file_invalidate($arr['obj_inst']->prop("code").".cache");
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

	function _get_general_webforms_toolbar($arr)
	{
		$t = &$arr['prop']['toolbar'];
		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Uus veebivorm"),
			"url" => $this->mk_my_orb("new", array(
				"alias_to" => $arr['obj_inst']->id(),
				"parent" => $arr['obj_inst']->id(),
				"reltype" => 12, // expp_journam_management.general_webform
				"return_url" => get_ru(),	
			), CL_WEBFORM),
		));

		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta"),
			"action" => "_delete_objects",
			"confirm" => t("Oled kindel, et soovid valitud veebivormid kustutada?"),
		));

		

		return PROP_OK;
	}

	function _get_general_webforms_table($arr)
	{

		$t = &$arr['prop']['vcl_inst'];
		$t->define_field(array(
			"name" => "activity",
			"caption" => t("Aktiivsus"),
			"align" => "center",
			"width" => "10%",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center",
			"width" => "10%",
		));
		$t->define_field(array(
			"name" => "select",
			"caption" => t("Vali"),
			"align" => "center",
			"width" => "5%",
		));
		$connections_to_webforms = $arr['obj_inst']->connections_from(array(
			"type" => "RELTYPE_GENERAL_WEBFORM",
			"sort_by_num" => "to.status",
			"sort_dir" => "asc",
		));
		foreach ($connections_to_webforms as $connection_to_webform)
		{
			$webform_id = $connection_to_webform->prop("to");
			$t->define_data(array(
				"activity" => html::radiobutton(array(
					"name" => "general_webform[activity]",
					"value" => $webform_id,
					"checked" => ($connection_to_webform->prop("to.status") == STAT_ACTIVE) ? true : false,
				)),
				"name" => $connection_to_webform->prop("to.name"),
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $webform_id,
						"return_url" => get_ru(),
						), CL_WEBFORM),
					"caption" => t("Muuda"),
				)),
				"select" => html::checkbox(array(
					"name" =>"general_webform[selected][".$webform_id."]",
					"value" => $webform_id,
				)),
			));
		}

		return PROP_OK;
	}

	function _set_general_webforms_table($arr)
	{
		$connections_to_webforms = $arr['obj_inst']->connections_from(array(
			"type" => "RELTYPE_GENERAL_WEBFORM",
		));

		foreach ($connections_to_webforms as $connection_to_webform)
		{
			$webform_id = $connection_to_webform->prop("to");
			if (is_oid($webform_id) && $this->can("edit", $webform_id))
			{
				$webform_object = new object($webform_id);
				if ($arr['request']['general_webform']['activity'] == $webform_id)
				{
					$webform_object->set_status(STAT_ACTIVE);
				}
				else
				{
					$webform_object->set_status(STAT_NOTACTIVE);
				}
				$webform_object->save();
			}
		}
		return PROP_OK;
	}

	function _get_general_forum($arr)
	{
		$forum_object = $arr['obj_inst']->get_first_obj_by_reltype("RELTYPE_GENERAL_FORUM");
		if (!empty($forum_object))
		{
			$forum_object_id = $forum_object->id();
		}
		if (is_oid($forum_object_id) && $this->can("view", $forum_object_id))
		{
			$arr['prop']['value'] = html::href(array(
				"url" => $this->mk_my_orb("change", array(
					"id" => $forum_object_id,
					"return_url" => get_ru(),
				), "forum_v2"),
				"caption" => t("Link foorumile"),
			));
		}
		else
		{
			$arr['prop']['value'] = html::href(array(
				"url" => $this->mk_my_orb("new", array(
					"alias_to" => $arr['obj_inst']->id(),
					"parent" => $arr['obj_inst']->id(),
					"reltype" => 11, // expp_journal_management.general_forum
					"return_url" => get_ru(),
				), CL_FORUM_V2),
				"caption" => t("Lisa foorum"),
			));
		}
		
		return PROP_OK;
	}
	/**
		@attrib name=_delete_objects
	**/
	function _delete_objects($arr)
	{

		foreach ($arr['general_webform']['selected'] as $webform_id)
		{
			if (is_oid($webform_id) && $this->can("delete", $webform_id))
			{
				$webform_object = new object($webform_id);
				$webform_object->delete();
			}
		}

		return $arr['post_ru'];
	}

}
?>
