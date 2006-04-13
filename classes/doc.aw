<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/doc.aw,v 2.124 2006/04/13 09:31:02 kristo Exp $
// doc.aw - document class which uses cfgform based editing forms
// this will be integrated back into the documents class later on
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_DOCUMENT, on_save_document)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_DOCUMENT, on_add_doc_rel)

@classinfo trans=1 no_comment=1 relationmgr=yes syslog_type=ST_DOCUMENT

@default table=documents
@default group=general

	@property navtoolbar type=toolbar no_caption=1 store=no trans=1
	@caption Toolbar

	@property plugins type=callback callback=callback_get_doc_plugins table=objects field=meta method=serialize trans=1
	@caption Pluginad

	@property title type=textbox size=60 trans=1
	@caption Pealkiri

	@property subtitle type=textbox size=60 trans=1
	@caption Alapealkiri

	@property alias type=textbox size=60 table=objects field=alias
	@caption Alias

	@property author type=textbox size=60 trans=1
	@caption Autor

	@property photos type=textbox size=60 trans=1
	@caption Fotode autor

	@property keywords type=textbox size=60 trans=1
	@caption V&otilde;tmes&otilde;nad

	@property names type=textbox size=60 trans=1
	@caption Nimed

	@property lead type=textarea richtext=1 cols=60 rows=10 trans=1
	@caption Lead

	@property content type=textarea richtext=1 cols=60 rows=30 trans=1
	@caption Sisu

	@property moreinfo type=textarea richtext=1 cols=60 rows=5 trans=1
	@caption Lisainfo

	@property link_text type=textbox size=60 trans=1
	@caption URL

	@property is_forum type=checkbox ch_value=1 trans=1
	@caption Foorum

	@property showlead type=checkbox ch_value=1 default=1 trans=1
	@caption N&auml;ita leadi

	@property show_modified type=checkbox ch_value=1 trans=1 default=1
	@caption N&auml;ita muutmise kuup&auml;eva

	@property doc_modified type=hidden table=documents field=modified trans=1
	@caption Dok. modified

	@property clear_styles type=checkbox ch_value=1 store=no trans=1
	@caption T&uuml;hista stiilid

	@property link_keywords type=checkbox ch_value=1 store=no trans=1
	@caption Lingi v&otilde;tmes&otilde;nad

	@property link_keywords2 type=checkbox ch_value=1 field=meta method=serialize table=objects default=1
	@caption V&otilde;tmes&otilde;nad lingina

	@property frontpage_left type=checkbox ch_value=1 trans=1
	@caption Esilehel tulbas

	@property dcache type=checkbox store=no trans=1
	@caption Cache otsingu jaoks

	@property dcache_content type=hidden field=dcache 
	@property rating type=hidden 
	@property num_ratings type=hidden 

	@property show_title type=checkbox ch_value=1 default=1 trans=1
	@caption N&auml;ita pealkirja

	@property no_search type=checkbox ch_value=1 trans=1
	@caption J&auml;ta otsingust v&auml;lja

	@property cite type=textarea cols=60 rows=10 trans=1
	@caption Tsitaat

	@property tm type=textbox size=20 trans=1
	@caption Kuup&auml;ev

	@property show_print type=checkbox ch_value=1 table=objects field=meta method=serialize default=1 trans=1
	@caption 'Prindi' nupp

	@property createdby table=objects field=createdby type=text trans=1
	@caption Kes tegi

	@property user1 table=documents type=textbox size=60 trans=1
	@caption Kasutaja defineeritud 1

	@property user2 table=documents type=textarea rows=2 cols=60 trans=1
	@caption Kasutaja defineeritud 2

	@property user3 table=documents type=textbox trans=1
	@caption Kasutaja defineeritud 3

	@property user4 table=documents type=textbox trans=1
	@caption Kasutaja defineeritud 4

	@property user5 table=documents type=textbox trans=1
	@caption Kasutaja defineeritud 5

	@property user6 table=documents type=textbox trans=1
	@caption Kasutaja defineeritud 6

	@property userta2 table=objects field=meta method=serialize type=textarea rows=10 cols=60 trans=1
	@caption Kasutaja defineeritud textarea 2

	@property userta3 table=objects field=meta method=serialize type=textarea rows=10 cols=60 trans=1
	@caption Kasutaja defineeritud textarea 3

	@property userta4 table=objects field=meta method=serialize type=textarea rows=10 cols=60 trans=1
	@caption Kasutaja defineeritud textarea 4

	@property userta5 table=objects field=meta method=serialize type=textarea rows=10 cols=60 trans=1
	@caption Kasutaja defineeritud textarea 5

	@property userta6 table=objects field=meta method=serialize type=textarea rows=10 cols=60 trans=1
	@caption Kasutaja defineeritud textarea 6

	@property ucheck1 type=checkbox ch_value=1 table=objects field=meta method=serialize 
	@caption Kasutaja defineeritud checkbox 1

	@property ucheck2 type=checkbox ch_value=1 table=documents field=ucheck2 
	@caption Kasutaja defineeritud checkbox 2

	@property ucheck3 type=checkbox ch_value=1 table=documents field=ucheck3 
	@caption Kasutaja defineeritud checkbox 3

	@property ucheck4 type=checkbox ch_value=1 table=documents field=ucheck4 
	@caption Kasutaja defineeritud checkbox 4

	@property ucheck5 type=checkbox ch_value=1 table=documents field=ucheck5 
	@caption Kasutaja defineeritud checkbox 5
	
	@property ucheck6 type=checkbox ch_value=1 table=documents field=ucheck6 
	@caption Kasutaja defineeritud checkbox 6

	@property uservar1 type=classificator field=aw_varuser1 reltype=RELTYPE_VARUSER1 store=connect
	@caption User-defined var 1

	@property uservar2 type=classificator field=aw_varuser2 reltype=RELTYPE_VARUSER2 store=connect
	@caption User-defined var 2

	@property uservar3 type=classificator field=aw_varuser3 reltype=RELTYPE_VARUSER3 store=connect
	@caption User-defined var 3

	@property language type=text type=text store=no trans=1
	@caption Keel

	@property calendar_relation type=select field=meta method=serialize table=objects trans=1
	@caption P&otilde;hikalender

	@property gen_static type=checkbox store=no trans=1
	@caption Genereeri staatiline

	@property sbt type=submit value=Salvesta store=no trans=1

	@property cb_part type=hidden value=1 group=general,settings store=no
	@caption cb_part

	@property nobreaks type=hidden table=documents

	@property no_topic_links type=checkbox table=objects field=meta method=serialize ch_value=1
	@caption &Auml;ra tee Samal teemal linke

	@property create_new_version type=checkbox ch_value=1 store=no
	@caption Loo uus versioon

	@property edit_version type=select store=no
	@caption Vali versioon, mida muuta

