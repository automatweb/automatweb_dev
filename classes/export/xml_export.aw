<?php
// $Header: /home/cvs/automatweb_dev/classes/export/xml_export.aw,v 1.2 2005/07/13 11:00:19 kristo Exp $
// xml_export.aw - XML eksport 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_RECURRENCE, activate_next_auto_export)

@classinfo syslog_type=ST_XML_EXPORT relationmgr=yes

@default table=objects
@default group=general

@property do_export type=checkbox_value=1 store=no
@caption Teosta eksport

@property last_export type=text store=no
@caption Viimane eksport
@comment Viimase expordi aeg
--------------------------------------------------------------------
@groupinfo config caption="Seaded"
@default group=config

@property last_object_change_time type=datetime_select field=meta method=serialize
@caption Esimese objekti muutmise kuup&auml;ev
@comment &Auml;ra ekspordi objekte, mis on viimati muudetud varem kui (Kuup&auml;ev)

@property last_changed_obj_count type=textbox size=5 field=meta method=serialize
@caption Mitu viimati muudeetud objekti exportida

--------------------------------------------------------------------
@groupinfo locations caption="Asukohad"
@default group=locations

@property local_file type=checkbox ch_value=1 field=meta method=serialize
@caption Fail salvestatakse kohalikku serverisse

@property local_file_location type=textbox field=meta method=serialize
@caption Serveri kataloog
@comment Serveri kataloog, kuhu XML fail salvestatakse (koos failinimega)

@property ftp_file type=checkbox ch_value=1 field=meta method=serialize
@caption Fail salvestatakse FTP-kataloogi

@property ftp_host type=textbox field=meta method=serialize
@caption FTP aadress
@comment FTP serveri aadress

@property ftp_user type=textbox field=meta method=serialize
@caption FTP kasutaja
@comment Kasutajanimi, millega FTP serverisse logitakse

@property ftp_password type=password field=meta method=serialize
@caption FTP parool
@comment Parool FTP kasutajale

@property ftp_file_location type=textbox field=meta method=serialize
@caption FTP kataloog
@comment Kataloog, kuhu üle FTP fail salvestatakse (koos failinimega)

@property parents_table type=table store=no 
@caption Kaustad, mille alt objekte v&otilde;etakse

--------------------------------------------------------------------
@groupinfo xml_struct caption="XML struktuur"
@default group=xml_struct

@property xml_struct_table type=table store=no no_caption=1
@caption Seadete vormi v&auml;ljad

--------------------------------------------------------------------
@reltype EXP_OBJECT_TYPE value=1 clid=CL_OBJECT_TYPE
@caption Eksporditav objekt

@reltype EXP_RECURRENCE value=2 clid=CL_RECURRENCE
@caption Faili genereerimise kordus

@reltype PARENT value=3 clid=CL_MENU
@caption Kaust

*/

class xml_export extends class_base
{
	function xml_export()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "export/xml_export",
			"clid" => CL_XML_EXPORT
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
			case "do_export":
				$prop['value'] = html::href(array(
					"caption" => t("Teosta eksport"),
					"url" => $this->mk_my_orb("fetch", array(
						"id" => $arr['obj_inst']->id(),
						"called_from" => "local",
					)),
					"title" => t("Ekspordi AW objektid"),
				));

				break;
			case "last_export":
				$last_export = $arr['obj_inst']->meta("last_export");
				$prop['value'] = (empty($last_export)) ? "0" : date("d-M-y / H:i", $last_export);

				break;
			case "parents_table":
				$this->create_parents_table($arr);
				break;
			case "xml_struct_table":
				$this->create_xml_struct_table($arr);
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
			case "parents_table":
				if (!empty($arr['request']['parents']))
				{
					$arr['obj_inst']->set_meta("parents", $arr['request']['parents']);
				}
				break;
			case "xml_struct_table":
//					arr($arr);
				if (!empty($arr['request']['xml_data']))
				{
					foreach ($arr['request']['xml_data'] as $key => $value)
					{
					/*
					// if we want to make it that way, that those xml struct fields will not be 
					// saved which aren't marked to be exported in xml
						if (empty($value['export_this_field']))
						{
							unset($arr['request']['xml_data'][$key]);
							continue;
						}
					*/
						foreach ($value['xml_tag_content'] as $k => $v)
						{
							if (empty($v['value']))
							{
								unset($arr['request']['xml_data'][$key]['xml_tag_content'][$k]);
							}
						}
						foreach ($value['xml_tag_param'] as $k => $v)
						{
							if (empty($v['param_value']))
							{
								unset($arr['request']['xml_data'][$key]['xml_tag_param'][$k]);
							}
						}
					}
					$arr['obj_inst']->set_meta("xml_data", $arr['request']['xml_data']);
				}
				break;
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
	
