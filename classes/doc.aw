<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/doc.aw,v 2.85 2004/11/09 15:44:50 sven Exp $
// doc.aw - document class which uses cfgform based editing forms
// this will be integrated back into the documents class later on
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_DOCUMENT, on_save_document)


@classinfo trans=1 no_comment=1 relationmgr=yes

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
@caption Võtmesõnad

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
@caption Näita leadi

@property show_modified type=checkbox ch_value=1 trans=1 default=1
@caption Näita muutmise kuupäeva

@property doc_modified type=hidden table=documents field=modified trans=1
@caption Dok. modified

//---------------
@property no_right_pane type=checkbox ch_value=1 group=settings trans=1
@caption Ilma parema paanita

@property no_left_pane type=checkbox ch_value=1 group=settings trans=1
@caption Ilma vasaku paanita

@property title_clickable type=checkbox ch_value=1 group=settings trans=1 default=1
@caption Pealkiri klikitav

@property clear_styles type=checkbox ch_value=1 store=no trans=1
@caption Tühista stiilid

@property link_keywords type=checkbox ch_value=1 store=no trans=1
@caption Lingi võtmesõnad

@property link_keywords2 type=checkbox ch_value=1 field=meta method=serialize table=objects
@caption Võtmesõnad lingina

@property esilehel type=checkbox ch_value=1 group=settings trans=1
@caption Esilehel

@property frontpage_left type=checkbox ch_value=1 trans=1
@caption Esilehel tulbas

@property dcache type=checkbox store=no trans=1
@caption Cache otsingu jaoks

@property dcache_save type=checkbox ch_value=1 group=settings table=objects field=meta method=serialize trans=1
@caption Cache otsingu jaoks (salvestub)

@property dcache_content type=hidden field=dcache 
@property rating type=hidden 
@property num_ratings type=hidden 


@property show_title type=checkbox ch_value=1 default=1 trans=1
@caption Näita pealkirja

@property no_search type=checkbox ch_value=1 trans=1
@caption Jäta otsingust välja

@property cite type=textarea cols=60 rows=10 trans=1
@caption Tsitaat

@property tm type=textbox size=20 trans=1
@caption Kuupäev

@property show_print type=checkbox ch_value=1 table=objects field=meta method=serialize default=1 trans=1
@caption 'Prindi' nupp

@property sections type=select multiple=1 size=20 group=vennastamine store=no trans=1
@caption Sektsioonid

@property aliasmgr type=aliasmgr store=no editonly=1 group=relationmgr trans=1
@caption Aliastehaldur

@property start type=date_select table=planner group=calendar trans=1
@caption Algab (kp)

@property start1 type=datetime_select field=start table=planner group=calendar trans=1
@caption Algab 

@property createdby table=objects field=createdby group=general type=text trans=1
@caption Kes tegi

@property user1 table=documents group=general type=textbox size=60 trans=1
@caption Kasutaja defineeritud 1

@property user2 table=documents group=general type=textarea rows=2 cols=60 trans=1
@caption Kasutaja defineeritud 2

@property user3 table=documents group=general type=textbox trans=1
@caption Kasutaja defineeritud 3

@property user4 table=documents group=general type=textbox trans=1
@caption Kasutaja defineeritud 4

@property user5 table=documents group=general type=textbox trans=1
@caption Kasutaja defineeritud 5

@property user6 table=documents group=general type=textbox trans=1
@caption Kasutaja defineeritud 6

@property ucheck1 type=checkbox ch_value=1 table=objects field=meta method=serialize group=general
@caption Kasutaja defineeritud checkbox 1

@property language type=text group=general type=text store=no trans=1
@caption Keel

@property duration type=time_select field=end table=planner group=calendar trans=1
@caption Kestab

@property calendar_relation type=select field=meta method=serialize group=general table=objects trans=1
@caption Põhikalender

@property gen_static type=checkbox store=no trans=1
@caption Genereeri staatiline

@property sbt type=submit value=Salvesta store=no trans=1

@property cb_part type=hidden value=1 group=general,settings store=no
@caption cb_part

@property nobreaks type=hidden table=documents

@property no_topic_links type=checkbox table=objects field=meta method=serialize ch_value=1
@caption Ära tee Samal teemal linke

@property no_last type=checkbox ch_value=1 group=settings trans=1
@caption Ära arvesta muutmist

@property show_last_changed type=checkbox ch_value=1 group=settings trans=1 table=objects field=meta method=serialize
@caption Muutmise kuupaev dokumendi sees

@groupinfo calendar caption=Kalender
@groupinfo vennastamine caption=Vennastamine
@groupinfo settings caption=Seadistused icon=archive.gif
@groupinfo relationmgr caption=Seostehaldur submit=no

