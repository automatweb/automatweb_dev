<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/search.aw,v 2.49 2003/10/22 13:24:32 duke Exp $
// search.aw - Search Manager

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=advanced

	@property s_server type=objpicker clid=CL_AW_LOGIN
	@caption Server

	@property s_name type=textbox group=search,advanced,objsearch
	@caption Nimi

	@property s_comment type=textbox group=search,advanced,objsearch
	@caption Kommentaar

	@property s_class_id type=select group=search,advanced,objsearch
	@caption T��p

	@property s_oid type=textbox size=5
	@caption OID

	@property s_location type=select
	@caption Asukoht

	@property s_lang_id type=select
	@caption Keel

	@property s_status type=s_status group=search,advanced,objsearch
	@caption Staatus

	@property s_createdby type=textbox group=search,advanced,objsearch
	@caption Looja

	@property s_modifiedby type=textbox group=search,advanced,objsearch
	@caption Muutja

	@property s_alias type=textbox
	@caption Alias

	@property s_period type=select
	@caption Periood

	@property s_site_id type=select
	@caption Saidi ID

/////valimi nupud lisamiseks
	@property selection_manage_buttons type=text callback=selection_manage_bar group=advanced,search,results

	@property results type=text group=search,advanced,objsearch,results callback=get_result_table

	@property objsearch type=text group=objsearch callback=get_objsearch

	@groupinfo search caption=Lihtne&nbsp;otsing
	@groupinfo advanced caption=Advanced&nbsp;otsing
	@groupinfo objsearch caption=Objektiotsing
	@groupinfo results caption=Tulemused submit=no

//////////////valimite kraam////////////////////////////////////////////////////////////////////////////

	@default group=selectione
	@groupinfo selectione caption=Valimid submit=no

	@property active_selection_objects type=text callback=callback_obj_list

	@property selections type=popup_objmgr clid=CL_SELECTION multiple=1 method=serialize field=meta table=objects width=600 group=general
	@caption majanda valimeid

	@property active_selection type=textbox group=objsearch,advanced,selectione


*/
class search extends class_base
{

//// valim///
/* �hes�naga valimi klassiga n�itame valimeid ja manageerime neid
p�him�tteliselt seda valimi tabi ei olegi vaja siin n�idata
*/
	function callback_obj_list($args)
	{
		classload('kliendibaas/selection');

		$arg2['obj']['oid'] = $args['obj']['meta']['active_selection'];
		$arg2['obj']['parent'] = $args['obj']['parent'];
		$arg2['obj']['meta']['active_selection'] = $args['obj']['meta']['active_selection'];
		$arg2['obj']['meta']['selections'] = $args['obj']['meta']['selections'];
		return $this->selection_object->obj_list($arg2);
	}

	function selection_manage_bar($args = array())
	{
		$nodes = array();
		$nodes['toolbar'] = array(
			'value' => $this->selection_object->mk_toolbar(array(
				'arr' =>$this->selection['meta']['selections'],
				'parent' => $this->selection['parent'],
				'selected' => $this->selection['meta']['active_selection'],
				'align' => 'right',
				'show_buttons' => array('add','change'),
			))
		);
		return $nodes;
	}
//// end:valim///

	function search($args = array())
	{
		$this->init(array(
			"tpldir" => "search",
			"clid" => CL_SEARCH,
		));
		$this->db_rows = array();
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
                $retval = PROP_OK;

		//// valim///
		/* loome valimi instansi kui seda juba tehtud pole */
		if (!is_object($this->selection_object) && method_exists($this,'callback_obj_list'))
		{
			classload('kliendibaas/selection');
			$this->selection_object = new selection();
			$this->selection = $args['obj'];
		}
		//// end:valim///

		switch($data["name"])
                {
			//// valim///
			/* �hes�naga see on hidden element, meil on vaja et ta metas salvestuks */
			case 'active_selection':
				$retval=PROP_IGNORE;
				break;
			//// end:valim///
			
			case "s_class_id":
				$data["options"] = $this->_get_s_class_id();
				break;

			case "s_location":
				$data["options"] = $this->_get_s_parent();
				break;

			case "s_lang_id":
				$lg = get_instance("languages");
				$data["options"] = $lg->get_list(array("addempty" => true));
				break;

			case "s_period":
				$pr = get_instance("period");
				$data["options"] = $pr->period_list(aw_global_get("act_per_id"),true);
				break;

			case "s_site_id":
				$dat = $this->_search_mk_call("objects","db_query", array("sql" => "SELECT distinct(site_id) as site_id FROM objects"), $args);
				$sid = aw_ini_get("site_id");
				$sites = array($sid => $sid);
				foreach($dat as $row)
				{
					if ($row["site_id"] != $sid)
					{
						$sites[$row["site_id"]] = $row["site_id"];
					}
				}
				$sites["0"] = "K&otilde;ik";
				$data["options"] = $sites;
				break;
		}
		return $retval;
	}

	function get_objsearch($args = array())
	{
		// now I need to load the bloody object and retrieve the property list
		// for it.
		$obj = $this->get_object($args["obj"]["oid"]);
		$cfgu = get_instance("cfg/cfgutils");
                $_all_props = $cfgu->load_properties(array(
                        "clid" => $obj["meta"]["s_class_id"],
                ));

		$retval = array();
		$item = array(
			"caption" => "Objektit��bi omadused",
		);
		$retval[] = $item;

		foreach($_all_props as $key => $val)
		{
			if ($val["search"])
			{
				$val["value"] = $obj["meta"]["obj"][$val["name"]];
				$retval[] = $val;
			};
		}

		if (sizeof($retval) == 1)
		{
			$item = array(
				"caption" => "<b><font color='red'>Sellel klassil pole �htegi otsitavat omadust</font></b>",
			);
			$retval[] = $item;
		};

		return $retval;
	}