	function create_parents_table($arr)
	{
		$o = $arr['obj_inst'];
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);
		$t->define_field(array(
			"name" => "parent",
			"caption" => t("Kaust"),
		));
		$t->define_field(array(
			"name" => "include_sub",
			"caption" => t("Kaasaarvatud alamkaustad"),
		));

		$conns_to_parents = $o->connections_from(array(
			"type" => RELTYPE_PARENT
		));
		$parents = new aw_array($o->meta("parents"));
		foreach($conns_to_parents as $conn_to_parent)
		{
			$parent_id = $conn_to_parent->prop("to");
			$t->define_data(array(
				"parent" => $conn_to_parent->prop("to.name"),
				"include_sub" => html::checkbox(array(
					"name" => "parents[".$parent_id."]",
					"checked" => (array_key_exists($parent_id, $parents->get())) ? true : false,
				)),
						
			));
		}
	}

	function create_xml_struct_table($arr)
	{
		$o = $arr['obj_inst'];
		
		$t = &$arr['prop']['vcl_inst'];
//		$t->set_sortable(false);
		$t->set_default_sortby("ord");
		$t->define_field(array(
			"name" => "export_this_field",
			"caption" => t("Vali"),
		));
		$t->define_field(array(
			"name" => "cfgform_field",
			"caption" => t("Seadete vormi v&auml;li"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "ord",
			"caption" => t("jrk"),
			"sortable" => 1,
			"align" => "center",
			"callback" => array(&$this, "callback_order"),
			"callb_pass_row" => 1,
		));
		$t->define_field(array(
			"name" => "xml_tag_name",
			"caption" => t("XML v&auml;lja nimetus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "xml_tag_content",
			"caption" => t("XML v&auml;lja sisu"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "xml_tag_param",
			"caption" => t("Parameetrid"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "cdata",
			"caption" => t("CDATA"),
			"align" => "center",
		));
		
		// getting all properties from cfgform, which is connected via object type 
		$all_properties = $this->get_all_props(array(
			"object" => $o,
		));
		// creating an array for select options from properties array, and i need objects
		// table fields also there, so where the hell can i get those?

		// well, i won't get them, so i have to manually handle those fields, which are needed
		// hmm, seems $obj->properties() fn. returns objects table fields 
		// also it seems that some objects table fields are accessible via prop() fn.
		// uh, what a mess :S
		// !!
		// OK - i'll add some objects table fields manually and hope that they can get their value
		// via prop() fn.
		// !!

		$options_arr = array("" => "");
		foreach($all_properties as $prop_data)
		{
			$options_arr[$prop_data['name']] = $prop_data['caption'];
		}
		// ah, ok, lets add those fields to $options_arr which are objects table fields and can
		// be accessible via ->prop() fn.
		$options_arr['parent'] = "parent";
		$options_arr['modified'] = "modified";
		$options_arr['class_id'] = "class_id";
//		$options_arr['modifiedby'] = "modifiedby";
//		$options_arr['createdby'] = "createdby";
//		$options_arr['jrk'] = "jrk";

		
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
/*
		$object_properties = $o->properties();
		foreach($o->properties() as $property_name => $property_value)
		{
			$option_arr[$property_name] = $property_name;
		}

		$object_type_obj = $o->get_first_obj_by_reltype("RELTYPE_EXP_OBJECT_TYPE");
		$object_type_subclass_id = $object_type_obj->subclass();
		$object_type_subclass_obj = new object($object_type_subclass_id);

		foreach ($object_type_subclass_obj->properties() as $property_name => $property_value)
		{
			$options_arr[$property_name] = $property_name;
		}
*/
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


		// here goes those objects table fields

		$saved_xml_data = $o->meta("xml_data");

		// put data into table:
		foreach ($all_properties as $prop_data)
		{
			// have to put together some strings here for table
			$xml_tag_content_str = "";
			$xml_tag_content_max_id = 0;
			$xml_tag_param_str = "";
			$xml_tag_param_max_id = 0;
			
			// data which should be appear as value of XML tag
			foreach($saved_xml_data[$prop_data['name']]['xml_tag_content'] as $value)
			{
				$xml_tag_content_str .= html::textbox(array(
					"name" => "xml_data[".$prop_data['name']."][xml_tag_content][".$xml_tag_content_max_id."][sep_before]",
					"size" => 5,
					"value" => $value['sep_before'],
				)).html::select(array(
					"name" => "xml_data[".$prop_data['name']."][xml_tag_content][".$xml_tag_content_max_id."][value]",
					"options" => $options_arr,
					"selected" => $value['value'],
				)).html::textbox(array(
					"name" => "xml_data[".$prop_data['name']."][xml_tag_content][".$xml_tag_content_max_id."][sep_after]",
					"size" => 5,
					"value" => $value['sep_after']
				))."<br />";
				$xml_tag_content_max_id++;
			}

			$xml_tag_content_str .= html::textbox(array(
				"name" => "xml_data[".$prop_data['name']."][xml_tag_content][".$xml_tag_content_max_id."][sep_before]",
				"size" => 5,
			)).html::select(array(
				"name" => "xml_data[".$prop_data['name']."][xml_tag_content][".$xml_tag_content_max_id."][value]",
				"options" => $options_arr,
				"selected" => (empty($saved_xml_data[$prop_data['name']]['xml_tag_content'])) ? $prop_data['name'] : "",
			)).html::textbox(array(
				"name" => "xml_data[".$prop_data['name']."][xml_tag_content][".$xml_tag_content_max_id."][sep_after]",
				"size" => 5,
			));
		
	
			// XML tag parameters
			foreach ($saved_xml_data[$prop_data['name']]['xml_tag_param'] as $value)
			{
				$xml_tag_param_str .= html::textbox(array(
					"name" => "xml_data[".$prop_data['name']."][xml_tag_param][".$xml_tag_param_max_id."][param_name]",
					"size" => 10,
					"value" => $value['param_name'],
				)).html::select(array(
					"name" => "xml_data[".$prop_data['name']."][xml_tag_param][".$xml_tag_param_max_id."][param_value]",
					"options" => $options_arr,
					"selected" => $value['param_value'],
				))."<br />";
				$xml_tag_param_max_id++;
			}

			$xml_tag_param_str .= html::textbox(array(
				"name" => "xml_data[".$prop_data['name']."][xml_tag_param][".$xml_tag_param_max_id."][param_name]",
				"size" => 10,
			)).html::select(array(
				"name" => "xml_data[".$prop_data['name']."][xml_tag_param][".$xml_tag_param_max_id."][param_value]",
				"options" => $options_arr,
			));


			// putting the data into table
			$t->define_data(array(
				"export_this_field" => html::checkbox(array(
					"name" => "xml_data[".$prop_data['name']."][export_this_field]",
					"checked" => isset($saved_xml_data[$prop_data['name']]['export_this_field']) ? true : false,
				)),
				"cfgform_field" => $prop_data['caption'],
				// [begin] - these are for ord textbox
				"ord_name" => "xml_data[".$prop_data['name']."][ord]",
				"ord_size" => 5,
				// [end] - these are for ord textbox
				"ord" => $saved_xml_data[$prop_data['name']]['ord'],
				"xml_tag_name" => html::textbox(array(
					"name" => "xml_data[".$prop_data['name']."][name]",
					"size" => 20,
					"value" => (empty($saved_xml_data[$prop_data['name']]['name'])) ? $prop_data['name'] : $saved_xml_data[$prop_data['name']]['name'],
//					"value" => (empty($saved_xml_data[$prop_data['name']]['name'])) ? "" : $saved_xml_data[$prop_data['name']]['name'],

				)),
				"xml_tag_content" => $xml_tag_content_str,
				"xml_tag_param" => $xml_tag_param_str,
				"cdata" => html::checkbox(array(
					"name" => "xml_data[".$prop_data['name']."][cdata]",
					"checked" => isset($saved_xml_data[$prop_data['name']]['cdata']) ? true : false,
				)),
			));
		}
	}
	function callback_order($arr)
	{
		return html::textbox(array(
			"name" => $arr['ord_name'],
			"size" => $arr['ord_size'],
			"value" => $arr['ord'],
		));
	}

	/**
		@attrib name=fetch nologin=1 all_args=1
		@param id required type=int acl=view
		@param modified optional type=int 
		@param obj_count optional type=int
		@param called_from optional
	**/
	function fetch($arr)
	{
/*
		$this->put_file(array(
			"file" => "/www/dev/dragut/automatweb_dev/files/test_test.txt",
			"content" => "test\n",
		));
*/
		$o = obj($arr['id']);
		$conns_to_parents = $o->connections_from(array(
			"type" => RELTYPE_PARENT,
		));
		$parents = $o->meta("parents");

		// figure out which last_modified timestamp to use:
		$last_modified = 0; 
		if ($o->prop("last_object_change_time") > 0)
		{
			$last_modified = $o->prop("last_object_change_time");
		}

		if (!empty($arr['modified']))
		{
			$last_modified = $arr['modified'];
		}

		$xml_config_data = $o->meta("xml_data");
		$params_from_url = $arr;

		$props_from_cfgform = $this->get_all_props(array(
			"object" => $o,
		));

		$params = array();

		// looping through all the xml_config_data to put tugether an array which can be
		// added to object_list params later.
		foreach ($xml_config_data as $xml_config_data_key => $xml_config_data_value)
		{
			foreach ($xml_config_data_value['xml_tag_param'] as $key => $value)
			{
				// i have to check where the value of this param/property is saved
				// cause if this is meta field, then i can't filter by this value when creating 
				// object_list
				if (array_key_exists($value['param_name'], $params_from_url) && $props_from_cfgform[$value['param_name']]['method'] != "serialize")
				{
					$params[$value['param_name']] = $params_from_url[$value['param_name']];
				}
			}

			// if there is xml_name_present in the url, then we should try to add
			// this one to params too

			if (array_key_exists($xml_config_data_value['name'], $params_from_url) && $props_from_cfgform[$xml_config_data_value['name']]['method'] != "serialize")
			{
				$params[$xml_config_data_value['name']] = $params_from_url[$xml_config_data_value['name']];
			}

		}


		$object_type_obj = $o->get_first_obj_by_reltype("RELTYPE_EXP_OBJECT_TYPE");
		$object_type_subclass_id = $object_type_obj->subclass();

		// i collect only parent ids
		$parent_ids = array();
		foreach ($conns_to_parents as $conn_to_parent)
		{
			$parent_id = $conn_to_parent->prop("to");
			$parent_ids[$parent_id] = $parent_id;
			if ($parents[$parent_id] == 1)
			{
				$parent_obj = obj($parent_id);
				$ot = new object_tree(array(
					"class_id" => CL_MENU,
					"parent" => $parent_id,
				));
				$parent_ids = $parent_ids + $this->make_keys($ot->ids());
			}
		}


		// count of objects which will be exported
		$exp_objects_count = ""; 
		if ($o->prop("last_changed_obj_count") != "")
		{
			$exp_objects_count = $o->prop("last_changed_obj_count");
		}
		if (!empty($arr['obj_count']))
		{
			$exp_objects_count = $arr['obj_count'];
		}

		//  
		// i have to merge here those params too which are coming from url
		// params can be only those fields, which are not stored in meta data field

		$params = $params + array(
			"parent" => $parent_ids,
			"class_id" => $object_type_subclass_id,
			"modified" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $last_modified),
			"sort_by" => "objects.modified",
		);
		if (!empty($exp_objects_count))
		{
			$params['limit'] = $exp_objects_count;
		}

		$all_objects = new object_list($params);


//arr("<strong>".$all_objects->count()."</strong>");

		////
		// i think this is the place to generate actually the XML file
		//

//		$xml_file = "&lt;?xml version=\"1.0\" encoding=\"iso-8859-1\"?&gt;\n";
		$xml_file = "";
		$xml_file .= "<objects>\n";

		foreach ($all_objects->arr() as $oid => $obj)
		{
			$xml_file .= "<object>\n";
			foreach ($xml_config_data as $field_name => $field_config)
			{
				if (empty($field_config['name']) || empty($field_config['export_this_field']))
				{
					continue;
				}
				$xml_file .= "<".$field_config['name']." ";
				foreach ($field_config['xml_tag_param'] as $xml_tag_param_value)
				{
					$xml_file .= $xml_tag_param_value['param_name']."=\"".$obj->prop($xml_tag_param_value['param_value'])."\" ";
				}
				
				if (empty($field_config['xml_tag_content']))
				{
					$xml_file .= " />\n";
				}
				else
				{
					$xml_file .= ">";
					if ($field_config['cdata'] == 1)
					{
						$xml_file .= "<![CDATA[";
					}
					foreach ($field_config['xml_tag_content'] as $xml_tag_content_value)
					{
						$xml_file .= $xml_tag_content_value['sep_before'];
						$xml_file .= $obj->prop($xml_tag_content_value['value']);
						$xml_file .= $xml_tag_content_value['sep_after'];
					}
					if ($field_config['cdata'] == 1)
					{
						$xml_file .= "]]>";
					}
					$xml_file .= "</".$field_config['name'].">\n";
				}
			}
			$xml_file .= "</object>\n";

		} 

		$xml_file .= "</objects>";

//arr(htmlentities($xml_file));
		header("Content-type: text/xml; charset=ISO-8859-15");
		echo $xml_file;
		
		
		// i'll check first if the fn. is called from local obj (clicked the link) or by scheduler
		// in other cases it is possibly called by url and then i don't need to put the file in local
		// folder or in the ftp server folder
		if ($arr['called_from'] == "local" || $arr['called_from'] == "sched")
		{


			if ($o->prop("local_file"))
			{
				$this->put_file(array(
					"file" => $o->prop("local_file_location"),
					"content" => $xml_file,	
				));
			}

			if ($o->prop("ftp_file"))
			{
			
				$ftp_inst = get_instance("protocols/file/ftp");
				$ftp_inst->connect(array(
					"host" => $o->prop("ftp_host"),
					"user" => $o->prop("ftp_user"),
					"pass" => $o->prop("ftp_password"),
				));
				$ftp_inst->put_file($o->prop("ftp_file_location"), $xml_file);
				$ftp_inst->disconnect();
			}

			// if the export is called from scheduler, then try to activate next auto export
			if ($arr['called_from'] == "sched")
			{
				$this->activate_next_auto_export(array(
					"object" => $o,
				));
			}
		}

		$o->set_meta("last_export", time());

		aw_disable_acl();
		$o->save();
		aw_restore_acl();
//		return $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id());
		exit();
	}


	//// params:
	// object => current object instance
	//
	// this fn. checks if there is a recurrence object configured
	// if it is then put it in scheduler
	//
	// returns the timestamp of next export
	function activate_next_auto_export($arr)
	{
		// if i call this function via message, then i have only object id ($arr['oid'])
		if (isset($arr['object']))
		{
			$o = $arr['object'];
		}
		else
		{
			// seems i have only recurrence oid here, so a little damn scanning is needed
			$recur_o = new object($arr['oid']);

			$conns = $recur_o->connections_to();
			foreach($conns as $conn)
			{
				if($conn->prop("from.class_id") == CL_XML_EXPORT)
				{
					$o = $conn->from();
					break;
				}
			}
		}

		if (!$o)
		{
			// seems this is not related to xml export
			return;
		}

		if ($recurrence_obj = $o->get_first_obj_by_reltype("RELTYPE_EXP_RECURRENCE"))
		{
			$recurrence_inst = get_instance(CL_RECURRENCE);
			$next = $recurrence_inst->get_next_event(array(
				"id" => $recurrence_obj->id(),
			));
			if ($next)
			{
				// add to scheduler
				$sc = get_instance("scheduler");
				$sc->add(array(
					"event" => $this->mk_my_orb("fetch", array("id" => $o->id(), "called_from" => "sched")),
					"time" => $next,
				));
			}
		}	
		return $next;

	}
	
	function get_all_props($arr)
	{
		$object_type_obj = $arr['object']->get_first_obj_by_reltype("RELTYPE_EXP_OBJECT_TYPE");
		if (!empty($object_type_obj))
		{
			$object_type_inst = $object_type_obj->instance();
			$props = $object_type_inst->get_properties($object_type_obj);

			// unsetting those elements cause in some reason - they have only captions
			// and they mess up my property lists a bit
			unset($props['is_translated'], $props['needs_translation']);
		}
		return $props;
	}

}
?>
