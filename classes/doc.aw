<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/doc.aw,v 2.16 2003/05/15 14:51:14 duke Exp $
// doc.aw - document class which uses cfgform based editing forms
// this will be integrated back into the documents class later on
/*

@default table=documents
@default group=general

@property title type=textbox size=60
@caption Pealkiri

@property subtitle type=textbox size=60
@caption Alapealkiri

@property author type=textbox size=60
@caption Autor

@property photos type=textbox size=60
@caption Fotode autor

@property keywords type=textbox size=60
@caption Võtmesõnad

@property names type=textbox size=60
@caption Nimed

@property lead type=textarea richtext=1 cols=60 rows=10
@caption Lead

@property content type=textarea richtext=1 cols=60 rows=30
@caption Sisu

@property moreinfo type=textarea richtext=1 cols=60 rows=5
@caption Lisainfo

@property is_forum type=checkbox ch_value=1
@caption Foorum

@property showlead type=checkbox ch_value=1
@caption Näita leadi

@property show_modified type=checkbox ch_value=1
@caption Näita muutmise kuupäeva

//---------------
@property no_right_pane type=checkbox ch_value=1
@caption Ilma parema paanita

@property no_left_pane type=checkbox ch_value=1
@caption Ilma vasaku paanita

@property title_clickable type=checkbox ch_value=1
@caption Pealkiri klikitav

@property clear_styles type=checkbox ch_value=1 store=no
@caption Tühista stiilid

@property link_keywords type=checkbox ch_value=1 store=no
@caption Lingi võtmesõnad

@property esilehel type=checkbox ch_value=1
@caption Esilehel

@property frontpage_left type=checkbox ch_value=1
@caption Esilehel tulbas

@property dcache
@caption Cache otsingu jaoks

@property show_title type=checkbox ch_value=1
@caption Näita pealkirja

@property no_search type=checkbox ch_value=1
@caption Jäta otsingust välja

@property cite type=textarea cols=60 rows=10
@caption Tsitaat

@property tm type=textbox size=20
@caption Kuupäev

@property referer type=textbox size=50 table=objects field=meta method=serialize
@caption Ref

@property refopt type=select table=objects store=no
@caption Ref tüüp

@property sections type=select multiple=1 size=20 group=vennastamine store=no
@caption Sektsioonid

@property aliasmgr type=aliasmgr store=no editonly=1
@caption Aliastehaldur

@property start type=date_select table=planner group=calendar
@caption Algab

@property link_calendars type=callback store=no callback=callback_gen_link_calendars group=calendar
@caption Vali kalendrid, millesse see sündmus veel salvestatakse.

@property calendar_relation type=select field=meta method=serialize group=general table=objects
@caption Põhikalender

@property sbt type=submit value=Salvesta store=no 

@groupinfo calendar caption=Kalender

@tableinfo documents index=docid master_table=objects master_index=oid
@tableinfo planner index=id master_table=objects master_index=oid

@classinfo toolbar=yes
@classinfo corefields=status

*/

class doc extends class_base
{
	function doc($args = array())
	{
		$this->init(array(
			"clid" => CL_DOCUMENT,
		));
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "sections":
				$d = get_instance("document");
				list($selected,$options) = $this->get_brothers(array(
					"id" => $args["obj"]["oid"],
				));
				$data["options"] = array("" => "") + $options;
				$data["selected"] = $selected;
				break;

			case "refopt":
				$data["options"] = array("Ignoreeri","Näita","Ära näita");
				break;

			case "calendar_relation":
				$data["options"] = $this->calendar_list;
				break;

		};
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "sections":
				$this->update_brothers(array(
					"id" => $args["obj"]["oid"],
					"sections" => $args["form_data"]["sections"],
				));
				break;
			
			case "cal_event":
				$this->create_event(array(
					"id" => $args["obj"]["oid"],
					"form_data" => $args["form_data"],
				));
				$retval = PROP_IGNORE;
				break;

			case "link_calendars":
				$this->update_link_calendars($args);
				break;