@tableinfo documents index=docid master_table=objects master_index=brother_of
@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype KEYWORD value=1 clid=CL_KEYWORD
@caption Märksõna
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
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
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
					if (empty($format))
					{
						$format = "d.m.Y";
					};
					$data["value"] = date($format);
				};
				break;

			case "sections":
				$d = get_instance("document");
				list($selected,$options) = $this->get_brothers(array(
					"id" => $arr["obj_inst"]->id(),
				));
				$data["options"] = array("" => "") + $options;
				$data["selected"] = $selected;
				break;

			case "calendar_relation":
				$cl = new aw_array($this->calendar_list);
				$data["options"] = array(-1 => "puudub") + $cl->get();
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
					$kw = get_instance("keywords");
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
					$dcx = get_instance("document");
					$preview = $dcx->gen_preview(array("docid" => $args["obj_inst"]->id()));
					$this->quote($preview);
					$this->_preview = $preview;
				};
				break;

			case "dcache_save":
				if ($data["value"] == 1)
				{
					$dcx = get_instance("document");
					$preview = $dcx->gen_preview(array("docid" => $args["obj_inst"]->id()));
					$this->quote($preview);
					$this->_preview = $preview;	
				}
				break;

			case "gen_static":
				if (!empty($data["value"]) && is_oid($args["obj_inst"]->id()))
				{
					$dcx = get_instance("document");
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
			$obj_inst->set_prop("content",$this->_doc_strip_tags($obj_inst->get_prop("content")));	
			$obj_inst->set_prop("lead",$this->_doc_strip_tags($obj_inst->get_prop("lead")));	
			$obj_inst->set_prop("moreinfo",$this->_doc_strip_tags($obj_inst->get_prop("moreinfo")));	
		};

		$old_tm = $obj_inst->prop("tm");
		if (empty($old_tm) && !empty($args["request"]["tm"]))
		{
			$obj_inst->set_prop("tm",date("d.m.y",$obj_inst->prop("modified")));
		};

	}

	function callback_post_save($args = array())
	{
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
			"tooltip" => "Salvesta",
			"url" => "javascript:submit_changeform();",
			"img" => "save.gif",
		));

	
		if (is_object($arr["obj_inst"]) && $arr["obj_inst"]->id())
		{
			$toolbar->add_button(array(
				"name" => "preview",
				"tooltip" => "Eelvaade",
				"target" => "_blank",
				"url" => aw_global_get("baseurl") . "/" . $arr["obj_inst"]->id(),
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
		$d = get_instance("document");
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
				"caption" => $tmp[CL_DOCUMENT]["name"],
				"link" => $this->mk_my_orb("new",array("parent" => $parent,"period" => $period),"document"),
			);
		};

		// can't use empty on function 
		$def_cfgform = aw_ini_get("document.default_cfgform");
		if (empty($def_cfgform))
		{
			$retval["ng_doc"] = array(
				"caption" => "Dokument 2.0",
				"link" => $this->mk_my_orb("new",array("parent" => $parent,"period" => $period),"doc"),
			);
		}

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
	}

	function callback_mod_reforb($args = array())
	{
		if ($_REQUEST["cb_part"])
		{
			$args["cb_part"] = $_REQUEST["cb_part"];
		};
	}

	/** Shows the pic1 element. Well, I think I could use a generic solution for displaying different 
		
		@attrib name=show_pic1 params=name caption="Näita pilti" default="0"
		
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
			$tplmgr = get_instance("templatemgr");
			$_long = $tplmgr->get_long_template($parent);
			$inst = get_instance("document");
			$inst->read_any_template($_long);
		};

		return $inst->get_subtemplates_regex("plugin\.(\w*)");
	}

	function on_save_document($params)
	{
		$o = obj($params["oid"]);
		$period = $o->period();
		$oid = $o->id();

		// go over all menus that are parents of this document and mark this doc as active for them if it is active and not active if it is not.
		foreach($o->path() as $p_o)
		{
			if ($p_o->id() != $o->id())
			{
				$docs = $p_o->meta("active_documents");
				$docs_p = $p_o->meta("active_documents_p");
				if ($o->status() == STAT_ACTIVE)
				{
					if ($period > 1)
					{
						$docs_p[$period][$oid] = $oid;
					}
					else
					{
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
						}
					}
					else
					{
						unset($docs[$oid]);
					}
				}

				$p_o->set_meta("active_documents", $docs);
				$p_o->set_meta("active_documents_p", $docs_p);
				if ($p_o->class_id() && $p_o->parent() && $this->can("edit", $p_o->id()))
				{
					$p_o->save();
				}
			}
		}
	}
};
?>