	function callback_pre_save($args = array())
	{
		$obj = $this->get_object($args["id"]);
		$cfgu = get_instance("cfg/cfgutils");
                $_all_props = $cfgu->load_properties(array(
                        "clid" => $obj["meta"]["s_class_id"],
                ));
		
		$obj_search_fields = array();

		foreach($_all_props as $key => $val)
		{
			if ($val["search"] && $args["form_data"][$val["name"]])
			{
				$retval[$val["name"]] = $args["form_data"][$val["name"]];
			};
		}
		$args["coredata"]["metadata"]["obj"] = $retval;
	}

	function get_result_table($args = array())
	{
		$meta = new aw_array($args["obj"]["meta"]);
		$cfgu = get_instance("cfg/cfgutils");
                $_all_props = $cfgu->load_properties(array(
                        "clid" => $args["obj"]["meta"]["s_class_id"],
                ));

		$params = array();
		foreach($meta->get() as $key => $val)
		{
			if (substr($key,0,2) == "s_")
			{
				$realkey = substr($key,2);
				$params[$realkey] = $val;
			};
		};
		$real_fields = $this->get_real_fields(array("s" => $params));
		// obj-meta-obj contains the values for object search
		$obj_fields = $args["obj"]["meta"]["obj"];
		$obj_data = array();
		if (is_array($obj_fields))
		{
			foreach($obj_fields as $key => $val)
			{
				$obj_data[$key] = array(
					"table" => $_all_props[$key]["table"],
					"field" => $_all_props[$key]["field"],
					"method" => $_all_props[$key]["method"],
					"value" => $val,
				);
					
			};
		};

		$this->execute_search(array(
			"real_fields" => $real_fields,
			"obj_data" => $obj_data,
		));
		$nodes = array();
		$nodes[] = array(
			"value" => $this->table,
		);
		return $nodes;
	}