			case "calendar_relation":
				// I need to create a brother here
				// to $data["value"];
				// I need to figure out to which calendar that relation object belongs to
				if (!empty($data["value"]))
				{
					$q = "SELECT aliases2.target AS target FROM aliases
						LEFT JOIN aliases AS aliases2 ON (aliases.target = aliases2.relobj_id)
						WHERE aliases.relobj_id = $data[value]";
					$target_relation = $this->db_fetch_field($q,"target");

					// now I have to figure out the event folder for that planner
					$pl = $this->get_object(array(
						"oid" => $target_relation,
						"clid" => CL_PLANNER,
					));

					$fldr = $pl["meta"]["event_folder"];

					if (is_numeric($fldr))
					{
						// do not create duplicates
						$b = $args["obj"]["oid"];
						$q = sprintf("SELECT oid FROM objects
								WHERE parent = %d AND brother_of = $b
								AND status != 0 AND class_id IN (%d,%d)",
								$fldr,CL_DOCUMENT,CL_BROTHER_DOCUMENT);
						$xrow = $this->db_fetch_row($q);
						if (empty($xrow))
						{
							$this->new_object(array(
								"parent" => $fldr,
								"class_id" => CL_BROTHER_DOCUMENT,
								"status" => STAT_ACTIVE,
								"brother_of" => $args["obj"]["oid"],
							));
						};
					}
				}
				else
				{
					// nuke all brothers
					$q = sprintf("UPDATE objects SET status = 0 WHERE brother_of = %d AND class_id = %d",
							$args["obj"]["oid"],CL_BROTHER_DOCUMENT);
					$this->db_query($q);
				}
				break;