@default group=settings

	@property no_right_pane type=checkbox ch_value=1 trans=1
	@caption Ilma parema paanita

	@property no_left_pane type=checkbox ch_value=1 trans=1
	@caption Ilma vasaku paanita

	@property title_clickable type=checkbox ch_value=1 trans=1 default=1
	@caption Pealkiri klikitav

	@property esilehel type=checkbox ch_value=1 trans=1
	@caption Esilehel

	@property dcache_save type=checkbox ch_value=1 table=objects field=meta method=serialize trans=1
	@caption Cache otsingu jaoks (salvestub)

	@property no_last type=checkbox ch_value=1 trans=1
	@caption &Auml;ra arvesta muutmist

	@property show_last_changed type=checkbox ch_value=1 trans=1 table=objects field=meta method=serialize
	@caption Muutmise kuupaev dokumendi sees

	@property no_show_in_promo type=checkbox ch_value=1 trans=1 table=documents field=no_show_in_promo method=
	@caption &Auml;ra n&auml;ita konteineris

	@property show_in_iframe type=checkbox ch_value=1 table=objects field=meta method=serialize
	@caption Kasuta siseraami

	@property target_audience type=chooser  store=connect multiple=1 reltype=RELTYPE_TARGET_AUDIENCE table=documents field=aw_target_audience
	@caption Sihtr&uuml;hm

@default group=vennastamine

	@property sections type=select multiple=1 size=20 store=no trans=1
	@caption Sektsioonid

@default group=relationmgr

	@property aliasmgr type=aliasmgr store=no editonly=1 trans=1
	@caption Aliastehaldur

@default group=calendar

	@property start type=date_select table=planner trans=1
	@caption Algab (kp)

	@property start1 type=datetime_select field=start table=planner trans=1
	@caption Algab 

	@property duration type=time_select field=end table=planner trans=1
	@caption Kestab

@default group=kws

	@property kw_tb type=toolbar no_caption=1 store=no group=keywords

	@property kws type=keyword_selector store=no 
	@caption M&auml;rks&otilde;nad

@default group=versions

	@property versions_tb type=toolbar store=no no_caption=1
	@property versions type=table store=no no_caption=1

@groupinfo calendar caption=Kalender
@groupinfo vennastamine caption=Vennastamine
@groupinfo settings caption=Seadistused icon=archive.gif
@groupinfo kws caption="M&auml;rks&otilde;nad" 
@groupinfo versions caption="Versioonid" 
@groupinfo relationmgr caption=Seostehaldur submit=no

@tableinfo documents index=docid master_table=objects master_index=brother_of
@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype TIMING value=20 clid=CL_TIMING
@caption Aeg

@reltype REMINDER value=21 clid=CL_REMINDER
@caption Meeldetuletus

@reltype LANG_REL value=22 clid=CL_DOCUMENT
@caption Keeleseos


@reltype VARUSER1 value=23 clid=CL_META
@caption kasutajadefineeritud muutuja 1