	////
	// !Shows a search form
	// this is the core for interactive searching. It allows to enter data
	// for search fields and displays the form and/or the results table
	// args:
	// fields(array) - list of fields you want in the search form
	// 	fields = array("name" => "%","comment" => "%");
	// 	obj - reference to caller
	function show($args = array())
	{


		$val = $args['s']['class_id'];
		if (is_array($val) || (($va = explode(',',ltrim($val,','))) && (count($va)>0)))
		{
			if (is_array($va))
			{
				$val = $va;
			}
			$tmp = array();
			foreach($val as $_v)
			{
				if ($_v != 0)
				{
					$tmp[] = $_v;
				}
			}
			$this->clid_selected = $tmp;
		}
		else
		if ($val != 0)
		{
			$this->clid_selected = array($val);
		};


		classload('icons');
		$this->db_rows = array();
		// all the required fields and their default values
		$defaults = array(
			"name" => "",
			"comment" => "",
			"class_id" => 0,
			"location" => 0,
			"createdby" => "",
			"modifiedby" => "",
			"status" => 3,
			"special" => "",
			"alias" => "",
			"redir_target" => "",
			"oid" => "",
			"site_id" => aw_ini_get('site_id')
		);

		$_obj = $args["obj"];
		$real_fields = array_merge($defaults,$args["s"]);
		$this->sub_merge = 1;
		extract($args);
		extract($real_fields);
		$c = "";
		$table = "";

		// create an instance of a object for callbacks
		if ($args["clid"])
		{
			if (gettype($args["clid"]) == "object")
			{
				$this->obj_ref = $args["clid"];
			}
			else
			{
				$_obj = get_instance($args["clid"]);
				if (!$_obj)
				{
					$this->raise_error(ERR_CORE_NO_FILE,"Cannot create an instance of $clid",true);
				};
				$this->obj_ref = $_obj;
				if ($args["clid"] == "document")
				{
					$mn = get_instance("admin/admin_menus");
					$parent = (int)$parent;
					$toolbar = $mn->rf_toolbar(array(
						"parent" => $parent,
						"no_save" => 1,
					));
				};
			};
			$this->read_template("objects.tpl");
		}
		else
		{
			$this->read_template("full.tpl");
			$parent = (int)$parent;


			$url = $this->mk_my_orb("search",array("parent" => $parent));
			$this->parent = $parent;
			$this->mk_path($parent,"<a href='$url'>Objektiotsing</a>");
		};

		$this->rescounter = 0;

		// perform the actual search
		if ($search)
		{
			$obj_list = $this->_get_s_parent($args);
			load_vcl("table");
			$this->t = new aw_table(array("layout" => "generic"));

			$this->_init_os_tbl();

			$parts = array();
			//$partcount = 0;
			$partcount = 1; //ahto wants that we search all the objects whenever we havent specified any search parameters
			foreach($real_fields as $key => $val)
			{
				$_part = "";
				switch ($key)
				{
					case "name":
						if ($val)
						{
							$parts["name"] = " name LIKE '%$val%' ";
							$partcount++;
						};
						break;

					case "comment":
						if ($val)
						{
							$parts["comment"] = " comment LIKE '%$val%' ";
							$partcount++;
						};
						break;

					case "location":

						if ($val === 0)
						{
							/*$tmp = join(",",array_filter(array_keys($obj_list),create_function('$v','return $v ? true : false;')));
							if (!empty($tmp))
							{
								$_part = " parent IN ($tmp) ";
							};*/
						}
						else
						{
							if (is_numeric($val))
							{
								$_part = " parent = '$val' ";
								$partcount++;
							}
							elseif (strlen($val) > 0)
							{
								$q = "select ".OID." from objects where class_id=".CL_PSEUDO." and name like '%".$val."%' limit 100";
								$locs = $this->db_fetch_array($q);
								if (count($locs)>0)
								{
									foreach($locs as $val)
									{
										$loc[] = $val[OID];
									}
									$_part = " parent in (".implode(',',$loc).") ";
								}
								$partcount++;
							}
						};
						if (!empty($_part))
						{
							$parts["location"] = $_part;
						};
						break;

					case "createdby":
						if ($val)
						{
							$val = preg_replace("/\s/","",$val);
							$_val = explode(",",$val);
							$val = join(",",map("'%s'",$_val));
							$parts["createdby"] = " createdby IN ($val) ";
							$partcount++;
						};
						break;

					case "modifiedby":
						if ($val)
						{
							$val = preg_replace("/\s/","",$val);
							$_val = explode(",",$val);
							$val = join(",",map("'%s'",$_val));
							$parts["modifiedby"] = " modifiedby IN ($val) ";
						};
						break;

					case "alias":
						if ($val)
						{
							$parts["alias"] = " alias LIKE '%$val%'";
							$partcount++;
						};
						break;

					case "class_id":

						if (is_array($val) || (($va = explode(',',ltrim($val,','))) && (count($va)>0)))
						{
							if (is_array($va))
							{
								$val = $va;
							}

							$tmp = array();
							foreach($val as $_v)
							{
								if ($_v != 0)
								{
									$tmp[] = $_v;
								}
							}
							$xval = join(",",$tmp);
							if ($xval != "")
							{
								$parts["class_id"] = " class_id IN ($xval) ";
								$partcount++;
							}
						}
						else
						if ($val != 0)
						{
							$parts["class_id"] = " class_id = '$val' ";
							$partcount++;
						};
						break;

					case "status":
						if ($val == 3)
						{
							$parts["status"] = " status != 0 ";
						}
						elseif ($val == 2)
						{
							$parts["status"] = " status = 2 ";
						}
						elseif ($val == 1)
						{
							$parts["status"] = " status = 1 ";
						};
						break;

					case "lang_id":
						if ($val)
						{
							$parts["lang_id"] = " lang_id = '$val' ";
						};
						break;

					case "period":
						if ($val)
						{
							$parts["period"] = " period = '$val' ";
						};
						break;

					case "site_id":
						// ignore site_id if we are doing a remote search
						if (empty($args["s"]["server"]) && $val)
						{
							$parts["site_id"] = " site_id = '$val' ";
						};
						break;

					case "oid":
						if ($val)
						{
							$parts["oid"] = " oid = '$oid' ";
							$partcount++;
						};
						break;

					default:
				};
					
			};

			// first check, whether the caller has a do_search callback,
			// if so, we assume that it knows what it's doing and simply
			// call it.
			$caller_search = $this->search_callback(array("name" => "do_search","args" => $args));

			// if not, use our own (old?) search method
			if ($caller_search === false)
			{
				$this->search_callback(array("name" => "modify_parts","args" => &$args,"parts" => &$parts));
				$query = $this->search_callback(array("name" => "get_query","args" => $args,"parts" => $parts));
				if ($query)
				{
					$this->db_query($query);
					$partcount = 1;
				}
				elseif ($partcount == 0)
				{
					$table = "<span style='font-family: Arial; font-size: 12px; color: red;'>Defineerige otsingutingimused</span>";
				}
				else
				{
					$where = join(" AND ",$parts);
					// limit the results to 500
					$q = "SELECT * FROM objects WHERE $where LIMIT 500";
//					echo "s_q = $q <br />";
					$_tmp = array();
					$_tmp = $this->_search_mk_call("objects", "db_query", array("sql" => $q), $args);
					if (is_array($_tmp))
					{
						$this->db_rows = $_tmp;
					}
					reset($this->db_rows);
				};

				$results = 0;
			};

			// we need to make the object change links point to the remote server if specified. so fake it. 
			if ($args["s"]["server"])
			{
				$lo = get_instance("remote_login");
				$serv = $lo->get_server($args["s"]["server"]);
				$old_bu = $this->cfg["baseurl"];
				$this->cfg["baseurl"] = "http://".$serv;
			}

			//while($row = $this->db_next())
			while($row = $this->get_next())
			{
				if (!$this->can("view",$row["oid"])) continue;
				
				$this->rescounter++;
				$type = $this->cfg["classes"][$row["class_id"]]["name"];
				$row["icon"] = sprintf("<img src='%s' alt='$type' title='$type'>",icons::get_icon_url($row["class_id"],""));
				if (!$row["name"])
				{
					$row["name"] = "(nimetu)";
				};
				$row["type"] = $type;
				$row["location"] = $obj_list[$row["parent"]];
				$this->search_callback(array(
					"name" => "modify_data",
					"data" => &$row,
					"args" => $args,
				));
				if (!$args["clid"] || ($args["clid"] == "aliasmgr"))
				{
					$row["name"] = "<a href='" . $this->mk_my_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $this->cfg["classes"][$row["class_id"]]["file"]) . "'>$row[name]</a>";
				};

				// trim the location to show only up to 3 levels
				$_loc = explode("/",$row["location"]);
				while(sizeof($_loc) > 3)
				{
					array_shift($_loc);
				};
				$row["location"] = join("/",$_loc);

				if (!$args["clid"])
				{
					$row["change"] = "<input type='checkbox' name='sel[$row[oid]]' value='$row[oid]'>";
				};

				$this->t->define_data($row);
				$results++;
			};

			if ($args["s"]["server"])
			{
				$this->cfg["baseurl"] = $old_bu;
			}

			if ($partcount > 0)
			{
				if (!$sortby)
				{
					$sortby = "name";
				}
				$this->t->sort_by(array("field" => $sortby));

				$table .= "<span style='font-family: Arial; font-size: 12px;'>$results tulemust</span>";
				$table .= $this->t->draw();
				$table .= "<span style='font-family: Arial; font-size: 12px;'>$results tulemust</span>";
			};

			if ($results > 0)
			{
				// I use that in modify_toolbar to determine whether
				// to show the "create object group" and "assign configuration"
				// buttons
				$this->has_results = 1;
			};

		};