			case "clear_styles":
				if (isset($args["form_data"]["clear_styles"]))
				{
					$this->clear_styles = true;
				};	
				break;

		};
		return $retval;
	}

	function callback_pre_save($args = array())
	{
		// map title to name
		$coredata = &$args["coredata"];
		$objdata = &$args["objdata"]["documents"];
		if ($objdata["title"])
		{
			$coredata["name"] = $objdata["title"];
		};
		if ($this->clear_styles)
		{
			$objdata["content"] = $this->_doc_strip_tags($objdata["content"]);
			$objdata["lead"] = $this->_doc_strip_tags($objdata["lead"]);
			$objdata["moreinfo"] = $this->_doc_strip_tags($objdata["moreinfo"]);
		};
	}

	function _doc_strip_tags($arg)
	{
		$arg = strip_tags($arg,"<b>,<i>,<u>,<br>,<p><ul><li><ol>");
		$arg = str_replace("<p>","",$arg);
		$arg = str_replace("<P>","",$arg);
		$arg = str_replace("</p>","",$arg);
		$arg = str_replace("</P>","",$arg);
		return $arg;
	}

	function callback_get_toolbar($args = array())
	{
		$toolbar = &$args["toolbar"];
		$toolbar->add_button(array(
                        "name" => "save",
                        "tooltip" => "Salvesta",
                        "url" => "javascript:document.changeform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));
		/*
		$toolbar->add_button(array(
                        "name" => "edit",
                        "tooltip" => "Muuda",
                        "url" => $this->mk_my_orb("change",array("id" => $args["id"])),
                        "imgover" => "edit_over.gif",
                        "img" => "edit.gif",
                ));
		$toolbar->add_button(array(
                        "name" => "brothering",
                        "tooltip" => "Vennastamine",
                        "url" => $this->mk_my_orb("change",array("id" => $args["id"],"group" => "vennastamine")),
			# wtf is brothering supposed to mean?
                        "imgover" => "brothering_over.gif",
                        "img" => "brothering.gif",
                ));

		$toolbar->add_button(array(
                        "name" => "lists",
                        "tooltip" => "Teavita liste",
                        "url" => $this->mk_my_orb("notify",array("id" => $args["id"]),"keywords"),
			"target" => "_blank",
                        "imgover" => "lists_over.gif",
                        "img" => "lists.gif",
                ));
		*/

		if (!empty($args["id"]))
		{
			$toolbar->add_button(array(
				"name" => "preview",
				"tooltip" => "Eelvaade",
				"target" => "_blank",
				"url" => aw_global_get("baseurl") . "/" . $args["id"],
				"imgover" => "preview_over.gif",
				"img" => "preview.gif",
			));
		};
	}

	function show($args = array())
	{
		extract($args);
		$d = get_instance("document");
		return $d->gen_preview(array("docid" => $args["id"]));
	}

	// creates a list of brothers for a document
	function _get_brother_documents($docid)
	{
		if (!is_numeric($docid))
		{
			return false;
		}
		$retval = array();
		$this->db_query("SELECT oid,parent FROM objects WHERE brother_of = $docid AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($arow = $this->db_next())
		{
			$retval[$arow["parent"]] = $arow;
		}
		return $retval;
	}

	function get_brothers($args = array())
	{
		extract($args);
		$sar = array();
		$this->db_query("SELECT parent FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($arow = $this->db_next())
		{
			$sar[$arow["parent"]] = $arow["parent"];
		}

		return array($sar,$this->get_menu_list(true));
	}

	function update_brothers($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);

		$sar = array(); $oidar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($row = $this->db_next())
		{
			$sar[$row["parent"]] = $row["parent"];
			$oidar[$row["parent"]] = $row["oid"];
		}

		$not_changed = array();
		$added = array();
		if (is_array($sections))
		{
			reset($sections);
			$a = array();
			while (list(,$v) = each($sections))
			{
				if ($sar[$v])
				{
					$not_changed[$v] = $v;
				}
				else
				{
					$added[$v] = $v;
				}
				$a[$v]=$v;
			}
		}
		$deleted = array();
		reset($sar);
		while (list($oid,) = each($sar))
		{
			if (!$a[$oid])
			{
				$deleted[$oid] = $oid;
			}
		}

		reset($deleted);
		while (list($oid,) = each($deleted))
		{
			$this->delete_object($oidar[$oid]);
		}
		reset($added);
		while(list($oid,) = each($added))
		{
			if ($oid != $id)	// no recursing , please
			{
				$noid = $this->new_object(array("parent" => $oid,"class_id" => CL_BROTHER_DOCUMENT,"status" => $obj["status"],"brother_of" => $id,"name" => $obj["name"],"comment" => $obj["comment"],"period" => $obj["period"]));
			}
		}
	}

	function get_doc_add_menu($parent, $period)
	{
		$cfgforms = $this->get_cfgform_list();
		$retval = array();
		$retval["doc_default"] = array(
			"caption" => "Dokument",
			"link" => $this->mk_my_orb("new",array("parent" => $parent,"period" => $period),"document"),
		);

		foreach($cfgforms as $key => $val)
		{
			$retval["doc_$key"] = array(
				"caption" => $val,
				"link" => $this->mk_my_orb("new",array("parent" => $parent,"period" => $period,"cfgform" => $key),"doc"),
			);
		}
		$retval["doc_brother"] = array(
			"caption" => "Dokument (vend)",
			"link" => $this->mk_my_orb("new",array("parent" => $parent,"period" => $period),"document_brother"),
		);
		return $retval;
	}

	////
	// !Shows the pic1 element. Well, I think I could use a generic solution for displaying different
	// values
	function show_pic1($args = array())
	{
		$retval = "";
		if (isset($args["id"]))
		{
			$obj = $this->get_object(array(
				"oid" => $args["id"],
				"class_id" => CL_DOCUMENT,
			));

			$q = sprintf("SELECT target FROM aliases WHERE source = %d AND type = %d AND pri = 1",
					$args["id"],CL_IMAGE);
		
			$tgt = $this->db_fetch_field($q,"target");


			if (!empty($tgt))
			{
				$awi = get_instance("image");
				$picdata = $awi->get_image_by_id($tgt);
				$retval = html::img(array(
					"url" => $picdata["url"],
					"border" => 0,
				));
			};
		};
		return $retval;
	}

	////
	function get_planners_with_folders($args = array())
	{
		$retval = array();
		$this->get_objects_by_class(array(
			"class" => CL_PLANNER,
			"active" => true,
			"fields" => "oid,name,metadata",
		));
		while($row = $this->db_next())
		{
			$row["meta"] = aw_unserialize($row["metadata"]);
			// aight, this is where I could use $obj->get_property_value("xxx");
			// but since I don't have it yet, this will do -- duke

			// display only the calendars which have an event folder defined
			if (!empty($row["meta"]["event_folder"]))
			{
				$retval[] = array(
					"oid" => $row["oid"],
					"name" => $row["name"],
					"event_folder" => $row["meta"]["event_folder"],
				);
			};
		};		
		return $retval;
	}

	////
	// !Generates the contents of cal1 property	
	function callback_gen_link_calendars($args = array())
	{
		$retval = array();
		$bs = $this->_get_brother_documents($args["obj"]["oid"]);
		$retval["caption"] = array(
			"caption" => $args["prop"]["caption"],
			"group" => $args["prop"]["group"],
		);

		foreach($this->get_planners_with_folders() as $row)
		{
			if (($row["class_id"] == CL_DOCUMENT) && $row["event_folder"] == $args["obj"]["parent"])
			{
				continue;
			};
			$folderdat = $this->get_object($row["event_folder"]);
			$retval["cal_" . $row["oid"]] = array(
				"type" => "checkbox",
				"name" => $args["prop"]["name"] . "[]",
				"caption" => $row["name"],
				"ch_value" => $row["oid"],
				"value" => isset($bs[$row["event_folder"]]) ? $row["oid"] : 0,
				"group" => $args["prop"]["group"],
			);
		}
		return $retval;
	}	

	function update_link_calendars($args = array())
	{
		// first, get rid of all brothers
		$event_id = $args["obj"]["oid"];

		$ev_doc = $this->get_object($event_id);
		
		$bs = $this->_get_brother_documents($event_id);
		$to_delete = $bs;

		if (is_array($args["prop"]["value"]))
		{
			foreach($this->get_planners_with_folders() as $row)
			{
				// kui vend on olemas, aga sellist eventfolderit pole, siis peab ta kustutama
				if (in_array($row["oid"],$args["prop"]["value"]))
				{
					if (empty($bs[$row["event_folder"]]))
					{
						$this->new_object(array(
							"parent" => $row["event_folder"],
							"class_id" => CL_BROTHER_DOCUMENT,
							"name" => $ev_doc["name"],
							"status" => STAT_ACTIVE,
							"brother_of" => $event_id,
						));
					};
					unset($to_delete[$row["event_folder"]]);
				};
			};
		};

		if (sizeof($to_delete) > 0)
		{
			$q = sprintf("DELETE FROM objects WHERE
					brother_of = '$event_id' AND
					parent IN (%s) AND 
					class_id = %d",join(",",array_keys($to_delete)),CL_BROTHER_DOCUMENT);
			$this->db_query($q);
		};

	}

	////
	// !Blergh, I really hate to integrate all that stuff into here
	// and .. I think I should subclass that shit anyway
	function set_calendars($args = array())
	{
		$cal_list = join(",",$args);
		// first I have to check whether this calendar has been told to
		// get it's calendar relations from somewhere else
		$q = "SELECT target FROM aliases
			LEFT JOIN objects ON (aliases.target = objects.oid)
			WHERE source IN ($cal_list) AND reltype = 5";
		///
		$other = $this->db_fetch_row($q);
		if (isset($other["target"]))
		{
			$cal_list = $other["target"];
		};
		$q = "SELECT source,target,relobj_id,objects.name FROM aliases
			LEFT JOIN objects ON (aliases.target = objects.oid)
			WHERE source IN ($cal_list) AND reltype = 4";
		$this->db_query($q);
		$this->calendar_list = array("" => "");
		while($row = $this->db_next())
		{
			$this->calendar_list[$row["relobj_id"]] = parse_obj_name($row["name"]);
		};

	}

	////
	// !Retrieves some information from the "show" template
	// parent - id of the menu from which to start the template search
	// template_dir - root template dir
	// inst - reference to an object that has loaded the required template (optional)
	// hm, maybe this should be a separate class? one which handles all
	// that document template class
	function parse_long_template($args = array())
	{
		// now, I want to gather some information about the "show" template:
		extract($args);
		if (!is_object($inst))
		{
			$_long = $this->get_long_template($parent);
			$inst = get_instance("aw_template");
			$base2 = substr($template_dir,strlen($this->cfg["tpldir"]));
			$inst->init($base2);
			$inst->read_any_template($_long);
		};

		// now I want get the list of "plugins"
		$tpls = array_keys($inst->v2_templates);
		$sx = "MAIN.plugin";
		$plugins = array();
		foreach($tpls as $key)
		{
		    if (preg_match("/MAIN\.plugin\.(\w*)/",$key,$matches))
		    {
			$plugins[] = $matches[1];
		    };
		};
		return $plugins;
	}


};
?>