@reltype VARUSER2 value=24 clid=CL_META
@caption kasutajadefineeritud muutuja 2

@reltype VARUSER3 value=25 clid=CL_META
@caption kasutajadefineeritud muutuja 3

@reltype TARGET_AUDIENCE value=26 clid=CL_TARGET_AUDIENCE
@caption Sihtr&uuml;hm

*/

define(RELTYPE_COMMENT,1);

class doc extends class_base
{
	function doc($args = array())
	{
		$this->init(array(
			"clid" => CL_DOCUMENT,
			"tpldir" => "automatweb/documents",
		));
	}

	function get_property($arr)
	{
		// let site mod props
		$si = __get_site_instance();
		if ($si)
		{
			$meth = "get_property_doc_".$arr["prop"]["name"];
			if (method_exists($si, $meth))
			{
				$si->$meth($arr);
			}
		}
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "target_audience":
				$ol = new object_list(array("class_id" => CL_TARGET_AUDIENCE, "lang_id" => array(), "site_id" => array()));
				$data["options"] = $ol->names();
				break;

			case "kw_tb":
				$this->kw_tb($arr);
				break;

			case "lead":
				$val = $data["value"];
				if ($data["richtext"] == 1)
				{
					/*
					$nlcount = substr_count($val,"\n");
					$brcount = substr_count($val,"<br>");
					if ($nlcount > 3 && $brcount == 0)
					{
						$data["value"] = nl2br($data["value"]);
					};
					*/
				}
				else
				{
					//$data["value"] = htmlspecialchars($data["value"]);
				};
				break;

			case "content":
				$data["value"] = htmlspecialchars($data["value"]);
				$val = $data["value"];
				if ($data["richtext"] == 1)
				{
					/*
					$nlcount = substr_count($val,"\n");
					$brcount = substr_count($val,"<br>");
					if ($nlcount > 3 && $brcount == 0)
					{
						$data["value"] = nl2br($data["value"]);
					};
					*/
				}
				else
				{
					//$data["value"] = htmlspecialchars($data["value"]);
				};
				break;

			case "name":
				$retval = PROP_IGNORE;
				break;

			case "tm":
				if ($arr["new"])
				{
					$format = aw_ini_get("document.date_format");
					if ($format == "n/a")
					{
						$format = "";
					}
					else
					if (empty($format))
					{
						$format = "d.m.Y";
					};
					$data["value"] = date($format);
				};
				break;

			case "sections":
				$d = get_instance(CL_DOCUMENT);
				list($selected,$options) = $this->get_brothers(array(
					"id" => $arr["obj_inst"]->id(),
				));
				$data["options"] = array("" => "") + $options;
				$data["selected"] = $selected;
				break;

			case "calendar_relation":
				$cl = new aw_array($this->calendar_list);
				$data["options"] = array(-1 => t("puudub")) + $cl->get();
				break;

			case "duration":
				$_tmp = $arr["data"]["planner"]["end"] - $arr["data"]["planner"]["start"];
				$data["value"] = array(
					"hour" => (int)($_tmp/3600),
					"minute" => ($_tmp % 3600) / 60,
				);
				break;
	
			case "navtoolbar":
				// I need a better way to do this!
				if (!empty($arr["request"]["cb_part"]))
				{
					$retval = PROP_IGNORE;	
				}
				else
				{
					$this->gen_navtoolbar($arr);
				};
				break;

			case "language":
				/*
				$objdata = $arr["obj_inst"];
				$lg = get_instance("languages");
				$lang_list = $lg->get_list();
				$lang_id = $lg->get_langid_for_code($objdata->lang());
				$data["value"] = $lang_list[$lang_id];
				*/
				break;

			case "edit_version":
				$data["options"] = $this->get_version_list($arr["obj_inst"]->id());
				$data["value"] = aw_url_change_var("edit_version", $arr["request"]["edit_version"]);
				$data["onchange"] = "window.location = this.options[this.selectedIndex].value;";
				break;

			case "versions":
				$this->_versions($arr);
				break;

			case "versions_tb":
				$this->_versions_tb($arr);
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
			case "create_new_version":
				
				break;

			case "sections":
				$this->update_brothers(array(
					"id" => $args["obj_inst"]->id(),
					"sections" => $args["request"]["sections"],
				));
				break;
			
			case "link_calendars":
				$this->update_link_calendars($args);
				break;

			case "link_keywords":
				if (is_oid($args["obj_inst"]->id()))
				{
					$kw = get_instance(CL_KEYWORD);
					if (isset($args["request"]["keywords"]))
					{
						$kw->update_keywords(array(
							"keywords" => $args["request"]["keywords"],
							"oid" => $args["obj_inst"]->id(),
						));
					}
					else
					{
						$kw->update_relations(array(
							"id" => $args["obj_inst"]->id(),
							"data" => $args["request"]["content"],
						));
						// also update keyword brother docs
						$kw->update_menu_keyword_bros(array("doc_ids" => array($args["obj_inst"]->id())));
					};
				};
				break;

			case "tm":
				$modified = time();
				list($_date, $_time) = explode(" ", $data["value"]);
				list($hour, $min) = explode(":", $_time);

				$try = explode("/",$_date);
				if (count($try) < 3)
				{
					$ts = 0;
				}
				else
				{
					list($day,$mon,$year) = explode("/",$_date);

					$ts = mktime($hour,$min,0,$mon,$day,$year);
				}

				if ($ts > (3600*24*400))
				{
					$modified = $ts;
				}
				else
				{
					// 2kki on punktidega eraldatud
					if ($_date == "")
					{
						$_date = $data["value"];
					}
					list($day,$mon,$year) = explode(".",$_date);
					$ts = mktime($hour,$min,0,$mon,$day,$year);
					if ($ts)
					{
						$modified = $ts;
					}	
					else
					{
						// 2kki on hoopis - 'ga eraldatud?
						list($day,$mon,$year) = explode("-",$_date);
						$ts = mktime($hour,$min,0,$mon,$day,$year);
						if ($ts)
						{
							$modified = $ts;
						}
					}
				}

				// we need this later too
				$this->_modified = $modified;
				break;

			case "dcache":
				if (aw_ini_get("document.use_dcache"))
				{
					//print "generating preview<br>";
					$dcx = get_instance(CL_DOCUMENT);
					$preview = $dcx->gen_preview(array("docid" => $args["obj_inst"]->id()));
					$this->quote($preview);
					$this->_preview = $preview;
				};
				break;

			case "gen_static":
				if (!empty($data["value"]) && is_oid($args["obj_inst"]->id()))
				{
					$dcx = get_instance(CL_DOCUMENT);
					// but this dies anyway
					$dcx->gen_static_doc($args["obj_inst"]->id());
				};
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
					$pl = new object($target_relation);

					$fldr = $pl->prop("event_folder");

					if (is_numeric($fldr))
					{
						// do not create duplicates
						$b = $args["obj_inst"]->id();
						$q = sprintf("SELECT oid FROM objects
								WHERE parent = %d AND brother_of = $b
								AND status != 0 AND class_id IN (%d,%d)",
								$fldr,CL_DOCUMENT,CL_BROTHER_DOCUMENT);
						$xrow = $this->db_fetch_row($q);
						if (empty($xrow))
						{
							$args["obj_inst"]->create_brother($fldr);
						};
					}
				}
				elseif ($data["value"] == -1)
				{
					// nuke all brothers
					$q = sprintf("UPDATE objects SET status = 0 WHERE brother_of = %d AND class_id = %d",
							$args["obj_inst"]->id(),CL_BROTHER_DOCUMENT);
					$this->db_query($q);
				}
				break;