		//ain't the best solution but the simplest right now
		// disable some search fileds in search used by menuedit
		if (($args['class'] == 'search') && ($args['action'] == 'search'))
		{
			$fields = array("location" => "n/a");
		}
		else
		{
			$fields = array();
		}

		$this->search_callback(array("name" => "get_fields","fields" => &$fields,"args" => $args));

		$this->modify_fields($args,&$fields);

		//foreach($real_fields as $key => $val)

		$selected = '';
		if (is_array($this->clid_selected))
		{
			foreach($this->clid_selected as $val)
			{
				$selected .= "defaults[".$val."] = 1;\n";
			}
		}

		foreach($fields as $key => $val)
		{
			if (is_array($fields[$key]))
			{
				$fieldref = $fields[$key];
				if (!isset($fieldref['name']))
				{
					$fieldref['name'] = 's['.$key.']';
				}

				switch($fieldref["type"])
				{
					case "select":
						$items = $this->picker($fieldref["selected"],$fieldref["options"]);
						$element = "<select name='".$fieldref['name']."' onChange='$fieldref[onChange]'>$items</select>";
						$caption = $fieldref["caption"];
						break;

					case "radiogroup":

						$element = "";
						foreach($fieldref["options"] as $okey => $oval)
						{
							$checked = checked($okey == $fieldref["selected"]);
							$element .= sprintf("<input type='radio' name='".$fieldref['name']."' value='%s' %s> %s &nbsp;&nbsp;&nbsp;",$okey,$checked,$oval);
						};

						$caption = $fieldref["caption"];
						break;

					case "class_id_multiple":
						if (is_array($fieldref["selected"]))
						{
							$sel = array_flip($fieldref["selected"]);
						}
						else
						{
							$sel = array();
						};

						$items = $this->mpicker($sel,$fieldref["options"]);
						$size = ($fieldref["size"]) ? $fieldref["size"] : 5;

						$this->vars(array(
							'selected' => $selected,
							'element' => 's[class_id][]',
						));


						$mselectbox = '<select multiple size="'.$size.'" name="s[class_id][]" style="width:200px"></select>'.$this->parse('getoptions');

						if (isset($fieldref["filter"]))
						{
							$element = html::textbox(array('name'=>'pattern1', 'size' => '14')).
								html::button(array(
									'name'=>'selectmatching1',
									'value'=>'vali',
									'onclick' => "selectMatchingOptions(document.forms[1].elements['s[class_id][]'],document.forms[1].pattern1.value.toLowerCase())",
								)).
								'<br />'.
								$mselectbox;
						}
						else
						{
							$element = $mselectbox;
						}
						$caption = $fieldref["caption"];
						break;

					case "class_id_hidden":
						$this->vars(array(
							'selected' => $selected,
							'element' => 's[class_id]',
						));

						$element = html::hidden($fieldref).$this->parse('getoptions');


					break;

					case "multiple":
						
						if (is_array($fieldref["selected"]))
						{
							//$sel = array_flip($fieldref["selected"]);
							$sel = $fieldref["selected"];	
						}
						else
						{
							$sel = array();
						};
						//$items = $this->mpicker($fieldref["selected"],$fieldref["options"]);
						$items = $this->mpicker($sel,$fieldref["options"]);
						$size = ($fieldref["size"]) ? $fieldref["size"] : 5;
						$element = sprintf("<select multiple size='$size' name='".$fieldref['name']."[]' onChange='%s'>%s</select>",$fieldref["onChange"],$items);
						$caption = $fieldref["caption"];
						break;

					case "textbox":
						//$element = "<input type='text' name='s[$key]' size='40' value='$fieldref[value]'>";
						$element = html::textbox($fieldref);
						$caption = $fieldref["caption"];
						break;

					case "checkbox":
						$element = "<input type='checkbox' name='".$fieldref['name']."' $fieldref[checked]>";
						$caption = $fieldref["caption"];
						break;

					case 'text':
						$element = $fieldref['value'];
						$caption = $fieldref["caption"];
						break;

					case 'hidden':
						$element = html::hidden($fieldref);//.$fieldref['value'];
						break;

					default:
						$element = "n/a";
						$caption = "n/a";
						break;
				};

				if (($fieldref['type'] == 'class_id_hidden') || ($fieldref['type'] == 'hidden') )
				{
					$this->vars(array(
							"element" => $element,
					));
					$c .= $this->parse("hidden");
				}
				else
				{
					$this->vars(array(
							"caption" => $caption,
							"element" => $element,
					));

					$c .= $this->parse("field");
				}
			};
		};

		if ($args["clid"])
		{
			$header = $this->search_callback(array("name" => "table_header","args" => $args));
			if (!$header)
			{
				$header = "<form name='searchform_'>";
			};
			
			$footer = $this->search_callback(array("name" => "table_footer","args" => $args));
			if (!$footer)
			{
				$footer = "</form>";
			};

			$table = $header .$table . $footer;
		}
		else
		{
			$table = "<form name='resulttable' method='post' action='reforb.aw'>" . $table;
			$args["subaction"] = "kala";
			$args["grpname"] = "noname";
			unset($args["class"]);
			unset($args["action"]);
			unset($args["no_reforb"]);
			unset($args["reforb"]);
			if (is_array($args["class_id"]))
			{
				$class_ids = join("\n",map("<input type='hidden' name='class_id[]' value='%d'>",$args["class_id"]));
				unset($args["class_id"]);
			};
			if (is_array($args["s"]))
			{
				$ss = $this->req_array_reforb("s", $args["s"]);
				unset($args["s"]);
			};
			$this->vars(array(
				"redir_target" => $this->_get_s_redir_target(),
				"treforb" => $this->mk_reforb("submit_table",$args) . $class_ids . $ss,
				"ef" => "</form>",
			));
		};