			case "clear_styles":
				if (isset($args["request"]["clear_styles"]))
				{
					$this->clear_styles = true;
				};	
				break;

			case "duration":
				$_start = date_edit::get_timestamp($args["request"]["start1"]);
				$_end = $_start + (3600 * $data["value"]["hour"]) + (60 * $data["value"]["minute"]);
				$data["value"] = $_end;
				break;

			case "content":
				if ($args["request"]["content"]["cb_breaks"] == 0)
				{
					$args["obj_inst"]->set_prop("nobreaks",0);
				};
				break;

			case "versions":
				$this->_save_versions = true;
				$args["obj_inst"]->set_no_modify(true);
				break;
		};
		return $retval;
	}

	function callback_pre_save($args = array())
	{
		// map title to name
		$obj_inst = &$args["obj_inst"];
		$obj_inst->set_name($obj_inst->prop("title"));
		
		if (isset($this->_preview))
		{
			$obj_inst->set_meta("dcache",$this->_preview);
			$res = trim(preg_replace("/<.*>/imsU", " ",$this->_preview));
			$len = strlen($res);
			for($i = 0; $i < $len; $i++)
			{
				if (ord($res{$i}) < 32)
				{
					$res{$i} = " ";
				}
			}
			$obj_inst->set_prop("dcache_content", $res);
		};
		
		if (isset($this->_modified))
		{
			$obj_inst->set_prop("doc_modified",$this->_modified);
		};

		// RTE also has a button to clear styles
		if ($this->clear_styles)
		{
			$obj_inst->set_prop("content",$this->_doc_strip_tags($obj_inst->prop("content")));	
			$obj_inst->set_prop("lead",$this->_doc_strip_tags($obj_inst->prop("lead")));	
			$obj_inst->set_prop("moreinfo",$this->_doc_strip_tags($obj_inst->prop("moreinfo")));	
		};

		$old_tm = $obj_inst->prop("tm");
		if (empty($old_tm) && !empty($args["request"]["tm"]))
		{
			$obj_inst->set_prop("tm",date("d.m.y",$obj_inst->prop("modified")));
		};

		$modby = $obj_inst->modifiedby();
		if ($args["request"]["edit_version"])
		{
			$out = array();
			parse_str($args["request"]["edit_version"], $out);
			if ($out["edit_version"] != "")
			{
				$modby = $this->db_fetch_field("SELECT vers_crea_by FROM documents_versions WHERE docid = ".$obj_inst->id()." AND version_id = '".$out["edit_version"]."'", "vers_crea_by");
			}
		}

		if ($args["request"]["create_new_version"] == 1)
		{
			$obj_inst->set_create_new_version();
		}
		else
		if (aw_global_get("uid") != $modby && !$this->_save_versions && is_oid($obj_inst->id()))
		{
			// if the user is different, then create new version
			$obj_inst->set_create_new_version();
			$this->force_new_version = true;
		}
		else
		if ($args["request"]["edit_version"])
		{
			$out = array();
			parse_str($args["request"]["edit_version"], $out);
			if ($out["edit_version"] != "")
			{
				$obj_inst->set_save_version($out["edit_version"]);
			}
		}
	}

	function callback_post_save($args = array())
	{
		if ($args["obj_inst"]->prop("dcache_save") == 1)
		{
			$dcx = get_instance(CL_DOCUMENT);
			$preview = $dcx->gen_preview(array(
				"docid" => $args["obj_inst"]->id()
			));
			$this->quote($preview);
			
			$res = trim(preg_replace("/<.*>/imsU", " ",$preview));
			$len = strlen($res);
			for($i = 0; $i < $len; $i++)
			{
				if (ord($res{$i}) < 32)
				{
					$res{$i} = " ";
				}
			}
			$args["obj_inst"]->set_prop("dcache_content", $res);
			$args["obj_inst"]->save();
		}

		if ($this->_save_versions)
		{
			$this->_save_versions($args);
		}

		$this->flush_cache();
	}

	function _doc_strip_tags($arg)
	{
		$arg = strip_tags($arg,"<b>,<i>,<u>,<br />,<p><ul><li><ol>");
		$arg = str_replace("<p>","",$arg);
		$arg = str_replace("<p>","",$arg);
		$arg = str_replace("</p>","",$arg);
		$arg = str_replace("</p>","",$arg);
		return $arg;
	}

	function gen_navtoolbar($arr)
	{
		$toolbar = &$arr["prop"]["toolbar"];
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"url" => "javascript:submit_changeform();",
			"img" => "save.gif",
		));

	
		if (is_object($arr["obj_inst"]) && $arr["obj_inst"]->id())
		{
			$url = obj_link($arr["obj_inst"]->id());
			if ($arr["request"]["edit_version"] != "")
			{
				$url = aw_url_change_var("docversion", $arr["request"]["edit_version"], $url);
			}
			$toolbar->add_button(array(
				"name" => "preview",
				"tooltip" => t("Eelvaade"),
				"target" => "_blank",
				"url" => $url,
				"img" => "preview.gif",
			));

			$toolbar->add_separator();
		};
	}

	/**  
		
		@attrib name=show params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function show($args = array())
	{
		extract($args);
		$d = get_instance(CL_DOCUMENT);
		return $d->gen_preview(array("docid" => $args["id"]));
	}

	function callback_get_doc_plugins($args = array())
	{
		if (!is_object($args["obj_inst"]) || !is_oid($args["obj_inst"]->id()))
		{
			return false;
		};

		$plugins = $this->parse_long_template(array(
			"parent"=> $args["obj_inst"]->parent(),
			"template_dir" => $this->template_dir,
		));

		$plg_ldr = get_instance("plugins/plugin_loader");
		$plugindata = $plg_ldr->load_by_category(array(
			"category" => "document",
			"plugins" => $plugins,
			"method" => "get_property",
			"args" => $args["obj_inst"]->meta("plugins"),
		));

		return $plugindata;
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
		$this->db_query("SELECT parent FROM objects WHERE brother_of = '$id' AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($arow = $this->db_next())
		{
			$sar[$arow["parent"]] = $arow["parent"];
		}

		return array($sar,$this->get_menu_list(true));
	}

	function update_brothers($args = array())
	{
		extract($args);
		if (!$id)
		{
			return;
		}
		$obj = new object($id);

		$sar = array(); $oidar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = '$id' AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
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
			$tmp = obj($oidar[$oid]);
			$tmp->delete();
		}
		reset($added);
		while(list($oid,) = each($added))
		{
			if ($oid != $id)	// no recursing , please
			{
				$tmp = obj($id);
				$noid = $tmp->create_brother($oid);
			}
		}
	}

	function get_doc_add_menu($parent, $period)
	{
		$cfgforms = $this->get_cfgform_list();
		$retval = array();
		if (aw_ini_get("document.no_static_forms") == 0)
		{
			$tmp = aw_ini_get("classes");
			$retval["doc_default"] = array(
				"name" => $tmp[CL_DOCUMENT]["name"],
				"link" => $this->mk_my_orb("new",array("parent" => $parent,"period" => $period),"document"),
			);
		};

		// can't use empty on function 
		$def_cfgform = aw_ini_get("document.default_cfgform");
		if (empty($def_cfgform))
		{
			$retval["ng_doc"] = array(
				"name" => t("Dokument 2.0"),
				"link" => $this->mk_my_orb("new",array("parent" => $parent,"period" => $period),"doc"),
			);
		}

		foreach($cfgforms as $key => $val)
		{
			$retval["doc_$key"] = array(
				"name" => $val,
				"link" => $this->mk_my_orb("new",array("parent" => $parent,"period" => $period,"cfgform" => $key),"doc"),
			);
		}
		$retval["doc_brother"] = array(
			"name" => t("Dokument (vend)"),
			"link" => $this->mk_my_orb("new",array("parent" => $parent,"period" => $period),"document_brother"),
		);
		return $retval;
	}

	function callback_mod_retval($args = array())
	{
		$request = &$args["request"];
		$new = $args["new"];
		$args = &$args["args"];
		// if this is a new object, then the form is posted with the _top target
		// this ensures that the top toolbar will be updated as well
		if (!$new && $request["cb_part"])
		{
			$args["cb_part"] = $request["cb_part"];
		};
		if (!empty($request["no_rte"]))
		{
			$args["no_rte"] = 1;
		};

		if (aw_ini_get("config.object_versioning") == 1)
		{
			if (!empty($request["edit_version"]))
			{
				$out = array();
				parse_str($request["edit_version"], $out);
				$args["edit_version"] = $out["edit_version"];
			};
			if ($request["create_new_version"] == 1 || $this->force_new_version)
			{
				// set edit version to new one
				$args["edit_version"] = $this->db_fetch_field("SELECT version_id FROM documents_versions ORDER BY vers_crea DESC LIMIT 1", "version_id");
			}
		}
	}

	function callback_mod_reforb($args = array())
	{
		if ($_REQUEST["cb_part"])
		{
			$args["cb_part"] = $_REQUEST["cb_part"];
		};
		$args["post_ru"] = post_ru();
	}

	/** Shows the pic1 element. Well, I think I could use a generic solution for displaying different 
		
		@attrib name=show_pic1 params=name caption="N&auml;ita pilti" default="0"
		
		@param id required
		
		@returns
		
		
		@comment
		values

	**/
	function show_pic1($args = array())
	{
		$retval = "";
		if (isset($args["id"]))
		{
			$q = sprintf("SELECT target FROM aliases WHERE source = %d AND type = %d AND pri = 1",
					$args["id"],CL_IMAGE);
		
			$tgt = $this->db_fetch_field($q,"target");


			if (!empty($tgt))
			{
				$awi = get_instance(CL_IMAGE);
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
			$tplmgr = get_instance("templatemgr");
			$_long = $tplmgr->get_long_template($parent);
			$inst = get_instance(CL_DOCUMENT);
			$inst->read_any_template($_long);
		};

		return $inst->get_subtemplates_regex("plugin\.(\w*)");
	}

	function on_save_document($params)
	{
		if (!aw_ini_get("document.save_act_docs"))
		{
			return;
		}

		$o = obj($params["oid"]);
		$period = $o->period();
		$oid = $o->id();

		// go over all menus that are parents of this document and mark this doc as active for them if it is active and not active if it is not.
		foreach($o->path() as $p_o)
		{
			if ($p_o->id() != $o->id())
			{
				$save = false;
				$docs = $p_o->meta("active_documents");
				$docs_p = $p_o->meta("active_documents_p");
				if ($o->status() == STAT_ACTIVE)
				{
					if ($period > 1)
					{
						if (!isset($docs_p[$period][$oid]))
						{
							$save = true;
						}
						$docs_p[$period][$oid] = $oid;
					}
					else
					{
						if (!isset($docs[$oid]))
						{
							$save = true;
						}
						$docs[$oid] = $oid;
					}
				}
				else
				{
					if ($period > 1)
					{
						if (isset($docs_p[$period][$oid]))
						{
							unset($docs_p[$period][$oid]);
							$save = true;
						}
					}
					else
					{
						if (isset($docs[$oid]))
						{
							$save = true;
						}
						unset($docs[$oid]);
					}
				}

				$p_o->set_meta("active_documents", $docs);
				$p_o->set_meta("active_documents_p", $docs_p);
				if ($save && $p_o->class_id() && $p_o->parent() && $this->can("edit", $p_o->id()))
				{
					$p_o->save();
				}
			}
		}
	}

	/** 

		@attrib name=upg nologin="1"

	**/
	function upg($arr)
	{
	}

	/**
		@attrib name=convert_br
		@param id optional

	**/
	function convert_br($arr)
	{
		$ol_args = array(
			"class_id" => CL_DOCUMENT,
			"site_id" => array(),
			"lang_id" => array(),
		);

		if (is_oid($arr["id"]))
		{
			$ol_args["oid"] = $arr["id"];
		};

		$ol = new object_list($ol_args);

		//arr($ol);

		foreach($ol->arr() as $o)
		{
			print "n = " . $o->name() . "<br>";
			print "nobr = ";
			$cbdat = $o->meta("cb_nobreaks");
			$save = false;
			if (empty($cbdat["content"]))
			{
				$o->set_prop("content",str_replace("\n","<br>\n",$o->prop("content")));
				$cbdat["content"] = 1;
				$save = true;
			};
			if (empty($cbdat["lead"]))
			{
				$o->set_prop("lead",str_replace("\n","<br>\n",$o->prop("lead")));
				$cbdat["lead"] = 1;
				$save = true;
			};
			if (empty($cbdat["moreinfo"]))
			{
				$o->set_prop("moreinfo",str_replace("\n","<br>\n",$o->prop("moreinfo")));
				$cbdat["moreinfo"] = 1;
				$save = true;
			};
			if ($save)
			{
				$o->set_meta("cb_nobreaks",$cbdat);
				print "saving";
				$o->save();
			}
			else
			{
				print "not saving";
			};
			//arr($o->meta());
			print "done";
			print "<hr>";
			flush();
		};

	}

	function on_add_doc_rel($arr)
	{
		if ($arr["connection"]->prop("reltype") != 22)
		{
			return;
		}

		// create reverse conn
		$other = $arr["connection"]->to();
		
		$other->connect(array(
			"to" => $arr["connection"]->prop("from"),
			"type" => "RELTYPE_LANG_REL"
		));
	}

	function get_version_list($did)
	{
		$ret = array(aw_url_change_var("edit_version", NULL) => t("Aktiivne"));
		$this->db_query("SELECT version_id, vers_crea, vers_crea_by FROM documents_versions WHERE docid = '$did'");
		$u = get_instance(CL_USER);
		while ($row = $this->db_next())
		{
			$pers = $u->get_person_for_uid($row["vers_crea_by"]);
			$ret[aw_url_change_var("edit_version", $row["version_id"])] = $pers->name()." ".date("d.m.Y H:i", $row["vers_crea"]);
		}
		return $ret;
	}

	function callback_on_load($p)
	{
		if (!empty($p["request"]["edit_version"]))
		{
			$out = array();
			parse_str($p["request"]["edit_version"], $out);
			if (!$out["id"] && $p["request"]["id"])
			{
				$o = obj($p["request"]["id"]);
				$o->load_version($p["request"]["edit_version"]);
			}
			else
			if ($out["edit_version"] != "")
			{
				$o = obj($p["request"]["id"]);
				$o->load_version($out["edit_version"]);
			}
			
		}
	}

	function _init_versions_t(&$t)
	{
		$t->define_field(array(
			"name" => "ver",
			"caption" => t("Versioon"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "mod",
			"caption" => t("Muuda"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "rating",
			"caption" => t("Hinne"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "rate",
			"caption" => t("Hinda"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "active",
			"caption" => t("M&auml;&auml;ra aktiivseks"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "delete",
			"caption" => t("Vali"),
			"align" => "center"
		));
	}

	function _versions($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_versions_t($t);

		$u = get_instance(CL_USER);
		$rs = get_instance(CL_RATE_SCALE);
		$my = $arr["obj_inst"]->modifiedby();


		if ($my != "")
		{
			$pers = $u->get_person_for_uid($my);
			$capt = $pers->name()." ".date("d.m.Y H:i", $arr["obj_inst"]->modified())." ".t("Aktiivne");
		}
		else
		{
			$capt = $my." ".date("d.m.Y H:i", $arr["obj_inst"]->modified())." ".t("Aktiivne");
		}
		$t->define_data(array(
			"ver" => html::href(array("target" => "_blank", "url" => obj_link($arr["obj_inst"]->id()), "caption" => $capt)),
			"active" => "",
			"delete" => "",
			"rating" => $u->get_rating($my),
			"rate" => html::select(array(
				"name" => "set_rating[".$row["version_id"]."]",
				"options" => array("" => t("--Vali--")) + $rs->_get_scale(aw_ini_get("config.object_rate_scale"))
			)),
			"mod" => html::href(array(
				"target" => "_blank",
				"url" => html::get_change_url($arr["obj_inst"]->id(), array("return_url" => get_ru())),
				"caption" => t("Muuda")
			))
		));
		$u = get_instance(CL_USER);
		$this->db_query("SELECT version_id, vers_crea, vers_crea_by FROM documents_versions WHERE docid = '".$arr["obj_inst"]->id()."'");
		while ($row = $this->db_next())
		{
			$pers = $u->get_person_for_uid($row["vers_crea_by"]);
			$capt = $pers->name()." ".date("d.m.Y H:i", $row["vers_crea"]);
			$t->define_data(array(
				"ver" => html::href(array("target" => "_blank", "url" => aw_url_change_var("docversion", $row["version_id"], obj_link($arr["obj_inst"]->id())), "caption" => $capt)),
				"active" => html::radiobutton(array(
					"name" => "set_act_ver",
					"value" => $row["version_id"]
				)),
				"delete" => html::checkbox(array(
					"name" => "del_version[]",
					"value" => $row["version_id"]
				)),
				"rating" => $u->get_rating($row["vers_crea_by"]),
				"rate" => html::select(array(
					"name" => "set_rating[".$row["version_id"]."]",
					"options" => array("" => t("--Vali--")) + $rs->_get_scale(aw_ini_get("config.object_rate_scale"))
				)),
				"mod" => html::href(array(
					"target" => "_blank",
					"url" => html::get_change_url($arr["obj_inst"]->id(), array("return_url" => get_ru(), "edit_version" => $row["version_id"])),
					"caption" => t("Muuda")
				))
			));
		}
	}

	function _save_versions($arr)
	{
		$arr["obj_inst"]->set_no_modify(true);

		$o = obj($arr["request"]["id"]);
		$o->load_version("");

		$u = get_instance(CL_USER);
		foreach(safe_array($arr["request"]["set_rating"]) as $version_id => $rating)
		{
			if ($rating !== "")
			{
				// get creator for version
				if ($version_id == "")
				{
					$u->add_rating($o->modifiedby(), $rating);
				}
				else
				{
					$crea = $this->db_fetch_field("SELECT vers_crea_by FROM documents_versions WHERE docid = '".$o->id()."' AND version_id = '$version_id'", "vers_crea_by");
					if ($crea)
					{
						$u->add_rating($crea, $rating);
					}
				}
			}
		}

		// set active
		// copy from _versions table do real table and flush cache
		if ($arr["request"]["set_act_ver"] != "")
		{
			$sav = $arr["request"]["set_act_ver"];
			$this->quote(&$sav);
			$data = $this->db_fetch_row("SELECT * FROM documents_versions WHERE docid = '".$arr["obj_inst"]->id()."' AND version_id = '$sav'");
			if ($data)
			{
				// switch old and new versions
				$old_o = $this->db_fetch_row("SELECT * FROM objects WHERE oid = '".$arr["obj_inst"]->id()."'");
				$old_d = $this->db_fetch_row("SELECT * FROM documents WHERE docid = '".$arr["obj_inst"]->id()."'");

				// write old version to versions table as new version
				$o->set_no_modify(true);
				$o->set_create_new_version();
				$o->save();
				// update the modified date and modifier to point to the old modifier, because it really is HIS version
				$new_ver = $this->db_fetch_field("SELECT version_id FROM documents_versions ORDER BY vers_crea DESC LIMIT 1", "version_id");
				$this->db_query("UPDATE documents_versions SET vers_crea = $old_o[modified], vers_crea_by = '$old_o[modifiedby]' WHERE version_id = '$new_ver'");


				// write version to objtable
				$this->quote(&$data);
				$id = $arr["obj_inst"]->id();
				$this->db_query("DESCRIBE documents");
				$sets = array();
				while ($row = $this->db_next())
				{
					$sets[$row["Field"]] = $data[$row["Field"]];
				}
				$q = "UPDATE objects SET name = '$data[title]',modified = '$data[vers_crea]', modifiedby = '$data[vers_crea_by]'  WHERE oid = $id";
				$this->db_query($q);
				$q = "UPDATE documents SET ".join(",", map2("`%s` = '%s'", $sets))."  WHERE docid = $id";
				$this->db_query($q);
				$this->db_query("DELETE FROM documents_versions WHERE docid = '".$arr["obj_inst"]->id()."' AND version_id = '$sav'");

				
				$c = get_instance("cache");
				$c->file_clear_pt("storage_object_data");
				$c->file_clear_pt("storage_search");
				$c->file_clear_pt("html");
			}
		}
	}

	function _versions_tb($arr)
	{
		$toolbar = &$arr["prop"]["vcl_inst"];
		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"action" => "delete_versions",
			"img" => "delete.gif",
		));
		$toolbar->closed = 1;
	}

	function kw_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		
		$pt = $arr["obj_inst"]->id();
		if (aw_ini_get("config.keyword_folder"))
		{
			$pt = aw_ini_get("config.keyword_folder");
		}
		$tb->add_button(array(
			"name" => "new_kw",
			"tooltip" => t("M&auml;rks&otilde;na"),
			"url" => html::get_new_url(CL_KEYWORD, $pt, array("return_url" => get_ru())),
			"img" => "new.gif",
		));
		$tb->closed = 1;
	}

	/**
		@attrib name=delete_versions
	**/
	function delete_versions($arr)
	{
		// delete selected
		$o = obj($arr["id"]);
		foreach(safe_array($arr["del_version"]) as $v)
		{
			$this->quote(&$v);
			$this->db_query("DELETE FROM documents_versions WHERE docid = '".$o->id()."' AND version_id = '$v'");
		}

		return $arr["post_ru"];
	}
};
?>