		if (!$args["clid"])
		{
			$mn = get_instance("admin/admin_menus");
			
			$toolbar = $mn->rf_toolbar(array(
				"parent" => $parent,
				"callback" => array($this,"modify_toolbar"),
				"no_save" => 1,
			));

			// override the search button with our own
			$toolbar->add_button(array(
				"name" => "search",
				"tooltip" => "Otsi",
				"url" => "javascript:document.searchform.submit()",
				"imgover" => "search_over.gif",
				"img" => "search.gif",
			));
		};

		$this->table = $table;

		if (!isset($reforb))
		{
			$reforb = $this->mk_reforb("search",array("no_reforb" => 1,"search" => 1,"obj" => $args["obj"],"docid" => $docid, "parent" => $parent));
		}


		$this->vars(array(
			"table" => $table,
			"toolbar" => ((is_object($toolbar)) ? $toolbar->get_toolbar() : ''),
			"reforb" => $reforb
		));

		return $this->parse();
	}

	function req_array_reforb($k, $v)
	{
		$ret = "";
		foreach($v as $_k => $_v)
		{
			if (is_array($_v))
			{
				$ret .= $this->req_array_reforb($k."[".$_k."]",$_v);
			}
			else
			{
				$ret .= "<input type='hidden' name='".$k."[".$_k."]' value=\"".str_replace("\"","&amp;",$_v)."\" />\n";
			}
		}
		return $ret;
        }


	function modify_toolbar($args)
	{
		if ($this->has_results && is_object($args["toolbar"]))
		{
			$args["toolbar"]->add_separator();

			$url = "javascript:mk_group('Objektigrupi nimi:')";
			$link = "<a href=\"$url\" class=\"fgtext\">Moodusta objektigrupp</a>";

                        $args["toolbar"]->add_cdata($link);

		};
	}

	function get_results()
	{
		return $this->table;
	}

	function modify_fields($args = array(),&$fields)
	{
		// two ways to solve the problem
		// 1 - put the fields into array, so that I can fetch
		// the names of the search fields and remember the values OR
		// 2 - put the names of the search fields into an array
		// s[name] .. and so on, so that I can access them by
		// accessing the s array. I like the latter a lot more.

		if (!$fields["obj_name"] && $this->is_object_search)
		{
			$fields["obj_name"] = array(
				"type" => "textbox",
				"caption" => "Objekti nimi",
				"value" => $args["s"]["obj_name"],
			);
		};

		if (!$fields["server"])
		{
			$fields["server"] = array(
				"type" => "select",
				"caption" => "Server",
				"options" => $this->list_objects(array("addempty" => true, "class" => CL_AW_LOGIN)),
				"selected" => $args["s"]["server"],
			);
		};

		if (!$fields["name"])
		{
			$fields["name"] = array(
				"type" => "textbox",
				"caption" => "Nimi",
				"value" => $args["s"]["name"],
			);
		};

		if (!$fields["comment"])
		{
			$fields["comment"] = array(
				"type" => "textbox",
				"caption" => "Kommentaar",
				"value" => $args["s"]["comment"],
			);
		};
		
		if (!$fields["special"])
		{
			$fields["special"] = array(
				"type" => "checkbox",
				"caption" => "Spetsiaalotsing",
				"checked" => checked($args["s"]["special"]),
			);
		};

		if (!$fields["class_id"])
		{
			$fields["class_id"] = array(
				"type" => "multiple",
				"caption" => "T��p",
				"size" => 10,
				"options" => $this->_get_s_class_id($args["s"]["class_id"]),
				"selected" => $args["s"]["class_id"],
				"onChange" => "refresh_page(this)",
			);

		};
		
		if (!$fields["oid"])
		{
			$fields["oid"] = array(
				"type" => "textbox",
				"caption" => "ID",
				"size" => 6,
				"value" => $args["s"]["oid"],
			);
		};


		if (!$fields["location"])
		{
/*			$fields["location"] = array(
				"type" => "select",
				"caption" => "Asukoht",
				"options" => $this->_get_s_parent($args),
				"selected" => $args["s"]["location"],
			);*/
			$fields["location"] = array(
				"type" => "textbox",
				"caption" => "Asukoht",
				"size" => '25',
				"value" => $args["s"]["location"],
			);
		};

		if (!$fields["createdby"])
		{
			$fields["createdby"] = array(
				"type" => "textbox",
				"caption" => "Looja",
				"value" => $args["s"]["createdby"],
				'size' => '15',
			);
		};

		if (!$fields["modifiedby"])
		{
			$fields["modifiedby"] = array(
				"type" => "textbox",
				"caption" => "Muutja",
				"value" => $args["s"]["modifiedby"],
				'size' => '15',
			);
		};

		if (!$fields["status"])
		{
			$fields["status"] = array(
				"type" => "radiogroup",
				"caption" => "Aktiivsus",
				"options" => array("3" => "K�ik","2" => "Aktiivsed","1" => "Deaktiivsed"),
				"selected" => ($args["s"]["status"]) ? $args["s"]["status"] : 3,
			);
		};

		if (!$fields["alias"])
		{
			$fields["alias"] = array(
				"type" => "textbox",
				"caption" => "Alias",
				"value" => $args["s"]["alias"],
			);
		};

		if (!$fields["lang_id"])
		{
			$lg = get_instance("languages");
//			$li = $lg->get_list(array("addempty" => true, "ignore_status" => true));
			$li = $lg->get_list(array("ignore_status" => true));
			$fields["lang_id"] = array(
//				"type" => "select",
				"type" => 'radiogroup',
				"caption" => "Keel",
				"options" => $li,
				"selected" => $args["s"]["lang_id"],
			);
		};

		if (!$fields["period"])
		{
			$lg = get_instance("period");
			$fields["period"] = array(
				"type" => "select",
				"caption" => "Periood",
				"options" => $lg->period_list(aw_global_get("act_per_id"), true),
				"selected" => $args["s"]["period"],
			);
		};

		if (!$fields["site_id"])
		{
			$dat = $this->_search_mk_call("objects","db_query", array("sql" => "SELECT distinct(site_id) as site_id FROM objects"), $args);
			$sites = array("0" => "");
			foreach($dat as $row)
			{
				$sites[$row["site_id"]] = $row["site_id"];
			}
			$fields["site_id"] = array(
				"type" => "select",
				"caption" => "Saidi ID",
				"options" => $sites,
				"selected" => ($args["s"]["site_id"] ? $args["s"]["site_id"] : aw_ini_get("site_id")),
			);
		};
		
	}

	// generates contents for the class picker drop-down menu
	function _get_s_class_id()
	{
		$tar = array(0 => LC_OBJECTS_ALL) + get_class_picker();
		return $tar;
	}

	function _get_s_parent($args = array())
	{
		$ret = $this->_search_mk_call("objects", "get_list", array(), $args);
		if (!is_array($ret))
		{
			return array("0" => "Igalt poolt");
		}
		return array("0" => "Igalt poolt") + $ret;
	}

	function _get_s_redir_target()
	{
		$this->vars(array(
			"clid" => 7,
			"url" => $this->mk_my_orb("docsearch",array("parent" => $this->parent),"document"),
		));
		$retval = $this->parse("redir_target");
		$this->vars(array(
			"redir_target" => '',
		));
		return $retval;
	}

	function _init_os_tbl()
	{
		$this->t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));

		$this->t->define_field(array(
			"name" => "icon",
			"caption" => "",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
			

		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
			"sortable" => 1,
		));
			
		$this->t->define_field(array(
			"name" => "type",
			"caption" => "T��p",
			"talign" => "center",
			"nowrap" => "1",
			"sortable" => 1,
		));
			
		$this->t->define_field(array(
			"name" => "location",
			"caption" => "Asukoht",
			"talign" => "center",
			"sortable" => 1,
		));
			
		$this->t->define_field(array(
			"name" => "created",
			"caption" => "Loodud",
			"talign" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"nowrap" => "1",
			"type" => "time",
			"format" => "d.m.y / H:i"
		));

		$this->t->define_field(array(
			"name" => "createdby",
			"caption" => "Looja",
			"talign" => "center",
			"sortable" => 1,
		));

		$this->t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"talign" => "center",
			"sortable" => 1,
			"nowrap" => "1",
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.y / H:i"
		));
			
		$this->t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
			"talign" => "center",
			"sortable" => 1,
		));
		
		$this->t->define_field(array(
			"name" => "change",
			"caption" => "<a href='javascript:selall(\"sel\")'>Vali</a>",
			"align" => "center",
			"talign" => "center",
		));
	}

	function get_next()
	{
		if (isset($this->obj_ref) && method_exists($this->obj_ref,"search_callback_get_next"))
		{
			$result = $this->search_callback(array("name" => "get_next"));
		}
		else
		{
//			$result = $this->db_next();
			list(,$result) = each($this->db_rows);
		};
		return $result;
	}

	////
	// !Callback funktsioonid
	function search_callback($args = array())
	{
		$prefix = "search_callback_";
		if (!isset($this->obj_ref) || !is_object($this->obj_ref))
		{
			return false;
		};
		$allowed = array("get_fields","get_query","get_table_defs","modify_data","table_header","table_footer","do_query","get_next","modify_parts");

		// paranoia? maybe. but still, do not let the user use random functions
		// from the caller.
		if (!in_array($args["name"],$allowed))
		{
			$retval = false;
		};

		$name = $prefix . $args["name"];

		if (method_exists($this->obj_ref,$name))
		{
			if ($args["data"])
			{
				// data is defined for modify_data and works directly
				// on the data fetched from the database and not on a copy

				// still, if it's done otherwise in the future, return
				// the possible value as well
				$retval = $this->obj_ref->$name(&$args["data"],$args["args"]);
			}
			elseif ($name == "search_callback_get_fields")
			{
				$retval = $this->obj_ref->$name(&$args["fields"],$args["args"]);
			}
			elseif ($name == "search_callback_get_query")
			{
				$retval = $this->obj_ref->$name($args["args"],$args["parts"]);
			}
			elseif ($name == "get_next")
			{
				$retval = $this->obj_ref->$name();
			}
			elseif ($name == "search_callback_modify_parts")
			{
				$retval = $this->obj_ref->$name(&$args["args"],&$args["parts"]);
			}
			else
			{
				$retval = $this->obj_ref->$name($args["args"]);
			};

		}
		else
		{
			$retval = false;
		};

		return $retval;

	}

	function submit_table($args = array())
	{
		extract($args);
		// if we searched from a remote server we need to copy the damn things instead
		if ($subaction == "cut" && !$s["server"])
		{
			$cut_objects = array();
			if (is_array($sel))
			{
				foreach($sel as $oid => $one)
				{
					$cut_objects[$oid] = $oid;
				}
			}
			aw_session_set("cut_objects",$cut_objects);
		}
		elseif($subaction == "copy" || ($subaction == "cut" && $s["server"]))
		{
			$copied_objects = array();
			$rels = array();
			if (is_array($sel))
			{
				// ok, so how do I add objects to here?
				foreach($sel as $oid => $one)
				{
//					aw_global_set("xmlrpc_dbg",1);
					$r = $this->_search_mk_call("objects", "serialize", array("oid" => $oid), $args);

					if ($r !== false)
					{
						if (is_array($r["connections"]))
						{
							$rels = $rels + $r["connections"];
						};
						$copied_objects[$oid] = $r;
						$ra = aw_unserialize($r);
//					echo "r = $r <br />ra = <pre>", var_dump($ra),"</pre> <br />";
					}
				}
			}
			foreach($rels as $rel_id)
			{
				$r = $this->_search_mk_call("objects","serialize",array("oid" => $rel_id["to"]), $args);
				if ($r !== false)
				{
					$copied_objects[$rel_id["to"]] = $r;
				};
			};
			aw_session_set("copied_objects", $copied_objects);
		}
		elseif($subaction == "delete")
		{
			if (is_array($sel))		
			{
				while (list($ooid,) = each($sel))
				{
					if($this->can('delete',$ooid))
					{
						$this->_search_mk_call("objects", "delete_object", array("oid" => $ooid), $args);
						$this->_search_mk_call("objects", "delete_aliases_of", array("oid" => $ooid), $args);					
					}
				}
			};
		}
		elseif($subaction == "mkgroup")
		{
			// aga kuidas otsing kirja panna?
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $grpname,
				"class_id" => CL_OBJECT_CHAIN,
				"metadata" => array("objs" => $sel,"s" => $s),
			));
		}
		elseif ($subaction == "assign_config")
		{
			$ac = get_instance("cfg/cfgobject");
			die($ac->assign($args));
		};

		unset($args["class"]);
		unset($args["action"]);
		unset($args["subaction"]);
		unset($args["sel"]);
		unset($args["reforb"]);
		$retval = $this->mk_my_orb("search",$args);
		return $retval;
	}

	function assign_config($args = array())
	{
		print "inside cfgobject->assign_config<br />";
		print "<pre>";
		print_r($args);
		print "</pre>";

	}

	function _search_mk_call($class, $action, $params, $args)
	{
		$_parms = array(
			"class" => $class,
			"action" => $action, 
			"params" => $params
		);
		if (!empty($args["s"]["server"]))
		{
			$_parms["method"] = "xmlrpc";
			$_parms["login_obj"] = $args["s"]["server"];
		}
		$ret =  $this->do_orb_method_call($_parms);
		return $ret;
	}

	function get_real_fields($args = array())
	{
		$defaults = array(
			"name" => "",
			"comment" => "",
			"class_id" => 0,
			"location" => 0,
			"createdby" => "",
			"modifiedby" => "",
			"status" => 3,
			"special" => "",
			"alias" => "",
			"redir_target" => "",
			"oid" => "",
		);
		$real_fields = array_merge($defaults,$args["s"]);
		return $real_fields;
	}

	function execute_search($args = array())
	{
		$obj_list = $this->_get_s_parent($args);
		extract($args);
		if (is_array($request))
		{
			extract($request);
		};
		$format = ($format) ? $format : "vcl";

		if ($format == "vcl")	
		{
			load_vcl("table");
			$this->t = new aw_table(array(
				"prefix" => "search",
			));

			$this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
			$this->_init_os_tbl();
		};

		$parts = array();
		$partcount = 0;
		foreach($real_fields as $key => $val)
		{
			switch ($key)
			{
				case "name":
					if ($val)
					{
						$parts["name"] = " name LIKE '%$val%' ";
						$partcount++;
					};
					break;

				case "comment":
					if ($val)
					{
						$parts["comment"] = " comment LIKE '%$val%' ";
						$partcount++;
					};
					break;

				case "location":
					if ($val == 0)
					{
						$_part = sprintf(" parent IN (%s) ",join(",",array_filter(array_keys($obj_list),create_function('$v','return $v ? true : false;'))));
					}
					else
					{
						$_part = " parent = '$val' ";
						$partcount++;
					};
					$parts["location"] = $_part;
					break;

				case "createdby":
					if ($val)
					{
						$val = preg_replace("/\s/","",$val);
						$_val = explode(",",$val);
						$val = join(",",map("'%s'",$_val));
						$parts["createdby"] = " createdby IN ($val) ";
						$partcount++;
					};
					break;

				case "modifiedby":
					if ($val)
					{
						$val = preg_replace("/\s/","",$val);
						$_val = explode(",",$val);
						$val = join(",",map("'%s'",$_val));
						$parts["modifiedby"] = " modifiedby IN ($val) ";
					};
					break;

				case "alias":
					if ($val)
					{
						$parts["alias"] = " alias LIKE '%$val%'";
						$partcount++;
					};
					break;

				case "class_id":
					if (is_array($val))
					{
						$xval = join(",",$val);
						$parts["class_id"] = " class_id IN ($xval) ";
						$partcount++;
					}
					elseif ($val != 0)
					{
						$parts["class_id"] = " class_id = '$val' ";
						$partcount++;
					};
					break;

				case "status":
					if ($val == 3)
					{
						$parts["status"] = " status != 0 ";
					}
					elseif ($val == 2)
					{
						$parts["status"] = " status = 2 ";
					}
					elseif ($val == 1)
					{
						$parts["status"] = " status = 1 ";
					};
					break;

				case "lang_id":
					if ($val)
					{
						$parts["lang_id"] = " lang_id = '$val' ";
					};
					break;

				case "period":
					if ($val)
					{
						$parts["period"] = " period = '$val' ";
					};
					break;

				case "site_id":
					if ($val)
					{
						$parts["site_id"] = " site_id = '$val' ";
					};
					break;

				case "oid":
					if ($val)
					{
						$parts["oid"] = " oid = '$oid' ";
						$partcount++;
					};
					break;

				default:
			};
				
		};

		// first check, whether the caller has a do_search callback,
		// if so, we assume that it knows what it's doing and simply
		// call it.
		$caller_search = $this->search_callback(array("name" => "do_search","args" => $args));

		// if not, use our own (old?) search method
		if ($caller_search === false)
		{
			$query = $this->search_callback(array("name" => "get_query","args" => $args,"parts" => $parts));

			if ($query)
			{
				$this->db_query($query);
				$partcount = 1;
			}
			elseif ($partcount == 0)
			{
				$this->table = "<span style='font-family: Arial; font-size: 12px; color: red;'>Defineerige otsingutingimused</span>";
			}
			else
			{
				// if there are any object fields then I need to create the big badass query
				// for property table now
				$join = '';

				if (is_array($obj_data) && (sizeof($obj_data) > 0))
				{
					$p = 0;
					foreach($obj_data as $k => $v)
					{
						$p++;
						if ( ($v["method"] == "serialize") )
						{
							$join .= " INNER JOIN properties AS p_val$p ON (p_val$p.oid = objects.oid AND p_val$p.pname = '$k' and p_val$p.pvalue = '$v[value]') ";
						}
						else
						{
							$join .= " INNER JOIN $v[table] AS p_val$p ON (p_val$p.id = objects.oid AND p_val$p.$v[field] = '$v[value]') ";
						};
					};
				};
				$where = join(" AND ",$parts);
				$q = "SELECT *,objects.name AS name FROM objects $join WHERE $where";
//					echo "s_q = $q <br />";
				$_tmp = array();
				$_tmp = $this->_search_mk_call("objects", "db_query", array("sql" => $q), $args);
				if (is_array($_tmp))
				{
					$this->db_rows = $_tmp;
				}
				reset($this->db_rows);
			};

			$results = 0;
		};

		// we need to make the object change links point to the remote server if specified. so fake it. 
		if ($args["s"]["server"])
		{
			$lo = get_instance("remote_login");
			$serv = $lo->get_server($args["s"]["server"]);
			$old_bu = $this->cfg["baseurl"];
			$this->cfg["baseurl"] = "http://".$serv;
		}

		get_instance("icons");

		//while($row = $this->db_next())
		while($row = $this->get_next())
		{
			$this->rescounter++;
			$type = $this->cfg["classes"][$row["class_id"]]["name"];
			$row["icon"] = sprintf("<img src='%s' alt='$type' title='$type'>",icons::get_icon_url($row["class_id"],""));
			if (!$row["name"])
			{
				$row["name"] = "(nimetu)";
			};
			$row["type"] = $type;
			$row["location"] = $obj_list[$row["parent"]];
			$this->search_callback(array(
				"name" => "modify_data",
				"data" => &$row,
				"args" => $args,
			));
			if (!isset($args["clid"]) || ($args["clid"] == "aliasmgr"))
			{
				$row["name"] = "<a href='" . $this->mk_my_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $this->cfg["classes"][$row["class_id"]]["file"]) . "'>$row[name]</a>";
			};

			// trim the location to show only up to 3 levels
			$_loc = explode("/",$row["location"]);
			while(sizeof($_loc) > 3)
			{
				array_shift($_loc);
			};
			$row["location"] = join("/",$_loc);

			if (!isset($args["clid"]))
			{
				$row["change"] = "<input type='checkbox' name='sel[$row[oid]]' value='$row[oid]'>";
			};

			if ($format == "vcl")
			{
				$this->t->define_data($row);
			};
			$results++;
		};

		if ($args["s"]["server"])
		{
			$this->cfg["baseurl"] = $old_bu;
		}


		if ($partcount > 0)
		{
			if (!$sortby)
			{
				$sortby = "name";
			}
			if ($format == "vcl")
			{
				$this->t->sort_by(array("field" => $sortby));
				$this->table .= "<span style='font-family: Arial; font-size: 12px;'>$results tulemust</span>";
				$this->table .= $this->t->draw();
				$this->table .= "<span style='font-family: Arial; font-size: 12px;'>$results tulemust</span>";
			};
		};

		if ($results > 0)
		{
			// I use that in modify_toolbar to determine whether
			// to show the "create object group" and "assign configuration"
			// buttons
			$this->has_results = 1;
		};
	}

	function view($args = array())
	{
		extract($args);
		$fobj = $this->get_object($id);
		$meta = new aw_array($fobj["meta"]);
		$params = array();
		foreach($meta->get() as $key => $val)
		{
			if (substr($key,0,2) == "s_")
			{
				$realkey = substr($key,2);
				$params[$realkey] = $val;
			};
		};
		$real_fields = $this->get_real_fields(array("s" => $params));
		$this->execute_search(array(
			"real_fields" => $real_fields,
			"request" => $args,
		));
		$this->mk_path($fobj["parent"],"Vaata otsingut $fobj[name]");
		return $this->table;
	}

	function get_search_results($args = array())
	{
		extract($args);
		$fobj = $this->get_object($id);
		$meta = new aw_array($fobj["meta"]);
		$params = array();
		foreach($meta->get() as $key => $val)
		{
			if (substr($key,0,2) == "s_")
			{
				$realkey = substr($key,2);
				$params[$realkey] = $val;
			};
		};
		$real_fields = $this->get_real_fields(array("s" => $params));
		$this->execute_search(array(
			"real_fields" => $real_fields,
			"request" => $args,
			"format" => "rows",
		));
		return $this->db_rows;
	}

	function on_get_subtemplate_content($arr)
	{
		$id = $arr["inst"]->section_obj->id();
		$t = get_instance("search_conf");
		$def = $GLOBALS["HTTP_GET_VARS"]["parent"] ? $GLOBALS["HTTP_GET_VARS"]["parent"] : $id;
		$sl = $t->get_search_list(&$def);
		$arr["inst"]->vars(array(
			"search_sel" => $this->option_list($def,$sl),
			"section" => $id,
			"str" => htmlentities($GLOBALS["HTTP_GET_VARS"]["str"])
		));
		$arr["inst"]->vars(array(
			"SEARCH_SEL" => $arr["inst"]->parse("SEARCH_SEL")
		));
	}
};
?>
